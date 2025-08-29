<?php
$DB_HOST = 'localhost';
$DB_NAME = 'sports';
$DB_USER = 'sports_user';
$DB_PASS = 'your_strong_password';
$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$pdo = new PDO($dsn, $DB_USER, $DB_PASS, [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
]);