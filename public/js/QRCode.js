const id = k => document.getElementById(k);

// เติมค่าเริ่มต้น
(function initDefaults() {
    const savedBase = localStorage.getItem('qr_base');
    const savedSession = localStorage.getItem('qr_session');
    const savedSize = localStorage.getItem('qr_size');

    id('base').value = savedBase || location.origin;
    id('session').value = savedSession || new Date().toISOString().slice(0, 10);
    id('size').value = savedSize || '320';
})();

function savePrefs() {
    localStorage.setItem('qr_base', id('base').value.trim());
    localStorage.setItem('qr_session', id('session').value);
    localStorage.setItem('qr_size', id('size').value);
}

function normalizeBase(base) {
    // ตัดช่องว่าง, ถ้าไม่มี protocol ใส่ https:// ให้
    let b = (base || '').trim();
    if (!b) return '';
    if (!/^https?:\/\//i.test(b)) b = 'https://' + b;
    // ลบ # และ query ทิ้ง
    try {
        const u = new URL(b);
        u.hash = ''; u.search = '';
        return u.toString().replace(/\/+$/, ''); // ตัด / ท้าย
    } catch { return ''; }
}

function buildChooseUrl(base, session) {
    const safeBase = normalizeBase(base);
    if (!safeBase) return '';
    try {
        const u = new URL('/choose.html', safeBase + '/');
        if (session) u.searchParams.set('session', session);
        return u.toString();
    } catch { return ''; }
}

function setStatus(ok, msg) {
    const el = id('status');
    el.textContent = msg || (ok ? 'พร้อม' : 'ตรวจสอบค่า');
    el.className = 'badge ' + (ok ? 'ok' : 'err');
}

function renderQR(text, size) {
    const box = id('qr');
    box.innerHTML = '';
    if (!text) {
        setStatus(false, 'Base URL ไม่ถูกต้อง');
        id('url').textContent = '';
        id('url').title = '';
        id('open').href = '#';
        id('open').style.pointerEvents = 'none';
        return;
    }
    new QRCode(box, { text, width: size, height: size, correctLevel: QRCode.CorrectLevel.M });
    id('url').textContent = text;
    id('url').title = text;
    id('open').href = text;
    id('open').style.pointerEvents = 'auto';
    setStatus(true, 'ลิงก์พร้อมใช้งาน');
}

function gen() {
    savePrefs();
    const base = id('base').value;
    const session = id('session').value;
    const size = parseInt(id('size').value, 10) || 320;
    const url = buildChooseUrl(base, session);
    renderQR(url, size);
}

function downloadPNG() {
    const img = id('qr').querySelector('img');
    const canvas = id('qr').querySelector('canvas');
    const data = img?.src || canvas?.toDataURL('image/png');
    if (!data) {
        alert('ยังไม่มีภาพ QR');
        return;
    }
    const nameDate = (id('session').value || 'session').replaceAll(/[^0-9\-]/g, '');
    const a = document.createElement('a');
    a.href = data;
    a.download = `qr-choose-${nameDate}.png`;
    a.click();
}

async function copyLink() {
    const txt = id('url').textContent.trim();
    if (!txt) return;
    try {
        await navigator.clipboard.writeText(txt);
        setStatus(true, 'คัดลอกลิงก์แล้ว');
        setTimeout(() => setStatus(true, 'ลิงก์พร้อมใช้งาน'), 1200);
    } catch {
        setStatus(false, 'คัดลอกไม่สำเร็จ');
    }
}

// events
['base', 'session', 'size'].forEach(k => {
    id(k).addEventListener('input', gen);
    id(k).addEventListener('change', gen);
});
id('btnGen').addEventListener('click', gen);
id('btnDL').addEventListener('click', downloadPNG);
id('btnCopy').addEventListener('click', copyLink);

// สร้างครั้งแรก
window.addEventListener('load', gen);