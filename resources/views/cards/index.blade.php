@extends('layouts.app')
@section('title','كروت العمولات / Commission Cards')
@section('page-title','كروت العمولات / Commission Cards')

@push('styles')
<style>
/* ── Toolbar ── */
.tbar{display:flex;align-items:center;gap:8px;flex-wrap:wrap;
  padding:12px 16px;border-bottom:1px solid var(--brd1);background:var(--bg2)}
.tbar-search{flex:1;min-width:160px;max-width:260px}
.tbar-right{display:flex;align-items:center;gap:6px;margin-right:auto}

/* ── Dense Table ── */
.dense-table{width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed}
.dense-table thead th{
  background:var(--bg1);padding:7px 10px;font-weight:600;color:var(--mu);
  border-bottom:1px solid var(--brd1);border-left:1px solid var(--brd1);
  white-space:nowrap;user-select:none;cursor:pointer;text-align:right;
  position:sticky;top:0;z-index:2}
.dense-table thead th:hover{background:var(--bg2);color:var(--tx)}
.dense-table thead th .si{font-size:9px;opacity:.4;margin-right:3px}
.dense-table thead th.asc .si::after{content:'▲';opacity:1;color:var(--teal)}
.dense-table thead th.desc .si::after{content:'▼';opacity:1;color:var(--teal)}
.dense-table thead th.asc,.dense-table thead th.desc{color:var(--teal)}
.dense-table tbody td{
  padding:6px 10px;border-bottom:1px solid var(--brd1);
  border-left:1px solid rgba(255,255,255,.03);
  color:var(--tx);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;text-align:right}
.dense-table tbody tr:hover td{background:rgba(20,184,126,.04)}
.dense-table tbody tr.row-modified td{background:rgba(245,166,35,.05)}
.dense-table tbody tr.row-cc td{
  border-right:3px solid rgba(20,184,126,.5);background:rgba(20,184,126,.03)}

/* ── Col widths ── */
.col-seq{width:42px}.col-acn{width:115px}.col-branch{width:88px}
.col-month{width:92px}.col-broker{width:110px}.col-bc{width:68px}
.col-mkt{width:100px}.col-mc{width:68px}.col-ext1{width:96px}
.col-ec1{width:68px}.col-dep{width:88px}.col-kind{width:66px}
.col-status{width:70px}.col-act{width:72px}

/* ── Cell types ── */
.c-mono{font-family:var(--font-mono,monospace);color:var(--pri2);font-size:11px}
.c-green{color:var(--gr)}.c-muted{color:var(--mu)}.c-num{font-family:monospace;font-size:11px;color:var(--gr)}
.c-center{text-align:center!important}

/* ── Badges ── */
.badge-cc{display:inline-flex;align-items:center;gap:3px;font-size:9px;font-weight:700;
  padding:1px 6px;border-radius:10px;background:rgba(20,184,126,.12);
  color:var(--teal);border:1px solid rgba(20,184,126,.25)}
.badge-mod{font-size:9px;padding:1px 5px;border-radius:4px;
  background:rgba(245,166,35,.15);color:var(--or);border:1px solid rgba(245,166,35,.2)}
.badge-new{font-size:9px;padding:1px 5px;border-radius:4px;
  background:rgba(20,184,126,.1);color:var(--teal)}
.badge-sub{font-size:9px;padding:1px 5px;border-radius:4px;
  background:rgba(124,110,238,.12);color:var(--pu)}

/* ── Pagination ── */
.pager{display:flex;align-items:center;gap:6px;padding:10px 16px;
  border-top:1px solid var(--brd1);background:var(--bg2);flex-wrap:wrap}
.pager-info{font-size:11px;color:var(--mu);flex:1;min-width:120px}
.page-btn{padding:3px 9px;border-radius:5px;font-size:11px;cursor:pointer;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);
  font-family:'Tajawal',sans-serif}
.page-btn:hover{border-color:var(--teal);color:var(--teal)}
.page-btn.active{background:var(--teal);border-color:var(--teal);color:white}
.page-btn:disabled{opacity:.35;cursor:default}
.per-page-sel{font-size:11px;padding:3px 6px;border-radius:5px;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu)}

/* ── Action btns ── */
.act-btn{padding:2px 8px;border-radius:4px;font-size:10px;cursor:pointer;
  border:1px solid var(--brd1);background:none;color:var(--mu);font-family:'Tajawal',sans-serif}
.act-btn:hover{border-color:var(--teal);color:var(--teal)}
.act-btn.danger:hover{border-color:var(--re);color:var(--re)}

/* ── Export ── */
.exp-btn{padding:5px 11px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);
  font-family:'Tajawal',sans-serif;display:flex;align-items:center;gap:4px;transition:all .15s}
.exp-btn:hover{border-color:var(--teal);color:var(--teal)}

/* ── Table scroll ── */
.table-outer{overflow-x:auto;-webkit-overflow-scrolling:touch}

/* ── Column chooser ── */
.col-chooser{display:flex;gap:4px;flex-wrap:wrap;padding:8px 14px;
  background:var(--bg2);border-bottom:1px solid var(--brd1)}
.col-toggle{padding:2px 8px;border-radius:12px;font-size:10px;cursor:pointer;
  border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);
  font-family:'Tajawal',sans-serif;transition:all .15s}
.col-toggle.on{background:rgba(20,184,126,.1);border-color:var(--teal);color:var(--teal)}

/* ── Summary bar ── */
.summary-bar{display:flex;gap:16px;padding:8px 16px;background:var(--bg3);
  border-bottom:1px solid var(--brd1);flex-wrap:wrap}
.sb-item{font-size:11px;color:var(--mu);display:flex;align-items:center;gap:5px}
.sb-item strong{color:var(--tx);font-weight:600}

@media(max-width:768px){
  .tbar{gap:6px}
  .tbar-search{max-width:100%;width:100%}
  .tbar-right{width:100%;justify-content:space-between}
  .col-chooser,.summary-bar{display:none}
}
</style>
@endpush

@section('topbar-actions')
@if(auth()->user()?->isFinanceAdmin())
  <a href="{{ route('import.index') }}" class="btn btn-sm btn-ghost">↑ استيراد / Import</a>
@endif
@if(auth()->user()?->isFinanceAdmin() || auth()->user()?->hasPermission('create_card'))
  <a href="{{ route('cards.create') }}" class="btn btn-sm btn-primary">+ كارت جديد / New Card</a>
@endif
@endsection

@section('content')

{{-- ═══ FILTER TOOLBAR ═══════════════════════════════════════ --}}
<div class="tbar">
  <div class="tbar-search">
    <input type="text" id="f-search" class="form-control form-control-sm"
           placeholder="بحث برقم الحساب / Search account..." oninput="debounceSearch()" autocomplete="off">
  </div>

  @if(auth()->user()?->isFinanceAdmin())
  <select id="f-branch" class="form-control form-control-sm" style="min-width:110px" onchange="loadCards()">
    <option value="">كل الفروع / All Branches</option>
  </select>
  @endif

  <div id="mp-filter" style="display:flex;gap:6px;align-items:center"></div>
  <input type="hidden" id="f-month" value="">

  <select id="f-broker" class="form-control form-control-sm" style="min-width:110px" onchange="loadCards()">
    <option value="">كل البروكرين / All Brokers</option>
  </select>

  <select id="f-kind" class="form-control form-control-sm" onchange="loadCards()">
    <option value="">كل الأنواع / All Types</option>
    <option value="new">New — جديد</option>
    <option value="sub">Sub — فرعي</option>
  </select>

  <select id="f-status" class="form-control form-control-sm" onchange="loadCards()">
    <option value="">كل الحالات / All Statuses</option>
    <option value="active">Active</option>
    <option value="modified">Modified — معدّل</option>
    <option value="new_added">New Added</option>
  </select>

  <select id="f-cc" class="form-control form-control-sm" onchange="loadCards()">
    <option value="">عادي + CC</option>
    <option value="cc_only">CC فقط</option>
    <option value="normal_only">عادي فقط</option>
  </select>

  <div class="tbar-right">
    <button class="btn btn-ghost btn-sm" onclick="clearFilters()">مسح / Clear</button>
    <div style="display:flex;gap:4px">
      <button class="exp-btn" onclick="exportExcel()">⬇ Excel</button>
      <button class="exp-btn" onclick="exportPdf()">⬇ PDF</button>
    </div>
  </div>
</div>

{{-- ═══ COLUMN CHOOSER ════════════════════════════════════════ --}}
<div class="col-chooser" id="col-chooser">
  <span style="font-size:10px;color:var(--mu);margin-left:6px;align-self:center">الأعمدة / Columns:</span>
</div>

{{-- ═══ SUMMARY BAR ════════════════════════════════════════════ --}}
<div class="summary-bar">
  <div class="sb-item">إجمالي / Total: <strong id="sb-total">—</strong></div>
  <div class="sb-item">إيداعات / Deposits: <strong id="sb-dep">—</strong></div>
  <div class="sb-item">متوسط بروكر / Avg Broker: <strong id="sb-avg-bc">—</strong></div>
  <div class="sb-item">CC: <strong id="sb-cc">—</strong></div>
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
      <th class="c-center" style="cursor:default">م</th>
      <th onclick="sortBy('account_number')" data-col="account_number">رقم الحساب / Account No.<span class="si"></span></th>
      <th onclick="sortBy('branch')"         data-col="branch"        id="th-branch">الفرع / Branch<span class="si"></span></th>
      <th onclick="sortBy('month_date')"     data-col="month_date">الشهر / Month<span class="si"></span></th>
      <th onclick="sortBy('broker')"         data-col="broker">البروكر / Broker<span class="si"></span></th>
      <th onclick="sortBy('broker_commission')" data-col="broker_commission" class="c-center">ع. بروكر / Broker Comm.<span class="si"></span></th>
      <th onclick="sortBy('marketer')"       data-col="marketer">المسوّق / Marketer<span class="si"></span></th>
      <th onclick="sortBy('marketer_commission')" data-col="marketer_commission" class="c-center">ع. مسوّق / Mkt. Comm.<span class="si"></span></th>
      <th onclick="sortBy('ext_marketer1')"  data-col="ext_marketer1">مسوّق خارجي / Ext. Mktr<span class="si"></span></th>
      <th onclick="sortBy('ext_commission1')" data-col="ext_commission1" class="c-center">ع. خارجي / Ext. Comm.<span class="si"></span></th>
      <th onclick="sortBy('initial_deposit')" data-col="initial_deposit" class="c-center">إيداع أولي / Init. Deposit<span class="si"></span></th>
      <th class="c-center" data-col="account_kind">نوع / Type</th>
      <th class="c-center" data-col="status">الحالة / Status</th>
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
// ── State ────────────────────────────────────────────────────
let allCards     = [];
let filteredCards= [];
let currentPage  = 1;
let perPage      = 50;
let sortCol      = 'month_date';
let sortDir      = 'desc';
let debounceTimer;
const IS_FA = {{ auth()->user()?->isFinanceAdmin() ? 'true' : 'false' }};

const COLS = [
  {id:'account_number',    on:true},
  {id:'branch',            on:true, faOnly:true},
  {id:'month_date',        on:true},
  {id:'broker',            on:true},
  {id:'broker_commission', on:true},
  {id:'marketer',          on:true},
  {id:'marketer_commission',on:true},
  {id:'ext_marketer1',     on:true},
  {id:'ext_commission1',   on:false},
  {id:'initial_deposit',   on:true},
  {id:'account_kind',      on:true},
  {id:'status',            on:true},
  {id:'actions',           on:true},
];

const COL_LABELS = {
  account_number:'رقم الحساب',branch:'الفرع',month_date:'الشهر',
  broker:'البروكر',broker_commission:'ع. بروكر',
  marketer:'المسوّق',marketer_commission:'ع. مسوّق',
  ext_marketer1:'مسوّق خارجي',ext_commission1:'ع. خارجي',
  initial_deposit:'إيداع أولي',account_kind:'نوع',status:'الحالة',actions:'إجراءات'
};

// ── Init ────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', async () => {
  // Month picker (two dropdowns)
  mountMonthPicker('mp-filter', 'f-month', () => loadCards());

  await Promise.all([loadBranches(), loadBrokers()]);
  await loadCards();
  buildColChooser();
});

// ── Load cards ───────────────────────────────────────────────
async function loadCards() {
  const tbody = document.getElementById('cards-tbody');
  tbody.innerHTML = `<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>`;

  const params = new URLSearchParams({per_page: 500});
  const month  = document.getElementById('f-month')?.value;
  const broker = document.getElementById('f-broker')?.value;
  const branch = document.getElementById('f-branch')?.value;
  const kind   = document.getElementById('f-kind')?.value;
  const status = document.getElementById('f-status')?.value;
  const search = document.getElementById('f-search')?.value?.trim();
  const cc     = document.getElementById('f-cc')?.value;

  if (month)  {
    const parts = month.split(' ');
    const EN = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
    if (parts.length === 2) params.set('month', month);
  }
  if (broker) params.set('broker_id', broker);
  if (branch) params.set('branch_id', branch);
  if (kind)   params.set('account_kind', kind);
  if (status) params.set('status', status);
  if (search) params.set('search', search);

  const r = await api('GET', '/cards?' + params);
  if (!r.success) {
    tbody.innerHTML = `<tr><td colspan="14" style="text-align:center;padding:30px;color:var(--re)">فشل التحميل / Load failed</td></tr>`;
    return;
  }

  allCards = r.data?.data || r.data || [];

  // Client-side CC filter
  if      (cc === 'cc_only')     filteredCards = allCards.filter(c => c.cc_branch_id);
  else if (cc === 'normal_only') filteredCards = allCards.filter(c => !c.cc_branch_id);
  else                           filteredCards = [...allCards];

  currentPage = 1;
  sortAndRender();
  updateSummary();
}

// ── Sort ────────────────────────────────────────────────────
function sortBy(col) {
  if (sortCol === col) sortDir = sortDir === 'asc' ? 'desc' : 'asc';
  else { sortCol = col; sortDir = 'asc'; }
  document.querySelectorAll('.dense-table thead th').forEach(th => {
    th.classList.remove('asc','desc');
    if (th.dataset.col === col) th.classList.add(sortDir === 'asc' ? 'asc' : 'desc');
  });
  sortAndRender();
}

function sortAndRender() {
  const sorted = [...filteredCards].sort((a, b) => {
    let va = getSortVal(a, sortCol), vb = getSortVal(b, sortCol);
    if (typeof va === 'string') { va = va.toLowerCase(); vb = vb.toLowerCase(); }
    return sortDir === 'asc' ? (va > vb ? 1 : -1) : (va < vb ? 1 : -1);
  });
  renderPage(sorted);
}

function getSortVal(c, col) {
  switch(col) {
    case 'account_number':       return c.account_number || '';
    case 'branch':               return c.branch?.name_ar || '';
    case 'month_date':           return c.month_date || '';
    case 'broker':               return c.broker?.name || '';
    case 'broker_commission':    return parseFloat(c.broker_commission) || 0;
    case 'marketer':             return c.marketer?.name || '';
    case 'marketer_commission':  return parseFloat(c.marketer_commission) || 0;
    case 'ext_marketer1':        return c.ext_marketer1?.name || '';
    case 'ext_commission1':      return parseFloat(c.ext_commission1) || 0;
    case 'initial_deposit':      return parseFloat(c.initial_deposit) || 0;
    default: return '';
  }
}

// ── Arabic month name ────────────────────────────────────────
function arMonth(monthStr) {
  const AR = {Jan:'يناير',Feb:'فبراير',Mar:'مارس',Apr:'أبريل',May:'مايو',Jun:'يونيو',
               Jul:'يوليو',Aug:'أغسطس',Sep:'سبتمبر',Oct:'أكتوبر',Nov:'نوفمبر',Dec:'ديسمبر'};
  const [m, y] = (monthStr || '').split(' ');
  return (AR[m] || m) + ' ' + (y || '');
}

// ── Render page ──────────────────────────────────────────────
function renderPage(sorted) {
  const total = sorted.length;
  const pages = Math.max(1, Math.ceil(total / perPage));
  if (currentPage > pages) currentPage = pages;
  const start = (currentPage - 1) * perPage;
  const slice = sorted.slice(start, start + perPage);
  const tbody = document.getElementById('cards-tbody');

  if (!slice.length) {
    tbody.innerHTML = `<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">
      <div style="font-size:24px;opacity:.25;margin-bottom:6px">📭</div>لا توجد نتائج / No results
    </td></tr>`;
    document.getElementById('pager').style.display = 'none';
    return;
  }

  tbody.innerHTML = slice.map((c, i) => {
    const seq    = start + i + 1;
    const isCC   = !!c.cc_branch_id;
    const isMod  = c.status === 'modified';
    const rowCls = `${isMod ? 'row-modified' : ''} ${isCC ? 'row-cc' : ''}`;

    const ccBadge = isCC
      ? `<span class="badge-cc" title="${c.cc_branch?.name_ar||'CC'}">CC</span> `
      : '';

    const broker   = c.broker?.name   || `<span class="c-muted">—</span>`;
    const marketer = c.marketer?.name || `<span class="c-muted">—</span>`;
    const ext1     = c.ext_marketer1?.name || `<span class="c-muted">—</span>`;

    const bc  = +c.broker_commission   > 0 ? `<span class="c-num">$${(+c.broker_commission).toFixed(2)}</span>`   : '<span class="c-muted">—</span>';
    const mc  = +c.marketer_commission > 0 ? `<span class="c-num">$${(+c.marketer_commission).toFixed(2)}</span>` : '<span class="c-muted">—</span>';
    const ec1 = +c.ext_commission1     > 0 ? `<span class="c-num">$${(+c.ext_commission1).toFixed(2)}</span>`     : '<span class="c-muted">—</span>';
    const dep = +c.initial_deposit     > 0
      ? `<span class="c-green">$${(+c.initial_deposit).toLocaleString()}</span>`
      : '<span class="c-muted">—</span>';

    const kind = c.account_kind === 'sub'
      ? `<span class="badge-sub">Sub</span>`
      : `<span class="badge-new">New</span>`;

    const statusBadge = isMod ? `<span class="badge-mod">معدّل</span>` : '';

    const actEdit = `<button class="act-btn" onclick="goEdit(${c.id})">تعديل</button>`;
    const actDel  = IS_FA
      ? ` <button class="act-btn danger" onclick="deleteCard(${c.id})">حذف</button>`
      : '';

    const branchCell = IS_FA
      ? `<td class="c-muted" style="font-size:11px">${c.branch?.name_ar||'—'}</td>`
      : '';

    return `<tr class="${rowCls}" data-id="${c.id}">
      <td class="c-center c-muted" style="font-size:10px">${seq}</td>
      <td class="c-mono">${ccBadge}#${c.account_number}</td>
      ${branchCell}
      <td style="font-size:11px;white-space:nowrap">${arMonth(c.month)}</td>
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

  // Update page title count
  const countEl = document.getElementById('cards-count');
  if (countEl) countEl.textContent = `(${total.toLocaleString()})`;
}

// ── Pagination buttons ───────────────────────────────────────
function renderPageButtons(total) {
  const cont = document.getElementById('page-btns');
  if (total <= 1) { cont.innerHTML = ''; return; }
  let btns = '';
  btns += `<button class="page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>‹</button>`;
  const pages = new Set([1]);
  for (let p = Math.max(2, currentPage-2); p <= Math.min(total-1, currentPage+2); p++) pages.add(p);
  pages.add(total);
  let prev = 0;
  for (const p of [...pages].sort((a,b)=>a-b)) {
    if (p - prev > 1) btns += `<span class="page-btn" style="border:none;cursor:default;color:var(--mu)">…</span>`;
    btns += `<button class="page-btn ${p===currentPage?'active':''}" onclick="goPage(${p})">${p}</button>`;
    prev = p;
  }
  btns += `<button class="page-btn" onclick="goPage(${currentPage+1})" ${currentPage===total?'disabled':''}>›</button>`;
  cont.innerHTML = btns;
}

function goPage(p) {
  const sorted = [...filteredCards].sort((a,b) => {
    let va = getSortVal(a,sortCol), vb = getSortVal(b,sortCol);
    if (typeof va==='string') { va=va.toLowerCase(); vb=vb.toLowerCase(); }
    return sortDir==='asc' ? (va>vb?1:-1) : (va<vb?1:-1);
  });
  currentPage = Math.max(1, Math.min(p, Math.max(1,Math.ceil(sorted.length/perPage))));
  renderPage(sorted);
  document.getElementById('table-outer')?.scrollIntoView({behavior:'smooth', block:'start'});
}

function changePerPage() {
  perPage = parseInt(document.getElementById('per-page').value);
  currentPage = 1;
  sortAndRender();
}

// ── Summary ─────────────────────────────────────────────────
function updateSummary() {
  const cards   = filteredCards;
  const dep     = cards.reduce((s,c) => s + (+c.initial_deposit||0), 0);
  const avgBc   = cards.length ? cards.reduce((s,c)=>s+(+c.broker_commission||0),0)/cards.length : 0;
  const ccCount = cards.filter(c=>c.cc_branch_id).length;
  document.getElementById('sb-total').textContent  = cards.length.toLocaleString();
  document.getElementById('sb-dep').textContent    = '$' + dep.toLocaleString();
  document.getElementById('sb-avg-bc').textContent = '$' + avgBc.toFixed(2);
  document.getElementById('sb-cc').textContent     = ccCount.toLocaleString();
}

// ── Load filter options ──────────────────────────────────────
async function loadBranches() {
  if (!IS_FA) return;
  const r = await api('GET', '/branches');
  if (!r.success) return;
  const sel = document.getElementById('f-branch');
  if (!sel) return;
  r.data.forEach(b => {
    const o = document.createElement('option');
    o.value = b.id;
    o.textContent = b.name_ar + ' / ' + (b.name_en || '');
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
    const grp = document.createElement('optgroup');
    grp.label = branch;
    emps.forEach(e => {
      const o = document.createElement('option');
      o.value = e.id;
      o.textContent = e.name;
      grp.appendChild(o);
    });
    sel.appendChild(grp);
  });
}

// ── Column chooser ───────────────────────────────────────────
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
  const allCols = IS_FA
    ? ['account_number','branch','month_date','broker','broker_commission','marketer',
       'marketer_commission','ext_marketer1','ext_commission1','initial_deposit','account_kind','status','actions']
    : ['account_number','month_date','broker','broker_commission','marketer',
       'marketer_commission','ext_marketer1','ext_commission1','initial_deposit','account_kind','status','actions'];
  let css = '';
  allCols.forEach((id, i) => {
    const col = COLS.find(c => c.id === id);
    if (col && !col.on) {
      const nth = i + 2;
      css += `#main-table th:nth-child(${nth}),#main-table td:nth-child(${nth}){display:none}`;
    }
  });
  let el = document.getElementById('col-vis-style');
  if (!el) { el = document.createElement('style'); el.id = 'col-vis-style'; document.head.appendChild(el); }
  el.textContent = css;
}

// ── Actions ──────────────────────────────────────────────────
function goEdit(id) { window.location.href = `{{ url('/cards/edit') }}/${id}`; }

async function deleteCard(id) {
  if (!confirm('حذف هذا الكارت نهائياً؟ / Delete this card permanently?')) return;
  const r = await api('DELETE', `/cards/${id}`);
  if (r.success) { toast('تم الحذف / Deleted', 'success'); loadCards(); }
  else toast(r.message || 'فشل الحذف / Delete failed', 'error');
}

function clearFilters() {
  ['f-search','f-month','f-broker','f-kind','f-status','f-cc','f-branch']
    .forEach(id => { const el = document.getElementById(id); if (el) el.value = ''; });
  // Re-mount month picker to reset
  const mpDiv = document.getElementById('mp-filter');
  if (mpDiv) { mpDiv.innerHTML = ''; mountMonthPicker('mp-filter', 'f-month', () => loadCards()); }
  loadCards();
}

function debounceSearch() {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(loadCards, 380);
}

// ── Export ───────────────────────────────────────────────────
function exportExcel() {
  window.open(`{{ url('/') }}/api/cards/export/excel?${buildExportParams()}`, '_blank');
}
function exportPdf() {
  window.open(`{{ url('/') }}/api/cards/export/pdf?${buildExportParams()}`, '_blank');
}
function buildExportParams() {
  const p = new URLSearchParams();
  const branch = document.getElementById('f-branch')?.value;
  const month  = document.getElementById('f-month')?.value;
  const broker = document.getElementById('f-broker')?.value;
  if (branch) p.set('branch_id', branch);
  if (month)  p.set('month', month);
  if (broker) p.set('broker_id', broker);
  return p.toString();
}
</script>
@endpush
