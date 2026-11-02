/* Dependency-free layout smoke check using a running Chromium DevTools endpoint. */
const fs = require('node:fs');
const assert = require('node:assert/strict');
const base = process.env.TEST_BASE_URL || 'http://127.0.0.1:8095';

async function main() {
    const tabs = await (await fetch('http://127.0.0.1:9223/json/list')).json();
    const socket = new WebSocket(tabs.find(tab => tab.type === 'page').webSocketDebuggerUrl);
    await new Promise(resolve => socket.addEventListener('open', resolve, { once: true }));
    let serial = 0;
    const pending = new Map();
    let errors = [];
    socket.addEventListener('message', event => {
        const message = JSON.parse(event.data);
        if (message.id) {
            const task = pending.get(message.id);
            if (task) {
                clearTimeout(task.timeout);
                pending.delete(message.id);
                message.error ? task.reject(message.error) : task.resolve(message.result);
            }
        }
        if (message.method === 'Runtime.exceptionThrown') {
            errors.push(message.params.exceptionDetails.exception?.description || message.params.exceptionDetails.text);
        }
    });
    const send = (method, params = {}) => new Promise((resolve, reject) => {
        const id = ++serial;
        const timeout = setTimeout(() => { pending.delete(id); reject(new Error('Timed out: ' + method)); }, 20000);
        pending.set(id, { resolve, reject, timeout });
        socket.send(JSON.stringify({ id, method, params }));
    });
    const evaluate = async expression => {
        const result = await send('Runtime.evaluate', { expression, returnByValue: true, awaitPromise: true });
        if (result.exceptionDetails) throw new Error(result.exceptionDetails.text);
        return result.result.value;
    };
    const navigate = async page => {
        errors = [];
        await send('Page.navigate', { url: base + '/pages/' + page });
        const destination = page === 'enroll.html' ? 'online-admission.html' : page;
        for (let attempt = 0; attempt < 80; attempt++) {
            await new Promise(resolve => setTimeout(resolve, 250));
            if (await evaluate(`document.readyState === 'complete' && location.pathname.endsWith(${JSON.stringify(destination)})`)) return;
        }
        throw new Error('Page did not finish loading: ' + page);
    };

    try {
        await send('Page.enable');
        await send('Runtime.enable');
        await send('Emulation.setEmulatedMedia', { features: [{ name: 'prefers-reduced-motion', value: 'reduce' }] });
        // Only select a course in this browser session; never submit an applicant/contact record.
        await navigate('selecting-course.php');
        const selected = await evaluate(`(async () => {
            const input = document.querySelector('input[name="course_id"]');
            if (!input) return false;
            await fetch('selecting-course.php', {method:'POST', body:new URLSearchParams({course_id:input.value})});
            return true;
        })()`);
        console.log('Enrollment session available:', selected);

        const pages = fs.readdirSync('pages').filter(file => /\.(html|php)$/.test(file));
        for (const page of pages) {
            if (page === 'enrollment-form.php' && !selected) continue;
            await navigate(page);
            assert.deepEqual(errors, [], page + ' runtime errors');
            const loaded = await evaluate(`({vue: typeof Vue !== 'undefined', tailwind: [...document.styleSheets].some(s=>s.href?.endsWith('/utilities.css') && s.cssRules.length > 0), brokenImages: [...document.images].filter(i=>!i.closest('.leaflet-container') && (!i.complete || i.naturalWidth===0)).map(i=>i.getAttribute('src'))})`);
            assert.equal(loaded.tailwind, true, page + ' Tailwind unavailable');
            assert.deepEqual(loaded.brokenImages, [], page + ' broken images');
            for (const width of [320, 375, 768, 1024, 1440]) {
                await send('Emulation.setDeviceMetricsOverride', { width, height: 900, deviceScaleFactor: 1, mobile: false });
                await evaluate('new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve)))');
                const layout = await evaluate(`({width:innerWidth, scroll:document.documentElement.scrollWidth, offenders:[...document.querySelectorAll('body *')].filter(e=>{const r=e.getBoundingClientRect();return r.width && (r.right>innerWidth+1 || r.left< -1) && getComputedStyle(e).position!=='absolute' && !e.closest('.leaflet-container')}).slice(0,5).map(e=>({tag:e.tagName,classes:e.className}))})`);
                assert.ok(layout.scroll <= width + 1, page + ' overflow at ' + width + ': ' + JSON.stringify(layout));
                const menu = await evaluate(`(() => {
                    const button=document.querySelector('.site-menu-toggle');
                    if(!button || innerWidth>=1200) return true;
                    button.click();
                    const open=button.getAttribute('aria-expanded')==='true' && getComputedStyle(document.querySelector('#primary-navigation')).display!=='none';
                    document.dispatchEvent(new KeyboardEvent('keydown',{key:'Escape'}));
                    return open && button.getAttribute('aria-expanded')==='false';
                })()`);
                assert.ok(menu, page + ' mobile menu');
            }
            console.log('PASS', page, '320/375/768/1024/1440px');
        }
        await navigate('enrollment-form.php');
        if (selected) {
            const result = await evaluate(`(async () => {
                const steps=document.querySelectorAll('.step-indicator button');
                steps[3].click();
                await Vue.nextTick();
                return {blocked:document.querySelectorAll('.form-section')[0].classList.contains('active'), invalid:document.querySelector('[aria-invalid="true"]')!==null, labels:[...document.querySelectorAll('label[for]')].every(l=>document.getElementById(l.htmlFor))};
            })()`);
            assert.deepEqual(result, { blocked: true, invalid: true, labels: true });
            console.log('PASS enrollment step validation and label targets');

            const progress = await evaluate(`(async () => {
                const values = {admission_type:'returnee',surname:"O'Connor",first_name:'Test',sex:'Female',civil_status:'Single',date_of_birth:'2000-01-01',place_of_birth:'Manila',email:'invalid',contact_number:'09123456789',address_street:'12 Test Street',address_barangay:'Sample',address_city:'Quezon City',address_province:'Metro Manila',parent_full_name:'Test Guardian',school_last_attended:'Test School',year_graduated:'2025'};
                for(const [name,value] of Object.entries(values)) {
                    const input=document.querySelector('[name="'+name+'"]');
                    input.value=value;
                    input.dispatchEvent(new Event('input',{bubbles:true}));
                    input.dispatchEvent(new Event('change',{bubbles:true}));
                }
                await Vue.nextTick();
                const steps=document.querySelectorAll('.step-indicator button');
                steps[1].click();
                await Vue.nextTick();
                const emailBlocked=document.querySelectorAll('.form-section')[0].classList.contains('active');
                const email=document.querySelector('[name="email"]');
                email.value='test@example.com';
                email.dispatchEvent(new Event('input',{bubbles:true}));
                steps[3].click();
                await Vue.nextTick();
                const summary=document.querySelectorAll('.form-section')[3].classList.contains('active');
                const file=document.querySelector('input[type="file"]');
                const transfer=new DataTransfer();
                transfer.items.add(new File(['invalid'], 'bad.exe', {type:'application/octet-stream'}));
                file.files=transfer.files;
                file.dispatchEvent(new Event('change',{bubbles:true}));
                await Vue.nextTick();
                return {emailBlocked, summary, uploadBlocked:!file.checkValidity(), address:document.querySelector('[name="address_complete"]').value};
            })()`);
            assert.deepEqual(progress, {emailBlocked:true, summary:true, uploadBlocked:true, address:'12 Test Street, Brgy. Sample, Quezon City, Metro Manila'});
            console.log('PASS invalid email, manual address, step progression, and invalid upload');

            fs.mkdirSync('tests/artifacts', { recursive: true });
            await send('Emulation.setDeviceMetricsOverride', { width: 375, height: 900, deviceScaleFactor: 1, mobile: false });
            await evaluate(`document.querySelector('.step-indicator button').click(); new Promise(resolve=>requestAnimationFrame(()=>{document.querySelector('.form-container').scrollIntoView();window.scrollBy(0,-90);resolve();}))`);
            const mobile = await send('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('tests/artifacts/enrollment-mobile.png', Buffer.from(mobile.data, 'base64'));
            await navigate('index.html');
            await send('Emulation.setDeviceMetricsOverride', { width: 1440, height: 1000, deviceScaleFactor: 1, mobile: false });
            await evaluate('window.scrollTo(0,0); new Promise(resolve=>requestAnimationFrame(resolve))');
            const desktop = await send('Page.captureScreenshot', { format: 'png' });
            fs.writeFileSync('tests/artifacts/home-desktop.png', Buffer.from(desktop.data, 'base64'));
        }
    } finally {
        socket.close();
    }
}
main().catch(error => { console.error(error); process.exitCode = 1; });
