<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\BorrowTicket;
use App\Models\Transaction;
use App\Models\SystemRule;
use App\Models\User;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Tạo đường dẫn mã QR VietQR Napas 247
     */
    public function generateVietQR(Request $request)
    {
        $rules = SystemRule::first();
        $bank = $rules->bank_name ?? 'MBBank';
        $acc = $rules->bank_account ?? '0987654321';
        $holder = $rules->account_holder ?? 'THU VIEN LIBRANOVA QUOC GIA';
        
        $amount = abs((int)$request->amount);
        if ($amount <= 0) {
            $amount = 10000;
        }
        $desc = $request->description ?: 'NOP PHAT THU VIEN LIBRANOVA';

        // Napas 247 VietQR QuickLink format
        $bankBin = 'MB'; // Standard MBBank alias
        $qrUrl = "https://img.vietqr.io/image/{$bankBin}-{$acc}-compact2.png?amount={$amount}&addInfo=" . urlencode($desc) . "&accountName=" . urlencode($holder);

        return response()->json([
            'qr_url' => $qrUrl,
            'bank' => $bank,
            'account' => $acc,
            'holder' => $holder,
            'amount' => $amount,
            'description' => $desc
        ]);
    }

    /**
     * Xác nhận thanh toán trực tuyến và cập nhật dữ liệu ngay lập tức
     */
    public function confirmPayment(Request $request)
    {
        try {
            $type = $request->type ?? 'fine';
            $ticketId = $request->ticket_id;
            $amount = abs((int)$request->amount);
            $method = $request->payment_method ?? 'vietqr';
            $autoRenew = $request->boolean('auto_renew') || ($type === 'book_renewal');
            
            // Lấy ID người dùng hiện tại và tải Eloquent model User trực tiếp từ database
            $authId = Auth::id();
            $user = $authId ? User::find($authId) : null;
            $methodName = $method === 'momo' ? 'Ví MoMo' : ($method === 'vnpay' ? 'VNPAY-QR' : 'VietQR');

            // 1. XỬ LÝ THANH TOÁN GIA HẠN THẺ ĐỘC GIẢ
            if ($type === 'card_renewal') {
                if (!$user) {
                    return response()->json(['status' => 'error', 'message' => 'Bạn chưa đăng nhập.'], 401);
                }

                if (empty($user->card_number)) {
                    return response()->json(['status' => 'error', 'message' => 'Bạn chưa được cấp thẻ thư viện.'], 400);
                }

                $rules = SystemRule::first();
                $fee = $rules ? (int)$rules->card_renewal_fee : 30000;
                if (!$amount || $amount <= 0) {
                    $amount = $fee;
                }

                // Tính toán hạn dùng mới: +1 năm từ ngày hết hạn (nếu còn hạn) hoặc từ hôm nay
                $baseDate = ($user->card_expiry_date && Carbon::parse($user->card_expiry_date)->isFuture())
                    ? Carbon::parse($user->card_expiry_date)
                    : Carbon::now();

                $newExpiryDate = $baseDate->copy()->addYear()->format('Y-m-d');

                // Cập nhật User an toàn cả qua save() và Query Builder dự phòng
                $user->card_expiry_date = $newExpiryDate;
                $user->status = 'active';
                $user->save();

                // Đảm bảo đồng bộ trực tiếp vào database
                User::where('id', $user->id)->update([
                    'card_expiry_date' => $newExpiryDate,
                    'status' => 'active'
                ]);

                // Lưu bản ghi giao dịch
                Transaction::create([
                    'transaction_code' => 'CARD-' . strtoupper(substr(uniqid(), -6)),
                    'ticket_id' => null,
                    'reader_id' => $user->id,
                    'reader_name' => $user->name,
                    'amount' => $amount,
                    'type' => 'card_renewal',
                    'payment_method' => $method,
                    'description' => "Gia hạn thẻ thư viện thường niên 1 năm qua {$methodName} (Mã thẻ: {$user->card_number})",
                    'status' => 'completed'
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Gia hạn thẻ thành công! Hạn sử dụng mới đến: ' . Carbon::parse($newExpiryDate)->format('d/m/Y')
                ]);
            }

            // 2. XỬ LÝ GIA HẠN SÁCH HOẶC NỘP PHẠT THEO PHIẾU MƯỢN CỤ THỂ
            if ($ticketId) {
                $ticket = BorrowTicket::with(['book', 'reader'])->find($ticketId);
                if (!$ticket) {
                    return response()->json(['status' => 'error', 'message' => 'Không tìm thấy phiếu mượn tương ứng.'], 404);
                }

                $rules = SystemRule::first();
                $maxRenew = $rules ? (int)$rules->max_renewal_times : 2;
                $isRenewAction = ($type === 'book_renewal' || $autoRenew);

                // Kiểm tra giới hạn số lần gia hạn sách
                if ($isRenewAction && (int)$ticket->renew_count >= $maxRenew) {
                    return response()->json([
                        'status' => 'error',
                        'message' => "Phiếu mượn #{$ticket->ticket_code} đã đạt giới hạn gia hạn tối đa ({$maxRenew} lần)."
                    ], 400);
                }

                $newDueDate = null;

                if ($isRenewAction) {
                    // Tính hạn trả mới: nếu hạn cũ còn hiệu lực thì cộng thêm 7 ngày từ hạn cũ; nếu đã quá hạn thì cộng thêm 7 ngày từ hôm nay
                    $baseDate = ($ticket->due_date && Carbon::parse($ticket->due_date)->isFuture())
                        ? Carbon::parse($ticket->due_date)
                        : Carbon::now();
                    $newDueDate = $baseDate->copy()->addDays(7)->format('Y-m-d');

                    $ticket->due_date = $newDueDate;
                    $ticket->renew_count = (int)$ticket->renew_count + 1;
                    $ticket->status = 'borrowing';
                    $ticket->overdue_days = 0;
                    $ticket->fine_amount = 0;
                    $ticket->payment_status = 'paid';
                    $ticket->payment_method = $method;
                    $ticket->save();

                    // Cập nhật DB trực tiếp đảm bảo dữ liệu luôn được lưu vào SQL Server
                    BorrowTicket::where('id', $ticket->id)->update([
                        'due_date' => $newDueDate,
                        'renew_count' => $ticket->renew_count,
                        'status' => 'borrowing',
                        'overdue_days' => 0,
                        'fine_amount' => 0,
                        'payment_status' => 'paid',
                        'payment_method' => $method
                    ]);
                } else {
                    // Chỉ nộp phạt trễ hạn
                    $ticket->payment_status = 'paid';
                    $ticket->payment_method = $method;
                    $ticket->save();

                    BorrowTicket::where('id', $ticket->id)->update([
                        'payment_status' => 'paid',
                        'payment_method' => $method
                    ]);
                }

                // Xác định số tiền giao dịch chính xác (không âm)
                $ticketAmount = $amount > 0 ? $amount : abs((int)$ticket->fine_amount);
                if ($ticketAmount <= 0) {
                    $ticketAmount = $isRenewAction ? 10000 : 30000;
                }

                $bookTitle = $ticket->book->title ?? 'Sách mượn';
                $readerName = $ticket->reader->name ?? ($user ? $user->name : 'Độc giả');
                $readerId = $ticket->reader_id ?? ($user ? $user->id : null);

                $desc = $isRenewAction 
                    ? "Gia hạn mượn sách (+7 ngày) phiếu #{$ticket->ticket_code} ({$bookTitle}) qua {$methodName}"
                    : "Thanh toán tiền phạt trễ hạn phiếu #{$ticket->ticket_code} ({$bookTitle}) qua {$methodName}";

                Transaction::create([
                    'transaction_code' => ($isRenewAction ? 'RENEW-' : 'TXN-') . strtoupper(substr(uniqid(), -6)),
                    'ticket_id' => $ticket->id,
                    'reader_id' => $readerId,
                    'reader_name' => $readerName,
                    'amount' => $ticketAmount,
                    'type' => $isRenewAction ? 'book_renewal' : 'fine',
                    'payment_method' => $method,
                    'description' => $desc,
                    'status' => 'completed'
                ]);

                $successMsg = $isRenewAction 
                    ? "Thanh toán thành công! Sách '{$bookTitle}' đã được gia hạn thêm 7 ngày (Hạn trả mới: " . Carbon::parse($newDueDate)->format('d/m/Y') . ")."
                    : "Thanh toán tiền phạt phiếu #{$ticket->ticket_code} thành công!";

                return response()->json([
                    'status' => 'success',
                    'message' => $successMsg
                ]);
            } 
            
            // 3. XỬ LÝ NỘP TOÀN BỘ TIỀN PHẠT CHO TẤT CẢ CÁC PHIẾU QUÁ HẠN CỦA ĐỘC GIẢ
            if ($user) {
                $unpaidTickets = BorrowTicket::where('reader_id', $user->id)
                    ->where('payment_status', 'unpaid')
                    ->get();

                $paidTotal = 0;
                foreach ($unpaidTickets as $t) {
                    $t->payment_status = 'paid';
                    $t->payment_method = $method;
                    $t->save();
                    $paidTotal += abs((int)$t->fine_amount);
                }

                BorrowTicket::where('reader_id', $user->id)
                    ->where('payment_status', 'unpaid')
                    ->update([
                        'payment_status' => 'paid',
                        'payment_method' => $method
                    ]);

                $totalAmount = $amount > 0 ? $amount : $paidTotal;
                if ($totalAmount <= 0) {
                    $totalAmount = 30000;
                }

                Transaction::create([
                    'transaction_code' => 'TXN-' . strtoupper(substr(uniqid(), -6)),
                    'ticket_id' => null,
                    'reader_id' => $user->id,
                    'reader_name' => $user->name,
                    'amount' => $totalAmount,
                    'type' => 'fine',
                    'payment_method' => $method,
                    'description' => "Thanh toán toàn bộ tiền phạt trễ hạn qua {$methodName} (Mã thẻ: {$user->card_number})",
                    'status' => 'completed'
                ]);

                return response()->json([
                    'status' => 'success', 
                    'message' => 'Giao dịch thanh toán tiền phạt thành công!'
                ]);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Không tìm thấy dữ liệu yêu cầu thanh toán.'
            ], 400);

        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lỗi khi lưu giao dịch: ' . $e->getMessage()
            ], 500);
        }
    }
}