@extends('layouts.app')
@section('title','تقرير المحيلين')
@section('page-title','تقرير المحيلين')
@section('content')
@verbatim
<style>
.rxr{max-width:1280px;margin:0 auto}
.rxr-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.rxr-nav a{display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);transition:all .18s;white-space:nowrap}
.rxr-nav a:hover{border-color:var(--pri);color:var(--pri2);transform:translateY(-1px)}
.rxr-nav a.on{background:linear-gradient(135deg,rgba(26,173,186,.22),rgba(26,173,186,.08));border-color:rgba(26,173,186,.45);color:var(--pri2)}
.rxr-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
.rxr-kpi{border-radius:18px;padding:18px 20px;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,.16)}
.rxr-kpi .l{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;opacity:.9;margin-bottom:8px}
.rxr-kpi .v{font-size:1.7rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1.05}
.rxr-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.rxr-card{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;padding:18px}
.rxr-pt{font-size:14px;font-weight:800;color:var(--pri2);margin-bottom:14px;display:flex;align-items:center;gap:8px}
/* Glassy bars — frosted fill from the start (RTL=right); name & count at opposite ends (never overlap) */
.rxr-gbar{position:relative;display:flex;align-items:center;justify-content:space-between;height:40px;border-radius:12px;margin-bottom:10px;background:var(--bg2);border:1px solid var(--brd1);overflow:hidden}
.rxr-gbar-fill{position:absolute;top:0;bottom:0;right:0;border-radius:12px;border:1px solid;-webkit-backdrop-filter:blur(10px) saturate(140%);backdrop-filter:blur(10px) saturate(140%);box-shadow:inset 0 1px 0 rgba(255,255,255,.18)}
.rxr-gbar-name{position:relative;z-index:1;padding:0 14px;font-weight:800;font-size:13px;color:var(--tx);white-space:nowrap}
.rxr-gbar-num{position:relative;z-index:1;padding:0 14px;font-weight:900;font-size:12.5px;white-space:nowrap}
table.rxr-tbl{width:100%;border-collapse:collapse;font-size:13px}
table.rxr-tbl th{padding:11px 12px;font-size:11px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;border-bottom:2px solid var(--pri);background:var(--bg3);position:sticky;top:0}
table.rxr-tbl td{padding:10px 12px;border-top:1px solid var(--brd1)}
.rxr-rank{display:inline-flex;align-items:center;justify-content:center;width:26px;height:26px;border-radius:50%;font-weight:900;font-size:12px}
.rxr-acc{font-family:'JetBrains Mono',monospace;font-weight:900;font-size:14px;color:var(--pri2)}
.rxr-exp{cursor:pointer;border:1px solid var(--brd2);background:var(--bg2);color:var(--pri2);border-radius:8px;padding:4px 10px;font-size:11.5px;font-weight:700}
.rxr-sub td{background:rgba(26,173,186,.05);border-top:none}
/* Referrer name cell — inline editable */
.rf-name{cursor:pointer;font-weight:800;color:var(--tx);border-bottom:1px dashed transparent;padding:2px 4px;border-radius:6px}
.rf-name:hover{border-bottom-color:var(--pri);background:rgba(26,173,186,.06)}
.rf-unnamed{color:#c2761a;font-weight:700;background:rgba(194,118,26,.1);border:1px dashed rgba(194,118,26,.45)}
.rf-name-input{font-size:13px;font-weight:700;padding:5px 9px;border-radius:8px;border:1.5px solid var(--pri);background:var(--inp-bg);color:var(--tx);min-width:150px}
.rf-tools{display:flex;gap:8px;align-items:center;flex-wrap:wrap}
.rf-btn{cursor:pointer;border:1px solid var(--brd2);background:var(--bg2);color:var(--pri2);border-radius:9px;padding:7px 13px;font-size:12.5px;font-weight:800;display:inline-flex;align-items:center;gap:6px}
.rf-btn.on{background:linear-gradient(135deg,#c2761a,#9a5a12);color:#fff;border-color:transparent}
.rf-btn-acc{background:linear-gradient(135deg,#1AADBA,#0E7A88);color:#fff;border-color:transparent}
.rf-badge{display:inline-flex;align-items:center;justify-content:center;min-width:22px;height:20px;padding:0 7px;border-radius:20px;background:rgba(194,118,26,.18);color:#c2761a;font-size:11px;font-weight:900;margin-inline-start:5px}
/* Bulk modal */
.rf-ovl{position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9998;display:none;align-items:center;justify-content:center;padding:20px}
.rf-ovl.show{display:flex}
.rf-modal{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:18px;padding:22px;width:560px;max-width:100%;max-height:88vh;overflow:auto;box-shadow:0 20px 60px rgba(0,0,0,.4)}
.rf-modal h3{margin:0 0 6px;font-size:16px;font-weight:900;color:var(--pri2)}
.rf-modal p{margin:0 0 12px;font-size:12px;color:var(--mu);line-height:1.6}
.rf-ta{width:100%;min-height:170px;font-family:'JetBrains Mono',monospace;font-size:13px;padding:12px;border-radius:10px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);resize:vertical}
/* Monthly column chart */
.rfm-chart{display:flex;align-items:stretch;gap:8px;height:230px;padding:8px 4px 0;overflow-x:auto}
.rfm-col{flex:1;min-width:40px;display:flex;flex-direction:column;align-items:center;height:100%}
.rfm-val{font-size:11px;font-weight:900;color:var(--pri2);margin-bottom:5px}
.rfm-track{flex:1;width:100%;display:flex;align-items:flex-end;justify-content:center}
.rfm-bar{width:100%;max-width:48px;border-radius:9px 9px 0 0;background:linear-gradient(180deg,#34c6d4,#0E7A88);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 -2px 8px rgba(14,122,136,.2);transition:opacity .15s;min-height:4px}
.rfm-bar:hover{opacity:.82}
.rfm-bar.peak{background:linear-gradient(180deg,#2ec07e,#157a4d);box-shadow:inset 0 1px 0 rgba(255,255,255,.3),0 -2px 10px rgba(34,160,107,.35)}
.rfm-lbl{font-size:10.5px;color:var(--mu);margin-top:7px;white-space:nowrap;font-weight:700;text-align:center}
.rf-cnt-link{cursor:pointer;color:#22a06b;font-weight:900;font-size:15px;border-bottom:1px dashed rgba(34,160,107,.5)}
.rf-cnt-link:hover{border-bottom-style:solid}
/* client/employee segmented toggle */
.rf-seg{display:inline-flex;border:1px solid var(--brd2);border-radius:10px;overflow:hidden;background:var(--bg2)}
.rf-seg-b{border:0;background:transparent;color:var(--mu);font-size:12.5px;font-weight:800;padding:7px 13px;cursor:pointer;border-inline-start:1px solid var(--brd1)}
.rf-seg-b:first-child{border-inline-start:0}
.rf-seg-b.on{background:linear-gradient(135deg,#1AADBA,#0E7A88);color:#fff}
.rf-seg-b b{font-weight:900;opacity:.85}
/* type badge */
.rf-type-badge{cursor:pointer;display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:900;white-space:nowrap;border:1px solid}
.rf-type-badge.cli{background:rgba(26,173,186,.12);color:#0E7A88;border-color:rgba(26,173,186,.4)}
.rf-type-badge.emp{background:rgba(123,104,238,.14);color:#5b46d6;border-color:rgba(123,104,238,.45)}
.rf-type-badge:hover{filter:brightness(.96)}
/* sortable headers */
.rf-sort{cursor:pointer;user-select:none;white-space:nowrap}
.rf-sort:hover{color:var(--pri2)}
/* classify popover */
.rf-pop{position:fixed;z-index:99999;background:var(--card-bg,#fff);border:1px solid var(--brd2);border-radius:12px;padding:12px;box-shadow:0 16px 44px rgba(0,0,0,.3);width:300px;max-width:92vw}
.rf-pop h4{margin:0 0 9px;font-size:13px;font-weight:900;color:var(--pri2)}
.rf-pop .rf-pop-opts{display:flex;gap:8px;margin-bottom:8px}
.rf-pop .rf-pop-opt{flex:1;text-align:center;padding:9px;border-radius:9px;border:1.5px solid var(--brd2);background:var(--bg3);color:var(--tx);font-weight:800;font-size:12.5px;cursor:pointer}
.rf-pop .rf-pop-opt.on{border-color:var(--pri);background:rgba(26,173,186,.1);color:var(--pri2)}
.rf-pop select,.rf-pop input{width:100%;font-size:13px;padding:8px;border-radius:9px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);margin-top:6px}
@media(max-width:1000px){.rxr-kpis{grid-template-columns:1fr 1fr}.rxr-2{grid-template-columns:1fr}}
</style>
@endverbatim

@php
  $totRef = (int)($rfTot->total ?? 0);
  $totCom = (float)($rfTot->commission ?? 0);
  $nRef   = (int)($rfTot->referrers ?? 0);
  $avg    = $nRef ? round($totRef / $nRef, 1) : 0;
  $best   = $rfList->first();
  $bestBr = $rfByBranch->first();
  $maxBr  = max(1, (int)($rfByBranch->max('referrals') ?? 1));
  $maxMo  = max(1, (int)($rfByMonth->max('referrals') ?? 1));
  $PAL    = ['#0E8A99','#6D3FD4','#1A8E4F','#C12B5E','#B25A0E','#1F66C9','#8A2BE2','#0E9488','#C0392B','#2D6A4F'];
  $money  = fn($v) => '$' . number_format((float)$v, (fmod((float)$v,1)?1:0));
@endphp

<div class="rxr">
  <div class="rxr-nav">
    <a href="{{ route('reports.index') }}">📊 لوحة التقارير</a>
    <a href="{{ route('reports.table') }}">📋 جدول البيانات</a>
    <a href="{{ route('reports.dynamic') }}">🔧 تقرير ديناميكي</a>
    <a href="{{ route('reports.branch-monthly') }}">🏢 تقرير الفروع</a>
    <a href="{{ route('reports.referrals') }}" class="on">🔗 تقرير المحيلين</a>
    <div style="flex:1"></div>
    @if($rfIsFa)
    <form method="GET" action="{{ route('reports.referrals') }}" style="margin:0">
      <select name="branch_id" onchange="this.form.submit()" style="font-size:12.5px;font-weight:700;padding:8px 12px;border-radius:10px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);cursor:pointer">
        <option value="">🏢 كل الفروع</option>
        @foreach($rfBranches as $b)
          <option value="{{ $b->id }}" @selected((string)($rfBranchId ?? '') === (string)$b->id)>{{ $b->name_ar ?: $b->name_en }}</option>
        @endforeach
      </select>
    </form>
    @endif
  </div>

  {{-- KPIs --}}
  <div class="rxr-kpis" style="grid-template-columns:repeat(3,1fr)">
    <div class="rxr-kpi" style="background:linear-gradient(135deg,#1AADBA,#0E7A88)"><div class="l">إجمالي الإحالات</div><div class="v">{{ number_format($totRef) }}</div></div>
    <div class="rxr-kpi" style="background:linear-gradient(135deg,#7b68ee,#5b46d6)"><div class="l">عدد المحيلين</div><div class="v">{{ number_format($nRef) }}</div></div>
    <div class="rxr-kpi" style="background:linear-gradient(135deg,#c2761a,#9a5a12)"><div class="l">متوسط حسابات لكل محيل</div><div class="v">{{ $avg }}</div></div>
  </div>

  {{-- Top 5 referrers + Best branch --}}
  <div class="rxr-2" style="align-items:start">
    <div class="rxr-card" style="border:2px solid rgba(26,173,186,.45)">
      <div class="rxr-pt">🥇 أفضل 5 محيلين</div>
      @php $top5 = $rfList->take(5); $maxTop = max(1, (int)($top5->max('accounts') ?? 1)); @endphp
      @forelse($top5 as $i => $r)
        @php
          $medal = $i==0?'🥇':($i==1?'🥈':($i==2?'🥉':'🔗'));
          $c = $PAL[$i % count($PAL)];
          $w = max(12, round($r->accounts / $maxTop * 100));
        @endphp
        <div class="rxr-gbar">
          <i class="rxr-gbar-fill" style="width:{{ $w }}%;background:{{ $c }}26;border-color:{{ $c }}"></i>
          <span class="rxr-gbar-name">{{ $medal }} {{ $r->name ?: '#'.$r->ref }}</span>
          <span class="rxr-gbar-num" style="color:{{ $c }}">{{ number_format($r->accounts) }} حساب</span>
        </div>
      @empty <div style="color:var(--mu);text-align:center;padding:20px">لا توجد بيانات إحالات</div> @endforelse
    </div>
    <div class="rxr-card" style="border:2px solid rgba(123,104,238,.45)">
      <div class="rxr-pt" style="color:#7b68ee">🏆 أفضل فرع للإحالات</div>
      @if($bestBr)
        <div style="display:flex;align-items:center;gap:14px">
          <div style="width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#7b68ee,#5b46d6);display:flex;align-items:center;justify-content:center;color:#fff;font-size:24px;flex-shrink:0">🏢</div>
          <div style="flex:1;min-width:0">
            <div style="font-size:17px;font-weight:900;color:var(--tx)">{{ $bestBr->nar }}</div>
            <div style="font-size:12px;color:var(--mu);margin-top:3px"><b style="color:#7b68ee">{{ number_format($bestBr->referrals) }}</b> إحالة عبر <b>{{ number_format($bestBr->referrers) }}</b> محيل</div>
          </div>
          <div style="text-align:left"><div style="font-size:1.4rem;font-weight:900;color:#7b68ee;font-family:monospace">{{ number_format($bestBr->referrers) }}</div><div style="font-size:10px;color:var(--mu)">محيل</div></div>
        </div>
      @else <div style="color:var(--mu);text-align:center;padding:20px">لا توجد بيانات</div> @endif
    </div>
  </div>

  {{-- Monthly trend — glassy bars (same style as the rest of the page) --}}
  <div class="rxr-card" style="margin-bottom:16px">
    <div class="rxr-pt">📅 الإحالات شهريًا</div>
    <div style="max-height:340px;overflow:auto">
    @forelse($rfByMonth as $m)
      @php $w = max(8, round($m->referrals / $maxMo * 100)); $peak = ((int)$m->referrals === (int)$maxMo); $mc = $peak ? '#22a06b' : '#1AADBA'; @endphp
      <div class="rxr-gbar">
        <i class="rxr-gbar-fill" style="width:{{ $w }}%;background:{{ $mc }}26;border-color:{{ $mc }}"></i>
        <span class="rxr-gbar-name">📅 {{ $m->month }}</span>
        <span class="rxr-gbar-num" style="color:{{ $mc }}">{{ number_format($m->referrals) }} إحالة</span>
      </div>
    @empty <div style="color:var(--mu);text-align:center;padding:18px">لا توجد بيانات</div> @endforelse
    </div>
  </div>

  {{-- By branch (glassy, full width) --}}
  <div class="rxr-card" style="margin-bottom:16px">
    <div class="rxr-pt">🏢 الإحالات حسب الفرع</div>
    @forelse($rfByBranch as $i => $b)
      @php $c = $PAL[$i % count($PAL)]; $w = max(10, round($b->referrals / $maxBr * 100)); @endphp
      <div class="rxr-gbar">
        <i class="rxr-gbar-fill" style="width:{{ $w }}%;background:{{ $c }}26;border-color:{{ $c }}"></i>
        <span class="rxr-gbar-name">🏢 {{ $b->nar }}</span>
        <span class="rxr-gbar-num" style="color:{{ $c }}">{{ number_format($b->referrals) }} إحالة</span>
      </div>
    @empty <div style="color:var(--mu);text-align:center;padding:18px">لا توجد بيانات</div> @endforelse
  </div>

  {{-- Referrers leaderboard --}}
  <div class="rxr-card" style="padding:0;overflow:hidden">
    <div style="padding:14px 18px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--brd1);flex-wrap:wrap">
      <div class="rxr-pt" style="margin:0">📋 جدول المحيلين <span id="rf-total-lbl" style="font-size:11px;color:var(--mu);font-weight:600">({{ number_format($rfList->count()) }})</span></div>
      <div class="rf-seg">
        <button type="button" class="rf-seg-b on" onclick="rfTypeFilter('',this)">الكل</button>
        <button type="button" class="rf-seg-b" onclick="rfTypeFilter('client',this)">👥 عملاء <b id="rf-cli-n">{{ number_format($rfCliCount) }}</b></button>
        <button type="button" class="rf-seg-b" onclick="rfTypeFilter('employee',this)">👤 موظفون <b id="rf-emp-n">{{ number_format($rfEmpCount) }}</b></button>
      </div>
      <div class="rf-tools">
        <button type="button" class="rf-btn" id="rf-unnamed-btn" onclick="rfToggleUnnamed(this)">
          ✎ بلا اسم <span class="rf-badge" id="rf-unnamed-count">{{ number_format($rfUnnamed) }}</span>
        </button>
        <button type="button" class="rf-btn rf-btn-acc" onclick="rfBulkOpen()">📥 إدخال أسماء دفعة</button>
        <button type="button" class="rf-btn" onclick="rfExportExcel()" style="background:linear-gradient(135deg,#1f8a4c,#146636);color:#fff;border-color:transparent">⬇️ تنزيل Excel</button>
      </div>
      <div style="flex:1"></div>
      <input id="rf-search" type="search" name="rf_nofill" placeholder="بحث بالاسم أو رقم المحيل..."
             autocomplete="off" data-lpignore="true" data-form-type="other"
             readonly onfocus="this.removeAttribute('readonly')" oninput="rfFilter(this.value)"
             style="font-size:13px;padding:8px 12px;border-radius:9px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);min-width:200px">
    </div>
    <div style="overflow:auto;max-height:620px">
      <table class="rxr-tbl">
        <thead><tr>
          <th>#</th>
          <th class="rf-sort" onclick="rfSort('acc',this)">رقم المحيل ⇅</th>
          <th class="rf-sort" onclick="rfSort('name',this)">اسم المحيل ⇅</th>
          <th class="rf-sort" onclick="rfSort('type',this)">النوع ⇅</th>
          <th class="rf-sort" onclick="rfSort('accounts',this)">عدد الحسابات ⇅</th>
          <th class="rf-sort" onclick="rfSort('maxc',this)">أعلى / أقل عمولة ⇅</th>
          <th class="rf-sort" onclick="rfSort('branches',this)">عدد الفروع ⇅</th>
          <th></th>
        </tr></thead>
        <tbody id="rf-body">
          @forelse($rfList as $i => $r)
            @php $maxTxt = $money($r->maxc); $minTxt = ($r->minc === null ? '—' : $money($r->minc)); $isEmp = ($r->ref_type === 'employee'); @endphp
            <tr class="rf-row" data-acc="{{ $r->ref }}" data-name="{{ $r->name }}" data-named="{{ $r->name ? 1 : 0 }}" data-accounts="{{ $r->accounts }}" data-branches="{{ $r->branches }}" data-comm="{{ $maxTxt }} / {{ $minTxt }}" data-maxc="{{ (float)$r->maxc }}" data-type="{{ $r->ref_type }}" data-emp="{{ $r->employee_id }}">
              <td><span class="rxr-rank" style="background:{{ $i==0?'#FFD54F':($i==1?'#B0BEC5':($i==2?'#D7A86E':'rgba(26,173,186,.15)')) }};color:{{ $i<3?'#222':'var(--pri2)' }}">{{ $i+1 }}</span></td>
              <td><span class="rxr-acc">#{{ $r->ref }}</span></td>
              <td class="rf-name-cell">
                @if($r->name)
                  <span class="rf-name" onclick="rfEditName(this,'{{ $r->ref }}')">{{ $r->name }}</span>
                @else
                  <span class="rf-name rf-unnamed" onclick="rfEditName(this,'{{ $r->ref }}')">＋ بدون اسم</span>
                @endif
              </td>
              <td class="rf-type-cell">
                <span class="rf-type-badge {{ $isEmp ? 'emp' : 'cli' }}" onclick="rfClassify(this,'{{ $r->ref }}')" title="اضغط لتغيير النوع">{{ $isEmp ? '👤 موظف' : '👥 عميل' }}</span>
              </td>
              <td><span class="rf-cnt-link" onclick="rfExpand('{{ $r->ref }}', this)" title="اضغط لعرض الحسابات المُحالة">{{ number_format($r->accounts) }}</span></td>
              <td class="mono" style="white-space:nowrap"><span style="color:#22a06b;font-weight:900">{{ $maxTxt }}</span> <span style="color:var(--mu)">/</span> <span style="color:#c2761a;font-weight:900">{{ $minTxt }}</span></td>
              <td><span style="color:var(--mu)">{{ number_format($r->branches) }}</span></td>
              <td><button class="rxr-exp" onclick="rfExpand('{{ $r->ref }}', this)">عرض الحسابات ▾</button></td>
            </tr>
          @empty
            <tr><td colspan="8" style="text-align:center;padding:40px;color:var(--mu)">لا توجد إحالات مُسجَّلة بعد</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Glassy distribution of referrers by TYPE (client vs employee) --}}
  @php
    $rfCliAcc = 0; $rfEmpAcc = 0;
    foreach ($rfList as $r) { if ($r->ref_type === 'employee') $rfEmpAcc += (int)$r->accounts; else $rfCliAcc += (int)$r->accounts; }
    $rfTypeTotal = max(1, $rfCliCount + $rfEmpCount);
    $rfCliW = max(6, round($rfCliCount / $rfTypeTotal * 100));
    $rfEmpW = max(6, round($rfEmpCount / $rfTypeTotal * 100));
    $rfCliPct = round($rfCliCount / $rfTypeTotal * 100);
    $rfEmpPct = round($rfEmpCount / $rfTypeTotal * 100);
  @endphp
  <div class="rxr-card" style="margin-top:16px">
    <div class="rxr-pt">👥👤 توزيع المحيلين حسب النوع</div>
    <div class="rxr-gbar">
      <i class="rxr-gbar-fill" id="rf-tp-cli-fill" style="width:{{ $rfCliW }}%;background:#1AADBA26;border-color:#1AADBA"></i>
      <span class="rxr-gbar-name">👥 عملاء <span id="rf-tp-cli-acc" style="color:var(--mu);font-weight:700">· {{ number_format($rfCliAcc) }} حساب مُحال</span></span>
      <span class="rxr-gbar-num" id="rf-tp-cli-num" style="color:#0E7A88">{{ number_format($rfCliCount) }} محيل ({{ $rfCliPct }}%)</span>
    </div>
    <div class="rxr-gbar">
      <i class="rxr-gbar-fill" id="rf-tp-emp-fill" style="width:{{ $rfEmpW }}%;background:#7b68ee26;border-color:#7b68ee"></i>
      <span class="rxr-gbar-name">👤 موظفون <span id="rf-tp-emp-acc" style="color:var(--mu);font-weight:700">· {{ number_format($rfEmpAcc) }} حساب مُحال</span></span>
      <span class="rxr-gbar-num" id="rf-tp-emp-num" style="color:#5b46d6">{{ number_format($rfEmpCount) }} محيل ({{ $rfEmpPct }}%)</span>
    </div>
  </div>

  {{-- breathing room so the page doesn't feel cut off when scrolled to the bottom --}}
  <div style="height:90px"></div>
</div>

{{-- Canonical referrer names — reuse the same spelling, never duplicate a referrer --}}
<datalist id="rf-names-dl">
  @foreach($rfNameList as $nm)<option value="{{ $nm }}"></option>@endforeach
</datalist>

{{-- Bulk entry modal --}}
<div class="rf-ovl" id="rf-bulk-ovl" onclick="if(event.target===this)rfBulkClose()">
  <div class="rf-modal">
    <h3>📥 إدخال أسماء المحيلين دفعة واحدة</h3>
    <p>الصق كل محيل في سطر بالصيغة: <b>رقم الحساب،الاسم</b> (يمكن الفصل بفاصلة أو Tab).<br>
       أي رقم موجود مسبقًا سيُحدَّث بالاسم الجديد — رقم واحد = اسم واحد.</p>
    <textarea id="rf-bulk-ta" class="rf-ta" placeholder="700259، أحمد علي&#10;700312، محمد سالم&#10;..."></textarea>
    <div id="rf-bulk-msg" style="font-size:12px;margin:10px 2px;min-height:16px"></div>
    <div style="display:flex;gap:10px;justify-content:flex-start;margin-top:6px">
      <button type="button" class="rf-btn rf-btn-acc" onclick="rfBulkSave()">💾 حفظ الكل</button>
      <button type="button" class="rf-btn" onclick="rfBulkClose()">إلغاء</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
var _rfOnlyUnnamed=false, _rfType='';
function rfFilter(q){
  q=(q||'').toLowerCase().trim();
  var shown=0;
  document.querySelectorAll('#rf-body .rf-row').forEach(function(tr){
    var acc=(tr.getAttribute('data-acc')||'').toLowerCase();
    var nm=(tr.getAttribute('data-name')||'').toLowerCase();
    var match=!q || acc.indexOf(q)>=0 || nm.indexOf(q)>=0;
    var unnamedOk=!_rfOnlyUnnamed || tr.getAttribute('data-named')!=='1';
    var typeOk=!_rfType || (tr.getAttribute('data-type')||'client')===_rfType;
    var ok=match && unnamedOk && typeOk;
    tr.style.display=ok?'':'none';
    if(ok) shown++;
    var sub=tr.nextElementSibling;
    if(sub && sub.classList.contains('rxr-sub')) sub.style.display = ok ? sub.style.display : 'none';
  });
  var lbl=document.getElementById('rf-total-lbl'); if(lbl) lbl.textContent='('+shown.toLocaleString('en-US')+')';
}
function rfToggleUnnamed(btn){
  _rfOnlyUnnamed=!_rfOnlyUnnamed;
  btn.classList.toggle('on',_rfOnlyUnnamed);
  rfFilter(document.getElementById('rf-search').value);
}
// client | employee | all  → the two reports the user asked for
function rfTypeFilter(t, btn){
  _rfType=t;
  document.querySelectorAll('.rf-seg-b').forEach(function(b){ b.classList.remove('on'); });
  if(btn) btn.classList.add('on');
  rfFilter(document.getElementById('rf-search').value);
}
// Recompute the glassy client/employee distribution panel from the current rows.
function rfUpdateTypePanel(){
  var cliC=0,empC=0,cliA=0,empA=0;
  document.querySelectorAll('#rf-body .rf-row').forEach(function(t){
    var emp=(t.getAttribute('data-type')==='employee'), a=+(t.getAttribute('data-accounts')||0);
    if(emp){empC++;empA+=a;}else{cliC++;cliA+=a;}
  });
  var tot=Math.max(1,cliC+empC);
  var set=function(id,v){var e=document.getElementById(id);if(e)e.textContent=v;};
  var w=function(id,p){var e=document.getElementById(id);if(e)e.style.width=Math.max(6,Math.round(p))+'%';};
  set('rf-tp-cli-num', cliC.toLocaleString('en')+' محيل ('+Math.round(cliC/tot*100)+'%)');
  set('rf-tp-emp-num', empC.toLocaleString('en')+' محيل ('+Math.round(empC/tot*100)+'%)');
  set('rf-tp-cli-acc', '· '+cliA.toLocaleString('en')+' حساب مُحال');
  set('rf-tp-emp-acc', '· '+empA.toLocaleString('en')+' حساب مُحال');
  w('rf-tp-cli-fill', cliC/tot*100); w('rf-tp-emp-fill', empC/tot*100);
}
// Sort the table by any column (client-side, toggles asc/desc).
var _rfSortKey='', _rfSortDir=1;
function rfSort(key, th){
  if(_rfSortKey===key) _rfSortDir=-_rfSortDir; else { _rfSortKey=key; _rfSortDir=1; }
  var numeric=(key==='acc'||key==='accounts'||key==='maxc'||key==='branches');
  var body=document.getElementById('rf-body');
  var rows=Array.prototype.slice.call(body.querySelectorAll('.rf-row'));
  var val=function(tr){
    if(key==='acc')      return tr.getAttribute('data-acc')||'';
    if(key==='name')     return (tr.getAttribute('data-name')||'').toLowerCase();
    if(key==='type')     return tr.getAttribute('data-type')||'client';
    if(key==='accounts') return +(tr.getAttribute('data-accounts')||0);
    if(key==='maxc')     return +(tr.getAttribute('data-maxc')||0);
    if(key==='branches') return +(tr.getAttribute('data-branches')||0);
    return '';
  };
  rows.sort(function(a,b){
    var x=val(a), y=val(b);
    if(numeric){ x=+x||0; y=+y||0; return (x-y)*_rfSortDir; }
    return String(x).localeCompare(String(y),'ar')*_rfSortDir;
  });
  rows.forEach(function(tr){
    var sub=tr.nextElementSibling && tr.nextElementSibling.classList.contains('rxr-sub') ? tr.nextElementSibling : null;
    body.appendChild(tr); if(sub) body.appendChild(sub);
  });
}
function rfRecount(){
  var n=document.querySelectorAll('#rf-body .rf-row[data-named="0"]').length;
  var el=document.getElementById('rf-unnamed-count'); if(el) el.textContent=n.toLocaleString('en-US');
}
// Inline edit a referrer name → saves to the referrers table, shows everywhere.
function rfEditName(span, acc){
  if(span.querySelector('input')) return;
  var cur=(span.closest('tr').getAttribute('data-name')||'').trim();
  var inp=document.createElement('input');
  inp.type='text'; inp.className='rf-name-input'; inp.value=cur;
  inp.setAttribute('list','rf-names-dl'); inp.setAttribute('autocomplete','off');
  span.replaceWith(inp); inp.focus(); inp.select();
  var done=false;
  function commit(save){
    if(done) return; done=true;
    var val=(inp.value||'').trim();
    if(save && val && val!==cur){ rfSaveName(acc, val, inp); }
    else { rfRenderName(inp, acc, save&&val?val:cur); }
  }
  inp.addEventListener('keydown',function(e){
    if(e.key==='Enter'){e.preventDefault();commit(true);}
    else if(e.key==='Escape'){e.preventDefault();commit(false);}
  });
  inp.addEventListener('blur',function(){commit(true);});
}
function rfRenderName(node, acc, name){
  var tr=node.closest('tr');
  var span=document.createElement('span');
  span.className='rf-name'+(name?'':' rf-unnamed');
  span.textContent=name||'＋ بدون اسم';
  span.setAttribute('onclick',"rfEditName(this,'"+acc+"')");
  node.replaceWith(span);
  tr.setAttribute('data-name',name||'');
  tr.setAttribute('data-named',name?'1':'0');
  rfRecount();
}
async function rfSaveName(acc, name, inp){
  inp.disabled=true;
  try{
    var r=await api('POST','/cards/referrer-name',{account:acc,name:name});
    if(r&&r.success){
      // add to datalist if new (keep one canonical spelling reusable)
      var dl=document.getElementById('rf-names-dl');
      if(dl && !Array.from(dl.options).some(o=>o.value===name)){var o=document.createElement('option');o.value=name;dl.appendChild(o);}
      rfRenderName(inp, acc, name);
      if(typeof toast==='function') toast('تم حفظ اسم المحيل','success');
    } else { inp.disabled=false; if(typeof toast==='function') toast('تعذّر الحفظ','error'); }
  }catch(e){ inp.disabled=false; if(typeof toast==='function') toast('خطأ بالحفظ','error'); }
}
// Bulk modal
function rfBulkOpen(){ document.getElementById('rf-bulk-ovl').classList.add('show'); document.getElementById('rf-bulk-ta').focus(); }
function rfBulkClose(){ document.getElementById('rf-bulk-ovl').classList.remove('show'); document.getElementById('rf-bulk-msg').textContent=''; }
async function rfBulkSave(){
  var raw=(document.getElementById('rf-bulk-ta').value||'').trim();
  var msg=document.getElementById('rf-bulk-msg');
  if(!raw){ msg.style.color='#c2761a'; msg.textContent='الصق البيانات أولًا'; return; }
  var items=[];
  raw.split(/\r?\n/).forEach(function(line){
    line=line.trim(); if(!line) return;
    var parts=line.split(/[\t,،;]+/);   // comma, Arabic comma, tab, semicolon
    var acc=(parts.shift()||'').replace(/[^0-9]/g,'').trim();
    var nm=parts.join(' ').trim();
    if(acc && nm) items.push({account:acc,name:nm});
  });
  if(!items.length){ msg.style.color='#c2761a'; msg.textContent='لم يتم التعرّف على أي سطر صالح (رقم،اسم)'; return; }
  msg.style.color='var(--mu)'; msg.textContent='جارٍ الحفظ ('+items.length+') ...';
  try{
    var r=await api('POST','/cards/referrer-names-bulk',{items:items});
    if(r&&r.success){
      msg.style.color='#22a06b'; msg.textContent='✓ تم حفظ '+r.saved+' محيل. سيتم تحديث الجدول...';
      setTimeout(function(){ location.reload(); }, 900);
    } else { msg.style.color='#e05050'; msg.textContent='تعذّر الحفظ'; }
  }catch(e){ msg.style.color='#e05050'; msg.textContent='خطأ: '+(e&&e.message||e); }
}
async function rfExpand(acc, el){
  var row=el.closest('tr');
  var btn=row.querySelector('.rxr-exp');   // always toggle the dedicated button's label
  var nxt=row.nextElementSibling;
  if(nxt && nxt.classList.contains('rxr-sub')){ // toggle
    var vis=nxt.style.display!=='none';
    nxt.style.display=vis?'none':''; if(btn) btn.textContent=vis?'عرض الحسابات ▾':'إخفاء ▴'; return;
  }
  if(btn) btn.textContent='... جارٍ التحميل';
  var sub=document.createElement('tr'); sub.className='rxr-sub';
  var td=document.createElement('td'); td.colSpan=8; td.style.padding='0 14px 12px'; sub.appendChild(td);
  row.parentNode.insertBefore(sub, row.nextSibling);
  try{
    var r=await api('GET','/cards/referrals?account='+encodeURIComponent(acc));
    if(!r||!r.success){ td.innerHTML='<div style="color:#e05050;padding:10px">تعذّر التحميل</div>'; if(btn) btn.textContent='عرض الحسابات ▾'; return; }
    var rows=(r.data||[]);
    if(!rows.length){ td.innerHTML='<div style="color:var(--mu);padding:10px">لا توجد حسابات</div>'; if(btn) btn.textContent='إخفاء ▴'; return; }
    var html='<table style="width:100%;border-collapse:collapse;font-size:12.5px;margin-top:4px"><thead><tr style="color:var(--mu)">'
      +'<th style="padding:6px 10px;text-align:right">رقم الحساب</th><th style="padding:6px 10px;text-align:right">الشهر</th>'
      +'<th style="padding:6px 10px;text-align:right">الفرع</th><th style="padding:6px 10px;text-align:right">النوع</th></tr></thead><tbody>';
    rows.forEach(function(d){
      html+='<tr style="border-top:1px solid var(--brd1)">'
        +'<td style="padding:6px 10px"><span class="rxr-acc" style="font-size:13px">'+(d.account_number||'—')+'</span></td>'
        +'<td style="padding:6px 10px;color:var(--mu)">'+(d.month||'—')+'</td>'
        +'<td style="padding:6px 10px">🏢 '+(d.branch||'—')+'</td>'
        +'<td style="padding:6px 10px">'+(d.account_kind==='new'?'جديد':(d.account_kind==='sub'?'فرعي':(d.account_kind||'—')))+'</td></tr>';
    });
    html+='</tbody></table>';
    td.innerHTML=html; if(btn) btn.textContent='إخفاء ▴';
  }catch(e){ td.innerHTML='<div style="color:#e05050;padding:10px">خطأ: '+(e&&e.message||e)+'</div>'; if(btn) btn.textContent='عرض الحسابات ▾'; }
}
// Excel export — styled like the rest of the system (wgExportExcel helper)
function rfExportExcel(){
  var rows=Array.from(document.querySelectorAll('#rf-body .rf-row'));
  var aoa=[['#','رقم حساب المحيل','اسم المحيل','النوع','عدد الحسابات المُحالة','أعلى / أقل عمولة','عدد الفروع']];
  var n=0;
  rows.forEach(function(tr){
    if(tr.style.display==='none') return;   // respect current filter (clients / employees)
    n++;
    aoa.push([
      n,
      tr.getAttribute('data-acc')||'',
      (tr.getAttribute('data-name')||'').trim() || '— بدون اسم —',
      (tr.getAttribute('data-type')==='employee') ? 'موظف' : 'عميل',
      Number(tr.getAttribute('data-accounts')||0),
      tr.getAttribute('data-comm')||'',
      Number(tr.getAttribute('data-branches')||0),
    ]);
  });
  if(n===0){ if(typeof toast==='function') toast('لا توجد بيانات للتصدير','error'); return; }
  var suffix = _rfType==='employee' ? '_الموظفين' : (_rfType==='client' ? '_العملاء' : '');
  var fname='تقرير_المحيلين'+suffix+'_'+new Date().toISOString().slice(0,10)+'.xlsx';
  wgExportExcel(aoa, fname, 'المحيلون', { headerColor:'0E7A88' });
}

/* ── Classify a referrer: client ↔ employee ─────────────────── */
var _rfEmps=null;   // slim employees cache
async function rfLoadEmps(){
  if(_rfEmps) return _rfEmps;
  try{ var r=await api('GET','/employees?status=approved&slim=1&all_branches=1'); _rfEmps=(r&&r.success)?r.data:[]; }
  catch(e){ _rfEmps=[]; }
  return _rfEmps;
}
function rfPopClose(){ var p=document.getElementById('rf-pop'); if(p) p.remove(); document.removeEventListener('click',rfPopOutside,true); }
function rfPopOutside(e){ var p=document.getElementById('rf-pop'); if(p && !p.contains(e.target)) rfPopClose(); }
async function rfClassify(badge, acc){
  rfPopClose();
  var tr=badge.closest('tr');
  var curType=tr.getAttribute('data-type')||'client';
  var curEmp=tr.getAttribute('data-emp')||'';
  var pop=document.createElement('div'); pop.id='rf-pop'; pop.className='rf-pop';
  pop.innerHTML='<h4>تصنيف المحيل #'+acc+'</h4>'
    +'<div class="rf-pop-opts">'
    +'<div class="rf-pop-opt'+(curType==='client'?' on':'')+'" id="rf-opt-cli">👥 عميل</div>'
    +'<div class="rf-pop-opt'+(curType==='employee'?' on':'')+'" id="rf-opt-emp">👤 موظف</div>'
    +'</div>'
    +'<div id="rf-emp-wrap" style="display:'+(curType==='employee'?'block':'none')+'">'
    +'<select id="rf-emp-sel"><option value="">— اختر الموظف المحيل —</option></select>'
    +'<button type="button" class="rf-btn rf-btn-acc" style="margin-top:8px;width:100%" onclick="rfAddEmp()">➕ إضافة موظف غير موجود</button>'
    +'<div id="rf-addemp" style="display:none;margin-top:8px">'
    +'<input id="rf-addemp-name" placeholder="اسم الموظف">'
    +'<select id="rf-addemp-branch"><option value="">— الفرع —</option></select>'
    +'<button type="button" class="rf-btn rf-btn-acc" style="margin-top:8px;width:100%" onclick="rfAddEmpSave()">حفظ الموظف</button>'
    +'</div></div>'
    +'<div id="rf-pop-msg" style="font-size:11.5px;min-height:14px;margin-top:7px"></div>'
    +'<div style="display:flex;gap:8px;margin-top:6px"><button type="button" class="rf-btn rf-btn-acc" style="flex:1" onclick="rfClassifySave(\''+acc+'\')">حفظ</button><button type="button" class="rf-btn" onclick="rfPopClose()">إلغاء</button></div>';
  document.body.appendChild(pop);
  var r=badge.getBoundingClientRect();
  var top=r.bottom+6; if(top+260>window.innerHeight) top=Math.max(8,r.top-266);
  pop.style.top=top+'px';
  pop.style.left=Math.max(8,Math.min(r.left, window.innerWidth-pop.offsetWidth-8))+'px';
  setTimeout(function(){ document.addEventListener('click',rfPopOutside,true); },0);
  // wire options
  document.getElementById('rf-opt-cli').onclick=function(){ this.classList.add('on'); document.getElementById('rf-opt-emp').classList.remove('on'); document.getElementById('rf-emp-wrap').style.display='none'; };
  document.getElementById('rf-opt-emp').onclick=async function(){ this.classList.add('on'); document.getElementById('rf-opt-cli').classList.remove('on'); document.getElementById('rf-emp-wrap').style.display='block'; await rfFillEmps(curEmp); };
  if(curType==='employee') await rfFillEmps(curEmp);
}
async function rfFillEmps(selId){
  var emps=await rfLoadEmps();
  var sel=document.getElementById('rf-emp-sel'); if(!sel) return;
  sel.innerHTML='<option value="">— اختر الموظف المحيل —</option>'+emps.map(function(e){
    var br=e.branch?(' — '+(e.branch.name_ar||e.branch.name_en||'')):'';
    return '<option value="'+e.id+'"'+(String(e.id)===String(selId)?' selected':'')+'>'+esc(e.name)+br+'</option>';
  }).join('');
}
function rfAddEmp(){ var w=document.getElementById('rf-addemp'); if(w){ w.style.display=w.style.display==='none'?'block':'none'; if(w.style.display==='block') rfFillBranches(); } }
async function rfFillBranches(){
  var sel=document.getElementById('rf-addemp-branch'); if(!sel||sel.options.length>1) return;
  try{ var b=await api('GET','/branches'); if(b&&b.success) sel.innerHTML='<option value="">— الفرع —</option>'+b.data.map(function(x){return '<option value="'+x.id+'">'+esc(x.name_ar||x.name_en)+'</option>';}).join(''); }catch(e){}
}
async function rfAddEmpSave(){
  var name=(document.getElementById('rf-addemp-name').value||'').trim();
  var branch=document.getElementById('rf-addemp-branch').value||null;
  var msg=document.getElementById('rf-pop-msg');
  if(!name){ msg.style.color='#c2761a'; msg.textContent='اكتب اسم الموظف'; return; }
  msg.style.color='var(--mu)'; msg.textContent='جارٍ إضافة الموظف...';
  try{
    var r=await api('POST','/employees',{name:name,role:'other',branch_id:branch?parseInt(branch):null});
    if(r&&r.success&&r.data){ _rfEmps=null; await rfFillEmps(r.data.id); document.getElementById('rf-addemp').style.display='none'; msg.style.color='#22a06b'; msg.textContent='✓ أُضيف الموظف'; }
    else { msg.style.color='#e05050'; msg.textContent=(r.errors?Object.values(r.errors).flat().join(' | '):(r.message||'تعذّر')); }
  }catch(e){ msg.style.color='#e05050'; msg.textContent='خطأ'; }
}
async function rfClassifySave(acc){
  var isEmp=document.getElementById('rf-opt-emp').classList.contains('on');
  var msg=document.getElementById('rf-pop-msg');
  var payload={account:acc, type:isEmp?'employee':'client'};
  if(isEmp){
    var eid=document.getElementById('rf-emp-sel').value;
    if(!eid){ msg.style.color='#c2761a'; msg.textContent='اختر الموظف المحيل (أو أضِفه)'; return; }
    payload.employee_id=parseInt(eid);
  }
  msg.style.color='var(--mu)'; msg.textContent='جارٍ الحفظ...';
  try{
    var r=await api('POST','/cards/referrer-name',payload);
    if(r&&r.success){
      var tr=document.querySelector('#rf-body .rf-row[data-acc="'+acc+'"]');
      if(tr){
        tr.setAttribute('data-type',r.type||(isEmp?'employee':'client'));
        tr.setAttribute('data-emp',r.employee_id||'');
        if(r.name){ tr.setAttribute('data-name',r.name); tr.setAttribute('data-named','1');
          var nc=tr.querySelector('.rf-name'); if(nc){ nc.textContent=r.name; nc.classList.remove('rf-unnamed'); } }
        var badge=tr.querySelector('.rf-type-badge');
        if(badge){ var emp=(r.type==='employee'); badge.className='rf-type-badge '+(emp?'emp':'cli'); badge.textContent=emp?'👤 موظف':'👥 عميل'; }
      }
      rfRecount();
      // update the segment counters
      var cli=0,emp=0; document.querySelectorAll('#rf-body .rf-row').forEach(function(t){ (t.getAttribute('data-type')==='employee'?emp++:cli++); });
      var ce=document.getElementById('rf-cli-n'); if(ce) ce.textContent=cli.toLocaleString('en-US');
      var ee=document.getElementById('rf-emp-n'); if(ee) ee.textContent=emp.toLocaleString('en-US');
      rfUpdateTypePanel();
      rfPopClose();
      if(typeof toast==='function') toast('تم تصنيف المحيل','success');
    } else { msg.style.color='#e05050'; msg.textContent=r.message||'تعذّر الحفظ'; }
  }catch(e){ msg.style.color='#e05050'; msg.textContent='خطأ بالحفظ'; }
}
</script>
@endpush
