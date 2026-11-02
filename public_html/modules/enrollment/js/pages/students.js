// ============================================================
// SCHEDULE VIEW MODAL
// ============================================================

function viewSchedule(studentId) {
    var modal   = document.getElementById('scheduleModal');
    var content = document.getElementById('scheduleContent');
    content.innerHTML = '<div class="loading-text">Loading schedule...</div>';
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden';

    // Resolve relative to the app root, not the current path
    var root = window.location.origin
        + window.location.pathname.replace(/\/[^\/]*$/, '');
    var url = root + '/api/get_student_schedule.php?student_id=' + encodeURIComponent(studentId);

    fetch(url)
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (data.success && data.data && data.data.grouped) {
                renderSchedule(data.data.grouped);
            } else {
                content.innerHTML = '<div class="alert alert-info schedule-message">No schedule found for this student.</div>';
            }
        })
        .catch(function (err) {
            console.error('Error loading schedule:', err);
            content.innerHTML = '<div class="alert alert-danger schedule-message">Error loading schedule. Please try again.</div>';
        });
}

function renderSchedule(groupedSchedule) {
    var content = document.getElementById('scheduleContent');
    var html = '';
    var totalSubjects = 0;

    var dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    dayOrder.forEach(function (day) {
        if (!groupedSchedule[day] || groupedSchedule[day].length === 0) return;

        html += '<div class="schedule-day"><h3>' + day + '</h3>';

        groupedSchedule[day].sort(function (a, b) {
            return (a.start_time || '00:00:00').localeCompare(b.start_time || '00:00:00');
        });

        groupedSchedule[day].forEach(function (item) {
            totalSubjects++;
            html += '<div class="schedule-item">';
            html += '<span class="time">'
                 +  (item.start_time ? String(item.start_time).substring(0, 5) : '--:--')
                 +  ' - '
                 +  (item.end_time   ? String(item.end_time).substring(0, 5)   : '--:--')
                 +  '</span>';
            html += '<span class="subject"><strong>' + (item.subject_code || '') + '</strong> - ' + (item.subject_name || '') + '</span>';

            var faculty = item.faculty_name
                       || (item.faculty_first ? (item.faculty_first + ' ' + (item.faculty_last || '')) : '');
            if (faculty) {
                html += '<span class="faculty">👨‍🏫 ' + faculty + '</span>';
            }

            if (item.room_code) {
                html += '<span class="room">🏫 ' + item.room_code + '</span>';
            } else if (item.room_name) {
                html += '<span class="room">🏫 ' + item.room_name + '</span>';
            }

            html += '</div>';
        });

        html += '</div>';
    });

    if (totalSubjects === 0) {
        html = '<div class="no-schedule">📅 No scheduled subjects found.</div>';
    }

    content.innerHTML = html;
}

function closeScheduleModal() {
    document.getElementById('scheduleModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.addEventListener('click', function (e) {
    var scheduleModal = document.getElementById('scheduleModal');
    if (e.target === scheduleModal) closeScheduleModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeScheduleModal();
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
PageUI.bind('click', {
    action4: function (event) {
        viewSchedule(JSON.parse(this.getAttribute('data-action4-arg-0')))
    },
    action5: function (event) {
        closeScheduleModal()
    }
});
