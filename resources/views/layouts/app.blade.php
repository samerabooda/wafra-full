<!DOCTYPE html>
<html lang="ar" dir="rtl" id="html-root">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="theme-color" content="#0A1628">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="وفرة الخليجية">
<meta name="mobile-web-app-capable" content="yes">
<title>{{ config('app.name', 'وفرة الخليجية') }} — @yield('title','لوحة المتابعة')</title>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&family=JetBrains+Mono:wght@400;600;700&display=swap" rel="stylesheet">
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<!-- SheetJS (Excel import/export) -->
<script src="https://cdn.sheetjs.com/xlsx-0.20.3/package/dist/xlsx.full.min.js"></script>
<!-- jsPDF -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.8.2/jspdf.plugin.autotable.min.js"></script>

<style>
/* ═══ CSS VARIABLES ═══ */
:root {
  --pri:#1AADBA;--pri2:#22C4D4;--pri3:#0E7A88;
  --bg:#0C1520;--bg1:#111D2B;--bg2:#162437;--bg3:#1B2D42;--bg4:#20344C;
  --brd1:#1E3650;--brd2:#2C4E70;
  --tx:#E6EFF6;--mu:#5A80A0;--m2:#7AABCA;
  --gr:#1ECC80;--re:#E84545;--or:#F5A828;--pu:#8A78F0;
  --card-bg:var(--bg3);--card-brd:var(--brd1);
  --inp-bg:var(--bg4);--inp-brd:var(--brd1);
  --sb-bg:var(--bg1);--topb-bg:rgba(12,21,32,.95);
  --shadow:0 8px 32px rgba(0,0,0,.38);
}
[data-theme="light"] {
  --bg:#EDF2F7;--bg1:#FFFFFF;--bg2:#E2EBF4;--bg3:#FFFFFF;--bg4:#EDF2F7;
  --brd1:#B0CCDF;--brd2:#5A9ABF;
  --tx:#0A1929;--mu:#3A5A78;--m2:#1A3A58;
  --card-bg:#FFFFFF;--card-brd:#B0CCDF;
  --sb-bg:#0C2240;--topb-bg:rgba(12,34,64,.98);
  --inp-bg:#FFFFFF;--inp-brd:#5A9ABF;
  --shadow:0 4px 24px rgba(26,173,186,.15);
  --gr:#158838;--or:#A04800;--re:#B80020;
}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent}
html{font-size:16px}
body{font-family:'Tajawal',sans-serif;background:var(--bg);color:var(--tx);min-height:100vh;overflow-x:hidden;transition:background .3s,color .3s}
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
.sb-header{
  padding:18px 16px 16px;
  border-bottom:1px solid rgba(255,255,255,.1);
  display:flex;flex-direction:column;align-items:center;gap:12px;
  background:linear-gradient(180deg,rgba(26,173,186,.12) 0%,transparent 100%);
}
.sb-logo{
  width:82px;height:82px;border-radius:18px;
  background:white;padding:4px;object-fit:contain;
  box-shadow:0 6px 24px rgba(26,173,186,.55),0 3px 10px rgba(0,0,0,.4);
  transition:transform .2s,box-shadow .2s;display:block;
}
.sb-logo:hover{transform:scale(1.05);box-shadow:0 8px 32px rgba(26,173,186,.7),0 4px 14px rgba(0,0,0,.45)}
.sb-brand{font-size:13px;font-weight:800;color:white;line-height:1.3;text-align:center}
.sb-brand small{display:block;font-size:10px;color:rgba(255,255,255,.55);font-weight:400}
.sb-nav{flex:1;padding:10px 8px}
.nav-section{font-size:9px;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.6px;padding:8px 10px 4px;margin-top:4px}
.nav-item{display:flex;align-items:center;gap:9px;padding:8px 10px;border-radius:8px;
  font-size:12px;font-weight:500;color:rgba(255,255,255,.6);cursor:pointer;
  text-decoration:none;position:relative;margin-bottom:1px}
.nav-item:hover{
  background:rgba(255,255,255,.12);
  color:white;
  backdrop-filter:blur(12px);
  -webkit-backdrop-filter:blur(12px);
  box-shadow:0 2px 12px rgba(26,173,186,.18),inset 0 1px 0 rgba(255,255,255,.15),inset 0 -1px 0 rgba(0,0,0,.08);
  border:1px solid rgba(255,255,255,.15);
  transform:translateX(-2px);
  transition:all .18s ease;
}
.nav-item{transition:all .18s ease;border:1px solid transparent}
.nav-item.active{background:rgba(46,134,171,.25);color:var(--pri2);font-weight:700}
.nav-item.active::before{content:'';position:absolute;right:0;top:22%;bottom:22%;
  width:3px;background:var(--pri);border-radius:2px 0 0 2px}
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
.tb-logo{width:48px;height:48px;border-radius:12px;background:white;padding:3px;object-fit:contain;
  box-shadow:0 3px 14px rgba(26,173,186,.35),0 1px 5px rgba(0,0,0,.25)}
.tb-title{font-size:14px;font-weight:800;color:rgba(255,255,255,.95)}
.tb-sub{font-size:11px;color:rgba(255,255,255,.5)}
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
/* ── CC card rows — purple tint ── */
.data-table tr.row-cc-card td{background:linear-gradient(90deg,rgba(123,104,238,.07),transparent 80%)!important}
.data-table tr.row-cc-card td:first-child{border-right:3px solid #7b68ee}
.data-table tr.row-cc-card:hover td{background:linear-gradient(90deg,rgba(123,104,238,.13),rgba(123,104,238,.04) 80%)!important}
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
::-webkit-scrollbar{width:4px;height:4px}
::-webkit-scrollbar-thumb{background:var(--brd2);border-radius:3px}

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
.mob-logo{width:40px;height:40px;object-fit:contain;border-radius:10px;background:white;padding:2px;flex-shrink:0;
  box-shadow:0 2px 12px rgba(26,173,186,.4),0 1px 4px rgba(0,0,0,.3)}
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
  .topbar{top:66px!important;padding:0 10px!important;height:46px!important;margin:0!important;border-radius:0!important}
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

  /* Forms */
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

  /* Theme button mobile */
  .theme-btn{top:66px;left:8px;padding:4px 8px;font-size:12px}

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

/* ══════════════════════════════════════════
   FONT SIZE & READABILITY UPGRADES
   ══════════════════════════════════════════ */
html{font-size:16px}
/* Sidebar */
.nav-item{font-size:14px!important;padding:10px 12px!important}
.nav-section{font-size:11px!important;padding:10px 12px 5px!important}
.nav-badge{font-size:11px!important;padding:2px 9px!important}
.sb-logo{width:105px!important;height:105px!important}
.sb-brand{font-size:14px!important;font-weight:800!important;color:white!important}
.sb-brand small{font-size:11px!important;color:rgba(255,255,255,.6)!important}
.user-name{font-size:14px!important;font-weight:700!important;color:white!important}
.user-role{font-size:11px!important;color:rgba(255,255,255,.6)!important}
.logout-btn{font-size:14px!important;color:rgba(255,255,255,.5)!important}
/* Topbar — always light text on dark topbar */
.tb-title{font-size:17px!important;font-weight:800!important;color:rgba(255,255,255,.95)!important}
.tb-sub{font-size:13px!important;color:rgba(255,255,255,.5)!important}
.tb-logo{width:48px!important;height:48px!important}
/* Header 1cm from top */
.main-wrap{padding-top:10px!important}
.topbar{top:10px!important;margin:0 10px;border-radius:14px!important}
.tb-btn{font-size:13px!important;padding:8px 14px!important}
/* KPI */
.kpi-label{font-size:13px!important;text-transform:none!important;letter-spacing:0!important;font-weight:700!important;margin-bottom:8px!important}
.kpi-value{font-size:1.8rem!important;font-weight:900!important}
.kpi-sub{font-size:12px!important}
.kpi-card{padding:18px 20px!important;border-radius:16px!important}
/* Panel */
.panel-title{font-size:16px!important;font-weight:800!important}
.panel-header{padding:16px 20px!important}
.panel-body{padding:20px!important}
/* Table */
.data-table th{font-size:13px!important;text-transform:none!important;padding:12px 16px!important;font-weight:800!important}
.data-table td{font-size:14px!important;padding:12px 16px!important}
/* Forms */
.form-label{font-size:13px!important;text-transform:none!important;letter-spacing:0!important;color:var(--m2)!important;font-weight:700!important;margin-bottom:8px!important}
.form-control{font-size:15px!important;padding:12px 15px!important}
.form-section-title{font-size:15px!important;font-weight:800!important;margin-bottom:14px!important}
.form-group{margin-bottom:18px!important}
/* Buttons */
.btn{font-size:14px!important;padding:11px 22px!important;border-radius:10px!important}
.btn-sm{font-size:13px!important;padding:8px 16px!important;border-radius:8px!important}
.btn-xl{font-size:16px!important;padding:14px 34px!important;border-radius:12px!important}
/* Badges */
.badge{font-size:12px!important;padding:4px 11px!important;border-radius:14px!important}
/* Modal */
.modal-title{font-size:17px!important;font-weight:800!important}
.modal-body{padding:20px 22px!important}
.modal-footer{padding:14px 22px!important}
/* Toast */
.toast{font-size:14px!important;padding:12px 26px!important}
/* AC number */
.ac-num{font-size:13px!important;padding:4px 10px!important}
/* Alerts */
.alert{font-size:14px!important;padding:14px 18px!important}
/* Page body */
.page-body{padding:24px!important}
/* Panel spacing */
.panel{border-radius:16px!important;margin-bottom:20px!important}

/* ══════════════════════════════════════════
   LTR / ENGLISH DIRECTION OVERRIDES
   ══════════════════════════════════════════ */
[dir="ltr"] .sidebar{border-left:1px solid var(--brd1);border-right:none}
[dir="ltr"] .nav-item.active::before{right:auto;left:0;border-radius:0 2px 2px 0}
[dir="ltr"] .nav-badge{margin-right:0;margin-left:auto}
[dir="ltr"] .user-chip .logout-btn{margin-right:0;margin-left:auto}
[dir="ltr"] .sb-footer .user-chip{flex-direction:row}
[dir="ltr"] .tb-left{flex-direction:row}
[dir="ltr"] .tb-right{flex-direction:row}
[dir="ltr"] .data-table th,[dir="ltr"] .data-table td{text-align:left}
[dir="ltr"] .form-label{text-align:left}
[dir="ltr"] .kpi-label,[dir="ltr"] .kpi-sub{text-align:left}
[dir="ltr"] .panel-title{text-align:left}
[dir="ltr"] .mob-nav-items{flex-direction:row}
[dir="ltr"] .mob-title{text-align:left}
[dir="ltr"] .topbar,[dir="ltr"] .mob-header{direction:ltr}
[dir="ltr"] .form-control,[dir="ltr"] input,[dir="ltr"] select,[dir="ltr"] textarea{text-align:left;direction:ltr}
[dir="ltr"] .alert,[dir="ltr"] .toast{text-align:left}
[dir="ltr"] .btn-group{flex-direction:row}
[dir="ltr"] .sb-header{flex-direction:row}
[dir="ltr"] .user-name,[dir="ltr"] .user-role{text-align:left}

/* Language toggle button */
.lang-toggle-btn{
  display:flex;align-items:center;gap:5px;
  background:rgba(26,173,186,.1);
  border:1px solid rgba(26,173,186,.3);
  border-radius:8px;padding:5px 11px;
  font-size:12px;font-weight:700;color:var(--pri2);
  cursor:pointer;font-family:'Tajawal',sans-serif;
  transition:all .2s;white-space:nowrap;
}
.lang-toggle-btn:hover{background:rgba(26,173,186,.22);border-color:var(--pri2)}
.lang-toggle-btn .lang-flag{font-size:15px}
</style>

@stack('styles')
</head>
<body>

{{-- ── Page-load animated splash (globe spins 2 s then fades) ── --}}
@include('partials.splash')

<div class="app-layout">

  {{-- SIDEBAR --}}
  <aside class="sidebar">
    <div class="sb-header">
      <img src="{{ asset('logo.png') }}" class="sb-logo" alt="وفرة الخليجية للخدمات المالية"
           onclick="logoRefresh()" style="cursor:pointer" title="تحديث الصفحة 🔄">
      <div class="sb-brand">
        <span data-i18n="company.short">وفرة الخليجية</span>
        <small data-i18n="company.tagline">للخدمات المالية</small>
      </div>
    </div>

    <nav class="sb-nav">
      <div class="nav-section" data-i18n="nav.sec.main">الرئيسية</div>
      <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
        <span>🏠</span> <span data-i18n="nav.dashboard">لوحة المتابعة</span>
      </a>
      <div class="nav-section" data-i18n="nav.sec.cards">🗂 كروت العمولة</div>
      <a href="{{ route('cards.index') }}" class="nav-item {{ request()->routeIs('cards.*') ? 'active' : '' }}">
        <span>🗂</span> <span data-i18n="nav.cards">كروت العمولة</span>
        <span class="nav-badge" id="sb-cards-count">—</span>
      </a>

      <div class="nav-section" data-i18n="nav.sec.cc">📞 مركز الاتصال</div>
      @if(auth()->user()?->isBranchManager())
      {{-- Branch managers go straight to the incoming-cards view --}}
      <a href="{{ route('callcenter.pending') }}" class="nav-item {{ request()->routeIs('callcenter.pending') ? 'active' : '' }}">
        <span>📩</span> <span data-i18n="nav.cc.pending">كروت CC الواردة</span>
        <span class="nav-badge orange" id="sb-cc-pending" style="display:none">0</span>
      </a>
      @else
      {{-- CC staff / FA see the full CC hub --}}
      <a href="{{ route('callcenter.index') }}" class="nav-item {{ request()->routeIs('callcenter.*') ? 'active' : '' }}">
        <span>📞</span> <span data-i18n="nav.cc">مركز الاتصال</span>
        <span class="nav-badge orange" id="sb-cc-pending" style="display:none">0</span>
      </a>
      @endif

      <div class="nav-section" data-i18n="nav.sec.reports">📈 التقارير</div>
      <a href="{{ route('reports.index') }}" class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
        <span>📈</span> <span data-i18n="nav.reports">التقارير</span>
      </a>

      <div class="nav-section" data-i18n="nav.sec.admin">الإدارة</div>
      <a href="{{ route('settings.index') }}" class="nav-item {{ request()->routeIs('settings.*') || request()->routeIs('employees.*') || request()->routeIs('managers.*') || request()->routeIs('branches.*') || request()->routeIs('permissions.*') ? 'active' : '' }}">
        <span>⚙️</span> <span data-i18n="nav.settings">الإعدادات</span>
        <span class="nav-badge orange" id="sb-pending-count" style="display:none">0</span>
      </a>
      @if(auth()->user()?->isFinanceAdmin())
      <a href="{{ route('import.index') }}" class="nav-item {{ request()->routeIs('import.*') ? 'active' : '' }}">
        <span>📥</span> <span data-i18n="nav.import">استيراد بيانات</span>
      </a>
      @endif

      <a href="{{ route('settings.index') }}?s=guide" class="nav-item {{ request()->routeIs('settings.*') && request()->get('s') === 'guide' ? 'active' : '' }}"
         style="color:rgba(255,255,255,.55)">
        <span>📖</span>
        <span data-i18n="nav.guide">دليل التشغيل</span>
      </a>
    </nav>

    <div class="sb-footer">
      <div class="user-chip">
        <div class="user-avatar" id="sb-avatar">م</div>
        <div style="flex:1;min-width:0">
          <div class="user-name" id="sb-username">—</div>
          <div class="user-role" id="sb-role">—</div>
        </div>
      </div>
      {{-- Prominent logout button --}}
      <a href="{{ route('auth.logout') }}"
         onclick="event.preventDefault(); doLogout()"
         style="
           display:flex;align-items:center;justify-content:center;gap:7px;
           margin-top:8px;padding:9px 14px;border-radius:9px;
           background:rgba(232,69,69,.1);border:1px solid rgba(232,69,69,.25);
           color:rgba(232,69,69,.8);font-size:13px;font-weight:700;
           cursor:pointer;text-decoration:none;width:100%;
           transition:all .18s;
         "
         onmouseover="this.style.background='rgba(232,69,69,.18)';this.style.color='#e84545'"
         onmouseout="this.style.background='rgba(232,69,69,.1)';this.style.color='rgba(232,69,69,.8)'"
         id="sidebar-logout-btn">
        <span style="font-size:16px">🚪</span>
        <span data-i18n="nav.logout">تسجيل الخروج</span>
      </a>
      <form id="logout-form" action="{{ route('auth.logout') }}" method="POST" style="display:none">
        @csrf
      </form>
    </div>
  </aside>

  {{-- MAIN CONTENT --}}
  <div class="main-wrap">
    <header class="topbar">
      <div class="tb-left">
        <img src="{{ asset('logo.png') }}" class="tb-logo" alt="وفرة الخليجية للخدمات المالية"
             onclick="logoRefresh()" style="cursor:pointer" title="تحديث الصفحة 🔄">
        <div>
          <div class="tb-title">@yield('page-title', 'لوحة المتابعة')</div>
          <div class="tb-sub"><span data-i18n="company.short">وفرة الخليجية</span> / <span id="tb-branch" data-i18n="tb.dept">الإدارة المالية</span></div>
        </div>
      </div>
      <div class="tb-right">
        @yield('topbar-actions')
        <a href="{{ route('cards.create') }}" class="tb-btn success">➕ <span data-i18n="btn.newcard">كرت جديد</span></a>
        <a href="{{ route('cards.edit-search') }}" class="tb-btn" style="color:var(--or);border-color:rgba(245,166,35,.3)">✏️ <span data-i18n="btn.edit">تعديل</span></a>
        <button class="lang-toggle-btn" onclick="toggleLang()" id="lang-btn" title="Switch Language / تغيير اللغة">
          <span class="lang-flag" id="lang-flag">🇬🇧</span><span id="lang-label">EN</span>
        </button>
        <button class="theme-toggle" onclick="toggleTheme()" id="theme-btn">🌙</button>
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
let API_TOKEN = localStorage.getItem('wg_token') || '{{ session("api_token","") }}';

/* ══ SECURE LOGOUT — clears auth token from client storage BEFORE server POST ══
   Prevents another person picking up the device and auto-logging in.
   Language & theme preferences are kept (non-sensitive).
   ══════════════════════════════════════════════════════════════════════════════ */
function doLogout() {
  try {
    localStorage.removeItem('wg_token');   /* wipe Sanctum bearer token */
    API_TOKEN = '';
    sessionStorage.clear();                /* wipe splash flag + any temp state */
  } catch(e) {}
  document.getElementById('logout-form').submit();
}
@php $__cu = auth()->user() ? auth()->user()->only('id','name','email','role','branch_id') : []; @endphp
const CURRENT_USER = @json($__cu);

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

// ══════════════════════════════════════════════════════════
// BILINGUAL i18n SYSTEM  (AR ↔ EN)
// ══════════════════════════════════════════════════════════
const I18N = {
  ar: {
    'company.short':   'وفرة الخليجية',
    'company.tagline': 'للخدمات المالية',
    'company.full':    'وفرة الخليجية للخدمات المالية',
    'nav.sec.main':    'الرئيسية',
    'nav.sec.create':  'إنشاء وتعديل',
    'nav.sec.cards':   '🗂 كروت العمولة',
    'nav.sec.cc':      '📞 مركز الاتصال',
    'nav.sec.reports': '📈 التقارير',
    'nav.sec.admin':   'الإدارة',
    'nav.dashboard':   'لوحة المتابعة',
    'nav.cards':       'كروت العمولات',
    'nav.modified':    'الحسابات المعدّلة',
    'nav.reports':     'التقارير',
    'nav.dynamic':     'تقرير ديناميكي',
    'nav.tree':        'شجرة الحسابات',
    'nav.create':      'كرت عمولة جديد',
    'nav.edit':        'تعديل حساب موجود',
    'nav.cc':          'مركز الاتصال',
    'nav.cc.pending':  'كروت CC الواردة',
    'nav.cc.accounts': 'حسابات CC',
    'nav.cc.reports':  'تقارير CC',
    'nav.cc.modified': 'الحسابات المعدّلة',
    'nav.cc.monthly':  'إحصائيات شهرية',
    'nav.sec.reports': '📈 التقارير',
    'nav.reports.main':'التقارير',
    'nav.employees':   'الموظفون',
    'nav.managers':    'المديرون',
    'nav.branches':    'الفروع',
    'nav.permissions': 'الصلاحيات',
    'nav.settings':    'الإعدادات',
    'nav.import':      'استيراد بيانات',
    'nav.guide':       'دليل التشغيل',
    'nav.logout':      'تسجيل الخروج',
    'btn.newcard':     'كرت جديد',
    'btn.edit':        'تعديل',
    'tb.dept':         'الإدارة المالية',
    'mob.home':        'الرئيسية',
    'mob.cards':       'الكروت',
    'mob.reports':     'التقارير',
    'mob.employees':   'الموظفون',
    'mob.more':        'المزيد',
    'mob.modified':    'المعدّلة',
    'mob.tree':        'شجرة الحسابات',
    'mob.dynamic':     'تقرير ديناميكي',
    'mob.import':      'استيراد',
    'mob.logout':      'خروج',
    'badge.new':       'جديد',
    'role.admin':      'مدير مالي 💼',
    'role.branch':     'مدير فرع',
    'page.dashboard':  'لوحة المتابعة',
    'page.cards':      'كروت العمولات',
    'page.cards.new':  'كرت جديد',
    'page.cards.mod':  'الحسابات المعدّلة',
    'page.cards.edit': 'تعديل حساب',
    'page.cards.tree': 'شجرة الحسابات',
    'page.reports':    'التقارير',
    'page.dynamic':    'تقرير ديناميكي',
    'page.employees':  'الموظفون',
    'page.settings':   'الإعدادات',
    'page.import':     'استيراد بيانات',
    'page.cc':         'مركز الاتصال',
    'page.cc.pending': 'كروت CC الواردة',
  },
  en: {
    'company.short':   'Wafra Gulf',
    'company.tagline': 'Financial Services',
    'company.full':    'Wafra Gulf Financial Services',
    'nav.sec.main':    'Main',
    'nav.sec.create':  'Create & Edit',
    'nav.sec.cards':   '🗂 Commission Cards',
    'nav.sec.cc':      '📞 Call Center',
    'nav.sec.reports': '📈 Reports',
    'nav.sec.admin':   'Administration',
    'nav.dashboard':   'Dashboard',
    'nav.cards':       'Commission Cards',
    'nav.modified':    'Modified Accounts',
    'nav.reports':     'Reports',
    'nav.dynamic':     'Dynamic Report',
    'nav.tree':        'Account Tree',
    'nav.create':      'New Commission Card',
    'nav.edit':        'Edit Existing Account',
    'nav.cc':          'Call Center',
    'nav.cc.pending':  'Incoming CC Cards',
    'nav.cc.accounts': 'CC Accounts',
    'nav.cc.reports':  'CC Reports',
    'nav.cc.modified': 'Modified Accounts',
    'nav.cc.monthly':  'Monthly Stats',
    'nav.sec.reports': '📈 Reports',
    'nav.reports.main':'Reports',
    'nav.employees':   'Employees',
    'nav.managers':    'Managers',
    'nav.branches':    'Branches',
    'nav.permissions': 'Permissions',
    'nav.settings':    'Settings',
    'nav.import':      'Import Data',
    'nav.guide':       'User Guide',
    'nav.logout':      'Sign Out',
    'btn.newcard':     'New Card',
    'btn.edit':        'Edit',
    'tb.dept':         'Finance Department',
    'mob.home':        'Home',
    'mob.cards':       'Cards',
    'mob.reports':     'Reports',
    'mob.employees':   'Staff',
    'mob.more':        'More',
    'mob.modified':    'Modified',
    'mob.tree':        'Account Tree',
    'mob.dynamic':     'Dynamic Report',
    'mob.import':      'Import',
    'mob.logout':      'Logout',
    'badge.new':       'New',
    'role.admin':      'Finance Admin 💼',
    'role.branch':     'Branch Manager',
    'page.dashboard':  'Dashboard',
    'page.cards':      'Commission Cards',
    'page.cards.new':  'New Card',
    'page.cards.mod':  'Modified Accounts',
    'page.cards.edit': 'Edit Account',
    'page.cards.tree': 'Account Tree',
    'page.reports':    'Reports',
    'page.dynamic':    'Dynamic Report',
    'page.employees':  'Employees',
    'page.settings':   'Settings',
    'page.import':     'Import Data',
    'page.cc':         'Call Center',
    'page.cc.pending': 'Incoming CC Cards',
  }
};

let curLang = localStorage.getItem('wg_lang') || 'ar';

function applyLang(lang) {
  const isEn  = lang === 'en';
  const dir   = isEn ? 'ltr' : 'rtl';
  const html  = document.documentElement;
  html.setAttribute('lang', lang);
  html.setAttribute('dir',  dir);

  /* Translate all data-i18n elements */
  document.querySelectorAll('[data-i18n]').forEach(el => {
    const key = el.getAttribute('data-i18n');
    const t   = I18N[lang]?.[key];
    if (t !== undefined) el.textContent = t;
  });

  /* Update lang buttons (desktop + mobile) */
  const flag  = isEn ? '🇸🇦' : '🇬🇧';
  const label = isEn ? 'عر'  : 'EN';
  ['lang-flag','lang-flag-mob'].forEach(id => {
    const el = document.getElementById(id); if (el) el.textContent = flag;
  });
  ['lang-label','lang-label-mob'].forEach(id => {
    const el = document.getElementById(id); if (el) el.textContent = label;
  });

  /* Update role text */
  const roleEl = document.getElementById('sb-role');
  if (roleEl && CURRENT_USER?.role) {
    const key = CURRENT_USER.role === 'finance_admin' ? 'role.admin' : 'role.branch';
    roleEl.textContent = I18N[lang]?.[key] || roleEl.textContent;
  }

  /* Logout arrow direction */
  const logoutBtn = document.querySelector('.logout-btn');
  if (logoutBtn) logoutBtn.textContent = isEn ? '➡' : '⬅';

  /* Save preference */
  localStorage.setItem('wg_lang', lang);
  curLang = lang;

  /* Re-apply mobile page title */
  updateMobTitle();

  /* Sync all empty text inputs to new language direction */
  document.querySelectorAll('input,textarea').forEach(function(inp){
    if (inp.value) return;
    var t = inp.type || '';
    if (['hidden','checkbox','radio','number','date','time','month','week',
         'color','range','file','email','password'].indexOf(t) !== -1) return;
    inp.setAttribute('dir', dir);
    inp.setAttribute('lang', lang);
  });
}

function toggleLang() { applyLang(curLang === 'ar' ? 'en' : 'ar'); }

/* ── Phone country codes ─────────────────────────────────── */
const PHONE_CODES = [
  {v:'+966',l:'🇸🇦 +966 السعودية'},{v:'+971',l:'🇦🇪 +971 الإمارات'},
  {v:'+965',l:'🇰🇼 +965 الكويت'}, {v:'+974',l:'🇶🇦 +974 قطر'},
  {v:'+973',l:'🇧🇭 +973 البحرين'},{v:'+968',l:'🇴🇲 +968 عُمان'},
  {v:'+962',l:'🇯🇴 +962 الأردن'}, {v:'+20', l:'🇪🇬 +20  مصر'},
  {v:'+213',l:'🇩🇿 +213 الجزائر'},{v:'+216',l:'🇹🇳 +216 تونس'},
  {v:'+212',l:'🇲🇦 +212 المغرب'}, {v:'+249',l:'🇸🇩 +249 السودان'},
  {v:'+218',l:'🇱🇾 +218 ليبيا'},  {v:'+963',l:'🇸🇾 +963 سوريا'},
  {v:'+961',l:'🇱🇧 +961 لبنان'},  {v:'+967',l:'🇾🇪 +967 اليمن'},
  {v:'+964',l:'🇮🇶 +964 العراق'}, {v:'+90', l:'🇹🇷 +90  تركيا'},
  {v:'+1',  l:'🇺🇸 +1   أمريكا'}, {v:'+44', l:'🇬🇧 +44  بريطانيا'},
  {v:'+49', l:'🇩🇪 +49  ألمانيا'},{v:'+33', l:'🇫🇷 +33  فرنسا'},
  {v:'+7',  l:'🇷🇺 +7   روسيا'},  {v:'+86', l:'🇨🇳 +86  الصين'},
  {v:'+91', l:'🇮🇳 +91  الهند'},  {v:'+92', l:'🇵🇰 +92  باكستان'},
  {v:'+880',l:'🇧🇩 +880 بنغلاديش'},
];
function fillPhoneCodeSelect(id, selected) {
  const el = document.getElementById(id); if (!el) return;
  el.innerHTML = PHONE_CODES.map(p =>
    `<option value="${p.v}"${p.v===(selected||'+966')?' selected':''}>${p.l}</option>`).join('');
}
function parsePhone(phone) {
  if (!phone) return {code:'+966', num:''};
  const sorted = [...PHONE_CODES].sort((a,b) => b.v.length - a.v.length);
  for (const p of sorted) {
    if (phone.startsWith(p.v)) return {code:p.v, num:phone.slice(p.v.length).replace(/^\s+/,'')};
  }
  return {code:'+966', num:phone};
}
function buildPhone(codeId, numId) {
  const code = document.getElementById(codeId)?.value || '+966';
  const num  = (document.getElementById(numId)?.value||'').trim();
  return num ? code + num : null;
}

/* ── Auto Input Direction (Arabic ↔ Latin) ───────────────── */
/* Applies to ALL text/textarea inputs — no class needed      */
const _SKIP_TYPES = new Set([
  'hidden','checkbox','radio','number','date','time','month','week',
  'color','range','file','email','password'
]);
function _autoInputDir(el) {
  if (!el || _SKIP_TYPES.has(el.type) || el.readOnly) return;
  var val = el.value || '';
  if (!val) {
    el.setAttribute('dir',  curLang === 'en' ? 'ltr' : 'rtl');
    el.setAttribute('lang', curLang);
    return;
  }
  var hasAr  = /[؀-ۿ]/.test(val);
  var hasLat = /[a-zA-Z]/.test(val);
  if      (hasAr  && !hasLat) { el.setAttribute('dir','rtl'); el.setAttribute('lang','ar'); }
  else if (hasLat && !hasAr)  { el.setAttribute('dir','ltr'); el.setAttribute('lang','en'); }
  /* mixed: keep current direction — no change */
}
/* Detect on every keystroke */
document.addEventListener('input', function(e) {
  if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA') _autoInputDir(e.target);
}, true);
/* Set initial direction on focus (empty field) */
document.addEventListener('focus', function(e) {
  var el = e.target;
  if ((el.tagName !== 'INPUT' && el.tagName !== 'TEXTAREA') || _SKIP_TYPES.has(el.type)) return;
  if (!el.value) {
    el.setAttribute('dir',  curLang === 'en' ? 'ltr' : 'rtl');
    el.setAttribute('lang', curLang);
  }
}, true);

/* Cards internal nav bilingual */
const CN_NAV={
  ar:{hdr:'🗂 كروت العمولة',lblIndex:'كروت العمولات',subIndex:'كل الحسابات المسجّلة',lblModified:'الحسابات المعدّلة',subModified:'السجلات المُعدَّلة',lblSearch:'بحث عن حساب',subSearch:'ابحث برقم الحساب',lblCreate:'إنشاء كرت جديد',subCreate:'تسجيل حساب عمولة',lblEdit:'تعديل كرت عمولة',subEdit:'تعديل حساب موجود',lblTree:'شجرة الحسابات',subTree:'توزيع العمولات',footer:'منصة وفرة الخليجية\nلإدارة العمولات'},
  en:{hdr:'🗂 Commission Cards',lblIndex:'All Cards',subIndex:'All registered accounts',lblModified:'Modified Accounts',subModified:'Modified records',lblSearch:'Search Account',subSearch:'Search by account number',lblCreate:'Create New Card',subCreate:'Register commission account',lblEdit:'Edit Card',subEdit:'Edit existing account',lblTree:'Account Tree',subTree:'Commission distribution',footer:'Wafra Gulf Platform\nCommission Management'},
};
function cNavApplyLang(){
  const d=CN_NAV[curLang]||CN_NAV.ar;
  const _t=(id,v)=>{const el=document.getElementById(id);if(el)el.textContent=v;};
  _t('cnav-hdr',d.hdr);
  _t('cnav-lbl-index',d.lblIndex);   _t('cnav-sub-index',d.subIndex);
  _t('cnav-lbl-modified',d.lblModified); _t('cnav-sub-modified',d.subModified);
  _t('cnav-lbl-search',d.lblSearch); _t('cnav-sub-search',d.subSearch);
  _t('cnav-lbl-create',d.lblCreate); _t('cnav-sub-create',d.subCreate);
  _t('cnav-lbl-edit',d.lblEdit);     _t('cnav-sub-edit',d.subEdit);
  _t('cnav-lbl-tree',d.lblTree);     _t('cnav-sub-tree',d.subTree);
  const fn=document.getElementById('cnav-footer');if(fn)fn.innerHTML=d.footer.replace('\n','<br>');
}
const _appApplyLang=applyLang;
applyLang=function(lang){_appApplyLang(lang);cNavApplyLang();};

/* Apply saved preference on load */
applyLang(curLang);

// ── Logo Refresh ───────────────────────────────────────────
// Clears the session-splash flag so the splash plays again,
// then reloads the current page.
function logoRefresh() {
  sessionStorage.removeItem('wfr_shown');
  location.reload();
}

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
async function api(method, url, body = null) {
  let res;
  try {
    res = await fetch(API + url, {
      method,
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        ...(API_TOKEN ? {'Authorization': 'Bearer ' + API_TOKEN} : {}),
      },
      body: body ? JSON.stringify(body) : null,
    });
  } catch (e) {
    return { success: false, message: 'تعذّر الاتصال بالخادم. تحقق من الإنترنت.' };
  }
  if (res.status === 401) {
    window.location.href = '{{ route("auth.login") }}';
    return { success: false, message: 'غير مصرح.' };
  }
  try {
    return await res.json();
  } catch (e) {
    return { success: false, message: 'خطأ في الخادم (' + res.status + ').' };
  }
}

// ── Sidebar user info ──────────────────────────────────────
if (CURRENT_USER && CURRENT_USER.name) {
  document.getElementById('sb-avatar').textContent = CURRENT_USER.name.charAt(0).toUpperCase();
  document.getElementById('sb-username').textContent = CURRENT_USER.name;
  document.getElementById('sb-role').textContent = CURRENT_USER.role === 'finance_admin' ? 'مدير مالي 💼' : 'مدير فرع';
}

// ── Security helper — HTML entity encoder ──────────────────
// Always use esc() when inserting user-supplied text into innerHTML.
// Usage: `<td>${esc(user.name)}</td>`
function esc(s) {
  if (s == null) return '';
  return String(s)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#39;')
    .replace(/\//g, '&#47;');
}

// ── Helpers ────────────────────────────────────────────────
const fmt = n => n > 0 ? Number(n).toLocaleString('en', {maximumFractionDigits:0}) : '—';
const fmtK = n => {
  if (!n || isNaN(n)) return '—';
  if (n >= 1e6) return (n/1e6).toFixed(1) + 'M';
  if (n >= 1000) return (n/1000).toFixed(0) + 'K';
  return Number(n).toLocaleString('en', {maximumFractionDigits:0});
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
  'e':           '{{ route("employees.index") }}',
  'settings':    '{{ route("settings.index") }}',
  'import':      '{{ route("import.index") }}',
  'managers':    '{{ route("managers.index") }}',
  'permissions': '{{ route("permissions.index") }}',
  'tree':        '{{ route("cards.tree") }}',
  'cc':          '{{ auth()->user()?->isBranchManager() ? route("callcenter.pending") : route("callcenter.index") }}',
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

// Update mobile page title (uses I18N)
function updateMobTitle() {
  const path = window.location.pathname;
  const keyMap = {
    '/dashboard':       'page.dashboard',
    '/cards/create':    'page.cards.new',
    '/cards/modified':  'page.cards.mod',
    '/cards/edit':      'page.cards.edit',
    '/cards/tree':      'page.cards.tree',
    '/cards':           'page.cards',
    '/reports/dynamic': 'page.dynamic',
    '/reports':         'page.reports',
    '/employees':       'page.employees',
    '/settings':        'page.settings',
    '/managers':        'page.settings',
    '/branches':        'page.settings',
    '/permissions':     'page.settings',
    '/import':          'page.import',
    '/callcenter/pending': 'page.cc.pending',
    '/callcenter':      'page.cc',
  };
  for (const [route, key] of Object.entries(keyMap)) {
    if (path.startsWith(route)) {
      const el = document.getElementById('mob-pg-title');
      if (el) el.textContent = I18N[curLang]?.[key] || I18N.ar[key] || '';
      break;
    }
  }
}

// Init
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
document.addEventListener('DOMContentLoaded', initMobile);
setTimeout(initMobile, 50);
</script>

<script>
// ── Pending employees badge (settings nav) ─────────────────
async function loadPendingBadge() {
  try {
    const r = await api('GET', '/employees/pending');
    const list = r?.data ?? r?.employees ?? [];
    const count = Array.isArray(list) ? list.length : (r?.count ?? 0);
    const badge = document.getElementById('sb-pending-count');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  } catch(e) { /* silent */ }
}
document.addEventListener('DOMContentLoaded', loadPendingBadge);
setInterval(loadPendingBadge, 60000);

// ── CC pending badge counter ───────────────────────────────
async function loadCcPendingCount() {
  try {
    const r = await api('GET', '/cc/pending');
    const count = r?.count ?? 0;
    const badge = document.getElementById('sb-cc-pending');
    if (!badge) return;
    if (count > 0) {
      badge.textContent = count;
      badge.style.display = '';
    } else {
      badge.style.display = 'none';
    }
  } catch(e) { /* silent */ }
}
// Load once on page ready, then refresh every 60s
document.addEventListener('DOMContentLoaded', loadCcPendingCount);
setInterval(loadCcPendingCount, 60000);
</script>

@stack('scripts')

{{-- ████ MOBILE UI ████ --}}
<div class="mob-header" id="mob-header">
  <button class="mob-burger" onclick="toggleMobSidebar()">☰</button>
  <div class="mob-logo-row">
    <img src="{{ asset('logo.png') }}" class="mob-logo" alt="وفرة الخليجية للخدمات المالية"
         onclick="logoRefresh()" style="cursor:pointer">
    <div class="mob-title"><span data-i18n="company.short">وفرة الخليجية</span><small id="mob-pg-title">لوحة المتابعة</small></div>
  </div>
  <div style="display:flex;gap:5px;align-items:center">
    <button class="lang-toggle-btn" onclick="toggleLang()" id="lang-btn-mob" style="padding:4px 8px;font-size:11px">
      <span id="lang-flag-mob">🇬🇧</span><span id="lang-label-mob">EN</span>
    </button>
    <button class="mob-theme-btn" onclick="toggleTheme()" id="mob-theme-btn">🌙</button>
  </div>
</div>

<div class="mob-overlay" id="mob-overlay" onclick="closeMobSidebar()"></div>

<div class="mob-bottom-nav" id="mob-bottom-nav">
  <div class="mob-nav-items">
    <button class="mob-nav-item on" id="mbn-d" onclick="mobGo('d','mbn-d')">
      <span class="mob-nav-icon">🏠</span>
      <span class="mob-nav-lbl" data-i18n="mob.home">الرئيسية</span>
    </button>
    <button class="mob-nav-item" id="mbn-c" onclick="mobGo('c','mbn-c')">
      <span class="mob-nav-icon">🗂</span>
      <span class="mob-nav-lbl" data-i18n="mob.cards">الكروت</span>
      <span class="mob-nav-dot" id="mbn-dot-c"></span>
    </button>
    <button class="mob-nav-item" id="mbn-r" onclick="mobGo('r','mbn-r')">
      <span class="mob-nav-icon">📈</span>
      <span class="mob-nav-lbl" data-i18n="mob.reports">التقارير</span>
    </button>
    <button class="mob-nav-item" id="mbn-e" onclick="mobGo('e','mbn-e')">
      <span class="mob-nav-icon">👥</span>
      <span class="mob-nav-lbl" data-i18n="mob.employees">الموظفون</span>
    </button>
    <button class="mob-nav-item" id="mbn-more" onclick="toggleMobMore()">
      <span class="mob-nav-icon">⋯</span>
      <span class="mob-nav-lbl" data-i18n="mob.more">المزيد</span>
      <span class="mob-nav-dot" id="mbn-dot-more"></span>
    </button>
  </div>
</div>

<div class="mob-more-sheet" id="mob-more-sheet">
  <div class="mob-sheet-handle"></div>
  <div class="mob-sheet-grid">
    <div class="mob-sheet-item" onclick="mobGo('cc');closeMobMore()">
      @if(auth()->user()?->isBranchManager())
      <div class="mob-sheet-ico">📩</div><div class="mob-sheet-lbl" data-i18n="nav.cc.pending">كروت CC الواردة</div>
      @else
      <div class="mob-sheet-ico">📞</div><div class="mob-sheet-lbl" data-i18n="nav.cc">مركز الاتصال</div>
      @endif
    </div>
    <div class="mob-sheet-item" onclick="mobGo('m');closeMobMore()">
      <div class="mob-sheet-ico">✏️</div><div class="mob-sheet-lbl" data-i18n="mob.modified">المعدّلة</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('tree');closeMobMore()">
      <div class="mob-sheet-ico">🌳</div><div class="mob-sheet-lbl" data-i18n="mob.tree">شجرة الحسابات</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('dynamic');closeMobMore()">
      <div class="mob-sheet-ico">🔧</div><div class="mob-sheet-lbl" data-i18n="mob.dynamic">تقرير ديناميكي</div>
    </div>
    <div class="mob-sheet-item" onclick="mobGo('settings');closeMobMore()">
      <div class="mob-sheet-ico">⚙️</div><div class="mob-sheet-lbl" data-i18n="nav.settings">الإعدادات</div>
    </div>
    @if(auth()->user()?->isFinanceAdmin())
    <div class="mob-sheet-item" onclick="mobGo('import');closeMobMore()">
      <div class="mob-sheet-ico">📥</div><div class="mob-sheet-lbl" data-i18n="mob.import">استيراد</div>
    </div>
    @endif
    <div class="mob-sheet-item" onclick="doLogout()">
      <div class="mob-sheet-ico">🚪</div><div class="mob-sheet-lbl" data-i18n="mob.logout">خروج</div>
    </div>
  </div>
</div>
<div class="mob-overlay" id="mob-sheet-overlay" onclick="closeMobMore()" style="display:none;z-index:1598"></div>

</body>
</html>
