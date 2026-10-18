@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
/* ── Modern Dashboard shell ─────────────────────────────── */
.dash-wrap{display:flex;flex-direction:column;gap:16px}

/* ── Period filter bar ──────────────────────────────────── */
.dash-topbar{display:flex;flex-wrap:wrap;gap:10px;align-items:center;padding:12px 16px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px}
.dash-period-chips{display:flex;gap:6px;flex-wrap:wrap}
.dash-chip{padding:6px 14px;border-radius:20px;border:1px solid var(--brd1);background:var(--bg2);color:var(--mu);cursor:pointer;font-size:12px;font-weight:700;transition:all .18s;font-family:inherit}
.dash-chip:hover{background:rgba(26,173,186,.12);border-color:rgba(26,173,186,.4);color:var(--pri2)}
.dash-chip.on{background:linear-gradient(135deg,#1AADBA,#0E7A88);color:#fff;border-color:#1AADBA;box-shadow:0 2px 8px rgba(26,173,186,.3)}
.dash-topbar-right{display:flex;align-items:center;gap:8px;flex:1;justify-content:flex-end;min-width:0}
.dash-topbar-right .form-control{min-width:160px;font-size:12px}
.dash-refresh-btn{background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3);color:var(--pri2);padding:7px 14px;border-radius:9px;cursor:pointer;font-weight:700;font-size:12px;transition:all .18s;font-family:inherit;display:flex;align-items:center;gap:5px}
.dash-refresh-btn:hover{background:rgba(26,173,186,.22);border-color:var(--pri2);transform:translateY(-1px)}

/* ── KPI Grid ───────────────────────────────────────────── */
.dash-kpi-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:12px}
@media(max-width:1200px){.dash-kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:600px) {.dash-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.dash-kpi{position:relative;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;padding:14px 16px;overflow:hidden;transition:all .22s;cursor:pointer}
.dash-kpi:hover{transform:translateY(-2px);border-color:rgba(26,173,186,.5);box-shadow:0 8px 24px rgba(0,0,0,.15)}
.dash-kpi::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:var(--kpi-accent, #1AADBA)}
.dash-kpi .k-top{display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px}
.dash-kpi .k-lbl{font-size:10px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;font-weight:700;line-height:1.3}
.dash-kpi .k-ico{font-size:20px;opacity:.5;transition:opacity .18s}
.dash-kpi:hover .k-ico{opacity:1}
.dash-kpi .k-val{font-size:1.75rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1;color:var(--kpi-color, var(--tx))}
.dash-kpi .k-sub{font-size:10px;color:var(--mu);margin-top:4px;display:flex;align-items:center;gap:4px}
.dash-kpi .k-trend{font-size:9px;padding:2px 6px;border-radius:10px;font-weight:800}
.dash-kpi .k-trend.up{background:rgba(34,201,122,.15);color:var(--gr)}
.dash-kpi .k-trend.flat{background:rgba(120,120,120,.1);color:var(--mu)}
.dash-kpi .k-trend.down{background:rgba(224,72,72,.15);color:var(--re)}

.dash-kpi.k-total   {--kpi-accent:#1AADBA;--kpi-color:#22C4D4}
.dash-kpi.k-dep     {--kpi-accent:#1ECC80;--kpi-color:#1ECC80}
.dash-kpi.k-mon     {--kpi-accent:#0E7A88;--kpi-color:#1AADBA}
.dash-kpi.k-mod     {--kpi-accent:#F5A828;--kpi-color:#F5A828}
.dash-kpi.k-new     {--kpi-accent:#8A78F0;--kpi-color:#A898FF}
.dash-kpi.k-alert   {--kpi-accent:#E84545;--kpi-color:#E84545}

/* ── Chart cards ────────────────────────────────────────── */
.dash-chart-row{display:grid;grid-template-columns:1fr 1fr;gap:16px}
@media(max-width:1024px){.dash-chart-row{grid-template-columns:1fr}}
.dash-chart-card{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;padding:16px}
.dash-chart-card .ch-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:14px}
.dash-chart-card .ch-title{font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px}
.dash-chart-card .ch-sub{font-size:11px;color:var(--mu)}
.dash-chart-card .ch-body{position:relative;height:260px}
.dash-chart-card .ch-body-tall{position:relative;height:320px}

/* ── Mini-section cards (3-col row) ─────────────────────── */
.dash-mini-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
@media(max-width:1024px){.dash-mini-row{grid-template-columns:1fr 1fr}}
@media(max-width:640px) {.dash-mini-row{grid-template-columns:1fr}}
.dash-mini{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;overflow:hidden;display:flex;flex-direction:column}
.dash-mini .mi-header{display:flex;align-items:center;justify-content:space-between;padding:14px 16px;border-bottom:1px solid var(--brd1);background:linear-gradient(135deg,var(--mi-tint,rgba(26,173,186,.07)),transparent)}
.dash-mini .mi-title{font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px}
.dash-mini .mi-count{font-size:11px;padding:3px 10px;border-radius:12px;font-weight:800}
.dash-mini .mi-body{flex:1;padding:4px 0;max-height:280px;overflow-y:auto}
.dash-mini .mi-row{padding:10px 16px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--brd1);transition:all .15s;cursor:pointer;text-decoration:none;color:inherit}
.dash-mini .mi-row:last-child{border-bottom:none}
.dash-mini .mi-row:hover{background:var(--bg2)}
.dash-mini .mi-row .mi-ac{font-family:'JetBrains Mono',monospace;font-weight:800;font-size:12px;color:var(--pri2);min-width:70px}
.dash-mini .mi-row .mi-meta{flex:1;min-width:0;font-size:11px;color:var(--tx)}
.dash-mini .mi-row .mi-meta b{display:block;color:var(--tx);margin-bottom:2px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-mini .mi-row .mi-meta span{color:var(--mu);font-size:10px}
.dash-mini .mi-row .mi-chip{font-size:9px;padding:2px 8px;border-radius:10px;font-weight:800;white-space:nowrap}
.dash-mini .mi-row .mi-chip.warn   {background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3)}
.dash-mini .mi-row .mi-chip.danger {background:rgba(224,72,72,.15);color:#e04848;border:1px solid rgba(224,72,72,.3)}
.dash-mini .mi-row .mi-chip.info   {background:rgba(26,173,186,.15);color:#1AADBA;border:1px solid rgba(26,173,186,.3)}
.dash-mini .mi-row .mi-chip.ok     {background:rgba(34,201,122,.15);color:var(--gr);border:1px solid rgba(34,201,122,.3)}
.dash-mini .mi-row .mi-chip.cc     {background:rgba(123,104,238,.15);color:#7b68ee;border:1px solid rgba(123,104,238,.3)}
.dash-mini .mi-footer{padding:10px 14px;border-top:1px solid var(--brd1);background:var(--bg2)}
.dash-mini .mi-footer a{display:flex;justify-content:center;align-items:center;gap:4px;font-size:11px;color:var(--pri2);font-weight:700;text-decoration:none;padding:4px}
.dash-mini .mi-footer a:hover{color:var(--pri)}
.dash-mini .mi-empty{text-align:center;padding:36px 14px;color:var(--mu);font-size:12px}
.dash-mini.danger-tint{--mi-tint:rgba(224,72,72,.07)}
.dash-mini.danger-tint .mi-count{background:rgba(224,72,72,.15);color:#e04848;border:1px solid rgba(224,72,72,.3)}
.dash-mini.cc-tint{--mi-tint:rgba(123,104,238,.08)}
.dash-mini.cc-tint .mi-count{background:rgba(123,104,238,.15);color:#7b68ee;border:1px solid rgba(123,104,238,.3)}
.dash-mini.warn-tint{--mi-tint:rgba(245,166,35,.08)}
.dash-mini.warn-tint .mi-count{background:rgba(245,166,35,.15);color:#f5a623;border:1px solid rgba(245,166,35,.3)}

/* ── Leaderboard (top performers) ───────────────────────── */
.dash-lb-row{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px}
@media(max-width:1024px){.dash-lb-row{grid-template-columns:1fr}}
.dash-lb{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;overflow:hidden}
.dash-lb .lb-header{padding:14px 16px;border-bottom:1px solid var(--brd1);display:flex;align-items:center;justify-content:space-between;background:linear-gradient(135deg,var(--lb-tint,rgba(26,173,186,.07)),transparent)}
.dash-lb .lb-title{font-size:13px;font-weight:800;display:flex;align-items:center;gap:8px}
.dash-lb .lb-tab-group{display:flex;gap:3px;background:var(--bg2);border-radius:8px;padding:2px}
.dash-lb .lb-tab{font-size:10px;padding:4px 10px;border-radius:6px;background:none;border:none;cursor:pointer;color:var(--mu);font-family:inherit;font-weight:700;transition:all .15s}
.dash-lb .lb-tab.on{background:var(--card-bg);color:var(--tx);box-shadow:0 1px 3px rgba(0,0,0,.1)}
.dash-lb .lb-body{padding:4px 0;max-height:360px;overflow-y:auto}
.dash-lb .lb-item{display:flex;align-items:center;gap:12px;padding:11px 16px;border-bottom:1px solid var(--brd1);transition:all .15s;position:relative}
.dash-lb .lb-item:last-child{border-bottom:none}
.dash-lb .lb-item:hover{background:var(--bg2)}
.dash-lb .lb-rank{font-size:17px;width:28px;text-align:center;flex-shrink:0}
.dash-lb .lb-info{flex:1;min-width:0}
.dash-lb .lb-info b{display:block;font-size:12px;color:var(--tx);font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.dash-lb .lb-info .lb-sub{font-size:10px;color:var(--mu);margin-top:3px;display:flex;align-items:center;gap:4px}
.dash-lb .lb-val{font-family:'JetBrains Mono',monospace;font-size:13px;font-weight:800;color:var(--lb-color,#1AADBA);flex-shrink:0}
.dash-lb .lb-bar-wrap{position:absolute;left:0;bottom:0;height:2px;background:var(--brd1);width:100%}
.dash-lb .lb-bar-fill{height:100%;background:var(--lb-color,#1AADBA);opacity:.6;transition:width .6s cubic-bezier(.22,1,.36,1)}
.dash-lb.brokers{--lb-tint:rgba(26,173,186,.08);--lb-color:#1AADBA}
.dash-lb.marketers{--lb-tint:rgba(34,201,122,.08);--lb-color:var(--gr)}
.dash-lb.branches{--lb-tint:rgba(123,104,238,.08);--lb-color:#7b68ee}
.dash-lb .lb-empty{text-align:center;padding:40px 14px;color:var(--mu);font-size:12px}

/* ── Loading shimmer ────────────────────────────────────── */
.shimmer{background:linear-gradient(90deg,var(--bg2),var(--card-bg),var(--bg2));background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:4px;color:transparent}
@keyframes shimmer{0%{background-position:200% 0}100%{background-position:-200% 0}}
.k-val.loading{display:inline-block;min-width:60px;height:28px;background:linear-gradient(90deg,var(--bg2),var(--card-bg),var(--bg2));background-size:200% 100%;animation:shimmer 1.4s infinite;border-radius:6px;color:transparent}

/* ── Small flourishes ───────────────────────────────────── */
.pulse{animation:pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.65}}
</style>
@endpush

@section('content')
<div class="dash-wrap">

  {{-- ── Period / Branch Filter Bar ─────────────────────── --}}
  <div class="dash-topbar">
    <div class="dash-period-chips" id="dash-period-chips">
      <button class="dash-chip on" data-period="all"  onclick="setPeriod('all')"  id="pc-all"></button>
      <button class="dash-chip"    data-period="this" onclick="setPeriod('this')" id="pc-this"></button>
      <button class="dash-chip"    data-period="3m"   onclick="setPeriod('3m')"   id="pc-3m"></button>
      <button class="dash-chip"    data-period="6m"   onclick="setPeriod('6m')"   id="pc-6m"></button>
      <button class="dash-chip"    data-period="ytd"  onclick="setPeriod('ytd')"  id="pc-ytd"></button>
    </div>
    <div class="dash-topbar-right">
      <select id="dash-branch-sel" class="form-control" onchange="reloadDash()">
        <option value="" id="dash-opt-allbr">كل الفروع</option>
      </select>
      <button class="dash-refresh-btn" onclick="reloadDash()">
        <span id="dash-refresh-ico">🔄</span>
        <span id="dash-refresh-txt">تحديث</span>
      </button>
    </div>
  </div>

  {{-- ── KPI Row (6 cards) ──────────────────────────────── --}}
  <div class="dash-kpi-grid">
    <div class="dash-kpi k-total" onclick="goTo('/cards')">
      <div class="k-top">
        <div class="k-lbl" id="k-lbl-total">إجمالي الكروت</div>
        <div class="k-ico">📁</div>
      </div>
      <div class="k-val loading" id="kpi-total">—</div>
      <div class="k-sub"><span id="k-sub-total">كل حسابات العمولات</span></div>
    </div>
    <div class="dash-kpi k-dep">
      <div class="k-top">
        <div class="k-lbl" id="k-lbl-dep">إيداع فتح الحساب</div>
        <div class="k-ico">💵</div>
      </div>
      <div class="k-val loading" id="kpi-dep">—</div>
      <div class="k-sub"><span id="k-sub-dep">إجمالي الإيداع الأولي</span></div>
    </div>
    <div class="dash-kpi k-mon">
      <div class="k-top">
        <div class="k-lbl" id="k-lbl-mon">الإيداع الشهري المتوقع</div>
        <div class="k-ico">📈</div>
      </div>
      <div class="k-val loading" id="kpi-mon">—</div>
      <div class="k-sub"><span id="k-sub-mon">إجمالي الإيداع الشهري</span></div>
    </div>
    <div class="dash-kpi k-mod" onclick="goTo('/cards/modified')">
      <div class="k-top">
        <div class="k-lbl" id="k-lbl-mod">حسابات معدّلة</div>
        <div class="k-ico">✏️</div>
      </div>
      <div class="k-val loading" id="kpi-mod">—</div>
      <div class="k-sub"><span id="k-sub-mod">كروت تم تعديلها</span></div>
    </div>
    <div class="dash-kpi k-new">
      <div class="k-top">
        <div class="k-lbl" id="k-lbl-new">كروت مضافة حديثاً</div>
        <div class="k-ico">🆕</div>
      </div>
      <div class="k-val loading" id="kpi-new">—</div>
      <div class="k-sub"><span id="k-sub-new">إضافات جديدة</span></div>
    </div>
    <div class="dash-kpi k-alert" onclick="goTo('/cards?filter=needs_review')">
      <div class="k-top">
        <div class="k-lbl" id="k-lbl-alert">تحتاج مراجعة</div>
        <div class="k-ico">⚠️</div>
      </div>
      <div class="k-val loading" id="kpi-alert">—</div>
      <div class="k-sub">
        <span class="k-trend" id="kpi-alert-trend">—</span>
        <span id="k-sub-alert">تجاوز 7$ أو مكرّر</span>
      </div>
    </div>
  </div>

  {{-- ── Charts: Monthly Trend + Branches ───────────────── --}}
  <div class="dash-chart-row">
    <div class="dash-chart-card">
      <div class="ch-header">
        <div class="ch-title">📊 <span id="ch-trend-title">النشاط الشهري</span></div>
        <div class="ch-sub" id="ch-trend-sub">آخر 12 شهر</div>
      </div>
      <div class="ch-body"><canvas id="chart-monthly"></canvas></div>
    </div>
    <div class="dash-chart-card">
      <div class="ch-header">
        <div class="ch-title">🏢 <span id="ch-branch-title">الحسابات حسب الفرع</span></div>
        <div class="ch-sub" id="ch-branch-sub">—</div>
      </div>
      <div class="ch-body"><canvas id="chart-branches"></canvas></div>
    </div>
  </div>

  {{-- ── 3-col mini-section: Needs Review + CC Pending + Recent Edits ── --}}
  <div class="dash-mini-row">
    {{-- Needs Review (Over-limit + Duplicates) --}}
    <div class="dash-mini danger-tint">
      <div class="mi-header">
        <div class="mi-title">⚠️ <span id="mi-rev-title">تحتاج مراجعة</span></div>
        <span class="mi-count" id="mi-rev-count">0</span>
      </div>
      <div class="mi-body" id="mi-rev-body">
        <div class="mi-empty" id="mi-rev-empty">— جارِ التحميل —</div>
      </div>
      <div class="mi-footer">
        <a href="/cards?filter=needs_review" id="mi-rev-link">عرض الكل ←</a>
      </div>
    </div>

    {{-- CC Pending --}}
    <div class="dash-mini cc-tint">
      <div class="mi-header">
        <div class="mi-title">📞 <span id="mi-cc-title">مركز الاتصال</span></div>
        <span class="mi-count" id="mi-cc-count">0</span>
      </div>
      <div class="mi-body" id="mi-cc-body">
        <div class="mi-empty" id="mi-cc-empty">— جارِ التحميل —</div>
      </div>
      <div class="mi-footer">
        <a href="/callcenter/pending" id="mi-cc-link">عرض الكل ←</a>
      </div>
    </div>

    {{-- Recent Modifications --}}
    <div class="dash-mini warn-tint">
      <div class="mi-header">
        <div class="mi-title">✏️ <span id="mi-mod-title">آخر التعديلات</span></div>
        <span class="mi-count" id="mi-mod-count">0</span>
      </div>
      <div class="mi-body" id="mi-mod-body">
        <div class="mi-empty" id="mi-mod-empty">— جارِ التحميل —</div>
      </div>
      <div class="mi-footer">
        <a href="/cards/modified" id="mi-mod-link">عرض الكل ←</a>
      </div>
    </div>
  </div>

  {{-- ── Leaderboard — top performers ───────────────────── --}}
  <div class="dash-lb-row">
    {{-- Top Brokers --}}
    <div class="dash-lb brokers">
      <div class="lb-header">
        <div class="lb-title">🥇 <span id="lb-brk-title">البروكرات</span></div>
        <div class="lb-tab-group">
          <button class="lb-tab on" data-metric="count" onclick="setBrkMetric('count')" id="lb-brk-tab-cnt">عدد</button>
          <button class="lb-tab"    data-metric="dep"   onclick="setBrkMetric('dep')"   id="lb-brk-tab-dep">إيداع</button>
        </div>
      </div>
      <div class="lb-body" id="lb-brokers">
        <div class="lb-empty">—</div>
      </div>
    </div>

    {{-- Top Marketers --}}
    <div class="dash-lb marketers">
      <div class="lb-header">
        <div class="lb-title">📢 <span id="lb-mkt-title">المسوّقين</span></div>
        <div style="font-size:10px;color:var(--mu)">
          <span id="lb-mkt-sub">عدد الحسابات</span>
        </div>
      </div>
      <div class="lb-body" id="lb-marketers">
        <div class="lb-empty">—</div>
      </div>
    </div>

    {{-- Branches Breakdown --}}
    <div class="dash-lb branches">
      <div class="lb-header">
        <div class="lb-title">🏢 <span id="lb-brn-title">الفروع</span></div>
        <div style="font-size:10px;color:var(--mu)">
          <span id="lb-brn-sub">إجمالي الحسابات</span>
        </div>
      </div>
      <div class="lb-body" id="lb-branches">
        <div class="lb-empty">—</div>
      </div>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════
   DASHBOARD CONTROLLER — modern interactive
   ══════════════════════════════════════════════════════════════ */

/* Bilingual strings */
const DASH = {
  ar: {
    pc_all:'🌐 الكل', pc_this:'📅 هذا الشهر', pc_3m:'3 شهور', pc_6m:'6 شهور', pc_ytd:'منذ بداية السنة',
    all_branches:'كل الفروع', refresh:'تحديث',
    k_total:'إجمالي الكروت', k_sub_total:'كل حسابات العمولات',
    k_dep:'إيداع فتح الحساب', k_sub_dep:'إجمالي الإيداع الأولي',
    k_mon:'الإيداع الشهري المتوقع', k_sub_mon:'إجمالي الإيداع الشهري',
    k_mod:'حسابات معدّلة', k_sub_mod:'كروت تم تعديلها',
    k_new:'كروت مضافة حديثاً', k_sub_new:'إضافات جديدة',
    k_alert:'تحتاج مراجعة', k_sub_alert:'تجاوز 7$ أو مكرّر',
    ch_trend_title:'النشاط الشهري', ch_trend_sub:'آخر 12 شهر',
    ch_branch_title:'الحسابات حسب الفرع', ch_branch_sub:'الإجمالي',
    mi_rev_title:'تحتاج مراجعة',  mi_rev_empty_ok:'لا يوجد — كل الكروت ضمن الحد', mi_rev_link:'عرض الكل ←',
    mi_cc_title:'مركز الاتصال',   mi_cc_empty_ok:'لا توجد كروت بانتظار الفرع',   mi_cc_link:'عرض الكل ←',
    mi_mod_title:'آخر التعديلات', mi_mod_empty_ok:'لا توجد تعديلات حديثة',       mi_mod_link:'عرض الكل ←',
    lb_brk_title:'البروكرات', lb_brk_tab_cnt:'عدد', lb_brk_tab_dep:'إيداع',
    lb_mkt_title:'المسوّقين', lb_mkt_sub:'عدد الحسابات',
    lb_brn_title:'الفروع',    lb_brn_sub:'إجمالي الحسابات',
    labels_months_short:['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'],
    monthly_cards:'عدد الكروت', monthly_dep:'الإيداع ($)',
    accounts:'حساب', unknown:'غير محدد',
    reason_over:'تجاوز 7$', reason_dup:'مكرّر', reason_both:'تجاوز + مكرّر',
    cc_awaiting:'بانتظار الفرع', cc_accepted:'مقبول',
  },
  en: {
    pc_all:'🌐 All', pc_this:'📅 This Month', pc_3m:'3 Months', pc_6m:'6 Months', pc_ytd:'YTD',
    all_branches:'All Branches', refresh:'Refresh',
    k_total:'Total Cards', k_sub_total:'All commission records',
    k_dep:'Initial Deposit', k_sub_dep:'Sum of initial deposits',
    k_mon:'Monthly Deposit', k_sub_mon:'Sum of monthly deposits',
    k_mod:'Modified', k_sub_mod:'Cards edited after creation',
    k_new:'Recently Added', k_sub_new:'New additions',
    k_alert:'Needs Review', k_sub_alert:'Over $7 or duplicate',
    ch_trend_title:'Monthly Activity', ch_trend_sub:'Last 12 months',
    ch_branch_title:'Cards by Branch', ch_branch_sub:'Total',
    mi_rev_title:'Needs Review',  mi_rev_empty_ok:'None — all cards within limit', mi_rev_link:'View all →',
    mi_cc_title:'Call Center',    mi_cc_empty_ok:'No cards awaiting branch',       mi_cc_link:'View all →',
    mi_mod_title:'Recent Edits',  mi_mod_empty_ok:'No recent modifications',       mi_mod_link:'View all →',
    lb_brk_title:'Brokers', lb_brk_tab_cnt:'Count', lb_brk_tab_dep:'Deposit',
    lb_mkt_title:'Marketers', lb_mkt_sub:'Account count',
    lb_brn_title:'Branches',  lb_brn_sub:'Total accounts',
    labels_months_short:['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'],
    monthly_cards:'Card count', monthly_dep:'Deposit ($)',
    accounts:'accounts', unknown:'Unassigned',
    reason_over:'Over $7', reason_dup:'Duplicate', reason_both:'Over + Duplicate',
    cc_awaiting:'Awaiting branch', cc_accepted:'Accepted',
  }
};
function dL()  { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function dT(k) { return DASH[dL()]?.[k] ?? DASH.ar[k] ?? k; }

/* ── State ─────────────────────────────────────────────── */
let _period      = 'all';
let _branchId    = '';
let _branches    = [];
let _report      = null;        // snapshot of /cards/report (filtered)
let _monthly     = [];          // monthly trend from backend
let _needsReview = { data: [], summary: {} };
let _ccPending   = [];
let _mods        = [];
let _brkMetric   = 'count';     // for Brokers tab: count | dep
let _branchChart = null;
let _monthlyChart = null;
let _dashLoading = false;

/* ── Helpers ───────────────────────────────────────────── */
function monthDateRange(period) {
  const now = new Date();
  const Y = now.getFullYear(), M = now.getMonth();
  const monLabels = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const label = (d) => monLabels[d.getMonth()] + ' ' + d.getFullYear();
  switch (period) {
    case 'this': return { from: label(new Date(Y, M, 1)),       to: label(new Date(Y, M, 1)) };
    case '3m':   return { from: label(new Date(Y, M - 2, 1)),  to: label(new Date(Y, M, 1)) };
    case '6m':   return { from: label(new Date(Y, M - 5, 1)),  to: label(new Date(Y, M, 1)) };
    case 'ytd':  return { from: label(new Date(Y, 0, 1)),       to: label(new Date(Y, M, 1)) };
    default:     return { from: null, to: null };
  }
}
function brandColors(n) {
  const base = ['#1AADBA','#22C4D4','#1ECC80','#F5A828','#8A78F0','#E84545','#F06A28','#2A9D8F','#7b68ee','#0E7A88'];
  return Array.from({length: n}, (_, i) => base[i % base.length]);
}
function escH(s) {
  const esc = (typeof esc_html === 'function') ? esc_html : null;
  if (esc) return esc(s);
  // minimal
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function fmtDate(iso) {
  if (!iso) return '—';
  const lang = dL();
  try { return new Date(iso).toLocaleDateString(lang === 'en' ? 'en-GB' : 'ar-EG'); } catch(e) { return '—'; }
}

/* ── Apply bilingual labels ────────────────────────────── */
function applyDashLang() {
  const L = dL();
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };

  // period chips
  set('pc-all',  dT('pc_all'));  set('pc-this', dT('pc_this'));
  set('pc-3m',   dT('pc_3m'));   set('pc-6m',   dT('pc_6m'));
  set('pc-ytd',  dT('pc_ytd'));
  // topbar
  set('dash-opt-allbr', dT('all_branches'));
  set('dash-refresh-txt', dT('refresh'));
  // KPIs
  set('k-lbl-total', dT('k_total'));  set('k-sub-total', dT('k_sub_total'));
  set('k-lbl-dep',   dT('k_dep'));    set('k-sub-dep',   dT('k_sub_dep'));
  set('k-lbl-mon',   dT('k_mon'));    set('k-sub-mon',   dT('k_sub_mon'));
  set('k-lbl-mod',   dT('k_mod'));    set('k-sub-mod',   dT('k_sub_mod'));
  set('k-lbl-new',   dT('k_new'));    set('k-sub-new',   dT('k_sub_new'));
  set('k-lbl-alert', dT('k_alert'));  set('k-sub-alert', dT('k_sub_alert'));
  // charts
  set('ch-trend-title',  dT('ch_trend_title'));  set('ch-trend-sub', dT('ch_trend_sub'));
  set('ch-branch-title', dT('ch_branch_title'));
  // mini-sections
  set('mi-rev-title',  dT('mi_rev_title'));  set('mi-rev-link',  dT('mi_rev_link'));
  set('mi-cc-title',   dT('mi_cc_title'));   set('mi-cc-link',   dT('mi_cc_link'));
  set('mi-mod-title',  dT('mi_mod_title'));  set('mi-mod-link',  dT('mi_mod_link'));
  // leaderboards
  set('lb-brk-title', dT('lb_brk_title'));
  set('lb-brk-tab-cnt', dT('lb_brk_tab_cnt'));
  set('lb-brk-tab-dep', dT('lb_brk_tab_dep'));
  set('lb-mkt-title', dT('lb_mkt_title'));
  set('lb-mkt-sub',   dT('lb_mkt_sub'));
  set('lb-brn-title', dT('lb_brn_title'));
  set('lb-brn-sub',   dT('lb_brn_sub'));
  // Direction for RTL
  document.querySelectorAll('.dash-mini .mi-body, .dash-lb .lb-body').forEach(el => {
    el.setAttribute('dir', L === 'en' ? 'ltr' : 'rtl');
  });
  // Re-render if data loaded
  if (_report) {
    renderCharts();
    renderLeaderboards();
  }
  if (_needsReview.data) renderNeedsReview();
  if (_ccPending)        renderCcPending();
  if (_mods)             renderMods();
}

/* Hook into global applyLang */
const _dashOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_dashOrigApplyLang) _dashOrigApplyLang(lang);
  applyDashLang();
};

/* ── Period / branch controls ───────────────────────────── */
function setPeriod(p) {
  _period = p;
  document.querySelectorAll('[data-period]').forEach(b => {
    b.classList.toggle('on', b.getAttribute('data-period') === p);
  });
  reloadDash();
}
function reloadDash() {
  _branchId = document.getElementById('dash-branch-sel').value || '';
  loadAll();
}
function goTo(url) { window.location.href = url; }

/* ── Shimmer helpers ────────────────────────────────────── */
function kpiShimmerOn() {
  ['kpi-total','kpi-dep','kpi-mon','kpi-mod','kpi-new','kpi-alert'].forEach(id => {
    const el = document.getElementById(id);
    if (el) { el.classList.add('loading'); el.textContent = '—'; }
  });
}
function kpiShimmerOff() {
  document.querySelectorAll('.k-val.loading').forEach(el => el.classList.remove('loading'));
}

/* ── MAIN loader ────────────────────────────────────────── */
async function loadAll() {
  if (_dashLoading) return;
  _dashLoading = true;
  kpiShimmerOn();

  // Spin the refresh icon briefly
  const refIco = document.getElementById('dash-refresh-ico');
  if (refIco) refIco.classList.add('pulse');

  const params = new URLSearchParams();
  const rng = monthDateRange(_period);
  if (rng.from) params.set('month_from', rng.from);
  if (rng.to)   params.set('month_to',   rng.to);
  if (_branchId) params.set('branch_id', _branchId);
  params.set('per_page', 500);  // small tail — we only use aggregates from server anyway

  // Fetch main report + needs-review + CC-pending + modifications in parallel
  const [rep, revR, ccR, modR] = await Promise.all([
    api('GET', '/cards/report?' + params.toString()).catch(e => ({success:false})),
    api('GET', '/cards/needs-review?per_page=10' + (_branchId?('&branch_id='+_branchId):'')).catch(e => ({success:false})),
    api('GET', '/cc/pending').catch(e => ({success:false})),
    api('GET', '/cards/modifications').catch(e => ({success:false})),
  ]);

  if (rep && rep.success) {
    _report = rep;
  }
  if (revR && revR.success) {
    _needsReview = { data: revR.data?.data || [], summary: revR.summary || {} };
  }
  if (ccR && ccR.success) {
    _ccPending = ccR.data?.data || ccR.data || [];
  }
  if (modR && modR.success) {
    _mods = modR.data?.data || [];
  }

  // Fetch monthly trend (reuse /api/dashboard which already has it per branch)
  const dashR = await api('GET', '/dashboard' + (_branchId ? ('?branch_id=' + _branchId) : '')).catch(e => null);
  if (dashR && dashR.success) {
    _monthly = dashR.monthly_trend || [];
  }

  kpiShimmerOff();
  if (refIco) refIco.classList.remove('pulse');

  renderKPIs();
  renderCharts();
  renderNeedsReview();
  renderCcPending();
  renderMods();
  renderLeaderboards();

  _dashLoading = false;
}

/* ── KPIs ───────────────────────────────────────────────── */
function renderKPIs() {
  if (!_report) return;
  const r = _report;
  const s = r.summary || {};
  document.getElementById('kpi-total').textContent = (r.count ?? 0).toLocaleString('en');
  document.getElementById('kpi-dep').textContent   = fmtK(s.total_initial_deposit);
  document.getElementById('kpi-mon').textContent   = fmtK(s.total_monthly_deposit);
  document.getElementById('kpi-mod').textContent   = (s.modified_count ?? 0).toLocaleString('en');
  document.getElementById('kpi-new').textContent   = (s.new_added_count ?? 0).toLocaleString('en');

  const rev = _needsReview.summary || {};
  const alertCount = (rev.total ?? 0);
  const alertEl    = document.getElementById('kpi-alert');
  const trendEl    = document.getElementById('kpi-alert-trend');
  alertEl.textContent = alertCount.toLocaleString('en');
  if (trendEl) {
    if (alertCount === 0)       { trendEl.textContent = '✓'; trendEl.className = 'k-trend up'; }
    else if (alertCount <= 10)  { trendEl.textContent = '!';  trendEl.className = 'k-trend flat'; }
    else                        { trendEl.textContent = '!!'; trendEl.className = 'k-trend down'; }
  }
}

/* ── Charts: monthly trend + branches ──────────────────── */
function renderCharts() {
  const L = dL();
  // ── Branches ──
  const r = _report;
  const bySrc = Array.isArray(r?.by_branch) ? r.by_branch : [];
  const sorted = bySrc.slice().sort((a,b) => (b.count||0) - (a.count||0));
  const labels = sorted.map(x => (L==='en' ? x.name_en : x.name_ar) || x.name_ar || x.name_en || dT('unknown'));
  const vals   = sorted.map(x => x.count || 0);
  const total  = vals.reduce((a,v) => a+v, 0);
  document.getElementById('ch-branch-sub').textContent = (dT('ch_branch_sub') + ': ') + total.toLocaleString('en') + ' ' + dT('accounts');

  if (_branchChart) _branchChart.destroy();
  const ctxB = document.getElementById('chart-branches');
  if (ctxB) {
    _branchChart = new Chart(ctxB, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: dT('accounts'),
          data: vals,
          backgroundColor: brandColors(labels.length).map(c => c + 'CC'),
          borderColor:     brandColors(labels.length),
          borderWidth: 2,
          borderRadius: 10,
          hoverBackgroundColor: brandColors(labels.length),
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        indexAxis: labels.length > 6 ? 'y' : 'x',
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ' ' + ctx.raw.toLocaleString('en') + ' ' + dT('accounts') } }
        },
        scales: {
          x: { ticks:{ color:'#7AABCA', font:{size:11} }, grid:{ color:'rgba(30,54,80,.35)' } },
          y: { ticks:{ color:'#7AABCA', font:{size:11} }, grid:{ color:'rgba(30,54,80,.35)' }, beginAtZero:true }
        }
      }
    });
  }

  // ── Monthly trend ──
  // Format monthly: [ { month: '2026-09', count, initial, monthly } ]
  const monthRows = (_monthly || []).slice(-12);
  const mLabels = monthRows.map(m => {
    if (!m.month) return '';
    const [y, mm] = m.month.split('-');
    const idx = parseInt(mm, 10) - 1;
    return (DASH[L].labels_months_short[idx] || mm) + ' ' + y.slice(2);
  });
  const mCounts  = monthRows.map(m => Number(m.count || 0));
  const mDeposit = monthRows.map(m => Number(m.initial || 0));

  if (_monthlyChart) _monthlyChart.destroy();
  const ctxM = document.getElementById('chart-monthly');
  if (ctxM) {
    _monthlyChart = new Chart(ctxM, {
      type: 'bar',
      data: {
        labels: mLabels,
        datasets: [
          {
            type: 'line',
            label: dT('monthly_cards'),
            data: mCounts,
            borderColor: '#1AADBA',
            backgroundColor: 'rgba(26,173,186,.15)',
            borderWidth: 2.5,
            tension: .35,
            fill: true,
            pointRadius: 3.5,
            pointBackgroundColor: '#1AADBA',
            yAxisID: 'yC',
            order: 1,
          },
          {
            type: 'bar',
            label: dT('monthly_dep'),
            data: mDeposit,
            backgroundColor: 'rgba(30,204,128,.35)',
            borderColor: 'rgba(30,204,128,.6)',
            borderWidth: 1,
            borderRadius: 6,
            yAxisID: 'yD',
            order: 2,
          }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: {
            position: 'bottom',
            labels: { color:'#7AABCA', font:{size:11}, usePointStyle:true, boxWidth:10 }
          },
          tooltip: {
            callbacks: {
              label: ctx => {
                const v = ctx.raw;
                if (ctx.dataset.yAxisID === 'yD') return ' ' + ctx.dataset.label + ': ' + fmtK(v);
                return ' ' + ctx.dataset.label + ': ' + Number(v).toLocaleString('en');
              }
            }
          }
        },
        scales: {
          x: { ticks:{ color:'#7AABCA', font:{size:10} }, grid:{ color:'rgba(30,54,80,.25)' } },
          yC: {
            position: L==='en' ? 'left' : 'right',
            ticks: { color:'#1AADBA', font:{size:10} },
            grid:  { color:'rgba(26,173,186,.1)' },
            beginAtZero: true,
          },
          yD: {
            position: L==='en' ? 'right' : 'left',
            ticks: { color:'#1ECC80', font:{size:10}, callback: v => fmtK(v) },
            grid:  { display:false },
            beginAtZero: true,
          }
        }
      }
    });
  }
}

/* ── Needs Review (over-limit + duplicates) ────────────── */
function renderNeedsReview() {
  const L = dL();
  const data = _needsReview.data || [];
  const sum  = _needsReview.summary || {};
  const total = sum.total ?? data.length ?? 0;
  document.getElementById('mi-rev-count').textContent = total.toLocaleString('en');
  const body = document.getElementById('mi-rev-body');
  if (!body) return;
  if (!data.length) {
    body.innerHTML = `<div class="mi-empty">✓ ${dT('mi_rev_empty_ok')}</div>`;
    return;
  }
  body.innerHTML = data.slice(0, 8).map(c => {
    const kind = c.is_over_limit && c.is_duplicate ? 'both'
               : c.is_over_limit ? 'over'
               : c.is_duplicate ? 'dup' : 'over';
    const chip = kind === 'both' ? `<span class="mi-chip danger">${dT('reason_both')}</span>`
               : kind === 'over' ? `<span class="mi-chip danger">${dT('reason_over')}</span>`
               : `<span class="mi-chip warn">${dT('reason_dup')}</span>`;
    const branch = L==='en' ? (c.branch?.name_en||c.branch?.name_ar||'—') : (c.branch?.name_ar||'—');
    return `<a class="mi-row" href="/cards/${c.id}/edit">
      <span class="mi-ac">#${escH(c.account_number)}</span>
      <div class="mi-meta">
        <b>${escH(c.broker?.name || '—')}</b>
        <span>${escH(branch)} · ${escH(c.month)}</span>
      </div>
      ${chip}
    </a>`;
  }).join('');
}

/* ── CC Pending (branch inbox) ──────────────────────────── */
function renderCcPending() {
  const L = dL();
  const data = _ccPending || [];
  document.getElementById('mi-cc-count').textContent = data.length.toLocaleString('en');
  const body = document.getElementById('mi-cc-body');
  if (!body) return;
  if (!data.length) {
    body.innerHTML = `<div class="mi-empty">${dT('mi_cc_empty_ok')}</div>`;
    return;
  }
  body.innerHTML = data.slice(0, 8).map(c => {
    const chip = c.cc_status === 'accepted'
      ? `<span class="mi-chip ok">${dT('cc_accepted')}</span>`
      : `<span class="mi-chip cc">${dT('cc_awaiting')}</span>`;
    const branch = L==='en' ? (c.cc_branch?.name_en||c.cc_branch?.name_ar||'—') : (c.cc_branch?.name_ar||'—');
    return `<a class="mi-row" href="/callcenter/pending">
      <span class="mi-ac">#${escH(c.account_number)}</span>
      <div class="mi-meta">
        <b>${escH(c.cc_agent?.name || '—')}</b>
        <span>${escH(branch)} · ${escH(c.month)}</span>
      </div>
      ${chip}
    </a>`;
  }).join('');
}

/* ── Modifications ──────────────────────────────────────── */
function renderMods() {
  const rows = (_mods || []).slice(0, 8);
  document.getElementById('mi-mod-count').textContent = rows.length.toLocaleString('en');
  const body = document.getElementById('mi-mod-body');
  if (!body) return;
  if (!rows.length) {
    body.innerHTML = `<div class="mi-empty">${dT('mi_mod_empty_ok')}</div>`;
    return;
  }
  body.innerHTML = rows.map(m => `
    <a class="mi-row" href="/cards/${m.card_id}/edit">
      <span class="mi-ac">#${escH(m.account_number)}</span>
      <div class="mi-meta">
        <b>${escH(m.reason || '—')}</b>
        <span>${fmtDate(m.modified_at)} · ${escH(m.modified_by?.name || '—')}</span>
      </div>
      <span class="mi-chip warn">✏️</span>
    </a>`).join('');
}

/* ── Leaderboards: brokers / marketers / branches ──────── */
function setBrkMetric(m) {
  _brkMetric = m;
  document.querySelectorAll('.dash-lb.brokers .lb-tab').forEach(b => {
    b.classList.toggle('on', b.getAttribute('data-metric') === m);
  });
  renderBrokers();
}
function renderLeaderboards() {
  renderBrokers();
  renderMarketers();
  renderBranchesLb();
}
function renderBrokers() {
  const r = _report; if (!r) return;
  const src = Array.isArray(r.by_broker) ? r.by_broker : [];
  const sorted = _brkMetric === 'dep'
    ? src.slice().sort((a,b) => (b.monthly_deposit||0) - (a.monthly_deposit||0))
    : src.slice().sort((a,b) => (b.count||0) - (a.count||0));
  const rows = sorted.slice(0, 10);
  const maxV = rows[0] ? (_brkMetric === 'dep' ? rows[0].monthly_deposit : rows[0].count) : 1;
  const body = document.getElementById('lb-brokers');
  if (!body) return;
  if (!rows.length) {
    body.innerHTML = `<div class="lb-empty">—</div>`;
    return;
  }
  const medals = ['🥇','🥈','🥉'];
  body.innerHTML = rows.map((x, i) => {
    const val = _brkMetric === 'dep' ? (x.monthly_deposit || 0) : (x.count || 0);
    const display = _brkMetric === 'dep' ? fmtK(val) : val.toLocaleString('en');
    const pct = maxV ? Math.max(2, Math.round(val / maxV * 100)) : 0;
    return `<div class="lb-item">
      <div class="lb-rank">${medals[i] || (i+1)}</div>
      <div class="lb-info">
        <b>${escH(x.name || '—')}</b>
        <div class="lb-sub">${_brkMetric==='dep' ? dT('monthly_dep') : dT('accounts')}</div>
      </div>
      <div class="lb-val">${display}</div>
      <div class="lb-bar-wrap"><div class="lb-bar-fill" style="width:${pct}%"></div></div>
    </div>`;
  }).join('');
}
function renderMarketers() {
  const r = _report; if (!r) return;
  const src = Array.isArray(r.by_marketer) ? r.by_marketer : [];
  const rows = src.slice().sort((a,b) => (b.count||0) - (a.count||0)).slice(0, 10);
  const body = document.getElementById('lb-marketers');
  const maxV = rows[0]?.count || 1;
  if (!body) return;
  if (!rows.length) { body.innerHTML = `<div class="lb-empty">—</div>`; return; }
  const medals = ['🥇','🥈','🥉'];
  body.innerHTML = rows.map((x, i) => {
    const pct = Math.max(2, Math.round((x.count || 0) / maxV * 100));
    return `<div class="lb-item">
      <div class="lb-rank">${medals[i] || (i+1)}</div>
      <div class="lb-info">
        <b>${escH(x.name || '—')}</b>
        <div class="lb-sub">${dT('accounts')}</div>
      </div>
      <div class="lb-val">${(x.count || 0).toLocaleString('en')}</div>
      <div class="lb-bar-wrap"><div class="lb-bar-fill" style="width:${pct}%"></div></div>
    </div>`;
  }).join('');
}
function renderBranchesLb() {
  const L = dL();
  const r = _report; if (!r) return;
  const src = Array.isArray(r.by_branch) ? r.by_branch : [];
  const rows = src.slice().sort((a,b) => (b.count||0) - (a.count||0));
  const body = document.getElementById('lb-branches');
  const maxV = rows[0]?.count || 1;
  if (!body) return;
  if (!rows.length) { body.innerHTML = `<div class="lb-empty">—</div>`; return; }
  const medals = ['🥇','🥈','🥉'];
  body.innerHTML = rows.map((x, i) => {
    const name = (L==='en' ? x.name_en : x.name_ar) || x.name_ar || x.name_en || dT('unknown');
    const pct = Math.max(2, Math.round((x.count || 0) / maxV * 100));
    return `<div class="lb-item">
      <div class="lb-rank">${medals[i] || (i+1)}</div>
      <div class="lb-info">
        <b>${escH(name)}</b>
        <div class="lb-sub">${dT('accounts')}</div>
      </div>
      <div class="lb-val">${(x.count || 0).toLocaleString('en')}</div>
      <div class="lb-bar-wrap"><div class="lb-bar-fill" style="width:${pct}%"></div></div>
    </div>`;
  }).join('');
}

/* ── Load branch filter options ────────────────────────── */
async function loadBranchOptions() {
  try {
    const r = await api('GET', '/branches');
    if (!r.success) return;
    _branches = r.data || [];
    const sel = document.getElementById('dash-branch-sel');
    if (!sel) return;
    const L = dL();
    const cur = sel.value;
    sel.innerHTML = `<option value="">${dT('all_branches')}</option>`;
    _branches.forEach(b => {
      const o = document.createElement('option');
      o.value = b.id;
      o.textContent = (L==='en' ? b.name_en : b.name_ar) || b.name_ar || b.name_en;
      sel.appendChild(o);
    });
    sel.value = cur;
  } catch (e) {}
}

/* ── Init ──────────────────────────────────────────────── */
(async function initDash() {
  applyDashLang();
  await loadBranchOptions();
  await loadAll();
})();
</script>
@endpush
