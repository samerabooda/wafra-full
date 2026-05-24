@extends('layouts.app')
@section('title', 'كروت العمولات')
@section('page-title', 'كروت العمولات')

@section('topbar-actions')
<a href="{{ route('cards.create') }}" class="tb-btn primary">➕ كرت جديد</a>
@endsection

@section('content')
<style>
/* ══════════════════════════════════════════════════════
   CARDS INDEX — COLOR-CODED DESIGN SYSTEM
   ══════════════════════════════════════════════════════ */

/* Status badge chips */
.chip { display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap; }
.chip-new      { background:rgba(34,201,122,.12); color:#22c97a;  border:1px solid rgba(34,201,122,.25); }
.chip-modified { background:rgba(245,166,35,.12);  color:#f5a623;  border:1px solid rgba(245,166,35,.25); }
.chip-active   { background:rgba(46,134,171,.12);  color:#3a9db5;  border:1px solid rgba(46,134,171,.25); }
.chip-inactive { background:rgba(120,120,140,.1);  color:#8899aa;  border:1px solid rgba(120,120,140,.2); }
.chip-cc       { background:linear-gradient(135deg,#7b68ee22,#5f4fcf22); color:#7b68ee; border:1px solid rgba(123,104,238,.3); }
.chip-new-acc  { background:rgba(34,201,122,.1);  color:#22c97a;  border:1px solid rgba(34,201,122,.2); }
.chip-sub-acc  { background:rgba(46,134,171,.1);  color:#2e86ab;  border:1px solid rgba(46,134,171,.2); }

/* Row color-coding */
.data-table tr.row-cc-card td        { background:linear-gradient(90deg,rgba(123,104,238,.06),transparent 70%) !important; }
.data-table tr.row-cc-card td:first-child { border-right:3px solid #7b68ee; }
.data-table tr.row-cc-card:hover td  { background:linear-gradient(90deg,rgba(123,104,238,.12),rgba(123,104,238,.03) 70%) !important; }
.data-table tr.row-modified td       { background:rgba(245,166,35,.05) !important; }
.data-table tr.row-modified td:first-child { border-right:3px solid #f5a623; }
.data-table tr.row-new_added td:first-child { border-right:3px solid #22c97a; }
.data-table tr.row-inactive td       { opacity:.65; }

/* Commission color scale */
.comm-low  { color:#22c97a; } /* ≤2  */
.comm-mid  { color:#f5a623; } /* 2-5 */
.comm-high { color:#e05050; } /* >5  */

/* Summary KPI strip */
.kpi-strip { display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px; }
.kpi-pill  { display:flex;flex-direction:column;align-items:center;background:var(--card-bg);
  border:1px solid var(--card-brd);border-radius:12px;padding:10px 16px;min-width:120px;flex:1; }
.kpi-pill-val { font-size:18px;font-weight:800;font-family:'JetBrains Mono',monospace; }
.kpi-pill-lbl { font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-top:3px; }

/* Filter bar */
.filter-bar { display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;padding:14px 16px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;margin-bottom:14px; }
.filter-group { display:flex;flex-direction:column;gap:4px; }
.filter-label { font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px; }
.filter-bar .form-control { min-width:110px; }

/* Source legend */
.legend { display:flex;gap:12px;flex-wrap:wrap;align-items:center;padding:8px 16px;
  background:var(--bg2);border-radius:10px;margin-bottom:10px;font-size:11px;color:var(--mu); }
.legend-dot { width:10px;height:10px;border-radius:3px;display:inline-block; }
</style>

<!-- KPI Summary Strip -->
<div class="kpi-strip" id="kpi-strip">
  <div class="kpi-pill kpi-blue">
    <span class="kpi-pill-val" id="kpi-total" style="color:var(--pri2)">—</span>
    <span class="kpi-pill-lbl">إجمالي الكروت</span>
  </div>
  <div class="kpi-pill kpi-green">
    <span class="kpi-pill-val" id="kpi-new" style="color:var(--gr)">—</span>
    <span class="kpi-pill-lbl">جديد NEW</span>
  </div>
  <div class="kpi-pill kpi-blue">
    <span class="kpi-pill-val" id="kpi-sub" style="color:var(--pri)">—</span>
    <span class="kpi-pill-lbl">فرعي SUB</span>
  </div>
  <div class="kpi-pill kpi-orange">
    <span class="kpi-pill-val" id="kpi-modified" style="color:var(--or)">—</span>
    <span class="kpi-pill-lbl">معدّل</span>
  </div>
  <div class="kpi-pill kpi-purple">
    <span class="kpi-pill-val" id="kpi-cc" style="color:var(--pu)">—</span>
    <span class="kpi-pill-lbl">من CC</span>
  </div>
  <div class="kpi-pill kpi-teal">
    <span class="kpi-pill-val" id="kpi-dep" style="color:var(--pri2)">—</span>
    <span class="kpi-pill-lbl">إجمالي الإيداع</span>
  </div>
  <div class="kpi-pill kpi-green">
    <span class="kpi-pill-val" id="kpi-avgcomm" style="color:var(--gr)">—</span>
    <span class="kpi-pill-lbl">متوسط العمولة</span>
  </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <div class="filter-group">
    <span class="filter-label">الشهر</span>
    <select id="f-month" class="form-control" onchange="loadCards()">
      <option value="">كل الشهور</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label">البروكر</span>
    <select id="f-broker" class="form-control" style="min-width:140px" onchange="loadCards()">
      <option value="">كل البروكرات</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label">نوع الحساب</span>
    <select id="f-kind" class="form-control" onchange="loadCards()">
      <option value="">الكل</option>
      <option value="new">🟢 جديد NEW</option>
      <option value="sub">🔵 فرعي SUB</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label">الحالة</span>
    <select id="f-status" class="form-control" onchange="loadCards()">
      <option value="">الكل</option>
      <option value="active">✅ عادي</option>
      <option value="modified">✏️ معدّل</option>
      <option value="new_added">🆕 مضاف جديد</option>
      <option value="inactive">⛔ غير نشط</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label">المصدر</span>
    <select id="f-source" class="form-control" onchange="loadCards()">
      <option value="">الكل</option>
      <option value="regular">🏦 عادي</option>
      <option value="cc">📞 من CC</option>
    </select>
  </div>
  <div class="filter-group" style="flex:1;min-width:180px">
    <span class="filter-label">بحث</span>
    <input type="text" id="f-search" class="form-control" placeholder="رقم حساب / اسم بروكر / مسوّق..." oninput="debounceLoad()">
  </div>
  <div style="display:flex;flex-direction:column;gap:4px">
    <span class="filter-label">&nbsp;</span>
    <div style="display:flex;gap:6px">
      <button class="btn btn-ghost btn-sm" onclick="clearFilters()" title="مسح الفلاتر">✕</button>
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportExcel()">📗 Excel</button>
      <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)" onclick="exportPdf()">📄 PDF</button>
    </div>
  </div>
</div>

<!-- Legend -->
<div class="legend">
  <strong style="color:var(--tx)">دليل الألوان:</strong>
  <span><span class="legend-dot" style="background:#7b68ee;border-radius:3px"></span> كرت CC (مركز الاتصال)</span>
  <span><span class="legend-dot" style="background:#f5a623;border-radius:3px"></span> معدّل</span>
  <span><span class="legend-dot" style="background:#22c97a;border-radius:3px"></span> مضاف جديد</span>
  <span style="color:#e05050">عمولة >5$</span>
  <span style="color:#f5a623">عمولة 2-5$</span>
  <span style="color:#22c97a">عمولة ≤2$</span>
</div>

<!-- Table -->
<div class="panel" style="padding:0">
  <div class="panel-header" style="padding:12px 16px">
    <div class="panel-title">🗂 كروت العمولات <span id="cards-count" style="font-size:11px;color:var(--mu);font-weight:400;margin-right:8px"></span></div>
    <div id="cards-summary" style="font-size:11px;color:var(--mu)"></div>
  </div>
  <div class="table-scroll">
    <table class="data-table" id="cards-table">
      <thead>
        <tr>
          <th>رقم الحساب</th>
          <th>الشهر</th>
          <th>الفرع</th>
          <th>البروكر</th>
          <th>ع. البروكر</th>
          <th>المسوّق</th>
          <th>ع. المسوّق</th>
          <th>خارجي 1</th>
          <th>خارجي 2</th>
          <th>إجمالي ع.</th>
          <th>إيداع أولي</th>
          <th>إيداع شهري</th>
          <th>نوع</th>
          <th>الحالة</th>
          <th>إجراءات</th>
        </tr>
      </thead>
      <tbody id="cards-tbody">
        <tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)">
          <div style="font-size:32px;opacity:.3;margin-bottom:8px">⏳</div>جاري التحميل...
        </td></tr>
      </tbody>
      <tfoot id="cards-tfoot" style="display:none">
        <tr style="background:var(--bg2);font-weight:700">
          <td colspan="4" style="padding:8px 12px;font-size:11px;color:var(--mu)">المجاميع ↓</td>
          <td id="tf-bcomm" class="mono" style="font-size:11px"></td>
          <td></td>
          <td id="tf-mcomm" class="mono" style="font-size:11px"></td>
          <td colspan="2"></td>
          <td id="tf-total-comm" class="mono" style="color:var(--or);font-size:11px"></td>
          <td id="tf-dep"  class="mono" style="color:var(--pri2);font-size:11px"></td>
          <td id="tf-mon"  class="mono" style="color:var(--gr);font-size:11px"></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:11px;color:var(--mu)" id="pagination-info"></span>
    <div style="display:flex;gap:6px" id="pagination-btns"></div>
  </div>
</div>

<!-- History Modal -->
<div id="history-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;overflow-y:auto">
  <div style="max-width:600px;margin:40px auto;background:var(--surface,var(--bg3));border-radius:16px;padding:24px;border:1px solid var(--brd1)">
    <div style="display:flex;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:15px">📋 سجل التعديلات</h3>
      <button onclick="document.getElementById('history-modal').style.display='none'" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--mu)">✕</button>
    </div>
    <div id="history-body"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let allCards = [], filteredCards = [];

// ── Commission color helper ──────────────────────────────────
function commColor(val) {
  const v = parseFloat(val) || 0;
  if (v > 5)  return 'comm-high';
  if (v > 2)  return 'comm-mid';
  return 'comm-low';
}

function commFmt(val) {
  const v = parseFloat(val) || 0;
  if (v === 0) return '<span style="color:var(--mu);font-size:10px">—</span>';
  return `<span class="mono ${commColor(v)}">$${v}/lot</span>`;
}

// ── Load filter dropdowns ────────────────────────────────────
async function loadFilterOptions() {
  const r = await api('GET', '/cards?per_page=500');
  if (!r.success) return;
  const cards = r.data?.data || [];

  const months  = [...new Set(cards.map(c => c.month))].sort().reverse();
  const brokers = [...new Set(cards.map(c => c.broker?.name).filter(Boolean))].sort();

  const mSel = document.getElementById('f-month');
  months.forEach(m => { const o = document.createElement('option'); o.value = o.textContent = m; mSel.appendChild(o); });

  const bSel = document.getElementById('f-broker');
  brokers.forEach(b => { const o = document.createElement('option'); o.value = o.textContent = b; bSel.appendChild(o); });
}

// ── Load cards ───────────────────────────────────────────────
async function loadCards() {
  const params = new URLSearchParams();
  const month  = document.getElementById('f-month').value;
  const broker = document.getElementById('f-broker').value;
  const kind   = document.getElementById('f-kind').value;
  const status = document.getElementById('f-status').value;
  const search = document.getElementById('f-search').value;
  const source = document.getElementById('f-source').value;

  if (month)  params.set('month', month);
  if (kind)   params.set('kind', kind);
  if (status) params.set('status', status);
  if (search) params.set('search', search);
  params.set('per_page', 200);

  document.getElementById('cards-tbody').innerHTML = `
    <tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)">
      <div style="font-size:28px;opacity:.3;margin-bottom:8px">⏳</div>جاري التحميل...</td></tr>`;

  const r = await api('GET', '/cards?' + params);
  if (!r.success) { toast('خطأ في تحميل البيانات', 'error'); return; }

  allCards = r.data?.data || [];

  // Apply source filter client-side
  filteredCards = source === 'cc'      ? allCards.filter(c => !!c.cc_branch_id) :
                  source === 'regular' ? allCards.filter(c => !c.cc_branch_id)  : allCards;

  updateKpis(filteredCards);
  renderTable(filteredCards);
}

// ── Update KPI strip ─────────────────────────────────────────
function updateKpis(cards) {
  const total   = cards.length;
  const newAcc  = cards.filter(c => c.account_kind === 'new').length;
  const subAcc  = cards.filter(c => c.account_kind === 'sub').length;
  const modAcc  = cards.filter(c => c.status === 'modified').length;
  const ccAcc   = cards.filter(c => !!c.cc_branch_id).length;
  const dep     = cards.reduce((s,c) => s + parseFloat(c.initial_deposit || 0), 0);
  const avgComm = total ? (cards.reduce((s,c) => s +
    parseFloat(c.broker_commission||0) + parseFloat(c.marketer_commission||0) +
    parseFloat(c.ext_commission1||0)  + parseFloat(c.ext_commission2||0), 0) / total) : 0;

  document.getElementById('kpi-total').textContent    = total.toLocaleString('ar');
  document.getElementById('kpi-new').textContent      = newAcc.toLocaleString('ar');
  document.getElementById('kpi-sub').textContent      = subAcc.toLocaleString('ar');
  document.getElementById('kpi-modified').textContent = modAcc.toLocaleString('ar');
  document.getElementById('kpi-cc').textContent       = ccAcc.toLocaleString('ar');
  document.getElementById('kpi-dep').textContent      = fmtK(dep);
  document.getElementById('kpi-avgcomm').textContent  = '$' + avgComm.toFixed(1);
  document.getElementById('cards-count').textContent  = total + ' سجل';
}

// ── Render table ─────────────────────────────────────────────
function renderTable(cards) {
  const tbody = document.getElementById('cards-tbody');
  const tfoot = document.getElementById('cards-tfoot');

  if (!cards.length) {
    tbody.innerHTML = '<tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:36px;opacity:.2;margin-bottom:8px">📭</div>لا توجد نتائج</td></tr>';
    tfoot.style.display = 'none';
    return;
  }

  const statusChip = s => ({
    modified:  '<span class="chip chip-modified">✏️ معدّل</span>',
    new_added: '<span class="chip chip-new">🆕 جديد</span>',
    active:    '<span class="chip chip-active">✅ عادي</span>',
    inactive:  '<span class="chip chip-inactive">⛔ متوقف</span>',
  }[s] || `<span class="chip chip-active">${s}</span>`);

  tbody.innerHTML = cards.map(c => {
    const isCc       = !!c.cc_branch_id;
    const totalComm  = (parseFloat(c.broker_commission)||0) + (parseFloat(c.marketer_commission)||0) +
                       (parseFloat(c.ext_commission1)||0) + (parseFloat(c.ext_commission2)||0);
    const rowClass   = isCc ? 'row-cc-card' :
                       c.status === 'modified'  ? 'row-modified' :
                       c.status === 'new_added' ? 'row-new_added' :
                       c.status === 'inactive'  ? 'row-inactive'  : '';

    const ext1 = c.ext_marketer1?.name
      ? `<span style="color:var(--pu);font-size:11px">${c.ext_marketer1.name}</span><br><span class="mono ${commColor(c.ext_commission1)}" style="font-size:10px">$${c.ext_commission1}/lot</span>`
      : '<span style="color:var(--mu);font-size:10px">—</span>';
    const ext2 = c.ext_marketer2?.name
      ? `<span style="color:var(--pu);font-size:11px">${c.ext_marketer2.name}</span><br><span class="mono ${commColor(c.ext_commission2)}" style="font-size:10px">$${c.ext_commission2}/lot</span>`
      : '<span style="color:var(--mu);font-size:10px">—</span>';

    return `
    <tr class="${rowClass}" style="cursor:default">
      <td>
        ${isCc ? '<span class="chip chip-cc" style="margin-left:4px;font-size:9px">📞 CC</span>' : ''}
        <span class="ac-num" style="font-size:13px;font-weight:800">#${c.account_number}</span>
      </td>
      <td style="color:var(--mu);font-size:11px;white-space:nowrap">${c.month}</td>
      <td style="font-size:11px;color:var(--mu)">${c.branch?.name_ar || '—'}</td>
      <td style="font-weight:700;color:var(--pri2)">${c.broker?.name || '—'}</td>
      <td>${commFmt(c.broker_commission)}</td>
      <td style="color:var(--m2);font-size:11px">${c.marketer?.name && c.marketer.name !== c.broker?.name ? c.marketer.name : '<span style="color:var(--mu)">—</span>'}</td>
      <td>${commFmt(c.marketer_commission)}</td>
      <td style="font-size:11px">${ext1}</td>
      <td style="font-size:11px">${ext2}</td>
      <td><span class="mono ${commColor(totalComm)}" style="font-weight:700">$${totalComm.toFixed(1)}</span></td>
      <td class="mono" style="color:var(--pri2);font-weight:600">${fmt(c.initial_deposit)}</td>
      <td class="mono" style="color:var(--gr)">${fmt(c.monthly_deposit)}</td>
      <td><span class="chip ${c.account_kind === 'new' ? 'chip-new-acc' : 'chip-sub-acc'}">${c.account_kind === 'new' ? '🟢 NEW' : '🔵 SUB'}</span></td>
      <td>${statusChip(c.status)}</td>
      <td>
        <a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm" style="padding:4px 8px">✏️</a>
        ${c.status === 'modified' ? `<button class="btn btn-sm" style="background:rgba(245,166,35,.1);color:var(--or);border:1px solid rgba(245,166,35,.25);padding:4px 8px" onclick="viewHistory(${c.id})">📋</button>` : ''}
      </td>
    </tr>`;
  }).join('');

  // Update footer totals
  const totalBComm = cards.reduce((s,c) => s + (parseFloat(c.broker_commission)||0), 0);
  const totalMComm = cards.reduce((s,c) => s + (parseFloat(c.marketer_commission)||0), 0);
  const totalAll   = cards.reduce((s,c) => s +
    (parseFloat(c.broker_commission)||0) + (parseFloat(c.marketer_commission)||0) +
    (parseFloat(c.ext_commission1)||0) + (parseFloat(c.ext_commission2)||0), 0);
  const totalDep   = cards.reduce((s,c) => s + (parseFloat(c.initial_deposit)||0), 0);
  const totalMon   = cards.reduce((s,c) => s + (parseFloat(c.monthly_deposit)||0), 0);

  document.getElementById('tf-bcomm').innerHTML      = `$${(totalBComm/cards.length).toFixed(1)} متوسط`;
  document.getElementById('tf-mcomm').innerHTML      = `$${(totalMComm/cards.length).toFixed(1)} متوسط`;
  document.getElementById('tf-total-comm').innerHTML = `$${(totalAll/cards.length).toFixed(1)} متوسط`;
  document.getElementById('tf-dep').innerHTML        = fmtK(totalDep);
  document.getElementById('tf-mon').innerHTML        = fmtK(totalMon);
  tfoot.style.display = '';
}

// ── Clear filters ────────────────────────────────────────────
function clearFilters() {
  ['f-month','f-broker','f-kind','f-status','f-source'].forEach(id => document.getElementById(id).value = '');
  document.getElementById('f-search').value = '';
  loadCards();
}

// ── Debounce ─────────────────────────────────────────────────
let debounceTimer;
function debounceLoad() { clearTimeout(debounceTimer); debounceTimer = setTimeout(loadCards, 380); }

// ── View history ─────────────────────────────────────────────
async function viewHistory(id) {
  const r = await api('GET', `/cards/${id}`);
  if (!r.success) return;
  const mods = r.data?.modifications || [];
  const modal = document.getElementById('history-modal');
  const body  = document.getElementById('history-body');

  if (!mods.length) { body.innerHTML = '<p style="color:var(--mu);text-align:center">لا توجد تعديلات مسجّلة</p>'; }
  else {
    body.innerHTML = mods.map(m => `
      <div style="border:1px solid var(--brd1);border-radius:10px;padding:12px;margin-bottom:8px">
        <div style="font-size:11px;color:var(--mu);margin-bottom:6px">${m.modified_at || ''}</div>
        <div style="font-size:12px">
          ${Object.entries(m.changes || {}).map(([k,v]) =>
            `<span style="background:var(--bg2);border-radius:6px;padding:2px 8px;margin:2px;display:inline-block;font-size:11px">
              <strong>${k}:</strong> <del style="color:var(--re)">${v.old ?? '—'}</del> → <span style="color:var(--gr)">${v.new ?? '—'}</span>
            </span>`).join('')}
        </div>
      </div>`).join('');
  }
  modal.style.display = 'block';
}

// ── Export Excel ─────────────────────────────────────────────
function exportExcel() {
  if (!filteredCards.length) { toast('لا توجد بيانات للتصدير', 'error'); return; }
  const hdr = ['رقم الحساب','الشهر','الفرع','البروكر','ع.البروكر','المسوّق','ع.المسوّق','خارجي 1','ع.خ1','خارجي 2','ع.خ2','إجمالي ع.','إيداع أولي','إيداع شهري','نوع','الحالة','المصدر'];
  const rows = [hdr, ...filteredCards.map(c => {
    const tot = (parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
    return [
      c.account_number, c.month, c.branch?.name_ar||'',
      c.broker?.name||'', '$'+c.broker_commission+'/lot',
      c.marketer?.name||'—', '$'+c.marketer_commission+'/lot',
      c.ext_marketer1?.name||'—', '$'+(c.ext_commission1||0)+'/lot',
      c.ext_marketer2?.name||'—', '$'+(c.ext_commission2||0)+'/lot',
      '$'+tot.toFixed(2)+'/lot',
      c.initial_deposit, c.monthly_deposit,
      c.account_kind === 'new' ? 'NEW' : 'SUB', c.status,
      c.cc_branch_id ? 'CC' : 'عادي',
    ];
  })];
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(rows);
  ws['!cols'] = hdr.map(() => ({ wch: 14 }));
  XLSX.utils.book_append_sheet(wb, ws, 'كروت العمولات');
  XLSX.writeFile(wb, 'WafraGulf_Cards_' + new Date().toISOString().slice(0,10) + '.xlsx');
  toast('تم تحميل Excel ✅', 'success');
}

// ── Export PDF ────────────────────────────────────────────────
function exportPdf() {
  if (!filteredCards.length) { toast('لا توجد بيانات للتصدير', 'error'); return; }
  const { jsPDF } = window.jspdf;
  const doc = new jsPDF({ orientation:'landscape', unit:'mm', format:'a3' });
  doc.setFontSize(14); doc.setTextColor(46,134,171);
  doc.text('وفرة الخليجية — كروت العمولات', 210, 14, {align:'center'});
  doc.setFontSize(9); doc.setTextColor(100,120,140);
  doc.text('تاريخ التصدير: ' + new Date().toLocaleDateString('ar'), 14, 14);
  doc.autoTable({
    startY: 22,
    head: [['AC No.','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Ext1','Ext2','Total','Initial','Monthly','Kind','Status','Src']],
    body: filteredCards.map(c => {
      const tot = (parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
      return [
        c.account_number, c.month, c.branch?.name_ar||'',
        c.broker?.name||'—', '$'+c.broker_commission,
        c.marketer?.name||'—', '$'+c.marketer_commission,
        c.ext_marketer1?.name||'—', c.ext_marketer2?.name||'—',
        '$'+tot.toFixed(1),
        fmt(c.initial_deposit), fmt(c.monthly_deposit),
        c.account_kind?.toUpperCase(), c.status,
        c.cc_branch_id ? 'CC' : '-',
      ];
    }),
    styles: { fontSize:7, cellPadding:1.5 },
    headStyles: { fillColor:[46,134,171], textColor:255, fontStyle:'bold' },
    alternateRowStyles: { fillColor:[245,248,252] },
    didParseCell: d => {
      if (d.section==='body') {
        const src = d.row.raw?.[14];
        if (src === 'CC') { d.cell.styles.fillColor = [240,236,255]; }
        if (d.row.raw?.[13] === 'modified') { d.cell.styles.fillColor = [255,247,230]; }
      }
    }
  });
  doc.save('WafraGulf_Cards_' + new Date().toISOString().slice(0,10) + '.pdf');
  toast('تم تحميل PDF ✅', 'success');
}

loadFilterOptions();
loadCards();
</script>
@endpush
