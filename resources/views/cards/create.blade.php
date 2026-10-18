@extends('layouts.app')
@section('title', 'New Commission Card')
@section('page-title', 'New Commission Card')

@push('styles')
<style>
/* ── Month / Year Picker ──────────────────────────────── */
.mp-wrap { position:relative }
.mp-input-row { display:flex;align-items:center;gap:0 }
.mp-input-row .form-control { cursor:pointer;caret-color:transparent }
.mp-input-row .mp-icon { position:absolute;left:12px;color:var(--mu);pointer-events:none;font-size:16px }
.mp-popup {
  position:absolute;top:calc(100% + 8px);right:0;z-index:999;
  background:var(--bg3);border:1px solid var(--brd2);border-radius:16px;
  box-shadow:0 12px 36px rgba(0,0,0,.35);padding:18px;width:290px;
  animation:mpIn .15s cubic-bezier(.16,1,.3,1)
}
@keyframes mpIn { from{opacity:0;transform:translateY(-8px)} to{opacity:1;transform:translateY(0)} }
.mp-header { display:flex;align-items:center;justify-content:space-between;margin-bottom:16px }
.mp-year-label { font-size:17px;font-weight:800;color:var(--tx) }
.mp-nav { background:var(--inp-bg);border:1px solid var(--inp-brd);border-radius:9px;width:36px;height:36px;
  cursor:pointer;font-size:18px;color:var(--tx);display:flex;align-items:center;justify-content:center;
  transition:all .15s;font-family:'Tajawal',sans-serif }
.mp-nav:hover { background:rgba(26,173,186,.18);border-color:var(--pri2);color:var(--pri2) }
.mp-grid { display:grid;grid-template-columns:repeat(4,1fr);gap:7px }
.mp-month {
  padding:9px 4px;border-radius:10px;border:1px solid transparent;cursor:pointer;
  font-size:13px;font-weight:700;font-family:'Tajawal',sans-serif;
  background:var(--inp-bg);color:var(--tx);transition:all .15s;text-align:center
}
.mp-month:hover { background:rgba(26,173,186,.18);border-color:var(--pri);color:var(--pri2) }
.mp-month.mp-selected { background:var(--pri2);color:#fff;border-color:var(--pri2);box-shadow:0 3px 10px rgba(26,173,186,.4) }
.mp-month.mp-today { border-color:rgba(26,173,186,.5);color:var(--pri2) }
/* ── end picker ───────────────────────────────────────── */

/* ── Parent account field ─────────────────────────────── */
#parent-ac-row {
  display:none;
  margin-top:12px;
  padding:14px 16px;
  background:rgba(26,173,186,.07);
  border:1px solid rgba(26,173,186,.25);
  border-radius:12px;
  animation:fadeSlide .2s ease
}
@keyframes fadeSlide { from{opacity:0;transform:translateY(-6px)} to{opacity:1;transform:translateY(0)} }
#parent-ac-row.show { display:block }
/* ── end parent ─────────────────────────────────────────── */
</style>
@endpush

@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'create'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">
<div class="panel" style="max-width:860px">
  <div class="panel-header">
    <div class="panel-title" id="crt-page-title">➕ كرت عمولة جديد</div>
    <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-sm" id="crt-btn-back">← رجوع</a>
  </div>
  <div class="panel-body">
    <div id="alert-err" class="alert alert-error"></div>
    <div id="alert-ok"  class="alert alert-success"></div>

    <form id="card-form">
      <!-- Section 1: Account Info -->
      <div class="form-section">
        <div class="form-section-title" id="crt-sec1-title">معلومات الحساب الأساسية</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" id="crt-lbl-ac">رقم الحساب (AC No.) *</label>
            <input type="text" id="f-ac" class="form-control" placeholder="719750" required>
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-month">الشهر *</label>
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
            <label class="form-label" id="crt-lbl-type">نوع الحساب</label>
            <select id="f-type" class="form-control">
              <option value="" id="crt-opt-select1">— اختر —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-status">حالة الحساب</label>
            <select id="f-status" class="form-control">
              <option value="" id="crt-opt-select2">— اختر —</option>
            </select>
          </div>
        </div>
        @php $isScopedUser = auth()->user()?->isScopedToBranch(); @endphp
        @if(!$isScopedUser)
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" id="crt-lbl-branch">الفرع</label>
            <select id="f-branch" class="form-control"></select>
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-trading">نوع التداول</label>
            <select id="f-trading" class="form-control">
              <option value="" id="crt-opt-select3">— اختر —</option>
            </select>
          </div>
        </div>
        @else
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" id="crt-lbl-branch">الفرع</label>
            <div class="form-control" style="background:rgba(46,134,171,.08);color:var(--pri2);font-weight:700">
              {{ auth()->user()->branch?->name_ar ?? 'فرعك المحدد' }}
            </div>
            <input type="hidden" id="f-branch" value="{{ auth()->user()->branch_id }}">
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-trading">نوع التداول</label>
            <select id="f-trading" class="form-control">
              <option value="" id="crt-opt-select3">— اختر —</option>
            </select>
          </div>
        </div>
        @endif
        <div class="form-group">
          <label class="form-label" id="crt-lbl-kind">نوع الحساب (New / Sub)</label>
          <select id="f-kind" class="form-control" onchange="onKindChange()">
            <option value="new" id="crt-opt-new">New — جديد</option>
            <option value="sub" id="crt-opt-sub">Sub — فرعي</option>
          </select>
        </div>
        {{-- Parent account: shown only when kind = sub --}}
        <div id="parent-ac-row">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" id="crt-lbl-parent" style="color:var(--pri2)!important">🔗 رقم حساب العميل الأساسي (Parent AC) *</label>
            <input type="text" id="f-parent-ac" class="form-control"
                   placeholder="أدخل رقم الحساب الأساسي..."
                   style="border-color:rgba(26,173,186,.4);background:var(--inp-bg)">
            <div id="crt-parent-hint" style="font-size:12px;color:var(--mu);margin-top:6px">⚠️ هذا الحقل إلزامي عند إنشاء حساب فرعي (Sub)</div>
          </div>
        </div>
      </div>

      <!-- Section 2: Broker & Marketers -->
      <div class="form-section">
        <div class="form-section-title" id="crt-sec2-title">البروكر والمسوّقون (من قائمة الموظفين)</div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" id="crt-lbl-broker">🧑‍💼 البروكر *</label>
            <select id="f-broker" class="form-control" required>
              <option value="" id="crt-opt-broker">— اختر البروكر —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-broker-comm">عمولة البروكر</label>
            <input type="number" id="f-broker-comm" class="form-control" value="4" min="0" step="0.5">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label" id="crt-lbl-mktr">📢 مسوّق داخلي</label>
            <select id="f-marketer" class="form-control" onchange="onMktrChange()">
              <option value="" id="crt-opt-none1">— لا يوجد —</option>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-mktr-comm">عمولة المسوّق الداخلي</label>
            <input type="number" id="f-marketer-comm" class="form-control" value="0" min="0" step="0.5" disabled>
          </div>
        </div>
        <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:14px;margin-bottom:10px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" id="crt-lbl-ext1">🌐 مسوّق خارجي 1</label>
            <select id="f-ext1" class="form-control" onchange="onExt1Change()">
              <option value="" id="crt-opt-none2">— لا يوجد —</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" id="crt-lbl-ext1-comm">عمولة مسوّق خارجي 1</label>
            <input type="number" id="f-ext1-comm" class="form-control" value="0" min="0" step="0.5" disabled>
          </div>
        </div>
        <div class="form-row" style="background:rgba(123,104,238,.05);border:1px solid rgba(123,104,238,.15);border-radius:9px;padding:14px">
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" id="crt-lbl-ext2">🌐 مسوّق خارجي 2</label>
            <select id="f-ext2" class="form-control" onchange="onExt2Change()">
              <option value="" id="crt-opt-none3">— لا يوجد —</option>
            </select>
          </div>
          <div class="form-group" style="margin-bottom:0">
            <label class="form-label" id="crt-lbl-ext2-comm">عمولة مسوّق خارجي 2</label>
            <input type="number" id="f-ext2-comm" class="form-control" value="0" min="0" step="0.5" disabled>
          </div>
        </div>
      </div>

      <!-- Section 3: Deposits & Commissions -->
      <div class="form-section">
        <div class="form-section-title" id="crt-sec3-title">الإيداعات والعمولات</div>
        <div class="form-row-3">
          <div class="form-group">
            <label class="form-label" id="crt-lbl-dep">إيداع فتح الحساب</label>
            <input type="number" id="f-dep" class="form-control" value="0" min="0">
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-mon">الإيداع الشهري المتوقع</label>
            <input type="number" id="f-mon" class="form-control" value="0" min="0">
          </div>
          <div class="form-group">
            <label class="form-label">Forex Commission</label>
            <input type="number" id="f-forex" class="form-control" value="8" min="0">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Futures Commission</label>
            <input type="number" id="f-futures" class="form-control" value="8" min="0">
          </div>
          <div class="form-group">
            <label class="form-label" id="crt-lbl-notes">ملاحظات</label>
            <input type="text" id="f-notes" class="form-control" id="crt-inp-notes" placeholder="ملاحظات اختيارية">
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px">
        <button type="button" class="btn btn-primary btn-xl" onclick="submitCard()" id="crt-btn-save">
          💾 حفظ الكرت
        </button>
        <a href="{{ route('cards.index') }}" class="btn btn-ghost btn-xl" id="crt-btn-cancel">إلغاء</a>
      </div>
    </form>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// CRT — Create Card Bilingual Dictionary
// ══════════════════════════════════════════════════════════
const CRT = {
  ar: {
    pageTitle:    '➕ كرت عمولة جديد',
    btnBack:      '← رجوع',
    btnSave:      '💾 حفظ الكرت',
    btnCancel:    'إلغاء',
    sec1:         'معلومات الحساب الأساسية',
    sec2:         'البروكر والمسوّقون (من قائمة الموظفين)',
    sec3:         'الإيداعات والعمولات',
    lblAc:        'رقم الحساب (AC No.) *',
    lblMonth:     'الشهر *',
    lblType:      'نوع الحساب',
    lblStatus:    'حالة الحساب',
    lblBranch:    'الفرع',
    lblTrading:   'نوع التداول',
    lblKind:      'نوع الحساب (New / Sub)',
    lblParent:    '🔗 رقم حساب العميل الأساسي (Parent AC) *',
    phParent:     'أدخل رقم الحساب الأساسي...',
    hintParent:   '⚠️ هذا الحقل إلزامي عند إنشاء حساب فرعي (Sub)',
    lblBroker:    '🧑‍💼 البروكر *',
    lblBrComm:    'عمولة البروكر',
    lblMktr:      '📢 مسوّق داخلي',
    lblMktrComm:  'عمولة المسوّق الداخلي',
    lblExt1:      '🌐 مسوّق خارجي 1',
    lblExt1Comm:  'عمولة مسوّق خارجي 1',
    lblExt2:      '🌐 مسوّق خارجي 2',
    lblExt2Comm:  'عمولة مسوّق خارجي 2',
    lblDep:       'إيداع فتح الحساب',
    lblMon:       'الإيداع الشهري المتوقع',
    lblNotes:     'ملاحظات',
    phNotes:      'ملاحظات اختيارية',
    phMonth:      'اختر الشهر والسنة...',
    optSelect:    '— اختر —',
    optBranch:    '— اختر الفرع —',
    optBroker:    '— اختر البروكر —',
    optNone:      '— لا يوجد —',
    optNew:       'New — جديد',
    optSub:       'Sub — فرعي',
    errRequired:  '⚠️ يرجى ملء رقم الحساب والشهر والبروكر على الأقل',
    errParent:    '⚠️ يرجى إدخال رقم حساب العميل الأساسي (Parent AC) عند اختيار حساب فرعي',
    tbTitle:      'إنشاء كرت عمولة جديد',
  },
  en: {
    pageTitle:    '➕ New Commission Card',
    btnBack:      '← Back',
    btnSave:      '💾 Save Card',
    btnCancel:    'Cancel',
    sec1:         'Basic Account Information',
    sec2:         'Broker & Marketers (from employees list)',
    sec3:         'Deposits & Commissions',
    lblAc:        'Account Number (AC No.) *',
    lblMonth:     'Month *',
    lblType:      'Account Type',
    lblStatus:    'Account Status',
    lblBranch:    'Branch',
    lblTrading:   'Trading Type',
    lblKind:      'Account Kind (New / Sub)',
    lblParent:    '🔗 Parent Account Number (Parent AC) *',
    phParent:     'Enter parent account number...',
    hintParent:   '⚠️ This field is required for Sub accounts',
    lblBroker:    '🧑‍💼 Broker *',
    lblBrComm:    'Broker Commission',
    lblMktr:      '📢 Internal Marketer',
    lblMktrComm:  'Internal Marketer Commission',
    lblExt1:      '🌐 External Marketer 1',
    lblExt1Comm:  'External Marketer 1 Commission',
    lblExt2:      '🌐 External Marketer 2',
    lblExt2Comm:  'External Marketer 2 Commission',
    lblDep:       'Opening Deposit',
    lblMon:       'Expected Monthly Deposit',
    lblNotes:     'Notes',
    phNotes:      'Optional notes',
    phMonth:      'Select month and year...',
    optSelect:    '— Select —',
    optBranch:    '— Select branch —',
    optBroker:    '— Select broker —',
    optNone:      '— None —',
    optNew:       'New',
    optSub:       'Sub',
    errRequired:  '⚠️ Please fill in account number, month and broker at minimum',
    errParent:    '⚠️ Please enter the parent account number for Sub accounts',
    tbTitle:      'Create New Commission Card',
  }
};

function crtL()    { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function crt(key)  { const l = crtL(); return CRT[l]?.[key] ?? CRT.ar[key] ?? key; }

function crtApplyLang() {
  const idMap = {
    'crt-page-title': 'pageTitle',
    'crt-btn-back':   'btnBack',
    'crt-btn-save':   'btnSave',
    'crt-btn-cancel': 'btnCancel',
    'crt-sec1-title': 'sec1',
    'crt-sec2-title': 'sec2',
    'crt-sec3-title': 'sec3',
    'crt-lbl-ac':     'lblAc',
    'crt-lbl-month':  'lblMonth',
    'crt-lbl-type':   'lblType',
    'crt-lbl-status': 'lblStatus',
    'crt-lbl-branch': 'lblBranch',
    'crt-lbl-trading':'lblTrading',
    'crt-lbl-kind':   'lblKind',
    'crt-lbl-parent': 'lblParent',
    'crt-parent-hint':'hintParent',
    'crt-lbl-broker': 'lblBroker',
    'crt-lbl-broker-comm':'lblBrComm',
    'crt-lbl-mktr':   'lblMktr',
    'crt-lbl-mktr-comm':'lblMktrComm',
    'crt-lbl-ext1':   'lblExt1',
    'crt-lbl-ext1-comm':'lblExt1Comm',
    'crt-lbl-ext2':   'lblExt2',
    'crt-lbl-ext2-comm':'lblExt2Comm',
    'crt-lbl-dep':    'lblDep',
    'crt-lbl-mon':    'lblMon',
    'crt-lbl-notes':  'lblNotes',
  };
  Object.entries(idMap).forEach(([id, key]) => {
    const el = document.getElementById(id);
    if (el) el.textContent = crt(key);
  });

  // Placeholders
  const phMap = {
    'f-month':    'phMonth',
    'f-parent-ac':'phParent',
    'f-notes':    'phNotes',
  };
  Object.entries(phMap).forEach(([id, key]) => {
    const el = document.getElementById(id);
    if (el) el.placeholder = crt(key);
  });

  // Select first options
  ['crt-opt-select1','crt-opt-select2','crt-opt-select3'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = crt('optSelect');
  });
  const optBrokerEl = document.getElementById('crt-opt-broker');
  if (optBrokerEl) optBrokerEl.textContent = crt('optBroker');
  ['crt-opt-none1','crt-opt-none2','crt-opt-none3'].forEach(id => {
    const el = document.getElementById(id);
    if (el) el.textContent = crt('optNone');
  });
  const optNew = document.getElementById('crt-opt-new');
  const optSub = document.getElementById('crt-opt-sub');
  if (optNew) optNew.textContent = crt('optNew');
  if (optSub) optSub.textContent = crt('optSub');

  // Branch first option
  const bSel = document.getElementById('f-branch');
  if (bSel && bSel.tagName === 'SELECT' && bSel.options[0] && bSel.options[0].value === '') {
    bSel.options[0].textContent = crt('optBranch');
  }

  // Topbar page title
  const tbTitle = document.querySelector('.tb-title');
  if (tbTitle) tbTitle.textContent = crt('tbTitle');

  // Re-render month picker (to switch month names language)
  mpRender();
}

// Hook into global applyLang
const _crtOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_crtOrigApplyLang) _crtOrigApplyLang(lang);
  crtApplyLang();
};

// ══ Month / Year Picker ═══════════════════════════════════════
const MP_MONTHS_EN = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
const MP_MONTHS_AR = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
const MP_MONTHS_EN_FULL = ['January','February','March','April','May','June','July','August','September','October','November','December'];

let mpYear     = new Date().getFullYear();
let mpSelected = null; // { year, month (0-based) }

function mpRender() {
  document.getElementById('mp-year-lbl').textContent = mpYear;
  const isEn  = crtL() === 'en';
  const labels = isEn ? MP_MONTHS_EN : MP_MONTHS_AR;
  const now  = new Date();
  const grid = document.getElementById('mp-grid');
  if (!grid) return;
  grid.innerHTML = labels.map((m, i) => {
    const isSelected = mpSelected && mpSelected.year === mpYear && mpSelected.month === i;
    const isToday    = now.getFullYear() === mpYear && now.getMonth() === i;
    return `<button type="button" class="mp-month${isSelected ? ' mp-selected' : ''}${isToday && !isSelected ? ' mp-today' : ''}"
      onclick="mpSelect(${i})">${m}</button>`;
  }).join('');
}

function mpToggle(e) {
  e.stopPropagation();
  const popup = document.getElementById('mp-popup');
  if (popup.style.display === 'none') {
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
  const isEn = crtL() === 'en';
  // Display: use current language month name
  const displayVal = (isEn ? MP_MONTHS_EN_FULL[monthIdx] : MP_MONTHS_AR[monthIdx]) + ' ' + mpYear;
  // Store: always English short for backend
  const storeVal   = MP_MONTHS_EN[monthIdx] + ' ' + mpYear;
  document.getElementById('f-month').value        = displayVal;
  document.getElementById('f-month').dataset.raw  = storeVal;
  document.getElementById('mp-popup').style.display = 'none';
}

// Close picker on outside click
document.addEventListener('click', function(e) {
  const wrap = document.getElementById('mp-wrap');
  if (wrap && !wrap.contains(e.target)) {
    const popup = document.getElementById('mp-popup');
    if (popup) popup.style.display = 'none';
  }
});
// ══ end picker ════════════════════════════════════════════════

// ── Kind change: show/hide parent account field ───────────────
function onKindChange() {
  const isSub = document.getElementById('f-kind').value === 'sub';
  const row   = document.getElementById('parent-ac-row');
  const inp   = document.getElementById('f-parent-ac');
  if (isSub) {
    row.classList.add('show');
    inp.required = true;
  } else {
    row.classList.remove('show');
    inp.required = false;
    inp.value = '';
  }
}

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
    const fill = (selId, items, firstOptId) => {
      const sel = document.getElementById(selId);
      if (!sel) return;
      // Keep first option (the "select" placeholder)
      const firstOpt = sel.options[0];
      sel.innerHTML = '';
      if (firstOpt) sel.appendChild(firstOpt);
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
      // Keep first option
      const firstOpt = sel.options[0];
      sel.innerHTML = '';
      if (firstOpt) sel.appendChild(firstOpt);
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
      bSel.innerHTML = `<option value="">${crt('optBranch')}</option>`;
      branches.data.forEach(b => {
        const o = document.createElement('option');
        o.value = b.id;
        o.textContent = b.name_ar + (b.name_en ? ' / ' + b.name_en : '');
        bSel.appendChild(o);
      });
    }
  }

  // After data loaded, apply current language
  crtApplyLang();
}

// ── Submit ────────────────────────────────────────────────────
async function submitCard() {
  const ac         = document.getElementById('f-ac').value.trim();
  const monthEl    = document.getElementById('f-month');
  const month      = monthEl.dataset.raw || monthEl.value;   // prefer English stored value
  const monthDisp  = monthEl.value;
  const broker     = document.getElementById('f-broker').value;

  const kind     = document.getElementById('f-kind').value;
  const parentAc = document.getElementById('f-parent-ac').value.trim();

  if (!ac || !monthDisp || !broker) {
    showAlert('err', crt('errRequired'));
    return;
  }
  if (kind === 'sub' && !parentAc) {
    showAlert('err', crt('errParent'));
    document.getElementById('f-parent-ac').focus();
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
    account_kind:        kind,
    parent_account_number: kind === 'sub' ? parentAc : null,
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

// ── Init ──────────────────────────────────────────────────────
crtApplyLang();
loadFormOptions();
</script>
@endpush
