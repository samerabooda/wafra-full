<!DOCTYPE html>
<html lang="ar" dir="rtl" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0,maximum-scale=1.0,user-scalable=no,viewport-fit=cover">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#0A1628">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="وفرة الخليجية">
<meta name="mobile-web-app-capable" content="yes">
<title>{{ config('app.name', 'وفرة الخليجية') }} — @yield('title','لوحة المتابعة')</title>

 Fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500;600;700&display=swap" rel="stylesheet">
 Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Chart.js global defaults — Tajawal font for all charts
if (typeof Chart !== 'undefined') {
  Chart.defaults.font.family = "'Tajawal', 'JetBrains Mono', sans-serif";
  Chart.defaults.font.size   = 12;
  Chart.defaults.color       = '#6B849E';
  Chart.defaults.plugins.tooltip.titleFont = { family: "'Tajawal', sans-serif", size: 12, weight: '700' };
  Chart.defaults.plugins.tooltip.bodyFont  = { family: "'Tajawal', sans-serif", size: 11 };
  Chart.defaults.plugins.legend.labels.font = { family: "'Tajawal', sans-serif", size: 11 };
}
</script>
 SheetJS (Excel import/export) -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
 jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
/* ═══ CSS VARIABLES ═══ */
:root {
  /* ── Brand ── */
  --teal:  #14B87E;
  --teal2: #0E9469;
  --teal3: #0B7556;
  --pri:   #2A82A8;
  --pri2:  #37A0CC;
  --pri3:  #1A5F7A;

  /* ── Dark backgrounds (deep navy/slate — easier on eye) ── */
  --bg:    #0C1420;   /* page bg — very dark navy */
  --bg1:   #111A2B;   /* sidebar bg */
  --bg2:   #162034;   /* cards, panels */
  --bg3:   #1C2940;   /* inputs, table rows */
  --bg4:   #21304A;   /* hover states */

  /* ── Borders (subtle) ── */
  --brd1: rgba(255,255,255,.07);
  --brd2: rgba(255,255,255,.13);

  /* ── Text ── */
  --tx:   #E8EEF5;    /* primary text — warm white */
  --tx2:  #B8C8D8;    /* secondary text */
  --mu:   #6B849E;    /* muted — readable */
  --mu2:  #4A6480;    /* very muted */

  /* ── Accents ── */
  --gr:   #1DC87A;    /* success green */
  --re:   #E04848;    /* danger red */
  --or:   #F59820;    /* warning orange */
  --pu:   #7C6EEE;    /* purple accent */
  --gold: #D4A83A;    /* premium gold */

  /* ── Semantic ── */
  --card-bg:  var(--bg2);
  --card-brd: var(--brd1);
  --inp-bg:   var(--bg3);
  --inp-brd:  var(--brd2);
  --sb-bg:    var(--bg1);
  --topb-bg:  rgba(12,20,32,.96);
  --shadow:0 8px 32px rgba(0,0,0,.4);
}
[data-theme="light"] {
  --bg:  #F0F4F8;
  --bg1: #FFFFFF;
  --bg2: #FAFCFE;
  --bg3: #F4F7FB;
  --bg4: #EAF0F7;
  --brd1: rgba(0,0,0,.09);
  --brd2: rgba(0,0,0,.15);
  --tx:  #0D1E2E;
  --tx2: #2A4A66;
  --mu:  #5A7A96;
  --mu2: #3A5A78;
  --card-bg:#FFFFFF;--card-brd:#A8C8E0;
  --sb-bg:#0D2B45;--topb-bg:rgba(13,43,69,.98);
  --inp-bg:#FFFFFF;--inp-brd:#5A9AC0;
  --shadow:0 4px 24px rgba(46,134,171,.15);
  --gr:#1A7A40;--or:#B05500;--re:#C00020;
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html{font-size:16px}
body{font-family:'Tajawal',sans-serif;background:var(--bg);color:var(--tx);overscroll-behavior:none;min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s}
body::before{content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:radial-gradient(ellipse 900px 500px at 70% -10%,rgba(46,134,171,.1),transparent 55%),
    radial-gradient(ellipse 600px 400px at -5% 80%,rgba(46,134,171,.05),transparent 50%)}

/* ── Layout ── */
.app-layout{display:flex;min-height:100vh}
.sidebar{width:240px;background:var(--sb-bg);border-left:1px solid var(--brd1);
  display:flex;flex-direction:column;flex-shrink:0;position:sticky;top:0;height:100vh;overflow-y:auto}
.main-wrap{flex:1;display:flex;flex-direction:column;overflow:hidden}
.topbar{background:var(--topb-bg);backdrop-filter:blur(14px);border-bottom:1px solid var(--brd1);
  padding:0 20px;height:54px;display:flex;align-items:center;justify-content:space-between;
  position:sticky;top:0;z-index:40}
.page-body{flex:1;overflow-y:auto;padding:20px}

/* ── Sidebar ── */
.sb-header{padding:14px;border-bottom:1px solid rgba(255,255,255,.08);display:flex;align-items:center;gap:10px}
.sb-logo{width:38px;height:38px;border-radius:8px;background:white;padding:3px;object-fit:contain;flex-shrink:0}
.sb-logo-3d{width:48px;height:48px;flex-shrink:0}
.sb-logo-3d svg,.tb-logo-3d svg,.mob-logo-3d svg{width:100%;height:100%;display:block}
.tb-logo-3d{width:40px;height:40px}
.mob-logo-3d{width:36px;height:36px}
.sb-brand{font-size:12px;font-weight:800;color:white;line-height:1.2}
.sb-brand small{display:block;font-size:9px;color:rgba(255,255,255,.4)}
.sb-nav{flex:1;padding:10px 8px}
.nav-section{font-size:9px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.6px;padding:8px 10px 4px;margin-top:4px}
.nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;
  font-size:12px;font-weight:500;color:rgba(255,255,255,.6);cursor:pointer;
  text-decoration:none;position:relative;margin-bottom:1px}
.nav-item:hover{background:rgba(255,255,255,.08);color:white}
.nav-item.active{background:rgba(20,184,126,.12);color:var(--teal);font-weight:700;letter-spacing:.01em}
.nav-item.active::before{content:'';position:absolute;right:0;top:22%;bottom:22%;
  width:3px;background:var(--teal);border-radius:2px 0 0 2px}
.nav-badge{margin-right:auto;padding:1px 7px;border-radius:20px;font-size:9px;font-weight:700;
  background:rgba(46,134,171,.2);color:var(--pri2);border:1px solid rgba(46,134,171,.3)}
.nav-badge.orange{background:rgba(245,166,35,.15);color:var(--or);border-color:rgba(245,166,35,.3)}
.nav-badge.green{background:rgba(34,201,122,.1);color:var(--gr);border-color:rgba(34,201,122,.2)}
.sb-footer{padding:10px 8px;border-top:1px solid rgba(255,255,255,.08)}
.user-chip{display:flex;align-items:center;gap:8px;padding:8px 10px;
  background:rgba(255,255,255,.05);border-radius:9px;border:1px solid rgba(255,255,255,.08)}
.user-avatar{width:28px;height:28px;border-radius:50%;
  background:linear-gradient(135deg,var(--pri2),var(--pri3));
  display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:white;flex-shrink:0}
.user-name{font-size:11px;font-weight:700;color:white;flex:1}
.user-role{font-size:9px;color:rgba(255,255,255,.5)}
.logout-btn{color:rgba(255,255,255,.4);font-size:12px;text-decoration:none;cursor:pointer}
.logout-btn:hover{color:var(--re)}

/* ── Topbar ── */
.tb-left{display:flex;align-items:center;gap:12px}
.tb-logo{width:32px;height:32px;border-radius:6px;background:white;padding:2px;object-fit:contain}
.tb-title{font-size:14px;font-weight:800}
.tb-sub{font-size:11px;color:var(--mu)}
.tb-right{display:flex;align-items:center;gap:6px}
.tb-btn{display:flex;align-items:center;gap:5px;background:var(--bg3);
  border:1px solid var(--brd1);border-radius:7px;padding:6px 10px;
  font-size:11px;font-weight:600;color:var(--m2);cursor:pointer;
  font-family:'Tajawal',sans-serif;text-decoration:none;white-space:nowrap}
.tb-btn:hover{border-color:var(--pri);color:var(--pri2)}
.tb-btn.primary{background:rgba(46,134,171,.15);border-color:rgba(46,134,171,.35);color:var(--pri2)}
.tb-btn.success{background:rgba(34,201,122,.1);border-color:rgba(34,201,122,.25);color:var(--gr)}
.theme-toggle{background:none;border:1px solid var(--brd1);border-radius:8px;
  padding:5px 8px;cursor:pointer;font-size:14px}

/* ── Cards / KPIs ── */
.kpi-grid{display:grid;grid-template-columns:repeat(5,1fr);gap:10px;margin-bottom:16px}
.kpi-card{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:13px;
  padding:14px 16px;position:relative;overflow:hidden;transition:all .2s}
.kpi-card:hover{border-color:var(--pri);transform:translateY(-2px)}
.kpi-card::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px}
.kpi-blue::after{background:linear-gradient(90deg,transparent,var(--pri),transparent)}
.kpi-teal::after{background:linear-gradient(90deg,transparent,var(--pri2),transparent)}
.kpi-green::after{background:linear-gradient(90deg,transparent,var(--gr),transparent)}
.kpi-orange::after{background:linear-gradient(90deg,transparent,var(--or),transparent)}
.kpi-purple::after{background:linear-gradient(90deg,transparent,var(--pu),transparent)}
.kpi-label{font-size:9px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
.kpi-value{font-size:1.3rem;font-weight:800;font-family:'JetBrains Mono',monospace;margin-bottom:3px}
.kpi-blue .kpi-value{color:var(--pri)}.kpi-teal .kpi-value{color:var(--pri2)}
.kpi-green .kpi-value{color:var(--gr)}.kpi-orange .kpi-value{color:var(--or)}
.kpi-purple .kpi-value{color:var(--pu)}
.kpi-sub{font-size:10px;color:var(--mu)}
.kpi-icon{position:absolute;left:-4px;bottom:-6px;font-size:44px;opacity:.05}

/* ── Panel / Card ── */
.panel{background:var(--card-bg);border:1px solid var(--card-brd);border-radius:13px;overflow:hidden;margin-bottom:16px}
.panel-header{padding:14px 16px;border-bottom:1px solid var(--brd1);
  display:flex;align-items:center;justify-content:space-between}
.panel-title{font-size:13px;font-weight:700}
.panel-body{padding:16px}

/* ── Table ── */
.data-table{width:100%;border-collapse:collapse}
.data-table th{font-size:10px;color:var(--mu);text-transform:uppercase;
  padding:10px 12px;text-align:right;border-bottom:2px solid var(--brd1);
  background:var(--inp-bg);white-space:nowrap;font-weight:700;position:sticky;top:0;z-index:2}
.data-table td{font-size:12px;padding:9px 12px;border-bottom:1px solid rgba(37,58,99,.3)}
.data-table tr:hover td{background:rgba(46,134,171,.04)}
.data-table tr:last-child td{border-bottom:none}
.data-table tr.row-modified td{background:rgba(245,166,35,.06)!important}
.data-table tr.row-modified td:first-child{border-right:3px solid var(--or)}
.table-scroll{overflow-x:auto;max-height:500px;overflow-y:auto}

/* ── Forms ── */
.form-group{margin-bottom:14px}
.form-label{display:block;font-size:10px;color:var(--mu);text-transform:uppercase;
  letter-spacing:.4px;margin-bottom:6px;font-weight:600}
.form-control{width:100%;background:var(--inp-bg);border:1px solid var(--inp-brd);
  border-radius:9px;padding:10px 13px;color:var(--tx);
  font-family:'Tajawal',sans-serif;font-size:14px;outline:none;transition:border-color .2s}
.form-control:focus{border-color:var(--pri);box-shadow:0 0 0 3px rgba(46,134,171,.1)}
.form-row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
.form-row-3{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
.form-section{margin-bottom:18px;padding-bottom:18px;border-bottom:1px solid var(--brd1)}
.form-section:last-child{border-bottom:none;margin-bottom:0;padding-bottom:0}
.form-section-title{font-size:11px;font-weight:700;color:var(--pri2);text-transform:uppercase;letter-spacing:.5px;margin-bottom:12px}

/* ── Buttons ── */
.btn{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;border:none;
  border-radius:9px;font-family:'Tajawal',sans-serif;font-size:13px;font-weight:700;cursor:pointer}
.btn-primary{background:linear-gradient(135deg,var(--pri2),var(--pri),var(--pri3));color:white;box-shadow:0 4px 14px rgba(46,134,171,.3)}
.btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(46,134,171,.4)}
.btn-success{background:var(--gr);color:white}.btn-success:hover{opacity:.9}
.btn-danger{background:var(--re);color:white}.btn-danger:hover{opacity:.9}
.btn-warning{background:var(--or);color:white}.btn-warning:hover{opacity:.9}
.btn-ghost{background:var(--inp-bg);border:1px solid var(--brd1);color:var(--m2)}
.btn-ghost:hover{border-color:var(--pri);color:var(--pri2)}
.btn-sm{padding:6px 12px;font-size:11px;border-radius:7px}
.btn-xl{padding:12px 28px;font-size:15px;border-radius:11px}

/* ── Badges ── */
.badge{display:inline-flex;align-items:center;gap:3px;padding:2px 8px;border-radius:12px;font-size:10px;font-weight:700}
.badge-blue{background:rgba(46,134,171,.15);color:var(--pri2);border:1px solid rgba(46,134,171,.25)}
.badge-green{background:rgba(34,201,122,.12);color:var(--gr);border:1px solid rgba(34,201,122,.22)}
.badge-orange{background:rgba(245,166,35,.15);color:var(--or);border:1px solid rgba(245,166,35,.25)}
.badge-red{background:rgba(224,80,80,.12);color:var(--re);border:1px solid rgba(224,80,80,.22)}
.badge-purple{background:rgba(123,104,238,.12);color:var(--pu);border:1px solid rgba(123,104,238,.22)}
.badge-gray{background:rgba(90,122,154,.1);color:var(--m2);border:1px solid var(--brd1)}

/* ── Alerts ── */
.alert{padding:11px 14px;border-radius:9px;font-size:12px;margin-bottom:14px;display:none}
.alert.show{display:block}
.alert-error{background:rgba(224,80,80,.08);border:1px solid rgba(224,80,80,.25);color:var(--re)}
.alert-success{background:rgba(34,201,122,.08);border:1px solid rgba(34,201,122,.25);color:var(--gr)}
.alert-info{background:rgba(46,134,171,.08);border:1px solid rgba(46,134,171,.2);color:var(--pri2)}
.alert-warning{background:rgba(245,166,35,.08);border:1px solid rgba(245,166,35,.2);color:var(--or)}

/* ── Modal ── */
.modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,.85);z-index:9000;
  display:none;align-items:center;justify-content:center;backdrop-filter:blur(7px);padding:16px}
.modal-overlay.open{display:flex}
.modal{background:var(--bg3);border:1px solid rgba(46,134,171,.2);border-radius:18px;
  width:580px;max-width:100%;max-height:88dvh;display:flex;flex-direction:column;
  box-shadow:var(--shadow);animation:modalUp .3s cubic-bezier(.16,1,.3,1)}
.modal.modal-wide{width:720px}.modal.modal-narrow{width:460px}.modal.modal-xl{width:860px}
@keyframes modalUp{from{opacity:0;transform:scale(.96) translateY(10px)}to{opacity:1;transform:none}}
.modal-header{padding:15px 19px;border-bottom:1px solid var(--brd1);
  display:flex;align-items:center;justify-content:space-between;flex-shrink:0}
.modal-title{font-size:14px;font-weight:800}
.modal-close{background:var(--inp-bg);border:1px solid var(--brd1);border-radius:6px;
  width:26px;height:26px;display:flex;align-items:center;justify-content:center;
  font-size:13px;color:var(--mu);cursor:pointer}
.modal-close:hover{border-color:var(--re);color:var(--re)}
.modal-body{padding:16px 19px;overflow-y:auto;flex:1}
.modal-footer{padding:11px 19px;border-top:1px solid var(--brd1);
  display:flex;gap:7px;justify-content:flex-end;flex-shrink:0}

/* ── Toast ── */
.toast-container{position:fixed;bottom:20px;left:50%;transform:translateX(-50%);
  z-index:99999;display:flex;flex-direction:column;gap:8px;pointer-events:none}
.toast{padding:10px 22px;border-radius:10px;font-size:12px;font-weight:700;
  white-space:nowrap;animation:toastIn .3s ease;box-shadow:var(--shadow)}
.toast-success{background:rgba(34,201,122,.15);border:1px solid rgba(34,201,122,.35);color:var(--gr)}
.toast-error{background:rgba(224,80,80,.15);border:1px solid rgba(224,80,80,.35);color:var(--re)}
.toast-info{background:rgba(46,134,171,.15);border:1px solid rgba(46,134,171,.35);color:var(--pri2)}
@keyframes toastIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}

/* ── AC Number ── */
.ac-num{font-family:'JetBrains Mono',monospace;font-size:11px;font-weight:700;color:var(--pri2);
  background:rgba(46,134,171,.12);padding:3px 8px;border-radius:5px;border:1px solid rgba(46,134,171,.25)}
.mono{font-family:'JetBrains Mono',monospace}
.c-blue{color:var(--pri)}.c-teal{color:var(--pri2)}.c-green{color:var(--gr)}
.c-orange{color:var(--or)}.c-red{color:var(--re)}.c-muted{color:var(--mu)}

/* ── Scrollbar ── */

/* ── iOS zoom prevention ── */
input,select,textarea{font-size:16px!important}
@media(max-width:768px){
  input[type="text"],input[type="email"],input[type="password"],
  input[type="number"],input[type="search"],select,textarea{
    font-size:16px!important;
  }
}
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-thumb{background:var(--brd2);border-radius:3px}
/* Mobile touch optimizations */
.table-scroll{-webkit-overflow-scrolling:touch;overflow-x:auto}
.tr-draggable{cursor:grab;touch-action:pan-y}
.col-pill{touch-action:manipulation}
.mob-nav-item{touch-action:manipulation}
.btn{touch-action:manipulation}

/* ══════════════════════════════════════════
   MOBILE NAVIGATION ELEMENTS
   ══════════════════════════════════════════ */
.mob-header{
  display:none;position:fixed;top:0;left:0;right:0;z-index:1000;
  height:56px;background:var(--sb-bg);border-bottom:1px solid rgba(255,255,255,.1);
  align-items:center;padding:0 14px;gap:10px;
}
.mob-burger{
  width:38px;height:38px;border-radius:9px;background:rgba(255,255,255,.1);
  border:none;color:white;font-size:20px;cursor:pointer;
  display:flex;align-items:center;justify-content:center;flex-shrink:0;
}
.mob-logo-row{display:flex;align-items:center;gap:9px;flex:1;min-width:0}
.mob-logo{width:30px;height:30px;object-fit:contain;border-radius:7px;background:white;padding:2px;flex-shrink:0}
.mob-title{font-size:13px;font-weight:800;color:white;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mob-title small{display:block;font-size:9px;color:rgba(255,255,255,.5);font-weight:400}
.mob-theme-btn{width:34px;height:34px;border-radius:8px;background:rgba(255,255,255,.1);
  border:none;color:white;font-size:15px;cursor:pointer;flex-shrink:0;display:flex;align-items:center;justify-content:center}

.mob-overlay{
  display:none;position:fixed;inset:0;background:rgba(0,0,0,.65);
  z-index:1500;backdrop-filter:blur(4px);
}
.mob-overlay.open{display:block}

.mob-bottom-nav{
  display:none;position:fixed;bottom:0;left:0;right:0;z-index:998;
  background:var(--sb-bg);border-top:1px solid rgba(255,255,255,.08);
  padding:4px 0 calc(6px + env(safe-area-inset-bottom));
}
.mob-nav-items{display:flex;justify-content:space-around;align-items:flex-start}
.mob-nav-item{
  display:flex;flex-direction:column;align-items:center;gap:2px;
  padding:5px 8px;border-radius:10px;cursor:pointer;
  min-width:52px;border:none;background:none;
  font-family:'Tajawal',sans-serif;transition:all .15s;position:relative;
}
.mob-nav-item:active{transform:scale(.9)}
.mob-nav-item.on{background:rgba(46,134,171,.2)}
.mob-nav-icon{font-size:20px;line-height:1.2}
.mob-nav-lbl{font-size:9px;color:rgba(255,255,255,.55);font-weight:600;white-space:nowrap}
.mob-nav-item.on .mob-nav-lbl{color:var(--pri2)}
.mob-nav-dot{
  position:absolute;top:3px;right:5px;width:8px;height:8px;
  border-radius:50%;background:var(--or);border:2px solid var(--sb-bg);display:none;
}
.mob-nav-dot.show{display:block}

.mob-more-sheet{
  position:fixed;bottom:0;left:0;right:0;z-index:1600;
  background:var(--bg2);border-radius:20px 20px 0 0;
  border-top:1px solid var(--brd1);
  transform:translateY(100%);transition:transform .3s cubic-bezier(.16,1,.3,1);
  padding:14px 0 calc(16px + env(safe-area-inset-bottom));
  max-height:70dvh;overflow-y:auto;
}
.mob-more-sheet.open{transform:translateY(0)}
.mob-sheet-handle{width:38px;height:4px;background:var(--brd2);border-radius:2px;margin:0 auto 14px}
.mob-sheet-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:0 14px 6px}
.mob-sheet-item{
  display:flex;flex-direction:column;align-items:center;gap:5px;
  padding:12px 6px;border-radius:12px;background:var(--bg3);
  border:1px solid var(--brd1);cursor:pointer;text-align:center;
}
.mob-sheet-item:active{transform:scale(.95)}
.mob-sheet-ico{font-size:22px}
.mob-sheet-lbl{font-size:10px;color:var(--m2);font-weight:600;line-height:1.2}

/* ══════════════════════════════════════════
   RESPONSIVE BREAKPOINTS
   ══════════════════════════════════════════ */
@media(max-width:768px){
  /* Show mobile UI */
  .mob-header{display:flex!important}
  .mob-bottom-nav{display:block!important}

  /* Hide desktop elements */
  .sidebar{
    position:fixed!important;right:0;top:0;bottom:0;z-index:1600;
    transform:translateX(100%);transition:transform .3s cubic-bezier(.16,1,.3,1);
    width:270px!important;height:100dvh!important;overflow-y:auto;
  }
  .sidebar.mob-open{transform:translateX(0)!important;box-shadow:-8px 0 30px rgba(0,0,0,.5)}

  /* Topbar */
  .topbar{top:56px!important;padding:0 10px!important;height:46px!important}
  .tb-title{font-size:12px}
  .tb-sub{display:none}
  /* Hide extra topbar buttons on mobile */
  .tb-right .tb-btn:nth-child(n+3){display:none}

  /* Content */
  .main-wrap{padding-top:56px}
  .page-body{padding:10px 12px;padding-bottom:75px!important}

  /* KPIs */
  .kpi-grid{grid-template-columns:repeat(2,1fr)!important;gap:8px}
  .kpi-card{padding:10px 12px}
  .kpi-value{font-size:1.05rem!important}
  .kpi-label{font-size:8px!important}

  /* Charts */
  .crow2,.top2row,.trow{grid-template-columns:1fr!important}
  .cbx{height:160px!important}

  /* Cards grid */
  .cgd{grid-template-columns:1fr!important}

  /* FA perm grid */
  .fa-perm-grid{grid-template-columns:1fr 1fr!important}

  /* Employees grid */
  .egd{grid-template-columns:1fr!important}

  /* Settings grid */
  .sgd{grid-template-columns:1fr!important}

  /* Modals — slide up from bottom */
  .modal{
    width:100%!important;max-width:100%!important;
    max-height:92dvh!important;
    border-radius:20px 20px 0 0!important;
    position:fixed;bottom:0;left:0;right:0;margin:0;
  }
  .modal-overlay{align-items:flex-end!important;padding:0!important}

  /* Forms — iOS zoom prevention: inputs must be ≥16px */
  .form-control{font-size:16px!important}
  select.form-control{font-size:16px!important}
  .form-row,.form-row-3{grid-template-columns:1fr!important}

  /* Reports */
  .rpt-tabs{overflow-x:auto;flex-wrap:nowrap;gap:2px}
  .rtab{font-size:10px;padding:6px 8px;white-space:nowrap}
  .rfgr{grid-template-columns:1fr 1fr!important}
  .diag-grid{grid-template-columns:1fr!important}
  .diag-cbx,.diag-mini-cbx{height:150px!important}

  /* Tables */
  .table-scroll{max-height:380px}
  .data-table{font-size:11px}
  .data-table th,.data-table td{padding:7px 8px}

  /* Tree filter bar */
  .tree-filter-bar{flex-direction:column;align-items:stretch}
  .tree-filter-bar select,.tree-filter-bar input{width:100%}

  /* Edit account page */
  .erp-fields{grid-template-columns:1fr!important}
  .edit-search-bar .form-row{grid-template-columns:1fr!important}

  /* Lang & theme buttons */
  #langbtn{top:66px;left:8px;font-size:10px;padding:4px 8px}
  .theme-btn{top:66px;left:56px;padding:4px 8px;font-size:12px}

  /* Hide demo dbar */
  .dbar{display:none!important}
}

/* Extra small phones */
@media(max-width:390px){
  .mob-nav-item{min-width:44px;padding:4px}
  .mob-nav-lbl{font-size:8px}
  .kpi-grid{gap:6px}
  .mob-sheet-grid{grid-template-columns:repeat(3,1fr)}
}
.row-cc{border-right:3px solid rgba(29,158,117,.5)!important}
</style>

@stack('styles')
</head>
<body>
<div class="app-layout">

  {{-- SIDEBAR --}}
  <aside class="sidebar">
    <div class="sb-header">
      <div class="sb-logo-3d" aria-label="وفرة الخليجية"
           >{!! file_get_contents(public_path("logo_3d.svg")) !!}</div>
      <div>
        <div class="sb-brand">وفرة الخليجية<small>Commission Cards</small></div>
      </div>
    </div>

    <nav class="sb-nav">
      <div class="nav-section">الرئيسية</div>
      <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span>🏠</span> لوحة المتابعة / Dashboard
      </a>
      <a href="{{ route('cards.index') }}" class="nav-item {{ request()->routeIs('cards.*') ? 'active' : '' }}">
        <span>🗂</span> كروت العمولات
        <span class="nav-badge" id="sb-cards-count">—</span>
      </a>
      <a href="{{ route('cards.modified') }}" class="nav-item {{ request()->routeIs('cards.modified') ? 'active' : '' }}">
        <span>✏️</span> الحسابات المعدّلة
        <span class="nav-badge orange" id="sb-mod-count">—</span>
      </a>
      <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.index') ? 'active' : '' }}">
        <span>📈</span> التقارير
      </a>
      <a href="{{ route('reports.dynamic') }}" class="nav-item {{ request()->routeIs('reports.dynamic') ? 'active' : '' }}">
        <span>🔧</span> تقرير ديناميكي
        <span class="nav-badge green">جديد</span>
      </a>
      {{-- Call Center nav --}}
      @php $ccBranchCode = 'CC'; $isCcBranch = auth()->user()?->branch?->code === $ccBranchCode; @endphp
      @if($isCcBranch || auth()->user()?->isFinanceAdmin())
      <a href="{{ route('callcenter.index') }}" class="nav-item {{ request()->routeIs('callcenter.*') ? 'active' : '' }}">
        <span>📞</span> مركز الاتصال
      </a>
      @endif
      @if(!$isCcBranch && auth()->user()?->branch_id)
      <a href="{{ route('callcenter.pending') }}" class="nav-item {{ request()->routeIs('callcenter.pending') ? 'active' : '' }}"
         onclick="loadCcBadge()">
        <span>📬</span> واردة من CC
        <span class="nav-badge orange" id="cc-badge" style="display:none">0</span>
      </a>
      @endif
      <a href="{{ route('cards.tree') }}" class="nav-item {{ request()->routeIs('cards.tree') ? 'active' : '' }}">
        <span>🌳</span> شجرة الحسابات
      </a>

      <div class="nav-section">إنشاء وتعديل</div>
      @if(auth()->user()?->isFinanceAdmin() || auth()->user()?->hasPermission('create_card'))
      <a href="{{ route('cards.create') }}" class="nav-item {{ request()->routeIs('cards.create') ? 'active' : '' }}">
        <span>➕</span> كرت عمولة جديد
        <span class="nav-badge green">جديد</span>
      </a>
      @endif
      @if(auth()->user()?->isFinanceAdmin() || auth()->user()?->hasPermission('edit_card'))
      <a href="{{ route('cards.edit-search') }}" class="nav-item {{ request()->routeIs('cards.edit-search') ? 'active' : '' }}">
        <span>✏️</span> تعديل حساب موجود
      </a>
      @endif

      <div class="nav-section">الإدارة</div>
      @if(auth()->user()?->isFinanceAdmin() || auth()->user()?->hasPermission('employees'))
      <a href="{{ route('employees.index') }}" class="nav-item {{ request()->routeIs('employees.*') ? 'active' : '' }}">
        <span>👥</span> الموظفون
        <span class="nav-badge" id="sb-emp-count">—</span>
      </a>
      @endif

      @if(auth()->user()?->isFinanceAdmin())
      <a href="{{ route('managers.index') }}" class="nav-item {{ request()->routeIs('managers.*') ? 'active' : '' }}">
        <span>👤</span> المديرون
      </a>
      <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <span>📋</span> التعريفات
      </a>
      <a href="{{ route('permissions.index') }}" class="nav-item {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
        <span>🛡️</span> الصلاحيات
        <span class="nav-badge orange" id="sb-pending-count" style="display:none">0</span>
      </a>
      @endif

      <a href="{{ route('guide.index') }}" class="nav-item {{ request()->routeIs('guide.*') ? 'active' : '' }}">
        <span>📖</span> دليل النظام
      </a>
      <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') ? 'active' : '' }}">
        <span>⚙️</span> الإعدادات
      </a>
      @if(auth()->user()?->isFinanceAdmin())
      <a href="{{ route('import.index') }}" class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
        <span>📥</span> استيراد بيانات
      </a>
      @endif
    </nav>

    <div class="sb-footer">
      <div class="user-chip">
        <div class="user-avatar" id="sb-avatar">م</div>
        <div>
          <div class="user-name" id="sb-username">—</div>
          <div class="user-role" id="sb-role">—</div>
        </div>
        <a href="{{ route('auth.logout') }}" class="logout-btn"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit()">⬅</a>
      </div>
      <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" style="display:none">
        @csrf
      </form>
    </div>
  </aside>

  {{-- MAIN CONTENT --}}
  <div class="main-wrap">
    <header class="topbar">
      <div class="tb-left">
        {!! '<div class="tb-logo-3d">' . file_get_contents(public_path("logo_3d.svg")) . '</div>' !!}
        <div>
          <div class="tb-title">@yield('page-title', 'لوحة المتابعة')</div>
          <div class="tb-sub">وفرة الخليجية / <span id="tb-branch">الإدارة المالية</span></div>
        </div>
      </div>
      <div class="tb-right">
        @yield('topbar-actions')
        <a href="{{ route('cards.create') }}" class="tb-btn success">➕ كرت جديد</a>
        <a href="{{ route('cards.edit-search') }}" class="tb-btn" style="color:var(--or);border-color:rgba(245,166,35,.3)">✏️ تعديل</a>
        <button class="theme-toggle" onclick="toggleTheme()" id="theme-btn">🌙</button>
        <button class="tb-btn" onclick="toggleLang()" style="font-size:10px" id="lang-btn">🌐 EN</button>
      </div>
    </header>

    <main class="page-body">
      @if(session('success'))
        <div class="alert alert-success show" style="margin-bottom:16px">
          ✅ {{ session('success') }}
        </div>
      @endif
      @if(session('error'))
        <div class="alert alert-error show" style="margin-bottom:16px">
          ❌ {{ session('error') }}
        </div>
      @endif

      @yield('content')
    </main>
  </div>
</div>

{{-- Toast container --}}
<div class="toast-container" id="toast-container"></div>

{{-- Shared JS --}}
<script>
const API = '{{ url("/api") }}';
// Token: session (server) takes priority over localStorage
let API_TOKEN = '{{ session("api_token","") }}' || localStorage.getItem('wg_token') || '';
// Persist for this session
if (API_TOKEN) localStorage.setItem('wg_token', API_TOKEN);
const CURRENT_USER = {!! json_encode(auth()->user() ? ['id'=>auth()->user()->id,'name'=>auth()->user()->name,'email'=>auth()->user()->email,'role'=>auth()->user()->role,'branch_id'=>auth()->user()->branch_id] : new stdClass()) !!};

// ── Theme ──────────────────────────────────────────────────
let curTheme = localStorage.getItem('wg_theme') || 'dark';
function applyTheme(){
  document.documentElement.setAttribute('data-theme', curTheme === 'light' ? 'light' : '');
  document.getElementById('theme-btn').textContent = curTheme === 'dark' ? '🌙' : '☀️';
}
function toggleTheme(){
  curTheme = curTheme === 'dark' ? 'light' : 'dark';
  localStorage.setItem('wg_theme', curTheme);
  applyTheme();
}
applyTheme();

// ── Lang ───────────────────────────────────────────────────
let curLang = localStorage.getItem('wg_lang') || 'ar';
function toggleLang(){
  curLang = curLang === 'ar' ? 'en' : 'ar';
  localStorage.setItem('wg_lang', curLang);
  document.documentElement.setAttribute('lang', curLang);
  document.documentElement.setAttribute('dir', curLang === 'ar' ? 'rtl' : 'ltr');
  document.getElementById('lang-btn').textContent = curLang === 'ar' ? '🌐 EN' : '🌐 عربي';
}
document.documentElement.setAttribute('lang', curLang);
document.documentElement.setAttribute('dir', curLang === 'ar' ? 'rtl' : 'ltr');

// ── Toast ──────────────────────────────────────────────────
function toast(msg, type = 'success') {
  const c = document.getElementById('toast-container');
  const t = document.createElement('div');
  t.className = `toast toast-${type}`;
  t.textContent = msg;
  c.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

// ── API Helper ─────────────────────────────────────────────
async function api(method, url, body = null, extraHeaders = {}) {
  const res = await fetch(API + url, {
    method,
    headers: {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
      ...(API_TOKEN ? {'Authorization': 'Bearer ' + API_TOKEN} : {}),
      ...extraHeaders,
    },
    body: body ? JSON.stringify(body) : null,
  });
  const data = await res.json();
  if (res.status === 401) {
    window.location.href = '{{ route("auth.login") }}';
  }
  return data;
}

// ── Sidebar user info ──────────────────────────────────────
if (CURRENT_USER && CURRENT_USER.name) {
  document.getElementById('sb-avatar').textContent = CURRENT_USER.name.charAt(0).toUpperCase();
  document.getElementById('sb-username').textContent = CURRENT_USER.name;
  document.getElementById('sb-role').textContent = CURRENT_USER.role === 'finance_admin' ? 'مدير مالي 💼' : 'مدير فرع';
}

// ── Helpers ────────────────────────────────────────────────
const fmt = n => n > 0 ? '$' + Number(n).toLocaleString('en', {maximumFractionDigits:0}) : '—';

/* ── Bilingual Month Picker (Option 3) ──────────────────────
   Usage: mountMonthPicker(containerId, hiddenInputId, onChange?)
   Auto-selects current month on open
   ─────────────────────────────────────────────────────────── */
const MONTHS_BI = [
  {ar:'يناير',en:'January',  short:'Jan'},
  {ar:'فبراير',en:'February', short:'Feb'},
  {ar:'مارس',  en:'March',    short:'Mar'},
  {ar:'أبريل', en:'April',    short:'Apr'},
  {ar:'مايو',  en:'May',      short:'May'},
  {ar:'يونيو', en:'June',     short:'Jun'},
  {ar:'يوليو', en:'July',     short:'Jul'},
  {ar:'أغسطس', en:'August',   short:'Aug'},
  {ar:'سبتمبر',en:'September',short:'Sep'},
  {ar:'أكتوبر',en:'October',  short:'Oct'},
  {ar:'نوفمبر',en:'November', short:'Nov'},
  {ar:'ديسمبر',en:'December', short:'Dec'},
];

function mountMonthPicker(containerId, hiddenId, onChangeCb) {
  const container = document.getElementById(containerId);
  if (!container) return;

  const now   = new Date();
  const state = { year: now.getFullYear(), month: now.getMonth() + 1 };

  // Build selects
  container.innerHTML = `
    <div style="display:flex;gap:8px;align-items:center">
      <select id="${containerId}-month" class="form-control" style="flex:1.4"
              onchange="mpUpdate('${containerId}','${hiddenId}')">
        ${MONTHS_BI.map((m,i) => `<option value="${i+1}" ${i+1===state.month?'selected':''}>
          ${m.ar} — ${m.en}</option>`).join('')}
      </select>
      <select id="${containerId}-year" class="form-control" style="flex:1"
              onchange="mpUpdate('${containerId}','${hiddenId}')">
        ${(()=>{let opts='';for(let y=2065;y>=2010;y--)opts+=`<option value="${y}" ${y===state.year?'selected':''}>${y}</option>`;return opts;})()}
      </select>
    </div>
    <div id="${containerId}-preview" style="font-size:11px;color:var(--pri2);margin-top:5px;
         padding:4px 8px;background:rgba(46,134,171,.06);border-radius:6px;display:flex;
         align-items:center;gap:5px">
      <span style="color:var(--teal)">📅</span>
      <span id="${containerId}-preview-text"></span>
    </div>`;

  mpUpdate(containerId, hiddenId, onChangeCb);
}

function mpUpdate(containerId, hiddenId, onChangeCb) {
  const m   = parseInt(document.getElementById(containerId+'-month').value);
  const y   = parseInt(document.getElementById(containerId+'-year').value);
  const mon = MONTHS_BI[m-1];
  const en  = mon.short + ' ' + y;           // "May 2026" — stored value
  const ar  = mon.ar + ' ' + y;              // "مايو 2026"
  const bi  = mon.ar + ' / ' + mon.en + ' ' + y; // "مايو / May 2026"

  const hidden = document.getElementById(hiddenId);
  if (hidden) hidden.value = en;

  const prev = document.getElementById(containerId+'-preview-text');
  if (prev) prev.textContent = bi;

  if (typeof onChangeCb === 'function') onChangeCb(en, ar, y, m);
}

const fmtK = n => {
  if (n >= 1e6) return '$' + (n/1e6).toFixed(1) + 'M';
  if (n >= 1000) return '$' + (n/1000).toFixed(0) + 'K';
  return '$' + n;
};

// ── Modal helpers ──────────────────────────────────────────
function openModal(id){ document.getElementById(id)?.classList.add('open'); }
function closeModal(id){ document.getElementById(id)?.classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(o => {
  o.addEventListener('click', e => { if (e.target === o) o.classList.remove('open'); });
});
</script>


<script>
// ══════════════════════════════════════════════════════════
// MOBILE NAVIGATION SYSTEM
// ══════════════════════════════════════════════════════════
const MOB_BREAKPOINT = 768;
const isMob = () => window.innerWidth <= MOB_BREAKPOINT;

const MOB_ROUTES = {
  'd':           '{{ route("dashboard") }}',
  'c':           '{{ route("cards.index") }}',
  'm':           '{{ route("cards.modified") }}',
  'r':           '{{ route("reports.index") }}',
  'dynamic':     '{{ route("reports.dynamic") }}',
  'cc':          '{{ route("callcenter.index") }}',
  'ccpending':   '{{ route("callcenter.pending") }}',
  'e':           '{{ route("employees.index") }}',
  'settings':    '{{ route("settings.index") }}',
  'import':      '{{ route("import.index") }}',
  'managers':    '{{ route("managers.index") }}',
  'permissions': '{{ route("permissions.index") }}',
  'tree':        '{{ route("cards.tree") }}',
  'guide':       '{{ route("guide.index") }}',
};

const MOB_TITLES = {
  'd':'لوحة المتابعة','c':'كروت العمولات','m':'الحسابات المعدّلة',
  'r':'التقارير','dynamic':'تقرير ديناميكي','e':'الموظفون',
  'settings':'الإعدادات','import':'استيراد بيانات','managers':'المديرون',
  'permissions':'الصلاحيات','tree':'شجرة الحسابات',
};

function toggleMobSidebar() {
  const sb = document.querySelector('.sidebar');
  const ov = document.getElementById('mob-overlay');
  if (!sb) return;
  const open = sb.classList.contains('mob-open');
  if (open) { closeMobSidebar(); }
  else {
    sb.classList.add('mob-open');
    ov?.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
}
function closeMobSidebar() {
  document.querySelector('.sidebar')?.classList.remove('mob-open');
  document.getElementById('mob-overlay')?.classList.remove('open');
  document.body.style.overflow = '';
}
function toggleMobMore() {
  const sheet = document.getElementById('mob-more-sheet');
  const ov    = document.getElementById('mob-sheet-overlay');
  if (sheet?.classList.contains('open')) { closeMobMore(); }
  else {
    sheet?.classList.add('open');
    if (ov) { ov.style.display = 'block'; }
    document.body.style.overflow = 'hidden';
  }
}
function closeMobMore() {
  document.getElementById('mob-more-sheet')?.classList.remove('open');
  const ov = document.getElementById('mob-sheet-overlay');
  if (ov) ov.style.display = 'none';
  document.body.style.overflow = '';
}
function mobGo(page, btnId) {
  closeMobSidebar();
  closeMobMore();
  const url = MOB_ROUTES[page];
  if (url) { window.location.href = url; return; }
}

// Swipe to open sidebar (RTL: swipe left from right edge)
let touchStartX = 0;
document.addEventListener('touchstart', e => { touchStartX = e.touches[0].clientX; }, {passive:true});
// Prevent pull-to-refresh on scrollable content areas
document.querySelectorAll('.table-scroll,.modal-body,.mob-more-sheet').forEach(el => {
  el.addEventListener('touchmove', e => { e.stopPropagation(); }, {passive:true});
});
document.addEventListener('touchend', e => {
  if (!isMob()) return;
  const dx = e.changedTouches[0].clientX - touchStartX;
  const startedAtEdge = touchStartX > window.innerWidth - 50;
  if (dx < -60 && startedAtEdge) toggleMobSidebar();
  if (dx >  60 && document.querySelector('.sidebar.mob-open')) closeMobSidebar();
}, {passive:true});

// Sync theme button
const _origToggleTheme = window.toggleTheme;
window.toggleTheme = function() {
  if (_origToggleTheme) _origToggleTheme();
  const btn = document.getElementById('mob-theme-btn');
  if (btn) btn.textContent = document.documentElement.getAttribute('data-theme')==='light' ? '☀️' : '🌙';
};

// Set active bottom nav item based on current URL
function setActiveMobNav() {
  const path = window.location.pathname;
  const map = {
    '/dashboard':     'mbn-d',
    '/cards':         'mbn-c',
    '/reports':       'mbn-r',
    '/employees':     'mbn-e',
  };
  document.querySelectorAll('.mob-nav-item').forEach(b => b.classList.remove('on'));
  for (const [route, id] of Object.entries(map)) {
    if (path.startsWith(route) || path === route) {
      document.getElementById(id)?.classList.add('on');
      break;
    }
  }
}

// Update mobile page title
function updateMobTitle() {
  const path = window.location.pathname;
  const titleMap = {
    '/dashboard':         'لوحة المتابعة',
    '/cards':             'كروت العمولات',
    '/cards/create':      'كرت جديد',
    '/cards/modified':    'الحسابات المعدّلة',
    '/cards/edit':        'تعديل حساب',
    '/cards/tree':        'شجرة الحسابات',
    '/reports':           'التقارير',
    '/reports/dynamic':   'تقرير ديناميكي',
    '/employees':         'الموظفون',
    '/settings':          'الإعدادات',
    '/import':            'استيراد بيانات',
    '/managers':          'المديرون',
    '/permissions':       'الصلاحيات',
  };
  for (const [route, title] of Object.entries(titleMap)) {
    if (path.startsWith(route)) {
      const el = document.getElementById('mob-pg-title');
      if (el) el.textContent = title;
      break;
    }
  }
}

// Init
async function loadCcBadge() {
  try {
    const r = await api('GET', '/cc/notifications');
    const count = r?.unread_count || 0;
    // Update all CC badges
    ['cc-badge','cc-badge-mob'].forEach(id => {
      const b = document.getElementById(id);
      if (!b) return;
      if (count > 0) { b.textContent = count; b.style.display = 'inline-flex'; }
      else           { b.style.display = 'none'; }
    });
    // Store notifications for dropdown
    if (r?.data) window._ccNotifs = r.data;
  } catch(e) {}
}

async function markNotifRead(id, cardId) {
  try {
    await api('PUT', `/cc/notifications/${id}/read`);
    loadCcBadge(); // refresh count
  } catch(e) {}
  // Navigate to pending page
  const isCcBranch = {{ auth()->user()?->branch?->code === 'CC' ? 'true' : 'false' }};
  const url = isCcBranch
    ? '{{ route("callcenter.index") }}'
    : '{{ route("callcenter.pending") }}';
  window.location.href = url;
}

function initMobile() {
  if (!isMob()) return;
  setActiveMobNav();
  updateMobTitle();
  // Sync theme button
  const btn = document.getElementById('mob-theme-btn');
  if (btn) btn.textContent = document.documentElement.getAttribute('data-theme')==='light' ? '☀️' : '🌙';
  // Add top padding for fixed mob-header
  const mw = document.querySelector('.main-wrap');
  if (mw) mw.style.paddingTop = '56px';
}

window.addEventListener('resize', () => {
  if (!isMob()) { closeMobSidebar(); closeMobMore(); }
});
document.addEventListener('DOMContentLoaded', () => {
  initMobile();
  // Load CC notification badge on every page
  @if(auth()->user()?->branch_id && auth()->user()?->branch?->code !== 'CC')
  loadCcBadge();
  setInterval(loadCcBadge, 60000);
  @endif
});
setTimeout(initMobile, 50);
</script>

@stack('scripts')

{{-- ████ MOBILE UI ████ --}}
<div class="mob-header" id="mob-header">
  <button class="mob-burger" onclick="toggleMobSidebar()">☰</button>
  <div class="mob-logo-row">
    {!! '<div class="mob-logo-3d">' . file_get_contents(public_path("logo_3d.svg")) . '</div>' !!}
    <div class="mob-title">وفرة الخليجية<small id="mob-pg-title">لوحة المتابعة</small></div>
  </div>
  <button class="mob-theme-btn" onclick="toggleTheme()" id="mob-theme-btn">🌙</button>
</div>

<div class="mob-overlay" id="mob-overlay" onclick="closeMobSidebar()"></div>

<div class="mob-bottom-nav" id="mob-bottom-nav">
  <div class="mob-nav-items">
    <button class="mob-nav-item on" id="mbn-d" onclick="mobGo('d','mbn-d')">
      <span class="mob-nav-icon">🏠</span>
      <span class="mob-nav-lbl">الرئيسية</span>
    </button>
    <button class="mob-nav-item" id="mbn-c" onclick="mobGo('c','mbn-c')">
      <span class="mob-nav-icon">🗂</span>
      <span class="mob-nav-lbl">الكروت</span>
      <span class="mob-nav-dot" id="mbn-dot-c"></span>
    </button>
    <button class="mob-nav-item" id="mbn-r" onclick="mobGo('r','mbn-r')">
      <span class="mob-nav-icon">📈</span>
      <span class="mob-nav-lbl">التقارير / Reports</span>
    </button>
    <button class="mob-nav-item" id="mbn-e" onclick="mobGo('e','mbn-e')">
      <span class="mob-nav-icon">👥</span>
      <span class="mob-nav-lbl">الموظفون / Employees</span>
    </button>
    <button class="mob-nav-item" id="mbn-more" onclick="toggleMobMore()">
      <span class="mob-nav-icon">⋯</span>
      <span class="mob-nav-lbl">المزيد</span>
      <span class="mob-nav-dot" id="mbn-dot-more"></span>
    </button>
  </div>
</div>

<div class="mob-more-sheet" id="mob-more-sheet">
  <div class="mob-sheet-handle"></div>
  <div class="mob-sheet-grid">
    <div class="mob-sheet-item" onclick="mobGo('m');closeMobMore()">
      <div class="mob-sheet-ico">✏️</div><div class="mob-sheet-lbl">المعدّلة</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('tree');closeMobMore()">
      <div class="mob-sheet-ico">🌳</div><div class="mob-sheet-lbl">شجرة الحسابات / Tree</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('dynamic');closeMobMore()">
      <div class="mob-sheet-ico">🔧</div><div class="mob-sheet-lbl">تقرير ديناميكي / Dynamic</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('cc');closeMobMore()">
      <div class="mob-sheet-ico">📞</div><div class="mob-sheet-lbl">مركز الاتصال / Call Center</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('settings');closeMobMore()">
      <div class="mob-sheet-ico">⚙️</div><div class="mob-sheet-lbl">الإعدادات / Settings</div>
    </div>
    @if(auth()->user()?->isFinanceAdmin())
    <div class="mob-sheet-item" onclick="mobGo('import');closeMobMore()">
      <div class="mob-sheet-ico">📥</div><div class="mob-sheet-lbl">استيراد</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('managers');closeMobMore()">
      <div class="mob-sheet-ico">👤</div><div class="mob-sheet-lbl">المديرون / Managers</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('permissions');closeMobMore()">
      <div class="mob-sheet-ico">🛡️</div><div class="mob-sheet-lbl">الصلاحيات / Permissions</div>
    </div>
    @endif
    <div class="mob-sheet-item" onclick="mobGo('guide');closeMobMore()">
      <div class="mob-sheet-ico">📖</div><div class="mob-sheet-lbl">دليل النظام / Guide</div>
    </div>
    <div class="mob-sheet-item" onclick="document.getElementById('logout-form').submit()">
      <div class="mob-sheet-ico">🚪</div><div class="mob-sheet-lbl">خروج</div>
    </div>
  </div>
</div>
<div class="mob-overlay" id="mob-sheet-overlay" onclick="closeMobMore()" style="display:none;z-index:1598"></div>

</body>
</html>
