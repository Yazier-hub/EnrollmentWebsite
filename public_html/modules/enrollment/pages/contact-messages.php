<?php
// View data supplied by ContactMessagesPage through EnrollmentPage.
/** @var string $currentDateTime */
/** @var int $unreadCount */
/** @var array<string, mixed>|null $flash */
/** @var array<string, mixed> $counts */
/** @var string $filter */
/** @var ContactMessagesController $ctrl */
/** @var string $search */
/** @var array<int, array<string, mixed>> $messages */
/** @var array<string, mixed>|null $activeMessage */
/** @var array<int, array<string, mixed>> $replies */
?>
<main class="main-content">
    <div class="container">

        <div class="module-header u-display-flex-f7db u-justify-content-space-between-daae u-align-items-center-1e2c u-flex-wrap-wrap-9012 u-gap-15px-7d09">
            <div>
                <h1>✉️ Contact Messages</h1>
                <p class="dashboard-subtitle">
                    Inbox for visitor inquiries • <?php echo PageView::escape($currentDateTime); ?>
                    <?php if ($unreadCount > 0): ?>
                        • <strong class="u-color-b91c1c-5387"><?php echo $unreadCount; ?> unread</strong>
                    <?php endif; ?>
                </p>
            </div>
            <div class="u-display-flex-f7db u-gap-10px-114b u-flex-wrap-wrap-9012">
                <?php if ($unreadCount > 0): ?>
                <form method="post" data-action-submit="action1" class="u-display-inline-5677">
                    <input type="hidden" name="action" value="mark_all_read">
                    <button type="submit" class="btn btn-success u-padding-10px-20px-cc5c u-font-weight-600-e634">
                        ✓ Mark All Read
                    </button>
                </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($flash)): ?>
            <div class="flash flash-<?php echo PageView::escape($flash['type']); ?>">
                <?php echo PageView::escape($flash['text']); ?>
            </div>
        <?php endif; ?>

        <div class="module-content">

            <!-- FILTER TABS -->
            <div class="filter-tabs">
                <?php
                $tabs = [
                    'all'    => ['label' => 'All',    'count' => $counts['all']],
                    'unread' => ['label' => 'Unread', 'count' => $counts['unread']],
                    'read'   => ['label' => 'Read',   'count' => $counts['read']],
                ];
                foreach ($tabs as $key => $tab):
                    $activeTab = $filter === $key ? ' active' : '';
                ?>
                    <a class="filter-tab<?php echo $activeTab; ?>" href="<?php echo PageView::escape($ctrl->urlTab($key)); ?>">
                        <?php echo PageView::escape($tab['label']); ?>
                        <span class="tab-count"><?php echo (int) $tab['count']; ?></span>
                    </a>
                <?php endforeach; ?>

                <form method="get" class="filter-search">
                    <input type="hidden" name="page" value="contact-messages">
                    <input type="hidden" name="filter" value="<?php echo PageView::escape($filter); ?>">
                    <input type="text" name="q" value="<?php echo PageView::escape($search); ?>"
                           placeholder="Search name, email, subject…">
                    <button type="submit" class="btn btn-primary">🔍</button>
                    <?php if ($search !== ''): ?>
                        <a href="<?php echo PageView::escape($ctrl->urlClear()); ?>" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- INBOX -->
            <div class="inbox">

                <!-- LIST -->
                <div class="inbox-list">
                    <form method="post" id="bulkForm">
                        <input type="hidden" name="action" value="bulk_delete">

                        <div class="bulk-bar">
                            <label class="bulk-check">
                                <input type="checkbox" id="selectAll">
                                <span>Select all</span>
                            </label>
                            <button type="submit" class="btn btn-danger" data-action-click="action2">
                                🗑 Delete Selected
                            </button>
                        </div>

                        <?php if (empty($messages)): ?>
                            <div class="empty-state">
                                <div class="u-font-size-2-5rem-b178">📭</div>
                                <p>No messages <?php echo $filter !== 'all' ? 'in this filter' : 'yet'; ?>.</p>
                                <?php if ($search !== ''): ?>
                                    <p class="muted">No results for "<strong><?php echo PageView::escape($search); ?></strong>".</p>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $m): ?>
                                <?php
                                    $isActive = $activeMessage && (int) $activeMessage['id'] === (int) $m['id'];
                                    $rowCls   = 'msg-row' . ($m['is_read'] ? '' : ' unread') . ($isActive ? ' active' : '');
                                ?>
                                <div class="<?php echo $rowCls; ?>">
                                    <input type="checkbox" name="ids[]" value="<?php echo (int) $m['id']; ?>" class="row-check">
                                    <a class="msg-link" href="<?php echo PageView::escape($ctrl->urlView((int) $m['id'])); ?>">
                                        <div class="msg-top">
                                            <span class="msg-name">
                                                <?php if (!$m['is_read']): ?><span class="dot"></span><?php endif; ?>
                                                <?php echo PageView::escape($m['name']); ?>
                                            </span>
                                            <span class="msg-time"><?php echo PageView::escape(PageView::timeAgo($m['created_at'])); ?></span>
                                        </div>
                                        <div class="msg-subject"><?php echo PageView::escape($m['subject']); ?></div>
                                        <div class="msg-preview"><?php echo PageView::escape(mb_strimwidth($m['message'], 0, 90, '…')); ?></div>
                                        <div class="msg-email"><?php echo PageView::escape($m['email']); ?></div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </form>
                </div>

                <!-- READER -->
                <div class="inbox-reader">
                    <?php if ($activeMessage): ?>
                        <div class="reader-header">
                            <a href="?<?php echo PageView::escape($ctrl->currentQuery()); ?>" class="btn btn-secondary">← Back</a>
                            <div class="reader-actions">
                                <?php if ($activeMessage['is_read']): ?>
                                    <form method="post" class="u-display-inline-5677">
                                        <input type="hidden" name="action" value="mark_unread">
                                        <input type="hidden" name="id" value="<?php echo (int) $activeMessage['id']; ?>">
                                        <button class="btn btn-info" type="submit">📩 Mark Unread</button>
                                    </form>
                                <?php else: ?>
                                    <form method="post" class="u-display-inline-5677">
                                        <input type="hidden" name="action" value="mark_read">
                                        <input type="hidden" name="id" value="<?php echo (int) $activeMessage['id']; ?>">
                                        <button class="btn btn-success" type="submit">✓ Mark Read</button>
                                    </form>
                                <?php endif; ?>

                                <form method="post" data-action-submit="action3" class="u-display-inline-5677">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?php echo (int) $activeMessage['id']; ?>">
                                    <button class="btn btn-danger" type="submit">🗑 Delete</button>
                                </form>
                            </div>
                        </div>

                        <h2 class="reader-subject"><?php echo PageView::escape($activeMessage['subject']); ?></h2>

                        <div class="reader-meta">
                            <div class="avatar"><?php echo PageView::escape(mb_strtoupper(mb_substr($activeMessage['name'], 0, 1))); ?></div>
                            <div>
                                <div class="reader-from">
                                    <strong><?php echo PageView::escape($activeMessage['name']); ?></strong>
                                    &lt;<a href="mailto:<?php echo PageView::escape($activeMessage['email']); ?>"><?php echo PageView::escape($activeMessage['email']); ?></a>&gt;
                                </div>
                                <div class="reader-date">
                                    <?php echo PageView::escape(date('M d, Y h:i A', strtotime($activeMessage['created_at']))); ?>
                                    • <?php echo PageView::escape(PageView::timeAgo($activeMessage['created_at'])); ?>
                                </div>
                            </div>
                        </div>

                        <div class="reader-body">
                            <?php echo nl2br(PageView::escape($activeMessage['message'])); ?>
                        </div>

                        <!-- REPLY FORM -->
                        <div class="reply-section">
                            <h3 class="reply-title">↩ Reply to <?php echo PageView::escape($activeMessage['name']); ?></h3>

                            <form method="post" class="reply-form">
                                <input type="hidden" name="action" value="reply">
                                <input type="hidden" name="id" value="<?php echo (int) $activeMessage['id']; ?>">
                                <input type="hidden" name="to_email" value="<?php echo PageView::escape($activeMessage['email']); ?>">
                                <input type="hidden" name="to_name" value="<?php echo PageView::escape($activeMessage['name']); ?>">

                                <label class="reply-label">To</label>
                                <input type="text" value="<?php echo PageView::escape($activeMessage['email']); ?>"
                                       readonly class="reply-input readonly">

                                <label class="reply-label">Subject</label>
                                <input type="text" name="reply_subject"
                                       value="Re: <?php echo PageView::escape($activeMessage['subject']); ?>"
                                       required class="reply-input">

                                <label class="reply-label">Message</label>
                                <textarea name="reply_body" rows="6" required
                                          class="reply-input"
                                          placeholder="Type your reply…"></textarea>

                                <button type="submit" class="btn btn-primary reply-submit">
                                    📤 Send Reply
                                </button>
                            </form>
                        </div>

                        <!-- REPLY HISTORY -->
                        <?php if (!empty($replies)): ?>
                            <div class="reply-history">
                                <h3 class="reply-history-title">📜 Reply History (<?php echo count($replies); ?>)</h3>
                                <?php foreach ($replies as $r): ?>
                                    <?php $ok = $r['status'] === 'sent'; ?>
                                    <div class="reply-item u-dynamic-border-left-color" data-css-border-left-color="<?php echo $ok ? '#2a5c9e' : '#ef4444'; ?>">
                                        <div class="reply-meta">
                                            <strong><?php echo PageView::escape($r['sent_to']); ?></strong>
                                            • <?php echo PageView::escape(date('M d, Y h:i A', strtotime($r['created_at']))); ?>
                                            • <span class="reply-status u-dynamic-color" data-css-color="<?php echo $ok ? '#2a5c9e' : '#ef4444'; ?>">
                                                <?php echo strtoupper(PageView::escape($r['status'])); ?>
                                              </span>
                                        </div>
                                        <div class="reply-body">
                                            <?php echo PageView::escape(mb_strimwidth($r['body'], 0, 200, '…')); ?>
                                        </div>
                                        <?php if (!$ok && !empty($r['error'])): ?>
                                            <div class="reply-error">⚠ <?php echo PageView::escape($r['error']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <div class="empty-state tall">
                            <div class="u-font-size-3rem-7d23">📬</div>
                            <p>Select a message to read it here.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- QUICK ACTIONS -->
            <div class="quick-actions">
                <h2>⚡ Quick Actions</h2>
                <div class="action-grid">
                    <a href="<?php echo PageView::escape($ctrl->urlTab('unread')); ?>" class="action-card">
                        <div class="action-icon">📩</div>
                        <div class="action-label">Unread (<?php echo (int) $counts['unread']; ?>)</div>
                    </a>
                    <a href="<?php echo PageView::escape($ctrl->urlTab('read')); ?>" class="action-card">
                        <div class="action-icon">📖</div>
                        <div class="action-label">Read (<?php echo (int) $counts['read']; ?>)</div>
                    </a>
                    <a href="<?php echo PageView::escape($ctrl->urlTab('all')); ?>" class="action-card">
                        <div class="action-icon">📥</div>
                        <div class="action-label">All (<?php echo (int) $counts['all']; ?>)</div>
                    </a>
                    <a href="?page=dashboard-overview" class="action-card">
                        <div class="action-icon">📊</div>
                        <div class="action-label">Dashboard</div>
                    </a>
                </div>
            </div>

        </div>
    </div>
</main>
