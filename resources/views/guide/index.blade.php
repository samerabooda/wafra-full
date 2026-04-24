@extends('layouts.app')
@section('title','دليل النظام | System Guide')
@section('page-title','دليل النظام | System Guide')

@push('styles')
<style>
/* ── Guide Layout ── */
.guide-lang-bar{display:flex;gap:8px;margin-bottom:18px;padding:10px 14px;background:var(--bg2);border-radius:10px;border:1px solid var(--brd1)}
.lang-btn{padding:6px 18px;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;border:none;transition:all .2s;font-family:'Tajawal',sans-serif}
.lang-btn.active{background:var(--pri);color:white}
.lang-btn:not(.active){background:var(--inp-bg);color:var(--m2)}

.guide-section{margin-bottom:2rem}
.guide-h1{font-size:20px;font-weight:800;color:var(--tx);margin-bottom:6px}
.guide-h2{font-size:15px;font-weight:700;color:var(--pri2);margin:18px 0 8px;padding-right:10px;border-right:3px solid var(--pri)}
.guide-h2.en{border-right:none;border-left:3px solid var(--pri);padding-right:0;padding-left:10px}
.guide-p{font-size:13px;color:var(--m2);line-height:1.85;margin-bottom:10px}

/* ── Role Cards ── */
.role-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(210px,1fr));gap:12px;margin-bottom:14px}
.role-card{border-radius:12px;padding:14px;border:1px solid var(--brd1)}
.role-card.fa   {background:rgba(83,74,183,.07);border-color:rgba(83,74,183,.25)}
.role-card.bm   {background:rgba(46,134,171,.07);border-color:rgba(46,134,171,.25)}
.role-card.cc   {background:rgba(29,158,117,.07);border-color:rgba(29,158,117,.25)}
.role-icon{font-size:22px;margin-bottom:6px}
.role-name{font-size:13px;font-weight:700;margin-bottom:6px}
.role-card.fa .role-name{color:#3C3489}
.role-card.bm .role-name{color:var(--pri2)}
.role-card.cc .role-name{color:#0F6E56}
.role-item{font-size:11px;color:var(--mu);padding:2px 0;display:flex;gap:5px}
.role-item::before{content:'✓';color:var(--gr);font-weight:700;flex-shrink:0}
.role-item.no::before{content:'✗';color:var(--re)}

/* ── Workflow Steps ── */
.steps{counter-reset:step}
.step{display:flex;gap:14px;margin-bottom:14px;align-items:flex-start}
.step-num{width:30px;height:30px;border-radius:50%;background:var(--pri);color:white;font-size:13px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;margin-top:1px}
.step-body{flex:1}
.step-title{font-size:13px;font-weight:700;color:var(--tx);margin-bottom:3px}
.step-desc{font-size:12px;color:var(--mu);line-height:1.7}
.step-badge{display:inline-block;font-size:10px;font-weight:600;padding:1px 8px;border-radius:20px;margin-right:4px}
.sb-cc{background:rgba(29,158,117,.15);color:#085041;border:1px solid rgba(29,158,117,.3)}
.sb-br{background:rgba(46,134,171,.12);color:#0C447C;border:1px solid rgba(46,134,171,.25)}
.sb-fa{background:rgba(83,74,183,.12);color:#3C3489;border:1px solid rgba(83,74,183,.25)}

/* ── Commission Table ── */
.comm-table{width:100%;border-collapse:collapse;font-size:12px;margin-bottom:10px}
.comm-table th{background:var(--bg2);padding:8px 12px;font-weight:600;color:var(--m2);border:1px solid var(--brd1);text-align:center}
.comm-table td{padding:7px 12px;border:1px solid var(--brd1);text-align:center;color:var(--tx)}
.comm-table .total-row{background:var(--bg2);font-weight:700}
.comm-ok{color:var(--gr);font-weight:700}
.comm-warn{color:var(--re);font-weight:700}

/* ── Warning System ── */
.warn-row{display:flex;gap:10px;align-items:flex-start;padding:8px 12px;border-radius:8px;margin-bottom:6px;font-size:12px}
.warn-row.w1{background:rgba(186,117,23,.08);border:1px solid rgba(186,117,23,.25)}
.warn-row.w2{background:rgba(224,130,50,.1);border:1px solid rgba(224,130,50,.35)}
.warn-row.w3{background:rgba(224,80,80,.08);border:1px solid rgba(224,80,80,.25)}
.warn-row.wX{background:rgba(224,80,80,.15);border:1px solid var(--re)}
.warn-num{width:22px;height:22px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;color:white}
.w1 .warn-num{background:rgba(186,117,23,.8)}
.w2 .warn-num{background:rgba(200,100,40,.85)}
.w3 .warn-num{background:rgba(224,80,80,.8)}
.wX .warn-num{background:var(--re)}

/* ── Diagram SVG ── */
.diagram-wrap{background:var(--bg);border:1px solid var(--brd1);border-radius:14px;padding:16px;margin-bottom:16px;overflow-x:auto}

/* ── Perm Matrix ── */
.perm-matrix{width:100%;border-collapse:collapse;font-size:11px}
.perm-matrix th,.perm-matrix td{border:1px solid var(--brd1);padding:6px 10px}
.perm-matrix th{background:var(--bg2);font-weight:600;color:var(--m2);text-align:center}
.perm-matrix td:first-child{font-weight:500;color:var(--mu);text-align:right}
.perm-matrix td:not(:first-child){text-align:center}
.py{color:var(--gr);font-weight:700} .pn{color:var(--re)} .pp{color:var(--or)}

/* ── Print ── */
@media print{
  .guide-lang-bar,.panel-header button{display:none!important}
  .guide-section{page-break-inside:avoid}
  body{background:white!important;color:black!important}
}
</style>
@endpush

@section('content')

{{-- Language Bar --}}
<div class="guide-lang-bar">
  <button class="lang-btn active" id="btn-ar" onclick="setLang('ar')">العربية</button>
  <button class="lang-btn" id="btn-en" onclick="setLang('en')">English</button>
  <span style="flex:1"></span>
  <button class="btn btn-ghost btn-sm" onclick="window.print()">🖨️ طباعة / Print</button>
</div>

{{-- ═══════════════════════════════════════════════════════
     SYSTEM DIAGRAM
═══════════════════════════════════════════════════════ --}}
<div class="panel guide-section">
  <div class="panel-header">
    <div class="panel-title">
      <span class="ar">🗺️ مخطط النظام الكامل</span>
      <span class="en" style="display:none">🗺️ System Architecture Diagram</span>
    </div>
  </div>
  <div class="panel-body">
    <div class="diagram-wrap">
<svg width="100%" viewBox="0 0 900 660" role="img" xmlns="http://www.w3.org/2000/svg">
<title>Wafra Gulf Commission Cards — System Diagram</title>
<desc>Full system architecture showing roles, workflow, and commission flow</desc>
<defs>
  <marker id="arr" viewBox="0 0 10 10" refX="8" refY="5" markerWidth="6" markerHeight="6" orient="auto-start-reverse">
    <path d="M2 1L8 5L2 9" fill="none" stroke="context-stroke" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
  </marker>
</defs>

<!-- ── BACKGROUND ZONES ─────────────────────────────── -->
<!-- CC Zone -->
<rect x="30" y="20" width="190" height="290" rx="14" fill="none" stroke="#1D9E75" stroke-width="1" stroke-dasharray="5 3" opacity=".5"/>
<text x="125" y="42" text-anchor="middle" font-size="11" font-weight="600" fill="#0F6E56" font-family="Tajawal,sans-serif">مركز الاتصال / Call Center</text>

<!-- Branch Zone -->
<rect x="360" y="20" width="190" height="290" rx="14" fill="none" stroke="#378ADD" stroke-width="1" stroke-dasharray="5 3" opacity=".5"/>
<text x="455" y="42" text-anchor="middle" font-size="11" font-weight="600" fill="#185FA5" font-family="Tajawal,sans-serif">الفرع العادي / Regular Branch</text>

<!-- FA Zone -->
<rect x="680" y="20" width="190" height="290" rx="14" fill="none" stroke="#7F77DD" stroke-width="1" stroke-dasharray="5 3" opacity=".5"/>
<text x="775" y="42" text-anchor="middle" font-size="11" font-weight="600" fill="#534AB7" font-family="Tajawal,sans-serif">المدير المالي / Finance Admin</text>

<!-- ── ACTORS ─────────────────────────────────────────── -->
<!-- CC Agent -->
<g>
  <rect x="60" y="55" width="130" height="52" rx="8" fill="#E1F5EE" stroke="#1D9E75" stroke-width="1"/>
  <text x="125" y="76" text-anchor="middle" font-size="12" font-weight="700" fill="#085041" font-family="Tajawal,sans-serif">موظف CC</text>
  <text x="125" y="93" text-anchor="middle" font-size="10" fill="#0F6E56" font-family="Tajawal,sans-serif">CC Agent</text>
</g>

<!-- CC Manager -->
<g>
  <rect x="60" y="125" width="130" height="52" rx="8" fill="#9FE1CB" stroke="#1D9E75" stroke-width="1"/>
  <text x="125" y="146" text-anchor="middle" font-size="12" font-weight="700" fill="#04342C" font-family="Tajawal,sans-serif">مدير فرع CC</text>
  <text x="125" y="163" text-anchor="middle" font-size="10" fill="#085041" font-family="Tajawal,sans-serif">CC Branch Manager</text>
</g>

<!-- Branch Manager -->
<g>
  <rect x="390" y="55" width="130" height="52" rx="8" fill="#E6F1FB" stroke="#378ADD" stroke-width="1"/>
  <text x="455" y="76" text-anchor="middle" font-size="12" font-weight="700" fill="#042C53" font-family="Tajawal,sans-serif">مدير الفرع</text>
  <text x="455" y="93" text-anchor="middle" font-size="10" fill="#185FA5" font-family="Tajawal,sans-serif">Branch Manager</text>
</g>

<!-- Finance Admin -->
<g>
  <rect x="710" y="55" width="130" height="52" rx="8" fill="#EEEDFE" stroke="#7F77DD" stroke-width="1"/>
  <text x="775" y="76" text-anchor="middle" font-size="12" font-weight="700" fill="#26215C" font-family="Tajawal,sans-serif">المدير المالي</text>
  <text x="775" y="93" text-anchor="middle" font-size="10" fill="#534AB7" font-family="Tajawal,sans-serif">Finance Admin</text>
</g>

<!-- ── CC CARD OBJECT ─────────────────────────────────── -->
<g>
  <rect x="60" y="205" width="130" height="86" rx="8" fill="#FAEEDA" stroke="#BA7517" stroke-width="1.5"/>
  <text x="125" y="226" text-anchor="middle" font-size="11" font-weight="700" fill="#412402" font-family="Tajawal,sans-serif">كرت عمولة CC</text>
  <text x="125" y="242" text-anchor="middle" font-size="10" fill="#633806" font-family="Tajawal,sans-serif">CC Commission Card</text>
  <line x1="70" y1="248" x2="180" y2="248" stroke="#EF9F27" stroke-width=".5"/>
  <text x="75" y="262" font-size="9" fill="#854F0B" font-family="Tajawal,sans-serif">رقم + شهر + فرع</text>
  <text x="75" y="276" font-size="9" fill="#854F0B" font-family="Tajawal,sans-serif">موظف CC + عمولته</text>
  <text x="75" y="288" font-size="9" fill="#854F0B" font-family="Tajawal,sans-serif">status: cc_pending</text>
</g>

<!-- ── BRANCH CARD OBJECT ─────────────────────────────── -->
<g>
  <rect x="390" y="175" width="130" height="116" rx="8" fill="#E6F1FB" stroke="#378ADD" stroke-width="1.5"/>
  <text x="455" y="196" text-anchor="middle" font-size="11" font-weight="700" fill="#042C53" font-family="Tajawal,sans-serif">كرت مكتمل</text>
  <text x="455" y="212" text-anchor="middle" font-size="10" fill="#185FA5" font-family="Tajawal,sans-serif">Completed Card</text>
  <line x1="400" y1="218" x2="510" y2="218" stroke="#85B7EB" stroke-width=".5"/>
  <text x="400" y="232" font-size="9" fill="#0C447C" font-family="Tajawal,sans-serif">بروكر + عمولة</text>
  <text x="400" y="246" font-size="9" fill="#0C447C" font-family="Tajawal,sans-serif">مسوّق + عمولة</text>
  <text x="400" y="260" font-size="9" fill="#0C447C" font-family="Tajawal,sans-serif">إيداع أولي / شهري</text>
  <text x="400" y="274" font-size="9" fill="#0C447C" font-family="Tajawal,sans-serif">cc_agent_commission</text>
  <text x="400" y="288" font-size="9" fill="#27500A" font-family="Tajawal,sans-serif" font-weight="600">status: completed ✓</text>
</g>

<!-- ── FA VIEW ─────────────────────────────────────────── -->
<g>
  <rect x="710" y="135" width="130" height="166" rx="8" fill="#EEEDFE" stroke="#7F77DD" stroke-width="1.5"/>
  <text x="775" y="156" text-anchor="middle" font-size="11" font-weight="700" fill="#26215C" font-family="Tajawal,sans-serif">رؤية شاملة</text>
  <text x="775" y="172" text-anchor="middle" font-size="10" fill="#534AB7" font-family="Tajawal,sans-serif">Full Visibility</text>
  <line x1="720" y1="178" x2="830" y2="178" stroke="#AFA9EC" stroke-width=".5"/>
  <text x="720" y="192" font-size="9" fill="#3C3489" font-family="Tajawal,sans-serif">جميع الفروع</text>
  <text x="720" y="206" font-size="9" fill="#3C3489" font-family="Tajawal,sans-serif">كل العمولات</text>
  <text x="720" y="220" font-size="9" fill="#3C3489" font-family="Tajawal,sans-serif">تقارير موحّدة</text>
  <text x="720" y="234" font-size="9" fill="#3C3489" font-family="Tajawal,sans-serif">إعداد الحدود</text>
  <text x="720" y="248" font-size="9" fill="#3C3489" font-family="Tajawal,sans-serif">عمولة كل موظف</text>
  <text x="720" y="262" font-size="9" fill="#3C3489" font-family="Tajawal,sans-serif">تفعيل حد $8</text>
  <line x1="720" y1="268" x2="830" y2="268" stroke="#AFA9EC" stroke-width=".5"/>
  <text x="720" y="282" font-size="9" fill="#27500A" font-weight="600" font-family="Tajawal,sans-serif">cc $1 + broker $X</text>
  <text x="720" y="295" font-size="9" fill="#27500A" font-weight="600" font-family="Tajawal,sans-serif">mkt $Y = $8 max</text>
</g>

<!-- ── ARROWS: CC Workflow ─────────────────────────────── -->
<!-- CC Agent → creates card -->
<line x1="125" y1="107" x2="125" y2="203" stroke="#1D9E75" stroke-width="1.5" marker-end="url(#arr)"/>
<text x="133" y="158" font-size="9" fill="#0F6E56" font-family="Tajawal,sans-serif">يُدخل</text>

<!-- CC Card → sends to Branch -->
<path d="M190 248 L360 248 L360 200 L388 200" fill="none" stroke="#BA7517" stroke-width="1.5" marker-end="url(#arr)"/>
<text x="265" y="242" text-anchor="middle" font-size="10" fill="#854F0B" font-weight="600" font-family="Tajawal,sans-serif">إرسال + إشعار</text>
<text x="265" y="256" text-anchor="middle" font-size="9" fill="#854F0B" font-family="Tajawal,sans-serif">Send + Notification</text>

<!-- Branch Manager → completes -->
<line x1="455" y1="107" x2="455" y2="173" stroke="#378ADD" stroke-width="1.5" marker-end="url(#arr)"/>
<text x="463" y="143" font-size="9" fill="#185FA5" font-family="Tajawal,sans-serif">يُكمل</text>

<!-- FA reads all -->
<line x1="520" y1="220" x2="708" y2="220" stroke="#7F77DD" stroke-width="1" stroke-dasharray="4 3" marker-end="url(#arr)"/>
<text x="614" y="214" text-anchor="middle" font-size="9" fill="#534AB7" font-family="Tajawal,sans-serif">يرى كل شيء</text>

<!-- FA → controls limits -->
<line x1="775" y1="107" x2="775" y2="133" stroke="#7F77DD" stroke-width="1.5" marker-end="url(#arr)"/>

<!-- CC Manager ↔ FA (sets commission) -->
<path d="M190 151 L580 151 L580 80 L708 80" fill="none" stroke="#7F77DD" stroke-width="1" stroke-dasharray="3 3" marker-end="url(#arr)"/>
<text x="440" y="144" text-anchor="middle" font-size="9" fill="#534AB7" font-family="Tajawal,sans-serif">يحدد عمولة الموظفين</text>

<!-- ── COMMISSION FLOW ─────────────────────────────────── -->
<!-- Flow area label -->
<text x="450" y="342" text-anchor="middle" font-size="13" font-weight="700" fill="#444441" font-family="Tajawal,sans-serif">توزيع العمولة / Commission Distribution</text>

<!-- Bar chart of commission parts -->
<!-- CC Agent bar -->
<rect x="80" y="360" width="70" height="30" rx="4" fill="#1D9E75" opacity=".85"/>
<text x="115" y="380" text-anchor="middle" font-size="11" font-weight="700" fill="white" font-family="Tajawal,sans-serif">$1</text>
<text x="115" y="403" text-anchor="middle" font-size="10" fill="#085041" font-family="Tajawal,sans-serif">موظف CC</text>

<!-- + -->
<text x="165" y="380" text-anchor="middle" font-size="18" fill="#888780" font-family="Tajawal,sans-serif">+</text>

<!-- Broker bar -->
<rect x="185" y="345" width="115" height="45" rx="4" fill="#378ADD" opacity=".85"/>
<text x="242" y="372" text-anchor="middle" font-size="14" font-weight="700" fill="white" font-family="Tajawal,sans-serif">$4</text>
<text x="242" y="403" text-anchor="middle" font-size="10" fill="#185FA5" font-family="Tajawal,sans-serif">بروكر / Broker</text>

<!-- + -->
<text x="315" y="380" text-anchor="middle" font-size="18" fill="#888780" font-family="Tajawal,sans-serif">+</text>

<!-- Marketer bar -->
<rect x="330" y="352" width="90" height="38" rx="4" fill="#534AB7" opacity=".85"/>
<text x="375" y="376" text-anchor="middle" font-size="13" font-weight="700" fill="white" font-family="Tajawal,sans-serif">$3</text>
<text x="375" y="403" text-anchor="middle" font-size="10" fill="#3C3489" font-family="Tajawal,sans-serif">مسوّق / Marketer</text>

<!-- = -->
<text x="435" y="380" text-anchor="middle" font-size="18" fill="#888780" font-family="Tajawal,sans-serif">=</text>

<!-- Total -->
<rect x="450" y="338" width="90" height="52" rx="4" fill="#27500A" opacity=".9"/>
<text x="495" y="368" text-anchor="middle" font-size="18" font-weight="700" fill="white" font-family="Tajawal,sans-serif">$8</text>
<text x="495" y="383" text-anchor="middle" font-size="10" fill="#C0DD97" font-family="Tajawal,sans-serif">/lot max</text>
<text x="495" y="403" text-anchor="middle" font-size="10" fill="#27500A" font-family="Tajawal,sans-serif">الحد الأقصى</text>

<!-- ── WARNING SYSTEM ──────────────────────────────────── -->
<text x="450" y="435" text-anchor="middle" font-size="12" font-weight="700" fill="#444441" font-family="Tajawal,sans-serif">آلية التحذير عند تجاوز $8 / Warning System</text>

<!-- Warning steps -->
<rect x="60"  y="448" width="160" height="36" rx="6" fill="#FAEEDA" stroke="#EF9F27" stroke-width="1"/>
<text x="140" y="462" text-anchor="middle" font-size="10" font-weight="600" fill="#633806" font-family="Tajawal,sans-serif">تحذير 1 — يُسمح بالمتابعة</text>
<text x="140" y="477" text-anchor="middle" font-size="9" fill="#854F0B" font-family="Tajawal,sans-serif">Warning 1 — Can proceed</text>

<line x1="220" y1="466" x2="248" y2="466" stroke="#888780" stroke-width="1" marker-end="url(#arr)"/>

<rect x="250" y="448" width="160" height="36" rx="6" fill="#F5C4B3" stroke="#D85A30" stroke-width="1"/>
<text x="330" y="462" text-anchor="middle" font-size="10" font-weight="600" fill="#712B13" font-family="Tajawal,sans-serif">تحذير 2 — تأكيد ثانٍ</text>
<text x="330" y="477" text-anchor="middle" font-size="9" fill="#993C1D" font-family="Tajawal,sans-serif">Warning 2 — Second confirm</text>

<line x1="410" y1="466" x2="438" y2="466" stroke="#888780" stroke-width="1" marker-end="url(#arr)"/>

<rect x="440" y="448" width="160" height="36" rx="6" fill="#F09595" stroke="#E24B4A" stroke-width="1"/>
<text x="520" y="462" text-anchor="middle" font-size="10" font-weight="600" fill="#501313" font-family="Tajawal,sans-serif">تحذير 3 — آخر تحذير</text>
<text x="520" y="477" text-anchor="middle" font-size="9" fill="#791F1F" font-family="Tajawal,sans-serif">Warning 3 — Final warning</text>

<line x1="600" y1="466" x2="628" y2="466" stroke="#888780" stroke-width="1" marker-end="url(#arr)"/>

<rect x="630" y="448" width="230" height="36" rx="6" fill="#A32D2D" stroke="#791F1F" stroke-width="1.5"/>
<text x="745" y="462" text-anchor="middle" font-size="10" font-weight="700" fill="white" font-family="Tajawal,sans-serif">حجب — تواصل مع المدير المالي</text>
<text x="745" y="477" text-anchor="middle" font-size="9" fill="#F7C1C1" font-family="Tajawal,sans-serif">Blocked — Contact Finance Admin</text>

<!-- ── STATUS LIFECYCLE ─────────────────────────────────── -->
<text x="450" y="516" text-anchor="middle" font-size="12" font-weight="700" fill="#444441" font-family="Tajawal,sans-serif">حالات الكرت / Card Status Lifecycle</text>

<rect x="60"  y="528" width="120" height="32" rx="6" fill="#FAEEDA" stroke="#EF9F27" stroke-width="1"/>
<text x="120" y="548" text-anchor="middle" font-size="10" font-weight="600" fill="#633806" font-family="Tajawal,sans-serif">cc_pending</text>

<line x1="180" y1="544" x2="205" y2="544" stroke="#888780" stroke-width="1" marker-end="url(#arr)"/>

<rect x="207" y="528" width="110" height="32" rx="6" fill="#E6F1FB" stroke="#378ADD" stroke-width="1"/>
<text x="262" y="548" text-anchor="middle" font-size="10" font-weight="600" fill="#0C447C" font-family="Tajawal,sans-serif">accepted</text>

<line x1="317" y1="544" x2="342" y2="544" stroke="#888780" stroke-width="1" marker-end="url(#arr)"/>

<rect x="344" y="528" width="120" height="32" rx="6" fill="#EAF3DE" stroke="#639922" stroke-width="1.5"/>
<text x="404" y="548" text-anchor="middle" font-size="10" font-weight="700" fill="#173404" font-family="Tajawal,sans-serif">completed ✓</text>

<!-- Rejected branch -->
<line x1="262" y1="560" x2="262" y2="580" stroke="#E24B4A" stroke-width="1" marker-end="url(#arr)"/>
<rect x="197" y="582" width="130" height="30" rx="6" fill="#FCEBEB" stroke="#E24B4A" stroke-width="1"/>
<text x="262" y="601" text-anchor="middle" font-size="10" font-weight="600" fill="#501313" font-family="Tajawal,sans-serif">rejected ↩ back to CC</text>

<!-- Legend -->
<text x="560" y="545" font-size="10" fill="#444441" font-family="Tajawal,sans-serif">--- تعديل / Edit</text>
<text x="560" y="560" font-size="10" fill="#444441" font-family="Tajawal,sans-serif">→   تدفق / Flow</text>
<text x="560" y="575" font-size="10" fill="#0F6E56" font-family="Tajawal,sans-serif">■   فرع CC</text>
<text x="560" y="590" font-size="10" fill="#185FA5" font-family="Tajawal,sans-serif">■   فرع عادي</text>
<text x="560" y="605" font-size="10" fill="#534AB7" font-family="Tajawal,sans-serif">■   مدير مالي</text>
<text x="650" y="545" font-size="10" fill="#854F0B" font-family="Tajawal,sans-serif">■   كرت CC</text>
<text x="650" y="560" font-size="10" fill="#27500A" font-family="Tajawal,sans-serif">■   حد العمولة</text>
</svg>
    </div>
  </div>
</div>

{{-- ═══════════════════════════════════════════════════════
     ARABIC GUIDE
═══════════════════════════════════════════════════════ --}}
<div id="content-ar">

{{-- Roles --}}
<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">👥 الأدوار والصلاحيات</div></div>
  <div class="panel-body">
    <div class="role-grid">
      <div class="role-card fa">
        <div class="role-icon">💼</div>
        <div class="role-name">المدير المالي</div>
        <div class="role-item">يرى جميع الفروع والكروت</div>
        <div class="role-item">يضيف / يحذف مديرين وموظفين</div>
        <div class="role-item">يستورد البيانات من Excel</div>
        <div class="role-item">يُعدّل عمولة كل موظف CC</div>
        <div class="role-item">يتحكم في حد $8/lot</div>
        <div class="role-item">التقارير الموحّدة لكل الفروع</div>
      </div>
      <div class="role-card bm">
        <div class="role-icon">🏢</div>
        <div class="role-name">مدير الفرع</div>
        <div class="role-item">يرى كروت فرعه فقط</div>
        <div class="role-item">يُضيف كروت لفرعه</div>
        <div class="role-item">يُعدّل كروته مع تسجيل السبب</div>
        <div class="role-item">يقبل / يرفض كروت CC</div>
        <div class="role-item">يُكمل بيانات كروت CC</div>
        <div class="role-item no">لا يرى فروعاً أخرى</div>
      </div>
      <div class="role-card cc">
        <div class="role-icon">📞</div>
        <div class="role-name">مدير / موظف CC</div>
        <div class="role-item">يُدخل بيانات العميل</div>
        <div class="role-item">يختار الفرع المعيّن</div>
        <div class="role-item">يُرسل الكرت للفرع</div>
        <div class="role-item">يرى عمولته هو فقط</div>
        <div class="role-item">يرى حالة كل كرت أرسله</div>
        <div class="role-item no">لا يُدخل بروكر الفرع</div>
      </div>
    </div>
  </div>
</div>

{{-- CC Workflow --}}
<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">📞 سير عمل مركز الاتصال</div></div>
  <div class="panel-body">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-cc">موظف CC</span> يُدخل بيانات العميل</div>
        <div class="step-desc">يُدخل رقم الحساب، الشهر، نوع الحساب، ويختار الفرع المستهدف واسمه كموظف. عمولته تُجلَب تلقائياً من ملف الموظف (يحددها المدير المالي مسبقاً).</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-cc">CC</span> يضغط "إرسال للفرع"</div>
        <div class="step-desc">يصل إشعار فوري لمدير الفرع المعيّن. حالة الكرت تُصبح <b>cc_pending</b>. يظهر عدد الإشعارات غير المقروءة في القائمة.</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-br">مدير الفرع</span> يقبل أو يرفض</div>
        <div class="step-desc">
          <b>قبول:</b> الكرت يظهر في قائمة "واردة من CC" جاهزاً للإكمال.<br>
          <b>رفض:</b> يكتب السبب — يصل إشعار لمركز الاتصال مع السبب. يمكن لـ CC إعادة الإرسال بعد التعديل.
        </div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-br">مدير الفرع</span> يُكمل بيانات الكرت</div>
        <div class="step-desc">يُضيف البروكر (من موظفي فرعه)، عمولة البروكر، المسوّق، الإيداع الأولي والشهري. عمولة موظف CC مُقفلة — لا يراها ولا يعدّلها.</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">5</div>
      <div class="step-body">
        <div class="step-title">الكرت يظهر عند <span class="step-badge sb-cc">CC</span> و<span class="step-badge sb-br">الفرع</span> معاً</div>
        <div class="step-desc">بعد الإكمال، يظهر الكرت في قائمة كلا الفرعين. كل فرع يرى عمولته فقط. المدير المالي يرى كل شيء مع إجمالي العمولات.</div>
      </div>
    </div>
  </div>
</div>

{{-- Commission System --}}
<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">💰 نظام العمولات وحد $8</div></div>
  <div class="panel-body">
    <table class="comm-table">
      <thead>
        <tr><th>العمولة</th><th>من يحددها</th><th>من يراها</th><th>المبلغ المثالي</th></tr>
      </thead>
      <tbody>
        <tr><td>عمولة موظف CC</td><td>المدير المالي — من ملف الموظف</td><td>CC فقط + المدير المالي</td><td>$1/lot</td></tr>
        <tr><td>عمولة البروكر</td><td>مدير الفرع</td><td>الفرع + المدير المالي</td><td>$4/lot</td></tr>
        <tr><td>عمولة المسوّق</td><td>مدير الفرع</td><td>الفرع + المدير المالي</td><td>$3/lot</td></tr>
        <tr class="total-row"><td><b>الإجمالي الأقصى</b></td><td>—</td><td>المدير المالي فقط</td><td><span class="comm-ok">$8/lot</span></td></tr>
      </tbody>
    </table>
    <div class="guide-p">عند تجاوز $8: النظام يُرسل تحذيرات تصاعدية (الحد الافتراضي 3 تحذيرات) ثم يحجب الحفظ نهائياً. المدير المالي يستطيع تغيير هذا الحد أو إيقافه كلياً من صفحة إعدادات العمولات.</div>

    <div class="warn-row w1">
      <div class="warn-num">1</div>
      <div><b>تحذير خفيف:</b> "إجمالي العمولات يتجاوز $8 — هل أنت متأكد؟" — زر تأكيد يسمح بالمتابعة</div>
    </div>
    <div class="warn-row w2">
      <div class="warn-num">2</div>
      <div><b>تحذير متوسط:</b> "هذا التجاوز الثاني — تأكيد مجدداً؟"</div>
    </div>
    <div class="warn-row w3">
      <div class="warn-num">3</div>
      <div><b>تحذير قوي:</b> "آخر تحذير — هل تريد المتابعة رغم التجاوز؟"</div>
    </div>
    <div class="warn-row wX">
      <div class="warn-num">!</div>
      <div><b>حجب تام:</b> "يرجى التواصل مع المدير المالي لمراجعة هذا الحساب" — زر الحفظ يُوقَف</div>
    </div>
  </div>
</div>

{{-- Permissions Matrix --}}
<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">🛡️ مصفوفة الصلاحيات الكاملة</div></div>
  <div class="panel-body" style="overflow-x:auto">
    <table class="perm-matrix">
      <thead>
        <tr><th style="min-width:160px">الإجراء</th><th style="min-width:80px">موظف CC</th><th>مدير الفرع</th><th>المدير المالي</th></tr>
      </thead>
      <tbody>
        <tr><td>إدخال كرت CC</td><td><span class="py">✓</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>إرسال الكرت للفرع</td><td><span class="py">✓</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>قبول / رفض كرت CC</td><td><span class="pn">✗</span></td><td><span class="py">✓ فرعه فقط</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>إكمال بيانات الكرت</td><td><span class="pn">✗</span></td><td><span class="py">✓ فرعه فقط</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>تعديل كرت موجود</td><td><span class="pn">✗</span></td><td><span class="py">✓ مع سبب</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>حذف كرت</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>رؤية عمولة CC</td><td><span class="py">✓ عمولته فقط</span></td><td><span class="pn">✗ مخفي</span></td><td><span class="py">✓ الكل</span></td></tr>
        <tr><td>رؤية عمولة الفرع</td><td><span class="pn">✗ مخفي</span></td><td><span class="py">✓ فرعه فقط</span></td><td><span class="py">✓ الكل</span></td></tr>
        <tr><td>استيراد Excel</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>إدارة إعدادات العمولات</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>إضافة فروع / مديرين</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>الموافقة على الموظفين</td><td><span class="pn">✗</span></td><td><span class="pp">~ فرعه فقط</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>التقارير الديناميكية</td><td><span class="pn">✗</span></td><td><span class="py">✓ فرعه فقط</span></td><td><span class="py">✓ كل الفروع</span></td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- Quick Setup --}}
<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">🚀 خطوات التشغيل الأولي</div></div>
  <div class="panel-body">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <div class="step-title">تثبيت المشروع</div>
        <div class="step-desc" style="font-family:monospace;font-size:11px;background:var(--bg2);padding:8px;border-radius:6px;direction:ltr;text-align:left">
          composer create-project laravel/laravel wafra-gulf<br>
          cd wafra-gulf<br>
          composer require laravel/sanctum maatwebsite/excel barryvdh/laravel-dompdf<br>
          cp .env.example .env &amp;&amp; php artisan key:generate
        </div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <div class="step-title">إعداد قاعدة البيانات</div>
        <div class="step-desc" style="font-family:monospace;font-size:11px;background:var(--bg2);padding:8px;border-radius:6px;direction:ltr;text-align:left">
          mysql -u root -p -e "CREATE DATABASE wafra_gulf CHARACTER SET utf8mb4;"<br>
          # Edit DB_USERNAME and DB_PASSWORD in .env<br>
          php artisan migrate<br>
          php artisan db:seed
        </div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        <div class="step-title">بيانات الدخول الافتراضية</div>
        <div class="step-desc">
          البريد: <b style="font-family:monospace">finance@wafragulf.com</b><br>
          كلمة المرور: <b style="font-family:monospace">Wafra@2026!</b><br>
          <span style="color:var(--re);font-size:11px">غيّر كلمة المرور فور الدخول الأول</span>
        </div>
      </div>
    </div>
  </div>
</div>

</div>{{-- end #content-ar --}}

{{-- ═══════════════════════════════════════════════════════
     ENGLISH GUIDE
═══════════════════════════════════════════════════════ --}}
<div id="content-en" style="display:none;direction:ltr;text-align:left">

<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">👥 Roles & Permissions</div></div>
  <div class="panel-body">
    <div class="role-grid">
      <div class="role-card fa">
        <div class="role-icon">💼</div>
        <div class="role-name">Finance Admin</div>
        <div class="role-item">Full access — all branches & cards</div>
        <div class="role-item">Add / remove managers & employees</div>
        <div class="role-item">Import data from Excel</div>
        <div class="role-item">Set CC agent commission rates</div>
        <div class="role-item">Control the $8/lot commission cap</div>
        <div class="role-item">Unified reports across all branches</div>
      </div>
      <div class="role-card bm">
        <div class="role-icon">🏢</div>
        <div class="role-name">Branch Manager</div>
        <div class="role-item">View only their own branch cards</div>
        <div class="role-item">Add cards for their branch</div>
        <div class="role-item">Edit cards with mandatory reason</div>
        <div class="role-item">Accept / reject CC cards</div>
        <div class="role-item">Complete CC card broker & deposit data</div>
        <div class="role-item no">Cannot see other branches</div>
      </div>
      <div class="role-card cc">
        <div class="role-icon">📞</div>
        <div class="role-name">CC Manager / Agent</div>
        <div class="role-item">Enter client account data</div>
        <div class="role-item">Select the target branch</div>
        <div class="role-item">Send card to branch</div>
        <div class="role-item">View only their own commission</div>
        <div class="role-item">Track status of sent cards</div>
        <div class="role-item no">Cannot set branch broker</div>
      </div>
    </div>
  </div>
</div>

<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">📞 Call Center Workflow</div></div>
  <div class="panel-body">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-cc">CC Agent</span> enters client data</div>
        <div class="step-desc">Enters account number, month, account type, selects the target branch and their own name. Their commission rate is auto-fetched from their employee profile (set by Finance Admin).</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-cc">CC</span> clicks "Send to Branch"</div>
        <div class="step-desc">An instant notification reaches the target branch manager. Card status becomes <b>cc_pending</b>. The unread notification badge appears in the navigation.</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-br">Branch Manager</span> accepts or rejects</div>
        <div class="step-desc">
          <b>Accept:</b> Card appears in "From CC" list, ready to complete.<br>
          <b>Reject:</b> Manager writes the reason — CC receives a notification with the reason. CC can edit and resend.
        </div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">4</div>
      <div class="step-body">
        <div class="step-title"><span class="step-badge sb-br">Branch Manager</span> completes card data</div>
        <div class="step-desc">Selects broker (from their branch employees), sets broker commission, marketer, initial and monthly deposits. The CC agent commission is locked — branch cannot see or edit it.</div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">5</div>
      <div class="step-body">
        <div class="step-title">Card visible to both <span class="step-badge sb-cc">CC</span> and <span class="step-badge sb-br">Branch</span></div>
        <div class="step-desc">After completion, the card appears in both branches' lists. Each sees only their own commission. Finance Admin sees everything including the total commission breakdown.</div>
      </div>
    </div>
  </div>
</div>

<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">💰 Commission System & $8 Cap</div></div>
  <div class="panel-body">
    <table class="comm-table">
      <thead>
        <tr><th>Commission</th><th>Set by</th><th>Visible to</th><th>Ideal amount</th></tr>
      </thead>
      <tbody>
        <tr><td>CC Agent commission</td><td>Finance Admin — from employee profile</td><td>CC only + Finance Admin</td><td>$1/lot</td></tr>
        <tr><td>Broker commission</td><td>Branch Manager</td><td>Branch + Finance Admin</td><td>$4/lot</td></tr>
        <tr><td>Marketer commission</td><td>Branch Manager</td><td>Branch + Finance Admin</td><td>$3/lot</td></tr>
        <tr class="total-row"><td><b>Maximum total</b></td><td>—</td><td>Finance Admin only</td><td><span class="comm-ok">$8/lot</span></td></tr>
      </tbody>
    </table>
    <p class="guide-p">When total exceeds $8: the system issues escalating warnings (default: 3 warnings) then permanently blocks saving. Finance Admin can change this cap or disable it entirely from the Commission Settings page.</p>
    <div class="warn-row w1"><div class="warn-num">1</div><div><b>Soft warning:</b> "Total commissions exceed $8 — are you sure?" — confirm button allows proceeding.</div></div>
    <div class="warn-row w2"><div class="warn-num">2</div><div><b>Medium warning:</b> "This is the second override — confirm again?"</div></div>
    <div class="warn-row w3"><div class="warn-num">3</div><div><b>Strong warning:</b> "Final warning — do you still want to proceed?"</div></div>
    <div class="warn-row wX"><div class="warn-num">!</div><div><b>Hard block:</b> "Please contact the Finance Admin to review this account" — Save button is disabled.</div></div>
  </div>
</div>

<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">🛡️ Full Permissions Matrix</div></div>
  <div class="panel-body" style="overflow-x:auto">
    <table class="perm-matrix">
      <thead>
        <tr><th style="min-width:180px">Action</th><th>CC Agent</th><th>Branch Manager</th><th>Finance Admin</th></tr>
      </thead>
      <tbody>
        <tr><td>Create CC card</td><td><span class="py">✓</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Send card to branch</td><td><span class="py">✓</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Accept / reject CC card</td><td><span class="pn">✗</span></td><td><span class="py">✓ own branch</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Complete card data</td><td><span class="pn">✗</span></td><td><span class="py">✓ own branch</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Edit existing card</td><td><span class="pn">✗</span></td><td><span class="py">✓ with reason</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Delete card</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>View CC commission</td><td><span class="py">✓ own only</span></td><td><span class="pn">✗ hidden</span></td><td><span class="py">✓ all</span></td></tr>
        <tr><td>View branch commission</td><td><span class="pn">✗ hidden</span></td><td><span class="py">✓ own only</span></td><td><span class="py">✓ all</span></td></tr>
        <tr><td>Excel import</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Manage commission settings</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Add branches / managers</td><td><span class="pn">✗</span></td><td><span class="pn">✗</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Approve employees</td><td><span class="pn">✗</span></td><td><span class="pp">~ own branch</span></td><td><span class="py">✓</span></td></tr>
        <tr><td>Dynamic reports</td><td><span class="pn">✗</span></td><td><span class="py">✓ own branch</span></td><td><span class="py">✓ all branches</span></td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="panel guide-section">
  <div class="panel-header"><div class="panel-title">🚀 Quick Setup Guide</div></div>
  <div class="panel-body">
    <div class="step">
      <div class="step-num">1</div>
      <div class="step-body">
        <div class="step-title">Install the project</div>
        <div class="step-desc" style="font-family:monospace;font-size:11px;background:var(--bg2);padding:8px;border-radius:6px">
          composer create-project laravel/laravel wafra-gulf<br>
          cd wafra-gulf<br>
          composer require laravel/sanctum maatwebsite/excel barryvdh/laravel-dompdf<br>
          cp .env.example .env && php artisan key:generate
        </div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">2</div>
      <div class="step-body">
        <div class="step-title">Set up the database</div>
        <div class="step-desc" style="font-family:monospace;font-size:11px;background:var(--bg2);padding:8px;border-radius:6px">
          mysql -u root -p -e "CREATE DATABASE wafra_gulf CHARACTER SET utf8mb4;"<br>
          # Edit DB_USERNAME and DB_PASSWORD in .env<br>
          php artisan migrate<br>
          php artisan db:seed
        </div>
      </div>
    </div>
    <div class="step">
      <div class="step-num">3</div>
      <div class="step-body">
        <div class="step-title">Default login credentials</div>
        <div class="step-desc">
          Email: <b style="font-family:monospace">finance@wafragulf.com</b><br>
          Password: <b style="font-family:monospace">Wafra@2026!</b><br>
          <span style="color:var(--re);font-size:11px">Change the password immediately after first login.</span>
        </div>
      </div>
    </div>
  </div>
</div>

</div>{{-- end #content-en --}}

@endsection

@push('scripts')
<script>
function setLang(lang) {
  const isAr = lang === 'ar';
  document.getElementById('content-ar').style.display = isAr ? 'block' : 'none';
  document.getElementById('content-en').style.display = isAr ? 'none'  : 'block';
  document.getElementById('btn-ar').classList.toggle('active', isAr);
  document.getElementById('btn-en').classList.toggle('active', !isAr);
  document.documentElement.setAttribute('dir', isAr ? 'rtl' : 'ltr');
  localStorage.setItem('guide_lang', lang);
}
// Restore last language
const saved = localStorage.getItem('guide_lang');
if (saved && saved !== 'ar') setLang('en');
</script>
@endpush
