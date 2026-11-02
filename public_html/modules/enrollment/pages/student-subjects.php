<?php
// View data supplied by StudentSubjectsPage through EnrollmentPage.
/** @var string $message */
/** @var array<string, mixed> $student */
/** @var int $studentId */
/** @var array<int, array<string, mixed>> $subjects */
/** @var array<string, mixed>|false|null $enrollment */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <div class="u-display-flex-f7db u-justify-content-space-between-daae u-align-items-center-1e2c u-flex-wrap-wrap-9012 u-gap-15px-7d09">
                <div>
                    <h1>📚 Student Subjects</h1>
                    <p class="dashboard-subtitle">
                        <?php echo htmlspecialchars(trim(
                            ($student['first_name'] ?? '') . ' ' . ($student['surname'] ?? '')
                        )); ?>
                        (<?php echo htmlspecialchars($student['student_number'] ?? ''); ?>)
                    </p>
                </div>
                <div class="u-display-flex-f7db u-gap-10px-114b u-flex-wrap-wrap-9012">
                    <a href="?page=student-details&id=<?php echo $studentId; ?>" class="btn btn-info">👤 Student Details</a>
                    <a href="?page=students" class="btn btn-secondary">← Back to Students</a>
                </div>
            </div>
        </div>

        <div class="module-content">
            <!-- Student Info Card -->
            <div class="student-info-card">
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Student Number</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['student_number'] ?? ''); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Name</span>
                        <span class="info-value"><?php echo htmlspecialchars(trim(
                            ($student['first_name'] ?? '') . ' ' . ($student['surname'] ?? '')
                        )); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Course</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['course_code'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Section</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['section_code'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Year Level</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['year_level'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Semester</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['semester'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">School Year</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['school_year'] ?? 'N/A'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Status</span>
                        <span class="info-value">
                            <?php $sStatus = $student['enrollment_status'] ?? 'enrolled'; ?>
                            <span class="status-badge status-<?php echo htmlspecialchars($sStatus); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($sStatus))); ?>
                            </span>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            <?php if ($message !== ''): ?>
                <div class="alert alert-<?php echo strpos($message, '✅') !== false ? 'success' : 'danger'; ?>">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Subjects Section -->
            <div class="subjects-container">
                <div class="subjects-header">
                    <h2>📖 Enrolled Subjects (Current Semester)</h2>
                    <span class="badge-count"><?php echo count($subjects); ?> Subjects</span>
                </div>

                <?php if (count($subjects) > 0): ?>
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
                                        $scheduleDisplay = $subject['day_of_week']
                                            . ' ' . date('h:i A', strtotime($subject['start_time']));
                                        if (!empty($subject['end_time'])) {
                                            $scheduleDisplay .= ' - ' . date('h:i A', strtotime($subject['end_time']));
                                        }
                                    }

                                    // Faculty
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

                                    // Enrollment status
                                    $enrollmentStatus = $subject['enrollment_status'] ?? 'enrolled';
                                    $gradeDisplay     = $subject['grade'] ?? null;

                                    // subject_type is NOT returned by the API anymore.
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
                                            <span class="schedule-badge has-schedule">
                                                📅 <?php echo htmlspecialchars($scheduleDisplay); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="schedule-badge no-schedule">
                                                ⏳ <?php echo htmlspecialchars($scheduleDisplay); ?>
                                            </span>
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
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($enrollmentStatus))); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($gradeDisplay !== null && $gradeDisplay !== ''): ?>
                                            <span class="grade-badge grade-<?php echo ((float) $gradeDisplay >= 75) ? 'passed' : 'failed'; ?>">
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

                    <!-- Summary Cards -->
                    <div class="summary-cards">
                        <div class="summary-card">
                            <div class="summary-number"><?php echo count($subjects); ?></div>
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
                            <div class="summary-label">Completed</div>
                        </div>
                        <div class="summary-card">
                            <?php
                                $withSchedule = 0;
                                foreach ($subjects as $s) {
                                    if (!empty($s['schedule_id'])) $withSchedule++;
                                }
                            ?>
                            <div class="summary-number"><?php echo $withSchedule; ?></div>
                            <div class="summary-label">With Schedule</div>
                        </div>
                        <div class="summary-card">
                            <?php
                                $inProgress = 0;
                                foreach ($subjects as $s) {
                                    if (($s['enrollment_status'] ?? '') === 'enrolled') $inProgress++;
                                }
                            ?>
                            <div class="summary-number"><?php echo $inProgress; ?></div>
                            <div class="summary-label">In Progress</div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info">
                        <p>No subjects found for this student's current semester.</p>
                        <p class="u-margin-top-10px-5371">
                            <a href="?page=enrollments" class="btn btn-primary">Go to Enrollments</a>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Subject Enrollment History -->
            <?php if (count($subjects) > 0 && is_array($enrollment)): ?>
            <div class="enrollment-history">
                <h3>📋 Enrollment Information</h3>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Enrollment Date</span>
                        <span class="info-value"><?php
                            echo !empty($enrollment['enrollment_date'])
                                ? htmlspecialchars(date('M d, Y', strtotime($enrollment['enrollment_date'])))
                                : 'N/A';
                        ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Enrollment Status</span>
                        <span class="info-value">
                            <?php $eStatus = $enrollment['enrollment_status'] ?? 'pending'; ?>
                            <span class="status-badge status-<?php echo htmlspecialchars($eStatus); ?>">
                                <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($eStatus))); ?>
                            </span>
                        </span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Academic Standing</span>
                        <span class="info-value"><?php echo htmlspecialchars($enrollment['academic_standing'] ?? 'Good Standing'); ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">School Year</span>
                        <span class="info-value"><?php echo htmlspecialchars($enrollment['school_year'] ?? 'N/A'); ?></span>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</main>
