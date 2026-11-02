<?php
// Buffer everything from this point on so any stray warning/notice from an
// include below lands in this buffer instead of corrupting the JSON response.

header('Content-Type: application/json');

function concern_respond($payload) {
    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    echo json_encode($payload);
    exit;
}

// Last-resort safety net for fatals that happen even outside try/catch
// (e.g. a parse error while including a file, or memory exhaustion).
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length() !== false) {
            ob_end_clean();
        }
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        echo json_encode([
            'success' => false,
            'message' => 'A fatal server error occurred.',
            // TEMP debug info — remove the "debug" key once things are stable.
            'debug' => $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
        ]);
    }
});

try {
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Issues.php';
    include_once __DIR__ . '/../classes/User.php';

    // ── Guard ──────────────────────────────────────────────────────────────
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    if (!$userInfo) {
        concern_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $issuesClass  = new Issues();
    $action       = $_REQUEST['action'] ?? '';
    $isDirectress = ($userInfo['role'] === 'School Directress');

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET list (optionally filtered by department / status / search)
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== ''
                ? (int) $_GET['department_id']
                : null;
            $status = isset($_GET['status']) && $_GET['status'] !== ''
                ? $_GET['status']
                : null;
            $search = trim($_GET['search'] ?? '');

            if (!$isDirectress && $departmentId === null) {
                $departmentId = $userInfo['department_id'] ?? null;
            }

            $concerns = $issuesClass->getConcerns($departmentId, $status, $search);
            concern_respond(['success' => true, 'data' => $concerns]);
            break;

        // ------------------------------------------------------------------
        // GET single concern (for the detail/review modal)
        // ------------------------------------------------------------------
        case 'get':
            $issueId = (int) ($_GET['issue_id'] ?? 0);
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            $concern = $issuesClass->getConcernById($issueId);
            if (!$concern) {
                concern_respond(['success' => false, 'message' => 'Concern not found.']);
            }
            concern_respond(['success' => true, 'data' => $concern]);
            break;

        // ------------------------------------------------------------------
        // POST save as draft
        // ------------------------------------------------------------------
        case 'save_draft':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                concern_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateConcernInput($_POST, $userInfo);
            if (!$validated['valid']) {
                concern_respond(['success' => false, 'message' => $validated['message']]);
            }

            $fields = $validated['fields'];
            $fileResult = handleOptionalAttachment();
            if (!$fileResult['success']) {
                concern_respond(['success' => false, 'message' => $fileResult['message']]);
            }
            if ($fileResult['file_path'] !== null) {
                $fields['file_path'] = $fileResult['file_path'];
            }

            $issueId = (int) ($_POST['issue_id'] ?? 0) ?: null;
            $newId   = $issuesClass->saveDraft($fields, $issueId);

            concern_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['issue_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit a concern — creates/promotes to 'submitted' and auto-
        // generates the PDF. PDF failure never blocks the submission.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                concern_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateConcernInput($_POST, $userInfo);
            if (!$validated['valid']) {
                concern_respond(['success' => false, 'message' => $validated['message']]);
            }

            $fields = $validated['fields'];
            $fileResult = handleOptionalAttachment();
            if (!$fileResult['success']) {
                concern_respond(['success' => false, 'message' => $fileResult['message']]);
            }
            if ($fileResult['file_path'] !== null) {
                $fields['file_path'] = $fileResult['file_path'];
            }

            $issueId = (int) ($_POST['issue_id'] ?? 0) ?: null;
            $newId   = $issuesClass->submitConcern($fields, $issueId);

            $pdfPath = null;
            $message = 'Concern logged successfully.';
            try {
                $pdfPath = $issuesClass->generatePdf($newId);
            } catch (\Throwable $e) {
                error_log('[IssueController] PDF generation failed for concern ' . $newId . ': ' . $e->getMessage());
                $message = 'Concern logged, but the PDF could not be generated. Contact your administrator (PDF library may not be installed).';
            }

            concern_respond([
                'success' => true,
                'message' => $message,
                'data'    => ['issue_id' => $newId, 'pdf_path' => $pdfPath],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted concern as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if (!$isDirectress) {
                concern_respond(['success' => false, 'message' => 'Only the School Directress can review concerns.']);
            }
            $issueId = (int) ($_POST['issue_id'] ?? 0);
            $notes   = trim($_POST['notes'] ?? '');
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            $ok = $issuesClass->markReviewed($issueId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            concern_respond(['success' => $ok, 'message' => $ok ? 'Marked as reviewed.' : 'Unable to review this concern.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress resolves/dismisses a reviewed concern
        // ------------------------------------------------------------------
        case 'decide':
            if (!$isDirectress) {
                concern_respond(['success' => false, 'message' => 'Only the School Directress can resolve concerns.']);
            }
            $issueId  = (int) ($_POST['issue_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $notes    = trim($_POST['notes'] ?? '');
            if (!$issueId || !in_array($decision, ['resolved', 'dismissed'], true)) {
                concern_respond(['success' => false, 'message' => 'A valid issue ID and decision are required.']);
            }
            $ok = $issuesClass->resolve($issueId, $userInfo['employee_id'], $decision, $notes !== '' ? $notes : null);
            concern_respond(['success' => $ok, 'message' => $ok ? "Concern {$decision}." : 'Unable to update this concern.']);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary
        // ------------------------------------------------------------------
        case 'summarize':
            $issueId = (int) ($_REQUEST['issue_id'] ?? 0);
            if (!$issueId) {
                concern_respond(['success' => false, 'message' => 'Issue ID is required.']);
            }
            $result = $issuesClass->generateAiSummary($issueId);
            concern_respond($result);
            break;

        default:
            concern_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[IssueController] Unhandled error: ' . $e->getMessage());
    concern_respond(['success' => false, 'message' => 'An unexpected server error occurred.']);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function validateConcernInput($post, $userInfo) {
    $title              = trim($post['title'] ?? '');
    $details            = trim($post['details'] ?? '');
    $desiredResolution  = trim($post['desired_resolution'] ?? '');
    $departmentId       = $userInfo['department_id'] ?? null;

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($details === '') {
        return ['valid' => false, 'message' => 'Details are required.'];
    }
    if (!$departmentId) {
        return ['valid' => false, 'message' => 'Department not found for your account.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'              => $title,
            'details'            => $details,
            'desired_resolution' => $desiredResolution,
            'department_id'      => $departmentId,
        ],
    ];
}

// Optional supporting attachment — same validation rules as the original upload flow.
function handleOptionalAttachment() {
    if (empty($_FILES['file']['name'])) {
        return ['success' => true, 'file_path' => null];
    }
    if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed. Please try again.'];
    }

    $allowedMimes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'image/png',
        'image/jpeg',
    ];
    $allowedExts  = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'png', 'jpg', 'jpeg'];
    $maxSizeBytes = 10 * 1024 * 1024;

    $fileExt  = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
    $fileMime = mime_content_type($_FILES['file']['tmp_name']);

    if (!in_array($fileExt, $allowedExts, true) || !in_array($fileMime, $allowedMimes, true)) {
        return ['success' => false, 'message' => 'Invalid file type.'];
    }
    if ($_FILES['file']['size'] > $maxSizeBytes) {
        return ['success' => false, 'message' => 'File exceeds the 10 MB size limit.'];
    }

    $uploadDir = __DIR__ . '/../../../uploads/issues/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $uniqueName = uniqid('issue_', true) . '.' . $fileExt;
    if (!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir . $uniqueName)) {
        return ['success' => false, 'message' => 'Failed to save the uploaded file.'];
    }

    return ['success' => true, 'file_path' => 'uploads/issues/' . $uniqueName];
}