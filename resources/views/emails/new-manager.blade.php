<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>بيانات دخولك — Wafra Gulf</title>
<style>
  body { margin:0; padding:0; background:#f0f4f8; font-family:'Segoe UI',Tahoma,Arial,sans-serif; direction:rtl; }
  .wrap { max-width:560px; margin:30px auto; background:#ffffff; border-radius:14px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,.08); }
  .header { background:linear-gradient(135deg,#1aadb3,#0e7a82); padding:30px 28px; text-align:center; }
  .header img { height:48px; margin-bottom:10px; }
  .header h1 { color:#fff; font-size:20px; margin:0; font-weight:700; letter-spacing:.3px; }
  .header p { color:rgba(255,255,255,.82); font-size:12px; margin:6px 0 0; }
  .body { padding:28px 28px 20px; }
  .greeting { font-size:16px; font-weight:700; color:#1a2535; margin-bottom:6px; }
  .sub { font-size:13px; color:#5c6d80; margin-bottom:22px; line-height:1.7; }
  .creds-box { background:#f5f9fc; border:1px solid #c8dde9; border-radius:10px; padding:18px 20px; margin-bottom:20px; }
  .creds-box h3 { font-size:12px; text-transform:uppercase; color:#8aa3b5; font-weight:700; margin:0 0 12px; letter-spacing:.5px; }
  .cred-row { display:flex; justify-content:space-between; align-items:center; padding:9px 0; border-bottom:1px solid #dde9f0; }
  .cred-row:last-child { border-bottom:none; }
  .cred-label { font-size:12px; color:#6b7f92; }
  .cred-value { font-family:'Courier New',monospace; font-size:14px; font-weight:700; color:#1a2535; background:#e0f0f5; padding:3px 10px; border-radius:6px; direction:ltr; }
  .pw-value { color:#0e7a82; font-size:16px; letter-spacing:1px; }
  .notice { background:#fff8e6; border:1px solid #f5ca6e; border-radius:9px; padding:12px 16px; font-size:12px; color:#7a5c1a; margin-bottom:20px; line-height:1.7; }
  .btn { display:block; text-align:center; background:linear-gradient(135deg,#1aadb3,#0e7a82); color:#fff !important; text-decoration:none; padding:13px 24px; border-radius:9px; font-size:14px; font-weight:700; margin-bottom:20px; }
  .footer { background:#f5f9fc; border-top:1px solid #dde9f0; padding:16px 28px; text-align:center; font-size:11px; color:#8aa3b5; line-height:1.8; }
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>🏢 وفرة الخليجية للخدمات المالية</h1>
    <p>Wafra Gulf Financial Services</p>
  </div>
  <div class="body">
    <div class="greeting">مرحباً {{ $manager->name }}،</div>
    <div class="sub">
      تم إنشاء حسابك في منصة إدارة العمولات. يمكنك تسجيل الدخول باستخدام البيانات التالية:<br>
      <span style="font-size:11px;color:#8aa3b5">Your account has been created. Use the credentials below to log in.</span>
    </div>

    <div class="creds-box">
      <h3>📋 بيانات الدخول / Login Credentials</h3>
      <div class="cred-row">
        <span class="cred-label">البريد الإلكتروني / Email</span>
        <span class="cred-value">{{ $manager->email }}</span>
      </div>
      <div class="cred-row">
        <span class="cred-label">كلمة المرور المؤقتة / Temp Password</span>
        <span class="cred-value pw-value">{{ $plainPassword }}</span>
      </div>
      @if($manager->branch)
      <div class="cred-row">
        <span class="cred-label">الفرع / Branch</span>
        <span class="cred-value">{{ $manager->branch->name_ar }}</span>
      </div>
      @endif
    </div>

    <div class="notice">
      ⚠️ <strong>هذه كلمة مرور مؤقتة.</strong> سيُطلب منك تغييرها عند تسجيل الدخول للمرة الأولى.<br>
      <span style="font-size:11px">This is a temporary password. You will be required to change it on first login.</span>
    </div>

    <a href="{{ $loginUrl }}" class="btn">🔐 تسجيل الدخول — Login Now</a>
  </div>
  <div class="footer">
    منصة وفرة الخليجية لإدارة العمولات &nbsp;|&nbsp; Wafra Gulf Commission Platform<br>
    {{ config('app.url') }}<br>
    <span style="color:#b0bec5">إذا لم تتوقع هذا البريد، يمكنك تجاهله بأمان.</span>
  </div>
</div>
</body>
</html>
