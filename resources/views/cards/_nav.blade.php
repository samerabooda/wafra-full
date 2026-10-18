{{-- Cards Internal Sidebar Nav
     @param string $active : 'index' | 'modified' | 'create' | 'edit' | 'tree' | 'search'
--}}
<div id="cnav-sidebar" style="width:220px;flex-shrink:0;background:var(--bg2);border-left:1px solid var(--brd1);display:flex;flex-direction:column;padding:10px 0;">
  <div style="padding:12px 16px 14px;border-bottom:1px solid var(--brd1);margin-bottom:8px">
    <div id="cnav-hdr" style="font-size:11px;color:var(--mu);font-weight:700;text-transform:uppercase;letter-spacing:.5px">🗂 كروت العمولة</div>
  </div>

  @foreach([
    ['index',    '🗂', route('cards.index'),       'cnav-lbl-index',    'cnav-sub-index',    'كروت العمولات',      'كل الحسابات المسجّلة'],
    ['modified', '📝', route('cards.modified'),    'cnav-lbl-modified', 'cnav-sub-modified', 'الحسابات المعدّلة',  'السجلات المُعدَّلة'],
    ['search',   '🔍', route('cards.search'),      'cnav-lbl-search',   'cnav-sub-search',   'بحث عن حساب',       'ابحث برقم الحساب'],
    ['create',   '➕', route('cards.create'),      'cnav-lbl-create',   'cnav-sub-create',   'إنشاء كرت جديد',    'تسجيل حساب عمولة'],
    ['edit',     '✏️', route('cards.edit-search'), 'cnav-lbl-edit',     'cnav-sub-edit',     'تعديل كرت عمولة',   'تعديل حساب موجود'],
    ['tree',     '🌳', route('cards.tree'),         'cnav-lbl-tree',     'cnav-sub-tree',     'شجرة الحسابات',     'توزيع العمولات'],
  ] as [$id, $ico, $href, $lblId, $subId, $lblTxt, $subTxt])
  @php $on = ($active === $id); @endphp
  <a href="{{ $href }}" style="display:flex;align-items:center;gap:10px;padding:10px 14px;margin:1px 8px;border-radius:9px;text-decoration:none;font-family:'Tajawal',sans-serif;{{ $on ? 'background:rgba(26,173,186,.15);border:1px solid rgba(26,173,186,.25);' : 'background:none;border:1px solid transparent;' }}">
    <div style="width:36px;height:36px;border-radius:9px;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:17px;{{ $on ? 'background:rgba(26,173,186,.25);border:1px solid rgba(26,173,186,.5);' : 'background:rgba(26,173,186,.1);border:1px solid rgba(26,173,186,.15);' }}">{{ $ico }}</div>
    <div style="min-width:0;flex:1">
      <div style="font-size:12px;font-weight:700;{{ $on ? 'color:var(--pri2);' : 'color:var(--tx);' }}white-space:nowrap" id="{{ $lblId }}">{{ $lblTxt }}</div>
      <div style="font-size:10px;color:var(--mu);margin-top:1px" id="{{ $subId }}">{{ $subTxt }}</div>
    </div>
  </a>
  @endforeach

  <div style="flex:1"></div>
  <div style="padding:12px 16px;border-top:1px solid var(--brd1);margin-top:8px">
    <div id="cnav-footer" style="font-size:10px;color:var(--mu);line-height:1.6"></div>
  </div>
</div>
