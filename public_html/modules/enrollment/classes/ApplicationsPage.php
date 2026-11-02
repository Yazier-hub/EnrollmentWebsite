<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/ApplicationController.php';

/** Request handling and view data for applications. */
final class ApplicationsPage extends EnrollmentPage
{
    protected const VIEW = 'applications';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $applicationController = new ApplicationController();

        // Cleanup once per day
        $today = date('Y-m-d');
        if (($_SESSION['last_app_cleanup'] ?? '') !== $today) {
            $applicationController->deleteOldApplications();
            $_SESSION['last_app_cleanup'] = $today;
        }

        // AJAX
        if (isset($_GET['ajax'])) {
            $applicationController->handleAjaxRequest();
            exit;
        }

        // POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $applicationController->handlePostRequest();
        }

        // Filters
        $status = $_GET['status'] ?? 'pending';
        $search = $_GET['search'] ?? '';

        if (isset($_GET['search']) && $_GET['search'] === '') {
            $params = $_GET;
            unset($params['search']);
            $newUrl = '?' . http_build_query($params);
            if ($_SERVER['REQUEST_URI'] !== $newUrl) {
                header('Location: ' . $newUrl);
                exit;
            }
        }

        // Load applications
        if ($status === 'all') {
            $applications = $applicationController->getAllApplications();
        } else {
            $applications = $applicationController->getApplicationsByStatus($status);
        }

        if (!is_array($applications)) $applications = [];

        // Search filter
        if ($search !== '') {
            $needle = strtolower($search);
            $applications = array_filter($applications, function ($app) use ($needle) {
                return strpos(strtolower($app['first_name']     ?? ''), $needle) !== false
                    || strpos(strtolower($app['surname']        ?? ''), $needle) !== false
                    || strpos(strtolower($app['email']          ?? ''), $needle) !== false
                    || strpos(strtolower($app['contact_number'] ?? ''), $needle) !== false;
            });
        }

        $courses = $applicationController->getCourses();
        if (!is_array($courses)) $courses = [];

        $stats = $applicationController->getStats();

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $pageTitle = 'Applications Management';

        return [
            'stats' => $stats ?? null,
            'message' => $message ?? null,
            'status' => $status ?? null,
            'search' => $search ?? null,
            'applications' => $applications ?? null,
            'courses' => $courses ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
