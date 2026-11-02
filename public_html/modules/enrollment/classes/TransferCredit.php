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
        return (bool) $this->query("SELECT cr.id FROM rgr_student_credited_subjects cr
            JOIN rgr_student_subject_evaluations e ON e.id = cr.evaluation_id
                AND e.curriculum_subject_id = cr.curriculum_subject_id
                AND e.status = 'approved'
            JOIN rgr_curriculum_subjects cs ON cs.id = cr.curriculum_subject_id
            JOIN rgr_curriculums c ON c.id = cs.curriculum_id
            JOIN enr_students st ON st.applicant_id = e.applicant_id AND st.course_id = c.course_id
            WHERE st.student_id = ? AND cs.subject_id = ? LIMIT 1" . ($lock ? ' FOR UPDATE' : ''), [$studentId, $subjectId])->fetchColumn();
    }

    /** Read Registrar-approved credits before a student record exists. Match underlying subjects across curricula. */
    public function getCreditedSubjectIdsForApplicant(int $applicantId): array
    {
        return array_map('intval', $this->query("SELECT DISTINCT cs.subject_id
            FROM rgr_student_credited_subjects cr
            JOIN rgr_student_subject_evaluations e ON e.id = cr.evaluation_id
                AND e.curriculum_subject_id = cr.curriculum_subject_id AND e.status = 'approved'
            JOIN rgr_curriculum_subjects cs ON cs.id = cr.curriculum_subject_id
            JOIN rgr_curriculums c ON c.id = cs.curriculum_id
            JOIN enr_applicants a ON a.applicant_id = e.applicant_id AND a.course_id = c.course_id
            WHERE e.applicant_id = ?", [$applicantId])->fetchAll(PDO::FETCH_COLUMN));
    }
}
