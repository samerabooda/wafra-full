@extends('layouts.app')
@section('title','User Guide')
@section('page-title','User Guide')

@section('content')
<div style="max-width:800px;margin:0 auto;padding:24px">
  <h1 id="gd-title" style="font-size:24px;font-weight:900;color:var(--pri2);margin-bottom:10px">📖 دليل تشغيل النظام</h1>
  <div style="background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:20px">
    <p id="gd-body" style="color:var(--tx);line-height:1.9">اختبار: إذا ظهرت هذه الصفحة فالخادم يعمل والمشكلة كانت في المحتوى.</p>
    <p id="gd-sub" style="color:var(--mu);line-height:1.9;font-size:13px">Test page — diagnostic only.</p>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  var AR={title:'📖 دليل تشغيل النظام', body:'اختبار: إذا ظهرت هذه الصفحة فالخادم يعمل والمشكلة كانت في المحتوى.', sub:'صفحة اختبار — تشخيصية فقط.'};
  var EN={title:'📖 System User Guide',  body:'Test: if this page shows, the server is up and the earlier issue was in the content.', sub:'Test page — diagnostic only.'};
  function L(){return (typeof curLang!=='undefined'?curLang:(localStorage.getItem('wg_lang')||'ar'));}
  function apply(){ var t=L()==='en'?EN:AR;
    var set=function(id,v){var e=document.getElementById(id); if(e)e.textContent=v;};
    set('gd-title',t.title); set('gd-body',t.body); set('gd-sub',t.sub); }
  var _o=window.applyLang; window.applyLang=function(l){ if(_o)_o(l); try{apply();}catch(e){} };
  apply();
})();
</script>
@endpush
