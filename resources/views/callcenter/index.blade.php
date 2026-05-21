@extends('layouts.app')
@section('title', 'مركز الاتصال')
@section('page-title', 'مركز الاتصال — كروت CC')

@section('content')
<style>
/* ── CC Badge ── */
.cc-badge {
  display:inline-flex;align-items:center;gap:4px;
  background:linear-gradient(135deg,#7b68ee,#5f4fcf);
  color:#fff;font-size:10px;font-weight:700;
  padding:2px 8px;border-radius:20px;letter-spacing:.4px;
}
/* ── Status chips ── */
.cc-chip{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap}
.cc-chip.cc_pending    {background:#fff3cd;color:#856404}
.cc-chip.branch_pending{background:#cff4fc;color:#055160}
.cc-chip.accepted      {background:#d1e7dd;color:#0a5c36}
.cc-chip.completed     {background:#d1fae5;color:#065f46}
.cc-chip.rejected      {background:#f8d7da;color:#842029}
/* ── CC card row highlight ── */
.cc-row td{background:linear-gradient(90deg,rgba(123,104,238,.06),transparent 70%) !important}
.cc-row:hover td{background:linear-gradient(90deg,rgba(123,104,238,.12),rgba(123,104,238,.04) 70%) !important}
/* ── Limit hint ── */
.limit-hint{font-size:11px;color:#7b68ee;background:rgba(123,104,238,.07);
  border:1px solid rgba(123,104,238,.2);border-radius:8px;padding:6px 12px;margin-bottom:10px}
</style>

<div class="panel" style="max-width:960px">
  <div class="panel-header">
    <div class="panel-title">📞 مركز الاتصال — كروت CC</div>
    <button class="btn btn-primary btn-sm" onclick="openCreateModal()">➕ كرت جديد</button>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    <!-- Filters -->
    <div class="form-row" style="margin-bottom:12px">
      <div class="form-group" style="flex:1">
        <select id="fl-status" class="form-control" onchange="loadCards()">
          <option value="">— كل الحالات —</option>
          <option value="cc_pending">📝 مسودة (cc_pending)</option>
          <option value="branch_pending">📩 مُرسل للفرع</option>
          <option value="accepted">✅ مقبول</option>
          <option value="completed">🏁 مكتمل</option>
          <option value="rejected">❌ مرفوض</option>
        </select>
      </div>
      <div class="form-group" style="flex:0 0 auto">
        <button class="btn btn-ghost btn-sm" onclick="loadCards()">🔄 تحديث</button>
      </div>
    </div>

    <!-- Cards Table -->
    <div style="overflow-x:auto">
      <table class="data-table" id="cc-table">
        <thead>
          <tr>
            <th>#</th>
            <th>رقم الحساب</th>
            <th>الشهر</th>
            <th>الفرع المستهدف</th>
            <th>الموظف</th>
            <th>الحالة</th>
            <th>الإجراء</th>
          </tr>
        </thead>
        <tbody id="cc-tbody">
          <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text2)">⏳ جاري التحميل...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Create Card Modal -->
<div id="create-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;overflow-y:auto">
  <div style="max-width:580px;margin:40px auto;background:var(--surface);border-radius:16px;padding:28px">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px">
      <h3 style="margin:0">📞 كرت CC جديد</h3>
      <button onclick="closeCreateModal()" style="background:none;border:none;font-size:20px;cursor:pointer;color:var(--text2)">✕</button>
    </div>

    <div class="limit-hint">
      ⚠️ تنبيه: عمولة البروكر + عمولة المسوّق يجب <strong>ألا تتجاوز 5$/lot</strong> عند إتمام الكرت من الفرع.
    </div>

    <div class="form-group">
      <label class="form-label">رقم الحساب *</label>
      <input type="text" id="nc-ac" class="form-control" placeholder="719750">
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">الشهر *</label>
        <select id="nc-month" class="form-control"></select>
      </div>
      <div class="form-group">
        <label class="form-label">الفرع المستهدف *</label>
        <select id="nc-branch" class="form-control"></select>
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">موظف CC *</label>
        <select id="nc-agent" class="form-control"></select>
      </div>
      <div class="form-group">
        <label class="form-label">نوع الحساب</label>
        <select id="nc-kind" class="form-control">
          <option value="new">New — جديد</option>
          <option value="sub">Sub — فرعي</option>
        </select>
      </div>
    </div>
    <div class="form-group">
      <label class="form-label">ملاحظات</label>
      <input type="text" id="nc-notes" class="form-control" placeholder="اختياري">
    </div>
    <div style="display:flex;gap:10px;margin-top:16px">
      <button class="btn btn-primary" onclick="createCard()">💾 حفظ كمسودة</button>
      <button class="btn btn-ghost" onclick="closeCreateModal()">إلغاء</button>
    </div>
    <div id="nc-err" class="alert alert-error" style="margin-top:12px"></div>
  </div>
</div>

<!-- Reject Modal -->
<div id="reject-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999">
  <div style="max-width:460px;margin:120px auto;background:var(--surface);border-radius:16px;padding:28px">
    <h3 style="margin:0 0 16px">❌ رفض الكرت</h3>
    <input type="hidden" id="rj-id">
    <div class="form-group">
      <label class="form-label">سبب الرفض *</label>
      <textarea id="rj-reason" class="form-control" rows="3" placeholder="اكتب سبب الرفض هنا..."></textarea>
    </div>
    <div style="display:flex;gap:10px;margin-top:12px">
      <button class="btn btn-danger" onclick="confirmReject()">❌ تأكيد الرفض</button>
      <button class="btn btn-ghost" onclick="document.getElementById('reject-modal').style.display='none'">إلغاء</button>
    </div>
    <div id="rj-err" class="alert alert-error" style="margin-top:12px"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const STATUS_LABEL = {
  cc_pending:     '📝 مسودة',
  branch_pending: '📩 أُرسل للفرع',
  accepted:       '✅ مقبول',
  completed:      '🏁 مكتمل',
  rejected:       '❌ مرفوض',
};

async function loadCards() {
  const status = document.getElementById('fl-status').value;
  const params = new URLSearchParams();
  if (status) params.set('cc_status', status);
  const r = await api('GET', '/cc/sent?' + params);
  const tbody = document.getElementById('cc-tbody');

  if (!r.success || !r.data.data.length) {
    tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--text2)">لا توجد كروت</td></tr>';
    return;
  }

  tbody.innerHTML = r.data.data.map(c => `
    <tr class="cc-row">
      <td>${c.id}</td>
      <td><strong>${c.account_number}</strong></td>
      <td>${c.month}</td>
      <td>${c.branch?.name_ar ?? '—'}</td>
      <td>${c.cc_agent?.name ?? '—'}</td>
      <td><span class="cc-chip ${c.cc_status}">${STATUS_LABEL[c.cc_status] ?? c.cc_status}</span>
        ${c.cc_rejection_reason ? `<div style="font-size:10px;color:#842029;margin-top:3px">سبب: ${c.cc_rejection_reason}</div>` : ''}
      </td>
      <td>${actionButtons(c)}</td>
    </tr>
  `).join('');
}

function actionButtons(c) {
  if (c.cc_status === 'cc_pending') {
    return `<button class="btn btn-primary btn-sm" onclick="sendCard(${c.id})">📤 إرسال للفرع</button>`;
  }
  if (c.cc_status === 'branch_pending') {
    return `<span style="font-size:11px;color:var(--text2)">⏳ بانتظار الفرع</span>`;
  }
  if (c.cc_status === 'accepted') {
    return `<span style="font-size:11px;color:#0a5c36">🔄 الفرع يستكمل البيانات</span>`;
  }
  if (c.cc_status === 'rejected') {
    return `<button class="btn btn-ghost btn-sm" onclick="resendCard(${c.id})">🔁 إعادة إرسال</button>`;
  }
  return '—';
}

async function sendCard(id) {
  if (!confirm('إرسال هذا الكرت للفرع؟')) return;
  const r = await api('POST', `/cc/cards/${id}/send`);
  showAlert(r.success ? 'ok' : 'err', r.message);
  if (r.success) loadCards();
}

async function resendCard(id) {
  // Reset to cc_pending then send — handled by backend send() which checks cc_pending only
  // For UX: tell user they need to re-create or we could allow re-send of rejected
  showAlert('err', 'يمكنك إنشاء كرت جديد بنفس الحساب للشهر التالي، أو راجع بيانات هذا الكرت.');
}

async function createCard() {
  const ac    = document.getElementById('nc-ac').value.trim();
  const month = document.getElementById('nc-month').value;
  const branch= document.getElementById('nc-branch').value;
  const agent = document.getElementById('nc-agent').value;

  if (!ac || !month || !branch || !agent) {
    document.getElementById('nc-err').textContent = '⚠️ يرجى ملء جميع الحقول المطلوبة';
    document.getElementById('nc-err').classList.add('show');
    return;
  }

  // Compute month_date from selected month string
  const parsed = new Date(month + ' 1');
  const monthDate = parsed.getFullYear() + '-'
    + String(parsed.getMonth()+1).padStart(2,'0') + '-01';

  const r = await api('POST', '/cc/cards', {
    account_number:   ac,
    month:            month,
    month_date:       monthDate,
    target_branch_id: parseInt(branch),
    cc_agent_id:      parseInt(agent),
    account_kind:     document.getElementById('nc-kind').value,
    notes:            document.getElementById('nc-notes').value || null,
  });

  const errEl = document.getElementById('nc-err');
  if (r.success) {
    errEl.classList.remove('show');
    closeCreateModal();
    showAlert('ok', '✅ ' + r.message);
    loadCards();
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message;
    errEl.textContent = '❌ ' + msg;
    errEl.classList.add('show');
  }
}

function openCreateModal() {
  document.getElementById('nc-err').classList.remove('show');
  document.getElementById('create-modal').style.display = 'block';
}
function closeCreateModal() {
  document.getElementById('create-modal').style.display = 'none';
}

function showAlert(type, msg) {
  const e = document.getElementById('alert-err');
  const o = document.getElementById('alert-ok');
  e.classList.remove('show'); o.classList.remove('show');
  if (type === 'err') { e.textContent = msg; e.classList.add('show'); }
  else                { o.textContent = msg; o.classList.add('show'); }
  window.scrollTo(0, 0);
}

async function loadFormOptions() {
  const [employees, branches] = await Promise.all([
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
  ]);

  // Months
  const mSel = document.getElementById('nc-month');
  const now = new Date();
  for (let i = 0; i < 24; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const label = d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
    const o = document.createElement('option');
    o.value = o.textContent = label;
    mSel.appendChild(o);
  }

  // Branches
  if (branches.success) {
    const bSel = document.getElementById('nc-branch');
    bSel.innerHTML = '<option value="">— اختر الفرع —</option>';
    branches.data.forEach(b => {
      const o = document.createElement('option');
      o.value = b.id;
      o.textContent = b.name_ar + ' / ' + b.name_en;
      bSel.appendChild(o);
    });
  }

  // Employees (CC agents)
  if (employees.success) {
    const aSel = document.getElementById('nc-agent');
    aSel.innerHTML = '<option value="">— اختر الموظف —</option>';
    employees.data.forEach(e => {
      const o = document.createElement('option');
      o.value = e.id;
      o.textContent = e.name;
      aSel.appendChild(o);
    });
  }
}

loadFormOptions();
loadCards();
</script>
@endpush
