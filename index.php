<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <!-- Favicon -->
  <link rel="icon" href="../../img/Logo_of_University_of_Phayao.svg.png" type="image/png">

  <title>กองกิจการนิสิต เข้าสู่ระบบ</title>
  <meta name="color-scheme" content="light dark">

  <!-- Stylesheets -->
  <link rel="stylesheet" href="/public/assets/css/login.css">

  <!-- JavaScript -->
  <script src="/public/js/login.js" defer></script>

</head>
<body>
  <!-- แถบบนพร้อมโลโก้ย่อ -->
  <header class="topbar" aria-label="University Bar">
    <div class="brand-small" aria-label="DQSD logo small">
      <span>
        <img src="/public/img/logoDSASMART.png" alt="DSA" height="100">
      </span>
    </div>
  </header>

  <!-- เนื้อหาหลัก -->
  <main class="wrap">
    <section class="card" role="region" aria-labelledby="login-title">

      <!-- ปุ่มสลับบทบาทผู้ใช้ (แท็บ) -->
      <div class="segmented"
           role="tablist"
           aria-label="ประเภทผู้ใช้">
        <button onclick="window.location.href='auth.php?role=staff'">เข้าสู่ระบบสำหรับเจ้าหน้าที่</button>
        <button onclick="window.location.href='auth.php?role=staff'">เข้าสู่ระบบสำหรับเจ้าหน้าที่</button>

      </div>

      <!-- โลโก้/ชื่อหน่วยงาน -->
      <div class="logo-block" aria-live="polite">
        <img src="/public/img/dsa.png" alt="ตรากองพัฒนาคุณภาพนิสิตและนิสิตพิการ" height="250">
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