@extends('layouts.app')
@section('title','Account Search')
@section('page-title','Account Search')
@section('content')
<div style="display:block;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'search'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

{{-- Search bar --}}
<div class="panel" style="padding:20px;margin-bottom:18px">
  <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
    <div style="flex:1;min-width:220px">
      <label class="form-label" id="srch-lbl">رقم الحساب / Account Number</label>
      <div style="position:relative">
        <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:16px;pointer-events:none">🔍</span>
        <input type="search" id="srch-input" name="srch_nofill" class="form-control"
               autocomplete="off" data-lpignore="true" data-form-type="other" readonly onfocus="this.removeAttribute('readonly')"
               placeholder="أدخل رقم الحساب..."
               oninput="schedSearch()" onkeydown="if(event.key==='Enter')doSearch()"
               dir="ltr" autocomplete="off" spellcheck="false"
               style="padding-right:42px;font-family:'JetBrains Mono',monospace;font-size:16px;letter-spacing:1px">
      </div>
    </div>
    <button class="btn btn-primary" onclick="doSearch()" id="srch-btn">🔍 <span id="srch-btn-lbl">بحث</span></button>
    <button class="btn btn-ghost" onclick="clearSearch()" id="srch-clear-btn" style="display:none">✕ <span id="srch-clear-lbl">مسح</span></button>
  </div>
  <div id="srch-scope-note" style="margin-top:10px;font-size:12px;color:var(--mu);display:none">
    <span id="srch-scope-txt"></span>
  </div>
</div>

{{-- Results area --}}
<div id="srch-results">
  <div style="text-align:center;padding:60px 20px;color:var(--mu)">
    <div style="font-size:48px;margin-bottom:16px">🔍</div>
    <div style="font-size:16px;font-weight:700;margin-bottom:8px" id="srch-empty-title">ابدأ بالبحث</div>
    <div style="font-size:13px" id="srch-empty-sub">أدخل رقم الحساب في الحقل أعلاه للبحث عن تفاصيله</div>
  </div>
</div>

</div>{{-- content panel --}}
</div>{{-- cards shell --}}
@endsection

@push('scripts')
<script>
const SRCH = {
  ar: {
    lbl:'رقم الحساب / Account Number',
    btn:'بحث', clear:'مسح',
    emptyTitle:'ابدأ بالبحث', emptySub:'أدخل رقم الحساب في الحقل أعلاه للبحث عن تفاصيله',
    loading:'جاري البحث...', noResult:'لم يُعثر على نتائج لـ',
    found:'نتيجة لـ', results:'نتيجة',
    scopeFA:'🔓 أنت مدير مالي — يمكنك رؤية جميع الحسابات في كل الفروع',
    scopeBM:'🔒 أنت مدير فرع — تظهر الحسابات الخاصة بفرعك فقط',
    thAc:'رقم الحساب', thMonth:'الشهر', thBranch:'الفرع',
    thBroker:'البروكر', thBComm:'ع. بروكر',
    thMktr:'مسوّق داخلي', thMComm:'ع. داخلي',
    thExt1:'مسوّق خارجي 1', thE1Comm:'ع. خارجي 1',
    thExt2:'مسوّق خارجي 2', thE2Comm:'ع. خارجي 2',
    thDep:'إيداع أولي', thMon:'إيداع شهري',
    thStatus:'الحالة', thType:'نوع الحساب',
    stMod:'✏️ معدّل', stNormal:'عادي',
    btnEdit:'✏️ تعديل',
    totalComm:'إجمالي العمولة',
  },
  en: {
    lbl:'Account Number',
    btn:'Search', clear:'Clear',
    emptyTitle:'Start Searching', emptySub:'Enter an account number above to find its details',
    loading:'Searching...', noResult:'No results for',
    found:'result(s) for', results:'results',
    scopeFA:'🔓 Finance Admin — you can see all accounts across all branches',
    scopeBM:'🔒 Branch Manager — showing only your branch accounts',
    thAc:'Account No.', thMonth:'Month', thBranch:'Branch',
    thBroker:'Broker', thBComm:'B.Comm',
    thMktr:'Int. Marketer', thMComm:'Int. Comm',
    thExt1:'Ext. Mktr 1', thE1Comm:'Ext 1 Comm',
    thExt2:'Ext. Mktr 2', thE2Comm:'Ext 2 Comm',
    thDep:'Initial Dep.', thMon:'Monthly Dep.',
    thStatus:'Status', thType:'Type',
    stMod:'✏️ Modified', stNormal:'Active',
    btnEdit:'✏️ Edit',
    totalComm:'Total Commission',
  }
};

function srchL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function s(k)    { const l = srchL(); return SRCH[l]?.[k] ?? SRCH.ar[k] ?? k; }
// Local fmt — the layout's `const fmt` is not in this script's scope (was "fmt is not defined").
function fmt(n){ n = Number(n); return (n > 0) ? n.toLocaleString('en', {maximumFractionDigits:0}) : '—'; }

function srchApplyLang() {
  const el = id => document.getElementById(id);
  const _t = (id, v) => { const e = el(id); if (e) e.textContent = v; };
  _t('srch-lbl', s('lbl'));
  _t('srch-btn-lbl', s('btn'));
  _t('srch-clear-lbl', s('clear'));
  _t('srch-empty-title', s('emptyTitle'));
  _t('srch-empty-sub', s('emptySub'));
  // scope note
  const isFa = CURRENT_USER?.role === 'finance_admin';
  const note = el('srch-scope-note');
  const txt  = el('srch-scope-txt');
  if (txt) txt.textContent = isFa ? s('scopeFA') : s('scopeBM');
  if (note) note.style.display = '';
  // re-render if there are results
  if (_srchData.length) renderResults(_srchData, _srchTerm);
  // Placeholder
  const inp = document.getElementById('srch-input');
  if (inp) inp.placeholder = srchL()==='en' ? 'Enter account number...' : 'أدخل رقم الحساب...';
}

const _srchOrig = window.applyLang;
window.applyLang = function(lang) { if (_srchOrig) _srchOrig(lang); srchApplyLang(); };

let _srchData  = [];
let _srchTerm  = '';
let _srchTimer = null;

function schedSearch() {
  clearTimeout(_srchTimer);
  _srchTimer = setTimeout(doSearch, 500);
}

async function doSearch() {
  clearTimeout(_srchTimer);
  const term = document.getElementById('srch-input').value.trim();
  if (!term) { clearSearch(); return; }
  _srchTerm = term;

  document.getElementById('srch-results').innerHTML =
    `<div style="text-align:center;padding:50px;color:var(--mu);font-size:14px">${s('loading')}</div>`;
  document.getElementById('srch-clear-btn').style.display = '';

  let r;
  try {
    r = await api('GET', '/cards/search-flat?q=' + encodeURIComponent(term));
  } catch (e) {
    document.getElementById('srch-results').innerHTML =
      `<div style="padding:30px;color:#e05050;direction:ltr;white-space:pre-wrap;text-align:left">API ERROR:\n${(e&&e.stack)||(e&&e.message)||e}</div>`;
    return;
  }
  if (!r || !r.success) {
    document.getElementById('srch-results').innerHTML =
      `<div style="text-align:center;padding:40px;color:var(--re)">${(r&&r.message) || 'خطأ في الاستجابة'}</div>`;
    return;
  }
  try {
    _srchData = r.data || [];
    renderResults(_srchData, term);
  } catch (e) {
    document.getElementById('srch-results').innerHTML =
      `<div style="padding:30px;color:#e05050;direction:ltr;white-space:pre-wrap;text-align:left">RENDER ERROR:\n${(e&&e.stack)||(e&&e.message)||e}</div>`;
  }
}

function clearSearch() {
  document.getElementById('srch-input').value = '';
  _srchData = [];
  _srchTerm = '';
  document.getElementById('srch-clear-btn').style.display = 'none';
  document.getElementById('srch-results').innerHTML = `
    <div style="text-align:center;padding:60px 20px;color:var(--mu)">
      <div style="font-size:48px;margin-bottom:16px">🔍</div>
      <div style="font-size:16px;font-weight:700;margin-bottom:8px">${s('emptyTitle')}</div>
      <div style="font-size:13px">${s('emptySub')}</div>
    </div>`;
}

function _e(v){ return String(v==null?'':v).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;'); }
function _m(v){ v=Number(v)||0; return '$'+(Math.round(v*100)/100); }
function renderResults(data, term){
  const res = document.getElementById('srch-results');
  if(!data.length){
    res.innerHTML = `<div style="text-align:center;padding:60px;color:var(--mu)"><div style="font-size:40px;margin-bottom:12px">🔎</div><div style="font-size:15px;font-weight:700">${s('noResult')} "${_e(term)}"</div></div>`;
    return;
  }
  const isEn = (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))==='en';
  let html = `<div class="panel" style="overflow:hidden">
    <div class="panel-header"><div class="panel-title">🔍 ${data.length} ${s('results')} — "<span class="ac-num">${_e(term)}</span>"</div></div>
    <div class="table-scroll"><table class="data-table"><thead><tr>
      <th>${isEn?'Account':'رقم الحساب'}</th><th>${isEn?'Month':'الشهر'}</th><th>${isEn?'Branch':'الفرع'}</th>
      <th>${isEn?'Broker':'البروكر'}</th><th>${isEn?'Marketer':'المسوّق'}</th>
      <th>${isEn?'Opening dep.':'إيداع الفتح'}</th><th>${isEn?'Referral':'الإحالة'}</th><th>${isEn?'Status':'الحالة'}</th><th></th>
    </tr></thead><tbody>`;
  data.forEach(c=>{
    const br = isEn ? (c.branch_en||c.branch_ar) : (c.branch_ar||c.branch_en);
    const mod = c.status==='modified';
    html += `<tr${mod?' style="background:rgba(245,166,35,.06)"':''}>
      <td><span class="ac-num">${_e(c.account_number)}</span></td>
      <td style="color:var(--mu);white-space:nowrap">${_e(c.month||'—')}</td>
      <td style="white-space:nowrap">🏢 ${_e(br||'—')}</td>
      <td style="color:var(--pri2);font-weight:700">${_e(c.broker_name||'—')}<div class="mono" style="font-size:11px;color:var(--mu)">${_m(c.broker_commission)}</div></td>
      <td style="color:var(--m2);font-weight:700">${_e(c.marketer_name||'—')}<div class="mono" style="font-size:11px;color:var(--mu)">${_m(c.marketer_commission)}</div></td>
      <td class="mono c-blue" style="font-weight:700">${fmt(c.initial_deposit)}</td>
      <td>${c.referral_account?('<span class="ac-num">'+_e(c.referral_account)+'</span>'):'<span style="color:var(--mu)">—</span>'}</td>
      <td>${mod?`<span class="badge badge-orange">${s('stMod')}</span>`:`<span class="badge badge-blue">${s('stNormal')}</span>`}</td>
      <td><a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm" style="color:var(--or);border-color:rgba(245,166,35,.3)">✏️</a></td>
    </tr>`;
  });
  html += `</tbody></table></div></div>`;
  res.innerHTML = html;
}

// Init
srchApplyLang();
document.getElementById('srch-input').focus();
</script>
@endpush
