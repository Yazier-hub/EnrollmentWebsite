<?php
// auth/login.php
// PHP warnings and database exceptions must not corrupt the JSON response.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
header('Content-Type: application/json; charset=utf-8');

set_exception_handler(function (Throwable $error) {
    error_log('[auth/login] ' . get_class($error) . ': ' . $error->getMessage()
        . ' in ' . $error->getFile() . ':' . $error->getLine());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'locked' => false,
        'message' => 'Login service is unavailable. Please contact the administrator.',
    ]);
});

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    header('Allow: POST');
    echo json_encode(['success' => false, 'message' => 'Use POST to log in.']);
    exit();
}

if (!isset($_POST['employee_id'], $_POST['password'])
    || !is_string($_POST['employee_id']) || !is_string($_POST['password'])
    || trim($_POST['employee_id']) === '' || $_POST['password'] === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Enter your Employee ID and password.']);
    exit();
}

if (!session_start()) {
    throw new RuntimeException('Unable to start the login session. Check session storage permissions.');
}

require_once __DIR__ . '/../database/db.php';

$db   = new Database();
$conn = $db->getConnection();

define('MAX_ATTEMPTS', 3);
define('LOCKOUT_TIME', 60); // 60 seconds lockout

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employeeid = trim($_POST['employee_id']);
    $password   = $_POST['password'];

    $ip  = $_SERVER['REMOTE_ADDR'];
    $key = 'login_attempts_' . $ip . '_' . $employeeid;

    if (!isset($_SESSION[$key])) {
        $_SESSION[$key] = ['count' => 0, 'last_attempt' => time()];
    }

    $attempts = &$_SESSION[$key];
    $elapsed  = time() - $attempts['last_attempt'];

    if ($elapsed > LOCKOUT_TIME) {
        $attempts = ['count' => 0, 'last_attempt' => time()];
        $elapsed  = 0;
    }

    if ($attempts['count'] >= MAX_ATTEMPTS) {
        $remaining_seconds = max(0, (int)(LOCKOUT_TIME - $elapsed));
        echo json_encode([
            'success'           => false,
            'locked'            => true,
            'remaining_seconds' => $remaining_seconds,
            'message'           => 'Too many failed attempts.',
        ]);
        exit();
    }

    // ── Authentication ────────────────────────────────────────────────────────
    $stmt = $conn->prepare("
        SELECT
            user_account.user_id,
            user_account.employee_id,
            user_account.password,
            em_employees.role_id AS role,
            em_employees.department_id AS department,
            em_employees.employment_status,
            em_roles.role_name,
            em_departments.department_name
        FROM user_account
        INNER JOIN em_employees
            ON em_employees.employee_id = user_account.employee_id
        LEFT JOIN em_roles
            ON em_roles.role_id = em_employees.role_id
        LEFT JOIN em_departments
            ON em_departments.department_id = em_employees.department_id
        WHERE user_account.employee_id = :employeeid
        LIMIT 1
    ");

    $stmt->execute([':employeeid' => $employeeid]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        unset($_SESSION[$key]);

        // ⭐ FIX #1: Regenerate session ID (security + prevents fixation)
        // ⭐ FIX #2: Idagdag ang user_id — ito ang hinahanap ng payment verification
        session_regenerate_id(true);

        $_SESSION['user_id']         = (int) $user['user_id'];
        $_SESSION['employee_id']     = $user['employee_id'];
        $_SESSION['role']            = (int) $user['role'];
        $_SESSION['role_name']       = $user['role_name'];
        $_SESSION['department_id']   = (int) $user['department'];
        $_SESSION['department_name'] = $user['department_name'];
        $_SESSION['login_time']      = time();  // ⭐ For timeout checks

        $redirectMap = [
            14 => 'modules/school-directress/index.php',
            15 => 'modules/guidance/index.php',
            16 => 'modules/registrar/',
            17 => 'modules/enrollment/index.php',
            18 => 'modules/monitoring/index.php',
            19 => 'modules/clinic/index.php',
            20 => 'modules/library/index.php',
            21 => 'modules/laboratory/',
            22 => 'modules/college-coor/index.php',
        ];

        $role = (int) $user['role'];

        if (!isset($redirectMap[$role])) {
            echo json_encode(['success' => false, 'locked' => false, 'message' => 'Invalid role.']);
            exit();
        }

        echo json_encode([
            'success'  => true,
            'redirect' => $redirectMap[$role],
        ]);
        exit();

    } else {
        $attempts['count']++;
        $attempts['last_attempt'] = time();

        $remaining_attempts = MAX_ATTEMPTS - $attempts['count'];

        if ($remaining_attempts <= 0) {
            echo json_encode([
                'success'           => false,
                'locked'            => true,
                'remaining_seconds' => (int) LOCKOUT_TIME,
                'message'           => 'Too many failed attempts. Account locked.',
            ]);
        } else {
            echo json_encode([
                'success'            => false,
                'locked'             => false,
                'remaining_attempts' => $remaining_attempts,
                'message'            => 'Invalid Employee ID or Password.',
            ]);
        }
        exit();
    }
}
