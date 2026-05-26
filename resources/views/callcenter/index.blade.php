@extends('layouts.app')
@section('title', 'مركز الاتصال')
@section('page-title', 'مركز الاتصال — كروت CC')

@section('topbar-actions')
<button class="tb-btn primary" onclick="openModal('modal-cc-create')">➕ كرت جديد</button>
@endsection

@section('content')
<style>
.cc-badge{display:inline-flex;align-items:center;gap:4px;background:linear-gradient(135deg,#7b68ee,#5f4fcf);color:#fff;font-size:10px;font-weight:700;padding:2px 8px;border-radius:20px;letter-spacing:.4px}
.cc-chip{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap}
.cc-chip.cc_pending    {background:#fff3cd;color:#856404}
.cc-chip.branch_pending{background:#cff4fc;color:#055160}
.cc-chip.accepted      {background:#d1e7dd;color:#0a5c36}
.cc-chip.completed     {background:#d1fae5;color:#065f46}
.cc-chip.rejected      {background:#f8d7da;color:#842029}
.cc-row td{background:linear-gradient(90deg,rgba(123,104,238,.06),transparent 70%) !important}
.cc-row:hover td{background:linear-gradient(90deg,rgba(123,104,238,.12),rgba(123,104,238,.04) 70%) !important}
.limit-hint{font-size:12px;color:#7b68ee;background:rgba(123,104,238,.07);border:1px solid rgba(123,104,238,.2);border-radius:8px;padding:8px 12px;margin-bottom:12px}
</style>

<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📞 مركز الاتصال — كروت CC</div>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    <!-- Filters -->
    <div class="form-row" style="margin-bottom:14px">
      <div class="form-group" style="flex:1">
        <select id="fl-status" class="form-control" onchange="loadCards()">
          <option value="">— كل الحالات —</option>
          <option value="cc_pending">📝 مسودة</option>
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

    <div style="overflow-x:auto">
      <table class="data-table" id="cc-table">
        <thead>
          <tr>
            <th>#</th>
            <th>رقم الحساب</th>
            <th>الشهر</th>
            <th>الفرع المستهدف</th>
            <th>موظف CC</th>
            <th>النوع</th>
            <th>الحالة</th>
            <th>الإجراء</th>
          </tr>
        </thead>
        <tbody id="cc-tbody">
          <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text2)">⏳ جاري التحميل...</td></tr>
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- ══ Modal: Create CC Card ══ --}}
<div class="modal-overlay" id="modal-cc-create">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title">📞 كرت CC جديد</div>
      <button class="modal-close" onclick="closeModal('modal-cc-create')">✕</button>
    </div>
    <div class="modal-body">
      <div class="limit-hint">⚠️ تنبيه: عمولة البروكر + عمولة المسوّق يجب <strong>ألا تتجاوز 5$/lot</strong> عند إتمام الكرت من الفرع.</div>

      <div id="nc-err" class="alert alert-error"></div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم الحساب *</label>
          <input type="text" id="nc-ac" class="form-control" placeholder="719750">
        </div>
        <div class="form-group">
          <label class="form-label">نوع الحساب</label>
          <select id="nc-kind" class="form-control">
            <option value="new">New — جديد</option>
            <option value="sub">Sub — فرعي</option>
          </select>
        </div>
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
          <label class="form-label">نوع الحساب (تصنيف)</label>
          <select id="nc-acc-type" class="form-control">
            <option value="">— اختياري —</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">حالة الحساب</label>
          <select id="nc-acc-status" class="form-control">
            <option value="">— اختياري —</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">نوع التداول</label>
          <select id="nc-trading" class="form-control">
            <option value="">— اختياري —</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">ملاحظات</label>
        <input type="text" id="nc-notes" class="form-control" placeholder="معلومات إضافية للفرع...">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-cc-create')">إلغاء</button>
      <button class="btn btn-primary" onclick="createCard()">💾 حفظ كمسودة</button>
    </div>
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

// ── Load cards list ───────────────────────────────────────────
async function loadCards() {
  const status = document.getElementById('fl-status').value;
  const params = new URLSearchParams();
  if (status) params.set('cc_status', status);
  const r = await api('GET', '/cc/sent?' + params);
  const tbody = document.getElementById('cc-tbody');

  if (!r.success || !r.data?.data?.length) {
    tbody.innerHTML = '<tr><td colspan="8" style="text-align:center;padding:30px;color:var(--text2)">لا توجد كروت</td></tr>';
    return;
  }

  tbody.innerHTML = r.data.data.map(c => `
    <tr class="cc-row">
      <td>${c.id}</td>
      <td><strong>${c.account_number}</strong></td>
      <td>${c.month}</td>
      <td>${c.branch?.name_ar ?? '—'}</td>
      <td>${c.cc_agent?.name ?? '—'}</td>
      <td><span style="font-size:11px;color:var(--mu)">${c.account_kind === 'sub' ? '🔀 Sub' : '🆕 New'}</span></td>
      <td>
        <span class="cc-chip ${c.cc_status}">${STATUS_LABEL[c.cc_status] ?? c.cc_status}</span>
        ${c.cc_rejection_reason
          ? `<div style="font-size:10px;color:#842029;margin-top:3px;max-width:180px">سبب: ${c.cc_rejection_reason}</div>`
          : ''}
      </td>
      <td>${actionButtons(c)}</td>
    </tr>`).join('');
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
    return `<button class="btn btn-primary btn-sm" onclick="resendCard(${c.id})">🔁 إعادة إرسال</button>`;
  }
  return '—';
}

// ── Send card to branch ───────────────────────────────────────
async function sendCard(id) {
  if (!confirm('إرسال هذا الكرت للفرع؟')) return;
  const r = await api('POST', `/cc/cards/${id}/send`);
  showAlert(r.success ? 'ok' : 'err', r.message);
  if (r.success) loadCards();
}

// ── Resend rejected card ──────────────────────────────────────
async function resendCard(id) {
  if (!confirm('إعادة إرسال هذا الكرت للفرع؟')) return;
  const r = await api('POST', `/cc/cards/${id}/resend`);
  showAlert(r.success ? 'ok' : 'err', r.message);
  if (r.success) loadCards();
}

// ── Create card ───────────────────────────────────────────────
async function createCard() {
  const ac     = document.getElementById('nc-ac').value.trim();
  const monthV = document.getElementById('nc-month').value;
  const branch = document.getElementById('nc-branch').value;
  const agent  = document.getElementById('nc-agent').value;
  const errEl  = document.getElementById('nc-err');
  errEl.classList.remove('show');

  if (!ac || !monthV || !branch || !agent) {
    errEl.textContent = '⚠️ يرجى ملء جميع الحقول المطلوبة (رقم الحساب، الشهر، الفرع، الموظف)';
    errEl.classList.add('show');
    return;
  }

  // Parse month safely: value is "MMM YYYY" e.g. "Jan 2024"
  const parts = monthV.split(' ');
  const monthMap = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
  const mm = String(monthMap[parts[0]] ?? 1).padStart(2,'0');
  const yyyy = parts[1] ?? new Date().getFullYear();
  const monthDate = `${yyyy}-${mm}-01`;

  const r = await api('POST', '/cc/cards', {
    account_number:    ac,
    month:             monthV,
    month_date:        monthDate,
    target_branch_id:  parseInt(branch),
    cc_agent_id:       parseInt(agent),
    account_kind:      document.getElementById('nc-kind').value,
    account_type_id:   parseInt(document.getElementById('nc-acc-type').value)   || null,
    account_status_id: parseInt(document.getElementById('nc-acc-status').value) || null,
    trading_type_id:   parseInt(document.getElementById('nc-trading').value)    || null,
    notes:             document.getElementById('nc-notes').value.trim() || null,
  });

  if (r.success) {
    errEl.classList.remove('show');
    closeModal('modal-cc-create');
    showAlert('ok', '✅ ' + r.message);
    loadCards();
    // Reset form
    ['nc-ac','nc-notes'].forEach(id => document.getElementById(id).value = '');
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message;
    errEl.textContent = '❌ ' + msg;
    errEl.classList.add('show');
  }
}

function showAlert(type, msg) {
  const e = document.getElementById('alert-err');
  const o = document.getElementById('alert-ok');
  e.classList.remove('show'); o.classList.remove('show');
  if (type === 'err') { e.textContent = msg; e.classList.add('show'); }
  else                { o.textContent = msg; o.classList.add('show'); }
  window.scrollTo(0, 0);
}

// ── Load form dropdowns ───────────────────────────────────────
async function loadFormOptions() {
  const [emps, branches, settings] = await Promise.all([
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
    api('GET', '/settings'),
  ]);

  // Months (past 24 months)
  const mSel = document.getElementById('nc-month');
  const now  = new Date();
  const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  for (let i = 0; i < 24; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const label = MONTHS[d.getMonth()] + ' ' + d.getFullYear();
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
      o.textContent = b.name_ar + (b.name_en ? ' / ' + b.name_en : '');
      bSel.appendChild(o);
    });
  }

  // Employees (CC agents)
  if (emps.success) {
    const aSel = document.getElementById('nc-agent');
    aSel.innerHTML = '<option value="">— اختر الموظف —</option>';
    emps.data.forEach(e => {
      const o = document.createElement('option');
      o.value = e.id;
      o.textContent = e.name;
      aSel.appendChild(o);
    });
  }

  // Settings dropdowns
  if (settings.success) {
    const fill = (selId, items) => {
      const sel = document.getElementById(selId);
      if (!sel) return;
      items?.forEach(item => {
        const o = document.createElement('option');
        o.value = item.id;
        o.textContent = (item.name_en || '') + (item.name_ar ? ' / ' + item.name_ar : '');
        sel.appendChild(o);
      });
    };
    fill('nc-acc-type',   settings.data?.account_types);
    fill('nc-acc-status', settings.data?.account_statuses);
    fill('nc-trading',    settings.data?.trading_types);
  }
}

loadFormOptions();
loadCards();
</script>
@endpush
