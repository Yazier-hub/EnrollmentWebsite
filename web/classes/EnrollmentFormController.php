<?php
// classes/EnrollmentFormController.php

require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Applicant.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/EnrollmentForm.php';
require_once __DIR__ . '/Requirement.php';
require_once __DIR__ . '/EnrollmentRepository.php';

class EnrollmentFormController
{
    private ?PDO $pdo;

    public int         $courseId    = 0;
    public int         $applicantId = 0;
    public ?Course     $course      = null;
    public ?Applicant  $applicant   = null;

    public EnrollmentForm $form;

    public string $message        = '';
    public string $messageType    = ''; // success | warning | error
    public bool   $shouldRedirect = false;
    public string $redirectUrl    = '';

    /** @var array<string,array> requirement definitions per admission type */
    public array $requiredDocsByType = [];
    public array $existingRequirements = [];

    public function __construct()
    {
        $this->pdo  = Database::getConnection();
        $this->form = new EnrollmentForm();

        $reqModel = new Requirement();
        $this->requiredDocsByType = $reqModel->allByType();
    }

    public function handleRequest(): void
    {
        if (!$this->pdo) {
            $this->message     = 'Database connection unavailable.';
            $this->messageType = 'error';
            return;
        }

        $this->courseId = isset($_SESSION['selected_course_id'])
            ? (int) $_SESSION['selected_course_id']
            : 0;

        $this->applicantId = isset($_GET['applicant_id'])
            ? (int) $_GET['applicant_id']
            : 0;

        if ($this->applicantId > 0) {
            $this->loadApplicant($this->applicantId);
            $this->loadExistingRequirements();
        }

        if ($this->courseId === 0 && !$this->applicant) {
            $this->redirectUrl    = 'selecting-course.php';
            $this->shouldRedirect = true;
            return;
        }

        if ($this->courseId > 0) {
            $this->course = $this->fetchCourse($this->courseId);
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && empty($_POST) && empty($_FILES)
            && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
            $this->message = 'The upload exceeds the server request limit. Please choose smaller files and try again.';
            $this->messageType = 'error';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST'
            && ($_POST['action'] ?? '') === 'submit') {
            $this->handlePost();
        }

    }

    private function loadApplicant(int $id): void
    {
        try {
            $row = (new EnrollmentRepository($this->pdo))->findApplicant($id);

            if ($row) {
                $this->applicant = new Applicant($row);
                if (!empty($row['course_id'])) {
                    $this->courseId = (int) $row['course_id'];
                }
                $this->form->fillFrom($row);
            }
        } catch (PDOException $e) {
            error_log('loadApplicant failed: ' . $e->getMessage());
        }
    }

    private function loadExistingRequirements(): void
    {
        try {
            $this->existingRequirements = (new EnrollmentRepository($this->pdo))->requirementsFor($this->applicantId);
        } catch (PDOException $error) {
            error_log('Requirement lookup failed: ' . $error->getMessage());
            $this->message = 'Your saved documents could not be loaded. Please try again later.';
            $this->messageType = 'error';
        }
    }

    private function fetchCourse(int $id): ?Course
    {
        try {
            return (new EnrollmentRepository($this->pdo))->findCourse($id);
        } catch (PDOException $error) {
            error_log('Course lookup failed: ' . $error->getMessage());
            return null;
        }
    }

    private function handlePost(): void
    {
        $this->form = new EnrollmentForm($_POST, $this->courseId);

        if (!$this->form->validate($this->existingRequirements)) {
            $this->message     = $this->form->getErrorHtml();
            if ($this->form->requirements) {
                $this->message .= '<br>Please select your document files again before resubmitting.';
            }
            $this->messageType = 'error';
            return;
        }

        $isUpdate = $this->applicantId > 0;
        try {
            $result = (new EnrollmentRepository($this->pdo))->save($this->form, $this->applicantId);
            $this->applicantId = $result['applicantId'];
            $this->message = ($isUpdate ? 'Application updated' : 'Application submitted')
                . ' successfully! Applicant ID: ' . $this->applicantId
                . ($result['savedFiles'] > 0 ? ' (' . $result['savedFiles'] . ' document(s) uploaded)' : '');
            $this->messageType = 'success';
            $this->redirectUrl = 'enroll.php?applicant_id=' . $this->applicantId;
            $this->shouldRedirect = true;
            unset($_SESSION['selected_course_id']);
        } catch (Throwable $error) {
            error_log('Enrollment save failed: ' . $error->getMessage());
            $this->message = 'Your application and documents could not be saved. Please select your files again and retry.';
            $this->messageType = 'error';
        }
    }

    public function viewData(): array
    {
        if ($this->shouldRedirect && $this->messageType === '') {
            header('Location: ' . $this->redirectUrl);
            exit;
        }

        $courseInfo = $this->course && $this->course->exists()
            ? ['code' => $this->course->code, 'name' => $this->course->name, 'years' => $this->course->years]
            : null;

        return [
            'course_id' => $this->courseId,
            'course_info' => $courseInfo,
            'formData' => $this->form->toArray(),
            'message' => $this->message,
            'messageType' => $this->messageType,
            'clientData' => [
                'formData' => $this->form->toArray(),
                'requiredDocsByType' => $this->requiredDocsByType,
                'existingRequirements' => (object) $this->existingRequirements,
                'redirectUrl' => $this->shouldRedirect && $this->messageType === 'success'
                    ? $this->redirectUrl : null,
                'redirectDelay' => 2000,
            ],
        ];
    }

}
