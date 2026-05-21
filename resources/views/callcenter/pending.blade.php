@extends('layouts.app')
@section('title', 'كروت CC الواردة')
@section('page-title', 'كروت CC الواردة للفرع')

@section('content')
<style>
/* ── CC Source badge ── */
.cc-source-badge {
  display:inline-flex;align-items:center;gap:4px;
  background:linear-gradient(135deg,#7b68ee,#5f4fcf);
  color:#fff;font-size:10px;font-weight:700;
  padding:2px 9px;border-radius:20px;letter-spacing:.4px;vertical-align:middle;
}
/* ── Status chips ── */
.cc-chip{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
.cc-chip.branch_pending{background:#cff4fc;color:#055160}
.cc-chip.accepted      {background:#d1e7dd;color:#0a5c36}
/* ── CC row highlight — purple left border ── */
.cc-row td:first-child { border-right:4px solid #7b68ee !important; }
.cc-row { background:linear-gradient(90deg,rgba(123,104,238,.05),transparent 60%) !important; }
.cc-row:hover { background:linear-gradient(90deg,rgba(123,104,238,.11),transparent 60%) !important; }
/* ── Complete form ── */
.complete-form { background:var(--surface2);border-radius:12px;padding:16px;margin-top:8px;border:1px solid var(--border) }
.cc-limit-bar  { height:6px;border-radius:3px;background:#e9ecef;margin-top:6px;overflow:hidden }
.cc-limit-fill { height:100%;border-radius:3px;transition:width .3s,background .3s }
</style>

<div class="panel" style="max-width:1000px">
  <div class="panel-header">
    <div class="panel-title">
      📩 كروت CC الواردة
      <span id="pending-count" style="background:#7b68ee;color:#fff;border-radius:20px;padding:2px 10px;font-size:12px;margin-right:8px">0</span>
    </div>
    <button class="btn btn-ghost btn-sm" onclick="loadPending()">🔄 تحديث</button>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    <!-- Commission limit notice -->
    <div style="background:rgba(123,104,238,.07);border:1px solid rgba(123,104,238,.25);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
      <span style="font-size:20px">⚠️</span>
      <div>
        <strong style="color:#7b68ee">حد عمولات كروت CC:</strong>
        <span style="font-size:13px"> عمولة البروكر + عمولة المسوّق لا يجب أن تتجاوز <strong>5$ / lot</strong></span>
        <span style="font-size:11px;color:var(--text2);display:block;margin-top:2px">سيتم رفض الحفظ إذا تجاوز مجموعهما 5$</span>
      </div>
    </div>

    <div id="pending-list"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
async function loadPending() {
  const r = await api('GET', '/cc/pending');
  const container = document.getElementById('pending-list');
  document.getElementById('pending-count').textContent = r.count ?? 0;

  if (!r.success || !r.data.length) {
    container.innerHTML = `
      <div style="text-align:center;padding:40px;color:var(--text2)">
        <div style="font-size:40px;margin-bottom:8px">📭</div>
        لا توجد كروت CC واردة حالياً
      </div>`;
    return;
  }

  container.innerHTML = r.data.map(c => cardHtml(c)).join('');
}

function cardHtml(c) {
  const statusLabel = c.cc_status === 'branch_pending' ? '📩 بانتظار القرار' : '✅ مقبول — أكمل البيانات';
  const statusClass = c.cc_status;

  return `
  <div id="card-${c.id}" style="border:1px solid var(--border);border-right:4px solid #7b68ee;border-radius:12px;margin-bottom:14px;overflow:hidden">
    <!-- Card Header -->
    <div style="background:var(--surface2);padding:14px 16px;display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between">
      <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <span class="cc-source-badge">📞 CC</span>
        <strong style="font-size:16px">${c.account_number}</strong>
        <span style="color:var(--text2);font-size:13px">${c.month}</span>
        <span class="cc-chip ${statusClass}">${statusLabel}</span>
      </div>
      <div style="font-size:12px;color:var(--text2)">
        من: <strong>${c.cc_branch?.name_ar ?? '—'}</strong>
        &nbsp;|&nbsp; موظف: <strong>${c.cc_agent?.name ?? '—'}</strong>
        &nbsp;|&nbsp; عمولة موظف CC: <strong>${c.cc_agent_commission}$</strong>
      </div>
    </div>

    <!-- Card Actions -->
    <div style="padding:14px 16px">
      ${c.cc_status === 'branch_pending' ? `
        <div style="display:flex;gap:10px;margin-bottom:12px">
          <button class="btn btn-primary btn-sm" onclick="acceptCard(${c.id})">✅ قبول الكرت</button>
          <button class="btn btn-danger btn-sm" onclick="openRejectModal(${c.id})">❌ رفض</button>
        </div>
      ` : ''}

      ${c.cc_status === 'accepted' ? completeFormHtml(c) : ''}
    </div>
  </div>`;
}

function completeFormHtml(c) {
  return `
  <div class="complete-form" id="cf-${c.id}">
    <div style="font-size:13px;font-weight:700;margin-bottom:12px;color:var(--pri2)">📋 استكمال بيانات الكرت</div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">البروكر *</label>
        <select id="cf-broker-${c.id}" class="form-control broker-sel" data-card="${c.id}" onchange="checkLimit(${c.id})"></select>
      </div>
      <div class="form-group">
        <label class="form-label">عمولة البروكر ($/lot)</label>
        <input type="number" id="cf-bcomm-${c.id}" class="form-control" value="2.5" min="0" max="5" step="0.5" oninput="checkLimit(${c.id})">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">المسوّق</label>
        <select id="cf-mktr-${c.id}" class="form-control" onchange="checkLimit(${c.id})"></select>
      </div>
      <div class="form-group">
        <label class="form-label">عمولة المسوّق ($/lot)</label>
        <input type="number" id="cf-mcomm-${c.id}" class="form-control" value="2.5" min="0" max="5" step="0.5" oninput="checkLimit(${c.id})">
      </div>
    </div>

    <!-- Commission limit progress bar -->
    <div style="margin-bottom:12px">
      <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px">
        <span>إجمالي العمولات (بروكر + مسوّق)</span>
        <span id="cf-total-${c.id}" style="font-weight:700">5.0$ / 5.0$</span>
      </div>
      <div class="cc-limit-bar"><div id="cf-bar-${c.id}" class="cc-limit-fill" style="width:100%;background:#198754"></div></div>
      <div id="cf-warn-${c.id}" style="font-size:11px;color:#dc3545;margin-top:4px;display:none">⛔ يتجاوز الحد المسموح (5$)</div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label class="form-label">الإيداع الأولي ($) *</label>
        <input type="number" id="cf-dep-${c.id}" class="form-control" value="0" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">الإيداع الشهري ($)</label>
        <input type="number" id="cf-mon-${c.id}" class="form-control" value="0" min="0">
      </div>
    </div>
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Forex Commission ($/lot)</label>
        <input type="number" id="cf-forex-${c.id}" class="form-control" value="8" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">Futures Commission ($/lot)</label>
        <input type="number" id="cf-fut-${c.id}" class="form-control" value="8" min="0">
      </div>
    </div>
    <div id="cf-err-${c.id}" class="alert alert-error"></div>
    <button class="btn btn-primary" onclick="completeCard(${c.id})">🏁 إتمام الكرت</button>
  </div>`;
}

function checkLimit(cardId) {
  const bComm = parseFloat(document.getElementById(`cf-bcomm-${cardId}`)?.value ?? 0) || 0;
  const mComm = parseFloat(document.getElementById(`cf-mcomm-${cardId}`)?.value ?? 0) || 0;
  const total = bComm + mComm;
  const pct   = Math.min((total / 5) * 100, 100);
  const bar   = document.getElementById(`cf-bar-${cardId}`);
  const warn  = document.getElementById(`cf-warn-${cardId}`);
  const lbl   = document.getElementById(`cf-total-${cardId}`);
  if (!bar) return;
  bar.style.width    = pct + '%';
  bar.style.background = total > 5 ? '#dc3545' : total > 3.5 ? '#fd7e14' : '#198754';
  lbl.textContent    = total.toFixed(1) + '$ / 5.0$';
  lbl.style.color    = total > 5 ? '#dc3545' : 'inherit';
  warn.style.display = total > 5 ? 'block' : 'none';
}

async function acceptCard(id) {
  if (!confirm('قبول هذا الكرت؟')) return;
  const r = await api('PUT', `/cc/cards/${id}/accept`);
  if (r.success) { showAlert('ok', r.message); loadPending(); }
  else showAlert('err', r.message);
}

async function completeCard(id) {
  const bComm = parseFloat(document.getElementById(`cf-bcomm-${id}`).value) || 0;
  const mComm = parseFloat(document.getElementById(`cf-mcomm-${id}`)?.value ?? 0) || 0;

  // Client-side pre-check
  if (bComm + mComm > 5) {
    document.getElementById(`cf-err-${id}`).textContent =
      `⛔ عمولة البروكر (${bComm}$) + عمولة المسوّق (${mComm}$) = ${bComm+mComm}$ تتجاوز الحد المسموح (5$/lot)`;
    document.getElementById(`cf-err-${id}`).classList.add('show');
    return;
  }

  const payload = {
    broker_id:           parseInt(document.getElementById(`cf-broker-${id}`).value) || null,
    broker_commission:   bComm,
    marketer_id:         parseInt(document.getElementById(`cf-mktr-${id}`)?.value)  || null,
    marketer_commission: mComm,
    initial_deposit:     parseFloat(document.getElementById(`cf-dep-${id}`).value)  || 0,
    monthly_deposit:     parseFloat(document.getElementById(`cf-mon-${id}`).value)  || 0,
    forex_commission:    parseFloat(document.getElementById(`cf-forex-${id}`).value)|| 0,
    futures_commission:  parseFloat(document.getElementById(`cf-fut-${id}`).value)  || 0,
  };

  const r = await api('PUT', `/cc/cards/${id}/complete`, payload);
  const errEl = document.getElementById(`cf-err-${id}`);
  if (r.success) {
    errEl.classList.remove('show');
    showAlert('ok', r.message);
    loadPending();
  } else {
    const msg = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message;
    errEl.textContent = '❌ ' + msg;
    errEl.classList.add('show');
  }
}

// Reject modal
let rejectCardId = null;
function openRejectModal(id) {
  rejectCardId = id;
  document.getElementById('rj-reason').value = '';
  document.getElementById('rj-err').classList.remove('show');
  document.getElementById('reject-modal').style.display = 'flex';
}
async function confirmReject() {
  const reason = document.getElementById('rj-reason').value.trim();
  if (!reason || reason.length < 5) {
    document.getElementById('rj-err').textContent = 'يجب كتابة سبب الرفض (5 أحرف على الأقل)';
    document.getElementById('rj-err').classList.add('show');
    return;
  }
  const r = await api('PUT', `/cc/cards/${rejectCardId}/reject`, { reason });
  if (r.success) {
    document.getElementById('reject-modal').style.display = 'none';
    showAlert('ok', r.message);
    loadPending();
  } else {
    document.getElementById('rj-err').textContent = r.message;
    document.getElementById('rj-err').classList.add('show');
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

async function loadEmployees() {
  const r = await api('GET', '/employees?status=approved');
  if (!r.success) return;
  window._ccEmployees = r.data;
}

function populateEmployeeSelects(cardId) {
  const emps = window._ccEmployees ?? [];
  ['broker', 'mktr'].forEach(type => {
    const sel = document.getElementById(`cf-${type}-${cardId}`);
    if (!sel) return;
    sel.innerHTML = '<option value="">— لا يوجد —</option>';
    emps.forEach(e => {
      const o = document.createElement('option');
      o.value = e.id;
      o.textContent = e.name + (e.role==='external'?' 🌐':e.role==='marketing'?' 📢':' 🏦');
      sel.appendChild(o);
    });
  });
  checkLimit(cardId);
}

// After DOM is ready, populate employee selects for accepted cards
async function init() {
  await loadEmployees();
  await loadPending();
  // For any accepted cards that rendered complete forms:
  document.querySelectorAll('.broker-sel').forEach(sel => {
    const cid = sel.dataset.card;
    populateEmployeeSelects(cid);
  });
}

// Override loadPending to also populate selects after render
const _origLoad = loadPending;
loadPending = async function() {
  const r = await api('GET', '/cc/pending');
  const container = document.getElementById('pending-list');
  document.getElementById('pending-count').textContent = r.count ?? 0;

  if (!r.success || !r.data.length) {
    container.innerHTML = `
      <div style="text-align:center;padding:40px;color:var(--text2)">
        <div style="font-size:40px;margin-bottom:8px">📭</div>
        لا توجد كروت CC واردة حالياً
      </div>`;
    return;
  }

  container.innerHTML = r.data.map(c => cardHtml(c)).join('');

  // Populate employee selects for accepted cards
  r.data.filter(c => c.cc_status === 'accepted').forEach(c => {
    populateEmployeeSelects(c.id);
  });
};

init();
</script>

<!-- Reject modal -->
<div id="reject-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:999;align-items:center;justify-content:center">
  <div style="max-width:460px;width:90%;background:var(--surface);border-radius:16px;padding:28px">
    <h3 style="margin:0 0 16px">❌ رفض الكرت</h3>
    <div class="form-group">
      <label class="form-label">سبب الرفض *</label>
      <textarea id="rj-reason" class="form-control" rows="3" placeholder="اكتب سبب الرفض هنا..."></textarea>
    </div>
    <div style="display:flex;gap:10px;margin-top:12px">
      <button class="btn btn-danger" onclick="confirmReject()">❌ تأكيد الرفض</button>
      <button class="btn btn-ghost" onclick="document.getElementById('reject-modal').style.display='none'">إلغاء</button>
    </div>
    <div id="rj-err" class="alert alert-error" style="margin-top:10px"></div>
  </div>
</div>
@endpush
