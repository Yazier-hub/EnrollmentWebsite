<?php
// View variables supplied by EnrollmentPage::render() and SidebarView::data().
/** @var string $logoUrl */
/** @var string $logoutUrl */
/** @var string $userName */
/** @var string $userPosition */
/** @var string $userAvatar */
/** @var int $unreadCount */
/** @var array<int, array<string, mixed>> $notifications */
/** @var array<string, string> $typeIcons */
/** @var PageController|null $pageController */
?>
<aside class="sidebar">
    <div class="school-logo">
        <img src="<?= htmlspecialchars($logoUrl) ?>" alt="School Logo">
        <div class="sidebar-icons">

            <div class="icon-wrapper" id="bellWrapper">
                <i class="fa-regular fa-bell" id="bellBtn"></i>
                <span class="notif-badge <?= $unreadCount ? '' : 'hidden' ?>" id="notifBadge">
                    <?= $unreadCount > 99 ? '99+' : (int) $unreadCount ?>
                </span>

                <div class="icon-dropdown" id="bellDropdown">
                    <div class="dropdown-header">
                        <span>Notifications</span>
                        <button type="button" class="mark-all-read">Mark all as read</button>
                    </div>

                    <ul class="notif-list" id="notifList">
                        <?php if (empty($notifications)): ?>
                            <li class="notif-empty">No unread notifications.</li>
                        <?php else: ?>
                            <?php foreach ($notifications as $n):
                                $icon   = $typeIcons[$n['type']] ?? 'fa-solid fa-bell';
                                $unread = !(bool) $n['is_read'];
                            ?>
                            <li class="notif-item <?= $unread ? 'unread' : '' ?>"
                                data-id="<?= (int) $n['notification_id'] ?>">
                                <div class="notif-icon notif-type-<?= htmlspecialchars($n['type']) ?>">
                                    <i class="<?= $icon ?>"></i>
                                </div>
                                <div class="notif-body">
                                    <strong><?= htmlspecialchars($n['title']) ?></strong>
                                    <p><?= htmlspecialchars($n['message']) ?></p>
                                    <span class="notif-time">
                                        <?= htmlspecialchars(Notification::timeAgo($n['created_at'])) ?>
                                    </span>
                                </div>
                            </li>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </ul>

                    <div class="dropdown-footer">
                        <a href="?page=notifications">View all notifications</a>
                    </div>
                </div>
            </div>

            <div class="icon-wrapper" id="userWrapper">
                <i class="fa-regular fa-circle-user" id="userBtn"></i>
                <div class="icon-dropdown" id="userDropdown">
                    <div class="dropdown-header">
                        <div class="dropdown-user-info">
                            <div class="dropdown-avatar">
                                <?= htmlspecialchars($userAvatar) ?>
                            </div>
                            <div>
                                <strong><?= htmlspecialchars($userName) ?></strong>
                                <span><?= htmlspecialchars($userPosition) ?></span>
                            </div>
                        </div>
                    </div>
                    <ul class="user-menu">
                        <li>
                            <a href="#"><i class="fa-regular fa-user"></i> Profile Settings</a>
                        </li>
                        <li>
                            <a href="#"><i class="fa-solid fa-lock"></i> Change Password</a>
                        </li>
                        <li class="divider"></li>
                        <li>
                            <a href="<?= htmlspecialchars($logoutUrl) ?>" class="signout-link">
                                <i class="fa-solid fa-right-from-bracket"></i> Sign Out
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

    <div class="sidebar-header">
        <div class="user_avatar"><?= htmlspecialchars($userAvatar) ?></div>
        <h1 class="employee_name"><?= htmlspecialchars($userName) ?></h1>
        <p class="employee_position"><?= htmlspecialchars($userPosition) ?></p>
    </div>

    <h2>Enrollment Dashboard</h2>
    <ul>
        <?php
        if (isset($pageController) && method_exists($pageController, 'renderNav')) {
            $pageController->renderNav();
        } else {
            $currentPage = $_GET['page'] ?? 'dashboard-overview';
            $navItems = [
                'dashboard-overview'  => ['label' => 'Dashboard',            'icon' => 'fa-solid fa-chart-pie'],
                'applications'        => ['label' => 'Applications',         'icon' => 'fa-solid fa-file-pen'],
                'enrollments'         => ['label' => 'Enrollments',          'icon' => 'fa-solid fa-user-graduate'],
                'students'            => ['label' => 'Students',             'icon' => 'fa-solid fa-users'],
                'courses'             => ['label' => 'Courses',              'icon' => 'fa-solid fa-book'],
                'sections'            => ['label' => 'Sections',             'icon' => 'fa-solid fa-layer-group'],
                'requirements'        => ['label' => 'Requirements',         'icon' => 'fa-solid fa-list-check'],
                'semesters'           => ['label' => 'Semester Management',  'icon' => 'fa-solid fa-calendar-days'],
                'archived-students'   => ['label' => 'Archived Students',    'icon' => 'fa-solid fa-box-archive'],
            ];

            foreach ($navItems as $page => $item) {
                $active = ($currentPage === $page) ? 'active-menu-link' : 'menu-link';
                printf(
                    '<li><a href="?page=%s" class="%s" data-page="%s"><i class="%s"></i> %s</a></li>',
                    htmlspecialchars($page),
                    $active,
                    htmlspecialchars($page),
                    htmlspecialchars($item['icon']),
                    htmlspecialchars($item['label'])
                );
            }
        }
        ?>

        <li class="sidebar-logout-item">
            <a href="<?= htmlspecialchars($logoutUrl) ?>" class="menu-link sidebar-logout-link">
                <i class="fa-solid fa-right-from-bracket"></i>
                <span>Sign Out</span>
            </a>
        </li>
    </ul>
</aside>
