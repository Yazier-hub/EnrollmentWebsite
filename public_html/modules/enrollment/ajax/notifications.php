<?php
require_once __DIR__ . '/../../../auth/session.php';
require_once __DIR__ . '/../classes/Notification.php';

header('Content-Type: application/json');

$notif  = new Notification();
$action = $_POST['action'] ?? $_GET['action'] ?? 'list';

try {
    switch ($action) {
        case 'list':
            $items = array_map(static function ($n) {
                return [
                    'id'       => (int) $n['notification_id'],
                    'title'    => $n['title'],
                    'message'  => $n['message'],
                    'type'     => $n['type'],
                    'is_read'  => (bool) $n['is_read'],
                    'time_ago' => Notification::timeAgo($n['created_at']),
                ];
            }, $notif->getRecent(8));

            echo json_encode([
                'items'        => $items,
                'unread_count' => $notif->getUnreadCount(),
            ]);
            break;

        case 'mark_all_read':
            $notif->markAllRead();
            echo json_encode(['success' => true, 'unread_count' => 0]);
            break;

        case 'mark_read':
            $id = (int) ($_POST['id'] ?? 0);
            if ($id > 0) $notif->markRead($id);
            echo json_encode(['success' => true, 'unread_count' => $notif->getUnreadCount()]);
            break;

        default:
            http_response_code(400);
            echo json_encode(['error' => 'Unknown action']);
    }
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server error']);
}