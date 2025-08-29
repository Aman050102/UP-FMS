<?php
// เส้นทางจริงของไฟล์ใน public
$public = __DIR__ . '/public';
$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// map /checkin -> public/checkin/pixel.php
if ($path === '/checkin') {
  require $public . '/checkin/pixel.php';
  return true;
}

// ถ้าเป็นไฟล์จริงใน public ให้เสิร์ฟตรง
$file = realpath($public . $path);
if ($file && is_file($file)) {
  return false; // ให้ built-in server เสิร์ฟไฟล์นี้
}

// ดีฟอลต์ชี้ไป index.php (หรือ 404 ตามต้องการ)
require $public . '/index.php';