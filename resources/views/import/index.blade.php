@extends('layouts.app')
@section('title','استيراد بيانات')
@section('page-title','استيراد بيانات Excel')
@section('content')

<div class="panel" style="max-width:800px;margin-bottom:14px">
  <div class="panel-header"><div class="panel-title">📋 أعمدة Excel المطلوبة</div></div>
  <div class="panel-body" style="display:flex;gap:6px;flex-wrap:wrap">
    @foreach(['AC No.','Broker','Broker Commission per Lot','Marketing','Marketing Commission per Lot','Ext Marketer 1','Ext Commission 1','Ext Marketer 2','Ext Commission 2','Month','Initial Deposit $','Monthly Deposit $','New Or Sub','Type'] as $col)
    <span style="background:var(--inp-bg);border:1px solid var(--brd1);border-radius:5px;padding:2px 8px;font-size:10px;font-family:'JetBrains Mono',monospace;color:var(--m2)">{{ $col }}</span>
    @endforeach
  </div>
</div>

<div class="panel" style="max-width:800px">
  <div class="panel-header"><div class="panel-title">📥 رفع ملف Excel</div></div>
  <div class="panel-body">
    <div id="drop-zone"
         style="border:2px dashed var(--brd2);border-radius:14px;padding:44px 24px;text-align:center;cursor:pointer;transition:all .2s"
         onclick="document.getElementById('file-inp').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--pri)';this.style.background='rgba(46,134,171,.05)'"
         ondragleave="this.style.borderColor='';this.style.background=''"
         ondrop="onDrop(event)">
      <div style="font-size:44px;opacity:.35;margin-bottom:12px">📂</div>
      <div style="font-size:1rem;font-weight:700;margin-bottom:5px">اسحب ملف Excel هنا أو اضغط للاختيار</div>
      <div style="font-size:12px;color:var(--mu);margin-bottom:12px">يدعم: .xlsx / .xls / .csv</div>
      <button class="btn btn-primary" type="button">📂 اختر الملف</button>
    </div>
    <input type="file" id="file-inp" accept=".xlsx,.xls,.csv" style="display:none" onchange="handleFile(this)">

    <!-- Preview & Import -->
    <div id="import-result" style="margin-top:14px;display:none">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title" id="import-filename">—</div>
          <div id="import-info" style="font-size:11px;color:var(--mu)"></div>
        </div>
        <div style="overflow-x:auto;max-height:240px;overflow-y:auto">
          <table class="data-table" id="import-preview" style="min-width:700px">
            <thead id="import-thead"></thead>
            <tbody id="import-tbody"></tbody>
          </table>
        </div>
        <div id="import-progress" style="padding:12px 16px;display:none">
          <div style="background:var(--inp-bg);border-radius:6px;height:8px;overflow:hidden;margin-bottom:6px">
            <div id="import-bar" style="height:100%;background:linear-gradient(90deg,var(--pri3),var(--pri2));border-radius:6px;width:0;transition:width .3s"></div>
          </div>
          <div id="import-pct" style="font-size:11px;color:var(--mu)">جاري المعالجة...</div>
        </div>
        <div style="padding:12px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px;align-items:center">
          <button class="btn btn-primary" onclick="confirmImport()">✅ استيراد البيانات</button>
          <button class="btn btn-ghost" onclick="cancelImport()">إلغاء</button>
          <span id="import-status" style="font-size:11px;color:var(--mu);margin-right:auto"></span>
        </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let importRows = [];

function onDrop(e) {
  e.preventDefault();
  const dz = document.getElementById('drop-zone');
  dz.style.borderColor = ''; dz.style.background = '';
  const f = e.dataTransfer.files[0];
  if (f) readFile(f);
}
function handleFile(inp) { if (inp.files[0]) readFile(inp.files[0]); }

function readFile(file) {
  const reader = new FileReader();
  reader.onload = e => {
    const wb = XLSX.read(e.target.result, { type: 'array' });
    const ws = wb.Sheets[wb.SheetNames[0]];
    const rows = XLSX.utils.sheet_to_json(ws, { header: 1 });
    if (rows.length < 2) { toast('الملف فارغ أو لا يحتوي على بيانات', 'error'); return; }

    const header = rows[0];
    importRows = rows.slice(1).filter(r => r.some(c => c));

    document.getElementById('import-filename').textContent = file.name;
    document.getElementById('import-info').textContent = importRows.length + ' سجل';
    document.getElementById('import-thead').innerHTML =
      '<tr>' + header.map(h => `<th>${h || ''}</th>`).join('') + '</tr>';
    document.getElementById('import-tbody').innerHTML =
      importRows.slice(0, 20).map(r =>
        `<tr>${header.map((_, i) => `<td>${r[i] ?? ''}</td>`).join('')}</tr>`
      ).join('');

    document.getElementById('import-result').style.display = 'block';
    document.getElementById('drop-zone').style.display = 'none';
    toast('تم قراءة ' + importRows.length + ' سجل', 'info');
  };
  reader.readAsArrayBuffer(file);
}

async function confirmImport() {
  if (!importRows.length) { toast('لا توجد بيانات', 'error'); return; }

  document.getElementById('import-progress').style.display = 'block';
  const bar = document.getElementById('import-bar');
  const pct = document.getElementById('import-pct');
  let w = 0;
  const timer = setInterval(() => { w = Math.min(w + 2, 88); bar.style.width = w + '%'; }, 80);

  // Build rows for API
  const rows = importRows.map(r => ({
    ac_no:                  String(r[0] || '').replace('.0', ''),
    broker:                 String(r[1] || ''),
    broker_commission:      parseFloat(r[2]) || 0,
    marketing:              String(r[3] || r[1] || ''),
    marketing_commission:   parseFloat(r[4]) || 0,
    ext_marketer1:          String(r[5] || ''),
    ext_commission1:        parseFloat(r[6]) || 0,
    ext_marketer2:          String(r[7] || ''),
    ext_commission2:        parseFloat(r[8]) || 0,
    month:                  String(r[9] || r[5] || '').trim(),
    initial_deposit:        parseFloat(r[10] || r[6]) || 0,
    monthly_deposit:        parseFloat(r[11] || r[7]) || 0,
    new_or_sub:             String(r[12] || r[8] || ''),
    type:                   String(r[13] || r[9] || 'ECN'),
  })).filter(r => r.ac_no && r.month);

  const result = await api('POST', '/import', {
    rows,
    filename: document.getElementById('import-filename').textContent,
  });

  clearInterval(timer);
  bar.style.width = '100%';
  bar.style.background = result.success ? 'var(--gr)' : 'var(--re)';
  pct.textContent = result.success
    ? `✅ تم الاستيراد: ${result.imported} جديد — تحديث: ${result.updated} — فشل: ${result.failed}`
    : `❌ فشل: ${result.message}`;

  document.getElementById('import-status').textContent =
    `دُفعة: ${result.batch_code}`;

  if (result.success) toast(`تم الاستيراد: ${result.imported} جديد`, 'success');
  else toast(`خطأ: ${result.message}`, 'error');
}

function cancelImport() {
  document.getElementById('import-result').style.display = 'none';
  document.getElementById('drop-zone').style.display = 'block';
  document.getElementById('import-progress').style.display = 'none';
  document.getElementById('import-bar').style.width = '0';
  importRows = [];
  document.getElementById('file-inp').value = '';
}

async function loadBatches() {
  const tbody = document.getElementById('batches-tbody');
  if (!tbody) return;
  const r = await api('GET', '/import/batches');
  if (!r.success) { tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--mu)">لا توجد دُفعات</td></tr>'; return; }
  const batches = r.data || [];
  if (!batches.length) {
    tbody.innerHTML = '<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--mu)">لم يتم استيراد أي بيانات بعد</td></tr>';
    return;
  }
  tbody.innerHTML = batches.map(b => `
    <tr>
      <td><span style="font-family:monospace;color:var(--pri2)">${b.batch_code}</span></td>
      <td>${b.file_name || '—'}</td>
      <td><span style="color:var(--gr)">${b.imported_count || 0}</span> / <span style="color:var(--or)">${b.updated_count || 0}</span> / <span style="color:var(--re)">${b.failed_count || 0}</span></td>
      <td>${b.imported_by?.name || '—'}</td>
      <td style="color:var(--mu);font-size:11px">${b.created_at ? b.created_at.substring(0,10) : '—'}</td>
    </tr>`).join('');
}

document.addEventListener('DOMContentLoaded', loadBatches);
</script>
@endpush
