@extends('layouts.app')
@section('title','Operation Guide')
@section('page-title','Operation Guide')

@section('content')
<style>
/* ══ Guide page styles ══════════════════════════════════════ */
.guide-page{max-width:960px;margin:0 auto}

/* ── Page header ── */
.guide-pg-header{text-align:center;padding:28px 0 22px;border-bottom:1px solid var(--brd1);margin-bottom:24px}
.guide-pg-header h1{font-size:26px;font-weight:900;margin-bottom:6px;
  background:linear-gradient(135deg,#22C4D4,#1AADBA);-webkit-background-clip:text;background-clip:text;color:transparent}
.guide-pg-header p{font-size:13px;color:var(--mu)}

/* ── Section label ── */
.guide-section-lbl{display:flex;align-items:center;gap:10px;margin-bottom:16px;margin-top:28px}
.guide-section-lbl::before,.guide-section-lbl::after{content:'';flex:1;height:1px;background:var(--brd1)}
.guide-section-lbl span{font-size:12px;font-weight:800;color:var(--pri2);
  background:var(--bg2);padding:4px 12px;border-radius:20px;
  border:1px solid rgba(26,173,186,.25);white-space:nowrap}

/* ── Role tabs ── */
.guide-role-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:20px;
  background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:6px}
.guide-role-tab{flex:1;min-width:110px;padding:9px 12px;border:none;background:none;border-radius:8px;
  cursor:pointer;font-family:'Tajawal',sans-serif;font-size:13px;font-weight:700;
  color:var(--mu);transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px}
.guide-role-tab.active{background:rgba(26,173,186,.16);border:1px solid rgba(26,173,186,.35);color:var(--pri2)}
.guide-tab-badge{font-size:9px;padding:1px 6px;border-radius:10px;
  background:rgba(26,173,186,.15);color:var(--pri2)}
.guide-role-tab.active .guide-tab-badge{background:rgba(26,173,186,.3)}

/* ── Role panels ── */
.guide-role-panel{display:none}
.guide-role-panel.active{display:block;animation:fadeIn .3s ease}
@keyframes fadeIn{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}

/* ── Role banner ── */
.role-banner{border-radius:14px;padding:18px 22px;margin-bottom:20px;
  display:flex;align-items:center;gap:16px}
.role-banner-icon{font-size:38px;flex-shrink:0}
.role-banner-title{font-size:17px;font-weight:900;margin-bottom:4px}
.role-banner-sub{font-size:12px;opacity:.75;line-height:1.65}

/* ── Flowchart ── */
.flow-diagram{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;
  gap:2px;padding:20px;background:var(--bg2);border:1px solid var(--brd1);
  border-radius:14px;margin-bottom:22px;overflow-x:auto}
.flow-node{display:flex;flex-direction:column;align-items:center;gap:5px;
  border-radius:12px;padding:13px 15px;min-width:90px;text-align:center;
  cursor:default;transition:transform .2s,box-shadow .2s}
.flow-node:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,.2)}
.flow-node-ico{font-size:26px;line-height:1}
.flow-node-lbl{font-size:10px;font-weight:800;white-space:nowrap}
.flow-node-sub{font-size:9px;color:var(--mu);white-space:nowrap}
.flow-arrow{font-size:22px;color:var(--mu);padding:0 6px;flex-shrink:0;line-height:1}

/* ── Steps ── */
.guide-steps{display:flex;flex-direction:column;gap:10px}
.guide-step{display:flex;gap:14px;background:var(--bg2);border:1px solid var(--brd1);
  border-radius:12px;padding:14px 16px;align-items:flex-start;transition:border-color .2s}
.guide-step:hover{border-color:rgba(26,173,186,.35)}
.step-icon-box{width:42px;height:42px;border-radius:10px;
  display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.step-num{display:inline-flex;align-items:center;justify-content:center;
  width:20px;height:20px;border-radius:50%;background:var(--pri3);
  color:white;font-size:10px;font-weight:800;margin-left:5px}
.step-title{font-size:13px;font-weight:800;margin-bottom:5px}
.step-desc{font-size:12px;color:var(--tx);line-height:1.8}
.step-note{display:inline-block;font-size:10px;color:var(--mu);
  margin-top:5px;padding:4px 9px;background:var(--bg3);border-radius:6px;
  border-left:2px solid var(--pri3)}
[dir="rtl"] .step-note{border-left:none;border-right:2px solid var(--pri3)}

/* ── Permissions table ── */
.perm-table{width:100%;border-collapse:collapse;font-size:12px}
.perm-table th{background:var(--bg3);padding:9px 12px;font-size:10px;font-weight:800;
  color:var(--mu);text-align:right;border-bottom:2px solid var(--brd1)}
.perm-table td{padding:9px 12px;border-bottom:1px solid var(--brd1);vertical-align:middle}
.perm-table tr:last-child td{border-bottom:none}
.perm-table tr:hover td{background:rgba(26,173,186,.04)}
.p-yes{color:var(--gr);font-weight:800}
.p-no{color:var(--re);opacity:.65}
.p-cond{color:var(--or);font-weight:700}

/* ── User-manual steps ── */
.manual-grid{display:flex;flex-direction:column;gap:12px}
.manual-card{display:flex;gap:14px;background:var(--bg2);border:1px solid var(--brd1);
  border-radius:12px;padding:16px;align-items:flex-start}
.manual-card:hover{border-color:rgba(26,173,186,.3);background:var(--bg3)}
.manual-ico{width:44px;height:44px;border-radius:11px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:22px}
.manual-body{flex:1}
.manual-title{font-size:13px;font-weight:800;margin-bottom:6px}
.manual-desc{font-size:12px;color:var(--tx);line-height:1.8}
.manual-tip{font-size:10px;color:var(--mu);margin-top:5px;
  background:var(--bg3);padding:4px 8px;border-radius:6px;display:inline-block}

/* ── Roles summary grid ── */
.roles-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:14px}
.role-card{background:var(--bg3);border:1px solid var(--brd1);border-radius:11px;padding:14px 16px}
.role-card-title{font-size:13px;font-weight:800;margin-bottom:8px}
.role-card-list{font-size:11px;color:var(--mu);line-height:1.9}

@media(max-width:700px){
  .flow-node{min-width:74px;padding:10px 10px}
  .flow-node-ico{font-size:22px}
  .roles-grid{grid-template-columns:1fr}
}
/* Language-toggled content */
.lang-ar{display:block}.lang-en{display:none}
</style>

<div class="guide-page">

  {{-- Page Header --}}
  <div class="guide-pg-header">
    <h1 id="guide-main-title">📖 دليل تشغيل النظام</h1>
    <p id="guide-main-sub">اختر دورك لمشاهدة Workflow خاص بك — ثم اطّلع على دليل المستخدم التفصيلي</p>
  </div>

  {{-- SECTION 1 — WORKFLOW --}}
  <div class="guide-section-lbl"><span id="guide-sec-workflow">🔄 مخطط سير العمل — Workflow</span></div>

  {{-- Role tabs --}}
  <div class="guide-role-tabs">
    <button class="guide-role-tab" id="gtab-fa" onclick="switchGuideTab('fa')">
      💼 <span id="gtab-fa-lbl">المدير المالي</span> <span class="guide-tab-badge" id="gtab-fa-badge">كامل</span>
    </button>
    <button class="guide-role-tab" id="gtab-bm" onclick="switchGuideTab('bm')">
      🏢 <span id="gtab-bm-lbl">مدير الفرع</span> <span class="guide-tab-badge" id="gtab-bm-badge">فرعي</span>
    </button>
    <button class="guide-role-tab" id="gtab-cc" onclick="switchGuideTab('cc')">
      📞 <span id="gtab-cc-lbl">كول سنتر CC</span> <span class="guide-tab-badge" id="gtab-cc-badge">متابعة</span>
    </button>
    <button class="guide-role-tab" id="gtab-vw" onclick="switchGuideTab('vw')">
      👁 <span id="gtab-vw-lbl">مشاهد</span> <span class="guide-tab-badge" id="gtab-vw-badge">قراءة</span>
    </button>
  </div>

  {{-- ════ Finance Admin panel ════ --}}
  <div class="guide-role-panel" id="gpanel-fa">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(26,173,186,.12),rgba(26,173,186,.04));border:1px solid rgba(26,173,186,.25)">
      <div class="role-banner-icon">💼</div>
      <div>
        <div class="role-banner-title" style="color:var(--pri2)">💼 Finance Admin — المدير المالي</div>
        <div class="role-banner-sub lang-ar">صلاحية كاملة لجميع وظائف النظام — يدير الفروع والمديرين والموظفين ويراقب كل العمليات</div>
        <div class="role-banner-sub lang-en">Full access to all system functions — manages branches, managers, employees, and monitors all operations</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">⚙️</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">إعداد النظام</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">System Setup</div><div class="flow-node-sub lang-ar">فروع + موظفون</div><div class="flow-node-sub lang-en">Branches + Staff</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="flow-node-ico">📥</div><div class="flow-node-lbl lang-ar" style="color:var(--gr)">استيراد Excel</div><div class="flow-node-lbl lang-en" style="color:var(--gr)">Import Excel</div><div class="flow-node-sub lang-ar">بيانات شهرية</div><div class="flow-node-sub lang-en">Monthly data</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="flow-node-ico">🗂</div><div class="flow-node-lbl lang-ar" style="color:var(--or)">مراجعة الكروت</div><div class="flow-node-lbl lang-en" style="color:var(--or)">Review Cards</div><div class="flow-node-sub lang-ar">إضافة / تعديل</div><div class="flow-node-sub lang-en">Add / Edit</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="flow-node-ico">📞</div><div class="flow-node-lbl lang-ar" style="color:var(--pu)">قبول CC</div><div class="flow-node-lbl lang-en" style="color:var(--pu)">Accept CC</div><div class="flow-node-sub lang-ar">جميع الفروع</div><div class="flow-node-sub lang-en">All branches</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(30,204,128,.1);border:1px solid rgba(30,204,128,.3)"><div class="flow-node-ico">✅</div><div class="flow-node-lbl lang-ar" style="color:var(--gr)">اعتماد الموظفين</div><div class="flow-node-lbl lang-en" style="color:var(--gr)">Approve Staff</div><div class="flow-node-sub lang-ar">كل الفروع</div><div class="flow-node-sub lang-en">All branches</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📈</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">التقارير</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Reports</div><div class="flow-node-sub">PDF / Excel</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">⚙️</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">1</span> إعداد النظام</div>
          <div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">1</span> System Setup</div>
          <div class="step-desc lang-ar"><strong>الإعدادات → الفروع</strong>: أضف فروع الشركة وكودها<br><strong>الإعدادات → الموظفون</strong>: أضف بروكرات ومسوّقين بعمولاتهم<br><strong>الإعدادات → المديرون</strong>: أضف مديري الفروع وحدد صلاحياتهم</div>
          <div class="step-desc lang-en"><strong>Settings → Branches</strong>: Add company branches and their codes<br><strong>Settings → Employees</strong>: Add brokers and marketers with their commissions<br><strong>Settings → Managers</strong>: Add branch managers and set their permissions</div>
          <span class="step-note lang-ar">🔑 يُنفَّذ مرة واحدة عند بدء تشغيل النظام</span>
          <span class="step-note lang-en">🔑 Done once when the system is first set up</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">📥</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--gr)"><span class="step-num">2</span> استيراد البيانات الشهرية</div>
          <div class="step-title lang-en" style="color:var(--gr)"><span class="step-num">2</span> Import Monthly Data</div>
          <div class="step-desc lang-ar">القائمة الجانبية → <strong>📥 استيراد بيانات</strong> → ارفع ملف Excel<br>راجع المعاينة → تأكد من صحة الأعمدة → <strong>رفع واستيراد</strong></div>
          <div class="step-desc lang-en">Sidebar → <strong>📥 Import Data</strong> → upload Excel file<br>Review preview → verify column mapping → <strong>Upload & Import</strong></div>
          <span class="step-note lang-ar">💡 يدعم آلاف السجلات في ملف واحد — بدون حد أقصى</span>
          <span class="step-note lang-en">💡 Supports thousands of records in one file — no limit</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">🗂</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--or)"><span class="step-num">3</span> مراجعة كروت العمولة</div>
          <div class="step-title lang-en" style="color:var(--or)"><span class="step-num">3</span> Review Commission Cards</div>
          <div class="step-desc lang-ar"><strong>كروت العمولات</strong>: كل الكروت مع الفلاتر (شهر / فرع / بروكر / حالة)<br><strong>➕ كرت جديد</strong>: أدخل رقم الحساب، الشهر، البروكر، المسوّق، الإيداع<br><strong>✏️ تعديل</strong>: ابحث برقم الحساب → عدّل → اختر سبب التعديل</div>
          <div class="step-desc lang-en"><strong>Commission Cards</strong>: All cards with filters (month / branch / broker / status)<br><strong>➕ New Card</strong>: Enter account#, month, broker, marketer, deposit<br><strong>✏️ Edit</strong>: Search by account# → edit → select edit reason</div>
          <span class="step-note lang-ar">📋 كل تعديل يُسجَّل تلقائياً باسم المعدّل والتاريخ والسبب</span>
          <span class="step-note lang-en">📋 All edits are automatically logged with editor name, date, and reason</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--pu)"><span class="step-num">4</span> كروت مركز الاتصال CC</div>
          <div class="step-title lang-en" style="color:var(--pu)"><span class="step-num">4</span> Call Center CC Cards</div>
          <div class="step-desc lang-ar"><strong>📞 مركز الاتصال</strong> → الكروت المعلّقة من جميع الفروع<br><strong>قبول</strong>: الكرت ينتقل فوراً للقائمة الرئيسية — <strong>رفض</strong>: أدخل سبب الرفض</div>
          <div class="step-desc lang-en"><strong>📞 Call Center</strong> → pending cards from all branches<br><strong>Accept</strong>: card moves immediately to main list — <strong>Reject</strong>: enter rejection reason</div>
          <span class="step-note lang-ar">👁 ترى كروت جميع الفروع — مدير الفرع يرى فرعه فقط</span>
          <span class="step-note lang-en">👁 You see all branch cards — branch managers see their branch only</span>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(30,204,128,.2),rgba(30,204,128,.06));border:1px solid rgba(30,204,128,.3)">✅</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--gr)"><span class="step-num">5</span> اعتماد الموظفين الجدد</div>
          <div class="step-title lang-en" style="color:var(--gr)"><span class="step-num">5</span> Approve New Employees</div>
          <div class="step-desc lang-ar"><strong>الإعدادات → الاعتمادات</strong>: موظفون بانتظار موافقتك أضافهم مدراء الفروع<br>اعتمد أو ارفض — بعد الاعتماد يظهر في قوائم الاختيار عند إنشاء الكروت</div>
          <div class="step-desc lang-en"><strong>Settings → Approvals</strong>: employees awaiting your approval added by branch managers<br>Approve or reject — after approval they appear in card creation dropdowns</div>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">6</span> التقارير والإحصائيات</div>
          <div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">6</span> Reports & Statistics</div>
          <div class="step-desc lang-ar"><strong>📊 لوحة المتابعة</strong>: ملخص الأرقام + ترتيب البروكرات + آخر التعديلات<br><strong>📈 التقارير</strong>: فلتر متقدم → تصدير PDF أو Excel<br><strong>📊 تقرير ديناميكي</strong>: تحليل مخصص حسب أي معيار</div>
          <div class="step-desc lang-en"><strong>📊 Dashboard</strong>: Numbers summary + broker rankings + recent edits<br><strong>📈 Reports</strong>: Advanced filter → export PDF or Excel<br><strong>📊 Dynamic Report</strong>: Custom analysis by any criterion</div>
        </div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">👤</div>
        <div>
          <div class="step-title lang-ar" style="color:var(--pu)"><span class="step-num">7</span> إدارة المديرين والدعوات</div>
          <div class="step-title lang-en" style="color:var(--pu)"><span class="step-num">7</span> Manage Managers & Invites</div>
          <div class="step-desc lang-ar"><strong>الإعدادات → المديرون</strong>: أضف مديراً مع تحديد صلاحياته<br><strong>📧 دعوة</strong>: أضف الإيميل → يصله بريد إلكتروني → يسجّل بنفسه<br>تعديل الصلاحيات أو تعطيل المدير في أي وقت</div>
          <div class="step-desc lang-en"><strong>Settings → Managers</strong>: Add manager with defined permissions<br><strong>📧 Invite</strong>: Add email → they receive an email → self-register<br>Edit permissions or disable manager at any time</div>
        </div>
      </div>
    </div>
    {{-- Permissions table --}}
    <div style="margin-top:18px;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:16px">
      <div style="font-size:13px;font-weight:800;color:var(--pri2);margin-bottom:12px" id="guide-perm-title">🔐 جدول مقارنة الصلاحيات</div>
      <div style="overflow-x:auto">
      <table class="perm-table">
        <thead><tr>
          <th id="guide-pt-func">الوظيفة / الصفحة</th>
          <th id="guide-pt-fa">المدير المالي</th>
          <th id="guide-pt-bm">مدير الفرع</th>
          <th id="guide-pt-vw">مشاهد</th>
        </tr></thead>
        <tbody id="guide-perm-tbody"></tbody>
      </table>
      </div>
    </div>
  </div>{{-- gpanel-fa --}}

  {{-- ════ Branch Manager panel ════ --}}
  <div class="guide-role-panel" id="gpanel-bm">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(34,201,122,.1),rgba(34,201,122,.04));border:1px solid rgba(34,201,122,.25)">
      <div class="role-banner-icon">🏢</div>
      <div>
        <div class="role-banner-title" style="color:var(--gr)">🏢 Branch Manager — مدير الفرع</div>
        <div class="role-banner-sub lang-ar">تعمل على فرعك المحدد فقط — الصلاحيات تحددها الإدارة المالية وقد تختلف من مدير لآخر</div>
        <div class="role-banner-sub lang-en">You work on your assigned branch only — permissions are set by Finance Admin and may vary</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="flow-node-ico">🔐</div><div class="flow-node-lbl lang-ar" style="color:var(--gr)">تسجيل الدخول</div><div class="flow-node-lbl lang-en" style="color:var(--gr)">Login</div><div class="flow-node-sub lang-ar">بيانات فرعك</div><div class="flow-node-sub lang-en">Your branch</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">🗂</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">كروت الفرع</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Branch Cards</div><div class="flow-node-sub lang-ar">عرض / إضافة</div><div class="flow-node-sub lang-en">View / Add</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="flow-node-ico">📞</div><div class="flow-node-lbl lang-ar" style="color:var(--pu)">مركز الاتصال</div><div class="flow-node-lbl lang-en" style="color:var(--pu)">Call Center</div><div class="flow-node-sub lang-ar">قبول / رفض</div><div class="flow-node-sub lang-en">Accept / Reject</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="flow-node-ico">👥</div><div class="flow-node-lbl lang-ar" style="color:var(--or)">إضافة موظفين</div><div class="flow-node-lbl lang-en" style="color:var(--or)">Add Employees</div><div class="flow-node-sub lang-ar">بانتظار اعتماد</div><div class="flow-node-sub lang-en">Pending approval</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📊</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">تقارير الفرع</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Branch Reports</div><div class="flow-node-sub lang-ar">إذا مسموح</div><div class="flow-node-sub lang-en">If permitted</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">🔐</div><div><div class="step-title lang-ar" style="color:var(--gr)"><span class="step-num">1</span> تسجيل الدخول</div><div class="step-title lang-en" style="color:var(--gr)"><span class="step-num">1</span> Login</div><div class="step-desc lang-ar">ادخل ببيانات حسابك التي أرسلتها الإدارة المالية على بريدك الإلكتروني<br>إذا أول مرة → سيُطلب منك تغيير كلمة المرور</div><div class="step-desc lang-en">Login with credentials sent by Finance Admin to your email<br>First login → you will be prompted to change your password</div><span class="step-note lang-ar">📧 إذا لم تستلم البيانات تواصل مع المدير المالي</span><span class="step-note lang-en">📧 If you didn't receive credentials, contact Finance Admin</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div><div><div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">2</span> كروت العمولة (فرعك فقط)</div><div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">2</span> Commission Cards (your branch only)</div><div class="step-desc lang-ar"><strong>كروت العمولات</strong>: ترى كروت فرعك فقط — مصنّفة بالشهر / الحالة / النوع<br>إضافة كرت جديد أو تعديل موجود (إذا كانت لديك الصلاحية)</div><div class="step-desc lang-en"><strong>Commission Cards</strong>: Shows your branch cards only — filtered by month / status / type<br>Add new card or edit existing (if you have permission)</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div><div><div class="step-title lang-ar" style="color:var(--pu)"><span class="step-num">3</span> معالجة كروت CC</div><div class="step-title lang-en" style="color:var(--pu)"><span class="step-num">3</span> Process CC Cards</div><div class="step-desc lang-ar"><strong>📞 مركز الاتصال</strong>: كروت واردة خاصة بفرعك<br>اضغط <strong>قبول</strong> → الكرت ينتقل لقائمة كروت العمولة<br>اضغط <strong>رفض</strong> → أدخل سبب الرفض</div><div class="step-desc lang-en"><strong>📞 Call Center</strong>: Incoming cards for your branch<br>Click <strong>Accept</strong> → card moves to commission cards list<br>Click <strong>Reject</strong> → enter rejection reason</div><span class="step-note lang-ar">⏰ راجع قسم CC يومياً لمعالجة الكروت في الوقت المناسب</span><span class="step-note lang-en">⏰ Check CC section daily to process cards on time</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">👥</div><div><div class="step-title lang-ar" style="color:var(--or)"><span class="step-num">4</span> إضافة موظفين</div><div class="step-title lang-en" style="color:var(--or)"><span class="step-num">4</span> Add Employees</div><div class="step-desc lang-ar"><strong>الإعدادات → الموظفون</strong>: أضف بروكراً أو مسوّقاً جديداً<br>يُرسل طلب للمدير المالي للاعتماد — بعده يظهر في قوائم الكروت</div><div class="step-desc lang-en"><strong>Settings → Employees</strong>: Add a new broker or marketer<br>Request sent to Finance Admin for approval — then appears in card dropdowns</div><span class="step-note lang-ar">⏳ الموظف لا يظهر في الكروت إلا بعد موافقة المدير المالي</span><span class="step-note lang-en">⏳ Employee won't appear in cards until Finance Admin approves</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📊</div><div><div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">5</span> التقارير (إذا مفعّلة)</div><div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">5</span> Reports (if enabled)</div><div class="step-desc lang-ar">إذا منحك المدير المالي صلاحية التقارير → عرض وتصدير تقارير فرعك<br>فلتر بالشهر والبروكر وتصدير PDF / Excel</div><div class="step-desc lang-en">If Finance Admin granted you reports permission → view and export your branch reports<br>Filter by month and broker, export PDF / Excel</div></div></div>
    </div>
  </div>{{-- gpanel-bm --}}

  {{-- ════ Call Center panel ════ --}}
  <div class="guide-role-panel" id="gpanel-cc">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(138,120,240,.1),rgba(138,120,240,.04));border:1px solid rgba(138,120,240,.25)">
      <div class="role-banner-icon">📞</div>
      <div>
        <div class="role-banner-title" style="color:var(--pu)">📞 Call Center CC — مركز الاتصال</div>
        <div class="role-banner-sub lang-ar">إدخال كروت العملاء الجدد عبر CC وإرسالها للمراجعة من مدير الفرع</div>
        <div class="role-banner-sub lang-en">Enter new customer cards via CC and send for branch manager review</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="flow-node-ico">📞</div><div class="flow-node-lbl lang-ar" style="color:var(--pu)">استقبال الكرت</div><div class="flow-node-lbl lang-en" style="color:var(--pu)">Receive Card</div><div class="flow-node-sub lang-ar">من العميل</div><div class="flow-node-sub lang-en">From client</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">➕</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">إدخال البيانات</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Enter Data</div><div class="flow-node-sub lang-ar">رقم حساب + بيانات</div><div class="flow-node-sub lang-en">Account# + info</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="flow-node-ico">⏳</div><div class="flow-node-lbl lang-ar" style="color:var(--or)">بانتظار المراجعة</div><div class="flow-node-lbl lang-en" style="color:var(--or)">Pending Review</div><div class="flow-node-sub lang-ar">مدير الفرع</div><div class="flow-node-sub lang-en">Branch Manager</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="flow-node-ico">✅</div><div class="flow-node-lbl lang-ar" style="color:var(--gr)">قبول المدير</div><div class="flow-node-lbl lang-en" style="color:var(--gr)">Manager Accept</div><div class="flow-node-sub lang-ar">يدخل القائمة الرئيسية</div><div class="flow-node-sub lang-en">Goes to main list</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📊</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">الكرت نشط</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Card Active</div><div class="flow-node-sub lang-ar">في النظام</div><div class="flow-node-sub lang-en">In system</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div><div><div class="step-title lang-ar" style="color:var(--pu)"><span class="step-num">1</span> فتح مركز الاتصال</div><div class="step-title lang-en" style="color:var(--pu)"><span class="step-num">1</span> Open Call Center</div><div class="step-desc lang-ar">من القائمة الجانبية → <strong>📞 مركز الاتصال</strong><br>هنا ترى الكروت الواردة المنتظرة للمراجعة</div><div class="step-desc lang-en">From sidebar → <strong>📞 Call Center</strong><br>Here you see incoming cards pending review</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">➕</div><div><div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">2</span> إدخال كرت جديد</div><div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">2</span> Enter New Card</div><div class="step-desc lang-ar"><strong>➕ كرت جديد</strong> → أدخل: رقم الحساب • الشهر • الفرع • نوع الحساب (NEW/SUB) • البروكر والمسوّق • الإيداع والعمولات</div><div class="step-desc lang-en"><strong>➕ New Card</strong> → Enter: Account# • Month • Branch • Account type (NEW/SUB) • Broker & Marketer • Deposit & Commissions</div><span class="step-note lang-ar">📌 الكرت يُرسل تلقائياً لمراجعة مدير الفرع</span><span class="step-note lang-en">📌 Card is automatically sent for branch manager review</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">⏳</div><div><div class="step-title lang-ar" style="color:var(--or)"><span class="step-num">3</span> متابعة حالة الكروت</div><div class="step-title lang-en" style="color:var(--or)"><span class="step-num">3</span> Track Card Status</div><div class="step-desc lang-ar">في قائمة CC ترى: <strong>معلّق</strong> أو <strong>مقبول</strong> أو <strong>مرفوض مع السبب</strong><br>إذا رُفض → اقرأ السبب وأعد الإدخال بعد التصحيح</div><div class="step-desc lang-en">In CC list you see: <strong>Pending</strong> or <strong>Accepted</strong> or <strong>Rejected with reason</strong><br>If rejected → read the reason and re-enter after correction</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">✅</div><div><div class="step-title lang-ar" style="color:var(--gr)"><span class="step-num">4</span> بعد القبول</div><div class="step-title lang-en" style="color:var(--gr)"><span class="step-num">4</span> After Acceptance</div><div class="step-desc lang-ar">عند قبول مدير الفرع → الكرت يظهر في <strong>كروت العمولات الرئيسية</strong> تلقائياً<br>يمكن البحث عنه برقم الحساب للتحقق</div><div class="step-desc lang-en">When branch manager accepts → card appears in <strong>main commission cards</strong> automatically<br>Search by account# to verify</div></div></div>
    </div>
  </div>{{-- gpanel-cc --}}

  {{-- ════ Viewer panel ════ --}}
  <div class="guide-role-panel" id="gpanel-vw">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(90,128,160,.1),rgba(90,128,160,.04));border:1px solid rgba(90,128,160,.25)">
      <div class="role-banner-icon">👁</div>
      <div>
        <div class="role-banner-title" style="color:var(--mu)">👁 Viewer — مشاهد</div>
        <div class="role-banner-sub lang-ar">صلاحية قراءة فقط — مشاهدة البيانات والتقارير دون إمكانية التعديل أو الإضافة</div>
        <div class="role-banner-sub lang-en">Read-only access — view data and reports without ability to edit or add</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(90,128,160,.1);border:1px solid rgba(90,128,160,.3)"><div class="flow-node-ico">🔐</div><div class="flow-node-lbl lang-ar" style="color:var(--mu)">تسجيل الدخول</div><div class="flow-node-lbl lang-en" style="color:var(--mu)">Login</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📊</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">لوحة المتابعة</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Dashboard</div><div class="flow-node-sub lang-ar">قراءة فقط</div><div class="flow-node-sub lang-en">Read only</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">🗂</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">عرض الكروت</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">View Cards</div><div class="flow-node-sub lang-ar">إذا مسموح</div><div class="flow-node-sub lang-en">If permitted</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📈</div><div class="flow-node-lbl lang-ar" style="color:var(--pri2)">التقارير</div><div class="flow-node-lbl lang-en" style="color:var(--pri2)">Reports</div><div class="flow-node-sub lang-ar">إذا مسموح</div><div class="flow-node-sub lang-en">If permitted</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(90,128,160,.2),rgba(90,128,160,.06));border:1px solid rgba(90,128,160,.3)">🔐</div><div><div class="step-title lang-ar" style="color:var(--mu)"><span class="step-num">1</span> تسجيل الدخول</div><div class="step-title lang-en" style="color:var(--mu)"><span class="step-num">1</span> Login</div><div class="step-desc lang-ar">ادخل ببيانات حسابك — إذا أول مرة غيّر كلمة المرور المؤقتة</div><div class="step-desc lang-en">Login with your credentials — first time: change the temporary password</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📊</div><div><div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">2</span> لوحة المتابعة</div><div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">2</span> Dashboard</div><div class="step-desc lang-ar">ملخص الأرقام: إجمالي الكروت، الإيداعات، ترتيب البروكرات — محدّث لحظياً</div><div class="step-desc lang-en">Numbers summary: total cards, deposits, broker rankings — updated in real time</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div><div><div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">3</span> عرض الكروت (إذا مسموح)</div><div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">3</span> View Cards (if permitted)</div><div class="step-desc lang-ar">إذا منحك المدير صلاحية الكروت → عرض وفلترة — <strong>لا يمكن الإضافة أو التعديل</strong></div><div class="step-desc lang-en">If manager granted you cards permission → view and filter — <strong>no adding or editing</strong></div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div><div><div class="step-title lang-ar" style="color:var(--pri2)"><span class="step-num">4</span> التقارير (إذا مسموح)</div><div class="step-title lang-en" style="color:var(--pri2)"><span class="step-num">4</span> Reports (if permitted)</div><div class="step-desc lang-ar">إذا منحك المدير صلاحية التقارير → عرض وتصدير PDF / Excel</div><div class="step-desc lang-en">If manager granted reports permission → view and export PDF / Excel</div></div></div>
    </div>
  </div>{{-- gpanel-vw --}}

  {{-- SECTION 2 — USER MANUAL --}}
  <div class="guide-section-lbl"><span id="guide-sec-manual">📚 دليل المستخدم — User Manual</span></div>

  <div class="manual-grid">
    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div>
      <div class="manual-body">
        <div class="manual-title lang-ar" style="color:var(--pri2)">1. إضافة كرت عمولة جديد</div>
        <div class="manual-title lang-en" style="color:var(--pri2)">1. Add New Commission Card</div>
        <div class="manual-desc lang-ar">اضغط <strong>➕ كرت جديد</strong> من شريط التنقل العلوي أو القائمة الجانبية<br>أدخل: رقم الحساب — الشهر — الفرع — البروكر — المسوّق — الإيداع الأولي والشهري — نوع الحساب (NEW جديد / SUB فرعي) — ثم اضغط <strong>حفظ</strong></div>
        <div class="manual-desc lang-en">Click <strong>➕ New Card</strong> from top nav or sidebar<br>Enter: Account# — Month — Branch — Broker — Marketer — Initial & Monthly Deposit — Account type (NEW / SUB) — then click <strong>Save</strong></div>
        <span class="manual-tip lang-ar">💡 رقم الحساب يجب أن يكون فريداً لنفس الشهر</span>
        <span class="manual-tip lang-en">💡 Account number must be unique per month</span>
      </div>
    </div>
    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(245,166,35,.18),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">✏️</div>
      <div class="manual-body">
        <div class="manual-title lang-ar" style="color:var(--or)">2. تعديل كرت موجود</div>
        <div class="manual-title lang-en" style="color:var(--or)">2. Edit Existing Card</div>
        <div class="manual-desc lang-ar">اضغط <strong>✏️ تعديل</strong> من شريط التنقل العلوي → ابحث برقم الحساب → اختر الكرت المطلوب → عدّل البيانات → اختر <strong>سبب التعديل</strong> من القائمة → <strong>حفظ</strong><br>التعديل يُسجَّل تلقائياً مع اسم المعدّل والتاريخ والسبب</div>
        <div class="manual-desc lang-en">Click <strong>✏️ Edit</strong> from top nav → search by account# → select the card → edit data → choose <strong>edit reason</strong> from dropdown → <strong>Save</strong><br>Edit is automatically logged with editor name, date, and reason</div>
        <span class="manual-tip lang-ar">📋 جميع التعديلات محفوظة في سجل الحركات بصفحة "الحسابات المعدّلة"</span>
        <span class="manual-tip lang-en">📋 All edits are saved in the audit log in "Modified Accounts" page</span>
      </div>
    </div>
    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(34,201,122,.18),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">📥</div>
      <div class="manual-body">
        <div class="manual-title lang-ar" style="color:var(--gr)">3. استيراد بيانات Excel</div>
        <div class="manual-title lang-en" style="color:var(--gr)">3. Import Excel Data</div>
        <div class="manual-desc lang-ar">القائمة الجانبية → <strong>📥 استيراد بيانات</strong> → ارفع ملف Excel بالأعمدة المطلوبة → راجع المعاينة → <strong>رفع واستيراد</strong><br>النظام يتحقق من كل سطر ويُظهر الأخطاء إن وُجدت قبل الحفظ النهائي</div>
        <div class="manual-desc lang-en">Sidebar → <strong>📥 Import Data</strong> → upload Excel file with required columns → review preview → <strong>Upload & Import</strong><br>System validates each row and shows errors if any before final save</div>
        <span class="manual-tip lang-ar">💡 لا حد للصفوف — يمكن رفع آلاف السجلات في ملف واحد</span>
        <span class="manual-tip lang-en">💡 No row limit — thousands of records in one file</span>
      </div>
    </div>
    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(138,120,240,.18),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div>
      <div class="manual-body">
        <div class="manual-title lang-ar" style="color:var(--pu)">4. إدارة كروت مركز الاتصال CC</div>
        <div class="manual-title lang-en" style="color:var(--pu)">4. Manage Call Center CC Cards</div>
        <div class="manual-desc lang-ar"><strong>📞 مركز الاتصال</strong> من القائمة الجانبية → قائمة الكروت المعلّقة<br><strong>قبول</strong>: الكرت ينتقل فوراً لقائمة كروت العمولة الرئيسية<br><strong>رفض</strong>: أدخل سبب الرفض — يُحفظ مع الكرت في سجل الحركات</div>
        <div class="manual-desc lang-en"><strong>📞 Call Center</strong> from sidebar → pending cards list<br><strong>Accept</strong>: card moves immediately to main commission cards<br><strong>Reject</strong>: enter rejection reason — saved with card in audit log</div>
        <span class="manual-tip lang-ar">مدير الفرع يرى فرعه فقط — المدير المالي يرى كل الفروع</span>
        <span class="manual-tip lang-en">Branch manager sees their branch only — Finance Admin sees all branches</span>
      </div>
    </div>
    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div>
      <div class="manual-body">
        <div class="manual-title lang-ar" style="color:var(--pri2)">5. التقارير والإحصائيات</div>
        <div class="manual-title lang-en" style="color:var(--pri2)">5. Reports & Statistics</div>
        <div class="manual-desc lang-ar"><strong>📈 التقارير</strong>: فلتر بالشهر / الفرع / البروكر / الحالة → تصدير PDF أو Excel<br><strong>📊 لوحة المتابعة</strong>: ملخص الأرقام + ترتيب البروكرات + آخر التعديلات<br><strong>📊 تقرير ديناميكي</strong>: تحليل مخصص حسب أي معيار تختاره</div>
        <div class="manual-desc lang-en"><strong>📈 Reports</strong>: Filter by month / branch / broker / status → export PDF or Excel<br><strong>📊 Dashboard</strong>: Numbers summary + broker rankings + recent edits<br><strong>📊 Dynamic Report</strong>: Custom analysis by any criterion you choose</div>
        <span class="manual-tip lang-ar">الأرقام في الداشبورد محسوبة من كامل قاعدة البيانات بدون حد أقصى</span>
        <span class="manual-tip lang-en">Dashboard numbers calculated from the entire database with no limit</span>
      </div>
    </div>
    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(30,204,128,.18),rgba(30,204,128,.06));border:1px solid rgba(30,204,128,.3)">👥</div>
      <div class="manual-body">
        <div class="manual-title lang-ar" style="color:var(--gr)">6. إدارة الموظفين والمديرين</div>
        <div class="manual-title lang-en" style="color:var(--gr)">6. Manage Employees & Managers</div>
        <div class="manual-desc lang-ar"><strong>الموظفون</strong>: إضافة بروكر / مسوّق بعمولاته → طلب اعتماد من المدير المالي → بعد الاعتماد يظهر في قوائم الكروت<br><strong>المديرون</strong> (للمدير المالي فقط): إضافة مدير فرع يدوياً أو دعوته بالإيميل — تحديد صلاحياته وتعديلها في أي وقت</div>
        <div class="manual-desc lang-en"><strong>Employees</strong>: Add broker / marketer with commissions → request Finance Admin approval → after approval appears in card dropdowns<br><strong>Managers</strong> (Finance Admin only): Add branch manager manually or invite by email — set and update permissions at any time</div>
      </div>
    </div>
  </div>{{-- manual-grid --}}

  {{-- Roles summary --}}
  <div class="guide-section-lbl" style="margin-top:28px"><span id="guide-sec-roles">🔐 ملخص الأدوار والصلاحيات</span></div>
  <div class="roles-grid">
    <div class="role-card" style="border-color:rgba(26,173,186,.3)">
      <div class="role-card-title" style="color:var(--pri2)">💼 <span class="lang-ar">المدير المالي</span><span class="lang-en">Finance Admin</span></div>
      <div class="role-card-list lang-ar">✅ كل الصفحات والوظائف<br>✅ استيراد Excel وتصدير البيانات<br>✅ إدارة المديرين والفروع<br>✅ اعتماد الموظفين الجدد<br>✅ إعدادات النظام الكاملة</div>
      <div class="role-card-list lang-en">✅ All pages and functions<br>✅ Import Excel & export data<br>✅ Manage managers & branches<br>✅ Approve new employees<br>✅ Full system settings</div>
    </div>
    <div class="role-card" style="border-color:rgba(34,201,122,.3)">
      <div class="role-card-title" style="color:var(--gr)">🏢 <span class="lang-ar">مدير الفرع</span><span class="lang-en">Branch Manager</span></div>
      <div class="role-card-list lang-ar">✅ فرعه المحدد فقط<br>✅ كروت العمولة (حسب صلاحية)<br>✅ مركز الاتصال CC<br>✅ إضافة موظفين (بانتظار اعتماد)<br>⚙️ باقي الصلاحيات يحددها المدير المالي</div>
      <div class="role-card-list lang-en">✅ Assigned branch only<br>✅ Commission cards (by permission)<br>✅ Call Center CC<br>✅ Add employees (pending approval)<br>⚙️ Other permissions set by Finance Admin</div>
    </div>
    <div class="role-card" style="border-color:rgba(138,120,240,.3)">
      <div class="role-card-title" style="color:var(--pu)">📞 <span class="lang-ar">كول سنتر / مشاهد</span><span class="lang-en">Call Center / Viewer</span></div>
      <div class="role-card-list lang-ar">✅ إدخال كروت CC<br>✅ متابعة حالة الكروت<br>✅ قراءة البيانات والتقارير<br>❌ لا تعديل على الكروت<br>❌ لا وصول للإعدادات</div>
      <div class="role-card-list lang-en">✅ Enter CC cards<br>✅ Track card status<br>✅ Read data & reports<br>❌ No card editing<br>❌ No settings access</div>
    </div>
  </div>

</div>{{-- guide-page --}}

@endsection

@push('scripts')
<script>
function switchGuideTab(role) {
  ['fa','bm','cc','vw'].forEach(r => {
    document.getElementById('gtab-'  +r)?.classList.toggle('active', r===role);
    document.getElementById('gpanel-'+r)?.classList.toggle('active', r===role);
  });
}

/* Auto-select current user's role */
(function(){
  const role = (typeof CURRENT_USER !== 'undefined' && CURRENT_USER?.role) ? CURRENT_USER.role : 'finance_admin';
  if      (role === 'finance_admin')  switchGuideTab('fa');
  else if (role === 'branch_manager') switchGuideTab('bm');
  else                                switchGuideTab('vw');
})();

/* ── Bilingual ──────────────────────────────────────────── */
const GUIDE_I18N = {
  ar: {
    mainTitle:'📖 دليل تشغيل النظام',
    mainSub:'اختر دورك لمشاهدة Workflow خاص بك — ثم اطّلع على دليل المستخدم التفصيلي',
    secWorkflow:'🔄 مخطط سير العمل — Workflow',
    secManual:'📚 دليل المستخدم — User Manual',
    secRoles:'🔐 ملخص الأدوار والصلاحيات',
    tabFa:'المدير المالي', badgeFa:'كامل',
    tabBm:'مدير الفرع',   badgeBm:'فرعي',
    tabCc:'كول سنتر CC',  badgeCc:'متابعة',
    tabVw:'مشاهد',        badgeVw:'قراءة',
    permTitle:'🔐 جدول مقارنة الصلاحيات',
    ptFunc:'الوظيفة / الصفحة', ptFa:'المدير المالي', ptBm:'مدير الفرع', ptVw:'مشاهد',
    pYes:'✅ كاملة', pBranchOnly:'✅ فرعه فقط', pRead:'✅ قراءة', pAllBr:'✅ كل الفروع',
    pByCond:'⚙️ حسب صلاحية', pNo:'❌ لا', pYesAdd:'✅ + اعتماد', pAddOnly:'⚙️ إضافة فقط', pFull:'✅ كاملة',
    rows:[
      ['لوحة المتابعة','كاملة','فرعه فقط','قراءة'],
      ['كروت العمولة — عرض','كل الفروع','حسب صلاحية','حسب صلاحية'],
      ['إضافة كرت جديد','نعم','حسب صلاحية','❌'],
      ['تعديل كرت','نعم','حسب صلاحية','❌'],
      ['مركز الاتصال CC','كل الفروع','فرعه فقط','❌'],
      ['استيراد Excel','نعم','حسب صلاحية','❌'],
      ['تصدير PDF / Excel','نعم','حسب صلاحية','❌'],
      ['التقارير','كل الفروع','حسب صلاحية','حسب صلاحية'],
      ['إدارة الموظفين','+ اعتماد','إضافة فقط','❌'],
      ['إدارة المديرين','نعم','❌','❌'],
      ['الإعدادات','كاملة','❌','❌'],
    ],
  },
  en: {
    mainTitle:'📖 System Operation Guide',
    mainSub:'Select your role to see your Workflow — then read the detailed user manual',
    secWorkflow:'🔄 Workflow Diagram',
    secManual:'📚 User Manual',
    secRoles:'🔐 Roles & Permissions Summary',
    tabFa:'Finance Admin', badgeFa:'Full',
    tabBm:'Branch Manager', badgeBm:'Branch',
    tabCc:'Call Center CC', badgeCc:'Tracking',
    tabVw:'Viewer',         badgeVw:'Read',
    permTitle:'🔐 Permissions Comparison Table',
    ptFunc:'Function / Page', ptFa:'Finance Admin', ptBm:'Branch Manager', ptVw:'Viewer',
    rows:[
      ['Dashboard','Full','Branch only','Read'],
      ['Commission Cards — View','All branches','By permission','By permission'],
      ['Add New Card','Yes','By permission','❌'],
      ['Edit Card','Yes','By permission','❌'],
      ['Call Center CC','All branches','Branch only','❌'],
      ['Import Excel','Yes','By permission','❌'],
      ['Export PDF / Excel','Yes','By permission','❌'],
      ['Reports','All branches','By permission','By permission'],
      ['Manage Employees','+ Approve','Add only','❌'],
      ['Manage Managers','Yes','❌','❌'],
      ['Settings','Full','❌','❌'],
    ],
  }
};

function guideL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function guide(k) { const l=guideL(); return (GUIDE_I18N[l]||GUIDE_I18N.ar)[k]||(GUIDE_I18N.ar)[k]||k; }

function guideApplyLang() {
  const L = guideL();
  const _t = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };

  // Static elements
  _t('guide-main-title',   guide('mainTitle'));
  _t('guide-main-sub',     guide('mainSub'));
  _t('guide-sec-workflow', guide('secWorkflow'));
  _t('guide-sec-manual',   guide('secManual'));
  _t('guide-sec-roles',    guide('secRoles'));
  _t('gtab-fa-lbl', guide('tabFa')); _t('gtab-fa-badge', guide('badgeFa'));
  _t('gtab-bm-lbl', guide('tabBm')); _t('gtab-bm-badge', guide('badgeBm'));
  _t('gtab-cc-lbl', guide('tabCc')); _t('gtab-cc-badge', guide('badgeCc'));
  _t('gtab-vw-lbl', guide('tabVw')); _t('gtab-vw-badge', guide('badgeVw'));
  _t('guide-perm-title', guide('permTitle'));
  _t('guide-pt-func', guide('ptFunc')); _t('guide-pt-fa', guide('ptFa'));
  _t('guide-pt-bm', guide('ptBm'));   _t('guide-pt-vw', guide('ptVw'));

  // Permissions table body
  const tbody = document.getElementById('guide-perm-tbody');
  if (tbody) {
    const rows = guide('rows');
    const yes  = '<span class="p-yes">✅</span>';
    const no   = '<span class="p-no">❌</span>';
    const cond = (v) => v.includes('❌') ? no : v.includes('✅') ? yes : `<span class="p-cond">⚙️ ${v}</span>`;
    const full = (v) => v === '❌' ? no : `<span class="p-yes">✅ ${v}</span>`;
    tbody.innerHTML = rows.map(([fn,fa,bm,vw]) =>
      `<tr><td>${fn}</td><td>${full(fa)}</td><td>${cond(bm)}</td><td>${cond(vw)}</td></tr>`
    ).join('');
  }

  // Toggle lang-ar / lang-en blocks
  document.querySelectorAll('.lang-ar').forEach(el => el.style.display = L === 'en' ? 'none' : '');
  document.querySelectorAll('.lang-en').forEach(el => el.style.display = L === 'en' ? '' : 'none');
}

const _guideOrigApplyLang = window.applyLang;
window.applyLang = function(lang) { if (_guideOrigApplyLang) _guideOrigApplyLang(lang); guideApplyLang(); };

guideApplyLang();
</script>
@endpush
