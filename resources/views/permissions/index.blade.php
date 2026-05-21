@extends('layouts.app')
@section('title','صلاحيات المدير المالي')
@section('page-title','صلاحيات المدير المالي')
@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🔒 الصلاحيات الحصرية للمدير المالي</div></div>
    <div class="panel-body">
      @foreach([['🏢','إضافة فروع جديدة','المدير المالي هو الوحيد المخوّل بتعريف الفروع'],['👤','إنشاء حسابات المديرين','إنشاء حسابات مديري الفروع وتحديد صلاحياتهم'],['✅','اعتماد الموظفين الجدد','مديرو الفروع يضيفون موظفين بحالة قيد الانتظار'],['📋','تعريف أنواع الحسابات','تعريف أنواع الحسابات وحالاتها وأنواع التداول'],['📊','تقارير كل الفروع','عرض تقارير وبيانات جميع الفروع'],['🌐','تبديل الفروع','عرض بيانات الفروع المختلفة']] as [$ic,$t,$d])
      <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--brd1)">
        <div style="width:36px;height:36px;border-radius:9px;background:rgba(46,134,171,.15);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">{{ $ic }}</div>
        <div><div style="font-size:13px;font-weight:700">{{ $t }}</div><div style="font-size:11px;color:var(--mu);margin-top:3px">{{ $d }}</div><span class="badge badge-blue" style="margin-top:5px">🔒 مدير مالي فقط</span></div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title">⏳ اعتماد الموظفين الجدد <span id="pending-badge" class="badge badge-orange" style="display:none;margin-right:8px"></span></div></div>
    <div class="panel-body" id="pending-list"><div style="text-align:center;padding:30px;color:var(--mu)"><div style="font-size:32px;opacity:.3;margin-bottom:8px">✅</div>لا يوجد موظفون بانتظار الاعتماد</div></div>
  </div>
</div>
@endsection
@push('scripts')
<script>
async function loadPending(){
  const r=await api('GET','/employees/pending');
  if(!r.success)return;
  const badge=document.getElementById('pending-badge');
  if(r.count>0){badge.textContent=r.count+' بانتظار الاعتماد';badge.style.display='inline-flex';}
  document.getElementById('pending-list').innerHTML=r.data.length?r.data.map(e=>`
    <div style="display:flex;align-items:center;gap:12px;padding:12px;margin-bottom:8px;border:1px solid rgba(245,166,35,.3);border-radius:10px;background:rgba(245,166,35,.04)">
      <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--pri2),var(--pri3));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:white;flex-shrink:0">${e.name.charAt(0)}</div>
      <div style="flex:1"><div style="font-size:13px;font-weight:700">${e.name}</div><div style="font-size:11px;color:var(--mu);margin-top:2px">${e.role} · ${e.branch?.name_ar||'—'} · أضافه: ${e.added_by?.name||'—'}</div></div>
      <button class="btn btn-sm" style="background:var(--gr);color:white" onclick="approveEmp(${e.id},'${e.name}')">✅ اعتماد</button>
      <button class="btn btn-sm" style="background:var(--re);color:white" onclick="rejectEmp(${e.id},'${e.name}')">❌ رفض</button>
    </div>`).join('')
  :'<div style="text-align:center;padding:30px;color:var(--mu)"><div style="font-size:32px;opacity:.3;margin-bottom:8px">✅</div>لا يوجد موظفون بانتظار الاعتماد</div>';
}
async function approveEmp(id,name){const r=await api('PUT',`/employees/${id}/approve`);if(r.success){toast(r.message,'success');loadPending();}else toast(r.message,'error');}
async function rejectEmp(id,name){const reason=prompt(`سبب رفض: ${name}`);if(reason===null)return;const r=await api('PUT',`/employees/${id}/reject`,{reason});if(r.success){toast(r.message,'success');loadPending();}else toast(r.message,'error');}
loadPending();
</script>
@endpush
