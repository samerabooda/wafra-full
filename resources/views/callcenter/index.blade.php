@extends('layouts.app')
@section('title','Call Center')
@section('page-title','Call Center')

@section('topbar-actions')
<button class="tb-btn primary" id="cc-topbar-new-btn" onclick="openModal('modal-cc-create')">➕ كرت جديد</button>
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
</style>
@endpush

@section('content')

<div id="cc-shell" style="display:flex;gap:0;min-height:calc(100vh - 120px);
  background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- ── CC Sidebar Nav ── --}}
  <div id="cc-nav" style="width:220px;flex-shrink:0;background:var(--bg2);
    border-left:1px solid var(--brd1);display:flex;flex-direction:column;padding:10px 0;">

    <div style="padding:12px 16px 14px;border-bottom:1px solid var(--brd1);margin-bottom:8px">
      <div id="ccnav-hdr" style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px">
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
      display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;
      border-radius:9px;background:none;border:none;cursor:pointer;
      text-align:right;width:calc(100% - 16px);font-family:'Tajawal',sans-serif;transition:all .18s;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;
        background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);
        display:flex;align-items:center;justify-content:center;font-size:17px;"
        id="ccnav-ico-{{ $sid }}">{{ $ico }}</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap"
          id="ccnav-lbl-{{ $sid }}"></div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px"
          id="ccnav-sub-{{ $sid }}"></div>
      </div>
      <span style="display:none;font-size:9px;padding:2px 7px;border-radius:10px;
        background:rgba(245,166,35,.2);color:var(--or);font-weight:700;border:1px solid rgba(245,166,35,.3)"
        id="ccnav-badge-{{ $sid }}">0</span>
    </button>
    @endforeach

    <div style="flex:1"></div>
    <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
      <div id="ccnav-footer" style="font-size:10px;color:var(--mu);line-height:1.7"></div>
    </div>
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

      <div class="panel">
        <div class="panel-header">
          <div class="panel-title" id="ccs-acc-tbl-title"></div>
          <button class="btn btn-ghost btn-sm" onclick="ccLoadAll()">🔄</button>
        </div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th>#</th>
              <th id="cc-th-acnum"></th><th id="cc-th-month"></th>
              <th id="cc-th-branch"></th><th id="cc-th-agent"></th>
              <th id="cc-th-type"></th><th id="cc-th-status"></th>
              <th id="cc-th-act"></th>
            </tr></thead>
            <tbody id="cc-tbody">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)" id="cc-loading-cell"></td></tr>
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
      <div class="limit-hint" id="cc-modal-limit-hint"></div>
      <div id="nc-err" class="alert alert-error"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-ac"></label>
          <input type="text" id="nc-ac" class="form-control" placeholder="719750">
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-kind"></label>
          <select id="nc-kind" class="form-control">
            <option value="new">New — جديد</option>
            <option value="sub">Sub — فرعي</option>
          </select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-month"></label>
          <select id="nc-month" class="form-control"></select>
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
          <label class="form-label" id="nc-lbl-acctype"></label>
          <select id="nc-acc-type" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="nc-lbl-accstatus"></label>
          <select id="nc-acc-status" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label" id="nc-lbl-trading"></label>
          <select id="nc-trading" class="form-control"></select>
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

@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════════════════
   CC HUB — Bilingual Controller
   ══════════════════════════════════════════════════════════════ */

const CCT = {
  ar: {
    navHdr:'📞 مركز الاتصال',
    footer:'⚡ حد عمولات CC:\nبروكر + مسوّق ≤ 5$/lot',
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
    th_acnum:'رقم الحساب', th_month:'الشهر', th_branch:'الفرع المستهدف',
    th_agent:'موظف CC', th_type:'النوع', th_status:'الحالة', th_act:'الإجراء',
    loading:'جاري التحميل...', empty:'لا توجد كروت',
    btn_send:'📤 إرسال للفرع', btn_resend:'🔁 إعادة إرسال',
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
    limit_hint:'⚠️ تنبيه: عمولة البروكر + عمولة المسوّق يجب ألا تتجاوز 5$/lot عند إتمام الكرت من الفرع.',
    nc_ac:'رقم الحساب *', nc_kind:'نوع الحساب', nc_month:'الشهر *',
    nc_branch:'الفرع المستهدف *', nc_agent:'موظف CC *',
    nc_acctype:'نوع الحساب (تصنيف)', nc_accstatus:'حالة الحساب',
    nc_trading:'نوع التداول', nc_notes:'ملاحظات',
    nc_ph_notes:'معلومات إضافية للفرع...',
    nc_cancel:'إلغاء', nc_save:'💾 حفظ كمسودة',
    nc_err_required:'يرجى ملء جميع الحقول المطلوبة (رقم الحساب، الشهر، الفرع، الموظف)',
    topbar_btn:'➕ كرت جديد',
    opt_optional:'— اختياري —', opt_branch:'— اختر الفرع —', opt_agent:'— اختر الموظف —',
  },
  en: {
    navHdr:'📞 Call Center',
    footer:'⚡ CC Commission Limit:\nBroker + Marketer ≤ $5/lot',
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
    th_acnum:'Account #', th_month:'Month', th_branch:'Target Branch',
    th_agent:'CC Agent', th_type:'Type', th_status:'Status', th_act:'Action',
    loading:'Loading...', empty:'No cards found',
    btn_send:'📤 Send to Branch', btn_resend:'🔁 Resend',
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
    limit_hint:'⚠️ Note: Broker + Marketer commission must not exceed $5/lot when the branch completes the card.',
    nc_ac:'Account Number *', nc_kind:'Account Type', nc_month:'Month *',
    nc_branch:'Target Branch *', nc_agent:'CC Agent *',
    nc_acctype:'Account Type (classification)', nc_accstatus:'Account Status',
    nc_trading:'Trading Type', nc_notes:'Notes',
    nc_ph_notes:'Additional information for the branch...',
    nc_cancel:'Cancel', nc_save:'💾 Save as Draft',
    nc_err_required:'Please fill in all required fields (account number, month, branch, agent)',
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
  _t('cc-th-acnum', cc('th_acnum')); _t('cc-th-month', cc('th_month'));
  _t('cc-th-branch', cc('th_branch')); _t('cc-th-agent', cc('th_agent'));
  _t('cc-th-type', cc('th_type')); _t('cc-th-status', cc('th_status'));
  _t('cc-th-act', cc('th_act'));

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
  const lh = document.getElementById('cc-modal-limit-hint'); if (lh) lh.textContent = cc('limit_hint');
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
let _ccFilter     = '';   // active status filter for accounts section
let _ccSection    = 'accounts';
let _ccStatusChart = null;
let _ccMonChart   = null;

/* ─── Nav ────────────────────────────────────────────────── */
function ccShowSection(name) {
  document.querySelectorAll('.cc-section').forEach(s => s.style.display = 'none');
  ['accounts','reports','modified','monthly'].forEach(id => {
    const btn = document.getElementById('ccnav-' + id);
    const ico = document.getElementById('ccnav-ico-' + id);
    if (btn) { btn.style.background = 'none'; btn.style.border = 'none'; }
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
    accepted:cc('sf_acc'), completed:cc('sf_comp'), rejected:cc('sf_rej'),
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
    {v:'accepted', lk:'sf_acc'},
    {v:'completed', lk:'sf_comp'},
    {v:'rejected', lk:'sf_rej'},
  ];
  wrap.innerHTML = chips.map(ch =>
    `<button class="cc-sf${_ccFilter===ch.v?' on':''}" onclick="setFilter('${ch.v}')">${cc(ch.lk)}</button>`
  ).join('');
}
function setFilter(v) { _ccFilter = v; buildFilterChips(); ccRenderAccounts(); }

/* ─── ACCOUNTS RENDER ────────────────────────────────────── */
function ccRenderAccounts() {
  const filtered = _ccFilter ? _ccAllCards.filter(c => c.cc_status === _ccFilter) : _ccAllCards;
  const tbody    = document.getElementById('cc-tbody'); if (!tbody) return;
  if (!filtered.length) {
    tbody.innerHTML = `<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)">${cc('empty')}</td></tr>`;
    return;
  }
  tbody.innerHTML = filtered.map(c => `
    <tr class="cc-row">
      <td style="color:var(--mu);font-size:11px">${c.id}</td>
      <td><strong>${c.account_number}</strong></td>
      <td>${c.month}</td>
      <td>${c.branch?.name_ar ?? '—'}</td>
      <td>${c.cc_agent?.name ?? c.ccAgent?.name ?? '—'}</td>
      <td><span style="font-size:11px;color:var(--mu)">${c.account_kind==='sub'?cc('type_sub'):cc('type_new')}</span></td>
      <td>
        ${ccStatusChip(c.cc_status)}
        ${c.cc_rejection_reason?`<div style="font-size:10px;color:#842029;margin-top:3px;max-width:180px">${cc('reason_lbl')} ${c.cc_rejection_reason}</div>`:''}
      </td>
      <td>${ccActionButtons(c)}</td>
    </tr>`).join('');
}
function ccActionButtons(c) {
  if (c.cc_status==='cc_pending')     return `<button class="btn btn-primary btn-sm" onclick="sendCard(${c.id})">${cc('btn_send')}</button>`;
  if (c.cc_status==='branch_pending') return `<span style="font-size:11px;color:var(--mu)">${cc('status_wait_branch')}</span>`;
  if (c.cc_status==='accepted')       return `<span style="font-size:11px;color:var(--gr)">${cc('status_completing')}</span>`;
  if (c.cc_status==='rejected')       return `<button class="btn btn-warning btn-sm" onclick="resendCard(${c.id})">${cc('btn_resend')}</button>`;
  return '—';
}

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
        <td class="mono c-blue">${c.broker_commission?'$'+c.broker_commission+'/lot':'—'}</td>
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

/* ─── Create card ────────────────────────────────────────── */
async function createCard() {
  const ac     = document.getElementById('nc-ac').value.trim();
  const monthV = document.getElementById('nc-month').value;
  const branch = document.getElementById('nc-branch').value;
  const agent  = document.getElementById('nc-agent').value;
  const errEl  = document.getElementById('nc-err');
  errEl.classList.remove('show');
  if (!ac || !monthV || !branch || !agent) {
    errEl.textContent = cc('nc_err_required');
    errEl.classList.add('show'); return;
  }
  const parts = monthV.split(' ');
  const mMap  = {Jan:1,Feb:2,Mar:3,Apr:4,May:5,Jun:6,Jul:7,Aug:8,Sep:9,Oct:10,Nov:11,Dec:12};
  const mm    = String(mMap[parts[0]]??1).padStart(2,'0');
  const yyyy  = parts[1] ?? new Date().getFullYear();
  const r = await api('POST', '/cc/cards', {
    account_number:    ac, month: monthV, month_date: `${yyyy}-${mm}-01`,
    target_branch_id:  parseInt(branch), cc_agent_id: parseInt(agent),
    account_kind:      document.getElementById('nc-kind').value,
    account_type_id:   parseInt(document.getElementById('nc-acc-type').value)||null,
    account_status_id: parseInt(document.getElementById('nc-acc-status').value)||null,
    trading_type_id:   parseInt(document.getElementById('nc-trading').value)||null,
    notes:             document.getElementById('nc-notes').value.trim()||null,
  });
  if (r.success) {
    errEl.classList.remove('show');
    closeModal('modal-cc-create');
    ccShowAlert('ok', '✅ ' + r.message);
    ['nc-ac','nc-notes'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    await ccLoadAll();
  } else {
    errEl.textContent = '❌ ' + (r.errors?Object.values(r.errors).flat().join(' | '):r.message);
    errEl.classList.add('show');
  }
}

/* ─── Load form options ──────────────────────────────────── */
async function loadFormOptions() {
  const [emps, branches, settings] = await Promise.all([
    api('GET', '/employees?status=approved'),
    api('GET', '/branches'),
    api('GET', '/settings'),
  ]);
  // Months
  const mSel = document.getElementById('nc-month');
  const MONTHS = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
  const now = new Date();
  for (let i = 0; i < 24; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    const label = MONTHS[d.getMonth()] + ' ' + d.getFullYear();
    const o = document.createElement('option'); o.value = o.textContent = label; mSel.appendChild(o);
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
    fill('nc-acc-status', settings.data?.account_statuses);
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
