@extends('layouts.app')
@section('title', 'لوحة المتابعة / Dashboard')
@section('page-title', 'لوحة المتابعة / Dashboard')

@section('content')

{{-- ═══ KPI CARDS ══════════════════════════════════════════════ --}}
<div class="kpi-grid" id="kpi-grid">
  <div class="kpi-card kpi-blue">
    <div class="kpi-label">إجمالي الحسابات / Total Cards</div>
    <div class="kpi-value" id="kpi-total">—</div>
    <div class="kpi-sub">Commission Cards</div>
    <div class="kpi-icon">📁</div>
  </div>
  <div class="kpi-card kpi-teal">
    <div class="kpi-label">إيداع أولي / Initial Deposits</div>
    <div class="kpi-value" id="kpi-dep">—</div>
    <div class="kpi-sub">Initial Deposits</div>
    <div class="kpi-icon">💵</div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-label">إيداع شهري / Monthly Deposits</div>
    <div class="kpi-value" id="kpi-mon">—</div>
    <div class="kpi-sub">Monthly Deposits</div>
    <div class="kpi-icon">📈</div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-label">حسابات معدّلة / Modified</div>
    <div class="kpi-value" id="kpi-mod">—</div>
    <div class="kpi-sub">Modified Cards</div>
    <div class="kpi-icon">✏️</div>
  </div>
  <div class="kpi-card kpi-purple">
    <div class="kpi-label">مضافة حديثاً / New Added</div>
    <div class="kpi-value" id="kpi-new">—</div>
    <div class="kpi-sub">Newly Added</div>
    <div class="kpi-icon">🆕</div>
  </div>
</div>

{{-- ═══ CHARTS ROW ══════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:2fr 1fr;gap:14px;margin-bottom:16px">
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📊 الإيداعات الشهرية / Monthly Deposits</div>
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
    <div class="panel-header"><div class="panel-title">🥧 توزيع البروكرات / Broker Distribution</div></div>
    <div class="panel-body" style="height:200px;position:relative">
      <canvas id="chart-pie"></canvas>
    </div>
  </div>
</div>

{{-- ═══ TOP PERFORMERS ══════════════════════════════════════════ --}}
<div class="panel" style="margin-bottom:14px">
  <div class="panel-header" style="padding:12px 16px;align-items:center">
    <div class="panel-title">🏆 الأداء المتميز / Top Performers</div>
    <div style="display:flex;gap:6px;margin-right:auto">
      <button class="btn btn-ghost btn-sm lb-tab active" onclick="switchLb('cnt',this)">البروكر — الحسابات</button>
      <button class="btn btn-ghost btn-sm lb-tab"        onclick="switchLb('dep',this)">البروكر — الإيداعات</button>
      <button class="btn btn-ghost btn-sm lb-tab"        onclick="switchLb('mkt',this)">المسوّقون</button>
    </div>
  </div>
  <div id="lb-cnt" class="lb-pane" style="display:block">
    <div id="top-broker-cnt"></div>
  </div>
  <div id="lb-dep" class="lb-pane" style="display:none">
    <div id="top-broker-dep"></div>
  </div>
  <div id="lb-mkt" class="lb-pane" style="display:none">
    <div id="top-marketer"></div>
  </div>
</div>

{{-- ═══ RECENT TABLES ════════════════════════════════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🔝 أعلى الإيداعات الشهرية / Top Monthly</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>رقم الحساب</th><th>البروكر</th><th>إيداع شهري</th><th>الشهر</th>
        </tr></thead>
        <tbody id="top-deposits-tb"></tbody>
      </table>
    </div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title">✏️ آخر التعديلات / Recent Modifications</div></div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>رقم الحساب</th><th>السبب</th><th>التاريخ</th><th>بواسطة</th>
        </tr></thead>
        <tbody id="modifications-tb"></tbody>
      </table>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
// ── Chart instances ───────────────────────────────────────────
let barChart = null;
let pieChart = null;

const CHART_COLORS = ['#14B87E','#2A82A8','#37A0CC','#F59820','#7C6EEE','#E04848'];

// ── Leaderboard ───────────────────────────────────────────────
const RANK_COLORS = ['#D4A83A','#8892a4','#CD7F32','rgba(255,255,255,.18)','rgba(255,255,255,.12)'];
const RANK_BG     = ['rgba(212,168,58,.08)','rgba(136,146,164,.06)','rgba(205,127,50,.06)','transparent','transparent'];

function lbTable(sorted, mapper) {
  if (!sorted.length) {
    return '<div style="padding:20px;text-align:center;color:var(--mu);font-size:12px">لا توجد بيانات / No data</div>';
  }
  const maxVal = sorted[0][1];
  return sorted.map(([name, val], i) => {
    const { label, value, unit, bar, meta } = mapper(name, val, i, maxVal);
    const rankNum   = i + 1;
    const rankColor = RANK_COLORS[i] || 'rgba(255,255,255,.1)';
    const rowBg     = RANK_BG[i]     || 'transparent';
    return `
      <div style="display:flex;align-items:center;gap:12px;padding:10px 16px;
                  border-bottom:1px solid var(--brd1);background:${rowBg};transition:background .15s"
           onmouseover="this.style.background='rgba(255,255,255,.03)'"
           onmouseout="this.style.background='${rowBg}'">
        <div style="width:26px;height:26px;border-radius:50%;background:${rankColor};
                    display:flex;align-items:center;justify-content:center;
                    font-size:11px;font-weight:700;color:white;flex-shrink:0;
                    ${rankNum<=3 ? 'box-shadow:0 2px 8px '+rankColor+'66' : ''}">
          ${rankNum}
        </div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:600;color:var(--tx);
                      white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${label}</div>
          ${meta ? `<div style="font-size:10px;color:var(--mu);margin-top:1px">${meta}</div>` : ''}
          <div style="height:3px;background:rgba(255,255,255,.06);border-radius:2px;margin-top:5px;overflow:hidden">
            <div style="height:100%;width:${Math.round(bar * 100)}%;background:${rankColor};
                        border-radius:2px;transition:width .6s ease"></div>
          </div>
        </div>
        <div style="text-align:left;flex-shrink:0">
          <div style="font-size:14px;font-weight:700;color:${rankColor};font-family:monospace">${value}</div>
          ${unit ? `<div style="font-size:10px;color:var(--mu);text-align:center">${unit}</div>` : ''}
        </div>
      </div>`;
  }).join('');
}

function switchLb(tab, btn) {
  document.querySelectorAll('.lb-pane').forEach(p => p.style.display = 'none');
  document.querySelectorAll('.lb-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('lb-' + tab).style.display = 'block';
  btn.classList.add('active');
}

// ── Main dashboard load ───────────────────────────────────────
async function loadDashboard() {
  const r = await api('GET', '/cards/report');
  if (!r.success) return;

  const s = r.summary || {};
  const cards = r.data || [];

  // ── KPIs ────────────────────────────────────────────────────
  document.getElementById('kpi-total').textContent = (r.count || 0).toLocaleString();
  document.getElementById('kpi-dep').textContent   = fmtK(s.total_initial_deposit  || 0);
  document.getElementById('kpi-mon').textContent   = fmtK(s.total_monthly_deposit  || 0);
  document.getElementById('kpi-mod').textContent   = (s.modified_count  || 0).toLocaleString();
  document.getElementById('kpi-new').textContent   = (s.new_added_count || 0).toLocaleString();

  // Update sidebar badges if present
  const sbCards = document.getElementById('sb-cards-count');
  const sbMod   = document.getElementById('sb-mod-count');
  if (sbCards) sbCards.textContent = r.count || 0;
  if (sbMod)   sbMod.textContent   = s.modified_count || 0;

  // ── Top deposits table ───────────────────────────────────────
  const topData = [...cards].sort((a, b) => (+b.monthly_deposit||0) - (+a.monthly_deposit||0)).slice(0, 8);
  const noDataRow = '<tr><td colspan="4" style="text-align:center;padding:20px;color:var(--mu)">لا توجد بيانات</td></tr>';
  document.getElementById('top-deposits-tb').innerHTML = topData.length
    ? topData.map(row => `
        <tr>
          <td><span class="ac-num">#${row.account_number}</span></td>
          <td>${row.broker?.name || '—'}</td>
          <td class="mono c-green">${fmt(row.monthly_deposit)}</td>
          <td class="c-muted" style="font-size:11px">${row.month || '—'}</td>
        </tr>`).join('')
    : noDataRow;

  // ── Recent modifications ──────────────────────────────────────
  const mods = await api('GET', '/cards/modifications?per_page=8');
  document.getElementById('modifications-tb').innerHTML = (mods.data || []).length
    ? (mods.data || []).map(m => `
        <tr>
          <td><span class="ac-num">#${m.account_number}</span></td>
          <td style="font-size:11px;max-width:120px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
            ${m.reason || '—'}
          </td>
          <td style="font-size:11px;color:var(--mu)">${m.modified_at ? m.modified_at.substring(0,10) : '—'}</td>
          <td style="font-size:11px">${m.modified_by_name || '—'}</td>
        </tr>`).join('')
    : noDataRow;

  // ── Monthly chart data ────────────────────────────────────────
  const monthly = {};
  cards.forEach(row => {
    const key = row.month || 'Unknown';
    if (!monthly[key]) monthly[key] = { dep: 0, mon: 0 };
    monthly[key].dep += parseFloat(row.initial_deposit  || 0);
    monthly[key].mon += parseFloat(row.monthly_deposit  || 0);
  });
  const mKeys = Object.keys(monthly).slice(-10);
  const mDeps = mKeys.map(m => Math.round(monthly[m].dep));
  const mMons = mKeys.map(m => Math.round(monthly[m].mon));

  if (barChart) barChart.destroy();
  barChart = new Chart(document.getElementById('chart-bar'), {
    type: 'bar',
    data: {
      labels: mKeys,
      datasets: [
        { label: 'إيداع أولي',  data: mDeps, backgroundColor: 'rgba(42,130,168,.75)',  borderRadius: 5 },
        { label: 'إيداع شهري', data: mMons, backgroundColor: 'rgba(20,184,126,.65)', borderRadius: 5 },
      ]
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: { legend: { display: false } },
      scales: {
        x: { ticks: { color: '#6B849E', font: { size: 9 }, maxRotation: 45 }, grid: { color: 'rgba(255,255,255,.05)' } },
        y: { ticks: { color: '#6B849E', font: { size: 9 }, callback: v => '$' + v.toLocaleString() }, grid: { color: 'rgba(255,255,255,.05)' } }
      }
    }
  });

  // ── Broker pie chart ───────────────────────────────────────────
  const brokerPieMap = {};
  cards.forEach(row => {
    const name = row.broker?.name;
    if (name) brokerPieMap[name] = (brokerPieMap[name] || 0) + parseFloat(row.monthly_deposit || 0);
  });
  const bNames = Object.keys(brokerPieMap);
  const bVals  = bNames.map(b => brokerPieMap[b]);

  if (pieChart) pieChart.destroy();
  pieChart = new Chart(document.getElementById('chart-pie'), {
    type: 'doughnut',
    data: {
      labels: bNames,
      datasets: [{ data: bVals, backgroundColor: CHART_COLORS, borderWidth: 0, hoverOffset: 4 }]
    },
    options: {
      responsive: true, maintainAspectRatio: false, cutout: '68%',
      plugins: {
        legend: { position: 'bottom', labels: { color: '#6B849E', font: { size: 9 }, boxWidth: 8, padding: 8 } }
      }
    }
  });

  // ── Leaderboard data ───────────────────────────────────────────
  // Build branch lookup maps FIRST (before using them)
  const brokerBranchMap = {};
  const mktBranchMap    = {};
  cards.forEach(row => {
    if (row.broker?.name   && row.branch?.name_ar) brokerBranchMap[row.broker.name]   = row.branch.name_ar;
    if (row.marketer?.name && row.branch?.name_ar) mktBranchMap[row.marketer.name]    = row.branch.name_ar;
  });

  // Top brokers by card count
  const brokerCountMap = {};
  cards.forEach(row => {
    const n = row.broker?.name;
    if (n) brokerCountMap[n] = (brokerCountMap[n] || 0) + 1;
  });
  const sortedBrokerCnt = Object.entries(brokerCountMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

  document.getElementById('top-broker-cnt').innerHTML = lbTable(
    sortedBrokerCnt,
    (name, val, i, max) => ({
      label: name,
      value: val,
      unit:  'حساب',
      bar:   val / max,
      meta:  brokerBranchMap[name] || ''
    })
  );

  // Top brokers by deposit
  const brokerDepMap = {};
  cards.forEach(row => {
    const n = row.broker?.name;
    if (n) brokerDepMap[n] = (brokerDepMap[n] || 0) + parseFloat(row.monthly_deposit || 0);
  });
  const sortedBrokerDep = Object.entries(brokerDepMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

  document.getElementById('top-broker-dep').innerHTML = lbTable(
    sortedBrokerDep,
    (name, val, i, max) => ({
      label: name,
      value: fmtK(val),
      unit:  '',
      bar:   val / max,
      meta:  brokerBranchMap[name] || ''
    })
  );

  // Top marketers by card count
  const mktCountMap = {};
  cards.forEach(row => {
    const n = row.marketer?.name;
    if (n && n !== row.broker?.name) mktCountMap[n] = (mktCountMap[n] || 0) + 1;
  });
  const sortedMkt = Object.entries(mktCountMap).sort((a, b) => b[1] - a[1]).slice(0, 5);

  document.getElementById('top-marketer').innerHTML = lbTable(
    sortedMkt,
    (name, val, i, max) => ({
      label: name,
      value: val,
      unit:  'حساب',
      bar:   val / max,
      meta:  mktBranchMap[name] || ''
    })
  );
}

document.addEventListener('DOMContentLoaded', loadDashboard);
</script>
@endpush
