<?php
ob_start();

const UM_PER_PAGE = 10;

function um_respond(array $payload, int $code = 200) {
    if (ob_get_length()) {
        ob_clean(); // discard any stray warnings/notices before the JSON
    }
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (ob_get_length()) {
            ob_clean();
        }
        http_response_code(500);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Server error.']);
    }
});

try {
    // All includes live inside the try block
    include_once __DIR__ . '/../../../auth/session.php';
    include_once __DIR__ . '/../../../auth/guard.php';
    require_once __DIR__ . '/../classes/Employee.php';

    $action = $_GET['action'] ?? '';

    $filters = [
        'search'     => trim((string) ($_GET['search'] ?? '')),
        'department' => trim((string) ($_GET['department'] ?? '')),
        'position'   => trim((string) ($_GET['position'] ?? '')),
        'status'     => trim((string) ($_GET['status'] ?? '')),
    ];

    $model = new Employee();

    switch ($action) {

        case 'list':
            $page    = max(1, (int) ($_GET['page'] ?? 1));
            $total   = $model->countEmployeesFiltered($filters);
            $pages   = max(1, (int) ceil($total / UM_PER_PAGE));
            if ($page > $pages) {
                $page = $pages;
            }
            $rows = $model->getEmployeesFiltered($filters, UM_PER_PAGE, ($page - 1) * UM_PER_PAGE);

            um_respond([
                'success' => true,
                'data'    => [
                    'rows'     => $rows,
                    'total'    => $total,
                    'page'     => $page,
                    'pages'    => $pages,
                    'per_page' => UM_PER_PAGE,
                ],
            ]);
            break;

        case 'export':
            um_respond([
                'success' => true,
                'data'    => ['rows' => $model->getEmployeesFiltered($filters)],
            ]);
            break;

        default:
            um_respond(['success' => false, 'message' => 'Unknown action.'], 400);
    }

} catch (\Throwable $e) {
    error_log('UserManagementController: ' . $e->getMessage());
    um_respond(['success' => false, 'message' => 'Unable to load employees.'], 500);
}