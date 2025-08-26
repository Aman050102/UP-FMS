<?php
$facility = $_GET['facility'] ?? '';
$session  = $_GET['session'] ?? '';
?>
<!doctype html><meta charset="utf-8">
<style>body{font-family:system-ui;padding:24px} .ok{color:#096c3b;font-weight:800}</style>
<h1 class="ok">เช็คอินสำเร็จ</h1>
<p>ลงทะเบียนเข้าใช้: <b><?=htmlspecialchars($facility)?></b></p>
<p>วันที่: <b><?=htmlspecialchars($session)?></b></p>
<p>สามารถปิดหน้านี้ได้</p>