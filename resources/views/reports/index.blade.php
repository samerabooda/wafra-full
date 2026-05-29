@extends('layouts.app')
@section('title','التقارير')
@section('page-title','التقارير الديناميكية')

@push('styles')
<style>
/* ── Report-specific styles ─────────────────────────────────── */
.rpt-tab-bar { display:flex;gap:3px;background:var(--inp-bg);border-radius:10px;padding:4px;margin-bottom:16px;overflow-x:auto;scrollbar-width:none }
.rpt-tab-bar::-webkit-scrollbar{display:none}
.rpt-tab-btn { flex:1;min-width:90px;border-radius:7px;padding:8px 10px;font-size:11px;font-weight:600;border:none;cursor:pointer;background:transparent;color:var(--mu);transition:all .18s;white-space:nowrap }
.rpt-tab-btn.active { background:var(--bg3);color:var(--pri2);box-shadow:0 1px 6px rgba(0,0,0,.2) }
.rpt-tab-btn:hover:not(.active) { background:rgba(46,134,171,.08);color:var(--tx) }

/* KPI grid */
.kpi-grid { display:grid;gap:10px;margin-bottom:14px }
.kpi-grid-4 { grid-template-columns:repeat(4,1fr) }
.kpi-grid-3 { grid-template-columns:repeat(3,1fr) }
.kpi-grid-6 { grid-template-columns:repeat(6,1fr) }
.kpi-card { background:var(--bg2);border:1px solid var(--brd1);border-radius:10px;padding:16px 12px;text-align:center }
.kpi-label { font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-bottom:6px }
.kpi-value { font-size:1.7rem;font-weight:900;line-height:1 }
.kpi-sub   { font-size:10px;color:var(--mu);margin-top:4px }

/* Comm scale */
.comm-high { color:#e05050!important;font-weight:700 }
.comm-mid  { color:#f5a623!important;font-weight:700 }
.comm-low  { color:#22c97a!important;font-weight:700 }

/* Rank bar rows */
.rank-row { display:flex;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--brd1) }
.rank-row:last-child { border-bottom:none }
.rank-bar-wrap { flex:1;height:5px;background:var(--brd1);border-radius:3px;overflow:hidden }
.rank-bar { height:5px;border-radius:3px;transition:width .4s }

/* CC pill */
.cc-pill { display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:20px;font-size:10px;font-weight:700;background:rgba(123,104,238,.12);color:#7b68ee;border:1px solid rgba(123,104,238,.3) }

/* Commission histogram bars */
.hist-bar-row { display:flex;align-items:center;gap:8px;margin-bottom:6px }
.hist-label   { width:70px;font-size:10px;color:var(--mu);text-align:left }
.hist-bar-bg  { flex:1;height:20px;background:var(--brd1);border-radius:4px;overflow:hidden;position:relative }
.hist-bar-fill{ height:100%;border-radius:4px;display:flex;align-items:center;padding:0 6px;font-size:9px;font-weight:700;color:#fff;transition:width .4s }
.hist-count   { width:36px;font-size:10px;font-weight:700;color:var(--tx);text-align:right }

/* Branch comparison cards */
.branch-cmp { display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:10px;margin-bottom:14px }
.branch-card { background:var(--bg2);border:1px solid var(--brd1);border-radius:10px;padding:12px }
.branch-name { font-size:12px;font-weight:800;color:var(--tx);margin-bottom:8px;padding-bottom:6px;border-bottom:1px solid var(--brd1) }
.branch-stat { display:flex;justify-content:space-between;font-size:11px;padding:3px 0 }
.branch-stat-label { color:var(--mu) }

/* Chart canvas wrappers */
.chart-wrap { height:220px;padding:12px;position:relative }
.chart-wrap-sm { height:170px;padding:10px;position:relative }
.chart-wrap-lg { height:280px;padding:12px;position:relative }

/* Table mini inside report */
.rpt-mini-table { width:100%;border-collapse:collapse;font-size:11px }
.rpt-mini-table th { padding:6px 8px;color:var(--mu);font-size:9px;text-transform:uppercase;letter-spacing:.4px;border-bottom:1px solid var(--brd1);text-align:right }
.rpt-mini-table td { padding:6px 8px;border-bottom:1px solid rgba(255,255,255,.04) }
.rpt-mini-table tr:last-child td { border-bottom:none }
.rpt-mini-table tr:hover td { background:rgba(46,134,171,.04) }

/* Color legend */
.color-legend { display:flex;gap:12px;flex-wrap:wrap;font-size:10px;color:var(--mu);padding:8px 0 }
.color-dot { display:inline-block;width:9px;height:9px;border-radius:50%;margin-left:4px }

.filter-label { font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:5px }

@media(max-width:768px){
  .kpi-grid-4,.kpi-grid-6{grid-template-columns:repeat(2,1fr)}
  .kpi-grid-3{grid-template-columns:repeat(2,1fr)}
}
</style>
@endpush

@section('content')

<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- Reports Sidebar Nav --}}
  <div id="rnav-sidebar" style="width:220px;flex-shrink:0;background:var(--bg2);border-left:1px solid var(--brd1);display:flex;flex-direction:column;padding:10px 0;">
    <div style="padding:12px 16px 14px;border-bottom:1px solid var(--brd1);margin-bottom:8px">
      <div id="rnav-hdr" style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px">📈 التقارير</div>
    </div>
    <a href="{{ route('reports.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:rgba(26,173,186,.15);border:1px solid rgba(26,173,186,.25);">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.25);border:1px solid rgba(26,173,186,.5);">📊</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--pri2);white-space:nowrap" id="rnav-lbl-monthly">التقارير الشهرية</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-monthly">تحليل شامل بالرسوم البيانية</div>
      </div>
    </a>
    <a href="{{ route('reports.dynamic') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);">🔧</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap" id="rnav-lbl-dynamic">تقرير ديناميكي</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-dynamic">بناء تقرير مخصص</div>
      </div>
      <span style="font-size:9px;padding:2px 7px;border-radius:10px;background:rgba(34,201,122,.2);color:var(--gr);font-weight:700;border:1px solid rgba(34,201,122,.3)" id="rnav-badge-new">جديد</span>
    </a>
    <a href="{{ route('reports.branch-monthly') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.2);">🏢</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap">تقرير شهري للفروع</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px">فرع + شهر محدد</div>
      </div>
    </a>
    <div style="flex:1"></div>
    <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
      <div id="rnav-footer" style="font-size:10px;color:var(--mu);line-height:1.6"></div>
    </div>
  </div>

  {{-- Content Panel --}}
  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

{{-- ══════════════ TAB BAR ══════════════ --}}
<div class="rpt-tab-bar" id="rpt-tab-bar">
  <button class="rpt-tab-btn active" id="tbn-table"    onclick="switchTab('table')">📋 جدول البيانات</button>
  <button class="rpt-tab-btn"        id="tbn-dash"     onclick="switchTab('dash')">📊 Dashboard</button>
  <button class="rpt-tab-btn"        id="tbn-brokers"  onclick="switchTab('brokers')">🏦 البروكرات</button>
  <button class="rpt-tab-btn"        id="tbn-branches" onclick="switchTab('branches')">🏢 الفروع</button>
  <button class="rpt-tab-btn"        id="tbn-trends"   onclick="switchTab('trends')">📈 الاتجاهات</button>
  <button class="rpt-tab-btn"        id="tbn-commissions" onclick="switchTab('commissions')">💰 تحليل العمولات</button>
  <button class="rpt-tab-btn"        id="tbn-cc"       onclick="switchTab('cc')">📞 كروت CC</button>
</div>

{{-- ══════════════ SHARED FILTER BAR ══════════════ --}}
<div class="panel" style="padding:14px 16px;margin-bottom:14px" id="shared-filters">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <div class="filter-label" id="rfl-from">من شهر</div>
      <select id="rf-from" class="form-control" style="min-width:120px"><option value="">—</option></select>
    </div>
    <div>
      <div class="filter-label" id="rfl-to">إلى شهر</div>
      <select id="rf-to" class="form-control" style="min-width:120px"><option value="">—</option></select>
    </div>
    <div>
      <div class="filter-label" id="rfl-broker">البروكر</div>
      <select id="rf-broker" class="form-control" style="min-width:140px"><option value="" id="rfl-all-brokers">الكل</option></select>
    </div>
    @if(auth()->user()?->isFinanceAdmin())
    <div>
      <div class="filter-label" id="rfl-branch">الفرع</div>
      <select id="rf-branch" class="form-control" style="min-width:140px"><option value="" id="rfl-all-branches">كل الفروع</option></select>
    </div>
    @endif
    <div>
      <div class="filter-label" id="rfl-status">الحالة</div>
      <select id="rf-status" class="form-control">
        <option value="" id="rfl-status-all">الكل</option>
        <option value="modified" id="rfl-status-mod">معدّلة فقط</option>
        <option value="new_added" id="rfl-status-new">مضافة جديدة فقط</option>
        <option value="active" id="rfl-status-act">عادي فقط</option>
      </select>
    </div>
    <div>
      <div class="filter-label" id="rfl-kind">النوع</div>
      <select id="rf-kind" class="form-control">
        <option value="" id="rfl-kind-all">الكل</option>
        <option value="new" id="rfl-kind-new">جديد</option>
        <option value="sub" id="rfl-kind-sub">فرعي</option>
      </select>
    </div>
    <div>
      <div class="filter-label" id="rfl-source">المصدر</div>
      <select id="rf-source" class="form-control">
        <option value="" id="rfl-src-all">الكل</option>
        <option value="regular" id="rfl-src-reg">عادي</option>
        <option value="cc" id="rfl-src-cc">CC فقط</option>
      </select>
    </div>
    <div>
      <div class="filter-label" id="rfl-min">حد أدنى $</div>
      <input type="number" id="rf-min" class="form-control" style="width:80px" value="0" min="0">
    </div>
    <button class="btn btn-primary" id="rpt-gen-btn" onclick="generateReport()">⚡ توليد التقرير</button>
    <button class="btn btn-ghost" id="rpt-clr-btn" onclick="clearRptFilters()">✕ مسح</button>
  </div>
</div>

{{-- ══════════════ TAB: TABLE ══════════════ --}}
<div id="tab-table">

  <!-- Empty state -->
  <div id="tbl-empty" style="text-align:center;padding:60px 20px;color:var(--mu)">
    <div style="font-size:48px;margin-bottom:12px">📋</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">لم يتم توليد التقرير بعد</div>
    <div style="font-size:12px">اضغط ⚡ توليد التقرير لعرض البيانات</div>
  </div>

  <div id="rpt-table-wrap" style="display:none">

    <!-- Summary KPIs -->
    <div class="kpi-grid kpi-grid-4" style="margin-bottom:14px" id="tbl-kpis">
      <div class="kpi-card">
        <div class="kpi-label">إجمالي الحسابات</div>
        <div class="kpi-value" style="color:var(--pri2)" id="t-k-total">—</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">إيداع أولي</div>
        <div class="kpi-value" style="color:var(--gr)" id="t-k-dep">—</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">إيداع شهري</div>
        <div class="kpi-value" style="color:#3a9db5" id="t-k-mon">—</div>
      </div>
      <div class="kpi-card">
        <div class="kpi-label">معدّلة / مضافة</div>
        <div class="kpi-value" style="color:var(--or)" id="t-k-mod">—</div>
        <div class="kpi-sub" id="t-k-new-sub"></div>
      </div>
    </div>

    <div class="panel">
      <div class="panel-header">
        <div class="panel-title">📋 نتائج التقرير <span id="rpt-count" style="font-size:11px;color:var(--mu)"></span></div>
        <div style="display:flex;gap:6px">
          <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportRptExcel()">📗 Excel</button>
          <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)" onclick="exportRptPdf()">📄 PDF</button>
          <button class="btn btn-sm" style="background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.25);color:var(--pri2)" onclick="window.print()">🖨️ طباعة</button>
        </div>
      </div>

      <!-- Color legend -->
      <div style="padding:6px 16px;border-bottom:1px solid var(--brd1)">
        <div class="color-legend">
          <span><span class="color-dot" style="background:#7b68ee"></span>CC</span>
          <span><span class="color-dot" style="background:#f5a623"></span>معدّل</span>
          <span><span class="color-dot" style="background:#22c97a"></span>جديد</span>
          <span><span class="color-dot" style="background:#e05050"></span>عمولة عالية (&gt;5$)</span>
          <span><span class="color-dot" style="background:#f5a623"></span>عمولة متوسطة (2-5$)</span>
          <span><span class="color-dot" style="background:#22c97a"></span>عمولة منخفضة (&lt;2$)</span>
        </div>
      </div>

      <div class="table-scroll">
        <table class="data-table">
          <thead>
            <tr>
              <th>#</th><th>رقم الحساب</th><th>المصدر</th>
              <th>البروكر / ع.بروكر</th>
              <th>مسوّق داخلي / عمولة</th>
              <th>خارجي 1 / عمولة</th>
              <th>خارجي 2 / عمولة</th>
              <th>إجمالي ع.</th>
              <th>إيداع أولي</th><th>إيداع شهري</th>
              <th>النوع</th><th>الشهر</th><th>الفرع</th><th>الحالة</th>
            </tr>
          </thead>
          <tbody id="rpt-tbody"></tbody>
          <tfoot id="rpt-tfoot"></tfoot>
        </table>
      </div>
    </div>
  </div>
</div>

{{-- ══════════════ TAB: DASHBOARD ══════════════ --}}
<div id="tab-dash" style="display:none">

  <!-- Quick filter -->
  <div class="panel" style="padding:10px 16px;margin-bottom:12px;background:rgba(46,134,171,.04);border-color:rgba(46,134,171,.15)">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <span style="font-size:10px;color:var(--mu);font-weight:700">🔍 فلتر سريع:</span>
      <select id="db-branch" class="form-control" style="min-width:130px" onchange="buildDashboard()"><option value="">كل الفروع</option></select>
      <select id="db-broker" class="form-control" style="min-width:130px" onchange="buildDashboard()"><option value="">كل البروكرات</option></select>
      <select id="db-marketer" class="form-control" style="min-width:130px" onchange="buildDashboard()"><option value="">كل المسوّقين</option></select>
      <select id="db-period" class="form-control" style="min-width:110px" onchange="buildDashboard()">
        <option value="all">كل الفترات</option>
        <option value="3">آخر 3 أشهر</option>
        <option value="6">آخر 6 أشهر</option>
        <option value="12">آخر 12 شهر</option>
      </select>
      <button class="btn btn-ghost btn-sm" onclick="clearDashFilters()">✕ مسح</button>
    </div>
  </div>

  <!-- KPI Row -->
  <div class="kpi-grid kpi-grid-6" style="margin-bottom:12px">
    <div class="kpi-card"><div class="kpi-label">إجمالي الحسابات</div><div class="kpi-value" style="color:var(--pri2)" id="db-k-total">—</div></div>
    <div class="kpi-card"><div class="kpi-label">إيداع أولي</div><div class="kpi-value" style="color:var(--gr)" id="db-k-dep">—</div></div>
    <div class="kpi-card"><div class="kpi-label">إيداع شهري</div><div class="kpi-value" style="color:#3a9db5" id="db-k-mon">—</div></div>
    <div class="kpi-card"><div class="kpi-label">حسابات NEW</div><div class="kpi-value" style="color:var(--gr)" id="db-k-new">—</div></div>
    <div class="kpi-card"><div class="kpi-label">حسابات SUB</div><div class="kpi-value" style="color:var(--pri2)" id="db-k-sub">—</div></div>
    <div class="kpi-card"><div class="kpi-label">كروت CC</div><div class="kpi-value" style="color:#7b68ee" id="db-k-cc">—</div></div>
  </div>

  <!-- Row 2: Top Broker / Top Marketer / Kind Donut -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥇 أفضل البروكرات</div></div>
      <div style="padding:10px" id="db-top-broker"></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥈 أفضل المسوّقين</div></div>
      <div style="padding:10px" id="db-top-marketer"></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥧 New / Sub</div></div>
      <div class="chart-wrap-sm"><canvas id="db-chart-kind"></canvas></div>
    </div>
  </div>

  <!-- Row 3: Broker counts / Monthly bar -->
  <div style="display:grid;grid-template-columns:3fr 2fr;gap:12px;margin-bottom:12px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 عدد الحسابات بالبروكر</div></div>
      <div class="chart-wrap"><canvas id="db-chart-broker-cnt"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥧 توزيع الإيداع بالبروكر</div></div>
      <div class="chart-wrap"><canvas id="db-chart-broker-dep"></canvas></div>
    </div>
  </div>

  <!-- Row 4: Monthly + marketer table -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📅 الحسابات شهرياً</div></div>
      <div class="chart-wrap"><canvas id="db-chart-monthly"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📋 أداء المسوّقين</div></div>
      <div style="max-height:250px;overflow-y:auto" id="db-marketer-table">
        <div style="color:var(--mu);font-size:12px;text-align:center;padding:20px">لا توجد بيانات بعد</div>
      </div>
    </div>
  </div>

  <div id="db-empty" style="text-align:center;padding:60px 20px;color:var(--mu);display:none">
    <div style="font-size:48px;margin-bottom:12px">📊</div>
    <div style="font-size:15px;font-weight:700;margin-bottom:6px">الداشبورد جاهز</div>
    <div style="font-size:12px">اضغط ⚡ توليد التقرير لتحميل البيانات</div>
  </div>
</div>

{{-- ══════════════ TAB: BROKERS ══════════════ --}}
<div id="tab-brokers" style="display:none">

  <!-- KPIs -->
  <div class="kpi-grid kpi-grid-4" style="margin-bottom:14px">
    <div class="kpi-card"><div class="kpi-label">عدد البروكرات النشطين</div><div class="kpi-value" style="color:var(--pri2)" id="br-k-count">—</div></div>
    <div class="kpi-card"><div class="kpi-label">متوسط حسابات / بروكر</div><div class="kpi-value" style="color:#3a9db5" id="br-k-avg-acc">—</div></div>
    <div class="kpi-card"><div class="kpi-label">أعلى إيداع بروكر واحد</div><div class="kpi-value" style="color:var(--gr)" id="br-k-top-dep">—</div></div>
    <div class="kpi-card"><div class="kpi-label">متوسط عمولة البروكر</div><div class="kpi-value" style="color:var(--or)" id="br-k-avg-comm">—</div><div class="kpi-sub">$/lot</div></div>
  </div>

  <div style="display:grid;grid-template-columns:3fr 2fr;gap:12px;margin-bottom:14px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 أداء البروكرات — الحسابات والإيداع</div></div>
      <div class="chart-wrap-lg"><canvas id="br-chart-bar"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">💰 متوسط العمولة بالبروكر</div></div>
      <div class="chart-wrap-lg"><canvas id="br-chart-comm"></canvas></div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">🏦 جدول أداء البروكرات الكامل</div>
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportBrokersExcel()">📗 تصدير Excel</button>
    </div>
    <div class="table-scroll">
      <table class="data-table rpt-mini-table" id="br-table">
        <thead>
          <tr>
            <th>#</th><th>البروكر</th><th>عدد الحسابات</th>
            <th>NEW</th><th>SUB</th>
            <th>إيداع أولي</th><th>إيداع شهري</th>
            <th>متوسط ع. بروكر</th><th>أدنى ع.</th><th>أعلى ع.</th>
            <th>عدد CC</th><th>النسبة من الكل</th>
          </tr>
        </thead>
        <tbody id="br-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══════════════ TAB: BRANCHES ══════════════ --}}
<div id="tab-branches" style="display:none">

  <!-- KPIs -->
  <div class="kpi-grid kpi-grid-4" style="margin-bottom:14px">
    <div class="kpi-card"><div class="kpi-label">عدد الفروع النشطة</div><div class="kpi-value" style="color:var(--pri2)" id="bn-k-count">—</div></div>
    <div class="kpi-card"><div class="kpi-label">الفرع الأعلى إيداعاً</div><div class="kpi-value" style="color:var(--gr);font-size:1.1rem" id="bn-k-top">—</div></div>
    <div class="kpi-card"><div class="kpi-label">متوسط حسابات / فرع</div><div class="kpi-value" style="color:#3a9db5" id="bn-k-avg">—</div></div>
    <div class="kpi-card"><div class="kpi-label">إجمالي CC من الفروع</div><div class="kpi-value" style="color:#7b68ee" id="bn-k-cc">—</div></div>
  </div>

  <!-- Branch cards -->
  <div class="branch-cmp" id="bn-cards"></div>

  <!-- Chart + table -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 مقارنة الفروع — الحسابات</div></div>
      <div class="chart-wrap"><canvas id="bn-chart-acc"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">💵 مقارنة الفروع — الإيداع</div></div>
      <div class="chart-wrap"><canvas id="bn-chart-dep"></canvas></div>
    </div>
  </div>

  <div class="panel">
    <div class="panel-header"><div class="panel-title">🏢 جدول مقارنة الفروع</div></div>
    <div class="table-scroll">
      <table class="data-table rpt-mini-table">
        <thead>
          <tr>
            <th>#</th><th>الفرع</th><th>إجمالي الحسابات</th>
            <th>NEW</th><th>SUB</th>
            <th>معدّلة</th><th>CC وارد</th>
            <th>إيداع أولي</th><th>إيداع شهري</th>
            <th>متوسط ع. بروكر</th>
          </tr>
        </thead>
        <tbody id="bn-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══════════════ TAB: TRENDS ══════════════ --}}
<div id="tab-trends" style="display:none">

  <!-- KPIs -->
  <div class="kpi-grid kpi-grid-4" style="margin-bottom:14px">
    <div class="kpi-card"><div class="kpi-label">أعلى شهر (حسابات)</div><div class="kpi-value" style="color:var(--pri2);font-size:1.1rem" id="tr-k-peak-month">—</div></div>
    <div class="kpi-card"><div class="kpi-label">أعلى شهر (إيداع)</div><div class="kpi-value" style="color:var(--gr);font-size:1.1rem" id="tr-k-peak-dep">—</div></div>
    <div class="kpi-card"><div class="kpi-label">متوسط حسابات / شهر</div><div class="kpi-value" style="color:#3a9db5" id="tr-k-avg-month">—</div></div>
    <div class="kpi-card"><div class="kpi-label">معدل النمو (آخر شهرين)</div><div class="kpi-value" style="color:var(--or)" id="tr-k-growth">—</div><div class="kpi-sub">%</div></div>
  </div>

  <!-- Line chart -->
  <div class="panel" style="margin-bottom:14px">
    <div class="panel-header"><div class="panel-title">📈 منحنى الإيداع الأولي الشهري</div></div>
    <div class="chart-wrap-lg"><canvas id="tr-line-dep"></canvas></div>
  </div>

  <!-- Two charts side by side -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 عدد الحسابات شهرياً</div></div>
      <div class="chart-wrap"><canvas id="tr-bar-cnt"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🔄 NEW vs SUB شهرياً</div></div>
      <div class="chart-wrap"><canvas id="tr-bar-kind"></canvas></div>
    </div>
  </div>

  <!-- Stacked area: modified vs normal -->
  <div class="panel" style="margin-bottom:14px">
    <div class="panel-header"><div class="panel-title">🟡 معدّلة مقابل عادية — شهرياً</div></div>
    <div class="chart-wrap"><canvas id="tr-line-mod"></canvas></div>
  </div>

  <!-- Monthly details table -->
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📅 جدول التفاصيل الشهرية</div>
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportTrendsExcel()">📗 Excel</button>
    </div>
    <div class="table-scroll">
      <table class="data-table rpt-mini-table">
        <thead>
          <tr><th>الشهر</th><th>إجمالي حسابات</th><th>NEW</th><th>SUB</th><th>معدّلة</th><th>مضافة جديدة</th><th>CC</th><th>إيداع أولي</th><th>إيداع شهري</th><th>متوسط ع.</th></tr>
        </thead>
        <tbody id="tr-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══════════════ TAB: COMMISSIONS ══════════════ --}}
<div id="tab-commissions" style="display:none">

  <!-- KPIs -->
  <div class="kpi-grid kpi-grid-4" style="margin-bottom:14px">
    <div class="kpi-card"><div class="kpi-label">متوسط عمولة بروكر</div><div class="kpi-value comm-mid" id="cm-k-avg-broker">—</div><div class="kpi-sub">$/lot</div></div>
    <div class="kpi-card"><div class="kpi-label">متوسط عمولة مسوّق</div><div class="kpi-value comm-low" id="cm-k-avg-mkt">—</div><div class="kpi-sub">$/lot</div></div>
    <div class="kpi-card"><div class="kpi-label">حسابات تجاوزت 5$/lot</div><div class="kpi-value comm-high" id="cm-k-over5">—</div><div class="kpi-sub">من إجمالي الحسابات</div></div>
    <div class="kpi-card"><div class="kpi-label">أعلى إجمالي عمولة</div><div class="kpi-value comm-high" id="cm-k-max">—</div><div class="kpi-sub">$/lot</div></div>
  </div>

  <!-- Histogram -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 توزيع عمولة البروكر (هيستوغرام)</div></div>
      <div style="padding:14px" id="cm-hist-broker"></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 توزيع عمولة المسوّق (هيستوغرام)</div></div>
      <div style="padding:14px" id="cm-hist-mkt"></div>
    </div>
  </div>

  <!-- Charts -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📈 متوسط عمولة البروكر — شهرياً</div></div>
      <div class="chart-wrap"><canvas id="cm-line-broker"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥧 نطاقات العمولة (كل النوعين)</div></div>
      <div class="chart-wrap"><canvas id="cm-donut-range"></canvas></div>
    </div>
  </div>

  <!-- Top commission cards table -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">⚠️ الحسابات الأعلى عمولةً (تجاوز 5$/lot)</div></div>
    <div class="table-scroll">
      <table class="data-table rpt-mini-table">
        <thead>
          <tr><th>رقم الحساب</th><th>البروكر</th><th>ع. بروكر</th><th>مسوّق داخلي</th><th>ع. مسوّق</th><th>إجمالي ع.</th><th>إيداع أولي</th><th>الشهر</th><th>الفرع</th></tr>
        </thead>
        <tbody id="cm-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══════════════ TAB: CC ══════════════ --}}
<div id="tab-cc" style="display:none">

  <!-- KPIs -->
  <div class="kpi-grid kpi-grid-4" style="margin-bottom:14px">
    <div class="kpi-card" style="border-color:rgba(123,104,238,.3)">
      <div class="kpi-label">إجمالي كروت CC</div>
      <div class="kpi-value" style="color:#7b68ee" id="cc-k-total">—</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(123,104,238,.3)">
      <div class="kpi-label">مكتملة</div>
      <div class="kpi-value" style="color:var(--gr)" id="cc-k-done">—</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(123,104,238,.3)">
      <div class="kpi-label">معلّقة / في الانتظار</div>
      <div class="kpi-value" style="color:var(--or)" id="cc-k-pending">—</div>
    </div>
    <div class="kpi-card" style="border-color:rgba(123,104,238,.3)">
      <div class="kpi-label">ملغاة / مرفوضة</div>
      <div class="kpi-value" style="color:var(--re)" id="cc-k-rejected">—</div>
    </div>
  </div>

  <!-- CC charts -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:14px">
    <div class="panel" style="border-color:rgba(123,104,238,.2)">
      <div class="panel-header"><div class="panel-title"><span class="cc-pill">CC</span> حالات الكروت</div></div>
      <div class="chart-wrap-sm"><canvas id="cc-donut-status"></canvas></div>
    </div>
    <div class="panel" style="border-color:rgba(123,104,238,.2)">
      <div class="panel-header"><div class="panel-title"><span class="cc-pill">CC</span> كروت CC شهرياً</div></div>
      <div class="chart-wrap-sm"><canvas id="cc-bar-monthly"></canvas></div>
    </div>
  </div>

  <!-- CC stats by branch -->
  <div class="panel" style="margin-bottom:14px;border-color:rgba(123,104,238,.2)">
    <div class="panel-header"><div class="panel-title"><span class="cc-pill">CC</span> توزيع كروت CC على الفروع</div></div>
    <div class="chart-wrap"><canvas id="cc-bar-branch"></canvas></div>
  </div>

  <!-- CC cards table -->
  <div class="panel" style="border-color:rgba(123,104,238,.2)">
    <div class="panel-header">
      <div class="panel-title"><span class="cc-pill">CC</span> قائمة كروت CC</div>
      <button class="btn btn-sm" style="background:rgba(123,104,238,.1);border:1px solid rgba(123,104,238,.3);color:#7b68ee" onclick="exportCcExcel()">📗 تصدير Excel</button>
    </div>
    <div class="table-scroll">
      <table class="data-table rpt-mini-table">
        <thead>
          <tr>
            <th>رقم الحساب</th><th>البروكر</th>
            <th>ع. بروكر</th><th>ع. مسوّق</th><th>إجمالي</th>
            <th>الفرع المُرسِل</th><th>حالة CC</th>
            <th>إيداع أولي</th><th>الشهر</th>
          </tr>
        </thead>
        <tbody id="cc-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

  </div>{{-- content panel --}}
</div>{{-- reports shell --}}

@endsection

@push('scripts')
<script>
/* ═══════════════════════════════════════════════════════════════
   WAFRA GULF — Reports Page JS
   Tabs: table | dash | brokers | branches | trends | commissions | cc
   ═══════════════════════════════════════════════════════════════ */

let RD = [];           // Full report data
let rptCharts = {};    // Chart instances keyed by id
let allEmployees = [];
let allBranches  = [];
let dashAutoLoaded = false;

function rL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }

const COLORS = ['#2E86AB','#22C97A','#F5A623','#7B68EE','#E05050','#3A9DB5','#1A5F7A','#26D4E8','#FF6B6B','#4ECDC4','#95B8D1','#E8A838'];
const tc = () => 'var(--mu)';
const gc = () => 'rgba(255,255,255,.05)';

function commColor(val){
  const v=parseFloat(val)||0;
  if(v>5) return 'comm-high';
  if(v>2) return 'comm-mid';
  return 'comm-low';
}
function fmt(v){ return '$'+(parseFloat(v)||0).toLocaleString('en',{minimumFractionDigits:0,maximumFractionDigits:0}); }
function fmtK(v){ const n=parseFloat(v)||0; return n>=1000000?'$'+(n/1000000).toFixed(1)+'M':n>=1000?'$'+(n/1000).toFixed(1)+'K':'$'+n.toFixed(0); }
function fmtComm(v){ return '$'+(parseFloat(v)||0).toFixed(2)+'/lot'; }
function destroyChart(id){ if(rptCharts[id]){rptCharts[id].destroy();rptCharts[id]=null;} }

/* ── Tab switching ───────────────────────────────────────── */
const ALL_TABS = ['table','dash','brokers','branches','trends','commissions','cc'];
let curTab = 'table';

function switchTab(name){
  ALL_TABS.forEach(t=>{
    document.getElementById('tab-'+t).style.display = t===name?'block':'none';
    const btn=document.getElementById('tbn-'+t);
    if(btn){ btn.classList.toggle('active',t===name); }
  });
  curTab=name;
  if(name==='dash')        buildDashboard();
  else if(name==='brokers')     buildBrokers();
  else if(name==='branches')    buildBranches();
  else if(name==='trends')      buildTrends();
  else if(name==='commissions') buildCommissions();
  else if(name==='cc')          buildCc();
}
switchTab('table');

/* ── Load filter options ─────────────────────────────────── */
async function loadFilterOptions(){
  const [empRes,brRes] = await Promise.all([
    api('GET','/employees?status=approved'),
    api('GET','/branches'),
  ]);

  // Months (last 36)
  const mNow=new Date();
  ['rf-from','rf-to'].forEach(id=>{
    const sel=document.getElementById(id); if(!sel)return;
    for(let i=0;i<36;i++){
      const d=new Date(mNow.getFullYear(),mNow.getMonth()-i,1);
      const m=d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
      const o=document.createElement('option');o.value=o.textContent=m;sel.appendChild(o);
    }
  });

  if(empRes.success){
    allEmployees=empRes.data;
    const icons={'broker':'🏦','marketing':'📢','external':'🌐','viewer':'👁'};
    const selIds=['rf-broker','db-broker','db-marketer'];
    selIds.forEach(sid=>{
      const s=document.getElementById(sid); if(!s)return;
      empRes.data.forEach(e=>{
        const o=document.createElement('option');
        o.value=e.id;
        o.textContent=e.name+(icons[e.role]?' '+icons[e.role]:'');
        s.appendChild(o);
      });
    });
  }

  if(brRes.success){
    allBranches=brRes.data;
    const brSelIds=['rf-branch','db-branch'];
    brSelIds.forEach(sid=>{
      const s=document.getElementById(sid); if(!s)return;
      brRes.data.forEach(b=>{
        const o=document.createElement('option');o.value=b.id;o.textContent=b.name_ar||b.name;s.appendChild(o);
      });
    });
  }
}

/* ── Generate Report ─────────────────────────────────────── */
async function generateReport(){
  const params=new URLSearchParams();
  const from   = document.getElementById('rf-from')?.value;
  const to     = document.getElementById('rf-to')?.value;
  const broker = document.getElementById('rf-broker')?.value;
  const branch = document.getElementById('rf-branch')?.value;
  const status = document.getElementById('rf-status')?.value;
  const kind   = document.getElementById('rf-kind')?.value;
  const source = document.getElementById('rf-source')?.value;
  const min    = document.getElementById('rf-min')?.value;

  if(from)   params.set('month_from',from);
  if(to)     params.set('month_to',to);
  if(broker) params.set('broker_id',broker);
  if(branch) params.set('branch_id',branch);
  if(status) params.set('status',status);
  if(kind)   params.set('kind',kind);
  if(source) params.set('source',source);
  if(min && parseInt(min)>0) params.set('min_deposit',min);
  params.set('per_page',2000);

  const loading = toast(rL()==='en'?'⚡ Generating report…':'⚡ جارٍ توليد التقرير…','info');
  const r=await api('GET','/cards/report?'+params);
  if(!r.success){ toast(rL()==='en'?'Error generating report':'خطأ في توليد التقرير','error'); return; }
  if(r.records_limited) toast(rL()==='en'?'⚠️ Limited to 2000 records — narrow your filters for more':'⚠️ محدود بـ 2000 سجل — ضيّق الفلتر للحصول على المزيد','warning');

  RD=r.data||[];
  renderTable(RD, r.summary||{});
  toast(rL()==='en'?`✅ Report: ${RD.length} records`:`✅ تقرير: ${RD.length} سجل`,'success');

  // Rebuild whichever tab is active
  if(curTab==='dash')        { buildDashboard(); }
  else if(curTab==='brokers')     buildBrokers();
  else if(curTab==='branches')    buildBranches();
  else if(curTab==='trends')      buildTrends();
  else if(curTab==='commissions') buildCommissions();
  else if(curTab==='cc')          buildCc();
}

/* ── Render Table Tab ────────────────────────────────────── */
function renderTable(data, summary){
  const wrap=document.getElementById('rpt-table-wrap');
  const empty=document.getElementById('tbl-empty');
  if(!data.length){ wrap.style.display='none'; if(empty)empty.style.display='block'; return; }
  if(empty)empty.style.display='none';
  wrap.style.display='block';

  const s=summary;
  const totalDep  = data.reduce((a,c)=>a+parseFloat(c.initial_deposit||0),0);
  const totalMon  = data.reduce((a,c)=>a+parseFloat(c.monthly_deposit||0),0);
  const modCount  = data.filter(c=>c.status==='modified').length;
  const newCount  = data.filter(c=>c.status==='new_added').length;

  document.getElementById('rpt-count').textContent = data.length+(rL()==='en'?' records':' سجل');
  document.getElementById('t-k-total').textContent = data.length.toLocaleString();
  document.getElementById('t-k-dep').textContent   = fmtK(totalDep);
  document.getElementById('t-k-mon').textContent   = fmtK(totalMon);
  document.getElementById('t-k-mod').textContent   = modCount.toLocaleString();
  document.getElementById('t-k-new-sub').textContent= (rL()==='en'?'New Added: ':'مضافة جديدة: ')+newCount;

  document.getElementById('rpt-tbody').innerHTML = data.map((c,i)=>{
    const isCC      = !!c.cc_status;
    const totalComm = (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0));
    const rowCls    = isCC?'row-cc-card':c.status==='modified'?'row-modified':c.status==='new_added'?'row-new_added':'';
    const brName    = c.branch?.name_ar || c.branch?.name || '—';
    return `<tr class="${rowCls}">
      <td style="color:var(--mu);font-size:10px">${i+1}</td>
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td>${isCC?`<span class="cc-pill">CC</span>`:`<span style="font-size:10px;color:var(--mu)">${rL()==='en'?'Regular':'عادي'}</span>`}</td>
      <td>
        <div style="font-weight:700;color:var(--pri2)">${c.broker?.name||'—'}</div>
        <div class="mono ${commColor(c.broker_commission)}" style="font-size:10px">${fmtComm(c.broker_commission)}</div>
      </td>
      <td>
        <div style="color:var(--m2)">${c.marketer?.name&&c.marketer.name!==c.broker?.name?c.marketer.name:'—'}</div>
        ${c.marketer_commission?`<div class="mono ${commColor(c.marketer_commission)}" style="font-size:10px">${fmtComm(c.marketer_commission)}</div>`:''}
      </td>
      <td>
        <div style="color:#7b68ee">${c.ext_marketer1?.name||'—'}</div>
        ${c.ext_commission1?`<div class="mono ${commColor(c.ext_commission1)}" style="font-size:10px">${fmtComm(c.ext_commission1)}</div>`:''}
      </td>
      <td>
        <div style="color:#7b68ee">${c.ext_marketer2?.name||'—'}</div>
        ${c.ext_commission2?`<div class="mono ${commColor(c.ext_commission2)}" style="font-size:10px">${fmtComm(c.ext_commission2)}</div>`:''}
      </td>
      <td class="mono ${commColor(totalComm)}" style="font-weight:800">${totalComm.toFixed(2)}$</td>
      <td class="mono c-blue">${fmt(c.initial_deposit)}</td>
      <td class="mono c-green">${fmt(c.monthly_deposit)}</td>
      <td><span class="badge ${c.account_kind==='new'?'badge-green':'badge-blue'}">${c.account_kind==='new'?'NEW':'SUB'}</span></td>
      <td style="color:var(--mu);font-size:11px">${c.month}</td>
      <td style="font-size:11px">${brName}</td>
      <td>${c.status==='modified'?`<span class="badge badge-orange">✏️ ${rL()==='en'?'Modified':'معدّل'}</span>`:c.status==='new_added'?`<span class="badge badge-green">🆕 ${rL()==='en'?'New Added':'جديد'}</span>`:`<span class="badge badge-blue" style="opacity:.5">${rL()==='en'?'Active':'عادي'}</span>`}</td>
    </tr>`;
  }).join('');

  // Footer totals
  const avgBroker = data.length ? (data.reduce((a,c)=>a+parseFloat(c.broker_commission||0),0)/data.length).toFixed(2) : '—';
  const avgMkt    = data.length ? (data.reduce((a,c)=>a+parseFloat(c.marketer_commission||0),0)/data.length).toFixed(2) : '—';
  const isEn = rL()==='en';
  document.getElementById('rpt-tfoot').innerHTML = `
    <tr style="background:var(--inp-bg);font-weight:700;font-size:11px">
      <td colspan="8" style="padding:8px 12px;color:var(--mu)">
        ${isEn?'Totals':'إجمالي'} — ${isEn?'Avg Broker Comm.':'متوسط ع.بروكر'}: <span class="${commColor(avgBroker)}">${avgBroker}$/lot</span>
        &nbsp;|&nbsp; ${isEn?'Avg Marketer Comm.':'متوسط ع.مسوّق'}: <span class="${commColor(avgMkt)}">${avgMkt}$/lot</span>
      </td>
      <td class="mono c-blue" style="padding:8px 12px">${fmtK(totalDep)}</td>
      <td class="mono c-green" style="padding:8px 12px">${fmtK(totalMon)}</td>
      <td colspan="4" style="padding:8px 12px;color:var(--mu)">${isEn?'Modified':'معدّلة'}: ${modCount} | ${isEn?'Added':'مضافة'}: ${newCount} | CC: ${data.filter(c=>!!c.cc_status).length}</td>
    </tr>`;
}

/* ── Dashboard filter helper ─────────────────────────────── */
function getDbFiltered(){
  let data=RD.length?[...RD]:[];
  const branchId  = document.getElementById('db-branch')?.value;
  const brokerId  = document.getElementById('db-broker')?.value;
  const marketerId= document.getElementById('db-marketer')?.value;
  const period    = document.getElementById('db-period')?.value||'all';

  if(branchId)   data=data.filter(c=>String(c.branch_id)===branchId);
  if(brokerId)   data=data.filter(c=>String(c.broker_id)===brokerId);
  if(marketerId) data=data.filter(c=>String(c.marketer_id)===marketerId||String(c.ext_marketer1_id)===marketerId||String(c.ext_marketer2_id)===marketerId);
  if(period!=='all'){
    const cutoff=new Date(); cutoff.setMonth(cutoff.getMonth()-parseInt(period));
    data=data.filter(c=>{ const d=new Date('01 '+c.month); return d>=cutoff; });
  }
  return data;
}
function clearDashFilters(){
  ['db-branch','db-broker','db-marketer'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  const p=document.getElementById('db-period'); if(p)p.value='all';
  buildDashboard();
}

/* ── Dashboard ───────────────────────────────────────────── */
async function buildDashboard(){
  if(!RD.length && !dashAutoLoaded){
    dashAutoLoaded=true;
    const r=await api('GET','/cards/report?per_page=2000');
    if(r.success){ RD=r.data||[]; renderTable(RD,r.summary||{}); }
  }
  const data=getDbFiltered();
  const empty=document.getElementById('db-empty');
  if(!data.length){ if(empty)empty.style.display='block'; return; }
  if(empty)empty.style.display='none';

  const totalDep = data.reduce((a,c)=>a+parseFloat(c.initial_deposit||0),0);
  const totalMon = data.reduce((a,c)=>a+parseFloat(c.monthly_deposit||0),0);
  const newAccts = data.filter(c=>c.account_kind==='new').length;
  const subAccts = data.filter(c=>c.account_kind==='sub').length;
  const ccAccts  = data.filter(c=>!!c.cc_status).length;

  document.getElementById('db-k-total').textContent=data.length.toLocaleString();
  document.getElementById('db-k-dep').textContent=fmtK(totalDep);
  document.getElementById('db-k-mon').textContent=fmtK(totalMon);
  document.getElementById('db-k-new').textContent=newAccts.toLocaleString();
  document.getElementById('db-k-sub').textContent=subAccts.toLocaleString();
  document.getElementById('db-k-cc').textContent=ccAccts.toLocaleString();

  // Broker analysis
  const brokerCnt={},brokerDep={};
  data.forEach(c=>{
    const n=c.broker?.name||(rL()==='en'?'Unassigned':'غير محدد');
    brokerCnt[n]=(brokerCnt[n]||0)+1;
    brokerDep[n]=(brokerDep[n]||0)+parseFloat(c.initial_deposit||0);
  });
  const sortedBrokers=Object.entries(brokerCnt).sort((a,b)=>b[1]-a[1]);
  const top1=sortedBrokers[0];

  // Top Brokers list
  document.getElementById('db-top-broker').innerHTML=sortedBrokers.slice(0,5).map(([n,cnt],i)=>`
    <div class="rank-row">
      <span style="font-size:15px">${['🥇','🥈','🥉','🏅','🏅'][i]||'•'}</span>
      <div style="flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--tx);margin-bottom:3px">${n}</div>
        <div class="rank-bar-wrap"><div class="rank-bar" style="background:var(--pri2);width:${top1?Math.round(cnt/top1[1]*100):100}%"></div></div>
      </div>
      <span style="font-size:11px;font-weight:700;color:var(--pri2)">${cnt}</span>
    </div>`).join('')||`<div style="color:var(--mu);text-align:center;padding:16px;font-size:12px">${rL()==='en'?'No data available':'لا توجد بيانات'}</div>`;

  // Top Marketers list
  const mktCnt={};
  data.forEach(c=>{
    [[c.marketer],[c.ext_marketer1],[c.ext_marketer2]].forEach(([emp])=>{
      if(emp?.name) mktCnt[emp.name]=(mktCnt[emp.name]||0)+1;
    });
  });
  const sortedMkt=Object.entries(mktCnt).sort((a,b)=>b[1]-a[1]);
  const topM=sortedMkt[0];
  document.getElementById('db-top-marketer').innerHTML=sortedMkt.slice(0,5).map(([n,cnt],i)=>`
    <div class="rank-row">
      <span style="font-size:15px">${['🥇','🥈','🥉','🏅','🏅'][i]||'•'}</span>
      <div style="flex:1">
        <div style="font-size:11px;font-weight:700;color:var(--tx);margin-bottom:3px">${n}</div>
        <div class="rank-bar-wrap"><div class="rank-bar" style="background:var(--gr);width:${topM?Math.round(cnt/topM[1]*100):100}%"></div></div>
      </div>
      <span style="font-size:11px;font-weight:700;color:var(--gr)">${cnt}</span>
    </div>`).join('')||`<div style="color:var(--mu);text-align:center;padding:16px;font-size:12px">${rL()==='en'?'No data available':'لا توجد بيانات'}</div>`;

  // Charts
  destroyChart('dbKind');
  const kindEl=document.getElementById('db-chart-kind');
  if(kindEl) rptCharts.dbKind=new Chart(kindEl,{type:'doughnut',
    data:{labels:['NEW — جديد','SUB — فرعي'],datasets:[{data:[newAccts,subAccts],backgroundColor:['#22C97A','#2E86AB'],borderWidth:0,hoverOffset:6}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'65%',plugins:{legend:{position:'bottom',labels:{color:'#5A7A9A',font:{size:9},boxWidth:8,padding:6}},
      tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/(data.length||1)*100)}%)`}}}}});

  const topBrk=sortedBrokers.slice(0,10);
  destroyChart('dbBrokerCnt');
  const bCntEl=document.getElementById('db-chart-broker-cnt');
  if(bCntEl) rptCharts.dbBrokerCnt=new Chart(bCntEl,{type:'bar',
    data:{labels:topBrk.map(([n])=>n),datasets:[{data:topBrk.map(([,c])=>c),backgroundColor:COLORS,borderRadius:5,borderSkipped:false}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},tooltip:{callbacks:{label:ctx=>`${ctx.raw} حساب`}}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  const topBrkDep=Object.entries(brokerDep).sort((a,b)=>b[1]-a[1]).slice(0,8);
  destroyChart('dbBrokerDep');
  const bDepEl=document.getElementById('db-chart-broker-dep');
  if(bDepEl) rptCharts.dbBrokerDep=new Chart(bDepEl,{type:'doughnut',
    data:{labels:topBrkDep.map(([n])=>n),datasets:[{data:topBrkDep.map(([,v])=>Math.round(v)),backgroundColor:COLORS,borderWidth:2,borderColor:'var(--bg3)',hoverOffset:6}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'55%',plugins:{legend:{position:'right',labels:{color:'#5A7A9A',font:{size:8},boxWidth:7,padding:4}},
      tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${fmtK(ctx.raw)}`}}}}});

  const mCnt={};
  data.forEach(c=>{ mCnt[c.month]=(mCnt[c.month]||0)+1; });
  const mKeys=Object.keys(mCnt).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  destroyChart('dbMonthly');
  const mEl=document.getElementById('db-chart-monthly');
  if(mEl) rptCharts.dbMonthly=new Chart(mEl,{type:'bar',
    data:{labels:mKeys,datasets:[{label:'عدد الحسابات',data:mKeys.map(m=>mCnt[m]),backgroundColor:'rgba(46,134,171,.75)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  // Marketer table
  const mktFull={};
  data.forEach(c=>{
    [[c.marketer,rL()==='en'?'Internal':'داخلي'],[c.ext_marketer1,rL()==='en'?'External 1':'خارجي 1'],[c.ext_marketer2,rL()==='en'?'External 2':'خارجي 2']].forEach(([emp,type])=>{
      if(!emp?.name)return;
      if(!mktFull[emp.name]) mktFull[emp.name]={name:emp.name,type,cnt:0,dep:0};
      mktFull[emp.name].cnt++;
      mktFull[emp.name].dep+=parseFloat(c.initial_deposit||0);
    });
  });
  const mktRows=Object.values(mktFull).sort((a,b)=>b.cnt-a.cnt);
  const mktTop=mktRows[0];
  document.getElementById('db-marketer-table').innerHTML=mktRows.length?`
    <table class="rpt-mini-table" style="width:100%">
      <thead><tr><th>#</th><th>الاسم</th><th>النوع</th><th>الحسابات</th><th>الإيداع الأولي</th><th>النسبة</th></tr></thead>
      <tbody>${mktRows.map((m,i)=>`
        <tr>
          <td>${i+1}</td><td style="font-weight:700">${m.name}</td>
          <td><span class="badge ${(m.type==='داخلي'||m.type==='Internal')?'badge-blue':'badge-orange'}" style="font-size:9px">${m.type}</span></td>
          <td style="font-weight:700;color:var(--pri2)">${m.cnt}</td>
          <td style="color:var(--gr)">${fmtK(m.dep)}</td>
          <td><div class="rank-bar-wrap"><div class="rank-bar" style="background:var(--gr);width:${mktTop?Math.round(m.cnt/mktTop.cnt*100):100}%"></div></div></td>
        </tr>`).join('')}</tbody></table>`
  :`<div style="color:var(--mu);text-align:center;padding:16px;font-size:12px">${rL()==='en'?'No data available':'لا توجد بيانات'}</div>`;
}

/* ── Brokers Tab ─────────────────────────────────────────── */
function buildBrokers(){
  if(!RD.length) return;
  const data=RD;

  const brokers={};
  data.forEach(c=>{
    const n=c.broker?.name||(rL()==='en'?'Unassigned':'غير محدد');
    if(!brokers[n]) brokers[n]={name:n,cnt:0,new:0,sub:0,dep:0,mon:0,comms:[],cc:0,minC:999,maxC:0};
    brokers[n].cnt++;
    if(c.account_kind==='new') brokers[n].new++;
    else brokers[n].sub++;
    brokers[n].dep+=parseFloat(c.initial_deposit||0);
    brokers[n].mon+=parseFloat(c.monthly_deposit||0);
    const comm=parseFloat(c.broker_commission||0);
    brokers[n].comms.push(comm);
    if(comm<brokers[n].minC) brokers[n].minC=comm;
    if(comm>brokers[n].maxC) brokers[n].maxC=comm;
    if(c.cc_status) brokers[n].cc++;
  });
  const rows=Object.values(brokers).sort((a,b)=>b.cnt-a.cnt);
  const total=data.length;

  // KPIs
  const topDep=rows.reduce((a,b)=>b.dep>a.dep?b:a,rows[0]);
  const avgAcc=(rows.length?total/rows.length:0).toFixed(1);
  const avgComm=(data.reduce((a,c)=>a+parseFloat(c.broker_commission||0),0)/total).toFixed(2);
  document.getElementById('br-k-count').textContent=rows.length;
  document.getElementById('br-k-avg-acc').textContent=avgAcc;
  document.getElementById('br-k-top-dep').textContent=topDep?fmtK(topDep.dep):'—';
  document.getElementById('br-k-avg-comm').textContent='$'+avgComm;

  // Charts
  const topRows=rows.slice(0,10);
  destroyChart('brBar');
  const barEl=document.getElementById('br-chart-bar');
  if(barEl) rptCharts.brBar=new Chart(barEl,{type:'bar',
    data:{labels:topRows.map(r=>r.name),datasets:[
      {label:'إيداع أولي',data:topRows.map(r=>Math.round(r.dep)),backgroundColor:'rgba(46,134,171,.7)',borderRadius:4,yAxisID:'y'},
      {label:'عدد الحسابات',data:topRows.map(r=>r.cnt),backgroundColor:'rgba(34,201,122,.7)',borderRadius:4,yAxisID:'y2'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#5A7A9A',font:{size:9},boxWidth:8}}},
      scales:{
        x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},
        y:{position:'right',ticks:{color:'#5A7A9A',font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}},
        y2:{position:'left',ticks:{color:'#5A7A9A',font:{size:9}},grid:{display:false}}}}});

  destroyChart('brComm');
  const commEl=document.getElementById('br-chart-comm');
  if(commEl){
    const avgComms=topRows.map(r=>r.comms.length?r.comms.reduce((a,v)=>a+v,0)/r.comms.length:0);
    const barColors=avgComms.map(v=>v>5?'rgba(224,80,80,.75)':v>2?'rgba(245,166,35,.75)':'rgba(34,201,122,.75)');
    rptCharts.brComm=new Chart(commEl,{type:'bar',
      data:{labels:topRows.map(r=>r.name),datasets:[{label:'متوسط ع.بروكر ($/lot)',data:avgComms.map(v=>parseFloat(v.toFixed(2))),backgroundColor:barColors,borderRadius:4}]},
      options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
        scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},
          y:{ticks:{color:'#5A7A9A',font:{size:9},callback:v=>'$'+v},grid:{color:gc()}}}}});
  }

  // Table
  document.getElementById('br-tbody').innerHTML=rows.map((r,i)=>{
    const avgC=r.comms.length?r.comms.reduce((a,v)=>a+v,0)/r.comms.length:0;
    return `<tr>
      <td>${i+1}</td>
      <td style="font-weight:700;color:var(--pri2)">${r.name}</td>
      <td style="font-weight:700">${r.cnt}</td>
      <td><span class="badge badge-green">${r.new}</span></td>
      <td><span class="badge badge-blue">${r.sub}</span></td>
      <td class="mono c-blue">${fmtK(r.dep)}</td>
      <td class="mono c-green">${fmtK(r.mon)}</td>
      <td class="mono ${commColor(avgC)}">${avgC.toFixed(2)}$</td>
      <td class="mono" style="font-size:10px;color:var(--mu)">${r.minC===999?'—':'$'+r.minC}</td>
      <td class="mono" style="font-size:10px;color:var(--mu)">${r.maxC>0?'$'+r.maxC:'—'}</td>
      <td>${r.cc>0?`<span class="cc-pill">${r.cc}</span>`:'—'}</td>
      <td>
        <div class="rank-bar-wrap"><div class="rank-bar" style="background:var(--pri2);width:${Math.round(r.cnt/total*100)}%"></div></div>
        <span style="font-size:9px;color:var(--mu)">${Math.round(r.cnt/total*100)}%</span>
      </td>
    </tr>`;
  }).join('');
}

/* ── Branches Tab ────────────────────────────────────────── */
function buildBranches(){
  if(!RD.length) return;
  const data=RD;

  const branches={};
  data.forEach(c=>{
    const id  = c.branch_id||'unknown';
    const name= c.branch?.name_ar||c.branch?.name||'غير محدد';
    if(!branches[id]) branches[id]={id,name,cnt:0,new:0,sub:0,mod:0,cc:0,dep:0,mon:0,comms:[]};
    branches[id].cnt++;
    if(c.account_kind==='new') branches[id].new++;
    else branches[id].sub++;
    if(c.status==='modified') branches[id].mod++;
    if(c.cc_status) branches[id].cc++;
    branches[id].dep+=parseFloat(c.initial_deposit||0);
    branches[id].mon+=parseFloat(c.monthly_deposit||0);
    branches[id].comms.push(parseFloat(c.broker_commission||0));
  });
  const rows=Object.values(branches).sort((a,b)=>b.dep-a.dep);
  const topDep=rows[0];

  // KPIs
  document.getElementById('bn-k-count').textContent=rows.length;
  document.getElementById('bn-k-top').textContent=topDep?.name||'—';
  document.getElementById('bn-k-avg').textContent=rows.length?(data.length/rows.length).toFixed(1):'—';
  document.getElementById('bn-k-cc').textContent=data.filter(c=>!!c.cc_status).length;

  // Branch cards
  document.getElementById('bn-cards').innerHTML=rows.map(r=>{
    const avgC=r.comms.length?r.comms.reduce((a,v)=>a+v,0)/r.comms.length:0;
    return `<div class="branch-card">
      <div class="branch-name">🏢 ${r.name}</div>
      <div class="branch-stat"><span class="branch-stat-label">إجمالي الحسابات</span><strong style="color:var(--pri2)">${r.cnt}</strong></div>
      <div class="branch-stat"><span class="branch-stat-label">NEW / SUB</span><span><span class="badge badge-green" style="font-size:9px">${r.new}</span> / <span class="badge badge-blue" style="font-size:9px">${r.sub}</span></span></div>
      <div class="branch-stat"><span class="branch-stat-label">معدّلة</span><strong style="color:var(--or)">${r.mod}</strong></div>
      <div class="branch-stat"><span class="branch-stat-label">كروت CC</span><strong style="color:#7b68ee">${r.cc}</strong></div>
      <div class="branch-stat"><span class="branch-stat-label">إيداع أولي</span><strong style="color:var(--gr)">${fmtK(r.dep)}</strong></div>
      <div class="branch-stat"><span class="branch-stat-label">متوسط ع.بروكر</span><strong class="${commColor(avgC)}">${avgC.toFixed(2)}$</strong></div>
    </div>`;
  }).join('');

  // Charts
  destroyChart('bnAcc');
  const accEl=document.getElementById('bn-chart-acc');
  if(accEl) rptCharts.bnAcc=new Chart(accEl,{type:'bar',
    data:{labels:rows.map(r=>r.name),datasets:[
      {label:'NEW',data:rows.map(r=>r.new),backgroundColor:'rgba(34,201,122,.75)',borderRadius:[4,4,0,0]},
      {label:'SUB',data:rows.map(r=>r.sub),backgroundColor:'rgba(46,134,171,.75)',borderRadius:[4,4,0,0]}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#5A7A9A',font:{size:9},boxWidth:8}}},
      scales:{x:{stacked:true,ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{stacked:true,ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  destroyChart('bnDep');
  const depEl=document.getElementById('bn-chart-dep');
  if(depEl) rptCharts.bnDep=new Chart(depEl,{type:'doughnut',
    data:{labels:rows.map(r=>r.name),datasets:[{data:rows.map(r=>Math.round(r.dep)),backgroundColor:COLORS,borderWidth:2,borderColor:'var(--bg3)'}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'55%',plugins:{legend:{position:'right',labels:{color:'#5A7A9A',font:{size:9},boxWidth:7,padding:4}},
      tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${fmtK(ctx.raw)}`}}}}});

  // Table
  document.getElementById('bn-tbody').innerHTML=rows.map((r,i)=>{
    const avgC=r.comms.length?r.comms.reduce((a,v)=>a+v,0)/r.comms.length:0;
    return `<tr>
      <td>${i+1}</td>
      <td style="font-weight:700">${r.name}</td>
      <td style="font-weight:700;color:var(--pri2)">${r.cnt}</td>
      <td><span class="badge badge-green">${r.new}</span></td>
      <td><span class="badge badge-blue">${r.sub}</span></td>
      <td><span class="badge badge-orange">${r.mod}</span></td>
      <td>${r.cc?`<span class="cc-pill">${r.cc}</span>`:'—'}</td>
      <td class="mono c-blue">${fmtK(r.dep)}</td>
      <td class="mono c-green">${fmtK(r.mon)}</td>
      <td class="mono ${commColor(avgC)}">${avgC.toFixed(2)}$</td>
    </tr>`;
  }).join('');
}

/* ── Trends Tab ──────────────────────────────────────────── */
function buildTrends(){
  if(!RD.length) return;
  const data=RD;

  const months={};
  data.forEach(c=>{
    const m=c.month;
    if(!months[m]) months[m]={m,cnt:0,new:0,sub:0,mod:0,newAdded:0,cc:0,dep:0,mon:0,comms:[]};
    months[m].cnt++;
    if(c.account_kind==='new') months[m].new++;
    else months[m].sub++;
    if(c.status==='modified') months[m].mod++;
    if(c.status==='new_added') months[m].newAdded++;
    if(c.cc_status) months[m].cc++;
    months[m].dep+=parseFloat(c.initial_deposit||0);
    months[m].mon+=parseFloat(c.monthly_deposit||0);
    months[m].comms.push(parseFloat(c.broker_commission||0));
  });
  const mRows=Object.values(months).sort((a,b)=>new Date('01 '+a.m)-new Date('01 '+b.m));
  const mKeys=mRows.map(r=>r.m);

  // KPIs
  const peakMonth=mRows.reduce((a,b)=>b.cnt>a.cnt?b:a,mRows[0]);
  const peakDep  =mRows.reduce((a,b)=>b.dep>a.dep?b:a,mRows[0]);
  const avgMonth =(data.length/Math.max(mRows.length,1)).toFixed(1);
  let growth='—';
  if(mRows.length>=2){
    const last2=mRows.slice(-2);
    const g=last2[0].cnt?Math.round((last2[1].cnt-last2[0].cnt)/last2[0].cnt*100):0;
    growth=(g>=0?'+':'')+g+'%';
  }
  document.getElementById('tr-k-peak-month').textContent=peakMonth?.m||'—';
  document.getElementById('tr-k-peak-dep').textContent=peakDep?fmtK(peakDep.dep):'—';
  document.getElementById('tr-k-avg-month').textContent=avgMonth;
  document.getElementById('tr-k-growth').textContent=growth;

  // Line chart — deposit
  destroyChart('trLineDep');
  const lineDepEl=document.getElementById('tr-line-dep');
  if(lineDepEl) rptCharts.trLineDep=new Chart(lineDepEl,{type:'line',
    data:{labels:mKeys,datasets:[{label:'إيداع أولي',data:mRows.map(r=>Math.round(r.dep)),borderColor:'#22C97A',backgroundColor:'rgba(34,201,122,.1)',tension:.4,fill:true,pointRadius:4,pointBackgroundColor:'#22C97A'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},
        y:{ticks:{color:'#5A7A9A',font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}}}}});

  // Bar — count
  destroyChart('trBarCnt');
  const cntEl=document.getElementById('tr-bar-cnt');
  if(cntEl) rptCharts.trBarCnt=new Chart(cntEl,{type:'bar',
    data:{labels:mKeys,datasets:[{label:'عدد الحسابات',data:mRows.map(r=>r.cnt),backgroundColor:'rgba(46,134,171,.75)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  // Bar — NEW vs SUB
  destroyChart('trBarKind');
  const kindEl=document.getElementById('tr-bar-kind');
  if(kindEl) rptCharts.trBarKind=new Chart(kindEl,{type:'bar',
    data:{labels:mKeys,datasets:[
      {label:'NEW',data:mRows.map(r=>r.new),backgroundColor:'rgba(34,201,122,.75)',borderRadius:[4,4,0,0]},
      {label:'SUB',data:mRows.map(r=>r.sub),backgroundColor:'rgba(46,134,171,.75)',borderRadius:[4,4,0,0]}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#5A7A9A',font:{size:9},boxWidth:8}}},
      scales:{x:{stacked:true,ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{stacked:true,ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  // Line — modified vs normal
  destroyChart('trLineMod');
  const modEl=document.getElementById('tr-line-mod');
  if(modEl) rptCharts.trLineMod=new Chart(modEl,{type:'line',
    data:{labels:mKeys,datasets:[
      {label:'معدّلة',data:mRows.map(r=>r.mod),borderColor:'#F5A623',backgroundColor:'rgba(245,166,35,.1)',tension:.4,fill:true,pointRadius:3},
      {label:'مضافة جديدة',data:mRows.map(r=>r.newAdded),borderColor:'#22C97A',backgroundColor:'rgba(34,201,122,.1)',tension:.4,fill:true,pointRadius:3}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:'#5A7A9A',font:{size:9},boxWidth:8}}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  // Table
  document.getElementById('tr-tbody').innerHTML=mRows.map(r=>{
    const avgC=r.comms.length?r.comms.reduce((a,v)=>a+v,0)/r.comms.length:0;
    return `<tr>
      <td style="font-weight:700;color:var(--tx)">${r.m}</td>
      <td style="font-weight:700;color:var(--pri2)">${r.cnt}</td>
      <td><span class="badge badge-green">${r.new}</span></td>
      <td><span class="badge badge-blue">${r.sub}</span></td>
      <td><span class="badge badge-orange">${r.mod}</span></td>
      <td><span class="badge badge-green">${r.newAdded}</span></td>
      <td>${r.cc?`<span class="cc-pill">${r.cc}</span>`:'—'}</td>
      <td class="mono c-blue">${fmtK(r.dep)}</td>
      <td class="mono c-green">${fmtK(r.mon)}</td>
      <td class="mono ${commColor(avgC)}">${avgC.toFixed(2)}$</td>
    </tr>`;
  }).join('');
}

/* ── Commissions Tab ─────────────────────────────────────── */
function buildCommissions(){
  if(!RD.length) return;
  const data=RD;

  const brokerComms = data.map(c=>parseFloat(c.broker_commission||0));
  const mktComms    = data.map(c=>parseFloat(c.marketer_commission||0));
  const totalComms  = data.map(c=>brokerComms[data.indexOf(c)]+mktComms[data.indexOf(c)]+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0));

  const avgBroker = brokerComms.reduce((a,v)=>a+v,0)/brokerComms.length;
  const avgMkt    = mktComms.reduce((a,v)=>a+v,0)/mktComms.length;
  const over5     = data.filter((_,i)=>totalComms[i]>5).length;
  const maxTotal  = Math.max(...totalComms);

  document.getElementById('cm-k-avg-broker').textContent='$'+avgBroker.toFixed(2);
  document.getElementById('cm-k-avg-mkt').textContent='$'+avgMkt.toFixed(2);
  document.getElementById('cm-k-over5').textContent=over5;
  document.getElementById('cm-k-max').textContent='$'+maxTotal.toFixed(2);

  // Histogram helper
  function buildHistogram(comms, containerId, color){
    const ranges=[
      {label:'0–0.5$',min:0,max:.5},
      {label:'0.5–1$',min:.5,max:1},
      {label:'1–1.5$',min:1,max:1.5},
      {label:'1.5–2$',min:1.5,max:2},
      {label:'2–3$',  min:2,max:3},
      {label:'3–4$',  min:3,max:4},
      {label:'4–5$',  min:4,max:5},
      {label:'>5$',   min:5,max:999},
    ];
    const counts=ranges.map(r=>comms.filter(v=>v>=r.min&&v<r.max).length);
    const maxC=Math.max(...counts,1);
    const colors=['#22c97a','#22c97a','#22c97a','#3a9db5','#f5a623','#f5a623','#e05050','#e05050'];
    document.getElementById(containerId).innerHTML=ranges.map((r,i)=>`
      <div class="hist-bar-row">
        <span class="hist-label">${r.label}</span>
        <div class="hist-bar-bg">
          <div class="hist-bar-fill" style="width:${Math.round(counts[i]/maxC*100)}%;background:${colors[i]}">${counts[i]>0?counts[i]:''}</div>
        </div>
        <span class="hist-count">${counts[i]}</span>
      </div>`).join('');
  }
  buildHistogram(brokerComms,'cm-hist-broker','#2E86AB');
  buildHistogram(mktComms,'cm-hist-mkt','#22C97A');

  // Monthly avg broker commission line
  const mBroker={};
  data.forEach(c=>{ const m=c.month; if(!mBroker[m])mBroker[m]=[]; mBroker[m].push(parseFloat(c.broker_commission||0)); });
  const mKeys=Object.keys(mBroker).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  destroyChart('cmLineBroker');
  const lineEl=document.getElementById('cm-line-broker');
  if(lineEl) rptCharts.cmLineBroker=new Chart(lineEl,{type:'line',
    data:{labels:mKeys,datasets:[{label:'متوسط ع.بروكر',data:mKeys.map(m=>{const c=mBroker[m];return parseFloat((c.reduce((a,v)=>a+v,0)/c.length).toFixed(2));}),borderColor:'#2E86AB',backgroundColor:'rgba(46,134,171,.1)',tension:.4,fill:true,pointRadius:4,pointBackgroundColor:'#2E86AB'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},
        y:{ticks:{color:'#5A7A9A',font:{size:9},callback:v=>'$'+v},grid:{color:gc()}}}}});

  // Commission range donut
  const rBuckets={'< 1$':0,'1–2$':0,'2–3$':0,'3–5$':0,'> 5$':0};
  totalComms.forEach(v=>{
    if(v<1)       rBuckets['< 1$']++;
    else if(v<2)  rBuckets['1–2$']++;
    else if(v<3)  rBuckets['2–3$']++;
    else if(v<=5) rBuckets['3–5$']++;
    else          rBuckets['> 5$']++;
  });
  destroyChart('cmDonut');
  const donutEl=document.getElementById('cm-donut-range');
  if(donutEl) rptCharts.cmDonut=new Chart(donutEl,{type:'doughnut',
    data:{labels:Object.keys(rBuckets),datasets:[{data:Object.values(rBuckets),backgroundColor:['#22C97A','#3A9DB5','#2E86AB','#F5A623','#E05050'],borderWidth:2,borderColor:'var(--bg3)',hoverOffset:6}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{position:'right',labels:{color:'#5A7A9A',font:{size:9},boxWidth:7,padding:4}},
      tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/data.length*100)}%)`}}}}});

  // High commission table (>5$ total)
  const highComm=data.filter((_,i)=>totalComms[i]>5).sort((a,b)=>totalComms[data.indexOf(b)]-totalComms[data.indexOf(a)]).slice(0,50);
  document.getElementById('cm-tbody').innerHTML=highComm.length?highComm.map(c=>{
    const tc2=parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0);
    return `<tr style="background:rgba(224,80,80,.04)">
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="font-weight:700;color:var(--pri2)">${c.broker?.name||'—'}</td>
      <td class="mono comm-high">${parseFloat(c.broker_commission||0).toFixed(2)}$</td>
      <td style="color:var(--m2)">${c.marketer?.name||'—'}</td>
      <td class="mono comm-mid">${parseFloat(c.marketer_commission||0).toFixed(2)}$</td>
      <td class="mono comm-high" style="font-weight:800">${tc2.toFixed(2)}$</td>
      <td class="mono c-blue">${fmt(c.initial_deposit)}</td>
      <td style="color:var(--mu);font-size:11px">${c.month}</td>
      <td style="font-size:11px">${c.branch?.name_ar||c.branch?.name||'—'}</td>
    </tr>`;
  }).join(''):'<tr><td colspan="9" style="text-align:center;padding:20px;color:var(--mu)">لا توجد حسابات تتجاوز 5$/lot</td></tr>';
}

/* ── CC Tab ──────────────────────────────────────────────── */
function buildCc(){
  if(!RD.length) return;
  const ccData=RD.filter(c=>!!c.cc_status);

  if(!ccData.length){
    document.getElementById('cc-k-total').textContent='0';
    document.getElementById('cc-k-done').textContent='0';
    document.getElementById('cc-k-pending').textContent='0';
    document.getElementById('cc-k-rejected').textContent='0';
    document.getElementById('cc-tbody').innerHTML='<tr><td colspan="9" style="text-align:center;padding:30px;color:var(--mu)">لا توجد كروت CC في هذه الفترة</td></tr>';
    return;
  }

  const doneStatuses    =['completed','accepted'];
  const pendingStatuses =['cc_pending','branch_pending'];
  const rejectedStatuses=['rejected','cancelled'];

  const done    = ccData.filter(c=>doneStatuses.includes(c.cc_status)).length;
  const pending = ccData.filter(c=>pendingStatuses.includes(c.cc_status)).length;
  const rejected= ccData.filter(c=>rejectedStatuses.includes(c.cc_status)).length;

  document.getElementById('cc-k-total').textContent   = ccData.length;
  document.getElementById('cc-k-done').textContent    = done;
  document.getElementById('cc-k-pending').textContent = pending;
  document.getElementById('cc-k-rejected').textContent= rejected;

  // Status donut
  const statusGroups={'مكتملة':done,'معلّقة':pending,'مرفوضة':rejected,'أخرى':ccData.length-done-pending-rejected};
  destroyChart('ccDonut');
  const donutEl=document.getElementById('cc-donut-status');
  if(donutEl) rptCharts.ccDonut=new Chart(donutEl,{type:'doughnut',
    data:{labels:Object.keys(statusGroups),datasets:[{data:Object.values(statusGroups),backgroundColor:['#22C97A','#F5A623','#E05050','#7B68EE'],borderWidth:2,borderColor:'var(--bg3)'}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{position:'right',labels:{color:'#5A7A9A',font:{size:9},boxWidth:7,padding:4}}}}});

  // Monthly CC bar
  const mCC={};
  ccData.forEach(c=>{ mCC[c.month]=(mCC[c.month]||0)+1; });
  const mKeys=Object.keys(mCC).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  destroyChart('ccBarMonthly');
  const mEl=document.getElementById('cc-bar-monthly');
  if(mEl) rptCharts.ccBarMonthly=new Chart(mEl,{type:'bar',
    data:{labels:mKeys,datasets:[{label:'كروت CC',data:mKeys.map(m=>mCC[m]),backgroundColor:'rgba(123,104,238,.7)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  // By branch bar
  const bCC={};
  ccData.forEach(c=>{ const n=c.branch?.name_ar||c.branch?.name||'غير محدد'; bCC[n]=(bCC[n]||0)+1; });
  const brEntries=Object.entries(bCC).sort((a,b)=>b[1]-a[1]);
  destroyChart('ccBarBranch');
  const brEl=document.getElementById('cc-bar-branch');
  if(brEl) rptCharts.ccBarBranch=new Chart(brEl,{type:'bar',
    data:{labels:brEntries.map(([n])=>n),datasets:[{label:'كروت CC',data:brEntries.map(([,v])=>v),backgroundColor:COLORS.map(c=>c+'bb'),borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}},y:{ticks:{color:'#5A7A9A',font:{size:9}},grid:{color:gc()}}}}});

  // Status label map
  const STATUS_MAP={cc_pending:'⏳ CC معلّق',branch_pending:'📩 فرع معلّق',accepted:'✅ مقبول',completed:'🎉 مكتمل',rejected:'❌ مرفوض',cancelled:'🚫 ملغي'};
  const STATUS_COLOR={cc_pending:'badge-orange',branch_pending:'badge-orange',accepted:'badge-blue',completed:'badge-green',rejected:'badge-red',cancelled:'badge-red'};

  // CC table
  document.getElementById('cc-tbody').innerHTML=ccData.map(c=>{
    const totalComm=parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0);
    return `<tr class="row-cc-card">
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="font-weight:700;color:var(--pri2)">${c.broker?.name||'—'}</td>
      <td class="mono ${commColor(c.broker_commission)}">${parseFloat(c.broker_commission||0).toFixed(2)}$</td>
      <td class="mono ${commColor(c.marketer_commission)}">${parseFloat(c.marketer_commission||0).toFixed(2)}$</td>
      <td class="mono ${commColor(totalComm)}" style="font-weight:800">${totalComm.toFixed(2)}$${totalComm>5?' ⚠️':''}</td>
      <td style="font-size:11px">${c.branch?.name_ar||c.branch?.name||'—'}</td>
      <td><span class="badge ${STATUS_COLOR[c.cc_status]||'badge-blue'}" style="font-size:9px">${STATUS_MAP[c.cc_status]||c.cc_status}</span></td>
      <td class="mono c-blue">${fmt(c.initial_deposit)}</td>
      <td style="color:var(--mu);font-size:11px">${c.month}</td>
    </tr>`;
  }).join('');
}

/* ── Export functions ────────────────────────────────────── */
function exportRptExcel(){
  if(!RD.length){toast(rL()==='en'?'No data to export':'لا توجد بيانات','error');return;}
  const headers=['#','رقم الحساب','المصدر','البروكر','ع.بروكر','مسوّق','ع.مسوّق','خارجي1','ع.خارجي1','خارجي2','ع.خارجي2','إجمالي ع.','إيداع أولي','إيداع شهري','النوع','الشهر','الفرع','الحالة'];
  const rows=[headers,...RD.map((c,i)=>[
    i+1,c.account_number,c.cc_status?'CC':'عادي',
    c.broker?.name||'',parseFloat(c.broker_commission||0),
    c.marketer?.name||'',parseFloat(c.marketer_commission||0),
    c.ext_marketer1?.name||'',parseFloat(c.ext_commission1||0),
    c.ext_marketer2?.name||'',parseFloat(c.ext_commission2||0),
    (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0)).toFixed(2),
    parseFloat(c.initial_deposit||0),parseFloat(c.monthly_deposit||0),
    c.account_kind,c.month,
    c.branch?.name_ar||c.branch?.name||'',
    c.status==='modified'?'معدّل':c.status==='new_added'?'جديد':'عادي',
  ])];
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'التقرير');
  XLSX.writeFile(wb,'WafraReport_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast('تم تحميل Excel ✅','success');
}

function exportRptPdf(){
  if(!RD.length){toast(rL()==='en'?'No data to export':'لا توجد بيانات','error');return;}
  const {jsPDF}=window.jspdf;
  const doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a3'});
  doc.setFontSize(13);doc.setTextColor(46,134,171);
  doc.text('وفرة الخليجية — التقرير الديناميكي',210,14,{align:'center'});
  doc.autoTable({startY:22,
    head:[['#','AC','Source','Broker','B.Comm','Marketer','M.Comm','Total','Initial','Monthly','Kind','Month','Branch','Status']],
    body:RD.map((c,i)=>[i+1,c.account_number,c.cc_status?'CC':'Regular',
      c.broker?.name||'',parseFloat(c.broker_commission||0).toFixed(2),
      c.marketer?.name||'',parseFloat(c.marketer_commission||0).toFixed(2),
      (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0)).toFixed(2),
      parseFloat(c.initial_deposit||0).toLocaleString(),parseFloat(c.monthly_deposit||0).toLocaleString(),
      c.account_kind,c.month,c.branch?.name_ar||'',c.status]),
    styles:{fontSize:6,cellPadding:2},
    headStyles:{fillColor:[46,134,171],textColor:[255,255,255]},
    didParseCell:d=>{
      const s=d.row.raw?.[13];
      if(s==='modified') Object.values(d.row.cells).forEach(cl=>{cl.styles.fillColor=[255,248,220];});
      else if(d.row.raw?.[2]==='CC') Object.values(d.row.cells).forEach(cl=>{cl.styles.fillColor=[240,238,255];});
    }});
  doc.save('WafraReport_'+new Date().toISOString().slice(0,10)+'.pdf');
  toast('تم تحميل PDF ✅','success');
}

function exportBrokersExcel(){
  if(!RD.length){toast(rL()==='en'?'No data to export':'لا توجد بيانات','error');return;}
  const brokers={};
  RD.forEach(c=>{
    const n=c.broker?.name||(rL()==='en'?'Unassigned':'غير محدد');
    if(!brokers[n]) brokers[n]={name:n,cnt:0,new:0,sub:0,dep:0,mon:0,comms:[],cc:0};
    brokers[n].cnt++;
    if(c.account_kind==='new') brokers[n].new++;else brokers[n].sub++;
    brokers[n].dep+=parseFloat(c.initial_deposit||0);brokers[n].mon+=parseFloat(c.monthly_deposit||0);
    brokers[n].comms.push(parseFloat(c.broker_commission||0));if(c.cc_status)brokers[n].cc++;
  });
  const rows=[['البروكر','عدد الحسابات','NEW','SUB','إيداع أولي','إيداع شهري','متوسط ع.بروكر','كروت CC'],
    ...Object.values(brokers).sort((a,b)=>b.cnt-a.cnt).map(r=>[r.name,r.cnt,r.new,r.sub,Math.round(r.dep),Math.round(r.mon),(r.comms.reduce((a,v)=>a+v,0)/r.comms.length).toFixed(2),r.cc])];
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'البروكرات');
  XLSX.writeFile(wb,'WafraBrokers_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast('تم تحميل Excel ✅','success');
}

function exportTrendsExcel(){
  if(!RD.length){toast(rL()==='en'?'No data to export':'لا توجد بيانات','error');return;}
  const months={};
  RD.forEach(c=>{
    const m=c.month;if(!months[m])months[m]={m,cnt:0,new:0,sub:0,mod:0,newAdded:0,cc:0,dep:0,mon:0,comms:[]};
    months[m].cnt++;if(c.account_kind==='new')months[m].new++;else months[m].sub++;
    if(c.status==='modified')months[m].mod++;if(c.status==='new_added')months[m].newAdded++;
    if(c.cc_status)months[m].cc++;months[m].dep+=parseFloat(c.initial_deposit||0);months[m].mon+=parseFloat(c.monthly_deposit||0);
    months[m].comms.push(parseFloat(c.broker_commission||0));
  });
  const rows=[['الشهر','إجمالي','NEW','SUB','معدّلة','مضافة جديدة','CC','إيداع أولي','إيداع شهري','متوسط ع.'],
    ...Object.values(months).sort((a,b)=>new Date('01 '+a.m)-new Date('01 '+b.m)).map(r=>[r.m,r.cnt,r.new,r.sub,r.mod,r.newAdded,r.cc,Math.round(r.dep),Math.round(r.mon),(r.comms.reduce((a,v)=>a+v,0)/r.comms.length).toFixed(2)])];
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'الاتجاهات الشهرية');
  XLSX.writeFile(wb,'WafraTrends_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast('تم تحميل Excel ✅','success');
}

function exportCcExcel(){
  const ccData=RD.filter(c=>!!c.cc_status);
  if(!ccData.length){toast('لا توجد كروت CC','error');return;}
  const rows=[['رقم الحساب','البروكر','ع.بروكر','ع.مسوّق','إجمالي','الفرع','حالة CC','إيداع أولي','الشهر'],
    ...ccData.map(c=>[(c.account_number),(c.broker?.name||''),(parseFloat(c.broker_commission||0)),(parseFloat(c.marketer_commission||0)),(parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)).toFixed(2),(c.branch?.name_ar||''),(c.cc_status),(parseFloat(c.initial_deposit||0)),(c.month)])];
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'كروت CC');
  XLSX.writeFile(wb,'WafraCC_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast('تم تحميل Excel ✅','success');
}

function clearRptFilters(){
  ['rf-from','rf-to','rf-broker','rf-branch','rf-status','rf-kind','rf-source'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  const m=document.getElementById('rf-min');if(m)m.value='0';
}

// Boot
loadFilterOptions();

/* ── Reports sidebar nav bilingual ──────────────────────── */
(function(){
  const RN={
    ar:{hdr:'📈 التقارير',lblMonthly:'التقارير الشهرية',subMonthly:'تحليل شامل بالرسوم البيانية',lblDynamic:'تقرير ديناميكي',subDynamic:'بناء تقرير مخصص',badgeNew:'جديد',footer:'منصة وفرة الخليجية\nلإدارة العمولات'},
    en:{hdr:'📈 Reports',lblMonthly:'Monthly Reports',subMonthly:'Full analysis with charts',lblDynamic:'Dynamic Report',subDynamic:'Build custom report',badgeNew:'New',footer:'Wafra Gulf Platform\nCommission Management'},
  };
  function rNavApplyLang(){
    const L=(typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';
    const d=RN[L]||RN.ar;
    const _t=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
    _t('rnav-hdr',d.hdr);_t('rnav-lbl-monthly',d.lblMonthly);_t('rnav-sub-monthly',d.subMonthly);
    _t('rnav-lbl-dynamic',d.lblDynamic);_t('rnav-sub-dynamic',d.subDynamic);_t('rnav-badge-new',d.badgeNew);
    const fn=document.getElementById('rnav-footer');if(fn)fn.innerHTML=d.footer.replace('\n','<br>');
  }
  const _rnOrig=window.applyLang;
  window.applyLang=function(lang){if(_rnOrig)_rnOrig(lang);rNavApplyLang();};
  rNavApplyLang();
})();

/* ── Reports Page Full Bilingual Translation ─────────────── */
(function(){
  const RPT_I18N = {
    ar: {
      tbTitle:'التقارير',
      tbnTable:'📋 جدول البيانات', tbnDash:'📊 Dashboard', tbnBrokers:'🏦 البروكرات',
      tbnBranches:'🏢 الفروع', tbnTrends:'📈 الاتجاهات', tbnCommissions:'💰 تحليل العمولات', tbnCc:'📞 كروت CC',
      flFrom:'من شهر', flTo:'إلى شهر', flBroker:'البروكر', flBranch:'الفرع',
      flStatus:'الحالة', flKind:'النوع', flSource:'المصدر', flMin:'حد أدنى $',
      allBrokers:'الكل', allBranches:'كل الفروع',
      statusAll:'الكل', statusMod:'معدّلة فقط', statusNew:'مضافة جديدة فقط', statusAct:'عادي فقط',
      kindAll:'الكل', kindNew:'جديد', kindSub:'فرعي',
      srcAll:'الكل', srcReg:'عادي', srcCC:'CC فقط',
      genBtn:'⚡ توليد التقرير', clearBtn:'✕ مسح',
    },
    en: {
      tbTitle:'Reports',
      tbnTable:'📋 Data Table', tbnDash:'📊 Dashboard', tbnBrokers:'🏦 Brokers',
      tbnBranches:'🏢 Branches', tbnTrends:'📈 Trends', tbnCommissions:'💰 Commission Analysis', tbnCc:'📞 CC Cards',
      flFrom:'From Month', flTo:'To Month', flBroker:'Broker', flBranch:'Branch',
      flStatus:'Status', flKind:'Kind', flSource:'Source', flMin:'Min Deposit $',
      allBrokers:'All', allBranches:'All Branches',
      statusAll:'All', statusMod:'Modified Only', statusNew:'New Added Only', statusAct:'Active Only',
      kindAll:'All', kindNew:'New', kindSub:'Sub',
      srcAll:'All', srcReg:'Regular', srcCC:'CC Only',
      genBtn:'⚡ Generate Report', clearBtn:'✕ Clear',
    }
  };

  function rL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
  function ri(key) { const l = rL(); return RPT_I18N[l]?.[key] ?? RPT_I18N.ar[key] ?? key; }

  function rptPageApplyLang() {
    const t = (id, key) => { const e = document.getElementById(id); if (e) e.textContent = ri(key); };
    // Tab buttons
    t('tbn-table','tbnTable'); t('tbn-dash','tbnDash'); t('tbn-brokers','tbnBrokers');
    t('tbn-branches','tbnBranches'); t('tbn-trends','tbnTrends');
    t('tbn-commissions','tbnCommissions'); t('tbn-cc','tbnCc');
    // Filter labels
    t('rfl-from','flFrom'); t('rfl-to','flTo'); t('rfl-broker','flBroker');
    t('rfl-branch','flBranch'); t('rfl-status','flStatus');
    t('rfl-kind','flKind'); t('rfl-source','flSource'); t('rfl-min','flMin');
    // Filter select options
    t('rfl-all-brokers','allBrokers'); t('rfl-all-branches','allBranches');
    t('rfl-status-all','statusAll'); t('rfl-status-mod','statusMod');
    t('rfl-status-new','statusNew'); t('rfl-status-act','statusAct');
    t('rfl-kind-all','kindAll'); t('rfl-kind-new','kindNew'); t('rfl-kind-sub','kindSub');
    t('rfl-src-all','srcAll'); t('rfl-src-reg','srcReg'); t('rfl-src-cc','srcCC');
    t('rpt-gen-btn','genBtn'); t('rpt-clr-btn','clearBtn');
    // Topbar
    const tb = document.querySelector('.tb-title'); if (tb) tb.textContent = ri('tbTitle');
    // Re-render table if data loaded
    if (RD.length) renderTable(RD, {});
  }

  const _rptOrig = window.applyLang;
  window.applyLang = function(lang) {
    if (_rptOrig) _rptOrig(lang);
    rptPageApplyLang();
    // Rebuild active tab
    if (curTab === 'dash')        buildDashboard();
    else if (curTab === 'brokers')     buildBrokers();
    else if (curTab === 'branches')    buildBranches();
    else if (curTab === 'trends')      buildTrends();
    else if (curTab === 'commissions') buildCommissions();
    else if (curTab === 'cc')          buildCc();
  };
  rptPageApplyLang();
})();
</script>
@endpush
