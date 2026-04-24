@extends('layouts.app')
@section('title','مركز الاتصال')
@section('page-title','مركز الاتصال')

@push('styles')
<style>
.cc-status-badge{display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600}
.cc-pending  {background:rgba(186,117,23,.15);color:var(--or);border:1px solid rgba(186,117,23,.3)}
.cc-accepted {background:rgba(46,134,171,.12);color:var(--pri2);border:1px solid rgba(46,134,171,.25)}
.cc-rejected {background:rgba(224,80,80,.12);color:var(--re);border:1px solid rgba(224,80,80,.25)}
.cc-completed{background:rgba(34,201,122,.12);color:var(--gr);border:1px solid rgba(34,201,122,.25)}
.cc-none     {background:var(--bg3);color:var(--mu);border:1px solid var(--brd1)}
.cc-source-tag{display:inline-flex;align-items:center;gap:4px;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700;background:rgba(29,158,117,.15);color:#0F6E56;border:1px solid rgba(29,158,117,.3)}
.notif-dot{width:8px;height:8px;border-radius:50%;background:var(--or);flex-shrink:0}
.notif-item{display:flex;align-items:flex-start;gap:10px;padding:10px 14px;border-bottom:1px solid var(--brd1);cursor:pointer;transition:background .15s}
.notif-item:hover{background:var(--bg2)}
.notif-item.unread{background:rgba(46,134,171,.05)}
.form-limit{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
.limit-toggle{position:relative;width:44px;height:24px;cursor:pointer}
.limit-toggle input{opacity:0;width:0;height:0}
.lt-slider{position:absolute;inset:0;background:var(--brd2);border-radius:12px;transition:.3s}
.lt-slider:before{content:'';position:absolute;width:18px;height:18px;left:3px;bottom:3px;background:white;border-radius:50%;transition:.3s}
input:checked+.lt-slider{background:var(--pri)}
input:checked+.lt-slider:before{transform:translateX(20px)}
</style>
@endpush

@section('content')
{{-- ═══ TOPBAR ACTIONS ══════════════════════════════════════ --}}
@section('topbar-actions')
<button class="btn btn-primary btn-sm" onclick="openModal('modal-new-cc-card')">+ إرسال حساب جديد</button>
@endsection

{{-- ═══ KPI CARDS ═══════════════════════════════════════════ --}}
<div class="kpi-grid" style="margin-bottom:16px">
  <div class="kpi-card"><div class="kpi-label">إجمالي المُرسَلة</div><div class="kpi-value" id="kpi-total">—</div></div>
  <div class="kpi-card"><div class="kpi-label">بانتظار الفرع</div><div class="kpi-value" style="color:var(--or)" id="kpi-pending">—</div></div>
  <div class="kpi-card"><div class="kpi-label">مقبولة / مكتملة</div><div class="kpi-value" style="color:var(--gr)" id="kpi-done">—</div></div>
  <div class="kpi-card"><div class="kpi-label">مرفوضة</div><div class="kpi-value" style="color:var(--re)" id="kpi-rejected">—</div></div>
</div>

{{-- ═══ TWO COLUMNS: table + notifications ═══════════════════ --}}
<div style="display:grid;grid-template-columns:1fr 320px;gap:14px">

{{-- ── CC CARDS TABLE ──────────────────────────────────────── --}}
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">📞 الحسابات المُرسَلة من مركز الاتصال</div>
    <div style="display:flex;gap:6px">
      <select id="f-cc-status" class="form-control form-control-sm" onchange="loadCcCards()">
        <option value="">كل الحالات</option>
        <option value="cc_pending">بانتظار الفرع</option>
        <option value="accepted">مقبولة</option>
        <option value="rejected">مرفوضة</option>
        <option value="completed">مكتملة</option>
      </select>
    </div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>رقم الحساب</th>
          <th>الشهر</th>
          <th>الموظف</th>
          <th>ع. الموظف</th>
          <th>الفرع المعيّن</th>
          <th>الحالة</th>
          <th>سبب الرفض</th>
          <th></th>
        </tr>
      </thead>
      <tbody id="cc-tbody"><tr><td colspan="8" class="empty-state">جارٍ التحميل...</td></tr></tbody>
    </table>
  </div>
</div>

{{-- ── NOTIFICATIONS PANEL ────────────────────────────────── --}}
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">🔔 الإشعارات <span id="notif-badge" class="badge badge-orange" style="display:none">0</span></div>
    <button class="btn btn-ghost btn-sm" onclick="markAllRead()">قراءة الكل</button>
  </div>
  <div id="notif-list" style="max-height:480px;overflow-y:auto">
    <div class="empty-state">لا توجد إشعارات</div>
  </div>
</div>

</div>

{{-- ═══ COMMISSION LIMIT SETTINGS (FA only) ══════════════════ --}}
@if(auth()->user()?->isFinanceAdmin())
<div class="panel" style="margin-top:14px">
  <div class="panel-header"><div class="panel-title">⚙️ إعداد حد إجمالي العمولات</div></div>
  <div class="panel-body">
    <div class="form-limit">
      <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:13px">
        <label class="limit-toggle">
          <input type="checkbox" id="limit-enabled" onchange="saveLimitSettings()">
          <span class="lt-slider"></span>
        </label>
        تفعيل الحد
      </label>
      <div class="form-group" style="margin:0;display:flex;align-items:center;gap:8px">
        <label class="form-label" style="margin:0;white-space:nowrap">الحد $/lot</label>
        <input type="number" id="limit-amount" class="form-control form-control-sm" style="width:80px" min="1" max="100" step="0.5" value="8.00" onchange="saveLimitSettings()">
      </div>
      <div class="form-group" style="margin:0;display:flex;align-items:center;gap:8px">
        <label class="form-label" style="margin:0;white-space:nowrap">تحذيرات قبل الحجب</label>
        <input type="number" id="limit-warnings" class="form-control form-control-sm" style="width:60px" min="1" max="10" value="3" onchange="saveLimitSettings()">
      </div>
      <span id="limit-save-msg" style="font-size:12px;color:var(--gr);display:none">✓ تم الحفظ</span>
    </div>
  </div>
</div>
@endif

{{-- ═══ MODAL: NEW CC CARD ════════════════════════════════════ --}}
<div class="modal-overlay" id="modal-new-cc-card">
  <div class="modal" style="max-width:560px">
    <div class="modal-header">
      <div class="modal-title">📞 إرسال حساب جديد للفرع</div>
      <button class="modal-close" onclick="closeModal('modal-new-cc-card')">✕</button>
    </div>
    <div class="modal-body">
      <div id="cc-alert" style="display:none;margin-bottom:12px"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم الحساب *</label>
          <input type="text" id="cc-account" class="form-control" placeholder="مثال: 719750">
        </div>
        <div class="form-group">
          <label class="form-label">الشهر *</label>
          <select id="cc-month" class="form-control"><option value="">اختر الشهر</option></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الفرع المعيّن *</label>
          <select id="cc-target-branch" class="form-control"><option value="">اختر الفرع</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">موظف CC *</label>
          <select id="cc-agent" class="form-control"><option value="">اختر الموظف</option></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">نوع الحساب</label>
          <select id="cc-kind" class="form-control">
            <option value="new">جديد</option>
            <option value="sub">فرعي</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">ملاحظات</label>
          <input type="text" id="cc-notes" class="form-control" placeholder="اختياري">
        </div>
      </div>

      {{-- Agent commission display --}}
      <div id="cc-agent-comm-row" style="display:none;padding:10px;background:var(--bg2);border-radius:8px;margin-bottom:12px;font-size:13px">
        <span style="color:var(--mu)">عمولة الموظف:</span>
        <span id="cc-agent-comm-val" style="font-weight:700;color:var(--pri2);margin-right:6px">—</span>
        <span style="color:var(--mu)">$/lot</span>
        <span style="color:var(--mu);margin-right:12px;font-size:11px">(محددة من المدير المالي)</span>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-new-cc-card')">إلغاء</button>
      <button class="btn btn-primary" onclick="submitCcCard()" id="btn-cc-submit">إرسال للفرع ←</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
let ccWarningCount = 0;
const MAX_WARNINGS = {{ auth()->user()?->isFinanceAdmin() ? 999 : 3 }};

async function loadCcCards() {
  const status = document.getElementById('f-cc-status')?.value || '';
  const r = await api('GET', `/cc/sent${status ? '?cc_status='+status : ''}`);
  if (!r.success) return;

  const cards = r.data?.data || r.data || [];

  // KPIs
  const all = r.data?.data || [];
  document.getElementById('kpi-total').textContent    = all.length;
  document.getElementById('kpi-pending').textContent  = all.filter(c=>c.cc_status==='cc_pending').length;
  document.getElementById('kpi-done').textContent     = all.filter(c=>['accepted','completed'].includes(c.cc_status)).length;
  document.getElementById('kpi-rejected').textContent = all.filter(c=>c.cc_status==='rejected').length;

  const tbody = document.getElementById('cc-tbody');
  if (!cards.length) { tbody.innerHTML = '<tr><td colspan="8" class="empty-state">لا توجد كروت</td></tr>'; return; }

  tbody.innerHTML = cards.map(c => `
    <tr>
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td>${c.month}</td>
      <td>${c.cc_agent?.name || '—'}</td>
      <td class="mono" style="color:var(--pri2)">$${(+c.cc_agent_commission).toFixed(1)}/lot</td>
      <td>${c.branch?.name_ar || '—'}</td>
      <td>${ccStatusBadge(c.cc_status)}</td>
      <td style="font-size:11px;color:var(--re)">${c.cc_rejection_reason || '—'}</td>
      <td>
        ${c.cc_status === 'cc_pending' ? `<button class="btn btn-sm btn-primary" onclick="sendCard(${c.id})">إرسال ←</button>` : ''}
        ${c.cc_status === 'rejected'   ? `<button class="btn btn-sm btn-ghost" onclick="resendCard(${c.id})">إعادة إرسال</button>` : ''}
      </td>
    </tr>`).join('');
}

function ccStatusBadge(s) {
  const map = {
    cc_pending: ['cc-pending',  'بانتظار الفرع'],
    accepted:   ['cc-accepted', 'مقبول'],
    rejected:   ['cc-rejected', 'مرفوض'],
    completed:  ['cc-completed','مكتمل'],
    none:       ['cc-none',     '—'],
  };
  const [cls, lbl] = map[s] || ['cc-none','—'];
  return `<span class="cc-status-badge ${cls}">${lbl}</span>`;
}

async function sendCard(id) {
  if (!confirm('هل تريد إرسال هذا الحساب للفرع؟')) return;
  const r = await api('POST', `/cc/cards/${id}/send`);
  if (r.success) { toast('تم الإرسال — انتظر رد الفرع', 'success'); loadCcCards(); }
  else toast(r.message || 'خطأ', 'error');
}

async function resendCard(id) {
  if (!confirm('إعادة إرسال هذا الحساب للفرع؟')) return;
  const r = await api('POST', `/cc/cards/${id}/send`);
  if (r.success) { toast('أُعيد الإرسال', 'success'); loadCcCards(); }
  else toast(r.message || 'خطأ', 'error');
}

// ── Load notifications ────────────────────────────────────
async function loadNotifications() {
  const r = await api('GET', '/cc/notifications');
  if (!r.success) return;

  const badge = document.getElementById('notif-badge');
  const navBadge = document.getElementById('cc-badge');
  if (r.unread_count > 0) {
    badge.textContent = r.unread_count; badge.style.display = 'inline-flex';
    if (navBadge) { navBadge.textContent = r.unread_count; navBadge.style.display = 'inline-flex'; }
  }

  const list = document.getElementById('notif-list');
  const notifs = r.data || [];
  if (!notifs.length) { list.innerHTML = '<div class="empty-state">لا توجد إشعارات</div>'; return; }

  const icons = { card_sent:'📨', card_accepted:'✅', card_rejected:'❌', card_completed:'🎉' };
  list.innerHTML = notifs.map(n => `
    <div class="notif-item ${n.status==='unread'?'unread':''}" onclick="readNotif(${n.id})">
      ${n.status==='unread' ? '<div class="notif-dot"></div>' : '<div style="width:8px"></div>'}
      <div>
        <div style="font-size:12px;font-weight:600">${icons[n.type]||'🔔'} ${n.message}</div>
        <div style="font-size:10px;color:var(--mu);margin-top:2px">${n.from_branch?.name_ar||''} • ${new Date(n.created_at).toLocaleDateString('ar-SA')}</div>
      </div>
    </div>`).join('');
}

async function readNotif(id) {
  await api('PUT', `/cc/notifications/${id}/read`);
  loadNotifications();
}

async function markAllRead() {
  const r = await api('GET', '/cc/notifications');
  if (!r.success) return;
  const unread = (r.data||[]).filter(n=>n.status==='unread');
  await Promise.all(unread.map(n => api('PUT', `/cc/notifications/${n.id}/read`)));
  loadNotifications();
  toast('تم تحديد الكل كمقروء', 'success');
}

// ── Load form options ─────────────────────────────────────
async function loadFormOptions() {
  // Months
  const now = new Date();
  const mSel = document.getElementById('cc-month');
  for (let i = 0; i < 24; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const m = d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
    const o = document.createElement('option'); o.value = o.textContent = m;
    mSel.appendChild(o);
  }

  // Branches (exclude CC itself)
  const bR = await api('GET', '/branches');
  const brSel = document.getElementById('cc-target-branch');
  if (bR.success) {
    bR.data.filter(b => b.code !== 'CC').forEach(b => {
      const o = document.createElement('option'); o.value = b.id; o.textContent = b.name_ar;
      brSel.appendChild(o);
    });
  }

  // CC agents (employees of current CC branch)
  const eR = await api('GET', '/employees?status=approved');
  const aSel = document.getElementById('cc-agent');
  if (eR.success) {
    eR.data.forEach(e => {
      const o = document.createElement('option');
      o.value = e.id; o.textContent = e.name;
      o.dataset.comm = e.cc_commission || 1.00;
      aSel.appendChild(o);
    });
  }

  // Show agent commission on change
  aSel.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const row = document.getElementById('cc-agent-comm-row');
    if (opt.value) {
      document.getElementById('cc-agent-comm-val').textContent = (+opt.dataset.comm).toFixed(2);
      row.style.display = 'block';
    } else {
      row.style.display = 'none';
    }
  });
}

// ── Submit CC card ────────────────────────────────────────
async function submitCcCard() {
  const account = document.getElementById('cc-account').value.trim();
  const month   = document.getElementById('cc-month').value;
  const branch  = document.getElementById('cc-target-branch').value;
  const agent   = document.getElementById('cc-agent').value;

  if (!account || !month || !branch || !agent) {
    showAlert('cc-alert', 'الرجاء تعبئة جميع الحقول المطلوبة', 'error');
    return;
  }

  // Generate month_date from selected month string
  const mDate = new Date(month); // e.g. "Jan 2025"
  const monthDate = isNaN(mDate.getTime())
    ? new Date().toISOString().slice(0,10)
    : mDate.toISOString().slice(0,10);

  const btn = document.getElementById('btn-cc-submit');
  btn.disabled = true; btn.textContent = 'جارٍ الإرسال...';

  const r = await api('POST', '/cc/cards', {
    account_number:   account,
    month:            month,
    month_date:       monthDate,
    target_branch_id: parseInt(branch),
    cc_agent_id:      parseInt(agent),
    account_kind:     document.getElementById('cc-kind').value,
    notes:            document.getElementById('cc-notes').value,
  });

  btn.disabled = false; btn.textContent = 'إرسال للفرع ←';

  if (r.success) {
    closeModal('modal-new-cc-card');
    toast(`✅ تم إنشاء الكرت #${r.data?.account_number} — أرسله للفرع من القائمة`, 'success');
    loadCcCards();
  } else {
    showAlert('cc-alert', r.message || 'خطأ في الحفظ', 'error');
  }
}

function showAlert(id, msg, type) {
  const el = document.getElementById(id);
  if (!el) return;
  const colors = { error:'var(--re)', success:'var(--gr)', warning:'var(--or)' };
  el.innerHTML = `<div style="padding:8px 12px;border-radius:8px;background:${colors[type]}22;border:1px solid ${colors[type]}44;color:${colors[type]};font-size:13px">${msg}</div>`;
  el.style.display = 'block';
}

// ── Commission limit settings (FA only) ───────────────────
@if(auth()->user()?->isFinanceAdmin())
async function loadLimitSettings() {
  const r = await api('GET', '/settings/commission-limit');
  if (!r.success) return;
  document.getElementById('limit-enabled').checked  = r.data.enabled;
  document.getElementById('limit-amount').value     = r.data.amount;
  document.getElementById('limit-warnings').value   = r.data.warning_count;
}

async function saveLimitSettings() {
  const r = await api('POST', '/settings/commission-limit', {
    enabled:       document.getElementById('limit-enabled').checked,
    amount:        parseFloat(document.getElementById('limit-amount').value),
    warning_count: parseInt(document.getElementById('limit-warnings').value),
  });
  if (r.success) {
    const msg = document.getElementById('limit-save-msg');
    msg.style.display = 'inline';
    setTimeout(() => msg.style.display = 'none', 2000);
  }
}
@endif

// ── Init ─────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  loadCcCards();
  loadNotifications();
  loadFormOptions();
  @if(auth()->user()?->isFinanceAdmin())
  loadLimitSettings();
  @endif
  // Poll notifications every 30s
  setInterval(loadNotifications, 30000);
});
</script>
@endpush
