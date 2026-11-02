<?php
// View data supplied by StudentDetailsPage through EnrollmentPage.
/** @var string $fullName */
/** @var array<string, mixed> $student */
/** @var string $message */
/** @var string $statusBadgeClass */
/** @var string $statusLabel */
/** @var string $admissionBadgeClass */
/** @var string $admissionLabel */
/** @var array<string, mixed>|false|null $enrollmentDetails */
/** @var int $subjectCount */
/** @var array<string, mixed>|null $completionStatus */
/** @var bool $mandatoryCompleted */
/** @var array<int, array<string, mixed>> $subjects */
/** @var array<int, array<string, mixed>> $requirements */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>👨‍🎓 Student Details</h1>
            <p class="dashboard-subtitle">
                <?php echo htmlspecialchars($fullName); ?>
                • Student #<?php echo htmlspecialchars($student['student_number'] ?? ''); ?>
            </p>
        </div>

        <div class="module-content">

            <!-- MESSAGES -->
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- STUDENT PROFILE -->
            <div class="student-profile">
                <div class="profile-header">
                    <div class="profile-info">
                        <div class="student-name"><?php echo htmlspecialchars($fullName); ?></div>
                        <div class="student-number">
                            🎓 Student #: <?php echo htmlspecialchars($student['student_number'] ?? ''); ?>
                        </div>
                        <div class="profile-badges">
                            <span class="badge-lg <?php echo htmlspecialchars($statusBadgeClass); ?>">
                                <?php echo htmlspecialchars($statusLabel); ?>
                            </span>
                            <span class="badge-lg <?php echo htmlspecialchars($admissionBadgeClass); ?>">
                                <?php echo htmlspecialchars($admissionLabel); ?>
                            </span>
                            <?php if ($enrollmentDetails): ?>
                                <span class="badge-lg badge-active">✅ Currently Enrolled</span>
                            <?php else: ?>
                                <span class="badge-lg badge-inactive">❌ Not Enrolled</span>
                            <?php endif; ?>
                            <?php if (($student['followup_status'] ?? '') === 'pending' && !empty($student['followup_date'])): ?>
                                <span class="badge-lg u-background-e8f0fe-86ba u-color-2a5c9e-2521 u-border-1px-solid-b8d4e8-1032">
                                    📅 Follow-up: <?php echo htmlspecialchars(date('M d, Y', strtotime($student['followup_date']))); ?>
                                </span>
                            <?php endif; ?>
                            <span class="badge-lg u-background-4a90d9-8491 u-color-white-6304">
                                📚 <?php echo $subjectCount; ?> Subjects
                            </span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <a href="?page=students" class="btn btn-secondary">← Back</a>
                        <a href="?page=student-edit&id=<?php echo (int) $student['student_id']; ?>" class="btn btn-warning">✏️ Edit</a>
                        <a href="?page=student-subjects&id=<?php echo (int) $student['student_id']; ?>" class="btn btn-info">📚 Subjects</a>
                        <a href="?page=requirements&student_id=<?php echo (int) $student['student_id']; ?>&view=students" class="btn btn-primary">📋 Requirements</a>
                        <?php if ($enrollmentDetails): ?>
                            <button class="btn btn-danger" data-action-click="action1">📦 Archive</button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DETAILS GRID -->
                <div class="details-grid">
                    <!-- Personal Information -->
                    <div class="detail-section">
                        <h3>📋 Personal Information</h3>
                        <div class="detail-row"><span class="detail-label">First Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['first_name'] ?? ''); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Middle Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['middle_name'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Surname</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['surname'] ?? ''); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Suffix</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['suffix'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Admission Type</span>
                            <span class="detail-value"><?php echo htmlspecialchars($admissionLabel); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Sex</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['sex'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Date of Birth</span>
                            <span class="detail-value"><?php echo !empty($student['date_of_birth']) ? htmlspecialchars(date('M d, Y', strtotime($student['date_of_birth']))) : 'N/A'; ?></span></div>
                        <div class="detail-row"><span class="detail-label">Age</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['age'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Place of Birth</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['place_of_birth'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Civil Status</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['civil_status'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Religion</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['religion'] ?? 'N/A'); ?></span></div>
                    </div>

                    <!-- Contact & Address -->
                    <div class="detail-section">
                        <h3>📞 Contact Information</h3>
                        <div class="detail-row"><span class="detail-label">Email</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Contact Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['contact_number'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Facebook</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['facebook'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Messenger</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['messenger'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-e8f4fd-fd6c">
                            <span class="detail-label">Barangay</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['address_barangay'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">City/Municipality</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['address_city'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Province</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['address_province'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Complete Address</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['address_complete'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-e8f4fd-fd6c">
                            <span class="detail-label"><strong>Parent/Guardian</strong></span>
                            <span class="detail-value"></span></div>
                        <div class="detail-row"><span class="detail-label">Parent Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['parent_full_name'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Parent Contact</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['parent_contact'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Parent Address</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['parent_address'] ?? 'N/A'); ?></span></div>
                    </div>

                    <!-- Academic Information -->
                    <div class="detail-section">
                        <h3>🎓 Academic Information</h3>
                        <div class="detail-row"><span class="detail-label">Course</span>
                            <span class="detail-value"><?php echo htmlspecialchars(
                                ($student['course_code'] ?? '') . ' - ' . ($student['course_name'] ?? '')
                            ); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Section</span>
                            <span class="detail-value">
                                <?php echo htmlspecialchars($student['section_code'] ?? 'Not Assigned'); ?>
                                <?php if (!empty($student['grade_level'])): ?>
                                    (<?php echo htmlspecialchars($student['grade_level']); ?>)
                                <?php endif; ?>
                            </span></div>
                        <div class="detail-row"><span class="detail-label">Year Level</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">School Last Attended</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['school_last_attended'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Year Graduated</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['year_graduated'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">How Heard</span>
                            <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($student['how_hear'] ?? 'N/A'))); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Enrolled At</span>
                            <span class="detail-value"><?php echo !empty($student['enrolled_at']) ? htmlspecialchars(date('M d, Y h:i A', strtotime($student['enrolled_at']))) : 'N/A'; ?></span></div>
                    </div>

                    <!-- Status Information -->
                    <div class="detail-section">
                        <h3>📊 Status Information</h3>
                        <div class="detail-row"><span class="detail-label">Enrollment Status</span>
                            <span class="detail-value">
                                <span class="badge-lg <?php echo htmlspecialchars($statusBadgeClass); ?> u-font-size-12px-6b9c u-padding-4px-12px-6ab3">
                                    <?php echo htmlspecialchars($statusLabel); ?>
                                </span>
                            </span></div>
                        <div class="detail-row"><span class="detail-label">Working Student</span>
                            <span class="detail-value"><?php echo htmlspecialchars($student['working_student'] ?? 'No'); ?></span></div>
                        <?php if (!empty($student['followup_date'])): ?>
                            <div class="detail-row"><span class="detail-label">Follow-up Date</span>
                                <span class="detail-value"><?php echo htmlspecialchars(date('M d, Y', strtotime($student['followup_date']))); ?></span></div>
                        <?php endif; ?>
                        <?php if (!empty($student['followup_notes'])): ?>
                            <div class="detail-row"><span class="detail-label">Follow-up Notes</span>
                                <span class="detail-value u-text-align-left-200a u-max-width-200px-3831"><?php echo htmlspecialchars($student['followup_notes']); ?></span></div>
                        <?php endif; ?>
                        <div class="detail-row"><span class="detail-label">Follow-up Status</span>
                            <span class="detail-value">
                                <span class="badge-lg u-font-size-12px-6b9c u-padding-4px-12px-6ab3 u-dynamic-background u-dynamic-color" data-css-background="<?php echo ($student['followup_status'] ?? '') === 'done' ? '#d4e8fc' : '#e8f0fe'; ?>" data-css-color="<?php echo ($student['followup_status'] ?? '') === 'done' ? '#1a3c6e' : '#2a5c9e'; ?>">
                                    <?php echo ucfirst(htmlspecialchars($student['followup_status'] ?? 'N/A')); ?>
                                </span>
                            </span></div>
                        <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-e8f4fd-fd6c">
                            <span class="detail-label">Requirements Progress</span>
                            <span class="detail-value">
                                <?php $pct = (int) ($completionStatus['percentage'] ?? 0); ?>
                                <span class="u-font-weight-600-e634 u-dynamic-color" data-css-color="<?php echo $pct >= 80 ? '#2a5c9e' : ($pct >= 50 ? '#4a90d9' : '#0f2a4e'); ?>">
                                    <?php echo $pct; ?>%
                                </span>
                                <br>
                                <small class="u-color-5a7fa8-c092 u-font-weight-normal-9aa7">
                                    <?php echo (int) ($completionStatus['submitted'] ?? 0); ?>/<?php echo (int) ($completionStatus['total'] ?? 0); ?> submitted
                                </small>
                                <?php if ($mandatoryCompleted): ?>
                                    <br><small class="u-color-2a5c9e-2521 u-font-weight-bold-0a15">✅ All mandatory requirements completed</small>
                                <?php else: ?>
                                    <br><small class="u-color-0f2a4e-9277">⚠️ Some mandatory requirements pending</small>
                                <?php endif; ?>
                            </span></div>
                    </div>
                </div>
            </div>

            <!-- FOLLOW-UP STATUS -->
            <?php if (($student['followup_status'] ?? '') === 'pending' && !empty($student['followup_date'])): ?>
                <div class="followup-card">
                    <div class="followup-label">📅 Follow-up Scheduled</div>
                    <p class="u-margin-5px-0-8ac3"><strong>Date:</strong> <?php echo htmlspecialchars(date('l, F d, Y', strtotime($student['followup_date']))); ?></p>
                    <?php if (!empty($student['followup_notes'])): ?>
                        <p class="u-margin-5px-0-8ac3"><strong>Notes:</strong> <?php echo htmlspecialchars($student['followup_notes']); ?></p>
                    <?php endif; ?>
                    <a href="?page=requirements&student_id=<?php echo (int) $student['student_id']; ?>&view=followup"
                       class="btn btn-sm btn-primary u-margin-top-5px-351b">View in Follow-ups</a>
                </div>
            <?php endif; ?>

            <!-- ENROLLMENT INFORMATION -->
            <?php if ($enrollmentDetails): ?>
                <div class="enrollment-card">
                    <h2>📌 Current Enrollment</h2>
                    <div class="enrollment-grid">
                        <div class="enrollment-item">
                            <div class="label">School Year</div>
                            <div class="value"><?php echo htmlspecialchars($enrollmentDetails['school_year'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="enrollment-item">
                            <div class="label">Section</div>
                            <div class="value"><?php echo htmlspecialchars($enrollmentDetails['section_code'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="enrollment-item">
                            <div class="label">Grade Level</div>
                            <div class="value"><?php echo htmlspecialchars($enrollmentDetails['grade_level'] ?? 'N/A'); ?></div>
                        </div>
                        <div class="enrollment-item">
                            <div class="label">Subjects</div>
                            <div class="value"><?php echo $subjectCount; ?></div>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    ℹ️ This student is not currently enrolled in any section.
                    <a href="?page=enrollments&student_id=<?php echo (int) $student['student_id']; ?>"
                       class="btn btn-sm btn-success u-margin-left-10px-6106">Enroll Now</a>
                </div>
            <?php endif; ?>

            <!-- SUBJECTS SECTION -->
            <div class="subjects-section">
                <div class="subjects-header">
                    <h2>📚 Enrolled Subjects (<?php echo $subjectCount; ?>)</h2>
                    <div class="u-display-flex-f7db u-gap-8px-304c">
                        <a href="?page=student-subjects&id=<?php echo (int) $student['student_id']; ?>"
                           class="btn btn-info btn-sm">View All Subjects →</a>
                    </div>
                </div>

                <?php if ($subjectCount > 0): ?>
                    <div class="table-responsive">
                        <table class="table subjects-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Subject Code</th>
                                    <th>Subject Name</th>
                                    <th>Units</th>
                                    <th>Type</th>
                                    <th>Schedule</th>
                                    <th>Faculty</th>
                                    <th>Room</th>
                                    <th>Status</th>
                                    <th>Grade</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $counter    = 1;
                                $totalUnits = 0;
                                foreach ($subjects as $subject):
                                    $totalUnits += (int) ($subject['units'] ?? 0);

                                    // Schedule display
                                    $scheduleDisplay = 'No schedule';
                                    if (!empty($subject['day_of_week']) && !empty($subject['start_time'])) {
                                        $scheduleDisplay = $subject['day_of_week'];
                                        if (!empty($subject['start_time'])) {
                                            $scheduleDisplay .= ' ' . date('h:i A', strtotime($subject['start_time']));
                                            if (!empty($subject['end_time'])) {
                                                $scheduleDisplay .= ' - ' . date('h:i A', strtotime($subject['end_time']));
                                            }
                                        }
                                    }

                                    // Faculty name
                                    $facultyDisplay = trim(
                                        ($subject['faculty_first'] ?? '') . ' ' . ($subject['faculty_last'] ?? '')
                                    );
                                    if ($facultyDisplay === '') $facultyDisplay = 'N/A';

                                    // Room
                                    $roomDisplay = $subject['room_name'] ?? '';
                                    if ($roomDisplay !== '' && !empty($subject['room_code'])) {
                                        $roomDisplay .= ' (' . $subject['room_code'] . ')';
                                    }
                                    if ($roomDisplay === '') $roomDisplay = 'N/A';

                                    $enrollmentStatus = $subject['enrollment_status'] ?? 'enrolled';
                                    $gradeDisplay     = $subject['grade'] ?? null;

                                    // subject_type no longer comes from the API. Fallback to 'Lecture'.
                                    $subjectType = $subject['subject_type'] ?? 'Lecture';
                                    $typeClass   = 'subject-' . strtolower($subjectType);
                                ?>
                                <tr>
                                    <td><?php echo $counter++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($subject['subject_code'] ?? 'N/A'); ?></strong></td>
                                    <td><?php echo htmlspecialchars($subject['subject_name'] ?? 'N/A'); ?></td>
                                    <td class="text-center"><?php echo (int) ($subject['units'] ?? 0); ?></td>
                                    <td>
                                        <span class="subject-type-badge <?php echo htmlspecialchars($typeClass); ?>">
                                            <?php echo htmlspecialchars($subjectType); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($subject['schedule_id'])): ?>
                                            <span class="schedule-badge has-schedule">📅 <?php echo htmlspecialchars($scheduleDisplay); ?></span>
                                        <?php else: ?>
                                            <span class="schedule-badge no-schedule">⏳ <?php echo htmlspecialchars($scheduleDisplay); ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($facultyDisplay !== 'N/A'): ?>
                                            <span class="faculty-name">👨‍🏫 <?php echo htmlspecialchars($facultyDisplay); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($roomDisplay !== 'N/A'): ?>
                                            <span class="room-name">🏫 <?php echo htmlspecialchars($roomDisplay); ?></span>
                                        <?php else: ?>
                                            <span class="text-muted">N/A</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="status-badge status-<?php echo htmlspecialchars($enrollmentStatus); ?>">
                                            <?php echo ucfirst(htmlspecialchars($enrollmentStatus)); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($gradeDisplay !== null): ?>
                                            <span class="grade-badge grade-<?php echo $gradeDisplay >= 75 ? 'passed' : 'failed'; ?>">
                                                <?php echo number_format((float) $gradeDisplay, 2); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="grade-badge grade-pending">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan="3"><strong>Total Units</strong></td>
                                    <td class="text-center"><strong><?php echo $totalUnits; ?></strong></td>
                                    <td colspan="6"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Subject Summary Cards -->
                    <div class="subject-summary-cards">
                        <div class="summary-card">
                            <div class="summary-number"><?php echo $subjectCount; ?></div>
                            <div class="summary-label">Total Subjects</div>
                        </div>
                        <div class="summary-card">
                            <div class="summary-number"><?php echo $totalUnits; ?></div>
                            <div class="summary-label">Total Units</div>
                        </div>
                        <div class="summary-card">
                            <?php
                                $completed = 0;
                                foreach ($subjects as $s) {
                                    if (($s['enrollment_status'] ?? '') === 'completed') $completed++;
                                }
                            ?>
                            <div class="summary-number"><?php echo $completed; ?></div>
                            <div class="summary-label">✅ Completed</div>
                        </div>
                        <div class="summary-card">
                            <?php
                                $withSchedule = 0;
                                foreach ($subjects as $s) {
                                    if (!empty($s['schedule_id'])) $withSchedule++;
                                }
                            ?>
                            <div class="summary-number"><?php echo $withSchedule; ?></div>
                            <div class="summary-label">📅 With Schedule</div>
                        </div>
                        <div class="summary-card">
                            <?php
                                $inProgress = 0;
                                foreach ($subjects as $s) {
                                    if (($s['enrollment_status'] ?? '') === 'enrolled') $inProgress++;
                                }
                            ?>
                            <div class="summary-number"><?php echo $inProgress; ?></div>
                            <div class="summary-label">⏳ In Progress</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info u-margin-top-10px-5371">
                        📚 No subjects enrolled for this student.
                        <a href="?page=enrollments" class="btn btn-sm btn-success u-margin-left-10px-6106">Enroll Now</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- REQUIREMENTS SECTION -->
            <div class="requirements-section">
                <div class="requirements-header">
                    <h2>📋 Requirements (<?php echo htmlspecialchars($admissionLabel); ?>)</h2>
                    <span class="progress-text">
                        <?php echo (int) ($completionStatus['submitted'] ?? 0); ?>/<?php echo (int) ($completionStatus['total'] ?? 0); ?>
                        submitted (<?php echo (int) ($completionStatus['percentage'] ?? 0); ?>%)
                    </span>
                </div>

                <div class="progress-bar">
                    <?php $pct = (int) ($completionStatus['percentage'] ?? 0); ?>
                    <div class="progress-fill u-dynamic-width u-dynamic-background" data-css-width="<?php echo $pct; ?>%" data-css-background="<?php echo $pct >= 80 ? '#2a5c9e' : ($pct >= 50 ? '#4a90d9' : '#0f2a4e'); ?>">
                    </div>
                </div>

                <div class="requirements-grid">
                    <?php if (count($requirements) > 0): ?>
                        <?php foreach ($requirements as $req):
                            $isSubmitted = !empty($req['is_submitted']);
                        ?>
                            <div class="requirement-item <?php echo $isSubmitted ? 'submitted' : 'pending'; ?>">
                                <span class="req-name">
                                    <?php echo htmlspecialchars($req['requirement_name'] ?? ''); ?>
                                    <?php if (!empty($req['is_mandatory'])): ?>
                                        <span class="u-color-0f2a4e-9277 u-font-weight-bold-0a15">*</span>
                                    <?php endif; ?>
                                </span>
                                <span class="req-status <?php echo $isSubmitted ? 'submitted' : 'pending'; ?>">
                                    <?php if ($isSubmitted): ?>
                                        ✅ Submitted
                                        <?php if (!empty($req['submitted_date'])): ?>
                                            <br><small class="u-color-5a7fa8-c092 u-font-weight-normal-9aa7">
                                                <?php echo htmlspecialchars(date('M d, Y', strtotime($req['submitted_date']))); ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        ⏳ Pending
                                        <?php if (!empty($req['notes'])): ?>
                                            <br><small class="u-color-5a7fa8-c092 u-font-weight-normal-9aa7">
                                                📝 <?php echo htmlspecialchars($req['notes']); ?>
                                            </small>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </span>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="alert alert-info u-grid-column-1-1-b452">
                            No requirements found for this student.
                            <a href="?page=requirements&view=manage" class="btn btn-sm btn-primary u-margin-left-10px-6106">Add Requirements</a>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="u-margin-top-20px-3ae1 u-text-align-right-f6e3 u-display-flex-f7db u-gap-10px-114b u-justify-content-flex-end-36b7 u-flex-wrap-wrap-9012">
                    <a href="?page=requirements&student_id=<?php echo (int) $student['student_id']; ?>&view=students" class="btn btn-primary">
                        📋 Manage Requirements
                    </a>
                    <?php if (($student['followup_status'] ?? '') !== 'done'): ?>
                        <a href="?page=requirements&student_id=<?php echo (int) $student['student_id']; ?>&view=followup" class="btn btn-warning">
                            📞 Schedule Follow-up
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ARCHIVE MODAL -->
<div id="archiveModal" class="modal u-display-none-cb45">
    <div class="modal-content u-max-width-500px-5ab1">
        <span class="close" data-action-click="action2">&times;</span>
        <h2>📦 Archive Student</h2>
        <div class="u-background-f0f7ff-085b u-padding-15px-3607 u-border-radius-4px-b6a1 u-margin-bottom-20px-3019 u-border-left-4px-solid-1a3c6e-5612">
            <p><strong>Student:</strong> <?php echo htmlspecialchars($fullName); ?></p>
            <p class="u-color-2a5c9e-2521 u-font-weight-bold-0a15">⚠️ Ang student ay hindi made-delete — ma-archive lang. Pwede pang i-restore.</p>
        </div>
        <form method="POST">
            <input type="hidden" name="action" value="archive">
            <input type="hidden" name="student_id" value="<?php echo (int) $student['student_id']; ?>">
            <?php if ($enrollmentDetails): ?>
                <input type="hidden" name="school_year" value="<?php echo htmlspecialchars($enrollmentDetails['school_year'] ?? ''); ?>">
            <?php endif; ?>
            <div class="form-group u-margin-bottom-15px-9ab9">
                <label class="u-display-block-fa9a u-margin-bottom-5px-980c u-font-weight-500-1e29 u-color-1a3c6e-5e20">Reason for Archiving *</label>
                <select name="archive_reason" required class="u-width-100-6d56 u-padding-8px-12px-6755 u-border-2px-solid-b8d4e8-93cb u-border-radius-4px-b6a1 u-color-1a3c6e-5e20 u-background-white-29c1">
                    <option value="Dropped">Dropped</option>
                    <option value="Transferred">Transferred</option>
                    <option value="LOA">Leave of Absence (LOA)</option>
                    <option value="Graduated">Graduated</option>
                    <option value="Other">Other</option>
                </select>
            </div>
            <div class="u-display-flex-f7db u-justify-content-flex-end-36b7 u-gap-10px-114b u-margin-top-20px-3ae1 u-padding-top-15px-2ca4 u-border-top-1px-solid-e8f4fd-ee0f">
                <button type="button" class="btn btn-secondary" data-action-click="action3">Cancel</button>
                <button type="submit" class="btn btn-warning">📦 Archive Student</button>
            </div>
        </form>
    </div>
</div>
