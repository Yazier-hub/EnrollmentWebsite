<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Section.php';

/** Request handling and view data for student-subjects. */
final class StudentSubjectsPage extends EnrollmentPage
{
    protected const VIEW = 'student-subjects';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: ?page=login');
            exit;
        }

        $studentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($studentId <= 0) {
            $_SESSION['message'] = '❌ Invalid student ID.';
            header('Location: ?page=students');
            exit;
        }

        $studentModel    = new Student();
        $enrollmentModel = new Enrollment();
        $application     = new Application();
        $courseModel     = new Course();
        $sectionModel    = new Section();

        $student = $studentModel->getStudentDetails($studentId);

        if (!$student) {
            $_SESSION['message'] = '❌ Student not found.';
            header('Location: ?page=students');
            exit;
        }

        $subjects = $enrollmentModel->getStudentCurrentSemesterEnrollments($studentId);
        if (!is_array($subjects)) $subjects = [];

        $enrollment = $enrollmentModel->getEnrollmentByStudentId($studentId);

        $pageTitle = 'Student Subjects - '
            . trim(($student['first_name'] ?? '') . ' ' . ($student['surname'] ?? ''));

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        return [
            'message' => $message,
            'student' => $student ?? null,
            'studentId' => $studentId ?? null,
            'subjects' => $subjects ?? null,
            'enrollment' => $enrollment ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
