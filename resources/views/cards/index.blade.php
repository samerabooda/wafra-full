@extends('layouts.app')
@section('title', 'Commission Cards')
@section('page-title', 'Commission Cards')

@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'index'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">
<style>
.chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:700;white-space:nowrap}
.chip-new      {background:rgba(34,201,122,.12);color:#22c97a;border:1px solid rgba(34,201,122,.25)}
.chip-modified {background:rgba(245,166,35,.12);color:#f5a623;border:1px solid rgba(245,166,35,.25)}
.chip-active   {background:rgba(46,134,171,.12);color:#3a9db5;border:1px solid rgba(46,134,171,.25)}
.chip-inactive {background:rgba(120,120,140,.1);color:#8899aa;border:1px solid rgba(120,120,140,.2)}
.chip-cc       {background:linear-gradient(135deg,#7b68ee22,#5f4fcf22);color:#7b68ee;border:1px solid rgba(123,104,238,.3)}
.chip-new-acc  {background:rgba(34,201,122,.1);color:#22c97a;border:1px solid rgba(34,201,122,.2)}
.chip-sub-acc  {background:rgba(46,134,171,.1);color:#2e86ab;border:1px solid rgba(46,134,171,.2)}
.chip-overlimit{background:rgba(224,72,72,.14);color:#e04848;border:1px solid rgba(224,72,72,.35)}
.chip-duplicate{background:rgba(245,152,32,.14);color:#f59820;border:1px solid rgba(245,152,32,.35)}
.data-table tr.row-cc-card td{background:linear-gradient(90deg,rgba(123,104,238,.06),transparent 70%)!important}
.data-table tr.row-cc-card td:first-child{border-right:3px solid #7b68ee}
.data-table tr.row-cc-card:hover td{background:linear-gradient(90deg,rgba(123,104,238,.12),rgba(123,104,238,.03) 70%)!important}
.data-table tr.row-modified td{background:rgba(245,166,35,.05)!important}
.data-table tr.row-modified td:first-child{border-right:3px solid #f5a623}
.data-table tr.row-new_added td:first-child{border-right:3px solid #22c97a}
.data-table tr.row-inactive td{opacity:.65}
.data-table tr.row-flagged td{background:rgba(224,72,72,.08)!important}
.data-table tr.row-flagged td:first-child{border-right:3px solid #e04848!important}
.data-table tr.row-flagged:hover td{background:rgba(224,72,72,.15)!important}
.review-banner{background:linear-gradient(135deg,rgba(224,72,72,.10),rgba(224,72,72,.04));border:1px solid rgba(224,72,72,.30);border-radius:14px;padding:12px 16px;margin-bottom:14px;display:flex;align-items:center;gap:12px;flex-wrap:wrap}
.comm-low{color:#22c97a}.comm-mid{color:#f5a623}.comm-high{color:#e05050}
.kpi-strip{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.kpi-pill{display:flex;flex-direction:column;align-items:center;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;padding:10px 16px;min-width:120px;flex:1}
.kpi-pill-val{font-size:18px;font-weight:800;font-family:'JetBrains Mono',monospace}
.kpi-pill-lbl{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-top:3px}
.filter-bar{display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;padding:14px 16px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;margin-bottom:14px}
.filter-group{display:flex;flex-direction:column;gap:4px}
.filter-label{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}
.filter-bar .form-control{min-width:110px}
.legend{display:flex;gap:12px;flex-wrap:wrap;align-items:center;padding:8px 16px;background:var(--bg2);border-radius:10px;margin-bottom:10px;font-size:11px;color:var(--mu)}
.legend-dot{width:10px;height:10px;border-radius:3px;display:inline-block}

/* ─── Column header sort + filter controls ─────────────── */
.data-table thead{position:sticky;top:0;z-index:30;background:var(--bg2);box-shadow:0 2px 0 var(--brd1)}
.data-table thead th{position:relative;padding:0!important;background:var(--bg2)}
.th-content{display:flex;align-items:center;gap:6px;padding:10px 12px;cursor:default;user-select:none;white-space:nowrap}
.th-label{flex:1;font-weight:700;font-size:11.5px;color:var(--tx)}
.th-ctrls{display:flex;align-items:center;gap:2px}
.th-btn{background:none;border:none;padding:2px 5px;border-radius:5px;cursor:pointer;color:var(--mu);font-size:11px;line-height:1;transition:all .15s;font-family:inherit}
.th-btn:hover{background:var(--card-bg);color:var(--pri2)}
.th-btn.active{background:rgba(26,173,186,.18);color:var(--pri2);font-weight:800}
.th-btn.filtered{background:rgba(245,166,35,.2);color:#f5a623}
.th-sort{font-size:12px;min-width:16px;text-align:center}
.th-filter{font-size:10px}
.th-pop{position:absolute;top:calc(100% - 2px);inset-inline-start:0;min-width:230px;max-width:280px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:10px;box-shadow:0 10px 32px rgba(0,0,0,.35);z-index:60;display:none;overflow:hidden}
.th-pop.open{display:block;animation:popFade .15s ease-out}
@keyframes popFade{from{opacity:0;transform:translateY(-4px)}to{opacity:1;transform:translateY(0)}}
.th-pop-head{padding:8px 10px;border-bottom:1px solid var(--brd1);display:flex;gap:6px;align-items:center;background:var(--bg2)}
.th-pop-head input{flex:1;background:var(--bg3);border:1px solid var(--brd1);border-radius:6px;padding:5px 8px;font-size:11px;color:var(--tx);font-family:inherit}
.th-pop-head .th-pop-close{background:none;border:none;color:var(--mu);cursor:pointer;font-size:13px;padding:3px 6px;border-radius:4px}
.th-pop-head .th-pop-close:hover{background:var(--card-bg);color:var(--re)}
.th-pop-list{max-height:260px;overflow-y:auto;padding:4px 0}
.th-pop-opt{display:flex;align-items:center;gap:8px;padding:6px 10px;cursor:pointer;transition:background .12s;font-size:11.5px;color:var(--tx)}
.th-pop-opt:hover{background:var(--bg2)}
.th-pop-opt input[type=checkbox]{margin:0;cursor:pointer}
.th-pop-opt .opt-cnt{margin-inline-start:auto;color:var(--mu);font-size:10px;font-family:'JetBrains Mono',monospace}
.th-pop-foot{padding:8px 10px;border-top:1px solid var(--brd1);display:flex;gap:6px;justify-content:space-between;background:var(--bg2)}
.th-pop-foot button{background:var(--bg3);border:1px solid var(--brd1);color:var(--mu);font-size:10px;padding:4px 10px;border-radius:5px;cursor:pointer;font-family:inherit;font-weight:700}
.th-pop-foot button:hover{background:var(--card-bg);color:var(--tx)}
.th-pop-foot .b-apply{background:rgba(26,173,186,.15);border-color:rgba(26,173,186,.35);color:var(--pri2)}
.th-pop-foot .b-apply:hover{background:var(--pri2);color:#fff}
.th-pop-empty{text-align:center;padding:20px;color:var(--mu);font-size:11px}

/* Column resize affordance */
.data-table th{min-width:70px}
.data-table thead tr{background:var(--bg2)}

/* Active filter indicator on top-level filter bar */
.active-filters-strip{display:flex;flex-wrap:wrap;gap:6px;padding:6px 14px;margin-bottom:10px;align-items:center;font-size:11px;color:var(--mu)}
.active-filters-strip:empty{display:none}
.active-filter-chip{background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3);color:var(--pri2);padding:3px 10px;border-radius:14px;display:inline-flex;align-items:center;gap:6px;font-weight:700}
.active-filter-chip button{background:none;border:none;color:var(--pri2);cursor:pointer;font-size:11px;padding:0;line-height:1}
.active-filter-chip button:hover{color:var(--re)}
</style>

<!-- Account Number Quick Search -->
<div id="ac-search-bar" style="
  display:flex;gap:10px;align-items:center;
  background:linear-gradient(135deg,rgba(26,173,186,.08),rgba(26,173,186,.04));
  border:1px solid rgba(26,173,186,.25);border-radius:14px;
  padding:14px 18px;margin-bottom:14px;flex-wrap:wrap;
">
  <span style="font-size:20px">🔍</span>
  <div style="flex:1;min-width:200px">
    <div style="font-size:11px;font-weight:700;color:var(--pri2);margin-bottom:5px" id="idx-acsearch-lbl">بحث سريع برقم الحساب</div>
    <div style="display:flex;gap:8px;align-items:center">
      <input type="text" id="ac-search-input" class="form-control"
        placeholder="أدخل رقم الحساب ..." inputmode="numeric"
        style="flex:1;max-width:280px;font-family:'JetBrains Mono',monospace;font-size:15px;font-weight:700;letter-spacing:.5px"
        onkeydown="if(event.key==='Enter')acSearch()">
      <button class="btn btn-primary btn-sm" onclick="acSearch()" style="white-space:nowrap" id="idx-acsearch-btn">بحث</button>
      <button class="btn btn-ghost btn-sm" onclick="acSearchClear()" id="idx-acsearch-clear" style="display:none">✕ مسح</button>
    </div>
  </div>
  <div id="ac-search-result-lbl" style="font-size:12px;color:var(--mu)"></div>
</div>

<!-- Needs-Review Banner (shown only when filter=needs_review) -->
<div id="review-banner" class="review-banner" style="display:none">
  <span style="font-size:22px">⚠</span>
  <div style="flex:1;min-width:200px">
    <div style="font-size:13px;font-weight:800;color:#e04848" id="idx-rev-title">كروت تحتاج مراجعة</div>
    <div style="font-size:11px;color:var(--mu);margin-top:2px" id="idx-rev-sub">كروت تجاوزت الحد الأعلى (7$) أو حسابات مكرّرة من الاستيراد</div>
  </div>
  <div id="review-banner-counts" style="display:flex;gap:8px;flex-wrap:wrap"></div>
  <a href="/cards" class="btn btn-ghost btn-sm" id="idx-rev-clear">✕ عرض الكل</a>
</div>

<!-- KPI Summary Strip -->
<div class="kpi-strip">
  <div class="kpi-pill kpi-blue">
    <span class="kpi-pill-val" id="kpi-total" style="color:var(--pri2)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-total">إجمالي الكروت</span>
  </div>
  <div class="kpi-pill kpi-green">
    <span class="kpi-pill-val" id="kpi-new" style="color:var(--gr)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-new">جديد NEW</span>
  </div>
  <div class="kpi-pill kpi-blue">
    <span class="kpi-pill-val" id="kpi-sub" style="color:var(--pri)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-sub">فرعي SUB</span>
  </div>
  <div class="kpi-pill kpi-orange">
    <span class="kpi-pill-val" id="kpi-modified" style="color:var(--or)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-mod">معدّل</span>
  </div>
  <div class="kpi-pill kpi-purple">
    <span class="kpi-pill-val" id="kpi-cc" style="color:var(--pu)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-cc">من CC</span>
  </div>
  <div class="kpi-pill kpi-teal">
    <span class="kpi-pill-val" id="kpi-dep" style="color:var(--pri2)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-dep">إجمالي الإيداع</span>
  </div>
  <div class="kpi-pill kpi-green">
    <span class="kpi-pill-val" id="kpi-avgcomm" style="color:var(--gr)">—</span>
    <span class="kpi-pill-lbl" id="idx-kpi-avg">متوسط العمولة</span>
  </div>
</div>

<!-- Filter Bar (global quick filters) -->
<div class="filter-bar">
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-month">الشهر</span>
    <select id="f-month" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-allmonths">كل الشهور</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-broker">البروكر</span>
    <select id="f-broker" class="form-control" style="min-width:140px" onchange="loadCards()">
      <option value="" id="idx-opt-allbrokers">كل البروكرات</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-kind">نوع الحساب</span>
    <select id="f-kind" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-all1">الكل</option>
      <option value="new" id="idx-opt-kindnew">🟢 جديد NEW</option>
      <option value="sub" id="idx-opt-kindsub">🔵 فرعي SUB</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-status">الحالة</span>
    <select id="f-status" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-all2">الكل</option>
      <option value="active"    id="idx-opt-stactive">✅ عادي</option>
      <option value="modified"  id="idx-opt-stmod">✏️ معدّل</option>
      <option value="new_added" id="idx-opt-stnew">🆕 مضاف جديد</option>
      <option value="inactive"  id="idx-opt-stinact">⛔ غير نشط</option>
    </select>
  </div>
  <div class="filter-group">
    <span class="filter-label" id="idx-fl-source">المصدر</span>
    <select id="f-source" class="form-control" onchange="loadCards()">
      <option value="" id="idx-opt-all3">الكل</option>
      <option value="regular" id="idx-opt-srcreg">🏦 عادي</option>
      <option value="cc"      id="idx-opt-srccc">📞 من CC</option>
    </select>
  </div>
  <div class="filter-group" style="flex:1;min-width:180px">
    <span class="filter-label" id="idx-fl-search">بحث</span>
    <input type="text" id="f-search" class="form-control" placeholder="رقم حساب / اسم بروكر / مسوّق..." oninput="debounceLoad()">
  </div>
  <div style="display:flex;flex-direction:column;gap:4px">
    <span class="filter-label">&nbsp;</span>
    <div style="display:flex;gap:6px">
      <button class="btn btn-ghost btn-sm" onclick="clearFilters()">✕</button>
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportExcel()">📗 Excel</button>
      <button class="btn btn-sm" style="background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.25);color:var(--re)" onclick="exportPdf()">📄 PDF</button>
    </div>
  </div>
</div>

<!-- Active column-level filters strip -->
<div class="active-filters-strip" id="active-cols-strip"></div>

<!-- Legend -->
<div class="legend">
  <strong style="color:var(--tx)" id="idx-legend-title">دليل الألوان:</strong>
  <span><span class="legend-dot" style="background:#7b68ee"></span> <span id="idx-legend-cc">كرت CC (مركز الاتصال)</span></span>
  <span><span class="legend-dot" style="background:#f5a623"></span> <span id="idx-legend-mod">معدّل</span></span>
  <span><span class="legend-dot" style="background:#22c97a"></span> <span id="idx-legend-new">مضاف جديد</span></span>
  <span><span class="legend-dot" style="background:#e04848"></span> <span id="idx-legend-flag">تجاوز 7$ / مكرّر</span></span>
  <span style="color:#e05050" id="idx-legend-high">عمولة &gt;5$</span>
  <span style="color:#f5a623" id="idx-legend-mid">عمولة 2-5$</span>
  <span style="color:#22c97a" id="idx-legend-low">عمولة ≤2$</span>
</div>

<!-- Table -->
<div class="panel" style="padding:0">
  <div class="panel-header" style="padding:12px 16px">
    <div class="panel-title" id="idx-panel-title">🗂 كروت العمولات <span id="cards-count" style="font-size:11px;color:var(--mu);font-weight:400;margin-right:8px"></span></div>
    <div id="cards-summary" style="font-size:11px;color:var(--mu)"></div>
  </div>
  <div class="table-scroll" style="max-height:62vh">
    <table class="data-table" id="cards-table">
      <thead>
        <tr>
          <th data-col="account_number">
            <div class="th-content">
              <span class="th-label" id="idx-th-ac">رقم الحساب</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('account_number', this)" title="فرز">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('account_number', this)" title="فلترة">▾</button>
              </div>
            </div>
          </th>
          <th data-col="month">
            <div class="th-content">
              <span class="th-label" id="idx-th-month">الشهر</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('month', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('month', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="branch">
            <div class="th-content">
              <span class="th-label" id="idx-th-branch">الفرع</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('branch', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('branch', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="broker">
            <div class="th-content">
              <span class="th-label" id="idx-th-broker">البروكر</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('broker', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('broker', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="broker_commission">
            <div class="th-content">
              <span class="th-label" id="idx-th-bcomm">ع. البروكر</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('broker_commission', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('broker_commission', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="marketer">
            <div class="th-content">
              <span class="th-label" id="idx-th-mktr">المسوّق</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('marketer', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('marketer', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="marketer_commission">
            <div class="th-content">
              <span class="th-label" id="idx-th-mcomm">ع. المسوّق</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('marketer_commission', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('marketer_commission', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="ext1">
            <div class="th-content">
              <span class="th-label" id="idx-th-ext1">خارجي 1</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('ext1', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('ext1', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="ext2">
            <div class="th-content">
              <span class="th-label" id="idx-th-ext2">خارجي 2</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('ext2', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('ext2', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="total_comm">
            <div class="th-content">
              <span class="th-label" id="idx-th-total">إجمالي ع.</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('total_comm', this)">↕</button>
              </div>
            </div>
          </th>
          <th data-col="initial_deposit">
            <div class="th-content">
              <span class="th-label" id="idx-th-dep">إيداع أولي</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('initial_deposit', this)">↕</button>
              </div>
            </div>
          </th>
          <th data-col="monthly_deposit">
            <div class="th-content">
              <span class="th-label" id="idx-th-mon">إيداع شهري</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('monthly_deposit', this)">↕</button>
              </div>
            </div>
          </th>
          <th data-col="account_kind">
            <div class="th-content">
              <span class="th-label" id="idx-th-kind">نوع</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('account_kind', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('account_kind', this)">▾</button>
              </div>
            </div>
          </th>
          <th data-col="status">
            <div class="th-content">
              <span class="th-label" id="idx-th-status">الحالة</span>
              <div class="th-ctrls">
                <button class="th-btn th-sort" onclick="colSort('status', this)">↕</button>
                <button class="th-btn th-filter" onclick="colFilterOpen('status', this)">▾</button>
              </div>
            </div>
          </th>
          <th><div class="th-content"><span class="th-label" id="idx-th-actions">إجراءات</span></div></th>
        </tr>
      </thead>
      <tbody id="cards-tbody">
        <tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)">
          <div style="font-size:32px;opacity:.3;margin-bottom:8px">⏳</div>جاري التحميل...
        </td></tr>
      </tbody>
      <tfoot id="cards-tfoot" style="display:none">
        <tr style="background:var(--bg2);font-weight:700">
          <td colspan="4" id="idx-tf-label" style="padding:8px 12px;font-size:11px;color:var(--mu)">المجاميع ↓</td>
          <td id="tf-bcomm" class="mono" style="font-size:11px"></td>
          <td></td>
          <td id="tf-mcomm" class="mono" style="font-size:11px"></td>
          <td colspan="2"></td>
          <td id="tf-total-comm" class="mono" style="color:var(--or);font-size:11px"></td>
          <td id="tf-dep" class="mono" style="color:var(--pri2);font-size:11px"></td>
          <td id="tf-mon" class="mono" style="color:var(--gr);font-size:11px"></td>
          <td colspan="3"></td>
        </tr>
      </tfoot>
    </table>
  </div>
  <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;align-items:center;justify-content:space-between">
    <span style="font-size:11px;color:var(--mu)" id="pagination-info"></span>
    <div style="display:flex;gap:6px" id="pagination-btns"></div>
  </div>
</div>

<!-- History Modal -->
<div id="history-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.55);z-index:999;overflow-y:auto">
  <div style="max-width:600px;margin:40px auto;background:var(--surface,var(--bg3));border-radius:16px;padding:24px;border:1px solid var(--brd1)">
    <div style="display:flex;justify-content:space-between;margin-bottom:16px">
      <h3 style="margin:0;font-size:15px" id="idx-hist-title">📋 سجل التعديلات</h3>
      <button onclick="document.getElementById('history-modal').style.display='none'" style="background:none;border:none;font-size:18px;cursor:pointer;color:var(--mu)">✕</button>
    </div>
    <div id="history-body"></div>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}
@endsection

@push('scripts')
<script>
// ══════════════════════════════════════════════════════════
// IDX — Cards Index Bilingual Dictionary
// ══════════════════════════════════════════════════════════
const IDX = {
  ar: {
    tbTitle:'كروت العمولات', btnNew:'➕ كرت جديد',
    kpiTotal:'إجمالي الكروت', kpiNew:'جديد NEW', kpiSub:'فرعي SUB',
    kpiMod:'معدّل', kpiCc:'من CC', kpiDep:'إجمالي الإيداع', kpiAvg:'متوسط العمولة',
    flMonth:'الشهر', allMonths:'كل الشهور', flBroker:'البروكر', allBrokers:'كل البروكرات',
    flKind:'نوع الحساب', all:'الكل', kindNew:'🟢 جديد NEW', kindSub:'🔵 فرعي SUB',
    flStatus:'الحالة', stActive:'✅ عادي', stMod:'✏️ معدّل', stNewAdded:'🆕 مضاف جديد', stInact:'⛔ غير نشط',
    flSource:'المصدر', srcReg:'🏦 عادي', srcCC:'📞 من CC',
    flSearch:'بحث', phSearch:'رقم حساب / اسم بروكر / مسوّق...',
    legendTitle:'دليل الألوان:', legendCC:'كرت CC (مركز الاتصال)', legendMod:'معدّل', legendNew:'مضاف جديد',
    legendFlag:'تجاوز 7$ / مكرّر',
    legendHigh:'عمولة >5$', legendMid:'عمولة 2-5$', legendLow:'عمولة ≤2$',
    revTitle:'كروت تحتاج مراجعة',
    revSub:'كروت تجاوزت الحد الأعلى (7$) أو حسابات مكرّرة من الاستيراد',
    revClear:'✕ عرض الكل',
    panelTitle:'🗂 كروت العمولات',
    thAc:'رقم الحساب', thMonth:'الشهر', thBranch:'الفرع', thBroker:'البروكر',
    thBComm:'ع. البروكر', thMktr:'المسوّق', thMComm:'ع. المسوّق',
    thExt1:'خارجي 1', thExt2:'خارجي 2', thTotal:'إجمالي ع.',
    thDep:'إيداع أولي', thMon:'إيداع شهري', thKind:'نوع', thStatus:'الحالة', thActions:'إجراءات',
    chipMod:'✏️ معدّل', chipNew:'🆕 جديد', chipActive:'✅ عادي', chipInact:'⛔ متوقف',
    loading:'جاري التحميل...', noResults:'لا توجد نتائج',
    avg:'متوسط', records:'سجل', tfLabel:'المجاميع ↓',
    histTitle:'📋 سجل التعديلات', histEmpty:'لا توجد تعديلات مسجّلة', histBy:'بواسطة',
    noData:'لا توجد بيانات للتصدير', excelDone:'تم تحميل Excel ✅', pdfDone:'تم تحميل PDF ✅',
    loadErr:'خطأ في تحميل البيانات',
    popSearch:'ابحث في القيم...', popApply:'تطبيق', popClear:'مسح', popSelAll:'الكل', popEmpty:'لا توجد قيم',
    sortedAsc:'↑ تصاعدي', sortedDesc:'↓ تنازلي',
    unassigned:'— بدون —',
    filterOn:'فلتر:',
  },
  en: {
    tbTitle:'Commission Cards', btnNew:'➕ New Card',
    kpiTotal:'Total Cards', kpiNew:'New', kpiSub:'Sub',
    kpiMod:'Modified', kpiCc:'From CC', kpiDep:'Total Deposit', kpiAvg:'Avg. Commission',
    flMonth:'Month', allMonths:'All Months', flBroker:'Broker', allBrokers:'All Brokers',
    flKind:'Account Kind', all:'All', kindNew:'🟢 New', kindSub:'🔵 Sub',
    flStatus:'Status', stActive:'✅ Active', stMod:'✏️ Modified', stNewAdded:'🆕 New Added', stInact:'⛔ Inactive',
    flSource:'Source', srcReg:'🏦 Regular', srcCC:'📞 From CC',
    flSearch:'Search', phSearch:'Account # / Broker / Marketer...',
    legendTitle:'Color Guide:', legendCC:'CC Card (Call Center)', legendMod:'Modified', legendNew:'New Added',
    legendFlag:'Over $7 / Duplicate',
    legendHigh:'Commission >$5', legendMid:'Commission $2-5', legendLow:'Commission ≤$2',
    revTitle:'Cards Needing Review',
    revSub:'Cards exceeding the $7 limit or duplicate accounts from import',
    revClear:'✕ Show All',
    panelTitle:'🗂 Commission Cards',
    thAc:'AC No.', thMonth:'Month', thBranch:'Branch', thBroker:'Broker',
    thBComm:'B.Comm', thMktr:'Marketer', thMComm:'M.Comm',
    thExt1:'External 1', thExt2:'External 2', thTotal:'Total Comm.',
    thDep:'Initial Dep.', thMon:'Monthly Dep.', thKind:'Kind', thStatus:'Status', thActions:'Actions',
    chipMod:'✏️ Modified', chipNew:'🆕 New Added', chipActive:'✅ Active', chipInact:'⛔ Inactive',
    loading:'Loading...', noResults:'No results found',
    avg:'avg', records:'records', tfLabel:'Totals ↓',
    histTitle:'📋 Modification History', histEmpty:'No modifications recorded', histBy:'by',
    noData:'No data to export', excelDone:'Excel downloaded ✅', pdfDone:'PDF downloaded ✅',
    loadErr:'Error loading data',
    popSearch:'Search values...', popApply:'Apply', popClear:'Clear', popSelAll:'Select All', popEmpty:'No values',
    sortedAsc:'↑ Asc', sortedDesc:'↓ Desc',
    unassigned:'— None —',
    filterOn:'Filter:',
  }
};
function idxL()   { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function idx(key) { const l=idxL(); return IDX[l]?.[key]??IDX.ar[key]??key; }

function idxApplyLang() {
  const map = {
    'idx-kpi-total':'kpiTotal','idx-kpi-new':'kpiNew','idx-kpi-sub':'kpiSub',
    'idx-kpi-mod':'kpiMod','idx-kpi-cc':'kpiCc','idx-kpi-dep':'kpiDep','idx-kpi-avg':'kpiAvg',
    'idx-fl-month':'flMonth','idx-fl-broker':'flBroker','idx-fl-kind':'flKind',
    'idx-fl-status':'flStatus','idx-fl-source':'flSource','idx-fl-search':'flSearch',
    'idx-legend-title':'legendTitle','idx-legend-cc':'legendCC','idx-legend-mod':'legendMod',
    'idx-legend-new':'legendNew','idx-legend-flag':'legendFlag',
    'idx-legend-high':'legendHigh','idx-legend-mid':'legendMid','idx-legend-low':'legendLow',
    'idx-rev-title':'revTitle','idx-rev-sub':'revSub','idx-rev-clear':'revClear',
    'idx-th-ac':'thAc','idx-th-month':'thMonth','idx-th-branch':'thBranch','idx-th-broker':'thBroker',
    'idx-th-bcomm':'thBComm','idx-th-mktr':'thMktr','idx-th-mcomm':'thMComm',
    'idx-th-ext1':'thExt1','idx-th-ext2':'thExt2','idx-th-total':'thTotal',
    'idx-th-dep':'thDep','idx-th-mon':'thMon','idx-th-kind':'thKind','idx-th-status':'thStatus','idx-th-actions':'thActions',
    'idx-tf-label':'tfLabel','idx-hist-title':'histTitle',
  };
  Object.entries(map).forEach(([id,key])=>{ const el=document.getElementById(id); if(el) el.textContent=idx(key); });

  const optMap = {
    'idx-opt-allmonths':'allMonths','idx-opt-allbrokers':'allBrokers',
    'idx-opt-all1':'all','idx-opt-kindnew':'kindNew','idx-opt-kindsub':'kindSub',
    'idx-opt-all2':'all','idx-opt-stactive':'stActive','idx-opt-stmod':'stMod',
    'idx-opt-stnew':'stNewAdded','idx-opt-stinact':'stInact',
    'idx-opt-all3':'all','idx-opt-srcreg':'srcReg','idx-opt-srccc':'srcCC',
  };
  Object.entries(optMap).forEach(([id,key])=>{ const el=document.getElementById(id); if(el) el.textContent=idx(key); });

  const searchEl=document.getElementById('f-search');
  if(searchEl) searchEl.placeholder=idx('phSearch');

  const pt=document.getElementById('idx-panel-title');
  if(pt){ const cs=document.getElementById('cards-count'); pt.textContent=idx('panelTitle')+' '; if(cs) pt.appendChild(cs); }

  const tb=document.querySelector('.tb-title'); if(tb) tb.textContent=idx('tbTitle');

  if(filteredCards.length>0) renderTable(filteredCards);
  if(filteredCards.length>0) updateKpis(filteredCards);
  renderActiveFiltersStrip();
}

const _idxOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if(_idxOrigApplyLang) _idxOrigApplyLang(lang);
  idxApplyLang();
};

let allCards=[], filteredCards=[], viewCards=[];
// Column-level sort + filter state
let _colSortCol = null;
let _colSortDir = null;  // 'asc' | 'desc' | null
let _colFilters = {};    // { colKey: Set<string value> }

function getReviewMode(){
  try { return new URLSearchParams(location.search).get('filter') === 'needs_review'; }
  catch(e){ return false; }
}

function commColor(val){ const v=parseFloat(val)||0; return v>5?'comm-high':v>2?'comm-mid':'comm-low'; }
function commFmt(val){
  const v=parseFloat(val)||0;
  if(v===0) return '<span style="color:var(--mu);font-size:10px">—</span>';
  return `<span class="mono ${commColor(v)}">$${v}/lot</span>`;
}

async function loadFilterOptions(){
  const r=await api('GET','/cards?per_page=500');
  if(!r.success) return;
  const cards=r.data?.data||[];
  const months=[...new Set(cards.map(c=>c.month))].sort().reverse();
  const brokers=[...new Set(cards.map(c=>c.broker?.name).filter(Boolean))].sort();
  const mSel=document.getElementById('f-month');
  months.forEach(m=>{const o=document.createElement('option');o.value=o.textContent=m;mSel.appendChild(o);});
  const bSel=document.getElementById('f-broker');
  brokers.forEach(b=>{const o=document.createElement('option');o.value=o.textContent=b;bSel.appendChild(o);});
}

async function loadCards(){
  const reviewMode = getReviewMode();
  document.getElementById('cards-tbody').innerHTML=`<tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:28px;opacity:.3;margin-bottom:8px">⏳</div>${idx('loading')}</td></tr>`;

  if (reviewMode) {
    const rp = new URLSearchParams();
    const s = document.getElementById('f-search').value; if (s) rp.set('search', s);
    rp.set('per_page', 500);
    const rr = await api('GET','/cards/needs-review?'+rp);
    if (!rr.success) { toast(idx('loadErr'),'error'); return; }
    allCards = rr.data?.data || [];
    filteredCards = allCards;
    const banner = document.getElementById('review-banner');
    const counts = document.getElementById('review-banner-counts');
    if (banner) {
      banner.style.display = 'flex';
      const sum = rr.summary || {};
      counts.innerHTML =
        `<span class="chip chip-overlimit">⚠ ${idxL()==='en'?'Over $7':'تجاوز 7$'}: ${sum.over_limit_count||0}</span>`+
        `<span class="chip chip-duplicate">🔁 ${idxL()==='en'?'Duplicate':'مكرّر'}: ${sum.duplicate_count||0}</span>`;
    }
    applyColSortFilterAndRender();
    return;
  }

  const banner = document.getElementById('review-banner');
  if (banner) banner.style.display = 'none';

  const params=new URLSearchParams();
  const month=document.getElementById('f-month').value;
  const broker=document.getElementById('f-broker').value;
  const kind=document.getElementById('f-kind').value;
  const status=document.getElementById('f-status').value;
  const search=document.getElementById('f-search').value;
  const source=document.getElementById('f-source').value;
  if(month)  params.set('month',month);
  if(kind)   params.set('kind',kind);
  if(status) params.set('status',status);
  if(search) params.set('search',search);
  if(source) params.set('cc_source',source);
  params.set('per_page',300);

  const r=await api('GET','/cards?'+params);
  if(!r.success){ toast(idx('loadErr'),'error'); return; }
  allCards=r.data?.data||[];
  filteredCards=source==='cc'?allCards.filter(c=>!!c.cc_branch_id):source==='regular'?allCards.filter(c=>!c.cc_branch_id):allCards;

  window._cardsPager = {
    current: r.data?.current_page || 1,
    last:    r.data?.last_page || 1,
    total:   r.data?.total || filteredCards.length,
    per:     r.data?.per_page || 300,
  };
  _lastLoadParams = params.toString();

  applyColSortFilterAndRender();
  renderLoadMore();
}

let _lastLoadParams = '';
async function loadMoreCards() {
  if (!window._cardsPager) return;
  if (window._cardsPager.current >= window._cardsPager.last) return;
  const btn = document.getElementById('cards-loadmore-btn');
  if (btn) { btn.disabled = true; btn.textContent = idx('loading'); }
  const nextPage = window._cardsPager.current + 1;
  const r = await api('GET', '/cards?' + _lastLoadParams + '&page=' + nextPage);
  if (!r.success) { if (btn) btn.disabled = false; toast(idx('loadErr'),'error'); return; }
  const newItems = r.data?.data || [];
  const source = document.getElementById('f-source')?.value || '';
  const filteredNew = source==='cc' ? newItems.filter(c=>!!c.cc_branch_id)
                     : source==='regular' ? newItems.filter(c=>!c.cc_branch_id)
                     : newItems;
  allCards = allCards.concat(newItems);
  filteredCards = filteredCards.concat(filteredNew);
  window._cardsPager.current = r.data?.current_page || nextPage;
  applyColSortFilterAndRender();
  renderLoadMore();
}
function renderLoadMore() {
  const info = document.getElementById('pagination-info');
  const btns = document.getElementById('pagination-btns');
  const pg = window._cardsPager; if (!pg || !info || !btns) return;
  const curL = idxL();
  if (pg.last > 1) {
    info.textContent = (curL==='en'?'Showing ':'عرض ') + viewCards.length +
                       (curL==='en'?' of ':' من ') + pg.total.toLocaleString();
    btns.innerHTML = pg.current < pg.last
      ? `<button id="cards-loadmore-btn" class="btn btn-sm" style="background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.3);color:#1AADBA" onclick="loadMoreCards()">${curL==='en'?'Load more':'تحميل المزيد'} (${pg.current}/${pg.last})</button>`
      : `<span style="font-size:11px;color:var(--gr)">${curL==='en'?'All loaded':'تم تحميل الكل'}</span>`;
  } else {
    info.textContent = viewCards.length + ' ' + idx('records');
    btns.innerHTML = '';
  }
}
(function initCardsInfinite(){
  const el = document.querySelector('.table-scroll');
  if (!el) return;
  el.addEventListener('scroll', () => {
    if (el.scrollTop + el.clientHeight >= el.scrollHeight - 80) {
      if (window._cardsPager && window._cardsPager.current < window._cardsPager.last) loadMoreCards();
    }
  });
})();

/* ══════════════════════════════════════════════════════════
   COLUMN SORT + FILTER (per-column dropdown)
   ══════════════════════════════════════════════════════════ */

// Pull a comparable value from a card for a given column key
function colValueFor(c, col) {
  const L = idxL();
  switch (col) {
    case 'account_number':   return c.account_number ?? '';
    case 'month':            return c.month ?? '';
    case 'branch':           return (L==='en' ? c.branch?.name_en : c.branch?.name_ar) || c.branch?.name_ar || c.branch?.name_en || '';
    case 'broker':           return c.broker?.name || '';
    case 'broker_commission':return Number(c.broker_commission) || 0;
    case 'marketer':         return c.marketer?.name || '';
    case 'marketer_commission':return Number(c.marketer_commission) || 0;
    case 'ext1':             return c.ext_marketer1?.name || '';
    case 'ext2':             return c.ext_marketer2?.name || '';
    case 'total_comm':       return (parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
    case 'initial_deposit':  return Number(c.initial_deposit) || 0;
    case 'monthly_deposit':  return Number(c.monthly_deposit) || 0;
    case 'account_kind':     return c.account_kind || '';
    case 'status':           return c.status || '';
    default: return '';
  }
}
function colIsNumeric(col) {
  return ['broker_commission','marketer_commission','total_comm','initial_deposit','monthly_deposit','account_number'].includes(col);
}
function colDisplayValue(col, raw) {
  if (raw === '' || raw === null || raw === undefined) return idx('unassigned');
  if (col === 'account_kind') return raw === 'new' ? '🟢 NEW' : raw === 'sub' ? '🔵 SUB' : raw;
  if (col === 'status') return ({modified:idx('chipMod'),new_added:idx('chipNew'),active:idx('chipActive'),inactive:idx('chipInact')}[raw] || raw);
  if (colIsNumeric(col)) return (typeof raw === 'number' ? raw.toLocaleString('en', {maximumFractionDigits: 2}) : String(raw));
  return String(raw);
}

function colSort(col, btn) {
  // Cycle: null/other → asc → desc → off
  if (_colSortCol !== col) {
    _colSortCol = col; _colSortDir = 'asc';
  } else if (_colSortDir === 'asc') {
    _colSortDir = 'desc';
  } else {
    _colSortCol = null; _colSortDir = null;
  }
  // Reset all sort buttons visual
  document.querySelectorAll('.th-sort').forEach(b => { b.textContent = '↕'; b.classList.remove('active'); });
  if (_colSortCol === col && _colSortDir) {
    btn.textContent = _colSortDir === 'asc' ? '↑' : '↓';
    btn.classList.add('active');
  }
  applyColSortFilterAndRender();
}

function colFilterOpen(col, btn) {
  // Close any other open popovers
  document.querySelectorAll('.th-pop.open').forEach(p => p.classList.remove('open'));

  const th = btn.closest('th');
  if (!th) return;
  let pop = th.querySelector('.th-pop');
  if (!pop) {
    pop = document.createElement('div');
    pop.className = 'th-pop';
    th.appendChild(pop);
  }
  // Build value counts from filteredCards (not already-column-filtered — more useful)
  // Use allCards base so counts show what's available
  const base = filteredCards;
  const counts = new Map();
  base.forEach(c => {
    const v = colValueFor(c, col);
    const key = (v === null || v === undefined || v === '') ? '' : String(v);
    counts.set(key, (counts.get(key) || 0) + 1);
  });
  const sel = _colFilters[col] || new Set();
  const keys = [...counts.keys()].sort((a, b) => {
    if (colIsNumeric(col)) return (parseFloat(a)||0) - (parseFloat(b)||0);
    return String(a).localeCompare(String(b));
  });

  pop.innerHTML = `
    <div class="th-pop-head">
      <input type="text" placeholder="${idx('popSearch')}" oninput="colPopSearch(this,'${col}')">
      <button class="th-pop-close" onclick="colFilterClose(event,this)">✕</button>
    </div>
    <div class="th-pop-list" id="pop-list-${col}">
      ${keys.length ? keys.map(k => {
        const checked = sel.has(k) ? 'checked' : '';
        const label = colDisplayValue(col, colIsNumeric(col) ? parseFloat(k) : k);
        return `<label class="th-pop-opt">
          <input type="checkbox" value="${encodeURIComponent(k)}" ${checked}>
          <span>${esc(label)}</span>
          <span class="opt-cnt">${counts.get(k)}</span>
        </label>`;
      }).join('') : `<div class="th-pop-empty">${idx('popEmpty')}</div>`}
    </div>
    <div class="th-pop-foot">
      <button onclick="colFilterSelectAll(event,'${col}')">${idx('popSelAll')}</button>
      <button onclick="colFilterClear('${col}')">${idx('popClear')}</button>
      <button class="b-apply" onclick="colFilterApply(event,'${col}')">${idx('popApply')}</button>
    </div>
  `;
  pop.classList.add('open');

  // Position fix for RTL — ensure popover stays within viewport
  requestAnimationFrame(() => {
    const r = pop.getBoundingClientRect();
    const vw = document.documentElement.clientWidth;
    if (r.right > vw - 8) {
      pop.style.insetInlineStart = 'auto';
      pop.style.insetInlineEnd = '0';
    }
  });
}

function colPopSearch(input, col) {
  const q = (input.value || '').toLowerCase().trim();
  const list = document.getElementById('pop-list-' + col);
  if (!list) return;
  list.querySelectorAll('.th-pop-opt').forEach(opt => {
    const txt = opt.textContent.toLowerCase();
    opt.style.display = (!q || txt.includes(q)) ? '' : 'none';
  });
}
function colFilterSelectAll(ev, col) {
  ev.stopPropagation();
  const list = document.getElementById('pop-list-' + col);
  if (!list) return;
  const visible = [...list.querySelectorAll('.th-pop-opt')].filter(o => o.style.display !== 'none');
  const allChecked = visible.every(o => o.querySelector('input').checked);
  visible.forEach(o => { o.querySelector('input').checked = !allChecked; });
}
function colFilterClear(col) {
  delete _colFilters[col];
  // Close popover
  document.querySelectorAll('.th-pop.open').forEach(p => p.classList.remove('open'));
  // Reset the filter button indicator
  const btn = document.querySelector(`th[data-col="${col}"] .th-filter`);
  if (btn) btn.classList.remove('filtered');
  applyColSortFilterAndRender();
}
function colFilterApply(ev, col) {
  ev.stopPropagation();
  const list = document.getElementById('pop-list-' + col);
  if (!list) return;
  const checked = [...list.querySelectorAll('input:checked')].map(i => decodeURIComponent(i.value));
  if (checked.length) {
    _colFilters[col] = new Set(checked);
    const btn = document.querySelector(`th[data-col="${col}"] .th-filter`);
    if (btn) btn.classList.add('filtered');
  } else {
    delete _colFilters[col];
    const btn = document.querySelector(`th[data-col="${col}"] .th-filter`);
    if (btn) btn.classList.remove('filtered');
  }
  document.querySelectorAll('.th-pop.open').forEach(p => p.classList.remove('open'));
  applyColSortFilterAndRender();
}
function colFilterClose(ev, btn) {
  ev.stopPropagation();
  const pop = btn.closest('.th-pop'); if (pop) pop.classList.remove('open');
}

// Close popover on outside click / Esc
document.addEventListener('click', (e) => {
  if (e.target.closest('.th-pop') || e.target.closest('.th-filter')) return;
  document.querySelectorAll('.th-pop.open').forEach(p => p.classList.remove('open'));
});
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') document.querySelectorAll('.th-pop.open').forEach(p => p.classList.remove('open'));
});

function applyColSortFilterAndRender() {
  let out = filteredCards.slice();
  // Apply column filters
  for (const [col, set] of Object.entries(_colFilters)) {
    if (!set || !set.size) continue;
    out = out.filter(c => {
      const v = colValueFor(c, col);
      const key = (v === null || v === undefined || v === '') ? '' : String(v);
      return set.has(key);
    });
  }
  // Apply sort
  if (_colSortCol && _colSortDir) {
    const dir = _colSortDir === 'asc' ? 1 : -1;
    const numeric = colIsNumeric(_colSortCol);
    out.sort((a, b) => {
      const va = colValueFor(a, _colSortCol);
      const vb = colValueFor(b, _colSortCol);
      if (numeric) return (va - vb) * dir;
      return String(va).localeCompare(String(vb)) * dir;
    });
  }
  viewCards = out;
  updateKpis(viewCards);
  renderTable(viewCards);
  renderActiveFiltersStrip();
  renderLoadMore();
}

function renderActiveFiltersStrip() {
  const strip = document.getElementById('active-cols-strip');
  if (!strip) return;
  const chips = [];
  if (_colSortCol && _colSortDir) {
    const sortLabel = _colSortDir === 'asc' ? idx('sortedAsc') : idx('sortedDesc');
    chips.push(`<span class="active-filter-chip" style="background:rgba(26,173,186,.1);border-color:rgba(26,173,186,.3);color:var(--pri2)">
      <span>${colHeaderLabel(_colSortCol)}: ${sortLabel}</span>
      <button onclick="clearColSort()">✕</button>
    </span>`);
  }
  Object.entries(_colFilters).forEach(([col, set]) => {
    const n = set.size;
    chips.push(`<span class="active-filter-chip" style="background:rgba(245,166,35,.1);border-color:rgba(245,166,35,.3);color:#f5a623">
      <span>${idx('filterOn')} ${colHeaderLabel(col)} (${n})</span>
      <button onclick="colFilterClear('${col}')">✕</button>
    </span>`);
  });
  strip.innerHTML = chips.length ? chips.join('') : '';
}
function colHeaderLabel(col) {
  const map = { account_number:'thAc', month:'thMonth', branch:'thBranch', broker:'thBroker',
    broker_commission:'thBComm', marketer:'thMktr', marketer_commission:'thMComm',
    ext1:'thExt1', ext2:'thExt2', total_comm:'thTotal',
    initial_deposit:'thDep', monthly_deposit:'thMon',
    account_kind:'thKind', status:'thStatus' };
  return idx(map[col] || col);
}
function clearColSort() {
  _colSortCol = null; _colSortDir = null;
  document.querySelectorAll('.th-sort').forEach(b => { b.textContent = '↕'; b.classList.remove('active'); });
  applyColSortFilterAndRender();
}

function updateKpis(cards){
  const total=cards.length;
  const newAcc=cards.filter(c=>c.account_kind==='new').length;
  const subAcc=cards.filter(c=>c.account_kind==='sub').length;
  const modAcc=cards.filter(c=>c.status==='modified').length;
  const ccAcc=cards.filter(c=>!!c.cc_branch_id).length;
  const dep=cards.reduce((s,c)=>s+parseFloat(c.initial_deposit||0),0);
  const avgComm=total?(cards.reduce((s,c)=>s+parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0),0)/total):0;
  document.getElementById('kpi-total').textContent=total.toLocaleString();
  document.getElementById('kpi-new').textContent=newAcc.toLocaleString();
  document.getElementById('kpi-sub').textContent=subAcc.toLocaleString();
  document.getElementById('kpi-modified').textContent=modAcc.toLocaleString();
  document.getElementById('kpi-cc').textContent=ccAcc.toLocaleString();
  document.getElementById('kpi-dep').textContent=fmtK(dep);
  document.getElementById('kpi-avgcomm').textContent='$'+avgComm.toFixed(1);
  document.getElementById('cards-count').textContent=total+' '+idx('records');
}

function renderTable(cards){
  const tbody=document.getElementById('cards-tbody');
  const tfoot=document.getElementById('cards-tfoot');
  if(!cards.length){
    tbody.innerHTML=`<tr><td colspan="15" style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:36px;opacity:.2;margin-bottom:8px">📭</div>${idx('noResults')}</td></tr>`;
    tfoot.style.display='none'; return;
  }
  const statusChip=s=>({
    modified: `<span class="chip chip-modified">${idx('chipMod')}</span>`,
    new_added:`<span class="chip chip-new">${idx('chipNew')}</span>`,
    active:   `<span class="chip chip-active">${idx('chipActive')}</span>`,
    inactive: `<span class="chip chip-inactive">${idx('chipInact')}</span>`,
  }[s]||`<span class="chip chip-active">${s}</span>`);

  tbody.innerHTML=cards.map(c=>{
    const isCc=!!c.cc_branch_id;
    const isOverLimit = !!c.is_over_limit;
    const isDuplicate = !!c.is_duplicate;
    const isFlagged = isOverLimit || isDuplicate;
    const totalComm=(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
    const rowClass = isFlagged ? 'row-flagged'
      : (isCc ? 'row-cc-card'
      : (c.status==='modified' ? 'row-modified'
      : (c.status==='new_added' ? 'row-new_added'
      : (c.status==='inactive' ? 'row-inactive' : ''))));
    const flagChips =
      (isOverLimit?`<span class="chip chip-overlimit" style="margin-left:4px;font-size:9px" title="تجاوز حد 7$">⚠ &gt;7$</span>`:'') +
      (isDuplicate?`<span class="chip chip-duplicate" style="margin-left:4px;font-size:9px" title="حساب مكرّر في الاستيراد">🔁</span>`:'');
    const ext1=c.ext_marketer1?.name?`<span style="color:var(--pu);font-size:11px">${esc(c.ext_marketer1.name)}</span><br><span class="mono ${commColor(c.ext_commission1)}" style="font-size:10px">$${c.ext_commission1}/lot</span>`:'<span style="color:var(--mu);font-size:10px">—</span>';
    const ext2=c.ext_marketer2?.name?`<span style="color:var(--pu);font-size:11px">${esc(c.ext_marketer2.name)}</span><br><span class="mono ${commColor(c.ext_commission2)}" style="font-size:10px">$${c.ext_commission2}/lot</span>`:'<span style="color:var(--mu);font-size:10px">—</span>';
    const branchName=idxL()==='en'?(c.branch?.name_en||c.branch?.name_ar||'—'):(c.branch?.name_ar||'—');
    return `<tr class="${rowClass}">
      <td>${isCc?'<span class="chip chip-cc" style="margin-left:4px;font-size:9px">📞 CC</span>':''}${flagChips}<span class="ac-num" style="font-size:13px;font-weight:800">#${esc(String(c.account_number))}</span></td>
      <td style="color:var(--mu);font-size:11px;white-space:nowrap">${esc(c.month)}</td>
      <td style="font-size:11px;color:var(--mu)">${esc(branchName)}</td>
      <td style="font-weight:700;color:var(--pri2)">${esc(c.broker?.name||'—')}</td>
      <td>${commFmt(c.broker_commission)}</td>
      <td style="color:var(--m2);font-size:11px">${c.marketer?.name&&c.marketer.name!==c.broker?.name?esc(c.marketer.name):'<span style="color:var(--mu)">—</span>'}</td>
      <td>${commFmt(c.marketer_commission)}</td>
      <td style="font-size:11px">${ext1}</td>
      <td style="font-size:11px">${ext2}</td>
      <td><span class="mono ${isOverLimit?'comm-high':commColor(totalComm)}" style="font-weight:${isOverLimit?900:700}${isOverLimit?';text-decoration:underline':''}" title="${isOverLimit?'تجاوز الحد 7$':''}">$${totalComm.toFixed(1)}</span></td>
      <td class="mono" style="color:var(--pri2);font-weight:600">${fmt(c.initial_deposit)}</td>
      <td class="mono" style="color:var(--gr)">${fmt(c.monthly_deposit)}</td>
      <td><span class="chip ${c.account_kind==='new'?'chip-new-acc':'chip-sub-acc'}">${c.account_kind==='new'?'🟢 NEW':'🔵 SUB'}</span></td>
      <td>${statusChip(c.status)}</td>
      <td><a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm" style="padding:4px 8px">✏️</a>${c.status==='modified'?`<button class="btn btn-sm" style="background:rgba(245,166,35,.1);color:var(--or);border:1px solid rgba(245,166,35,.25);padding:4px 8px" onclick="viewHistory(${c.id})">📋</button>`:''}${isFlagged?`<button class="btn btn-sm" style="background:rgba(224,72,72,.1);color:#e04848;border:1px solid rgba(224,72,72,.25);padding:4px 8px;margin-inline-start:4px" onclick="clearReviewFlag(${c.id})" title="تحرير علامة المراجعة">✓ حل</button>`:''}</td>
    </tr>`;
  }).join('');

  const totalBComm=cards.reduce((s,c)=>s+(parseFloat(c.broker_commission)||0),0);
  const totalMComm=cards.reduce((s,c)=>s+(parseFloat(c.marketer_commission)||0),0);
  const totalAll=cards.reduce((s,c)=>s+(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0),0);
  const totalDep=cards.reduce((s,c)=>s+(parseFloat(c.initial_deposit)||0),0);
  const totalMon=cards.reduce((s,c)=>s+(parseFloat(c.monthly_deposit)||0),0);
  const avgLbl=idx('avg');
  document.getElementById('tf-bcomm').innerHTML=`$${(totalBComm/cards.length).toFixed(1)} ${avgLbl}`;
  document.getElementById('tf-mcomm').innerHTML=`$${(totalMComm/cards.length).toFixed(1)} ${avgLbl}`;
  document.getElementById('tf-total-comm').innerHTML=`$${(totalAll/cards.length).toFixed(1)} ${avgLbl}`;
  document.getElementById('tf-dep').innerHTML=fmtK(totalDep);
  document.getElementById('tf-mon').innerHTML=fmtK(totalMon);
  tfoot.style.display='';
}

function clearFilters(){
  ['f-month','f-broker','f-kind','f-status','f-source'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('f-search').value='';
  // Also clear per-column
  _colFilters = {};
  _colSortCol = null; _colSortDir = null;
  document.querySelectorAll('.th-filter').forEach(b => b.classList.remove('filtered'));
  document.querySelectorAll('.th-sort').forEach(b => { b.textContent = '↕'; b.classList.remove('active'); });
  loadCards();
}
let debounceTimer;
function debounceLoad(){ clearTimeout(debounceTimer); debounceTimer=setTimeout(loadCards,380); }

async function viewHistory(id){
  const r=await api('GET',`/cards/${id}`);
  if(!r.success) return;
  const mods=r.data?.modifications||[];
  document.getElementById('idx-hist-title').textContent=idx('histTitle');
  const body=document.getElementById('history-body');
  if(!mods.length){ body.innerHTML=`<p style="color:var(--mu);text-align:center">${idx('histEmpty')}</p>`; }
  else{ body.innerHTML=mods.map(m=>`<div style="border:1px solid var(--brd1);border-radius:10px;padding:12px;margin-bottom:8px">
    <div style="font-size:11px;color:var(--mu);margin-bottom:6px">${esc(m.modified_at||'')} — ${idx('histBy')}: ${esc(m.modified_by?.name||'—')}</div>
    <div style="font-size:12px">${Object.entries(m.changes||{}).map(([k,v])=>`<span style="background:var(--bg2);border-radius:6px;padding:2px 8px;margin:2px;display:inline-block;font-size:11px"><strong>${esc(k)}:</strong> <del style="color:var(--re)">${esc(String(v.old??'—'))}</del> → <span style="color:var(--gr)">${esc(String(v.new??'—'))}</span></span>`).join('')}</div>
  </div>`).join(''); }
  document.getElementById('history-modal').style.display='block';
}

function exportExcel(){
  if(!viewCards.length){ toast(idx('noData'),'error'); return; }
  const isEn=idxL()==='en';
  const hdr=isEn?['AC No.','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Ext 1','Ext 1C','Ext 2','Ext 2C','Total Comm.','Initial Dep.','Monthly Dep.','Kind','Status','Source']:['رقم الحساب','الشهر','الفرع','البروكر','ع.البروكر','المسوّق','ع.المسوّق','خارجي 1','ع.خ1','خارجي 2','ع.خ2','إجمالي ع.','إيداع أولي','إيداع شهري','نوع','الحالة','المصدر'];
  const rows=[hdr,...viewCards.map(c=>{
    const tot=(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
    return [c.account_number,c.month,isEn?(c.branch?.name_en||c.branch?.name_ar||''):(c.branch?.name_ar||''),c.broker?.name||'','$'+c.broker_commission+'/lot',c.marketer?.name||'—','$'+c.marketer_commission+'/lot',c.ext_marketer1?.name||'—','$'+(c.ext_commission1||0)+'/lot',c.ext_marketer2?.name||'—','$'+(c.ext_commission2||0)+'/lot','$'+tot.toFixed(2)+'/lot',c.initial_deposit,c.monthly_deposit,c.account_kind==='new'?'NEW':'SUB',c.status,c.cc_branch_id?'CC':(isEn?'Regular':'عادي')];
  })];
  const wb=XLSX.utils.book_new();
  const ws=XLSX.utils.aoa_to_sheet(rows);
  ws['!cols']=hdr.map(()=>({wch:14}));
  XLSX.utils.book_append_sheet(wb,ws,isEn?'Commission Cards':'كروت العمولات');
  XLSX.writeFile(wb,'WafraGulf_Cards_'+new Date().toISOString().slice(0,10)+'.xlsx');
  toast(idx('excelDone'),'success');
}

function exportPdf(){
  if(!viewCards.length){ toast(idx('noData'),'error'); return; }
  const {jsPDF}=window.jspdf;
  const doc=new jsPDF({orientation:'landscape',unit:'mm',format:'a3'});
  doc.setFontSize(14);doc.setTextColor(46,134,171);
  doc.text('Wafra Gulf — Commission Cards',210,14,{align:'center'});
  doc.setFontSize(9);doc.setTextColor(100,120,140);
  doc.text('Export: '+new Date().toLocaleDateString(),14,14);
  doc.autoTable({
    startY:22,
    head:[['AC No.','Month','Branch','Broker','B.Comm','Marketer','M.Comm','Ext1','Ext2','Total','Initial','Monthly','Kind','Status','Src']],
    body:viewCards.map(c=>{
      const tot=(parseFloat(c.broker_commission)||0)+(parseFloat(c.marketer_commission)||0)+(parseFloat(c.ext_commission1)||0)+(parseFloat(c.ext_commission2)||0);
      return [c.account_number,c.month,c.branch?.name_ar||'',c.broker?.name||'—','$'+c.broker_commission,c.marketer?.name||'—','$'+c.marketer_commission,c.ext_marketer1?.name||'—',c.ext_marketer2?.name||'—','$'+tot.toFixed(1),fmt(c.initial_deposit),fmt(c.monthly_deposit),c.account_kind?.toUpperCase(),c.status,c.cc_branch_id?'CC':'-'];
    }),
    styles:{fontSize:7,cellPadding:1.5},
    headStyles:{fillColor:[46,134,171],textColor:255,fontStyle:'bold'},
    alternateRowStyles:{fillColor:[245,248,252]},
  });
  doc.save('WafraGulf_Cards_'+new Date().toISOString().slice(0,10)+'.pdf');
  toast(idx('pdfDone'),'success');
}

idxApplyLang();
loadFilterOptions();
loadCards();

// ── Account Number Quick Search ────────────────────────────
function acSearch() {
  const inp = document.getElementById('ac-search-input');
  const val = (inp?.value || '').trim();
  if (!val) { toast('أدخل رقم الحساب', 'error'); return; }

  const mainSearch = document.getElementById('f-search');
  if (mainSearch) mainSearch.value = val;

  const monthSel = document.getElementById('f-month');
  const brokerSel = document.getElementById('f-broker');
  const kindSel = document.getElementById('f-kind');
  const statusSel = document.getElementById('f-status');
  const sourceSel = document.getElementById('f-source');
  if (monthSel) monthSel.value = '';
  if (brokerSel) brokerSel.value = '';
  if (kindSel) kindSel.value = '';
  if (statusSel) statusSel.value = '';
  if (sourceSel) sourceSel.value = '';

  loadCards().then(() => {
    const clearBtn = document.getElementById('idx-acsearch-clear');
    const lbl = document.getElementById('ac-search-result-lbl');
    if (clearBtn) clearBtn.style.display = '';
    if (lbl) {
      const cnt = viewCards.length;
      lbl.textContent = cnt > 0
        ? (cnt + ' كرت ' + (cnt > 1 ? 'مسجّل' : 'مسجّل') + ' لهذا الحساب')
        : 'لا توجد نتائج لهذا الرقم';
      lbl.style.color = cnt > 0 ? 'var(--gr)' : 'var(--re)';
    }
  });
}

function acSearchClear() {
  const inp = document.getElementById('ac-search-input');
  const clearBtn = document.getElementById('idx-acsearch-clear');
  const lbl = document.getElementById('ac-search-result-lbl');
  if (inp) inp.value = '';
  if (clearBtn) clearBtn.style.display = 'none';
  if (lbl) lbl.textContent = '';
  clearFilters();
}

async function clearReviewFlag(id) {
  const msg = idxL()==='en'
    ? 'Mark this card as reviewed and clear its flags?'
    : 'هل تم مراجعة هذا الكرت؟ سيتم إزالة علامة المراجعة.';
  if (!confirm(msg)) return;
  const r = await api('POST', `/cards/${id}/clear-review`, { flag: 'both' });
  if (!r?.success) {
    toast(r?.message || (idxL()==='en' ? 'Failed to clear flag' : 'تعذر إزالة العلامة'), 'error');
    return;
  }
  toast(idxL()==='en' ? '✓ Flag cleared' : '✓ تمت المراجعة', 'success');
  loadCards();
}
</script>
@endpush
