@extends('layouts.app')
@section('title','تعديل حساب')
@section('page-title','تعديل حساب موجود')
@section('content')
<div class="panel" style="max-width:860px;margin-bottom:14px">
  <div class="panel-header"><div class="panel-title">🔍 البحث عن الحساب</div><a href="{{ route('cards.index') }}" class="btn btn-ghost btn-sm">← رجوع</a></div>
  <div class="panel-body">
    <div class="form-row">
      <div class="form-group"><label class="form-label">رقم الحساب أو اسم البروكر</label><input type="text" id="ec-search" class="form-control" placeholder="ابحث هنا..." oninput="ecSearch(this.value)"></div>
      <div class="form-group"><label class="form-label">اختر الحساب من النتائج</label><select id="ec-sel" class="form-control" onchange="ecLoad(this.value)"><option value="">— اختر حساباً —</option></select></div>
    </div>
  </div>
</div>
<div id="ec-panel" class="panel" style="max-width:860px;display:none">
  <div class="panel-header">
    <div><div class="panel-title" id="ec-title">—</div><div style="font-size:11px;color:var(--mu);margin-top:3px" id="ec-sub"></div></div>
    <span id="ec-status-badge"></span>
  </div>
  <div class="panel-body">
    <div id="ec-alert-err" class="alert alert-error"></div>
    <div id="ec-alert-ok" class="alert alert-success"></div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">البروكر</label><select id="ec-broker" class="form-control"></select></div>
      <div class="form-group"><label class="form-label">عمولة البروكر ($)</label><input type="number" id="ec-bc" class="form-control" step="0.5" min="0"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">المسوّق الداخلي</label><select id="ec-marketer" class="form-control"></select></div>
      <div class="form-group"><label class="form-label">عمولة المسوّق الداخلي ($)</label><input type="number" id="ec-mc" class="form-control" step="0.5" min="0"></div>
    </div>
    <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:12px">
      <div class="form-group" style="margin-bottom:0"><label class="form-label">🌐 مسوّق خارجي 1</label><select id="ec-ext1" class="form-control"></select></div>
      <div class="form-group" style="margin-bottom:0"><label class="form-label">عمولة خارجي 1 ($)</label><input type="number" id="ec-ec1" class="form-control" step="0.5" min="0"></div>
    </div>
    <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:12px;margin-bottom:12px">
      <div class="form-group" style="margin-bottom:0"><label class="form-label">🌐 مسوّق خارجي 2</label><select id="ec-ext2" class="form-control"></select></div>
      <div class="form-group" style="margin-bottom:0"><label class="form-label">عمولة خارجي 2 ($)</label><input type="number" id="ec-ec2" class="form-control" step="0.5" min="0"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">إيداع أولي ($)</label><input type="number" id="ec-dep" class="form-control" min="0"></div>
      <div class="form-group"><label class="form-label">إيداع شهري ($)</label><input type="number" id="ec-mon" class="form-control" min="0"></div>
    </div>
    <div class="form-group"><label class="form-label">⚠️ سبب التعديل (مطلوب)</label>
      <select id="ec-reason" class="form-control"><option value="">— اختر السبب —</option><option>تصحيح بيانات</option><option>تعديل عمولة</option><option>تغيير بروكر</option><option>تغيير مسوّق</option><option>تحديث إيداع</option><option>تصحيح خطأ</option><option>قرار إداري</option><option>أخرى</option></select>
    </div>
    <div class="form-group"><label class="form-label">ملاحظات التعديل</label><textarea id="ec-notes" class="form-control" style="min-height:60px"></textarea></div>
    <!-- Edit history -->
    <div id="ec-history" style="display:none">
      <div style="font-size:11px;font-weight:700;color:var(--or);margin-bottom:8px;text-transform:uppercase;letter-spacing:.4px">📋 سجل التعديلات السابقة</div>
      <div id="ec-hist-list"></div>
    </div>
  </div>
  <div class="panel-body" style="border-top:1px solid var(--brd1);display:flex;gap:10px">
    <button class="btn btn-warning btn-xl" onclick="saveEdit()">✏️ حفظ التعديلات</button>
    <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-xl">إلغاء</a>
  </div>
</div>
@endsection
@push('scripts')
<script>
let ecCardId=null;
async function initSelects(){
  const emps=await api('GET','/employees?status=approved');
  if(!emps.success)return;
  ['ec-broker','ec-marketer','ec-ext1','ec-ext2'].forEach(id=>{
    const s=document.getElementById(id);
    s.innerHTML=id==='ec-broker'?'':'<option value="">— لا يوجد —</option>';
    emps.data.forEach(e=>{const o=document.createElement('option');o.value=e.id;o.textContent=e.name+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦');s.appendChild(o);});
  });
}
async function ecSearch(q){
  if(q.length<2)return;
  const r=await api('GET',`/cards?search=${encodeURIComponent(q)}&per_page=15`);
  if(!r.success)return;
  const sel=document.getElementById('ec-sel');
  sel.innerHTML='<option value="">— اختر حساباً —</option>';
  (r.data?.data||[]).forEach(c=>{const o=document.createElement('option');o.value=c.id;o.textContent=`#${c.account_number} — ${c.broker?.name||'—'} (${c.month})${c.status==='modified'?' ✏️ معدّل':''}`;sel.appendChild(o);});
}
async function ecLoad(id){
  if(!id)return;
  ecCardId=id;
  const r=await api('GET',`/cards/${id}`);
  if(!r.success)return;
  const c=r.data;
  document.getElementById('ec-panel').style.display='block';
  document.getElementById('ec-title').textContent='#'+c.account_number+' — '+c.month;
  document.getElementById('ec-sub').textContent=(c.broker?.name||'—')+' · '+c.account_kind;
  document.getElementById('ec-status-badge').innerHTML=c.status==='modified'?'<span class="badge badge-orange">✏️ معدّل</span>':'<span class="badge badge-blue">✅ عادي</span>';
  document.getElementById('ec-broker').value=c.broker_id||'';
  document.getElementById('ec-bc').value=c.broker_commission||0;
  document.getElementById('ec-marketer').value=c.marketer_id||'';
  document.getElementById('ec-mc').value=c.marketer_commission||0;
  document.getElementById('ec-ext1').value=c.ext_marketer1_id||'';
  document.getElementById('ec-ec1').value=c.ext_commission1||0;
  document.getElementById('ec-ext2').value=c.ext_marketer2_id||'';
  document.getElementById('ec-ec2').value=c.ext_commission2||0;
  document.getElementById('ec-dep').value=c.initial_deposit||0;
  document.getElementById('ec-mon').value=c.monthly_deposit||0;
  document.getElementById('ec-reason').value='';
  document.getElementById('ec-notes').value='';
  const mods=c.modifications||[];
  if(mods.length){document.getElementById('ec-history').style.display='block';document.getElementById('ec-hist-list').innerHTML=mods.map(m=>`<div style="background:rgba(245,166,35,.06);border:1px solid rgba(245,166,35,.2);border-radius:8px;padding:8px 12px;margin-bottom:6px;font-size:11px"><b style="color:var(--or)">${m.reason}</b> — ${new Date(m.modified_at).toLocaleDateString('ar')} — بواسطة: ${m.modified_by?.name||'—'}${m.notes?'<br><span style="color:var(--mu)">'+m.notes+'</span>':''}</div>`).join('');}
  window.scrollTo(0,document.getElementById('ec-panel').offsetTop-100);
}
async function saveEdit(){
  if(!ecCardId){toast('اختر حساباً أولاً','error');return;}
  const reason=document.getElementById('ec-reason').value;
  if(!reason){toast('يرجى تحديد سبب التعديل','error');return;}
  const r=await api('PUT',`/cards/${ecCardId}`,{
    broker_id:parseInt(document.getElementById('ec-broker').value)||null,
    broker_commission:parseFloat(document.getElementById('ec-bc').value)||0,
    marketer_id:parseInt(document.getElementById('ec-marketer').value)||null,
    marketer_commission:parseFloat(document.getElementById('ec-mc').value)||0,
    ext_marketer1_id:parseInt(document.getElementById('ec-ext1').value)||null,
    ext_commission1:parseFloat(document.getElementById('ec-ec1').value)||0,
    ext_marketer2_id:parseInt(document.getElementById('ec-ext2').value)||null,
    ext_commission2:parseFloat(document.getElementById('ec-ec2').value)||0,
    initial_deposit:parseFloat(document.getElementById('ec-dep').value)||0,
    monthly_deposit:parseFloat(document.getElementById('ec-mon').value)||0,
    reason,notes:document.getElementById('ec-notes').value,
  });
  if(r.success){
    document.getElementById('ec-alert-ok').textContent='✅ '+r.message+' — سيظهر بالأصفر في التقارير 🟡';
    document.getElementById('ec-alert-ok').classList.add('show');
    document.getElementById('ec-alert-err').classList.remove('show');
    ecLoad(ecCardId);
    window.scrollTo(0,0);
  }else{document.getElementById('ec-alert-err').textContent='❌ '+r.message;document.getElementById('ec-alert-err').classList.add('show');}
}
initSelects();
</script>
@endpush
