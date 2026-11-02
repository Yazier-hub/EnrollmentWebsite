<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/StudentController.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/Requirement.php';
require_once __DIR__ . '/Application.php';

/** Request handling and view data for student-details. */
final class StudentDetailsPage extends EnrollmentPage
{
    protected const VIEW = 'student-details';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $controller = new StudentController();

        $studentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($studentId <= 0) {
            header('Location: ?page=students');
            exit;
        }

        $student = $controller->getStudentById($studentId);
        if (!$student) {
            $_SESSION['message'] = '❌ Student not found.';
            header('Location: ?page=students');
            exit;
        }

        $enrollment        = new Enrollment();
        $enrollmentDetails = $enrollment->getEnrollmentByStudentId($studentId);

        $subjects = $enrollment->getStudentCurrentSemesterEnrollments($studentId);
        if (!is_array($subjects)) $subjects = [];

        $requirement         = new Requirement();
        $requirements        = $requirement->getStudentRequirementsWithStatus($studentId);
        if (!is_array($requirements)) $requirements = [];
        $completionStatus    = $requirement->getRequirementCompletionStatus($studentId);
        $mandatoryCompleted  = $requirement->hasCompletedMandatoryRequirements($studentId);

        $fullName = trim(
            ($student['first_name'] ?? '')
            . ' ' . ($student['middle_name'] ?? '')
            . ' ' . ($student['surname'] ?? '')
            . ' ' . ($student['suffix'] ?? '')
        );

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        // ============================================================
        // POST: Archive / Restore actions
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $actionStudentId = (int) ($_POST['student_id'] ?? 0);

            if ($actionStudentId > 0) {
                switch ($_POST['action']) {
                    case 'archive':
                        $reason     = trim($_POST['archive_reason'] ?? 'Dropped');
                        $schoolYear = $_POST['school_year'] ?? null;
                        $_SESSION['message'] = $enrollment->archiveStudent($actionStudentId, $reason, $schoolYear)
                            ? '✅ Student archived successfully! Pwede pang i-restore.'
                            : '❌ Failed to archive student.';
                        header('Location: ?page=student-details&id=' . $studentId);
                        exit;

                    case 'restore':
                        $_SESSION['message'] = $enrollment->restoreStudent($actionStudentId)
                            ? '✅ Student restored successfully!'
                            : '❌ Failed to restore student.';
                        header('Location: ?page=student-details&id=' . $studentId);
                        exit;

                    case 'drop': // legacy — kept for backward compat
                        $schoolYear = $_POST['school_year'] ?? null;
                        $_SESSION['message'] = $enrollment->dropStudent($actionStudentId, $schoolYear)
                            ? '✅ Student dropped successfully!'
                            : '❌ Failed to drop student.';
                        header('Location: ?page=student-details&id=' . $studentId);
                        exit;
                }
            }
        }

        // ============================================================
        // Badges / labels
        // ============================================================
        $admissionType = $student['admission_type'] ?? 'freshmen';
        $badgeMap = [
            'freshmen'    => 'badge-freshmen',
            'transferee'  => 'badge-transferee',
            'returnee'    => 'badge-returnee',
            'senior_high' => 'badge-senior_high'
        ];
        $admissionBadgeClass = $badgeMap[$admissionType] ?? 'badge-default';
        $admissionLabel      = ucfirst(str_replace('_', ' ', $admissionType));

        $currentStatus = $student['enrollment_status'] ?? 'enrolled';
        $statusMap = [
            'enrolled'  => 'badge-enrolled',
            'on_leave'  => 'badge-on_leave',
            'graduated' => 'badge-graduated',
            'dropped'   => 'badge-dropped'
        ];
        $statusBadgeClass = $statusMap[$currentStatus] ?? 'badge-enrolled';
        $statusLabel      = ucfirst(str_replace('_', ' ', $currentStatus));

        $subjectCount = count($subjects);
        $pageTitle    = 'Student Details - ' . $fullName;

        return [
            'fullName' => $fullName ?? null,
            'student' => $student ?? null,
            'message' => $message ?? null,
            'statusBadgeClass' => $statusBadgeClass ?? null,
            'statusLabel' => $statusLabel ?? null,
            'admissionBadgeClass' => $admissionBadgeClass ?? null,
            'admissionLabel' => $admissionLabel ?? null,
            'enrollmentDetails' => $enrollmentDetails ?? null,
            'subjectCount' => $subjectCount ?? null,
            'completionStatus' => $completionStatus ?? null,
            'mandatoryCompleted' => $mandatoryCompleted ?? null,
            'subjects' => $subjects ?? null,
            'requirements' => $requirements ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
