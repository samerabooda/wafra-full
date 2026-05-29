@extends('layouts.app')
@section('title','المديرون')
@section('page-title','إدارة المديرين')
@section('topbar-actions')
<button class="tb-btn primary" onclick="openModal('modal-add-mgr')">👤 مدير جديد</button>
<button class="tb-btn" onclick="openModal('modal-add-invite')" style="margin-right:8px">📧 دعوة مدير فرع</button>
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
    <div class="panel-title">👤 قائمة المديرين</div>
    <div style="font-size:11px;color:var(--mu)" id="mgr-count"></div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th style="min-width:160px">المدير</th>
          <th style="min-width:110px">الفرع</th>
          <th style="min-width:90px">الدور</th>
          <th style="min-width:80px">الحالة</th>
          <th style="min-width:90px">آخر دخول</th>
          <th style="min-width:90px">الصلاحيات</th>
          <th style="width:100px;text-align:center">إجراءات</th>
        </tr>
      </thead>
      <tbody id="mgr-tbody">
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- Invites List --}}
<div class="panel" style="margin-top:24px">
  <div class="panel-header" style="display:flex;align-items:center;justify-content:space-between">
    <div class="panel-title">📧 دعوات التسجيل لمدراء الفروع</div>
    <button class="btn btn-primary btn-sm" onclick="openModal('modal-add-invite')">+ دعوة جديدة</button>
  </div>
  <div style="padding:10px 16px;font-size:12px;color:var(--mu);border-bottom:1px solid var(--brd1);line-height:1.7">
    📌 أضف إيميل مدير الفرع — سيصله بريد إلكتروني فوراً ويتمكن من التسجيل بنفسه.
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>البريد الإلكتروني</th><th>الفرع</th><th>الدور</th>
          <th>ملاحظة</th><th>الحالة</th><th>تاريخ الإضافة</th><th>إجراء</th>
        </tr>
      </thead>
      <tbody id="inv-tbody">
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══ Modal: Create Manager ══ --}}
<div class="modal-overlay" id="modal-add-mgr">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title">👤 إنشاء حساب مدير جديد</div>
      <button class="modal-close" onclick="closeModal('modal-add-mgr')">✕</button>
    </div>
    <div class="modal-body">
      <div id="mgr-err" class="alert alert-error"></div>
      <div id="mgr-ok"  class="alert alert-success"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الاسم الكامل *</label>
          <input type="text"  id="mg-name"  class="form-control" placeholder="Manager Name">
        </div>
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني *</label>
          <input type="email" id="mg-email" class="form-control" placeholder="manager@wafragulf.com">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم التليفون</label>
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
          <label class="form-label">الفرع المسؤول عنه *</label>
          <select id="mg-branch" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">كلمة مرور مؤقتة (فارغة = توليد تلقائي)</label>
          <input type="password" id="mg-pw" class="form-control" placeholder="—">
        </div>
        <div class="form-group">
          <label class="form-label">الدور</label>
          <select id="mg-role" class="form-control">
            <option value="branch_manager">🏢 مدير فرع</option>
            <option value="viewer">👁 مشاهد</option>
          </select>
        </div>
      </div>
      <div class="form-section-title" style="margin-top:14px">🔐 الصلاحيات</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <button class="btn btn-ghost btn-sm" onclick="selectAllPerms(true,'add')">✅ تحديد الكل</button>
        <button class="btn btn-ghost btn-sm" onclick="selectAllPerms(false,'add')">☐ إلغاء الكل</button>
      </div>
      <div id="perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   onclick="closeModal('modal-add-mgr')">إلغاء</button>
      <button class="btn btn-primary" id="btn-create-mgr" onclick="createManager()">📧 إنشاء وإرسال بيانات الدخول</button>
    </div>
  </div>
</div>

{{-- ══ Modal: Edit Manager ══ --}}
<div class="modal-overlay" id="modal-edit-mgr">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title">✏️ تعديل بيانات المدير</div>
      <button class="modal-close" onclick="closeModal('modal-edit-mgr')">✕</button>
    </div>
    <div class="modal-body">
      <div id="edit-mgr-err" class="alert alert-error"></div>
      <div id="edit-mgr-ok"  class="alert alert-success"></div>
      <input type="hidden" id="edit-mgr-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الاسم الكامل *</label>
          <input type="text" id="edit-mg-name" class="form-control">
        </div>
        <div class="form-group">
          <label class="form-label">رقم التليفون</label>
          <input type="tel" id="edit-mg-phone" class="form-control" dir="ltr">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الفرع *</label>
          <select id="edit-mg-branch" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">الحالة</label>
          <select id="edit-mg-active" class="form-control">
            <option value="1">✅ نشط</option>
            <option value="0">🚫 معطّل</option>
          </select>
        </div>
      </div>
      <div class="form-section-title" style="margin-top:14px">🔐 الصلاحيات</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <button class="btn btn-ghost btn-sm" onclick="selectAllPerms(true,'edit')">✅ تحديد الكل</button>
        <button class="btn btn-ghost btn-sm" onclick="selectAllPerms(false,'edit')">☐ إلغاء الكل</button>
      </div>
      <div id="edit-perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   onclick="closeModal('modal-edit-mgr')">إلغاء</button>
      <button class="btn btn-primary" id="btn-save-mgr" onclick="saveManager()">💾 حفظ التعديلات</button>
    </div>
  </div>
</div>

{{-- ══ Modal: Add Invite ══ --}}
<div class="modal-overlay" id="modal-add-invite">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">📧 دعوة مدير فرع للتسجيل</div>
      <button class="modal-close" onclick="closeModal('modal-add-invite')">✕</button>
    </div>
    <div class="modal-body">
      <div style="background:rgba(26,173,186,.07);border:1px solid rgba(26,173,186,.2);border-radius:10px;padding:11px 14px;font-size:12px;color:var(--mu);margin-bottom:14px;line-height:1.8">
        📌 سيُرسل النظام <strong style="color:var(--pri2)">بريد إلكتروني تلقائياً</strong> لمدير الفرع يُعلمه بأن إيميله تمت إضافته وأنه يستطيع التسجيل الآن.
      </div>
      <div id="inv-err-modal" class="alert alert-error"></div>
      <div id="inv-ok-modal"  class="alert alert-success"></div>
      <div class="form-group">
        <label class="form-label">البريد الإلكتروني *</label>
        <input type="email" id="inv-email-input" class="form-control" placeholder="manager@example.com">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الفرع (اختياري)</label>
          <select id="inv-branch-input" class="form-control">
            <option value="">— بدون فرع محدد —</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">الدور</label>
          <select id="inv-role-input" class="form-control">
            <option value="branch_manager">🏢 مدير فرع</option>
            <option value="viewer">👁 مشاهد</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">ملاحظة (اختياري)</label>
        <input type="text" id="inv-note-input" class="form-control" placeholder="مثال: مدير فرع الرياض">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   onclick="closeModal('modal-add-invite')">إلغاء</button>
      <button class="btn btn-primary" onclick="addInvite()">📧 إضافة الدعوة وإرسال البريد</button>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
const PERMS = [
  {id:'dashboard',   ar:'لوحة المتابعة'},
  {id:'cards',       ar:'كروت العمولات'},
  {id:'modified',    ar:'الحسابات المعدّلة'},
  {id:'reports',     ar:'التقارير'},
  {id:'create_card', ar:'إنشاء كرت'},
  {id:'edit_card',   ar:'تعديل الحسابات'},
  {id:'employees',   ar:'إدارة الموظفين'},
  {id:'import',      ar:'استيراد بيانات'},
  {id:'export',      ar:'تصدير البيانات'},
];
const ROLE_AR = { branch_manager:'🏢 مدير فرع', viewer:'👁 مشاهد' };

// ── Build perm grid (add / edit) ──────────────────────────
function buildPermGrid(containerId, prefix) {
  document.getElementById(containerId).innerHTML = PERMS.map(p => `
    <div class="panel" style="padding:8px 10px;display:flex;align-items:center;gap:8px;cursor:pointer;background:rgba(46,134,171,.1);border-color:rgba(46,134,171,.3)"
         id="${prefix}pit-${p.id}" onclick="togPerm('${p.id}','${prefix}')">
      <div style="width:16px;height:16px;border-radius:4px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:10px;flex-shrink:0"
           id="${prefix}pchk-${p.id}">✓</div>
      <div style="font-size:11px;font-weight:600">${p.ar}</div>
    </div>`).join('');
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

// ── Init ──────────────────────────────────────────────────
async function init() {
  buildPermGrid('perm-grid',      '');
  buildPermGrid('edit-perm-grid', 'e-');
  await Promise.all([loadManagers(), loadBranches(), loadInvites()]);
}

async function loadManagers() {
  const r = await api('GET', '/managers');
  if (!r.success) return;
  document.getElementById('mgr-count').textContent = r.data.length + ' مدير';
  document.getElementById('mgr-tbody').innerHTML = r.data.length
    ? r.data.map(m => `
      <tr style="${!m.is_active ? 'opacity:.55' : ''}">
        <td>
          <div class="mgr-name-cell">${esc(m.name)}</div>
          <div class="mgr-email" title="${esc(m.email)}">${esc(m.email)}</div>
          ${m.phone ? `<div class="mgr-phone">${esc(m.phone)}</div>` : ''}
        </td>
        <td style="font-size:12px">${esc(m.branch?.name_ar || '—')}</td>
        <td><span class="badge badge-blue" style="font-size:10px">${esc(ROLE_AR[m.role] || m.role)}</span></td>
        <td><span class="badge ${m.is_active ? 'badge-green' : 'badge-red'}" style="font-size:10px">${m.is_active ? '● نشط' : '○ معطّل'}</span></td>
        <td style="font-size:10px;color:var(--mu)">${m.last_login ? esc(m.last_login.slice(0,10)) : '<span style="color:var(--mu)">—</span>'}</td>
        <td>
          <div style="display:flex;flex-wrap:wrap;gap:3px">
            ${(m.permissions||[]).slice(0,3).map(p => `<span class="perm-chip">${esc(p)}</span>`).join('')}
            ${(m.permissions||[]).length > 3 ? `<span class="perm-chip">+${(m.permissions||[]).length - 3}</span>` : ''}
          </div>
        </td>
        <td>
          <div style="display:flex;gap:4px;justify-content:center">
            <button class="icon-btn" onclick="editMgr(${m.id})" title="تعديل">✏️</button>
            <button class="icon-btn warn" onclick="resetPw(${m.id},'${esc(m.name)}')" title="إعادة تعيين كلمة المرور">🔑</button>
            <button class="icon-btn ${m.is_active ? 'danger' : 'success-btn'}" onclick="toggleMgr(${m.id},${m.is_active},'${esc(m.name)}')" title="${m.is_active ? 'تعطيل المدير' : 'تفعيل المدير'}">${m.is_active ? '🚫' : '✅'}</button>
          </div>
        </td>
      </tr>`).join('')
    : '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا يوجد مديرون بعد</td></tr>';
}

async function loadBranches() {
  const r = await api('GET', '/branches');
  if (!r.success) return;
  _branchOpts = r.data;
  const opt = r.data.map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
  document.getElementById('mg-branch').innerHTML        = opt;
  document.getElementById('edit-mg-branch').innerHTML   = opt;
  document.getElementById('inv-branch-input').innerHTML =
    '<option value="">— بدون فرع محدد —</option>' + opt;
}
let _branchOpts = [];

async function loadInvites() {
  const r = await api('GET', '/manager-invites');
  if (!r.success) return;
  document.getElementById('inv-tbody').innerHTML = r.data.length
    ? r.data.map(i => `
      <tr>
        <td style="font-weight:600">${esc(i.email)}</td>
        <td style="font-size:12px">${i.branch?.name_ar || '<span style="color:var(--mu)">—</span>'}</td>
        <td><span class="badge badge-blue" style="font-size:10px">${ROLE_AR[i.role] || i.role}</span></td>
        <td style="color:var(--mu);font-size:11px">${i.note || '—'}</td>
        <td>${i.is_pending
            ? '<span class="badge badge-yellow">⏳ بانتظار التسجيل</span>'
            : '<span class="badge badge-green">✅ مُستخدمة</span>'}</td>
        <td style="color:var(--mu);font-size:10px">${i.created_at?.slice(0,10) || '—'}</td>
        <td>${i.is_pending
            ? `<button class="icon-btn danger" title="حذف الدعوة" onclick="deleteInvite(${i.id},'${esc(i.email)}')">🗑</button>`
            : '<span style="color:var(--mu);font-size:10px">مُنتهية</span>'}</td>
      </tr>`).join('')
    : '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا توجد دعوات. أضف إيميل مدير فرع لبدء التسجيل.</td></tr>';
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
    errEl.textContent = 'يرجى ملء الاسم والإيميل'; errEl.classList.add('show'); return;
  }
  const perms = getSelectedPerms('');
  const btn = document.getElementById('btn-create-mgr');
  btn.disabled = true; btn.textContent = '⏳ جاري الإنشاء...';
  const r = await api('POST', '/managers', { name, email, phone, branch_id: parseInt(branch), role, password: document.getElementById('mg-pw').value || null, permissions: perms });
  btn.disabled = false; btn.textContent = '📧 إنشاء وإرسال بيانات الدخول';
  if (r.success) {
    const emailStatus = r.email_sent
      ? '<span style="color:var(--gr)">📧 تم إرسال بيانات الدخول للمدير بالبريد الإلكتروني</span>'
      : '<span style="color:var(--or)">⚠️ الحساب أُنشئ لكن البريد لم يُرسل — تحقق من إعدادات البريد</span>';
    okEl.innerHTML = `✅ تم إنشاء الحساب!<br>
      📧 <b>${esc(email)}</b><br>
      🔑 كلمة المرور: <b style="font-family:monospace;color:var(--pri2)">${esc(r.credentials?.temp_password || '—')}</b><br>
      ${emailStatus}`;
    okEl.classList.add('show');
    loadManagers();
    document.getElementById('mg-name').value = '';
    document.getElementById('mg-email').value = '';
    document.getElementById('mg-pw').value = '';
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || 'حدث خطأ'; errEl.classList.add('show');
  }
}

// ── Edit Manager ──────────────────────────────────────────
let _editMgrData = null;
async function editMgr(id) {
  const r = await api('GET', '/managers');
  if (!r.success) return;
  const m = r.data.find(x => x.id === id);
  if (!m) { toast('لم يتم العثور على المدير', 'error'); return; }
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
  if (!name) { errEl.textContent = 'أدخل الاسم'; errEl.classList.add('show'); return; }
  const btn = document.getElementById('btn-save-mgr');
  btn.disabled = true; btn.textContent = '⏳ جاري الحفظ...';
  const r = await api('PUT', `/managers/${id}`, { name, phone, branch_id: parseInt(branch), is_active: active, permissions: perms });
  btn.disabled = false; btn.textContent = '💾 حفظ التعديلات';
  if (r.success) {
    okEl.textContent = '✅ تم حفظ التعديلات بنجاح'; okEl.classList.add('show');
    loadManagers();
    setTimeout(() => closeModal('modal-edit-mgr'), 1200);
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || 'حدث خطأ'; errEl.classList.add('show');
  }
}

// ── Toggle active ─────────────────────────────────────────
async function toggleMgr(id, currentlyActive, name) {
  const action = currentlyActive ? 'تعطيل' : 'تفعيل';
  if (!confirm(`${action} المدير: ${name}?`)) return;
  const r = await api('PUT', `/managers/${id}`, { is_active: !currentlyActive });
  if (r.success) { toast(`تم ${action} المدير بنجاح`, 'success'); loadManagers(); }
  else toast(r.message || 'حدث خطأ', 'error');
}

// ── Reset Password ────────────────────────────────────────
async function resetPw(id, name) {
  if (!confirm('إعادة تعيين كلمة مرور: ' + name + '?')) return;
  const r = await api('POST', `/managers/${id}/reset-password`);
  if (r.success) {
    const emailNote = r.email_sent ? ' (تم الإرسال بالبريد)' : ' (البريد لم يُرسل)';
    toast(`كلمة المرور الجديدة: ${r.new_password}${emailNote}`, 'info');
  } else toast(r.message || 'حدث خطأ', 'error');
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
  if (!email) { errEl.textContent = 'أدخل البريد الإلكتروني'; errEl.classList.add('show'); return; }
  const r = await api('POST', '/manager-invites', { email, branch_id: branchId ? parseInt(branchId) : null, role, note: note || null });
  if (r.success) {
    const emailNote = r.email_sent
      ? '<br><span style="color:var(--gr)">📧 تم إرسال بريد إلكتروني للمدير</span>'
      : '<br><span style="color:var(--or)">⚠️ الدعوة أُضيفت لكن البريد لم يُرسل</span>';
    okEl.innerHTML = `✅ تمت إضافة <b>${esc(email)}</b> للقائمة.${emailNote}`;
    okEl.classList.add('show');
    document.getElementById('inv-email-input').value = '';
    document.getElementById('inv-note-input').value  = '';
    loadInvites();
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || 'حدث خطأ'; errEl.classList.add('show');
  }
}

// ── Delete Invite ─────────────────────────────────────────
async function deleteInvite(id, email) {
  if (!confirm(`حذف الدعوة لـ ${email}؟`)) return;
  const r = await api('DELETE', `/manager-invites/${id}`);
  if (r.success) { toast('تم حذف الدعوة', 'success'); loadInvites(); }
  else toast(r.message || 'حدث خطأ', 'error');
}

init();
</script>
@endpush
