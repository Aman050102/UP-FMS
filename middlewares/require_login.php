<?php
session_start();

// ถ้ายังไม่ได้ login → redirect ไปหน้า login
if (!isset($_SESSION['user'])) {
    $next = urlencode($_SERVER['REQUEST_URI']);
    header("Location: /auth/auth.php?next={$next}");
    exit;
}