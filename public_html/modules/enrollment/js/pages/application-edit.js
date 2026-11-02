function calculateAge() {
    const birthDateInput = document.querySelector('input[name="date_of_birth"]');
    const ageInput       = document.getElementById('age');
    if (!birthDateInput || !ageInput || !birthDateInput.value) return;

    const today = new Date();
    const birth = new Date(birthDateInput.value);
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    ageInput.value = age;
}

document.getElementById('editForm')?.addEventListener('submit', function (e) {
    const required = this.querySelectorAll('[required]');
    let hasError = false;
    let firstError = null;

    required.forEach(function (field) {
        if (!field.value.trim()) {
            field.classList.add('error');
            hasError = true;
            if (!firstError) firstError = field;
        } else {
            field.classList.remove('error');
        }
    });

    if (hasError) {
        e.preventDefault();
        if (firstError) firstError.focus();
        alert('⚠️ Please fill in all required fields.');
    }
});

document.querySelectorAll('.form-control').forEach(function (input) {
    input.addEventListener('input', function () {
        this.classList.remove('error');
    });
});

document.querySelector('input[name="date_of_birth"]')?.addEventListener('change', calculateAge);
document.querySelector('input[name="date_of_birth"]')?.addEventListener('input',  calculateAge);

// Recalculate age on page load in case it was empty
document.addEventListener('DOMContentLoaded', calculateAge);

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('change', {
    action1: function (event) {
        calculateAge()
    }
});
PageUI.bind('submit', {
    action2: function (event) {
        return confirm('Are you sure you want to delete this application? This action cannot be undone!');
    }
});
