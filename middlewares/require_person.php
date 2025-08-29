<?php
require __DIR__ . '/require_login.php';

// ตรวจ role จาก session
if (($_SESSION['role'] ?? '') !== 'person') {
    http_response_code(403);
    echo "หน้านี้สำหรับนิสิต/บุคลากรเท่านั้น";
    exit;
}