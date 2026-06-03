{{-- Cards Internal Nav — horizontal top bar
     @param string $active : 'index' | 'modified' | 'create' | 'edit' | 'tree' | 'search'
--}}
<div id="cnav-bar" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;padding:10px 14px;background:var(--bg2);border-bottom:1px solid var(--brd1);overflow-x:auto">
  @foreach([
    ['index',    '🗂', route('cards.index'),       'cnav-lbl-index',    'كروت العمولات'],
    ['modified', '📝', route('cards.modified'),    'cnav-lbl-modified', 'الحسابات المعدّلة'],
    ['search',   '🔍', route('cards.search'),      'cnav-lbl-search',   'بحث عن حساب'],
    ['create',   '➕', route('cards.create'),      'cnav-lbl-create',   'إنشاء كرت جديد'],
    ['edit',     '✏️', route('cards.edit-search'), 'cnav-lbl-edit',     'تعديل كرت عمولة'],
  ] as [$id, $ico, $href, $lblId, $lblTxt])
  @php $on = ($active === $id); @endphp
  <a href="{{ $href }}" style="display:inline-flex;align-items:center;gap:7px;padding:8px 14px;border-radius:10px;text-decoration:none;white-space:nowrap;font-family:'Tajawal',sans-serif;transition:all .15s;{{ $on ? 'background:rgba(26,173,186,.16);border:1px solid rgba(26,173,186,.4);' : 'background:var(--bg3);border:1px solid var(--brd1);' }}">
    <span style="font-size:15px">{{ $ico }}</span>
    <span style="font-size:12px;font-weight:700;{{ $on ? 'color:var(--pri2);' : 'color:var(--tx);' }}" id="{{ $lblId }}">{{ $lblTxt }}</span>
  </a>
  @endforeach
  {{-- hidden anchors kept so layout's cNavApplyLang() never errors on missing sub/footer ids --}}
  <span id="cnav-hdr" style="display:none"></span>
  <span id="cnav-footer" style="display:none"></span>
</div>
