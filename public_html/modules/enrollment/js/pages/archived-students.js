/**
 * Confirm restore with student details.
 */
function confirmRestore(studentName, studentNumber) {
    var message = '♻️ RESTORE STUDENT\n\n'
                + 'Student: '   + studentName   + '\n'
                + 'Student #: ' + studentNumber + '\n\n'
                + '⚠️ Kapag na-restore:\n'
                + '  • Ibabalik ang enrollment status sa "Enrolled"\n'
                + '  • Ibabalik ang lahat ng subjects\n'
                + '  • Ma-clear ang archive record\n\n'
                + 'Sigurado ka bang gusto mong i-restore ang student na ito?';
    return confirm(message);
}

// Keyboard shortcut: Focus search input on '/' key
document.addEventListener('keydown', function (e) {
    if (e.key === '/'
        && e.target.tagName !== 'INPUT'
        && e.target.tagName !== 'TEXTAREA') {
        e.preventDefault();
        var searchInput = document.getElementById('search_input');
        if (searchInput) searchInput.focus();
    }
});

// Highlight rows with Graduated status
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.table tbody tr').forEach(function (row) {
        var badge = row.querySelector('.badge-success');
        if (badge && badge.textContent.includes('Graduated')) {
            row.style.background = '#f0fdf0';
        }
    });
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('change', {
    action1: function (event) {
        this.form.submit()
    },
    action2: function (event) {
        this.form.submit()
    },
    action3: function (event) {
        this.form.submit()
    }
});
PageUI.bind('submit', {
    action4: function (event) {
        return confirmRestore(
        JSON.parse(this.getAttribute('data-action4-arg-0')),
        JSON.parse(this.getAttribute('data-action4-arg-1'))
        )
    }
});
