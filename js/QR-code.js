const $ = (s, p = document) => p.querySelector(s); const $$ = (s, p = document) => Array.from(p.querySelectorAll(s));
const FAC = { outdoor: 'สนามกลางแจ้ง', badminton: 'แบดมินตัน', pool: 'สระว่ายน้ำ', track: 'ลู่-ลาน' };
const id = (k) => document.getElementById(k);

// ตั้งค่าวันนี้เป็นค่าเริ่มต้นของ session
(function setToday() { const d = new Date(); id('session').value = d.toISOString().slice(0, 10); })();

// เลือกสนาม (ชิป)
let selected = 'outdoor';
$$('#chips .chip').forEach(ch => { ch.addEventListener('click', () => { selected = ch.dataset.k; $$('#chips .chip').forEach(x => x.classList.remove('selected')); ch.classList.add('selected'); }); });
const def = $('#chips .chip'); def && def.click();

function uuid() { return ([1e7] + -1e3 + -4e3 + -8e3 + -1e11).replace(/[018]/g, c => (c ^ crypto.getRandomValues(new Uint8Array(1))[0] & 15 >> c / 4).toString(16)); }

function buildUrl(base, facility, session) {
    const u = new URL(base || 'https://example.com/checkin');
    u.searchParams.set('facility', facility);
    u.searchParams.set('session', session);
    u.searchParams.set('nonce', uuid()); // ใช้ครั้งเดียว
    return u.toString();
}

function renderQR(container, text, size) {
    container.innerHTML = ''; new QRCode(container, { text, width: size, height: size, correctLevel: QRCode.CorrectLevel.M });
}

function dataURLOf(container) { const img = container.querySelector('img'); const canvas = container.querySelector('canvas'); return img?.src || canvas?.toDataURL('image/png') || null; }
function download(container, filename) { const data = dataURLOf(container); if (!data) return alert('ไม่มีภาพ'); const a = document.createElement('a'); a.href = data; a.download = filename; a.click(); }

function genOne(key) {
    const base = id('base').value.trim(); const session = id('session').value; const size = parseInt(id('size').value, 10) || 320;
    const url = buildUrl(base, key, session);
    renderQR(id('qr-' + key), url, size);
    id('url-' + key).textContent = url;
}

id('btnGen').addEventListener('click', () => genOne(selected));
id('btnGenAll').addEventListener('click', () => ['outdoor', 'badminton', 'pool', 'track'].forEach(genOne));
$$('.dl').forEach(btn => btn.addEventListener('click', () => download(id('qr-' + btn.dataset.k), `qr-${btn.dataset.k}.png`)));

// เรนเดอร์ชุดแรกตอนโหลด
['outdoor', 'badminton', 'pool', 'track'].forEach(genOne);