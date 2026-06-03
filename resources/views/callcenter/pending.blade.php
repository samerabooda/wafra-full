@extends('layouts.app')
@section('title', 'CC Cards Inbox')
@section('page-title', 'CC Cards Inbox')

@section('content')
<style>
.cc-source-badge{display:inline-flex;align-items:center;gap:4px;background:linear-gradient(135deg,#7b68ee,#5f4fcf);color:#fff;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;letter-spacing:.4px;vertical-align:middle}
.cc-chip{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700}
.cc-chip.branch_pending{background:#cff4fc;color:#055160}
.cc-chip.accepted{background:#d1e7dd;color:#0a5c36}
.cc-row td:first-child{border-right:4px solid #7b68ee !important}
.cc-row{background:linear-gradient(90deg,rgba(123,104,238,.05),transparent 60%) !important}
.cc-row:hover{background:linear-gradient(90deg,rgba(123,104,238,.11),transparent 60%) !important}
.complete-form{background:var(--bg3);border-radius:12px;padding:16px;margin-top:8px;border:1px solid var(--brd1)}
.cc-limit-bar{height:6px;border-radius:3px;background:var(--brd2);margin-top:6px;overflow:hidden}
.cc-limit-fill{height:100%;border-radius:3px;transition:width .3s,background .3s}
.cc-notes-box{font-size:12px;color:var(--mu);background:rgba(123,104,238,.06);border:1px solid rgba(123,104,238,.2);border-radius:8px;padding:6px 10px;margin-top:6px;max-width:500px}
</style>

<div class="panel" style="max-width:1060px">
  <div class="panel-header">
    <div class="panel-title" id="pnd-panel-title">
      📩 كروت CC الواردة
      <span id="pending-count" style="background:#7b68ee;color:#fff;border-radius:20px;padding:2px 10px;font-size:12px;margin-right:8px">0</span>
    </div>
    <button class="btn btn-ghost btn-sm" id="pnd-refresh-btn" onclick="loadPending()">🔄 تحديث</button>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    <!-- Commission limit notice -->
    <div style="background:rgba(123,104,238,.07);border:1px solid rgba(123,104,238,.25);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px">
      <span style="font-size:20px">⚠️</span>
      <div id="pnd-limit-notice">
        <strong style="color:#7b68ee">حد عمولات كروت CC:</strong>
        <span style="font-size:13px"> عمولة البروكر + عمولة المسوّق لا يجب أن تتجاوز <strong>5$ / lot</strong></span>
        <span style="font-size:11px;color:var(--mu);display:block;margin-top:2px">سيتم رفض الحفظ إذا تجاوز مجموعهما 5$</span>
      </div>
    </div>

    <div id="pending-list"></div>
  </div>
</div>

{{-- ══ Reject Modal ══ --}}
<div class="modal-overlay" id="modal-reject" style="display:none">
  <div class="modal" style="max-width:480px">
    <div class="modal-header">
      <div class="modal-title" id="rj-modal-title">❌ رفض الكرت</div>
      <button class="modal-close" onclick="closeRejectModal()">✕</button>
    </div>
    <div class="modal-body">
      <div id="rj-err" class="alert alert-error"></div>
      <div class="form-group">
        <label class="form-label" id="rj-lbl-reason">سبب الرفض * <span style="font-size:11px;color:var(--mu)">(5 أحرف على الأقل)</span></label>
        <textarea id="rj-reason" class="form-control" rows="3" placeholder="اكتب سبب الرفض هنا..."></textarea>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="rj-btn-cancel" onclick="closeRejectModal()">إلغاء</button>
      <button class="btn btn-danger" id="rj-btn-confirm" onclick="confirmReject()">❌ تأكيد الرفض</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
/* ══ CC Pending Bilingual Dictionary ══ */
const PND = {
  ar: {
    tbTitle:'كروت CC الواردة', panelTitle:'📩 كروت CC الواردة',
    refreshBtn:'🔄 تحديث',
    limitTitle:'حد عمولات كروت CC:',
    limitBody:' عمولة البروكر + عمولة المسوّق لا يجب أن تتجاوز <strong>5$ / lot</strong>',
    limitSub:'سيتم رفض الحفظ إذا تجاوز مجموعهما 5$',
    rjModalTitle:'❌ رفض الكرت',
    rjLblReason:'سبب الرفض * <span style="font-size:11px;color:var(--mu)">(5 أحرف على الأقل)</span>',
    rjPhReason:'اكتب سبب الرفض هنا...',
    rjBtnCancel:'إلغاء', rjBtnConfirm:'❌ تأكيد الرفض',
    errReason:'⚠️ يجب كتابة سبب الرفض (5 أحرف على الأقل)',
    empty:'لا توجد كروت CC واردة حالياً',
    statusPending:'📩 بانتظار القرار', statusAccepted:'✅ مقبول — أكمل البيانات',
    kindSub:'🔀 Sub', kindNew:'🆕 New',
    from:'من:', agent:'موظف:',  ccComm:'عمولة CC:',
    btnAccept:'✅ قبول الكرت', btnReject:'❌ رفض',
    completeTitle:'📋 استكمال بيانات الكرت',
    lblBroker:'البروكر * <span style="font-size:10px;color:#dc3545">(مطلوب)</span>',
    lblBComm:'عمولة البروكر ($) *',
    lblMktr:'المسوّق الرئيسي', lblMComm:'عمولة المسوّق ($)',
    totalComm:'إجمالي العمولات (بروكر + مسوّق)',
    lblExt1:'مسوّق إضافي 1', lblEcomm1:'عمولة المسوّق الإضافي 1 ($)',
    lblExt2:'مسوّق إضافي 2', lblEcomm2:'عمولة المسوّق الإضافي 2 ($)',
    lblDep:'إيداع فتح الحساب *', lblMon:'الإيداع الشهري المتوقع',
    btnComplete:'🏁 إتمام الكرت',
    optBroker:'— اختر البروكر —', optNoMktr:'— لا يوجد —',
    errBroker:'⚠️ يرجى اختيار البروكر',
    confirmAccept:'قبول هذا الكرت؟',
  },
  en: {
    tbTitle:'Incoming CC Cards', panelTitle:'📩 Incoming CC Cards',
    refreshBtn:'🔄 Refresh',
    limitTitle:'CC Card Commission Limit:',
    limitBody:' Broker + Marketer commission must not exceed <strong>$5 / lot</strong>',
    limitSub:'Save will be rejected if total exceeds $5',
    rjModalTitle:'❌ Reject Card',
    rjLblReason:'Rejection Reason * <span style="font-size:11px;color:var(--mu)">(min 5 characters)</span>',
    rjPhReason:'Enter rejection reason here...',
    rjBtnCancel:'Cancel', rjBtnConfirm:'❌ Confirm Rejection',
    errReason:'⚠️ Please enter a rejection reason (min 5 characters)',
    empty:'No incoming CC cards at the moment',
    statusPending:'📩 Awaiting Decision', statusAccepted:'✅ Accepted — Complete Data',
    kindSub:'🔀 Sub', kindNew:'🆕 New',
    from:'From:', agent:'Agent:', ccComm:'CC Comm.:',
    btnAccept:'✅ Accept Card', btnReject:'❌ Reject',
    completeTitle:'📋 Complete Card Data',
    lblBroker:'Broker * <span style="font-size:10px;color:#dc3545">(required)</span>',
    lblBComm:'Broker Commission ($) *',
    lblMktr:'Main Marketer', lblMComm:'Marketer Commission ($)',
    totalComm:'Total Commissions (Broker + Marketer)',
    lblExt1:'Additional Marketer 1', lblEcomm1:'Additional Marketer 1 Commission ($)',
    lblExt2:'Additional Marketer 2', lblEcomm2:'Additional Marketer 2 Commission ($)',
    lblDep:'Opening Deposit *', lblMon:'Expected Monthly Deposit',
    btnComplete:'🏁 Complete Card',
    optBroker:'— Select Broker —', optNoMktr:'— None —',
    errBroker:'⚠️ Please select a broker',
    confirmAccept:'Accept this card?',
  }
};
function pndL()    { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function pnd(key)  { const l = pndL(); return PND[l]?.[key] ?? PND.ar[key] ?? key; }

function pndApplyLang() {
  const el = (id) => document.getElementById(id);
  const t  = (id, key) => { const e = el(id); if (e) e.textContent = pnd(key); };
  const h  = (id, key) => { const e = el(id); if (e) e.innerHTML  = pnd(key); };

  t('pnd-panel-title', 'panelTitle');
  // keep count badge inside panel title
  const pt = el('pnd-panel-title');
  if (pt) { const cs = el('pending-count'); pt.textContent = pnd('panelTitle') + ' '; if (cs) pt.appendChild(cs); }
  t('pnd-refresh-btn', 'refreshBtn');
  // limit notice
  const ln = el('pnd-limit-notice');
  if (ln) ln.innerHTML = `<strong style="color:#7b68ee">${pnd('limitTitle')}</strong><span style="font-size:13px">${pnd('limitBody')}</span><span style="font-size:11px;color:var(--mu);display:block;margin-top:2px">${pnd('limitSub')}</span>`;
  // reject modal
  t('rj-modal-title', 'rjModalTitle');
  h('rj-lbl-reason', 'rjLblReason');
  const rjPh = el('rj-reason'); if (rjPh) rjPh.placeholder = pnd('rjPhReason');
  t('rj-btn-cancel', 'rjBtnCancel'); t('rj-btn-confirm', 'rjBtnConfirm');
  // topbar
  const tb = document.querySelector('.tb-title'); if (tb) tb.textContent = pnd('tbTitle');
  // Re-render if loaded
  if (_pendingData.length) renderPendingList(_pendingData);
}
const _pndOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_pndOrigApplyLang) _pndOrigApplyLang(lang);
  pndApplyLang();
};

let _pendingData = [];
let rejectCardId = null;

// ── Reject modal helpers ───────────────────────────────────────
function openRejectModal(id) {
  rejectCardId = id;
  document.getElementById('rj-reason').value = '';
  document.getElementById('rj-err').classList.remove('show');
  document.getElementById('modal-reject').style.display = 'flex';
}
function closeRejectModal() {
  document.getElementById('modal-reject').style.display = 'none';
  rejectCardId = null;
}

async function confirmReject() {
  const reason = document.getElementById('rj-reason').value.trim();
  if (!reason || reason.length < 5) {
    const e = document.getElementById('rj-err');
    e.textContent = pnd('errReason');
    e.classList.add('show');
    return;
  }
  const r = await api('PUT', `/cc/cards/${rejectCardId}/reject`, { reason });
  if (r.success) {
    closeRejectModal();
    showAlert('ok', r.message);
    loadPending();
  } else {
    const e = document.getElementById('rj-err');
    e.textContent = r.message;
    e.classList.add('show');
  }
}

function renderPendingList(data) {
  const container = document.getElementById('pending-list');
  document.getElementById('pending-count').textContent = data.length;
  if (!data.length) {
    container.innerHTML = `<div style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:40px;margin-bottom:8px">📭</div>${pnd('empty')}</div>`;
    return;
  }
  container.innerHTML = data.map(c => cardHtml(c)).join('');
  data.filter(c => c.cc_status === 'accepted').forEach(c => populateEmployeeSelects(c.id));
}

// ── Card HTML builder ──────────────────────────────────────────
function cardHtml(c) {
  const statusLabel = c.cc_status === 'branch_pending' ? pnd('statusPending') : pnd('statusAccepted');
  const kindLabel   = c.account_kind === 'sub' ? pnd('kindSub') : pnd('kindNew');

  const notesHtml = c.notes
    ? `<div class="cc-notes-box">💬 ${c.notes}</div>`
    : '';

  const accountTypeHtml = c.account_type?.type
    ? `<span style="font-size:11px;color:var(--mu)">| ${c.account_type.type}</span>`
    : '';

  return `
  <div id="card-${c.id}" style="border:1px solid var(--brd1);border-right:4px solid #7b68ee;border-radius:12px;margin-bottom:14px;overflow:hidden">
    <!-- Card Header -->
    <div style="background:var(--bg2);padding:14px 16px">
      <div style="display:flex;flex-wrap:wrap;gap:10px;align-items:center;justify-content:space-between;margin-bottom:6px">
        <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
          <span class="cc-source-badge">📞 CC</span>
          <strong style="font-size:16px">${c.account_number}</strong>
          <span style="color:var(--mu);font-size:13px">${c.month}</span>
          <span style="font-size:11px;color:var(--mu)">${kindLabel}</span>
          ${accountTypeHtml}
          <span class="cc-chip ${c.cc_status}">${statusLabel}</span>
        </div>
        <div style="font-size:12px;color:var(--mu)">
          ${pnd('from')} <strong>${c.cc_branch?.name_ar ?? '—'}</strong>
          &nbsp;|&nbsp; ${pnd('agent')} <strong>${c.cc_agent?.name ?? '—'}</strong>
          &nbsp;|&nbsp; ${pnd('ccComm')} <strong>${c.cc_agent_commission}$</strong>
        </div>
      </div>
      ${notesHtml}
    </div>

    <!-- Card Actions -->
    <div style="padding:14px 16px">
      ${c.cc_status === 'branch_pending' ? `
        <div style="display:flex;gap:10px;margin-bottom:12px">
          <button class="btn btn-primary btn-sm" onclick="acceptCard(${c.id})">${pnd('btnAccept')}</button>
          <button class="btn btn-danger btn-sm"  onclick="openRejectModal(${c.id})">${pnd('btnReject')}</button>
        </div>
      ` : ''}
      ${c.cc_status === 'accepted' ? completeFormHtml(c) : ''}
    </div>
  </div>`;
}

// ── Complete form builder ─────────────────────────────────────
function completeFormHtml(c) {
  return `
  <div class="complete-form" id="cf-${c.id}">
    <div style="font-size:13px;font-weight:700;margin-bottom:12px;color:var(--pri2)">${pnd('completeTitle')}</div>

    <!-- Row 1: Broker + broker commission -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${pnd('lblBroker')}</label>
        <select id="cf-broker-${c.id}" class="form-control broker-sel" data-card="${c.id}" onchange="checkLimit(${c.id})">
          <option value="">${pnd('optBroker')}</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">${pnd('lblBComm')}</label>
        <input type="number" id="cf-bcomm-${c.id}" class="form-control" value="2.5" min="0" max="5" step="0.5" oninput="checkLimit(${c.id})">
      </div>
    </div>

    <!-- Row 2: Main marketer + marketer commission -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${pnd('lblMktr')}</label>
        <select id="cf-mktr-${c.id}" class="form-control" onchange="onMarketerChange(${c.id})">
          <option value="">${pnd('optNoMktr')}</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">${pnd('lblMComm')}</label>
        <input type="number" id="cf-mcomm-${c.id}" class="form-control" value="0" min="0" max="5" step="0.5" oninput="checkLimit(${c.id})" disabled>
      </div>
    </div>

    <!-- Commission limit progress bar -->
    <div style="margin-bottom:14px">
      <div style="display:flex;justify-content:space-between;font-size:11px;margin-bottom:3px">
        <span>${pnd('totalComm')}</span>
        <span id="cf-total-${c.id}" style="font-weight:700">0.0$ / 5.0$</span>
      </div>
      <div class="cc-limit-bar"><div id="cf-bar-${c.id}" class="cc-limit-fill" style="width:0%;background:#198754"></div></div>
      <div id="cf-warn-${c.id}" style="font-size:11px;color:#dc3545;margin-top:4px;display:none">⛔ ${pndL()==='en'?'Exceeds the allowed limit ($5)':'يتجاوز الحد المسموح (5$)'}</div>
    </div>

    <!-- Row 3: Ext marketer 1 -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${pnd('lblExt1')}</label>
        <select id="cf-ext1-${c.id}" class="form-control">
          <option value="">${pnd('optNoMktr')}</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">${pnd('lblEcomm1')}</label>
        <input type="number" id="cf-ecomm1-${c.id}" class="form-control" value="0" min="0" step="0.5">
      </div>
    </div>

    <!-- Row 4: Ext marketer 2 -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${pnd('lblExt2')}</label>
        <select id="cf-ext2-${c.id}" class="form-control">
          <option value="">${pnd('optNoMktr')}</option>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">${pnd('lblEcomm2')}</label>
        <input type="number" id="cf-ecomm2-${c.id}" class="form-control" value="0" min="0" step="0.5">
      </div>
    </div>

    <!-- Row 5: Deposits -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">${pnd('lblDep')}</label>
        <input type="number" id="cf-dep-${c.id}" class="form-control" value="0" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">${pnd('lblMon')}</label>
        <input type="number" id="cf-mon-${c.id}" class="form-control" value="0" min="0">
      </div>
    </div>

    <!-- Row 6: Commissions -->
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">Forex Commission ($)</label>
        <input type="number" id="cf-forex-${c.id}" class="form-control" value="8" min="0">
      </div>
      <div class="form-group">
        <label class="form-label">Futures Commission ($)</label>
        <input type="number" id="cf-fut-${c.id}" class="form-control" value="8" min="0">
      </div>
    </div>

    <div id="cf-err-${c.id}" class="alert alert-error"></div>
    <button class="btn btn-primary" onclick="completeCard(${c.id})">${pnd('btnComplete')}</button>
  </div>`;
}

// ── Marketer change: enable/disable commission field ──────────
function onMarketerChange(cardId) {
  const mktrSel   = document.getElementById(`cf-mktr-${cardId}`);
  const mcommInp  = document.getElementById(`cf-mcomm-${cardId}`);
  const hasMarketer = !!mktrSel?.value;
  mcommInp.disabled = !hasMarketer;
  if (!hasMarketer) mcommInp.value = '0';
  checkLimit(cardId);
}

// ── Commission limit bar updater ───────────────────────────────
function checkLimit(cardId) {
  const bComm = parseFloat(document.getElementById(`cf-bcomm-${cardId}`)?.value ?? 0) || 0;
  const mComm = parseFloat(document.getElementById(`cf-mcomm-${cardId}`)?.value ?? 0) || 0;
  const total = bComm + mComm;
  const pct   = Math.min((total / 5) * 100, 100);
  const bar   = document.getElementById(`cf-bar-${cardId}`);
  const warn  = document.getElementById(`cf-warn-${cardId}`);
  const lbl   = document.getElementById(`cf-total-${cardId}`);
  if (!bar) return;
  bar.style.width      = pct + '%';
  bar.style.background = total > 5 ? '#dc3545' : total > 3.5 ? '#fd7e14' : '#198754';
  lbl.textContent      = total.toFixed(1) + '$ / 5.0$';
  lbl.style.color      = total > 5 ? '#dc3545' : 'inherit';
  warn.style.display   = total > 5 ? 'block' : 'none';
}

// ── Accept card ────────────────────────────────────────────────
async function acceptCard(id) {
  if (!confirm(pnd('confirmAccept'))) return;
  const r = await api('PUT', `/cc/cards/${id}/accept`);
  if (r.success) { showAlert('ok', r.message); loadPending(); }
  else showAlert('err', r.message);
}

// ── Complete card ─────────────────────────────────────────────
async function completeCard(id) {
  const brokerId = parseInt(document.getElementById(`cf-broker-${id}`).value) || null;
  const errEl    = document.getElementById(`cf-err-${id}`);
  errEl.classList.remove('show');

  // Broker is required
  if (!brokerId) {
    errEl.textContent = pnd('errBroker');
    errEl.classList.add('show');
    return;
  }

  const bComm   = parseFloat(document.getElementById(`cf-bcomm-${id}`).value) || 0;
  const mktrId  = parseInt(document.getElementById(`cf-mktr-${id}`)?.value)   || null;
  const mComm   = mktrId ? (parseFloat(document.getElementById(`cf-mcomm-${id}`)?.value) || 0) : 0;

  // Client-side commission limit check
  if (bComm + mComm > 5) {
    errEl.textContent = `⛔ عمولة البروكر (${bComm}$) + عمولة المسوّق (${mComm}$) = ${(bComm+mComm).toFixed(1)}$ تتجاوز الحد المسموح (5$)`;
    errEl.classList.add('show');
    return;
  }

  const payload = {
    broker_id:           brokerId,
    broker_commission:   bComm,
    marketer_id:         mktrId,
    marketer_commission: mComm,
    ext_marketer1_id:    parseInt(document.getElementById(`cf-ext1-${id}`)?.value)   || null,
    ext_commission1:     parseFloat(document.getElementById(`cf-ecomm1-${id}`)?.value) || 0,
    ext_marketer2_id:    parseInt(document.getElementById(`cf-ext2-${id}`)?.value)   || null,
    ext_commission2:     parseFloat(document.getElementById(`cf-ecomm2-${id}`)?.value) || 0,
    initial_deposit:     parseFloat(document.getElementById(`cf-dep-${id}`).value)   || 0,
    monthly_deposit:     parseFloat(document.getElementById(`cf-mon-${id}`).value)   || 0,
    forex_commission:    parseFloat(document.getElementById(`cf-forex-${id}`).value) || 0,
    futures_commission:  parseFloat(document.getElementById(`cf-fut-${id}`).value)   || 0,
  };

  const r = await api('PUT', `/cc/cards/${id}/complete`, payload);
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

// ── Alert helper ───────────────────────────────────────────────
function showAlert(type, msg) {
  const e = document.getElementById('alert-err');
  const o = document.getElementById('alert-ok');
  e.classList.remove('show'); o.classList.remove('show');
  if (type === 'err') { e.textContent = msg; e.classList.add('show'); }
  else                { o.textContent = msg; o.classList.add('show'); }
  window.scrollTo(0, 0);
}

// ── Load pending cards ─────────────────────────────────────────
async function loadPending() {
  const r = await api('GET', '/cc/pending');
  _pendingData = r.success ? (r.data || []) : [];
  renderPendingList(_pendingData);
}

// ── Load employees into memory, then populate selects ──────────
async function loadEmployees() {
  const r = await api('GET', '/employees?status=approved');
  window._ccEmployees = r.success ? r.data : [];
}

function populateEmployeeSelects(cardId) {
  const emps = window._ccEmployees ?? [];
  ['broker', 'mktr', 'ext1', 'ext2'].forEach(slot => {
    const sel = document.getElementById(`cf-${slot}-${cardId}`);
    if (!sel) return;
    // Keep the placeholder option, then append employees
    const placeholder = sel.options[0]?.textContent ?? '—';
    sel.innerHTML = `<option value="">${placeholder}</option>`;
    emps.forEach(e => {
      const o = document.createElement('option');
      o.value = e.id;
      o.textContent = e.name
        + (e.role === 'external'   ? ' 🌐'
         : e.role === 'marketing'  ? ' 📢'
         :                           ' 🏦');
      sel.appendChild(o);
    });
  });
  // Sync marketer commission field state after populating
  onMarketerChange(cardId);
}

// ── Init ───────────────────────────────────────────────────────
async function init() {
  pndApplyLang();
  await loadEmployees();
  await loadPending();
}

init();
</script>
@endpush
