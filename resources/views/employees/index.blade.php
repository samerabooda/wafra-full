@extends('layouts.app')
@section('title','الموظفون')
@section('page-title','الموظفون')

@section('topbar-actions')
<button class="tb-btn primary" onclick="openModal('modal-add-emp')">➕ إضافة موظف</button>
@endsection

@section('content')

@if(auth()->user()?->isFinanceAdmin())
<!-- Pending Approvals Banner -->
<div id="pending-banner" class="alert alert-warning" style="display:none;margin-bottom:14px">
  ⏳ <span id="pending-text"></span>
  <a href="{{ route('permissions.index') }}" style="color:var(--or);font-weight:700;margin-right:8px">اعتماد الآن ←</a>
</div>
@endif

<div class="panel">
  <div class="panel-header">
    <div class="panel-title">👥 قائمة الموظفين <span id="emp-count" style="font-size:11px;color:var(--mu)"></span></div>
    <div style="display:flex;gap:8px">
      <select id="f-role" class="form-control" style="width:auto;font-size:12px;padding:5px 9px" onchange="loadEmps()">
        <option value="">كل الأدوار</option>
        <option value="broker">🏦 بروكر</option>
        <option value="marketing">📢 مسوّق داخلي</option>
        <option value="external">🌐 مسوّق خارجي</option>
        <option value="other">📋 أخرى</option>
      </select>
    </div>
  </div>
  <div class="table-scroll">
    <table class="data-table" style="min-width:620px">
      <thead>
        <tr>
          <th>الاسم</th><th>الدور</th><th>الفرع / Branch</th>
          <th>ع. بروكر / Broker Comm.</th><th>ع. تسويق</th>
          <th>الحالة / Status</th><th>تمت إضافته</th><th>إجراءات / Actions</th>
        </tr>
      </thead>
      <tbody id="emp-tbody">
        <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Add Employee Modal -->
<div class="modal-overlay" id="modal-add-emp">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <div class="modal-title">➕ إضافة موظف جديد</div>
      <button class="modal-close" onclick="closeModal('modal-add-emp')">✕</button>
    </div>
    <div class="modal-body">
      <div id="add-emp-err" class="alert alert-error"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">الاسم / Full Name *</label>
          <input type="text" id="ae-name" class="form-control" placeholder="Ahmed Al-Sayed">
        </div>
        <div class="form-group">
          <label class="form-label">الدور / Role</label>
          <select id="ae-role" class="form-control">
            <option value="broker">🏦 بروكر</option>
            <option value="marketing">📢 مسوّق داخلي</option>
            <option value="external">🌐 مسوّق خارجي</option>
            <option value="other">📋 أخرى</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">عمولة البروكر / Broker Commission ($)</label>
          <input type="number" id="ae-bc" class="form-control" value="4" min="0" step="0.5">
        </div>
        <div class="form-group">
          <label class="form-label">ع. تسويق / Marketing Comm. ($)</label>
          <input type="number" id="ae-mc" class="form-control" value="3" min="0" step="0.5">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">البريد / Email</label>
        <input type="email" id="ae-email" class="form-control" placeholder="employee@wafragulf.com">
      </div>
      <div class="form-group">
        <label class="form-label">الفرع / Branch / Branch</label>
        <select id="ae-branch" class="form-control"></select>
      </div>
      @if(auth()->user()?->isBranchManager())
      <div class="alert alert-info show">
        ℹ️ سيتم إضافة الموظف كـ <strong>قيد الانتظار</strong> — يحتاج اعتماد المدير المالي.
      </div>
      @endif
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-add-emp')">إلغاء</button>
      <button class="btn btn-primary" onclick="addEmployee()">إضافة ←</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
const roleLabels = {broker:'🏦 بروكر', marketing:'📢 مسوّق داخلي', external:'🌐 مسوّق خارجي', other:'📋 أخرى'};
const statusBadge = s => ({
  approved: '<span class="badge badge-green">✅ معتمد</span>',
  pending:  '<span class="badge badge-orange">⏳ قيد الانتظار</span>',
  rejected: '<span class="badge badge-red">❌ مرفوض</span>',
}[s] || s);

async function loadEmps() {
  const role = document.getElementById('f-role').value;
  const params = role ? `?role=${role}` : '';
  const r = await api('GET', '/employees'+params);
  if (!r.success) return;

  const emps = r.data;
  document.getElementById('emp-count').textContent = emps.filter(e=>e.status==='approved').length + ' موظف معتمد';

  // Pending banner
  if (r.pending_count > 0) {
    document.getElementById('pending-banner')?.style && (document.getElementById('pending-banner').style.display='block');
    const pt = document.getElementById('pending-text');
    if (pt) pt.textContent = r.pending_count + ' موظف بانتظار الاعتماد';
  }

  document.getElementById('emp-tbody').innerHTML = emps.map(e => `
    <tr style="${e.status==='pending'?'opacity:.8;border-right:3px solid var(--or)':''}">
      <td style="font-weight:700">${e.name}</td>
      <td>${roleLabels[e.role] || e.role}</td>
      <td style="color:var(--mu)">${e.branch?.name_ar || '—'}</td>
      <td class="mono c-blue">$${e.broker_commission}</td>
      <td class="mono c-green">$${e.marketing_commission}</td>
      <td>${statusBadge(e.status)}</td>
      <td style="color:var(--mu);font-size:11px">${e.added_by?.name || '—'}</td>
      <td>
        ${!e.is_base ? `<button class="btn btn-ghost btn-sm" onclick="deleteEmp(${e.id},'${e.name}')">🗑️</button>` : '<span style="font-size:10px;color:var(--mu)">أساسي</span>'}
      </td>
    </tr>`).join('') || '<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)">لا يوجد موظفون</td></tr>';
}

async function loadBranches() {
  const r = await api('GET', '/branches');
  if (!r.success) return;
  const sel = document.getElementById('ae-branch');
  sel.innerHTML = '<option value="">— اختر الفرع —</option>';
  r.data.forEach(b => { const o=document.createElement('option'); o.value=b.id; o.textContent=b.name_ar; sel.appendChild(o); });
}

async function addEmployee() {
  const name = document.getElementById('ae-name').value.trim();
  if (!name) { document.getElementById('add-emp-err').textContent='يرجى إدخال الاسم'; document.getElementById('add-emp-err').classList.add('show'); return; }

  const r = await api('POST', '/employees', {
    name,
    email:                  document.getElementById('ae-email').value || null,
    role:                   document.getElementById('ae-role').value,
    branch_id:              parseInt(document.getElementById('ae-branch').value) || null,
    broker_commission:      parseFloat(document.getElementById('ae-bc').value) || 4,
    marketing_commission:   parseFloat(document.getElementById('ae-mc').value) || 3,
  });

  if (r.success) {
    closeModal('modal-add-emp');
    toast(r.message, r.pending ? 'info' : 'success');
    loadEmps();
    document.getElementById('ae-name').value = '';
  } else {
    const err = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message;
    document.getElementById('add-emp-err').textContent = err;
    document.getElementById('add-emp-err').classList.add('show');
  }
}

async function deleteEmp(id, name) {
  if (!confirm('حذف الموظف: ' + name + '?')) return;
  const r = await api('DELETE', `/employees/${id}`);
  if (r.success) { toast(r.message, 'success'); loadEmps(); }
  else toast(r.message, 'error');
}

loadEmps();
loadBranches();
</script>
@endpush
