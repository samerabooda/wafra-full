@extends('layouts.app')
@section('title','المديرون')
@section('page-title','إدارة المديرين')
@section('topbar-actions')
<button class="tb-btn primary" onclick="openModal('modal-add-mgr')">👤 مدير جديد</button>
@endsection
@section('content')
<div class="panel" id="mgr-list"><div class="panel-header"><div class="panel-title">👤 قائمة المديرين</div></div><div class="table-scroll"><table class="data-table"><thead><tr><th>الاسم</th><th>البريد</th><th>الفرع</th><th>الدور</th><th>آخر دخول</th><th>الحالة</th><th>إجراءات</th></tr></thead><tbody id="mgr-tbody"><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr></tbody></table></div></div>
<div class="modal-overlay" id="modal-add-mgr"><div class="modal modal-wide"><div class="modal-header"><div class="modal-title">👤 إنشاء حساب مدير جديد</div><button class="modal-close" onclick="closeModal('modal-add-mgr')">✕</button></div><div class="modal-body"><div id="mgr-err" class="alert alert-error"></div><div id="mgr-ok" class="alert alert-success"></div><div class="form-row"><div class="form-group"><label class="form-label">الاسم الكامل *</label><input type="text" id="mg-name" class="form-control" placeholder="Manager Name"></div><div class="form-group"><label class="form-label">البريد الإلكتروني *</label><input type="email" id="mg-email" class="form-control" placeholder="manager@wafragulf.com"></div></div><div class="form-row"><div class="form-group"><label class="form-label">الفرع المسؤول عنه *</label><select id="mg-branch" class="form-control"></select></div><div class="form-group"><label class="form-label">كلمة مرور مؤقتة (اتركها فارغة للتوليد التلقائي)</label><input type="password" id="mg-pw" class="form-control" placeholder="—"></div></div><div class="form-section-title" style="margin-top:14px">🔐 الصلاحيات</div><div style="display:flex;gap:8px;margin-bottom:10px"><button class="btn btn-ghost btn-sm" onclick="selectAllPerms(true)">✅ تحديد الكل</button><button class="btn btn-ghost btn-sm" onclick="selectAllPerms(false)">☐ إلغاء الكل</button></div><div id="perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div></div><div class="modal-footer"><button class="btn btn-ghost" onclick="closeModal('modal-add-mgr')">إلغاء</button><button class="btn btn-primary" onclick="createManager()">📧 إنشاء وإرسال بيانات الدخول</button></div></div></div>
@endsection
@push('scripts')
<script>
const PERMS=[{id:'dashboard',ar:'لوحة المتابعة'},{id:'cards',ar:'كروت العمولات'},{id:'modified',ar:'الحسابات المعدّلة'},{id:'reports',ar:'التقارير'},{id:'create_card',ar:'إنشاء كرت'},{id:'edit_card',ar:'تعديل الحسابات'},{id:'employees',ar:'إدارة الموظفين'},{id:'import',ar:'استيراد بيانات'},{id:'export',ar:'تصدير البيانات'}];
async function init(){
  const r=await api('GET','/managers');if(!r.success)return;
  document.getElementById('mgr-tbody').innerHTML=r.data.map(m=>`<tr><td style="font-weight:700">${m.name}</td><td style="color:var(--mu)">${m.email}</td><td>${m.branch?.name_ar||'—'}</td><td><span class="badge badge-blue">${m.role}</span></td><td style="color:var(--mu);font-size:11px">${m.last_login||'لم يدخل بعد'}</td><td><span class="badge ${m.is_active?'badge-green':'badge-red'}">${m.is_active?'نشط':'معطّل'}</span></td><td><button class="btn btn-ghost btn-sm" onclick="resetPw(${m.id},'${m.name}')">🔑</button></td></tr>`).join('')||'<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا يوجد مديرون</td></tr>';
  const br=await api('GET','/branches');if(br.success){const s=document.getElementById('mg-branch');s.innerHTML='';br.data.forEach(b=>{const o=document.createElement('option');o.value=b.id;o.textContent=b.name_ar;s.appendChild(o);});}
  document.getElementById('perm-grid').innerHTML=PERMS.map(p=>`<div class="panel" style="padding:10px;display:flex;align-items:center;gap:8px;cursor:pointer;background:rgba(46,134,171,.1);border-color:rgba(46,134,171,.3)" id="pit-${p.id}" onclick="togPerm('${p.id}')"><div style="width:18px;height:18px;border-radius:5px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:11px" id="pchk-${p.id}">✓</div><div style="font-size:12px;font-weight:600">${p.ar}</div></div>`).join('');
}
function togPerm(id){const el=document.getElementById('pit-'+id);const chk=document.getElementById('pchk-'+id);const on=chk.textContent==='✓';chk.textContent=on?'':'✓';el.style.background=on?'var(--inp-bg)':'rgba(46,134,171,.1)';el.style.borderColor=on?'var(--brd1)':'rgba(46,134,171,.3)';}
function selectAllPerms(v){PERMS.forEach(p=>{const chk=document.getElementById('pchk-'+p.id);const el=document.getElementById('pit-'+p.id);chk.textContent=v?'✓':'';el.style.background=v?'rgba(46,134,171,.1)':'var(--inp-bg)';el.style.borderColor=v?'rgba(46,134,171,.3)':'var(--brd1)';});}
async function createManager(){
  const name=document.getElementById('mg-name').value.trim();
  const email=document.getElementById('mg-email').value.trim();
  const branch=document.getElementById('mg-branch').value;
  if(!name||!email){document.getElementById('mgr-err').textContent='يرجى ملء الاسم والإيميل';document.getElementById('mgr-err').classList.add('show');return;}
  const perms=PERMS.filter(p=>document.getElementById('pchk-'+p.id).textContent==='✓').map(p=>p.id);
  const r=await api('POST','/managers',{name,email,branch_id:parseInt(branch),password:document.getElementById('mg-pw').value||null,permissions:perms});
  if(r.success){document.getElementById('mgr-ok').innerHTML=`✅ تم الإنشاء!<br>📧 ${email}<br>🔑 <b style="font-family:'JetBrains Mono',monospace">${r.credentials.temp_password}</b>`;document.getElementById('mgr-ok').classList.add('show');init();}
  else{document.getElementById('mgr-err').textContent=r.message;document.getElementById('mgr-err').classList.add('show');}}
async function resetPw(id,name){if(!confirm('إعادة تعيين كلمة مرور: '+name+'?'))return;const r=await api('POST',`/managers/${id}/reset-password`);if(r.success)toast(`كلمة المرور الجديدة: ${r.new_password}`,'info');else toast(r.message,'error');}
init();
</script>
@endpush
