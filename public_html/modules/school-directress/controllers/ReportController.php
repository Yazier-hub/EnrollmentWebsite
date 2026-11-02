<?php
// Buffer everything from this point on. If ANY warning/notice/deprecation gets
// printed by an include below, it lands in this buffer instead of corrupting
// the JSON response — we discard the buffer right before echoing our own JSON.
ob_start();
ini_set('display_errors', '0'); // never leak raw PHP errors into the JSON response
error_reporting(E_ALL);

header('Content-Type: application/json');

function rsm_respond($payload) {
    if (ob_get_length() !== false) {
        ob_end_clean();
    }
    echo json_encode($payload);
    exit;
}

// Last-resort safety net: catches fatals that happen even outside try/catch
// (e.g. a parse error while including a file, or memory exhaustion) so the
// response is always valid JSON instead of an empty/HTML body.
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
            // TEMP debug info — remove the "debug" key once this is fixed.
            'debug' => $error['message'] . ' in ' . $error['file'] . ':' . $error['line'],
        ]);
    }
});

try {
    // Includes are inside the try so a parse/fatal error in any of these files
    // gets caught below and returned as JSON instead of an empty 500 response.
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../classes/Report.php';
    include_once __DIR__ . '/../classes/Department.php';
    include_once __DIR__ . '/../classes/User.php';

    // ── Guard ──────────────────────────────────────────────────────────────
    $userClass = new User();
    $userInfo  = $userClass->userSession();

    if (!$userInfo) {
        rsm_respond(['success' => false, 'message' => 'Session expired.']);
    }

    $reportClass  = new Report();
    $action       = $_REQUEST['action'] ?? '';
    $isDirectress = ($userInfo['role'] === 'School Directress');

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
                $departmentId = $_SESSION['department_id'] ?? null;
            }

            $reports = $reportClass->getReports($departmentId, $status);
            rsm_respond(['success' => true, 'data' => $reports]);
            break;

        // ------------------------------------------------------------------
        // GET single report (for the detail/review modal)
        // ------------------------------------------------------------------
        case 'get':
            $reportId = (int) ($_GET['report_id'] ?? 0);
            if (!$reportId) {
                rsm_respond(['success' => false, 'message' => 'Report ID is required.']);
            }
            $report = $reportClass->getReportById($reportId);
            if (!$report) {
                rsm_respond(['success' => false, 'message' => 'Report not found.']);
            }
            rsm_respond(['success' => true, 'data' => $report]);
            break;

        // ------------------------------------------------------------------
        // POST save as draft
        // ------------------------------------------------------------------
        case 'save_draft':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                rsm_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateReportInput($_POST);
            if (!$validated['valid']) {
                rsm_respond(['success' => false, 'message' => $validated['message']]);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0) ?: null;
            $newId    = $reportClass->saveDraft($validated['fields'], $reportId);

            rsm_respond(['success' => true, 'message' => 'Draft saved.', 'data' => ['report_id' => $newId]]);
            break;

        // ------------------------------------------------------------------
        // POST submit a report — creates/promotes to 'submitted' and auto-generates the PDF.
        // PDF generation failure never blocks the submission itself.
        // ------------------------------------------------------------------
        case 'submit':
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                rsm_respond(['success' => false, 'message' => 'Method not allowed.']);
            }
            $validated = validateReportInput($_POST);
            if (!$validated['valid']) {
                rsm_respond(['success' => false, 'message' => $validated['message']]);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0) ?: null;
            $newId    = $reportClass->submitReport($validated['fields'], $reportId);

            $pdfPath = null;
            $message = 'Report submitted successfully.';
            $debug   = null;
            try {
                $pdfPath = $reportClass->generatePdf($newId);
            } catch (\Throwable $e) {
                error_log('[ReportController] PDF generation failed for report ' . $newId . ': ' . $e->getMessage());
                $message = 'Report submitted, but the PDF could not be generated. Contact your administrator (PDF library may not be installed).';
                // TEMP debug info — remove once things are stable.
                $debug = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
            }

            rsm_respond([
                'success' => true,
                'message' => $message,
                'debug'   => $debug,
                'data'    => ['report_id' => $newId, 'pdf_path' => $pdfPath],
            ]);
            break;

        // ------------------------------------------------------------------
        // POST Directress marks a submitted report as reviewed
        // ------------------------------------------------------------------
        case 'review':
            if (!$isDirectress) {
                rsm_respond(['success' => false, 'message' => 'Only the School Directress can review reports.']);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0);
            $notes    = trim($_POST['notes'] ?? '');
            if (!$reportId) {
                rsm_respond(['success' => false, 'message' => 'Report ID is required.']);
            }
            $ok = $reportClass->markReviewed($reportId, $userInfo['employee_id'], $notes !== '' ? $notes : null);
            rsm_respond(['success' => $ok, 'message' => $ok ? 'Report marked as reviewed.' : 'Unable to review this report.']);
            break;

        // ------------------------------------------------------------------
        // POST Directress approves/rejects a reviewed report
        // ------------------------------------------------------------------
        case 'decide':
            if (!$isDirectress) {
                rsm_respond(['success' => false, 'message' => 'Only the School Directress can decide on reports.']);
            }
            $reportId = (int) ($_POST['report_id'] ?? 0);
            $decision = $_POST['decision'] ?? '';
            $notes    = trim($_POST['notes'] ?? '');
            if (!$reportId || !in_array($decision, ['approved', 'rejected'], true)) {
                rsm_respond(['success' => false, 'message' => 'A valid report ID and decision are required.']);
            }
            $ok = $reportClass->decide($reportId, $userInfo['employee_id'], $decision, $notes !== '' ? $notes : null);
            rsm_respond(['success' => $ok, 'message' => $ok ? "Report {$decision}." : 'Unable to decide on this report.']);
            break;

        // ------------------------------------------------------------------
        // POST/GET generate an AI summary for a report
        // ------------------------------------------------------------------
        case 'summarize':
            $reportId = (int) ($_REQUEST['report_id'] ?? 0);
            if (!$reportId) {
                rsm_respond(['success' => false, 'message' => 'Report ID is required.']);
            }
            $result = $reportClass->generateAiSummary($reportId);
            rsm_respond($result);
            break;

        default:
            rsm_respond(['success' => false, 'message' => 'Unknown action.']);
            break;
    }
} catch (\Throwable $e) {
    error_log('[ReportController] Unhandled error: ' . $e->getMessage());
    rsm_respond([
        'success' => false,
        'message' => 'An unexpected server error occurred.',
        // TEMP debug info — remove the "debug" key once things are stable.
        'debug' => $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
    ]);
}

// ── Helpers ────────────────────────────────────────────────────────────────
function validateReportInput($post) {
    $title           = trim($post['title'] ?? '');
    $reportType      = (int) ($post['report_type'] ?? 0);
    $summary         = trim($post['summary'] ?? '');
    $findings        = trim($post['findings'] ?? '');
    $recommendations = trim($post['recommendations'] ?? '');

    if ($title === '') {
        return ['valid' => false, 'message' => 'Title is required.'];
    }
    if ($reportType === 0) {
        return ['valid' => false, 'message' => 'Please select a report type.'];
    }
    if ($summary === '') {
        return ['valid' => false, 'message' => 'Summary is required.'];
    }

    return [
        'valid'  => true,
        'fields' => [
            'title'           => $title,
            'report_type'     => $reportType,
            'summary'         => $summary,
            'findings'        => $findings,
            'recommendations' => $recommendations,
        ],
    ];
}