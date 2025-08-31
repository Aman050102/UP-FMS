<?php
// เปิดใช้จริงค่อยเอาคอมเมนต์ออก
// require __DIR__ . '/../../middlewares/require_staff.php';
session_start();

// ดึงชื่อจาก session (ปรับ key ให้ตรง SSO ของคุณ)
$displayName = $_SESSION['user']['name'] 
  ?? $_SESSION['user']['display_name'] 
  ?? $_SESSION['user']['username'] 
  ?? 'เจ้าหน้าที่';
?>

<!doctype html>
<html lang="th">
<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>UP-FMS · เมนูเจ้าหน้าที่</title>
<link rel="icon" href="/img/Logo_of_University_of_Phayao.svg.png" type="image/png">
<meta name="color-scheme" content="light dark">

<link rel="stylesheet" href="/assets/css/staff_console.css">


</head>
<body>
  <header class="topbar">
    <div class="brand">
      <img src="/img/logoDSASMART.png" alt="DSA" class="brand-logo">
    </div>

    <!-- ขวาบน: ชื่อผู้ใช้ + ออกจากระบบ -->
    <div class="righttools">
      <span class="user-btn" aria-label="ผู้ใช้ปัจจุบัน">
        <!-- ไอคอนผู้ใช้เล็ก ๆ -->
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M20 21a8 8 0 0 0-16 0"/><circle cx="12" cy="7" r="4"/>
        </svg>
        <?php echo htmlspecialchars($displayName, ENT_QUOTES, 'UTF-8'); ?>
      </span>
      <a class="logout" href="/auth/logout.php" title="ออกจากระบบ">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
             stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <path d="M16 17l5-5-5-5"/><path d="M21 12H9"/>
        </svg>
        ออกจากระบบ
      </a>
    </div>
  </header>

  <main>
    <div class="section-title">เมนูหลักสำหรับเจ้าหน้าที่</div>

    <section class="grid" aria-label="เมนูด่วน">
      <a class="tile" href="/staff/generator_checkin.php">
        <div class="tile-inner">
          <svg viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm2 2v4h4V5H5zm6 0h2v2h-2V5zm4 0h6v6h-6V5zm2 2v2h2V7h-2zm-8 6h2v2H9v-2zm-6 0h4v4H3v-4zm2 2v0m8 0h2v2h-2v-2zm4 0h2v6h-6v-2h4v-4zm-12 4h2v2H5v-2z" fill="currentColor"/></svg>
          <b>สร้างคิวอาร์โค้ด<br>Check in</b>
          <small>สแกนเข้าเลือกสนาม</small>
        </div>
      </a>

      <a class="tile" href="/staff/generator_checkout_pool.php">
        <div class="tile-inner">
          <svg viewBox="0 0 24 24"><path d="M3 3h8v8H3V3zm10 0h8v8h-8V3zM3 13h8v8H3v-8zm12 0h6v2h-6v6h-2v-8h2z" fill="currentColor"/></svg>
          <b>สร้างคิวอาร์โค้ด<br>Check out </b>
          <small>เฉพาะผู้ใช้สระ</small>
        </div>
      </a>

      <a class="tile" href="/staff/equipment.php">
        <div class="tile-inner">
          <svg viewBox="0 0 24 24"><path d="M4 7h12l4 5-4 5H4l4-5-4-5z" fill="currentColor"/></svg>
          <b>ยืม-คืน อุปกรณ์กีฬา</b>
          <small>จัดการรายการ/สต็อก</small>
        </div>
      </a>

      <a class="tile" href="/staff/badminton_booking.php">
        <div class="tile-inner">
          <svg viewBox="0 0 24 24"><path d="M4 6h16M4 12h16M4 18h16M8 6v12M16 6v12" stroke="currentColor" stroke-width="2" fill="none"/></svg>
          <b>จองสนามแบดมินตัน</b>
          <small>บริหารคอร์ท</small>
        </div>
      </a>

      <a class="tile" href="/staff/report.php">
        <div class="tile-inner">
          <svg viewBox="0 0 24 24"><path d="M4 19h16M6 17V7m6 10V5m6 12V9" stroke="currentColor" stroke-width="2" fill="none"/></svg>
          <b>ข้อมูลการเข้าใช้สนาม</b>
          <small>ดู/ค้นหา/ดาวน์โหลด</small>
        </div>
      </a>

      <a class="tile" href="/staff/borrow_stats.php">
        <div class="tile-inner">
          <svg viewBox="0 0 24 24"><path d="M3 12l5 5 13-13" stroke="currentColor" stroke-width="2" fill="none"/></svg>
          <b>ข้อมูลสถิติการยืม-คืน</b>
          <small>สรุปยอด/แนวโน้ม</small>
        </div>
      </a>
    </section>
  </main>
</body>
</html>