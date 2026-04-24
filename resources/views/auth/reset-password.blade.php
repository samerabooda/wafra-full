<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>إعادة تعيين كلمة المرور — وفرة الخليجية</title>
<link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;700;800;900&display=swap" rel="stylesheet">
<style>
:root{--pri:#2E86AB;--pri2:#3A9DB5;--bg:#0A1628;--bg2:#142240;--brd1:#253A63;--tx:#EDF4F8;--mu:#5A7A9A;--gr:#22C97A;--re:#E05050;}
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Tajawal',sans-serif;background:var(--bg);color:var(--tx);min-height:100vh;display:flex;align-items:center;justify-content:center;padding:20px}
.card{background:var(--bg2);border:1px solid var(--brd1);border-radius:20px;padding:40px 36px;width:100%;max-width:420px}
h2{font-size:1.5rem;font-weight:800;margin-bottom:8px;color:var(--tx)}
.sub{font-size:13px;color:var(--mu);margin-bottom:28px}
.form-group{margin-bottom:18px}
.form-label{display:block;font-size:13px;color:var(--mu);margin-bottom:6px;font-weight:700}
.form-input{width:100%;background:var(--bg);border:1.5px solid var(--brd1);border-radius:10px;padding:11px 14px;color:var(--tx);font-family:inherit;font-size:14px;outline:none;transition:border .2s}
.form-input:focus{border-color:var(--pri)}
.btn{width:100%;background:var(--pri);color:#fff;border:none;border-radius:10px;padding:13px;font-size:15px;font-weight:800;cursor:pointer;margin-top:4px;font-family:inherit;transition:background .2s}
.btn:hover{background:var(--pri2)}
.err{background:rgba(224,80,80,.1);border:1px solid rgba(224,80,80,.3);border-radius:8px;padding:10px 14px;font-size:13px;color:var(--re);margin-bottom:16px}
.back{text-align:center;margin-top:18px;font-size:13px;color:var(--mu)}
.back a{color:var(--pri2);text-decoration:none;font-weight:700}
</style>
</head>
<body>
<div class="card">
  <h2>إعادة تعيين كلمة المرور</h2>
  <p class="sub">أدخل كلمة المرور الجديدة وتأكيدها</p>

  @if($errors->any())
    <div class="err">{{ $errors->first() }}</div>
  @endif

  <form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">

    <div class="form-group">
      <label class="form-label">البريد الإلكتروني</label>
      <input class="form-input" type="email" name="email" value="{{ old('email', $email) }}" required autofocus>
    </div>

    <div class="form-group">
      <label class="form-label">كلمة المرور الجديدة</label>
      <input class="form-input" type="password" name="password" required minlength="8">
    </div>

    <div class="form-group">
      <label class="form-label">تأكيد كلمة المرور</label>
      <input class="form-input" type="password" name="password_confirmation" required minlength="8">
    </div>

    <button type="submit" class="btn">تغيير كلمة المرور</button>
  </form>

  <div class="back"><a href="{{ route('auth.login') }}">← العودة لصفحة الدخول</a></div>
</div>
</body>
</html>
