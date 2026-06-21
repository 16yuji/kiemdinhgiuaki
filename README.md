# KIỂM ĐỊNH PHẦN MỀM GIỮA KỲ

# Travel Mate – Website Đặt Phòng Khách Sạn Trực Tuyến

## Giới thiệu

Đây là đồ án môn Kiểm định phần mềm sử dụng hệ thống Travel Mate làm đối tượng kiểm thử.

Travel Mate là website đặt phòng khách sạn trực tuyến kết nối khách hàng với chủ cơ sở lưu trú và quản trị viên. Hệ thống hỗ trợ tìm kiếm khách sạn, đặt phòng, thanh toán, quản lý lưu trú, đánh giá và quản lý doanh thu.

---

## Mục tiêu kiểm định

- Phân tích yêu cầu phần mềm (SRS).
- Xây dựng kế hoạch kiểm thử (Test Plan).
- Kiểm thử hộp trắng (White-box Testing).
- Kiểm thử hộp đen (Black-box Testing).
- Kiểm thử phân quyền và luồng nghiệp vụ.
- Đánh giá chất lượng phần mềm theo ISO/IEC 25010.
- Tổng hợp và phân tích kết quả kiểm thử.

---

## Công nghệ sử dụng

- Laravel 12
- PHP 8.2+
- MySQL / MariaDB
- Blade Template
- Tailwind CSS
- Vite
- PHPUnit
- Selenium IDE
- XAMPP

---

## Nội dung kiểm định

### Phần I: Software Requirement Specification (SRS)

- Đặc tả yêu cầu phần mềm.
- Mô tả bài toán.
- Use Case Diagram.
- ERD.
- Class Diagram.
- Activity Diagram.
- Sequence Diagram.

### Phần II: Test Plan

- Phạm vi kiểm thử.
- Môi trường kiểm thử.
- Kỹ thuật kiểm thử.
- Bộ Test Case.
- Điều kiện nghiệm thu.

### Phần III: Thực hiện kiểm thử

#### White-box Testing

- Phân tích route.
- Phân tích luồng đăng nhập.
- Phân tích quy trình đặt phòng.
- Phân tích chống overbooking.
- Phân tích thanh toán VNPAY.
- Kiểm thử PHPUnit.

#### Black-box Testing

- Public/Auth.
- Customer.
- Owner.
- Admin.

#### Selenium IDE

- Tự động hóa đăng nhập.

### Phần IV: Report

- Tổng hợp kết quả kiểm thử.
- Phân tích lỗi.
- Đánh giá rủi ro.
- Kết luận.

---

## Kết quả kiểm thử

| Nhóm chức năng | Số test case | Đạt |
|---------------|--------------|-----|
| Public/Auth | 8 | 8 |
| Customer | 18 | 18 |
| Owner | 12 | 12 |
| Admin | 12 | 12 |
| Tổng cộng | 50 | 50 |

Tỷ lệ đạt: 100%.

---

## Kỹ thuật kiểm thử

- Static Review
- White-box Testing
- Black-box Testing
- Boundary Value Analysis
- Equivalence Partitioning
- State Transition Testing
- Role-based Testing

---

## Cấu trúc Repository

```text
kiemdinhgiuaki
│
├── BaoCao/
├── SRS/
├── TestPlan/
├── TestCase/
├── SeleniumIDE/
├── Images/
├── SourceCode/
└── README.md
```

---

## Vai trò hệ thống

- Guest / Public
- Customer
- Owner
- Admin

---

## Hướng dẫn chạy dự án

### Cài đặt thư viện

```bash
composer install
```

### Tạo file môi trường

```bash
copy .env.example .env
```

### Sinh khóa ứng dụng

```bash
php artisan key:generate
```

### Chạy migration

```bash
php artisan migrate
```

### Khởi động server

```bash
php artisan serve
```

Truy cập:

```text
http://127.0.0.1:8000
```

---

## Tài liệu tham khảo

- ISO/IEC 25010
- Laravel Documentation
- PHPUnit Documentation
- Selenium IDE Documentation
- Tài liệu môn Kiểm định phần mềm

---

## License

Dự án được thực hiện phục vụ mục đích học tập môn Kiểm định phần mềm.
