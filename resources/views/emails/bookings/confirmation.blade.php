<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận đặt phòng</title>
</head>
<body style="margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                <tr>
                    <td style="background:#0f172a;color:#ffffff;padding:28px 32px;">
                        <div style="font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#93c5fd;">Travel Mate</div>
                        <h1 style="margin:8px 0 0;font-size:26px;line-height:1.25;">Đặt phòng đã được xác nhận</h1>
                        <p style="margin:10px 0 0;color:#cbd5e1;font-size:15px;">Mã đơn: <strong style="color:#ffffff;">{{ $booking->booking_code }}</strong></p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 32px;">
                        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Xin chào <strong>{{ $booking->contact_name }}</strong>,</p>
                        <p style="margin:0 0 22px;font-size:15px;line-height:1.6;color:#374151;">
                            Cảm ơn bạn đã thanh toán. Đơn đặt phòng của bạn tại <strong>{{ $booking->hotel->name }}</strong> đã được xác nhận.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f8fafc;border-radius:14px;overflow:hidden;">
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;width:38%;">Khách sạn</td>
                                <td style="padding:14px 16px;font-weight:bold;">{{ $booking->hotel->name }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Thời gian lưu trú</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;">
                                    {{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}
                                </td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Số khách</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;">{{ $booking->guest_count }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Tổng tiền</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;font-weight:bold;color:#0369a1;">
                                    {{ number_format($booking->total_amount, 0, ',', '.') }}đ
                                </td>
                            </tr>
                        </table>

                        <h3 style="margin:24px 0 12px;font-size:17px;">Hạng phòng đã đặt</h3>
                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;">
                            @foreach($booking->roomTypes as $item)
                                <tr>
                                    <td style="padding:10px 0;border-bottom:1px solid #e5e7eb;">
                                        <strong>{{ $item->roomType->name ?? 'Hạng phòng' }}</strong><br>
                                        <span style="font-size:13px;color:#64748b;">{{ $item->quantity }} phòng × {{ $item->nights }} đêm</span>
                                    </td>
                                    <td align="right" style="padding:10px 0;border-bottom:1px solid #e5e7eb;font-weight:bold;">
                                        {{ number_format($item->subtotal, 0, ',', '.') }}đ
                                    </td>
                                </tr>
                            @endforeach
                        </table>

                        <div style="margin-top:24px;">
                            <a href="{{ route('customer.bookings.show', $booking) }}" style="display:inline-block;background:#0071e3;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:999px;font-weight:bold;">
                                Xem chi tiết đơn
                            </a>
                        </div>

                        <p style="margin:24px 0 0;font-size:13px;color:#64748b;line-height:1.6;">
                            Vui lòng lưu lại mã đặt phòng để đối chiếu khi nhận phòng. Nếu cần hỗ trợ, hãy liên hệ khách sạn hoặc bộ phận hỗ trợ của hệ thống.
                        </p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
