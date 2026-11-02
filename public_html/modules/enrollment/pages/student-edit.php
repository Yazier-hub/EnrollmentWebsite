<?php
// View data supplied by StudentEditPage through EnrollmentPage.
/** @var string $fullName */
/** @var array<string, mixed> $student */
/** @var string $message */
/** @var string|null $error */
/** @var array<string, mixed>|false|null $enrollmentDetails */
/** @var int $studentId */
/** @var string $currentSex */
/** @var string $currentCivil */
/** @var string $getAdmissionType */
/** @var string $getWorkingStudent */
/** @var string $currentHowHear */
/** @var array<int, array<string, mixed>> $courses */
/** @var array<int, array<string, mixed>> $activeSections */
/** @var array<int, array<string, mixed>> $subjects */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>✏️ Edit Student</h1>
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

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="edit-form-container">

                <!-- HEADER -->
                <div class="student-info-header">
                    <div class="student-avatar">
                        <?php echo strtoupper(
                            substr($student['first_name'] ?? '?', 0, 1)
                            . substr($student['surname'] ?? '?', 0, 1)
                        ); ?>
                    </div>
                    <div class="student-header-info">
                        <div class="name"><?php echo htmlspecialchars($fullName); ?></div>
                        <div class="number">🎓 Student #: <?php echo htmlspecialchars($student['student_number'] ?? ''); ?></div>
                        <div class="u-margin-top-5px-351b">
                            <?php $eStatus = $student['enrollment_status'] ?? 'unknown'; ?>
                            <span class="badge-lg badge-<?php echo htmlspecialchars($eStatus); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($eStatus))); ?>
                            </span>
                            <?php if ($enrollmentDetails): ?>
                                <span class="badge-lg u-background-d4edda-c317 u-color-155724-8d78">✅ Enrolled</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="u-margin-left-auto-8345">
                        <a href="?page=student-details&id=<?php echo $studentId; ?>" class="btn btn-info">← Back</a>
                        <a href="?page=students" class="btn btn-secondary">👥 All Students</a>
                    </div>
                </div>

                <!-- ============================================================
                     ONE FORM — personal + academic
                     ============================================================ -->
                <form method="POST" class="edit-form" id="editForm">
                    <input type="hidden" name="action" value="update_all">
                    <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">

                    <!-- PERSONAL INFORMATION -->
                    <div class="form-section">
                        <h3>👤 Personal Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">First Name <span class="required">*</span></label>
                                <input type="text" name="first_name" id="first_name" class="form-control" required
                                       value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="middle_name">Middle Name</label>
                                <input type="text" name="middle_name" id="middle_name" class="form-control"
                                       value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="surname">Surname <span class="required">*</span></label>
                                <input type="text" name="surname" id="surname" class="form-control" required
                                       value="<?php echo htmlspecialchars($student['surname'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="suffix">Suffix</label>
                                <input type="text" name="suffix" id="suffix" class="form-control"
                                       value="<?php echo htmlspecialchars($student['suffix'] ?? ''); ?>"
                                       placeholder="e.g., Jr., Sr., III">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="sex">Sex</label>
                                <select name="sex" id="sex" class="form-control">
                                    <option value="">Select Sex</option>
                                    <option value="Male"   <?php echo $currentSex === 'Male'   ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $currentSex === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other"  <?php echo $currentSex === 'Other'  ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="civil_status">Civil Status</label>
                                <select name="civil_status" id="civil_status" class="form-control">
                                    <option value="">Select Civil Status</option>
                                    <option value="Single"   <?php echo $currentCivil === 'Single'   ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married"  <?php echo $currentCivil === 'Married'  ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo $currentCivil === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed"  <?php echo $currentCivil === 'Widowed'  ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="date_of_birth">Date of Birth</label>
                                <input type="date" name="date_of_birth" id="date_of_birth" class="form-control"
                                       value="<?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="age">Age</label>
                                <input type="number" name="age" id="age" class="form-control"
                                       value="<?php echo htmlspecialchars($student['age'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="place_of_birth">Place of Birth</label>
                                <input type="text" name="place_of_birth" id="place_of_birth" class="form-control"
                                       value="<?php echo htmlspecialchars($student['place_of_birth'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="religion">Religion</label>
                                <input type="text" name="religion" id="religion" class="form-control"
                                       value="<?php echo htmlspecialchars($student['religion'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="admission_type">Admission Type</label>
                                <select name="admission_type" id="admission_type" class="form-control">
                                    <option value="freshmen"    <?php echo $getAdmissionType === 'freshmen'    ? 'selected' : ''; ?>>Freshmen</option>
                                    <option value="transferee"  <?php echo $getAdmissionType === 'transferee'  ? 'selected' : ''; ?>>Transferee</option>
                                    <option value="returnee"    <?php echo $getAdmissionType === 'returnee'    ? 'selected' : ''; ?>>Returnee</option>
                                    <option value="senior_high" <?php echo $getAdmissionType === 'senior_high' ? 'selected' : ''; ?>>Senior High</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="working_student">Working Student</label>
                                <select name="working_student" id="working_student" class="form-control">
                                    <option value="No"  <?php echo $getWorkingStudent === 'No'  ? 'selected' : ''; ?>>No</option>
                                    <option value="Yes" <?php echo $getWorkingStudent === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- CONTACT & ADDRESS -->
                    <div class="form-section">
                        <h3>📞 Contact & Address</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">Email <span class="required">*</span></label>
                                <input type="email" name="email" id="email" class="form-control" required
                                       value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="contact_number">Contact Number</label>
                                <input type="text" name="contact_number" id="contact_number" class="form-control"
                                       placeholder="09xxxxxxxxx"
                                       value="<?php echo htmlspecialchars($student['contact_number'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="facebook">Facebook</label>
                                <input type="text" name="facebook" id="facebook" class="form-control"
                                       value="<?php echo htmlspecialchars($student['facebook'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="messenger">Messenger</label>
                                <input type="text" name="messenger" id="messenger" class="form-control"
                                       value="<?php echo htmlspecialchars($student['messenger'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="address_barangay">Barangay</label>
                                <input type="text" name="address_barangay" id="address_barangay" class="form-control"
                                       value="<?php echo htmlspecialchars($student['address_barangay'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address_city">City/Municipality</label>
                                <input type="text" name="address_city" id="address_city" class="form-control"
                                       value="<?php echo htmlspecialchars($student['address_city'] ?? ''); ?>">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="address_province">Province</label>
                                <input type="text" name="address_province" id="address_province" class="form-control"
                                       value="<?php echo htmlspecialchars($student['address_province'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="address_complete">Complete Address</label>
                                <textarea name="address_complete" id="address_complete" class="form-control" rows="2"><?php echo htmlspecialchars($student['address_complete'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- PARENT / GUARDIAN -->
                    <div class="form-section">
                        <h3>👨‍👩‍👧 Parent / Guardian</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="parent_full_name">Parent Full Name</label>
                                <input type="text" name="parent_full_name" id="parent_full_name" class="form-control"
                                       value="<?php echo htmlspecialchars($student['parent_full_name'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="parent_contact">Parent Contact</label>
                                <input type="text" name="parent_contact" id="parent_contact" class="form-control"
                                       value="<?php echo htmlspecialchars($student['parent_contact'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="parent_address">Parent Address</label>
                            <textarea name="parent_address" id="parent_address" class="form-control" rows="2"><?php echo htmlspecialchars($student['parent_address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- EDUCATIONAL BACKGROUND -->
                    <div class="form-section">
                        <h3>🎓 Educational Background</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="school_last_attended">School Last Attended</label>
                                <input type="text" name="school_last_attended" id="school_last_attended" class="form-control"
                                       value="<?php echo htmlspecialchars($student['school_last_attended'] ?? ''); ?>">
                            </div>
                            <div class="form-group">
                                <label for="year_graduated">Year Graduated</label>
                                <input type="text" name="year_graduated" id="year_graduated" class="form-control"
                                       placeholder="e.g., 2024-2025"
                                       value="<?php echo htmlspecialchars($student['year_graduated'] ?? ''); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="how_hear">How did you hear about us?</label>
                            <select name="how_hear" id="how_hear" class="form-control">
                                <option value="">Select option</option>
                                <option value="social_media"  <?php echo $currentHowHear === 'social_media'  ? 'selected' : ''; ?>>Social Media</option>
                                <option value="friend"        <?php echo $currentHowHear === 'friend'        ? 'selected' : ''; ?>>Friend/Relative</option>
                                <option value="school"        <?php echo $currentHowHear === 'school'        ? 'selected' : ''; ?>>School</option>
                                <option value="advertisement" <?php echo $currentHowHear === 'advertisement' ? 'selected' : ''; ?>>Advertisement</option>
                                <option value="other"         <?php echo $currentHowHear === 'other'         ? 'selected' : ''; ?>>Other</option>
                                <option value="N/A"           <?php echo $currentHowHear === 'N/A'           ? 'selected' : ''; ?>>N/A</option>
                            </select>
                        </div>
                    </div>

                    <!-- ACADEMIC INFORMATION -->
                    <div class="form-section">
                        <h3>🎓 Academic Information</h3>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Student Number</label>
                                <input type="text" class="form-control"
                                       value="<?php echo htmlspecialchars($student['student_number'] ?? ''); ?>" readonly>
                            </div>
                            <div class="form-group">
                                <label>Full Name</label>
                                <input type="text" class="form-control"
                                       value="<?php echo htmlspecialchars($fullName); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="course_id">Course <span class="required">*</span></label>
                                <select name="course_id" id="course_id" class="form-control" required>
                                    <option value="">Select Course</option>
                                    <?php $currentCourseId = (int) ($student['course_id'] ?? 0); ?>
                                    <?php foreach ($courses as $c): ?>
                                        <option value="<?php echo (int) $c['id']; ?>"
                                            <?php echo $currentCourseId === (int) $c['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="section_id">Section</label>
                                <select name="section_id" id="section_id" class="form-control">
                                    <option value="">No Section</option>
                                    <?php $currentSectionId = (int) ($student['section_id'] ?? 0); ?>
                                    <?php foreach ($activeSections as $sec): ?>
                                        <option value="<?php echo (int) $sec['id']; ?>"
                                            <?php echo $currentSectionId === (int) $sec['id'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars(($sec['section_code'] ?? '') . ' - ' . ($sec['course_code'] ?? '')); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="year_level">Year Level <span class="required">*</span></label>
                                <select name="year_level" id="year_level" class="form-control" required>
                                    <?php $currentYear = (int) ($student['year_level'] ?? 1); ?>
                                    <?php foreach ([1 => '1st Year', 2 => '2nd Year', 3 => '3rd Year', 4 => '4th Year'] as $y => $lbl): ?>
                                        <option value="<?php echo $y; ?>" <?php echo $currentYear === $y ? 'selected' : ''; ?>>
                                            <?php echo $lbl; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="enrollment_status">Enrollment Status <span class="required">*</span></label>
                                <select name="enrollment_status" id="enrollment_status" class="form-control" required>
                                    <?php $currentEnrStatus = $student['enrollment_status'] ?? 'enrolled'; ?>
                                    <?php foreach ([
                                        'enrolled'  => 'Enrolled',
                                        'on_leave'  => 'On Leave',
                                        'graduated' => 'Graduated',
                                        'dropped'   => 'Dropped'
                                    ] as $val => $lbl): ?>
                                        <option value="<?php echo $val; ?>"
                                            <?php echo $currentEnrStatus === $val ? 'selected' : ''; ?>>
                                            <?php echo $lbl; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="info-box">
                            <h4>📋 Additional Information</h4>
                            <div class="form-row">
                                <div class="form-group u-margin-bottom-0-e7ec">
                                    <label>Enrolled At</label>
                                    <input type="text" class="form-control"
                                           value="<?php echo !empty($student['enrolled_at'])
                                                ? htmlspecialchars(date('M d, Y h:i A', strtotime($student['enrolled_at'])))
                                                : 'N/A'; ?>"
                                           readonly>
                                </div>
                                <div class="form-group u-margin-bottom-0-e7ec">
                                    <label>Follow-up Status</label>
                                    <input type="text" class="form-control"
                                           value="<?php echo htmlspecialchars(ucfirst($student['followup_status'] ?? 'N/A')); ?>"
                                           readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- FORM ACTIONS -->
                    <div class="form-actions">
                        <a href="?page=student-details&id=<?php echo $studentId; ?>" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-success">💾 Update Student</button>
                    </div>
                </form>

                <!-- SCHEDULE SECTION -->
                <div class="schedule-section">
                    <h3>📅 Student Schedule (<?php echo count($subjects); ?> subjects)</h3>

                    <?php if (count($subjects) > 0): ?>
                        <div class="table-responsive">
                            <table class="table schedule-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Subject</th>
                                        <th>Schedule</th>
                                        <th>Faculty</th>
                                        <th>Room</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $counter = 1; ?>
                                    <?php foreach ($subjects as $subject):
                                        $scheduleDisplay = 'No schedule';
                                        if (!empty($subject['day_of_week']) && !empty($subject['start_time'])) {
                                            $scheduleDisplay = $subject['day_of_week']
                                                . ' ' . date('h:i A', strtotime($subject['start_time']));
                                            if (!empty($subject['end_time'])) {
                                                $scheduleDisplay .= ' - ' . date('h:i A', strtotime($subject['end_time']));
                                            }
                                        }

                                        $facultyDisplay = trim(
                                            ($subject['faculty_first'] ?? '') . ' ' . ($subject['faculty_last'] ?? '')
                                        );
                                        if ($facultyDisplay === '') $facultyDisplay = 'N/A';

                                        $roomDisplay = $subject['room_name'] ?? '';
                                        if ($roomDisplay !== '' && !empty($subject['room_code'])) {
                                            $roomDisplay .= ' (' . $subject['room_code'] . ')';
                                        }
                                        if ($roomDisplay === '') $roomDisplay = 'N/A';

                                        $enrollmentStatus = $subject['enrollment_status'] ?? 'enrolled';
                                    ?>
                                    <tr>
                                        <td><?php echo $counter++; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($subject['subject_code'] ?? 'N/A'); ?></strong>
                                            <br><small><?php echo htmlspecialchars($subject['subject_name'] ?? 'N/A'); ?></small>
                                        </td>
                                        <td>
                                            <?php if (!empty($subject['schedule_id'])): ?>
                                                <span class="schedule-badge has-schedule">
                                                    📅 <?php echo htmlspecialchars($scheduleDisplay); ?>
                                                </span>
                                            <?php else: ?>
                                                <span class="schedule-badge no-schedule">
                                                    ⏳ <?php echo htmlspecialchars($scheduleDisplay); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($facultyDisplay); ?></td>
                                        <td><?php echo htmlspecialchars($roomDisplay); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo htmlspecialchars($enrollmentStatus); ?>">
                                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($enrollmentStatus))); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            No subjects found for this student's current semester.
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Danger Zone -->
                <div class="danger-zone">
                    <h4>⚠️ Danger Zone</h4>
                    <p>This action cannot be undone. Please be certain before proceeding.</p>
                    <?php if ($enrollmentDetails): ?>
                        <div class="alert alert-warning">
                            <strong>⚠️ This student has an active enrollment.</strong>
                            Please <a href="?page=student-details&id=<?php echo $studentId; ?>">drop the student</a>
                            first before deleting.
                        </div>
                    <?php else: ?>
                        <form method="POST" data-action-submit="action1" class="u-display-inline-block-6a19">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="student_id" value="<?php echo $studentId; ?>">
                            <button type="submit" class="btn btn-danger">🗑️ Delete Student</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</main>
