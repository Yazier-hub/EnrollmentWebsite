const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

class Element {
    constructor(attributes = {}, display = 'none') {
        this.attributes = attributes;
        this.style = {};
        this.cssDisplay = display;
    }
    getAttribute(name) { return this.attributes[name] ?? null; }
    closest(selector) { return this.getAttribute(selector.slice(1, -1)) === null ? null : this; }
    scrollIntoView() {}
}

const nodes = Object.fromEntries(['detailsView', 'editForm', 'editToggleBtn', 'convertForm', 'rejectForm'].map(id => [id, new Element()]));
const listeners = {};
const context = vm.createContext({
    Element,
    document: {
        getElementById: id => nodes[id],
        querySelectorAll: () => [],
        addEventListener: (event, callback) => { (listeners[event] ||= []).push(callback); }
    },
    getComputedStyle: element => ({ display: element.style.display || element.cssDisplay }),
    confirm: () => false
});
context.window = context;
for (const file of ['js/page-ui.js', 'js/pages/application-details.js']) {
    vm.runInContext(fs.readFileSync(path.join(__dirname, '..', file), 'utf8'), context, { filename: file });
}

// External CSS hides forms initially; element.style.display is initially empty.
context.toggleEdit();
assert.equal(nodes.editForm.style.display, 'block');
context.toggleEdit();
assert.equal(nodes.editForm.style.display, 'none');
context.toggleConvert();
assert.equal(nodes.convertForm.style.display, 'block');
context.showRejectForm();
assert.equal(nodes.rejectForm.style.display, 'block');
for (const listener of listeners.keydown) listener({ key: 'Escape' });
assert.equal(nodes.convertForm.style.display, 'none');
assert.equal(nodes.rejectForm.style.display, 'none');

const button = new Element({ 'data-action-click': 'action1' });
for (const listener of listeners.click) listener({ target: button, preventDefault() {} });
assert.equal(nodes.editForm.style.display, 'block');
let prevented = false;
const form = new Element({ 'data-action-submit': 'action4' });
for (const listener of listeners.submit) listener({ target: form, preventDefault() { prevented = true; } });
assert.ok(prevented, 'Cancelled confirmation must prevent form submission');
console.log('PASS: CSS-hidden panels, modal toggles, Escape, delegated clicks, and cancelled submissions.');
