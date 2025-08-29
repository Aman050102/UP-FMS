// --- server.js (ฉบับสะอาด ไม่มี /checkin ซ้ำ) ---
import express from 'express';
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;
const DB_PATH = path.join(__dirname, 'db.json');

// สนามที่อนุญาต (ให้ชื่อ key ตรงกับฝั่งหน้าเว็บ)
const ALLOWED = new Set([
    'badminton_outdoor', 'badminton_dome', 'tennis', 'basketball', 'pool',
    'petanque', 'futsal', 'track', 'volleyball', 'sepak_takraw', 'outdoor', 'badminton'
]);

async function loadDB() { try { return JSON.parse(await fs.readFile(DB_PATH, 'utf8')); } catch { return { entries: [] } } }
async function saveDB(db) { await fs.writeFile(DB_PATH, JSON.stringify(db, null, 2), 'utf8'); }

app.use(express.static(__dirname)); // เสิร์ฟไฟล์หน้าเว็บ

// พิกเซล 1x1 (GIF) สำหรับบันทึกแบบเงียบ
function sendPixel(res) {
    const gif = Buffer.from('R0lGODlhAQABAPAAAP///wAAACwAAAAAAQABAAACAkQBADs=', 'base64');
    res.setHeader('Content-Type', 'image/gif');
    res.setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, proxy-revalidate');
    res.setHeader('Pragma', 'no-cache'); res.setHeader('Expires', '0');
    res.status(200).end(gif);
}

// ✅ Check-in: รองรับ pixel/page/redirect และ role (student|staff)
app.get('/checkin', async (req, res) => {
    const { facility, session, role = 'student', format, redirect } = req.query;
    if (!ALLOWED.has(String(facility || ''))) return res.status(400).send('facility ไม่ถูกต้อง');
    if (!/^\d{4}-\d{2}-\d{2}$/.test(String(session || ''))) return res.status(400).send('session ต้องเป็น YYYY-MM-DD');

    const entry = {
        ts: new Date().toISOString(),
        facility: String(facility),
        session: String(session),
        role: (role === 'staff' ? 'staff' : 'student')
    };

    const db = await loadDB(); db.entries.push(entry); await saveDB(db);

    if (format === 'pixel') return sendPixel(res);
    if (redirect) return res.redirect(302, String(redirect));
    // fallback เป็นหน้า OK แบบง่าย
    res.type('html').send(`
    <!doctype html><html lang="th"><meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>บันทึกสำเร็จ</title>
    <body style="font-family: system-ui, -apple-system, 'Noto Sans Thai', Arial; padding:24px">
      <h2>บันทึกการเข้าใช้สนามสำเร็จ</h2>
      <p>สนาม: <b>${facility}</b></p>
      <p>วันที่: <b>${session}</b></p>
      <p>ประเภทผู้ใช้: <b>${entry.role}</b></p>
      <p>เวลาเข้า: <b>${new Date(entry.ts).toLocaleString('th-TH')}</b></p>
    </body></html>`);
});

// รายการแถว (หน้า Report ใช้)
app.get('/api/checkins', async (req, res) => {
    const { from = '0000-01-01', to = '9999-12-31', facility = '' } = req.query;
    const db = await loadDB();
    const rows = db.entries
        .filter(e => (!facility || e.facility === facility) && e.session >= from && e.session <= to)
        .map(e => ({
            ts: e.ts, session_date: e.session, facility: e.facility,
            student_id: '', name_full: '', faculty: '', email: ''
        }))
        .sort((a, b) => a.session_date.localeCompare(b.session_date) || a.ts.localeCompare(b.ts));
    res.json(rows);
});

// ตารางสรุปแบบ matrix (นิสิต/บุคลากร + รวม)
app.get('/api/daily-matrix', async (req, res) => {
    const { from = '0000-01-01', to = '9999-12-31' } = req.query;
    const FAC_ORDER = ['pool', 'track', 'outdoor', 'badminton'];
    const FAC_LABEL = { pool: 'สระว่ายน้ำ', track: 'สนามลู่-ลาน', outdoor: 'สนามกีฬากลางแจ้ง', badminton: 'สนามแบดมินตัน' };

    const db = await loadDB();
    const rows = db.entries.filter(e => e.session >= from && e.session <= to);

    const byDate = new Map();
    for (const e of rows) {
        const d = e.session;
        if (!byDate.has(d)) byDate.set(d, {});
        const b = byDate.get(d);
        const key = `${e.facility}-${e.role === 'staff' ? 'staff' : 'student'}`;
        b[key] = (b[key] || 0) + 1;
        b.total = (b.total || 0) + 1;
    }
    const out = Array.from(byDate.keys()).sort().map(date => {
        const b = byDate.get(date);
        const row = { date };
        for (const f of FAC_ORDER) {
            row[`${f}_student`] = b?.[`${f}-student`] || 0;
            row[`${f}_staff`] = b?.[`${f}-staff`] || 0;
        }
        row.total = b?.total || 0;
        return row;
    });

    res.json({
        columns: [
            { key: 'date', label: 'วันที่ (session)' },
            ...FAC_ORDER.flatMap(f => [
                { key: `${f}_student`, label: `${FAC_LABEL[f]} – นิสิต` },
                { key: `${f}_staff`, label: `${FAC_LABEL[f]} – บุคลากร` },
            ]),
            { key: 'total', label: 'รวม' }
        ],
        rows: out
    });
});

app.listen(PORT, () => console.log(`Server on http://localhost:${PORT}`));