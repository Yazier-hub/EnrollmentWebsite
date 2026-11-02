const bellBtn      = document.getElementById('bellBtn');
const bellDropdown = document.getElementById('bellDropdown');
const bellWrapper  = document.getElementById('bellWrapper');
const userBtn      = document.getElementById('userBtn');
const userDropdown = document.getElementById('userDropdown');
const userWrapper  = document.getElementById('userWrapper');
const notifBadge   = document.getElementById('notifBadge');
const markAllRead  = document.querySelector('.mark-all-read');
const notifList    = document.getElementById('notifList');
let notificationRefreshVersion = 0;

// Resolve the endpoint from the enrollment page, including installs in subdirectories.
const NOTIF_URL = (window.NOTIF_ENDPOINT)
    || new URL('ajax/notifications.php', window.location.href).href;

function closeAll() {
    bellDropdown.classList.remove('open');
    userDropdown.classList.remove('open');
}

function openDropdown(dropdown, triggerEl) {
    const sidebar     = document.querySelector('.sidebar');
    const rect        = triggerEl.getBoundingClientRect();
    const sidebarRect = sidebar.getBoundingClientRect();
    dropdown.style.top  = rect.top + 'px';
    dropdown.style.left = (sidebarRect.right + 8) + 'px';
    dropdown.classList.add('open');
}

function updateBadge(count) {
    if (!notifBadge) return;
    if (count > 0) {
        notifBadge.textContent = count > 99 ? '99+' : count;
        notifBadge.classList.remove('hidden');
    } else {
        notifBadge.classList.add('hidden');
    }
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str ?? '';
    return d.innerHTML;
}

const TYPE_ICONS = {
    enrollment:   'fa-solid fa-user-graduate',
    payment:      'fa-solid fa-credit-card',
    announcement: 'fa-solid fa-bullhorn',
    system:       'fa-solid fa-gear',
    reminder:     'fa-solid fa-clock'
};

async function refreshNotifications() {
    if (!notifList) return;
    const version = ++notificationRefreshVersion;
    try {
        const res  = await fetch(NOTIF_URL + '?action=list', {
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) throw new Error('Failed to load notifications');
        const data = await res.json();
        // Ignore responses fetched before a notification was marked as read.
        if (version !== notificationRefreshVersion) return;

        if (!data.items || data.items.length === 0) {
            notifList.innerHTML = '<li class="notif-empty">No unread notifications.</li>';
        } else {
            notifList.innerHTML = data.items.map(n => {
                const icon = TYPE_ICONS[n.type] || 'fa-solid fa-bell';
                return `
                  <li class="notif-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                    <div class="notif-icon notif-type-${escapeHtml(n.type)}">
                      <i class="${icon}"></i>
                    </div>
                    <div class="notif-body">
                      <strong>${escapeHtml(n.title)}</strong>
                      <p>${escapeHtml(n.message)}</p>
                      <span class="notif-time">${escapeHtml(n.time_ago)}</span>
                    </div>
                  </li>`;
            }).join('');
        }
        updateBadge(data.unread_count || 0);
    } catch (err) {
        console.error('Failed to load notifications', err);
    }
}

bellBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = bellDropdown.classList.contains('open');
    closeAll();
    if (!isOpen) {
        openDropdown(bellDropdown, bellBtn);
        refreshNotifications();
    }
});

userBtn.addEventListener('click', function (e) {
    e.stopPropagation();
    const isOpen = userDropdown.classList.contains('open');
    closeAll();
    if (!isOpen) openDropdown(userDropdown, userBtn);
});

document.addEventListener('click', function (e) {
    if (!bellWrapper.contains(e.target) && !userWrapper.contains(e.target)) {
        closeAll();
    }
});

markAllRead?.addEventListener('click', async function (e) {
    e.stopPropagation();
    if (markAllRead.disabled) return;
    markAllRead.disabled = true;
    try {
        const res  = await fetch(NOTIF_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'action=mark_all_read'
        });
        if (!res.ok) throw new Error('Failed to mark notifications as read');
        const data = await res.json();
        if (data.success) {
            ++notificationRefreshVersion;
            notifList.innerHTML = '<li class="notif-empty">No unread notifications.</li>';
            updateBadge(data.unread_count || 0);
            await refreshNotifications();
        }
    } catch (err) {
        console.error(err);
    } finally {
        markAllRead.disabled = false;
    }
});

notifList?.addEventListener('click', async function (e) {
    const item = e.target.closest('.notif-item');
    if (!item || !item.classList.contains('unread') || item.dataset.markingRead) return;

    const id = item.dataset.id;
    if (!id) return;

    item.dataset.markingRead = 'true';
    try {
        const res  = await fetch(NOTIF_URL, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=mark_read&id=${encodeURIComponent(id)}`
        });
        if (!res.ok) throw new Error('Failed to mark notification as read');
        const data = await res.json();
        if (data.success) {
            ++notificationRefreshVersion;
            item.remove();
            if (!notifList.querySelector('.notif-item')) {
                notifList.innerHTML = '<li class="notif-empty">No unread notifications.</li>';
            }
            updateBadge(data.unread_count || 0);
            await refreshNotifications();
        }
    } catch (err) {
        console.error(err);
    } finally {
        delete item.dataset.markingRead;
    }
});

// Periodic refresh (60s) so new notifications appear
setInterval(refreshNotifications, 60000);
