<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>دعوة للتسجيل — Wafra Gulf</title>
</head>
<body style="margin:0;padding:0;background:#eef3f7;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#eef3f7;font-family:'Segoe UI',Tahoma,Arial,sans-serif;direction:rtl;">
  <tr><td align="center" style="padding:24px 12px;">

    <table role="presentation" width="558" cellpadding="0" cellspacing="0" border="0" style="width:558px;max-width:558px;background:#ffffff;border-radius:16px;overflow:hidden;box-shadow:0 4px 22px rgba(0,0,0,.08);">

      <tr><td style="background:#0E7A82;padding:24px 26px;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
          <td width="92" valign="middle" style="width:92px;">
            <table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
              <td align="center" valign="middle" width="86" height="86" style="width:86px;height:86px;background:#ffffff;border-radius:16px;">
                <img src="{{ url('/assets/email-logo.png') }}" width="74" height="73" alt="وفرة الخليجية" style="display:block;width:74px;height:73px;max-width:74px;border:0;outline:none;">
              </td>
            </tr></table>
          </td>
          <td valign="middle" style="padding-right:12px;">
            <div style="color:#ffffff;font-size:16px;font-weight:700;line-height:1.4;">وفرة الخليجية للخدمات المالية</div>
            <div style="color:#cfeaec;font-size:11px;padding-top:2px;">Wafra Gulf — Commission Platform</div>
          </td>
        </tr></table>
      </td></tr>

      <tr><td style="padding:30px 30px 26px;">
        <div style="font-size:18px;font-weight:700;color:#16212e;padding-bottom:9px;">مرحبًا بك 👋</div>
        <div style="font-size:13.5px;color:#52647a;line-height:1.85;padding-bottom:16px;">
          يسعدنا انضمامك إلى فريق <span style="color:#0E7A82;font-weight:700;">وفرة الخليجية</span>. تمّت دعوتك لإنشاء حسابك على منصّة إدارة العمولات الخاصة بالشركة.
          <br><span style="font-size:11px;color:#9aa8b6;">You have been invited to register on the Wafra Gulf Commission Platform.</span>
        </div>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom:16px;">
          <tr><td style="background:#f3fafb;border:1px solid #cfe9ec;border-radius:11px;padding:14px 16px;">
            <div style="font-size:12px;font-weight:700;color:#0E7A82;padding-bottom:6px;">ما هي المنصّة؟</div>
            <div style="font-size:12.5px;color:#4a5a6b;line-height:1.8;">نظام متكامل لإدارة <span style="font-weight:700;">كروت عمولات الحسابات</span> عبر الفروع: تسجيل البروكرز والمسوّقين، احتساب عمولة كل طرف لكل حساب، ومتابعة <span style="font-weight:700;">التقارير والتحليلات</span> لحظيًا — كلٌّ ضمن صلاحيات فرعه.</div>
          </td></tr>
        </table>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#fafbfc;border:1px solid #e4eaf0;border-radius:11px;margin-bottom:18px;">
          <tr><td style="padding:11px 16px;border-bottom:1px solid #eef2f6;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
              <td align="right" style="font-size:12px;color:#6b7f92;">البريد الإلكتروني / Email</td>
              <td align="left" style="font-size:12.5px;font-weight:700;color:#16212e;direction:ltr;">{{ $email }}</td>
            </tr></table>
          </td></tr>
          @if($branchName)
          <tr><td style="padding:11px 16px;border-bottom:1px solid #eef2f6;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
              <td align="right" style="font-size:12px;color:#6b7f92;">الفرع / Branch</td>
              <td align="left" style="font-size:12.5px;font-weight:700;color:#16212e;">{{ $branchName }}</td>
            </tr></table>
          </td></tr>
          @endif
          <tr><td style="padding:11px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0"><tr>
              <td align="right" style="font-size:12px;color:#6b7f92;">الدور / Role</td>
              <td align="left"><span style="font-size:12px;font-weight:700;color:#0E7A82;background:#e2f2f4;padding:3px 10px;border-radius:6px;">{{ $role === 'branch_manager' ? 'مدير فرع / Branch Manager' : 'مشاهد / Viewer' }}</span></td>
            </tr></table>
          </td></tr>
        </table>

        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr><td align="center" style="background:#0E7A82;border-radius:10px;">
            <a href="{{ $loginUrl }}" style="display:block;padding:13px 18px;color:#ffffff;text-decoration:none;font-size:14.5px;font-weight:700;">تسجيل الدخول وإنشاء كلمة المرور</a>
          </td></tr>
        </table>
        <div style="text-align:center;font-size:11px;color:#9aa8b6;padding-top:8px;">الرابط صالح لحسابك فقط — لا تشاركه مع أحد</div>
      </td></tr>

      <tr><td style="background:#f5f9fc;border-top:1px solid #e4eaf0;padding:20px 26px;text-align:center;font-size:11px;color:#8aa3b5;line-height:1.8;">
        منصّة وفرة الخليجية لإدارة العمولات &nbsp;|&nbsp; {{ config('app.url') }}<br>
        <span style="color:#aab8c4;">إذا لم تتوقّع هذا البريد، يمكنك تجاهله بأمان.</span>
      </td></tr>

    </table>

  </td></tr>
</table>
</body>
</html>
