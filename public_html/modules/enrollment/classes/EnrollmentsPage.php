<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Section.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Requirement.php';
require_once __DIR__ . '/EnrollmentController.php';
require_once __DIR__ . '/StudentProgression.php';

/** Request handling and view data for enrollments. */
final class EnrollmentsPage extends EnrollmentPage
{
    protected const VIEW = 'enrollments';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $basePath = dirname(__DIR__);

        $controller         = new EnrollmentController();
        $studentProgression = new StudentProgression();
        $enrollmentModel    = new Enrollment();
        $requirementModel   = new Requirement();   // ⭐ para sa payment gate

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handlePostRequest();
        }

        $search         = $_GET['search']          ?? '';
        $enrolledSearch = $_GET['enrolled_search'] ?? '';
        $courseFilter   = isset($_GET['course_id'])  ? (int) $_GET['course_id']  : '';
        $yearFilter     = isset($_GET['year_level']) ? (int) $_GET['year_level'] : '';

        $pendingApplicants = $controller->getPendingApplicants($search);
        if (!is_array($pendingApplicants)) $pendingApplicants = [];

        $enrollments = $controller->getEnrolledStudents($courseFilter, $yearFilter);
        if (!is_array($enrollments)) $enrollments = [];

        if (!empty($enrolledSearch)) {
            $needle = strtolower(trim($enrolledSearch));
            $enrollments = array_filter($enrollments, function ($e) use ($needle) {
                $fullName = strtolower(trim(
                    ($e['first_name']  ?? '') . ' ' .
                    ($e['middle_name'] ?? '') . ' ' .
                    ($e['surname']     ?? '') . ' ' .
                    ($e['suffix']      ?? '')
                ));
                return strpos($fullName, $needle) !== false
                    || strpos(strtolower($e['student_number'] ?? ''), $needle) !== false
                    || strpos(strtolower($e['section_code']   ?? ''), $needle) !== false
                    || strpos(strtolower($e['course_code']    ?? ''), $needle) !== false;
            });
            $enrollments = array_values($enrollments);
        }

        $courses = $controller->getAllCourses();
        if (!is_array($courses)) $courses = [];

        $archivedCount = (int) $controller->getArchivedStudentCount();

        $sectionModel      = new Section();
        $sections          = $sectionModel->getAllSectionsWithDetails();
        if (!is_array($sections)) $sections = [];
        $availableSections = count($sections);

        $applicationModel = new Application();
        $allApplications  = $applicationModel->getAllApplications();
        if (!is_array($allApplications)) $allApplications = [];

        $noCourseCount = 0;
        foreach ($allApplications as $app) {
            if (empty($app['course_id']) && ($app['status'] ?? '') === 'pending') {
                $noCourseCount++;
            }
        }

        $currentSchoolYear = date('Y') . '-' . (date('Y') + 1);
        $nextSchoolYear    = (date('Y') + 1) . '-' . (date('Y') + 2);

        $message       = $_SESSION['message']        ?? '';
        $accountCreated = $_SESSION['account_created'] ?? false;
        $username      = $_SESSION['username']       ?? '';
        $password      = $_SESSION['password']       ?? '';
        $isNewAccount  = $_SESSION['is_new_account'] ?? false;
        $emailSent     = $_SESSION['email_sent']     ?? false;
        $emailError    = $_SESSION['email_error']    ?? null;

        unset(
            $_SESSION['message'],
            $_SESSION['account_created'],
            $_SESSION['username'],
            $_SESSION['password'],
            $_SESSION['is_new_account'],
            $_SESSION['email_sent'],
            $_SESSION['email_error']
        );

        $stats = $controller->getEnrollmentStats();
        if (!is_array($stats)) $stats = [];

        $protocol   = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https://' : 'http://';
        $host       = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $scriptPath = rtrim(dirname($_SERVER['SCRIPT_NAME'] ?? ''), '/');
        $apiBaseUrl = $protocol . $host . $scriptPath;

        // ⭐ Bilang ng students na may pending payment OR walang verified for current term
        $pendingPaymentCount    = 0;
        $noVerifiedPaymentCount = 0;
        foreach ($enrollments as $e) {
            $sid = (int) $e['student_id'];
            if ($requirementModel->hasPendingPayment($sid)) {
                $pendingPaymentCount++;
            } elseif (!$requirementModel->hasVerifiedPaymentForCurrentTerm($sid)) {
                $noVerifiedPaymentCount++;
            }
        }

        $pageTitle = 'Enrollment Management';
        $clientData = [
            'currentSchoolYear' => $currentSchoolYear,
            'apiBaseUrl' => $apiBaseUrl,
        ];

        $enrollmentDetails = [];
        foreach ($enrollments as $row) {
            $studentId = (int) $row['student_id'];
            $enrollmentDetails[$studentId] = [
                'paymentGate' => $requirementModel->canEnrollBasedOnPayment($studentId),
                'hasPendingPayment' => $requirementModel->hasPendingPayment($studentId),
                'hasVerifiedCurrent' => $requirementModel->hasVerifiedPaymentForCurrentTerm($studentId),
                'latestPayment' => $requirementModel->getLatestPaymentStatus($studentId),
                'current' => $enrollmentModel->getStudentCurrentProgression($studentId),
                'next' => $enrollmentModel->getStudentNextProgression($studentId),
            ];
        }

        return [
            'clientData' => $clientData ?? null,
            'message' => $message ?? null,
            'pendingPaymentCount' => $pendingPaymentCount ?? null,
            'noVerifiedPaymentCount' => $noVerifiedPaymentCount ?? null,
            'accountCreated' => $accountCreated ?? null,
            'username' => $username ?? null,
            'password' => $password ?? null,
            'isNewAccount' => $isNewAccount ?? null,
            'emailSent' => $emailSent ?? null,
            'stats' => $stats ?? null,
            'availableSections' => $availableSections ?? null,
            'noCourseCount' => $noCourseCount ?? null,
            'search' => $search ?? null,
            'pendingApplicants' => $pendingApplicants ?? null,
            'enrollments' => $enrollments ?? null,
            'archivedCount' => $archivedCount ?? null,
            'enrolledSearch' => $enrolledSearch ?? null,
            'courses' => $courses ?? null,
            'courseFilter' => $courseFilter ?? null,
            'yearFilter' => $yearFilter ?? null,
            'enrollmentDetails' => $enrollmentDetails ?? null,
            'currentSchoolYear' => $currentSchoolYear ?? null,
            'nextSchoolYear' => $nextSchoolYear ?? null,
            'studentProgression' => $studentProgression ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
