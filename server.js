// server.js
import express from 'express';
import fs from 'fs/promises';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const app = express();
const PORT = process.env.PORT || 3000;
const DB_PATH = path.join(__dirname, 'db.json');

// รายชื่อสนามที่อนุญาต (ต้องตรงกับ generator)
const ALLOWED = new Set([
    'badminton_outdoor', 'badminton_dome', 'tennis', 'basketball', 'pool', 'petanque', 'futsal', 'track', 'volleyball', 'sepak_takraw'
]);

async function loadDB() {
    try { const raw = await fs.readFile(DB_PATH, 'utf8'); return JSON.parse(raw); }
    catch { return { entries: [] }; }
}
async function saveDB(db) { await fs.writeFile(DB_PATH, JSON.stringify(db, null, 2), 'utf8'); }

// เสิร์ฟไฟล์ static (ใส่ generator.html ในโฟลเดอร์เดียวกันได้)
app.use(express.static(__dirname));

// 1) Check-in: /checkin?facility=...&session=YYYY-MM-DD
app.get('/checkin', async (req, res) => {
    const { facility, session } = req.query;
    if (!ALLOWED.has(String(facility || ''))) return res.status(400).send('facility ไม่ถูกต้อง');
    if (!/^\d{4}-\d{2}-\d{2}$/.test(String(session || ''))) return res.status(400).send('session ต้องเป็น YYYY-MM-DD');

    const now = new Date();
    const entry = { ts: now.toISOString(), facility: String(facility), session: String(session) };

    const db = await loadDB();
    db.entries.push(entry);
    await saveDB(db);

    // หน้าสำเร็จอย่างง่าย
    res.type('html').send(`
    <!doctype html><html lang="th"><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>บันทึกสำเร็จ</title>
    <body style="font-family: system-ui, -apple-system, 'Noto Sans Thai', Arial; padding:24px">
      <h2>บันทึกการเข้าใช้สนามสำเร็จ</h2>
      <p>สนาม: <b>${facility}</b></p>
      <p>วันที่ (session): <b>${session}</b></p>
      <p>เวลาเข้า: <b>${new Date(entry.ts).toLocaleString('th-TH')}</b></p>
      <hr/>
      <p><a href="/">กลับหน้าแรก</a></p>
    </body></html>
  `);
});

// 2) ดึงสถิติรายวัน: /api/daily?facility=all|ชื่อสนาม&from=YYYY-MM-DD&to=YYYY-MM-DD
app.get('/api/daily', async (req, res) => {
    const { facility = 'all', from = '0000-01-01', to = '9999-12-31' } = req.query;
    const db = await loadDB();
    const rows = db.entries.filter(e => (
        (facility === 'all' || e.facility === facility)
        && e.session >= from && e.session <= to
    ));
    // สรุปนับจำนวนต่อวันต่อสนาม
    const map = new Map(); // key: `${e.session}|${e.facility}` -> count
    for (const e of rows) {
        const key = `${e.session}|${e.facility}`;
        map.set(key, (map.get(key) || 0) + 1);
    }
    const out = Array.from(map.entries()).map(([k, count]) => {
        const [date, fac] = k.split('|');
        return { date, facility: fac, count };
    }).sort((a, b) => a.date.localeCompare(b.date) || a.facility.localeCompare(b.facility));
    res.json(out);
});

// 3) ส่งออก CSV รายการเข้า (วัน/เดือน/ปี, เวลาเข้า, สนาม)
app.get('/api/entries.csv', async (req, res) => {
    const { facility = 'all', from = '0000-01-01', to = '9999-12-31' } = req.query;
    const db = await loadDB();
    const rows = db.entries.filter(e => (
        (facility === 'all' || e.facility === facility)
        && e.session >= from && e.session <= to
    ));
    const header = 'date,month,year,time,facility\n';
    const lines = rows.map(e => {
        const d = new Date(e.ts);
        const th = new Intl.DateTimeFormat('th-TH', { year: 'numeric', month: '2-digit', day: '2-digit', hour: '2-digit', minute: '2-digit', second: '2-digit' }).formatToParts(d);
        const obj = Objec