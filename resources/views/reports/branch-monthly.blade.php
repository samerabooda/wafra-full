@extends('layouts.app')
@section('title','Branch Monthly Report')
@section('page-title','Branch Monthly Report')
@section('content')
@verbatim
<style>
.bmcal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:2147483000;display:none;align-items:center;justify-content:center}
.bmcal-overlay.open{display:flex}
.bmcal{width:340px;max-width:92vw;background:var(--card-bg);border:1px solid var(--brd2);border-radius:16px;padding:16px;box-shadow:0 24px 70px rgba(0,0,0,.4)}
.bmcal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px}
.bmcal-nav button{width:34px;height:34px;border-radius:9px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);font-size:16px;cursor:pointer}
.bmcal-y{font-size:17px;font-weight:900;color:var(--pri2)}
.bmcal-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:8px}
.bmcal-m{padding:11px 0;border-radius:10px;border:1px solid var(--brd1);background:var(--bg3);color:var(--tx);font-size:13px;font-weight:700;cursor:pointer;transition:all .12s}
.bmcal-m:hover{border-color:var(--pri);color:var(--pri2)}
.bmcal-m.on{background:linear-gradient(135deg,var(--pri2),var(--pri3));color:#fff;border-color:transparent}
</style>
@endverbatim

@php
  $bmMoney = fn($v) => '$' . number_format((float)$v, (fmod((float)$v,1)?1:0));
  $bmMonAr = ['Jan'=>'يناير','Feb'=>'فبراير','Mar'=>'مارس','Apr'=>'أبريل','May'=>'مايو','Jun'=>'يونيو','Jul'=>'يوليو','Aug'=>'أغسطس','Sep'=>'سبتمبر','Oct'=>'أكتوبر','Nov'=>'نوفمبر','Dec'=>'ديسمبر'];
  $bmMonEn = ['Jan'=>'January','Feb'=>'February','Mar'=>'March','Apr'=>'April','May'=>'May','Jun'=>'June','Jul'=>'July','Aug'=>'August','Sep'=>'September','Oct'=>'October','Nov'=>'November','Dec'=>'December'];
  $bmLabelAr = $bmMonthStr ? (($bmMonAr[$bmM] ?? $bmM) . ' ' . $bmY) : 'اختر الشهر';
  $bmLabelEn = $bmMonthStr ? (($bmMonEn[$bmM] ?? $bmM) . ' ' . $bmY) : 'Pick a month';
@endphp

<div style="display:flex;flex-direction:column;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- Reports Nav — TOP horizontal bar --}}
  <div style="width:100%;background:var(--bg2);border-bottom:1px solid var(--brd1);display:flex;flex-direction:row;flex-wrap:wrap;align-items:center;gap:8px;padding:11px 16px;overflow-x:auto;">
    @php
      $rNav = [
        ['reports.index','📊','التقارير الشهرية','Reports Dashboard', false],
        ['reports.table','📋','جدول البيانات','Data Table', false],
        ['reports.dynamic','🔧','تقرير ديناميكي','Dynamic Report', false],
        ['reports.branch-monthly','🏢','تقرير شهري للفروع','Branch Monthly Report', true],
        ['reports.referrals','🔗','تقرير المحيلين','Referrals Report', false],
      ];
    @endphp
    @foreach($rNav as [$rt,$ic,$tx_ar,$tx_en,$on])
      <a href="{{ route($rt) }}" style="display:flex;align-items:center;gap:7px;padding:9px 15px;border-radius:11px;text-decoration:none;font-size:13px;font-weight:700;white-space:nowrap;{{ $on ? 'background:linear-gradient(135deg,rgba(245,166,35,.22),rgba(245,166,35,.08));border:1px solid rgba(245,166,35,.45);color:var(--or)' : 'background:var(--bg3);border:1px solid var(--brd1);color:var(--tx)' }}">{{ $ic }} <span class="lang-ar">{{ $tx_ar }}</span><span class="lang-en">{{ $tx_en }}</span></a>
    @endforeach
  </div>

  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

    {{-- Filter --}}
    <div class="panel" style="padding:20px;margin-bottom:16px">
      <div class="panel-header" style="padding:0 0 14px;border-bottom:1px solid var(--brd1);margin-bottom:16px">
        <div class="panel-title"><span class="lang-ar">🏢 تقرير شهري للفروع</span><span class="lang-en">🏢 Branch Monthly Report</span></div>
      </div>
      <form method="GET" action="{{ route('reports.branch-monthly') }}" id="bm-form" style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        @if($bmIsFa)
        <div style="min-width:200px;flex:1">
          <label class="form-label"><span class="lang-ar">🏢 الفرع</span><span class="lang-en">🏢 Branch</span></label>
          <select name="branch_id" class="form-control">
            <option value=""><span class="lang-ar">— كل الفروع —</span><span class="lang-en">— All Branches —</span></option>
            @foreach($bmBranches as $b)
              <option value="{{ $b->id }}" @selected((string)($bmBranchId ?? '') === (string)$b->id)>{{ $b->name_ar ?: $b->name_en }}@if($b->name_en && $b->name_ar) <span style="font-size:10px;opacity:.6">/ {{ $b->name_en }}</span>@endif</option>
            @endforeach
          </select>
        </div>
        @endif
        <div style="min-width:200px">
          <label class="form-label"><span class="lang-ar">📅 الشهر والسنة</span><span class="lang-en">📅 Month & Year</span></label>
          <input type="hidden" name="m" id="bm-m" value="{{ $bmM }}">
          <input type="hidden" name="y" id="bm-y" value="{{ $bmY }}">
          <button type="button" class="form-control" onclick="bmCalOpen()" id="bm-cal-btn"
                  style="cursor:pointer;text-align:right;display:flex;align-items:center;gap:8px;width:100%;font-weight:700">
            <span>📅</span><span id="bm-cal-label" data-ar="{{ $bmLabelAr }}" data-en="{{ $bmLabelEn }}">{{ $bmLabelAr }}</span>
          </button>
        </div>
        <button class="btn btn-primary" type="submit"><span class="lang-ar">⚡ توليد التقرير</span><span class="lang-en">⚡ Generate Report</span></button>
        @if($bmRows->count())
        <button class="btn btn-ghost" type="button" onclick="bmExport()"><span class="lang-ar">📥 تصدير Excel</span><span class="lang-en">📥 Export Excel</span></button>
        @endif
      </form>
    </div>

    {{-- Results --}}
    @if(!$bmMonthStr)
      <div style="text-align:center;padding:60px 20px;color:var(--mu)">
        <div style="font-size:48px;margin-bottom:12px">🏢</div>
        <div style="font-size:15px;font-weight:700;margin-bottom:6px"><span class="lang-ar">اختر الفرع والشهر</span><span class="lang-en">Pick the branch and the month</span></div>
        <div style="font-size:12px"><span class="lang-ar">اضغط ⚡ توليد التقرير لعرض الكروت المُدخلة أو المُعدَّلة في الشهر المختار</span><span class="lang-en">Click ⚡ Generate Report to see cards added or edited in the selected month</span></div>
      </div>
    @else
      <div class="panel" style="padding:12px 16px;margin-bottom:14px;display:flex;gap:18px;flex-wrap:wrap;align-items:center">
        <div style="font-weight:800;color:var(--pri2)">📅 <span class="lang-ar">{{ $bmLabelAr }}</span><span class="lang-en">{{ $bmLabelEn }}</span></div>
        <div style="color:var(--mu);font-size:13px"><span class="lang-ar">عدد الكروت:</span><span class="lang-en">Cards:</span> <b style="color:var(--tx)">{{ number_format($bmRows->count()) }}</b></div>
        <div style="color:var(--mu);font-size:12px"><span class="lang-ar">🟡 الصفوف الصفراء = كروت تم تعديلها في هذا الشهر</span><span class="lang-en">🟡 Yellow rows = cards edited in this month</span></div>
      </div>
      <div class="panel" style="padding:0">
        <div class="table-scroll" style="max-height:64vh">
          <table class="data-table" id="bm-table">
            <thead><tr>
              <th><span class="lang-ar">رقم الحساب</span><span class="lang-en">Account No.</span></th>
              <th><span class="lang-ar">البروكر</span><span class="lang-en">Broker</span></th>
              <th><span class="lang-ar">عمولة البروكر</span><span class="lang-en">Broker Comm.</span></th>
              <th><span class="lang-ar">المسوّق الأول</span><span class="lang-en">Marketer 1</span></th>
              <th><span class="lang-ar">عمولة م1</span><span class="lang-en">M1 Comm.</span></th>
              <th><span class="lang-ar">المسوّق الثاني</span><span class="lang-en">Marketer 2</span></th>
              <th><span class="lang-ar">عمولة م2</span><span class="lang-en">M2 Comm.</span></th>
              <th><span class="lang-ar">المحيل</span><span class="lang-en">Referrer</span></th>
              <th><span class="lang-ar">عمولة المحيل</span><span class="lang-en">Ref. Comm.</span></th>
              <th><span class="lang-ar">الشهر</span><span class="lang-en">Month</span></th>
              <th><span class="lang-ar">الإيداع الأولي</span><span class="lang-en">Initial Deposit</span></th>
              <th><span class="lang-ar">نوع الحساب</span><span class="lang-en">Account Type</span></th>
            </tr></thead>
            <tbody>
              @forelse($bmRows as $r)
                @php $mod = ($r->modified_month === $bmMonthStr); @endphp
                <tr @if($mod) style="background:rgba(245,166,35,.14);border-right:3px solid #f5a623" @endif>
                  <td><span class="ac-num">{{ $r->account_number }}</span>@if($mod)<div style="font-size:10px;color:#f5a623;font-weight:800;margin-top:2px">✏️ <span class="lang-ar">معدّل</span><span class="lang-en">Edited</span></div>@endif</td>
                  <td style="color:var(--pri2);font-weight:700">{{ $r->broker_name ?: 'Self' }}</td>
                  <td class="mono">{{ $bmMoney($r->broker_commission) }}</td>
                  <td style="color:var(--m2);font-weight:600">{{ $r->m1_name ?: '—' }}</td>
                  <td class="mono">{{ $bmMoney($r->marketer_commission) }}</td>
                  <td style="color:var(--m2);font-weight:600">{{ $r->m2_name ?: '—' }}</td>
                  <td class="mono">{{ $bmMoney($r->ext_commission2) }}</td>
                  <td>@if($r->referral_account)<span class="bm-ref" onclick="bmRefPop(this)" data-acc="{{ $r->referral_account }}" data-name="{{ $r->referral_name }}" data-type="{{ $r->referral_type }}" style="cursor:pointer;color:var(--pri2);font-weight:800;border-bottom:1px dashed var(--pri)">{{ ($r->referral_type === 'employee' ? '👤 ' : '🔗 ') }}{{ $r->referral_account }}</span>@else — @endif</td>
                  <td class="mono">{{ $r->referral_account ? $bmMoney($r->referral_commission) : '—' }}</td>
                  <td style="color:var(--mu);white-space:nowrap">{{ $r->month ?: '—' }}</td>
                  <td class="mono c-green">{{ $r->initial_deposit > 0 ? number_format($r->initial_deposit) : '—' }}</td>
                  <td>
                    @if($r->account_kind === 'sub')
                      <span class="badge badge-blue"><span class="lang-ar">فرعي</span><span class="lang-en">Sub</span></span>
                      @if($r->parent_account)<div style="font-size:10px;color:var(--mu);margin-top:2px"><span class="lang-ar">أساسي:</span><span class="lang-en">Parent:</span> <b>{{ $r->parent_account }}</b></div>@endif
                    @else
                      <span class="badge badge-green"><span class="lang-ar">أساسي</span><span class="lang-en">Main</span></span>
                    @endif
                  </td>
                </tr>
              @empty
                <tr><td colspan="12" style="text-align:center;padding:40px;color:var(--mu)"><span class="lang-ar">لا توجد كروت في هذا الشهر</span><span class="lang-en">No cards in this month</span></td></tr>
              @endforelse
            </tbody>
          </table>
        </div>
      </div>
    @endif

  </div>
</div>

{{-- Month/Year calendar popup --}}
<div class="bmcal-overlay" id="bm-cal" onclick="if(event.target===this)bmCalClose()">
  <div class="bmcal">
    <div class="bmcal-nav">
      <button type="button" onclick="bmCalYear(-1)">‹</button>
      <span class="bmcal-y" id="bm-cal-year">2026</span>
      <button type="button" onclick="bmCalYear(1)">›</button>
    </div>
    <div class="bmcal-grid" id="bm-cal-grid"></div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function bmL(){ return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }
// Referrer account → click shows the saved name in a popover (same idea as the parent-account chip).
function bmRefClose(){ var p=document.getElementById('bm-ref-pop'); if(p) p.remove(); document.removeEventListener('click',bmRefOutside,true); }
function bmRefOutside(e){ var p=document.getElementById('bm-ref-pop'); if(p && !p.contains(e.target) && !(e.target.classList&&e.target.classList.contains('bm-ref'))) bmRefClose(); }
function bmRefPop(el){
  if(event && event.stopPropagation) event.stopPropagation();
  bmRefClose();
  var acc=el.getAttribute('data-acc'), name=(el.getAttribute('data-name')||'').trim(), emp=(el.getAttribute('data-type')==='employee');
  var isEn = bmL()==='en';
  var pop=document.createElement('div'); pop.id='bm-ref-pop';
  pop.style.cssText='position:fixed;z-index:99999;background:var(--card-bg,#fff);border:1.5px solid '+(emp?'#7b68ee':'#1AADBA')+';border-radius:12px;padding:9px 13px;box-shadow:0 12px 34px rgba(0,0,0,.28);font-size:13px;font-weight:800;color:var(--tx,#222);white-space:nowrap;display:flex;align-items:center;gap:8px';
  var lblEmp = isEn ? 'Referrer employee: ' : 'الموظف المحيل: ';
  var lblRef = isEn ? 'Referrer: ' : 'المحيل: ';
  var noName = isEn ? 'No name — classify from the Referrals report' : 'بدون اسم — صنّفه من تقرير المحيلين';
  pop.innerHTML = (emp?'👤':'🔗')+' '+(name ? (emp?lblEmp:lblRef)+'<span style="color:'+(emp?'#5b46d6':'#0E7A88')+'">'+name+'</span>' : '<span style="color:var(--mu,#888)">'+noName+'</span>')+' <span class="mono" style="color:var(--mu);font-weight:600">#'+acc+'</span>';
  document.body.appendChild(pop);
  var r=el.getBoundingClientRect(), pw=pop.offsetWidth, ph=pop.offsetHeight;
  var top=r.top-ph-8; if(top<8) top=r.bottom+8;
  pop.style.top=top+'px'; pop.style.left=Math.max(8,Math.min(r.left+r.width/2-pw/2, window.innerWidth-pw-8))+'px';
  setTimeout(function(){ document.addEventListener('click',bmRefOutside,true); },0);
  setTimeout(function(){ if(pop.parentNode) pop.remove(); }, 4500);
}
var BM_MONTHS=['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
var BM_AR=['يناير','فبراير','مارس','أبريل','مايو','يونيو','يوليو','أغسطس','سبتمبر','أكتوبر','نوفمبر','ديسمبر'];
var BM_EN=['January','February','March','April','May','June','July','August','September','October','November','December'];
var _bmCalY = parseInt(document.getElementById('bm-y')?.value) || new Date().getFullYear();
function bmCalOpen(){
  _bmCalY = parseInt(document.getElementById('bm-y').value) || new Date().getFullYear();
  bmCalRender();
  document.getElementById('bm-cal').classList.add('open');
}
function bmCalClose(){ document.getElementById('bm-cal').classList.remove('open'); }
function bmCalYear(d){ _bmCalY += d; bmCalRender(); }
function bmCalRender(){
  var isEn = bmL()==='en';
  var names = isEn ? BM_EN : BM_AR;
  document.getElementById('bm-cal-year').textContent = _bmCalY;
  var selM = document.getElementById('bm-m').value, selY = document.getElementById('bm-y').value;
  document.getElementById('bm-cal-grid').innerHTML = BM_MONTHS.map(function(m,i){
    var on = (m===selM && String(_bmCalY)===String(selY));
    return '<button type="button" class="bmcal-m'+(on?' on':'')+'" onclick="bmCalPick(\''+m+'\')">'+names[i]+'</button>';
  }).join('');
}
function bmCalPick(m){
  document.getElementById('bm-m').value = m;
  document.getElementById('bm-y').value = _bmCalY;
  var i = BM_MONTHS.indexOf(m);
  var isEn = bmL()==='en';
  document.getElementById('bm-cal-label').textContent = (isEn?BM_EN[i]:BM_AR[i]) + ' ' + _bmCalY;
  bmCalClose();
  // auto-generate
  document.getElementById('bm-form').submit();
}
// Update the picker label on language switch (reads data-ar/data-en + re-renders grid if open)
function bmApplyLang(){
  var lbl = document.getElementById('bm-cal-label');
  if(lbl){ var ar=lbl.getAttribute('data-ar')||'', en=lbl.getAttribute('data-en')||''; lbl.textContent = bmL()==='en' ? en : ar; }
  if(document.getElementById('bm-cal').classList.contains('open')) bmCalRender();
}
var _bmOrig=window.applyLang; window.applyLang=function(l){ if(_bmOrig)_bmOrig(l); try{ bmApplyLang(); }catch(e){ console.warn(e); } };
bmApplyLang();

function bmExport(){
  var rows=[], t=document.getElementById('bm-table'); if(!t) return;
  t.querySelectorAll('tr').forEach(function(tr){
    var cells=tr.querySelectorAll('th,td');
    rows.push(Array.prototype.map.call(cells,function(c){return c.innerText.replace(/\s+/g,' ').trim();}));
  });
  var sheet = bmL()==='en' ? 'Branch Report' : 'تقرير الفروع';
  wgExportExcel(rows, 'WafraBranchMonthly_'+new Date().toISOString().slice(0,10)+'.xlsx', sheet,
    { colColors:{ 0:'EAF3DE', 2:'FAEEDA', 4:'FAEEDA', 6:'FAEEDA', 8:'FAEEDA', 9:'EEEDFE', 10:'E6F1FB' } });
}
</script>
@endpush
