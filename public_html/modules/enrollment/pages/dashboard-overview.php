<?php
// View data supplied by DashboardOverviewPage through EnrollmentPage.
/** @var array<string, mixed> $clientData */
/** @var string $currentDateTime */
/** @var int $totalStudents */
/** @var int $activeEnrollments */
/** @var int $totalSubjectEnrollments */
/** @var int $pendingCount */
/** @var array<string, mixed> $sectionStats */
/** @var int $archivedCount */
/** @var int $currentYear */
/** @var int $predictedNextMonth */
/** @var float $avgGrowth */
/** @var int $predictedNextQuarter */
/** @var float $conversionRate */
/** @var array<int, array<string, mixed>> $studentsWithIncomplete */
/** @var array<int, array<string, mixed>> $recentEnrollments */
?>
<div id="page-data" hidden data-values="<?= PageView::escape(json_encode($clientData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE)) ?>"></div>





<main class="main-content">
    <div class="container">
        <!-- ============================================================
             HEADER
             ============================================================ -->
        <div class="module-header">
            <div class="u-display-flex-f7db u-justify-content-space-between-daae u-align-items-flex-start-b505 u-flex-wrap-wrap-9012 u-gap-15px-7d09">
                <div>
                    <h1>📊 Dashboard Overview</h1>
                    <p class="dashboard-subtitle">
                        Welcome back! Here's what's happening with your enrollment system.
                        <span class="u-color-94a3b8-8cd4 u-margin-left-8px-c3f8">• <?php echo htmlspecialchars($currentDateTime); ?></span>
                    </p>
                </div>

                <!-- Export Dropdown -->
                <div class="export-dropdown">
                    <button class="export-toggle" data-action-click="action1">
                        📥 Export Reports <span class="u-font-size-10px-bcf8">▼</span>
                    </button>
                    <div class="export-menu" id="exportMenu">
                        <a href="?page=dashboard-overview&export=analysis" class="export-item">
                            <span>📈</span> Analysis Report
                        </a>
                        <a href="?page=dashboard-overview&export=summary" class="export-item">
                            <span>📊</span> Summary Report
                        </a>
                        <a href="?page=dashboard-overview&export=students" class="export-item">
                            <span>👨‍🎓</span> Student List
                        </a>
                        <a href="?page=dashboard-overview&export=archive" class="export-item">
                            <span>📦</span> Archive Report
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="module-content">

            <!-- ============================================================
                 STATISTICS
                 ============================================================ -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="stat-icon">👨‍🎓</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) $totalStudents; ?></div>
                        <div class="stat-label">Total Students</div>
                        <span class="stat-change up">↑ <?php echo (int) $activeEnrollments; ?> active</span>
                    </div>
                </div>

                <div class="stat-card success">
                    <div class="stat-icon">📚</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo $totalSubjectEnrollments; ?></div>
                        <div class="stat-label">Subject Enrollments</div>
                        <span class="stat-change up">↑ <?php echo (int) $activeEnrollments; ?> students</span>
                    </div>
                </div>

                <div class="stat-card warning">
                    <div class="stat-icon">✅</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) $activeEnrollments; ?></div>
                        <div class="stat-label">Active Students</div>
                        <span class="stat-change up">✓ Currently enrolled</span>
                    </div>
                </div>

                <div class="stat-card danger">
                    <div class="stat-icon">📝</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) $pendingCount; ?></div>
                        <div class="stat-label">Pending Applications</div>
                        <span class="stat-change <?php echo $pendingCount > 0 ? 'down' : 'up'; ?>">
                            <?php echo $pendingCount > 0 ? '⚠️ Needs attention' : '✅ All clear'; ?>
                        </span>
                    </div>
                </div>

                <div class="stat-card info">
                    <div class="stat-icon">🏫</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) ($sectionStats['total_sections'] ?? 0); ?></div>
                        <div class="stat-label">Total Sections</div>
                        <span class="stat-change up">
                            <?php echo (int) ($sectionStats['available_slots'] ?? 0); ?> slots available
                        </span>
                    </div>
                </div>

                <div class="stat-card secondary">
                    <div class="stat-icon">📦</div>
                    <div class="stat-content">
                        <div class="stat-number"><?php echo (int) $archivedCount; ?></div>
                        <div class="stat-label">Archived Students</div>
                        <span class="stat-change <?php echo $archivedCount > 0 ? 'warning' : 'up'; ?>">
                            <?php echo $archivedCount > 0 ? '📦 Pwede i-restore' : '✅ No archived'; ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 CHARTS
                 ============================================================ -->
            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📈 Subject Enrollments Trend</h3>
                        <span class="chart-badge"><?php echo $currentYear; ?></span>
                    </div>
                    <div class="chart-container">
                        <canvas id="enrollmentChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>🎯 Enrollment by Course</h3>
                    </div>
                    <div class="chart-container chart-container-doughnut">
                        <canvas id="courseChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="charts-grid">
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📊 Applications Trend</h3>
                        <span class="chart-badge"><?php echo $currentYear; ?></span>
                    </div>
                    <div class="chart-container">
                        <canvas id="applicationChart"></canvas>
                    </div>
                </div>
                <div class="chart-card">
                    <div class="chart-header">
                        <h3>📚 Enrollment by Year Level</h3>
                    </div>
                    <div class="chart-container">
                        <canvas id="yearChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 PREDICTIVE ANALYTICS
                 ============================================================ -->
            <div class="analytics-section">
                <div class="section-header">
                    <div>
                        <h2>🔮 Predictive Analytics</h2>
                        <p class="section-subtitle">Based on current trends and historical data for <?php echo $currentYear; ?></p>
                    </div>
                </div>

                <div class="predictive-grid">
                    <div class="predictive-card green">
                        <div class="pred-icon">📈</div>
                        <div class="pred-number"><?php echo $predictedNextMonth; ?></div>
                        <div class="pred-label">Projected Enrollments<br><small>Next Month</small></div>
                        <div class="pred-trend">↑ <?php echo round($avgGrowth, 1); ?>% growth</div>
                    </div>
                    <div class="predictive-card orange">
                        <div class="pred-icon">📊</div>
                        <div class="pred-number"><?php echo $predictedNextQuarter; ?></div>
                        <div class="pred-label">Projected Enrollments<br><small>Next Quarter</small></div>
                        <div class="pred-trend">↑ <?php echo round($avgGrowth * 3, 1); ?>% growth</div>
                    </div>
                    <div class="predictive-card purple">
                        <div class="pred-icon">🎯</div>
                        <div class="pred-number"><?php echo round($conversionRate, 1); ?>%</div>
                        <div class="pred-label">Conversion Rate<br><small>Applications → Students</small></div>
                        <div class="pred-trend"><?php echo $conversionRate > 50 ? '✅ Good conversion' : '📈 Room for improvement'; ?></div>
                    </div>
                    <div class="predictive-card red">
                        <div class="pred-icon">⚡</div>
                        <div class="pred-number"><?php echo count($studentsWithIncomplete); ?></div>
                        <div class="pred-label">Incomplete Requirements<br><small>Needs follow-up</small></div>
                        <div class="pred-trend">
                            <?php echo count($studentsWithIncomplete) > 0 ? '⚡ Needs follow-up' : '✅ All complete'; ?>
                        </div>
                    </div>
                </div>

                <div class="insights-box">
                    <h4>📊 Key Insights</h4>
                    <div class="insights-grid">
                        <div class="insight-item border-green">
                            <strong>Enrollment Growth:</strong> Expected to grow by <strong><?php echo round($avgGrowth, 1); ?>%</strong> monthly
                        </div>
                        <div class="insight-item border-yellow">
                            <strong>Pending Applications:</strong> <strong><?php echo (int) $pendingCount; ?></strong> waiting for processing
                        </div>
                        <div class="insight-item border-blue">
                            <strong>Available Slots:</strong> <strong><?php echo (int) ($sectionStats['available_slots'] ?? 0); ?></strong> slots available
                        </div>
                        <div class="insight-item border-gray">
                            <strong>Archived Students:</strong> <strong><?php echo (int) $archivedCount; ?></strong> archived (pwede i-restore)
                        </div>
                    </div>
                </div>
            </div>

            <!-- ============================================================
                 RECENT ENROLLMENTS
                 ============================================================ -->
            <div class="recent-applications">
                <div class="section-header">
                    <h2>📋 Recent Subject Enrollments</h2>
                    <a href="?page=enrollments" class="btn btn-sm btn-primary">View All</a>
                </div>

                <?php if (!empty($recentEnrollments)): ?>
                <div class="table-scroll-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Student #</th>
                                <th>Subject</th>
                                <th>Course</th>
                                <th>Section</th>
                                <th>Enrolled</th>
                                <th>Status</th>
                                <th>Schedule</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEnrollments as $enr): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars(trim(
                                    ($enr['first_name'] ?? '') . ' ' . ($enr['surname'] ?? '')
                                )); ?></strong></td>
                                <td><?php echo htmlspecialchars($enr['student_number'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($enr['subject_code'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($enr['course_code'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($enr['section_code'] ?? 'N/A'); ?></td>
                                <td><?php
                                    echo !empty($enr['enrolled_at'])
                                        ? htmlspecialchars(date('M d, Y', strtotime($enr['enrolled_at'])))
                                        : 'N/A';
                                ?></td>
                                <td><span class="status-badge status-enrolled">Enrolled</span></td>
                                <td>
                                    <?php if (!empty($enr['day_of_week'])): ?>
                                        <small><?php echo htmlspecialchars(
                                            $enr['day_of_week']
                                            . ' ' . ($enr['start_time'] ?? '')
                                            . '-' . ($enr['end_time'] ?? '')
                                        ); ?></small>
                                    <?php else: ?>
                                        <small class="u-color-94a3b8-8cd4">No schedule</small>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="text-center">No recent subject enrollments found.</div>
                <?php endif; ?>
            </div>

            <!-- ============================================================
                 QUICK ACTIONS
                 ============================================================ -->
            <div class="quick-actions">
                <h2>⚡ Quick Actions</h2>
                <div class="action-grid">
                    <a href="?page=applications"      class="action-card"><div class="action-icon">📋</div><div class="action-label">New Application</div></a>
                    <a href="?page=enrollments"       class="action-card"><div class="action-icon">📚</div><div class="action-label">Enroll Students</div></a>
                    <a href="?page=students"          class="action-card"><div class="action-icon">👨‍🎓</div><div class="action-label">View Students</div></a>
                    <a href="?page=sections"          class="action-card"><div class="action-icon">🏫</div><div class="action-label">Manage Sections</div></a>
                    <a href="?page=requirements"      class="action-card"><div class="action-icon">📋</div><div class="action-label">Requirements</div></a>
                    <a href="?page=courses"           class="action-card"><div class="action-icon">📖</div><div class="action-label">Manage Courses</div></a>
                    <a href="?page=archived-students" class="action-card"><div class="action-icon">📦</div><div class="action-label">Archived Students</div></a>
                </div>
            </div>
        </div>
    </div>
</main>
