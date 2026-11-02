<?php
// classes/CourseSelectionController.php

require_once __DIR__ . '/CourseCatalog.php';

class CourseSelectionController
{
    public CourseCatalog $catalog;

    public string $branch      = 'main';
    public string $message     = '';
    public string $messageType = ''; // success | error
    public bool   $shouldRedirect = false;
    public string $redirectUrl    = 'enrollment-form.php';

    public function __construct()
    {
        $this->catalog = (new CourseCatalog())->load();
    }

    public function handleRequest(): void
    {
        // Branch from URL
        if (isset($_GET['branch']) && is_string($_GET['branch'])) {
            $this->branch = $_GET['branch'];
        }

        // Handle POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['course_id'])) {
            $this->handlePost((int) $_POST['course_id']);
        }
    }

    private function handlePost(int $courseId): void
    {
        if ($courseId <= 0) {
            $this->message     = 'Please select a course.';
            $this->messageType = 'error';
            return;
        }

        $course = $this->catalog->find($courseId);

        if (!$course) {
            $this->message     = 'Selected course not found. Please try again.';
            $this->messageType = 'error';
            return;
        }

        $_SESSION['selected_course_id'] = $courseId;

        $this->message     = 'Course selected successfully: ' . $course->label();
        $this->messageType = 'success';

        $this->shouldRedirect = true;
    }

    public function viewData(): array
    {
        return [
            'courses' => $this->catalog->all(),
            'branch' => $this->branch,
            'message' => $this->message,
            'messageType' => $this->messageType,
            'clientData' => [
                'redirectUrl' => $this->shouldRedirect ? $this->redirectUrl : null,
                'redirectDelay' => 1500,
            ],
        ];
    }

}
