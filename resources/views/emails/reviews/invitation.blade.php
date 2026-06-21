<!DOCTYPE html>
<html lang="vi">
<head><meta charset="UTF-8"><title>Mời đánh giá dịch vụ</title></head>
<body style="margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;"><tr><td align="center">
<table width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:18px;overflow:hidden;">
<tr><td style="background:#0f172a;color:#fff;padding:28px 32px;"><div style="font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#93c5fd;">Travel Mate</div><h1 style="margin:8px 0 0;font-size:26px;">Cảm ơn bạn đã lưu trú</h1></td></tr>
<tr><td style="padding:28px 32px;line-height:1.6;">
<p>Xin chào <strong>{{ $booking->contact_name }}</strong>,</p>
<p>Cảm ơn bạn đã sử dụng dịch vụ tại <strong>{{ $booking->hotel->name }}</strong>.</p>
<p>Trải nghiệm của bạn sẽ giúp khách sạn cải thiện dịch vụ và giúp những khách hàng khác có lựa chọn phù hợp hơn.</p>
<table width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border-radius:14px;margin:18px 0;">
<tr><td style="padding:14px 16px;color:#64748b;">Mã đơn</td><td style="padding:14px 16px;font-weight:bold;">{{ $booking->booking_code }}</td></tr>
<tr><td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Thời gian</td><td style="padding:14px 16px;border-top:1px solid #e5e7eb;">{{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}</td></tr>
</table>
@if(!$booking->review)
<p><a href="{{ route('customer.reviews.create', $booking) }}" style="display:inline-block;background:#0071e3;color:#fff;text-decoration:none;padding:12px 22px;border-radius:999px;font-weight:bold;">Đánh giá ngay</a></p>
@else
<p style="color:#64748b;">Bạn đã đánh giá đơn này. Cảm ơn bạn!</p>
@endif
</td></tr>
</table>
</td></tr></table>
</body>
</html>
