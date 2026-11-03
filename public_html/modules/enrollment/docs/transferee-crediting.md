# Enrollment: reading transferee credits

Regis owns subject evaluation, equivalency decisions, and credit approval. Enrollment only reads approved credits; it has no evaluation form or approve/reject actions and does not write either Registrar table.

Credits are linked through:

`rgr_student_subject_evaluations.applicant_id -> enr_applicants.applicant_id`

Enrollment reads credits by applicant ID before conversion. After conversion, it resolves `enr_students.applicant_id` to the same credits. The evaluation's optional `student_id` can remain NULL; no backfill or schema migration is required.

An approved evaluation with a matching curriculum subject is recognized directly. A separate `rgr_student_credited_subjects` row is not required: Regis can save approved evaluations without creating that row. Pending and rejected evaluations never count, even if a separate credit row exists. Credits must belong to the applicant/student's course, including older curricula. Enrollment excludes credited subjects from automatic loading, recognizes them as completed for prerequisites, and prevents repeat enrollment.

For transferee applicants with approved credits, placement is the earliest term in the active course curriculum that still contains an uncredited subject. A fully credited first semester advances to the second semester; a fully credited year advances to the next curriculum year level. Partial credits do not skip unfinished terms. An absent matching section does not fall back to a completed semester. If the entire curriculum is credited, no section is offered. Conversion rechecks placement so a stale selection cannot assign the wrong term. This does not change the active calendar school year or rewrite existing enrollments; other enrollment requirements retain their existing rules.

The Enrollment Requirements page continues to manage its existing requirements checklist. Subject evaluation and approval are performed in Regis.

Validation:

```text
C:/xampp/php/php.exe tests/transfer_credit_regression.php
node tests/applicant_credit_selection.js
C:/xampp/php/php.exe tests/pages_smoke.php
```

The transfer regression test uses a disposable database and synthetic Regis decisions matching the supplied schema. It checks credit ownership, recognition before and after conversion, prerequisites, repeat-enrollment prevention, and preservation of Registrar records. The JavaScript test verifies automatic selection excludes credited subjects even when they have no schedule.
