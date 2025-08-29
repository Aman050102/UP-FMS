// สคริปต์เล็ก ๆ สำหรับสลับแท็บและโฟกัสเพื่อการเข้าถึง (accessibility)
const tabs = [
    { btn: document.getElementById('tab-staff'), panel: document.getElementById('panel-staff') },
    { btn: document.getElementById('tab-person'), panel: document.getElementById('panel-person') },
];

function setActive(index) {
    tabs.forEach((t, i) => {
        const active = i === index;
        t.btn.setAttribute('aria-pressed', active);
        t.panel.dataset.active = active;
        t.panel.setAttribute('aria-hidden', !active);
    });
}

tabs.forEach((t, i) => {
    t.btn.addEventListener('click', () => setActive(i));
    t.btn.addEventListener('keydown', (e) => {
        if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
            const dir = e.key === 'ArrowRight' ? 1 : -1;
            const next = (i + dir + tabs.length) % tabs.length;
            setActive(next); tabs[next].btn.focus();
        }
    });
});

// ปุ่มเข้าสู่ระบบ – เปลี่ยนเป็นลิงก์จริงของ UP SSO ได้ตามระบบ
document.getElementById('login-staff').addEventListener('click', () => {
    // location.href = 'https://sso.up.ac.th/...';
    alert('เข้าสู่ระบบ (สำหรับเจ้าหน้าที่) – โปรดเชื่อมต่อ URL SSO จริง');
});
document.getElementById('login-person').addEventListener('click', () => {
    // location.href = 'https://sso.up.ac.th/...';
    alert('เข้าสู่ระบบ (สำหรับบุคลากร/นิสิต) – โปรดเชื่อมต่อ URL SSO จริง');
});

// ลิงก์ลืมรหัสผ่าน
document.getElementById('forgot-link').addEventListener('click', (e) => {
    e.preventDefault();
    alert('ไปยังหน้าลืมรหัสผ่าน – ใส่ URL จริงของระบบกู้คืนรหัสผ่าน');
});