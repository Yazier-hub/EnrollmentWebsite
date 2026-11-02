<?php
// View data supplied by SectionsPage through EnrollmentPage.
/** @var string $message */
/** @var array<string, mixed> $stats */
/** @var array<int, array<string, mixed>> $sections */
/** @var int $DEFAULT_CAPACITY */
/** @var array<int, array<string, mixed>> $fullSections */
/** @var string $statusFilter */
/** @var array<int, array<string, mixed>> $courses */
/** @var int|string $courseFilter */
/** @var array<int, array<string, mixed>> $filteredSections */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>📚 Sections Management</h1>
            <p class="dashboard-subtitle">Manage academic sections</p>
        </div>

        <div class="module-content">

            <!-- MESSAGES -->
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- STATISTICS -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="number"><?php echo (int) ($stats['total_sections'] ?? count($sections)); ?></div>
                    <div class="label">📚 Total Sections</div>
                </div>
                <div class="stat-card success">
                    <div class="number"><?php echo (int) ($stats['total_students'] ?? 0); ?></div>
                    <div class="label">👨‍🎓 Enrolled Students</div>
                </div>
                <div class="stat-card info">
                    <div class="number">
                        <?php
                            $totalSlots = 0;
                            foreach ($sections as $s) {
                                $totalSlots += max(0, (int) $s['max_students'] - (int) $s['current_students']);
                            }
                            echo $totalSlots;
                        ?>
                    </div>
                    <div class="label">📊 Available Slots</div>
                </div>
                <div class="stat-card danger">
                    <div class="number">
                        <?php
                            $totalCap = count($sections) * $DEFAULT_CAPACITY;
                            $used     = (int) ($stats['total_students'] ?? 0);
                            echo $totalCap > 0 ? round(($used / $totalCap) * 100) : 0;
                        ?>%
                    </div>
                    <div class="label">📈 Average Utilization</div>
                </div>
            </div>

            <!-- WARNINGS -->
            <?php if (count($fullSections) > 0): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ <?php echo count($fullSections); ?> section(s) are at full capacity!</strong>
                    <a href="#full-sections">View full sections</a>
                </div>
            <?php endif; ?>

            <!-- FILTERS -->
            <div class="filters-section">
                <form method="GET" class="u-display-flex-f7db u-gap-15px-7d09 u-flex-wrap-wrap-9012 u-align-items-flex-end-2e00 u-width-100-6d56">
                    <input type="hidden" name="page" value="sections">

                    <div class="filter-group">
                        <label for="status_filter">Status</label>
                        <select name="status" id="status_filter" data-action-change="action1">
                            <option value="all"       <?php echo $statusFilter === 'all'       ? 'selected' : ''; ?>>All Sections</option>
                            <option value="available" <?php echo $statusFilter === 'available' ? 'selected' : ''; ?>>Available</option>
                            <option value="full"      <?php echo $statusFilter === 'full'      ? 'selected' : ''; ?>>Full</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="course_filter">Course</label>
                        <select name="course_id" id="course_filter" data-action-change="action2">
                            <option value="0">All Courses</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo (int) $c['id']; ?>"
                                    <?php echo $courseFilter == $c['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <?php if ($statusFilter !== 'all' || $courseFilter > 0): ?>
                            <a href="?page=sections" class="btn btn-secondary btn-sm">Clear Filters</a>
                        <?php endif; ?>
                        <button type="button" class="btn btn-primary" data-action-click="action3">+ Add Section</button>
                    </div>
                </form>
            </div>

            <!-- SECTIONS TABLE -->
            <div class="sections-table-container">
                <div class="table-header">
                    <span class="title">
                        Sections
                        <?php if ($statusFilter !== 'all'): ?>
                            (<?php echo htmlspecialchars(ucfirst($statusFilter)); ?>)
                        <?php endif; ?>
                        <?php if ($courseFilter > 0): ?>
                            <?php
                                $courseName = '';
                                foreach ($courses as $c) {
                                    if ((int) $c['id'] === $courseFilter) { $courseName = $c['code'] ?? ''; break; }
                                }
                                echo ' - ' . htmlspecialchars($courseName);
                            ?>
                        <?php endif; ?>
                    </span>
                    <span class="badge-count"><?php echo count($filteredSections); ?></span>
                </div>

                <?php if (count($filteredSections) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Code</th>
                                <th>Course</th>
                                <th>Grade Level</th>
                                <th>Semester</th>
                                <th>Students</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($filteredSections as $sec):
                                $current   = (int) ($sec['current_students'] ?? 0);
                                $max       = max(1, (int) ($sec['max_students'] ?? $DEFAULT_CAPACITY));
                                $utilization = $max > 0 ? round(($current / $max) * 100) : 0;

                                if ($utilization >= 100)     { $capClass = 'capacity-full';       $capLabel = 'Full'; }
                                elseif ($utilization >= 80)  { $capClass = 'capacity-near-full';  $capLabel = 'Near Full'; }
                                elseif ($utilization >= 50)  { $capClass = 'capacity-half';       $capLabel = 'Half Full'; }
                                elseif ($utilization > 0)    { $capClass = 'capacity-available';  $capLabel = 'Available'; }
                                else                         { $capClass = 'capacity-empty';      $capLabel = 'Empty'; }

                                $fillColor = $utilization >= 100 ? '#0f2a4e'
                                           : ($utilization >= 80  ? '#4a90d9'
                                           : ($utilization >= 50  ? '#6aa8e0'
                                           : '#2a5c9e'));
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($sec['section_code'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars(
                                    ($sec['course_code'] ?? '') . ' - ' . ($sec['course_name'] ?? '')
                                ); ?></td>
                                <td><?php echo htmlspecialchars($sec['grade_level'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($sec['semester'] ?? ''); ?></td>
                                <td>
                                    <span class="capacity-status <?php echo htmlspecialchars($capClass); ?>">
                                        <?php echo $current; ?>/<?php echo $max; ?>
                                    </span>
                                    <div class="capacity-bar">
                                        <div class="capacity-fill u-dynamic-width u-dynamic-background" data-css-width="<?php echo $utilization; ?>%" data-css-background="<?php echo $fillColor; ?>">
                                        </div>
                                    </div>
                                    <small><?php echo htmlspecialchars($capLabel); ?> (<?php echo $utilization; ?>%)</small>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="text-center">
                        <?php if ($statusFilter !== 'all' || $courseFilter > 0): ?>
                            No sections found matching the selected filters.
                            <a href="?page=sections">Clear filters</a>
                        <?php else: ?>
                            No sections found.
                            <a href="#" data-action-click="action4">Create one now</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- FULL SECTIONS -->
            <?php if (count($fullSections) > 0): ?>
            <div id="full-sections" class="u-margin-top-20px-3ae1 u-background-white-29c1 u-border-radius-8px-a503 u-padding-20px-7fa5 u-box-shadow-0-2px-4px-rgba-26-60-110-0-08-0dff">
                <h3 class="u-color-0f2a4e-9277 u-margin-bottom-10px-a740">⚠️ Full Sections (<?php echo count($fullSections); ?>)</h3>
                <p class="text-muted">These sections have reached maximum capacity (<?php echo $DEFAULT_CAPACITY; ?> students). Consider adding new sections.</p>
                <div class="u-display-grid-998b u-grid-template-columns-repeat-auto-fill-minmax-250px-1fr-57dd u-gap-10px-114b u-margin-top-10px-5371">
                    <?php foreach ($fullSections as $fs): ?>
                    <div class="u-background-f0f7ff-085b u-padding-10px-15px-f531 u-border-radius-6px-9aae u-border-left-4px-solid-0f2a4e-a801">
                        <strong><?php echo htmlspecialchars($fs['section_code'] ?? ''); ?></strong>
                        <span class="u-color-5a7fa8-c092 u-font-size-13px-3044"><?php echo htmlspecialchars($fs['course_code'] ?? ''); ?></span>
                        <div class="u-font-size-13px-3044 u-color-0f2a4e-9277">
                            <?php echo (int) $fs['current_students']; ?>/<?php echo (int) $fs['max_students']; ?> students
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- ADD SECTION MODAL -->
<div id="addSectionModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>➕ Add New Section</h2>
            <span class="close" data-action-click="action5">&times;</span>
        </div>
        <form method="POST" class="section-form">
            <input type="hidden" name="action" value="create">
            <input type="hidden" name="redirect" value="?page=sections">

            <div class="form-group">
                <label>Course <span class="required">*</span></label>
                <select name="program_id" required>
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo (int) $c['id']; ?>">
                            <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Year Level <span class="required">*</span></label>
                <select name="year_level" required>
                    <option value="1">1st Year</option>
                    <option value="2">2nd Year</option>
                    <option value="3">3rd Year</option>
                    <option value="4">4th Year</option>
                </select>
            </div>
            <div class="form-group">
                <label>Semester <span class="required">*</span></label>
                <select name="semester" required>
                    <option value="1st Semester">1st Semester</option>
                    <option value="2nd Semester">2nd Semester</option>
                </select>
            </div>
            <div class="form-group">
                <label>Section Number <span class="required">*</span></label>
                <input type="number" name="section_number" min="1" max="99" required>
                <small>Example: 1 = Section 1, 2 = Section 2, etc.</small>
            </div>
            <div class="form-group">
                <label>School Year <span class="required">*</span></label>
                <input type="text" name="school_year"
                       value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>" required>
                <small>Format: YYYY-YYYY (e.g., 2025-2026)</small>
            </div>
            <!-- NOTE: no max_students field — column doesn't exist in kms -->
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action6">Cancel</button>
                <button type="submit" class="btn btn-primary">Create Section</button>
            </div>
        </form>
    </div>
</div>
