<?php
$facility = $_GET['facility'] ?? '';
$session  = $_GET['session'] ?? '';
?>
<!doctype html><meta charset="utf-8">
<style>body{font-family:system-ui;padding:24px} .warn{color:#b42318;font-weight:800}</style>
<h1 class="warn">ลิงก์นี้ถูกใช้ไปแล้ว</h1>
<p>สนาม: <b><?=htmlspecialchars($facility)?></b></p>
<p>วันที่: <b><?=htmlspecialchars($session)?></b></p>
<p>หากมีปัญหา กรุณาติดต่อเจ้าหน้าที่</p>