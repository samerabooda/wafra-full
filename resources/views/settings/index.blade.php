@extends('layouts.app')
@section('title','ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ')
@section('page-title','ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ ظˆط§ظ„ط¥ط¯ط§ط±ط©')

@section('topbar-actions')
<div id="settings-topbar-actions" style="display:flex;gap:6px"></div>
@endsection

@section('content')

<div id="settings-shell" style="
  display:flex; gap:0; min-height:calc(100vh - 120px);
  background:var(--card-bg); border:1px solid var(--card-brd);
  border-radius:16px; overflow:hidden;
">

  {{-- â”€â”€ Settings Sidebar Nav â”€â”€ --}}
  <div id="settings-nav" style="
    width:220px; flex-shrink:0;
    background:var(--bg2); border-left:1px solid var(--brd1);
    display:flex; flex-direction:column; padding:10px 0;
  ">
    <div style="padding:12px 16px 14px; border-bottom:1px solid var(--brd1); margin-bottom:8px">
      <div id="stnav-hdr" style="font-size:11px; color:var(--mu); font-weight:700; text-transform:uppercase; letter-spacing:.5px">âڑ™ï¸ڈ ظ„ظˆط­ط© ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ</div>
    </div>

    @foreach(['general','branches','employees','managers','approvals','permissions'] as $sid)
    <button onclick="showSection('{{ $sid }}')" id="snav-{{ $sid }}" style="
      display:flex; align-items:center; gap:10px;
      padding:10px 14px; margin:1px 8px; border-radius:9px;
      background:none; border:none; cursor:pointer;
      text-align:right; width:calc(100% - 16px);
      font-family:'Tajawal',sans-serif; transition:all .18s;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;
        background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);
        display:flex;align-items:center;justify-content:center;font-size:17px;"
        id="snav-ico-{{ $sid }}"></div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap" id="snav-lbl-{{ $sid }}"></div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px" id="snav-sub-{{ $sid }}"></div>
      </div>
      <span style="display:none;font-size:9px;padding:2px 7px;border-radius:10px;
        background:rgba(245,166,35,.2);color:var(--or);font-weight:700;border:1px solid rgba(245,166,35,.3)"
        id="snav-badge-{{ $sid }}">0</span>
    </button>
    @endforeach

    <div style="flex:1"></div>
    <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
      <div id="st-footer-note" style="font-size:10px;color:var(--mu);line-height:1.6"></div>
    </div>
  </div>

  {{-- â”€â”€ Settings Content Panel â”€â”€ --}}
  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

    {{-- GENERAL --}}
    <div id="section-general" class="settings-section" style="display:none">
      <div style="margin-bottom:20px">
        <h2 id="gen-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
        <p  id="gen-p"  style="font-size:12px;color:var(--mu)"></p>
      </div>
      @if(!auth()->user()?->isFinanceAdmin())
      <div class="alert alert-warning show" id="gen-admin-only" style="margin-bottom:16px"></div>
      @endif
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="sgd">
        @foreach([
          ['account_types',    'account-types',    'gen_acTypes'],
          ['account_statuses', 'account-statuses', 'gen_acStatuses'],
          ['trading_types',    'trading-types',    'gen_trTypes'],
        ] as [$key,$ep,$lk])
        <div class="panel">
          <div class="panel-header">
            <div class="panel-title" id="pt-{{ $key }}"></div>
            <span style="font-size:10px;color:var(--mu)" id="cnt-{{ $key }}">â€”</span>
          </div>
          <div id="list-{{ $key }}" style="max-height:220px;overflow-y:auto;padding:8px 16px"></div>
          @if(auth()->user()?->isFinanceAdmin())
          <div style="padding:10px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px">
            <input type="text" id="new-en-{{ $key }}" class="form-control" placeholder="English" style="flex:1">
            <input type="text" id="new-ar-{{ $key }}" class="form-control" placeholder="ط¹ط±ط¨ظٹ" style="flex:1">
            <button class="btn btn-primary btn-sm" id="addbtn-{{ $key }}"
              onclick="addLookup('{{ $ep }}','{{ $key }}')"></button>
          </div>
          @endif
        </div>
        @endforeach

        <div class="panel">
          <div class="panel-header"><div class="panel-title" id="gen-sysinfo-title"></div></div>
          <div class="panel-body">
            <div style="display:flex;flex-direction:column;gap:12px">
              @foreach([
                ['gen_company',     'company.full',    'span', 'ظˆظپط±ط© ط§ظ„ط®ظ„ظٹط¬ظٹط© ظ„ظ„ط®ط¯ظ…ط§طھ ط§ظ„ظ…ط§ظ„ظٹط©'],
                ['gen_version',     null,              'badge','v2.0'],
                ['gen_status',      null,              'active',null],
                ['gen_currentUser', null,              'user',  null],
              ] as [$lk,$i18,$type,$val])
              <div style="display:flex;justify-content:space-between;align-items:center;padding:10px;background:var(--bg3);border-radius:9px;border:1px solid var(--brd1)">
                <span id="lbl-{{ $lk }}" style="font-size:12px;color:var(--mu)"></span>
                @if($type==='span')
                  <span style="font-size:12px;font-weight:700" data-i18n="{{ $i18 }}">{{ $val }}</span>
                @elseif($type==='badge')
                  <span class="badge badge-blue">v2.0</span>
                @elseif($type==='active')
                  <span class="badge badge-green" id="gen-active-badge"></span>
                @else
                  <span style="font-size:12px;font-weight:700" id="gen-current-user">â€”</span>
                @endif
              </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>
    </div>

    {{-- BRANCHES --}}
    <div id="section-branches" class="settings-section" style="display:none">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
          <h2 id="br-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
          <p  id="br-p"  style="font-size:12px;color:var(--mu)"></p>
        </div>
        @if(auth()->user()?->isFinanceAdmin())
        <button class="btn btn-primary" id="br-new-btn" onclick="openModal('modal-add-branch')"></button>
        @endif
      </div>
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span id="br-list-title"></span>
            <span id="br-count" class="badge badge-blue" style="margin-right:6px">â€”</span>
          </div>
          <button class="btn btn-ghost btn-sm" id="br-refresh-btn" onclick="loadBranchesSection()"></button>
        </div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="br-th-code"></th><th id="br-th-nar"></th><th id="br-th-nen"></th>
              <th id="br-th-cards"></th><th id="br-th-date"></th><th id="br-th-act"></th>
            </tr></thead>
            <tbody id="br-tbody">
              <tr><td colspan="6" style="text-align:center;padding:40px;color:var(--mu)" id="br-loading-cell"></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- EMPLOYEES --}}
    <div id="section-employees" class="settings-section" style="display:none">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
          <h2 id="emp-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
          <p  id="emp-p"  style="font-size:12px;color:var(--mu)"></p>
        </div>
        <button class="btn btn-primary" id="emp-new-btn" onclick="openModal('modal-add-emp')"></button>
      </div>
      @if(auth()->user()?->isFinanceAdmin())
      <div id="emp-pending-banner" class="alert alert-warning" style="display:none;margin-bottom:14px">
        âڈ³ <span id="emp-pending-text"></span>
        <button onclick="showSection('approvals')" id="emp-approve-now-btn"
          style="background:none;border:none;color:var(--or);font-weight:700;cursor:pointer;margin-right:8px;font-family:'Tajawal',sans-serif"></button>
      </div>
      @endif
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span id="emp-list-title"></span>
            <span id="emp-count-badge" style="font-size:11px;color:var(--mu)"></span>
          </div>
          <div style="display:flex;gap:8px">
            <select id="emp-f-role" class="form-control" style="width:auto;font-size:12px;padding:5px 9px" onchange="loadEmployeesSection()"></select>
            <button class="btn btn-ghost btn-sm" onclick="loadEmployeesSection()">ًں”„</button>
          </div>
        </div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="emp-th-name"></th><th id="emp-th-role"></th><th id="emp-th-branch"></th>
              <th id="emp-th-bc"></th><th id="emp-th-mc"></th>
              <th id="emp-th-status"></th><th id="emp-th-addedby"></th><th id="emp-th-act"></th>
            </tr></thead>
            <tbody id="emp-tbody">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)" id="emp-loading-cell"></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- MANAGERS --}}
    <div id="section-managers" class="settings-section" style="display:none">
      <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px">
        <div>
          <h2 id="mgr-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
          <p  id="mgr-p"  style="font-size:12px;color:var(--mu)"></p>
        </div>
        @if(auth()->user()?->isFinanceAdmin())
        <div style="display:flex;gap:8px">
          <button class="btn btn-primary" id="mgr-new-btn"    onclick="openModal('modal-add-mgr')"></button>
          <button class="btn btn-ghost"   id="mgr-invite-btn" onclick="openModal('modal-add-invite')"></button>
        </div>
        @endif
      </div>
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title" id="mgr-list-title"></div>
          <button class="btn btn-ghost btn-sm" id="mgr-refresh-btn" onclick="loadManagersSection()"></button>
        </div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="mgr-th-name"></th><th id="mgr-th-email"></th><th id="mgr-th-phone"></th>
              <th id="mgr-th-branch"></th><th id="mgr-th-role"></th><th id="mgr-th-login"></th>
              <th id="mgr-th-status"></th><th id="mgr-th-act"></th>
            </tr></thead>
            <tbody id="mgr-tbody">
              <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)" id="mgr-loading-cell"></td></tr>
            </tbody>
          </table>
        </div>
      </div>
      <div class="panel">
        <div class="panel-header" style="display:flex;align-items:center;justify-content:space-between">
          <div class="panel-title" id="inv-list-title"></div>
          @if(auth()->user()?->isFinanceAdmin())
          <button class="btn btn-primary btn-sm" id="inv-new-btn" onclick="openModal('modal-add-invite')"></button>
          @endif
        </div>
        <div style="padding:10px 16px;font-size:12px;color:var(--mu);border-bottom:1px solid var(--brd1)" id="inv-desc"></div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="inv-th-email"></th><th id="inv-th-branch"></th><th id="inv-th-role"></th>
              <th id="inv-th-note"></th><th id="inv-th-status"></th>
              <th id="inv-th-date"></th><th id="inv-th-act"></th>
            </tr></thead>
            <tbody id="inv-tbody">
              <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)" id="inv-loading-cell"></td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- APPROVALS --}}
    <div id="section-approvals" class="settings-section" style="display:none">
      <div style="margin-bottom:20px">
        <h2 id="appr-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
        <p  id="appr-p"  style="font-size:12px;color:var(--mu)"></p>
      </div>
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title">
            <span id="appr-list-title"></span>
            <span id="appr-count-badge" class="badge badge-orange" style="display:none;margin-right:8px">0</span>
          </div>
          <button class="btn btn-ghost btn-sm" id="appr-refresh-btn" onclick="loadApprovalsSection()"></button>
        </div>
        <div id="appr-list" class="panel-body">
          <div style="text-align:center;padding:30px;color:var(--mu)" id="appr-loading-cell">
            <div style="font-size:38px;opacity:.25;margin-bottom:10px">âœ…</div>
          </div>
        </div>
      </div>
      <div class="panel" style="margin-top:8px">
        <div class="panel-header"><div class="panel-title" id="appr-hist-title"></div></div>
        <div class="table-scroll">
          <table class="data-table">
            <thead><tr>
              <th id="appr-th-emp"></th><th id="appr-th-role"></th><th id="appr-th-branch"></th>
              <th id="appr-th-decision"></th><th id="appr-th-by"></th>
            </tr></thead>
            <tbody id="appr-history">
              <tr><td colspan="5" style="text-align:center;padding:20px;color:var(--mu)">â€”</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    {{-- PERMISSIONS --}}
    <div id="section-permissions" class="settings-section" style="display:none">
      <div style="margin-bottom:20px">
        <h2 id="perm-h2" style="font-size:18px;font-weight:800;margin-bottom:4px"></h2>
        <p  id="perm-p"  style="font-size:12px;color:var(--mu)"></p>
      </div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px" class="sgd">
        <div class="panel">
          <div class="panel-header"><div class="panel-title" id="perm-fa-title"></div></div>
          <div class="panel-body" id="perm-fa-list"></div>
        </div>
        <div class="panel">
          <div class="panel-header"><div class="panel-title" id="perm-bm-title"></div></div>
          <div class="panel-body" id="perm-bm-list"></div>
        </div>
      </div>
    </div>{{-- section-permissions --}}

    {{-- Guide section moved to /guide (standalone page) --}}

  </div>{{-- content panel --}}
</div>{{-- shell --}}

{{-- â•گâ•گâ•گ MODALS â•گâ•گâ•گ --}}

{{-- Branch Modal --}}
<div class="modal-overlay" id="modal-add-branch">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <div class="modal-title" id="br-modal-title"></div>
      <button class="modal-close" onclick="closeModal('modal-add-branch')">âœ•</button>
    </div>
    <div class="modal-body">
      <div id="br-err" class="alert alert-error"></div>
      <div id="br-ok"  class="alert alert-success"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="br-lbl-ar"></label>
          <input type="text" id="br-ar" class="form-control auto-lang" lang="ar" placeholder="">
        </div>
        <div class="form-group">
          <label class="form-label" id="br-lbl-en"></label>
          <input type="text" id="br-en" class="form-control" placeholder="" dir="ltr" lang="en">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" id="br-lbl-code"></label>
        <input type="text" id="br-code" class="form-control" placeholder="B001" style="font-family:monospace">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   id="br-cancel-btn" onclick="closeModal('modal-add-branch')"></button>
      <button class="btn btn-primary" id="br-submit-btn" onclick="addBranch()"></button>
    </div>
  </div>
</div>

{{-- Employee Modal --}}
<div class="modal-overlay" id="modal-add-emp">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <div class="modal-title" id="ae-modal-title"></div>
      <button class="modal-close" onclick="closeModal('modal-add-emp')">âœ•</button>
    </div>
    <div class="modal-body">
      <div id="emp-err" class="alert alert-error"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="ae-lbl-name"></label>
          <input type="text" id="ae-name" class="form-control auto-lang" lang="ar" placeholder="Ahmed Al-Sayed">
        </div>
        <div class="form-group">
          <label class="form-label" id="ae-lbl-role"></label>
          <select id="ae-role" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="ae-lbl-bc"></label>
          <input type="number" id="ae-bc" class="form-control" value="4" min="0" step="0.5">
        </div>
        <div class="form-group">
          <label class="form-label" id="ae-lbl-mc"></label>
          <input type="number" id="ae-mc" class="form-control" value="3" min="0" step="0.5">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="ae-lbl-email"></label>
          <input type="email" id="ae-email" class="form-control" placeholder="employee@wafragulf.com" dir="ltr" lang="en" inputmode="email">
        </div>
        <div class="form-group">
          <label class="form-label" id="ae-lbl-branch"></label>
          <select id="ae-branch" class="form-control"></select>
        </div>
      </div>
      @if(auth()->user()?->isBranchManager())
      <div class="alert alert-info show" id="ae-pending-note"></div>
      @endif
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   id="ae-cancel-btn" onclick="closeModal('modal-add-emp')"></button>
      <button class="btn btn-primary" id="ae-submit-btn" onclick="addEmployee()"></button>
    </div>
  </div>
</div>

{{-- Manager Modal --}}
<div class="modal-overlay" id="modal-add-mgr">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title" id="mg-modal-title"></div>
      <button class="modal-close" onclick="closeModal('modal-add-mgr')">âœ•</button>
    </div>
    <div class="modal-body">
      <div id="mgr-err" class="alert alert-error"></div>
      <div id="mgr-ok"  class="alert alert-success"></div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mg-lbl-name"></label>
          <input type="text"  id="mg-name"  class="form-control auto-lang" lang="ar" placeholder="Manager Name">
        </div>
        <div class="form-group">
          <label class="form-label" id="mg-lbl-email"></label>
          <input type="email" id="mg-email" class="form-control" placeholder="manager@wafragulf.com" dir="ltr" lang="en" inputmode="email">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mg-lbl-phone"></label>
          <div style="display:flex;gap:6px">
            <select id="mg-phone-code" class="form-control" style="width:165px;flex-shrink:0;direction:ltr;text-align:left;padding:10px 6px;font-size:13px"></select>
            <input type="tel" id="mg-phone-num" class="form-control" placeholder="5XXXXXXXX" dir="ltr" inputmode="tel" style="flex:1;min-width:0"></div>
        </div>
        <div class="form-group">
          <label class="form-label" id="mg-lbl-branch"></label>
          <select id="mg-branch" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="mg-lbl-pw"></label>
          <input type="password" id="mg-pw" class="form-control" placeholder="â€”">
        </div>
        <div class="form-group"></div>
      </div>
      <div class="form-section-title" id="mg-perms-title" style="margin-top:16px"></div>
      <div style="display:flex;gap:8px;margin-bottom:12px">
        <button class="btn btn-ghost btn-sm" id="mg-sel-all"  onclick="selectAllPerms(true)"></button>
        <button class="btn btn-ghost btn-sm" id="mg-clr-all"  onclick="selectAllPerms(false)"></button>
      </div>
      <div id="perm-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   id="mg-cancel-btn" onclick="closeModal('modal-add-mgr')"></button>
      <button class="btn btn-primary" id="mg-submit-btn" onclick="createManager()"></button>
    </div>
  </div>
</div>

{{-- Edit Manager Modal --}}
<div class="modal-overlay" id="modal-edit-mgr">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title" id="emg-modal-title">âœڈï¸ڈ طھط¹ط¯ظٹظ„ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ط¯ظٹط±</div>
      <button class="modal-close" onclick="closeModal('modal-edit-mgr')">âœ•</button>
    </div>
    <div class="modal-body">
      <div id="emg-err" class="alert alert-error"></div>
      <div id="emg-ok"  class="alert alert-success"></div>
      <input type="hidden" id="emg-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="emg-lbl-name"></label>
          <input type="text" id="emg-name" class="form-control auto-lang" lang="ar" placeholder="Manager Name">
        </div>
        <div class="form-group">
          <label class="form-label" id="emg-lbl-phone"></label>
          <div style="display:flex;gap:6px">
            <select id="emg-phone-code" class="form-control" style="width:165px;flex-shrink:0;direction:ltr;text-align:left;padding:10px 6px;font-size:13px"></select>
            <input type="tel" id="emg-phone-num" class="form-control" placeholder="5XXXXXXXX" dir="ltr" inputmode="tel" style="flex:1;min-width:0"></div>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="emg-lbl-branch"></label>
          <select id="emg-branch" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label" id="emg-lbl-active" style="margin-bottom:14px"></label>
          <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 14px;background:var(--bg4);border-radius:9px;border:1px solid var(--brd1)">
            <input type="checkbox" id="emg-active" style="width:18px;height:18px;cursor:pointer;accent-color:var(--pri)">
            <span id="emg-active-lbl" style="font-size:14px;font-weight:600;color:var(--gr)">ظ†ط´ط·</span>
          </label>
        </div>
      </div>
      <div class="form-section-title" id="emg-perms-title" style="margin-top:8px">ًں”گ ط§ظ„طµظ„ط§ط­ظٹط§طھ ط§ظ„ظ…ظ…ظ†ظˆط­ط©</div>
      <div style="display:flex;gap:8px;margin-bottom:10px">
        <button class="btn btn-ghost btn-sm" onclick="selectAllPermsEdit(true)">âœ… طھط­ط¯ظٹط¯ ط§ظ„ظƒظ„</button>
        <button class="btn btn-ghost btn-sm" onclick="selectAllPermsEdit(false)">âکگ ط¥ظ„ط؛ط§ط، ط§ظ„ظƒظ„</button>
      </div>
      <div id="perm-grid-edit" style="display:grid;grid-template-columns:1fr 1fr;gap:8px"></div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   id="emg-cancel-btn" onclick="closeModal('modal-edit-mgr')"></button>
      <button class="btn btn-primary" id="emg-save-btn"   onclick="saveManagerEdit()"></button>
    </div>
  </div>
</div>

{{-- Edit Employee Modal --}}
<div class="modal-overlay" id="modal-edit-emp">
  <div class="modal modal-wide">
    <div class="modal-header">
      <div class="modal-title" id="eep-modal-title">âœڈï¸ڈ طھط¹ط¯ظٹظ„ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظˆط¸ظپ</div>
      <button class="modal-close" onclick="closeModal('modal-edit-emp')">âœ•</button>
    </div>
    <div class="modal-body">
      <div id="eep-err" class="alert alert-error"></div>
      <div id="eep-ok"  class="alert alert-success"></div>
      <input type="hidden" id="eep-id">
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="eep-lbl-name"></label>
          <input type="text" id="eep-name" class="form-control auto-lang" lang="ar" placeholder="Employee Name">
        </div>
        <div class="form-group">
          <label class="form-label" id="eep-lbl-email"></label>
          <input type="email" id="eep-email" class="form-control" placeholder="employee@wafragulf.com" dir="ltr" lang="en" inputmode="email">
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="eep-lbl-role"></label>
          <select id="eep-role" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label" id="eep-lbl-branch"></label>
          <select id="eep-branch" class="form-control"></select>
        </div>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="eep-lbl-bc"></label>
          <input type="number" id="eep-bc" class="form-control" min="0" step="0.5">
        </div>
        <div class="form-group">
          <label class="form-label" id="eep-lbl-mc"></label>
          <input type="number" id="eep-mc" class="form-control" min="0" step="0.5">
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" id="eep-lbl-active" style="margin-bottom:10px"></label>
        <label style="display:flex;align-items:center;gap:10px;cursor:pointer;padding:10px 14px;background:var(--bg4);border-radius:9px;border:1px solid var(--brd1);width:fit-content">
          <input type="checkbox" id="eep-active" style="width:18px;height:18px;cursor:pointer;accent-color:var(--pri)">
          <span id="eep-active-lbl" style="font-size:14px;font-weight:600;color:var(--gr)">ظ†ط´ط·</span>
        </label>
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   id="eep-cancel-btn" onclick="closeModal('modal-edit-emp')"></button>
      <button class="btn btn-primary" id="eep-save-btn"   onclick="saveEmployeeEdit()"></button>
    </div>
  </div>
</div>

{{-- Invite Modal --}}
<div class="modal-overlay" id="modal-add-invite">
  <div class="modal modal-narrow">
    <div class="modal-header">
      <div class="modal-title" id="inv-modal-title"></div>
      <button class="modal-close" onclick="closeModal('modal-add-invite')">âœ•</button>
    </div>
    <div class="modal-body">
      <div class="alert alert-info show" id="inv-modal-info" style="margin-bottom:14px"></div>
      <div id="inv-err-modal" class="alert alert-error"></div>
      <div id="inv-ok-modal"  class="alert alert-success"></div>
      <div class="form-group">
        <label class="form-label" id="inv-lbl-email"></label>
        <input type="email" id="inv-email-input" class="form-control" placeholder="manager@example.com">
      </div>
      <div class="form-row">
        <div class="form-group">
          <label class="form-label" id="inv-lbl-branch"></label>
          <select id="inv-branch-input" class="form-control"></select>
        </div>
        <div class="form-group">
          <label class="form-label" id="inv-lbl-role"></label>
          <select id="inv-role-input" class="form-control"></select>
        </div>
      </div>
      <div class="form-group">
        <label class="form-label" id="inv-lbl-note"></label>
        <input type="text" id="inv-note-input" class="form-control" placeholder="">
      </div>
    </div>
    <div class="modal-footer">
      <button class="btn btn-ghost"   id="inv-cancel-btn" onclick="closeModal('modal-add-invite')"></button>
      <button class="btn btn-primary" id="inv-submit-btn" onclick="addInvite()"></button>
    </div>
  </div>
</div>

{{-- (duplicate removed) --}}

  {{-- Workflow Steps --}}
  <div style="display:flex;flex-direction:column;gap:14px">

    {{-- Step 1 --}}
    <div style="display:flex;gap:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;padding:18px;align-items:flex-start">
      <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.08));border:1px solid rgba(26,173,186,.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">ًں—‚</div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:800;color:var(--pri2);margin-bottom:6px">1. ط¥ط¶ط§ظپط© ظƒط±طھ ط¹ظ…ظˆظ„ط© ط¬ط¯ظٹط¯</div>
        <div style="font-size:13px;color:var(--tx);line-height:1.7">
          ط§ط¶ط؛ط· ط¹ظ„ظ‰ <strong>â‍• ظƒط±طھ ط¬ط¯ظٹط¯</strong> ظپظٹ ط£ط¹ظ„ظ‰ ط§ظ„ط´ط§ط´ط© ط£ظˆ ظ…ظ† ط§ظ„ظ‚ط§ط¦ظ…ط© ط§ظ„ط¬ط§ظ†ط¨ظٹط©.<br>
          ط£ط¯ط®ظ„ ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨طŒ ط§ظ„ط´ظ‡ط±طŒ ط§ظ„ط¨ط±ظˆظƒط±طŒ ط§ظ„ظ…ط³ظˆظ‘ظ‚طŒ ظ‚ظٹظ…ط© ط§ظ„ط¥ظٹط¯ط§ط¹طŒ ظˆظ†ظˆط¹ ط§ظ„ط­ط³ط§ط¨ (NEW / SUB).<br>
          ط§ط¶ط؛ط· <strong>ط­ظپط¸</strong> â€” ط³ظٹط¸ظ‡ط± ط§ظ„ظƒط±طھ ظپظٹ ظ‚ط§ط¦ظ…ط© ظƒط±ظˆطھ ط§ظ„ط¹ظ…ظˆظ„ط§طھ ظپظˆط±ط§ظ‹.
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <span class="badge badge-blue">â‍• ظƒط±طھ ط¬ط¯ظٹط¯</span>
          <span class="badge badge-green">ط­ظ‚ظ„: ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨</span>
          <span class="badge badge-green">ط­ظ‚ظ„: ط§ظ„ط´ظ‡ط±</span>
          <span class="badge badge-green">ط­ظ‚ظ„: ط§ظ„ط¨ط±ظˆظƒط±</span>
        </div>
      </div>
    </div>

    {{-- Step 2 --}}
    <div style="display:flex;gap:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;padding:18px;align-items:flex-start">
      <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(245,166,35,.2),rgba(245,166,35,.08));border:1px solid rgba(245,166,35,.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">âœڈï¸ڈ</div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:800;color:var(--or);margin-bottom:6px">2. طھط¹ط¯ظٹظ„ ظƒط±طھ ظ…ظˆط¬ظˆط¯</div>
        <div style="font-size:13px;color:var(--tx);line-height:1.7">
          ط§ط¶ط؛ط· ط¹ظ„ظ‰ <strong>âœڈï¸ڈ طھط¹ط¯ظٹظ„</strong> ظپظٹ ط´ط±ظٹط· ط§ظ„ط£ط¯ظˆط§طھ ط§ظ„ط¹ظ„ظˆظٹطŒ ط£ظˆ ط§ط¨ط­ط« ظپظٹ <strong>ظƒط±ظˆطھ ط§ظ„ط¹ظ…ظˆظ„ط§طھ</strong>.<br>
          ط£ط¯ط®ظ„ ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨ â†’ ط§ط®طھط± ط§ظ„ظƒط±طھ â†’ ط¹ط¯ظ‘ظ„ ط§ظ„ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ط·ظ„ظˆط¨ط© â†’ ط§ط®طھط± <strong>ط³ط¨ط¨ ط§ظ„طھط¹ط¯ظٹظ„</strong>.<br>
          ط§ظ„طھط¹ط¯ظٹظ„ ظٹظڈط³ط¬ظژظ‘ظ„ طھظ„ظ‚ط§ط¦ظٹط§ظ‹ ظپظٹ ط³ط¬ظ„ ط§ظ„طھط¯ظ‚ظٹظ‚ ظ…ط¹ ط§ط³ظ… ط§ظ„ظ…ط¹ط¯ظ‘ظ„ ظˆط§ظ„طھط§ط±ظٹط®.
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <span class="badge badge-orange">âœڈï¸ڈ طھط¹ط¯ظٹظ„</span>
          <span class="badge badge-orange">ط³ط¬ظ„ ط§ظ„طھط¹ط¯ظٹظ„ط§طھ â€” ظ„ظˆط­ط© ط§ظ„ظ…طھط§ط¨ط¹ط©</span>
        </div>
      </div>
    </div>

    {{-- Step 3 --}}
    <div style="display:flex;gap:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;padding:18px;align-items:flex-start">
      <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(34,201,122,.2),rgba(34,201,122,.08));border:1px solid rgba(34,201,122,.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">ًں“¥</div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:800;color:var(--gr);margin-bottom:6px">3. ط§ط³طھظٹط±ط§ط¯ ط¨ظٹط§ظ†ط§طھ ط¬ظ…ط§ط¹ظٹ (Excel)</div>
        <div style="font-size:13px;color:var(--tx);line-height:1.7">
          ظ…ظ† ط§ظ„ظ‚ط§ط¦ظ…ط© ط§ظ„ط¬ط§ظ†ط¨ظٹط© â†’ <strong>ًں“¥ ط§ط³طھظٹط±ط§ط¯ ط¨ظٹط§ظ†ط§طھ</strong> (ظ„ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ ظپظ‚ط·).<br>
          ط§ط±ظپط¹ ظ…ظ„ظپ Excel ط¨ط§ظ„ط¨ظٹط§ظ†ط§طھ â€” ط§ظ„ظ†ط¸ط§ظ… ظٹظ‚ط±ط£ ط§ظ„طµظپظˆظپ طھظ„ظ‚ط§ط¦ظٹط§ظ‹ ظˆظٹط¹ط±ط¶ ظ…ط¹ط§ظٹظ†ط©.<br>
          طھط£ظƒط¯ ظ…ظ† ظ…ط·ط§ط¨ظ‚ط© ط§ظ„ط£ط¹ظ…ط¯ط© ط«ظ… ط§ط¶ط؛ط· <strong>ط±ظپط¹ ظˆط§ط³طھظٹط±ط§ط¯</strong>.<br>
          ظ„ط§ ظٹظˆط¬ط¯ ط­ط¯ ط£ظ‚طµظ‰ ظ„ط¹ط¯ط¯ ط§ظ„طµظپظˆظپ â€” ظٹظ…ظƒظ† ط±ظپط¹ ط£ظٹ ط¹ط¯ط¯ ظپظٹ ظ…ظ„ظپ ظˆط§ط­ط¯.
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <span class="badge badge-green">ظ„ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ ظپظ‚ط·</span>
          <span class="badge badge-blue">ط£ط¹ظ…ط¯ط©: ط±ظ‚ظ… ط§ظ„ط­ط³ط§ط¨طŒ ط§ظ„ط´ظ‡ط±طŒ ط§ظ„ط¨ط±ظˆظƒط±طŒ ...</span>
        </div>
      </div>
    </div>

    {{-- Step 4 --}}
    <div style="display:flex;gap:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;padding:18px;align-items:flex-start">
      <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(138,120,240,.2),rgba(138,120,240,.08));border:1px solid rgba(138,120,240,.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">ًں“‍</div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:800;color:var(--pu);margin-bottom:6px">4. ظƒط±ظˆطھ ظ…ط±ظƒط² ط§ظ„ط§طھطµط§ظ„ (CC)</div>
        <div style="font-size:13px;color:var(--tx);line-height:1.7">
          ط§ظ„ظƒط±ظˆطھ ط§ظ„ظˆط§ط±ط¯ط© ظ…ظ† ظ…ط±ظƒط² ط§ظ„ط§طھطµط§ظ„ طھط¸ظ‡ط± ظپظٹ <strong>ًں“‍ ظ…ط±ظƒط² ط§ظ„ط§طھطµط§ظ„</strong>.<br>
          ظ…ط¯ظٹط±ظˆ ط§ظ„ظپط±ظˆط¹ ظٹط±ظˆظ† ظپظ‚ط· ط§ظ„ظƒط±ظˆطھ ط§ظ„ظˆط§ط±ط¯ط© ظ„ظپط±ط¹ظ‡ظ….<br>
          ظٹظ…ظƒظ† ظ‚ط¨ظˆظ„ ط§ظ„ظƒط±طھ (ظٹظ†طھظ‚ظ„ ظ„ظ‚ط§ط¦ظ…ط© ظƒط±ظˆطھ ط§ظ„ط¹ظ…ظˆظ„ط§طھ) ط£ظˆ ط±ظپط¶ظ‡ ظ…ط¹ ط°ظƒط± ط§ظ„ط³ط¨ط¨.
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <span class="badge badge-purple">CC â€” ظ…ط±ظƒط² ط§ظ„ط§طھطµط§ظ„</span>
          <span class="badge badge-purple">ظ‚ط¨ظˆظ„ / ط±ظپط¶</span>
        </div>
      </div>
    </div>

    {{-- Step 5 --}}
    <div style="display:flex;gap:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;padding:18px;align-items:flex-start">
      <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.08));border:1px solid rgba(26,173,186,.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">ًں“ˆ</div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:800;color:var(--pri2);margin-bottom:6px">5. ط§ظ„طھظ‚ط§ط±ظٹط± ظˆط§ظ„ط¥ط­طµط§ط¦ظٹط§طھ</div>
        <div style="font-size:13px;color:var(--tx);line-height:1.7">
          <strong>ًں“ˆ ط§ظ„طھظ‚ط§ط±ظٹط±</strong>: طھظ‚ط±ظٹط± ط´ط§ظ…ظ„ ط¨ظƒظ„ ط§ظ„ط­ط³ط§ط¨ط§طھ ظ…ط¹ ظپظ„ط§طھط± ط§ظ„ط´ظ‡ط± / ط§ظ„ظپط±ط¹ / ط§ظ„ط¨ط±ظˆظƒط±.<br>
          <strong>طھظ‚ط±ظٹط± ط¯ظٹظ†ط§ظ…ظٹظƒظٹ</strong>: طھط¬ظ…ظٹط¹ ط­ط³ط¨ ط§ظ„ط¨ط±ظˆظƒط± ط£ظˆ ط§ظ„ظ…ط³ظˆظ‘ظ‚ ط£ظˆ ط§ظ„ظپط±ط¹ ط£ظˆ ط§ظ„ط´ظ‡ط±.<br>
          <strong>ظ„ظˆط­ط© ط§ظ„ظ…طھط§ط¨ط¹ط©</strong>: ظ…ظ„ط®طµ ط³ط±ظٹط¹ ظ„ظ„ط£ط±ظ‚ط§ظ… ط§ظ„ط±ط¦ظٹط³ظٹط© ظˆطھط±طھظٹط¨ ط§ظ„ط¨ط±ظˆظƒط±ط§طھ.
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <span class="badge badge-blue">ًں“„ طھطµط¯ظٹط± PDF</span>
          <span class="badge badge-green">ًں“— طھطµط¯ظٹط± Excel</span>
        </div>
      </div>
    </div>

    {{-- Step 6 --}}
    <div style="display:flex;gap:16px;background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;padding:18px;align-items:flex-start">
      <div style="width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,rgba(26,173,186,.2),rgba(26,173,186,.08));border:1px solid rgba(26,173,186,.3);display:flex;align-items:center;justify-content:center;font-size:22px;flex-shrink:0">ًں‘¥</div>
      <div style="flex:1">
        <div style="font-size:14px;font-weight:800;color:var(--pri2);margin-bottom:6px">6. ط¥ط¯ط§ط±ط© ط§ظ„ظ…ظˆط¸ظپظٹظ† ظˆط§ظ„ظ…ط¯ظٹط±ظٹظ†</div>
        <div style="font-size:13px;color:var(--tx);line-height:1.7">
          <strong>ط§ظ„ظ…ظˆط¸ظپظˆظ†</strong>: ط¥ط¶ط§ظپط© ط¨ط±ظˆظƒط±ط§طھ ظˆظ…ط³ظˆظ‘ظ‚ظٹظ† â€” طھط­طھط§ط¬ ظ…ظˆط§ظپظ‚ط© ظ…ط¯ظٹط± ظ…ط§ظ„ظٹ.<br>
          <strong>ط§ظ„ظ…ط¯ظٹط±ظˆظ†</strong>: ط¥ط¶ط§ظپط© ظ…ط¯ظٹط±ظٹ ظپط±ظˆط¹ ظˆطھط­ط¯ظٹط¯ ط§ظ„ظپط±ط¹ ظˆط§ظ„طµظ„ط§ط­ظٹط§طھ (ظ„ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ ظپظ‚ط·).<br>
          <strong>ط§ظ„طµظ„ط§ط­ظٹط§طھ</strong>: طھط­ظƒظ… ط¯ظ‚ظٹظ‚ ظپظٹ ظ…ط§ ظٹظ…ظƒظ† ظ„ظƒظ„ ظ…ط¯ظٹط± ظپط±ط¹ ط±ط¤ظٹطھظ‡ ط£ظˆ طھط¹ط¯ظٹظ„ظ‡.
        </div>
        <div style="margin-top:10px;display:flex;gap:8px;flex-wrap:wrap">
          <span class="badge badge-blue">ظ…ظˆط¸ظپ ط¬ط¯ظٹط¯ â†’ ظ…ط±ط§ط¬ط¹ط© â†’ ظ…ظˆط§ظپظ‚ط©</span>
          <span class="badge badge-orange">ط§ظ„طµظ„ط§ط­ظٹط§طھ: ط¨ظ†ط§ط،ظ‹ ط¹ظ„ظ‰ ط§ظ„ط¯ظˆط±</span>
        </div>
      </div>
    </div>

    {{-- Roles summary --}}
    <div style="background:linear-gradient(135deg,rgba(26,173,186,.06),rgba(26,173,186,.02));border:1px solid rgba(26,173,186,.2);border-radius:14px;padding:18px">
      <div style="font-size:14px;font-weight:800;color:var(--pri2);margin-bottom:14px">ًں”گ ظ…ظ„ط®طµ ط§ظ„ط£ط¯ظˆط§ط± ظˆط§ظ„طµظ„ط§ط­ظٹط§طھ</div>
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px" class="sgd">
        <div style="background:var(--bg3);border:1px solid var(--brd1);border-radius:10px;padding:14px">
          <div style="font-size:13px;font-weight:800;margin-bottom:8px">ًں’¼ ط§ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ</div>
          <div style="font-size:12px;color:var(--mu);line-height:1.8">
            âœ… ط¬ظ…ظٹط¹ ط§ظ„طµظپط­ط§طھ<br>
            âœ… ط§ط³طھظٹط±ط§ط¯ / طھطµط¯ظٹط±<br>
            âœ… ط¥ط¯ط§ط±ط© ط§ظ„ظ…ط¯ظٹط±ظٹظ†<br>
            âœ… ط¥ط¯ط§ط±ط© ط§ظ„ظ…ظˆط¸ظپظٹظ†<br>
            âœ… ط¥ط¹ط¯ط§ط¯ط§طھ ط§ظ„ظ†ط¸ط§ظ…
          </div>
        </div>
        <div style="background:var(--bg3);border:1px solid var(--brd1);border-radius:10px;padding:14px">
          <div style="font-size:13px;font-weight:800;margin-bottom:8px">ًںڈ¢ ظ…ط¯ظٹط± ط§ظ„ظپط±ط¹</div>
          <div style="font-size:12px;color:var(--mu);line-height:1.8">
            âœ… ط¨ظٹط§ظ†ط§طھ ظپط±ط¹ظ‡ ظپظ‚ط·<br>
            âœ… ظƒط±ظˆطھ ط§ظ„ط¹ظ…ظˆظ„ط©<br>
            âœ… ظ…ط±ظƒط² ط§ظ„ط§طھطµط§ظ„ CC<br>
            âڑ™ï¸ڈ ط¨ظ†ط§ط،ظ‹ ط¹ظ„ظ‰ ط§ظ„طµظ„ط§ط­ظٹط§طھ<br>
            â‌Œ ظ„ط§ ظٹط±ظ‰ ط¨ط§ظ‚ظٹ ط§ظ„ظپط±ظˆط¹
          </div>
        </div>
        <div style="background:var(--bg3);border:1px solid var(--brd1);border-radius:10px;padding:14px">
          <div style="font-size:13px;font-weight:800;margin-bottom:8px">ًں‘پ ط§ظ„ظ…ط´ط§ظ‡ط¯ (Viewer)</div>
          <div style="font-size:12px;color:var(--mu);line-height:1.8">
            âœ… ظ‚ط±ط§ط،ط© ظپظ‚ط·<br>
            âœ… ط§ظ„طھظ‚ط§ط±ظٹط±<br>
            â‌Œ ظ„ط§ طھط¹ط¯ظٹظ„<br>
            â‌Œ ظ„ط§ ط¥ط¶ط§ظپط©<br>
            â‌Œ ظ„ط§ ط­ط°ظپ
          </div>
        </div>
      </div>
    </div>

  </div>{{-- end old duplicate (empty) --}}
</div>{{-- end old duplicate --}}

@endsection

@push('scripts')
<script>
/* â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ
   SETTINGS PAGE â€” BILINGUAL CONTROLLER
   â•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گâ•گ */

/* â”€â”€â”€ Translation Dictionary â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
const ST = {
  ar: {
    panelHdr:'âڑ™ï¸ڈ ظ„ظˆط­ط© ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ',
    footerNote:'ًں”گ ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ ط§ظ„ط­ط³ط§ط³ط©\nظ„ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ ظپظ‚ط·',
    // Nav sections
    s_general_ico:'ًں“‹',     s_general_lbl:'ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ ط§ظ„ط¹ط§ظ…ط©',  s_general_sub:'ط£ظ†ظˆط§ط¹ ظˆط­ط§ظ„ط§طھ ط§ظ„ط­ط³ط§ط¨ط§طھ',
    s_branches_ico:'ًںڈ¢',    s_branches_lbl:'ط§ظ„ظپط±ظˆط¹',             s_branches_sub:'ط¥ط¯ط§ط±ط© ظپط±ظˆط¹ ط§ظ„ط´ط±ظƒط©',
    s_employees_ico:'ًں‘¥',   s_employees_lbl:'ط§ظ„ظ…ظˆط¸ظپظˆظ†',          s_employees_sub:'ط§ظ„ط¨ط±ظˆظƒط±ط² ظˆط§ظ„ظ…ط³ظˆظ‘ظ‚ظˆظ†',
    s_managers_ico:'ًں‘¤',    s_managers_lbl:'ط§ظ„ظ…ط¯ظٹط±ظˆظ†',           s_managers_sub:'ظ…ط¯ظٹط±ظˆ ط§ظ„ظپط±ظˆط¹ ظˆط§ظ„طµظ„ط§ط­ظٹط§طھ',
    s_approvals_ico:'âœ…',   s_approvals_lbl:'ط§ظ„ط§ط¹طھظ…ط§ط¯ط§طھ',        s_approvals_sub:'ظ…ظˆط¸ظپظˆظ† ط¨ط§ظ†طھط¸ط§ط± ط§ظ„ظ‚ط¨ظˆظ„',
    s_permissions_ico:'ًں”’', s_permissions_lbl:'ط§ظ„طµظ„ط§ط­ظٹط§طھ',       s_permissions_sub:'طµظ„ط§ط­ظٹط§طھ ط§ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ',
    // General
    gen_h2:'ًں“‹ ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ ط§ظ„ط¹ط§ظ…ط©',
    gen_p:'ط£ظ†ظˆط§ط¹ ط§ظ„ط­ط³ط§ط¨ط§طھ ظˆط­ط§ظ„ط§طھظ‡ط§ ظˆط£ظ†ظˆط§ط¹ ط§ظ„طھط¯ط§ظˆظ„ ط§ظ„ظ…طھط§ط­ط© ظپظٹ ط§ظ„ظ†ط¸ط§ظ…',
    gen_adminOnly:'ًں”’ ظ‡ط°ظ‡ ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ ظ„ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ ظپظ‚ط·.',
    gen_acTypes:'ًں“‹ ط£ظ†ظˆط§ط¹ ط§ظ„ط­ط³ط§ط¨ط§طھ',
    gen_acStatuses:'ًںڈ·ï¸ڈ ط­ط§ظ„ط§طھ ط§ظ„ط­ط³ط§ط¨ط§طھ',
    gen_trTypes:'ًں’° ط£ظ†ظˆط§ط¹ ط§ظ„طھط¯ط§ظˆظ„',
    gen_addBtn:'+ ط¥ط¶ط§ظپط©',
    gen_sysInfo:'â„¹ï¸ڈ ظ…ط¹ظ„ظˆظ…ط§طھ ط§ظ„ظ†ط¸ط§ظ…',
    gen_company:'ط§ط³ظ… ط§ظ„ط´ط±ظƒط©',
    gen_version:'ط§ظ„ط¥طµط¯ط§ط±',
    gen_status:'ط­ط§ظ„ط© ط§ظ„ظ†ط¸ط§ظ…',
    gen_active:'ًںں¢ ظ†ط´ط·',
    gen_currentUser:'ط§ظ„ظ…ط³طھط®ط¯ظ… ط§ظ„ط­ط§ظ„ظٹ',
    gen_noItems:'ظ„ط§ طھظˆط¬ط¯ ط¹ظ†ط§طµط±',
    gen_items:' ط¹ظ†طµط±',
    gen_phAr:'ط¹ط±ط¨ظٹ',
    // Branches
    br_h2:'ًںڈ¢ ط¥ط¯ط§ط±ط© ط§ظ„ظپط±ظˆط¹',
    br_p:'طھط¹ط±ظٹظپ ظپط±ظˆط¹ ط§ظ„ط´ط±ظƒط© ظˆط¥ط¯ط§ط±طھظ‡ط§',
    br_newBtn:'ًںڈ¢ ظپط±ط¹ ط¬ط¯ظٹط¯',
    br_listTitle:'ًںڈ¢ ظ‚ط§ط¦ظ…ط© ط§ظ„ظپط±ظˆط¹',
    br_refresh:'ًں”„ طھط­ط¯ظٹط«',
    br_th_code:'ط§ظ„ظƒظˆط¯', br_th_nar:'ط§ظ„ط§ط³ظ… ط¨ط§ظ„ط¹ط±ط¨ظٹ', br_th_nen:'ط§ظ„ط§ط³ظ… ط¨ط§ظ„ط¥ظ†ط¬ظ„ظٹط²ظٹ',
    br_th_cards:'ط¹ط¯ط¯ ط§ظ„ظƒط±ظˆطھ', br_th_date:'طھط§ط±ظٹط® ط§ظ„ط¥ظ†ط´ط§ط،',
    br_loading:'ط¬ط§ط±ظٹ ط§ظ„طھط­ظ…ظٹظ„...',
    br_empty:'ظ„ط§ طھظˆط¬ط¯ ظپط±ظˆط¹',
    br_del:'ًں—‘ ط­ط°ظپ',
    br_card_lbl:'ظƒط±طھ',
    br_modal_title:'ًںڈ¢ ط¥ط¶ط§ظپط© ظپط±ط¹ ط¬ط¯ظٹط¯',
    br_lbl_ar:'ط§ط³ظ… ط§ظ„ظپط±ط¹ ط¨ط§ظ„ط¹ط±ط¨ظٹ *',
    br_ph_ar:'ظ…ط«ط§ظ„: ظپط±ط¹ ط§ظ„ط±ظٹط§ط¶',
    br_lbl_en:'Branch Name (English)',
    br_ph_en:'e.g. Riyadh Branch',
    br_lbl_code:'ظƒظˆط¯ ط§ظ„ظپط±ط¹ (ظٹطھظ… طھظˆظ„ظٹط¯ظ‡ طھظ„ظ‚ط§ط¦ظٹط§ظ‹)',
    br_cancel:'ط¥ظ„ط؛ط§ط،',
    br_submit:'ًںڈ¢ ط¥ط¶ط§ظپط© ط§ظ„ظپط±ط¹',
    br_err_noAr:'ط£ط¯ط®ظ„ ط§ط³ظ… ط§ظ„ظپط±ط¹ ط¨ط§ظ„ط¹ط±ط¨ظٹ',
    br_ok_prefix:'âœ… طھظ…طھ ط¥ط¶ط§ظپط© ط§ظ„ظپط±ط¹: ',
    br_confirm_cards:'âڑ ï¸ڈ ظ‡ط°ط§ ط§ظ„ظپط±ط¹ ظٹط­طھظˆظٹ ط¹ظ„ظ‰ ',
    br_confirm_cards2:' ظƒط±طھ.\nط³ظٹطھظ… ظپطµظ„ظ‡ط§ ط¹ظ†ط¯ ط§ظ„ط­ط°ظپ.\nظ‡ظ„ طھط±ظٹط¯ ط§ظ„ظ…طھط§ط¨ط¹ط©طں',
    br_confirm_del:'ط­ط°ظپ ط§ظ„ظپط±ط¹ آ«',
    br_confirm_del2:'آ»طں',
    // Employees
    emp_h2:'ًں‘¥ ط¥ط¯ط§ط±ط© ط§ظ„ظ…ظˆط¸ظپظٹظ†',
    emp_p:'ط§ظ„ط¨ط±ظˆظƒط±ط² ظˆط§ظ„ظ…ط³ظˆظ‘ظ‚ظˆظ† ط§ظ„ط¯ط§ط®ظ„ظٹظˆظ† ظˆط§ظ„ط®ط§ط±ط¬ظٹظˆظ†',
    emp_newBtn:'â‍• ظ…ظˆط¸ظپ ط¬ط¯ظٹط¯',
    emp_approveNow:'ط§ط¹طھظ…ط§ط¯ ط§ظ„ط¢ظ† â†گ',
    emp_listTitle:'ًں‘¥ ظ‚ط§ط¦ظ…ط© ط§ظ„ظ…ظˆط¸ظپظٹظ†',
    emp_f_all:'ظƒظ„ ط§ظ„ط£ط¯ظˆط§ط±',
    emp_role_broker:'ًںڈ¦ ط¨ط±ظˆظƒط±',
    emp_role_mkt:'ًں“¢ ظ…ط³ظˆظ‘ظ‚ ط¯ط§ط®ظ„ظٹ',
    emp_role_ext:'ًںŒگ ظ…ط³ظˆظ‘ظ‚ ط®ط§ط±ط¬ظٹ',
    emp_role_other:'ًں“‹ ط£ط®ط±ظ‰',
    emp_status_approved:'âœ… ظ…ط¹طھظ…ط¯',
    emp_status_pending:'âڈ³ ظ‚ظٹط¯ ط§ظ„ط§ظ†طھط¸ط§ط±',
    emp_status_rejected:'â‌Œ ظ…ط±ظپظˆط¶',
    emp_th_name:'ط§ظ„ط§ط³ظ…', emp_th_role:'ط§ظ„ط¯ظˆط±', emp_th_branch:'ط§ظ„ظپط±ط¹',
    emp_th_bc:'ط¹. ط§ظ„ط¨ط±ظˆظƒط±', emp_th_mc:'ط¹. ط§ظ„طھط³ظˆظٹظ‚',
    emp_th_status:'ط§ظ„ط­ط§ظ„ط©', emp_th_addedby:'ط£ط¶ظٹظپ ط¨ظˆط§ط³ط·ط©',
    emp_loading:'ط¬ط§ط±ظٹ ط§ظ„طھط­ظ…ظٹظ„...',
    emp_empty:'ظ„ط§ ظٹظˆط¬ط¯ ظ…ظˆط¸ظپظˆظ†',
    emp_base:'ط£ط³ط§ط³ظٹ',
    emp_approved_lbl:'ظ…ظˆط¸ظپ ظ…ط¹طھظ…ط¯',
    emp_pending_lbl:'ظ…ظˆط¸ظپ ط¨ط§ظ†طھط¸ط§ط± ط§ظ„ط§ط¹طھظ…ط§ط¯',
    emp_del_confirm:'ط­ط°ظپ ط§ظ„ظ…ظˆط¸ظپ: ',
    ae_modal_title:'â‍• ط¥ط¶ط§ظپط© ظ…ظˆط¸ظپ ط¬ط¯ظٹط¯',
    ae_lbl_name:'ط§ظ„ط§ط³ظ… ط§ظ„ظƒط§ظ…ظ„ *', ae_lbl_role:'ط§ظ„ط¯ظˆط± ط§ظ„ظˆط¸ظٹظپظٹ',
    ae_lbl_bc:'ط¹ظ…ظˆظ„ط© ط§ظ„ط¨ط±ظˆظƒط±', ae_lbl_mc:'ط¹ظ…ظˆظ„ط© ط§ظ„طھط³ظˆظٹظ‚',
    ae_lbl_email:'ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ', ae_lbl_branch:'ط§ظ„ظپط±ط¹',
    ae_pendingNote:'â„¹ï¸ڈ ط³ظٹظڈط¶ط§ظپ ط§ظ„ظ…ظˆط¸ظپ ظƒظ€ ظ‚ظٹط¯ ط§ظ„ط§ظ†طھط¸ط§ط± â€” ظٹط­طھط§ط¬ ط§ط¹طھظ…ط§ط¯ ط§ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ.',
    ae_cancel:'ط¥ظ„ط؛ط§ط،', ae_submit:'ط¥ط¶ط§ظپط© ط§ظ„ظ…ظˆط¸ظپ â†گ',
    ae_err_noName:'ظٹط±ط¬ظ‰ ط¥ط¯ط®ط§ظ„ ط§ظ„ط§ط³ظ…',
    // Managers
    mgr_h2:'ًں‘¤ ط¥ط¯ط§ط±ط© ط§ظ„ظ…ط¯ظٹط±ظٹظ†',
    mgr_p:'ظ…ط¯ظٹط±ظˆ ط§ظ„ظپط±ظˆط¹ ظˆطµظ„ط§ط­ظٹط§طھظ‡ظ… ظˆط¯ط¹ظˆط§طھ ط§ظ„طھط³ط¬ظٹظ„',
    mgr_newBtn:'ًں‘¤ ظ…ط¯ظٹط± ط¬ط¯ظٹط¯',
    mgr_inviteBtn:'ًں“§ ط¯ط¹ظˆط© ظ…ط¯ظٹط±',
    mgr_listTitle:'ًں‘¤ ظ‚ط§ط¦ظ…ط© ط§ظ„ظ…ط¯ظٹط±ظٹظ†',
    mgr_refresh:'ًں”„ طھط­ط¯ظٹط«',
    mgr_th_name:'ط§ظ„ط§ط³ظ…', mgr_th_email:'ط§ظ„ط¨ط±ظٹط¯', mgr_th_phone:'ط§ظ„طھظ„ظٹظپظˆظ†', mgr_th_branch:'ط§ظ„ظپط±ط¹',
    mgr_th_role:'ط§ظ„ط¯ظˆط±', mgr_th_login:'ط¢ط®ط± ط¯ط®ظˆظ„',
    mgr_th_status:'ط§ظ„ط­ط§ظ„ط©',
    mgr_loading:'ط¬ط§ط±ظٹ ط§ظ„طھط­ظ…ظٹظ„...',
    mgr_empty:'ظ„ط§ ظٹظˆط¬ط¯ ظ…ط¯ظٹط±ظˆظ†',
    mgr_neverLogin:'ظ„ظ… ظٹط¯ط®ظ„ ط¨ط¹ط¯',
    mgr_active:'ظ†ط´ط·', mgr_inactive:'ظ…ط¹ط·ظ‘ظ„',
    mgr_resetPw:'ًں”‘ ط¥ط¹ط§ط¯ط© طھط¹ظٹظٹظ†',
    mgr_del:'ًں—‘ ط­ط°ظپ',
    mgr_del_confirm:'ط­ط°ظپ ط§ظ„ظ…ط¯ظٹط±: ',
    mgr_role_bm:'ظ…ط¯ظٹط± ظپط±ط¹', mgr_role_viewer:'ظ…ط´ط§ظ‡ط¯', mgr_role_fa:'ظ…ط¯ظٹط± ظ…ط§ظ„ظٹ',
    mgr_reset_confirm:'ط¥ط¹ط§ط¯ط© طھط¹ظٹظٹظ† ظƒظ„ظ…ط© ظ…ط±ظˆط±: ',
    mgr_reset_ok:'ظƒظ„ظ…ط© ط§ظ„ظ…ط±ظˆط± ط§ظ„ط¬ط¯ظٹط¯ط©: ',
    mgr_edit:'âœڈï¸ڈ طھط¹ط¯ظٹظ„',
    mgr_edit_title:'âœڈï¸ڈ طھط¹ط¯ظٹظ„ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ط¯ظٹط±',
    mgr_edit_lbl_name:'ط§ظ„ط§ط³ظ… ط§ظ„ظƒط§ظ…ظ„', mgr_edit_lbl_phone:'ط±ظ‚ظ… ط§ظ„طھظ„ظٹظپظˆظ†',
    mgr_edit_lbl_branch:'ط§ظ„ظپط±ط¹', mgr_edit_lbl_active:'ط§ظ„ط­ط§ظ„ط© (ظ†ط´ط·)',
    mgr_edit_save:'ًں’¾ ط­ظپط¸ ط§ظ„طھط¹ط¯ظٹظ„ط§طھ', mgr_edit_cancel:'ط¥ظ„ط؛ط§ط،',
    mgr_edit_ok:'âœ… طھظ… طھط­ط¯ظٹط« ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ط¯ظٹط±',
    emp_edit:'âœڈï¸ڈ طھط¹ط¯ظٹظ„',
    emp_edit_title:'âœڈï¸ڈ طھط¹ط¯ظٹظ„ ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظˆط¸ظپ',
    emp_edit_lbl_name:'ط§ظ„ط§ط³ظ… ط§ظ„ظƒط§ظ…ظ„', emp_edit_lbl_email:'ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ',
    emp_edit_lbl_role:'ط§ظ„ط¯ظˆط± ط§ظ„ظˆط¸ظٹظپظٹ', emp_edit_lbl_branch:'ط§ظ„ظپط±ط¹',
    emp_edit_lbl_bc:'ط¹ظ…ظˆظ„ط© ط§ظ„ط¨ط±ظˆظƒط±', emp_edit_lbl_mc:'ط¹ظ…ظˆظ„ط© ط§ظ„طھط³ظˆظٹظ‚',
    emp_edit_lbl_active:'ط§ظ„ط­ط§ظ„ط© (ظ†ط´ط·)',
    emp_edit_save:'ًں’¾ ط­ظپط¸ ط§ظ„طھط¹ط¯ظٹظ„ط§طھ', emp_edit_cancel:'ط¥ظ„ط؛ط§ط،',
    emp_edit_ok:'âœ… طھظ… طھط­ط¯ظٹط« ط¨ظٹط§ظ†ط§طھ ط§ظ„ظ…ظˆط¸ظپ',
    // Invites
    inv_listTitle:'ًں“§ ط¯ط¹ظˆط§طھ ط§ظ„طھط³ط¬ظٹظ„',
    inv_newBtn:'+ ط¯ط¹ظˆط© ط¬ط¯ظٹط¯ط©',
    inv_desc:'ط£ط¶ظپ ط¥ظٹظ…ظٹظ„ط§طھ ظ…ط¯ط±ط§ط، ط§ظ„ظپط±ظˆط¹ â€” ط³ظٹطھظ…ظƒظ†ظˆظ† ظ…ظ† ط§ظ„طھط³ط¬ظٹظ„ ط¨ط£ظ†ظپط³ظ‡ظ… ط¨ط§ط³طھط®ط¯ط§ظ… ظ‡ط°ظ‡ ط§ظ„ط¥ظٹظ…ظٹظ„ط§طھ',
    inv_th_email:'ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ', inv_th_branch:'ط§ظ„ظپط±ط¹', inv_th_role:'ط§ظ„ط¯ظˆط±',
    inv_th_note:'ظ…ظ„ط§ط­ط¸ط©', inv_th_status:'ط§ظ„ط­ط§ظ„ط©', inv_th_date:'ط§ظ„طھط§ط±ظٹط®',
    inv_loading:'ط¬ط§ط±ظٹ ط§ظ„طھط­ظ…ظٹظ„...',
    inv_empty:'ظ„ط§ طھظˆط¬ط¯ ط¯ط¹ظˆط§طھ',
    inv_pending_badge:'âڈ³ ط¨ط§ظ†طھط¸ط§ط± ط§ظ„طھط³ط¬ظٹظ„',
    inv_used_badge:'âœ… ظ…ظڈط³طھط®ط¯ظ…ط©',
    inv_expired:'ظ…ظ†طھظ‡ظٹط©',
    inv_del:'ًں—‘ ط­ط°ظپ',
    inv_del_confirm:'ط­ط°ظپ ط§ظ„ط¯ط¹ظˆط© ظ„ظ€ ',
    inv_modal_title:'ًں“§ ط¯ط¹ظˆط© ظ…ط¯ظٹط± ظپط±ط¹ ظ„ظ„طھط³ط¬ظٹظ„',
    inv_modal_info:'ًں“Œ ط£ط¶ظپ ط¨ط±ظٹط¯ ظ…ط¯ظٹط± ط§ظ„ظپط±ط¹ â€” ط³ظٹطھظ…ظƒظ† ظ…ظ† ط§ظ„طھط³ط¬ظٹظ„ ط¨ظ†ظپط³ظ‡ ط¹ط¨ط± طµظپط­ط© ط§ظ„ط¯ط®ظˆظ„.',
    inv_lbl_email:'ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ *', inv_lbl_branch:'ط§ظ„ظپط±ط¹',
    inv_lbl_role:'ط§ظ„ط¯ظˆط±', inv_lbl_note:'ظ…ظ„ط§ط­ط¸ط© (ط§ط®طھظٹط§ط±ظٹ)',
    inv_ph_note:'ظ…ط«ط§ظ„: ظ…ط¯ظٹط± ظپط±ط¹ ط§ظ„ط±ظٹط§ط¶',
    inv_no_branch:'â€” ط¨ط¯ظˆظ† ظپط±ط¹ ظ…ط­ط¯ط¯ â€”',
    inv_cancel:'ط¥ظ„ط؛ط§ط،', inv_submit:'ًں“§ ط¥ط¶ط§ظپط© ط§ظ„ط¯ط¹ظˆط©',
    inv_err_noEmail:'ط£ط¯ط®ظ„ ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ',
    inv_ok_prefix:'âœ… طھظ…طھ ط¥ط¶ط§ظپط© ',
    // Manager modal
    mg_modal_title:'ًں‘¤ ط¥ظ†ط´ط§ط، ط­ط³ط§ط¨ ظ…ط¯ظٹط± ط¬ط¯ظٹط¯',
    mg_lbl_name:'ط§ظ„ط§ط³ظ… ط§ظ„ظƒط§ظ…ظ„ *', mg_lbl_email:'ط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ *',
    mg_lbl_phone:'ط±ظ‚ظ… ط§ظ„طھظ„ظٹظپظˆظ† (ط§ط®طھظٹط§ط±ظٹ)', mg_lbl_branch:'ط§ظ„ظپط±ط¹ ط§ظ„ظ…ط³ط¤ظˆظ„ ط¹ظ†ظ‡ *',
    mg_lbl_pw:'ظƒظ„ظ…ط© ظ…ط±ظˆط± ظ…ط¤ظ‚طھط© (ط§طھط±ظƒظ‡ط§ ظپط§ط±ط؛ط© ظ„ظ„طھظˆظ„ظٹط¯ ط§ظ„طھظ„ظ‚ط§ط¦ظٹ)',
    mg_perms_title:'ًں”گ ط§ظ„طµظ„ط§ط­ظٹط§طھ ط§ظ„ظ…ظ…ظ†ظˆط­ط©',
    mg_sel_all:'âœ… طھط­ط¯ظٹط¯ ط§ظ„ظƒظ„', mg_clr_all:'âکگ ط¥ظ„ط؛ط§ط، ط§ظ„ظƒظ„',
    mg_cancel:'ط¥ظ„ط؛ط§ط،', mg_submit:'ًں“§ ط¥ظ†ط´ط§ط، ظˆط¥ط±ط³ط§ظ„ ط¨ظٹط§ظ†ط§طھ ط§ظ„ط¯ط®ظˆظ„',
    mg_err_required:'ظٹط±ط¬ظ‰ ظ…ظ„ط، ط§ظ„ط§ط³ظ… ظˆط§ظ„ط¨ط±ظٹط¯ ط§ظ„ط¥ظ„ظƒطھط±ظˆظ†ظٹ',
    mg_ok_prefix:'âœ… طھظ… ط§ظ„ط¥ظ†ط´ط§ط،!',
    // Approvals
    appr_h2:'âœ… ط§ط¹طھظ…ط§ط¯ ط§ظ„ظ…ظˆط¸ظپظٹظ† ط§ظ„ط¬ط¯ط¯',
    appr_p:'ظ…ط±ط§ط¬ط¹ط© ط·ظ„ط¨ط§طھ ط¥ط¶ط§ظپط© ط§ظ„ظ…ظˆط¸ظپظٹظ† ط§ظ„ظ…ظ‚ط¯ظ‘ظ…ط© ظ…ظ† ظ…ط¯ط±ط§ط، ط§ظ„ظپط±ظˆط¹',
    appr_listTitle:'âڈ³ ظ…ظˆط¸ظپظˆظ† ط¨ط§ظ†طھط¸ط§ط± ط§ظ„ط§ط¹طھظ…ط§ط¯',
    appr_refresh:'ًں”„ طھط­ط¯ظٹط«',
    appr_histTitle:'ًں“‹ ط³ط¬ظ„ ط§ظ„ظ‚ط±ط§ط±ط§طھ ط§ظ„ط£ط®ظٹط±ط©',
    appr_th_emp:'ط§ظ„ظ…ظˆط¸ظپ', appr_th_role:'ط§ظ„ط¯ظˆط±', appr_th_branch:'ط§ظ„ظپط±ط¹',
    appr_th_decision:'ط§ظ„ظ‚ط±ط§ط±', appr_th_by:'ط¨ظˆط§ط³ط·ط©',
    appr_loading:'ط¬ط§ط±ظٹ ط§ظ„طھط­ظ…ظٹظ„...',
    appr_empty:'ظ„ط§ ظٹظˆط¬ط¯ ظ…ظˆط¸ظپظˆظ† ط¨ط§ظ†طھط¸ط§ط± ط§ظ„ط§ط¹طھظ…ط§ط¯',
    appr_approve:'âœ… ط§ط¹طھظ…ط§ط¯', appr_reject:'â‌Œ ط±ظپط¶',
    appr_addedBy:'ط£ط¶ط§ظپظ‡:', appr_broker:'ط¨ط±ظˆظƒط±:', appr_mkt:'طھط³ظˆظٹظ‚:',
    appr_waiting:'ط¨ط§ظ†طھط¸ط§ط± ط§ظ„ط§ط¹طھظ…ط§ط¯',
    appr_reject_prompt:'ط³ط¨ط¨ ط±ظپط¶ ',
    // Permissions section
    perm_h2:'ًں”’ ط§ظ„طµظ„ط§ط­ظٹط§طھ ظˆط§ظ„ط£ط¯ظˆط§ط±',
    perm_p:'طھظˆط¶ظٹط­ طµظ„ط§ط­ظٹط§طھ ظƒظ„ ط¯ظˆط± ظپظٹ ط§ظ„ظ†ط¸ط§ظ…',
    perm_fa_title:'ًں’¼ ط§ظ„ظ…ط¯ظٹط± ط§ظ„ظ…ط§ظ„ظٹ â€” طµظ„ط§ط­ظٹط§طھ ط­طµط±ظٹط©',
    perm_bm_title:'ًںڈ¢ ظ…ط¯ظٹط± ط§ظ„ظپط±ط¹ â€” طµظ„ط§ط­ظٹط§طھظ‡',
    perm_fa_badge:'ًں’¼ ظ…ط§ظ„ظٹ ظپظ‚ط·',
    perm_bm_badge:'ًںڈ¢ ظ…ط¯ظٹط± ظپط±ط¹',
    // FA permissions list
    perm_fa_items:[
      ['ًںڈ¢','ط¥ط¶ط§ظپط© ظˆطھط¹ط¯ظٹظ„ ط§ظ„ظپط±ظˆط¹','ط§ظ„ظˆط­ظٹط¯ ط§ظ„ظ…ط®ظˆظ‘ظ„ ط¨طھط¹ط±ظٹظپ ط§ظ„ظپط±ظˆط¹ ظˆطھط¹ط¯ظٹظ„ظ‡ط§'],
      ['ًں‘¤','ط¥ظ†ط´ط§ط، ط­ط³ط§ط¨ط§طھ ط§ظ„ظ…ط¯ظٹط±ظٹظ†','ط¥ظ†ط´ط§ط، ظˆط¥ط¯ط§ط±ط© ظ…ط¯ظٹط±ظٹ ط§ظ„ظپط±ظˆط¹'],
      ['âœ…','ط§ط¹طھظ…ط§ط¯ ط§ظ„ظ…ظˆط¸ظپظٹظ† ط§ظ„ط¬ط¯ط¯','ط§ظ„ظ‚ط¨ظˆظ„ ط£ظˆ ط§ظ„ط±ظپط¶ ظ„ط·ظ„ط¨ط§طھ ظ…ط¯ط±ط§ط، ط§ظ„ظپط±ظˆط¹'],
      ['ًں“‹','طھط¹ط±ظٹظپ ط§ظ„ط¥ط¹ط¯ط§ط¯ط§طھ ط§ظ„ط¹ط§ظ…ط©','ط£ظ†ظˆط§ط¹ ظˆط­ط§ظ„ط§طھ ط§ظ„ط­ط³ط§ط¨ط§طھ ظˆط£ظ†ظˆط§ط¹ ط§ظ„طھط¯ط§ظˆظ„'],
      ['ًں“ٹ','طھظ‚ط§ط±ظٹط± ط¬ظ…ظٹط¹ ط§ظ„ظپط±ظˆط¹','ط¹ط±ط¶ ظˆطھطµط¯ظٹط± ط¨ظٹط§ظ†ط§طھ ظƒظ„ ط§ظ„ظپط±ظˆط¹'],
      ['ًںŒگ','ط§ظ„طھط¨ط¯ظٹظ„ ط¨ظٹظ† ط§ظ„ظپط±ظˆط¹','ط¹ط±ط¶ ط¨ظٹط§ظ†ط§طھ ط£ظٹ ظپط±ط¹'],
      ['ًں“¥','ط§ط³طھظٹط±ط§ط¯ ط§ظ„ط¨ظٹط§ظ†ط§طھ','ط±ظپط¹ ظ…ظ„ظپط§طھ Excel ظˆطھط­ط¯ظٹط« ط§ظ„ظƒط±ظˆطھ'],
    ],
    perm_bm_items:[
      ['âœ…','ط¹ط±ط¶ ظƒط±ظˆطھ ظپط±ط¹ظ‡','ظٹط±ظ‰ ط§ظ„ظƒط±ظˆطھ ط§ظ„ط®ط§طµط© ط¨ظپط±ط¹ظ‡ ظپظ‚ط·'],
      ['â‍•','ط¥ظ†ط´ط§ط، ظƒط±ظˆطھ ط¹ظ…ظˆظ„ط§طھ','ط¥ط¶ط§ظپط© ظƒط±ظˆطھ ط¬ط¯ظٹط¯ط© ظ„ظپط±ط¹ظ‡'],
      ['âœڈï¸ڈ','طھط¹ط¯ظٹظ„ ط§ظ„ظƒط±ظˆطھ','طھط¹ط¯ظٹظ„ ط­ط³ط§ط¨ط§طھ ط§ظ„ظپط±ط¹'],
      ['ًں‘¥','ط¥ط¶ط§ظپط© ظ…ظˆط¸ظپظٹظ†','ظٹط¶ظٹظپ ظ…ظˆط¸ظپظٹظ† ط¨ط­ط§ظ„ط© آ«ظ‚ظٹط¯ ط§ظ„ط§ظ†طھط¸ط§ط±آ»'],
      ['ًں“ˆ','طھظ‚ط§ط±ظٹط± ط§ظ„ظپط±ط¹','طھظ‚ط§ط±ظٹط± ط®ط§طµط© ط¨ظپط±ط¹ظ‡ ظپظ‚ط·'],
      ['ًں“‍','ظ…ط±ظƒط² ط§ظ„ط§طھطµط§ظ„','ط¥ط¶ط§ظپط© ظˆط¹ط±ط¶ ظƒط±ظˆطھ CC'],
    ],
    // Perms list
    perms_list:[
      {id:'dashboard',  label:'ظ„ظˆط­ط© ط§ظ„ظ…طھط§ط¨ط¹ط©'},
      {id:'cards',      label:'ظƒط±ظˆطھ ط§ظ„ط¹ظ…ظˆظ„ط§طھ'},
      {id:'modified',   label:'ط§ظ„ط­ط³ط§ط¨ط§طھ ط§ظ„ظ…ط¹ط¯ظ‘ظ„ط©'},
      {id:'reports',    label:'ط§ظ„طھظ‚ط§ط±ظٹط±'},
      {id:'create_card',label:'ط¥ظ†ط´ط§ط، ظƒط±طھ'},
      {id:'edit_card',  label:'طھط¹ط¯ظٹظ„ ط§ظ„ط­ط³ط§ط¨ط§طھ'},
      {id:'employees',  label:'ط¥ط¯ط§ط±ط© ط§ظ„ظ…ظˆط¸ظپظٹظ†'},
      {id:'import',     label:'ط§ط³طھظٹط±ط§ط¯ ط¨ظٹط§ظ†ط§طھ'},
      {id:'export',     label:'طھطµط¯ظٹط± ط§ظ„ط¨ظٹط§ظ†ط§طھ'},
    ],
    // Shared
    th_actions:'ط¥ط¬ط±ط§ط،ط§طھ',
    btn_cancel:'ط¥ظ„ط؛ط§ط،',
    err_generic:'ط­ط¯ط« ط®ط·ط£',
    branch_select:'â€” ط§ط®طھط± ط§ظ„ظپط±ط¹ â€”',
    // Lookup panel
    lk_del_confirm:'ط­ط°ظپ آ«',
    lk_del_confirm2:'آ»طں',
    lk_err_noEn:'ط£ط¯ط®ظ„ ط§ظ„ط§ط³ظ… ط§ظ„ط¥ظ†ط¬ظ„ظٹط²ظٹ',
    lk_added_suffix:' â€” طھظ…طھ ط§ظ„ط¥ط¶ط§ظپط© âœ…',
    lk_deleted:'طھظ… ط§ظ„ط­ط°ظپ',
  },
  en: {
    panelHdr:'âڑ™ï¸ڈ Settings Panel',
    footerNote:'ًں”گ Sensitive settings\nFinance Admin only',
    s_general_ico:'ًں“‹',     s_general_lbl:'General Settings',   s_general_sub:'Account types & statuses',
    s_branches_ico:'ًںڈ¢',    s_branches_lbl:'Branches',           s_branches_sub:'Manage company branches',
    s_employees_ico:'ًں‘¥',   s_employees_lbl:'Employees',         s_employees_sub:'Brokers & marketers',
    s_managers_ico:'ًں‘¤',    s_managers_lbl:'Managers',           s_managers_sub:'Branch managers & roles',
    s_approvals_ico:'âœ…',   s_approvals_lbl:'Approvals',         s_approvals_sub:'Pending employee approvals',
    s_permissions_ico:'ًں”’', s_permissions_lbl:'Permissions',      s_permissions_sub:'Finance admin permissions',
    gen_h2:'ًں“‹ General Settings',
    gen_p:'Account types, statuses and trading types available in the system',
    gen_adminOnly:'ًں”’ These settings are for Finance Admin only.',
    gen_acTypes:'ًں“‹ Account Types',
    gen_acStatuses:'ًںڈ·ï¸ڈ Account Statuses',
    gen_trTypes:'ًں’° Trading Types',
    gen_addBtn:'+ Add',
    gen_sysInfo:'â„¹ï¸ڈ System Info',
    gen_company:'Company Name',
    gen_version:'Version',
    gen_status:'System Status',
    gen_active:'ًںں¢ Active',
    gen_currentUser:'Current User',
    gen_noItems:'No items found',
    gen_items:' items',
    gen_phAr:'Arabic',
    br_h2:'ًںڈ¢ Branch Management',
    br_p:'Define and manage company branches',
    br_newBtn:'ًںڈ¢ New Branch',
    br_listTitle:'ًںڈ¢ Branches List',
    br_refresh:'ًں”„ Refresh',
    br_th_code:'Code', br_th_nar:'Arabic Name', br_th_nen:'English Name',
    br_th_cards:'Cards Count', br_th_date:'Created',
    br_loading:'Loading...',
    br_empty:'No branches found',
    br_del:'ًں—‘ Delete',
    br_card_lbl:'card(s)',
    br_modal_title:'ًںڈ¢ Add New Branch',
    br_lbl_ar:'Arabic Branch Name *',
    br_ph_ar:'e.g. Riyadh Branch (Arabic)',
    br_lbl_en:'Branch Name (English)',
    br_ph_en:'e.g. Riyadh Branch',
    br_lbl_code:'Branch Code (auto-generated)',
    br_cancel:'Cancel',
    br_submit:'ًںڈ¢ Add Branch',
    br_err_noAr:'Enter the branch name in Arabic',
    br_ok_prefix:'âœ… Branch added: ',
    br_confirm_cards:'âڑ ï¸ڈ This branch has ',
    br_confirm_cards2:' card(s).\nCards will be unlinked on delete.\nContinue?',
    br_confirm_del:'Delete branch "',
    br_confirm_del2:'"?',
    emp_h2:'ًں‘¥ Employee Management',
    emp_p:'Brokers and internal/external marketers',
    emp_newBtn:'â‍• New Employee',
    emp_approveNow:'Approve Now â†’',
    emp_listTitle:'ًں‘¥ Employees List',
    emp_f_all:'All Roles',
    emp_role_broker:'ًںڈ¦ Broker',
    emp_role_mkt:'ًں“¢ Internal Marketer',
    emp_role_ext:'ًںŒگ External Marketer',
    emp_role_other:'ًں“‹ Other',
    emp_status_approved:'âœ… Approved',
    emp_status_pending:'âڈ³ Pending',
    emp_status_rejected:'â‌Œ Rejected',
    emp_th_name:'Name', emp_th_role:'Role', emp_th_branch:'Branch',
    emp_th_bc:'Broker Comm.', emp_th_mc:'Mkt. Comm.',
    emp_th_status:'Status', emp_th_addedby:'Added By',
    emp_loading:'Loading...',
    emp_empty:'No employees found',
    emp_base:'Base',
    emp_approved_lbl:'approved employee(s)',
    emp_pending_lbl:'employee(s) pending approval',
    emp_del_confirm:'Delete employee: ',
    ae_modal_title:'â‍• Add New Employee',
    ae_lbl_name:'Full Name *', ae_lbl_role:'Job Role',
    ae_lbl_bc:'Broker Commission', ae_lbl_mc:'Marketing Commission',
    ae_lbl_email:'Email Address', ae_lbl_branch:'Branch',
    ae_pendingNote:'â„¹ï¸ڈ Employee will be added as Pending â€” requires Finance Admin approval.',
    ae_cancel:'Cancel', ae_submit:'Add Employee â†’',
    ae_err_noName:'Please enter the name',
    mgr_h2:'ًں‘¤ Manager Management',
    mgr_p:'Branch managers, their permissions and registration invites',
    mgr_newBtn:'ًں‘¤ New Manager',
    mgr_inviteBtn:'ًں“§ Invite Manager',
    mgr_listTitle:'ًں‘¤ Managers List',
    mgr_refresh:'ًں”„ Refresh',
    mgr_th_name:'Name', mgr_th_email:'Email', mgr_th_phone:'Phone', mgr_th_branch:'Branch',
    mgr_th_role:'Role', mgr_th_login:'Last Login',
    mgr_th_status:'Status',
    mgr_loading:'Loading...',
    mgr_empty:'No managers found',
    mgr_neverLogin:'Never logged in',
    mgr_active:'Active', mgr_inactive:'Disabled',
    mgr_resetPw:'ًں”‘ Reset Password',
    mgr_del:'ًں—‘ Delete',
    mgr_del_confirm:'Delete manager: ',
    mgr_role_bm:'Branch Manager', mgr_role_viewer:'Viewer', mgr_role_fa:'Finance Admin',
    mgr_reset_confirm:'Reset password for: ',
    mgr_reset_ok:'New password: ',
    mgr_edit:'âœڈï¸ڈ Edit',
    mgr_edit_title:'âœڈï¸ڈ Edit Manager Details',
    mgr_edit_lbl_name:'Full Name', mgr_edit_lbl_phone:'Phone Number',
    mgr_edit_lbl_branch:'Branch', mgr_edit_lbl_active:'Status (Active)',
    mgr_edit_save:'ًں’¾ Save Changes', mgr_edit_cancel:'Cancel',
    mgr_edit_ok:'âœ… Manager updated successfully',
    emp_edit:'âœڈï¸ڈ Edit',
    emp_edit_title:'âœڈï¸ڈ Edit Employee Details',
    emp_edit_lbl_name:'Full Name', emp_edit_lbl_email:'Email Address',
    emp_edit_lbl_role:'Job Role', emp_edit_lbl_branch:'Branch',
    emp_edit_lbl_bc:'Broker Commission', emp_edit_lbl_mc:'Marketing Commission',
    emp_edit_lbl_active:'Status (Active)',
    emp_edit_save:'ًں’¾ Save Changes', emp_edit_cancel:'Cancel',
    emp_edit_ok:'âœ… Employee updated successfully',
    inv_listTitle:'ًں“§ Registration Invites',
    inv_newBtn:'+ New Invite',
    inv_desc:'Add branch manager emails â€” they can self-register using these emails',
    inv_th_email:'Email', inv_th_branch:'Branch', inv_th_role:'Role',
    inv_th_note:'Note', inv_th_status:'Status', inv_th_date:'Date',
    inv_loading:'Loading...',
    inv_empty:'No invites found',
    inv_pending_badge:'âڈ³ Awaiting Registration',
    inv_used_badge:'âœ… Used',
    inv_expired:'Expired',
    inv_del:'ًں—‘ Delete',
    inv_del_confirm:'Delete invite for ',
    inv_modal_title:'ًں“§ Invite Branch Manager',
    inv_modal_info:'ًں“Œ Add the branch manager email â€” they can self-register from the login page.',
    inv_lbl_email:'Email Address *', inv_lbl_branch:'Branch',
    inv_lbl_role:'Role', inv_lbl_note:'Note (optional)',
    inv_ph_note:'e.g. Riyadh Branch Manager',
    inv_no_branch:'â€” No branch assigned â€”',
    inv_cancel:'Cancel', inv_submit:'ًں“§ Add Invite',
    inv_err_noEmail:'Enter an email address',
    inv_ok_prefix:'âœ… Invite added for ',
    mg_modal_title:'ًں‘¤ Create New Manager Account',
    mg_lbl_name:'Full Name *', mg_lbl_email:'Email Address *',
    mg_lbl_phone:'Phone Number (optional)', mg_lbl_branch:'Assigned Branch *',
    mg_lbl_pw:'Temporary password (leave blank for auto-generation)',
    mg_perms_title:'ًں”گ Granted Permissions',
    mg_sel_all:'âœ… Select All', mg_clr_all:'âکگ Clear All',
    mg_cancel:'Cancel', mg_submit:'ًں“§ Create & Send Credentials',
    mg_err_required:'Please fill in name and email',
    mg_ok_prefix:'âœ… Created!',
    appr_h2:'âœ… New Employee Approvals',
    appr_p:'Review employee addition requests submitted by branch managers',
    appr_listTitle:'âڈ³ Employees Awaiting Approval',
    appr_refresh:'ًں”„ Refresh',
    appr_histTitle:'ًں“‹ Recent Decisions Log',
    appr_th_emp:'Employee', appr_th_role:'Role', appr_th_branch:'Branch',
    appr_th_decision:'Decision', appr_th_by:'By',
    appr_loading:'Loading...',
    appr_empty:'No employees awaiting approval',
    appr_approve:'âœ… Approve', appr_reject:'â‌Œ Reject',
    appr_addedBy:'Added by:', appr_broker:'Broker:', appr_mkt:'Mkt:',
    appr_waiting:'awaiting approval',
    appr_reject_prompt:'Reason for rejecting ',
    perm_h2:'ًں”’ Permissions & Roles',
    perm_p:"Explanation of each role's permissions in the system",
    perm_fa_title:'ًں’¼ Finance Admin â€” Exclusive Permissions',
    perm_bm_title:'ًںڈ¢ Branch Manager â€” Permissions',
    perm_fa_badge:'ًں’¼ Admin Only',
    perm_bm_badge:'ًںڈ¢ Branch Mgr',
    perm_fa_items:[
      ['ًںڈ¢','Add & Edit Branches','Only authorized to define and modify branches'],
      ['ًں‘¤','Create Manager Accounts','Create and manage branch managers'],
      ['âœ…','Approve New Employees','Accept or reject requests from branch managers'],
      ['ًں“‹','Define General Settings','Account types, statuses and trading types'],
      ['ًں“ٹ','All-Branch Reports','View and export data from all branches'],
      ['ًںŒگ','Switch Between Branches','View data of any branch'],
      ['ًں“¥','Import Data','Upload Excel files and update cards'],
    ],
    perm_bm_items:[
      ['âœ…','View Branch Cards','Sees only cards belonging to their branch'],
      ['â‍•','Create Commission Cards','Add new cards for their branch'],
      ['âœڈï¸ڈ','Edit Cards','Edit branch accounts'],
      ['ًں‘¥','Add Employees','Adds employees with "Pending" status'],
      ['ًں“ˆ','Branch Reports','Reports for their branch only'],
      ['ًں“‍','Call Center','Add and view CC cards'],
    ],
    perms_list:[
      {id:'dashboard',  label:'Dashboard'},
      {id:'cards',      label:'Commission Cards'},
      {id:'modified',   label:'Modified Accounts'},
      {id:'reports',    label:'Reports'},
      {id:'create_card',label:'Create Card'},
      {id:'edit_card',  label:'Edit Accounts'},
      {id:'employees',  label:'Manage Employees'},
      {id:'import',     label:'Import Data'},
      {id:'export',     label:'Export Data'},
    ],
    th_actions:'Actions',
    btn_cancel:'Cancel',
    err_generic:'An error occurred',
    branch_select:'â€” Select Branch â€”',
    lk_del_confirm:'Delete "',
    lk_del_confirm2:'"?',
    lk_err_noEn:'Enter the English name',
    lk_added_suffix:' â€” added âœ…',
    lk_deleted:'Deleted successfully',
  }
};

/* â”€â”€â”€ Helpers â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function stLang() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function st(key)  { const l = stLang(); return ST[l]?.[key] ?? ST.ar[key] ?? key; }

/* â”€â”€â”€ Apply all translations â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function stApplyLang() {
  const L = stLang();
  const s = ST[L] || ST.ar;

  // Panel header & footer
  _txt('stnav-hdr',      s.panelHdr);
  _html('st-footer-note', s.footerNote.replace('\n','<br>'));

  // Nav items
  ['general','branches','employees','managers','approvals','permissions',].forEach(id => {
    _txt('snav-ico-' + id, s['s_' + id + '_ico'] || '');
    _txt('snav-lbl-' + id, s['s_' + id + '_lbl'] || '');
    _txt('snav-sub-' + id, s['s_' + id + '_sub'] || '');
  });

  // General section
  _txt('gen-h2', s.gen_h2); _txt('gen-p', s.gen_p);
  _txt('gen-admin-only', s.gen_adminOnly);
  _txt('pt-account_types',    s.gen_acTypes);
  _txt('pt-account_statuses', s.gen_acStatuses);
  _txt('pt-trading_types',    s.gen_trTypes);
  ['account_types','account_statuses','trading_types'].forEach(k => {
    _txt('addbtn-' + k, s.gen_addBtn);
    _ph('new-ar-' + k, s.gen_phAr);
  });
  _txt('gen-sysinfo-title', s.gen_sysInfo);
  _txt('lbl-gen_company',     s.gen_company);
  _txt('lbl-gen_version',     s.gen_version);
  _txt('lbl-gen_status',      s.gen_status);
  _txt('gen-active-badge',    s.gen_active);
  _txt('lbl-gen_currentUser', s.gen_currentUser);

  // Branches section
  _txt('br-h2', s.br_h2); _txt('br-p', s.br_p);
  _txt('br-new-btn', s.br_newBtn);
  _txt('br-list-title', s.br_listTitle);
  _txt('br-refresh-btn', s.br_refresh);
  _txt('br-th-code', s.br_th_code); _txt('br-th-nar', s.br_th_nar);
  _txt('br-th-nen', s.br_th_nen);   _txt('br-th-cards', s.br_th_cards);
  _txt('br-th-date', s.br_th_date); _txt('br-th-act', s.th_actions);
  // Branch modal
  _txt('br-modal-title', s.br_modal_title);
  _txt('br-lbl-ar', s.br_lbl_ar); _ph('br-ar', s.br_ph_ar);
  _txt('br-lbl-en', s.br_lbl_en); _ph('br-en', s.br_ph_en);
  _txt('br-lbl-code', s.br_lbl_code);
  _txt('br-cancel-btn', s.br_cancel); _txt('br-submit-btn', s.br_submit);

  // Employees section
  _txt('emp-h2', s.emp_h2); _txt('emp-p', s.emp_p);
  _txt('emp-new-btn', s.emp_newBtn);
  _txt('emp-approve-now-btn', s.emp_approveNow);
  _txt('emp-list-title', s.emp_listTitle);
  _txt('emp-th-name', s.emp_th_name); _txt('emp-th-role', s.emp_th_role);
  _txt('emp-th-branch', s.emp_th_branch); _txt('emp-th-bc', s.emp_th_bc);
  _txt('emp-th-mc', s.emp_th_mc); _txt('emp-th-status', s.emp_th_status);
  _txt('emp-th-addedby', s.emp_th_addedby); _txt('emp-th-act', s.th_actions);
  // Employee modal
  _txt('ae-modal-title', s.ae_modal_title);
  _txt('ae-lbl-name', s.ae_lbl_name); _txt('ae-lbl-role', s.ae_lbl_role);
  _txt('ae-lbl-bc', s.ae_lbl_bc); _txt('ae-lbl-mc', s.ae_lbl_mc);
  _txt('ae-lbl-email', s.ae_lbl_email); _txt('ae-lbl-branch', s.ae_lbl_branch);
  _txt('ae-pending-note', s.ae_pendingNote);
  _txt('ae-cancel-btn', s.ae_cancel); _txt('ae-submit-btn', s.ae_submit);
  // Role dropdowns
  _rebuildSelect('emp-f-role', [
    {v:'',label:s.emp_f_all},{v:'broker',label:s.emp_role_broker},
    {v:'marketing',label:s.emp_role_mkt},{v:'external',label:s.emp_role_ext},
    {v:'other',label:s.emp_role_other},
  ]);
  _rebuildSelect('ae-role', [
    {v:'broker',label:s.emp_role_broker},{v:'marketing',label:s.emp_role_mkt},
    {v:'external',label:s.emp_role_ext},{v:'other',label:s.emp_role_other},
  ]);

  // Managers section
  _txt('mgr-h2', s.mgr_h2); _txt('mgr-p', s.mgr_p);
  _txt('mgr-new-btn', s.mgr_newBtn); _txt('mgr-invite-btn', s.mgr_inviteBtn);
  _txt('mgr-list-title', s.mgr_listTitle); _txt('mgr-refresh-btn', s.mgr_refresh);
  _txt('mgr-th-name', s.mgr_th_name); _txt('mgr-th-email', s.mgr_th_email);
  _txt('mgr-th-phone', s.mgr_th_phone);
  _txt('mgr-th-branch', s.mgr_th_branch); _txt('mgr-th-role', s.mgr_th_role);
  _txt('mgr-th-login', s.mgr_th_login); _txt('mgr-th-status', s.mgr_th_status);
  _txt('mgr-th-act', s.th_actions);
  // Invites
  _txt('inv-list-title', s.inv_listTitle); _txt('inv-new-btn', s.inv_newBtn);
  _txt('inv-desc', s.inv_desc);
  _txt('inv-th-email', s.inv_th_email); _txt('inv-th-branch', s.inv_th_branch);
  _txt('inv-th-role', s.inv_th_role); _txt('inv-th-note', s.inv_th_note);
  _txt('inv-th-status', s.inv_th_status); _txt('inv-th-date', s.inv_th_date);
  _txt('inv-th-act', s.th_actions);
  // Invite modal
  _txt('inv-modal-title', s.inv_modal_title);
  _txt('inv-modal-info', s.inv_modal_info);
  _txt('inv-lbl-email', s.inv_lbl_email); _txt('inv-lbl-branch', s.inv_lbl_branch);
  _txt('inv-lbl-role', s.inv_lbl_role);   _txt('inv-lbl-note', s.inv_lbl_note);
  _ph('inv-note-input', s.inv_ph_note);
  _txt('inv-cancel-btn', s.inv_cancel); _txt('inv-submit-btn', s.inv_submit);
  _rebuildSelect('inv-role-input', [
    {v:'branch_manager',label:s.mgr_role_bm},{v:'viewer',label:s.mgr_role_viewer},
  ]);
  // Manager modal
  _txt('mg-modal-title', s.mg_modal_title);
  _txt('mg-lbl-name', s.mg_lbl_name); _txt('mg-lbl-email', s.mg_lbl_email);
  _txt('mg-lbl-phone', s.mg_lbl_phone);
  _txt('mg-lbl-branch', s.mg_lbl_branch); _txt('mg-lbl-pw', s.mg_lbl_pw);
  _txt('mg-perms-title', s.mg_perms_title);
  _txt('mg-sel-all', s.mg_sel_all); _txt('mg-clr-all', s.mg_clr_all);
  _txt('mg-cancel-btn', s.mg_cancel); _txt('mg-submit-btn', s.mg_submit);

  // Approvals section
  _txt('appr-h2', s.appr_h2); _txt('appr-p', s.appr_p);
  _txt('appr-list-title', s.appr_listTitle); _txt('appr-refresh-btn', s.appr_refresh);
  _txt('appr-hist-title', s.appr_histTitle);
  _txt('appr-th-emp', s.appr_th_emp); _txt('appr-th-role', s.appr_th_role);
  _txt('appr-th-branch', s.appr_th_branch); _txt('appr-th-decision', s.appr_th_decision);
  _txt('appr-th-by', s.appr_th_by);

  // Permissions section
  _txt('perm-h2', s.perm_h2); _txt('perm-p', s.perm_p);
  _txt('perm-fa-title', s.perm_fa_title); _txt('perm-bm-title', s.perm_bm_title);
  _renderPermsList('perm-fa-list', s.perm_fa_items, s.perm_fa_badge, 'rgba(26,173,186,.12)', 'badge-blue');
  _renderPermsList('perm-bm-list', s.perm_bm_items, s.perm_bm_badge, 'rgba(245,166,35,.1)', 'badge-orange');

  // Rebuild perm-grid if already rendered
  const grid = document.getElementById('perm-grid');
  if (grid && grid.children.length) {
    grid.innerHTML = s.perms_list.map(p => `
      <div style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:9px;
        background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.3);cursor:pointer"
        id="pit-${p.id}" onclick="togPerm('${p.id}')">
        <div style="width:18px;height:18px;border-radius:5px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700"
          id="pchk-${p.id}">âœ“</div>
        <span style="font-size:12px;font-weight:600">${p.label}</span>
      </div>`).join('');
  }

  // Re-render current section from cache (for dynamic content)
  if (_currentSection && _cacheRender[_currentSection]) {
    _cacheRender[_currentSection]();
  }

  // Update branch selects placeholder
  _updateBranchSelects();
}

function _txt(id, val) { const e = document.getElementById(id); if (e && val !== undefined) e.textContent = val; }
function _html(id, val) { const e = document.getElementById(id); if (e && val !== undefined) e.innerHTML = val; }
function _ph(id, val)  { const e = document.getElementById(id); if (e && val !== undefined) e.placeholder = val; }
function _rebuildSelect(id, opts) {
  const el = document.getElementById(id); if (!el) return;
  const cur = el.value;
  el.innerHTML = opts.map(o => `<option value="${o.v}">${o.label}</option>`).join('');
  el.value = cur;
}
function _renderPermsList(containerId, items, badge, bg, bCls) {
  const el = document.getElementById(containerId); if (!el) return;
  el.innerHTML = (items || []).map(([ic,t,d]) => `
    <div style="display:flex;align-items:flex-start;gap:11px;padding:10px 0;border-bottom:1px solid var(--brd1)">
      <div style="width:34px;height:34px;border-radius:8px;background:${bg};display:flex;align-items:center;justify-content:center;font-size:16px;flex-shrink:0">${ic}</div>
      <div style="flex:1">
        <div style="font-size:12px;font-weight:700">${t}</div>
        <div style="font-size:10px;color:var(--mu);margin-top:2px">${d}</div>
      </div>
      <span class="badge ${bCls}">${badge}</span>
    </div>`).join('');
}

/* â”€â”€â”€ Hook into global applyLang â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
const _stOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_stOrigApplyLang) _stOrigApplyLang(lang);
  stApplyLang();
};

/* â”€â”€â”€ State & cache â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
let _currentSection = '';
let _branchesCache  = [];
const _cacheRender  = {};
let _cachedBranches = null;
let _cachedEmployees = null;
let _cachedManagers = null;
let _cachedInvites  = null;
let _cachedApprovals = null;

function _empRoles() {
  return { broker: st('emp_role_broker'), marketing: st('emp_role_mkt'), external: st('emp_role_ext'), other: st('emp_role_other') };
}
function _empStatus(status) {
  const map = { approved: st('emp_status_approved'), pending: st('emp_status_pending'), rejected: st('emp_status_rejected') };
  const cls = { approved:'badge-green', pending:'badge-orange', rejected:'badge-red' };
  return `<span class="badge ${cls[status]||'badge-gray'}">${map[status]||status}</span>`;
}
function _mgrRole(role) {
  const map = { branch_manager: st('mgr_role_bm'), viewer: st('mgr_role_viewer'), finance_admin: st('mgr_role_fa') };
  return map[role] || role;
}

/* â”€â”€â”€ Section navigation â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function showSection(name) {
  document.querySelectorAll('.settings-section').forEach(s => s.style.display = 'none');
  ['general','branches','employees','managers','approvals','permissions',].forEach(id => {
    const btn = document.getElementById('snav-' + id);
    const ico = document.getElementById('snav-ico-' + id);
    if (btn) { btn.style.background = 'none'; btn.style.border = 'none'; }
    if (ico) { ico.style.background = 'rgba(26,173,186,.1)'; ico.style.borderColor = 'rgba(26,173,186,.15)'; }
  });
  const section = document.getElementById('section-' + name);
  const navBtn  = document.getElementById('snav-' + name);
  const navIco  = document.getElementById('snav-ico-' + name);
  if (section) section.style.display = '';
  if (navBtn)  { navBtn.style.background = 'rgba(26,173,186,.15)'; navBtn.style.border = '1px solid rgba(26,173,186,.25)'; }
  if (navIco)  { navIco.style.background = 'rgba(26,173,186,.25)'; navIco.style.borderColor = 'rgba(26,173,186,.5)'; }
  _currentSection = name;
  const loaders = { general:loadGeneralSection, branches:loadBranchesSection, employees:loadEmployeesSection, managers:loadManagersSection, approvals:loadApprovalsSection, permissions:function(){} };
  if (loaders[name]) loaders[name]();
}

/* â”€â”€â”€ GENERAL â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
async function loadGeneralSection() {
  const r = await api('GET', '/settings');
  if (!r.success) return;
  const u = document.getElementById('gen-current-user');
  if (u) u.textContent = document.getElementById('sb-username')?.textContent || 'â€”';
  ['account_types','account_statuses','trading_types'].forEach(key => {
    const el  = document.getElementById('list-' + key);
    const cnt = document.getElementById('cnt-' + key);
    if (!el) return;
    const epMap = { account_types:'account-types', account_statuses:'account-statuses', trading_types:'trading-types' };
    const items = r.data[key] || [];
    if (cnt) cnt.textContent = items.length + st('gen_items');
    el.innerHTML = items.map(item =>
      `<div style="display:flex;align-items:center;gap:8px;padding:8px 0;border-bottom:1px solid var(--brd1)">
        <span style="flex:1;font-size:12px;font-weight:600">${item.name_en}</span>
        <span style="color:var(--mu);font-size:11px">${item.name_ar}</span>
        <button class="btn btn-ghost btn-sm" style="color:var(--re);padding:3px 8px"
          onclick="deleteLookup('${epMap[key]}',${item.id},'${item.name_en}')">âœ•</button>
      </div>`
    ).join('') || `<div style="color:var(--mu);font-size:12px;padding:12px 0;text-align:center">${st('gen_noItems')}</div>`;
  });
}

async function addLookup(endpoint, key) {
  const en = document.getElementById('new-en-' + key).value.trim();
  const ar = document.getElementById('new-ar-' + key).value.trim();
  if (!en) { toast(st('lk_err_noEn'),'error'); return; }
  const r = await api('POST', '/settings/' + endpoint, { name_en: en, name_ar: ar || en });
  if (r.success) {
    toast(en + st('lk_added_suffix'),'success');
    document.getElementById('new-en-' + key).value = '';
    document.getElementById('new-ar-' + key).value = '';
    loadGeneralSection();
  } else toast(r.message || st('err_generic'),'error');
}

async function deleteLookup(endpoint, id, name) {
  if (!confirm(st('lk_del_confirm') + name + st('lk_del_confirm2'))) return;
  const r = await api('DELETE', '/settings/' + endpoint + '/' + id);
  if (r.success) { toast(st('lk_deleted'),'success'); loadGeneralSection(); }
  else toast(r.message || st('err_generic'),'error');
}

/* â”€â”€â”€ BRANCHES â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
async function loadBranchesSection() {
  const r = await api('GET', '/branches');
  if (!r.success) return;
  _branchesCache = r.data;
  _cachedBranches = r.data;
  _cacheRender.branches = () => _renderBranches(_cachedBranches);
  const cnt = document.getElementById('br-count');
  if (cnt) cnt.textContent = r.data.length;
  _renderBranches(r.data);
  _updateBranchSelects();
}

function _renderBranches(data) {
  const tbody = document.getElementById('br-tbody'); if (!tbody) return;
  tbody.innerHTML = (data && data.length)
    ? data.map(b => `
      <tr>
        <td><span class="ac-num">${b.code}</span></td>
        <td style="font-weight:700">${b.name_ar}</td>
        <td style="color:var(--mu)">${b.name_en || 'â€”'}</td>
        <td><span class="badge badge-blue">${b.commission_cards_count || 0} ${st('br_card_lbl')}</span></td>
        <td style="color:var(--mu);font-size:11px">${b.created_at?.slice(0,10) || 'â€”'}</td>
        <td><button class="btn btn-ghost btn-sm" style="color:var(--re)"
          onclick="deleteBranch(${b.id},'${b.name_ar.replace(/'/g,"\\'")}',${b.commission_cards_count||0})">${st('br_del')}</button></td>
      </tr>`).join('')
    : `<tr><td colspan="6" style="text-align:center;padding:40px;color:var(--mu)">${st('br_empty')}</td></tr>`;
}

function _updateBranchSelects() {
  const opt = (_branchesCache || []).map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
  const fa  = `<option value="">${st('branch_select')}</option>`;
  ['mg-branch','ae-branch','emg-branch','eep-branch'].forEach(id => {
    const el = document.getElementById(id); if (el) el.innerHTML = fa + opt;
  });
  const inv = document.getElementById('inv-branch-input');
  if (inv) inv.innerHTML = `<option value="">${st('inv_no_branch')}</option>` + opt;
}

async function addBranch() {
  const ar = document.getElementById('br-ar').value.trim();
  const en = document.getElementById('br-en').value.trim();
  let code = document.getElementById('br-code').value.trim();
  const err = document.getElementById('br-err');
  const ok  = document.getElementById('br-ok');
  err.classList.remove('show'); ok.classList.remove('show');
  if (!ar) { err.textContent = st('br_err_noAr'); err.classList.add('show'); return; }
  if (!code) code = 'B' + String(Math.floor(Math.random() * 900) + 100);
  const r = await api('POST', '/branches', { code, name_ar: ar, name_en: en || ar });
  if (r.success) {
    ok.textContent = st('br_ok_prefix') + ar;
    ok.classList.add('show');
    ['br-ar','br-en','br-code'].forEach(id => { const el=document.getElementById(id); if(el) el.value=''; });
    loadBranchesSection();
  } else { err.textContent = r.message || st('err_generic'); err.classList.add('show'); }
}

async function deleteBranch(id, nameAr, cardCount) {
  const msg = cardCount > 0
    ? st('br_confirm_cards') + cardCount + st('br_confirm_cards2')
    : st('br_confirm_del') + nameAr + st('br_confirm_del2');
  if (!confirm(msg)) return;
  const r = await api('DELETE', '/branches/' + id);
  if (r.success) { toast(r.message || st('br_del'),'success'); loadBranchesSection(); }
  else toast(r.message || st('err_generic'),'error');
}

/* â”€â”€â”€ EMPLOYEES â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
async function loadEmployeesSection() {
  const role = document.getElementById('emp-f-role')?.value || '';
  const r = await api('GET', '/employees' + (role ? '?role=' + role : ''));
  if (!r.success) return;
  _cachedEmployees = r.data;
  _cacheRender.employees = () => _renderEmployees(_cachedEmployees);
  const badge = document.getElementById('emp-count-badge');
  if (badge) badge.textContent = r.data.filter(e => e.status==='approved').length + ' ' + st('emp_approved_lbl');
  if (r.pending_count > 0) {
    const bn = document.getElementById('emp-pending-banner');
    const bt = document.getElementById('emp-pending-text');
    if (bn) bn.style.display = 'block';
    if (bt) bt.textContent = r.pending_count + ' ' + st('emp_pending_lbl');
    const nb = document.getElementById('snav-badge-approvals');
    if (nb) { nb.textContent = r.pending_count; nb.style.display = ''; }
  }
  _renderEmployees(r.data);
}

function _renderEmployees(data) {
  const tbody = document.getElementById('emp-tbody'); if (!tbody) return;
  const roles = _empRoles();
  tbody.innerHTML = (data && data.length)
    ? data.map(e => `
      <tr style="${e.status==='pending'?'opacity:.8':''}">
        <td style="font-weight:700">${e.name}</td>
        <td>${roles[e.role] || e.role}</td>
        <td style="color:var(--mu)">${e.branch?.name_ar || 'â€”'}</td>
        <td class="mono c-blue">$${e.broker_commission}/lot</td>
        <td class="mono c-green">$${e.marketing_commission}/lot</td>
        <td>${_empStatus(e.status)}</td>
        <td style="color:var(--mu);font-size:11px">${e.added_by?.name || 'â€”'}</td>
        <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" style="color:var(--or);border-color:rgba(245,166,35,.3)" onclick="openEditEmployee(${e.id})">
            ${st('emp_edit')}</button>
          ${CURRENT_USER.role==='finance_admin'
            ? (e.is_base
                ? `<span style="font-size:11px;color:var(--mu);padding:4px 8px;background:var(--bg3);border-radius:6px;border:1px solid var(--brd1)">${st('emp_base')}</span>`
                : `<button class="btn btn-ghost btn-sm" style="color:var(--re);border-color:rgba(232,69,69,.3)" onclick="deleteEmployee(${e.id},'${e.name.replace(/'/g,"\\'")}')">ًں—‘</button>`)
            : ''}
        </td>
      </tr>`).join('')
    : `<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)">${st('emp_empty')}</td></tr>`;
}

async function addEmployee() {
  const name = document.getElementById('ae-name').value.trim();
  const err  = document.getElementById('emp-err');
  err.classList.remove('show');
  if (!name) { err.textContent = st('ae_err_noName'); err.classList.add('show'); return; }
  const r = await api('POST', '/employees', {
    name, email: document.getElementById('ae-email').value || null,
    role: document.getElementById('ae-role').value,
    branch_id: parseInt(document.getElementById('ae-branch').value) || null,
    broker_commission:    parseFloat(document.getElementById('ae-bc').value) || 4,
    marketing_commission: parseFloat(document.getElementById('ae-mc').value) || 3,
  });
  if (r.success) {
    closeModal('modal-add-emp');
    toast(r.message || st('ae_submit'), r.pending ? 'info' : 'success');
    document.getElementById('ae-name').value = '';
    loadEmployeesSection();
  } else {
    err.textContent = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message;
    err.classList.add('show');
  }
}

async function deleteEmployee(id, name) {
  if (!confirm(st('emp_del_confirm') + name + '?')) return;
  const r = await api('DELETE', '/employees/' + id);
  if (r.success) { toast(r.message,'success'); loadEmployeesSection(); }
  else toast(r.message,'error');
}

async function deleteManager(id, name) {
  if (!confirm(st('mgr_del_confirm') + name + '?')) return;
  const r = await api('DELETE', '/managers/' + id);
  if (r.success) { toast(r.message || st('mgr_del') + ' âœ…','success'); loadManagersSection(); }
  else toast(r.message,'error');
}

/* â”€â”€â”€ MANAGERS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
async function loadManagersSection() {
  const r = await api('GET', '/managers');
  if (r.success) {
    _cachedManagers = r.data;
    _cacheRender.managers = () => { _renderManagers(_cachedManagers); _renderInvites(_cachedInvites); };
    _renderManagers(r.data);
  }
  if (_branchesCache.length === 0) await loadBranchesSection();
  else _updateBranchSelects();
  // Build perm grid
  const grid = document.getElementById('perm-grid');
  if (grid && !grid.children.length) {
    const perms = ST[stLang()]?.perms_list || ST.ar.perms_list;
    grid.innerHTML = perms.map(p => `
      <div style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:9px;
        background:rgba(46,134,171,.1);border:1px solid rgba(46,134,171,.3);cursor:pointer"
        id="pit-${p.id}" onclick="togPerm('${p.id}')">
        <div style="width:18px;height:18px;border-radius:5px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700"
          id="pchk-${p.id}">âœ“</div>
        <span style="font-size:12px;font-weight:600">${p.label}</span>
      </div>`).join('');
  }
  loadInvites();
}

function _renderManagers(data) {
  const tbody = document.getElementById('mgr-tbody'); if (!tbody) return;
  tbody.innerHTML = (data && data.length)
    ? data.map(m => `
      <tr>
        <td style="font-weight:700">${m.name}</td>
        <td style="color:var(--mu)">${m.email}</td>
        <td style="color:var(--mu);direction:ltr;text-align:left">${m.phone || '<span style="color:var(--brd2)">â€”</span>'}</td>
        <td>${m.branch?.name_ar || 'â€”'}</td>
        <td><span class="badge badge-blue">${_mgrRole(m.role)}</span></td>
        <td style="color:var(--mu);font-size:11px">${m.last_login || st('mgr_neverLogin')}</td>
        <td><span class="badge ${m.is_active?'badge-green':'badge-red'}">${m.is_active?st('mgr_active'):st('mgr_inactive')}</span></td>
        <td style="display:flex;gap:6px;align-items:center;flex-wrap:wrap">
          <button class="btn btn-ghost btn-sm" style="color:var(--or);border-color:rgba(245,166,35,.3)" onclick="openEditManager(${m.id})">
            ${st('mgr_edit')}</button>
          <button class="btn btn-ghost btn-sm" onclick="resetManagerPw(${m.id},'${m.name.replace(/'/g,"\\'")}')">
            ${st('mgr_resetPw')}</button>
          ${CURRENT_USER.role==='finance_admin'
            ? `<button class="btn btn-ghost btn-sm" style="color:var(--re)" title="${st('mgr_del_confirm').trim()}" onclick="deleteManager(${m.id},'${m.name.replace(/'/g,"\\'")}')">ًں—‘</button>`
            : ''}
        </td>
      </tr>`).join('')
    : `<tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)">${st('mgr_empty')}</td></tr>`;
}

async function loadInvites() {
  const r = await api('GET', '/manager-invites');
  if (!r.success) return;
  _cachedInvites = r.data;
  _renderInvites(r.data);
}

function _renderInvites(data) {
  const tbody = document.getElementById('inv-tbody'); if (!tbody) return;
  tbody.innerHTML = (data && data.length)
    ? data.map(i => `
      <tr>
        <td style="font-weight:600">${i.email}</td>
        <td>${i.branch?.name_ar || '<span style="color:var(--mu)">â€”</span>'}</td>
        <td><span class="badge badge-blue">${_mgrRole(i.role)}</span></td>
        <td style="color:var(--mu);font-size:12px">${i.note || 'â€”'}</td>
        <td>${i.is_pending
          ? `<span class="badge badge-orange">${st('inv_pending_badge')}</span>`
          : `<span class="badge badge-green">${st('inv_used_badge')}</span>`}</td>
        <td style="color:var(--mu);font-size:11px">${i.created_at?.slice(0,10) || 'â€”'}</td>
        <td>${i.is_pending
          ? `<button class="btn btn-ghost btn-sm" style="color:var(--re)" onclick="deleteInvite(${i.id},'${i.email}')">${st('inv_del')}</button>`
          : `<span style="color:var(--mu);font-size:11px">${st('inv_expired')}</span>`}</td>
      </tr>`).join('')
    : `<tr><td colspan="7" style="text-align:center;padding:30px;color:var(--mu)">${st('inv_empty')}</td></tr>`;
}

function togPerm(id) {
  const el = document.getElementById('pit-' + id);
  const chk = document.getElementById('pchk-' + id);
  const on = chk.textContent === 'âœ“';
  chk.textContent = on ? '' : 'âœ“';
  el.style.background  = on ? 'var(--inp-bg)' : 'rgba(46,134,171,.1)';
  el.style.borderColor = on ? 'var(--brd1)'   : 'rgba(46,134,171,.3)';
}
function selectAllPerms(v) {
  (ST[stLang()]?.perms_list || ST.ar.perms_list).forEach(p => {
    const chk = document.getElementById('pchk-' + p.id);
    const el  = document.getElementById('pit-'  + p.id);
    if (!chk || !el) return;
    chk.textContent = v ? 'âœ“' : '';
    el.style.background  = v ? 'rgba(46,134,171,.1)' : 'var(--inp-bg)';
    el.style.borderColor = v ? 'rgba(46,134,171,.3)' : 'var(--brd1)';
  });
}

async function createManager() {
  const name  = document.getElementById('mg-name').value.trim();
  const email = document.getElementById('mg-email').value.trim();
  const branch= document.getElementById('mg-branch').value;
  const err   = document.getElementById('mgr-err');
  const ok    = document.getElementById('mgr-ok');
  err.classList.remove('show'); ok.classList.remove('show');
  if (!name || !email) { err.textContent = st('mg_err_required'); err.classList.add('show'); return; }
  const perms = (ST[stLang()]?.perms_list || ST.ar.perms_list)
    .filter(p => document.getElementById('pchk-' + p.id)?.textContent === 'âœ“')
    .map(p => p.id);
  const phone = buildPhone('mg-phone-code','mg-phone-num');
  const r = await api('POST', '/managers', {
    name, email,
    phone: phone || null,
    branch_id: parseInt(branch) || null,
    password: document.getElementById('mg-pw').value || null,
    permissions: perms,
  });
  if (r.success) {
    const emailIcon = r.email_sent ? 'ًں“¨ طھظ… ط¥ط±ط³ط§ظ„ ط§ظ„ط¥ظٹظ…ظٹظ„' : 'âڑ ï¸ڈ ظ„ظ… ظٹظڈط±ط³ظ„ ط§ظ„ط¥ظٹظ…ظٹظ„ (طھط­ظ‚ظ‚ ظ…ظ† ط¥ط¹ط¯ط§ط¯ط§طھ ط§ظ„ط¨ط±ظٹط¯)';
    ok.innerHTML = `${st('mg_ok_prefix')}<br>ًں“§ ${email}<br>ًں”‘ ${st('mgr_reset_ok')} <b style="font-family:monospace">${r.credentials?.temp_password || 'â€”'}</b><br><small style="color:var(--mu)">${emailIcon}</small>`;
    ok.classList.add('show');
    loadManagersSection();
  } else { err.textContent = r.message || st('err_generic'); err.classList.add('show'); }
}

async function addInvite() {
  const email = document.getElementById('inv-email-input').value.trim();
  const branchId = document.getElementById('inv-branch-input').value || null;
  const role  = document.getElementById('inv-role-input').value;
  const note  = document.getElementById('inv-note-input').value.trim();
  const err   = document.getElementById('inv-err-modal');
  const ok    = document.getElementById('inv-ok-modal');
  err.classList.remove('show'); ok.classList.remove('show');
  if (!email) { err.textContent = st('inv_err_noEmail'); err.classList.add('show'); return; }
  const r = await api('POST', '/manager-invites', { email, branch_id: branchId ? parseInt(branchId) : null, role, note: note || null });
  if (r.success) {
    ok.textContent = st('inv_ok_prefix') + email;
    ok.classList.add('show');
    document.getElementById('inv-email-input').value = '';
    document.getElementById('inv-note-input').value  = '';
    loadInvites();
  } else { err.textContent = r.errors ? Object.values(r.errors).flat().join(' â€” ') : r.message; err.classList.add('show'); }
}

async function deleteInvite(id, email) {
  if (!confirm(st('inv_del_confirm') + email + '?')) return;
  const r = await api('DELETE', '/manager-invites/' + id);
  if (r.success) { toast(st('inv_del'),'success'); loadInvites(); }
  else toast(r.message || st('err_generic'),'error');
}

async function resetManagerPw(id, name) {
  if (!confirm(st('mgr_reset_confirm') + name + '?')) return;
  const r = await api('POST', '/managers/' + id + '/reset-password');
  if (r.success) {
    const emailNote = r.email_sent ? ' ًں“¨' : ' (ظ„ظ… ظٹظڈط±ط³ظ„ ط§ظ„ط¥ظٹظ…ظٹظ„)';
    toast(st('mgr_reset_ok') + r.new_password + emailNote, 'info');
  } else toast(r.message || st('err_generic'),'error');
}

/* â”€â”€â”€ EDIT MANAGER â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function openEditManager(id) {
  const m = (_cachedManagers || []).find(x => x.id === id);
  if (!m) { toast('ظ„ط§ طھظˆط¬ط¯ ط¨ظٹط§ظ†ط§طھ ظ„ظ„ظ…ط¯ظٹط±','error'); return; }
  document.getElementById('emg-id').value   = id;
  document.getElementById('emg-name').value = m.name || '';
  // Phone â€” parse into code + number
  const _ph = parsePhone(m.phone);
  fillPhoneCodeSelect('emg-phone-code', _ph.code);
  document.getElementById('emg-phone-num').value = _ph.num;
  // build branch select
  const opt  = (_branchesCache || []).map(b => `<option value="${b.id}"${m.branch?.id===b.id?' selected':''}>${b.name_ar}</option>`).join('');
  const sel  = document.getElementById('emg-branch');
  if (sel) sel.innerHTML = `<option value="">${st('branch_select')}</option>` + opt;
  // active toggle
  const chk = document.getElementById('emg-active');
  if (chk) chk.checked = !!m.is_active;
  _emgUpdateActiveLbl();
  // perm grid
  const perms = ST[stLang()]?.perms_list || ST.ar.perms_list;
  const mPerms = m.permissions?.map(p => p.name || p) || [];
  const grid = document.getElementById('perm-grid-edit');
  if (grid) grid.innerHTML = perms.map(p => {
    const on = mPerms.includes(p.id);
    return `<div style="display:flex;align-items:center;gap:8px;padding:10px;border-radius:9px;
        background:${on?'rgba(46,134,171,.1)':'var(--inp-bg)'};border:1px solid ${on?'rgba(46,134,171,.3)':'var(--brd1)'};cursor:pointer"
        id="epit-${p.id}" onclick="togPermEdit('${p.id}')">
      <div style="width:18px;height:18px;border-radius:5px;background:var(--pri);display:flex;align-items:center;justify-content:center;color:white;font-size:11px;font-weight:700"
        id="epchk-${p.id}">${on?'âœ“':''}</div>
      <span style="font-size:12px;font-weight:600">${p.label}</span>
    </div>`;
  }).join('');
  // apply labels
  _txt('emg-lbl-name',   st('mgr_edit_lbl_name'));
  _txt('emg-lbl-phone',  st('mgr_edit_lbl_phone'));
  _txt('emg-lbl-branch', st('mgr_edit_lbl_branch'));
  _txt('emg-lbl-active', st('mgr_edit_lbl_active'));
  _txt('emg-perms-title',st('mg_perms_title'));
  _txt('emg-cancel-btn', st('mgr_edit_cancel'));
  _txt('emg-save-btn',   st('mgr_edit_save'));
  _txt('emg-modal-title',st('mgr_edit_title'));
  document.getElementById('emg-err').classList.remove('show');
  document.getElementById('emg-ok').classList.remove('show');
  // active toggle listener
  document.getElementById('emg-active').onchange = _emgUpdateActiveLbl;
  openModal('modal-edit-mgr');
}
function _emgUpdateActiveLbl() {
  const chk = document.getElementById('emg-active');
  const lbl = document.getElementById('emg-active-lbl');
  if (!chk || !lbl) return;
  lbl.textContent = chk.checked ? (st('mgr_active')||'ظ†ط´ط·') : (st('mgr_inactive')||'ظ…ط¹ط·ظ‘ظ„');
  lbl.style.color = chk.checked ? 'var(--gr)' : 'var(--re)';
}
function togPermEdit(id) {
  const el  = document.getElementById('epit-' + id);
  const chk = document.getElementById('epchk-' + id);
  if (!el || !chk) return;
  const on = chk.textContent === 'âœ“';
  chk.textContent = on ? '' : 'âœ“';
  el.style.background  = on ? 'var(--inp-bg)' : 'rgba(46,134,171,.1)';
  el.style.borderColor = on ? 'var(--brd1)'   : 'rgba(46,134,171,.3)';
}
function selectAllPermsEdit(v) {
  (ST[stLang()]?.perms_list || ST.ar.perms_list).forEach(p => {
    const chk = document.getElementById('epchk-' + p.id);
    const el  = document.getElementById('epit-'  + p.id);
    if (!chk || !el) return;
    chk.textContent = v ? 'âœ“' : '';
    el.style.background  = v ? 'rgba(46,134,171,.1)' : 'var(--inp-bg)';
    el.style.borderColor = v ? 'rgba(46,134,171,.3)' : 'var(--brd1)';
  });
}
async function saveManagerEdit() {
  const id     = document.getElementById('emg-id').value;
  const name   = document.getElementById('emg-name').value.trim();
  const phone  = buildPhone('emg-phone-code','emg-phone-num');
  const branch = document.getElementById('emg-branch').value;
  const active = document.getElementById('emg-active').checked;
  const err    = document.getElementById('emg-err');
  const ok     = document.getElementById('emg-ok');
  err.classList.remove('show'); ok.classList.remove('show');
  if (!name) { err.textContent = st('mg_err_required')||'ظٹط±ط¬ظ‰ ط¥ط¯ط®ط§ظ„ ط§ظ„ط§ط³ظ…'; err.classList.add('show'); return; }
  const perms = (ST[stLang()]?.perms_list || ST.ar.perms_list)
    .filter(p => document.getElementById('epchk-' + p.id)?.textContent === 'âœ“')
    .map(p => p.id);
  const r = await api('PUT', '/managers/' + id, {
    name, phone: phone || null,
    branch_id: parseInt(branch) || null,
    is_active: active,
    permissions: perms,
  });
  if (r.success) {
    ok.textContent = st('mgr_edit_ok') || 'âœ… طھظ… ط§ظ„طھط­ط¯ظٹط«';
    ok.classList.add('show');
    toast(st('mgr_edit_ok'),'success');
    loadManagersSection();
    setTimeout(() => closeModal('modal-edit-mgr'), 1200);
  } else { err.textContent = r.message || st('err_generic'); err.classList.add('show'); }
}

/* â”€â”€â”€ EDIT EMPLOYEE â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
function openEditEmployee(id) {
  const e = (_cachedEmployees || []).find(x => x.id === id);
  if (!e) { toast('ظ„ط§ طھظˆط¬ط¯ ط¨ظٹط§ظ†ط§طھ ظ„ظ„ظ…ظˆط¸ظپ','error'); return; }
  document.getElementById('eep-id').value    = id;
  document.getElementById('eep-name').value  = e.name || '';
  document.getElementById('eep-email').value = e.email || '';
  document.getElementById('eep-bc').value    = e.broker_commission ?? 4;
  document.getElementById('eep-mc').value    = e.marketing_commission ?? 3;
  // role select
  const roles = [
    {v:'broker',     label:st('emp_role_broker')},
    {v:'marketing',  label:st('emp_role_mkt')},
    {v:'external',   label:st('emp_role_ext')},
    {v:'other',      label:st('emp_role_other')},
  ];
  const roleSel = document.getElementById('eep-role');
  if (roleSel) { roleSel.innerHTML = roles.map(r => `<option value="${r.v}"${e.role===r.v?' selected':''}>${r.label}</option>`).join(''); }
  // branch select
  const opt = (_branchesCache || []).map(b => `<option value="${b.id}"${e.branch?.id===b.id?' selected':''}>${b.name_ar}</option>`).join('');
  const bSel = document.getElementById('eep-branch');
  if (bSel) bSel.innerHTML = `<option value="">${st('branch_select')}</option>` + opt;
  // active
  const chk = document.getElementById('eep-active');
  if (chk) chk.checked = !!e.is_active;
  _eepUpdateActiveLbl();
  // labels
  _txt('eep-lbl-name',   st('emp_edit_lbl_name'));
  _txt('eep-lbl-email',  st('emp_edit_lbl_email'));
  _txt('eep-lbl-role',   st('emp_edit_lbl_role'));
  _txt('eep-lbl-branch', st('emp_edit_lbl_branch'));
  _txt('eep-lbl-bc',     st('emp_edit_lbl_bc'));
  _txt('eep-lbl-mc',     st('emp_edit_lbl_mc'));
  _txt('eep-lbl-active', st('emp_edit_lbl_active'));
  _txt('eep-cancel-btn', st('emp_edit_cancel'));
  _txt('eep-save-btn',   st('emp_edit_save'));
  _txt('eep-modal-title',st('emp_edit_title'));
  document.getElementById('eep-err').classList.remove('show');
  document.getElementById('eep-ok').classList.remove('show');
  document.getElementById('eep-active').onchange = _eepUpdateActiveLbl;
  openModal('modal-edit-emp');
}
function _eepUpdateActiveLbl() {
  const chk = document.getElementById('eep-active');
  const lbl = document.getElementById('eep-active-lbl');
  if (!chk || !lbl) return;
  lbl.textContent = chk.checked ? (st('mgr_active')||'ظ†ط´ط·') : (st('mgr_inactive')||'ظ…ط¹ط·ظ‘ظ„');
  lbl.style.color = chk.checked ? 'var(--gr)' : 'var(--re)';
}
async function saveEmployeeEdit() {
  const id     = document.getElementById('eep-id').value;
  const name   = document.getElementById('eep-name').value.trim();
  const err    = document.getElementById('eep-err');
  const ok     = document.getElementById('eep-ok');
  err.classList.remove('show'); ok.classList.remove('show');
  if (!name) { err.textContent = st('ae_err_noName'); err.classList.add('show'); return; }
  const r = await api('PUT', '/employees/' + id, {
    name,
    email:               document.getElementById('eep-email').value.trim() || null,
    role:                document.getElementById('eep-role').value,
    branch_id:           parseInt(document.getElementById('eep-branch').value) || null,
    broker_commission:   parseFloat(document.getElementById('eep-bc').value) || 0,
    marketing_commission:parseFloat(document.getElementById('eep-mc').value) || 0,
    is_active:           document.getElementById('eep-active').checked,
  });
  if (r.success) {
    ok.textContent = st('emp_edit_ok') || 'âœ… طھظ… ط§ظ„طھط­ط¯ظٹط«';
    ok.classList.add('show');
    toast(st('emp_edit_ok'),'success');
    loadEmployeesSection();
    setTimeout(() => closeModal('modal-edit-emp'), 1200);
  } else { err.textContent = r.errors ? Object.values(r.errors).flat().join(' | ') : r.message; err.classList.add('show'); }
}

/* â”€â”€â”€ APPROVALS â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
async function loadApprovalsSection() {
  const r = await api('GET', '/employees/pending');
  if (!r.success) return;
  _cachedApprovals = r.data;
  _cacheRender.approvals = () => _renderApprovals(_cachedApprovals, r.count);
  const badge = document.getElementById('appr-count-badge');
  if (badge) {
    if (r.count > 0) { badge.textContent = r.count + ' ' + st('appr_waiting'); badge.style.display = 'inline-flex'; }
    else badge.style.display = 'none';
  }
  const nb = document.getElementById('snav-badge-approvals');
  if (nb) { if (r.count > 0) { nb.textContent = r.count; nb.style.display = ''; } else nb.style.display = 'none'; }
  _renderApprovals(r.data, r.count);
}

function _renderApprovals(data) {
  const list = document.getElementById('appr-list'); if (!list) return;
  const roles = _empRoles();
  list.innerHTML = (data && data.length)
    ? data.map(e => `
      <div style="display:flex;align-items:center;gap:12px;padding:14px;margin-bottom:10px;
        border:1px solid rgba(245,166,35,.3);border-radius:12px;background:rgba(245,166,35,.04)">
        <div style="width:44px;height:44px;border-radius:50%;flex-shrink:0;
          background:linear-gradient(135deg,var(--pri2),var(--pri3));
          display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;color:white">${e.name.charAt(0)}</div>
        <div style="flex:1;min-width:0">
          <div style="font-size:13px;font-weight:800;margin-bottom:3px">${e.name}</div>
          <div style="font-size:11px;color:var(--mu)">
            ${roles[e.role] || e.role}
            ${e.branch ? ' آ· ' + e.branch.name_ar : ''}
            آ· ${st('appr_addedBy')} ${e.added_by?.name || 'â€”'}
          </div>
          <div style="display:flex;gap:8px;margin-top:6px">
            <span class="badge badge-blue">${st('appr_broker')} $${e.broker_commission}/lot</span>
            <span class="badge badge-green">${st('appr_mkt')} $${e.marketing_commission}/lot</span>
          </div>
        </div>
        <div style="display:flex;gap:8px;flex-shrink:0">
          <button class="btn btn-sm" style="background:var(--gr);color:white"
            onclick="approveEmployee(${e.id},'${e.name.replace(/'/g,"\\'")}')">
            ${st('appr_approve')}</button>
          <button class="btn btn-sm" style="background:var(--re);color:white"
            onclick="rejectEmployee(${e.id},'${e.name.replace(/'/g,"\\'")}')">
            ${st('appr_reject')}</button>
        </div>
      </div>`).join('')
    : `<div style="text-align:center;padding:40px;color:var(--mu)"><div style="font-size:42px;opacity:.2;margin-bottom:10px">âœ…</div><div style="font-weight:700">${st('appr_empty')}</div></div>`;
}

async function approveEmployee(id, name) {
  const r = await api('PUT', '/employees/' + id + '/approve');
  if (r.success) { toast(r.message || st('appr_approve') + ' ' + name,'success'); loadApprovalsSection(); }
  else toast(r.message || st('err_generic'),'error');
}

async function rejectEmployee(id, name) {
  const reason = prompt(st('appr_reject_prompt') + name + ':');
  if (reason === null) return;
  const r = await api('PUT', '/employees/' + id + '/reject', { reason });
  if (r.success) { toast(r.message || st('appr_reject') + ' ' + name,'info'); loadApprovalsSection(); }
  else toast(r.message || st('err_generic'),'error');
}

/* â”€â”€â”€ INIT â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ */
(async function init() {
  stApplyLang();
  // Init phone code dropdowns
  fillPhoneCodeSelect('mg-phone-code',  '+966');
  fillPhoneCodeSelect('emg-phone-code', '+966');
  await loadBranchesSection();
  const ap = await api('GET', '/employees/pending');
  if (ap.success && ap.count > 0) {
    const nb = document.getElementById('snav-badge-approvals');
    if (nb) { nb.textContent = ap.count; nb.style.display = ''; }
  }
  // Open specific section from URL param ?s=guide
  const _urlSection = new URLSearchParams(window.location.search).get('s');
  showSection(_urlSection || 'general');
})();
</script>
@endpush


