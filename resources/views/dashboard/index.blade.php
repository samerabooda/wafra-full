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
    <div class="kpi-label">إيداع أولي</div>
    <div class="kpi-value" id="kpi-dep">—</div>
    <div class="kpi-sub">Initial Deposits</div>
    <div class="kpi-icon">💵</div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-label">إيداع شهري</div>
    <div class="kpi-value" id="kpi-mon">—</div>
    <div class="kpi-sub">Monthly Deposits</div>
    <div class="kpi-icon">📈</div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-label">حسابات معدّلة</div>
    <div class="kpi-value" id="kpi-mod">—</div>
    <div class="kpi-sub">Modified</div>
    <div class="kpi-icon">✏️</div>
  </div>
  <div class="kpi-card kpi-purple">
    <div class="kpi-label">كروت مضافة</div>
    <div class="kpi-value" id="kpi-new">—</div>
    <div class="kpi-sub">Newly Added</div>
    <div class="kpi-icon">🆕</div>
  </div>
</div>

<!-- Charts Row -->
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px" class="dash-grid-2">
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📊 الإيداعات الشهرية</div>
      <div style="display:flex;gap:10px;font-size:10px;color:var(--mu)">
        <span><span style="display:inline-block;width:8px;height:8px;background:var(--pri2);border-radius:50%"></span> أولي</span>
        <span><span style="display:inline-block;width:8px;height:8px;background:var(--gr);border-radius:50%"></span> شهري</span>
      </div>
    </div>
    <div class="panel-body" style="height:200px;position:relative">
      <canvas id="chart-bar"></canvas>
    </div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🥧 توزيع البروكرات</div></div>
    <div class="panel-body" style="height:200px;position:relative">
      <canvas id="chart-pie"></canvas>
    </div>
  </div>
</div>

<!-- Top Brokers & Marketers -->
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px" class="dash-grid-3">
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🥇 أفضل بروكر — حسابات</div></div>
    <div class="panel-body" id="top-broker-cnt" style="padding:10px 16px"></div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title">💰 أفضل بروكر — إيداع</div></div>
    <div class="panel-body" id="top-broker-dep" style="padding:10px 16px"></div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title">📢 أفضل مسوّق</div></div>
    <div class="panel-body" id="top-marketer" style="padding:10px 16px"></div>
  </div>
</div>

<!-- Recent Tables -->
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px" class="dash-grid-2">
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🔝 أعلى الإيداعات الشهرية</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr><th>رقم الحساب</th><th>البروكر</th><th>إيداع شهري</th><th>الشهر</th></tr></thead>
        <tbody id="top-deposits-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel">
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
let barChart = null, pieChart = null;
const MONTHS_DATA = @json($monthlyData ?? []);
const COLORS = ['#2E86AB','#3A9DB5','#1A5F7A','#22C97A','#F5A623','#7B68EE'];

async function loadDashboard() {
  // KPIs
  const r = await api('GET', '/cards/report');
  if (!r.success) return;
  const s = r.summary;

  document.getElementById('kpi-total').textContent = r.count.toLocaleString();
  document.getElementById('kpi-dep').textContent   = fmtK(s.total_initial_deposit);
  document.getElementById('kpi-mon').textContent   = fmtK(s.total_monthly_deposit);
  document.getElementById('kpi-mod').textContent   = s.modified_count;
  document.getElementById('kpi-new').textContent   = s.new_added_count;

  document.getElementById('sb-cards-count').textContent = r.count;
  document.getElementById('sb-mod-count').textContent   = s.modified_count;

  // Top deposits
  const topData = (r.data || []).sort((a,b) => b.monthly_deposit - a.monthly_deposit).slice(0,8);
  document.getElementById('top-deposits-tb').innerHTML = topData.map(row => `
    <tr>
      <td><span class="ac-num">#${row.account_number}</span></td>
      <td>${row.broker?.name || '—'}</td>
      <td class="mono c-green">${fmt(row.monthly_deposit)}</td>
      <td class="c-muted">${row.month}</td>
    </tr>`).join('') || '<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">لا توجد بيانات</td></tr>';

  // Charts
  const monthly = {};
  (r.data || []).forEach(row => {
    if (!monthly[row.month]) monthly[row.month] = {dep:0, mon:0};
    monthly[row.month].dep += parseFloat(row.initial_deposit || 0);
    monthly[row.month].mon += parseFloat(row.monthly_deposit || 0);
  });
  const mKeys   = Object.keys(monthly).slice(-10);
  const mDeps   = mKeys.map(m => Math.round(monthly[m].dep));
  const mMons   = mKeys.map(m => Math.round(monthly[m].mon));

  if (barChart) barChart.destroy();
  barChart = new Chart(document.getElementById('chart-bar'), {
    type: 'bar',
    data: {
      labels: mKeys,
      datasets: [
        { label:'إيداع أولي',  data:mDeps, backgroundColor:'rgba(46,134,171,.75)', borderRadius:5 },
        { label:'إيداع شهري', data:mMons, backgroundColor:'rgba(34,201,122,.65)', borderRadius:5 },
      ]
    },
    options: {
      responsive:true, maintainAspectRatio:false,
      plugins: { legend:{display:false} },
      scales: {
        x: { ticks:{color:'#5A7A9A',font:{size:9},maxRotation:45}, grid:{color:'rgba(37,58,99,.3)'} },
        y: { ticks:{color:'#5A7A9A',font:{size:9},callback:v=>'$'+v.toLocaleString()}, grid:{color:'rgba(37,58,99,.3)'} }
      }
    }
  });

  // Broker distribution pie
  const brokerMap = {};
  (r.data || []).forEach(row => {
    const name = row.broker?.name || 'Unknown';
    if (name !== 'IB account' && name !== 'Self') {
      brokerMap[name] = (brokerMap[name] || 0) + parseFloat(row.monthly_deposit || 0);
    }
  });
  const bNames = Object.keys(brokerMap);
  const bVals  = bNames.map(b => brokerMap[b]);
  if (pieChart) pieChart.destroy();
  pieChart = new Chart(document.getElementById('chart-pie'), {
    type: 'doughnut',
    data: { labels:bNames, datasets:[{ data:bVals, backgroundColor:COLORS, borderWidth:0, hoverOffset:4 }] },
    options: {
      responsive:true, maintainAspectRatio:false, cutout:'68%',
      plugins: { legend:{ position:'bottom', labels:{color:'#7A9AB5',font:{size:9},boxWidth:8,padding:8} } }
    }
  });

  // Top brokers by count
  const brokerCount = {};
  (r.data || []).forEach(row => {
    const n = row.broker?.name || 'Unknown';
    brokerCount[n] = (brokerCount[n] || 0) + 1;
  });
  const sortedCnt = Object.entries(brokerCount).sort((a,b)=>b[1]-a[1]).slice(0,5);
  const medals = ['🥇','🥈','🥉','4️⃣','5️⃣'];
  document.getElementById('top-broker-cnt').innerHTML = sortedCnt.map(([name,cnt],i) => `
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--brd1)">
      <span style="font-size:16px">${medals[i]||'·'}</span>
      <div style="flex:1"><div style="font-size:12px;font-weight:700">${name}</div><div style="font-size:10px;color:var(--mu)">${cnt} حساب</div></div>
      <div class="mono c-teal" style="font-size:12px;font-weight:700">${cnt}</div>
    </div>`).join('');

  // Top brokers by deposit
  const sortedDep = Object.entries(brokerMap).sort((a,b)=>b[1]-a[1]).slice(0,5);
  document.getElementById('top-broker-dep').innerHTML = sortedDep.map(([name,dep],i) => `
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--brd1)">
      <span style="font-size:16px">${medals[i]||'·'}</span>
      <div style="flex:1"><div style="font-size:12px;font-weight:700">${name}</div><div style="font-size:10px;color:var(--mu)">${fmtK(dep)}</div></div>
      <div class="mono c-green" style="font-size:12px;font-weight:700">${fmtK(dep)}</div>
    </div>`).join('');

  // Top marketers
  const mktMap = {};
  (r.data || []).forEach(row => {
    if (row.marketer?.name && row.marketer.name !== row.broker?.name) {
      mktMap[row.marketer.name] = (mktMap[row.marketer.name] || 0) + 1;
    }
  });
  const sortedMkt = Object.entries(mktMap).sort((a,b)=>b[1]-a[1]).slice(0,5);
  document.getElementById('top-marketer').innerHTML = sortedMkt.length
    ? sortedMkt.map(([name,cnt],i) => `
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--brd1)">
        <span style="font-size:16px">${medals[i]||'·'}</span>
        <div style="flex:1"><div style="font-size:12px;font-weight:700">${name}</div></div>
        <div class="mono c-teal" style="font-size:12px">${cnt}</div>
      </div>`).join('')
    : '<div style="color:var(--mu);font-size:12px;padding:10px 0">لا توجد بيانات مسوّق منفصل</div>';
}

// Modifications
async function loadModifications() {
  const r = await api('GET', '/cards/modifications');
  if (!r.success) return;
  const rows = (r.data?.data || []).slice(0, 8);
  document.getElementById('modifications-tb').innerHTML = rows.map(m => `
    <tr>
      <td><span class="ac-num">#${m.account_number}</span></td>
      <td style="color:var(--or);font-size:11px">${m.reason}</td>
      <td style="color:var(--mu)">${new Date(m.modified_at).toLocaleDateString('ar')}</td>
      <td style="color:var(--mu)">${m.modified_by?.name || '—'}</td>
    </tr>`).join('') || '<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">لا توجد تعديلات</td></tr>';
}

loadDashboard();
loadModifications();
</script>
@endpush
