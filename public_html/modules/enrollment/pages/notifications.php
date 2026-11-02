<?php
// View data supplied by NotificationsPage through EnrollmentPage.
/** @var int $total */
/** @var int $unreadCount */
/** @var Closure $notifQs */
/** @var string $csrf */
/** @var string $filter */
/** @var array<string, string> $types */
/** @var string $type */
/** @var array<int, array<string, mixed>> $items */
/** @var int $pages */
/** @var int $page */
?>
<main class="main-content">
<div class="notifications-page">

    <div class="page-header">
        <div>
            <h1>Notifications</h1>
            <p class="muted">
                <?= $total ?> total
                <?php if ($unreadCount > 0): ?>
                    · <strong><?= $unreadCount ?> unread</strong>
                <?php endif; ?>
            </p>
        </div>

        <?php if ($unreadCount > 0): ?>
            <form method="post" action="<?= htmlspecialchars($notifQs(), ENT_QUOTES, 'UTF-8') ?>" class="inline-form">
                <input type="hidden" name="csrf"   value="<?= $csrf ?>">
                <input type="hidden" name="action" value="mark_all_read">
                <button type="submit" class="btn btn-secondary">
                    <i class="fa-solid fa-check-double"></i> Mark all as read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <!-- Filters -->
    <div class="notif-filters">
        <div class="filter-group">
            <span class="filter-label">Show:</span>
            <?php foreach (['all' => 'All', 'unread' => 'Unread', 'read' => 'Read'] as $key => $label): ?>
                <a href="<?= htmlspecialchars($notifQs(['filter' => $key, 'p' => 1]), ENT_QUOTES, 'UTF-8') ?>"
                   class="filter-pill<?= $filter === $key ? ' active' : '' ?>">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>

        <form method="get" class="filter-group">
            <input type="hidden" name="page"   value="notifications">
            <input type="hidden" name="filter" value="<?= htmlspecialchars($filter, ENT_QUOTES, 'UTF-8') ?>">
            <select name="type" data-action-change="action1" class="filter-select">
                <?php foreach ($types as $val => $label): ?>
                    <option value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>"
                        <?= $type === $val ? 'selected' : '' ?>>
                        <?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <!-- List -->
    <ul class="notif-page-list">
        <?php if (empty($items)): ?>
            <li class="notif-empty">No notifications found.</li>
        <?php else: ?>
            <?php foreach ($items as $n):
                $createdTs = strtotime((string) ($n['created'] ?? '')) ?: time();
                $createdFmt = date('M j, Y g:i A', $createdTs);
                $unread     = empty($n['is_read']);
            ?>
                <li class="notif-page-item<?= $unread ? ' unread' : '' ?>">
                    <div class="notif-icon notif-type-<?= htmlspecialchars($n['type'], ENT_QUOTES, 'UTF-8') ?>">
                        <i class="<?= htmlspecialchars($n['icon'], ENT_QUOTES, 'UTF-8') ?>"></i>
                    </div>

                    <div class="notif-page-body">
                        <div class="notif-page-head">
                            <strong><?= htmlspecialchars($n['title'], ENT_QUOTES, 'UTF-8') ?></strong>
                            <span class="notif-badge-type"><?= htmlspecialchars($n['type'], ENT_QUOTES, 'UTF-8') ?></span>
                        </div>
                        <p><?= nl2br(htmlspecialchars($n['message'], ENT_QUOTES, 'UTF-8')) ?></p>
                        <span class="notif-time">
                            <?= htmlspecialchars($n['time_ago'], ENT_QUOTES, 'UTF-8') ?>
                            · <?= htmlspecialchars($createdFmt, ENT_QUOTES, 'UTF-8') ?>
                        </span>
                    </div>

                    <?php if ($unread): ?>
                        <form method="post" action="<?= htmlspecialchars($notifQs(), ENT_QUOTES, 'UTF-8') ?>" class="notif-mark-form">
                            <input type="hidden" name="csrf"   value="<?= $csrf ?>">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="id"     value="<?= (int) $n['id'] ?>">
                            <button type="submit" class="icon-btn" title="Mark as read">
                                <i class="fa-solid fa-check"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <!-- Pagination -->
    <?php if ($pages > 1): ?>
        <nav class="pagination">
            <?php if ($page > 1): ?>
                <a href="<?= htmlspecialchars($notifQs(['p' => $page - 1]), ENT_QUOTES, 'UTF-8') ?>" class="page-link">« Prev</a>
            <?php endif; ?>

            <?php
            $start = max(1, $page - 2);
            $end   = min($pages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
                <a href="<?= htmlspecialchars($notifQs(['p' => $i]), ENT_QUOTES, 'UTF-8') ?>"
                   class="page-link<?= $i === $page ? ' active' : '' ?>">
                    <?= $i ?>
                </a>
            <?php endfor; ?>

            <?php if ($page < $pages): ?>
                <a href="<?= htmlspecialchars($notifQs(['p' => $page + 1]), ENT_QUOTES, 'UTF-8') ?>" class="page-link">Next »</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

</div>
</main>
