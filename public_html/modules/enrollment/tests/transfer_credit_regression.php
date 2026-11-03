<?php
/** Run: C:/xampp/php/php.exe tests/transfer_credit_regression.php. Uses a disposable database. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
set_include_path(dirname(__DIR__) . '/classes' . PATH_SEPARATOR . get_include_path());
require_once __DIR__ . '/../classes/TransferCredit.php';
require_once __DIR__ . '/../classes/Enrollment.php';
require_once __DIR__ . '/../classes/Requirement.php';

$pdo = Database::getInstance()->getConnection();
$original = $pdo->query('SELECT DATABASE()')->fetchColumn();
$test = 'enrollment_transfer_test_' . bin2hex(random_bytes(8));
$created = false;
$checks = 0;
function checkTransfer($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}

try {
    $pdo->exec("CREATE DATABASE `$test` CHARACTER SET utf8mb4");
    $created = true;
    $pdo->exec("USE `$test`");
    checkTransfer($pdo->query('SELECT DATABASE()')->fetchColumn() === $test, 'Database isolation failed');
    $tables = [
        'CREATE TABLE enr_applicants (applicant_id INT PRIMARY KEY, admission_type VARCHAR(30), course_id INT) ENGINE=InnoDB',
        'CREATE TABLE enr_students (student_id INT PRIMARY KEY, applicant_id INT, course_id INT) ENGINE=InnoDB',
        'CREATE TABLE enr_requirements (requirement_id INT PRIMARY KEY, requirement_name VARCHAR(100), requirement_category VARCHAR(30)) ENGINE=InnoDB',
        "CREATE TABLE enr_student_requirements (id INT PRIMARY KEY, applicant_id INT, requirement_id INT,
            file_name VARCHAR(255), file_data LONGBLOB, status VARCHAR(20), is_submitted INT) ENGINE=InnoDB",
        'CREATE TABLE rgr_curriculums (id INT PRIMARY KEY, course_id INT, is_active INT, curriculum_name VARCHAR(120)) ENGINE=InnoDB',
        'CREATE TABLE rgr_subjects (id INT PRIMARY KEY, code VARCHAR(50), name VARCHAR(255), units DECIMAL(5,2)) ENGINE=InnoDB',
        'CREATE TABLE rgr_curriculum_subjects (id INT PRIMARY KEY, curriculum_id INT, subject_id INT, year_level INT, semester VARCHAR(250)) ENGINE=InnoDB',
        'CREATE TABLE cc_schedule (id INT PRIMARY KEY, subject_id INT) ENGINE=InnoDB',
        "CREATE TABLE enr_enrollments (enrollment_id INT PRIMARY KEY, student_id INT, schedule_id INT, enrollment_status VARCHAR(20),
            section_id INT, semester VARCHAR(30), school_year VARCHAR(30), created_at DATETIME) ENGINE=InnoDB",
        'CREATE TABLE rgr_grades (enrollment_id INT, final_grade DECIMAL(5,2), remarks VARCHAR(20)) ENGINE=InnoDB',
        'CREATE TABLE enr_prerequisites (subject_id INT, prerequisite_subject_id INT) ENGINE=InnoDB',
        'CREATE TABLE cc_sections (id INT PRIMARY KEY, grade_level VARCHAR(20), section_code VARCHAR(20)) ENGINE=InnoDB',
    ];
    foreach ($tables as $sql) $pdo->exec($sql);
    // Reproduce the supplied applicant-owned schema independently of the live database.
    $pdo->exec(file_get_contents(__DIR__ . '/fixtures/applicant-transfer-schema.sql'));
    $pdo->exec("INSERT INTO enr_applicants VALUES (101,'transferee',10),(102,'transferee',10),(103,'freshmen',10),(104,'transferee',10)");
    $pdo->exec('INSERT INTO enr_students VALUES (1,101,10),(2,102,10),(3,103,10)');
    $pdo->exec("INSERT INTO enr_requirements VALUES (1,'Transcript','transferee'),(2,'Birth Certificate','freshmen')");
    $pdo->exec("INSERT INTO enr_student_requirements VALUES
        (11,101,1,'transcript.pdf','PDF','verified',1), (12,102,1,'other.pdf','PDF','verified',1),
        (13,101,1,'pending.pdf','PDF','pending',1), (14,101,1,'rejected.pdf','PDF','rejected',1),
        (15,101,1,'empty.pdf',NULL,'verified',1), (16,101,2,'birth.pdf','PDF','verified',1)");
    $pdo->exec("INSERT INTO rgr_curriculums VALUES (1,10,1,'Current'),(2,20,1,'Other course'),(3,10,0,'Old'),(4,10,1,'Alternate')");
    $pdo->exec("INSERT INTO rgr_subjects VALUES (1,'MATH1','Math 1',3),(2,'MATH2','Math 2',3),(3,'ENG1','English',3),(4,'SCI1','Science',3)");
    $pdo->exec("INSERT INTO rgr_curriculum_subjects VALUES
        (21,1,1,1,'First Semester'),(22,1,2,1,'Second Semester'),(23,1,3,1,'First Semester'),
        (24,2,1,1,'First Semester'),(25,3,1,1,'First Semester'),(26,4,1,1,'First Semester'),(27,1,4,1,'First Semester')");
    $pdo->exec('INSERT INTO cc_schedule VALUES (31,1),(32,2),(33,3),(34,4)');
    $pdo->exec('INSERT INTO enr_prerequisites VALUES (2,1)');

    $model = new TransferCredit($pdo);
    $status = new SubjectStatusManager();
    $validator = new PrerequisiteValidator();
    $enrollment = new Enrollment();
    checkTransfer(!$validator->validateSubject(1,2)['valid'], 'Unmet prerequisite must block');
    // These fixtures represent decisions written by Regis, never by Enrollment.
    $pdo->exec("INSERT INTO rgr_student_subject_evaluations
        (applicant_id,requirement_file_id,curriculum_subject_id,previous_subject_name,previous_grade,previous_units,status)
        VALUES (101,11,21,'Previous Math','1.75','3.00','pending')");
    $id = (int)$pdo->lastInsertId();
    checkTransfer(!$model->hasCredit(1,1), 'Pending evaluation granted credit');
    $pdo->exec("UPDATE rgr_student_subject_evaluations SET status='approved' WHERE id=$id");
    checkTransfer($model->hasCredit(1,1), 'Approved evaluation without a credit record was ignored');
    checkTransfer($model->getCreditedSubjectIdsForApplicant(101) === [1], 'Approved evaluation missing before conversion');
    checkTransfer($validator->validateSubject(1,2)['valid'], 'Approved evaluation alone did not satisfy prerequisite');
    checkTransfer(!$enrollment->enrollStudentInSubject(1,1,31,'2026-2027')['success'], 'Approved evaluation alone allowed repeat enrollment');
    checkTransfer($status->getSubjectStatus(1,1)['status'] === SubjectStatusManager::STATUS_COMPLETED, 'Approved evaluation alone not shown as completed');
    $pdo->exec("INSERT INTO rgr_student_credited_subjects (curriculum_subject_id,evaluation_id,credited_by)
        VALUES (21,$id,7)");
    $registrarEvaluation = $pdo->query("SELECT * FROM rgr_student_subject_evaluations WHERE id=$id")->fetch(PDO::FETCH_ASSOC);
    $registrarCredit = $pdo->query('SELECT * FROM rgr_student_credited_subjects')->fetch(PDO::FETCH_ASSOC);
    checkTransfer($model->hasCredit(1,1) && !$model->hasCredit(2,1), 'Credit ownership incorrect');
    checkTransfer($model->getCreditedSubjectIdsForApplicant(101) === [1], 'Applicant credit lookup failed');
    checkTransfer($model->getCreditedSubjectIdsForApplicant(1) === [], 'Student ID was treated as applicant ID');
    checkTransfer($validator->validateSubject(1,2)['valid'], 'Credit did not satisfy prerequisite');
    checkTransfer(!$validator->validateSubject(1,1)['valid'], 'Credited subject enrollable via prerequisite validator');
    checkTransfer(!$enrollment->validateEnrollment(1,[31])['valid'], 'Credited subject enrollable through enrollment model');
    checkTransfer(!$enrollment->enrollStudentInSubject(1,1,31,'2026-2027')['success'], 'Direct enrollment bypassed credit');
    checkTransfer($status->getSubjectStatus(1,1)['status'] === SubjectStatusManager::STATUS_COMPLETED, 'Credit not shown as completed');
    checkTransfer($pdo->query("SELECT * FROM rgr_student_subject_evaluations WHERE id=$id")->fetch(PDO::FETCH_ASSOC) === $registrarEvaluation, 'Enrollment changed the Regis evaluation');
    checkTransfer($pdo->query('SELECT * FROM rgr_student_credited_subjects')->fetch(PDO::FETCH_ASSOC) === $registrarCredit, 'Enrollment changed the Regis credit');

    $pdo->exec("INSERT INTO enr_enrollments VALUES (2,1,31,'dropped',1,'First Semester','2025-2026',NOW())");
    $pdo->exec("INSERT INTO rgr_grades VALUES (2,65,'failed')");
    checkTransfer(!$status->checkIfSubjectNeedsRetake(1,1) && !$status->checkIfSubjectFailed(1,1), 'Credit overridden by old failure');
    checkTransfer($status->getFailedSubjectsForRetake(1) === [], 'Credited subject still appears in retake list');
    // Registrar approves a credit while the person is still an applicant.
    $pdo->exec("INSERT INTO enr_student_requirements VALUES (17,104,1,'applicant.pdf','PDF','verified',1)");
    $pdo->exec("INSERT INTO rgr_student_subject_evaluations
        (applicant_id, requirement_file_id, curriculum_subject_id, previous_subject_name, previous_grade, previous_units, status)
        VALUES (104,17,25,'Previous Math','2','3.00','approved')");
    $applicantEvaluationId = (int)$pdo->lastInsertId();
    $pdo->exec("INSERT INTO rgr_student_credited_subjects (curriculum_subject_id,evaluation_id) VALUES (25,$applicantEvaluationId)");
    checkTransfer($model->getCreditedSubjectIdsForApplicant(104) === [1], 'Pre-conversion credit from older curriculum missing');
    checkTransfer(!$model->hasCredit(4,1), 'Nonexistent student received credit');
    $pdo->exec('INSERT INTO enr_students VALUES (4,104,10)');
    checkTransfer($model->hasCredit(4,1), 'Conversion did not recognize applicant credit with NULL student_id');
    checkTransfer($validator->validateSubject(4,2)['valid'], 'Applicant credit did not unlock prerequisite after conversion');
    checkTransfer(!$enrollment->enrollStudentInSubject(4,1,31,'2026-2027')['success'], 'Applicant credit did not block direct repeat enrollment');
    $pdo->exec("UPDATE rgr_student_subject_evaluations SET status='pending' WHERE id=$applicantEvaluationId");
    checkTransfer(!$model->hasCredit(4,1) && $model->getCreditedSubjectIdsForApplicant(104) === [], 'Pending evaluation with credit row granted credit');
    $pdo->exec("UPDATE rgr_student_subject_evaluations SET status='rejected' WHERE id=$applicantEvaluationId");
    checkTransfer(!$model->hasCredit(4,1), 'Rejected evaluation with credit row granted credit');
    $pdo->exec("UPDATE rgr_student_subject_evaluations SET status='approved' WHERE id=$applicantEvaluationId");
    $pdo->exec('UPDATE enr_applicants SET course_id=20 WHERE applicant_id=104');
    checkTransfer($model->getCreditedSubjectIdsForApplicant(104) === [], 'Applicant credit leaked into another course');
    $pdo->exec('UPDATE enr_students SET course_id=20 WHERE student_id=1');
    checkTransfer(!$model->hasCredit(1,1), 'Credit leaked into different course');
    $pdo->exec('UPDATE enr_students SET course_id=10 WHERE student_id=1');

    // A mismatched curriculum reference must not grant a different subject.
    $pdo->exec("INSERT INTO rgr_student_credited_subjects (curriculum_subject_id,evaluation_id) VALUES (23,$id)");
    checkTransfer(!$model->hasCredit(1,3), 'Mismatched credit and evaluation granted credit');
    checkTransfer($model->getCreditedSubjectIdsForApplicant(101) === [1], 'Mismatched credit leaked into applicant subject list');
    checkTransfer(!method_exists($model, 'submit') && !method_exists($model, 'review'), 'Enrollment must not expose evaluation or approval actions');

    checkTransfer($model->getPlacementForApplicant(102) === null, 'Uncredited transferee placement changed');
    checkTransfer($model->getPlacementForApplicant(103) === null, 'Freshman placement changed');
    checkTransfer($model->getPlacementForApplicant(101)['semester'] === 1, 'Partial credits skipped an unfinished term');
    // Add a later year and multiple semester encodings to the active curriculum.
    $pdo->exec("INSERT INTO rgr_subjects VALUES (5,'YEAR2','Second year subject',3)");
    $pdo->exec("INSERT INTO rgr_curriculum_subjects VALUES (28,1,5,2,'1')");
    $pdo->exec("UPDATE rgr_curriculum_subjects SET semester='1st Semester' WHERE id=23");
    $pdo->exec("UPDATE rgr_curriculum_subjects SET semester='First' WHERE id=27");
    $approve = function ($curriculumSubjectId) use ($pdo) {
        $pdo->exec("INSERT INTO rgr_student_subject_evaluations
            (applicant_id,requirement_file_id,curriculum_subject_id,previous_subject_name,previous_grade,previous_units,status)
            VALUES (101,11,$curriculumSubjectId,'Transfer subject','1.75','3.00','approved')");
        // Regis can approve evaluations without creating a separate credited-subject row.
    };
    $approve(23);
    $approve(27);
    $placement = $model->getPlacementForApplicant(101);
    checkTransfer($placement['year_level'] === 1 && $placement['semester'] === 2, 'Fully credited first semester did not advance');
    // Exercise the actual endpoint used by the enrollment modal in this isolated database.
    $pdo->exec('CREATE TABLE rgr_courses (id INT PRIMARY KEY, code VARCHAR(20), name VARCHAR(100))');
    $pdo->exec('CREATE TABLE rgr_school_years (id INT PRIMARY KEY, name VARCHAR(20), is_active INT)');
    $pdo->exec('CREATE TABLE rgr_semesters (id INT PRIMARY KEY, name VARCHAR(30), school_year_id INT)');
    $pdo->exec('ALTER TABLE cc_sections ADD program_id INT, ADD semester_id INT, ADD school_year_id INT');
    $pdo->exec("INSERT INTO rgr_courses VALUES (10,'TEST','Test Course')");
    $pdo->exec("INSERT INTO rgr_school_years VALUES (50,'2026-2027',1)");
    $pdo->exec("INSERT INTO rgr_semesters VALUES (13,'First Semester',50),(14,'2nd Semester',50)");
    $pdo->exec("INSERT INTO cc_sections VALUES (41,'1st Year','FIRST',10,13,50),(42,'1st Year','SECOND',10,14,50),(43,'2nd Year','YEAR2',10,13,50)");
    $loadSections = function () {
        $_GET = ['applicant_id' => 101, 'course_id' => 10, 'year_level' => 1, 'semester' => 1, 'only_available' => 1];
        ob_start();
        include __DIR__ . '/../api/get_sections_by_course.php';
        return json_decode(ob_get_clean(), true, 512, JSON_THROW_ON_ERROR);
    };
    $response = $loadSections();
    checkTransfer($response['success'] && array_column($response['data'], 'id') === [42], 'Modal endpoint retained first-semester section');
    $pdo->exec('DELETE FROM cc_sections WHERE id=42');
    checkTransfer($loadSections()['data'] === [], 'Missing next-semester section fell back to completed first semester');
    $approve(22);
    $placement = $model->getPlacementForApplicant(101);
    checkTransfer($placement['year_level'] === 2 && $placement['semester'] === 1, 'Fully credited year did not advance');
    checkTransfer(array_column($loadSections()['data'], 'id') === [43], 'Modal endpoint did not select second-year section');
    $approve(28);
    checkTransfer($model->getPlacementForApplicant(101)['curriculum_completed'], 'Completed curriculum wrapped back to first year');
    $response = $loadSections();
    checkTransfer($response['data'] === [] && $response['placement']['curriculum_completed'], 'Completed curriculum still offers sections');
    $pdo->exec("UPDATE rgr_student_subject_evaluations SET status='pending' WHERE applicant_id=101 AND curriculum_subject_id=23");
    checkTransfer($model->getPlacementForApplicant(101)['semester'] === 1, 'Pending credit allowed advancement');
    $pdo->exec("UPDATE rgr_student_subject_evaluations SET status='rejected' WHERE applicant_id=101 AND curriculum_subject_id=23");
    checkTransfer($model->getPlacementForApplicant(101)['year_level'] === 1, 'Rejected credit allowed advancement');

    echo "PASS: $checks transfer-credit checks\n";
} finally {
    if ($pdo->inTransaction()) $pdo->rollBack();
    $pdo->exec("USE `$original`");
    if ($created) $pdo->exec("DROP DATABASE `$test`");
}
