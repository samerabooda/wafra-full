@extends('layouts.app')
@section('title','Acquisition Comparison')
@section('page-title','Acquisition Comparison')
@section('content')

@php
  $pct = fn($n,$d) => $d ? round($n/$d*100) : 0;
  $PAL = ['#1AADBA','#7b68ee','#22a06b','#c2761a','#2563EB','#e0708a','#0E7A88','#8A2BE2','#f5a623','#5b46d6'];
@endphp

@verbatim
<style>
.aq{max-width:1280px;margin:0 auto}
.aq-nav{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:16px;align-items:center}
.aq-nav a{display:flex;align-items:center;gap:7px;padding:9px 16px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;border:1px solid var(--brd1);background:var(--bg2);color:var(--tx);white-space:nowrap}
.aq-nav a:hover{border-color:var(--pri);color:var(--pri2);transform:translateY(-1px)}
.aq-nav a.on{background:linear-gradient(135deg,rgba(194,118,26,.22),rgba(194,118,26,.08));border-color:rgba(194,118,26,.5);color:#c2761a}
.aq-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;margin-bottom:16px}
.aq-kpi{border-radius:16px;padding:16px 20px;color:#fff;box-shadow:0 6px 20px rgba(0,0,0,.14)}
.aq-kpi .l{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.6px;opacity:.9;margin-bottom:6px}
.aq-kpi .v{font-size:1.6rem;font-weight:900;font-family:'JetBrains Mono',monospace;line-height:1.05}
.aq-kpi .s{font-size:11px;opacity:.85;margin-top:3px}
.aq-2{display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px}
.aq-card{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;padding:18px}
.aq-pt{font-size:14px;font-weight:800;color:var(--pri2);margin-bottom:14px;display:flex;align-items:center;gap:8px}
/* glass bars (reused style — client/visitor duo) */
.aq-gbar{position:relative;display:flex;align-items:center;justify-content:space-between;height:40px;border-radius:12px;margin-bottom:10px;background:var(--bg2);border:1px solid var(--brd1);overflow:hidden}
.aq-gbar-fill{position:absolute;top:0;bottom:0;right:0;border-radius:12px;border:1px solid;-webkit-backdrop-filter:blur(10px) saturate(140%);backdrop-filter:blur(10px) saturate(140%);box-shadow:inset 0 1px 0 rgba(255,255,255,.18)}
.aq-gbar-name{position:relative;z-index:1;padding:0 14px;font-weight:800;font-size:13px;color:var(--tx);white-space:nowrap;display:flex;align-items:center;gap:6px}
.aq-gbar-num{position:relative;z-index:1;padding:0 14px;font-weight:900;font-size:12.5px;white-space:nowrap}
table.aq-tbl{width:100%;border-collapse:collapse;font-size:13px}
table.aq-tbl th{padding:11px 12px;font-size:11px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;border-bottom:2px solid var(--pri);background:var(--bg3);position:sticky;top:0}
table.aq-tbl td{padding:10px 12px;border-top:1px solid var(--brd1)}
.aq-ratio{display:flex;gap:3px;align-items:center;height:12px;width:160px;border-radius:7px;overflow:hidden;background:rgba(127,143,160,.14)}
.aq-ratio-p{height:100%;background:linear-gradient(90deg,#1AADBA,#0E7A88)}
.aq-ratio-w{height:100%;background:linear-gradient(90deg,#c2761a,#9a5a12)}
.aq-pill{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:12px;font-size:11px;font-weight:800}
.aq-pill.p{background:rgba(26,173,186,.14);color:#0E7A88;border:1px solid rgba(26,173,186,.3)}
.aq-pill.w{background:rgba(245,166,35,.14);color:#c2761a;border:1px dashed rgba(194,118,26,.5)}
.aq-empty{text-align:center;padding:48px 24px;color:var(--mu);font-size:13.5px}
.aq-banner-missing{background:rgba(245,166,35,.1);border:1px solid rgba(194,118,26,.4);border-radius:12px;padding:12px 16px;margin-bottom:16px;font-size:12.5px;color:#8b5a2b}
@media(max-width:1000px){.aq-kpis{grid-template-columns:1fr 1fr}.aq-2{grid-template-columns:1fr}}
</style>
@endverbatim

<div class="aq">
  {{-- Reports nav --}}
  <div class="aq-nav">
    <a href="{{ route('reports.index') }}">📊 <span class="lang-ar">لوحة التقارير</span><span class="lang-en">Reports Dashboard</span></a>
    <a href="{{ route('reports.table') }}">📋 <span class="lang-ar">جدول البيانات</span><span class="lang-en">Data Table</span></a>
    <a href="{{ route('reports.dynamic') }}">🔧 <span class="lang-ar">تقرير ديناميكي</span><span class="lang-en">Dynamic Report</span></a>
    <a href="{{ route('reports.branch-monthly') }}">🏢 <span class="lang-ar">تقرير الفروع</span><span class="lang-en">Branch Report</span></a>
    <a href="{{ route('reports.referrals') }}">🔗 <span class="lang-ar">تقرير المحيلين</span><span class="lang-en">Referrals Report</span></a>
    <a href="{{ route('reports.acq-comparison') }}" class="on">👤 <span class="lang-ar">مُستقطَب vs زائر</span><span class="lang-en">Prospected vs Visitor</span></a>
    <div style="flex:1"></div>
    @if($acqIsFa)
    <form method="GET" action="{{ route('reports.acq-comparison') }}" style="margin:0">
      <select name="branch_id" onchange="this.form.submit()" style="font-size:12.5px;font-weight:700;padding:8px 12px;border-radius:10px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);cursor:pointer">
        <option value=""><span class="lang-ar">🏢 كل الفروع</span><span class="lang-en">🏢 All Branches</span></option>
        @foreach($acqBranches as $b)
          <option value="{{ $b->id }}" @selected((string)($acqBranchId ?? '') === (string)$b->id)>{{ $b->name_ar ?: $b->name_en }}</option>
        @endforeach
      </select>
    </form>
    @endif
  </div>

  @if($acqColMissing)
    <div class="aq-banner-missing">
      ⚠️ <span class="lang-ar">عمود <code>acquisition_type</code> غير موجود في قاعدة البيانات بعد. أعد تشغيل الـmigrations.</span><span class="lang-en">The <code>acquisition_type</code> column has not been migrated yet. Run the pending migrations first.</span>
    </div>
  @endif

  {{-- KPIs --}}
  <div class="aq-kpis" style="grid-template-columns:repeat(4,1fr)">
    <div class="aq-kpi" style="background:linear-gradient(135deg,#1AADBA,#0E7A88)">
      <div class="l"><span class="lang-ar">إجمالي الحسابات</span><span class="lang-en">Total Accounts</span></div>
      <div class="v">{{ number_format($acqTotals['total']) }}</div>
    </div>
    <div class="aq-kpi" style="background:linear-gradient(135deg,#0E7A88,#0b5f6a)">
      <div class="l">🎯 <span class="lang-ar">مُستقطَب</span><span class="lang-en">Prospected</span></div>
      <div class="v">{{ number_format($acqTotals['prospected']) }}</div>
      <div class="s">{{ $pct($acqTotals['prospected'], $acqTotals['total']) }}% <span class="lang-ar">من الإجمالي</span><span class="lang-en">of total</span></div>
    </div>
    <div class="aq-kpi" style="background:linear-gradient(135deg,#c2761a,#9a5a12)">
      <div class="l">🚶 <span class="lang-ar">زائر</span><span class="lang-en">Visitor</span></div>
      <div class="v">{{ number_format($acqTotals['walkin']) }}</div>
      <div class="s">{{ $acqTotals['pct_walkin'] }}% <span class="lang-ar">من الإجمالي</span><span class="lang-en">of total</span></div>
    </div>
    <div class="aq-kpi" style="background:linear-gradient(135deg,#7b68ee,#5b46d6)">
      <div class="l"><span class="lang-ar">نسبة الاستقطاب الفعّال</span><span class="lang-en">Effective Prospecting Ratio</span></div>
      <div class="v">{{ 100 - $acqTotals['pct_walkin'] }}%</div>
      <div class="s"><span class="lang-ar">من البطاقات</span><span class="lang-en">of cards</span></div>
    </div>
  </div>

  {{-- Pie chart + per-branch glass bars --}}
  <div class="aq-2">
    <div class="aq-card">
      <div class="aq-pt"><span class="lang-ar">🥧 التوزيع العام</span><span class="lang-en">🥧 Overall Split</span></div>
      <div style="position:relative;min-height:280px">
        <canvas id="aq-pie-c" height="260"></canvas>
      </div>
    </div>
    <div class="aq-card">
      <div class="aq-pt"><span class="lang-ar">🏢 التوزيع حسب الفرع</span><span class="lang-en">🏢 Split by Branch</span></div>
      @forelse($acqByBranch as $i => $br)
        @php
          $pB = (int) $br->prospected; $wB = (int) $br->walkin; $tB = (int) $br->total;
          $pctP = $pct($pB, $tB); $pctW = $pct($wB, $tB);
        @endphp
        <div class="aq-gbar">
          <i class="aq-gbar-fill" style="width:{{ max(8, $pctP) }}%;background:#1AADBA1a;border-color:#1AADBA"></i>
          <span class="aq-gbar-name">🏢 {{ $br->nar }}</span>
          <span class="aq-gbar-num" style="color:#0E7A88">
            🎯 {{ number_format($pB) }} ({{ $pctP }}%) &middot;
            <span style="color:#c2761a">🚶 {{ number_format($wB) }} ({{ $pctW }}%)</span>
          </span>
        </div>
      @empty
        <div class="aq-empty"><span class="lang-ar">لا توجد بيانات.</span><span class="lang-en">No data.</span></div>
      @endforelse
    </div>
  </div>

  {{-- Per-marketer table --}}
  <div class="aq-card" style="padding:0;overflow:hidden">
    <div style="padding:14px 18px;display:flex;align-items:center;gap:10px;border-bottom:1px solid var(--brd1);flex-wrap:wrap">
      <div class="aq-pt" style="margin:0"><span class="lang-ar">📋 مقارنة أداء المسوّقين</span><span class="lang-en">📋 Marketer Performance</span></div>
      <div style="font-size:12px;color:var(--mu)"><span class="lang-ar">(أعلى 80 مسوّقاً)</span><span class="lang-en">(top 80 marketers)</span></div>
      <div style="flex:1"></div>
      <button type="button" onclick="aqExport()" style="font-size:12.5px;font-weight:800;padding:8px 14px;border-radius:10px;border:0;background:linear-gradient(135deg,#1f8a4c,#146636);color:#fff;cursor:pointer">
        ⬇️ <span class="lang-ar">تنزيل Excel</span><span class="lang-en">Download Excel</span>
      </button>
    </div>
    <div style="overflow:auto;max-height:620px">
      <table class="aq-tbl" id="aq-mk-tbl">
        <thead><tr>
          <th>#</th>
          <th><span class="lang-ar">المسوّق</span><span class="lang-en">Marketer</span></th>
          <th><span class="lang-ar">الإجمالي</span><span class="lang-en">Total</span></th>
          <th><span class="lang-ar">🎯 مُستقطَب</span><span class="lang-en">🎯 Prospected</span></th>
          <th><span class="lang-ar">🚶 زائر</span><span class="lang-en">🚶 Visitor</span></th>
          <th><span class="lang-ar">نسبة الاستقطاب</span><span class="lang-en">Prospecting %</span></th>
          <th><span class="lang-ar">إجمالي العمولات</span><span class="lang-en">Total Commission</span></th>
        </tr></thead>
        <tbody>
          @forelse($acqByMarketer as $i => $m)
            @php
              $tM = (int) $m->total; $pM = (int) $m->prospected; $wM = (int) $m->walkin;
              $pctP = $pct($pM, $tM); $pctW = 100 - $pctP;
            @endphp
            <tr>
              <td style="color:var(--mu);font-size:11px">{{ $i + 1 }}</td>
              <td style="font-weight:800;color:var(--pri2)">{{ $m->nm ?: '—' }}</td>
              <td><b>{{ number_format($tM) }}</b></td>
              <td><span class="aq-pill p">🎯 {{ number_format($pM) }}</span></td>
              <td>@if($wM > 0)<span class="aq-pill w">🚶 {{ number_format($wM) }}</span>@else <span style="color:var(--mu)">—</span> @endif</td>
              <td>
                <div style="display:flex;align-items:center;gap:8px">
                  <div class="aq-ratio">
                    <div class="aq-ratio-p" style="width:{{ $pctP }}%"></div>
                    <div class="aq-ratio-w" style="width:{{ $pctW }}%"></div>
                  </div>
                  <b style="color:{{ $pctP >= 70 ? '#22a06b' : ($pctP >= 40 ? '#f5a623' : '#c2761a') }}">{{ $pctP }}%</b>
                </div>
              </td>
              <td class="mono" style="color:#22a06b;font-weight:800">${{ number_format((float) $m->total_comm, 2) }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="aq-empty"><span class="lang-ar">لا توجد بيانات للمسوّقين.</span><span class="lang-en">No marketer data.</span></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  <div style="height:80px"></div>
</div>

@endsection

@push('scripts')
<script>
function aqL(){ return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }

// Pie chart (Chart.js — already loaded by the app layout)
(function(){
  var canvas = document.getElementById('aq-pie-c'); if (!canvas || !window.Chart) return;
  var pr = {{ (int) $acqTotals['prospected'] }}, wk = {{ (int) $acqTotals['walkin'] }};
  if (pr + wk === 0) { canvas.parentNode.innerHTML = '<div class="aq-empty">—</div>'; return; }
  var isEn = aqL() === 'en';
  new Chart(canvas, {
    type: 'pie',
    data: {
      labels: [isEn ? '🎯 Prospected' : '🎯 مُستقطَب', isEn ? '🚶 Visitor' : '🚶 زائر'],
      datasets: [{
        data: [pr, wk],
        backgroundColor: ['#1AADBA', '#c2761a'],
        borderColor: 'var(--card-bg)',
        borderWidth: 3,
        hoverOffset: 10
      }]
    },
    options: {
      plugins: {
        legend: { position: 'bottom', labels: { font: { size: 13, weight: '800' }, padding: 15 } },
        tooltip: {
          callbacks: {
            label: function(ctx){
              var tot = ctx.dataset.data.reduce(function(a,b){return a+b;}, 0);
              var p = tot ? Math.round(ctx.parsed / tot * 100) : 0;
              return ctx.label + ': ' + ctx.parsed.toLocaleString('en') + ' (' + p + '%)';
            }
          }
        }
      },
      animation: { animateRotate: true, duration: 900 }
    }
  });
})();

// Excel export of the marketer table (reads the DOM so filters are respected)
function aqExport(){
  var t = document.getElementById('aq-mk-tbl'); if (!t) return;
  var isEn = aqL() === 'en';
  var hdr = isEn
    ? ['#','Marketer','Total','🎯 Prospected','🚶 Visitor','Prospecting %','Total Commission']
    : ['#','المسوّق','الإجمالي','🎯 مُستقطَب','🚶 زائر','نسبة الاستقطاب','إجمالي العمولات'];
  var aoa = [hdr];
  t.querySelectorAll('tbody tr').forEach(function(tr){
    var c = tr.querySelectorAll('td');
    if (c.length < 7) return;
    aoa.push([0,1,2,3,4,5,6].map(function(i){ return (c[i].innerText || '').replace(/\s+/g,' ').trim(); }));
  });
  var sheet = isEn ? 'Acquisition Comparison' : 'مُستقطَب vs زائر';
  var fname = (isEn ? 'Acquisition_Comparison_' : 'مستقطب_vs_زائر_') + new Date().toISOString().slice(0,10) + '.xlsx';
  if (typeof wgExportExcel === 'function') wgExportExcel(aoa, fname, sheet, { headerColor:'C2761A' });
  else { alert(isEn ? 'Export helper unavailable.' : 'أداة التصدير غير متاحة.'); }
}

// Top-bar + the main tb-title text follows the current language
function aqApplyLang(){
  var tb = document.querySelector('.tb-title'); if (tb) tb.textContent = aqL()==='en' ? 'Acquisition Comparison' : 'مُستقطَب vs زائر';
}
var _aqOrig = window.applyLang; window.applyLang = function(l){ if (_aqOrig) _aqOrig(l); try { aqApplyLang(); } catch(e){} };
aqApplyLang();
</script>
@endpush
