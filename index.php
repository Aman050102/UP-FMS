<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Favicon -->
  <link rel="icon" href="../../img/Logo_of_University_of_Phayao.svg.png" type="image/png">

  <title>กองพัฒนาคุณภาพนิสิตและนิสิตพิการ – เข้าสู่ระบบ</title>
  <meta name="color-scheme" content="light dark">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/assets/css/login.css">

  <!-- JavaScript -->
  <script src="js/login.js" defer></script>
</head>
<body>
  <!-- แถบบนพร้อมโลโก้ย่อ -->
  <header class="topbar" aria-label="University Bar">
    <div class="brand-small" aria-label="DQSD logo small">
      <span>
        <img src="/img/logo-dsa.png" alt="DQSD" height="50">
      </span>
    </div>
  </header>

  <!-- เนื้อหาหลัก -->
  <main class="wrap">
    <section class="card" role="region" aria-labelledby="login-title">
      <h1 id="login-title" class="visually-hidden">เข้าสู่ระบบ DQSD</h1>

      <!-- ปุ่มสลับบทบาทผู้ใช้ (แท็บ) -->
      <div class="segmented"
           role="tablist"
           aria-label="ประเภทผู้ใช้">
        <button id="tab-staff"
                role="tab"
                aria-controls="panel-staff"
                aria-selected="true"
                aria-pressed="true"
                tabindex="0">
          สำหรับเจ้าหน้าที่
        </button>
        <button id="tab-person"
                role="tab"
                aria-controls="panel-person"
                aria-selected="false"
                aria-pressed="false"
                tabindex="-1">
          สำหรับบุคลากร/นิสิต
        </button>
      </div>

      <!-- โลโก้/ชื่อหน่วยงาน -->
      <div class="logo-block" aria-live="polite">
        <img src="../../img/dsa.png" alt="ตรากองพัฒนาคุณภาพนิสิตและนิสิตพิการ" height="250">
      </div>

      <div class="divider" role="separator" aria-hidden="true"></div>

      <!-- แผงเนื้อหาของแต่ละแท็บ -->
      <div id="panel-staff"
           class="panel"
           data-active="true"
           role="tabpanel"
           aria-labelledby="tab-staff">
        <div class="cta">
          <button type="button" id="login-staff">เข้าสู่ระบบด้วย UP ACCOUNT</button>
        </div>
      </div>

      <div id="panel-person"
           class="panel"
           data-active="false"
           role="tabpanel"
           aria-labelledby="tab-person"
           hidden>
        <div class="cta">
          <button type="button" id="login-person">เข้าสู่ระบบด้วย UP ACCOUNT</button>
        </div>
      </div>

      <!-- ลืมรหัสผ่าน -->
      <div class="actions">
        <a class="forgot" href="#" id="forgot-link" aria-label="ลืมรหัสผ่าน (เปิดหน้าช่วยเหลือ)">
          <span class="key" aria-hidden="true"></span>
          <span>ลืมรหัสผ่าน</span>
        </a>
      </div>
    </section>
  </main>
</body>
</html>