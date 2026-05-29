<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>دعوة للتسجيل — Wafra Gulf</title>
<style>
  body{margin:0;padding:0;background:#f0f4f8;font-family:'Segoe UI',Tahoma,Arial,sans-serif;direction:rtl}
  .wrap{max-width:560px;margin:30px auto;background:#ffffff;border-radius:14px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.08)}
  .header{background:linear-gradient(135deg,#1aadb3,#0e7a82);padding:30px 28px;text-align:center}
  .header h1{color:#fff;font-size:20px;margin:0 0 4px;font-weight:700}
  .header p{color:rgba(255,255,255,.82);font-size:12px;margin:0}
  .body{padding:28px 28px 20px}
  .greeting{font-size:16px;font-weight:700;color:#1a2535;margin-bottom:8px}
  .sub{font-size:13px;color:#5c6d80;margin-bottom:22px;line-height:1.8}
  .info-box{background:#f0f9fb;border:1px solid #b0dde4;border-radius:10px;padding:18px 20px;margin-bottom:20px}
  .info-box h3{font-size:11px;text-transform:uppercase;color:#8aa3b5;font-weight:700;margin:0 0 12px;letter-spacing:.5px}
  .info-row{display:flex;justify-content:space-between;align-items:center;padding:9px 0;border-bottom:1px solid #d4eaf0}
  .info-row:last-child{border-bottom:none}
  .info-label{font-size:12px;color:#6b7f92}
  .info-value{font-size:13px;font-weight:700;color:#1a2535;background:#e0f0f5;padding:3px 10px;border-radius:6px;direction:ltr}
  .steps-box{background:#fffbf0;border:1px solid #f5c96e;border-radius:10px;padding:16px 20px;margin-bottom:20px}
  .steps-box h3{font-size:12px;color:#8a6a1a;font-weight:700;margin:0 0 12px}
  .step{display:flex;gap:10px;align-items:flex-start;padding:6px 0;font-size:13px;color:#3a2a08;line-height:1.6}
  .step-num{background:#f5a828;color:white;border-radius:50%;width:22px;height:22px;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:700;flex-shrink:0;margin-top:2px}
  .btn{display:block;text-align:center;background:linear-gradient(135deg,#1aadb3,#0e7a82);color:#fff!important;text-decoration:none;padding:14px 24px;border-radius:9px;font-size:15px;font-weight:700;margin-bottom:20px;letter-spacing:.3px}
  .footer{background:#f5f9fc;border-top:1px solid #dde9f0;padding:16px 28px;text-align:center;font-size:11px;color:#8aa3b5;line-height:1.8}
</style>
</head>
<body>
<div class="wrap">
  <div class="header">
    <h1>🏢 وفرة الخليجية للخدمات المالية</h1>
    <p>Wafra Gulf Financial Services</p>
  </div>
  <div class="body">
    <div class="greeting">مرحباً،</div>
    <div class="sub">
      تمت <strong>دعوتك للتسجيل</strong> في منصة إدارة عمولات وفرة الخليجية. يمكنك الآن إنشاء حسابك واستخدام المنصة.<br>
      <span style="font-size:11px;color:#8aa3b5">You have been invited to register on the Wafra Gulf Commission Platform.</span>
    </div>

    <div class="info-box">
      <h3>📋 تفاصيل الدعوة / Invitation Details</h3>
      <div class="info-row">
        <span class="info-label">البريد الإلكتروني / Email</span>
        <span class="info-value">{{ $email }}</span>
      </div>
      @if($branchName)
      <div class="info-row">
        <span class="info-label">الفرع / Branch</span>
        <span class="info-value">{{ $branchName }}</span>
      </div>
      @endif
      <div class="info-row">
        <span class="info-label">الدور / Role</span>
        <span class="info-value">{{ $role === 'branch_manager' ? 'مدير فرع / Branch Manager' : 'مشاهد / Viewer' }}</span>
      </div>
    </div>

    <div class="steps-box">
      <h3>📌 خطوات التسجيل / How to Register</h3>
      <div class="step"><div class="step-num">1</div><div>افتح رابط تسجيل الدخول أدناه / Open the login link below</div></div>
      <div class="step"><div class="step-num">2</div><div>اضغط على <strong>تسجيل</strong> واختر <strong>لديّ دعوة</strong> / Click Register → I have an invitation</div></div>
      <div class="step"><div class="step-num">3</div><div>أدخل هذا البريد الإلكتروني للتحقق / Enter this email to verify</div></div>
      <div class="step"><div class="step-num">4</div><div>أنشئ اسمك وكلمة المرور / Set your name and password</div></div>
    </div>

    <a href="{{ $loginUrl }}" class="btn">🔐 تسجيل الآن — Register Now</a>
  </div>
  <div class="footer">
    منصة وفرة الخليجية لإدارة العمولات &nbsp;|&nbsp; Wafra Gulf Commission Platform<br>
    {{ config('app.url') }}<br>
    <span style="color:#b0bec5">إذا لم تتوقع هذا البريد، يمكنك تجاهله بأمان.</span>
  </div>
</div>
</body>
</html>
