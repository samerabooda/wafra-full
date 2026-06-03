@extends('layouts.app')
@section('title','Report Table')
@section('page-title','Report Table')

@section('content')
<style>
.rt-filter-bar{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;
  padding:14px 18px;margin-bottom:14px}
.rt-filter-row{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
.rt-fg{display:flex;flex-direction:column;gap:4px}
.rt-fl{font-size:9px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.4px}
.rt-nav-bar{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;align-items:center}
.rt-nav-btn{display:flex;align-items:center;gap:7px;padding:10px 16px;border-radius:11px;
  font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;
  border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);transition:all .18s;white-space:nowrap}
.rt-nav-btn:hover{border-color:var(--pri);background:rgba(26,173,186,.08);color:var(--pri2)}
.rt-nav-btn.active{background:rgba(26,173,186,.15);border-color:rgba(26,173,186,.4);color:var(--pri2)}
.rt-kpi-strip{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.rt-kpi{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:11px;
  padding:10px 14px;flex:1;min-width:130px;text-align:center}
.rt-kpi-val{font-size:1.2rem;font-weight:900;font-family:'JetBrains Mono',monospace}
.rt-kpi-lbl{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-top:3px}
.rt-pagination{display:flex;gap:6px;align-items:center;flex-wrap:wrap}
.rt-page-btn{padding:6px 12px;border-radius:8px;border:1px solid var(--brd1);
  background:var(--bg2);color:var(--mu);cursor:pointer;font-size:12px;font-weight:700;transition:all .15s}
.rt-page-btn:hover{border-color:var(--pri);color:var(--pri2)}
.rt-page-btn.active{background:rgba(26,173,186,.15);border-color:var(--pri2);color:var(--pri2)}
.rt-page-btn:disabled{opacity:.4;cursor:default}
</style>

{{-- ── Nav bar ── --}}
<div class="rt-nav-bar">
  <a href="{{ route('reports.index') }}" class="rt-nav-btn" id="rtn-dash">
    📊 <span id="rtn-dash-lbl">لوحة التقارير</span>
  </a>
  <a href="{{ route('reports.table') }}" class="rt-nav-btn active" id="rtn-table">
    📋 <span id="rtn-table-lbl">جدول البيانات</span>
  </a>
  <a href="{{ route('reports.dynamic') }}" class="rt-nav-btn" id="rtn-dynamic">
    🔧 <span id="rtn-dynamic-lbl">تقرير ديناميكي</span>
  </a>
  <a href="{{ route('reports.branch-monthly') }}" class="rt-nav-btn" id="rtn-branch">
    🏢 <span id="rtn-branch-lbl">تقرير الفروع</span>
  </a>
  <div style="flex:1"></div>
  <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportExcel()" id="rt-excel-btn">📗 Excel</button>
  <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)" onclick="exportPdf()" id="rt-pdf-btn">📄 PDF</button>
  <button class="btn btn-sm" style="background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.25);color:var(--pri2)" onclick="window.print()" id="rt-print-btn">🖨️ <span id="rtn-print">طباعة</span></button>
</div>

{{-- ── Advanced filter bar ── --}}
<div class="rt-filter-bar">
  <div class="rt-filter-row">
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-from">من شهر</span>
      <select id="rt-from" class="form-control" style="min-width:120px" onchange="scheduleLoad()">
        <option value="" id="rt-opt-allperiod">كل الفترات</option>
      </select>
    </div>
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-to">إلى شهر</span>
      <select id="rt-to" class="form-control" style="min-width:120px" onchange="scheduleLoad()">
        <option value="">—</option>
      </select>
    </div>
    @if(auth()->user()?->isFinanceAdmin())
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-branch">الفرع</span>
      <select id="rt-branch" class="form-control" style="min-width:130px" onchange="scheduleLoad()">
        <option value="" id="rt-opt-allbranch">كل الفروع</option>
      </select>
    </div>
    @endif
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-broker">البروكر</span>
      <select id="rt-broker" class="form-control" style="min-width:140px" onchange="scheduleLoad()">
        <option value="" id="rt-opt-allbroker">كل البروكرات</option>
      </select>
    </div>
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-status">الحالة</span>
      <select id="rt-status" class="form-control" onchange="scheduleLoad()">
        <option value="" id="rt-opt-all">الكل</option>
        <option value="active"    id="rt-opt-active">عادي</option>
        <option value="modified"  id="rt-opt-mod">معدّلة</option>
        <option value="new_added" id="rt-opt-new">مضافة جديدة</option>
      </select>
    </div>
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-kind">نوع الحساب</span>
      <select id="rt-kind" class="form-control" onchange="scheduleLoad()">
        <option value="" id="rt-opt-allkind">الكل</option>
        <option value="new" id="rt-opt-new-kind">🟢 NEW</option>
        <option value="sub" id="rt-opt-sub-kind">🔵 SUB</option>
      </select>
    </div>
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-source">المصدر</span>
      <select id="rt-source" class="form-control" onchange="scheduleLoad()">
        <option value="" id="rt-opt-allsrc">الكل</option>
        <option value="regular" id="rt-opt-reg">عادي</option>
        <option value="cc"      id="rt-opt-cc">📞 CC</option>
      </select>
    </div>
    <div class="rt-fg">
      <span class="rt-fl" id="rt-fl-search">بحث</span>
      <input type="text" id="rt-search" class="form-control" style="min-width:180px"
        placeholder="رقم حساب / بروكر..." oninput="debounceSearch()">
    </div>
    <button class="btn btn-ghost btn-sm" onclick="clearAll()" id="rt-clear-btn" style="align-self:flex-end">✕ <span id="rt-clear-lbl">مسح</span></button>
  </div>
  <div style="margin-top:10px;display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <span id="rt-per-page-lbl" style="font-size:11px;color:var(--mu)">عرض:</span>
    <select id="rt-per-page" class="form-control" style="width:80px;font-size:12px" onchange="currentPage=1;renderTable()">
      <option value="50">50</option>
      <option value="100">100</option>
      <option value="200" selected>200</option>
      <option value="500">500</option>
    </select>
    <span id="rt-count-lbl" style="font-size:12px;color:var(--mu);margin-right:auto"></span>
    <div id="rt-loading" style="display:none"><span style="font-size:12px;color:var(--mu)">⏳ <span id="rt-loading-lbl">جاري التحميل...</span></span></div>
  </div>
</div>

{{-- ── KPI Summary Strip ── --}}
<div class="rt-kpi-strip" id="rt-kpi-strip">
  <div class="rt-kpi"><div class="rt-kpi-val" style="color:var(--pri2)" id="rt-kpi-total">—</div><div class="rt-kpi-lbl" id="rt-kpi-total-lbl">إجمالي السجلات</div></div>
  <div class="rt-kpi"><div class="rt-kpi-val" style="color:var(--gr)" id="rt-kpi-dep">—</div><div class="rt-kpi-lbl" id="rt-kpi-dep-lbl">إيداع أولي</div></div>
  <div class="rt-kpi"><div class="rt-kpi-val" style="color:#3a9db5" id="rt-kpi-mon">—</div><div class="rt-kpi-lbl" id="rt-kpi-mon-lbl">إيداع شهري</div></div>
  <div class="rt-kpi"><div class="rt-kpi-val" style="color:var(--or)" id="rt-kpi-mod">—</div><div class="rt-kpi-lbl" id="rt-kpi-mod-lbl">معدّلة</div></div>
  <div class="rt-kpi"><div class="rt-kpi-val" style="color:var(--gr)" id="rt-kpi-bcomm">—</div><div class="rt-kpi-lbl" id="rt-kpi-bcomm-lbl">متوسط ع.بروكر</div></div>
</div>

{{-- ── Data table ── --}}
<div class="panel" style="padding:0">
  <div class="table-scroll" style="max-height:60vh">
    <table class="data-table" id="rt-table">
      <thead>
        <tr>
          <th style="width:36px">#</th>
          <th id="rth-ac">رقم الحساب</th>
          <th id="rth-month">الشهر</th>
          <th id="rth-branch">الفرع</th>
          <th id="rth-broker">البروكر</th>
          <th id="rth-bcomm">ع.بروكر</th>
          <th id="rth-mktr">مسوّق</th>
          <th id="rth-mcomm">ع.مسوّق</th>
          <th id="rth-totcomm">إجمالي ع.</th>
          <th id="rth-dep">إيداع أولي</th>
          <th id="rth-mon">إيداع شهري</th>
          <th id="rth-kind">نوع</th>
          <th id="rth-status">الحالة</th>
          <th id="rth-src">المصدر</th>
          <th id="rth-act">إجراء</th>
        </tr>
      </thead>
      <tbody id="rt-tbody">
        <tr><td colspan="15" style="text-align:center;padding:60px;color:var(--mu)">
          <div style="font-size:42px;margin-bottom:12px">📋</div>
          <div style="font-size:14px;font-weight:700" id="rt-empty-title">جاري تحميل البيانات...</div>
        </td></tr>
      </tbody>
      <tfoot id="rt-tfoot" style="display:none">
        <tr style="background:var(--bg2);font-weight:800;font-size:11px">
          <td colspan="4" style="padding:8px 12px;color:var(--mu)" id="rt-tf-label">المجاميع</td>
          <td></td><td id="rt-tf-bcomm" class="mono" style="color:var(--or)"></td>
          <td></td><td id="rt-tf-mcomm" class="mono" style="color:var(--or)"></td>
          <td id="rt-tf-tot" class="mono" style="color:var(--or)"></td>
          <td id="rt-tf-dep" class="mono" style="color:var(--gr)"></td>
          <td id="rt-tf-mon" class="mono" style="color:var(--pri2)"></td>
          <td colspan="4"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  {{-- Pagination ── --}}
  <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
    <span style="font-size:11px;color:var(--mu)" id="rt-page-info"></span>
    <div class="rt-pagination" id="rt-pagination"></div>
  </div>
</div>

@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════
   WAFRA GULF — Reports Table v2
   Loads all data once, paginates client-side
   ══════════════════════════════════════════════════════════════ */

/* ── i18n ── */
const RTB = {
  ar:{
    tbTitle:'جدول التقارير',
    dash:'لوحة التقارير',table:'جدول البيانات',dynamic:'تقرير ديناميكي',branch:'تقرير الفروع',
    excel:'Excel',pdf:'PDF',print:'طباعة',
    flFrom:'من شهر',flTo:'إلى شهر',flBranch:'الفرع',flBroker:'البروكر',
    flStatus:'الحالة',flKind:'نوع الحساب',flSource:'المصدر',flSearch:'بحث',
    allPeriod:'كل الفترات',allBranch:'كل الفروع',allBroker:'كل البروكرات',
    all:'الكل',statusMod:'معدّلة',statusNew:'مضافة جديدة',statusActive:'عادي',
    allSrc:'الكل',reg:'عادي',
    clear:'مسح',perPage:'عرض:',loading:'جاري التحميل...',
    kpiTotal:'إجمالي السجلات',kpiDep:'إيداع أولي',kpiMon:'إيداع شهري',kpiMod:'معدّلة',kpiBComm:'متوسط ع.بروكر',
    thAc:'رقم الحساب',thMonth:'الشهر',thBranch:'الفرع',thBroker:'البروكر',
    thBComm:'ع.بروكر',thMktr:'مسوّق',thMComm:'ع.مسوّق',thTotComm:'إجمالي ع.',
    thDep:'إيداع أولي',thMon:'إيداع شهري',thKind:'نوع',thStatus:'الحالة',thSrc:'المصدر',thAct:'إجراء',
    tfLabel:'المجاميع',
    empty:'لا توجد نتائج',loading2:'جاري تحميل البيانات...',
    prev:'السابق',next:'التالي',pageOf:'صفحة {p} من {t}',showing:'{n} من {total} سجل',
    modified:'✏️ معدّل',newAdded:'🆕 جديد',active:'✅ عادي',inactive:'⛔ متوقف',
    regular:'عادي',cc:'📞 CC',
    editBtn:'✏️ تعديل',
    noExport:'لا توجد بيانات',
    excelDone:'تم تحميل Excel ✅',pdfDone:'تم تحميل PDF ✅',
  },
  en:{
    tbTitle:'Reports Table',
    dash:'Reports Dashboard',table:'Data Table',dynamic:'Dynamic Report',branch:'Branch Report',
    excel:'Excel',pdf:'PDF',print:'Print',
    flFrom:'From Month',flTo:'To Month',flBranch:'Branch',flBroker:'Broker',
    flStatus:'Status',flKind:'Account Kind',flSource:'Source',flSearch:'Search',
    allPeriod:'All Periods',allBranch:'All Branches',allBroker:'All Brokers',
    all:'All',statusMod:'Modified',statusNew:'New Added',statusActive:'Active',
    allSrc:'All',reg:'Regular',
    clear:'Clear',perPage:'Show:',loading:'Loading...',
    kpiTotal:'Total Records',kpiDep:'Initial Deposit',kpiMon:'Monthly Deposit',kpiMod:'Modified',kpiBComm:'Avg Broker Comm',
    thAc:'Account #',thMonth:'Month',thBranch:'Branch',thBroker:'Broker',
    thBComm:'B.Comm',thMktr:'Marketer',thMComm:'M.Comm',thTotComm:'Total Comm',
    thDep:'Initial Dep.',thMon:'Monthly Dep.',thKind:'Kind',thStatus:'Status',thSrc:'Source',thAct:'Action',
    tfLabel:'Totals',
    empty:'No results found',loading2:'Loading data...',
    prev:'Prev',next:'Next',pageOf:'Page {p} of {t}',showing:'{n} of {total} records',
    modified:'✏️ Modified',newAdded:'🆕 New Added',active:'✅ Active',inactive:'⛔ Inactive',
    regular:'Regular',cc:'📞 CC',
    editBtn:'✏️ Edit',
    noExport:'No data to export',
    excelDone:'Excel downloaded ✅',pdfDone:'PDF downloaded ✅',
  }
};
function rtL()   { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function rtb(k)  { const l=rtL(); return RTB[l]?.[k]??RTB.ar[k]??k; }
function _t(id,v){ const e=document.getElementById(id); if(e&&v!==undefined) e.textContent=v; }

function rtApplyLang() {
  const t = _t;
  t('rtn-dash-lbl',rtb('dash')); t('rtn-table-lbl',rtb('table'));
  t('rtn-dynamic-lbl',rtb('dynamic')); t('rtn-branch-lbl',rtb('branch'));
  t('rt-excel-btn',`📗 ${rtb('excel')}`); t('rt-pdf-btn',`📄 ${rtb('pdf')}`);
  t('rtn-print',rtb('print'));
  t('rt-fl-from',rtb('flFrom')); t('rt-fl-to',rtb('flTo'));
  t('rt-fl-branch',rtb('flBranch')); t('rt-fl-broker',rtb('flBroker'));
  t('rt-fl-status',rtb('flStatus')); t('rt-fl-kind',rtb('flKind'));
  t('rt-fl-source',rtb('flSource')); t('rt-fl-search',rtb('flSearch'));
  t('rt-opt-allperiod',rtb('allPeriod')); t('rt-opt-allbranch',rtb('allBranch'));
  t('rt-opt-allbroker',rtb('allBroker'));
  t('rt-opt-all',rtb('all')); t('rt-opt-active',rtb('statusActive'));
  t('rt-opt-mod',rtb('statusMod')); t('rt-opt-new',rtb('statusNew'));
  t('rt-opt-allkind',rtb('all')); t('rt-opt-allsrc',rtb('allSrc')); t('rt-opt-reg',rtb('reg'));
  t('rt-clear-btn',`✕ ${rtb('clear')}`); t('rt-per-page-lbl',rtb('perPage'));
  t('rt-loading-lbl',rtb('loading'));
  t('rt-kpi-total-lbl',rtb('kpiTotal')); t('rt-kpi-dep-lbl',rtb('kpiDep'));
  t('rt-kpi-mon-lbl',rtb('kpiMon')); t('rt-kpi-mod-lbl',rtb('kpiMod'));
  t('rt-kpi-bcomm-lbl',rtb('kpiBComm'));
  t('rth-ac',rtb('thAc')); t('rth-month',rtb('thMonth')); t('rth-branch',rtb('thBranch'));
  t('rth-broker',rtb('thBroker')); t('rth-bcomm',rtb('thBComm')); t('rth-mktr',rtb('thMktr'));
  t('rth-mcomm',rtb('thMComm')); t('rth-totcomm',rtb('thTotComm'));
  t('rth-dep',rtb('thDep')); t('rth-mon',rtb('thMon')); t('rth-kind',rtb('thKind'));
  t('rth-status',rtb('thStatus')); t('rth-src',rtb('thSrc')); t('rth-act',rtb('thAct'));
  t('rt-tf-label',rtb('tfLabel'));
  const inp = document.getElementById('rt-search');
  if(inp) inp.placeholder = rtL()==='en' ? 'Account # / broker...' : 'رقم حساب / بروكر...';
  const tb = document.querySelector('.tb-title'); if(tb) tb.textContent = rtb('tbTitle');
  if(allData.length) renderTable();
}
const _rtOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if(_rtOrigApplyLang) _rtOrigApplyLang(lang);
  try { rtApplyLang(); } catch(e) { console.warn('rt i18n:', e); }
};

/* ── State ── */
let allData = [];
let filteredData = [];
let currentPage = 1;
let _loadTimer = null;
let _searchTimer = null;

/* ── Filters ── */
function scheduleLoad() { clearTimeout(_loadTimer); _loadTimer = setTimeout(loadTable, 400); }
function debounceSearch() { clearTimeout(_searchTimer); _searchTimer = setTimeout(applySearch, 300); }

function applySearch() {
  const q = (document.getElementById('rt-search')?.value || '').toLowerCase().trim();
  const source = document.getElementById('rt-source')?.value;
  filteredData = allData.filter(c => {
    if(source === 'cc'      && !c.cc_status)  return false;
    if(source === 'regular' && !!c.cc_status) return false;
    if(!q) return true;
    return String(c.account_number).includes(q)
      || (c.broker?.name||'').toLowerCase().includes(q)
      || (c.marketer?.name||'').toLowerCase().includes(q)
      || (c.month||'').toLowerCase().includes(q);
  });
  currentPage = 1;
  renderTable();
}

function clearAll() {
  ['rt-from','rt-to','rt-branch','rt-broker','rt-status','rt-kind','rt-source'].forEach(id=>{
    const el=document.getElementById(id); if(el) el.value='';
  });
  const s=document.getElementById('rt-search'); if(s) s.value='';
  loadTable();
}

/* ── Load data from API ── */
async function loadTable() {
  const params = new URLSearchParams();
  const from   = document.getElementById('rt-from')?.value;
  const to     = document.getElementById('rt-to')?.value;
  const branch = document.getElementById('rt-branch')?.value;
  const broker = document.getElementById('rt-broker')?.value;
  const status = document.getElementById('rt-status')?.value;
  const kind   = document.getElementById('rt-kind')?.value;
  if(from)   params.set('month_from', from);
  if(to)     params.set('month_to', to);
  if(branch) params.set('branch_id', branch);
  if(broker) params.set('broker_id', broker);
  if(status) params.set('status', status);
  if(kind)   params.set('kind', kind);
  params.set('per_page', 5000);

  document.getElementById('rt-loading').style.display = '';
  document.getElementById('rt-tbody').innerHTML = `<tr><td colspan="15" style="text-align:center;padding:60px;color:var(--mu)">
    <div style="font-size:36px;margin-bottom:10px">⏳</div>
    <div>${rtb('loading')}</div></td></tr>`;

  const r = await api('GET', '/cards/report?' + params);
  document.getElementById('rt-loading').style.display = 'none';

  if(!r.success) { toast(rtb('noExport'),'error'); return; }

  allData = r.data || [];
  applySearch();
  updateKpis(r.summary || {}, r.unique_accounts ?? r.count ?? 0);
}

/* ── KPI ── */
function updateKpis(s, uniqueAcc) {
  const fmtK = n => { if(!n||isNaN(n)) return '—'; if(n>=1e6) return '$'+(n/1e6).toFixed(1)+'M'; if(n>=1000) return '$'+(n/1000).toFixed(0)+'K'; return '$'+Math.round(n); };
  _t('rt-kpi-total', (uniqueAcc||0).toLocaleString('en'));
  _t('rt-kpi-dep',   fmtK(s.total_initial_deposit));
  _t('rt-kpi-mon',   fmtK(s.total_monthly_deposit));
  _t('rt-kpi-mod',   (s.modified_count||0).toLocaleString('en'));
  const avgC = allData.length ? (allData.reduce((a,c)=>a+parseFloat(c.broker_commission||0),0)/allData.length).toFixed(1) : '—';
  _t('rt-kpi-bcomm', avgC === '—' ? '—' : '$'+avgC+'');
}

/* ── Render table (client-side pagination) ── */
function commColor(v) { return parseFloat(v)>5?'comm-high':parseFloat(v)>2?'comm-mid':'comm-low'; }
function fmtK(n)      { const v=parseFloat(n)||0; return v>=1e6?'$'+(v/1e6).toFixed(1)+'M':v>=1000?'$'+(v/1000).toFixed(0)+'K':'$'+v.toFixed(0); }

function renderTable() {
  const perPage = parseInt(document.getElementById('rt-per-page')?.value)||200;
  const total   = filteredData.length;
  const pages   = Math.max(1, Math.ceil(total / perPage));
  if(currentPage > pages) currentPage = pages;
  const start   = (currentPage - 1) * perPage;
  const slice   = filteredData.slice(start, start + perPage);
  const isEn    = rtL() === 'en';

  /* Count label */
  const countLbl = rtb('showing')
    .replace('{n}', total.toLocaleString('en'))
    .replace('{total}', allData.length.toLocaleString('en'));
  _t('rt-count-lbl', countLbl);

  /* Status / source helpers */
  const statusChip = s => ({
    modified:  `<span class="badge badge-orange">${rtb('modified')}</span>`,
    new_added: `<span class="badge badge-green">${rtb('newAdded')}</span>`,
    active:    `<span class="badge badge-blue">${rtb('active')}</span>`,
    inactive:  `<span class="badge badge-gray">${rtb('inactive')}</span>`,
  }[s] || `<span class="badge badge-blue">${s}</span>`);

  /* Empty state */
  if(!total) {
    document.getElementById('rt-tbody').innerHTML = `<tr><td colspan="15" style="text-align:center;padding:60px;color:var(--mu)">
      <div style="font-size:40px;margin-bottom:12px">📭</div>
      <div style="font-size:14px;font-weight:700">${rtb('empty')}</div></td></tr>`;
    document.getElementById('rt-tfoot').style.display = 'none';
    document.getElementById('rt-pagination').innerHTML = '';
    document.getElementById('rt-page-info').textContent = '';
    return;
  }

  /* Rows */
  document.getElementById('rt-tbody').innerHTML = slice.map((c, i) => {
    const isCC  = !!c.cc_status;
    const tot   = (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0));
    const rCls  = isCC ? 'row-cc-card' : c.status==='modified' ? 'row-modified' : c.status==='new_added' ? 'row-new_added' : '';
    const brName= isEn ? (c.branch?.name_en||c.branch?.name_ar||'—') : (c.branch?.name_ar||'—');
    const srcLbl= isCC ? `<span class="badge badge-purple" style="font-size:9px">📞 CC</span>` : `<span style="font-size:10px;color:var(--mu)">${rtb('regular')}</span>`;
    const kindBadge = c.account_kind==='new'
      ? `<span class="badge badge-green" style="font-size:9px">🟢 NEW</span>`
      : `<span class="badge badge-blue" style="font-size:9px">🔵 SUB</span>`;
    return `<tr class="${rCls}">
      <td style="color:var(--mu);font-size:10px">${start+i+1}</td>
      <td><span class="ac-num" style="font-size:13px;font-weight:800">${esc(String(c.account_number))}</span></td>
      <td style="font-size:11px;color:var(--mu);white-space:nowrap">${esc(c.month||'—')}</td>
      <td style="font-size:11px">${esc(brName)}</td>
      <td style="font-weight:700;color:var(--pri2)">${esc(c.broker?.name||'—')}</td>
      <td><span class="mono ${commColor(c.broker_commission)}" style="font-size:11px">$${parseFloat(c.broker_commission||0).toFixed(1)}</span></td>
      <td style="font-size:11px;color:var(--m2)">${c.marketer?.name&&c.marketer.name!==c.broker?.name?esc(c.marketer.name):'<span style="color:var(--mu)">—</span>'}</td>
      <td><span class="mono ${commColor(c.marketer_commission)}" style="font-size:11px">$${parseFloat(c.marketer_commission||0).toFixed(1)}</span></td>
      <td><span class="mono ${commColor(tot)}" style="font-size:11px;font-weight:700">$${tot.toFixed(1)}</span></td>
      <td class="mono" style="color:var(--pri2);font-size:11px">${fmtK(c.initial_deposit)}</td>
      <td class="mono" style="color:var(--gr);font-size:11px">${fmtK(c.monthly_deposit)}</td>
      <td>${kindBadge}</td>
      <td>${statusChip(c.status)}</td>
      <td>${srcLbl}</td>
      <td><a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm" style="padding:3px 7px;font-size:10px">${rtb('editBtn')}</a></td>
    </tr>`;
  }).join('');

  /* Footer totals */
  const visSlice = slice;
  const totDep  = visSlice.reduce((a,c)=>a+parseFloat(c.initial_deposit||0),0);
  const totMon  = visSlice.reduce((a,c)=>a+parseFloat(c.monthly_deposit||0),0);
  const avgBCom = visSlice.length ? (visSlice.reduce((a,c)=>a+parseFloat(c.broker_commission||0),0)/visSlice.length).toFixed(2) : '—';
  const avgMCom = visSlice.length ? (visSlice.reduce((a,c)=>a+parseFloat(c.marketer_commission||0),0)/visSlice.length).toFixed(2) : '—';
  const avgTot  = visSlice.length ? (visSlice.reduce((a,c)=>a+(parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0)),0)/visSlice.length).toFixed(2) : '—';
  _t('rt-tf-bcomm', `$${avgBCom} avg`); _t('rt-tf-mcomm', `$${avgMCom} avg`);
  _t('rt-tf-tot', `$${avgTot} avg`);
  _t('rt-tf-dep', fmtK(totDep)); _t('rt-tf-mon', fmtK(totMon));
  document.getElementById('rt-tfoot').style.display = '';

  /* Page info */
  _t('rt-page-info', rtb('pageOf').replace('{p}',currentPage).replace('{t}',pages));

  /* Pagination buttons */
  const pag = document.getElementById('rt-pagination');
  let btns = '';
  btns += `<button class="rt-page-btn" onclick="goPage(${currentPage-1})" ${currentPage===1?'disabled':''}>${rtb('prev')}</button>`;
  const start2 = Math.max(1, currentPage-2);
  const end2   = Math.min(pages, currentPage+2);
  if(start2>1) btns += `<button class="rt-page-btn" onclick="goPage(1)">1</button>${start2>2?'<span style="color:var(--mu)">…</span>':''}`;
  for(let p=start2;p<=end2;p++) btns += `<button class="rt-page-btn${p===currentPage?' active':''}" onclick="goPage(${p})">${p}</button>`;
  if(end2<pages) btns += `${end2<pages-1?'<span style="color:var(--mu)">…</span>':''}<button class="rt-page-btn" onclick="goPage(${pages})">${pages}</button>`;
  btns += `<button class="rt-page-btn" onclick="goPage(${currentPage+1})" ${currentPage===pages?'disabled':''}>${rtb('next')}</button>`;
  pag.innerHTML = btns;
}

function goPage(p) {
  const perPage = parseInt(document.getElementById('rt-per-page')?.value)||200;
  const pages = Math.max(1, Math.ceil(filteredData.length / perPage));
  if(p < 1 || p > pages) return;
  currentPage = p;
  renderTable();
  document.querySelector('.table-scroll')?.scrollTo({top:0,behavior:'smooth'});
}

/* ── Exports ── */
function exportExcel() {
  if(!filteredData.length) { toast(rtb('noExport'),'error'); return; }
  const isEn = rtL()==='en';
  const hdr = isEn
    ? ['#','Account #','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Total Comm','Initial Dep.','Monthly Dep.','Kind','Status','Source']
    : ['#','رقم الحساب','الشهر','الفرع','البروكر','ع.البروكر','المسوّق','ع.المسوّق','إجمالي ع.','إيداع أولي','إيداع شهري','نوع','الحالة','المصدر'];
  const rows = [hdr, ...filteredData.map((c,i) => [
    i+1, c.account_number, c.month,
    isEn?(c.branch?.name_en||c.branch?.name_ar||''):(c.branch?.name_ar||''),
    c.broker?.name||'', parseFloat(c.broker_commission||0),
    c.marketer?.name||'', parseFloat(c.marketer_commission||0),
    (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0)).toFixed(2),
    parseFloat(c.initial_deposit||0), parseFloat(c.monthly_deposit||0),
    c.account_kind||'', c.status||'',
    c.cc_status ? 'CC' : (isEn?'Regular':'عادي'),
  ])];
  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet(rows);
  ws['!cols'] = hdr.map(()=>({wch:16}));
  XLSX.utils.book_append_sheet(wb, ws, isEn?'Report':'التقرير');
  XLSX.writeFile(wb, 'WafraReport_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast(rtb('excelDone'),'success');
}

function exportPdf() {
  if(!filteredData.length) { toast(rtb('noExport'),'error'); return; }
  const isEn = rtL()==='en';
  const {jsPDF} = window.jspdf;
  const doc = new jsPDF({orientation:'landscape',unit:'mm',format:'a3'});
  doc.setFontSize(13); doc.setTextColor(26,173,186);
  doc.text(isEn?'Wafra Gulf — Report':'وفرة الخليجية — التقرير', 210, 14, {align:'center'});
  doc.autoTable({
    startY:22,
    head:[isEn?['#','Account #','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Total','Initial','Monthly','Kind','Status']:
              ['#','الحساب','الشهر','الفرع','البروكر','ع.ب','المسوّق','ع.م','إجمالي','أولي','شهري','نوع','الحالة']],
    body:filteredData.map((c,i)=>[i+1,c.account_number,c.month,
      isEn?(c.branch?.name_en||c.branch?.name_ar||''):(c.branch?.name_ar||''),
      c.broker?.name||'',parseFloat(c.broker_commission||0).toFixed(1),
      c.marketer?.name||'',parseFloat(c.marketer_commission||0).toFixed(1),
      (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0)).toFixed(1),
      parseFloat(c.initial_deposit||0).toLocaleString(),parseFloat(c.monthly_deposit||0).toLocaleString(),
      c.account_kind||'',c.status||'']),
    styles:{fontSize:6,cellPadding:2},
    headStyles:{fillColor:[26,173,186],textColor:[255,255,255]},
    didParseCell:d=>{
      if(d.row.raw?.[12]==='modified') Object.values(d.row.cells).forEach(cl=>{cl.styles.fillColor=[255,248,220];});
    }
  });
  doc.save('WafraReport_'+new Date().toISOString().slice(0,10)+'.pdf');
  toast(rtb('pdfDone'),'success');
}

/* ── Init filter dropdowns ── */
async function initFilters() {
  const now = new Date();
  ['rt-from','rt-to'].forEach(id=>{
    const sel=document.getElementById(id); if(!sel) return;
    for(let i=0;i<36;i++){
      const d=new Date(now.getFullYear(),now.getMonth()-i,1);
      const m=d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
      const o=document.createElement('option');o.value=o.textContent=m;sel.appendChild(o);
    }
  });
  const [empR, brR] = await Promise.all([api('GET','/employees?status=approved'), api('GET','/branches')]);
  if(empR.success) {
    const sel=document.getElementById('rt-broker'); if(!sel) return;
    empR.data.filter(e=>e.role==='broker').forEach(e=>{
      const o=document.createElement('option');o.value=e.id;o.textContent=e.name;sel.appendChild(o);
    });
  }
  if(brR.success) {
    const sel=document.getElementById('rt-branch'); if(!sel) return;
    brR.data.forEach(b=>{
      const o=document.createElement('option');o.value=b.id;
      o.textContent=rtL()==='en'?(b.name_en||b.name_ar):b.name_ar;
      sel.appendChild(o);
    });
  }
}

/* ── Boot ── */
rtApplyLang();
initFilters().then(() => loadTable());
</script>
@endpush
