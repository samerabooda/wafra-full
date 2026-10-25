@extends('layouts.app')
@section('title','Mailbox')
@section('page-title','صندوق البريد')

@section('content')
@verbatim
<style>
.ib{max-width:1500px;margin:0 auto}
.ib-bar{display:flex;gap:8px;align-items:center;flex-wrap:wrap;background:var(--card-bg);border:1px solid var(--card-brd);border-radius:13px;padding:11px 14px;margin-bottom:14px}
.ib-f{padding:6px 14px;border-radius:20px;border:1px solid var(--brd2);background:none;cursor:pointer;font-size:12px;font-weight:700;color:var(--mu);font-family:'Tajawal',sans-serif;transition:all .15s}
.ib-f:hover,.ib-f.on{background:rgba(26,173,186,.14);border-color:var(--pri);color:var(--pri2)}
.ib-clear{padding:6px 12px;border-radius:20px;border:1px solid rgba(123,104,238,.3);background:rgba(123,104,238,.08);color:#a898ff;cursor:pointer;font-size:12px;font-weight:700;font-family:'Tajawal',sans-serif;display:none}
.ib-clear.on{display:inline-flex;align-items:center;gap:5px}
.ib-clear:hover{background:rgba(123,104,238,.18)}
.ib-panel{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:14px;overflow:hidden}
.ib-scroll{overflow:auto;max-height:calc(100vh - 230px)}
.ib-tbl{width:100%;border-collapse:separate;border-spacing:0;font-size:12.5px}
.ib-tbl thead th{position:sticky;top:0;z-index:2;background:var(--bg3);padding:10px 11px;font-size:10px;text-transform:uppercase;color:var(--m2);font-weight:800;text-align:right;white-space:nowrap;border-bottom:2px solid var(--pri)}
.ib-tbl thead th.kh{cursor:pointer;user-select:none;position:relative}
.ib-tbl thead th.kh:hover{background:rgba(26,173,186,.08)}
.ib-tbl thead th.kh.srt{color:var(--pri2)}
.ib-tbl thead th.kh.flt{color:#a898ff}
.ib-tbl thead th.kh .ar{display:inline-block;font-size:11px;margin-inline-start:4px}
.ib-tbl thead th.kh .kh-fn{display:inline-block;font-size:10px;opacity:.6;margin-inline-start:3px}
.ib-tbl thead th.kh.flt .kh-fn{opacity:1;color:#a898ff}
.ib-tbl tbody td{padding:10px 11px;border-bottom:1px solid var(--brd1);white-space:nowrap;vertical-align:top}
.ib-tbl tbody tr:hover td{background:rgba(26,173,186,.06)}
.ib-tbl tbody tr.unread td{background:rgba(245,166,35,.06)}
.ib-dir{display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:12px;font-size:10px;font-weight:800}
.ib-in{background:rgba(34,201,122,.14);color:#22c97a}
.ib-out{background:rgba(58,157,181,.14);color:#3a9db5}
.ib-ac{font-family:'JetBrains Mono',monospace;font-weight:800;color:var(--pri2)}
.ib-br{display:inline-block;padding:2px 8px;border-radius:9px;font-size:10.5px;font-weight:700;background:rgba(26,173,186,.1);color:var(--pri2);border:1px solid rgba(26,173,186,.22)}
.ib-st{font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px}
.ib-st-unread{background:rgba(245,166,35,.15);color:var(--or)}
.ib-st-read{background:rgba(34,201,122,.12);color:#22c97a}
.ib-mu{color:var(--mu)}
.ib-empty{text-align:center;padding:50px 14px;color:var(--mu)}
.ib-count{font-size:11px;color:var(--mu);font-weight:700}

/* Filter menu — copied from CC pattern */
#ib-fmenu{position:fixed;z-index:2147483000;background:var(--bg3);border:1px solid var(--card-brd);border-radius:12px;padding:10px;box-shadow:0 24px 60px rgba(0,0,0,.6);display:none;width:260px;max-height:80vh;overflow-y:auto}
.kfm-srt{display:flex;gap:6px;margin-bottom:7px}
.kfm-srt button{flex:1;padding:5px 7px;border-radius:7px;border:1px solid var(--brd2);background:var(--bg3);color:var(--tx);font-size:11px;font-weight:700;cursor:pointer}
.kfm-srt button:hover{border-color:#7b68ee;color:#a898ff}
.kfm-srch{width:100%;margin-bottom:7px;padding:6px 9px;font-size:12px}
.kfm-it{display:flex;align-items:center;gap:7px;padding:4px 6px;border-radius:6px;cursor:pointer;font-size:11.5px;color:var(--tx)}
.kfm-it:hover{background:rgba(123,104,238,.08)}
.kfm-it input{margin:0;flex-shrink:0}
.kfm-it span{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.kfm-all{border-bottom:1px solid var(--brd1);padding-bottom:7px;margin-bottom:4px;font-weight:800}
.kfm-list{max-height:220px;overflow-y:auto;margin-bottom:7px;padding-inline-end:3px}
.kfm-list::-webkit-scrollbar{width:6px}
.kfm-list::-webkit-scrollbar-thumb{background:rgba(255,255,255,.14);border-radius:3px}
.kfm-note{color:var(--mu);font-size:10px;padding:5px;text-align:center}
.kfm-act{display:flex;gap:6px;padding-top:7px;border-top:1px solid var(--brd1)}
.kfm-ok,.kfm-cl{flex:1;padding:7px;border-radius:7px;border:0;font-weight:800;font-size:11.5px;cursor:pointer}
.kfm-ok{background:linear-gradient(135deg,#7b68ee,#5f4bc4);color:#fff}
.kfm-ok:hover{filter:brightness(1.08)}
.kfm-cl{background:var(--bg4);color:var(--mu);border:1px solid var(--brd1)}
.kfm-cl:hover{color:var(--tx)}
</style>
@endverbatim

<div class="ib">
  <div class="ib-bar">
    <span style="font-size:20px">📬</span>
    <strong id="ib-title" style="font-size:14px">صندوق بريد مركز الاتصال</strong>
    <span class="ib-count" id="ib-count"></span>
    <div style="flex:1"></div>
    <button class="ib-f on" id="ib-f-all"  onclick="ibFilter('')">الكل</button>
    <button class="ib-f"    id="ib-f-in"   onclick="ibFilter('in')">📥 وارد</button>
    <button class="ib-f"    id="ib-f-out"  onclick="ibFilter('out')">📤 صادر</button>
    <button class="ib-clear" id="ib-clear-filters" onclick="ibkClearAll()" title="مسح كل الفلاتر">↺ <span id="ib-clear-l">مسح الفلاتر</span></button>
    <button class="btn btn-ghost btn-sm" onclick="ibLoad()">🔄 <span id="ib-refresh">تحديث</span></button>
  </div>

  <div class="ib-panel">
    <div class="ib-scroll">
      <table class="ib-tbl"><thead><tr id="ib-head"></tr></thead><tbody id="ib-body"></tbody></table>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
  'use strict';
  var AR={title:'صندوق بريد مركز الاتصال',titleFA:'صندوق البريد — كل التنبيهات',all:'الكل',inb:'📥 وارد',out:'📤 صادر',refresh:'تحديث',
    thDir:'النوع',thAc:'الحساب',thMonth:'الشهر',
    thFromU:'المرسل',thFromB:'فرع المرسل',thToU:'المستلم',thToB:'الفرع الموجه إليه',
    thCreated:'التاريخ',thDeliv:'وصل',thRead:'قُرئ',thStatus:'الحالة',
    dirIn:'📥 وارد',dirOut:'📤 صادر',unread:'غير مقروء',read:'مقروء',none:'لا توجد رسائل',tb:'صندوق البريد',loading:'جارٍ التحميل…',
    kfm_asc:'تصاعدي',kfm_desc:'تنازلي',kfm_srch:'بحث…',kfm_all:'تحديد الكل',kfm_apply:'تطبيق',kfm_clear:'مسح',kfm_blank:'(فارغ)',
    counter:function(n,t){return n+' من '+t+' رسالة';}};
  var EN={title:'Call-Center Mailbox',titleFA:'Mailbox — All Notifications',all:'All',inb:'📥 Incoming',out:'📤 Outgoing',refresh:'Refresh',
    thDir:'Type',thAc:'Account',thMonth:'Month',
    thFromU:'From',thFromB:'From Branch',thToU:'To',thToB:'Destination Branch',
    thCreated:'Date',thDeliv:'Delivered',thRead:'Read',thStatus:'Status',
    dirIn:'📥 In',dirOut:'📤 Out',unread:'Unread',read:'Read',none:'No messages',tb:'Mailbox',loading:'Loading…',
    kfm_asc:'Ascending',kfm_desc:'Descending',kfm_srch:'Search…',kfm_all:'Select all',kfm_apply:'Apply',kfm_clear:'Clear',kfm_blank:'(blank)',
    counter:function(n,t){return n+' of '+t+' messages';}};
  function L(){return (typeof curLang!=='undefined'?curLang:localStorage.getItem('wg_lang'))||'ar';}
  function t(k){return (L()==='en'?EN:AR)[k]||k;}
  function E(s){return window.esc?esc(s):String(s==null?'':s);}
  function dt(s){ if(!s)return '<span class="ib-mu">—</span>'; var d=new Date(s.replace(' ','T'));
    return d.toLocaleDateString(L()==='en'?'en-GB':'ar-EG',{month:'short',day:'numeric'})+' '+d.toLocaleTimeString([],{hour:'2-digit',minute:'2-digit'}); }

  var _data=null,_isFA=false,_filter='';
  // Per-column sort/filter state
  // [key, labelKey, sortable, filterable]
  var IBCOLS=[
    ['direction',      'thDir',     1, 1],
    ['account_number', 'thAc',      1, 1],
    ['month',          'thMonth',   1, 1],
    ['from_user',      'thFromU',   1, 1],
    ['from_branch',    'thFromB',   1, 1],
    ['to_user',        'thToU',     1, 1],
    ['to_branch',      'thToB',     1, 1],
    ['created_at',     'thCreated', 1, 0],
    ['delivered_at',   'thDeliv',   1, 0],
    ['read_at',        'thRead',    1, 0],
    ['status',         'thStatus',  1, 1],
  ];
  var IBSK='created_at', IBSD='desc';
  var IFV={}, IFCOL=null, IFITEMS=[], IFWORK=null, IFSEARCH='';

  window.ibFilter=function(f){_filter=f;['all','in','out'].forEach(function(x){var b=document.getElementById('ib-f-'+x);if(b)b.classList.toggle('on',(x==='all'&&f==='')||x===f);});render();};
  window.ibLoad=function(){
    document.getElementById('ib-body').innerHTML='<tr><td colspan="'+IBCOLS.length+'" class="ib-empty">'+t('loading')+'</td></tr>';
    api('GET','/notifications/inbox').then(function(r){
      if(r&&r.success){_data=r.data||[];_isFA=!!r.is_fa;render();}
      else document.getElementById('ib-body').innerHTML='<tr><td colspan="'+IBCOLS.length+'" class="ib-empty">—</td></tr>';
    });
  };

  // ─── Value extraction (for sort + filter) ──────────────────
  function rowStatus(n){ return n.read_at ? 'read' : 'unread'; }
  function rowDirection(n){
    if(n.direction && n.direction!=='all') return n.direction;
    return n.to_user ? 'in' : 'out';
  }
  function rowValue(n, col){
    switch(col){
      case 'direction':      return rowDirection(n);
      case 'account_number': return n.account_number||'';
      case 'month':          return n.month||'';
      case 'from_user':      return n.from_user||'';
      case 'from_branch':    return n.from_branch||'';
      case 'to_user':        return n.to_user||'';
      case 'to_branch':      return n.to_branch||'';
      case 'created_at':     return n.created_at ? new Date(n.created_at.replace(' ','T')).getTime() : 0;
      case 'delivered_at':   return n.delivered_at ? new Date(n.delivered_at.replace(' ','T')).getTime() : 0;
      case 'read_at':        return n.read_at ? new Date(n.read_at.replace(' ','T')).getTime() : 0;
      case 'status':         return rowStatus(n);
    }
    return '';
  }
  function rowLabel(n, col){
    var v = rowValue(n, col);
    if (col==='direction') return v==='in' ? t('dirIn') : t('dirOut');
    if (col==='status')    return v==='read' ? t('read') : t('unread');
    if (col==='created_at'||col==='delivered_at'||col==='read_at') {
      return v ? new Date(v).toLocaleDateString(L()==='en'?'en-GB':'ar-EG') : t('kfm_blank');
    }
    return (v===''||v==null) ? t('kfm_blank') : String(v);
  }

  function sortCompare(a, b, col){
    var va = rowValue(a, col), vb = rowValue(b, col);
    if (typeof va==='number' && typeof vb==='number') return va - vb;
    return String(va).toLowerCase().localeCompare(String(vb).toLowerCase(), undefined, {numeric:true});
  }

  function applyFilters(rows){
    // Direction chip
    if (_filter) rows = rows.filter(function(n){ return rowDirection(n)===_filter; });
    // Per-column filter
    for (var col in IFV) {
      var sel = IFV[col];
      if (!sel || !sel.length) continue;
      if (sel.length===1 && sel[0]==='__none__') { rows = []; break; }
      var set = new Set(sel.map(String));
      rows = rows.filter(function(n){
        var v = rowValue(n, col);
        var key = (v===''||v==null) ? '__blank__' : String(v);
        return set.has(key);
      });
    }
    // Sort
    if (IBSK) {
      rows = rows.slice().sort(function(a,b){
        var d = sortCompare(a, b, IBSK);
        return IBSD==='asc' ? d : -d;
      });
    }
    return rows;
  }
  function hasActiveFilter(){
    if (_filter) return true;
    for (var k in IFV) if (IFV[k] && IFV[k].length) return true;
    return !(IBSK==='created_at' && IBSD==='desc');
  }

  function head(){
    var th = IBCOLS.map(function(c){
      var key=c[0], lk=c[1], sortable=c[2], filterable=c[3];
      var label = t(lk);
      if (!sortable) return '<th>'+label+'</th>';
      var sorted  = IBSK===key;
      var filtered= !!(IFV[key] && IFV[key].length);
      var arrow   = sorted ? (IBSD==='asc'?'▲':'▼') : '';
      var funnel  = filtered ? '🔽' : (filterable ? '▾' : '');
      return '<th class="kh'+(sorted?' srt':'')+(filtered?' flt':'')+'" '
        + 'title="'+(L()==='en'?'Sort &amp; filter':'فرز وتصفية')+'" '
        + 'onclick="ibkMenu(event,\''+key+'\','+(filterable?1:0)+')">'
        + '<span>'+label+'</span>'
        + (arrow?' <span class="ar">'+arrow+'</span>':'')
        + (funnel?' <span class="kh-fn">'+funnel+'</span>':'')
        + '</th>';
    }).join('');
    document.getElementById('ib-head').innerHTML = th;
  }

  function render(){
    head();
    var tb=document.querySelector('.tb-title');if(tb)tb.textContent=t('tb');
    document.getElementById('ib-title').textContent=_isFA?t('titleFA'):t('title');
    if(!_data){ document.getElementById('ib-count').textContent=''; return;}
    var totalCount = _data.length;
    var rows = applyFilters(_data);
    var cnt = document.getElementById('ib-count');
    if (cnt) cnt.textContent = t('counter')(rows.length, totalCount);
    var cl = document.getElementById('ib-clear-filters');
    if (cl) cl.classList.toggle('on', hasActiveFilter());

    if(!rows.length){document.getElementById('ib-body').innerHTML='<tr><td colspan="'+IBCOLS.length+'" class="ib-empty">🔕 '+t('none')+'</td></tr>';return;}
    document.getElementById('ib-body').innerHTML=rows.map(function(n){
      var isOut=(rowDirection(n)==='out');
      var dirChip=isOut?'<span class="ib-dir ib-out">'+t('dirOut')+'</span>':'<span class="ib-dir ib-in">'+t('dirIn')+'</span>';
      var unread=!n.read_at;
      return '<tr class="'+(unread?'unread':'')+'">'+
        '<td>'+dirChip+'</td>'+
        '<td><span class="ib-ac">'+E(n.account_number||'—')+'</span></td>'+
        '<td class="ib-mu">'+E(n.month||'—')+'</td>'+
        '<td>'+E(n.from_user||'—')+'</td>'+
        '<td>'+(n.from_branch?'<span class="ib-br">'+E(n.from_branch)+'</span>':'<span class="ib-mu">—</span>')+'</td>'+
        '<td>'+E(n.to_user||'—')+'</td>'+
        '<td>'+(n.to_branch?'<span class="ib-br">'+E(n.to_branch)+'</span>':'<span class="ib-mu">—</span>')+'</td>'+
        '<td class="ib-mu">'+dt(n.created_at)+'</td>'+
        '<td>'+dt(n.delivered_at)+'</td>'+
        '<td>'+(n.read_at?'<span style="color:#22c97a">'+dt(n.read_at)+'</span>':'<span class="ib-mu">—</span>')+'</td>'+
        '<td><span class="ib-st '+(unread?'ib-st-unread':'ib-st-read')+'">'+(unread?t('unread'):t('read'))+'</span></td>'+
        '</tr>';
    }).join('');
  }

  // ─── Filter menu (per-column dropdown) ──────────────────────
  function ibkEnsureMenu(){
    if (document.getElementById('ib-fmenu')) return;
    var el = document.createElement('div');
    el.id = 'ib-fmenu';
    document.body.appendChild(el);
    document.addEventListener('click', function(e){
      var m = document.getElementById('ib-fmenu');
      if (!m) return;
      if (m.style.display==='none') return;
      if (m.contains(e.target)) return;
      if (e.target.closest('th.kh')) return;
      m.style.display='none';
    });
  }
  function ibkPosition(anchor){
    var m = document.getElementById('ib-fmenu');
    var r = anchor.getBoundingClientRect();
    var top = r.bottom + 4;
    var left = r.left;
    var mw = 260;
    if (left + mw > window.innerWidth - 10) left = window.innerWidth - mw - 10;
    if (left < 10) left = 10;
    m.style.top = top + 'px';
    m.style.left = left + 'px';
  }
  window.ibkMenu = function(ev, col, filterable){
    ev.stopPropagation();
    var anchor = ev.currentTarget;
    ibkEnsureMenu();
    var m = document.getElementById('ib-fmenu');
    IFCOL = col; IFSEARCH = ''; IFITEMS = []; IFWORK = new Set();

    // Build items from "all except this column" so other filters are reflected
    var _save = IFV[col]; delete IFV[col];
    var _savedFilter = _filter;
    var src = _data || [];
    var pool = applyFilters(src);
    if (_save) IFV[col] = _save;

    var seen = {}, items = [];
    pool.forEach(function(n){
      var v = rowValue(n, col);
      var key = (v===''||v==null) ? '__blank__' : String(v);
      if (seen[key]) return; seen[key]=1;
      items.push({ v:key, label: rowLabel(n, col) });
    });
    items.sort(function(a,b){ return String(a.label).localeCompare(String(b.label), undefined, {numeric:true}); });

    IFITEMS = items;
    var sel = IFV[col] ? new Set(IFV[col].map(String)) : null;
    IFWORK = new Set();
    IFITEMS.forEach(function(it){ if (!sel || sel.has(String(it.v))) IFWORK.add(String(it.v)); });

    if (filterable) {
      ibkBuildShell(true);
      ibkRenderList();
    } else {
      ibkBuildShell(false);
    }
    m.style.display = 'block';
    ibkPosition(anchor);
  };
  function ibkBuildShell(withList){
    var m = document.getElementById('ib-fmenu');
    var h = '<div class="kfm-srt">'
          + '<button type="button" onclick="ibkMenuSort(\'asc\')">▲ '+t('kfm_asc')+'</button>'
          + '<button type="button" onclick="ibkMenuSort(\'desc\')">▼ '+t('kfm_desc')+'</button></div>';
    if (withList) {
      h += '<input class="kfm-srch form-control" placeholder="'+t('kfm_srch')+'" oninput="ibkMenuSearch(this.value)">'
        + '<label class="kfm-it kfm-all"><input type="checkbox" id="ibk-allck" onchange="ibkMenuAll(this.checked)"> <b>'+t('kfm_all')+'</b> <span id="ibk-cnt" style="color:var(--mu);font-weight:600"></span></label>'
        + '<div class="kfm-list" id="ibk-list"></div>'
        + '<div class="kfm-act">'
        +   '<button class="kfm-ok" type="button" onclick="ibkMenuApply()">'+t('kfm_apply')+'</button>'
        +   '<button class="kfm-cl" type="button" onclick="ibkMenuClear()">'+t('kfm_clear')+'</button>'
        + '</div>';
    }
    m.innerHTML = h;
  }
  function ibkRenderList(){
    var lst = document.getElementById('ibk-list'); if (!lst) return;
    var s = IFSEARCH.toLowerCase();
    var list = IFITEMS.filter(function(it){ return !s || String(it.label).toLowerCase().indexOf(s)>=0; });
    var cap = 800, shown = list.slice(0, cap);
    var escf = window.esc || function(x){ return String(x).replace(/[&<>"]/g,function(c){return{'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c];}); };
    lst.innerHTML = shown.map(function(it){
      var v = String(it.v);
      return '<label class="kfm-it"><input type="checkbox" '+(IFWORK.has(v)?'checked':'')
        +' onchange="ibkTick(this)" data-v="'+escf(v)+'"> <span>'+escf(it.label)+'</span></label>';
    }).join('') || ('<div style="padding:10px;color:var(--mu);text-align:center">—</div>');
    if (list.length > cap) lst.innerHTML += '<div class="kfm-note">+'+(list.length-cap)+' …</div>';
    ibkCnt();
  }
  function ibkCnt(){
    var cnt = document.getElementById('ibk-cnt');
    if (cnt) cnt.textContent = '('+IFWORK.size+'/'+IFITEMS.length+')';
    var a = document.getElementById('ibk-allck');
    if (a) a.checked = (IFITEMS.length>0 && IFWORK.size===IFITEMS.length);
  }
  window.ibkTick = function(cb){
    var v = cb.getAttribute('data-v');
    if (cb.checked) IFWORK.add(v); else IFWORK['delete'](v);
    ibkCnt();
  };
  window.ibkMenuAll    = function(ck){ IFWORK = ck ? new Set(IFITEMS.map(function(it){return String(it.v);})) : new Set(); ibkRenderList(); };
  window.ibkMenuSearch = function(v){ IFSEARCH = v; ibkRenderList(); };
  window.ibkMenuSort   = function(dir){ IBSK = IFCOL; IBSD = dir; document.getElementById('ib-fmenu').style.display='none'; render(); };
  window.ibkMenuApply  = function(){
    if (IFWORK.size === IFITEMS.length)      { delete IFV[IFCOL]; }
    else if (IFWORK.size === 0)              { IFV[IFCOL] = ['__none__']; }
    else                                     { IFV[IFCOL] = Array.from(IFWORK); }
    document.getElementById('ib-fmenu').style.display='none';
    render();
  };
  window.ibkMenuClear  = function(){ delete IFV[IFCOL]; document.getElementById('ib-fmenu').style.display='none'; render(); };
  window.ibkClearAll   = function(){
    IFV = {}; IBSK='created_at'; IBSD='desc'; _filter='';
    ['all','in','out'].forEach(function(x){var b=document.getElementById('ib-f-'+x);if(b)b.classList.toggle('on',x==='all');});
    render();
  };

  var _o=window.applyLang;window.applyLang=function(l){if(_o)_o(l);try{render();}catch(e){}};
  ibLoad();
})();
</script>
@endpush
