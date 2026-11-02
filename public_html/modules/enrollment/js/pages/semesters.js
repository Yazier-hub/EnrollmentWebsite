function setActiveSemester(semesterId, semesterName) {
    if (!confirm('Gawing active ang "' + semesterName + '"?\n\nAwtomatikong ma-deactivate ang ibang semesters.')) {
        return;
    }

    var formData = new FormData();
    formData.append('semester_id', semesterId);

    fetch('api/set_active_semester.php', {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            showMessage('✅ ' + data.message, 'success');
            setTimeout(function () { window.location.reload(); }, 1500);
        } else {
            showMessage('❌ ' + data.message, 'danger');
        }
    })
    .catch(function (err) {
        console.error('Error:', err);
        showMessage('❌ Error: ' + err.message, 'danger');
    });
}

function setActiveSchoolYear(syId, syName) {
    if (!confirm('Gawing active ang School Year "' + syName + '"?\n\nAwtomatikong ma-deactivate ang ibang school years.')) {
        return;
    }

    var formData = new FormData();
    formData.append('school_year_id', syId);

    fetch('api/set_active_school_year.php', {
        method: 'POST',
        body: formData
    })
    .then(function (response) {
        if (!response.ok) throw new Error('HTTP ' + response.status);
        return response.json();
    })
    .then(function (data) {
        if (data.success) {
            showMessage('✅ ' + data.message, 'success');
            setTimeout(function () { window.location.reload(); }, 1500);
        } else {
            showMessage('❌ ' + data.message, 'danger');
        }
    })
    .catch(function (err) {
        console.error('Error:', err);
        showMessage('❌ Error: ' + err.message, 'danger');
    });
}

function showMessage(message, type) {
    var box = document.getElementById('messageBox');
    box.classList.remove('alert-success', 'alert-danger', 'alert-info', 'alert-warning');
    box.classList.add('alert', 'alert-' + type);
    box.textContent = message;
    box.style.display = 'block';
    box.scrollIntoView({ behavior: 'smooth', block: 'center' });
}

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        setActiveSchoolYear(
        JSON.parse(this.getAttribute('data-action1-arg-0')),
        JSON.parse(this.getAttribute('data-action1-arg-1'))
        )
    },
    action2: function (event) {
        setActiveSemester(
        JSON.parse(this.getAttribute('data-action2-arg-0')),
        JSON.parse(this.getAttribute('data-action2-arg-1'))
        )
    }
});
