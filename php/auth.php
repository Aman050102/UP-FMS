<?php
// auth.php
function ensureAuth() {
  session_start();
  if (!isset($_SESSION['user'])) {
    $_SESSION['return_to'] = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: /sso/login.php');
    exit;
  }
}