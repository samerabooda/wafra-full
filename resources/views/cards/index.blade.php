@extends('layouts.app')
@section('title', 'كروت العمولات')
@section('page-title', 'كروت العمولات')

@section('topbar-actions')
<a href="{{ route('cards.create') }}" class="tb-btn primary">➕ كرت جديد</a>
@endsection

@section('content')
<!-- Filters -->
<div class="panel" style="padding:14px 16px;margin-bottom:14px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">الشهر</div>
      <select id="f-month" class="form-control" style="min-width:120px" onchange="loadCards()">
        <option value="">كل الشهور</option>
      </select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">البروكر</div>
      <select id="f-broker" class="form-control" style="min-width:140px" onchange="loadCards()">
        <option value="">كل البروكرات</option>
      </select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">نوع الحساب</div>
      <select id="f-kind" class="form-control" onchange="loadCards()">
        <option value="">الكل</option>
        <option value="new">جديد</option>
        <option value="sub">فرعي</option>
      </select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">الحالة</div>
      <select id="f-status" class="form-control" onchange="loadCards()">
        <option value="">الكل</option>
        <option value="active">عادي</option>
        <option value="modified">معدّل ✏️</option>
        <option value="new_added">مضاف جديد 🆕</option>
      </select>
    </div>
    <div style="flex:1;min-width:200px">
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">بحث</div>
      <input type="text" id="f-search" class="form-control" placeholder="رقم حساب، بروكر، مسوّق..." oninput="debounceLoad()">
    </div>
    <button class="btn btn-ghost btn-sm" onclick="clearFilters()">✕ مسح</button>
    <div style="display:flex;gap:6px">
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportExcel()">📗 Excel</button>
      <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)" onclick="exportPdf()">📄 PDF</button>
    </div>
  </div>
</div>

<!-- Table -->
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">🗂 كروت العمولات <span id="cards-count" style="font-size:11px;color:var(--mu);font-weight:400;margin-right:6px"></span></div>
    <div style="font-size:11px;color:var(--mu)" id="cards-summary"></div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>رقم الحساب</th>
          <th>الشهر</th>
          <th>البروكر</th>
          <th>ع. بروكر</th>
          <th>مسوّق داخلي</th>
          <th>ع. مسوّق</th>
          <th>مسوّق خارجي 1</th>
          <th>ع. خارجي 1</th>
          <th>مسوّق خارجي 2</th>
          <th>ع. خارجي 2</th>
          <th>إيداع أولي</th>
          <th>إيداع شهري</th>
          <th>النوع</th>
          <th>الحالة</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody id="cards-tbody">
        <tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)">
          <div style="font-size:32px;opacity:.3;margin-bottom:8px">⏳</div>جاري التحميل...
        </td></tr>
      </tbody>
    </table>
  </div>
  <!-- Pagination -->
  <div style="padding:12px 16px;border-top:1px solid var(--brd1);display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:11px;color:var(--mu)" id="pagination-info"></span>
    <div style="display:flex;gap:6px" id="pagination-btns"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let allCards = [];
let currentPage = 1;

// Load filter options
async function loadFilterOptions() {
  const r = await api('GET', '/cards?per_page=500');
  if (!r.success) return;

  const months  = [...new Set((r.data?.data || []).map(c => c.month))];
  const brokers = [...new Set((r.data?.data || []).map(c => c.broker?.name).filter(Boolean))];

  const mSel = document.getElementById('f-month');
  months.forEach(m => { const o = document.createElement('option'); o.value = o.textContent = m; mSel.appendChild(o); });

  const bSel = document.getElementById('f-broker');
  brokers.forEach(b => { const o = document.createElement('option'); o.value = o.textContent = b; bSel.appendChild(o); });
}

async function loadCards() {
  const params = new URLSearchParams();
  const month  = document.getElementById('f-month').value;
  const broker = document.getElementById('f-broker').value;
  const kind   = document.getElementById('f-kind').value;
  const status = document.getElementById('f-status').value;
  const search = document.getElementById('f-search').value;

  if (month)  params.set('month', month);
  if (kind)   params.set('kind', kind);
  if (status) params.set('status', status);
  if (search) params.set('search', search);
  params.set('per_page', 100);

  document.getElementById('cards-tbody').innerHTML = `
    <tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)">
      <div style="font-size:28px;opacity:.3;margin-bottom:8px">⏳</div>جاري التحميل...</td></tr>`;

  const r = await api('GET', '/cards?' + params);
  if (!r.success) { toast('خطأ في تحميل البيانات', 'error'); return; }

  allCards = r.data?.data || [];
  document.getElementById('cards-count').textContent = allCards.length + ' سجل';

  const totalDep = allCards.reduce((s,c) => s + parseFloat(c.initial_deposit || 0), 0);
  const totalMon = allCards.reduce((s,c) => s + parseFloat(c.monthly_deposit || 0), 0);
  document.getElementById('cards-summary').textContent =
    `إيداع أولي: ${fmtK(totalDep)} | شهري: ${fmtK(totalMon)}`;

  renderTable(allCards);
}

function renderTable(cards) {
  if (!cards.length) {
    document.getElementById('cards-tbody').innerHTML =
      '<tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:36px;opacity:.2;margin-bottom:8px">📭</div>لا توجد نتائج</td></tr>';
    return;
  }

  const statusBadge = s => {
    const map = {
      modified:  '<span class="badge badge-orange">✏️ معدّل</span>',
      new_added: '<span class="badge badge-green">🆕 جديد</span>',
      active:    '<span class="badge badge-blue">✅ عادي</span>',
      inactive:  '<span class="badge badge-gray">غير نشط</span>',
    };
    return map[s] || s;
  };

  document.getElementById('cards-tbody').innerHTML = cards.map(c => `
    <tr class="${c.status === 'modified' ? 'row-modified' : ''}">
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="color:var(--mu)">${c.month}</td>
      <td style="font-weight:600;color:var(--pri2)">${c.broker?.name || '—'}</td>
      <td class="mono c-blue">$${c.broker_commission}/lot</td>
      <td style="color:var(--m2)">${c.marketer?.name && c.marketer.name !== c.broker?.name ? c.marketer.name : '—'}</td>
      <td class="mono" style="color:var(--gr)">$${c.marketer_commission || 0}/lot</td>
      <td style="color:var(--pu)">${c.ext_marketer1?.name || '—'}</td>
      <td class="mono" style="color:var(--pu)">$${c.ext_commission1 || 0}/lot</td>
      <td style="color:var(--pu)">${c.ext_marketer2?.name || '—'}</td>
      <td class="mono" style="color:var(--pu)">$${c.ext_commission2 || 0}/lot</td>
      <td class="mono c-blue">${fmt(c.initial_deposit)}</td>
      <td class="mono c-green">${fmt(c.monthly_deposit)}</td>
      <td><span class="badge ${c.account_kind === 'new' ? 'badge-green' : 'badge-blue'}">${c.account_kind === 'new' ? 'NEW' : 'SUB'}</span></td>
      <td>${statusBadge(c.status)}</td>
      <td>
        <a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm">✏️</a>
        ${c.status === 'modified' ? `<button class="btn btn-sm" style="background:rgba(245,166,35,.1);color:var(--or);border:1px solid rgba(245,166,35,.3)" onclick="viewHistory(${c.id})">📋</button>` : ''}
      </td>
    </tr>`).join('');
}

function clearFilters() {
  ['f-month','f-broker','f-kind','f-status'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('f-search').value = '';
  loadCards();
}

let debounceTimer;
function debounceLoad() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadCards, 400);
}

async function viewHistory(id) {
  const r = await api('GET', `/cards/${id}`);
  if (!r.success) return;
  const mods = r.data?.modifications || [];
  toast(`${mods.length} تعديل على هذا الحساب`, 'info');
}

// Export functions
function exportExcel() {
  if (!allCards.length) { toast('لا توجد بيانات', 'error'); return; }
  const headers = ['رقم الحساب','الشهر','البروكر','ع.بروكر','مسوّق داخلي','ع.داخلي','مسوّق خارجي1','ع.خارجي1','مسوّق خارجي2','ع.خارجي2','إيداع أولي','إيداع شهري','النوع','الحالة'];
  const rows = [headers, ...allCards.map(c => [
    c.account_number, c.month, c.broker?.name || '',
    '$'+c.broker_commission+'/lot',
    c.marketer?.name && c.marketer.name !== c.broker?.name ? c.marketer.name : '',
    '$'+c.marketer_commission+'/lot',
    c.ext_marketer1?.name || '', '$'+c.ext_commission1+'/lot',
    c.ext_marketer2?.name || '', '$'+c.ext_commission2+'/lot',
    c.initial_deposit, c.monthly_deposit,
    c.account_kind, c.status,
  ])];
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(rows);
  XLSX.utils.book_append_sheet(wb, ws, 'كروت العمولات');
  XLSX.writeFile(wb, 'WafraGulf_Cards_' + new Date().toISOString().slice(0,10) + '.xlsx');
  toast('تم تحميل Excel ✅', 'success');
}

function exportPdf() {
  if (!allCards.length) { toast('لا توجد بيانات', 'error'); return; }
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation:'landscape', unit:'mm', format:'a3' });
  doc.setFontSize(13); doc.setTextColor(46,134,171);
  doc.text('وفرة الخليجية — كروت العمولات', 210, 14, {align:'center'});
  doc.autoTable({
    startY: 22,
    head: [['AC No.','Month','Broker','B.Comm','Int.Mkt','Int.C','Ext1','E1C','Ext2','E2C','Initial','Monthly','Kind','Status']],
    body: allCards.map(c => [
      c.account_number, c.month, c.broker?.name||'',
      '$'+c.broker_commission, c.marketer?.name||'—', '$'+c.marketer_commission,
      c.ext_marketer1?.name||'—', '$'+c.ext_commission1,
      c.ext_marketer2?.name||'—', '$'+c.ext_commission2,
      fmt(c.initial_deposit), fmt(c.monthly_deposit),
      c.account_kind, c.status,
    ]),
    styles: { fontSize:7, cellPadding:2 },
    headStyles: { fillColor:[46,134,171], textColor:[255,255,255] },
    didParseCell: d => { if (d.row.raw?.[13]==='modified') Object.values(d.row.cells).forEach(cell => { cell.styles.fillColor=[255,248,220]; }); }
  });
  doc.save('WafraGulf_Cards_' + new Date().toISOString().slice(0,10) + '.pdf');
  toast('تم تحميل PDF ✅', 'success');
}

loadFilterOptions();
loadCards();
</script>
@endpush
