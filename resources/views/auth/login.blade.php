<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>تسجيل الدخول — وفرة الخليجية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--pri:#2E86AB;--pri2:#3A9DB5;--pri3:#1A5F7A;--bg:#0A1628;--bg2:#142240;--bg3:#1A2B4E;--brd1:#253A63;--tx:#EDF4F8;--mu:#5A7A9A;--gr:#22C97A;--re:#E05050;}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Tajawal',sans-serif;background:var(--bg);color:var(--tx);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
body::before{content:'';position:fixed;inset:0;pointer-events:none;background:radial-gradient(ellipse 900px 500px at 70% -10%,rgba(46,134,171,.1),transparent 55%)}

.login-wrap{display:flex;gap:54px;align-items:center;max-width:960px;width:100%}

/* Brand */
.brand{flex:1}
.brand-logo-row{display:flex;align-items:center;gap:16px;margin-bottom:24px}
.brand-logo-row svg{flex-shrink:0}
.brand-name{font-size:1.8rem;font-weight:900;color:var(--tx)}
.brand-name span{color:var(--pri2)}
.brand-sub{font-size:12px;color:var(--mu);margin-top:3px}
.brand-pill{display:inline-flex;align-items:center;gap:7px;background:rgba(46,134,171,.1);
  border:1px solid rgba(46,134,171,.3);border-radius:30px;padding:5px 14px;
  font-size:11px;color:var(--pri2);font-weight:700;margin-bottom:20px}
.brand-dot{width:6px;height:6px;background:var(--gr);border-radius:50%;animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}
.brand-heading{font-size:2.8rem;font-weight:900;line-height:1.05;letter-spacing:-1.5px;margin-bottom:14px}
.brand-heading em{font-style:normal;color:var(--pri2)}
.brand-desc{font-size:.88rem;color:var(--mu);line-height:1.75;margin-bottom:24px}
.brand-features{display:flex;flex-direction:column;gap:10px;margin-top:4px}
.feat-item{background:rgba(46,134,171,.08);border:1px solid rgba(46,134,171,.18);border-radius:10px;padding:10px 16px;font-size:13px;color:var(--tx);font-weight:600}

/* Card */
.login-card{background:var(--bg2);border:1px solid var(--brd1);border-radius:22px;
  padding:36px 32px;width:400px;flex-shrink:0;
  box-shadow:0 40px 90px rgba(0,0,0,.6);animation:cardUp .5s cubic-bezier(.16,1,.3,1)}
@keyframes cardUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
/* card-logo replaced with inline SVG globe */
.card-title{text-align:center;font-size:1rem;font-weight:800;margin-bottom:3px}
.card-sub{text-align:center;font-size:11px;color:var(--mu);margin-bottom:22px}

/* First-time banner */
.first-time-banner{background:rgba(34,201,122,.15);border:2px solid rgba(34,201,122,.5);
  border-radius:11px;padding:13px 16px;font-size:13px;color:#E8FFF2;
  line-height:1.7;margin-bottom:15px;text-align:center;font-weight:600;display:none}
.first-time-banner strong{color:#FFFDE7;font-weight:900}
.first-time-banner.show{display:block}

/* Tabs */
.tabs{display:flex;background:var(--bg3);border-radius:10px;padding:3px;margin-bottom:18px}
.tab{flex:1;padding:7px;border:none;background:none;color:var(--mu);
  font-family:'Tajawal',sans-serif;font-size:12px;font-weight:600;cursor:pointer;border-radius:8px}
.tab.active{background:var(--bg2);color:var(--pri2)}

/* Form */
.form-group{margin-bottom:12px}
.form-label{display:block;font-size:10px;color:var(--mu);text-transform:uppercase;letter-spacing:.4px;margin-bottom:6px}
.form-input{width:100%;background:var(--bg3);border:1px solid var(--brd1);border-radius:10px;
  padding:11px 13px;color:var(--tx);font-family:'Tajawal',sans-serif;font-size:14px;outline:none}
.form-input:focus{border-color:var(--pri)}
.pw-wrap{position:relative}.pw-wrap .form-input{padding-left:38px}
.pw-toggle{position:absolute;left:11px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--mu);cursor:pointer;font-size:14px}
.psb{height:3px;border-radius:2px;background:var(--brd1);margin-top:4px}
.psb.strong{background:var(--gr);width:100%}.psb.medium{background:var(--or);width:66%}.psb.weak{background:var(--re);width:30%}
.err-box{background:rgba(224,80,80,.08);border:1px solid rgba(224,80,80,.22);
  border-radius:8px;padding:9px 12px;font-size:12px;color:var(--re);margin-bottom:10px;display:none}
.err-box.show{display:block}
.ok-box{background:rgba(34,201,122,.08);border:1px solid rgba(34,201,122,.22);
  border-radius:8px;padding:9px 12px;font-size:12px;color:var(--gr);margin-bottom:10px;display:none}
.ok-box.show{display:block}
.btn-login{width:100%;padding:12px;background:linear-gradient(135deg,var(--pri2),var(--pri),var(--pri3));
  border:none;border-radius:10px;color:white;font-family:'Tajawal',sans-serif;
  font-size:14px;font-weight:800;cursor:pointer;box-shadow:0 5px 18px rgba(46,134,171,.35)}
.btn-login:hover{transform:translateY(-2px)}
.link-small{text-align:center;margin-top:10px;font-size:11px;color:var(--mu);cursor:pointer}
.link-small:hover{color:var(--pri2)}
.info-box{background:rgba(46,134,171,.08);border:1px solid rgba(46,134,171,.2);
  border-radius:9px;padding:10px 12px;font-size:11px;color:var(--mu);line-height:1.7;margin-bottom:12px}
.info-box strong{color:var(--pri2)}

@media(max-width:700px){.brand{display:none}.login-card{width:100%}}
</style>
</head>
<body>

{{-- Splash (standalone page — inline version) --}}
<div id="wfr-splash" aria-hidden="true">
  <div id="wfr-splash-inner">
    @include('partials.globe', ['size'=>'xl', 'showText'=>true, 'gid'=>'lsplash'])
    <div id="wfr-splash-dots"><span></span><span></span><span></span></div>
  </div>
</div>
<style>
#wfr-splash{position:fixed;inset:0;z-index:99999;background:linear-gradient(145deg,#060D1B,#0C1830,#0E2040);display:flex;align-items:center;justify-content:center;animation:wfr-splash-out .4s ease-in 2.2s forwards;pointer-events:all}
#wfr-splash-inner{display:flex;flex-direction:column;align-items:center;gap:18px;animation:wfr-splash-in .5s cubic-bezier(.34,1.56,.64,1) both}
@keyframes wfr-splash-in{from{transform:scale(.85);opacity:0}to{transform:scale(1);opacity:1}}
@keyframes wfr-splash-out{to{opacity:0;visibility:hidden;pointer-events:none}}
#wfr-splash-dots{display:flex;gap:7px;margin-top:4px}
#wfr-splash-dots span{width:7px;height:7px;border-radius:50%;background:rgba(90,205,230,.55);animation:wfr-dot-pulse .7s ease-in-out infinite alternate}
#wfr-splash-dots span:nth-child(2){animation-delay:.22s}
#wfr-splash-dots span:nth-child(3){animation-delay:.44s}
@keyframes wfr-dot-pulse{from{opacity:.2;transform:scale(.7)}to{opacity:1;transform:scale(1.1)}}
body.wfr-loading{overflow:hidden}
</style>
<script>
document.body.classList.add('wfr-loading');
var sp=document.getElementById('wfr-splash');
if(sp){sp.addEventListener('animationend',function(e){if(e.animationName==='wfr-splash-out'){sp.remove();document.body.classList.remove('wfr-loading');}});}
</script>

<div class="login-wrap">

  <!-- Brand Left -->
  <div class="brand">
    <div class="brand-logo-row">
      @include('partials.globe', ['size'=>'lg', 'showText'=>false, 'gid'=>'brand',
        'style'=>'border-radius:18px;background:white;padding:9px;box-shadow:0 14px 44px rgba(46,134,171,.4)'])
      <div>
        <div class="brand-name">وفرة <span>الخليجية</span></div>
        <div class="brand-sub">للخدمات المالية · Financial Services</div>
      </div>
    </div>
    <div class="brand-pill"><span class="brand-dot"></span> النظام نشط ومتصل</div>
    <div class="brand-heading">كروت<br><em>العمولات</em></div>
    <div class="brand-desc">نظام إدارة عمولات وفرة الخليجية — يدار حصراً من الإدارة المالية للشركة.</div>
    <div class="brand-features">
      <div class="feat-item">✅ إدارة كروت العمولات بسهولة</div>
      <div class="feat-item">📊 تقارير ديناميكية فورية</div>
      <div class="feat-item">🔒 صلاحيات متدرجة وآمنة</div>
      <div class="feat-item">🌐 دعم كامل للغة العربية</div>
    </div>
  </div>

  <!-- Login Card Right -->
  <div class="login-card">
    <div style="margin:0 auto 14px;display:block;width:fit-content">
      @include('partials.globe', ['size'=>'md', 'showText'=>false, 'gid'=>'card',
        'style'=>'border-radius:18px;background:white;padding:9px;box-shadow:0 8px 24px rgba(46,134,171,.3)'])
    </div>
    <div class="card-title">وفرة الخليجية</div>
    <div class="card-sub">Commission Cards · بوابة الدخول</div>

    <!-- First time banner -->
    <div class="first-time-banner" id="first-time-banner">
      🎉 <strong>أول مرة؟</strong> أنشئ حساب المدير المالي أولاً
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab active" onclick="switchTab('login', this)">دخول</button>
      <button class="tab" id="register-tab" onclick="switchTab('register', this)" style="display:none">📝 تسجيل</button>
      <button class="tab" onclick="switchTab('forgot', this)">استعادة</button>
    </div>

    <!-- Login form -->
    <div id="tab-login">
      @if(session('error'))
        <div class="err-box show">{{ session('error') }}</div>
      @endif
      <form method="POST" action="{{ route('auth.login.submit') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني</label>
          <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="your@wafragulf.com" required autocomplete="email">
          @error('email')<div style="color:var(--re);font-size:11px;margin-top:4px">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label">كلمة المرور</label>
          <div class="pw-wrap">
            <input class="form-input" type="password" name="password" id="pw" required autocomplete="current-password">
            <button type="button" class="pw-toggle" onclick="togglePw('pw')">👁</button>
          </div>
        </div>
        <button type="submit" class="btn-login">دخول إلى النظام ←</button>
      </form>
      <div class="link-small" onclick="switchTab('forgot', null)">نسيت كلمة المرور؟</div>
    </div>

    <!-- Register form (FA first time) -->
    <div id="tab-register" style="display:none">
      <div class="info-box">🔐 <strong>لمرة واحدة فقط.</strong> بعد الإنشاء يختفي هذا الخيار نهائياً.</div>
      <form method="POST" action="{{ route('auth.register.submit') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">الاسم الكامل</label>
          <input class="form-input" type="text" name="name" value="{{ old('name') }}" placeholder="الاسم الكامل" required>
        </div>
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني</label>
          <input class="form-input" type="email" name="email" value="{{ old('email') }}" placeholder="your@wafragulf.com" required>
        </div>
        <div class="form-group">
          <label class="form-label">كلمة المرور</label>
          <div class="pw-wrap">
            <input class="form-input" type="password" name="password" id="rpw" required oninput="checkPwStrength(this)">
            <button type="button" class="pw-toggle" onclick="togglePw('rpw')">👁</button>
          </div>
          <div class="psb" id="pw-bar"></div>
          <span id="pw-hint" style="font-size:9px;color:var(--gr)">✅ كلمة مرور قوية</span>
        </div>
        <div class="form-group">
          <label class="form-label">تأكيد كلمة المرور</label>
          <div class="pw-wrap">
            <input class="form-input" type="password" name="password_confirmation" required>
          </div>
        </div>
        @if($errors->any())
          <div class="err-box show">{{ $errors->first() }}</div>
        @endif
        <button type="submit" class="btn-login">إنشاء الحساب 🚀</button>
        <div class="link-small" onclick="switchTab('login',null)">← رجوع للدخول</div>
      </form>
    </div>

    <!-- Forgot Password -->
    <div id="tab-forgot" style="display:none">
      <div class="info-box">سيتم إرسال رابط الاستعادة على إيميلك مباشرة</div>
      <form method="POST" action="{{ route('auth.password.email') }}">
        @csrf
        <div class="form-group">
          <label class="form-label">البريد الإلكتروني</label>
          <input class="form-input" type="email" name="email" placeholder="your@wafragulf.com" required>
        </div>
        @if(session('status'))
          <div class="ok-box show">✅ {{ session('status') }}</div>
        @endif
        <button type="submit" class="btn-login">إرسال رابط الاستعادة ←</button>
        <div class="link-small" onclick="switchTab('login',null)">← رجوع للدخول</div>
      </form>
    </div>
  </div>
</div>

<script>
// Show register tab if no FA exists
fetch('{{ route("auth.fa-check") }}')
  .then(r => r.json())
  .then(d => {
    if (!d.exists) {
      document.getElementById('first-time-banner').classList.add('show');
      document.getElementById('register-tab').style.display = '';
    }
  }).catch(() => {});

function switchTab(name, btn) {
  ['login','register','forgot'].forEach(t => document.getElementById('tab-'+t).style.display = 'none');
  document.getElementById('tab-'+name).style.display = 'block';
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');
}

function togglePw(id) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
}

function checkPwStrength(inp) {
  const v = inp.value;
  const bar = document.getElementById('pw-bar');
  const hint = document.getElementById('pw-hint');
  if (!v) { bar.className = 'psb'; hint.textContent = ''; return; }
  const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#!$%]).{8,}$/.test(v);
  const medium = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/.test(v);
  if (strong)      { bar.className = 'psb strong'; hint.style.color='var(--gr)'; hint.textContent = '✅ قوية'; }
  else if (medium) { bar.className = 'psb medium'; hint.style.color='var(--or)'; hint.textContent = '⚠️ متوسطة'; }
  else             { bar.className = 'psb weak';   hint.style.color='var(--re)'; hint.textContent = '❌ ضعيفة'; }
}
</script>
</body>
</html>
