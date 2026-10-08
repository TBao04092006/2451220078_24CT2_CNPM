# Hệ Thống Quản Lý Thư Viện Tự Động Hóa - QuanLiThuVien (Laravel & MySQL)

Hệ thống quản lý thư viện trực tuyến xây dựng trên nền tảng **Laravel Framework (PHP)** và cơ sở dữ liệu **MySQL Database (`QuanLiThuVien.sql`)**.

## System Architecture & Database Specification

- **Primary Database Engine:** MySQL Database (Engine: InnoDB, Port: 3306)
- **Database Schema File:** `QuanLiThuVien.sql`
- **All Eloquent Models strictly persist to MySQL Database:**
  - `Book.php` -> MySQL table `books`
  - `BorrowTicket.php` -> MySQL table `borrow_tickets`
  - `Transaction.php` -> MySQL table `transactions`
  - `User.php` -> MySQL table `users`
  - `Category.php` -> MySQL table `categories`
  - `Publisher.php` -> MySQL table `publishers`
  - `SystemRule.php` -> MySQL table `system_rules`
  - `AuditLog.php` -> MySQL table `audit_logs`

## Kiến Trúc Hệ Thống

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