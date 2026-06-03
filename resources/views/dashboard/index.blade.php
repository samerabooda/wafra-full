@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
@verbatim
<style>
.ig{max-width:1300px;margin:0 auto}
/* Glassy header bar */
.ig-head{display:flex;align-items:center;gap:12px;flex-wrap:wrap;padding:14px 18px;margin-bottom:16px;border-radius:18px;background:linear-gradient(135deg,rgba(26,173,186,.14),rgba(123,104,238,.08));backdrop-filter:blur(18px) saturate(160%);-webkit-backdrop-filter:blur(18px) saturate(160%);border:1px solid rgba(255,255,255,.14);box-shadow:0 8px 30px rgba(0,0,0,.2)}
.ig-head-t{display:flex;align-items:center;gap:11px}
.ig-head-f{display:flex;flex-direction:column;gap:3px}
.ig-fl{font-size:9px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.4px}
.ig-head .form-control{background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.16);min-width:120px}
.ig-clr{align-self:flex-end;padding:9px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.16);background:rgba(255,255,255,.06);color:var(--tx);font-size:12px;font-weight:700;cursor:pointer}
.ig-clr:hover{border-color:var(--pri);color:var(--pri2)}
/* Premium KPI tiles with icon */
.ig-nums{display:grid;grid-template-columns:repeat(6,1fr);gap:12px;margin-bottom:16px}
.ig-num{display:flex;align-items:center;gap:12px;background:linear-gradient(135deg,rgba(255,255,255,.08),rgba(255,255,255,.02));backdrop-filter:blur(16px) saturate(155%);-webkit-backdrop-filter:blur(16px) saturate(155%);border:1px solid rgba(255,255,255,.12);border-radius:16px;padding:15px 16px;position:relative;overflow:hidden;box-shadow:0 6px 22px rgba(0,0,0,.16);transition:transform .18s,box-shadow .18s}
.ig-num:hover{transform:translateY(-3px);box-shadow:0 12px 30px rgba(0,0,0,.24)}
.ig-num::after{content:'';position:absolute;bottom:-50%;left:-20%;width:120px;height:120px;border-radius:50%;background:radial-gradient(circle,var(--ac,#1AADBA),transparent 68%);opacity:.13;pointer-events:none}
.ig-num-ic{width:42px;height:42px;border-radius:13px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:19px;background:var(--ac,#1AADBA);box-shadow:0 4px 14px rgba(0,0,0,.25);position:relative}
.ig-num-b{min-width:0;position:relative}
.ig-num-v{font-size:1.5rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1;color:var(--ac,#1AADBA)}
.ig-num-l{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-top:5px;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* panels */
.ig-grid{display:grid;gap:16px;margin-bottom:16px}
.ig-3{grid-template-columns:repeat(3,1fr)}
.ig-21{grid-template-columns:2fr 1fr}
.ig-panel{background:linear-gradient(160deg,rgba(255,255,255,.05),rgba(255,255,255,.015));border:1px solid rgba(255,255,255,.1);border-radius:16px;padding:16px;box-shadow:0 6px 24px rgba(0,0,0,.18)}
.ig-pt{font-size:12px;font-weight:800;color:var(--m2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px}
.ig-canvas{position:relative;height:200px}
.ig-center{position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none}
.ig-center-v{font-size:2rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1}
.ig-center-l{font-size:10px;color:var(--mu);margin-top:3px;text-transform:uppercase;letter-spacing:.5px}
.ig-gauge{position:relative;height:170px}
.ig-gauge-c{position:absolute;left:0;right:0;bottom:6px;display:flex;flex-direction:column;align-items:center}
.ig-gauge-v{font-size:2.2rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1}
.ig-gauge-l{font-size:10px;color:var(--mu);margin-top:2px}
.ig-leg{display:flex;flex-direction:column;gap:9px;margin-top:10px}
.ig-leg-i{display:flex;align-items:center;gap:8px;font-size:12px}
.ig-leg-d{width:11px;height:11px;border-radius:3px;flex-shrink:0}
.ig-leg-n{color:var(--mu);flex:1}.ig-leg-v{font-weight:800;font-family:'JetBrains Mono',monospace}
/* gradient bars (infographic style) */
.ig-gb{margin-bottom:13px}
.ig-gb:last-child{margin-bottom:2px}
.ig-gb-top{display:flex;align-items:center;gap:7px;font-size:12.5px;margin-bottom:5px}
.ig-gb-rk{width:18px;height:18px;border-radius:6px;display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:900;color:#fff;flex-shrink:0}
.ig-gb-nm{font-weight:800;flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ig-gb-vl{font-family:'JetBrains Mono',monospace;font-weight:900;font-size:13px}
.ig-gb-tr{height:18px;border-radius:9px;background:rgba(255,255,255,.05);overflow:hidden;box-shadow:inset 0 1px 3px rgba(0,0,0,.3)}
.ig-gb-fl{height:100%;border-radius:9px;transition:width 1s cubic-bezier(.2,.8,.2,1);display:flex;align-items:center;justify-content:flex-end;padding-right:9px;font-size:10px;font-weight:900;color:rgba(255,255,255,.95);text-shadow:0 1px 2px rgba(0,0,0,.35);min-width:34px;box-shadow:0 2px 8px rgba(0,0,0,.25)}
.ig-ins{display:flex;gap:10px;overflow-x:auto;padding-bottom:4px;margin-bottom:16px}
.ig-ins-c{display:flex;gap:9px;align-items:center;padding:10px 14px;border-radius:12px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.1);white-space:nowrap;flex-shrink:0;border-left:3px solid var(--ic,#1AADBA)}
.ig-ins-x{font-size:12px;font-weight:600}.ig-ins-s{font-weight:900}
.ig-spin{width:24px;height:24px;border:3px solid rgba(26,173,186,.2);border-top-color:var(--pri2);border-radius:50%;animation:igs .6s linear infinite;display:inline-block}
@keyframes igs{to{transform:rotate(360deg)}}
/* leaderboard cards */
.ig-lead{padding:0;overflow:hidden}
.ig-lead-h{display:flex;align-items:center;gap:9px;padding:12px 15px;border-bottom:1px solid rgba(255,255,255,.08)}
.ig-lead-h .ic{width:33px;height:33px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0}
.ig-lead-h .ti{font-size:11px;font-weight:800;color:var(--m2);text-transform:uppercase;letter-spacing:.4px}
.ig-lead-top{display:flex;align-items:center;gap:11px;padding:13px 15px}
.ig-lead-av{width:44px;height:44px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:900;color:#fff;flex-shrink:0;box-shadow:0 3px 10px rgba(0,0,0,.25)}
.ig-lead-nm{font-size:14.5px;font-weight:900;line-height:1.2;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ig-lead-sub{font-size:10px;color:var(--mu);margin-top:3px}
.ig-lead-big{margin-inline-start:auto;text-align:center}
.ig-lead-big .v{font-size:1.7rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1}
.ig-lead-rest{padding:0 15px 11px}
.ig-lead-row{display:flex;align-items:center;gap:8px;padding:6px 0;font-size:12px;border-top:1px dashed rgba(255,255,255,.07)}
.ig-lead-rk{width:18px;height:18px;border-radius:5px;background:rgba(255,255,255,.06);display:flex;align-items:center;justify-content:center;font-size:10px;font-weight:800;flex-shrink:0}
.ig-lead-rn{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ig-lead-rv{font-family:'JetBrains Mono',monospace;font-weight:800}
.ig-lead-empty{padding:22px 15px;text-align:center;color:var(--mu);font-size:12px}
@media(max-width:1000px){.ig-nums{grid-template-columns:repeat(3,1fr)}.ig-3,.ig-21{grid-template-columns:1fr}}
@media(max-width:560px){.ig-nums{grid-template-columns:repeat(2,1fr)}}
</style>
@endverbatim

<div class="ig">
  <div class="ig-head">
    <div class="ig-head-t"><span style="font-size:22px">📊</span><div><div id="ig-h-title" style="font-size:16px;font-weight:900">لوحة تحليلات كروت العمولة</div><div id="ig-h-sub" style="font-size:11px;color:var(--mu)">نظرة شاملة على جميع الحسابات</div></div></div>
    <div style="flex:1"></div>
    <div class="ig-head-f">
      <span class="ig-fl" id="ig-l-from">من شهر</span>
      <select id="ig-from" class="form-control" onchange="igReload()"><option value="" id="ig-o-from">الأقدم</option></select>
    </div>
    <div class="ig-head-f">
      <span class="ig-fl" id="ig-l-to">إلى شهر</span>
      <select id="ig-to" class="form-control" onchange="igReload()"><option value="" id="ig-o-to">الأحدث</option></select>
    </div>
    <button class="ig-clr" onclick="igClear()" id="ig-clr">↺ <span id="ig-clr-l">الكل</span></button>
  </div>

  <div class="ig-nums">
    <div class="ig-num" style="--ac:#1AADBA"><div class="ig-num-ic">👥</div><div class="ig-num-b"><div class="ig-num-v" id="ig-acc">—</div><div class="ig-num-l" id="ig-l-acc">إجمالي الحسابات</div></div></div>
    <div class="ig-num" style="--ac:#22c97a"><div class="ig-num-ic">💵</div><div class="ig-num-b"><div class="ig-num-v" id="ig-dep">—</div><div class="ig-num-l" id="ig-l-dep">الإيداع الأولي</div></div></div>
    <div class="ig-num" style="--ac:#3a9db5"><div class="ig-num-ic">📈</div><div class="ig-num-b"><div class="ig-num-v" id="ig-mon">—</div><div class="ig-num-l" id="ig-l-mon">الإيداع الشهري</div></div></div>
    <div class="ig-num" style="--ac:#f5a623"><div class="ig-num-ic">💰</div><div class="ig-num-b"><div class="ig-num-v" id="ig-comm">—</div><div class="ig-num-l" id="ig-l-comm">إجمالي العمولات</div></div></div>
    <div class="ig-num" style="--ac:#7b68ee"><div class="ig-num-ic">📞</div><div class="ig-num-b"><div class="ig-num-v" id="ig-cc">—</div><div class="ig-num-l" id="ig-l-cc">كروت CC</div></div></div>
    <div class="ig-num" style="--ac:#e0708a"><div class="ig-num-ic">✏️</div><div class="ig-num-b"><div class="ig-num-v" id="ig-mod">—</div><div class="ig-num-l" id="ig-l-mod">معدّلة</div></div></div>
  </div>

  <div class="ig-ins" id="ig-insights"></div>

  {{-- 🏆 Leaderboards: best broker / marketer / branch by NEW accounts --}}
  <div class="ig-grid ig-3" style="margin-bottom:16px">
    <div class="ig-panel ig-lead" id="ig-lead-broker"></div>
    <div class="ig-panel ig-lead" id="ig-lead-mktr"></div>
    <div class="ig-panel ig-lead" id="ig-lead-branch"></div>
  </div>

  <div class="ig-grid ig-3">
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-gauge">🎯 نسبة الحسابات الجديدة</div>
      <div class="ig-gauge"><canvas id="ig-gauge-c"></canvas><div class="ig-gauge-c"><div class="ig-gauge-v" id="ig-gauge-v" style="color:#22c97a">—</div><div class="ig-gauge-l" id="ig-gauge-l">جديد من الإجمالي</div></div></div>
    </div>
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-comm">💰 توزيع العمولات</div>
      <div class="ig-canvas"><canvas id="ig-comm-c"></canvas></div>
      <div class="ig-leg" id="ig-comm-leg"></div>
    </div>
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-cc">📞 حصة مركز الاتصال</div>
      <div class="ig-canvas"><canvas id="ig-cc-c"></canvas><div class="ig-center"><div class="ig-center-v" id="ig-cc-v" style="color:#7b68ee">—</div><div class="ig-center-l" id="ig-cc-l">من الكروت</div></div></div>
    </div>
  </div>

  <div class="ig-grid ig-21">
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-branch">🏢 الحسابات حسب الفرع</div>
      <div id="ig-branches"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
    </div>
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-monthly">📅 الاتجاه الشهري</div>
      <div id="ig-month-insight" style="font-size:12.5px;font-weight:700;color:var(--tx);background:rgba(26,173,186,.08);border:1px solid rgba(26,173,186,.18);border-radius:10px;padding:9px 12px;margin-bottom:10px;line-height:1.7"></div>
      <div class="ig-canvas" style="height:250px"><canvas id="ig-month-c"></canvas></div>
    </div>
  </div>

  <div class="ig-grid ig-2" style="grid-template-columns:1fr 1fr">
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-modbr">✏️ أكثر الفروع تعديلاً للكروت</div>
      <div id="ig-modbranches"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
    </div>
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-empty">⚠️ حسابات جديدة بدون إيداع</div>
      <div id="ig-emptybranches"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
window.addEventListener('error',function(ev){try{var b=document.getElementById('ig-branches');if(b)b.innerHTML='<div style="color:#e05050;padding:14px;direction:ltr;font-size:12px;white-space:pre-wrap">JS ERROR: '+(ev.message||'')+'\n'+(ev.lineno||'')+'</div>';}catch(e){}});
</script>
<script>
(function(){
  'use strict';
  var T={ar:{lAcc:'إجمالي الحسابات',lDep:'الإيداع الأولي',lMon:'الإيداع الشهري',lComm:'إجمالي العمولات',lCc:'كروت CC',lMod:'معدّلة',
      tGauge:'🎯 نسبة الحسابات الجديدة',gaugeL:'جديد من الإجمالي',tComm:'💰 توزيع العمولات',tCc:'📞 حصة مركز الاتصال',ccL:'من الكروت',tBranch:'🏢 الحسابات حسب الفرع',tMonthly:'📅 الحسابات المفتوحة بالفروع شهرياً',
      broker:'بروكر',marketer:'مسوّق',ext:'خارجي',accounts:'حسابات',deposit:'إيداع (ألف)',noData:'لا توجد بيانات',tb:'لوحة المتابعة',
      hTitle:'لوحة تحليلات كروت العمولة',hSub:'نظرة شاملة على جميع الحسابات',lFrom:'من شهر',lTo:'إلى شهر',oFrom:'الأقدم',oTo:'الأحدث',clr:'الكل',
      tModbr:'✏️ أكثر الفروع تعديلاً للكروت',tEmpty:'⚠️ حسابات فُتحت ولم تودع',mods:'تعديل',perAcc:'لكل حساب',newAcc:'حساب جديد',empty0:'لا توجد تعديلات بعد',noDepLbl:'بدون إيداع',
      bestBroker:'🥇 أفضل بروكر',bestMktr:'🥇 أفضل مسوّق/ة',bestBranch:'🏆 أفضل فرع (حسابات جديدة)',newAccLbl:'حساب جديد'},
    en:{lAcc:'Total Accounts',lDep:'Initial Deposit',lMon:'Monthly Deposit',lComm:'Total Commission',lCc:'CC Cards',lMod:'Modified',
      tGauge:'🎯 New Accounts Ratio',gaugeL:'new of total',tComm:'💰 Commission Split',tCc:'📞 Call Center Share',ccL:'of cards',tBranch:'🏢 Accounts by Branch',tMonthly:'📅 Accounts Opened by Branch (Monthly)',
      broker:'Broker',marketer:'Marketer',ext:'External',accounts:'Accounts',deposit:'Deposit (K)',noData:'No data',tb:'Dashboard',
      hTitle:'Commission Cards Analytics',hSub:'Overview of all accounts',lFrom:'From',lTo:'To',oFrom:'Earliest',oTo:'Latest',clr:'All',
      tModbr:'✏️ Most-Modifying Branches',tEmpty:'⚠️ Opened Without Deposit',mods:'edits',perAcc:'per account',newAcc:'new accounts',empty0:'No modifications yet',noDepLbl:'no deposit',
      bestBroker:'🥇 Best Broker',bestMktr:'🥇 Best Marketer',bestBranch:'🏆 Best Branch (New Accounts)',newAccLbl:'new accounts'}};
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (T[L()]&&T[L()][k])||T.ar[k]||k;}
  function set(id,v){var e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}
  function num(v){return parseFloat(v)||0;}
  function kk(n){n=num(n);return n>=1e6?'$'+(n/1e6).toFixed(1)+'M':n>=1000?'$'+(n/1000).toFixed(0)+'K':'$'+Math.round(n);}
  function E(s){return window.esc?esc(s):String(s);}
  var PAL=['#1AADBA','#22c97a','#f5a623','#7b68ee','#e0708a','#3a9db5','#ff7a45','#26d4e8','#e05050','#4ecdc4','#95b8d1','#a06cd5'];
  // Vivid gradient pairs matching the reference infographic (red→pink, orange→yellow, ...)
  var GRADS=[['#e63946','#ff7a9c'],['#ff7a45','#ffd24c'],['#f1c40f','#22c97a'],['#1AADBA','#4ecdc4'],['#3a9db5','#7b68ee'],['#22c97a','#a8e063'],['#7b68ee','#c084fc'],['#26d4e8','#1AADBA'],['#e05050','#ff7a45'],['#457b9d','#26d4e8']];
  // Render a list of ranked horizontal gradient bars into `el`.
  // items: [{name, value, sub}]  — sub is the small text shown at bar end (e.g. "37%").
  function gradBars(el, items){
    if(!el)return;
    if(!items||!items.length){el.innerHTML='<div style="text-align:center;padding:30px;color:var(--mu)">'+t('noData')+'</div>';return;}
    var max=Math.max.apply(null,items.map(function(i){return num(i.value);}))||1;
    el.innerHTML=items.map(function(it,i){
      var w=Math.max(7,Math.round(num(it.value)/max*100)),g=GRADS[i%GRADS.length];
      return '<div class="ig-gb"><div class="ig-gb-top">'+
        '<span class="ig-gb-rk" style="background:linear-gradient(135deg,'+g[0]+','+g[1]+')">'+(i+1)+'</span>'+
        '<span class="ig-gb-nm">'+E(it.name)+'</span>'+
        '<span class="ig-gb-vl" style="color:'+g[0]+'">'+E(it.value.toLocaleString('en'))+'</span></div>'+
        '<div class="ig-gb-tr"><div class="ig-gb-fl" style="width:'+w+'%;background:linear-gradient(90deg,'+g[0]+','+g[1]+')">'+(it.sub||'')+'</div></div></div>';
    }).join('');
  }
  var ch={},DATA=null,tickC='#7aa0bf',gridC='rgba(255,255,255,.06)';
  function mk(id,cfg){if(ch[id]){ch[id].destroy();delete ch[id];}var e=document.getElementById(id);if(e&&window.Chart)ch[id]=new Chart(e,cfg);}

  function applyLang(){
    set('ig-l-acc',t('lAcc'));set('ig-l-dep',t('lDep'));set('ig-l-mon',t('lMon'));set('ig-l-comm',t('lComm'));set('ig-l-cc',t('lCc'));set('ig-l-mod',t('lMod'));
    set('ig-t-gauge',t('tGauge'));set('ig-gauge-l',t('gaugeL'));set('ig-t-comm',t('tComm'));set('ig-t-cc',t('tCc'));set('ig-cc-l',t('ccL'));set('ig-t-branch',t('tBranch'));set('ig-t-monthly',t('tMonthly'));
    set('ig-h-title',t('hTitle'));set('ig-h-sub',t('hSub'));set('ig-l-from',t('lFrom'));set('ig-l-to',t('lTo'));set('ig-o-from',t('oFrom'));set('ig-o-to',t('oTo'));set('ig-clr-l',t('clr'));set('ig-t-modbr',t('tModbr'));set('ig-t-empty',t('tEmpty'));
    var tb=document.querySelector('.tb-title');if(tb)tb.textContent=t('tb');
    if(DATA)render(DATA);
  }
  var _o=window.applyLang;window.applyLang=function(l){if(_o)_o(l);try{applyLang();}catch(e){console.warn(e);}};

  function igOpts(){
    var now=new Date(),f=document.getElementById('ig-from'),tt=document.getElementById('ig-to');
    for(var i=0;i<36;i++){var d=new Date(now.getFullYear(),now.getMonth()-i,1),m=d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
      if(f){var o=document.createElement('option');o.value=o.textContent=m;f.appendChild(o);}
      if(tt){var o2=document.createElement('option');o2.value=o2.textContent=m;tt.appendChild(o2);}}
  }
  window.igReload=function(){load();};
  window.igClear=function(){var f=document.getElementById('ig-from'),tt=document.getElementById('ig-to');if(f)f.value='';if(tt)tt.value='';load();};

  function render(r){
    var isEn=L()==='en',tot=r.totals||{},bm=r.by_month||[],bb=r.by_branch||[];
    var total=num(tot.count),totComm=num(tot.bc)+num(tot.mc)+num(tot.ec);
    set('ig-acc',(tot.unique||tot.count||0).toLocaleString('en'));set('ig-dep',kk(tot.dep));set('ig-mon',kk(tot.mon));set('ig-comm',kk(totComm));set('ig-cc',(tot.cc||0).toLocaleString('en'));set('ig-mod',(tot.mod||0).toLocaleString('en'));
    var sbc=document.getElementById('sb-cards-count');if(sbc)sbc.textContent=(tot.count||0).toLocaleString('en');
    var sbcc=document.getElementById('sb-cc-count');if(sbcc)sbcc.textContent=(tot.cc||0).toLocaleString('en');
    var sbm=document.getElementById('sb-mod-count');if(sbm)sbm.textContent=(tot.mod||0);
    insights(tot,bb,totComm,isEn);
    renderLeaderboards(r,isEn);

    // GAUGE: new ratio (semicircle)
    var nwsb=num(tot.new)+num(tot.sub),nr=nwsb?Math.round(num(tot.new)/nwsb*100):0;
    set('ig-gauge-v',nr+'%');
    mk('ig-gauge-c',{type:'doughnut',data:{datasets:[{data:[nr,100-nr],backgroundColor:['#22c97a','rgba(255,255,255,.06)'],borderWidth:0}]},
      options:{rotation:-90,circumference:180,cutout:'74%',plugins:{legend:{display:false},tooltip:{enabled:false}},animation:{animateRotate:true}}});

    // COMMISSION donut + legend
    var cd=[num(tot.bc),num(tot.mc),num(tot.ec)],cc=['#1AADBA','#22c97a','#7b68ee'],cn=[t('broker'),t('marketer'),t('ext')];
    mk('ig-comm-c',{type:'doughnut',data:{labels:cn,datasets:[{data:cd,backgroundColor:cc.map(function(x){return x+'dd';}),borderWidth:3,borderColor:'rgba(15,25,40,.6)'}]},
      options:{responsive:true,maintainAspectRatio:false,cutout:'62%',plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return ' '+kk(c.raw);}}}}}});
    var leg=document.getElementById('ig-comm-leg');if(leg){leg.innerHTML=cd.map(function(v,i){var p=totComm?Math.round(v/totComm*100):0;return '<div class="ig-leg-i"><span class="ig-leg-d" style="background:'+cc[i]+'"></span><span class="ig-leg-n">'+cn[i]+' <b style="color:'+cc[i]+'">'+p+'%</b></span><span class="ig-leg-v">'+kk(v)+'</span></div>';}).join('');}

    // CC ring (center %)
    var ccp=total?Math.round(num(tot.cc)/total*100):0;set('ig-cc-v',ccp+'%');
    mk('ig-cc-c',{type:'doughnut',data:{datasets:[{data:[ccp,100-ccp],backgroundColor:['#7b68ee','rgba(255,255,255,.06)'],borderWidth:0}]},
      options:{responsive:true,maintainAspectRatio:false,cutout:'76%',plugins:{legend:{display:false},tooltip:{enabled:false}}}});

    // BRANCHES — horizontal gradient bars (infographic style)
    var brItems=bb.slice().sort(function(a,b){return num(b.cnt)-num(a.cnt);}).map(function(b){
      var c=num(b.cnt),p=total?Math.round(c/total*100):0;
      return {name:isEn?(b.nen||b.nar||'—'):(b.nar||'—'),value:c,sub:p+'%'};
    });
    gradBars(document.getElementById('ig-branches'),brItems);

    // MONTHLY — TOTAL accounts opened, stacked BY BRANCH across months
    var bbm=r.by_branch_month||[];
    var months=[]; (r.by_month||[]).forEach(function(m){ if(months.indexOf(m.month)<0)months.push(m.month); });
    var brName=function(x){return isEn?(x.nen||x.nar):(x.nar||x.nen);};
    var brNames=[]; bbm.forEach(function(x){ var n=brName(x); if(n&&brNames.indexOf(n)<0)brNames.push(n); });
    var totPerMonth=months.map(function(mo){ return bbm.filter(function(x){return x.month===mo;}).reduce(function(s,x){return s+num(x.cnt);},0); });
    // one stacked dataset per branch (value = total accounts opened that month)
    var monthDs=brNames.map(function(bn,i){
      return {label:bn,
        data:months.map(function(mo){ var row=bbm.find(function(x){return brName(x)===bn && x.month===mo;}); return row?num(row.cnt):0; }),
        backgroundColor:PAL[i%PAL.length]+'cc',borderColor:PAL[i%PAL.length],borderWidth:1,borderRadius:3,stack:'s',maxBarThickness:48};
    });
    if(!monthDs.length){ monthDs=[{label:(isEn?'Accounts':'الحسابات'),data:bm.map(function(m){return num(m.cnt);}),backgroundColor:'#1AADBAcc',borderColor:'#1AADBA',borderWidth:1,borderRadius:5,maxBarThickness:48}]; totPerMonth=bm.map(function(m){return num(m.cnt);}); }
    // conclusion
    var insEl=document.getElementById('ig-month-insight');
    if(insEl){
      if(!totPerMonth.length){insEl.innerHTML='';}
      else{
        var grand=totPerMonth.reduce(function(a,b){return a+b;},0),avg=Math.round(grand/totPerMonth.length);
        var maxV=Math.max.apply(null,totPerMonth),maxMo=months[totPerMonth.indexOf(maxV)];
        insEl.innerHTML=(isEn
          ? ('Total opened: <b style="color:#22C4D4">'+grand.toLocaleString('en')+'</b> · Peak month <b>'+maxMo+'</b> ('+maxV.toLocaleString('en')+') · Avg <b>'+avg.toLocaleString('en')+'</b>/mo · '+brNames.length+' branches')
          : ('إجمالي الحسابات المفتوحة: <b style="color:#22C4D4">'+grand.toLocaleString('en')+'</b> · أعلى شهر <b>'+maxMo+'</b> ('+maxV.toLocaleString('en')+') · المتوسط <b>'+avg.toLocaleString('en')+'</b>/شهر · '+brNames.length+' فروع'));
      }
    }
    // total label on top of each stacked column
    var totPlugin={id:'totlbl',afterDatasetsDraw:function(c){try{var ctx=c.ctx;ctx.save();ctx.font='800 10px "JetBrains Mono",monospace';ctx.fillStyle='#cfe8ee';ctx.textAlign='center';
      months.forEach(function(mo,i){var v=totPerMonth[i];if(!v)return;var x=c.scales.x.getPixelForValue(i),y=c.scales.y.getPixelForValue(v);ctx.fillText(v.toLocaleString('en'),x,y-5);});
      ctx.restore();}catch(e){}}};
    mk('ig-month-c',{
      type:'bar',
      data:{labels:months,datasets:monthDs},
      options:{responsive:true,maintainAspectRatio:false,
        plugins:{legend:{display:brNames.length>1&&brNames.length<=8,position:'bottom',labels:{color:tickC,font:{size:9},boxWidth:10,padding:6}},
          tooltip:{padding:9,callbacks:{label:function(ctx){return ' '+ctx.dataset.label+': '+ctx.formattedValue;},
            footer:function(items){var s=0;items.forEach(function(it){s+=(it.parsed.y||0);});return (isEn?'Total: ':'الإجمالي: ')+s.toLocaleString('en');}}}},
        scales:{x:{stacked:true,ticks:{color:tickC,font:{size:9},maxRotation:45},grid:{display:false}},
          y:{stacked:true,ticks:{color:tickC,font:{size:9},precision:0},grid:{color:gridC},beginAtZero:true,suggestedMax:Math.max.apply(null,totPerMonth.concat([1]))*1.15}}},
      plugins:[totPlugin]
    });

    // MOST-MODIFYING branches — horizontal gradient bars
    var modb=bb.filter(function(b){return num(b.modc)>0;}).slice().sort(function(a,b){return num(b.modc)-num(a.modc);});
    var modEl=document.getElementById('ig-modbranches');
    if(modb.length){
      gradBars(modEl,modb.map(function(b){var m=num(b.modc),c=num(b.cnt),p=c?Math.round(m/c*100):0;
        return {name:isEn?(b.nen||b.nar||'—'):(b.nar||'—'),value:m,sub:p+'%'};}));
    }else{
      modEl.innerHTML='<div style="text-align:center;padding:30px;color:var(--mu)">'+t('empty0')+'</div>';
    }

    // ACCOUNTS OPENED WITHOUT DEPOSIT (exact count from summary)
    var emp=bb.filter(function(b){return num(b.ndc)>0;}).slice().sort(function(a,b){return num(b.ndc)-num(a.ndc);});
    document.getElementById('ig-emptybranches').innerHTML=emp.length?emp.map(function(b){var nd=num(b.ndc),cnt=num(b.cnt),pct=cnt?Math.round(nd/cnt*100):0,col=pct>30?'#e05050':pct>10?'#f5a623':'#22c97a';
      return '<div style="display:flex;align-items:center;gap:10px;padding:9px 4px;border-bottom:1px solid var(--brd1)"><span style="width:9px;height:9px;border-radius:50%;background:'+col+';flex-shrink:0"></span><span style="font-weight:700;flex:1">'+E(isEn?(b.nen||b.nar||'—'):(b.nar||'—'))+'</span><span style="font-family:monospace;font-weight:900;color:'+col+'">'+nd.toLocaleString('en')+'</span><span style="font-size:10px;color:var(--mu)">'+t('noDepLbl')+' ('+pct+'%)</span></div>';
    }).join(''):'<div style="text-align:center;padding:30px;color:var(--mu)">'+t('noData')+'</div>';
  }

  // 🏆 Leaderboards: best broker / marketer / branch (by new accounts)
  function leadCard(el,o){
    if(!el)return;
    var hdr='<div class="ig-lead-h"><span class="ic" style="background:'+o.color+'">'+o.icon+'</span><span class="ti">'+o.title+'</span></div>';
    var items=(o.items||[]).filter(function(x){return x&&x.name&&num(x.value)>0;});
    if(!items.length){el.innerHTML=hdr+'<div class="ig-lead-empty">'+t('noData')+'</div>';return;}
    var top=items[0],rest=items.slice(1);
    el.innerHTML=hdr+
      '<div class="ig-lead-top"><div class="ig-lead-av" style="background:'+o.color+'">'+E((top.name||'?').charAt(0).toUpperCase())+'</div>'+
      '<div style="min-width:0;flex:1"><div class="ig-lead-nm">🥇 '+E(top.name)+'</div><div class="ig-lead-sub">'+o.label+'</div></div>'+
      '<div class="ig-lead-big"><div class="v" style="color:'+o.color+'">'+num(top.value).toLocaleString('en')+'</div></div></div>'+
      (rest.length?'<div class="ig-lead-rest">'+rest.map(function(r2,i){return '<div class="ig-lead-row"><span class="ig-lead-rk">'+(i+2)+'</span><span class="ig-lead-rn">'+E(r2.name)+'</span><span class="ig-lead-rv" style="color:'+o.color+'">'+num(r2.value).toLocaleString('en')+'</span></div>';}).join('')+'</div>':'');
  }
  function renderLeaderboards(r,isEn){
    var tb=r.top_brokers||[],tm=r.top_marketers||[],bb=r.by_branch||[];
    var branchItems=bb.slice().sort(function(a,b){return num(b.newc)-num(a.newc);}).slice(0,3).map(function(b){return {name:isEn?(b.nen||b.nar):(b.nar||b.nen),value:num(b.newc)};});
    leadCard(document.getElementById('ig-lead-broker'),{icon:'🧑‍💼',color:'#1AADBA',title:t('bestBroker'),label:t('newAccLbl'),items:tb.map(function(x){return {name:x.nm,value:num(x.newc)};})});
    leadCard(document.getElementById('ig-lead-mktr'),  {icon:'📢',color:'#7b68ee',title:t('bestMktr'),  label:t('newAccLbl'),items:tm.map(function(x){return {name:x.nm,value:num(x.newc)};})});
    leadCard(document.getElementById('ig-lead-branch'),{icon:'🏢',color:'#22c97a',title:t('bestBranch'),label:t('newAccLbl'),items:branchItems});
  }

  function insights(tot,bb,totComm,isEn){
    var box=document.getElementById('ig-insights');if(!box)return;var total=num(tot.count);if(!total){box.innerHTML='';return;}
    var ins=[],S=function(v){return '<span class="ig-ins-s">'+v+'</span>';};
    if(bb.length){var b=bb[0],p=Math.round(num(b.cnt)/total*100);ins.push({c:'#1AADBA',i:'🏢',x:isEn?'Branch '+S(b.nen||b.nar)+' leads '+S(p+'%'):'فرع '+S(b.nar||b.nen)+' يتصدّر '+S(p+'%')});}
    if(num(tot.new)+num(tot.sub)>0){var np=Math.round(num(tot.new)/(num(tot.new)+num(tot.sub))*100);ins.push({c:'#22c97a',i:'📈',x:isEn?S(np+'%')+' new vs '+(100-np)+'% sub':S(np+'%')+' جديدة مقابل '+(100-np)+'% فرعية'});}
    if(totComm>0){var ra=num(tot.dep)/totComm;ins.push({c:'#f5a623',i:'💵',x:isEn?'$1 → '+S('$'+Math.round(ra).toLocaleString('en'))+' deposits':'كل $1 → '+S('$'+Math.round(ra).toLocaleString('en'))+' إيداعات'});}
    if(num(tot.cc)>0){var cp=Math.round(num(tot.cc)/total*100);ins.push({c:'#7b68ee',i:'📞',x:isEn?S(cp+'%')+' via CC':S(cp+'%')+' عبر CC'});}
    if(num(tot.mod)>0){var mp=Math.round(num(tot.mod)/total*100);ins.push({c:'#e0708a',i:'✏️',x:isEn?S(tot.mod)+' modified ('+mp+'%)':S(tot.mod)+' معدّل ('+mp+'%)'});}
    box.innerHTML=ins.map(function(o){return '<div class="ig-ins-c" style="--ic:'+o.c+'"><span style="font-size:20px">'+o.i+'</span><span class="ig-ins-x">'+o.x+'</span></div>';}).join('');
  }

  function fail(m){document.getElementById('ig-branches').innerHTML='<div style="padding:14px;color:var(--re)">⚠️ '+t('noData')+'<pre style="font-size:11px;color:var(--mu);white-space:pre-wrap;direction:ltr;background:rgba(0,0,0,.2);padding:8px;border-radius:8px;margin-top:8px">'+E(m||'')+'</pre></div>';}
  function load(tries){
    tries=tries||0;
    var f=(document.getElementById('ig-from')||{}).value||'',tt=(document.getElementById('ig-to')||{}).value||'';
    var qs=[];if(f)qs.push('month_from='+encodeURIComponent(f));if(tt)qs.push('month_to='+encodeURIComponent(tt));
    var box=document.getElementById('ig-branches');
    if(box&&tries===0)box.innerHTML='<div style="padding:14px;color:var(--mu)">⏳ '+(L()==='en'?'Loading…':'جارٍ التحميل…')+'</div>';
    api('GET','/cards/summary'+(qs.length?('?'+qs.join('&')):'')).then(function(r){
      if(r&&r.success){DATA=r;render(r);return;}
      // Transient connection reset (shared-hosting throttle) → retry up to 3x with backoff
      if(tries<3){setTimeout(function(){load(tries+1);},600*(tries+1));return;}
      fail('summary success='+(r&&r.success)+' '+((r&&r.message)||''));
    }).catch(function(e){
      if(tries<3){setTimeout(function(){load(tries+1);},600*(tries+1));return;}
      fail('EX '+(e&&e.message||e));
    });
  }
  igOpts();applyLang();load();
})();
</script>
@endpush
