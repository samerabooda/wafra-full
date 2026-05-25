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
    @include('partials.globe', ['size'=>'xl', 'showText'=>true, 'gid'=>'lsplash', 'whiteBg'=>false, 'darkCtx'=>true])
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
      @include('partials.globe', ['size'=>'lg', 'showText'=>false, 'gid'=>'brand', 'whiteBg'=>true])
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
      @include('partials.globe', ['size'=>'md', 'showText'=>false, 'gid'=>'card', 'whiteBg'=>true])
    </div>
    <div class="card-title">وفرة الخليجية</div>
    <div class="card-sub">Commission Cards · بوابة الدخول</div>

    <!-- First time banner -->
    <div class="first-time-banner" id="first-time-banner">
      🎉 <strong>أول مرة؟</strong> أنشئ حساب المدير المالي أولاً
    </div>

    <!-- Tabs -->
    <div class="tabs">
      <button class="tab active" id="tab-btn-login"    onclick="switchTab('login',this)">دخول</button>
      <button class="tab"        id="tab-btn-register" onclick="switchTab('register',this)" style="display:none">📝 تسجيل</button>
      <button class="tab"        id="tab-btn-forgot"   onclick="switchTab('forgot',this)">استعادة</button>
    </div>

    <!-- ── Login form ─────────────────────────────────────── -->
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
      <div class="link-small" onclick="switchTab('forgot',null)">نسيت كلمة المرور؟</div>
    </div>

    <!-- ── Register tab — FA first-time OR invited manager ── -->
    <div id="tab-register" style="display:none">

      {{-- ── FA first-time registration (hidden after FA exists) ── --}}
      <div id="reg-fa">
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
            <span id="pw-hint" style="font-size:9px;color:var(--gr)"></span>
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

      {{-- ── Invited manager registration (shown when FA exists + invites pending) ── --}}
      <div id="reg-invite" style="display:none">

        {{-- Step 1: email check --}}
        <div id="inv-step1">
          <div class="info-box">📧 أدخل بريدك الإلكتروني للتحقق من دعوتك</div>
          <div class="form-group">
            <label class="form-label">البريد الإلكتروني</label>
            <input class="form-input" type="email" id="inv-email" placeholder="your@wafragulf.com" autocomplete="email">
          </div>
          <div class="err-box" id="inv-err"></div>
          <button type="button" class="btn-login" onclick="checkInvite()">التحقق من الدعوة ←</button>
          <div class="link-small" onclick="switchTab('login',null)">← رجوع للدخول</div>
        </div>

        {{-- Step 2: fill details (shown after successful invite check) --}}
        <div id="inv-step2" style="display:none">
          <div class="ok-box show" id="inv-branch-info" style="margin-bottom:12px"></div>
          <div class="form-group">
            <label class="form-label">الاسم الكامل</label>
            <input class="form-input" type="text" id="inv-name" placeholder="اسمك الكامل">
          </div>
          <div class="form-group">
            <label class="form-label">كلمة المرور</label>
            <div class="pw-wrap">
              <input class="form-input" type="password" id="inv-pw" placeholder="8 أحرف على الأقل" oninput="checkPwStrength2(this)">
              <button type="button" class="pw-toggle" onclick="togglePw('inv-pw')">👁</button>
            </div>
            <div class="psb" id="inv-pw-bar"></div>
            <span id="inv-pw-hint" style="font-size:9px"></span>
          </div>
          <div class="form-group">
            <label class="form-label">تأكيد كلمة المرور</label>
            <div class="pw-wrap">
              <input class="form-input" type="password" id="inv-pw2" placeholder="أعد كتابة كلمة المرور">
              <button type="button" class="pw-toggle" onclick="togglePw('inv-pw2')">👁</button>
            </div>
          </div>
          <div class="err-box" id="inv-reg-err"></div>
          <div class="ok-box" id="inv-reg-ok"></div>
          <button type="button" class="btn-login" id="inv-submit-btn" onclick="submitInviteReg()">إنشاء حسابي 🚀</button>
          <div class="link-small" onclick="invBack()">← تغيير الإيميل</div>
        </div>
      </div>
    </div>

    <!-- ── Forgot Password ─────────────────────────────────── -->
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
// ── Show correct register form based on FA/invite status ──────
fetch('{{ route("auth.fa-check") }}')
  .then(r => r.json())
  .then(d => {
    const regTab = document.getElementById('tab-btn-register');
    if (!d.exists) {
      // First-time: show FA register
      document.getElementById('first-time-banner').classList.add('show');
      document.getElementById('reg-fa').style.display = '';
      document.getElementById('reg-invite').style.display = 'none';
      regTab.style.display = '';
    } else if (d.invites_pending) {
      // Invited managers waiting to register
      document.getElementById('reg-fa').style.display = 'none';
      document.getElementById('reg-invite').style.display = '';
      regTab.textContent = '📝 تسجيل';
      regTab.style.display = '';
    }
    // If FA exists and no pending invites, register tab stays hidden
  }).catch(() => {});

// ── Tab switching ─────────────────────────────────────────────
function switchTab(name, btn) {
  ['login','register','forgot'].forEach(t => document.getElementById('tab-'+t).style.display='none');
  document.getElementById('tab-'+name).style.display = 'block';
  document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
  if (btn) btn.classList.add('active');
  else document.getElementById('tab-btn-'+name)?.classList.add('active');
}

// ── Password helpers ──────────────────────────────────────────
function togglePw(id) {
  const i = document.getElementById(id);
  i.type = i.type==='password' ? 'text' : 'password';
}
function _pwScore(v) {
  const strong = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@#!$%]).{8,}$/.test(v);
  const medium = /^(?=.*[a-zA-Z])(?=.*\d).{6,}$/.test(v);
  return strong ? 3 : medium ? 2 : v.length ? 1 : 0;
}
function _applyPwBar(barId, hintId, v) {
  const bar = document.getElementById(barId), hint = document.getElementById(hintId);
  const s = _pwScore(v);
  const cls = ['psb','psb weak','psb medium','psb strong'];
  const lbl = ['','❌ ضعيفة','⚠️ متوسطة','✅ قوية'];
  const clr = ['','var(--re)','#f5a623','var(--gr)'];
  bar.className = cls[s]; hint.textContent = lbl[s]; hint.style.color = clr[s];
}
function checkPwStrength(inp)  { _applyPwBar('pw-bar',    'pw-hint',    inp.value); }
function checkPwStrength2(inp) { _applyPwBar('inv-pw-bar','inv-pw-hint',inp.value); }

// ── Invite check ──────────────────────────────────────────────
let _inviteData = null;
async function checkInvite() {
  const email = document.getElementById('inv-email').value.trim();
  const errEl = document.getElementById('inv-err');
  errEl.textContent = ''; errEl.classList.remove('show');
  if (!email) { errEl.textContent = 'أدخل بريدك الإلكتروني'; errEl.classList.add('show'); return; }

  try {
    const res = await fetch('/api/auth/check-invite', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
      body: JSON.stringify({ email }),
    });
    const d = await res.json();
    if (!res.ok || !d.success) {
      errEl.textContent = d.message || 'الإيميل غير مصرح بالتسجيل.';
      errEl.classList.add('show');
      return;
    }
    _inviteData = { email, ...d.invite };
    const branchName = d.invite.branch ? ` — فرع: ${d.invite.branch.name_ar}` : '';
    document.getElementById('inv-branch-info').textContent = `✅ مرحباً! تم التحقق من دعوتك${branchName}`;
    document.getElementById('inv-step1').style.display = 'none';
    document.getElementById('inv-step2').style.display = '';
  } catch(e) {
    errEl.textContent = 'حدث خطأ، حاول مجدداً.'; errEl.classList.add('show');
  }
}

function invBack() {
  document.getElementById('inv-step2').style.display = 'none';
  document.getElementById('inv-step1').style.display = '';
  _inviteData = null;
}

// ── Submit invite registration ────────────────────────────────
async function submitInviteReg() {
  const name = document.getElementById('inv-name').value.trim();
  const pw   = document.getElementById('inv-pw').value;
  const pw2  = document.getElementById('inv-pw2').value;
  const err  = document.getElementById('inv-reg-err');
  const ok   = document.getElementById('inv-reg-ok');
  err.textContent = ''; err.classList.remove('show');
  ok.classList.remove('show');

  if (!name)          { err.textContent = 'أدخل اسمك الكامل.'; err.classList.add('show'); return; }
  if (pw.length < 8)  { err.textContent = 'كلمة المرور يجب أن تكون 8 أحرف على الأقل.'; err.classList.add('show'); return; }
  if (pw !== pw2)     { err.textContent = 'كلمتا المرور غير متطابقتين.'; err.classList.add('show'); return; }

  const btn = document.getElementById('inv-submit-btn');
  btn.disabled = true; btn.textContent = 'جاري الإنشاء...';

  try {
    const res = await fetch('/api/auth/register-invite', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
      body: JSON.stringify({ name, email: _inviteData.email, password: pw, password_confirmation: pw2 }),
    });
    const d = await res.json();
    if (!res.ok || !d.success) {
      const msg = d.errors ? Object.values(d.errors).flat().join(' ') : d.message;
      err.textContent = msg || 'حدث خطأ.'; err.classList.add('show');
      btn.disabled = false; btn.textContent = 'إنشاء حسابي 🚀';
      return;
    }
    ok.textContent = '✅ تم إنشاء حسابك! جاري التحويل...'; ok.classList.add('show');
    setTimeout(() => { switchTab('login', null); }, 1800);
  } catch(e) {
    err.textContent = 'حدث خطأ، حاول مجدداً.'; err.classList.add('show');
    btn.disabled = false; btn.textContent = 'إنشاء حسابي 🚀';
  }
}
</script>
</body>
</html>
