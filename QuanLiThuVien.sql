<<<<<<< HEAD
﻿-- ====================================================================
-- HE THONG CO SO DU LIEU QUAN LY THU VIEN LIBRANOVA
-- PHIEN BAN CHUAN CHO MICROSOFT SQL SERVER MANAGEMENT STUDIO (SSMS)
-- ====================================================================

-- 1. TAO DATABASE
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'QuanLyThuVien')
BEGIN
    CREATE DATABASE [QuanLyThuVien];
END
GO

USE [QuanLyThuVien];
GO

-- 2. XOA BANG CU THEO DUNG THU TU NEU DA TON TAI
IF OBJECT_ID('dbo.transactions', 'U') IS NOT NULL DROP TABLE dbo.transactions;
IF OBJECT_ID('dbo.ticket_details', 'U') IS NOT NULL DROP TABLE dbo.ticket_details;
IF OBJECT_ID('dbo.borrow_tickets', 'U') IS NOT NULL DROP TABLE dbo.borrow_tickets;
IF OBJECT_ID('dbo.book_ratings', 'U') IS NOT NULL DROP TABLE dbo.book_ratings;
IF OBJECT_ID('dbo.favorite_books', 'U') IS NOT NULL DROP TABLE dbo.favorite_books;
IF OBJECT_ID('dbo.books', 'U') IS NOT NULL DROP TABLE dbo.books;
IF OBJECT_ID('dbo.categories', 'U') IS NOT NULL DROP TABLE dbo.categories;
IF OBJECT_ID('dbo.users', 'U') IS NOT NULL DROP TABLE dbo.users;
IF OBJECT_ID('dbo.system_rules', 'U') IS NOT NULL DROP TABLE dbo.system_rules;
GO

-- ====================================================================
-- 3. TAO CAC BANG
-- ====================================================================

-- Bang 1: Cau hinh he thong va thong tin ngan hang VietQR
CREATE TABLE dbo.system_rules (
    id INT IDENTITY(1,1) PRIMARY KEY,
    fine_per_day DECIMAL(10,2) NOT NULL DEFAULT 5000.00,
    max_borrow_days INT NOT NULL DEFAULT 14,
    max_borrow_books INT NOT NULL DEFAULT 5,
    annual_fee DECIMAL(10,2) NOT NULL DEFAULT 30000.00,
    bank_name NVARCHAR(100) NOT NULL DEFAULT N'MBBank',
    bank_account NVARCHAR(50) NOT NULL DEFAULT N'0987654321',
    account_holder NVARCHAR(150) NOT NULL DEFAULT N'THU VIEN QUOC GIA',
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Bang 2: Nguoi dung (Admin, Thu thu, Doc gia)
CREATE TABLE dbo.users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    card_number NVARCHAR(50) NULL,
    name NVARCHAR(150) NOT NULL,
    email NVARCHAR(150) NOT NULL UNIQUE,
    phone NVARCHAR(20) NULL,
    address NVARCHAR(255) NULL,
    avatar NVARCHAR(255) NULL,
    password NVARCHAR(255) NOT NULL,
    role NVARCHAR(20) NOT NULL DEFAULT N'reader', -- 'admin', 'librarian', 'reader'
    card_expiry_date DATE NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Chi tao Unique Index cho the thu vien khi khong NULL (tranh loi trung lap NULL trong SQL Server)
CREATE UNIQUE NONCLUSTERED INDEX uq_users_card_number 
ON dbo.users(card_number) 
WHERE card_number IS NOT NULL;
GO

-- Bang 3: Danh muc sach
CREATE TABLE dbo.categories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL,
    description NVARCHAR(255) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Bang 4: Sach
CREATE TABLE dbo.books (
    id INT IDENTITY(1,1) PRIMARY KEY,
    isbn NVARCHAR(30) NULL,
    title NVARCHAR(255) NOT NULL,
    author NVARCHAR(150) NOT NULL,
    publisher NVARCHAR(150) NULL,
    publish_year INT NULL,
    category_id INT NOT NULL,
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    image_url NVARCHAR(255) NULL,
    location NVARCHAR(100) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES dbo.categories(id) ON DELETE CASCADE
);
GO

-- Bang 5: Phieu muon
CREATE TABLE dbo.borrow_tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    reader_id INT NOT NULL,
    borrow_date DATE NOT NULL DEFAULT CAST(GETDATE() AS DATE),
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status NVARCHAR(20) NOT NULL DEFAULT N'borrowed', -- 'pending', 'borrowed', 'returned', 'overdue'
    fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_status NVARCHAR(20) NOT NULL DEFAULT N'unpaid', -- 'unpaid', 'paid'
    note NVARCHAR(255) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_tickets_reader FOREIGN KEY (reader_id) REFERENCES dbo.users(id) ON DELETE CASCADE
);
GO

-- Bang 6: Chi tiet phieu muon
CREATE TABLE dbo.ticket_details (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    book_id INT NOT NULL,
    status NVARCHAR(20) NOT NULL DEFAULT N'borrowed', -- 'borrowed', 'returned', 'lost'
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_details_ticket FOREIGN KEY (ticket_id) REFERENCES dbo.borrow_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_details_book FOREIGN KEY (book_id) REFERENCES dbo.books(id) ON DELETE CASCADE
);
GO

-- Bang 7: Giao dich thanh toan (Khong dung CASCADE de khong bi loi Msg 1785)
CREATE TABLE dbo.transactions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    transaction_code NVARCHAR(50) NOT NULL UNIQUE,
    ticket_id INT NULL,
    reader_id INT NOT NULL,
    reader_name NVARCHAR(150) NULL,
    amount DECIMAL(10,2) NOT NULL,
    type NVARCHAR(50) NOT NULL DEFAULT N'fine', -- 'fine', 'card_renewal'
    payment_method NVARCHAR(50) NOT NULL DEFAULT N'vietqr', -- 'vietqr', 'cash'
    description NVARCHAR(255) NULL,
    status NVARCHAR(20) NOT NULL DEFAULT N'completed', -- 'completed', 'pending', 'failed'
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_transactions_ticket FOREIGN KEY (ticket_id) REFERENCES dbo.borrow_tickets(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_reader FOREIGN KEY (reader_id) REFERENCES dbo.users(id) ON DELETE NO ACTION
);
GO

-- Bang 8: Sach yeu thich
CREATE TABLE dbo.favorite_books (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_book FOREIGN KEY (book_id) REFERENCES dbo.books(id) ON DELETE CASCADE
);
GO

-- Bang 9: Danh gia sach
CREATE TABLE dbo.book_ratings (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment NVARCHAR(500) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_book FOREIGN KEY (book_id) REFERENCES dbo.books(id) ON DELETE CASCADE
);
GO

-- ====================================================================
-- 4. CHEN DU LIEU KHOI TAO MAU
-- ====================================================================

-- 1. Cau hinh he thong
INSERT INTO dbo.system_rules (fine_per_day, max_borrow_days, max_borrow_books, annual_fee, bank_name, bank_account, account_holder) VALUES
(5000.00, 14, 5, 30000.00, N'MBBank', N'0987654321', N'THU VIEN QUOC GIA LIBRANOVA');

-- 2. Nguoi dung mau (Mat khau mau: 123456)
INSERT INTO dbo.users (card_number, name, email, phone, address, password, role, card_expiry_date) VALUES
('LIB-ADMIN-01', N'Quan Tri Vien', 'admin@library.com', '0901234567', N'Ha Noi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2030-12-31'),
('LIB-STAFF-01', N'Thu Thu Nguyen Thi Mai', 'librarian@library.com', '0912345678', N'Da Nang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian', '2030-12-31'),
('LIB-2026-8899', N'Nguyen Van An', 'an.nguyen@gmail.com', '0988776655', N'TP Ho Chi Minh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', '2026-12-31'),
('LIB-2026-7788', N'Tran Thi Bich', 'bich.tran@gmail.com', '0977665544', N'Can Tho', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', '2026-11-20');

-- 3. Danh muc sach
INSERT INTO dbo.categories (name, description) VALUES
(N'Cong nghe thong tin', N'Giao trinh, lap trinh va khoa hoc may tinh'),
(N'Kinh te & Khoi nghiep', N'Quan tri kinh doanh, tai chinh va dau tu'),
(N'Van hoc & Nghe thuat', N'Tieu thuyet, truyen ngan va kien truc'),
(N'Ky nang song', N'Phat trien ban than va tam ly hoc');

-- 4. Sach mau
INSERT INTO dbo.books (isbn, title, author, publisher, publish_year, category_id, total_copies, available_copies, location) VALUES
('978-604-0-12345-1', N'Lap trinh Laravel toan tap tu co ban den nang cao', N'Nguyen Van Khoa', N'NXB Thong Tin & Truyen Thong', 2024, 1, 10, 8, N'Ke A1-02'),
('978-604-0-12345-2', N'Cau truc du lieu va giai thuat hien dai', N'Le Huu Tri', N'NXB Dai Hoc Quoc Gia', 2023, 1, 5, 3, N'Ke A1-03'),
('978-604-2-98765-4', N'Dac nhan tam', N'Dale Carnegie', N'NXB Tre', 2022, 4, 15, 12, N'Ke B2-01'),
('978-604-1-55443-8', N'Nha gia kim', N'Paulo Coelho', N'NXB Hoi Nha Van', 2021, 3, 12, 10, N'Ke C1-05');

-- 5. Phieu muon mau (Co phieu qua han de test dong tien phat)
INSERT INTO dbo.borrow_tickets (reader_id, borrow_date, due_date, return_date, status, fine_amount, payment_status, note) VALUES
(3, DATEADD(DAY, -20, CAST(GETDATE() AS DATE)), DATEADD(DAY, -6, CAST(GETDATE() AS DATE)), NULL, 'overdue', 30000.00, 'unpaid', N'Muon qua han 6 ngay');

-- 6. Chi tiet muon sach
INSERT INTO dbo.ticket_details (ticket_id, book_id, status) VALUES
(1, 1, 'borrowed');

-- 7. Giao dich mau
INSERT INTO dbo.transactions (transaction_code, ticket_id, reader_id, reader_name, amount, type, payment_method, description, status) VALUES
('CARD-2026-001', NULL, 3, N'Nguyen Van An', 30000.00, 'card_renewal', 'vietqr', N'Gia han the doc gia thu vien 1 nam qua VietQR', 'completed');
=======
﻿-- ====================================================================
-- HE THONG CO SO DU LIEU QUAN LY THU VIEN LIBRANOVA
-- PHIEN BAN CHUAN CHO MICROSOFT SQL SERVER MANAGEMENT STUDIO (SSMS)
-- ====================================================================

-- 1. TAO DATABASE
IF NOT EXISTS (SELECT name FROM sys.databases WHERE name = N'QuanLyThuVien')
BEGIN
    CREATE DATABASE [QuanLyThuVien];
END
GO

USE [QuanLyThuVien];
GO

-- 2. XOA BANG CU THEO DUNG THU TU NEU DA TON TAI
IF OBJECT_ID('dbo.transactions', 'U') IS NOT NULL DROP TABLE dbo.transactions;
IF OBJECT_ID('dbo.ticket_details', 'U') IS NOT NULL DROP TABLE dbo.ticket_details;
IF OBJECT_ID('dbo.borrow_tickets', 'U') IS NOT NULL DROP TABLE dbo.borrow_tickets;
IF OBJECT_ID('dbo.book_ratings', 'U') IS NOT NULL DROP TABLE dbo.book_ratings;
IF OBJECT_ID('dbo.favorite_books', 'U') IS NOT NULL DROP TABLE dbo.favorite_books;
IF OBJECT_ID('dbo.books', 'U') IS NOT NULL DROP TABLE dbo.books;
IF OBJECT_ID('dbo.categories', 'U') IS NOT NULL DROP TABLE dbo.categories;
IF OBJECT_ID('dbo.users', 'U') IS NOT NULL DROP TABLE dbo.users;
IF OBJECT_ID('dbo.system_rules', 'U') IS NOT NULL DROP TABLE dbo.system_rules;
GO

-- ====================================================================
-- 3. TAO CAC BANG
-- ====================================================================

-- Bang 1: Cau hinh he thong va thong tin ngan hang VietQR
CREATE TABLE dbo.system_rules (
    id INT IDENTITY(1,1) PRIMARY KEY,
    fine_per_day DECIMAL(10,2) NOT NULL DEFAULT 5000.00,
    max_borrow_days INT NOT NULL DEFAULT 14,
    max_borrow_books INT NOT NULL DEFAULT 5,
    annual_fee DECIMAL(10,2) NOT NULL DEFAULT 30000.00,
    bank_name NVARCHAR(100) NOT NULL DEFAULT N'MBBank',
    bank_account NVARCHAR(50) NOT NULL DEFAULT N'0987654321',
    account_holder NVARCHAR(150) NOT NULL DEFAULT N'THU VIEN QUOC GIA',
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Bang 2: Nguoi dung (Admin, Thu thu, Doc gia)
CREATE TABLE dbo.users (
    id INT IDENTITY(1,1) PRIMARY KEY,
    card_number NVARCHAR(50) NULL,
    name NVARCHAR(150) NOT NULL,
    email NVARCHAR(150) NOT NULL UNIQUE,
    phone NVARCHAR(20) NULL,
    address NVARCHAR(255) NULL,
    avatar NVARCHAR(255) NULL,
    password NVARCHAR(255) NOT NULL,
    role NVARCHAR(20) NOT NULL DEFAULT N'reader', -- 'admin', 'librarian', 'reader'
    card_expiry_date DATE NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Chi tao Unique Index cho the thu vien khi khong NULL (tranh loi trung lap NULL trong SQL Server)
CREATE UNIQUE NONCLUSTERED INDEX uq_users_card_number 
ON dbo.users(card_number) 
WHERE card_number IS NOT NULL;
GO

-- Bang 3: Danh muc sach
CREATE TABLE dbo.categories (
    id INT IDENTITY(1,1) PRIMARY KEY,
    name NVARCHAR(100) NOT NULL,
    description NVARCHAR(255) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Bang 4: Sach
CREATE TABLE dbo.books (
    id INT IDENTITY(1,1) PRIMARY KEY,
    isbn NVARCHAR(30) NULL,
    title NVARCHAR(255) NOT NULL,
    author NVARCHAR(150) NOT NULL,
    publisher NVARCHAR(150) NULL,
    publish_year INT NULL,
    category_id INT NOT NULL,
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    image_url NVARCHAR(255) NULL,
    location NVARCHAR(100) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_books_category FOREIGN KEY (category_id) REFERENCES dbo.categories(id) ON DELETE CASCADE
);
GO

-- Bang 5: Phieu muon
CREATE TABLE dbo.borrow_tickets (
    id INT IDENTITY(1,1) PRIMARY KEY,
    reader_id INT NOT NULL,
    borrow_date DATE NOT NULL DEFAULT CAST(GETDATE() AS DATE),
    due_date DATE NOT NULL,
    return_date DATE NULL,
    status NVARCHAR(20) NOT NULL DEFAULT N'borrowed', -- 'pending', 'borrowed', 'returned', 'overdue'
    fine_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    payment_status NVARCHAR(20) NOT NULL DEFAULT N'unpaid', -- 'unpaid', 'paid'
    note NVARCHAR(255) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_tickets_reader FOREIGN KEY (reader_id) REFERENCES dbo.users(id) ON DELETE CASCADE
);
GO

-- Bang 6: Chi tiet phieu muon
CREATE TABLE dbo.ticket_details (
    id INT IDENTITY(1,1) PRIMARY KEY,
    ticket_id INT NOT NULL,
    book_id INT NOT NULL,
    status NVARCHAR(20) NOT NULL DEFAULT N'borrowed', -- 'borrowed', 'returned', 'lost'
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_details_ticket FOREIGN KEY (ticket_id) REFERENCES dbo.borrow_tickets(id) ON DELETE CASCADE,
    CONSTRAINT fk_details_book FOREIGN KEY (book_id) REFERENCES dbo.books(id) ON DELETE CASCADE
);
GO

-- Bang 7: Giao dich thanh toan (Khong dung CASCADE de khong bi loi Msg 1785)
CREATE TABLE dbo.transactions (
    id INT IDENTITY(1,1) PRIMARY KEY,
    transaction_code NVARCHAR(50) NOT NULL UNIQUE,
    ticket_id INT NULL,
    reader_id INT NOT NULL,
    reader_name NVARCHAR(150) NULL,
    amount DECIMAL(10,2) NOT NULL,
    type NVARCHAR(50) NOT NULL DEFAULT N'fine', -- 'fine', 'card_renewal'
    payment_method NVARCHAR(50) NOT NULL DEFAULT N'vietqr', -- 'vietqr', 'cash'
    description NVARCHAR(255) NULL,
    status NVARCHAR(20) NOT NULL DEFAULT N'completed', -- 'completed', 'pending', 'failed'
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_transactions_ticket FOREIGN KEY (ticket_id) REFERENCES dbo.borrow_tickets(id) ON DELETE SET NULL,
    CONSTRAINT fk_transactions_reader FOREIGN KEY (reader_id) REFERENCES dbo.users(id) ON DELETE NO ACTION
);
GO

-- Bang 8: Sach yeu thich
CREATE TABLE dbo.favorite_books (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_favorites_user FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE,
    CONSTRAINT fk_favorites_book FOREIGN KEY (book_id) REFERENCES dbo.books(id) ON DELETE CASCADE
);
GO

-- Bang 9: Danh gia sach
CREATE TABLE dbo.book_ratings (
    id INT IDENTITY(1,1) PRIMARY KEY,
    user_id INT NOT NULL,
    book_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment NVARCHAR(500) NULL,
    created_at DATETIME DEFAULT GETDATE(),
    CONSTRAINT fk_ratings_user FOREIGN KEY (user_id) REFERENCES dbo.users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ratings_book FOREIGN KEY (book_id) REFERENCES dbo.books(id) ON DELETE CASCADE
);
GO

-- ====================================================================
-- 4. CHEN DU LIEU KHOI TAO MAU
-- ====================================================================

-- 1. Cau hinh he thong
INSERT INTO dbo.system_rules (fine_per_day, max_borrow_days, max_borrow_books, annual_fee, bank_name, bank_account, account_holder) VALUES
(5000.00, 14, 5, 30000.00, N'MBBank', N'0987654321', N'THU VIEN QUOC GIA LIBRANOVA');

-- 2. Nguoi dung mau (Mat khau mau: 123456)
INSERT INTO dbo.users (card_number, name, email, phone, address, password, role, card_expiry_date) VALUES
('LIB-ADMIN-01', N'Quan Tri Vien', 'admin@library.com', '0901234567', N'Ha Noi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '2030-12-31'),
('LIB-STAFF-01', N'Thu Thu Nguyen Thi Mai', 'librarian@library.com', '0912345678', N'Da Nang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'librarian', '2030-12-31'),
('LIB-2026-8899', N'Nguyen Van An', 'an.nguyen@gmail.com', '0988776655', N'TP Ho Chi Minh', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', '2026-12-31'),
('LIB-2026-7788', N'Tran Thi Bich', 'bich.tran@gmail.com', '0977665544', N'Can Tho', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'reader', '2026-11-20');

-- 3. Danh muc sach
INSERT INTO dbo.categories (name, description) VALUES
(N'Cong nghe thong tin', N'Giao trinh, lap trinh va khoa hoc may tinh'),
(N'Kinh te & Khoi nghiep', N'Quan tri kinh doanh, tai chinh va dau tu'),
(N'Van hoc & Nghe thuat', N'Tieu thuyet, truyen ngan va kien truc'),
(N'Ky nang song', N'Phat trien ban than va tam ly hoc');

-- 4. Sach mau
INSERT INTO dbo.books (isbn, title, author, publisher, publish_year, category_id, total_copies, available_copies, location) VALUES
('978-604-0-12345-1', N'Lap trinh Laravel toan tap tu co ban den nang cao', N'Nguyen Van Khoa', N'NXB Thong Tin & Truyen Thong', 2024, 1, 10, 8, N'Ke A1-02'),
('978-604-0-12345-2', N'Cau truc du lieu va giai thuat hien dai', N'Le Huu Tri', N'NXB Dai Hoc Quoc Gia', 2023, 1, 5, 3, N'Ke A1-03'),
('978-604-2-98765-4', N'Dac nhan tam', N'Dale Carnegie', N'NXB Tre', 2022, 4, 15, 12, N'Ke B2-01'),
('978-604-1-55443-8', N'Nha gia kim', N'Paulo Coelho', N'NXB Hoi Nha Van', 2021, 3, 12, 10, N'Ke C1-05');

-- 5. Phieu muon mau (Co phieu qua han de test dong tien phat)
INSERT INTO dbo.borrow_tickets (reader_id, borrow_date, due_date, return_date, status, fine_amount, payment_status, note) VALUES
(3, DATEADD(DAY, -20, CAST(GETDATE() AS DATE)), DATEADD(DAY, -6, CAST(GETDATE() AS DATE)), NULL, 'overdue', 30000.00, 'unpaid', N'Muon qua han 6 ngay');

-- 6. Chi tiet muon sach
INSERT INTO dbo.ticket_details (ticket_id, book_id, status) VALUES
(1, 1, 'borrowed');

-- 7. Giao dich mau
INSERT INTO dbo.transactions (transaction_code, ticket_id, reader_id, reader_name, amount, type, payment_method, description, status) VALUES
('CARD-2026-001', NULL, 3, N'Nguyen Van An', 30000.00, 'card_renewal', 'vietqr', N'Gia han the doc gia thu vien 1 nam qua VietQR', 'completed');
>>>>>>> cb4f14e98ad09f69c44e1bb0112711036c34b2f3
GO