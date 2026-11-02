<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/StudentController.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Section.php';
require_once __DIR__ . '/Enrollment.php';

/** Request handling and view data for students. */
final class StudentsPage extends EnrollmentPage
{
    protected const VIEW = 'students';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $controller = new StudentController();
        $enrollment = new Enrollment();
        $db         = Database::getInstance();

        // ============================================================
        // Filters
        // ============================================================
        $search        = $_GET['search']     ?? '';
        $courseFilter  = isset($_GET['course_id'])  ? (int) $_GET['course_id']  : '';
        $sectionFilter = isset($_GET['section_id']) ? (int) $_GET['section_id'] : '';
        $statusFilter  = $_GET['status']     ?? '';

        // ============================================================
        // Data
        // ============================================================
        $students = $controller->getStudents($search, $courseFilter, $sectionFilter, $statusFilter);
        if (!is_array($students)) $students = [];

        $stats   = $controller->getStudentStats();
        if (!is_array($stats)) $stats = [];

        $courses = $controller->getAllCourses();
        if (!is_array($courses)) $courses = [];

        $sections = $controller->getActiveSections();
        if (!is_array($sections)) $sections = [];

        // ============================================================
        // Prefetch subject counts for all rows in ONE query
        // (avoids N+1: previously one query per row)
        // ============================================================
        $studentIds = array_values(array_filter(
            array_map(fn($s) => (int) ($s['student_id'] ?? 0), $students),
            fn($id) => $id > 0
        ));

        $subjectCountsByStudent = [];
        if (!empty($studentIds)) {
            try {
                $placeholders = implode(',', array_fill(0, count($studentIds), '?'));
                $sql = "SELECT e.student_id,
                               COUNT(DISTINCT e.schedule_id) AS subject_count
                        FROM enr_enrollments e
                        JOIN enr_students s ON s.student_id = e.student_id
                        WHERE e.student_id IN ($placeholders)
                          AND e.enrollment_status = 'enrolled'
                          AND e.section_id = s.section_id
                        GROUP BY e.student_id";
                $stmt = $db->prepare($sql);
                $stmt->execute($studentIds);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $subjectCountsByStudent[(int) $row['student_id']] = (int) $row['subject_count'];
                }
            } catch (Exception $e) {
                error_log('students.php: subject count prefetch failed: ' . $e->getMessage());
            }
        }

        // ============================================================
        // CSV export
        // ============================================================
        if (isset($_GET['export']) && $_GET['export'] === 'csv') {
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="students_' . date('Y-m-d_His') . '.csv"');

            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM for Excel

            fputcsv($out, ['STUDENT LIST REPORT']);
            fputcsv($out, ['Generated:', date('l, F d, Y h:i A')]);
            fputcsv($out, ['']);
            fputcsv($out, [
                'Student #', 'Full Name', 'Course', 'Section', 'Year Level',
                'Admission Type', 'Status', 'Email', 'Contact',
                'Subjects', 'Enrolled At'
            ]);

            foreach ($students as $s) {
                $name = trim(
                    ($s['first_name'] ?? '')
                    . ' ' . ($s['middle_name'] ?? '')
                    . ' ' . ($s['surname'] ?? '')
                    . ' ' . ($s['suffix'] ?? '')
                );

                fputcsv($out, [
                    $s['student_number']     ?? 'N/A',
                    $name,
                    $s['course_code']        ?? 'N/A',
                    $s['section_code']       ?? 'Not Assigned',
                    $s['year_level']         ?? 'N/A',
                    ucfirst(str_replace('_', ' ', $s['admission_type'] ?? 'N/A')),
                    ucfirst(str_replace('_', ' ', $s['enrollment_status'] ?? 'Unknown')),
                    $s['email']              ?? 'N/A',
                    $s['contact_number']     ?? 'N/A',
                    $subjectCountsByStudent[(int) ($s['student_id'] ?? 0)] ?? 0,
                    !empty($s['enrolled_at']) ? date('M d, Y', strtotime($s['enrolled_at'])) : 'N/A'
                ]);
            }

            fclose($out);
            exit;
        }

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $badgeMap = [
            'freshmen'    => 'badge-freshmen',
            'transferee'  => 'badge-transferee',
            'returnee'    => 'badge-returnee',
            'senior_high' => 'badge-senior_high'
        ];

        $pageTitle = 'Students Management';

        return [
            'students' => $students ?? null,
            'search' => $search ?? null,
            'courseFilter' => $courseFilter ?? null,
            'sectionFilter' => $sectionFilter ?? null,
            'statusFilter' => $statusFilter ?? null,
            'message' => $message ?? null,
            'stats' => $stats ?? null,
            'courses' => $courses ?? null,
            'sections' => $sections ?? null,
            'badgeMap' => $badgeMap ?? null,
            'subjectCountsByStudent' => $subjectCountsByStudent ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
