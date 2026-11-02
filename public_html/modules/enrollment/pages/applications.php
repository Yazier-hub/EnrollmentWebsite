<?php
// View data supplied by ApplicationsPage through EnrollmentPage.
/** @var array<string, mixed> $stats */
/** @var string $message */
/** @var string $status */
/** @var string $search */
/** @var array<int, array<string, mixed>> $applications */
/** @var array<int, array<string, mixed>> $courses */
?>
<main class="main-content">
    <div class="container">
        <!-- HEADER: Forced left alignment -->
        <div class="module-header u-justify-content-flex-start-important-908e u-text-align-left-important-a3cc">
            <div class="module-header-text u-align-items-flex-start-important-26f6 u-text-align-left-important-a3cc u-width-100-6d56">
                <h1 class="u-text-align-left-important-a3cc u-margin-0-0-4px-0-important-0e20">📋 Applications Management</h1>
                <p class="dashboard-subtitle u-text-align-left-important-a3cc u-margin-0-important-d323">Manage student applications</p>
            </div>
        </div>

        <div class="module-content">

            <!-- STATISTICS -->
            <div class="stats-grid">
                <div class="stat-card stat-total">
                    <span class="number"><?php echo (int) ($stats['total'] ?? 0); ?></span>
                    <span class="label">📊 Total Applications</span>
                </div>
                <div class="stat-card stat-pending">
                    <span class="number"><?php echo (int) ($stats['pending'] ?? 0); ?></span>
                    <span class="label">⏳ Pending</span>
                </div>
                <div class="stat-card stat-converted">
                    <span class="number"><?php echo (int) ($stats['converted'] ?? 0); ?></span>
                    <span class="label">✅ Converted</span>
                </div>
                <div class="stat-card stat-rejected">
                    <span class="number"><?php echo (int) ($stats['rejected'] ?? 0); ?></span>
                    <span class="label">❌ Rejected</span>
                </div>
            </div>

            <!-- INFO BANNER -->
            <div class="info-banner">
                <strong>💡 How it works:</strong> Submit an application, then go to
                <a href="?page=enrollments">Enrollments</a> to enroll the student.
                <br>
                <small>✅ <strong>NEW:</strong> Optional fields have an <strong>N/A</strong>
                checkbox — tick it to explicitly set the field to "N/A".</small>
            </div>

            <!-- MESSAGES -->
            <?php if ($message !== ''): ?>
                <?php $isSuccess = (strpos($message, '✅') !== false); ?>
                <div class="alert alert-<?php echo $isSuccess ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- SEARCH & FILTERS (with Create Application button) -->
            <div class="search-section">
                <div class="filters-and-action">
                    <div class="status-filters">
                        <a href="?page=applications&status=pending"
                           class="status-filter <?php echo $status === 'pending' ? 'active' : ''; ?>">
                            ⏳ Pending <span class="count"><?php echo (int) ($stats['pending'] ?? 0); ?></span>
                        </a>
                        <a href="?page=applications&status=converted"
                           class="status-filter <?php echo $status === 'converted' ? 'active' : ''; ?>">
                            ✅ Converted <span class="count"><?php echo (int) ($stats['converted'] ?? 0); ?></span>
                        </a>
                        <a href="?page=applications&status=rejected"
                           class="status-filter <?php echo $status === 'rejected' ? 'active' : ''; ?>">
                            ❌ Rejected <span class="count"><?php echo (int) ($stats['rejected'] ?? 0); ?></span>
                        </a>
                        <a href="?page=applications&status=all"
                           class="status-filter <?php echo $status === 'all' ? 'active' : ''; ?>">
                            📊 All <span class="count"><?php echo (int) ($stats['total'] ?? 0); ?></span>
                        </a>
                    </div>

                    <button type="button" class="btn btn-primary btn-create-app" data-action-click="action1">
                        ➕ Create Application
                    </button>
                </div>

                <form method="GET" class="search-form">
                    <input type="hidden" name="page" value="applications">
                    <input type="hidden" name="status" value="<?php echo htmlspecialchars($status); ?>">
                    <input type="text" name="search"
                           placeholder="Search applications by name, email, or contact..."
                           value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit" class="btn btn-primary">🔍 Search</button>
                    <?php if ($search !== ''): ?>
                        <a href="?page=applications&status=<?php echo htmlspecialchars($status); ?>"
                           class="btn btn-secondary">Clear</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- APPLICATIONS TABLE -->
            <div class="applications-table-container">
                <div class="table-header">
                    <span class="title">
                        <?php echo ucfirst($status); ?> Applications
                        <?php if ($search !== ''): ?>
                            <span class="u-font-weight-normal-9aa7 u-color-666-909c u-font-size-14px-0cf0">
                                (search: "<?php echo htmlspecialchars($search); ?>")
                            </span>
                        <?php endif; ?>
                    </span>
                    <span class="badge-count"><?php echo count($applications); ?></span>
                </div>

                <?php if (count($applications) > 0): ?>
                <form method="POST" id="bulkActionForm" data-action-submit="action2">
                    <table class="table">
                        <thead>
                            <tr>
                                <th width="40">
                                    <input type="checkbox" id="selectAll" data-action-change="action3">
                                </th>
                                <th>Name</th>
                                <th>Course</th>
                                <th>Contact</th>
                                <th>Type</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($applications as $app): ?>
                            <tr>
                                <td class="checkbox-column">
                                    <?php if (($app['status'] ?? '') !== 'converted'): ?>
                                        <input type="checkbox" name="ids[]"
                                               value="<?php echo (int) $app['applicant_id']; ?>"
                                               class="app-checkbox">
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong>
                                        <?php
                                            $firstName = PageView::value($app['first_name'] ?? '', '');
                                            $surname   = PageView::value($app['surname']    ?? '', '');
                                            echo htmlspecialchars(trim($firstName . ' ' . $surname));
                                        ?>
                                    </strong>
                                    <?php
                                        $suffix = trim((string) ($app['suffix'] ?? ''));
                                        if ($suffix !== '' && strtoupper($suffix) !== 'N/A'):
                                    ?>
                                        <small>(<?php echo htmlspecialchars($suffix); ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars(PageView::value($app['course_code'] ?? '')); ?></td>
                                <td>
                                    <?php echo htmlspecialchars(PageView::value($app['contact_number'] ?? '')); ?><br>
                                    <small><?php echo htmlspecialchars(PageView::value($app['email'] ?? '')); ?></small>
                                </td>
                                <td>
                                    <?php
                                        $typeLabels = [
                                            'freshmen'    => 'Freshmen',
                                            'transferee'  => 'Transferee',
                                            'returnee'    => 'Returnee',
                                            'senior_high' => 'Senior High'
                                        ];
                                        $admissionType = $app['admission_type'] ?? '';
                                        $typeLabel  = $typeLabels[$admissionType] ?? 'N/A';
                                        $badgeClass = $admissionType !== ''
                                            ? 'badge-' . $admissionType
                                            : 'badge-default';
                                    ?>
                                    <span class="badge <?php echo htmlspecialchars($badgeClass); ?>">
                                        <?php echo htmlspecialchars($typeLabel); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                        $submitted = $app['submitted_at'] ?? null;
                                        echo $submitted ? date('M d, Y', strtotime($submitted)) : 'N/A';
                                    ?>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo htmlspecialchars($app['status'] ?? 'pending'); ?>">
                                        <?php echo ucfirst(htmlspecialchars($app['status'] ?? 'pending')); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="?page=application-details&id=<?php echo (int) $app['applicant_id']; ?>"
                                           class="btn btn-sm btn-info">View</a>
                                        <?php if (($app['status'] ?? '') === 'pending'): ?>
                                            <a href="javascript:void(0)" data-action-click="action4" data-action4-arg-0="<?php echo (int) $app['applicant_id']; ?>"
                                               class="btn btn-sm btn-warning">Edit</a>
                                            <a href="?page=enrollments&applicant_id=<?php echo (int) $app['applicant_id']; ?>"
                                               class="btn btn-sm btn-success">🎯 Enroll</a>
                                            <form method="POST" data-action-submit="action5" class="u-display-inline-block-6a19">
                                                <input type="hidden" name="applicant_id"
                                                       value="<?php echo (int) $app['applicant_id']; ?>">
                                                <input type="hidden" name="action" value="delete">
                                                <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>

                    <?php if ($status === 'pending'): ?>
                    <div class="bulk-actions">
                        <span>Bulk Actions:</span>
                        <select name="bulk_action" id="bulkAction">
                            <option value="">-- Select Action --</option>
                            <option value="delete">Delete Selected</option>
                            <option value="reject">Reject Selected</option>
                        </select>
                        <button type="submit" name="action" value="bulk_action"
                                class="btn btn-primary">Apply</button>
                        <span class="selected-count" id="selectedCount">0 selected</span>
                    </div>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                    <div class="text-center">
                        <?php if ($search !== ''): ?>
                            No applications found for
                            "<strong><?php echo htmlspecialchars($search); ?></strong>".
                            <a href="?page=applications&status=<?php echo htmlspecialchars($status); ?>">
                                Clear search
                            </a>
                        <?php else: ?>
                            No <?php echo $status === 'all' ? '' : htmlspecialchars(ucfirst($status)); ?>
                            applications found.
                            <?php if ($status === 'pending'): ?>
                                <a href="#" data-action-click="action6">Create one now</a>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- NEW APPLICATION MODAL -->
<div id="newApplicationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>📝 New Application</h2>
            <span class="close" data-action-click="action7">&times;</span>
        </div>
        <form method="POST" class="application-form" id="applicationForm">
            <input type="hidden" name="action" value="submit">

            <div class="form-section">
                <h3>👤 Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" required>
                    </div>

                    <?php PageView::input(
                        'middle_name', 'new_middle_name',
                        'Middle Name', false,
                        'Type or click N/A', '', 'text'
                    ); ?>

                    <div class="form-group">
                        <label>Surname <span class="required">*</span></label>
                        <input type="text" name="surname" required>
                    </div>

                    <?php PageView::input(
                        'suffix', 'new_suffix',
                        'Suffix', false,
                        'e.g., Jr., Sr., III', '', 'text'
                    ); ?>

                    <div class="form-group">
                        <label>Admission Type <span class="required">*</span></label>
                        <select name="admission_type" required>
                            <option value="freshmen">Freshmen</option>
                            <option value="transferee">Transferee</option>
                            <option value="returnee">Returnee</option>
                            <option value="senior_high">Senior High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Working Student</label>
                        <select name="working_student">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sex <span class="required">*</span></label>
                        <select name="sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Civil Status <span class="required">*</span></label>
                        <select name="civil_status" required>
                            <option value="">Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" data-action-change="action8">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" id="new_age" readonly>
                    </div>

                    <?php PageView::input(
                        'place_of_birth', 'new_place_of_birth',
                        'Place of Birth', false,
                        'Type or click N/A', '', 'text'
                    ); ?>

                    <?php PageView::input(
                        'religion', 'new_religion',
                        'Religion', false,
                        'Type or click N/A', '', 'text'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>📍 Address Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Barangay <span class="required">*</span></label>
                        <input type="text" name="address_barangay" required>
                    </div>
                    <div class="form-group">
                        <label>City/Municipality <span class="required">*</span></label>
                        <input type="text" name="address_city" required>
                    </div>
                    <div class="form-group">
                        <label>Province <span class="required">*</span></label>
                        <input type="text" name="address_province" required>
                    </div>

                    <?php PageView::input(
                        'address_complete', 'new_address_complete',
                        'Complete Address', false,
                        'Type or click N/A', '', 'textarea'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>📞 Contact Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number <span class="required">*</span></label>
                        <input type="text" name="contact_number" required placeholder="09xxxxxxxxx">
                        <div class="help-text">Format: 09xxxxxxxxx</div>
                    </div>

                    <?php PageView::input(
                        'facebook', 'new_facebook',
                        'Facebook Account', false,
                        'Type or click N/A', '', 'text'
                    ); ?>

                    <?php PageView::input(
                        'messenger', 'new_messenger',
                        'Messenger', false,
                        'Type or click N/A', '', 'text'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>🎓 Educational Background</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>School Last Attended <span class="required">*</span></label>
                        <input type="text" name="school_last_attended" required>
                    </div>

                    <?php PageView::input(
                        'year_graduated', 'new_year_graduated',
                        'Year Graduated', false,
                        'e.g., 2024-2025', '', 'text',
                        'Format: YYYY-YYYY or click N/A', true
                    ); ?>

                    <div class="form-group full-width">
                        <label>How did you hear about us?</label>
                        <select name="how_hear">
                            <option value="">Select option</option>
                            <option value="social_media">Social Media</option>
                            <option value="friend">Friend/Relative</option>
                            <option value="school">School</option>
                            <option value="advertisement">Advertisement</option>
                            <option value="other">Other</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>👨‍👩‍👧 Parent/Guardian Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Parent/Guardian Full Name <span class="required">*</span></label>
                        <input type="text" name="parent_full_name" required>
                    </div>

                    <?php PageView::input(
                        'parent_contact', 'new_parent_contact',
                        'Parent Contact Number', false,
                        '09xxxxxxxxx', '', 'text'
                    ); ?>

                    <?php PageView::input(
                        'parent_address', 'new_parent_address',
                        'Parent Address', false,
                        'Type or click N/A', '', 'textarea'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>📚 Course Selection</h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Preferred Course <span class="required">*</span></label>
                        <select name="course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo (int) $c['id']; ?>">
                                    <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="reset" class="btn btn-secondary">Reset</button>
                <button type="submit" class="btn btn-primary">Submit Application</button>
            </div>
        </form>
    </div>
</div>

<!-- EDIT APPLICATION MODAL -->
<div id="editApplicationModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>✏️ Edit Application</h2>
            <span class="close" data-action-click="action9">&times;</span>
        </div>
        <form method="POST" class="application-form" id="editForm">
            <input type="hidden" name="applicant_id" id="edit_applicant_id">
            <input type="hidden" name="action" value="update">
            <input type="hidden" name="redirect" value="?page=applications">

            <div class="form-section">
                <h3>👤 Personal Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>First Name <span class="required">*</span></label>
                        <input type="text" name="first_name" id="edit_first_name" required>
                    </div>

                    <?php PageView::input(
                        'middle_name', 'edit_middle_name',
                        'Middle Name', false,
                        'Type or click N/A'
                    ); ?>

                    <div class="form-group">
                        <label>Surname <span class="required">*</span></label>
                        <input type="text" name="surname" id="edit_surname" required>
                    </div>

                    <?php PageView::input(
                        'suffix', 'edit_suffix',
                        'Suffix', false,
                        'e.g., Jr., Sr., III'
                    ); ?>

                    <div class="form-group">
                        <label>Admission Type <span class="required">*</span></label>
                        <select name="admission_type" id="edit_admission_type" required>
                            <option value="freshmen">Freshmen</option>
                            <option value="transferee">Transferee</option>
                            <option value="returnee">Returnee</option>
                            <option value="senior_high">Senior High</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Working Student</label>
                        <select name="working_student" id="edit_working_student">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Sex <span class="required">*</span></label>
                        <select name="sex" id="edit_sex" required>
                            <option value="">Select Sex</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Civil Status <span class="required">*</span></label>
                        <select name="civil_status" id="edit_civil_status" required>
                            <option value="">Select Civil Status</option>
                            <option value="Single">Single</option>
                            <option value="Married">Married</option>
                            <option value="Divorced">Divorced</option>
                            <option value="Widowed">Widowed</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Date of Birth</label>
                        <input type="date" name="date_of_birth" id="edit_date_of_birth" data-action-change="action10">
                    </div>
                    <div class="form-group">
                        <label>Age</label>
                        <input type="number" name="age" id="edit_age" readonly>
                    </div>

                    <?php PageView::input(
                        'place_of_birth', 'edit_place_of_birth',
                        'Place of Birth', false,
                        'Type or click N/A'
                    ); ?>

                    <?php PageView::input(
                        'religion', 'edit_religion',
                        'Religion', false,
                        'Type or click N/A'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>📍 Address Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Barangay <span class="required">*</span></label>
                        <input type="text" name="address_barangay" id="edit_address_barangay" required>
                    </div>
                    <div class="form-group">
                        <label>City/Municipality <span class="required">*</span></label>
                        <input type="text" name="address_city" id="edit_address_city" required>
                    </div>
                    <div class="form-group">
                        <label>Province <span class="required">*</span></label>
                        <input type="text" name="address_province" id="edit_address_province" required>
                    </div>

                    <?php PageView::input(
                        'address_complete', 'edit_address_complete',
                        'Complete Address', false,
                        'Type or click N/A', '', 'textarea'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>📞 Contact Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Email Address <span class="required">*</span></label>
                        <input type="email" name="email" id="edit_email" required>
                    </div>
                    <div class="form-group">
                        <label>Contact Number <span class="required">*</span></label>
                        <input type="text" name="contact_number" id="edit_contact_number"
                               required placeholder="09xxxxxxxxx">
                    </div>

                    <?php PageView::input(
                        'facebook', 'edit_facebook',
                        'Facebook Account', false,
                        'Type or click N/A'
                    ); ?>

                    <?php PageView::input(
                        'messenger', 'edit_messenger',
                        'Messenger', false,
                        'Type or click N/A'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>🎓 Educational Background</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>School Last Attended <span class="required">*</span></label>
                        <input type="text" name="school_last_attended"
                               id="edit_school_last_attended" required>
                    </div>

                    <?php PageView::input(
                        'year_graduated', 'edit_year_graduated',
                        'Year Graduated', false,
                        'e.g., 2024-2025', '', 'text',
                        'Format: YYYY-YYYY or click N/A', true
                    ); ?>

                    <div class="form-group full-width">
                        <label>How did you hear about us?</label>
                        <select name="how_hear" id="edit_how_hear">
                            <option value="">Select option</option>
                            <option value="social_media">Social Media</option>
                            <option value="friend">Friend/Relative</option>
                            <option value="school">School</option>
                            <option value="advertisement">Advertisement</option>
                            <option value="other">Other</option>
                            <option value="N/A">N/A</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h3>👨‍👩‍👧 Parent/Guardian Information</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label>Parent/Guardian Full Name <span class="required">*</span></label>
                        <input type="text" name="parent_full_name"
                               id="edit_parent_full_name" required>
                    </div>

                    <?php PageView::input(
                        'parent_contact', 'edit_parent_contact',
                        'Parent Contact Number', false,
                        '09xxxxxxxxx'
                    ); ?>

                    <?php PageView::input(
                        'parent_address', 'edit_parent_address',
                        'Parent Address', false,
                        'Type or click N/A', '', 'textarea'
                    ); ?>
                </div>
            </div>

            <div class="form-section">
                <h3>📚 Course Selection</h3>
                <div class="form-grid">
                    <div class="form-group full-width">
                        <label>Preferred Course <span class="required">*</span></label>
                        <select name="course_id" id="edit_course_id" required>
                            <option value="">Select Course</option>
                            <?php foreach ($courses as $c): ?>
                                <option value="<?php echo (int) $c['id']; ?>">
                                    <?php echo htmlspecialchars(($c['code'] ?? '') . ' - ' . ($c['name'] ?? '')); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="button" class="btn btn-secondary" data-action-click="action11">Cancel</button>
                <button type="submit" class="btn btn-primary">Update Application</button>
            </div>
        </form>
    </div>
</div>
