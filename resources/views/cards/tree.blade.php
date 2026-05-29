@extends('layouts.app')
@section('title','Account Tree')
@section('page-title','Account Tree')
@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'tree'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

<!-- KPI Strip -->
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px">
  <div class="kpi-card kpi-blue">
    <div class="kpi-label" id="tre-kpi-total">إجمالي الحسابات</div>
    <div class="kpi-value" id="ts-total">—</div>
    <div class="kpi-icon">📁</div>
  </div>
  <div class="kpi-card kpi-teal">
    <div class="kpi-label" id="tre-kpi-broker">إجمالي ع. البروكر</div>
    <div class="kpi-value" id="ts-broker">—</div>
    <div class="kpi-icon">🧑‍💼</div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-label" id="tre-kpi-mkt">إجمالي ع. التسويق</div>
    <div class="kpi-value" id="ts-mkt">—</div>
    <div class="kpi-icon">📢</div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-label" id="tre-kpi-dep">إجمالي الإيداع الشهري</div>
    <div class="kpi-value" id="ts-dep">—</div>
    <div class="kpi-icon">💰</div>
  </div>
</div>

<!-- Filter Bar -->
<div class="panel" style="padding:12px 16px;margin-bottom:14px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px" id="tre-lbl-group">تجميع حسب</div>
      <select id="t-group" class="form-control" onchange="loadTree()">
        <option value="broker"       id="tre-grp-broker">البروكر</option>
        <option value="branch"       id="tre-grp-branch">الفرع</option>
        <option value="month"        id="tre-grp-month">الشهر</option>
        <option value="ext_marketer" id="tre-grp-ext">المسوّق الخارجي</option>
      </select>
    </div>
    <div>
      <div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px" id="tre-lbl-month">الشهر</div>
      <select id="t-month" class="form-control" onchange="loadTree()">
        <option value="" id="tre-opt-allmonths">كل الشهور</option>
      </select>
    </div>
    <button class="btn btn-primary btn-sm" onclick="loadTree()" id="tre-btn-refresh">🔄 تحديث</button>
    <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportTreeExcel()">📗 Excel</button>
  </div>
</div>

<!-- Tree Table -->
<div class="panel">
  <div class="table-scroll">
    <table class="data-table" style="min-width:1000px">
      <thead>
        <tr>
          <th id="tre-th-group">المجموعة / الحساب</th>
          <th id="tre-th-month">الشهر</th>
          <th id="tre-th-dep">إيداع أولي</th>
          <th id="tre-th-mon">إيداع شهري</th>
          <th id="tre-th-broker">البروكر</th>
          <th id="tre-th-bcomm">ع. بروكر</th>
          <th id="tre-th-mktr">مسوّق داخلي</th>
          <th id="tre-th-mcomm">ع. داخلي</th>
          <th id="tre-th-ext1">مسوّق خارجي 1</th>
          <th id="tre-th-e1comm">ع. خارجي 1</th>
          <th id="tre-th-ext2">مسوّق خارجي 2</th>
          <th id="tre-th-e2comm">ع. خارجي 2</th>
          <th id="tre-th-total">إجمالي ع.</th>
          <th id="tre-th-status">الحالة</th>
        </tr>
      </thead>
      <tbody id="tree-tbody">
        <tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)" id="tre-loading">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}

@endsection
@push('scripts')
<script>
const TRE={
  ar:{
    tbTitle:'شجرة الحسابات وتوزيع العمولات',
    kpiTotal:'إجمالي الحسابات', kpiBroker:'إجمالي ع. البروكر',
    kpiMkt:'إجمالي ع. التسويق', kpiDep:'إجمالي الإيداع الشهري',
    lblGroup:'تجميع حسب', lblMonth:'الشهر',
    grpBroker:'البروكر', grpBranch:'الفرع', grpMonth:'الشهر', grpExt:'المسوّق الخارجي',
    allMonths:'كل الشهور', btnRefresh:'🔄 تحديث',
    thGroup:'المجموعة / الحساب', thMonth:'الشهر',
    thDep:'إيداع أولي', thMon:'إيداع شهري',
    thBroker:'البروكر', thBComm:'ع. بروكر',
    thMktr:'مسوّق داخلي', thMComm:'ع. داخلي',
    thExt1:'مسوّق خارجي 1', thE1Comm:'ع. خارجي 1',
    thExt2:'مسوّق خارجي 2', thE2Comm:'ع. خارجي 2',
    thTotal:'إجمالي ع.', thStatus:'الحالة',
    loading:'جاري التحميل...', noData:'لا توجد بيانات',
    accounts:'حساب', totalComm:'إجمالي العمولات',
    stMod:'✏️ معدّل', stNormal:'عادي', noExport:'لا توجد بيانات',
  },
  en:{
    tbTitle:'Account Tree & Commission Distribution',
    kpiTotal:'Total Accounts', kpiBroker:'Total Broker Comm.',
    kpiMkt:'Total Marketing Comm.', kpiDep:'Total Monthly Deposit',
    lblGroup:'Group by', lblMonth:'Month',
    grpBroker:'Broker', grpBranch:'Branch', grpMonth:'Month', grpExt:'External Marketer',
    allMonths:'All Months', btnRefresh:'🔄 Refresh',
    thGroup:'Group / Account', thMonth:'Month',
    thDep:'Initial Dep.', thMon:'Monthly Dep.',
    thBroker:'Broker', thBComm:'B.Comm',
    thMktr:'Int. Marketer', thMComm:'Int. Comm',
    thExt1:'External Mktr 1', thE1Comm:'Ext 1 Comm',
    thExt2:'External Mktr 2', thE2Comm:'Ext 2 Comm',
    thTotal:'Total Comm.', thStatus:'Status',
    loading:'Loading...', noData:'No data available',
    accounts:'accounts', totalComm:'Total Commission',
    stMod:'✏️ Modified', stNormal:'Active', noExport:'No data to export',
  }
};
function treL()   { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function tre(key) { const l=treL(); return TRE[l]?.[key]??TRE.ar[key]??key; }

function treApplyLang(){
  const map={
    'tre-kpi-total':'kpiTotal','tre-kpi-broker':'kpiBroker','tre-kpi-mkt':'kpiMkt','tre-kpi-dep':'kpiDep',
    'tre-lbl-group':'lblGroup','tre-lbl-month':'lblMonth','tre-btn-refresh':'btnRefresh',
    'tre-th-group':'thGroup','tre-th-month':'thMonth','tre-th-dep':'thDep','tre-th-mon':'thMon',
    'tre-th-broker':'thBroker','tre-th-bcomm':'thBComm','tre-th-mktr':'thMktr','tre-th-mcomm':'thMComm',
    'tre-th-ext1':'thExt1','tre-th-e1comm':'thE1Comm','tre-th-ext2':'thExt2','tre-th-e2comm':'thE2Comm',
    'tre-th-total':'thTotal','tre-th-status':'thStatus',
  };
  Object.entries(map).forEach(([id,key])=>{const el=document.getElementById(id);if(el)el.textContent=tre(key);});
  const optMap={
    'tre-grp-broker':'grpBroker','tre-grp-branch':'grpBranch','tre-grp-month':'grpMonth','tre-grp-ext':'grpExt',
    'tre-opt-allmonths':'allMonths',
  };
  Object.entries(optMap).forEach(([id,key])=>{const el=document.getElementById(id);if(el)el.textContent=tre(key);});
  const tb=document.querySelector('.tb-title');if(tb)tb.textContent=tre('tbTitle');
  if(treeData.length) renderTree(treeData, _treeSummary);
}
const _treOrig=window.applyLang;
window.applyLang=function(lang){if(_treOrig)_treOrig(lang);treApplyLang();};

let treeData=[], _treeSummary={};

async function loadTree(){
  const group=document.getElementById('t-group').value;
  const month=document.getElementById('t-month').value;
  const params=new URLSearchParams({group_by:group});
  if(month)params.set('month',month);
  document.getElementById('tree-tbody').innerHTML=`<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">${tre('loading')}</td></tr>`;
  const r=await api('GET','/cards/tree?'+params);
  if(!r.success)return;
  treeData=r.tree;
  _treeSummary=r.summary;
  renderTree(treeData, _treeSummary);
}

function renderTree(data, s){
  document.getElementById('ts-total').textContent=(s.total_accounts||0).toLocaleString();
  document.getElementById('ts-broker').textContent='$'+(s.total_broker_comm||0).toFixed(1)+'/lot';
  document.getElementById('ts-mkt').textContent='$'+((s.total_mkt_comm||0)+(s.total_ext1_comm||0)+(s.total_ext2_comm||0)).toFixed(1)+'/lot';
  document.getElementById('ts-dep').textContent=fmtK(s.total_monthly||0);

  let html='';
  data.forEach(g=>{
    html+=`<tr style="background:linear-gradient(135deg,rgba(46,134,171,.12),rgba(46,134,171,.06));font-weight:800">
      <td><div style="display:flex;align-items:center;gap:8px;padding:9px 14px">${g.group_icon||'📋'} <strong>${g.group_key}</strong> <span style="font-size:10px;color:var(--mu);font-weight:400">${g.count} ${tre('accounts')}</span></div></td>
      <td>—</td>
      <td class="mono c-blue" style="font-weight:700">${fmt(g.initial_deposit)}</td>
      <td class="mono c-green" style="font-weight:700">${fmt(g.monthly_deposit)}</td>
      <td colspan="8"><span style="font-size:11px;color:var(--mu)">${tre('totalComm')}: <b class="c-orange">$${(g.total_comm||0).toFixed(1)}/lot</b></span></td>
      <td></td>
    </tr>`;
    (g.cards||[]).forEach(c=>{
      const totalComm=(parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0));
      html+=`<tr class="${c.status==='modified'?'row-modified':''}">
        <td><div style="padding:8px 14px 8px 34px;display:flex;align-items:center;gap:6px"><span style="color:var(--brd2)">└</span> <span class="ac-num">#${c.account_number}</span>${c.status==='modified'?'<span class="badge badge-orange" style="font-size:9px">✏️</span>':''}</div></td>
        <td style="color:var(--mu)">${c.month}</td>
        <td class="mono c-blue">${fmt(c.initial_deposit)}</td>
        <td class="mono c-green">${fmt(c.monthly_deposit)}</td>
        <td style="font-weight:600;color:var(--pri2)">${c.broker?.name||'—'}</td>
        <td class="mono c-blue">$${c.broker_commission||0}/lot</td>
        <td style="color:var(--m2)">${c.marketer?.name&&c.marketer.name!==c.broker?.name?c.marketer.name:'—'}</td>
        <td class="mono c-green">$${c.marketer_commission||0}/lot</td>
        <td style="color:var(--pu)">${c.ext_marketer1?.name||'—'}</td>
        <td class="mono" style="color:var(--pu)">$${c.ext_commission1||0}/lot</td>
        <td style="color:var(--pu)">${c.ext_marketer2?.name||'—'}</td>
        <td class="mono" style="color:var(--pu)">$${c.ext_commission2||0}/lot</td>
        <td><span class="badge badge-orange">$${totalComm.toFixed(1)}/lot</span></td>
        <td>${c.status==='modified'?`<span class="badge badge-orange">${tre('stMod')}</span>`:`<span class="badge badge-blue">${tre('stNormal')}</span>`}</td>
      </tr>`;
    });
  });
  document.getElementById('tree-tbody').innerHTML=html||`<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">${tre('noData')}</td></tr>`;
}

async function initMonths(){
  const r=await api('GET','/cards?per_page=1');
  const m=document.getElementById('t-month');
  ['Jan 2025','Feb 2025','Mar 2025','Apr 2025','May 2025','Jun 2025','Jul 2025','Aug 2025','Sep 2025','Oct 2025','Nov 2025','Dec 2025'].forEach(mo=>{
    const o=document.createElement('option');o.value=o.textContent=mo;m.appendChild(o);
  });
}

function exportTreeExcel(){
  if(!treeData.length){toast(tre('noExport'),'error');return;}
  toast('قريباً / Coming soon','info');
}

treApplyLang();
initMonths();
loadTree();
</script>
@endpush
