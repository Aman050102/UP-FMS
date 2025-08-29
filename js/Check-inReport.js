// ===== Helper =====
const $ = (s, p = document) => p.querySelector(s);
const $$ = (s, p = document) => Array.from(p.querySelectorAll(s));
function ymd(d) { return new Date(d).toISOString().slice(0, 10); }
function fmtTime(iso) { const d = new Date(iso); return d.toLocaleString([], { hour: '2-digit', minute: '2-digit' }); }

const FAC = {
    badminton_outdoor: 'แบดมินตันกลางแจ้ง',
    badminton_dome: 'แบดมินตันในโดม',
    tennis: 'เทนนิส',
    basketball: 'บาสเก็ตบอล',
    pool: 'สระว่ายน้ำ',
    petanque: 'เปตอง',
    futsal: 'ฟุตซอล',
    track: 'ลู่-ลาน',
    volleyball: 'วอลเลย์บอล',
    sepak_takraw: 'เซปักตะกร้อ'
};

let allRows = []; let filtered = []; let facilityFilter = 'all';

// รับช่วงวันที่จาก ?session=
const url = new URL(location.href);
const sess = url.searchParams.get('session');
const $from = $('#from'), $to = $('#to');
if (sess) { $from.value = sess; $to.value = sess; }
else { const today = ymd(new Date()); $from.value = today; $to.value = today; }

async function fetchCheckins(params) {
    const qs = new URLSearchParams(params).toString();
    const res = await fetch('/api/checkins?' + qs);
    return res.ok ? res.json() : [];
}

async function load() {
    const from = $from.value || ymd(new Date());
    const to = $to.value || ymd(new Date());
    const facility = (facilityFilter === 'all') ? '' : facilityFilter;
    allRows = await fetchCheckins({ from, to, facility });
    applyFilters();
}

function applyFilters() {
    const q = ($('#q')?.value || '').trim().toLowerCase();
    filtered = allRows.filter(r => {
        const okFac = facilityFilter === 'all' || r.facility === facilityFilter;
        const bag = (FAC[r.facility] || r.facility).toLowerCase();
        return okFac && (!q || bag.includes(q));
    });
    render();
}

function render() {
    // ตัวอย่าง: ถ้าตารางหน้าจอเดิมเป็นรายการทีละแถว
    const tb = $('#table tbody'); if (!tb) return;
    tb.innerHTML = '';
    filtered.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML =
            `<td>${fmtTime(r.ts)}</td>` +
            `<td>${r.session_date}</td>` +
            `<td>${FAC[r.facility] || r.facility}</td>` +
            `<td></td><td></td><td></td><td></td>`; // ไม่เก็บ PII แล้ว
        tb.appendChild(tr);
    });

    // นับรวม
    $('#st-total') && ($('#st-total').textContent = String(filtered.length));
}

// ===== Export: สรุปเป็น "วันที่ / ชื่อสนาม / จำนวนคนเข้าใช้" =====
function rowsForExport() {
    const map = new Map(); // key = date|facility
    filtered.forEach(r => {
        const key = `${r.session_date}|${r.facility}`;
        map.set(key, (map.get(key) || 0) + 1);
    });
    const rows = [];
    for (const [k, count] of map.entries()) {
        const [date, fac] = k.split('|');
        rows.push({ 'วันที่ (session)': date, 'ชื่อสนาม': (FAC[fac] || fac), 'จำนวนคนเข้าใช้': count });
    }
    rows.sort((a, b) => a['วันที่ (session)'].localeCompare(b['วันที่ (session)'])
        || a['ชื่อสนาม'].localeCompare(b['ชื่อสนาม']));
    return rows;
}

// ปุ่ม export (ต้องมีสคริปต์ XLSX/jsPDF/docx รวมในหน้า)
$('#btnExcel')?.addEventListener('click', () => {
    const ws = XLSX.utils.json_to_sheet(rowsForExport());
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Counts');
    XLSX.writeFile(wb, `checkins_${$from.value || ''}_${$to.value || ''}.xlsx`);
});

$('#btnPDF')?.addEventListener('click', () => {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: 'p', unit: 'pt', format: 'a4' });
    const data = rowsForExport();
    doc.setFont('Helvetica', ''); doc.setFontSize(12);
    doc.text(`รายงานผู้เข้าใช้สนามกีฬา (ช่วง ${$from.value || ''} - ${$to.value || ''})`, 40, 40);
    const headers = [['วันที่ (session)', 'ชื่อสนาม', 'จำนวนคนเข้าใช้']];
    const body = data.map(o => [o['วันที่ (session)'], o['ชื่อสนาม'], o['จำนวนคนเข้าใช้']]);
    doc.autoTable({ head: headers, body, startY: 60, styles: { fontSize: 10 } });
    doc.save(`checkins_${$from.value || ''}_${$to.value || ''}.pdf`);
});

$('#btnDoc')?.addEventListener('click', async () => {
    const { Document, Packer, Paragraph, Table, TableRow, TableCell, WidthType, HeadingLevel, AlignmentType } = docx;
    const rows = rowsForExport();
    const headerCells = ['วันที่ (session)', 'ชื่อสนาม', 'จำนวนคนเข้าใช้']
        .map(t => new TableCell({ children: [new Paragraph({ text: t, bold: true })] }));
    const tableRows = [new TableRow({ children: headerCells })];
    rows.forEach(r => {
        tableRows.push(new TableRow({
            children: [r['วันที่ (session)'], r['ชื่อสนาม'], r['จำนวนคนเข้าใช้']]
                .map(v => new TableCell({ children: [new Paragraph(String(v))] }))
        }));
    });
    const table = new Table({ width: { size: 100, type: WidthType.PERCENT }, rows: tableRows });
    const doc = new Document({
        sections: [{
            children: [
                new Paragraph({ text: 'รายงานผู้เข้าใช้สนามกีฬา', heading: HeadingLevel.HEADING_1, alignment: AlignmentType.CENTER }),
                new Paragraph(`ช่วง ${$from.value || ''} - ${$to.value || ''}`),
                table
            ]
        }]
    });
    const blob = await Packer.toBlob(doc);
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob);
    a.download = `checkins_${$from.value || ''}_${$to.value || ''}.docx`; a.click();
});

$('#btnPrint')?.addEventListener('click', () => window.print());

// Events
$('#q')?.addEventListener('input', applyFilters);
$$('#chips .chip').forEach(ch => ch.addEventListener('click', () => {
    $$('#chips .chip').forEach(x => x.classList.remove('selected'));
    ch.classList.add('selected');
    facilityFilter = ch.dataset.k;
    load();
}));

load();