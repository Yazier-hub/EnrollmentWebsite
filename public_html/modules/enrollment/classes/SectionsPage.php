<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Section.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/SectionController.php';

/** Request handling and view data for sections. */
final class SectionsPage extends EnrollmentPage
{
    protected const VIEW = 'sections';

    protected function loadData(): array
    {
        $controller = new SectionController();
        $controller->handleRequest();

        $db = Database::getInstance();

        // ============================================================
        // Load sections with COMPUTED capacity (no cc_sections columns)
        // ============================================================
        $sections = $controller->getSectionsWithCapacityStatus();
        if (!is_array($sections)) $sections = [];

        // Compute actual enrolled-student count per section in ONE query,
        // then attach it as `current_students` for the UI.
        $sectionIds = array_values(array_filter(
            array_map(fn($s) => (int) ($s['id'] ?? 0), $sections),
            fn($id) => $id > 0
        ));

        $countBySection = [];
        if (!empty($sectionIds)) {
            try {
                $ph  = implode(',', array_fill(0, count($sectionIds), '?'));
                $sql = "SELECT section_id, COUNT(DISTINCT student_id) AS cnt
                        FROM enr_enrollments
                        WHERE section_id IN ($ph)
                          AND enrollment_status = 'enrolled'
                        GROUP BY section_id";
                $stmt = $db->prepare($sql);
                $stmt->execute($sectionIds);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $countBySection[(int) $row['section_id']] = (int) $row['cnt'];
                }
            } catch (Exception $e) {
                error_log('sections.php: enrollment count fetch failed: ' . $e->getMessage());
            }
        }

        // Default per-section capacity (no DB column)
        $DEFAULT_CAPACITY = 40;

        foreach ($sections as &$sec) {
            $sid = (int) ($sec['id'] ?? 0);
            $sec['current_students'] = $countBySection[$sid] ?? 0;
            $sec['max_students']     = $DEFAULT_CAPACITY;
        }
        unset($sec);

        // ============================================================
        // Courses / stats
        // ============================================================
        $courses = $controller->getCourses();
        if (!is_array($courses)) $courses = [];

        $stats = $controller->getSectionStats();
        if (!is_array($stats)) $stats = [];

        // Compute total enrolled students from $sections (avoid relying on
        // Section::getSectionStats() which uses cc_sections capacity columns)
        $totalStudents = 0;
        foreach ($sections as $s) {
            $totalStudents += (int) ($s['current_students'] ?? 0);
        }
        $stats['total_students'] = $totalStudents;

        // Full sections = those at or above default capacity
        $fullSections = array_values(array_filter($sections, function ($s) {
            return (int) $s['current_students'] >= (int) $s['max_students'];
        }));

        // ============================================================
        // Filters
        // ============================================================
        $statusFilter = $_GET['status']     ?? 'all';
        $courseFilter = isset($_GET['course_id']) ? (int) $_GET['course_id'] : 0;

        $filteredSections = $sections;

        if ($statusFilter === 'available') {
            $filteredSections = array_filter($filteredSections, function ($s) {
                return (int) $s['current_students'] < (int) $s['max_students'];
            });
        } elseif ($statusFilter === 'full') {
            $filteredSections = array_filter($filteredSections, function ($s) {
                return (int) $s['current_students'] >= (int) $s['max_students'];
            });
        }

        if ($courseFilter > 0) {
            $filteredSections = array_filter($filteredSections, function ($s) use ($courseFilter) {
                return (int) ($s['program_id'] ?? 0) === $courseFilter;
            });
        }

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $pageTitle = 'Sections Management';

        return [
            'message' => $message ?? null,
            'stats' => $stats ?? null,
            'sections' => $sections ?? null,
            'DEFAULT_CAPACITY' => $DEFAULT_CAPACITY ?? null,
            'fullSections' => $fullSections ?? null,
            'statusFilter' => $statusFilter ?? null,
            'courses' => $courses ?? null,
            'courseFilter' => $courseFilter ?? null,
            'filteredSections' => $filteredSections ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
