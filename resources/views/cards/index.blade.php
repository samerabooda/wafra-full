@extends('layouts.app')
@section('title','كروت العمولات')
@section('page-title','كروت العمولات')

@push('styles')
<style>
/* ── Toolbar ── */
.tbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;padding:12px 16px;border-bottom:1px solid var(--brd1);background:var(--bg2)}
.tbar-search{flex:1;min-width:160px;max-width:260px}
.tbar-right{display:flex;align-items:center;gap:6px;margin-right:auto}

/* ── Dense Table ── */
.dense-table{width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed}
.dense-table colgroup col{}
.dense-table thead th{
  background:var(--bg3);padding:7px 10px;font-weight:600;color:var(--mu);
  border-bottom:1px solid var(--brd1);border-left:1px solid var(--brd1);
  white-space:nowrap;user-select:none;cursor:pointer;text-align:right;
  position:sticky;top:0;z-index:2
}
.dense-table thead th:first-child{border-right:none}
.dense-table thead th:hover{background:var(--bg2);color:var(--tx)}
.dense-table thead th .sort-icon{font-size:9px;opacity:.4;margin-right:3px}
.dense-table thead th.sort-asc .sort-icon::after{content:'▲';opacity:1;color:var(--pri2)}
.dense-table thead th.sort-desc .sort-icon::after{content:'▼';opacity:1;color:var(--pri2)}
.dense-table thead th.sort-asc,
.dense-table thead th.sort-desc{color:var(--pri2)}
.dense-table tbody td{
  padding:6px 10px;border-bottom:1px solid var(--brd1);
  border-left:1px solid rgba(255,255,255,.04);
  color:var(--tx);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:right
}
.dense-table tbody tr:hover td{background:rgba(20,184,126,.04)}
.dense-table tbody tr.row-modified td{background:rgba(245,166,35,.05)}
.dense-table tbody tr.row-cc td{border-right:3px solid rgba(20,184,126,.5);background:rgba(20,184,126,.03)}
.dense-table tbody tr.row-deleted td{opacity:.4;text-decoration:line-through}

/* ── Col widths ── */
.col-seq{width:46px}  .col-acn{width:110px} .col-branch{width:90px}
.col-month{width:90px}.col-broker{width:110px}.col-bc{width:62px}
.col-mkt{width:100px} .col-mc{width:62px}   .col-ext1{width:96px}
.col-ec1{width:62px}  .col-dep{width:86px}  .col-kind{width:68px}
.col-status{width:72px}.col-act{width:68px}

/* ── Cell types ── */
.c-mono{font-family:var(--font-mono,monospace);color:var(--pri2);font-size:11px}
.c-green{color:var(--gr)}
.c-muted{color:var(--mu)}
.c-num{font-family:monospace;font-size:11px;color:var(--gr)}
.c-center{text-align:center!important}

/* ── Badges ── */
.badge-cc{display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;
  padding:1px 6px;border-radius:10px;background:rgba(29,158,117,.15);
  color:#1D9E75;border:1px solid rgba(29,158,117,.25)}
.badge-mod{font-size:9px;padding:1px 5px;border-radius:4px;
  background:rgba(245,166,35,.15);color:#EF9F27;border:1px solid rgba(245,166,35,.2)}
.badge-new{font-size:9px;padding:1px 5px;border-radius:4px;
  background:rgba(29,158,117,.12);color:#1D9E75}
.badge-sub{font-size:9px;padding:1px 5px;border-radius:4px;
  background:rgba(83,74,183,.12);color:#9D98E8}
.cc-status-dot{width:7px;height:7px;border-radius:50%;display:inline-block;margin-left:4px;flex-shrink:0}
.csd-pending{background:#EF9F27}.csd-accepted{background:var(--pri2)}
.csd-completed{background:var(--gr)}.csd-rejected{background:var(--re)}

/* ── Pagination ── */
.pager{display:flex;align-items:center;gap:6px;padding:10px 16px;
  border-top:1px solid var(--brd1);background:var(--bg2);flex-wrap:wrap}
.pager-info{font-size:11px;color:var(--mu);flex:1;min-width:120px}
.page-btn{padding:3px 9px;border-radius:5px;font-size:11px;cursor:pointer;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);font-family:'Tajawal',sans-serif}
.page-btn:hover{border-color:var(--pri2);color:var(--pri2)}
.page-btn.active{background:var(--pri2);border-color:var(--pri2);color:white}
.page-btn:disabled{opacity:.35;cursor:default}
.per-page-sel{font-size:11px;padding:3px 6px;border-radius:5px;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu)}

/* ── Action btns in row ── */
.act-btn{padding:2px 8px;border-radius:4px;font-size:10px;cursor:pointer;
  border:1px solid var(--brd1);background:none;color:var(--mu);font-family:'Tajawal',sans-serif}
.act-btn:hover{border-color:var(--pri2);color:var(--pri2)}
.act-btn.danger:hover{border-color:var(--re);color:var(--re)}

/* ── Export ── */
.export-group{display:flex;gap:4px}
.exp-btn{padding:4px 10px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);font-family:'Tajawal',sans-serif;
  display:flex;align-items:center;gap:4px;transition:all .15s}
.exp-btn:hover{border-color:var(--pri2);color:var(--pri2)}

/* ── Table scroll container ── */
.table-outer{overflow-x:auto;-webkit-overflow-scrolling:touch}

/* ── Column chooser ── */
.col-chooser{display:flex;gap:4px;flex-wrap:wrap;padding:10px 14px;
  background:var(--bg2);border-bottom:1px solid var(--brd1)}
.col-toggle{padding:2px 8px;border-radius:12px;font-size:10px;cursor:pointer;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);
  font-family:'Tajawal',sans-serif;transition:all .15s}
.col-toggle.on{background:rgba(46,134,171,.12);border-color:var(--pri2);color:var(--pri2)}

/* ── Summary bar ── */
.summary-bar{display:flex;gap:16px;padding:8px 16px;background:var(--bg3);
  border-bottom:1px solid var(--brd1);flex-wrap:wrap}
.sb-item{font-size:11px;color:var(--mu);display:flex;align-items:center;gap:5px}
.sb-item strong{color:var(--tx);font-weight:600}

@media(max-width:768px){
  .tbar{gap:6px}
  .tbar-search{max-width:100%;width:100%}
  .tbar-right{width:100%;justify-content:space-between}
  .col-chooser{display:none}
  .summary-bar{display:none}
}
</style>
@endpush

@section('topbar-actions')
@if(auth()->user()?->isFinanceAdmin())
  <a href="{{ route('import.index') }}" class="btn btn-sm btn-ghost">↑ استيراد Excel</a>
@endif
<a href="{{ route('cards.create') }}" class="btn btn-sm btn-primary">+ كارت جديد</a>
@endsection

@section('content')

{{-- ═══ FILTER TOOLBAR ═══════════════════════════════════════ --}}
<div class="tbar">
  {{-- Search --}}
  <div class="tbar-search">
    <input type="text" id="f-search" class="form-control form-control-sm"
           placeholder="بحث برقم الحساب..." oninput="debounceSearch()" autocomplete="off">
  </div>

  {{-- Filters --}}
  @if(auth()->user()?->isFinanceAdmin())
  <select id="f-branch" class="form-control form-control-sm" style="min-width:110px" onchange="loadCards()">
    <option value="">كل الفروع</option>
  </select>
  @endif

  <input type="month" id="f-month" class="form-control form-control-sm"
         min="2010-01" max="2065-12" style="width:150px" onchange="loadCards()">

  <select id="f-broker" class="form-control form-control-sm" style="min-width:110px" onchange="loadCards()">
    <option value="">كل البروكرين</option>
  </select>

  <select id="f-kind" class="form-control form-control-sm" onchange="loadCards()">
    <option value="">كل الأنواع</option>
    <option value="new">جديد</option>
    <option value="sub">فرعي</option>
  </select>

  <select id="f-status" class="form-control form-control-sm" onchange="loadCards()">
    <option value="">كل الحالات</option>
    <option value="active">نشط</option>
    <option value="modified">معدّل</option>
    <option value="new_added">جديد</option>
    <option value="inactive">غير نشط</option>
  </select>

  <select id="f-cc" class="form-control form-control-sm" onchange="loadCards()">
    <option value="">عادي + CC</option>
    <option value="cc_only">CC فقط</option>
    <option value="normal_only">عادي فقط</option>
  </select>

  <div class="tbar-right">
    <button class="btn btn-ghost btn-sm" onclick="clearFilters()">مسح</button>
    <div class="export-group">
      <button class="exp-btn" onclick="exportExcel()">⬇ Excel</button>
      <button class="exp-btn" onclick="exportPdf()">⬇ PDF</button>
    </div>
  </div>
</div>

{{-- ═══ COLUMN CHOOSER ════════════════════════════════════════ --}}
<div class="col-chooser" id="col-chooser">
  <span style="font-size:10px;color:var(--mu);margin-left:6px;align-self:center">الأعمدة:</span>
</div>

{{-- ═══ SUMMARY BAR ════════════════════════════════════════════ --}}
<div class="summary-bar" id="summary-bar">
  <div class="sb-item">إجمالي: <strong id="sb-total">—</strong></div>
  <div class="sb-item">إيداعات: <strong id="sb-dep">—</strong></div>
  <div class="sb-item">متوسط بروكر: <strong id="sb-avg-bc">—</strong></div>
  <div class="sb-item">كروت CC: <strong id="sb-cc">—</strong></div>
</div>

{{-- ═══ TABLE ══════════════════════════════════════════════════ --}}
<div class="table-outer" id="table-outer">
<table class="dense-table" id="main-table">
  <colgroup>
    <col class="col-seq"><col class="col-acn"><col class="col-branch">
    <col class="col-month"><col class="col-broker"><col class="col-bc">
    <col class="col-mkt"><col class="col-mc">
    <col class="col-ext1"><col class="col-ec1">
    <col class="col-dep"><col class="col-kind">
    <col class="col-status"><col class="col-act">
  </colgroup>
  <thead>
    <tr>
      <th class="c-center" style="width:46px;cursor:default" title="التسلسل">م</th>
      <th onclick="sortBy('account_number')"              data-col="account_number">رقم الحساب<span class="sort-icon"></span></th>
      <th onclick="sortBy('branch')"  data-col="branch"  id="th-branch">الفرع<span class="sort-icon"></span></th>
      <th onclick="sortBy('month_date')" data-col="month_date">الشهر<span class="sort-icon"></span></th>
      <th onclick="sortBy('broker')"  data-col="broker">البروكر<span class="sort-icon"></span></th>
      <th onclick="sortBy('broker_commission')" data-col="broker_commission" class="c-center">ع. بروكر<span class="sort-icon"></span></th>
      <th onclick="sortBy('marketer')" data-col="marketer">المسوّق<span class="sort-icon"></span></th>
      <th onclick="sortBy('marketer_commission')" data-col="marketer_commission" class="c-center">ع. مسوّق<span class="sort-icon"></span></th>
      <th onclick="sortBy('ext_marketer1')" data-col="ext_marketer1">مسوّق خارجي / Ext. Mktr<span class="sort-icon"></span></th>
      <th onclick="sortBy('ext_commission1')" data-col="ext_commission1" class="c-center">ع. خارجي<span class="sort-icon"></span></th>
      <th onclick="sortBy('initial_deposit')" data-col="initial_deposit" class="c-center">إيداع أولي / Init. Deposit<span class="sort-icon"></span></th>
      <th class="c-center" data-col="account_kind">نوع / Type</th>
      <th class="c-center" data-col="status">الحالة</th>
      <th class="c-center" data-col="actions">إجراءات / Actions</th>
    </tr>
  </thead>
  <tbody id="cards-tbody">
    <tr><td colspan="14" style="text-align:center;padding:50px;color:var(--mu)">
      <div style="font-size:28px;opacity:.25;margin-bottom:8px">⏳</div>جاري التحميل...
    </td></tr>
  </tbody>
</table>
</div>

{{-- ═══ PAGINATION ═════════════════════════════════════════════ --}}
<div class="pager" id="pager" style="display:none">
  <div class="pager-info" id="pager-info"></div>
  <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
    <select class="per-page-sel" id="per-page" onchange="changePerPage()">
      <option value="50">50 / صفحة</option>
      <option value="100">100 / صفحة</option>
      <option value="200">200 / صفحة</option>
    </select>
    <div id="page-btns" style="display:flex;gap:4px;flex-wrap:wrap"></div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// ── State ──────────────────────────────────────────────────────
let allCards     = [];
let filteredCards= [];
let currentPage  = 1;
let perPage      = 50;
let sortCol      = 'month_date';
let sortDir      = 'desc';
let debounceTimer;
const IS_FA      = {{ auth()->user()?->isFinanceAdmin() ? 'true' : 'false' }};
const MY_BRANCH  = {{ auth()->user()?->branch_id ?? 'null' }};

// ── Column definitions ─────────────────────────────────────────
const COLS = [
  { id:'account_number',   on:true },
  { id:'branch',           on:true,  faOnly:true  },
  { id:'month_date',       on:true },
  { id:'broker',           on:true },
  { id:'broker_commission',on:true },
  { id:'marketer',         on:true },
  { id:'marketer_commission',on:true},
  { id:'ext_marketer1',    on:true },
  { id:'ext_commission1',  on:false },
  { id:'initial_deposit',  on:true },
  { id:'account_kind',     on:true },
  { id:'status',           on:true },
  { id:'actions',          on:true },
];

// ── Init ───────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  await Promise.all([loadBranches(), loadBrokers()]);
  await loadCards();
  buildColChooser();
  hideUnneeded();
});

function hideUnneeded() {
  if (!IS_FA) {
    document.getElementById('th-branch')?.parentNode.querySelectorAll('th')[2]
      ?.closest('table')?.querySelectorAll('colgroup col')[2]?.setAttribute('style','width:0;display:none');
  }
}

// ── Data load ──────────────────────────────────────────────────
async function loadCards() {
  const tbody = document.getElementById('cards-tbody');
  tbody.innerHTML = `<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>`;

  const params = new URLSearchParams({ per_page: 500 });
  const month  = document.getElementById('f-month')?.value;
  const broker = document.getElementById('f-broker')?.value;
  const branch = document.getElementById('f-branch')?.value;
  const kind   = document.getElementById('f-kind')?.value;
  const status = document.getElementById('f-status')?.value;
  const search = document.getElementById('f-search')?.value?.trim();
  const cc     = document.getElementById('f-cc')?.value;

  if (month)  { const d=new Date(month+'-01'); params.set('month', d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear()); }
  if (broker) params.set('broker_id', broker);
  if (branch) params.set('branch_id', branch);
  if (kind)   params.set('account_kind', kind);
  if (status) params.set('status', status);
  if (search) params.set('search', search);

  const r = await api('GET', '/cards?' + params);
  if (!r.success) { tbody.innerHTML = `<tr><td colspan="14" style="text-align:center;padding:30px;color:var(--re)">فشل التحميل</td></tr>`; return; }

  allCards = r.data?.data || r.data || [];

  // Client-side CC filter
  if (cc === 'cc_only')     filteredCards = allCards.filter(c => c.cc_branch_id);
  else if (cc === 'normal_only') filteredCards = allCards.filter(c => !c.cc_branch_id);
  else filteredCards = [...allCards];

  currentPage = 1;
  sortAndRender();
  updateSummary();
}

// ── Sort ───────────────────────────────────────────────────────
function sortBy(col) {
  if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
  else { sortCol = col; sortDir = 'asc'; }
  document.querySelectorAll('.dense-table thead th').forEach(th => {
    th.classList.remove('sort-asc','sort-desc');
    if (th.dataset.col === col) th.classList.add(sortDir === 'asc' ? 'sort-asc' : 'sort-desc');
  });
  sortAndRender();
}

function sortAndRender() {
  const sorted = [...filteredCards].sort((a, b) => {
    let va = getSortVal(a, sortCol), vb = getSortVal(b, sortCol);
    if (typeof va === 'string') va = va.toLowerCase();
    if (typeof vb === 'string') vb = vb.toLowerCase();
    return sortDir === 'asc' ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
  });
  renderPage(sorted);
}

function getSortVal(c, col) {
  switch(col) {
    case 'account_number': return c.account_number || '';
    case 'branch':         return c.branch?.name_ar || '';
    case 'month_date':     return c.month_date || '';
    case 'broker':         return c.broker?.name || '';
    case 'broker_commission': return parseFloat(c.broker_commission) || 0;
    case 'marketer':       return c.marketer?.name || '';
    case 'marketer_commission': return parseFloat(c.marketer_commission) || 0;
    case 'ext_marketer1':  return c.ext_marketer1?.name || '';
    case 'ext_commission1':return parseFloat(c.ext_commission1) || 0;
    case 'initial_deposit':return parseFloat(c.initial_deposit) || 0;
    default: return '';
  }
}

// ── Render ─────────────────────────────────────────────────────
function renderPage(sorted) {
  const total = sorted.length;
  const pages = Math.max(1, Math.ceil(total / perPage));
  if (currentPage > pages) currentPage = pages;
  const start = (currentPage - 1) * perPage;
  const slice = sorted.slice(start, start + perPage);

  const tbody = document.getElementById('cards-tbody');

  if (!slice.length) {
    tbody.innerHTML = `<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">
      <div style="font-size:24px;opacity:.25;margin-bottom:6px">📭</div>لا توجد نتائج
    </td></tr>`;
    document.getElementById('pager').style.display = 'none';
    return;
  }

  tbody.innerHTML = slice.map((c, i) => {
    const seq    = start + i + 1;
    const isCC   = !!c.cc_branch_id;
    const isMod  = c.status === 'modified';
    const rowCls = `${isMod ? 'row-modified' : ''} ${isCC ? 'row-cc' : ''}`;

    const ccBadge = isCC ? `<span class="badge-cc" title="${c.cc_branch?.name_ar||'CC'}">CC</span> ` : '';

    // CC status dot
    const ccDot = isCC ? `<span class="cc-status-dot ${
      {branch_pending:'csd-pending',accepted:'csd-accepted',completed:'csd-completed',rejected:'csd-rejected'}[c.cc_status] || ''
    }"></span>` : '';

    const broker  = c.broker?.name  || '<span class="c-muted">—</span>';
    const marketer= c.marketer?.name || '<span class="c-muted">—</span>';
    const ext1    = c.ext_marketer1?.name || '<span class="c-muted">—</span>';
    const bc  = +c.broker_commission   > 0 ? `<span class="c-num">$${(+c.broker_commission).toFixed(2)}</span>`  : '<span class="c-muted">—</span>';
    const mc  = +c.marketer_commission > 0 ? `<span class="c-num">$${(+c.marketer_commission).toFixed(2)}</span>` : '<span class="c-muted">—</span>';
    const ec1 = +c.ext_commission1     > 0 ? `<span class="c-num">$${(+c.ext_commission1).toFixed(2)}</span>`     : '<span class="c-muted">—</span>';
    const dep = +c.initial_deposit > 0
      ? `<span class="c-green">$${(+c.initial_deposit).toLocaleString()}</span>`
      : '<span class="c-muted">—</span>';
    const kind = c.account_kind === 'sub'
      ? `<span class="badge-sub">فرعي</span>`
      : `<span class="badge-new">جديد</span>`;
    const statusBadge = isMod ? `<span class="badge-mod">معدّل</span>` : '';

    const actEdit = `<button class="act-btn" onclick="goEdit(${c.id})">تعديل</button>`;
    const actDel  = IS_FA ? ` <button class="act-btn danger" onclick="deleteCard(${c.id})">حذف</button>` : '';

    const branchCell = IS_FA ? `<td class="c-muted" style="font-size:11px">${c.branch?.name_ar||'—'}</td>` : '';

    // Month display - Arabic
    const monthAr = c.month ? arMonth(c.month) : '—';

    return `<tr class="${rowCls}" data-id="${c.id}">
      <td class="c-center c-muted" style="font-size:10px">${seq}</td>
      <td class="c-mono">${ccBadge}#${c.account_number}${ccDot}</td>
      ${branchCell}
      <td style="font-size:11px;white-space:nowrap">${monthAr}</td>
      <td style="font-size:11px">${broker}</td>
      <td class="c-center">${bc}</td>
      <td style="font-size:11px">${marketer}</td>
      <td class="c-center">${mc}</td>
      <td style="font-size:11px">${ext1}</td>
      <td class="c-center">${ec1}</td>
      <td class="c-center">${dep}</td>
      <td class="c-center">${kind}</td>
      <td class="c-center">${statusBadge}</td>
      <td class="c-center">${actEdit}${actDel}</td>
    </tr>`;
  }).join('');

  // Pagination
  document.getElementById('pager').style.display = 'flex';
  document.getElementById('pager-info').textContent =
    `عرض ${start+1}–${Math.min(start+perPage, total)} من ${total.toLocaleString()} كارت`;
  renderPageButtons(pages);
  document.getElementById('cards-count').textContent = `(${total.toLocaleString()})`;
}

function arMonth(monthStr) {
  // "May 2026" → "مايو 2026"
  const AR = {Jan:'يناير',Feb:'فبراير',Mar:'مارس',Apr:'أبريل',May:'مايو',Jun:'يونيو',
               Jul:'يوليو',Aug:'أغسطس',Sep:'سبتمبر',Oct:'أكتوبر',Nov:'نوفمبر',Dec:'ديسمبر'};
  const parts = monthStr.split(' ');
  return (AR[parts[0]] || parts[0]) + ' ' + (parts[1] || '');
}

// ── Pagination buttons ─────────────────────────────────────────
function renderPageButtons(total) {
  const cont = document.getElementById('page-btns');
  if (total <= 1) { cont.innerHTML = ''; return; }

  let btns = '';
  btns += `<button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;

  // Smart window: show first, last, and window around current
  const pages = [];
  pages.push(1);
  for (let p = Math.max(2, currentPage-2); p <= Math.min(total-1, currentPage+2); p++) pages.push(p);
  if (total > 1) pages.push(total);

  let prev = 0;
  for (const p of [...new Set(pages)]) {
    if (p - prev > 1) btns += `<span class="page-btn" style="border:none;cursor:default">…</span>`;
    btns += `<button class="page-btn ${p===currentPage?'active':''}" onclick="goPage(${p})">${p}</button>`;
    prev = p;
  }
  btns += `<button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage===total?'disabled':''}>›</button>`;
  cont.innerHTML = btns;
}

function goPage(p) {
  const sorted = [...filteredCards].sort((a, b) => {
    let va = getSortVal(a, sortCol), vb = getSortVal(b, sortCol);
    if (typeof va === 'string') { va = va.toLowerCase(); vb = vb.toLowerCase(); }
    return sortDir === 'asc' ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
  });
  const maxPage = Math.max(1, Math.ceil(sorted.length / perPage));
  currentPage = Math.max(1, Math.min(p, maxPage));
  renderPage(sorted);
  document.getElementById('table-outer')?.scrollIntoView({behavior:'smooth', block:'start'});
}

function changePerPage() {
  perPage = parseInt(document.getElementById('per-page').value);
  currentPage = 1;
  sortAndRender();
}

// ── Summary bar ────────────────────────────────────────────────
function updateSummary() {
  const cards = filteredCards;
  const totalDep = cards.reduce((s,c) => s + (+c.initial_deposit||0), 0);
  const avgBc    = cards.length ? (cards.reduce((s,c) => s + (+c.broker_commission||0), 0) / cards.length) : 0;
  const ccCount  = cards.filter(c => c.cc_branch_id).length;

  document.getElementById('sb-total').textContent  = cards.length.toLocaleString();
  document.getElementById('sb-dep').textContent    = '$' + totalDep.toLocaleString();
  document.getElementById('sb-avg-bc').textContent = '$' + avgBc.toFixed(2);
  document.getElementById('sb-cc').textContent     = ccCount.toLocaleString();
}

// ── Load filter options ────────────────────────────────────────
async function loadBranches() {
  if (!IS_FA) return;
  const r = await api('GET', '/branches');
  if (!r.success) return;
  const sel = document.getElementById('f-branch');
  if (sel) r.data.forEach(b => {
    const o = document.createElement('option'); o.value = b.id; o.textContent = b.name_ar;
    sel.appendChild(o);
  });
}

async function loadBrokers() {
  const r = await api('GET', '/employees?status=approved&role=broker');
  if (!r.success) return;
  const sel = document.getElementById('f-broker');
  if (!sel) return;
  const byBranch = {};
  r.data.forEach(e => {
    const bn = e.branch?.name_ar || 'عام';
    if (!byBranch[bn]) byBranch[bn] = [];
    byBranch[bn].push(e);
  });
  Object.entries(byBranch).forEach(([branch, emps]) => {
    const grp = document.createElement('optgroup'); grp.label = branch;
    emps.forEach(e => {
      const o = document.createElement('option'); o.value = e.id; o.textContent = e.name;
      grp.appendChild(o);
    });
    sel.appendChild(grp);
  });
}

// ── Column chooser ─────────────────────────────────────────────
const COL_LABELS = {
  account_number:'رقم الحساب', branch:'الفرع', month_date:'الشهر',
  broker:'البروكر', broker_commission:'ع. بروكر',
  marketer:'المسوّق', marketer_commission:'ع. مسوّق',
  ext_marketer1:'مسوّق خارجي', ext_commission1:'ع. خارجي',
  initial_deposit:'إيداع أولي', account_kind:'نوع', status:'الحالة', actions:'إجراءات'
};

function buildColChooser() {
  const cont = document.getElementById('col-chooser');
  if (!cont) return;
  COLS.forEach(col => {
    if (col.faOnly && !IS_FA) return;
    const btn = document.createElement('button');
    btn.className = 'col-toggle' + (col.on ? ' on' : '');
    btn.textContent = COL_LABELS[col.id] || col.id;
    btn.onclick = () => {
      col.on = !col.on;
      btn.classList.toggle('on', col.on);
      applyColVisibility();
    };
    cont.appendChild(btn);
  });
}

function applyColVisibility() {
  // Toggle table columns via nth-child CSS
  // Build a <style> tag
  let css = '';
  const allCols = IS_FA
    ? ['account_number','branch','month_date','broker','broker_commission','marketer','marketer_commission','ext_marketer1','ext_commission1','initial_deposit','account_kind','status','actions']
    : ['account_number','month_date','broker','broker_commission','marketer','marketer_commission','ext_marketer1','ext_commission1','initial_deposit','account_kind','status','actions'];

  allCols.forEach((id, i) => {
    const col = COLS.find(c => c.id === id);
    if (col && !col.on) {
      const nth = i + 2; // +1 for seq col, +1 for 1-based
      css += `#main-table th:nth-child(${nth}),#main-table td:nth-child(${nth}){display:none}`;
    }
  });
  let styleEl = document.getElementById('col-vis-style');
  if (!styleEl) { styleEl = document.createElement('style'); styleEl.id = 'col-vis-style'; document.head.appendChild(styleEl); }
  styleEl.textContent = css;
}

// ── Actions ────────────────────────────────────────────────────
function goEdit(id) { window.location.href = `{{ url('/cards/edit') }}/${id}`; }

async function deleteCard(id) {
  if (!confirm('حذف هذا الكارت نهائياً؟')) return;
  const r = await api('DELETE', `/cards/${id}`);
  if (r.success) { toast('تم الحذف', 'success'); loadCards(); }
  else toast(r.message || 'فشل الحذف', 'error');
}

function clearFilters() {
  ['f-search','f-month','f-broker','f-kind','f-status','f-cc','f-branch']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  loadCards();
}

function debounceSearch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadCards, 380);
}

// ── Export ─────────────────────────────────────────────────────
function exportExcel() {
  const params = buildExportParams();
  window.open(`{{ url('/') }}/api/cards/export/excel?${params}&_token=${getToken()}`, '_blank');
}
function exportPdf() {
  const params = buildExportParams();
  window.open(`{{ url('/') }}/api/cards/export/pdf?${params}&_token=${getToken()}`, '_blank');
}
function buildExportParams() {
  const p = new URLSearchParams();
  const branch = document.getElementById('f-branch')?.value;
  const month  = document.getElementById('f-month')?.value;
  const broker = document.getElementById('f-broker')?.value;
  if (branch) p.set('branch_id', branch);
  if (month)  { const d=new Date(month+'-01'); p.set('month', d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear()); }
  if (broker) p.set('broker_id', broker);
  return p.toString();
}
function getToken() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; }
</script>
@endpush
