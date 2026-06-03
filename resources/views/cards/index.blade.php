@extends('layouts.app')
@section('title','Commission Cards')
@section('page-title','Commission Cards')

@section('content')
@verbatim
<style>
.cc{max-width:100%;margin:0 auto}
/* top bar */
.cc-top{display:flex;gap:10px;align-items:center;flex-wrap:wrap;margin-bottom:12px}
.cc-tabs{display:flex;gap:6px;flex-wrap:wrap}
.cc-tab{display:flex;align-items:center;gap:6px;padding:8px 14px;border-radius:10px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);transition:all .15s;white-space:nowrap}
.cc-tab:hover{border-color:var(--pri);color:var(--pri2)}
.cc-tab.on{background:rgba(26,173,186,.14);border-color:rgba(26,173,186,.4);color:var(--pri2)}
.cc-new{display:flex;align-items:center;gap:7px;padding:9px 18px;border-radius:10px;font-size:13px;font-weight:800;text-decoration:none;background:linear-gradient(135deg,#22c97a,#179c5d);color:#fff;box-shadow:0 3px 12px rgba(34,201,122,.3);transition:all .15s}
.cc-new:hover{transform:translateY(-1px);box-shadow:0 5px 18px rgba(34,201,122,.45);color:#fff}
.cc-scope{font-size:11px;color:var(--mu);background:var(--bg3);padding:5px 12px;border-radius:20px;border:1px solid var(--brd1)}
/* kpi chips — compact */
.cc-kpis{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:12px}
.cc-chip{display:flex;align-items:center;gap:8px;padding:8px 14px;border-radius:11px;background:var(--card-bg);border:1px solid var(--card-brd);flex:1;min-width:120px}
.cc-chip-i{width:30px;height:30px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:15px;flex-shrink:0}
.cc-chip-v{font-size:1.05rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1}
.cc-chip-l{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-top:2px}
/* toolbar */
.cc-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;padding:12px 14px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;margin-bottom:10px}
.cc-fg{display:flex;flex-direction:column;gap:3px}
.cc-fl{font-size:9px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.4px}
/* insights — compact strip */
.cc-ins{display:flex;gap:8px;overflow-x:auto;padding-bottom:4px;margin-bottom:10px}
.cc-ins-c{display:flex;gap:9px;align-items:center;padding:9px 13px;border-radius:11px;background:var(--card-bg);border:1px solid var(--card-brd);white-space:nowrap;flex-shrink:0;border-left:3px solid var(--ic,#3a9db5)}
.cc-ins-x{font-size:12px;font-weight:600;color:var(--tx)}
.cc-ins-s{font-weight:900}
/* TABLE — the hero */
.cc-wrap{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;overflow:hidden}
.cc-scroll{overflow:auto;max-height:calc(100vh - 360px);min-height:300px}
.cc-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:12px}
.cc-tbl thead th{position:sticky;top:0;z-index:3;background:var(--bg3);padding:10px 11px;font-size:10px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;white-space:nowrap;border-bottom:2px solid var(--pri);cursor:pointer;user-select:none;transition:background .12s}
.cc-tbl thead th:hover{background:rgba(26,173,186,.12)}
.cc-tbl thead th .ar{opacity:.3;font-size:9px;margin-right:3px}
.cc-tbl thead th.srt .ar{opacity:1;color:var(--pri2)}
.cc-tbl tbody td{padding:9px 11px;border-bottom:1px solid var(--brd1)}
.cc-tbl tbody tr:nth-child(even) td{background:rgba(255,255,255,.015)}
.cc-tbl tbody tr:hover td{background:rgba(26,173,186,.07)}
.cc-r-cc td:first-child{border-right:3px solid #7b68ee}
.cc-r-mod td:first-child{border-right:3px solid #f5a623}
.cc-r-new td:first-child{border-right:3px solid #22c97a}
.cc-tbl tfoot td{position:sticky;bottom:0;z-index:3;background:var(--bg3);padding:11px;font-weight:900;border-top:2px solid var(--pri);font-size:12px;font-family:'JetBrains Mono',monospace;white-space:nowrap}
.cc-tf-l{font-family:'Tajawal',sans-serif;color:var(--pri2);font-size:11px}
.acn{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--pri2)}
.chip{display:inline-flex;align-items:center;padding:2px 8px;border-radius:20px;font-size:9px;font-weight:700;white-space:nowrap}
.chip-new{background:rgba(34,201,122,.14);color:#22c97a}.chip-sub{background:rgba(58,157,181,.14);color:#3a9db5}
.chip-mod{background:rgba(245,166,35,.14);color:#f5a623}.chip-act{background:rgba(46,134,171,.14);color:#3a9db5}.chip-cc{background:rgba(123,104,238,.14);color:#7b68ee}
.cl{color:#22c97a}.cm{color:#f5a623}.ch{color:#e05050}
.cc-eb{display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;background:var(--bg3);border:1px solid var(--brd1);text-decoration:none;font-size:12px;transition:all .12s}
.cc-eb:hover{background:rgba(245,166,35,.15);border-color:rgba(245,166,35,.4)}
.cc-spin{width:22px;height:22px;border:3px solid rgba(26,173,186,.2);border-top-color:var(--pri2);border-radius:50%;animation:ccsp .6s linear infinite;display:inline-block}
@keyframes ccsp{to{transform:rotate(360deg)}}
</style>
@endverbatim

<div class="cc">
  {{-- Top bar --}}
  <div class="cc-top">
    <div class="cc-tabs">
      <a href="{{ route('cards.index') }}" class="cc-tab on">🗂 <span id="cc-n-all">كل الحسابات</span></a>
      <a href="{{ route('cards.modified') }}" class="cc-tab">✏️ <span id="cc-n-mod">المعدّلة</span></a>
      <a href="{{ route('cards.tree') }}" class="cc-tab">🌳 <span id="cc-n-tree">الشجرة</span></a>
    </div>
    <div style="flex:1"></div>
    <span class="cc-scope" id="cc-scope"></span>
    <button class="btn btn-ghost btn-sm" onclick="ccExcel()" style="border-color:rgba(34,201,122,.3);color:#22c97a">📗 Excel</button>
    <a href="{{ route('cards.create') }}" class="cc-new">➕ <span id="cc-n-new">كرت جديد</span></a>
  </div>

  {{-- KPI chips --}}
  <div class="cc-kpis">
    <div class="cc-chip"><div class="cc-chip-i" style="background:rgba(26,173,186,.14)">🗂</div><div><div class="cc-chip-v" style="color:var(--pri2)" id="cc-k-total">—</div><div class="cc-chip-l" id="cc-k-total-l">إجمالي الكروت</div></div></div>
    <div class="cc-chip"><div class="cc-chip-i" style="background:rgba(34,201,122,.14)">🟢</div><div><div class="cc-chip-v" style="color:#22c97a" id="cc-k-new">—</div><div class="cc-chip-l" id="cc-k-new-l">جديد</div></div></div>
    <div class="cc-chip"><div class="cc-chip-i" style="background:rgba(58,157,181,.14)">🔵</div><div><div class="cc-chip-v" style="color:#3a9db5" id="cc-k-sub">—</div><div class="cc-chip-l" id="cc-k-sub-l">فرعي</div></div></div>
    <div class="cc-chip"><div class="cc-chip-i" style="background:rgba(245,166,35,.14)">✏️</div><div><div class="cc-chip-v" style="color:#f5a623" id="cc-k-mod">—</div><div class="cc-chip-l" id="cc-k-mod-l">معدّلة</div></div></div>
    <div class="cc-chip"><div class="cc-chip-i" style="background:rgba(123,104,238,.14)">📞</div><div><div class="cc-chip-v" style="color:#7b68ee" id="cc-k-cc">—</div><div class="cc-chip-l" id="cc-k-cc-l">من CC</div></div></div>
    <div class="cc-chip"><div class="cc-chip-i" style="background:rgba(34,201,122,.14)">💵</div><div><div class="cc-chip-v" style="color:#22c97a" id="cc-k-dep">—</div><div class="cc-chip-l" id="cc-k-dep-l">إجمالي الإيداع</div></div></div>
  </div>

  {{-- Insights strip --}}
  <div class="cc-ins" id="cc-insights"></div>

  {{-- Smart Filter Bar --}}
  <div class="cc-bar" style="gap:10px;padding:14px 16px;border-radius:14px">
    {{-- Search — full width row on top --}}
    <div style="width:100%;display:flex;align-items:center;gap:8px;background:var(--bg4);border:1.5px solid var(--brd2);border-radius:11px;padding:0 14px;transition:border-color .2s" id="cc-search-wrap">
      <span style="font-size:16px;opacity:.5">🔍</span>
      <input type="text" id="cc-search" style="flex:1;background:none;border:none;outline:none;font-family:'Tajawal',sans-serif;font-size:15px;font-weight:500;color:var(--tx);padding:10px 4px" placeholder="ابحث برقم الحساب أو اسم البروكر أو المسوّق..." oninput="ccDeb()">
      <button onclick="ccClear()" id="cc-clear" style="background:none;border:none;cursor:pointer;font-size:13px;color:var(--mu);padding:4px 6px;border-radius:6px;font-family:'Tajawal',sans-serif" title="مسح البحث">
        <span id="cc-clear-l">✕ مسح</span>
      </button>
      <span id="cc-loading" style="display:none"><span class="cc-spin"></span></span>
    </div>
    {{-- Filters row --}}
    <div style="width:100%;display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end">
      <div class="cc-fg" style="flex:1;min-width:110px">
        <span class="cc-fl" id="cc-fl-month" style="font-size:11px;font-weight:700;color:var(--m2);margin-bottom:5px;display:block">الشهر</span>
        <select id="cc-month" class="form-control" style="font-size:13px" onchange="ccApply()"><option value="" id="cc-o-allm">كل الشهور</option></select>
      </div>
      <div class="cc-fg" style="flex:1.5;min-width:130px">
        <span class="cc-fl" id="cc-fl-broker" style="font-size:11px;font-weight:700;color:var(--m2);margin-bottom:5px;display:block">البروكر</span>
        <select id="cc-broker" class="form-control" style="font-size:13px" onchange="ccApply()"><option value="" id="cc-o-allb">كل البروكرات</option></select>
      </div>
      <div class="cc-fg" style="min-width:100px">
        <span class="cc-fl" id="cc-fl-kind" style="font-size:11px;font-weight:700;color:var(--m2);margin-bottom:5px;display:block">النوع</span>
        <select id="cc-kind" class="form-control" style="font-size:13px" onchange="ccApply()">
          <option value="" id="cc-o-allk">الكل</option>
          <option value="new" id="cc-o-kn">🟢 جديد</option>
          <option value="sub" id="cc-o-ks">🔵 فرعي</option>
        </select>
      </div>
      <div class="cc-fg" style="min-width:100px">
        <span class="cc-fl" id="cc-fl-status" style="font-size:11px;font-weight:700;color:var(--m2);margin-bottom:5px;display:block">الحالة</span>
        <select id="cc-status" class="form-control" style="font-size:13px" onchange="ccApply()">
          <option value="" id="cc-o-alls">الكل</option>
          <option value="active" id="cc-o-sa">عادي</option>
          <option value="modified" id="cc-o-sm">✏️ معدّل</option>
          <option value="new_added" id="cc-o-sn">🆕 مضاف</option>
        </select>
      </div>
      <div class="cc-fg" style="min-width:100px">
        <span class="cc-fl" id="cc-fl-source" style="font-size:11px;font-weight:700;color:var(--m2);margin-bottom:5px;display:block">المصدر</span>
        <select id="cc-source" class="form-control" style="font-size:13px" onchange="ccApply()">
          <option value="" id="cc-o-allsr">الكل</option>
          <option value="regular" id="cc-o-reg">عادي</option>
          <option value="cc" id="cc-o-cc">📞 CC</option>
        </select>
      </div>
      <span id="cc-count" style="font-size:12px;color:var(--mu);align-self:center;white-space:nowrap;margin-right:auto;padding:6px 0"></span>
    </div>
  </div>

  {{-- THE TABLE --}}
  <div class="cc-wrap">
    <div class="cc-scroll">
      <table class="cc-tbl" id="cc-tbl">
        <thead><tr id="cc-head"></tr></thead>
        <tbody id="cc-body"><tr><td colspan="15" style="text-align:center;padding:60px"><span class="cc-spin"></span></td></tr></tbody>
        <tfoot id="cc-foot" style="display:none"></tfoot>
      </table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const CC={
  ar:{nAll:'كل الحسابات',nMod:'المعدّلة',nSearch:'بحث',nTree:'الشجرة',nNew:'كرت جديد',
    kTotal:'إجمالي الكروت',kNew:'جديد',kSub:'فرعي',kMod:'معدّلة',kCc:'من CC',kDep:'إجمالي الإيداع',
    flSearch:'بحث',flMonth:'الشهر',flBroker:'البروكر',flKind:'النوع',flStatus:'الحالة',flSource:'المصدر',
    allM:'كل الشهور',allB:'كل البروكرات',allK:'الكل',kn:'🟢 جديد',ks:'🔵 فرعي',allS:'الكل',sa:'عادي',sm:'معدّل',sn:'مضاف',allSr:'الكل',reg:'عادي',cc:'📞 CC',
    clear:'مسح',search:'رقم حساب / بروكر / مسوّق...',
    thAc:'الحساب',thMonth:'الشهر',thBranch:'الفرع',thBroker:'البروكر',thBC:'ع.بروكر',thMk:'المسوّق',thMC:'ع.مسوّق',thTot:'إجمالي ع.',thDep:'إيداع أولي',thMon:'إيداع شهري',thKind:'النوع',thStatus:'الحالة',thSrc:'المصدر',thAct:'',
    cNew:'🟢 جديد',cSub:'🔵 فرعي',sMod:'✏️ معدّل',sNew:'🆕 مضاف',sAct:'✅ عادي',regular:'عادي',ccL:'📞 CC',
    showing:'{n} من {t} كرت',empty:'لا توجد كروت مطابقة',loading:'جاري التحميل...',err:'تعذّر تحميل البيانات',retry:'إعادة المحاولة',
    tfTotal:'الإجمالي',tfCards:'كرت',tfAvg:'متوسط',
    tagKey:'استنتاج',tagPos:'إيجابي',tagWarn:'انتباه',tagInfo:'معلومة',
    noExport:'لا توجد بيانات',done:'تم ✅',tb:'كروت العمولات',
  },
  en:{nAll:'All Accounts',nMod:'Modified',nSearch:'Search',nTree:'Tree',nNew:'New Card',
    kTotal:'Total Cards',kNew:'New',kSub:'Sub',kMod:'Modified',kCc:'From CC',kDep:'Total Deposit',
    flSearch:'Search',flMonth:'Month',flBroker:'Broker',flKind:'Kind',flStatus:'Status',flSource:'Source',
    allM:'All Months',allB:'All Brokers',allK:'All',kn:'🟢 New',ks:'🔵 Sub',allS:'All',sa:'Active',sm:'Modified',sn:'New Added',allSr:'All',reg:'Regular',cc:'📞 CC',
    clear:'Clear',search:'Account # / broker / marketer...',
    thAc:'Account',thMonth:'Month',thBranch:'Branch',thBroker:'Broker',thBC:'B.Comm',thMk:'Marketer',thMC:'M.Comm',thTot:'Total C.',thDep:'Initial',thMon:'Monthly',thKind:'Kind',thStatus:'Status',thSrc:'Source',thAct:'',
    cNew:'🟢 New',cSub:'🔵 Sub',sMod:'✏️ Modified',sNew:'🆕 New',sAct:'✅ Active',regular:'Regular',ccL:'📞 CC',
    showing:'{n} of {t} cards',empty:'No matching cards',loading:'Loading...',err:'Failed to load data',retry:'Retry',
    tfTotal:'TOTAL',tfCards:'cards',tfAvg:'avg',
    tagKey:'Insight',tagPos:'Positive',tagWarn:'Attention',tagInfo:'Info',
    noExport:'No data',done:'Done ✅',tb:'Commission Cards',
  }
};
function ccL(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
function tr(k){return CC[ccL()]?.[k]??CC.ar[k]??k;}
function _t(id,v){const e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}

const COLS=[['account_number','thAc'],['month','thMonth'],['branch','thBranch'],['broker','thBroker'],
  ['broker_commission','thBC'],['marketer','thMk'],['marketer_commission','thMC'],['total','thTot'],
  ['initial_deposit','thDep'],['monthly_deposit','thMon'],['account_kind','thKind'],['status','thStatus'],['source','thSrc'],['act','thAct']];
let _all=[],_view=[],_sk='month',_sd='desc',_timer=null,_optsLoaded=false;

function ccApplyLang(){
  _t('cc-n-all',tr('nAll'));_t('cc-n-mod',tr('nMod'));_t('cc-n-search',tr('nSearch'));_t('cc-n-tree',tr('nTree'));_t('cc-n-new',tr('nNew'));
  _t('cc-k-total-l',tr('kTotal'));_t('cc-k-new-l',tr('kNew'));_t('cc-k-sub-l',tr('kSub'));_t('cc-k-mod-l',tr('kMod'));_t('cc-k-cc-l',tr('kCc'));_t('cc-k-dep-l',tr('kDep'));
  _t('cc-fl-search',tr('flSearch'));_t('cc-fl-month',tr('flMonth'));_t('cc-fl-broker',tr('flBroker'));_t('cc-fl-kind',tr('flKind'));_t('cc-fl-status',tr('flStatus'));_t('cc-fl-source',tr('flSource'));
  _t('cc-o-allm',tr('allM'));_t('cc-o-allb',tr('allB'));_t('cc-o-allk',tr('allK'));_t('cc-o-kn',tr('kn'));_t('cc-o-ks',tr('ks'));_t('cc-o-alls',tr('allS'));_t('cc-o-sa',tr('sa'));_t('cc-o-sm',tr('sm'));_t('cc-o-sn',tr('sn'));_t('cc-o-allsr',tr('allSr'));_t('cc-o-reg',tr('reg'));_t('cc-o-cc',tr('cc'));
  _t('cc-clear-l',tr('clear'));
  const se=document.getElementById('cc-search');if(se)se.placeholder=tr('search');
  const t=document.querySelector('.tb-title');if(t)t.textContent=tr('tb');
  ccHead();if(_all.length)ccRender();
}
const _ccOrig=window.applyLang;window.applyLang=function(l){if(_ccOrig)_ccOrig(l);try{ccApplyLang();}catch(e){console.warn(e);}};

function ccHead(){
  const h=document.getElementById('cc-head');if(!h)return;
  h.innerHTML='<th style="width:28px;cursor:default">#</th>'+COLS.map(([k,l])=>{
    if(k==='act')return `<th style="cursor:default">${tr(l)}</th>`;
    const s=_sk===k,a=s?(_sd==='asc'?'▲':'▼'):'⇅';
    return `<th class="${s?'srt':''}" onclick="ccSort('${k}')">${tr(l)} <span class="ar">${a}</span></th>`;
  }).join('');
}
function ccSort(k){if(_sk===k)_sd=_sd==='asc'?'desc':'asc';else{_sk=k;_sd='asc';}ccHead();ccRender();}
function ccDeb(){clearTimeout(_timer);_timer=setTimeout(ccRender,200);}
function ccApply(){ccRender();}
function ccClear(){['cc-month','cc-broker','cc-kind','cc-status','cc-source'].forEach(id=>{const e=document.getElementById(id);if(e)e.value='';});const s=document.getElementById('cc-search');if(s)s.value='';ccRender();}

function tComm(c){return (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0));}
function ccol(v){v=parseFloat(v)||0;return v>5?'ch':v>2?'cm':'cl';}
function fmtK(n){const v=parseFloat(n)||0;return v>=1e6?'$'+(v/1e6).toFixed(1)+'M':v>=1000?'$'+(v/1000).toFixed(0)+'K':'$'+v.toFixed(0);}
function fmtF(n){return '$'+Math.round(n||0).toLocaleString('en');}

async function ccLoad(){
  document.getElementById('cc-loading').style.display='';
  let rows=null,scope='',dbg='';
  /* PRIMARY: the proven endpoint the (working) reports page uses */
  try{
    const r=await api('GET','/cards/report?per_page=10000');
    dbg='report success='+(r&&r.success)+' data='+(r&&Array.isArray(r.data)?r.data.length:typeof(r&&r.data))+(r&&r.message?(' msg='+r.message):'');
    if(r&&r.success&&Array.isArray(r.data)){rows=r.data;scope=r.branch_scope||'';}
  }catch(e){dbg='report EX '+(e&&e.message||e);}
  /* FALLBACK: plain cards list */
  if(rows===null){
    try{
      const r2=await api('GET','/cards?per_page=10000');
      dbg+=' | cards success='+(r2&&r2.success)+' data='+((r2&&r2.data&&Array.isArray(r2.data.data))?r2.data.data.length:'?');
      if(r2&&r2.success){rows=r2.data?.data||r2.data||[];scope=r2.branch_scope||scope;}
    }catch(e){dbg+=' | cards EX '+(e&&e.message||e);}
  }
  document.getElementById('cc-loading').style.display='none';
  if(rows===null){
    document.getElementById('cc-body').innerHTML=`<tr><td colspan="15" style="padding:24px;color:var(--re)"><div style="font-size:26px;text-align:center">⚠️</div><div style="text-align:center;font-weight:700">${tr('err')}</div><pre style="font-size:11px;color:var(--mu);white-space:pre-wrap;direction:ltr;background:rgba(0,0,0,.2);padding:8px;border-radius:8px;margin-top:8px">${esc(dbg)}</pre><div style="text-align:center"><button class="btn btn-ghost btn-sm" style="margin-top:8px" onclick="ccLoad()">🔄 ${tr('retry')}</button></div></td></tr>`;
    return;
  }
  _all=rows;
  const sc=document.getElementById('cc-scope');if(sc)sc.textContent=scope;
  if(!_optsLoaded){ccOpts(_all);_optsLoaded=true;}
  ccRender();
}

function ccRender(){
  const isEn=ccL()==='en';
  const q=(document.getElementById('cc-search')?.value||'').toLowerCase().trim();
  const fM=document.getElementById('cc-month')?.value||'',fB=document.getElementById('cc-broker')?.value||'',fK=document.getElementById('cc-kind')?.value||'',fS=document.getElementById('cc-status')?.value||'',fSrc=document.getElementById('cc-source')?.value||'';
  let d=_all;
  if(fM)d=d.filter(c=>c.month===fM);
  if(fB)d=d.filter(c=>(c.broker?.name||'')===fB);
  if(fK)d=d.filter(c=>c.account_kind===fK);
  if(fS)d=d.filter(c=>c.status===fS);
  if(fSrc==='cc')d=d.filter(c=>!!c.cc_branch_id);else if(fSrc==='regular')d=d.filter(c=>!c.cc_branch_id);
  if(q)d=d.filter(c=>String(c.account_number).includes(q)||(c.broker?.name||'').toLowerCase().includes(q)||(c.marketer?.name||'').toLowerCase().includes(q)||(c.month||'').toLowerCase().includes(q)||(c.branch?.name_ar||'').toLowerCase().includes(q)||(c.branch?.name_en||'').toLowerCase().includes(q));
  d=d.slice();

  /* sort */
  const k=_sk,dir=_sd==='asc'?1:-1;
  const val=c=>{switch(k){case 'branch':return isEn?(c.branch?.name_en||c.branch?.name_ar||''):(c.branch?.name_ar||'');case 'broker':return c.broker?.name||'';case 'marketer':return c.marketer?.name||'';case 'source':return c.cc_branch_id?1:0;case 'total':return tComm(c);case 'month':return c.month_date||c.month||'';case 'account_number':case 'broker_commission':case 'marketer_commission':case 'initial_deposit':case 'monthly_deposit':return parseFloat(c[k])||0;default:return (c[k]??'').toString();}};
  d.sort((a,b)=>{const x=val(a),y=val(b);if(typeof x==='number'&&typeof y==='number')return (x-y)*dir;return String(x).localeCompare(String(y),isEn?'en':'ar')*dir;});
  _view=d;
  const total=d.length;

  /* KPIs + totals (over filtered set) */
  let nN=0,nS=0,nMod=0,nCc=0,tDep=0,tMon=0,tBC=0,tMC=0,tEC=0,tTot=0;
  for(const c of d){
    const bc=parseFloat(c.broker_commission||0),mc=parseFloat(c.marketer_commission||0),ec=parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0);
    tDep+=parseFloat(c.initial_deposit||0);tMon+=parseFloat(c.monthly_deposit||0);tBC+=bc;tMC+=mc;tEC+=ec;tTot+=bc+mc+ec;
    if(c.account_kind==='new')nN++;else if(c.account_kind==='sub')nS++;
    if(c.status==='modified')nMod++;if(c.cc_branch_id)nCc++;
  }
  _t('cc-k-total',total.toLocaleString('en'));_t('cc-k-new',nN.toLocaleString('en'));_t('cc-k-sub',nS.toLocaleString('en'));_t('cc-k-mod',nMod.toLocaleString('en'));_t('cc-k-cc',nCc.toLocaleString('en'));_t('cc-k-dep',fmtK(tDep));
  _t('cc-count',tr('showing').replace('{n}',total.toLocaleString('en')).replace('{t}',_all.length.toLocaleString('en')));

  try{ccInsights({total,nN,nS,nMod,nCc,tDep,tBC,tMC,tEC,tTot},d,isEn);}catch(e){console.warn(e);}

  if(!total){
    document.getElementById('cc-body').innerHTML=`<tr><td colspan="15" style="text-align:center;padding:50px;color:var(--mu)"><div style="font-size:34px;opacity:.3">📭</div><div style="margin-top:8px;font-weight:700">${tr('empty')}</div></td></tr>`;
    document.getElementById('cc-foot').style.display='none';return;
  }
  const sc=s=>({modified:`<span class="chip chip-mod">${tr('sMod')}</span>`,new_added:`<span class="chip chip-new">${tr('sNew')}</span>`,active:`<span class="chip chip-act">${tr('sAct')}</span>`}[s]||`<span class="chip chip-act">${s||''}</span>`);
  const parts=new Array(total);
  for(let i=0;i<total;i++){
    const c=d[i],isCC=!!c.cc_branch_id;
    const bc=parseFloat(c.broker_commission||0),mc=parseFloat(c.marketer_commission||0),t=tComm(c);
    const rc=isCC?'cc-r-cc':c.status==='modified'?'cc-r-mod':c.status==='new_added'?'cc-r-new':'';
    const bn=isEn?(c.branch?.name_en||c.branch?.name_ar||'—'):(c.branch?.name_ar||'—');
    parts[i]=`<tr class="${rc}"><td style="color:var(--mu);font-size:10px">${i+1}</td><td><span class="acn">${esc(String(c.account_number))}</span></td><td style="color:var(--mu);font-size:11px;white-space:nowrap">${esc(c.month||'—')}</td><td style="font-size:11px">${esc(bn)}</td><td style="font-weight:700;color:var(--pri2)">${esc(c.broker?.name||'—')}</td><td><span class="mono ${ccol(bc)}">$${bc.toFixed(1)}</span></td><td style="font-size:11px;color:var(--m2)">${c.marketer?.name&&c.marketer.name!==c.broker?.name?esc(c.marketer.name):'<span style="color:var(--mu)">—</span>'}</td><td><span class="mono ${ccol(mc)}">$${mc.toFixed(1)}</span></td><td><span class="mono ${ccol(t)}" style="font-weight:700">$${t.toFixed(1)}</span></td><td class="mono" style="color:var(--pri2);font-size:11px">${fmtK(c.initial_deposit)}</td><td class="mono" style="color:#22c97a;font-size:11px">${fmtK(c.monthly_deposit)}</td><td><span class="chip ${c.account_kind==='new'?'chip-new':'chip-sub'}">${c.account_kind==='new'?tr('cNew'):tr('cSub')}</span></td><td>${sc(c.status)}</td><td>${isCC?`<span class="chip chip-cc">${tr('ccL')}</span>`:`<span style="font-size:10px;color:var(--mu)">${tr('regular')}</span>`}</td><td><a href="/cards/${c.id}/edit" class="cc-eb" title="${tr('sm')}">✏️</a></td></tr>`;
  }
  document.getElementById('cc-body').innerHTML=parts.join('');

  const avg=total?(tTot/total).toFixed(1):'0';
  document.getElementById('cc-foot').innerHTML=`<tr><td colspan="4"><span class="cc-tf-l">${tr('tfTotal')}:</span> <span style="color:var(--pri2);font-size:15px">${total.toLocaleString('en')}</span> <span class="cc-tf-l">${tr('tfCards')}</span></td><td class="cl">$${Math.round(tBC).toLocaleString('en')}</td><td></td><td class="cl">$${Math.round(tMC).toLocaleString('en')}</td><td style="color:var(--or)">$${Math.round(tTot).toLocaleString('en')}<div style="font-size:9px;color:var(--mu);font-family:Tajawal">${tr('tfAvg')} $${avg}</div></td><td style="color:var(--pri2)">${fmtF(tDep)}</td><td style="color:#22c97a">${fmtF(tMon)}</td><td colspan="4"></td></tr>`;
  document.getElementById('cc-foot').style.display='';
}

function ccInsights(s,d,isEn){
  const box=document.getElementById('cc-insights');if(!box)return;
  if(!s.total){box.innerHTML='';return;}
  const ins=[],S=v=>`<span class="cc-ins-s">${v}</span>`,mk=n=>{const v=parseFloat(n)||0;return v>=1e6?'$'+(v/1e6).toFixed(2)+'M':v>=1000?'$'+(v/1000).toFixed(1)+'K':'$'+Math.round(v);};
  const totComm=s.tBC+s.tMC+s.tEC;
  const bm={};d.forEach(c=>{const n=c.broker?.name;if(n)bm[n]=(bm[n]||0)+1;});
  const tb=Object.entries(bm).sort((a,b)=>b[1]-a[1])[0];
  if(tb){const pct=Math.round(tb[1]/s.total*100);ins.push({c:'#7b68ee',i:'🥇',x:isEn?`Top broker ${S(tb[0])}: ${S(tb[1])} cards (${pct}%)`:`أنشط بروكر ${S(tb[0])}: ${S(tb[1])} كرت (${pct}%)`});}
  ins.push({c:'#3a9db5',i:'💰',x:isEn?`Commission load ${S(mk(totComm))}`:`عبء العمولات ${S(mk(totComm))}`});
  if(totComm>0){const r=s.tDep/totComm;ins.push({c:'#22c97a',i:'💵',x:isEn?`$1 commission → ${S('$'+Math.round(r).toLocaleString('en'))} deposits`:`كل $1 عمولة → ${S('$'+Math.round(r).toLocaleString('en'))} إيداعات`});}
  if(s.nN+s.nS>0){const np=Math.round(s.nN/(s.nN+s.nS)*100);ins.push({c:'#22c97a',i:'📈',x:isEn?`${S(np+'%')} new vs ${100-np}% sub`:`${S(np+'%')} جديدة مقابل ${100-np}% فرعية`});}
  if(s.nMod>0){const mp=Math.round(s.nMod/s.total*100);ins.push({c:mp>20?'#f5a623':'#3a9db5',i:'✏️',x:isEn?`${S(s.nMod)} modified (${mp}%)`:`${S(s.nMod)} معدّل (${mp}%)`});}
  const hi=d.filter(c=>parseFloat(c.broker_commission||0)>5).length;
  if(hi>0){const hp=Math.round(hi/s.total*100);ins.push({c:'#f5a623',i:'⚠️',x:isEn?`${S(hi)} cards >$5 (${hp}%)`:`${S(hi)} كرت >$5 (${hp}%)`});}
  if(s.nCc>0){const cp=Math.round(s.nCc/s.total*100);ins.push({c:'#7b68ee',i:'📞',x:isEn?`${S(cp+'%')} via Call Center`:`${S(cp+'%')} عبر مركز الاتصال`});}
  box.innerHTML=ins.map(o=>`<div class="cc-ins-c" style="--ic:${o.c}"><span style="font-size:18px">${o.i}</span><span class="cc-ins-x">${o.x}</span></div>`).join('');
}

function ccOpts(cards){
  const months=[...new Set(cards.map(c=>c.month).filter(Boolean))].sort((a,b)=>new Date('01 '+b)-new Date('01 '+a));
  const brokers=[...new Set(cards.map(c=>c.broker?.name).filter(Boolean))].sort();
  const m=document.getElementById('cc-month'),b=document.getElementById('cc-broker');
  if(m)months.forEach(x=>{const o=document.createElement('option');o.value=o.textContent=x;m.appendChild(o);});
  if(b)brokers.forEach(x=>{const o=document.createElement('option');o.value=o.textContent=x;b.appendChild(o);});
}

function ccExcel(){
  if(!_view.length){toast(tr('noExport'),'error');return;}
  const isEn=ccL()==='en';
  const hdr=isEn?['#','Account','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Total','Initial','Monthly','Kind','Status','Source']:['#','الحساب','الشهر','الفرع','البروكر','ع.بروكر','المسوّق','ع.مسوّق','إجمالي','إيداع أولي','إيداع شهري','النوع','الحالة','المصدر'];
  const rows=[hdr,..._view.map((c,i)=>[i+1,c.account_number,c.month,isEn?(c.branch?.name_en||c.branch?.name_ar||''):(c.branch?.name_ar||''),c.broker?.name||'',parseFloat(c.broker_commission||0),c.marketer?.name||'',parseFloat(c.marketer_commission||0),tComm(c).toFixed(2),parseFloat(c.initial_deposit||0),parseFloat(c.monthly_deposit||0),c.account_kind||'',c.status||'',c.cc_branch_id?'CC':(isEn?'Regular':'عادي')])];
  const wb=XLSX.utils.book_new();const ws=XLSX.utils.aoa_to_sheet(rows);ws['!cols']=hdr.map(()=>({wch:15}));
  XLSX.utils.book_append_sheet(wb,ws,isEn?'Cards':'الكروت');XLSX.writeFile(wb,'WafraCards_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast(tr('done'),'success');
}

ccApplyLang();
ccLoad();
</script>
@endpush
