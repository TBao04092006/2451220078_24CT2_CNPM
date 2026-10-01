<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\SystemRule;
use App\Models\PurchaseProposal;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\Book;
use App\Models\Category;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $rules = SystemRule::firstOrCreate([], [
            'max_books_per_loan' => 5,
            'max_loan_days' => 14,
            'fine_per_day' => 5000,
            'card_renewal_fee' => 30000,
            'max_renewal_times' => 2,
            'session_timeout_minutes' => 15,
            'max_failed_logins' => 5,
            'bank_name' => 'MB Bank (Ngân hàng Quân Đội)',
            'bank_account' => '0987654321',
            'account_holder' => 'THU VIEN LIBRANOVA QUOC GIA',
        ]);

        $staffUsers = User::whereIn('role', ['librarian', 'admin'])->get();
        $proposals = PurchaseProposal::latest()->get();
        $transactions = Transaction::latest()->get();
        $auditLogs = AuditLog::latest()->take(50)->get();

        // XỬ LÝ KIỂM KÊ & LỌC SỐ LƯỢNG SÁCH
        $bookQuery = Book::with(['category', 'publisher']);
        
        // 1. Lọc từ khóa
        if ($request->filled('book_keyword')) {
            $kw = $request->book_keyword;
            $bookQuery->where(function($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                  ->orWhere('author', 'like', "%{$kw}%")
                  ->orWhere('isbn', 'like', "%{$kw}%")
                  ->orWhere('shelf_location', 'like', "%{$kw}%")
                  ->orWhereHas('publisher', function($pq) use ($kw) {
                      $pq->where('name', 'like', "%{$kw}%");
                  });
            });
        }

        // 2. Lọc theo chuỗi thẻ tag
        if ($request->filled('book_tags')) {
            $tags = array_filter(array_map('trim', explode(',', $request->book_tags)));
            if (!empty($tags)) {
                $bookQuery->where(function($q) use ($tags) {
                    foreach ($tags as $t) {
                        $q->orWhere('tags', 'like', "%{$t}%")
                          ->orWhereHas('category', function($cq) use ($t) {
                              $cq->where('name', 'like', "%{$t}%");
                          });
                    }
                });
            }
        }

        // 3. Lọc theo trạng thái tồn kho
        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'out') {
                $bookQuery->where('available_qty', '<=', 0);
            } elseif ($request->stock_status === 'low') {
                $bookQuery->where('available_qty', '>', 0)->where('available_qty', '<=', 2);
            } elseif ($request->stock_status === 'available') {
                $bookQuery->where('available_qty', '>', 2);
            }
        }

        $books = $bookQuery->latest()->get();
        $categories = Category::all();

        $totalRevenue = $transactions->where('status', 'completed')->sum('amount');
        $vietqrRevenue = $transactions->where('payment_method', 'vietqr')->sum('amount');
        $cashRevenue = $transactions->where('payment_method', 'cash')->sum('amount');
        $walletRevenue = $transactions->whereIn('payment_method', ['momo', 'vnpay'])->sum('amount');

        return view('admin.index', compact(
            'rules',
            'staffUsers',
            'proposals',
            'transactions',
            'auditLogs',
            'books',
            'categories',
            'totalRevenue',
            'vietqrRevenue',
            'cashRevenue',
            'walletRevenue'
        ));
    }

    public function updateRules(Request $request)
    {
        $rules = SystemRule::firstOrCreate([], [
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

        $rules->update([
            'max_books_per_loan' => (int)($request->max_books_per_loan ?? $rules->max_books_per_loan),
            'max_loan_days' => (int)($request->max_loan_days ?? $rules->max_loan_days),
            'fine_per_day' => (float)($request->fine_per_day ?? $rules->fine_per_day),
            'card_renewal_fee' => (float)($request->card_renewal_fee ?? $rules->card_renewal_fee),
            'max_renewal_times' => (int)($request->max_renewal_times ?? $rules->max_renewal_times),
            'session_timeout_minutes' => (int)($request->session_timeout_minutes ?? $rules->session_timeout_minutes),
            'max_failed_logins' => (int)($request->max_failed_logins ?? $rules->max_failed_logins),
            'bank_name' => $request->bank_name ? trim($request->bank_name) : $rules->bank_name,
            'bank_account' => $request->bank_account ? trim($request->bank_account) : $rules->bank_account,
            'account_holder' => $request->account_holder ? strtoupper(trim($request->account_holder)) : $rules->account_holder,
        ]);

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Admin',
            'operator_role' => 'admin',
            'action' => 'UPDATE_SYSTEM_RULES',
            'target_id' => (string)$rules->id,
            'details' => "Cập nhật chính sách mượn trả & thông số bảo mật thư viện (Hạn mượn: {$rules->max_loan_days} ngày, Phạt: " . number_format($rules->fine_per_day) . " đ/ngày)",
            'ip_address' => $request->ip()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật cấu hình quy định thư viện thành công!',
                'rules' => $rules
            ]);
        }

        return back()->with('success', 'Cập nhật cấu hình quy định thư viện thành công!');
    }

    public function storeStaff(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:librarian,admin',
            'card_number' => 'nullable|string|unique:users,card_number',
            'card_expiry_date' => 'nullable|date',
            'phone' => 'nullable|string|max:20',
        ]);

        // Tự động sinh mã thẻ nếu để trống
        $cardNumber = $request->filled('card_number') ? trim($request->card_number) : null;
        if (empty($cardNumber)) {
            $prefix = $request->role === 'admin' ? 'LIB-ADMIN' : 'LIB-STAFF';
            $count = User::where('role', $request->role)->count() + 1;
            $cardNumber = sprintf("%s-%02d", $prefix, $count);
            while (User::where('card_number', $cardNumber)->exists()) {
                $count++;
                $cardNumber = sprintf("%s-%02d", $prefix, $count);
            }
        }

        // Hạn thẻ mặc định 5 năm nếu để trống
        $cardExpiryDate = $request->filled('card_expiry_date') 
            ? $request->card_expiry_date 
            : now()->addYears(5)->format('Y-12-31');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt('123456'),
            'role' => $request->role,
            'card_number' => $cardNumber,
            'card_expiry_date' => $cardExpiryDate,
            'status' => 'active',
            'phone' => $request->phone,
        ]);

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Admin',
            'operator_role' => 'admin',
            'action' => 'CREATE_STAFF_ACCOUNT',
            'target_id' => (string)$user->id,
            'details' => "Tạo mới tài khoản nhân sự: {$user->name} ({$user->role}, Mã thẻ: {$user->card_number}, Hạn: {$user->card_expiry_date})",
            'ip_address' => $request->ip()
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Tạo tài khoản nhân sự '{$user->name}' thành công! Mã thẻ: {$user->card_number}",
                'user' => $user
            ]);
        }

        return back()->with('success', "Tạo tài khoản nhân sự '{$user->name}' thành công! Mã thẻ: {$user->card_number}");
    }

    public function toggleStaffLock(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->status = $user->status === 'locked' ? 'active' : 'locked';
        $user->save();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Admin',
            'operator_role' => 'admin',
            'action' => 'TOGGLE_USER_LOCK',
            'target_id' => (string)$user->id,
            'details' => "Chuyển trạng thái tài khoản {$user->name} thành '{$user->status}'",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Đã thay đổi trạng thái tài khoản thành '{$user->status}'.");
    }

    public function reviewProposal(Request $request, $id)
    {
        $proposal = PurchaseProposal::findOrFail($id);
        $proposal->status = $request->status;
        $proposal->admin_note = $request->admin_note;
        $proposal->save();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Admin',
            'operator_role' => 'admin',
            'action' => 'REVIEW_PROPOSAL',
            'target_id' => (string)$proposal->id,
            'details' => "Admin duyệt đề xuất mua sách: '{$proposal->title}' -> {$proposal->status}",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Đã cập nhật trạng thái phiếu đề xuất thành: {$proposal->status}");
    }

    public function exportBackup()
    {
        $data = [
            'timestamp' => now()->toIso8601String(),
            'books' => Book::all(),
            'users' => User::all(),
            'rules' => SystemRule::first(),
            'transactions' => Transaction::all(),
            'auditLogs' => AuditLog::latest()->take(100)->get()
        ];

        $filename = 'libranova-backup-' . date('Y-m-d_H-i-s') . '.json';
        return response()->streamDownload(function () use ($data) {
            echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $filename, ['Content-Type' => 'application/json']);
    }
}