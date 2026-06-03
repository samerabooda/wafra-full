@extends('layouts.app')
@section('title','Branch Monthly Report')
@section('page-title','Branch Monthly Report')

@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- Reports Sidebar Nav --}}
  <div id="rnav-sidebar" style="width:220px;flex-shrink:0;background:var(--bg2);border-left:1px solid var(--brd1);display:flex;flex-direction:column;padding:10px 0;">
    <div style="padding:12px 16px 14px;border-bottom:1px solid var(--brd1);margin-bottom:8px">
      <div id="rnav-hdr" style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px">📈 التقارير</div>
    </div>
    <a href="{{ route('reports.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);">📊</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap" id="rnav-lbl-monthly">التقارير الشهرية</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-monthly">تحليل شامل بالرسوم البيانية</div>
      </div>
    </a>
    <a href="{{ route('reports.dynamic') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);">🔧</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap" id="rnav-lbl-dynamic">تقرير ديناميكي</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-dynamic">بناء تقرير مخصص</div>
      </div>
    </a>
    <a href="{{ route('reports.branch-monthly') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:rgba(245,166,35,.12);border:1px solid rgba(245,166,35,.3);">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(245,166,35,.25);border:1px solid rgba(245,166,35,.5);">🏢</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--or);white-space:nowrap" id="rnav-lbl-branch">تقرير شهري للفروع</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-branch">فرع + شهر محدد</div>
      </div>
    </a>
    <div style="flex:1"></div>
    <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
      <div id="rnav-footer" style="font-size:10px;color:var(--mu);line-height:1.6">منصة وفرة الخليجية</div>
    </div>
  </div>

  {{-- Content Panel --}}
  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

    {{-- Filter bar --}}
    <div class="panel" style="padding:20px;margin-bottom:16px">
      <div class="panel-header" style="padding:0 0 14px;border-bottom:1px solid var(--brd1);margin-bottom:16px">
        <div class="panel-title" id="bm-panel-title">🏢 تقرير شهري للفروع</div>
      </div>
      <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        @if(auth()->user()?->isFinanceAdmin())
        <div style="min-width:200px;flex:1">
          <label class="form-label" id="bm-lbl-branch">🏢 الفرع</label>
          <select id="bm-branch" class="form-control">
            <option value="" id="bm-opt-all-branch">— كل الفروع —</option>
          </select>
        </div>
        @endif
        <div style="min-width:160px;flex:1">
          <label class="form-label" id="bm-lbl-month">📅 الشهر (مثال: Jan 2025)</label>
          <input type="text" id="bm-month" class="form-control" placeholder="Jan 2025" dir="ltr"
                 list="bm-month-list" autocomplete="off">
          <datalist id="bm-month-list"></datalist>
        </div>
        <button class="btn btn-primary" onclick="bmGenerate()" id="bm-gen-btn">⚡ توليد التقرير</button>
        <button class="btn btn-ghost" onclick="bmExport()" id="bm-export-btn" style="display:none">📥 تصدير Excel</button>
      </div>
    </div>

    {{-- KPI summary --}}
    <div id="bm-kpis" style="display:none;margin-bottom:16px">
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
        <div class="kpi-card kpi-teal">
          <div class="kpi-label" id="bm-k-lbl-total">إجمالي الحسابات</div>
          <div class="kpi-value" id="bm-k-total">—</div>
        </div>
        <div class="kpi-card kpi-green">
          <div class="kpi-label" id="bm-k-lbl-dep">إيداع أولي</div>
          <div class="kpi-value" id="bm-k-dep">—</div>
          <div class="kpi-sub">USD</div>
        </div>
        <div class="kpi-card kpi-blue">
          <div class="kpi-label" id="bm-k-lbl-mon">إيداع شهري</div>
          <div class="kpi-value" id="bm-k-mon">—</div>
          <div class="kpi-sub">USD</div>
        </div>
        <div class="kpi-card kpi-orange">
          <div class="kpi-label" id="bm-k-lbl-comm">إجمالي العمولة</div>
          <div class="kpi-value" id="bm-k-comm">—</div>
          <div class="kpi-sub">$</div>
        </div>
        <div class="kpi-card kpi-purple">
          <div class="kpi-label" id="bm-k-lbl-mod">حسابات معدّلة</div>
          <div class="kpi-value" id="bm-k-mod">—</div>
        </div>
      </div>
    </div>

    {{-- Results table --}}
    <div id="bm-results">
      <div style="text-align:center;padding:60px 20px;color:var(--mu)">
        <div style="font-size:48px;margin-bottom:12px">🏢</div>
        <div style="font-size:15px;font-weight:700;margin-bottom:6px" id="bm-empty-title">اختر الفرع والشهر</div>
        <div style="font-size:12px" id="bm-empty-sub">اضغط ⚡ توليد التقرير لعرض البيانات</div>
      </div>
    </div>

  </div>{{-- content panel --}}
</div>{{-- shell --}}
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// BILINGUAL DICTIONARY
// ══════════════════════════════════════════════════════════
const BM = {
  ar: {
    panelTitle:'🏢 تقرير شهري للفروع',
    lblBranch:'🏢 الفرع', optAllBranch:'— كل الفروع —',
    lblMonth:'📅 الشهر (مثال: Jan 2025)',
    btnGen:'⚡ توليد التقرير', btnGenLoading:'⏳ جاري التحميل...',
    btnExport:'📥 تصدير Excel',
    kpiTotal:'إجمالي الحسابات', kpiDep:'إيداع أولي',
    kpiMon:'إيداع شهري', kpiComm:'إجمالي العمولة', kpiMod:'حسابات معدّلة',
    emptyTitle:'اختر الفرع والشهر',
    emptySub:'اضغط ⚡ توليد التقرير لعرض البيانات',
    toastEnterMonth:'أدخل الشهر أولاً — مثال: Jan 2025',
    toastLoadErr:'خطأ في التحميل',
    toastExported:'تم تصدير التقرير',
    toastNoData:'لا توجد بيانات',
    loadingData:'⏳ جاري تحميل البيانات...',
    noDataFor:'لا توجد بيانات لـ "{m}" في {b}',
    thAccount:'رقم الحساب', thMonth:'الشهر', thBroker:'البروكر',
    thBComm:'ع.بروكر', thMarketer:'المسوّق', thMComm:'ع.مسوّق',
    thInitDep:'إيداع أولي', thMonDep:'إيداع شهري',
    thStatus:'الحالة', thBranch:'الفرع',
    statusMod:'معدّل', statusAct:'عادي',
    allBranches:'كل الفروع',
    xlsxStatusMod:'معدّل', xlsxStatusAct:'عادي',
    navHdr:'📈 التقارير', navLblMonthly:'التقارير الشهرية',
    navSubMonthly:'تحليل شامل بالرسوم البيانية',
    navLblDynamic:'تقرير ديناميكي', navSubDynamic:'بناء تقرير مخصص',
    navLblBranch:'تقرير شهري للفروع', navSubBranch:'فرع + شهر محدد',
    navFooter:'منصة وفرة الخليجية\nلإدارة العمولات',
  },
  en: {
    panelTitle:'🏢 Branch Monthly Report',
    lblBranch:'🏢 Branch', optAllBranch:'— All Branches —',
    lblMonth:'📅 Month (e.g. Jan 2025)',
    btnGen:'⚡ Generate Report', btnGenLoading:'⏳ Loading...',
    btnExport:'📥 Export Excel',
    kpiTotal:'Total Accounts', kpiDep:'Initial Deposit',
    kpiMon:'Monthly Deposit', kpiComm:'Total Commission', kpiMod:'Modified Accounts',
    emptyTitle:'Select Branch & Month',
    emptySub:'Click ⚡ Generate Report to view data',
    toastEnterMonth:'Enter a month first — e.g. Jan 2025',
    toastLoadErr:'Error loading data',
    toastExported:'Report exported',
    toastNoData:'No data available',
    loadingData:'⏳ Loading data...',
    noDataFor:'No data for "{m}" in {b}',
    thAccount:'Account #', thMonth:'Month', thBroker:'Broker',
    thBComm:'Broker C.', thMarketer:'Marketer', thMComm:'Mkt. C.',
    thInitDep:'Initial Dep.', thMonDep:'Monthly Dep.',
    thStatus:'Status', thBranch:'Branch',
    statusMod:'Modified', statusAct:'Active',
    allBranches:'All Branches',
    xlsxStatusMod:'Modified', xlsxStatusAct:'Active',
    navHdr:'📈 Reports', navLblMonthly:'Monthly Reports',
    navSubMonthly:'Full analysis with charts',
    navLblDynamic:'Dynamic Report', navSubDynamic:'Build custom report',
    navLblBranch:'Branch Monthly Report', navSubBranch:'Branch + specific month',
    navFooter:'Wafra Gulf Platform\nCommission Management',
  }
};
function bmL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function bm(k) { return (BM[bmL()] || BM.ar)[k] || (BM.ar)[k] || k; }

function bmApplyLang() {
  const _t = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  _t('bm-panel-title',    bm('panelTitle'));
  _t('bm-lbl-branch',     bm('lblBranch'));
  _t('bm-opt-all-branch', bm('optAllBranch'));
  _t('bm-lbl-month',      bm('lblMonth'));
  const genBtn = document.getElementById('bm-gen-btn');
  if (genBtn && !genBtn.disabled) genBtn.textContent = bm('btnGen');
  const expBtn = document.getElementById('bm-export-btn');
  if (expBtn) expBtn.textContent = bm('btnExport');
  _t('bm-k-lbl-total',    bm('kpiTotal'));
  _t('bm-k-lbl-dep',      bm('kpiDep'));
  _t('bm-k-lbl-mon',      bm('kpiMon'));
  _t('bm-k-lbl-comm',     bm('kpiComm'));
  _t('bm-k-lbl-mod',      bm('kpiMod'));
  _t('bm-empty-title',    bm('emptyTitle'));
  _t('bm-empty-sub',      bm('emptySub'));
  // Sidebar nav
  _t('rnav-hdr',          bm('navHdr'));
  _t('rnav-lbl-monthly',  bm('navLblMonthly'));
  _t('rnav-sub-monthly',  bm('navSubMonthly'));
  _t('rnav-lbl-dynamic',  bm('navLblDynamic'));
  _t('rnav-sub-dynamic',  bm('navSubDynamic'));
  _t('rnav-lbl-branch',   bm('navLblBranch'));
  _t('rnav-sub-branch',   bm('navSubBranch'));
  const fn = document.getElementById('rnav-footer');
  if (fn) fn.innerHTML = bm('navFooter').replace('\n','<br>');
  // Re-render table if data is loaded
  if (_bmData && _bmData.length) bmRenderTable(_bmData);
}

let _bmData = [];
let _bmBranchName = '';
let _bmMonthVal = '';

// Load branches
async function bmLoadBranches() {
  const sel = document.getElementById('bm-branch'); if (!sel) return;
  const r = await api('GET', '/branches');
  if (!r.success) return;
  sel.innerHTML = `<option value="" id="bm-opt-all-branch">${bm('optAllBranch')}</option>` +
    r.data.map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
  // Also build month datalist suggestions
  const months = [];
  const now = new Date();
  for (let i = 0; i < 36; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push(d.toLocaleDateString('en-US',{month:'short',year:'numeric'}));
  }
  const dl = document.getElementById('bm-month-list');
  if (dl) dl.innerHTML = months.map(m => `<option value="${m}">`).join('');
}

async function bmGenerate() {
  const branchId = document.getElementById('bm-branch')?.value || '';
  const month    = document.getElementById('bm-month')?.value.trim() || '';
  const btn      = document.getElementById('bm-gen-btn');

  if (!month) { toast(bm('toastEnterMonth'),'error'); return; }

  btn.disabled = true;
  btn.textContent = bm('btnGenLoading');
  document.getElementById('bm-results').innerHTML =
    `<div style="text-align:center;padding:50px;color:var(--mu)">${bm('loadingData')}</div>`;
  document.getElementById('bm-kpis').style.display = 'none';

  let url = `/cards?month=${encodeURIComponent(month)}&per_page=200`;
  if (branchId) url += `&branch_id=${branchId}`;

  const r = await api('GET', url);
  btn.disabled = false;
  btn.textContent = bm('btnGen');

  if (!r.success) {
    document.getElementById('bm-results').innerHTML =
      `<div style="text-align:center;padding:40px;color:var(--re)">${r.message||bm('toastLoadErr')}</div>`;
    return;
  }

  _bmData = r.data?.data ?? r.data ?? [];
  _bmMonthVal = month;

  // Branch name
  const brSel = document.getElementById('bm-branch');
  _bmBranchName = branchId && brSel
    ? (brSel.options[brSel.selectedIndex]?.text || '')
    : bm('allBranches');

  bmRenderKpis(_bmData);
  bmRenderTable(_bmData);
  document.getElementById('bm-export-btn').style.display = '';
  document.getElementById('bm-export-btn').textContent = bm('btnExport');
}

function bmRenderKpis(data) {
  const kpis = document.getElementById('bm-kpis');
  if (!kpis) return;
  kpis.style.display = '';
  const total = data.length;
  const dep   = data.reduce((s,c) => s + parseFloat(c.initial_deposit||0), 0);
  const mon   = data.reduce((s,c) => s + parseFloat(c.monthly_deposit||0), 0);
  const comm  = data.reduce((s,c) => s +
    parseFloat(c.broker_commission||0) + parseFloat(c.marketer_commission||0) +
    parseFloat(c.ext_commission1||0)   + parseFloat(c.ext_commission2||0), 0);
  const mod   = data.filter(c => c.status==='modified').length;
  const fmtN  = n => n >= 1000 ? (n/1000).toFixed(1)+'k' : n.toFixed(0);
  document.getElementById('bm-k-total').textContent = total;
  document.getElementById('bm-k-dep').textContent   = '$'+fmtN(dep);
  document.getElementById('bm-k-mon').textContent   = '$'+fmtN(mon);
  document.getElementById('bm-k-comm').textContent  = '$'+comm.toFixed(1);
  document.getElementById('bm-k-mod').textContent   = mod;
}

function bmRenderTable(data) {
  const res = document.getElementById('bm-results');
  if (!data.length) {
    res.innerHTML = `<div style="text-align:center;padding:60px;color:var(--mu)">
      <div style="font-size:40px;margin-bottom:12px">🔎</div>
      <div style="font-size:15px;font-weight:700">${bm('noDataFor').replace('{m}',_bmMonthVal).replace('{b}',_bmBranchName)}</div>
    </div>`;
    return;
  }

  let html = `
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📋 ${data.length} ${bmL()==='en'?'accounts':'حساب'} — ${_bmMonthVal} — ${_bmBranchName}</div>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>${bm('thAccount')}</th>
          <th>${bm('thMonth')}</th>
          <th>${bm('thBroker')}</th>
          <th>${bm('thBComm')}</th>
          <th>${bm('thMarketer')}</th>
          <th>${bm('thMComm')}</th>
          <th>${bm('thInitDep')}</th>
          <th>${bm('thMonDep')}</th>
          <th>${bm('thStatus')}</th>
          <th>${bm('thBranch')}</th>
        </tr></thead>
        <tbody>`;

  data.forEach(c => {
    const isM = c.status === 'modified';
    html += `<tr${isM?' class="row-modified"':''}>
      <td><span class="ac-num">${c.account_number}</span></td>
      <td style="color:var(--mu)">${c.month}</td>
      <td style="font-weight:600">${c.broker?.name||'—'}</td>
      <td class="mono c-blue">$${c.broker_commission||0}</td>
      <td style="color:var(--mu)">${c.marketer?.name||'—'}</td>
      <td class="mono c-green">$${c.marketer_commission||0}</td>
      <td class="mono" style="color:var(--gr)">$${fmt(parseFloat(c.initial_deposit||0))}</td>
      <td class="mono" style="color:var(--pri2)">$${fmt(parseFloat(c.monthly_deposit||0))}</td>
      <td>${isM?`<span class="badge badge-orange">${bm('statusMod')}</span>`:`<span class="badge badge-blue">${bm('statusAct')}</span>`}</td>
      <td style="color:var(--mu);font-size:12px">${c.branch?.name_ar||'—'}</td>
    </tr>`;
  });

  html += `</tbody></table></div></div>`;
  res.innerHTML = html;
}

function fmt(n) { return n >= 1000 ? (n/1000).toFixed(1)+'k' : n.toFixed(0); }

function bmExport() {
  if (!_bmData.length) { toast(bm('toastNoData'),'error'); return; }
  const headers = [bm('thAccount'),bm('thMonth'),bm('thBroker'),bm('thBComm'),
    bm('thMarketer'),bm('thMComm'),bm('thInitDep'),bm('thMonDep'),bm('thStatus'),bm('thBranch')];
  const rows = _bmData.map(c => [
    c.account_number, c.month,
    c.broker?.name||'', c.broker_commission||0,
    c.marketer?.name||'', c.marketer_commission||0,
    parseFloat(c.initial_deposit||0), parseFloat(c.monthly_deposit||0),
    c.status==='modified'?bm('xlsxStatusMod'):bm('xlsxStatusAct'),
    c.branch?.name_ar||'',
  ]);
  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
  ws['!cols'] = headers.map(()=>({wch:18}));
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Report');
  XLSX.writeFile(wb, `branch_monthly_${_bmMonthVal}_${_bmBranchName}.xlsx`.replace(/\s+/g,'_'));
  toast(bm('toastExported'),'success');
}

// Init
bmLoadBranches();
bmApplyLang();

/* ── Language hook ──────────────────────────────────────── */
const _bmOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_bmOrigApplyLang) _bmOrigApplyLang(lang);
  bmApplyLang();
};
</script>
@endpush
