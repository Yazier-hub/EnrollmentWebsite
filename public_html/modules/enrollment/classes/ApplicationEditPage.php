<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Course.php';

/** Request handling and view data for application-edit. */
final class ApplicationEditPage extends EnrollmentPage
{
    protected const VIEW = 'application-edit';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $application = new Application();
        $course      = new Course();

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

        $app = $application->getApplicationById($applicationId);

        if (!$app) {
            $_SESSION['message'] = '❌ Application not found.';
            header('Location: ?page=applications');
            exit;
        }

        if (($app['status'] ?? '') === 'converted') {
            $_SESSION['message'] = '❌ This application has already been converted to a student and cannot be edited.';
            header('Location: ?page=application-details&id=' . $applicationId);
            exit;
        }

        $courses = $course->findAll();

        // ============================================================
        // POST handling
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {

                case 'update':
                    try {
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
                            'course_id'            => !empty($_POST['course_id']) ? (int) $_POST['course_id'] : null
                        ];

                        if ($application->update($applicationId, $data)) {
                            $_SESSION['message'] = '✅ Application updated successfully!';
                            header('Location: ?page=application-details&id=' . $applicationId);
                            exit;
                        }
                        $error = '❌ Failed to update application.';
                    } catch (Exception $e) {
                        $error = '❌ Error: ' . $e->getMessage();
                    }
                    break;

                case 'delete':
                    if ($application->delete($applicationId)) {
                        $_SESSION['message'] = '✅ Application deleted successfully!';
                        header('Location: ?page=applications');
                        exit;
                    }
                    $error = '❌ Failed to delete application.';
                    break;
            }
        }

        // ============================================================
        // Post-processing
        // ============================================================
        $fullName = trim(
            ($app['first_name'] ?? '')
            . ' ' . ($app['middle_name'] ?? '')
            . ' ' . ($app['surname'] ?? '')
            . ' ' . ($app['suffix'] ?? '')
        );

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $pageTitle = 'Edit Application - ' . $fullName;

        return [
            'fullName' => $fullName ?? null,
            'app' => $app ?? null,
            'message' => $message ?? null,
            'error' => $error ?? null,
            'applicationId' => $applicationId ?? null,
            'courses' => $courses ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
