<?php
require_once __DIR__ . '/Employee.php';
require_once __DIR__ . '/Notification.php';
require_once __DIR__ . '/PageAssets.php';

/** Supplies employee identity and unread notifications to the shared sidebar. */
final class SidebarView
{
    public function data(): array
    {
        $employee = new Employee();
        $userName = $employee->getEmployeeName();
        $initials = '';
        foreach (preg_split('/\s+/', trim($userName)) as $part) {
            if ($part !== '') $initials .= mb_strtoupper(mb_substr($part, 0, 1));
            if (mb_strlen($initials) >= 2) break;
        }
        $notifications = new Notification();

        return [
            'userName' => $userName,
            'userPosition' => $employee->getEmployeePosition(),
            'userAvatar' => $initials !== '' ? $initials : '?',
            'logoUrl' => PageAssets::baseUrl() . 'assets/bcp-logo.png',
            'logoutUrl' => '?page=logout',
            'notifications' => $notifications->getRecent(8),
            'unreadCount' => $notifications->getUnreadCount(),
            'typeIcons' => [
                'enrollment' => 'fa-solid fa-user-graduate',
                'payment' => 'fa-solid fa-credit-card',
                'announcement' => 'fa-solid fa-bullhorn',
                'system' => 'fa-solid fa-gear',
                'reminder' => 'fa-solid fa-clock',
            ],
        ];
    }
}
