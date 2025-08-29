<?php
session_start();
$target = $_SESSION['return_to'] ?? '/checkin.php';
?>
<!doctype html><meta charset="utf-8">
<style>body{font-family:system-ui;padding:24px}</style>
<h2>Mock SSO Login</h2>
<form method="post" action="callback.php">
  <label>อีเมล: <input name="email" value="b6500001@up.ac.th"></label><br><br>
  <label>รหัสนิสิต: <input name="studentId" value="6500001"></label><br><br>
  <label>ชื่อ-สกุล: <input name="name" value="Student One"></label><br><br>
  <label>คณะ: <input name="faculty" value="สาธารณสุขศาสตร์"></label><br><br>
  <button>เข้าสู่ระบบ</button>
</form>