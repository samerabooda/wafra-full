@extends('layouts.app')
@section('title','Reports')
@section('page-title','Reports')

@section('content')
@verbatim
<style>
/* ====== Reports v3 — elegant ====== */
.rx{max-width:1280px;margin:0 auto}
.rx-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.rx-nav a,.rx-nav button{display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:12px;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);transition:all .18s;white-space:nowrap}
.rx-nav a:hover,.rx-nav button:hover{border-color:var(--pri);color:var(--pri2);transform:translateY(-1px)}
.rx-nav a.on{background:linear-gradient(135deg,rgba(26,173,186,.22),rgba(26,173,186,.08));border-color:rgba(26,173,186,.45);color:var(--pri2)}

/* Filter bar */
.rx-filter{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;padding:14px 16px;margin-bottom:16px;box-shadow:0 2px 12px rgba(0,0,0,.06)}
.rx-chips{display:flex;gap:6px;flex-wrap:wrap;align-items:center}
.rx-chip{padding:7px 14px;border-radius:20px;font-size:12px;font-weight:700;cursor:pointer;border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);transition:all .15s}
.rx-chip:hover{border-color:var(--pri);color:var(--pri2)}
.rx-chip.on{background:linear-gradient(135deg,var(--pri2),var(--pri));color:#fff;border-color:transparent;box-shadow:0 3px 10px rgba(26,173,186,.3)}
/* Month-range calendar */
.rx-cal{position:absolute;top:38px;right:0;z-index:50;width:280px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;box-shadow:0 12px 40px rgba(0,0,0,.3);padding:12px;display:none}
.rx-cal.open{display:block}
.rx-cal-tabs{display:flex;gap:6px;margin-bottom:10px}
.rx-cal-tab{flex:1;padding:7px 8px;border-radius:9px;border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);font-size:11px;font-weight:700;cursor:pointer}
.rx-cal-tab.on{border-color:var(--pri);color:var(--pri2);background:rgba(26,173,186,.1)}
.rx-cal-tab b{color:var(--tx)}
.rx-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.rx-cal-nav button{width:30px;height:30px;border-radius:8px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);cursor:pointer;font-size:16px}
.rx-cal-nav span{font-weight:900;font-family:'JetBrains Mono',monospace}
.rx-cal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:6px;margin-bottom:10px}
.rx-cal-m{padding:9px 4px;border-radius:9px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);font-size:12px;font-weight:700;cursor:pointer;text-align:center;transition:all .12s}
.rx-cal-m:hover{border-color:var(--pri);color:var(--pri2)}
.rx-cal-m.f{background:linear-gradient(135deg,var(--pri2),var(--pri));color:#fff;border-color:transparent}
.rx-cal-m.t{background:linear-gradient(135deg,#22c97a,#168a52);color:#fff;border-color:transparent}
.rx-cal-m.in{background:rgba(26,173,186,.14);border-color:rgba(26,173,186,.3)}
.rx-cal-foot{display:flex;gap:8px;justify-content:flex-end}
.rx-adv{display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--brd1);gap:10px;flex-wrap:wrap;align-items:flex-end}
.rx-adv.show{display:flex}
.rx-fg{display:flex;flex-direction:column;gap:4px;min-width:120px}
.rx-fl{font-size:9px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}

/* Insights */
.rx-insights{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:12px;margin-bottom:18px}
.rx-ins{display:flex;gap:12px;align-items:flex-start;padding:14px 16px;border-radius:14px;background:var(--card-bg);border:1px solid var(--card-brd);position:relative;overflow:hidden;transition:transform .18s,box-shadow .18s}
.rx-ins:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,.12)}
.rx-ins::before{content:'';position:absolute;inset:0 auto 0 0;width:4px}
.rx-ins.positive::before{background:#22c97a}.rx-ins.warning::before{background:#f5a623}
.rx-ins.info::before{background:#3a9db5}.rx-ins.highlight::before{background:#7b68ee}
.rx-ins-ico{font-size:26px;line-height:1;flex-shrink:0}
.rx-ins-body{flex:1;min-width:0}
.rx-ins-tag{font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.6px;margin-bottom:3px}
.rx-ins.positive .rx-ins-tag{color:#22c97a}.rx-ins.warning .rx-ins-tag{color:#f5a623}
.rx-ins.info .rx-ins-tag{color:#3a9db5}.rx-ins.highlight .rx-ins-tag{color:#7b68ee}
.rx-ins-txt{font-size:13px;font-weight:600;color:var(--tx);line-height:1.6}
.rx-ins-strong{font-weight:900}

/* KPI hero */
.rx-kpis{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-bottom:18px}
.rx-kpi{position:relative;overflow:hidden;border-radius:18px;padding:18px 20px;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,.14)}
.rx-kpi.k1{background:linear-gradient(135deg,#1AADBA,#127a83)}
.rx-kpi.k2{background:linear-gradient(135deg,#22c97a,#168a52)}
.rx-kpi.k3{background:linear-gradient(135deg,#3a9db5,#256b7d)}
.rx-kpi.k4{background:linear-gradient(135deg,#f5a623,#c47e10)}
.rx-kpi.k5{background:linear-gradient(135deg,#7b68ee,#5544c0)}
.rx-kpi-ico{position:absolute;right:12px;bottom:8px;font-size:54px;opacity:.18;line-height:1}
.rx-kpi-lbl{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;opacity:.85;margin-bottom:8px}
.rx-kpi-val{font-size:1.7rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1.1}
.rx-kpi-sub{font-size:11px;opacity:.8;margin-top:4px}

/* Chart panels */
.rx-grid{display:grid;gap:16px;margin-bottom:16px}
.rx-2{grid-template-columns:1fr 1fr}.rx-31{grid-template-columns:3fr 2fr}
.rx-panel{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.05);transition:box-shadow .18s}
.rx-panel:hover{box-shadow:0 6px 20px rgba(0,0,0,.1)}
.rx-ph{padding:14px 18px;border-bottom:1px solid var(--brd1);display:flex;align-items:center;justify-content:space-between}
.rx-pt{font-size:13px;font-weight:800}
.rx-pb{padding:14px;height:240px;position:relative}
.rx-pb-lg{padding:14px;height:280px;position:relative}
.rx-tbl{width:100%;border-collapse:collapse;font-size:12px}
.rx-tbl th{padding:8px 12px;background:var(--bg3);font-size:9px;text-transform:uppercase;color:var(--mu);border-bottom:1px solid var(--brd1);font-weight:800;text-align:right}
.rx-tbl td{padding:9px 12px;border-bottom:1px solid rgba(255,255,255,.04)}
.rx-tbl tr:last-child td{border-bottom:none}
.rx-tbl tr:hover td{background:rgba(26,173,186,.05)}
.rx-bar{height:6px;border-radius:3px;background:var(--bg3);overflow:hidden;margin-top:4px}
.rx-bar-fill{height:100%;border-radius:3px;background:linear-gradient(90deg,var(--pri2),var(--pri))}
.rx-spin{width:20px;height:20px;border:3px solid rgba(26,173,186,.2);border-top-color:var(--pri2);border-radius:50%;animation:rxsp .6s linear infinite;display:inline-block}
@keyframes rxsp{to{transform:rotate(360deg)}}
@media(max-width:1000px){.rx-kpis{grid-template-columns:repeat(3,1fr)}.rx-2,.rx-31{grid-template-columns:1fr}}
@media(max-width:560px){.rx-kpis{grid-template-columns:1fr 1fr}}
</style>
@endverbatim

<div class="rx">

  {{-- Nav --}}
  <div class="rx-nav">
    <a href="{{ route('reports.index') }}" class="on">📊 <span id="rx-n-dash">لوحة التقارير</span></a>
    <a href="{{ route('reports.table') }}">📋 <span id="rx-n-table">جدول البيانات</span></a>
    <a href="{{ route('reports.dynamic') }}">🔧 <span id="rx-n-dyn">تقرير ديناميكي</span></a>
    <a href="{{ route('reports.branch-monthly') }}">🏢 <span id="rx-n-branch">تقرير الفروع</span></a>
    <div style="flex:1"></div>
    <span id="rx-scope" style="font-size:11px;color:var(--mu)"></span>
    <button onclick="rxRefresh()" id="rx-refresh">🔄 <span id="rx-n-refresh">تحديث</span></button>
  </div>

  {{-- Smart filter --}}
  <div class="rx-filter">
    <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
      <span style="font-size:11px;font-weight:700;color:var(--mu)" id="rx-period-lbl">الفترة:</span>
      <div class="rx-chips" id="rx-period-chips">
        <span class="rx-chip on" data-p="all" onclick="rxPeriod('all',this)" id="rx-p-all">كل الفترات</span>
        <span class="rx-chip" data-p="3" onclick="rxPeriod('3',this)" id="rx-p-3">آخر 3 شهور</span>
        <span class="rx-chip" data-p="6" onclick="rxPeriod('6',this)" id="rx-p-6">آخر 6 شهور</span>
        <span class="rx-chip" data-p="12" onclick="rxPeriod('12',this)" id="rx-p-12">آخر سنة</span>
      </div>
      <div style="position:relative">
        <span class="rx-chip" onclick="rxCalToggle()" id="rx-p-custom">📅 <span id="rx-p-custom-l">فترة مخصّصة</span></span>
        <div class="rx-cal" id="rx-cal">
          <div class="rx-cal-tabs">
            <button class="rx-cal-tab on" id="rx-cal-tab-from" onclick="rxCalTab('from')"><span id="rx-cal-l-from">من</span>: <b id="rx-cal-from-v">—</b></button>
            <button class="rx-cal-tab" id="rx-cal-tab-to" onclick="rxCalTab('to')"><span id="rx-cal-l-to">إلى</span>: <b id="rx-cal-to-v">—</b></button>
          </div>
          <div class="rx-cal-nav">
            <button onclick="rxCalYear(-1)">‹</button>
            <span id="rx-cal-year">2025</span>
            <button onclick="rxCalYear(1)">›</button>
          </div>
          <div class="rx-cal-grid" id="rx-cal-grid"></div>
          <div class="rx-cal-foot">
            <button class="rx-chip" onclick="rxCalClear()" id="rx-cal-clear">مسح</button>
            <button class="rx-chip on" onclick="rxCalApply()" id="rx-cal-apply">تطبيق</button>
          </div>
        </div>
      </div>
      <div style="flex:1;min-width:140px">
        <input type="text" id="rx-search" class="form-control" placeholder="بحث: فرع / بروكر / حساب..." oninput="rxDebounce()" style="width:100%">
      </div>
      <button class="rx-chip" onclick="rxToggleAdv()" id="rx-adv-btn">⚙️ فلاتر متقدمة</button>
      <span id="rx-loading" style="display:none"><span class="rx-spin"></span></span>
    </div>
    <div class="rx-adv" id="rx-adv">
      @if(auth()->user()?->isFinanceAdmin())
      <div class="rx-fg">
        <span class="rx-fl" id="rx-fl-branch">الفرع</span>
        <select id="rx-branch" class="form-control" onchange="rxLoad()"><option value="" id="rx-o-allbr">كل الفروع</option></select>
      </div>
      @endif
      <div class="rx-fg">
        <span class="rx-fl" id="rx-fl-broker">البروكر</span>
        <select id="rx-broker" class="form-control" onchange="rxLoad()"><option value="" id="rx-o-allbk">كل البروكرات</option></select>
      </div>
      <div class="rx-fg">
        <span class="rx-fl" id="rx-fl-status">الحالة</span>
        <select id="rx-status" class="form-control" onchange="rxLoad()">
          <option value="" id="rx-o-allst">الكل</option>
          <option value="active" id="rx-o-active">عادي</option>
          <option value="modified" id="rx-o-mod">معدّلة</option>
          <option value="new_added" id="rx-o-new">مضافة جديدة</option>
        </select>
      </div>
    </div>
  </div>

  {{-- 💡 Insights --}}
  <div class="rx-insights" id="rx-insights">
    <div class="rx-ins info"><div class="rx-ins-ico">💡</div><div class="rx-ins-body"><div class="rx-ins-txt"><span class="rx-spin"></span> <span id="rx-ins-load">جارٍ تحليل البيانات...</span></div></div></div>
  </div>

  {{-- KPIs --}}
  <div class="rx-kpis">
    <div class="rx-kpi k1"><div class="rx-kpi-lbl" id="rx-k-acc-l">إجمالي الحسابات</div><div class="rx-kpi-val" id="rx-k-acc">—</div><div class="rx-kpi-sub" id="rx-k-acc-s">حساب فريد</div><div class="rx-kpi-ico">👥</div></div>
    <div class="rx-kpi k2"><div class="rx-kpi-lbl" id="rx-k-dep-l">الإيداع الأولي</div><div class="rx-kpi-val" id="rx-k-dep">—</div><div class="rx-kpi-sub" id="rx-k-dep-s">إجمالي</div><div class="rx-kpi-ico">💵</div></div>
    <div class="rx-kpi k3"><div class="rx-kpi-lbl" id="rx-k-mon-l">الإيداع الشهري</div><div class="rx-kpi-val" id="rx-k-mon">—</div><div class="rx-kpi-sub" id="rx-k-mon-s">متوقع</div><div class="rx-kpi-ico">📈</div></div>
    <div class="rx-kpi k4"><div class="rx-kpi-lbl" id="rx-k-mod-l">معدّلة</div><div class="rx-kpi-val" id="rx-k-mod">—</div><div class="rx-kpi-sub" id="rx-k-mod-s">من الإجمالي</div><div class="rx-kpi-ico">✏️</div></div>
    <div class="rx-kpi k5"><div class="rx-kpi-lbl" id="rx-k-cc-l">كروت CC</div><div class="rx-kpi-val" id="rx-k-cc">—</div><div class="rx-kpi-sub" id="rx-k-cc-s">مركز الاتصال</div><div class="rx-kpi-ico">📞</div></div>
  </div>

  {{-- Row 1: monthly + kind --}}
  <div class="rx-grid rx-31">
    <div class="rx-panel">
      <div class="rx-ph"><div class="rx-pt" id="rx-c-monthly">📅 الاتجاه الشهري</div><a href="{{ route('reports.table') }}" class="rx-chip" id="rx-goto-tbl">📋 الجدول الكامل →</a></div>
      <div class="rx-pb-lg"><canvas id="rxc-monthly"></canvas></div>
    </div>
    <div class="rx-panel">
      <div class="rx-ph"><div class="rx-pt" id="rx-c-kind">🥧 جديد / فرعي</div></div>
      <div class="rx-pb"><canvas id="rxc-kind"></canvas></div>
    </div>
  </div>

  {{-- Row 2: branches + brokers --}}
  <div class="rx-grid rx-2">
    <div class="rx-panel">
      <div class="rx-ph"><div class="rx-pt" id="rx-c-branch">🏢 مقارنة الفروع</div></div>
      <div class="rx-pb"><canvas id="rxc-branch"></canvas></div>
    </div>
    <div class="rx-panel">
      <div class="rx-ph"><div class="rx-pt" id="rx-c-broker">🥇 أفضل البروكرات</div></div>
      <div style="overflow-y:auto;max-height:268px">
        <table class="rx-tbl"><thead><tr><th>#</th><th id="rx-th-bk">البروكر</th><th id="rx-th-cnt">الحسابات</th><th id="rx-th-dep">الإيداع</th></tr></thead>
        <tbody id="rx-brokers"><tr><td colspan="4" style="text-align:center;padding:30px"><span class="rx-spin"></span></td></tr></tbody></table>
      </div>
    </div>
  </div>

  {{-- Row 3: commission + status --}}
  <div class="rx-grid rx-2">
    <div class="rx-panel"><div class="rx-ph"><div class="rx-pt" id="rx-c-comm">💰 توزيع العمولات</div></div><div class="rx-pb"><canvas id="rxc-comm"></canvas></div></div>
    <div class="rx-panel"><div class="rx-ph"><div class="rx-pt" id="rx-c-status">📊 توزيع الحالات</div></div><div class="rx-pb"><canvas id="rxc-status"></canvas></div></div>
  </div>

</div>
@endsection

@push('scripts')
<script>
/* ============ Reports v3 ============ */
const RX = {
  ar:{
    nDash:'لوحة التقارير',nTable:'جدول البيانات',nDyn:'تقرير ديناميكي',nBranch:'تقرير الفروع',nRefresh:'تحديث',
    period:'الفترة:',pAll:'كل الفترات',p3:'آخر 3 شهور',p6:'آخر 6 شهور',p12:'آخر سنة',
    pCustom:'📅 فترة مخصّصة',calFrom:'من',calTo:'إلى',calClear:'مسح',calApply:'تطبيق',
    advBtn:'⚙️ فلاتر متقدمة',flBranch:'الفرع',flBroker:'البروكر',flStatus:'الحالة',
    allBr:'كل الفروع',allBk:'كل البروكرات',allSt:'الكل',active:'عادي',mod:'معدّلة',newA:'مضافة جديدة',
    search:'بحث: فرع / بروكر / حساب...',
    kAcc:'إجمالي الحسابات',kAccS:'حساب فريد',kDep:'الإيداع الأولي',kDepS:'إجمالي',kMon:'الإيداع الشهري',kMonS:'متوقع',kMod:'معدّلة',kModS:'من الإجمالي',kCc:'كروت CC',kCcS:'مركز الاتصال',
    cMonthly:'📅 الاتجاه الشهري',cKind:'🥧 جديد / فرعي',cBranch:'🏢 مقارنة الفروع',cBroker:'🥇 أفضل البروكرات',cComm:'💰 توزيع العمولات',cStatus:'📊 توزيع الحالات',
    gotoTbl:'📋 الجدول الكامل →',thBk:'البروكر',thCnt:'الحسابات',thDep:'الإيداع',
    insLoad:'جارٍ تحليل البيانات...',noData:'لا توجد بيانات للفترة المحددة',
    tag_key:'استنتاج رئيسي',tag_pos:'إيجابي',tag_warn:'انتباه',tag_info:'معلومة',
    new:'جديد',sub:'فرعي',acct:'حساب',
    iNoData:'لا توجد بيانات لعرض استنتاجات — جرّب توسيع الفترة أو تعديل الفلاتر.',
    tbTitle:'التقارير',
  },
  en:{
    nDash:'Dashboard',nTable:'Data Table',nDyn:'Dynamic Report',nBranch:'Branch Report',nRefresh:'Refresh',
    period:'Period:',pAll:'All Time',p3:'Last 3 Months',p6:'Last 6 Months',p12:'Last Year',
    pCustom:'📅 Custom Range',calFrom:'From',calTo:'To',calClear:'Clear',calApply:'Apply',
    advBtn:'⚙️ Advanced Filters',flBranch:'Branch',flBroker:'Broker',flStatus:'Status',
    allBr:'All Branches',allBk:'All Brokers',allSt:'All',active:'Active',mod:'Modified',newA:'New Added',
    search:'Search: branch / broker / account...',
    kAcc:'Total Accounts',kAccS:'unique',kDep:'Initial Deposit',kDepS:'total',kMon:'Monthly Deposit',kMonS:'expected',kMod:'Modified',kModS:'of total',kCc:'CC Cards',kCcS:'call center',
    cMonthly:'📅 Monthly Trend',cKind:'🥧 New / Sub',cBranch:'🏢 Branch Comparison',cBroker:'🥇 Top Brokers',cComm:'💰 Commission Spread',cStatus:'📊 Status Breakdown',
    gotoTbl:'📋 Full Table →',thBk:'Broker',thCnt:'Accounts',thDep:'Deposit',
    insLoad:'Analyzing data...',noData:'No data for selected period',
    tag_key:'Key Insight',tag_pos:'Positive',tag_warn:'Attention',tag_info:'Info',
    new:'New',sub:'Sub',acct:'acct',
    iNoData:'No data to derive insights — widen the period or adjust filters.',
    tbTitle:'Reports',
  }
};
function rdL(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
function rx(k){return RX[rdL()]?.[k]??RX.ar[k]??k;}
function _t(id,v){const e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}

function rxApplyLang(){
  _t('rx-n-dash',rx('nDash'));_t('rx-n-table',rx('nTable'));_t('rx-n-dyn',rx('nDyn'));_t('rx-n-branch',rx('nBranch'));_t('rx-n-refresh',rx('nRefresh'));
  _t('rx-period-lbl',rx('period'));_t('rx-p-all',rx('pAll'));_t('rx-p-3',rx('p3'));_t('rx-p-6',rx('p6'));_t('rx-p-12',rx('p12'));
  if(!_customFrom)_t('rx-p-custom-l',rx('pCustom').replace('📅 ',''));
  _t('rx-cal-l-from',rx('calFrom'));_t('rx-cal-l-to',rx('calTo'));_t('rx-cal-clear',rx('calClear'));_t('rx-cal-apply',rx('calApply'));
  _t('rx-adv-btn',rx('advBtn'));_t('rx-fl-branch',rx('flBranch'));_t('rx-fl-broker',rx('flBroker'));_t('rx-fl-status',rx('flStatus'));
  _t('rx-o-allbr',rx('allBr'));_t('rx-o-allbk',rx('allBk'));_t('rx-o-allst',rx('allSt'));_t('rx-o-active',rx('active'));_t('rx-o-mod',rx('mod'));_t('rx-o-new',rx('newA'));
  _t('rx-k-acc-l',rx('kAcc'));_t('rx-k-acc-s',rx('kAccS'));_t('rx-k-dep-l',rx('kDep'));_t('rx-k-dep-s',rx('kDepS'));_t('rx-k-mon-l',rx('kMon'));_t('rx-k-mon-s',rx('kMonS'));_t('rx-k-mod-l',rx('kMod'));_t('rx-k-mod-s',rx('kModS'));_t('rx-k-cc-l',rx('kCc'));_t('rx-k-cc-s',rx('kCcS'));
  _t('rx-c-monthly',rx('cMonthly'));_t('rx-c-kind',rx('cKind'));_t('rx-c-branch',rx('cBranch'));_t('rx-c-broker',rx('cBroker'));_t('rx-c-comm',rx('cComm'));_t('rx-c-status',rx('cStatus'));
  _t('rx-goto-tbl',rx('gotoTbl'));_t('rx-th-bk',rx('thBk'));_t('rx-th-cnt',rx('thCnt'));_t('rx-th-dep',rx('thDep'));
  const se=document.getElementById('rx-search');if(se)se.placeholder=rx('search');
  const tb=document.querySelector('.tb-title');if(tb)tb.textContent=rx('tbTitle');
  if(_data)rxRender(_data);
}
const _rxOrig=window.applyLang;
window.applyLang=function(l){if(_rxOrig)_rxOrig(l);try{rxApplyLang();}catch(e){console.warn(e);}};

const COLORS=['#1AADBA','#22c97a','#f5a623','#7b68ee','#e05050','#3a9db5','#26d4e8','#ff6b6b','#4ecdc4','#95b8d1','#e8a838','#1a5f7a'];
let _charts={},_data=null,_timer=null,_period='all',_loading=false;
let _calYear=new Date().getFullYear(),_calTab='from',_calFrom=null,_calTo=null,_customFrom='',_customTo='';
function mk(id,cfg){if(_charts[id]){_charts[id].destroy();delete _charts[id];}const e=document.getElementById(id);if(e)_charts[id]=new Chart(e,cfg);}
const tickC=()=>'#5A80A0',gridC=()=>'rgba(255,255,255,.06)';
const MONTHS_EN=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function mLabel(o){return o?MONTHS_EN[o.m]+' '+o.y:'—';}
function mIndex(o){return o.y*12+o.m;}

function rxPeriod(p,el){_period=p;_customFrom='';_customTo='';document.getElementById('rx-cal').classList.remove('open');document.querySelectorAll('#rx-period-chips .rx-chip').forEach(c=>c.classList.remove('on'));el.classList.add('on');document.getElementById('rx-p-custom').classList.remove('on');rxLoad();}

/* ── Month-range calendar ── */
function rxCalToggle(){const c=document.getElementById('rx-cal');c.classList.toggle('open');if(c.classList.contains('open'))rxCalRender();}
function rxCalTab(w){_calTab=w;document.getElementById('rx-cal-tab-from').classList.toggle('on',w==='from');document.getElementById('rx-cal-tab-to').classList.toggle('on',w==='to');}
function rxCalYear(d){_calYear+=d;rxCalRender();}
function rxCalRender(){
  document.getElementById('rx-cal-year').textContent=_calYear;
  document.getElementById('rx-cal-from-v').textContent=mLabel(_calFrom);
  document.getElementById('rx-cal-to-v').textContent=mLabel(_calTo);
  const g=document.getElementById('rx-cal-grid');
  g.innerHTML=MONTHS_EN.map((m,i)=>{
    const o={y:_calYear,m:i},idx=mIndex(o);let cls='';
    if(_calFrom&&idx===mIndex(_calFrom))cls='f';else if(_calTo&&idx===mIndex(_calTo))cls='t';
    else if(_calFrom&&_calTo&&idx>mIndex(_calFrom)&&idx<mIndex(_calTo))cls='in';
    return `<div class="rx-cal-m ${cls}" onclick="rxCalPick(${i})">${m}</div>`;
  }).join('');
}
function rxCalPick(i){
  const o={y:_calYear,m:i};
  if(_calTab==='from'){_calFrom=o;_calTab='to';rxCalTab('to');}
  else{_calTo=o;}
  if(_calFrom&&_calTo&&mIndex(_calFrom)>mIndex(_calTo)){const t=_calFrom;_calFrom=_calTo;_calTo=t;}
  rxCalRender();
}
function rxCalClear(){_calFrom=null;_calTo=null;_calTab='from';rxCalTab('from');rxCalRender();}
function rxCalApply(){
  if(!_calFrom){return;}
  const to=_calTo||_calFrom;
  _customFrom=mLabel(_calFrom);_customTo=mLabel(to);_period='custom';
  document.querySelectorAll('#rx-period-chips .rx-chip').forEach(c=>c.classList.remove('on'));
  const cc=document.getElementById('rx-p-custom');cc.classList.add('on');
  document.getElementById('rx-p-custom-l').textContent=_customFrom+' → '+_customTo;
  document.getElementById('rx-cal').classList.remove('open');
  rxLoad();
}
document.addEventListener('click',function(e){const cal=document.getElementById('rx-cal'),btn=document.getElementById('rx-p-custom');if(cal&&cal.classList.contains('open')&&!cal.contains(e.target)&&btn&&!btn.contains(e.target))cal.classList.remove('open');});
function rxToggleAdv(){document.getElementById('rx-adv').classList.toggle('show');}
function rxDebounce(){clearTimeout(_timer);_timer=setTimeout(()=>{if(_data)rxRender(_data);},250);}
function rxRefresh(){_data=null;rxLoad();}

function rxMonthsBack(n){
  const now=new Date(),out=[];
  for(let i=0;i<n;i++){const d=new Date(now.getFullYear(),now.getMonth()-i,1);out.push(d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear());}
  return out;
}

async function rxLoad(){
  if(_loading)return;_loading=true;
  document.getElementById('rx-loading').style.display='';
  const p=new URLSearchParams();
  if(_period==='custom'){if(_customFrom)p.set('month_from',_customFrom);if(_customTo)p.set('month_to',_customTo);}
  else if(_period!=='all'){const ms=rxMonthsBack(parseInt(_period));p.set('month_from',ms[ms.length-1]);p.set('month_to',ms[0]);}
  const br=document.getElementById('rx-branch')?.value;const bk=document.getElementById('rx-broker')?.value;const st=document.getElementById('rx-status')?.value;
  if(br)p.set('branch_id',br);if(bk)p.set('broker_id',bk);if(st)p.set('status',st);
  p.set('per_page',5000);
  try{
    const r=await api('GET','/cards/report?'+p);
    if(r.success){_data=r;rxRender(r);const sc=document.getElementById('rx-scope');if(sc)sc.textContent=r.branch_scope||'';}
  }catch(e){console.error(e);}
  document.getElementById('rx-loading').style.display='none';_loading=false;
}

/* search filters the already-loaded dataset */
function rxFiltered(){
  if(!_data)return[];
  const q=(document.getElementById('rx-search')?.value||'').toLowerCase().trim();
  let d=_data.data||[];
  if(q)d=d.filter(c=>String(c.account_number).includes(q)||(c.broker?.name||'').toLowerCase().includes(q)||(c.branch?.name_ar||'').toLowerCase().includes(q)||(c.branch?.name_en||'').toLowerCase().includes(q)||(c.month||'').toLowerCase().includes(q));
  return d;
}

function rxRender(r){
  const isEn=rdL()==='en';
  const data=rxFiltered();
  const s=r.summary||{};
  const fmtN=n=>(n||0).toLocaleString('en');
  const fmtK=n=>{if(!n||isNaN(n))return '—';if(n>=1e6)return '$'+(n/1e6).toFixed(1)+'M';if(n>=1000)return '$'+(n/1000).toFixed(0)+'K';return '$'+Math.round(n);};
  const uniq=r.unique_accounts??r.count??data.length;
  const newC=data.filter(c=>c.account_kind==='new').length, subC=data.filter(c=>c.account_kind==='sub').length;
  const modC=data.filter(c=>c.status==='modified').length, ccC=data.filter(c=>!!c.cc_status).length;
  const totDep=data.reduce((a,c)=>a+parseFloat(c.initial_deposit||0),0);
  const totMon=data.reduce((a,c)=>a+parseFloat(c.monthly_deposit||0),0);

  /* KPIs */
  _t('rx-k-acc',fmtN(data.length?data.length:uniq));_t('rx-k-dep',fmtK(totDep));_t('rx-k-mon',fmtK(totMon));
  _t('rx-k-mod',fmtN(modC));_t('rx-k-cc',fmtN(ccC));

  /* ===== INSIGHTS ===== */
  rxInsights(data,{newC,subC,modC,ccC,totDep,totMon},isEn);

  /* Monthly */
  const mm={};data.forEach(c=>{const m=c.month||'—';if(!mm[m])mm[m]={c:0,d:0};mm[m].c++;mm[m].d+=parseFloat(c.initial_deposit||0);});
  const months=Object.keys(mm).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  mk('rxc-monthly',{type:'bar',data:{labels:months,datasets:[
    {label:isEn?'Accounts':'الحسابات',data:months.map(m=>mm[m].c),backgroundColor:'#1AADBAcc',borderColor:'#1AADBA',borderWidth:2,borderRadius:6,yAxisID:'y'},
    {label:isEn?'Deposit (K)':'الإيداع (ألف)',data:months.map(m=>Math.round(mm[m].d/1000)),type:'line',borderColor:'#22c97a',backgroundColor:'rgba(34,201,122,.12)',borderWidth:2,tension:.4,fill:true,pointBackgroundColor:'#22c97a',yAxisID:'y2'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:true,labels:{color:tickC(),font:{size:10},boxWidth:12}}},
    scales:{x:{ticks:{color:tickC(),font:{size:9},maxRotation:45},grid:{color:gridC()}},y:{position:'right',ticks:{color:tickC(),font:{size:9}},grid:{color:gridC()},beginAtZero:true},y2:{position:'left',ticks:{color:'#22c97a',font:{size:9}},grid:{display:false},beginAtZero:true}}}});

  /* Kind donut */
  mk('rxc-kind',{type:'doughnut',data:{labels:[rx('new'),rx('sub')],datasets:[{data:[newC,subC],backgroundColor:['#22c97acc','#1AADBAcc'],borderWidth:3,borderColor:'var(--card-bg)'}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'68%',plugins:{legend:{display:true,position:'bottom',labels:{color:tickC(),font:{size:11},boxWidth:12,padding:10}},tooltip:{callbacks:{label:c=>`${c.label}: ${c.raw} (${data.length?Math.round(c.raw/data.length*100):0}%)`}}}}});

  /* Branch bars */
  const bm={};data.forEach(c=>{const n=(isEn?c.branch?.name_en||c.branch?.name_ar:c.branch?.name_ar)||(isEn?'Unassigned':'غير محدد');bm[n]=(bm[n]||0)+1;});
  const bl=Object.keys(bm).sort((a,b)=>bm[b]-bm[a]),bv=bl.map(b=>bm[b]);
  mk('rxc-branch',{type:'bar',data:{labels:bl,datasets:[{data:bv,backgroundColor:bl.map((_,i)=>COLORS[i%COLORS.length]+'cc'),borderColor:bl.map((_,i)=>COLORS[i%COLORS.length]),borderWidth:2,borderRadius:6}]},
    options:{responsive:true,maintainAspectRatio:false,indexAxis:bl.length>5?'y':'x',plugins:{legend:{display:false},tooltip:{callbacks:{label:c=>c.raw+' '+(isEn?'accounts':'حساب')}}},scales:{x:{ticks:{color:tickC(),font:{size:9}},grid:{color:gridC()}},y:{ticks:{color:tickC(),font:{size:9}},grid:{color:gridC()},beginAtZero:true}}}});

  /* Top brokers */
  const km={};data.forEach(c=>{const n=c.broker?.name||(isEn?'Unassigned':'غير محدد');if(!km[n])km[n]={c:0,d:0};km[n].c++;km[n].d+=parseFloat(c.initial_deposit||0);});
  const top=Object.entries(km).sort((a,b)=>b[1].c-a[1].c).slice(0,10),md=['🥇','🥈','🥉'],maxc=top.length?top[0][1].c:1;
  document.getElementById('rx-brokers').innerHTML=top.length?top.map(([n,v],i)=>`<tr><td style="font-size:15px;text-align:center">${md[i]||i+1}</td><td style="font-weight:700">${esc(n)}</td><td><span style="font-weight:800;color:var(--pri2)">${v.c}</span><div class="rx-bar"><div class="rx-bar-fill" style="width:${Math.round(v.c/maxc*100)}%"></div></div></td><td class="mono" style="color:var(--gr);font-size:11px">${v.d>=1000?'$'+(v.d/1000).toFixed(1)+'K':'$'+Math.round(v.d)}</td></tr>`).join(''):`<tr><td colspan="4" style="text-align:center;padding:30px;color:var(--mu)">${rx('noData')}</td></tr>`;

  /* Commission */
  const cb={low:0,mid:0,high:0};data.forEach(c=>{const v=parseFloat(c.broker_commission||0);if(v>5)cb.high++;else if(v>2)cb.mid++;else cb.low++;});
  mk('rxc-comm',{type:'doughnut',data:{labels:[isEn?'Low ≤$2':'منخفضة ≤$2',isEn?'Mid $2-5':'متوسطة $2-5',isEn?'High >$5':'عالية >$5'],datasets:[{data:[cb.low,cb.mid,cb.high],backgroundColor:['#22c97acc','#f5a623cc','#e05050cc'],borderWidth:3,borderColor:'var(--card-bg)'}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:true,position:'bottom',labels:{color:tickC(),font:{size:10},boxWidth:12,padding:8}}}}});

  /* Status */
  const act=data.filter(c=>!c.status||c.status==='active').length,nad=data.filter(c=>c.status==='new_added').length;
  mk('rxc-status',{type:'doughnut',data:{labels:[isEn?'Active':'عادي',isEn?'Modified':'معدّل',isEn?'New Added':'مضاف'],datasets:[{data:[act,modC,nad],backgroundColor:['#1AADBAcc','#f5a623cc','#22c97acc'],borderWidth:3,borderColor:'var(--card-bg)'}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{display:true,position:'bottom',labels:{color:tickC(),font:{size:10},boxWidth:12,padding:8}}}}});
}

/* ====== INSIGHTS ENGINE — derives plain-language conclusions ====== */
function rxInsights(data,m,isEn){
  const box=document.getElementById('rx-insights');
  if(!data.length){box.innerHTML=`<div class="rx-ins info"><div class="rx-ins-ico">ℹ️</div><div class="rx-ins-body"><div class="rx-ins-txt">${rx('iNoData')}</div></div></div>`;return;}
  const ins=[];
  const total=data.length;
  const S=v=>`<span class="rx-ins-strong">${v}</span>`;

  /* Branch concentration */
  const bm={};data.forEach(c=>{const n=(isEn?c.branch?.name_en||c.branch?.name_ar:c.branch?.name_ar)||'—';bm[n]=(bm[n]||0)+1;});
  const br=Object.entries(bm).sort((a,b)=>b[1]-a[1]);
  if(br.length){const[tn,tc]=br[0],pct=Math.round(tc/total*100);
    ins.push({t:'highlight',tag:rx('tag_key'),i:'🏢',x:isEn?`Branch ${S(tn)} leads with ${S(pct+'%')} of accounts (${tc})`:`فرع ${S(tn)} يتصدّر بـ ${S(pct+'%')} من الحسابات (${tc})`});
    if(br.length>=2&&pct>55)ins.push({t:'warning',tag:rx('tag_warn'),i:'⚠️',x:isEn?`High concentration — over half the accounts sit in one branch. Consider balancing.`:`تركّز عالٍ — أكثر من نصف الحسابات في فرع واحد. يُفضّل التوازن.`});
  }
  /* Top broker */
  const km={};data.forEach(c=>{const n=c.broker?.name;if(n)km[n]=(km[n]||0)+1;});
  const ke=Object.entries(km).sort((a,b)=>b[1]-a[1]);
  if(ke.length){const[bn,bc]=ke[0],pct=Math.round(bc/total*100);
    ins.push({t:'positive',tag:rx('tag_pos'),i:'🥇',x:isEn?`Top broker ${S(bn)} handles ${S(bc)} accounts (${pct}%)`:`أكثر بروكر نشاطاً ${S(bn)} يدير ${S(bc)} حساب (${pct}%)`});
  }
  /* NEW vs SUB */
  if(m.newC+m.subC>0){const np=Math.round(m.newC/(m.newC+m.subC)*100);
    ins.push({t:np>=50?'positive':'info',tag:np>=50?rx('tag_pos'):rx('tag_info'),i:'📈',x:isEn?`${S(np+'%')} are brand-new accounts vs ${100-np}% sub-accounts${np>=60?' — strong fresh growth':''}`:`${S(np+'%')} حسابات جديدة كلياً مقابل ${100-np}% فرعية${np>=60?' — نمو جديد قوي':''}`});
  }
  /* Modified */
  if(m.modC>0){const mp=Math.round(m.modC/total*100);
    ins.push({t:mp>20?'warning':'info',tag:mp>20?rx('tag_warn'):rx('tag_info'),i:'✏️',x:isEn?`${S(m.modC)} modified accounts (${mp}%)${mp>20?' — high revision activity, worth reviewing':''}`:`${S(m.modC)} حساب معدّل (${mp}%)${mp>20?' — نشاط تعديل مرتفع، يستحق المراجعة':''}`});
  }
  /* Monthly trend */
  const mo={};data.forEach(c=>{mo[c.month]=(mo[c.month]||0)+1;});
  const mk2=Object.keys(mo).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  if(mk2.length>=2){const last=mo[mk2[mk2.length-1]],prev=mo[mk2[mk2.length-2]],ch=prev?Math.round((last-prev)/prev*100):0;
    ins.push({t:ch>=0?'positive':'warning',tag:ch>=0?rx('tag_pos'):rx('tag_warn'),i:ch>=0?'⬆️':'⬇️',x:isEn?`Latest month ${ch>=0?'grew':'dropped'} ${S(Math.abs(ch)+'%')} vs previous (${last} vs ${prev})`:`الشهر الأخير ${ch>=0?'ارتفع':'انخفض'} ${S(Math.abs(ch)+'%')} عن السابق (${last} مقابل ${prev})`});
  }
  /* Avg deposit */
  const avg=total?m.totDep/total:0;
  ins.push({t:'info',tag:rx('tag_info'),i:'💵',x:isEn?`Average initial deposit is ${S('$'+Math.round(avg).toLocaleString('en'))} per account`:`متوسط الإيداع الأولي ${S('$'+Math.round(avg).toLocaleString('en'))} لكل حساب`});
  /* CC */
  if(m.ccC>0){const cp=Math.round(m.ccC/total*100);
    ins.push({t:'info',tag:rx('tag_info'),i:'📞',x:isEn?`${S(cp+'%')} of accounts originated from the Call Center`:`${S(cp+'%')} من الحسابات جاءت عبر مركز الاتصال`});
  }
  /* High commission risk */
  const hi=data.filter(c=>parseFloat(c.broker_commission||0)>5).length;
  if(hi>0){const hp=Math.round(hi/total*100);
    ins.push({t:'warning',tag:rx('tag_warn'),i:'💰',x:isEn?`${S(hi)} accounts (${hp}%) pay broker commission above $5 — review profitability`:`${S(hi)} حساب (${hp}%) بعمولة بروكر تفوق $5 — راجع الربحية`});
  }

  box.innerHTML=ins.map(o=>`<div class="rx-ins ${o.t}"><div class="rx-ins-ico">${o.i}</div><div class="rx-ins-body"><div class="rx-ins-tag">${o.tag}</div><div class="rx-ins-txt">${o.x}</div></div></div>`).join('');
}

/* init filters */
async function rxInit(){
  const [e,b]=await Promise.all([api('GET','/employees?status=approved'),api('GET','/branches')]);
  if(e.success){const s=document.getElementById('rx-broker');if(s)e.data.filter(x=>x.role==='broker').forEach(x=>{const o=document.createElement('option');o.value=x.id;o.textContent=x.name;s.appendChild(o);});}
  if(b.success){const s=document.getElementById('rx-branch');if(s)b.data.forEach(x=>{const o=document.createElement('option');o.value=x.id;o.textContent=rdL()==='en'?(x.name_en||x.name_ar):x.name_ar;s.appendChild(o);});}
}

rxApplyLang();
rxInit().then(()=>rxLoad());
</script>
@endpush
