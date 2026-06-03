@extends('layouts.app')
@section('title','Employees')
@section('page-title','Employees')

@section('topbar-actions')
<button class="tb-btn primary" id="emp-topbar-add-btn" onclick="openModal('modal-add-emp')">➕ إضافة موظف</button>
@endsection

<style>
.icon-btn{display:inline-flex;align-items:center;justify-content:center;
  width:28px;height:28px;border-radius:7px;border:1px solid var(--brd1);
  background:var(--bg3);cursor:pointer;font-size:13px;
  transition:all .18s;color:var(--mu)}
.icon-btn:hover{border-color:var(--pri);background:rgba(26,173,186,.12);color:var(--pri2)}
.icon-btn.danger:hover{border-color:var(--re);background:rgba(232,69,69,.1);color:var(--re)}
.emp-chips-bar{display:flex;gap:7px;overflow-x:auto;-webkit-overflow-scrolling:touch;flex-wrap:nowrap}
.emp-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 13px;border-radius:20px;border:1px solid var(--brd1);background:var(--bg2);color:var(--mu);font-size:12px;font-weight:700;cursor:pointer;white-space:nowrap;font-family:'Tajawal',sans-serif;transition:all .15s;flex-shrink:0}
.emp-chip:hover{border-color:var(--pri);color:var(--pri2)}
.emp-chip.on{background:rgba(26,173,186,.16);border-color:var(--pri);color:var(--pri2)}
.emp-chip b{font-family:'JetBrains Mono',monospace;background:rgba(255,255,255,.1);padding:0 6px;border-radius:9px;font-size:11px;line-height:1.6}
</style>

@section('content')

@if(auth()->user()?->isFinanceAdmin())
<!-- Pending Approvals Banner -->
<div id="pending-banner" class="alert alert-warning" style="display:none;margin-bottom:14px">
  ⏳ <span id="pending-text"></span>
  <a href="{{ route('permissions.index') }}" id="emp-approve-link" style="color:var(--or);font-weight:700;margin-right:8px">اعتماد الآن ←</a>
</div>
@endif

<div class="panel">
  <div class="panel-header">
    <div class="panel-title" id="emp-panel-title">👥 قائمة الموظفين <span id="emp-count" style="font-size:11px;color:var(--mu)"></span></div>
    <div style="display:flex;gap:8px">
      <select id="f-role" class="form-control" style="width:auto;font-size:12px;padding:5px 9px" onchange="loadEmps()">
        <option value="" id="emp-opt-all">كل الأدوار</option>
        <option value="broker" id="emp-opt-broker">🏦 بروكر</option>
        <option value="marketing" id="emp-opt-mkt">📢 مسوّق</option>
        <option value="broker_marketer">🔁 بروكر ومسوّق</option>
        <option value="external" id="emp-opt-ext">🌐 مسوّق خارجي</option>
        <option value="other" id="emp-opt-other">📋 أخرى</option>
      </select>
    </div>
  </div>
  {{-- Branch filter chips (with per-branch counts) --}}
  <div class="emp-chips-bar" id="emp-branch-chips" style="padding:6px 14px 12px"></div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th style="min-width:140px" id="emp-th-name">الموظف</th>
          <th style="min-width:80px" id="emp-th-branch">الفرع</th>
          <th style="min-width:120px" id="emp-th-comm">العمولات</th>
          <th style="min-width:90px" id="emp-th-status">الحالة</th>
          <th style="width:70px;text-align:center" id="emp-th-act">إجراءات</th>
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
      <div class="modal-title" id="ae-modal-title">➕ إضافة موظف جديد</div>
      <button class="modal-close" onclick="closeModal('modal-add-emp')">✕</button>
    </div>
    <div class="modal-body">
      <div id="add-emp-err" class="alert alert-error"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="ae-lbl-name">الاسم الكامل *</label>
          <input type="text" id="ae-name" class="form-control" placeholder="Ahmed Al-Sayed">
        </div>
        <div class="form-group">
          <label class="form-label" id="ae-lbl-role">الدور الوظيفي / Role</label>
          <select id="ae-role" class="form-control">
            <option value="broker">🏦 بروكر / Broker</option>
            <option value="marketing">📢 مسوّق / Marketer</option>
            <option value="broker_marketer">🔁 بروكر ومسوّق / Broker + Marketer</option>
            <option value="external">🌐 مسوّق خارجي / External Marketer</option>
            <option value="other">📋 أخرى / Other</option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" id="ae-lbl-email">البريد الإلكتروني</label>
        <input type="email" id="ae-email" class="form-control" placeholder="employee@wafragulf.com">
      </div>
      <div class="form-group">
        <label class="form-label" id="ae-lbl-branch">الفرع</label>
        <select id="ae-branch" class="form-control"></select>
      </div>
      @if(auth()->user()?->isBranchManager())
      <div class="alert alert-info show" id="ae-pending-info">
        ℹ️ سيتم إضافة الموظف كـ <strong>قيد الانتظار</strong> — يحتاج اعتماد المدير المالي.
      </div>
      @endif
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="ae-btn-cancel" onclick="closeModal('modal-add-emp')">إلغاء</button>
      <button class="btn btn-primary" id="ae-btn-add" onclick="addEmployee()">إضافة ←</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
/* ══ Employees Bilingual Dictionary ══ */
const EMP = {
  ar: {
    tbTitle:'الموظفون', topbarAdd:'➕ إضافة موظف',
    panelTitle:'👥 قائمة الموظفين',
    optAll:'كل الأدوار', optBroker:'🏦 بروكر', optMkt:'📢 مسوّق داخلي',
    optExt:'🌐 مسوّق خارجي', optOther:'📋 أخرى',
    thName:'الموظف', thBranch:'الفرع', thComm:'الدور', thStatus:'الحالة', thAct:'إجراءات',
    loading:'جاري التحميل...', empty:'لا يوجد موظفون',
    approved:'موظف معتمد', pending:'موظف بانتظار الاعتماد',
    brokerComm:'بروكر:', mktComm:'تسويق:',
    base:'أساسي', delete:'حذف الموظف',
    modalTitle:'➕ إضافة موظف جديد',
    lblName:'الاسم الكامل *', lblRole:'الدور الوظيفي',
    lblBc:'عمولة البروكر ($)', lblMc:'عمولة التسويق ($)',
    lblEmail:'البريد الإلكتروني', lblBranch:'الفرع',
    pendingInfo:'ℹ️ سيتم إضافة الموظف كـ <strong>قيد الانتظار</strong> — يحتاج اعتماد المدير المالي.',
    btnCancel:'إلغاء', btnAdd:'إضافة ←',
    errName:'يرجى إدخال الاسم',
    confirmDelete:'حذف الموظف: ',
    optSelectBranch:'— اختر الفرع —',
    badgeApproved:'✅ معتمد', badgePending:'⏳ قيد الانتظار', badgeRejected:'❌ مرفوض',
    approveLink:'اعتماد الآن ←',
  },
  en: {
    tbTitle:'Employees', topbarAdd:'➕ Add Employee',
    panelTitle:'👥 Employees List',
    optAll:'All Roles', optBroker:'🏦 Broker', optMkt:'📢 Internal Marketer',
    optExt:'🌐 External Marketer', optOther:'📋 Other',
    thName:'Employee', thBranch:'Branch', thComm:'Role', thStatus:'Status', thAct:'Actions',
    loading:'Loading...', empty:'No employees found',
    approved:'approved employee', pending:'awaiting approval',
    brokerComm:'Broker:', mktComm:'Marketing:',
    base:'Base', delete:'Delete Employee',
    modalTitle:'➕ Add New Employee',
    lblName:'Full Name *', lblRole:'Job Role',
    lblBc:'Broker Commission ($)', lblMc:'Marketing Commission ($)',
    lblEmail:'Email Address', lblBranch:'Branch',
    pendingInfo:'ℹ️ Employee will be added as <strong>Pending</strong> — requires Finance Admin approval.',
    btnCancel:'Cancel', btnAdd:'Add ←',
    errName:'Please enter a name',
    confirmDelete:'Delete employee: ',
    optSelectBranch:'— Select Branch —',
    badgeApproved:'✅ Approved', badgePending:'⏳ Pending', badgeRejected:'❌ Rejected',
    approveLink:'Approve Now ←',
  }
};
function empL()    { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function emp(key)  { const l = empL(); return EMP[l]?.[key] ?? EMP.ar[key] ?? key; }

function empApplyLang() {
  const idMap = {
    'emp-topbar-add-btn': 'topbarAdd',
    'emp-th-name':'thName', 'emp-th-branch':'thBranch',
    'emp-th-comm':'thComm', 'emp-th-status':'thStatus', 'emp-th-act':'thAct',
    'emp-opt-all':'optAll', 'emp-opt-broker':'optBroker', 'emp-opt-mkt':'optMkt',
    'emp-opt-ext':'optExt', 'emp-opt-other':'optOther',
    'ae-modal-title':'modalTitle', 'ae-lbl-name':'lblName', 'ae-lbl-role':'lblRole',
    'ae-lbl-bc':'lblBc', 'ae-lbl-mc':'lblMc',
    'ae-lbl-email':'lblEmail', 'ae-lbl-branch':'lblBranch',
    'ae-opt-broker':'optBroker', 'ae-opt-mkt':'optMkt',
    'ae-opt-ext':'optExt', 'ae-opt-other':'optOther',
    'ae-btn-cancel':'btnCancel', 'ae-btn-add':'btnAdd',
  };
  Object.entries(idMap).forEach(([id, key]) => {
    const el = document.getElementById(id); if (el) el.textContent = emp(key);
  });
  // Panel title (preserves child count span)
  const pt = document.getElementById('emp-panel-title');
  if (pt) { const cs = document.getElementById('emp-count'); pt.textContent = emp('panelTitle') + ' '; if (cs) pt.appendChild(cs); }
  // Pending info uses HTML
  const pi = document.getElementById('ae-pending-info');
  if (pi) pi.innerHTML = emp('pendingInfo');
  // Topbar title
  const tb = document.querySelector('.tb-title'); if (tb) tb.textContent = emp('tbTitle');
  // Approve link
  const al = document.getElementById('emp-approve-link'); if (al) al.textContent = emp('approveLink');
  // Re-render if data already loaded
  if (_empCache.length) { buildBranchChips(); renderEmps(currentEmps()); }
}
const _empOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_empOrigApplyLang) _empOrigApplyLang(lang);
  empApplyLang();
};

let _empCache = [];

const roleLabels = {broker:'🏦 بروكر', marketing:'📢 مسوّق', broker_marketer:'🔁 بروكر ومسوّق', external:'🌐 مسوّق خارجي', other:'📋 أخرى'};
const roleLabelEn = {broker:'🏦 Broker', marketing:'📢 Marketer', broker_marketer:'🔁 Broker + Marketer', external:'🌐 External Marketer', other:'📋 Other'};
function roleBadge(r){return r==='broker'?'badge-blue':r==='marketing'?'badge-green':r==='broker_marketer'?'badge-purple':r==='external'?'badge-orange':'badge-gray';}
const statusBadge = s => ({
  approved: `<span class="badge badge-green">${emp('badgeApproved')}</span>`,
  pending:  `<span class="badge badge-orange">${emp('badgePending')}</span>`,
  rejected: `<span class="badge badge-red">${emp('badgeRejected')}</span>`,
}[s] || s);

function renderEmps(emps) {
  const isEn = empL() === 'en';
  const rl = isEn ? roleLabelEn : roleLabels;
  document.getElementById('emp-count').textContent = emps.filter(e=>e.status==='approved').length + ' ' + emp('approved');
  document.getElementById('emp-tbody').innerHTML = emps.map(e => `
    <tr style="${e.status==='pending'?'opacity:.82;border-right:3px solid var(--or)':''}">
      <td>
        <div style="font-weight:700;font-size:13px">${esc(e.name)}</div>
        <div style="font-size:10px;color:var(--mu);margin-top:2px">${esc(rl[e.role] || e.role)}</div>
        ${e.email ? `<div style="font-size:10px;color:var(--mu);direction:ltr">${esc(e.email)}</div>` : ''}
      </td>
      <td style="font-size:12px;color:var(--mu)">${esc(isEn?(e.branch?.name_en||e.branch?.name_ar||'—'):(e.branch?.name_ar || '—'))}</td>
      <td><span class="badge ${roleBadge(e.role)}" style="font-size:10px">${esc(rl[e.role] || e.role)}</span></td>
      <td>${statusBadge(e.status)}</td>
      <td style="text-align:center">
        ${!e.is_base
          ? `<button class="icon-btn danger" onclick="deleteEmp(${e.id})" data-name="${esc(e.name)}" title="${emp('delete')}">🗑</button>`
          : `<span style="font-size:10px;color:var(--mu)">${emp('base')}</span>`}
      </td>
    </tr>`).join('') || `<tr><td colspan="5" style="text-align:center;padding:40px;color:var(--mu)">${emp('empty')}</td></tr>`;
}

let _branchFilter = '';
function currentEmps() {
  if (_branchFilter === '')     return _empCache;
  if (_branchFilter === 'none') return _empCache.filter(e => !(e.branch && e.branch.id));
  return _empCache.filter(e => e.branch && String(e.branch.id) === String(_branchFilter));
}
function buildBranchChips() {
  const box = document.getElementById('emp-branch-chips'); if (!box) return;
  const isEn = empL() === 'en';
  const counts = {}; let noBranch = 0;
  (_empCache || []).forEach(e => {
    if (e.branch && e.branch.id) {
      const k = e.branch.id;
      if (!counts[k]) counts[k] = { n: 0, name: isEn ? (e.branch.name_en || e.branch.name_ar) : (e.branch.name_ar || e.branch.name_en), cc: !!e.branch.is_call_center };
      counts[k].n++;
    } else noBranch++;
  });
  let html = `<button class="emp-chip ${_branchFilter===''?'on':''}" onclick="filterByBranch('')">🏢 ${isEn?'All':'الكل'} <b>${(_empCache||[]).length}</b></button>`;
  Object.keys(counts).sort((a,b)=>counts[b].n-counts[a].n).forEach(k => {
    html += `<button class="emp-chip ${String(_branchFilter)===String(k)?'on':''}" onclick="filterByBranch('${k}')">${counts[k].cc?'📞 ':''}${esc(counts[k].name)} <b>${counts[k].n}</b></button>`;
  });
  if (noBranch > 0) html += `<button class="emp-chip ${_branchFilter==='none'?'on':''}" onclick="filterByBranch('none')">— ${isEn?'No branch':'بدون فرع'} <b>${noBranch}</b></button>`;
  box.innerHTML = html;
}
function filterByBranch(id) { _branchFilter = id; buildBranchChips(); renderEmps(currentEmps()); }

async function loadEmps() {
  const role = document.getElementById('f-role').value;
  const params = role ? `?role=${role}` : '';
  const r = await api('GET', '/employees'+params);
  if (!r.success) return;

  _empCache = r.data;

  // Pending banner
  if (r.pending_count > 0) {
    document.getElementById('pending-banner')?.style && (document.getElementById('pending-banner').style.display='block');
    const pt = document.getElementById('pending-text');
    if (pt) pt.textContent = r.pending_count + ' ' + emp('pending');
  }

  buildBranchChips();
  renderEmps(currentEmps());
}

async function loadBranches() {
  const r = await api('GET', '/branches');
  if (!r.success) return;
  const sel = document.getElementById('ae-branch');
  const isEn = empL() === 'en';
  sel.innerHTML = `<option value="">${emp('optSelectBranch')}</option>`;
  r.data.forEach(b => { const o=document.createElement('option'); o.value=b.id; o.textContent=isEn?(b.name_en||b.name_ar):b.name_ar; sel.appendChild(o); });
}

async function addEmployee() {
  const name = document.getElementById('ae-name').value.trim();
  if (!name) { document.getElementById('add-emp-err').textContent=emp('errName'); document.getElementById('add-emp-err').classList.add('show'); return; }

  const r = await api('POST', '/employees', {
    name,
    email:                  document.getElementById('ae-email').value || null,
    role:                   document.getElementById('ae-role').value,
    branch_id:              parseInt(document.getElementById('ae-branch').value) || null,
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
  if (!confirm(emp('confirmDelete') + name + '?')) return;
  const r = await api('DELETE', `/employees/${id}`);
  if (r.success) { toast(r.message, 'success'); loadEmps(); }
  else toast(r.message, 'error');
}

empApplyLang();
loadEmps();
loadBranches();
</script>
@endpush
