@extends('layouts.app')
@section('title', 'لوحة المتابعة')
@section('page-title', 'لوحة المتابعة')

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
    },
    en: {
      'total': 'Total: ', 'account': ' accounts',
      'accounts': 'Accounts', 'unknown': 'Unassigned',
      'nodata': 'No data available', 'nomkt': 'No marketers found',
      'noedit': 'No modifications', 'nocc': 'No pending CC cards',
      'pending': ' pending', 'review': 'Under Review',
    },
  };
  const lang = (typeof curLang !== 'undefined' ? curLang : 'ar');
  return D[lang]?.[key] ?? D.ar[key] ?? key;
}

async function loadDashboard() {
  const r = await api('GET', '/cards/report');
  if (!r.success) return;
  const s = r.summary;
  const lang = (typeof curLang !== 'undefined' ? curLang : 'ar');
  const locale = lang === 'en' ? 'en-US' : 'ar-EG';

  document.getElementById('kpi-total').textContent = r.count.toLocaleString('en');
  document.getElementById('kpi-dep').textContent   = fmtK(s.total_initial_deposit);
  document.getElementById('kpi-mon').textContent   = fmtK(s.total_monthly_deposit);
  document.getElementById('kpi-mod').textContent   = s.modified_count;
  document.getElementById('kpi-new').textContent   = s.new_added_count;

  document.getElementById('sb-cards-count').textContent = r.count;
  if (document.getElementById('sb-mod-count'))
    document.getElementById('sb-mod-count').textContent = s.modified_count;

  const data = r.data || [];

  // ── Per-branch count chart ──
  const branchMap = {};
  data.forEach(row => {
    const name = (lang === 'en' ? row.branch?.name_en : row.branch?.name_ar)
               || row.branch?.name_ar || row.branch?.name_en || d('unknown');
    branchMap[name] = (branchMap[name] || 0) + 1;
  });
  const bLabels = Object.keys(branchMap).sort((a,b) => branchMap[b] - branchMap[a]);
  const bVals   = bLabels.map(b => branchMap[b]);
  const totalBr = bVals.reduce((a,v) => a+v, 0);
  document.getElementById('branch-total-lbl').textContent = d('total') + totalBr.toLocaleString('en') + d('account');

  if (branchChart) branchChart.destroy();
  branchChart = new Chart(document.getElementById('chart-branches'), {
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

  // ── Broker count table ──
  const brokerCount = {};
  data.forEach(row => {
    const n = row.broker?.name || d('unknown');
    brokerCount[n] = (brokerCount[n] || 0) + 1;
  });
  const sortedBrCnt = Object.entries(brokerCount).sort((a,b) => b[1] - a[1]).slice(0, 10);
  const medals = ['🥇','🥈','🥉','4','5','6','7','8','9','10'];
  document.getElementById('broker-cnt-tb').innerHTML = sortedBrCnt.map(([name,cnt],i) => `
    <tr>
      <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
      <td style="font-weight:700">${esc(name)}</td>
      <td class="mono c-teal" style="font-weight:800">${cnt.toLocaleString('en')}</td>
    </tr>`).join('') || `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nodata')}</td></tr>`;

  // ── Marketer count table ──
  const mktMap = {};
  data.forEach(row => {
    if (row.marketer?.name) {
      mktMap[row.marketer.name] = (mktMap[row.marketer.name] || 0) + 1;
    }
  });
  const sortedMkt = Object.entries(mktMap).sort((a,b) => b[1] - a[1]).slice(0, 10);
  document.getElementById('marketer-cnt-tb').innerHTML = sortedMkt.length
    ? sortedMkt.map(([name,cnt],i) => `
      <tr>
        <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
        <td style="font-weight:700">${esc(name)}</td>
        <td class="mono c-green" style="font-weight:800">${cnt.toLocaleString('en')}</td>
      </tr>`).join('')
    : `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nomkt')}</td></tr>`;

  // ── Broker deposit table ──
  const brokerDep = {};
  data.forEach(row => {
    const n = row.broker?.name || d('unknown');
    brokerDep[n] = (brokerDep[n] || 0) + parseFloat(row.monthly_deposit || 0);
  });
  const sortedBrDep = Object.entries(brokerDep).sort((a,b) => b[1] - a[1]).slice(0, 10);
  document.getElementById('broker-dep-tb').innerHTML = sortedBrDep.map(([name,dep],i) => `
    <tr>
      <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
      <td style="font-weight:700">${esc(name)}</td>
      <td class="mono c-green" style="font-weight:800">${fmtK(dep)}</td>
    </tr>`).join('') || `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nodata')}</td></tr>`;
}

// ── CC Pending ──
async function loadCcPending() {
  const r = await api('GET', '/cc/pending');
  if (!r.success) return;
  const items = r.data?.data || r.items || [];
  const count = r.count ?? items.length;
  const lang = (typeof curLang !== 'undefined' ? curLang : 'ar');
  if (count > 0) {
    const badge = document.getElementById('cc-badge');
    badge.textContent = count + d('pending');
    badge.style.display = '';
  }
  document.getElementById('cc-tb').innerHTML = items.slice(0, 6).map(cc => `
    <tr>
      <td><span class="ac-num">#${esc(String(cc.account_number || '—'))}</span></td>
      <td><span class="badge badge-orange">${esc(cc.cc_status || d('review'))}</span></td>
      <td style="color:var(--mu);font-size:13px">${cc.created_at ? new Date(cc.created_at).toLocaleDateString(lang === 'en' ? 'en-GB' : 'ar-EG') : '—'}</td>
    </tr>`).join('') || `<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">${d('nocc')}</td></tr>`;
}

// ── Modifications ──
async function loadModifications() {
  const r = await api('GET', '/cards/modifications');
  if (!r.success) return;
  const lang = (typeof curLang !== 'undefined' ? curLang : 'ar');
  const rows = (r.data?.data || []).slice(0, 8);
  document.getElementById('modifications-tb').innerHTML = rows.map(m => `
    <tr>
      <td><span class="ac-num">#${esc(String(m.account_number))}</span></td>
      <td style="color:var(--or);font-size:13px">${esc(m.reason)}</td>
      <td style="color:var(--mu)">${new Date(m.modified_at).toLocaleDateString(lang === 'en' ? 'en-GB' : 'ar-EG')}</td>
      <td style="color:var(--mu)">${esc(m.modified_by?.name || '—')}</td>
    </tr>`).join('') || `<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">${d('noedit')}</td></tr>`;
}

loadDashboard();
loadModifications();
loadCcPending();

/* Re-render when language is toggled (re-runs fetches with new lang) */
const _dashOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_dashOrigApplyLang) _dashOrigApplyLang(lang);
  /* Re-render dynamic JS content with new language */
  loadDashboard();
  loadCcPending();
  loadModifications();
};
</script>
@endpush
