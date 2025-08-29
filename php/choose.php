<?php
require __DIR__.'/auth.php';
ensureAuth();
?>
<!doctype html>
<html lang="th">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover" />
  <title>เลือกสนามเพื่อเช็คอิน</title>
  <!-- ใส่ CSS ที่จัดเต็มหน้าจอของคุณตามเวอร์ชันล่าสุด -->
</head>
<body>
  <!-- ... โครงหน้า: ฟอร์มเลือกวัน, เลือกประเภทผู้ใช้, ปุ่มสนามชั้นบน/ย่อย ... -->

  <div id="overlay" class="overlay" aria-live="polite" style="display:none">
    <div class="card-ok">
      <p class="ok-title">เช็คอินสำเร็จ</p>
      <div class="ok-icon">✔️</div>
      <div><button id="btnHome" class="home">🏠 กลับหน้าแรก</button></div>
    </div>
  </div>

  <script>
    const id = k => document.getElementById(k);

    function checkin(facility) {
      const session = id('session').value;
      const role = id('role').value;

      const u = new URL('/checkin.php', location.origin);
      u.searchParams.set('facility', facility);
      u.searchParams.set('session', session);
      u.searchParams.set('role', role);
      u.searchParams.set('format', 'pixel');

      const img = new Image(1,1);
      img.onload  = afterCheckin;
      img.onerror = afterCheckin;
      img.src = u.toString();
    }

    async function afterCheckin() {
      // โชว์การ์ดสำเร็จ
      id('overlay').style.display = 'flex';

      // แจ้งเก็บข้อมูลเรียบร้อย: Report/หน้ารวมจะดึงเองตามช่วงวัน
      // ออกจากระบบอัตโนมัติ (สำคัญ: include credentials)
      try {
        await fetch('/logout.php', { method:'POST', credentials:'include' });
      } catch (e) { /* ignore */ }

      // จะปิดหน้าหรือไม่ขึ้นอยู่กับกรณีใช้งาน:
      // setTimeout(() => window.close(), 1200); // ถ้าเปิดจาก PWA/แท็บแยก
    }

    id('btnHome')?.addEventListener('click', () => location.href = '/');
  </script>
</body>
</html>