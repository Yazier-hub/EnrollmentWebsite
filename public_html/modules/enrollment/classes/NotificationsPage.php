<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';
require_once __DIR__ . '/NotificationsController.php';

/** Request handling and view data for notifications. */
final class NotificationsPage extends EnrollmentPage
{
    protected const VIEW = 'notifications';

    protected function loadData(): array
    {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start();
        if (empty($_SESSION['notifications_csrf'])) $_SESSION['notifications_csrf'] = bin2hex(random_bytes(32));
        $csrfToken = $_SESSION['notifications_csrf'];
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST'
            && (!isset($_POST['csrf']) || !is_string($_POST['csrf']) || !hash_equals($csrfToken, $_POST['csrf']))) {
            http_response_code(403);
            exit('Invalid CSRF token.');
        }
        $controller = new NotificationsController();
        $controller->handlePageActions();
        $data = $controller->index();
        $pageTitle = 'Notifications';

        $items       = $data['items']        ?? [];
        $total       = (int) ($data['total']      ?? 0);
        $page        = (int) ($data['page']       ?? 1);
        $pages       = (int) ($data['pages']      ?? 1);
        $filter      = (string) ($data['filter']  ?? 'all');
        $type        = (string) ($data['type']    ?? '');
        $unreadCount = (int) ($data['unread_count'] ?? 0);

        $types = [
            ''             => 'All types',
            'enrollment'   => 'Enrollment',
            'payment'      => 'Payment',
            'announcement' => 'Announcement',
            'system'       => 'System',
            'reminder'     => 'Reminder',
        ];

        /**
         * Build a query string for the current page, preserving filter/type,
         * optionally overriding keys, and always dropping action/id.
         * Closure — no global function collision.
         */
        $notifQs = static function (array $overrides = []) use ($filter, $type): string {
            $params = array_merge([
                'page'   => 'notifications',
                'filter' => $filter,
                'type'   => $type,
                'p'      => 1,
            ], $overrides);

            // Drop keys with null; keep empty strings and 0 for filter/type
            $params = array_filter($params, static fn($v) => $v !== null);

            return '?' . http_build_query($params);
        };

        $csrf = htmlspecialchars($csrfToken ?? '', ENT_QUOTES, 'UTF-8');

        return [
            'total' => $total ?? null,
            'unreadCount' => $unreadCount ?? null,
            'notifQs' => $notifQs ?? null,
            'csrf' => $csrf ?? null,
            'filter' => $filter ?? null,
            'types' => $types ?? null,
            'type' => $type ?? null,
            'items' => $items ?? null,
            'pages' => $pages ?? null,
            'page' => $page ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
