<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Section.php';
require_once __DIR__ . '/Requirement.php';
require_once __DIR__ . '/EnrollmentReport.php';
require_once __DIR__ . '/ReportExporter.php';

/** Request handling and view data for dashboard-overview. */
final class DashboardOverviewPage extends EnrollmentPage
{
    protected const VIEW = 'dashboard-overview';

    protected function loadData(): array
    {
        if (isset($_GET['export'])) {
            $exporter = new ReportExporter();

            switch ($_GET['export']) {
                case 'analysis': $exporter->exportAnalysisReport(); break;
                case 'summary':  $exporter->exportSummary();        break;
                case 'students': $exporter->exportStudentList();    break;
                case 'archive':  $exporter->exportArchiveReport();  break;
            }
            exit;
        }

        // ============================================================
        // INIT MODELS
        // ============================================================
        $enrollment  = new Enrollment();
        $application = new Application();
        $student     = new Student();
        $course      = new Course();
        $section     = new Section();
        $requirement = new Requirement();
        $report      = new EnrollmentReport();
        $db          = Database::getInstance();

        // ============================================================
        // STATISTICS
        // ============================================================
        $totalStudents = $student->getStudentCount();

        try {
            $stmt = $db->prepare("SELECT COUNT(*) AS count FROM enr_enrollments WHERE enrollment_status = 'enrolled'");
            $stmt->execute();
            $totalSubjectEnrollments = (int) ($stmt->fetch()['count'] ?? 0);
        } catch (Exception $e) {
            $totalSubjectEnrollments = 0;
        }

        try {
            $stmt = $db->prepare("SELECT COUNT(DISTINCT student_id) AS count FROM enr_enrollments WHERE enrollment_status = 'enrolled'");
            $stmt->execute();
            $activeEnrollments = (int) ($stmt->fetch()['count'] ?? 0);
        } catch (Exception $e) {
            $activeEnrollments = 0;
        }

        try {
            $stmt = $db->prepare("SELECT COUNT(*) AS count FROM enr_students WHERE archived_at IS NOT NULL AND status = 'inactive'");
            $stmt->execute();
            $archivedCount = (int) ($stmt->fetch()['count'] ?? 0);
        } catch (Exception $e) {
            $archivedCount = 0;
        }

        $pendingApps = $application->getPendingApplications();
        if (!is_array($pendingApps)) $pendingApps = [];
        $pendingCount = count($pendingApps);

        $allApplications = $application->getAllApplications();
        if (!is_array($allApplications)) $allApplications = [];
        $totalApplications = count($allApplications);

        $sectionStats = $section->getSectionStats();
        if (!is_array($sectionStats)) {
            $sectionStats = ['total_sections' => 0, 'available_slots' => 0];
        }

        // ============================================================
        // REPORT DATA
        // ============================================================
        $reportSummary = $report->getSummaryStats();
        if (!is_array($reportSummary)) $reportSummary = [];

        $enrollmentByCourse = $report->getEnrollmentByCourse();
        if (!is_array($enrollmentByCourse)) $enrollmentByCourse = [];

        $enrollmentByYear = $report->getEnrollmentByYearLevel();
        if (!is_array($enrollmentByYear)) $enrollmentByYear = [];

        $monthlyTrend = $report->getMonthlyTrend();
        if (!is_array($monthlyTrend)) $monthlyTrend = [];

        $predictive = $report->getPredictiveAnalytics();
        if (!is_array($predictive)) $predictive = [];

        $recentEnrollments = $report->getRecentEnrollments(5);
        if (!is_array($recentEnrollments)) $recentEnrollments = [];

        $studentsWithIncomplete = $report->getStudentsWithIncompleteRequirements();
        if (!is_array($studentsWithIncomplete)) $studentsWithIncomplete = [];

        // ============================================================
        // CHART DATA
        // ============================================================
        $months      = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $currentYear = (int) date('Y');

        $enrollmentTrend  = array_fill(0, 12, 0);
        $applicationTrend = array_fill(0, 12, 0);

        foreach ($monthlyTrend as $row) {
            $monthName = $row['month'] ?? '';
            $idx = array_search($monthName, $months, true);

            if ($idx !== false) {
                $enrollmentTrend[$idx]  = (int) ($row['enrollments']  ?? 0);
                $applicationTrend[$idx] = (int) ($row['applications'] ?? 0);
            }
        }

        // ============================================================
        // PREDICTIVE DATA
        // ============================================================
        $predictedNextMonth   = (int)   ($predictive['predicted_next_month']   ?? 0);
        $predictedNextQuarter = (int)   ($predictive['predicted_next_quarter'] ?? 0);
        $avgGrowth            = (float) ($predictive['avg_growth']             ?? 0);
        $conversionRate       = (float) ($reportSummary['conversion_rate']     ?? 0);

        $currentDateTime = date('l, F d, Y h:i A');
        $pageTitle       = 'Dashboard Overview';
        $clientData = [
            'months' => $months,
            'enrollmentTrend' => $enrollmentTrend,
            'currentYear' => $currentYear,
            'enrollmentByCourse' => $enrollmentByCourse,
            'applicationTrend' => $applicationTrend,
            'enrollmentByYear' => $enrollmentByYear,
        ];

        return [
            'clientData' => $clientData ?? null,
            'currentDateTime' => $currentDateTime ?? null,
            'totalStudents' => $totalStudents ?? null,
            'activeEnrollments' => $activeEnrollments ?? null,
            'totalSubjectEnrollments' => $totalSubjectEnrollments ?? null,
            'pendingCount' => $pendingCount ?? null,
            'sectionStats' => $sectionStats ?? null,
            'archivedCount' => $archivedCount ?? null,
            'currentYear' => $currentYear ?? null,
            'predictedNextMonth' => $predictedNextMonth ?? null,
            'avgGrowth' => $avgGrowth ?? null,
            'predictedNextQuarter' => $predictedNextQuarter ?? null,
            'conversionRate' => $conversionRate ?? null,
            'studentsWithIncomplete' => $studentsWithIncomplete ?? null,
            'recentEnrollments' => $recentEnrollments ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
