@extends('layouts.app')
@section('title','Mailbox')
@section('page-title','صندوق البريد')

@section('content')
@verbatim
<style>
.ib{max-width:1150px;margin:0 auto}
.ib-bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:13px;padding:11px 14px;margin-bottom:14px}
.ib-f{padding:6px 14px;border-radius:20px;border:1px solid var(--brd2);background:none;cursor:pointer;font-size:12px;font-weight:700;color:var(--mu);font-family:'Tajawal',sans-serif;transition:all .15s}
.ib-f:hover,.ib-f.on{background:rgba(26,173,186,.14);border-color:var(--pri);color:var(--pri2)}
.ib-panel{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;overflow:hidden}
.ib-scroll{overflow:auto;max-height:calc(100vh - 250px)}
.ib-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.ib-tbl thead th{position:sticky;top:0;z-index:2;background:var(--bg3);padding:10px 11px;font-size:10px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;white-space:nowrap;border-bottom:2px solid var(--pri)}
.ib-tbl tbody td{padding:10px 11px;border-bottom:1px solid var(--brd1);white-space:nowrap;vertical-align:top}
.ib-tbl tbody tr:hover td{background:rgba(26,173,186,.06)}
.ib-tbl tbody tr.unread td{background:rgba(245,166,35,.06)}
.ib-dir{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:12px;font-size:10px;font-weight:800}
.ib-in{background:rgba(34,201,122,.14);color:#22c97a}
.ib-out{background:rgba(58,157,181,.14);color:#3a9db5}
.ib-ac{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--pri2)}
.ib-st{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px}
.ib-st-unread{background:rgba(245,166,35,.15);color:var(--or)}
.ib-st-read{background:rgba(34,201,122,.12);color:#22c97a}
.ib-mu{color:var(--mu)}
.ib-empty{text-align:center;padding:50px 14px;color:var(--mu)}
</style>
@endverbatim

<div class="ib">
  <div class="ib-bar">
    <span style="font-size:20px">📬</span>
    <strong id="ib-title" style="font-size:14px">صندوق بريد مركز الاتصال</strong>
    <div style="flex:1"></div>
    <button class="ib-f on" id="ib-f-all"  onclick="ibFilter('')">الكل</button>
    <button class="ib-f"    id="ib-f-in"   onclick="ibFilter('in')">📥 وارد</button>
    <button class="ib-f"    id="ib-f-out"  onclick="ibFilter('out')">📤 صادر</button>
    <button class="btn btn-ghost btn-sm" onclick="ibLoad()">🔄 <span id="ib-refresh">تحديث</span></button>
  </div>

  <div class="ib-panel">
    <div class="ib-scroll">
      <table class="ib-tbl"><thead><tr id="ib-head"></tr></thead><tbody id="ib-body"></tbody></table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  'use strict';
  var AR={title:'صندوق بريد مركز الاتصال',titleFA:'صندوق البريد — كل التنبيهات',all:'الكل',inb:'📥 وارد',out:'📤 صادر',refresh:'تحديث',
    thDir:'النوع',thAc:'الحساب',thMonth:'الشهر',thFrom:'من (CC)',thTo:'إلى',thCreated:'التاريخ',thDeliv:'وصل',thRead:'قُرئ',thStatus:'الحالة',
    dirIn:'📥 وارد',dirOut:'📤 صادر',unread:'غير مقروء',read:'مقروء',none:'لا توجد رسائل',tb:'صندوق البريد',loading:'جارٍ التحميل…'};
  var EN={title:'Call-Center Mailbox',titleFA:'Mailbox — All Notifications',all:'All',inb:'📥 Incoming',out:'📤 Outgoing',refresh:'Refresh',
    thDir:'Type',thAc:'Account',thMonth:'Month',thFrom:'From (CC)',thTo:'To',thCreated:'Date',thDeliv:'Delivered',thRead:'Read',thStatus:'Status',
    dirIn:'📥 In',dirOut:'📤 Out',unread:'Unread',read:'Read',none:'No messages',tb:'Mailbox',loading:'Loading…'};
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (L()==='en'?EN:AR)[k]||k;}
  function E(s){return window.esc?esc(s):String(s==null?'':s);}
  function dt(s){ if(!s)return '<span class="ib-mu">—</span>'; var d=new Date(s.replace(' ','T'));
    return d.toLocaleDateString(L()==='en'?'en-GB':'ar-EG',{month:'short',day:'numeric'})+' '+d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}); }

  var _data=null,_isFA=false,_filter='';
  window.ibFilter=function(f){_filter=f;['all','in','out'].forEach(function(x){var b=document.getElementById('ib-f-'+x);if(b)b.classList.toggle('on',(x==='all'&&f==='')||x===f);});render();};
  window.ibLoad=function(){
    document.getElementById('ib-body').innerHTML='<tr><td colspan="9" class="ib-empty">'+t('loading')+'</td></tr>';
    api('GET','/notifications/inbox').then(function(r){
      if(r&&r.success){_data=r.data||[];_isFA=!!r.is_fa;render();}
      else document.getElementById('ib-body').innerHTML='<tr><td colspan="9" class="ib-empty">—</td></tr>';
    });
  };
  function head(){
    document.getElementById('ib-head').innerHTML=
      '<th>'+t('thDir')+'</th><th>'+t('thAc')+'</th><th>'+t('thMonth')+'</th><th>'+t('thFrom')+'</th><th>'+t('thTo')+'</th><th>'+t('thCreated')+'</th><th>'+t('thDeliv')+'</th><th>'+t('thRead')+'</th><th>'+t('thStatus')+'</th>';
  }
  function render(){
    head();
    var tb=document.querySelector('.tb-title');if(tb)tb.textContent=t('tb');
    document.getElementById('ib-title').textContent=_isFA?t('titleFA'):t('title');
    if(!_data){return;}
    var rows=_data.filter(function(n){ if(!_filter)return true; var d=(n.direction==='all')?(n.to_user?'in':'out'):n.direction; return d===_filter; });
    if(!rows.length){document.getElementById('ib-body').innerHTML='<tr><td colspan="9" class="ib-empty">🔕 '+t('none')+'</td></tr>';return;}
    document.getElementById('ib-body').innerHTML=rows.map(function(n){
      var isOut=(n.direction==='out');
      var dirChip=isOut?'<span class="ib-dir ib-out">'+t('dirOut')+'</span>':'<span class="ib-dir ib-in">'+t('dirIn')+'</span>';
      var unread=!n.read_at;
      return '<tr class="'+(unread?'unread':'')+'">'+
        '<td>'+dirChip+'</td>'+
        '<td><span class="ib-ac">'+E(n.account_number||'—')+'</span></td>'+
        '<td class="ib-mu">'+E(n.month||'—')+'</td>'+
        '<td>'+E(n.from_user||'—')+(n.from_branch?'<div class="ib-mu" style="font-size:10px">'+E(n.from_branch)+'</div>':'')+'</td>'+
        '<td>'+E(n.to_user||'—')+(n.to_branch?'<div class="ib-mu" style="font-size:10px">'+E(n.to_branch)+'</div>':'')+'</td>'+
        '<td class="ib-mu">'+dt(n.created_at)+'</td>'+
        '<td>'+dt(n.delivered_at)+'</td>'+
        '<td>'+(n.read_at?'<span style="color:#22c97a">'+dt(n.read_at)+'</span>':'<span class="ib-mu">—</span>')+'</td>'+
        '<td><span class="ib-st '+(unread?'ib-st-unread':'ib-st-read')+'">'+(unread?t('unread'):t('read'))+'</span></td>'+
        '</tr>';
    }).join('');
  }
  var _o=window.applyLang;window.applyLang=function(l){if(_o)_o(l);try{render();}catch(e){}};
  ibLoad();
})();
</script>
@endpush
