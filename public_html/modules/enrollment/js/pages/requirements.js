function openModal(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'block';
    document.body.style.overflow = 'hidden';
}
function closeModal(id) {
    var el = document.getElementById(id);
    if (el) el.style.display = 'none';
    document.body.style.overflow = 'auto';
}
window.editRequirement = function (id, name, category, mandatory) {
    document.getElementById('edit_req_id').value = id;
    document.getElementById('edit_req_name').value = name;
    document.getElementById('edit_req_category').value = category;
    document.getElementById('edit_req_mandatory').checked = (mandatory == 1);
    openModal('editRequirementModal');
};

document.addEventListener('DOMContentLoaded', function () {
    var searchInput = document.getElementById('searchInput');
    var searchForm  = document.getElementById('sidebarSearchForm');
    var searchTimeout = null;

    if (searchInput && searchForm) {
        searchInput.addEventListener('input', function () {
            if (searchTimeout) clearTimeout(searchTimeout);
            var val = this.value.trim();
            searchTimeout = setTimeout(function () {
                var url = new URL(window.location.href);
                if (val !== '') url.searchParams.set('search', val);
                else url.searchParams.delete('search');
                url.searchParams.delete('student_id');
                url.searchParams.delete('category');
                window.location.href = url.toString();
            }, 500);
        });
    }

    var paymentSearchInput = document.getElementById('paymentSearchInput');
    var payTimeout = null;
    if (paymentSearchInput) {
        paymentSearchInput.addEventListener('input', function () {
            if (payTimeout) clearTimeout(payTimeout);
            var val = this.value.trim();
            payTimeout = setTimeout(function () {
                var url = new URL(window.location.href);
                if (val !== '') url.searchParams.set('pay_search', val);
                else url.searchParams.delete('pay_search');
                window.location.href = url.toString();
            }, 500);
        });
    }
});

window.addEventListener('click', function (event) {
    ['addRequirementModal', 'editRequirementModal'].forEach(function (id) {
        var modal = document.getElementById(id);
        if (event.target === modal) closeModal(id);
    });
});

document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
        closeModal('addRequirementModal');
        closeModal('editRequirementModal');
    }
});

// Event bindings for this page; dynamic values come from HTML data attributes.
PageUI.bind('click', {
    action1: function (event) {
        openModal('addRequirementModal')
    },
    action2: function (event) {
        editRequirement(JSON.parse(this.getAttribute('data-action2-arg-0')), JSON.parse(this.getAttribute('data-action2-arg-1')), JSON.parse(this.getAttribute('data-action2-arg-2')), JSON.parse(this.getAttribute('data-action2-arg-3')))
    },
    action3: function (event) {
        return confirm('Delete this requirement?')
    },
    action4: function (event) {
        return confirm('Verify this payment? Student will be notified.')
    },
    action5: function (event) {
        return confirm('Reject this payment?')
    },
    action6: function (event) {
        closeModal('addRequirementModal')
    },
    action7: function (event) {
        closeModal('addRequirementModal')
    },
    action8: function (event) {
        closeModal('editRequirementModal')
    },
    action9: function (event) {
        closeModal('editRequirementModal')
    }
});
