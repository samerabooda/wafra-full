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
.ig-clr{align-self:flex-end;padding:13px 32px;border-radius:12px;border:none;
  background:linear-gradient(135deg,var(--pri),var(--pri3));color:#fff;
  font-size:15px;font-weight:800;cursor:pointer;display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 5px 18px rgba(26,173,186,.45);transition:all .16s ease;white-space:nowrap}
.ig-clr:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(26,173,186,.6);filter:brightness(1.08)}
.ig-sel{min-width:158px;padding:10px 12px;font-size:13px;font-weight:600;cursor:pointer}
.ig-cal-chip{align-self:flex-end;padding:11px 16px;border-radius:10px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);font-size:13px;font-weight:700;cursor:pointer;white-space:nowrap;transition:all .15s}
.ig-cal-chip:hover{border-color:var(--pri);color:var(--pri2)}
.ig-cal{position:fixed;top:120px;right:24px;z-index:2147483000;width:380px;max-width:92vw;
  background:var(--bg3);border:1px solid var(--card-brd);border-radius:16px;
  box-shadow:0 24px 70px rgba(0,0,0,.6);padding:18px;display:none;backdrop-filter:none}
[data-theme="light"] .ig-cal{background:#FFFFFF}
.ig-cal.open{display:block}
.ig-cal-tabs{display:flex;gap:6px;margin-bottom:10px}
.ig-cal-tab{flex:1;padding:8px;border-radius:9px;border:1px solid var(--brd1);background:var(--bg3);color:var(--mu);font-size:12px;font-weight:700;cursor:pointer}
.ig-cal-tab.on{border-color:var(--pri);color:var(--pri2);background:rgba(26,173,186,.1)}
.ig-cal-tab b{color:var(--tx)}
.ig-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.ig-cal-nav button{width:32px;height:32px;border-radius:8px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);cursor:pointer;font-size:16px}
.ig-cal-nav span{font-weight:900;font-family:'JetBrains Mono',monospace;font-size:15px;color:var(--tx)}
.ig-cal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:12px}
.ig-cal-m{padding:14px 4px;border-radius:10px;border:1px solid var(--brd1);background:var(--bg4);color:var(--tx);font-size:14px;font-weight:700;cursor:pointer;text-align:center;transition:all .12s}
.ig-cal-m:hover{border-color:var(--pri);color:var(--pri2)}
.ig-cal-m.f{background:linear-gradient(135deg,var(--pri2),var(--pri));color:#fff;border-color:transparent}
.ig-cal-m.t{background:linear-gradient(135deg,#22c97a,#168a52);color:#fff;border-color:transparent}
.ig-cal-m.in{background:rgba(26,173,186,.14);border-color:rgba(26,173,186,.3)}
.ig-cal-foot{display:flex;gap:8px;justify-content:flex-end}
.ig-cal-fbtn{padding:8px 16px;border-radius:9px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);font-size:12px;font-weight:700;cursor:pointer}
.ig-cal-fbtn.on{background:linear-gradient(135deg,var(--pri),var(--pri3));color:#fff;border:none}
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
.ig-donut-grid{display:flex;flex-wrap:wrap;gap:16px;justify-content:center;align-items:flex-start;padding:10px 4px}
.ig-donut{text-align:center;width:112px}
.ig-donut-n{font-size:12px;font-weight:800;margin-top:5px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;color:var(--tx,#dfe7ef)}
.ig-donut-s{font-size:10.5px;font-weight:700;margin-top:2px;letter-spacing:.3px}
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
    <div style="position:relative">
      <button class="ig-cal-chip" onclick="igCalToggle()" id="ig-cal-chip">📅 <span id="ig-cal-chip-l">اختر الفترة (شهر/سنة)</span></button>
      <div class="ig-cal" id="ig-cal">
        <div class="ig-cal-tabs">
          <button class="ig-cal-tab on" id="ig-cal-tab-from" onclick="igCalTab('from')">من: <b id="ig-cal-from-v">—</b></button>
          <button class="ig-cal-tab" id="ig-cal-tab-to" onclick="igCalTab('to')">إلى: <b id="ig-cal-to-v">—</b></button>
        </div>
        <div class="ig-cal-nav">
          <button type="button" onclick="igCalYear(-1)">‹</button>
          <span id="ig-cal-year">2025</span>
          <button type="button" onclick="igCalYear(1)">›</button>
        </div>
        <div class="ig-cal-grid" id="ig-cal-grid"></div>
        <div class="ig-cal-foot">
          <button class="ig-cal-fbtn" onclick="igCalClear()">مسح</button>
          <button class="ig-cal-fbtn on" onclick="igCalApply()">تطبيق</button>
        </div>
      </div>
    </div>
    <button class="ig-clr" onclick="igClear()" id="ig-clr">🗓️ <span id="ig-clr-l">عرض الكل</span></button>
  </div>

  <div id="ig-ol-modal" style="display:none;position:fixed;inset:0;z-index:2147483000;background:rgba(10,12,24,.55);-webkit-backdrop-filter:blur(4px);backdrop-filter:blur(4px);align-items:flex-start;justify-content:center;padding:40px 16px;overflow:auto">
    <div style="background:var(--bg3,#fff);border:1px solid rgba(224,80,80,.5);border-radius:16px;max-width:880px;width:100%;box-shadow:0 24px 70px rgba(0,0,0,.4)">
      <div style="display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid var(--brd1)">
        <div style="font-weight:900;font-size:15px;color:#e05050">⚠️ حسابات تجاوزت سقف العمولة ($7)</div>
        <button onclick="window.igOlClose()" style="border:none;background:rgba(224,80,80,.14);color:#e05050;width:30px;height:30px;border-radius:9px;font-weight:900;cursor:pointer;font-size:14px">✕</button>
      </div>
      <div id="ig-ol-body" style="padding:14px 18px;max-height:70vh;overflow:auto"></div>
    </div>
  </div>

  <div class="ig-nums">
    <div class="ig-num" style="--ac:#1AADBA"><div class="ig-num-ic">👥</div><div class="ig-num-b"><div class="ig-num-v" id="ig-acc">—</div><div class="ig-num-l" id="ig-l-acc">إجمالي الحسابات</div></div></div>
    <div class="ig-num" style="--ac:#7b68ee"><div class="ig-num-ic">📣</div><div class="ig-num-b"><div class="ig-num-v" id="ig-mkt">—</div><div class="ig-num-l">المسوّقون</div></div></div>
    <div class="ig-num" style="--ac:#2563EB"><div class="ig-num-ic">🧑‍💼</div><div class="ig-num-b"><div class="ig-num-v" id="ig-brk">—</div><div class="ig-num-l">البروكرز</div></div></div>
    <div class="ig-num" style="--ac:#f5a623"><div class="ig-num-ic">📞</div><div class="ig-num-b"><div class="ig-num-v" id="ig-cccomm">—</div><div class="ig-num-l" id="ig-l-cccomm">عمولات مركز الاتصال</div></div></div>
    <div class="ig-num" style="--ac:#22c97a"><div class="ig-num-ic">🎯</div><div class="ig-num-b"><div class="ig-num-v" id="ig-avg">—</div><div class="ig-num-l" id="ig-l-avg">متوسط العمولة / حساب</div></div></div>
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

  {{-- 🔗 Top referrals + drill-down of accounts they brought --}}
  <div class="ig-grid ig-21" style="margin-bottom:16px">
    <div class="ig-panel ig-lead" id="ig-lead-referral"></div>
    <div class="ig-panel" id="ig-referral-detail" style="padding:16px"></div>
  </div>

  <div class="ig-grid ig-3">
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-gauge">🎯 نسبة الحسابات الجديدة</div>
      <div class="ig-gauge"><canvas id="ig-gauge-c"></canvas><div class="ig-gauge-c"><div class="ig-gauge-v" id="ig-gauge-v" style="color:#22c97a">—</div><div class="ig-gauge-l" id="ig-gauge-l">جديد من الإجمالي</div></div></div>
      <div id="ig-gauge-counts" style="display:flex;justify-content:center;gap:10px;margin-top:6px;flex-wrap:wrap"></div>
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

  <div class="ig-grid ig-21" id="ig-grid-branch-monthly">
    <div class="ig-panel" id="ig-panel-branch">
      <div class="ig-pt" id="ig-t-branch">🏢 الحسابات حسب الفرع</div>
      <div id="ig-branches"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
    </div>
    <div class="ig-panel" id="ig-panel-monthly">
      <div style="display:flex;align-items:center;justify-content:space-between;gap:10px;flex-wrap:wrap;margin-bottom:8px">
        <div class="ig-pt" id="ig-t-monthly" style="margin:0">📅 الاتجاه الشهري</div>
        <select id="ig-month-year" onchange="igYearChange()" style="padding:6px 12px;border-radius:9px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);font-weight:800;font-size:12.5px;cursor:pointer"></select>
      </div>
      <div id="ig-month-insight" style="font-size:12.5px;font-weight:700;color:var(--tx);background:rgba(26,173,186,.08);border:1px solid rgba(26,173,186,.18);border-radius:10px;padding:9px 12px;margin-bottom:10px;line-height:1.7"></div>
      <div class="ig-canvas" style="height:250px"><canvas id="ig-month-c"></canvas></div>
    </div>
  </div>

  <div class="ig-grid" style="margin-bottom:16px" id="ig-grid-newsub">
    <div class="ig-panel" id="ig-panel-newsub">
      <div class="ig-pt" id="ig-t-newsub">🟢🔵 الحسابات الجديدة مقابل الفرعية — لكل فرع</div>
      <div id="ig-newsub" class="ig-donut-grid"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
    </div>
  </div>

  <div class="ig-grid ig-2" style="grid-template-columns:1fr 1fr">
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-modbr">✏️ أكثر الفروع تعديلاً للكروت</div>
      <div id="ig-modbranches"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
    </div>
    <div class="ig-panel">
      <div class="ig-pt" id="ig-t-ceil">🎯 سقف العمولة — $7 لكل حساب</div>
      <div class="ig-gauge"><canvas id="ig-ceil-c"></canvas><div class="ig-gauge-c"><div class="ig-gauge-v" id="ig-ceil-v" style="color:#22c97a">—</div><div class="ig-gauge-l">متوسط العمولة / حساب</div></div></div>
      <div id="ig-ceil-alert" style="margin-top:8px"></div>
    </div>
  </div>

  {{-- 🔁 Cross-branch duplicate accounts — monitoring list (Finance Admin) --}}
  <div class="ig-grid" id="ig-xbr-wrap" style="margin-bottom:16px;display:none">
    <div class="ig-panel" style="border:1px solid rgba(123,104,238,.45)">
      <div class="ig-pt">🔁 حسابات مكرّرة بين الفروع — للمراقبة <span id="ig-xbr-count" style="font-size:11px;color:var(--mu)"></span></div>
      <div style="font-size:12px;color:var(--mu);margin-bottom:10px">نفس رقم الحساب موجود في أكثر من فرع — اضغط على الفرع لرؤية تفاصيل حسابه فيه. يُترك كل حساب في فرعه والقرار للإدارة.</div>
      <div id="ig-xbr" style="max-height:560px;overflow:auto"><div style="text-align:center;padding:30px"><span class="ig-spin"></span></div></div>
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
      bestBroker:'🥇 أفضل بروكر',bestMktr:'🥇 أفضل مسوّق/ة',bestBranch:'🏆 أفضل فرع (حسابات جديدة)',newAccLbl:'حساب جديد',
      bestReferral:'🔗 أفضل المُحيلين',refAccLbl:'حساب جاء به',refPick:'اختر مُحيلاً لعرض الحسابات التي جاء بها 👈',refBrought:'حسابات جاء بها الحساب',refTotalComm:'إجمالي عمولة الإحالة',refAcc:'رقم الحساب المُحيل'},
    en:{lAcc:'Total Accounts',lDep:'Initial Deposit',lMon:'Monthly Deposit',lComm:'Total Commission',lCc:'CC Cards',lMod:'Modified',
      tGauge:'🎯 New Accounts Ratio',gaugeL:'new of total',tComm:'💰 Commission Split',tCc:'📞 Call Center Share',ccL:'of cards',tBranch:'🏢 Accounts by Branch',tMonthly:'📅 Accounts Opened by Branch (Monthly)',
      broker:'Broker',marketer:'Marketer',ext:'External',accounts:'Accounts',deposit:'Deposit (K)',noData:'No data',tb:'Dashboard',
      hTitle:'Commission Cards Analytics',hSub:'Overview of all accounts',lFrom:'From',lTo:'To',oFrom:'Earliest',oTo:'Latest',clr:'All',
      tModbr:'✏️ Most-Modifying Branches',tEmpty:'⚠️ Opened Without Deposit',mods:'edits',perAcc:'per account',newAcc:'new accounts',empty0:'No modifications yet',noDepLbl:'no deposit',
      bestBroker:'🥇 Best Broker',bestMktr:'🥇 Best Marketer',bestBranch:'🏆 Best Branch (New Accounts)',newAccLbl:'new accounts',
      bestReferral:'🔗 Top Referrals',refAccLbl:'accounts brought',refPick:'Pick a referrer to see the accounts they brought 👈',refBrought:'Accounts brought by',refTotalComm:'Total referral commission',refAcc:'Referrer account'}};
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (T[L()]&&T[L()][k])||T.ar[k]||k;}
  function set(id,v){var e=document.getElementById(id);if(e&&v!==undefined)e.textContent=v;}
  function num(v){return parseFloat(v)||0;}
  function kk(n){n=num(n);return n>=1e6?'$'+(n/1e6).toFixed(1)+'M':n>=1000?'$'+(n/1000).toFixed(0)+'K':'$'+Math.round(n);}
  function E(s){return window.esc?esc(s):String(s);}
  var PAL=['#1AADBA','#22c97a','#f5a623','#7b68ee','#e0708a','#2563EB','#ff7a45','#26d4e8','#e05050','#4ecdc4','#95b8d1','#a06cd5'];
  // Vivid gradient pairs matching the reference infographic (red→pink, orange→yellow, ...)
  var GRADS=[['#e63946','#ff7a9c'],['#ff7a45','#ffd24c'],['#f1c40f','#22c97a'],['#1AADBA','#4ecdc4'],['#2563EB','#7b68ee'],['#22c97a','#a8e063'],['#7b68ee','#c084fc'],['#26d4e8','#1AADBA'],['#e05050','#ff7a45'],['#457b9d','#26d4e8']];
  // Render a list of ranked horizontal gradient bars into `el`.
  // items: [{name, value, sub}]  — sub is the small text shown at bar end (e.g. "37%").
  // Donut (circle) per item — infographic style: green=new, blue=sub, centre % = new ratio.
  function igDonuts(el, items, isEn){
    if(!el)return;
    if(!items||!items.length){el.innerHTML='<div style="text-align:center;padding:30px;color:var(--mu)">'+t('noData')+'</div>';return;}
    var R=34, C=2*Math.PI*R;
    el.innerHTML=items.map(function(b){
      var nw=num(b.nw), sb=num(b.sb), tot=nw+sb||1, np=Math.round(nw/tot*100);
      var nl=(nw/tot)*C, sl=(sb/tot)*C;
      return '<div class="ig-donut">'
        +'<svg viewBox="0 0 90 90" width="92" height="92" style="overflow:visible">'
        +'<circle cx="45" cy="45" r="'+R+'" fill="none" stroke="rgba(127,143,160,.18)" stroke-width="11"/>'
        +'<circle cx="45" cy="45" r="'+R+'" fill="none" stroke="#22c97a" stroke-width="11" stroke-linecap="round" stroke-dasharray="'+nl.toFixed(1)+' '+C.toFixed(1)+'" transform="rotate(-90 45 45)"/>'
        +'<circle cx="45" cy="45" r="'+R+'" fill="none" stroke="#2563EB" stroke-width="11" stroke-dasharray="'+sl.toFixed(1)+' '+C.toFixed(1)+'" stroke-dashoffset="'+(-nl).toFixed(1)+'" transform="rotate(-90 45 45)"/>'
        +'<text x="45" y="43" text-anchor="middle" font-size="19" font-weight="800" fill="#22c97a">'+np+'%</text>'
        +'<text x="45" y="59" text-anchor="middle" font-size="9.5" fill="#7d8ea0">'+tot.toLocaleString('en')+'</text>'
        +'</svg>'
        +'<div class="ig-donut-n">'+E(b.name)+'</div>'
        +'<div class="ig-donut-s"><span style="color:#22c97a">●'+nw.toLocaleString('en')+'</span> <span style="color:#2563EB">●'+sb.toLocaleString('en')+'</span></div>'
        +'</div>';
    }).join('')
      +'<div style="flex-basis:100%;text-align:center;margin-top:6px;font-size:11.5px;font-weight:700"><span style="color:#22c97a">● '+(isEn?'New':'جديد')+'</span> &nbsp; <span style="color:#2563EB">● '+(isEn?'Sub':'فرعي')+'</span></div>';
  }

  // Glassy frosted bars (same language as the Referrals report) — distinct sharp colour per
  // branch, frosted fill from the start (RTL=right), name + value at opposite ends.
  var BR_PAL=['#0E8A99','#6D3FD4','#1A8E4F','#C12B5E','#B25A0E','#1F66C9','#0E9488','#8A2BE2','#C0392B','#2D6A4F'];
  function gradBars(el, items){
    if(!el)return;
    if(!items||!items.length){el.innerHTML='<div style="text-align:center;padding:30px;color:var(--mu)">'+t('noData')+'</div>';return;}
    var max=Math.max.apply(null,items.map(function(i){return num(i.value);}))||1;
    el.innerHTML=items.map(function(it,i){
      var w=Math.max(8,Math.round(num(it.value)/max*100)), c=BR_PAL[i%BR_PAL.length];
      return '<div style="position:relative;display:flex;align-items:center;justify-content:space-between;height:44px;border-radius:12px;margin-bottom:11px;background:rgba(127,143,160,.10);border:1px solid rgba(127,143,160,.22);overflow:hidden">'+
        '<i style="position:absolute;top:0;bottom:0;right:0;width:'+w+'%;background:'+c+'29;border:1.5px solid '+c+';border-radius:12px;-webkit-backdrop-filter:blur(9px) saturate(150%);backdrop-filter:blur(9px) saturate(150%);box-shadow:inset 0 1px 0 rgba(255,255,255,.22)"></i>'+
        '<span style="position:relative;z-index:1;padding:0 13px;display:flex;align-items:center;gap:9px;font-weight:800;font-size:13.5px;color:var(--tx)">'+
          '<span style="display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:50%;background:'+c+';color:#fff;font-weight:900;font-size:11px;flex-shrink:0">'+(i+1)+'</span>'+E(it.name)+'</span>'+
        '<span style="position:relative;z-index:1;padding:0 14px;font-weight:900;font-size:13.5px;color:'+c+';white-space:nowrap">'+E(num(it.value).toLocaleString('en'))+(it.sub?' <span style="opacity:.75;font-size:11.5px">('+it.sub+')</span>':'')+'</span>'+
      '</div>';
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

  /* ── Month-range calendar (same component as the custom-reports screen) ── */
  var _igCY=new Date().getFullYear(), _igTab='from', _igFrom=null, _igTo=null;
  var IG_M=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  function _igLbl(o){return o?IG_M[o.m]+' '+o.y:'—';}
  function _igIdx(o){return o.y*12+o.m;}
  function igOpts(){
    // move the calendar OUT of the blurred header (escapes its stacking context) so it always renders on top
    var c=document.getElementById('ig-cal');
    if(c && c.parentNode!==document.body){ document.body.appendChild(c); }
  }
  function _igPlace(){
    var c=document.getElementById('ig-cal'), btn=document.getElementById('ig-cal-chip'); if(!c||!btn) return;
    var r=btn.getBoundingClientRect(), w=c.offsetWidth||380;
    var left=Math.min(Math.max(8, r.right-w), window.innerWidth-w-8);   // align right edge to chip, clamp
    c.style.left=left+'px'; c.style.right='auto'; c.style.top=(r.bottom+8)+'px';
  }
  window.igCalToggle=function(){
    var c=document.getElementById('ig-cal'); var willOpen=!c.classList.contains('open');
    c.classList.toggle('open');
    if(willOpen){ igCalRender(); _igPlace(); }
  };
  window.addEventListener('resize',function(){var c=document.getElementById('ig-cal');if(c&&c.classList.contains('open'))_igPlace();});
  window.igCalTab=function(w){_igTab=w;document.getElementById('ig-cal-tab-from').classList.toggle('on',w==='from');document.getElementById('ig-cal-tab-to').classList.toggle('on',w==='to');};
  window.igCalYear=function(d){_igCY+=d;igCalRender();};
  function igCalRender(){
    document.getElementById('ig-cal-year').textContent=_igCY;
    document.getElementById('ig-cal-from-v').textContent=_igLbl(_igFrom);
    document.getElementById('ig-cal-to-v').textContent=_igLbl(_igTo);
    document.getElementById('ig-cal-grid').innerHTML=IG_M.map(function(m,i){
      var o={y:_igCY,m:i},idx=_igIdx(o),cls='';
      if(_igFrom&&idx===_igIdx(_igFrom))cls='f';else if(_igTo&&idx===_igIdx(_igTo))cls='t';
      else if(_igFrom&&_igTo&&idx>_igIdx(_igFrom)&&idx<_igIdx(_igTo))cls='in';
      return '<div class="ig-cal-m '+cls+'" onclick="igCalPick('+i+')">'+m+'</div>';
    }).join('');
  }
  window.igCalPick=function(i){
    var o={y:_igCY,m:i};
    if(_igTab==='from'){_igFrom=o;_igTab='to';igCalTab('to');}else{_igTo=o;}
    if(_igFrom&&_igTo&&_igIdx(_igFrom)>_igIdx(_igTo)){var t=_igFrom;_igFrom=_igTo;_igTo=t;}
    igCalRender();
  };
  window.igCalClear=function(){_igFrom=null;_igTo=null;_igTab='from';igCalTab('from');igCalRender();};
  window.igCalApply=function(){
    if(!_igFrom)return;
    var to=_igTo||_igFrom;
    document.getElementById('ig-cal-chip-l').textContent=_igLbl(_igFrom)+' → '+_igLbl(to);
    document.getElementById('ig-cal').classList.remove('open');
    load();
  };
  document.addEventListener('click',function(e){var cal=document.getElementById('ig-cal'),btn=document.getElementById('ig-cal-chip');if(cal&&cal.classList.contains('open')&&!cal.contains(e.target)&&btn&&!btn.contains(e.target))cal.classList.remove('open');});
  // "2025-01" (month input) -> "Jan 2025" (format the API/data understand)
  function _m2api(v){ if(!v) return ''; var p=String(v).split('-'); if(p.length<2) return v;
    var d=new Date(parseInt(p[0]),parseInt(p[1])-1,1); return d.toLocaleString('en-US',{month:'short'})+' '+p[0]; }
  window.igReload=function(){load();};
  window.igClear=function(){_igFrom=null;_igTo=null;_igTab='from';var l=document.getElementById('ig-cal-chip-l');if(l)l.textContent=(L()==='en'?'Pick period (month/year)':'اختر الفترة (شهر/سنة)');load();};

  // Cross-branch duplicate accounts — monitoring list (loaded once, independent of the period filter)
  function igCrossBranch(){
    var wrap=document.getElementById('ig-xbr-wrap'),box=document.getElementById('ig-xbr');if(!wrap||!box)return;
    api('GET','/cards/cross-branch').then(function(r){
      if(!r||!r.data||!r.data.length){wrap.style.display='none';return;}
      var isEn=L()==='en';
      wrap.style.display='';
      var ct=document.getElementById('ig-xbr-count');if(ct)ct.textContent='('+r.count.toLocaleString('en')+')';
      var RL=isEn?{broker:'Broker',marketer1:'Int. Marketer 1',marketer2:'Int. Marketer 2',external:'External Mktr',referral:'Referral'}
                 :{broker:'بروكر',marketer1:'مسوّق داخلي 1',marketer2:'مسوّق داخلي 2',external:'مسوّق خارجي',referral:'إحالة'};
      // Saturated, high-contrast, clearly-distinct hues — one sharp colour per branch.
      var PAL=['#0E8A99','#6D3FD4','#1A8E4F','#C12B5E','#B25A0E','#1F66C9','#8A2BE2','#0E9488','#C0392B','#2D6A4F'];
      var side=isEn?'left':'right';
      // ONE flat table. Account & branch cells are merged (rowspan); each person = one row.
      var rowsHtml=r.data.map(function(a){
        var acctRows=a.branches.reduce(function(s,b){return s+Math.max((b.people||[]).length,1);},0);
        var acctShown=false;
        return a.branches.map(function(b){
          var rawn=(isEn?(b.name_en||b.name_ar):(b.name_ar||b.name_en))||'';
          var nm=E(rawn),_h=0; for(var _k=0;_k<rawn.length;_k++){_h=(_h*31+rawn.charCodeAt(_k))>>>0;}
          var col=PAL[_h%PAL.length];   // stable colour per branch name
          var ppl=(b.people||[]);
          var pn=Math.max(ppl.length,1);
          var brShown=false;
          return (ppl.length?ppl:[null]).map(function(p){
            var isAcctFirst=!acctShown, tds='';
            if(!acctShown){
              tds+='<td rowspan="'+acctRows+'" style="padding:9px 10px;vertical-align:top;border-'+side+':3px solid var(--pri2);font-family:monospace;font-weight:900;color:var(--pri2);font-size:14px;white-space:nowrap">#'+E(String(a.account_number))
                +' <span style="display:inline-flex;align-items:center;justify-content:center;min-width:19px;height:19px;border-radius:50%;background:#7b68ee;color:#fff;font-weight:900;font-size:10px;vertical-align:middle">'+a.branch_count+'</span></td>';
              acctShown=true;
            }
            if(!brShown){
              tds+='<td rowspan="'+pn+'" style="padding:9px 10px;vertical-align:top;background:'+col+'1F;border-'+side+':4px solid '+col+';white-space:nowrap">'
                +'<div style="display:flex;align-items:center;gap:7px">'
                  +'<a href="/cards/'+b.id+'/edit" title="'+(isEn?'Open card':'فتح الكرت')+'" style="background:'+col+';color:#fff;font-weight:800;font-size:12.5px;padding:4px 11px;border-radius:9px;text-decoration:none;box-shadow:0 2px 7px '+col+'66">🏢 '+nm+'</a>'
                  +'<a href="/cards/'+b.id+'/edit" title="'+(isEn?'Edit card':'تعديل الكرت')+'" style="text-decoration:none;font-size:13px;background:rgba(245,168,40,.18);border:1px solid rgba(245,168,40,.55);border-radius:7px;padding:2px 7px">✏️</a>'
                  +'<a href="#" onclick="igXbrDelete(event,'+b.id+',\''+E(String(a.account_number))+'\',\''+nm.replace(/'/g,"")+'\')" title="'+(isEn?'Delete this branch\'s card':'حذف كرت هذا الفرع')+'" style="text-decoration:none;font-size:13px;background:rgba(232,69,69,.18);border:1px solid rgba(232,69,69,.6);border-radius:7px;padding:2px 7px">🗑</a>'
                +'</div>'
                +'<div style="color:var(--mu);font-size:10.5px;margin-top:5px;font-weight:700">'+E(b.month||'')+' · '+(isEn?'Total':'إجمالي')+' <b style="color:'+col+'">$'+(+b.total_commission).toFixed(1)+'</b></div></td>';
              brShown=true;
            }
            if(p){
              tds+='<td style="padding:8px 10px;color:var(--mu);font-size:11px;white-space:nowrap">'+RL[p.role]+'</td>'
                  +'<td style="padding:8px 10px;font-weight:700;color:var(--tx);font-size:12.5px">'+E(p.name)+'</td>'
                  +'<td style="padding:8px 10px;font-family:monospace;font-weight:800;color:'+col+';white-space:nowrap;text-align:'+side+'">$'+(+p.commission).toFixed(1)+'</td>';
            }else{
              tds+='<td colspan="3" style="padding:9px 10px;color:var(--mu);font-style:italic;font-size:12px">'+(isEn?'Self / no employees':'ذاتي / لا موظفين')+'</td>';
            }
            return '<tr style="border-top:'+(isAcctFirst?'2px solid var(--brd2)':'1px solid var(--brd1)')+'">'+tds+'</tr>';
          }).join('');
        }).join('');
      }).join('');
      box.innerHTML='<table style="width:100%;border-collapse:collapse;font-size:12.5px">'
        +'<thead><tr style="text-align:'+side+';color:var(--mu);font-size:11px;text-transform:uppercase;position:sticky;top:0;background:var(--card-bg);z-index:1">'
        +'<th style="padding:9px 10px">'+(isEn?'Account':'الحساب')+'</th>'
        +'<th style="padding:9px 10px">'+(isEn?'Branch':'الفرع')+'</th>'
        +'<th style="padding:9px 10px">'+(isEn?'Role':'الدور')+'</th>'
        +'<th style="padding:9px 10px">'+(isEn?'Employee':'الموظف')+'</th>'
        +'<th style="padding:9px 10px;text-align:'+side+'">'+(isEn?'Commission':'العمولة')+'</th></tr></thead>'
        +'<tbody>'+rowsHtml+'</tbody></table>';
    }).catch(function(){wrap.style.display='none';});
  }

  // Delete one branch's copy of a duplicated account (Finance Admin only — this panel is FA-only).
  window.igXbrDelete=function(ev,id,acct,branch){
    if(ev&&ev.preventDefault)ev.preventDefault();
    var isEn=L()==='en';
    var ok=confirm(isEn
      ? 'Delete card #'+acct+' from branch "'+branch+'"?\nThis removes it from that branch only.'
      : 'حذف الكرت رقم '+acct+' من فرع "'+branch+'"؟\nسيُحذف من هذا الفرع فقط.');
    if(!ok)return;
    api('DELETE','/cards/'+id).then(function(r){
      if(r&&r.success){ if(window.toast)toast(isEn?'Card deleted':'تم حذف الكرت','success'); igCrossBranch(); }
      else { if(window.toast)toast((r&&r.message)||(isEn?'Delete failed':'تعذّر الحذف'),'error'); }
    }).catch(function(){ if(window.toast)toast(isEn?'Delete failed':'تعذّر الحذف','error'); });
  };

  // $7 ceiling violations modal
  window.igOlClose=function(){var m=document.getElementById('ig-ol-modal');if(m)m.style.display='none';};
  window.igOverLimit=function(){
    var m=document.getElementById('ig-ol-modal'),b=document.getElementById('ig-ol-body');if(!m||!b)return;
    var isEn=L()==='en';
    m.style.display='flex';
    b.innerHTML='<div style="text-align:center;padding:30px"><span class="ig-spin"></span></div>';
    api('GET','/cards/over-limit').then(function(r){
      if(!r||!r.rows||!r.rows.length){b.innerHTML='<div style="text-align:center;padding:30px;color:#22c97a;font-weight:800">✅ '+(isEn?'No violations':'لا توجد تجاوزات')+'</div>';return;}
      var head='<table style="width:100%;border-collapse:collapse;font-size:12.5px"><thead><tr style="color:var(--mu);text-align:right">'
        +'<th style="padding:6px">'+(isEn?'Account':'الحساب')+'</th><th style="padding:6px">'+(isEn?'Branch':'الفرع')+'</th><th style="padding:6px">'+(isEn?'Broker':'بروكر')+'</th><th style="padding:6px">'+(isEn?'Marketer':'مسوّق')+'</th><th style="padding:6px">CC</th><th style="padding:6px">'+(isEn?'Total':'الإجمالي')+'</th><th></th></tr></thead><tbody>';
      var body=r.rows.map(function(c){
        var ccv=(num(c.cc_agent_commission)+num(c.cc_manager_commission));
        var tt=num(c.total_comm);
        return '<tr style="border-top:1px solid var(--brd1)">'
          +'<td style="padding:7px 6px;font-family:monospace;font-weight:800;color:var(--pri2)">'+E(String(c.account_number))+'</td>'
          +'<td style="padding:7px 6px">'+E(isEn?(c.branch_en||c.branch_ar||'—'):(c.branch_ar||'—'))+'</td>'
          +'<td style="padding:7px 6px">'+E(c.broker_name||'—')+'</td>'
          +'<td style="padding:7px 6px">'+E(c.marketer_name||'—')+'</td>'
          +'<td style="padding:7px 6px;font-family:monospace">$'+ccv.toFixed(1)+'</td>'
          +'<td style="padding:7px 6px;font-family:monospace;font-weight:900;color:#e05050">$'+tt.toFixed(2)+'</td>'
          +'<td style="padding:7px 6px"><a href="/cards/'+c.id+'/edit" style="color:#f5a623;text-decoration:none">✏️</a></td></tr>';
      }).join('');
      b.innerHTML=head+body+'</tbody></table>'+(r.count>=500?'<div style="text-align:center;color:var(--mu);font-size:11px;padding:8px">'+(isEn?'Showing first 500':'يُعرض أول 500')+'</div>':'');
    }).catch(function(){b.innerHTML='<div style="text-align:center;padding:30px;color:#e05050">'+(isEn?'Failed to load':'تعذّر التحميل')+'</div>';});
  };

  function chartTheme(){
    var light=document.documentElement.getAttribute('data-theme')==='light';
    return {
      pri:'#1AADBA', gr:'#1ECC80', pu:'#7b68ee', or:'#F5A828',
      track: light ? '#D7E3EE' : 'rgba(255,255,255,.07)',   // empty-ring, visible in both themes
      seg:   light ? '#FFFFFF' : 'rgba(15,25,40,.55)',       // segment separators = card bg
      tx:    light ? '#243447' : '#dfe7ef',                  // axis/legend text
      mu:    light ? '#6b7a8d' : '#8aa0b5',                  // muted ticks
      grid:  light ? 'rgba(0,0,0,.06)' : 'rgba(255,255,255,.06)'
    };
  }
  // Re-render charts when the theme switches (so colors stay harmonious live)
  try{ new MutationObserver(function(){ if(typeof DATA!=='undefined' && DATA) render(DATA); })
        .observe(document.documentElement,{attributes:true,attributeFilter:['data-theme']}); }catch(e){}
  function render(r){
    var isEn=L()==='en',tot=r.totals||{},bm=r.by_month||[],bb=r.by_branch||[];
    var TH=chartTheme();
    var total=num(tot.count),totComm=num(tot.bc)+num(tot.mc)+num(tot.ec);
    var _CM=r.commission||{};
    set('ig-acc',(tot.count||0).toLocaleString('en'));set('ig-mkt',num(_CM.marketers).toLocaleString('en'));set('ig-brk',num(_CM.brokers).toLocaleString('en'));set('ig-cccomm',kk(_CM.cc||0));set('ig-avg','$'+num(_CM.avg).toFixed(1));set('ig-comm',kk(_CM.total||totComm));set('ig-cc',(tot.cc||0).toLocaleString('en'));set('ig-mod',(tot.mod||0).toLocaleString('en'));
    var sbc=document.getElementById('sb-cards-count');if(sbc)sbc.textContent=(tot.count||0).toLocaleString('en');
    var sbcc=document.getElementById('sb-cc-count');if(sbcc)sbcc.textContent=(tot.cc||0).toLocaleString('en');
    var sbm=document.getElementById('sb-mod-count');if(sbm)sbm.textContent=(tot.mod||0);
    insights(tot,bb,totComm,isEn);
    renderLeaderboards(r,isEn);

    // GAUGE: new ratio (semicircle)
    var nwsb=num(tot.new)+num(tot.sub),nr=nwsb?Math.round(num(tot.new)/nwsb*100):0;
    set('ig-gauge-v',nr+'%');
    var _pNew=nwsb?Math.round(num(tot.new)/nwsb*100):0, _pSub=nwsb?100-_pNew:0;
    var gc=document.getElementById('ig-gauge-counts');
    if(gc){ gc.innerHTML=
      '<span style="display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:900;color:#22c97a;background:rgba(34,201,122,.14);border:1.5px solid rgba(34,201,122,.45);border-radius:9px;padding:4px 11px">🟢 '+num(tot.new).toLocaleString('en')+' '+(isEn?'New':'جديد')+' · '+_pNew+'%</span>'+
      '<span style="display:inline-flex;align-items:center;gap:5px;font-size:12.5px;font-weight:900;color:#2563EB;background:rgba(37,99,235,.14);border:1.5px solid rgba(37,99,235,.45);border-radius:9px;padding:4px 11px">🔵 '+num(tot.sub).toLocaleString('en')+' '+(isEn?'Sub':'فرعي')+' · '+_pSub+'%</span>'; }
    mk('ig-gauge-c',{type:'doughnut',data:{datasets:[{data:[nr,100-nr],backgroundColor:[TH.gr,TH.track],borderWidth:0}]},
      options:{rotation:-90,circumference:180,cutout:'74%',plugins:{legend:{display:false},tooltip:{enabled:false}},animation:{animateRotate:true}}});

    // COMMISSION donut + legend (broker / marketer / external / CC / referral)
    var CM=r.commission||{};
    var cd=[num(CM.broker),num(CM.marketer),num(CM.external),num(CM.cc),num(CM.referral)],
        cc=['#0E7A88','#16A34A','#7C3AED','#F59E0B','#E11D48'],
        cn=[t('broker'),t('marketer'),t('ext'),'CC',isEn?'Referral':'إحالة'];
    var totCM=cd.reduce(function(s,v){return s+v;},0)||1;
    mk('ig-comm-c',{type:'doughnut',data:{labels:cn,datasets:[{data:cd,backgroundColor:cc.map(function(x){return x+'dd';}),borderWidth:3,borderColor:TH.seg}]},
      options:{responsive:true,maintainAspectRatio:false,cutout:'62%',plugins:{legend:{display:false},tooltip:{callbacks:{label:function(c){return ' '+kk(c.raw);}}}}}});
    var leg=document.getElementById('ig-comm-leg');if(leg){leg.innerHTML=cd.map(function(v,i){var p=Math.round(v/totCM*100);return '<div class="ig-leg-i"><span class="ig-leg-d" style="background:'+cc[i]+'"></span><span class="ig-leg-n">'+cn[i]+' <b style="color:'+cc[i]+'">'+p+'%</b></span><span class="ig-leg-v">'+kk(v)+'</span></div>';}).join('');}

    // $7 CEILING gauge (avg commission per account) + over-limit alert
    var avg=num(CM.avg),avgP=Math.min(100,Math.round(avg/7*100)),over7=num(CM.over7);
    var ceilCol=avg>=6.5?'#e05050':avg>=5?'#f5a623':'#22c97a';
    var cv=document.getElementById('ig-ceil-v');if(cv){cv.textContent='$'+avg.toFixed(1);cv.style.color=ceilCol;}
    mk('ig-ceil-c',{type:'doughnut',data:{datasets:[{data:[avgP,100-avgP],backgroundColor:[ceilCol,TH.track],borderWidth:0}]},
      options:{rotation:-90,circumference:180,cutout:'74%',plugins:{legend:{display:false},tooltip:{enabled:false}},animation:{animateRotate:true}}});
    var al=document.getElementById('ig-ceil-alert');if(al){al.innerHTML=over7>0
      ? '<div onclick="window.igOverLimit()" title="'+(isEn?'View accounts':'عرض الحسابات')+'" style="cursor:pointer;display:flex;align-items:center;gap:8px;background:rgba(224,80,80,.12);border:1px solid rgba(224,80,80,.32);border-radius:10px;padding:9px 12px;color:#e05050;font-weight:800;font-size:12.5px"><span style="font-size:18px">⚠️</span> '+over7.toLocaleString('en')+' '+(isEn?'accounts exceed $7 — click to view':'حساب تجاوز سقف $7 — اضغط للعرض')+' <span style="margin-right:auto">🔎</span></div>'
      : '<div style="display:flex;align-items:center;gap:8px;background:rgba(34,201,122,.10);border:1px solid rgba(34,201,122,.25);border-radius:10px;padding:9px 12px;color:#22c97a;font-weight:800;font-size:12.5px"><span style="font-size:16px">✅</span> '+(isEn?'All accounts within $7':'كل الحسابات ضمن سقف $7')+'</div>';}

    // NEW vs SUB per branch — circle (donut) per branch, infographic style
    igDonuts(document.getElementById('ig-newsub'),
      bb.slice().sort(function(a,b){return num(b.cnt)-num(a.cnt);}).map(function(b){
        return {name:isEn?(b.nen||b.nar||'—'):(b.nar||'—'), nw:num(b.newc), sb:num(b.subc)};
      }), isEn);

    // CC ring (center %)
    var ccp=total?Math.round(num(tot.cc)/total*100):0;set('ig-cc-v',ccp+'%');
    mk('ig-cc-c',{type:'doughnut',data:{datasets:[{data:[ccp,100-ccp],backgroundColor:[TH.pu,TH.track],borderWidth:0}]},
      options:{responsive:true,maintainAspectRatio:false,cutout:'76%',plugins:{legend:{display:false},tooltip:{enabled:false}}}});

    // BRANCHES — horizontal gradient bars. A manager only has ONE branch (always 100%) — a
    // misleading "best branch" signal — so hide this panel for managers and widen the monthly one.
    if(IS_MGR()){
      var _bp=document.getElementById('ig-panel-branch'); if(_bp) _bp.style.display='none';
      var _pm=document.getElementById('ig-panel-monthly'); if(_pm) _pm.style.gridColumn='1 / -1';
    }else{
      var brItems=bb.slice().sort(function(a,b){return num(b.cnt)-num(a.cnt);}).map(function(b){
        var c=num(b.cnt),p=total?Math.round(c/total*100):0;
        return {name:isEn?(b.nen||b.nar||'—'):(b.nar||'—'),value:c,sub:p+'%'};
      });
      gradBars(document.getElementById('ig-branches'),brItems);
    }

    // MONTHLY — accounts opened per month.
    var bbm=r.by_branch_month||[];
    if(IS_MGR()){
      // ── ONE unified branch dashboard: KPI chips + a stacked monthly bar (new vs sub) ──
      var mt=document.getElementById('ig-t-monthly'); if(mt) mt.textContent=isEn?'📊 Accounts opened — monthly & new/sub mix':'📊 الحسابات المفتوحة — شهريًا ونسبة الجديد/الفرعي';
      var nsP=document.getElementById('ig-panel-newsub'); if(nsP) nsP.style.display='none';   // merged in here
      var mrows=(bm||[]).slice();
      var totNew=num(tot.new),totSub=num(tot.sub),totAll=totNew+totSub,pNew=totAll?Math.round(totNew/totAll*100):0,pSub=totAll?100-pNew:0;
      var _peakV=0,_peakMo='—'; mrows.forEach(function(m){ if(num(m.cnt)>_peakV){_peakV=num(m.cnt);_peakMo=m.month;} });
      var kchip=function(lab,val,sub,col){return '<div style="background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:11px 12px;text-align:center">'
        +'<div style="font-size:10.5px;color:var(--mu);font-weight:700;margin-bottom:4px">'+lab+'</div>'
        +'<div style="font-size:20px;font-weight:900;color:'+col+';line-height:1.1">'+val+'</div>'
        +(sub?'<div style="font-size:10.5px;color:var(--mu);margin-top:2px">'+sub+'</div>':'')+'</div>';};
      var insEl=document.getElementById('ig-month-insight');
      if(insEl){
        insEl.style.background='transparent';insEl.style.border='none';insEl.style.padding='0';
        insEl.innerHTML='<div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;margin-bottom:6px">'
          +kchip(isEn?'Total accounts':'إجمالي الحسابات',totAll.toLocaleString('en'),mrows.length+' '+(isEn?'months':'شهر'),'var(--pri2)')
          +kchip(isEn?'New':'جديد',totNew.toLocaleString('en'),pNew+'%','#22c97a')
          +kchip(isEn?'Sub':'فرعي',totSub.toLocaleString('en'),pSub+'%','#2563EB')
          +kchip(isEn?'Peak month':'أعلى شهر',_peakV.toLocaleString('en'),_peakMo,'#f5a623')
          +'</div>';
      }
      if(!mrows.length){ var _cc0=document.getElementById('ig-month-c'); if(_cc0&&_cc0.parentElement) _cc0.parentElement.innerHTML='<div style="text-align:center;color:var(--mu);padding:30px">'+t('noData')+'</div>'; }
      else mk('ig-month-c',{type:'bar',
        data:{labels:mrows.map(function(m){return m.month;}),datasets:[
          {label:(isEn?'New':'جديد'),data:mrows.map(function(m){return num(m.newc);}),backgroundColor:'#22c97a',borderRadius:5,stack:'a',maxBarThickness:34},
          {label:(isEn?'Sub':'فرعي'),data:mrows.map(function(m){return num(m.subc);}),backgroundColor:'#2563EB',borderRadius:5,stack:'a',maxBarThickness:34}
        ]},
        options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
          plugins:{legend:{display:true,position:'bottom',labels:{color:tickC,font:{size:11},boxWidth:12,boxHeight:12,padding:14,usePointStyle:true}},
            tooltip:{padding:10,callbacks:{footer:function(items){var s=items.reduce(function(a,b){return a+num(b.parsed.y);},0);return (isEn?'Total: ':'الإجمالي: ')+s.toLocaleString('en');}}}},
          scales:{x:{stacked:true,ticks:{color:tickC,font:{size:9},maxRotation:0,autoSkip:true,maxTicksLimit:12},grid:{display:false}},
            y:{stacked:true,ticks:{color:tickC,font:{size:9},precision:0},grid:{color:gridC},beginAtZero:true}}}
      });
    }else{
      // Finance Admin: a clean, year-filtered monthly view (the all-time chart was unreadable).
      _IGYEAR.bm = bm; _IGYEAR.isEn = isEn;
      var yrs = {}; bm.forEach(function(m){ var y=String(m.month||'').match(/\d{4}/); if(y) yrs[y[0]]=true; });
      var years = Object.keys(yrs).sort();                          // ascending
      var sel = document.getElementById('ig-month-year');
      if(sel){
        var cur = sel.value;
        var def = (cur && yrs[cur]) ? cur : (years.length ? years[years.length-1] : '');
        sel.innerHTML = '<option value="__all">'+(isEn?'All years':'كل السنوات')+'</option>'
          + years.map(function(y){return '<option value="'+y+'"'+(y===def?' selected':'')+'>'+y+'</option>';}).join('');
        sel.value = def;
      }
      igRenderYear((sel && sel.value) || '__all');
    }

    // MOST-MODIFYING branches — horizontal gradient bars
    var modb=bb.filter(function(b){return num(b.modc)>0;}).slice().sort(function(a,b){return num(b.modc)-num(a.modc);});
    var modEl=document.getElementById('ig-modbranches');
    if(modb.length){
      gradBars(modEl,modb.map(function(b){var m=num(b.modc),c=num(b.cnt),p=c?Math.round(m/c*100):0;
        return {name:isEn?(b.nen||b.nar||'—'):(b.nar||'—'),value:m,sub:p+'%'};}));
    }else{
      modEl.innerHTML='<div style="text-align:center;padding:30px;color:var(--mu)">'+t('empty0')+'</div>';
    }

  }

  // ── Year-filtered monthly view (Finance Admin) ──
  var _IGYEAR = { bm:[], isEn:false };
  var _MONABBR = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  window.igYearChange=function(){ var s=document.getElementById('ig-month-year'); igRenderYear(s?s.value:'__all'); };
  function igRenderYear(year){
    var bm=_IGYEAR.bm||[], isEn=_IGYEAR.isEn;
    var labels=[], dNew=[], dSub=[], dTot=[];
    if(year==='__all'){
      var byY={};
      bm.forEach(function(m){ var ym=String(m.month||'').match(/(\d{4})/); if(!ym)return; var y=ym[1];
        if(!byY[y])byY[y]={n:0,s:0,t:0}; byY[y].n+=num(m.newc); byY[y].s+=num(m.subc); byY[y].t+=num(m.cnt); });
      Object.keys(byY).sort().forEach(function(y){ labels.push(y); dNew.push(byY[y].n); dSub.push(byY[y].s); dTot.push(byY[y].t); });
    } else {
      var map={};
      bm.forEach(function(m){ var s=String(m.month||''); if(s.indexOf(year)<0)return;
        var mo=s.replace(/[^A-Za-z]/g,'').slice(0,3); map[mo]={n:num(m.newc),s:num(m.subc),t:num(m.cnt)}; });
      for(var i=0;i<12;i++){ var d=map[_MONABBR[i]]||{n:0,s:0,t:0}; labels.push(_MONABBR[i]); dNew.push(d.n); dSub.push(d.s); dTot.push(d.t); }
    }
    var grand=dTot.reduce(function(a,b){return a+b;},0);
    var peak=Math.max.apply(null,dTot.concat([0])), pi=dTot.indexOf(peak);
    var gN=dNew.reduce(function(a,b){return a+b;},0), gS=dSub.reduce(function(a,b){return a+b;},0);
    var pNew=(gN+gS)?Math.round(gN/(gN+gS)*100):0;
    var insEl=document.getElementById('ig-month-insight');
    if(insEl){
      insEl.innerHTML = grand
        ? (isEn
           ? ('Opened <b style="color:#22C4D4">'+grand.toLocaleString('en')+'</b> · '+pNew+'% new · Peak <b>'+(labels[pi]||'—')+'</b> ('+peak.toLocaleString('en')+')')
           : ('المفتوحة <b style="color:#22C4D4">'+grand.toLocaleString('en')+'</b> · '+pNew+'% جديد · الأعلى <b>'+(labels[pi]||'—')+'</b> ('+peak.toLocaleString('en')+')'))
        : (isEn?'No data for this period':'لا بيانات لهذه الفترة');
    }
    mk('ig-month-c',{type:'bar',
      data:{labels:labels,datasets:[
        {label:(isEn?'New':'جديد'),data:dNew,backgroundColor:'#22c97a',borderRadius:5,stack:'a',maxBarThickness:40},
        {label:(isEn?'Sub':'فرعي'),data:dSub,backgroundColor:'#2563EB',borderRadius:5,stack:'a',maxBarThickness:40}
      ]},
      options:{responsive:true,maintainAspectRatio:false,interaction:{mode:'index',intersect:false},
        plugins:{legend:{display:true,position:'bottom',labels:{color:tickC,font:{size:11},boxWidth:12,boxHeight:12,padding:14,usePointStyle:true}},
          tooltip:{padding:10,callbacks:{footer:function(items){var s=items.reduce(function(a,b){return a+num(b.parsed.y);},0);return (isEn?'Total: ':'الإجمالي: ')+s.toLocaleString('en');}}}},
        scales:{x:{stacked:true,ticks:{color:tickC,font:{size:10},maxRotation:0},grid:{display:false}},
          y:{stacked:true,ticks:{color:tickC,font:{size:9},precision:0},grid:{color:gridC},beginAtZero:true}}}
    });
  }

  // A branch manager / viewer is scoped to one branch — used to swap branch-comparison panels.
  function IS_MGR(){ return (typeof CURRENT_USER!=='undefined' && CURRENT_USER && CURRENT_USER.role!=='finance_admin'); }

  // Marketers ranked by their SHARE (%) of the branch's new accounts (manager view).
  function mgrMarketerCard(el,tm,totalNew,isEn){
    if(!el)return;
    var color='#7b68ee';
    var title=isEn?'📊 Marketers — share of new accounts':'📊 نسبة المسوّقين من الحسابات الجديدة';
    var hdr='<div class="ig-lead-h"><span class="ic" style="background:'+color+'">📊</span><span class="ti">'+title+'</span></div>';
    var items=(tm||[]).filter(function(x){return x&&x.nm&&num(x.newc)>0;});
    if(!items.length){el.innerHTML=hdr+'<div class="ig-lead-empty">'+t('noData')+'</div>';return;}
    var tn=num(totalNew)||items.reduce(function(s,x){return s+num(x.newc);},0)||1;
    var pct=function(v){return Math.round(num(v)/tn*100);};
    var top=items[0],rest=items.slice(1);
    el.innerHTML=hdr+
      '<div class="ig-lead-top"><div class="ig-lead-av" style="background:'+color+'">'+E((top.nm||'?').charAt(0).toUpperCase())+'</div>'+
      '<div style="min-width:0;flex:1"><div class="ig-lead-nm">🥇 '+E(top.nm)+'</div><div class="ig-lead-sub">'+num(top.newc).toLocaleString('en')+' '+(isEn?'new accounts':'حساب جديد')+'</div></div>'+
      '<div class="ig-lead-big"><div class="v" style="color:'+color+'">'+pct(top.newc)+'%</div></div></div>'+
      (rest.length?'<div class="ig-lead-rest">'+rest.map(function(r2,i){return '<div class="ig-lead-row"><span class="ig-lead-rk">'+(i+2)+'</span><span class="ig-lead-rn">'+E(r2.nm)+'</span><span class="ig-lead-rv" style="color:'+color+'">'+pct(r2.newc)+'% <span style="color:var(--mu);font-weight:600">('+num(r2.newc)+')</span></span></div>';}).join('')+'</div>':'');
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
    var tb=r.top_brokers||[],tm=r.top_marketers||[],bb=r.by_branch||[],tr=r.top_referrals||[];
    var branchItems=bb.slice().sort(function(a,b){return num(b.newc)-num(a.newc);}).slice(0,3).map(function(b){return {name:isEn?(b.nen||b.nar):(b.nar||b.nen),value:num(b.newc)};});
    leadCard(document.getElementById('ig-lead-broker'),{icon:'🧑‍💼',color:'#1AADBA',title:t('bestBroker'),label:t('newAccLbl'),items:tb.map(function(x){return {name:x.nm,value:num(x.newc)};})});
    leadCard(document.getElementById('ig-lead-mktr'),  {icon:'📢',color:'#7b68ee',title:t('bestMktr'),  label:t('newAccLbl'),items:tm.map(function(x){return {name:x.nm,value:num(x.newc)};})});
    // Branch managers only see their own branch, so "best branch" (always 100%) is a false signal.
    // Replace it with each marketer's SHARE of new accounts — a fair gauge to judge marketers.
    if(IS_MGR()){
      mgrMarketerCard(document.getElementById('ig-lead-branch'),tm,num((r.totals||{}).new),isEn);
    }else{
      leadCard(document.getElementById('ig-lead-branch'),{icon:'🏢',color:'#22c97a',title:t('bestBranch'),label:t('newAccLbl'),items:branchItems});
    }
    referralCard(document.getElementById('ig-lead-referral'),tr);
    var det=document.getElementById('ig-referral-detail');
    if(det) det.innerHTML='<div class="ig-lead-empty">'+t('refPick')+'</div>';
  }
  function referralCard(el,tr){
    if(!el)return;
    var color='#f5a623';
    var hdr='<div class="ig-lead-h"><span class="ic" style="background:'+color+'">🔗</span><span class="ti">'+t('bestReferral')+'</span></div>';
    var items=(tr||[]).filter(function(x){return x&&(x.acc||x.nm)&&num(x.total)>0;});
    if(!items.length){el.innerHTML=hdr+'<div class="ig-lead-empty">'+t('noData')+'</div>';return;}
    var rows=items.map(function(x,i){
      var acc=String(x.acc||x.nm), emp=(x.rtype==='employee');
      var badge=emp?' <span style="font-size:9.5px;font-weight:800;color:#5b46d6;background:rgba(123,104,238,.14);border-radius:8px;padding:1px 5px">👤</span>'
                   :'';
      // name + account number side by side (account shown only if it differs from the name)
      var acctTag=(acc && acc!==String(x.nm)) ? ' <span style="color:var(--mu);font-weight:600;font-size:10.5px">#'+E(acc)+'</span>' : '';
      return '<div class="ig-lead-row" style="cursor:pointer" title="'+E(t('refPick'))+'" onclick="showReferralDetail(\''+E(acc).replace(/\\\\/g,"")+'\')">'+
        '<span class="ig-lead-rk">'+(i+1)+'</span>'+
        '<span class="ig-lead-rn">🔗 '+E(x.nm)+acctTag+badge+'</span>'+
        '<span class="ig-lead-rv" style="color:'+color+'">'+num(x.total).toLocaleString('en')+'</span></div>';
    }).join('');
    el.innerHTML=hdr+'<div class="ig-lead-sub" style="padding:2px 4px 8px">'+t('refAccLbl')+' · '+t('refPick')+'</div><div class="ig-lead-rest">'+rows+'</div>';
  }
  async function showReferralDetail(acc){
    var det=document.getElementById('ig-referral-detail');if(!det)return;
    det.innerHTML='<div class="ig-lead-empty">…</div>';
    try{
      var r=await api('GET','/cards/referrals?account='+encodeURIComponent(acc));
      if(!r||!r.success){det.innerHTML='<div class="ig-lead-empty">'+t('noData')+'</div>';return;}
      var rows=(r.data||[]).map(function(d){
        return '<div class="ig-lead-row"><span class="ig-lead-rn">#'+E(d.account_number)+' · '+E(d.month)+(d.branch?' · '+E(d.branch):'')+'</span><span class="ig-lead-rv" style="color:#22c97a">$'+num(d.referral_commission).toLocaleString('en')+'</span></div>';
      }).join('');
      det.innerHTML='<div class="ig-lead-h"><span class="ic" style="background:'+'#f5a623'+'">🔗</span><span class="ti">'+t('refBrought')+' #'+E(acc)+'</span></div>'+
        '<div style="display:flex;gap:16px;margin:10px 0 12px;flex-wrap:wrap"><div class="ig-lead-sub">'+t('refBrought')+': <b>'+num(r.count)+'</b></div><div class="ig-lead-sub">'+t('refTotalComm')+': <b style="color:#22c97a">$'+num(r.total_commission).toLocaleString('en')+'</b></div></div>'+
        '<div class="ig-lead-rest" style="max-height:240px;overflow:auto">'+(rows||'<div class="ig-lead-empty">'+t('noData')+'</div>')+'</div>';
    }catch(e){det.innerHTML='<div class="ig-lead-empty">'+t('noData')+'</div>';}
  }
  window.showReferralDetail=showReferralDetail;

  function insights(tot,bb,totComm,isEn){
    var box=document.getElementById('ig-insights');if(!box)return;var total=num(tot.count);if(!total){box.innerHTML='';return;}
    var ins=[],S=function(v){return '<span class="ig-ins-s">'+v+'</span>';};
    // "Branch X leads Y%" is meaningless for a manager (they only see their own branch → always 100%).
    if(bb.length && !IS_MGR()){var b=bb[0],p=Math.round(num(b.cnt)/total*100);ins.push({c:'#1AADBA',i:'🏢',x:isEn?'Branch '+S(b.nen||b.nar)+' leads '+S(p+'%'):'فرع '+S(b.nar||b.nen)+' يتصدّر '+S(p+'%')});}
    if(num(tot.new)+num(tot.sub)>0){var np=Math.round(num(tot.new)/(num(tot.new)+num(tot.sub))*100);ins.push({c:'#22c97a',i:'📈',x:isEn?S(np+'%')+' new vs '+(100-np)+'% sub':S(np+'%')+' جديدة مقابل '+(100-np)+'% فرعية'});}
    if(totComm>0){var ra=num(tot.dep)/totComm;ins.push({c:'#f5a623',i:'💵',x:isEn?'$1 → '+S('$'+Math.round(ra).toLocaleString('en'))+' deposits':'كل $1 → '+S('$'+Math.round(ra).toLocaleString('en'))+' إيداعات'});}
    if(num(tot.cc)>0){var cp=Math.round(num(tot.cc)/total*100);ins.push({c:'#7b68ee',i:'📞',x:isEn?S(cp+'%')+' via CC':S(cp+'%')+' عبر CC'});}
    if(num(tot.mod)>0){var mp=Math.round(num(tot.mod)/total*100);ins.push({c:'#e0708a',i:'✏️',x:isEn?S(tot.mod)+' modified ('+mp+'%)':S(tot.mod)+' معدّل ('+mp+'%)'});}
    box.innerHTML=ins.map(function(o){return '<div class="ig-ins-c" style="--ic:'+o.c+'"><span style="font-size:20px">'+o.i+'</span><span class="ig-ins-x">'+o.x+'</span></div>';}).join('');
  }

  function fail(m){document.getElementById('ig-branches').innerHTML='<div style="padding:14px;color:var(--re)">⚠️ '+t('noData')+'<pre style="font-size:11px;color:var(--mu);white-space:pre-wrap;direction:ltr;background:rgba(0,0,0,.2);padding:8px;border-radius:8px;margin-top:8px">'+E(m||'')+'</pre></div>';}
  function load(tries){
    tries=tries||0;
    var f=_igFrom?_igLbl(_igFrom):'', tt=_igTo?_igLbl(_igTo):(_igFrom?_igLbl(_igFrom):'');
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
  igOpts();applyLang();load();try{igCrossBranch();}catch(e){}
})();
</script>
@endpush
