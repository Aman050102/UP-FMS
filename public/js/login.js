
(function () {
  // ✅ ใช้ route Laravel (จะได้ base URL ถูกเสมอ)
  const BASE_AUTH_URL = "{{ route('auth.redirect') }}";

  const qs  = (s, el=document) => el.querySelector(s);
  const buildUrl = (base, params) => {
    const u = new URL(base, window.location.origin);
    Object.entries(params).forEach(([k,v]) => (v!=null) && u.searchParams.set(k,v));
    return u.toString();
  };
  const redirectWithRole = (role) => {
    const next = window.location.href;                   // ต้องการส่ง next ก็ได้
    const url  = buildUrl(BASE_AUTH_URL, { role, next }); // หรือจะไม่ส่ง next ก็ได้
    window.location.href = url;
  };

  // ====== โค้ดแท็บ (ตามเดิม) ======
  const tabs = [
    { tab: qs('#tab-staff'), panel: qs('#panel-staff') },
    { tab: qs('#tab-person'), panel: qs('#panel-person') }
  ];
  function activate(index){
    tabs.forEach((t,i)=>{
      const active = i===index;
      t.tab?.setAttribute('aria-selected', active?'true':'false');
      t.tab?.setAttribute('aria-pressed',  active?'true':'false');
      if (t.tab) t.tab.tabIndex = active?0:-1;
      if (t.panel){
        t.panel.dataset.active = active?'true':'false';
        active ? t.panel.removeAttribute('hidden') : t.panel.setAttribute('hidden','');
      }
    });
    tabs[index].tab?.focus();
  }
  tabs.forEach((t,i)=> t.tab?.addEventListener('click', ()=>activate(i)));
  const tablist = qs('[role="tablist"]');
  tablist?.addEventListener('keydown', (e)=>{
    const idx = tabs.findIndex(t=>t.tab?.getAttribute('aria-selected')==='true');
    if (e.key==='ArrowRight'){ e.preventDefault(); activate((idx+1)%tabs.length); }
    else if (e.key==='ArrowLeft'){ e.preventDefault(); activate((idx-1+tabs.length)%tabs.length); }
    else if (e.key==='Enter' || e.key===' '){ e.preventDefault(); activate(idx); }
  });

  // ====== ปุ่มเข้าสู่ระบบแบบสองปุ่ม ======
  const btnStaff  = qs('#login-staff');
  const btnPerson = qs('#login-person');

  const handleLogin = (role, btn) => {
    if (!btn) return;
    btn.disabled = true; btn.setAttribute('aria-busy','true');
    try { redirectWithRole(role); }
    finally {
      setTimeout(()=>{ btn.disabled=false; btn.removeAttribute('aria-busy'); }, 3000);
    }
  };

  btnStaff?.addEventListener('click',  ()=>handleLogin('staff',  btnStaff));
  btnPerson?.addEventListener('click', ()=>handleLogin('person', btnPerson));

  // ลืมรหัสผ่าน
  qs('#forgot-link')?.addEventListener('click', (e)=>{
    e.preventDefault();
    window.location.href = 'https://password.up.ac.th/';
  });

  activate(0); // staff เป็นค่าเริ่มต้น
})();
