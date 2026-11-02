<?php
// View data supplied by RequirementsPage through EnrollmentPage.
/** @var string $view */
/** @var array<string, mixed> $paymentCounts */
/** @var string $message */
/** @var array<int, array<string, mixed>> $categories */
/** @var array<string, array<int, array<string, mixed>>> $allRequirements */
/** @var array<int, array<string, mixed>> $paymentSubmissions */
/** @var string|null $paymentStatusFilter */
/** @var string $paymentSearchQuery */
/** @var array<int, array<string, mixed>> $allStudents */
/** @var string $searchQuery */
/** @var int|null $studentId */
/** @var array<string, mixed>|null $studentDetails */
/** @var array<string, mixed>|null $completionStatus */
/** @var array<int, array<string, mixed>> $studentRequirements */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>📋 Requirements Management</h1>
            <p class="dashboard-subtitle">Manage student requirements and payments</p>
        </div>

        <div class="module-content">
            <div class="view-nav">
                <div class="btn-group">
                    <a href="?page=requirements&view=students" class="btn <?php echo $view === 'students' ? 'btn-primary' : 'btn-secondary'; ?>">
                        👥 Student Requirements
                    </a>
                    <a href="?page=requirements&view=payments" class="btn <?php echo $view === 'payments' ? 'btn-primary' : 'btn-secondary'; ?>">
                        💳 Payments
                        <?php if ($paymentCounts['pending'] > 0): ?>
                            <span class="badge u-background-f59e0b-a76e u-color-fff-4eb2 u-margin-left-6px-da47"><?php echo $paymentCounts['pending']; ?></span>
                        <?php endif; ?>
                    </a>
                    <a href="?page=requirements&view=manage" class="btn <?php echo $view === 'manage' ? 'btn-primary' : 'btn-secondary'; ?>">
                        ⚙️ Manage
                    </a>
                </div>
                <?php if ($view === 'manage'): ?>
                    <button class="btn btn-success" data-action-click="action1">➕ Add Requirement</button>
                <?php endif; ?>
            </div>

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : (strpos($message, '⚠️') !== false ? 'warning' : 'danger'); ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($view === 'manage'): ?>
                <div class="manage-requirements-view">
                    <?php foreach ($categories as $cat):
                        $category = $cat['requirement_category'] ?? '';
                        $reqList  = $allRequirements[$category] ?? [];
                    ?>
                        <div class="category-management">
                            <h3><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $category))); ?> Requirements</h3>
                            <div class="requirement-list">
                                <?php if (!empty($reqList)): ?>
                                    <?php foreach ($reqList as $req):
                                        $reqId       = (int) ($req['requirement_id'] ?? 0);
                                        $reqName     = $req['requirement_name'] ?? '';
                                        $isMandatory = !empty($req['is_mandatory']);
                                    ?>
                                        <div class="requirement-manage-item">
                                            <div class="req-info">
                                                <span class="req-name"><?php echo htmlspecialchars($reqName); ?></span>
                                                <span class="req-mandatory"><?php echo $isMandatory ? 'Required *' : 'Optional'; ?></span>
                                            </div>
                                            <div class="req-actions">
                                                <button class="btn btn-sm btn-primary" data-action-click="action2" data-action2-arg-0="<?php echo $reqId; ?>" data-action2-arg-1="<?php echo htmlspecialchars(json_encode($reqName), ENT_QUOTES); ?>" data-action2-arg-2="<?php echo htmlspecialchars(json_encode($category), ENT_QUOTES); ?>" data-action2-arg-3="<?php echo $isMandatory ? '1' : '0'; ?>">✏️</button>
                                                <form method="POST" class="u-display-inline-block-6a19">
                                                    <input type="hidden" name="requirement_id" value="<?php echo $reqId; ?>">
                                                    <input type="hidden" name="action" value="delete_requirement">
                                                    <button type="submit" class="btn btn-sm btn-danger" data-action-click="action3">🗑️</button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="alert alert-info u-margin-10px-0-cca1">No requirements in this category.</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php elseif ($view === 'payments'): ?>
                <div class="payments-view">
                    <div class="payments-summary">
                        <div class="summary-card"><div class="summary-label">Total</div><div class="summary-value"><?php echo (int) $paymentCounts['total']; ?></div></div>
                        <div class="summary-card summary-pending"><div class="summary-label">⏳ Pending</div><div class="summary-value"><?php echo (int) $paymentCounts['pending']; ?></div></div>
                        <div class="summary-card summary-verified"><div class="summary-label">✅ Verified</div><div class="summary-value"><?php echo (int) $paymentCounts['verified']; ?></div></div>
                        <div class="summary-card summary-rejected"><div class="summary-label">❌ Rejected</div><div class="summary-value"><?php echo (int) $paymentCounts['rejected']; ?></div></div>
                    </div>

                    <div class="payments-header">
                        <h3>💳 Payment Submissions (<?php echo count($paymentSubmissions); ?>)</h3>
                        <div class="payments-toolbar">
                            <form method="GET" class="payments-search-form" autocomplete="off">
                                <input type="hidden" name="page" value="requirements">
                                <input type="hidden" name="view" value="payments">
                                <?php if ($paymentStatusFilter): ?>
                                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($paymentStatusFilter); ?>">
                                <?php endif; ?>
                                <div class="payments-search-group">
                                    <input type="text" name="pay_search" id="paymentSearchInput"
                                           placeholder="🔍 Search student or file..."
                                           value="<?php echo htmlspecialchars($paymentSearchQuery); ?>">
                                    <?php if ($paymentSearchQuery !== ''): ?>
                                        <a href="?page=requirements&view=payments<?php echo $paymentStatusFilter ? '&status=' . urlencode($paymentStatusFilter) : ''; ?>" class="payments-clear-search">✖</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                            <div class="payments-filters">
                                <a href="?page=requirements&view=payments<?php echo $paymentSearchQuery !== '' ? '&pay_search=' . urlencode($paymentSearchQuery) : ''; ?>" class="panel-filter-btn <?php echo !$paymentStatusFilter ? 'active' : ''; ?>">All</a>
                                <a href="?page=requirements&view=payments&status=pending<?php echo $paymentSearchQuery !== '' ? '&pay_search=' . urlencode($paymentSearchQuery) : ''; ?>" class="panel-filter-btn <?php echo $paymentStatusFilter === 'pending' ? 'active' : ''; ?>">⏳ Pending</a>
                                <a href="?page=requirements&view=payments&status=verified<?php echo $paymentSearchQuery !== '' ? '&pay_search=' . urlencode($paymentSearchQuery) : ''; ?>" class="panel-filter-btn <?php echo $paymentStatusFilter === 'verified' ? 'active' : ''; ?>">✅ Verified</a>
                                <a href="?page=requirements&view=payments&status=rejected<?php echo $paymentSearchQuery !== '' ? '&pay_search=' . urlencode($paymentSearchQuery) : ''; ?>" class="panel-filter-btn <?php echo $paymentStatusFilter === 'rejected' ? 'active' : ''; ?>">❌ Rejected</a>
                            </div>
                        </div>
                    </div>

                    <?php if (empty($paymentSubmissions)): ?>
                        <div class="payments-empty-result">
                            <?php if ($paymentSearchQuery !== ''): ?>
                                🔍 Walang result para sa "<strong><?php echo htmlspecialchars($paymentSearchQuery); ?></strong>".
                                <a href="?page=requirements&view=payments<?php echo $paymentStatusFilter ? '&status=' . urlencode($paymentStatusFilter) : ''; ?>">Clear search</a>
                            <?php else: ?>
                                📭 No payment submissions found.
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <div class="payments-table-wrap">
                            <table class="payments-table">
                                <thead>
                                    <tr>
                                        <th>Student</th><th>File</th><th>Notes</th><th>Submitted</th><th>Status</th><th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($paymentSubmissions as $ps):
                                        $psId        = (int) $ps['id'];
                                        $psStudentId = (int) ($ps['student_id'] ?? 0);
                                        $psStatus    = $ps['status'] ?? 'pending';

                                        $studentName = trim(
                                            ($ps['first_name']  ?? '')
                                            . ' ' . ($ps['middle_name'] ?? '')
                                            . ' ' . ($ps['surname']     ?? '')
                                            . ' ' . ($ps['suffix']      ?? '')
                                        );
                                        if ($studentName === '') {
                                            $studentName = 'Student #' . ($ps['student_number'] ?? '');
                                        }

                                        $statusClass = [
                                            'pending'  => 'badge-pending',
                                            'verified' => 'badge-verified',
                                            'rejected' => 'badge-rejected',
                                        ][$psStatus] ?? 'badge-pending';

                                        $fileName = $ps['file_name'] ?? '';
                                    ?>
                                        <tr>
                                            <td>
                                                <div class="ps-student-name"><?php echo htmlspecialchars($studentName); ?></div>
                                                <div class="ps-student-meta">
                                                    🎓 <?php echo htmlspecialchars($ps['student_number'] ?? ''); ?>
                                                    <?php if (!empty($ps['course_code'])): ?> · 📚 <?php echo htmlspecialchars($ps['course_code']); ?><?php endif; ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="ps-file-link">📄 <?php echo htmlspecialchars($fileName ?: '(no file)'); ?></span>
                                            </td>
                                            <td class="ps-notes"><?php echo htmlspecialchars($ps['notes'] ?? '—'); ?></td>
                                            <td class="ps-date">
                                                <?php
                                                    $submitted = $ps['submitted_at'] ?? '';
                                                    echo $submitted ? htmlspecialchars(date('M j, Y g:i A', strtotime($submitted))) : '—';
                                                ?>
                                            </td>
                                            <td>
                                                <span class="ps-status-badge <?php echo $statusClass; ?>">
                                                    <?php echo htmlspecialchars(ucfirst($psStatus)); ?>
                                                </span>
                                                <?php if (!empty($ps['verified_at'])): ?>
                                                    <div class="ps-verified-meta"><?php echo htmlspecialchars(date('M j, Y', strtotime($ps['verified_at']))); ?></div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="ps-actions">
                                                    <?php if ($psStatus !== 'verified'): ?>
                                                        <form method="POST" class="u-display-inline-block-6a19">
                                                            <input type="hidden" name="payment_action" value="verify">
                                                            <input type="hidden" name="submission_id" value="<?php echo $psId; ?>">
                                                            <input type="hidden" name="student_id" value="<?php echo $psStudentId; ?>">
                                                            <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($paymentStatusFilter ?? ''); ?>">
                                                            <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($paymentSearchQuery ?? ''); ?>">
                                                            <button type="submit" class="btn btn-sm btn-success" data-action-click="action4">✅ Verify</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($psStatus !== 'rejected'): ?>
                                                        <form method="POST" class="u-display-inline-block-6a19">
                                                            <input type="hidden" name="payment_action" value="reject">
                                                            <input type="hidden" name="submission_id" value="<?php echo $psId; ?>">
                                                            <input type="hidden" name="student_id" value="<?php echo $psStudentId; ?>">
                                                            <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($paymentStatusFilter ?? ''); ?>">
                                                            <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($paymentSearchQuery ?? ''); ?>">
                                                            <button type="submit" class="btn btn-sm btn-danger" data-action-click="action5">❌ Reject</button>
                                                        </form>
                                                    <?php endif; ?>
                                                    <?php if ($psStatus === 'verified'): ?>
                                                        <form method="POST" class="u-display-inline-block-6a19">
                                                            <input type="hidden" name="payment_action" value="resend_email">
                                                            <input type="hidden" name="student_id" value="<?php echo $psStudentId; ?>">
                                                            <input type="hidden" name="submission_id" value="<?php echo $psId; ?>">
                                                            <input type="hidden" name="return_status" value="<?php echo htmlspecialchars($paymentStatusFilter ?? ''); ?>">
                                                            <input type="hidden" name="return_search" value="<?php echo htmlspecialchars($paymentSearchQuery ?? ''); ?>">
                                                            <button type="submit" class="btn btn-sm btn-secondary" title="Resend verification email">📧 Resend</button>
                                                        </form>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <div class="requirements-layout">
                    <aside class="student-sidebar">
                        <div class="req-student-header"><h3>👥 Students (<?php echo count($allStudents); ?>)</h3></div>
                        <div class="req-student-search">
                            <form method="GET" id="sidebarSearchForm" autocomplete="off">
                                <input type="hidden" name="page" value="requirements">
                                <input type="hidden" name="view" value="students">
                                <div class="req-student-search-group">
                                    <input type="text" name="search" id="searchInput" placeholder="🔍 Search student..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                    <?php if ($searchQuery !== ''): ?>
                                        <a href="?page=requirements&view=students" class="clear-search">✖</a>
                                    <?php endif; ?>
                                </div>
                            </form>
                        </div>
                        <div class="req-student-list">
                            <?php if (count($allStudents) > 0): ?>
                                <?php foreach ($allStudents as $s):
                                    $sid        = (int) ($s['student_id'] ?? 0);
                                    $isSelected = ($studentId === $sid);
                                    $studentName = trim(($s['first_name'] ?? '') . ' ' . ($s['surname'] ?? ''));
                                    if ($studentName === '') $studentName = 'Student #' . ($s['student_number'] ?? '');
                                ?>
                                    <a href="?page=requirements&view=students&student_id=<?php echo $sid; ?><?php echo $searchQuery ? '&search=' . urlencode($searchQuery) : ''; ?>"
                                       class="student-item <?php echo $isSelected ? 'selected' : ''; ?>">
                                        <div class="student-avatar"><?php echo strtoupper(substr($studentName, 0, 1)); ?></div>
                                        <div class="student-item-info">
                                            <div class="student-item-name"><?php echo htmlspecialchars($studentName); ?></div>
                                            <div class="student-item-meta">
                                                🎓 <?php echo htmlspecialchars($s['student_number'] ?? ''); ?>
                                                <?php if (!empty($s['course_code'])): ?> · 📚 <?php echo htmlspecialchars($s['course_code']); ?><?php endif; ?>
                                            </div>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <div class="empty-icon">🔍</div>
                                    <p>No students found</p>
                                    <?php if ($searchQuery !== ''): ?>
                                        <a href="?page=requirements&view=students" class="btn btn-sm btn-secondary">Clear search</a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </aside>

                    <section class="requirements-panel">
                        <?php if ($studentId && $studentDetails): ?>
                            <div class="panel-student-header">
                                <div class="panel-student-info">
                                    <div class="panel-student-name"><?php echo htmlspecialchars(trim(($studentDetails['first_name'] ?? '') . ' ' . ($studentDetails['surname'] ?? ''))); ?></div>
                                    <div class="panel-student-meta">
                                        <span>🎓 <?php echo htmlspecialchars($studentDetails['student_number'] ?? ''); ?></span>
                                        <span>📚 <?php echo htmlspecialchars($studentDetails['course_code'] ?? 'No Course'); ?></span>
                                    </div>
                                </div>
                                <?php $pct = (int) ($completionStatus['percentage'] ?? 0); ?>
                                <div class="panel-progress">
                                    <div class="panel-progress-label">
                                        <span><?php echo (int) ($completionStatus['submitted'] ?? 0); ?>/<?php echo (int) ($completionStatus['total'] ?? 0); ?></span>
                                        <strong><?php echo $pct; ?>%</strong>
                                    </div>
                                    <div class="panel-progress-bar">
                                        <div class="panel-progress-fill u-dynamic-width u-dynamic-background" data-css-width="<?php echo $pct; ?>%" data-css-background="<?php echo $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444'); ?>"></div>
                                    </div>
                                </div>
                            </div>

                            <form method="POST" class="panel-requirements-form" id="studentRequirementsForm">
                                <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                                <input type="hidden" name="action" value="update_requirements">
                                <p class="panel-verification-help">Check requirements that have been submitted, then click Save Requirements. Verification status is read-only; marking a requirement does not change its status.</p>

                                <?php foreach ($studentRequirements as $req):
                                    $rid = (int) ($req['requirement_id'] ?? 0);
                                    if ($rid > 0): ?>
                                        <input type="hidden" name="all_requirement_ids[]" value="<?php echo $rid; ?>">
                                <?php endif; endforeach; ?>

                                <div class="panel-requirements-list panel-category-items">
                                    <?php
                                    foreach ($studentRequirements as $req):
                                        $reqId  = (int) ($req['requirement_id'] ?? 0);
                                        $isSub  = !empty($req['is_submitted']);
                                        $reviewStatus = $req['status'] ?? 'pending';
                                        $statusClass = $reviewStatus === 'verified' ? 'submitted' : $reviewStatus;
                                        $isMan  = !empty($req['is_mandatory']);
                                        ?>
                                            <div class="panel-requirement-item <?php echo htmlspecialchars($statusClass); ?>">
                                                <div class="panel-req-check">
                                                    <input type="checkbox" name="requirements[<?php echo $reqId; ?>]" value="1" <?php echo $isSub ? 'checked' : ''; ?> id="req_<?php echo $reqId; ?>" aria-label="<?php echo htmlspecialchars('Submitted: ' . ($req['requirement_name'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                                                    <label for="req_<?php echo $reqId; ?>">
                                                        <?php echo htmlspecialchars($req['requirement_name'] ?? ''); ?>
                                                        <?php if ($isMan): ?><span class="panel-mandatory">*</span><?php endif; ?>
                                                    </label>
                                                </div>
                                                <div class="panel-req-notes">
                                                    <?php if (!empty($req['has_file'])): ?>
                                                        <a href="api/get_requirement_file.php?student_id=<?php echo (int) $studentId; ?>&amp;requirement_id=<?php echo $reqId; ?>" class="requirement-file-link">
                                                            Download <?php echo htmlspecialchars($req['custom_name'] ?: ($req['file_name'] ?: 'attachment'), ENT_QUOTES, 'UTF-8'); ?>
                                                        </a>
                                                    <?php endif; ?>
                                                    <input type="text" name="req_notes[<?php echo $reqId; ?>]" placeholder="Notes..." value="<?php echo htmlspecialchars($req['notes'] ?? ''); ?>" class="panel-note-input">
                                                </div>
                                                <div class="panel-req-status">
                                                    <?php if ($reviewStatus === 'verified'): ?>
                                                        <span class="panel-status-badge submitted">✅ Verified</span>
                                                    <?php elseif ($reviewStatus === 'rejected'): ?>
                                                        <span class="panel-status-badge rejected">Rejected</span>
                                                    <?php else: ?>
                                                        <span class="panel-status-badge pending">⏳ Pending</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                </div>

                                <div class="panel-form-actions">
                                    <button type="submit" class="btn btn-success btn-large">💾 Save Requirements</button>
                                </div>
                            </form>
                        <?php else: ?>
                            <div class="panel-empty-state">
                                <div class="panel-empty-icon">👈</div>
                                <h3>Select a Student</h3>
                                <p>Choose a student from the list on the left to manage their requirements.</p>
                            </div>
                        <?php endif; ?>
                    </section>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<div id="addRequirementModal" class="modal">
    <div class="modal-content">
        <span class="close" data-action-click="action6">&times;</span>
        <h2>➕ Add New Requirement</h2>
        <form method="POST">
            <input type="hidden" name="action" value="add_requirement">
            <div class="form-group"><label>Requirement Name *</label><input type="text" name="requirement_name" required></div>
            <div class="form-group">
                <label>Category *</label>
                <select name="requirement_category" required>
                    <option value="freshmen">Freshmen</option>
                    <option value="transferee">Transferee</option>
                    <option value="continuing">Continuing</option>
                </select>
            </div>
            <div class="form-group"><label><input type="checkbox" name="is_mandatory" checked> Mandatory Requirement</label></div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action7">Cancel</button>
                <button type="submit" class="btn btn-primary">Add Requirement</button>
            </div>
        </form>
    </div>
</div>

<div id="editRequirementModal" class="modal">
    <div class="modal-content">
        <span class="close" data-action-click="action8">&times;</span>
        <h2>✏️ Edit Requirement</h2>
        <form method="POST">
            <input type="hidden" name="action" value="update_requirement">
            <input type="hidden" name="requirement_id" id="edit_req_id">
            <div class="form-group"><label>Requirement Name *</label><input type="text" name="requirement_name" id="edit_req_name" required></div>
            <div class="form-group">
                <label>Category *</label>
                <select name="requirement_category" id="edit_req_category" required>
                    <option value="freshmen">Freshmen</option>
                    <option value="transferee">Transferee</option>
                    <option value="continuing">Continuing</option>
                </select>
            </div>
            <div class="form-group"><label><input type="checkbox" name="is_mandatory" id="edit_req_mandatory"> Mandatory Requirement</label></div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action9">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Requirement</button>
            </div>
        </form>
    </div>
</div>
