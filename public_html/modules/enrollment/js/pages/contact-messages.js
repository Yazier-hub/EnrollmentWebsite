document.addEventListener('DOMContentLoaded', function () {
    const selAll = document.getElementById('selectAll');
    if (selAll) {
        const boxes = () => document.querySelectorAll('.row-check');
        selAll.addEventListener('change', function () {
            boxes().forEach(b => b.checked = selAll.checked);
        });
        document.querySelectorAll('.row-check').forEach(b => {
            b.addEventListener('change', function () {
                const all = boxes();
                const checked = document.querySelectorAll('.row-check:checked');
                selAll.indeterminate = checked.length > 0 && checked.length < all.length;
                selAll.checked = checked.length === all.length && all.length > 0;
            });
        });
    }
    const q = new URLSearchParams(window.location.search).get('q');
    if (q) {
        const inp = document.querySelector('.filter-search input[name=q]');
        if (inp) inp.focus();
    }
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('submit', {
    action1: function (event) {
        return confirm('Mark all as read?');
    },
    action3: function (event) {
        return confirm('Delete this message permanently?');
    }
});
PageUI.bind('click', {
    action2: function (event) {
        return confirm('Delete selected messages?');
    }
});
