<?php
// ต้องล็อกอินก่อนเสมอ (อ่านโปรไฟล์/role จาก session)
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

  <!-- Stylesheet -->
  <link rel="stylesheet" href="/assets/css/choose.css"/>
  
  <!-- JavaScript -->
  <script src="/js/choose.js" defer></script>
</head>

<body>
  <div class="wrap">
    <header aria-label="University Bar">
      <img class="logo" src="/img/logo-dsa.png" alt="DSA">
    </header>

    <main>
      <section class="card">
        <div class="row">
          <label for="session">วันที่ (session)</label>
          <input id="session" type="date" inputmode="numeric" autocomplete="off" />
        </div>
      </section>

      <!-- ชั้นที่ 1 -->
      <section class="card" id="panel-top">
        <h3 class="h3">เลือกประเภทสนาม</h3>
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
</body>
</html>