<?php
// View data supplied by StudentsPage through EnrollmentPage.
/** @var array<int, array<string, mixed>> $students */
/** @var string $search */
/** @var int|string $courseFilter */
/** @var int|string $sectionFilter */
/** @var string $statusFilter */
/** @var string $message */
/** @var array<string, mixed> $stats */
/** @var array<int, array<string, mixed>> $courses */
/** @var array<int, array<string, mixed>> $sections */
/** @var array<string, string> $badgeMap */
/** @var array<int, int> $subjectCountsByStudent */
?>
<main class="main-content">
    <div class="container">
        <!-- ============================================================
             HEADER
             ============================================================ -->
        <div class="module-header">
            <div class="u-display-flex-f7db u-justify-content-space-between-daae u-align-items-flex-start-b505 u-flex-wrap-wrap-9012 u-gap-15px-7d09">
                <div>
                    <h1>👨‍🎓 Students Management</h1>
                    <p class="dashboard-subtitle">Manage and view all student records in the system</p>
                </div>
                <?php if (count($students) > 0): ?>
                <a href="?page=students&export=csv<?php
                        echo $search        ? '&search='    . urlencode($search)        : '';
                        echo $courseFilter  ? '&course_id=' . (int) $courseFilter       : '';
                        echo $sectionFilter ? '&section_id=' . (int) $sectionFilter     : '';
                        echo $statusFilter  ? '&status='    . urlencode($statusFilter)  : '';
                    ?>"
                   class="btn btn-primary u-padding-10px-20px-cc5c u-font-weight-600-e634">
                    📥 Export CSV
                </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="module-content">

            <!-- MESSAGES -->
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- ============================================================
                 STATISTICS
                 ============================================================ -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) ($stats['total_students'] ?? 0); ?></div>
                        <div class="stat-label">Total Students</div>
                    </div>
                </div>
                <div class="stat-card success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) ($stats['enrolled'] ?? 0); ?></div>
                        <div class="stat-label">Enrolled</div>
                    </div>
                </div>
                <div class="stat-card warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) ($stats['on_leave'] ?? 0); ?></div>
                        <div class="stat-label">On Leave</div>
                    </div>
                </div>
                <div class="stat-card info">
                    <div class="stat-icon">🎓</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) ($stats['graduated'] ?? 0); ?></div>
                        <div class="stat-label">Graduated</div>
                    </div>
                </div>
                <div class="stat-card danger">
                    <div class="stat-icon">❌</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) ($stats['dropped'] ?? 0); ?></div>
                        <div class="stat-label">Dropped</div>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 FILTERS
                 ============================================================ -->
            <div class="filters-section">
                <form method="GET" class="filter-form">
                    <input type="hidden" name="page" value="students">

                    <div class="filter-group search-group">
                        <label for="search_input">Search</label>
                        <div class="search-input-group">
                            <input type="text" name="search" id="search_input"
                                   placeholder="Search by name, student #, or email..."
                                   value="<?php echo htmlspecialchars($search); ?>"
                                   class="search-input">
                            <button type="submit" class="btn btn-primary btn-search">🔍</button>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label for="course_filter">Course</label>
                        <select name="course_id" id="course_filter" data-action-change="action1">
                            <option value="">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo (int) $c['id']; ?>"
                                    <?php echo $courseFilter == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($c['code'] ?? ''); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="section_filter">Section</label>
                        <select name="section_id" id="section_filter" data-action-change="action2">
                            <option value="">All Sections</option>
                            <?php foreach ($sections as $sec): ?>
                                <option value="<?php echo (int) $sec['id']; ?>"
                                    <?php echo $sectionFilter == $sec['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(
                                        ($sec['section_code'] ?? '') . ' (' . ($sec['course_code'] ?? '') . ')'
                                    ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="status_filter">Status</label>
                        <select name="status" id="status_filter" data-action-change="action3">
                            <option value="">All Status</option>
                            <?php foreach ([
                                'enrolled'  => 'Enrolled',
                                'on_leave'  => 'On Leave',
                                'graduated' => 'Graduated',
                                'dropped'   => 'Dropped'
                            ] as $val => $lbl): ?>
                                <option value="<?php echo $val; ?>"
                                    <?php echo $statusFilter === $val ? 'selected' : ''; ?>>
                                    <?php echo $lbl; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if ($search || $courseFilter || $sectionFilter || $statusFilter): ?>
                        <div class="filter-actions">
                            <a href="?page=students" class="btn btn-secondary">✖ Clear</a>
                        </div>
                    <?php endif; ?>
                </form>
            </div>

            <!-- ============================================================
                 STUDENTS TABLE
                 ============================================================ -->
            <div class="students-table-container">
                <div class="table-header">
                    <span class="title">Student Records</span>
                    <span class="badge-count"><?php echo count($students); ?> students</span>
                </div>

                <div class="table-scroll-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student #</th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Section</th>
                                <th>Year</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Enrolled</th>
                                <th>Subjects</th>
                                <th class="u-text-align-right-f6e3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($students) > 0): ?>
                                <?php foreach ($students as $s):
                                    $studentId = (int) ($s['student_id'] ?? 0);

                                    $name = trim(
                                        ($s['first_name'] ?? '')
                                        . ' ' . ($s['middle_name'] ?? '')
                                        . ' ' . ($s['surname'] ?? '')
                                        . ' ' . ($s['suffix'] ?? '')
                                    );

                                    $admissionType = $s['admission_type'] ?? 'freshmen';
                                    $badgeClass    = $badgeMap[$admissionType] ?? 'badge-default';
                                    $badgeLabel    = ucfirst(str_replace('_', ' ', $admissionType));

                                    $subjectCount = $subjectCountsByStudent[$studentId] ?? 0;
                                    $statusVal    = $s['enrollment_status'] ?? 'unknown';
                                ?>
                                <tr>
                                    <td><strong>🎓 <?php echo htmlspecialchars($s['student_number'] ?? ''); ?></strong></td>
                                    <td><?php echo htmlspecialchars($name); ?></td>
                                    <td><?php echo htmlspecialchars($s['course_code'] ?? 'N/A'); ?></td>
                                    <td><?php echo htmlspecialchars($s['section_code'] ?? 'Not Assigned'); ?></td>
                                    <td><?php echo htmlspecialchars($s['year_level'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                                            <?php echo htmlspecialchars($badgeLabel); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($statusVal); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($statusVal))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo !empty($s['enrolled_at'])
                                            ? htmlspecialchars(date('M d, Y', strtotime($s['enrolled_at'])))
                                            : 'N/A'; ?>
                                    </td>
                                    <td>
                                        <?php if ($subjectCount > 0): ?>
                                            <span class="badge badge-info"><?php echo $subjectCount; ?> subjects</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">0 subjects</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="u-text-align-right-f6e3">
                                        <div class="action-buttons">
                                            <a href="?page=student-details&id=<?php echo $studentId; ?>"
                                               class="btn btn-sm btn-primary" title="View Details">View</a>
                                            <a href="?page=requirements&student_id=<?php echo $studentId; ?>&view=students"
                                               class="btn btn-sm btn-warning" title="Requirements">📋</a>
                                            <?php if ($subjectCount > 0): ?>
                                                <button data-action-click="action4" data-action4-arg-0="<?php echo $studentId; ?>"
                                                        class="btn btn-sm btn-info" title="View Schedule">📅</button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="10" class="text-center">
                                        <?php if ($search || $courseFilter || $sectionFilter || $statusFilter): ?>
                                            No students found matching the selected filters.
                                            <a href="?page=students">Clear filters</a>
                                        <?php else: ?>
                                            No students found.
                                            <a href="?page=applications">Convert an application</a>
                                            to create a student.
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- SCHEDULE MODAL -->
<div id="scheduleModal" class="modal">
    <div class="modal-content">
        <span class="close" data-action-click="action5">&times;</span>
        <h2>📅 Student Schedule</h2>
        <div id="scheduleContent">
            <div class="loading-text">Loading schedule...</div>
        </div>
    </div>
</div>
