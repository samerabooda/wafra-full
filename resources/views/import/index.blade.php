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

{{-- ── نوع الاستيراد ── --}}
@if(auth()->user()?->isFinanceAdmin())
<div class="panel" style="max-width:900px;margin-bottom:14px">
  <div class="panel-header"><div class="panel-title" id="imp-mode-title">📂 نوع الاستيراد</div></div>
  <div class="panel-body" style="display:flex;gap:10px;flex-wrap:wrap">
    <button type="button" id="impmode-normal" class="btn btn-primary" onclick="setImportMode('normal')">📋 استيراد عادي</button>
    <button type="button" id="impmode-cc"     class="btn btn-ghost"   onclick="setImportMode('cc')">📞 استيراد مركز الاتصال</button>
  </div>
</div>
@endif

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
    colNotes:['رقم الحساب — مطلوب','اسم البروكر (يجب أن يطابق اسمه في الموظفين تماماً)','عمولة البروكر لكل لوت — مثال: 4','اسم المسوّق الداخلي 1','عمولة المسوّق — مثال: 3','المسوّق الخارجي (لا يعمل بالشركة — اختياري)','عمولته','المسوّق الداخلي 2 (اختياري)','عمولته','الشهر — مثال: Jan 2025','إيداع فتح الحساب','الإيداع الشهري المتوقع','NEW أو SUB','ECN أو STP أو غيره'],
    notes:[
      ['rgba(224,80,80,.08)','var(--re)','🔑 <strong>أسماء الموظفين</strong> يجب أن تطابق بالضبط الأسماء المسجّلة في قسم الموظفين — حرف بحرف'],
      ['rgba(46,134,171,.08)','var(--pri)','📅 <strong>تنسيق الشهر:</strong> اكتب مثل <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">Jan 2025</code> أو <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">February 2024</code>'],
      ['rgba(34,201,122,.08)','var(--gr)','🔄 <strong>التكرار محمي:</strong> إذا كان الحساب + الشهر موجوداً مسبقاً سيتم تحديثه فقط'],
      ['rgba(26,173,186,.08)','var(--pri2)','🧠 <strong>قراءة ذكية:</strong> يتعرّف النظام على الأعمدة من أسمائها (عربي/إنجليزي) مهما اختلف ترتيبها، وتجاوز الأعمدة الناقصة (كالمسوّق الخارجي).'],
      ['rgba(245,168,40,.08)','var(--or)','🔗 <strong>دمج المسوّقين:</strong> إذا تكرّر رقم الحساب على سطرين (مسوّقان لنفس الحساب) يدمجهما النظام في كرت واحد بمسوّقَين.'],
      ['rgba(123,104,238,.08)','var(--pu)','💡 <strong>إحالة تلقائية:</strong> إذا وُجد <u>رقم حساب</u> بدل اسم المسوّق وبجواره عمولة، يحتسبه النظام تلقائيًا كـ<strong>إحالة (Referral)</strong> ويضع العمولة لصاحب الحساب المُحيل.'],
      ['rgba(34,201,122,.08)','var(--gr)','🧑‍💼 <strong>تعريف الموظفين تلقائيًا:</strong> أي اسم بروكر/مسوّق في الشيت غير موجود في الموظفين يُنشأ تلقائيًا بدوره الصحيح (بروكر/مسوّق/خارجي) ويُربط بالفرع — لا حاجة لتعريفهم مسبقًا.'],
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
    colNotes:['Account number — required','Broker name (must exactly match name in Employees)','Broker commission per lot — e.g. 4','Internal marketer 1 name','Marketer commission — e.g. 3','External marketer (not a company employee — optional)','Their commission','Internal marketer 2 (optional)','Their commission','Month — e.g. Jan 2025','Account opening deposit','Expected monthly deposit','NEW or SUB','ECN or STP or other'],
    notes:[
      ['rgba(224,80,80,.08)','var(--re)','🔑 <strong>Employee names</strong> must exactly match names registered in the Employees section — character by character'],
      ['rgba(46,134,171,.08)','var(--pri)','📅 <strong>Month format:</strong> write like <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">Jan 2025</code> or <code style="background:var(--inp-bg);padding:1px 5px;border-radius:4px">February 2024</code>'],
      ['rgba(34,201,122,.08)','var(--gr)','🔄 <strong>Duplicate protection:</strong> if account + month already exists it will only be updated'],
      ['rgba(26,173,186,.08)','var(--pri2)','🧠 <strong>Smart reading:</strong> columns are detected by their header names (AR/EN) in any order, and missing columns (e.g. external marketers) are skipped.'],
      ['rgba(245,168,40,.08)','var(--or)','🔗 <strong>Marketer merge:</strong> if the same account appears on two rows (two marketers), they are merged into one card with two marketers.'],
      ['rgba(123,104,238,.08)','var(--pu)','💡 <strong>Auto referral:</strong> if a marketer cell contains an <u>account number</u> (not a name) with a commission, the system treats it as a <strong>Referral</strong> and credits the commission to that referrer account.'],
      ['rgba(34,201,122,.08)','var(--gr)','🧑‍💼 <strong>Auto-define employees:</strong> any broker/marketer name not already in Employees is created automatically with the right role (broker/marketer/external) and linked to the branch — no need to pre-define them.'],
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
let importHeader = [];
let importMode = 'normal';

function setImportMode(m){
  importMode = m;
  const en = (typeof impL==='function' && impL()==='en');
  const _cls = (id,on) => { const e=document.getElementById(id); if(e) e.className = 'btn ' + (on?'btn-primary':'btn-ghost'); };
  _cls('impmode-normal', m==='normal'); _cls('impmode-cc', m==='cc');
  const title = document.getElementById('imp-branch-title');
  const lbl   = document.getElementById('imp-branch-lbl');
  if (m==='cc'){
    if(title) title.textContent = en?'📞 Call Center source branch':'📞 فرع مركز الاتصال (المصدر)';
    if(lbl)   lbl.textContent   = en?'Select the Call Center branch — the target branch is auto-detected from the duplicate account number already imported under its branch (the "Target Branch" column is optional) *'
                                    :'اختر فرع مركز الاتصال — يكتشف النظام الفرع المستهدف تلقائيًا من رقم الحساب المكرّر المستورد مسبقًا تحت فرعه (عمود «الفرع المُرسَل له» اختياري) *';
  } else {
    if(title) title.textContent = en?'🏢 Target branch':'🏢 تحديد الفرع المستهدف';
    if(lbl)   lbl.textContent   = en?'Branch — all cards in the file will be added to this branch *'
                                    :'الفرع — جميع كروت الملف ستُضاف لهذا الفرع *';
  }
}

/* ── Smart column detection: match header names (AR/EN, fuzzy) to fields ── */
// Excel stores "Dec-25" as a date — read it as a real month ("Dec 2025"),
// never as the raw serial number (43361). Handles Date objects and serials.
const _IMP_MON = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
function _fmtMonth(v){
  // Date object (only if cellDates was on) — read in UTC so the calendar month never
  // drifts by a day due to the local timezone (the cause of "Mar" being saved as "Feb").
  if (v instanceof Date && !isNaN(v))
    return _IMP_MON[v.getUTCMonth()] + ' ' + v.getUTCFullYear();
  const s = String(v==null?'':v).trim();
  if (s === '') return '';
  // bare Excel serial number → convert via the Excel epoch (UTC, timezone-safe)
  if (/^\d{4,6}(\.\d+)?$/.test(s)) {
    const n = parseFloat(s);
    if (n > 20000 && n < 80000) {
      const d = new Date(Math.round((n - 25569) * 86400 * 1000));
      if (!isNaN(d)) return _IMP_MON[d.getUTCMonth()] + ' ' + d.getUTCFullYear();
    }
  }
  // text like "Mar-26", "Mar 2026", "March-2026", "Mar/26" → "Mar 2026"
  const m = s.match(/^([A-Za-z]{3,})[\s\-\/.]+(\d{2,4})$/);
  if (m) {
    const idx = _IMP_MON.findIndex(mn => m[1].toLowerCase().startsWith(mn.toLowerCase()));
    if (idx >= 0) { let y = parseInt(m[2], 10); if (y < 100) y += 2000; return _IMP_MON[idx] + ' ' + y; }
  }
  return s;
}
function _impNorm(s){
  return String(s==null?'':s).toLowerCase().trim()
    .replace(/[ً-ْ]/g,'')          // strip Arabic diacritics
    .replace(/[أإآ]/g,'ا') // normalize alef
    .replace(/[._\-\/\\():#]/g,' ').replace(/\s+/g,' ').trim();
}
const IMP_ALIASES = {
  ac_no:['account number','account no','account','ac no.','ac no','acno','acc no','ac','a c','رقم الحساب','رقم حساب','الحساب','حساب'],
  broker:['broker','broker name','بروكر','اسم البروكر','البروكر'],
  broker_commission:['broker commission per lot','broker comm per lot','broker commission/lot','broker commission','broker comm','broker com','عمولة البروكر','عمولة بروكر','عموله البروكر'],
  marketing:['marketing','marketer','marketing name','marketer name','internal marketer','مسوق','المسوق','اسم المسوق','مسوق داخلي','المسوق الداخلي','تسويق'],
  marketing_commission:['marketing commission per lot','marketing comm per lot','marketing commission/lot','marketing commission','marketer commission','marketing comm','عمولة المسوق','عمولة التسويق','عمولة مسوق','عموله المسوق'],
  ext_marketer1:['external marketer 1','external marketer','ext marketer 1','external 1','ext marketer1','مسوق خارجي 1','مسوق خارجي','المسوق الخارجي','مسوق خارجي اول','خارجي 1','خارجي'],
  ext_commission1:['external commission 1','external commission','ext commission 1','external comm','عمولة خارجي 1','عمولة المسوق الخارجي','عمولة خارجي','عموله خارجي'],
  ext_marketer2:['internal marketer 2','marketer 2','marketing 2','marketer2','مسوق داخلي 2','مسوق 2','المسوق الثاني','مسوق ثاني','مسوق2','external marketer 2','مسوق خارجي 2'],
  ext_commission2:['internal marketer 2 commission','marketer 2 commission','marketing 2 commission','عمولة مسوق 2','عمولة المسوق الثاني','عمولة مسوق ثاني','external commission 2','عمولة خارجي 2'],
  month:['opening month','open month','account opening month','month','شهر فتح الحساب','الشهر','شهر'],
  initial_deposit:['initial deposit','opening deposit','first deposit','deposit','الايداع الاولي','ايداع فتح الحساب','اول ايداع','الايداع','ايداع'],
  monthly_deposit:['monthly deposit','expected monthly deposit','الايداع الشهري','ايداع شهري','الايداع الشهري المتوقع'],
  new_or_sub:['new or sub','new/sub','new sub','نوع الحساب','جديد ام فرعي','جديد او فرعي'],
  // "Type" / "ECN" / platform column → mapped here and IGNORED (never read as month or new/sub)
  trading:['trading type','account type','platform','trading','type','ecn','stp','نوع التداول','نوع العقد','نوع'],
  // ── Referral (a referrer account number that earns the marketing commission) ──
  referral_account:['referral','referral account','referrer account','referral acc','referrer','رقم المحيل','حساب المحيل','احاله','إحالة','المحيل','ريفيرال'],
  referral_commission:['referral commission','referral comm','referrer commission','عمولة الاحالة','عمولة الإحالة','عمولة المحيل','عموله الاحاله'],
  // ── Call Center columns ──
  cc_agent:['cc agent','call center agent','موظف الكول سنتر','موظف مركز الاتصال','موظف الكول','الموظف'],
  cc_agent_commission:['cc agent commission','call center agent commission','agent commission','عمولة موظف الكول سنتر','عمولة موظف مركز الاتصال','عمولة الموظف'],
  cc_manager_commission:['cc manager commission','call center manager commission','manager commission','عمولة مديرة مركز الاتصال','عمولة مدير مركز الاتصال','عمولة المديرة'],
  target_branch:['target branch','branch sent to','الفرع المرسل له','الفرع المستهدف','الفرع المرسل','فرع الوجهة'],
};
function buildColMap(header){
  const map={ac_no:-1,broker:-1,broker_commission:-1,marketing:-1,marketing_commission:-1,
    ext_marketer1:-1,ext_commission1:-1,ext_marketer2:-1,ext_commission2:-1,
    month:-1,initial_deposit:-1,monthly_deposit:-1,new_or_sub:-1,trading:-1,
    referral_account:-1,referral_commission:-1,
    cc_agent:-1,cc_agent_commission:-1,cc_manager_commission:-1,target_branch:-1};
  (header||[]).forEach((cell,i)=>{
    const h=_impNorm(cell); if(!h) return;
    let best=null,bestLen=0;
    for(const field in IMP_ALIASES){
      for(const a of IMP_ALIASES[field]){
        // short aliases (<=3 chars, e.g. "ac") must match EXACTLY so they don't
        // accidentally hit longer headers that merely contain those letters.
        const hit = a.length <= 3 ? (h === a) : (h === a || h.indexOf(a) >= 0);
        if(hit){ if(a.length>bestLen){ bestLen=a.length; best=field; } }
      }
    }
    if(best && map[best]===-1) map[best]=i;
  });
  return map;
}

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
  let headers, samples, fname = 'wafra_import_template.xlsx';
  if (importMode === 'cc') {
    headers = ['AC No.','Month','CC Agent','CC Agent Commission','CC Manager Commission','Target Branch',
      'Broker','Broker Commission','Marketing','Marketing Commission','External Marketer','External Commission',
      'Internal Marketer 2','Internal Marketer 2 Commission','Initial Deposit $','New Or Sub','Type'];
    samples = [
      ['730100','Jan 2025','Sara Ali','1','2','الجهراء','Ahmed Al-Sayed','4','Mohammed Al-Rashid','3','','0','','0','5000','NEW','ECN'],
      ['730105','Jan 2025','Sara Ali','1','2','','','0','','0','','0','','0','2000','NEW','ECN'],
    ];
    fname = 'wafra_callcenter_import_template.xlsx';
  } else {
    headers = ['AC No.','Broker','Broker Commission','Marketing','Marketing Commission',
      'External Marketer','External Commission','Internal Marketer 2','Internal Marketer 2 Commission',
      'Month','Initial Deposit $','Monthly Deposit $','New Or Sub','Type'];
    samples = [
      ['719750','Ahmed Al-Sayed','4','Mohammed Al-Rashid','3','','0','','0','Jan 2025','5000','12000','NEW','ECN'],
      ['720100','Samer Obeid','4','Fahad Al-Bloshi','3','Ali Hassan','1.5','','0','Jan 2025','3000','8000','NEW','STP'],
      ['718500','Ahmed Al-Sayed','4','','0','','0','','0','Feb 2025','10000','0','SUB','ECN'],
      ['721000','Khalid Nasser','3.5','Mariam Al-Azmi','2','','0','','0','Mar 2025','7500','15000','NEW','ECN'],
    ];
  }
  const ws = XLSX.utils.aoa_to_sheet([headers, ...samples]);
  ws['!cols'] = headers.map(() => ({wch:22}));
  const wb = XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb, ws, 'Cards Import');
  XLSX.writeFile(wb, fname);
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
    // NOTE: cellDates intentionally OFF — date cells come through as Excel serial numbers,
    // which _fmtMonth converts via the UTC epoch (timezone-safe). cellDates produced Date
    // objects that drifted by a day in some timezones, saving "Mar" as "Feb".
    const wb   = XLSX.read(e.target.result, { type: 'array' });
    const ws   = wb.Sheets[wb.SheetNames[0]];
    const rows = XLSX.utils.sheet_to_json(ws, { header: 1 });
    if (rows.length < 2) { toast(imp('toastEmpty'), 'error'); return; }

    // SMART header detection: the first row may be a title ("Employees Commission for...").
    // Scan the first rows and pick the one that actually holds recognised column names
    // (must include the account column); data starts on the row after it.
    let headerIdx = 0, bestScore = -1;
    const scan = Math.min(rows.length, 20);
    for (let i = 0; i < scan; i++) {
      const mm = buildColMap(rows[i] || []);
      if (mm.ac_no === -1) continue;                 // not a header without an account column
      let score = 0; for (const k in mm) if (mm[k] !== -1) score++;
      if (score > bestScore) { bestScore = score; headerIdx = i; }
    }
    const header = rows[headerIdx];
    importHeader = header;
    // Data rows after the header. Keep any row that has an ACCOUNT NUMBER (even if every
    // other cell is empty) so account-only rows still import; otherwise require ≥2 cells to
    // drop blank rows / stray single-cell section labels. (Backend still skips non-data rows.)
    const _hm = buildColMap(header);
    const _acIdx = _hm.ac_no >= 0 ? _hm.ac_no : 0;
    importRows = rows.slice(headerIdx + 1).filter(r => {
      if (String(r[_acIdx] ?? '').trim() !== '') return true;
      return r.filter(c => c !== '' && c != null).length >= 2;
    });

    document.getElementById('import-filename').textContent = '📄 ' + file.name;
    document.getElementById('import-info').textContent     = importRows.length + (impL()==='en'?' records':' سجل');
    document.getElementById('import-details').style.display = 'none';

    // format the month column (now a serial) for the preview
    const _mIdxPrev = (_hm.ac_no === -1) ? 9 : (_hm.month >= 0 ? _hm.month : -1);
    document.getElementById('import-thead').innerHTML =
      '<tr>' + header.map(h => `<th>${h || ''}</th>`).join('') + '</tr>';
    document.getElementById('import-tbody').innerHTML =
      importRows.slice(0, 20).map(r =>
        `<tr>${header.map((_, i) => `<td style="font-size:11px">${ (i === _mIdxPrev || r[i] instanceof Date) ? _fmtMonth(r[i]) : (r[i] ?? '') }</td>`).join('')}</tr>`
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

  // ── Smart column mapping (by header name); fall back to fixed template order ──
  const m = buildColMap(importHeader);
  const FIXED = {ac_no:0,broker:1,broker_commission:2,marketing:3,marketing_commission:4,
    ext_marketer1:5,ext_commission1:6,ext_marketer2:7,ext_commission2:8,
    month:9,initial_deposit:10,monthly_deposit:11,new_or_sub:12,trading:13};
  const useFixed = (m.ac_no === -1);           // header not recognized → legacy order
  const ix = f => useFixed ? FIXED[f] : (m[f] >= 0 ? m[f] : -1);
  const S  = (r,f) => { const i=ix(f); return i>=0 ? String(r[i] ?? '').trim() : ''; };
  const N  = (r,f) => { const i=ix(f); return i>=0 ? (parseFloat(r[i]) || 0) : 0; };

  // a marketer cell that is a pure number = an account number → it's a REFERRAL, not an employee
  const isAccNo = v => v !== '' && /^\d{2,}(\.\d+)?$/.test(String(v).replace(/[,\s]/g,''));
  // 0 / - / – / $ in a broker/marketer cell = "no employee" → ignore it
  const empName = v => { const t=String(v==null?'':v).trim();
    if(t===''||t==='-'||t==='—'||t==='–') return '';
    if(/^(self|direct|ذاتي)$/i.test(t)) return '';   // "self" in broker/marketer = NO employee (direct)
    const c=t.replace(/[,\s$]/g,''); if(c===''||/^0+(\.0+)?$/.test(c)) return ''; return t; };

  const _mi = ix('month');
  let _pAcc='', _curMon='';   // forward-fill ONLY for duplicate-account continuation rows
  const raw = importRows.map(r => {
    let acNo  = String(S(r,'ac_no')).replace(/\.0$/, '').trim();
    let month = _fmtMonth(_mi >= 0 ? r[_mi] : '');
    const isCont = (acNo === '');   // blank account number = a continuation of the account above
    if (isCont) {
      // continuation row → belongs to the account above; inherit ITS account & ITS month only
      if (_pAcc) acNo = _pAcc;
      if (!month) month = _curMon;
      if (month) _curMon = month;
    } else {
      // a NEW account starts here — its own month (if the sheet left it blank, it STAYS blank;
      // never borrow the previous account's month)
      _pAcc = acNo;
      _curMon = month;
    }

    // marketer slots (name + commission) — 0/-/$ ignored
    const slots = [
      { name: empName(S(r,'marketing')),     comm: N(r,'marketing_commission') },
      { name: empName(S(r,'ext_marketer1')), comm: N(r,'ext_commission1') },
      { name: empName(S(r,'ext_marketer2')), comm: N(r,'ext_commission2') },
    ];
    // broker — but a numeric account number in the BROKER column = a REFERRAL too
    let brokerName = empName(S(r,'broker'));
    let brokerComm = N(r,'broker_commission');
    // dedicated referral column (if present)
    let refAcc  = S(r,'referral_account');
    let refComm = N(r,'referral_commission');
    // SMART: an account number in the BROKER column → referral (its commission = referral commission)
    if (!refAcc && brokerName && isAccNo(brokerName)) {
      refAcc = String(brokerName).replace(/[,\s]/g,'').replace(/\.0$/, '');
      refComm = brokerComm;
      brokerName = ''; brokerComm = 0;
    }
    // SMART: any numeric name in a marketer column → referral account (take first)
    for (const s of slots) {
      if (!refAcc && s.name && isAccNo(s.name)) {
        refAcc = String(s.name).replace(/[,\s]/g,'').replace(/\.0$/, '');
        refComm = s.comm;
        s.name = ''; s.comm = 0;        // remove from marketer slot so it isn't matched as employee
      }
    }
    return {
      ac_no:                acNo,
      broker:               brokerName,
      broker_commission:    brokerComm,
      marketing:            slots[0].name, marketing_commission: slots[0].comm,
      ext_marketer1:        slots[1].name, ext_commission1:      slots[1].comm,
      ext_marketer2:        slots[2].name, ext_commission2:      slots[2].comm,
      referral_account:     refAcc,        referral_commission:  refComm,
      month:                month,
      initial_deposit:      N(r,'initial_deposit'),
      monthly_deposit:      N(r,'monthly_deposit'),
      new_or_sub:           S(r,'new_or_sub'),
      type:                 S(r,'trading') || 'ECN',
      cc_agent:             S(r,'cc_agent'),
      cc_agent_commission:  N(r,'cc_agent_commission'),
      cc_manager_commission:N(r,'cc_manager_commission'),
      target_branch:        S(r,'target_branch'),
    };
  }).filter(r => r.ac_no);   // require only an account number; month may be blank (still imported)

  // ── Merge duplicate account+month rows ──
  // INTERNAL marketers (work at the company) → marketer 1 + marketer 2 slots.
  // EXTERNAL marketer (does NOT work at the company, only brings accounts) → external slot.
  const bag = {}, order = [];
  const pushTo = (slot, name, comm) => { if (name && !slot.some(x => x.name === name)) slot.push({name, comm}); };
  const collect = (b, r) => {
    // internal: the "Marketing" column + the "internal marketer 2" column
    pushTo(b._int, r.marketing,     r.marketing_commission);
    pushTo(b._int, r.ext_marketer2, r.ext_commission2);
    // external: the "External Marketer" column
    pushTo(b._ext, r.ext_marketer1, r.ext_commission1);
  };
  raw.forEach(r => {
    // Merge by ACCOUNT NUMBER only — an account is opened once (one opening month),
    // so the same account on multiple rows = ONE card (marketers combined).
    const key = String(r.ac_no);
    if (!bag[key]) { bag[key] = Object.assign({}, r, {_int:[], _ext:[]}); order.push(key); collect(bag[key], r); }
    else {
      const b = bag[key];
      if (!b.broker && r.broker) { b.broker = r.broker; b.broker_commission = r.broker_commission; }
      if (!b.broker_commission && r.broker_commission) b.broker_commission = r.broker_commission;
      if (!b.initial_deposit && r.initial_deposit) b.initial_deposit = r.initial_deposit;
      if (!b.monthly_deposit && r.monthly_deposit) b.monthly_deposit = r.monthly_deposit;
      if (!b.new_or_sub && r.new_or_sub) b.new_or_sub = r.new_or_sub;
      if (!b.referral_account && r.referral_account) { b.referral_account = r.referral_account; b.referral_commission = r.referral_commission; }
      collect(b, r);
    }
  });
  const mergedCount = raw.length - order.length;
  const rows = order.map(key => {
    const b = bag[key], int = b._int, ext = b._ext;
    return {
      ac_no: b.ac_no, month: b.month,
      broker: b.broker, broker_commission: b.broker_commission,
      marketing:     int[0] ? int[0].name : '', marketing_commission: int[0] ? int[0].comm : 0,  // مسوّق داخلي 1
      ext_marketer2: int[1] ? int[1].name : '', ext_commission2:      int[1] ? int[1].comm : 0,  // مسوّق داخلي 2
      ext_marketer1: ext[0] ? ext[0].name : '', ext_commission1:      ext[0] ? ext[0].comm : 0,  // مسوّق خارجي
      referral_account: b.referral_account || '', referral_commission: b.referral_commission || 0,
      initial_deposit: b.initial_deposit, monthly_deposit: b.monthly_deposit,
      new_or_sub: b.new_or_sub, type: b.type,
      cc_agent: b.cc_agent, cc_agent_commission: b.cc_agent_commission,
      cc_manager_commission: b.cc_manager_commission, target_branch: b.target_branch,
    };
  });
  if (mergedCount > 0) toast((impL()==='en'
        ? '🔗 Merged '+mergedCount+' duplicate account row(s)'
        : '🔗 تم دمج '+mergedCount+' سطر حساب مكرّر'), 'info');

  const result = await api('POST', '/import', {
    rows,
    filename:  document.getElementById('import-filename').textContent.replace('📄 ', ''),
    mode:      importMode,
    branch_id:    importMode==='cc' ? null : (branchId ? parseInt(branchId) : null),
    cc_branch_id: importMode==='cc' ? (branchId ? parseInt(branchId) : null) : null,
  });

  clearInterval(timer);
  bar.style.width = '100%';

  if (result.success || result.imported > 0 || result.updated > 0 || result.linked > 0) {
    bar.style.background = 'var(--gr)';
    const _lk = (result.linked > 0) ? ` | ${impL()==='en'?'Linked to branch':'مرتبط بفرع'}: ${result.linked}` : '';
    const _ce = (result.created_employees > 0) ? ` | ${impL()==='en'?'New employees':'موظفون جدد'}: ${result.created_employees}` : '';
    pct.textContent = `✅ ${impL()==='en'?'Done':'تم بنجاح'} — ${impL()==='en'?'New':'جديد'}: ${result.imported} | ${impL()==='en'?'Updated':'تحديث'}: ${result.updated}${_lk}${_ce} | ${impL()==='en'?'Errors':'أخطاء'}: ${result.failed}`;
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
  const _cards = [
    { labelKey:'summaryTotal',   val: result.total   || 0, color:'var(--pri2)' },
    { labelKey:'summaryNew',     val: result.imported || 0, color:'var(--gr)'   },
    { labelKey:'summaryUpdated', val: result.updated  || 0, color:'var(--or)'   },
  ];
  if (result.linked > 0) _cards.push({ label:(impL()==='en'?'Linked to branch':'مرتبط بفرع'), val: result.linked, color:'#7b68ee' });
  if (result.created_employees > 0) _cards.push({ label:(impL()==='en'?'New employees':'موظفون جدد'), val: result.created_employees, color:'#f5a623' });
  _cards.push({ labelKey:'summaryFailed',  val: result.failed   || 0, color:'var(--re)'   });
  if (_cards.length > 4) grid.style.gridTemplateColumns = 'repeat(' + _cards.length + ',1fr)';
  grid.innerHTML = _cards.map(k => `
    <div style="background:var(--bg4);border-radius:10px;padding:14px;text-align:center;border:1px solid var(--brd1)">
      <div style="font-size:10px;color:var(--mu);margin-bottom:6px">${k.label || imp(k.labelKey)}</div>
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
