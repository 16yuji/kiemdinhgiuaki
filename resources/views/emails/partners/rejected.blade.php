<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Từ chối đối tác</title></head>
<body style="margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;"><tr><td align="center">
<table width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">
<tr><td style="background:#7f1d1d;color:#fff;padding:28px 32px;"><div style="font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#fecaca;">Travel Mate Partner</div><h1 style="margin:8px 0 0;font-size:26px;">Yêu cầu đối tác chưa được phê duyệt</h1></td></tr>
<tr><td style="padding:28px 32px;line-height:1.6;">
<p>Xin chào <strong>{{ $partnerRequest->user->name ?? 'bạn' }}</strong>,</p>
<p>Yêu cầu đăng ký làm đối tác cho cơ sở <strong>{{ $partnerRequest->business_name }}</strong> chưa được phê duyệt.</p>
<div style="margin:18px 0;padding:16px;border-radius:14px;background:#fef2f2;color:#991b1b;">
<strong>Lý do từ chối:</strong><br>{{ $partnerRequest->reject_reason ?: 'Chưa có lý do cụ thể.' }}
</div>
<p>Bạn có thể kiểm tra lại thông tin cơ sở và gửi yêu cầu mới khi đã bổ sung đầy đủ.</p>
<p><a href="{{ route('customer.partner-request.create') }}" style="display:inline-block;background:#0071e3;color:#fff;text-decoration:none;padding:12px 22px;border-radius:999px;font-weight:bold;">Xem yêu cầu đối tác</a></p>
</td></tr>
</table>
</td></tr></table>
</body>
</html>
