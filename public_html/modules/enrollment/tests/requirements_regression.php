<?php
/**
 * Run: php tests/requirements_regression.php
 * Uses a randomly named, disposable MySQL database on the configured local server.
 * The application's database and student records are never changed.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

set_include_path(dirname(__DIR__) . '/classes' . PATH_SEPARATOR . get_include_path());
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Requirement.php';
require_once __DIR__ . '/../classes/RequirementController.php';
require_once __DIR__ . '/../classes/EnrollmentReport.php';

$database = Database::getInstance();
$pdo = $database->getConnection();
$originalDatabase = $pdo->query('SELECT DATABASE()')->fetchColumn();
$testDatabase = 'enrollment_requirements_test_' . bin2hex(random_bytes(8));
$checks = 0;
$created = false;

function check($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}

try {
    $pdo->exec("CREATE DATABASE `$testDatabase` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    $created = true;
    $pdo->exec("USE `$testDatabase`");
    check($pdo->query('SELECT DATABASE()')->fetchColumn() === $testDatabase, 'Test database isolation failed');

    $tables = [
        "CREATE TABLE enr_requirements (
            requirement_id INT PRIMARY KEY AUTO_INCREMENT,
            requirement_name VARCHAR(100) NOT NULL,
            requirement_category ENUM('freshmen','transferee','continuing') NOT NULL,
            is_mandatory TINYINT DEFAULT 1,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB",
        "CREATE TABLE enr_student_requirements (
            id INT PRIMARY KEY AUTO_INCREMENT, applicant_id INT NOT NULL, requirement_id INT NOT NULL,
            file_data LONGBLOB NULL, file_name VARCHAR(255) NULL, custom_name VARCHAR(255) NULL,
            file_mime VARCHAR(100) NULL, is_submitted TINYINT NULL DEFAULT 0,
            submitted_date DATETIME NULL,
            status ENUM('pending','verified','rejected') NOT NULL DEFAULT 'pending', notes TEXT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX (applicant_id), INDEX (requirement_id),
            FOREIGN KEY (requirement_id) REFERENCES enr_requirements(requirement_id) ON DELETE CASCADE
        ) ENGINE=InnoDB",
        "CREATE TABLE enr_applicants (
            applicant_id INT PRIMARY KEY, admission_type VARCHAR(30),
            first_name VARCHAR(50), middle_name VARCHAR(50), surname VARCHAR(50), suffix VARCHAR(10)
        ) ENGINE=InnoDB",
        "CREATE TABLE enr_students (
            student_id INT PRIMARY KEY, applicant_id INT, student_number VARCHAR(20), course_id INT,
            section_id INT, enrollment_status VARCHAR(20), followup_date DATE,
            followup_notes TEXT, followup_status VARCHAR(20)
        ) ENGINE=InnoDB",
        'CREATE TABLE rgr_courses (id INT PRIMARY KEY, code VARCHAR(20), name VARCHAR(100)) ENGINE=InnoDB',
        'CREATE TABLE cc_sections (id INT PRIMARY KEY, section_code VARCHAR(20), grade_level VARCHAR(20)) ENGINE=InnoDB',
        'CREATE TABLE enr_enrollments (student_id INT, schedule_id INT, enrollment_status VARCHAR(20)) ENGINE=InnoDB'
    ];
    foreach ($tables as $sql) $pdo->exec($sql);
    $pdo->exec("INSERT INTO enr_requirements (requirement_id, requirement_name, requirement_category, is_mandatory) VALUES
        (1, 'Birth Certificate', 'freshmen', 1), (2, 'Report Card', 'freshmen', 1),
        (3, 'Photo', 'freshmen', 0), (4, 'Clearance', 'continuing', 1), (5, 'Transcript', 'transferee', 1)");
    $pdo->exec("INSERT INTO enr_applicants VALUES
        (101, 'freshmen', 'First', '', 'Student', ''), (102, 'freshmen', 'Second', '', 'Student', ''),
        (103, 'returnee', 'Third', '', 'Student', ''), (104, 'senior_high', 'Fourth', '', 'Student', '')");
    $pdo->exec("INSERT INTO enr_students VALUES
        (1, 101, 'S001', NULL, NULL, 'enrolled', CURDATE(), '', 'pending'),
        (2, 102, 'S002', NULL, NULL, 'enrolled', CURDATE(), '', 'pending'),
        (3, 103, 'S003', NULL, NULL, 'enrolled', NULL, '', 'done'),
        (4, 104, 'S004', NULL, NULL, 'enrolled', NULL, '', 'done')");
    $blob = "\x00PDF\xFF\x01";
    $insert = $pdo->prepare("INSERT INTO enr_student_requirements
        (applicant_id, requirement_id, file_data, file_name, custom_name, file_mime, is_submitted, submitted_date, notes)
        VALUES (101, 1, ?, 'birth.pdf', 'Original Certificate', 'application/pdf', 1, '2025-01-02 03:04:05', 'Uploaded')");
    $insert->execute([$blob]);

    $model = new Requirement();
    $rows = $model->getStudentRequirements(1);
    check(count($rows) === 3, 'Checklist must include missing submissions');
    $byId = array_column($rows, null, 'requirement_id');
    check((int) $byId[1]['is_submitted'] === 1, 'Must resolve student ID to applicant ID');
    check($byId[1]['status'] === 'pending', 'Uploaded requirement must remain pending until reviewed');
    check($byId[2]['status'] === 'pending', 'Missing submission must display pending');
    check($byId[1]['file_name'] === 'birth.pdf' && $byId[1]['has_file'], 'Attachment metadata missing');
    check(!array_key_exists('file_data', $byId[1]), 'Checklist must not include binary data');
    check(json_encode($rows) !== false, 'Checklist must remain JSON encodable');
    check($model->getRequirementCompletionStatus(1)['total'] === 3, 'Incorrect completion total');
    check($model->getRequirementCompletionStatus(1)['submitted'] === 1, 'Incorrect submitted count');
    check(!$model->hasCompletedMandatoryRequirements(1), 'Missing mandatory submission must block completion');
    check($model->getStudentRequirements(999) === [], 'Unknown student must not have a checklist');

    check($model->initializeStudentRequirements(1, 101) === 2, 'Initialize only missing rows');
    check($model->initializeStudentRequirements(1, 101) === 0, 'Repeated initialization created duplicates');
    check($model->initializeStudentRequirements(1, 102) === 0, 'Mismatched applicant accepted');
    check($model->getStudentRequirementStatus(1, 1)['file_data'] === $blob, 'Initialization lost upload');
    check($model->updateOrCreateRequirementStatus(1, 1, 1, 'Checked'), 'Status update failed');
    $file = $model->getStudentRequirementStatus(1, 1);
    check($file['submitted_date'] === '2025-01-02 03:04:05', 'Repeated save changed original submission date');
    check($file['custom_name'] === 'Original Certificate' && $file['file_data'] === $blob, 'Status save changed file');
    check($model->updateOrCreateRequirementStatus(1, 1, 0, ''), 'Unchecked status failed');
    $file = $model->getStudentRequirementStatus(1, 1);
    check((int) $file['is_submitted'] === 0 && $file['submitted_date'] === null && $file['notes'] === null, 'Unchecked status not cleared');
    check($file['file_data'] === $blob, 'Unchecking must preserve attachments');
    check($model->updateOrCreateRequirementStatus(1, 1, 1), 'Re-submission failed');
    check($model->updateOrCreateRequirementStatus(1, 2, 1), 'Second requirement save failed');
    check($model->hasCompletedMandatoryRequirements(1), 'Mandatory completion is wrong');
    check($model->getStudentProgressByCategory(1)['mandatory_submitted'] === 2, 'Mandatory progress is wrong');
    check($model->getRequirementCompletionStatus(2)['submitted'] === 0, 'Saved another student\'s requirements');
    check(!$model->updateOrCreateRequirementStatus(999, 1, 1), 'Unknown student accepted');
    check(!$model->updateOrCreateRequirementStatus(1, 999, 1), 'Unknown requirement accepted');
    check(!$pdo->inTransaction(), 'Failed save leaked a transaction');
    check($model->initializeStudentRequirements(3, 103) === 1, 'Returnee category mapping failed');
    check($model->getStudentRequirements(3)[0]['requirement_category'] === 'continuing', 'Wrong returnee checklist');
    check(count($model->getStudentRequirements(4)) === 3, 'Senior high category mapping failed');
    check($model->getRequirementsByAdmissionType('continuing')[0]['requirement_category'] === 'continuing', 'Continuing students received freshmen requirements');
    check($model->getRequirementsByCategory('returnee')[0]['requirement_category'] === 'continuing', 'Category alias was not normalized');
    check($model->getRequirementCompletionStatus(1, 'returnee')['total'] === 1, 'Filtered summary does not match checklist');

    // No composite unique index: repeated saves and pre-existing duplicates still work.
    check($model->updateOrCreateRequirementStatus(2, 1, 1), 'New status insert failed');
    check($model->updateOrCreateRequirementStatus(2, 1, 1), 'Repeated status save failed');
    check((int) $pdo->query('SELECT COUNT(*) FROM enr_student_requirements WHERE applicant_id = 102 AND requirement_id = 1')->fetchColumn() === 1, 'Repeated save duplicated a submission');
    $pdo->exec('INSERT INTO enr_student_requirements (applicant_id, requirement_id, is_submitted) VALUES (102, 1, 0)');
    check($model->getRequirementCompletionStatus(2)['total'] === 3, 'Duplicate rows inflated total');
    check($model->getRequirementCompletionStatus(2)['submitted'] === 0, 'Latest submission status was ignored');
    check($model->updateOrCreateRequirementStatus(2, 1, 1), 'Latest duplicate update failed');
    check($model->getRequirementCompletionStatus(2)['submitted'] === 1, 'Duplicate rows inflated submitted total');
    $pdo->exec('DELETE FROM enr_student_requirements WHERE applicant_id = 102');
    $pdo->exec('ALTER TABLE enr_student_requirements ADD UNIQUE KEY uniq_applicant_requirement (applicant_id, requirement_id)');
    check($model->updateOrCreateRequirementStatus(2, 1, 1), 'Save with unique index failed');
    check($model->updateOrCreateRequirementStatus(2, 1, 0), 'Update with unique index failed');

    $incomplete = array_column($model->getStudentsWithIncompleteRequirements(), 'student_id');
    check(in_array(2, $incomplete) && in_array(4, $incomplete), 'Incomplete list omitted uninitialized requirements');
    check(!in_array(1, $incomplete), 'Mandatory-complete student marked incomplete');
    $controllerReflection = new ReflectionClass(RequirementController::class);
    $controller = $controllerReflection->newInstanceWithoutConstructor();
    $controllerReflection->getProperty('db')->setValue($controller, $database);
    $controllerReflection->getProperty('requirement')->setValue($controller, $model);
    $followups = array_column($controller->getFollowupStudents(), null, 'student_id');
    check((int) $followups[2]['total_mandatory'] === 2, 'Follow-up total omitted missing submissions');
    check((int) $followups[1]['submitted_mandatory'] === 2, 'Follow-up submitted count is wrong');
    $report = new EnrollmentReport();
    $reportRows = array_column($report->getStudentsWithIncompleteRequirements(), null, 'student_id');
    check((int) $reportRows[2]['total_requirements'] === 3, 'Report total omitted missing submissions');
    check((int) $reportRows[4]['submitted_count'] === 0, 'Report failed for student without submission rows');

    $_SESSION = [];
    $_POST = ['student_id' => 1, 'all_requirement_ids' => [1, 999], 'requirements' => [], 'req_notes' => []];
    $controllerReflection->getMethod('handleUpdateRequirements')->invoke($controller);
    check((int) $model->getStudentRequirementStatus(1, 1)['is_submitted'] === 1, 'Partial form save was not rolled back');
    check(!$pdo->inTransaction(), 'Failed form save leaked a transaction');
    $_POST = ['student_id' => 1, 'all_requirement_ids' => [1, 4], 'requirements' => [4 => '1'], 'req_notes' => []];
    $controllerReflection->getMethod('handleUpdateRequirements')->invoke($controller);
    check(!$model->getStudentRequirementStatus(1, 4), 'Freshman form saved a continuing requirement');
    check((int) $model->getStudentRequirementStatus(1, 1)['is_submitted'] === 1, 'Wrong-category form partially saved changes');
    $_POST = ['student_id' => 1, 'all_requirement_ids' => [1, 2], 'requirements' => [1 => '1'], 'req_notes' => [2 => 'Waiting']];
    $controllerReflection->getMethod('handleUpdateRequirements')->invoke($controller);
    check((int) $model->getStudentRequirementStatus(1, 2)['is_submitted'] === 0, 'Unchecked form item was ignored');
    check($model->getStudentRequirementStatus(1, 2)['notes'] === 'Waiting', 'Form notes were not saved');

    // This form may mark submissions, but cannot change verification status.
    $_POST = ['student_id' => 1, 'all_requirement_ids' => [1, 2], 'requirements' => [1 => '1', 2 => '1'],
        'req_status' => [1 => 'verified', 2 => 'rejected'], 'req_notes' => [1 => 'Reviewed', 2 => 'Unreadable']];
    $controllerReflection->getMethod('handleUpdateRequirements')->invoke($controller);
    $reviewed = $model->getStudentRequirementStatus(1, 1);
    check($reviewed['status'] === 'pending', 'Posted status must not verify a pending requirement');
    check($reviewed['file_data'] === $blob && $reviewed['custom_name'] === 'Original Certificate', 'Submission save changed attachment');
    check($model->getStudentRequirementStatus(1, 2)['status'] === 'pending', 'Posted status must not reject a pending requirement');
    check((int) $reviewed['is_submitted'] === 1 && $reviewed['notes'] === 'Reviewed', 'Submission and notes must remain editable');

    // Simulate review decisions made outside this form.
    $pdo->exec("UPDATE enr_student_requirements SET status = 'verified' WHERE applicant_id = 101 AND requirement_id = 1");
    $pdo->exec("UPDATE enr_student_requirements SET status = 'rejected' WHERE applicant_id = 101 AND requirement_id = 2");
    $reloaded = array_column($model->getStudentRequirements(1), null, 'requirement_id');
    check($reloaded[1]['status'] === 'verified' && $reloaded[2]['status'] === 'rejected', 'Checklist did not reload saved reviews');
    check($model->updateOrCreateRequirementStatus(1, 1, 1, 'Notes only'), 'Legacy save failed');
    check($model->getStudentRequirementStatus(1, 1)['status'] === 'verified', 'Notes-only save reset verification');
    check($model->getStudentRequirementStatus(1, 1)['submitted_date'] === $reviewed['submitted_date'], 'Verification changed submission date');
    check($model->updateOrCreateRequirementStatus(4, 1, 1), 'New submission failed');
    check($model->getStudentRequirementStatus(4, 1)['status'] === 'pending', 'New submission must default to pending');

    $_POST['req_status'] = [1 => 'pending', 2 => 'pending'];
    $_POST['requirements'] = [];
    $controllerReflection->getMethod('handleUpdateRequirements')->invoke($controller);
    check($model->getStudentRequirementStatus(1, 1)['status'] === 'verified', 'Unchecking changed verified status');
    check($model->getStudentRequirementStatus(1, 2)['status'] === 'rejected', 'Unchecking changed rejected status');
    check(!$pdo->inTransaction(), 'Form save leaked a transaction');

    // Render the checklist to verify status is visible but cannot be edited.
    $model->updateOrCreateRequirementStatus(1, 1, 1, 'Reviewed');
    $view = 'students';
    $studentId = 1;
    $studentDetails = ['first_name' => 'Test', 'surname' => 'Student'];
    $studentRequirements = $model->getStudentRequirements(1);
    $completionStatus = $model->getRequirementCompletionStatus(1);
    $allStudents = [];
    $allRequirements = [];
    $categories = [];
    $searchQuery = $message = '';
    $paymentCounts = ['pending' => 0];
    ob_start();
    include __DIR__ . '/../pages/requirements.php';
    $html = ob_get_clean();
    check(strpos($html, 'name="req_status[') === false, 'Checklist still has an editable review control');
    check(strpos($html, '⏳ Pending') !== false, 'Pending badge missing');
    check(strpos($html, 'panel-status-badge rejected">Rejected') !== false, 'Rejected badge missing');
    check(strpos($html, '✅ Verified') !== false, 'Saved verification badge missing');

    $added = $model->addRequirement(['requirement_name' => 'New Document', 'requirement_category' => 'freshmen']);
    check($added['success'], 'Create definition failed on schema without updated_at');
    check($model->updateRequirement($added['id'], ['requirement_name' => 'Renamed Document'])['success'], 'Edit definition failed on schema without updated_at');
    check(!$model->addRequirement(['requirement_name' => '   ', 'requirement_category' => 'freshmen'])['success'], 'Blank definition name accepted');
    check(!$model->updateRequirement($added['id'], ['requirement_category' => 'invalid'])['success'], 'Invalid category accepted');
    check($model->deleteRequirement($added['id'])['success'], 'Delete definition failed');

    echo "PASS: $checks requirements regression checks.\n";
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $pdo->exec('USE `' . str_replace('`', '``', $originalDatabase) . '`');
    if ($created) $pdo->exec("DROP DATABASE `$testDatabase`");
}
