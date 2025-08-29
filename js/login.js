(function () {
    const tabs = [
        { tab: document.getElementById('tab-staff'), panel: document.getElementById('panel-staff') },
        { tab: document.getElementById('tab-person'), panel: document.getElementById('panel-person') }
    ];

    function activate(index) {
        tabs.forEach((t, i) => {
            const active = i === index;
            t.tab.setAttribute('aria-selected', active ? 'true' : 'false');
            t.tab.setAttribute('aria-pressed', active ? 'true' : 'false');
            t.tab.tabIndex = active ? 0 : -1;

            t.panel.dataset.active = active ? 'true' : 'false';
            if (active) {
                t.panel.removeAttribute('hidden');
            } else {
                t.panel.setAttribute('hidden', '');
            }
        });
        tabs[index].tab.focus();
    }

    // คลิกแท็บ
    tabs.forEach((t, i) => {
        t.tab.addEventListener('click', () => activate(i));
    });

    // รองรับคีย์บอร์ดซ้าย/ขวา + Enter/Space
    document.querySelector('[role="tablist"]').addEventListener('keydown', (e) => {
        const currentIndex = tabs.findIndex(t => t.tab.getAttribute('aria-selected') === 'true');
        if (e.key === 'ArrowRight') {
            e.preventDefault();
            activate((currentIndex + 1) % tabs.length);
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            activate((currentIndex - 1 + tabs.length) % tabs.length);
        } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            activate(currentIndex);
        }
    });

    // ตัวอย่าง: ต่อกับระบบ SSO (แทนที่ URL จริงภายหลัง)
    document.getElementById('login-staff')?.addEventListener('click', () => {
        // window.location.href = '/auth/sso?role=staff';
        console.log('SSO: staff');
    });

    document.getElementById('login-person')?.addEventListener('click', () => {
        // window.location.href = '/auth/sso?role=person';
        console.log('SSO: person');
    });

    document.getElementById('forgot-link')?.addEventListener('click', (e) => {
        e.preventDefault();
        // เปลี่ยนเป็นลิงก์หน้าตั้งรหัสผ่านใหม่ของ UP
        window.location.href = 'https://password.up.ac.th/';
    });
})();