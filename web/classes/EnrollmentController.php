<?php
// classes/EnrollmentController.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Applicant.php';
require_once __DIR__ . '/EnrollmentRepository.php';

class EnrollmentController
{
    private ?PDO $pdo;
    public Applicant $applicant;

    public function __construct()
    {
        $this->pdo       = Database::getConnection();
        $this->applicant = new Applicant();
    }

    /**
     * Read applicant_id from GET (or POST) and load the record.
     */
    public function handleRequest(): void
    {
        $applicantId = isset($_GET['applicant_id'])
            ? (int) $_GET['applicant_id']
            : 0;

        if ($applicantId <= 0 || !$this->pdo) {
            return;
        }

        try {
            $row = (new EnrollmentRepository($this->pdo))->findApplicant($applicantId);

            if ($row) {
                $this->applicant = new Applicant($row);
            }
        } catch (PDOException $e) {
            error_log('EnrollmentController load failed: ' . $e->getMessage());
        }
    }

    public function viewData(): array
    {
        return [
            'applicant' => $this->applicant,
            'hasApp' => $this->hasApplicant(),
        ];
    }

    public function hasApplicant(): bool
    {
        return $this->applicant->exists();
    }

}
