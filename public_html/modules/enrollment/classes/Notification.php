<?php
require_once __DIR__ . '/Database.php';

class Notification
{
    private PDO $db;

    public function __construct(?PDO $db = null)
    {
        if ($db !== null) {
            $this->db = $db;
            return;
        }
        $this->db = Database::getInstance()->getConnection();
    }

    /** Latest unread notifications for the bell dropdown. */
    public function getRecent(int $limit = 8): array
    {
        $limit = max(1, min(50, $limit)); // clamp

        $sql = "SELECT notification_id, title, message, type, reference_id,
                       is_read, created_at, read_at
                FROM enr_notifications
                WHERE is_read = 0
                ORDER BY created_at DESC, notification_id DESC
                LIMIT :limit";
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Unread count for the bell badge. */
    public function getUnreadCount(): int
    {
        return (int) $this->db->query(
            "SELECT COUNT(*) FROM enr_notifications WHERE is_read = 0"
        )->fetchColumn();
    }

    public function markAllRead(): void
    {
        $this->db->exec(
            "UPDATE enr_notifications
             SET is_read = 1, read_at = NOW()
             WHERE is_read = 0"
        );
    }

    public function markRead(int $id): void
    {
        $stmt = $this->db->prepare(
            "UPDATE enr_notifications
             SET is_read = 1, read_at = NOW()
             WHERE notification_id = :id AND is_read = 0"
        );
        $stmt->execute([':id' => $id]);
    }

    /** Paginated list for the "View all notifications" page. */
    public function paginate(int $page = 1, int $perPage = 20, string $filter = 'all', string $type = ''): array
    {
        $page    = max(1, $page);
        $perPage = max(1, min(100, $perPage));

        $where  = [];
        $params = [];

        if ($filter === 'unread') {
            $where[] = 'is_read = 0';
        } elseif ($filter === 'read') {
            $where[] = 'is_read = 1';
        }

        $validTypes = ['enrollment', 'payment', 'announcement', 'system', 'reminder'];
        if ($type !== '' && in_array($type, $validTypes, true)) {
            $where[] = 'type = :type';
            $params[':type'] = $type;
        }

        $whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $countStmt = $this->db->prepare("SELECT COUNT(*) FROM enr_notifications $whereSql");
        $countStmt->execute($params);
        $total = (int) $countStmt->fetchColumn();

        $offset = ($page - 1) * $perPage;

        $sql = "SELECT notification_id, title, message, type, reference_id,
                       is_read, created_at, read_at
                FROM enr_notifications
                $whereSql
                ORDER BY is_read ASC, created_at DESC
                LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);
        $stmt->execute();

        return [
            'items' => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total' => $total,
        ];
    }

    public static function timeAgo(string $datetime): string
    {
        $ts   = strtotime($datetime);
        $diff = time() - $ts;
        if ($diff < 60)     return 'Just now';
        if ($diff < 3600)   return floor($diff / 60) . 'm ago';
        if ($diff < 86400)  return floor($diff / 3600) . 'h ago';
        if ($diff < 604800) return floor($diff / 86400) . 'd ago';
        return date('M j', $ts);
    }

    public static function typeIcon(string $type): string
    {
        return [
            'enrollment'   => 'fa-solid fa-user-graduate',
            'payment'      => 'fa-solid fa-credit-card',
            'announcement' => 'fa-solid fa-bullhorn',
            'system'       => 'fa-solid fa-gear',
            'reminder'     => 'fa-solid fa-clock',
        ][$type] ?? 'fa-solid fa-bell';
    }
}
