# Hệ Thống Quản Lý Thư Viện Tự Động Hóa - QuanLiThuVien (Laravel & MySQL)

Hệ thống quản lý thư viện trực tuyến xây dựng trên nền tảng **Laravel (PHP)** kết hợp hệ quản trị cơ sở dữ liệu **MySQL / SQL Server (SSMS - `QuanLiThuVien.sql`)**.

## Kiến Trúc Hệ Thống & Cơ Sở Dữ Liệu (MySQL Database)

- **Frontend (Blade Views):**
  - `resources/views/reader/index.blade.php` (Giao diện Độc giả)
  - `resources/views/librarian/index.blade.php` (Giao diện Thủ thư)
  - `resources/views/admin/index.blade.php` (Giao diện Quản trị viên)
  - `resources/views/auth/login.blade.php` (Giao diện Đăng nhập & Đăng ký)
- **Backend (Laravel Controllers & Routes):**
  - `routes/web.php`
  - `app/Http/Controllers/ReaderController.php`
  - `app/Http/Controllers/LibrarianController.php`
  - `app/Http/Controllers/AdminController.php`
  - `app/Http/Controllers/PaymentController.php`
  - `app/Http/Controllers/AuthController.php`
- **Database Engine: MySQL Database (`QuanLiThuVien.sql` & `config/database.php`):**
  - Hệ quản trị cơ sở dữ liệu **MySQL / SQL Server (SSMS)** lưu trữ toàn bộ bảng dữ liệu trong file `QuanLiThuVien.sql` và cấu hình tại `config/database.php`.
  - Tất cả các Eloquent Models trong `app/Models/` đều kết nối và truy vấn trực tiếp vào **MySQL Database (`QuanLiThuVien.sql`)**:
    - `app/Models/Book.php` (Bảng `books`)
    - `app/Models/BorrowTicket.php` (Bảng `borrow_tickets`)
    - `app/Models/Transaction.php` (Bảng `transactions`)
    - `app/Models/User.php` (Bảng `users`)
    - `app/Models/Category.php` (Bảng `categories`)
    - `app/Models/Publisher.php` (Bảng `publishers`)
    - `app/Models/SystemRule.php` (Bảng `system_rules`)
    - `app/Models/AuditLog.php` (Bảng `audit_logs`)