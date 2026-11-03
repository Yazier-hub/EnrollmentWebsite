document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const sidebar = document.querySelector('.sidebar');
    const mainContent = document.querySelector('.main-content');
    const footer = document.querySelector('footer');
    const header = document.querySelector('header.top-bar');

    if (!hamburger || !sidebar) return;

    let overlay = document.querySelector('.sidebar-overlay');
    if (!overlay) {
        overlay = document.createElement('div');
        overlay.className = 'sidebar-overlay';
        document.body.appendChild(overlay);
    }

    const mobileQuery = window.matchMedia('(max-width: 768px)');
    let desktopHidden = sidebar.classList.contains('hidden');
    let mobileOpen = false;

    function updateSidebar() {
        const mobile = mobileQuery.matches;
        const expanded = mobile ? mobileOpen : !desktopHidden;

        sidebar.classList.toggle('active', mobile && mobileOpen);
        sidebar.classList.toggle('hidden', !mobile && desktopHidden);
        sidebar.inert = !expanded;
        overlay.classList.toggle('active', mobile && mobileOpen);
        hamburger.classList.toggle('active', mobile ? mobileOpen : desktopHidden);
        hamburger.setAttribute('aria-expanded', String(expanded));
        hamburger.setAttribute('aria-label', expanded ? 'Close navigation' : 'Open navigation');

        // Clear overrides when expanded so the layout uses its CSS sidebar width.
        const collapsed = !mobile && desktopHidden;
        if (mainContent) mainContent.style.marginLeft = collapsed ? '0' : '';
        if (footer) footer.style.marginLeft = collapsed ? '0' : '';
        if (header) {
            header.style.left = collapsed ? '0' : '';
            header.style.width = collapsed ? '100%' : '';
        }
    }

    function closeMobileSidebar() {
        if (!mobileQuery.matches || !mobileOpen) return;
        if (sidebar.contains(document.activeElement)) hamburger.focus();
        mobileOpen = false;
        updateSidebar();
    }

    hamburger.addEventListener('click', function() {
        if (mobileQuery.matches) {
            mobileOpen = !mobileOpen;
        } else {
            desktopHidden = !desktopHidden;
        }
        updateSidebar();
    });

    overlay.addEventListener('click', closeMobileSidebar);

    sidebar.addEventListener('click', function(event) {
        if (event.target.closest('a[href]')) closeMobileSidebar();
    });

    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && mobileQuery.matches && mobileOpen) {
            closeMobileSidebar();
            hamburger.focus();
        }
    });

    mobileQuery.addEventListener('change', function() {
        mobileOpen = false;
        if ((mobileQuery.matches || desktopHidden) && sidebar.contains(document.activeElement)) {
            hamburger.focus();
        }
        updateSidebar();
    });

    updateSidebar();
});
