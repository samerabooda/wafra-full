@extends('layouts.app')
@section('title','دليل التشغيل')
@section('page-title','دليل التشغيل')

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

/* ── User-manual steps (دليل المستخدم) ── */
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
</style>

<div class="guide-page">

  {{-- Page Header --}}
  <div class="guide-pg-header">
    <h1>📖 دليل تشغيل النظام</h1>
    <p>اختر دورك لمشاهدة Workflow خاص بك — ثم اطّلع على دليل المستخدم التفصيلي</p>
  </div>

  {{-- ═══════════════════════════════════════════════════════
       SECTION 1 — WORKFLOW DIAGRAM (ROLE-BASED)
       ═══════════════════════════════════════════════════════ --}}
  <div class="guide-section-lbl"><span>🔄 مخطط سير العمل — Workflow Diagram</span></div>

  {{-- Role tabs --}}
  <div class="guide-role-tabs">
    <button class="guide-role-tab" id="gtab-fa" onclick="switchGuideTab('fa')">
      💼 المدير المالي <span class="guide-tab-badge">كامل</span>
    </button>
    <button class="guide-role-tab" id="gtab-bm" onclick="switchGuideTab('bm')">
      🏢 مدير الفرع <span class="guide-tab-badge">فرعي</span>
    </button>
    <button class="guide-role-tab" id="gtab-cc" onclick="switchGuideTab('cc')">
      📞 كول سنتر CC <span class="guide-tab-badge">متابعة</span>
    </button>
    <button class="guide-role-tab" id="gtab-vw" onclick="switchGuideTab('vw')">
      👁 مشاهد <span class="guide-tab-badge">قراءة</span>
    </button>
  </div>

  {{-- ════ Finance Admin panel ════ --}}
  <div class="guide-role-panel" id="gpanel-fa">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(26,173,186,.12),rgba(26,173,186,.04));border:1px solid rgba(26,173,186,.25)">
      <div class="role-banner-icon">💼</div>
      <div>
        <div class="role-banner-title" style="color:var(--pri2)">المدير المالي — Finance Admin</div>
        <div class="role-banner-sub">صلاحية كاملة لجميع وظائف النظام — يدير الفروع والمديرين والموظفين ويراقب كل العمليات</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">⚙️</div><div class="flow-node-lbl" style="color:var(--pri2)">إعداد النظام</div><div class="flow-node-sub">فروع + موظفون</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="flow-node-ico">📥</div><div class="flow-node-lbl" style="color:var(--gr)">استيراد Excel</div><div class="flow-node-sub">بيانات شهرية</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="flow-node-ico">🗂</div><div class="flow-node-lbl" style="color:var(--or)">مراجعة الكروت</div><div class="flow-node-sub">إضافة / تعديل</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="flow-node-ico">📞</div><div class="flow-node-lbl" style="color:var(--pu)">قبول CC</div><div class="flow-node-sub">جميع الفروع</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(30,204,128,.1);border:1px solid rgba(30,204,128,.3)"><div class="flow-node-ico">✅</div><div class="flow-node-lbl" style="color:var(--gr)">اعتماد الموظفين</div><div class="flow-node-sub">كل الفروع</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📈</div><div class="flow-node-lbl" style="color:var(--pri2)">التقارير</div><div class="flow-node-sub">PDF / Excel</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">⚙️</div>
        <div><div class="step-title" style="color:var(--pri2)"><span class="step-num">1</span> إعداد النظام</div>
        <div class="step-desc"><strong>الإعدادات → الفروع</strong>: أضف فروع الشركة وكودها<br><strong>الإعدادات → الموظفون</strong>: أضف بروكرات ومسوّقين بعمولاتهم<br><strong>الإعدادات → المديرون</strong>: أضف مديري الفروع وحدد صلاحياتهم</div>
        <span class="step-note">🔑 يُنفَّذ مرة واحدة عند بدء تشغيل النظام</span></div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">📥</div>
        <div><div class="step-title" style="color:var(--gr)"><span class="step-num">2</span> استيراد البيانات الشهرية</div>
        <div class="step-desc">القائمة الجانبية → <strong>📥 استيراد بيانات</strong> → ارفع ملف Excel<br>راجع المعاينة → تأكد من صحة الأعمدة → <strong>رفع واستيراد</strong></div>
        <span class="step-note">💡 يدعم آلاف السجلات في ملف واحد — بدون حد أقصى</span></div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">🗂</div>
        <div><div class="step-title" style="color:var(--or)"><span class="step-num">3</span> مراجعة كروت العمولة</div>
        <div class="step-desc"><strong>كروت العمولات</strong>: كل الكروت مع الفلاتر (شهر / فرع / بروكر / حالة)<br><strong>➕ كرت جديد</strong>: أدخل رقم الحساب، الشهر، البروكر، المسوّق، الإيداع<br><strong>✏️ تعديل</strong>: ابحث برقم الحساب → عدّل → اختر سبب التعديل</div>
        <span class="step-note">📋 كل تعديل يُسجَّل تلقائياً باسم المعدّل والتاريخ والسبب</span></div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div>
        <div><div class="step-title" style="color:var(--pu)"><span class="step-num">4</span> كروت مركز الاتصال CC</div>
        <div class="step-desc"><strong>📞 مركز الاتصال</strong> → الكروت المعلّقة من جميع الفروع<br><strong>قبول</strong>: الكرت ينتقل فوراً للقائمة الرئيسية — <strong>رفض</strong>: أدخل سبب الرفض</div>
        <span class="step-note">👁 ترى كروت جميع الفروع — مدير الفرع يرى فرعه فقط</span></div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(30,204,128,.2),rgba(30,204,128,.06));border:1px solid rgba(30,204,128,.3)">✅</div>
        <div><div class="step-title" style="color:var(--gr)"><span class="step-num">5</span> اعتماد الموظفين الجدد</div>
        <div class="step-desc"><strong>الإعدادات → الاعتمادات</strong>: موظفون بانتظار موافقتك أضافهم مدراء الفروع<br>اعتمد أو ارفض — بعد الاعتماد يظهر في قوائم الاختيار عند إنشاء الكروت</div></div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div>
        <div><div class="step-title" style="color:var(--pri2)"><span class="step-num">6</span> التقارير والإحصائيات</div>
        <div class="step-desc"><strong>📊 لوحة المتابعة</strong>: ملخص الأرقام + ترتيب البروكرات + آخر التعديلات<br><strong>📈 التقارير</strong>: فلتر متقدم → تصدير PDF أو Excel<br><strong>📊 تقرير ديناميكي</strong>: تحليل مخصص حسب أي معيار</div></div>
      </div>
      <div class="guide-step">
        <div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">👤</div>
        <div><div class="step-title" style="color:var(--pu)"><span class="step-num">7</span> إدارة المديرين والدعوات</div>
        <div class="step-desc"><strong>الإعدادات → المديرون</strong>: أضف مديراً مع تحديد صلاحياته<br><strong>📧 دعوة</strong>: أضف الإيميل → يصله بريد إلكتروني → يسجّل بنفسه<br>تعديل الصلاحيات أو تعطيل المدير في أي وقت</div></div>
      </div>
    </div>
    {{-- Permissions table --}}
    <div style="margin-top:18px;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:16px">
      <div style="font-size:13px;font-weight:800;color:var(--pri2);margin-bottom:12px">🔐 جدول مقارنة الصلاحيات</div>
      <div style="overflow-x:auto">
      <table class="perm-table">
        <thead><tr><th>الوظيفة / الصفحة</th><th>المدير المالي</th><th>مدير الفرع</th><th>مشاهد</th></tr></thead>
        <tbody>
          <tr><td>لوحة المتابعة</td><td class="p-yes">✅ كاملة</td><td class="p-yes">✅ فرعه فقط</td><td class="p-yes">✅ قراءة</td></tr>
          <tr><td>كروت العمولة — عرض</td><td class="p-yes">✅ كل الفروع</td><td class="p-cond">⚙️ حسب صلاحية</td><td class="p-cond">⚙️ حسب صلاحية</td></tr>
          <tr><td>إضافة كرت جديد</td><td class="p-yes">✅ نعم</td><td class="p-cond">⚙️ حسب صلاحية</td><td class="p-no">❌ لا</td></tr>
          <tr><td>تعديل كرت</td><td class="p-yes">✅ نعم</td><td class="p-cond">⚙️ حسب صلاحية</td><td class="p-no">❌ لا</td></tr>
          <tr><td>مركز الاتصال CC</td><td class="p-yes">✅ كل الفروع</td><td class="p-yes">✅ فرعه فقط</td><td class="p-no">❌ لا</td></tr>
          <tr><td>استيراد Excel</td><td class="p-yes">✅ نعم</td><td class="p-cond">⚙️ حسب صلاحية</td><td class="p-no">❌ لا</td></tr>
          <tr><td>تصدير PDF / Excel</td><td class="p-yes">✅ نعم</td><td class="p-cond">⚙️ حسب صلاحية</td><td class="p-no">❌ لا</td></tr>
          <tr><td>التقارير</td><td class="p-yes">✅ كل الفروع</td><td class="p-cond">⚙️ حسب صلاحية</td><td class="p-cond">⚙️ حسب صلاحية</td></tr>
          <tr><td>إدارة الموظفين</td><td class="p-yes">✅ + اعتماد</td><td class="p-cond">⚙️ إضافة فقط</td><td class="p-no">❌ لا</td></tr>
          <tr><td>إدارة المديرين</td><td class="p-yes">✅ نعم</td><td class="p-no">❌ لا</td><td class="p-no">❌ لا</td></tr>
          <tr><td>الإعدادات</td><td class="p-yes">✅ كاملة</td><td class="p-no">❌ لا</td><td class="p-no">❌ لا</td></tr>
        </tbody>
      </table>
      </div>
    </div>
  </div>{{-- gpanel-fa --}}

  {{-- ════ Branch Manager panel ════ --}}
  <div class="guide-role-panel" id="gpanel-bm">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(34,201,122,.1),rgba(34,201,122,.04));border:1px solid rgba(34,201,122,.25)">
      <div class="role-banner-icon">🏢</div>
      <div>
        <div class="role-banner-title" style="color:var(--gr)">مدير الفرع — Branch Manager</div>
        <div class="role-banner-sub">تعمل على فرعك المحدد فقط — الصلاحيات تحددها الإدارة المالية وقد تختلف من مدير لآخر</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="flow-node-ico">🔐</div><div class="flow-node-lbl" style="color:var(--gr)">تسجيل الدخول</div><div class="flow-node-sub">بيانات فرعك</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">🗂</div><div class="flow-node-lbl" style="color:var(--pri2)">كروت الفرع</div><div class="flow-node-sub">عرض / إضافة</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="flow-node-ico">📞</div><div class="flow-node-lbl" style="color:var(--pu)">مركز الاتصال</div><div class="flow-node-sub">قبول / رفض</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="flow-node-ico">👥</div><div class="flow-node-lbl" style="color:var(--or)">إضافة موظفين</div><div class="flow-node-sub">بانتظار اعتماد</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📊</div><div class="flow-node-lbl" style="color:var(--pri2)">تقارير الفرع</div><div class="flow-node-sub">إذا مسموح</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">🔐</div><div><div class="step-title" style="color:var(--gr)"><span class="step-num">1</span> تسجيل الدخول</div><div class="step-desc">ادخل ببيانات حسابك التي أرسلتها الإدارة المالية على بريدك الإلكتروني<br>إذا أول مرة → سيُطلب منك تغيير كلمة المرور</div><span class="step-note">📧 إذا لم تستلم البيانات تواصل مع المدير المالي</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div><div><div class="step-title" style="color:var(--pri2)"><span class="step-num">2</span> كروت العمولة (فرعك فقط)</div><div class="step-desc"><strong>كروت العمولات</strong>: ترى كروت فرعك فقط — مصنّفة بالشهر / الحالة / النوع<br>إضافة كرت جديد أو تعديل موجود (إذا كانت لديك الصلاحية)</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div><div><div class="step-title" style="color:var(--pu)"><span class="step-num">3</span> معالجة كروت CC</div><div class="step-desc"><strong>📞 مركز الاتصال</strong>: كروت واردة خاصة بفرعك<br>اضغط <strong>قبول</strong> → الكرت ينتقل لقائمة كروت العمولة<br>اضغط <strong>رفض</strong> → أدخل سبب الرفض</div><span class="step-note">⏰ راجع قسم CC يومياً لمعالجة الكروت في الوقت المناسب</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">👥</div><div><div class="step-title" style="color:var(--or)"><span class="step-num">4</span> إضافة موظفين</div><div class="step-desc"><strong>الإعدادات → الموظفون</strong>: أضف بروكراً أو مسوّقاً جديداً<br>يُرسل طلب للمدير المالي للاعتماد — بعده يظهر في قوائم الكروت</div><span class="step-note">⏳ الموظف لا يظهر في الكروت إلا بعد موافقة المدير المالي</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📊</div><div><div class="step-title" style="color:var(--pri2)"><span class="step-num">5</span> التقارير (إذا مفعّلة)</div><div class="step-desc">إذا منحك المدير المالي صلاحية التقارير → عرض وتصدير تقارير فرعك<br>فلتر بالشهر والبروكر وتصدير PDF / Excel</div></div></div>
    </div>
  </div>{{-- gpanel-bm --}}

  {{-- ════ Call Center panel ════ --}}
  <div class="guide-role-panel" id="gpanel-cc">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(138,120,240,.1),rgba(138,120,240,.04));border:1px solid rgba(138,120,240,.25)">
      <div class="role-banner-icon">📞</div>
      <div>
        <div class="role-banner-title" style="color:var(--pu)">مركز الاتصال — Call Center CC</div>
        <div class="role-banner-sub">إدخال كروت العملاء الجدد عبر CC وإرسالها للمراجعة من مدير الفرع</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="flow-node-ico">📞</div><div class="flow-node-lbl" style="color:var(--pu)">استقبال الكرت</div><div class="flow-node-sub">من العميل</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">➕</div><div class="flow-node-lbl" style="color:var(--pri2)">إدخال البيانات</div><div class="flow-node-sub">رقم حساب + بيانات</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="flow-node-ico">⏳</div><div class="flow-node-lbl" style="color:var(--or)">بانتظار المراجعة</div><div class="flow-node-sub">مدير الفرع</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="flow-node-ico">✅</div><div class="flow-node-lbl" style="color:var(--gr)">قبول المدير</div><div class="flow-node-sub">يدخل القائمة الرئيسية</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📊</div><div class="flow-node-lbl" style="color:var(--pri2)">الكرت نشط</div><div class="flow-node-sub">في النظام</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div><div><div class="step-title" style="color:var(--pu)"><span class="step-num">1</span> فتح مركز الاتصال</div><div class="step-desc">من القائمة الجانبية → <strong>📞 مركز الاتصال</strong><br>هنا ترى الكروت الواردة المنتظرة للمراجعة</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">➕</div><div><div class="step-title" style="color:var(--pri2)"><span class="step-num">2</span> إدخال كرت جديد</div><div class="step-desc"><strong>➕ كرت جديد</strong> → أدخل: رقم الحساب • الشهر • الفرع • نوع الحساب (NEW/SUB) • البروكر والمسوّق • الإيداع والعمولات</div><span class="step-note">📌 الكرت يُرسل تلقائياً لمراجعة مدير الفرع</span></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">⏳</div><div><div class="step-title" style="color:var(--or)"><span class="step-num">3</span> متابعة حالة الكروت</div><div class="step-desc">في قائمة CC ترى: <strong>معلّق</strong> أو <strong>مقبول</strong> أو <strong>مرفوض مع السبب</strong><br>إذا رُفض → اقرأ السبب وأعد الإدخال بعد التصحيح</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">✅</div><div><div class="step-title" style="color:var(--gr)"><span class="step-num">4</span> بعد القبول</div><div class="step-desc">عند قبول مدير الفرع → الكرت يظهر في <strong>كروت العمولات الرئيسية</strong> تلقائياً<br>يمكن البحث عنه برقم الحساب للتحقق</div></div></div>
    </div>
  </div>{{-- gpanel-cc --}}

  {{-- ════ Viewer panel ════ --}}
  <div class="guide-role-panel" id="gpanel-vw">
    <div class="role-banner" style="background:linear-gradient(135deg,rgba(90,128,160,.1),rgba(90,128,160,.04));border:1px solid rgba(90,128,160,.25)">
      <div class="role-banner-icon">👁</div>
      <div>
        <div class="role-banner-title" style="color:var(--mu)">مشاهد — Viewer</div>
        <div class="role-banner-sub">صلاحية قراءة فقط — مشاهدة البيانات والتقارير دون إمكانية التعديل أو الإضافة</div>
      </div>
    </div>
    <div class="flow-diagram">
      <div class="flow-node" style="background:rgba(90,128,160,.1);border:1px solid rgba(90,128,160,.3)"><div class="flow-node-ico">🔐</div><div class="flow-node-lbl" style="color:var(--mu)">تسجيل الدخول</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📊</div><div class="flow-node-lbl" style="color:var(--pri2)">لوحة المتابعة</div><div class="flow-node-sub">قراءة فقط</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">🗂</div><div class="flow-node-lbl" style="color:var(--pri2)">عرض الكروت</div><div class="flow-node-sub">إذا مسموح</div></div>
      <div class="flow-arrow">→</div>
      <div class="flow-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="flow-node-ico">📈</div><div class="flow-node-lbl" style="color:var(--pri2)">التقارير</div><div class="flow-node-sub">إذا مسموح</div></div>
    </div>
    <div class="guide-steps">
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(90,128,160,.2),rgba(90,128,160,.06));border:1px solid rgba(90,128,160,.3)">🔐</div><div><div class="step-title" style="color:var(--mu)"><span class="step-num">1</span> تسجيل الدخول</div><div class="step-desc">ادخل ببيانات حسابك — إذا أول مرة غيّر كلمة المرور المؤقتة</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📊</div><div><div class="step-title" style="color:var(--pri2)"><span class="step-num">2</span> لوحة المتابعة</div><div class="step-desc">ملخص الأرقام: إجمالي الكروت، الإيداعات، ترتيب البروكرات — محدّث لحظياً</div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div><div><div class="step-title" style="color:var(--pri2)"><span class="step-num">3</span> عرض الكروت (إذا مسموح)</div><div class="step-desc">إذا منحك المدير صلاحية الكروت → عرض وفلترة — <strong>لا يمكن الإضافة أو التعديل</strong></div></div></div>
      <div class="guide-step"><div class="step-icon-box" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div><div><div class="step-title" style="color:var(--pri2)"><span class="step-num">4</span> التقارير (إذا مسموح)</div><div class="step-desc">إذا منحك المدير صلاحية التقارير → عرض وتصدير PDF / Excel</div></div></div>
    </div>
  </div>{{-- gpanel-vw --}}

  {{-- ═══════════════════════════════════════════════════════
       SECTION 2 — USER MANUAL (دليل المستخدم)
       ═══════════════════════════════════════════════════════ --}}
  <div class="guide-section-lbl"><span>📚 دليل المستخدم — User Manual</span></div>

  <div class="manual-grid">

    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div>
      <div class="manual-body">
        <div class="manual-title" style="color:var(--pri2)">1. إضافة كرت عمولة جديد</div>
        <div class="manual-desc">اضغط <strong>➕ كرت جديد</strong> من شريط التنقل العلوي أو القائمة الجانبية<br>أدخل: رقم الحساب — الشهر — الفرع — البروكر — المسوّق — الإيداع الأولي والشهري — نوع الحساب (NEW جديد / SUB فرعي) — ثم اضغط <strong>حفظ</strong></div>
        <span class="manual-tip">💡 رقم الحساب يجب أن يكون فريداً لنفس الشهر</span>
      </div>
    </div>

    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(245,166,35,.18),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">✏️</div>
      <div class="manual-body">
        <div class="manual-title" style="color:var(--or)">2. تعديل كرت موجود</div>
        <div class="manual-desc">اضغط <strong>✏️ تعديل</strong> من شريط التنقل العلوي → ابحث برقم الحساب → اختر الكرت المطلوب → عدّل البيانات → اختر <strong>سبب التعديل</strong> من القائمة → <strong>حفظ</strong><br>التعديل يُسجَّل تلقائياً مع اسم المعدّل والتاريخ والسبب</div>
        <span class="manual-tip">📋 جميع التعديلات محفوظة في سجل الحركات بصفحة "الحسابات المعدّلة"</span>
      </div>
    </div>

    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(34,201,122,.18),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">📥</div>
      <div class="manual-body">
        <div class="manual-title" style="color:var(--gr)">3. استيراد بيانات Excel</div>
        <div class="manual-desc">القائمة الجانبية → <strong>📥 استيراد بيانات</strong> → ارفع ملف Excel بالأعمدة المطلوبة → راجع المعاينة → <strong>رفع واستيراد</strong><br>النظام يتحقق من كل سطر ويُظهر الأخطاء إن وُجدت قبل الحفظ النهائي</div>
        <span class="manual-tip">💡 لا حد للصفوف — يمكن رفع آلاف السجلات في ملف واحد</span>
      </div>
    </div>

    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(138,120,240,.18),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div>
      <div class="manual-body">
        <div class="manual-title" style="color:var(--pu)">4. إدارة كروت مركز الاتصال CC</div>
        <div class="manual-desc"><strong>📞 مركز الاتصال</strong> من القائمة الجانبية → قائمة الكروت المعلّقة<br><strong>قبول</strong>: الكرت ينتقل فوراً لقائمة كروت العمولة الرئيسية<br><strong>رفض</strong>: أدخل سبب الرفض — يُحفظ مع الكرت في سجل الحركات</div>
        <span class="manual-tip">مدير الفرع يرى فرعه فقط — المدير المالي يرى كل الفروع</span>
      </div>
    </div>

    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div>
      <div class="manual-body">
        <div class="manual-title" style="color:var(--pri2)">5. التقارير والإحصائيات</div>
        <div class="manual-desc"><strong>📈 التقارير</strong>: فلتر بالشهر / الفرع / البروكر / الحالة → تصدير PDF أو Excel<br><strong>📊 لوحة المتابعة</strong>: ملخص الأرقام + ترتيب البروكرات + آخر التعديلات<br><strong>📊 تقرير ديناميكي</strong>: تحليل مخصص حسب أي معيار تختاره</div>
        <span class="manual-tip">الأرقام في الداشبورد محسوبة من كامل قاعدة البيانات بدون حد أقصى</span>
      </div>
    </div>

    <div class="manual-card">
      <div class="manual-ico" style="background:linear-gradient(135deg,rgba(30,204,128,.18),rgba(30,204,128,.06));border:1px solid rgba(30,204,128,.3)">👥</div>
      <div class="manual-body">
        <div class="manual-title" style="color:var(--gr)">6. إدارة الموظفين والمديرين</div>
        <div class="manual-desc"><strong>الموظفون</strong>: إضافة بروكر / مسوّق بعمولاته → طلب اعتماد من المدير المالي → بعد الاعتماد يظهر في قوائم الكروت<br><strong>المديرون</strong> (للمدير المالي فقط): إضافة مدير فرع يدوياً أو دعوته بالإيميل — تحديد صلاحياته وتعديلها في أي وقت</div>
      </div>
    </div>

  </div>{{-- manual-grid --}}

  {{-- Roles summary --}}
  <div class="guide-section-lbl" style="margin-top:28px"><span>🔐 ملخص الأدوار والصلاحيات</span></div>
  <div class="roles-grid">
    <div class="role-card" style="border-color:rgba(26,173,186,.3)">
      <div class="role-card-title" style="color:var(--pri2)">💼 المدير المالي</div>
      <div class="role-card-list">
        ✅ كل الصفحات والوظائف<br>
        ✅ استيراد Excel وتصدير البيانات<br>
        ✅ إدارة المديرين والفروع<br>
        ✅ اعتماد الموظفين الجدد<br>
        ✅ إعدادات النظام الكاملة
      </div>
    </div>
    <div class="role-card" style="border-color:rgba(34,201,122,.3)">
      <div class="role-card-title" style="color:var(--gr)">🏢 مدير الفرع</div>
      <div class="role-card-list">
        ✅ فرعه المحدد فقط<br>
        ✅ كروت العمولة (حسب صلاحية)<br>
        ✅ مركز الاتصال CC<br>
        ✅ إضافة موظفين (بانتظار اعتماد)<br>
        ⚙️ باقي الصلاحيات يحددها المدير المالي
      </div>
    </div>
    <div class="role-card" style="border-color:rgba(138,120,240,.3)">
      <div class="role-card-title" style="color:var(--pu)">📞 كول سنتر / مشاهد</div>
      <div class="role-card-list">
        ✅ إدخال كروت CC<br>
        ✅ متابعة حالة الكروت<br>
        ✅ قراءة البيانات والتقارير<br>
        ❌ لا تعديل على الكروت<br>
        ❌ لا وصول للإعدادات
      </div>
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
/* Auto-select current user's role — runs after layout defines CURRENT_USER */
(function(){
  const role = (typeof CURRENT_USER !== 'undefined' && CURRENT_USER?.role) ? CURRENT_USER.role : 'finance_admin';
  if      (role === 'finance_admin')  switchGuideTab('fa');
  else if (role === 'branch_manager') switchGuideTab('bm');
  else                                switchGuideTab('vw');
})();
</script>
@endpush
