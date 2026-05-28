@extends('layouts.app')
@section('title','بحث عن حساب')
@section('page-title','بحث عن رقم حساب')
@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">
@include('cards._nav', ['active' => 'search'])
<div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

{{-- Search bar --}}
<div class="panel" style="padding:20px;margin-bottom:18px">
  <div style="display:flex;gap:10px;align-items:flex-end;flex-wrap:wrap">
    <div style="flex:1;min-width:220px">
      <label class="form-label" id="srch-lbl">رقم الحساب / Account Number</label>
      <div style="position:relative">
        <span style="position:absolute;right:14px;top:50%;transform:translateY(-50%);font-size:16px;pointer-events:none">🔍</span>
        <input type="text" id="srch-input" class="form-control"
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
  }
};

function srchL() { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function s(k)    { const l = srchL(); return SRCH[l]?.[k] ?? SRCH.ar[k] ?? k; }

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

  const r = await api('GET', '/cards?search=' + encodeURIComponent(term) + '&per_page=100');
  if (!r.success) {
    document.getElementById('srch-results').innerHTML =
      `<div style="text-align:center;padding:40px;color:var(--re)">${r.message || 'خطأ'}</div>`;
    return;
  }
  _srchData = r.data?.data ?? r.data ?? [];
  renderResults(_srchData, term);
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

function renderResults(data, term) {
  const res = document.getElementById('srch-results');
  if (!data.length) {
    res.innerHTML = `
      <div style="text-align:center;padding:60px;color:var(--mu)">
        <div style="font-size:40px;margin-bottom:12px">🔎</div>
        <div style="font-size:15px;font-weight:700">${s('noResult')} "${term}"</div>
      </div>`;
    return;
  }

  // Header
  let html = `<div class="panel-header" style="margin-bottom:12px;padding:14px 16px;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:12px;">
    <div class="panel-title">🔍 ${data.length} ${s('results')} — "<span class="ac-num">${term}</span>"</div>
  </div>`;

  // Cards grid - show each card as a detailed panel
  data.forEach(c => {
    const totalComm = (parseFloat(c.broker_commission||0)+parseFloat(c.marketer_commission||0)+parseFloat(c.ext_commission1||0)+parseFloat(c.ext_commission2||0)).toFixed(1);
    const isModified = c.status === 'modified';
    html += `
    <div class="panel" style="margin-bottom:14px;${isModified?'border-color:rgba(245,166,35,.35);':''}" >
      <div class="panel-header" style="background:${isModified?'rgba(245,166,35,.06)':'var(--bg3)'}">
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
          <span class="ac-num" style="font-size:15px">#${c.account_number}</span>
          <span style="color:var(--mu);font-size:13px">📅 ${c.month}</span>
          ${c.branch ? `<span style="color:var(--mu);font-size:12px">🏢 ${c.branch.name_ar}</span>` : ''}
          ${isModified ? `<span class="badge badge-orange">${s('stMod')}</span>` : `<span class="badge badge-blue">${s('stNormal')}</span>`}
        </div>
        <a href="/cards/${c.id}/edit" class="btn btn-ghost btn-sm" style="color:var(--or);border-color:rgba(245,166,35,.3)">✏️ ${s('btnEdit')}</a>
      </div>
      <div class="panel-body">
        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:12px">
          <div style="background:var(--bg4);border-radius:10px;padding:12px 14px;border:1px solid var(--brd1)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">💰 ${s('thDep')}</div>
            <div class="mono c-blue" style="font-size:15px;font-weight:700">${fmt(c.initial_deposit)}</div>
          </div>
          <div style="background:var(--bg4);border-radius:10px;padding:12px 14px;border:1px solid var(--brd1)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">📅 ${s('thMon')}</div>
            <div class="mono c-green" style="font-size:15px;font-weight:700">${fmt(c.monthly_deposit)}</div>
          </div>
          <div style="background:rgba(46,134,171,.08);border-radius:10px;padding:12px 14px;border:1px solid rgba(46,134,171,.2)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">🧑‍💼 ${s('thBroker')}</div>
            <div style="font-size:14px;font-weight:700;color:var(--pri2)">${c.broker?.name||'—'}</div>
            <div class="mono c-blue" style="font-size:12px;margin-top:3px">$${c.broker_commission||0}/lot</div>
          </div>
          ${c.marketer && c.marketer.name !== c.broker?.name ? `
          <div style="background:rgba(34,201,122,.06);border-radius:10px;padding:12px 14px;border:1px solid rgba(34,201,122,.2)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">📢 ${s('thMktr')}</div>
            <div style="font-size:14px;font-weight:700;color:var(--gr)">${c.marketer.name}</div>
            <div class="mono c-green" style="font-size:12px;margin-top:3px">$${c.marketer_commission||0}/lot</div>
          </div>` : ''}
          ${c.ext_marketer1 ? `
          <div style="background:rgba(138,120,240,.06);border-radius:10px;padding:12px 14px;border:1px solid rgba(138,120,240,.2)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">🌐 ${s('thExt1')}</div>
            <div style="font-size:14px;font-weight:700;color:var(--pu)">${c.ext_marketer1.name}</div>
            <div class="mono" style="color:var(--pu);font-size:12px;margin-top:3px">$${c.ext_commission1||0}/lot</div>
          </div>` : ''}
          ${c.ext_marketer2 ? `
          <div style="background:rgba(138,120,240,.06);border-radius:10px;padding:12px 14px;border:1px solid rgba(138,120,240,.2)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">🌐 ${s('thExt2')}</div>
            <div style="font-size:14px;font-weight:700;color:var(--pu)">${c.ext_marketer2.name}</div>
            <div class="mono" style="color:var(--pu);font-size:12px;margin-top:3px">$${c.ext_commission2||0}/lot</div>
          </div>` : ''}
          <div style="background:rgba(245,166,35,.08);border-radius:10px;padding:12px 14px;border:1px solid rgba(245,166,35,.2)">
            <div style="font-size:10px;color:var(--mu);margin-bottom:4px;text-transform:uppercase">💎 إجمالي العمولة</div>
            <div class="badge badge-orange" style="font-size:14px;padding:6px 14px">$${totalComm}/lot</div>
          </div>
        </div>
      </div>
    </div>`;
  });

  res.innerHTML = html;
}

// Init
srchApplyLang();
document.getElementById('srch-input').focus();
</script>
@endpush
