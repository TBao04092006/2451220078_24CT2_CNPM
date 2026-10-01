<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\BorrowTicket;
use App\Models\SystemRule;
use App\Models\Transaction;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;

class ReaderController extends Controller
{
    /**
     * Giao diện chính tra cứu sách, quản lý thẻ và xem lịch sử
     */
    public function index(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->withErrors(['email' => 'Vui lòng đăng nhập để truy cập trang độc giả.']);
        }
        $user = Auth::user();
        
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

        $query = Book::with(['category', 'publisher']);

        // 1. Tìm kiếm đa năng
        if ($request->filled('keyword')) {
            $kw = trim($request->keyword);
            $query->where(function ($q) use ($kw) {
                $q->where('title', 'like', "%{$kw}%")
                  ->orWhere('author', 'like', "%{$kw}%")
                  ->orWhere('isbn', 'like', "%{$kw}%")
                  ->orWhere('shelf_location', 'like', "%{$kw}%")
                  ->orWhere('description', 'like', "%{$kw}%")
                  ->orWhereHas('publisher', function ($pq) use ($kw) {
                      $pq->where('name', 'like', "%{$kw}%");
                  })
                  ->orWhereHas('category', function ($cq) use ($kw) {
                      $cq->where('name', 'like', "%{$kw}%");
                  });
            });
        }

        // 2. Lọc theo Thể loại
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 3. Lọc theo Tình trạng tồn kho
        if ($request->filled('status')) {
            if ($request->status === 'available') {
                $query->where('available_qty', '>', 0);
            } elseif ($request->status === 'low_stock') {
                $query->where('available_qty', '<=', 2)->where('available_qty', '>', 0);
            } elseif ($request->status === 'out_of_stock') {
                $query->where('available_qty', '<=', 0);
            }
        }

        // 4. Lọc theo danh sách Thẻ Tag
        if ($request->filled('selected_tags')) {
            $tagList = array_filter(array_map('trim', explode(',', $request->selected_tags)));
            if (!empty($tagList)) {
                $query->where(function ($q) use ($tagList) {
                    foreach ($tagList as $tag) {
                        $q->orWhere('description', 'like', "%{$tag}%")
                          ->orWhere('title', 'like', "%{$tag}%")
                          ->orWhereHas('category', function ($cq) use ($tag) {
                              $cq->where('name', 'like', "%{$tag}%");
                          });
                    }
                });
            }
        }

        $books = $query->get();
        $categories = Category::all();
        $publishers = Publisher::all();

        $readerId = $user ? $user->id : 1;

        $tickets = collect([]);
        try {
            $ticketQuery = BorrowTicket::where('reader_id', $readerId);
            if (method_exists(BorrowTicket::class, 'book')) {
                $ticketQuery->with(['book']);
            }
            if (method_exists(BorrowTicket::class, 'reader')) {
                $ticketQuery->with(['reader']);
            }
            $tickets = $ticketQuery->orderByDesc('created_at')->get();

            foreach ($tickets as $ticket) {
                if (method_exists($ticket, 'calculateOverdue')) {
                    $ticket->calculateOverdue($rules->fine_per_day);
                    $ticket->save();
                }
            }
        } catch (\Throwable $ticketErr) {
            $tickets = collect([]);
        }

        $totalUnpaidFines = $tickets->where('payment_status', 'unpaid')->sum('fine_amount');

        $cardTransactions = collect([]);
        $allTransactions = collect([]);

        try {
            if (class_exists(Transaction::class)) {
                $txQuery = Transaction::where('reader_id', $readerId);
                if ($user && !empty($user->name)) {
                    $txQuery->orWhere('reader_name', $user->name);
                }

                $allTransactions = $txQuery->orderByDesc('created_at')->get();
                $cardTransactions = $allTransactions->where('type', 'card_renewal');
            }
        } catch (\Throwable $txErr) {
            $cardTransactions = collect([]);
            $allTransactions = collect([]);
        }

        return view('reader.index', compact(
            'user',
            'books',
            'categories',
            'publishers',
            'tickets',
            'cardTransactions',
            'allTransactions',
            'rules',
            'totalUnpaidFines'
        ));
    }

    public function searchBooks(Request $request)
    {
        return $this->index($request);
    }

    /**
     * Độc giả gửi yêu cầu mượn sách trực tuyến
     */
    public function requestBorrow(Request $request)
    {
        try {
            $request->validate([
                'book_id' => 'required|exists:books,id'
            ]);

            $user = Auth::user() ?? User::where('role', 'reader')->first();
            if (!$user) {
                return redirect()->route('login')->with('error', 'Vui lòng đăng nhập tài khoản độc giả để mượn sách.');
            }

            // CHẶN: Nếu chưa được cấp thẻ
            if (empty($user->card_number)) {
                return redirect()->route('reader.index')->with('error', 'Bạn chưa được cấp thẻ! Vui lòng Cấp thẻ Độc Giả');
            }

            if ($user->status === 'locked') {
                return redirect()->route('reader.index')->with('error', 'Thẻ thư viện của bạn hiện đang bị khóa. Vui lòng liên hệ thủ thư để mở khóa.');
            }

            if ($user->card_expiry_date && Carbon::parse($user->card_expiry_date)->isPast()) {
                return redirect()->route('reader.index')->with('error', 'Thẻ thư viện của bạn đã hết hạn. Vui lòng bấm "Gia Hạn Thẻ" trước khi mượn sách.');
            }

            $book = Book::findOrFail($request->book_id);
            if ($book->available_qty <= 0) {
                return redirect()->route('reader.index')->with('error', "Cuốn sách '{$book->title}' hiện đã hết lượt trên kệ.");
            }

            $rules = SystemRule::firstOrCreate([], [
                'max_books_per_loan' => 5,
                'max_loan_days' => 14,
                'fine_per_day' => 5000,
            ]);

            $hasUnpaidFines = BorrowTicket::where('reader_id', $user->id)
                ->where('payment_status', 'unpaid')
                ->exists();
            if ($hasUnpaidFines) {
                return redirect()->route('reader.index')->with('error', 'Bạn còn tiền phạt trễ hạn chưa thanh toán. Vui lòng nộp phạt trước khi mượn sách mới.');
            }

            $activeLoansCount = BorrowTicket::where('reader_id', $user->id)
                ->whereIn('status', ['borrowing', 'pending', 'overdue'])
                ->count();
            $maxBooks = $rules->max_books_per_loan ?? 5;
            if ($activeLoansCount >= $maxBooks) {
                return redirect()->route('reader.index')->with('error', "Bạn đang mượn/chờ nhận {$activeLoansCount}/{$maxBooks} cuốn (đạt hạn mức tối đa).");
            }

            $alreadyBorrowing = BorrowTicket::where('reader_id', $user->id)
                ->where('book_id', $book->id)
                ->whereIn('status', ['borrowing', 'pending', 'overdue'])
                ->exists();
            if ($alreadyBorrowing) {
                return redirect()->route('reader.index')->with('error', "Bạn đã có phiếu đang mượn hoặc đang chờ nhận cuốn '{$book->title}'.");
            }

            $loanDays = $rules->max_loan_days ?? 14;
            $dueDate = Carbon::now()->addDays($loanDays);
            $ticketCode = 'PM-' . date('Ymd') . '-' . str_pad((string)rand(10, 999), 3, '0', STR_PAD_LEFT);

            $ticket = BorrowTicket::create([
                'ticket_code' => $ticketCode,
                'reader_id' => $user->id,
                'book_id' => $book->id,
                'borrow_date' => Carbon::now(),
                'due_date' => $dueDate,
                'status' => 'pending',
                'renew_count' => 0,
                'overdue_days' => 0,
                'fine_amount' => 0,
                'payment_status' => 'none',
                'created_by_staff' => 'Độc giả gửi trực tuyến'
            ]);

            $book->decrement('available_qty');

            try {
                if (class_exists(AuditLog::class)) {
                    AuditLog::create([
                        'operator_name' => $user->name,
                        'operator_role' => 'reader',
                        'action' => 'ONLINE_BORROW_REQUEST',
                        'target_id' => (string)$ticket->id,
                        'details' => "Độc giả {$user->name} đã gửi yêu cầu mượn cuốn '{$book->title}' (Phiếu #{$ticketCode}).",
                        'ip_address' => $request->ip()
                    ]);
                }
            } catch (\Throwable $logEx) {}

            return redirect()->route('reader.index')->with('success', "🎉 Đăng ký mượn sách '{$book->title}' thành công! Thông tin đã được gửi qua quầy Thủ thư (Phiếu #{$ticketCode}). Bạn có thể đến vị trí [{$book->shelf_location}] để nhận sách.");
        } catch (\Throwable $e) {
            return redirect()->route('reader.index')->with('error', 'Lỗi khi gửi yêu cầu mượn sách: ' . $e->getMessage());
        }
    }

    /**
     * Gia hạn lượt mượn sách
     */
    public function renewLoan(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || empty($user->card_number)) {
            return back()->with('error', 'Bạn chưa được cấp thẻ! Vui lòng Cấp thẻ Độc Giả');
        }

        $ticket = BorrowTicket::findOrFail($id);
        $rules = SystemRule::first();
        $maxRenew = $rules ? (int)$rules->max_renewal_times : 2;

        if ($ticket->renew_count >= $maxRenew) {
            return back()->with('error', "Bạn đã đạt giới hạn gia hạn tối đa ({$maxRenew} lần) cho cuốn sách này.");
        }

        if ($ticket->status === 'overdue' && $ticket->fine_amount > 0) {
            return back()->with('error', "Sách đang quá hạn, vui lòng thanh toán phí phạt trước khi gia hạn.");
        }

        $ticket->due_date = Carbon::parse($ticket->due_date)->addDays(7);
        $ticket->renew_count += 1;
        $ticket->save();

        try {
            if (class_exists(AuditLog::class)) {
                AuditLog::create([
                    'operator_name' => Auth::user() ? Auth::user()->name : 'Độc giả',
                    'operator_role' => 'reader',
                    'action' => 'RENEW_LOAN_ONLINE',
                    'target_id' => (string)$ticket->id,
                    'details' => "Độc giả tự gia hạn trực tuyến thêm 7 ngày cho phiếu #{$ticket->ticket_code}.",
                    'ip_address' => $request->ip()
                ]);
            }
        } catch (\Throwable $logEx) {}

        return back()->with('success', "Gia hạn thành công! Hạn trả mới là: " . Carbon::parse($ticket->due_date)->format('d/m/Y'));
    }

    public function rateBook(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $book = Book::findOrFail($request->book_id);
        $oldTotal = $book->rating * ($book->rating_count ?? 0);
        $book->rating_count = ($book->rating_count ?? 0) + 1;
        $book->rating = round(($oldTotal + $request->rating) / $book->rating_count, 1);
        $book->save();

        return back()->with('success', "Cảm ơn bạn đã đánh giá cuốn sách '{$book->title}'!");
    }

    /**
     * Gia hạn thẻ thư viện thường niên
     */
    public function renewCard(Request $request)
    {
        $user = Auth::user() ?? User::where('role', 'reader')->first();
        if (!$user) {
            return back()->with('error', 'Vui lòng đăng nhập để gia hạn thẻ.');
        }

        // CHẶN: Nếu chưa có thẻ thì không thể gia hạn thẻ
        if (empty($user->card_number)) {
            return back()->with('error', 'Bạn chưa được cấp thẻ! Vui lòng Cấp thẻ Độc Giả');
        }

        $rules = SystemRule::first();
        $fee = $rules ? (float)$rules->card_renewal_fee : 30000;

        $baseDate = ($user->card_expiry_date && Carbon::parse($user->card_expiry_date)->isFuture())
            ? Carbon::parse($user->card_expiry_date)
            : Carbon::now();

        $user->card_expiry_date = $baseDate->addYear();
        $user->status = 'active';
        $user->save();

        try {
            if (class_exists(Transaction::class)) {
                Transaction::create([
                    'transaction_code' => 'CARD-' . strtoupper(substr(uniqid(), -6)),
                    'ticket_id' => null,
                    'reader_id' => $user->id,
                    'reader_name' => $user->name,
                    'amount' => $fee,
                    'type' => 'card_renewal',
                    'payment_method' => $request->payment_method ?? 'vietqr',
                    'description' => "Gia hạn thẻ thư viện thường niên 1 năm (Mã thẻ: {$user->card_number})",
                    'status' => 'completed'
                ]);
            }
        } catch (\Throwable $txEx) {}

        return back()->with('success', "Gia hạn thẻ thư viện thành công! Hạn dùng mới đến " . Carbon::parse($user->card_expiry_date)->format('d/m/Y'));
    }
}