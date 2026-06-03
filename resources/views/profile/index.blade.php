@extends('layouts.app')
@section('title','Profile')
@section('page-title','Profile')

@section('content')
@verbatim
<style>
.pf{max-width:640px;margin:0 auto;display:flex;flex-direction:column;gap:16px}
.pf-card{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;padding:22px;box-shadow:0 4px 18px rgba(0,0,0,.08)}
.pf-h{display:flex;align-items:center;gap:18px;margin-bottom:6px}
.pf-av{width:88px;height:88px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:900;color:#fff;background:linear-gradient(135deg,var(--pri2),var(--pri3));overflow:hidden;cursor:pointer;position:relative;border:3px solid rgba(255,255,255,.12)}
.pf-av img{width:100%;height:100%;object-fit:cover}
.pf-av-ov{position:absolute;inset:0;background:rgba(0,0,0,.45);display:flex;align-items:center;justify-content:center;font-size:20px;opacity:0;transition:opacity .15s}
.pf-av:hover .pf-av-ov{opacity:1}
.pf-name{font-size:19px;font-weight:900}
.pf-role{font-size:12px;color:var(--mu);margin-top:3px}
.pf-photo-btns{display:flex;gap:8px;margin-top:8px}
.pf-sec-t{font-size:13px;font-weight:800;color:var(--pri2);margin-bottom:14px;display:flex;align-items:center;gap:7px;padding-bottom:8px;border-bottom:1px solid var(--brd1)}
.pf-fg{margin-bottom:14px}
.pf-fl{font-size:11px;font-weight:700;color:var(--mu);margin-bottom:5px;display:block}
.pf-msg{padding:10px 14px;border-radius:10px;font-size:13px;font-weight:600;margin-bottom:12px;display:none}
.pf-msg.ok{display:block;background:rgba(34,201,122,.12);color:#22c97a;border:1px solid rgba(34,201,122,.3)}
.pf-msg.err{display:block;background:rgba(224,80,80,.12);color:#e05050;border:1px solid rgba(224,80,80,.3)}
</style>
@endverbatim

<div class="pf">
  <div class="pf-card">
    <div class="pf-h">
      <div class="pf-av" id="pf-avatar" onclick="document.getElementById('pf-file').click()">
        <span id="pf-initial">?</span>
        <div class="pf-av-ov">📷</div>
      </div>
      <div style="flex:1">
        <div class="pf-name" id="pf-dname">—</div>
        <div class="pf-role" id="pf-drole"></div>
        <div class="pf-photo-btns">
          <button class="btn btn-ghost btn-sm" onclick="document.getElementById('pf-file').click()" id="pf-btn-upload">📷 <span id="pf-l-upload">تغيير الصورة</span></button>
          <button class="btn btn-ghost btn-sm" onclick="pfRemovePhoto()" id="pf-btn-remove" style="display:none;color:var(--re)">✕ <span id="pf-l-remove">حذف</span></button>
        </div>
        <input type="file" id="pf-file" accept="image/png,image/jpeg,image/gif,image/webp" style="display:none" onchange="pfPhotoPick(this)">
      </div>
    </div>
  </div>

  <div class="pf-card">
    <div class="pf-sec-t" id="pf-t-info">👤 <span>المعلومات الشخصية</span></div>
    <div class="pf-msg" id="pf-info-msg"></div>
    <div class="pf-fg"><label class="pf-fl" id="pf-l-name">الاسم الكامل</label><input type="text" id="pf-name" class="form-control"></div>
    <div class="pf-fg"><label class="pf-fl" id="pf-l-email">البريد الإلكتروني</label><input type="text" id="pf-email" class="form-control" readonly style="opacity:.6"></div>
    <div class="pf-fg"><label class="pf-fl" id="pf-l-phone">رقم التليفون</label><input type="tel" id="pf-phone" class="form-control" dir="ltr"></div>
    <button class="btn btn-primary" onclick="pfSaveInfo()" id="pf-save"><span id="pf-l-save">حفظ التغييرات</span></button>
  </div>

  <div class="pf-card">
    <div class="pf-sec-t" id="pf-t-pwd">🔒 <span>تغيير كلمة المرور</span></div>
    <div class="pf-msg" id="pf-pwd-msg"></div>
    <div class="pf-fg"><label class="pf-fl" id="pf-l-cur">كلمة المرور الحالية</label><input type="password" id="pf-cur" class="form-control" autocomplete="current-password"></div>
    <div class="pf-fg"><label class="pf-fl" id="pf-l-new">كلمة المرور الجديدة</label><input type="password" id="pf-new" class="form-control" autocomplete="new-password"></div>
    <div class="pf-fg"><label class="pf-fl" id="pf-l-conf">تأكيد كلمة المرور</label><input type="password" id="pf-conf" class="form-control" autocomplete="new-password"></div>
    <button class="btn btn-primary" onclick="pfChangePwd()" id="pf-pwd-btn"><span id="pf-l-pwdbtn">تغيير كلمة المرور</span></button>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  'use strict';
  var AR={tInfo:'👤 المعلومات الشخصية',tPwd:'🔒 تغيير كلمة المرور',lName:'الاسم الكامل',lEmail:'البريد الإلكتروني',lPhone:'رقم التليفون',save:'حفظ التغييرات',
    lCur:'كلمة المرور الحالية',lNew:'كلمة المرور الجديدة',lConf:'تأكيد كلمة المرور',pwdBtn:'تغيير كلمة المرور',upload:'تغيير الصورة',remove:'حذف',
    savedI:'✅ تم حفظ المعلومات',savedP:'✅ تم تغيير الصورة',removedP:'تم حذف الصورة',savedPw:'✅ تم تغيير كلمة المرور',
    errPw:'كلمتا المرور غير متطابقتين',errMin:'كلمة المرور 8 أحرف على الأقل',big:'الصورة كبيرة (أقصى 2 ميجا)',tb:'الملف الشخصي'};
  var EN={tInfo:'👤 Personal Information',tPwd:'🔒 Change Password',lName:'Full Name',lEmail:'Email',lPhone:'Phone',save:'Save Changes',
    lCur:'Current Password',lNew:'New Password',lConf:'Confirm Password',pwdBtn:'Change Password',upload:'Change Photo',remove:'Remove',
    savedI:'✅ Info saved',savedP:'✅ Photo updated',removedP:'Photo removed',savedPw:'✅ Password changed',
    errPw:'Passwords do not match',errMin:'Password must be at least 8 characters',big:'Image too large (max 2MB)',tb:'Profile'};
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (L()==='en'?EN:AR)[k]||k;}
  function set(id,v){var e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}
  function val(id){var e=document.getElementById(id);return e?e.value:'';}
  function msg(id,type,txt){var e=document.getElementById(id);if(!e)return;e.className='pf-msg '+type;e.textContent=txt;if(type==='ok')setTimeout(function(){e.className='pf-msg';},3000);}
  var roleAr={finance_admin:'💼 المدير المالي',branch_manager:'🏢 مدير الفرع',viewer:'👁 مشاهد'},roleEn={finance_admin:'💼 Finance Admin',branch_manager:'🏢 Branch Manager',viewer:'👁 Viewer'};

  function applyLang(){
    set('pf-t-info',t('tInfo').replace(/^.. /,''));document.querySelector('#pf-t-info span').textContent=t('tInfo').replace(/^\S+ /,'');
    set('pf-l-name',t('lName'));set('pf-l-email',t('lEmail'));set('pf-l-phone',t('lPhone'));set('pf-l-save',t('save'));
    set('pf-l-cur',t('lCur'));set('pf-l-new',t('lNew'));set('pf-l-conf',t('lConf'));set('pf-l-pwdbtn',t('pwdBtn'));
    set('pf-l-upload',t('upload'));set('pf-l-remove',t('remove'));
    document.querySelector('#pf-t-pwd span').textContent=t('tPwd').replace(/^\S+ /,'');
    var tb=document.querySelector('.tb-title');if(tb)tb.textContent=t('tb');
  }
  var _o=window.applyLang;window.applyLang=function(l){if(_o)_o(l);try{applyLang();}catch(e){}};

  function showAvatar(name,photo){
    var av=document.getElementById('pf-avatar'),ini=document.getElementById('pf-initial'),rm=document.getElementById('pf-btn-remove');
    if(photo){av.innerHTML='<img src="'+photo+'"><div class="pf-av-ov">📷</div>';rm.style.display='';}
    else{av.innerHTML='<span id="pf-initial">'+((name||'?').charAt(0).toUpperCase())+'</span><div class="pf-av-ov">📷</div>';rm.style.display='none';}
  }

  function load(){
    api('GET','/profile').then(function(r){
      if(!r||!r.success)return;
      var d=r.data;
      set('pf-dname',d.name);set('pf-drole',(L()==='en'?roleEn:roleAr)[d.role]||d.role);
      document.getElementById('pf-name').value=d.name||'';
      document.getElementById('pf-email').value=d.email||'';
      document.getElementById('pf-phone').value=d.phone||'';
      showAvatar(d.name,d.photo);
    });
  }

  window.pfSaveInfo=function(){
    api('PUT','/profile',{name:val('pf-name'),phone:val('pf-phone')}).then(function(r){
      if(r&&r.success){msg('pf-info-msg','ok',t('savedI'));set('pf-dname',val('pf-name'));
        var nm=document.getElementById('sb-username');if(nm)nm.textContent=val('pf-name');if(window.toast)toast(t('savedI'),'success');}
      else msg('pf-info-msg','err',(r&&r.message)||(r&&r.errors?Object.values(r.errors).flat().join(' '):'Error'));
    });
  };

  window.pfPhotoPick=function(inp){
    if(!inp.files||!inp.files[0])return;
    var f=inp.files[0];
    if(f.size>2*1024*1024){msg('pf-info-msg','err',t('big'));inp.value='';return;}
    var rd=new FileReader();
    rd.onload=function(e){
      var data=e.target.result;
      api('POST','/profile/photo',{photo:data}).then(function(r){
        if(r&&r.success){showAvatar(val('pf-name'),data);msg('pf-info-msg','ok',t('savedP'));
          var av=document.getElementById('sb-avatar');if(av)av.innerHTML='<img src="'+data+'" style="width:100%;height:100%;object-fit:cover;border-radius:50%">';
          if(window.CURRENT_USER)CURRENT_USER.photo=data;if(window.toast)toast(t('savedP'),'success');}
        else msg('pf-info-msg','err',(r&&r.message)||'Error');
      });
    };
    rd.readAsDataURL(f);
  };

  window.pfRemovePhoto=function(){
    api('DELETE','/profile/photo').then(function(r){
      if(r&&r.success){showAvatar(val('pf-name'),null);msg('pf-info-msg','ok',t('removedP'));
        var av=document.getElementById('sb-avatar');if(av)av.textContent=(val('pf-name')||'?').charAt(0).toUpperCase();
        if(window.CURRENT_USER)CURRENT_USER.photo=null;}
    });
  };

  window.pfChangePwd=function(){
    var cur=val('pf-cur'),nw=val('pf-new'),cf=val('pf-conf');
    if(nw.length<8){msg('pf-pwd-msg','err',t('errMin'));return;}
    if(nw!==cf){msg('pf-pwd-msg','err',t('errPw'));return;}
    api('PUT','/profile/password',{current_password:cur,password:nw,password_confirmation:cf}).then(function(r){
      if(r&&r.success){msg('pf-pwd-msg','ok',t('savedPw'));['pf-cur','pf-new','pf-conf'].forEach(function(id){document.getElementById(id).value='';});if(window.toast)toast(t('savedPw'),'success');}
      else msg('pf-pwd-msg','err',(r&&r.message)||(r&&r.errors?Object.values(r.errors).flat().join(' '):'Error'));
    });
  };

  applyLang();load();
})();
</script>
@endpush
