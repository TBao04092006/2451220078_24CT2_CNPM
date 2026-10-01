<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Book;
use App\Models\SystemRule;
use App\Models\BorrowTicket;
use Carbon\Carbon;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Seed System Rules
        SystemRule::create([
            'max_books_per_loan' => 5,
            'max_loan_days' => 14,
            'fine_per_day' => 5000,
            'card_renewal_fee' => 30000,
            'max_renewal_times' => 2,
            'session_timeout_minutes' => 15,
            'max_failed_logins' => 5,
            'bank_name' => 'MB Bank (Ngân hàng Quân Đội)',
            'bank_account' => '0987654321',
            'account_holder' => 'THU VIEN LIBRANOVA QUOC GIA'
        ]);

        // 2. Seed Default Accounts
        $reader = User::create([
            'name' => 'Nguyễn Văn An',
            'email' => 'docgia@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'reader',
            'card_number' => 'LIB-2026-8899',
            'card_expiry_date' => Carbon::now()->addMonths(8),
            'status' => 'active',
            'phone' => '0901234567',
            'address' => 'Số 12 Phố Cổ, Q. Hoàn Kiếm, Hà Nội'
        ]);

        $librarian = User::create([
            'name' => 'Trần Thu Thư',
            'email' => 'thuthu@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'librarian',
            'status' => 'active',
            'phone' => '0912345678',
            'address' => 'Thư viện LibraNova Trụ Sở Chính'
        ]);

        $admin = User::create([
            'name' => 'Phạm Quang Admin',
            'email' => 'admin@gmail.com',
            'password' => Hash::make('123456'),
            'role' => 'admin',
            'status' => 'active',
            'phone' => '0988888888',
            'address' => 'Ban Giám Đốc Thư viện LibraNova'
        ]);

        // 3. Seed Categories
        $cat1 = Category::create(['name' => 'Văn Học & Nghệ Thuật Á Đông', 'code' => 'LIT-ORIENTAL']);
        $cat2 = Category::create(['name' => 'Khoa Học & Trí Tuệ Nhân Tạo', 'code' => 'SCI-TECH']);
        $cat3 = Category::create(['name' => 'Lịch Sử & Văn Hóa Dân Tộc', 'code' => 'HIST-VN']);
        $cat4 = Category::create(['name' => 'Kinh Tế & Phát Triển Bền Vững', 'code' => 'ECON-BIZ']);

        // 4. Seed Publishers
        $pub1 = Publisher::create(['name' => 'NXB Khoa Học Xã Hội', 'address' => 'Hà Nội']);
        $pub2 = Publisher::create(['name' => 'NXB Trẻ', 'address' => 'TP. Hồ Chí Minh']);
        $pub3 = Publisher::create(['name' => 'NXB Kim Đồng', 'address' => 'Hà Nội']);
        $pub4 = Publisher::create(['name' => 'NXB Đại Học Quốc Gia', 'address' => 'Hà Nội']);

        // 5. Seed Books with Exact Shelf Locations
        $b1 = Book::create([
            'isbn' => '978-604-58-1234-1',
            'title' => 'Đại Việt Sử Ký Toàn Thư',
            'author' => 'Ngô Sĩ Liên & Sử quán Hậu Lê',
            'category_id' => $cat3->id,
            'publisher_id' => $pub1->id,
            'publish_year' => 2021,
            'total_qty' => 8,
            'available_qty' => 5,
            'shelf_location' => 'Kệ A1 - Tầng 1 - Ngăn Lịch Sử Cổ Trung Đại',
            'description' => 'Bộ quốc sử đồ sộ ghi chép dòng lịch sử hào hùng của dân tộc Việt Nam.',
            'cover_url' => 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=400',
            'rating' => 4.9,
            'rating_count' => 28
        ]);

        $b2 = Book::create([
            'isbn' => '978-604-98-5678-2',
            'title' => 'Thiền Uyển Tập Anh',
            'author' => 'Khuyết danh thời Trần',
            'category_id' => $cat1->id,
            'publisher_id' => $pub2->id,
            'publish_year' => 2020,
            'total_qty' => 5,
            'available_qty' => 3,
            'shelf_location' => 'Kệ B2 - Tầng 2 - Ngăn Triết Lý Phật Giáo',
            'description' => 'Tác phẩm văn học - triết học Phật giáo quý báu thời Lý - Trần.',
            'cover_url' => 'https://images.unsplash.com/photo-1512820790803-83ca734da794?auto=format&fit=crop&q=80&w=400',
            'rating' => 4.8,
            'rating_count' => 19
        ]);

        $b3 = Book::create([
            'isbn' => '978-604-20-8910-3',
            'title' => 'Trí Tuệ Nhân Tạo & Chuyển Đổi Số Hiện Đại',
            'author' => 'TS. Trần Minh Khoa',
            'category_id' => $cat2->id,
            'publisher_id' => $pub4->id,
            'publish_year' => 2024,
            'total_qty' => 6,
            'available_qty' => 0, // Out of stock to test filter
            'shelf_location' => 'Kệ C1 - Tầng 3 - Ngăn Công Nghệ Số',
            'description' => 'Nghiên cứu kiến trúc Generative AI, Machine Learning ứng dụng thực tế.',
            'cover_url' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&q=80&w=400',
            'rating' => 5.0,
            'rating_count' => 35
        ]);

        // 6. Seed Sample Active Borrow Tickets
        BorrowTicket::create([
            'ticket_code' => 'PM-20260901-01',
            'reader_id' => $reader->id,
            'book_id' => $b1->id,
            'borrow_date' => Carbon::now()->subDays(5),
            'due_date' => Carbon::now()->addDays(9),
            'status' => 'borrowing',
            'renew_count' => 0,
            'created_by_staff' => 'Trần Thu Thư'
        ]);

        // One ticket that is overdue to test VietQR payment
        BorrowTicket::create([
            'ticket_code' => 'PM-20260815-08',
            'reader_id' => $reader->id,
            'book_id' => $b2->id,
            'borrow_date' => Carbon::now()->subDays(20),
            'due_date' => Carbon::now()->subDays(6), // 6 days overdue
            'status' => 'overdue',
            'overdue_days' => 6,
            'fine_amount' => 30000, // 6 * 5000đ
            'payment_status' => 'unpaid',
            'created_by_staff' => 'Trần Thu Thư'
        ]);
    }
}
