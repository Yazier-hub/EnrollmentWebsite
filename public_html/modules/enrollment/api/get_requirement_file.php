<?php
// Downloads use the same employee session guard as the enrollment module.
require_once dirname(__DIR__, 3) . '/auth/guard.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/Requirement.php';

$studentId = filter_input(INPUT_GET, 'student_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$requirementId = filter_input(INPUT_GET, 'requirement_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
$submissionId = filter_input(INPUT_GET, 'submission_id', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
if (!$studentId || !$requirementId || (isset($_GET['submission_id']) && !$submissionId)) {
    http_response_code(400);
    exit('A valid student and requirement are required.');
}

$requirement = new Requirement();
$file = $requirement->getStudentRequirementStatus($studentId, $requirementId, $submissionId ?: null);
if (!$file || $file['file_data'] === null || $file['file_data'] === '') {
    http_response_code(404);
    exit('File not found.');
}

$filename = basename(str_replace('\\', '/', $file['file_name'] ?: 'requirement'));
$filename = preg_replace('/[\x00-\x1F\x7F"\\\\]/', '_', $filename);
$fallback = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);

// Download arbitrary uploads rather than execute user-supplied HTML or SVG.
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $fallback . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
header('Content-Length: ' . strlen($file['file_data']));
header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store');
echo $file['file_data'];
