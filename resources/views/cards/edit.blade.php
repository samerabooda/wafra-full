@extends('layouts.app')
@section('title','Edit Account')
@section('page-title','Edit Account')
@section('content')
<div style="display:block;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
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
        <input type="search" id="ec-search" name="ec_nofill" autocomplete="off" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" class="form-control" placeholder="ابحث هنا..." oninput="ecSearch(this.value)">
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
        <select id="ec-broker" class="form-control" onchange="ecSyncComm('ec-broker','ec-bc',4)"></select>
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-bcomm">عمولة البروكر</label>
        <input type="number" id="ec-bc" class="form-control" step="any" min="0" inputmode="decimal">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" id="edt-lbl-mktr">المسوّق الداخلي 1</label>
        <select id="ec-marketer" class="form-control" onchange="ecSyncComm('ec-marketer','ec-mc')"></select>
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-mcomm">عمولة المسوّق الداخلي 1</label>
        <input type="number" id="ec-mc" class="form-control" step="any" min="0" inputmode="decimal">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label" id="edt-lbl-ext2">المسوّق الداخلي 2</label>
        <select id="ec-ext2" class="form-control" onchange="ecSyncComm('ec-ext2','ec-ec2')"></select>
      </div>
      <div class="form-group">
        <label class="form-label" id="edt-lbl-ext2c">عمولة المسوّق الداخلي 2</label>
        <input type="number" id="ec-ec2" class="form-control" step="any" min="0" inputmode="decimal">
      </div>
    </div>
    <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:12px;margin-bottom:12px">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ext1">🌐 مسوّق خارجي</label>
        <select id="ec-ext1" class="form-control" onchange="ecSyncComm('ec-ext1','ec-ec1')"></select>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ext1c">عمولة المسوّق الخارجي</label>
        <input type="number" id="ec-ec1" class="form-control" step="any" min="0" inputmode="decimal">
      </div>
    </div>
    <div class="form-row" style="background:rgba(245,168,40,.07);border:1px solid rgba(245,168,40,.25);border-radius:9px;padding:12px;margin-bottom:12px">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-ref">🔗 إحالة (Referral) — رقم حساب المُحيل</label>
        <input type="text" id="ec-ref" class="form-control" placeholder="رقم حساب المحيل أو اختر من القائمة" inputmode="numeric" autocomplete="off" list="ec-ref-datalist" oninput="onEditReferral()">
        <datalist id="ec-ref-datalist"></datalist>
        <div id="ec-ref-msg" style="font-size:11px;margin-top:5px;min-height:14px"></div>
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-refname">اسم المُحيل</label>
        <input type="text" id="ec-refname" class="form-control" placeholder="اكتب اسم المُحيل (يُحفظ ويظهر تلقائيًا لاحقًا)" autocomplete="off">
      </div>
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-refc">عمولة الإحالة</label>
        <input type="number" id="ec-refc" class="form-control" step="any" min="0" inputmode="decimal">
      </div>
    </div>
    <div class="form-row" style="background:rgba(58,157,181,.06);border:1px solid rgba(58,157,181,.22);border-radius:9px;padding:12px;margin-bottom:12px">
      <div class="form-group" style="margin-bottom:0">
        <label class="form-label" id="edt-lbl-kind">نوع الحساب</label>
        <select id="ec-kind" class="form-control" onchange="onKindChange()">
          <option value="new" id="edt-opt-knew">حساب أساسي (جديد)</option>
          <option value="sub" id="edt-opt-ksub">حساب فرعي — له حساب أساسي</option>
        </select>
      </div>
      <div class="form-group" id="ec-parent-wrap" style="margin-bottom:0;display:none">
        <label class="form-label" id="edt-lbl-parent">رقم الحساب الأساسي</label>
        <input type="text" id="ec-parent" class="form-control" placeholder="مثال: 700000" inputmode="numeric" autocomplete="off">
        <div id="ec-parent-msg" style="font-size:11px;margin-top:5px;min-height:14px"></div>
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
    lblMktr:'المسوّق الداخلي 1', lblMComm:'عمولة المسوّق الداخلي 1',
    lblExt2:'المسوّق الداخلي 2', lblExt2C:'عمولة المسوّق الداخلي 2',
    lblExt1:'🌐 مسوّق خارجي', lblExt1C:'عمولة المسوّق الخارجي',
    lblRef:'🔗 إحالة (Referral) — رقم حساب المُحيل', lblRefC:'عمولة الإحالة',
    lblKind:'نوع الحساب', kNew:'حساب أساسي (جديد)', kSub:'حساب فرعي — له حساب أساسي', lblParent:'رقم الحساب الأساسي',
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
    lblMktr:'Internal Marketer 1', lblMComm:'Internal Marketer 1 Commission',
    lblExt2:'Internal Marketer 2', lblExt2C:'Internal Marketer 2 Commission',
    lblExt1:'🌐 External Marketer', lblExt1C:'External Marketer Commission',
    lblRef:'🔗 Referral — Referrer Account Number', lblRefC:'Referral Commission',
    lblKind:'Account Type', kNew:'Primary (new) account', kSub:'Sub account — has a main account', lblParent:'Main Account Number',
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
    'edt-lbl-ref':'lblRef','edt-lbl-refc':'lblRefC',
    'edt-lbl-kind':'lblKind','edt-lbl-parent':'lblParent',
    'edt-lbl-dep':'lblDep','edt-lbl-mon':'lblMon',
    'edt-lbl-reason':'lblReason','edt-lbl-notes':'lblNotes',
    'edt-hist-label':'histLabel','edt-btn-save':'btnSave','edt-btn-cancel':'btnCancel',
  };
  Object.entries(map).forEach(([id,key])=>{const el=document.getElementById(id);if(el)el.textContent=edt(key);});
  const optMap={
    'edt-opt-pick':'optPick','edt-opt-reason':'optReason',
    'edt-opt-knew':'kNew','edt-opt-ksub':'kSub',
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
  const emps=await api('GET','/employees?status=approved&slim=1&all_branches=1&recent=1');
  if(!emps.success)return;
  const _br=e=>{const b=e.branch&&(e.branch.name_ar||e.branch.name_en); return b?(' — '+b):'';};
  ['ec-broker','ec-marketer','ec-ext1','ec-ext2'].forEach(id=>{
    const s=document.getElementById(id);
    // Empty value = "Self" (the account has no broker/marketer — direct/self-sourced).
    s.innerHTML=`<option value="">Self</option>`;
    emps.data.forEach(e=>{const o=document.createElement('option');o.value=e.id;o.textContent=e.name+_br(e)+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦');s.appendChild(o);});
  });
}
// "Self" (empty person) → its commission is forced to 0 and disabled.
function ecSyncComm(selId, inpId, def){
  var v=document.getElementById(selId).value, inp=document.getElementById(inpId); if(!inp) return;
  var real=!!v;
  inp.disabled=!real;
  if(!real) inp.value='0';
  else if(def && !parseFloat(inp.value)) inp.value=String(def);
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
async function ecLoad(id,pre){
  if(!id)return;
  ecCardId=id;
  const r=pre||await api('GET',`/cards/${id}`);
  if(!r.success)return;
  const c=r.data;
  document.getElementById('ec-panel').style.display='block';
  document.getElementById('ec-title').textContent='#'+c.account_number+' — '+c.month;
  document.getElementById('ec-sub').textContent=(c.broker?.name||'—')+' · '+c.account_kind;
  document.getElementById('ec-status-badge').innerHTML=c.status==='modified'
    ?`<span class="badge badge-orange">${edt('stMod')}</span>`
    :`<span class="badge badge-blue">${edt('stNormal')}</span>`;
  // The picker only lists employees from the last 2 years; make sure THIS card's current
  // broker/marketer are present even if older (so the value isn't lost).
  function _ecEnsureOpt(selId, emp){
    if(!emp || !emp.id) return; var s=document.getElementById(selId); if(!s) return;
    if(![].some.call(s.options,function(o){return o.value==String(emp.id);})){
      var o=document.createElement('option'); o.value=emp.id;
      var b=emp.branch&&(emp.branch.name_ar||emp.branch.name_en);
      o.textContent=emp.name+(b?' — '+b:''); s.appendChild(o);
    }
  }
  _ecEnsureOpt('ec-broker',c.broker); _ecEnsureOpt('ec-marketer',c.marketer);
  _ecEnsureOpt('ec-ext1',c.ext_marketer1); _ecEnsureOpt('ec-ext2',c.ext_marketer2);
  document.getElementById('ec-broker').value=c.broker_id||'';
  document.getElementById('ec-bc').value=c.broker_commission||0;
  document.getElementById('ec-marketer').value=c.marketer_id||'';
  document.getElementById('ec-mc').value=c.marketer_commission||0;
  document.getElementById('ec-ext1').value=c.ext_marketer1_id||'';
  document.getElementById('ec-ec1').value=c.ext_commission1||0;
  document.getElementById('ec-ext2').value=c.ext_marketer2_id||'';
  document.getElementById('ec-ec2').value=c.ext_commission2||0;
  // Self (empty person) → commission disabled & 0
  ecSyncComm('ec-broker','ec-bc'); ecSyncComm('ec-marketer','ec-mc'); ecSyncComm('ec-ext1','ec-ec1'); ecSyncComm('ec-ext2','ec-ec2');
  document.getElementById('ec-ref').value=c.referral_account||'';
  document.getElementById('ec-refname').value='';
  document.getElementById('ec-refc').value=c.referral_commission||0;
  onEditReferral();
  document.getElementById('ec-kind').value=(c.account_kind==='sub'?'sub':'new');
  document.getElementById('ec-parent').value=c.parent_account||'';
  onKindChange();
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
function onKindChange(){
  const isSub=document.getElementById('ec-kind').value==='sub';
  const w=document.getElementById('ec-parent-wrap');
  if(w)w.style.display=isSub?'':'none';
  if(!isSub){const p=document.getElementById('ec-parent');if(p)p.value='';const m=document.getElementById('ec-parent-msg');if(m)m.textContent='';}
}
let _ecRefT=null;
window._ecRefMap = window._ecRefMap || {};
(async function ecLoadReferrers(){
  try{
    const r=await api('GET','/cards/referrer-names');
    if(r&&r.success){
      var dl=document.getElementById('ec-ref-datalist');
      (r.data||[]).forEach(function(x){
        window._ecRefMap[String(x.account)]=x.name||'';
        if(dl){var o=document.createElement('option');o.value=x.account;o.textContent=x.name||'';dl.appendChild(o);}
      });
    }
  }catch(e){}
})();
function onEditReferral(){
  const acc=(document.getElementById('ec-ref').value||'').trim();
  const msg=document.getElementById('ec-ref-msg');
  const en=(typeof curLang!=='undefined'&&curLang==='en');
  if(!msg)return;
  if(!acc){msg.textContent='';return;}
  var nm=window._ecRefMap[acc];
  if(nm){msg.style.color='var(--gr)';msg.innerHTML='✓ '+(en?'Referrer':'المحيل')+': <b>'+nm+'</b>';_ecSetName(nm);return;}
  msg.style.color='var(--mu)';msg.textContent=en?'Checking…':'جارٍ التحقق…';
  clearTimeout(_ecRefT);
  _ecRefT=setTimeout(async()=>{
    const r=await api('GET','/cards/account-name?account='+encodeURIComponent(acc));
    if(r&&r.name){window._ecRefMap[acc]=r.name;msg.style.color='var(--gr)';msg.innerHTML='✓ '+(en?'Referrer':'المحيل')+': <b>'+r.name+'</b>';_ecSetName(r.name);}
    else{msg.style.color='var(--or)';msg.innerHTML=en?'✍️ New referrer — type the name on the right to save it':'✍️ مُحيل جديد — اكتب الاسم في الخانة المجاورة ليُحفظ تلقائيًا';}
  },400);
}
function _ecSetName(nm){var el=document.getElementById('ec-refname');if(el && !el.value.trim())el.value=nm||'';}
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
    referral_account:(document.getElementById('ec-ref').value||'').trim()||null,
    referral_name:(document.getElementById('ec-refname').value||'').trim()||null,
    referral_commission:(document.getElementById('ec-ref').value||'').trim()?(parseFloat(document.getElementById('ec-refc').value)||0):0,
    account_kind:document.getElementById('ec-kind').value==='sub'?'sub':'new',
    parent_account:document.getElementById('ec-kind').value==='sub'?((document.getElementById('ec-parent').value||'').replace(/[^0-9]/g,'')||null):null,
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
@if(isset($cardId) && $cardId)
// Opening a specific card: hide the search panel INSTANTLY (no 5-second flash),
// show the edit panel in a loading state, then load selects + card in the background.
(function(){
  var sp=document.getElementById('edt-title-search');
  if(sp){ var pn=sp.closest('.panel'); if(pn) pn.style.display='none'; }
  var ep=document.getElementById('ec-panel'); if(ep) ep.style.display='block';
  var ttl=document.getElementById('ec-title');
  if(ttl) ttl.textContent=((typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))==='en'?'Loading…':'جارٍ التحميل…');
  var _cid={{ (int)$cardId }};
  var _cardP=_cid?api('GET','/cards/'+_cid):null;   // fetch the card in PARALLEL with the employee dropdowns
  Promise.resolve(initSelects()).catch(function(){}).then(function(){
    if(_cardP){ _cardP.then(function(r){ ecLoad(_cid,r); }).catch(function(){ ecLoad(_cid); }); }
    else ecLoad(_cid);
  });
})();
@else
initSelects();
@endif
</script>
@endpush
