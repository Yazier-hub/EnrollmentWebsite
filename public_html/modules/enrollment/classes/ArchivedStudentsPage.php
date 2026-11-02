<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/EnrollmentController.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Section.php';

/** Request handling and view data for archived-students. */
final class ArchivedStudentsPage extends EnrollmentPage
{
    protected const VIEW = 'archived-students';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $basePath = dirname(__DIR__);

        $controller      = new EnrollmentController();
        $enrollmentModel = new Enrollment();
        $courseModel     = new Course();
        $sectionModel    = new Section();

        // ============================================================
        // POST (restore, etc.)
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $controller->handlePostRequest();
        }

        // ============================================================
        // Filters
        // ============================================================
        $search       = $_GET['search']     ?? '';
        $courseFilter = isset($_GET['course_id'])  ? (int) $_GET['course_id']  : '';
        $yearFilter   = isset($_GET['year_level']) ? (int) $_GET['year_level'] : '';
        $reasonFilter = $_GET['reason']     ?? '';

        // ============================================================
        // Data
        // ============================================================
        $archivedStudents = $enrollmentModel->getArchivedStudents($courseFilter, $yearFilter);
        if (!is_array($archivedStudents)) $archivedStudents = [];

        // Search filter
        if ($search !== '') {
            $searchLower = strtolower($search);
            $archivedStudents = array_filter($archivedStudents, function ($student) use ($searchLower) {
                $fullName = strtolower(trim(
                    ($student['first_name'] ?? '')
                    . ' ' . ($student['middle_name'] ?? '')
                    . ' ' . ($student['surname'] ?? '')
                    . ' ' . ($student['suffix'] ?? '')
                ));
                $studentNumber = strtolower($student['student_number'] ?? '');
                $email         = strtolower($student['email'] ?? '');

                return strpos($fullName,     $searchLower) !== false
                    || strpos($studentNumber, $searchLower) !== false
                    || strpos($email,         $searchLower) !== false;
            });
        }

        // Reason filter
        if ($reasonFilter !== '') {
            $archivedStudents = array_filter($archivedStudents, function ($student) use ($reasonFilter) {
                return ($student['archive_reason'] ?? '') === $reasonFilter;
            });
        }

        $archivedStudents = array_values($archivedStudents);

        $courses = $courseModel->findAll();
        if (!is_array($courses)) $courses = [];

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // ============================================================
        // Stats
        // ============================================================
        $totalArchived = count($archivedStudents);
        $reasonCounts  = [
            'Dropped'     => 0,
            'Transferred' => 0,
            'LOA'         => 0,
            'Graduated'   => 0,
            'Other'       => 0
        ];

        foreach ($archivedStudents as $s) {
            $reason = $s['archive_reason'] ?? 'Other';
            if (isset($reasonCounts[$reason])) {
                $reasonCounts[$reason]++;
            } else {
                $reasonCounts['Other']++;
            }
        }

        $pageTitle = 'Archived Students';

        return [
            'message' => $message ?? null,
            'totalArchived' => $totalArchived ?? null,
            'reasonCounts' => $reasonCounts ?? null,
            'search' => $search ?? null,
            'courses' => $courses ?? null,
            'courseFilter' => $courseFilter ?? null,
            'yearFilter' => $yearFilter ?? null,
            'reasonFilter' => $reasonFilter ?? null,
            'archivedStudents' => $archivedStudents ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
