# Enrollment: reading transferee credits

Regis owns subject evaluation, equivalency decisions, and credit approval. Enrollment only reads approved credits; it has no evaluation form or approve/reject actions and does not write either Registrar table.

Credits are linked through:

`rgr_student_credited_subjects.evaluation_id -> rgr_student_subject_evaluations.id -> applicant_id`

Enrollment reads credits by applicant ID before conversion. After conversion, it resolves `enr_students.applicant_id` to the same credits. The evaluation's optional `student_id` can remain NULL; no backfill or schema migration is required.

Only an approved evaluation with a matching credit record and curriculum subject is recognized. Credits must belong to the applicant/student's course, including older curricula. Enrollment excludes credited subjects from automatic loading, recognizes them as completed for prerequisites, and prevents repeat enrollment. Year placement and other enrollment requirements retain their existing rules.

The Enrollment Requirements page continues to manage its existing requirements checklist. Subject evaluation and approval are performed in Regis.

Validation:

```text
C:/xampp/php/php.exe tests/transfer_credit_regression.php
node tests/applicant_credit_selection.js
C:/xampp/php/php.exe tests/pages_smoke.php
```

The transfer regression test uses a disposable database and synthetic Regis decisions matching the supplied schema. It checks credit ownership, recognition before and after conversion, prerequisites, repeat-enrollment prevention, and preservation of Registrar records. The JavaScript test verifies automatic selection excludes credited subjects even when they have no schedule.
