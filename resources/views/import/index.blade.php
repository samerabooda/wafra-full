@extends('layouts.app')
@section('title','استيراد بيانات')
@section('page-title','استيراد بيانات Excel')
@section('content')

{{-- ── خطة الاستيراد للفروع ── --}}
<div class="panel" style="max-width:900px;margin-bottom:14px">
  <div class="panel-header">
    <div class="panel-title">📋 خطة الاستيراد — كروت الفروع السابقة</div>
  </div>
  <div class="panel-body">
    <div class="alert alert-info show" style="margin-bottom:14px;font-size:13px;line-height:1.8">
      💡 <strong>للبدء السريع:</strong>
      اتبع الخطوات التالية لاستيراد كروت كل فرع من البيانات السابقة دون أي كتابة يدوية.
    </div>

    {{-- خطوات مرقمة --}}
    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:18px">
      @foreach([
        ['1','📥','حمّل النموذج','اضغط "تحميل النموذج" للحصول على ملف Excel جاهز بالأعمدة الصحيحة'],
        ['2','✏️','أدخل البيانات','افتح الملف وأدخل كروت الفرع — صف واحد لكل كرت. يمكن تكرار العملية لكل فرع في ملف منفصل'],
        ['3','🏢','اختر الفرع','قبل الرفع حدد الفرع المستهدف — جميع الكروت في الملف ستُضاف لهذا الفرع'],
        ['4','📤','ارفع الملف','اسحب الملف أو اضغط للاختيار — ستظهر معاينة قبل الحفظ النهائي'],
        ['5','✅','راجع وتأكيد','راجع الأعداد: جديد / تحديث / أخطاء — ثم اضغط "استيراد" للحفظ'],
        ['6','🔁','كرر للفروع','كرر من الخطوة 2 لكل فرع حتى تكتمل بيانات جميع الفروع'],
      ] as [$n,$ico,$ttl,$desc])
      <div style="background:var(--bg4);border-radius:10px;padding:12px 14px;border:1px solid var(--brd1);position:relative">
        <div style="position:absolute;top:-10px;right:12px;width:22px;height:22px;background:var(--pri);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white">{{$n}}</div>
        <div style="font-size:18px;margin-bottom:6px">{{$ico}}</div>
        <div style="font-size:12px;font-weight:700;color:var(--pri2);margin-bottom:4px">{{$ttl}}</div>
        <div style="font-size:11px;color:var(--mu);line-height:1.6">{{$desc}}</div>
      </div>
      @endforeach
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      {{-- أعمدة Excel --}}
      <div style="background:var(--bg4);border-radius:10px;padding:14px;border:1px solid var(--brd1)">
        <div style="font-size:12px;font-weight:800;color:var(--pri2);margin-bottom:8px">📝 أعمدة ملف Excel (بالترتيب)</div>
        <div style="display:flex;flex-direction:column;gap:3px">
          @foreach([
            ['A','AC No.','رقم الحساب — مطلوب','#e05050'],
            ['B','Broker','اسم البروكر (يجب أن يطابق اسمه في الموظفين تماماً)','#f5a623'],
            ['C','Broker Commission','عمولة البروكر لكل لوت — مثال: 4','#f5a623'],
            ['D','Marketing','اسم المسوّق الداخلي','#22c97a'],
            ['E','Marketing Commission','عمولة المسوّق — مثال: 3','#22c97a'],
            ['F','Ext Marketer 1','المسوّق الخارجي الأول (اختياري)','#7b68ee'],
            ['G','Ext Commission 1','عمولته','#7b68ee'],
            ['H','Ext Marketer 2','المسوّق الخارجي الثاني (اختياري)','#7b68ee'],
            ['I','Ext Commission 2','عمولته','#7b68ee'],
            ['J','Month','الشهر — مثال: Jan 2025','#2e86ab'],
            ['K','Initial Deposit $','إيداع فتح الحساب','#2e86ab'],
            ['L','Monthly Deposit $','الإيداع الشهري المتوقع','#2e86ab'],
            ['M','New Or Sub','NEW أو SUB','#3a9db5'],
            ['N','Type','ECN أو STP أو غيره','#3a9db5'],
          ] as [$col,$name,$note,$clr])
          <div style="display:flex;align-items:center;gap:6px;font-size:11px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.04)">
            <span style="background:{{$clr}};color:white;font-family:monospace;font-weight:700;font-size:10px;padding:1px 6px;border-radius:4px;flex-shrink:0;min-width:16px;text-align:center">{{$col}}</span>
            <span class="mono" style="color:var(--m2);font-size:10px;flex-shrink:0;min-width:120px">{{$name}}</span>
            <span style="color:var(--mu);font-size:10px">{{$note}}</span>
          </div>
          @endforeach
        </div>
      </div>

      {{-- ملاحظات مهمة --}}
      <div style="background:var(--bg4);border-radius:10px;padding:14px;border:1px solid var(--brd1)">
        <div style="font-size:12px;font-weight:800;color:var(--or);margin-bottom:8px">⚠️ ملاحظات مهمة</div>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:12px">
          <div style="padding:8px;background:rgba(224,80,80,.08);border-radius:7px;border-right:3px solid var(--re)">
            🔑 <strong>أسماء الموظفين</strong> يجب أن تطابق بالضبط الأسماء المسجّلة في قسم الموظفين — حرف بحرف
          </div>
          <div style="padding:8px;background:rgba(46,134,171,.08);border-radius:7px;border-right:3px solid var(--pri)">
            📅 <strong>تنسيق الشهر:</strong> اكتب مثل <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">Jan 2025</code> أو <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">February 2024</code>
          </div>
          <div style="padding:8px;background:rgba(34,201,122,.08);border-radius:7px;border-right:3px solid var(--gr)">
            🔄 <strong>التكرار محمي:</strong> إذا كان الحساب + الشهر موجوداً مسبقاً سيتم تحديثه فقط
          </div>
          <div style="padding:8px;background:rgba(245,166,35,.08);border-radius:7px;border-right:3px solid var(--or)">
            🏢 <strong>ملف واحد = فرع واحد</strong> — حدد الفرع المستهدف قبل كل رفع
          </div>
          <div style="padding:8px;background:rgba(123,104,238,.08);border-radius:7px;border-right:3px solid #7b68ee">
            📊 <strong>لا يوجد حد أقصى</strong> — يمكنك رفع أي عدد من السجلات في ملف واحد
          </div>
          <div style="padding:8px;background:rgba(46,134,171,.08);border-radius:7px;border-right:3px solid var(--pri)">
            💾 <strong>النتيجة:</strong> بعد الاستيراد ستظهر تفاصيل كاملة — جديد / تحديث / أخطاء مع رقم الصف
          </div>
        </div>
        <div style="margin-top:12px">
          <button class="btn btn-primary" onclick="downloadTemplate()">⬇ تحميل نموذج Excel</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── تحديد الفرع (المدير المالي فقط) ── --}}
@if(auth()->user()?->isFinanceAdmin())
<div class="panel" style="max-width:900px;margin-bottom:14px">
  <div class="panel-header"><div class="panel-title">🏢 تحديد الفرع المستهدف</div></div>
  <div class="panel-body">
    <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div style="flex:1;min-width:220px">
        <label class="form-label">الفرع — جميع كروت الملف ستُضاف لهذا الفرع *</label>
        <select id="import-branch" class="form-control">
          <option value="">— اختر الفرع —</option>
        </select>
      </div>
      <div id="import-branch-badge" style="display:none;padding:10px 16px;background:rgba(26,173,186,.12);border:1px solid rgba(26,173,186,.3);border-radius:10px;font-size:13px;font-weight:700;color:var(--pri2)"></div>
    </div>
  </div>
</div>
@endif

{{-- ── رفع الملف ── --}}
<div class="panel" style="max-width:900px">
  <div class="panel-header"><div class="panel-title">📥 رفع ملف Excel</div></div>
  <div class="panel-body">
    <div id="drop-zone"
         style="border:2px dashed var(--brd2);border-radius:14px;padding:44px 24px;text-align:center;cursor:pointer;transition:all .2s"
         onclick="document.getElementById('file-inp').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--pri)';this.style.background='rgba(46,134,171,.05)'"
         ondragleave="this.style.borderColor='';this.style.background=''"
         ondrop="onDrop(event)">
      <div style="font-size:44px;opacity:.35;margin-bottom:12px">📂</div>
      <div style="font-size:1rem;font-weight:700;margin-bottom:5px">اسحب ملف Excel هنا أو اضغط للاختيار</div>
      <div style="font-size:12px;color:var(--mu);margin-bottom:12px">يدعم: .xlsx / .xls / .csv</div>
      <button class="btn btn-primary" type="button">📂 اختر الملف</button>
    </div>
    <input type="file" id="file-inp" accept=".xlsx,.xls,.csv" style="display:none" onchange="handleFile(this)">

    {{-- معاينة + استيراد --}}
    <div id="import-result" style="margin-top:14px;display:none">
      <div class="panel">
        <div class="panel-header">
          <div class="panel-title" id="import-filename">—</div>
          <div id="import-info" style="font-size:11px;color:var(--mu)"></div>
        </div>
        <div style="overflow-x:auto;max-height:240px;overflow-y:auto">
          <table class="data-table" id="import-preview" style="min-width:900px">
            <thead id="import-thead"></thead>
            <tbody id="import-tbody"></tbody>
          </table>
        </div>
        <div id="import-progress" style="padding:12px 16px;display:none">
          <div style="background:var(--inp-bg);border-radius:6px;height:8px;overflow:hidden;margin-bottom:6px">
            <div id="import-bar" style="height:100%;background:linear-gradient(90deg,var(--pri3),var(--pri2));border-radius:6px;width:0;transition:width .3s"></div>
          </div>
          <div id="import-pct" style="font-size:11px;color:var(--mu)">جاري المعالجة...</div>
        </div>
        <div style="padding:12px 16px;border-top:1px solid var(--brd1);display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <button class="btn btn-primary" onclick="confirmImport()">✅ استيراد البيانات</button>
          <button class="btn btn-ghost" onclick="cancelImport()">إلغاء</button>
          <span id="import-status" style="font-size:11px;color:var(--mu);margin-right:auto"></span>
        </div>
      </div>
    </div>

    {{-- نتيجة الاستيراد التفصيلية --}}
    <div id="import-details" style="margin-top:14px;display:none">
      <div class="panel">
        <div class="panel-header"><div class="panel-title">📊 نتيجة الاستيراد</div></div>
        <div id="import-summary-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;border-bottom:1px solid var(--brd1)"></div>
        <div id="import-errors-wrap" style="display:none;padding:16px">
          <div style="font-size:12px;font-weight:700;color:var(--re);margin-bottom:8px">⚠️ الصفوف التي بها أخطاء:</div>
          <div style="overflow-x:auto;max-height:200px;overflow-y:auto">
            <table class="data-table" style="font-size:11px">
              <thead><tr><th>رقم الصف</th><th>رقم الحساب</th><th>سبب الخطأ</th></tr></thead>
              <tbody id="import-errors-tbody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

@endsection

@push('scripts')
<script>
let importRows = [];

// ── تحميل الفروع ───────────────────────────────────────────
async function loadImportBranches() {
  if (CURRENT_USER?.role !== 'finance_admin') return;
  const r = await api('GET', '/branches');
  if (!r.success) return;
  const sel = document.getElementById('import-branch');
  if (!sel) return;
  sel.innerHTML = '<option value="">— اختر الفرع —</option>' +
    r.data.map(b => `<option value="${b.id}">${b.name_ar}</option>`).join('');
  sel.onchange = function() {
    const badge = document.getElementById('import-branch-badge');
    if (!badge) return;
    if (this.value) {
      badge.textContent = '🏢 ' + this.options[this.selectedIndex].text;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  };
}
loadImportBranches();

// ── تحميل نموذج Excel ──────────────────────────────────────
function downloadTemplate() {
  const headers = ['AC No.','Broker','Broker Commission','Marketing','Marketing Commission',
    'Ext Marketer 1','Ext Commission 1','Ext Marketer 2','Ext Commission 2',
    'Month','Initial Deposit $','Monthly Deposit $','New Or Sub','Type'];
  const samples = [
    ['719750','Ahmed Al-Sayed','4','Mohammed Al-Rashid','3','','0','','0','Jan 2025','5000','12000','NEW','ECN'],
    ['720100','Samer Obeid','4','Fahad Al-Bloshi','3','Ali Hassan','1.5','','0','Jan 2025','3000','8000','NEW','STP'],
    ['718500','Ahmed Al-Sayed','4','','0','','0','','0','Feb 2025','10000','0','SUB','ECN'],
    ['721000','Khalid Nasser','3.5','Mariam Al-Azmi','2','','0','','0','Mar 2025','7500','15000','NEW','ECN'],
  ];
  const ws = XLSX.utils.aoa_to_sheet([headers, ...samples]);
  ws['!cols'] = headers.map(() => ({wch:22}));
  // تلوين الرأس (لن يعمل في جميع الأجهزة لكن يحسّن الشكل)
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Cards Import');
  XLSX.writeFile(wb, 'wafra_import_template.xlsx');
  toast('✅ تم تحميل النموذج','success');
}

function onDrop(e) {
  e.preventDefault();
  const dz = document.getElementById('drop-zone');
  dz.style.borderColor = ''; dz.style.background = '';
  const f = e.dataTransfer.files[0];
  if (f) readFile(f);
}
function handleFile(inp) { if (inp.files[0]) readFile(inp.files[0]); }

function readFile(file) {
  const reader = new FileReader();
  reader.onload = e => {
    const wb   = XLSX.read(e.target.result, { type: 'array' });
    const ws   = wb.Sheets[wb.SheetNames[0]];
    const rows = XLSX.utils.sheet_to_json(ws, { header: 1 });
    if (rows.length < 2) { toast('الملف فارغ أو لا يحتوي على بيانات', 'error'); return; }

    const header = rows[0];
    importRows   = rows.slice(1).filter(r => r.some(c => c));

    document.getElementById('import-filename').textContent = '📄 ' + file.name;
    document.getElementById('import-info').textContent     = importRows.length + ' سجل';
    document.getElementById('import-details').style.display = 'none';

    document.getElementById('import-thead').innerHTML =
      '<tr>' + header.map(h => `<th>${h || ''}</th>`).join('') + '</tr>';
    document.getElementById('import-tbody').innerHTML =
      importRows.slice(0, 20).map(r =>
        `<tr>${header.map((_, i) => `<td style="font-size:11px">${r[i] ?? ''}</td>`).join('')}</tr>`
      ).join('');

    document.getElementById('import-result').style.display = 'block';
    document.getElementById('drop-zone').style.display     = 'none';
    document.getElementById('import-progress').style.display = 'none';
    document.getElementById('import-bar').style.width = '0';
    document.getElementById('import-bar').style.background = 'linear-gradient(90deg,var(--pri3),var(--pri2))';

    toast('تم قراءة ' + importRows.length + ' سجل — راجع البيانات ثم اضغط استيراد', 'info');
  };
  reader.readAsArrayBuffer(file);
}

async function confirmImport() {
  if (!importRows.length) { toast('لا توجد بيانات', 'error'); return; }

  const branchId = document.getElementById('import-branch')?.value || null;
  if (!branchId && CURRENT_USER?.role === 'finance_admin') {
    toast('⚠️ يرجى اختيار الفرع المستهدف أولاً','error');
    document.getElementById('import-branch')?.focus();
    return;
  }

  document.getElementById('import-progress').style.display = 'block';
  const bar   = document.getElementById('import-bar');
  const pct   = document.getElementById('import-pct');
  let w = 0;
  const timer = setInterval(() => { w = Math.min(w + 2, 88); bar.style.width = w + '%'; }, 80);

  const rows = importRows.map(r => ({
    ac_no:                  String(r[0] || '').trim().replace(/\.0$/, ''),
    broker:                 String(r[1] || '').trim(),
    broker_commission:      parseFloat(r[2]) || 0,
    marketing:              String(r[3] || '').trim(),
    marketing_commission:   parseFloat(r[4]) || 0,
    ext_marketer1:          String(r[5] || '').trim(),
    ext_commission1:        parseFloat(r[6]) || 0,
    ext_marketer2:          String(r[7] || '').trim(),
    ext_commission2:        parseFloat(r[8]) || 0,
    month:                  String(r[9] || '').trim(),
    initial_deposit:        parseFloat(r[10]) || 0,
    monthly_deposit:        parseFloat(r[11]) || 0,
    new_or_sub:             String(r[12] || '').trim(),
    type:                   String(r[13] || 'ECN').trim(),
  })).filter(r => r.ac_no && r.month);

  const result = await api('POST', '/import', {
    rows,
    filename:  document.getElementById('import-filename').textContent.replace('📄 ', ''),
    branch_id: branchId ? parseInt(branchId) : null,
  });

  clearInterval(timer);
  bar.style.width = '100%';

  if (result.success || result.imported > 0 || result.updated > 0) {
    bar.style.background = 'var(--gr)';
    pct.textContent = `✅ تم بنجاح — جديد: ${result.imported} | تحديث: ${result.updated} | أخطاء: ${result.failed}`;
    toast(`تم الاستيراد: ${result.imported} جديد، ${result.updated} تحديث`, 'success');
  } else {
    bar.style.background = 'var(--re)';
    pct.textContent = `❌ فشل: ${result.message || 'خطأ غير معروف'}`;
    toast(`خطأ: ${result.message}`, 'error');
  }

  document.getElementById('import-status').textContent = result.batch_code
    ? `دُفعة: ${result.batch_code}`
    : '';

  // عرض ملخص التفصيلي
  showImportDetails(result);
}

function showImportDetails(result) {
  const wrap = document.getElementById('import-details');
  wrap.style.display = 'block';

  // بطاقات الإحصاء
  const grid = document.getElementById('import-summary-grid');
  grid.innerHTML = [
    { label:'إجمالي الصفوف',  val: result.total   || 0, color:'var(--pri2)' },
    { label:'حسابات جديدة',   val: result.imported || 0, color:'var(--gr)'   },
    { label:'سجلات محدّثة',   val: result.updated  || 0, color:'var(--or)'   },
    { label:'صفوف فاشلة',     val: result.failed   || 0, color:'var(--re)'   },
  ].map(k => `
    <div style="background:var(--bg4);border-radius:10px;padding:14px;text-align:center;border:1px solid var(--brd1)">
      <div style="font-size:10px;color:var(--mu);margin-bottom:6px">${k.label}</div>
      <div style="font-size:1.8rem;font-weight:900;color:${k.color}">${k.val}</div>
    </div>`).join('');

  // جدول الأخطاء
  const errWrap = document.getElementById('import-errors-wrap');
  const errBody = document.getElementById('import-errors-tbody');
  if (result.errors && result.errors.length) {
    errBody.innerHTML = result.errors.map(e =>
      `<tr>
        <td style="color:var(--or)">صف ${e.row}</td>
        <td class="mono">${e.ac_no || '—'}</td>
        <td style="color:var(--re)">${e.error}</td>
      </tr>`
    ).join('');
    errWrap.style.display = 'block';
  } else {
    errWrap.style.display = 'none';
  }

  wrap.scrollIntoView({ behavior:'smooth', block:'start' });
}

function cancelImport() {
  document.getElementById('import-result').style.display   = 'none';
  document.getElementById('import-details').style.display  = 'none';
  document.getElementById('drop-zone').style.display       = 'block';
  document.getElementById('import-progress').style.display = 'none';
  document.getElementById('import-bar').style.width = '0';
  importRows = [];
  document.getElementById('file-inp').value = '';
  const badge = document.getElementById('import-branch-badge');
  if (badge) badge.style.display = 'none';
  const sel = document.getElementById('import-branch');
  if (sel) sel.value = '';
}
</script>
@endpush
