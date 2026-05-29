@extends('layouts.app')
@section('title', 'Dynamic Report')
@section('page-title', 'Dynamic Report')

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

<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- Reports Sidebar Nav --}}
  <div id="rnav-sidebar" style="width:220px;flex-shrink:0;background:var(--bg2);border-left:1px solid var(--brd1);display:flex;flex-direction:column;padding:10px 0;">
    <div style="padding:12px 16px 14px;border-bottom:1px solid var(--brd1);margin-bottom:8px">
      <div id="rnav-hdr" style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px">📈 التقارير</div>
    </div>
    <a href="{{ route('reports.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);">📊</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap" id="rnav-lbl-monthly">التقارير الشهرية</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-monthly">تحليل شامل بالرسوم البيانية</div>
      </div>
    </a>
    <a href="{{ route('reports.dynamic') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:rgba(26,173,186,.15);border:1px solid rgba(26,173,186,.25);">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.25);border:1px solid rgba(26,173,186,.5);">🔧</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--pri2);white-space:nowrap" id="rnav-lbl-dynamic">تقرير ديناميكي</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-dynamic">بناء تقرير مخصص</div>
      </div>
      <span style="font-size:9px;padding:2px 7px;border-radius:10px;background:rgba(34,201,122,.2);color:var(--gr);font-weight:700;border:1px solid rgba(34,201,122,.3)" id="rnav-badge-new">جديد</span>
    </a>
    <a href="{{ route('reports.branch-monthly') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(245,166,35,.1);border:1px solid rgba(245,166,35,.2);">🏢</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap" id="rnav-lbl-branch">تقرير شهري للفروع</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="rnav-sub-branch">فرع + شهر محدد</div>
      </div>
    </a>
    <div style="flex:1"></div>
    <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
      <div id="rnav-footer" style="font-size:10px;color:var(--mu);line-height:1.6"></div>
    </div>
  </div>

  {{-- Content Panel --}}
  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

{{-- ═══ SECTION 1: FILTERS ═══ --}}
<div class="panel" style="margin-bottom:14px">
  <div class="panel-header">
    <div class="panel-title" id="dyn-title-filters">🔍 فلاتر التقرير</div>
    <div style="display:flex;gap:6px">
      <button class="layout-btn" id="dyn-btn-save-layout" onclick="saveLayout()">💾 حفظ التخطيط</button>
      <button class="layout-btn" id="dyn-btn-load-layout" onclick="loadLayout()">📂 تحميل التخطيط</button>
      <button class="layout-btn" id="dyn-btn-reset-layout" onclick="resetLayout()">↺ إعادة ضبط</button>
    </div>
  </div>
  <div class="panel-body">
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;margin-bottom:12px">
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-from">من شهر</label>
        <select id="rf-from" class="form-control"><option value="">—</option></select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-to">إلى شهر</label>
        <select id="rf-to" class="form-control"><option value="">—</option></select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-broker">البروكر</label>
        <select id="rf-broker" class="form-control"><option value="" id="dyn-opt-all-broker">الكل</option></select>
      </div>
      @if(auth()->user()?->isFinanceAdmin())
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-branch">الفرع</label>
        <select id="rf-branch" class="form-control"><option value="" id="dyn-opt-all-branch">كل الفروع</option></select>
      </div>
      @endif
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-status">حالة الكرت</label>
        <select id="rf-status" class="form-control">
          <option value="" id="dyn-opt-status-all">الكل</option>
          <option value="modified" id="dyn-opt-status-mod">معدّلة فقط 🟡</option>
          <option value="new_added" id="dyn-opt-status-new">مضافة جديدة فقط 🆕</option>
          <option value="active" id="dyn-opt-status-act">عادي فقط</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-kind">نوع الحساب</label>
        <select id="rf-kind" class="form-control">
          <option value="" id="dyn-opt-kind-all">الكل</option>
          <option value="new" id="dyn-opt-kind-new">جديد</option>
          <option value="sub" id="dyn-opt-kind-sub">فرعي</option>
        </select>
      </div>
      <div class="form-group" style="margin:0">
        <label class="form-label" id="dyn-lbl-min">حد أدنى إيداع $</label>
        <input type="number" id="rf-min" class="form-control" value="0" min="0">
      </div>
      <div class="form-group" style="margin:0;display:flex;flex-direction:column;justify-content:flex-end">
        <button class="btn btn-primary" id="dyn-btn-gen" onclick="fetchAndRender()">⚡ توليد التقرير</button>
      </div>
    </div>
  </div>
</div>

{{-- ═══ SECTION 2: COLUMN BUILDER ═══ --}}
<div class="panel" style="margin-bottom:14px" id="col-builder">
  <div class="panel-header">
    <div class="panel-title" id="dyn-title-cols">🔧 بناء الأعمدة — اسحب وأفلت لإعادة الترتيب</div>
    <div style="display:flex;gap:6px;align-items:center">
      <span style="font-size:11px;color:var(--mu)" id="dyn-hint-cols">اضغط على عمود لتفعيله/إلغائه</span>
      <button class="layout-btn" id="dyn-btn-all-cols" onclick="selectAllCols()">✅ الكل</button>
      <button class="layout-btn" id="dyn-btn-none-cols" onclick="selectNoneCols()">☐ لا شيء</button>
    </div>
  </div>
  <div class="panel-body">
    {{-- Available columns palette --}}
    <div class="col-zone-label" id="dyn-zone-lbl-avail">الأعمدة المتاحة — اضغط للإضافة</div>
    <div class="col-picker" id="col-available"></div>

    {{-- Selected columns (draggable order) --}}
    <div style="margin-top:12px">
      <div class="col-zone-label" id="dyn-zone-lbl-sel">الأعمدة المختارة — اسحب لإعادة الترتيب</div>
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
    <div class="panel-title" id="dyn-title-sort">↕️ ترتيب الصفوف</div>
  </div>
  <div class="panel-body" style="display:flex;gap:12px;flex-wrap:wrap;align-items:flex-end">
    <div class="form-group" style="margin:0">
      <label class="form-label" id="dyn-lbl-sort1">ترتيب أولي حسب</label>
      <select id="sort-col-1" class="form-control" style="min-width:160px">
        <option value="" id="dyn-opt-nosort1">— بلا ترتيب —</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label" id="dyn-lbl-dir1">اتجاه</label>
      <select id="sort-dir-1" class="form-control">
        <option value="asc" id="dyn-opt-asc1">تصاعدي ↑</option>
        <option value="desc" id="dyn-opt-desc1">تنازلي ↓</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label" id="dyn-lbl-sort2">ترتيب ثانوي حسب</label>
      <select id="sort-col-2" class="form-control" style="min-width:160px">
        <option value="" id="dyn-opt-nosort2">— بلا —</option>
      </select>
    </div>
    <div class="form-group" style="margin:0">
      <label class="form-label" id="dyn-lbl-dir2">اتجاه</label>
      <select id="sort-dir-2" class="form-control">
        <option value="asc" id="dyn-opt-asc2">تصاعدي ↑</option>
        <option value="desc" id="dyn-opt-desc2">تنازلي ↓</option>
      </select>
    </div>
    <button class="btn btn-ghost btn-sm" id="dyn-btn-apply-sort" onclick="applySort()">تطبيق الترتيب</button>
    <button class="btn btn-ghost btn-sm" id="dyn-btn-reset-sort" onclick="resetSort()">↺ إعادة ضبط الترتيب</button>
  </div>
</div>

{{-- ═══ SECTION 4: THE TABLE ═══ --}}
<div class="panel" id="result-panel" style="display:none">
  <div class="panel-header">
    <div class="panel-title">
      <span id="dyn-title-results">📋 نتائج التقرير الديناميكي</span>
      <span id="result-count" style="font-size:11px;color:var(--mu);font-weight:400;margin-right:8px"></span>
    </div>
    <div style="display:flex;gap:6px;flex-wrap:wrap">
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportDynExcel()">📗 Excel</button>
      <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)"   onclick="exportDynPdf()">📄 PDF</button>
      <button class="btn btn-sm" id="dyn-btn-print" style="background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.25);color:var(--pri2)" onclick="window.print()">🖨️ طباعة</button>
    </div>
  </div>

  {{-- Inline column filter toggle --}}
  <div style="padding:8px 16px;border-bottom:1px solid var(--brd1);display:flex;gap:8px;align-items:center">
    <span style="font-size:11px;color:var(--mu)" id="dyn-lbl-tbl-filter">فلترة الجدول:</span>
    <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer">
      <input type="checkbox" id="toggle-col-filters" onchange="toggleColFilters(this.checked)" style="accent-color:var(--pri)">
      <span id="dyn-lbl-col-filters">إظهار فلاتر الأعمدة</span>
    </label>
    <label style="display:flex;align-items:center;gap:5px;font-size:11px;cursor:pointer">
      <input type="checkbox" id="toggle-row-drag" onchange="toggleRowDrag(this.checked)" style="accent-color:var(--pri)">
      <span id="dyn-lbl-row-drag">تمكين سحب الصفوف</span>
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

  </div>{{-- content panel --}}
</div>{{-- reports shell --}}

@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// BILINGUAL DICTIONARY
// ══════════════════════════════════════════════════════════
const DYN = {
  ar: {
    titleFilters:'🔍 فلاتر التقرير',
    btnSaveLayout:'💾 حفظ التخطيط', btnLoadLayout:'📂 تحميل التخطيط', btnResetLayout:'↺ إعادة ضبط',
    lblFrom:'من شهر', lblTo:'إلى شهر', lblBroker:'البروكر', lblBranch:'الفرع',
    lblStatus:'حالة الكرت', lblKind:'نوع الحساب', lblMin:'حد أدنى إيداع $',
    btnGen:'⚡ توليد التقرير',
    optAllBroker:'الكل', optAllBranch:'كل الفروع',
    optStatusAll:'الكل', optStatusMod:'معدّلة فقط 🟡', optStatusNew:'مضافة جديدة فقط 🆕', optStatusAct:'عادي فقط',
    optKindAll:'الكل', optKindNew:'جديد', optKindSub:'فرعي',
    titleCols:'🔧 بناء الأعمدة — اسحب وأفلت لإعادة الترتيب',
    hintCols:'اضغط على عمود لتفعيله/إلغائه',
    btnAllCols:'✅ الكل', btnNoneCols:'☐ لا شيء',
    zoneLblAvail:'الأعمدة المتاحة — اضغط للإضافة',
    zoneLblSel:'الأعمدة المختارة — اسحب لإعادة الترتيب',
    colEmptyHint:'اضغط على أعمدة من القائمة أعلاه أو اسحبها هنا',
    titleSort:'↕️ ترتيب الصفوف',
    lblSort1:'ترتيب أولي حسب', lblDir1:'اتجاه', lblSort2:'ترتيب ثانوي حسب', lblDir2:'اتجاه',
    optNoSort1:'— بلا ترتيب —', optNoSort2:'— بلا —',
    optAsc:'تصاعدي ↑', optDesc:'تنازلي ↓',
    btnApplySort:'تطبيق الترتيب', btnResetSort:'↺ إعادة ضبط الترتيب',
    titleResults:'📋 نتائج التقرير الديناميكي',
    btnPrint:'🖨️ طباعة',
    lblTblFilter:'فلترة الجدول:', lblColFilters:'إظهار فلاتر الأعمدة', lblRowDrag:'تمكين سحب الصفوف',
    filterPlaceholder:'فلتر...',
    recordSuffix:' سجل',
    toastNoCols:'اختر أعمدة أولاً',
    toastLoadErr:'خطأ في تحميل البيانات',
    toastLoaded:'تم تحميل {n} سجل',
    toastSaved:'✅ تم حفظ التخطيط',
    toastNoLayout:'لا يوجد تخطيط محفوظ',
    toastLayoutLoaded:'✅ تم تحميل التخطيط',
    toastReset:'تم إعادة الضبط',
    toastNoData:'لا توجد بيانات',
    toastExcelDone:'تم تحميل Excel ✅',
    toastPdfDone:'تم تحميل PDF ✅',
    summaryTotals:'الإجماليات:', summaryModified:'معدّلة:',
    statusModified:'✏️ معدّل', statusNew:'🆕 جديد', statusActive:'✅ عادي', statusInactive:'غير نشط',
    pdfTitle:'وفرة الخليجية — تقرير ديناميكي',
    pdfSubtitle:'{date} | {n} سجل | أعمدة: {cols}',
    xlsxSheet:'تقرير ديناميكي',
    xlsxStatusMod:'🟡 معدّل', xlsxStatusNew:'🆕 جديد', xlsxStatusAct:'عادي',
  },
  en: {
    titleFilters:'🔍 Report Filters',
    btnSaveLayout:'💾 Save Layout', btnLoadLayout:'📂 Load Layout', btnResetLayout:'↺ Reset',
    lblFrom:'From Month', lblTo:'To Month', lblBroker:'Broker', lblBranch:'Branch',
    lblStatus:'Card Status', lblKind:'Account Type', lblMin:'Min Deposit $',
    btnGen:'⚡ Generate Report',
    optAllBroker:'All', optAllBranch:'All Branches',
    optStatusAll:'All', optStatusMod:'Modified Only 🟡', optStatusNew:'New Added Only 🆕', optStatusAct:'Active Only',
    optKindAll:'All', optKindNew:'New', optKindSub:'Sub',
    titleCols:'🔧 Column Builder — Drag & Drop to Reorder',
    hintCols:'Click a column to toggle it',
    btnAllCols:'✅ All', btnNoneCols:'☐ None',
    zoneLblAvail:'Available Columns — click to add',
    zoneLblSel:'Selected Columns — drag to reorder',
    colEmptyHint:'Click columns above or drag them here',
    titleSort:'↕️ Row Sorting',
    lblSort1:'Primary Sort By', lblDir1:'Direction', lblSort2:'Secondary Sort By', lblDir2:'Direction',
    optNoSort1:'— No Sort —', optNoSort2:'— None —',
    optAsc:'Ascending ↑', optDesc:'Descending ↓',
    btnApplySort:'Apply Sort', btnResetSort:'↺ Reset Sort',
    titleResults:'📋 Dynamic Report Results',
    btnPrint:'🖨️ Print',
    lblTblFilter:'Table Filter:', lblColFilters:'Show Column Filters', lblRowDrag:'Enable Row Drag',
    filterPlaceholder:'Filter...',
    recordSuffix:' records',
    toastNoCols:'Please select columns first',
    toastLoadErr:'Error loading data',
    toastLoaded:'Loaded {n} records',
    toastSaved:'✅ Layout saved',
    toastNoLayout:'No saved layout found',
    toastLayoutLoaded:'✅ Layout loaded',
    toastReset:'Reset done',
    toastNoData:'No data available',
    toastExcelDone:'Excel downloaded ✅',
    toastPdfDone:'PDF downloaded ✅',
    summaryTotals:'Totals:', summaryModified:'Modified:',
    statusModified:'✏️ Modified', statusNew:'🆕 New', statusActive:'✅ Active', statusInactive:'Inactive',
    pdfTitle:'Wafra Gulf — Dynamic Report',
    pdfSubtitle:'{date} | {n} records | Columns: {cols}',
    xlsxSheet:'Dynamic Report',
    xlsxStatusMod:'🟡 Modified', xlsxStatusNew:'🆕 New', xlsxStatusAct:'Active',
  }
};
function dynL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function dyn(k) { return (DYN[dynL()] || DYN.ar)[k] || (DYN.ar)[k] || k; }

function dynApplyLang() {
  const _t = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
  _t('dyn-title-filters',    dyn('titleFilters'));
  _t('dyn-btn-save-layout',  dyn('btnSaveLayout'));
  _t('dyn-btn-load-layout',  dyn('btnLoadLayout'));
  _t('dyn-btn-reset-layout', dyn('btnResetLayout'));
  _t('dyn-lbl-from',         dyn('lblFrom'));
  _t('dyn-lbl-to',           dyn('lblTo'));
  _t('dyn-lbl-broker',       dyn('lblBroker'));
  _t('dyn-lbl-branch',       dyn('lblBranch'));
  _t('dyn-lbl-status',       dyn('lblStatus'));
  _t('dyn-lbl-kind',         dyn('lblKind'));
  _t('dyn-lbl-min',          dyn('lblMin'));
  _t('dyn-btn-gen',          dyn('btnGen'));
  _t('dyn-opt-all-broker',   dyn('optAllBroker'));
  _t('dyn-opt-all-branch',   dyn('optAllBranch'));
  _t('dyn-opt-status-all',   dyn('optStatusAll'));
  _t('dyn-opt-status-mod',   dyn('optStatusMod'));
  _t('dyn-opt-status-new',   dyn('optStatusNew'));
  _t('dyn-opt-status-act',   dyn('optStatusAct'));
  _t('dyn-opt-kind-all',     dyn('optKindAll'));
  _t('dyn-opt-kind-new',     dyn('optKindNew'));
  _t('dyn-opt-kind-sub',     dyn('optKindSub'));
  _t('dyn-title-cols',       dyn('titleCols'));
  _t('dyn-hint-cols',        dyn('hintCols'));
  _t('dyn-btn-all-cols',     dyn('btnAllCols'));
  _t('dyn-btn-none-cols',    dyn('btnNoneCols'));
  _t('dyn-zone-lbl-avail',   dyn('zoneLblAvail'));
  _t('dyn-zone-lbl-sel',     dyn('zoneLblSel'));
  _t('col-selected-empty',   dyn('colEmptyHint'));
  _t('dyn-title-sort',       dyn('titleSort'));
  _t('dyn-lbl-sort1',        dyn('lblSort1'));
  _t('dyn-lbl-dir1',         dyn('lblDir1'));
  _t('dyn-lbl-sort2',        dyn('lblSort2'));
  _t('dyn-lbl-dir2',         dyn('lblDir2'));
  _t('dyn-opt-nosort1',      dyn('optNoSort1'));
  _t('dyn-opt-nosort2',      dyn('optNoSort2'));
  _t('dyn-opt-asc1',         dyn('optAsc'));
  _t('dyn-opt-desc1',        dyn('optDesc'));
  _t('dyn-opt-asc2',         dyn('optAsc'));
  _t('dyn-opt-desc2',        dyn('optDesc'));
  _t('dyn-btn-apply-sort',   dyn('btnApplySort'));
  _t('dyn-btn-reset-sort',   dyn('btnResetSort'));
  _t('dyn-title-results',    dyn('titleResults'));
  _t('dyn-btn-print',        dyn('btnPrint'));
  _t('dyn-lbl-tbl-filter',   dyn('lblTblFilter'));
  _t('dyn-lbl-col-filters',  dyn('lblColFilters'));
  _t('dyn-lbl-row-drag',     dyn('lblRowDrag'));
  // Re-render picker & table if data already loaded
  if (selectedCols && selectedCols.length) renderColPicker();
  if (displayData && displayData.length)   renderTable();
}

// ══════════════════════════════════════════════════════════
// ALL AVAILABLE COLUMNS DEFINITION
// ══════════════════════════════════════════════════════════
const ALL_COLUMNS = [
  { id:'account_number',    label:'رقم الحساب',              labelEn:'Account #',           type:'text',    width:130, frozen:true },
  { id:'month',             label:'الشهر',                   labelEn:'Month',               type:'text',    width:100 },
  { id:'branch',            label:'الفرع',                   labelEn:'Branch',              type:'text',    width:130 },
  { id:'account_kind',      label:'نوع الحساب',              labelEn:'Acc. Kind',           type:'badge',   width:80  },
  { id:'account_type',      label:'نوع (ECN/STP)',           labelEn:'Type (ECN/STP)',      type:'text',    width:90  },
  { id:'account_status',    label:'حالة الحساب',             labelEn:'Acc. Status',         type:'text',    width:110 },
  { id:'trading_type',      label:'نوع التداول',             labelEn:'Trading Type',        type:'text',    width:110 },
  { id:'broker',            label:'البروكر',                 labelEn:'Broker',              type:'text',    width:140 },
  { id:'broker_commission', label:'عمولة البروكر',           labelEn:'Broker Comm.',        type:'number',  width:130 },
  { id:'marketer',          label:'مسوّق داخلي',            labelEn:'Int. Marketer',       type:'text',    width:140 },
  { id:'marketer_commission',label:'عمولة المسوّق',         labelEn:'Mkt. Comm.',          type:'number',  width:120 },
  { id:'ext_marketer1',     label:'مسوّق خارجي 1',          labelEn:'Ext. Marketer 1',     type:'text',    width:140 },
  { id:'ext_commission1',   label:'عمولة خارجي 1',          labelEn:'Ext. Comm. 1',        type:'number',  width:130 },
  { id:'ext_marketer2',     label:'مسوّق خارجي 2',          labelEn:'Ext. Marketer 2',     type:'text',    width:140 },
  { id:'ext_commission2',   label:'عمولة خارجي 2',          labelEn:'Ext. Comm. 2',        type:'number',  width:130 },
  { id:'total_commission',  label:'إجمالي العمولات',         labelEn:'Total Comm.',         type:'number',  width:140 },
  { id:'initial_deposit',   label:'إيداع فتح الحساب',        labelEn:'Initial Deposit',     type:'currency',width:140 },
  { id:'monthly_deposit',   label:'الإيداع الشهري المتوقع', labelEn:'Monthly Deposit',     type:'currency',width:155 },
  { id:'forex_commission',  label:'Forex Comm.',             labelEn:'Forex Comm.',         type:'number',  width:110 },
  { id:'futures_commission',label:'Futures Comm.',           labelEn:'Futures Comm.',       type:'number',  width:110 },
  { id:'status',            label:'الحالة',                  labelEn:'Status',              type:'status',  width:100 },
  { id:'created_by',        label:'أنشئ بواسطة',             labelEn:'Created By',          type:'text',    width:130 },
];

// Helper: bilingual column label
function colLabel(col) { return dynL() === 'en' ? (col.labelEn || col.label) : col.label; }

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
  dynApplyLang();
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
      ${colLabel(col)}
      ${selectedCols.includes(col.id) ? '<button class="remove-btn" onclick="event.stopPropagation();removeCol(\''+col.id+'\')">✕</button>' : ''}
    </div>`).join('');

  // Selected zone — ordered
  renderSelectedZone();
  updateSortSelects();
}

function renderSelectedZone() {
  const zone  = document.getElementById('col-selected');

  if (!selectedCols.length) {
    zone.innerHTML = '<div style="color:var(--mu);font-size:11px;text-align:center;padding:6px" id="col-selected-empty">'+dyn('colEmptyHint')+'</div>';
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
      ${colLabel(col)}
      <button class="remove-btn" onclick="removeCol('${id}')">✕</button>
    </div>`;
  }).join('');

  zone.innerHTML = pills;
}

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
    toast(dyn('toastNoCols'), 'error');
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
  if (!r.success) { toast(dyn('toastLoadErr'), 'error'); return; }

  rawData      = r.data || [];
  filteredData = [...rawData];
  colFilters   = {};

  applySort();
  document.getElementById('result-panel').style.display  = 'block';
  document.getElementById('sort-controls').style.display = 'block';
  document.getElementById('result-count').textContent    = rawData.length + dyn('recordSuffix');

  toast(dyn('toastLoaded').replace('{n}', rawData.length), 'success');
}

// ══════════════════════════════════════════════════════════
// SORT
// ══════════════════════════════════════════════════════════
function updateSortSelects() {
  ['sort-col-1','sort-col-2'].forEach(selId => {
    const sel = document.getElementById(selId); if (!sel) return;
    const cur = sel.value;
    sel.innerHTML = selId === 'sort-col-1'
      ? `<option value="" id="dyn-opt-nosort1">${dyn('optNoSort1')}</option>`
      : `<option value="" id="dyn-opt-nosort2">${dyn('optNoSort2')}</option>`;
    selectedCols.forEach(id => {
      const col = ALL_COLUMNS.find(c => c.id === id); if (!col) return;
      const o = document.createElement('option');
      o.value = col.id; o.textContent = colLabel(col);
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
        <th><input type="text" placeholder="${dyn('filterPlaceholder')}" value="${colFilters[col.id]||''}"
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
          ${colLabel(col)}
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
  const summaryParts = [`<span style="color:var(--mu)">${dyn('summaryTotals')}</span>`];
  numCols.forEach(col => {
    const total = displayData.reduce((s, r) => s + (parseFloat(getCellRawValue(r, col.id)) || 0), 0);
    if (total > 0) {
      const formatted = col.type === 'currency' ? fmtK(total) : '$' + total.toFixed(2) + '/lot';
      summaryParts.push(`<span class="mono">${colLabel(col)}: <b style="color:var(--pri2)">${formatted}</b></span>`);
    }
  });
  summaryParts.push(`<span style="color:var(--or)">${dyn('summaryModified')} <b>${displayData.filter(r=>r.status==='modified').length}</b></span>`);
  document.getElementById('dyn-summary').innerHTML = summaryParts.join('');
  document.getElementById('result-count').textContent = displayData.length + dyn('recordSuffix');
  updateSortSelects();
}

// ── Cell value accessors ──────────────────────────────────
function getCellRawValue(row, colId) {
  const map = {
    account_number:     row.account_number,
    month:              row.month,
    branch:             row.branch?.name_ar,
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
    case 'badge':
      return v === 'new'
        ? '<span class="badge badge-green">NEW</span>'
        : '<span class="badge badge-blue">SUB</span>';
    case 'status':
      const statusMap = {
        modified:  `<span class="badge badge-orange">${dyn('statusModified')}</span>`,
        new_added: `<span class="badge badge-green">${dyn('statusNew')}</span>`,
        active:    `<span class="badge badge-blue">${dyn('statusActive')}</span>`,
        inactive:  `<span class="badge badge-gray">${dyn('statusInactive')}</span>`,
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
  toast(dyn('toastSaved'), 'success');
}

function loadLayout() {
  const saved = localStorage.getItem('wg_dyn_layout');
  if (!saved) { toast(dyn('toastNoLayout'), 'info'); return; }
  const layout = JSON.parse(saved);
  selectedCols = layout.cols || [...DEFAULT_SELECTED];
  renderColPicker();
  if (layout.sortCol1) {
    const s1 = document.getElementById('sort-col-1'); if(s1) s1.value = layout.sortCol1;
    const d1 = document.getElementById('sort-dir-1'); if(d1) d1.value = layout.sortDir1;
    const s2 = document.getElementById('sort-col-2'); if(s2) s2.value = layout.sortCol2;
    const d2 = document.getElementById('sort-dir-2'); if(d2) d2.value = layout.sortDir2;
  }
  toast(dyn('toastLayoutLoaded'), 'success');
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
  toast(dyn('toastReset'), 'info');
}

// ══════════════════════════════════════════════════════════
// EXPORT
// ══════════════════════════════════════════════════════════
function exportDynExcel() {
  if (!displayData.length) { toast(dyn('toastNoData'), 'error'); return; }

  const cols = selectedCols.map(id => ALL_COLUMNS.find(c => c.id === id)).filter(Boolean);
  const headers = cols.map(c => colLabel(c));
  const rows = displayData.map(row =>
    cols.map(col => {
      const v = getCellRawValue(row, col.id);
      if (v === null || v === undefined) return '';
      if (col.type === 'number')   return '$' + parseFloat(v).toFixed(2) + '/lot';
      if (col.type === 'currency') return parseFloat(v) || 0;
      if (col.type === 'badge')    return v === 'new' ? 'NEW' : 'SUB';
      if (col.type === 'status')   return v === 'modified' ? dyn('xlsxStatusMod') : v === 'new_added' ? dyn('xlsxStatusNew') : dyn('xlsxStatusAct');
      return v ?? '';
    })
  );

  const wb = XLSX.utils.book_new();
  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
  // Column widths
  ws['!cols'] = cols.map(c => ({ wch: Math.round(c.width / 7) }));
  XLSX.utils.book_append_sheet(wb, ws, dyn('xlsxSheet'));
  XLSX.writeFile(wb, 'WafraGulf_DynReport_' + new Date().toISOString().slice(0,10) + '.xlsx');
  toast(dyn('toastExcelDone'), 'success');
}

function exportDynPdf() {
  if (!displayData.length) { toast(dyn('toastNoData'), 'error'); return; }

  const { jsPDF } = window.jspdf;
  const cols = selectedCols.map(id => ALL_COLUMNS.find(c => c.id === id)).filter(Boolean);
  const doc  = new jsPDF({ orientation: 'landscape', unit: 'mm', format: 'a3' });

  doc.setFontSize(14); doc.setTextColor(46, 134, 171);
  doc.text(dyn('pdfTitle'), 210, 14, { align: 'center' });
  doc.setFontSize(8); doc.setTextColor(120, 154, 181);
  const subtitle = dyn('pdfSubtitle')
    .replace('{date}', new Date().toLocaleDateString())
    .replace('{n}', displayData.length)
    .replace('{cols}', cols.map(c=>colLabel(c)).join(', '));
  doc.text(subtitle, 210, 20, { align: 'center' });

  doc.autoTable({
    startY: 26,
    head: [cols.map(c => colLabel(c))],
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
  toast(dyn('toastPdfDone'), 'success');
}

// ══════════════════════════════════════════════════════════
initPage();

/* ── Reports sidebar nav bilingual ──────────────────────── */
(function(){
  const RN={
    ar:{hdr:'📈 التقارير',lblMonthly:'التقارير الشهرية',subMonthly:'تحليل شامل بالرسوم البيانية',lblDynamic:'تقرير ديناميكي',subDynamic:'بناء تقرير مخصص',badgeNew:'جديد',lblBranch:'تقرير شهري للفروع',subBranch:'فرع + شهر محدد',footer:'منصة وفرة الخليجية\nلإدارة العمولات'},
    en:{hdr:'📈 Reports',lblMonthly:'Monthly Reports',subMonthly:'Full analysis with charts',lblDynamic:'Dynamic Report',subDynamic:'Build custom report',badgeNew:'New',lblBranch:'Branch Monthly Report',subBranch:'Branch + specific month',footer:'Wafra Gulf Platform\nCommission Management'},
  };
  function rNavApplyLang(){
    const L=(typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';
    const d=RN[L]||RN.ar;
    const _t=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
    _t('rnav-hdr',d.hdr);_t('rnav-lbl-monthly',d.lblMonthly);_t('rnav-sub-monthly',d.subMonthly);
    _t('rnav-lbl-dynamic',d.lblDynamic);_t('rnav-sub-dynamic',d.subDynamic);_t('rnav-badge-new',d.badgeNew);
    _t('rnav-lbl-branch',d.lblBranch);_t('rnav-sub-branch',d.subBranch);
    const fn=document.getElementById('rnav-footer');if(fn)fn.innerHTML=d.footer.replace('\n','<br>');
  }
  const _rnOrig=window.applyLang;
  window.applyLang=function(lang){if(_rnOrig)_rnOrig(lang);rNavApplyLang();dynApplyLang();};
  rNavApplyLang();
})();
</script>
@endpush
