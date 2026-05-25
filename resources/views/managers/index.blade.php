@extends('layouts.app')
@section('title','المديرون')
@section('page-title','إدارة المديرين')
@section('topbar-actions')
<button class="tb-btn primary" onclick="openModal('modal-add-mgr')">👤 مدير جديد</button>
<button class="tb-btn" onclick="openModal('modal-add-invite')" style="margin-right:8px">📧 دعوة مدير فرع</button>
@endsection
@section('content')

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- Managers List                                              --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="panel" id="mgr-list">
  <div class="panel-header">
    <div class="panel-title">👤 قائمة المديرين</div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>الاسم</th><th>البريد</th><th>الفرع</th><th>الدور</th>
          <th>آخر دخول</th><th>الحالة</th><th>إجراءات</th>
        </tr>
      </thead>
      <tbody id="mgr-tbody">
        <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- Invites List                                               --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="panel" style="margin-top:24px">
  <div class="panel-header" style="display:flex;align-items:center;justify-content:space-between">
    <div class="panel-title">📧 دعوات التسجيل لمدراء الفروع</div>
    <button class="btn btn-primary btn-sm" onclick="openModal('modal-add-invite')">+ دعوة جديدة</button>
  </div>
  <div style="padding:12px 16px;font-size:12px;color:var(--mu);border-bottom:1px solid var(--brd1)">
    أضف إيميلات مدراء الفروع هنا. سيتمكنون من التسجيل بأنفسهم باستخدام هذه الإيميلات.
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

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- Modal: Create Manager (manual)                             --}}
{{-- ══════════════════════════════════════════════════════════ --}}
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
          <input type="text"  id="mg-name"   class="form-control" placeholder="Manager Name">
        </div>
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني *</label>
          <input type="email" id="mg-email"  class="form-control" placeholder="manager@wafragulf.com">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الفرع المسؤول عنه *</label>
          <select id="mg-branch" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">كلمة مرور مؤقتة (اتركها فارغة للتوليد التلقائي)</label>
          <input type="password" id="mg-pw" class="form-control" placeholder="—">
        </div>
      </div>
      <div class="form-section-title" style="margin-top:14px">🔐 الصلاحيات</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <button class="btn btn-ghost btn-sm" onclick="selectAllPerms(true)">✅ تحديد الكل</button>
        <button class="btn btn-ghost btn-sm" onclick="selectAllPerms(false)">☐ إلغاء الكل</button>
      </div>
      <div id="perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   onclick="closeModal('modal-add-mgr')">إلغاء</button>
      <button class="btn btn-primary" onclick="createManager()">📧 إنشاء وإرسال بيانات الدخول</button>
    </div>
  </div>
</div>

{{-- ══════════════════════════════════════════════════════════ --}}
{{-- Modal: Add Invite                                          --}}
{{-- ══════════════════════════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-add-invite">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">📧 دعوة مدير فرع للتسجيل</div>
      <button class="modal-close" onclick="closeModal('modal-add-invite')">✕</button>
    </div>
    <div class="modal-body">
      <div style="background:rgba(46,134,171,.08);border:1px solid rgba(46,134,171,.2);border-radius:10px;padding:12px 14px;font-size:12px;color:var(--mu);margin-bottom:16px;line-height:1.7">
        📌 أضف بريد مدير الفرع — سيتمكن من التسجيل بنفسه عبر صفحة الدخول باستخدام هذا الإيميل وتحديد اسمه وكلمة مروره.
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
            <option value="branch_manager">مدير فرع</option>
            <option value="viewer">مشاهد</option>
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
      <button class="btn btn-primary" onclick="addInvite()">📧 إضافة الدعوة</button>
    </div>
  </div>
</div>

@endsection
@push('scripts')
<script>
const PERMS = [
  {id:'dashboard',  ar:'لوحة المتابعة'},
  {id:'cards',      ar:'كروت العمولات'},
  {id:'modified',   ar:'الحسابات المعدّلة'},
  {id:'reports',    ar:'التقارير'},
  {id:'create_card',ar:'إنشاء كرت'},
  {id:'edit_card',  ar:'تعديل الحسابات'},
  {id:'employees',  ar:'إدارة الموظفين'},
  {id:'import',     ar:'استيراد بيانات'},
  {id:'export',     ar:'تصدير البيانات'},
];

const ROLE_AR = { branch_manager: 'مدير فرع', viewer: 'مشاهد' };

// ── Init ──────────────────────────────────────────────────────
async function init() {
  // Load managers
  const r = await api('GET', '/managers');
  if (r.success) {
    document.getElementById('mgr-tbody').innerHTML = r.data.map(m => `
      <tr>
        <td style="font-weight:700">${m.name}</td>
        <td style="color:var(--mu)">${m.email}</td>
        <td>${m.branch?.name_ar || '—'}</td>
        <td><span class="badge badge-blue">${ROLE_AR[m.role] || m.role}</span></td>
        <td style="color:var(--mu);font-size:11px">${m.last_login || 'لم يدخل بعد'}</td>
        <td><span class="badge ${m.is_active ? 'badge-green' : 'badge-red'}">${m.is_active ? 'نشط' : 'معطّل'}</span></td>
        <td><button class="btn btn-ghost btn-sm" onclick="resetPw(${m.id},'${m.name}')">🔑 تغيير كلمة المرور</button></td>
      </tr>`).join('') ||
      '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا يوجد مديرون</td></tr>';
  }

  // Load branches (for both modals)
  const br = await api('GET', '/branches');
  if (br.success) {
    const opt = br.data.map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
    document.getElementById('mg-branch').innerHTML = opt;
    document.getElementById('inv-branch-input').innerHTML =
      '<option value="">— بدون فرع محدد —</option>' + opt;
  }

  // Build perm grid
  document.getElementById('perm-grid').innerHTML = PERMS.map(p => `
    <div class="panel" style="padding:10px;display:flex;align-items:center;gap:8px;cursor:pointer;background:rgba(46,134,171,.1);border-color:rgba(46,134,171,.3)"
         id="pit-${p.id}" onclick="togPerm('${p.id}')">
      <div style="width:18px;height:18px;border-radius:5px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:11px"
           id="pchk-${p.id}">✓</div>
      <div style="font-size:12px;font-weight:600">${p.ar}</div>
    </div>`).join('');

  // Load invites
  loadInvites();
}

// ── Load Invites ──────────────────────────────────────────────
async function loadInvites() {
  const r = await api('GET', '/manager-invites');
  if (!r.success) return;
  document.getElementById('inv-tbody').innerHTML = r.data.length
    ? r.data.map(i => `
      <tr>
        <td style="font-weight:600">${i.email}</td>
        <td>${i.branch?.name_ar || '<span style="color:var(--mu)">—</span>'}</td>
        <td><span class="badge badge-blue">${ROLE_AR[i.role] || i.role}</span></td>
        <td style="color:var(--mu);font-size:12px">${i.note || '—'}</td>
        <td>${i.is_pending
            ? '<span class="badge badge-yellow">⏳ بانتظار التسجيل</span>'
            : '<span class="badge badge-green">✅ مُستخدمة</span>'}</td>
        <td style="color:var(--mu);font-size:11px">${i.created_at?.slice(0,10) || '—'}</td>
        <td>${i.is_pending
            ? `<button class="btn btn-ghost btn-sm" style="color:var(--re)" onclick="deleteInvite(${i.id},'${i.email}')">🗑 حذف</button>`
            : '<span style="color:var(--mu);font-size:11px">مُنتهية</span>'}</td>
      </tr>`).join('')
    : '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا توجد دعوات. أضف إيميل مدير فرع لبدء التسجيل.</td></tr>';
}

// ── Permissions helpers ───────────────────────────────────────
function togPerm(id) {
  const el  = document.getElementById('pit-' + id);
  const chk = document.getElementById('pchk-' + id);
  const on  = chk.textContent === '✓';
  chk.textContent    = on ? '' : '✓';
  el.style.background  = on ? 'var(--inp-bg)' : 'rgba(46,134,171,.1)';
  el.style.borderColor = on ? 'var(--brd1)'   : 'rgba(46,134,171,.3)';
}
function selectAllPerms(v) {
  PERMS.forEach(p => {
    const chk = document.getElementById('pchk-' + p.id);
    const el  = document.getElementById('pit-'  + p.id);
    chk.textContent    = v ? '✓' : '';
    el.style.background  = v ? 'rgba(46,134,171,.1)' : 'var(--inp-bg)';
    el.style.borderColor = v ? 'rgba(46,134,171,.3)' : 'var(--brd1)';
  });
}

// ── Create Manager (manual) ───────────────────────────────────
async function createManager() {
  const name   = document.getElementById('mg-name').value.trim();
  const email  = document.getElementById('mg-email').value.trim();
  const branch = document.getElementById('mg-branch').value;
  if (!name || !email) {
    document.getElementById('mgr-err').textContent = 'يرجى ملء الاسم والإيميل';
    document.getElementById('mgr-err').classList.add('show');
    return;
  }
  const perms = PERMS.filter(p => document.getElementById('pchk-' + p.id).textContent === '✓').map(p => p.id);
  const r = await api('POST', '/managers', {
    name, email,
    branch_id: parseInt(branch),
    password: document.getElementById('mg-pw').value || null,
    permissions: perms,
  });
  if (r.success) {
    document.getElementById('mgr-ok').innerHTML =
      `✅ تم الإنشاء!<br>📧 ${email}<br>🔑 <b style="font-family:monospace">${r.credentials.temp_password}</b>`;
    document.getElementById('mgr-ok').classList.add('show');
    init();
  } else {
    document.getElementById('mgr-err').textContent = r.message || 'حدث خطأ';
    document.getElementById('mgr-err').classList.add('show');
  }
}

// ── Add Invite ────────────────────────────────────────────────
async function addInvite() {
  const email    = document.getElementById('inv-email-input').value.trim();
  const branchId = document.getElementById('inv-branch-input').value || null;
  const role     = document.getElementById('inv-role-input').value;
  const note     = document.getElementById('inv-note-input').value.trim();
  const errEl    = document.getElementById('inv-err-modal');
  const okEl     = document.getElementById('inv-ok-modal');
  errEl.textContent = ''; errEl.classList.remove('show');
  okEl.classList.remove('show');

  if (!email) { errEl.textContent = 'أدخل البريد الإلكتروني'; errEl.classList.add('show'); return; }

  const r = await api('POST', '/manager-invites', {
    email,
    branch_id: branchId ? parseInt(branchId) : null,
    role,
    note: note || null,
  });

  if (r.success) {
    okEl.textContent = `✅ تمت إضافة ${email} للقائمة المسموح بها.`;
    okEl.classList.add('show');
    document.getElementById('inv-email-input').value = '';
    document.getElementById('inv-note-input').value  = '';
    loadInvites();
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' — ') : r.message;
    errEl.textContent = msg || 'حدث خطأ';
    errEl.classList.add('show');
  }
}

// ── Delete Invite ─────────────────────────────────────────────
async function deleteInvite(id, email) {
  if (!confirm(`حذف الدعوة لـ ${email}؟`)) return;
  const r = await api('DELETE', `/manager-invites/${id}`);
  if (r.success) { toast('تم حذف الدعوة', 'success'); loadInvites(); }
  else toast(r.message || 'حدث خطأ', 'error');
}

// ── Reset Password ────────────────────────────────────────────
async function resetPw(id, name) {
  if (!confirm('إعادة تعيين كلمة مرور: ' + name + '?')) return;
  const r = await api('POST', `/managers/${id}/reset-password`);
  if (r.success) toast(`كلمة المرور الجديدة: ${r.new_password}`, 'info');
  else toast(r.message || 'حدث خطأ', 'error');
}

init();
</script>
@endpush
