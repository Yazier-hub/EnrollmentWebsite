<?php
// Buffer everything from this point on so any stray warning/notice from an
// include below lands in this buffer instead of corrupting the JSON response.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);         // still log real errors — do NOT silently suppress them

header('Content-Type: application/json');

function approval_respond($payload) {
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
    include_once __DIR__ . '/../classes/Approval.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    // ── Guard ──────────────────────────────────────────────────────────────
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    if (!$userInfo) {
        approval_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $approvalClass = new Approval();
    $action        = $_REQUEST['action'] ?? '';
    $isDirectress  = ($userInfo['role'] === 'School Directress')
                     || (($userInfo['department_name'] ?? null) === 'School Directress');

    // ── Router ─────────────────────────────────────────────────────────────
    switch ($action) {

        // ------------------------------------------------------------------
        // GET list (optionally filtered by department / status)
        // ------------------------------------------------------------------
        case 'list':
            $departmentId = isset($_GET['department_id']) && $_GET['department_id'] !== ''
                ? (int) $_GET['department_id']
                : null;
            $status = isset($_GET['status']) && $_GET['status'] !== ''
                ? $_GET['status']
                : null;

            if (!$isDirectress && $departmentId === null) {
                $departmentId = $userInfo['department_id'] ?? null;
            }

            $approvals = $approvalClass->getApprovals($departmentId, $status);
            approval_respond(['success' => true, 'data' => $approvals]);
            break;

        // ------------------------------------------------------------------
        // GET single approval (for the detail/review modal)
        // ------------------------------------------------------------------
        case 'get':
            $approvalId = (int) ($_GET['approval_id'] ?? 0);
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $approval = $approvalClass->getApprovalById($approvalId);
            if (!$approval) {
                approval_respond(['success' => false, 'message' => 'Approval request not found.']);
            }
            approval_respond(['success' => true, 'data' => $approval]);
            break;

        // ------------------------------------------------------------------
        // POST save as draft
        // ------------------------------------------------------------------
        case 'save_draft':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateApprovalInput($_POST);
            if (!$validated['valid']) {
                approval_respond(['success' => false, 'message' => $validated['message']]);
            }

            $fields = $validated['fields'];
            $fileResult = handleOptionalAttachment();
            if (!$fileResult['success']) {
                approval_respond(['success' => false, 'message' => $fileResult['message']]);
            }
            if ($fileResult['file_path'] !== null) {
                $fields['file_path'] = $fileResult['file_path'];
            }

            $approvalId = (int) ($_POST['approval_id'] ?? 0) ?: null;
            $newId      = $approvalClass->saveDraft($fields, $approvalId);

            approval_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['approval_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit an approval request — creates/promotes to 'submitted'
        // and auto-generates the PDF. PDF failure never blocks submission.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                approval_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateApprovalInput($_POST);
            if (!$validated['valid']) {
                approval_respond(['success' => false, 'message' => $validated['message']]);
            }

            $fields = $validated['fields'];
            $fileResult = handleOptionalAttachment();
            if (!$fileResult['success']) {
                approval_respond(['success' => false, 'message' => $fileResult['message']]);
            }
            if ($fileResult['file_path'] !== null) {
                $fields['file_path'] = $fileResult['file_path'];
            }

            $approvalId = (int) ($_POST['approval_id'] ?? 0) ?: null;
            $newId      = $approvalClass->submitApproval($fields, $approvalId);

            $pdfPath = null;
            $message = 'Approval request submitted successfully.';
            try {
                $pdfPath = $approvalClass->generatePdf($newId);
            } catch (\Throwable $e) {
                error_log('[ApprovalController] PDF generation failed for approval ' . $newId . ': ' . $e->getMessage());
                $message = 'Request submitted, but the PDF could not be generated. Contact your administrator (PDF library may not be installed).';
            }

            approval_respond([
                'success' => true,
                'message' => $message,
                'data'    => ['approval_id' => $newId, 'pdf_path' => $pdfPath],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted request as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if (!$isDirectress) {
                approval_respond(['success' => false, 'message' => 'Only the School Directress can review approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $notes      = trim($_POST['notes'] ?? '');
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $ok = $approvalClass->markReviewed($approvalId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            approval_respond(['success' => $ok, 'message' => $ok ? 'Marked as reviewed.' : 'Unable to review this request.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress approves/rejects a reviewed request
        // ------------------------------------------------------------------
        case 'decide':
            if (!$isDirectress) {
                approval_respond(['success' => false, 'message' => 'Only the School Directress can decide on approval requests.']);
            }
            $approvalId = (int) ($_POST['approval_id'] ?? 0);
            $decision   = $_POST['decision'] ?? '';
            $notes      = trim($_POST['notes'] ?? '');
            if (!$approvalId || !in_array($decision, ['approved', 'rejected'], true)) {
                approval_respond(['success' => false, 'message' => 'A valid approval ID and decision are required.']);
            }
            $ok = $approvalClass->decide($approvalId, $userInfo['employee_id'], $decision, $notes !== '' ? $notes : null);
            approval_respond(['success' => $ok, 'message' => $ok ? "Request {$decision}." : 'Unable to decide on this request.']);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary
        // ------------------------------------------------------------------
        case 'summarize':
            $approvalId = (int) ($_REQUEST['approval_id'] ?? 0);
            if (!$approvalId) {
                approval_respond(['success' => false, 'message' => 'Approval ID is required.']);
            }
            $result = $approvalClass->generateAiSummary($approvalId);
            approval_respond($result);
            break;

        default:
            approval_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[ApprovalController] Unhandled error: ' . $e->getMessage());
    approval_respond(['success' => false, 'message' => 'An unexpected server error occurred.']);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function validateApprovalInput($post) {
    $title         = trim($post['title'] ?? '');
    $description   = trim($post['description'] ?? '');
    $justification = trim($post['justification'] ?? '');

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($description === '') {
        return ['valid' => false, 'message' => 'Description is required.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'         => $title,
            'description'   => $description,
            'justification' => $justification,
        ],
    ];
}

// Optional supporting attachment — same validation rules as the original upload flow.
function handleOptionalAttachment() {
    if (empty($_FILES['attachment']['name'])) {
        return ['success' => true, 'file_path' => null];
    }

    $allowedTypes = [
        'application/pdf', 'image/jpeg', 'image/png',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
    ];
    $fileType = mime_content_type($_FILES['attachment']['tmp_name']);

    if (!in_array($fileType, $allowedTypes, true)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: PDF, JPG, PNG, DOC, DOCX.'];
    }
    if ($_FILES['attachment']['size'] > 5 * 1024 * 1024) {
        return ['success' => false, 'message' => 'File exceeds the 5 MB limit.'];
    }

    $uploadDir = __DIR__ . '/../../../uploads/approvals/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $ext      = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
    $fileName = uniqid('approval_', true) . '.' . $ext;

    if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadDir . $fileName)) {
        return ['success' => false, 'message' => 'Failed to upload the attachment.'];
    }

    return ['success' => true, 'file_path' => 'uploads/approvals/' . $fileName];
}