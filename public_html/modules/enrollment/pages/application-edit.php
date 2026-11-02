<?php
// View data supplied by ApplicationEditPage through EnrollmentPage.
/** @var string $fullName */
/** @var array<string, mixed> $app */
/** @var string $message */
/** @var string|null $error */
/** @var int $applicationId */
/** @var array<int, array<string, mixed>> $courses */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>✏️ Edit Application</h1>
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

            <div class="edit-form-container">
                <div class="application-header">
                    <div class="application-avatar">
                        <?php echo strtoupper(
                            substr($app['first_name'] ?? '?', 0, 1)
                            . substr($app['surname'] ?? '?', 0, 1)
                        ); ?>
                    </div>
                    <div class="application-header-info">
                        <div class="name"><?php echo htmlspecialchars($fullName); ?></div>
                        <div class="id">📋 Application #: <?php echo (int) $app['applicant_id']; ?></div>
                        <div>
                            <span class="status-badge status-<?php echo htmlspecialchars($app['status'] ?? 'pending'); ?>">
                                <?php echo ucfirst(htmlspecialchars($app['status'] ?? 'pending')); ?>
                            </span>
                            <span class="u-margin-left-10px-6106 u-font-size-13px-3044 u-color-666-909c">
                                Submitted:
                                <?php echo !empty($app['submitted_at'])
                                    ? date('M d, Y h:i A', strtotime($app['submitted_at']))
                                    : 'N/A'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="u-margin-left-auto-8345">
                        <a href="?page=application-details&id=<?php echo (int) $applicationId; ?>" class="btn btn-info">← Back</a>
                        <a href="?page=applications" class="btn btn-secondary">📋 All Apps</a>
                    </div>
                </div>

                <form method="POST" class="edit-form" id="editForm" action="?page=application-edit&id=<?php echo (int) $applicationId; ?>">
                    <input type="hidden" name="action" value="update">
                    <input type="hidden" name="applicant_id" value="<?php echo (int) $applicationId; ?>">

                    <!-- ========== PERSONAL INFORMATION ========== -->
                    <div class="form-section">
                        <h3><span class="section-icon">👤</span> Personal Information</h3>
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
                                       value="<?php echo htmlspecialchars($app['suffix'] ?? ''); ?>"
                                       placeholder="e.g., Jr., Sr., III">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Admission Type <span class="required">*</span></label>
                                <select name="admission_type" class="form-control" required>
                                    <?php
                                    $admissionOptions = [
                                        'freshmen'    => 'Freshmen',
                                        'transferee'  => 'Transferee',
                                        'returnee'    => 'Returnee',
                                        'senior_high' => 'Senior High'
                                    ];
                                    $currentAdmission = $app['admission_type'] ?? 'freshmen';
                                    foreach ($admissionOptions as $val => $label):
                                    ?>
                                        <option value="<?php echo $val; ?>" <?php echo $currentAdmission === $val ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Working Student</label>
                                <select name="working_student" class="form-control">
                                    <?php $currentWorking = $app['working_student'] ?? 'No'; ?>
                                    <option value="No"  <?php echo $currentWorking === 'No'  ? 'selected' : ''; ?>>No</option>
                                    <option value="Yes" <?php echo $currentWorking === 'Yes' ? 'selected' : ''; ?>>Yes</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Sex <span class="required">*</span></label>
                                <select name="sex" class="form-control" required>
                                    <option value="">Select Sex</option>
                                    <?php $currentSex = $app['sex'] ?? ''; ?>
                                    <option value="Male"   <?php echo $currentSex === 'Male'   ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $currentSex === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other"  <?php echo $currentSex === 'Other'  ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Civil Status <span class="required">*</span></label>
                                <select name="civil_status" class="form-control" required>
                                    <option value="">Select Civil Status</option>
                                    <?php $currentCivil = $app['civil_status'] ?? ''; ?>
                                    <option value="Single"   <?php echo $currentCivil === 'Single'   ? 'selected' : ''; ?>>Single</option>
                                    <option value="Married"  <?php echo $currentCivil === 'Married'  ? 'selected' : ''; ?>>Married</option>
                                    <option value="Divorced" <?php echo $currentCivil === 'Divorced' ? 'selected' : ''; ?>>Divorced</option>
                                    <option value="Widowed"  <?php echo $currentCivil === 'Widowed'  ? 'selected' : ''; ?>>Widowed</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Date of Birth <span class="required">*</span></label>
                                <input type="date" name="date_of_birth" class="form-control"
                                       value="<?php echo htmlspecialchars($app['date_of_birth'] ?? ''); ?>"
                                       required data-action-change="action1">
                            </div>
                            <div class="form-group">
                                <label>Age</label>
                                <input type="number" name="age" id="age" class="form-control"
                                       value="<?php echo htmlspecialchars($app['age'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Place of Birth <span class="required">*</span></label>
                                <input type="text" name="place_of_birth" class="form-control"
                                       value="<?php echo htmlspecialchars($app['place_of_birth'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Religion</label>
                                <input type="text" name="religion" class="form-control"
                                       value="<?php echo htmlspecialchars($app['religion'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <!-- ========== CONTACT INFORMATION ========== -->
                    <div class="form-section">
                        <h3><span class="section-icon">📞</span> Contact Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address <span class="required">*</span></label>
                                <input type="email" name="email" class="form-control"
                                       value="<?php echo htmlspecialchars($app['email'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Contact Number <span class="required">*</span></label>
                                <input type="text" name="contact_number" class="form-control"
                                       value="<?php echo htmlspecialchars($app['contact_number'] ?? ''); ?>" required>
                                <div class="help-text">Format: 09xxxxxxxxx</div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Facebook Account</label>
                                <input type="text" name="facebook" class="form-control"
                                       value="<?php echo htmlspecialchars($app['facebook'] ?? ''); ?>"
                                       placeholder="Facebook username or URL">
                            </div>
                            <div class="form-group">
                                <label>Messenger</label>
                                <input type="text" name="messenger" class="form-control"
                                       value="<?php echo htmlspecialchars($app['messenger'] ?? ''); ?>"
                                       placeholder="Messenger account">
                            </div>
                        </div>
                    </div>

                    <!-- ========== ADDRESS ========== -->
                    <div class="form-section">
                        <h3><span class="section-icon">📍</span> Address Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Barangay <span class="required">*</span></label>
                                <input type="text" name="address_barangay" class="form-control"
                                       value="<?php echo htmlspecialchars($app['address_barangay'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>City/Municipality <span class="required">*</span></label>
                                <input type="text" name="address_city" class="form-control"
                                       value="<?php echo htmlspecialchars($app['address_city'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Province <span class="required">*</span></label>
                                <input type="text" name="address_province" class="form-control"
                                       value="<?php echo htmlspecialchars($app['address_province'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Complete Address</label>
                                <textarea name="address_complete" class="form-control" rows="2"><?php echo htmlspecialchars($app['address_complete'] ?? ''); ?></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ========== EDUCATIONAL BACKGROUND ========== -->
                    <div class="form-section">
                        <h3><span class="section-icon">🎓</span> Educational Background</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>School Last Attended <span class="required">*</span></label>
                                <input type="text" name="school_last_attended" class="form-control"
                                       value="<?php echo htmlspecialchars($app['school_last_attended'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Year Graduated <span class="required">*</span></label>
                                <input type="number" name="year_graduated" class="form-control"
                                       value="<?php echo htmlspecialchars($app['year_graduated'] ?? ''); ?>"
                                       min="1990" max="<?php echo date('Y'); ?>" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>How did you hear about us?</label>
                            <select name="how_hear" class="form-control">
                                <option value="">Select option</option>
                                <?php $currentHowHear = $app['how_hear'] ?? ''; ?>
                                <option value="social_media"  <?php echo $currentHowHear === 'social_media'  ? 'selected' : ''; ?>>Social Media</option>
                                <option value="friend"        <?php echo $currentHowHear === 'friend'        ? 'selected' : ''; ?>>Friend/Relative</option>
                                <option value="school"        <?php echo $currentHowHear === 'school'        ? 'selected' : ''; ?>>School</option>
                                <option value="advertisement" <?php echo $currentHowHear === 'advertisement' ? 'selected' : ''; ?>>Advertisement</option>
                                <option value="other"         <?php echo $currentHowHear === 'other'         ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- ========== PARENT/GUARDIAN ========== -->
                    <div class="form-section">
                        <h3><span class="section-icon">👨‍👩‍👧</span> Parent/Guardian Information</h3>
                        <div class="form-row">
                            <div class="form-group">
                                <label>Parent/Guardian Full Name <span class="required">*</span></label>
                                <input type="text" name="parent_full_name" class="form-control"
                                       value="<?php echo htmlspecialchars($app['parent_full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="form-group">
                                <label>Parent Contact Number</label>
                                <input type="text" name="parent_contact" class="form-control"
                                       value="<?php echo htmlspecialchars($app['parent_contact'] ?? ''); ?>">
                                <div class="help-text">Format: 09xxxxxxxxx</div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Parent Address</label>
                            <textarea name="parent_address" class="form-control" rows="2"><?php echo htmlspecialchars($app['parent_address'] ?? ''); ?></textarea>
                        </div>
                    </div>

                    <!-- ========== COURSE SELECTION ========== -->
                    <div class="form-section">
                        <h3><span class="section-icon">📚</span> Course Selection</h3>
                        <div class="form-group">
                            <label>Preferred Course <span class="required">*</span></label>
                            <select name="course_id" class="form-control" required>
                                <option value="">Select Course</option>
                                <?php $currentCourseId = (int) ($app['course_id'] ?? 0); ?>
                                <?php foreach ($courses as $c): ?>
                                    <option value="<?php echo (int) $c['id']; ?>"
                                        <?php echo $currentCourseId === (int) $c['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="help-text">Select the course the applicant wants to enroll in</div>
                        </div>
                    </div>

                    <!-- ========== FORM ACTIONS ========== -->
                    <div class="form-actions">
                        <a href="?page=application-details&id=<?php echo (int) $applicationId; ?>" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-success">
                            💾 Update Application
                        </button>
                    </div>
                </form>

                <!-- DANGER ZONE -->
                <?php if (($app['status'] ?? '') !== 'converted'): ?>
                <div class="danger-zone">
                    <h4>⚠️ Danger Zone</h4>
                    <p>This action cannot be undone. Please be certain before proceeding.</p>
                    <form method="POST" data-action-submit="action2" class="u-display-inline-block-6a19">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="applicant_id" value="<?php echo (int) $applicationId; ?>">
                        <button type="submit" class="btn btn-danger">🗑️ Delete Application</button>
                    </form>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
