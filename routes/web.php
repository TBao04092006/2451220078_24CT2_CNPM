<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ReaderController;
use App\Http\Controllers\LibrarianController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PaymentController;

/*
|--------------------------------------------------------------------------
| Web Routes - Hệ Thống Quản Lý Thư Viện LibraNova
|--------------------------------------------------------------------------
*/

// ================= 1. AUTHENTICATION ROUTES =================
Route::get('/', [AuthController::class, 'showLoginForm'])->name('home');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
Route::post('/forgot-password', [AuthController::class, 'resetPasswordDirect'])->name('password.reset.direct');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');


// ================= 2. READER ROUTES (Phân hệ Độc Giả) =================
Route::prefix('reader')->name('reader.')->group(function () {
    Route::get('/', [ReaderController::class, 'index'])->name('index');
    Route::get('/books', [ReaderController::class, 'searchBooks'])->name('books.search');
    
    // Độc giả gửi yêu cầu mượn sách trực tuyến (Đã bổ sung để sửa lỗi 500)
    Route::post('/borrow', [ReaderController::class, 'requestBorrow'])->name('borrow');
    
    Route::post('/renew-loan/{id}', [ReaderController::class, 'renewLoan'])->name('loan.renew');
    Route::post('/rate-book', [ReaderController::class, 'rateBook'])->name('book.rate');
    Route::post('/renew-card', [ReaderController::class, 'renewCard'])->name('card.renew');
});


// ================= 3. LIBRARIAN ROUTES (Phân hệ Thủ Thư) =================
Route::prefix('librarian')->name('librarian.')->group(function () {
    Route::get('/', [LibrarianController::class, 'index'])->name('index');

    // Quản lý kho sách (Thêm, Sửa, Xóa)
    Route::post('/books', [LibrarianController::class, 'storeBook'])->name('books.store');
    Route::put('/books/{id}', [LibrarianController::class, 'updateBook'])->name('books.update');
    Route::delete('/books/{id}', [LibrarianController::class, 'destroyBook'])->name('books.destroy');

    // Quản lý độc giả (Cấp thẻ, Gia hạn, Khóa/Mở)
    Route::post('/readers', [LibrarianController::class, 'storeReader'])->name('readers.store');
    Route::post('/readers/{id}/renew', [LibrarianController::class, 'renewReaderCard'])->name('readers.renew');
    Route::post('/readers/{id}/toggle-lock', [LibrarianController::class, 'toggleReaderLock'])->name('readers.toggle-lock');

    // Quản lý Mượn & Trả sách
    Route::post('/tickets/borrow', [LibrarianController::class, 'issueBorrowTicket'])->name('tickets.borrow');
    Route::post('/borrow', [LibrarianController::class, 'createLoan'])->name('borrow');
    Route::post('/tickets/{id}/approve', [LibrarianController::class, 'approveBorrowTicket'])->name('tickets.approve');
    Route::post('/tickets/{id}/reject', [LibrarianController::class, 'rejectBorrowTicket'])->name('tickets.reject');
    Route::post('/tickets/{id}/return', [LibrarianController::class, 'processReturnTicket'])->name('tickets.return');
    Route::post('/return/{ticketId}', [LibrarianController::class, 'returnBook'])->name('return');
    Route::post('/tickets/{id}/renew', [LibrarianController::class, 'renewTicket'])->name('tickets.renew');

    // Nhắc nhở độc giả
    Route::post('/notifications/send', [LibrarianController::class, 'sendReminder'])->name('notifications.send');

    // Đề xuất mua sách lên Quản trị viên
    Route::post('/proposals', [LibrarianController::class, 'storeProposal'])->name('proposals.store');
});


// ================= 4. ADMIN ROUTES (Phân hệ Quản Trị Viên) =================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('index');
    Route::post('/rules', [AdminController::class, 'updateRules'])->name('rules.update');
    Route::post('/users', [AdminController::class, 'storeStaff'])->name('users.store');
    Route::post('/users/{id}/toggle-lock', [AdminController::class, 'toggleStaffLock'])->name('users.toggle-lock');
    Route::post('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle');
    Route::post('/proposals/{id}/review', [AdminController::class, 'reviewProposal'])->name('proposals.review');
    Route::post('/proposals/{id}/approve', [AdminController::class, 'approveProposal'])->name('proposals.approve');
    Route::post('/proposals/{id}/reject', [AdminController::class, 'rejectProposal'])->name('proposals.reject');
    Route::get('/backup', [AdminController::class, 'exportBackup'])->name('backup.export');
    Route::post('/restore', [AdminController::class, 'restoreDatabase'])->name('backup.restore');
});


// ================= 5. VIETQR & PAYMENT ROUTES =================
Route::prefix('payment')->name('payment.')->group(function () {
    Route::post('/generate-vietqr', [PaymentController::class, 'generateVietQR'])->name('vietqr.generate');
    Route::post('/confirm', [PaymentController::class, 'confirmPayment'])->name('confirm');
});

Route::post('/report-admin', function (\Illuminate\Http\Request $request) {
    $info = $request->input('reporter_info');
    $issue = $request->input('issue_type');
    $reason = $request->input('custom_reason');

    $details = ($issue === 'Lý do khác') ? $reason : $issue;

    try {
        if (class_exists(\App\Models\AuditLog::class)) {
            \App\Models\AuditLog::create([
                'operator_name' => $info ?? 'Khách / Độc giả',
                'operator_role' => 'guest',
                'action' => 'REPORT_TO_ADMIN',
                'target_id' => 'ADMIN',
                'details' => "Báo cáo sự cố: [{$issue}] - Nội dung: {$details}",
                'ip_address' => $request->ip()
            ]);
        }
    } catch (\Throwable $e) {}

    return back()->with('success', 'Báo cáo sự cố của bạn đã được gửi trực tiếp đến Quản trị viên (Admin) thành công!');
})->name('report.admin');

// Route Độc Giả: Trả sách & nộp phạt
Route::middleware(['auth'])->group(function () {
    Route::post('/reader/tickets/{id}/return', [ReaderController::class, 'returnBook'])->name('reader.tickets.return');
    Route::post('/payment/fine/{id}/confirm', [PaymentController::class, 'confirmFinePayment'])->name('payment.fine.confirm');
});

// Route Thủ Thư: Xác nhận thu hồi sách
Route::middleware(['auth'])->group(function () {
    Route::post('/librarian/tickets/{id}/confirm-return', [LibrarianController::class, 'confirmReturn'])->name('librarian.tickets.confirm-return');
});