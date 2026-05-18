@extends('layouts.app')
@section('title','التقارير')
@section('page-title','التقارير الديناميكية')

@section('content')
<!-- Report Tabs -->
<div style="display:flex;gap:4px;background:var(--inp-bg);border-radius:10px;padding:4px;margin-bottom:16px">
  <button class="btn btn-sm active-tab" id="tab-btn-table"   onclick="switchTab('table')"   style="flex:1;border-radius:7px;padding:8px">📋 جدول بيانات</button>
  <button class="btn btn-sm" id="tab-btn-dash"    onclick="switchTab('dash')"    style="flex:1;border-radius:7px;padding:8px">📊 Dashboard</button>
  <button class="btn btn-sm" id="tab-btn-diagrams"onclick="switchTab('diagrams')" style="flex:1;border-radius:7px;padding:8px">📈 Diagrams</button>
</div>

<!-- Filters (shared across tabs) -->
<div class="panel" style="padding:14px 16px;margin-bottom:14px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">من شهر</div>
      <select id="rf-from" class="form-control" style="min-width:120px"><option value="">—</option></select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">إلى شهر</div>
      <select id="rf-to" class="form-control" style="min-width:120px"><option value="">—</option></select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">البروكر</div>
      <select id="rf-broker" class="form-control" style="min-width:140px"><option value="">الكل</option></select>
    </div>
    @if(auth()->user()?->isFinanceAdmin())
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">الفرع</div>
      <select id="rf-branch" class="form-control" style="min-width:140px"><option value="">كل الفروع</option></select>
    </div>
    @endif
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">الحالة</div>
      <select id="rf-status" class="form-control">
        <option value="">الكل</option>
        <option value="modified">معدّلة فقط</option>
        <option value="new_added">مضافة جديدة فقط</option>
        <option value="active">عادي فقط</option>
      </select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">نوع</div>
      <select id="rf-kind" class="form-control"><option value="">الكل</option><option value="new">جديد</option><option value="sub">فرعي</option></select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">حد أدنى $</div>
      <input type="number" id="rf-min" class="form-control" style="width:90px" value="0" min="0">
    </div>
    <button class="btn btn-primary" onclick="generateReport()">⚡ توليد التقرير</button>
    <button class="btn btn-ghost" onclick="clearRptFilters()">✕ مسح</button>
  </div>
</div>

<!-- ── TAB: Table ── -->
<div id="tab-table">
  <div class="panel" id="rpt-table-wrap" style="display:none">
    <div class="panel-header">
      <div class="panel-title">📋 نتائج التقرير <span id="rpt-count" style="font-size:11px;color:var(--mu)"></span></div>
      <div style="display:flex;gap:6px">
        <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportRptExcel()">📗 Excel</button>
        <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)"   onclick="exportRptPdf()">📄 PDF</button>
        <button class="btn btn-sm" style="background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.25);color:var(--pri2)" onclick="window.print()">🖨️ طباعة</button>
      </div>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead>
          <tr>
            <th>رقم الحساب</th><th>البروكر</th><th>مسوّق داخلي</th>
            <th>مسوّق خارجي 1</th><th>مسوّق خارجي 2</th>
            <th>إيداع أولي</th><th>إيداع شهري</th>
            <th>ع. بروكر</th><th>ع. داخلي</th><th>ع. خارجي 1</th><th>ع. خارجي 2</th>
            <th>النوع</th><th>الشهر</th><th>الحالة</th>
          </tr>
        </thead>
        <tbody id="rpt-tbody"></tbody>
      </table>
    </div>
    <!-- Totals -->
    <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;gap:20px;background:var(--inp-bg);flex-wrap:wrap;font-size:12px">
      <span style="color:var(--mu)">الإجماليات:</span>
      <span class="mono c-blue">إيداع أولي: <b id="rpt-sum-dep">$0</b></span>
      <span class="mono c-green">إيداع شهري: <b id="rpt-sum-mon">$0</b></span>
      <span class="mono c-orange">معدّلة: <b id="rpt-sum-mod">0</b></span>
      <span class="mono c-purple">مضافة جديدة: <b id="rpt-sum-new">0</b></span>
    </div>
  </div>
</div>

<!-- ── TAB: Dashboard (BI Style) ── -->
<div id="tab-dash" style="display:none">

  <!-- Auto-filter bar for dashboard -->
  <div class="panel" style="padding:12px 16px;margin-bottom:12px;background:rgba(46,134,171,.05);border-color:rgba(46,134,171,.2)">
    <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
      <span style="font-size:11px;color:var(--mu);font-weight:700">🔍 فلتر سريع:</span>
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
  <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:12px">
    <div class="panel" style="padding:16px;text-align:center">
      <div style="font-size:11px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">إجمالي الحسابات</div>
      <div style="font-size:2rem;font-weight:900;color:var(--pri2)" id="db-k-total">—</div>
    </div>
    <div class="panel" style="padding:16px;text-align:center">
      <div style="font-size:11px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">إيداع أولي</div>
      <div style="font-size:2rem;font-weight:900;color:var(--gr)" id="db-k-dep">—</div>
    </div>
    <div class="panel" style="padding:16px;text-align:center">
      <div style="font-size:11px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">حسابات جديدة</div>
      <div style="font-size:2rem;font-weight:900;color:var(--or)" id="db-k-new">—</div>
    </div>
    <div class="panel" style="padding:16px;text-align:center">
      <div style="font-size:11px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px">حسابات فرعية</div>
      <div style="font-size:2rem;font-weight:900;color:var(--pu,#7B68EE)" id="db-k-sub">—</div>
    </div>
  </div>

  <!-- Row 2: Best Broker + Best Marketer + Kind Donut -->
  <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:12px">

    <!-- Best Broker -->
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥇 أفضل بروكر</div></div>
      <div class="panel-body" id="db-top-broker" style="padding:10px">
        <div style="color:var(--mu);font-size:12px;text-align:center;padding:20px">جارٍ التحميل…</div>
      </div>
    </div>

    <!-- Best Marketer -->
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥈 أفضل مسوّق</div></div>
      <div class="panel-body" id="db-top-marketer" style="padding:10px">
        <div style="color:var(--mu);font-size:12px;text-align:center;padding:20px">جارٍ التحميل…</div>
      </div>
    </div>

    <!-- Account kind donut -->
    <div class="panel">
      <div class="panel-header"><div class="panel-title">🥧 New / Sub</div></div>
      <div style="height:160px;padding:10px;position:relative"><canvas id="db-chart-kind"></canvas></div>
    </div>
  </div>

  <!-- Row 3: Accounts per broker bar + Monthly trend -->
  <div style="display:grid;grid-template-columns:3fr 2fr;gap:12px;margin-bottom:12px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📊 عدد الحسابات بالبروكر</div></div>
      <div style="height:220px;padding:12px;position:relative"><canvas id="db-chart-broker-cnt"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📈 توزيع الإيداع الأولي بالبروكر</div></div>
      <div style="height:220px;padding:12px;position:relative"><canvas id="db-chart-broker-dep"></canvas></div>
    </div>
  </div>

  <!-- Row 4: Accounts per month + Marketer performance table -->
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px">
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📅 الحسابات شهرياً</div></div>
      <div style="height:200px;padding:12px;position:relative"><canvas id="db-chart-monthly"></canvas></div>
    </div>
    <div class="panel">
      <div class="panel-header"><div class="panel-title">📋 أداء المسوّقين</div></div>
      <div class="panel-body" style="padding:8px;max-height:230px;overflow-y:auto" id="db-marketer-table">
        <div style="color:var(--mu);font-size:12px;text-align:center;padding:20px">لا توجد بيانات بعد</div>
      </div>
    </div>
  </div>

  <!-- Empty state -->
  <div id="db-empty" style="text-align:center;padding:60px 20px;color:var(--mu)">
    <div style="font-size:48px;margin-bottom:12px">📊</div>
    <div style="font-size:16px;font-weight:700;margin-bottom:6px">الداشبورد جاهز</div>
    <div style="font-size:12px">استخدم فلتر الأعلى أو انقر ⚡ توليد التقرير من التبويب الرئيسي</div>
  </div>
</div>

<!-- ── TAB: Diagrams ── -->
<div id="tab-diagrams" style="display:none">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px">
    <div class="panel"><div class="panel-header"><div class="panel-title">📈 منحنى الإيداعات الشهرية</div></div>
      <div style="height:220px;padding:14px;position:relative"><canvas id="rpt-line"></canvas></div></div>
    <div class="panel"><div class="panel-header"><div class="panel-title">📊 عدد الحسابات بالشهر</div></div>
      <div style="height:220px;padding:14px;position:relative"><canvas id="rpt-cnt-bar"></canvas></div></div>
  </div>
  <div class="panel" style="margin-bottom:14px">
    <div class="panel-header"><div class="panel-title">🔀 مقارنة الإيداع الأولي مقابل الشهري لكل بروكر</div></div>
    <div style="height:220px;padding:14px;position:relative"><canvas id="rpt-compare"></canvas></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="panel"><div class="panel-header"><div class="panel-title">🟡 نسبة المعدّل مقابل العادي</div></div>
      <div style="height:180px;padding:14px;position:relative"><canvas id="rpt-mod-ratio"></canvas></div></div>
    <div class="panel"><div class="panel-header"><div class="panel-title">📉 معدل العمولة بالبروكر</div></div>
      <div style="height:180px;padding:14px;position:relative"><canvas id="rpt-comm-rate"></canvas></div></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
let RD = []; // Full report data (all fetched records)
let DB = []; // Dashboard-filtered subset
let rptCharts = {};
let allEmployees = []; // cached employees for dashboard filter
const COLORS = ['#2E86AB','#3A9DB5','#1A5F7A','#22C97A','#F5A623','#7B68EE','#E05050','#26D4E8','#FF6B6B','#4ECDC4'];

// ── Tab switching ──────────────────────────────────────────
let curTab = 'table';
function switchTab(name) {
  ['table','dash','diagrams'].forEach(t => {
    document.getElementById('tab-'+t).style.display = t===name ? 'block' : 'none';
    const btn = document.getElementById('tab-btn-'+t);
    if (btn) { btn.style.background = t===name ? 'var(--bg3)' : ''; btn.style.color = t===name ? 'var(--pri2)' : 'var(--m2)'; }
  });
  curTab = name;
  if (name === 'dash') buildDashboard();
  else if (name === 'diagrams' && RD.length) buildDiagrams();
}
switchTab('table');

// ── Load filter options ────────────────────────────────────
async function loadFilterOptions() {
  const [employees, branches] = await Promise.all([
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
  ]);

  // Months
  const mNow = new Date();
  ['rf-from','rf-to'].forEach(id => {
    const sel = document.getElementById(id); if (!sel) return;
    for (let i=0; i<24; i++) {
      const d = new Date(mNow.getFullYear(), mNow.getMonth()-i, 1);
      const m = d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
      const o = document.createElement('option'); o.value=o.textContent=m; sel.appendChild(o);
    }
  });

  if (employees.success) {
    allEmployees = employees.data;
    // Table filter: broker by name
    const sel = document.getElementById('rf-broker');
    employees.data.forEach(e => { const o=document.createElement('option');o.value=e.name;o.textContent=e.name+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦');sel.appendChild(o); });

    // Dashboard filter: broker & marketer by id
    const dbBroker = document.getElementById('db-broker');
    const dbMkt    = document.getElementById('db-marketer');
    employees.data.forEach(e => {
      const ob=document.createElement('option'); ob.value=e.id; ob.textContent=e.name+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦'); dbBroker.appendChild(ob);
      const om=document.createElement('option'); om.value=e.id; om.textContent=e.name+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦'); dbMkt.appendChild(om);
    });
  }

  if (branches.success) {
    const brSel = document.getElementById('rf-branch');
    const dbBr  = document.getElementById('db-branch');
    branches.data.forEach(b => {
      if (brSel) { const o=document.createElement('option');o.value=b.id;o.textContent=b.name_ar;brSel.appendChild(o); }
      if (dbBr)  { const o=document.createElement('option');o.value=b.id;o.textContent=b.name_ar;dbBr.appendChild(o);  }
    });
  }
}

// ── Generate Report ────────────────────────────────────────
async function generateReport() {
  const params = new URLSearchParams();
  const from   = document.getElementById('rf-from')?.value;
  const to     = document.getElementById('rf-to')?.value;
  const broker = document.getElementById('rf-broker')?.value;
  const branch = document.getElementById('rf-branch')?.value;
  const status = document.getElementById('rf-status')?.value;
  const kind   = document.getElementById('rf-kind')?.value;
  const min    = document.getElementById('rf-min')?.value;

  if (from) params.set('month_from', from);
  if (to)   params.set('month_to', to);
  if (status) params.set('status', status);
  if (kind)   params.set('kind', kind);
  if (min && parseInt(min) > 0) params.set('min_deposit', min);
  params.set('per_page', 500);

  const r = await api('GET', '/cards/report?' + params);
  if (!r.success) { toast('خطأ في توليد التقرير', 'error'); return; }

  RD = r.data || [];
  const s = r.summary;

  // Table
  document.getElementById('rpt-count').textContent   = RD.length + ' سجل';
  document.getElementById('rpt-sum-dep').textContent  = fmtK(s.total_initial_deposit);
  document.getElementById('rpt-sum-mon').textContent  = fmtK(s.total_monthly_deposit);
  document.getElementById('rpt-sum-mod').textContent  = s.modified_count;
  document.getElementById('rpt-sum-new').textContent  = s.new_added_count;

  document.getElementById('rpt-tbody').innerHTML = RD.map(c => `
    <tr class="${c.status==='modified'?'row-modified':''}">
      <td><span class="ac-num">#${c.account_number}${c.status==='modified'?' 🟡':''}</span></td>
      <td style="font-weight:600;color:var(--pri2)">${c.broker?.name||'—'}</td>
      <td style="color:var(--m2)">${c.marketer?.name&&c.marketer.name!==c.broker?.name?c.marketer.name:'—'}</td>
      <td style="color:var(--pu)">${c.ext_marketer1?.name||'—'}</td>
      <td style="color:var(--pu)">${c.ext_marketer2?.name||'—'}</td>
      <td class="mono c-blue">${fmt(c.initial_deposit)}</td>
      <td class="mono c-green">${fmt(c.monthly_deposit)}</td>
      <td class="mono c-blue">$${c.broker_commission}/lot</td>
      <td class="mono c-green">$${c.marketer_commission||0}/lot</td>
      <td class="mono" style="color:var(--pu)">$${c.ext_commission1||0}/lot</td>
      <td class="mono" style="color:var(--pu)">$${c.ext_commission2||0}/lot</td>
      <td><span class="badge ${c.account_kind==='new'?'badge-green':'badge-blue'}">${c.account_kind==='new'?'NEW':'SUB'}</span></td>
      <td style="color:var(--mu)">${c.month}</td>
      <td>${c.status==='modified'?'<span class="badge badge-orange">✏️ معدّل</span>':c.status==='new_added'?'<span class="badge badge-green">🆕 جديد</span>':'<span class="badge badge-blue">عادي</span>'}</td>
    </tr>`).join('') || '<tr><td colspan="14" style="text-align:center;padding:30px;color:var(--mu)">لا توجد نتائج</td></tr>';

  document.getElementById('rpt-table-wrap').style.display = 'block';

  if (curTab === 'dash') buildDashboard();
  else if (curTab === 'diagrams') buildDiagrams();

  toast(`تقرير: ${RD.length} سجل — معدّلة: ${s.modified_count} 🟡`, 'success');
}

// ── Chart helpers ──────────────────────────────────────────
function destroyChart(id) { if (rptCharts[id]) { rptCharts[id].destroy(); rptCharts[id]=null; } }
const tc = () => '#5A7A9A';
const gc = () => 'rgba(37,58,99,.3)';

// ── Dashboard filter helper ────────────────────────────────
function getDbFiltered() {
  let data = RD.length ? RD : [];
  const branchId  = document.getElementById('db-branch')?.value;
  const brokerId  = document.getElementById('db-broker')?.value;
  const marketerId= document.getElementById('db-marketer')?.value;
  const period    = document.getElementById('db-period')?.value || 'all';

  if (branchId)   data = data.filter(c => String(c.branch_id) === branchId);
  if (brokerId)   data = data.filter(c => String(c.broker_id) === brokerId);
  if (marketerId) data = data.filter(c =>
    String(c.marketer_id) === marketerId ||
    String(c.ext_marketer1_id) === marketerId ||
    String(c.ext_marketer2_id) === marketerId
  );
  if (period !== 'all') {
    const months = parseInt(period);
    const cutoff = new Date();
    cutoff.setMonth(cutoff.getMonth() - months);
    data = data.filter(c => {
      if (!c.month_date && c.month) {
        // parse "Jan 2025" style
        const parsed = new Date('01 ' + c.month);
        return parsed >= cutoff;
      }
      return new Date(c.month_date) >= cutoff;
    });
  }
  return data;
}

function clearDashFilters() {
  ['db-branch','db-broker','db-marketer'].forEach(id=>{ const el=document.getElementById(id); if(el) el.value=''; });
  const p = document.getElementById('db-period'); if(p) p.value='all';
  buildDashboard();
}

// ── Build Dashboard ────────────────────────────────────────
async function buildDashboard() {
  // If no data loaded yet, try to auto-load everything
  if (!RD.length) {
    const r = await api('GET', '/cards/report?per_page=1000');
    if (r.success) {
      RD = r.data || [];
    }
  }

  const data = getDbFiltered();
  const empty = document.getElementById('db-empty');

  if (!data.length) {
    if (empty) empty.style.display = 'block';
    return;
  }
  if (empty) empty.style.display = 'none';

  // ── KPIs ──────────────────────────────────────────────────
  const totalDep   = data.reduce((s,c)=>s+parseFloat(c.initial_deposit||0),0);
  const newAccts   = data.filter(c=>c.account_kind==='new').length;
  const subAccts   = data.filter(c=>c.account_kind==='sub').length;
  document.getElementById('db-k-total').textContent = data.length.toLocaleString();
  document.getElementById('db-k-dep').textContent   = fmtK(totalDep);
  document.getElementById('db-k-new').textContent   = newAccts.toLocaleString();
  document.getElementById('db-k-sub').textContent   = subAccts.toLocaleString();

  // ── Broker counts map ──────────────────────────────────────
  const brokerCnt={}, brokerDep={};
  data.forEach(c => {
    const n = c.broker?.name || 'غير محدد';
    brokerCnt[n] = (brokerCnt[n]||0)+1;
    brokerDep[n] = (brokerDep[n]||0)+parseFloat(c.initial_deposit||0);
  });
  const sortedBrokers = Object.entries(brokerCnt).sort((a,b)=>b[1]-a[1]);

  // ── Top Broker ────────────────────────────────────────────
  const top1 = sortedBrokers[0];
  const topPercent1 = top1 ? Math.round(top1[1]/data.length*100) : 0;
  document.getElementById('db-top-broker').innerHTML = sortedBrokers.slice(0,5).map((([n,cnt],i) => `
    <div style="display:flex;align-items:center;gap:8px;padding:6px 0;${i<sortedBrokers.slice(0,5).length-1?'border-bottom:1px solid var(--brd1)':''}">
      <span style="font-size:16px">${i===0?'🥇':i===1?'🥈':i===2?'🥉':'🏅'}</span>
      <div style="flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx)">${n}</div>
        <div style="height:4px;background:var(--brd1);border-radius:2px;margin-top:3px">
          <div style="height:4px;background:var(--pri2);border-radius:2px;width:${Math.round(cnt/top1[1]*100)}%"></div>
        </div>
      </div>
      <span style="font-size:11px;font-weight:700;color:var(--pri2)">${cnt}</span>
    </div>`)).join('') || '<div style="color:var(--mu);font-size:12px;text-align:center;padding:16px">لا توجد بيانات</div>';

  // ── Top Marketer ──────────────────────────────────────────
  const mktCnt={};
  data.forEach(c => {
    if (c.marketer?.name)    mktCnt[c.marketer.name]    = (mktCnt[c.marketer.name]||0)+1;
    if (c.ext_marketer1?.name) mktCnt[c.ext_marketer1.name] = (mktCnt[c.ext_marketer1.name]||0)+1;
    if (c.ext_marketer2?.name) mktCnt[c.ext_marketer2.name] = (mktCnt[c.ext_marketer2.name]||0)+1;
  });
  const sortedMkt = Object.entries(mktCnt).sort((a,b)=>b[1]-a[1]);
  const topM1 = sortedMkt[0];
  document.getElementById('db-top-marketer').innerHTML = sortedMkt.slice(0,5).map(([n,cnt],i) => `
    <div style="display:flex;align-items:center;gap:8px;padding:6px 0;${i<sortedMkt.slice(0,5).length-1?'border-bottom:1px solid var(--brd1)':''}">
      <span style="font-size:16px">${i===0?'🥇':i===1?'🥈':i===2?'🥉':'🏅'}</span>
      <div style="flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx)">${n}</div>
        <div style="height:4px;background:var(--brd1);border-radius:2px;margin-top:3px">
          <div style="height:4px;background:var(--gr);border-radius:2px;width:${topM1?Math.round(cnt/topM1[1]*100):100}%"></div>
        </div>
      </div>
      <span style="font-size:11px;font-weight:700;color:var(--gr)">${cnt}</span>
    </div>`).join('') || '<div style="color:var(--mu);font-size:12px;text-align:center;padding:16px">لا توجد بيانات</div>';

  // ── Kind donut ────────────────────────────────────────────
  destroyChart('dbKind');
  const kindEl = document.getElementById('db-chart-kind');
  if (kindEl) rptCharts.dbKind = new Chart(kindEl, {
    type:'doughnut',
    data:{labels:['New — جديد','Sub — فرعي'],datasets:[{data:[newAccts,subAccts],backgroundColor:['#2E86AB','#22C97A'],borderWidth:0,hoverOffset:6}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'65%',
      plugins:{legend:{position:'bottom',labels:{color:tc(),font:{size:9},boxWidth:8,padding:6}},
        tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/data.length*100)}%)`}}}}
  });

  // ── Broker count bar ──────────────────────────────────────
  const topBrkrs = sortedBrokers.slice(0,10);
  destroyChart('dbBrokerCnt');
  const bCntEl = document.getElementById('db-chart-broker-cnt');
  if (bCntEl) rptCharts.dbBrokerCnt = new Chart(bCntEl, {
    type:'bar',
    data:{labels:topBrkrs.map(([n])=>n), datasets:[{data:topBrkrs.map(([,c])=>c), backgroundColor:COLORS, borderRadius:5, borderSkipped:false}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false},
      tooltip:{callbacks:{label:ctx=>`${ctx.raw} حساب (${Math.round(ctx.raw/data.length*100)}%)`}}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}}}}
  });

  // ── Broker initial deposit doughnut ───────────────────────
  const topBrkrDep = Object.entries(brokerDep).sort((a,b)=>b[1]-a[1]).slice(0,8);
  destroyChart('dbBrokerDep');
  const bDepEl = document.getElementById('db-chart-broker-dep');
  if (bDepEl) rptCharts.dbBrokerDep = new Chart(bDepEl, {
    type:'doughnut',
    data:{labels:topBrkrDep.map(([n])=>n), datasets:[{data:topBrkrDep.map(([,v])=>Math.round(v)), backgroundColor:COLORS, borderWidth:2, borderColor:'var(--bg3)', hoverOffset:6}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'55%',
      plugins:{legend:{position:'right',labels:{color:tc(),font:{size:8},boxWidth:7,padding:4}},
        tooltip:{callbacks:{label:ctx=>`${ctx.label}: ${fmtK(ctx.raw)}`}}}}
  });

  // ── Monthly accounts bar ──────────────────────────────────
  const mCnt={};
  data.forEach(c => { mCnt[c.month]=(mCnt[c.month]||0)+1; });
  const mKeys = Object.keys(mCnt).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  destroyChart('dbMonthly');
  const mEl = document.getElementById('db-chart-monthly');
  if (mEl) rptCharts.dbMonthly = new Chart(mEl, {
    type:'bar',
    data:{labels:mKeys, datasets:[{label:'عدد الحسابات',data:mKeys.map(m=>mCnt[m]),backgroundColor:'rgba(46,134,171,.75)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}}}}
  });

  // ── Marketer performance table ────────────────────────────
  const mktFull = {};
  data.forEach(c => {
    [[c.marketer,'داخلي'],[c.ext_marketer1,'خارجي 1'],[c.ext_marketer2,'خارجي 2']].forEach(([emp,type]) => {
      if (!emp?.name) return;
      if (!mktFull[emp.name]) mktFull[emp.name] = {name:emp.name,type,cnt:0,dep:0};
      mktFull[emp.name].cnt++;
      mktFull[emp.name].dep += parseFloat(c.initial_deposit||0);
    });
  });
  const mktRows = Object.values(mktFull).sort((a,b)=>b.cnt-a.cnt);
  document.getElementById('db-marketer-table').innerHTML = mktRows.length ? `
    <table style="width:100%;border-collapse:collapse;font-size:11px">
      <thead><tr style="color:var(--mu)">
        <th style="text-align:right;padding:4px 6px">#</th>
        <th style="text-align:right;padding:4px 6px">الاسم</th>
        <th style="text-align:center;padding:4px 6px">النوع</th>
        <th style="text-align:center;padding:4px 6px">الحسابات</th>
        <th style="text-align:center;padding:4px 6px">الإيداع الأولي</th>
        <th style="text-align:center;padding:4px 6px">النسبة</th>
      </tr></thead>
      <tbody>${mktRows.map((m,i)=>`
        <tr style="${i%2===0?'background:rgba(46,134,171,.03)':''}">
          <td style="padding:5px 6px;color:var(--mu)">${i+1}</td>
          <td style="padding:5px 6px;font-weight:700">${m.name}</td>
          <td style="padding:5px 6px;text-align:center"><span class="badge ${m.type==='داخلي'?'badge-blue':'badge-orange'}" style="font-size:9px">${m.type}</span></td>
          <td style="padding:5px 6px;text-align:center;font-weight:700;color:var(--pri2)">${m.cnt}</td>
          <td style="padding:5px 6px;text-align:center;color:var(--gr)">${fmtK(m.dep)}</td>
          <td style="padding:5px 6px;text-align:center">
            <div style="background:var(--brd1);border-radius:2px;height:5px;min-width:50px">
              <div style="background:var(--gr);border-radius:2px;height:5px;width:${mktRows[0]?Math.round(m.cnt/mktRows[0].cnt*100):0}%"></div>
            </div>
          </td>
        </tr>`).join('')}
      </tbody>
    </table>` : '<div style="color:var(--mu);font-size:12px;text-align:center;padding:16px">لا توجد بيانات مسوّقين</div>';
}

// ── Build Diagrams (Diagrams tab) ──────────────────────────
function buildDiagrams() {
  if (!RD.length) return;
  const data = RD;

  // ── Monthly line ──────────────────────────────────────────
  const mDep={}, mCnt={};
  data.forEach(c => {
    mDep[c.month]=(mDep[c.month]||0)+parseFloat(c.initial_deposit||0);
    mCnt[c.month]=(mCnt[c.month]||0)+1;
  });
  const mKeys = Object.keys(mDep).sort((a,b)=>new Date('01 '+a)-new Date('01 '+b));
  destroyChart('line');
  const lineEl = document.getElementById('rpt-line');
  if (lineEl) rptCharts.line = new Chart(lineEl, {
    type:'line',
    data:{labels:mKeys, datasets:[{label:'إيداع أولي',data:mKeys.map(m=>mDep[m]),borderColor:'#2E86AB',backgroundColor:'rgba(46,134,171,.1)',tension:.4,fill:true,pointRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}}}}
  });

  // ── Count bar ──────────────────────────────────────────────
  destroyChart('cnt');
  const cntEl = document.getElementById('rpt-cnt-bar');
  if (cntEl) rptCharts.cnt = new Chart(cntEl, {
    type:'bar', data:{labels:mKeys, datasets:[{label:'عدد الحسابات',data:mKeys.map(m=>mCnt[m]),backgroundColor:'rgba(34,201,122,.65)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}}}}
  });

  // ── Compare initial vs broker commission ──────────────────
  const brDep2={}, brComm2={};
  data.forEach(c => {
    const n=c.broker?.name||'غير محدد';
    brDep2[n]=(brDep2[n]||0)+parseFloat(c.initial_deposit||0);
    if (!brComm2[n]) brComm2[n]=[];
    brComm2[n].push(parseFloat(c.broker_commission||0));
  });
  const brs2=Object.keys(brDep2);
  destroyChart('compare');
  const compEl = document.getElementById('rpt-compare');
  if (compEl) rptCharts.compare = new Chart(compEl, {
    type:'bar',
    data:{labels:brs2, datasets:[
      {label:'إيداع أولي',data:brs2.map(b=>brDep2[b]),backgroundColor:'rgba(46,134,171,.7)',borderRadius:3},
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:tc(),font:{size:9},boxWidth:8}}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}}}}
  });

  // ── Mod ratio ──────────────────────────────────────────────
  const modCount=data.filter(c=>c.status==='modified').length;
  const normalCount=data.filter(c=>c.status!=='modified'&&c.status!=='new_added').length;
  const newCount=data.filter(c=>c.status==='new_added').length;
  destroyChart('modratio');
  const mrEl = document.getElementById('rpt-mod-ratio');
  if (mrEl) rptCharts.modratio = new Chart(mrEl, {
    type:'doughnut',
    data:{labels:['معدّلة 🟡','عادية','مضافة جديدة'],datasets:[{data:[modCount,normalCount,newCount],backgroundColor:['#F5A623','#2E86AB','#22C97A'],borderWidth:2,borderColor:'var(--bg3)'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:tc(),font:{size:9},boxWidth:8,padding:6}}}}
  });

  // ── Avg commission rate per broker ─────────────────────────
  const brs3=Object.keys(brComm2);
  const avgComm=brs3.map(b=>brComm2[b].reduce((a,v)=>a+v,0)/brComm2[b].length);
  destroyChart('comm');
  const commEl = document.getElementById('rpt-comm-rate');
  if (commEl) rptCharts.comm = new Chart(commEl, {
    type:'bar', data:{labels:brs3,datasets:[{label:'ع. بروكر ($/lot)',data:avgComm,backgroundColor:'rgba(123,104,238,.7)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9},callback:v=>'$'+v},grid:{color:gc()}}}}
  });
}

// ── Export functions ───────────────────────────────────────
function exportRptExcel() {
  if (!RD.length) { toast('لا توجد بيانات', 'error'); return; }
  const headers = ['رقم الحساب','البروكر','مسوّق داخلي','مسوّق خارجي1','مسوّق خارجي2','إيداع أولي','إيداع شهري','ع.بروكر','ع.داخلي','ع.خارجي1','ع.خارجي2','النوع','الشهر','الحالة'];
  const rows = [headers, ...RD.map(c=>[
    c.account_number, c.broker?.name||'', c.marketer?.name||'',
    c.ext_marketer1?.name||'', c.ext_marketer2?.name||'',
    c.initial_deposit, c.monthly_deposit,
    '$'+c.broker_commission+'/lot', '$'+(c.marketer_commission||0)+'/lot',
    '$'+(c.ext_commission1||0)+'/lot', '$'+(c.ext_commission2||0)+'/lot',
    c.account_kind, c.month,
    c.status==='modified'?'🟡 معدّل':c.status==='new_added'?'🆕 جديد':'عادي',
  ])];
  const wb=XLSX.utils.book_new();
  const ws=XLSX.utils.aoa_to_sheet(rows);
  XLSX.utils.book_append_sheet(wb,ws,'التقرير');
  XLSX.writeFile(wb,'WafraGulf_Report_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast('تم تحميل Excel ✅','success');
}

function exportRptPdf() {
  if (!RD.length) { toast('لا توجد بيانات','error'); return; }
  const {jsPDF}=window.jspdf;
  const doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a3'});
  doc.setFontSize(13); doc.setTextColor(46,134,171);
  doc.text('وفرة الخليجية — التقرير الديناميكي',210,14,{align:'center'});
  doc.autoTable({
    startY:22,
    head:[['AC No.','Broker','Int.Mkt','Ext1','Ext2','Initial','Monthly','B.Comm','M.Comm','E1C','E2C','Kind','Month','Status']],
    body:RD.map(c=>[c.account_number,c.broker?.name||'',c.marketer?.name||'',c.ext_marketer1?.name||'',c.ext_marketer2?.name||'',fmt(c.initial_deposit),fmt(c.monthly_deposit),'$'+c.broker_commission,'$'+(c.marketer_commission||0),'$'+(c.ext_commission1||0),'$'+(c.ext_commission2||0),c.account_kind,c.month,c.status]),
    styles:{fontSize:6,cellPadding:2},
    headStyles:{fillColor:[46,134,171],textColor:[255,255,255]},
    didParseCell:d=>{if(d.row.raw?.[13]==='modified')Object.values(d.row.cells).forEach(cell=>{cell.styles.fillColor=[255,248,220];})}
  });
  doc.save('WafraGulf_Report_'+new Date().toISOString().slice(0,10)+'.pdf');
  toast('تم تحميل PDF ✅','success');
}

function clearRptFilters() {
  ['rf-from','rf-to','rf-broker','rf-branch','rf-status','rf-kind'].forEach(id=>{ const el=document.getElementById(id); if(el)el.value=''; });
  document.getElementById('rf-min').value='0';
}

loadFilterOptions();
</script>
@endpush
