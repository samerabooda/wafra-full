@extends('layouts.app')
@section('title', 'كروت العمولات')
@section('page-title', 'كروت العمولات')

@section('topbar-actions')
<a href="{{ route('cards.create') }}" class="tb-btn primary" id="idx-btn-new">➕ كرت جديد</a>
@endsection

@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'index'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">
<style>
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap}
.chip-new      {background:rgba(34,201,122,.12);color:#22c97a;border:1px solid rgba(34,201,122,.25)}
.chip-modified {background:rgba(245,166,35,.12);color:#f5a623;border:1px solid rgba(245,166,35,.25)}
.chip-active   {background:rgba(46,134,171,.12);color:#3a9db5;border:1px solid rgba(46,134,171,.25)}
.chip-inactive {background:rgba(120,120,140,.1);color:#8899aa;border:1px solid rgba(120,120,140,.2)}
.chip-cc       {background:linear-gradient(135deg,#7b68ee22,#5f4fcf22);color:#7b68ee;border:1px solid rgba(123,104,238,.3)}
.chip-new-acc  {background:rgba(34,201,122,.1);color:#22c97a;border:1px solid rgba(34,201,122,.2)}
.chip-sub-acc  {background:rgba(46,134,171,.1);color:#2e86ab;border:1px solid rgba(46,134,171,.2)}
.data-table tr.row-cc-card td{background:linear-gradient(90deg,rgba(123,104,238,.06),transparent 70%)!important}
.data-table tr.row-cc-card td:first-child{border-right:3px solid #7b68ee}
.data-table tr.row-cc-card:hover td{background:linear-gradient(90deg,rgba(123,104,238,.12),rgba(123,104,238,.03) 70%)!important}
.data-table tr.row-modified td{background:rgba(245,166,35,.05)!important}
.data-table tr.row-modified td:first-child{border-right:3px solid #f5a623}
.data-table tr.row-new_added td:first-child{border-right:3px solid #22c97a}
.data-table tr.row-inactive td{opacity:.65}
.comm-low{color:#22c97a}.comm-mid{color:#f5a623}.comm-high{color:#e05050}
.kpi-strip{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.kpi-pill{display:flex;flex-direction:column;align-items:center;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;padding:10px 16px;min-width:120px;flex:1}
.kpi-pill-val{font-size:18px;font-weight:800;font-family:'JetBrains Mono',monospace}
.kpi-pill-lbl{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-top:3px}
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;padding:14px 16px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;margin-bottom:14px}
.filter-group{display:flex;flex-direction:column;gap:4px}
.filter-label{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}
.filter-bar .form-control{min-width:110px}
.legend{display:flex;gap:12px;flex-wrap:wrap;align-items:center;padding:8px 16px;background:var(--bg2);border-radius:10px;margin-bottom:10px;font-size:11px;color:var(--mu)}
.legend-dot{width:10px;height:10px;border-radius:3px;display:inline-block}
</style>

<!-- Account Number Quick Search -->
<div id="ac-search-bar" style="
  display:flex;gap:10px;align-items:center;
  background:linear-gradient(135deg,rgba(26,173,186,.08),rgba(26,173,186,.04));
  border:1px solid rgba(26,173,186,.25);border-radius:14px;
  padding:14px 18px;margin-bottom:14px;flex-wrap:wrap;
">
  <span style="font-size:20px">🔍</span>
  <div style="flex:1;min-width:200px">
    <div style="font-size:11px;font-weight:700;color:var(--pri2);margin-bottom:5px" id="idx-acsearch-lbl">بحث سريع برقم الحساب</div>
    <div style="display:flex;gap:8px;align-items:center">
      <input type="text" id="ac-search-input" class="form-control"
        placeholder="أدخل رقم الحساب ..." inputmode="numeric"
        style="flex:1;max-width:280px;font-family:'JetBrains Mono',monospace;font-size:15px;font-weight:700;letter-spacing:.5px"
        onkeydown="if(event.key==='Enter')acSearch()">
      <button class="btn btn-primary btn-sm" onclick="acSearch()" style="white-space:nowrap" id="idx-acsearch-btn">بحث</button>
      <button class="btn btn-ghost btn-sm" onclick="acSearchClear()" id="idx-acsearch-clear" style="display:none">✕ مسح</button>
    </div>
  </div>
  <div id="ac-search-result-lbl" style="font-size:12px;color:var(--mu)"></div>
</div>

<!-- KPI Summary Strip -->
<div class="kpi-strip">
  <div class="kpi-pill kpi-blue">
    <span class="kpi-pill-val" id="kpi-total" style="color:var(--pri2)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-total">إجمالي الكروت</span>
  </div>
  <div class="kpi-pill kpi-green">
    <span class="kpi-pill-val" id="kpi-new" style="color:var(--gr)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-new">جديد NEW</span>
  </div>
  <div class="kpi-pill kpi-blue">
    <span class="kpi-pill-val" id="kpi-sub" style="color:var(--pri)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-sub">فرعي SUB</span>
  </div>
  <div class="kpi-pill kpi-orange">
    <span class="kpi-pill-val" id="kpi-modified" style="color:var(--or)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-mod">معدّل</span>
  </div>
  <div class="kpi-pill kpi-purple">
    <span class="kpi-pill-val" id="kpi-cc" style="color:var(--pu)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-cc">من CC</span>
  </div>
  <div class="kpi-pill kpi-teal">
    <span class="kpi-pill-val" id="kpi-dep" style="color:var(--pri2)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-dep">إجمالي الإيداع</span>
  </div>
  <div class="kpi-pill kpi-green">
    <span class="kpi-pill-val" id="kpi-avgcomm" style="color:var(--gr)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-avg">متوسط العمولة</span>
  </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-month">الشهر</span>
    <select id="f-month" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-allmonths">كل الشهور</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-broker">البروكر</span>
    <select id="f-broker" class="form-control" style="min-width:140px" onchange="loadCards()">
      <option value="" id="idx-opt-allbrokers">كل البروكرات</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-kind">نوع الحساب</span>
    <select id="f-kind" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-all1">الكل</option>
      <option value="new" id="idx-opt-kindnew">🟢 جديد NEW</option>
      <option value="sub" id="idx-opt-kindsub">🔵 فرعي SUB</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-status">الحالة</span>
    <select id="f-status" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-all2">الكل</option>
      <option value="active"    id="idx-opt-stactive">✅ عادي</option>
      <option value="modified"  id="idx-opt-stmod">✏️ معدّل</option>
      <option value="new_added" id="idx-opt-stnew">🆕 مضاف جديد</option>
      <option value="inactive"  id="idx-opt-stinact">⛔ غير نشط</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-source">المصدر</span>
    <select id="f-source" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-all3">الكل</option>
      <option value="regular" id="idx-opt-srcreg">🏦 عادي</option>
      <option value="cc"      id="idx-opt-srccc">📞 من CC</option>
    </select>
  </div>
  <div class="filter-group" style="flex:1;min-width:180px">
    <span class="filter-label" id="idx-fl-search">بحث</span>
    <input type="text" id="f-search" class="form-control" placeholder="رقم حساب / اسم بروكر / مسوّق..." oninput="debounceLoad()">
  </div>
  <div style="display:flex;flex-direction:column;gap:4px">
    <span class="filter-label">&nbsp;</span>
    <div style="display:flex;gap:6px">
      <button class="btn btn-ghost btn-sm" onclick="clearFilters()">✕</button>
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportExcel()">📗 Excel</button>
      <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)" onclick="exportPdf()">📄 PDF</button>
    </div>
  </div>
</div>

<!-- Legend -->
<div class="legend">
  <strong style="color:var(--tx)" id="idx-legend-title">دليل الألوان:</strong>
  <span><span class="legend-dot" style="background:#7b68ee"></span> <span id="idx-legend-cc">كرت CC (مركز الاتصال)</span></span>
  <span><span class="legend-dot" style="background:#f5a623"></span> <span id="idx-legend-mod">معدّل</span></span>
  <span><span class="legend-dot" style="background:#22c97a"></span> <span id="idx-legend-new">مضاف جديد</span></span>
  <span style="color:#e05050" id="idx-legend-high">عمولة &gt;5$</span>
  <span style="color:#f5a623" id="idx-legend-mid">عمولة 2-5$</span>
  <span style="color:#22c97a" id="idx-legend-low">عمولة ≤2$</span>
</div>

<!-- Table -->
<div class="panel" style="padding:0">
  <div class="panel-header" style="padding:12px 16px">
    <div class="panel-title" id="idx-panel-title">🗂 كروت العمولات <span id="cards-count" style="font-size:11px;color:var(--mu);font-weight:400;margin-right:8px"></span></div>
    <div id="cards-summary" style="font-size:11px;color:var(--mu)"></div>
  </div>
  <div class="table-scroll">
    <table class="data-table" id="cards-table">
      <thead>
        <tr>
          <th id="idx-th-ac">رقم الحساب</th>
          <th id="idx-th-month">الشهر</th>
          <th id="idx-th-branch">الفرع</th>
          <th id="idx-th-broker">البروكر</th>
          <th id="idx-th-bcomm">ع. البروكر</th>
          <th id="idx-th-mktr">المسوّق</th>
          <th id="idx-th-mcomm">ع. المسوّق</th>
          <th id="idx-th-ext1">خارجي 1</th>
          <th id="idx-th-ext2">خارجي 2</th>
          <th id="idx-th-total">إجمالي ع.</th>
          <th id="idx-th-dep">إيداع أولي</th>
          <th id="idx-th-mon">إيداع شهري</th>
          <th id="idx-th-kind">نوع</th>
          <th id="idx-th-status">الحالة</th>
          <th id="idx-th-actions">إجراءات</th>
        </tr>
      </thead>
      <tbody id="cards-tbody">
        <tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)">
          <div style="font-size:32px;opacity:.3;margin-bottom:8px">⏳</div>جاري التحميل...
        </td></tr>
      </tbody>
      <tfoot id="cards-tfoot" style="display:none">
        <tr style="background:var(--bg2);font-weight:700">
          <td colspan="4" id="idx-tf-label" style="padding:8px 12px;font-size:11px;color:var(--mu)">المجاميع ↓</td>
          <td id="tf-bcomm" class="mono" style="font-size:11px"></td>
          <td></td>
          <td id="tf-mcomm" class="mono" style="font-size:11px"></td>
          <td colspan="2"></td>
          <td id="tf-total-comm" class="mono" style="color:var(--or);font-size:11px"></td>
          <td id="tf-dep" class="mono" style="color:var(--pri2);font-size:11px"></td>
          <td id="tf-mon" class="mono" style="color:var(--gr);font-size:11px"></td>
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
      <h3 style="margin:0;font-size:15px" id="idx-hist-title">📋 سجل التعديلات</h3>
      <button onclick="document.getElementById('history-modal').style.display='none'" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--mu)">✕</button>
    </div>
    <div id="history-body"></div>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// IDX — Cards Index Bilingual Dictionary
// ══════════════════════════════════════════════════════════
const IDX = {
  ar: {
    tbTitle:'كروت العمولات', btnNew:'➕ كرت جديد',
    kpiTotal:'إجمالي الكروت', kpiNew:'جديد NEW', kpiSub:'فرعي SUB',
    kpiMod:'معدّل', kpiCc:'من CC', kpiDep:'إجمالي الإيداع', kpiAvg:'متوسط العمولة',
    flMonth:'الشهر', allMonths:'كل الشهور', flBroker:'البروكر', allBrokers:'كل البروكرات',
    flKind:'نوع الحساب', all:'الكل', kindNew:'🟢 جديد NEW', kindSub:'🔵 فرعي SUB',
    flStatus:'الحالة', stActive:'✅ عادي', stMod:'✏️ معدّل', stNewAdded:'🆕 مضاف جديد', stInact:'⛔ غير نشط',
    flSource:'المصدر', srcReg:'🏦 عادي', srcCC:'📞 من CC',
    flSearch:'بحث', phSearch:'رقم حساب / اسم بروكر / مسوّق...',
    legendTitle:'دليل الألوان:', legendCC:'كرت CC (مركز الاتصال)', legendMod:'معدّل', legendNew:'مضاف جديد',
    legendHigh:'عمولة >5$', legendMid:'عمولة 2-5$', legendLow:'عمولة ≤2$',
    panelTitle:'🗂 كروت العمولات',
    thAc:'رقم الحساب', thMonth:'الشهر', thBranch:'الفرع', thBroker:'البروكر',
    thBComm:'ع. البروكر', thMktr:'المسوّق', thMComm:'ع. المسوّق',
    thExt1:'خارجي 1', thExt2:'خارجي 2', thTotal:'إجمالي ع.',
    thDep:'إيداع أولي', thMon:'إيداع شهري', thKind:'نوع', thStatus:'الحالة', thActions:'إجراءات',
    chipMod:'✏️ معدّل', chipNew:'🆕 جديد', chipActive:'✅ عادي', chipInact:'⛔ متوقف',
    loading:'جاري التحميل...', noResults:'لا توجد نتائج',
    avg:'متوسط', records:'سجل', tfLabel:'المجاميع ↓',
    histTitle:'📋 سجل التعديلات', histEmpty:'لا توجد تعديلات مسجّلة', histBy:'بواسطة',
    noData:'لا توجد بيانات للتصدير', excelDone:'تم تحميل Excel ✅', pdfDone:'تم تحميل PDF ✅',
    loadErr:'خطأ في تحميل البيانات',
  },
  en: {
    tbTitle:'Commission Cards', btnNew:'➕ New Card',
    kpiTotal:'Total Cards', kpiNew:'New', kpiSub:'Sub',
    kpiMod:'Modified', kpiCc:'From CC', kpiDep:'Total Deposit', kpiAvg:'Avg. Commission',
    flMonth:'Month', allMonths:'All Months', flBroker:'Broker', allBrokers:'All Brokers',
    flKind:'Account Kind', all:'All', kindNew:'🟢 New', kindSub:'🔵 Sub',
    flStatus:'Status', stActive:'✅ Active', stMod:'✏️ Modified', stNewAdded:'🆕 New Added', stInact:'⛔ Inactive',
    flSource:'Source', srcReg:'🏦 Regular', srcCC:'📞 From CC',
    flSearch:'Search', phSearch:'Account # / Broker / Marketer...',
    legendTitle:'Color Guide:', legendCC:'CC Card (Call Center)', legendMod:'Modified', legendNew:'New Added',
    legendHigh:'Commission >$5', legendMid:'Commission $2-5', legendLow:'Commission ≤$2',
    panelTitle:'🗂 Commission Cards',
    thAc:'AC No.', thMonth:'Month', thBranch:'Branch', thBroker:'Broker',
    thBComm:'B.Comm', thMktr:'Marketer', thMComm:'M.Comm',
    thExt1:'External 1', thExt2:'External 2', thTotal:'Total Comm.',
    thDep:'Initial Dep.', thMon:'Monthly Dep.', thKind:'Kind', thStatus:'Status', thActions:'Actions',
    chipMod:'✏️ Modified', chipNew:'🆕 New Added', chipActive:'✅ Active', chipInact:'⛔ Inactive',
    loading:'Loading...', noResults:'No results found',
    avg:'avg', records:'records', tfLabel:'Totals ↓',
    histTitle:'📋 Modification History', histEmpty:'No modifications recorded', histBy:'by',
    noData:'No data to export', excelDone:'Excel downloaded ✅', pdfDone:'PDF downloaded ✅',
    loadErr:'Error loading data',
  }
};
function idxL()   { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function idx(key) { const l=idxL(); return IDX[l]?.[key]??IDX.ar[key]??key; }

function idxApplyLang() {
  const map = {
    'idx-kpi-total':'kpiTotal','idx-kpi-new':'kpiNew','idx-kpi-sub':'kpiSub',
    'idx-kpi-mod':'kpiMod','idx-kpi-cc':'kpiCc','idx-kpi-dep':'kpiDep','idx-kpi-avg':'kpiAvg',
    'idx-fl-month':'flMonth','idx-fl-broker':'flBroker','idx-fl-kind':'flKind',
    'idx-fl-status':'flStatus','idx-fl-source':'flSource','idx-fl-search':'flSearch',
    'idx-legend-title':'legendTitle','idx-legend-cc':'legendCC','idx-legend-mod':'legendMod',
    'idx-legend-new':'legendNew','idx-legend-high':'legendHigh','idx-legend-mid':'legendMid','idx-legend-low':'legendLow',
    'idx-th-ac':'thAc','idx-th-month':'thMonth','idx-th-branch':'thBranch','idx-th-broker':'thBroker',
    'idx-th-bcomm':'thBComm','idx-th-mktr':'thMktr','idx-th-mcomm':'thMComm',
    'idx-th-ext1':'thExt1','idx-th-ext2':'thExt2','idx-th-total':'thTotal',
    'idx-th-dep':'thDep','idx-th-mon':'thMon','idx-th-kind':'thKind','idx-th-status':'thStatus','idx-th-actions':'thActions',
    'idx-tf-label':'tfLabel','idx-hist-title':'histTitle',
  };
  Object.entries(map).forEach(([id,key])=>{ const el=document.getElementById(id); if(el) el.textContent=idx(key); });

  const optMap = {
    'idx-opt-allmonths':'allMonths','idx-opt-allbrokers':'allBrokers',
    'idx-opt-all1':'all','idx-opt-kindnew':'kindNew','idx-opt-kindsub':'kindSub',
    'idx-opt-all2':'all','idx-opt-stactive':'stActive','idx-opt-stmod':'stMod',
    'idx-opt-stnew':'stNewAdded','idx-opt-stinact':'stInact',
    'idx-opt-all3':'all','idx-opt-srcreg':'srcReg','idx-opt-srccc':'srcCC',
  };
  Object.entries(optMap).forEach(([id,key])=>{ const el=document.getElementById(id); if(el) el.textContent=idx(key); });

  const searchEl=document.getElementById('f-search');
  if(searchEl) searchEl.placeholder=idx('phSearch');

  // Panel title
  const pt=document.getElementById('idx-panel-title');
  if(pt){ const cs=document.getElementById('cards-count'); pt.textContent=idx('panelTitle')+' '; if(cs) pt.appendChild(cs); }

  // Topbar
  const tb=document.querySelector('.tb-title'); if(tb) tb.textContent=idx('tbTitle');

  // Re-render table
  if(filteredCards.length>0) renderTable(filteredCards);
  if(filteredCards.length>0) updateKpis(filteredCards);
}

const _idxOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if(_idxOrigApplyLang) _idxOrigApplyLang(lang);
  idxApplyLang();
};

let allCards=[], filteredCards=[];

function commColor(val){ const v=parseFloat(val)||0; return v>5?'comm-high':v>2?'comm-mid':'comm-low'; }
function commFmt(val){
  const v=parseFloat(val)||0;
  if(v===0) return '<span style="color:var(--mu);font-size:10px">—</span>';
  return `<span class="mono ${commColor(v)}">$${v}/lot</span>`;
}

async function loadFilterOptions(){
  const r=await api('GET','/cards?per_page=500');
  if(!r.success) return;
  const cards=r.data?.data||[];
  const months=[...new Set(cards.map(c=>c.month))].sort().reverse();
  const brokers=[...new Set(cards.map(c=>c.broker?.name).filter(Boolean))].sort();
  const mSel=document.getElementById('f-month');
  months.forEach(m=>{const o=document.createElement('option');o.value=o.textContent=m;mSel.appendChild(o);});
  const bSel=document.getElementById('f-broker');
  brokers.forEach(b=>{const o=document.createElement('option');o.value=o.textContent=b;bSel.appendChild(o);});
}

async function loadCards(){
  const params=new URLSearchParams();
  const month=document.getElementById('f-month').value;
  const broker=document.getElementById('f-broker').value;
  const kind=document.getElementById('f-kind').value;
  const status=document.getElementById('f-status').value;
  const search=document.getElementById('f-search').value;
  const source=document.getElementById('f-source').value;
  if(month)  params.set('month',month);
  if(kind)   params.set('kind',kind);
  if(status) params.set('status',status);
  if(search) params.set('search',search);
  params.set('per_page',200);

  document.getElementById('cards-tbody').innerHTML=`<tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:28px;opacity:.3;margin-bottom:8px">⏳</div>${idx('loading')}</td></tr>`;

  const r=await api('GET','/cards?'+params);
  if(!r.success){ toast(idx('loadErr'),'error'); return; }
  allCards=r.data?.data||[];
  filteredCards=source==='cc'?allCards.filter(c=>!!c.cc_branch_id):source==='regular'?allCards.filter(c=>!c.cc_branch_id):allCards;
  updateKpis(filteredCards);
  renderTable(filteredCards);
}

function updateKpis(cards){
  const total=cards.length;
  const newAcc=cards.filter(c=>c.account_kind==='new').length;
  const subAcc=cards.filter(c=>c.account_kind==='sub').length;
  const modAcc=cards.filter(c=>c.status==='modified').length;
  const ccAcc=cards.filter(c=>!!c.cc_branch_id).length;
  const dep=cards.reduce((s,c)=>s+parseFloat(c.initial_deposit||0),0);
  const avgComm=total?(cards.reduce((s,c)=>s+parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0),0)/total):0;
  document.getElementById('kpi-total').textContent=total.toLocaleString();
  document.getElementById('kpi-new').textContent=newAcc.toLocaleString();
  document.getElementById('kpi-sub').textContent=subAcc.toLocaleString();
  document.getElementById('kpi-modified').textContent=modAcc.toLocaleString();
  document.getElementById('kpi-cc').textContent=ccAcc.toLocaleString();
  document.getElementById('kpi-dep').textContent=fmtK(dep);
  document.getElementById('kpi-avgcomm').textContent='$'+avgComm.toFixed(1);
  document.getElementById('cards-count').textContent=total+' '+idx('records');
}

function renderTable(cards){
  const tbody=document.getElementById('cards-tbody');
  const tfoot=document.getElementById('cards-tfoot');
  if(!cards.length){
    tbody.innerHTML=`<tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:36px;opacity:.2;margin-bottom:8px">📭</div>${idx('noResults')}</td></tr>`;
    tfoot.style.display='none'; return;
  }
  const statusChip=s=>({
    modified: `<span class="chip chip-modified">${idx('chipMod')}</span>`,
    new_added:`<span class="chip chip-new">${idx('chipNew')}</span>`,
    active:   `<span class="chip chip-active">${idx('chipActive')}</span>`,
    inactive: `<span class="chip chip-inactive">${idx('chipInact')}</span>`,
  }[s]||`<span class="chip chip-active">${s}</span>`);

  tbody.innerHTML=cards.map(c=>{
    const isCc=!!c.cc_branch_id;
    const totalComm=(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
    const rowClass=isCc?'row-cc-card':c.status==='modified'?'row-modified':c.status==='new_added'?'row-new_added':c.status==='inactive'?'row-inactive':'';
    const ext1=c.ext_marketer1?.name?`<span style="color:var(--pu);font-size:11px">${esc(c.ext_marketer1.name)}</span><br><span class="mono ${commColor(c.ext_commission1)}" style="font-size:10px">$${c.ext_commission1}/lot</span>`:'<span style="color:var(--mu);font-size:10px">—</span>';
    const ext2=c.ext_marketer2?.name?`<span style="color:var(--pu);font-size:11px">${esc(c.ext_marketer2.name)}</span><br><span class="mono ${commColor(c.ext_commission2)}" style="font-size:10px">$${c.ext_commission2}/lot</span>`:'<span style="color:var(--mu);font-size:10px">—</span>';
    const branchName=idxL()==='en'?(c.branch?.name_en||c.branch?.name_ar||'—'):(c.branch?.name_ar||'—');
    return `<tr class="${rowClass}">
      <td>${isCc?'<span class="chip chip-cc" style="margin-left:4px;font-size:9px">📞 CC</span>':''}<span class="ac-num" style="font-size:13px;font-weight:800">#${esc(String(c.account_number))}</span></td>
      <td style="color:var(--mu);font-size:11px;white-space:nowrap">${esc(c.month)}</td>
      <td style="font-size:11px;color:var(--mu)">${esc(branchName)}</td>
      <td style="font-weight:700;color:var(--pri2)">${esc(c.broker?.name||'—')}</td>
      <td>${commFmt(c.broker_commission)}</td>
      <td style="color:var(--m2);font-size:11px">${c.marketer?.name&&c.marketer.name!==c.broker?.name?esc(c.marketer.name):'<span style="color:var(--mu)">—</span>'}</td>
      <td>${commFmt(c.marketer_commission)}</td>
      <td style="font-size:11px">${ext1}</td>
      <td style="font-size:11px">${ext2}</td>
      <td><span class="mono ${commColor(totalComm)}" style="font-weight:700">$${totalComm.toFixed(1)}</span></td>
      <td class="mono" style="color:var(--pri2);font-weight:600">${fmt(c.initial_deposit)}</td>
      <td class="mono" style="color:var(--gr)">${fmt(c.monthly_deposit)}</td>
      <td><span class="chip ${c.account_kind==='new'?'chip-new-acc':'chip-sub-acc'}">${c.account_kind==='new'?'🟢 NEW':'🔵 SUB'}</span></td>
      <td>${statusChip(c.status)}</td>
      <td><a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm" style="padding:4px 8px">✏️</a>${c.status==='modified'?`<button class="btn btn-sm" style="background:rgba(245,166,35,.1);color:var(--or);border:1px solid rgba(245,166,35,.25);padding:4px 8px" onclick="viewHistory(${c.id})">📋</button>`:''}</td>
    </tr>`;
  }).join('');

  const totalBComm=cards.reduce((s,c)=>s+(parseFloat(c.broker_commission)||0),0);
  const totalMComm=cards.reduce((s,c)=>s+(parseFloat(c.marketer_commission)||0),0);
  const totalAll=cards.reduce((s,c)=>s+(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0),0);
  const totalDep=cards.reduce((s,c)=>s+(parseFloat(c.initial_deposit)||0),0);
  const totalMon=cards.reduce((s,c)=>s+(parseFloat(c.monthly_deposit)||0),0);
  const avgLbl=idx('avg');
  document.getElementById('tf-bcomm').innerHTML=`$${(totalBComm/cards.length).toFixed(1)} ${avgLbl}`;
  document.getElementById('tf-mcomm').innerHTML=`$${(totalMComm/cards.length).toFixed(1)} ${avgLbl}`;
  document.getElementById('tf-total-comm').innerHTML=`$${(totalAll/cards.length).toFixed(1)} ${avgLbl}`;
  document.getElementById('tf-dep').innerHTML=fmtK(totalDep);
  document.getElementById('tf-mon').innerHTML=fmtK(totalMon);
  tfoot.style.display='';
}

function clearFilters(){
  ['f-month','f-broker','f-kind','f-status','f-source'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('f-search').value=''; loadCards();
}
let debounceTimer;
function debounceLoad(){ clearTimeout(debounceTimer); debounceTimer=setTimeout(loadCards,380); }

async function viewHistory(id){
  const r=await api('GET',`/cards/${id}`);
  if(!r.success) return;
  const mods=r.data?.modifications||[];
  document.getElementById('idx-hist-title').textContent=idx('histTitle');
  const body=document.getElementById('history-body');
  if(!mods.length){ body.innerHTML=`<p style="color:var(--mu);text-align:center">${idx('histEmpty')}</p>`; }
  else{ body.innerHTML=mods.map(m=>`<div style="border:1px solid var(--brd1);border-radius:10px;padding:12px;margin-bottom:8px">
    <div style="font-size:11px;color:var(--mu);margin-bottom:6px">${esc(m.modified_at||'')} — ${idx('histBy')}: ${esc(m.modified_by?.name||'—')}</div>
    <div style="font-size:12px">${Object.entries(m.changes||{}).map(([k,v])=>`<span style="background:var(--bg2);border-radius:6px;padding:2px 8px;margin:2px;display:inline-block;font-size:11px"><strong>${esc(k)}:</strong> <del style="color:var(--re)">${esc(String(v.old??'—'))}</del> → <span style="color:var(--gr)">${esc(String(v.new??'—'))}</span></span>`).join('')}</div>
  </div>`).join(''); }
  document.getElementById('history-modal').style.display='block';
}

function exportExcel(){
  if(!filteredCards.length){ toast(idx('noData'),'error'); return; }
  const isEn=idxL()==='en';
  const hdr=isEn?['AC No.','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Ext 1','Ext 1C','Ext 2','Ext 2C','Total Comm.','Initial Dep.','Monthly Dep.','Kind','Status','Source']:['رقم الحساب','الشهر','الفرع','البروكر','ع.البروكر','المسوّق','ع.المسوّق','خارجي 1','ع.خ1','خارجي 2','ع.خ2','إجمالي ع.','إيداع أولي','إيداع شهري','نوع','الحالة','المصدر'];
  const rows=[hdr,...filteredCards.map(c=>{
    const tot=(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
    return [c.account_number,c.month,isEn?(c.branch?.name_en||c.branch?.name_ar||''):(c.branch?.name_ar||''),c.broker?.name||'','$'+c.broker_commission+'/lot',c.marketer?.name||'—','$'+c.marketer_commission+'/lot',c.ext_marketer1?.name||'—','$'+(c.ext_commission1||0)+'/lot',c.ext_marketer2?.name||'—','$'+(c.ext_commission2||0)+'/lot','$'+tot.toFixed(2)+'/lot',c.initial_deposit,c.monthly_deposit,c.account_kind==='new'?'NEW':'SUB',c.status,c.cc_branch_id?'CC':(isEn?'Regular':'عادي')];
  })];
  const wb=XLSX.utils.book_new();
  const ws=XLSX.utils.aoa_to_sheet(rows);
  ws['!cols']=hdr.map(()=>({wch:14}));
  XLSX.utils.book_append_sheet(wb,ws,isEn?'Commission Cards':'كروت العمولات');
  XLSX.writeFile(wb,'WafraGulf_Cards_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast(idx('excelDone'),'success');
}

function exportPdf(){
  if(!filteredCards.length){ toast(idx('noData'),'error'); return; }
  const {jsPDF}=window.jspdf;
  const doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a3'});
  doc.setFontSize(14);doc.setTextColor(46,134,171);
  doc.text('Wafra Gulf — Commission Cards',210,14,{align:'center'});
  doc.setFontSize(9);doc.setTextColor(100,120,140);
  doc.text('Export: '+new Date().toLocaleDateString(),14,14);
  doc.autoTable({
    startY:22,
    head:[['AC No.','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Ext1','Ext2','Total','Initial','Monthly','Kind','Status','Src']],
    body:filteredCards.map(c=>{
      const tot=(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
      return [c.account_number,c.month,c.branch?.name_ar||'',c.broker?.name||'—','$'+c.broker_commission,c.marketer?.name||'—','$'+c.marketer_commission,c.ext_marketer1?.name||'—',c.ext_marketer2?.name||'—','$'+tot.toFixed(1),fmt(c.initial_deposit),fmt(c.monthly_deposit),c.account_kind?.toUpperCase(),c.status,c.cc_branch_id?'CC':'-'];
    }),
    styles:{fontSize:7,cellPadding:1.5},
    headStyles:{fillColor:[46,134,171],textColor:255,fontStyle:'bold'},
    alternateRowStyles:{fillColor:[245,248,252]},
  });
  doc.save('WafraGulf_Cards_'+new Date().toISOString().slice(0,10)+'.pdf');
  toast(idx('pdfDone'),'success');
}

idxApplyLang();
loadFilterOptions();
loadCards();

// ── Account Number Quick Search ────────────────────────────
function acSearch() {
  const inp = document.getElementById('ac-search-input');
  const val = (inp?.value || '').trim();
  if (!val) { toast('أدخل رقم الحساب', 'error'); return; }

  // Put the value into the main search filter and reload
  const mainSearch = document.getElementById('f-search');
  if (mainSearch) mainSearch.value = val;

  // Also clear other filters for a clean search
  const monthSel = document.getElementById('f-month');
  const brokerSel = document.getElementById('f-broker');
  const kindSel = document.getElementById('f-kind');
  const statusSel = document.getElementById('f-status');
  const sourceSel = document.getElementById('f-source');
  if (monthSel) monthSel.value = '';
  if (brokerSel) brokerSel.value = '';
  if (kindSel) kindSel.value = '';
  if (statusSel) statusSel.value = '';
  if (sourceSel) sourceSel.value = '';

  currentPage = 1;
  loadCards().then(() => {
    const clearBtn = document.getElementById('idx-acsearch-clear');
    const lbl = document.getElementById('ac-search-result-lbl');
    if (clearBtn) clearBtn.style.display = '';
    if (lbl) {
      const cnt = filteredCards.length;
      lbl.textContent = cnt > 0
        ? (cnt + ' كرت ' + (cnt > 1 ? 'مسجّل' : 'مسجّل') + ' لهذا الحساب')
        : 'لا توجد نتائج لهذا الرقم';
      lbl.style.color = cnt > 0 ? 'var(--gr)' : 'var(--re)';
    }
  });
}

function acSearchClear() {
  const inp = document.getElementById('ac-search-input');
  const clearBtn = document.getElementById('idx-acsearch-clear');
  const lbl = document.getElementById('ac-search-result-lbl');
  if (inp) inp.value = '';
  if (clearBtn) clearBtn.style.display = 'none';
  if (lbl) lbl.textContent = '';
  clearFilters();
}
</script>
@endpush
