@extends('layouts.app')
@section('title','Managers')
@section('page-title','Managers')
@section('topbar-actions')
<button class="tb-btn primary" id="mgr-tb-btn-add" onclick="openModal('modal-add-mgr')">👤 مدير جديد</button>
<button class="tb-btn" id="mgr-tb-btn-invite" onclick="openModal('modal-add-invite')" style="margin-right:8px">📧 دعوة مدير فرع</button>
@endsection

<style>
.icon-btn{display:inline-flex;align-items:center;justify-content:center;
  width:30px;height:30px;border-radius:7px;border:1px solid var(--brd1);
  background:var(--bg3);cursor:pointer;font-size:14px;
  transition:all .18s;color:var(--mu)}
.icon-btn:hover{border-color:var(--pri);background:rgba(26,173,186,.12);color:var(--pri2)}
.icon-btn.danger:hover{border-color:var(--re);background:rgba(232,69,69,.1);color:var(--re)}
.icon-btn.warn:hover{border-color:var(--or);background:rgba(245,166,35,.1);color:var(--or)}
.icon-btn.success-btn:hover{border-color:var(--gr);background:rgba(30,204,128,.1);color:var(--gr)}
.mgr-name-cell{font-weight:700;font-size:13px;white-space:nowrap}
.mgr-email{font-size:10px;color:var(--mu);direction:ltr;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:180px}
.mgr-phone{font-size:10px;color:var(--mu);direction:ltr}
.email-pill{display:inline-flex;align-items:center;gap:5px;background:rgba(30,204,128,.08);
  border:1px solid rgba(30,204,128,.25);border-radius:7px;padding:2px 7px;
  font-size:10px;color:#22c97a;font-weight:700;margin-top:4px}
.email-pill.failed{background:rgba(232,69,69,.08);border-color:rgba(232,69,69,.25);color:var(--re)}
.perm-chip{display:inline-flex;align-items:center;gap:2px;padding:2px 6px;border-radius:5px;
  font-size:9px;font-weight:700;background:rgba(26,173,186,.1);color:var(--pri2);
  border:1px solid rgba(26,173,186,.2);white-space:nowrap}
</style>

@section('content')

{{-- Managers List --}}
<div class="panel" id="mgr-list">
  <div class="panel-header">
    <div class="panel-title" id="mgr-list-title">👤 قائمة المديرين</div>
    <div style="font-size:11px;color:var(--mu)" id="mgr-count"></div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th style="min-width:160px" id="mgr-th-manager">المدير</th>
          <th style="min-width:110px" id="mgr-th-branch">الفرع</th>
          <th style="min-width:90px" id="mgr-th-role">الدور</th>
          <th style="min-width:80px" id="mgr-th-status">الحالة</th>
          <th style="min-width:90px" id="mgr-th-last">آخر دخول</th>
          <th style="min-width:90px" id="mgr-th-perms">الصلاحيات</th>
          <th style="width:100px;text-align:center" id="mgr-th-act">إجراءات</th>
        </tr>
      </thead>
      <tbody id="mgr-tbody">
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)" id="mgr-loading">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- Invites List --}}
<div class="panel" style="margin-top:24px">
  <div class="panel-header" style="display:flex;align-items:center;justify-content:space-between">
    <div class="panel-title" id="inv-list-title">📧 دعوات التسجيل لمدراء الفروع</div>
    <button class="btn btn-primary btn-sm" id="inv-btn-new" onclick="openModal('modal-add-invite')">+ دعوة جديدة</button>
  </div>
  <div style="padding:10px 16px;font-size:12px;color:var(--mu);border-bottom:1px solid var(--brd1);line-height:1.7" id="inv-note">
    📌 أضف إيميل مدير الفرع — سيصله بريد إلكتروني فوراً ويتمكن من التسجيل بنفسه.
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th id="inv-th-email">البريد الإلكتروني</th>
          <th id="inv-th-branch">الفرع</th>
          <th id="inv-th-role">الدور</th>
          <th id="inv-th-note">ملاحظة</th>
          <th id="inv-th-status">الحالة</th>
          <th id="inv-th-date">تاريخ الإضافة</th>
          <th id="inv-th-act">إجراء</th>
        </tr>
      </thead>
      <tbody id="inv-tbody">
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)" id="inv-loading">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══ Modal: Create Manager ══ --}}
<div class="modal-overlay" id="modal-add-mgr">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title" id="mgr-modal-add-title">👤 إنشاء حساب مدير جديد</div>
      <button class="modal-close" onclick="closeModal('modal-add-mgr')">✕</button>
    </div>
    <div class="modal-body">
      <div id="mgr-err" class="alert alert-error"></div>
      <div id="mgr-ok"  class="alert alert-success"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mgr-lbl-name">الاسم الكامل *</label>
          <input type="text"  id="mg-name"  class="form-control" placeholder="Manager Name">
        </div>
        <div class="form-group">
          <label class="form-label" id="mgr-lbl-email">البريد الإلكتروني *</label>
          <input type="email" id="mg-email" class="form-control" placeholder="manager@wafragulf.com">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mgr-lbl-phone">رقم التليفون</label>
          <div style="display:flex;gap:6px">
            <select id="mg-phone-code" class="form-control" style="width:150px;flex-shrink:0;font-size:12px;direction:ltr">
              <option value="+965">🇰🇼 +965 الكويت</option>
              <option value="+966">🇸🇦 +966 السعودية</option>
              <option value="+971">🇦🇪 +971 الإمارات</option>
              <option value="+973">🇧🇭 +973 البحرين</option>
              <option value="+974">🇶🇦 +974 قطر</option>
              <option value="+968">🇴🇲 +968 عُمان</option>
              <option value="+962">🇯🇴 +962 الأردن</option>
              <option value="+20">🇪🇬 +20 مصر</option>
              <option value="+964">🇮🇶 +964 العراق</option>
              <option value="+963">🇸🇾 +963 سوريا</option>
            </select>
            <input type="tel" id="mg-phone" class="form-control" placeholder="5XXXXXXXX" dir="ltr" style="flex:1">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" id="mgr-lbl-branch">الفرع المسؤول عنه *</label>
          <select id="mg-branch" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mgr-lbl-pw">كلمة مرور مؤقتة (فارغة = توليد تلقائي)</label>
          <input type="password" id="mg-pw" class="form-control" placeholder="—">
        </div>
        <div class="form-group">
          <label class="form-label" id="mgr-lbl-role">الدور</label>
          <select id="mg-role" class="form-control">
            <option value="branch_manager" id="mgr-opt-bm">🏢 مدير فرع</option>
            <option value="viewer" id="mgr-opt-viewer">👁 مشاهد</option>
          </select>
        </div>
      </div>
      <div class="form-section-title" style="margin-top:14px" id="mgr-lbl-perms">🔐 الصلاحيات</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <button class="btn btn-ghost btn-sm" id="mgr-btn-all-perms" onclick="selectAllPerms(true,'add')">✅ تحديد الكل</button>
        <button class="btn btn-ghost btn-sm" id="mgr-btn-none-perms" onclick="selectAllPerms(false,'add')">☐ إلغاء الكل</button>
      </div>
      <div id="perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="mgr-btn-cancel-add" onclick="closeModal('modal-add-mgr')">إلغاء</button>
      <button class="btn btn-primary" id="btn-create-mgr" onclick="createManager()">📧 إنشاء وإرسال بيانات الدخول</button>
    </div>
  </div>
</div>

{{-- ══ Modal: Edit Manager ══ --}}
<div class="modal-overlay" id="modal-edit-mgr">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title" id="mgr-modal-edit-title">✏️ تعديل بيانات المدير</div>
      <button class="modal-close" onclick="closeModal('modal-edit-mgr')">✕</button>
    </div>
    <div class="modal-body">
      <div id="edit-mgr-err" class="alert alert-error"></div>
      <div id="edit-mgr-ok"  class="alert alert-success"></div>
      <input type="hidden" id="edit-mgr-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mgr-edit-lbl-name">الاسم الكامل *</label>
          <input type="text" id="edit-mg-name" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label" id="mgr-edit-lbl-phone">رقم التليفون</label>
          <input type="tel" id="edit-mg-phone" class="form-control" dir="ltr">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mgr-edit-lbl-branch">الفرع *</label>
          <select id="edit-mg-branch" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label" id="mgr-edit-lbl-status">الحالة</label>
          <select id="edit-mg-active" class="form-control">
            <option value="1" id="mgr-opt-active">✅ نشط</option>
            <option value="0" id="mgr-opt-disabled">🚫 معطّل</option>
          </select>
        </div>
      </div>
      <div class="form-section-title" style="margin-top:14px" id="mgr-edit-lbl-perms">🔐 الصلاحيات</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <button class="btn btn-ghost btn-sm" id="mgr-btn-all-perms-edit" onclick="selectAllPerms(true,'edit')">✅ تحديد الكل</button>
        <button class="btn btn-ghost btn-sm" id="mgr-btn-none-perms-edit" onclick="selectAllPerms(false,'edit')">☐ إلغاء الكل</button>
      </div>
      <div id="edit-perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="mgr-btn-cancel-edit" onclick="closeModal('modal-edit-mgr')">إلغاء</button>
      <button class="btn btn-primary" id="btn-save-mgr" onclick="saveManager()">💾 حفظ التعديلات</button>
    </div>
  </div>
</div>

{{-- ══ Modal: Add Invite ══ --}}
<div class="modal-overlay" id="modal-add-invite">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title" id="inv-modal-title">📧 دعوة مدير فرع للتسجيل</div>
      <button class="modal-close" onclick="closeModal('modal-add-invite')">✕</button>
    </div>
    <div class="modal-body">
      <div id="inv-modal-note" style="background:rgba(26,173,186,.07);border:1px solid rgba(26,173,186,.2);border-radius:10px;padding:11px 14px;font-size:12px;color:var(--mu);margin-bottom:14px;line-height:1.8">
        📌 سيُرسل النظام <strong style="color:var(--pri2)">بريد إلكتروني تلقائياً</strong> لمدير الفرع يُعلمه بأن إيميله تمت إضافته وأنه يستطيع التسجيل الآن.
      </div>
      <div id="inv-err-modal" class="alert alert-error"></div>
      <div id="inv-ok-modal"  class="alert alert-success"></div>
      <div class="form-group">
        <label class="form-label" id="inv-lbl-email">البريد الإلكتروني *</label>
        <input type="email" id="inv-email-input" class="form-control" placeholder="manager@example.com">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="inv-lbl-branch">الفرع (اختياري)</label>
          <select id="inv-branch-input" class="form-control">
            <option value="" id="inv-opt-no-branch">— بدون فرع محدد —</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label" id="inv-lbl-role">الدور</label>
          <select id="inv-role-input" class="form-control">
            <option value="branch_manager" id="inv-opt-bm">🏢 مدير فرع</option>
            <option value="viewer" id="inv-opt-viewer">👁 مشاهد</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" id="inv-lbl-note">ملاحظة (اختياري)</label>
        <input type="text" id="inv-note-input" class="form-control" placeholder="مثال: مدير فرع الرياض">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="inv-btn-cancel" onclick="closeModal('modal-add-invite')">إلغاء</button>
      <button class="btn btn-primary" id="inv-btn-submit" onclick="addInvite()">📧 إضافة الدعوة وإرسال البريد</button>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// BILINGUAL DICTIONARY
// ══════════════════════════════════════════════════════════
const MGR_I18N = {
  ar: {
    tbBtnAdd:'👤 مدير جديد', tbBtnInvite:'📧 دعوة مدير فرع',
    listTitle:'👤 قائمة المديرين', mgrCount:' مدير',
    thManager:'المدير', thBranch:'الفرع', thRole:'الدور', thStatus:'الحالة',
    thLast:'آخر دخول', thPerms:'الصلاحيات', thAct:'إجراءات',
    loading:'جاري التحميل...', noManagers:'لا يوجد مديرون بعد',
    invListTitle:'📧 دعوات التسجيل لمدراء الفروع',
    invBtnNew:'+ دعوة جديدة',
    invNote:'📌 أضف إيميل مدير الفرع — سيصله بريد إلكتروني فوراً ويتمكن من التسجيل بنفسه.',
    invThEmail:'البريد الإلكتروني', invThBranch:'الفرع', invThRole:'الدور',
    invThNote:'ملاحظة', invThStatus:'الحالة', invThDate:'تاريخ الإضافة', invThAct:'إجراء',
    invLoading:'جاري التحميل...', invNone:'لا توجد دعوات. أضف إيميل مدير فرع لبدء التسجيل.',
    invPending:'⏳ بانتظار التسجيل', invUsed:'✅ مُستخدمة', invExpired:'مُنتهية',
    modalAddTitle:'👤 إنشاء حساب مدير جديد',
    lblName:'الاسم الكامل *', lblEmail:'البريد الإلكتروني *',
    lblPhone:'رقم التليفون', lblBranch:'الفرع المسؤول عنه *',
    lblPw:'كلمة مرور مؤقتة (فارغة = توليد تلقائي)',
    lblRole:'الدور', lblPerms:'🔐 الصلاحيات',
    btnAllPerms:'✅ تحديد الكل', btnNonePerms:'☐ إلغاء الكل',
    btnCancelAdd:'إلغاء', btnCreate:'📧 إنشاء وإرسال بيانات الدخول', btnCreating:'⏳ جاري الإنشاء...',
    modalEditTitle:'✏️ تعديل بيانات المدير',
    editLblName:'الاسم الكامل *', editLblPhone:'رقم التليفون',
    editLblBranch:'الفرع *', editLblStatus:'الحالة',
    optActive:'✅ نشط', optDisabled:'🚫 معطّل',
    editLblPerms:'🔐 الصلاحيات',
    btnCancelEdit:'إلغاء', btnSave:'💾 حفظ التعديلات', btnSaving:'⏳ جاري الحفظ...',
    invModalTitle:'📧 دعوة مدير فرع للتسجيل',
    invModalNote:'📌 سيُرسل النظام <strong style="color:var(--pri2)">بريد إلكتروني تلقائياً</strong> لمدير الفرع يُعلمه بأن إيميله تمت إضافته وأنه يستطيع التسجيل الآن.',
    invLblEmail:'البريد الإلكتروني *', invLblBranch:'الفرع (اختياري)',
    invOptNoBranch:'— بدون فرع محدد —',
    invLblRole:'الدور', invLblNote:'ملاحظة (اختياري)',
    invPlaceholderNote:'مثال: مدير فرع الرياض',
    invBtnCancel:'إلغاء', invBtnSubmit:'📧 إضافة الدعوة وإرسال البريد',
    roleOptBM:'🏢 مدير فرع', roleOptViewer:'👁 مشاهد',
    statusActive:'● نشط', statusDisabled:'○ معطّل',
    errFillName:'يرجى ملء الاسم والإيميل', errFillEmail:'أدخل البريد الإلكتروني',
    errEnterName:'أدخل الاسم', errGeneric:'حدث خطأ',
    successCreated:'✅ تم إنشاء الحساب!',
    emailSent:'📧 تم إرسال بيانات الدخول للمدير بالبريد الإلكتروني',
    emailNotSent:'⚠️ الحساب أُنشئ لكن البريد لم يُرسل — تحقق من إعدادات البريد',
    credPw:'🔑 كلمة المرور:',
    savedOk:'✅ تم حفظ التعديلات بنجاح',
    confirmToggle:'تعطيل المدير: {name}?', confirmToggleEn:'Enable Manager: {name}?',
    toggleDisable:'تعطيل', toggleEnable:'تفعيل',
    toggledOk:'تم {action} المدير بنجاح',
    confirmReset:'إعادة تعيين كلمة مرور: {name}?',
    resetOk:'كلمة المرور الجديدة: {pw}',
    emailSentNote:' (تم الإرسال بالبريد)', emailNotSentNote:' (البريد لم يُرسل)',
    invAddedOk:'✅ تمت إضافة {email} للقائمة.',
    invEmailSent:'<br><span style="color:var(--gr)">📧 تم إرسال بريد إلكتروني للمدير</span>',
    invEmailNotSent:'<br><span style="color:var(--or)">⚠️ الدعوة أُضيفت لكن البريد لم يُرسل</span>',
    confirmDeleteInv:'حذف الدعوة لـ {email}؟',
    invDeleted:'تم حذف الدعوة',
    mgrNotFound:'لم يتم العثور على المدير',
    titleResetPw:'إعادة تعيين كلمة المرور',
    titleToggle:'تغيير الحالة',
    titleEdit:'تعديل',
  },
  en: {
    tbBtnAdd:'👤 New Manager', tbBtnInvite:'📧 Invite Branch Manager',
    listTitle:'👤 Managers List', mgrCount:' manager(s)',
    thManager:'Manager', thBranch:'Branch', thRole:'Role', thStatus:'Status',
    thLast:'Last Login', thPerms:'Permissions', thAct:'Actions',
    loading:'Loading...', noManagers:'No managers yet',
    invListTitle:'📧 Branch Manager Registration Invites',
    invBtnNew:'+ New Invite',
    invNote:'📌 Add the branch manager\'s email — they will receive an email immediately and can self-register.',
    invThEmail:'Email', invThBranch:'Branch', invThRole:'Role',
    invThNote:'Note', invThStatus:'Status', invThDate:'Date Added', invThAct:'Action',
    invLoading:'Loading...', invNone:'No invites. Add a branch manager email to start registration.',
    invPending:'⏳ Pending Registration', invUsed:'✅ Used', invExpired:'Expired',
    modalAddTitle:'👤 Create New Manager Account',
    lblName:'Full Name *', lblEmail:'Email Address *',
    lblPhone:'Phone Number', lblBranch:'Assigned Branch *',
    lblPw:'Temporary Password (blank = auto-generate)',
    lblRole:'Role', lblPerms:'🔐 Permissions',
    btnAllPerms:'✅ Select All', btnNonePerms:'☐ None',
    btnCancelAdd:'Cancel', btnCreate:'📧 Create & Send Login Credentials', btnCreating:'⏳ Creating...',
    modalEditTitle:'✏️ Edit Manager',
    editLblName:'Full Name *', editLblPhone:'Phone Number',
    editLblBranch:'Branch *', editLblStatus:'Status',
    optActive:'✅ Active', optDisabled:'🚫 Disabled',
    editLblPerms:'🔐 Permissions',
    btnCancelEdit:'Cancel', btnSave:'💾 Save Changes', btnSaving:'⏳ Saving...',
    invModalTitle:'📧 Invite Branch Manager',
    invModalNote:'📌 The system will send an <strong style="color:var(--pri2)">automatic email</strong> to the branch manager notifying them that their email was added and they can now register.',
    invLblEmail:'Email Address *', invLblBranch:'Branch (optional)',
    invOptNoBranch:'— No specific branch —',
    invLblRole:'Role', invLblNote:'Note (optional)',
    invPlaceholderNote:'e.g. Riyadh Branch Manager',
    invBtnCancel:'Cancel', invBtnSubmit:'📧 Add Invite & Send Email',
    roleOptBM:'🏢 Branch Manager', roleOptViewer:'👁 Viewer',
    statusActive:'● Active', statusDisabled:'○ Disabled',
    errFillName:'Please fill in name and email', errFillEmail:'Enter email address',
    errEnterName:'Enter name', errGeneric:'An error occurred',
    successCreated:'✅ Account Created!',
    emailSent:'📧 Login credentials sent to the manager via email',
    emailNotSent:'⚠️ Account created but email not sent — check email settings',
    credPw:'🔑 Password:',
    savedOk:'✅ Changes saved successfully',
    confirmToggle:'Disable Manager: {name}?', confirmToggleEn:'Enable Manager: {name}?',
    toggleDisable:'Disable', toggleEnable:'Enable',
    toggledOk:'{action} manager successfully',
    confirmReset:'Reset password for: {name}?',
    resetOk:'New password: {pw}',
    emailSentNote:' (sent via email)', emailNotSentNote:' (email not sent)',
    invAddedOk:'✅ Added {email} to the list.',
    invEmailSent:'<br><span style="color:var(--gr)">📧 Email sent to manager</span>',
    invEmailNotSent:'<br><span style="color:var(--or)">⚠️ Invite added but email not sent</span>',
    confirmDeleteInv:'Delete invite for {email}?',
    invDeleted:'Invite deleted',
    mgrNotFound:'Manager not found',
    titleResetPw:'Reset Password',
    titleToggle:'Toggle Status',
    titleEdit:'Edit',
  }
};
function mgrL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function mgr(k) { return (MGR_I18N[mgrL()] || MGR_I18N.ar)[k] || (MGR_I18N.ar)[k] || k; }

function mgrApplyLang() {
  const _t = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  const _h = (id, v) => { const el = document.getElementById(id); if (el) el.innerHTML = v; };
  // Topbar
  _t('mgr-tb-btn-add',    mgr('tbBtnAdd'));
  _t('mgr-tb-btn-invite', mgr('tbBtnInvite'));
  // Managers list
  _t('mgr-list-title', mgr('listTitle'));
  _t('mgr-th-manager', mgr('thManager')); _t('mgr-th-branch', mgr('thBranch'));
  _t('mgr-th-role',    mgr('thRole'));    _t('mgr-th-status', mgr('thStatus'));
  _t('mgr-th-last',    mgr('thLast'));    _t('mgr-th-perms',  mgr('thPerms'));
  _t('mgr-th-act',     mgr('thAct'));
  // Invites
  _t('inv-list-title', mgr('invListTitle'));
  _t('inv-btn-new',    mgr('invBtnNew'));
  _t('inv-note',       mgr('invNote'));
  _t('inv-th-email',   mgr('invThEmail')); _t('inv-th-branch', mgr('invThBranch'));
  _t('inv-th-role',    mgr('invThRole'));  _t('inv-th-note',   mgr('invThNote'));
  _t('inv-th-status',  mgr('invThStatus')); _t('inv-th-date', mgr('invThDate'));
  _t('inv-th-act',     mgr('invThAct'));
  // Add modal
  _t('mgr-modal-add-title', mgr('modalAddTitle'));
  _t('mgr-lbl-name',   mgr('lblName'));   _t('mgr-lbl-email',  mgr('lblEmail'));
  _t('mgr-lbl-phone',  mgr('lblPhone')); _t('mgr-lbl-branch', mgr('lblBranch'));
  _t('mgr-lbl-pw',     mgr('lblPw'));     _t('mgr-lbl-role',   mgr('lblRole'));
  _t('mgr-lbl-perms',  mgr('lblPerms'));
  _t('mgr-btn-all-perms',  mgr('btnAllPerms'));
  _t('mgr-btn-none-perms', mgr('btnNonePerms'));
  _t('mgr-btn-cancel-add', mgr('btnCancelAdd'));
  _t('mgr-opt-bm',     mgr('roleOptBM'));  _t('mgr-opt-viewer', mgr('roleOptViewer'));
  const btnCreate = document.getElementById('btn-create-mgr');
  if (btnCreate && !btnCreate.disabled) btnCreate.textContent = mgr('btnCreate');
  // Edit modal
  _t('mgr-modal-edit-title', mgr('modalEditTitle'));
  _t('mgr-edit-lbl-name',   mgr('editLblName'));  _t('mgr-edit-lbl-phone', mgr('editLblPhone'));
  _t('mgr-edit-lbl-branch', mgr('editLblBranch')); _t('mgr-edit-lbl-status', mgr('editLblStatus'));
  _t('mgr-opt-active',   mgr('optActive'));   _t('mgr-opt-disabled', mgr('optDisabled'));
  _t('mgr-edit-lbl-perms', mgr('editLblPerms'));
  _t('mgr-btn-all-perms-edit',  mgr('btnAllPerms'));
  _t('mgr-btn-none-perms-edit', mgr('btnNonePerms'));
  _t('mgr-btn-cancel-edit', mgr('btnCancelEdit'));
  const btnSave = document.getElementById('btn-save-mgr');
  if (btnSave && !btnSave.disabled) btnSave.textContent = mgr('btnSave');
  // Invite modal
  _t('inv-modal-title',    mgr('invModalTitle'));
  _h('inv-modal-note',     mgr('invModalNote'));
  _t('inv-lbl-email',      mgr('invLblEmail'));   _t('inv-lbl-branch',  mgr('invLblBranch'));
  _t('inv-opt-no-branch',  mgr('invOptNoBranch'));
  _t('inv-lbl-role',       mgr('invLblRole'));    _t('inv-lbl-note',    mgr('invLblNote'));
  _t('inv-opt-bm',         mgr('roleOptBM'));     _t('inv-opt-viewer',  mgr('roleOptViewer'));
  _t('inv-btn-cancel',     mgr('invBtnCancel')); _t('inv-btn-submit',   mgr('invBtnSubmit'));
  const invNote = document.getElementById('inv-note-input');
  if (invNote) invNote.placeholder = mgr('invPlaceholderNote');
  // Re-render dynamic content
  if (_mgrData.length)  renderMgrTable(_mgrData);
  if (_invData.length)  renderInvTable(_invData);
  // Re-build perm grids with updated labels
  buildPermGrid('perm-grid',      '');
  buildPermGrid('edit-perm-grid', 'e-');
}

const _mgrOrig = window.applyLang;
window.applyLang = function(lang) { if (_mgrOrig) _mgrOrig(lang); mgrApplyLang(); };

const PERMS = [
  {id:'dashboard',   ar:'لوحة المتابعة',    en:'Dashboard'},
  {id:'cards',       ar:'كروت العمولات',     en:'Commission Cards'},
  {id:'modified',    ar:'الحسابات المعدّلة', en:'Modified Accounts'},
  {id:'reports',     ar:'التقارير',          en:'Reports'},
  {id:'create_card', ar:'إنشاء كرت',         en:'Create Card'},
  {id:'edit_card',   ar:'تعديل الحسابات',    en:'Edit Accounts'},
  {id:'employees',   ar:'إدارة الموظفين',    en:'Manage Employees'},
  {id:'import',      ar:'استيراد بيانات',    en:'Import Data'},
  {id:'export',      ar:'تصدير البيانات',    en:'Export Data'},
];
const ROLE_AR = { branch_manager:'🏢 مدير فرع', viewer:'👁 مشاهد' };
const ROLE_EN = { branch_manager:'🏢 Branch Manager', viewer:'👁 Viewer' };

function permLabel(p) { return mgrL() === 'en' ? p.en : p.ar; }
function roleLabel(role) { return mgrL() === 'en' ? (ROLE_EN[role]||role) : (ROLE_AR[role]||role); }

// ── Build perm grid (add / edit) ──────────────────────────
function buildPermGrid(containerId, prefix) {
  const container = document.getElementById(containerId);
  if (!container) return;
  // Preserve existing checked states
  const checkedMap = {};
  PERMS.forEach(p => {
    const chk = document.getElementById(prefix+'pchk-'+p.id);
    if (chk) checkedMap[p.id] = chk.textContent === '✓';
    else checkedMap[p.id] = true; // default on
  });
  container.innerHTML = PERMS.map(p => {
    const isOn = checkedMap[p.id] !== false;
    return `
    <div class="panel" style="padding:8px 10px;display:flex;align-items:center;gap:8px;cursor:pointer;background:${isOn?'rgba(46,134,171,.1)':'var(--inp-bg)'};border-color:${isOn?'rgba(46,134,171,.3)':'var(--brd1)'}"
         id="${prefix}pit-${p.id}" onclick="togPerm('${p.id}','${prefix}')">
      <div style="width:16px;height:16px;border-radius:4px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;flex-shrink:0"
           id="${prefix}pchk-${p.id}">${isOn?'✓':''}</div>
      <div style="font-size:11px;font-weight:600">${permLabel(p)}</div>
    </div>`;
  }).join('');
}

function togPerm(id, prefix) {
  const el  = document.getElementById(prefix+'pit-' +id);
  const chk = document.getElementById(prefix+'pchk-'+id);
  const on  = chk.textContent === '✓';
  chk.textContent    = on ? '' : '✓';
  el.style.background  = on ? 'var(--inp-bg)' : 'rgba(46,134,171,.1)';
  el.style.borderColor = on ? 'var(--brd1)'   : 'rgba(46,134,171,.3)';
}

function selectAllPerms(v, mode) {
  const prefix = mode === 'edit' ? 'e-' : '';
  PERMS.forEach(p => {
    const chk = document.getElementById(prefix+'pchk-'+p.id);
    const el  = document.getElementById(prefix+'pit-' +p.id);
    if (!chk || !el) return;
    chk.textContent    = v ? '✓' : '';
    el.style.background  = v ? 'rgba(46,134,171,.1)' : 'var(--inp-bg)';
    el.style.borderColor = v ? 'rgba(46,134,171,.3)' : 'var(--brd1)';
  });
}

function getSelectedPerms(prefix) {
  return PERMS.filter(p => {
    const chk = document.getElementById(prefix+'pchk-'+p.id);
    return chk && chk.textContent === '✓';
  }).map(p => p.id);
}

function setPerms(perms, prefix) {
  PERMS.forEach(p => {
    const has = perms.includes(p.id);
    const chk = document.getElementById(prefix+'pchk-'+p.id);
    const el  = document.getElementById(prefix+'pit-' +p.id);
    if (!chk || !el) return;
    chk.textContent    = has ? '✓' : '';
    el.style.background  = has ? 'rgba(46,134,171,.1)' : 'var(--inp-bg)';
    el.style.borderColor = has ? 'rgba(46,134,171,.3)' : 'var(--brd1)';
  });
}

// ── State ─────────────────────────────────────────────────
let _mgrData  = [];
let _invData  = [];
let _branchOpts = [];

// ── Init ──────────────────────────────────────────────────
async function init() {
  buildPermGrid('perm-grid',      '');
  buildPermGrid('edit-perm-grid', 'e-');
  await Promise.all([loadManagers(), loadBranches(), loadInvites()]);
  mgrApplyLang();
}

function renderMgrTable(data) {
  const L = mgrL();
  document.getElementById('mgr-count').textContent = data.length + mgr('mgrCount');
  document.getElementById('mgr-tbody').innerHTML = data.length
    ? data.map(m => `
      <tr style="${!m.is_active ? 'opacity:.55' : ''}">
        <td>
          <div class="mgr-name-cell">${esc(m.name)}</div>
          <div class="mgr-email" title="${esc(m.email)}">${esc(m.email)}</div>
          ${m.phone ? `<div class="mgr-phone">${esc(m.phone)}</div>` : ''}
        </td>
        <td style="font-size:12px">${esc(m.branch?.name_ar || '—')}</td>
        <td><span class="badge badge-blue" style="font-size:10px">${esc(roleLabel(m.role))}</span></td>
        <td><span class="badge ${m.is_active ? 'badge-green' : 'badge-red'}" style="font-size:10px">${m.is_active ? mgr('statusActive') : mgr('statusDisabled')}</span></td>
        <td style="font-size:10px;color:var(--mu)">${m.last_login ? esc(m.last_login.slice(0,10)) : '<span style="color:var(--mu)">—</span>'}</td>
        <td>
          <div style="display:flex;flex-wrap:wrap;gap:3px">
            ${(m.permissions||[]).slice(0,3).map(p => `<span class="perm-chip">${esc(p)}</span>`).join('')}
            ${(m.permissions||[]).length > 3 ? `<span class="perm-chip">+${(m.permissions||[]).length - 3}</span>` : ''}
          </div>
        </td>
        <td>
          <div style="display:flex;gap:4px;justify-content:center">
            <button class="icon-btn" onclick="editMgr(${m.id})" title="${mgr('titleEdit')}">✏️</button>
            <button class="icon-btn warn" onclick="resetPw(${m.id},'${esc(m.name)}')" title="${mgr('titleResetPw')}">🔑</button>
            <button class="icon-btn ${m.is_active ? 'danger' : 'success-btn'}" onclick="toggleMgr(${m.id},${m.is_active},'${esc(m.name)}')" title="${mgr('titleToggle')}">${m.is_active ? '🚫' : '✅'}</button>
          </div>
        </td>
      </tr>`).join('')
    : `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">${mgr('noManagers')}</td></tr>`;
}

async function loadManagers() {
  const r = await api('GET', '/managers');
  if (!r.success) return;
  _mgrData = r.data || [];
  renderMgrTable(_mgrData);
}

async function loadBranches() {
  const r = await api('GET', '/branches');
  if (!r.success) return;
  _branchOpts = r.data;
  const opt = r.data.map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
  document.getElementById('mg-branch').innerHTML        = opt;
  document.getElementById('edit-mg-branch').innerHTML   = opt;
  document.getElementById('inv-branch-input').innerHTML =
    `<option value="" id="inv-opt-no-branch">${mgr('invOptNoBranch')}</option>` + opt;
}

function renderInvTable(data) {
  document.getElementById('inv-tbody').innerHTML = data.length
    ? data.map(i => `
      <tr>
        <td style="font-weight:600">${esc(i.email)}</td>
        <td style="font-size:12px">${i.branch?.name_ar || '<span style="color:var(--mu)">—</span>'}</td>
        <td><span class="badge badge-blue" style="font-size:10px">${roleLabel(i.role)}</span></td>
        <td style="color:var(--mu);font-size:11px">${i.note || '—'}</td>
        <td>${i.is_pending
            ? `<span class="badge badge-yellow">${mgr('invPending')}</span>`
            : `<span class="badge badge-green">${mgr('invUsed')}</span>`}</td>
        <td style="color:var(--mu);font-size:10px">${i.created_at?.slice(0,10) || '—'}</td>
        <td>${i.is_pending
            ? `<button class="icon-btn danger" title="${mgr('invDeleted')}" onclick="deleteInvite(${i.id},'${esc(i.email)}')">🗑</button>`
            : `<span style="color:var(--mu);font-size:10px">${mgr('invExpired')}</span>`}</td>
      </tr>`).join('')
    : `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">${mgr('invNone')}</td></tr>`;
}

async function loadInvites() {
  const r = await api('GET', '/manager-invites');
  if (!r.success) return;
  _invData = r.data || [];
  renderInvTable(_invData);
}

// ── Create Manager ────────────────────────────────────────
async function createManager() {
  const name   = document.getElementById('mg-name').value.trim();
  const email  = document.getElementById('mg-email').value.trim();
  const branch = document.getElementById('mg-branch').value;
  const phoneCode = document.getElementById('mg-phone-code').value;
  const phoneNum  = document.getElementById('mg-phone').value.trim();
  const phone  = phoneNum ? (phoneCode + phoneNum) : null;
  const role   = document.getElementById('mg-role').value;
  const errEl  = document.getElementById('mgr-err');
  const okEl   = document.getElementById('mgr-ok');
  errEl.classList.remove('show'); okEl.classList.remove('show');
  if (!name || !email) {
    errEl.textContent = mgr('errFillName'); errEl.classList.add('show'); return;
  }
  const perms = getSelectedPerms('');
  const btn = document.getElementById('btn-create-mgr');
  btn.disabled = true; btn.textContent = mgr('btnCreating');
  const r = await api('POST', '/managers', { name, email, phone, branch_id: parseInt(branch), role, password: document.getElementById('mg-pw').value || null, permissions: perms });
  btn.disabled = false; btn.textContent = mgr('btnCreate');
  if (r.success) {
    const emailStatus = r.email_sent
      ? `<span style="color:var(--gr)">${mgr('emailSent')}</span>`
      : `<span style="color:var(--or)">${mgr('emailNotSent')}</span>`;
    okEl.innerHTML = `${mgr('successCreated')}<br>
      📧 <b>${esc(email)}</b><br>
      ${mgr('credPw')} <b style="font-family:monospace;color:var(--pri2)">${esc(r.credentials?.temp_password || '—')}</b><br>
      ${emailStatus}`;
    okEl.classList.add('show');
    _mgrData = [];
    loadManagers();
    document.getElementById('mg-name').value = '';
    document.getElementById('mg-email').value = '';
    document.getElementById('mg-pw').value = '';
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || mgr('errGeneric'); errEl.classList.add('show');
  }
}

// ── Edit Manager ──────────────────────────────────────────
let _editMgrData = null;
async function editMgr(id) {
  const r = await api('GET', '/managers');
  if (!r.success) return;
  const m = r.data.find(x => x.id === id);
  if (!m) { toast(mgr('mgrNotFound'), 'error'); return; }
  _editMgrData = m;
  document.getElementById('edit-mgr-id').value    = m.id;
  document.getElementById('edit-mg-name').value   = m.name;
  document.getElementById('edit-mg-phone').value  = m.phone || '';
  document.getElementById('edit-mg-branch').value = m.branch?.id || '';
  document.getElementById('edit-mg-active').value = m.is_active ? '1' : '0';
  document.getElementById('edit-mgr-err').classList.remove('show');
  document.getElementById('edit-mgr-ok').classList.remove('show');
  setPerms(m.permissions || [], 'e-');
  openModal('modal-edit-mgr');
}

async function saveManager() {
  const id     = parseInt(document.getElementById('edit-mgr-id').value);
  const name   = document.getElementById('edit-mg-name').value.trim();
  const phone  = document.getElementById('edit-mg-phone').value.trim() || null;
  const branch = document.getElementById('edit-mg-branch').value;
  const active = document.getElementById('edit-mg-active').value === '1';
  const perms  = getSelectedPerms('e-');
  const errEl  = document.getElementById('edit-mgr-err');
  const okEl   = document.getElementById('edit-mgr-ok');
  errEl.classList.remove('show'); okEl.classList.remove('show');
  if (!name) { errEl.textContent = mgr('errEnterName'); errEl.classList.add('show'); return; }
  const btn = document.getElementById('btn-save-mgr');
  btn.disabled = true; btn.textContent = mgr('btnSaving');
  const r = await api('PUT', `/managers/${id}`, { name, phone, branch_id: parseInt(branch), is_active: active, permissions: perms });
  btn.disabled = false; btn.textContent = mgr('btnSave');
  if (r.success) {
    okEl.textContent = mgr('savedOk'); okEl.classList.add('show');
    _mgrData = [];
    loadManagers();
    setTimeout(() => closeModal('modal-edit-mgr'), 1200);
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || mgr('errGeneric'); errEl.classList.add('show');
  }
}

// ── Toggle active ─────────────────────────────────────────
async function toggleMgr(id, currentlyActive, name) {
  const actionKey = currentlyActive ? 'toggleDisable' : 'toggleEnable';
  const action = mgr(actionKey);
  const confirmKey = currentlyActive ? 'confirmToggle' : 'confirmToggleEn';
  if (!confirm(mgr(confirmKey).replace('{name}', name))) return;
  const r = await api('PUT', `/managers/${id}`, { is_active: !currentlyActive });
  if (r.success) { toast(mgr('toggledOk').replace('{action}', action), 'success'); _mgrData = []; loadManagers(); }
  else toast(r.message || mgr('errGeneric'), 'error');
}

// ── Reset Password ────────────────────────────────────────
async function resetPw(id, name) {
  if (!confirm(mgr('confirmReset').replace('{name}', name))) return;
  const r = await api('POST', `/managers/${id}/reset-password`);
  if (r.success) {
    const emailNote = r.email_sent ? mgr('emailSentNote') : mgr('emailNotSentNote');
    toast(mgr('resetOk').replace('{pw}', r.new_password) + emailNote, 'info');
  } else toast(r.message || mgr('errGeneric'), 'error');
}

// ── Add Invite ────────────────────────────────────────────
async function addInvite() {
  const email    = document.getElementById('inv-email-input').value.trim();
  const branchId = document.getElementById('inv-branch-input').value || null;
  const role     = document.getElementById('inv-role-input').value;
  const note     = document.getElementById('inv-note-input').value.trim();
  const errEl    = document.getElementById('inv-err-modal');
  const okEl     = document.getElementById('inv-ok-modal');
  errEl.textContent = ''; errEl.classList.remove('show'); okEl.classList.remove('show');
  if (!email) { errEl.textContent = mgr('errFillEmail'); errEl.classList.add('show'); return; }
  const r = await api('POST', '/manager-invites', { email, branch_id: branchId ? parseInt(branchId) : null, role, note: note || null });
  if (r.success) {
    const emailNote = r.email_sent ? mgr('invEmailSent') : mgr('invEmailNotSent');
    okEl.innerHTML = `${mgr('invAddedOk').replace('{email}', `<b>${esc(email)}</b>`)}${emailNote}`;
    okEl.classList.add('show');
    document.getElementById('inv-email-input').value = '';
    document.getElementById('inv-note-input').value  = '';
    _invData = [];
    loadInvites();
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || mgr('errGeneric'); errEl.classList.add('show');
  }
}

// ── Delete Invite ─────────────────────────────────────────
async function deleteInvite(id, email) {
  if (!confirm(mgr('confirmDeleteInv').replace('{email}', email))) return;
  const r = await api('DELETE', `/manager-invites/${id}`);
  if (r.success) { toast(mgr('invDeleted'), 'success'); _invData = []; loadInvites(); }
  else toast(r.message || mgr('errGeneric'), 'error');
}

init();
</script>
