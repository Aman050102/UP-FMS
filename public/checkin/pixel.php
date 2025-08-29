<?php
// ต้องล็อกอินและเป็นนิสิต/บุคลากรเท่านั้น
require __DIR__ . '/../middlewares/require_person.php';

// อ่านพารามิเตอร์
$facility = $_GET['facility'] ?? '';
$session  = $_GET['session']  ?? date('Y-m-d');

// ตรวจ facility ที่อนุญาต
$allowed = ['outdoor','badminton','track','pool','tennis','basketball','futsal','volleyball','sepak_takraw','petanque','football'];
if (!in_array($facility, $allowed, true)) {
  http_response_code(400);
  exit;
}

// === DB (SQLite ตัวอย่าง) ===
// สร้างไฟล์เก็บข้อมูลไว้ข้างนอก public เพื่อความปลอดภัย
$dbFile = __DIR__ . '/../../data/checkins.sqlite';
@mkdir(dirname($dbFile), 0775, true);

$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// สร้างตารางถ้ายังไม่มี (เก็บเฉพาะที่จำเป็น – ไม่เก็บ PII)
$db->exec("
  CREATE TABLE IF NOT EXISTS checkins (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ts TEXT NOT NULL,              -- ISO datetime
    session_date TEXT NOT NULL,    -- YYYY-MM-DD (ตามที่หน้า choose ส่งมา)
    facility TEXT NOT NULL,        -- ชนิดสนาม
    user_email TEXT,               -- ถ้าต้องใช้ทำ dedup/นับ unique (ไม่ต้องแสดงหน้า report)
    user_name TEXT,                -- (ไม่ต้องส่งออกหน้า report)
    role TEXT                      -- person/staff (ที่นี่จะเป็น person)
  );
");

// เตรียมข้อมูลบันทึก (ถ้าไม่อยากเก็บ PII ให้ใส่เป็น NULL)
$now = (new DateTime('now', new DateTimeZone('Asia/Bangkok')))->format('c');

$user  = $_SESSION['user'] ?? [];
$email = $user['email']    ?? null;
$name  = $user['username'] ?? null;

$stmt = $db->prepare("INSERT INTO checkins(ts, session_date, facility, user_email, user_name, role)
                      VALUES(:ts, :session_date, :facility, :email, :name, :role)");
$stmt->execute([
  ':ts'           => $now,
  ':session_date' => $session,
  ':facility'     => $facility,
  ':email'        => $email,
  ':name'         => $name,
  ':role'         => $_SESSION['role'] ?? 'person',
]);

// ตอบ GIF 1x1 สำหรับ pixel
header('Content-Type: image/gif');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
echo base64_decode('R0lGODlhAQABAPAAAP///wAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==');