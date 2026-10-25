@extends('layouts.app')
@section('title','Notifications Tracker')
@section('page-title','متتبّع التنبيهات')

@section('content')
@verbatim
<style>
.nt{max-width:1200px;margin:0 auto}
.nt-bar{display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:13px;padding:13px 15px;margin-bottom:14px}
.nt-fg{display:flex;flex-direction:column;gap:4px}
.nt-fl{font-size:10px;font-weight:800;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}
.nt-kpis{display:grid;grid-template-columns:repeat(7,1fr);gap:10px;margin-bottom:16px}
.nt-kpi{background:linear-gradient(135deg,rgba(255,255,255,.06),rgba(255,255,255,.015));border:1px solid var(--card-brd);border-radius:14px;padding:14px;text-align:center}
.nt-kpi-v{font-size:1.5rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1}
.nt-kpi-l{font-size:9.5px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-top:6px;font-weight:700}
.nt-panel{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;padding:16px;margin-bottom:16px;overflow:hidden}
.nt-pt{font-size:13px;font-weight:800;color:var(--m2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px}
.nt-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.nt-tbl thead th{background:var(--bg3);padding:10px 11px;font-size:10px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;white-space:nowrap;border-bottom:2px solid var(--pri)}
.nt-tbl tbody td{padding:9px 11px;border-bottom:1px solid var(--brd1);white-space:nowrap}
.nt-tbl tbody tr:hover td{background:rgba(26,173,186,.06)}
.nt-bar2{height:8px;border-radius:5px;background:rgba(255,255,255,.06);overflow:hidden;min-width:70px}
.nt-bar2 i{display:block;height:100%;border-radius:5px}
.nt-dot{display:inline-flex;align-items:center;gap:5px}
.nt-ok{color:#22c97a}.nt-no{color:#e05050}.nt-mu{color:var(--mu)}
.nt-scroll{overflow:auto;max-height:460px}
@media(max-width:1000px){.nt-kpis{grid-template-columns:repeat(3,1fr)}}
</style>
@endverbatim

<div class="nt">
  <div class="nt-bar">
    <div class="nt-fg"><span class="nt-fl" id="nt-l-from">من تاريخ</span><input type="date" id="nt-from" class="form-control" onchange="ntLoad()"></div>
    <div class="nt-fg"><span class="nt-fl" id="nt-l-to">إلى تاريخ</span><input type="date" id="nt-to" class="form-control" onchange="ntLoad()"></div>
    <button class="btn btn-ghost btn-sm" onclick="ntClear()" id="nt-clr">↺ <span>الكل</span></button>
    <div style="flex:1"></div>
    <button class="btn btn-ghost btn-sm" onclick="ntLoad()">🔄 <span id="nt-refresh">تحديث</span></button>
  </div>

  <div class="nt-kpis" id="nt-kpis"></div>

  <div class="nt-panel">
    <div class="nt-pt" id="nt-t-branch">🏢 حسب الفرع</div>
    <div class="nt-scroll"><table class="nt-tbl"><thead><tr id="nt-bh"></tr></thead><tbody id="nt-bb"></tbody></table></div>
  </div>

  <div class="nt-panel">
    <div class="nt-pt" id="nt-t-recent">🕑 آخر التنبيهات (سجل دقيق)</div>
    <div class="nt-scroll"><table class="nt-tbl"><thead><tr id="nt-rh"></tr></thead><tbody id="nt-rb"></tbody></table></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  'use strict';
  var AR={from:'من تاريخ',to:'إلى تاريخ',all:'الكل',refresh:'تحديث',tBranch:'🏢 حسب الفرع',tRecent:'🕑 آخر التنبيهات (سجل دقيق)',
    total:'الإجمالي',delivered:'وصلت',read:'قُرئت',acted:'تم التصرّف',unread:'غير مقروءة',avgRead:'م.زمن القراءة',avgAct:'م.زمن التصرّف',
    min:'دقيقة',branch:'الفرع',readRate:'نسبة القراءة',ac:'الحساب',mgr:'المدير',created:'أُنشئ',deliv:'وصل',readc:'قُرئ',act:'تصرّف',resp:'زمن الرد',
    yes:'✓',no:'—',tb:'متتبّع التنبيهات',none:'لا توجد بيانات'};
  var EN={from:'From',to:'To',all:'All',refresh:'Refresh',tBranch:'🏢 By Branch',tRecent:'🕑 Recent (precise log)',
    total:'Total',delivered:'Delivered',read:'Read',acted:'Acted',unread:'Unread',avgRead:'Avg read',avgAct:'Avg act',
    min:'min',branch:'Branch',readRate:'Read rate',ac:'Account',mgr:'Manager',created:'Created',deliv:'Delivered',readc:'Read',act:'Acted',resp:'Response',
    yes:'✓',no:'—',tb:'Notifications Tracker',none:'No data'};
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (L()==='en'?EN:AR)[k]||k;}
  function E(s){return window.esc?esc(s):String(s==null?'':s);}
  function set(id,v){var e=document.getElementById(id);if(e)e.textContent=v;}
  function dt(s){ if(!s)return '<span class="nt-mu">—</span>'; var d=new Date(s.replace(' ','T'));
    return d.toLocaleDateString(L()==='en'?'en-GB':'ar-EG',{month:'short',day:'numeric'})+' '+d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}); }
  function mins(a,b){ if(!a||!b)return null; return Math.round((new Date(b.replace(' ','T'))-new Date(a.replace(' ','T')))/60000); }

  function applyLang(){
    set('nt-l-from',t('from'));set('nt-l-to',t('to'));set('nt-refresh',t('refresh'));
    set('nt-t-branch',t('tBranch'));set('nt-t-recent',t('tRecent'));
    var tb=document.querySelector('.tb-title');if(tb)tb.textContent=t('tb');
    if(_data)render(_data);
  }
  var _o=window.applyLang;window.applyLang=function(l){if(_o)_o(l);try{applyLang();}catch(e){}};

  var _data=null;
  window.ntClear=function(){document.getElementById('nt-from').value='';document.getElementById('nt-to').value='';ntLoad();};
  window.ntLoad=function(){
    var f=document.getElementById('nt-from').value,tt=document.getElementById('nt-to').value,qs=[];
    if(f)qs.push('date_from='+f);if(tt)qs.push('date_to='+tt);
    api('GET','/notifications/tracker'+(qs.length?('?'+qs.join('&')):'')).then(function(r){
      if(r&&r.success){_data=r;render(r);}
    });
  };

  function kpi(v,l,c){return '<div class="nt-kpi"><div class="nt-kpi-v" style="color:'+c+'">'+v+'</div><div class="nt-kpi-l">'+l+'</div></div>';}
  function render(r){
    var to=r.totals||{};
    document.getElementById('nt-kpis').innerHTML=
      kpi((to.total||0).toLocaleString('en'),t('total'),'#1AADBA')+
      kpi((to.delivered||0).toLocaleString('en'),t('delivered'),'#3a9db5')+
      kpi((to.read||0).toLocaleString('en'),t('read'),'#22c97a')+
      kpi((to.acted||0).toLocaleString('en'),t('acted'),'#7b68ee')+
      kpi((to.unread||0).toLocaleString('en'),t('unread'),'#e05050')+
      kpi((to.avg_read_min==null?'—':to.avg_read_min)+'<span style="font-size:11px">'+(to.avg_read_min==null?'':(' '+t('min')))+'</span>',t('avgRead'),'#f5a623')+
      kpi((to.avg_act_min==null?'—':to.avg_act_min)+'<span style="font-size:11px">'+(to.avg_act_min==null?'':(' '+t('min')))+'</span>',t('avgAct'),'#e0708a');

    document.getElementById('nt-bh').innerHTML='<th>'+t('branch')+'</th><th>'+t('total')+'</th><th>'+t('read')+'</th><th>'+t('acted')+'</th><th>'+t('unread')+'</th><th>'+t('readRate')+'</th><th>'+t('avgRead')+'</th>';
    var bb=r.by_branch||[],isEn=L()==='en';
    document.getElementById('nt-bb').innerHTML=bb.length?bb.map(function(b){
      var tot=+b.total||0,rd=+b.read_c||0,rate=tot?Math.round(rd/tot*100):0,col=rate>=70?'#22c97a':rate>=40?'#f5a623':'#e05050';
      return '<tr><td style="font-weight:700">'+E(isEn?(b.nen||b.nar):(b.nar||b.nen))+'</td><td>'+tot+'</td><td class="nt-ok">'+rd+'</td><td style="color:#7b68ee">'+(+b.acted||0)+'</td><td class="'+((+b.unread||0)>0?'nt-no':'nt-mu')+'">'+(+b.unread||0)+'</td>'+
        '<td><div style="display:flex;align-items:center;gap:7px"><div class="nt-bar2"><i style="width:'+rate+'%;background:'+col+'"></i></div><span style="font-weight:800;color:'+col+'">'+rate+'%</span></div></td>'+
        '<td>'+(b.avg_read_min==null?'<span class="nt-mu">—</span>':(b.avg_read_min+' '+t('min')))+'</td></tr>';
    }).join(''):'<tr><td colspan="7" style="text-align:center;color:var(--mu);padding:24px">'+t('none')+'</td></tr>';

    document.getElementById('nt-rh').innerHTML='<th>'+t('ac')+'</th><th>'+t('branch')+'</th><th>'+t('mgr')+'</th><th>'+t('created')+'</th><th>'+t('deliv')+'</th><th>'+t('readc')+'</th><th>'+t('act')+'</th><th>'+t('resp')+'</th>';
    var rc=r.recent||[];
    document.getElementById('nt-rb').innerHTML=rc.length?rc.map(function(n){
      var resp=mins(n.created_at,n.read_at);
      return '<tr><td class="mono" style="font-weight:800;color:var(--pri2)">'+E(n.account_number)+'</td><td>'+E(n.branch||'—')+'</td><td>'+E(n.manager||'—')+'</td>'+
        '<td class="nt-mu">'+dt(n.created_at)+'</td><td>'+dt(n.delivered_at)+'</td><td>'+(n.read_at?'<span class="nt-ok">'+dt(n.read_at)+'</span>':'<span class="nt-no">'+t('no')+'</span>')+'</td>'+
        '<td>'+(n.acted_at?'<span style="color:#7b68ee">'+dt(n.acted_at)+'</span>':'<span class="nt-mu">'+t('no')+'</span>')+'</td>'+
        '<td>'+(resp==null?'<span class="nt-mu">—</span>':('<b>'+resp+'</b> '+t('min')))+'</td></tr>';
    }).join(''):'<tr><td colspan="8" style="text-align:center;color:var(--mu);padding:24px">'+t('none')+'</td></tr>';
  }

  applyLang();ntLoad();
})();
</script>
@endpush
