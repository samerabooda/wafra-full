@extends('layouts.app')
@section('title','تعديل حساب / Edit Card')
@section('page-title','تعديل حساب موجود / Edit Card')

@section('content')

{{-- ─── Search Panel ───────────────────────────────────────── --}}
<div class="panel" style="max-width:860px;margin-bottom:14px" id="search-panel">
  <div class="panel-header">
    <div class="panel-title">🔍 البحث عن الحساب / Search Card</div>
    <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-sm">← رجوع</a>
  </div>
  <div class="panel-body">
    <div class="form-row">
      <div class="form-group">
        <label class="form-label">رقم الحساب / Account No.</label>
        <input type="text" id="s-ac" class="form-control" placeholder="719750"
               oninput="debounceSearch()" autocomplete="off">
      </div>
      <div class="form-group">
        <label class="form-label">البروكر / Broker</label>
        <select id="s-broker" class="form-control" onchange="searchCards()">
          <option value="">— كل البروكرين —</option>
        </select>
      </div>
      <div class="form-group" style="align-self:flex-end">
        <button class="btn btn-primary" onclick="searchCards()">🔍 بحث</button>
      </div>
    </div>
    <div id="search-results" style="margin-top:12px;display:none">
      <table class="data-table" style="min-width:500px">
        <thead><tr>
          <th>رقم الحساب</th><th>الشهر</th><th>البروكر</th>
          <th>إيداع أولي</th><th>الحالة</th><th></th>
        </tr></thead>
        <tbody id="search-tbody"></tbody>
      </table>
    </div>
  </div>
</div>

{{-- ─── Edit Form Panel ─────────────────────────────────────── --}}
<div class="panel" style="max-width:860px;display:none" id="edit-panel">
  <div class="panel-header">
    <div class="panel-title">✏️ تعديل كارت <span id="edit-ac-title"></span></div>
    <div style="display:flex;gap:8px">
      <button class="btn btn-ghost btn-sm" onclick="backToSearch()">← رجوع للبحث</button>
      <button class="btn btn-primary" onclick="submitEdit()" id="btn-save-edit">💾 حفظ التعديلات</button>
    </div>
  </div>
  <div class="panel-body">

    <div id="edit-err"  style="display:none;padding:10px 14px;border-radius:8px;font-size:12px;
         margin-bottom:12px;background:rgba(224,72,72,.1);border:1px solid rgba(224,72,72,.25);color:var(--re)"></div>
    <div id="edit-warn" style="display:none;padding:10px 14px;border-radius:8px;font-size:12px;
         margin-bottom:12px;background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.3);color:var(--or)"></div>

    {{-- Reason (required for edit) --}}
    <div class="form-group" style="margin-bottom:16px">
      <label class="form-label" style="color:var(--re)">
        سبب التعديل / Reason for Edit <span style="color:var(--re)">*</span>
      </label>
      <input type="text" id="e-reason" class="form-control"
             placeholder="مثال: تصحيح عمولة البروكر / تحديث الإيداع" required>
    </div>

    {{-- SECTION 1: Account Info --}}
    <div class="form-sec">
      <div class="fst" style="font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;
           letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px">
        <span style="width:3px;height:14px;background:var(--teal);border-radius:2px;display:block"></span>
        معلومات الحساب / Account Info
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم الحساب / Account No. *</label>
          <input type="text" id="e-ac" class="form-control" readonly
                 style="background:var(--bg3);opacity:.7">
        </div>
        <div class="form-group">
          <label class="form-label">الشهر / Month *</label>
          <div id="mp-edit"></div>
          <input type="hidden" id="e-month" value="">
        </div>
      </div>
      <div class="form-row-3">
        <div class="form-group">
          <label class="form-label">نوع الحساب / Account Type</label>
          <select id="e-type" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">حالة الحساب / Account Status</label>
          <select id="e-status" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">نوع / New or Sub</label>
          <select id="e-kind" class="form-control">
            <option value="new">New — جديد</option>
            <option value="sub">Sub — فرعي</option>
          </select>
        </div>
      </div>
    </div>

    {{-- SECTION 2: Beneficiaries --}}
    <div class="form-sec" style="margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--brd1)">
      <div class="fst" style="font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;
           letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px">
        <span style="width:3px;height:14px;background:var(--teal);border-radius:2px;display:block"></span>
        المستفيدون / Beneficiaries
        <span style="font-size:9px;padding:1px 6px;border-radius:10px;
          background:rgba(136,146,164,.1);color:var(--mu);border:1px solid var(--brd1)">اختياريون</span>
      </div>

      <div class="add-bar" style="display:flex;gap:6px;flex-wrap:wrap;margin-bottom:12px">
        <button class="add-btn" id="ebtn-broker"   onclick="showEBene('broker')"  >+ بروكر / Broker</button>
        <button class="add-btn" id="ebtn-marketer" onclick="showEBene('marketer')">+ مسوّق / Marketer</button>
        <button class="add-btn" id="ebtn-ext1"     onclick="showEBene('ext1')"    >+ مسوّق خارجي 1</button>
        <button class="add-btn" id="ebtn-ext2"     onclick="showEBene('ext2')"    >+ مسوّق خارجي 2</button>
        <button class="add-btn" id="ebtn-referral" onclick="showEBene('referral')">+ Referral</button>
      </div>

      {{-- Broker row --}}
      <div class="bene-row" id="erow-broker" style="display:none;align-items:flex-end;gap:8px;
           padding:10px 12px;background:rgba(255,255,255,.02);border:1px solid var(--brd1);
           border-radius:8px;margin-bottom:8px">
        <span style="font-size:15px;width:26px;text-align:center;margin-bottom:7px">🧑‍💼</span>
        <div style="flex:1.5">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">البروكر / Broker</div>
          <select id="e-broker" class="form-control" onchange="calcEditTotal()">
            <option value="">— اختر / Select —</option>
          </select>
        </div>
        <div style="flex:1">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">العمولة ($)</div>
          <input type="number" id="e-bc" class="form-control" value="4" min="0" step="0.5" oninput="calcEditTotal()">
        </div>
        <button style="background:none;border:none;color:var(--mu);cursor:pointer;padding:4px 5px;
                border-radius:4px;font-size:11px;margin-bottom:6px"
                onclick="hideEBene('broker')">✕</button>
      </div>

      {{-- Marketer row --}}
      <div class="bene-row" id="erow-marketer" style="display:none;align-items:flex-end;gap:8px;
           padding:10px 12px;background:rgba(255,255,255,.02);border:1px solid var(--brd1);
           border-radius:8px;margin-bottom:8px">
        <span style="font-size:15px;width:26px;text-align:center;margin-bottom:7px">📢</span>
        <div style="flex:1.5">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">مسوّق / Marketer</div>
          <select id="e-mkt" class="form-control" onchange="calcEditTotal()">
            <option value="">— لا يوجد —</option>
          </select>
        </div>
        <div style="flex:1">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">العمولة ($)</div>
          <input type="number" id="e-mc" class="form-control" value="3" min="0" step="0.5" oninput="calcEditTotal()">
        </div>
        <button style="background:none;border:none;color:var(--mu);cursor:pointer;padding:4px 5px;
                border-radius:4px;font-size:11px;margin-bottom:6px"
                onclick="hideEBene('marketer')">✕</button>
      </div>

      {{-- Ext1 row --}}
      <div class="bene-row" id="erow-ext1" style="display:none;align-items:flex-end;gap:8px;
           padding:10px 12px;background:rgba(255,255,255,.02);border:1px solid var(--brd1);
           border-radius:8px;margin-bottom:8px">
        <span style="font-size:15px;width:26px;text-align:center;margin-bottom:7px">🌐</span>
        <div style="flex:1.5">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">مسوّق خارجي 1 / Ext.1</div>
          <select id="e-ext1" class="form-control" onchange="calcEditTotal()">
            <option value="">— لا يوجد —</option>
          </select>
        </div>
        <div style="flex:1">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">ع. خارجي 1 ($)</div>
          <input type="number" id="e-ec1" class="form-control" value="0" min="0" step="0.5" oninput="calcEditTotal()">
        </div>
        <button style="background:none;border:none;color:var(--mu);cursor:pointer;padding:4px 5px;
                border-radius:4px;font-size:11px;margin-bottom:6px"
                onclick="hideEBene('ext1')">✕</button>
      </div>

      {{-- Ext2 row --}}
      <div class="bene-row" id="erow-ext2" style="display:none;align-items:flex-end;gap:8px;
           padding:10px 12px;background:rgba(255,255,255,.02);border:1px solid var(--brd1);
           border-radius:8px;margin-bottom:8px">
        <span style="font-size:15px;width:26px;text-align:center;margin-bottom:7px">🌐</span>
        <div style="flex:1.5">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">مسوّق خارجي 2 / Ext.2</div>
          <select id="e-ext2" class="form-control" onchange="calcEditTotal()">
            <option value="">— لا يوجد —</option>
          </select>
        </div>
        <div style="flex:1">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">ع. خارجي 2 ($)</div>
          <input type="number" id="e-ec2" class="form-control" value="0" min="0" step="0.5" oninput="calcEditTotal()">
        </div>
        <button style="background:none;border:none;color:var(--mu);cursor:pointer;padding:4px 5px;
                border-radius:4px;font-size:11px;margin-bottom:6px"
                onclick="hideEBene('ext2')">✕</button>
      </div>

      {{-- Referral row --}}
      <div class="bene-row" id="erow-referral" style="display:none;align-items:flex-end;gap:8px;
           padding:10px 12px;background:rgba(255,255,255,.02);border:1px solid var(--brd1);
           border-radius:8px;margin-bottom:8px">
        <span style="font-size:15px;width:26px;text-align:center;margin-bottom:7px">↩️</span>
        <div style="flex:1.5">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">رقم حساب الـ Referral</div>
          <input type="text" id="e-ref-acct" class="form-control" placeholder="رقم الحساب الخارجي" oninput="calcEditTotal()">
        </div>
        <div style="flex:1">
          <div style="font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px">عمولة Referral ($)</div>
          <input type="number" id="e-ref-comm" class="form-control" value="1" min="0" step="0.5" oninput="calcEditTotal()">
        </div>
        <button style="background:none;border:none;color:var(--mu);cursor:pointer;padding:4px 5px;
                border-radius:4px;font-size:11px;margin-bottom:6px"
                onclick="hideEBene('referral')">✕</button>
      </div>

      {{-- Rebate toggle --}}
      <div style="display:flex;align-items:center;gap:10px;padding:9px 12px;
           background:rgba(255,255,255,.02);border:1px solid var(--brd1);border-radius:8px;margin-bottom:10px">
        <div style="flex:1">
          <div style="font-size:12px;font-weight:600;color:var(--tx)">↩️ Rebate للعميل</div>
          <div style="font-size:10px;color:var(--mu);margin-top:1px">الحد يصبح $7 بدلاً من $8</div>
        </div>
        <label style="position:relative;width:38px;height:20px;cursor:pointer">
          <input type="checkbox" id="e-rebate-toggle" style="opacity:0;width:0;height:0"
                 onchange="toggleEditRebate()">
          <span id="e-sw" style="position:absolute;inset:0;background:rgba(255,255,255,.1);
            border-radius:10px;transition:.2s;cursor:pointer"></span>
        </label>
      </div>

      {{-- Rebate amount --}}
      <div id="e-rebate-panel" style="display:none;background:rgba(124,110,238,.06);border:1px solid rgba(124,110,238,.2);
           border-radius:9px;padding:13px;margin-bottom:10px">
        <div style="font-size:11px;font-weight:700;color:#9D98E8;margin-bottom:8px">Rebate Amount</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">مبلغ الـ Rebate ($)</label>
            <input type="number" id="e-rebate" class="form-control" value="1" min="0" max="5" step="0.5" oninput="calcEditTotal()">
          </div>
        </div>
      </div>

      {{-- Commission summary --}}
      <div style="background:var(--bg3);border:1px solid var(--brd2);border-radius:9px;padding:13px 15px;margin-top:10px">
        <div style="font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:8px">ملخص العمولات / Summary</div>
        <div id="e-comm-rows"></div>
        <div style="height:1px;background:var(--brd1);margin:6px 0"></div>
        <div style="display:flex;justify-content:space-between;font-size:13px;font-weight:700">
          <span>الإجمالي / Total</span>
          <span id="e-comm-total" style="font-family:monospace;color:var(--gr)">$0.00</span>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:11px;margin-top:4px;
             padding-top:6px;border-top:1px solid var(--brd1)">
          <span style="color:var(--mu)">الشركة / Company</span>
          <span id="e-comm-company" style="font-family:monospace;color:var(--pri2)">$8.00</span>
        </div>
        <div id="e-comm-note" style="font-size:10px;margin-top:6px;display:flex;align-items:center;gap:4px">
          <span style="color:var(--gr)">✓</span>
          <span style="color:var(--mu)">ضمن الحد ($8.00)</span>
        </div>
      </div>
    </div>

    {{-- SECTION 3: Deposits --}}
    <div>
      <div class="fst" style="font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;
           letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px">
        <span style="width:3px;height:14px;background:var(--teal);border-radius:2px;display:block"></span>
        الإيداعات / Deposits
      </div>
      <div class="form-row-3">
        <div class="form-group">
          <label class="form-label">إيداع أولي / Initial Deposit ($)</label>
          <input type="number" id="e-ini" class="form-control" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">إيداع شهري / Monthly Deposit ($)</label>
          <input type="number" id="e-mon" class="form-control" min="0">
        </div>
        <div class="form-group">
          <label class="form-label">ملاحظات / Notes</label>
          <input type="text" id="e-notes" class="form-control" placeholder="اختياري">
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
let editCardId    = null;
let editWarnCount = 0;
const EVIS = { broker:false, marketer:false, ext1:false, ext2:false, referral:false };
let searchTimer;

// ── Search ─────────────────────────────────────────────────
function debounceSearch() {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(searchCards, 380);
}

async function searchCards() {
  const ac     = document.getElementById('s-ac').value.trim();
  const broker = document.getElementById('s-broker').value;
  if (!ac && !broker) return;

  const params = new URLSearchParams({ per_page: 20 });
  if (ac)     params.set('search', ac);
  if (broker) params.set('broker_id', broker);

  const r = await api('GET', '/cards?' + params);
  const tbody = document.getElementById('search-tbody');
  const panel = document.getElementById('search-results');
  panel.style.display = 'block';

  const cards = r.data?.data || r.data || [];
  if (!cards.length) {
    tbody.innerHTML = '<tr><td colspan="6" style="text-align:center;padding:20px;color:var(--mu)">لا توجد نتائج</td></tr>';
    return;
  }

  tbody.innerHTML = cards.map(c => `
    <tr>
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="color:var(--mu);font-size:11px">${c.month}</td>
      <td>${c.broker?.name || '—'}</td>
      <td class="mono c-green">${fmt(c.initial_deposit)}</td>
      <td><span class="badge ${c.status==='modified'?'badge-mod':'badge-new'}">${c.status}</span></td>
      <td>
        <button class="btn btn-sm btn-ghost" onclick="loadCardForEdit(${c.id})">✏️ تعديل</button>
      </td>
    </tr>`).join('');
}

// ── Load card into edit form ────────────────────────────────
async function loadCardForEdit(id) {
  const r = await api('GET', `/cards/${id}`);
  if (!r.success) { toast('فشل تحميل الكارت', 'error'); return; }
  const c = r.data;
  editCardId = id;

  document.getElementById('edit-ac-title').textContent = '#' + c.account_number;
  document.getElementById('e-ac').value    = c.account_number;
  document.getElementById('e-kind').value  = c.account_kind || 'new';
  document.getElementById('e-ini').value   = c.initial_deposit  || 0;
  document.getElementById('e-mon').value   = c.monthly_deposit  || 0;
  document.getElementById('e-notes').value = c.notes || '';

  // Set month
  document.getElementById('e-month').value = c.month || '';

  // Load beneficiaries
  if (c.broker_id) {
    showEBene('broker');
    document.getElementById('e-broker').value = c.broker_id;
    document.getElementById('e-bc').value     = c.broker_commission || 0;
  }
  if (c.marketer_id) {
    showEBene('marketer');
    document.getElementById('e-mkt').value = c.marketer_id;
    document.getElementById('e-mc').value  = c.marketer_commission || 0;
  }
  if (c.ext_marketer1_id) {
    showEBene('ext1');
    document.getElementById('e-ext1').value = c.ext_marketer1_id;
    document.getElementById('e-ec1').value  = c.ext_commission1 || 0;
  }
  if (c.ext_marketer2_id) {
    showEBene('ext2');
    document.getElementById('e-ext2').value = c.ext_marketer2_id;
    document.getElementById('e-ec2').value  = c.ext_commission2 || 0;
  }
  if (c.referral_account) {
    showEBene('referral');
    document.getElementById('e-ref-acct').value = c.referral_account;
    document.getElementById('e-ref-comm').value = c.referral_commission || 0;
  }
  if (c.has_rebate) {
    document.getElementById('e-rebate-toggle').checked = true;
    toggleEditRebate();
    document.getElementById('e-rebate').value = c.rebate_amount || 0;
  }

  // Show edit panel, hide search
  document.getElementById('search-panel').style.display = 'none';
  document.getElementById('edit-panel').style.display   = 'block';
  calcEditTotal();
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

function backToSearch() {
  editCardId = null;
  editWarnCount = 0;
  Object.keys(EVIS).forEach(k => hideEBene(k));
  document.getElementById('e-rebate-toggle').checked = false;
  document.getElementById('e-rebate-panel').style.display = 'none';
  document.getElementById('e-sw').style.background = 'rgba(255,255,255,.1)';
  document.getElementById('e-reason').value = '';
  document.getElementById('edit-err').style.display  = 'none';
  document.getElementById('edit-warn').style.display = 'none';
  document.getElementById('search-panel').style.display = 'block';
  document.getElementById('edit-panel').style.display   = 'none';
}

// ── Beneficiary show/hide ───────────────────────────────────
function showEBene(k) {
  EVIS[k] = true;
  document.getElementById('erow-'+k).style.display = 'flex';
  document.getElementById('ebtn-'+k).style.display = 'none';
  calcEditTotal();
}
function hideEBene(k) {
  EVIS[k] = false;
  document.getElementById('erow-'+k).style.display = 'none';
  document.getElementById('ebtn-'+k).style.display = 'inline-flex';
  const inputs = document.querySelectorAll('#erow-'+k+' input[type=number]');
  inputs.forEach(el => el.value = 0);
  const sels = document.querySelectorAll('#erow-'+k+' select');
  sels.forEach(el => el.value = '');
  calcEditTotal();
}

function toggleEditRebate() {
  const on = document.getElementById('e-rebate-toggle').checked;
  document.getElementById('e-rebate-panel').style.display = on ? 'block' : 'none';
  document.getElementById('e-sw').style.background = on ? 'var(--teal)' : 'rgba(255,255,255,.1)';
  if (!on) document.getElementById('e-rebate').value = 0;
  calcEditTotal();
}

// ── Commission calculation ──────────────────────────────────
function calcEditTotal() {
  const hasReb = document.getElementById('e-rebate-toggle').checked;
  const limit  = hasReb ? 7 : 8;

  const vals = [
    { lbl:'بروكر',   val: EVIS.broker   ? +document.getElementById('e-bc')?.value||0    : 0, color:'var(--gr)' },
    { lbl:'مسوّق',   val: EVIS.marketer ? +document.getElementById('e-mc')?.value||0    : 0, color:'var(--gr)' },
    { lbl:'خارجي 1', val: EVIS.ext1     ? +document.getElementById('e-ec1')?.value||0   : 0, color:'var(--gr)' },
    { lbl:'خارجي 2', val: EVIS.ext2     ? +document.getElementById('e-ec2')?.value||0   : 0, color:'var(--gr)' },
    { lbl:'Referral',val: EVIS.referral ? +document.getElementById('e-ref-comm')?.value||0:0, color:'var(--or)' },
    { lbl:'Rebate',  val: hasReb        ? +document.getElementById('e-rebate')?.value||0 :0, color:'#9D98E8'   },
  ];

  const total   = vals.reduce((s, v) => s + v.val, 0);
  const company = Math.max(0, limit - total);
  const active  = vals.filter(v => v.val > 0);

  document.getElementById('e-comm-rows').innerHTML = active.length
    ? active.map(v => `<div style="display:flex;justify-content:space-between;padding:4px 0;
         border-bottom:1px solid var(--brd1);font-size:12px">
         <span style="color:var(--mu)">${v.lbl}</span>
         <span style="font-family:monospace;font-size:11px;font-weight:600;color:${v.color}">$${v.val.toFixed(2)}</span>
       </div>`).join('')
    : '<div style="font-size:11px;color:var(--mu);padding:4px 0">لا مستفيدون — للشركة</div>';

  const totEl = document.getElementById('e-comm-total');
  totEl.textContent = '$' + total.toFixed(2);
  totEl.style.color = total > limit ? 'var(--re)' : total === limit ? 'var(--or)' : 'var(--gr)';
  document.getElementById('e-comm-company').textContent = '$' + company.toFixed(2);

  const note = document.getElementById('e-comm-note');
  const lbl  = hasReb ? `حد Rebate ($${limit})` : `الحد ($${limit})`;
  if (total > limit)
    note.innerHTML = `<span style="color:var(--re)">⚠</span><span style="color:var(--re)">تجاوز ${lbl}</span>`;
  else if (total === limit)
    note.innerHTML = `<span style="color:var(--or)">!</span><span style="color:var(--or)">عند الحد ${lbl}</span>`;
  else
    note.innerHTML = `<span style="color:var(--gr)">✓</span><span style="color:var(--mu)">ضمن ${lbl}</span>`;
}

// ── Submit edit ─────────────────────────────────────────────
async function submitEdit() {
  document.getElementById('edit-err').style.display  = 'none';
  document.getElementById('edit-warn').style.display = 'none';

  const reason = document.getElementById('e-reason').value.trim();
  if (!reason) {
    const el = document.getElementById('edit-err');
    el.textContent = 'يرجى كتابة سبب التعديل / Reason is required';
    el.style.display = 'block';
    return;
  }

  const btn = document.getElementById('btn-save-edit');
  btn.disabled = true;

  const payload = {
    reason,
    month:               document.getElementById('e-month').value,
    account_type_id:     document.getElementById('e-type').value    || null,
    account_status_id:   document.getElementById('e-status').value  || null,
    account_kind:        document.getElementById('e-kind').value,
    broker_id:           EVIS.broker   ? document.getElementById('e-broker').value || null : null,
    broker_commission:   EVIS.broker   ? +document.getElementById('e-bc').value    : 0,
    marketer_id:         EVIS.marketer ? document.getElementById('e-mkt').value || null  : null,
    marketer_commission: EVIS.marketer ? +document.getElementById('e-mc').value    : 0,
    ext_marketer1_id:    EVIS.ext1     ? document.getElementById('e-ext1').value || null : null,
    ext_commission1:     EVIS.ext1     ? +document.getElementById('e-ec1').value   : 0,
    ext_marketer2_id:    EVIS.ext2     ? document.getElementById('e-ext2').value || null : null,
    ext_commission2:     EVIS.ext2     ? +document.getElementById('e-ec2').value   : 0,
    referral_account:    EVIS.referral ? document.getElementById('e-ref-acct').value.trim() : null,
    referral_commission: EVIS.referral ? +document.getElementById('e-ref-comm').value : 0,
    has_rebate:          document.getElementById('e-rebate-toggle').checked,
    rebate_amount:       document.getElementById('e-rebate-toggle').checked ? +document.getElementById('e-rebate').value : 0,
    initial_deposit:     +document.getElementById('e-ini').value || 0,
    monthly_deposit:     +document.getElementById('e-mon').value || 0,
    notes:               document.getElementById('e-notes').value,
  };

  const r = await api('PUT', `/cards/${editCardId}`, payload,
                       { 'X-Commission-Warning-Count': editWarnCount });

  btn.disabled = false;

  if (r.success) {
    toast('✅ تم حفظ التعديلات بنجاح', 'success');
    editWarnCount = 0;
    setTimeout(() => window.location.href = '{{ route("cards.index") }}', 800);
    return;
  }

  if (r.warning) {
    editWarnCount = r.warning_number;
    const w = document.getElementById('edit-warn');
    w.style.display = 'block';
    w.innerHTML = `⚠ تحذير ${editWarnCount}: ${r.message}
      <button onclick="forceEditSave()" style="margin-right:8px;padding:3px 12px;border-radius:5px;
        background:var(--or);color:white;border:none;cursor:pointer;font-family:'Tajawal',sans-serif;font-size:11px">
        تأكيد المتابعة ←
      </button>`;
    return;
  }

  if (r.blocked) {
    const e = document.getElementById('edit-err');
    e.textContent = '🚫 ' + r.message;
    e.style.display = 'block';
    return;
  }

  const e = document.getElementById('edit-err');
  e.textContent = r.message || (r.errors ? Object.values(r.errors).flat()[0] : 'فشل الحفظ');
  e.style.display = 'block';
}

function forceEditSave() {
  editWarnCount++;
  submitEdit();
}

// ── Init ───────────────────────────────────────────────────
async function init() {
  // Load brokers for search filter
  const emps = await api('GET', '/employees?status=approved');
  if (emps.success) {
    const brokerSel = document.getElementById('s-broker');
    const editSels  = ['e-broker','e-mkt','e-ext1','e-ext2'];

    emps.data.forEach(e => {
      // Search filter — all employees
      const o = document.createElement('option');
      o.value = e.id;
      o.textContent = e.name + (e.branch?.name_ar ? ' — ' + e.branch.name_ar : '');
      brokerSel.appendChild(o);
    });

    // Edit form selects — by role
    editSels.forEach(selId => {
      const sel = document.getElementById(selId);
      if (!sel) return;
      emps.data.forEach(e => {
        const o = document.createElement('option');
        o.value = e.id;
        o.textContent = e.name + (e.branch?.name_ar ? ' — ' + e.branch.name_ar : '');
        sel.appendChild(o);
      });
    });
  }

  // Load lookup selects
  const settings = await api('GET', '/settings');
  if (settings.success) {
    const d = settings.data;
    ['e-type'].forEach((id, i) => {
      const sel = document.getElementById(id);
      if (!sel) return;
      const arr = [d.account_types, d.account_statuses][i] || [];
      sel.innerHTML = '<option value="">— اختياري —</option>';
      arr.forEach(item => {
        const o = document.createElement('option');
        o.value = item.id;
        o.textContent = (item.name_ar||'') + ' / ' + (item.name_en||'');
        sel.appendChild(o);
      });
    });
    // Status select
    const sSel = document.getElementById('e-status');
    if (sSel) {
      sSel.innerHTML = '<option value="">— اختياري —</option>';
      (d.account_statuses||[]).forEach(item => {
        const o = document.createElement('option');
        o.value = item.id;
        o.textContent = (item.name_ar||'') + ' / ' + (item.name_en||'');
        sSel.appendChild(o);
      });
    }
  }

  // Month picker for edit form
  mountMonthPicker('mp-edit', 'e-month');
}

// If URL has card ID, load directly
(async () => {
  await init();
  const urlId = new URLSearchParams(window.location.search).get('id');
  if (urlId) await loadCardForEdit(parseInt(urlId));
})();
</script>
@endpush
