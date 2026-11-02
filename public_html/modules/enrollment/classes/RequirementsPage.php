<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Requirement.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/RequirementController.php';

/** Request handling and view data for requirements. */
final class RequirementsPage extends EnrollmentPage
{
    protected const VIEW = 'requirements';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $controller = new RequirementController();

        $controller->handleRequest();

        $view           = $_GET['view']     ?? 'students';
        $studentId      = isset($_GET['student_id']) ? (int) $_GET['student_id'] : null;
        $searchQuery    = isset($_GET['search']) ? trim($_GET['search']) : '';

        $paymentStatusFilter = $_GET['status'] ?? null;
        $paymentSearchQuery  = isset($_GET['pay_search']) ? trim($_GET['pay_search']) : '';
        $paymentSubmissions  = [];
        $paymentCounts       = ['total' => 0, 'pending' => 0, 'verified' => 0, 'rejected' => 0];

        if ($view === 'payments') {
            $paymentSubmissions = $controller->getAllPaymentSubmissions($paymentStatusFilter);
            if (!is_array($paymentSubmissions)) $paymentSubmissions = [];

            if ($paymentSearchQuery !== '') {
                $needle = mb_strtolower($paymentSearchQuery);
                $paymentSubmissions = array_values(array_filter($paymentSubmissions, function ($ps) use ($needle) {
                    $haystack = mb_strtolower(
                        ($ps['first_name']     ?? '') . ' ' .
                        ($ps['middle_name']    ?? '') . ' ' .
                        ($ps['surname']        ?? '') . ' ' .
                        ($ps['suffix']         ?? '') . ' ' .
                        ($ps['student_number'] ?? '') . ' ' .
                        ($ps['file_name']      ?? '') . ' ' .
                        ($ps['notes']          ?? '')
                    );
                    return mb_strpos($haystack, $needle) !== false;
                }));
            }

            $paymentCounts = $controller->getPaymentStatusCounts();
        }

        $allStudents = $controller->getAllStudentsWithSearch($searchQuery);
        if (!is_array($allStudents)) $allStudents = [];

        if (!empty($searchQuery)
            && count($allStudents) === 1
            && empty($studentId)
            && $view === 'students'
        ) {
            $redirectKey  = 'auto_select_' . md5($searchQuery);
            $lastRedirect = $_SESSION[$redirectKey] ?? 0;

            if ($lastRedirect < time() - 5) {
                $_SESSION[$redirectKey] = time();
                $autoSelectedId = (int) $allStudents[0]['student_id'];
                $redirectUrl = '?page=requirements&view=students&student_id=' . $autoSelectedId;
                header('Location: ' . $redirectUrl);
                exit;
            }
            unset($_SESSION[$redirectKey]);
        }

        $studentDetails      = null;
        $studentRequirements = [];
        $completionStatus    = null;

        if ($studentId) {
            $studentDetails = $controller->getStudentDetails($studentId);
            if (!$studentDetails) $studentId = null;
        }

        $categories = $controller->getAllCategories();
        if (!is_array($categories)) $categories = [];

        if ($view === 'payments' && isset($_GET['download'])) {
            $downloadId = (int) $_GET['download'];
            $file = $controller->getPaymentSubmissionFile($downloadId);

            if (!$file || empty($file['file_data'])) {
                http_response_code(404);
                echo 'File not found.';
                exit;
            }

            $mimeType     = $file['file_mime'] ?: 'application/octet-stream';
            $downloadName = $file['file_name'] ?: 'payment';
            $safeName     = preg_replace('/[\r\n"]/', '', $downloadName);

            header('Content-Type: ' . $mimeType);
            header('Content-Disposition: inline; filename="' . $safeName . '"');
            header('Content-Length: ' . strlen($file['file_data']));
            header('X-Content-Type-Options: nosniff');
            echo $file['file_data'];
            exit;
        }

        if ($studentId && $studentDetails) {
            // The student's admission type determines the checklist, not URL filters.
            $studentRequirements = $controller->getStudentRequirements($studentId);
            if (!is_array($studentRequirements)) $studentRequirements = [];

            $completionStatus = $controller->getCompletionStatus($studentId);
            if (!is_array($completionStatus)) {
                $completionStatus = ['submitted' => 0, 'total' => 0, 'percentage' => 0];
            }
        }

        $allRequirements = $controller->getAllRequirementsGrouped();
        if (!is_array($allRequirements)) $allRequirements = [];

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);
        if ($message !== '') {
            error_log("[requirements.php] flash: " . substr($message, 0, 200));
        }

        $pageTitle = 'Requirements Management';

        return [
            'view' => $view ?? null,
            'paymentCounts' => $paymentCounts ?? null,
            'message' => $message ?? null,
            'categories' => $categories ?? null,
            'allRequirements' => $allRequirements ?? null,
            'paymentSubmissions' => $paymentSubmissions ?? null,
            'paymentStatusFilter' => $paymentStatusFilter ?? null,
            'paymentSearchQuery' => $paymentSearchQuery ?? null,
            'allStudents' => $allStudents ?? null,
            'searchQuery' => $searchQuery ?? null,
            'studentId' => $studentId ?? null,
            'studentDetails' => $studentDetails ?? null,
            'completionStatus' => $completionStatus ?? null,
            'studentRequirements' => $studentRequirements ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
