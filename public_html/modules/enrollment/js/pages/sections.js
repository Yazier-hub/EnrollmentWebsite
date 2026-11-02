function showAddSection() {
    document.getElementById('addSectionModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function hideAddSection() {
    document.getElementById('addSectionModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.addEventListener('click', function (event) {
    var modal = document.getElementById('addSectionModal');
    if (event.target === modal) hideAddSection();
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') hideAddSection();
});

document.querySelector('.section-form')?.addEventListener('submit', function (e) {
    var programId     = this.querySelector('select[name="program_id"]').value;
    var sectionNumber = this.querySelector('input[name="section_number"]').value;
    var schoolYear    = this.querySelector('input[name="school_year"]').value;

    if (!programId) {
        e.preventDefault(); alert('⚠️ Please select a course.'); return false;
    }
    if (!sectionNumber || sectionNumber < 1) {
        e.preventDefault(); alert('⚠️ Please enter a valid section number.'); return false;
    }
    if (!schoolYear || !schoolYear.match(/^\d{4}-\d{4}$/)) {
        e.preventDefault(); alert('⚠️ Please enter a valid school year format (YYYY-YYYY).'); return false;
    }
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('change', {
    action1: function (event) {
        this.form.submit()
    },
    action2: function (event) {
        this.form.submit()
    }
});
PageUI.bind('click', {
    action3: function (event) {
        showAddSection()
    },
    action4: function (event) {
        showAddSection(); return false;
    },
    action5: function (event) {
        hideAddSection()
    },
    action6: function (event) {
        hideAddSection()
    }
});
