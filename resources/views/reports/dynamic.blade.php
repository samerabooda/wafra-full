@extends('layouts.app')
@section('title', 'تقرير ديناميكي')
@section('page-title', 'تقرير ديناميكي')

@push('styles')
<style>
/* ── Drag & Drop Columns ── */
.col-picker {
  display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 14px;
}
.col-pill {
  display: inline-flex; align-items: center; gap: 6px;
  padding: 6px 12px; border-radius: 20px; font-size: 12px; font-weight: 600;
  cursor: grab; user-select: none; transition: all .2s;
  border: 2px solid var(--brd2); background: var(--inp-bg); color: var(--m2);
}
.col-pill:hover   { border-color: var(--pri); color: var(--pri2); }
.col-pill.active  { background: rgba(46,134,171,.2); border-color: var(--pri); color: var(--pri2); }
.col-pill.dragging{ opacity: .4; transform: scale(.95); }
.col-pill .drag-handle { color: var(--mu); font-size: 10px; cursor: grab; }
.col-pill .remove-btn  { background: none; border: none; color: var(--mu);
  cursor: pointer; font-size: 11px; padding: 0; line-height: 1; }
.col-pill .remove-btn:hover { color: var(--re); }

/* Column zones */
.col-zone {
  min-height: 54px; padding: 10px; border-radius: 10px;
  border: 2px dashed var(--brd1); transition: all .2s;
}
.col-zone.drag-over { border-color: var(--pri); background: rgba(46,134,171,.05); }
.col-zone-label { font-size:10px; color:var(--mu); text-transform:uppercase;
  letter-spacing:.5px; margin-bottom:8px; font-weight:700; }

/* Sortable table headers */
.sortable-th {
  cursor: pointer; white-space: nowrap; user-select: none;
}
.sortable-th:hover { color: var(--pri2); }
.sortable-th .sort-icon { font-size: 9px; margin-right: 4px; opacity: .5; }
.sortable-th.sort-asc  .sort-icon,
.sortable-th.sort-desc .sort-icon { opacity: 1; color: var(--pri2); }

/* Drag-to-reorder column headers */
.th-draggable { cursor: grab; }
.th-draggable.over { background: rgba(46,134,171,.15) !important; }

/* Filter row */
.filter-row th {
  padding: 4px 8px !important;
  background: var(--bg2) !important;
}
.filter-row input, .filter-row select {
  width: 100%; background: var(--inp-bg); border: 1px solid var(--inp-brd);
  border-radius: 5px; padding: 4px 7px; color: var(--tx);
  font-family: 'Tajawal', sans-serif; font-size: 11px; outline: none;
}
.filter-row input:focus, .filter-row select:focus { border-color: var(--pri); }

/* Row drag */
.tr-draggable { cursor: grab; }
.tr-draggable:active { cursor: grabbing; opacity: .6; }
.tr-draggable.over { background: rgba(46,134,171,.1) !important; outline: 2px dashed var(--pri); }

/* Frozen/pinned indicator */
.col-frozen { border-right: 2px solid var(--pri2) !important; }

/* Save/load layout */
.layout-btn {
  padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 700;
  cursor: pointer; font-family: 'Tajawal', sans-serif;
  border: 1px solid var(--brd2); background: var(--inp-bg); color: var(--m2);
}
.layout-btn:hover { border-color: var(--pri); color: var(--pri2); }

/* Summary bar */
.summary-bar {
  display: flex; gap: 20px; flex-wrap: wrap; padding: 10px 16px;
  border-top: 1px solid var(--brd1); background: var(--inp-bg);
  font-size: 12px;
}
</style>
@endpush

@section('content')

{{-- ═══ SECTION 1: FILTERS ═══ --}}
<div class="panel" style="margin-bottom:14px">
  <div class="panel-header">
    <div class="panel-title">🔍 فلاتر التقرير</div>
    <div style="display:flex;gap:6px">
      <button class="layout-btn" onclick="saveLayout()">💾 حفظ التخطيط</button>
      <button class="layout-btn" onclick="loadLayout()">📂 تحميل التخطيط</button>
      <button class="layout-btn" onclick="resetLayout()">↺ إعادة ضبط</button>
    </div>
  </div>
  <div class="panel-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:12px">
      <div class="form-group" style="margin:0">
        <label class="form-label">من شهر</label>
        <select id="rf-from" class="form-control"><option value="">—</option></select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">إلى شهر</label>
        <select id="rf-to" class="form-control"><option value="">—</option></select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">البروكر</label>
        <select id="rf-broker" class="form-control"><option value="">الكل</option></select>
      </div>
      @if(auth()->user()?->isFinanceAdmin())
      <div class="form-group" style="margin:0">
        <label class="form-label">الفرع</label>
        <select id="rf-branch" class="form-control"><option value="">كل الفروع</option></select>
      </div>
      @endif
      <div class="form-group" style="margin:0">
        <label class="form-label">حالة الكرت</label>
        <select id="rf-status" class="form-control">
          <option value="">الكل</option>
          <option value="modified">معدّلة فقط 🟡</option>
          <option value="new_added">مضافة جديدة فقط 🆕</option>
          <option value="active">عادي فقط</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">نوع الحساب</label>
        <select id="rf-kind" class="form-control">
          <option value="">الكل</option>
          <option value="new">جديد</option>
          <option value="sub">فرعي</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label">حد أدنى إيداع $</label>
        <input type="number" id="rf-min" class="form-control" value="0" min="0">
      </div>
      <div class="form-group" style="margin:0;display:flex;flex-direction:column;justify-content:flex-end">
        <button class="btn btn-primary" onclick="fetchAndRender()">⚡ توليد التقرير</button>
      </div>
    </div>
  </div>
</div>

{{-- ═══ SECTION 2: COLUMN BUILDER ═══ --}}
<div class="panel" style="margin-bottom:14px" id="col-builder">
  <div class="panel-header">
    <div class="panel-title">🔧 بناء الأعمدة — اسحب وأفلت لإعادة الترتيب</div>
    <div style="display:flex;gap:6px;align-items:center">
      <span style="font-size:11px;color:var(--mu)">اضغط على عمود لتفعيله/إلغائه</span>
      <button class="layout-btn" onclick="selectAllCols()">✅ الكل</button>
      <button class="layout-btn" onclick="selectNoneCols()">☐ لا شيء</button>
    </div>
  </div>
  <div class="panel-body">
    {{-- Available columns palette --}}
    <div class="col-zone-label">الأعمدة المتاحة — اضغط للإضافة</div>
    <div class="col-picker" id="col-available"></div>

    {{-- Selected columns (draggable order) --}}
    <div style="margin-top:12px">
      <div class="col-zone-label">الأعمدة المختارة — اسحب لإعادة الترتيب</div>
      <div class="col-zone" id="col-selected"
           ondragover="colZoneDragOver(event)"
           ondrop="colZoneDrop(event)"
           ondragleave="colZoneDragLeave(event)">
        <div style="color:var(--mu);font-size:11px;text-align:center;padding:6px"
             id="col-selected-empty">اضغط على أعمدة من القائمة أعلاه أو اسحبها هنا</div>
      </div>
    </div>
  </div>
</div>

{{-- ═══ SECTION 3: SORT CONTROLS ═══ --}}
<div class="panel" style="margin-bottom:14px" id="sort-controls" style="display:none">
  <div class="panel-header">
    <div class="panel-title">↕️ ترتيب الصفوف</div>
  </div>
  <div class="panel-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
    <div class="form-group" style="margin:0">
      <label class="form-label">ترتيب أولي حسب</label>
      <select id="sort-col-1" class="form-control" style="min-width:160px">
        <option value="">— بلا ترتيب —</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label">اتجاه</label>
      <select id="sort-dir-1" class="form-control">
        <option value="asc">تصاعدي ↑</option>
        <option value="desc">تنازلي ↓</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label">ترتيب ثانوي حسب</label>
      <select id="sort-col-2" class="form-control" style="min-width:160px">
        <option value="">— بلا —</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label">اتجاه</label>
      <select id="sort-dir-2" class="form-control">
        <option value="asc">تصاعدي ↑</option>
        <option value="desc">تنازلي ↓</option>
      </select>
    </div>
    <button class="btn btn-ghost btn-sm" onclick="applySort()">تطبيق الترتيب</button>
    <button class="btn btn-ghost btn-sm" onclick="resetSort()">↺ إعادة ضبط الترتيب</button>
  </div>
</div>

{{-- ═══ SECTION 4: THE TABLE ═══ --}}
<div class="panel" id="result-panel" style="display:none">
  <div class="panel-header">
    <div class="panel-title">
      📋 نتائج التقرير الديناميكي
      <span id="result-count" style="font-size:11px;color:var(--mu);font-weight:400;margin-right:8px"></span>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap">
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportDynExcel()">📗 Excel</button>
      <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)"   onclick="exportDynPdf()">📄 PDF</button>
      <button class="btn btn-sm" style="background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.25);color:var(--pri2)" onclick="window.print()">🖨️ طباعة</button>
    </div>
  </div>

  {{-- Inline column filter toggle --}}
  <div style="padding:8px 16px;border-bottom:1px solid var(--brd1);display:flex;gap:8px;align-items:center">
    <span style="font-size:11px;color:var(--mu)">فلترة الجدول:</span>
    <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer">
      <input type="checkbox" id="toggle-col-filters" onchange="toggleColFilters(this.checked)" style="accent-color:var(--pri)">
      إظهار فلاتر الأعمدة
    </label>
    <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer">
      <input type="checkbox" id="toggle-row-drag" onchange="toggleRowDrag(this.checked)" style="accent-color:var(--pri)">
      تمكين سحب الصفوف
    </label>
  </div>

  <div class="table-scroll" style="max-height:520px;overflow-x:auto;-webkit-overflow-scrolling:touch">
    <table class="data-table" id="dyn-table">
      <thead id="dyn-thead"></thead>
      <tbody id="dyn-tbody"></tbody>
    </table>
  </div>

  <div class="summary-bar" id="dyn-summary"></div>
</div>

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// ALL AVAILABLE COLUMNS DEFINITION
// ══════════════════════════════════════════════════════════
const ALL_COLUMNS = [
  { id:'account_number',    label:'رقم الحساب',         type:'text',    width:130, frozen:true },
  { id:'cc_source',         label:'مصدر CC',             type:'cc',      width:110 },
  { id:'cc_agent',          label:'موظف CC',              type:'text',    width:130 },
  { id:'cc_agent_commission',label:'ع. موظف CC',          type:'number',  width:120 },
  { id:'month',             label:'الشهر',               type:'text',    width:100 },
  { id:'branch',            label:'الفرع',               type:'text',    width:130 },
  { id:'account_kind',      label:'نوع الحساب',          type:'badge',   width:80  },
  { id:'account_type',      label:'نوع (ECN/STP)',        type:'text',    width:90  },
  { id:'account_status',    label:'حالة الحساب',          type:'text',    width:110 },
  { id:'trading_type',      label:'نوع التداول',          type:'text',    width:110 },
  { id:'broker',            label:'البروكر',              type:'text',    width:140 },
  { id:'broker_commission', label:'ع. بروكر ($/lot)',     type:'number',  width:130 },
  { id:'marketer',          label:'مسوّق داخلي',         type:'text',    width:140 },
  { id:'marketer_commission',label:'ع. داخلي ($/lot)',   type:'number',  width:120 },
  { id:'ext_marketer1',     label:'مسوّق خارجي 1',       type:'text',    width:140 },
  { id:'ext_commission1',   label:'ع. خارجي 1 ($/lot)',  type:'number',  width:130 },
  { id:'ext_marketer2',     label:'مسوّق خارجي 2',       type:'text',    width:140 },
  { id:'ext_commission2',   label:'ع. خارجي 2 ($/lot)',  type:'number',  width:130 },
  { id:'total_commission',  label:'إجمالي العمولات',      type:'number',  width:140 },
  { id:'initial_deposit',   label:'إيداع أولي ($)',       type:'currency',width:130 },
  { id:'monthly_deposit',   label:'إيداع شهري ($)',       type:'currency',width:130 },
  { id:'forex_commission',  label:'Forex Comm.',          type:'number',  width:110 },
  { id:'futures_commission',label:'Futures Comm.',        type:'number',  width:110 },
  { id:'status',            label:'الحالة',               type:'status',  width:100 },
  { id:'created_by',        label:'أنشئ بواسطة',          type:'text',    width:130 },
];

// Default selected columns (ordered)
const DEFAULT_SELECTED = [
  'account_number','month','broker','broker_commission',
  'marketer','marketer_commission','ext_marketer1','ext_commission1',
  'initial_deposit','monthly_deposit','status'
];

// ── State ──────────────────────────────────────────────────
let selectedCols = [...DEFAULT_SELECTED]; // ordered column IDs
let rawData       = [];   // full API response rows
let filteredData  = [];   // after col filters applied
let displayData   = [];   // after sort applied
let colFilters    = {};   // { colId: filterValue }
let sortState     = { col1:'', dir1:'desc', col2:'', dir2:'asc' };
let rowDragEnabled = false;

// drag state
let dragColId   = null;
let dragRowIdx  = null;

// ══════════════════════════════════════════════════════════
// INIT
// ══════════════════════════════════════════════════════════
async function initPage() {
  await loadFilterOptions();
  renderColPicker();
  loadSavedLayout();
}

async function loadFilterOptions() {
  const [emps, branches] = await Promise.all([
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
  ]);

  // Months
  const now = new Date();
  ['rf-from','rf-to'].forEach(id => {
    const sel = document.getElementById(id); if (!sel) return;
    for (let i = 0; i < 30; i++) {
      const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
      const m = d.toLocaleString('en-US',{month:'short'})+' '+d.getFullYear();
      const o = document.createElement('option'); o.value = o.textContent = m;
      sel.appendChild(o);
    }
  });

  if (emps.success) {
    const bSel = document.getElementById('rf-broker');
    emps.data.forEach(e => {
      const o = document.createElement('option'); o.value = e.name; o.textContent = e.name;
      bSel.appendChild(o);
    });
  }

  const brSel = document.getElementById('rf-branch');
  if (brSel && branches.success) {
    branches.data.forEach(b => {
      const o = document.createElement('option'); o.value = b.id; o.textContent = b.name_ar;
      brSel.appendChild(o);
    });
  }
}

// ══════════════════════════════════════════════════════════
// COLUMN PICKER
// ══════════════════════════════════════════════════════════
function renderColPicker() {
  const available = document.getElementById('col-available');
  const selected  = document.getElementById('col-selected');

  // Available pills
  available.innerHTML = ALL_COLUMNS.map(col => `
    <div class="col-pill ${selectedCols.includes(col.id) ? 'active' : ''}"
         id="cpill-${col.id}"
         draggable="true"
         ondragstart="colPillDragStart(event,'${col.id}')"
         onclick="toggleCol('${col.id}')">
      <span class="drag-handle">⠿</span>
      ${col.label}
      ${selectedCols.includes(col.id) ? '<button class="remove-btn" onclick="event.stopPropagation();removeCol(\''+col.id+'\')">✕</button>' : ''}
    </div>`).join('');

  // Selected zone — ordered
  renderSelectedZone();
  updateSortSelects();
}

function renderSelectedZone() {
  const zone  = document.getElementById('col-selected');
  const empty = document.getElementById('col-selected-empty');

  if (!selectedCols.length) {
    zone.innerHTML = '<div style="color:var(--mu);font-size:11px;text-align:center;padding:6px" id="col-selected-empty">اضغط على أعمدة من القائمة أعلاه أو اسحبها هنا</div>';
    return;
  }

  const pills = selectedCols.map(id => {
    const col = ALL_COLUMNS.find(c => c.id === id);
    if (!col) return '';
    return `<div class="col-pill active" id="spill-${id}"
               draggable="true"
               ondragstart="selectedPillDragStart(event,'${id}')"
               ondragover="selectedPillDragOver(event,'${id}')"
               ondrop="selectedPillDrop(event,'${id}')"
               ondragleave="selectedPillDragLeave(event,'${id}')">
      <span class="drag-handle">⠿</span>
      ${col.label}
      <button class="remove-btn" onclick="removeCol('${id}')">✕</button>
    </div>`;
  }).join('');

  zone.innerHTML = pills;
}

// Mobile: detect if touch device and disable drag, use tap instead
const isTouchDevice = () => window.matchMedia('(hover: none) and (pointer: coarse)').matches;

function toggleCol(id) {
  if (selectedCols.includes(id)) {
    removeCol(id);
  } else {
    selectedCols.push(id);
    renderColPicker();
    if (displayData.length) renderTable();
  }
}

function removeCol(id) {
  selectedCols = selectedCols.filter(c => c !== id);
  renderColPicker();
  if (displayData.length) renderTable();
}

function selectAllCols()  { selectedCols = ALL_COLUMNS.map(c => c.id); renderColPicker(); if (displayData.length) renderTable(); }
function selectNoneCols() { selectedCols = []; renderColPicker(); if (displayData.length) renderTable(); }

// ── Drag within selected zone (reorder) ──
let dragSrcId = null;
function selectedPillDragStart(e, id) {
  dragSrcId = id;
  e.dataTransfer.effectAllowed = 'move';
}
function selectedPillDragOver(e, id) {
  e.preventDefault();
  e.dataTransfer.dropEffect = 'move';
  document.getElementById('spill-'+id)?.classList.add('dragging');
}
function selectedPillDrop(e, id) {
  e.preventDefault();
  if (dragSrcId && dragSrcId !== id) {
    const fromIdx = selectedCols.indexOf(dragSrcId);
    const toIdx   = selectedCols.indexOf(id);
    if (fromIdx >= 0 && toIdx >= 0) {
      selectedCols.splice(fromIdx, 1);
      selectedCols.splice(toIdx, 0, dragSrcId);
      renderColPicker();
      if (displayData.length) renderTable();
    }
  }
  dragSrcId = null;
}
function selectedPillDragLeave(e, id) {
  document.getElementById('spill-'+id)?.classList.remove('dragging');
}

// ── Drag from available → selected zone ──
function colPillDragStart(e, id) {
  dragColId = id;
  e.dataTransfer.effectAllowed = 'copy';
}
function colZoneDragOver(e)   { e.preventDefault(); document.getElementById('col-selected').classList.add('drag-over'); }
function colZoneDragLeave(e)  { document.getElementById('col-selected').classList.remove('drag-over'); }
function colZoneDrop(e) {
  e.preventDefault();
  document.getElementById('col-selected').classList.remove('drag-over');
  if (dragColId && !selectedCols.includes(dragColId)) {
    selectedCols.push(dragColId);
    renderColPicker();
    if (displayData.length) renderTable();
  }
  dragColId = null;
}

// ══════════════════════════════════════════════════════════
// FETCH DATA
// ══════════════════════════════════════════════════════════
async function fetchAndRender() {
  if (!selectedCols.length) {
    toast('اختر أعمدة أولاً', 'error');
    return;
  }

  const params = new URLSearchParams();
  const from   = document.getElementById('rf-from')?.value;
  const to     = document.getElementById('rf-to')?.value;
  const broker = document.getElementById('rf-broker')?.value;
  const branch = document.getElementById('rf-branch')?.value;
  const status = document.getElementById('rf-status')?.value;
  const kind   = document.getElementById('rf-kind')?.value;
  const min    = document.getElementById('rf-min')?.value;

  if (from)   params.set('month_from', from);
  if (to)     params.set('month_to', to);
  if (broker) params.set('search', broker);
  if (branch) params.set('branch_id', branch);
  if (status) params.set('status', status);
  if (kind)   params.set('kind', kind);
  if (min && parseInt(min) > 0) params.set('min_deposit', min);
  params.set('per_page', 500);

  const r = await api('GET', '/cards/report?' + params);
  if (!r.success) { toast('خطأ في تحميل البيانات', 'error'); return; }

  rawData      = r.data || [];
  filteredData = [...rawData];
  colFilters   = {};

  applySort();
  document.getElementById('result-panel').style.display  = 'block';
  document.getElementById('sort-controls').style.display = 'block';
  document.getElementById('result-count').textContent    = rawData.length + ' سجل';

  toast(`تم تحميل ${rawData.length} سجل`, 'success');
}

// ══════════════════════════════════════════════════════════
// SORT
// ══════════════════════════════════════════════════════════
function updateSortSelects() {
  ['sort-col-1','sort-col-2'].forEach(selId => {
    const sel = document.getElementById(selId); if (!sel) return;
    const cur = sel.value;
    sel.innerHTML = selId === 'sort-col-1'
      ? '<option value="">— بلا ترتيب —</option>'
      : '<option value="">— بلا —</option>';
    selectedCols.forEach(id => {
      const col = ALL_COLUMNS.find(c => c.id === id); if (!col) return;
      const o = document.createElement('option');
      o.value = col.id; o.textContent = col.label;
      if (col.id === cur) o.selected = true;
      sel.appendChild(o);
    });
  });
}

function applySort() {
  const col1 = document.getElementById('sort-col-1')?.value || '';
  const dir1 = document.getElementById('sort-dir-1')?.value || 'desc';
  const col2 = document.getElementById('sort-col-2')?.value || '';
  const dir2 = document.getElementById('sort-dir-2')?.value || 'asc';

  sortState = { col1, dir1, col2, dir2 };

  // Apply column filters first
  filteredData = rawData.filter(row => {
    return Object.entries(colFilters).every(([colId, fval]) => {
      if (!fval) return true;
      const v = String(getCellValue(row, colId)).toLowerCase();
      return v.includes(fval.toLowerCase());
    });
  });

  // Sort
  displayData = [...filteredData].sort((a, b) => {
    if (col1) {
      const va = getCellRawValue(a, col1);
      const vb = getCellRawValue(b, col1);
      const cmp = compare(va, vb);
      if (cmp !== 0) return dir1 === 'asc' ? cmp : -cmp;
    }
    if (col2) {
      const va = getCellRawValue(a, col2);
      const vb = getCellRawValue(b, col2);
      const cmp = compare(va, vb);
      return dir2 === 'asc' ? cmp : -cmp;
    }
    return 0;
  });

  renderTable();
}

function resetSort() {
  ['sort-col-1','sort-col-2'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
  ['sort-dir-1','sort-dir-2'].forEach(id => { const el = document.getElementById(id); if(el) el.value='desc'; });
  applySort();
}

function compare(a, b) {
  if (a === null || a === undefined) return 1;
  if (b === null || b === undefined) return -1;
  if (typeof a === 'number' && typeof b === 'number') return a - b;
  return String(a).localeCompare(String(b), 'ar');
}

// ══════════════════════════════════════════════════════════
// RENDER TABLE
// ══════════════════════════════════════════════════════════
function renderTable() {
  if (!selectedCols.length || !displayData.length) return;

  const cols = selectedCols.map(id => ALL_COLUMNS.find(c => c.id === id)).filter(Boolean);

  // ── THEAD ──────────────────────────────────────────────
  const sortIcons = { '': '↕', asc: '↑', desc: '↓' };
  const getSort = id => {
    if (sortState.col1 === id) return sortState.dir1;
    if (sortState.col2 === id) return sortState.dir2;
    return '';
  };

  const filterRow = document.getElementById('toggle-col-filters')?.checked
    ? `<tr class="filter-row">${cols.map(col => `
        <th><input type="text" placeholder="فلتر..." value="${colFilters[col.id]||''}"
             oninput="setColFilter('${col.id}',this.value)"></th>`).join('')}</tr>`
    : '';

  document.getElementById('dyn-thead').innerHTML = `
    <tr>
      ${cols.map((col, i) => `
        <th class="sortable-th th-draggable sort-${getSort(col.id)}"
            draggable="true"
            onclick="headerSort('${col.id}')"
            ondragstart="headerDragStart(event,${i})"
            ondragover="headerDragOver(event,${i})"
            ondrop="headerDrop(event,${i})"
            style="min-width:${col.width}px${col.frozen?';position:sticky;right:0;z-index:1':''}">
          <span class="sort-icon">${sortIcons[getSort(col.id)] || '↕'}</span>
          ${col.label}
        </th>`).join('')}
    </tr>
    ${filterRow}`;

  // ── TBODY ──────────────────────────────────────────────
  document.getElementById('dyn-tbody').innerHTML = displayData.map((row, rowIdx) => `
    <tr class="tr-draggable ${row.status === 'modified' ? 'row-modified' : ''}"
        data-idx="${rowIdx}"
        draggable="${rowDragEnabled}"
        ondragstart="rowDragStart(event,${rowIdx})"
        ondragover="rowDragOver(event,${rowIdx})"
        ondrop="rowDrop(event,${rowIdx})"
        ondragleave="rowDragLeave(event,${rowIdx})">
      ${cols.map(col => `<td>${renderCell(row, col)}</td>`).join('')}
    </tr>`).join('');

  // ── SUMMARY ────────────────────────────────────────────
  const numCols = cols.filter(c => ['number','currency'].includes(c.type));
  const summaryParts = [`<span style="color:var(--mu)">الإجماليات:</span>`];
  numCols.forEach(col => {
    const total = displayData.reduce((s, r) => s + (parseFloat(getCellRawValue(r, col.id)) || 0), 0);
    if (total > 0) {
      const formatted = col.type === 'currency' ? fmtK(total) : '$' + total.toFixed(2) + '/lot';
      summaryParts.push(`<span class="mono">${col.label}: <b style="color:var(--pri2)">${formatted}</b></span>`);
    }
  });
  summaryParts.push(`<span style="color:var(--or)">معدّلة: <b>${displayData.filter(r=>r.status==='modified').length}</b></span>`);
  document.getElementById('dyn-summary').innerHTML = summaryParts.join('');
  document.getElementById('result-count').textContent = displayData.length + ' سجل';
  updateSortSelects();
}

// ── Cell value accessors ──────────────────────────────────
function getCellRawValue(row, colId) {
  const map = {
    account_number:     row.account_number,
    month:              row.month,
    branch:             row.branch?.name_ar,
    cc_source:          row.cc_branch_id ? '📞 CC' : null,
    cc_agent:           row.cc_agent?.name,
    cc_agent_commission:parseFloat(row.cc_agent_commission) || 0,
    account_kind:       row.account_kind,
    account_type:       row.account_type?.name_en,
    account_status:     row.account_status?.name_en,
    trading_type:       row.trading_type?.name_en,
    broker:             row.broker?.name,
    broker_commission:  parseFloat(row.broker_commission) || 0,
    marketer:           row.marketer?.name,
    marketer_commission:parseFloat(row.marketer_commission) || 0,
    ext_marketer1:      row.ext_marketer1?.name,
    ext_commission1:    parseFloat(row.ext_commission1) || 0,
    ext_marketer2:      row.ext_marketer2?.name,
    ext_commission2:    parseFloat(row.ext_commission2) || 0,
    total_commission:   (parseFloat(row.broker_commission)||0)+(parseFloat(row.marketer_commission)||0)+(parseFloat(row.ext_commission1)||0)+(parseFloat(row.ext_commission2)||0),
    initial_deposit:    parseFloat(row.initial_deposit) || 0,
    monthly_deposit:    parseFloat(row.monthly_deposit) || 0,
    forex_commission:   parseFloat(row.forex_commission) || 0,
    futures_commission: parseFloat(row.futures_commission) || 0,
    status:             row.status,
    created_by:         row.created_by?.name,
  };
  return map[colId] ?? '';
}

function getCellValue(row, colId) {
  return getCellRawValue(row, colId) ?? '';
}

function renderCell(row, col) {
  const v = getCellRawValue(row, col.id);
  if (v === null || v === undefined || v === '') return '<span style="color:var(--mu)">—</span>';

  switch (col.type) {
    case 'currency':
      return `<span class="mono" style="color:var(--pri2);font-weight:600">${fmtK(v)}</span>`;
    case 'number':
      return `<span class="mono" style="color:var(--gr)">$${parseFloat(v).toFixed(2)}/lot</span>`;
    case 'cc':
      if (!v) return '<span style="color:var(--mu)">—</span>';
      return '<span style="font-size:10px;padding:2px 7px;border-radius:12px;background:rgba(29,158,117,.15);color:#1D9E75;border:1px solid rgba(29,158,117,.3)">📞 CC</span>';
    case 'badge':
      return v === 'new'
        ? '<span class="badge badge-green">NEW</span>'
        : '<span class="badge badge-blue">SUB</span>';
    case 'status':
      const statusMap = {
        modified:  '<span class="badge badge-orange">✏️ معدّل</span>',
        new_added: '<span class="badge badge-green">🆕 جديد</span>',
        active:    '<span class="badge badge-blue">✅ عادي</span>',
        inactive:  '<span class="badge badge-gray">غير نشط</span>',
      };
      return statusMap[v] || v;
    default:
      if (col.id === 'account_number')
        return `<span class="ac-num">#${v}</span>`;
      return String(v);
  }
}

// ── Header click sort ─────────────────────────────────────
function headerSort(colId) {
  if (sortState.col1 === colId) {
    sortState.dir1 = sortState.dir1 === 'asc' ? 'desc' : 'asc';
  } else {
    sortState.col2 = sortState.col1;
    sortState.dir2 = sortState.dir1;
    sortState.col1 = colId;
    sortState.dir1 = 'desc';
  }
  const s1 = document.getElementById('sort-col-1');
  const s2 = document.getElementById('sort-col-2');
  if (s1) s1.value = sortState.col1;
  if (s2) s2.value = sortState.col2 || '';
  document.getElementById('sort-dir-1').value = sortState.dir1;
  applySort();
}

// ── Header drag reorder ───────────────────────────────────
let dragColIdx = null;
function headerDragStart(e, idx) { dragColIdx = idx; e.dataTransfer.effectAllowed = 'move'; }
function headerDragOver(e, idx)  { e.preventDefault(); }
function headerDrop(e, idx) {
  e.preventDefault();
  if (dragColIdx !== null && dragColIdx !== idx) {
    const moved = selectedCols.splice(dragColIdx, 1)[0];
    selectedCols.splice(idx, 0, moved);
    renderColPicker();
    renderTable();
  }
  dragColIdx = null;
}

// ── Row drag reorder ──────────────────────────────────────
function rowDragStart(e, idx) { dragRowIdx = idx; e.dataTransfer.effectAllowed = 'move'; }
function rowDragOver(e, idx)  { e.preventDefault(); document.querySelector(`[data-idx="${idx}"]`)?.classList.add('over'); }
function rowDragLeave(e, idx) { document.querySelector(`[data-idx="${idx}"]`)?.classList.remove('over'); }
function rowDrop(e, idx) {
  e.preventDefault();
  document.querySelectorAll('.tr-draggable').forEach(r => r.classList.remove('over'));
  if (dragRowIdx !== null && dragRowIdx !== idx) {
    const moved = displayData.splice(dragRowIdx, 1)[0];
    displayData.splice(idx, 0, moved);
    renderTable();
  }
  dragRowIdx = null;
}

// ── Column inline filters ─────────────────────────────────
function setColFilter(colId, val) {
  colFilters[colId] = val;
  applySort();
}

function toggleColFilters(show) {
  renderTable();
}

function toggleRowDrag(enabled) {
  rowDragEnabled = enabled;
  renderTable();
}

// ══════════════════════════════════════════════════════════
// SAVE / LOAD LAYOUT
// ══════════════════════════════════════════════════════════
function saveLayout() {
  const layout = {
    cols:     selectedCols,
    sortCol1: document.getElementById('sort-col-1')?.value || '',
    sortDir1: document.getElementById('sort-dir-1')?.value || 'desc',
    sortCol2: document.getElementById('sort-col-2')?.value || '',
    sortDir2: document.getElementById('sort-dir-2')?.value || 'asc',
  };
  localStorage.setItem('wg_dyn_layout', JSON.stringify(layout));
  toast('✅ تم حفظ التخطيط', 'success');
}

function loadLayout() {
  const saved = localStorage.getItem('wg_dyn_layout');
  if (!saved) { toast('لا يوجد تخطيط محفوظ', 'info'); return; }
  const layout = JSON.parse(saved);
  selectedCols = layout.cols || [...DEFAULT_SELECTED];
  renderColPicker();
  if (layout.sortCol1) {
    const s1 = document.getElementById('sort-col-1'); if(s1) s1.value = layout.sortCol1;
    const d1 = document.getElementById('sort-dir-1'); if(d1) d1.value = layout.sortDir1;
    const s2 = document.getElementById('sort-col-2'); if(s2) s2.value = layout.sortCol2;
    const d2 = document.getElementById('sort-dir-2'); if(d2) d2.value = layout.sortDir2;
  }
  toast('✅ تم تحميل التخطيط', 'success');
}

function loadSavedLayout() {
  const saved = localStorage.getItem('wg_dyn_layout');
  if (saved) {
    try {
      const layout = JSON.parse(saved);
      selectedCols = layout.cols || [...DEFAULT_SELECTED];
    } catch(e) {
      selectedCols = [...DEFAULT_SELECTED];
    }
  }
  renderColPicker();
}

function resetLayout() {
  selectedCols = [...DEFAULT_SELECTED];
  colFilters   = {};
  ['sort-col-1','sort-col-2'].forEach(id => { const el = document.getElementById(id); if(el) el.value=''; });
  renderColPicker();
  if (displayData.length) renderTable();
  toast('تم إعادة الضبط', 'info');
}

// ══════════════════════════════════════════════════════════
// EXPORT
// ══════════════════════════════════════════════════════════
function exportDynExcel() {
  if (!displayData.length) { toast('لا توجد بيانات', 'error'); return; }

  const cols = selectedCols.map(id => ALL_COLUMNS.find(c => c.id === id)).filter(Boolean);
  const headers = cols.map(c => c.label);
  const rows = displayData.map(row =>
    cols.map(col => {
      const v = getCellRawValue(row, col.id);
      if (v === null || v === undefined) return '';
      if (col.type === 'number')   return '$' + parseFloat(v).toFixed(2) + '/lot';
      if (col.type === 'currency') return parseFloat(v) || 0;
      if (col.type === 'badge')    return v === 'new' ? 'NEW' : 'SUB';
      if (col.type === 'status')   return v === 'modified' ? '🟡 معدّل' : v === 'new_added' ? '🆕 جديد' : 'عادي';
      return v ?? '';
    })
  );

  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
  // Column widths
  ws['!cols'] = cols.map(c => ({ wch: Math.round(c.width / 7) }));
  XLSX.utils.book_append_sheet(wb, ws, 'تقرير ديناميكي');
  XLSX.writeFile(wb, 'WafraGulf_DynReport_' + new Date().toISOString().slice(0,10) + '.xlsx');
  toast('تم تحميل Excel ✅', 'success');
}

function exportDynPdf() {
  if (!displayData.length) { toast('لا توجد بيانات', 'error'); return; }

  const { jsPDF } = window.jspdf;
  const cols = selectedCols.map(id => ALL_COLUMNS.find(c => c.id === id)).filter(Boolean);
  const doc  = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a3' });

  doc.setFontSize(14); doc.setTextColor(46, 134, 171);
  doc.text('وفرة الخليجية — تقرير ديناميكي', 210, 14, { align: 'center' });
  doc.setFontSize(8); doc.setTextColor(120, 154, 181);
  doc.text(`${new Date().toLocaleDateString()} | ${displayData.length} سجل | أعمدة: ${cols.map(c=>c.label).join(', ')}`, 210, 20, { align: 'center' });

  doc.autoTable({
    startY: 26,
    head: [cols.map(c => c.label)],
    body: displayData.map(row =>
      cols.map(col => {
        const v = getCellRawValue(row, col.id);
        if (v === null || v === undefined) return '—';
        if (col.type === 'number')   return '$' + parseFloat(v).toFixed(1);
        if (col.type === 'currency') return fmtK(parseFloat(v) || 0);
        if (col.type === 'badge')    return v === 'new' ? 'NEW' : 'SUB';
        if (col.type === 'status')   return v === 'modified' ? '🟡 Mod' : v === 'new_added' ? '🆕' : '—';
        return String(v ?? '—');
      })
    ),
    styles: { fontSize: 7, cellPadding: 2 },
    headStyles: { fillColor: [46, 134, 171], textColor: [255, 255, 255] },
    alternateRowStyles: { fillColor: [232, 244, 248] },
    didParseCell: d => {
      const row = d.row.raw;
      if (row) {
        // Check if status cell is modified
        const statusIdx = cols.findIndex(c => c.id === 'status');
        if (statusIdx >= 0 && row[statusIdx] === '🟡 Mod') {
          Object.values(d.row.cells).forEach(cell => {
            cell.styles.fillColor = [255, 248, 220];
          });
        }
      }
    }
  });

  doc.save('WafraGulf_DynReport_' + new Date().toISOString().slice(0,10) + '.pdf');
  toast('تم تحميل PDF ✅', 'success');
}

// ══════════════════════════════════════════════════════════
initPage();
</script>
@endpush
