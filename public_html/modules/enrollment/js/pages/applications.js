// ===== N/A TOGGLE =====
function toggleNA(inputId, checkbox) {
    const input = document.getElementById(inputId);
    if (!input) return;

    if (checkbox.checked) {
        input.dataset.previousValue = input.value;
        input.value = 'N/A';
        input.readOnly = true;
        input.classList.add('na-active');
    } else {
        input.value = input.dataset.previousValue || '';
        input.readOnly = false;
        input.classList.remove('na-active');
        input.focus();
    }
}

PageUI.bind('change', {
    'toggle-na': function () { toggleNA(this.dataset.inputId, this); }
});

// ===== MODAL CONTROLS =====
function showNewApplication() {
    document.getElementById('newApplicationModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hideNewApplication() {
    document.getElementById('newApplicationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function hideEditApplication() {
    document.getElementById('editApplicationModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ===== EDIT APPLICATION =====
function editApplication(id) {
    fetch('?page=applications&ajax=get_application&id=' + encodeURIComponent(id))
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Failed to load application data.');
                return;
            }

            const app = data.data;
            const map = {
                edit_applicant_id: 'applicant_id',
                edit_first_name: 'first_name',
                edit_middle_name: 'middle_name',
                edit_surname: 'surname',
                edit_suffix: 'suffix',
                edit_admission_type: 'admission_type',
                edit_working_student: 'working_student',
                edit_sex: 'sex',
                edit_civil_status: 'civil_status',
                edit_date_of_birth: 'date_of_birth',
                edit_age: 'age',
                edit_place_of_birth: 'place_of_birth',
                edit_religion: 'religion',
                edit_address_barangay: 'address_barangay',
                edit_address_city: 'address_city',
                edit_address_province: 'address_province',
                edit_address_complete: 'address_complete',
                edit_email: 'email',
                edit_contact_number: 'contact_number',
                edit_facebook: 'facebook',
                edit_messenger: 'messenger',
                edit_school_last_attended: 'school_last_attended',
                edit_year_graduated: 'year_graduated',
                edit_how_hear: 'how_hear',
                edit_parent_full_name: 'parent_full_name',
                edit_parent_contact: 'parent_contact',
                edit_parent_address: 'parent_address',
                edit_course_id: 'course_id'
            };

            for (const [domId, field] of Object.entries(map)) {
                const el = document.getElementById(domId);
                if (!el) continue;

                const raw = app[field] ?? '';
                const isNA = typeof raw === 'string'
                          && raw.trim().toUpperCase() === 'N/A';

                const naChk = document.getElementById('na_chk_' + domId);

                if (naChk) {
                    if (isNA) {
                        el.value = 'N/A';
                        el.readOnly = true;
                        el.classList.add('na-active');
                        naChk.checked = true;
                    } else {
                        el.value = raw;
                        el.readOnly = false;
                        el.classList.remove('na-active');
                        naChk.checked = false;
                    }
                } else {
                    el.value = isNA ? '' : raw;
                }
            }

            document.getElementById('editApplicationModal').style.display = 'block';
            document.body.style.overflow = 'hidden';
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading application data. Please try again.');
        });
}

// ===== AGE CALCULATION =====
function calculateAge(scope) {
    const birthInputId = scope === 'edit' ? 'edit_date_of_birth' : null;
    const ageInputId   = scope === 'edit' ? 'edit_age'         : 'new_age';

    const birthInput = birthInputId
        ? document.getElementById(birthInputId)
        : document.querySelector('#applicationForm input[name="date_of_birth"]');

    const ageInput = document.getElementById(ageInputId);

    if (!birthInput || !ageInput || !birthInput.value) {
        if (ageInput) ageInput.value = '';
        return;
    }

    const birth = new Date(birthInput.value);
    const today = new Date();

    if (isNaN(birth.getTime())) {
        ageInput.value = '';
        return;
    }

    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;

    ageInput.value = age >= 0 ? age : '';
}

// ===== BULK ACTIONS =====
function toggleAllCheckboxes(master) {
    document.querySelectorAll('.app-checkbox').forEach(cb => cb.checked = master.checked);
    updateSelectedCount();
}

function updateSelectedCount() {
    const count = document.querySelectorAll('.app-checkbox:checked').length;
    const span  = document.getElementById('selectedCount');
    if (span) span.textContent = count + ' selected';
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.app-checkbox').forEach(cb => {
        cb.addEventListener('change', updateSelectedCount);
    });

    const editDob = document.getElementById('edit_date_of_birth');
    if (editDob) {
        editDob.addEventListener('change', () => calculateAge('edit'));
    }
});

function confirmBulkAction() {
    const actionEl = document.getElementById('bulkAction');
    const action   = actionEl ? actionEl.value : '';

    if (!action) { alert('Please select an action.'); return false; }

    const selected = document.querySelectorAll('.app-checkbox:checked').length;
    if (selected === 0) { alert('Please select at least one application.'); return false; }

    if (action === 'delete') {
        return confirm('Are you sure you want to delete ' + selected + ' application(s)? This cannot be undone!');
    }
    if (action === 'reject') {
        return confirm('Are you sure you want to reject ' + selected + ' application(s)?');
    }
    return true;
}

// ===== CLOSE MODALS =====
window.addEventListener('click', function (event) {
    const newModal  = document.getElementById('newApplicationModal');
    const editModal = document.getElementById('editApplicationModal');
    if (event.target === newModal)  hideNewApplication();
    if (event.target === editModal) hideEditApplication();
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        hideNewApplication();
        hideEditApplication();
    }
});

// ===== FORM VALIDATION =====
document.getElementById('applicationForm')?.addEventListener('submit', function (e) {
    const required = this.querySelectorAll('[required]');
    let hasError = false;
    let firstError = null;

    required.forEach(function (field) {
        if (field.readOnly && field.value === 'N/A') return;

        if (!field.value.trim()) {
            field.style.borderColor = '#dc3545';
            hasError = true;
            if (!firstError) firstError = field;
        } else {
            field.style.borderColor = '';
        }
    });

    const yearGrad = this.querySelector('input[name="year_graduated"]');
    if (yearGrad
        && yearGrad.value.trim() !== ''
        && yearGrad.value.trim().toUpperCase() !== 'N/A') {
        const ok = /^\d{4}(\s*-\s*\d{4})?$/.test(yearGrad.value.trim());
        if (!ok) {
            yearGrad.style.borderColor = '#dc3545';
            alert('⚠️ Year Graduated must be in YYYY-YYYY format (e.g., 2024-2025).');
            e.preventDefault();
            return;
        }
    }

    if (hasError) {
        e.preventDefault();
        if (firstError) firstError.focus();
        alert('⚠️ Please fill in all required fields.');
    }
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        showNewApplication()
    },
    action4: function (event) {
        editApplication(JSON.parse(this.getAttribute('data-action4-arg-0')))
    },
    action6: function (event) {
        showNewApplication(); return false;
    },
    action7: function (event) {
        hideNewApplication()
    },
    action9: function (event) {
        hideEditApplication()
    },
    action11: function (event) {
        hideEditApplication()
    }
});
PageUI.bind('submit', {
    action2: function (event) {
        return confirmBulkAction();
    },
    action5: function (event) {
        return confirm('Are you sure you want to delete this application?');
    }
});
PageUI.bind('change', {
    action3: function (event) {
        toggleAllCheckboxes(this)
    },
    action8: function (event) {
        calculateAge('new')
    },
    action10: function (event) {
        calculateAge('edit')
    }
});
