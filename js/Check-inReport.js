// ========== Helper ==========
const $ = (s, p = document) => p.querySelector(s); const $$ = (s, p = document) => Array.from(p.querySelectorAll(s));
const FAC = { outdoor: 'สนามกลางแจ้ง', badminton: 'แบดมินตัน', pool: 'สระว่ายน้ำ', track: 'ลู่-ลาน' };

function fmtDateTime(iso) { const d = new Date(iso); return d.toLocaleString([], { hour: '2-digit', minute: '2-digit' }); }
function ymd(d) { return new Date(d).toISOString().slice(0, 10); }

// ========== Data source ==========
// เปลี่ยน URL นี้ให้ชี้ไป API จริง: /api/checkins?from=YYYY-MM-DD&to=YYYY-MM-DD&facility=...
async function fetchCheckins(params) {
    const qs = new URLSearchParams(params).toString();
    try {
        const res = await fetch('/api/checkins?' + qs);
        if (res.ok) { return await res.json(); }
    } catch (e) { /* ignore and fallback */ }
    // fallback demo: ดึงจาก localStorage (ข้อมูลที่หน้าแสกนบันทึกไว้)
    const list = [];
    for (let i = 0; i < 7; i++) {
        const d = new Date(); d.setDate(d.getDate() - i);
        const key = `facility-checkins:${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
        try { list.push(...JSON.parse(localStorage.getItem(key) || '[]')); } catch { }
    }
    // map เป็นรูปแบบเดียวกับ API จริง
    return list.map(r => ({
        ts: new Date(r.ts).toISOString(),
        session_date: (new Date(r.ts)).toISOString().slice(0, 10),
        facility: r.facility,
        student_id: r.student?.id || '',
        name_full: r.student?.name || '',
        faculty: r.student?.faculty || '',
        email: ''
    }));
}

// ========== State ==========
let allRows = []; let filtered = []; let facilityFilter = 'all';

async function load() {
    const from = $('#from').value || ymd(new Date());
    const to = $('#to').value || ymd(new Date());
    allRows = await fetchCheckins({ from, to, facility: facilityFilter === 'all' ? '' : facilityFilter });
    applyFilters();
}

function applyFilters() {
    const q = $('#q').value.trim().toLowerCase();
    filtered = allRows.filter(r => {
        let okFacility = facilityFilter === 'all' || r.facility === facilityFilter;
        let okQuery = !q || [r.student_id, r.name_full, r.faculty, r.email, FAC[r.facility]].join(' ').toLowerCase().includes(q);
        return okFacility && okQuery;
    });
    render();
}

function render() {
    // stats
    $('#st-total').textContent = filtered.length;
    const agg = { outdoor: 0, badminton: 0, pool: 0, track: 0 };
    filtered.forEach(r => { if (agg[r.facility] != null) agg[r.facility]++; });
    Object.keys(agg).forEach(k => $('#st-' + k) && ($('#st-' + k).textContent = agg[k]));

    // table
    const tb = $('#table tbody'); tb.innerHTML = '';
    filtered.forEach(r => {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td>${fmtDateTime(r.ts)}</td>` +
            `<td>${r.session_date}</td>` +
            `<td>${FAC[r.facility] || r.facility}</td>` +
            `<td>${r.student_id}</td>` +
            `<td>${r.name_full}</td>` +
            `<td>${r.faculty}</td>` +
            `<td>${r.email || ''}</td>`;
        tb.appendChild(tr);
    });
}

// ========== Export ==========
function rowsForExport() {
    return filtered.map(r => ({
        'เวลา': fmtDateTime(r.ts),
        'วันที่ (session)': r.session_date,
        'สนาม': FAC[r.facility] || r.facility,
        'รหัสนิสิต': r.student_id,
        'ชื่อ-สกุล': r.name_full,
        'คณะ': r.faculty,
        'อีเมล': r.email || ''
    }));
}

// Excel (xlsx)
$('#btnExcel').addEventListener('click', () => {
    const ws = XLSX.utils.json_to_sheet(rowsForExport());
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Checkins');
    XLSX.writeFile(wb, `checkins_${$('#from').value || ''}_${$('#to').value || ''}.xlsx`);
});

// PDF (A4 portrait)
$('#btnPDF').addEventListener('click', () => {
    const { jsPDF } = window.jspdf; const doc = new jsPDF({ orientation: 'p', unit: 'pt', format: 'a4' });
    const data = rowsForExport();
    doc.setFont('Helvetica', ''); doc.setFontSize(12);
    doc.text('รายงานผู้เข้าใช้สนามกีฬา (ช่วง ' + ($('#from').value || '') + ' - ' + ($('#to').value || '') + ')', 40, 40);
    const headers = [['เวลา', 'วันที่ (session)', 'สนาม', 'รหัสนิสิต', 'ชื่อ-สกุล', 'คณะ', 'อีเมล']];
    const body = data.map(o => Object.values(o));
    doc.autoTable({ head: headers, body, startY: 60, styles: { fontSize: 9 } });
    doc.save(`checkins_${$('#from').value || ''}_${$('#to').value || ''}.pdf`);
});

// DOCX
$('#btnDoc').addEventListener('click', async () => {
    const { Document, Packer, Paragraph, Table, TableRow, TableCell, WidthType, HeadingLevel, AlignmentType } = docx;
    const rows = rowsForExport();
    const headerCells = ['เวลา', 'วันที่ (session)', 'สนาม', 'รหัสนิสิต', 'ชื่อ-สกุล', 'คณะ', 'อีเมล'].map(t => new TableCell({ children: [new Paragraph({ text: t, bold: true })] }));
    const tableRows = [new TableRow({ children: headerCells })];
    rows.forEach(r => {
        tableRows.push(new TableRow({ children: Object.values(r).map(v => new TableCell({ children: [new Paragraph(String(v || ''))] })) }));
    });
    const table = new Table({ width: { size: 100, type: WidthType.PERCENT }, rows: tableRows });
    const doc = new Document({ sections: [{ properties: {}, children: [new Paragraph({ text: 'รายงานผู้เข้าใช้สนามกีฬา', heading: HeadingLevel.HEADING_1, alignment: AlignmentType.CENTER }), new Paragraph('ช่วง ' + ($('#from').value || '') + ' - ' + ($('#to').value || '')), table] }] });
    const blob = await Packer.toBlob(doc);
    const a = document.createElement('a'); a.href = URL.createObjectURL(blob); a.download = `checkins_${$('#from').value || ''}_${$('#to').value || ''}.docx`; a.click();
});

// Print
$('#btnPrint').addEventListener('click', () => window.print());

// ========== Events ==========
$('#from').value = ymd(new Date()); $('#to').value = ymd(new Date());
$('#q').addEventListener('input', applyFilters);
$$('#chips .chip').forEach(ch => ch.addEventListener('click', () => { $$('#chips .chip').forEach(x => x.classList.remove('selected')); ch.classList.add('selected'); facilityFilter = ch.dataset.k; load(); }));
$('#groupBy').addEventListener('change', () => {/* ที่ว่างไว้ หากต้องการสรุปกลุ่ม */ });

load();