const pageData = JSON.parse(document.getElementById('page-data').dataset.values);

// ===== MODAL CONTROLS =====
function showAddCourse() {
    document.getElementById('courseModalTitle').textContent = 'Add New Course';
    document.getElementById('courseAction').value   = 'add';
    document.getElementById('courseId').value       = '';
    document.getElementById('courseCode').value     = '';
    document.getElementById('courseName').value     = '';
    document.getElementById('courseDuration').value = '4';
    document.getElementById('courseSubmitBtn').textContent = 'Add Course';
    document.getElementById('courseModal').style.display   = 'block';
    document.body.style.overflow = 'hidden';
    setTimeout(function () { document.getElementById('courseCode').focus(); }, 100);
}

function hideCourseModal() {
    document.getElementById('courseModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// ===== EDIT COURSE =====
function editCourse(courseId) {
    document.getElementById('courseModalTitle').textContent = 'Loading...';
    document.getElementById('courseModal').style.display    = 'block';
    document.body.style.overflow = 'hidden';

    fetch('?page=courses&ajax=get_course&id=' + encodeURIComponent(courseId))
        .then(function (response) {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(function (data) {
            if (data.success && data.data) {
                var course = data.data;
                document.getElementById('courseModalTitle').textContent = 'Edit Course';
                document.getElementById('courseAction').value   = 'update';
                document.getElementById('courseId').value       = course.id ?? '';
                document.getElementById('courseCode').value     = course.code || '';
                document.getElementById('courseName').value     = course.name || '';
                document.getElementById('courseDuration').value = course.years || '4';
                document.getElementById('courseSubmitBtn').textContent = 'Update Course';
            } else {
                alert(data.message || 'Failed to load course data.');
                hideCourseModal();
            }
        })
        .catch(function (error) {
            console.error('Error:', error);
            alert('Error loading course data. Please try again.');
            hideCourseModal();
        });
}

// ===== CLOSE MODAL =====
window.addEventListener('click', function (event) {
    var modal = document.getElementById('courseModal');
    if (event.target === modal) hideCourseModal();
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') hideCourseModal();
});

// ===== AUTO-UPPERCASE =====
document.addEventListener('DOMContentLoaded', function () {
    var courseCodeInput = document.getElementById('courseCode');
    if (courseCodeInput) {
        courseCodeInput.addEventListener('input', function () {
            this.value = this.value.toUpperCase();
        });
    }
});

// ===== FORM VALIDATION =====
var form = document.getElementById('courseForm');
if (form) {
    form.addEventListener('submit', function (e) {
        var code     = document.getElementById('courseCode').value.trim();
        var name     = document.getElementById('courseName').value.trim();
        var duration = document.getElementById('courseDuration').value;

        if (!code) {
            e.preventDefault();
            alert('⚠️ Please enter a course code.');
            document.getElementById('courseCode').focus();
            return false;
        }

        if (!name) {
            e.preventDefault();
            alert('⚠️ Please enter a course name.');
            document.getElementById('courseName').focus();
            return false;
        }

        if (!duration || duration < 2 || duration > 6) {
            e.preventDefault();
            alert('⚠️ Please enter a valid duration (2-6 years).');
            document.getElementById('courseDuration').focus();
            return false;
        }

        if (document.getElementById('courseAction').value === 'add') {
            var existingCodes = pageData.courseCodes;
            if (Array.isArray(existingCodes) && existingCodes.includes(code)) {
                e.preventDefault();
                alert('⚠️ Course code "' + code + '" already exists. Please use a different code.');
                document.getElementById('courseCode').focus();
                return false;
            }
        }
    });
}

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        showAddCourse()
    },
    action2: function (event) {
        editCourse(JSON.parse(this.getAttribute('data-action2-arg-0')))
    },
    action4: function (event) {
        showAddCourse(); return false;
    },
    action5: function (event) {
        hideCourseModal()
    },
    action6: function (event) {
        hideCourseModal()
    }
});
PageUI.bind('submit', {
    action3: function (event) {
        return confirm('Delete this course? This action cannot be undone.');
    }
});
