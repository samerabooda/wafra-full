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
.k-filter-row th{padding:2px 4px!important;position:sticky;top:34px;z-index:5}
.kf-in{width:100%;font-size:10px!important;font-weight:600;padding:3px 6px!important;height:26px!important;min-height:0!important;border-radius:6px!important}
.kf-in::placeholder{font-size:9.5px;font-weight:500;opacity:.6}
.kf-clr{width:24px;height:24px;border:none;border-radius:6px;background:rgba(239,68,68,.14);color:#ef4444;font-size:12px;cursor:pointer;font-weight:800;line-height:1}
.kf-clr:hover{background:rgba(239,68,68,.28)}
.k-tbl thead th.kh{cursor:pointer;user-select:none;white-space:nowrap}
.k-tbl thead th.kh:hover{background:rgba(123,104,238,.10)}
.k-tbl thead th.kh.flt{color:#7b68ee}
.kh-fn{font-size:9px;opacity:.55}
.k-tbl thead th.kh.flt .kh-fn{opacity:1}
.k-fmenu{position:fixed;display:none;z-index:2147483000;width:320px;max-width:94vw;
  background:rgba(255,255,255,.82);-webkit-backdrop-filter:blur(18px) saturate(160%);backdrop-filter:blur(18px) saturate(160%);
  border:1.5px solid rgba(123,104,238,.55);border-radius:16px;box-shadow:0 24px 70px rgba(60,40,160,.35),0 0 0 4px rgba(123,104,238,.10);
  padding:12px;font-family:Tajawal,sans-serif;color:#1a1730}
[data-theme="dark"] .k-fmenu{background:rgba(30,28,48,.82);color:#eee;border-color:rgba(150,130,255,.6)}
.kfm-srt{display:flex;gap:8px;margin-bottom:9px}
.kfm-srt button{flex:1;padding:9px;font-size:13px;font-weight:800;border:1.5px solid rgba(123,104,238,.45);border-radius:10px;background:rgba(123,104,238,.12);color:#5b46d6;cursor:pointer;transition:.15s}
.kfm-srt button:hover{background:#7b68ee;color:#fff}
[data-theme="dark"] .kfm-srt button{color:#cdbcff}
.kfm-srch{font-size:13.5px!important;padding:9px 12px!important;height:40px!important;margin-bottom:9px;border-radius:10px!important;border:1.5px solid rgba(123,104,238,.35)!important;font-weight:600}
.kfm-list{max-height:300px;overflow:auto;border:1.5px solid rgba(123,104,238,.22);border-radius:12px;padding:5px;background:rgba(255,255,255,.5)}
[data-theme="dark"] .kfm-list{background:rgba(0,0,0,.18)}
.kfm-it{display:flex;align-items:center;gap:9px;padding:7px 9px;font-size:14px;font-weight:700;cursor:pointer;border-radius:9px}
.kfm-it:hover{background:rgba(123,104,238,.16)}
.kfm-it input{width:17px;height:17px;accent-color:#7b68ee;cursor:pointer}
.kfm-it span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.kfm-all{margin-bottom:6px;border-bottom:1.5px solid rgba(123,104,238,.25);padding-bottom:8px;font-size:14px}
.kfm-note{padding:6px;font-size:11px;color:var(--mu);text-align:center;font-weight:700}
.kfm-act{display:flex;gap:8px;margin-top:10px}
.kfm-act button{flex:1;padding:11px;font-size:14px;font-weight:900;border:none;border-radius:11px;cursor:pointer;transition:.15s}
.kfm-ok{background:linear-gradient(135deg,#7b68ee,#5b46d6);color:#fff;box-shadow:0 6px 16px rgba(123,104,238,.4)}
.kfm-ok:hover{filter:brightness(1.08)}
.kfm-cl{background:rgba(239,68,68,.16);color:#ef4444}
.kfm-cl:hover{background:rgba(239,68,68,.28)}
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
.k-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:13.5px}
.k-tbl thead th{position:sticky;top:0;z-index:3;background:var(--bg3);padding:11px 12px;font-size:12px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;white-space:nowrap;border-bottom:2px solid var(--pri);cursor:pointer;user-select:none}
.k-tbl thead th:hover{background:rgba(26,173,186,.12)}
.k-tbl thead th .ar{opacity:.3;font-size:9px;margin-right:3px}
.k-tbl thead th.srt .ar{opacity:1;color:var(--pri2)}
.k-tbl tbody td{padding:11px 12px;border-bottom:1px solid var(--brd1);font-size:13px;white-space:nowrap}
.k-tbl tbody tr:nth-child(even) td{background:rgba(255,255,255,.015)}
.k-tbl tbody tr:hover td{background:rgba(26,173,186,.07)}
.k-cc td:first-child{border-right:3px solid #7b68ee}
.k-mo td:first-child{border-right:3px solid #f5a623}
.k-nw td:first-child{border-right:3px solid #22c97a}
.k-tbl tfoot td{position:sticky;bottom:0;z-index:3;background:var(--bg3);padding:11px;font-weight:900;border-top:2px solid var(--pri);font-size:12px;font-family:'JetBrains Mono',monospace;white-space:nowrap}
.k-tf{font-family:'Tajawal',sans-serif;color:var(--pri2);font-size:11px}
/* ── High-contrast cell text — sharp & bright in BOTH themes (light=deep/saturated, dark=neon) ── */
.acn{font-family:'JetBrains Mono',monospace;font-weight:800;font-size:14px;color:#2DD4E6}
.knm-b{font-weight:800;font-size:13.7px;color:#2DD4E6}                 /* broker name */
.knm-m{font-weight:700;font-size:13px;color:#9CC4E8}                   /* marketer name */
.knm-dep{font-weight:800;font-size:13px;color:#2DD4E6}                 /* initial deposit */
.knm-ref{font-family:'JetBrains Mono',monospace;font-weight:800;color:#E0B65A}  /* referral account */
[data-theme="light"] .acn,[data-theme="light"] .knm-b,[data-theme="light"] .knm-dep{color:#06707F}
[data-theme="light"] .knm-m{color:#10325C}
[data-theme="light"] .knm-ref{color:#9A6A00}
.chip{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
.k-tbl .mono{font-size:12.5px;font-weight:700}
.c-new{background:rgba(34,201,122,.14);color:#22c97a}.c-sub{background:rgba(58,157,181,.14);color:#3a9db5}
.c-mod{background:rgba(245,166,35,.14);color:#f5a623}.c-act{background:rgba(46,134,171,.14);color:#3a9db5}.c-ccx{background:rgba(123,104,238,.14);color:#7b68ee}
.lo{color:#1ECC80}.mi{color:#F5A828}.hi{color:#FF5757}
[data-theme="light"] .lo{color:#0B9B57}[data-theme="light"] .mi{color:#C26A00}[data-theme="light"] .hi{color:#D62020}
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
  @include('cards._nav', ['active' => 'index', 'tabs' => ['index', 'modified']])
  <div class="k-top">
    <div style="flex:1"></div>
    <span class="k-badge" id="k-scope"></span>
    <button class="btn btn-ghost btn-sm" onclick="kClear()" title="مسح كل الفلاتر" style="border-color:rgba(239,68,68,.3);color:#ef4444">✕ مسح الفلاتر</button>
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
      thAc:'الحساب',thMonth:'شهر الفتح',thBranch:'الفرع',thBroker:'البروكر',thBC:'ع.بروكر',thMk:'المسوّق',thMC:'ع.مسوّق',thMk1:'المسوّق 1',thMC1:'ع.مسوّق1',thMk2:'المسوّق 2',thMC2:'ع.مسوّق2',thRef:'الإحالة',thRefC:'ع.إحالة',thTot:'إجمالي ع.',thDep:'إيداع أولي',thMon:'إيداع شهري',thKind:'النوع',thStatus:'الحالة',thSrc:'المصدر',
      cNew:'🟢 جديد',cSub:'🔵 فرعي',paLbl:'الحساب الأساسي:',paNone:'لا يوجد حساب أساسي مذكور',sMod:'✏️ معدّل',sNew:'🆕 مضاف',sAct:'✅ عادي',reg:'عادي',cc:'📞 CC',
      showing:'{n} من {t}',empty:'لا توجد كروت مطابقة',err:'تعذّر تحميل البيانات',retry:'إعادة',
      tfTotal:'الإجمالي',tfCards:'كرت',tfAvg:'متوسط',noExp:'لا توجد بيانات',done:'تم',tb:'كروت العمولات'},
    en:{nAll:'All Accounts',nMod:'Modified',nSrch:'Search',nTree:'Tree',nNew:'New Card',
      total:'Total Cards',knew:'New',ksub:'Sub',kmod:'Modified',kcc:'From CC',kdep:'Total Deposit',
      flSrch:'Search',flMonth:'Month',flBroker:'Broker',flKind:'Kind',flStatus:'Status',flSource:'Source',
      oM:'All Months',oB:'All Brokers',oK:'All',oKn:'🟢 New',oKs:'🔵 Sub',oS:'All',oSa:'Active',oSm:'Modified',oSn:'New Added',oSr:'All',oRg:'Regular',oCc:'📞 CC',
      clear:'Clear',search:'Account # / broker / marketer...',
      thAc:'Account',thMonth:'Opening',thBranch:'Branch',thBroker:'Broker',thBC:'B.Comm',thMk:'Marketer',thMC:'M.Comm',thMk1:'Marketer1',thMC1:'M1.Comm',thMk2:'Marketer2',thMC2:'M2.Comm',thRef:'Referral',thRefC:'Ref.Comm',thTot:'Total C.',thDep:'Initial',thMon:'Monthly',thKind:'Kind',thStatus:'Status',thSrc:'Source',
      cNew:'🟢 New',cSub:'🔵 Sub',paLbl:'Main account:',paNone:'No main account listed',sMod:'✏️ Modified',sNew:'🆕 New',sAct:'✅ Active',reg:'Regular',cc:'📞 CC',
      showing:'{n} of {t}',empty:'No matching cards',err:'Failed to load data',retry:'Retry',
      tfTotal:'TOTAL',tfCards:'cards',tfAvg:'avg',noExp:'No data',done:'Done',tb:'Commission Cards'}
  };
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (T[L()]&&T[L()][k])||T.ar[k]||k;}
  function set(id,v){var e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}

  var COLS=[['account_number','thAc',1],['broker','thBroker',1],['broker_commission','thBC',1],
    ['marketer','thMk1',1],['marketer_commission','thMC1',1],['marketer2','thMk2',1],['ext_commission2','thMC2',1],
    ['referral','thRef',1],['referral_commission','thRefC',1],['month','thMonth',1],['initial_deposit','thDep',1],['account_kind','thKind',1],['branch','thBranch',1]];
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
    head();if(LAST&&ROWS.length)applyView();
  }
  var _orig=window.applyLang;window.applyLang=function(l){if(_orig)_orig(l);try{applyLang();}catch(e){console.warn(e);}};

  function head(){
    var h=document.getElementById('k-head');if(!h)return;
    h.innerHTML='<th style="width:28px;cursor:default">#</th>'+COLS.map(function(c){
      var k=c[0],l=c[1],s=c[2];
      if(!s)return '<th style="cursor:default">'+(l?t(l):'')+'</th>';
      var sorted=SK===k, filtered=!!CFV[k];
      var ar=sorted?(SD==='asc'?'▲':'▼'):'';
      return '<th class="kh'+(sorted?' srt':'')+(filtered?' flt':'')+'" title="'+(L()==='en'?'Sort & filter':'فرز وتصفية')+'" onclick="window.kMenu(event,\''+k+'\')">'
        +'<span>'+t(l)+'</span> '+(ar?'<span class="ar">'+ar+'</span> ':'')+'<span class="kh-fn">'+(filtered?'🔽':'▾')+'</span></th>';
    }).join('')+'<th style="cursor:default"></th>';
  }
  // Sorting + filtering now happen ON THE SERVER (true pagination, scales to any size).
  window.kSort=function(k){if(SK===k)SD=SD==='asc'?'desc':'asc';else{SK=k;SD='asc';}PG=1;head();fetchPage();};
  window.kDeb=function(){clearTimeout(timer);timer=setTimeout(function(){PG=1;fetchPage();},350);};
  window.kRender=function(){PG=1;fetchPage();};
  window.kClear=function(){CFV={};SK='month';SD='desc';head();PG=1;fetchPage();};
  // ── Excel-style AutoFilter: each column header opens a sort + searchable checklist popup ──
  // CFV: col -> array of RAW selected values ('__blank__' = NULL/empty). Sent to the server as colf.
  var CFV={};
  var KFCOL=null,KFITEMS=[],KFWORK=null,KFSEARCH='';

  function colVal(c,col){switch(col){
    case 'account_number':     return String(c.account_number==null?'':c.account_number);
    case 'broker':             return c.broker_name||'(Self)';
    case 'broker_commission':  return num(c.broker_commission).toFixed(2);
    case 'marketer':           return c.marketer_name||'(Self)';
    case 'marketer_commission':return num(c.marketer_commission).toFixed(2);
    case 'marketer2':          return c.marketer2_name||'(—)';
    case 'ext_commission2':    return num(c.ext_commission2).toFixed(2);
    case 'referral':           return c.referral_account?String(c.referral_account):'(—)';
    case 'referral_commission':return num(c.referral_commission).toFixed(2);
    case 'month':              return c.month||(L()==='en'?'(blank)':'(فارغ)');
    case 'initial_deposit':    return num(c.initial_deposit).toFixed(2);
    case 'account_kind':       return c.account_kind==='sub'?t('cSub'):t('cNew');
    case 'branch':             return (L()==='en'?(c.branch_en||c.branch_ar):c.branch_ar)||'(—)';
    default: return '';
  }}

  function ensureMenu(){
    var m=document.getElementById('k-fmenu');
    if(!m){m=document.createElement('div');m.id='k-fmenu';m.className='k-fmenu';document.body.appendChild(m);
      document.addEventListener('click',function(ev){if(m.style.display==='block'&&!m.contains(ev.target))m.style.display='none';});
      window.addEventListener('resize',function(){m.style.display='none';});}
    return m;
  }
  function positionMenu(anchor){
    var m=document.getElementById('k-fmenu');if(!m||!anchor)return;
    var r=anchor.getBoundingClientRect(),w=m.offsetWidth||240;
    var left=Math.min(Math.max(8,r.right-w),window.innerWidth-w-8);
    var top=Math.min(r.bottom+4,window.innerHeight-m.offsetHeight-8);
    m.style.left=left+'px';m.style.top=Math.max(8,top)+'px';
  }
  window.kMenu=function(ev,col){
    ev.stopPropagation();KFCOL=col;KFSEARCH='';KFITEMS=[];KFWORK=new Set();
    var anchor=ev.currentTarget;
    ensureMenu();var m=document.getElementById('k-fmenu');
    m.innerHTML='<div style="padding:26px;text-align:center"><span class="k-spin"></span></div>';
    m.style.display='block';positionMenu(anchor);
    // Distinct values come from the SERVER (no full client dataset needed).
    api('GET','/cards/distinct?col='+encodeURIComponent(col)).then(function(r){
      if(KFCOL!==col)return;                       // user moved on
      KFITEMS=(r&&r.success&&Array.isArray(r.data))?r.data:[];
      // translate kind labels to the localized chips
      if(col==='account_kind')KFITEMS.forEach(function(it){it.label=it.v==='new'?t('cNew'):it.v==='sub'?t('cSub'):it.label;});
      var sel=CFV[col]?new Set(CFV[col].map(String)):null;   // null = none selected yet → select all
      KFWORK=new Set();KFITEMS.forEach(function(it){if(!sel||sel.has(String(it.v)))KFWORK.add(String(it.v));});
      buildMenuShell();renderList();positionMenu(anchor);
    }).catch(function(){ if(KFCOL===col){m.innerHTML='<div style="padding:18px;text-align:center;color:#e05050">⚠️</div>';} });
  };
  function buildMenuShell(){
    var m=document.getElementById('k-fmenu'),isEn=L()==='en';
    m.innerHTML='<div class="kfm-srt"><button onclick="window.kMenuSort(\'asc\')">▲ '+(isEn?'A → Z':'تصاعدي')+'</button><button onclick="window.kMenuSort(\'desc\')">▼ '+(isEn?'Z → A':'تنازلي')+'</button></div>'
      +'<input class="kfm-srch form-control" placeholder="'+(isEn?'Search…':'بحث…')+'" oninput="window.kMenuSearch(this.value)">'
      +'<label class="kfm-it kfm-all"><input type="checkbox" id="kfm-allck" onchange="window.kMenuAll(this.checked)"> <b>'+(isEn?'Select all':'تحديد الكل')+'</b> <span id="kfm-cnt" style="color:var(--mu);font-weight:600"></span></label>'
      +'<div class="kfm-list" id="kfm-list"></div>'
      +'<div class="kfm-act"><button class="kfm-ok" onclick="window.kMenuApply()">'+(isEn?'Apply':'تطبيق')+'</button><button class="kfm-cl" onclick="window.kMenuClear()">'+(isEn?'Clear':'مسح')+'</button></div>';
  }
  function renderList(){
    var lst=document.getElementById('kfm-list');if(!lst)return;
    var sl=KFSEARCH.toLowerCase();
    var list=KFITEMS.filter(function(it){return !sl||String(it.label).toLowerCase().indexOf(sl)>=0;});
    var cap=800,shown=list.slice(0,cap);
    lst.innerHTML=shown.map(function(it){var v=String(it.v);
      return '<label class="kfm-it"><input type="checkbox" '+(KFWORK.has(v)?'checked':'')+' onchange="window.kTick(this)" data-v="'+E(v)+'"> <span>'+E(it.label)+'</span></label>';
    }).join('')||('<div style="padding:10px;color:var(--mu);text-align:center">—</div>');
    if(list.length>cap)lst.innerHTML+='<div class="kfm-note">+'+(list.length-cap)+' …</div>';
    _kfCnt();
  }
  function _kfCnt(){var cnt=document.getElementById('kfm-cnt');if(cnt)cnt.textContent='('+KFWORK.size+'/'+KFITEMS.length+')';
    var a=document.getElementById('kfm-allck');if(a)a.checked=(KFITEMS.length>0&&KFWORK.size===KFITEMS.length);}
  window.kTick=function(cb){var v=cb.getAttribute('data-v');if(cb.checked)KFWORK.add(v);else KFWORK['delete'](v);_kfCnt();};
  window.kMenuAll=function(ck){KFWORK=ck?new Set(KFITEMS.map(function(it){return String(it.v);})):new Set();renderList();};
  window.kMenuSearch=function(v){KFSEARCH=v;renderList();};
  window.kMenuSort=function(dir){SK=KFCOL;SD=dir;document.getElementById('k-fmenu').style.display='none';PG=1;head();fetchPage();};
  window.kMenuApply=function(){
    if(KFWORK.size===KFITEMS.length){delete CFV[KFCOL];}        // all selected → no filter
    else if(KFWORK.size===0){CFV[KFCOL]=['__none__'];}          // none selected → match nothing
    else{CFV[KFCOL]=Array.from(KFWORK);}
    document.getElementById('k-fmenu').style.display='none';PG=1;head();fetchPage();};
  window.kMenuClear=function(){delete CFV[KFCOL];document.getElementById('k-fmenu').style.display='none';PG=1;head();fetchPage();};
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

  // ── Server-driven engine: each page/sort/filter is ONE server request (true pagination) ──
  var ROWS=[],TOT={},TOTAL=0;

  // Referral account → referrer name (for hover/inline name in the referral column).
  window._kRefMap = window._kRefMap || {};
  window._kRefType = window._kRefType || {};
  (function(){ try{ api('GET','/cards/referrer-names').then(function(r){
    if(r&&r.success){ (r.data||[]).forEach(function(x){ window._kRefMap[String(x.account)]=x.name||''; window._kRefType[String(x.account)]=x.type||'client'; });
      if(typeof applyView==='function' && ROWS.length) applyView(); }
  }); }catch(e){} })();

  // Build the query string from the current sort + page + per-column filters (colf).
  function buildQS(page,per){
    var qs='fast=1&page='+page+'&per='+per+'&sort='+encodeURIComponent(SK)+'&dir='+SD;
    var cf={},any=false;
    for(var k in CFV){ if(CFV[k]&&CFV[k].length){ cf[k]=CFV[k]; any=true; } }
    if(any)qs+='&colf='+encodeURIComponent(JSON.stringify(cf));
    return qs;
  }
  var _seq=0;
  function fetchPage(){   // fetch ONE server page for the current sort/filter set
    var body=document.getElementById('k-body');
    if(body)body.innerHTML='<tr><td colspan="15" style="text-align:center;padding:40px"><span class="k-spin"></span></td></tr>';
    var my=++_seq;
    api('GET','/cards?'+buildQS(PG,PER)).then(function(r){
      if(my!==_seq)return;                                   // a newer request superseded this one
      if(r&&r.success&&r.rows){
        LAST=r; ROWS=r.rows; TOT=r.totals||{}; TOTAL=num(r.total);
        var sc=document.getElementById('k-scope');if(sc)sc.textContent=r.scope||'';
        if(!optsDone&&(r.months||r.brokers)){populateOpts(r);optsDone=true;}
        var pages=Math.max(1,Math.ceil(TOTAL/PER));
        if(PG>pages&&pages>=1){PG=pages;return fetchPage();}   // clamp page after a filter shrinks the set
        applyView();
      } else fail((r&&r.message)||t('err'));
    }).catch(function(e){ if(my===_seq)fail('EXCEPTION: '+(e&&e.message||e)); });
  }

  // Render-only (no client filtering/sorting — the server already did both).
  function applyView(){ renderRows(); buildPager(TOTAL); setKPIs(); }

  function setKPIs(){
    var tot=TOT||{};
    set('k-total',TOTAL.toLocaleString('en'));set('k-new',num(tot.new).toLocaleString('en'));set('k-sub',num(tot.sub).toLocaleString('en'));
    set('k-mod',num(tot.mod).toLocaleString('en'));set('k-cc',num(tot.cc).toLocaleString('en'));set('k-dep',kk(tot.dep));
    set('k-count',TOTAL.toLocaleString('en')+' '+t('tfCards'));
    try{insights({totals:tot,total:TOTAL,top_broker:LAST&&LAST.top_broker,top_branch:LAST&&LAST.top_branch});}catch(e){}
  }

  function renderRows(){
    var isEn=L()==='en',total=TOTAL,tot=TOT||{};
    var rows=ROWS||[];
    if(!TOTAL){document.getElementById('k-body').innerHTML='<tr><td colspan="15" style="text-align:center;padding:50px;color:var(--mu)"><div style="font-size:34px;opacity:.3">📭</div><div style="margin-top:8px;font-weight:700">'+t('empty')+'</div></td></tr>';document.getElementById('k-foot').style.display='none';return;}
    function chip(s){return {modified:'<span class="chip c-mod">'+t('sMod')+'</span>',new_added:'<span class="chip c-new">'+t('sNew')+'</span>',active:'<span class="chip c-act">'+t('sAct')+'</span>'}[s]||'<span class="chip c-act">'+(s||'')+'</span>';}
    var startNum=(PG-1)*PER,html=[];
    var DASH='<span style="color:var(--mu)">—</span>';
    for(var j=0;j<rows.length;j++){var c2=rows[j],isCC=!!c2.cc_branch_id,bc2=num(c2.broker_commission),mc2=num(c2.marketer_commission),ec2=num(c2.ext_commission2);
      var rc=isCC?'k-cc':c2.status==='modified'?'k-mo':c2.status==='new_added'?'k-nw':'';
      var bn=isEn?(c2.branch_en||c2.branch_ar||'—'):(c2.branch_ar||'—');
      var SELF='<span style="color:var(--mu);font-style:italic">Self</span>';
      var bkN=c2.broker_name?E(c2.broker_name):SELF;
      var mkN=c2.marketer_name?E(c2.marketer_name):SELF;
      var m2N=c2.marketer2_name?E(c2.marketer2_name):DASH;
      var _rnm=c2.referral_account?(window._kRefMap&&window._kRefMap[String(c2.referral_account)]):'';
      var _rty=c2.referral_account?(window._kRefType&&window._kRefType[String(c2.referral_account)]):'';
      var _rbadge=(_rty==='employee')?' <span style="font-size:9px;color:#5b46d6;font-weight:800;background:rgba(123,104,238,.14);border-radius:7px;padding:0 4px">👤 موظف</span>':'';
      var refN=c2.referral_account?('<span class="knm-ref" title="'+(_rnm?('المحيل: '+E(_rnm)):'')+'" style="cursor:help">'+E(String(c2.referral_account))+(_rnm?' <span style="color:var(--mu);font-weight:600;font-size:10px">('+E(_rnm)+')</span>':'')+_rbadge+'</span>'):DASH;
      var rcV=num(c2.referral_commission);
      var rcN=c2.referral_account?('<span class="mono '+col(rcV)+'">$'+rcV.toFixed(1)+'</span>'):DASH;
      html.push('<tr class="'+rc+'">'
        +'<td style="color:var(--mu);font-size:11px">'+(startNum+j+1)+'</td>'
        +'<td><span class="acn">'+E(String(c2.account_number))+'</span></td>'
        +'<td class="knm-b">'+bkN+'</td>'
        +'<td><span class="mono '+col(bc2)+'">$'+bc2.toFixed(1)+'</span></td>'
        +'<td class="knm-m">'+mkN+'</td>'
        +'<td><span class="mono '+col(mc2)+'">$'+mc2.toFixed(1)+'</span></td>'
        +'<td class="knm-m">'+m2N+'</td>'
        +'<td><span class="mono '+col(ec2)+'">$'+ec2.toFixed(1)+'</span></td>'
        +'<td>'+refN+'</td>'
        +'<td>'+rcN+'</td>'
        +'<td style="color:var(--mu);font-size:12.5px;white-space:nowrap">'+E(c2.month||'—')+((c2.status==='modified'&&c2.modified_month)?'<br><span style="color:#f5a623;font-weight:800;font-size:10px">✏️ '+E(c2.modified_month)+'</span>':'')+'</td>'
        +'<td class="mono knm-dep" style="font-size:12.5px">'+kk(c2.initial_deposit)+'</td>'
        +'<td>'+(c2.account_kind==='new'
            ? '<span class="chip c-new">'+t('cNew')+'</span>'
            : '<span class="chip c-sub" style="cursor:pointer" title="'+(c2.parent_account?(t('paLbl')+' '+E(String(c2.parent_account))):'')+'" onclick="window.kShowParent(event, this, \''+(c2.parent_account?E(String(c2.parent_account)):'')+'\')">'+t('cSub')+' 🔎</span>')+'</td>'
        +'<td style="font-size:12.5px;font-weight:600">'+E(bn)+'</td>'
        +'<td><a href="/cards/'+c2.id+'/edit" class="k-edit" title="'+t('sMod')+'">✏️</a></td></tr>');
    }
    document.getElementById('k-body').innerHTML=html.join('');
    buildSearchList(rows);
    var tBC=num(tot.bc),tMC=num(tot.mc),tEC=num(tot.ec);
    document.getElementById('k-foot').innerHTML='<tr><td colspan="3"><span class="k-tf">'+t('tfTotal')+':</span> <span class="knm-dep" style="font-size:16px">'+total.toLocaleString('en')+'</span> <span class="k-tf">'+t('tfCards')+'</span></td><td class="lo">$'+Math.round(tBC).toLocaleString('en')+'</td><td></td><td class="lo">$'+Math.round(tMC).toLocaleString('en')+'</td><td></td><td class="lo">$'+Math.round(tEC).toLocaleString('en')+'</td><td></td><td></td><td></td><td class="knm-dep">'+ff(num(tot.dep))+'</td><td colspan="3"></td></tr>';
    document.getElementById('k-foot').style.display='';
  }

  // Typeable dropdown (datalist): suggest account numbers + broker/marketer names + months.
  // Accumulates across pages; free typing still does a full server search for anything.
  var SUGG={};
  function buildSearchList(rows){
    var dl=document.getElementById('k-search-list'); if(!dl) return;
    (rows||[]).forEach(function(c){
      if(c.account_number) SUGG['#'+c.account_number]=String(c.account_number);
      if(c.broker_name)    SUGG['b'+c.broker_name]=c.broker_name;
      if(c.marketer_name)  SUGG['m'+c.marketer_name]=c.marketer_name;
      if(c.marketer2_name) SUGG['m'+c.marketer2_name]=c.marketer2_name;
    });
    if(LAST){
      (LAST.brokers||[]).forEach(function(x){ if(x&&x.name) SUGG['b'+x.name]=x.name; });
      (LAST.months||[]).forEach(function(mo){ if(mo) SUGG['o'+mo]=mo; });
    }
    var keys=Object.keys(SUGG),opts=[];
    for(var i=0;i<keys.length && i<3000;i++){ opts.push('<option value="'+E(SUGG[keys[i]])+'"></option>'); }
    dl.innerHTML=opts.join('');
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

  // Click a "Sub" chip → show the main/parent account number (from the New/Sub column).
  // Show the parent (main) account in a small popover anchored to the clicked chip —
  // never at the bottom of the page where it would overlap the totals row.
  window.kShowParent=function(ev, el, pa){
    if(ev && ev.stopPropagation) ev.stopPropagation();
    var old=document.getElementById('k-parent-pop'); if(old) old.remove();
    var pop=document.createElement('div');
    pop.id='k-parent-pop';
    pop.style.cssText='position:fixed;z-index:99999;background:var(--card-bg,#fff);border:1.5px solid #3a9db5;'
      +'border-radius:12px;padding:9px 13px;box-shadow:0 12px 34px rgba(0,0,0,.28);font-size:13px;font-weight:800;'
      +'color:var(--tx,#222);white-space:nowrap;display:flex;align-items:center;gap:8px';
    pop.innerHTML = pa
      ? '🔵 '+t('paLbl')+' <span style="color:#3a9db5;font-family:monospace;font-size:15px">'+pa+'</span>'
      : '<span style="color:var(--mu,#888)">'+t('paNone')+'</span>';
    document.body.appendChild(pop);
    // position centered above the chip; flip below if there isn't room
    var r=el.getBoundingClientRect(), pw=pop.offsetWidth, ph=pop.offsetHeight;
    var top=r.top - ph - 8; if(top < 8) top = r.bottom + 8;
    var left=Math.max(8, Math.min(r.left + r.width/2 - pw/2, window.innerWidth - pw - 8));
    pop.style.top=top+'px'; pop.style.left=left+'px';
    // dismiss on any outside click, on scroll, or after a few seconds
    var close=function(e){ if(e && e.type==='click' && pop.contains(e.target)) return;
      pop.remove(); document.removeEventListener('click',close,true); window.removeEventListener('scroll',close,true); };
    setTimeout(function(){ document.addEventListener('click',close,true); window.addEventListener('scroll',close,true); }, 0);
    setTimeout(function(){ if(pop.parentNode) pop.remove(); }, 4500);
  };

  // Excel export: fetch ALL rows matching the current filters/sort from the server, then export.
  window.kExcel=function(){
    var isEn=L()==='en';
    if(!TOTAL){if(window.toast)toast(t('noExp'),'error');return;}
    if(window.toast)toast(isEn?'Preparing export…':'جاري تجهيز التصدير…','info');
    api('GET','/cards?'+buildQS(1,100000)).then(function(r){
      var all=(r&&r.rows)||[];
      if(!all.length){if(window.toast)toast(t('noExp'),'error');return;}
      var hdr=isEn?['#','Account','Broker','B.Comm','Marketer1','M1.Comm','Marketer2','M2.Comm','Referral','Ref.Comm','Opening Month','Initial Deposit','Kind','Branch']:['#','الحساب','البروكر','ع.بروكر','المسوّق1','ع.مسوّق1','المسوّق2','ع.مسوّق2','الإحالة','ع.الإحالة','شهر الفتح','الإيداع الأولي','النوع','الفرع'];
      var rows=[hdr];all.forEach(function(c,i){rows.push([i+1,c.account_number,c.broker_name||'',num(c.broker_commission),c.marketer_name||'',num(c.marketer_commission),c.marketer2_name||'',num(c.ext_commission2),c.referral_account||'',num(c.referral_commission),c.month,num(c.initial_deposit),c.account_kind||'',isEn?(c.branch_en||c.branch_ar||''):(c.branch_ar||'')]);});
      wgExportExcel(rows, 'WafraCards_'+new Date().toISOString().slice(0,10)+'.xlsx', isEn?'Cards':'الكروت',
        { colColors:{ 1:'EAF3DE', 3:'FAEEDA', 5:'FAEEDA', 7:'FAEEDA', 9:'FAEEDA', 10:'EEEDFE', 11:'E6F1FB' } });
    }).catch(function(){ if(window.toast)toast(t('noExp'),'error'); });
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
