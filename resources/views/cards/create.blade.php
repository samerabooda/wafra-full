@extends('layouts.app')
@section('title','كرت عمولة جديد / New Commission Card')
@section('page-title','كرت عمولة جديد / New Commission Card')

@push('styles')
<style>
/* ── Section titles ── */
.form-sec-title{font-size:10px;font-weight:700;color:var(--mu);text-transform:uppercase;
  letter-spacing:.5px;margin-bottom:12px;display:flex;align-items:center;gap:7px}
.form-sec-title::before{content:'';width:3px;height:14px;background:var(--teal);border-radius:2px;flex-shrink:0}
.form-sec{padding-bottom:18px;margin-bottom:18px;border-bottom:1px solid var(--brd1)}
.form-sec:last-of-type{border-bottom:none;margin-bottom:0;padding-bottom:0}

/* ── Beneficiary row ── */
.bene-row{display:flex;align-items:flex-end;gap:8px;padding:10px 12px;
  background:rgba(255,255,255,.02);border:1px solid var(--brd1);border-radius:8px;margin-bottom:8px;
  transition:border .2s}
.bene-row:hover{border-color:rgba(46,134,171,.2)}
.bene-row.active{background:rgba(29,158,117,.04);border-color:rgba(29,158,117,.2)}
.bene-label{font-size:10px;font-weight:700;color:var(--mu2);margin-bottom:5px;
  display:flex;align-items:center;justify-content:space-between}
.bene-icon{font-size:16px;width:28px;text-align:center;flex-shrink:0;margin-bottom:8px}
.bene-remove{background:none;border:none;color:var(--mu);cursor:pointer;
  padding:4px 6px;border-radius:4px;font-size:12px;margin-bottom:5px;flex-shrink:0}
.bene-remove:hover{color:var(--re)}

/* ── Add beneficiary buttons ── */
.add-bene-bar{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.add-bene-btn{display:inline-flex;align-items:center;gap:5px;padding:5px 12px;
  border-radius:20px;font-size:11px;font-weight:600;cursor:pointer;
  border:1px dashed var(--brd2);background:transparent;color:var(--mu);
  font-family:'Tajawal',sans-serif;transition:all .2s}
.add-bene-btn:hover{border-color:var(--teal);color:var(--teal);background:rgba(29,158,117,.06)}
.add-bene-btn.added{display:none}

/* ── Commission summary live ── */
.comm-live{background:var(--bg3);border:1px solid var(--brd2);border-radius:9px;padding:14px 16px;margin-top:12px}
.cl-row{display:flex;align-items:center;justify-content:space-between;padding:4px 0;font-size:12px;
  border-bottom:1px solid var(--brd1)}
.cl-row:last-of-type{border-bottom:none}
.cl-row.company{color:var(--mu)}
.cl-lbl{color:var(--mu)}.cl-val{font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:600}
.cl-div{height:1px;background:var(--brd1);margin:6px 0}
.cl-total{display:flex;align-items:center;justify-content:space-between;font-size:13px;font-weight:700;margin-top:4px}
.cl-limit-note{font-size:10px;margin-top:6px;display:flex;align-items:center;gap:4px}
.limit-ok{color:var(--gr)}.limit-warn{color:var(--or)}.limit-block{color:var(--re)}

/* ── Rebate box ── */
.rebate-panel{background:rgba(83,74,183,.06);border:1px solid rgba(83,74,183,.2);
  border-radius:9px;padding:14px;margin-bottom:10px;display:none}
.rebate-panel.show{display:block}
.rebate-title{font-size:11px;font-weight:700;color:#9D98E8;margin-bottom:10px}

/* ── Toggle switch ── */
.tog-row{display:flex;align-items:center;gap:10px;padding:9px 12px;
  background:rgba(255,255,255,.02);border:1px solid var(--brd1);border-radius:8px;margin-bottom:10px}
.tog-info{flex:1}
.tog-lbl{font-size:12px;font-weight:600;color:var(--tx)}
.tog-sub{font-size:10px;color:var(--mu);margin-top:1px}
.sw{position:relative;width:38px;height:20px;flex-shrink:0;cursor:pointer}
.sw input{opacity:0;width:0;height:0}
.sw-sl{position:absolute;inset:0;background:var(--brd2);border-radius:10px;transition:.2s;cursor:pointer}
.sw-sl::before{content:'';position:absolute;width:14px;height:14px;right:3px;top:3px;
  background:var(--mu);border-radius:50%;transition:.2s}
.sw input:checked+.sw-sl{background:var(--teal)}
.sw input:checked+.sw-sl::before{right:auto;left:3px;background:white}

/* ── Req / Opt chips ── */
.chip-req{display:inline-block;font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;
  background:rgba(226,75,74,.12);color:var(--re);border:1px solid rgba(226,75,74,.2)}
.chip-opt{display:inline-block;font-size:9px;padding:1px 6px;border-radius:10px;
  background:rgba(136,146,164,.1);color:var(--mu);border:1px solid var(--brd1)}

/* ── Warning alert ── */
.warn-alert{padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:12px;display:none}
.warn-1{background:rgba(186,117,23,.1);border:1px solid rgba(186,117,23,.25);color:var(--or)}
.warn-2{background:rgba(200,100,40,.12);border:1px solid rgba(200,100,40,.3);color:#F0855A}
.warn-3{background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)}
.warn-block{background:rgba(163,45,45,.15);border:1px solid var(--re);color:var(--re)}

/* ── Month preview ── */
.month-prev{font-size:11px;color:var(--teal);margin-top:4px;padding:3px 8px;
  background:rgba(29,158,117,.06);border-radius:5px;display:none}
</style>
@endpush

@section('topbar-actions')
<a href="{{ route('cards.index') }}" class="btn btn-ghost btn-sm">← رجوع / Back</a>
@endsection

@section('content')
<div class="panel" style="max-width:900px">
  <div class="panel-header">
    <div class="panel-title">➕ كرت عمولة جديد / New Commission Card</div>
  </div>
  <div class="panel-body">

    {{-- Alerts --}}
    <div id="warn-alert" class="warn-alert"></div>
    <div id="err-alert"  style="display:none;padding:10px 14px;border-radius:8px;font-size:12px;
         margin-bottom:12px;background:rgba(226,75,74,.1);border:1px solid rgba(226,75,74,.25);color:var(--re)"></div>

    {{-- ═══ SECTION 1: Essential (Required) ═══════════════════ --}}
    <div class="form-sec">
      <div class="form-sec-title">
        الحقول الإلزامية / Required Fields
        <span class="chip-req">إلزامي / Required</span>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">رقم الحساب / Account No. <span style="color:var(--re)">*</span></label>
          <input type="text" id="f-ac" class="form-control" placeholder="719750"
                 autocomplete="off" inputmode="numeric" required>
        </div>
        <div class="form-group">
          <label class="form-label">الشهر / Month <span style="color:var(--re)">*</span></label>
          <div id="mp-create"></div>
          <input type="hidden" id="f-month" value="">
        </div>
      </div>

      <div class="form-row-3">
        <div class="form-group">
          <label class="form-label">نوع الحساب / Account Type <span style="color:var(--re)">*</span></label>
          <select id="f-type" class="form-control"><option value="">— اختر / Select —</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">حالة الحساب / Account Status</label>
          <select id="f-status" class="form-control"><option value="">— اختياري —</option></select>
        </div>
        <div class="form-group">
          <label class="form-label">نوع التداول / Trading Type</label>
          <select id="f-trading" class="form-control"><option value="">— اختياري —</option></select>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">نوع / New or Sub</label>
          <select id="f-kind" class="form-control">
            <option value="new">New — جديد</option>
            <option value="sub">Sub — فرعي</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">ملاحظات / Notes <span class="chip-opt">اختياري</span></label>
          <input type="text" id="f-notes" class="form-control" placeholder="اختياري / Optional">
        </div>
      </div>
    </div>

    {{-- ═══ SECTION 2: Beneficiaries (Flexible) ═══════════════ --}}
    <div class="form-sec">
      <div class="form-sec-title">
        المستفيدون من العمولة / Commission Beneficiaries
        <span class="chip-opt">كلهم اختياريون / All optional</span>
      </div>

      {{-- Add beneficiary buttons --}}
      <div class="add-bene-bar">
        <button type="button" class="add-bene-btn" id="btn-add-broker"
                onclick="showBene('broker')">+ بروكر / Broker</button>
        <button type="button" class="add-bene-btn" id="btn-add-marketer"
                onclick="showBene('marketer')">+ مسوّق داخلي / Marketer</button>
        <button type="button" class="add-bene-btn" id="btn-add-ext1"
                onclick="showBene('ext1')">+ مسوّق خارجي 1 / Ext. 1</button>
        <button type="button" class="add-bene-btn" id="btn-add-ext2"
                onclick="showBene('ext2')">+ مسوّق خارجي 2 / Ext. 2</button>
        <button type="button" class="add-bene-btn" id="btn-add-referral"
                onclick="showBene('referral')">+ Referral (رقم حساب خارجي)</button>
      </div>

      {{-- Empty state --}}
      <div id="bene-empty" style="font-size:12px;color:var(--mu);padding:12px;
           background:rgba(255,255,255,.02);border-radius:8px;border:1px dashed var(--brd2);text-align:center">
        لا يوجد مستفيدون — العمولة كاملة للشركة / No beneficiaries — full commission to company
      </div>

      {{-- Broker row --}}
      <div class="bene-row" id="row-broker" style="display:none">
        <span class="bene-icon">🧑‍💼</span>
        <div style="flex:1.5">
          <div class="bene-label">البروكر / Broker</div>
          <select id="f-broker" class="form-control" onchange="calcTotal()">
            <option value="">— اختر / Select —</option>
          </select>
        </div>
        <div style="flex:1">
          <div class="bene-label">العمولة / Commission ($)</div>
          <input type="number" id="f-bc" class="form-control" value="4" min="0" step="0.5"
                 oninput="calcTotal()">
        </div>
        <button type="button" class="bene-remove" onclick="hideBene('broker')" title="إزالة">✕</button>
      </div>

      {{-- Marketer row --}}
      <div class="bene-row" id="row-marketer" style="display:none">
        <span class="bene-icon">📢</span>
        <div style="flex:1.5">
          <div class="bene-label">مسوّق داخلي / Internal Marketer</div>
          <select id="f-mkt" class="form-control" onchange="calcTotal()">
            <option value="">— اختر / Select —</option>
          </select>
        </div>
        <div style="flex:1">
          <div class="bene-label">العمولة / Commission ($)</div>
          <input type="number" id="f-mc" class="form-control" value="3" min="0" step="0.5"
                 oninput="calcTotal()">
        </div>
        <button type="button" class="bene-remove" onclick="hideBene('marketer')" title="إزالة">✕</button>
      </div>

      {{-- Ext1 row --}}
      <div class="bene-row" id="row-ext1" style="display:none">
        <span class="bene-icon">🌐</span>
        <div style="flex:1.5">
          <div class="bene-label">مسوّق خارجي 1 / Ext. Marketer 1</div>
          <select id="f-ext1" class="form-control" onchange="calcTotal()">
            <option value="">— اختر / Select —</option>
          </select>
        </div>
        <div style="flex:1">
          <div class="bene-label">العمولة / Ext. Comm. 1 ($)</div>
          <input type="number" id="f-ec1" class="form-control" value="0" min="0" step="0.5"
                 oninput="calcTotal()">
        </div>
        <button type="button" class="bene-remove" onclick="hideBene('ext1')" title="إزالة">✕</button>
      </div>

      {{-- Ext2 row --}}
      <div class="bene-row" id="row-ext2" style="display:none">
        <span class="bene-icon">🌐</span>
        <div style="flex:1.5">
          <div class="bene-label">مسوّق خارجي 2 / Ext. Marketer 2</div>
          <select id="f-ext2" class="form-control" onchange="calcTotal()">
            <option value="">— اختر / Select —</option>
          </select>
        </div>
        <div style="flex:1">
          <div class="bene-label">العمولة / Ext. Comm. 2 ($)</div>
          <input type="number" id="f-ec2" class="form-control" value="0" min="0" step="0.5"
                 oninput="calcTotal()">
        </div>
        <button type="button" class="bene-remove" onclick="hideBene('ext2')" title="إزالة">✕</button>
      </div>

      {{-- Referral row --}}
      <div class="bene-row" id="row-referral" style="display:none">
        <span class="bene-icon">↩️</span>
        <div style="flex:1.5">
          <div class="bene-label">رقم حساب الـ Referral / Referral Account No.</div>
          <input type="text" id="f-ref-acct" class="form-control" placeholder="رقم الحساب الخارجي"
                 oninput="calcTotal()" autocomplete="off">
        </div>
        <div style="flex:1">
          <div class="bene-label">عمولة الـ Referral ($)</div>
          <input type="number" id="f-ref-comm" class="form-control" value="1" min="0" step="0.5"
                 oninput="calcTotal()">
        </div>
        <button type="button" class="bene-remove" onclick="hideBene('referral')" title="إزالة">✕</button>
      </div>

      {{-- Rebate toggle --}}
      <div class="tog-row">
        <div class="tog-info">
          <div class="tog-lbl">↩️ Rebate للعميل / Client Rebate</div>
          <div class="tog-sub">صاحب الحساب يسترد جزءاً — الحد يصبح $7 بدلاً من $8</div>
        </div>
        <label class="sw">
          <input type="checkbox" id="f-rebate-toggle" onchange="toggleRebate()">
          <span class="sw-sl"></span>
        </label>
      </div>

      {{-- Rebate fields --}}
      <div class="rebate-panel" id="rebate-panel">
        <div class="rebate-title">↩️ Rebate — استرجاع عمولة للعميل</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">مبلغ الـ Rebate / Rebate Amount ($)</label>
            <input type="number" id="f-rebate" class="form-control" value="1" min="0" max="5" step="0.5"
                   oninput="calcTotal()" placeholder="0.00">
          </div>
          <div class="form-group">
            <label class="form-label" style="color:var(--mu2);font-size:10px;margin-top:6px">
              ملاحظة: الـ Rebate يُخصم من سقف العمولة ($7 بدلاً من $8)
            </label>
          </div>
        </div>
      </div>

      {{-- Live commission summary --}}
      <div class="comm-live" id="comm-live">
        <div style="font-size:10px;font-weight:700;color:var(--mu);margin-bottom:8px;
             text-transform:uppercase;letter-spacing:.4px">ملخص العمولات / Commission Summary</div>
        <div id="comm-rows"></div>
        <div class="cl-div"></div>
        <div class="cl-total">
          <span>الإجمالي / Total</span>
          <span id="comm-total-val" style="font-family:'JetBrains Mono',monospace">$0.00</span>
        </div>
        <div class="cl-row company" style="border-top:1px solid var(--brd1);margin-top:6px;padding-top:6px;border-bottom:none">
          <span class="cl-lbl">الشركة / Company share</span>
          <span id="comm-company-val" class="cl-val" style="color:var(--pri2)">$8.00</span>
        </div>
        <div class="cl-limit-note" id="comm-limit-note">
          <span class="limit-ok">✓</span>
          <span id="comm-limit-text">ضمن الحد المسموح ($8.00)</span>
        </div>
      </div>
    </div>

    {{-- ═══ SECTION 3: Deposits (Optional) ═══════════════════ --}}
    <div class="form-sec">
      <div class="form-sec-title">
        الإيداعات / Deposits
        <span class="chip-opt">اختياري / Optional</span>
      </div>
      <div class="form-row-3">
        <div class="form-group">
          <label class="form-label">إيداع أولي / Initial Deposit ($)</label>
          <input type="number" id="f-ini" class="form-control" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label class="form-label">إيداع شهري / Monthly Deposit ($)</label>
          <input type="number" id="f-mon" class="form-control" min="0" placeholder="0">
        </div>
        <div class="form-group">
          <label class="form-label">Forex Commission ($)</label>
          <input type="number" id="f-forex" class="form-control" min="0" placeholder="0">
        </div>
      </div>
    </div>

    {{-- ═══ SUBMIT ══════════════════════════════════════════════ --}}
    <div style="display:flex;gap:10px;align-items:center;padding-top:4px">
      <button type="button" class="btn btn-primary" id="btn-save" onclick="submitCard()" style="padding:10px 28px;font-size:13px">
        💾 حفظ الكرت / Save Card
      </button>
      <button type="button" class="btn btn-ghost" onclick="window.location.href='{{ route('cards.index') }}'">
        إلغاء / Cancel
      </button>
      <span id="save-spinner" style="display:none;font-size:12px;color:var(--mu)">⏳ جارٍ الحفظ...</span>
    </div>

  </div>{{-- panel-body --}}
</div>{{-- panel --}}
@endsection

@push('scripts')
<script>
// ── State ────────────────────────────────────────────────────
let warningCount  = 0;
let pendingOverride = false;
const IS_FA = {{ auth()->user()?->isFinanceAdmin() ? 'true' : 'false' }};

const BENE_VISIBLE = { broker:false, marketer:false, ext1:false, ext2:false, referral:false };

// ── Show/hide beneficiary rows ────────────────────────────────
function showBene(type) {
  BENE_VISIBLE[type] = true;
  document.getElementById('row-' + type).style.display = 'flex';
  document.getElementById('btn-add-' + type).classList.add('added');
  document.getElementById('bene-empty').style.display = 'none';
  calcTotal();
}

function hideBene(type) {
  BENE_VISIBLE[type] = false;
  document.getElementById('row-' + type).style.display = 'none';
  document.getElementById('btn-add-' + type).classList.remove('added');
  // Clear values
  const inputs = document.querySelectorAll('#row-' + type + ' input, #row-' + type + ' select');
  inputs.forEach(el => { if(el.type==='number') el.value=0; else if(el.tagName==='SELECT') el.value=''; });
  // Show empty state if all hidden
  if (!Object.values(BENE_VISIBLE).some(Boolean)) {
    document.getElementById('bene-empty').style.display = 'block';
  }
  calcTotal();
}

function toggleRebate() {
  const on = document.getElementById('f-rebate-toggle').checked;
  const panel = document.getElementById('rebate-panel');
  panel.classList.toggle('show', on);
  if (!on) document.getElementById('f-rebate').value = 0;
  calcTotal();
}

// ── Commission calculation ────────────────────────────────────
function calcTotal() {
  const hasRebate = document.getElementById('f-rebate-toggle').checked;
  const limit     = hasRebate ? {{ Setting::get('rebate_commission_limit', 7.00) ?? 7 }} : {{ Setting::get('commission_limit_amount', 8.00) ?? 8 }};

  const vals = {
    broker:   { label:'بروكر / Broker',        val: BENE_VISIBLE.broker   ? (+document.getElementById('f-bc')?.value||0)       : 0 },
    marketer: { label:'مسوّق / Marketer',       val: BENE_VISIBLE.marketer ? (+document.getElementById('f-mc')?.value||0)       : 0 },
    ext1:     { label:'خارجي 1 / Ext.1',        val: BENE_VISIBLE.ext1     ? (+document.getElementById('f-ec1')?.value||0)      : 0 },
    ext2:     { label:'خارجي 2 / Ext.2',        val: BENE_VISIBLE.ext2     ? (+document.getElementById('f-ec2')?.value||0)      : 0 },
    referral: { label:'Referral',               val: BENE_VISIBLE.referral ? (+document.getElementById('f-ref-comm')?.value||0) : 0 },
    rebate:   { label:'Rebate للعميل',          val: hasRebate             ? (+document.getElementById('f-rebate')?.value||0)  : 0 },
  };

  const total   = Object.values(vals).reduce((s,v) => s + v.val, 0);
  const company = Math.max(0, limit - total);

  // Build rows HTML
  let rows = '';
  for (const [key, {label, val}] of Object.entries(vals)) {
    if (val > 0) {
      const color = key==='rebate' ? 'color:var(--pu)' : key==='referral' ? 'color:var(--or)' : 'color:var(--gr)';
      rows += `<div class="cl-row"><span class="cl-lbl">${label}</span>
               <span class="cl-val" style="${color}">$${val.toFixed(2)}</span></div>`;
    }
  }
  document.getElementById('comm-rows').innerHTML = rows ||
    '<div style="font-size:11px;color:var(--mu);padding:4px 0">لا مستفيدون — العمولة للشركة</div>';

  // Total
  const totEl = document.getElementById('comm-total-val');
  totEl.textContent = '$' + total.toFixed(2);
  totEl.style.color = total > limit ? 'var(--re)' : total === limit ? 'var(--or)' : 'var(--gr)';

  // Company share
  document.getElementById('comm-company-val').textContent = '$' + company.toFixed(2);

  // Limit note
  const noteEl   = document.getElementById('comm-limit-note');
  const textEl   = document.getElementById('comm-limit-text');
  const limitLbl = hasRebate ? `الحد مع Rebate ($${limit})` : `الحد ($${limit})`;
  if (total > limit) {
    noteEl.innerHTML = `<span class="limit-block">⚠</span><span style="color:var(--re)">تجاوز ${limitLbl}</span>`;
  } else if (total === limit) {
    noteEl.innerHTML = `<span class="limit-warn">!</span><span style="color:var(--or)">عند الحد ${limitLbl}</span>`;
  } else {
    noteEl.innerHTML = `<span class="limit-ok">✓</span><span>ضمن ${limitLbl}</span>`;
  }
}

// ── Submit ────────────────────────────────────────────────────
async function submitCard() {
  // Clear alerts
  document.getElementById('warn-alert').style.display = 'none';
  document.getElementById('err-alert').style.display  = 'none';

  // Required fields
  const ac    = document.getElementById('f-ac').value.trim();
  const month = document.getElementById('f-month').value;
  const type  = document.getElementById('f-type').value;
  if (!ac || !month || !type) {
    showErr('يرجى تعبئة الحقول الإلزامية: رقم الحساب، الشهر، نوع الحساب / Fill required fields');
    return;
  }

  const btn = document.getElementById('btn-save');
  btn.disabled = true;
  document.getElementById('save-spinner').style.display = 'inline';

  const payload = {
    account_number:      ac,
    month:               month,
    month_date:          buildMonthDate(month),
    account_type_id:     document.getElementById('f-type').value   || null,
    account_status_id:   document.getElementById('f-status').value  || null,
    trading_type_id:     document.getElementById('f-trading').value || null,
    account_kind:        document.getElementById('f-kind').value,
    notes:               document.getElementById('f-notes').value,
    // Beneficiaries
    broker_id:           BENE_VISIBLE.broker   ? (document.getElementById('f-broker').value||null) : null,
    broker_commission:   BENE_VISIBLE.broker   ? (+document.getElementById('f-bc').value||0)       : 0,
    marketer_id:         BENE_VISIBLE.marketer ? (document.getElementById('f-mkt').value||null)    : null,
    marketer_commission: BENE_VISIBLE.marketer ? (+document.getElementById('f-mc').value||0)       : 0,
    ext_marketer1_id:    BENE_VISIBLE.ext1     ? (document.getElementById('f-ext1').value||null)   : null,
    ext_commission1:     BENE_VISIBLE.ext1     ? (+document.getElementById('f-ec1').value||0)      : 0,
    ext_marketer2_id:    BENE_VISIBLE.ext2     ? (document.getElementById('f-ext2').value||null)   : null,
    ext_commission2:     BENE_VISIBLE.ext2     ? (+document.getElementById('f-ec2').value||0)      : 0,
    // Referral
    referral_account:    BENE_VISIBLE.referral ? document.getElementById('f-ref-acct').value.trim() : null,
    referral_commission: BENE_VISIBLE.referral ? (+document.getElementById('f-ref-comm').value||0) : 0,
    // Rebate
    has_rebate:          document.getElementById('f-rebate-toggle').checked,
    rebate_amount:       document.getElementById('f-rebate-toggle').checked ? (+document.getElementById('f-rebate').value||0) : 0,
    // Deposits
    initial_deposit:     +document.getElementById('f-ini').value   || 0,
    monthly_deposit:     +document.getElementById('f-mon').value   || 0,
    forex_commission:    +document.getElementById('f-forex').value || 0,
  };

  const r = await api('POST', '/cards', payload, {'X-Commission-Warning-Count': warningCount});

  btn.disabled = false;
  document.getElementById('save-spinner').style.display = 'none';

  if (r.success) {
    toast(`✅ تم حفظ الكرت #${r.data?.account_number} — ${r.data?.month}`, 'success');
    warningCount    = 0;
    pendingOverride = false;
    // Reset form
    setTimeout(() => window.location.href = '{{ route("cards.index") }}', 800);
    return;
  }

  // Warning response
  if (r.warning) {
    warningCount = r.warning_number;
    const levels = ['warn-1','warn-2','warn-3'];
    const warnEl = document.getElementById('warn-alert');
    warnEl.className = 'warn-alert ' + (levels[warningCount-1] || 'warn-3');
    warnEl.style.display = 'block';
    const rebateNote = r.has_rebate ? ' [حد Rebate: $7]' : '';
    warnEl.innerHTML = `⚠ تحذير ${warningCount}/${{{ Setting::commissionWarningCount() ?? 3 }}}${rebateNote}: ${r.message}<br>
      <button onclick="forceSubmit()" style="margin-top:6px;padding:4px 12px;border-radius:5px;
        background:var(--or);color:white;border:none;cursor:pointer;font-family:'Tajawal',sans-serif;font-size:11px">
        تأكيد المتابعة رغم التحذير ←
      </button>`;
    return;
  }

  // Hard block
  if (r.blocked) {
    const blockEl = document.getElementById('warn-alert');
    blockEl.className = 'warn-alert warn-block';
    blockEl.style.display = 'block';
    blockEl.textContent = '🚫 ' + r.message;
    return;
  }

  // Other error
  showErr(r.message || (r.errors ? Object.values(r.errors).flat().join(' · ') : 'حدث خطأ'));
}

function forceSubmit() {
  // User confirmed warning — increment and retry
  warningCount++;
  submitCard();
}

function buildMonthDate(monthStr) {
  // "May 2026" → "2026-05-01"
  const EN = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
  const [m, y] = monthStr.split(' ');
  const mn = EN[m];
  if (!mn) return new Date().toISOString().slice(0,10);
  return `${y}-${String(mn).padStart(2,'0')}-01`;
}

function showErr(msg) {
  const el = document.getElementById('err-alert');
  el.textContent = msg; el.style.display = 'block';
  el.scrollIntoView({behavior:'smooth', block:'nearest'});
}

// ── Load options ──────────────────────────────────────────────
async function loadOptions() {
  const [settings, emps] = await Promise.all([
    api('GET', '/settings'),
    api('GET', '/employees?status=approved'),
  ]);

  if (settings.success) {
    ['f-type','f-status','f-trading'].forEach((id,i) => {
      const key = ['account_types','account_statuses','trading_types'][i];
      const sel = document.getElementById(id);
      (settings.data[key] || []).forEach(item => {
        const o = document.createElement('option');
        o.value = item.id;
        o.textContent = (item.name_ar||'') + ' / ' + (item.name_en||'');
        sel.appendChild(o);
      });
    });
  }

  if (emps.success) {
    // Group by branch for broker/marketer selects
    const byBranch = {};
    emps.data.forEach(e => {
      const bn = e.branch?.name_ar || 'عام';
      if (!byBranch[bn]) byBranch[bn] = [];
      byBranch[bn].push(e);
    });

    ['f-broker','f-mkt','f-ext1','f-ext2'].forEach(selId => {
      const sel = document.getElementById(selId);
      Object.entries(byBranch).forEach(([branch, list]) => {
        const grp = document.createElement('optgroup');
        grp.label = branch;
        list.forEach(e => {
          const o = document.createElement('option');
          o.value = e.id;
          o.textContent = e.name;
          grp.appendChild(o);
        });
        sel.appendChild(grp);
      });
    });
  }

  // Mount month picker
  mountMonthPicker('mp-create', 'f-month');
}

document.addEventListener('DOMContentLoaded', loadOptions);
</script>
@endpush
