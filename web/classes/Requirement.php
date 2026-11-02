<?php
// classes/Requirement.php

require_once __DIR__ . '/Database.php';

class Requirement
{
    private ?PDO $pdo;

    /**
     * Map admission types to requirement IDs.
     * Based on enr_requirements inserts in your DB dump.
     * VERIFY these IDs against your enr_requirements table.
     */
    private const ADMISSION_REQUIREMENTS = [
        'freshmen'    => [1, 2, 3, 4, 5, 6],
        'senior_high' => [1, 2, 3, 4, 5, 6],
        'transferee'  => [1, 2, 3, 4, 5, 6, 7],
        'returnee'    => [3, 4, 5, 6],
    ];

    /** Fallback labels (used if DB lookup fails). */
    private const LABELS = [
        1  => 'Form 138 (Report Card)',
        2  => 'Form 137',
        3  => 'Certificate of Good Moral',
        4  => 'PSA Authenticated Birth Certificate',
        5  => 'Passport Size ID Picture (2pcs)',
        6  => 'Barangay Clearance',
        7  => 'Transcript of Records / Honorable Dismissal',
        12 => 'Medical Certificate',
        14 => 'Payment Receipt',
    ];

    public function __construct()
    {
        $this->pdo = Database::getConnection();
    }

    public static function idsForAdmissionType(string $type): array
    {
        return self::ADMISSION_REQUIREMENTS[$type] ?? [];
    }

    public static function labelFor(int $id): string
    {
        return self::LABELS[$id] ?? ('Requirement #' . $id);
    }

    /**
     * Return [['id'=>1,'name'=>'Form 138'], ...] for an admission type.
     */
    public function forAdmissionType(string $type): array
    {
        $ids = self::idsForAdmissionType($type);
        if (empty($ids)) {
            return [];
        }

        $names = [];
        if ($this->pdo) {
            try {
                $ph   = implode(',', array_fill(0, count($ids), '?'));
                $stmt = $this->pdo->prepare(
                    "SELECT requirement_id, requirement_name AS name FROM enr_requirements
                     WHERE requirement_id IN ($ph)"
                );
                $stmt->execute($ids);
                foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                    $names[(int) $row['requirement_id']] = $row['name'];
                }
            } catch (PDOException $e) {
                error_log('Requirement DB lookup failed: ' . $e->getMessage());
            }
        }

        $out = [];
        foreach ($ids as $id) {
            $out[] = [
                'id'   => $id,
                'name' => $names[$id] ?? self::labelFor($id),
            ];
        }
        return $out;
    }

    /**
     * All admission types → their requirement lists (for JS).
     */
    public function allByType(): array
    {
        $out = [];
        foreach (array_keys(self::ADMISSION_REQUIREMENTS) as $type) {
            $out[$type] = $this->forAdmissionType($type);
        }
        return $out;
    }
}
