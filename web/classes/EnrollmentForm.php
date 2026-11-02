<?php
// classes/EnrollmentForm.php

require_once __DIR__ . '/Requirement.php';

class EnrollmentForm
{
    public array $data = [
        'surname'             => '',
        'first_name'          => '',
        'middle_name'         => '',
        'suffix'              => '',
        'admission_type'      => '',
        'working_student'     => 'No',
        'sex'                 => '',
        'civil_status'        => '',
        'religion'            => '',
        'date_of_birth'       => '',
        'place_of_birth'      => '',
        'age'                 => 0,
        'email'               => '',
        'contact_number'      => '',
        'facebook'            => '',
        'messenger'           => '',
        'address_complete'    => '',
        'address_barangay'    => '',
        'address_city'        => '',
        'address_province'    => '',
        'address_region'      => '',
        'address_street'      => '',
        'address_lat'         => '',
        'address_lng'         => '',
        'parent_full_name'    => '',
        'parent_contact'      => '',
        'parent_address'      => '',
        'school_last_attended'=> '',
        'year_graduated'      => '',
        'how_hear'            => '',
        'course_id'           => 0,
    ];

    /** @var array<int,array<string,mixed>> Uploaded file metas keyed by requirement_id */
    public array $requirements = [];

    private array $errors = [];

    /** File upload limits */
    private const MAX_FILE_BYTES = 5 * 1024 * 1024; // 5 MB
    private const ALLOWED_MIMES  = [
        'image/jpeg', 'image/png', 'image/webp', 'image/gif',
        'application/pdf',
    ];

    public function __construct(array $input = [], int $defaultCourseId = 0)
    {
        foreach ($this->data as $key => $default) {
            if ($key === 'course_id') {
                $this->data[$key] = $defaultCourseId > 0 ? $defaultCourseId
                    : (is_scalar($input[$key] ?? null) ? (int) $input[$key] : 0);
                continue;
            }
            $this->data[$key] = isset($input[$key]) && is_scalar($input[$key])
                ? trim((string) $input[$key])
                : $default;
        }

        $this->data['age'] = (int) ($this->data['age'] ?: 0);
        $this->requirements = $this->normalizeUploadedFiles();
    }

    public function fillFrom(array $row): void
    {
        foreach ($row as $key => $value) {
            if (array_key_exists($key, $this->data)) {
                $this->data[$key] = $value ?? $this->data[$key];
            }
        }
        // Older records only store the combined address and its main components.
        if ($this->data['address_street'] === '' && $this->data['address_complete'] !== '') {
            $components = array_filter([
                $this->data['address_barangay'],
                'Brgy. ' . $this->data['address_barangay'],
                $this->data['address_city'],
                $this->data['address_province'],
                $this->data['address_region'],
            ]);
            $parts = array_map('trim', explode(',', $this->data['address_complete']));
            $this->data['address_street'] = implode(', ', array_diff($parts, $components));
        }
    }

    /**
     * Normalize $_FILES['requirements'] into [req_id => [name, tmp_name, type, size, error]]
     */
    private function normalizeUploadedFiles(): array
    {
        if (!isset($_FILES['requirements'])
            || !is_array($_FILES['requirements']['name'] ?? null)) {
            return [];
        }

        $out = [];
        foreach ($_FILES['requirements']['name'] as $reqId => $name) {
            $err = $_FILES['requirements']['error'][$reqId] ?? UPLOAD_ERR_PARTIAL;
            $tmpName = $_FILES['requirements']['tmp_name'][$reqId] ?? '';
            $size = $_FILES['requirements']['size'][$reqId] ?? 0;
            if (!ctype_digit((string) $reqId) || !is_string($name)
                || !is_string($tmpName) || !is_int($err) || !is_int($size)) {
                $out[0] = ['error' => UPLOAD_ERR_PARTIAL];
                continue;
            }

            if ($err === UPLOAD_ERR_NO_FILE) {
                continue;
            }

            $out[$reqId] = [
                'name'     => (string) $name,
                'tmp_name' => $tmpName,
                'size'     => $size,
                'error'    => $err,
                'custom_name' => is_string($_POST['requirement_names'][$reqId] ?? null)
                    ? trim($_POST['requirement_names'][$reqId]) : '',
                'notes' => is_string($_POST['requirement_notes'][$reqId] ?? null)
                    ? trim($_POST['requirement_notes'][$reqId]) : '',
            ];
        }
        return $out;
    }

    public function validate(array $existingRequirements = []): bool
    {
        $this->errors = [];

        $required = [
            'surname'              => 'Surname is required',
            'first_name'           => 'First Name is required',
            'sex'                  => 'Sex is required',
            'civil_status'         => 'Civil Status is required',
            'date_of_birth'        => 'Date of Birth is required',
            'place_of_birth'       => 'Place of Birth is required',
            'email'                => 'Email Address is required',
            'contact_number'       => 'Contact Number is required',
            'address_barangay'     => 'Barangay is required',
            'address_street'       => 'House No. / Block / Lot is required',
            'address_city'         => 'City/Municipality is required',
            'address_province'     => 'Province is required',
            'parent_full_name'     => 'Parent/Guardian Full Name is required',
            'school_last_attended' => 'School Last Attended is required',
            'year_graduated'       => 'Year Graduated is required',
            'admission_type'       => 'Admission Type is required',
        ];

        foreach ($required as $field => $msg) {
            if (empty($this->data[$field])) {
                $this->errors[] = $msg;
            }
        }

        if (!empty($this->data['email'])
            && !filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            $this->errors[] = 'Please enter a valid email address.';
        }

        if (empty($this->data['course_id'])) {
            $this->errors[] = 'No course selected. Please go back and select a course.';
        }

        if ($this->data['date_of_birth'] !== '') {
            $birthDate = DateTimeImmutable::createFromFormat('!Y-m-d', $this->data['date_of_birth']);
            $today = new DateTimeImmutable('today');
            if (!$birthDate || $birthDate->format('Y-m-d') !== $this->data['date_of_birth'] || $birthDate > $today) {
                $this->errors[] = 'Please enter a valid date of birth that is not in the future.';
            } else {
                $this->data['age'] = $birthDate->diff($today)->y;
            }
        }

        $this->data['address_complete'] = implode(', ', array_filter([
            $this->data['address_street'],
            $this->data['address_barangay'] !== '' ? 'Brgy. ' . $this->data['address_barangay'] : '',
            $this->data['address_city'],
            $this->data['address_province'],
            $this->data['address_region'],
        ]));

        // Validate uploaded files
        $this->validateUploads($existingRequirements);

        return empty($this->errors);
    }

    private function validateUploads(array $existingRequirements): void
    {
        if (empty($this->data['admission_type'])) {
            return;
        }

        $requiredIds = Requirement::idsForAdmissionType($this->data['admission_type']);
        if (!$requiredIds) {
            $this->errors[] = 'Please select a valid admission type.';
            return;
        }

        foreach ($this->requirements as $rid => $file) {
            if (!in_array($rid, $requiredIds, true)) {
                $this->errors[] = 'An uploaded document does not match the selected admission type.';
            }
        }

        $definitions = (new Requirement())->forAdmissionType($this->data['admission_type']);
        $labels = array_column($definitions, 'name', 'id');

        foreach ($requiredIds as $rid) {
            $label = $labels[$rid] ?? Requirement::labelFor($rid);
            if (empty($this->requirements[$rid])) {
                if (empty($existingRequirements[$rid])) {
                    $this->errors[] = 'Missing required document: ' . $label;
                }
                continue;
            }

            $f = $this->requirements[$rid];

            if (mb_strlen($f['name']) > 255 || mb_strlen($f['custom_name']) > 255
                || mb_strlen($f['notes']) > 2000) {
                $this->errors[] = $label . ': file names must be at most 255 characters and notes at most 2,000 characters.';
            }

            if ($f['error'] !== UPLOAD_ERR_OK) {
                $this->errors[] = 'Upload error for '
                    . $label
                    . ' (code ' . $f['error'] . ')';
                continue;
            }

            if ($f['size'] > self::MAX_FILE_BYTES) {
                $this->errors[] = $label
                    . ' exceeds the 5 MB limit.';
                continue;
            }

            if ($f['size'] <= 0 || !is_uploaded_file($f['tmp_name'])) {
                $this->errors[] = $label . ' could not be read. Please select the file again.';
                continue;
            }

            $mime = $this->detectMime($f);
            if ($mime === null || !in_array($mime, self::ALLOWED_MIMES, true)) {
                $this->errors[] = $label
                    . ' must be a JPG, PNG, WEBP, GIF, or PDF file.';
            }
        }
    }

    /** Detect MIME from tmp file (safer than trusting the client). */
    public function detectMime(array $file): ?string
    {
        if (!is_file($file['tmp_name'])) {
            return null;
        }
        $mime = @mime_content_type($file['tmp_name']);
        return $mime ?: null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getErrorHtml(): string
    {
        return 'Please fix the following errors:<br>' . implode('<br>', array_map(
            static fn($error) => htmlspecialchars($error, ENT_QUOTES, 'UTF-8'),
            $this->errors
        ));
    }

    public function get(string $key, $default = null)
    {
        if ($key === 'requirements') {
            return $this->requirements;
        }
        return $this->data[$key] ?? $default;
    }

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
