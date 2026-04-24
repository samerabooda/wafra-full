@extends('layouts.app')
@section('title', 'كرت عمولة جديد')
@section('page-title', 'إنشاء كرت عمولة جديد')

@section('content')
<div class="panel" style="max-width:860px">
  <div class="panel-header">
    <div class="panel-title">➕ كرت عمولة جديد</div>
    <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-sm">← رجوع</a>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    <form id="card-form">
      <!-- Section 1: Account Info -->
      <div class="form-section">
        <div class="form-section-title">معلومات الحساب الأساسية</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">رقم الحساب (AC No.) *</label>
            <input type="text" id="f-ac" class="form-control" placeholder="719750" required>
          </div>
          <div class="form-group">
            <label class="form-label">الشهر *</label>
            <select id="f-month" class="form-control" required>
              <option value="">— اختر الشهر —</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">نوع الحساب</label>
            <select id="f-type" class="form-control">
              <option value="">— اختر —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">حالة الحساب</label>
            <select id="f-status" class="form-control">
              <option value="">— اختر —</option>
            </select>
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">الفرع</label>
            <select id="f-branch" class="form-control"></select>
          </div>
          <div class="form-group">
            <label class="form-label">نوع التداول</label>
            <select id="f-trading" class="form-control">
              <option value="">— اختر —</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label class="form-label">نوع الحساب (New / Sub)</label>
          <select id="f-kind" class="form-control">
            <option value="new">New — جديد</option>
            <option value="sub">Sub — فرعي</option>
          </select>
        </div>
      </div>

      <!-- Section 2: Broker & Marketers -->
      <div class="form-section">
        <div class="form-section-title">البروكر والمسوّقون (من قائمة الموظفين)</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">🧑‍💼 البروكر *</label>
            <select id="f-broker" class="form-control" required></select>
          </div>
          <div class="form-group">
            <label class="form-label">عمولة البروكر ($/lot)</label>
            <input type="number" id="f-broker-comm" class="form-control" value="4" min="0" step="0.5">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">📢 مسوّق داخلي</label>
            <select id="f-marketer" class="form-control"></select>
          </div>
          <div class="form-group">
            <label class="form-label">عمولة المسوّق الداخلي ($/lot)</label>
            <input type="number" id="f-marketer-comm" class="form-control" value="3" min="0" step="0.5">
          </div>
        </div>
        <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:14px;margin-bottom:10px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">🌐 مسوّق خارجي 1</label>
            <select id="f-ext1" class="form-control"></select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">عمولة مسوّق خارجي 1 ($/lot)</label>
            <input type="number" id="f-ext1-comm" class="form-control" value="0" min="0" step="0.5">
          </div>
        </div>
        <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">🌐 مسوّق خارجي 2</label>
            <select id="f-ext2" class="form-control"></select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">عمولة مسوّق خارجي 2 ($/lot)</label>
            <input type="number" id="f-ext2-comm" class="form-control" value="0" min="0" step="0.5">
          </div>
        </div>
      </div>

      <!-- Section 3: Deposits & Commissions -->
      <div class="form-section">
        <div class="form-section-title">الإيداعات والعمولات</div>
        <div class="form-row-3">
          <div class="form-group">
            <label class="form-label">إيداع أولي ($)</label>
            <input type="number" id="f-dep" class="form-control" value="0" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">إيداع شهري ($)</label>
            <input type="number" id="f-mon" class="form-control" value="0" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Forex Commission ($/lot)</label>
            <input type="number" id="f-forex" class="form-control" value="8" min="0">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Futures Commission ($/lot)</label>
            <input type="number" id="f-futures" class="form-control" value="8" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">ملاحظات</label>
            <input type="text" id="f-notes" class="form-control" placeholder="ملاحظات اختيارية">
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px">
        <button type="button" class="btn btn-primary btn-xl" onclick="submitCard()">
          💾 حفظ الكرت
        </button>
        <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-xl">إلغاء</a>
      </div>
    </form>
  </div>
</div>
@endsection

@push('scripts')
<script>
async function loadFormOptions() {
  const [settings, employees, branches] = await Promise.all([
    api('GET', '/settings'),
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
  ]);

  // Months (last 24 months)
  const mSel = document.getElementById('f-month');
  const months = [];
  const now = new Date();
  for (let i = 0; i < 24; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push(d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear());
  }
  months.forEach(m => { const o = document.createElement('option'); o.value=o.textContent=m; mSel.appendChild(o); });

  // Settings lookups
  if (settings.success) {
    const typesSel  = document.getElementById('f-type');
    const statSel   = document.getElementById('f-status');
    const tradeSel  = document.getElementById('f-trading');
    settings.data.account_types.forEach(t    => { const o=document.createElement('option');o.value=t.id;o.textContent=t.name_en+' / '+t.name_ar;typesSel.appendChild(o);  });
    settings.data.account_statuses.forEach(s => { const o=document.createElement('option');o.value=s.id;o.textContent=s.name_en+' / '+s.name_ar;statSel.appendChild(o);   });
    settings.data.trading_types.forEach(t    => { const o=document.createElement('option');o.value=t.id;o.textContent=t.name_en+' / '+t.name_ar;tradeSel.appendChild(o);  });
  }

  // Employees
  if (employees.success) {
    const emps = employees.data;
    ['f-broker','f-marketer','f-ext1','f-ext2'].forEach(id => {
      const sel = document.getElementById(id);
      const isOptional = id !== 'f-broker';
      if (isOptional) { const o=document.createElement('option'); o.value=''; o.textContent='— لا يوجد —'; sel.appendChild(o); }
      emps.forEach(e => {
        const o = document.createElement('option');
        o.value = e.id;
        o.textContent = e.name + (e.role==='external' ? ' 🌐' : e.role==='marketing' ? ' 📢' : ' 🏦');
        sel.appendChild(o);
      });
    });
  }

  // Branches
  if (branches.success) {
    const bSel = document.getElementById('f-branch');
    bSel.innerHTML = '<option value="">— اختر الفرع —</option>';
    branches.data.forEach(b => {
      const o = document.createElement('option');
      o.value = b.id;
      o.textContent = b.name_ar + ' / ' + b.name_en;
      bSel.appendChild(o);
    });
  }
}

async function submitCard() {
  const ac     = document.getElementById('f-ac').value.trim();
  const month  = document.getElementById('f-month').value;
  const broker = document.getElementById('f-broker').value;

  if (!ac || !month || !broker) {
    showAlert('err', '⚠️ يرجى ملء رقم الحساب والشهر والبروكر على الأقل');
    return;
  }

  // Build month_date
  let monthDate = '2025-01-01';
  try { monthDate = new Date('01 '+month).toISOString().slice(0,10); } catch(e){}

  const payload = {
    account_number:     ac,
    month:              month,
    month_date:         monthDate,
    branch_id:          parseInt(document.getElementById('f-branch').value) || null,
    account_type_id:    parseInt(document.getElementById('f-type').value)   || null,
    account_status_id:  parseInt(document.getElementById('f-status').value) || null,
    trading_type_id:    parseInt(document.getElementById('f-trading').value)|| null,
    account_kind:       document.getElementById('f-kind').value,
    broker_id:          parseInt(broker),
    broker_commission:  parseFloat(document.getElementById('f-broker-comm').value) || 0,
    marketer_id:        parseInt(document.getElementById('f-marketer').value) || null,
    marketer_commission:parseFloat(document.getElementById('f-marketer-comm').value) || 0,
    ext_marketer1_id:   parseInt(document.getElementById('f-ext1').value)   || null,
    ext_commission1:    parseFloat(document.getElementById('f-ext1-comm').value) || 0,
    ext_marketer2_id:   parseInt(document.getElementById('f-ext2').value)   || null,
    ext_commission2:    parseFloat(document.getElementById('f-ext2-comm').value) || 0,
    forex_commission:   parseFloat(document.getElementById('f-forex').value)   || 0,
    futures_commission: parseFloat(document.getElementById('f-futures').value) || 0,
    initial_deposit:    parseFloat(document.getElementById('f-dep').value)  || 0,
    monthly_deposit:    parseFloat(document.getElementById('f-mon').value)  || 0,
    notes:              document.getElementById('f-notes').value || null,
  };

  const r = await api('POST', '/cards', payload);
  if (r.success) {
    showAlert('ok', '✅ ' + r.message);
    document.getElementById('f-ac').value = '';
    document.getElementById('f-dep').value = '0';
    document.getElementById('f-mon').value = '0';
    window.scrollTo(0,0);
  } else {
    const errs = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message;
    showAlert('err', '❌ ' + errs);
  }
}

function showAlert(type, msg) {
  const errEl = document.getElementById('alert-err');
  const okEl  = document.getElementById('alert-ok');
  errEl.classList.remove('show'); okEl.classList.remove('show');
  if (type === 'err') { errEl.textContent = msg; errEl.classList.add('show'); }
  else                { okEl.textContent  = msg; okEl.classList.add('show'); }
  window.scrollTo(0, 0);
}

loadFormOptions();
</script>
@endpush
