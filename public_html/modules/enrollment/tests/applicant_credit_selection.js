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

// The modal must send applicant identity and display the server-computed placement.
async function checkPlacementRequest() {
    const start = source.indexOf('function loadAvailableSections(');
    const end = source.indexOf('\nfunction loadSubjectsForSection(', start);
    assert(start >= 0 && end > start);
    const elements = {
        sectionSelect: { innerHTML: '', appendChild() {} },
        sectionInfo: { innerHTML: '', style: {} },
        subjectsSection: { style: {} }
    };
    let requestedUrl;
    let selectedSection;
    let response = { success: true, year_level: 2, semester: 1,
        data: [{ id: 43, section_code: 'YEAR2', grade_level: '2nd Year', semester: '1st Semester' }] };
    const context = vm.createContext({
        baseUrl: '/enrollment', currentApplicantId: 101,
        document: { getElementById: id => elements[id], createElement: () => ({}) },
        fetch: async url => { requestedUrl = url; return { ok: true, json: async () => response }; },
        loadSubjectsForSection: id => { selectedSection = id; }, console
    });
    vm.runInContext(source.slice(start, end), context);
    context.loadAvailableSections(10, 1, 1);
    await new Promise(resolve => setImmediate(resolve));
    assert.match(requestedUrl, /applicant_id=101/);
    assert.match(elements.sectionInfo.innerHTML, /2nd Year - 1st Semester/);
    assert.equal(selectedSection, 43);
    selectedSection = null;
    response = { success: true, data: [], placement: { curriculum_completed: true }, message: 'All curriculum subjects are already credited.' };
    context.loadAvailableSections(10, 1, 1);
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(selectedSection, null);
    assert.equal(elements.sectionInfo.textContent, response.message);
    assert.equal(elements.subjectsSection.style.display, 'none');
    console.log('PASS: applicant placement request and completed-curriculum display');
}
checkPlacementRequest().catch(error => { console.error(error); process.exitCode = 1; });
