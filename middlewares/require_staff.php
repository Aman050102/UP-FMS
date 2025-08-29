<?php
// <รากโปรเจกต์>/middlewares/require_staff.php
session_start();
if (!isset($_SESSION['user'])) {
  $next = urlencode($_SERVER['REQUEST_URI']);
  header("Location: /auth/auth.php?role=staff&next={$next}");
  exit;
}
if (($_SESSION['role'] ?? '') !== 'staff') {
  http_response_code(403);
  echo "❌ หน้านี้สำหรับเจ้าหน้าที่เท่านั้น";
  exit;
}