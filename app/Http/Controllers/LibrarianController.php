<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\User;
use App\Models\BorrowTicket;
use App\Models\PurchaseProposal;
use App\Models\SystemRule;
use App\Models\Transaction;
use App\Models\AuditLog;
use Carbon\Carbon;

class LibrarianController extends Controller
{
    /**
     * Hiển thị bảng điều khiển vận hành của Thủ thư
     */
    public function index()
    {
        $books = Book::with(['category', 'publisher'])->latest()->get();
        $categories = Category::all();
        $publishers = Publisher::all();
        $readers = User::where('role', 'reader')->latest()->get();
        $tickets = BorrowTicket::with(['reader', 'book'])->latest()->get();
        $proposals = PurchaseProposal::latest()->get();
        $rules = SystemRule::first() ?? new SystemRule();

        // Cập nhật phạt trễ hạn tự động cho các phiếu chưa trả
        foreach ($tickets as $t) {
            if ($t->status !== 'returned' && method_exists($t, 'calculateOverdue')) {
                $t->calculateOverdue($rules->fine_per_day ?? 5000);
                $t->save();
            }
        }

        return view('librarian.index', compact(
            'books',
            'categories',
            'publishers',
            'readers',
            'tickets',
            'proposals',
            'rules'
        ));
    }

    /**
     * Thêm đầu sách mới
     */
    public function storeBook(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'category_name' => 'nullable|string',
            'category_id' => 'nullable',
            'publisher_name' => 'nullable|string',
            'publisher_id' => 'nullable',
            'total_qty' => 'required|integer|min:1',
            'shelf_location' => 'required|string',
            'cover_image' => 'nullable|image|max:5120',
        ]);

        $publisherId = $request->publisher_id;
        if (!empty($request->publisher_name)) {
            $pub = Publisher::firstOrCreate(
                ['name' => trim($request->publisher_name)],
                ['address' => 'Việt Nam', 'email' => null, 'phone' => null]
            );
            $publisherId = $pub->id;
        } elseif (empty($publisherId)) {
            $defaultPub = Publisher::firstOrCreate(['name' => 'NXB Tri Thức']);
            $publisherId = $defaultPub->id;
        }

        $categoryId = $request->category_id;
        if (!empty($request->category_name)) {
            $cat = Category::firstOrCreate(
                ['name' => trim($request->category_name)],
                ['code' => 'CAT-' . strtoupper(substr(md5($request->category_name), 0, 6))]
            );
            $categoryId = $cat->id;
        } elseif (empty($categoryId)) {
            $defaultCat = Category::firstOrCreate(['name' => 'Tổng Hợp'], ['code' => 'TONG-HOP']);
            $categoryId = $defaultCat->id;
        }

        $description = $request->description ?? '';
        if (!empty($request->selected_tags)) {
            $tagText = "Thẻ thể loại chi tiết: " . $request->selected_tags;
            $description = !empty($description) ? ($description . " | " . $tagText) : $tagText;
        }

        $coverUrl = $request->cover_url;
        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $destinationPath = public_path('uploads/covers');
            if (!file_exists($destinationPath)) {
                @mkdir($destinationPath, 0755, true);
            }
            $file->move($destinationPath, $filename);
            $coverUrl = '/uploads/covers/' . $filename;
        } elseif ($request->filled('cover_base64')) {
            $coverUrl = $request->cover_base64;
        }

        if (empty($coverUrl)) {
            $coverUrl = 'https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=400';
        }

        $book = Book::create([
            'isbn' => $request->isbn ?: ('978-604-' . rand(10, 99) . '-' . rand(1000, 9999) . '-' . rand(1, 9)),
            'title' => $request->title,
            'author' => $request->author,
            'category_id' => $categoryId,
            'publisher_id' => $publisherId,
            'publish_year' => $request->publish_year ?: date('Y'),
            'total_qty' => $request->total_qty,
            'available_qty' => $request->total_qty,
            'shelf_location' => $request->shelf_location,
            'description' => $description,
            'cover_url' => $coverUrl,
            'rating' => 5.0,
            'rating_count' => 1
        ]);

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'ADD_BOOK',
            'target_id' => (string)$book->id,
            'details' => "Thêm mới đầu sách '{$book->title}' (SL: {$book->total_qty}, Kệ: {$book->shelf_location})",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Thêm đầu sách '{$book->title}' thành công!");
    }

    public function updateBook(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $book->update($request->all());

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'UPDATE_BOOK',
            'target_id' => (string)$book->id,
            'details' => "Cập nhật thông tin/vị trí kệ sách '{$book->title}'",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Cập nhật thông tin sách thành công!");
    }

    public function destroyBook(Request $request, $id)
    {
        $book = Book::findOrFail($id);
        $title = $book->title;
        $book->delete();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'DELETE_BOOK',
            'target_id' => (string)$id,
            'details' => "Xóa đầu sách '{$title}' khỏi kho",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Đã xóa đầu sách '{$title}' khỏi hệ thống.");
    }

    /**
     * CẤP THẺ ĐỘC GIẢ: Bắt buộc đối soát đúng cả Email và Số điện thoại đã đăng ký
     */
    public function storeReader(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'phone' => 'required|string',
        ]);

        $inputPhone = trim($request->phone);
        $cleanPhone = preg_replace('/[^0-9]/', '', $inputPhone);
        $normalizedPhone = ltrim($cleanPhone, '0');

        // Tìm kiếm độc giả theo đúng Email và Số điện thoại đã đăng ký
        $reader = User::where('role', 'reader')
            ->where('email', trim($request->email))
            ->where(function($q) use ($inputPhone, $cleanPhone, $normalizedPhone) {
                $q->where('phone', $inputPhone)
                  ->orWhere('phone', $cleanPhone)
                  ->orWhere('phone', '0' . $normalizedPhone)
                  ->orWhere('phone', '+84' . $normalizedPhone)
                  ->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '.', '') = ?", [$cleanPhone]);
            })
            ->first();

        // 1. Nếu không tìm thấy hoặc sai thông tin
        if (!$reader) {
            return back()->with('error', "❌ Không tìm thấy tài khoản độc giả khớp với Email [{$request->email}] và Số điện thoại [{$request->phone}]! Vui lòng kiểm tra lại thông tin hoặc yêu cầu khách hàng đăng ký tài khoản trước.")->withInput();
        }

        // 2. Nếu tài khoản này đã có thẻ từ trước
        if (!empty($reader->card_number)) {
            $expiryStr = $reader->card_expiry_date ? Carbon::parse($reader->card_expiry_date)->format('d/m/Y') : 'Chưa hết hạn';
            return back()->with('info', "ℹ️ Tài khoản độc giả [{$reader->name}] ({$reader->email}) đã được cấp thẻ trước đó (Mã thẻ: {$reader->card_number}, Hạn dùng: {$expiryStr}).");
        }

        // 3. Khớp thông tin và chưa có thẻ -> Cấp mã thẻ mới
        $cardNumber = 'LIB-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        while (User::where('card_number', $cardNumber)->exists()) {
            $cardNumber = 'LIB-' . date('Y') . '-' . str_pad((string)rand(1000, 9999), 4, '0', STR_PAD_LEFT);
        }

        $reader->card_number = $cardNumber;
        $reader->card_expiry_date = Carbon::now()->addYear();
        $reader->status = 'active';
        if (!empty($request->name) && empty($reader->name)) {
            $reader->name = trim($request->name);
        }
        $reader->save();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'ISSUE_READER_CARD',
            'target_id' => (string)$reader->id,
            'details' => "Đối soát thành công (Email: {$reader->email}, SĐT: {$reader->phone}) và cấp mã thẻ #{$cardNumber} cho độc giả {$reader->name}",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "🎉 Cấp thẻ độc giả thành công! Tài khoản [{$reader->name}] (Email: {$reader->email} - SĐT: {$reader->phone}) đã được cấp Mã thẻ: {$cardNumber}.");
    }

    public function renewReaderCard(Request $request, $id)
    {
        $reader = User::findOrFail($id);
        $rules = SystemRule::first();
        $fee = $rules ? $rules->card_renewal_fee : 30000;

        $baseDate = ($reader->card_expiry_date && Carbon::parse($reader->card_expiry_date)->isFuture())
            ? Carbon::parse($reader->card_expiry_date)
            : Carbon::now();

        $reader->card_expiry_date = $baseDate->addYear();
        $reader->status = 'active';
        $reader->save();

        Transaction::create([
            'transaction_code' => 'CARD-' . strtoupper(substr(uniqid(), -6)),
            'ticket_id' => null,
            'reader_id' => $reader->id,
            'reader_name' => $reader->name,
            'amount' => $fee,
            'type' => 'card_renewal',
            'payment_method' => $request->payment_method ?? 'cash',
            'description' => "Gia hạn thẻ độc giả #{$reader->card_number} (Thủ thư thực hiện)",
            'status' => 'completed'
        ]);

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'RENEW_READER_CARD',
            'target_id' => (string)$reader->id,
            'details' => "Gia hạn thẻ cho {$reader->name} đến " . Carbon::parse($reader->card_expiry_date)->format('d/m/Y'),
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Gia hạn thẻ cho độc giả {$reader->name} thành công!");
    }

    public function toggleReaderLock(Request $request, $id)
    {
        $reader = User::findOrFail($id);
        $reader->status = $reader->status === 'locked' ? 'active' : 'locked';
        $reader->save();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'TOGGLE_READER_LOCK',
            'target_id' => (string)$reader->id,
            'details' => "Đổi trạng thái thẻ độc giả {$reader->name} thành: {$reader->status}",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Đã " . ($reader->status === 'active' ? 'mở khóa' : 'khóa') . " thẻ độc giả {$reader->name}!");
    }

    /**
     * Lập phiếu mượn trực tiếp tại quầy
     */
    public function issueBorrowTicket(Request $request)
    {
        $request->validate([
            'reader_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
        ]);

        $reader = User::findOrFail($request->reader_id);
        $book = Book::findOrFail($request->book_id);
        $rules = SystemRule::first() ?? new SystemRule();

        if (empty($reader->card_number)) {
            return back()->with('error', 'Tài khoản độc giả chưa được cấp thẻ thư viện. Vui lòng cấp thẻ trước.');
        }

        if ($reader->status === 'locked') {
            return back()->with('error', 'Thẻ độc giả đang bị khóa, không thể lập phiếu mượn.');
        }

        if ($book->available_qty <= 0) {
            return back()->with('error', "Đầu sách '{$book->title}' hiện đã hết lượt mượn trong kho.");
        }

        $activeLoansCount = BorrowTicket::where('reader_id', $reader->id)
            ->whereIn('status', ['borrowing', 'overdue'])
            ->count();

        $maxBooks = $rules->max_books_per_loan ?? 5;
        if ($activeLoansCount >= $maxBooks) {
            return back()->with('error', "Độc giả đã mượn {$activeLoansCount}/{$maxBooks} cuốn (đạt giới hạn tối đa).");
        }

        $hasUnpaidFines = BorrowTicket::where('reader_id', $reader->id)
            ->where('payment_status', 'unpaid')
            ->exists();
        if ($hasUnpaidFines) {
            return back()->with('error', "Độc giả còn tiền phạt trễ hạn chưa thanh toán, vui lòng xử lý trước khi mượn mới.");
        }

        $loanDays = $rules->max_loan_days ?? 14;
        $dueDate = Carbon::now()->addDays($loanDays);
        $ticketCode = 'PM-' . date('Ymd') . '-' . str_pad((string)rand(10, 999), 3, '0', STR_PAD_LEFT);

        $ticket = BorrowTicket::create([
            'ticket_code' => $ticketCode,
            'reader_id' => $reader->id,
            'book_id' => $book->id,
            'borrow_date' => Carbon::now(),
            'due_date' => $dueDate,
            'status' => 'borrowing',
            'renew_count' => 0,
            'overdue_days' => 0,
            'fine_amount' => 0,
            'payment_status' => 'none',
            'created_by_staff' => Auth::user() ? Auth::user()->name : 'Thủ thư'
        ]);

        $book->decrement('available_qty');

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'ISSUE_BORROW_TICKET',
            'target_id' => (string)$ticket->id,
            'details' => "Lập phiếu mượn #{$ticketCode} cho {$reader->name}, mượn cuốn '{$book->title}'",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Lập phiếu mượn #{$ticketCode} thành công! Hạn trả: " . $dueDate->format('d/m/Y'));
    }

    public function createLoan(Request $request)
    {
        return $this->issueBorrowTicket($request);
    }

    public function approveBorrowTicket(Request $request, $id)
    {
        $ticket = BorrowTicket::with(['book', 'reader'])->findOrFail($id);
        $ticket->status = 'borrowing';
        $ticket->created_by_staff = Auth::user() ? Auth::user()->name : 'Thủ thư';
        $ticket->save();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'APPROVE_BORROW_TICKET',
            'target_id' => (string)$ticket->id,
            'details' => "Thủ thư đã duyệt & bàn giao sách '{$ticket->book->title}' cho độc giả {$ticket->reader->name} (Phiếu #{$ticket->ticket_code})",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Xác nhận duyệt & bàn giao sách '{$ticket->book->title}' cho độc giả {$ticket->reader->name} thành công!");
    }

    public function rejectBorrowTicket(Request $request, $id)
    {
        $ticket = BorrowTicket::with(['book', 'reader'])->findOrFail($id);
        if ($ticket->status === 'pending') {
            $ticket->status = 'cancelled';
            $ticket->save();

            if ($ticket->book) {
                $ticket->book->increment('available_qty');
            }

            AuditLog::create([
                'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
                'operator_role' => 'librarian',
                'action' => 'REJECT_BORROW_TICKET',
                'target_id' => (string)$ticket->id,
                'details' => "Thủ thư đã hủy yêu cầu mượn cuốn '{$ticket->book->title}' của độc giả {$ticket->reader->name} (Phiếu #{$ticket->ticket_code})",
                'ip_address' => $request->ip()
            ]);

            return back()->with('success', "Đã từ chối phiếu mượn #{$ticket->ticket_code} và hoàn lại 1 cuốn vào kho.");
        }

        return back()->with('error', "Phiếu mượn không ở trạng thái chờ duyệt.");
    }

    public function renewTicket(Request $request, $id)
    {
        $ticket = BorrowTicket::findOrFail($id);
        $rules = SystemRule::first();
        $maxRenew = $rules ? $rules->max_renewal_times : 2;

        if ($ticket->renew_count >= $maxRenew) {
            return back()->with('error', "Phiếu mượn đã đạt giới hạn gia hạn tối đa ({$maxRenew} lần).");
        }

        $ticket->due_date = Carbon::parse($ticket->due_date)->addDays(7);
        $ticket->renew_count += 1;
        $ticket->save();

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'RENEW_LOAN_TICKET',
            'target_id' => (string)$ticket->id,
            'details' => "Thủ thư gia hạn phiếu #{$ticket->ticket_code} thêm 7 ngày (Lần {$ticket->renew_count})",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Gia hạn phiếu mượn #{$ticket->ticket_code} thành công! Hạn mới: " . Carbon::parse($ticket->due_date)->format('d/m/Y'));
    }

    public function processReturnTicket(Request $request, $id)
    {
        $ticket = BorrowTicket::with('book')->findOrFail($id);
        $book = $ticket->book;
        $rules = SystemRule::first();
        $finePerDay = $rules ? $rules->fine_per_day : 5000;

        $ticket->calculateOverdue($finePerDay);
        $ticket->return_date = Carbon::now();
        $ticket->status = 'returned';

        if ($ticket->fine_amount > 0) {
            $ticket->payment_status = 'paid';
            $ticket->payment_method = $request->payment_method ?? 'cash';

            Transaction::create([
                'transaction_code' => 'FINE-' . strtoupper(substr(uniqid(), -6)),
                'ticket_id' => $ticket->id,
                'reader_id' => $ticket->reader_id,
                'reader_name' => $ticket->reader->name ?? 'Độc giả',
                'amount' => $ticket->fine_amount,
                'type' => 'fine',
                'payment_method' => $ticket->payment_method,
                'description' => "Thu phạt trễ hạn {$ticket->overdue_days} ngày (Phiếu #{$ticket->ticket_code})",
                'status' => 'completed'
            ]);
        }

        $ticket->save();

        if ($book) {
            $book->increment('available_qty');
        }

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'PROCESS_RETURN_TICKET',
            'target_id' => (string)$ticket->id,
            'details' => "Xử lý trả sách phiếu #{$ticket->ticket_code}. Tiền phạt: " . number_format($ticket->fine_amount) . "đ",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Xử lý trả sách thành công! Đã hoàn tồn kho cuốn '{$book->title}'.");
    }

    public function returnBook(Request $request, $ticketId)
    {
        return $this->processReturnTicket($request, $ticketId);
    }

    public function sendReminder(Request $request)
    {
        $ticketId = $request->ticket_id;
        $ticket = BorrowTicket::with(['reader', 'book'])->findOrFail($ticketId);

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'SEND_REMINDER',
            'target_id' => (string)$ticket->id,
            'details' => "Gửi thông báo nhắc trả sách '{$ticket->book->title}' tới độc giả {$ticket->reader->name} ({$ticket->reader->email})",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Đã gửi thông báo nhắc trả sách thành công tới độc giả {$ticket->reader->name}!");
    }

    public function storeProposal(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'author' => 'required|string',
            'suggested_qty' => 'required|integer|min:1',
            'reason' => 'required|string'
        ]);

        $proposal = PurchaseProposal::create([
            'title' => $request->title,
            'author' => $request->author,
            'category_name' => $request->category_name ?: 'Sách tham khảo',
            'reason' => $request->reason,
            'suggested_qty' => $request->suggested_qty,
            'estimated_price' => $request->estimated_price ?: 150000,
            'librarian_name' => Auth::user() ? Auth::user()->name : 'Trần Thu Thư',
            'status' => 'pending'
        ]);

        AuditLog::create([
            'operator_name' => Auth::user() ? Auth::user()->name : 'Thủ thư',
            'operator_role' => 'librarian',
            'action' => 'CREATE_PURCHASE_PROPOSAL',
            'target_id' => (string)$proposal->id,
            'details' => "Lập đề xuất mua sách '{$proposal->title}' (SL: {$proposal->suggested_qty}) gửi Admin",
            'ip_address' => $request->ip()
        ]);

        return back()->with('success', "Đã gửi phiếu đề xuất mua sách '{$proposal->title}' tới Ban Quản Trị thành công!");
    }

    public function createProposal(Request $request)
    {
        return $this->storeProposal($request);
    }
}