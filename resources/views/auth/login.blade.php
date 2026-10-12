<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
<meta http-equiv="Pragma" content="no-cache">
<meta http-equiv="Expires" content="0">
<title>تسجيل الدخول — وفرة الخليجية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;700;800;900&display=swap" rel="stylesheet">
<style>
:root {
  --pri:#1AADBA; --pri2:#22C4D4; --pri3:#0E7A88;
  --bg:#070D18;  --bg2:#0D1B2C; --bg3:#132234;
  --brd:rgba(26,173,186,.22);
  --tx:#E6EFF6;  --mu:#5A80A0;  --gr:#1ECC80; --re:#E84545;
}
*{ margin:0; padding:0; box-sizing:border-box }

/* ── Page ── */
html, body {
  height: 100%;
}
body {
  font-family:'Tajawal',sans-serif;
  background:var(--bg);
  color:var(--tx);
  min-height:100vh;
  overflow-x:hidden;
  position:relative;
}

/* ── Background blobs ── */
.bg-blob {
  position:fixed; border-radius:50%;
  filter:blur(90px); pointer-events:none; z-index:0;
}
.blob-1 {
  width:600px; height:600px;
  background:radial-gradient(circle, rgba(26,173,186,.16) 0%, transparent 70%);
  top:-150px; right:-100px;
  animation:blobFloat1 9s ease-in-out infinite alternate;
}
.blob-2 {
  width:500px; height:500px;
  background:radial-gradient(circle, rgba(14,122,136,.12) 0%, transparent 70%);
  bottom:-120px; left:-80px;
  animation:blobFloat2 11s ease-in-out infinite alternate;
}
.blob-3 {
  width:300px; height:300px;
  background:radial-gradient(circle, rgba(30,204,128,.06) 0%, transparent 70%);
  top:40%; left:30%;
  animation:blobFloat3 13s ease-in-out infinite alternate;
}
@keyframes blobFloat1 { from{transform:translate(0,0) scale(1)} to{transform:translate(-30px,20px) scale(1.08)} }
@keyframes blobFloat2 { from{transform:translate(0,0) scale(1)} to{transform:translate(20px,-25px) scale(1.05)} }
@keyframes blobFloat3 { from{transform:translate(0,0)} to{transform:translate(15px,15px)} }

/* ── Geometric decorations ── */
.geo { position:fixed; pointer-events:none; z-index:0; }
.geo-ring { border-radius:50%; border:1px solid rgba(26,173,186,.07); }
.geo-r1 { width:700px; height:700px; top:-250px; left:10%; }
.geo-r2 { width:450px; height:450px; bottom:-100px; right:10%; border-color:rgba(26,173,186,.05); }
.geo-line { height:1px; background:linear-gradient(90deg, transparent, rgba(26,173,186,.12), transparent); }
.geo-l1 { width:50%; top:25%; left:5%; }
.geo-l2 { width:35%; bottom:30%; right:5%; }

/* ══════════════════════════════════════════════
   SPLIT SCREEN LAYOUT
   ══════════════════════════════════════════════ */
.login-wrap {
  position:relative; z-index:1;
  display:flex;
  align-items:stretch;
  min-height:100vh;
  width:100%;
}

/* ── Brand half (fills remaining space) ── */
.brand {
  flex:1;
  min-width:0;
  display:flex;
  flex-direction:column;
  justify-content:center;
  padding:60px 56px 60px 72px;
  animation:brandFadeUp .95s cubic-bezier(.16,1,.3,1) .5s both;
}
[dir="ltr"] .brand {
  padding:60px 72px 60px 56px;
}
@keyframes brandFadeUp {
  from { opacity:0; transform:translateY(28px); }
  to   { opacity:1; transform:none; }
}

/* ── Card panel — HALF SCREEN ── */
.login-card {
  /* Takes exactly half the viewport */
  width:50%;
  min-width:380px;
  flex-shrink:0;
  min-height:100vh;
  /* Glass panel */
  background: linear-gradient(160deg,
    rgba(8,18,35,.95) 0%,
    rgba(11,24,44,.92) 50%,
    rgba(8,18,35,.97) 100%
  );
  backdrop-filter:blur(40px);
  -webkit-backdrop-filter:blur(40px);
  /* No border-radius — flush panel */
  border-radius:0;
  border:none;
  /* Left separator glow line (RTL: card is on right, left side faces brand) */
  border-left:1px solid rgba(26,173,186,.15);
  /* Shadow toward the brand side */
  box-shadow:
    -40px 0 120px rgba(0,0,0,.5),
    -4px 0 20px rgba(0,0,0,.3);
  /* Inner layout */
  display:flex;
  flex-direction:column;
  justify-content:center;
  align-items:center;
  overflow-y:auto;
  position:relative;
  /* 3D from-back animation */
  animation:cardFromBack 1.1s cubic-bezier(.16,1,.3,1) 0s both;
}

/* LTR: card on LEFT — separator on right side */
[dir="ltr"] .login-card {
  border-left:none;
  border-right:1px solid rgba(26,173,186,.15);
  box-shadow:40px 0 120px rgba(0,0,0,.5), 4px 0 20px rgba(0,0,0,.3);
}

/* Separator glowing line on card edge */
.login-card::after {
  content:'';
  position:absolute; top:0; left:0;
  width:1px; height:100%;
  background:linear-gradient(180deg,
    transparent 0%,
    rgba(26,173,186,.08) 10%,
    rgba(26,173,186,.45) 30%,
    rgba(26,173,186,.65) 50%,
    rgba(26,173,186,.45) 70%,
    rgba(26,173,186,.08) 90%,
    transparent 100%
  );
  pointer-events:none; z-index:1;
}
[dir="ltr"] .login-card::after {
  left:auto; right:0;
}

/* Top accent bar */
.login-card::before {
  content:'';
  position:absolute; top:0; left:10%; right:10%; height:2px;
  background:linear-gradient(90deg, transparent, rgba(26,173,186,.5), var(--pri2), rgba(26,173,186,.5), transparent);
  z-index:2;
}

/* ── Card inner content (constrained width, centered) ── */
.card-inner {
  width:100%;
  max-width:440px;
  padding:48px 32px;
}

/* ═══════════════════════════════════════════════════
   3D FROM-BACK-TO-FRONT ANIMATION
   ═══════════════════════════════════════════════════ */
@keyframes cardFromBack {
  0% {
    opacity:0;
    transform:perspective(2000px) translateZ(-1400px) scale(0.08) rotateX(12deg);
    filter:blur(32px) brightness(.25);
    background:rgba(8,18,35,0);
  }
  20% {
    opacity:.4;
    filter:blur(18px) brightness(.55);
    transform:perspective(2000px) translateZ(-600px) scale(0.35) rotateX(6deg);
  }
  50% {
    opacity:.85;
    filter:blur(6px) brightness(.85);
    transform:perspective(2000px) translateZ(-80px) scale(0.88) rotateX(1.5deg);
  }
  68% {
    opacity:1;
    filter:blur(1px) brightness(1);
    transform:perspective(2000px) translateZ(28px) scale(1.02) rotateX(-.5deg);
  }
  80% {
    transform:perspective(2000px) translateZ(-10px) scale(0.992);
    filter:blur(0) brightness(1);
  }
  90% {
    transform:perspective(2000px) translateZ(5px) scale(1.004);
  }
  100% {
    opacity:1;
    transform:perspective(2000px) translateZ(0) scale(1) rotateX(0);
    filter:blur(0) brightness(1);
  }
}

/* ─────────────────────────────────────────
   BRAND SECTION
   ───────────────────────────────────────── */
.brand-logo-row {
  display:flex; align-items:center; gap:22px; margin-bottom:32px;
}

/* ── Logo wrapper ── */
.brand-logo-wrap {
  position:relative; flex-shrink:0;
  width:138px; height:138px;
  display:flex; align-items:center; justify-content:center;
}
.brand-logo-wrap::before {
  content:''; position:absolute; inset:0;
  border-radius:28px;
  border:3px solid rgba(26,173,186,.7);
  animation:lgRing 30s linear infinite;
  pointer-events:none;
}
.brand-logo-wrap::after {
  content:''; position:absolute; inset:0;
  border-radius:28px;
  border:2px solid rgba(26,173,186,.4);
  animation:lgRing 30s linear .55s infinite;
  pointer-events:none;
}
.brand-logo-img {
  width:138px; height:138px;
  background:white;
  border:2.5px solid rgba(26,173,186,.32);
  border-radius:28px;
  display:flex; align-items:center; justify-content:center;
  flex-shrink:0; overflow:hidden; position:relative;
  animation:lgPop 30s linear infinite;
}
.brand-logo-img img { width:124px; height:124px; object-fit:contain; }
.brand-logo-text .name {
  font-size:26px; font-weight:900; color:var(--tx); line-height:1.25;
}
.brand-logo-text .name span { color:var(--pri2); }
.brand-logo-text .en {
  font-size:11px; color:var(--pri2);
  letter-spacing:2px; text-transform:uppercase; margin-top:6px;
}

/* 30-second logo pop */
@keyframes lgPop {
  0%   { transform:scale(0) rotate(-18deg); opacity:0; filter:blur(16px);
         box-shadow:0 0 0 rgba(26,173,186,0); }
  2%   { transform:scale(1.38) rotate(7deg); opacity:1; filter:blur(0);
         box-shadow:0 24px 80px rgba(26,173,186,.85),0 8px 24px rgba(0,0,0,.35); }
  4%   { transform:scale(0.82) rotate(-3deg); box-shadow:0 12px 44px rgba(26,173,186,.55); }
  5.5% { transform:scale(1.14) rotate(1.5deg); }
  7%   { transform:scale(0.94) rotate(-.5deg); }
  8%   { transform:scale(1.05); }
  9%   { transform:scale(0.98); }
  10%  { transform:scale(1) rotate(0); opacity:1; filter:blur(0);
         box-shadow:0 10px 40px rgba(26,173,186,.42),0 4px 14px rgba(0,0,0,.28); }
  35%  { box-shadow:0 16px 58px rgba(26,173,186,.68),0 0 90px rgba(26,173,186,.2); }
  55%  { box-shadow:0 10px 40px rgba(26,173,186,.42),0 4px 14px rgba(0,0,0,.28); }
  80%  { box-shadow:0 16px 58px rgba(26,173,186,.68),0 0 90px rgba(26,173,186,.2); }
  100% { transform:scale(1) rotate(0); opacity:1; filter:blur(0);
         box-shadow:0 10px 40px rgba(26,173,186,.42),0 4px 14px rgba(0,0,0,.28); }
}
@keyframes lgRing {
  0%   { transform:scale(.85); opacity:0; }
  1.5% { transform:scale(.85); opacity:.9; }
  10%  { transform:scale(2.6); opacity:0; }
  100% { transform:scale(2.6); opacity:0; }
}

/* Status pill */
.brand-pill {
  display:inline-flex; align-items:center; gap:7px;
  background:rgba(26,173,186,.08);
  border:1px solid rgba(26,173,186,.22);
  border-radius:24px; padding:5px 14px;
  font-size:11px; color:var(--pri2); font-weight:700;
  margin-bottom:24px; backdrop-filter:blur(6px);
}
.brand-dot {
  width:7px; height:7px; background:var(--gr); border-radius:50%;
  box-shadow:0 0 6px rgba(30,204,128,.6);
  animation:dotPulse 2s infinite;
}
@keyframes dotPulse { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.8)} }

/* Big headline */
.brand-headline {
  font-size:3.4rem; font-weight:900; line-height:1.05;
  letter-spacing:-1.5px; margin-bottom:18px; color:var(--tx);
}
.brand-headline em {
  font-style:normal; color:transparent;
  background:linear-gradient(135deg, var(--pri2), var(--pri), #7AABCA);
  -webkit-background-clip:text; background-clip:text;
}
.brand-desc {
  font-size:14.5px; color:var(--mu); line-height:1.85; margin-bottom:28px;
}

/* Feature badges */
.brand-badges { display:flex; flex-wrap:wrap; gap:10px; }
.brand-badge {
  display:flex; align-items:center; gap:8px;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(26,173,186,.18);
  border-radius:10px; padding:8px 14px;
  font-size:12.5px; color:var(--tx); font-weight:600;
  backdrop-filter:blur(8px); transition:all .2s;
}
.brand-badge:hover { background:rgba(26,173,186,.1); border-color:rgba(26,173,186,.35); }
.badge-icon {
  width:24px; height:24px; background:rgba(26,173,186,.15);
  border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:13px;
}

/* ─────────────────────────────────────────
   CARD CONTENT
   ───────────────────────────────────────── */

/* Card logo — bigger */
.card-logo-wrap {
  display:flex; align-items:center; gap:14px;
  margin-bottom:26px; padding-bottom:22px;
  border-bottom:1px solid rgba(26,173,186,.14);
}
.card-logo-img {
  width:100px; height:100px;
  background:white;
  border:2px solid rgba(26,173,186,.35);
  border-radius:22px;
  display:flex; align-items:center; justify-content:center;
  overflow:hidden; flex-shrink:0;
  box-shadow:
    0 10px 36px rgba(26,173,186,.45),
    0 4px 12px rgba(0,0,0,.28),
    0 0 0 4px rgba(26,173,186,.08);
  animation:cardLogoPop 1.2s cubic-bezier(.34,1.56,.64,1) .6s both;
}
.card-logo-img img { width:90px; height:90px; object-fit:contain; }
@keyframes cardLogoPop {
  0%  { transform:scale(0) rotate(-18deg); opacity:0; filter:blur(12px);
        box-shadow:0 0 0 rgba(26,173,186,0); }
  55% { transform:scale(1.18) rotate(4deg); opacity:1; filter:blur(0);
        box-shadow:0 18px 60px rgba(26,173,186,.7); }
  75% { transform:scale(0.92) rotate(-1.5deg); }
  88% { transform:scale(1.05); }
  100%{ transform:scale(1) rotate(0); opacity:1; filter:blur(0);
        box-shadow:0 10px 36px rgba(26,173,186,.45),0 4px 12px rgba(0,0,0,.28),0 0 0 4px rgba(26,173,186,.08); }
}

.card-logo-text .title {
  font-size:16px; font-weight:900; color:var(--tx); line-height:1.3;
}
.card-logo-text .sub {
  font-size:11px; color:var(--pri2); margin-top:3px;
}

/* First-time banner */
.first-time-banner {
  background:rgba(30,204,128,.1); border:1px solid rgba(30,204,128,.3);
  border-radius:10px; padding:10px 13px;
  font-size:12px; color:#b0ffdd; line-height:1.6;
  margin-bottom:16px; display:none;
}
.first-time-banner.show { display:block; }
.first-time-banner strong { color:#EFFFEE; font-weight:900; }

/* Tabs */
.tabs {
  display:flex;
  background:rgba(255,255,255,.04);
  border:1px solid rgba(26,173,186,.12);
  border-radius:12px; padding:3px; margin-bottom:22px;
}
.tab {
  flex:1; padding:9px; border:none; background:none;
  color:var(--mu); font-family:'Tajawal',sans-serif;
  font-size:13px; font-weight:700; cursor:pointer;
  border-radius:10px; transition:all .2s;
}
.tab.active {
  background:rgba(26,173,186,.18);
  border:1px solid rgba(26,173,186,.25);
  color:var(--pri2);
}

/* Form elements */
.form-group { margin-bottom:16px; }
.form-label {
  display:block; font-size:12.5px; color:var(--mu);
  font-weight:700; margin-bottom:8px;
}
.form-input {
  width:100%;
  background:rgba(255,255,255,.05);
  border:1px solid rgba(26,173,186,.18);
  border-radius:12px;
  padding:13px 16px;
  color:var(--tx);
  font-family:'Tajawal',sans-serif;
  font-size:14px; outline:none;
  transition:all .22s;
  backdrop-filter:blur(4px);
}
.form-input:focus {
  border-color:rgba(26,173,186,.6);
  background:rgba(26,173,186,.07);
  box-shadow:0 0 0 3px rgba(26,173,186,.12);
}
.form-input::placeholder { color:rgba(90,128,160,.55); }

.pw-wrap { position:relative; }
.pw-wrap .form-input { padding-left:44px; }
.pw-toggle {
  position:absolute; left:13px; top:50%; transform:translateY(-50%);
  background:none; border:none; color:var(--mu); cursor:pointer;
  font-size:16px; line-height:1; transition:color .2s;
}
.pw-toggle:hover { color:var(--pri2); }

/* Password strength */
.psb { height:3px; border-radius:2px; background:rgba(255,255,255,.06); margin-top:6px; transition:all .3s; }
.psb.weak   { background:var(--re);  width:33%; }
.psb.medium { background:#F5A828; width:66%; }
.psb.strong { background:var(--gr);  width:100%; }

/* Alerts */
.err-box, .ok-box {
  border-radius:10px; padding:10px 14px;
  font-size:13px; margin-bottom:13px; display:none;
  backdrop-filter:blur(8px);
}
.err-box.show { display:block; }
.ok-box.show  { display:block; }
.err-box { background:rgba(232,69,69,.1); border:1px solid rgba(232,69,69,.25); color:#ffb3b3; }
.ok-box  { background:rgba(30,204,128,.1); border:1px solid rgba(30,204,128,.25); color:#b0ffdd; }

/* Login button */
.btn-login {
  width:100%; padding:14px;
  background:linear-gradient(135deg, var(--pri2), var(--pri), var(--pri3));
  border:none; border-radius:12px;
  color:white; font-family:'Tajawal',sans-serif;
  font-size:16px; font-weight:900; cursor:pointer;
  box-shadow:0 8px 28px rgba(26,173,186,.4), 0 2px 8px rgba(0,0,0,.3);
  transition:all .22s; position:relative; overflow:hidden;
  letter-spacing:.3px;
}
.btn-login::after {
  content:''; position:absolute; inset:0;
  background:linear-gradient(135deg, rgba(255,255,255,.15), transparent 60%);
}
.btn-login:hover {
  transform:translateY(-2px);
  box-shadow:0 12px 38px rgba(26,173,186,.5), 0 3px 10px rgba(0,0,0,.3);
}
.btn-login:active { transform:translateY(0); }

/* Misc */
.link-small {
  text-align:center; margin-top:12px;
  font-size:12.5px; color:var(--mu); cursor:pointer; transition:color .2s;
}
.link-small:hover { color:var(--pri2); }
.info-box {
  background:rgba(26,173,186,.07); border:1px solid rgba(26,173,186,.18);
  border-radius:10px; padding:10px 14px;
  font-size:12px; color:var(--mu); line-height:1.7; margin-bottom:15px;
}
.info-box strong { color:var(--pri2); }

/* ── Language toggle button ── */
#lp-lang-btn {
  margin-right:auto; margin-left:0;
  background:rgba(26,173,186,.08);
  border:1px solid rgba(26,173,186,.25);
  border-radius:8px; padding:6px 12px;
  color:var(--pri2); font-size:12px; font-weight:700;
  cursor:pointer; font-family:'Tajawal',sans-serif;
  display:flex; align-items:center; gap:5px; flex-shrink:0;
  transition:all .2s;
}
#lp-lang-btn:hover {
  background:rgba(26,173,186,.16);
  border-color:rgba(26,173,186,.45);
  box-shadow:0 0 12px rgba(26,173,186,.25);
}
[dir="ltr"] #lp-lang-btn { margin-right:0; margin-left:auto; }

/* ── LTR form overrides ── */
[dir="ltr"] .form-input        { text-align:left; direction:ltr; }
[dir="ltr"] .pw-wrap .form-input { padding-left:16px; padding-right:44px; }
[dir="ltr"] .pw-toggle          { left:auto; right:13px; }
[dir="ltr"] .brand-headline     { letter-spacing:-1px; }

/* ── Mobile ── */
@media(max-width:900px) {
  .brand { display:none; }
  .login-card {
    width:100%; min-width:0;
    border-left:none; border-right:none;
    min-height:100vh;
    box-shadow:none;
  }
  .login-card::after { display:none; }
  [dir="ltr"] .login-card { border-left:none; border-right:none; }
}
@media(max-width:480px) {
  .card-inner { padding:32px 20px; }
  .card-logo-img { width:84px; height:84px; border-radius:18px; }
  .card-logo-img img { width:74px; height:74px; }
}
</style>
</head>
<body>

{{-- Blobs --}}
<div class="bg-blob blob-1"></div>
<div class="bg-blob blob-2"></div>
<div class="bg-blob blob-3"></div>

{{-- Geometric rings --}}
<div class="geo geo-ring geo-r1"></div>
<div class="geo geo-ring geo-r2"></div>
<div class="geo geo-line geo-l1"></div>
<div class="geo geo-line geo-l2"></div>

{{-- Splash --}}
@include('partials.splash')

<div class="login-wrap">

  {{-- ══════════════════════════════════════════════════════
       CARD PANEL — FIRST IN HTML
       RTL (Arabic)  → appears on the RIGHT
       LTR (English) → appears on the LEFT
       ═══════════════════════════════════════════════════ --}}
  <div class="login-card">
    <div class="card-inner">

      {{-- Card Logo --}}
      <div class="card-logo-wrap">
        <div class="card-logo-img">
          <img src="{{ asset('logo.png') }}" alt="وفرة الخليجية">
        </div>
        <div class="card-logo-text">
          <div class="title" id="lp-card-title">وفرة الخليجية للخدمات المالية</div>
          <div class="sub"   id="lp-card-sub">Wafra Gulf Financial Services</div>
        </div>
        <button onclick="lpToggleLang()" id="lp-lang-btn" title="Switch Language">
          <span id="lp-lang-flag">🇬🇧</span><span id="lp-lang-lbl">EN</span>
        </button>
      </div>

      {{-- First-time banner --}}
      <div class="first-time-banner" id="first-time-banner">
        🎉 <strong id="lp-first-strong">أول مرة؟</strong>
        <span id="lp-first-text">أنشئ حساب المدير المالي أولاً</span>
      </div>

      {{-- Tabs --}}
      <div class="tabs">
        <button class="tab active" id="tab-btn-login"    onclick="switchTab('login',this)">دخول</button>
        <button class="tab"        id="tab-btn-register" onclick="switchTab('register',this)" style="display:none">📝 تسجيل</button>
        <button class="tab"        id="tab-btn-forgot"   onclick="switchTab('forgot',this)">استعادة</button>
      </div>

      {{-- ── Login ── --}}
      <div id="tab-login">
        @if(session('error'))
          <div class="err-box show">{{ session('error') }}</div>
        @endif
        @if(session('status'))
          <div class="ok-box show">✅ {{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('auth.login.submit') }}">
          @csrf
          <div class="form-group">
            <label class="form-label" id="lp-label-email">البريد الإلكتروني</label>
            <input class="form-input" type="email" name="email" id="inp-email"
                   value="{{ old('email') }}" placeholder="your@wafragulf.com"
                   required autocomplete="email">
            @error('email')
              <div style="color:var(--re);font-size:11px;margin-top:5px">{{ $message }}</div>
            @enderror
          </div>
          <div class="form-group">
            <label class="form-label" id="lp-label-password">كلمة المرور</label>
            <div class="pw-wrap">
              <input class="form-input" type="password" name="password" id="pw"
                     required autocomplete="new-password">
              <button type="button" class="pw-toggle" onclick="togglePw('pw')">👁</button>
            </div>
          </div>
          <button type="submit" class="btn-login" id="lp-btn-login">دخول إلى النظام ←</button>
        </form>
        <div class="link-small" id="lp-link-forgot" onclick="switchTab('forgot',null)">نسيت كلمة المرور؟</div>
      </div>

      {{-- ── Register ── --}}
      <div id="tab-register" style="display:none">
        {{-- FA first-time --}}
        <div id="reg-fa">
          <div class="info-box" id="lp-reg-info">
            🔐 <strong>لمرة واحدة فقط.</strong> بعد الإنشاء يختفي هذا الخيار نهائياً.
          </div>
          <form method="POST" action="{{ route('auth.register.submit') }}">
            @csrf
            <div class="form-group">
              <label class="form-label" id="lp-label-name-r">الاسم الكامل</label>
              <input class="form-input" type="text" name="name" id="inp-name-r"
                     value="{{ old('name') }}" placeholder="الاسم الكامل" required>
            </div>
            <div class="form-group">
              <label class="form-label" id="lp-label-email-r">البريد الإلكتروني</label>
              <input class="form-input" type="email" name="email"
                     value="{{ old('email') }}" placeholder="your@wafragulf.com" required>
            </div>
            <div class="form-group">
              <label class="form-label" id="lp-label-pw-r">كلمة المرور</label>
              <div class="pw-wrap">
                <input class="form-input" type="password" name="password" id="rpw"
                       required oninput="checkPwStrength(this)">
                <button type="button" class="pw-toggle" onclick="togglePw('rpw')">👁</button>
              </div>
              <div class="psb" id="pw-bar"></div>
              <span id="pw-hint" style="font-size:11px"></span>
            </div>
            <div class="form-group">
              <label class="form-label" id="lp-label-confirm-r">تأكيد كلمة المرور</label>
              <div class="pw-wrap">
                <input class="form-input" type="password" name="password_confirmation"
                       id="inp-confirm-r" required>
              </div>
            </div>
            @if($errors->any())
              <div class="err-box show">{{ $errors->first() }}</div>
            @endif
            <button type="submit" class="btn-login" id="lp-btn-register">إنشاء الحساب 🚀</button>
            <div class="link-small" id="lp-link-back-r" onclick="switchTab('login',null)">← رجوع للدخول</div>
          </form>
        </div>

        {{-- Invited manager --}}
        <div id="reg-invite" style="display:none">
          <div id="inv-step1">
            <div class="info-box" id="lp-inv-info">📧 أدخل بريدك الإلكتروني للتحقق من دعوتك</div>
            <div class="form-group">
              <label class="form-label" id="lp-label-email-i">البريد الإلكتروني</label>
              <input class="form-input" type="email" id="inv-email"
                     placeholder="your@wafragulf.com" autocomplete="email">
            </div>
            <div class="err-box" id="inv-err"></div>
            <button type="button" class="btn-login" id="lp-btn-verify" onclick="checkInvite()">التحقق من الدعوة ←</button>
            <div class="link-small" id="lp-link-back-i" onclick="switchTab('login',null)">← رجوع للدخول</div>
          </div>
          <div id="inv-step2" style="display:none">
            <div class="ok-box show" id="inv-branch-info" style="margin-bottom:12px"></div>
            <div class="form-group">
              <label class="form-label" id="lp-label-name-i">الاسم الكامل</label>
              <input class="form-input" type="text" id="inv-name" placeholder="اسمك الكامل">
            </div>
            <div class="form-group">
              <label class="form-label" id="lp-label-pw-i">كلمة المرور</label>
              <div class="pw-wrap">
                <input class="form-input" type="password" id="inv-pw"
                       placeholder="8 أحرف على الأقل" oninput="checkPwStrength2(this)">
                <button type="button" class="pw-toggle" onclick="togglePw('inv-pw')">👁</button>
              </div>
              <div class="psb" id="inv-pw-bar"></div>
              <span id="inv-pw-hint" style="font-size:11px"></span>
            </div>
            <div class="form-group">
              <label class="form-label" id="lp-label-confirm-i">تأكيد كلمة المرور</label>
              <div class="pw-wrap">
                <input class="form-input" type="password" id="inv-pw2"
                       placeholder="أعد كتابة كلمة المرور">
                <button type="button" class="pw-toggle" onclick="togglePw('inv-pw2')">👁</button>
              </div>
            </div>
            <div class="err-box" id="inv-reg-err"></div>
            <div class="ok-box"  id="inv-reg-ok"></div>
            <button type="button" class="btn-login" id="inv-submit-btn"
                    onclick="submitInviteReg()">إنشاء حسابي 🚀</button>
            <div class="link-small" id="lp-link-change-email" onclick="invBack()">← تغيير الإيميل</div>
          </div>
        </div>
      </div>

      {{-- ── Forgot ── --}}
      <div id="tab-forgot" style="display:none">
        <div class="info-box" id="lp-forgot-info">سيتم إرسال رابط الاستعادة على إيميلك مباشرة</div>
        <form method="POST" action="{{ route('auth.password.email') }}">
          @csrf
          <div class="form-group">
            <label class="form-label" id="lp-label-email-f">البريد الإلكتروني</label>
            <input class="form-input" type="email" name="email"
                   placeholder="your@wafragulf.com" required>
          </div>
          @if(session('status'))
            <div class="ok-box show">✅ {{ session('status') }}</div>
          @endif
          <button type="submit" class="btn-login" id="lp-btn-reset">إرسال رابط الاستعادة ←</button>
          <div class="link-small" id="lp-link-back-f" onclick="switchTab('login',null)">← رجوع للدخول</div>
        </form>
      </div>

    </div>{{-- .card-inner --}}
  </div>{{-- .login-card --}}

  {{-- ══════════════════════════════════════════════════════
       BRAND PANEL — SECOND IN HTML
       RTL (Arabic)  → appears on the LEFT
       LTR (English) → appears on the RIGHT
       ═══════════════════════════════════════════════════ --}}
  <div class="brand">
    <div class="brand-logo-row">
      <div class="brand-logo-wrap">
        <div class="brand-logo-img">
          <img src="{{ asset('logo.png') }}" alt="وفرة الخليجية">
        </div>
      </div>
      <div class="brand-logo-text">
        <div class="name" id="lg-name-ar">وفرة <span>الخليجية</span> للخدمات المالية</div>
        <div class="en"   id="lg-name-subline">Wafra Gulf Financial Services</div>
      </div>
    </div>

    <div class="brand-pill">
      <span class="brand-dot"></span>
      <span id="lp-status">النظام نشط ومتصل</span>
    </div>

    <div class="brand-headline" id="lp-headline">
      إدارة<br><em>كروت العمولات</em>
    </div>

    <div class="brand-desc" id="lp-desc">
      نظام إدارة عمولات وفرة الخليجية للخدمات المالية — يدار حصراً من الإدارة المالية عبر صلاحيات متدرجة وآمنة.
    </div>

    <div class="brand-badges">
      <div class="brand-badge"><div class="badge-icon">📊</div><span id="lp-b1">تقارير ديناميكية فورية</span></div>
      <div class="brand-badge"><div class="badge-icon">🔒</div><span id="lp-b2">صلاحيات متدرجة وآمنة</span></div>
      <div class="brand-badge"><div class="badge-icon">🏢</div><span id="lp-b3">دعم متعدد الفروع</span></div>
      <div class="brand-badge"><div class="badge-icon">🌐</div><span id="lp-b4">دعم كامل للغتين</span></div>
    </div>
  </div>

</div>{{-- .login-wrap --}}

<script>
/* ══════════════════════════════════════════════════════════
   UTILITIES
   ══════════════════════════════════════════════════════════ */
function switchTab(name, btn) {
  ['login','register','forgot'].forEach(function(t){
    document.getElementById('tab-'+t).style.display = 'none';
  });
  document.getElementById('tab-'+name).style.display = 'block';
  document.querySelectorAll('.tab').forEach(function(t){ t.classList.remove('active'); });
  if (btn) btn.classList.add('active');
  else { var b = document.getElementById('tab-btn-'+name); if(b) b.classList.add('active'); }
}

function togglePw(id) {
  var i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
}

function _pwScore(v) {
  return /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#!$%]).{8,}$/.test(v) ? 3
       : /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/.test(v) ? 2 : v.length ? 1 : 0;
}

function _applyBar(barId, hintId, v) {
  var s = _pwScore(v);
  var bar  = document.getElementById(barId);
  var hint = document.getElementById(hintId);
  var t    = LP_T[lpLang];
  bar.className  = ['psb','psb weak','psb medium','psb strong'][s];
  hint.textContent = ['', t.pwWeak, t.pwMedium, t.pwStrong][s];
  hint.style.color = ['','var(--re)','#F5A828','var(--gr)'][s];
}

function checkPwStrength(inp)  { _applyBar('pw-bar',    'pw-hint',    inp.value); }
function checkPwStrength2(inp) { _applyBar('inv-pw-bar','inv-pw-hint',inp.value); }

/* ══════════════════════════════════════════════════════════
   INVITE REGISTRATION
   ══════════════════════════════════════════════════════════ */
var _inviteData = null;

function checkInvite() {
  var email = document.getElementById('inv-email').value.trim();
  var errEl = document.getElementById('inv-err');
  var t     = LP_T[lpLang];
  errEl.textContent = ''; errEl.classList.remove('show');
  if (!email) { errEl.textContent = t.errEnterEmail; errEl.classList.add('show'); return; }
  fetch('/api/auth/check-invite', {
    method:'POST',
    headers:{
      'Content-Type':'application/json',
      'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
    },
    body: JSON.stringify({ email: email })
  })
  .then(function(res){ return res.json().then(function(d){ return {ok:res.ok, d:d}; }); })
  .then(function(r){
    if (!r.ok || !r.d.success) {
      errEl.textContent = r.d.message || t.errNotAuthorized;
      errEl.classList.add('show'); return;
    }
    _inviteData = Object.assign({ email: email }, r.d.invite);
    var branchInfo = document.getElementById('inv-branch-info');
    branchInfo.textContent = '✅ ' + t.invVerified +
      (r.d.invite.branch ? ' — ' + t.branch + ': ' + r.d.invite.branch.name_ar : '');
    document.getElementById('inv-step1').style.display = 'none';
    document.getElementById('inv-step2').style.display = '';
  })
  .catch(function(){ errEl.textContent = t.errRetry; errEl.classList.add('show'); });
}

function invBack() {
  document.getElementById('inv-step2').style.display = 'none';
  document.getElementById('inv-step1').style.display = '';
  _inviteData = null;
}

function submitInviteReg() {
  var name = document.getElementById('inv-name').value.trim();
  var pw   = document.getElementById('inv-pw').value;
  var pw2  = document.getElementById('inv-pw2').value;
  var err  = document.getElementById('inv-reg-err');
  var ok   = document.getElementById('inv-reg-ok');
  var btn  = document.getElementById('inv-submit-btn');
  var t    = LP_T[lpLang];
  err.textContent = ''; err.classList.remove('show'); ok.classList.remove('show');
  if (!name)         { err.textContent = t.errEnterName; err.classList.add('show'); return; }
  if (pw.length < 8) { err.textContent = t.errPwMin;     err.classList.add('show'); return; }
  if (pw !== pw2)    { err.textContent = t.errPwMatch;    err.classList.add('show'); return; }
  btn.disabled = true; btn.textContent = t.btnCreating;
  fetch('/api/auth/register-invite', {
    method:'POST',
    headers:{
      'Content-Type':'application/json',
      'X-CSRF-TOKEN':document.querySelector('meta[name=csrf-token]').content
    },
    body: JSON.stringify({
      name: name, email: _inviteData.email,
      password: pw, password_confirmation: pw2
    })
  })
  .then(function(res){ return res.json().then(function(d){ return {ok:res.ok, d:d}; }); })
  .then(function(r){
    if (!r.ok || !r.d.success) {
      var msg = r.d.errors
        ? Object.values(r.d.errors).reduce(function(a,b){ return a.concat(b); },[]).join(' ')
        : r.d.message;
      err.textContent = msg;
      err.classList.add('show');
      btn.disabled = false; btn.textContent = t.btnCreateAccount; return;
    }
    ok.textContent = t.okAccountCreated; ok.classList.add('show');
    setTimeout(function(){ switchTab('login', null); }, 1800);
  })
  .catch(function(){
    err.textContent = t.errRetry; err.classList.add('show');
    btn.disabled = false; btn.textContent = t.btnCreateAccount;
  });
}

/* ══════════════════════════════════════════════════════════
   FA CHECK — show register tab if needed
   ══════════════════════════════════════════════════════════ */
fetch('{{ route("auth.fa-check") }}')
  .then(function(r){ return r.json(); })
  .then(function(d){
    var regTab = document.getElementById('tab-btn-register');
    if (!regTab) return;
    if (d && !d.exists) {
      var banner = document.getElementById('first-time-banner');
      if (banner) banner.classList.add('show');
      var fa = document.getElementById('reg-fa');
      var inv = document.getElementById('reg-invite');
      if (fa)  fa.style.display  = '';
      if (inv) inv.style.display = 'none';
      regTab.style.display = '';
    } else if (d && d.invites_pending) {
      var fa2 = document.getElementById('reg-fa');
      var inv2 = document.getElementById('reg-invite');
      if (fa2)  fa2.style.display  = 'none';
      if (inv2) inv2.style.display = '';
      regTab.style.display = '';
    }
    /* Re-translate after updating tab visibility */
    lpApplyLang(lpLang);
  })
  .catch(function(){});

/* ══════════════════════════════════════════════════════════
   BILINGUAL DICTIONARY (AR ↔ EN)
   ══════════════════════════════════════════════════════════ */
var LP_T = {
  ar: {
    dir: 'rtl',
    /* Page */
    pageTitle: 'تسجيل الدخول — وفرة الخليجية',
    /* Company */
    nameFullHtml: 'وفرة <span style="color:var(--pri2)">الخليجية</span> للخدمات المالية',
    nameSubline:  'Wafra Gulf Financial Services',
    cardTitle:    'وفرة الخليجية للخدمات المالية',
    cardSub:      'Wafra Gulf Financial Services',
    /* Brand */
    status:   'النظام نشط ومتصل',
    headline: 'إدارة<br><em>كروت العمولات</em>',
    desc:     'نظام إدارة عمولات وفرة الخليجية للخدمات المالية — يدار حصراً من الإدارة المالية عبر صلاحيات متدرجة وآمنة.',
    b1:'تقارير ديناميكية فورية', b2:'صلاحيات متدرجة وآمنة',
    b3:'دعم متعدد الفروع',       b4:'دعم كامل للغتين',
    /* Banner */
    firstStrong: 'أول مرة؟',
    firstText:   'أنشئ حساب المدير المالي أولاً',
    /* Tabs */
    tabLogin: 'دخول', tabForgot: 'استعادة', tabRegister: '📝 تسجيل',
    /* Labels */
    labelEmail: 'البريد الإلكتروني', labelPassword: 'كلمة المرور',
    labelName: 'الاسم الكامل',       labelConfirm: 'تأكيد كلمة المرور',
    /* Placeholders */
    phName: 'الاسم الكامل', phInvName: 'اسمك الكامل',
    phMinPass: '8 أحرف على الأقل', phConfirmPass: 'أعد كتابة كلمة المرور',
    /* Buttons */
    btnLogin:         'دخول إلى النظام ←',
    btnReset:         'إرسال رابط الاستعادة ←',
    btnRegister:      'إنشاء الحساب 🚀',
    btnVerify:        'التحقق من الدعوة ←',
    btnCreateAccount: 'إنشاء حسابي 🚀',
    btnCreating:      'جاري الإنشاء...',
    /* Links */
    linkForgot:      'نسيت كلمة المرور؟',
    linkBack:        '← رجوع للدخول',
    linkChangeEmail: '← تغيير الإيميل',
    /* Info boxes */
    forgotInfo: 'سيتم إرسال رابط الاستعادة على إيميلك مباشرة',
    regInfo:    '🔐 <strong>لمرة واحدة فقط.</strong> بعد الإنشاء يختفي هذا الخيار نهائياً.',
    invInfo:    '📧 أدخل بريدك الإلكتروني للتحقق من دعوتك',
    /* Strength */
    pwWeak: '❌ ضعيفة', pwMedium: '⚠️ متوسطة', pwStrong: '✅ قوية',
    /* Errors */
    errEnterEmail:   'أدخل بريدك الإلكتروني',
    errNotAuthorized:'الإيميل غير مصرح بالتسجيل.',
    errEnterName:    'أدخل اسمك الكامل.',
    errPwMin:        'كلمة المرور 8 أحرف على الأقل.',
    errPwMatch:      'كلمتا المرور غير متطابقتين.',
    errRetry:        'حدث خطأ، حاول مجدداً.',
    okAccountCreated:'✅ تم إنشاء حسابك! جاري التحويل...',
    invVerified: 'مرحباً! تم التحقق', branch: 'فرع',
    /* Lang button */
    flag: '🇬🇧', langLbl: 'EN',
  },
  en: {
    dir: 'ltr',
    /* Page */
    pageTitle: 'Sign In — Wafra Gulf',
    /* Company */
    nameFullHtml: 'Wafra Gulf <span style="color:var(--pri2)">Financial</span> Services',
    nameSubline:  'وفرة الخليجية للخدمات المالية',
    cardTitle:    'Wafra Gulf Financial Services',
    cardSub:      'وفرة الخليجية للخدمات المالية',
    /* Brand */
    status:   'System Active & Connected',
    headline: 'Commission<br><em>Cards System</em>',
    desc:     'Wafra Gulf Financial Services — Commission cards management platform, exclusively operated by the Finance Department with role-based access.',
    b1:'Real-time Dynamic Reports', b2:'Role-based Secure Access',
    b3:'Multi-branch Support',      b4:'Arabic & English Support',
    /* Banner */
    firstStrong: 'First time?',
    firstText:   'Create the Finance Admin account first',
    /* Tabs */
    tabLogin: 'Login', tabForgot: 'Reset', tabRegister: '📝 Register',
    /* Labels */
    labelEmail: 'Email Address', labelPassword: 'Password',
    labelName: 'Full Name',      labelConfirm: 'Confirm Password',
    /* Placeholders */
    phName: 'Your full name', phInvName: 'Your full name',
    phMinPass: 'At least 8 characters', phConfirmPass: 'Re-enter your password',
    /* Buttons */
    btnLogin:         'Login to System →',
    btnReset:         'Send Reset Link →',
    btnRegister:      'Create Account 🚀',
    btnVerify:        'Verify Invitation →',
    btnCreateAccount: 'Create My Account 🚀',
    btnCreating:      'Creating...',
    /* Links */
    linkForgot:      'Forgot your password?',
    linkBack:        '→ Back to Login',
    linkChangeEmail: '→ Change Email',
    /* Info boxes */
    forgotInfo: 'A password reset link will be sent to your email address',
    regInfo:    '🔐 <strong>One-time only.</strong> This option disappears permanently after creation.',
    invInfo:    '📧 Enter your email address to verify your invitation',
    /* Strength */
    pwWeak: '❌ Weak', pwMedium: '⚠️ Medium', pwStrong: '✅ Strong',
    /* Errors */
    errEnterEmail:   'Please enter your email address',
    errNotAuthorized:'This email is not authorized to register.',
    errEnterName:    'Please enter your full name.',
    errPwMin:        'Password must be at least 8 characters.',
    errPwMatch:      'Passwords do not match.',
    errRetry:        'An error occurred, please try again.',
    okAccountCreated:'✅ Account created! Redirecting...',
    invVerified: 'Welcome! Verified', branch: 'Branch',
    /* Lang button */
    flag: '🇸🇦', langLbl: 'عر',
  }
};

/* ══════════════════════════════════════════════════════════
   LANGUAGE ENGINE
   ══════════════════════════════════════════════════════════ */
var lpLang = localStorage.getItem('wg_lang') || 'ar';

function lpApplyLang(lang) {
  if (!LP_T[lang]) lang = 'ar';
  var t   = LP_T[lang];
  var doc = document.documentElement;

  /* Direction + lang attribute */
  doc.setAttribute('lang', lang);
  doc.setAttribute('dir',  t.dir);
  document.title = t.pageTitle;

  /* Helper: set textContent */
  function set(id, val, asHtml) {
    var el = document.getElementById(id);
    if (!el) return;
    if (asHtml) el.innerHTML = val;
    else el.textContent = val;
  }

  /* Helper: set placeholder */
  function ph(id, val) {
    var el = document.getElementById(id);
    if (el) el.placeholder = val;
  }

  /* ── Company ── */
  set('lg-name-ar',     t.nameFullHtml, true);
  set('lg-name-subline',t.nameSubline);
  set('lp-card-title',  t.cardTitle);
  set('lp-card-sub',    t.cardSub);

  /* ── Brand ── */
  set('lp-status',  t.status);
  set('lp-headline',t.headline, true);
  set('lp-desc',    t.desc);
  set('lp-b1',t.b1); set('lp-b2',t.b2);
  set('lp-b3',t.b3); set('lp-b4',t.b4);

  /* ── Banner ── */
  set('lp-first-strong',t.firstStrong);
  set('lp-first-text',  t.firstText);

  /* ── Tabs ── */
  set('tab-btn-login', t.tabLogin);
  set('tab-btn-forgot',t.tabForgot);
  var regTab = document.getElementById('tab-btn-register');
  if (regTab && regTab.style.display !== 'none') set('tab-btn-register', t.tabRegister);

  /* ── Form labels (all tabs) ── */
  set('lp-label-email',    t.labelEmail);
  set('lp-label-password', t.labelPassword);
  set('lp-label-name-r',   t.labelName);
  set('lp-label-email-r',  t.labelEmail);
  set('lp-label-pw-r',     t.labelPassword);
  set('lp-label-confirm-r',t.labelConfirm);
  set('lp-label-email-i',  t.labelEmail);
  set('lp-label-name-i',   t.labelName);
  set('lp-label-pw-i',     t.labelPassword);
  set('lp-label-confirm-i',t.labelConfirm);
  set('lp-label-email-f',  t.labelEmail);

  /* ── Placeholders ── */
  ph('inp-name-r',  t.phName);
  ph('inv-name',    t.phInvName);
  ph('inv-pw',      t.phMinPass);
  ph('inv-pw2',     t.phConfirmPass);
  ph('inp-confirm-r',t.phConfirmPass);

  /* ── Buttons ── */
  set('lp-btn-login',    t.btnLogin);
  set('lp-btn-reset',    t.btnReset);
  set('lp-btn-register', t.btnRegister);
  set('lp-btn-verify',   t.btnVerify);
  set('inv-submit-btn',  t.btnCreateAccount);

  /* ── Links ── */
  set('lp-link-forgot',       t.linkForgot);
  set('lp-link-back-r',       t.linkBack);
  set('lp-link-back-i',       t.linkBack);
  set('lp-link-back-f',       t.linkBack);
  set('lp-link-change-email', t.linkChangeEmail);

  /* ── Info boxes ── */
  set('lp-forgot-info', t.forgotInfo);
  set('lp-reg-info',    t.regInfo, true);
  set('lp-inv-info',    t.invInfo);

  /* ── Lang button ── */
  set('lp-lang-flag',t.flag);
  set('lp-lang-lbl', t.langLbl);

  /* Persist */
  localStorage.setItem('wg_lang', lang);
  lpLang = lang;
}

function lpToggleLang() {
  lpApplyLang(lpLang === 'ar' ? 'en' : 'ar');
}

/* ── Apply immediately (script at end of body, DOM is ready) ── */
lpApplyLang(lpLang);

/* ══════════════════════════════════════════════════════════
   SECURITY: Clear any saved session / token on login page load
   Prevents auto-login after logout (another person picks up device)
   ══════════════════════════════════════════════════════════ */
(function(){
  /* Always wipe the API token — if user got here they need to re-auth */
  localStorage.removeItem('wg_token');
  sessionStorage.removeItem('wfr_shown');

  /* Force the login form fields blank so browser-filled values are erased */
  var emailEl = document.getElementById('inp-email');
  var pwEl    = document.getElementById('pw');
  if (emailEl) { emailEl.value = ''; emailEl.setAttribute('autocomplete','off'); }
  if (pwEl)    { pwEl.value    = ''; }

  /* Extra: briefly set type=text then back to password to defeat browser autofill timing */
  if (pwEl) {
    pwEl.setAttribute('autocomplete', 'new-password');
    setTimeout(function(){
      pwEl.value = '';
    }, 150);
  }
})();
</script>
</body>
</html>
