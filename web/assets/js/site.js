(() => {
    'use strict';

    class SiteNavigation {
        constructor(header) {
            this.header = header;
            this.menu = header.querySelector('nav');
            this.toggle = header.querySelector('.site-menu-toggle');
            this.mobile = window.matchMedia('(max-width: 1199px)');
        }

        init() {
            if (!this.menu || !this.toggle) return;
            this.header.classList.add('nav-enhanced');
            this.toggle.addEventListener('click', () => this.setOpen(!this.isOpen));
            this.menu.addEventListener('click', event => {
                if (event.target.closest('a')) this.setOpen(false);
            });
            document.addEventListener('keydown', event => {
                if (event.key === 'Escape' && this.isOpen) {
                    this.setOpen(false);
                    this.toggle.focus();
                }
            });
            document.addEventListener('click', event => {
                if (!this.header.contains(event.target)) this.setOpen(false);
            });
            this.mobile.addEventListener('change', () => this.setOpen(false));
            this.setOpen(false);
            const current = window.location.pathname.split('/').pop() || 'index.html';
            this.menu.querySelectorAll('a').forEach(link => {
                if (link.getAttribute('href') === current) link.setAttribute('aria-current', 'page');
            });
        }

        setOpen(open) {
            this.isOpen = open && this.mobile.matches;
            this.header.classList.toggle('is-menu-open', this.isOpen);
            this.toggle.setAttribute('aria-expanded', String(this.isOpen));
            this.toggle.setAttribute('aria-label', this.isOpen ? 'Close navigation menu' : 'Open navigation menu');
        }
    }

    document.querySelectorAll('.site-header').forEach(header => new SiteNavigation(header).init());

    // JSON is data only; PHP never writes executable JavaScript.
    const dataElement = document.getElementById('page-data');
    if (dataElement) {
        const config = JSON.parse(dataElement.textContent);
        if (config.redirectUrl) {
            const destination = new URL(config.redirectUrl, window.location.href);
            if (destination.origin === window.location.origin) {
                window.setTimeout(() => window.location.assign(destination.href), config.redirectDelay ?? 1500);
            }
        }
    }
})();
