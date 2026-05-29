@extends('layouts.app')
@section('title','الموظفون')
@section('page-title','الموظفون')

@section('topbar-actions')
<button class="tb-btn primary" onclick="openModal('modal-add-emp')">➕ إضافة موظف</button>
@endsection

<style>
.icon-btn{display:inline-flex;align-items:center;justify-content:center;
  width:28px;height:28px;border-radius:7px;border:1px solid var(--brd1);
  background:var(--bg3);cursor:pointer;font-size:13px;
  transition:all .18s;color:var(--mu)}
.icon-btn:hover{border-color:var(--pri);background:rgba(26,173,186,.12);color:var(--pri2)}
.icon-btn.danger:hover{border-color:var(--re);background:rgba(232,69,69,.1);color:var(--re)}
</style>

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
    <table class="data-table">
      <thead>
        <tr>
          <th style="min-width:140px">الموظف</th>
          <th style="min-width:80px">الفرع</th>
          <th style="min-width:120px">العمولات</th>
          <th style="min-width:90px">الحالة</th>
          <th style="width:70px;text-align:center">إجراءات</th>
        </tr>
      </thead>
      <tbody id="emp-tbody">
        <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr>
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
          <label class="form-label">الاسم الكامل *</label>
          <input type="text" id="ae-name" class="form-control" placeholder="Ahmed Al-Sayed">
        </div>
        <div class="form-group">
          <label class="form-label">الدور الوظيفي</label>
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
          <label class="form-label">عمولة البروكر ($/lot)</label>
          <input type="number" id="ae-bc" class="form-control" value="4" min="0" step="0.5">
        </div>
        <div class="form-group">
          <label class="form-label">عمولة التسويق ($/lot)</label>
          <input type="number" id="ae-mc" class="form-control" value="3" min="0" step="0.5">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">البريد الإلكتروني</label>
        <input type="email" id="ae-email" class="form-control" placeholder="employee@wafragulf.com">
      </div>
      <div class="form-group">
        <label class="form-label">الفرع</label>
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
    <tr style="${e.status==='pending'?'opacity:.82;border-right:3px solid var(--or)':''}">
      <td>
        <div style="font-weight:700;font-size:13px">${esc(e.name)}</div>
        <div style="font-size:10px;color:var(--mu);margin-top:2px">${esc(roleLabels[e.role] || e.role)}</div>
        ${e.email ? `<div style="font-size:10px;color:var(--mu);direction:ltr">${esc(e.email)}</div>` : ''}
      </td>
      <td style="font-size:12px;color:var(--mu)">${esc(e.branch?.name_ar || '—')}</td>
      <td>
        <span class="mono c-blue" style="font-size:11px">بروكر: $${esc(String(e.broker_commission))}</span><br>
        <span class="mono c-green" style="font-size:11px">تسويق: $${esc(String(e.marketing_commission))}</span>
      </td>
      <td>${statusBadge(e.status)}</td>
      <td style="text-align:center">
        ${!e.is_base
          ? `<button class="icon-btn danger" onclick="deleteEmp(${e.id})" data-name="${esc(e.name)}" title="حذف الموظف">🗑</button>`
          : '<span style="font-size:10px;color:var(--mu)">أساسي</span>'}
      </td>
    </tr>`).join('') || '<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--mu)">لا يوجد موظفون</td></tr>';
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

async function deleteEmp(id) {
  const btn  = document.querySelector(`[onclick="deleteEmp(${id})"]`);
  const name = btn?.dataset?.name || '#' + id;
  if (!confirm('حذف الموظف: ' + name + '?')) return;
  const r = await api('DELETE', `/employees/${id}`);
  if (r.success) { toast(r.message, 'success'); loadEmps(); }
  else toast(r.message, 'error');
}

loadEmps();
loadBranches();
</script>
@endpush
