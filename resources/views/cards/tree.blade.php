@extends('layouts.app')
@section('title','شجرة الحسابات')
@section('page-title','شجرة الحسابات وتوزيع العمولات')
@section('content')
<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:14px">
  <div class="kpi-card kpi-blue"><div class="kpi-label">إجمالي الحسابات</div><div class="kpi-value" id="ts-total">—</div><div class="kpi-icon">📁</div></div>
  <div class="kpi-card kpi-teal"><div class="kpi-label">إجمالي ع. البروكر</div><div class="kpi-value" id="ts-broker">—</div><div class="kpi-icon">🧑‍💼</div></div>
  <div class="kpi-card kpi-green"><div class="kpi-label">إجمالي ع. التسويق</div><div class="kpi-value" id="ts-mkt">—</div><div class="kpi-icon">📢</div></div>
  <div class="kpi-card kpi-orange"><div class="kpi-label">إجمالي الإيداع الشهري</div><div class="kpi-value" id="ts-dep">—</div><div class="kpi-icon">💰</div></div>
</div>
<div class="panel" style="padding:12px 16px;margin-bottom:14px">
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
    <div><div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">تجميع حسب</div><select id="t-group" class="form-control" onchange="loadTree()"><option value="broker">البروكر</option><option value="branch">الفرع</option><option value="month">الشهر</option><option value="ext_marketer">المسوّق الخارجي</option></select></div>
    <div><div style="font-size:9px;color:var(--mu);text-transform:uppercase;margin-bottom:5px">الشهر</div><select id="t-month" class="form-control" onchange="loadTree()"><option value="">كل الشهور</option></select></div>
    <button class="btn btn-primary btn-sm" onclick="loadTree()">🔄 تحديث</button>
    <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportTreeExcel()">📗 Excel</button>
  </div>
</div>
<div class="panel"><div class="table-scroll"><table class="data-table" style="min-width:1000px">
  <thead><tr><th>المجموعة / الحساب</th><th>الشهر</th><th>إيداع أولي</th><th>إيداع شهري</th><th>البروكر</th><th>ع. بروكر</th><th>مسوّق داخلي</th><th>ع. داخلي</th><th>مسوّق خارجي 1</th><th>ع. خارجي 1</th><th>مسوّق خارجي 2</th><th>ع. خارجي 2</th><th>إجمالي ع.</th><th>الحالة</th></tr></thead>
  <tbody id="tree-tbody"><tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr></tbody>
</table></div></div>
@endsection
@push('scripts')
<script>
let treeData=[];
async function loadTree(){
  const group=document.getElementById('t-group').value;
  const month=document.getElementById('t-month').value;
  const params=new URLSearchParams({group_by:group});
  if(month)params.set('month',month);
  const r=await api('GET','/cards/tree?'+params);
  if(!r.success)return;
  treeData=r.tree;
  const s=r.summary;
  document.getElementById('ts-total').textContent=s.total_accounts.toLocaleString();
  document.getElementById('ts-broker').textContent='$'+(s.total_broker_comm||0).toFixed(1)+'/lot';
  document.getElementById('ts-mkt').textContent='$'+((s.total_mkt_comm||0)+(s.total_ext1_comm||0)+(s.total_ext2_comm||0)).toFixed(1)+'/lot';
  document.getElementById('ts-dep').textContent=fmtK(s.total_monthly);
  let html='';
  treeData.forEach(g=>{
    html+=`<tr style="background:linear-gradient(135deg,rgba(46,134,171,.12),rgba(46,134,171,.06));font-weight:800">
      <td><div style="display:flex;align-items:center;gap:8px;padding:9px 14px">${g.group_icon||'📋'} <strong>${g.group_key}</strong> <span style="font-size:10px;color:var(--mu);font-weight:400">${g.count} حساب</span></div></td>
      <td>—</td>
      <td class="mono c-blue" style="font-weight:700">${fmt(g.initial_deposit)}</td>
      <td class="mono c-green" style="font-weight:700">${fmt(g.monthly_deposit)}</td>
      <td colspan="8"><span style="font-size:11px;color:var(--mu)">إجمالي العمولات: <b class="c-orange">$${(g.total_comm||0).toFixed(1)}/lot</b></span></td>
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
        <td>${c.status==='modified'?'<span class="badge badge-orange">✏️ معدّل</span>':'<span class="badge badge-blue">عادي</span>'}</td>
      </tr>`;
    });
  });
  document.getElementById('tree-tbody').innerHTML=html||'<tr><td colspan="14" style="text-align:center;padding:40px;color:var(--mu)">لا توجد بيانات</td></tr>';
}
async function initMonths(){const r=await api('GET','/cards?per_page=1');const m=document.getElementById('t-month');['Jan 2025','Feb 2025','Mar 2025','Apr 2025','May 2025','Jun 2025','Jul 2025','Aug 2025','Sep 2025'].forEach(mo=>{const o=document.createElement('option');o.value=o.textContent=mo;m.appendChild(o);});}
function exportTreeExcel(){if(!treeData.length){toast('لا توجد بيانات','error');return;}toast('قريباً','info');}
initMonths();loadTree();
</script>
@endpush
