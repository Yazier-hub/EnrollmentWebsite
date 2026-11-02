<?php
// Requirement definitions and applicant-owned submission records.
require_once __DIR__ . '/Model.php';

class Requirement extends Model {
    protected $table      = 'enr_requirements';
    protected $primaryKey = 'requirement_id';
    protected $fillable   = ['requirement_name', 'requirement_category', 'is_mandatory'];

    // enr_requirements has created_at only; the database supplies its default.
    protected $timestamps = false;

    private static $admissionTypeCache = [];

    public function __construct() {
        parent::__construct();
    }

    /* ============================================================
       CATEGORY / ADMISSION HELPERS
    ============================================================ */

    public function getRequirementsByCategory($category) {
        try {
            $sql  = "SELECT * FROM enr_requirements
                     WHERE requirement_category = ?
                     ORDER BY is_mandatory DESC, requirement_name";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([self::normalizeCategory($category)]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getRequirementsByCategory: " . $e->getMessage());
            return [];
        }
    }

    public function getAllCategories() {
        try {
            $sql  = "SELECT DISTINCT requirement_category FROM enr_requirements ORDER BY requirement_category";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll();

            if (empty($result)) {
                return [
                    ['requirement_category' => 'freshmen'],
                    ['requirement_category' => 'transferee'],
                    ['requirement_category' => 'continuing']
                ];
            }
            return $result;
        } catch (Exception $e) {
            error_log("Error in getAllCategories: " . $e->getMessage());
            return [
                ['requirement_category' => 'freshmen'],
                ['requirement_category' => 'transferee'],
                ['requirement_category' => 'continuing']
            ];
        }
    }

    public static function normalizeCategory($category) {
        $category = trim((string) $category);
        return ['returnee' => 'continuing', 'senior_high' => 'freshmen'][$category]
            ?? ($category === '' ? 'freshmen' : $category);
    }

    private function getStudentApplicant($studentId, $lock = false) {
        $sql = "SELECT a.applicant_id, a.admission_type
                FROM enr_students s
                JOIN enr_applicants a ON a.applicant_id = s.applicant_id
                WHERE s.student_id = ?";
        if ($lock) $sql .= ' FOR UPDATE';
        $stmt = $this->connection->prepare($sql);
        $stmt->execute([(int) $studentId]);
        return $stmt->fetch();
    }

    private function getStudentAdmissionType($studentId) {
        if (!isset(self::$admissionTypeCache[$studentId])) {
            $applicant = $this->getStudentApplicant($studentId);
            self::$admissionTypeCache[$studentId] = self::normalizeCategory($applicant['admission_type'] ?? null);
        }
        return self::$admissionTypeCache[$studentId];
    }

    public function clearAdmissionTypeCache() {
        self::$admissionTypeCache = [];
    }

    /* ============================================================
       STUDENT REQUIREMENTS
    ============================================================ */

    public function getStudentRequirements($studentId, $category = null) {
        try {
            $applicant = $this->getStudentApplicant($studentId);
            if (!$applicant) return [];
            $category = self::normalizeCategory($category ?: $applicant['admission_type']);

            // Use one submission per requirement even if older duplicate rows exist.
            // Keep BLOB data out of checklist and JSON responses.
            $sql = "SELECT r.*, sr.id AS submission_id,
                           COALESCE(sr.is_submitted, 0) AS is_submitted,
                           COALESCE(sr.status, 'pending') AS status,
                           sr.submitted_date, sr.notes, sr.created_at, sr.updated_at,
                           sr.file_name, sr.custom_name, sr.file_mime,
                           COALESCE(OCTET_LENGTH(sr.file_data), 0) > 0 AS has_file
                    FROM enr_requirements r
                    LEFT JOIN enr_student_requirements sr ON sr.id = (
                        SELECT MAX(saved.id) FROM enr_student_requirements saved
                        WHERE saved.applicant_id = ? AND saved.requirement_id = r.requirement_id
                    )
                    WHERE r.requirement_category = ?
                    ORDER BY r.is_mandatory DESC, r.requirement_name";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([(int) $applicant['applicant_id'], $category]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log('Error in getStudentRequirements: ' . $e->getMessage());
            return [];
        }
    }

    public function getStudentRequirementsByCategory($studentId, $category) {
        return $this->getStudentRequirements($studentId, $category);
    }

    public function getStudentRequirementsWithStatus($studentId) {
        return $this->getRequirementsWithStudentStatus($studentId);
    }

    /* ============================================================
       INITIALIZE
    ============================================================ */

    public function initializeStudentRequirements($studentId, $applicantId = null) {
        $ownTransaction = !$this->connection->inTransaction();
        try {
            if ($ownTransaction) $this->connection->beginTransaction();
            // Serialize initialization and updates without requiring a new unique index.
            $applicant = $this->getStudentApplicant($studentId, true);
            if (!$applicant || ($applicantId !== null && (int) $applicantId !== (int) $applicant['applicant_id'])) {
                throw new InvalidArgumentException('Student and applicant do not match.');
            }
            $applicantId = (int) $applicant['applicant_id'];
            $requirements = $this->getRequirementsByCategory(self::normalizeCategory($applicant['admission_type']));
            $exists = $this->connection->prepare(
                'SELECT id FROM enr_student_requirements WHERE applicant_id = ? AND requirement_id = ? LIMIT 1 FOR UPDATE'
            );
            $insert = $this->connection->prepare(
                'INSERT INTO enr_student_requirements (applicant_id, requirement_id, is_submitted) VALUES (?, ?, 0)'
            );
            $inserted = 0;
            foreach ($requirements as $requirement) {
                $params = [$applicantId, $requirement['requirement_id']];
                $exists->execute($params);
                if (!$exists->fetch()) {
                    $insert->execute($params);
                    $inserted++;
                }
            }
            if ($ownTransaction) $this->connection->commit();
            return $inserted;
        } catch (Exception $e) {
            if ($ownTransaction && $this->connection->inTransaction()) $this->connection->rollBack();
            error_log('Error in initializeStudentRequirements: ' . $e->getMessage());
            return 0;
        }
    }

    /* ============================================================
       UPSERT
    ============================================================ */

    public function updateOrCreateRequirementStatus($studentId, $requirementId, $isSubmitted = 0, $notes = null) {
        $ownTransaction = !$this->connection->inTransaction();
        try {
            if ((int) $studentId <= 0 || (int) $requirementId <= 0) return false;
            $isSubmitted = filter_var($isSubmitted, FILTER_VALIDATE_BOOLEAN) ? 1 : 0;
            if ($ownTransaction) $this->connection->beginTransaction();
            $applicant = $this->getStudentApplicant($studentId, true);
            if (!$applicant || !$this->getRequirementById($requirementId)) {
                throw new InvalidArgumentException('Student or requirement not found.');
            }
            $applicantId = (int) $applicant['applicant_id'];
            $notes = $notes === null ? null : trim((string) $notes);
            if ($notes === '') $notes = null;

            $stmt = $this->connection->prepare(
                'SELECT id FROM enr_student_requirements WHERE applicant_id = ? AND requirement_id = ? ORDER BY id DESC LIMIT 1 FOR UPDATE'
            );
            $stmt->execute([$applicantId, (int) $requirementId]);
            $existing = $stmt->fetch();
            if ($existing) {
                // Preserve verification status, attachments, custom names, and the original submission date.
                $stmt = $this->connection->prepare(
                    'UPDATE enr_student_requirements
                     SET submitted_date = CASE WHEN ? = 1 THEN COALESCE(submitted_date, NOW()) ELSE NULL END,
                         is_submitted = ?, notes = ?, updated_at = NOW()
                     WHERE id = ?'
                );
                $stmt->execute([$isSubmitted, $isSubmitted, $notes, $existing['id']]);
            } else {
                $stmt = $this->connection->prepare(
                    'INSERT INTO enr_student_requirements
                         (applicant_id, requirement_id, is_submitted, submitted_date, notes)
                     VALUES (?, ?, ?, CASE WHEN ? = 1 THEN NOW() ELSE NULL END, ?)'
                );
                $stmt->execute([$applicantId, (int) $requirementId, $isSubmitted, $isSubmitted, $notes]);
            }
            if ($ownTransaction) $this->connection->commit();
            return true;
        } catch (Exception $e) {
            if ($ownTransaction && $this->connection->inTransaction()) $this->connection->rollBack();
            error_log('Error in updateOrCreateRequirementStatus: ' . $e->getMessage());
            return false;
        }
    }

    public function createRequirementStatus($studentId, $requirementId, $isSubmitted = 0, $notes = null) {
        return $this->updateOrCreateRequirementStatus($studentId, $requirementId, $isSubmitted, $notes);
    }

    public function updateRequirementStatus($studentId, $requirementId, $isSubmitted = 0, $notes = null) {
        return $this->updateOrCreateRequirementStatus($studentId, $requirementId, $isSubmitted, $notes);
    }

    public function getStudentRequirementStatus($studentId, $requirementId, $submissionId = null) {
        try {
            $sql = 'SELECT sr.* FROM enr_student_requirements sr
                    JOIN enr_students s ON s.applicant_id = sr.applicant_id
                    WHERE s.student_id = ? AND sr.requirement_id = ?';
            $params = [(int) $studentId, (int) $requirementId];
            if ($submissionId !== null) {
                $sql .= ' AND sr.id = ?';
                $params[] = (int) $submissionId;
            }
            $sql .= ' ORDER BY sr.id DESC LIMIT 1';
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log('Error in getStudentRequirementStatus: ' . $e->getMessage());
            return null;
        }
    }

    /* ============================================================
       COMPLETION / STATS
    ============================================================ */

    public function getRequirementCompletionStatus($studentId, $category = null) {
        $requirements = $this->getStudentRequirements($studentId, $category);
        $total = count($requirements);
        $submitted = count(array_filter($requirements, function ($row) {
            return (int) $row['is_submitted'] === 1;
        }));
        return [
            'total' => $total,
            'submitted' => $submitted,
            'percentage' => $total > 0 ? round($submitted / $total * 100) : 0,
            'remaining' => $total - $submitted,
            'is_complete' => $total > 0 && $submitted === $total
        ];
    }

    public function hasCompletedMandatoryRequirements($studentId, $category = null) {
        $mandatory = array_filter($this->getStudentRequirements($studentId, $category), function ($row) {
            return (int) $row['is_mandatory'] === 1;
        });
        foreach ($mandatory as $requirement) {
            if ((int) $requirement['is_submitted'] !== 1) return false;
        }
        return count($mandatory) > 0;
    }

    public function getStudentProgressByCategory($studentId) {
        $requirements = $this->getStudentRequirements($studentId);
        if (!$requirements) return null;
        $progress = ['requirement_category' => $requirements[0]['requirement_category'],
                     'total' => count($requirements), 'submitted' => 0,
                     'mandatory_submitted' => 0, 'mandatory_total' => 0];
        foreach ($requirements as $requirement) {
            $submitted = (int) $requirement['is_submitted'] === 1;
            $mandatory = (int) $requirement['is_mandatory'] === 1;
            $progress['submitted'] += (int) $submitted;
            $progress['mandatory_total'] += (int) $mandatory;
            $progress['mandatory_submitted'] += (int) ($submitted && $mandatory);
        }
        return $progress;
    }

    /* ============================================================
       CRUD
    ============================================================ */

    public function addRequirement($data) {
        try {
            if (trim((string) ($data['requirement_name'] ?? '')) === '' || empty($data['requirement_category'])) {
                return ['success' => false, 'message' => 'Requirement name and category are required'];
            }

            if (mb_strlen(trim($data['requirement_name'])) > 100
                || !in_array($data['requirement_category'], ['freshmen', 'transferee', 'continuing'], true)) {
                return ['success' => false, 'message' => 'Invalid requirement name or category'];
            }

            $sql  = "SELECT 1 FROM enr_requirements
                     WHERE requirement_name = ? AND requirement_category = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([trim($data['requirement_name']), $data['requirement_category']]);
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Requirement already exists for this category'];
            }

            $insertData = [
                'requirement_name'     => trim($data['requirement_name']),
                'requirement_category' => $data['requirement_category'],
                'is_mandatory'         => isset($data['is_mandatory']) ? (int) $data['is_mandatory'] : 1
            ];

            $result = $this->create($insertData);
            if ($result) {
                $newId = is_array($result)
                    ? ($result[$this->primaryKey] ?? null)
                    : $result;
                return ['success' => true, 'message' => 'Requirement added successfully', 'id' => $newId];
            }
            return ['success' => false, 'message' => 'Failed to add requirement'];
        } catch (Exception $e) {
            error_log("Error in addRequirement: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function updateRequirement($requirementId, $data) {
        try {
            if (empty($requirementId)) {
                return ['success' => false, 'message' => 'Requirement ID is required'];
            }

            $updateData = [];
            if (isset($data['requirement_name'])) {
                $updateData['requirement_name'] = trim($data['requirement_name']);
            }
            if (isset($data['requirement_category'])) {
                $updateData['requirement_category'] = $data['requirement_category'];
            }
            if (isset($data['is_mandatory'])) {
                $updateData['is_mandatory'] = (int) $data['is_mandatory'];
            }

            if (empty($updateData)) {
                return ['success' => false, 'message' => 'No data to update'];
            }

            if (!$this->findById($requirementId)) {
                return ['success' => false, 'message' => 'Requirement not found'];
            }

            if ((isset($updateData['requirement_name']) && ($updateData['requirement_name'] === '' || mb_strlen($updateData['requirement_name']) > 100))
                || (isset($updateData['requirement_category']) && !in_array($updateData['requirement_category'], ['freshmen', 'transferee', 'continuing'], true))) {
                return ['success' => false, 'message' => 'Invalid requirement name or category'];
            }
            $result = $this->update($requirementId, $updateData);
            if ($result) {
                return ['success' => true, 'message' => 'Requirement updated successfully'];
            }
            return ['success' => false, 'message' => 'Failed to update requirement'];
        } catch (Exception $e) {
            error_log("Error in updateRequirement: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function deleteRequirement($requirementId) {
        try {
            if (empty($requirementId)) {
                return ['success' => false, 'message' => 'Requirement ID is required'];
            }

            if (!$this->findById($requirementId)) {
                return ['success' => false, 'message' => 'Requirement not found'];
            }

            $deleted = $this->delete($requirementId);
            if ($deleted) {
                return ['success' => true, 'message' => 'Requirement deleted successfully'];
            }
            return ['success' => false, 'message' => 'Failed to delete requirement'];
        } catch (Exception $e) {
            error_log("Error in deleteRequirement: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getAllRequirements($category = null) {
        try {
            if ($category) {
                return $this->getRequirementsByCategory($category);
            }

            $sql  = "SELECT * FROM enr_requirements ORDER BY requirement_category, requirement_name";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllRequirements: " . $e->getMessage());
            return [];
        }
    }

    public function getRequirementById($requirementId) {
        try {
            return $this->findById($requirementId);
        } catch (Exception $e) {
            error_log("Error in getRequirementById: " . $e->getMessage());
            return null;
        }
    }

    /* ============================================================
       GROUPING / ADMISSION MAPPING
    ============================================================ */

    public function getRequirementsGroupedByCategory() {
        try {
            $sql  = "SELECT * FROM enr_requirements ORDER BY requirement_category, requirement_name";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $results = $stmt->fetchAll();

            $grouped = [];
            foreach ($results as $req) {
                $grouped[$req['requirement_category']][] = $req;
            }
            return $grouped;
        } catch (Exception $e) {
            error_log("Error in getRequirementsGroupedByCategory: " . $e->getMessage());
            return [];
        }
    }

    public function getRequirementCountByCategory() {
        try {
            $sql = "SELECT
                        requirement_category,
                        COUNT(*) as count,
                        SUM(is_mandatory) as mandatory_count
                    FROM enr_requirements
                    GROUP BY requirement_category";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getRequirementCountByCategory: " . $e->getMessage());
            return [];
        }
    }

    public function getRequirementsByAdmissionType($admissionType) {
        return $this->getRequirementsByCategory(self::normalizeCategory($admissionType));
    }

    public function requirementExistsForCategory($requirementName, $category) {
        try {
            $sql  = "SELECT COUNT(*) as count FROM enr_requirements
                     WHERE requirement_name = ? AND requirement_category = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([trim($requirementName), $category]);
            $result = $stmt->fetch();
            return ((int) ($result['count'] ?? 0)) > 0;
        } catch (Exception $e) {
            error_log("Error in requirementExistsForCategory: " . $e->getMessage());
            return false;
        }
    }

    /* ============================================================
       SUMMARY HELPERS
    ============================================================ */

    public function getStudentRequirementsSummary($studentId) {
        $completion         = $this->getRequirementCompletionStatus($studentId);
        $mandatoryCompleted = $this->hasCompletedMandatoryRequirements($studentId);

        return [
            'total_requirements'  => $completion['total'],
            'completed'           => $completion['submitted'],
            'pending'             => $completion['remaining'],
            'percentage'          => $completion['percentage'],
            'is_complete'         => $completion['is_complete'],
            'mandatory_completed' => $mandatoryCompleted
        ];
    }

    public function getRequirementsWithStudentStatus($studentId, $category = null) {
        $requirements = $this->getStudentRequirements($studentId, $category);
        foreach ($requirements as &$requirement) {
            $submitted = (int) $requirement['is_submitted'] === 1;
            $inProgress = !$submitted && !empty($requirement['notes']);
            $requirement['status_label'] = $submitted ? 'Submitted' : ($inProgress ? 'In Progress' : 'Pending');
            $requirement['status_display'] = $requirement['status_label'];
            $requirement['status_class'] = $submitted ? 'success' : ($inProgress ? 'warning' : 'danger');
            $requirement['student_updated_at'] = $requirement['updated_at'];
        }
        unset($requirement);
        return $requirements;
    }

    public function countRequirementsByCategory() {
        try {
            $sql = "SELECT
                        requirement_category,
                        COUNT(*) as total,
                        SUM(is_mandatory) as mandatory
                    FROM enr_requirements
                    GROUP BY requirement_category";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in countRequirementsByCategory: " . $e->getMessage());
            return [];
        }
    }

    /* ============================================================
       INCOMPLETE-STUDENT LOOKUP
    ============================================================ */

    public function getStudentsWithIncompleteRequirements($admissionType = null) {
        try {
            $sql = "SELECT DISTINCT
                        s.student_id,
                        s.student_number,
                        a.first_name, a.middle_name, a.surname, a.suffix,
                        c.code as course_code, c.name as course_name,
                        sec.section_code, sec.grade_level,
                        a.admission_type,
                        (SELECT COUNT(DISTINCT e.schedule_id) FROM enr_enrollments e
                         WHERE e.student_id = s.student_id
                           AND e.enrollment_status = 'enrolled') as enrolled_subjects
                    FROM enr_students s
                    JOIN enr_applicants a ON s.applicant_id = a.applicant_id
                    LEFT JOIN rgr_courses c ON s.course_id = c.id
                    LEFT JOIN cc_sections sec ON s.section_id = sec.id
                    WHERE s.enrollment_status = 'enrolled'";

            $params = [];

            if ($admissionType) {
                $sql      .= " AND a.admission_type = ?";
                $params[] = $admissionType;
            }

            $sql .= " AND EXISTS (
                        SELECT 1
                        FROM enr_requirements r
                        LEFT JOIN enr_student_requirements sr ON sr.id = (
                            SELECT MAX(saved.id) FROM enr_student_requirements saved
                            WHERE saved.applicant_id = s.applicant_id
                              AND saved.requirement_id = r.requirement_id
                        )
                        WHERE r.is_mandatory = 1
                          AND COALESCE(sr.is_submitted, 0) = 0
                          AND r.requirement_category = CASE
                                WHEN a.admission_type = 'returnee'    THEN 'continuing'
                                WHEN a.admission_type = 'senior_high' THEN 'freshmen'
                                ELSE COALESCE(NULLIF(a.admission_type, ''), 'freshmen')
                              END
                      )
                      ORDER BY a.surname, a.first_name";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getStudentsWithIncompleteRequirements: " . $e->getMessage());
            return [];
        }
    }

    /* ============================================================
       ENROLLMENT SUMMARY
    ============================================================ */

    public function getStudentEnrolledSubjectsSummary($studentId) {
        try {
            $schoolYear = date('Y') . '-' . (date('Y') + 1);

            $sql = "SELECT
                        COUNT(DISTINCT e.schedule_id) as total_subjects,
                        SUM(CASE WHEN e.enrollment_status = 'enrolled'  THEN 1 ELSE 0 END) as enrolled_count,
                        SUM(CASE WHEN e.enrollment_status = 'completed' THEN 1 ELSE 0 END) as completed_count,
                        SUM(CASE WHEN e.enrollment_status = 'dropped'   THEN 1 ELSE 0 END) as dropped_count
                    FROM enr_enrollments e
                    WHERE e.student_id = ?
                      AND e.school_year = ?";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute([$studentId, $schoolYear]);
            $result = $stmt->fetch();

            return [
                'total_subjects'  => (int) ($result['total_subjects']  ?? 0),
                'enrolled_count'  => (int) ($result['enrolled_count']  ?? 0),
                'completed_count' => (int) ($result['completed_count'] ?? 0),
                'dropped_count'   => (int) ($result['dropped_count']   ?? 0),
                'school_year'     => $schoolYear
            ];
        } catch (Exception $e) {
            error_log("Error in getStudentEnrolledSubjectsSummary: " . $e->getMessage());
            return [
                'total_subjects'  => 0,
                'enrolled_count'  => 0,
                'completed_count' => 0,
                'dropped_count'   => 0,
                'school_year'     => null
            ];
        }
    }

    /* ============================================================
       ⭐ NEW: PAYMENT SUBMISSIONS
    ============================================================ */

    public function getAllPaymentSubmissions($statusFilter = null) {
        try {
            $sql = "SELECT ps.id,
                           ps.student_id,
                           ps.school_year_id,
                           ps.semester_id,
                           ps.file_name,
                           ps.file_mime,
                           ps.status,
                           ps.notes,
                           ps.submitted_at,
                           ps.verified_at,
                           ps.verified_by,
                           s.student_number,
                           a.first_name,
                           a.middle_name,
                           a.surname,
                           a.suffix,
                           a.admission_type,
                           c.code AS course_code,
                           sec.section_code
                    FROM enr_payment_submissions ps
                    JOIN enr_students s       ON s.student_id   = ps.student_id
                    JOIN enr_applicants a     ON a.applicant_id = s.applicant_id
                    LEFT JOIN rgr_courses c   ON c.id           = s.course_id
                    LEFT JOIN cc_sections sec ON sec.id         = s.section_id";

            $params = [];
            if ($statusFilter && in_array($statusFilter, ['pending', 'verified', 'rejected'], true)) {
                $sql .= " WHERE ps.status = ?";
                $params[] = $statusFilter;
            }

            $sql .= " ORDER BY ps.submitted_at DESC, ps.id DESC";

            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error in getAllPaymentSubmissions: " . $e->getMessage());
            return [];
        }
    }

    public function getPaymentStatusCounts() {
        try {
            $sql = "SELECT
                        COUNT(*) as total,
                        SUM(CASE WHEN status = 'pending'  THEN 1 ELSE 0 END) as pending,
                        SUM(CASE WHEN status = 'verified' THEN 1 ELSE 0 END) as verified,
                        SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
                    FROM enr_payment_submissions";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute();
            $row = $stmt->fetch();
            return [
                'total'    => (int) ($row['total']    ?? 0),
                'pending'  => (int) ($row['pending']  ?? 0),
                'verified' => (int) ($row['verified'] ?? 0),
                'rejected' => (int) ($row['rejected'] ?? 0),
            ];
        } catch (Exception $e) {
            error_log("Error in getPaymentStatusCounts: " . $e->getMessage());
            return ['total' => 0, 'pending' => 0, 'verified' => 0, 'rejected' => 0];
        }
    }

    public function updatePaymentStatus($submissionId, $newStatus, $verifierId) {
        if (!in_array($newStatus, ['verified', 'rejected'], true)) {
            return ['success' => false, 'message' => 'Invalid status: ' . $newStatus];
        }
        try {
            $sql = "UPDATE enr_payment_submissions
                    SET status = ?,
                        verified_at = NOW(),
                        verified_by = ?
                    WHERE id = ?";
            $stmt = $this->connection->prepare($sql);
            $ok = $stmt->execute([$newStatus, (int) $verifierId, (int) $submissionId]);

            return $ok
                ? ['success' => true,  'message' => "Payment #{$submissionId} marked as {$newStatus}."]
                : ['success' => false, 'message' => 'Failed to update payment status.'];
        } catch (Exception $e) {
            error_log("Error in updatePaymentStatus: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getPaymentSubmissionFile($submissionId) {
        try {
            $sql  = "SELECT id, file_data, file_name, file_mime
                     FROM enr_payment_submissions
                     WHERE id = ? LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute([(int) $submissionId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("Error in getPaymentSubmissionFile: " . $e->getMessage());
            return null;
        }
    }

    public function getStudentForPaymentEmail($studentId) {
        try {
            $sql  = "SELECT s.student_id,
                            s.student_number,
                            a.first_name,
                            a.middle_name,
                            a.surname,
                            a.suffix,
                            a.email,
                            c.code AS course_code
                     FROM enr_students s
                     JOIN enr_applicants a ON a.applicant_id = s.applicant_id
                     LEFT JOIN rgr_courses c ON c.id = s.course_id
                     WHERE s.student_id = :student_id
                     LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['student_id' => (int) $studentId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("getStudentForPaymentEmail: " . $e->getMessage());
            return null;
        }
    }

    /* ============================================================
       ⭐ PAYMENT GATE HELPERS
    ============================================================ */

    public function hasPendingPayment($studentId) {
        try {
            $sql  = "SELECT COUNT(*) AS c
                     FROM enr_payment_submissions
                     WHERE student_id = :student_id
                       AND status = 'pending'";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['student_id' => (int) $studentId]);
            $row = $stmt->fetch();
            return ((int) ($row['c'] ?? 0)) > 0;
        } catch (Exception $e) {
            error_log("hasPendingPayment: " . $e->getMessage());
            return false;
        }
    }

    public function hasVerifiedPayment($studentId) {
        try {
            $sql  = "SELECT COUNT(*) AS c
                     FROM enr_payment_submissions
                     WHERE student_id = :student_id
                       AND status = 'verified'";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['student_id' => (int) $studentId]);
            $row = $stmt->fetch();
            return ((int) ($row['c'] ?? 0)) > 0;
        } catch (Exception $e) {
            error_log("hasVerifiedPayment: " . $e->getMessage());
            return false;
        }
    }

    public function hasVerifiedPaymentForCurrentTerm($studentId) {
        try {
            $studentId = (int) $studentId;
            if ($studentId <= 0) return false;

            $termStmt = $this->connection->prepare(
                "SELECT
                    (SELECT id FROM rgr_school_years WHERE is_active = 1 ORDER BY id DESC LIMIT 1) AS sy_id,
                    (SELECT id FROM rgr_semesters    WHERE is_active = 1 ORDER BY id DESC LIMIT 1) AS sem_id"
            );
            $termStmt->execute();
            $term = $termStmt->fetch(PDO::FETCH_ASSOC);

            $syId  = (int) ($term['sy_id']  ?? 0);
            $semId = (int) ($term['sem_id'] ?? 0);

            if ($syId <= 0 || $semId <= 0) {
                error_log("hasVerifiedPaymentForCurrentTerm: no active SY/semester found");
                return false;
            }

            $stmt = $this->connection->prepare(
                "SELECT COUNT(*) AS c
                 FROM enr_payment_submissions
                 WHERE student_id    = :student_id
                   AND status        = 'verified'
                   AND school_year_id = :sy_id
                   AND semester_id    = :sem_id"
            );
            $stmt->execute([
                'student_id' => $studentId,
                'sy_id'      => $syId,
                'sem_id'     => $semId,
            ]);
            $row = $stmt->fetch();
            return ((int) ($row['c'] ?? 0)) > 0;
        } catch (Exception $e) {
            error_log("hasVerifiedPaymentForCurrentTerm: " . $e->getMessage());
            return false;
        }
    }

    public function canEnrollBasedOnPayment($studentId) {
        try {
            $studentId = (int) $studentId;

            if ($this->hasPendingPayment($studentId)) {
                return [
                    'can_enroll' => false,
                    'reason'     => 'May pending payment — kailangan munang ma-verify.',
                ];
            }

            if ($this->hasVerifiedPaymentForCurrentTerm($studentId)) {
                return ['can_enroll' => true, 'reason' => 'Verified payment on file.'];
            }

            return [
                'can_enroll' => false,
                'reason'     => 'Wala pang verified payment para sa kasalukuyang semester.',
            ];
        } catch (Exception $e) {
            error_log("canEnrollBasedOnPayment: " . $e->getMessage());
            return ['can_enroll' => false, 'reason' => 'Error checking payment status.'];
        }
    }

    public function getLatestPaymentStatus($studentId) {
        try {
            $sql  = "SELECT id, status, submitted_at, verified_at
                     FROM enr_payment_submissions
                     WHERE student_id = :student_id
                     ORDER BY submitted_at DESC, id DESC
                     LIMIT 1";
            $stmt = $this->connection->prepare($sql);
            $stmt->execute(['student_id' => (int) $studentId]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("getLatestPaymentStatus: " . $e->getMessage());
            return null;
        }
    }
}
