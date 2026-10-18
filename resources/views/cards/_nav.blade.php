{{-- Cards Internal Horizontal Tabs Nav
     @param string $active : 'index' | 'review' | 'modified' | 'create' | 'edit' | 'tree' | 'search'
--}}
@php
    // Auto-upgrade $active to 'review' when the URL has ?filter=needs_review
    if (($active ?? '') === 'index' && request()->query('filter') === 'needs_review') {
        $active = 'review';
    }
@endphp
<style>
.cnav-topbar{
  display:flex;flex-wrap:wrap;gap:4px;
  background:var(--bg2);border-bottom:1px solid var(--brd1);
  padding:10px 14px;
}
.cnav-tab{
  display:flex;align-items:center;gap:8px;
  padding:8px 14px;border-radius:10px;
  text-decoration:none;font-family:'Tajawal',sans-serif;
  transition:background .18s,border .18s,color .18s;
  background:none;border:1px solid transparent;
  color:var(--tx);
}
.cnav-tab:hover{
  background:rgba(26,173,186,.08);
  border-color:rgba(26,173,186,.2);
}
.cnav-tab.on{
  background:rgba(26,173,186,.15);
  border-color:rgba(26,173,186,.3);
  color:var(--pri2);
}
.cnav-tab .cnav-ico{
  width:28px;height:28px;border-radius:8px;flex-shrink:0;
  display:flex;align-items:center;justify-content:center;font-size:14px;
  background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);
}
.cnav-tab.on .cnav-ico{
  background:rgba(26,173,186,.25);
  border-color:rgba(26,173,186,.5);
}
.cnav-tab .cnav-lbl{
  font-size:12px;font-weight:700;white-space:nowrap;
}
.cnav-tab .cnav-sub{
  font-size:10px;color:var(--mu);white-space:nowrap;
}
@media(max-width:900px){
  .cnav-tab .cnav-sub{display:none}
  .cnav-tab{padding:6px 10px}
}
@media(max-width:600px){
  .cnav-tab .cnav-lbl{font-size:11px}
}
</style>

<div class="cnav-topbar">
  @foreach([
    ['index',    '🗂', route('cards.index'),                 'كروت العمولات',      'كل الحسابات المسجّلة'],
    ['review',   '⚠', route('cards.index').'?filter=needs_review', 'تحتاج مراجعة',   'تجاوز 7$ أو مكرّر'],
    ['modified', '📝', route('cards.modified'),              'الحسابات المعدّلة',  'السجلات المُعدَّلة'],
    ['search',   '🔍', route('cards.search'),                'بحث عن حساب',       'ابحث برقم الحساب'],
    ['create',   '➕', route('cards.create'),                'إنشاء كرت جديد',    'تسجيل حساب عمولة'],
    ['edit',     '✏️', route('cards.edit-search'),           'تعديل كرت عمولة',   'تعديل حساب موجود'],
    ['tree',     '🌳', route('cards.tree'),                   'شجرة الحسابات',     'توزيع العمولات'],
  ] as [$id, $ico, $href, $lblTxt, $subTxt])
    @php $on = ($active === $id); @endphp
    <a href="{{ $href }}" class="cnav-tab {{ $on ? 'on' : '' }}">
      <div class="cnav-ico">{{ $ico }}</div>
      <div>
        <div class="cnav-lbl">{{ $lblTxt }}</div>
        <div class="cnav-sub">{{ $subTxt }}</div>
      </div>
    </a>
  @endforeach
</div>
