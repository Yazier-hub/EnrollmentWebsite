// Run: node tests/applicant_credit_selection.js
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const source = fs.readFileSync(path.join(__dirname, '../js/pages/enrollments.js'), 'utf8');
const start = source.indexOf('function renderFreshmenSubjectsAutoSelected(');
const end = source.indexOf('\nfunction updateSubjectCount()', start);
assert(start >= 0 && end > start, 'Enrollment renderer must exist');
const elements = Object.fromEntries(['subjectsList', 'selectedSubjectsCount', 'scheduleIdsInput', 'scheduleInfo']
    .map(id => [id, { innerHTML: '', textContent: '', value: '', style: {} }]));
const context = vm.createContext({ document: { getElementById: id => elements[id] } });
vm.runInContext(source.slice(start, end), context);

context.renderFreshmenSubjectsAutoSelected([
    { subject_id: 34, is_credited: true, has_schedule: true, representative_schedule_id: 100 },
    { subject_id: 35, is_credited: false, has_schedule: true, representative_schedule_id: 101 },
    { subject_id: 36, is_credited: true, has_schedule: false },
    { subject_id: 37, is_enrolled: true, has_schedule: true, representative_schedule_id: 102 },
    { subject_id: 38, is_credited: false, has_schedule: false }
]);
assert.equal(elements.scheduleIdsInput.value, '101', 'Only the uncredited, unenrolled schedule may be submitted');
assert.equal(elements.selectedSubjectsCount.textContent, 1);
assert.match(elements.subjectsList.innerHTML, /Credited from previous school: 2/);
assert.match(elements.subjectsList.innerHTML, /No schedule: 1/);

context.renderFreshmenSubjectsAutoSelected([
    { subject_id: 34, is_credited: true, has_schedule: true, representative_schedule_id: 100 }
]);
assert.equal(elements.scheduleIdsInput.value, '', 'All credited subjects must leave no selected schedules');
assert.equal(elements.selectedSubjectsCount.textContent, 0);

context.renderFreshmenSubjectsAutoSelected([
    { subject_id: 35, has_schedule: true, representative_schedule_id: 101 }
]);
assert.equal(elements.scheduleIdsInput.value, '101', 'Ordinary applicant selection must continue to work');
console.log('PASS: 7 applicant credit selection checks');
