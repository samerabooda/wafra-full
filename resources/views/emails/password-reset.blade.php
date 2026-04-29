<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>إعادة تعيين كلمة المرور</title>
<style>
  body{margin:0;padding:0;background:#f4f6f9;font-family:'Segoe UI',Tahoma,Arial,sans-serif;direction:rtl}
  .wrap{max-width:560px;margin:32px auto;background:white;border-radius:12px;
    overflow:hidden;box-shadow:0 2px 12px rgba(0,0,0,.08)}
  .head{background:linear-gradient(135deg,#0a2a20,#0F6E56);padding:32px 36px;text-align:center}
  .head-title{color:white;font-size:20px;font-weight:700;margin:12px 0 4px}
  .head-sub{color:rgba(255,255,255,.65);font-size:13px}
  .body{padding:32px 36px}
  .greeting{font-size:16px;color:#1a2a22;margin-bottom:16px}
  .text{font-size:14px;color:#4a5568;line-height:1.8;margin-bottom:20px}
  .btn-wrap{text-align:center;margin:28px 0}
  .btn{display:inline-block;padding:14px 36px;background:#0F6E56;color:white;
    text-decoration:none;border-radius:8px;font-size:15px;font-weight:600;
    letter-spacing:.3px}
  .btn:hover{background:#1D9E75}
  .divider{height:1px;background:#edf2f7;margin:24px 0}
  .link-text{font-size:12px;color:#718096;word-break:break-all}
  .link-url{color:#0F6E56;font-family:monospace;font-size:11px}
  .warning{background:#fff8e6;border-right:4px solid #EF9F27;padding:12px 16px;
    border-radius:4px;font-size:13px;color:#7d4a00;margin-bottom:20px}
  .footer{background:#f9fafb;padding:20px 36px;text-align:center;
    border-top:1px solid #edf2f7}
  .footer-text{font-size:12px;color:#a0aec0;line-height:1.7}
  .logo-box{width:52px;height:52px;background:white;border-radius:12px;
    margin:0 auto 12px;display:flex;align-items:center;justify-content:center;
    font-size:26px;box-shadow:0 2px 8px rgba(0,0,0,.15)}
</style>
</head>
<body>
<div class="wrap">

  {{-- Header --}}
  <div class="head">
    <div class="logo-box">🏦</div>
    <div class="head-title">وفرة الخليجية للخدمات المالية</div>
    <div class="head-sub">Commission Cards System</div>
  </div>

  {{-- Body --}}
  <div class="body">
    <div class="greeting">مرحباً {{ $userName }}،</div>

    <p class="text">
      تلقّينا طلباً لإعادة تعيين كلمة المرور الخاصة بحسابك في نظام كروت العمولات.
      اضغط على الزر أدناه لإنشاء كلمة مرور جديدة.
    </p>

    <div class="btn-wrap">
      <a href="{{ $resetUrl }}" class="btn">إعادة تعيين كلمة المرور ←</a>
    </div>

    <div class="warning">
      ⏱ هذا الرابط صالح لمدة <strong>{{ $expiresMinutes }} دقيقة</strong> فقط.
      إذا لم تطلب إعادة تعيين كلمة المرور، تجاهل هذا البريد.
    </div>

    <div class="divider"></div>

    <p class="link-text">
      إذا لم يعمل الزر، انسخ الرابط التالي وضعه في متصفحك:
    </p>
    <div class="link-url">{{ $resetUrl }}</div>
  </div>

  {{-- Footer --}}
  <div class="footer">
    <p class="footer-text">
      هذا البريد أُرسِل تلقائياً من نظام وفرة الخليجية.<br>
      إذا لم تطلب هذا، يمكنك تجاهل الرسالة بأمان.<br>
      © {{ date('Y') }} وفرة الخليجية للخدمات المالية
    </p>
  </div>

</div>
</body>
</html>
