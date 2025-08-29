<?php
session_start();
$_SESSION = [];
session_destroy();

// กลับหน้าแรกของเว็บ (public root)
header('Location: /index.php');
exit;