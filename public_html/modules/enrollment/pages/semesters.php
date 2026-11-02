<?php
// View data supplied by SemestersPage through EnrollmentPage.
/** @var array<string, mixed>|false|null $activeSchoolYear */
/** @var array<string, mixed>|false|null $activeSemester */
/** @var array<int, array<string, mixed>> $schoolYears */
/** @var array<int, array<string, mixed>> $semesters */
?>
<main class="main-content">
    <div class="container">
        <div class="module-header">
            <h1>📅 Semester Management</h1>
            <p class="dashboard-subtitle">I-set kung aling semester at school year ang active ngayon</p>
        </div>

        <div class="module-content">
            <!-- MESSAGES -->
            <div id="messageBox" class="u-display-none-cb45"></div>

            <!-- CURRENT ACTIVE INFO -->
            <div class="stats-grid">
                <div class="stat-card primary">
                    <div class="number" id="activeSyDisplay">
                        <?php echo $activeSchoolYear
                            ? htmlspecialchars($activeSchoolYear['school_year'] ?? 'N/A')
                            : 'N/A'; ?>
                    </div>
                    <div class="label">Active School Year</div>
                </div>
                <div class="stat-card success">
                    <div class="number" id="activeSemDisplay">
                        <?php echo $activeSemester
                            ? htmlspecialchars($activeSemester['semester_name'] ?? 'N/A')
                            : 'N/A'; ?>
                    </div>
                    <div class="label">Active Semester</div>
                    <?php if ($activeSemester && !empty($activeSemester['school_year_name'])): ?>
                        <div class="sub-info">
                            SY: <?php echo htmlspecialchars($activeSemester['school_year_name']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SCHOOL YEARS -->
            <div class="table-container">
                <h2>📆 School Years</h2>
                <p class="section-note">I-click ang "Set Active" para gawing current school year.</p>

                <?php if (count($schoolYears) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($schoolYears as $sy):
                            $syId      = (int) ($sy['id'] ?? 0);
                            $syName    = $sy['school_year'] ?? '';
                            $isActive  = !empty($sy['is_active']);
                        ?>
                        <tr>
                            <td><?php echo $syId; ?></td>
                            <td><strong><?php echo htmlspecialchars($syName); ?></strong></td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge badge-success">✅ Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$isActive): ?>
                                    <button class="btn btn-sm btn-success" data-action-click="action1" data-action1-arg-0="<?php echo $syId; ?>" data-action1-arg-1="<?php echo htmlspecialchars(json_encode($syName), ENT_QUOTES); ?>">
                                        ✅ Set Active
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Current</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="alert alert-info">No school years found in the database.</div>
                <?php endif; ?>
            </div>

            <!-- SEMESTERS -->
            <div class="table-container u-margin-top-20px-3ae1">
                <h2>📅 Semesters</h2>
                <p class="section-note">I-click ang "Set Active" para gawing current semester.</p>

                <?php if (count($semesters) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Semester</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($semesters as $sem):
                            $semId      = (int) ($sem['id'] ?? 0);
                            $semName    = $sem['semester_name'] ?? '';
                            $syName     = $sem['school_year_name'] ?? '—';
                            $isActive   = !empty($sem['is_active']);
                        ?>
                        <tr>
                            <td><?php echo $semId; ?></td>
                            <td><strong><?php echo htmlspecialchars($semName); ?></strong></td>
                            <td><?php echo htmlspecialchars($syName); ?></td>
                            <td>
                                <?php if ($isActive): ?>
                                    <span class="badge badge-success">✅ Active</span>
                                <?php else: ?>
                                    <span class="badge badge-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$isActive): ?>
                                    <button class="btn btn-sm btn-success" data-action-click="action2" data-action2-arg-0="<?php echo $semId; ?>" data-action2-arg-1="<?php echo htmlspecialchars(json_encode($semName), ENT_QUOTES); ?>">
                                        ✅ Set Active
                                    </button>
                                <?php else: ?>
                                    <button class="btn btn-sm btn-secondary" disabled>Current</button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <div class="alert alert-info">No semesters found in the database.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
