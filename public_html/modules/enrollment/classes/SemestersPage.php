<?php
require_once __DIR__ . '/EnrollmentPage.php';
require_once __DIR__ . '/Database.php';

/** Request handling and view data for semesters. */
final class SemestersPage extends EnrollmentPage
{
    protected const VIEW = 'semesters';

    protected function loadData(): array
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $basePath = dirname(__DIR__);

        $db = Database::getInstance();

        // ============================================================
        // All school years — FIX: column is `name`
        // ============================================================
        $syStmt = $db->prepare("
            SELECT id, name AS school_year, is_active, created_at, updated_at
            FROM rgr_school_years
            ORDER BY name DESC
        ");
        $syStmt->execute();
        $schoolYears = $syStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($schoolYears)) $schoolYears = [];

        // ============================================================
        // All semesters — FIX: column is `name`; join to SY for context
        // ============================================================
        $semStmt = $db->prepare("
            SELECT s.id,
                   s.name AS semester_name,
                   s.school_year_id,
                   s.is_active,
                   s.created_at,
                   s.updated_at,
                   sy.name AS school_year_name
            FROM rgr_semesters s
            LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
            ORDER BY sy.name DESC, s.id ASC
        ");
        $semStmt->execute();
        $semesters = $semStmt->fetchAll(PDO::FETCH_ASSOC);
        if (!is_array($semesters)) $semesters = [];

        // ============================================================
        // Current active school year — FIX: column is `name`
        // ============================================================
        $activeSyStmt = $db->prepare("
            SELECT id, name AS school_year, is_active
            FROM rgr_school_years
            WHERE is_active = 1
            LIMIT 1
        ");
        $activeSyStmt->execute();
        $activeSchoolYear = $activeSyStmt->fetch(PDO::FETCH_ASSOC);

        // ============================================================
        // Current active semester — FIX: column is `name`
        // ============================================================
        $activeSemStmt = $db->prepare("
            SELECT s.id, s.name AS semester_name, s.is_active, sy.name AS school_year_name
            FROM rgr_semesters s
            LEFT JOIN rgr_school_years sy ON s.school_year_id = sy.id
            WHERE s.is_active = 1
            LIMIT 1
        ");
        $activeSemStmt->execute();
        $activeSemester = $activeSemStmt->fetch(PDO::FETCH_ASSOC);

        $pageTitle = 'Semester Management';

        return [
            'activeSchoolYear' => $activeSchoolYear ?? null,
            'activeSemester' => $activeSemester ?? null,
            'schoolYears' => $schoolYears ?? null,
            'semesters' => $semesters ?? null,
            'pageTitle' => $pageTitle ?? null,
        ];
    }
}
