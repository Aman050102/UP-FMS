<?php
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
session_start();
if (!isset($_SESSION['user'])) { http_response_code(401); exit; } // ต้องล็อกอินเสมอ

$allowed = ['outdoor','badminton','pool','track','tennis','basketball','futsal','volleyball','sepak_takraw','petanque','football'];
$facility = $_GET['facility'] ?? '';
$session  = $_GET['session']  ?? '';
$role     = $_GET['role']     ?? 'student';
$format   = $_GET['format']   ?? 'page';

if (!in_array($facility, $allowed, true)) { http_response_code(400); exit('Invalid facility'); }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $session)) { http_response_code(400); exit('Invalid session'); }
$role = ($role === 'staff') ? 'staff' : 'student';

// (ตัวอย่าง) ดึงข้อมูลผู้ใช้จาก session
$user = $_SESSION['user'] ?? [];
$studentId = $user['studentId'] ?? null;
$email     = $user['email']     ?? null;
$name      = $user['name']      ?? null;
$faculty   = $user['faculty']   ?? null;

// บันทึก
$stmt = $pdo->prepare("
  INSERT INTO checkins (ts, session_date, facility, role, student_id, name_full, faculty, email, ip_address, user_agent)
  VALUES (NOW(), :session_date, :facility, :role, :sid, :name, :faculty, :email, :ip, :ua)
");
$stmt->execute([
  ':session_date' => $session,
  ':facility'     => $facility,
  ':role'         => $role,
  ':sid'          => $studentId,
  ':name'         => $name,
  ':faculty'      => $faculty,
  ':email'        => $email,
  ':ip'           => $_SERVER['REMOTE_ADDR'] ?? null,
  ':ua'           => $_SERVER['HTTP_USER_AGENT'] ?? null,
]);

if ($format === 'pixel') {
  header('Content-Type: image/gif');
  header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate');
  echo base64_decode('R0lGODlhAQABAPAAAP///wAAACwAAAAAAQABAAACAkQBADs=');
  exit;
}

echo 'OK';