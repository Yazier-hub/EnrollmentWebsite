<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Enrollment.php';
require_once __DIR__ . '/Section.php';
require_once __DIR__ . '/Requirement.php';

/** Request handling and view data for application-details. */
final class ApplicationDetailsPage extends EnrollmentPage
{
    protected const VIEW = 'application-details';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        error_reporting(E_ALL);
        ini_set('display_errors', 0);

        $application = new Application();
        $student     = new Student();
        $course      = new Course();
        $enrollment  = new Enrollment();
        $section     = new Section();
        $requirement = new Requirement();

        // ============================================================
        // Resolve application ID (GET on load, POST as fallback)
        // ============================================================
        $applicationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($applicationId <= 0 && isset($_POST['applicant_id'])) {
            $applicationId = (int) $_POST['applicant_id'];
        }

        if ($applicationId <= 0) {
            $_SESSION['message'] = '❌ Invalid application ID.';
            header('Location: ?page=applications');
            exit;
        }

        $app = $application->getApplicationWithStudent($applicationId);
        if (!$app) {
            $_SESSION['message'] = '❌ Application not found.';
            header('Location: ?page=applications');
            exit;
        }

        $fullName = trim(
            ($app['first_name'] ?? '')
            . ' ' . ($app['middle_name'] ?? '')
            . ' ' . ($app['surname'] ?? '')
            . ' ' . ($app['suffix'] ?? '')
        );

        $courses           = $course->findAll();
        $availableSections = [];

        if (!empty($app['course_id'])) {
            // NOTE: Section::getAvailableSections() returns rows keyed with `id`,
            // not `section_id`. We normalize below.
            $raw = $section->getAvailableSections((int) $app['course_id']);
            if (is_array($raw)) {
                foreach ($raw as $sec) {
                    $sec['section_id'] = $sec['id'] ?? $sec['section_id'] ?? null;
                    $availableSections[] = $sec;
                }
            }
        }

        // ============================================================
        // POST handling
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {

                case 'update':
                    $data = [
                        'surname'              => trim($_POST['surname'] ?? ''),
                        'first_name'           => trim($_POST['first_name'] ?? ''),
                        'middle_name'          => trim($_POST['middle_name'] ?? ''),
                        'suffix'               => trim($_POST['suffix'] ?? ''),
                        'admission_type'       => $_POST['admission_type'] ?? 'freshmen',
                        'working_student'      => $_POST['working_student'] ?? 'No',
                        'sex'                  => $_POST['sex'] ?? '',
                        'address_barangay'     => trim($_POST['address_barangay'] ?? ''),
                        'address_city'         => trim($_POST['address_city'] ?? ''),
                        'address_province'     => trim($_POST['address_province'] ?? ''),
                        'address_complete'     => trim($_POST['address_complete'] ?? ''),
                        'school_last_attended' => trim($_POST['school_last_attended'] ?? ''),
                        'year_graduated'       => !empty($_POST['year_graduated']) ? (int) $_POST['year_graduated'] : null,
                        'how_hear'             => $_POST['how_hear'] ?? '',
                        'email'                => trim($_POST['email'] ?? ''),
                        'date_of_birth'        => $_POST['date_of_birth'] ?? null,
                        'place_of_birth'       => trim($_POST['place_of_birth'] ?? ''),
                        'age'                  => !empty($_POST['age']) ? (int) $_POST['age'] : null,
                        'civil_status'         => $_POST['civil_status'] ?? 'Single',
                        'religion'             => trim($_POST['religion'] ?? ''),
                        'contact_number'       => trim($_POST['contact_number'] ?? ''),
                        'facebook'             => trim($_POST['facebook'] ?? ''),
                        'messenger'            => trim($_POST['messenger'] ?? ''),
                        'address'              => trim($_POST['address'] ?? ''),
                        'parent_full_name'     => trim($_POST['parent_full_name'] ?? ''),
                        'parent_contact'       => trim($_POST['parent_contact'] ?? ''),
                        'parent_address'       => trim($_POST['parent_address'] ?? ''),
                        'course_id'            => !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null,
                        'preferred_section_id' => !empty($_POST['preferred_section_id']) ? (int) $_POST['preferred_section_id'] : null
                    ];

                    if ($application->update($applicationId, $data)) {
                        $_SESSION['message'] = '✅ Application updated successfully!';
                        header('Location: ?page=application-details&id=' . $applicationId);
                        exit;
                    }
                    $error = '❌ Failed to update application.';
                    break;

                case 'delete':
                    if ($application->delete($applicationId)) {
                        $_SESSION['message'] = '✅ Application deleted successfully!';
                        header('Location: ?page=applications');
                        exit;
                    }
                    $error = '❌ Failed to delete application.';
                    break;

                case 'reject':
                    $notes = trim($_POST['notes'] ?? 'Application rejected');
                    if ($application->updateStatus($applicationId, 'rejected')) {
                        if ($notes !== '') {
                            $application->update($applicationId, ['notes' => $notes]);
                        }
                        $_SESSION['message'] = '✅ Application rejected successfully!';
                        header('Location: ?page=application-details&id=' . $applicationId);
                        exit;
                    }
                    $error = '❌ Failed to reject application.';
                    break;

                case 'convert':
                    if (empty($app['course_id'])) {
                        $error = '❌ Please assign a course to this application first.';
                        break;
                    }

                    if (empty($_POST['section_id'])) {
                        $error = '❌ Please select a section for enrollment.';
                        break;
                    }

                    $sectionId  = (int) $_POST['section_id'];
                    $schoolYear = !empty($_POST['school_year'])
                        ? $_POST['school_year']
                        : (date('Y') . '-' . (date('Y') + 1));

                    // NOTE: schedule_ids comes from the section's offered schedules.
                    // If your convert form does not yet let the user pick schedules,
                    // we pass an empty array — the applicant will be converted but
                    // not enrolled in any subjects. Add schedule selection to enable
                    // real enrollment.
                    $scheduleIds = [];
                    if (isset($_POST['schedule_ids']) && !empty($_POST['schedule_ids'])) {
                        $scheduleIds = array_map('intval', explode(',', $_POST['schedule_ids']));
                    }
                    if (isset($_POST['schedule_ids_array']) && is_array($_POST['schedule_ids_array'])) {
                        $scheduleIds = array_merge(
                            $scheduleIds,
                            array_map('intval', $_POST['schedule_ids_array'])
                        );
                    }
                    $scheduleIds = array_values(array_unique(array_filter($scheduleIds, fn($id) => $id > 0)));

                    $result = $application->convertToStudentAndEnroll(
                        $applicationId,
                        $sectionId,
                        $schoolYear,
                        $scheduleIds
                    );

                    if (!empty($result['success'])) {
                        // Save checked requirements (if any)
                        $checked = $_POST['requirements'] ?? [];
                        if (is_array($checked) && !empty($checked) && !empty($result['student_id'])) {
                            $reqNotes = $_POST['req_notes'] ?? [];
                            foreach ($checked as $reqId => $submitted) {
                                $isSubmitted = ($submitted == '1' || $submitted === true || $submitted === 'on');
                                $note        = isset($reqNotes[$reqId]) ? trim($reqNotes[$reqId]) : null;
                                $requirement->updateOrCreateRequirementStatus(
                                    (int) $result['student_id'],
                                    (int) $reqId,
                                    $isSubmitted ? 1 : 0,
                                    $note
                                );
                            }
                        }

                        $_SESSION['message'] = '✅ Student created successfully! Student Number: '
                            . ($result['student_number'] ?? 'N/A');
                        if (!empty($result['enrollment_count'])) {
                            $_SESSION['message'] .= ' Enrolled in '
                                . (int) $result['enrollment_count'] . ' subject(s).';
                        }
                        header('Location: ?page=student-details&id=' . (int) $result['student_id']);
                        exit;
                    }

                    $error = '❌ Failed to convert: ' . ($result['message'] ?? 'Unknown error');
                    break;
            }
        }

        // ============================================================
        // Post-processing
        // ============================================================
        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $requirements = [];
        if (!empty($app['admission_type'])) {
            $requirements = $requirement->getRequirementsByCategory($app['admission_type']);
            if (!is_array($requirements)) {
                $requirements = [];
            }
        }

        $pageTitle = 'Application Details - ' . $fullName;

        return [
            'fullName' => $fullName ?? null,
            'app' => $app ?? null,
            'message' => $message ?? null,
            'error' => $error ?? null,
            'availableSections' => $availableSections ?? null,
            'requirements' => $requirements ?? null,
            'courses' => $courses ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
