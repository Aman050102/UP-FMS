<?php
require __DIR__.'/db.php';
require __DIR__.'/auth.php';
ensureAuth();

$user = $_SESSION['user']; // ['email','studentId','name','faculty']
$facility = $_GET['facility'] ?? '';
$session  = $_GET['session']  ?? '';
$nonce    = $_GET['nonce']    ?? '';

$allowedFacilities = ['outdoor','badminton','pool','track'];

if (!in_array($facility, $allowedFacilities, true)) { http_response_code(400); exit('Invalid facility'); }
if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $session)) { http_response_code(400); exit('Invalid session'); }
if (!preg_match('/^[0-9a-fA-F-]{8,}$/', $nonce))     { http_response_code(400); exit('Invalid nonce'); }

$studentId = $user['studentId'] ?? null;
$email     = $user['email'] ?? null;
$name      = $user['name'] ?? null;
$faculty   = $user['faculty'] ?? null;

if (!$studentId && $email && preg_match('/^(\d{5,9})@/', $email, $m)) { $studentId = $m[1]; }
if (!$studentId) { http_response_code(400); exit('No student ID'); }

try {
  $stmt = $pdo->prepare("
    INSERT INTO checkins
      (ts, session_date, facility, student_id, name_full, faculty, email, ip_address, user_agent, nonce)
    VALUES
      (NOW(), :session_date, :facility, :student_id, :name_full, :faculty, :email, :ip_address, :user_agent, :nonce)
  ");
  $stmt->execute([
    ':session_date' => $session,
    ':facility'     => $facility,
    ':student_id'   => $studentId,
    ':name_full'    => $name,
    ':faculty'      => $faculty,
    ':email'        => $email,
    ':ip_address'   => $_SERVER['REMOTE_ADDR'] ?? null,
    ':user_agent'   => $_SERVER['HTTP_USER_AGENT'] ?? null,
    ':nonce'        => $nonce,
  ]);
} catch (PDOException $e) {
  if ($e->getCode() === '23000') {
    header("Location: /already.php?facility=$facility&session=$session");
    exit;
  }
  http_response_code(500); exit('DB Error');
}

header("Location: /success.php?facility=$facility&session=$session");
exit;