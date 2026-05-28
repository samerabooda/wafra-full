@extends('layouts.app')
@section('title','تعديل حساب')
@section('page-title','تعديل حساب موجود')
@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'edit'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

<div class="panel" style="max-width:860px;margin-bottom:14px">
  <div class="panel-header">
    <div class="panel-title" id="edt-title-search">🔍 البحث عن الحساب</div>
    <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-sm" id="edt-btn-back">← رجوع</a>
  </div>
  <div class="panel-body">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" id="edt-lbl-search">رقم الحساب أو اسم البروكر</label>
        <input type="text" id="ec-search" class="form-control" placeholder="ابحث هنا..." oninput="ecSearch(this.value)">
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-pick">اختر الحساب من النتائج</label>
        <select id="ec-sel" class="form-control" onchange="ecLoad(this.value)">
          <option value="" id="edt-opt-pick">— اختر حساباً —</option>
        </select>
      </div>
    </div>
  </div>
</div>

<div id="ec-panel" class="panel" style="max-width:860px;display:none">
  <div class="panel-header">
    <div>
      <div class="panel-title" id="ec-title">—</div>
      <div style="font-size:11px;color:var(--mu);margin-top:3px" id="ec-sub"></div>
    </div>
    <span id="ec-status-badge"></span>
  </div>
  <div class="panel-body">
    <div id="ec-alert-err" class="alert alert-error"></div>
    <div id="ec-alert-ok"  class="alert alert-success"></div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" id="edt-lbl-broker">البروكر</label>
        <select id="ec-broker" class="form-control"></select>
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-bcomm">عمولة البروكر</label>
        <input type="number" id="ec-bc" class="form-control" step="0.5" min="0">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" id="edt-lbl-mktr">المسوّق الداخلي</label>
        <select id="ec-marketer" class="form-control"></select>
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-mcomm">عمولة المسوّق الداخلي</label>
        <input type="number" id="ec-mc" class="form-control" step="0.5" min="0">
      </div>
    </div>
    <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:12px">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ext1">🌐 مسوّق خارجي 1</label>
        <select id="ec-ext1" class="form-control"></select>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ext1c">عمولة خارجي 1</label>
        <input type="number" id="ec-ec1" class="form-control" step="0.5" min="0">
      </div>
    </div>
    <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:12px;margin-bottom:12px">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ext2">🌐 مسوّق خارجي 2</label>
        <select id="ec-ext2" class="form-control"></select>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ext2c">عمولة خارجي 2</label>
        <input type="number" id="ec-ec2" class="form-control" step="0.5" min="0">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" id="edt-lbl-dep">إيداع فتح الحساب</label>
        <input type="number" id="ec-dep" class="form-control" min="0">
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-mon">الإيداع الشهري المتوقع</label>
        <input type="number" id="ec-mon" class="form-control" min="0">
      </div>
    </div>
    <div class="form-group">
      <label class="form-label" id="edt-lbl-reason">⚠️ سبب التعديل (مطلوب)</label>
      <select id="ec-reason" class="form-control">
        <option value="" id="edt-opt-reason">— اختر السبب —</option>
        <option id="edt-r1">تصحيح بيانات</option>
        <option id="edt-r2">تعديل عمولة</option>
        <option id="edt-r3">تغيير بروكر</option>
        <option id="edt-r4">تغيير مسوّق</option>
        <option id="edt-r5">تحديث إيداع</option>
        <option id="edt-r6">تصحيح خطأ</option>
        <option id="edt-r7">قرار إداري</option>
        <option id="edt-r8">أخرى</option>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label" id="edt-lbl-notes">ملاحظات التعديل</label>
      <textarea id="ec-notes" class="form-control" style="min-height:60px"></textarea>
    </div>
    <div id="ec-history" style="display:none">
      <div style="font-size:11px;font-weight:700;color:var(--or);margin-bottom:8px;text-transform:uppercase;letter-spacing:.4px" id="edt-hist-label">📋 سجل التعديلات السابقة</div>
      <div id="ec-hist-list"></div>
    </div>
  </div>
  <div class="panel-body" style="border-top:1px solid var(--brd1);display:flex;gap:10px">
    <button class="btn btn-warning btn-xl" onclick="saveEdit()" id="edt-btn-save">✏️ حفظ التعديلات</button>
    <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-xl" id="edt-btn-cancel">إلغاء</a>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}

@endsection
@push('scripts')
<script>
const EDT={
  ar:{
    tbTitle:'تعديل حساب موجود', titleSearch:'🔍 البحث عن الحساب', btnBack:'← رجوع',
    lblSearch:'رقم الحساب أو اسم البروكر', phSearch:'ابحث هنا...',
    lblPick:'اختر الحساب من النتائج', optPick:'— اختر حساباً —',
    lblBroker:'البروكر', lblBComm:'عمولة البروكر',
    lblMktr:'المسوّق الداخلي', lblMComm:'عمولة المسوّق الداخلي',
    lblExt1:'🌐 مسوّق خارجي 1', lblExt1C:'عمولة خارجي 1',
    lblExt2:'🌐 مسوّق خارجي 2', lblExt2C:'عمولة خارجي 2',
    lblDep:'إيداع فتح الحساب', lblMon:'الإيداع الشهري المتوقع',
    lblReason:'⚠️ سبب التعديل (مطلوب)', optReason:'— اختر السبب —',
    r1:'تصحيح بيانات', r2:'تعديل عمولة', r3:'تغيير بروكر', r4:'تغيير مسوّق',
    r5:'تحديث إيداع', r6:'تصحيح خطأ', r7:'قرار إداري', r8:'أخرى',
    lblNotes:'ملاحظات التعديل', histLabel:'📋 سجل التعديلات السابقة',
    btnSave:'✏️ حفظ التعديلات', btnCancel:'إلغاء', optNone:'— لا يوجد —',
    stMod:'✏️ معدّل', stNormal:'✅ عادي',
    errNoCard:'اختر حساباً أولاً', errNoReason:'يرجى تحديد سبب التعديل',
    successHint:'سيظهر بالأصفر في التقارير 🟡', histBy:'بواسطة',
  },
  en:{
    tbTitle:'Edit Existing Account', titleSearch:'🔍 Search Account', btnBack:'← Back',
    lblSearch:'Account number or broker name', phSearch:'Search here...',
    lblPick:'Select account from results', optPick:'— Select an account —',
    lblBroker:'Broker', lblBComm:'Broker Commission',
    lblMktr:'Internal Marketer', lblMComm:'Internal Marketer Commission',
    lblExt1:'🌐 External Marketer 1', lblExt1C:'External 1 Commission',
    lblExt2:'🌐 External Marketer 2', lblExt2C:'External 2 Commission',
    lblDep:'Opening Deposit', lblMon:'Expected Monthly Deposit',
    lblReason:'⚠️ Reason for Edit (required)', optReason:'— Select reason —',
    r1:'Data correction', r2:'Commission adjustment', r3:'Broker change', r4:'Marketer change',
    r5:'Deposit update', r6:'Error correction', r7:'Administrative decision', r8:'Other',
    lblNotes:'Edit Notes', histLabel:'📋 Previous Modifications',
    btnSave:'✏️ Save Changes', btnCancel:'Cancel', optNone:'— None —',
    stMod:'✏️ Modified', stNormal:'✅ Active',
    errNoCard:'Please select an account first', errNoReason:'Please specify a reason for the edit',
    successHint:'Will appear highlighted in reports 🟡', histBy:'by',
  }
};
function edtL()   { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function edt(key) { const l=edtL(); return EDT[l]?.[key]??EDT.ar[key]??key; }

function edtApplyLang(){
  const map={
    'edt-title-search':'titleSearch','edt-btn-back':'btnBack',
    'edt-lbl-search':'lblSearch','edt-lbl-pick':'lblPick',
    'edt-lbl-broker':'lblBroker','edt-lbl-bcomm':'lblBComm',
    'edt-lbl-mktr':'lblMktr','edt-lbl-mcomm':'lblMComm',
    'edt-lbl-ext1':'lblExt1','edt-lbl-ext1c':'lblExt1C',
    'edt-lbl-ext2':'lblExt2','edt-lbl-ext2c':'lblExt2C',
    'edt-lbl-dep':'lblDep','edt-lbl-mon':'lblMon',
    'edt-lbl-reason':'lblReason','edt-lbl-notes':'lblNotes',
    'edt-hist-label':'histLabel','edt-btn-save':'btnSave','edt-btn-cancel':'btnCancel',
  };
  Object.entries(map).forEach(([id,key])=>{const el=document.getElementById(id);if(el)el.textContent=edt(key);});
  const optMap={
    'edt-opt-pick':'optPick','edt-opt-reason':'optReason',
    'edt-r1':'r1','edt-r2':'r2','edt-r3':'r3','edt-r4':'r4',
    'edt-r5':'r5','edt-r6':'r6','edt-r7':'r7','edt-r8':'r8',
  };
  Object.entries(optMap).forEach(([id,key])=>{const el=document.getElementById(id);if(el)el.textContent=edt(key);});
  const s=document.getElementById('ec-search');if(s)s.placeholder=edt('phSearch');
  const tb=document.querySelector('.tb-title');if(tb)tb.textContent=edt('tbTitle');
}
const _edtOrig=window.applyLang;
window.applyLang=function(lang){if(_edtOrig)_edtOrig(lang);edtApplyLang();};

let ecCardId=null;
async function initSelects(){
  const emps=await api('GET','/employees?status=approved');
  if(!emps.success)return;
  ['ec-broker','ec-marketer','ec-ext1','ec-ext2'].forEach(id=>{
    const s=document.getElementById(id);
    s.innerHTML=id==='ec-broker'?'':`<option value="">${edt('optNone')}</option>`;
    emps.data.forEach(e=>{const o=document.createElement('option');o.value=e.id;o.textContent=e.name+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦');s.appendChild(o);});
  });
}
async function ecSearch(q){
  if(q.length<2)return;
  const r=await api('GET',`/cards?search=${encodeURIComponent(q)}&per_page=15`);
  if(!r.success)return;
  const sel=document.getElementById('ec-sel');
  sel.innerHTML=`<option value="">${edt('optPick')}</option>`;
  (r.data?.data||[]).forEach(c=>{
    const o=document.createElement('option');o.value=c.id;
    o.textContent=`#${c.account_number} — ${c.broker?.name||'—'} (${c.month})${c.status==='modified'?' ✏️ '+edt('stMod'):''}`;
    sel.appendChild(o);
  });
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
  document.getElementById('ec-status-badge').innerHTML=c.status==='modified'
    ?`<span class="badge badge-orange">${edt('stMod')}</span>`
    :`<span class="badge badge-blue">${edt('stNormal')}</span>`;
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
  if(mods.length){
    document.getElementById('ec-history').style.display='block';
    document.getElementById('ec-hist-list').innerHTML=mods.map(m=>`<div style="background:rgba(245,166,35,.06);border:1px solid rgba(245,166,35,.2);border-radius:8px;padding:8px 12px;margin-bottom:6px;font-size:11px"><b style="color:var(--or)">${esc(m.reason)}</b> — ${new Date(m.modified_at).toLocaleDateString()} — ${edt('histBy')}: ${esc(m.modified_by?.name||'—')}${m.notes?'<br><span style="color:var(--mu)">'+esc(m.notes)+'</span>':''}</div>`).join('');
  }
  window.scrollTo(0,document.getElementById('ec-panel').offsetTop-100);
}
async function saveEdit(){
  if(!ecCardId){toast(edt('errNoCard'),'error');return;}
  const reason=document.getElementById('ec-reason').value;
  if(!reason){toast(edt('errNoReason'),'error');return;}
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
    document.getElementById('ec-alert-ok').textContent='✅ '+r.message+' — '+edt('successHint');
    document.getElementById('ec-alert-ok').classList.add('show');
    document.getElementById('ec-alert-err').classList.remove('show');
    ecLoad(ecCardId);window.scrollTo(0,0);
  }else{
    document.getElementById('ec-alert-err').textContent='❌ '+r.message;
    document.getElementById('ec-alert-err').classList.add('show');
  }
}
edtApplyLang();
initSelects();
</script>
@endpush
