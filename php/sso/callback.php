<?php
session_start();
$_SESSION['user'] = [
  'email'     => $_POST['email'] ?? null,
  'studentId' => $_POST['studentId'] ?? null,
  'name'      => $_POST['name'] ?? null,
  'faculty'   => $_POST['faculty'] ?? null,
];
$to = $_SESSION['return_to'] ?? '/';
unset($_SESSION['return_to']);
header("Location: $to");