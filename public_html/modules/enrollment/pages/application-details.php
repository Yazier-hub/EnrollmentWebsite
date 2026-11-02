<?php
// View data supplied by ApplicationDetailsPage through EnrollmentPage.
/** @var string $fullName */
/** @var array<string, mixed> $app */
/** @var string $message */
/** @var string|null $error */
/** @var array<int, array<string, mixed>> $availableSections */
/** @var array<int, array<string, mixed>> $requirements */
/** @var array<int, array<string, mixed>> $courses */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>📋 Application Details</h1>
            <p class="dashboard-subtitle">
                <?php echo htmlspecialchars($fullName); ?>
                • Application #<?php echo (int) $app['applicant_id']; ?>
            </p>
        </div>

        <div class="module-content">

            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <!-- APPLICATION PROFILE -->
            <div class="application-profile">
                <div class="profile-header">
                    <div class="profile-info">
                        <div class="applicant-name"><?php echo htmlspecialchars($fullName); ?></div>
                        <div class="applicant-id">📋 Application #: <?php echo (int) $app['applicant_id']; ?></div>
                        <div class="profile-badges">
                            <span class="badge-lg badge-<?php echo htmlspecialchars($app['status'] ?? 'pending'); ?>">
                                <?php echo ucfirst(htmlspecialchars($app['status'] ?? 'pending')); ?>
                            </span>
                            <?php if (!empty($app['admission_type'])): ?>
                                <span class="badge-lg u-background-e9ecef-9177 u-color-333-7574">
                                    <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($app['admission_type']))); ?>
                                </span>
                            <?php endif; ?>
                            <?php if (!empty($app['student_id'])): ?>
                                <span class="badge-lg badge-converted">✅ Converted to Student</span>
                            <?php endif; ?>
                            <span class="badge-lg u-background-e9ecef-9177 u-color-333-7574">
                                <?php echo htmlspecialchars($app['course_code'] ?? 'No Course Assigned'); ?>
                            </span>
                        </div>
                    </div>
                    <div class="profile-actions">
                        <a href="?page=applications" class="btn btn-secondary">← Back</a>
                        <?php if (($app['status'] ?? '') === 'pending'): ?>
                            <button data-action-click="action1" class="btn btn-warning" id="editToggleBtn">✏️ Edit</button>
                            <button data-action-click="action2" class="btn btn-danger">❌ Reject</button>
                            <button data-action-click="action3" class="btn btn-success">🎓 Convert</button>
                        <?php endif; ?>
                        <?php if (!empty($app['student_id'])): ?>
                            <a href="?page=student-details&id=<?php echo (int) $app['student_id']; ?>"
                               class="btn btn-primary">👤 View Student</a>
                        <?php endif; ?>
                        <?php if (($app['status'] ?? '') !== 'converted'): ?>
                            <form method="POST" data-action-submit="action4" class="u-display-inline-block-6a19">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="applicant_id" value="<?php echo (int) $app['applicant_id']; ?>">
                                <button type="submit" class="btn btn-danger">🗑️ Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- DETAILS GRID -->
                <div class="details-grid" id="detailsView">
                    <!-- Personal Information -->
                    <div class="detail-section">
                        <h3>📋 Personal Information</h3>
                        <div class="detail-row"><span class="detail-label">First Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['first_name'] ?? ''); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Middle Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['middle_name'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Surname</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['surname'] ?? ''); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Suffix</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['suffix'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Admission Type</span>
                            <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($app['admission_type'] ?? 'N/A'))); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Working Student</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['working_student'] ?? 'No'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Sex</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['sex'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Date of Birth</span>
                            <span class="detail-value"><?php echo !empty($app['date_of_birth']) ? date('M d, Y', strtotime($app['date_of_birth'])) : 'N/A'; ?></span></div>
                        <div class="detail-row"><span class="detail-label">Age</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['age'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Place of Birth</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['place_of_birth'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Civil Status</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['civil_status'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Religion</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['religion'] ?? 'N/A'); ?></span></div>
                    </div>

                    <!-- Contact & Address -->
                    <div class="detail-section">
                        <h3>📞 Contact Information</h3>
                        <div class="detail-row"><span class="detail-label">Email</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['email'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Contact Number</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['contact_number'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Facebook</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['facebook'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Messenger</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['messenger'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-dee2e6-6cf2">
                            <span class="detail-label">Barangay</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['address_barangay'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">City/Municipality</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['address_city'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Province</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['address_province'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Complete Address</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['address_complete'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-dee2e6-6cf2">
                            <span class="detail-label"><strong>Parent/Guardian</strong></span>
                            <span class="detail-value"></span></div>
                        <div class="detail-row"><span class="detail-label">Parent Name</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['parent_full_name'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Parent Contact</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['parent_contact'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Parent Address</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['parent_address'] ?? 'N/A'); ?></span></div>
                    </div>

                    <!-- Academic Information -->
                    <div class="detail-section">
                        <h3>🎓 Academic Information</h3>
                        <div class="detail-row"><span class="detail-label">Course</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['course_code'] ?? 'No Course Assigned'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">School Last Attended</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['school_last_attended'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Year Graduated</span>
                            <span class="detail-value"><?php echo htmlspecialchars($app['year_graduated'] ?? 'N/A'); ?></span></div>
                        <div class="detail-row"><span class="detail-label">How Heard</span>
                            <span class="detail-value"><?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($app['how_hear'] ?? 'N/A'))); ?></span></div>
                        <div class="detail-row"><span class="detail-label">Submitted At</span>
                            <span class="detail-value"><?php echo !empty($app['submitted_at']) ? date('M d, Y h:i A', strtotime($app['submitted_at'])) : 'N/A'; ?></span></div>
                        <?php if (!empty($app['notes'])): ?>
                            <div class="detail-row"><span class="detail-label">Notes</span>
                                <span class="detail-value u-text-align-left-200a u-max-width-200px-3831"><?php echo htmlspecialchars($app['notes']); ?></span></div>
                        <?php endif; ?>
                    </div>

                    <!-- Status Information -->
                    <div class="detail-section">
                        <h3>📊 Status Information</h3>
                        <div class="detail-row"><span class="detail-label">Application Status</span>
                            <span class="detail-value">
                                <span class="badge-lg badge-<?php echo htmlspecialchars($app['status'] ?? 'pending'); ?> u-font-size-12px-6b9c u-padding-4px-12px-6ab3">
                                    <?php echo ucfirst(htmlspecialchars($app['status'] ?? 'pending')); ?>
                                </span>
                            </span>
                        </div>
                        <?php if (!empty($app['student_id'])): ?>
                            <div class="detail-row"><span class="detail-label">Student ID</span>
                                <span class="detail-value"><?php echo htmlspecialchars($app['student_id']); ?></span></div>
                            <div class="detail-row"><span class="detail-label">Student Number</span>
                                <span class="detail-value"><?php echo htmlspecialchars($app['student_number'] ?? 'N/A'); ?></span></div>
                            <div class="detail-row"><span class="detail-label">Student Status</span>
                                <span class="detail-value"><?php echo htmlspecialchars($app['student_status'] ?? 'N/A'); ?></span></div>
                        <?php else: ?>
                            <div class="detail-row">
                                <span class="detail-label u-color-856404-d0e1">Not yet converted</span>
                                <span class="detail-value u-color-856404-d0e1">⏳ Pending</span>
                            </div>
                            <?php if (!empty($app['course_id'])): ?>
                                <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-dee2e6-6cf2">
                                    <span class="detail-label u-color-28a745-41c2">Ready for enrollment</span>
                                    <span class="detail-value u-color-28a745-41c2">✅ Click "Convert to Student"</span>
                                </div>
                                <?php if (!empty($availableSections)): ?>
                                    <div class="detail-row">
                                        <span class="detail-label u-color-17a2b8-ba60">Available sections</span>
                                        <span class="detail-value u-color-17a2b8-ba60"><?php echo count($availableSections); ?> found</span>
                                    </div>
                                <?php else: ?>
                                    <div class="detail-row">
                                        <span class="detail-label u-color-dc3545-0b27">No available sections</span>
                                        <span class="detail-value u-color-dc3545-0b27">⚠️ Please add a section</span>
                                    </div>
                                <?php endif; ?>
                            <?php else: ?>
                                <div class="detail-row u-margin-top-10px-5371 u-padding-top-10px-8ae9 u-border-top-2px-solid-dee2e6-6cf2">
                                    <span class="detail-label u-color-dc3545-0b27">Missing Course</span>
                                    <span class="detail-value u-color-dc3545-0b27">⚠️ Edit to assign a course</span>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- CONVERT FORM -->
            <?php if (($app['status'] ?? '') === 'pending' && empty($app['student_id']) && !empty($app['course_id'])): ?>
            <div class="convert-section u-display-none-cb45" id="convertForm">
                <h3>🎓 Convert to Student & Enroll</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="convert">
                    <input type="hidden" name="applicant_id" value="<?php echo (int) $app['applicant_id']; ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label>Select Section <span class="required">*</span></label>
                            <select name="section_id" class="form-control" required>
                                <option value="">-- Select Section --</option>
                                <?php foreach ($availableSections as $sec): ?>
                                    <option value="<?php echo (int) $sec['section_id']; ?>">
                                        <?php echo htmlspecialchars($sec['section_code'] ?? ''); ?>
                                        (<?php echo (int) ($sec['current_students'] ?? 0); ?>/<?php echo (int) ($sec['max_students'] ?? 40); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if (empty($availableSections)): ?>
                                <small class="u-color-dc3545-0b27">⚠️ No available sections for this course.</small>
                            <?php endif; ?>
                        </div>
                        <div class="form-group">
                            <label>School Year <span class="required">*</span></label>
                            <input type="text" name="school_year" class="form-control"
                                   value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>"
                                   pattern="\d{4}-\d{4}" placeholder="e.g., 2026-2027" required>
                        </div>
                    </div>

                    <?php if (!empty($requirements)): ?>
                    <div class="form-group u-margin-top-15px-886e">
                        <label>Requirements Checklist</label>
                        <div class="u-background-f8f9fa-7c6e u-padding-15px-3607 u-border-radius-4px-b6a1 u-border-1px-solid-e9ecef-6d6f">
                            <?php foreach ($requirements as $req): ?>
                                <div class="u-margin-5px-0-8ac3">
                                    <label class="u-font-weight-normal-9aa7 u-cursor-pointer-b202">
                                        <input type="checkbox"
                                               name="requirements[<?php echo (int) $req['requirement_id']; ?>]"
                                               value="1">
                                        <?php echo htmlspecialchars($req['requirement_name']); ?>
                                        <?php if (!empty($req['is_mandatory'])): ?>
                                            <span class="u-color-dc3545-0b27 u-font-size-12px-6b9c">*Required</span>
                                        <?php endif; ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                            <small class="u-color-6c757d-823f">Check off any requirements already submitted.</small>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions u-margin-top-20px-3ae1">
                        <button type="button" class="btn btn-secondary" data-action-click="action5">Cancel</button>
                        <button type="submit" class="btn btn-success" <?php echo empty($availableSections) ? 'disabled' : ''; ?>>
                            🎓 Convert & Enroll
                        </button>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            <!-- REJECT FORM -->
            <div id="rejectForm" class="u-display-none-cb45 u-background-f8f9fa-7c6e u-border-1px-solid-e9ecef-6d6f u-border-radius-8px-a503 u-padding-20px-7fa5 u-margin-top-20px-3ae1">
                <h3>❌ Reject Application</h3>
                <form method="POST">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="applicant_id" value="<?php echo (int) $app['applicant_id']; ?>">
                    <div class="form-group">
                        <label>Reason for Rejection</label>
                        <textarea name="notes" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                    </div>
                    <div class="form-actions u-margin-top-15px-886e">
                        <button type="button" class="btn btn-secondary" data-action-click="action6">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Reject</button>
                    </div>
                </form>
            </div>

            <!-- EDIT FORM -->
<div class="edit-form-container u-display-none-cb45" id="editForm">
    <h2>✏️ Edit Application</h2>
    <form method="POST">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="applicant_id" value="<?php echo (int) $app['applicant_id']; ?>">

        <!-- PERSONAL INFORMATION -->
        <h3 class="u-color-var-navy-a138 u-margin-15px-0-10px-d114 u-font-size-15px-3dd9">📋 Personal Information</h3>
        <div class="form-row">
            <div class="form-group">
                <label>First Name <span class="required">*</span></label>
                <input type="text" name="first_name" class="form-control"
                       value="<?php echo htmlspecialchars($app['first_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Middle Name</label>
                <input type="text" name="middle_name" class="form-control"
                       value="<?php echo htmlspecialchars($app['middle_name'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Surname <span class="required">*</span></label>
                <input type="text" name="surname" class="form-control"
                       value="<?php echo htmlspecialchars($app['surname'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label>Suffix</label>
                <input type="text" name="suffix" class="form-control"
                       value="<?php echo htmlspecialchars($app['suffix'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Admission Type</label>
                <select name="admission_type" class="form-control">
                    <?php
                    $admissionTypes = ['freshmen', 'transferee', 'returnee', 'shiftee', 'second_courser'];
                    foreach ($admissionTypes as $type):
                    ?>
                        <option value="<?php echo $type; ?>"
                            <?php echo (($app['admission_type'] ?? '') === $type) ? 'selected' : ''; ?>>
                            <?php echo ucfirst(str_replace('_', ' ', $type)); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Working Student</label>
                <select name="working_student" class="form-control">
                    <option value="No"  <?php echo (($app['working_student'] ?? 'No') === 'No')  ? 'selected' : ''; ?>>No</option>
                    <option value="Yes" <?php echo (($app['working_student'] ?? '')   === 'Yes') ? 'selected' : ''; ?>>Yes</option>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Sex</label>
                <select name="sex" class="form-control">
                    <option value="">-- Select --</option>
                    <option value="Male"   <?php echo (($app['sex'] ?? '') === 'Male')   ? 'selected' : ''; ?>>Male</option>
                    <option value="Female" <?php echo (($app['sex'] ?? '') === 'Female') ? 'selected' : ''; ?>>Female</option>
                </select>
            </div>
            <div class="form-group">
                <label>Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control"
                       value="<?php echo htmlspecialchars($app['date_of_birth'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Age</label>
                <input type="number" name="age" class="form-control" min="0" max="120"
                       value="<?php echo htmlspecialchars($app['age'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Place of Birth</label>
                <input type="text" name="place_of_birth" class="form-control"
                       value="<?php echo htmlspecialchars($app['place_of_birth'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Civil Status</label>
                <select name="civil_status" class="form-control">
                    <?php
                    $civilStatuses = ['Single', 'Married', 'Widowed', 'Separated', 'Annulled'];
                    foreach ($civilStatuses as $cs):
                    ?>
                        <option value="<?php echo $cs; ?>"
                            <?php echo (($app['civil_status'] ?? 'Single') === $cs) ? 'selected' : ''; ?>>
                            <?php echo $cs; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Religion</label>
                <input type="text" name="religion" class="form-control"
                       value="<?php echo htmlspecialchars($app['religion'] ?? ''); ?>">
            </div>
        </div>

        <!-- CONTACT INFORMATION -->
        <h3 class="u-color-var-navy-a138 u-margin-20px-0-10px-c24d u-font-size-15px-3dd9">📞 Contact Information</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control"
                       value="<?php echo htmlspecialchars($app['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Contact Number</label>
                <input type="text" name="contact_number" class="form-control"
                       value="<?php echo htmlspecialchars($app['contact_number'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Facebook</label>
                <input type="text" name="facebook" class="form-control"
                       value="<?php echo htmlspecialchars($app['facebook'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Messenger</label>
                <input type="text" name="messenger" class="form-control"
                       value="<?php echo htmlspecialchars($app['messenger'] ?? ''); ?>">
            </div>
        </div>

        <!-- ADDRESS -->
        <h3 class="u-color-var-navy-a138 u-margin-20px-0-10px-c24d u-font-size-15px-3dd9">🏠 Address</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Barangay</label>
                <input type="text" name="address_barangay" class="form-control"
                       value="<?php echo htmlspecialchars($app['address_barangay'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>City/Municipality</label>
                <input type="text" name="address_city" class="form-control"
                       value="<?php echo htmlspecialchars($app['address_city'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>Province</label>
                <input type="text" name="address_province" class="form-control"
                       value="<?php echo htmlspecialchars($app['address_province'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Complete Address</label>
                <input type="text" name="address_complete" class="form-control"
                       value="<?php echo htmlspecialchars($app['address_complete'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Address (legacy / free text)</label>
            <input type="text" name="address" class="form-control"
                   value="<?php echo htmlspecialchars($app['address'] ?? ''); ?>">
        </div>

        <!-- PARENT / GUARDIAN -->
        <h3 class="u-color-var-navy-a138 u-margin-20px-0-10px-c24d u-font-size-15px-3dd9">👨‍👩‍👧 Parent / Guardian</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Parent Full Name</label>
                <input type="text" name="parent_full_name" class="form-control"
                       value="<?php echo htmlspecialchars($app['parent_full_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Parent Contact</label>
                <input type="text" name="parent_contact" class="form-control"
                       value="<?php echo htmlspecialchars($app['parent_contact'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>Parent Address</label>
            <input type="text" name="parent_address" class="form-control"
                   value="<?php echo htmlspecialchars($app['parent_address'] ?? ''); ?>">
        </div>

        <!-- ACADEMIC INFORMATION -->
        <h3 class="u-color-var-navy-a138 u-margin-20px-0-10px-c24d u-font-size-15px-3dd9">🎓 Academic Information</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Course</label>
                <select name="course_id" class="form-control">
                    <option value="">-- Select Course --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?php echo (int) $c['id']; ?>"
                            <?php echo ((int) ($app['course_id'] ?? 0) === (int) $c['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($c['course_code'] ?? ''); ?>
                            — <?php echo htmlspecialchars($c['course_name'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Preferred Section</label>
                <select name="preferred_section_id" class="form-control">
                    <option value="">-- None --</option>
                    <?php foreach ($availableSections as $sec): ?>
                        <option value="<?php echo (int) $sec['section_id']; ?>"
                            <?php echo ((int) ($app['preferred_section_id'] ?? 0) === (int) $sec['section_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($sec['section_code'] ?? ''); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label>School Last Attended</label>
                <input type="text" name="school_last_attended" class="form-control"
                       value="<?php echo htmlspecialchars($app['school_last_attended'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Year Graduated</label>
                <input type="number" name="year_graduated" class="form-control"
                       min="1900" max="<?php echo date('Y') + 1; ?>"
                       value="<?php echo htmlspecialchars($app['year_graduated'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label>How did you hear about us?</label>
            <select name="how_hear" class="form-control">
                <option value="">-- Select --</option>
                <?php
                $howHearOptions = ['facebook', 'friend', 'family', 'school', 'walk_in', 'other'];
                foreach ($howHearOptions as $opt):
                ?>
                    <option value="<?php echo $opt; ?>"
                        <?php echo (($app['how_hear'] ?? '') === $opt) ? 'selected' : ''; ?>>
                        <?php echo ucfirst(str_replace('_', ' ', $opt)); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="button" class="btn btn-secondary" data-action-click="action7">Cancel</button>
            <button type="submit" class="btn btn-primary">Update Application</button>
        </div>
    </form>
</div>
