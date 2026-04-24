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

<!-- ── TAB: Dashboard ── -->
<div id="tab-dash" style="display:none">
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:14px" id="rpt-kpis" style="display:none">
    <div class="panel" style="display:grid;grid-template-columns:1fr 1fr;gap:10px;padding:14px">
      <div class="kpi-card kpi-blue"><div class="kpi-label">السجلات</div><div class="kpi-value" id="rpt-k-total">—</div></div>
      <div class="kpi-card kpi-green"><div class="kpi-label">إيداع شهري</div><div class="kpi-value" id="rpt-k-mon">—</div></div>
      <div class="kpi-card kpi-teal"><div class="kpi-label">إيداع أولي</div><div class="kpi-value" id="rpt-k-dep">—</div></div>
      <div class="kpi-card kpi-orange"><div class="kpi-label">معدّلة</div><div class="kpi-value" id="rpt-k-mod">—</div></div>
    </div>
    <div class="panel"><div class="panel-header"><div class="panel-title">🥇 أفضل بروكر في التقرير</div></div>
      <div class="panel-body" id="rpt-top-broker"></div></div>
  </div>
  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
    <div class="panel"><div class="panel-header"><div class="panel-title">📊 إيداع شهري بالبروكر</div></div>
      <div style="height:220px;padding:14px;position:relative"><canvas id="rpt-chart-broker"></canvas></div></div>
    <div class="panel"><div class="panel-header"><div class="panel-title">🥧 توزيع الحسابات بالنوع</div></div>
      <div style="height:220px;padding:14px;position:relative"><canvas id="rpt-chart-kind"></canvas></div></div>
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
let RD = []; // Report data
let rptCharts = {};
const COLORS = ['#2E86AB','#3A9DB5','#1A5F7A','#22C97A','#F5A623','#7B68EE','#E05050','#26D4E8'];

// ── Tab switching ──────────────────────────────────────────
let curTab = 'table';
function switchTab(name) {
  ['table','dash','diagrams'].forEach(t => {
    document.getElementById('tab-'+t).style.display = t===name ? 'block' : 'none';
    const btn = document.getElementById('tab-btn-'+t);
    if (btn) { btn.style.background = t===name ? 'var(--bg3)' : ''; btn.style.color = t===name ? 'var(--pri2)' : 'var(--m2)'; }
  });
  curTab = name;
  if (RD.length && (name==='dash'||name==='diagrams')) buildCharts();
}
switchTab('table');

// ── Load filter options ────────────────────────────────────
async function loadFilterOptions() {
  const [settings, employees, branches] = await Promise.all([
    api('GET', '/cards?per_page=1'),
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
    const sel = document.getElementById('rf-broker');
    employees.data.forEach(e => { const o=document.createElement('option');o.value=e.name;o.textContent=e.name;sel.appendChild(o); });
  }

  const brSel = document.getElementById('rf-branch');
  if (brSel && branches.success) {
    branches.data.forEach(b => { const o=document.createElement('option');o.value=b.id;o.textContent=b.name_ar;brSel.appendChild(o); });
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

  // Update KPIs for dashboard tab
  document.getElementById('rpt-k-total').textContent = RD.length.toLocaleString();
  document.getElementById('rpt-k-dep').textContent   = fmtK(s.total_initial_deposit);
  document.getElementById('rpt-k-mon').textContent   = fmtK(s.total_monthly_deposit);
  document.getElementById('rpt-k-mod').textContent   = s.modified_count;

  if (curTab === 'dash' || curTab === 'diagrams') buildCharts();

  toast(`تقرير: ${RD.length} سجل — معدّلة: ${s.modified_count} 🟡`, 'success');
}

// ── Build Charts ───────────────────────────────────────────
function destroyChart(id) { if (rptCharts[id]) { rptCharts[id].destroy(); rptCharts[id]=null; } }
const tc = () => getComputedStyle(document.documentElement).getPropertyValue('--mu').trim() || '#5A7A9A';
const gc = () => 'rgba(37,58,99,.3)';

function buildCharts() {
  if (!RD.length) return;

  // ── Broker bar ────────────────────────────────────────────
  const brokerMap = {};
  RD.forEach(c => {
    const n = c.broker?.name || 'Unknown';
    brokerMap[n] = (brokerMap[n]||0) + parseFloat(c.monthly_deposit||0);
  });
  const bNames = Object.keys(brokerMap), bVals = bNames.map(b=>brokerMap[b]);
  destroyChart('broker');
  rptCharts.broker = new Chart(document.getElementById('rpt-chart-broker'), {
    type:'bar', data:{labels:bNames, datasets:[{data:bVals, backgroundColor:COLORS, borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}}}}
  });

  // ── Top broker for dashboard ────────────────────────────
  const topBroker = Object.entries(brokerMap).sort((a,b)=>b[1]-a[1])[0];
  const cnt = RD.filter(c => c.broker?.name === topBroker?.[0]).length;
  document.getElementById('rpt-top-broker').innerHTML = topBroker ? `
    <div style="display:flex;align-items:center;gap:12px;padding:10px 0">
      <span style="font-size:28px">🥇</span>
      <div><div style="font-size:16px;font-weight:800;color:var(--pri2)">${topBroker[0]}</div>
      <div style="font-size:12px;color:var(--mu)">${cnt} حساب · ${fmtK(topBroker[1])}</div></div>
    </div>` : '—';

  // ── Kind pie ──────────────────────────────────────────────
  const kinds = {};
  RD.forEach(c => { const k = c.account_kind==='new'?'New':'Sub'; kinds[k]=(kinds[k]||0)+1; });
  destroyChart('kind');
  rptCharts.kind = new Chart(document.getElementById('rpt-chart-kind'), {
    type:'doughnut', data:{labels:Object.keys(kinds), datasets:[{data:Object.values(kinds), backgroundColor:['#2E86AB','#22C97A'], borderWidth:0}]},
    options:{responsive:true,maintainAspectRatio:false,cutout:'60%',plugins:{legend:{position:'bottom',labels:{color:tc(),font:{size:9},boxWidth:8,padding:6}}}}
  });

  // ── Monthly line ──────────────────────────────────────────
  const mDep={}, mCnt={};
  RD.forEach(c => {
    mDep[c.month]=(mDep[c.month]||0)+parseFloat(c.monthly_deposit||0);
    mCnt[c.month]=(mCnt[c.month]||0)+1;
  });
  const mKeys = Object.keys(mDep).sort();
  destroyChart('line');
  rptCharts.line = new Chart(document.getElementById('rpt-line'), {
    type:'line',
    data:{labels:mKeys, datasets:[{label:'إيداع شهري',data:mKeys.map(m=>mDep[m]),borderColor:'var(--pri2)',backgroundColor:'rgba(46,134,171,.1)',tension:.4,fill:true,pointRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}}}}
  });

  // ── Count bar ──────────────────────────────────────────────
  destroyChart('cnt');
  rptCharts.cnt = new Chart(document.getElementById('rpt-cnt-bar'), {
    type:'bar', data:{labels:mKeys, datasets:[{label:'عدد الحسابات',data:mKeys.map(m=>mCnt[m]),backgroundColor:'rgba(34,201,122,.65)',borderRadius:4}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{display:false}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}}}}
  });

  // ── Compare ────────────────────────────────────────────────
  const brDep2={}, brMon2={};
  RD.forEach(c => {
    const n=c.broker?.name||'Unknown';
    brDep2[n]=(brDep2[n]||0)+parseFloat(c.initial_deposit||0);
    brMon2[n]=(brMon2[n]||0)+parseFloat(c.monthly_deposit||0);
  });
  const brs2=Object.keys(brDep2);
  destroyChart('compare');
  rptCharts.compare = new Chart(document.getElementById('rpt-compare'), {
    type:'bar',
    data:{labels:brs2, datasets:[
      {label:'إيداع أولي',data:brs2.map(b=>brDep2[b]),backgroundColor:'rgba(46,134,171,.7)',borderRadius:3},
      {label:'إيداع شهري',data:brs2.map(b=>brMon2[b]),backgroundColor:'rgba(34,201,122,.6)',borderRadius:3},
    ]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{labels:{color:tc(),font:{size:9},boxWidth:8}}},
      scales:{x:{ticks:{color:tc(),font:{size:9}},grid:{color:gc()}},y:{ticks:{color:tc(),font:{size:9},callback:v=>'$'+v.toLocaleString()},grid:{color:gc()}}}}
  });

  // ── Mod ratio ──────────────────────────────────────────────
  const modCount=RD.filter(c=>c.status==='modified').length;
  const normalCount=RD.filter(c=>c.status!=='modified'&&c.status!=='new_added').length;
  const newCount=RD.filter(c=>c.status==='new_added').length;
  destroyChart('modratio');
  rptCharts.modratio = new Chart(document.getElementById('rpt-mod-ratio'), {
    type:'doughnut',
    data:{labels:['معدّلة 🟡','عادية','مضافة جديدة'],datasets:[{data:[modCount,normalCount,newCount],backgroundColor:['#F5A623','#2E86AB','#22C97A'],borderWidth:2,borderColor:'var(--bg3)'}]},
    options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom',labels:{color:tc(),font:{size:9},boxWidth:8,padding:6}}}}
  });

  // ── Commission rate ────────────────────────────────────────
  const brComm2={};
  RD.forEach(c => {
    const n=c.broker?.name||'Unknown';
    if (!brComm2[n]) brComm2[n]=[];
    brComm2[n].push(parseFloat(c.broker_commission||0));
  });
  const brs3=Object.keys(brComm2);
  const avgComm=brs3.map(b=>brComm2[b].reduce((a,v)=>a+v,0)/brComm2[b].length);
  destroyChart('comm');
  rptCharts.comm = new Chart(document.getElementById('rpt-comm-rate'), {
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
