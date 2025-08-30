const $ = id => document.getElementById(id);

function normalizeBase(base) {
    let b = (base || '').trim(); if (!b) return '';
    if (!/^https?:\/\//i.test(b)) b = 'https://' + b;
    try { const u = new URL(b); u.hash = ''; u.search = ''; return u.toString().replace(/\/+$/, ''); }
    catch { return ''; }
}

function buildUrl(base, session) {
    const safeBase = normalizeBase(base); if (!safeBase) return '';
    const next = '/choose.php?session=' + encodeURIComponent(session || '');
    const u = new URL('/auth/auth.php', safeBase + '/');
    u.searchParams.set('role', 'person');
    u.searchParams.set('next', next);
    return u.toString();
}

function renderQR(text, size) {
    const box = $('qr'); box.innerHTML = '';
    if (!text) { $('status').textContent = 'Base URL ไม่ถูกต้อง'; $('status').className = 'badge err'; return; }
    new QRCode(box, { text, width: +size, height: +size });
    $('url').textContent = text; $('open').href = text;
    $('status').textContent = 'ลิงก์พร้อมใช้งาน'; $('status').className = 'badge ok';
}

function gen() {
    const base = $('base').value, session = $('session').value, size = $('size').value;
    renderQR(buildUrl(base, session), size);
}

$('btnGen').onclick = gen;
window.onload = () => { $('session').value = new Date().toISOString().slice(0, 10); gen(); };