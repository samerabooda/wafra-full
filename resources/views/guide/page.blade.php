@extends('layouts.app')
@section('title','User Guide')
@section('page-title','User Guide')

@section('content')
@verbatim
<style>
.gd{max-width:960px;margin:0 auto}
.gd-hdr{text-align:center;padding:28px 0 20px;border-bottom:1px solid var(--brd1);margin-bottom:22px}
.gd-hdr h1{font-size:24px;font-weight:900;margin-bottom:6px;background:linear-gradient(135deg,#22C4D4,#1AADBA);-webkit-background-clip:text;background-clip:text;color:transparent}
.gd-hdr p{font-size:13px;color:var(--mu)}
.gd-sec{display:flex;align-items:center;gap:10px;margin:24px 0 16px}
.gd-sec::before,.gd-sec::after{content:'';flex:1;height:1px;background:var(--brd1)}
.gd-sec span{font-size:12px;font-weight:800;color:var(--pri2);background:var(--bg2);padding:4px 14px;border-radius:20px;border:1px solid rgba(26,173,186,.25);white-space:nowrap}
.gd-tabs{display:flex;gap:6px;flex-wrap:wrap;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:6px;margin-bottom:20px}
.gd-tab{flex:1;min-width:110px;padding:9px 12px;border:none;background:none;border-radius:8px;cursor:pointer;font-family:'Tajawal',sans-serif;font-size:13px;font-weight:700;color:var(--mu);transition:all .2s;display:flex;align-items:center;justify-content:center;gap:6px}
.gd-tab.active{background:rgba(26,173,186,.16);border:1px solid rgba(26,173,186,.35);color:var(--pri2)}
.gd-tab-badge{font-size:9px;padding:1px 6px;border-radius:10px;background:rgba(26,173,186,.15);color:var(--pri2)}
.gd-panel{display:none}
.gd-panel.on{display:block;animation:gdFade .3s ease}
@keyframes gdFade{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.gd-banner{border-radius:14px;padding:16px 20px;margin-bottom:18px;display:flex;align-items:center;gap:16px}
.gd-banner-ico{font-size:36px;flex-shrink:0}
.gd-banner-title{font-size:16px;font-weight:900;margin-bottom:4px}
.gd-banner-sub{font-size:12px;opacity:.75;line-height:1.65}
.gd-flow{display:flex;align-items:center;justify-content:center;flex-wrap:wrap;gap:4px;padding:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;margin-bottom:18px;overflow-x:auto}
.gd-node{display:flex;flex-direction:column;align-items:center;gap:4px;border-radius:12px;padding:12px 14px;min-width:82px;text-align:center}
.gd-node-ico{font-size:24px}
.gd-node-lbl{font-size:10px;font-weight:800;white-space:nowrap}
.gd-node-sub{font-size:9px;color:var(--mu);white-space:nowrap}
.gd-arrow{font-size:20px;color:var(--mu);padding:0 4px;flex-shrink:0}
.gd-steps{display:flex;flex-direction:column;gap:10px}
.gd-step{display:flex;gap:14px;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:14px 16px;align-items:flex-start;transition:border-color .2s}
.gd-step:hover{border-color:rgba(26,173,186,.35)}
.gd-step-ico{width:42px;height:42px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px;flex-shrink:0}
.gd-step-num{display:inline-flex;align-items:center;justify-content:center;width:20px;height:20px;border-radius:50%;background:var(--pri3);color:white;font-size:10px;font-weight:800;margin-left:5px}
.gd-step-title{font-size:13px;font-weight:800;margin-bottom:5px}
.gd-step-desc{font-size:12px;color:var(--tx);line-height:1.8}
.gd-step-note{display:inline-block;font-size:10px;color:var(--mu);margin-top:5px;padding:4px 9px;background:var(--bg3);border-radius:6px;border-right:2px solid var(--pri3)}
.perm-tbl{width:100%;border-collapse:collapse;font-size:12px}
.perm-tbl th{background:var(--bg3);padding:9px 12px;font-size:10px;font-weight:800;color:var(--mu);text-align:right;border-bottom:2px solid var(--brd1)}
.perm-tbl td{padding:9px 12px;border-bottom:1px solid var(--brd1);vertical-align:middle}
.perm-tbl tr:last-child td{border-bottom:none}
.perm-tbl tr:hover td{background:rgba(26,173,186,.04)}
.p-yes{color:var(--gr);font-weight:800}
.p-no{color:var(--re);opacity:.65}
.p-cond{color:var(--or);font-weight:700}
.gd-manual{display:flex;flex-direction:column;gap:12px}
.gd-mcard{display:flex;gap:14px;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:16px;align-items:flex-start}
.gd-mcard:hover{border-color:rgba(26,173,186,.3)}
.gd-mico{width:44px;height:44px;border-radius:11px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:22px}
.gd-mbody h3{font-size:13px;font-weight:800;margin-bottom:6px}
.gd-mbody p{font-size:12px;color:var(--tx);line-height:1.8}
.gd-mtip{font-size:10px;color:var(--mu);margin-top:5px;background:var(--bg3);padding:4px 8px;border-radius:6px;display:inline-block}
.gd-roles{display:grid;grid-template-columns:repeat(3,1fr);gap:12px;margin-top:14px}
.gd-role{background:var(--bg3);border:1px solid var(--brd1);border-radius:11px;padding:14px 16px}
.gd-role h3{font-size:13px;font-weight:800;margin-bottom:8px}
.gd-role p{font-size:11px;color:var(--mu);line-height:1.9}
.gd-roadmap{background:var(--bg2);border:1px solid var(--brd1);border-radius:16px;overflow:hidden;margin-bottom:20px}
.gd-rm-hdr{background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.04));padding:16px 22px;border-bottom:1px solid var(--brd1);display:flex;align-items:center;gap:12px}
.gd-rm-hdr h2{font-size:15px;font-weight:900;color:var(--pri2)}
.gd-rm-hdr p{font-size:11px;color:var(--mu);margin-top:2px}
.gd-phases{display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:18px;margin-bottom:0}
.gd-phase{text-align:center;padding:14px;border-radius:12px;position:relative}
.gd-phase-num{position:absolute;top:-10px;left:50%;transform:translateX(-50%);width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:900}
.gd-phase-ico{font-size:26px;margin:6px 0 8px}
.gd-phase-title{font-size:12px;font-weight:800}
.gd-phase-sub{font-size:10px;color:var(--mu);margin-top:3px}
.gd-qs{display:grid;grid-template-columns:1fr 1fr;gap:12px;padding:0 18px 18px}
.gd-qs-card{border-radius:12px;padding:14px}
.gd-qs-card h4{font-size:12px;font-weight:800;margin-bottom:8px}
.gd-qs-card div{font-size:11px;line-height:2}
@media(max-width:768px){
  .gd-roles{grid-template-columns:1fr}
  .gd-phases{grid-template-columns:1fr 1fr}
  .gd-qs{grid-template-columns:1fr}
}
.lang-ar{display:block}.lang-en{display:none}
</style>
@endverbatim

<div class="gd">

  {{-- Header --}}
  <div class="gd-hdr">
    <h1 id="gd-title">📖 دليل تشغيل النظام</h1>
    <p id="gd-sub">اختر دورك لمشاهدة Workflow — ثم اطّلع على دليل المستخدم التفصيلي</p>
  </div>

  {{-- Role Tabs --}}
  <div class="gd-sec"><span id="gd-sec-wf">🔄 مخطط سير العمل</span></div>
  <div class="gd-tabs">
    <button class="gd-tab" id="gtab-fa" onclick="gTab('fa')">
      💼 <span id="gd-tab-fa">المدير المالي</span>
      <span class="gd-tab-badge" id="gd-badge-fa">كامل</span>
    </button>
    <button class="gd-tab" id="gtab-bm" onclick="gTab('bm')">
      🏢 <span id="gd-tab-bm">مدير الفرع</span>
      <span class="gd-tab-badge" id="gd-badge-bm">فرعي</span>
    </button>
    <button class="gd-tab" id="gtab-cc" onclick="gTab('cc')">
      📞 <span id="gd-tab-cc">كول سنتر</span>
      <span class="gd-tab-badge" id="gd-badge-cc">CC</span>
    </button>
    <button class="gd-tab" id="gtab-vw" onclick="gTab('vw')">
      👁 <span id="gd-tab-vw">مشاهد</span>
      <span class="gd-tab-badge" id="gd-badge-vw">قراءة</span>
    </button>
  </div>

  {{-- ══ Finance Admin Panel ══ --}}
  <div class="gd-panel" id="gpanel-fa">
    <div class="gd-banner" style="background:linear-gradient(135deg,rgba(26,173,186,.12),rgba(26,173,186,.04));border:1px solid rgba(26,173,186,.25)">
      <div class="gd-banner-ico">💼</div>
      <div>
        <div class="gd-banner-title" style="color:var(--pri2)">💼 Finance Admin — المدير المالي</div>
        <div class="gd-banner-sub lang-ar">صلاحية كاملة — يدير الفروع والمديرين والموظفين ويراقب كل العمليات</div>
        <div class="gd-banner-sub lang-en">Full access — manages branches, managers, employees, and monitors all operations</div>
      </div>
    </div>
    <div class="gd-flow">
      <div class="gd-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="gd-node-ico">⚙️</div><div class="gd-node-lbl lang-ar" style="color:var(--pri2)">إعداد النظام</div><div class="gd-node-lbl lang-en" style="color:var(--pri2)">System Setup</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="gd-node-ico">📥</div><div class="gd-node-lbl lang-ar" style="color:var(--gr)">استيراد Excel</div><div class="gd-node-lbl lang-en" style="color:var(--gr)">Import Excel</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="gd-node-ico">🗂</div><div class="gd-node-lbl lang-ar" style="color:var(--or)">مراجعة الكروت</div><div class="gd-node-lbl lang-en" style="color:var(--or)">Review Cards</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="gd-node-ico">📞</div><div class="gd-node-lbl lang-ar" style="color:var(--pu)">قبول CC</div><div class="gd-node-lbl lang-en" style="color:var(--pu)">Accept CC</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="gd-node-ico">📈</div><div class="gd-node-lbl lang-ar" style="color:var(--pri2)">التقارير</div><div class="gd-node-lbl lang-en" style="color:var(--pri2)">Reports</div></div>
    </div>
    <div class="gd-steps">
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">⚙️</div><div><div class="gd-step-title lang-ar" style="color:var(--pri2)"><span class="gd-step-num">1</span> إعداد النظام</div><div class="gd-step-title lang-en" style="color:var(--pri2)"><span class="gd-step-num">1</span> System Setup</div><div class="gd-step-desc lang-ar"><strong>الإعدادات → الفروع:</strong> أضف فروع الشركة — <strong>الموظفون:</strong> أضف البروكرات والمسوّقين — <strong>المديرون:</strong> أضف مديري الفروع وحدد صلاحياتهم</div><div class="gd-step-desc lang-en"><strong>Settings → Branches:</strong> Add branches — <strong>Employees:</strong> Add brokers & marketers — <strong>Managers:</strong> Add branch managers and set permissions</div><span class="gd-step-note lang-ar">🔑 يُنفَّذ مرة واحدة عند بدء التشغيل</span><span class="gd-step-note lang-en">🔑 Done once at initial setup</span></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">📥</div><div><div class="gd-step-title lang-ar" style="color:var(--gr)"><span class="gd-step-num">2</span> استيراد البيانات الشهرية</div><div class="gd-step-title lang-en" style="color:var(--gr)"><span class="gd-step-num">2</span> Import Monthly Data</div><div class="gd-step-desc lang-ar">القائمة الجانبية ← <strong>📥 استيراد بيانات</strong> ← ارفع ملف Excel ← راجع المعاينة ← <strong>استيراد</strong></div><div class="gd-step-desc lang-en">Sidebar ← <strong>📥 Import Data</strong> ← upload Excel ← review preview ← <strong>Import</strong></div><span class="gd-step-note lang-ar">💡 يدعم آلاف السجلات — بدون حد أقصى</span><span class="gd-step-note lang-en">💡 Supports thousands of records — no limit</span></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📞</div><div><div class="gd-step-title lang-ar" style="color:var(--pu)"><span class="gd-step-num">3</span> كروت مركز الاتصال CC</div><div class="gd-step-title lang-en" style="color:var(--pu)"><span class="gd-step-num">3</span> Call Center CC Cards</div><div class="gd-step-desc lang-ar"><strong>📞 مركز الاتصال</strong> ← الكروت المعلّقة من جميع الفروع ← <strong>قبول:</strong> ينتقل للقائمة الرئيسية ← <strong>رفض:</strong> أدخل السبب</div><div class="gd-step-desc lang-en"><strong>📞 Call Center</strong> ← pending cards from all branches ← <strong>Accept:</strong> moves to main list ← <strong>Reject:</strong> enter reason</div></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📈</div><div><div class="gd-step-title lang-ar" style="color:var(--pri2)"><span class="gd-step-num">4</span> التقارير والإحصائيات</div><div class="gd-step-title lang-en" style="color:var(--pri2)"><span class="gd-step-num">4</span> Reports & Statistics</div><div class="gd-step-desc lang-ar"><strong>📊 لوحة التقارير:</strong> KPIs تتحمّل تلقائياً — <strong>📋 جدول البيانات:</strong> فلتر متقدم + تصدير — <strong>📊 تقرير ديناميكي:</strong> تحليل مخصص</div><div class="gd-step-desc lang-en"><strong>📊 Dashboard:</strong> KPIs auto-load — <strong>📋 Data Table:</strong> advanced filter + export — <strong>📊 Dynamic:</strong> custom analysis</div></div></div>
    </div>

    {{-- Permissions table --}}
    <div style="margin-top:20px;background:var(--bg2);border:1px solid var(--brd1);border-radius:12px;padding:16px">
      <div style="font-size:13px;font-weight:800;color:var(--pri2);margin-bottom:12px" id="gd-perm-title">🔐 جدول مقارنة الصلاحيات</div>
      <div style="overflow-x:auto">
        <table class="perm-tbl">
          <thead><tr>
            <th id="gd-pt-fn">الوظيفة / الصفحة</th>
            <th id="gd-pt-fa">المدير المالي</th>
            <th id="gd-pt-bm">مدير الفرع</th>
            <th id="gd-pt-vw">مشاهد</th>
          </tr></thead>
          <tbody id="gd-perm-tbody"></tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ══ Branch Manager Panel ══ --}}
  <div class="gd-panel" id="gpanel-bm">
    <div class="gd-banner" style="background:linear-gradient(135deg,rgba(34,201,122,.1),rgba(34,201,122,.04));border:1px solid rgba(34,201,122,.25)">
      <div class="gd-banner-ico">🏢</div>
      <div>
        <div class="gd-banner-title" style="color:var(--gr)">🏢 Branch Manager — مدير الفرع</div>
        <div class="gd-banner-sub lang-ar">تعمل على فرعك المحدد — الصلاحيات تحددها الإدارة المالية</div>
        <div class="gd-banner-sub lang-en">You work on your assigned branch — permissions set by Finance Admin</div>
      </div>
    </div>
    <div class="gd-flow">
      <div class="gd-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="gd-node-ico">🔐</div><div class="gd-node-lbl lang-ar" style="color:var(--gr)">تسجيل الدخول</div><div class="gd-node-lbl lang-en" style="color:var(--gr)">Login</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="gd-node-ico">🗂</div><div class="gd-node-lbl lang-ar" style="color:var(--pri2)">كروت الفرع</div><div class="gd-node-lbl lang-en" style="color:var(--pri2)">Branch Cards</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)"><div class="gd-node-ico">📩</div><div class="gd-node-lbl lang-ar" style="color:var(--pu)">قبول CC</div><div class="gd-node-lbl lang-en" style="color:var(--pu)">Accept CC</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="gd-node-ico">👥</div><div class="gd-node-lbl lang-ar" style="color:var(--or)">إضافة موظفين</div><div class="gd-node-lbl lang-en" style="color:var(--or)">Add Staff</div></div>
    </div>
    <div class="gd-steps">
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">🔐</div><div><div class="gd-step-title lang-ar" style="color:var(--gr)"><span class="gd-step-num">1</span> تسجيل الدخول</div><div class="gd-step-title lang-en" style="color:var(--gr)"><span class="gd-step-num">1</span> Login</div><div class="gd-step-desc lang-ar">ادخل بالبيانات المُرسَلة لبريدك — أول دخول: غيّر كلمة المرور المؤقتة</div><div class="gd-step-desc lang-en">Login with credentials sent to your email — first time: change temporary password</div></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div><div><div class="gd-step-title lang-ar" style="color:var(--pri2)"><span class="gd-step-num">2</span> كروت العمولة</div><div class="gd-step-title lang-en" style="color:var(--pri2)"><span class="gd-step-num">2</span> Commission Cards</div><div class="gd-step-desc lang-ar">ترى كروت فرعك فقط — يمكن إضافة كرت جديد أو تعديل موجود (حسب صلاحيتك)</div><div class="gd-step-desc lang-en">You see your branch cards only — add or edit cards based on your permissions</div></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.06));border:1px solid rgba(138,120,240,.3)">📩</div><div><div class="gd-step-title lang-ar" style="color:var(--pu)"><span class="gd-step-num">3</span> معالجة كروت CC الواردة</div><div class="gd-step-title lang-en" style="color:var(--pu)"><span class="gd-step-num">3</span> Process CC Cards</div><div class="gd-step-desc lang-ar"><strong>مركز الاتصال:</strong> كروت واردة لفرعك — <strong>قبول</strong> ← تنتقل للقائمة الرئيسية — <strong>رفض</strong> ← أدخل السبب</div><div class="gd-step-desc lang-en"><strong>Call Center:</strong> incoming cards for your branch — <strong>Accept</strong> ← moves to main list — <strong>Reject</strong> ← enter reason</div><span class="gd-step-note lang-ar">⏰ راجع قسم CC يومياً</span><span class="gd-step-note lang-en">⏰ Check CC section daily</span></div></div>
    </div>
  </div>

  {{-- ══ Call Center Panel ══ --}}
  <div class="gd-panel" id="gpanel-cc">
    <div class="gd-banner" style="background:linear-gradient(135deg,rgba(123,104,238,.1),rgba(123,104,238,.04));border:1px solid rgba(123,104,238,.25)">
      <div class="gd-banner-ico">📞</div>
      <div>
        <div class="gd-banner-title" style="color:var(--pu)">📞 Call Center — كول سنتر CC</div>
        <div class="gd-banner-sub lang-ar">تُنشئ كروت CC وتُرسلها للفروع — تتابع حالة الكروت</div>
        <div class="gd-banner-sub lang-en">Create CC cards and send to branches — track card status</div>
      </div>
    </div>
    <div class="gd-flow">
      <div class="gd-node" style="background:rgba(123,104,238,.1);border:1px solid rgba(123,104,238,.3)"><div class="gd-node-ico">➕</div><div class="gd-node-lbl lang-ar" style="color:var(--pu)">إنشاء كرت CC</div><div class="gd-node-lbl lang-en" style="color:var(--pu)">Create CC Card</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3)"><div class="gd-node-ico">📤</div><div class="gd-node-lbl lang-ar" style="color:var(--pri2)">إرسال للفرع</div><div class="gd-node-lbl lang-en" style="color:var(--pri2)">Send to Branch</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)"><div class="gd-node-ico">🏢</div><div class="gd-node-lbl lang-ar" style="color:var(--or)">مدير الفرع يراجع</div><div class="gd-node-lbl lang-en" style="color:var(--or)">Branch Reviews</div></div>
      <div class="gd-arrow">→</div>
      <div class="gd-node" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)"><div class="gd-node-ico">✅</div><div class="gd-node-lbl lang-ar" style="color:var(--gr)">قبول / رفض</div><div class="gd-node-lbl lang-en" style="color:var(--gr)">Accept / Reject</div></div>
    </div>
    <div class="gd-steps">
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(123,104,238,.2),rgba(123,104,238,.06));border:1px solid rgba(123,104,238,.3)">➕</div><div><div class="gd-step-title lang-ar" style="color:var(--pu)"><span class="gd-step-num">1</span> إنشاء كرت CC جديد</div><div class="gd-step-title lang-en" style="color:var(--pu)"><span class="gd-step-num">1</span> Create New CC Card</div><div class="gd-step-desc lang-ar"><strong>مركز الاتصال ← كروت CC</strong> ← اضغط ➕ كرت جديد — أدخل: رقم الحساب، الشهر، الفرع، البروكر، الإيداع</div><div class="gd-step-desc lang-en"><strong>Call Center ← CC Cards</strong> ← click ➕ New Card — enter: account number, month, branch, broker, deposit</div></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">👁</div><div><div class="gd-step-title lang-ar" style="color:var(--pri2)"><span class="gd-step-num">2</span> متابعة حالة الكروت</div><div class="gd-step-title lang-en" style="color:var(--pri2)"><span class="gd-step-num">2</span> Track Card Status</div><div class="gd-step-desc lang-ar">ترى حالة كل كرت: ⏳ معلّق — ✅ مقبول — ❌ مرفوض — بعد القبول ينتقل لقائمة كروت العمولة الرئيسية</div><div class="gd-step-desc lang-en">Track each card: ⏳ Pending — ✅ Accepted — ❌ Rejected — after acceptance it joins the main commission cards list</div></div></div>
    </div>
  </div>

  {{-- ══ Viewer Panel ══ --}}
  <div class="gd-panel" id="gpanel-vw">
    <div class="gd-banner" style="background:linear-gradient(135deg,rgba(90,128,160,.1),rgba(90,128,160,.04));border:1px solid rgba(90,128,160,.25)">
      <div class="gd-banner-ico">👁</div>
      <div>
        <div class="gd-banner-title" style="color:var(--mu)">👁 Viewer — مشاهد</div>
        <div class="gd-banner-sub lang-ar">قراءة فقط — مشاهدة البيانات والتقارير دون تعديل</div>
        <div class="gd-banner-sub lang-en">Read only — view data and reports without editing</div>
      </div>
    </div>
    <div class="gd-steps">
      <div class="gd-step"><div class="gd-step-ico" style="background:rgba(90,128,160,.15);border:1px solid rgba(90,128,160,.3)">🔐</div><div><div class="gd-step-title lang-ar"><span class="gd-step-num">1</span> تسجيل الدخول</div><div class="gd-step-title lang-en"><span class="gd-step-num">1</span> Login</div><div class="gd-step-desc lang-ar">ادخل بالبيانات المُرسَلة لبريدك</div><div class="gd-step-desc lang-en">Login with credentials sent to your email</div></div></div>
      <div class="gd-step"><div class="gd-step-ico" style="background:rgba(90,128,160,.15);border:1px solid rgba(90,128,160,.3)">📊</div><div><div class="gd-step-title lang-ar"><span class="gd-step-num">2</span> عرض البيانات</div><div class="gd-step-title lang-en"><span class="gd-step-num">2</span> View Data</div><div class="gd-step-desc lang-ar">لوحة المتابعة + الكروت + التقارير (حسب الصلاحيات الممنوحة) — <strong>لا يمكن التعديل أو الإضافة</strong></div><div class="gd-step-desc lang-en">Dashboard + cards + reports (based on granted permissions) — <strong>no editing or adding</strong></div></div></div>
    </div>
  </div>

  {{-- Section 2: User Manual --}}
  <div class="gd-sec"><span id="gd-sec-manual">📚 دليل المستخدم</span></div>
  <div class="gd-manual">
    <div class="gd-mcard"><div class="gd-mico" style="background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">🗂</div><div class="gd-mbody"><h3 class="lang-ar" style="color:var(--pri2)">1. إضافة كرت عمولة جديد</h3><h3 class="lang-en" style="color:var(--pri2)">1. Add New Commission Card</h3><p class="lang-ar">اضغط <strong>➕ كرت جديد</strong> من شريط التنقل أو القائمة الجانبية — أدخل: رقم الحساب، الشهر، الفرع، البروكر، المسوّق، الإيداع، نوع الحساب (NEW/SUB) — اضغط <strong>حفظ</strong></p><p class="lang-en">Click <strong>➕ New Card</strong> from nav or sidebar — enter: account #, month, branch, broker, marketer, deposit, account type (NEW/SUB) — click <strong>Save</strong></p><span class="gd-mtip lang-ar">💡 رقم الحساب يجب أن يكون فريداً لنفس الشهر</span><span class="gd-mtip lang-en">💡 Account number must be unique per month</span></div></div>
    <div class="gd-mcard"><div class="gd-mico" style="background:linear-gradient(135deg,rgba(245,166,35,.18),rgba(245,166,35,.06));border:1px solid rgba(245,166,35,.3)">✏️</div><div class="gd-mbody"><h3 class="lang-ar" style="color:var(--or)">2. تعديل كرت موجود</h3><h3 class="lang-en" style="color:var(--or)">2. Edit Existing Card</h3><p class="lang-ar">اضغط <strong>✏️ تعديل</strong> من شريط التنقل ← ابحث برقم الحساب ← اختر الكرت ← عدّل البيانات ← اختر <strong>سبب التعديل</strong> ← <strong>حفظ</strong> — التعديل يُسجَّل تلقائياً مع اسم المعدّل والتاريخ</p><p class="lang-en">Click <strong>✏️ Edit</strong> from nav ← search by account # ← select card ← edit data ← choose <strong>edit reason</strong> ← <strong>Save</strong> — edit is auto-logged with editor name and date</p></div></div>
    <div class="gd-mcard"><div class="gd-mico" style="background:linear-gradient(135deg,rgba(34,201,122,.18),rgba(34,201,122,.06));border:1px solid rgba(34,201,122,.3)">📥</div><div class="gd-mbody"><h3 class="lang-ar" style="color:var(--gr)">3. استيراد بيانات Excel</h3><h3 class="lang-en" style="color:var(--gr)">3. Import Excel Data</h3><p class="lang-ar">القائمة الجانبية ← <strong>📥 استيراد بيانات</strong> ← حمّل النموذج ← أدخل بيانات الكروت ← ارفع الملف ← راجع المعاينة ← <strong>استيراد</strong></p><p class="lang-en">Sidebar ← <strong>📥 Import Data</strong> ← download template ← enter card data ← upload file ← review preview ← <strong>Import</strong></p><span class="gd-mtip lang-ar">💡 لا حد للصفوف — آلاف السجلات في ملف واحد</span><span class="gd-mtip lang-en">💡 No row limit — thousands of records per file</span></div></div>
    <div class="gd-mcard"><div class="gd-mico" style="background:linear-gradient(135deg,rgba(26,173,186,.18),rgba(26,173,186,.06));border:1px solid rgba(26,173,186,.3)">📊</div><div class="gd-mbody"><h3 class="lang-ar" style="color:var(--pri2)">4. التقارير والإحصائيات</h3><h3 class="lang-en" style="color:var(--pri2)">4. Reports & Statistics</h3><p class="lang-ar"><strong>📊 لوحة التقارير:</strong> KPIs تتحمّل تلقائياً بمجرد فتح الصفحة — <strong>📋 جدول البيانات:</strong> فلتر متقدم + pagination + تصدير Excel/PDF — <strong>🔧 تقرير ديناميكي:</strong> اختر الأعمدة التي تريدها</p><p class="lang-en"><strong>📊 Reports Dashboard:</strong> KPIs auto-load on page open — <strong>📋 Data Table:</strong> advanced filter + pagination + export Excel/PDF — <strong>🔧 Dynamic Report:</strong> choose your columns</p></div></div>
    <div class="gd-mcard"><div class="gd-mico" style="background:linear-gradient(135deg,rgba(224,80,80,.18),rgba(224,80,80,.06));border:1px solid rgba(224,80,80,.3)">🔔</div><div class="gd-mbody"><h3 class="lang-ar" style="color:#e0708a">5. التنبيهات ومتتبّع الاستجابة</h3><h3 class="lang-en" style="color:#e0708a">5. Notifications & Response Tracker</h3><p class="lang-ar">عند إرسال مركز الاتصال كرتاً جديداً إلى فرع، يصل <strong>تنبيه فوري</strong> لمدير الفرع والمدير المالي عبر <strong>🔔 جرس الإشعارات</strong> أعلى الصفحة (عدّاد أحمر بعدد غير المقروء) مع رسالة منبثقة. اضغط على التنبيه ليفتح <strong>الكرت مباشرة</strong>، أو «تعليم الكل كمقروء».</p><p class="lang-en">When the Call Center sends a new card to a branch, the branch manager and Finance Admin get an <strong>instant alert</strong> via the <strong>🔔 notification bell</strong> in the top bar (red unread badge) plus a toast. Click a notification to <strong>open the card directly</strong>, or “Mark all read”.</p><span class="gd-mtip lang-ar">📊 متتبّع دقيق (المدير المالي): <strong>/notifications/tracker</strong> — يعرض لكل تنبيه أوقات: أُنشئ / وصل / قُرئ / تمّ التصرّف، و<strong>متوسط زمن الاستجابة لكل فرع</strong> ونسبة القراءة.</span><span class="gd-mtip lang-en">📊 Precise tracker (Finance Admin): <strong>/notifications/tracker</strong> — shows per alert: created / delivered / read / acted times, plus <strong>average response time per branch</strong> and read rate.</span></div></div>
  </div>

  {{-- Section 3: Roles Summary --}}
  <div class="gd-sec"><span id="gd-sec-roles">🔐 ملخص الأدوار والصلاحيات</span></div>
  <div class="gd-roles">
    <div class="gd-role" style="border-color:rgba(26,173,186,.3)">
      <h3 style="color:var(--pri2)">💼 <span class="lang-ar">المدير المالي</span><span class="lang-en">Finance Admin</span></h3>
      <p class="lang-ar">✅ كل الصفحات والوظائف<br>✅ استيراد Excel وتصدير<br>✅ إدارة المديرين والفروع<br>✅ اعتماد الموظفين الجدد<br>✅ إعدادات النظام الكاملة</p>
      <p class="lang-en">✅ All pages & functions<br>✅ Import Excel & export<br>✅ Manage managers & branches<br>✅ Approve new employees<br>✅ Full system settings</p>
    </div>
    <div class="gd-role" style="border-color:rgba(34,201,122,.3)">
      <h3 style="color:var(--gr)">🏢 <span class="lang-ar">مدير الفرع</span><span class="lang-en">Branch Manager</span></h3>
      <p class="lang-ar">✅ فرعه المحدد فقط<br>✅ كروت العمولة (حسب صلاحية)<br>✅ مركز الاتصال CC<br>✅ إضافة موظفين (بانتظار اعتماد)<br>⚙️ الصلاحيات يحددها المدير المالي</p>
      <p class="lang-en">✅ Assigned branch only<br>✅ Commission cards (by permission)<br>✅ Call Center CC<br>✅ Add employees (pending approval)<br>⚙️ Permissions set by Finance Admin</p>
    </div>
    <div class="gd-role" style="border-color:rgba(138,120,240,.3)">
      <h3 style="color:var(--pu)">📞 <span class="lang-ar">كول سنتر / مشاهد</span><span class="lang-en">Call Center / Viewer</span></h3>
      <p class="lang-ar">✅ إدخال كروت CC<br>✅ متابعة حالة الكروت<br>✅ قراءة البيانات والتقارير<br>❌ لا تعديل على الكروت<br>❌ لا وصول للإعدادات</p>
      <p class="lang-en">✅ Enter CC cards<br>✅ Track card status<br>✅ Read data & reports<br>❌ No card editing<br>❌ No settings access</p>
    </div>
  </div>

  {{-- Section 4: Roadmap --}}
  <div class="gd-sec"><span id="gd-sec-roadmap">🗺️ خارطة الطريق</span></div>
  <div class="gd-roadmap">
    <div class="gd-rm-hdr">
      <div style="font-size:32px">🗺️</div>
      <div>
        <h2 id="gd-rm-title"><span class="lang-ar">خارطة الطريق الكاملة</span><span class="lang-en">Complete System Roadmap</span></h2>
        <p id="gd-rm-sub"><span class="lang-ar">تدفق العمل من الإعداد حتى التقارير</span><span class="lang-en">Workflow from setup to reports</span></p>
      </div>
    </div>
    <div class="gd-phases">
      <div class="gd-phase" style="background:rgba(26,173,186,.12);border:1px solid rgba(26,173,186,.3)">
        <div class="gd-phase-num" style="background:rgba(26,173,186,.25);border:2px solid var(--pri2);color:var(--pri2)">1</div>
        <div class="gd-phase-ico">⚙️</div>
        <div class="gd-phase-title" style="color:var(--pri2)"><span class="lang-ar">الإعداد</span><span class="lang-en">Setup</span></div>
        <div class="gd-phase-sub"><span class="lang-ar">مرة واحدة</span><span class="lang-en">One time</span></div>
        <div style="margin-top:8px;font-size:10px;color:var(--tx);text-align:right;line-height:1.8">
          <div class="lang-ar">🏢 إنشاء الفروع<br>👥 إضافة الموظفين<br>👤 تعيين المديرين</div>
          <div class="lang-en">🏢 Create branches<br>👥 Add employees<br>👤 Assign managers</div>
        </div>
      </div>
      <div class="gd-phase" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.3)">
        <div class="gd-phase-num" style="background:rgba(34,201,122,.2);border:2px solid var(--gr);color:var(--gr)">2</div>
        <div class="gd-phase-ico">📥</div>
        <div class="gd-phase-title" style="color:var(--gr)"><span class="lang-ar">الإدخال</span><span class="lang-en">Data Entry</span></div>
        <div class="gd-phase-sub"><span class="lang-ar">شهرياً</span><span class="lang-en">Monthly</span></div>
        <div style="margin-top:8px;font-size:10px;color:var(--tx);text-align:right;line-height:1.8">
          <div class="lang-ar">📥 استيراد Excel<br>🗂 إضافة كروت يدوية<br>📞 إدخال كروت CC</div>
          <div class="lang-en">📥 Import Excel<br>🗂 Manual cards<br>📞 Enter CC cards</div>
        </div>
      </div>
      <div class="gd-phase" style="background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3)">
        <div class="gd-phase-num" style="background:rgba(245,166,35,.2);border:2px solid var(--or);color:var(--or)">3</div>
        <div class="gd-phase-ico">✅</div>
        <div class="gd-phase-title" style="color:var(--or)"><span class="lang-ar">المراجعة</span><span class="lang-en">Review</span></div>
        <div class="gd-phase-sub"><span class="lang-ar">يومياً</span><span class="lang-en">Daily</span></div>
        <div style="margin-top:8px;font-size:10px;color:var(--tx);text-align:right;line-height:1.8">
          <div class="lang-ar">📩 قبول/رفض CC<br>✅ اعتماد موظفين<br>✏️ تعديل الكروت</div>
          <div class="lang-en">📩 Accept/reject CC<br>✅ Approve staff<br>✏️ Edit cards</div>
        </div>
      </div>
      <div class="gd-phase" style="background:rgba(138,120,240,.1);border:1px solid rgba(138,120,240,.3)">
        <div class="gd-phase-num" style="background:rgba(138,120,240,.2);border:2px solid var(--pu);color:var(--pu)">4</div>
        <div class="gd-phase-ico">📊</div>
        <div class="gd-phase-title" style="color:var(--pu)"><span class="lang-ar">التقارير</span><span class="lang-en">Reports</span></div>
        <div class="gd-phase-sub"><span class="lang-ar">في أي وقت</span><span class="lang-en">Anytime</span></div>
        <div style="margin-top:8px;font-size:10px;color:var(--tx);text-align:right;line-height:1.8">
          <div class="lang-ar">📊 لوحة التقارير<br>📋 جدول البيانات<br>📤 تصدير PDF/Excel</div>
          <div class="lang-en">📊 Reports dashboard<br>📋 Data table<br>📤 Export PDF/Excel</div>
        </div>
      </div>
    </div>
    <div class="gd-qs">
      <div class="gd-qs-card" style="background:var(--bg3);border:1px solid rgba(26,173,186,.25)">
        <h4 style="color:var(--pri2)"><span class="lang-ar">🚀 بداية سريعة — المدير المالي</span><span class="lang-en">🚀 Quick Start — Finance Admin</span></h4>
        <div style="color:var(--tx)">
          <div class="lang-ar"><span style="color:var(--pri2)">①</span> الإعدادات ← أضف الفروع والموظفين والمديرين<br><span style="color:var(--pri2)">②</span> استيراد ← ارفع ملف Excel الشهري<br><span style="color:var(--pri2)">③</span> مركز الاتصال ← راجع كروت CC يومياً<br><span style="color:var(--pri2)">④</span> التقارير ← تابع الأرقام وصدّر البيانات</div>
          <div class="lang-en"><span style="color:var(--pri2)">①</span> Settings ← add branches, employees, managers<br><span style="color:var(--pri2)">②</span> Import ← upload monthly Excel file<br><span style="color:var(--pri2)">③</span> Call Center ← review CC cards daily<br><span style="color:var(--pri2)">④</span> Reports ← monitor numbers & export data</div>
        </div>
      </div>
      <div class="gd-qs-card" style="background:var(--bg3);border:1px solid rgba(34,201,122,.25)">
        <h4 style="color:var(--gr)"><span class="lang-ar">🚀 بداية سريعة — مدير الفرع</span><span class="lang-en">🚀 Quick Start — Branch Manager</span></h4>
        <div style="color:var(--tx)">
          <div class="lang-ar"><span style="color:var(--gr)">①</span> سجّل الدخول بالبيانات المُرسَلة لبريدك<br><span style="color:var(--gr)">②</span> كروت العمولة ← عرض وإدارة كروت فرعك<br><span style="color:var(--gr)">③</span> مركز الاتصال ← راجع الكروت الواردة يومياً<br><span style="color:var(--gr)">④</span> أضف موظفين جدد عند الحاجة</div>
          <div class="lang-en"><span style="color:var(--gr)">①</span> Login with credentials sent to your email<br><span style="color:var(--gr)">②</span> Commission Cards ← view & manage your branch<br><span style="color:var(--gr)">③</span> Call Center ← review incoming cards daily<br><span style="color:var(--gr)">④</span> Add new employees when needed</div>
        </div>
      </div>
    </div>
  </div>

</div>{{-- gd --}}
@endsection

@push('scripts')
<script>
/* ── Tab switching ── */
function gTab(role) {
  ['fa','bm','cc','vw'].forEach(r => {
    const tab   = document.getElementById('gtab-' + r);
    const panel = document.getElementById('gpanel-' + r);
    const on = r === role;
    if (tab)   tab.classList.toggle('active', on);
    if (panel) { panel.classList.toggle('on', on); panel.style.display = on ? 'block' : 'none'; }
  });
}

/* Auto-select based on current user role */
(function(){
  const role = (typeof CURRENT_USER !== 'undefined' && CURRENT_USER?.role) || 'finance_admin';
  if      (role === 'finance_admin')  gTab('fa');
  else if (role === 'branch_manager') gTab('bm');
  else if (role === 'viewer')         gTab('vw');
  else                                gTab('cc');
})();

/* ── Bilingual ── */
const GI = {
  ar: {
    title:'📖 دليل تشغيل النظام', sub:'اختر دورك لمشاهدة Workflow — ثم اطّلع على دليل المستخدم التفصيلي',
    secWf:'🔄 مخطط سير العمل', secManual:'📚 دليل المستخدم', secRoles:'🔐 ملخص الأدوار', secRoadmap:'🗺️ خارطة الطريق',
    tabFa:'المدير المالي', badgeFa:'كامل', tabBm:'مدير الفرع', badgeBm:'فرعي', tabCc:'كول سنتر', badgeCc:'CC', tabVw:'مشاهد', badgeVw:'قراءة',
    permTitle:'🔐 جدول مقارنة الصلاحيات', ptFn:'الوظيفة', ptFa:'المدير المالي', ptBm:'مدير الفرع', ptVw:'مشاهد',
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
    title:'📖 System User Guide', sub:'Select your role to see your Workflow — then read the detailed user manual',
    secWf:'🔄 Workflow Diagram', secManual:'📚 User Manual', secRoles:'🔐 Roles Summary', secRoadmap:'🗺️ System Roadmap',
    tabFa:'Finance Admin', badgeFa:'Full', tabBm:'Branch Manager', badgeBm:'Branch', tabCc:'Call Center', badgeCc:'CC', tabVw:'Viewer', badgeVw:'Read',
    permTitle:'🔐 Permissions Comparison', ptFn:'Function / Page', ptFa:'Finance Admin', ptBm:'Branch Manager', ptVw:'Viewer',
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

function gL()   { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function gi(k)  { return (GI[gL()] || GI.ar)[k] || GI.ar[k] || k; }
function _t(id,v){ const e = document.getElementById(id); if (e && v !== undefined) e.textContent = v; }

function gApplyLang() {
  const L = gL();
  _t('gd-title', gi('title')); _t('gd-sub', gi('sub'));
  _t('gd-sec-wf', gi('secWf')); _t('gd-sec-manual', gi('secManual'));
  _t('gd-sec-roles', gi('secRoles')); _t('gd-sec-roadmap', gi('secRoadmap'));
  _t('gd-tab-fa', gi('tabFa')); _t('gd-badge-fa', gi('badgeFa'));
  _t('gd-tab-bm', gi('tabBm')); _t('gd-badge-bm', gi('badgeBm'));
  _t('gd-tab-cc', gi('tabCc')); _t('gd-badge-cc', gi('badgeCc'));
  _t('gd-tab-vw', gi('tabVw')); _t('gd-badge-vw', gi('badgeVw'));
  _t('gd-perm-title', gi('permTitle'));
  _t('gd-pt-fn', gi('ptFn')); _t('gd-pt-fa', gi('ptFa'));
  _t('gd-pt-bm', gi('ptBm')); _t('gd-pt-vw', gi('ptVw'));
  /* Topbar */
  const tb = document.querySelector('.tb-title'); if (tb) tb.textContent = gi('title');
  /* Permissions table */
  const tbody = document.getElementById('gd-perm-tbody');
  if (tbody) {
    const yes  = v => v==='❌' ? '<span class="p-no">❌</span>' : `<span class="p-yes">✅ ${v}</span>`;
    const cond = v => v.includes('❌') ? '<span class="p-no">❌</span>' : v.includes('✅') ? `<span class="p-yes">${v}</span>` : `<span class="p-cond">⚙️ ${v}</span>`;
    tbody.innerHTML = gi('rows').map(([fn,fa,bm,vw]) =>
      `<tr><td>${fn}</td><td>${yes(fa)}</td><td>${cond(bm)}</td><td>${cond(vw)}</td></tr>`
    ).join('');
  }
  /* lang-ar / lang-en toggle */
  document.querySelectorAll('.lang-ar').forEach(el => el.style.display = L === 'en' ? 'none' : 'block');
  document.querySelectorAll('.lang-en').forEach(el => el.style.display = L === 'en' ? 'block' : 'none');
}

const _gOrig = window.applyLang;
window.applyLang = function(lang) { if (_gOrig) _gOrig(lang); gApplyLang(); };
gApplyLang();
</script>
@endpush
