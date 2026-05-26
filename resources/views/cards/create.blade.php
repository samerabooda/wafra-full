@extends('layouts.app')
@section('title', 'كرت عمولة جديد')
@section('page-title', 'إنشاء كرت عمولة جديد')

@push('styles')
<style>
/* ── Month / Year Picker ──────────────────────────────── */
.mp-wrap { position:relative }
.mp-input-row { display:flex;align-items:center;gap:0 }
.mp-input-row .form-control { cursor:pointer;caret-color:transparent;background:var(--inp-bg) }
.mp-input-row .mp-icon { position:absolute;left:10px;color:var(--mu);pointer-events:none;font-size:14px }
.mp-popup {
  position:absolute;top:calc(100% + 6px);right:0;z-index:999;
  background:var(--surface);border:1px solid var(--border);border-radius:14px;
  box-shadow:0 8px 28px rgba(0,0,0,.25);padding:14px;width:260px;
  animation:mpIn .12s ease
}
@keyframes mpIn { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
.mp-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:12px }
.mp-year-label { font-size:15px;font-weight:800;color:var(--tx) }
.mp-nav { background:none;border:1px solid var(--border);border-radius:8px;width:30px;height:30px;
  cursor:pointer;font-size:16px;color:var(--tx);display:flex;align-items:center;justify-content:center;
  transition:background .15s }
.mp-nav:hover { background:rgba(46,134,171,.12) }
.mp-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:5px }
.mp-month {
  padding:7px 4px;border-radius:8px;border:none;cursor:pointer;font-size:11px;font-weight:600;
  background:var(--surface2);color:var(--tx);transition:all .15s;text-align:center
}
.mp-month:hover { background:rgba(46,134,171,.18);color:var(--pri2) }
.mp-month.mp-selected { background:var(--pri2);color:#fff;box-shadow:0 2px 8px rgba(46,134,171,.4) }
.mp-month.mp-today { outline:2px solid rgba(46,134,171,.35);outline-offset:1px }
/* ── end picker ───────────────────────────────────────── */
</style>
@endpush

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
            {{-- Custom Month/Year Picker --}}
            <div class="mp-wrap" id="mp-wrap">
              <div class="mp-input-row">
                <input type="text" id="f-month" class="form-control" placeholder="اختر الشهر والسنة..."
                       readonly required autocomplete="off" onclick="mpToggle(event)">
                <span class="mp-icon">📅</span>
              </div>
              <div class="mp-popup" id="mp-popup" style="display:none">
                <div class="mp-header">
                  <button type="button" class="mp-nav" onclick="mpShiftYear(-1)">‹</button>
                  <span class="mp-year-label" id="mp-year-lbl">2025</span>
                  <button type="button" class="mp-nav" onclick="mpShiftYear(+1)">›</button>
                </div>
                <div class="mp-grid" id="mp-grid"></div>
              </div>
            </div>
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
        @php $isScopedUser = auth()->user()?->isScopedToBranch(); @endphp
        @if(!$isScopedUser)
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
        @else
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">الفرع</label>
            <div class="form-control" style="background:rgba(46,134,171,.08);color:var(--pri2);font-weight:700">
              {{ auth()->user()->branch?->name_ar ?? 'فرعك المحدد' }}
            </div>
            <input type="hidden" id="f-branch" value="{{ auth()->user()->branch_id }}">
          </div>
          <div class="form-group">
            <label class="form-label">نوع التداول</label>
            <select id="f-trading" class="form-control">
              <option value="">— اختر —</option>
            </select>
          </div>
        </div>
        @endif
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
            <select id="f-broker" class="form-control" required>
              <option value="">— اختر البروكر —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">عمولة البروكر ($/lot)</label>
            <input type="number" id="f-broker-comm" class="form-control" value="4" min="0" step="0.5">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">📢 مسوّق داخلي</label>
            <select id="f-marketer" class="form-control" onchange="onMktrChange()">
              <option value="">— لا يوجد —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">عمولة المسوّق الداخلي ($/lot)</label>
            <input type="number" id="f-marketer-comm" class="form-control" value="0" min="0" step="0.5" disabled>
          </div>
        </div>
        <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:14px;margin-bottom:10px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">🌐 مسوّق خارجي 1</label>
            <select id="f-ext1" class="form-control" onchange="onExt1Change()">
              <option value="">— لا يوجد —</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">عمولة مسوّق خارجي 1 ($/lot)</label>
            <input type="number" id="f-ext1-comm" class="form-control" value="0" min="0" step="0.5" disabled>
          </div>
        </div>
        <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">🌐 مسوّق خارجي 2</label>
            <select id="f-ext2" class="form-control" onchange="onExt2Change()">
              <option value="">— لا يوجد —</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label">عمولة مسوّق خارجي 2 ($/lot)</label>
            <input type="number" id="f-ext2-comm" class="form-control" value="0" min="0" step="0.5" disabled>
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
// ══ Month / Year Picker ═══════════════════════════════════════
const MP_MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const MP_MONTHS_AR = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];

let mpYear     = new Date().getFullYear();
let mpSelected = null; // { year, month (0-based) }

function mpRender() {
  document.getElementById('mp-year-lbl').textContent = mpYear;
  const now  = new Date();
  const grid = document.getElementById('mp-grid');
  grid.innerHTML = MP_MONTHS.map((m, i) => {
    const isSelected = mpSelected && mpSelected.year === mpYear && mpSelected.month === i;
    const isToday    = now.getFullYear() === mpYear && now.getMonth() === i;
    return `<button type="button" class="mp-month${isSelected ? ' mp-selected' : ''}${isToday && !isSelected ? ' mp-today' : ''}"
      onclick="mpSelect(${i})" title="${MP_MONTHS_AR[i]}">${m}</button>`;
  }).join('');
}

function mpToggle(e) {
  e.stopPropagation();
  const popup = document.getElementById('mp-popup');
  if (popup.style.display === 'none') {
    // Open at current selected year or current year
    if (mpSelected) mpYear = mpSelected.year;
    else mpYear = new Date().getFullYear();
    mpRender();
    popup.style.display = 'block';
  } else {
    popup.style.display = 'none';
  }
}

function mpShiftYear(delta) {
  mpYear += delta;
  mpRender();
}

function mpSelect(monthIdx) {
  mpSelected = { year: mpYear, month: monthIdx };
  const val = MP_MONTHS[monthIdx] + ' ' + mpYear;
  document.getElementById('f-month').value = val;
  document.getElementById('mp-popup').style.display = 'none';
}

// Close picker on outside click
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('mp-wrap');
  if (wrap && !wrap.contains(e.target)) {
    document.getElementById('mp-popup').style.display = 'none';
  }
});
// ══ end picker ════════════════════════════════════════════════

// ── Marketer commission field enablers ────────────────────────
function onMktrChange() {
  const has = !!document.getElementById('f-marketer').value;
  const inp = document.getElementById('f-marketer-comm');
  inp.disabled = !has;
  if (!has) inp.value = '0';
}
function onExt1Change() {
  const has = !!document.getElementById('f-ext1').value;
  const inp = document.getElementById('f-ext1-comm');
  inp.disabled = !has;
  if (!has) inp.value = '0';
}
function onExt2Change() {
  const has = !!document.getElementById('f-ext2').value;
  const inp = document.getElementById('f-ext2-comm');
  inp.disabled = !has;
  if (!has) inp.value = '0';
}

// ── Load dropdowns ────────────────────────────────────────────
async function loadFormOptions() {
  const [settings, employees, branches] = await Promise.all([
    api('GET', '/settings'),
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
  ]);

  // Settings lookups
  if (settings.success) {
    const fill = (selId, items) => {
      const sel = document.getElementById(selId);
      if (!sel) return;
      items?.forEach(t => {
        const o = document.createElement('option');
        o.value = t.id;
        o.textContent = (t.name_en || '') + (t.name_ar ? ' / ' + t.name_ar : '');
        sel.appendChild(o);
      });
    };
    fill('f-type',    settings.data.account_types);
    fill('f-status',  settings.data.account_statuses);
    fill('f-trading', settings.data.trading_types);
  }

  // Employees
  if (employees.success) {
    ['f-broker','f-marketer','f-ext1','f-ext2'].forEach(id => {
      const sel = document.getElementById(id);
      if (!sel) return;
      employees.data.forEach(e => {
        const o = document.createElement('option');
        o.value = e.id;
        o.textContent = e.name + (e.role === 'external' ? ' 🌐' : e.role === 'marketing' ? ' 📢' : ' 🏦');
        sel.appendChild(o);
      });
    });
  }

  // Branches (FA only — scoped users have hidden input)
  if (branches.success) {
    const bSel = document.getElementById('f-branch');
    if (bSel && bSel.tagName === 'SELECT') {
      bSel.innerHTML = '<option value="">— اختر الفرع —</option>';
      branches.data.forEach(b => {
        const o = document.createElement('option');
        o.value = b.id;
        o.textContent = b.name_ar + (b.name_en ? ' / ' + b.name_en : '');
        bSel.appendChild(o);
      });
    }
  }
}

// ── Submit ────────────────────────────────────────────────────
async function submitCard() {
  const ac     = document.getElementById('f-ac').value.trim();
  const month  = document.getElementById('f-month').value;
  const broker = document.getElementById('f-broker').value;

  if (!ac || !month || !broker) {
    showAlert('err', '⚠️ يرجى ملء رقم الحساب والشهر والبروكر على الأقل');
    return;
  }

  // Derive month_date from "MMM YYYY"
  const monthMap = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
  const parts    = month.split(' ');
  const mm       = String(monthMap[parts[0]] ?? 1).padStart(2,'0');
  const yyyy     = parts[1] ?? new Date().getFullYear();
  const monthDate = `${yyyy}-${mm}-01`;

  const mktrId = parseInt(document.getElementById('f-marketer').value) || null;
  const ext1Id = parseInt(document.getElementById('f-ext1').value)     || null;
  const ext2Id = parseInt(document.getElementById('f-ext2').value)     || null;

  const payload = {
    account_number:      ac,
    month,
    month_date:          monthDate,
    branch_id:           parseInt(document.getElementById('f-branch').value) || null,
    account_type_id:     parseInt(document.getElementById('f-type').value)   || null,
    account_status_id:   parseInt(document.getElementById('f-status').value) || null,
    trading_type_id:     parseInt(document.getElementById('f-trading').value)|| null,
    account_kind:        document.getElementById('f-kind').value,
    broker_id:           parseInt(broker),
    broker_commission:   parseFloat(document.getElementById('f-broker-comm').value) || 0,
    marketer_id:         mktrId,
    marketer_commission: mktrId ? (parseFloat(document.getElementById('f-marketer-comm').value) || 0) : 0,
    ext_marketer1_id:    ext1Id,
    ext_commission1:     ext1Id ? (parseFloat(document.getElementById('f-ext1-comm').value) || 0) : 0,
    ext_marketer2_id:    ext2Id,
    ext_commission2:     ext2Id ? (parseFloat(document.getElementById('f-ext2-comm').value) || 0) : 0,
    forex_commission:    parseFloat(document.getElementById('f-forex').value)   || 0,
    futures_commission:  parseFloat(document.getElementById('f-futures').value) || 0,
    initial_deposit:     parseFloat(document.getElementById('f-dep').value)  || 0,
    monthly_deposit:     parseFloat(document.getElementById('f-mon').value)  || 0,
    notes:               document.getElementById('f-notes').value.trim() || null,
  };

  const r = await api('POST', '/cards', payload);
  if (r.success) {
    showAlert('ok', '✅ ' + r.message);
    // Reset key fields
    document.getElementById('f-ac').value   = '';
    document.getElementById('f-dep').value  = '0';
    document.getElementById('f-mon').value  = '0';
    document.getElementById('f-notes').value = '';
    window.scrollTo(0, 0);
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
