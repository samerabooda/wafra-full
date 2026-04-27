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
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px">
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
<div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;margin-bottom:16px">
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
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
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
const COLORS = ['#2E86AB','#3A9DB5','#1A5F7A','#22C97A','#F5A623','#7B68EE'];
const medals  = ['🥇','🥈','🥉','4️⃣','5️⃣'];

async function loadDashboard() {
  // Single pre-aggregated endpoint — no full-table scan in PHP
  const r = await api('GET', '/dashboard');
  if (!r.success) return;

  const k = r.kpi;
  document.getElementById('kpi-total').textContent = k.total.toLocaleString();
  document.getElementById('kpi-dep').textContent   = fmtK(k.initial_deposit);
  document.getElementById('kpi-mon').textContent   = fmtK(k.monthly_deposit);
  document.getElementById('kpi-mod').textContent   = k.modified;
  document.getElementById('kpi-new').textContent   = k.new_added;

  document.getElementById('sb-cards-count').textContent = k.total;
  document.getElementById('sb-mod-count').textContent   = k.modified;

  // ── Monthly bar chart (data already grouped server-side) ──
  const mKeys = r.monthly.map(m => m.month);
  const mDeps = r.monthly.map(m => Math.round(parseFloat(m.initial_deposit)));
  const mMons = r.monthly.map(m => Math.round(parseFloat(m.monthly_deposit)));

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

  // ── Broker distribution pie (top 10, already aggregated) ──
  const bNames = r.broker_dist.map(b => b.broker_name);
  const bVals  = r.broker_dist.map(b => parseFloat(b.monthly_deposit));
  if (pieChart) pieChart.destroy();
  pieChart = new Chart(document.getElementById('chart-pie'), {
    type: 'doughnut',
    data: { labels:bNames, datasets:[{ data:bVals, backgroundColor:COLORS, borderWidth:0, hoverOffset:4 }] },
    options: {
      responsive:true, maintainAspectRatio:false, cutout:'68%',
      plugins: { legend:{ position:'bottom', labels:{color:'#7A9AB5',font:{size:9},boxWidth:8,padding:8} } }
    }
  });

  // ── Top brokers by count ───────────────────────────────────
  const byCount = [...r.broker_dist].sort((a,b) => b.account_count - a.account_count).slice(0,5);
  document.getElementById('top-broker-cnt').innerHTML = byCount.map((b,i) => `
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--brd1)">
      <span style="font-size:16px">${medals[i]||'·'}</span>
      <div style="flex:1"><div style="font-size:12px;font-weight:700">${b.broker_name}</div><div style="font-size:10px;color:var(--mu)">${b.account_count} حساب</div></div>
      <div class="mono c-teal" style="font-size:12px;font-weight:700">${b.account_count}</div>
    </div>`).join('') || '<div style="color:var(--mu);font-size:12px;padding:10px 0">لا توجد بيانات</div>';

  // ── Top brokers by deposit ─────────────────────────────────
  const byDep = [...r.broker_dist].sort((a,b) => b.monthly_deposit - a.monthly_deposit).slice(0,5);
  document.getElementById('top-broker-dep').innerHTML = byDep.map((b,i) => `
    <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--brd1)">
      <span style="font-size:16px">${medals[i]||'·'}</span>
      <div style="flex:1"><div style="font-size:12px;font-weight:700">${b.broker_name}</div><div style="font-size:10px;color:var(--mu)">${fmtK(parseFloat(b.monthly_deposit))}</div></div>
      <div class="mono c-green" style="font-size:12px;font-weight:700">${fmtK(parseFloat(b.monthly_deposit))}</div>
    </div>`).join('') || '<div style="color:var(--mu);font-size:12px;padding:10px 0">لا توجد بيانات</div>';

  // ── Top marketers ──────────────────────────────────────────
  document.getElementById('top-marketer').innerHTML = r.top_marketers.length
    ? r.top_marketers.map((m,i) => `
      <div style="display:flex;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--brd1)">
        <span style="font-size:16px">${medals[i]||'·'}</span>
        <div style="flex:1"><div style="font-size:12px;font-weight:700">${m.marketer_name}</div></div>
        <div class="mono c-teal" style="font-size:12px">${m.account_count}</div>
      </div>`).join('')
    : '<div style="color:var(--mu);font-size:12px;padding:10px 0">لا توجد بيانات مسوّق منفصل</div>';

  // ── Top deposits table ─────────────────────────────────────
  document.getElementById('top-deposits-tb').innerHTML = r.top_deposits.length
    ? r.top_deposits.map(row => `
      <tr>
        <td><span class="ac-num">#${row.account_number}</span></td>
        <td>${row.broker?.name || '—'}</td>
        <td class="mono c-green">${fmt(row.monthly_deposit)}</td>
        <td class="c-muted">${row.month}</td>
      </tr>`).join('')
    : '<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">لا توجد بيانات</td></tr>';

  // ── Recent modifications ───────────────────────────────────
  document.getElementById('modifications-tb').innerHTML = r.recent_mods.length
    ? r.recent_mods.map(m => `
      <tr>
        <td><span class="ac-num">#${m.account_number}</span></td>
        <td style="color:var(--or);font-size:11px">${m.reason}</td>
        <td style="color:var(--mu)">${new Date(m.modified_at).toLocaleDateString('ar')}</td>
        <td style="color:var(--mu)">${m.modified_by?.name || '—'}</td>
      </tr>`).join('')
    : '<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">لا توجد تعديلات</td></tr>';
}

loadDashboard();
</script>
@endpush
