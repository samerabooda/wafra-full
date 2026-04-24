@extends('layouts.app')
@section('title','الحسابات المعدّلة')
@section('page-title','الحسابات المعدّلة')
@section('content')
<div style="background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.25);border-radius:11px;padding:11px 15px;margin-bottom:14px;font-size:12px;color:var(--or)">
  ✏️ جميع الحسابات التي تم تعديلها — تظهر بتمييز <strong>أصفر</strong> في التقارير.
</div>
<div class="panel">
  <div class="panel-header">
    <div class="panel-title">✏️ الحسابات المعدّلة <span id="mod-count" style="font-size:11px;color:var(--mu)"></span></div>
    <div style="display:flex;gap:6px">
      <button class="btn btn-sm" style="background:rgba(34,201,122,.1);border:1px solid rgba(34,201,122,.25);color:var(--gr)" onclick="exportModExcel()">📗 Excel</button>
    </div>
  </div>
  <div class="table-scroll">
    <table class="data-table">
      <thead><tr><th>رقم الحساب</th><th>الشهر</th><th>البروكر</th><th>إيداع شهري</th><th>سبب التعديل</th><th>تاريخ التعديل</th><th>بواسطة</th></tr></thead>
      <tbody id="mod-tbody"><tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">جاري التحميل...</td></tr></tbody>
    </table>
  </div>
</div>
@endsection
@push('scripts')
<script>
async function loadModified() {
  const r = await api('GET', '/cards?status=modified&per_page=200');
  if (!r.success) return;
  const cards = r.data?.data || [];
  document.getElementById('mod-count').textContent = cards.length + ' سجل';
  document.getElementById('mod-tbody').innerHTML = cards.map(c => `
    <tr class="row-modified">
      <td><span class="ac-num">#${c.account_number}</span></td>
      <td style="color:var(--mu)">${c.month}</td>
      <td style="color:var(--pri2);font-weight:600">${c.broker?.name||'—'}</td>
      <td class="mono c-green">${fmt(c.monthly_deposit)}</td>
      <td style="color:var(--or)">${c.modifications?.[0]?.reason || '—'}</td>
      <td style="color:var(--mu)">${c.modifications?.[0]?.modified_at ? new Date(c.modifications[0].modified_at).toLocaleDateString('ar') : '—'}</td>
      <td style="color:var(--mu)">${c.modifications?.[0]?.modified_by?.name || '—'}</td>
    </tr>`).join('') || '<tr><td colspan="7" style="text-align:center;padding:40px;color:var(--mu)">لا توجد حسابات معدّلة</td></tr>';
}
loadModified();
function exportModExcel() { toast('قريباً', 'info'); }
</script>
@endpush
