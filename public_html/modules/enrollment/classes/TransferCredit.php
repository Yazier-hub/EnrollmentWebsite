<?php
require_once __DIR__ . '/Database.php';

/** Read-only access to credits approved by Regis. Evaluation and approval belong to Regis. */
final class TransferCredit
{
    private $connection;

    public function __construct(?PDO $connection = null)
    {
        $this->connection = $connection ?? Database::getInstance()->getConnection();
    }

    private function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /** Applicant ownership remains valid after conversion, even when evaluation.student_id is NULL. */
    public function hasCredit(int $studentId, int $subjectId, bool $lock = false): bool
    {
        return (bool) $this->query("SELECT e.id FROM rgr_student_subject_evaluations e
            JOIN rgr_curriculum_subjects cs ON cs.id = e.curriculum_subject_id
            JOIN rgr_curriculums c ON c.id = cs.curriculum_id
            JOIN enr_students st ON st.applicant_id = e.applicant_id AND st.course_id = c.course_id
            WHERE e.status = 'approved' AND st.student_id = ? AND cs.subject_id = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : ''), [$studentId, $subjectId])->fetchColumn();
    }

    /** Read Registrar-approved credits before a student record exists. Match underlying subjects across curricula. */
    public function getCreditedSubjectIdsForApplicant(int $applicantId): array
    {
        return array_map('intval', $this->query("SELECT DISTINCT cs.subject_id
            FROM rgr_student_subject_evaluations e
            JOIN rgr_curriculum_subjects cs ON cs.id = e.curriculum_subject_id
            JOIN rgr_curriculums c ON c.id = cs.curriculum_id
            JOIN enr_applicants a ON a.applicant_id = e.applicant_id AND a.course_id = c.course_id
            WHERE e.status = 'approved' AND e.applicant_id = ?", [$applicantId])->fetchAll(PDO::FETCH_COLUMN));
    }

    /** Earliest active-curriculum term with subjects still to take; NULL preserves ordinary placement. */
    public function getPlacementForApplicant(int $applicantId): ?array
    {
        $applicant = $this->query('SELECT admission_type, course_id FROM enr_applicants WHERE applicant_id = ?', [$applicantId])->fetch();
        if (!$applicant || strtolower(trim((string) ($applicant['admission_type'] ?? ''))) !== 'transferee') {
            return null;
        }
        $credits = $this->getCreditedSubjectIdsForApplicant($applicantId);
        if (!$credits) return null;

        $subjects = $this->query('SELECT DISTINCT cs.subject_id, cs.year_level, cs.semester
            FROM rgr_curriculum_subjects cs
            JOIN rgr_curriculums c ON c.id = cs.curriculum_id
            WHERE c.course_id = ? AND c.is_active = 1', [$applicant['course_id']])->fetchAll();
        if (!$subjects) return null;

        $terms = [];
        foreach ($subjects as $subject) {
            $year = (int) $subject['year_level'];
            $raw = strtolower(trim((string) $subject['semester']));
            $semester = preg_match('/^(1|1st|first)(\s|$)/', $raw) ? 1
                : (preg_match('/^(2|2nd|second)(\s|$)/', $raw) ? 2 : 0);
            if ($year < 1 || !$semester) {
                throw new RuntimeException('Cannot determine placement: check curriculum year and semester values.');
            }
            $terms[$year * 10 + $semester][(int) $subject['subject_id']] = true;
        }
        ksort($terms, SORT_NUMERIC);
        foreach ($terms as $key => $ids) {
            if (array_diff(array_keys($ids), $credits)) {
                $year = intdiv($key, 10);
                $semester = $key % 10;
                $suffix = [1 => 'st', 2 => 'nd', 3 => 'rd'][$year] ?? 'th';
                return ['year_level' => $year, 'semester' => $semester,
                    'grade_level' => $year . $suffix . ' Year',
                    'semester_name' => $semester === 1 ? '1st Semester' : '2nd Semester',
                    'curriculum_completed' => false];
            }
        }
        return ['year_level' => null, 'semester' => null, 'grade_level' => null,
            'semester_name' => null, 'curriculum_completed' => true];
    }
}
