@extends('layouts.app')
@section('title', 'Clients in Follow-up')
@section('page-title', 'Clients in Follow-up')

@php
  $me       = auth()->user();
  $myBranch = $me?->branch_id;
  $isFa     = $me && !$me->isScopedToBranch();      // Finance Admin (no branch) — can act on all
@endphp

@section('content')
<style>
.lead-card{border:1px solid var(--brd1);border-right:4px solid #7b68ee;border-radius:12px;margin-bottom:14px;overflow:hidden}
.lead-head{background:var(--bg2);padding:13px 16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between}
.lead-phone{font-size:17px;font-weight:800;letter-spacing:.3px;direction:ltr;display:inline-block}
.lead-chip{display:inline-block;padding:3px 11px;border-radius:12px;font-size:11px;font-weight:700}
.lead-chip.branch_pending{background:#cff4fc;color:#055160}
.lead-chip.in_progress{background:#fff3cd;color:#7a5b00}
.lead-chip.accepted{background:#d1e7dd;color:#0a5c36}
.lead-meta{font-size:12px;color:var(--mu)}
.lead-body{padding:12px 16px}
.lead-log{background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.18);border-radius:9px;padding:8px 12px;margin-top:8px;font-size:12px}
.lead-log .li{padding:4px 0;border-bottom:1px dashed var(--brd2)}
.lead-log .li:last-child{border-bottom:0}
.act-row{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}
.cc-limit-bar{height:6px;border-radius:3px;background:var(--brd2);margin-top:6px;overflow:hidden}
.cc-limit-fill{height:100%;border-radius:3px;transition:width .3s,background .3s}
.miniform{background:var(--bg3);border-radius:12px;padding:14px;margin-top:10px;border:1px solid var(--brd1)}
/* ── Stepper (realistic lead stages) ── */
.stepper{display:flex;align-items:center;margin:4px 0 12px;flex-wrap:wrap;gap:4px}
.stp{display:flex;align-items:center;gap:7px}
.stp-n{width:26px;height:26px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:800;font-size:12px;border:2px solid var(--brd2);color:var(--mu);background:var(--bg2);flex-shrink:0}
.stp-l{font-size:12px;font-weight:700;color:var(--mu);white-space:nowrap}
.stp.done .stp-n{background:#22a06b;border-color:#22a06b;color:#fff}
.stp.done .stp-l{color:var(--tx)}
.stp.cur .stp-n{background:#7b68ee;border-color:#7b68ee;color:#fff;box-shadow:0 0 0 4px rgba(123,104,238,.18)}
.stp.cur .stp-l{color:#5b46d6;font-weight:800}
.stp-bar{flex:1;height:2px;background:var(--brd2);margin:0 8px;min-width:16px}
/* ── Quick-outcome action panel ── */
.qo-wrap{margin-top:10px;background:rgba(123,104,238,.04);border:1px solid rgba(123,104,238,.18);border-radius:11px;padding:11px 13px}
.qo-title{font-size:12px;font-weight:700;color:var(--mu);margin-bottom:8px}
.qo-row{display:flex;flex-wrap:wrap;gap:7px;margin-bottom:9px}
.qo{padding:8px 13px;border-radius:9px;border:1.5px solid var(--brd1);background:var(--bg2);color:var(--tx);font-size:12.5px;font-weight:700;cursor:pointer;transition:.13s}
.qo:hover{border-color:#7b68ee;color:#5b46d6;background:rgba(123,104,238,.08)}
.qo-note{height:34px;font-size:12.5px;margin-bottom:9px;width:100%}
.qo-final{display:flex;gap:7px;flex-wrap:wrap}
/* ── Classification report cards + tabs ── */
.lead-stats{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:12px}
.lst-card{flex:1;min-width:150px;display:flex;align-items:center;gap:11px;padding:12px 15px;border-radius:13px;border:1px solid var(--card-brd);background:var(--card-bg);cursor:pointer;transition:.13s}
.lst-card:hover{border-color:#7b68ee;transform:translateY(-1px)}
.lst-card.on{box-shadow:0 0 0 2px rgba(123,104,238,.45)}
.lst-i{width:40px;height:40px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:19px;flex-shrink:0}
.lst-v{font-size:1.45rem;font-weight:900;line-height:1;font-family:'JetBrains Mono',monospace}
.lst-l{font-size:11px;color:var(--mu);margin-top:3px;font-weight:700}
.lead-tabs{display:flex;gap:7px;flex-wrap:wrap;margin-bottom:14px}
.ltab{padding:8px 16px;border-radius:10px;border:1.5px solid var(--brd1);background:var(--bg2);color:var(--tx);font-size:12.5px;font-weight:800;cursor:pointer;transition:.13s}
.ltab:hover{border-color:#7b68ee}
.ltab.on{background:linear-gradient(135deg,#7b68ee,#5b46d6);border-color:transparent;color:#fff}
/* ── Compact leads table ── */
.lead-table{width:100%;min-width:0}
.lead-table td{vertical-align:middle}
.lead-phone-cell{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--pri2);white-space:nowrap}
.lead-act{display:flex;gap:7px;flex-wrap:nowrap;white-space:nowrap;justify-content:flex-start}
.lact{padding:8px 14px;border-radius:9px;border:1.5px solid var(--brd1);background:var(--bg2);color:var(--tx);cursor:pointer;font-size:12.5px;font-weight:800;display:inline-flex;align-items:center;gap:6px;transition:.12s;white-space:nowrap;line-height:1}
.lact:hover{transform:translateY(-1px)}
.lact.call{border-color:rgba(123,104,238,.4);color:#5b46d6}
.lact.call:hover{background:#7b68ee;color:#fff;border-color:#7b68ee}
.lact.open{border-color:rgba(34,160,107,.4);color:#0f8a52}
.lact.open:hover{background:#22a06b;color:#fff;border-color:#22a06b}
.lact.dec{border-color:rgba(220,53,69,.4);color:#dc3545}
.lact.dec:hover{background:#dc3545;color:#fff;border-color:#dc3545}
.lact.log{border-color:rgba(26,173,186,.4);color:var(--pri2)}
.lact.log:hover{background:var(--pri);color:#fff;border-color:var(--pri)}
.lead-table th:last-child,.lead-table td:last-child{min-width:340px}
.qo-modal-row{display:flex;flex-wrap:wrap;gap:8px;margin-bottom:10px}
</style>

<div class="panel" style="max-width:100%;width:100%">
  <div class="panel-header">
    <div class="panel-title">
      <span id="fu-panel-title">🕒 العملاء قيد المتابعة</span>
      <span id="lead-count" style="background:#7b68ee;color:#fff;border-radius:20px;padding:2px 10px;font-size:12px;margin-right:8px">0</span>
    </div>
    <button class="btn btn-ghost btn-sm" id="fu-refresh-btn" onclick="loadLeads()">🔄 تحديث</button>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    {{-- Classification reports (counts by category) — visible to branch + CC manager + Finance Admin --}}
    <div class="lead-stats" id="lead-stats"></div>
    {{-- Category tabs --}}
    <div class="lead-tabs" id="lead-tabs"></div>

    <div id="lead-list"></div>
  </div>
</div>

{{-- ══ Follow-up note modal ══ --}}
<div class="modal-overlay" id="modal-fu" style="display:none">
  <div class="modal" style="max-width:480px">
    <div class="modal-header"><div class="modal-title" id="fu-title">🔄 تحديث متابعة العميل</div><button class="modal-close" onclick="closeFu()">✕</button></div>
    <div class="modal-body">
      <div id="fu-err" class="alert alert-error"></div>
      <div id="fu-hint" style="font-size:12px;color:var(--mu);margin-bottom:8px">العميل لا يزال قيد المتابعة — اكتب ما تمّ في المكالمة. يبقى في القائمة.</div>
      <textarea id="fu-note" class="form-control" rows="3" placeholder="مثال: لم يرد / طلب معاودة الاتصال غدًا / يفكّر في المبلغ…"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="fu-cancel-btn" onclick="closeFu()">إلغاء</button>
      <button class="btn btn-primary" id="fu-save-btn" onclick="submitFu()">حفظ التحديث</button>
    </div>
  </div>
</div>

{{-- ══ Decline modal ══ --}}
<div class="modal-overlay" id="modal-dec" style="display:none">
  <div class="modal" style="max-width:480px">
    <div class="modal-header"><div class="modal-title" id="dec-title">🚫 العميل رفض نهائيًا</div><button class="modal-close" onclick="closeDec()">✕</button></div>
    <div class="modal-body">
      <div id="dec-err" class="alert alert-error"></div>
      <div id="dec-hint" style="font-size:12px;color:var(--mu);margin-bottom:8px">سيخرج العميل من قائمة المتابعة نهائيًا. اكتب السبب.</div>
      <textarea id="dec-reason" class="form-control" rows="3" placeholder="مثال: طلب عدم الاتصال به مجددًا"></textarea>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="dec-cancel-btn" onclick="closeDec()">إلغاء</button>
      <button class="btn btn-danger" id="dec-ok-btn" onclick="submitDec()">تأكيد الرفض النهائي</button>
    </div>
  </div>
</div>

{{-- ══ Call-log modal ══ --}}
<div class="modal-overlay" id="modal-log" style="display:none">
  <div class="modal" style="max-width:520px">
    <div class="modal-header"><div class="modal-title" id="log-title">📞 سجل متابعة العميل</div><button class="modal-close" onclick="closeLog()">✕</button></div>
    <div class="modal-body"><div id="log-body"></div></div>
    <div class="modal-footer"><button class="btn btn-ghost" id="log-close-btn" onclick="closeLog()">إغلاق</button></div>
  </div>
</div>

{{-- ══ Record call-outcome modal (quick outcomes) ══ --}}
<div class="modal-overlay" id="modal-outcome" style="display:none">
  <div class="modal" style="max-width:460px">
    <div class="modal-header"><div class="modal-title" id="out-title">📞 نتيجة المكالمة</div><button class="modal-close" onclick="closeOutcome()">✕</button></div>
    <div class="modal-body">
      <div id="out-err" class="alert alert-error"></div>
      <div id="out-phone" style="font-weight:800;direction:ltr;margin-bottom:12px;color:var(--pri2)"></div>
      <div id="out-pick" class="qo-title" style="font-size:12px;font-weight:700;color:var(--mu);margin-bottom:8px">اختر نتيجة سريعة:</div>
      <div class="qo-modal-row" id="out-qo-row"></div>
      <input id="out-note" class="form-control qo-note" placeholder="ملاحظة إضافية عن المكالمة (اختياري)…">
      <div id="out-foot" style="font-size:11px;color:var(--mu);margin-top:8px">لفتح الحساب أو رفض العميل استخدم الأزرار المخصّصة في الجدول (✅ / 🚫).</div>
    </div>
  </div>
</div>

{{-- ══ Open-account modal (deferred until the customer agrees) ══ --}}
<div class="modal-overlay" id="modal-open" style="display:none">
  <div class="modal" style="max-width:700px">
    <div class="modal-header"><div class="modal-title" id="open-title">🏁 فتح الحساب</div><button class="modal-close" onclick="closeAccount()">✕</button></div>
    <div class="modal-body"><div id="open-body"></div></div>
  </div>
</div>

@endsection

@push('scripts')
<script>
const ME = { branch: {{ $myBranch ?? 'null' }}, isFa: {{ $isFa ? 'true' : 'false' }} };
let _leads = [], _view = 'active', _stats = {};

/* ─── AR / EN dictionary ─────────────────────────────── */
const FUT = {
  ar:{
    tb:'العملاء قيد المتابعة',
    panel:'🕒 العملاء قيد المتابعة', refresh:'🔄 تحديث',
    fuTitle:'🔄 تحديث متابعة العميل', fuHint:'العميل لا يزال قيد المتابعة — اكتب ما تمّ في المكالمة. يبقى في القائمة.',
    fuPh:'مثال: لم يرد / طلب معاودة الاتصال غدًا / يفكّر في المبلغ…',
    cancel:'إلغاء', fuSave:'حفظ التحديث',
    decTitle:'🚫 العميل رفض نهائيًا', decHint:'سيخرج العميل من قائمة المتابعة نهائيًا. اكتب السبب.',
    decPh:'مثال: طلب عدم الاتصال به مجددًا', decOk:'تأكيد الرفض النهائي',
    logTitle:'📞 سجل متابعة العميل', close:'إغلاق',
    outTitle:'📞 نتيجة المكالمة', outPick:'اختر نتيجة سريعة:',
    outQo:[{v:'📵 لم يرد',t:'📵 لم يرد'},{v:'⏳ مشغول',t:'⏳ مشغول'},{v:'🔁 طلب معاودة الاتصال',t:'🔁 طلب معاودة'},{v:'🤔 مهتم — يفكّر',t:'🤔 مهتم يفكّر'}],
    outPh:'ملاحظة إضافية عن المكالمة (اختياري)…',
    outFoot:'لفتح الحساب أو رفض العميل استخدم الأزرار المخصّصة في الجدول (✅ / 🚫).',
    openTitle:'🏁 فتح الحساب',
    sInprog:'🔄 قيد المتابعة', sAccepted:'✅ مقبول', sNew:'📩 جديد — لم يُتابَع بعد',
    oOpened:'🏁 فُتح الحساب', oDeclined:'🚫 رفض نهائي', oReassigned:'↩️ أُعيد', oFollowup:'🔄 متابعة',
    stepArrived:'وصل العميل', stepCalling:'الاتصال والمتابعة', stepResult:'النتيجة', stepOpened:'✅ فُتح الحساب', stepDeclined:'🚫 رفض نهائي',
    catActive:'🔄 قيد المتابعة', catOpened:'🏁 فتحوا حساب', catDeclined:'🚫 رفضوا',
    catActiveShort:'قيد المتابعة', catOpenedShort:'فتحوا حساب', catDeclinedShort:'رفضوا / لا تتصل',
    emptyActive:'لا يوجد عملاء قيد المتابعة حاليًا', emptyOpened:'لا يوجد عملاء فتحوا حسابًا بعد', emptyDeclined:'لا يوجد عملاء مرفوضون',
    thPhone:'📱 هاتف العميل', thSource:'المصدر', thBranch:'الفرع', thAgent:'موظف CC',
    thLast:'آخر متابعة', thStatus:'الحالة', thAction:'الإجراء',
    tryN:(n)=>`محاولة ${n}`, notFollowed:'— لم يُتابَع بعد',
    opened:'🏁 فُتح', declinedFinal:'🚫 رفض نهائي', inFollow:'🔄 قيد المتابعة',
    actCall:'📞 نتيجة المكالمة', actOpen:'✅ فتح الحساب', actDec:'🚫 رفض', actLog:'📋 السجل', actLogFull:'📋 سجل المتابعة',
    noLog:'لا توجد تحديثات بعد',
    tossAcc:'اكتب ملخص ما تمّ مع العميل.', tossDec:'اكتب سبب رفض العميل.',
    tossSaved:(l)=>'تم تسجيل: '+l, tossErrorSave:'تعذّر حفظ التحديث', tossGeneric:'خطأ',
    openHdr:'🏁 فتح الحساب — أدخل البيانات',
    lblAcc:'رقم الحساب * <span style="font-size:10px;color:#dc3545">(6 أرقام بالضبط)</span>', phAcc:'مثال: 123456',
    lblDep:'إيداع فتح الحساب *', lblBroker:'البروكر * <span style="font-size:10px;color:#dc3545">(مطلوب)</span>',
    optSelect:'— اختر —', optNone:'— لا يوجد —',
    lblBComm:'عمولة البروكر ($)', lblMktr:'المسوّق الرئيسي', lblMComm:'عمولة المسوّق ($)',
    totalLine:'إجمالي (بروكر + مسوّق)', overCap:'⛔ يتجاوز الحد المسموح (5$)',
    lblExt1:'مسوّق إضافي 1', lblEc1:'عمولة الإضافي 1 ($)',
    lblExt2:'مسوّق إضافي 2', lblEc2:'عمولة الإضافي 2 ($)',
    lblRef:'🔗 رقم حساب المُحيل <span style="font-size:10px;color:var(--mu)">(6 أرقام)</span>',
    lblRefC:'عمولة الإحالة ($)', lblMon:'الإيداع الشهري المتوقع',
    lblForex:'Forex Commission ($)', lblFut:'Futures Commission ($)',
    btnOpenOk:'🏁 تأكيد فتح الحساب',
    selfOpt:'🔷 Self (الفرع)',
    errAcc:'⚠️ رقم الحساب يجب أن يكون 6 أرقام بالضبط.',
    errBroker:'⚠️ اختر البروكر أو Self.',
    errRef:'⚠️ رقم حساب المُحيل يجب أن يكون 6 أرقام.',
    errBranchCap:(t)=>`⛔ إجمالي عمولات الفرع (${t}$) — البروكر + المسوّقون + الإحالة — يتجاوز الحد (5$).`,
  },
  en:{
    tb:'Clients in Follow-up',
    panel:'🕒 Clients in Follow-up', refresh:'🔄 Refresh',
    fuTitle:'🔄 Update Client Follow-up', fuHint:'This client is still in follow-up — write what happened on the call. The lead stays in the list.',
    fuPh:'e.g. No answer / Call-back tomorrow / Thinking about the amount…',
    cancel:'Cancel', fuSave:'Save Update',
    decTitle:'🚫 Client Declined Permanently', decHint:'The lead will be removed from the follow-up list permanently. Enter the reason.',
    decPh:'e.g. Asked not to be contacted again', decOk:'Confirm Permanent Decline',
    logTitle:'📞 Client Follow-up Log', close:'Close',
    outTitle:'📞 Call Outcome', outPick:'Pick a quick outcome:',
    outQo:[{v:'📵 لم يرد',t:'📵 No answer'},{v:'⏳ مشغول',t:'⏳ Busy'},{v:'🔁 طلب معاودة الاتصال',t:'🔁 Asked to call back'},{v:'🤔 مهتم — يفكّر',t:'🤔 Interested — thinking'}],
    outPh:'Extra note about the call (optional)…',
    outFoot:'To open the account or decline the lead, use the dedicated buttons in the table (✅ / 🚫).',
    openTitle:'🏁 Open Account',
    sInprog:'🔄 In Follow-up', sAccepted:'✅ Accepted', sNew:'📩 New — not followed up yet',
    oOpened:'🏁 Account opened', oDeclined:'🚫 Final decline', oReassigned:'↩️ Reassigned', oFollowup:'🔄 Follow-up',
    stepArrived:'Lead arrived', stepCalling:'Call & follow-up', stepResult:'Result', stepOpened:'✅ Account opened', stepDeclined:'🚫 Final decline',
    catActive:'🔄 In Follow-up', catOpened:'🏁 Opened Account', catDeclined:'🚫 Declined',
    catActiveShort:'in follow-up', catOpenedShort:'opened account', catDeclinedShort:'declined / do not call',
    emptyActive:'No clients in follow-up right now', emptyOpened:'No clients have opened an account yet', emptyDeclined:'No declined clients',
    thPhone:'📱 Client Phone', thSource:'Source', thBranch:'Branch', thAgent:'CC Agent',
    thLast:'Last Follow-up', thStatus:'Status', thAction:'Action',
    tryN:(n)=>`attempt ${n}`, notFollowed:'— not followed up yet',
    opened:'🏁 Opened', declinedFinal:'🚫 Final decline', inFollow:'🔄 In Follow-up',
    actCall:'📞 Call Outcome', actOpen:'✅ Open Account', actDec:'🚫 Decline', actLog:'📋 Log', actLogFull:'📋 Follow-up Log',
    noLog:'No updates yet',
    tossAcc:'Enter a short summary of what happened with the client.', tossDec:'Enter the reason the client declined.',
    tossSaved:(l)=>'Recorded: '+l, tossErrorSave:'Could not save the update', tossGeneric:'Error',
    openHdr:'🏁 Open Account — Enter Data',
    lblAcc:'Account Number * <span style="font-size:10px;color:#dc3545">(exactly 6 digits)</span>', phAcc:'e.g. 123456',
    lblDep:'Opening Deposit *', lblBroker:'Broker * <span style="font-size:10px;color:#dc3545">(required)</span>',
    optSelect:'— Select —', optNone:'— None —',
    lblBComm:'Broker Commission ($)', lblMktr:'Main Marketer', lblMComm:'Marketer Commission ($)',
    totalLine:'Total (Broker + Marketer)', overCap:'⛔ Over the $5 cap',
    lblExt1:'Additional Marketer 1', lblEc1:'Additional 1 Commission ($)',
    lblExt2:'Additional Marketer 2', lblEc2:'Additional 2 Commission ($)',
    lblRef:'🔗 Referrer Account Number <span style="font-size:10px;color:var(--mu)">(6 digits)</span>',
    lblRefC:'Referral Commission ($)', lblMon:'Expected Monthly Deposit',
    lblForex:'Forex Commission ($)', lblFut:'Futures Commission ($)',
    btnOpenOk:'🏁 Confirm Open Account',
    selfOpt:'🔷 Self (the branch)',
    errAcc:'⚠️ Account number must be exactly 6 digits.',
    errBroker:'⚠️ Select a broker or Self.',
    errRef:'⚠️ Referrer account must be 6 digits.',
    errBranchCap:(t)=>`⛔ Branch total commissions ($${t}) — broker + marketers + referral — exceeds the $5 cap.`,
  }
};
function fuL(){ return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function fuT(k){ var l=fuL(); return (FUT[l]&&FUT[l][k])||FUT.ar[k]||k; }
function _fuSet(id,v){ var e=document.getElementById(id); if(e) e.textContent=v; }
function _fuHtml(id,v){ var e=document.getElementById(id); if(e) e.innerHTML=v; }

function fuApplyLang(){
  _fuSet('fu-panel-title', fuT('panel'));
  _fuSet('fu-refresh-btn', fuT('refresh'));
  _fuSet('fu-title', fuT('fuTitle'));  _fuSet('fu-hint', fuT('fuHint'));
  var fp=document.getElementById('fu-note'); if(fp) fp.placeholder=fuT('fuPh');
  _fuSet('fu-cancel-btn', fuT('cancel')); _fuSet('fu-save-btn', fuT('fuSave'));
  _fuSet('dec-title', fuT('decTitle')); _fuSet('dec-hint', fuT('decHint'));
  var dp=document.getElementById('dec-reason'); if(dp) dp.placeholder=fuT('decPh');
  _fuSet('dec-cancel-btn', fuT('cancel')); _fuSet('dec-ok-btn', fuT('decOk'));
  _fuSet('log-title', fuT('logTitle')); _fuSet('log-close-btn', fuT('close'));
  _fuSet('out-title', fuT('outTitle')); _fuSet('out-pick', fuT('outPick')); _fuSet('out-foot', fuT('outFoot'));
  var on=document.getElementById('out-note'); if(on) on.placeholder=fuT('outPh');
  var qr=document.getElementById('out-qo-row'); if(qr){
    qr.innerHTML = fuT('outQo').map(function(o){
      return '<button class="qo" onclick="chooseOutcome(\''+o.v.replace(/'/g,"\\'")+'\')">'+o.t+'</button>';
    }).join('');
  }
  _fuSet('open-title', fuT('openTitle'));
  var tb=document.querySelector('.tb-title'); if(tb) tb.textContent=fuT('tb');
  renderStats(); renderTabs(); render();
}
var _fuOrig=window.applyLang; window.applyLang=function(l){ if(_fuOrig)_fuOrig(l); try{ fuApplyLang(); }catch(e){ console.warn(e); } };

const _e = s => String(s==null?'':s).replace(/[&<>"]/g, m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m]));
function showAlert(type,msg){const e=document.getElementById('alert-err'),o=document.getElementById('alert-ok');e.classList.remove('show');o.classList.remove('show');const t=type==='err'?e:o;t.textContent=msg;t.classList.add('show');window.scrollTo(0,0);}

function phoneOf(c){ const cc=(c.client_country_code||'').trim(); const p=(c.client_phone||'').trim(); return p?((cc?cc+' ':'')+p):'—'; }
function canAct(c){ return ME.isFa || (ME.branch && Number(c.branch_id)===Number(ME.branch)); }
function statusLabel(s){ return s==='in_progress'?fuT('sInprog'):(s==='accepted'?fuT('sAccepted'):fuT('sNew')); }

function fmtDate(d){ if(!d) return ''; try{ const x=new Date(d); return x.toLocaleString(fuL()==='en'?'en-GB':'ar-EG',{day:'2-digit',month:'2-digit',hour:'2-digit',minute:'2-digit'}); }catch(e){ return d; } }
function outcomeLabel(o){ return o==='opened'?fuT('oOpened'):o==='declined'?fuT('oDeclined'):o==='reassigned'?fuT('oReassigned'):fuT('oFollowup'); }

// Realistic 3-stage pipeline. Account opening is the LAST stage — never shown up front.
function stepper(c){
  const done3 = (c.cc_status==='completed' || c.cc_status==='declined');
  const s1 = 'done';                                   // lead arrived (it's here)
  const s2 = done3 ? 'done' : 'cur';                   // call & follow-up
  const s3 = done3 ? 'done' : 'todo';                  // result (open / decline)
  const l3 = c.cc_status==='completed' ? fuT('stepOpened') : c.cc_status==='declined' ? fuT('stepDeclined') : fuT('stepResult');
  const step = (n,l,st) => `<div class="stp ${st}"><span class="stp-n">${st==='done'?'✓':n}</span><span class="stp-l">${l}</span></div>`;
  return `<div class="stepper">${step(1,fuT('stepArrived'),s1)}<span class="stp-bar"></span>${step(2,fuT('stepCalling'),s2)}<span class="stp-bar"></span>${step(3,l3,s3)}</div>`;
}

function _CAT(){ return { active:{label:fuT('catActive'),color:'#7b68ee'}, opened:{label:fuT('catOpened'),color:'#22a06b'}, declined:{label:fuT('catDeclined'),color:'#dc3545'} }; }
function renderStats(){
  const s=_stats||{};
  const card=(v,ic,bg,col,val,l)=>`<div class="lst-card${_view===v?' on':''}" onclick="loadLeads('${v}')"><div class="lst-i" style="background:${bg}">${ic}</div><div><div class="lst-v" style="color:${col}">${(val||0).toLocaleString('en')}</div><div class="lst-l">${l}</div></div></div>`;
  const el=document.getElementById('lead-stats'); if(!el) return;
  el.innerHTML=
    card('active','🔄','rgba(245,168,40,.14)','#f5a828',s.in_followup,fuT('catActiveShort'))+
    card('opened','🏁','rgba(34,160,107,.14)','#22a06b',s.opened,fuT('catOpenedShort'))+
    card('declined','🚫','rgba(220,53,69,.14)','#dc3545',s.declined,fuT('catDeclinedShort'));
}
function renderTabs(){
  const el=document.getElementById('lead-tabs'); if(!el) return;
  const CAT=_CAT();
  el.innerHTML=Object.keys(CAT).map(v=>
    `<button class="ltab${_view===v?' on':''}" onclick="loadLeads('${v}')">${CAT[v].label}</button>`).join('');
}

// One compact TABLE ROW per lead. The action(s) sit in the last cell; heavier actions
// (record outcome / open account) open a focused modal so the table stays clean.
function rowHtml(c,n){
  const last=(c.cc_followups&&c.cc_followups.length)?c.cc_followups[0]:null;
  const tries=(c.cc_followups||[]).length;
  const follow = last
    ? `<div>${outcomeLabel(last.outcome)} ${tries>1?`<span style="color:var(--mu);font-size:10px">(${fuT('tryN')(tries)})</span>`:''}</div>`
      +`<div style="font-size:10.5px;color:var(--mu)">${_e(last.note||'')} · ${fmtDate(last.created_at)}</div>`
    : `<span style="color:var(--mu)">${fuT('notFollowed')}</span>`;
  const branchName = fuL()==='en' ? (c.branch?.name_en || c.branch?.name_ar || '—') : (c.branch?.name_ar || c.branch?.name_en || '—');
  let statusCell, actionCell;
  if(c.cc_status==='completed'){
    statusCell=`<span class="lead-chip" style="background:rgba(34,160,107,.14);color:#22a06b">${fuT('opened')} #${_e(c.account_number||'—')}</span>`;
    actionCell=`<button class="lact log" onclick="showLog(${c.id})">${fuT('actLogFull')}</button>`;
  } else if(c.cc_status==='declined'){
    statusCell=`<span class="lead-chip" style="background:rgba(220,53,69,.14);color:#dc3545" title="${_e(c.cc_rejection_reason||'')}">${fuT('declinedFinal')}</span>`;
    actionCell=`<button class="lact log" onclick="showLog(${c.id})">${fuT('actLogFull')}</button>`;
  } else {
    statusCell=`<span class="lead-chip in_progress">${fuT('inFollow')}</span>`;
    actionCell = canAct(c)
      ? `<button class="lact call" onclick="openOutcome(${c.id})">${fuT('actCall')}</button>`
       +`<button class="lact open" onclick="openAccount(${c.id})">${fuT('actOpen')}</button>`
       +`<button class="lact dec" onclick="openDec(${c.id})">${fuT('actDec')}</button>`
       +`<button class="lact log" onclick="showLog(${c.id})">${fuT('actLog')}</button>`
      : `<button class="lact log" onclick="showLog(${c.id})">${fuT('actLogFull')}</button>`;
  }
  return `<tr>
    <td style="color:var(--mu);font-size:11px">${n}</td>
    <td class="lead-phone-cell" dir="ltr">📱 ${_e(phoneOf(c))}</td>
    <td>${_e(c.client_source||'—')}</td>
    <td style="font-weight:600">${_e(branchName)}</td>
    <td>${_e(c.cc_agent?.name ?? '—')}</td>
    <td style="min-width:150px">${follow}</td>
    <td>${statusCell}</td>
    <td><div class="lead-act">${actionCell}</div></td>
  </tr>`;
}

function render(){
  const box=document.getElementById('lead-list'); if(!box) return;
  const lc=document.getElementById('lead-count'); if(lc) lc.textContent=_leads.length;
  if(!_leads.length){
    const msg = _view==='opened'?fuT('emptyOpened'):_view==='declined'?fuT('emptyDeclined'):fuT('emptyActive');
    box.innerHTML=`<div style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:40px">✅</div>${msg}</div>`;
    return;
  }
  const rows=_leads.map((c,i)=>rowHtml(c,i+1)).join('');
  box.innerHTML=`<div class="table-scroll"><table class="data-table lead-table"><thead><tr>`
    +`<th>#</th><th>${fuT('thPhone')}</th><th>${fuT('thSource')}</th><th>${fuT('thBranch')}</th><th>${fuT('thAgent')}</th><th>${fuT('thLast')}</th><th>${fuT('thStatus')}</th><th>${fuT('thAction')}</th>`
    +`</tr></thead><tbody>${rows}</tbody></table></div>`;
  return;
}

async function loadLeads(view){
  if(view)_view=view;
  const r=await api('GET','/cc/leads?view='+encodeURIComponent(_view));
  _leads = r.success ? (r.data||[]) : [];
  if(r.success && r.stats)_stats=r.stats;
  renderStats(); renderTabs(); render();
}

/* ── Record call-outcome modal ── */
let _outId=null;
function openOutcome(id){
  _outId=id; const c=_leads.find(x=>x.id===id);
  document.getElementById('out-phone').textContent='📱 '+(c?phoneOf(c):'');
  document.getElementById('out-note').value='';
  document.getElementById('out-err').classList.remove('show');
  document.getElementById('modal-outcome').style.display='flex';
}
function closeOutcome(){ document.getElementById('modal-outcome').style.display='none'; _outId=null; }
async function chooseOutcome(label){
  if(!_outId) return;
  const extra=(document.getElementById('out-note').value||'').trim();
  const note=extra?(label+' — '+extra):label;
  const r=await api('PUT',`/cc/cards/${_outId}/followup`,{note});
  if(r.success){ closeOutcome(); showAlert('ok', fuT('tossSaved')(label)); loadLeads(); }
  else { const e=document.getElementById('out-err'); e.textContent=(r&&r.message)||fuT('tossErrorSave'); e.classList.add('show'); }
}

/* ── Open-account modal ── */
function openAccount(id){
  const body=document.getElementById('open-body');
  body.innerHTML=openFormHtml(id);
  populateEmps(id);
  document.getElementById('modal-open').style.display='flex';
}
function closeAccount(){ document.getElementById('modal-open').style.display='none'; document.getElementById('open-body').innerHTML=''; }

/* ── Follow-up note (free text) ── */
let _fuId=null;
function openFu(id){ _fuId=id; document.getElementById('fu-note').value=''; document.getElementById('fu-err').classList.remove('show'); document.getElementById('modal-fu').style.display='flex'; }
function closeFu(){ document.getElementById('modal-fu').style.display='none'; _fuId=null; }
async function submitFu(){
  const note=document.getElementById('fu-note').value.trim();
  if(note.length<2){ const e=document.getElementById('fu-err'); e.textContent=fuT('tossAcc'); e.classList.add('show'); return; }
  const r=await api('PUT',`/cc/cards/${_fuId}/followup`,{note});
  if(r.success){ closeFu(); showAlert('ok',r.message); loadLeads(); }
  else { const e=document.getElementById('fu-err'); e.textContent=r.message||fuT('tossGeneric'); e.classList.add('show'); }
}

/* ── Decline ── */
let _decId=null;
function openDec(id){ _decId=id; document.getElementById('dec-reason').value=''; document.getElementById('dec-err').classList.remove('show'); document.getElementById('modal-dec').style.display='flex'; }
function closeDec(){ document.getElementById('modal-dec').style.display='none'; _decId=null; }
async function submitDec(){
  const reason=document.getElementById('dec-reason').value.trim();
  if(reason.length<2){ const e=document.getElementById('dec-err'); e.textContent=fuT('tossDec'); e.classList.add('show'); return; }
  const r=await api('PUT',`/cc/cards/${_decId}/decline`,{reason});
  if(r.success){ closeDec(); showAlert('ok',r.message); loadLeads(); }
  else { const e=document.getElementById('dec-err'); e.textContent=r.message||fuT('tossGeneric'); e.classList.add('show'); }
}

/* ── Call log ── */
function showLog(id){
  const c=_leads.find(x=>x.id===id); const body=document.getElementById('log-body');
  const logs=(c&&c.cc_followups)||[];
  body.innerHTML = logs.length
    ? `<div class="lead-log">${logs.map(l=>`<div class="li">${outcomeLabel(l.outcome)} — ${_e(l.note||'')}<div style="color:var(--mu);font-size:11px;margin-top:2px">${fmtDate(l.created_at)}${l.user?(' · '+_e(l.user.name)):''}</div></div>`).join('')}</div>`
    : `<div style="color:var(--mu);text-align:center;padding:20px">${fuT('noLog')}</div>`;
  document.getElementById('modal-log').style.display='flex';
}
function closeLog(){ document.getElementById('modal-log').style.display='none'; }

/* ── Open-account (complete) inline form ── */
function toggleOpen(id){
  const box=document.getElementById(`open-${id}`);
  if(box.style.display==='block'){ box.style.display='none'; return; }
  box.style.display='block';
  box.innerHTML=openFormHtml(id);
  populateEmps(id);
}
function openFormHtml(id){
  return `
  <div class="miniform" id="of-${id}">
    <div style="font-weight:700;margin-bottom:10px;color:var(--pri2)">${fuT('openHdr')}</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${fuT('lblAcc')}</label>
        <input type="text" id="of-acc-${id}" class="form-control" inputmode="numeric" maxlength="6" placeholder="${fuT('phAcc')}" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)">
      </div>
      <div class="form-group">
        <label class="form-label">${fuT('lblDep')}</label>
        <input type="number" id="of-dep-${id}" class="form-control" value="0" min="0" step="any">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${fuT('lblBroker')}</label>
        <select id="of-broker-${id}" class="form-control" onchange="ofBk(${id})"><option value="">${fuT('optSelect')}</option></select>
      </div>
      <div class="form-group">
        <label class="form-label">${fuT('lblBComm')}</label>
        <input type="number" id="of-bc-${id}" class="form-control" value="0" min="0" max="5" step="any" oninput="ofLimit(${id})">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${fuT('lblMktr')}</label>
        <select id="of-mktr-${id}" class="form-control" onchange="ofMk(${id})"><option value="">${fuT('optNone')}</option></select>
      </div>
      <div class="form-group">
        <label class="form-label">${fuT('lblMComm')}</label>
        <input type="number" id="of-mc-${id}" class="form-control" value="0" min="0" max="5" step="any" oninput="ofLimit(${id})" disabled>
      </div>
    </div>
    <div style="margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px"><span>${fuT('totalLine')}</span><span id="of-tot-${id}" style="font-weight:700">0.0$ / 5.0$</span></div>
      <div class="cc-limit-bar"><div id="of-bar-${id}" class="cc-limit-fill" style="width:0%;background:#198754"></div></div>
      <div id="of-warn-${id}" style="font-size:11px;color:#dc3545;margin-top:4px;display:none">${fuT('overCap')}</div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">${fuT('lblExt1')}</label><select id="of-ext1-${id}" class="form-control"><option value="">${fuT('optNone')}</option></select></div>
      <div class="form-group"><label class="form-label">${fuT('lblEc1')}</label><input type="number" id="of-ec1-${id}" class="form-control" value="0" min="0" step="any"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">${fuT('lblExt2')}</label><select id="of-ext2-${id}" class="form-control"><option value="">${fuT('optNone')}</option></select></div>
      <div class="form-group"><label class="form-label">${fuT('lblEc2')}</label><input type="number" id="of-ec2-${id}" class="form-control" value="0" min="0" step="any"></div>
    </div>
    <div class="form-row" style="background:rgba(245,168,40,.07);border:1px solid rgba(245,168,40,.25);border-radius:9px;padding:12px">
      <div class="form-group"><label class="form-label">${fuT('lblRef')}</label><input type="text" id="of-ref-${id}" class="form-control" inputmode="numeric" maxlength="6" placeholder="000000" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)"></div>
      <div class="form-group"><label class="form-label">${fuT('lblRefC')}</label><input type="number" id="of-refc-${id}" class="form-control" value="0" min="0" step="any"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">${fuT('lblMon')}</label><input type="number" id="of-mon-${id}" class="form-control" value="0" min="0" step="any"></div>
      <div class="form-group"><label class="form-label">${fuT('lblForex')}</label><input type="number" id="of-forex-${id}" class="form-control" value="0" min="0" step="any"></div>
    </div>
    <div class="form-row">
      <div class="form-group"><label class="form-label">${fuT('lblFut')}</label><input type="number" id="of-fut-${id}" class="form-control" value="0" min="0" step="any"></div>
      <div class="form-group"></div>
    </div>
    <div id="of-err-${id}" class="alert alert-error"></div>
    <button class="btn btn-primary" onclick="submitOpen(${id})">${fuT('btnOpenOk')}</button>
  </div>`;
}
// Broker: Self (or empty) → no commission field.
function ofBk(id){ const s=document.getElementById(`of-broker-${id}`),i=document.getElementById(`of-bc-${id}`); const self=(s.value===''||s.value==='self'); i.disabled=self; if(self)i.value='0'; ofLimit(id); }
// Marketer: enabled only for a REAL employee (Self / none → 0).
function ofMk(id){ const s=document.getElementById(`of-mktr-${id}`),i=document.getElementById(`of-mc-${id}`); const real=(s.value&&s.value!=='self'); i.disabled=!real; if(!real)i.value='0'; ofLimit(id); }
function ofLimit(id){
  const b=parseFloat(document.getElementById(`of-bc-${id}`)?.value||0)||0;
  const m=parseFloat(document.getElementById(`of-mc-${id}`)?.value||0)||0;
  const t=b+m, pct=Math.min(t/5*100,100);
  const bar=document.getElementById(`of-bar-${id}`),lbl=document.getElementById(`of-tot-${id}`),w=document.getElementById(`of-warn-${id}`);
  if(!bar)return; bar.style.width=pct+'%'; bar.style.background=t>5?'#dc3545':t>3.5?'#fd7e14':'#198754';
  lbl.textContent=t.toFixed(1)+'$ / 5.0$'; lbl.style.color=t>5?'#dc3545':'inherit'; w.style.display=t>5?'block':'none';
}
function populateEmps(id){
  const emps=window._ccEmployees||[];
  ['broker','mktr','ext1','ext2'].forEach(slot=>{
    const sel=document.getElementById(`of-${slot}-${id}`); if(!sel)return;
    const ph=sel.options[0]?.textContent??'—'; sel.innerHTML=`<option value="">${ph}</option>`;
    // "Self" (the branch itself, no external person → 0 commission) — available for ALL branches.
    if(slot==='broker'||slot==='mktr') sel.innerHTML+=`<option value="self">${fuT('selfOpt')}</option>`;
    emps.forEach(e=>{const o=document.createElement('option');o.value=e.id;o.textContent=e.name+(e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦');sel.appendChild(o);});
  });
  ofMk(id);
}
async function submitOpen(id){
  const err=document.getElementById(`of-err-${id}`); err.classList.remove('show');
  const acc=(document.getElementById(`of-acc-${id}`).value||'').trim();
  if(!/^\d{6}$/.test(acc)){ err.textContent=fuT('errAcc'); err.classList.add('show'); return; }
  const brokerVal=(document.getElementById(`of-broker-${id}`).value||'');
  if(!brokerVal){ err.textContent=fuT('errBroker'); err.classList.add('show'); return; }
  const brokerSelf=brokerVal==='self';
  const brokerId=brokerSelf?null:(parseInt(brokerVal)||null);
  const bc=brokerSelf?0:(parseFloat(document.getElementById(`of-bc-${id}`).value)||0);
  const mktrVal=(document.getElementById(`of-mktr-${id}`).value||'');
  const mktrId=(mktrVal&&mktrVal!=='self')?(parseInt(mktrVal)||null):null;
  const mc=mktrId?(parseFloat(document.getElementById(`of-mc-${id}`).value)||0):0;
  const ec1=parseFloat(document.getElementById(`of-ec1-${id}`).value)||0;
  const ec2=parseFloat(document.getElementById(`of-ec2-${id}`).value)||0;
  const ref=(document.getElementById(`of-ref-${id}`).value||'').trim();
  if(ref && !/^\d{6}$/.test(ref)){ err.textContent=fuT('errRef'); err.classList.add('show'); return; }
  const refc=ref?(parseFloat(document.getElementById(`of-refc-${id}`).value)||0):0;
  const branchTotal=bc+mc+ec1+ec2+refc;
  if(branchTotal>5.0001){ err.textContent=fuT('errBranchCap')(branchTotal.toFixed(2)); err.classList.add('show'); return; }
  const payload={
    account_number:acc, broker_id:brokerId, broker_commission:bc,
    marketer_id:mktrId, marketer_commission:mc,
    ext_marketer1_id:parseInt(document.getElementById(`of-ext1-${id}`).value)||null, ext_commission1:parseFloat(document.getElementById(`of-ec1-${id}`).value)||0,
    ext_marketer2_id:parseInt(document.getElementById(`of-ext2-${id}`).value)||null, ext_commission2:parseFloat(document.getElementById(`of-ec2-${id}`).value)||0,
    referral_account:ref||null, referral_commission:ref?(parseFloat(document.getElementById(`of-refc-${id}`).value)||0):0,
    initial_deposit:parseFloat(document.getElementById(`of-dep-${id}`).value)||0,
    monthly_deposit:parseFloat(document.getElementById(`of-mon-${id}`).value)||0,
    forex_commission:parseFloat(document.getElementById(`of-forex-${id}`).value)||0,
    futures_commission:parseFloat(document.getElementById(`of-fut-${id}`).value)||0,
  };
  const r=await api('PUT',`/cc/cards/${id}/complete`,payload);
  if(r.success){ closeAccount(); showAlert('ok',r.message); loadLeads(); }
  else { const msg=r.errors?Object.values(r.errors).flat().join(' | '):r.message; err.textContent='❌ '+msg; err.classList.add('show'); }
}

async function loadEmps(){ const r=await api('GET','/employees?status=approved&slim=1'); window._ccEmployees=r.success?r.data:[]; }
function focusLead(){
  try{
    const id=new URLSearchParams(location.search).get('focus'); if(!id)return;
    const el=document.getElementById('lead-'+id); if(!el)return;
    el.scrollIntoView({behavior:'smooth',block:'center'});
    el.style.transition='box-shadow .3s'; el.style.boxShadow='0 0 0 3px rgba(123,104,238,.6)';
    setTimeout(()=>{el.style.boxShadow='';},2600);
  }catch(e){}
}
async function init(){ fuApplyLang(); await loadEmps(); await loadLeads(); setTimeout(focusLead,250); }
init();
</script>
@endpush
