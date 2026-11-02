<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/Student.php';
require_once __DIR__ . '/StudentController.php';
require_once __DIR__ . '/Course.php';
require_once __DIR__ . '/Section.php';
require_once __DIR__ . '/Application.php';
require_once __DIR__ . '/Enrollment.php';

/** Request handling and view data for student-edit. */
final class StudentEditPage extends EnrollmentPage
{
    protected const VIEW = 'student-edit';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $controller  = new StudentController();
        $course      = new Course();
        $section     = new Section();
        $application = new Application();
        $enrollment  = new Enrollment();

        $studentId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
        if ($studentId <= 0) {
            $_SESSION['message'] = '❌ Invalid student ID.';
            header('Location: ?page=students');
            exit;
        }

        $student = $controller->getStudentById($studentId);
        if (!$student) {
            $_SESSION['message'] = '❌ Student not found.';
            header('Location: ?page=students');
            exit;
        }

        $enrollmentDetails = $enrollment->getEnrollmentByStudentId($studentId);
        $subjects          = $enrollment->getStudentCurrentSemesterEnrollments($studentId);
        if (!is_array($subjects)) $subjects = [];

        $courses  = $course->findAll();
        if (!is_array($courses)) $courses = [];

        $sections = $section->getAllSectionsWithDetails();
        if (!is_array($sections)) $sections = [];

        $activeSections = $sections;

        // ============================================================
        // POST handling — isang action lang: 'update_all'
        // ============================================================
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            switch ($_POST['action']) {

                case 'update_all':
                    try {
                        // ------- Update personal info sa enr_applicants -------
                        if (!empty($student['applicant_id'])) {
                            $personalFields = [
                                'first_name', 'middle_name', 'surname', 'suffix',
                                'sex', 'date_of_birth', 'place_of_birth', 'civil_status', 'religion',
                                'email', 'contact_number', 'facebook', 'messenger',
                                'address_barangay', 'address_city', 'address_province', 'address_complete',
                                'parent_full_name', 'parent_contact', 'parent_address',
                                'school_last_attended', 'year_graduated', 'how_hear',
                                'working_student', 'admission_type'
                            ];

                            $applicantData = [];
                            foreach ($personalFields as $field) {
                                if (array_key_exists($field, $_POST)) {
                                    $value = trim((string) $_POST[$field]);
                                    $applicantData[$field] = ($value === '') ? null : $value;
                                }
                            }

                            // Validate
                            if (empty($applicantData['first_name'])) {
                                throw new Exception('First name is required.');
                            }
                            if (empty($applicantData['surname'])) {
                                throw new Exception('Surname is required.');
                            }
                            if (empty($applicantData['email'])) {
                                throw new Exception('Email is required.');
                            }

                            $application->update((int) $student['applicant_id'], $applicantData);
                        }

                        // ------- Update academic info sa enr_students -------
                        $updateData = [
                            'course_id'         => !empty($_POST['course_id'])  ? (int) $_POST['course_id']  : null,
                            'section_id'        => !empty($_POST['section_id']) ? (int) $_POST['section_id'] : null,
                            'year_level'        => (int) ($_POST['year_level'] ?? 1),
                            'enrollment_status' => $_POST['enrollment_status'] ?? 'enrolled'
                        ];

                        $studentModel = new Student();
                        $result = $studentModel->update($studentId, $updateData);

                        if ($result) {
                            $_SESSION['message'] = '✅ Student updated successfully!';
                            header('Location: ?page=student-edit&id=' . $studentId);
                            exit;
                        }
                        $error = '❌ Failed to update student academic info.';

                    } catch (Exception $e) {
                        $error = '❌ Error: ' . $e->getMessage();
                    }
                    break;

                case 'delete':
                    if ($enrollmentDetails) {
                        $error = '❌ Cannot delete student with active enrollment. Please drop the student first.';
                        break;
                    }

                    $studentModel = new Student();
                    if ($studentModel->delete($studentId)) {
                        $_SESSION['message'] = '✅ Student deleted successfully!';
                        header('Location: ?page=students');
                        exit;
                    }
                    $error = '❌ Failed to delete student.';
                    break;
            }
        }

        // ============================================================
        // Post-processing
        // ============================================================
        $fullName = trim(
            ($student['first_name'] ?? '')
            . ' ' . ($student['middle_name'] ?? '')
            . ' ' . ($student['surname'] ?? '')
            . ' ' . ($student['suffix'] ?? '')
        );

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $pageTitle = 'Edit Student - ' . $fullName;

        $getAdmissionType  = $student['admission_type']  ?? 'freshmen';
        $getWorkingStudent = $student['working_student'] ?? 'No';
        $currentSex        = $student['sex']             ?? '';
        $currentCivil      = $student['civil_status']    ?? '';
        $currentHowHear    = $student['how_hear']        ?? '';

        return [
            'fullName' => $fullName ?? null,
            'student' => $student ?? null,
            'message' => $message ?? null,
            'error' => $error ?? null,
            'enrollmentDetails' => $enrollmentDetails ?? null,
            'studentId' => $studentId ?? null,
            'currentSex' => $currentSex ?? null,
            'currentCivil' => $currentCivil ?? null,
            'getAdmissionType' => $getAdmissionType ?? null,
            'getWorkingStudent' => $getWorkingStudent ?? null,
            'currentHowHear' => $currentHowHear ?? null,
            'courses' => $courses ?? null,
            'activeSections' => $activeSections ?? null,
            'subjects' => $subjects ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
