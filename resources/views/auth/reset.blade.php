<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>تغيير كلمة المرور — وفرة الخليجية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--pri:#2E86AB;--pri2:#3A9DB5;--bg:#0A1628;--bg2:#142240;--bg3:#1A2B4E;--brd1:#253A63;--tx:#EDF4F8;--mu:#5A7A9A;--gr:#22C97A;--re:#E05050;}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Tajawal',sans-serif;background:var(--bg);color:var(--tx);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
body::before{content:'';position:fixed;inset:0;pointer-events:none;background:radial-gradient(ellipse 900px 500px at 70% -10%,rgba(46,134,171,.1),transparent 55%)}

.wrap{width:100%;max-width:420px}

/* Card */
.card{background:var(--bg2);border:1px solid var(--brd1);border-radius:20px;padding:38px 32px;box-shadow:0 24px 60px rgba(0,0,0,.4)}

/* Logo row */
.logo-row{display:flex;justify-content:center;margin-bottom:28px}

/* Heading */
.card-title{font-size:1.4rem;font-weight:900;text-align:center;margin-bottom:6px}
.card-sub{font-size:12px;color:var(--mu);text-align:center;margin-bottom:28px}

/* Form */
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:12px;color:var(--mu);font-weight:700;margin-bottom:7px;text-transform:uppercase;letter-spacing:.4px}
.form-input{width:100%;background:var(--bg3);border:1px solid var(--brd1);border-radius:10px;padding:11px 14px;color:var(--tx);font-family:'Tajawal',sans-serif;font-size:14px;transition:.2s}
.form-input:focus{outline:none;border-color:var(--pri2);box-shadow:0 0 0 3px rgba(58,157,181,.15)}
.pw-wrap{position:relative}
.pw-toggle{position:absolute;left:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--mu);cursor:pointer;font-size:14px;line-height:1}

/* Strength bar */
.pw-strength{height:3px;border-radius:2px;margin-top:6px;transition:.3s;background:var(--brd1)}
.pw-strength[data-level="1"]{background:var(--re);width:30%}
.pw-strength[data-level="2"]{background:#f5a623;width:60%}
.pw-strength[data-level="3"]{background:var(--gr);width:100%}

/* Btn */
.btn{width:100%;background:linear-gradient(135deg,var(--pri),var(--pri3));border:none;border-radius:12px;padding:13px;color:#fff;font-family:'Tajawal',sans-serif;font-size:15px;font-weight:800;cursor:pointer;transition:.2s;margin-top:6px}
.btn:hover{opacity:.9;transform:translateY(-1px)}
.btn:active{transform:none}

/* Alerts */
.alert{border-radius:10px;padding:11px 14px;font-size:13px;margin-bottom:16px;display:flex;align-items:center;gap:8px}
.alert-err{background:rgba(224,80,80,.12);border:1px solid rgba(224,80,80,.3);color:#ff8888}
.alert-ok {background:rgba(34,201,122,.10);border:1px solid rgba(34,201,122,.3);color:#4ae89a}

/* Back link */
.back-link{display:block;text-align:center;margin-top:18px;font-size:13px;color:var(--mu);cursor:pointer;text-decoration:none;transition:.2s}
.back-link:hover{color:var(--pri2)}
</style>
</head>
<body>
<div class="wrap">
  <div class="card">

    {{-- Logo --}}
    <div class="logo-row">
      @include('partials.globe', ['size'=>'sm','showText'=>false,'gid'=>'reset_logo'])
    </div>

    <div class="card-title">تغيير كلمة المرور</div>
    <div class="card-sub">أدخل كلمة المرور الجديدة لحسابك</div>

    {{-- Errors --}}
    @if($errors->any())
      <div class="alert alert-err">⚠️ {{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('auth.password.update') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">

      <div class="form-group">
        <label class="form-label">البريد الإلكتروني</label>
        <input class="form-input" type="email" name="email"
               value="{{ old('email', $email ?? '') }}"
               placeholder="your@wafragulf.com" required autocomplete="email">
      </div>

      <div class="form-group">
        <label class="form-label">كلمة المرور الجديدة</label>
        <div class="pw-wrap">
          <input class="form-input" type="password" name="password" id="pw"
                 placeholder="8 أحرف على الأقل" required autocomplete="new-password"
                 oninput="checkStrength(this)">
          <button type="button" class="pw-toggle" onclick="togglePw('pw',this)">👁</button>
        </div>
        <div class="pw-strength" id="pw-bar"></div>
      </div>

      <div class="form-group">
        <label class="form-label">تأكيد كلمة المرور</label>
        <div class="pw-wrap">
          <input class="form-input" type="password" name="password_confirmation" id="pw2"
                 placeholder="أعد كتابة كلمة المرور" required autocomplete="new-password">
          <button type="button" class="pw-toggle" onclick="togglePw('pw2',this)">👁</button>
        </div>
      </div>

      <button type="submit" class="btn">تغيير كلمة المرور ←</button>
    </form>

    <a class="back-link" href="{{ route('auth.login') }}">← رجوع لتسجيل الدخول</a>
  </div>
</div>

<script>
function togglePw(id, btn) {
  const i = document.getElementById(id);
  i.type = i.type === 'password' ? 'text' : 'password';
  btn.textContent = i.type === 'password' ? '👁' : '🙈';
}
function checkStrength(input) {
  const v = input.value, bar = document.getElementById('pw-bar');
  let score = 0;
  if (v.length >= 8) score++;
  if (/[A-Z]/.test(v) || /[0-9]/.test(v)) score++;
  if (/[^A-Za-z0-9]/.test(v) || v.length >= 12) score++;
  bar.setAttribute('data-level', score > 0 ? score : '');
}
</script>
</body>
</html>
