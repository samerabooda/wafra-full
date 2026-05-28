@extends('layouts.app')
@section('title','تقرير شهري للفروع')
@section('page-title','تقرير شهري للفروع')

@section('content')
<div style="display:flex;gap:0;min-height:calc(100vh - 120px);background:var(--card-bg);border:1px solid var(--card-brd);border-radius:16px;overflow:hidden;">

  {{-- Reports Sidebar Nav --}}
  <div id="rnav-sidebar" style="width:220px;flex-shrink:0;background:var(--bg2);border-left:1px solid var(--brd1);display:flex;flex-direction:column;padding:10px 0;">
    <div style="padding:12px 16px 14px;border-bottom:1px solid var(--brd1);margin-bottom:8px">
      <div style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px">📈 التقارير</div>
    </div>
    <a href="{{ route('reports.index') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);">📊</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap">التقارير الشهرية</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px">تحليل شامل بالرسوم البيانية</div>
      </div>
    </a>
    <a href="{{ route('reports.dynamic') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:none;border:1px solid transparent;">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);">🔧</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--tx);white-space:nowrap">تقرير ديناميكي</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px">بناء تقرير مخصص</div>
      </div>
    </a>
    <a href="{{ route('reports.branch-monthly') }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;background:rgba(245,166,35,.12);border:1px solid rgba(245,166,35,.3);">
      <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;background:rgba(245,166,35,.25);border:1px solid rgba(245,166,35,.5);">🏢</div>
      <div style="min-width:0;flex:1">
        <div style="font-size:12px;font-weight:700;color:var(--or);white-space:nowrap">تقرير شهري للفروع</div>
        <div style="font-size:10px;color:var(--mu);margin-top:1px">فرع + شهر محدد</div>
      </div>
    </a>
    <div style="flex:1"></div>
    <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
      <div style="font-size:10px;color:var(--mu);line-height:1.6">منصة وفرة الخليجية</div>
    </div>
  </div>

  {{-- Content Panel --}}
  <div style="flex:1;overflow-y:auto;padding:24px;min-width:0">

    {{-- Filter bar --}}
    <div class="panel" style="padding:20px;margin-bottom:16px">
      <div class="panel-header" style="padding:0 0 14px;border-bottom:1px solid var(--brd1);margin-bottom:16px">
        <div class="panel-title">🏢 تقرير شهري للفروع</div>
      </div>
      <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
        @if(auth()->user()?->isFinanceAdmin())
        <div style="min-width:200px;flex:1">
          <label class="form-label">🏢 الفرع</label>
          <select id="bm-branch" class="form-control">
            <option value="">— كل الفروع —</option>
          </select>
        </div>
        @endif
        <div style="min-width:160px;flex:1">
          <label class="form-label">📅 الشهر (مثال: Jan 2025)</label>
          <input type="text" id="bm-month" class="form-control" placeholder="Jan 2025" dir="ltr"
                 list="bm-month-list" autocomplete="off">
          <datalist id="bm-month-list"></datalist>
        </div>
        <button class="btn btn-primary" onclick="bmGenerate()" id="bm-gen-btn">⚡ توليد التقرير</button>
        <button class="btn btn-ghost" onclick="bmExport()" id="bm-export-btn" style="display:none">📥 تصدير Excel</button>
      </div>
    </div>

    {{-- KPI summary --}}
    <div id="bm-kpis" style="display:none;margin-bottom:16px">
      <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:10px">
        <div class="kpi-card kpi-teal">
          <div class="kpi-label">إجمالي الحسابات</div>
          <div class="kpi-value" id="bm-k-total">—</div>
        </div>
        <div class="kpi-card kpi-green">
          <div class="kpi-label">إيداع أولي</div>
          <div class="kpi-value" id="bm-k-dep">—</div>
          <div class="kpi-sub">USD</div>
        </div>
        <div class="kpi-card kpi-blue">
          <div class="kpi-label">إيداع شهري</div>
          <div class="kpi-value" id="bm-k-mon">—</div>
          <div class="kpi-sub">USD</div>
        </div>
        <div class="kpi-card kpi-orange">
          <div class="kpi-label">إجمالي العمولة</div>
          <div class="kpi-value" id="bm-k-comm">—</div>
          <div class="kpi-sub">$/lot</div>
        </div>
        <div class="kpi-card kpi-purple">
          <div class="kpi-label">حسابات معدّلة</div>
          <div class="kpi-value" id="bm-k-mod">—</div>
        </div>
      </div>
    </div>

    {{-- Results table --}}
    <div id="bm-results">
      <div style="text-align:center;padding:60px 20px;color:var(--mu)">
        <div style="font-size:48px;margin-bottom:12px">🏢</div>
        <div style="font-size:15px;font-weight:700;margin-bottom:6px">اختر الفرع والشهر</div>
        <div style="font-size:12px">اضغط ⚡ توليد التقرير لعرض البيانات</div>
      </div>
    </div>

  </div>{{-- content panel --}}
</div>{{-- shell --}}
@endsection

@push('scripts')
<script>
let _bmData = [];
let _bmBranchName = '';
let _bmMonthVal = '';

// Load branches
async function bmLoadBranches() {
  const sel = document.getElementById('bm-branch'); if (!sel) return;
  const r = await api('GET', '/branches');
  if (!r.success) return;
  sel.innerHTML = '<option value="">— كل الفروع —</option>' +
    r.data.map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
  // Also build month datalist suggestions
  const months = [];
  const now = new Date();
  for (let i = 0; i < 36; i++) {
    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
    months.push(d.toLocaleDateString('en-US',{month:'short',year:'numeric'}));
  }
  const dl = document.getElementById('bm-month-list');
  if (dl) dl.innerHTML = months.map(m => `<option value="${m}">`).join('');
}

async function bmGenerate() {
  const branchId = document.getElementById('bm-branch')?.value || '';
  const month    = document.getElementById('bm-month')?.value.trim() || '';
  const btn      = document.getElementById('bm-gen-btn');

  if (!month) { toast('أدخل الشهر أولاً — مثال: Jan 2025','error'); return; }

  btn.disabled = true;
  btn.textContent = '⏳ جاري التحميل...';
  document.getElementById('bm-results').innerHTML =
    `<div style="text-align:center;padding:50px;color:var(--mu)">⏳ جاري تحميل البيانات...</div>`;
  document.getElementById('bm-kpis').style.display = 'none';

  let url = `/cards?month=${encodeURIComponent(month)}&per_page=200`;
  if (branchId) url += `&branch_id=${branchId}`;

  const r = await api('GET', url);
  btn.disabled = false;
  btn.textContent = '⚡ توليد التقرير';

  if (!r.success) {
    document.getElementById('bm-results').innerHTML =
      `<div style="text-align:center;padding:40px;color:var(--re)">${r.message||'خطأ في التحميل'}</div>`;
    return;
  }

  _bmData = r.data?.data ?? r.data ?? [];
  _bmMonthVal = month;

  // Branch name
  const brSel = document.getElementById('bm-branch');
  _bmBranchName = branchId && brSel
    ? (brSel.options[brSel.selectedIndex]?.text || '')
    : 'كل الفروع';

  bmRenderKpis(_bmData);
  bmRenderTable(_bmData);
  document.getElementById('bm-export-btn').style.display = '';
}

function bmRenderKpis(data) {
  const kpis = document.getElementById('bm-kpis');
  if (!kpis) return;
  kpis.style.display = '';
  const total = data.length;
  const dep   = data.reduce((s,c) => s + parseFloat(c.initial_deposit||0), 0);
  const mon   = data.reduce((s,c) => s + parseFloat(c.monthly_deposit||0), 0);
  const comm  = data.reduce((s,c) => s +
    parseFloat(c.broker_commission||0) + parseFloat(c.marketer_commission||0) +
    parseFloat(c.ext_commission1||0)   + parseFloat(c.ext_commission2||0), 0);
  const mod   = data.filter(c => c.status==='modified').length;
  const fmt   = n => n >= 1000 ? (n/1000).toFixed(1)+'k' : n.toFixed(0);
  document.getElementById('bm-k-total').textContent = total;
  document.getElementById('bm-k-dep').textContent   = '$'+fmt(dep);
  document.getElementById('bm-k-mon').textContent   = '$'+fmt(mon);
  document.getElementById('bm-k-comm').textContent  = '$'+comm.toFixed(1);
  document.getElementById('bm-k-mod').textContent   = mod;
}

function bmRenderTable(data) {
  const res = document.getElementById('bm-results');
  if (!data.length) {
    res.innerHTML = `<div style="text-align:center;padding:60px;color:var(--mu)">
      <div style="font-size:40px;margin-bottom:12px">🔎</div>
      <div style="font-size:15px;font-weight:700">لا توجد بيانات لـ "${_bmMonthVal}" في ${_bmBranchName}</div>
    </div>`;
    return;
  }

  let html = `
  <div class="panel">
    <div class="panel-header">
      <div class="panel-title">📋 ${data.length} حساب — ${_bmMonthVal} — ${_bmBranchName}</div>
    </div>
    <div class="table-scroll">
      <table class="data-table">
        <thead><tr>
          <th>رقم الحساب</th>
          <th>الشهر</th>
          <th>البروكر</th>
          <th>ع.بروكر</th>
          <th>المسوّق</th>
          <th>ع.مسوّق</th>
          <th>إيداع أولي</th>
          <th>إيداع شهري</th>
          <th>الحالة</th>
          <th>الفرع</th>
        </tr></thead>
        <tbody>`;

  data.forEach(c => {
    const isM = c.status === 'modified';
    html += `<tr${isM?' class="row-modified"':''}>
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="color:var(--mu)">${c.month}</td>
      <td style="font-weight:600">${c.broker?.name||'—'}</td>
      <td class="mono c-blue">$${c.broker_commission||0}</td>
      <td style="color:var(--mu)">${c.marketer?.name||'—'}</td>
      <td class="mono c-green">$${c.marketer_commission||0}</td>
      <td class="mono" style="color:var(--gr)">$${fmt(parseFloat(c.initial_deposit||0))}</td>
      <td class="mono" style="color:var(--pri2)">$${fmt(parseFloat(c.monthly_deposit||0))}</td>
      <td>${isM?'<span class="badge badge-orange">معدّل</span>':'<span class="badge badge-blue">عادي</span>'}</td>
      <td style="color:var(--mu);font-size:12px">${c.branch?.name_ar||'—'}</td>
    </tr>`;
  });

  html += `</tbody></table></div></div>`;
  res.innerHTML = html;
}

function fmt(n) { return n >= 1000 ? (n/1000).toFixed(1)+'k' : n.toFixed(0); }

function bmExport() {
  if (!_bmData.length) { toast('لا توجد بيانات','error'); return; }
  const headers = ['رقم الحساب','الشهر','البروكر','ع.بروكر','المسوّق','ع.مسوّق',
    'إيداع أولي','إيداع شهري','الحالة','الفرع'];
  const rows = _bmData.map(c => [
    c.account_number, c.month,
    c.broker?.name||'', c.broker_commission||0,
    c.marketer?.name||'', c.marketer_commission||0,
    parseFloat(c.initial_deposit||0), parseFloat(c.monthly_deposit||0),
    c.status==='modified'?'معدّل':'عادي',
    c.branch?.name_ar||'',
  ]);
  const ws = XLSX.utils.aoa_to_sheet([headers, ...rows]);
  ws['!cols'] = headers.map(()=>({wch:18}));
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Report');
  XLSX.writeFile(wb, `branch_monthly_${_bmMonthVal}_${_bmBranchName}.xlsx`.replace(/\s+/g,'_'));
  toast('تم تصدير التقرير','success');
}

// Init
bmLoadBranches();
</script>
@endpush
