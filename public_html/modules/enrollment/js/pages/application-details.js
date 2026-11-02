function toggleEdit() {
    var detailsView = document.getElementById('detailsView');
    var editForm = document.getElementById('editForm');
    var editBtn = document.getElementById('editToggleBtn');

    if (!editForm || !detailsView || !editBtn) return;
    if (getComputedStyle(editForm).display === 'none') {
        detailsView.style.display = 'none';
        editForm.style.display = 'block';
        editBtn.textContent = 'Cancel Edit';
        editBtn.className = 'btn btn-secondary';
    } else {
        detailsView.style.display = 'grid';
        editForm.style.display = 'none';
        editBtn.textContent = '✏️ Edit';
        editBtn.className = 'btn btn-warning';
    }
}

function toggleConvert() {
    var convertForm = document.getElementById('convertForm');
    if (convertForm) {
        if (getComputedStyle(convertForm).display === 'none') {
            convertForm.style.display = 'block';
            convertForm.scrollIntoView({ behavior: 'smooth' });
        } else {
            convertForm.style.display = 'none';
        }
    }
}

function showRejectForm() {
    var rejectForm = document.getElementById('rejectForm');
    if (!rejectForm) return;
    if (getComputedStyle(rejectForm).display === 'none') {
        rejectForm.style.display = 'block';
        rejectForm.scrollIntoView({ behavior: 'smooth' });
    } else {
        rejectForm.style.display = 'none';
    }
}

function hideRejectForm() {
    document.getElementById('rejectForm').style.display = 'none';
}

// Close modals on Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        if (document.getElementById('editForm') && getComputedStyle(document.getElementById('editForm')).display !== 'none') {
            toggleEdit();
        }
        if (document.getElementById('rejectForm') && getComputedStyle(document.getElementById('rejectForm')).display !== 'none') {
            hideRejectForm();
        }
        if (document.getElementById('convertForm') && getComputedStyle(document.getElementById('convertForm')).display !== 'none') {
            toggleConvert();
        }
    }
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        toggleEdit()
    },
    action2: function (event) {
        showRejectForm()
    },
    action3: function (event) {
        toggleConvert()
    },
    action5: function (event) {
        toggleConvert()
    },
    action6: function (event) {
        hideRejectForm()
    },
    action7: function (event) {
        toggleEdit()
    }
});
PageUI.bind('submit', {
    action4: function (event) {
        return confirm('Are you sure you want to delete this application?');
    }
});
