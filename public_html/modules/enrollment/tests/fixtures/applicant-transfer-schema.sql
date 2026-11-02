-- Structure from the supplied Registrar dumps. No student_id on credited subjects.
CREATE TABLE rgr_student_subject_evaluations (
    id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    applicant_id INT(11) DEFAULT NULL,
    requirement_file_id INT(11) NOT NULL,
    student_id INT(11) DEFAULT NULL,
    curriculum_subject_id INT(10) NOT NULL,
    previous_subject_code VARCHAR(50) DEFAULT NULL,
    previous_subject_name VARCHAR(255) NOT NULL,
    previous_grade VARCHAR(20) NOT NULL,
    previous_units DECIMAL(5,2) NOT NULL,
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    evaluated_by INT(11) DEFAULT NULL,
    evaluated_at DATETIME DEFAULT NULL,
    remarks TEXT DEFAULT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    KEY idx_requirement_file (requirement_file_id),
    KEY idx_student (student_id),
    KEY idx_curriculum_subject (curriculum_subject_id),
    CONSTRAINT fk_eval_curriculum FOREIGN KEY (curriculum_subject_id) REFERENCES rgr_curriculum_subjects (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rgr_student_credited_subjects (
    id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
    curriculum_subject_id INT(10) NOT NULL,
    evaluation_id INT(10) NOT NULL,
    credited_by INT(11) DEFAULT NULL,
    credited_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    remarks TEXT DEFAULT NULL,
    KEY idx_evaluation (evaluation_id),
    KEY fk_credit_curriculum (curriculum_subject_id),
    CONSTRAINT fk_credit_curriculum FOREIGN KEY (curriculum_subject_id) REFERENCES rgr_curriculum_subjects (id),
    CONSTRAINT fk_credit_evaluation FOREIGN KEY (evaluation_id) REFERENCES rgr_student_subject_evaluations (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
