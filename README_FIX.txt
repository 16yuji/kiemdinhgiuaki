Travel Mate - Business/UI fix patch

Đã sửa:
1. Chỉ Customer mới được tạo booking, thanh toán, hủy, đánh giá, gửi yêu cầu đối tác.
2. Admin/Owner vẫn xem được trang public khách sạn, nhưng không thấy nút đặt khả dụng.
3. Customer booking detail không còn hiển thị tài chính nội bộ 15%/85%.
4. Nếu đơn đã đến ngày nhận phòng, customer không còn thấy form hủy online; chỉ hiện hướng dẫn liên hệ hỗ trợ.
5. Booking history chỉ hiện nút Hủy khi còn được hủy online; nếu đã đến ngày nhận phòng thì hiện 'Liên hệ hỗ trợ'.
6. Chuẩn hóa nút, badge trạng thái, bảng, sidebar icon/active.
7. Footer không hiển thị Đăng nhập/Đăng ký cho user đã đăng nhập.
8. Gỡ khối tài khoản test khỏi trang đăng nhập.

Sau khi copy đè, chạy:
php artisan optimize:clear
php artisan view:clear
php artisan route:clear

Nên xóa thư mục rác nếu đang có do giải nén patch nhầm tầng:
rm -rf resources/views/resources
# PowerShell: Remove-Item -Recurse -Force resources\views\resources
