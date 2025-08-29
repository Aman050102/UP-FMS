<?php
session_start();

// รับค่าจากฟอร์ม mock
$email     = trim($_POST['email'] ?? '');
$studentId = trim($_POST['studentId'] ?? '');
$name      = trim($_POST['name'] ?? '');
$faculty   = trim($_POST['faculty'] ?? '');

// ตรวจง่าย ๆ ว่าเป็นเมลโดเมน up.ac.th (เอาไว้พอทดสอบ)
if (!preg_match('/@up\.ac\.th$/i', $email)) {
  http_response_code(400);
  exit('ต้องใช้อีเมลโดเมน @up.ac.th');
}

// เซ็ตโปรไฟล์ผู้ใช้ลงใน session
$_SESSION['user'] = [
  'email'     => $email,
  'studentId' => $studentId,
  'name'      => $name,
  'faculty'   => $faculty,
];

// ดึงหน้าเป้าหมายที่ตั้งไว้ตอนโดนบังคับล็อกอิน
$target = $_SESSION['return_to'] ?? '/';
unset($_SESSION['return_to']);

header("Location: $target");
exit;