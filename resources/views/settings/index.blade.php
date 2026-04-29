@extends('layouts.app')
@section('title','التعريفات')
@section('page-title','التعريفات والإعدادات')
@section('content')

@if(!auth()->user()?->isFinanceAdmin())
<div class="alert alert-warning show" style="margin-bottom:16px">🔒 هذه الصفحة للمدير المالي فقط.</div>
@endif

<div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
  <!-- Account Types -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">📋 أنواع الحسابات / Account Types</div></div>
    <div class="panel-body" id="list-account_types" style="max-height:250px;overflow-y:auto;padding:8px 16px"></div>
    @if(auth()->user()?->isFinanceAdmin())
    <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px">
      <input type="text" id="new-en-account_types" class="form-control" placeholder="English" style="flex:1">
      <input type="text" id="new-ar-account_types" class="form-control" placeholder="عربي" style="flex:1">
      <button class="btn btn-primary btn-sm" onclick="addItem('account-types','account_types')">+ إضافة</button>
    </div>
    @endif
  </div>

  <!-- Account Statuses -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">🏷️ حالات الحسابات / Account Statuses</div></div>
    <div class="panel-body" id="list-account_statuses" style="max-height:250px;overflow-y:auto;padding:8px 16px"></div>
    @if(auth()->user()?->isFinanceAdmin())
    <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px">
      <input type="text" id="new-en-account_statuses" class="form-control" placeholder="English" style="flex:1">
      <input type="text" id="new-ar-account_statuses" class="form-control" placeholder="عربي" style="flex:1">
      <button class="btn btn-primary btn-sm" onclick="addItem('account-statuses','account_statuses')">+ إضافة</button>
    </div>
    @endif
  </div>

  <!-- Trading Types -->
  <div class="panel">
    <div class="panel-header"><div class="panel-title">💰 أنواع التداول / Trading Types</div></div>
    <div class="panel-body" id="list-trading_types" style="max-height:250px;overflow-y:auto;padding:8px 16px"></div>
    @if(auth()->user()?->isFinanceAdmin())
    <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px">
      <input type="text" id="new-en-trading_types" class="form-control" placeholder="English" style="flex:1">
      <input type="text" id="new-ar-trading_types" class="form-control" placeholder="عربي" style="flex:1">
      <button class="btn btn-primary btn-sm" onclick="addItem('trading-types','trading_types')">+ إضافة</button>
    </div>
    @endif
  </div>

  <!-- Branches (FA only) -->
  @if(auth()->user()?->isFinanceAdmin())
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">🏢 الفروع <span style="font-size:9px;color:var(--or)">— للمدير المالي فقط</span></div>
    </div>
    <div class="panel-body" id="list-branches" style="max-height:250px;overflow-y:auto;padding:8px 16px"></div>
    <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px">
      <input type="text" id="new-branch-ar" class="form-control" placeholder="اسم الفرع بالعربي" style="flex:1">
      <input type="text" id="new-branch-en" class="form-control" placeholder="Branch Name EN" style="flex:1">
      <button class="btn btn-primary btn-sm" onclick="addBranch()">+ إضافة</button>
    </div>
  </div>
  @endif
</div>

<div class="panel" style="margin-bottom:14px">
  <div class="panel-header">
    <div class="panel-title">⚙️ حد العمولات / Commission Limit</div>
  </div>
  <div class="panel-body">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">تفعيل الحد / Enable Limit</label>
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0">
          <label style="position:relative;width:38px;height:20px;cursor:pointer">
            <input type="checkbox" id="limit-enabled" style="opacity:0;width:0;height:0"
                   onchange="saveLimit()">
            <span id="sw-limit" style="position:absolute;inset:0;background:rgba(255,255,255,.1);
              border-radius:10px;transition:.2s;cursor:pointer"></span>
          </label>
          <span id="limit-status" style="font-size:12px;color:var(--mu)">جاري التحميل...</span>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label">الحد الأقصى للعمولة ($) / Max Commission ($)</label>
        <input type="number" id="limit-amount" class="form-control"
               value="8" min="1" max="50" step="0.5" onchange="saveLimit()">
      </div>
      <div class="form-group">
        <label class="form-label">الحد مع Rebate ($) / Rebate Limit ($)</label>
        <input type="number" id="rebate-limit" class="form-control"
               value="7" min="1" max="50" step="0.5" onchange="saveLimit()">
      </div>
      <div class="form-group">
        <label class="form-label">عدد التحذيرات / Warning Count</label>
        <input type="number" id="warn-count" class="form-control"
               value="3" min="1" max="10" step="1" onchange="saveLimit()">
      </div>
    </div>
    <div id="limit-msg" style="font-size:11px;margin-top:6px;display:none"></div>
  </div>
</div>

@endsection

@push('scripts')
<script>
async function loadSettings() {
  const r = await api('GET', '/settings');
  if (!r.success) return;

  ['account_types','account_statuses','trading_types'].forEach(key => {
    const el = document.getElementById('list-'+key);
    if (!el) return;
    const endpointMap = {account_types:'account-types', account_statuses:'account-statuses', trading_types:'trading-types'};
    el.innerHTML = (r.data[key] || []).map(item =>
      `<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--brd1)">
        <span style="flex:1;font-size:12px;font-weight:600">${item.name_en}</span>
        <span style="color:var(--mu);font-size:11px">${item.name_ar}</span>
        <button class="btn btn-ghost btn-sm" onclick="deleteItem('${endpointMap[key]}',${item.id})">✕</button>
      </div>`
    ).join('') || '<div style="color:var(--mu);font-size:12px;padding:8px">لا توجد عناصر</div>';
  });

  // Branches
  const bEl = document.getElementById('list-branches');
  if (bEl) {
    const br = await api('GET', '/branches');
    if (br.success) {
      bEl.innerHTML = br.data.map(b =>
        `<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--brd1)">
          <span style="flex:1;font-size:12px;font-weight:600">${b.name_ar}</span>
          <span style="color:var(--mu);font-size:11px">${b.name_en}</span>
          <span class="badge badge-blue">${b.code}</span>
        </div>`
      ).join('') || '<div style="color:var(--mu);font-size:12px;padding:8px">لا توجد فروع</div>';
    }
  }
}

async function addItem(endpoint, key) {
  const en = document.getElementById(`new-en-${key}`).value.trim();
  const ar = document.getElementById(`new-ar-${key}`).value.trim();
  if (!en) return;
  const r = await api('POST', `/settings/${endpoint}`, { name_en: en, name_ar: ar || en });
  if (r.success) {
    toast(en + ' ✅ تمت الإضافة', 'success');
    document.getElementById(`new-en-${key}`).value = '';
    document.getElementById(`new-ar-${key}`).value = '';
    loadSettings();
  } else toast(r.message, 'error');
}

async function deleteItem(endpoint, id) {
  if (!confirm('حذف هذا العنصر؟')) return;
  const r = await api('DELETE', `/settings/${endpoint}/${id}`);
  if (r.success) { toast('تم الحذف', 'success'); loadSettings(); }
  else toast(r.message, 'error');
}

async function addBranch() {
  const ar = document.getElementById('new-branch-ar').value.trim();
  const en = document.getElementById('new-branch-en').value.trim();
  if (!ar) return;
  const code = 'B' + String(Math.floor(Math.random()*900)+100);
  const r = await api('POST', '/branches', { code, name_ar: ar, name_en: en || ar });
  if (r.success) {
    toast(ar + ' ✅ تمت الإضافة', 'success');
    document.getElementById('new-branch-ar').value = '';
    document.getElementById('new-branch-en').value = '';
    loadSettings();
  } else toast(r.message, 'error');
}

loadSettings();
loadLimit();

async function loadLimit() {
  const r = await api('GET', '/settings');
  if (!r.success) return;
  const d = r.data || {};
  const enabled = d.commission_limit_enabled != null ? !!d.commission_limit_enabled : true;
  const amount  = d.commission_limit_amount  || 8;
  const rebate  = d.rebate_commission_limit  || 7;
  const warns   = d.commission_warning_count || 3;
  document.getElementById('limit-enabled').checked = enabled;
  document.getElementById('limit-amount').value    = amount;
  document.getElementById('rebate-limit').value    = rebate;
  document.getElementById('warn-count').value      = warns;
  const sw = document.getElementById('sw-limit');
  const st = document.getElementById('limit-status');
  sw.style.background      = enabled ? 'var(--teal)' : 'rgba(255,255,255,.1)';
  st.textContent           = enabled ? 'مفعّل — الحد سيُطبَّق' : 'معطّل — لا حد للعمولة';
  st.style.color           = enabled ? 'var(--gr)' : 'var(--mu)';
}

async function saveLimit() {
  const enabled = document.getElementById('limit-enabled').checked;
  const amount  = parseFloat(document.getElementById('limit-amount').value) || 8;
  const rebate  = parseFloat(document.getElementById('rebate-limit').value) || 7;
  const warns   = parseInt(document.getElementById('warn-count').value)     || 3;
  const sw = document.getElementById('sw-limit');
  const st = document.getElementById('limit-status');
  sw.style.background = enabled ? 'var(--teal)' : 'rgba(255,255,255,.1)';
  st.textContent = enabled ? 'مفعّل' : 'معطّل';
  st.style.color = enabled ? 'var(--gr)' : 'var(--mu)';
  const r = await api('POST', '/settings/commission-limit', {
    enabled, amount, rebate_limit: rebate, warning_count: warns
  });
  const msg = document.getElementById('limit-msg');
  msg.style.display = 'block';
  msg.textContent   = r.success ? '✅ تم الحفظ' : '❌ ' + (r.message||'فشل');
  msg.style.color   = r.success ? 'var(--gr)' : 'var(--re)';
  setTimeout(() => msg.style.display='none', 2500);
}
</script>
@endpush
