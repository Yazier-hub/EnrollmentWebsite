<?php
require_once __DIR__ . '/EnrollmentPage.php';

/** Request handling and view data for logout. */
final class LogoutPage extends EnrollmentPage
{
    protected const VIEW = 'logout';

    protected function loadData(): array
    {
        // modules/enrollment/pages/logout.php
        // Destroy the session and redirect to the live login page.

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Clear all session variables.
        $_SESSION = [];

        // Expire the session cookie.
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();

            setcookie(
                session_name(),
                '',
                [
                    'expires'  => time() - 42000,
                    'path'     => $params['path'],
                    'domain'   => $params['domain'],
                    'secure'   => $params['secure'],
                    'httponly' => $params['httponly'],
                    'samesite' => $params['samesite'] ?? 'Lax',
                ]
            );
        }

        // Destroy the session.
        session_destroy();

        header('Location: /');
        exit;

    }
}
