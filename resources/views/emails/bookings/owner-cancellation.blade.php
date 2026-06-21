<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thông báo khách hủy đơn</title>
</head>
<body style="margin:0;background:#f5f7fb;font-family:Arial,Helvetica,sans-serif;color:#111827;">
<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f5f7fb;padding:24px 0;">
    <tr>
        <td align="center">
            <table role="presentation" width="640" cellspacing="0" cellpadding="0" style="max-width:640px;width:100%;background:#ffffff;border-radius:18px;overflow:hidden;border:1px solid #e5e7eb;">
                <tr>
                    <td style="background:#111827;color:#ffffff;padding:28px 32px;">
                        <div style="font-size:13px;letter-spacing:1px;text-transform:uppercase;color:#bfdbfe;">Travel Mate Owner</div>
                        <h1 style="margin:8px 0 0;font-size:25px;line-height:1.25;">Khách đã hủy đơn đặt phòng</h1>
                        <p style="margin:10px 0 0;color:#e5e7eb;font-size:15px;">Mã đơn: <strong style="color:#ffffff;">{{ $booking->booking_code }}</strong></p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:28px 32px;">
                        <p style="margin:0 0 16px;font-size:16px;line-height:1.6;">Xin chào <strong>{{ $booking->hotel->owner->name ?? 'Chủ cơ sở' }}</strong>,</p>
                        <p style="margin:0 0 22px;font-size:15px;line-height:1.6;color:#374151;">
                            Khách hàng đã hủy đơn đặt phòng tại <strong>{{ $booking->hotel->name }}</strong>. Vui lòng cập nhật lịch/phòng của cơ sở.
                            Việc xử lý tiền hoàn, nếu có, do Admin Travel Mate kiểm tra theo chính sách hủy/hoàn tiền của khách sạn.
                        </p>

                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="border-collapse:collapse;background:#f8fafc;border-radius:14px;overflow:hidden;">
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;width:38%;">Khách hàng</td>
                                <td style="padding:14px 16px;">{{ $booking->contact_name }} - {{ $booking->contact_phone }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Thời gian lưu trú</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;">{{ $booking->checkin_date->format('d/m/Y') }} - {{ $booking->checkout_date->format('d/m/Y') }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Lý do khách hủy</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;">{{ $booking->cancel_reason ?: 'Không có lý do cụ thể.' }}</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Tổng tiền đơn</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;font-weight:bold;">{{ number_format($booking->total_amount, 0, ',', '.') }}đ</td>
                            </tr>
                            <tr>
                                <td style="padding:14px 16px;color:#64748b;border-top:1px solid #e5e7eb;">Trạng thái thanh toán</td>
                                <td style="padding:14px 16px;border-top:1px solid #e5e7eb;">{{ $booking->payment->status ?? 'Chưa có thanh toán' }}</td>
                            </tr>
                        </table>

                        <div style="margin-top:20px;padding:16px;border-radius:14px;background:#eff6ff;color:#1e3a8a;line-height:1.6;">
                            <strong>Lưu ý:</strong><br>
                            Owner không xử lý hoàn tiền trực tiếp cho khách trên hệ thống. Admin Travel Mate sẽ kiểm tra chính sách và xử lý tiền nếu đơn đủ điều kiện hoàn.
                        </div>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
