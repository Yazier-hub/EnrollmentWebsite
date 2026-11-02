/* ============================================================
   School Directress — User Management (AJAX)
   Prefix: um / UM_
   ============================================================ */

const PAGE_KEY = 'user-management';

const UM_ENDPOINT = 'controllers/UserController.php';
const UM_SEARCH_DELAY = 300;
const UM_REQUEST_TIMEOUT = 15000;

let umEls = {};
let umPage = 1;
let umTotalPages = 1;
let umRequestId = 0;
let umSearchTimer = null;

/* ── Setup ──────────────────────────────────────── */

function umQueryElements() {
    umEls = {
        search:    document.getElementById('umSearch'),
        dept:      document.getElementById('umDepartment'),
        position:  document.getElementById('umPosition'),
        status:    document.getElementById('umStatus'),
        exportBtn: document.getElementById('umExportBtn'),
        body:      document.getElementById('umBody'),
        showing:   document.getElementById('umShowing'),
        pageNav:   document.getElementById('umPageNav')
    };
    return !!umEls.body;
}

function umBindOnce(el, flag, evt, handler) {
    if (!el || el.dataset[flag]) return;
    el.dataset[flag] = '1';
    el.addEventListener(evt, handler);
}

function umInit() {
    if (!umQueryElements()) return;

    umBindOnce(umEls.search, 'umBound', 'input', function () {
        clearTimeout(umSearchTimer);
        umSearchTimer = setTimeout(function () {
            umPage = 1;
            umLoad();
        }, UM_SEARCH_DELAY);
    });

    [umEls.dept, umEls.position, umEls.status].forEach(function (sel) {
        umBindOnce(sel, 'umBound', 'change', function () {
            umPage = 1;
            umLoad();
        });
    });

    umBindOnce(umEls.exportBtn, 'umBound', 'click', umExportCsv);

    umBindOnce(umEls.body, 'umBound', 'click', function (e) {
        const row = e.target.closest('tr.um-row');
        if (row) umToggle(row, row.dataset.detail);
    });

    umBindOnce(umEls.pageNav, 'umBound', 'click', function (e) {
        const btn = e.target.closest('.um-page-btn');
        if (!btn || btn.disabled) return;
        umPage = parseInt(btn.dataset.page, 10) || 1;
        umLoad();
    });

    // Initial load once per rendered page (init fires on both DOMContentLoaded and page:loaded)
    if (!umEls.body.dataset.umLoaded) {
        umEls.body.dataset.umLoaded = '1';
        umPage = 1;
        umLoad();
    }
}

/* ── Helpers ────────────────────────────────────── */

function umEsc(v) {
    return String(v === null || v === undefined ? '' : v)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function umDash(v) {
    return (v === null || v === undefined || String(v).trim() === '') ? '—' : v;
}

function umFullName(e) {
    const parts = [e.first_name, e.middle_name, e.last_name]
        .filter(function (p) { return p && String(p).trim() !== ''; });
    return parts.length ? parts.join(' ') : '—';
}

function umInitials(e) {
    const f = String(e.first_name || '').trim().charAt(0);
    const l = String(e.last_name || '').trim().charAt(0);
    return (f + l).toUpperCase() || '?';
}

function umStatusMeta(status) {
    const s = String(status || '').trim().toLowerCase();
    const map = {
        'active':       'um-badge--active',
        'probationary': 'um-badge--probation',
        'resigned':     'um-badge--inactive',
        'terminated':   'um-badge--inactive'
    };
    if (!s) return { cls: 'um-badge--muted', label: '—' };
    return { cls: map[s] || 'um-badge--muted', label: s.charAt(0).toUpperCase() + s.slice(1) };
}

function umFormatDate(d) {
    if (!d) return '—';
    const dt = new Date(String(d).substring(0, 10) + 'T00:00:00');
    if (isNaN(dt.getTime())) return '—';
    return dt.toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
}

function umFilterParams() {
    return {
        search:     umEls.search ? umEls.search.value.trim() : '',
        department: umEls.dept ? umEls.dept.value : '',
        position:   umEls.position ? umEls.position.value : '',
        status:     umEls.status ? umEls.status.value : ''
    };
}

/* ── AJAX ───────────────────────────────────────── */

async function umRequest(action, extra) {
    const params = new URLSearchParams(Object.assign({ action: action }, umFilterParams(), extra || {}));
    const controller = new AbortController();
    const timer = setTimeout(function () { controller.abort(); }, UM_REQUEST_TIMEOUT);

    let res;
    try {
        res = await fetch(UM_ENDPOINT + '?' + params.toString(), {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
            signal: controller.signal
        });
    } catch (err) {
        throw new Error(err.name === 'AbortError' ? 'The request timed out.' : 'Network error.');
    } finally {
        clearTimeout(timer);
    }

    let json;
    try {
        json = await res.json();
    } catch (err) {
        throw new Error('Unexpected server response.');
    }
    if (!res.ok || !json.success) {
        throw new Error((json && json.message) || 'Request failed.');
    }
    return json.data;
}

async function umLoad() {
    if (!umEls.body) return;

    const requestId = ++umRequestId; // ignore out-of-order responses
    umRenderMessage('Loading employees…');

    try {
        const data = await umRequest('list', { page: umPage });
        if (requestId !== umRequestId) return;

        umPage = data.page;
        umTotalPages = data.pages;
        umRenderRows(data.rows);
        umRenderShowing(data);
        umRenderPager();
    } catch (err) {
        if (requestId !== umRequestId) return;
        umRenderMessage(err.message || 'Unable to load employees.', true);
        if (umEls.showing) umEls.showing.textContent = '';
        if (umEls.pageNav) umEls.pageNav.innerHTML = '';
    }
}

/* ── Row expand / collapse ──────────────────────── */

function umToggle(row, detailId) {
    const detail = document.getElementById(detailId);
    if (!detail) return;
    const open = !row.classList.contains('um-open');
    row.classList.toggle('um-open', open);
    detail.classList.toggle('um-open', open);
}

/* ── Render ─────────────────────────────────────── */

function umRenderMessage(text, isError) {
    umEls.body.innerHTML =
        '<tr><td colspan="8"><div class="um-empty' + (isError ? ' um-empty--error' : '') + '">' +
        umEsc(text) + '</div></td></tr>';
}

function umRenderRows(rows) {
    if (!rows || !rows.length) {
        umRenderMessage('No employees found.');
        return;
    }

    umEls.body.innerHTML = rows.map(function (e) {
        const name = umFullName(e);
        const em_code = umDash(e.employee_code);
        const sm = umStatusMeta(e.employment_status);
        const unit = umDash(e.unit_name);
        const dept = umDash(e.department_name);
        const pos = umDash(e.position_name);
        const rid = 'umd-' + e.employee_id;

        return '' +
        '<tr class="um-row" data-detail="' + umEsc(rid) + '">' +
            '<td><div class="um-toggle">' +
                '<svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="m6 9 6 6 6-6"/></svg>' +
            '</div></td>' +
            '<td><div class="um-employee">' +
                '<div class="um-avatar">' + umEsc(umInitials(e)) + '</div>' +
                '<div><div class="um-name">' + umEsc(name) + '</div>' +
                '<div class="um-meta">' + umEsc(pos) + '</div></div>' +
            '</div></td>' +
            '<td>' + umEsc(em_code) + '</td>' +
            '<td>' + umEsc(dept) + '</td>' +
            '<td>' + umEsc(unit) + '</td>' +
            '<td>' + umEsc(pos) + '</td>' +
            '<td><span class="um-badge ' + sm.cls + '">' + umEsc(sm.label) + '</span></td>' +
        '</tr>' +
        '<tr class="um-detail-row" id="' + umEsc(rid) + '">' +
            '<td class="um-detail-cell" colspan="8"><div class="um-detail-inner">' +
                umDetailField('Employee Code', umDash(e.employee_code)) +
                umDetailField('Department', dept) +
                umDetailField('Unit', unit) +
                umDetailField('Position', pos) +
                umDetailField('Status', sm.label) +
                umDetailField('Employment Type', umDash(e.employment_type)) +
                umDetailField('Email', umDash(e.email)) +
                umDetailField('Mobile No.', umDash(e.mobile_no)) +
                umDetailField('Hire Date', umFormatDate(e.hire_date)) +
            '</div></td>' +
        '</tr>';
    }).join('');
}

function umDetailField(label, value) {
    return '<div><div class="um-fl__k">' + umEsc(label) + '</div>' +
           '<div class="um-fl__v">' + umEsc(value) + '</div></div>';
}

function umRenderShowing(data) {
    if (!umEls.showing) return;
    if (!data.total) {
        umEls.showing.textContent = '';
        return;
    }
    const from = (data.page - 1) * data.per_page + 1;
    const to = from + data.rows.length - 1;
    umEls.showing.textContent = 'Showing ' + from + '-' + to + ' of ' + data.total;
}

function umRenderPager() {
    if (!umEls.pageNav) return;
    if (umTotalPages <= 1) {
        umEls.pageNav.innerHTML = '';
        return;
    }

    const nums = [];
    for (let p = 1; p <= umTotalPages; p++) {
        if (p === 1 || p === umTotalPages || Math.abs(p - umPage) <= 1) nums.push(p);
    }

    let html = '<button type="button" class="um-page-btn" data-page="' + (umPage - 1) + '"' +
               (umPage === 1 ? ' disabled' : '') + '>&laquo; Prev</button>';

    let prev = 0;
    nums.forEach(function (p) {
        if (p - prev > 1) html += '<span class="um-page-gap">…</span>';
        html += '<button type="button" class="um-page-btn' + (p === umPage ? ' um-page-btn--active' : '') +
                '" data-page="' + p + '">' + p + '</button>';
        prev = p;
    });

    html += '<button type="button" class="um-page-btn" data-page="' + (umPage + 1) + '"' +
            (umPage === umTotalPages ? ' disabled' : '') + '>Next &raquo;</button>';

    umEls.pageNav.innerHTML = html;
}

/* ── Export (all rows matching current filters) ─── */

function umCsvCell(v) {
    return '"' + String(v === null || v === undefined ? '' : v).replace(/"/g, '""') + '"';
}

async function umExportCsv() {
    const btn = umEls.exportBtn;
    if (!btn || btn.disabled) return;

    const label = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Exporting…';

    try {
        const data = await umRequest('export');
        if (!data.rows.length) return;

        const lines = [['Employee Code', 'First Name', 'Middle Name', 'Last Name', 'Department', 'Position', 'Status', 'Email', 'Mobile No.']
            .map(umCsvCell).join(',')];

        data.rows.forEach(function (e) {
            lines.push([
                e.employee_code, e.first_name, e.middle_name, e.last_name,
                e.department_name, e.position_name, e.employment_status,
                e.email, e.mobile_no
            ].map(umCsvCell).join(','));
        });

        const blob = new Blob(['\ufeff' + lines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'employees_' + new Date().toISOString().slice(0, 10) + '.csv';
        document.body.appendChild(a);
        a.click();
        a.remove();
        URL.revokeObjectURL(url);
    } catch (err) {
        alert(err.message || 'Export failed.');
    } finally {
        btn.disabled = false;
        btn.textContent = label;
    }
}

/* ── Lifecycle ──────────────────────────────────── */

document.addEventListener('DOMContentLoaded', umInit);
document.addEventListener('page:loaded', umInit);

// Safety net: the router may insert the page after (or without) firing the events
// above, so also watch for #umBody appearing. umInit is safe to call repeatedly —
// the initial load is guarded by a flag on the table body.
function umWatchDom() {
    if (window.__umObserver || !document.body) return;
    window.__umObserver = new MutationObserver(function () {
        const body = document.getElementById('umBody');
        if (body && !body.dataset.umLoaded) umInit();
    });
    window.__umObserver.observe(document.body, { childList: true, subtree: true });
}

umWatchDom();
document.addEventListener('DOMContentLoaded', umWatchDom);

if (document.readyState !== 'loading') {
    umInit();
}