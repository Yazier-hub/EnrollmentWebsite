<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/CourseController.php';
require_once __DIR__ . '/Section.php';

/** Request handling and view data for courses. */
final class CoursesPage extends EnrollmentPage
{
    protected const VIEW = 'courses';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $courseController = new CourseController();
        $section          = new Section();

        // ============================================================
        // AJAX
        // ============================================================
        if (isset($_GET['ajax'])) {
            $courseController->handleAjaxRequest();
            exit;
        }

        // ============================================================
        // POST
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $courseController->handlePostRequest();
            // handlePostRequest() redirects and exits
        }

        // ============================================================
        // Data
        // ============================================================
        $courses = $courseController->getAllCoursesWithStats();
        if (!is_array($courses)) $courses = [];

        $stats = $courseController->getCourseStats();
        if (!is_array($stats)) $stats = [];

        $popularCourses = $courseController->getPopularCourses(5);
        if (!is_array($popularCourses)) $popularCourses = [];

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $pageTitle = 'Courses Management';
        $clientData = [
            'courseCodes' => array_column($courses, 'code'),
        ];

        return [
            'clientData' => $clientData ?? null,
            'message' => $message ?? null,
            'stats' => $stats ?? null,
            'courses' => $courses ?? null,
            'popularCourses' => $popularCourses ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
