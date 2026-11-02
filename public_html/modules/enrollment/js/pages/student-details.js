function openArchiveModal() {
    document.getElementById('archiveModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
}

function closeArchiveModal() {
    document.getElementById('archiveModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

window.addEventListener('click', function (e) {
    var m = document.getElementById('archiveModal');
    if (e.target === m) closeArchiveModal();
});

document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') closeArchiveModal();
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        openArchiveModal()
    },
    action2: function (event) {
        closeArchiveModal()
    },
    action3: function (event) {
        closeArchiveModal()
    }
});
