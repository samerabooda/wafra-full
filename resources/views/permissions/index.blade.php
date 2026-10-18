@extends('layouts.app')
@section('title','Finance Admin Permissions')
@section('page-title','Finance Admin Permissions')
@section('content')
<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
  <div class="panel">
    <div class="panel-header"><div class="panel-title" id="prm-title-excl">🔒 الصلاحيات الحصرية للمدير المالي</div></div>
    <div class="panel-body" id="prm-list-body">
      @foreach([
        ['🏢','إضافة فروع جديدة','المدير المالي هو الوحيد المخوّل بتعريف الفروع','Add New Branches','Only the Finance Admin can define branches'],
        ['👤','إنشاء حسابات المديرين','إنشاء حسابات مديري الفروع وتحديد صلاحياتهم','Create Manager Accounts','Create branch manager accounts and set their permissions'],
        ['✅','اعتماد الموظفين الجدد','مديرو الفروع يضيفون موظفين بحالة قيد الانتظار','Approve New Employees','Branch managers add employees with pending status'],
        ['📋','تعريف أنواع الحسابات','تعريف أنواع الحسابات وحالاتها وأنواع التداول','Define Account Types','Define account types, statuses, and trading types'],
        ['📊','تقارير كل الفروع','عرض تقارير وبيانات جميع الفروع','All-Branch Reports','View reports and data for all branches'],
        ['🌐','تبديل الفروع','عرض بيانات الفروع المختلفة','Switch Branches','View data across different branches'],
      ] as [$ic,$t,$d,$tEn,$dEn])
      <div style="display:flex;align-items:flex-start;gap:12px;padding:12px 0;border-bottom:1px solid var(--brd1)">
        <div style="width:36px;height:36px;border-radius:9px;background:rgba(46,134,171,.15);display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0">{{ $ic }}</div>
        <div>
          <div class="prm-item-title" style="font-size:13px;font-weight:700" data-ar="{{ $t }}" data-en="{{ $tEn }}">{{ $t }}</div>
          <div class="prm-item-desc" style="font-size:11px;color:var(--mu);margin-top:3px" data-ar="{{ $d }}" data-en="{{ $dEn }}">{{ $d }}</div>
          <span class="badge badge-blue prm-badge" style="margin-top:5px">🔒 مدير مالي فقط</span>
        </div>
      </div>
      @endforeach
    </div>
  </div>
  <div class="panel">
    <div class="panel-header"><div class="panel-title" id="prm-title-pending">⏳ اعتماد الموظفين الجدد <span id="pending-badge" class="badge badge-orange" style="display:none;margin-right:8px"></span></div></div>
    <div class="panel-body" id="pending-list"><div style="text-align:center;padding:30px;color:var(--mu)"><div style="font-size:32px;opacity:.3;margin-bottom:8px">✅</div><span id="prm-no-pending">لا يوجد موظفون بانتظار الاعتماد</span></div></div>
  </div>
</div>
@endsection
@push('scripts')
<script>
const PRM = {
  ar: {
    titleExcl:'🔒 الصلاحيات الحصرية للمدير المالي',
    titlePending:'⏳ اعتماد الموظفين الجدد',
    noPending:'لا يوجد موظفون بانتظار الاعتماد',
    badgeFinance:'🔒 مدير مالي فقط',
    pendingBadge:' بانتظار الاعتماد',
    addedBy:'أضافه:',
    btnApprove:'✅ اعتماد',
    btnReject:'❌ رفض',
    promptReject:'سبب الرفض:',
  },
  en: {
    titleExcl:'🔒 Finance Admin Exclusive Permissions',
    titlePending:'⏳ Approve New Employees',
    noPending:'No employees awaiting approval',
    badgeFinance:'🔒 Finance Admin Only',
    pendingBadge:' awaiting approval',
    addedBy:'Added by:',
    btnApprove:'✅ Approve',
    btnReject:'❌ Reject',
    promptReject:'Rejection reason:',
  }
};
function prmL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function prm(k) { return (PRM[prmL()] || PRM.ar)[k] || (PRM.ar)[k] || k; }

function prmApplyLang() {
  const L = prmL();
  const _t = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  _t('prm-title-excl',    prm('titleExcl'));
  _t('prm-title-pending', prm('titlePending'));
  // Update permission list items
  document.querySelectorAll('.prm-item-title').forEach(el => {
    el.textContent = L === 'en' ? (el.dataset.en || el.dataset.ar) : el.dataset.ar;
  });
  document.querySelectorAll('.prm-item-desc').forEach(el => {
    el.textContent = L === 'en' ? (el.dataset.en || el.dataset.ar) : el.dataset.ar;
  });
  document.querySelectorAll('.prm-badge').forEach(el => {
    el.textContent = prm('badgeFinance');
  });
  // Re-render pending list if loaded
  if (_prmPendingData.length) prmRenderPending(_prmPendingData);
  else {
    const np = document.getElementById('prm-no-pending');
    if (np) np.textContent = prm('noPending');
  }
}

const _prmOrig = window.applyLang;
window.applyLang = function(lang) { if (_prmOrig) _prmOrig(lang); prmApplyLang(); };

let _prmPendingData = [];

async function loadPending(){
  const r = await api('GET', '/employees/pending');
  if (!r.success) return;
  _prmPendingData = r.data || [];
  const badge = document.getElementById('pending-badge');
  if (r.count > 0) {
    badge.textContent = r.count + prm('pendingBadge');
    badge.style.display = 'inline-flex';
  }
  prmRenderPending(_prmPendingData);
}

function prmRenderPending(data) {
  const container = document.getElementById('pending-list');
  if (!data.length) {
    container.innerHTML = `<div style="text-align:center;padding:30px;color:var(--mu)"><div style="font-size:32px;opacity:.3;margin-bottom:8px">✅</div><span id="prm-no-pending">${prm('noPending')}</span></div>`;
    return;
  }
  container.innerHTML = data.map(e => `
    <div style="display:flex;align-items:center;gap:12px;padding:12px;margin-bottom:8px;border:1px solid rgba(245,166,35,.3);border-radius:10px;background:rgba(245,166,35,.04)">
      <div style="width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,var(--pri2),var(--pri3));display:flex;align-items:center;justify-content:center;font-size:14px;font-weight:800;color:white;flex-shrink:0">${e.name.charAt(0)}</div>
      <div style="flex:1"><div style="font-size:13px;font-weight:700">${e.name}</div><div style="font-size:11px;color:var(--mu);margin-top:2px">${e.role} · ${e.branch?.name_ar||'—'} · ${prm('addedBy')} ${e.added_by?.name||'—'}</div></div>
      <button class="btn btn-sm" style="background:var(--gr);color:white" onclick="approveEmp(${e.id},'${e.name}')">${prm('btnApprove')}</button>
      <button class="btn btn-sm" style="background:var(--re);color:white" onclick="rejectEmp(${e.id},'${e.name}')">${prm('btnReject')}</button>
    </div>`).join('');
}

async function approveEmp(id,name){
  const r = await api('PUT', `/employees/${id}/approve`);
  if (r.success) { toast(r.message,'success'); _prmPendingData = _prmPendingData.filter(e=>e.id!==id); prmRenderPending(_prmPendingData); }
  else toast(r.message,'error');
}
async function rejectEmp(id,name){
  const reason = prompt(`${prm('promptReject')} ${name}`);
  if (reason === null) return;
  const r = await api('PUT', `/employees/${id}/reject`, {reason});
  if (r.success) { toast(r.message,'success'); _prmPendingData = _prmPendingData.filter(e=>e.id!==id); prmRenderPending(_prmPendingData); }
  else toast(r.message,'error');
}

prmApplyLang();
loadPending();
</script>
@endpush
