<?php

require_once __DIR__ . '/../bootstrap.php';

function check(bool $condition, string $message): void
{
    if (!$condition) throw new RuntimeException($message);
}

/** Transaction double: these checks never write to the configured database. */
class TestConnection extends PDO
{
    public array $calls = [];
    public string $failOn = '';
    public bool $active = false;
    public bool $applicantExists = true;

    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false
    {
        return new TestStatement($this, $query);
    }
    public function beginTransaction(): bool { $this->calls[] = 'begin'; return $this->active = true; }
    public function commit(): bool { $this->calls[] = 'commit'; $this->active = false; return true; }
    public function rollBack(): bool { $this->calls[] = 'rollback'; $this->active = false; return true; }
    public function inTransaction(): bool { return $this->active; }
    public function lastInsertId(?string $name = null): string|false { return '42'; }
}

class TestStatement extends PDOStatement
{
    public function __construct(private TestConnection $connection, private string $query) {}
    public function execute(?array $params = null): bool
    {
        $this->connection->calls[] = $this->query;
        if ($this->connection->failOn !== '' && str_contains($this->query, $this->connection->failOn)) {
            throw new PDOException('Simulated database failure');
        }
        return true;
    }
    public function fetchColumn(int $column = 0): mixed { return $this->connection->applicantExists ? 42 : false; }
    public function fetchAll(int $mode = PDO::FETCH_DEFAULT, mixed ...$args): array { return []; }
}

$connection = new TestConnection();
$instance = new ReflectionProperty(Database::class, 'instance');
$instance->setValue(null, $connection);
(new ReflectionProperty(Database::class, 'attempted'))->setValue(null, true);

$_POST = $_FILES = [];
$valid = [
    'surname' => "O'Connor", 'first_name' => 'Test', 'admission_type' => 'returnee',
    'sex' => 'Female', 'civil_status' => 'Single', 'date_of_birth' => '2000-01-01',
    'place_of_birth' => 'Manila', 'email' => 'test@example.com', 'contact_number' => '09123456789',
    'address_street' => '12 Test Street', 'address_barangay' => 'Sample', 'address_city' => 'Quezon City',
    'address_province' => 'Metro Manila', 'parent_full_name' => 'Test Guardian',
    'school_last_attended' => 'Test School', 'year_graduated' => '2025', 'age' => '999',
];
$existing = array_fill_keys([3, 4, 5, 6], ['file_name' => 'existing.pdf']);
$form = new EnrollmentForm($valid + ['course_id' => 999], 7);
check($form->validate($existing), implode('; ', $form->getErrors()));
check($form->get('course_id') === 7, 'Selected course must come from server context');
check($form->get('age') !== 999, 'Age must be calculated on the server');
check($form->get('address_complete') === '12 Test Street, Brgy. Sample, Quezon City, Metro Manila', 'Combined address');

$loaded = new EnrollmentForm();
$loaded->fillFrom($form->toArray() + []);
$old = $form->toArray();
unset($old['address_street']);
$loaded = new EnrollmentForm();
$loaded->fillFrom($old);
check($loaded->get('address_street') === '12 Test Street', 'Existing combined address restores street');

$invalid = new EnrollmentForm(array_replace($valid, ['email' => 'invalid', 'date_of_birth' => '2099-02-31']), 7);
check(!$invalid->validate($existing), 'Invalid email and date must be rejected');
check(count($invalid->getErrors()) >= 2, 'Date and email validation errors');
$missing = new EnrollmentForm($valid, 7);
check(!$missing->validate(), 'Missing required documents must be rejected');
$badInput = new ContactMessage(['name' => ['invalid'], 'email' => ['invalid']]);
check(!$badInput->validate(), 'Array input must not crash the contact form');

$repository = new EnrollmentRepository($connection);
$result = $repository->save($form);
check($result === ['applicantId' => 42, 'savedFiles' => 0], 'Insert result');
check(end($connection->calls) === 'commit', 'Insert commits');
$repository->save($form, 42);
check(end($connection->calls) === 'commit', 'Update commits');
check(count(array_filter($connection->calls, static fn($call) => str_contains($call, 'FOR UPDATE'))) === 1, 'Updates lock the applicant');

$connection->failOn = 'UPDATE enr_applicants';
try {
    $repository->save($form, 42);
    throw new RuntimeException('Expected a database exception');
} catch (PDOException $expected) {
    check(end($connection->calls) === 'rollback', 'Database failure rolls back');
}
$connection->failOn = '';
$badUpload = new EnrollmentForm($valid, 7);
$badUpload->requirements = [3 => ['error' => UPLOAD_ERR_PARTIAL, 'tmp_name' => '', 'size' => 0]];
try {
    $repository->save($badUpload);
    throw new LogicException('Expected upload rejection');
} catch (RuntimeException $expected) {
    check(end($connection->calls) === 'rollback', 'Document failure rolls back applicant creation');
}

$payload = ['name' => "O'Connor </script><script>alert(1)</script> & \"quoted\""];
$json = PageView::json($payload);
check(!str_contains($json, '</script>') && json_decode($json, true) === $payload, 'Safe JSON round trip');

$controller = new EnrollmentFormController();
$controller->form = $form;
$controller->message = 'Saved';
$controller->messageType = 'success';
$controller->shouldRedirect = true;
$controller->redirectUrl = 'enroll.php?applicant_id=42';
ob_start();
PageView::render('enrollment-form', $controller->viewData());
$html = ob_get_clean();
check(!str_contains($html, 'id="map"'), 'Success view omits the form and map');
check(str_contains($html, 'application/json'), 'Success redirect is serialized as data');
check(!str_contains($html, '<script>'), 'No executable inline scripts');

echo "PASS: validation, existing address restoration, JSON safety, insert/update transactions, rollback, and success view.\n";
