(function () {
    // ====== ตั้งค่าปลายทาง SSO / auth ======
    // ตัวอย่าง: ชี้ไปยัง endpoint ฝั่งเซิร์ฟเวอร์ของคุณ
    const BASE_AUTH_URL = '/auth/sso'; // เปลี่ยนเป็นปลายทางจริงของระบบคุณ เช่น '/auth.php' หรือ '/sso/login'

    // ====== ตัวช่วย ======
    const qs = (sel, el = document) => el.querySelector(sel);
    const qsa = (sel, el = document) => Array.from(el.querySelectorAll(sel));
    const buildUrl = (base, params) => {
        const u = new URL(base, window.location.origin);
        Object.entries(params).forEach(([k, v]) => {
            if (v !== undefined && v !== null) u.searchParams.set(k, v);
        });
        return u.toString();
    };
    const redirectWithRole = (role) => {
        // ส่ง role + next (ลิงก์กลับมาหน้าเดิม)
        const next = window.location.href;
        const url = buildUrl(BASE_AUTH_URL, { role, next });
        window.location.href = url;
    };

    // ====== โครงแท็บ ======
    const tabs = [
        { tab: qs('#tab-staff'), panel: qs('#panel-staff') },
        { tab: qs('#tab-person'), panel: qs('#panel-person') }
    ];

    function activate(index) {
        tabs.forEach((t, i) => {
            const active = i === index;
            t.tab?.setAttribute('aria-selected', active ? 'true' : 'false');
            t.tab?.setAttribute('aria-pressed', active ? 'true' : 'false');
            if (t.tab) t.tab.tabIndex = active ? 0 : -1;

            if (t.panel) {
                t.panel.dataset.active = active ? 'true' : 'false';
                if (active) t.panel.removeAttribute('hidden');
                else t.panel.setAttribute('hidden', '');
            }
        });
        tabs[index].tab?.focus();
    }

    // คลิกแท็บ
    tabs.forEach((t, i) => {
        t.tab?.addEventListener('click', () => activate(i));
    });

    // ซัพพอร์ตคีย์บอร์ด
    const tablist = qs('[role="tablist"]');
    tablist?.addEventListener('keydown', (e) => {
        const currentIndex = tabs.findIndex(t => t.tab?.getAttribute('aria-selected') === 'true');
        if (e.key === 'ArrowRight') {
            e.preventDefault(); activate((currentIndex + 1) % tabs.length);
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault(); activate((currentIndex - 1 + tabs.length) % tabs.length);
        } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault(); activate(currentIndex);
        }
    });

    // ====== ปุ่มเข้าสู่ระบบ (พร้อมกันกดซ้ำ) ======
    const btnStaff = qs('#login-staff');
    const btnPerson = qs('#login-person');

    const handleLogin = (role, btn) => {
        if (!btn) return;
        // กันกดซ้ำ
        btn.disabled = true;
        btn.setAttribute('aria-busy', 'true');
        try {
            redirectWithRole(role);
        } finally {
            // ถ้าโดนบล็อก popup/redirect ปุ่มจะกลับมาใช้ได้อีกครั้ง
            setTimeout(() => {
                btn.disabled = false;
                btn.removeAttribute('aria-busy');
            }, 3000);
        }
    };

    btnStaff?.addEventListener('click', () => handleLogin('staff', btnStaff));
    btnPerson?.addEventListener('click', () => handleLogin('person', btnPerson));

    // ลืมรหัสผ่าน
    qs('#forgot-link')?.addEventListener('click', (e) => {
        e.preventDefault();
        window.location.href = 'https://password.up.ac.th/';
    });

    // ค่าเริ่มต้น (ให้แท็บ staff แสดงก่อนตาม HTML เดิม)
    activate(0);
})();