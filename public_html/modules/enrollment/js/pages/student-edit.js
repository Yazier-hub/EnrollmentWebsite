// Age auto-calc
function calculateAge() {
    var birthInput = document.getElementById('date_of_birth');
    var ageInput   = document.getElementById('age');
    if (!birthInput || !ageInput || !birthInput.value) return;

    var today = new Date();
    var birth = new Date(birthInput.value);
    if (isNaN(birth.getTime())) { ageInput.value = ''; return; }

    var age = today.getFullYear() - birth.getFullYear();
    var m = today.getMonth() - birth.getMonth();
    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) age--;
    ageInput.value = age >= 0 ? age : '';
}

document.getElementById('date_of_birth')?.addEventListener('change', calculateAge);
document.getElementById('date_of_birth')?.addEventListener('input',  calculateAge);
document.addEventListener('DOMContentLoaded', calculateAge);

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('submit', {
    action1: function (event) {
        return confirm('Are you sure you want to delete this student? This action cannot be undone!');
    }
});
