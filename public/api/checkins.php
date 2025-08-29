<?php
// หน้ารายงานสำหรับเจ้าหน้าที่ → ล็อกอิน + จำกัดสิทธิ์เป็น staff
require __DIR__ . '/../middlewares/require_login.php';
// ถ้าต้องการล็อกเข้มเฉพาะ staff:
if (($_SESSION['role'] ?? '') !== 'staff') {
  http_response_code(403);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(['error' => 'forbidden']);
  exit;
}

// อ่านช่วงวันที่/ตัวกรองจาก query
$from     = $_GET['from']     ?? date('Y-m-d');
$to       = $_GET['to']       ?? date('Y-m-d');
$facility = $_GET['facility'] ?? '';   // ว่าง = ทุกสนาม

// เชื่อม DB (ต้องชี้ไฟล์เดียวกับ pixel.php)
$dbFile = __DIR__ . '/../../data/checkins.sqlite';
$db = new PDO('sqlite:' . $dbFile);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// ดึงข้อมูล “เท่าที่หน้า report ต้องใช้” (ไม่ส่ง PII ออกไป)
$sql = "SELECT ts, session_date, facility
        FROM checkins
        WHERE date(session_date) BETWEEN :from AND :to";
$params = [':from' => $from, ':to' => $to];

if ($facility !== '') {
  $sql .= " AND facility = :facility";
  $params[':facility'] = $facility;
}

$sql .= " ORDER BY session_date ASC, ts ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งออก JSON
header('Content-Type: application/json; charset=utf-8');
echo json_encode($rows, JSON_UNESCAPED_UNICODE);