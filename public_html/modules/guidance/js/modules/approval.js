const ApprovalController = 'controllers/ApprovalController.php';

const APPROVAL_STATUS_LABELS = {
    draft: 'Draft',
    submitted: 'Submitted',
    reviewed: 'Reviewed',
    approved: 'Approved',
    rejected: 'Rejected',
};

// ── INIT ──────────────────────────────────────────────────────────────────────
function initApprovalModule() {
    const moduleRoot = document.querySelector('.approval-module');
    if (!moduleRoot) return;

    // Prevents the same element from getting a listener attached twice when
    // init() runs more than once on unchanged DOM.
    function bindOnce(el, event, handler) {
        if (!el || el.dataset.approvalBound === '1') return;
        el.addEventListener(event, handler);
        el.dataset.approvalBound = '1';
    }

    loadApprovalQueue();

    const approvalForm = document.getElementById('approval-form');

    // --- Create modal wiring ---
    const modalOverlay  = document.getElementById('approval-modal-overlay');
    const openModalBtn  = document.getElementById('approval-open-modal');
    const closeModalBtn = document.getElementById('approval-modal-close');

    const openCreateModal = () => {
        approvalForm?.reset();
        document.getElementById('approval-id').value = '';
        modalOverlay?.classList.add('active');
    };
    const closeCreateModal = () => {
        modalOverlay?.classList.remove('active');
        const formError = document.getElementById('approval-form-error');
        if (formError) formError.style.display = 'none';
    };

    bindOnce(openModalBtn, 'click', openCreateModal);
    bindOnce(closeModalBtn, 'click', closeCreateModal);
    bindOnce(modalOverlay, 'click', (e) => {
        if (e.target === modalOverlay) closeCreateModal();
    });

    // --- View/PDF modal wiring ---
    const viewOverlay = document.getElementById('approval-view-modal-overlay');
    const viewClose   = document.getElementById('approval-view-modal-close');
    const pdfOverlay  = document.getElementById('approval-pdf-modal-overlay');
    const pdfClose    = document.getElementById('approval-pdf-modal-close');
    const pdfFrame    = document.getElementById('approval-pdf-frame');

    const closeViewModal = () => viewOverlay?.classList.remove('active');
    const closePdfModal  = () => {
        pdfOverlay?.classList.remove('active');
        if (pdfFrame) pdfFrame.src = '';
    };

    bindOnce(viewClose, 'click', closeViewModal);
    bindOnce(viewOverlay, 'click', (e) => {
        if (e.target === viewOverlay) closeViewModal();
    });
    bindOnce(pdfClose, 'click', closePdfModal);
    bindOnce(pdfOverlay, 'click', (e) => {
        if (e.target === pdfOverlay) closePdfModal();
    });

    if (!window.approvalEscBound) {
        document.addEventListener('keydown', (e) => {
            if (e.key !== 'Escape') return;
            closeCreateModal();
            closeViewModal();
            closePdfModal();
        });
        window.approvalEscBound = true;
    }

    // --- Save as Draft ---
    const draftBtn = document.getElementById('approval-draft-btn');
    bindOnce(draftBtn, 'click', () => submitApprovalForm('save_draft'));

    // --- Submit ---
    bindOnce(approvalForm, 'submit', (e) => {
        e.preventDefault();
        submitApprovalForm('submit');
    });

    // --- Table row "View" clicks (delegated, survives re-renders) ---
    if (!window.approvalViewDelegationBound) {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.approval-view-btn');
            if (btn) viewApproval(btn.dataset.approvalId);
        });
        window.approvalViewDelegationBound = true;
    }

    // --- Department filter ---
    const deptFilter = document.getElementById('department-filter');
    bindOnce(deptFilter, 'change', () => loadApprovalQueue());

    // --- Status filter ---
    const statusFilter = document.getElementById('status-filter');
    bindOnce(statusFilter, 'change', () => loadApprovalQueue());
}

// Handles both hard refresh and sidebar navigation
window.addEventListener('page:loaded', initApprovalModule);
document.addEventListener('DOMContentLoaded', initApprovalModule);

// ── SUBMIT / DRAFT ────────────────────────────────────────────────────────────
async function submitApprovalForm(action) {
    const approvalForm = document.getElementById('approval-form');
    const formError     = document.getElementById('approval-form-error');
    const submitBtn     = document.getElementById('approval-submit-btn');
    const draftBtn      = document.getElementById('approval-draft-btn');

    formError.style.display = 'none';
    submitBtn.disabled = true;
    draftBtn.disabled  = true;
    const originalLabel = submitBtn.textContent;
    submitBtn.textContent = action === 'submit' ? 'Submitting…' : submitBtn.textContent;

    const formData = new FormData(approvalForm);
    formData.set('action', action);

    try {
        const res  = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (!json.success && json.message?.toLowerCase().includes('session expired')) {
            setTimeout(() => window.location.href = '/sms/index.php', 1500);
            return;
        }

        if (json.success) {
            document.getElementById('approval-modal-overlay')?.classList.remove('active');
            showToast(action === 'submit' ? 'Approval request submitted successfully!' : 'Draft saved.', 'success');
            loadApprovalQueue();
        } else {
            formError.textContent   = json.message;
            formError.style.display = 'block';
        }
    } catch (err) {
        console.error(err);
        formError.textContent   = 'A network error occurred. Please try again.';
        formError.style.display = 'block';
    } finally {
        submitBtn.disabled    = false;
        draftBtn.disabled     = false;
        submitBtn.textContent = originalLabel;
    }
}

// ── LOAD QUEUE ────────────────────────────────────────────────────────────────
async function loadApprovalQueue() {
    const deptFilter    = document.getElementById('department-filter');
    const statusFilter  = document.getElementById('status-filter');
    const department_id = deptFilter && deptFilter.value !== '' ? deptFilter.value : '';
    const status         = statusFilter && statusFilter.value !== '' ? statusFilter.value : '';
    const tbody          = document.querySelector('.ads-table tbody');
    if (!tbody) return;

    tbody.innerHTML = `<tr><td colspan="7" class="muted">Loading…</td></tr>`;

    const params = new URLSearchParams({ action: 'list' });
    if (department_id !== '') params.set('department_id', department_id);
    if (status !== '') params.set('status', status);

    try {
        const res  = await fetch(`${ApprovalController}?${params.toString()}`);
        const json = await res.json();

        if (!json.success) {
            tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load approvals.</td></tr>`;
            return;
        }

        if (!json.data?.length) {
            tbody.innerHTML = `<tr><td colspan="7" class="muted">No approvals found.</td></tr>`;
            return;
        }

        tbody.innerHTML = json.data.map(row => `
            <tr data-approval-id="${esc(row.approval_id)}">
                <td>${esc(row.approval_id)}</td>
                <td>${esc(row.title)}</td>
                <td>${esc(row.submit_by)}</td>
                <td>${esc(row.department_name)}</td>
                <td><span class="badge badge-${esc(row.status)}">${esc(APPROVAL_STATUS_LABELS[row.status] ?? row.status)}</span></td>
                <td>${esc(row.submitted_on ?? 'N/A')}</td>
                <td class="actions-cell">
                    <button type="button" class="attachment-link approval-view-btn" data-approval-id="${esc(row.approval_id)}">View</button>
                </td>
            </tr>
        `).join('');

    } catch (err) {
        console.error('loadApprovalQueue error:', err);
        tbody.innerHTML = `<tr><td colspan="7" class="muted">Failed to load approvals.</td></tr>`;
    }
}

// ── VIEW / REVIEW / DECISION ─────────────────────────────────────────────────
async function viewApproval(approvalId) {
    const overlay = document.getElementById('approval-view-modal-overlay');
    const body    = document.getElementById('approval-view-modal-body');
    if (!overlay || !body) return;

    body.innerHTML = '<p class="muted">Loading…</p>';
    overlay.classList.add('active');

    try {
        const res  = await fetch(`${ApprovalController}?action=get&approval_id=${encodeURIComponent(approvalId)}`);
        const json = await res.json();

        if (!json.success) {
            body.innerHTML = `<p class="muted">${esc(json.message)}</p>`;
            return;
        }

        renderApprovalDetail(json.data);
    } catch (err) {
        console.error('viewApproval error:', err);
        body.innerHTML = '<p class="muted">Failed to load approval request.</p>';
    }
}

function renderApprovalDetail(approval) {
    const moduleRoot    = document.querySelector('.approval-module');
    const isDirectress  = moduleRoot?.dataset.isDirectress === '1';
    const body          = document.getElementById('approval-view-modal-body');
    const statusLabel   = APPROVAL_STATUS_LABELS[approval.status] ?? approval.status;

    let reviewSection = '';
    if (isDirectress && approval.status === 'submitted') {
        reviewSection = `
            <div class="approval-review-box">
                <label for="approval-review-notes">Review Notes (optional)</label>
                <textarea id="approval-review-notes" rows="2" placeholder="Any notes for this review"></textarea>
                <div class="approval-actions">
                    <button type="button" class="approval-submit-btn" id="approval-mark-reviewed" data-approval-id="${esc(approval.approval_id)}">Mark as Reviewed</button>
                </div>
            </div>`;
    } else if (isDirectress && approval.status === 'reviewed') {
        reviewSection = `
            <div class="approval-review-box">
                <label for="approval-decision-notes">Decision Notes (optional)</label>
                <textarea id="approval-decision-notes" rows="2" placeholder="Any notes for this decision"></textarea>
                <div class="approval-actions">
                    <button type="button" class="btn-approve approval-decide-btn" data-decision="approved" data-approval-id="${esc(approval.approval_id)}">Approve</button>
                    <button type="button" class="btn-reject approval-decide-btn" data-decision="rejected" data-approval-id="${esc(approval.approval_id)}">Reject</button>
                </div>
            </div>`;
    }

    const pdfButton = approval.pdf_path
        ? `<button type="button" class="attachment-link" id="approval-open-pdf" data-pdf-path="${esc(approval.pdf_path)}">View PDF</button>`
        : `<span class="muted">PDF not yet generated</span>`;

    const attachmentLine = approval.file_path
        ? `<a class="attachment-link" href="/sms/${esc(approval.file_path)}" target="_blank">View Attachment</a>`
        : `<span class="muted">No attachment</span>`;

    const aiSummaryBox = `
        <div class="approval-ai-box">
            <div class="approval-ai-box-header">
                <strong>AI Summary</strong>
                <button type="button" class="attachment-link" id="approval-summarize-btn" data-approval-id="${esc(approval.approval_id)}">
                    ${approval.ai_summary ? 'Regenerate' : 'Generate Summary'}
                </button>
            </div>
            <p id="approval-ai-summary-text" class="muted">${approval.ai_summary ? esc(approval.ai_summary) : 'No AI summary yet.'}</p>
        </div>`;

    body.innerHTML = `
        <div class="approval-detail-row"><span class="approval-detail-label">Title</span><span>${esc(approval.title)}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Department</span><span>${esc(approval.department_name ?? 'N/A')}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Submitted By</span><span>${esc(approval.submit_by_name ?? 'N/A')}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Status</span><span class="badge badge-${esc(approval.status)}">${esc(statusLabel)}</span></div>
        <div class="approval-detail-row"><span class="approval-detail-label">Attachment</span>${attachmentLine}</div>

        <h4>Description</h4>
        <p>${esc(approval.description)}</p>

        <h4>Justification</h4>
        <p>${esc(approval.justification) || '<span class="muted">None provided.</span>'}</p>

        ${aiSummaryBox}

        <div class="approval-actions">${pdfButton}</div>

        ${reviewSection}
    `;

    document.getElementById('approval-open-pdf')?.addEventListener('click', (e) => {
        openPdfModal(e.currentTarget.dataset.pdfPath);
    });
    document.getElementById('approval-summarize-btn')?.addEventListener('click', (e) => {
        summarizeApproval(e.currentTarget.dataset.approvalId);
    });
    document.getElementById('approval-mark-reviewed')?.addEventListener('click', (e) => {
        const notes = document.getElementById('approval-review-notes')?.value ?? '';
        reviewApproval(e.currentTarget.dataset.approvalId, notes);
    });
    body.querySelectorAll('.approval-decide-btn').forEach(btn => {
        btn.addEventListener('click', (e) => {
            const notes = document.getElementById('approval-decision-notes')?.value ?? '';
            decideApproval(e.currentTarget.dataset.approvalId, e.currentTarget.dataset.decision, notes);
        });
    });
}

function openPdfModal(pdfPath) {
    const overlay = document.getElementById('approval-pdf-modal-overlay');
    const frame   = document.getElementById('approval-pdf-frame');
    if (!overlay || !frame || !pdfPath) return;
    frame.src = `/../${pdfPath}`;
    overlay.classList.add('active');
}

async function reviewApproval(approvalId, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'review');
        formData.append('approval_id', approvalId);
        formData.append('notes', notes);

        const res  = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast('Marked as reviewed.', 'success');
            viewApproval(approvalId);
            loadApprovalQueue();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('reviewApproval error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function decideApproval(approvalId, decision, notes) {
    try {
        const formData = new FormData();
        formData.append('action', 'decide');
        formData.append('approval_id', approvalId);
        formData.append('decision', decision);
        formData.append('notes', notes);

        const res  = await fetch(ApprovalController, { method: 'POST', body: formData });
        const json = await res.json();

        if (json.success) {
            showToast(`Request ${decision}.`, 'success');
            viewApproval(approvalId);
            loadApprovalQueue();
        } else {
            showToast(json.message, 'error');
        }
    } catch (err) {
        console.error('decideApproval error:', err);
        showToast('A network error occurred.', 'error');
    }
}

async function summarizeApproval(approvalId) {
    const btn  = document.getElementById('approval-summarize-btn');
    const text = document.getElementById('approval-ai-summary-text');
    if (btn) { btn.disabled = true; btn.textContent = 'Summarizing…'; }

    try {
        const res  = await fetch(`${ApprovalController}?action=summarize&approval_id=${encodeURIComponent(approvalId)}`, { method: 'POST' });
        const json = await res.json();

        if (json.success) {
            if (text) text.textContent = json.summary;
            if (btn) btn.textContent = 'Regenerate';
        } else {
            showToast(json.message, 'error');
            if (btn) btn.textContent = 'Generate Summary';
        }
    } catch (err) {
        console.error('summarizeApproval error:', err);
        showToast('A network error occurred.', 'error');
        if (btn) btn.textContent = 'Generate Summary';
    } finally {
        if (btn) btn.disabled = false;
    }
}

// ── TOAST ─────────────────────────────────────────────────────────────────────
function showToast(message, type = 'info') {
    const bg = type === 'success' ? '#28a745' : type === 'error' ? '#dc3545' : '#17a2b8';

    const toast = document.createElement('div');
    toast.textContent = message;
    toast.style.cssText = `
        position:fixed; bottom:24px; right:24px; z-index:9999;
        background:${bg}; color:#fff; padding:12px 20px;
        border-radius:6px; box-shadow:0 4px 12px rgba(0,0,0,.2);
        font-size:.9rem; max-width:320px;
    `;

    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}

// ── HELPERS ───────────────────────────────────────────────────────────────────
function esc(str) {
    const d = document.createElement('div');
    d.textContent = String(str ?? '');
    return d.innerHTML;
}