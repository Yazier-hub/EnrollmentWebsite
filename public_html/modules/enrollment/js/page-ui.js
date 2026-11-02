/** Shared binding helpers for page scripts. No executable code is read from HTML. */
window.PageUI = {
    bind(eventName, handlers) {
        const attribute = 'data-action-' + eventName;
        document.addEventListener(eventName, function (event) {
            if (!(event.target instanceof Element)) return;
            const element = event.target.closest('[' + attribute + ']');
            if (!element) return;
            const handler = handlers[element.getAttribute(attribute)];
            if (handler && handler.call(element, event) === false) event.preventDefault();
        });
    }
};

// Only presentation values vary per record; the CSS rules live in page-utilities.css.
for (const property of ['width', 'background', 'color', 'border-left-color']) {
    document.querySelectorAll('[data-css-' + property + ']').forEach(element => {
        element.style.setProperty('--page-' + property, element.getAttribute('data-css-' + property));
    });
}
