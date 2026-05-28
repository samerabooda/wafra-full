@extends('layouts.app')
@section('title', 'لوحة المتابعة')
@section('page-title', 'لوحة المتابعة')

@section('content')
<!-- KPI Cards -->
<div class="kpi-grid" id="kpi-grid">
  <div class="kpi-card kpi-blue">
    <div class="kpi-label">إجمالي الحسابات</div>
    <div class="kpi-value" id="kpi-total">—</div>
    <div class="kpi-sub">سجل مسجّل</div>
    <div class="kpi-icon">📁</div>
  </div>
  <div class="kpi-card kpi-teal">
    <div class="kpi-label">إيداع فتح الحساب</div>
    <div class="kpi-value" id="kpi-dep">—</div>
    <div class="kpi-sub">إجمالي الإيداع الأولي</div>
    <div class="kpi-icon">💵</div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-label">الإيداع الشهري المتوقع</div>
    <div class="kpi-value" id="kpi-mon">—</div>
    <div class="kpi-sub">إجمالي الإيداع الشهري</div>
    <div class="kpi-icon">📈</div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-label">حسابات معدّلة</div>
    <div class="kpi-value" id="kpi-mod">—</div>
    <div class="kpi-sub">Modified</div>
    <div class="kpi-icon">✏️</div>
  </div>
  <div class="kpi-card kpi-purple">
    <div class="kpi-label">كروت مضافة حديثاً</div>
    <div class="kpi-value" id="kpi-new">—</div>
    <div class="kpi-sub">Newly Added</div>
    <div class="kpi-icon">🆕</div>
  </div>
</div>

<!-- Branches Chart -->
<div class="panel" style="margin-bottom:20px">
  <div class="panel-header">
    <div class="panel-title">🏢 عدد الحسابات المفتوحة لكل فرع</div>
    <span style="font-size:13px;color:var(--mu)" id="branch-total-lbl"></span>
  </div>
  <div class="panel-body" style="height:240px;position:relative">
    <canvas id="chart-branches"></canvas>
  </div>
</div>

<!-- Broker & Marketer Reports -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:20px">
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title">🥇 تقرير البروكرات — عدد الحسابات</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>#</th><th>البروكر</th><th>عدد الحسابات</th></tr></thead>
        <tbody id="broker-cnt-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title">📢 تقرير المسوّقين</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>#</th><th>المسوّق</th><th>عدد الحسابات</th></tr></thead>
        <tbody id="marketer-cnt-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header">
      <div class="panel-title">📞 مركز الاتصال — CC</div>
      <span class="badge badge-purple" id="cc-badge" style="display:none"></span>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>رقم الحساب</th><th>الحالة</th><th>التاريخ</th></tr></thead>
        <tbody id="cc-tb"></tbody>
      </table>
    </div>
    <div style="padding:10px 20px;border-top:1px solid var(--brd1)">
      <a href="{{ route('callcenter.pending') }}" class="btn btn-ghost btn-sm" style="width:100%;justify-content:center">عرض الكل ←</a>
    </div>
  </div>
</div>

<!-- Recent Modifications & Broker Deposits -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title">💰 البروكرات — إجمالي الإيداع الشهري</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>#</th><th>البروكر</th><th>إيداع شهري</th></tr></thead>
        <tbody id="broker-dep-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel" style="margin-bottom:0">
    <div class="panel-header"><div class="panel-title">✏️ آخر التعديلات</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>رقم الحساب</th><th>السبب</th><th>التاريخ</th><th>بواسطة</th></tr></thead>
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

async function loadDashboard() {
  const r = await api('GET', '/cards/report');
  if (!r.success) return;
  const s = r.summary;

  document.getElementById('kpi-total').textContent = r.count.toLocaleString('ar');
  document.getElementById('kpi-dep').textContent   = fmtK(s.total_initial_deposit);
  document.getElementById('kpi-mon').textContent   = fmtK(s.total_monthly_deposit);
  document.getElementById('kpi-mod').textContent   = s.modified_count;
  document.getElementById('kpi-new').textContent   = s.new_added_count;

  document.getElementById('sb-cards-count').textContent = r.count;
  document.getElementById('sb-mod-count').textContent   = s.modified_count;

  const data = r.data || [];

  // ── Per-branch count chart ──
  const branchMap = {};
  data.forEach(row => {
    const name = row.branch?.name_ar || row.branch?.name_en || 'غير محدد';
    branchMap[name] = (branchMap[name] || 0) + 1;
  });
  const bLabels = Object.keys(branchMap).sort((a,b) => branchMap[b] - branchMap[a]);
  const bVals   = bLabels.map(b => branchMap[b]);
  const totalBr = bVals.reduce((a,v) => a+v, 0);
  document.getElementById('branch-total-lbl').textContent = 'الإجمالي: ' + totalBr.toLocaleString('ar') + ' حساب';

  if (branchChart) branchChart.destroy();
  branchChart = new Chart(document.getElementById('chart-branches'), {
    type: 'bar',
    data: {
      labels: bLabels,
      datasets: [{
        label: 'عدد الحسابات',
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
        tooltip: { callbacks: { label: ctx => ' ' + ctx.raw.toLocaleString('ar') + ' حساب' } }
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
    const n = row.broker?.name || 'غير محدد';
    brokerCount[n] = (brokerCount[n] || 0) + 1;
  });
  const sortedBrCnt = Object.entries(brokerCount).sort((a,b) => b[1] - a[1]).slice(0, 10);
  const medals = ['🥇','🥈','🥉','4','5','6','7','8','9','10'];
  document.getElementById('broker-cnt-tb').innerHTML = sortedBrCnt.map(([name,cnt],i) => `
    <tr>
      <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
      <td style="font-weight:700">${esc(name)}</td>
      <td class="mono c-teal" style="font-weight:800">${cnt.toLocaleString('ar')}</td>
    </tr>`).join('') || '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">لا توجد بيانات</td></tr>';

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
        <td class="mono c-green" style="font-weight:800">${cnt.toLocaleString('ar')}</td>
      </tr>`).join('')
    : '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">لا يوجد مسوّقون</td></tr>';

  // ── Broker deposit table ──
  const brokerDep = {};
  data.forEach(row => {
    const n = row.broker?.name || 'غير محدد';
    brokerDep[n] = (brokerDep[n] || 0) + parseFloat(row.monthly_deposit || 0);
  });
  const sortedBrDep = Object.entries(brokerDep).sort((a,b) => b[1] - a[1]).slice(0, 10);
  document.getElementById('broker-dep-tb').innerHTML = sortedBrDep.map(([name,dep],i) => `
    <tr>
      <td style="font-size:16px;width:36px">${medals[i]||i+1}</td>
      <td style="font-weight:700">${esc(name)}</td>
      <td class="mono c-green" style="font-weight:800">${fmtK(dep)}</td>
    </tr>`).join('') || '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">لا توجد بيانات</td></tr>';
}

// ── CC Pending ──
async function loadCcPending() {
  const r = await api('GET', '/cc/pending');
  if (!r.success) return;
  const items = r.data?.data || r.items || [];
  const count = r.count ?? items.length;
  if (count > 0) {
    const badge = document.getElementById('cc-badge');
    badge.textContent = count + ' معلّق';
    badge.style.display = '';
  }
  document.getElementById('cc-tb').innerHTML = items.slice(0, 6).map(cc => `
    <tr>
      <td><span class="ac-num">#${esc(String(cc.account_number || '—'))}</span></td>
      <td><span class="badge badge-orange">${esc(cc.cc_status || 'قيد المراجعة')}</span></td>
      <td style="color:var(--mu);font-size:13px">${cc.created_at ? new Date(cc.created_at).toLocaleDateString('ar') : '—'}</td>
    </tr>`).join('') || '<tr><td colspan="3" style="text-align:center;padding:20px;color:var(--mu)">لا توجد كروت CC معلّقة</td></tr>';
}

// ── Modifications ──
async function loadModifications() {
  const r = await api('GET', '/cards/modifications');
  if (!r.success) return;
  const rows = (r.data?.data || []).slice(0, 8);
  document.getElementById('modifications-tb').innerHTML = rows.map(m => `
    <tr>
      <td><span class="ac-num">#${esc(String(m.account_number))}</span></td>
      <td style="color:var(--or);font-size:13px">${esc(m.reason)}</td>
      <td style="color:var(--mu)">${new Date(m.modified_at).toLocaleDateString('ar')}</td>
      <td style="color:var(--mu)">${esc(m.modified_by?.name || '—')}</td>
    </tr>`).join('') || '<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">لا توجد تعديلات</td></tr>';
}

loadDashboard();
loadModifications();
loadCcPending();
</script>
@endpush
