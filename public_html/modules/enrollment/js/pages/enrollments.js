const pageData = JSON.parse(document.getElementById('page-data').dataset.values);

// ============================================================
// GLOBALS
// ============================================================
var currentApplicantId = null;
var currentCourseId    = null;
var baseUrl = pageData.apiBaseUrl;
console.log('Base URL:', baseUrl);

// ============================================================
// FORM VALIDATION
// ============================================================

function validateEnrollmentForm() {
    var scheduleIds = document.getElementById('scheduleIdsInput').value.trim();
    var subjectItems = document.querySelectorAll('#subjectsList .subject-item');
    var blockedCount = document.querySelectorAll('#subjectsList .subject-item.blocked').length;

    if (scheduleIds === '') {
        if (blockedCount > 0) {
            alert('⚠️ Walang subject na pwedeng i-enroll.\n\n'
                + 'May ' + blockedCount + ' blocked subject(s) — kailangan munang maipasa ang mga prerequisites.');
        } else if (subjectItems.length === 0) {
            alert('⚠️ Walang curriculum subjects na na-load. I-check ang curriculum setup para sa course na ito.');
        } else {
            alert('⚠️ Walang subjects na napili para sa enrollment.');
        }
        return false;
    }

    var mandatoryUnchecked = document.querySelectorAll('#requirementsList .req-item input[data-mandatory="1"]:not(:checked)');
    if (mandatoryUnchecked.length > 0) {
        alert('⚠️ Please check ALL mandatory requirements (*).\n\n' + mandatoryUnchecked.length + ' mandatory requirement(s) still unchecked.');
        return false;
    }

    var reqCheckboxes = document.querySelectorAll('#requirementsList input[type="checkbox"]');
    if (reqCheckboxes.length > 0) {
        var checkedCount = document.querySelectorAll('#requirementsList input[type="checkbox"]:checked').length;
        if (checkedCount === 0) {
            alert('⚠️ Please check at least one requirement before enrolling.');
            return false;
        }
    }

    return true;
}

function validateProgressionForm() {
    var scheduleIds = document.getElementById('progressionScheduleIds').value.trim();
    var blockedCount = document.querySelectorAll('#progressionSubjectsList .subject-item.blocked').length;

    if (scheduleIds === '') {
        if (blockedCount > 0) {
            alert('⚠️ Walang subject na pwedeng i-enroll.\n\n'
                + 'May ' + blockedCount + ' blocked subject(s) — kailangan munang maipasa ang mga prerequisites.');
        } else {
            alert('⚠️ Walang subjects na napili para sa enrollment.');
        }
        return false;
    }

    return true;
}

// ============================================================
// SELECT ALL HELPERS
// ============================================================

function updateSelectAllState(containerId, selectAllId, checkboxSelector) {
    var selectAll = document.getElementById(selectAllId);
    if (!selectAll) return;
    var checkboxes   = document.querySelectorAll(containerId + ' ' + checkboxSelector + ':not(:disabled)');
    var checkedBoxes = document.querySelectorAll(containerId + ' ' + checkboxSelector + ':not(:disabled):checked');

    if (checkboxes.length === 0) {
        selectAll.checked = false; selectAll.disabled = true; selectAll.indeterminate = false;
    } else if (checkedBoxes.length === checkboxes.length) {
        selectAll.checked = true; selectAll.disabled = false; selectAll.indeterminate = false;
    } else if (checkedBoxes.length > 0) {
        selectAll.checked = false; selectAll.disabled = false; selectAll.indeterminate = true;
    } else {
        selectAll.checked = false; selectAll.disabled = false; selectAll.indeterminate = false;
    }
}

function toggleSelectAllRequirements(cb) {
    document.querySelectorAll('#requirementsList input[type="checkbox"]').forEach(function (x) { x.checked = cb.checked; });
    updateRequirementsCount();
}

function toggleCategoryRequirements(categoryCheckbox, categoryName) {
    document.querySelectorAll('#requirementsList .req-category[data-category="' + categoryName + '"] input[type="checkbox"]').forEach(function (cb) {
        cb.checked = categoryCheckbox.checked;
    });
    updateRequirementsCount();
}

// ============================================================
// ARCHIVE
// ============================================================

function openArchiveModal(studentId, studentName) {
    document.getElementById('archiveStudentId').value = studentId;
    document.getElementById('archiveStudentName').textContent = studentName;
    document.getElementById('archiveReason').value = 'Dropped';
    document.getElementById('archiveModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeArchiveModal() {
    document.getElementById('archiveModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ============================================================
// NEW STUDENT ENROLLMENT MODAL
// ============================================================

function openEnrollmentModal(applicantId, name, course, admissionType, courseId) {
    currentApplicantId = applicantId;
    currentCourseId    = courseId;

    document.getElementById('applicantId').value              = applicantId;
    document.getElementById('applicantNameDisplay').textContent  = name;
    document.getElementById('applicantCourseDisplay').textContent = course;

    var typeLabel = (admissionType || '').charAt(0).toUpperCase()
        + (admissionType || '').slice(1).replace('_', ' ');
    document.getElementById('applicantTypeDisplay').textContent = typeLabel;
    document.getElementById('courseId').value = courseId;

    document.getElementById('sectionSelect').innerHTML = '<option value="">Loading sections...</option>';
    document.getElementById('sectionInfo').textContent = 'Loading sections...';
    document.getElementById('subjectsSection').style.display = 'none';
    document.getElementById('subjectsList').innerHTML = '<div class="loading-text">Select a section first...</div>';
    document.getElementById('selectedSubjectsCount').textContent = '0';
    document.getElementById('scheduleIdsInput').value = '';
    document.getElementById('scheduleInfo').textContent = '';

    var sr = document.getElementById('selectAllRequirements');
    if (sr) { sr.checked = false; sr.disabled = true; sr.indeterminate = false; }

    document.getElementById('enrollmentModal').style.display = 'block';
    document.body.style.overflow = 'hidden';

    var effectiveType = (admissionType && admissionType.trim() !== '') ? admissionType : 'freshmen';

    loadAvailableSections(courseId, 1, 1);
    loadRequirements(effectiveType);
}

function closeEnrollmentModal() {
    document.getElementById('enrollmentModal').style.display = 'none';
    document.body.style.overflow = 'auto';
    currentApplicantId = null;
    currentCourseId = null;
}

function loadAvailableSections(courseId, yearLevel, semester) {
    var select = document.getElementById('sectionSelect');
    var info   = document.getElementById('sectionInfo');
    select.innerHTML = '<option value="">Loading sections...</option>';
    info.textContent = 'Loading sections...';

    var url = baseUrl + '/api/get_sections_by_course.php'
            + '?course_id='       + encodeURIComponent(courseId)
            + '&year_level='      + encodeURIComponent(yearLevel || 1)
            + '&semester='        + encodeURIComponent(semester || 1)
            + '&only_available=1'
            + '&applicant_id=' + encodeURIComponent(currentApplicantId);

    fetch(url)
        .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function (data) {
            select.innerHTML = '<option value="">Choose a section</option>';

            yearLevel = Number(data.year_level || yearLevel || 1);
            semester = Number(data.semester || semester || 1);
            if (data.placement && data.placement.curriculum_completed) {
                select.innerHTML = '<option value="">All subjects credited</option>';
                info.textContent = data.message;
                document.getElementById('subjectsSection').style.display = 'none';
                return;
            }

            if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                var yearText = (yearLevel === 1) ? '1st Year'
                             : (yearLevel === 2) ? '2nd Year'
                             : (yearLevel === 3) ? '3rd Year' : '4th Year';
                var semText  = (semester === 1) ? '1st Semester' : '2nd Semester';

                data.data.forEach(function (section) {
                    var opt = document.createElement('option');
                    opt.value = section.id;
                    var availText = (section.available_slots !== undefined)
                        ? ' (' + section.available_slots + ' slots available)'
                        : '';
                    opt.textContent = (section.section_code || '')
                        + ' - ' + (section.grade_level || 'N/A')
                        + ' ' + (section.semester || '')
                        + availText;
                    select.appendChild(opt);
                });

                info.innerHTML = '✅ Found ' + data.data.length + ' section(s) for <strong>'
                    + yearText + ' - ' + semText + '</strong>. Auto-selected: <strong>'
                    + data.data[0].section_code + '</strong>';
                info.style.color = '#2a5c9e';

                select.value = data.data[0].id;
                loadSubjectsForSection(data.data[0].id);
            } else {
                select.innerHTML = '<option value="">No sections available</option>';
                info.innerHTML = '⚠️ Walang section na may bakante.';
                if (data.placement) {
                    info.textContent = 'Walang available section para sa '
                        + data.placement.grade_level + ' - ' + data.placement.semester_name + '.';
                }
                info.style.color = '#0f2a4e';
                document.getElementById('subjectsSection').style.display = 'none';
            }
        })
        .catch(function (err) {
            console.error('Error loading sections:', err);
            select.innerHTML = '<option value="">Error loading sections</option>';
            info.textContent = 'Could not load sections. Please refresh and try again.';
            info.style.color = '#0f2a4e';
        });
}

function loadSubjectsForSection(sectionId) {
    if (!sectionId) {
        document.getElementById('subjectsSection').style.display = 'none';
        return;
    }

    var applicantId = document.getElementById('applicantId').value;
    if (!applicantId) {
        document.getElementById('subjectsSection').style.display = 'none';
        return;
    }

    var container     = document.getElementById('subjectsList');
    var section       = document.getElementById('subjectsSection');
    var countEl       = document.getElementById('selectedSubjectsCount');
    var scheduleInput = document.getElementById('scheduleIdsInput');
    var scheduleInfo  = document.getElementById('scheduleInfo');

    container.innerHTML = '<div class="loading-text">Loading curriculum subjects...</div>';
    section.style.display = 'block';
    countEl.textContent = '0';
    scheduleInput.value = '';
    scheduleInfo.textContent = '';

    var url = baseUrl + '/api/get_subjects_for_section.php?section_id='
        + encodeURIComponent(sectionId) + '&applicant_id=' + encodeURIComponent(applicantId);

    fetch(url)
        .then(function (r) { if (!r.ok) throw new Error('HTTP ' + r.status + ': ' + r.statusText); return r.json(); })
        .then(function (data) {
            if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                renderFreshmenSubjectsAutoSelected(data.data);
            } else {
                container.innerHTML = '<div class="alert alert-warning">'
                    + '⚠️ Walang curriculum subjects na nahanap para sa section na ito.<br>'
                    + 'I-check ang curriculum setup (course + year level + semester).'
                    + '</div>';
                scheduleInput.value = '';
                scheduleInfo.textContent = '';
            }
        })
        .catch(function (err) {
            console.error('Error loading subjects:', err);
            container.innerHTML = '<div class="alert alert-danger">Error loading subjects: ' + err.message + '</div>';
            scheduleInput.value = '';
            scheduleInfo.textContent = '';
        });
}

function renderFreshmenSubjectsAutoSelected(subjects) {
    var container = document.getElementById('subjectsList');
    if (!container) return;

    var html = '';
    var scheduleIds = [];
    var availableCount = 0;
    var missingScheduleCount = 0;
    var alreadyEnrolledCount = 0;
    var creditedCount = 0;

    subjects.forEach(function (subject) {
        var hasSchedule = (subject.has_schedule === true || subject.has_schedule === 1);
        var scheduleId  = subject.representative_schedule_id || null;
        var isEnrolled  = subject.is_enrolled === true;

        if (subject.is_credited === true) {
            creditedCount++;
            return;
        }

        if (isEnrolled) {
            alreadyEnrolledCount++;
            return;
        }

        if (hasSchedule && scheduleId) {
            availableCount++;
            scheduleIds.push(scheduleId);
        } else {
            missingScheduleCount++;
        }
    });

    html += '<div class="subject-counts">';
    if (creditedCount > 0) {
        html += '<span class="count-retake">Credited from previous school: ' + creditedCount + '</span>';
    }
    html += '<span class="count-available">🟢 Available (auto-included): ' + availableCount + '</span>';
    if (missingScheduleCount > 0) {
        html += '<span class="count-blocked">⚠️ No schedule: ' + missingScheduleCount + '</span>';
    }
    if (alreadyEnrolledCount > 0) {
        html += '<span class="count-retake">✅ Already enrolled: ' + alreadyEnrolledCount + '</span>';
    }
    html += '</div>';

    if (availableCount > 0 && missingScheduleCount === 0) {
        html += '<div class="alert alert-info subject-info">'
             + '✅ <strong>Lahat ng ' + availableCount + ' curriculum subject(s) ay awtomatikong isasama.</strong> '
             + 'I-click lang ang <strong>"Enroll Student"</strong> para magpatuloy.'
             + '</div>';
    }

    if (missingScheduleCount > 0) {
        html += '<div class="alert alert-warning">'
             + '⚠️ <strong>' + missingScheduleCount + ' subject(s)</strong> ang walang schedule sa section na ito. '
             + 'Pumili ng ibang section o mag-assign muna ng schedules sa '
             + '<a href="?page=schedules">Schedules module</a>.'
             + '</div>';

        html += '<h4 class="subject-group-title">';
        html += '⚠️ Subjects na Walang Schedule (' + missingScheduleCount + ') — hindi maisasama';
        html += '</h4>';

        subjects.forEach(function (subject) {
            if (subject.is_enrolled || subject.is_credited) return;
            var hasSchedule = (subject.has_schedule === true || subject.has_schedule === 1);
            var scheduleId  = subject.representative_schedule_id || null;
            if (hasSchedule && scheduleId) return;

            html += '<div class="subject-item blocked">';
            html += '<input type="checkbox" disabled>';
            html += '<span class="subject-code">' + (subject.subject_code || 'N/A') + '</span>';
            html += '<span class="subject-name">' + (subject.subject_name || 'Unknown') + '</span>';
            html += '<span class="subject-units">' + (subject.units || 0) + ' units</span>';
            html += '<span class="subject-status-badge blocked">⚠️ No schedule</span>';
            html += '</div>';
        });
    }

    if (availableCount === 0 && missingScheduleCount === 0 && alreadyEnrolledCount === 0 && creditedCount === 0) {
        html += '<div class="alert alert-info">'
             + 'Walang subjects na available para i-enroll sa section na ito.'
             + '</div>';
    }

    container.innerHTML = html;

    document.getElementById('selectedSubjectsCount').textContent = scheduleIds.length;
    document.getElementById('scheduleIdsInput').value = scheduleIds.join(',');

    var scheduleInfoEl = document.getElementById('scheduleInfo');
    if (scheduleIds.length > 0) {
        scheduleInfoEl.textContent = '📘 ' + scheduleIds.length + ' curriculum subject(s) — all auto-included';
        scheduleInfoEl.style.color = '#2a5c9e';
    } else if (missingScheduleCount > 0) {
        scheduleInfoEl.textContent = '⚠️ ' + missingScheduleCount + ' subject(s) ang walang schedule. Pumili ng ibang section.';
        scheduleInfoEl.style.color = '#0f2a4e';
    } else {
        scheduleInfoEl.textContent = '⚠️ Walang subjects na pwedeng i-enroll.';
        scheduleInfoEl.style.color = '#0f2a4e';
    }
}

function updateSubjectCount() {
    var count = document.querySelectorAll('#subjectsList input[type="checkbox"]:checked').length;
    document.getElementById('selectedSubjectsCount').textContent = count;
}

// ============================================================
// PROGRESSION ENROLLMENT
// ============================================================

function openProgressionEnrollment(studentId, studentName, yearLevel, semester, schoolYear) {
    document.getElementById('progressionStudentId').value = studentId;
    document.getElementById('progressionStudentName').textContent = studentName;

    var semesterName = semester === 1 ? '1st Semester' : '2nd Semester';
    var yearText = yearLevel === 1 ? '1st Year'
                 : yearLevel === 2 ? '2nd Year'
                 : yearLevel === 3 ? '3rd Year' : '4th Year';

    document.getElementById('progressionNextLevel').textContent = yearText + ' - ' + semesterName;
    document.getElementById('progressionSchoolYear').textContent = schoolYear || pageData.currentSchoolYear;
    document.getElementById('progressionSchoolYearInput').value = schoolYear || pageData.currentSchoolYear;

    if (schoolYear && schoolYear !== pageData.currentSchoolYear) {
        document.getElementById('progressionSchoolYearHint').textContent = '📅 New Academic Year: ' + schoolYear;
        document.getElementById('progressionSchoolYearHint').style.color = '#0f2a4e';
    } else {
        document.getElementById('progressionSchoolYearHint').textContent = 'Same Academic Year: ' + pageData.currentSchoolYear;
        document.getElementById('progressionSchoolYearHint').style.color = '#2a5c9e';
    }

    document.getElementById('progressionSectionSelect').innerHTML = '<option value="">Loading sections...</option>';
    document.getElementById('progressionSectionInfo').textContent = '';
    document.getElementById('progressionSubjectsSection').style.display = 'none';
    document.getElementById('progressionSubjectsList').innerHTML = '';
    document.getElementById('progressionSelectedCount').textContent = '0';
    document.getElementById('progressionScheduleIds').value = '';

    document.getElementById('progressionModal').style.display = 'block';
    document.body.style.overflow = 'hidden';

    loadProgressionSections(studentId, yearLevel, semester);
}

function closeProgressionModal() {
    document.getElementById('progressionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function loadProgressionSections(studentId, yearLevel, semester) {
    var select = document.getElementById('progressionSectionSelect');
    select.innerHTML = '<option value="">Loading sections...</option>';

    fetch(baseUrl + '/api/get_student_progression.php?student_id=' + encodeURIComponent(studentId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data.success || !data.data || !data.data.student) {
                select.innerHTML = '<option value="">Student data not found</option>';
                return;
            }

            var studentCourseId = data.data.student.course_id;

            var url = baseUrl + '/api/get_sections_by_course.php'
                    + '?course_id='       + encodeURIComponent(studentCourseId)
                    + '&year_level='      + encodeURIComponent(yearLevel)
                    + '&semester='        + encodeURIComponent(semester)
                    + '&only_available=1';

            return fetch(url)
                .then(function (r) { return r.json(); })
                .then(function (sectionData) {
                    select.innerHTML = '<option value="">Choose a section</option>';
                    var info = document.getElementById('progressionSectionInfo');

                    if (sectionData.success && Array.isArray(sectionData.data) && sectionData.data.length > 0) {
                        var semText = semester === 1 ? '1st Semester' : '2nd Semester';

                        sectionData.data.forEach(function (section) {
                            var opt = document.createElement('option');
                            opt.value = section.id;
                            var slots = (section.available_slots !== undefined)
                                ? ' [' + section.available_slots + '/40 slots]'
                                : '';
                            opt.textContent = section.section_code
                                + ' - ' + section.grade_level
                                + ' ' + (section.semester || semText)
                                + slots;
                            select.appendChild(opt);
                        });

                        info.textContent = '✅ ' + sectionData.data.length + ' section(s) found.';
                        info.style.color = '#2a5c9e';

                        select.value = sectionData.data[0].id;
                        loadProgressionSubjects(sectionData.data[0].id);
                    } else {
                        select.innerHTML = '<option value="">No sections with available slots</option>';
                        info.innerHTML = '⚠️ Walang section na may bakante.';
                        info.style.color = '#0f2a4e';
                        document.getElementById('progressionSubjectsSection').style.display = 'none';
                    }
                });
        })
        .catch(function (err) {
            console.error('Error:', err);
            select.innerHTML = '<option value="">Error loading sections</option>';
        });
}

function loadProgressionSubjects(sectionId) {
    if (!sectionId) {
        document.getElementById('progressionSubjectsSection').style.display = 'none';
        return;
    }

    var studentId = document.getElementById('progressionStudentId').value;
    var container = document.getElementById('progressionSubjectsList');
    container.innerHTML = '<div class="loading-text">Loading subjects...</div>';
    document.getElementById('progressionSubjectsSection').style.display = 'block';
    document.getElementById('progressionSelectedCount').textContent = '0';
    document.getElementById('progressionScheduleIds').value = '';

    var url = baseUrl + '/api/get_available_subjects_with_status.php?student_id='
        + encodeURIComponent(studentId) + '&section_id=' + encodeURIComponent(sectionId);

    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.data) {
                renderProgressionSubjectsAutoSelected(data.data);
            } else {
                container.innerHTML = '<div class="alert alert-info">No subjects available.</div>';
            }
        })
        .catch(function (err) {
            console.error('Error:', err);
            container.innerHTML = '<div class="alert alert-danger">Error loading subjects.</div>';
        });
}

function renderProgressionSubjectsAutoSelected(data) {
    var container   = document.getElementById('progressionSubjectsList');
    var allSubjects = data.all_subjects || [];
    var scheduleIds = [];
    var html = '';

    var visibleSubjects = allSubjects.filter(function (s) {
        var st = (s.status || '').toUpperCase();
        return st !== 'COMPLETED' && st !== 'PASSED';
    });

    var availableSubjects = visibleSubjects.filter(function (s) {
        return (s.status || '').toUpperCase() === 'AVAILABLE';
    });
    var retakeSubjects    = visibleSubjects.filter(function (s) {
        return (s.status || '').toUpperCase() === 'RETAKE';
    });
    var blockedSubjects   = visibleSubjects.filter(function (s) {
        return (s.status || '').toUpperCase() === 'BLOCKED';
    });

    availableSubjects.forEach(function (s) {
        if (s.representative_schedule_id) scheduleIds.push(s.representative_schedule_id);
    });
    retakeSubjects.forEach(function (s) {
        if (s.representative_schedule_id) scheduleIds.push(s.representative_schedule_id);
    });

    html += '<div class="subject-counts">';
    html += '<span class="count-available">🟢 Available (auto-included): ' + availableSubjects.length + '</span>';
    html += '<span class="count-retake">🟡 Retake: ' + retakeSubjects.length + '</span>';
    html += '<span class="count-blocked">🔴 Blocked: ' + blockedSubjects.length + '</span>';
    html += '</div>';

    if (retakeSubjects.length === 0 && blockedSubjects.length === 0) {
        if (availableSubjects.length > 0) {
            html += '<div class="alert alert-info subject-info">'
                 + '✅ <strong>Lahat ng ' + availableSubjects.length + ' available subject(s) ay awtomatikong isasama.</strong> '
                 + 'Walang retake o blocked subjects para sa semester na ito. '
                 + 'I-click lang ang <strong>"Enroll Next Semester"</strong> para magpatuloy.'
                 + '</div>';
        } else if (visibleSubjects.length === 0) {
            html += '<div class="alert alert-info">'
                 + '🎓 Lahat ng curriculum subjects ay tapos na. '
                 + 'Wala nang natitirang subject para sa semester na ito.'
                 + '</div>';
        }
        container.innerHTML = html;
        document.getElementById('progressionSelectedCount').textContent = scheduleIds.length;
        document.getElementById('progressionScheduleIds').value = scheduleIds.join(',');
        return;
    }

    if (retakeSubjects.length > 0) {
        html += '<div class="subject-group">';
        html += '<h4 class="retake-section-header">🟡 Retake Subjects (' + retakeSubjects.length + ') — kailangang i-retake</h4>';
        html += '<p class="retake-note">Failed previously — required to retake. Auto-checked.</p>';

        retakeSubjects.forEach(function (subject) {
            var scheduleId = subject.representative_schedule_id || null;
            var canCheck   = scheduleId && scheduleId > 0;

            html += '<div class="subject-item retake">';
            html += '<input type="checkbox" name="schedule_ids_array[]" value="' + (scheduleId || '') + '" '
                 +  (canCheck ? 'checked disabled' : 'disabled') + '>';
            html += '<span class="subject-code">' + (subject.subject_code || 'N/A') + '</span>';
            html += '<span class="subject-name">' + (subject.subject_name || 'Unknown') + '</span>';
            html += '<span class="subject-units">' + (subject.units || 0) + ' units</span>';
            html += '<span class="subject-status-badge retake">🔄 Retake</span>';
            if (subject.previous_grade) {
                html += '<span class="subject-previous-grade">Prev grade: ' + subject.previous_grade + '</span>';
            }
            html += '</div>';
        });
        html += '</div>';
    }

    if (blockedSubjects.length > 0) {
        html += '<div class="subject-group">';
        html += '<h4 class="subject-group-title">';
        html += '🔴 Blocked Subjects (' + blockedSubjects.length + ') — hindi pwedeng i-enroll';
        html += '</h4>';
        html += '<p class="retake-note">May hindi pa natapos na prerequisite. Kailangan munang maipasa ang prereq bago ito i-enroll.</p>';

        blockedSubjects.forEach(function (subject) {
            html += '<div class="subject-item blocked">';
            html += '<input type="checkbox" disabled>';
            html += '<span class="subject-code">' + (subject.subject_code || 'N/A') + '</span>';
            html += '<span class="subject-name">' + (subject.subject_name || 'Unknown') + '</span>';
            html += '<span class="subject-units">' + (subject.units || 0) + ' units</span>';
            html += '<span class="subject-status-badge blocked">🚫 Blocked</span>';
            if (subject.message) {
                html += '<span class="subject-message">' + subject.message + '</span>';
            }
            html += '</div>';
        });
        html += '</div>';
    }

    container.innerHTML = html;

    document.getElementById('progressionSelectedCount').textContent = scheduleIds.length;
    document.getElementById('progressionScheduleIds').value = scheduleIds.join(',');
}

// ============================================================
// SCHEDULE VIEW
// ============================================================

function viewSchedule(studentId) {
    var modal   = document.getElementById('scheduleModal');
    var content = document.getElementById('scheduleContent');
    content.innerHTML = '<div class="loading-text">Loading schedule...</div>';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    fetch(baseUrl + '/api/get_student_schedule.php?student_id=' + encodeURIComponent(studentId))
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.data && data.data.grouped) {
                renderSchedule(data.data.grouped);
            } else {
                content.innerHTML = '<div class="alert alert-info">No schedule found.</div>';
            }
        })
        .catch(function (err) {
            console.error('Error:', err);
            content.innerHTML = '<div class="alert alert-danger">Error loading schedule.</div>';
        });
}

function renderSchedule(groupedSchedule) {
    var content = document.getElementById('scheduleContent');
    var html    = '';
    var total   = 0;
    var dayOrder = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'];

    dayOrder.forEach(function (day) {
        if (!groupedSchedule[day] || groupedSchedule[day].length === 0) return;
        html += '<div class="schedule-day"><h3>' + day + '</h3>';
        groupedSchedule[day].sort(function (a, b) {
            return (a.start_time || '00:00:00').localeCompare(b.start_time || '00:00:00');
        });
        groupedSchedule[day].forEach(function (item) {
            total++;
            html += '<div class="schedule-item">';
            html += '<span class="time">' + String(item.start_time || '--:--').substring(0, 5) + ' - '
                 +  String(item.end_time || '--:--').substring(0, 5) + '</span>';
            html += '<span class="subject"><strong>' + (item.subject_code || '') + '</strong> - ' + (item.subject_name || '') + '</span>';
            if (item.faculty_name) html += '<span class="faculty">👨‍🏫 ' + item.faculty_name + '</span>';
            if (item.room_code)    html += '<span class="room">🏫 ' + item.room_code + '</span>';
            html += '</div>';
        });
        html += '</div>';
    });

    if (total === 0) html = '<div class="no-schedule">📅 No scheduled subjects found.</div>';
    content.innerHTML = html;
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ============================================================
// REQUIREMENTS
// ============================================================

function loadRequirements(admissionType) {
    var container = document.getElementById('requirementsList');
    container.innerHTML = '<div class="loading-text">Loading requirements...</div>';

    var effectiveType = (admissionType && admissionType.trim() !== '') ? admissionType : 'freshmen';
    var url = baseUrl + '/api/get_requirements_by_type.php?admission_type='
        + encodeURIComponent(effectiveType);

    fetch(url)
        .then(function (r) {
            if (!r.ok) throw new Error('HTTP ' + r.status);
            return r.json();
        })
        .then(function (data) {
            if (data.success && Array.isArray(data.requirements) && data.requirements.length > 0) {
                renderRequirements(data.requirements);
            } else {
                container.innerHTML = '<div class="alert alert-info">No specific requirements found.</div>';
            }
        })
        .catch(function (err) {
            console.error('Error loading requirements:', err);
            container.innerHTML = '<div class="alert alert-danger">Error loading requirements: ' + err.message + '</div>';
        });
}

function renderRequirements(requirements) {
    var container = document.getElementById('requirementsList');
    var grouped = {};

    requirements.forEach(function (req) {
        var cat = req.requirement_category || 'General';
        if (!grouped[cat]) grouped[cat] = [];
        grouped[cat].push(req);
    });

    var html = '';
    Object.keys(grouped).forEach(function (category) {
        html += '<div class="req-category" data-category="' + category + '">';
        html += '<h4>' + category.charAt(0).toUpperCase() + category.slice(1) + ' Requirements';
        html += '<label class="select-all-category">';
        html += '<input type="checkbox" data-action-change="toggle-category">';
        html += '<span>Select All</span>';
        html += '</label></h4>';
        html += '<div class="req-items">';

        grouped[category].forEach(function (req) {
            var checkedAttr = req.is_mandatory ? 'checked' : '';
            var mandatoryAttr = req.is_mandatory ? 'data-mandatory="1"' : '';

            html += '<div class="req-item">';
            html += '<input type="checkbox" '
                 +  'name="requirements[' + req.requirement_id + ']" '
                 +  'value="1" '
                 +  'id="req_' + req.requirement_id + '" '
                 +  checkedAttr + ' ' + mandatoryAttr + ' '
                 +  'data-action-change="update-requirements-count">';
            html += '<label for="req_' + req.requirement_id + '">' + req.requirement_name;
            if (req.is_mandatory) html += ' <span class="mandatory-badge">*</span>';
            html += '</label>';
            html += '<input type="text" name="req_notes[' + req.requirement_id + ']" placeholder="Notes (optional)" class="req-note-input">';
            html += '</div>';
        });

        html += '</div></div>';
    });

    container.innerHTML = html;

    var sa = document.getElementById('selectAllRequirements');
    if (sa) { sa.disabled = false; sa.checked = false; sa.indeterminate = false; }

    updateRequirementsCount();
}

function updateRequirementsCount() {
    var count = document.querySelectorAll('#requirementsList input[type="checkbox"]:checked').length;
    var el    = document.getElementById('selectedRequirementsCount');
    if (el) el.textContent = count;

    var selectAll = document.getElementById('selectAllRequirements');
    if (selectAll) {
        var all     = document.querySelectorAll('#requirementsList input[type="checkbox"]');
        var checked = document.querySelectorAll('#requirementsList input[type="checkbox"]:checked');

        if (all.length === 0) {
            selectAll.checked = false; selectAll.disabled = true; selectAll.indeterminate = false;
        } else if (checked.length === all.length) {
            selectAll.checked = true; selectAll.disabled = false; selectAll.indeterminate = false;
        } else if (checked.length > 0) {
            selectAll.checked = false; selectAll.disabled = false; selectAll.indeterminate = true;
        } else {
            selectAll.checked = false; selectAll.disabled = false; selectAll.indeterminate = false;
        }
    }

    document.querySelectorAll('#requirementsList .req-category').forEach(function (cat) {
        var catBoxes    = cat.querySelectorAll('input[type="checkbox"]');
        var catChecked  = cat.querySelectorAll('input[type="checkbox"]:checked');
        var catSelectAll = cat.querySelector('.select-all-category input[type="checkbox"]');
        if (catSelectAll) {
            if (catChecked.length === catBoxes.length && catBoxes.length > 0) {
                catSelectAll.checked = true; catSelectAll.indeterminate = false;
            } else if (catChecked.length > 0) {
                catSelectAll.checked = false; catSelectAll.indeterminate = true;
            } else {
                catSelectAll.checked = false; catSelectAll.indeterminate = false;
            }
        }
    });
}

// ============================================================
// MODAL CLOSE HANDLERS
// ============================================================

window.addEventListener('click', function (e) {
    if (e.target === document.getElementById('enrollmentModal'))  closeEnrollmentModal();
    if (e.target === document.getElementById('progressionModal')) closeProgressionModal();
    if (e.target === document.getElementById('scheduleModal'))    closeScheduleModal();
    if (e.target === document.getElementById('archiveModal'))     closeArchiveModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
        closeEnrollmentModal();
        closeProgressionModal();
        closeScheduleModal();
        closeArchiveModal();
    }
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        openEnrollmentModal(
        JSON.parse(this.getAttribute('data-action1-arg-0')),
        JSON.parse(this.getAttribute('data-action1-arg-1')),
        JSON.parse(this.getAttribute('data-action1-arg-2')),
        JSON.parse(this.getAttribute('data-action1-arg-3')),
        JSON.parse(this.getAttribute('data-action1-arg-4'))
        )
    },
    action4: function (event) {
        viewSchedule(JSON.parse(this.getAttribute('data-action4-arg-0')))
    },
    action5: function (event) {
        openProgressionEnrollment(
        JSON.parse(this.getAttribute('data-action5-arg-0')),
        JSON.parse(this.getAttribute('data-action5-arg-1')),
        JSON.parse(this.getAttribute('data-action5-arg-2')),
        JSON.parse(this.getAttribute('data-action5-arg-3')),
        JSON.parse(this.getAttribute('data-action5-arg-4'))
        )
    },
    action6: function (event) {
        openArchiveModal(
        JSON.parse(this.getAttribute('data-action6-arg-0')),
        JSON.parse(this.getAttribute('data-action6-arg-1'))
        )
    },
    action7: function (event) {
        closeEnrollmentModal()
    },
    action11: function (event) {
        closeEnrollmentModal()
    },
    action12: function (event) {
        closeProgressionModal()
    },
    action15: function (event) {
        closeProgressionModal()
    },
    action16: function (event) {
        closeArchiveModal()
    },
    action17: function (event) {
        closeArchiveModal()
    },
    action18: function (event) {
        closeScheduleModal()
    }
});
PageUI.bind('change', {
    'toggle-category': function () {
        toggleCategoryRequirements(this, this.closest('.req-category').dataset.category);
    },
    'update-requirements-count': function () { updateRequirementsCount(); },
    action2: function (event) {
        this.form.submit()
    },
    action3: function (event) {
        this.form.submit()
    },
    action9: function (event) {
        loadSubjectsForSection(this.value)
    },
    action10: function (event) {
        toggleSelectAllRequirements(this)
    },
    action14: function (event) {
        loadProgressionSubjects(this.value)
    }
});
PageUI.bind('submit', {
    action8: function (event) {
        return validateEnrollmentForm()
    },
    action13: function (event) {
        return validateProgressionForm()
    }
});
