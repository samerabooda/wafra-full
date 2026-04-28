@extends('layouts.app')
@section('title','واردة من مركز الاتصال')
@section('page-title','واردة من مركز الاتصال')

@push('styles')
<style>
.cc-card-item{background:var(--bg);border:1px solid var(--brd1);border-radius:12px;padding:14px 16px;margin-bottom:10px;border-right:4px solid var(--pri)}
.cc-card-item.pending{border-right-color:var(--or)}
.cc-card-item.accepted{border-right-color:var(--pri)}
.cc-card-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px}
.cc-source-tag{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:700;background:rgba(29,158,117,.15);color:#0F6E56;border:1px solid rgba(29,158,117,.3)}
.cc-meta{display:flex;gap:16px;flex-wrap:wrap;font-size:12px;color:var(--mu);margin-bottom:12px}
.cc-meta span b{color:var(--tx);font-weight:600}
.comm-warning{background:rgba(224,80,80,.08);border:1px solid rgba(224,80,80,.25);border-radius:8px;padding:10px 12px;font-size:12px;color:var(--re);margin-bottom:10px}
.comm-warning.w1{border-color:rgba(186,117,23,.35);background:rgba(186,117,23,.08);color:var(--or)}
.comm-warning.w2{border-color:rgba(224,130,50,.4);background:rgba(224,130,50,.1);color:#d06010}
.comm-warning.blocked{border-color:var(--re);background:rgba(224,80,80,.12)}
</style>
@endpush

@section('content')

<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📬 حسابات واردة من مركز الاتصال — بانتظار إكمال بياناتها</div>
    <span id="pending-count" class="badge badge-orange">0</span>
  </div>
  <div id="pending-list" style="padding:12px 16px">
    <div class="empty-state">جارٍ التحميل...</div>
  </div>
</div>

{{-- ═══ MODAL: REJECT CARD ════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-reject">
  <div class="modal" style="max-width:420px">
    <div class="modal-header">
      <div class="modal-title">❌ رفض الحساب</div>
      <button class="modal-close" onclick="closeModal('modal-reject')">✕</button>
    </div>
    <div class="modal-body">
      <div class="form-group">
        <label class="form-label">سبب الرفض *</label>
        <textarea id="reject-reason" class="form-control" rows="3" placeholder="اكتب سبب الرفض ليصل لمركز الاتصال..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-reject')">إلغاء</button>
      <button class="btn btn-danger" onclick="confirmReject()" id="btn-reject-confirm">تأكيد الرفض</button>
    </div>
  </div>
</div>

{{-- ═══ MODAL: COMPLETE CARD ══════════════════════════════════ --}}
<div class="modal-overlay" id="modal-complete">
  <div class="modal" style="max-width:620px">
    <div class="modal-header">
      <div class="modal-title">✅ إكمال بيانات الحساب</div>
      <button class="modal-close" onclick="closeModal('modal-complete')">✕</button>
    </div>
    <div class="modal-body">
      <div id="complete-card-info" style="padding:10px;background:var(--bg2);border-radius:8px;margin-bottom:14px;font-size:13px"></div>
      <div id="complete-alert" style="display:none;margin-bottom:10px"></div>
      <div id="warning-box" style="display:none;margin-bottom:10px"></div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">البروكر *</label>
          <select id="comp-broker" class="form-control" onchange="calcTotal()"><option value="">اختر البروكر</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">عمولة البروكر ($/lot) *</label>
          <input type="number" id="comp-broker-comm" class="form-control" min="0" step="0.5" value="4" onchange="calcTotal()">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">المسوّق</label>
          <select id="comp-marketer" class="form-control" onchange="calcTotal()"><option value="">— اختياري —</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">عمولة المسوّق ($/lot)</label>
          <input type="number" id="comp-marketer-comm" class="form-control" min="0" step="0.5" value="0" onchange="calcTotal()">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الإيداع الأولي ($) *</label>
          <input type="number" id="comp-initial" class="form-control" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">الإيداع الشهري ($) *</label>
          <input type="number" id="comp-monthly" class="form-control" min="0">
        </div>
      </div>

      {{-- Commission summary ─────────── --}}
      <div style="background:var(--bg2);border-radius:8px;padding:12px;font-size:12px;margin-top:4px">
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px">
          <div>
            <div style="color:var(--mu);margin-bottom:2px">عمولة CC</div>
            <div class="mono" style="font-weight:700;color:var(--pri2)" id="disp-cc-comm">$0.00/lot</div>
          </div>
          <div>
            <div style="color:var(--mu);margin-bottom:2px">عمولة البروكر</div>
            <div class="mono" style="font-weight:700;color:var(--gr)" id="disp-broker-comm">$0.00/lot</div>
          </div>
          <div>
            <div style="color:var(--mu);margin-bottom:2px">الإجمالي</div>
            <div class="mono" style="font-weight:700" id="disp-total">$0.00/lot</div>
          </div>
        </div>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-complete')">إلغاء</button>
      <button class="btn btn-primary" onclick="submitComplete()" id="btn-complete">حفظ وإكمال ✓</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
let currentCardId   = null;
let currentCcComm   = 0;
let warningCount    = 0;
let pendingOverride = false;
const LIMIT_AMOUNT  = {{ \App\Models\Setting::commissionLimitAmount() }};
const LIMIT_ENABLED = {{ \App\Models\Setting::commissionLimitEnabled() ? 'true' : 'false' }};
const MAX_WARNINGS  = {{ \App\Models\Setting::commissionWarningCount() }};

async function loadPending() {
  const r = await api('GET', '/cc/pending');
  if (!r.success) return;
  const cards = r.data || [];
  document.getElementById('pending-count').textContent = cards.length;

  const list = document.getElementById('pending-list');
  if (!cards.length) {
    list.innerHTML = '<div class="empty-state" style="padding:30px 0">لا توجد حسابات واردة من مركز الاتصال</div>';
    return;
  }

  list.innerHTML = cards.map(c => `
    <div class="cc-card-item ${c.cc_status === 'branch_pending' ? 'pending' : c.cc_status}">
      <div class="cc-card-header">
        <div style="display:flex;align-items:center;gap:10px">
          <span class="ac-num">#${c.account_number}</span>
          <span style="font-size:13px;color:var(--mu)">${c.month}</span>
          <span class="cc-source-tag">📞 من CC</span>
        </div>
        <div style="display:flex;gap:6px">
          ${c.cc_status === 'branch_pending'
            ? `<button class="btn btn-sm btn-primary" onclick="acceptCard(${c.id})">✅ قبول</button>`
            : c.cc_status === 'accepted'
            ? `<button class="btn btn-sm btn-primary" onclick="openComplete(${c.id},${c.cc_agent_commission})">إكمال البيانات →</button>`
            : ''
          }
          <button class="btn btn-sm btn-danger" onclick="openReject(${c.id})">رفض</button>
        </div>
      </div>
      <div class="cc-meta">
        <span>الحالة: <b style="color:${c.cc_status==='branch_pending'?'var(--or)':c.cc_status==='accepted'?'var(--pri2)':'var(--gr)'}">${
          {branch_pending:'⏳ بانتظار ردك',accepted:'✅ مقبول — أكمل البيانات',rejected:'❌ مرفوض',completed:'✓ مكتمل'}[c.cc_status]||c.cc_status
        }</b></span>
        <span>موظف CC: <b>${c.cc_agent?.name || '—'}</b></span>
        <span>عمولة CC: <b class="mono" style="color:var(--pri2)">$${(+c.cc_agent_commission).toFixed(2)}/lot</b></span>
        <span>تاريخ الإرسال: <b>${new Date(c.created_at).toLocaleDateString('ar-SA')}</b></span>
        ${c.notes ? `<span>ملاحظات: <b>${c.notes}</b></span>` : ''}
      </div>
    </div>`).join('');
}

async function acceptCard(id) {
  const r = await api('PUT', `/cc/cards/${id}/accept`);
  if (r.success) { toast('تم القبول — أكمل بيانات الحساب الآن', 'success'); loadPending(); }
  else toast(r.message||'خطأ', 'error');
}

// ── Reject ────────────────────────────────────────────────
function openReject(id) {
  currentCardId = id;
  document.getElementById('reject-reason').value = '';
  openModal('modal-reject');
}

async function confirmReject() {
  const reason = document.getElementById('reject-reason').value.trim();
  if (!reason) { toast('اكتب سبب الرفض', 'error'); return; }

  const btn = document.getElementById('btn-reject-confirm');
  btn.disabled = true; btn.textContent = 'جارٍ...';

  const r = await api('PUT', `/cc/cards/${currentCardId}/reject`, { reason });
  btn.disabled = false; btn.textContent = 'تأكيد الرفض';

  if (r.success) { closeModal('modal-reject'); toast('تم الرفض — أُبلغ مركز الاتصال', 'success'); loadPending(); }
  else toast(r.message||'خطأ','error');
}

// ── Complete ──────────────────────────────────────────────
async function openComplete(id, ccComm) {
  currentCardId   = id;
  currentCcComm   = parseFloat(ccComm) || 0;
  warningCount    = 0;
  pendingOverride = false;

  document.getElementById('complete-card-info').innerHTML =
    `الحساب <b>#${id}</b> — عمولة موظف CC: <span class="mono" style="color:var(--pri2)">$${currentCcComm.toFixed(2)}/lot</span> (محددة مسبقاً)`;
  document.getElementById('disp-cc-comm').textContent = `$${currentCcComm.toFixed(2)}/lot`;
  document.getElementById('complete-alert').style.display = 'none';
  document.getElementById('warning-box').style.display = 'none';
  document.getElementById('comp-broker-comm').value = 4;
  document.getElementById('comp-marketer-comm').value = 0;
  calcTotal();
  openModal('modal-complete');
  await loadEmployees();
}

async function loadEmployees() {
  const r = await api('GET', '/employees?status=approved');
  if (!r.success) return;
  ['comp-broker','comp-marketer'].forEach(id => {
    const sel = document.getElementById(id);
    while (sel.options.length > 1) sel.remove(1);
    r.data.forEach(e => {
      const o = document.createElement('option'); o.value = e.id; o.textContent = e.name;
      sel.appendChild(o);
    });
  });
}

function calcTotal() {
  const broker   = parseFloat(document.getElementById('comp-broker-comm')?.value) || 0;
  const marketer = parseFloat(document.getElementById('comp-marketer-comm')?.value) || 0;
  const total    = currentCcComm + broker + marketer;

  document.getElementById('disp-broker-comm').textContent  = `$${broker.toFixed(2)}/lot`;
  document.getElementById('disp-total').textContent        = `$${total.toFixed(2)}/lot`;

  // Color total
  const el = document.getElementById('disp-total');
  el.style.color = LIMIT_ENABLED && total > LIMIT_AMOUNT ? 'var(--re)' : 'var(--gr)';
}

async function submitComplete() {
  const brokerId   = document.getElementById('comp-broker').value;
  const brokerComm = parseFloat(document.getElementById('comp-broker-comm').value) || 0;
  const mktId      = document.getElementById('comp-marketer').value;
  const mktComm    = parseFloat(document.getElementById('comp-marketer-comm').value) || 0;
  const initial    = parseFloat(document.getElementById('comp-initial').value) || 0;
  const monthly    = parseFloat(document.getElementById('comp-monthly').value) || 0;

  if (!brokerId || !initial || !monthly) {
    showAlert('complete-alert', 'الرجاء تعبئة البروكر والإيداعات', 'error');
    return;
  }

  const total = currentCcComm + brokerComm + mktComm;

  // Client-side warning check before sending
  if (LIMIT_ENABLED && total > LIMIT_AMOUNT && !pendingOverride) {
    warningCount++;
    if (warningCount > MAX_WARNINGS) {
      showWarning('blocked', total);
      return;
    }
    const levels = ['w1','w2','w3'];
    showWarning(levels[Math.min(warningCount-1, 2)], total);
    pendingOverride = true; // allow one more click
    return;
  }
  pendingOverride = false;

  const btn = document.getElementById('btn-complete');
  btn.disabled = true; btn.textContent = 'جارٍ الحفظ...';

  const r = await api('PUT', `/cc/cards/${currentCardId}/complete`, {
    broker_id: parseInt(brokerId), broker_commission: brokerComm,
    marketer_id: mktId ? parseInt(mktId) : null, marketer_commission: mktComm,
    initial_deposit: initial, monthly_deposit: monthly,
  }, { 'X-Commission-Warning-Count': warningCount });

  btn.disabled = false; btn.textContent = 'حفظ وإكمال ✓';

  if (r.success) {
    closeModal('modal-complete');
    toast(`✅ تم إكمال الحساب #${currentCardId}`, 'success');
    loadPending();
  } else if (r.blocked) {
    showWarning('blocked', total);
  } else if (r.warning) {
    warningCount = r.warning_number;
    showWarning(warningCount >= MAX_WARNINGS ? 'w3' : `w${warningCount}`, total);
    pendingOverride = true;
  } else {
    showAlert('complete-alert', r.message||'خطأ في الحفظ', 'error');
  }
}

function showWarning(level, total) {
  const box = document.getElementById('warning-box');
  const blocked = level === 'blocked';
  const msgs = {
    w1: `⚠️ تحذير: إجمالي العمولات $${total.toFixed(2)}/lot يتجاوز الحد ($${LIMIT_AMOUNT}). هل تريد المتابعة؟`,
    w2: `⚠️ تحذير ثانٍ: إجمالي العمولات $${total.toFixed(2)}/lot — هذا التجاوز الثاني. تأكيد؟`,
    w3: `🔴 آخر تحذير: ثلاث محاولات تجاوز الحد. هل تريد المتابعة رغم ذلك؟`,
    blocked: `🚫 تجاوزت العمولات الحد المسموح ($${LIMIT_AMOUNT}/lot). يرجى التواصل مع المدير المالي لمراجعة هذا الحساب.`,
  };
  box.innerHTML = `<div class="comm-warning ${level === 'blocked' ? 'blocked' : level}">${msgs[level]||msgs.blocked}</div>`;
  box.style.display = 'block';
  const btn = document.getElementById('btn-complete');
  if (blocked) { btn.disabled = true; btn.textContent = 'محجوب'; }
  else { btn.disabled = false; btn.textContent = 'تأكيد المتابعة رغم التحذير'; }
}

function showAlert(id, msg, type) {
  const el = document.getElementById(id); if (!el) return;
  const colors = { error:'var(--re)', success:'var(--gr)', warning:'var(--or)' };
  el.innerHTML = `<div style="padding:8px 12px;border-radius:8px;background:${colors[type]}22;border:1px solid ${colors[type]}44;color:${colors[type]};font-size:13px">${msg}</div>`;
  el.style.display = 'block';
}

document.addEventListener('DOMContentLoaded', loadPending);
</script>
@endpush
