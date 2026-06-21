<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác nhận hủy đơn</title>
</head>
<body style="margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                <tr>
                    <td style="background:#991b1b;color:#ffffff;padding:28px 32px;">
                        <div style="font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#fecaca;">Travel Mate</div>
                        <h1 style="margin:8px 0 0;font-size:26px;line-height:1.25;">Đơn đặt phòng đã được hủy</h1>
                        <p style="margin:10px 0 0;color:#fee2e2;font-size:15px;">Mã đơn: <strong style="color:#ffffff;">{{ $booking->booking_code }}</strong></p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 32px;">
                        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Xin chào <strong>{{ $booking->contact_name }}</strong>,</p>
                        <p style="margin:0 0 22px;font-size:15px;line-height:1.6;color:#374151;">
                            Hệ thống đã ghi nhận yêu cầu hủy đơn đặt phòng tại <strong>{{ $booking->hotel->name }}</strong>.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f8fafc;border-radius:14px;overflow:hidden;">
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;width:38%;">Thời gian lưu trú</td>
                                <td style="padding:14px 16px;">{{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Lý do hủy</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;">{{ $booking->cancel_reason ?: 'Không có lý do cụ thể.' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Tổng tiền</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;font-weight:bold;">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</td>
                            </tr>
                        </table>

                        @if($booking->payment && in_array($booking->payment->status, ['refunding', 'refunded', 'non_refundable'], true))
                            <div style="margin-top:20px;padding:16px;border-radius:14px;background:#fff7ed;color:#9a3412;line-height:1.6;">
                                <strong>Thông tin hoàn tiền:</strong><br>
                                Trạng thái: {{ $booking->payment->status }}<br>
                                Số tiền dự kiến/đã hoàn: {{ number_format($booking->payment->refund_amount ?? 0, 0, ',', '.') }}đ<br>
                                {{ $booking->payment->refund_note ?: 'Admin sẽ xử lý theo chính sách của khách sạn.' }}
                            </div>
                        @endif

                        <div style="margin-top:24px;">
                            <a href="{{ route('customer.bookings.show', $booking) }}" style="display:inline-block;background:#0071e3;color:#ffffff;text-decoration:none;padding:12px 22px;border-radius:999px;font-weight:bold;">
                                Xem chi tiết đơn
                            </a>
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
