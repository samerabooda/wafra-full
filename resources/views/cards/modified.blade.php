@extends('layouts.app')
@section('title','Modified Accounts')
@section('page-title','Modified Accounts')
@section('content')
<div style="display:block;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'modified', 'tabs' => ['index', 'modified']])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

<div id="mod-info-bar" style="background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.25);border-radius:11px;padding:11px 15px;margin-bottom:14px;font-size:12px;color:var(--or)">
  ✏️ <span id="mod-info-text">جميع الحسابات التي تم تعديلها — تظهر بتمييز <strong>أصفر</strong> في التقارير.</span>
</div>

<div class="panel">
  <div class="panel-header">
    <div class="panel-title" id="mod-panel-title">✏️ الحسابات المعدّلة <span id="mod-count" style="font-size:11px;color:var(--mu)">{{ count($mods ?? []) }} سجل</span></div>
    <div style="display:flex;gap:6px">
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportModExcel()">📗 Excel</button>
    </div>
  </div>
  {{-- Filters (all client-side over the rendered rows) --}}
  @php $mfSel = 'font-size:12.5px;font-weight:700;padding:7px 11px;border-radius:9px;border:1px solid var(--brd2);background:var(--inp-bg);color:var(--tx);cursor:pointer'; @endphp
  <div style="padding:10px 16px;border-bottom:1px solid var(--brd1);display:flex;gap:8px;align-items:center;background:var(--bg4);flex-wrap:wrap">
    <select id="mf-branch" onchange="applyModFilters()" style="{{ $mfSel }}"><option value="">🏢 كل الفروع</option></select>
    <select id="mf-month"  onchange="applyModFilters()" style="{{ $mfSel }}"><option value="">📅 كل أشهر التعديل</option></select>
    <select id="mf-reason" onchange="applyModFilters()" style="{{ $mfSel }}"><option value="">📝 كل الأسباب</option></select>
    <span style="font-size:15px;opacity:.5">🔍</span>
    <input type="search" id="mod-search" autocomplete="off" data-lpignore="true" readonly onfocus="this.removeAttribute('readonly')" class="form-control" style="flex:1;min-width:140px;font-size:14px;background:none;border:none;outline:none;padding:6px 4px" placeholder="بحث برقم الحساب..." oninput="applyModFilters()">
    <button onclick="clearModFilters()" style="font-size:11.5px;font-weight:700;padding:7px 11px;border-radius:9px;border:1px solid rgba(239,68,68,.3);background:rgba(239,68,68,.08);color:#ef4444;cursor:pointer">✕ مسح</button>
    <span id="mod-result-count" style="font-size:12px;color:var(--mu);white-space:nowrap"></span>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead>
        <tr>
          <th>الفرع</th>
          <th>شهر التعديل</th>
          <th>رقم الحساب</th>
          <th>شهر فتح الحساب</th>
          <th>بيانات ما قبل التعديل</th>
          <th>سبب التعديل</th>
          <th>بيانات ما بعد التعديل</th>
        </tr>
      </thead>
      <tbody id="mod-tbody">
        @php
          $sideHtml = function($lines){
            if(empty($lines)) return '<span style="color:var(--mu)">—</span>';
            $out='';
            foreach($lines as $ln){
              $c = $ln['changed'] ? 'color:#e05050;font-weight:800' : 'color:var(--tx)';
              $out.='<div style="font-size:11.5px;line-height:1.7"><span style="color:var(--mu)">'.e($ln['lbl']).':</span> <span style="'.$c.'">'.e($ln['val']).'</span></div>';
            }
            return $out;
          };
        @endphp
        @forelse(($mods ?? []) as $m)
          <tr class="row-modified mod-row"
              data-branch="{{ $m->branch_ar ?: ($m->branch_en ?: '') }}"
              data-month="{{ $m->mod_month }}"
              data-reason="{{ $m->reason ?: '' }}"
              data-s="{{ strtolower(($m->account_number ?? '').' '.(($m->branch_ar ?? '')).' '.($m->reason ?? '')) }}">
            <td><span class="rc-pill" style="white-space:nowrap">🏢 {{ $m->branch_ar ?: ($m->branch_en ?: '—') }}</span></td>
            <td style="color:var(--or);font-weight:700;white-space:nowrap">{{ $m->mod_month }}</td>
            <td><span class="ac-num">{{ $m->account_number ?: '—' }}</span></td>
            <td style="color:var(--mu);white-space:nowrap">{{ $m->month ?: '—' }}</td>
            <td style="min-width:200px">{!! $sideHtml($m->before) !!}</td>
            <td style="color:var(--or);font-weight:700;min-width:110px">{{ $m->reason ?: '—' }}</td>
            <td style="min-width:200px">{!! $sideHtml($m->after) !!}</td>
          </tr>
        @empty
          <tr id="mod-empty-row"><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا توجد حسابات معدّلة</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
</div>{{-- content panel --}}
</div>{{-- cards shell --}}

@endsection
@push('scripts')
<script>
// Rows are rendered SERVER-SIDE (PHP) — JS here only filters & exports them. No fetch, no
// dependency on the API, so the table can never get stuck on "loading".
function modL(){ return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar'; }

// ── Client-side filters over the server-rendered rows ──
function _mfVal(id){ var e=document.getElementById(id); return e?e.value:''; }
function _mfPopulate(id, attr){
  var sel=document.getElementById(id); if(!sel) return;
  var seen={};
  document.querySelectorAll('#mod-tbody .mod-row').forEach(function(tr){
    var v=(tr.getAttribute(attr)||'').trim(); if(v) seen[v]=1;
  });
  Object.keys(seen).sort().forEach(function(v){
    var o=document.createElement('option'); o.value=v; o.textContent=v; sel.appendChild(o);
  });
}
function applyModFilters(){
  var b=_mfVal('mf-branch'), mo=_mfVal('mf-month'), re=_mfVal('mf-reason'),
      q=(_mfVal('mod-search')||'').toLowerCase().trim();
  var rows=document.querySelectorAll('#mod-tbody .mod-row'), shown=0;
  rows.forEach(function(tr){
    var ok = (!b  || tr.getAttribute('data-branch')===b)
          && (!mo || tr.getAttribute('data-month')===mo)
          && (!re || tr.getAttribute('data-reason')===re)
          && (!q  || (tr.getAttribute('data-s')||'').indexOf(q)>=0);
    tr.style.display=ok?'':'none'; if(ok) shown++;
  });
  var cnt=document.getElementById('mod-result-count');
  if(cnt) cnt.textContent=shown+' / '+rows.length;
}
function clearModFilters(){
  ['mf-branch','mf-month','mf-reason','mod-search'].forEach(function(id){ var e=document.getElementById(id); if(e) e.value=''; });
  applyModFilters();
}
// Populate filter dropdowns from the rendered rows on load.
(function(){
  _mfPopulate('mf-branch','data-branch');
  _mfPopulate('mf-month','data-month');
  _mfPopulate('mf-reason','data-reason');
})();

// Export the visible rows to Excel (reads straight from the table DOM).
function exportModExcel(){
  var trs=Array.prototype.slice.call(document.querySelectorAll('#mod-tbody .mod-row'))
            .filter(function(tr){ return tr.style.display!=='none'; });
  if(!trs.length){ if(window.toast) toast(modL()==='en'?'No data to export':'لا توجد بيانات للتصدير','error'); return; }
  var hdr=['الفرع','شهر التعديل','رقم الحساب','شهر فتح الحساب','قبل التعديل','سبب التعديل','بعد التعديل'];
  var aoa=[hdr];
  trs.forEach(function(tr){
    var c=tr.querySelectorAll('td');
    aoa.push([0,1,2,3,4,5,6].map(function(i){ return (c[i]?c[i].innerText:'').replace(/\s+/g,' ').trim(); }));
  });
  wgExportExcel(aoa, 'WafraGulf_Modified_'+new Date().toISOString().slice(0,10)+'.xlsx', 'الحسابات المعدّلة',
    { headerColor:'B25A0E', colColors:{2:'EAF3DE', 5:'FAEEDA'} });
}
</script>
@endpush
