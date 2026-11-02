<?php
// View data supplied by CoursesPage through EnrollmentPage.
/** @var array<string, mixed> $clientData */
/** @var string $message */
/** @var array<string, mixed> $stats */
/** @var array<int, array<string, mixed>> $courses */
/** @var array<int, array<string, mixed>> $popularCourses */
?>
<div id="page-data" hidden data-values="<?= PageView::escape(json_encode($clientData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE)) ?>"></div>





<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>📚 Courses Management</h1>
            <p class="dashboard-subtitle">Manage academic courses</p>
        </div>

        <div class="module-content">

            <!-- MESSAGES -->
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- STATISTICS -->
            <div class="stats-container">
                <div class="stat-card stat-total">
                    <span class="stat-number"><?php echo (int) ($stats['total_courses']  ?? count($courses)); ?></span>
                    <span class="stat-label">📚 Total Courses</span>
                </div>
                <div class="stat-card stat-sections">
                    <span class="stat-number"><?php echo (int) ($stats['total_sections'] ?? 0); ?></span>
                    <span class="stat-label">📋 Total Sections</span>
                </div>
                <div class="stat-card stat-students">
                    <span class="stat-number"><?php echo (int) ($stats['total_students'] ?? 0); ?></span>
                    <span class="stat-label">👨‍🎓 Total Students</span>
                </div>
            </div>

            <!-- POPULAR COURSES -->
            <?php if (!empty($popularCourses)): ?>
            <div class="popular-courses">
                <h3>🔥 Popular Courses</h3>
                <div class="popular-list">
                    <?php foreach ($popularCourses as $pc): ?>
                    <div class="popular-item">
                        <div class="course-name"><?php echo htmlspecialchars($pc['code'] ?? ''); ?></div>
                        <div class="course-stats">
                            <?php echo htmlspecialchars($pc['name'] ?? ''); ?><br>
                            <span><?php echo (int) ($pc['total_students'] ?? 0); ?></span> students enrolled
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- COURSES TABLE -->
            <div class="courses-table-container">
                <div class="table-header">
                    <span class="title">All Courses</span>
                    <div class="u-display-flex-f7db u-gap-10px-114b u-align-items-center-1e2c u-flex-wrap-wrap-9012">
                        <span class="badge-count"><?php echo count($courses); ?></span>
                        <button class="btn btn-primary btn-sm" data-action-click="action1">+ Add Course</button>
                    </div>
                </div>

                <table class="table">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Course Name</th>
                            <th>Years</th>
                            <th>Sections</th>
                            <th>Students</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($courses) > 0): ?>
                            <?php foreach ($courses as $c):
                                $courseId     = (int) ($c['id'] ?? 0);
                                $totalSections = (int) ($c['total_sections'] ?? 0);
                                $totalStudents = (int) ($c['total_students'] ?? 0);
                                $hasSections   = $totalSections > 0;
                                $canDelete     = $totalStudents === 0 && $totalSections === 0;
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($c['code'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars($c['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($c['years'] ?? ''); ?></td>
                                <td>
                                    <span class="badge badge-info"><?php echo $totalSections; ?></span>
                                </td>
                                <td>
                                    <span class="badge badge-success"><?php echo $totalStudents; ?></span>
                                </td>
                                <td>
                                    <?php if ($hasSections): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning">No Sections</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <button data-action-click="action2" data-action2-arg-0="<?php echo $courseId; ?>"
                                                class="btn btn-sm btn-warning">Edit</button>
                                        <a href="?page=sections&course_id=<?php echo $courseId; ?>"
                                           class="btn btn-sm btn-info">Sections</a>
                                        <form method="POST" data-action-submit="action3" class="u-display-inline-block-6a19">
                                            <input type="hidden" name="id" value="<?php echo $courseId; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <input type="hidden" name="redirect" value="?page=courses">
                                            <button type="submit"
                                                    class="btn btn-sm btn-danger"
                                                    <?php echo $canDelete ? '' : 'disabled'; ?>
                                                    title="<?php echo $canDelete
                                                        ? 'Delete this course'
                                                        : 'Cannot delete: course has sections or enrolled students'; ?>">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    No courses found.
                                    <a href="#" data-action-click="action4">Create one now</a>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- ADD/EDIT COURSE MODAL -->
<div id="courseModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="courseModalTitle">Add New Course</h2>
            <span class="close" data-action-click="action5">&times;</span>
        </div>
        <form method="POST" class="course-form" id="courseForm">
            <input type="hidden" name="id" id="courseId">
            <input type="hidden" name="action" id="courseAction" value="add">
            <input type="hidden" name="redirect" value="?page=courses">

            <div class="form-group">
                <label>Course Code <span class="required">*</span></label>
                <input type="text" name="code" id="courseCode" required
                       placeholder="e.g., BSIT" class="u-text-transform-uppercase-5f7a">
                <small>Use uppercase letters only (e.g., BSIT, BSCS)</small>
            </div>
            <div class="form-group">
                <label>Course Name <span class="required">*</span></label>
                <input type="text" name="name" id="courseName" required
                       placeholder="e.g., Bachelor of Science in Information Technology">
            </div>
            <div class="form-group">
                <label>Duration (Years) <span class="required">*</span></label>
                <input type="number" name="years" id="courseDuration"
                       value="4" min="2" max="6" required>
                <small>Number of years to complete the course (2-6 years)</small>
            </div>
            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action6">Cancel</button>
                <button type="submit" class="btn btn-primary" id="courseSubmitBtn">Save Course</button>
            </div>
        </form>
    </div>
</div>
