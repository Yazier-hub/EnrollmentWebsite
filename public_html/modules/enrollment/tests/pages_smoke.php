<?php
/** Run with php tests/pages_smoke.php. Renders GET views inside a read-only transaction. */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

chdir(dirname(__DIR__));
spl_autoload_register(static function ($class) {
    $file = __DIR__ . '/../classes/' . $class . '.php';
    if (is_file($file)) require_once $file;
});
session_start();
$_SESSION = ['user_id' => 0, 'employee_id' => 0, 'department_id' => 0,
    'role' => 0, 'role_name' => 'test', 'last_app_cleanup' => date('Y-m-d')];
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/modules/enrollment/index.php';
$_SERVER['HTTP_HOST'] = 'localhost';
$pdo = Database::getInstance()->getConnection();
$studentId = (int) $pdo->query('SELECT MIN(student_id) FROM enr_students')->fetchColumn();
$applicantId = (int) $pdo->query('SELECT MIN(applicant_id) FROM enr_applicants')->fetchColumn();
$editableApplicantId = (int) $pdo->query("SELECT MIN(applicant_id) FROM enr_applicants WHERE status <> 'converted'")->fetchColumn();
$routes = array_map(static fn($file) => basename($file, '.php'), glob(__DIR__ . '/../pages/*.php'));
$results = [];
$finished = false;
register_shutdown_function(static function () use (&$finished, &$route) {
    if (!$finished) {
        while (ob_get_level()) ob_end_clean();
        fwrite(STDERR, 'Unexpected redirect or exit while rendering ' . ($route ?? 'setup') . PHP_EOL);
        exit(1);
    }
});
$navigation = new class { public function renderNav(): void {} };
$issues = [];
set_error_handler(static function ($severity, $message, $file, $line) use (&$issues) {
    if (!(error_reporting() & $severity)) return false;
    $issues[] = basename($file) . ':' . $line . ' ' . $message;
    return true;
});

try {
    foreach ($routes as $route) {
        if ($route === 'logout') continue;
        $_GET = ['page' => $route];
        $temporaryApplicant = false;
        if ($route === 'application-edit' && !$editableApplicantId) {
            $fixture = $pdo->query('SELECT * FROM enr_applicants ORDER BY applicant_id LIMIT 1')->fetch(PDO::FETCH_ASSOC);
            if (!$fixture) throw new RuntimeException('An applicant fixture is required for the edit-page smoke check.');
            $fixture['status'] = 'pending';
            $schema = $pdo->query('SHOW COLUMNS FROM enr_applicants')->fetchAll(PDO::FETCH_ASSOC);
            $definitions = array_map(static fn($column) => '`' . $column['Field'] . '` ' . $column['Type'] . ' NULL', $schema);
            $pdo->exec('CREATE TEMPORARY TABLE enr_applicants (' . implode(', ', $definitions) . ')');
            $columns = implode(', ', array_map(static fn($column) => '`' . $column . '`', array_keys($fixture)));
            $placeholders = implode(', ', array_fill(0, count($fixture), '?'));
            $pdo->prepare("INSERT INTO enr_applicants ($columns) VALUES ($placeholders)")->execute(array_values($fixture));
            $editableApplicantId = (int) $fixture['applicant_id'];
            $temporaryApplicant = true;
        }
        if (in_array($route, ['application-details', 'application-edit'], true)) $_GET['id'] = $applicantId;
        if ($route === 'application-edit') $_GET['id'] = $editableApplicantId;
        if (in_array($route, ['student-details', 'student-edit', 'student-subjects'], true)) $_GET['id'] = $studentId;
        if ($route === 'requirements') {
            $_GET['student_id'] = $studentId;
            $_GET['category'] = 'unrelated-category'; // Stale URL filters must not replace the student's checklist.
        }
        $_SERVER['REQUEST_URI'] = '/modules/enrollment/index.php?' . http_build_query($_GET);
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);
        $issues = [];
        $pdo->exec('SET TRANSACTION READ ONLY');
        $pdo->beginTransaction();
        ob_start();
        try {
            EnrollmentPage::forRoute($route)->render($navigation);
            $html = ob_get_clean();
            if (substr_count($html, '<!DOCTYPE html>') !== 1 || substr_count($html, '</html>') !== 1) {
                $issues[] = 'Expected exactly one complete shared layout';
            }
            if (preg_match('/<style\b|<script(?![^>]*\bsrc=)|\son(?:click|change|submit|input)=|\sstyle=/i', $html)) {
                $issues[] = 'Found embedded styles, scripts, or event handlers';
            }
            preg_match_all('/(?:href|src)="(\/modules\/enrollment\/(?:css|js)\/[^"?]+)(?:\?[^"\s]*)?"/', $html, $assets);
            foreach ($assets[1] as $url) {
                if (!is_file(substr($url, strlen('/modules/enrollment/')))) $issues[] = 'Missing asset ' . $url;
            }
            $document = new DOMDocument();
            $previousLibxml = libxml_use_internal_errors(true);
            $document->loadHTML($html);
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxml);
            if ($route === 'requirements') {
                $xpath = new DOMXPath($document);
                $actualIds = [];
                foreach ($xpath->query('//form[@id="studentRequirementsForm"]//input[@name="all_requirement_ids[]"]') as $input) {
                    $actualIds[] = (int) $input->getAttribute('value');
                }
                $expectedIds = array_map('intval', array_column((new Requirement())->getStudentRequirements($studentId), 'requirement_id'));
                sort($actualIds);
                sort($expectedIds);
                if ($actualIds !== $expectedIds) $issues[] = 'Checklist does not match the student admission type';
            }
            foreach ($document->getElementsByTagName('*') as $element) {
                foreach ($element->attributes as $attribute) {
                    if (preg_match('/^data-action\d+-arg-\d+$/', $attribute->name) || $attribute->name === 'data-values') {
                        json_decode($attribute->value, true, 512, JSON_THROW_ON_ERROR);
                    }
                }
            }
        } catch (Throwable $error) {
            ob_end_clean();
            $issues[] = get_class($error) . ': ' . $error->getMessage();
        }
        $results[$route] = $issues;
        if ($pdo->inTransaction()) $pdo->rollBack();
        if ($temporaryApplicant) $pdo->exec('DROP TEMPORARY TABLE enr_applicants');
    }
} finally {
    restore_error_handler();
    if ($pdo->inTransaction()) $pdo->rollBack();
    session_destroy();
}

$failed = 0;
$finished = true;
foreach ($results as $route => $issues) {
    echo ($issues ? 'FAIL ' : 'PASS ') . $route . PHP_EOL;
    foreach ($issues as $issue) echo '  ' . $issue . PHP_EOL;
    if ($issues) $failed++;
}
exit($failed ? 1 : 0);
