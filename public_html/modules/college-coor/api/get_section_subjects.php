<?php
require_once('../../../database/db.php');

header('Content-Type: application/json');

try {
    if (!isset($_GET['section_id'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing section_id parameter']);
        exit;
    }
    
    $database = new Database();
    $conn = $database->getConnection();
    
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    $sectionId = $_GET['section_id'];
    
    // First, get the section details (program, year level, semester id)
    $sectionQuery = "SELECT program_id, grade_level, semester_id FROM cc_sections WHERE id = :section_id";
    $sectionStmt = $conn->prepare($sectionQuery);
    $sectionStmt->bindParam(':section_id', $sectionId);
    $sectionStmt->execute();
    $section = $sectionStmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$section) {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Section not found']);
        exit;
    }
    
    // Determine numeric year level from section.grade_level
    $rawYearLevel = $section['grade_level'];
    if (is_numeric($rawYearLevel)) {
        $yearLevel = (int)$rawYearLevel;
    } else {
        // convert strings like "1st Year" -> 1
        if (preg_match('/(\\d+)/', $rawYearLevel, $m)) {
            $yearLevel = (int)$m[1];
        } else {
            $yearLevel = 0;
        }
    }

    // Resolve semester name from rgr_semesters (curriculum_subjects.semester stores semester name)
    $semesterName = '';
    if (!empty($section['semester_id'])) {
        $semStmt = $conn->prepare("SELECT name FROM rgr_semesters WHERE id = :id LIMIT 1");
        $semStmt->bindParam(':id', $section['semester_id'], PDO::PARAM_INT);
        $semStmt->execute();
        $semRow = $semStmt->fetch(PDO::FETCH_ASSOC);
        $semesterName = $semRow['name'] ?? '';
    }

    // Now get subjects for this curriculum, year level, and semester name
    $sql = "SELECT DISTINCT
        s.id,
        s.code,
        s.name,
        s.units,
        s.lecture_hours,
        s.lab_hours
    FROM rgr_subjects s
    INNER JOIN rgr_curriculum_subjects cs ON cs.subject_id = s.id
    INNER JOIN rgr_curriculums cu ON cu.id = cs.curriculum_id
    WHERE cu.course_id = :program_id
    AND cs.year_level = :year_level
    AND cs.semester = :semester
    AND cu.is_active = 1
    ORDER BY s.code";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':program_id', $section['program_id']);
    $stmt->bindParam(':year_level', $yearLevel, PDO::PARAM_INT);
    $stmt->bindParam(':semester', $semesterName);
    $stmt->execute();

    $subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$subjects || count($subjects) === 0) {
        // No subjects found for the active curriculum — return success with message
        echo json_encode([
            'success' => true,
            'subjects' => [],
            'message' => 'No subjects are available for the active curriculum.'
        ]);
        exit;
    }

    echo json_encode(['success' => true, 'subjects' => $subjects]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
