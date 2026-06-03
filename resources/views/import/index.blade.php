@extends('layouts.app')
@section('title','Import Data')
@section('page-title','Import Excel Data')
@section('content')

{{-- ── خطة الاستيراد للفروع ── --}}
<div class="panel" style="max-width:900px;margin-bottom:14px">
  <div class="panel-header">
    <div class="panel-title" id="imp-plan-title">📋 خطة الاستيراد — كروت الفروع السابقة</div>
  </div>
  <div class="panel-body">
    <div class="alert alert-info show" style="margin-bottom:14px;font-size:13px;line-height:1.8" id="imp-quick-start">
      💡 <strong>للبدء السريع:</strong>
      اتبع الخطوات التالية لاستيراد كروت كل فرع من البيانات السابقة دون أي كتابة يدوية.
    </div>

    {{-- خطوات مرقمة (مُولَّدة بـ JS للدعم الثنائي) --}}
    <div id="imp-steps-grid" style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:10px;margin-bottom:18px"></div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
      {{-- أعمدة Excel (JS-rendered) --}}
      <div style="background:var(--bg4);border-radius:10px;padding:14px;border:1px solid var(--brd1)">
        <div style="font-size:12px;font-weight:800;color:var(--pri2);margin-bottom:8px" id="imp-cols-title">📝 أعمدة ملف Excel (بالترتيب)</div>
        <div style="display:flex;flex-direction:column;gap:3px" id="imp-cols-list"></div>
      </div>

      {{-- ملاحظات مهمة (JS-rendered) --}}
      <div style="background:var(--bg4);border-radius:10px;padding:14px;border:1px solid var(--brd1)">
        <div style="font-size:12px;font-weight:800;color:var(--or);margin-bottom:8px" id="imp-notes-title">⚠️ ملاحظات مهمة</div>
        <div style="display:flex;flex-direction:column;gap:8px;font-size:12px" id="imp-notes-list"></div>
        <div style="margin-top:12px">
          <button class="btn btn-primary" id="imp-dl-btn" onclick="downloadTemplate()">⬇ تحميل نموذج Excel</button>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ── تحديد الفرع (المدير المالي فقط) ── --}}
@if(auth()->user()?->isFinanceAdmin())
<div class="panel" style="max-width:900px;margin-bottom:14px">
  <div class="panel-header"><div class="panel-title" id="imp-branch-title">🏢 تحديد الفرع المستهدف</div></div>
  <div class="panel-body">
    <div style="display:flex;gap:12px;align-items:flex-end;flex-wrap:wrap">
      <div style="flex:1;min-width:220px">
        <label class="form-label" id="imp-branch-lbl">الفرع — جميع كروت الملف ستُضاف لهذا الفرع *</label>
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
  <div class="panel-header"><div class="panel-title" id="imp-upload-title">📥 رفع ملف Excel</div></div>
  <div class="panel-body">
    <div id="drop-zone"
         style="border:2px dashed var(--brd2);border-radius:14px;padding:44px 24px;text-align:center;cursor:pointer;transition:all .2s"
         onclick="document.getElementById('file-inp').click()"
         ondragover="event.preventDefault();this.style.borderColor='var(--pri)';this.style.background='rgba(46,134,171,.05)'"
         ondragleave="this.style.borderColor='';this.style.background=''"
         ondrop="onDrop(event)">
      <div style="font-size:44px;opacity:.35;margin-bottom:12px">📂</div>
      <div style="font-size:1rem;font-weight:700;margin-bottom:5px" id="imp-dz-title">اسحب ملف Excel هنا أو اضغط للاختيار</div>
      <div style="font-size:12px;color:var(--mu);margin-bottom:12px" id="imp-dz-sub">يدعم: .xlsx / .xls / .csv</div>
      <button class="btn btn-primary" type="button" id="imp-dz-btn">📂 اختر الملف</button>
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
          <button class="btn btn-primary" id="imp-confirm-btn" onclick="confirmImport()">✅ استيراد البيانات</button>
          <button class="btn btn-ghost" id="imp-cancel-btn" onclick="cancelImport()">إلغاء</button>
          <span id="import-status" style="font-size:11px;color:var(--mu);margin-right:auto"></span>
        </div>
      </div>
    </div>

    {{-- نتيجة الاستيراد التفصيلية --}}
    <div id="import-details" style="margin-top:14px;display:none">
      <div class="panel">
        <div class="panel-header"><div class="panel-title" id="imp-result-title">📊 نتيجة الاستيراد</div></div>
        <div id="import-summary-grid" style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px;padding:16px;border-bottom:1px solid var(--brd1)"></div>
        <div id="import-errors-wrap" style="display:none;padding:16px">
          <div style="font-size:12px;font-weight:700;color:var(--re);margin-bottom:8px" id="imp-err-title">⚠️ الصفوف التي بها أخطاء:</div>
          <div style="overflow-x:auto;max-height:200px;overflow-y:auto">
            <table class="data-table" style="font-size:11px">
              <thead><tr><th id="imp-err-th-row">رقم الصف</th><th id="imp-err-th-ac">رقم الحساب</th><th id="imp-err-th-reason">سبب الخطأ</th></tr></thead>
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
/* ══ Import Page Bilingual Dictionary ══ */
const IMP = {
  ar: {
    tbTitle:'استيراد بيانات', planTitle:'📋 خطة الاستيراد — كروت الفروع السابقة',
    quickStart:'💡 <strong>للبدء السريع:</strong> اتبع الخطوات التالية لاستيراد كروت كل فرع من البيانات السابقة دون أي كتابة يدوية.',
    colsTitle:'📝 أعمدة ملف Excel (بالترتيب)',
    notesTitle:'⚠️ ملاحظات مهمة',
    dlBtn:'⬇ تحميل نموذج Excel',
    branchTitle:'🏢 تحديد الفرع المستهدف',
    branchLbl:'الفرع — جميع كروت الملف ستُضاف لهذا الفرع *',
    uploadTitle:'📥 رفع ملف Excel',
    dzTitle:'اسحب ملف Excel هنا أو اضغط للاختيار',
    dzSub:'يدعم: .xlsx / .xls / .csv',
    dzBtn:'📂 اختر الملف',
    confirmBtn:'✅ استيراد البيانات',
    cancelBtn:'إلغاء',
    resultTitle:'📊 نتيجة الاستيراد',
    errTitle:'⚠️ الصفوف التي بها أخطاء:',
    errThRow:'رقم الصف', errThAc:'رقم الحساب', errThReason:'سبب الخطأ',
    processing:'جاري المعالجة...',
    optBranch:'— اختر الفرع —',
    toastTemplate:'✅ تم تحميل النموذج',
    toastEmpty:'الملف فارغ أو لا يحتوي على بيانات',
    toastRead:'تم قراءة {n} سجل — راجع البيانات ثم اضغط استيراد',
    toastNoData:'لا توجد بيانات', toastNoBranch:'⚠️ يرجى اختيار الفرع المستهدف أولاً',
    toastSuccess:'تم الاستيراد: {i} جديد، {u} تحديث',
    toastFail:'خطأ: ',
    summaryTotal:'إجمالي الصفوف', summaryNew:'حسابات جديدة',
    summaryUpdated:'سجلات محدّثة', summaryFailed:'صفوف فاشلة',
    rowLabel:'صف',
    steps:[
      ['📥','حمّل النموذج','اضغط "تحميل النموذج" للحصول على ملف Excel جاهز بالأعمدة الصحيحة'],
      ['✏️','أدخل البيانات','افتح الملف وأدخل كروت الفرع — صف واحد لكل كرت. يمكن تكرار العملية لكل فرع في ملف منفصل'],
      ['🏢','اختر الفرع','قبل الرفع حدد الفرع المستهدف — جميع الكروت في الملف ستُضاف لهذا الفرع'],
      ['📤','ارفع الملف','اسحب الملف أو اضغط للاختيار — ستظهر معاينة قبل الحفظ النهائي'],
      ['✅','راجع وتأكيد','راجع الأعداد: جديد / تحديث / أخطاء — ثم اضغط "استيراد" للحفظ'],
      ['🔁','كرر للفروع','كرر من الخطوة 2 لكل فرع حتى تكتمل بيانات جميع الفروع'],
    ],
    colNotes:['رقم الحساب — مطلوب','اسم البروكر (يجب أن يطابق اسمه في الموظفين تماماً)','عمولة البروكر لكل لوت — مثال: 4','اسم المسوّق الداخلي','عمولة المسوّق — مثال: 3','المسوّق الخارجي الأول (اختياري)','عمولته','المسوّق الخارجي الثاني (اختياري)','عمولته','الشهر — مثال: Jan 2025','إيداع فتح الحساب','الإيداع الشهري المتوقع','NEW أو SUB','ECN أو STP أو غيره'],
    notes:[
      ['rgba(224,80,80,.08)','var(--re)','🔑 <strong>أسماء الموظفين</strong> يجب أن تطابق بالضبط الأسماء المسجّلة في قسم الموظفين — حرف بحرف'],
      ['rgba(46,134,171,.08)','var(--pri)','📅 <strong>تنسيق الشهر:</strong> اكتب مثل <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">Jan 2025</code> أو <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">February 2024</code>'],
      ['rgba(34,201,122,.08)','var(--gr)','🔄 <strong>التكرار محمي:</strong> إذا كان الحساب + الشهر موجوداً مسبقاً سيتم تحديثه فقط'],
      ['rgba(245,166,35,.08)','var(--or)','🏢 <strong>ملف واحد = فرع واحد</strong> — حدد الفرع المستهدف قبل كل رفع'],
      ['rgba(123,104,238,.08)','#7b68ee','📊 <strong>لا يوجد حد أقصى</strong> — يمكنك رفع أي عدد من السجلات في ملف واحد'],
      ['rgba(46,134,171,.08)','var(--pri)','💾 <strong>النتيجة:</strong> بعد الاستيراد ستظهر تفاصيل كاملة — جديد / تحديث / أخطاء مع رقم الصف'],
    ],
  },
  en: {
    tbTitle:'Import Data', planTitle:'📋 Import Plan — Previous Branch Cards',
    quickStart:'💡 <strong>Quick Start:</strong> Follow these steps to import each branch\'s cards from previous data without any manual entry.',
    colsTitle:'📝 Excel File Columns (in order)',
    notesTitle:'⚠️ Important Notes',
    dlBtn:'⬇ Download Excel Template',
    branchTitle:'🏢 Select Target Branch',
    branchLbl:'Branch — all cards in the file will be added to this branch *',
    uploadTitle:'📥 Upload Excel File',
    dzTitle:'Drag Excel file here or click to select',
    dzSub:'Supports: .xlsx / .xls / .csv',
    dzBtn:'📂 Choose File',
    confirmBtn:'✅ Import Data',
    cancelBtn:'Cancel',
    resultTitle:'📊 Import Results',
    errTitle:'⚠️ Rows with errors:',
    errThRow:'Row #', errThAc:'Account #', errThReason:'Error Reason',
    processing:'Processing...',
    optBranch:'— Select Branch —',
    toastTemplate:'✅ Template downloaded',
    toastEmpty:'File is empty or has no data',
    toastRead:'Read {n} records — review data then click Import',
    toastNoData:'No data to import', toastNoBranch:'⚠️ Please select the target branch first',
    toastSuccess:'Imported: {i} new, {u} updated',
    toastFail:'Error: ',
    summaryTotal:'Total Rows', summaryNew:'New Accounts',
    summaryUpdated:'Updated Records', summaryFailed:'Failed Rows',
    rowLabel:'Row',
    steps:[
      ['📥','Download Template','Click "Download Template" to get a ready Excel file with the correct columns'],
      ['✏️','Enter Data','Open the file and enter the branch cards — one row per card. Repeat for each branch in a separate file'],
      ['🏢','Select Branch','Before uploading select the target branch — all cards in the file will be added to this branch'],
      ['📤','Upload File','Drag the file or click to select — a preview will appear before final save'],
      ['✅','Review & Confirm','Review counts: new / updated / errors — then click "Import" to save'],
      ['🔁','Repeat per Branch','Repeat from step 2 for each branch until all branch data is complete'],
    ],
    colNotes:['Account number — required','Broker name (must exactly match name in Employees)','Broker commission per lot — e.g. 4','Internal marketer name','Marketer commission — e.g. 3','External marketer 1 (optional)','Their commission','External marketer 2 (optional)','Their commission','Month — e.g. Jan 2025','Account opening deposit','Expected monthly deposit','NEW or SUB','ECN or STP or other'],
    notes:[
      ['rgba(224,80,80,.08)','var(--re)','🔑 <strong>Employee names</strong> must exactly match names registered in the Employees section — character by character'],
      ['rgba(46,134,171,.08)','var(--pri)','📅 <strong>Month format:</strong> write like <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">Jan 2025</code> or <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">February 2024</code>'],
      ['rgba(34,201,122,.08)','var(--gr)','🔄 <strong>Duplicate protection:</strong> if account + month already exists it will only be updated'],
      ['rgba(245,166,35,.08)','var(--or)','🏢 <strong>One file = one branch</strong> — select the target branch before each upload'],
      ['rgba(123,104,238,.08)','#7b68ee','📊 <strong>No maximum limit</strong> — you can upload any number of records in one file'],
      ['rgba(46,134,171,.08)','var(--pri)','💾 <strong>Result:</strong> after import full details will appear — new / updated / errors with row numbers'],
    ],
  }
};
function impL()    { return (typeof curLang !== 'undefined' ? curLang : localStorage.getItem('wg_lang')) || 'ar'; }
function imp(key)  { const l = impL(); return IMP[l]?.[key] ?? IMP.ar[key] ?? key; }

function impApplyLang() {
  const L  = impL();
  const D  = IMP[L] || IMP.ar;
  const t  = (id, key) => { const e = document.getElementById(id); if (e) e.textContent = D[key] ?? IMP.ar[key] ?? key; };
  const h  = (id, key) => { const e = document.getElementById(id); if (e) e.innerHTML  = D[key] ?? IMP.ar[key] ?? key; };
  const el = (id) => document.getElementById(id);

  t('imp-plan-title','planTitle');
  h('imp-quick-start','quickStart');
  t('imp-cols-title','colsTitle');
  t('imp-notes-title','notesTitle');
  t('imp-dl-btn','dlBtn');
  t('imp-branch-title','branchTitle');
  t('imp-branch-lbl','branchLbl');
  t('imp-upload-title','uploadTitle');
  t('imp-dz-title','dzTitle');
  t('imp-dz-sub','dzSub');
  t('imp-dz-btn','dzBtn');
  t('imp-confirm-btn','confirmBtn');
  t('imp-cancel-btn','cancelBtn');
  t('imp-result-title','resultTitle');
  t('imp-err-title','errTitle');
  t('imp-err-th-row','errThRow');
  t('imp-err-th-ac','errThAc');
  t('imp-err-th-reason','errThReason');
  const tb = document.querySelector('.tb-title'); if (tb) tb.textContent = D.tbTitle;

  /* ── Render Steps ── */
  const stepsGrid = el('imp-steps-grid');
  if (stepsGrid && D.steps) {
    stepsGrid.innerHTML = D.steps.map(([ico,ttl,desc], i) => `
      <div style="background:var(--bg4);border-radius:10px;padding:12px 14px;border:1px solid var(--brd1);position:relative">
        <div style="position:absolute;top:-10px;right:12px;width:22px;height:22px;background:var(--pri);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white">${i+1}</div>
        <div style="font-size:18px;margin-bottom:6px">${ico}</div>
        <div style="font-size:12px;font-weight:700;color:var(--pri2);margin-bottom:4px">${ttl}</div>
        <div style="font-size:11px;color:var(--mu);line-height:1.6">${desc}</div>
      </div>`).join('');
  }

  /* ── Render Column Notes ── */
  const colsList = el('imp-cols-list');
  const colDefs = [
    ['A','AC No.','#e05050'],['B','Broker','#f5a623'],['C','Broker Commission','#f5a623'],
    ['D','Marketing','#22c97a'],['E','Marketing Commission','#22c97a'],
    ['F','Ext Marketer 1','#7b68ee'],['G','Ext Commission 1','#7b68ee'],
    ['H','Ext Marketer 2','#7b68ee'],['I','Ext Commission 2','#7b68ee'],
    ['J','Month','#2e86ab'],['K','Initial Deposit $','#2e86ab'],['L','Monthly Deposit $','#2e86ab'],
    ['M','New Or Sub','#3a9db5'],['N','Type','#3a9db5'],
  ];
  if (colsList && D.colNotes) {
    colsList.innerHTML = colDefs.map(([col,name,clr], i) => `
      <div style="display:flex;align-items:center;gap:6px;font-size:11px;padding:3px 0;border-bottom:1px solid rgba(255,255,255,.04)">
        <span style="background:${clr};color:white;font-family:monospace;font-weight:700;font-size:10px;padding:1px 6px;border-radius:4px;flex-shrink:0;min-width:16px;text-align:center">${col}</span>
        <span class="mono" style="color:var(--m2);font-size:10px;flex-shrink:0;min-width:120px">${name}</span>
        <span style="color:var(--mu);font-size:10px">${D.colNotes[i] ?? ''}</span>
      </div>`).join('');
  }

  /* ── Render Notes ── */
  const notesList = el('imp-notes-list');
  if (notesList && D.notes) {
    notesList.innerHTML = D.notes.map(([bg,brd,html]) =>
      `<div style="padding:8px;background:${bg};border-radius:7px;border-right:3px solid ${brd}">${html}</div>`
    ).join('');
  }
}
const _impOrigApplyLang = window.applyLang;
window.applyLang = function(lang) {
  if (_impOrigApplyLang) _impOrigApplyLang(lang);
  impApplyLang();
};

let importRows = [];

// ── تحميل الفروع ───────────────────────────────────────────
async function loadImportBranches() {
  if (CURRENT_USER?.role !== 'finance_admin') return;
  const r = await api('GET', '/branches');
  if (!r.success) return;
  const sel = document.getElementById('import-branch');
  if (!sel) return;
  const isEn = impL() === 'en';
  sel.innerHTML = `<option value="">${imp('optBranch')}</option>` +
    r.data.map(b => `<option value="${b.id}">${isEn?(b.name_en||b.name_ar):b.name_ar}</option>`).join('');
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
impApplyLang();
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
  toast(imp('toastTemplate'),'success');
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
    if (rows.length < 2) { toast(imp('toastEmpty'), 'error'); return; }

    const header = rows[0];
    importRows   = rows.slice(1).filter(r => r.some(c => c));

    document.getElementById('import-filename').textContent = '📄 ' + file.name;
    document.getElementById('import-info').textContent     = importRows.length + (impL()==='en'?' records':' سجل');
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

    toast(imp('toastRead').replace('{n}', importRows.length), 'info');
  };
  reader.readAsArrayBuffer(file);
}

async function confirmImport() {
  if (!importRows.length) { toast(imp('toastNoData'), 'error'); return; }

  const branchId = document.getElementById('import-branch')?.value || null;
  if (!branchId && CURRENT_USER?.role === 'finance_admin') {
    toast(imp('toastNoBranch'),'error');
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
    pct.textContent = `✅ ${impL()==='en'?'Done':'تم بنجاح'} — ${impL()==='en'?'New':'جديد'}: ${result.imported} | ${impL()==='en'?'Updated':'تحديث'}: ${result.updated} | ${impL()==='en'?'Errors':'أخطاء'}: ${result.failed}`;
    toast(imp('toastSuccess').replace('{i}',result.imported).replace('{u}',result.updated), 'success');
  } else {
    bar.style.background = 'var(--re)';
    pct.textContent = `❌ ${impL()==='en'?'Failed':'فشل'}: ${result.message || (impL()==='en'?'Unknown error':'خطأ غير معروف')}`;
    toast(imp('toastFail') + result.message, 'error');
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
    { labelKey:'summaryTotal',   val: result.total   || 0, color:'var(--pri2)' },
    { labelKey:'summaryNew',     val: result.imported || 0, color:'var(--gr)'   },
    { labelKey:'summaryUpdated', val: result.updated  || 0, color:'var(--or)'   },
    { labelKey:'summaryFailed',  val: result.failed   || 0, color:'var(--re)'   },
  ].map(k => `
    <div style="background:var(--bg4);border-radius:10px;padding:14px;text-align:center;border:1px solid var(--brd1)">
      <div style="font-size:10px;color:var(--mu);margin-bottom:6px">${imp(k.labelKey)}</div>
      <div style="font-size:1.8rem;font-weight:900;color:${k.color}">${k.val}</div>
    </div>`).join('');

  // جدول الأخطاء
  const errWrap = document.getElementById('import-errors-wrap');
  const errBody = document.getElementById('import-errors-tbody');
  if (result.errors && result.errors.length) {
    errBody.innerHTML = result.errors.map(e =>
      `<tr>
        <td style="color:var(--or)">${imp('rowLabel')} ${e.row}</td>
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
