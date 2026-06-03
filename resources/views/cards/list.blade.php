@extends('layouts.app')
@section('title','Commission Cards')
@section('page-title','Commission Cards')

@section('content')
@verbatim
<style>
.k{max-width:100%;margin:0 auto}
.k-top{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:14px}
.k-tab{display:inline-flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);transition:all .15s}
.k-tab:hover{border-color:var(--pri);color:var(--pri2)}
.k-tab.on{background:rgba(26,173,186,.14);border-color:rgba(26,173,186,.4);color:var(--pri2)}
.k-new{display:inline-flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:800;text-decoration:none;background:linear-gradient(135deg,#22c97a,#179c5d);color:#fff;box-shadow:0 3px 12px rgba(34,201,122,.3)}
.k-new:hover{transform:translateY(-1px);color:#fff}
.k-badge{font-size:11px;color:var(--mu);background:var(--bg3);padding:5px 12px;border-radius:20px;border:1px solid var(--brd1)}
.k-kpis{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.k-kpi{display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:12px;background:var(--card-bg);border:1px solid var(--card-brd);flex:1;min-width:120px}
.k-kpi-i{width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.k-kpi-v{font-size:1.1rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1}
.k-kpi-l{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-top:2px}
.k-ins{display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;margin-bottom:12px}
.k-ins-c{display:flex;gap:9px;align-items:center;padding:9px 13px;border-radius:11px;background:var(--card-bg);border:1px solid var(--card-brd);white-space:nowrap;flex-shrink:0;border-left:3px solid var(--ic,#3a9db5)}
.k-ins-x{font-size:12px;font-weight:600;color:var(--tx)}.k-ins-s{font-weight:900}
.k-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;padding:10px 12px;background:linear-gradient(135deg,rgba(26,173,186,.07),rgba(123,104,238,.045));border:1px solid var(--card-brd);border-radius:12px;margin-bottom:12px;box-shadow:0 3px 14px rgba(0,0,0,.12)}
.k-fg{display:flex;flex-direction:column;gap:3px}
.k-fl{font-size:9px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;display:flex;align-items:center;gap:3px}
.k-bar .form-control{height:32px;font-size:11.5px;font-weight:600;padding:4px 8px;border-radius:8px;background:var(--bg2);border:1px solid var(--brd1);transition:border-color .15s,box-shadow .15s,background .15s}
.k-bar .form-control:hover{border-color:rgba(26,173,186,.4)}
.k-bar .form-control:focus{border-color:var(--pri);box-shadow:0 0 0 2px rgba(26,173,186,.16);background:var(--bg3)}
.k-bar select.form-control{cursor:pointer;min-width:108px}
.k-srch{position:relative;flex:2;min-width:200px}
.k-srch input{width:100%;padding-right:28px!important;font-size:11.5px!important;font-weight:600}
.k-srch input::placeholder{font-size:11px;font-weight:500}
.k-srch-ic{position:absolute;bottom:0;right:10px;height:32px;display:flex;align-items:center;font-size:12px;opacity:.45;pointer-events:none}
.k-srch:focus-within .k-srch-ic{opacity:.85;color:var(--pri2)}
.k-clr-btn{height:32px;border-radius:8px;padding:0 12px;border:1px solid var(--brd1);background:var(--bg2);color:var(--mu);font-weight:700;font-size:11.5px;cursor:pointer;transition:all .15s;display:inline-flex;align-items:center;gap:5px}
.k-clr-btn:hover{border-color:var(--re);color:var(--re);background:rgba(224,80,80,.07)}
/* lively scrollbar for the table */
.k-scroll::-webkit-scrollbar{width:11px;height:11px}
.k-scroll::-webkit-scrollbar-track{background:rgba(255,255,255,.03);border-radius:8px}
.k-scroll::-webkit-scrollbar-thumb{background:linear-gradient(var(--pri2),var(--pri3));border-radius:8px;border:2px solid var(--card-bg)}
.k-scroll::-webkit-scrollbar-thumb:hover{background:linear-gradient(#22C4D4,#1AADBA)}
.k-scroll{scrollbar-color:var(--pri2) rgba(255,255,255,.05);scrollbar-width:thin}
.k-wrap{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;overflow:hidden}
.k-scroll{overflow:auto;max-height:calc(100vh - 350px);min-height:280px}
.k-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
.k-tbl thead th{position:sticky;top:0;z-index:3;background:var(--bg3);padding:10px 11px;font-size:10px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;white-space:nowrap;border-bottom:2px solid var(--pri);cursor:pointer;user-select:none}
.k-tbl thead th:hover{background:rgba(26,173,186,.12)}
.k-tbl thead th .ar{opacity:.3;font-size:9px;margin-right:3px}
.k-tbl thead th.srt .ar{opacity:1;color:var(--pri2)}
.k-tbl tbody td{padding:9px 11px;border-bottom:1px solid var(--brd1)}
.k-tbl tbody tr:nth-child(even) td{background:rgba(255,255,255,.015)}
.k-tbl tbody tr:hover td{background:rgba(26,173,186,.07)}
.k-cc td:first-child{border-right:3px solid #7b68ee}
.k-mo td:first-child{border-right:3px solid #f5a623}
.k-nw td:first-child{border-right:3px solid #22c97a}
.k-tbl tfoot td{position:sticky;bottom:0;z-index:3;background:var(--bg3);padding:11px;font-weight:900;border-top:2px solid var(--pri);font-size:12px;font-family:'JetBrains Mono',monospace;white-space:nowrap}
.k-tf{font-family:'Tajawal',sans-serif;color:var(--pri2);font-size:11px}
.acn{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--pri2)}
.chip{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;white-space:nowrap}
.c-new{background:rgba(34,201,122,.14);color:#22c97a}.c-sub{background:rgba(58,157,181,.14);color:#3a9db5}
.c-mod{background:rgba(245,166,35,.14);color:#f5a623}.c-act{background:rgba(46,134,171,.14);color:#3a9db5}.c-ccx{background:rgba(123,104,238,.14);color:#7b68ee}
.lo{color:#22c97a}.mi{color:#f5a623}.hi{color:#e05050}
.k-edit{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:var(--bg3);border:1px solid var(--brd1);text-decoration:none;font-size:12px}
.k-edit:hover{background:rgba(245,166,35,.15);border-color:rgba(245,166,35,.4)}
.k-spin{width:22px;height:22px;border:3px solid rgba(26,173,186,.2);border-top-color:var(--pri2);border-radius:50%;animation:kspin .6s linear infinite;display:inline-block}
.k-pager{display:flex;align-items:center;justify-content:center;gap:6px;flex-wrap:wrap;margin-top:12px}
.k-pg{min-width:34px;height:34px;padding:0 10px;border-radius:9px;border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);font-weight:800;font-size:13px;cursor:pointer;transition:all .12s;display:inline-flex;align-items:center;justify-content:center}
.k-pg:hover:not(:disabled){border-color:var(--pri);color:var(--pri2)}
.k-pg.on{background:linear-gradient(135deg,var(--pri2),var(--pri3));border-color:transparent;color:#fff}
.k-pg:disabled{opacity:.4;cursor:not-allowed}
.k-pg-info{font-size:12px;font-weight:700;color:var(--mu);margin:0 8px}
@keyframes kspin{to{transform:rotate(360deg)}}
</style>
@endverbatim

<div class="k">
  <div class="k-top">
    <a href="{{ route('cards.index') }}" class="k-tab on">🗂 <span id="k-n-all">كل الحسابات</span></a>
    <a href="{{ route('cards.modified') }}" class="k-tab">✏️ <span id="k-n-mod">المعدّلة</span></a>
    <div style="flex:1"></div>
    <span class="k-badge" id="k-scope"></span>
    <button class="btn btn-ghost btn-sm" onclick="kExcel()" style="border-color:rgba(34,201,122,.3);color:#22c97a">📗 Excel</button>
    <a href="{{ route('cards.create') }}" class="k-new">➕ <span id="k-n-new">كرت جديد</span></a>
  </div>

  <div class="k-kpis">
    <div class="k-kpi"><div class="k-kpi-i" style="background:rgba(26,173,186,.14)">🗂</div><div><div class="k-kpi-v" style="color:var(--pri2)" id="k-total">—</div><div class="k-kpi-l" id="k-total-l">إجمالي الكروت</div></div></div>
    <div class="k-kpi"><div class="k-kpi-i" style="background:rgba(34,201,122,.14)">🟢</div><div><div class="k-kpi-v" style="color:#22c97a" id="k-new">—</div><div class="k-kpi-l" id="k-new-l">جديد</div></div></div>
    <div class="k-kpi"><div class="k-kpi-i" style="background:rgba(58,157,181,.14)">🔵</div><div><div class="k-kpi-v" style="color:#3a9db5" id="k-sub">—</div><div class="k-kpi-l" id="k-sub-l">فرعي</div></div></div>
    <div class="k-kpi"><div class="k-kpi-i" style="background:rgba(245,166,35,.14)">✏️</div><div><div class="k-kpi-v" style="color:#f5a623" id="k-mod">—</div><div class="k-kpi-l" id="k-mod-l">معدّلة</div></div></div>
    <div class="k-kpi"><div class="k-kpi-i" style="background:rgba(123,104,238,.14)">📞</div><div><div class="k-kpi-v" style="color:#7b68ee" id="k-cc">—</div><div class="k-kpi-l" id="k-cc-l">من CC</div></div></div>
    <div class="k-kpi"><div class="k-kpi-i" style="background:rgba(34,201,122,.14)">💵</div><div><div class="k-kpi-v" style="color:#22c97a" id="k-dep">—</div><div class="k-kpi-l" id="k-dep-l">إجمالي الإيداع</div></div></div>
  </div>

  <div class="k-ins" id="k-insights"></div>

  <div class="k-bar">
    <div class="k-fg k-srch"><span class="k-fl" id="k-fl-srch">🔍 بحث ذكي</span>
      <input type="text" id="k-search" class="form-control" placeholder="رقم حساب / بروكر / مسوّق / شهر..." oninput="kDeb()">
      <span class="k-srch-ic">🔍</span></div>
    <div class="k-fg"><span class="k-fl" id="k-fl-month">📅 الشهر</span>
      <select id="k-month" class="form-control" onchange="kRender()"><option value="" id="k-o-m">كل الشهور</option></select></div>
    <div class="k-fg"><span class="k-fl" id="k-fl-broker">🧑‍💼 البروكر</span>
      <select id="k-broker" class="form-control" onchange="kRender()"><option value="" id="k-o-b">كل البروكرات</option></select></div>
    <div class="k-fg"><span class="k-fl" id="k-fl-kind">🏷️ النوع</span>
      <select id="k-kind" class="form-control" onchange="kRender()"><option value="" id="k-o-k">الكل</option><option value="new" id="k-o-kn">🟢 جديد</option><option value="sub" id="k-o-ks">🔵 فرعي</option></select></div>
    <div class="k-fg"><span class="k-fl" id="k-fl-status">⚙️ الحالة</span>
      <select id="k-status" class="form-control" onchange="kRender()"><option value="" id="k-o-s">الكل</option><option value="active" id="k-o-sa">عادي</option><option value="modified" id="k-o-sm">معدّل</option><option value="new_added" id="k-o-sn">مضاف</option></select></div>
    <div class="k-fg"><span class="k-fl" id="k-fl-source">📡 المصدر</span>
      <select id="k-source" class="form-control" onchange="kRender()"><option value="" id="k-o-sr">الكل</option><option value="regular" id="k-o-rg">عادي</option><option value="cc" id="k-o-cc">📞 CC</option></select></div>
    <button class="k-clr-btn" onclick="kClear()" id="k-clear">✕ <span id="k-clear-l">مسح</span></button>
    <span id="k-count" style="font-size:11.5px;font-weight:700;color:var(--mu);align-self:center;margin-right:auto"></span>
  </div>

  <div class="k-wrap">
    <div class="k-scroll">
      <table class="k-tbl" id="k-tbl">
        <thead><tr id="k-head"></tr></thead>
        <tbody id="k-body"><tr><td colspan="15" style="text-align:center;padding:60px"><span class="k-spin"></span></td></tr></tbody>
        <tfoot id="k-foot" style="display:none"></tfoot>
      </table>
    </div>
  </div>
  <div class="k-pager" id="k-pager" style="display:none"></div>
</div>
@endsection

@push('scripts')
<script>
/* ── Global error catcher: registered BEFORE the main script so that even a
   parse/runtime error there becomes VISIBLE in the table (no more endless spinner). */
window.addEventListener('error', function(ev){
  try{
    var b=document.getElementById('k-body');
    if(b) b.innerHTML='<tr><td colspan="15" style="color:#e05050;padding:18px;direction:ltr;font-size:12px;white-space:pre-wrap;text-align:left">⚠️ JS ERROR:\n'+(ev.message||'')+'\n'+(ev.filename||'')+' : '+(ev.lineno||'')+':'+(ev.colno||'')+'</td></tr>';
  }catch(e){}
});
</script>
<script>
(function(){
  'use strict';
  var T={
    ar:{nAll:'كل الحسابات',nMod:'المعدّلة',nSrch:'بحث',nTree:'الشجرة',nNew:'كرت جديد',
      total:'إجمالي الكروت',knew:'جديد',ksub:'فرعي',kmod:'معدّلة',kcc:'من CC',kdep:'إجمالي الإيداع',
      flSrch:'بحث',flMonth:'الشهر',flBroker:'البروكر',flKind:'النوع',flStatus:'الحالة',flSource:'المصدر',
      oM:'كل الشهور',oB:'كل البروكرات',oK:'الكل',oKn:'🟢 جديد',oKs:'🔵 فرعي',oS:'الكل',oSa:'عادي',oSm:'معدّل',oSn:'مضاف',oSr:'الكل',oRg:'عادي',oCc:'📞 CC',
      clear:'مسح',search:'رقم حساب / بروكر / مسوّق...',
      thAc:'الحساب',thMonth:'الشهر',thBranch:'الفرع',thBroker:'البروكر',thBC:'ع.بروكر',thMk:'المسوّق',thMC:'ع.مسوّق',thTot:'إجمالي ع.',thDep:'إيداع أولي',thMon:'إيداع شهري',thKind:'النوع',thStatus:'الحالة',thSrc:'المصدر',
      cNew:'🟢 جديد',cSub:'🔵 فرعي',sMod:'✏️ معدّل',sNew:'🆕 مضاف',sAct:'✅ عادي',reg:'عادي',cc:'📞 CC',
      showing:'{n} من {t}',empty:'لا توجد كروت مطابقة',err:'تعذّر تحميل البيانات',retry:'إعادة',
      tfTotal:'الإجمالي',tfCards:'كرت',tfAvg:'متوسط',noExp:'لا توجد بيانات',done:'تم',tb:'كروت العمولات'},
    en:{nAll:'All Accounts',nMod:'Modified',nSrch:'Search',nTree:'Tree',nNew:'New Card',
      total:'Total Cards',knew:'New',ksub:'Sub',kmod:'Modified',kcc:'From CC',kdep:'Total Deposit',
      flSrch:'Search',flMonth:'Month',flBroker:'Broker',flKind:'Kind',flStatus:'Status',flSource:'Source',
      oM:'All Months',oB:'All Brokers',oK:'All',oKn:'🟢 New',oKs:'🔵 Sub',oS:'All',oSa:'Active',oSm:'Modified',oSn:'New Added',oSr:'All',oRg:'Regular',oCc:'📞 CC',
      clear:'Clear',search:'Account # / broker / marketer...',
      thAc:'Account',thMonth:'Month',thBranch:'Branch',thBroker:'Broker',thBC:'B.Comm',thMk:'Marketer',thMC:'M.Comm',thTot:'Total C.',thDep:'Initial',thMon:'Monthly',thKind:'Kind',thStatus:'Status',thSrc:'Source',
      cNew:'🟢 New',cSub:'🔵 Sub',sMod:'✏️ Modified',sNew:'🆕 New',sAct:'✅ Active',reg:'Regular',cc:'📞 CC',
      showing:'{n} of {t}',empty:'No matching cards',err:'Failed to load data',retry:'Retry',
      tfTotal:'TOTAL',tfCards:'cards',tfAvg:'avg',noExp:'No data',done:'Done',tb:'Commission Cards'}
  };
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (T[L()]&&T[L()][k])||T.ar[k]||k;}
  function set(id,v){var e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}

  var COLS=[['account_number','thAc',1],['month','thMonth',1],['branch','thBranch',0],['broker','thBroker',0],
    ['broker_commission','thBC',1],['marketer','thMk',0],['marketer_commission','thMC',1],['total','thTot',0],
    ['initial_deposit','thDep',1],['monthly_deposit','thMon',1],['account_kind','thKind',1],['status','thStatus',1],['source','thSrc',0],['act','',0]];
  var LAST=null,SK='month',SD='desc',timer=null,optsDone=false,PG=1,PER=50;

  function num(v){return parseFloat(v)||0;}
  function comm(c){return num(c.broker_commission)+num(c.marketer_commission)+num(c.ext_commission1)+num(c.ext_commission2);}
  function col(v){v=num(v);return v>5?'hi':v>2?'mi':'lo';}
  function kk(n){n=num(n);return n>=1e6?'$'+(n/1e6).toFixed(1)+'M':n>=1000?'$'+(n/1000).toFixed(0)+'K':'$'+n.toFixed(0);}
  function ff(n){return '$'+Math.round(num(n)).toLocaleString('en');}
  function E(s){return (window.esc?esc(s):String(s).replace(/[&<>"]/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}));}

  function applyLang(){
    set('k-n-all',t('nAll'));set('k-n-mod',t('nMod'));set('k-n-srch',t('nSrch'));set('k-n-tree',t('nTree'));set('k-n-new',t('nNew'));
    set('k-total-l',t('total'));set('k-new-l',t('knew'));set('k-sub-l',t('ksub'));set('k-mod-l',t('kmod'));set('k-cc-l',t('kcc'));set('k-dep-l',t('kdep'));
    set('k-fl-srch',t('flSrch'));set('k-fl-month',t('flMonth'));set('k-fl-broker',t('flBroker'));set('k-fl-kind',t('flKind'));set('k-fl-status',t('flStatus'));set('k-fl-source',t('flSource'));
    set('k-o-m',t('oM'));set('k-o-b',t('oB'));set('k-o-k',t('oK'));set('k-o-kn',t('oKn'));set('k-o-ks',t('oKs'));set('k-o-s',t('oS'));set('k-o-sa',t('oSa'));set('k-o-sm',t('oSm'));set('k-o-sn',t('oSn'));set('k-o-sr',t('oSr'));set('k-o-rg',t('oRg'));set('k-o-cc',t('oCc'));
    set('k-clear-l',t('clear'));
    var se=document.getElementById('k-search');if(se)se.placeholder=t('search');
    var tb=document.querySelector('.tb-title');if(tb)tb.textContent=t('tb');
    head();if(LAST)renderRows();
  }
  var _orig=window.applyLang;window.applyLang=function(l){if(_orig)_orig(l);try{applyLang();}catch(e){console.warn(e);}};

  function head(){
    var h=document.getElementById('k-head');if(!h)return;
    h.innerHTML='<th style="width:28px;cursor:default">#</th>'+COLS.map(function(c){
      var k=c[0],l=c[1],s=c[2];
      if(!s)return '<th style="cursor:default">'+(l?t(l):'')+'</th>';
      var on=SK===k,a=on?(SD==='asc'?'▲':'▼'):'⇅';
      return '<th class="'+(on?'srt':'')+'" onclick="window.kSort(\''+k+'\')">'+t(l)+' <span class="ar">'+a+'</span></th>';
    }).join('')+'<th style="cursor:default"></th>';
  }
  window.kSort=function(k){if(SK===k)SD=SD==='asc'?'desc':'asc';else{SK=k;SD='asc';}PG=1;head();fetchPage();};
  window.kDeb=function(){clearTimeout(timer);timer=setTimeout(function(){PG=1;fetchPage();},350);};
  window.kRender=function(){PG=1;fetchPage();};
  window.kClear=function(){['k-month','k-broker','k-kind','k-status','k-source'].forEach(function(id){var e=document.getElementById(id);if(e)e.value='';});var s=document.getElementById('k-search');if(s)s.value='';PG=1;fetchPage();};
  window.kGoPage=function(p){PG=p;fetchPage();var sc=document.querySelector('.k-scroll');if(sc)sc.scrollTop=0;window.scrollTo({top:0,behavior:'smooth'});};

  function buildPager(total){
    var box=document.getElementById('k-pager');if(!box)return;
    var pages=Math.max(1,Math.ceil(total/PER));
    if(pages<=1){box.style.display='none';box.innerHTML='';return;}
    box.style.display='flex';
    var isEn=L()==='en',h='';
    h+='<button class="k-pg" onclick="kGoPage(1)" '+(PG<=1?'disabled':'')+'>«</button>';
    h+='<button class="k-pg" onclick="kGoPage('+(PG-1)+')" '+(PG<=1?'disabled':'')+'>‹</button>';
    var s=Math.max(1,PG-2),e=Math.min(pages,s+4);s=Math.max(1,e-4);
    for(var p=s;p<=e;p++)h+='<button class="k-pg'+(p===PG?' on':'')+'" onclick="kGoPage('+p+')">'+p+'</button>';
    h+='<button class="k-pg" onclick="kGoPage('+(PG+1)+')" '+(PG>=pages?'disabled':'')+'>›</button>';
    h+='<button class="k-pg" onclick="kGoPage('+pages+')" '+(PG>=pages?'disabled':'')+'>»</button>';
    h+='<span class="k-pg-info">'+(isEn?('Page '+PG+' / '+pages):('صفحة '+PG+' / '+pages))+'</span>';
    box.innerHTML=h;
  }

  function curFilters(){
    return {
      q:((document.getElementById('k-search')||{}).value||'').trim(),
      month:(document.getElementById('k-month')||{}).value||'',
      broker_id:(document.getElementById('k-broker')||{}).value||'',
      kind:(document.getElementById('k-kind')||{}).value||'',
      status:(document.getElementById('k-status')||{}).value||'',
      source:(document.getElementById('k-source')||{}).value||''
    };
  }
  function qsFor(f,page,per){
    var qs='fast=1&page='+page+'&per='+per+'&sort='+encodeURIComponent(SK)+'&dir='+SD;
    if(f.q)qs+='&q='+encodeURIComponent(f.q);
    if(f.month)qs+='&month='+encodeURIComponent(f.month);
    if(f.broker_id)qs+='&broker_id='+encodeURIComponent(f.broker_id);
    if(f.kind)qs+='&kind='+encodeURIComponent(f.kind);
    if(f.status)qs+='&status='+encodeURIComponent(f.status);
    if(f.source)qs+='&source='+encodeURIComponent(f.source);
    return qs;
  }

  // Fetch only the current page (50 rows) from the server — keeps every load tiny & fast.
  function fetchPage(){
    var body=document.getElementById('k-body');
    if(body)body.innerHTML='<tr><td colspan="15" style="text-align:center;padding:40px"><span class="k-spin"></span></td></tr>';
    api('GET','/cards?'+qsFor(curFilters(),PG,PER)).then(function(r){
      if(r&&r.success&&r.rows){ LAST=r; onData(r); }
      else fail((r&&r.message)||t('err'));
    }).catch(function(e){ fail('EXCEPTION: '+(e&&e.message||e)); });
  }
  function onData(r){
    var sc=document.getElementById('k-scope');if(sc)sc.textContent=r.scope||'';
    if(!optsDone&&(r.months||r.brokers)){populateOpts(r);optsDone=true;}
    setKPIs(r);
    renderRows();
    buildPager(num(r.total));
  }

  function setKPIs(r){
    var tot=r.totals||{},total=num(r.total);
    set('k-total',total.toLocaleString('en'));set('k-new',num(tot.new).toLocaleString('en'));set('k-sub',num(tot.sub).toLocaleString('en'));
    set('k-mod',num(tot.mod).toLocaleString('en'));set('k-cc',num(tot.cc).toLocaleString('en'));set('k-dep',kk(tot.dep));
    set('k-count',total.toLocaleString('en')+' '+t('tfCards'));
    try{insights(r);}catch(e){}
  }

  function renderRows(){
    if(!LAST||!LAST.rows){return;}
    var isEn=L()==='en',rows=LAST.rows,total=num(LAST.total),tot=LAST.totals||{};
    if(!rows.length){document.getElementById('k-body').innerHTML='<tr><td colspan="15" style="text-align:center;padding:50px;color:var(--mu)"><div style="font-size:34px;opacity:.3">📭</div><div style="margin-top:8px;font-weight:700">'+t('empty')+'</div></td></tr>';document.getElementById('k-foot').style.display='none';return;}
    function chip(s){return {modified:'<span class="chip c-mod">'+t('sMod')+'</span>',new_added:'<span class="chip c-new">'+t('sNew')+'</span>',active:'<span class="chip c-act">'+t('sAct')+'</span>'}[s]||'<span class="chip c-act">'+(s||'')+'</span>';}
    var startNum=(PG-1)*PER,html=[];
    for(var j=0;j<rows.length;j++){var c2=rows[j],isCC=!!c2.cc_branch_id,bc2=num(c2.broker_commission),mc2=num(c2.marketer_commission),tt=bc2+mc2+num(c2.ext_commission1)+num(c2.ext_commission2);
      var rc=isCC?'k-cc':c2.status==='modified'?'k-mo':c2.status==='new_added'?'k-nw':'';
      var bn=isEn?(c2.branch_en||c2.branch_ar||'—'):(c2.branch_ar||'—');
      var mkN=(c2.marketer_name&&c2.marketer_name!==c2.broker_name)?E(c2.marketer_name):'<span style="color:var(--mu)">—</span>';
      html.push('<tr class="'+rc+'"><td style="color:var(--mu);font-size:10px">'+(startNum+j+1)+'</td><td><span class="acn">'+E(String(c2.account_number))+'</span></td><td style="color:var(--mu);font-size:11px;white-space:nowrap">'+E(c2.month||'—')+'</td><td style="font-size:11px">'+E(bn)+'</td><td style="font-weight:700;color:var(--pri2)">'+E(c2.broker_name||'—')+'</td><td><span class="mono '+col(bc2)+'">$'+bc2.toFixed(1)+'</span></td><td style="font-size:11px;color:var(--m2)">'+mkN+'</td><td><span class="mono '+col(mc2)+'">$'+mc2.toFixed(1)+'</span></td><td><span class="mono '+col(tt)+'" style="font-weight:700">$'+tt.toFixed(1)+'</span></td><td class="mono" style="color:var(--pri2);font-size:11px">'+kk(c2.initial_deposit)+'</td><td class="mono" style="color:#22c97a;font-size:11px">'+kk(c2.monthly_deposit)+'</td><td><span class="chip '+(c2.account_kind==='new'?'c-new':'c-sub')+'">'+(c2.account_kind==='new'?t('cNew'):t('cSub'))+'</span></td><td>'+chip(c2.status)+'</td><td>'+(isCC?'<span class="chip c-ccx">'+t('cc')+'</span>':'<span style="font-size:10px;color:var(--mu)">'+t('reg')+'</span>')+'</td><td><a href="/cards/'+c2.id+'/edit" class="k-edit" title="'+t('sMod')+'">✏️</a></td></tr>');
    }
    document.getElementById('k-body').innerHTML=html.join('');
    var tBC=num(tot.bc),tMC=num(tot.mc),tEC=num(tot.ec),tTot=tBC+tMC+tEC,avg=total?(tTot/total).toFixed(1):'0';
    document.getElementById('k-foot').innerHTML='<tr><td colspan="4"><span class="k-tf">'+t('tfTotal')+':</span> <span style="color:var(--pri2);font-size:15px">'+total.toLocaleString('en')+'</span> <span class="k-tf">'+t('tfCards')+'</span></td><td class="lo">$'+Math.round(tBC).toLocaleString('en')+'</td><td></td><td class="lo">$'+Math.round(tMC).toLocaleString('en')+'</td><td style="color:var(--or)">$'+Math.round(tTot).toLocaleString('en')+'<div style="font-size:9px;color:var(--mu);font-family:Tajawal">'+t('tfAvg')+' $'+avg+'</div></td><td style="color:var(--pri2)">'+ff(num(tot.dep))+'</td><td style="color:#22c97a">'+ff(num(tot.mon))+'</td><td colspan="3"></td></tr>';
    document.getElementById('k-foot').style.display='';
  }

  // Insights built from the SERVER aggregates (cover the whole filtered set, not just the page)
  function insights(r){
    var box=document.getElementById('k-insights');if(!box)return;
    var tot=r.totals||{},total=num(r.total);if(!total){box.innerHTML='';return;}
    var isEn=L()==='en',ins=[],S=function(v){return '<span class="k-ins-s">'+v+'</span>';},
      mk=function(n){n=num(n);return n>=1e6?'$'+(n/1e6).toFixed(2)+'M':n>=1000?'$'+(n/1000).toFixed(1)+'K':'$'+Math.round(n);};
    var totC=num(tot.bc)+num(tot.mc)+num(tot.ec),nN=num(tot.new),nS=num(tot.sub),nM=num(tot.mod),nC=num(tot.cc);
    if(r.top_broker&&r.top_broker.name){var p=Math.round(r.top_broker.count/total*100);ins.push({c:'#7b68ee',i:'🥇',x:isEn?'Top broker '+S(r.top_broker.name)+': '+S(r.top_broker.count)+' ('+p+'%)':'أنشط بروكر '+S(r.top_broker.name)+': '+S(r.top_broker.count)+' ('+p+'%)'});}
    ins.push({c:'#3a9db5',i:'💰',x:isEn?'Commission '+S(mk(totC)):'عمولات '+S(mk(totC))});
    if(totC>0){var rr=num(tot.dep)/totC;ins.push({c:'#22c97a',i:'💵',x:isEn?'$1 → '+S('$'+Math.round(rr).toLocaleString('en'))+' deposits':'كل $1 → '+S('$'+Math.round(rr).toLocaleString('en'))+' إيداعات'});}
    if(nN+nS>0){var np=Math.round(nN/(nN+nS)*100);ins.push({c:'#22c97a',i:'📈',x:isEn?S(np+'%')+' new vs '+(100-np)+'% sub':S(np+'%')+' جديدة مقابل '+(100-np)+'% فرعية'});}
    if(nM>0){var mp=Math.round(nM/total*100);ins.push({c:mp>20?'#f5a623':'#3a9db5',i:'✏️',x:isEn?S(nM)+' modified ('+mp+'%)':S(nM)+' معدّل ('+mp+'%)'});}
    if(nC>0){var cp=Math.round(nC/total*100);ins.push({c:'#7b68ee',i:'📞',x:isEn?S(cp+'%')+' via CC':S(cp+'%')+' عبر CC'});}
    box.innerHTML=ins.map(function(o){return '<div class="k-ins-c" style="--ic:'+o.c+'"><span style="font-size:18px">'+o.i+'</span><span class="k-ins-x">'+o.x+'</span></div>';}).join('');
  }

  function populateOpts(r){
    var m=document.getElementById('k-month'),b=document.getElementById('k-broker');
    if(m&&r.months)r.months.forEach(function(x){var o=document.createElement('option');o.value=o.textContent=x;m.appendChild(o);});
    if(b&&r.brokers)r.brokers.forEach(function(x){var o=document.createElement('option');o.value=x.id;o.textContent=x.name;b.appendChild(o);});
  }

  // Excel export: pull all matching rows (paged) from the server, then build the file.
  window.kExcel=function(){
    if(!LAST||!num(LAST.total)){if(window.toast)toast(t('noExp'),'error');return;}
    var isEn=L()==='en',total=Math.min(num(LAST.total),20000),per=500,pages=Math.ceil(total/per),f=curFilters(),all=[];
    if(window.toast)toast(isEn?'Preparing export…':'جاري تجهيز التصدير…','info');
    function grab(p){
      if(p>pages){buildXlsx();return;}
      api('GET','/cards?'+qsFor(f,p,per)).then(function(r){if(r&&r.rows)all=all.concat(r.rows);grab(p+1);}).catch(function(){buildXlsx();});
    }
    function buildXlsx(){
      if(!all.length){if(window.toast)toast(t('noExp'),'error');return;}
      var hdr=isEn?['#','Account','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Total','Initial','Monthly','Kind','Status','Source']:['#','الحساب','الشهر','الفرع','البروكر','ع.بروكر','المسوّق','ع.مسوّق','إجمالي','إيداع أولي','إيداع شهري','النوع','الحالة','المصدر'];
      var rows=[hdr];all.forEach(function(c,i){var tt=num(c.broker_commission)+num(c.marketer_commission)+num(c.ext_commission1)+num(c.ext_commission2);rows.push([i+1,c.account_number,c.month,isEn?(c.branch_en||c.branch_ar||''):(c.branch_ar||''),c.broker_name||'',num(c.broker_commission),c.marketer_name||'',num(c.marketer_commission),tt.toFixed(2),num(c.initial_deposit),num(c.monthly_deposit),c.account_kind||'',c.status||'',c.cc_branch_id?'CC':(isEn?'Regular':'عادي')]);});
      var wb=XLSX.utils.book_new(),ws=XLSX.utils.aoa_to_sheet(rows);ws['!cols']=hdr.map(function(){return {wch:15};});
      XLSX.utils.book_append_sheet(wb,ws,isEn?'Cards':'الكروت');XLSX.writeFile(wb,'WafraCards_'+new Date().toISOString().slice(0,10)+'.xlsx');
      if(window.toast)toast(t('done'),'success');
    }
    grab(1);
  };

  function fail(msg){
    document.getElementById('k-body').innerHTML='<tr><td colspan="15" style="padding:22px;color:var(--re)"><div style="font-size:26px;text-align:center">⚠️</div><div style="text-align:center;font-weight:700">'+t('err')+'</div><pre style="font-size:11px;color:var(--mu);white-space:pre-wrap;direction:ltr;background:rgba(0,0,0,.2);padding:8px;border-radius:8px;margin-top:8px">'+E(msg||'')+'</pre><div style="text-align:center"><button class="btn btn-ghost btn-sm" style="margin-top:8px" onclick="location.reload()">🔄 '+t('retry')+'</button></div></td></tr>';
  }

  // boot
  applyLang();
  fetchPage();
})();
</script>
@endpush
