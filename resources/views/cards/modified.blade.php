@extends('layouts.app')
@section('title','الحسابات المعدّلة')
@section('page-title','الحسابات المعدّلة')
@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'modified'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

<div id="mod-info-bar" style="background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.25);border-radius:11px;padding:11px 15px;margin-bottom:14px;font-size:12px;color:var(--or)">
  ✏️ <span id="mod-info-text">جميع الحسابات التي تم تعديلها — تظهر بتمييز <strong>أصفر</strong> في التقارير.</span>
</div>

<div class="panel">
  <div class="panel-header">
    <div class="panel-title" id="mod-panel-title">✏️ الحسابات المعدّلة <span id="mod-count" style="font-size:11px;color:var(--mu)"></span></div>
    <div style="display:flex;gap:6px">
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportModExcel()">📗 Excel</button>
    </div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th id="mod-th-ac">رقم الحساب</th>
          <th id="mod-th-month">الشهر</th>
          <th id="mod-th-broker">البروكر</th>
          <th id="mod-th-dep">إيداع شهري</th>
          <th id="mod-th-reason">سبب التعديل</th>
          <th id="mod-th-date">تاريخ التعديل</th>
          <th id="mod-th-by">بواسطة</th>
        </tr>
      </thead>
      <tbody id="mod-tbody">
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)" id="mod-loading">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}

@endsection
@push('scripts')
<script>
const MOD={
  ar:{
    tbTitle:'الحسابات المعدّلة',
    infoText:'جميع الحسابات التي تم تعديلها — تظهر بتمييز <strong>أصفر</strong> في التقارير.',
    panelTitle:'✏️ الحسابات المعدّلة',
    thAc:'رقم الحساب', thMonth:'الشهر', thBroker:'البروكر',
    thDep:'إيداع شهري', thReason:'سبب التعديل', thDate:'تاريخ التعديل', thBy:'بواسطة',
    records:'سجل', loading:'جاري التحميل...', empty:'لا توجد حسابات معدّلة',
    noData:'لا توجد بيانات للتصدير', soon:'قريباً',
  },
  en:{
    tbTitle:'Modified Accounts',
    infoText:'All modified accounts — highlighted in <strong>yellow</strong> in reports.',
    panelTitle:'✏️ Modified Accounts',
    thAc:'AC No.', thMonth:'Month', thBroker:'Broker',
    thDep:'Monthly Dep.', thReason:'Edit Reason', thDate:'Edit Date', thBy:'By',
    records:'records', loading:'Loading...', empty:'No modified accounts found',
    noData:'No data to export', soon:'Coming soon',
  }
};
function modL()   { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function mod(key) { const l=modL(); return MOD[l]?.[key]??MOD.ar[key]??key; }

function modApplyLang(){
  const map={
    'mod-th-ac':'thAc','mod-th-month':'thMonth','mod-th-broker':'thBroker',
    'mod-th-dep':'thDep','mod-th-reason':'thReason','mod-th-date':'thDate','mod-th-by':'thBy',
  };
  Object.entries(map).forEach(([id,key])=>{const el=document.getElementById(id);if(el)el.textContent=mod(key);});
  // Info bar (has HTML)
  const info=document.getElementById('mod-info-text');
  if(info) info.innerHTML=mod('infoText');
  // Panel title
  const pt=document.getElementById('mod-panel-title');
  if(pt){ const cs=document.getElementById('mod-count'); pt.textContent=mod('panelTitle')+' '; if(cs) pt.appendChild(cs); }
  // Topbar
  const tb=document.querySelector('.tb-title');if(tb) tb.textContent=mod('tbTitle');
  // Re-render table
  if(_modCards.length) renderModTable(_modCards);
}
const _modOrig=window.applyLang;
window.applyLang=function(lang){if(_modOrig)_modOrig(lang);modApplyLang();};

let _modCards=[];

function renderModTable(cards){
  const isEn=modL()==='en';
  document.getElementById('mod-count').textContent=cards.length+' '+mod('records');
  document.getElementById('mod-tbody').innerHTML=cards.map(c=>`
    <tr class="row-modified">
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="color:var(--mu)">${c.month}</td>
      <td style="color:var(--pri2);font-weight:600">${c.broker?.name||'—'}</td>
      <td class="mono c-green">${fmt(c.monthly_deposit)}</td>
      <td style="color:var(--or)">${c.modifications?.[0]?.reason||'—'}</td>
      <td style="color:var(--mu)">${c.modifications?.[0]?.modified_at?new Date(c.modifications[0].modified_at).toLocaleDateString():'—'}</td>
      <td style="color:var(--mu)">${c.modifications?.[0]?.modified_by?.name||'—'}</td>
    </tr>`).join()||`<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">${mod('empty')}</td></tr>`;
}

async function loadModified(){
  document.getElementById('mod-tbody').innerHTML=`<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">${mod('loading')}</td></tr>`;
  const r=await api('GET','/cards?status=modified&per_page=200');
  if(!r.success)return;
  _modCards=r.data?.data||[];
  renderModTable(_modCards);
}

function exportModExcel(){
  if(!_modCards.length){toast(mod('noData'),'error');return;}
  const isEn=modL()==='en';
  const hdr=isEn?['AC No.','Month','Broker','Monthly Dep.','Reason','Date','By']:['رقم الحساب','الشهر','البروكر','إيداع شهري','سبب التعديل','تاريخ التعديل','بواسطة'];
  const rows=[hdr,..._modCards.map(c=>[
    c.account_number, c.month, c.broker?.name||'—', c.monthly_deposit,
    c.modifications?.[0]?.reason||'—',
    c.modifications?.[0]?.modified_at?new Date(c.modifications[0].modified_at).toLocaleDateString():'—',
    c.modifications?.[0]?.modified_by?.name||'—',
  ])];
  const wb=XLSX.utils.book_new();
  const ws=XLSX.utils.aoa_to_sheet(rows);
  ws['!cols']=hdr.map(()=>({wch:16}));
  XLSX.utils.book_append_sheet(wb,ws,isEn?'Modified Accounts':'الحسابات المعدّلة');
  XLSX.writeFile(wb,'WafraGulf_Modified_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast('Excel ✅','success');
}

modApplyLang();
loadModified();
</script>
@endpush
