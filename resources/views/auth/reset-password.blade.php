<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>إعادة تعيين كلمة المرور — وفرة الخليجية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;600;700;800&display=swap" rel="stylesheet">
<style>
*{box-sizing:border-box;margin:0;padding:0}
:root{--bg:#0f1117;--bg2:#161b27;--tx:#e8eaf0;--mu:#8892a4;--brd:rgba(255,255,255,.08);
  --teal:#14B87E;--pri:#2A82A8;--re:#E24B4A;--gr:#88C34B;--inp:#1e2535}
body{font-family:'Tajawal',sans-serif;background:var(--bg);color:var(--tx);
  min-height:100dvh;display:flex;align-items:center;justify-content:center;padding:20px}

.card{background:var(--bg2);border:1px solid var(--brd);border-radius:16px;
  padding:36px 32px;width:100%;max-width:420px;box-shadow:0 8px 40px rgba(0,0,0,.4)}

.logo-wrap{text-align:center;margin-bottom:24px}
.logo-3d{width:56px;height:56px;margin:0 auto 10px;display:flex;align-items:center;justify-content:center}
.brand-name{font-size:18px;font-weight:800;color:var(--tx)}
.brand-sub{font-size:11px;color:var(--mu)}

.card-title{font-size:16px;font-weight:700;color:var(--tx);margin-bottom:4px}
.card-sub{font-size:12px;color:var(--mu);margin-bottom:24px;line-height:1.6}

.form-group{margin-bottom:16px}
.form-label{display:block;font-size:12px;color:var(--mu);margin-bottom:6px}
.form-control{width:100%;background:var(--inp);border:1px solid var(--brd);border-radius:8px;
  padding:10px 14px;color:var(--tx);font-size:16px;font-family:'Tajawal',sans-serif;
  transition:border .2s}
.form-control:focus{outline:none;border-color:var(--teal)}

.pw-wrap{position:relative}
.pw-eye{position:absolute;left:10px;top:50%;transform:translateY(-50%);
  background:none;border:none;color:var(--mu);cursor:pointer;font-size:16px;padding:4px}

.strength{height:4px;border-radius:2px;margin-top:6px;background:var(--brd);overflow:hidden}
.strength-bar{height:100%;border-radius:2px;transition:all .3s}
.strength-label{font-size:10px;color:var(--mu);margin-top:3px}

.btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,var(--teal),#0E9469);color:white;border:none;
  border-radius:8px;font-size:15px;font-weight:700;cursor:pointer;font-family:'Tajawal',sans-serif;
  transition:background .2s;margin-top:4px}
.btn-submit:hover{background:#1a8a65}
.btn-submit:disabled{opacity:.5;cursor:default}

.error-box{background:rgba(226,75,74,.1);border:1px solid rgba(226,75,74,.25);
  border-radius:8px;padding:10px 14px;font-size:12px;color:var(--re);margin-bottom:16px}

.back-link{text-align:center;margin-top:16px}
.back-link a{font-size:12px;color:var(--mu);text-decoration:none}
.back-link a:hover{color:var(--teal)}

.rules{font-size:11px;color:var(--mu);margin-top:6px;padding:8px 10px;
  background:rgba(255,255,255,.03);border-radius:6px}
.rule{display:flex;align-items:center;gap:5px;padding:2px 0}
.rule .dot{width:6px;height:6px;border-radius:50%;background:var(--brd);flex-shrink:0}
.rule.ok .dot{background:var(--gr)}
</style>
</head>
<body>

<div class="card">
  <div class="logo-wrap">
    <div class="logo-3d">
      {!! file_get_contents(public_path('logo_3d.svg')) !!}
    </div>
    <div class="brand-name">وفرة الخليجية</div>
    <div class="brand-sub">Commission Cards System</div>
  </div>

  <div class="card-title">🔐 إعادة تعيين كلمة المرور</div>
  <div class="card-sub">أنشئ كلمة مرور جديدة قوية لحسابك</div>

  @if($errors->any())
  <div class="error-box">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('auth.password.reset.submit') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">

    <div class="form-group">
      <label class="form-label">كلمة المرور الجديدة / New Password *</label>
      <div class="pw-wrap">
        <input type="password" name="password" id="pw-new" class="form-control"
               autocomplete="new-password" oninput="checkStrength(this.value)"
               enterkeyhint="next" required>
        <button type="button" class="pw-eye" onclick="togglePw('pw-new', this)">👁</button>
      </div>
      <div class="strength"><div class="strength-bar" id="strength-bar"></div></div>
      <div class="strength-label" id="strength-label"></div>
      <div class="rules" id="rules">
        <div class="rule" id="r-len"><span class="dot"></span> 8 أحرف على الأقل</div>
        <div class="rule" id="r-upper"><span class="dot"></span> حرف كبير (A-Z)</div>
        <div class="rule" id="r-num"><span class="dot"></span> رقم (0-9)</div>
        <div class="rule" id="r-sym"><span class="dot"></span> رمز خاص (!@#$...)</div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">تأكيد / Confirm Password *</label>
      <div class="pw-wrap">
        <input type="password" name="password_confirmation" id="pw-confirm"
               class="form-control" autocomplete="new-password"
               oninput="checkMatch()" enterkeyhint="go" required>
        <button type="button" class="pw-eye" onclick="togglePw('pw-confirm', this)">👁</button>
      </div>
      <div id="match-msg" style="font-size:11px;margin-top:4px"></div>
    </div>

    <button type="submit" class="btn-submit" id="btn-submit" disabled>
      تعيين كلمة المرور الجديدة ←
    </button>
  </form>

  <div class="back-link">
    <a href="{{ route('auth.login') }}">← العودة لتسجيل الدخول</a>
  </div>
</div>

<script>
let strengthOk = false, matchOk = false;

function togglePw(id, btn) {
  const el = document.getElementById(id);
  el.type = el.type === 'password' ? 'text' : 'password';
  btn.textContent = el.type === 'password' ? '👁' : '🙈';
}

function checkStrength(pw) {
  const rules = {
    'r-len':   pw.length >= 8,
    'r-upper': /[A-Z]/.test(pw),
    'r-num':   /[0-9]/.test(pw),
    'r-sym':   /[^A-Za-z0-9]/.test(pw),
  };
  Object.entries(rules).forEach(([id, ok]) => {
    document.getElementById(id)?.classList.toggle('ok', ok);
  });
  const score = Object.values(rules).filter(Boolean).length;
  const bar   = document.getElementById('strength-bar');
  const lbl   = document.getElementById('strength-label');
  const colors = ['','#E24B4A','#EF9F27','#EF9F27','#88C34B'];
  const labels = ['','ضعيفة','متوسطة','جيدة','قوية ✓'];
  bar.style.width   = (score * 25) + '%';
  bar.style.background = colors[score] || '';
  lbl.textContent   = labels[score] || '';
  strengthOk = score === 4;
  updateBtn();
}

function checkMatch() {
  const pw1 = document.getElementById('pw-new').value;
  const pw2 = document.getElementById('pw-confirm').value;
  const msg = document.getElementById('match-msg');
  if (!pw2) { msg.textContent = ''; matchOk = false; }
  else if (pw1 === pw2) { msg.textContent = '✓ كلمتا المرور متطابقتان'; msg.style.color = 'var(--gr)'; matchOk = true; }
  else { msg.textContent = '✗ كلمتا المرور غير متطابقتين'; msg.style.color = 'var(--re)'; matchOk = false; }
  updateBtn();
}

function updateBtn() {
  document.getElementById('btn-submit').disabled = !(strengthOk && matchOk);
}
</script>
</body>
</html>
