<?php

/** Persists an applicant and their documents as one transaction. */
final class EnrollmentRepository
{
    private PDO $pdo;
    private EnrollmentForm $form;
    private int $applicantId = 0;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function findApplicant(int $id): ?array
    {
        $statement = $this->pdo->prepare(
            'SELECT a.*, c.code, c.name AS course_name FROM enr_applicants a
             LEFT JOIN rgr_courses c ON a.course_id = c.id WHERE a.applicant_id = ? LIMIT 1'
        );
        $statement->execute([$id]);
        return $statement->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function findCourse(int $id): ?Course
    {
        $statement = $this->pdo->prepare('SELECT id, code, name, years FROM rgr_courses WHERE id = ? LIMIT 1');
        $statement->execute([$id]);
        $row = $statement->fetch(PDO::FETCH_ASSOC);
        return $row ? new Course($row) : null;
    }

    public function requirementsFor(int $applicantId): array
    {
        $statement = $this->pdo->prepare(
            'SELECT requirement_id, file_name, custom_name, notes FROM enr_student_requirements
             WHERE applicant_id = ? AND is_submitted = 1 AND OCTET_LENGTH(file_data) > 0 ORDER BY id'
        );
        $statement->execute([$applicantId]);
        $requirements = [];
        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $requirements[(int) $row['requirement_id']] = $row;
        }
        return $requirements;
    }

    /** @return array{applicantId: int, savedFiles: int} */
    public function save(EnrollmentForm $form, int $applicantId = 0): array
    {
        $this->form = $form;
        $this->applicantId = $applicantId;
        $this->pdo->beginTransaction();
        try {
            if ($applicantId > 0) {
                $lock = $this->pdo->prepare('SELECT applicant_id FROM enr_applicants WHERE applicant_id = ? FOR UPDATE');
                $lock->execute([$applicantId]);
                if (!$lock->fetchColumn()) {
                    throw new RuntimeException('Applicant no longer exists.');
                }
                $this->updateApplicant();
            } else {
                $this->insertApplicant();
            }
            $savedFiles = $this->saveRequirementFiles($this->applicantId);
            $this->pdo->commit();
            return ['applicantId' => $this->applicantId, 'savedFiles' => $savedFiles];
        } catch (Throwable $error) {
            if ($this->pdo->inTransaction()) $this->pdo->rollBack();
            throw $error;
        }
    }

    private function insertApplicant(): void
    {
        $sql = "INSERT INTO enr_applicants (
                    surname, first_name, middle_name, suffix,
                    admission_type, working_student,
                    sex, civil_status, religion,
                    date_of_birth, place_of_birth, age,
                    email, contact_number,
                    facebook, messenger,
                    address_complete, address_barangay, address_city, address_province,
                    parent_full_name, parent_contact, parent_address,
                    school_last_attended, year_graduated,
                    how_hear, course_id, status, submitted_at
                ) VALUES (
                    :surname, :first_name, :middle_name, :suffix,
                    :admission_type, :working_student,
                    :sex, :civil_status, :religion,
                    :date_of_birth, :place_of_birth, :age,
                    :email, :contact_number,
                    :facebook, :messenger,
                    :address_complete, :address_barangay, :address_city, :address_province,
                    :parent_full_name, :parent_contact, :parent_address,
                    :school_last_attended, :year_graduated,
                    :how_hear, :course_id, 'pending', NOW()
                )";

        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute($this->buildBindings());

        if (!$ok) {
            throw new RuntimeException('Could not create applicant.');
        }
        $this->applicantId = (int) $this->pdo->lastInsertId();
    }

    private function updateApplicant(): void
    {
        $sql = "UPDATE enr_applicants SET
                    surname = :surname,
                    first_name = :first_name,
                    middle_name = :middle_name,
                    suffix = :suffix,
                    admission_type = :admission_type,
                    working_student = :working_student,
                    sex = :sex,
                    civil_status = :civil_status,
                    religion = :religion,
                    date_of_birth = :date_of_birth,
                    place_of_birth = :place_of_birth,
                    age = :age,
                    email = :email,
                    contact_number = :contact_number,
                    facebook = :facebook,
                    messenger = :messenger,
                    address_complete = :address_complete,
                    address_barangay = :address_barangay,
                    address_city = :address_city,
                    address_province = :address_province,
                    parent_full_name = :parent_full_name,
                    parent_contact = :parent_contact,
                    parent_address = :parent_address,
                    school_last_attended = :school_last_attended,
                    year_graduated = :year_graduated,
                    how_hear = :how_hear,
                    course_id = :course_id,
                    updated_at = NOW()
                WHERE applicant_id = :applicant_id";

        $bindings = $this->buildBindings();
        $bindings[':applicant_id'] = $this->applicantId;

        $stmt = $this->pdo->prepare($sql);
        $ok   = $stmt->execute($bindings);

        if (!$ok) {
            throw new RuntimeException('Could not update applicant.');
        }
    }

    /**
     * Insert/update requirement files.
     * Returns number of files successfully saved.
     */
    private function saveRequirementFiles(int $applicantId): int
    {
        $files = $this->form->get('requirements', []);
        if (empty($files) || !$this->pdo) {
            return 0;
        }

        $insert = $this->pdo->prepare("INSERT INTO enr_student_requirements
                    (applicant_id, requirement_id, file_data, file_name, custom_name, file_mime,
                     notes, is_submitted, submitted_date)
                VALUES (:aid, :rid, :data, :name, :custom_name, :mime, :notes, 1, NOW())");
        $update = $this->pdo->prepare("UPDATE enr_student_requirements SET
                    file_data = :data,
                    file_name = :name,
                    custom_name = :custom_name,
                    file_mime = :mime,
                    notes = :notes,
                    is_submitted = 1,
                    submitted_date = NOW()
                WHERE applicant_id = :aid AND requirement_id = :rid");
        $find = $this->pdo->prepare(
            'SELECT id FROM enr_student_requirements WHERE applicant_id = ? AND requirement_id = ? LIMIT 1'
        );
        $saved = 0;

        foreach ($files as $reqId => $file) {
            $mime = $this->form->detectMime($file);
            $allowed = ['image/jpeg','image/png','image/webp','image/gif','application/pdf'];
            if ($file['error'] !== UPLOAD_ERR_OK || $file['size'] > 5 * 1024 * 1024
                || !is_uploaded_file($file['tmp_name']) || !in_array($mime, $allowed, true)) {
                throw new RuntimeException('Invalid requirement upload.');
            }
            $data = @file_get_contents($file['tmp_name']);
            if ($data === false || $data === '') {
                throw new RuntimeException('Could not read requirement upload.');
            }

            $find->execute([$applicantId, (int) $reqId]);
            $stmt = $find->fetchColumn() === false ? $insert : $update;
            $stmt->bindValue(':aid',  $applicantId, PDO::PARAM_INT);
            $stmt->bindValue(':rid',  (int)$reqId, PDO::PARAM_INT);
            $stmt->bindValue(':data', $data, PDO::PARAM_LOB);
            $stmt->bindValue(':name', basename($file['name']));
            $stmt->bindValue(':custom_name', $file['custom_name'] !== '' ? $file['custom_name'] : basename($file['name']));
            $stmt->bindValue(':mime', $mime);
            $stmt->bindValue(':notes', $file['notes'] !== '' ? $file['notes'] : null);
            $stmt->execute();
            $saved++;
        }
        return $saved;
    }

    private function buildBindings(): array
    {
        return [
            ':surname'              => $this->form->get('surname'),
            ':first_name'           => $this->form->get('first_name'),
            ':middle_name'          => $this->form->get('middle_name'),
            ':suffix'               => $this->form->get('suffix'),
            ':admission_type'       => $this->form->get('admission_type'),
            ':working_student'      => $this->form->get('working_student'),
            ':sex'                  => $this->form->get('sex'),
            ':civil_status'         => $this->form->get('civil_status'),
            ':religion'             => $this->form->get('religion'),
            ':date_of_birth'        => $this->form->get('date_of_birth'),
            ':place_of_birth'       => $this->form->get('place_of_birth'),
            ':age'                  => (int) $this->form->get('age', 0),
            ':email'                => $this->form->get('email'),
            ':contact_number'       => $this->form->get('contact_number'),
            ':facebook'             => $this->form->get('facebook'),
            ':messenger'            => $this->form->get('messenger'),
            ':address_complete'     => $this->form->get('address_complete'),
            ':address_barangay'     => $this->form->get('address_barangay'),
            ':address_city'         => $this->form->get('address_city'),
            ':address_province'     => $this->form->get('address_province'),
            ':parent_full_name'     => $this->form->get('parent_full_name'),
            ':parent_contact'       => $this->form->get('parent_contact'),
            ':parent_address'       => $this->form->get('parent_address'),
            ':school_last_attended' => $this->form->get('school_last_attended'),
            ':year_graduated'       => $this->form->get('year_graduated'),
            ':how_hear'             => $this->form->get('how_hear'),
            ':course_id'            => (int) $this->form->get('course_id'),
        ];
    }

}
