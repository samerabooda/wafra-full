@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<!-- KPI Cards -->
<div class="kpi-grid" id="kpi-grid">
  <div class="kpi-card kpi-blue">
    <div class="kpi-label" data-i18n="dash.kpi.total">إجمالي الحسابات</div>
    <div class="kpi-value" id="kpi-total">—</div>
    <div class="kpi-sub" data-i18n="dash.kpi.total.sub">سجل مسجّل</div>
    <div class="kpi-icon">📁</div>
  </div>
  <div class="kpi-card kpi-teal">
    <div class="kpi-label" data-i18n="dash.kpi.dep">إيداع فتح الحساب</div>
    <div class="kpi-value" id="kpi-dep">—</div>
    <div class="kpi-sub" data-i18n="dash.kpi.dep.sub">إجمالي الإيداع الأولي</div>
    <div class="kpi-icon">💵</div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-label" data-i18n="dash.kpi.mon">الإيداع الشهري المتوقع</div>
    <div class="kpi-value" id="kpi-mon">—</div>
    <div class="kpi-sub" data-i18n="dash.kpi.mon.sub">إجمالي الإيداع الشهري</div>
    <div class="kpi-icon">📈</div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-label" data-i18n="dash.kpi.mod">حسابات معدّلة</div>
    <div class="kpi-value" id="kpi-mod">—</div>
    <div class="kpi-sub" data-i18n="dash.kpi.mod.sub">حسابات معدّلة</div>
    <div class="kpi-icon">✏️</div>
  </div>
  <div class="kpi-card kpi-purple">
    <div class="kpi-label" data-i18n="dash.kpi.new">كروت مضافة حديثاً</div>
    <div class="kpi-value" id="kpi-new">—</div>
    <div class="kpi-sub" data-i18n="dash.kpi.new.sub">مضافة حديثاً</div>
    <div class="kpi-icon">🆕</div>
  </div>
  <a href="/cards?filter=needs_review" class="kpi-card" style="background:linear-gradient(135deg,rgba(224,72,72,.08),rgba(224,72,72,.03));border:1px solid rgba(224,72,72,.3);text-decoration:none;color:inherit;cursor:pointer">
    <div class="kpi-label" data-i18n="dash.kpi.rev" style="color:#e04848">تحتاج مراجعة</div>
    <div class="kpi-value" id="kpi-rev" style="color:#e04848">—</div>
    <div class="kpi-sub" data-i18n="dash.kpi.rev.sub">تجاوز 7$ أو مكرّر</div>
    <div class="kpi-icon">⚠️</div>
  </a>
</div>

<!-- Branches Chart -->
<div class="panel" style="margin-bottom:20px">
  <div class="panel-header">
    <div class="panel-title" data-i18n="dash.chart.branches">🏢 عدد الحسابات المفتوحة لكل فرع</div>
    <span style="font-size:13px;color:var(--mu)" id="branch-total-lbl"></span>
  </div>
  <div class="panel-body" style="height:240px;position:relative">
    <canvas id="chart-branches"></canvas>
  </div>
</div>

<!-- Broker & Marketer Reports -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px" class="sgd">
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title" data-i18n="dash.broker.cnt.title">🥇 تقرير البروكرات — عدد الحسابات</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>#</th>
          <th data-i18n="dash.th.broker">البروكر</th>
          <th data-i18n="dash.th.cnt">عدد الحسابات</th>
        </tr></thead>
        <tbody id="broker-cnt-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title" data-i18n="dash.mkt.title">📢 تقرير المسوّقين</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>#</th>
          <th data-i18n="dash.th.mkt">المسوّق</th>
          <th data-i18n="dash.th.cnt">عدد الحسابات</th>
        </tr></thead>
        <tbody id="marketer-cnt-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header">
      <div class="panel-title" data-i18n="dash.cc.title">📞 مركز الاتصال — CC</div>
      <span class="badge badge-purple" id="cc-badge" style="display:none"></span>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th data-i18n="dash.th.acno">رقم الحساب</th>
          <th data-i18n="dash.th.status">الحالة</th>
          <th data-i18n="dash.th.date">التاريخ</th>
        </tr></thead>
        <tbody id="cc-tb"></tbody>
      </table>
    </div>
    <div style="padding:10px 20px;border-top:1px solid var(--brd1)">
      <a href="{{ route('callcenter.pending') }}" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center" data-i18n="dash.cc.viewall">عرض الكل ←</a>
    </div>
  </div>
</div>

<!-- Recent Modifications & Broker Deposits -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="sgd">
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title" data-i18n="dash.broker.dep.title">💰 البروكرات — إجمالي الإيداع الشهري</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>#</th>
          <th data-i18n="dash.th.broker">البروكر</th>
          <th data-i18n="dash.th.dep">إيداع شهري</th>
        </tr></thead>
        <tbody id="broker-dep-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title" data-i18n="dash.mods.title">✏️ آخر التعديلات</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th data-i18n="dash.th.acno">رقم الحساب</th>
          <th data-i18n="dash.th.reason">السبب</th>
          <th data-i18n="dash.th.date">التاريخ</th>
          <th data-i18n="dash.th.by">بواسطة</th>
        </tr></thead>
        <tbody id="modifications-tb"></tbody>
      </table>
    </div>
  </div>
</div>

<!-- Needs Review: Duplicates + Over-$7 -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:16px" class="sgd">
  <div class="panel" style="margin-bottom:0;border:1px solid rgba(224,72,72,.25)">
    <div class="panel-header" style="background:linear-gradient(135deg,rgba(224,72,72,.08),transparent)">
      <div class="panel-title" data-i18n="dash.rev.over.title">⚠️ حسابات تجاوزت 7$ عمولة</div>
      <span class="badge" id="rev-over-badge" style="background:rgba(224,72,72,.15);color:#e04848;border:1px solid rgba(224,72,72,.3);display:none">0</span>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th data-i18n="dash.th.acno">رقم الحساب</th>
          <th data-i18n="dash.th.broker">البروكر</th>
          <th data-i18n="dash.th.comm">عمولة</th>
          <th data-i18n="dash.th.month">الشهر</th>
        </tr></thead>
        <tbody id="rev-over-tb"></tbody>
      </table>
    </div>
    <div style="padding:10px 20px;border-top:1px solid var(--brd1)">
      <a href="/cards?filter=needs_review" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center" data-i18n="dash.rev.viewall">عرض الكل ←</a>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0;border:1px solid rgba(245,152,32,.25)">
    <div class="panel-header" style="background:linear-gradient(135deg,rgba(245,152,32,.08),transparent)">
      <div class="panel-title" data-i18n="dash.rev.dup.title">🔁 الحسابات المكررة</div>
      <span class="badge" id="rev-dup-badge" style="background:rgba(245,152,32,.15);color:#f59820;border:1px solid rgba(245,152,32,.3);display:none">0</span>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th data-i18n="dash.th.acno">رقم الحساب</th>
          <th data-i18n="dash.th.month">الشهر</th>
          <th data-i18n="dash.th.branch">الفرع</th>
          <th data-i18n="dash.th.broker">البروكر</th>
        </tr></thead>
        <tbody id="rev-dup-tb"></tbody>
      </table>
    </div>
    <div style="padding:10px 20px;border-top:1px solid var(--brd1)">
      <a href="/cards?filter=needs_review" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center" data-i18n="dash.rev.viewall">عرض الكل ←</a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let branchChart = null;
const COLORS = ['#1AADBA','#22C4D4','#1ECC80','#F5A828','#8A78F0','#E84545','#F06A28','#2A9D8F'];

/* Dashboard i18n strings — respects curLang */
function d(key) {
  const D = {
    ar: {
      'total': 'الإجمالي: ', 'account': ' حساب',
      'accounts': 'عدد الحسابات', 'unknown': 'غير محدد',
      'nodata': 'لا توجد بيانات', 'nomkt': 'لا يوجد مسوّقون',
      'noedit': 'لا توجد تعديلات', 'nocc': 'لا توجد كروت CC معلّقة',
      'pending': ' معلّق', 'review': 'قيد المراجعة',
      'empty_ok': 'لا يوجد',
    },
    en: {
      'total': 'Total: ', 'account': ' accounts',
      'accounts': 'Accounts', 'unknown': 'Unassigned',
      'nodata': 'No data available', 'nomkt': 'No marketers found',
      'noedit': 'No modifications', 'nocc': 'No pending CC cards',
      'pending': ' pending', 'review': 'Under Review',
      'empty_ok': 'None',
    },
  };
  const lang = (typeof curLang !== 'undefined' ? curLang : 'ar');
  return D[lang]?.[key] ?? D.ar[key] ?? key;
}

/* ──────────────────────────────────────────────────────────
   ONE endpoint — /api/dashboard — serves every tile below.
   Available to every authenticated user (FA, branch manager,
   viewer) with no permission:* middleware. Branch-scoped
   users get auto-filtered to their branch_id.
   ────────────────────────────────────────────────────────── */
async function loadDashboard() {
  const r = await api('GET', '/dashboard');
  if (!r || !r.success) {
    const msg = r?.message || 'تعذّر تحميل لوحة المتابعة';
    if (typeof toast === 'function') toast(msg, 'error');
    return;
  }
  const lang = (typeof curLang !== 'undefined' ? curLang : 'ar');
  const k    = r.kpis || {};

  // ── KPIs ──
  document.getElementById('kpi-total').textContent = Number(k.total_cards || 0).toLocaleString('en');
  document.getElementById('kpi-dep').textContent   = fmtK(k.total_initial_dep);
  document.getElementById('kpi-mon').textContent   = fmtK(k.total_monthly_dep);
  document.getElementById('kpi-mod').textContent   = Number(k.modified_count || 0).toLocaleString('en');
  document.getElementById('kpi-new').textContent   = Number(k.new_added_count || 0).toLocaleString('en');
  document.getElementById('kpi-rev').textContent   = Number(k.needs_review_count || 0).toLocaleString('en');

  const sbCards = document.getElementById('sb-cards-count');
  if (sbCards) sbCards.textContent = Number(k.total_cards || 0).toLocaleString('en');
  const sbMod = document.getElementById('sb-mod-count');
  if (sbMod) sbMod.textContent = Number(k.modified_count || 0).toLocaleString('en');

  // ── Branches chart ──
  const branches = r.by_branch || [];
  const bLabels  = branches.map(b => (lang === 'en' ? b.name_en : b.name_ar) || b.name_ar || b.name_en || d('unknown'));
  const bVals    = branches.map(b => Number(b.count || 0));
  const totalBr  = bVals.reduce((a,v) => a+v, 0);
  const branchTotalEl = document.getElementById('branch-total-lbl');
  if (branchTotalEl) branchTotalEl.textContent = d('total') + totalBr.toLocaleString('en') + d('account');

  if (branchChart) branchChart.destroy();
  const ctxB = document.getElementById('chart-branches');
  if (ctxB) {
    branchChart = new Chart(ctxB, {
      type: 'bar',
      data: {
        labels: bLabels,
        datasets: [{
          label: d('accounts'),
          data: bVals,
          backgroundColor: bLabels.map((_,i) => COLORS[i % COLORS.length] + 'CC'),
          borderColor:     bLabels.map((_,i) => COLORS[i % COLORS.length]),
          borderWidth: 2,
          borderRadius: 8,
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, indexAxis: bLabels.length > 6 ? 'y' : 'x',
        plugins: {
          legend: { display: false },
          tooltip: { callbacks: { label: ctx => ' ' + ctx.raw.toLocaleString('en') + ' ' + d('account').trim() } }
        },
        scales: {
          x: { ticks:{color:'#7AABCA',font:{size:12}}, grid:{color:'rgba(30,54,80,.5)'} },
          y: { ticks:{color:'#7AABCA',font:{size:12}}, grid:{color:'rgba(30,54,80,.5)'}, beginAtZero:true }
        }
      }
    });
  }

  const medals = ['🥇','🥈','🥉','4','5','6','7','8','9','10'];

  // ── Broker count table ──
  const brokers = r.top_brokers || [];
  const brkCntEl = document.getElementById('broker-cnt-tb');
  if (brkCntEl) {
    brkCntEl.innerHTML = brokers.length ? brokers.slice(0,10).map((x,i) => `
      <tr>
        <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
        <td style="font-weight:700">${esc(x.name)}</td>
        <td class="mono c-teal" style="font-weight:800">${Number(x.count||0).toLocaleString('en')}</td>
      </tr>`).join('')
      : `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nodata')}</td></tr>`;
  }

  // ── Marketer count table ──
  const markters = r.top_marketers || [];
  const mktEl = document.getElementById('marketer-cnt-tb');
  if (mktEl) {
    mktEl.innerHTML = markters.length ? markters.slice(0,10).map((x,i) => `
      <tr>
        <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
        <td style="font-weight:700">${esc(x.name)}</td>
        <td class="mono c-green" style="font-weight:800">${Number(x.count||0).toLocaleString('en')}</td>
      </tr>`).join('')
      : `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nomkt')}</td></tr>`;
  }

  // ── CC Pending ──
  const ccPending = r.cc_pending || [];
  const ccCount   = Number(k.cc_pending_count || 0);
  const ccBadge   = document.getElementById('cc-badge');
  if (ccBadge) {
    if (ccCount > 0) { ccBadge.textContent = ccCount + d('pending'); ccBadge.style.display = ''; }
    else ccBadge.style.display = 'none';
  }
  const ccTB = document.getElementById('cc-tb');
  if (ccTB) {
    ccTB.innerHTML = ccPending.length ? ccPending.slice(0,6).map(c => `
      <tr>
        <td><span class="ac-num">#${esc(String(c.account_number || '—'))}</span></td>
        <td><span class="badge badge-orange">${esc(c.cc_status || d('review'))}</span></td>
        <td style="color:var(--mu);font-size:13px">${c.created_at ? new Date(c.created_at).toLocaleDateString(lang === 'en' ? 'en-GB' : 'ar-EG') : '—'}</td>
      </tr>`).join('')
      : `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nocc')}</td></tr>`;
  }

  // ── Broker deposit table (reusing top_brokers sorted by deposit) ──
  const brkDepSorted = brokers.slice().sort((a,b) => Number(b.monthly_deposit||0) - Number(a.monthly_deposit||0));
  const brkDepEl = document.getElementById('broker-dep-tb');
  if (brkDepEl) {
    brkDepEl.innerHTML = brkDepSorted.length ? brkDepSorted.slice(0,10).map((x,i) => `
      <tr>
        <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
        <td style="font-weight:700">${esc(x.name)}</td>
        <td class="mono c-green" style="font-weight:800">${fmtK(x.monthly_deposit)}</td>
      </tr>`).join('')
      : `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nodata')}</td></tr>`;
  }

  // ── Recent Modifications ──
  const mods = r.recent_modifications || [];
  const modEl = document.getElementById('modifications-tb');
  if (modEl) {
    modEl.innerHTML = mods.length ? mods.slice(0,8).map(m => `
      <tr>
        <td><span class="ac-num">#${esc(String(m.account_number))}</span></td>
        <td style="color:var(--or);font-size:13px">${esc(m.reason)}</td>
        <td style="color:var(--mu)">${m.modified_at ? new Date(m.modified_at).toLocaleDateString(lang === 'en' ? 'en-GB' : 'ar-EG') : '—'}</td>
        <td style="color:var(--mu)">${esc(m.modified_by?.name || '—')}</td>
      </tr>`).join('')
      : `<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">${d('noedit')}</td></tr>`;
  }

  // ── Over-$7 rows ──
  const overRows = r.over_limit_rows || [];
  const overBadge = document.getElementById('rev-over-badge');
  if (overBadge) {
    if (k.over_limit_count > 0) { overBadge.textContent = k.over_limit_count; overBadge.style.display = ''; }
    else overBadge.style.display = 'none';
  }
  const overTB = document.getElementById('rev-over-tb');
  if (overTB) {
    overTB.innerHTML = overRows.length ? overRows.map(c => {
      const tot = Number(c.broker_commission||0)+Number(c.marketer_commission||0)+Number(c.ext_commission1||0)+Number(c.ext_commission2||0);
      return `<tr>
        <td><a href="/cards/${c.id}/edit" style="color:var(--pri2);text-decoration:none"><span class="ac-num">#${esc(c.account_number)}</span></a></td>
        <td style="font-size:12px">${esc(c.broker_name || '—')}</td>
        <td class="mono" style="color:#e04848;font-weight:800">$${tot.toFixed(1)}</td>
        <td style="color:var(--mu);font-size:11px">${esc(c.month)}</td>
      </tr>`;
    }).join('')
    : `<tr><td colspan="4" style="text-align:center;padding:18px;color:var(--mu);font-size:12px">${d('empty_ok')}</td></tr>`;
  }

  // ── Duplicate rows ──
  const dupRows = r.duplicate_rows || [];
  const dupBadge = document.getElementById('rev-dup-badge');
  if (dupBadge) {
    if (k.duplicate_count > 0) { dupBadge.textContent = k.duplicate_count; dupBadge.style.display = ''; }
    else dupBadge.style.display = 'none';
  }
  const dupTB = document.getElementById('rev-dup-tb');
  if (dupTB) {
    dupTB.innerHTML = dupRows.length ? dupRows.map(c => {
      const brN = lang==='en' ? (c.branch_name_en||c.branch_name_ar||'—') : (c.branch_name_ar||'—');
      return `<tr>
        <td><a href="/cards/${c.id}/edit" style="color:var(--pri2);text-decoration:none"><span class="ac-num">#${esc(c.account_number)}</span></a></td>
        <td style="color:var(--mu);font-size:11px">${esc(c.month)}</td>
        <td style="font-size:11px">${esc(brN)}</td>
        <td style="font-size:12px">${esc(c.broker_name || '—')}</td>
      </tr>`;
    }).join('')
    : `<tr><td colspan="4" style="text-align:center;padding:18px;color:var(--mu);font-size:12px">${d('empty_ok')}</td></tr>`;
  }
}

loadDashboard();

/* Re-render when language is toggled */
const _dashOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_dashOrigApplyLang) _dashOrigApplyLang(lang);
  loadDashboard();
};
</script>
@endpush
