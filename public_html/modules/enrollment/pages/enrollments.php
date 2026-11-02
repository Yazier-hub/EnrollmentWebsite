<?php
// View data supplied by EnrollmentsPage through EnrollmentPage.
/** @var array<string, mixed> $clientData */
/** @var string $message */
/** @var int $pendingPaymentCount */
/** @var int $noVerifiedPaymentCount */
/** @var bool $accountCreated */
/** @var string $username */
/** @var string $password */
/** @var bool $isNewAccount */
/** @var bool $emailSent */
/** @var array<string, mixed> $stats */
/** @var int $availableSections */
/** @var int $noCourseCount */
/** @var string $search */
/** @var array<int, array<string, mixed>> $pendingApplicants */
/** @var array<int, array<string, mixed>> $enrollments */
/** @var int $archivedCount */
/** @var string $enrolledSearch */
/** @var array<int, array<string, mixed>> $courses */
/** @var int|string $courseFilter */
/** @var int|string $yearFilter */
/** @var array<int, array<string, mixed>> $enrollmentDetails */
/** @var string $currentSchoolYear */
/** @var string $nextSchoolYear */
/** @var StudentProgression $studentProgression */
?>
<div id="page-data" hidden data-values="<?= PageView::escape(json_encode($clientData, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE)) ?>"></div>





<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>🎯 Enrollment Management</h1>
            <p class="dashboard-subtitle">Enroll ONE student at a time — subjects are auto-loaded from the curriculum</p>
        </div>

        <div class="module-content">

            <?php if ($message !== ''): ?>
                <?php
                    $alertClass = 'danger';
                    if (strpos($message, '✅') !== false) $alertClass = 'success';
                    elseif (strpos($message, '⚠️') !== false) $alertClass = 'warning';
                ?>
                <div class="alert alert-<?php echo $alertClass; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($pendingPaymentCount > 0 || $noVerifiedPaymentCount > 0): ?>
                <div class="alert alert-warning u-border-left-4px-solid-f59e0b-6423">
                    <?php if ($pendingPaymentCount > 0): ?>
                        <strong>💳 <?php echo $pendingPaymentCount; ?> student(s) have pending payment.</strong>
                        <a href="?page=requirements&view=payments&status=pending">View pending payments →</a>
                    <?php endif; ?>
                    <?php if ($noVerifiedPaymentCount > 0): ?>
                        <br>
                        <strong>💸 <?php echo $noVerifiedPaymentCount; ?> student(s) have NO verified payment for the current term.</strong>
                        Hindi sila makakapag-enroll sa susunod na semester hanggang may verified payment.
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <?php if ($accountCreated && $username !== '' && $password !== ''): ?>
                <div class="alert alert-success u-border-left-4px-solid-2a5c9e-491e">
                    <div class="u-display-flex-f7db u-align-items-flex-start-b505 u-gap-15px-7d09 u-flex-wrap-wrap-9012">
                        <div class="u-font-size-24px-fcc5">🔐</div>
                        <div class="u-flex-1-da5c">
                            <strong>Student Account Created!</strong>
                            <div class="u-margin-top-8px-d431 u-padding-10px-6b28 u-background-f8f9fa-7c6e u-border-radius-5px-1ce3 u-border-1px-solid-c8e6c9-1a3b u-max-width-600px-3e9d">
                                <div class="u-display-grid-998b u-grid-template-columns-100px-1fr-a8d2 u-gap-5px-445f">
                                    <span class="u-font-weight-600-e634">Username:</span>
                                    <span><code class="u-background-e9ecef-9177 u-padding-2px-8px-1e18 u-border-radius-3px-5dae"><?php echo htmlspecialchars($username); ?></code></span>
                                    <span class="u-font-weight-600-e634">Password:</span>
                                    <span><code class="u-background-e9ecef-9177 u-padding-2px-8px-1e18 u-border-radius-3px-5dae"><?php echo htmlspecialchars($password); ?></code></span>
                                </div>
                                <?php if ($isNewAccount): ?>
                                    <small class="u-color-2a5c9e-2521 u-display-block-fa9a u-margin-top-5px-351b">✅ New account created.</small>
                                <?php else: ?>
                                    <small class="u-color-4a90d9-64f8 u-display-block-fa9a u-margin-top-5px-351b">⚠️ Account already existed.</small>
                                <?php endif; ?>
                                <?php if ($emailSent): ?>
                                    <small class="u-color-2a5c9e-2521 u-display-block-fa9a">📧 ✅ Credentials sent to student's email</small>
                                <?php else: ?>
                                    <small class="u-color-0f2a4e-9277 u-display-block-fa9a">📧 ⚠️ Email not sent. Please provide credentials manually.</small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="number"><?php echo (int) ($stats['total_enrolled']         ?? 0); ?></div>
                    <div class="label">Total Enrolled Students</div>
                </div>
                <div class="stat-card success">
                    <div class="number"><?php echo (int) ($stats['total_sections']         ?? 0); ?></div>
                    <div class="label">Total Sections</div>
                </div>
                <div class="stat-card warning">
                    <div class="number"><?php echo (int) ($stats['pending_applications']   ?? 0); ?></div>
                    <div class="label">Pending Applications</div>
                </div>
                <div class="stat-card danger">
                    <div class="number"><?php echo (int) ($stats['recent_enrollments']     ?? 0); ?></div>
                    <div class="label">Recent Enrollments (30 days)</div>
                </div>
            </div>

            <?php if ($availableSections === 0): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ No available sections!</strong>
                    Please <a href="?page=sections">add a section</a> before enrolling students.
                </div>
            <?php endif; ?>

            <?php if ($noCourseCount > 0): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ <?php echo $noCourseCount; ?> applicant(s) have no course assigned!</strong>
                    Please <a href="?page=applications">edit the applications</a> to assign a course.
                </div>
            <?php endif; ?>

            <div class="search-section">
                <form method="GET" class="search-form" action="?page=enrollments">
                    <input type="hidden" name="page" value="enrollments">
                    <input type="text" name="search"
                           placeholder="Search applicants by name, email, or contact..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="?page=enrollments" class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <div class="students-ready-section">
                <div class="section-header-flex">
                    <div>
                        <h2>📋 Pending Applicants (<?php echo count($pendingApplicants); ?>)</h2>
                        <p class="section-note">Click "Enroll" — subjects will be auto-loaded from the curriculum</p>
                    </div>
                </div>

                <?php if (count($pendingApplicants) > 0): ?>
                    <div class="table-scroll-container">
                        <table class="table table-compact">
                            <thead>
                                <tr>
                                    <th>Applicant Name</th>
                                    <th>Course</th>
                                    <th>Email</th>
                                    <th>Contact</th>
                                    <th>Type</th>
                                    <th class="u-text-align-right-f6e3">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingApplicants as $app):
                                    $name = trim(
                                        ($app['first_name'] ?? '')
                                        . ' ' . ($app['middle_name'] ?? '')
                                        . ' ' . ($app['surname'] ?? '')
                                    );
                                    if (!empty($app['suffix'])) $name .= ' ' . $app['suffix'];

                                    $admissionType = $app['admission_type'] ?? 'freshmen';
                                    $badgeMap = [
                                        'freshmen'    => 'badge-freshmen',
                                        'transferee'  => 'badge-transferee',
                                        'returnee'    => 'badge-returnee',
                                        'senior_high' => 'badge-senior_high'
                                    ];
                                    $badgeClass = $badgeMap[$admissionType] ?? 'badge-default';
                                    $badgeLabel = ucfirst(str_replace('_', ' ', $admissionType));

                                    $canEnroll = !empty($app['course_id']) && $availableSections > 0;
                                ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                                    <td>📚 <?php echo htmlspecialchars($app['course_code'] ?? 'No Course'); ?></td>
                                    <td>✉️ <?php echo htmlspecialchars($app['email'] ?? ''); ?></td>
                                    <td>📱 <?php echo htmlspecialchars($app['contact_number'] ?? ''); ?></td>
                                    <td>
                                        <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                                            <?php echo htmlspecialchars($badgeLabel); ?>
                                        </span>
                                    </td>
                                    <td class="u-text-align-right-f6e3">
                                        <?php if ($canEnroll): ?>
                                            <button class="btn btn-sm btn-success" data-action-click="action1" data-action1-arg-0="<?php echo (int) $app['applicant_id']; ?>" data-action1-arg-1="<?php echo htmlspecialchars(json_encode($name), ENT_QUOTES); ?>" data-action1-arg-2="<?php echo htmlspecialchars(json_encode($app['course_code'] ?? 'N/A'), ENT_QUOTES); ?>" data-action1-arg-3="<?php echo htmlspecialchars(json_encode($admissionType), ENT_QUOTES); ?>" data-action1-arg-4="<?php echo (int) $app['course_id']; ?>">
                                                🎓 Enroll
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm btn-secondary" disabled>
                                                ⚠️ Cannot Enroll
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <?php if ($search !== ''): ?>
                            No pending applications found for
                            "<strong><?php echo htmlspecialchars($search); ?></strong>".
                            <a href="?page=enrollments">Clear search</a>
                        <?php else: ?>
                            No pending applications.
                            <a href="?page=applications">Submit an application</a> first.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="enrollments-table-container">
                <div class="u-display-flex-f7db u-justify-content-space-between-daae u-align-items-center-1e2c u-flex-wrap-wrap-9012 u-gap-10px-114b u-margin-bottom-15px-9ab9">
                    <h2 class="u-margin-0-1386">✅ Enrolled Students (<?php echo count($enrollments); ?>)</h2>
                    <div class="u-display-flex-f7db u-gap-10px-114b u-flex-wrap-wrap-9012">
                        <a href="?page=enrollments" class="btn btn-sm btn-info">View All</a>
                        <a href="?page=students" class="btn btn-sm btn-secondary">All Students</a>
                        <a href="?page=archived-students" class="btn btn-sm btn-warning">
                            📦 Archived (<?php echo $archivedCount; ?>)
                        </a>
                    </div>
                </div>

                <div class="filters-section">
                    <form method="GET" class="filters-form" action="?page=enrollments">
                        <input type="hidden" name="page" value="enrollments">

                        <div class="filter-group filter-group-search">
                            <label for="enrolled_search">Search</label>
                            <div class="search-input-group">
                                <input type="text"
                                       name="enrolled_search"
                                       id="enrolled_search"
                                       placeholder="Search by student #, name, or section..."
                                       value="<?php echo htmlspecialchars($enrolledSearch ?? ''); ?>"
                                       class="enrolled-search-input">
                                <button type="submit" class="btn btn-primary btn-search">🔍 Search</button>
                            </div>
                        </div>

                        <div class="filter-group">
                            <label for="course_filter">Course</label>
                            <select name="course_id" id="course_filter" data-action-change="action2">
                                <option value="">All Courses</option>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo (int) $c['id']; ?>"
                                        <?php echo $courseFilter == $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="year_filter">Year Level</label>
                            <select name="year_level" id="year_filter" data-action-change="action3">
                                <option value="">All Years</option>
                                <?php foreach ([1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'] as $y => $lbl): ?>
                                    <option value="<?php echo $y; ?>" <?php echo $yearFilter == $y ? 'selected' : ''; ?>>
                                        <?php echo $lbl; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="filter-actions">
                            <?php if ($courseFilter || $yearFilter || !empty($enrolledSearch)): ?>
                                <a href="?page=enrollments" class="btn btn-secondary btn-sm">Clear Filters</a>
                            <?php endif; ?>
                        </div>
                    </form>
                </div>

                <?php if (count($enrollments) > 0): ?>
                <div class="table-scroll-container table-scroll-large">
                    <table class="table table-compact">
                        <thead>
                            <tr>
                                <th>Student #</th>
                                <th>Name</th>
                                <th>Section</th>
                                <th>Course</th>
                                <th>Current</th>
                                <th>Next</th>
                                <th>Subjects</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($enrollments as $enroll):
                                $fullName = trim(
                                    ($enroll['first_name'] ?? '')
                                    . ' ' . ($enroll['middle_name'] ?? '')
                                    . ' ' . ($enroll['surname'] ?? '')
                                );
                                if (!empty($enroll['suffix'])) $fullName .= ' ' . $enroll['suffix'];

                                $subjectCount = isset($enroll['subject_count']) ? (int) $enroll['subject_count'] : 0;

                                $studentId = (int) $enroll['student_id'];

                                // ⭐ Payment gate (term-based)
                                $paymentGate        = $enrollmentDetails[$studentId]['paymentGate'];
                                $hasPendingPayment  = $enrollmentDetails[$studentId]['hasPendingPayment'];
                                $hasVerifiedCurrent = $enrollmentDetails[$studentId]['hasVerifiedCurrent'];
                                $latestPayment      = $enrollmentDetails[$studentId]['latestPayment'];

                                $current = $enrollmentDetails[$studentId]['current'];
                                $next    = $enrollmentDetails[$studentId]['next'];

                                $currentYearLevel = $current ? (int) $current['year_level'] : 1;
                                $nextYearLevel    = $next    ? (int) $next['year_level']    : 1;
                                $academicYearChanges = ($currentYearLevel != $nextYearLevel && $nextYearLevel > $currentYearLevel);
                                $currentSchoolYearDisplay = $currentSchoolYear;
                                $nextSchoolYearDisplay    = $academicYearChanges ? $nextSchoolYear : $currentSchoolYear;

                                // Override canProgress kung hindi papasa sa payment gate
                                if (empty($paymentGate['can_enroll'])) {
                                    $canProgress   = false;
                                    $waitForGrades = false;
                                } else {
                                    $canProgress   = $next && empty($next['is_completed']) && !empty($next['can_progress']);
                                    $waitForGrades = $next && empty($next['is_completed']) && empty($next['can_progress']);
                                }

                                // Row background
                                $rowClass = '';
                                if ($hasPendingPayment) {
                                    $rowClass = 'payment-pending-row';
                                } elseif (empty($paymentGate['can_enroll'])) {
                                    $rowClass = 'payment-blocked-row';
                                }
                            ?>
                            <tr class="<?= $rowClass ?>">
                                <td><strong>🎓 <?php echo htmlspecialchars($enroll['student_number'] ?? ''); ?></strong></td>
                                <td><?php echo htmlspecialchars($fullName); ?></td>
                                <td><?php echo htmlspecialchars($enroll['section_code'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($enroll['course_code'] ?? 'N/A'); ?></td>
                                <td>
                                    <?php if ($current): ?>
                                        <span class="badge badge-primary">
                                            <?php echo htmlspecialchars($studentProgression->getYearLevelText($current['year_level'])); ?>
                                            <?php echo htmlspecialchars($current['semester_name'] ?? ''); ?>
                                        </span>
                                        <br><small class="u-color-5a7fa8-c092">SY: <?php echo htmlspecialchars($currentSchoolYearDisplay); ?></small>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if (!empty($paymentGate['can_enroll'])): ?>
                                        <?php if ($next && empty($next['is_completed'])): ?>
                                            <span class="badge badge-warning">
                                                <?php echo htmlspecialchars($studentProgression->getYearLevelText($next['year_level'])); ?>
                                                <?php echo htmlspecialchars($studentProgression->getSemesterName($next['semester'])); ?>
                                            </span>
                                            <?php if ($academicYearChanges): ?>
                                                <br><small class="u-color-0f2a4e-9277 u-font-weight-bold-0a15">📅 New SY: <?php echo htmlspecialchars($nextSchoolYearDisplay); ?></small>
                                            <?php else: ?>
                                                <br><small class="u-color-5a7fa8-c092">Same SY</small>
                                            <?php endif; ?>
                                        <?php elseif ($next && !empty($next['is_completed'])): ?>
                                            <span class="badge badge-success">🎓 Graduated</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Completed</span>
                                        <?php endif; ?>
                                    <?php elseif ($hasPendingPayment): ?>
                                        <span class="badge u-background-f59e0b-a76e u-color-white-6304">💳 Pending Payment</span>
                                        <br><small class="u-color-92400e-9a2b u-font-weight-bold-0a15">Verify payment first</small>
                                    <?php else: ?>
                                        <span class="badge u-background-ef4444-82b6 u-color-white-6304">💸 No Verified Payment</span>
                                        <br><small class="u-color-991b1b-2174 u-font-weight-bold-0a15">Submit payment for this term</small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge badge-info"><?php echo $subjectCount; ?> Subjects</span></td>
                                <td><span class="status-badge status-enrolled">Enrolled</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?page=student-details&id=<?php echo $studentId; ?>"
                                           class="btn btn-sm btn-info">View</a>
                                        <button data-action-click="action4" data-action4-arg-0="<?php echo $studentId; ?>"
                                                class="btn btn-sm btn-primary">📅</button>

                                        <?php if (!empty($paymentGate['can_enroll'])): ?>
                                            <?php if ($canProgress): ?>
                                                <button class="btn btn-sm btn-success" data-action-click="action5" data-action5-arg-0="<?php echo $studentId; ?>" data-action5-arg-1="<?php echo htmlspecialchars(json_encode($fullName), ENT_QUOTES); ?>" data-action5-arg-2="<?php echo (int) $next['year_level']; ?>" data-action5-arg-3="<?php echo (int) $next['semester']; ?>" data-action5-arg-4="<?php echo htmlspecialchars(json_encode($nextSchoolYearDisplay), ENT_QUOTES); ?>">
                                                    📈 Next
                                                </button>
                                            <?php elseif ($waitForGrades): ?>
                                                <button class="btn btn-sm btn-secondary" disabled
                                                    title="Kailangan munang tapusin ang current semester at may grades bago makapag-enroll sa susunod na semester">
                                                    ⏳
                                                </button>
                                            <?php endif; ?>
                                        <?php elseif ($hasPendingPayment): ?>
                                            <button class="btn btn-sm btn-warning" disabled
                                                title="May pending payment — kailangan munang ma-verify bago makapag-enroll">
                                                💳 Pending
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm u-background-ef4444-82b6 u-color-white-6304" disabled
                                                title="Wala pang verified payment para sa kasalukuyang semester">
                                                💸 No Payment
                                            </button>
                                        <?php endif; ?>

                                        <button class="btn btn-sm btn-warning" data-action-click="action6" data-action6-arg-0="<?php echo $studentId; ?>" data-action6-arg-1="<?php echo htmlspecialchars(json_encode($fullName), ENT_QUOTES); ?>">
                                            📦
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                    <div class="text-center">
                        <?php if ($courseFilter || $yearFilter || !empty($enrolledSearch)): ?>
                            No enrolled students found matching your filters.
                            <a href="?page=enrollments">Clear filters</a>
                        <?php else: ?>
                            No enrollments found.
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- ENROLLMENT MODAL -->
<div id="enrollmentModal" class="modal">
    <div class="modal-content">
        <span class="close" data-action-click="action7">&times;</span>
        <h2>🎯 Enroll New Student</h2>

        <div class="enroll-info">
            <p><strong>Student:</strong> <span id="applicantNameDisplay"></span></p>
            <p><strong>Course:</strong> <span id="applicantCourseDisplay"></span></p>
            <p><strong>Admission Type:</strong> <span id="applicantTypeDisplay"></span></p>
            <p class="u-color-1a3c6e-5e20 u-font-weight-bold-0a15">⚠️ Student number will be generated upon enrollment</p>
            <p class="u-color-2a5c9e-2521 u-font-weight-bold-0a15 u-font-style-italic-4ddb u-font-size-13px-3044">
                ℹ️ Ang lahat ng curriculum subjects ay awtomatikong isasama —
                hindi na kailangan i-select.
            </p>
        </div>

        <form method="POST" class="enrollment-form" id="enrollmentForm" data-action-submit="action8">
            <input type="hidden" name="applicant_id" id="applicantId">
            <input type="hidden" name="action" value="enroll">
            <input type="hidden" name="redirect" value="?page=enrollments">
            <input type="hidden" name="course_id" id="courseId">
            <input type="hidden" name="schedule_ids" id="scheduleIdsInput" value="">

            <div class="form-group">
                <label>Select Section *</label>
                <select name="section_id" id="sectionSelect" required data-action-change="action9">
                    <option value="">Choose a section</option>
                </select>
                <small id="sectionInfo" class="u-color-5a7fa8-c092 u-display-block-fa9a u-margin-top-5px-351b"></small>
            </div>

            <div class="form-group">
                <label>School Year *</label>
                <input type="text" name="school_year" value="<?php echo htmlspecialchars($currentSchoolYear); ?>" required>
                <small class="u-color-5a7fa8-c092">Current Academic Year: <?php echo htmlspecialchars($currentSchoolYear); ?></small>
            </div>

            <div class="subjects-section u-display-none-cb45" id="subjectsSection">
                <div class="subjects-header">
                    <h3>📘 Curriculum Subjects (Auto-Loaded)</h3>
                </div>
                <p class="subjects-note">
                    Ang mga subjects ay awtomatikong naka-load mula sa curriculum ng course
                    para sa section na ito. <strong>Required lahat — hindi pwedeng i-deselect.</strong>
                </p>
                <div id="subjectsList">
                    <div class="loading-text">Select a section first...</div>
                </div>
                <p class="u-margin-top-10px-5371 u-color-5a7fa8-c092 u-font-size-13px-3044">
                    Auto-included: <span id="selectedSubjectsCount" class="selected-count">0</span> subjects
                </p>
                <p class="u-margin-top-5px-351b u-color-5a7fa8-c092 u-font-size-13px-3044">
                    <span id="scheduleInfo" class="u-color-1a3c6e-5e20 u-font-weight-bold-0a15"></span>
                </p>
            </div>

            <div class="requirements-section">
                <div class="requirements-header">
                    <h3>📋 Requirements</h3>
                    <label class="select-all-label">
                        <input type="checkbox" id="selectAllRequirements" data-action-change="action10">
                        <span>Select All</span>
                    </label>
                </div>
                <p class="requirements-note">✅ Mandatory requirements are pre-checked. Uncheck only if not yet submitted.</p>
                <div id="requirementsList">
                    <div class="loading-text">Loading requirements...</div>
                </div>
                <p class="u-margin-top-10px-5371 u-color-5a7fa8-c092 u-font-size-13px-3044">
                    Selected: <span id="selectedRequirementsCount" class="selected-count">0</span> requirements
                </p>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action11">Cancel</button>
                <button type="submit" class="btn btn-success u-font-size-16px-2592 u-padding-10px-30px-18b0">
                    🎓 Enroll Student
                </button>
            </div>
        </form>
    </div>
</div>

<!-- PROGRESSION MODAL -->
<div id="progressionModal" class="modal">
    <div class="modal-content u-max-width-800px-c231">
        <span class="close" data-action-click="action12">&times;</span>
        <h2>📈 Enroll for Next Semester</h2>

        <div class="enroll-info" id="progressionInfo">
            <p><strong>Student:</strong> <span id="progressionStudentName"></span></p>
            <p><strong>Next:</strong> <span id="progressionNextLevel"></span></p>
            <p><strong>Academic Year:</strong> <span id="progressionSchoolYear"></span></p>
            <p class="u-color-4a90d9-64f8 u-font-weight-bold-0a15">🟡 Retake subjects — kailangang i-retake</p>
            <p class="u-color-0f2a4e-9277 u-font-weight-bold-0a15">🔴 Blocked subjects — may hindi pa natapos na prereq</p>
            <p class="u-color-2a5c9e-2521 u-font-weight-bold-0a15 u-font-style-italic-4ddb u-font-size-13px-3044">
                ℹ️ Ang lahat ng Available (regular) subjects ay awtomatikong isasama sa enrollment —
                hindi na kailangan i-select.
            </p>
        </div>

        <form method="POST" class="enrollment-form" id="progressionForm" data-action-submit="action13">
            <input type="hidden" name="student_id" id="progressionStudentId">
            <input type="hidden" name="action" value="progression_enroll">
            <input type="hidden" name="redirect" value="?page=enrollments">
            <input type="hidden" name="schedule_ids" id="progressionScheduleIds" value="">

            <div class="form-group">
                <label>Select Section *</label>
                <select name="section_id" id="progressionSectionSelect" required data-action-change="action14">
                    <option value="">Loading sections...</option>
                </select>
                <small id="progressionSectionInfo" class="u-color-5a7fa8-c092 u-display-block-fa9a u-margin-top-5px-351b"></small>
            </div>

            <div class="form-group">
                <label>School Year *</label>
                <input type="text" name="school_year" id="progressionSchoolYearInput"
                       value="<?php echo htmlspecialchars($currentSchoolYear); ?>" required>
                <small id="progressionSchoolYearHint" class="u-color-5a7fa8-c092"></small>
            </div>

            <div class="subjects-section u-display-none-cb45" id="progressionSubjectsSection">
                <div class="subjects-header">
                    <h3>📚 Subjects na Kailangang I-enroll</h3>
                </div>
                <p class="u-color-5a7fa8-c092 u-font-size-13px-3044 u-margin-bottom-10px-a740">
                    Ang AVAILABLE subjects ay awtomatikong isinasama (hidden). Ang makikita lang
                    dito ay ang mga <strong>RETAKE</strong> at <strong>BLOCKED</strong> na subjects.
                </p>
                <div id="progressionSubjectsList">
                    <div class="loading-text">Loading subjects...</div>
                </div>
                <p class="u-margin-top-10px-5371 u-color-5a7fa8-c092 u-font-size-13px-3044">
                    Auto-included: <span id="progressionSelectedCount" class="selected-count">0</span> subjects
                </p>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action15">Cancel</button>
                <button type="submit" class="btn btn-success u-font-size-16px-2592 u-padding-10px-30px-18b0">
                    🎓 Enroll Next Semester
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ARCHIVE MODAL -->
<div id="archiveModal" class="modal">
    <div class="modal-content u-max-width-500px-5ab1">
        <span class="close" data-action-click="action16">&times;</span>
        <h2>📦 Archive Student</h2>

        <div class="enroll-info">
            <p><strong>Student:</strong> <span id="archiveStudentName"></span></p>
            <p class="u-color-4a90d9-64f8 u-font-weight-bold-0a15">⚠️ Ang student ay hindi made-delete — ma-archive lang. Pwede pang i-restore.</p>
        </div>

        <form method="POST" class="enrollment-form" id="archiveForm">
            <input type="hidden" name="student_id" id="archiveStudentId">
            <input type="hidden" name="action" value="archive">
            <input type="hidden" name="redirect" value="?page=enrollments">

            <div class="form-group">
                <label>Reason for Archiving *</label>
                <select name="archive_reason" id="archiveReason" required>
                    <option value="Dropped">Dropped</option>
                    <option value="Transferred">Transferred</option>
                    <option value="LOA">Leave of Absence (LOA)</option>
                    <option value="Graduated">Graduated</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action17">Cancel</button>
                <button type="submit" class="btn btn-warning u-font-size-16px-2592 u-padding-10px-30px-18b0">
                    📦 Archive Student
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SCHEDULE MODAL -->
<div id="scheduleModal" class="modal">
    <div class="modal-content u-max-width-900px-10ef">
        <span class="close" data-action-click="action18">&times;</span>
        <h2>📅 Student Schedule</h2>
        <div id="scheduleContent">
            <div class="loading-text">Loading schedule...</div>
        </div>
    </div>
</div>
