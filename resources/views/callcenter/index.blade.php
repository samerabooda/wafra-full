@extends('layouts.app')
@section('title','Call Center')
@section('page-title','Call Center')

@section('topbar-actions')
<button class="tb-btn primary" id="cc-topbar-new-btn" onclick="openCcNew()">➕ كرت جديد</button>
@endsection

@push('styles')
<style>
.cc-chip{display:inline-block;padding:3px 10px;border-radius:12px;font-size:11px;font-weight:700;white-space:nowrap}
.cc-chip.cc_pending    {background:#fff3cd;color:#856404}
.cc-chip.branch_pending{background:#cff4fc;color:#055160}
.cc-chip.accepted      {background:#d1e7dd;color:#0a5c36}
.cc-chip.completed     {background:#d1fae5;color:#065f46}
.cc-chip.rejected      {background:#f8d7da;color:#842029}
.cc-row td{background:linear-gradient(90deg,rgba(123,104,238,.06),transparent 70%)!important}
.cc-row:hover td{background:linear-gradient(90deg,rgba(123,104,238,.12),rgba(123,104,238,.04) 70%)!important}
.limit-hint{font-size:12px;color:#7b68ee;background:rgba(123,104,238,.07);border:1px solid rgba(123,104,238,.2);border-radius:8px;padding:8px 12px;margin-bottom:12px}
.cc-status-chips{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.cc-sf{padding:6px 14px;border-radius:20px;border:1px solid var(--brd2);background:none;cursor:pointer;font-size:12px;font-weight:600;color:var(--mu);font-family:'Tajawal',sans-serif;transition:all .18s}
.cc-sf:hover,.cc-sf.on{background:rgba(123,104,238,.15);border-color:#7b68ee;color:#a898ff}
.cc-sf.on{font-weight:700}
.cc-kpi-grid{display:grid;grid-template-columns:repeat(6,1fr);gap:10px;margin-bottom:18px}
@media(max-width:900px){.cc-kpi-grid{grid-template-columns:repeat(3,1fr)}}
@media(max-width:550px){.cc-kpi-grid{grid-template-columns:repeat(2,1fr)}}
.cc-kpi{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;padding:14px;text-align:center;position:relative;overflow:hidden}
.cc-kpi-lbl{font-size:10px;color:var(--mu);margin-bottom:6px;font-weight:600}
.cc-kpi-val{font-size:1.6rem;font-weight:900;line-height:1;font-family:'JetBrains Mono',monospace}
.cc-kpi-sub{font-size:10px;color:var(--mu);margin-top:4px}
.cc-kpi::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px}
.cc-kpi-total::after{background:linear-gradient(90deg,transparent,#7b68ee,transparent)}
.cc-kpi-draft::after{background:linear-gradient(90deg,transparent,var(--or),transparent)}
.cc-kpi-sent::after{background:linear-gradient(90deg,transparent,var(--pri2),transparent)}
.cc-kpi-acc::after{background:linear-gradient(90deg,transparent,var(--gr),transparent)}
.cc-kpi-comp::after{background:linear-gradient(90deg,transparent,var(--pri),transparent)}
.cc-kpi-rej::after{background:linear-gradient(90deg,transparent,var(--re),transparent)}
.cc-chart-grid{display:grid;grid-template-columns:300px 1fr;gap:16px;margin-bottom:18px}
@media(max-width:768px){.cc-chart-grid{grid-template-columns:1fr}}
.monthly-table-wrap{overflow-x:auto}

/* ── Distinctive CC cards table (compact single-line rows) ── */
.cc-cards-scroll{overflow-x:auto}
.cc-cards-table{min-width:920px;border-collapse:separate;border-spacing:0}
.cc-cards-table th{position:sticky;top:0;z-index:2;white-space:nowrap;background:var(--bg2);
  font-size:9.5px;color:var(--mu);text-transform:uppercase;letter-spacing:.2px;font-weight:800;
  padding:6px 8px;border-bottom:2px solid var(--brd1);text-align:start;line-height:1.2}
.cc-cards-table td{white-space:nowrap;padding:5px 8px;border-bottom:1px solid var(--brd1);
  font-size:11.5px;vertical-align:middle;line-height:1.3}
.cc-cards-table tbody tr:nth-child(even){background:rgba(127,127,127,.04)}
.cc-cards-table tbody tr:hover{background:rgba(26,173,186,.08)}
.cc-th-c,.cc-td-c{text-align:center}
.cc-id{font-family:monospace;font-weight:800;font-size:10px;color:var(--pri2);
  background:var(--bg3);border:1px solid var(--brd1);border-radius:5px;padding:1px 6px}
.cc-acc{font-size:12px;color:var(--tx)}
.cc-person{font-size:11px}.cc-person.mgr{color:#7b68ee;font-weight:700}
.cc-branch{font-size:11px;font-weight:600}
.cc-comm{display:inline-block;font-family:monospace;font-weight:700;font-size:11px;color:var(--gr);
  background:rgba(34,201,122,.12);border:1px solid rgba(34,201,122,.22);border-radius:7px;padding:1px 7px}
.cc-dep{font-family:monospace;font-weight:700;font-size:11.5px;color:var(--pri2)}
.cc-cards-table .badge,.cc-cards-table .btn-sm{font-size:10px!important}
.cc-cards-table .btn-sm{padding:3px 7px!important}
/* CC month/year picker (same idea as the dashboard calendar) */
.nc-cal{display:none;position:absolute;z-index:5000;margin-top:5px;width:288px;max-width:92vw;background:var(--bg2,#fff);border:1px solid var(--brd2);border-radius:12px;padding:12px;box-shadow:0 16px 44px rgba(0,0,0,.32)}
.nc-cal.open{display:block}
.nc-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;font-weight:900;color:var(--tx);font-size:15px}
.nc-cal-nav button{border:1px solid var(--brd2);background:var(--bg3);border-radius:8px;width:32px;height:32px;cursor:pointer;color:var(--tx);font-weight:900;font-size:14px}
.nc-cal-nav button:hover{border-color:var(--pri);color:var(--pri2)}
.nc-cal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:7px}
.nc-cal-m{padding:10px 0;border-radius:9px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);font-weight:700;font-size:12.5px;cursor:pointer;transition:all .12s}
.nc-cal-m:hover{border-color:var(--pri);color:var(--pri2)}
.nc-cal-m.on{background:linear-gradient(135deg,#1AADBA,#0E7A88);color:#fff;border-color:transparent}

/* ── Excel-style per-column sort + filter (mirror of /cards/list) ── */
.cc-cards-table thead th.kh{cursor:pointer;user-select:none;position:relative}
.cc-cards-table thead th.kh:hover{background:rgba(123,104,238,.08)}
.cc-cards-table thead th.kh.srt{color:#a898ff}
.cc-cards-table thead th.kh.flt{background:rgba(123,104,238,.12)}
.cc-cards-table thead th.kh .ar{display:inline-block;font-size:9px;margin:0 3px;color:#a898ff}
.cc-cards-table thead th.kh .kh-fn{display:inline-block;font-size:10px;opacity:.6;margin-inline-start:3px}
.cc-cards-table thead th.kh.flt .kh-fn{opacity:1;color:#a898ff}
.k-fmenu{display:none;position:fixed;width:260px;max-height:420px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:11px;box-shadow:0 14px 42px rgba(0,0,0,.42);padding:9px;z-index:9000;overflow:hidden;font-family:'Tajawal',sans-serif}
.kfm-srt{display:flex;gap:6px;margin-bottom:7px}
.kfm-srt button{flex:1;padding:5px 7px;border-radius:7px;border:1px solid var(--brd2);background:var(--bg3);color:var(--tx);font-size:11px;font-weight:700;cursor:pointer}
.kfm-srt button:hover{border-color:#7b68ee;color:#a898ff}
.kfm-srch{width:100%;margin-bottom:7px;padding:6px 9px;font-size:12px}
.kfm-it{display:flex;align-items:center;gap:7px;padding:4px 6px;border-radius:6px;cursor:pointer;font-size:11.5px}
.kfm-it:hover{background:rgba(123,104,238,.08)}
.kfm-it input{margin:0;flex-shrink:0}
.kfm-it span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.kfm-all{border-bottom:1px solid var(--brd1);padding-bottom:7px;margin-bottom:4px;font-weight:800}
.kfm-list{max-height:220px;overflow-y:auto;margin-bottom:7px;padding-inline-end:3px}
.kfm-list::-webkit-scrollbar{width:6px}
.kfm-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:3px}
.kfm-note{color:var(--mu);font-size:10px;padding:5px;text-align:center}
.kfm-act{display:flex;gap:6px;padding-top:7px;border-top:1px solid var(--brd1)}
.kfm-ok,.kfm-cl{flex:1;padding:7px;border-radius:7px;border:0;font-weight:800;font-size:11.5px;cursor:pointer}
.kfm-ok{background:linear-gradient(135deg,#7b68ee,#5f4bc4);color:#fff}
.kfm-ok:hover{filter:brightness(1.08)}
.kfm-cl{background:var(--bg3);color:var(--mu);border:1px solid var(--brd2)}
.kfm-cl:hover{color:var(--re);border-color:rgba(224,80,80,.3)}
.k-spin{display:inline-block;width:16px;height:16px;border:2px solid var(--brd2);border-top-color:#7b68ee;border-radius:50%;animation:kspin .8s linear infinite}
@keyframes kspin{to{transform:rotate(360deg)}}
.cc-clear-flt{padding:5px 12px;border-radius:16px;border:1px solid rgba(224,80,80,.3);background:none;color:var(--re);font-size:11px;font-weight:700;cursor:pointer;font-family:'Tajawal',sans-serif;display:none;margin-inline-start:8px}
.cc-clear-flt.on{display:inline-block}
.cc-clear-flt:hover{background:rgba(224,80,80,.1)}
</style>
@endpush

@section('content')

<div id="cc-shell" style="display:flex;flex-direction:column;gap:0;min-height:calc(100vh - 120px);
  background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- ── CC Top Nav (horizontal) ── --}}
  <div id="cc-nav" style="width:100%;flex-shrink:0;background:var(--bg2);
    border-bottom:1px solid var(--brd1);display:flex;flex-direction:row;flex-wrap:wrap;
    align-items:center;gap:5px;padding:8px 12px;">

    <div style="padding:0 6px 0 10px;border-left:1px solid var(--brd1);flex-shrink:0">
      <div id="ccnav-hdr" style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px;white-space:nowrap">
        📞 مركز الاتصال
      </div>
    </div>

    @foreach([
      ['accounts', '📋'],
      ['reports',  '📊'],
      ['modified', '✏️'],
      ['monthly',  '📅'],
    ] as [$sid, $ico])
    <button onclick="ccShowSection('{{ $sid }}')" id="ccnav-{{ $sid }}" style="
      display:flex;align-items:center;gap:7px;padding:7px 11px;margin:0;
      border-radius:8px;background:none;border:1px solid transparent;cursor:pointer;
      text-align:right;width:auto;flex-shrink:0;white-space:nowrap;font-family:'Tajawal',sans-serif;transition:all .18s;">
      <div style="width:26px;height:26px;border-radius:7px;flex-shrink:0;
        background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);
        display:flex;align-items:center;justify-content:center;font-size:14px;"
        id="ccnav-ico-{{ $sid }}">{{ $ico }}</div>
      <div style="min-width:0">
        <div style="font-size:12.5px;font-weight:700;color:var(--tx);white-space:nowrap"
          id="ccnav-lbl-{{ $sid }}"></div>
        <div style="display:none" id="ccnav-sub-{{ $sid }}"></div>
      </div>
      <span style="display:none;font-size:9px;padding:2px 7px;border-radius:10px;
        background:rgba(245,166,35,.2);color:var(--or);font-weight:700;border:1px solid rgba(245,166,35,.3)"
        id="ccnav-badge-{{ $sid }}">0</span>
    </button>
    @endforeach
    <div id="ccnav-footer" style="display:none"></div>
  </div>

  {{-- ── Content Panel ── --}}
  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

    {{-- Global alerts --}}
    <div id="cc-alert-err" class="alert alert-error" style="margin-bottom:12px"></div>
    <div id="cc-alert-ok"  class="alert alert-success" style="margin-bottom:12px"></div>

    {{-- ══ SECTION: ACCOUNTS ══ --}}
    <div id="ccsec-accounts" class="cc-section">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:18px">
        <div>
          <h2 id="ccs-acc-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
          <p  id="ccs-acc-p"  style="font-size:12px;color:var(--mu)"></p>
        </div>
        <button class="btn btn-primary" id="ccs-acc-new" onclick="openModal('modal-cc-create')"></button>
      </div>

      <div class="cc-status-chips" id="cc-filter-chips"></div>
      <button class="cc-clear-flt" id="cc-clear-filters" onclick="cckClearAll()">↺ <span id="cc-clear-l">مسح الفرز والتصفية</span></button>

      <div class="panel">
        <div class="panel-header">
          <div class="panel-title" id="ccs-acc-tbl-title"></div>
          <button class="btn btn-ghost btn-sm" onclick="ccLoadAll()">🔄</button>
        </div>
        <div class="table-scroll cc-cards-scroll">
          <table class="data-table cc-cards-table" id="cc-cards-table">
            <thead id="cc-thead"></thead>
            <tbody id="cc-tbody">
              <tr><td colspan="11" style="text-align:center;padding:40px;color:var(--mu)" id="cc-loading-cell"></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══ SECTION: REPORTS ══ --}}
    <div id="ccsec-reports" class="cc-section" style="display:none">
      <div style="margin-bottom:18px">
        <h2 id="ccs-rpt-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
        <p  id="ccs-rpt-p"  style="font-size:12px;color:var(--mu)"></p>
      </div>

      {{-- KPI Grid --}}
      <div class="cc-kpi-grid">
        <div class="cc-kpi cc-kpi-total">
          <div class="cc-kpi-lbl" id="kpi-lbl-total"></div>
          <div class="cc-kpi-val" style="color:#a898ff" id="kpi-val-total">—</div>
        </div>
        <div class="cc-kpi cc-kpi-draft">
          <div class="cc-kpi-lbl" id="kpi-lbl-draft"></div>
          <div class="cc-kpi-val" style="color:var(--or)" id="kpi-val-draft">—</div>
        </div>
        <div class="cc-kpi cc-kpi-sent">
          <div class="cc-kpi-lbl" id="kpi-lbl-sent"></div>
          <div class="cc-kpi-val" style="color:var(--pri2)" id="kpi-val-sent">—</div>
        </div>
        <div class="cc-kpi cc-kpi-acc">
          <div class="cc-kpi-lbl" id="kpi-lbl-acc"></div>
          <div class="cc-kpi-val" style="color:var(--gr)" id="kpi-val-acc">—</div>
        </div>
        <div class="cc-kpi cc-kpi-comp">
          <div class="cc-kpi-lbl" id="kpi-lbl-comp"></div>
          <div class="cc-kpi-val" style="color:var(--pri)" id="kpi-val-comp">—</div>
        </div>
        <div class="cc-kpi cc-kpi-rej">
          <div class="cc-kpi-lbl" id="kpi-lbl-rej"></div>
          <div class="cc-kpi-val" style="color:var(--re)" id="kpi-val-rej">—</div>
        </div>
      </div>

      {{-- Chart + By Branch --}}
      <div class="cc-chart-grid">
        <div class="panel">
          <div class="panel-header"><div class="panel-title" id="rpt-chart-title"></div></div>
          <div style="padding:12px;height:260px;display:flex;align-items:center;justify-content:center">
            <canvas id="cc-status-chart"></canvas>
          </div>
        </div>
        <div class="panel">
          <div class="panel-header"><div class="panel-title" id="rpt-branch-title"></div></div>
          <div class="table-scroll" style="max-height:290px">
            <table class="data-table">
              <thead><tr>
                <th id="rpt-bth-branch"></th><th id="rpt-bth-total"></th>
                <th id="rpt-bth-acc"></th><th id="rpt-bth-comp"></th><th id="rpt-bth-rej"></th>
              </tr></thead>
              <tbody id="rpt-branch-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>

      {{-- By Agent --}}
      <div class="panel">
        <div class="panel-header"><div class="panel-title" id="rpt-agent-title"></div></div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="rpt-ath-agent"></th><th id="rpt-ath-total"></th>
              <th id="rpt-ath-comp"></th><th id="rpt-ath-rej"></th><th id="rpt-ath-rate"></th>
            </tr></thead>
            <tbody id="rpt-agent-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══ SECTION: MODIFIED ══ --}}
    <div id="ccsec-modified" class="cc-section" style="display:none">
      <div style="margin-bottom:18px">
        <h2 id="ccs-mod-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
        <p  id="ccs-mod-p"  style="font-size:12px;color:var(--mu)"></p>
      </div>
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title" id="ccs-mod-title"></div>
          <span class="badge badge-orange" id="ccs-mod-count" style="display:none">0</span>
        </div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="mod-th-acnum"></th><th id="mod-th-month"></th>
              <th id="mod-th-branch"></th><th id="mod-th-agent"></th>
              <th id="mod-th-broker"></th><th id="mod-th-deposit"></th>
              <th id="mod-th-status"></th>
            </tr></thead>
            <tbody id="mod-tbody">
              <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)" id="mod-loading-cell"></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- ══ SECTION: MONTHLY ══ --}}
    <div id="ccsec-monthly" class="cc-section" style="display:none">
      <div style="margin-bottom:18px">
        <h2 id="ccs-mon-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
        <p  id="ccs-mon-p"  style="font-size:12px;color:var(--mu)"></p>
      </div>

      {{-- Year filter --}}
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <label style="font-size:12px;color:var(--mu);font-weight:600" id="mon-year-lbl"></label>
        <select id="mon-year-sel" class="form-control" style="width:120px" onchange="ccRenderMonthly()"></select>
      </div>

      {{-- Chart --}}
      <div class="panel" style="margin-bottom:16px">
        <div class="panel-header"><div class="panel-title" id="mon-chart-title"></div></div>
        <div style="padding:14px;height:280px">
          <canvas id="cc-monthly-chart"></canvas>
        </div>
      </div>

      {{-- Monthly breakdown table --}}
      <div class="panel">
        <div class="panel-header"><div class="panel-title" id="mon-tbl-title"></div></div>
        <div class="monthly-table-wrap">
          <table class="data-table">
            <thead><tr>
              <th id="mon-th-month"></th><th id="mon-th-total"></th>
              <th id="mon-th-draft"></th><th id="mon-th-sent"></th>
              <th id="mon-th-acc"></th><th id="mon-th-comp"></th><th id="mon-th-rej"></th>
            </tr></thead>
            <tbody id="mon-tbody"></tbody>
          </table>
        </div>
      </div>
    </div>

  </div>{{-- content --}}
</div>{{-- shell --}}

{{-- ══ Create CC Card Modal ══ --}}
<div class="modal-overlay" id="modal-cc-create">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title" id="cc-modal-create-title">📞 كرت CC جديد</div>
      <button class="modal-close" onclick="closeModal('modal-cc-create')">✕</button>
    </div>
    <div class="modal-body">
      <div id="nc-err" class="alert alert-error"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-ac"></label>
          <input type="text" id="nc-ac" class="form-control" placeholder="اختياري — 6 أرقام" inputmode="numeric" maxlength="6" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)">
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-kind"></label>
          <select id="nc-kind" class="form-control" onchange="ncKindChange()">
            <option value="new">New — جديد</option>
            <option value="sub">Sub — فرعي</option>
          </select>
        </div>
      </div>
      <div class="form-row" id="nc-parent-row" style="display:none">
        <div class="form-group">
          <label class="form-label">🔗 رقم الحساب الأساسي / Parent (Main) Account * <span style="font-size:10px;color:var(--mu)">(6 أرقام)</span></label>
          <input type="text" id="nc-parent" class="form-control" inputmode="numeric" maxlength="6" placeholder="رقم الحساب الأساسي" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,6)">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group" style="position:relative">
          <label class="form-label" id="nc-lbl-month"></label>
          <input type="hidden" id="nc-month">
          <button type="button" id="nc-month-btn" class="form-control" style="text-align:start;cursor:pointer" onclick="ncCalToggle(event)">—</button>
          <div id="nc-cal" class="nc-cal"></div>
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-branch"></label>
          <select id="nc-branch" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-agent"></label>
          <select id="nc-agent" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label">👤 عمولة موظف مركز الاتصال / CC Agent Commission</label>
          <input type="number" id="nc-agent-comm" class="form-control" value="0" min="0" step="0.5">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-acctype"></label>
          <select id="nc-acc-type" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-trading"></label>
          <select id="nc-trading" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-phone">📱 رقم هاتف العميل * <span style="font-size:10px;color:#dc3545">(الحقل الأساسي)</span></label>
          <div style="display:flex;gap:6px">
            <select id="nc-cc" class="form-control" dir="ltr" style="flex:0 0 165px;max-width:165px"><option value="+965">🇰🇼 +965</option></select>
            <input type="tel" id="nc-phone" class="form-control" dir="ltr" placeholder="50001234" inputmode="numeric" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
          </div>
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-source">📡 كيف تم التعرّف على العميل؟ / Client Source</label>
          <select id="nc-source" class="form-control" onchange="ncSourceChange()">
            <option value="">— اختر / Select —</option>
            <option value="إعلان">📢 إعلان / Advertisement</option>
            <option value="توصية">🤝 توصية عميل أو صديق / Referral</option>
            <option value="اتصال مباشر">📞 اتصال مباشر / Direct call</option>
            <option value="سوشيال ميديا">🌐 سوشيال ميديا / Social media</option>
            <option value="واتساب">💬 واتساب / WhatsApp</option>
            <option value="معرض أو فعالية">🎪 معرض أو فعالية / Event</option>
            <option value="أخرى">➕ أخرى / Other</option>
          </select>
        </div>
      </div>
      <div class="form-row" id="nc-source-other-row" style="display:none">
        <div class="form-group">
          <label class="form-label">✏️ اذكر طريقة التعرّف على العميل / Specify the source</label>
          <input type="text" id="nc-source-other" class="form-control" placeholder="اكتب المصدر">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-dep">💰 الإيداع المتوقع لفتح الحساب / Expected Deposit</label>
          <input type="number" id="nc-dep" class="form-control" value="0" min="0" step="100">
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-mgrcomm">👩‍💼 عمولة مديرة مركز الاتصال / CC Manager Commission</label>
          <input type="number" id="nc-mgr-comm" class="form-control" value="0" min="0" step="0.5">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" id="nc-lbl-notes"></label>
        <input type="text" id="nc-notes" class="form-control" placeholder="">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" id="nc-cancel-btn" onclick="closeModal('modal-cc-create')"></button>
      <button class="btn btn-primary" id="nc-save-btn" onclick="createCard()"></button>
    </div>
  </div>
</div>

{{-- Delete confirmation — requires the manager's own password --}}
<div class="modal-overlay" id="modal-cc-delete">
  <div class="modal">
    <div class="modal-header">
      <div class="modal-title">🗑 حذف الكرت / Delete Card</div>
      <button class="modal-close" onclick="closeModal('modal-cc-delete')">✕</button>
    </div>
    <div class="modal-body">
      <div id="cc-del-err" class="alert alert-error"></div>
      <p style="font-size:13px;color:var(--mu);line-height:1.8;margin-bottom:12px">
        ⚠️ لتأكيد حذف الكرت نهائياً، أدخل <strong>كلمة مرور حسابك</strong>.<br>
        To permanently delete this card, enter <strong>your account password</strong>.
      </p>
      <input type="password" id="cc-del-pass" class="form-control" dir="ltr"
             placeholder="كلمة المرور / Password" autocomplete="current-password"
             onkeydown="if(event.key==='Enter')ccConfirmDelete()">
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost" onclick="closeModal('modal-cc-delete')">إلغاء / Cancel</button>
      <button class="btn" style="background:var(--re);color:#fff;border-color:var(--re)" onclick="ccConfirmDelete()">🗑 حذف نهائي / Delete</button>
    </div>
  </div>
</div>

@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════
   CC HUB — Bilingual Controller
   ══════════════════════════════════════════════════════════════ */

const CCT = {
  ar: {
    navHdr:'📞 مركز الاتصال',
    footer:'⚡ حد عمولات CC:\nبروكر + مسوّق ≤ 5$',
    s_accounts_lbl:'حسابات CC',          s_accounts_sub:'كل كروت مركز الاتصال',
    s_reports_lbl:'تقارير CC',           s_reports_sub:'تحليل الحالات والفروع',
    s_modified_lbl:'الحسابات المعدّلة',  s_modified_sub:'كروت CC المُعدَّلة',
    s_monthly_lbl:'إحصائيات شهرية',      s_monthly_sub:'عدد الحسابات بالشهر',
    // Accounts
    acc_h2:'📋 حسابات مركز الاتصال',
    acc_p:'جميع كروت CC — أنشئها وأرسلها للفروع وتابع الردود',
    acc_newBtn:'➕ كرت جديد',
    acc_tbl_title:'قائمة كروت CC',
    sf_all:'الكل', sf_draft:'📝 مسودة', sf_sent:'📩 أُرسل للفرع',
    sf_acc:'✅ مقبول', sf_comp:'🏁 مكتمل', sf_rej:'❌ مرفوض',
    th_id:'رقم الكرت', th_acnum:'رقم الحساب', th_phone:'📱 هاتف العميل', th_month:'الشهر',
    th_mgr:'مديرة CC', th_mgr_c:'عمولة المديرة', th_agent:'موظف CC', th_agent_c:'عمولة الموظف',
    th_branch:'الفرع المستهدف', th_dep:'الإيداع المتوقع',
    th_type:'النوع', th_status:'الحالة', th_act:'الإجراء',
    kfm_asc:'تصاعدي', kfm_desc:'تنازلي', kfm_srch:'بحث…', kfm_all:'تحديد الكل',
    kfm_apply:'تطبيق', kfm_clear:'مسح', kfm_blank:'(فارغ)', kfm_na:'(—)',
    clear_flt:'↺ مسح الفرز والتصفية',
    loading:'جاري التحميل...', empty:'لا توجد كروت',
    btn_send:'📤 إرسال للفرع', btn_resend:'🔁 إعادة إرسال', btn_send_now:'📤 إرسال الآن للفرع', btn_send_later:'لاحقًا',
    status_wait_branch:'⏳ بانتظار الفرع', status_completing:'🔄 يستكمل البيانات',
    type_new:'🆕 جديد', type_sub:'🔀 فرعي',
    confirm_send:'إرسال هذا الكرت للفرع؟', confirm_resend:'إعادة إرسال هذا الكرت للفرع؟',
    reason_lbl:'سبب:',
    // Reports
    rpt_h2:'📊 تقارير مركز الاتصال',
    rpt_p:'تحليل توزيع الكروت حسب الحالة والفروع والموظفين',
    kpi_total:'إجمالي الكروت', kpi_draft:'مسودة', kpi_sent:'مُرسل للفرع',
    kpi_acc:'مقبول', kpi_comp:'مكتمل', kpi_rej:'مرفوض',
    chart_title:'📊 توزيع الحالات',
    br_title:'🏢 تفصيل الفروع المستهدفة',
    bth_branch:'الفرع', bth_total:'الكل', bth_acc:'مقبول', bth_comp:'مكتمل', bth_rej:'مرفوض',
    ag_title:'👤 تفصيل موظفي CC',
    ath_agent:'الموظف', ath_total:'الكل', ath_comp:'مكتمل', ath_rej:'مرفوض', ath_rate:'معدل الإنجاز',
    empty_rpt:'لا توجد بيانات بعد',
    // Modified
    mod_h2:'✏️ الحسابات المعدّلة من CC',
    mod_p:'كروت CC التي تم تعديلها بعد اكتمالها بالفرع',
    mod_title:'الكروت المعدّلة',
    mod_th_acnum:'رقم الحساب', mod_th_month:'الشهر', mod_th_branch:'الفرع',
    mod_th_agent:'موظف CC', mod_th_broker:'البروكر/عمولة', mod_th_deposit:'الإيداع',
    mod_th_status:'الحالة', mod_empty:'لا توجد حسابات معدّلة من CC',
    // Monthly
    mon_h2:'📅 إحصائيات شهرية — CC',
    mon_p:'عدد الحسابات التي أنشأها مركز الاتصال شهرياً',
    year_lbl:'السنة:',
    mon_chart_title:'📅 توزيع الكروت الشهري',
    mon_tbl_title:'جدول التفاصيل الشهرية',
    mon_th_month:'الشهر', mon_th_total:'الإجمالي',
    mon_th_draft:'مسودة', mon_th_sent:'مُرسل', mon_th_acc:'مقبول',
    mon_th_comp:'مكتمل', mon_th_rej:'مرفوض', mon_empty:'لا توجد بيانات',
    // Modal
    modal_title:'📞 كرت CC جديد',
    limit_hint:'⚠️ تنبيه: عمولة البروكر + عمولة المسوّق يجب ألا تتجاوز 5$ عند إتمام الكرت من الفرع.',
    nc_ac:'رقم الحساب', nc_kind:'نوع الحساب', nc_month:'الشهر *',
    nc_branch:'الفرع المستهدف *', nc_agent:'موظف CC *',
    nc_acctype:'نوع الحساب (تصنيف)', nc_accstatus:'حالة الحساب',
    nc_trading:'نوع التداول', nc_notes:'ملاحظات',
    nc_ph_notes:'معلومات إضافية للفرع...',
    nc_cancel:'إلغاء', nc_save:'💾 حفظ كمسودة',
    nc_err_required:'يرجى ملء الحقول المطلوبة (الشهر، الفرع، الموظف)',
    nc_err_ac_or_phone:'أدخل رقم الحساب أو رقم هاتف العميل (أحدهما على الأقل)',
    topbar_btn:'➕ كرت جديد',
    opt_optional:'— اختياري —', opt_branch:'— اختر الفرع —', opt_agent:'— اختر الموظف —',
  },
  en: {
    navHdr:'📞 Call Center',
    footer:'⚡ CC Commission Limit:\nBroker + Marketer ≤ $5',
    s_accounts_lbl:'CC Accounts',         s_accounts_sub:'All call center cards',
    s_reports_lbl:'CC Reports',           s_reports_sub:'Status & branch analysis',
    s_modified_lbl:'Modified Accounts',   s_modified_sub:'Modified CC cards',
    s_monthly_lbl:'Monthly Stats',         s_monthly_sub:'Monthly account counts',
    acc_h2:'📋 Call Center Accounts',
    acc_p:'All CC cards — create, send to branches and track responses',
    acc_newBtn:'➕ New Card',
    acc_tbl_title:'CC Cards List',
    sf_all:'All', sf_draft:'📝 Draft', sf_sent:'📩 Sent to Branch',
    sf_acc:'✅ Accepted', sf_comp:'🏁 Completed', sf_rej:'❌ Rejected',
    th_id:'Card #', th_acnum:'Account #', th_phone:'📱 Client Phone', th_month:'Month',
    th_mgr:'CC Manager', th_mgr_c:'Mgr Comm.', th_agent:'CC Agent', th_agent_c:'Agent Comm.',
    th_branch:'Target Branch', th_dep:'Expected Deposit',
    th_type:'Type', th_status:'Status', th_act:'Action',
    kfm_asc:'Ascending', kfm_desc:'Descending', kfm_srch:'Search…', kfm_all:'Select all',
    kfm_apply:'Apply', kfm_clear:'Clear', kfm_blank:'(blank)', kfm_na:'(—)',
    clear_flt:'↺ Clear sort & filters',
    loading:'Loading...', empty:'No cards found',
    btn_send:'📤 Send to Branch', btn_resend:'🔁 Resend', btn_send_now:'📤 Send to Branch now', btn_send_later:'Later',
    status_wait_branch:'⏳ Awaiting Branch', status_completing:'🔄 Branch completing',
    type_new:'🆕 New', type_sub:'🔀 Sub',
    confirm_send:'Send this card to the branch?', confirm_resend:'Resend this card to the branch?',
    reason_lbl:'Reason:',
    rpt_h2:'📊 Call Center Reports',
    rpt_p:'Status distribution analysis by branch and CC agent',
    kpi_total:'Total Cards', kpi_draft:'Draft', kpi_sent:'Sent to Branch',
    kpi_acc:'Accepted', kpi_comp:'Completed', kpi_rej:'Rejected',
    chart_title:'📊 Status Distribution',
    br_title:'🏢 Target Branches Breakdown',
    bth_branch:'Branch', bth_total:'Total', bth_acc:'Accepted', bth_comp:'Completed', bth_rej:'Rejected',
    ag_title:'👤 CC Agents Breakdown',
    ath_agent:'Agent', ath_total:'Total', ath_comp:'Completed', ath_rej:'Rejected', ath_rate:'Completion Rate',
    empty_rpt:'No data yet',
    mod_h2:'✏️ Modified CC Accounts',
    mod_p:'CC cards that were modified after branch completion',
    mod_title:'Modified Cards',
    mod_th_acnum:'Account #', mod_th_month:'Month', mod_th_branch:'Branch',
    mod_th_agent:'CC Agent', mod_th_broker:'Broker/Comm.', mod_th_deposit:'Deposit',
    mod_th_status:'Status', mod_empty:'No modified CC accounts found',
    mon_h2:'📅 Monthly CC Statistics',
    mon_p:'Monthly count of accounts created by the call center',
    year_lbl:'Year:',
    mon_chart_title:'📅 Monthly Card Distribution',
    mon_tbl_title:'Monthly Breakdown Table',
    mon_th_month:'Month', mon_th_total:'Total',
    mon_th_draft:'Draft', mon_th_sent:'Sent', mon_th_acc:'Accepted',
    mon_th_comp:'Completed', mon_th_rej:'Rejected', mon_empty:'No data',
    modal_title:'📞 New CC Card',
    limit_hint:'⚠️ Note: Broker + Marketer commission must not exceed $5 when the branch completes the card.',
    nc_ac:'Account Number', nc_kind:'Account Type', nc_month:'Month *',
    nc_branch:'Target Branch *', nc_agent:'CC Agent *',
    nc_acctype:'Account Type (classification)', nc_accstatus:'Account Status',
    nc_trading:'Trading Type', nc_notes:'Notes',
    nc_ph_notes:'Additional information for the branch...',
    nc_cancel:'Cancel', nc_save:'💾 Save as Draft',
    nc_err_required:'Please fill in the required fields (month, branch, agent)',
    nc_err_ac_or_phone:'Enter an account number or a client phone (at least one)',
    topbar_btn:'➕ New Card',
    opt_optional:'— Optional —', opt_branch:'— Select Branch —', opt_agent:'— Select Agent —',
  }
};

function ccL()    { return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
function cc(key)  { return CCT[ccL()]?.[key] ?? CCT.ar[key] ?? key; }
function _t(id,v) { const e=document.getElementById(id); if(e&&v!==undefined) e.textContent=v; }

/* ─── Apply language ─────────────────────────────────────── */
function ccApplyLang() {
  const L = ccL();
  _t('ccnav-hdr', CCT[L].navHdr);
  _t('ccnav-footer', ''); // will be set with innerHTML
  const fn = document.getElementById('ccnav-footer');
  if (fn) fn.innerHTML = CCT[L].footer.replace('\n','<br>');

  ['accounts','reports','modified','monthly'].forEach(id => {
    _t('ccnav-lbl-' + id, CCT[L]['s_' + id + '_lbl'] || '');
    _t('ccnav-sub-' + id, CCT[L]['s_' + id + '_sub'] || '');
  });

  // Accounts section
  _t('ccs-acc-h2', cc('acc_h2')); _t('ccs-acc-p', cc('acc_p'));
  _t('ccs-acc-new', cc('acc_newBtn')); _t('ccs-acc-tbl-title', cc('acc_tbl_title'));
  if (typeof ccRenderAccounts === 'function' && document.getElementById('cc-thead')) ccRenderAccounts();

  // Reports section
  _t('ccs-rpt-h2', cc('rpt_h2')); _t('ccs-rpt-p', cc('rpt_p'));
  _t('kpi-lbl-total', cc('kpi_total')); _t('kpi-lbl-draft', cc('kpi_draft'));
  _t('kpi-lbl-sent', cc('kpi_sent')); _t('kpi-lbl-acc', cc('kpi_acc'));
  _t('kpi-lbl-comp', cc('kpi_comp')); _t('kpi-lbl-rej', cc('kpi_rej'));
  _t('rpt-chart-title', cc('chart_title')); _t('rpt-branch-title', cc('br_title'));
  _t('rpt-bth-branch', cc('bth_branch')); _t('rpt-bth-total', cc('bth_total'));
  _t('rpt-bth-acc', cc('bth_acc')); _t('rpt-bth-comp', cc('bth_comp'));
  _t('rpt-bth-rej', cc('bth_rej')); _t('rpt-agent-title', cc('ag_title'));
  _t('rpt-ath-agent', cc('ath_agent')); _t('rpt-ath-total', cc('ath_total'));
  _t('rpt-ath-comp', cc('ath_comp')); _t('rpt-ath-rej', cc('ath_rej'));
  _t('rpt-ath-rate', cc('ath_rate'));

  // Modified section
  _t('ccs-mod-h2', cc('mod_h2')); _t('ccs-mod-p', cc('mod_p'));
  _t('ccs-mod-title', cc('mod_title'));
  _t('mod-th-acnum', cc('mod_th_acnum')); _t('mod-th-month', cc('mod_th_month'));
  _t('mod-th-branch', cc('mod_th_branch')); _t('mod-th-agent', cc('mod_th_agent'));
  _t('mod-th-broker', cc('mod_th_broker')); _t('mod-th-deposit', cc('mod_th_deposit'));
  _t('mod-th-status', cc('mod_th_status'));

  // Monthly section
  _t('ccs-mon-h2', cc('mon_h2')); _t('ccs-mon-p', cc('mon_p'));
  _t('mon-year-lbl', cc('year_lbl'));
  _t('mon-chart-title', cc('mon_chart_title'));
  _t('mon-tbl-title', cc('mon_tbl_title'));
  _t('mon-th-month', cc('mon_th_month')); _t('mon-th-total', cc('mon_th_total'));
  _t('mon-th-draft', cc('mon_th_draft')); _t('mon-th-sent', cc('mon_th_sent'));
  _t('mon-th-acc', cc('mon_th_acc')); _t('mon-th-comp', cc('mon_th_comp'));
  _t('mon-th-rej', cc('mon_th_rej'));

  // Modal
  _t('cc-modal-create-title', cc('modal_title'));
  _t('nc-lbl-ac', cc('nc_ac')); _t('nc-lbl-kind', cc('nc_kind'));
  _t('nc-lbl-month', cc('nc_month')); _t('nc-lbl-branch', cc('nc_branch'));
  _t('nc-lbl-agent', cc('nc_agent')); _t('nc-lbl-acctype', cc('nc_acctype'));
  _t('nc-lbl-accstatus', cc('nc_accstatus')); _t('nc-lbl-trading', cc('nc_trading'));
  _t('nc-lbl-notes', cc('nc_notes'));
  const nn = document.getElementById('nc-notes'); if(nn) nn.placeholder = cc('nc_ph_notes');
  _t('nc-cancel-btn', cc('nc_cancel')); _t('nc-save-btn', cc('nc_save'));
  _t('cc-topbar-new-btn', cc('topbar_btn'));

  // Status filter chips
  buildFilterChips();

  // Re-render current section if data loaded
  if (_ccAllCards.length) ccRenderCurrentSection();
}

/* Hook into global applyLang */
const _ccOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_ccOrigApplyLang) _ccOrigApplyLang(lang);
  ccApplyLang();
};

/* ─── State ──────────────────────────────────────────────── */
let _ccAllCards   = [];
let _ccFilter     = '';   // active status filter for accounts section (quick chip)
let _ccSection    = 'accounts';
let _ccStatusChart = null;
let _ccMonChart   = null;

/* ─── Per-column sort + filter state (same pattern as /cards/list) ─ */
/* [key, labelKey, sortable, filterable] — labelKey is a cc('…') dictionary key */
var CCCOLS = [
  ['id',                    'th_id',       1, 0],
  ['account_number',        'th_acnum',    1, 1],
  ['client_phone',          'th_phone',    1, 1],
  ['month',                 'th_month',    1, 1],
  ['cc_manager_name',       'th_mgr',      1, 1],
  ['cc_manager_commission', 'th_mgr_c',    1, 0],
  ['cc_agent_name',         'th_agent',    1, 1],
  ['cc_agent_commission',   'th_agent_c',  1, 0],
  ['branch_name',           'th_branch',   1, 1],
  ['initial_deposit',       'th_dep',      1, 0],
  ['cc_status',             'th_status',   1, 1],
  ['_act',                  'th_act',      0, 0],
];
var CCSK='id',CCSD='desc',CFV={},KFCOL=null,KFITEMS=[],KFWORK=null,KFSEARCH='';

/* ─── Nav ────────────────────────────────────────────────── */
function ccShowSection(name) {
  document.querySelectorAll('.cc-section').forEach(s => s.style.display = 'none');
  ['accounts','reports','modified','monthly'].forEach(id => {
    const btn = document.getElementById('ccnav-' + id);
    const ico = document.getElementById('ccnav-ico-' + id);
    if (btn) { btn.style.background = 'none'; btn.style.border = '1px solid transparent'; }
    if (ico) { ico.style.background='rgba(26,173,186,.1)'; ico.style.borderColor='rgba(26,173,186,.15)'; }
  });
  const sec = document.getElementById('ccsec-' + name);
  const btn = document.getElementById('ccnav-' + name);
  const ico = document.getElementById('ccnav-ico-' + name);
  if (sec) sec.style.display = '';
  if (btn) { btn.style.background='rgba(26,173,186,.15)'; btn.style.border='1px solid rgba(26,173,186,.25)'; }
  if (ico) { ico.style.background='rgba(26,173,186,.25)'; ico.style.borderColor='rgba(26,173,186,.5)'; }
  _ccSection = name;
  history.replaceState(null,'',window.location.pathname + '#' + name);
  ccRenderCurrentSection();
}

function ccRenderCurrentSection() {
  if      (_ccSection === 'accounts') ccRenderAccounts();
  else if (_ccSection === 'reports')  ccRenderReports();
  else if (_ccSection === 'modified') ccRenderModified();
  else if (_ccSection === 'monthly')  ccRenderMonthly();
}

/* ─── Status helpers ──────────────────────────────────────── */
const CC_STATUS_COLORS = {
  cc_pending:'#f5a828', branch_pending:'#1AADBA', accepted:'#1ECC80', completed:'#0E7A88', rejected:'#E84545'
};
function ccStatusChip(s) {
  const labels = {
    cc_pending:cc('sf_draft'), branch_pending:cc('sf_sent'),
    accepted:cc('sf_acc'), in_progress:'🔄 قيد المتابعة', completed:cc('sf_comp'),
    rejected:cc('sf_rej'), declined:'🚫 رفض العميل',
  };
  return `<span class="cc-chip ${s}">${labels[s]||s}</span>`;
}

/* ─── Filter chips ──────────────────────────────────────── */
function buildFilterChips() {
  const wrap = document.getElementById('cc-filter-chips'); if (!wrap) return;
  const chips = [
    {v:'',   lk:'sf_all'},
    {v:'cc_pending', lk:'sf_draft'},
    {v:'branch_pending', lk:'sf_sent'},
    {v:'in_progress', t:'🔄 قيد المتابعة'},
    {v:'completed', lk:'sf_comp'},
    {v:'rejected', lk:'sf_rej'},
    {v:'declined', t:'🚫 رفض العميل'},
  ];
  wrap.innerHTML = chips.map(ch =>
    `<button class="cc-sf${_ccFilter===ch.v?' on':''}" onclick="setFilter('${ch.v}')">${ch.t || cc(ch.lk)}</button>`
  ).join('');
}
function setFilter(v) { _ccFilter = v; buildFilterChips(); ccRenderAccounts(); }

/* ─── Row-value extractors used by sort/filter (keep raw + display apart) ─ */
function ccRowAgentName(c){ return c.cc_agent?.name ?? c.ccAgent?.name ?? ''; }
function ccRowBranchName(c){ return (ccL()==='en' ? (c.branch?.name_en||c.branch?.name_ar) : (c.branch?.name_ar||c.branch?.name_en)) || ''; }
function ccRowPhone(c){ return ((c.client_country_code||'')+' '+(c.client_phone||'')).trim(); }
function ccRowStatus(c){ return c.cc_status||''; }
/* raw value for sort/filter — strings get lowercased in sort compare */
function ccRowValue(c, col){
  switch(col){
    case 'id':                    return Number(c.id)||0;
    case 'account_number':        return c.account_number || '';
    case 'client_phone':          return ccRowPhone(c);
    case 'month':                 return c.month_date || c.month || '';
    case 'cc_manager_name':       return c.cc_manager_name || '';
    case 'cc_manager_commission': return Number(c.cc_manager_commission)||0;
    case 'cc_agent_name':         return ccRowAgentName(c);
    case 'cc_agent_commission':   return Number(c.cc_agent_commission)||0;
    case 'branch_name':           return ccRowBranchName(c);
    case 'initial_deposit':       return Number(c.initial_deposit)||0;
    case 'cc_status':             return ccRowStatus(c);
    default:                      return '';
  }
}
/* label used in the filter dropdown — human-readable, same for id/raw */
function ccRowLabel(c, col){
  const v = ccRowValue(c, col);
  if (col === 'cc_status') {
    const map = {
      cc_pending:cc('sf_draft'), branch_pending:cc('sf_sent'), accepted:cc('sf_acc'),
      in_progress:'🔄 قيد المتابعة', completed:cc('sf_comp'), rejected:cc('sf_rej'),
      declined:'🚫 رفض العميل',
    };
    return map[v] || v || cc('kfm_blank');
  }
  if (col === 'cc_manager_commission' || col === 'cc_agent_commission' || col === 'initial_deposit')
    return Number(v).toFixed(2);
  if (col === 'id') return '#'+v;
  return (v==='' || v==null) ? cc('kfm_blank') : String(v);
}

/* ─── Sort/filter engine ───────────────────────────────────── */
function ccSortCompare(a, b, col){
  const va = ccRowValue(a, col), vb = ccRowValue(b, col);
  if (typeof va === 'number' && typeof vb === 'number') return va - vb;
  return String(va).toLowerCase().localeCompare(String(vb).toLowerCase(), undefined, {numeric:true});
}
function ccApplyFilters(rows){
  // 1) Status chip filter (preserved for quick-click UX)
  if (_ccFilter) rows = rows.filter(c => c.cc_status === _ccFilter);
  // 2) Per-column filter values
  for (const col in CFV) {
    const sel = CFV[col];
    if (!sel || !sel.length) continue;
    if (sel.length === 1 && sel[0] === '__none__') { rows = []; break; }
    const set = new Set(sel.map(String));
    rows = rows.filter(c => {
      const v = ccRowValue(c, col);
      const key = (v==='' || v==null) ? '__blank__' : String(v);
      return set.has(key);
    });
  }
  // 3) Sort
  if (CCSK) {
    rows = rows.slice().sort((a,b) => {
      const d = ccSortCompare(a, b, CCSK);
      return CCSD === 'asc' ? d : -d;
    });
  }
  return rows;
}
function ccHasActiveFilter(){
  if (_ccFilter) return true;
  for (const k in CFV) if (CFV[k] && CFV[k].length) return true;
  return !(CCSK === 'id' && CCSD === 'desc');
}

/* ─── ACCOUNTS RENDER ────────────────────────────────────── */
function ccRenderAccounts() {
  const thead = document.getElementById('cc-thead');
  if (thead) {
    thead.innerHTML = '<tr>' + CCCOLS.map(function(c){
      const key=c[0], lk=c[1], sortable=c[2], filterable=c[3];
      const label = cc(lk) || lk;
      if (!sortable) return '<th>'+label+'</th>';
      const sorted  = CCSK===key;
      const filtered= !!(CFV[key] && CFV[key].length);
      const arrow   = sorted ? (CCSD==='asc'?'▲':'▼') : '';
      const funnel  = filtered ? '🔽' : (filterable ? '▾' : '');
      return '<th class="kh'+(sorted?' srt':'')+(filtered?' flt':'')+'" '
        + 'title="'+(ccL()==='en'?'Sort &amp; filter':'فرز وتصفية')+'" '
        + 'onclick="cckMenu(event,\''+key+'\','+(filterable?1:0)+')">'
        + '<span>'+label+'</span>'
        + (arrow?' <span class="ar">'+arrow+'</span>':'')
        + (funnel?' <span class="kh-fn">'+funnel+'</span>':'')
        + '</th>';
    }).join('') + '</tr>';
  }
  const src = _ccAllCards || [];
  const filtered = ccApplyFilters(src);
  // Toggle the "clear" button next to chips
  const cl = document.getElementById('cc-clear-filters');
  if (cl) cl.classList.toggle('on', ccHasActiveFilter());
  const cll = document.getElementById('cc-clear-l');
  if (cll) cll.textContent = cc('clear_flt').replace(/^↺\s*/,'');

  const tbody = document.getElementById('cc-tbody'); if (!tbody) return;
  if (!filtered.length) {
    tbody.innerHTML = '<tr><td colspan="12" style="text-align:center;padding:40px;color:var(--mu)">'+cc('empty')+'</td></tr>';
    return;
  }
  const money = v => (v!=null && v!=='' && !isNaN(v) && Number(v)>0) ? '$'+Number(v).toLocaleString('en') : '<span style="color:var(--brd2)">—</span>';
  const pill  = v => (v!=null && Number(v)>0) ? `<span class="cc-comm">$${Number(v).toLocaleString('en')}</span>` : '<span style="color:var(--brd2)">—</span>';
  tbody.innerHTML = filtered.map(c => `
    <tr class="cc-row"${c.cc_status==='cc_pending'?' style="background:rgba(245,168,40,.10);border-right:3px solid #f5a828"':''}>
      <td><span class="cc-id">#${c.id}</span></td>
      <td><strong class="cc-acc">${c.account_number ? esc(c.account_number) : '<span style=\"color:var(--brd2)\">—</span>'}</strong></td>
      <td style="white-space:nowrap;direction:ltr">${esc(((c.client_country_code? c.client_country_code+' ':'')+(c.client_phone||'')).trim()||'—')}</td>
      <td style="white-space:nowrap">${esc(c.month)}</td>
      <td><span class="cc-person mgr">👩‍💼 ${esc(c.cc_manager_name || '—')}</span></td>
      <td>${pill(c.cc_manager_commission)}</td>
      <td><span class="cc-person">🎧 ${esc(c.cc_agent?.name ?? c.ccAgent?.name ?? '—')}</span></td>
      <td>${pill(c.cc_agent_commission)}</td>
      <td><span class="cc-branch">🏢 ${esc(c.branch?.name_ar ?? '—')}</span></td>
      <td class="cc-td-c"><span class="cc-dep">${money(c.initial_deposit)}</span></td>
      <td>
        ${ccStatusChip(c.cc_status)}
        ${c.cc_rejection_reason?`<div style="font-size:10px;color:#842029;margin-top:3px;max-width:170px">${cc('reason_lbl')} ${esc(c.cc_rejection_reason)}</div>`:''}
      </td>
      <td>${ccActionButtons(c)}</td>
    </tr>`).join('');
}
function ccActionButtons(c) {
  let main = '';
  if      (c.cc_status==='cc_pending')     main = `<button class="btn btn-primary btn-sm" onclick="sendCard(${c.id})">${cc('btn_send')}</button>`;
  else if (c.cc_status==='branch_pending') main = `<span style="font-size:11px;color:var(--mu)">${cc('status_wait_branch')}</span>`;
  else if (c.cc_status==='accepted')       main = `<span style="font-size:11px;color:var(--gr)">${cc('status_completing')}</span>`;
  else if (c.cc_status==='rejected')       main = `<button class="btn btn-warning btn-sm" onclick="resendCard(${c.id})">${cc('btn_resend')}</button>`;
  // ✏️ edit only while the card is still a draft (before it is sent to the branch)
  const edit = c.cc_status==='cc_pending'
    ? `<button class="btn btn-ghost btn-sm" title="تعديل / Edit" onclick="openCcEdit(${c.id})" style="padding:4px 8px">✏️</button>` : '';
  // 🗑 delete — requires the manager's password
  const del = `<button class="btn btn-ghost btn-sm" title="حذف / Delete" onclick="ccDeleteCard(${c.id})" style="padding:4px 8px;color:var(--re);border-color:rgba(224,80,80,.3)">🗑</button>`;
  return `<div style="display:flex;gap:5px;align-items:center;flex-wrap:wrap">${main}${edit}${del}</div>`;
}

/* ──────────────────────────────────────────────────────────── */
/*  Excel-style sort + filter dropdown — mirrors /cards/list    */
/* ──────────────────────────────────────────────────────────── */
function cckEnsureMenu(){
  var m = document.getElementById('cck-fmenu');
  if (!m) {
    m = document.createElement('div');
    m.id = 'cck-fmenu'; m.className = 'k-fmenu';
    document.body.appendChild(m);
    document.addEventListener('click', function(ev){
      if (m.style.display==='block' && !m.contains(ev.target)) m.style.display='none';
    });
    window.addEventListener('resize', function(){ m.style.display='none'; });
  }
  return m;
}
function cckPosition(anchor){
  var m = document.getElementById('cck-fmenu'); if (!m || !anchor) return;
  var r = anchor.getBoundingClientRect(), w = m.offsetWidth||260;
  var isRtl = (document.documentElement.getAttribute('dir')||'').toLowerCase()==='rtl'
    || (document.body && getComputedStyle(document.body).direction==='rtl');
  var left = isRtl ? Math.max(8, r.left) : Math.min(Math.max(8, r.right-w), window.innerWidth-w-8);
  left = Math.max(8, Math.min(left, window.innerWidth-w-8));
  var top = Math.min(r.bottom+4, window.innerHeight-(m.offsetHeight||300)-8);
  m.style.left = left+'px'; m.style.top = Math.max(8, top)+'px';
}

/* Open the menu for a column — ev: click event, col: column key, filterable: 1/0 */
window.cckMenu = function(ev, col, filterable){
  ev.stopPropagation();
  KFCOL = col; KFSEARCH = ''; KFITEMS = []; KFWORK = new Set();
  var anchor = ev.currentTarget;
  cckEnsureMenu();
  var m = document.getElementById('cck-fmenu');
  // Build items from the IN-MEMORY data (status chip + non-column filters still apply to the source)
  // but when a user is filtering several columns, we want each dropdown to reflect the LATEST data
  // after OTHER columns' filters — so build items from "all except this column".
  var _save = CFV[col]; delete CFV[col];
  var _savedFilter = _ccFilter;              // keep status chip; it's a soft overlay
  var src = _ccAllCards || [];
  // We DO respect the status chip, so the user can "filter inside this status".
  var pool = ccApplyFilters(src);
  if (_save) CFV[col] = _save;

  // Distinct values with labels, limited pool is already filtered
  var seen = {}, items = [];
  pool.forEach(function(c){
    var v = ccRowValue(c, col);
    var key = (v==='' || v==null) ? '__blank__' : String(v);
    if (seen[key]) return; seen[key]=1;
    items.push({ v:key, label: ccRowLabel(c, col) });
  });
  // Sort items: numeric columns by value, others by label
  var numericCols = {cc_manager_commission:1,cc_agent_commission:1,initial_deposit:1,id:1};
  if (numericCols[col]) items.sort(function(a,b){ return (parseFloat(a.v)||0)-(parseFloat(b.v)||0); });
  else items.sort(function(a,b){ return String(a.label).localeCompare(String(b.label), undefined, {numeric:true}); });

  KFITEMS = items;
  var sel = CFV[col] ? new Set(CFV[col].map(String)) : null;
  KFWORK = new Set();
  KFITEMS.forEach(function(it){ if (!sel || sel.has(String(it.v))) KFWORK.add(String(it.v)); });

  if (filterable) {
    cckBuildShell(/*withList=*/true);
    cckRenderList();
  } else {
    // Sort-only menu (no value list)
    cckBuildShell(/*withList=*/false);
  }
  m.style.display = 'block';
  cckPosition(anchor);
};
function cckBuildShell(withList){
  var m = document.getElementById('cck-fmenu');
  var h = '<div class="kfm-srt">'
        + '<button type="button" onclick="cckMenuSort(\'asc\')">▲ '+cc('kfm_asc')+'</button>'
        + '<button type="button" onclick="cckMenuSort(\'desc\')">▼ '+cc('kfm_desc')+'</button></div>';
  if (withList) {
    h += '<input class="kfm-srch form-control" placeholder="'+cc('kfm_srch')+'" oninput="cckMenuSearch(this.value)">'
      + '<label class="kfm-it kfm-all"><input type="checkbox" id="cck-allck" onchange="cckMenuAll(this.checked)"> <b>'+cc('kfm_all')+'</b> <span id="cck-cnt" style="color:var(--mu);font-weight:600"></span></label>'
      + '<div class="kfm-list" id="cck-list"></div>'
      + '<div class="kfm-act">'
      +   '<button class="kfm-ok" type="button" onclick="cckMenuApply()">'+cc('kfm_apply')+'</button>'
      +   '<button class="kfm-cl" type="button" onclick="cckMenuClear()">'+cc('kfm_clear')+'</button>'
      + '</div>';
  }
  m.innerHTML = h;
}
function cckRenderList(){
  var lst = document.getElementById('cck-list'); if (!lst) return;
  var s = KFSEARCH.toLowerCase();
  var list = KFITEMS.filter(function(it){ return !s || String(it.label).toLowerCase().indexOf(s)>=0; });
  var cap = 800, shown = list.slice(0, cap);
  var esc = window.esc || function(x){ return String(x).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); };
  lst.innerHTML = shown.map(function(it){
    var v = String(it.v);
    return '<label class="kfm-it"><input type="checkbox" '+(KFWORK.has(v)?'checked':'')
      +' onchange="cckTick(this)" data-v="'+esc(v)+'"> <span>'+esc(it.label)+'</span></label>';
  }).join('') || ('<div style="padding:10px;color:var(--mu);text-align:center">—</div>');
  if (list.length > cap) lst.innerHTML += '<div class="kfm-note">+'+(list.length-cap)+' …</div>';
  cckCnt();
}
function cckCnt(){
  var cnt = document.getElementById('cck-cnt');
  if (cnt) cnt.textContent = '('+KFWORK.size+'/'+KFITEMS.length+')';
  var a = document.getElementById('cck-allck');
  if (a) a.checked = (KFITEMS.length>0 && KFWORK.size===KFITEMS.length);
}
window.cckTick = function(cb){
  var v = cb.getAttribute('data-v');
  if (cb.checked) KFWORK.add(v); else KFWORK['delete'](v);
  cckCnt();
};
window.cckMenuAll    = function(ck){ KFWORK = ck ? new Set(KFITEMS.map(function(it){return String(it.v);})) : new Set(); cckRenderList(); };
window.cckMenuSearch = function(v){ KFSEARCH = v; cckRenderList(); };
window.cckMenuSort   = function(dir){ CCSK = KFCOL; CCSD = dir; document.getElementById('cck-fmenu').style.display='none'; ccRenderAccounts(); };
window.cckMenuApply  = function(){
  if (KFWORK.size === KFITEMS.length)      { delete CFV[KFCOL]; }
  else if (KFWORK.size === 0)              { CFV[KFCOL] = ['__none__']; }
  else                                     { CFV[KFCOL] = Array.from(KFWORK); }
  document.getElementById('cck-fmenu').style.display='none';
  ccRenderAccounts();
};
window.cckMenuClear  = function(){ delete CFV[KFCOL]; document.getElementById('cck-fmenu').style.display='none'; ccRenderAccounts(); };

/* Clear all filters + sort — button sitting next to the status chips */
window.cckClearAll = function(){
  CFV = {}; CCSK='id'; CCSD='desc'; _ccFilter='';
  buildFilterChips(); ccRenderAccounts();
};

/* ─── REPORTS RENDER ────────────────────────────────────── */
function ccRenderReports() {
  const data = _ccAllCards;
  const count = (s) => data.filter(c => c.cc_status === s).length;
  const draft = count('cc_pending'), sent = count('branch_pending'),
        acc   = count('accepted'),   comp = count('completed'), rej = count('rejected');

  document.getElementById('kpi-val-total').textContent = data.length;
  document.getElementById('kpi-val-draft').textContent = draft;
  document.getElementById('kpi-val-sent').textContent  = sent;
  document.getElementById('kpi-val-acc').textContent   = acc;
  document.getElementById('kpi-val-comp').textContent  = comp;
  document.getElementById('kpi-val-rej').textContent   = rej;

  // Doughnut chart
  if (_ccStatusChart) _ccStatusChart.destroy();
  const ctx = document.getElementById('cc-status-chart')?.getContext('2d');
  if (ctx && data.length) {
    _ccStatusChart = new Chart(ctx, {
      type: 'doughnut',
      data: {
        labels: [cc('kpi_draft'),cc('kpi_sent'),cc('kpi_acc'),cc('kpi_comp'),cc('kpi_rej')],
        datasets: [{
          data: [draft, sent, acc, comp, rej],
          backgroundColor: ['#f5a828','#1AADBA','#1ECC80','#0E7A88','#E84545'],
          borderWidth: 2, borderColor: 'var(--bg3)',
        }]
      },
      options: { responsive:true, maintainAspectRatio:false, plugins: {
        legend: { position:'bottom', labels: { color:'#7AABCA', font:{size:11} } }
      }}
    });
  }

  // By Branch
  const branchMap = {};
  data.forEach(c => {
    const bName = c.branch?.name_ar || '—';
    if (!branchMap[bName]) branchMap[bName] = {total:0,acc:0,comp:0,rej:0};
    branchMap[bName].total++;
    if (c.cc_status==='accepted')  branchMap[bName].acc++;
    if (c.cc_status==='completed') branchMap[bName].comp++;
    if (c.cc_status==='rejected')  branchMap[bName].rej++;
  });
  const brTbody = document.getElementById('rpt-branch-tbody');
  if (brTbody) {
    const rows = Object.entries(branchMap).sort((a,b)=>b[1].total-a[1].total);
    brTbody.innerHTML = rows.length
      ? rows.map(([br,s]) => `<tr>
          <td style="font-weight:700">${br}</td>
          <td><span class="badge badge-blue">${s.total}</span></td>
          <td><span style="color:var(--gr)">${s.acc}</span></td>
          <td><span style="color:var(--pri)">${s.comp}</span></td>
          <td><span style="color:var(--re)">${s.rej}</span></td>
        </tr>`).join('')
      : `<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--mu)">${cc('empty_rpt')}</td></tr>`;
  }

  // By Agent
  const agentMap = {};
  data.forEach(c => {
    const aName = c.cc_agent?.name || c.ccAgent?.name || '—';
    if (!agentMap[aName]) agentMap[aName] = {total:0,comp:0,rej:0};
    agentMap[aName].total++;
    if (c.cc_status==='completed') agentMap[aName].comp++;
    if (c.cc_status==='rejected')  agentMap[aName].rej++;
  });
  const agTbody = document.getElementById('rpt-agent-tbody');
  if (agTbody) {
    const rows = Object.entries(agentMap).sort((a,b)=>b[1].total-a[1].total);
    agTbody.innerHTML = rows.length
      ? rows.map(([ag,s]) => {
          const rate = s.total > 0 ? Math.round((s.comp/s.total)*100) : 0;
          return `<tr>
            <td style="font-weight:700">${ag}</td>
            <td>${s.total}</td>
            <td><span style="color:var(--pri)">${s.comp}</span></td>
            <td><span style="color:var(--re)">${s.rej}</span></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:var(--brd1);border-radius:3px;overflow:hidden">
                  <div style="width:${rate}%;height:100%;background:var(--gr);border-radius:3px"></div>
                </div>
                <span style="font-size:11px;font-weight:700;color:var(--gr)">${rate}%</span>
              </div>
            </td>
          </tr>`;
        }).join('')
      : `<tr><td colspan="5" style="text-align:center;padding:20px;color:var(--mu)">${cc('empty_rpt')}</td></tr>`;
  }
}

/* ─── MODIFIED RENDER ────────────────────────────────────── */
function ccRenderModified() {
  const modified = _ccAllCards.filter(c => c.status === 'modified');
  const badge = document.getElementById('ccs-mod-count');
  if (badge) {
    if (modified.length) { badge.textContent = modified.length; badge.style.display=''; }
    else badge.style.display = 'none';
  }
  const tbody = document.getElementById('mod-tbody'); if (!tbody) return;
  tbody.innerHTML = modified.length
    ? modified.map(c => `<tr class="row-modified">
        <td><strong>${c.account_number}</strong></td>
        <td>${c.month}</td>
        <td>${c.branch?.name_ar||'—'}</td>
        <td>${c.cc_agent?.name||c.ccAgent?.name||'—'}</td>
        <td class="mono c-blue">${c.broker_commission?'$'+c.broker_commission+'':'—'}</td>
        <td class="mono c-green">${c.initial_deposit?'$'+Number(c.initial_deposit).toLocaleString('en'):'—'}</td>
        <td>${ccStatusChip(c.cc_status)}</td>
      </tr>`).join('')
    : `<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">${cc('mod_empty')}</td></tr>`;
}

/* ─── MONTHLY RENDER ────────────────────────────────────── */
function ccRenderMonthly() {
  // Build year selector
  const years = [...new Set(_ccAllCards.map(c => c.month_date?.slice(0,4) || c.month?.slice(-4)).filter(Boolean))].sort((a,b)=>b-a);
  const sel = document.getElementById('mon-year-sel');
  if (sel && years.length && !sel.options.length) {
    years.forEach(y => { const o = document.createElement('option'); o.value = o.textContent = y; sel.appendChild(o); });
  }
  const selYear = sel?.value || years[0] || String(new Date().getFullYear());

  // Filter cards for selected year
  const yearCards = _ccAllCards.filter(c => {
    const y = c.month_date?.slice(0,4) || c.month?.match(/(\d{4})/)?.[1];
    return y === selYear;
  });

  // Group by month
  const MONTHS_AR = ['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
  const MONTHS_EN = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const monthData = {};
  for (let m = 1; m <= 12; m++) {
    const key = selYear + '-' + String(m).padStart(2,'0');
    monthData[key] = {label: ccL()==='ar'?MONTHS_AR[m-1]:MONTHS_EN[m-1], total:0, cc_pending:0, branch_pending:0, accepted:0, completed:0, rejected:0};
  }
  yearCards.forEach(c => {
    const key = (c.month_date||'').slice(0,7);
    if (monthData[key]) {
      monthData[key].total++;
      if (monthData[key].hasOwnProperty(c.cc_status)) monthData[key][c.cc_status]++;
    }
  });

  const keys   = Object.keys(monthData);
  const labels = keys.map(k => monthData[k].label);
  const totals = keys.map(k => monthData[k].total);

  // Bar chart
  if (_ccMonChart) _ccMonChart.destroy();
  const ctx = document.getElementById('cc-monthly-chart')?.getContext('2d');
  if (ctx) {
    _ccMonChart = new Chart(ctx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: cc('kpi_comp'), data: keys.map(k=>monthData[k].completed),    backgroundColor:'#0E7A88' },
          { label: cc('kpi_acc'),  data: keys.map(k=>monthData[k].accepted),     backgroundColor:'#1ECC80' },
          { label: cc('kpi_sent'), data: keys.map(k=>monthData[k].branch_pending),backgroundColor:'#1AADBA' },
          { label: cc('kpi_rej'),  data: keys.map(k=>monthData[k].rejected),     backgroundColor:'#E84545' },
          { label: cc('kpi_draft'),data: keys.map(k=>monthData[k].cc_pending),   backgroundColor:'#f5a828' },
        ]
      },
      options: {
        responsive:true, maintainAspectRatio:false,
        plugins: { legend:{ position:'bottom', labels:{ color:'#7AABCA', font:{size:11} } } },
        scales: {
          x: { stacked:true, ticks:{ color:'#5A80A0', font:{size:10} }, grid:{ color:'rgba(46,134,171,.1)' } },
          y: { stacked:true, ticks:{ color:'#5A80A0', font:{size:10} }, grid:{ color:'rgba(46,134,171,.1)' }, beginAtZero:true }
        }
      }
    });
  }

  // Monthly table
  const tbody = document.getElementById('mon-tbody'); if (!tbody) return;
  const hasData = keys.some(k => monthData[k].total > 0);
  tbody.innerHTML = hasData
    ? keys.map(k => {
        const d = monthData[k];
        return d.total > 0
          ? `<tr>
              <td style="font-weight:700">${d.label} ${selYear}</td>
              <td><span class="badge badge-blue">${d.total}</span></td>
              <td style="color:var(--or)">${d.cc_pending||'—'}</td>
              <td style="color:var(--pri2)">${d.branch_pending||'—'}</td>
              <td style="color:var(--gr)">${d.accepted||'—'}</td>
              <td style="color:var(--pri)">${d.completed||'—'}</td>
              <td style="color:var(--re)">${d.rejected||'—'}</td>
            </tr>`
          : '';
      }).join('') || `<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--mu)">${cc('mon_empty')}</td></tr>`
    : `<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--mu)">${cc('mon_empty')}</td></tr>`;
}

/* ─── Load all CC data ───────────────────────────────────── */
async function ccLoadAll() {
  const loading = document.getElementById('cc-loading-cell');
  if (loading) loading.textContent = cc('loading');
  const r = await api('GET', '/cc/sent?per_page=1000');
  if (!r.success) {
    const eb = document.getElementById('cc-alert-err');
    if (eb) { eb.textContent = r.message; eb.classList.add('show'); }
    return;
  }
  _ccAllCards = r.data?.data || r.data || [];
  // Badge for accounts section
  const nb = document.getElementById('ccnav-badge-accounts');
  const pending = _ccAllCards.filter(c=>c.cc_status==='branch_pending').length;
  if (nb && pending > 0) { nb.textContent = pending; nb.style.display = ''; }
  ccRenderCurrentSection();
}

/* ─── Actions ────────────────────────────────────────────── */
async function sendCard(id) {
  if (!confirm(cc('confirm_send'))) return;
  const r = await api('POST', `/cc/cards/${id}/send`);
  ccShowAlert(r.success?'ok':'err', r.message);
  if (r.success) { await ccLoadAll(); }
}
async function resendCard(id) {
  if (!confirm(cc('confirm_resend'))) return;
  const r = await api('POST', `/cc/cards/${id}/resend`);
  ccShowAlert(r.success?'ok':'err', r.message);
  if (r.success) { await ccLoadAll(); }
}
function ccShowAlert(type, msg) {
  const e = document.getElementById('cc-alert-err');
  const o = document.getElementById('cc-alert-ok');
  if (e) e.classList.remove('show');
  if (o) o.classList.remove('show');
  if (type==='err' && e) { e.textContent=msg; e.classList.add('show'); window.scrollTo(0,0); }
  else if (o)             { o.textContent=msg; o.classList.add('show'); window.scrollTo(0,0); }
}
// Success banner for a new draft card WITH an inline "Send to branch now" button.
function ccShowSendPrompt(id, msg) {
  const e = document.getElementById('cc-alert-err'); if (e) e.classList.remove('show');
  const o = document.getElementById('cc-alert-ok');  if (!o) return;
  o.innerHTML = '<div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">'
    + '<span style="font-weight:700">✅ ' + esc(msg) + '</span>'
    + '<button class="btn btn-primary btn-sm" style="white-space:nowrap" onclick="sendCard(' + id + ')">' + cc('btn_send_now') + '</button>'
    + '<button class="btn btn-ghost btn-sm" onclick="this.closest(\'.alert\').classList.remove(\'show\')">' + cc('btn_send_later') + '</button>'
    + '</div>';
  o.classList.add('show'); window.scrollTo(0, 0);
}

/* ─── Month / year picker (same look as the dashboard calendar) ─ */
var NC_MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var _ncCalYear = new Date().getFullYear();
function ncSetMonth(label){
  var inp=document.getElementById('nc-month'), btn=document.getElementById('nc-month-btn');
  if(inp) inp.value = label || '';
  if(btn) btn.textContent = label || (cc('opt_month') || 'اختر الشهر / Pick month');
}
function ncCalRender(){
  var el=document.getElementById('nc-cal'); if(!el) return;
  var cur=(document.getElementById('nc-month').value||'').split(' '), curM=cur[0], curY=cur[1];
  var h='<div class="nc-cal-nav"><button type="button" onclick="ncCalYearStep(-1,event)">◀</button><b>'+_ncCalYear+'</b><button type="button" onclick="ncCalYearStep(1,event)">▶</button></div><div class="nc-cal-grid">';
  NC_MONTHS.forEach(function(m){
    var on=(m===curM && String(_ncCalYear)===String(curY));
    h+='<button type="button" class="nc-cal-m'+(on?' on':'')+'" onclick="ncCalPick(\''+m+'\',event)">'+m+'</button>';
  });
  el.innerHTML=h+'</div>';
}
function ncCalToggle(e){
  if(e&&e.stopPropagation)e.stopPropagation();
  var el=document.getElementById('nc-cal'); if(!el) return;
  var open=el.classList.contains('open');
  if(open){ el.classList.remove('open'); return; }
  var v=(document.getElementById('nc-month').value||'').split(' '); if(v[1]) _ncCalYear=parseInt(v[1]);
  ncCalRender(); el.classList.add('open');
}
function ncCalYearStep(d,e){ if(e&&e.stopPropagation)e.stopPropagation(); _ncCalYear+=d; ncCalRender(); }
function ncCalPick(m,e){ if(e&&e.stopPropagation)e.stopPropagation(); ncSetMonth(m+' '+_ncCalYear); var el=document.getElementById('nc-cal'); if(el) el.classList.remove('open'); }
document.addEventListener('click',function(e){
  var el=document.getElementById('nc-cal');
  if(el&&el.classList.contains('open')&&!el.contains(e.target)&&e.target.id!=='nc-month-btn') el.classList.remove('open');
});

/* Sub account → show a required parent-account field (same as the branch new-card screen). */
function ncKindChange(){
  var sub = document.getElementById('nc-kind').value === 'sub';
  var row = document.getElementById('nc-parent-row'); if(row) row.style.display = sub ? '' : 'none';
  if(!sub){ var p=document.getElementById('nc-parent'); if(p) p.value=''; }
}
/* Client source = "Other" → show a free-text field to type the actual source. */
function ncSourceChange(){
  var other = document.getElementById('nc-source').value === 'أخرى';
  var row = document.getElementById('nc-source-other-row'); if(row) row.style.display = other ? '' : 'none';
  if(!other){ var o=document.getElementById('nc-source-other'); if(o) o.value=''; }
}

/* ─── Create card ────────────────────────────────────────── */
async function createCard() {
  const ac     = document.getElementById('nc-ac').value.trim();
  const monthV = document.getElementById('nc-month').value;
  const branch = document.getElementById('nc-branch').value;
  const agent  = document.getElementById('nc-agent').value;
  const phone  = document.getElementById('nc-phone').value.trim();
  const ccode  = document.getElementById('nc-cc').value || '+965';
  const errEl  = document.getElementById('nc-err');
  errEl.classList.remove('show');
  // Phone is now the PRIMARY required field — the lead is identified by it. Account is optional.
  if (!phone) {
    errEl.textContent = '⚠️ رقم هاتف العميل إلزامي — هو أساس بناء الكرت.';
    errEl.classList.add('show'); return;
  }
  if (!monthV || !branch || !agent) {
    errEl.textContent = cc('nc_err_required');
    errEl.classList.add('show'); return;
  }
  if (ac && !/^\d{6}$/.test(ac)) {
    errEl.textContent = '⚠️ رقم الحساب يجب أن يتكوّن من 6 أرقام بالضبط (أو اتركه فارغًا).';
    errEl.classList.add('show'); return;
  }
  const kind = document.getElementById('nc-kind').value;
  const parent = (document.getElementById('nc-parent').value||'').replace(/[^0-9]/g,'');
  if (kind === 'sub' && !/^\d{6}$/.test(parent)) {
    errEl.textContent = 'رقم الحساب الأساسي يجب أن يتكوّن من 6 أرقام بالضبط.';
    errEl.classList.add('show'); return;
  }
  // CC team cap: agent + manager commission ≤ $2
  const ccAgentC = parseFloat(document.getElementById('nc-agent-comm').value)||0;
  const ccMgrC   = parseFloat(document.getElementById('nc-mgr-comm').value)||0;
  if (ccAgentC + ccMgrC > 2.0001) {
    errEl.textContent = '⛔ عمولة موظف مركز الاتصال + المديرة ('+(ccAgentC+ccMgrC).toFixed(2)+'$) تتجاوز الحد المسموح (2$).';
    errEl.classList.add('show'); return;
  }
  // Client source: when "Other" is chosen, use the free-text value the user typed.
  let source = document.getElementById('nc-source').value || null;
  if (source === 'أخرى') source = (document.getElementById('nc-source-other').value||'').trim() || 'أخرى';
  const parts = monthV.split(' ');
  const mMap  = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
  const mm    = String(mMap[parts[0]]??1).padStart(2,'0');
  const yyyy  = parts[1] ?? new Date().getFullYear();
  const payload = {
    account_number:    ac || null, month: monthV, month_date: `${yyyy}-${mm}-01`,
    target_branch_id:  parseInt(branch), cc_agent_id: parseInt(agent),
    account_kind:      kind,
    parent_account:    kind === 'sub' ? parent : null,
    account_type_id:   parseInt(document.getElementById('nc-acc-type').value)||null,
    account_status_id: null,
    trading_type_id:   parseInt(document.getElementById('nc-trading').value)||null,
    notes:             document.getElementById('nc-notes').value.trim()||null,
    client_phone:      phone,
    client_country_code: ccode,
    client_source:     source,
    initial_deposit:   parseFloat(document.getElementById('nc-dep').value)||0,
    cc_agent_commission:   parseFloat(document.getElementById('nc-agent-comm').value)||0,
    cc_manager_commission: parseFloat(document.getElementById('nc-mgr-comm').value)||0,
  };
  // Edit mode (draft) → PUT; otherwise create → POST
  const r = window._ccEditId
    ? await api('PUT', `/cc/cards/${window._ccEditId}`, payload)
    : await api('POST', '/cc/cards', payload);
  if (r.success) {
    errEl.classList.remove('show');
    const wasEdit = !!window._ccEditId;
    const newId   = r.data?.id;
    window._ccEditId = null;
    closeModal('modal-cc-create');
    ['nc-ac','nc-notes','nc-phone'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    var ns=document.getElementById('nc-source'); if(ns) ns.value='';
    await ccLoadAll();
    // A freshly-created card is saved as a DRAFT — it is NOT sent automatically. The CC manager
    // reviews the data, then sends it to the target branch via the «إرسال للفرع» button.
    if (!wasEdit && newId) ccShowSendPrompt(newId, r.message);
    else ccShowAlert('ok', '✅ ' + r.message);
  } else if (r.duplicate) {
    // Same customer already under follow-up — not an error; inform and close.
    closeModal('modal-cc-create');
    ['nc-ac','nc-notes','nc-phone'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    var ns2=document.getElementById('nc-source'); if(ns2) ns2.value='';
    ccShowAlert('ok', 'ℹ️ ' + r.message);
  } else {
    errEl.textContent = '❌ ' + (r.errors?Object.values(r.errors).flat().join(' | '):r.message);
    errEl.classList.add('show');
  }
}

/* ─── New / Edit / Delete a CC card ──────────────────────── */
function openCcNew(){
  window._ccEditId = null;
  ['nc-ac','nc-notes','nc-phone','nc-parent','nc-source-other'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  ['nc-kind','nc-source'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='';});
  ['nc-dep','nc-mgr-comm','nc-agent-comm'].forEach(id=>{const el=document.getElementById(id);if(el)el.value='0';});
  ncKindChange(); ncSourceChange();
  const _n=new Date(); ncSetMonth(NC_MONTHS[_n.getMonth()]+' '+_n.getFullYear());
  const err=document.getElementById('nc-err'); if(err) err.classList.remove('show');
  const ttl=document.getElementById('cc-modal-create-title'); if(ttl) ttl.textContent='📞 كرت CC جديد / New CC Card';
  openModal('modal-cc-create');
}
function openCcEdit(id){
  const c = (_ccAllCards||[]).find(x=>x.id===id);
  if(!c){ ccShowAlert('err','الكرت غير موجود'); return; }
  window._ccEditId = id;
  const setV=(eid,val)=>{const el=document.getElementById(eid); if(el) el.value=(val==null?'':val);};
  setV('nc-ac', c.account_number); setV('nc-kind', c.account_kind||'new');
  ncSetMonth(c.month || ''); setV('nc-branch', c.branch_id);
  setV('nc-agent', c.cc_agent_id); setV('nc-acc-type', c.account_type_id);
  setV('nc-trading', c.trading_type_id);
  setV('nc-notes', c.notes); setV('nc-phone', c.client_phone);
  setV('nc-cc', c.client_country_code || '+965');
  setV('nc-parent', c.parent_account);
  setV('nc-dep', c.initial_deposit||0); setV('nc-mgr-comm', c.cc_manager_commission||0); setV('nc-agent-comm', c.cc_agent_commission||0);
  // client source: a saved value not in the preset list → treat as "Other" + free text
  var srcSel=document.getElementById('nc-source'); var preset=['إعلان','توصية','اتصال مباشر','سوشيال ميديا','واتساب','معرض أو فعالية'];
  if(c.client_source && preset.indexOf(c.client_source)<0){ setV('nc-source','أخرى'); setV('nc-source-other', c.client_source); }
  else { setV('nc-source', c.client_source||''); setV('nc-source-other',''); }
  ncKindChange(); ncSourceChange();
  const err=document.getElementById('nc-err'); if(err) err.classList.remove('show');
  const ttl=document.getElementById('cc-modal-create-title'); if(ttl) ttl.textContent='✏️ تعديل كرت CC / Edit CC Card';
  openModal('modal-cc-create');
}
function ccDeleteCard(id){
  window._ccDelId = id;
  const p=document.getElementById('cc-del-pass'); if(p) p.value='';
  const e=document.getElementById('cc-del-err'); if(e) e.classList.remove('show');
  openModal('modal-cc-delete');
}
async function ccConfirmDelete(){
  const err=document.getElementById('cc-del-err');
  const pass=(document.getElementById('cc-del-pass')||{}).value||'';
  if(!pass){ err.textContent='⚠️ أدخل كلمة المرور / Enter password'; err.classList.add('show'); return; }
  const r=await api('POST', `/cc/cards/${window._ccDelId}/delete-secure`, {password:pass});
  if(r&&r.success){ closeModal('modal-cc-delete'); ccShowAlert('ok','✅ '+r.message); window._ccDelId=null; await ccLoadAll(); }
  else { err.textContent='❌ '+((r&&r.message)||'فشل الحذف'); err.classList.add('show'); }
}
// One "new card" icon only: hide the global top-bar card buttons + the body duplicate on this screen.
/* ─── Full country dialing-code list (flags derived from the 2-letter code) ─── */
const NC_COUNTRIES=[
['+93','AF','Afghanistan'],['+355','AL','Albania'],['+213','DZ','Algeria'],['+376','AD','Andorra'],['+244','AO','Angola'],['+1','AG','Antigua & Barbuda'],['+54','AR','Argentina'],['+374','AM','Armenia'],['+61','AU','Australia'],['+43','AT','Austria'],['+994','AZ','Azerbaijan'],
['+1','BS','Bahamas'],['+973','BH','Bahrain'],['+880','BD','Bangladesh'],['+1','BB','Barbados'],['+375','BY','Belarus'],['+32','BE','Belgium'],['+501','BZ','Belize'],['+229','BJ','Benin'],['+975','BT','Bhutan'],['+591','BO','Bolivia'],['+387','BA','Bosnia & Herzegovina'],['+267','BW','Botswana'],['+55','BR','Brazil'],['+673','BN','Brunei'],['+359','BG','Bulgaria'],['+226','BF','Burkina Faso'],['+257','BI','Burundi'],
['+855','KH','Cambodia'],['+237','CM','Cameroon'],['+1','CA','Canada'],['+238','CV','Cape Verde'],['+236','CF','Central African Rep.'],['+235','TD','Chad'],['+56','CL','Chile'],['+86','CN','China'],['+57','CO','Colombia'],['+269','KM','Comoros'],['+242','CG','Congo'],['+243','CD','Congo (DRC)'],['+506','CR','Costa Rica'],['+225','CI','Côte d’Ivoire'],['+385','HR','Croatia'],['+53','CU','Cuba'],['+357','CY','Cyprus'],['+420','CZ','Czechia'],
['+45','DK','Denmark'],['+253','DJ','Djibouti'],['+1','DM','Dominica'],['+1','DO','Dominican Rep.'],
['+593','EC','Ecuador'],['+20','EG','Egypt'],['+503','SV','El Salvador'],['+240','GQ','Equatorial Guinea'],['+291','ER','Eritrea'],['+372','EE','Estonia'],['+268','SZ','Eswatini'],['+251','ET','Ethiopia'],
['+679','FJ','Fiji'],['+358','FI','Finland'],['+33','FR','France'],
['+241','GA','Gabon'],['+220','GM','Gambia'],['+995','GE','Georgia'],['+49','DE','Germany'],['+233','GH','Ghana'],['+30','GR','Greece'],['+1','GD','Grenada'],['+502','GT','Guatemala'],['+224','GN','Guinea'],['+245','GW','Guinea-Bissau'],['+592','GY','Guyana'],
['+509','HT','Haiti'],['+504','HN','Honduras'],['+852','HK','Hong Kong'],['+36','HU','Hungary'],
['+354','IS','Iceland'],['+91','IN','India'],['+62','ID','Indonesia'],['+98','IR','Iran'],['+964','IQ','Iraq'],['+353','IE','Ireland'],['+972','IL','Israel'],['+39','IT','Italy'],
['+1','JM','Jamaica'],['+81','JP','Japan'],['+962','JO','Jordan'],
['+7','KZ','Kazakhstan'],['+254','KE','Kenya'],['+686','KI','Kiribati'],['+965','KW','Kuwait'],['+996','KG','Kyrgyzstan'],
['+856','LA','Laos'],['+371','LV','Latvia'],['+961','LB','Lebanon'],['+266','LS','Lesotho'],['+231','LR','Liberia'],['+218','LY','Libya'],['+423','LI','Liechtenstein'],['+370','LT','Lithuania'],['+352','LU','Luxembourg'],
['+853','MO','Macau'],['+261','MG','Madagascar'],['+265','MW','Malawi'],['+60','MY','Malaysia'],['+960','MV','Maldives'],['+223','ML','Mali'],['+356','MT','Malta'],['+692','MH','Marshall Islands'],['+222','MR','Mauritania'],['+230','MU','Mauritius'],['+52','MX','Mexico'],['+691','FM','Micronesia'],['+373','MD','Moldova'],['+377','MC','Monaco'],['+976','MN','Mongolia'],['+382','ME','Montenegro'],['+212','MA','Morocco'],['+258','MZ','Mozambique'],['+95','MM','Myanmar'],
['+264','NA','Namibia'],['+674','NR','Nauru'],['+977','NP','Nepal'],['+31','NL','Netherlands'],['+64','NZ','New Zealand'],['+505','NI','Nicaragua'],['+227','NE','Niger'],['+234','NG','Nigeria'],['+850','KP','North Korea'],['+389','MK','North Macedonia'],['+47','NO','Norway'],
['+968','OM','Oman'],
['+92','PK','Pakistan'],['+680','PW','Palau'],['+970','PS','Palestine'],['+507','PA','Panama'],['+675','PG','Papua New Guinea'],['+595','PY','Paraguay'],['+51','PE','Peru'],['+63','PH','Philippines'],['+48','PL','Poland'],['+351','PT','Portugal'],['+974','QA','Qatar'],
['+40','RO','Romania'],['+7','RU','Russia'],['+250','RW','Rwanda'],
['+1','KN','Saint Kitts & Nevis'],['+1','LC','Saint Lucia'],['+1','VC','Saint Vincent'],['+685','WS','Samoa'],['+378','SM','San Marino'],['+239','ST','São Tomé & Príncipe'],['+966','SA','Saudi Arabia'],['+221','SN','Senegal'],['+381','RS','Serbia'],['+248','SC','Seychelles'],['+232','SL','Sierra Leone'],['+65','SG','Singapore'],['+421','SK','Slovakia'],['+386','SI','Slovenia'],['+677','SB','Solomon Islands'],['+252','SO','Somalia'],['+27','ZA','South Africa'],['+82','KR','South Korea'],['+211','SS','South Sudan'],['+34','ES','Spain'],['+94','LK','Sri Lanka'],['+249','SD','Sudan'],['+597','SR','Suriname'],['+46','SE','Sweden'],['+41','CH','Switzerland'],['+963','SY','Syria'],
['+886','TW','Taiwan'],['+992','TJ','Tajikistan'],['+255','TZ','Tanzania'],['+66','TH','Thailand'],['+670','TL','Timor-Leste'],['+228','TG','Togo'],['+676','TO','Tonga'],['+1','TT','Trinidad & Tobago'],['+216','TN','Tunisia'],['+90','TR','Türkiye'],['+993','TM','Turkmenistan'],['+688','TV','Tuvalu'],
['+256','UG','Uganda'],['+380','UA','Ukraine'],['+971','AE','United Arab Emirates'],['+44','GB','United Kingdom'],['+1','US','United States'],['+598','UY','Uruguay'],['+998','UZ','Uzbekistan'],
['+678','VU','Vanuatu'],['+58','VE','Venezuela'],['+84','VN','Vietnam'],
['+967','YE','Yemen'],['+260','ZM','Zambia'],['+263','ZW','Zimbabwe']
];
function ncFlag(cc){ try{ return cc.toUpperCase().replace(/./g,c=>String.fromCodePoint(127397+c.charCodeAt(0))); }catch(e){ return ''; } }
function ncBuildCountries(){
  const sel=document.getElementById('nc-cc'); if(!sel) return;
  const cur=sel.value||'+965';
  const pinned=['+965','+966','+971','+973','+974','+968','+20','+962','+961','+964','+963','+967','+970','+218','+249','+212','+216','+213'];
  const top=[], rest=[];
  NC_COUNTRIES.forEach(c=>{ (pinned.indexOf(c[0])>=0?top:rest).push(c); });
  top.sort((a,b)=>pinned.indexOf(a[0])-pinned.indexOf(b[0]));
  rest.sort((a,b)=>a[2].localeCompare(b[2]));
  const opt=c=>`<option value="${c[0]}">${ncFlag(c[1])} ${c[0]} ${c[2]}</option>`;
  sel.innerHTML=top.map(opt).join('')+'<option value="" disabled>──────────</option>'+rest.map(opt).join('');
  sel.value=cur || '+965';
}

document.addEventListener('DOMContentLoaded', function(){
  document.querySelectorAll('.topbar .tb-right > a.tb-btn').forEach(function(a){ a.style.display='none'; });
  var dup=document.getElementById('ccs-acc-new'); if(dup) dup.style.display='none';
  ncBuildCountries();
});

/* ─── Load form options ──────────────────────────────────── */
async function loadFormOptions() {
  const [emps, branches, settings] = await Promise.all([
    api('GET', '/employees?status=approved&slim=1'),
    api('GET', '/branches'),
    api('GET', '/settings'),
  ]);
  // Month — default to the current month in the calendar picker
  if (!document.getElementById('nc-month').value) {
    const _n = new Date(); ncSetMonth(NC_MONTHS[_n.getMonth()] + ' ' + _n.getFullYear());
  }
  // Branches
  if (branches.success) {
    const bSel = document.getElementById('nc-branch');
    bSel.innerHTML = `<option value="">${cc('opt_branch')}</option>`;
    branches.data.forEach(b => {
      const o = document.createElement('option'); o.value = b.id;
      o.textContent = b.name_ar + (b.name_en?' / '+b.name_en:''); bSel.appendChild(o);
    });
  }
  // Employees
  if (emps.success) {
    const aSel = document.getElementById('nc-agent');
    aSel.innerHTML = `<option value="">${cc('opt_agent')}</option>`;
    emps.data.forEach(e => {
      const o = document.createElement('option'); o.value = e.id; o.textContent = e.name; aSel.appendChild(o);
    });
  }
  // Settings
  if (settings.success) {
    const fill = (selId, items) => {
      const sel = document.getElementById(selId); if (!sel) return;
      sel.innerHTML = `<option value="">${cc('opt_optional')}</option>`;
      items?.forEach(item => {
        const o = document.createElement('option'); o.value = item.id;
        o.textContent = (item.name_en||'')+(item.name_ar?' / '+item.name_ar:''); sel.appendChild(o);
      });
    };
    fill('nc-acc-type',   settings.data?.account_types);
    fill('nc-trading',    settings.data?.trading_types);
  }
}

/* ─── Init ───────────────────────────────────────────────── */
(async function init() {
  ccApplyLang();
  await loadFormOptions();
  await ccLoadAll();
  const hash = window.location.hash.replace('#','') || 'accounts';
  ccShowSection(['accounts','reports','modified','monthly'].includes(hash) ? hash : 'accounts');
})();
</script>
@endpush
