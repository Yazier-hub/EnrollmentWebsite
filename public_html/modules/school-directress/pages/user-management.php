<?php
include_once __DIR__ . '/../../../auth/session.php';
include __DIR__ . '/../classes/Department.php';
include __DIR__ . '/../classes/Position.php';
include __DIR__ . '/../classes/Unit.php';
include __DIR__ . '/../../../auth/guard.php';

/* Department Class */
$departmentsClass = new Department();
$departments = $departmentsClass->getAllDepartments();

/* Position Class */
$positionClass = new Position();
$positions = $positionClass->getAllPositions();

/* Unit Class */
$unitClass = new Unit();
$units = $unitClass->getAllUnits();

/* Employee rows are loaded via AJAX (controller → user_management.js) */
?>

<div class="module-header">
    <h1>User Management</h1>
    <p>View and monitor user accounts within the system.</p>
</div>

<div class="module-content">

    <!-- ── Toolbar: search + filters + export ── -->
    <div class="um-toolbar">
        <h3>Employees</h3>
        <div class="um-search">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" id="umSearch" placeholder="Search by name, department, position…" autocomplete="off">
        </div>

        <select class="um-sel" id="umDepartment">
            <option value="">All Departments</option>
            <?php foreach ($departments as $dept): ?>
            <option value="<?= htmlspecialchars($dept['department_name']) ?>">
                <?= htmlspecialchars($dept['department_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select class="um-sel" id="umPosition">
            <option value="">All Positions</option>
            <?php foreach ($positions as $pos): ?>
            <option value="<?= htmlspecialchars($pos['position_name']) ?>">
                <?= htmlspecialchars($pos['position_name']) ?>
            </option>
            <?php endforeach; ?>
        </select>

        <select class="um-sel" id="umStatus">
            <option value="">All Status</option>
            <option value="Active">Active</option>
            <option value="Probationary">Probationary</option>
            <option value="Resigned">Resigned</option>
            <option value="Terminated">Terminated</option>
        </select>

        <button type="button" id="umExportBtn" class="um-btn-primary">Export CSV</button>
    </div>

    <!-- ── Table (rows rendered by JS) ── -->
    <div class="um-table-wrap">
        <table class="um-table">
            <thead>
                <tr>
                    <th></th>
                    <th>Employee</th>
                    <th>Employee Code</th>
                    <th>Department</th>
                    <th>Unit</th>
                    <th>Position</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody id="umBody">
                <tr><td colspan="8"><div class="um-empty">Loading employees…</div></td></tr>
            </tbody>
        </table>
    </div>

    <!-- ── Footer: "Showing X-Y of Z" + numbered pagination ── -->
    <div class="um-footer">
        <span class="um-showing" id="umShowing"></span>
        <div class="um-page-nav" id="umPageNav"></div>
    </div>

</div>