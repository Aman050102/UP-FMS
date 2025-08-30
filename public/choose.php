<?php
// ต้องล็อกอินก่อนเสมอ (SSO) — อ่าน role/โปรไฟล์จาก session
require __DIR__ . '/../middlewares/require_person.php';
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />
  <link rel="icon" href="/img/Logo_of_University_of_Phayao.svg.png" type="image/png">
  <title>UP-FMS เช็คอิน</title>
  <meta name="color-scheme" content="light dark">
  <style>
    :root{
      --bg:#f6f5fb; --card:#fff; --text:#1b1a21; --muted:#6b657a; --divider:#e7e3ef;
      --shadow:0 12px 30px rgba(30,16,60,.12); --brand:#6f4ab4; --hbar:68px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;
      font-family:ui-sans-serif,system-ui,-apple-system,"Noto Sans Thai",Roboto,Arial,sans-serif;
      color:var(--text);
      background:linear-gradient(180deg,var(--brand) 0 var(--hbar),var(--bg) var(--hbar) 100%);
    }

    /* โครงหลัก: ใช้ 100dvh ให้พอดีกับแถบ dynamic บนมือถือ + safe-area */
    .wrap{
      min-height:100dvh;
      padding:env(safe-area-inset-top) env(safe-area-inset-right)
              env(safe-area-inset-bottom) env(safe-area-inset-left);
      display:flex; flex-direction:column;
    }
    header{
      height:var(--hbar); display:flex; align-items:center; gap:12px;
      padding:0 clamp(14px,4vw,22px); color:#fff; font-weight:900; background:transparent;
      flex:0 0 auto;
    }
    header img{height:46px; width:auto; object-fit:contain}

    main{
      flex:1 1 auto;
      display:flex; flex-direction:column; gap:14px;
      width:100%; max-width:1040px;
      margin:0 auto;
      padding:14px clamp(12px,4vw,24px) 24px;
    }

    /* การ์ด/ฟอร์ม */
    .card{
      background:var(--card); border-radius:18px; box-shadow:var(--shadow);
      padding:clamp(12px,2.4vw,18px); width:100%;
    }
    .hint{color:var(--muted); font-size:clamp(13px,2.8vw,14px); margin:0 0 6px}
    .row{
      display:grid; grid-template-columns:minmax(120px, 220px) 1fr;
      align-items:center; gap:10px; margin:8px 0;
    }
    input[type="date"]{
      padding:14px 16px; border:1px solid var(--divider); border-radius:14px;
      font-weight:600; width:100%; font-size:clamp(15px,2.8vw,16px);
    }

    /* กริดปุ่ม: เดสก์ท็อปหลายคอลัมน์ / iPad 2 / มือถือ 1 */
    .grid{
      display:grid; gap:12px;
      grid-template-columns:repeat(3, minmax(180px,1fr));
    }
    @container (max-width: 900px){} /* เผื่ออนาคต */

    /* ปุ่มใหญ่ แตะง่าย */
    .btn{
      display:flex; align-items:center; justify-content:center;
      padding:clamp(16px,4.2vw,22px);
      min-height:clamp(64px,14vw,96px);
      border:1px solid var(--divider); border-radius:16px;
      background:#fff; font-weight:800; text-align:center;
      font-size:clamp(16px,3.6vw,18px);
      cursor:pointer; user-select:none;
      transition:transform .05s, box-shadow .15s, background .15s;
      touch-action:manipulation; -webkit-tap-highlight-color:transparent;
    }
    .btn:hover{background:#faf9fd; box-shadow:0 6px 18px rgba(30,16,60,.08)}
    .btn:active{transform:translateY(1px)}

    .toolbar{display:flex; gap:10px; align-items:center; margin-bottom:8px}
    .hidden{display:none}

    /* Overlay เสร็จสิ้น—เต็มหน้าจอ, รองรับมือถือ */
    .overlay{
      position:fixed; inset:0; display:none;
      align-items:center; justify-content:center;
      background:rgba(18,16,26,.5); z-index:50; padding:24px;
    }
    .overlay.show{display:flex}
    .card-ok{
      background:#e6e6e6; border-radius:22px;
      padding:24px clamp(22px,6vw,44px);
      box-shadow:0 20px 60px rgba(0,0,0,.18);
      text-align:center; min-width:min(360px, 92vw);
    }
    .ok-title{font-size:clamp(26px,7vw,38px); line-height:1.2; margin:0 0 12px}
    .ok-icon{font-size:clamp(40px,10vw,60px)}

    /* เบรกพอยต์สำหรับแท็บเล็ต/มือถือ */
    @media (max-width: 980px){
      .grid{grid-template-columns:repeat(2, minmax(160px,1fr))}
    }
    @media (max-width: 640px){
      :root{ --hbar:60px }
      header img{height:40px}
      .row{grid-template-columns:1fr}
      .grid{grid-template-columns:1fr; gap:10px}
    }

    /* โหมดมืด */
    @media (prefers-color-scheme:dark){
      :root{ --bg:#1f1a29; --card:#221d2e; --text:#f6f1ff; --muted:#c7bfd3; --divider:#3a304b }
      .btn{background:#281f3a}
      .btn:hover{background:#2d2442}
      .card-ok{background:#d9d9d9}
    }

    /* ลด motion สำหรับผู้ใช้ที่ตั้งค่าไว้ */
    @media (prefers-reduced-motion:reduce){
      .btn, .overlay{transition:none}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <header aria-label="University Bar">
      <img src="/img/logo-dsa.png" alt="DSA">
    </header>

    <main>
      <section class="card">
        <p class="hint">เลือกประเภทสนาม หากเป็น “สนามกลางแจ้ง” จะให้เลือกชนิดสนามย่อย แล้วระบบจะบันทึกแบบเงียบ</p>
        <div class="row">
          <label for="session">วันที่ (session)</label>
          <input id="session" type="date" inputmode="numeric" autocomplete="off" />
        </div>
      </section>

      <!-- ชั้นที่ 1 -->
      <section class="card" id="panel-top">
        <h3 style="margin:0 0 10px">เลือกประเภทสนาม</h3>
        <div class="grid" id="grid-top"></div>
      </section>

      <!-- ชั้นที่ 2: เฉพาะ “สนามกลางแจ้ง” -->
      <section class="card hidden" id="panel-outdoor">
        <div class="toolbar">
          <button class="btn" id="btnBack" type="button" aria-label="กลับ">&larr; กลับ</button>
          <span class="hint">เลือกชนิดสนามกลางแจ้ง</span>
        </div>
        <div class="grid" id="grid-outdoor"></div>
      </section>
    </main>
  </div>

  <!-- Overlay success -->
  <div id="overlay" class="overlay" aria-live="polite">
    <div class="card-ok">
      <p class="ok-title">check in<br>เสร็จสิ้น</p>
      <div class="ok-icon">✔️</div>
    </div>
  </div>

  <script>
    const id = s => document.getElementById(s);

    const TOP = [
      { k:'outdoor',  name:'สนามกลางแจ้ง', isOutdoor:true },
      { k:'badminton', name:'สนามแบดมินตัน' },
      { k:'track',     name:'สนามลู่-ลาน' },
      { k:'pool',      name:'สระว่ายน้ำ' },
    ];
    const OUTDOOR_SUBS = [
      { k:'tennis',        name:'เทนนิส' },
      { k:'basketball',    name:'บาสเก็ตบอล' },
      { k:'futsal',        name:'ฟุตซอล' },
      { k:'volleyball',    name:'วอลเลย์บอล' },
      { k:'sepak_takraw',  name:'เซปักตะกร้อ' },
    ];

    (function init(){
      const u = new URL(location.href);
      id('session').value = u.searchParams.get('session') || new Date().toISOString().slice(0,10);

      const gridTop = id('grid-top');
      TOP.forEach(f => {
        const b = document.createElement('button');
        b.className = 'btn';
        b.textContent = f.name;
        b.type = 'button';
        b.onclick = () => onTopClick(f);
        gridTop.appendChild(b);
      });

      const gridOutdoor = id('grid-outdoor');
      OUTDOOR_SUBS.forEach(s => {
        const b = document.createElement('button');
        b.className = 'btn';
        b.textContent = s.name;
        b.type = 'button';
        b.onclick = () => checkin(s.k);
        gridOutdoor.appendChild(b);
      });

      id('btnBack').onclick = () => {
        id('panel-outdoor').classList.add('hidden');
        id('panel-top').classList.remove('hidden');
      };
    })();

    function onTopClick(f){
      if (f.isOutdoor){
        id('panel-top').classList.add('hidden');
        id('panel-outdoor').classList.remove('hidden');
      }else{
        checkin(f.k);
      }
    }

    // ไม่ส่ง role จาก client; ฝั่ง PHP อ่าน role จาก session (require_person.php)
    function checkin(facility){
      const session = id('session').value;
      const url = new URL('/checkin', location.origin);   // หรือ '/checkin/pixel.php'
      url.searchParams.set('facility', facility);
      url.searchParams.set('session', session);
      url.searchParams.set('format', 'pixel');

      const img = new Image(1,1);
      img.onload = showDone; img.onerror = showDone;
      img.src = url.toString();
    }

    function showDone(){
      id('overlay').classList.add('show');
      setTimeout(() => { window.location.href = '/auth/logout.php'; }, 1200);
    }
  </script>
</body>
</html>