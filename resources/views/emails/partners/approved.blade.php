<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Duyệt đối tác</title></head>
<body style="margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;"><tr><td align="center">
<table width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">
<tr><td style="background:#064e3b;color:#fff;padding:28px 32px;"><div style="font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#a7f3d0;">Travel Mate Partner</div><h1 style="margin:8px 0 0;font-size:26px;">Yêu cầu đối tác đã được phê duyệt</h1></td></tr>
<tr><td style="padding:28px 32px;line-height:1.6;">
<p>Xin chào <strong>{{ $partnerRequest->user->name ?? 'bạn' }}</strong>,</p>
<p>Yêu cầu đăng ký làm đối tác cho cơ sở <strong>{{ $partnerRequest->business_name }}</strong> đã được Admin phê duyệt.</p>
<p>Tài khoản của bạn đã được nâng cấp thành <strong>Chủ cơ sở (Owner)</strong>. Bạn có thể truy cập Dashboard để tạo khách sạn, hạng phòng và quản lý đặt phòng.</p>
<p><a href="{{ route('owner.dashboard') }}" style="display:inline-block;background:#0071e3;color:#fff;text-decoration:none;padding:12px 22px;border-radius:999px;font-weight:bold;">Vào Owner Dashboard</a></p>
<p style="font-size:13px;color:#64748b;">Cảm ơn bạn đã đồng hành cùng Travel Mate.</p>
</td></tr>
</table>
</td></tr></table>
</body>
</html>
