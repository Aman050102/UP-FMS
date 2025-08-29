<?php
// public/staff/report.php
require __DIR__ . '/../middlewares/require_person.php';
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8">
  <title>Report OK</title>
</head>
<body>
  <!-- <p>Report page OK (passed require_staff)</p> -->
</body>
</html>

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover" />

  <!-- Favicon -->
  <link rel="icon" href="/img/Logo_of_University_of_Phayao.svg.png" type="image/png">

  <title>UP-FMS เช็คอิน</title>
  <meta name="color-scheme" content="light dark">

  <style>
    :root { --bg:#f6f5fb; --card:#fff; --text:#1b1a21; --muted:#6b657a; --divider:#e7e3ef; --shadow:0 12px 30px rgba(30,16,60,.12); --hbar:72px; }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{margin:0;font-family:ui-sans-serif,system-ui,-apple-system,"Noto Sans Thai",Roboto,Arial,sans-serif;color:var(--text);background:linear-gradient(180deg,#6f4ab4 0 var(--hbar),var(--bg) var(--hbar) 100%)}
    .wrap{min-height:100svh;padding:env(safe-area-inset-top) env(safe-area-inset-right) env(safe-area-inset-bottom) env(safe-area-inset-left);display:flex;flex-direction:column}
    header{height:var(--hbar);display:flex;align-items:center;padding:0 18px;color:#fff;font-weight:900;background:transparent;flex:0 0 auto}
    main{flex:1 1 auto;display:flex;flex-direction:column;gap:16px;width:100%;max-width:1200px;margin:0 auto;padding:16px clamp(12px,4vw,28px) 28px}
    .card{background:var(--card);border-radius:20px;box-shadow:var(--shadow);padding:clamp(14px,2.5vw,22px);width:100%}
    .hint{color:var(--muted);font-size:clamp(13px,1.9vw,14px)}
    .row{display:grid;grid-template-columns:minmax(120px,220px) 1fr;gap:12px;align-items:center;margin:10px 0}
    input,select{padding:14px 16px;border:1px solid var(--divider);border-radius:14px;font-weight:600;width:100%;font-size:clamp(15px,2.5vw,16px)}
    .grid{display:grid;gap:14px;grid-template-columns:repeat(auto-fill,minmax(min(220px,100%),1fr))}
    .btn{display:flex;align-items:center;justify-content:center;padding:clamp(16px,3.5vw,22px);min-height:clamp(64px,12vw,84px);border:1px solid var(--divider);border-radius:16px;background:#fff;font-weight:800;font-size:clamp(16px,3.2vw,18px);cursor:pointer;user-select:none;transition:transform .05s,box-shadow .15s,background .15s;touch-action:manipulation}
    .btn:hover{background:#faf9fd;box-shadow:0 6px 18px rgba(30,16,60,.08)}
    .btn:active{transform:translateY(1px)}
    .toolbar{display:flex;gap:10px;align-items:center;margin-bottom:10px}
    .hidden{display:none}
    .overlay{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(18,16,26,.5);z-index:50;padding:32px}
    .overlay.show{display:flex}
    .card-ok{background:#e6e6e6;border-radius:28px;padding:32px clamp(28px,6vw,56px);box-shadow:0 20px 60px rgba(0,0,0,.18);text-align:center;min-width:min(420px,90vw)}
    .ok-title{font-size:clamp(28px,6vw,40px);margin:0 0 18px}
    .ok-icon{font-size:clamp(40px,8vw,56px);display:inline-block}
    .home{margin-top:22px;display:inline-flex;align-items:center;gap:8px;border:1px solid #cfcfcf;padding:12px 16px;border-radius:12px;background:#fff;font-weight:800;cursor:pointer;font-size:clamp(15px,2.8vw,16px)}
    @media (max-width:640px){.row{grid-template-columns:1fr}.grid{grid-template-columns:1fr 1fr;gap:12px}}
    @media (prefers-color-scheme:dark){:root{--bg:#1f1a29;--card:#221d2e;--text:#f6f1ff;--muted:#c7bfd3;--divider:#3a304b}.btn{background:#281f3a}.btn:hover{background:#2d2442}.card-ok{background:#d9d9d9}}
  </style>
</head>

<body>
  <div class="wrap">
    <header class="topbar" aria-label="University Bar">
      <div class="brand-small" aria-label="DQSD logo small">
        <span><img src="/img/logo-dsa.png" alt="DQSD" height="50"></span>
      </div>
    </header>

    <main>
      <section class="card">
        <!-- <p class="hint">สแกนคิวอาร์เดียว → เลือกสนาม หากเป็น “สนามกลางแจ้ง” จะให้เลือกชนิดสนามย่อย แล้วระบบจะบันทึกแบบเงียบ</p> -->
        <div class="row">
          <label for="session">วันที่ (session)</label>
          <input id="session" type="date" />
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
          <button class="btn" id="btnBack">&larr; กลับ</button>
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
      <!-- ถ้าต้องการปุ่ม: ยกเลิกคอมเมนต์แล้วค่อยใส่ handler
      <div><button id="btnHome" class="home" aria-label="ออกจากระบบ">🏠 ออกจากระบบ</button></div> -->
    </div>
  </div>

  <script>
    const id = k => document.getElementById(k);

    const TOP = [
      { k: 'outdoor', name: 'สนามกลางแจ้ง', isOutdoor: true },
      { k: 'badminton', name: 'สนามแบดมินตัน' },
      { k: 'track', name: 'สนามลู่-ลาน' },
      { k: 'pool', name: 'สระว่ายน้ำ' },
    ];
    const OUTDOOR_SUBS = [
      { k: 'tennis', name: 'เทนนิส' },
      { k: 'basketball', name: 'บาสเก็ตบอล' },
      { k: 'futsal', name: 'ฟุตซอล' },
      { k: 'volleyball', name: 'วอลเลย์บอล' },
      { k: 'sepak_takraw', name: 'เซปักตะกร้อ' },
    ];

    (function init() {
      const u = new URL(location.href);
      id('session').value = u.searchParams.get('session') || new Date().toISOString().slice(0, 10);

      const gridTop = id('grid-top');
      TOP.forEach(f => {
        const b = document.createElement('button');
        b.className = 'btn'; b.textContent = f.name; b.onclick = () => onTopClick(f);
        gridTop.appendChild(b);
      });

      const gridOutdoor = id('grid-outdoor');
      OUTDOOR_SUBS.forEach(s => {
        const b = document.createElement('button');
        b.className = 'btn'; b.textContent = s.name; b.onclick = () => checkin(s.k);
        gridOutdoor.appendChild(b);
      });

      id('btnBack').onclick = () => {
        id('panel-outdoor').classList.add('hidden');
        id('panel-top').classList.remove('hidden');
      };
      // ถ้ายกเลิกคอมเมนต์ปุ่ม btnHome ค่อยเพิ่ม handler นี้
      // id('btnHome').onclick = () => { location.href = '/auth/logout.php'; };
    })();

    function onTopClick(f) {
      if (f.isOutdoor) {
        id('panel-top').classList.add('hidden');
        id('panel-outdoor').classList.remove('hidden');
      } else {
        checkin(f.k);
      }
    }

    // ไม่ส่ง role จาก client; ฝั่ง PHP อ่าน role จาก session (require_person.php)
    function checkin(facility) {
      const session = id('session').value;
      const url = new URL('/checkin', location.origin); // หรือ '/checkin/pixel.php'
      url.searchParams.set('facility', facility);
      url.searchParams.set('session', session);
      url.searchParams.set('format', 'pixel');

      const img = new Image(1, 1);
      img.onload = showDone;
      img.onerror = showDone;
      img.src = url.toString();
    }

    function showDone() {
      id('overlay').classList.add('show');
      setTimeout(() => {
        window.location.href = '/auth/logout.php';
      }, 1200);
    }
  </script>
</body>
</html>