<?php
// View data supplied by ArchivedStudentsPage through EnrollmentPage.
/** @var string $message */
/** @var int $totalArchived */
/** @var array<string, int> $reasonCounts */
/** @var string $search */
/** @var array<int, array<string, mixed>> $courses */
/** @var int|string $courseFilter */
/** @var int|string $yearFilter */
/** @var string $reasonFilter */
/** @var array<int, array<string, mixed>> $archivedStudents */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>📦 Archived Students</h1>
            <p class="dashboard-subtitle">Listahan ng archived students — pwedeng i-restore</p>
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
                <div class="stat-card warning">
                    <div class="number"><?php echo $totalArchived; ?></div>
                    <div class="label">📦 Total Archived</div>
                </div>
                <div class="stat-card danger">
                    <div class="number"><?php echo (int) $reasonCounts['Dropped']; ?></div>
                    <div class="label">❌ Dropped</div>
                </div>
                <div class="stat-card info">
                    <div class="number"><?php echo (int) $reasonCounts['Transferred']; ?></div>
                    <div class="label">🔄 Transferred</div>
                </div>
                <div class="stat-card primary">
                    <div class="number"><?php echo (int) $reasonCounts['LOA']; ?></div>
                    <div class="label">⏸️ LOA</div>
                </div>
                <div class="stat-card success">
                    <div class="number"><?php echo (int) $reasonCounts['Graduated']; ?></div>
                    <div class="label">🎓 Graduated</div>
                </div>
            </div>



            <!-- SEARCH & FILTERS -->
            <div class="filters-section">
                <form method="GET" class="filter-form">
                    <input type="hidden" name="page" value="archived-students">

                    <div class="filter-group search-group">
                        <label for="search_input">Search</label>
                        <input type="text" name="search" id="search_input"
                               placeholder="Search by name, student #, or email..."
                               value="<?php echo htmlspecialchars($search); ?>"
                               class="search-input">
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
                        <label for="year_filter">Year Level</label>
                        <select name="year_level" id="year_filter" data-action-change="action2">
                            <option value="">All Years</option>
                            <?php foreach ([1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'] as $y => $lbl): ?>
                                <option value="<?php echo $y; ?>" <?php echo $yearFilter == $y ? 'selected' : ''; ?>>
                                    <?php echo $lbl; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="reason_filter">Reason</label>
                        <select name="reason" id="reason_filter" data-action-change="action3">
                            <option value="">All Reasons</option>
                            <?php foreach ([
                                'Dropped'     => 'Dropped',
                                'Transferred' => 'Transferred',
                                'LOA'         => 'Leave of Absence',
                                'Graduated'   => 'Graduated',
                                'Other'       => 'Other'
                            ] as $val => $lbl): ?>
                                <option value="<?php echo $val; ?>"
                                    <?php echo $reasonFilter === $val ? 'selected' : ''; ?>>
                                    <?php echo $lbl; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">🔍 Search</button>
                        <?php if ($search || $courseFilter || $yearFilter || $reasonFilter): ?>
                            <a href="?page=archived-students" class="btn btn-secondary">✖ Clear</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- ARCHIVED STUDENTS TABLE -->
            <div class="enrollments-table-container">
                <div class="table-header">
                    <h2 class="u-margin-0-1386">📦 Archived Students (<?php echo $totalArchived; ?>)</h2>
                    <div class="u-display-flex-f7db u-gap-10px-114b u-flex-wrap-wrap-9012">
                        <a href="?page=enrollments" class="btn btn-sm btn-info">← Back to Enrollments</a>
                        <a href="?page=students" class="btn btn-sm btn-secondary">All Students</a>
                    </div>
                </div>

                <?php if (count($archivedStudents) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Student #</th>
                            <th>Name</th>
                            <th>Section</th>
                            <th>Course</th>
                            <th>Year</th>
                            <th>Archived At</th>
                            <th>Reason</th>
                            <th>Archived By</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($archivedStudents as $s):
                            $studentId = (int) ($s['student_id'] ?? 0);
                            $fullName  = trim(
                                ($s['first_name'] ?? '')
                                . ' ' . ($s['middle_name'] ?? '')
                                . ' ' . ($s['surname'] ?? '')
                                . ' ' . ($s['suffix'] ?? '')
                            );

                            $archivedDate = !empty($s['archived_at'])
                                ? date('M d, Y', strtotime($s['archived_at']))
                                : 'N/A';
                            $archivedTime = !empty($s['archived_at'])
                                ? date('h:i A', strtotime($s['archived_at']))
                                : '';

                            $reason = $s['archive_reason'] ?? 'Other';
                            switch ($reason) {
                                case 'Dropped':     $reasonBadge = 'badge-danger';    $reasonIcon = '❌'; break;
                                case 'Transferred': $reasonBadge = 'badge-warning';   $reasonIcon = '🔄'; break;
                                case 'LOA':         $reasonBadge = 'badge-info';      $reasonIcon = '⏸️'; break;
                                case 'Graduated':   $reasonBadge = 'badge-success';   $reasonIcon = '🎓'; break;
                                default:            $reasonBadge = 'badge-secondary'; $reasonIcon = '📦'; break;
                            }

                            $yearLevelText = 'N/A';
                            if (!empty($s['grade_level'])) {
                                $yearLevelText = $s['grade_level'];
                            } elseif (!empty($s['year_level'])) {
                                $yearLevelText = (int) $s['year_level'] . 'th Year';
                            }
                        ?>
                        <tr>
                            <td><strong>🎓 <?php echo htmlspecialchars($s['student_number'] ?? ''); ?></strong></td>
                            <td>
                                <?php echo htmlspecialchars($fullName); ?>
                                <?php if (!empty($s['email'])): ?>
                                    <br><small class="u-color-666-909c">✉️ <?php echo htmlspecialchars($s['email']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($s['section_code'])): ?>
                                    <?php echo htmlspecialchars($s['section_code']); ?>
                                    <br><small class="u-color-666-909c"><?php echo htmlspecialchars($s['section_semester'] ?? ''); ?></small>
                                <?php else: ?>
                                    <em class="u-color-999-d522">Not Assigned</em>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($s['course_code'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($yearLevelText); ?></td>
                            <td>
                                <?php echo htmlspecialchars($archivedDate); ?>
                                <?php if ($archivedTime): ?>
                                    <br><small class="u-color-666-909c"><?php echo htmlspecialchars($archivedTime); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge <?php echo htmlspecialchars($reasonBadge); ?>">
                                    <?php echo $reasonIcon; ?> <?php echo htmlspecialchars($reason); ?>
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($s['archived_by_name'])): ?>
                                    <?php echo htmlspecialchars($s['archived_by_name']); ?>
                                <?php elseif (!empty($s['archived_by_username'])): ?>
                                    <?php echo htmlspecialchars($s['archived_by_username']); ?>
                                <?php else: ?>
                                    <em class="u-color-999-d522">System</em>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="?page=student-details&id=<?php echo $studentId; ?>"
                                       class="btn btn-sm btn-info" title="View student details">
                                        👁️ View
                                    </a>
                                    <form method="POST" data-action-submit="action4" data-action4-arg-0="<?php echo htmlspecialchars(json_encode($fullName), ENT_QUOTES); ?>" data-action4-arg-1="<?php echo htmlspecialchars(json_encode($s['student_number'] ?? ''), ENT_QUOTES); ?>" class="u-display-inline-block-6a19">
                                        <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                                        <input type="hidden" name="action" value="restore">
                                        <input type="hidden" name="redirect" value="?page=archived-students">
                                        <button type="submit" class="btn btn-sm btn-success" title="Restore student">
                                            ♻️ Restore
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="text-center">
                        <div class="u-font-size-64px-2963 u-margin-bottom-15px-9ab9">📦</div>
                        <h3 class="u-color-666-909c u-margin-bottom-10px-a740">
                            <?php if ($search || $courseFilter || $yearFilter || $reasonFilter): ?>
                                Walang nakitang archived students
                            <?php else: ?>
                                Walang archived students
                            <?php endif; ?>
                        </h3>
                        <p class="u-color-999-d522 u-margin-bottom-20px-3019">
                            <?php if ($search || $courseFilter || $yearFilter || $reasonFilter): ?>
                                Walang match sa iyong filters. Subukan i-clear ang filters.
                            <?php else: ?>
                                Kapag nag-archive ka ng student, lalabas sila dito.
                            <?php endif; ?>
                        </p>
                        <div class="u-display-flex-f7db u-gap-10px-114b u-justify-content-center-9b54 u-flex-wrap-wrap-9012">
                            <?php if ($search || $courseFilter || $yearFilter || $reasonFilter): ?>
                                <a href="?page=archived-students" class="btn btn-secondary">Clear Filters</a>
                            <?php endif; ?>
                            <a href="?page=enrollments" class="btn btn-primary">← Back to Enrollments</a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
