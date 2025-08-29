<?php
// public/auth/auth.php
session_start();
$config = require __DIR__ . '/../../config/app.php';

// หน้าไหนจะกลับไปหลังล็อกอินสำเร็จ
$next = $_GET['next'] ?? '/index.php';

// ---------- ตัวอย่างจำลอง SSO ----------
// แทนที่ส่วนนี้ด้วยผลลัพธ์จาก SSO จริงของมหาลัย
$sso_profile = [
  'email'       => 'student001@up.ac.th',
  'username'    => 'student001',
  'affiliations'=> ['student'], // ตัวอย่างค่า claim
];
// ----------------------------------------

// 1) สร้าง user object
$user = [
  'email'        => $sso_profile['email'] ?? null,
  'username'     => $sso_profile['username'] ?? null,
  'affiliations' => array_map('strtolower', $sso_profile['affiliations'] ?? []),
];

// 2) กำหนด role
$allowed_staff = $config['allowed_staff'] ?? [];
if (in_array($user['email'], $allowed_staff, true)) {
  $role = 'staff';
} else {
  // ผ่าน SSO แล้ว → เข้าเป็นบุคลากร/นิสิต (person)
  $role = 'person';
}

// 3) เซฟลง session
$_SESSION['user']      = $user;
$_SESSION['role']      = $role;
$_SESSION['logged_at'] = time();

// 4) ปลอดภัย: อนุญาต redirect เฉพาะ path ภายในเว็บ
if (preg_match('#^/[A-Za-z0-9/_\-.?=&]*$#', $next) !== 1) {
  $next = '/index.php';
}

header("Location: {$next}");
exit;