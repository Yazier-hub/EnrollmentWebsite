<?php
require_once __DIR__ . '/Notification.php';

class NotificationsController
{
    private Notification $notifications;
    private int $perPage = 20;

    public function __construct(?Notification $notifications = null)
    {
        $this->notifications = $notifications ?? new Notification();
    }

    /* =========================================================
     *  AJAX  — bell dropdown
     * ========================================================= */

    public function handleAjax(): void
    {
        // Always emit JSON — never let a fatal escape as HTML
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');

        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';

        try {
            switch ($action) {
                case 'list':
                    echo json_encode([
                        'items'        => $this->serializeItems($this->notifications->getRecent(8)),
                        'unread_count' => $this->notifications->getUnreadCount(),
                    ]);
                    break;

                case 'mark_all_read':
                    $this->notifications->markAllRead();
                    echo json_encode(['success' => true, 'unread_count' => 0]);
                    break;

                case 'mark_read':
                    $id = (int) ($_POST['id'] ?? 0);
                    if ($id > 0) {
                        $this->notifications->markRead($id);
                    }
                    echo json_encode([
                        'success'      => true,
                        'unread_count' => $this->notifications->getUnreadCount(),
                    ]);
                    break;

                default:
                    http_response_code(400);
                    echo json_encode(['error' => 'Unknown action']);
            }
        } catch (Throwable $e) {
            if (!headers_sent()) {
                http_response_code(500);
            }
            echo json_encode(['error' => 'Server error']);
        }
    }

    /* =========================================================
     *  PAGE  — "View all notifications"
     * ========================================================= */

    public function index(): array
    {
        $page   = max(1, (int) ($_GET['p'] ?? 1));
        $filter = (string) ($_GET['filter'] ?? 'all');
        $type   = (string) ($_GET['type']   ?? '');

        // Normalize inputs (never echo raw back to the view)
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }

        $validTypes = ['enrollment', 'payment', 'announcement', 'system', 'reminder'];
        if ($type !== '' && !in_array($type, $validTypes, true)) {
            $type = '';
        }

        $result = $this->notifications->paginate($page, $this->perPage, $filter, $type);

        $total  = (int) $result['total'];
        $items  = $result['items'];
        $pages  = $perPage = $this->perPage ? (int) ceil($total / $this->perPage) : 1;

        return [
            'items'        => $this->serializeItems($items),
            'total'        => $total,
            'page'         => $page,
            'pages'        => $pages,
            'per_page'     => $this->perPage,
            'filter'       => $filter,
            'type'         => $type,
            'unread_count' => $this->notifications->getUnreadCount(),
        ];
    }

    /**
     * POST handler for the page: mark single/all as read.
     * Redirects back to the same query string (with page/filter/type preserved,
     * and action/id/p stripped) so the user never lands on the wrong page.
     */
    public function handlePageActions(): void
    {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $action = $_POST['action'] ?? '';

        if ($action === 'mark_all_read') {
            $this->notifications->markAllRead();
        } elseif ($action === 'mark_read') {
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) {
                $this->notifications->markRead($id);
            }
        }

        // Rebuild the redirect URL from the current query string,
        // stripping action/id/p so they don't leak forward.
        $redirect = $this->buildRedirectUrl();

        header('Location: ' . $redirect, true, 303);
        exit;
    }

    /* =========================================================
     *  Helpers
     * ========================================================= */

    /**
     * Build a safe redirect back to the notifications page,
     * preserving page/filter/type but resetting p and dropping action/id.
     */
    private function buildRedirectUrl(): string
    {
        // Start from current query string, which is guaranteed to have
        // at least `page=notifications` if the router used ?page=...
        $qs = $_SERVER['QUERY_STRING'] ?? '';
        parse_str($qs, $params);

        // Enforce this is the notifications page
        $params['page'] = 'notifications';

        // Drop transient fields
        unset($params['action'], $params['id']);

        // Normalize filter/type
        $filter = $params['filter'] ?? 'all';
        if (!in_array($filter, ['all', 'unread', 'read'], true)) {
            $filter = 'all';
        }
        $params['filter'] = $filter;

        $validTypes = ['enrollment', 'payment', 'announcement', 'system', 'reminder'];
        if (!empty($params['type']) && !in_array($params['type'], $validTypes, true)) {
            $params['type'] = '';
        }

        // Preserve current page number if it exists
        $p = max(1, (int) ($params['p'] ?? 1));
        unset($params['p']);
        if ($p > 1) {
            $params['p'] = $p;
        }

        return '?' . http_build_query($params);
    }

    private function serializeItems(array $rows): array
    {
        return array_map(static function ($n) {
            return [
                'id'       => (int) $n['notification_id'],
                'title'    => (string) $n['title'],
                'message'  => (string) $n['message'],
                'type'     => (string) $n['type'],
                'is_read'  => (bool) $n['is_read'],
                'time_ago' => Notification::timeAgo($n['created_at']),
                'icon'     => Notification::typeIcon($n['type']),
                'created'  => $n['created_at'],
            ];
        }, $rows);
    }
}