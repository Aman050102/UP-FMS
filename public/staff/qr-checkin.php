<?php
// (เปิดดู error ชั่วคราวระหว่างพัฒนา; ปิดเมื่อขึ้นโปรดักชัน)
ini_set('display_errors', 1);
error_reporting(E_ALL);

// ตั้งค่าโซนเวลาไทย + ค่าวันที่วันนี้
date_default_timezone_set('Asia/Bangkok');
$today = date('Y-m-d');

// เดา base URL จากการรันปัจจุบัน
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'] ?? 'localhost:8080';
$defaultBase = $scheme . '://' . $host;
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=1280, initial-scale=1" />
    <link rel="icon" href="/img/Logo_of_University_of_Phayao.svg.png" type="image/png">
    <title>UP-FMS - QR เช็คอิน</title>
    <meta name="color-scheme" content="light dark">

    <!-- QRCode lib -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js" defer></script>

    <!-- Stylesheets (อ้างจาก web root = public/) -->
    <link rel="stylesheet" href="/assets/css/QRCode.css">
</head>

<body>
    <header class="topbar">
        <div class="brand">
            <img src="/img/logoDSASMART.png" alt="DSA" class="brand-logo">
        </div>
    </header>

    <br>
    <main class="stage">
        <!-- Left: Form -->
        <div class="col">
            <section class="card">
                <h2>สร้างคิวอาร์</h2>

                <div class="form">
                    <label for="base">Base URL</label>
                    <input id="base" type="url" value="<?= htmlspecialchars($defaultBase, ENT_QUOTES, 'UTF-8') ?>"
                        placeholder="https://sports.up.ac.th">

                    <label for="session">วันที่ (session)</label>
                    <input id="session" type="date" value="<?= htmlspecialchars($today, ENT_QUOTES, 'UTF-8') ?>">

                    <label for="size">ขนาด QR</label>
                    <select id="size">
                        <option value="512" selected>512 × 512</option>
                        <option value="384">384 × 384</option>
                        <option value="640">640 × 640</option>
                    </select>
                </div>

                <div class="actions">
                    <button id="btnGen" class="btn primary">สร้าง / อัปเดต</button>
                    <button id="btnDL" class="btn">ดาวน์โหลด PNG</button>
                    <button id="btnCopy" class="btn">คัดลอกลิงก์</button>
                </div>
            </section>
        </div>

        <!-- Right: QR Preview -->
        <div class="col">
            <section class="card qr-pane">
                <h2>พรีวิวคิวอาร์ <span id="status" class="badge">พร้อม</span></h2>
                <div class="qrbox">
                    <div class="qr-inner">
                        <div id="qr"></div>
                    </div>
                </div>
                <div class="url-line">
                    <div id="url" class="url-view"></div>
                    <a id="open" class="btn" href="" target="_blank" rel="noopener">เปิดลิงก์</a>
                </div>
            </section>
        </div>
    </main>

    <script>
        // ===== JS ทำงานร่วมกับ qrcodejs =====
        function buildURL() {
            const base = document.getElementById('base').value.trim().replace(/\/+$/, '');
            const session = document.getElementById('session').value;
            // ตัวอย่างปลายทาง (ปรับตามระบบจริงของคุณได้):
            // /auth/auth.php?role=person&next=/choose.php?session=YYYY-MM-DD
            const next = `/choose.php?session=${encodeURIComponent(session)}`;
            const url = `${base}/auth/auth.php?role=person&next=${encodeURIComponent(next)}`;
            return url;
        }

        let qr;
        function renderQR() {
            const size = parseInt(document.getElementById('size').value, 10) || 512;
            const url = buildURL();

            const box = document.getElementById('qr');
            box.innerHTML = '';
            qr = new QRCode(box, { text: url, width: size, height: size, correctLevel: QRCode.CorrectLevel.M });

            document.getElementById('url').textContent = url;
            const open = document.getElementById('open');
            open.href = url;
        }

        function downloadPNG() {
            const canvas = document.querySelector('#qr canvas');
            if (!canvas) { alert('ยังไม่มี QR — กด "สร้าง / อัปเดต" ก่อนครับ'); return; }
            const a = document.createElement('a');
            a.href = canvas.toDataURL('image/png');
            a.download = 'qr-checkin.png';
            a.click();
        }

        async function copyURL() {
            const url = document.getElementById('url').textContent;
            try { await navigator.clipboard.writeText(url); alert('คัดลอกลิงก์แล้ว'); }
            catch { alert('คัดลอกไม่สำเร็จ'); }
        }

        window.addEventListener('DOMContentLoaded', () => {
            document.getElementById('btnGen').addEventListener('click', renderQR);
            document.getElementById('btnDL').addEventListener('click', downloadPNG);
            document.getElementById('btnCopy').addEventListener('click', copyURL);
            // เรนเดอร์ครั้งแรกด้วยค่าตั้งต้น
            renderQR();
        });
    </script>
</body>

</html>