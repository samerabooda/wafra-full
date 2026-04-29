<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,viewport-fit=cover">
<meta name="theme-color" content="#0C1420">
<title>تسجيل الدخول — وفرة الخليجية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;600;700;800;900&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{
  --bg:#0C1420; --bg1:#111A2B; --bg2:#162034; --bg3:#1C2940;
  --tx:#E8EEF5; --tx2:#B8C8D8; --mu:#6B849E; --mu2:#4A6480;
  --teal:#14B87E; --teal2:#0E9469; --teal3:#0B7556;
  --pri:#2A82A8; --pri2:#37A0CC;
  --brd1:rgba(255,255,255,.07); --brd2:rgba(255,255,255,.13);
  --re:#E04848; --gr:#1DC87A; --or:#F59820;
  --inp:#1C2940;
}
html,body{height:100%}
body{
  font-family:'Tajawal',sans-serif;
  background:var(--bg);color:var(--tx);
  min-height:100dvh;display:flex;align-items:stretch;
  overflow:hidden;position:relative;
}

/* ── Atmospheric background ── */
body::before{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background:
    radial-gradient(ellipse 700px 600px at 0% 50%, rgba(20,184,126,.08) 0%, transparent 60%),
    radial-gradient(ellipse 600px 500px at 100% 0%, rgba(42,130,168,.1) 0%, transparent 55%),
    radial-gradient(ellipse 400px 400px at 100% 100%, rgba(20,184,126,.05) 0%, transparent 50%);
}
/* subtle grid */
body::after{
  content:'';position:fixed;inset:0;pointer-events:none;z-index:0;
  background-image:
    linear-gradient(rgba(255,255,255,.015) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.015) 1px,transparent 1px);
  background-size:60px 60px;
}

/* ══ LEFT PANEL — Branding ══════════════════════════════════ */
.brand-panel{
  flex:1.1;display:flex;flex-direction:column;justify-content:center;
  padding:60px 56px;position:relative;z-index:1;
}

/* Logo */
.logo-wrap{display:flex;align-items:center;gap:20px;margin-bottom:48px}
.logo-box{
  width:96px;height:96px;flex-shrink:0;
  filter:drop-shadow(0 8px 24px rgba(20,184,126,.4));
  transition:transform .3s;
}
.logo-box:hover{transform:translateY(-3px) scale(1.04)}
.logo-box svg{width:100%;height:100%}
.logo-text{}
.logo-name{
  font-size:26px;font-weight:900;color:var(--tx);
  letter-spacing:-.5px;line-height:1.1;
}
.logo-name span{color:var(--teal)}
.logo-en{font-size:13px;color:var(--mu);margin-top:3px;letter-spacing:.5px}

/* Brand copy */
.brand-tagline{
  display:inline-flex;align-items:center;gap:8px;
  background:rgba(20,184,126,.1);border:1px solid rgba(20,184,126,.2);
  border-radius:20px;padding:5px 14px;font-size:11px;font-weight:700;
  color:var(--teal);letter-spacing:.3px;margin-bottom:28px;
}
.brand-tagline::before{
  content:'';width:7px;height:7px;border-radius:50%;
  background:var(--teal);animation:pulse 2s infinite;flex-shrink:0;
}
@keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:.5;transform:scale(.8)}}

.brand-headline{
  font-size:3.2rem;font-weight:900;line-height:1.05;
  letter-spacing:-2px;margin-bottom:20px;color:var(--tx);
}
.brand-headline em{
  font-style:normal;
  background:linear-gradient(135deg,var(--teal),var(--pri2));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.brand-desc{
  font-size:.92rem;color:var(--mu);line-height:1.8;
  max-width:400px;margin-bottom:40px;
}

/* Stats */
.stats{display:flex;gap:12px;flex-wrap:wrap}
.stat{
  flex:1;min-width:100px;
  background:var(--bg2);border:1px solid var(--brd1);border-radius:14px;
  padding:16px 18px;text-align:center;
  transition:transform .2s,border-color .2s;
}
.stat:hover{transform:translateY(-2px);border-color:rgba(20,184,126,.2)}
.stat-val{
  font-size:1.6rem;font-weight:900;color:var(--teal);
  font-family:'JetBrains Mono',monospace;line-height:1;margin-bottom:4px;
}
.stat-lbl{font-size:10px;color:var(--mu);text-transform:uppercase;letter-spacing:.5px}

/* ══ RIGHT PANEL — Login form ═══════════════════════════════ */
.form-panel{
  width:440px;flex-shrink:0;display:flex;align-items:center;justify-content:center;
  padding:32px 40px;position:relative;z-index:1;
  background:rgba(17,26,43,.7);backdrop-filter:blur(24px);
  border-right:1px solid var(--brd1);
}

.login-card{width:100%;max-width:360px}

/* Card logo */
.card-logo-wrap{text-align:center;margin-bottom:28px}
.card-logo{
  width:80px;height:80px;margin:0 auto 14px;
  filter:drop-shadow(0 6px 20px rgba(20,184,126,.5));
}
.card-logo svg{width:100%;height:100%}
.card-title{font-size:22px;font-weight:800;color:var(--tx);margin-bottom:4px}
.card-sub{font-size:12px;color:var(--mu)}

/* Tabs */
.tabs{display:flex;border-bottom:1px solid var(--brd1);margin-bottom:24px;margin-top:24px}
.tab-btn{
  flex:1;padding:10px;font-size:12px;font-weight:700;
  background:none;border:none;color:var(--mu);cursor:pointer;
  border-bottom:2px solid transparent;font-family:'Tajawal',sans-serif;
  transition:all .2s;
}
.tab-btn.on{color:var(--teal);border-bottom-color:var(--teal)}

/* Form */
.form-group{margin-bottom:16px}
.form-label{
  display:block;font-size:11px;font-weight:600;
  color:var(--mu2);margin-bottom:7px;letter-spacing:.3px;
}
.form-input{
  width:100%;background:var(--inp);border:1px solid var(--brd2);
  border-radius:10px;padding:11px 14px;color:var(--tx);
  font-size:16px;font-family:'Tajawal',sans-serif;
  transition:border .2s,box-shadow .2s;
}
.form-input:focus{
  outline:none;border-color:var(--teal);
  box-shadow:0 0 0 3px rgba(20,184,126,.15);
}
.form-input::placeholder{color:var(--mu2)}

/* Password eye */
.pw-wrap{position:relative}
.pw-eye{
  position:absolute;left:12px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--mu);cursor:pointer;font-size:15px;
  padding:4px;transition:color .15s;
}
.pw-eye:hover{color:var(--tx)}

/* Submit */
.btn-submit{
  width:100%;padding:13px;border-radius:10px;
  background:linear-gradient(135deg,var(--teal),var(--teal2));
  color:white;font-size:14px;font-weight:800;
  letter-spacing:.3px;cursor:pointer;border:none;
  font-family:'Tajawal',sans-serif;
  box-shadow:0 4px 20px rgba(20,184,126,.3);
  transition:all .2s;margin-top:4px;
}
.btn-submit:hover{
  transform:translateY(-1px);
  box-shadow:0 6px 28px rgba(20,184,126,.45);
}
.btn-submit:active{transform:translateY(0)}
.btn-submit:disabled{opacity:.5;cursor:default;transform:none}

/* Alerts */
.err-box{
  padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:14px;
  background:rgba(224,72,72,.1);border:1px solid rgba(224,72,72,.25);color:var(--re);
  display:none;
}
.err-box.show{display:block}
.ok-box{
  padding:10px 14px;border-radius:8px;font-size:12px;margin-bottom:14px;
  background:rgba(20,184,126,.1);border:1px solid rgba(20,184,126,.25);color:var(--teal);
  display:none;
}
.ok-box.show{display:block}
.info-box{
  padding:9px 12px;border-radius:8px;font-size:11px;margin-bottom:14px;
  background:rgba(42,130,168,.1);border:1px solid rgba(42,130,168,.2);color:var(--pri2);
}

/* Forgot link */
.forgot-link{font-size:11px;color:var(--mu);text-align:center;margin-top:16px;cursor:pointer}
.forgot-link:hover{color:var(--teal)}

/* Tab pane */
.tab-pane{display:none}.tab-pane.on{display:block}

/* ── Mobile ── */
@media(max-width:820px){
  body{flex-direction:column;overflow-y:auto}
  .brand-panel{
    flex:none;padding:40px 28px 32px;
    border-bottom:1px solid var(--brd1);
  }
  .logo-box{width:72px;height:72px}
  .logo-name{font-size:20px}
  .brand-headline{font-size:2.2rem;letter-spacing:-1px}
  .stats{gap:8px}
  .stat{padding:12px 10px}
  .form-panel{
    width:100%;padding:28px 24px 40px;
    background:transparent;backdrop-filter:none;border-right:none;
  }
  .login-card{max-width:100%}
  .card-logo-wrap{display:none}
}
@media(max-width:400px){
  .brand-panel{padding:28px 20px}
  .brand-headline{font-size:1.8rem}
  .form-panel{padding:20px 16px 36px}
}
</style>
</head>
<body>

<!-- ══ BRAND PANEL ══════════════════════════════════════════ -->
<div class="brand-panel">
  <div class="logo-wrap">
    <div class="logo-box">
      {!! file_get_contents(public_path('logo_3d.svg')) !!}
    </div>
    <div class="logo-text">
      <div class="logo-name">وفرة <span>الخليجية</span></div>
      <div class="logo-en">WAFRA GULF · Financial Services</div>
    </div>
  </div>

  <div class="brand-tagline">نظام كروت العمولات · Commission Cards System</div>

  <h1 class="brand-headline">
    إدارة<br>العمولات<br><em>بذكاء</em>
  </h1>

  <p class="brand-desc">
    منصة متكاملة لإدارة عمولات البروكرين والمسوّقين والفروع —
    آمنة، سريعة، ثنائية اللغة
  </p>

  <div class="stats">
    <div class="stat">
      <div class="stat-val">10+</div>
      <div class="stat-lbl">فروع</div>
    </div>
    <div class="stat">
      <div class="stat-val">8</div>
      <div class="stat-lbl">سيناريوهات</div>
    </div>
    <div class="stat">
      <div class="stat-val">∞</div>
      <div class="stat-lbl">كروت</div>
    </div>
  </div>
</div>

<!-- ══ FORM PANEL ════════════════════════════════════════════ -->
<div class="form-panel">
  <div class="login-card">

    <div class="card-logo-wrap">
      <div class="card-logo">
        {!! file_get_contents(public_path('logo_3d.svg')) !!}
      </div>
      <div class="card-title">أهلاً بك</div>
      <div class="card-sub">سجّل الدخول للمتابعة / Sign in to continue</div>
    </div>

    {{-- Tabs --}}
    <div class="tabs">
      <button class="tab-btn on" onclick="showTab('login',this)">
        🔐 تسجيل الدخول
      </button>
      <button class="tab-btn" onclick="showTab('forgot',this)">
        🔑 نسيت كلمة المرور
      </button>
    </div>

    {{-- ── Login Tab ── --}}
    <div class="tab-pane on" id="tab-login">

      @if(session('error'))
      <div class="err-box show">{{ session('error') }}</div>
      @endif
      @if(session('success'))
      <div class="ok-box show">✅ {{ session('success') }}</div>
      @endif

      <div id="login-err" class="err-box"></div>

      <form method="POST" action="{{ route('auth.login.submit') }}" id="login-form">
        @csrf
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني / Email</label>
          <input class="form-input" type="email" name="email"
                 value="{{ old('email') }}"
                 placeholder="your@wafragulf.com"
                 autocomplete="email" enterkeyhint="next"
                 autofocus required>
        </div>

        <div class="form-group">
          <label class="form-label">كلمة المرور / Password</label>
          <div class="pw-wrap">
            <input class="form-input" type="password" id="pw-main"
                   name="password" placeholder="••••••••"
                   autocomplete="current-password" enterkeyhint="go"
                   required>
            <button type="button" class="pw-eye" onclick="togglePw('pw-main',this)">👁</button>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          دخول / Sign In →
        </button>
      </form>

      <div class="forgot-link" onclick="showTab('forgot',null)">
        نسيت كلمة المرور؟
      </div>
    </div>

    {{-- ── Forgot Password Tab ── --}}
    <div class="tab-pane" id="tab-forgot">

      @if(session('reset_sent'))
      <div class="ok-box show">✅ {{ session('reset_sent') }}</div>
      @else
      <div class="info-box">
        أدخل بريدك الإلكتروني وسيصلك رابط إعادة تعيين كلمة المرور
      </div>
      @endif

      @if($errors->any())
      <div class="err-box show">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('auth.password.email') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني / Email</label>
          <input class="form-input" type="email" name="email"
                 placeholder="your@wafragulf.com"
                 autocomplete="email" enterkeyhint="send" required>
        </div>
        <button type="submit" class="btn-submit">
          إرسال رابط الاستعادة →
        </button>
      </form>

      <div class="forgot-link" onclick="showTab('login',null)">
        ← العودة لتسجيل الدخول
      </div>
    </div>

  </div>
</div>

<script>
function showTab(id, btn) {
  document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('on'));
  document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('on'));
  document.getElementById('tab-' + id).classList.add('on');
  if (btn) btn.classList.add('on');
  else {
    const idx = id === 'login' ? 0 : 1;
    document.querySelectorAll('.tab-btn')[idx]?.classList.add('on');
  }
}
function togglePw(id, btn) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
  btn.textContent = el.type === 'password' ? '👁' : '🙈';
}
</script>
</body>
</html>
