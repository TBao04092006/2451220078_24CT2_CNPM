@extends('layouts.app')

@section('title', 'Cổng Tra Cứu & Độc Giả')

@section('content')
@php
    $hasCard = !empty($user->card_number);
    $rules = $rules ?? \App\Models\SystemRule::first();
    $maxRenews = (int)($rules->max_renewal_times ?? 2);
    $cardRenewalFee = (int)($rules->card_renewal_fee ?? 30000);
    $finePerDay = (int)($rules->fine_per_day ?? 5000);
@endphp
<div class="space-y-6">
    <!-- 1. Thẻ thông tin độc giả (Hero Card) -->
    <div class="bg-gradient-to-br from-blue-900 via-indigo-900 to-slate-900 text-white rounded-3xl p-6 sm:p-8 shadow-lg relative overflow-hidden">
        <div class="absolute -right-16 -bottom-16 w-64 h-64 bg-sky-500/10 rounded-full blur-2xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="space-y-2">
                @if($hasCard)
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-sky-300 text-xs font-medium backdrop-blur-sm border border-white/10">
                        <i data-lucide="credit-card" class="w-3.5 h-3.5"></i>
                        <span>Thẻ Thư Viện: <strong class="font-mono tracking-wider">{{ $user->card_number }}</strong></span>
                        <span class="ml-1 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Hợp Lệ</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold font-serif">Xin chào, {{ $user->name }}</h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl flex items-center flex-wrap gap-2">
                        <span>Hạn sử dụng thẻ: <strong>{{ $user->card_expiry_date ? \Carbon\Carbon::parse($user->card_expiry_date)->format('d/m/Y') : '15/12/2026' }}</strong></span>
                        @if($user->status === 'active')
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">Hợp Lệ</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-red-500/20 text-red-300 border border-red-500/30">Hết Hạn/Khóa</span>
                        @endif
                    </p>
                @else
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 text-sky-200 text-xs font-medium backdrop-blur-sm border border-white/10">
                        <i data-lucide="credit-card" class="w-3.5 h-3.5 text-slate-300"></i>
                        <span>Thẻ Thư Viện: <strong class="font-mono text-amber-300 tracking-wider">...-....-....</strong></span>
                        <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-400/40">Chưa cấp thẻ</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold font-serif">Xin chào, {{ $user->name }}</h1>
                    <p class="text-xs sm:text-sm text-slate-300 max-w-xl flex items-center flex-wrap gap-2">
                        <span>Hạn sử dụng thẻ: <strong class="text-amber-200">Chưa kích hoạt (--/--/----)</strong></span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold bg-amber-500/20 text-amber-300 border border-amber-500/30">Chưa cấp thẻ</span>
                    </p>
                @endif
            </div>

            <!-- Card Actions -->
            <div class="flex flex-wrap items-center gap-3">
                <a href="#history-section" class="px-4 py-2.5 bg-white/10 hover:bg-white/20 text-white font-semibold text-xs rounded-xl border border-white/20 shadow-xs transition flex items-center gap-2 backdrop-blur-sm">
                    <i data-lucide="history" class="w-4 h-4 text-sky-300"></i>
                    Lịch Sử Mượn - Trả & Giao Dịch
                </a>
                @if($hasCard)
                    <button type="button" onclick="openPaymentModal('card_renewal')" class="px-4 py-2.5 bg-sky-500 hover:bg-sky-400 text-slate-950 font-semibold text-xs rounded-xl shadow transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="credit-card" class="w-4 h-4"></i>
                        <span>Thanh Toán & Gia Hạn Thẻ</span>
                    </button>
                @else
                    <button type="button" onclick="notifyNoCard()" class="px-4 py-2.5 bg-slate-700/80 hover:bg-slate-700 text-slate-300 font-semibold text-xs rounded-xl shadow transition flex items-center gap-2 border border-white/10 cursor-pointer">
                        <i data-lucide="credit-card" class="w-4 h-4 text-amber-400"></i>
                        <span>Thanh Toán</span>
                        <span class="px-1.5 py-0.5 rounded bg-amber-500/20 text-amber-300 text-[10px] font-bold">Chưa cấp thẻ</span>
                    </button>
                @endif
                @if($totalUnpaidFines > 0)
                    <button type="button" 
                        onclick="openPaymentModal('fine')" 
                        class="px-4 py-2.5 bg-amber-400 hover:bg-amber-300 text-slate-950 font-semibold text-xs rounded-xl shadow transition flex items-center gap-2 cursor-pointer">
                        <i data-lucide="qr-code" class="w-4 h-4"></i>
                        <span>Nộp Phạt: {{ number_format($totalUnpaidFines) }}đ</span>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if(!$hasCard)
        <!-- Banner cảnh báo chưa cấp thẻ cho độc giả -->
        <div class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-2xl shadow-xs flex items-start gap-3.5 text-amber-900 animate-fadeIn">
            <div class="p-2 rounded-xl bg-amber-100 text-amber-700 flex-shrink-0 mt-0.5">
                <i data-lucide="alert-triangle" class="w-5 h-5"></i>
            </div>
            <div class="space-y-1 flex-1 text-xs">
                <div class="font-bold text-sm text-amber-950 flex items-center gap-2">
                    <span>Tài khoản độc giả chưa được cấp thẻ thư viện</span>
                    <span class="px-2 py-0.5 rounded-full bg-amber-200 text-amber-900 font-bold text-[10px]">Chưa cấp thẻ</span>
                </div>
                <p class="text-amber-800 leading-relaxed">
                    Bạn đã đăng ký tài khoản thành công nhưng chưa được cấp thẻ độc giả chính thức. Các tính năng <strong>Mượn sách, Trả sách và Gia hạn</strong> sẽ tạm thời không thực thi cho đến khi được cấp thẻ.
                </p>
                <div class="pt-1 text-[11px] text-amber-900 font-medium flex flex-wrap items-center gap-x-4 gap-y-1">
                    <span>Email đăng ký: <strong class="font-mono text-amber-950">{{ $user->email }}</strong></span>
                    <span>Số điện thoại: <strong class="font-mono text-amber-950">{{ $user->phone ?? 'Chưa cập nhật' }}</strong></span>
                    <span class="text-amber-700 font-semibold">👉 Vui lòng liên hệ quầy Thủ thư đối soát đúng Email & SĐT trên để được Cấp Thẻ Độc Giả.</span>
                </div>
            </div>
        </div>
    @endif

    <!-- 2. Quy định thư viện hiện hành -->
    <div class="bg-blue-50/90 border border-blue-200/80 rounded-2xl p-3.5 px-5 flex flex-wrap items-center justify-between gap-3 text-xs text-blue-900 shadow-2xs">
        <div class="flex items-center gap-2">
            <span class="p-1 rounded bg-blue-100 text-blue-700"><i data-lucide="shield-check" class="w-4 h-4"></i></span>
            <span class="font-bold">Quy định thư viện hiện hành:</span>
        </div>
        <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-slate-600">
            <span>Mượn tối đa: <strong class="text-blue-800 font-semibold">{{ $rules->max_books_per_loan ?? 5 }} cuốn</strong></span>
            <span>Thời hạn: <strong class="text-blue-800 font-semibold">{{ $rules->max_loan_days ?? 14 }} ngày</strong></span>
            <span>Gia hạn: <strong class="text-blue-800 font-semibold">Tối đa {{ $rules->max_renewal_times ?? 2 }} lần</strong></span>
            <span>Phạt trễ: <strong class="text-rose-600 font-semibold">{{ number_format($rules->fine_per_day ?? 5000) }}đ/ngày</strong></span>
            <span>Phí gia hạn thẻ: <strong class="text-emerald-700 font-semibold">{{ number_format($rules->card_renewal_fee ?? 30000) }}đ</strong></span>
        </div>
    </div>

    <!-- 3. Sách Đang Mượn & Hạn Trả -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                    <i data-lucide="bookmark-check" class="w-4 h-4"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold text-slate-900">Sách Đang Mượn & Hạn Trả</h2>
                    <p class="text-xs text-slate-500">Tự động tính phí phạt trễ hạn {{ number_format($rules->fine_per_day ?? 5000) }}đ/ngày theo quy định</p>
                </div>
            </div>
            @php
                $activeLoans = $tickets->where('status', '!=', 'returned');
            @endphp
            <span class="text-xs font-semibold px-2.5 py-1 bg-slate-100 text-slate-700 rounded-full">
                {{ $activeLoans->count() }} cuốn đang giữ
            </span>
        </div>

        @if($activeLoans->count() === 0)
            <div class="text-center py-8 text-slate-400 text-xs">
                Hiện bạn không có sách nào đang mượn. Hãy tra cứu kho sách bên dưới để chọn mượn nhé!
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($activeLoans as $t)
                    @php
                        // Kiểm tra nếu đã nộp phạt thì không còn tính là quá hạn
                        $isPaid = ($t->payment_status === 'paid');
                        $isReturning = ($t->status === 'returning');
                        $isOverdue = (($t->status === 'overdue' || (int)$t->overdue_days != 0 || (float)$t->fine_amount > 0) && !$isPaid);
                        
                        $cleanOverdueDays = abs((int)$t->overdue_days);
                        $cleanFineAmount = abs((float)$t->fine_amount);
                        if ($isOverdue && $cleanFineAmount == 0) {
                            $cleanFineAmount = $cleanOverdueDays > 0 ? ($cleanOverdueDays * $finePerDay) : $finePerDay;
                        }
                        $canRenew = (int)($t->renew_count ?? 0) < $maxRenews;
                        $renewalCost = $isOverdue ? max(10000, (int)$cleanFineAmount) : 10000;
                        $ticketCode = $t->ticket_code ?: ('TK-' . str_pad($t->id, 4, '0', STR_PAD_LEFT));
                        $bookTitle = $t->book->title ?? 'Sách thư viện';
                        $dueDateFormatted = $t->due_date ? \Carbon\Carbon::parse($t->due_date)->format('d/m/Y') : date('d/m/Y');
                    @endphp
                    <div class="p-4 rounded-2xl border {{ $isReturning ? 'border-amber-300 bg-amber-50/50' : ($isPaid ? 'border-emerald-300 bg-emerald-50/30' : ($isOverdue ? 'border-red-200 bg-red-50/40' : 'border-slate-200 bg-slate-50/50')) }} flex flex-col justify-between gap-3 shadow-xs">
                        <div class="space-y-1">
                            <div class="flex items-center justify-between text-[11px]">
                                <span class="font-mono text-slate-400 font-semibold">#{{ $ticketCode }}</span>
                                @if($isReturning)
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-amber-100 text-amber-900 border border-amber-300 animate-pulse flex items-center gap-1">
                                        <i data-lucide="clock" class="w-3 h-3 text-amber-600"></i>
                                        <span>Chờ thủ thư nhận sách</span>
                                    </span>
                                @elseif($isPaid)
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-300 flex items-center gap-1">
                                        <i data-lucide="check-circle" class="w-3 h-3 text-emerald-600"></i>
                                        <span>ĐÃ NỘP PHẠT (Hợp lệ)</span>
                                    </span>
                                @elseif($isOverdue)
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-red-100 text-red-700">Trễ {{ $cleanOverdueDays }} ngày</span>
                                @elseif($t->status === 'pending')
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-amber-100 text-amber-800">Chờ nhận sách</span>
                                @else
                                    <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-blue-100 text-blue-700">Đang mượn</span>
                                @endif
                            </div>
                            <h3 class="font-bold text-sm text-slate-900 line-clamp-1">{{ $bookTitle }}</h3>
                            <p class="text-xs text-slate-500">Tác giả: {{ $t->book->author ?? 'Đang cập nhật' }}</p>
                            <p class="text-xs text-slate-600 font-medium">Hạn trả: <strong class="{{ $isOverdue ? 'text-red-600' : ($isPaid ? 'text-emerald-700' : 'text-slate-800') }}">{{ $dueDateFormatted }}</strong></p>
                            
                            {{-- Trạng thái tiền phạt --}}
                            @if($isPaid)
                                <div class="p-2 bg-emerald-100/80 rounded-xl text-xs text-emerald-900 font-semibold flex items-center justify-between mt-2 border border-emerald-200">
                                    <span>Phạt trễ hạn:</span>
                                    <span class="font-bold text-emerald-800 flex items-center gap-1">
                                        <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600 stroke-[3]"></i>
                                        0đ còn nợ (Đã nộp {{ number_format($cleanFineAmount) }}đ)
                                    </span>
                                </div>
                            @elseif($isOverdue && $cleanFineAmount > 0)
                                <div class="p-2 bg-red-100/80 rounded-xl text-xs text-red-800 font-semibold flex items-center justify-between mt-2">
                                    <span>Phạt trễ hạn:</span>
                                    <span class="font-bold text-rose-700">{{ number_format($cleanFineAmount) }}đ (Chưa nộp)</span>
                                </div>
                            @endif
                        </div>

                        <!-- Ticket Action Buttons (Nút Trả Sách & Nộp Phạt) -->
                        <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-200/60">
                            {{-- NÚT 1: TRẢ SÁCH TẠI QUẦY --}}
                            @if($isReturning)
                                <button type="button" disabled class="flex-1 py-1.5 px-2.5 text-xs font-bold bg-amber-100 text-amber-900 rounded-lg border border-amber-300 animate-pulse flex items-center justify-center gap-1 cursor-not-allowed">
                                    <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-700"></i>
                                    <span>Đang chờ thủ thư nhận</span>
                                </button>
                            @elseif($t->status !== 'pending')
                                <form action="{{ route('reader.tickets.return', $t->id) }}" method="POST" class="flex-1">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Bạn có chắc chắn muốn gửi yêu cầu trả cuốn sách này tại quầy?')" class="w-full py-1.5 px-2.5 text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer">
                                        <i data-lucide="rotate-ccw" class="w-3.5 h-3.5"></i>
                                        <span>{{ $isPaid ? 'Trả Sách Cho Thủ Thư' : 'Trả Sách Tại Quầy' }}</span>
                                    </button>
                                </form>
                            @endif

                            {{-- NÚT 2: GIA HẠN / NỘP PHẠT --}}
                            @if($t->status !== 'returned' && $t->status !== 'pending' && !$isReturning)
                                @if($canRenew)
                                    <button type="button" 
                                        data-id="{{ $t->id }}"
                                        data-code="{{ $ticketCode }}"
                                        data-title="{{ htmlspecialchars($bookTitle, ENT_QUOTES) }}"
                                        data-overdue="{{ $isOverdue ? '1' : '0' }}"
                                        data-cost="{{ $renewalCost }}"
                                        data-due="{{ $dueDateFormatted }}"
                                        onclick="handleBookRenewalClick(this)"
                                        class="py-1.5 px-2.5 text-xs font-bold {{ $isOverdue ? 'bg-amber-500 hover:bg-amber-600 text-slate-950' : 'bg-indigo-600 hover:bg-indigo-700 text-white' }} rounded-lg shadow-xs transition flex items-center justify-center gap-1.5 cursor-pointer whitespace-nowrap">
                                        <i data-lucide="qr-code" class="w-3.5 h-3.5"></i>
                                        <span>{{ $isOverdue ? 'Gia hạn & Nộp phạt' : '+7 ngày' }}</span>
                                    </button>
                                @endif
                            @endif

                            <button type="button" 
                                data-id="{{ $t->book_id }}" 
                                data-title="{{ htmlspecialchars($t->book->title ?? '', ENT_QUOTES) }}" 
                                onclick="openRateModal(this.dataset.id, this.dataset.title)" 
                                class="p-1.5 text-amber-500 hover:bg-amber-50 rounded-lg border border-slate-200 transition cursor-pointer"
                                title="Đánh giá sách">
                                <i data-lucide="star" class="w-3.5 h-3.5"></i>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- 4. KHU VỰC LỊCH SỬ MƯỢN - TRẢ & GIAO DỊCH -->
    <div id="history-section" class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm space-y-5 scroll-mt-20">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-xs flex-shrink-0">
                    <i data-lucide="history" class="w-5 h-5"></i>
                </div>
                <div>
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight flex items-center gap-2">
                        Lịch Sử Mượn - Trả & Giao Dịch
                        <span class="text-xs font-semibold px-2.5 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200">Độc Giả</span>
                    </h2>
                    <p class="text-xs text-slate-500 mt-0.5">Theo dõi chi tiết tất cả các lượt mượn trả sách, gia hạn và các giao dịch tài chính</p>
                </div>
            </div>

            <!-- Tab Buttons -->
            <div class="flex items-center p-1.5 bg-slate-100/90 rounded-2xl border border-slate-200/80 self-start lg:self-auto">
                <button type="button" 
                    id="btn-tab-borrow" 
                    onclick="switchHistoryTab('borrow')" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 bg-white text-indigo-900 shadow-xs">
                    <i data-lucide="book-check" class="w-4 h-4 text-indigo-600"></i>
                    <span>Lịch Sử Mượn - Trả Sách</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">{{ $tickets->count() }}</span>
                </button>

                <button type="button" 
                    id="btn-tab-card" 
                    onclick="switchHistoryTab('card')" 
                    class="px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 text-slate-600 hover:text-slate-900">
                    <i data-lucide="credit-card" class="w-4 h-4 text-slate-400"></i>
                    <span>Lịch Sử Giao Dịch Thẻ & Phí</span>
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-slate-200 text-slate-700">{{ $allTransactions->count() }}</span>
                </button>
            </div>
        </div>

        <!-- TAB 1: LỊCH SỬ MƯỢN - TRẢ SÁCH -->
        <div id="tab-content-borrow" class="space-y-4">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                <div class="relative flex-1 min-w-[240px]">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="filter-borrow-kw" onkeyup="filterBorrowTable()" placeholder="Tìm nhanh theo Tên sách, ISBN, Mã phiếu mượn..." class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition shadow-2xs">
                </div>

                <div class="flex items-center gap-2 text-xs">
                    <label class="text-slate-500 font-medium hidden sm:inline">Trạng thái:</label>
                    <select id="filter-borrow-status" onchange="filterBorrowTable()" class="py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 shadow-2xs text-slate-700">
                        <option value="">Tất cả trạng thái</option>
                        <option value="borrowing">Đang mượn</option>
                        <option value="returned">Đã trả sách</option>
                        <option value="overdue">Quá hạn</option>
                        <option value="pending">Chờ nhận sách</option>
                    </select>

                    <button type="button" onclick="resetBorrowFilters()" class="py-2 px-3.5 bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold rounded-xl text-xs transition">
                        Đặt lại
                    </button>
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm bg-white">
                <table class="w-full text-left border-collapse min-w-[1020px]" id="table-borrow-history">
                    <thead>
                        <tr class="bg-slate-100/90 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4 w-[140px]">MÃ THẺ</th>
                            <th class="py-3.5 px-4 w-[160px]">TÊN NGƯỜI DÙNG</th>
                            <th class="py-3.5 px-4 w-[150px]">NGÀY THỰC HIỆN</th>
                            <th class="py-3.5 px-4 min-w-[240px]">TÊN SÁCH (TÊN GIAO DỊCH)</th>
                            <th class="py-3.5 px-4 w-[150px]">MÃ SÁCH</th>
                            <th class="py-3.5 px-4 w-[150px]">MÃ GIAO DỊCH</th>
                            <th class="py-3.5 px-4 w-[130px]">PHÍ GIAO DỊCH</th>
                            <th class="py-3.5 px-4 w-[120px] text-center">TRẠNG THÁI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($tickets as $t)
                            <tr class="hover:bg-indigo-50/40 transition borrow-row" 
                                data-title="{{ strtolower($t->book->title ?? '') }}" 
                                data-isbn="{{ strtolower($t->book->isbn ?? '') }}" 
                                data-code="{{ strtolower($t->ticket_code) }}" 
                                data-status="{{ $t->status }}">
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-900 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 text-[11px]">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5 text-sky-600"></i>
                                        {{ $user->card_number ?? $t->reader->card_number ?? 'LIB-2026-8899' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $user->name ?? $t->reader->name ?? 'Độc giả' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap space-y-1">
                                    <div class="flex items-center gap-1.5 text-xs">
                                        <span class="text-[10px] uppercase font-bold text-slate-400">Mượn:</span>
                                        <span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($t->borrow_date)->format('d/m/Y') }}</span>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-slate-500">
                                        <span class="text-[10px] uppercase font-bold text-slate-400">Hạn:</span>
                                        <span>{{ \Carbon\Carbon::parse($t->due_date)->format('d/m/Y') }}</span>
                                    </div>
                                    @if($t->return_date || $t->status === 'returned')
                                        <div class="flex items-center gap-1.5 text-[11px] text-emerald-700 font-semibold">
                                            <span class="text-[10px] uppercase font-bold text-emerald-600">Trả:</span>
                                            <span>{{ $t->return_date ? \Carbon\Carbon::parse($t->return_date)->format('d/m/Y') : \Carbon\Carbon::parse($t->updated_at)->format('d/m/Y') }}</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm hover:text-indigo-600 transition cursor-pointer" onclick="document.getElementById('btn-view-{{ $t->book_id }}')?.click()">
                                        {{ $t->book->title ?? 'Sách thư viện' }}
                                    </div>
                                    <div class="mt-1 flex items-center gap-1.5">
                                        @if($t->status === 'returned')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 border border-emerald-200 text-[11px] font-medium">
                                                <i data-lucide="check" class="w-3.5 h-3.5 text-emerald-600"></i>
                                                Trả sách vào kho lưu thông
                                            </span>
                                        @elseif($t->status === 'overdue')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-rose-50 text-rose-700 border border-rose-200 text-[11px] font-medium">
                                                <i data-lucide="alert-circle" class="w-3.5 h-3.5 text-rose-600"></i>
                                                Mượn sách (Quá hạn)
                                            </span>
                                        @elseif($t->status === 'pending')
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200 text-[11px] font-medium">
                                                <i data-lucide="clock" class="w-3.5 h-3.5 text-amber-600"></i>
                                                Yêu cầu mượn trực tuyến
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-sky-50 text-sky-800 border border-sky-200 text-[11px] font-medium">
                                                <i data-lucide="book-open" class="w-3.5 h-3.5 text-sky-600"></i>
                                                Mượn sách đọc tại nhà {{ $t->renew_count > 0 ? "(Gia hạn {$t->renew_count} lần)" : '' }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono text-xs font-semibold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200 inline-block">
                                        {{ $t->book->isbn ?? ('BK-' . str_pad($t->book_id, 4, '0', STR_PAD_LEFT)) }}
                                    </span>
                                    <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-600"></i>
                                        <span>{{ $t->book->shelf_location ?? 'Kệ A1' }}</span>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200 text-xs inline-block">
                                        #{{ $t->ticket_code }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($t->fine_amount > 0)
                                        <div class="font-bold text-rose-600 text-xs">{{ number_format($t->fine_amount) }}đ</div>
                                        @if($t->payment_status === 'paid')
                                            <span class="inline-flex items-center gap-0.5 text-[10px] text-emerald-700 font-semibold mt-0.5">
                                                <i data-lucide="check" class="w-3 h-3"></i> Đã nộp
                                            </span>
                                        @else
                                            <button type="button" onclick="openPaymentModal('fine', '{{ $t->id }}', '{{ $t->fine_amount }}', '{{ $t->ticket_code }}')" class="mt-1 inline-flex items-center gap-1 px-2 py-0.5 text-[10px] font-bold bg-rose-600 hover:bg-rose-700 text-white rounded transition shadow-2xs cursor-pointer">
                                                <i data-lucide="qr-code" class="w-3.5 h-3.5"></i> Nộp phạt
                                            </button>
                                        @endif
                                    @else
                                        <span class="inline-block px-2.5 py-0.5 text-[10px] font-semibold text-emerald-700 bg-emerald-50 rounded-full border border-emerald-200">
                                            0đ (Miễn phí)
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] inline-block {{ $t->status === 'returned' ? 'bg-slate-100 text-slate-700 border border-slate-200' : ($t->status === 'overdue' ? 'bg-red-100 text-red-700 border border-red-200' : ($t->status === 'pending' ? 'bg-amber-100 text-amber-800 border border-amber-200' : 'bg-blue-100 text-blue-700 border border-blue-200')) }}">
                                        {{ $t->status === 'returned' ? 'Đã Trả Sách' : ($t->status === 'overdue' ? 'Quá Hạn' : ($t->status === 'pending' ? 'Chờ Nhận' : 'Đang Mượn')) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-slate-400 text-xs">
                                    Chưa có dữ liệu nhật ký mượn - trả sách.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- TAB 2: LỊCH SỬ GIA HẠN THẺ & GIAO DỊCH -->
        <div id="tab-content-card" class="space-y-4 hidden">
            <div class="flex flex-wrap items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200/80">
                <div class="flex items-center gap-3">
                    <span class="text-xs text-slate-600">Tổng số giao dịch: <strong class="text-slate-900 font-bold">{{ $allTransactions->count() }} lượt</strong></span>
                    <span class="text-slate-300">|</span>
                    <span class="text-xs text-slate-600">Tổng tiền đã trả: <strong class="text-indigo-700 font-bold">{{ number_format($allTransactions->where('status', 'completed')->sum('amount')) }} VNĐ</strong></span>
                </div>
                <div class="relative min-w-[240px]">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="text" id="filter-card-kw" onkeyup="filterCardTable()" placeholder="Tìm theo Mã giao dịch, Nội dung..." class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition shadow-2xs">
                </div>
            </div>

            <div class="overflow-x-auto rounded-2xl border border-slate-200 shadow-sm bg-white">
                <table class="w-full text-left border-collapse min-w-[1020px]" id="table-card-history">
                    <thead>
                        <tr class="bg-slate-100/90 text-slate-700 text-[11px] font-bold uppercase tracking-wider border-b border-slate-200">
                            <th class="py-3.5 px-4 w-[140px]">MÃ THẺ</th>
                            <th class="py-3.5 px-4 w-[160px]">TÊN NGƯỜI DÙNG</th>
                            <th class="py-3.5 px-4 w-[150px]">NGÀY THỰC HIỆN</th>
                            <th class="py-3.5 px-4 min-w-[240px]">TÊN SÁCH (TÊN GIAO DỊCH)</th>
                            <th class="py-3.5 px-4 w-[150px]">MÃ SÁCH</th>
                            <th class="py-3.5 px-4 w-[150px]">MÃ GIAO DỊCH</th>
                            <th class="py-3.5 px-4 w-[130px]">PHÍ GIAO DỊCH</th>
                            <th class="py-3.5 px-4 w-[120px] text-center">TRẠNG THÁI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-700">
                        @forelse($allTransactions as $tx)
                            <tr class="hover:bg-indigo-50/40 transition card-tx-row"
                                data-search="{{ strtolower($tx->transaction_code . ' ' . $tx->description . ' ' . $tx->payment_method) }}">
                                <td class="py-3.5 px-4 font-mono font-semibold text-slate-900 whitespace-nowrap">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-sky-50 text-sky-800 border border-sky-200 text-[11px]">
                                        <i data-lucide="credit-card" class="w-3.5 h-3.5 text-sky-600"></i>
                                        {{ $user->card_number ?? 'LIB-2026-8899' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-900 whitespace-nowrap">
                                    {{ $tx->reader_name ?? $user->name ?? 'Độc giả' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap font-mono text-xs">
                                    <div class="font-bold text-slate-900">{{ \Carbon\Carbon::parse($tx->created_at)->format('H:i') }}</div>
                                    <div class="text-[11px] text-slate-500">{{ \Carbon\Carbon::parse($tx->created_at)->format('d/m/Y') }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900 text-xs sm:text-sm">
                                        @if($tx->type === 'card_renewal')
                                            Gia hạn thẻ thư viện thường niên 1 năm
                                        @elseif($tx->type === 'fine')
                                            Nộp phạt vi phạm trễ hạn
                                        @elseif($tx->type === 'book_renewal')
                                            Gia hạn mượn sách (+7 ngày)
                                        @else
                                            Thanh toán dịch vụ thư viện
                                        @endif
                                    </div>
                                    <p class="text-[11px] text-slate-500 mt-1 line-clamp-1">{{ $tx->description }}</p>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    @if($tx->ticket && $tx->ticket->book)
                                        <span class="font-mono text-xs font-semibold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md border border-slate-200 inline-block">
                                            {{ $tx->ticket->book->isbn ?? ('BK-' . $tx->ticket->book_id) }}
                                        </span>
                                    @else
                                        <span class="font-mono text-[11px] font-semibold text-sky-800 bg-sky-50 px-2.5 py-1 rounded-md border border-sky-200 inline-block">
                                            THE-THU-VIEN
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <span class="font-mono font-bold text-sky-700 bg-sky-50 px-2.5 py-1 rounded-lg border border-sky-200 text-xs inline-block">
                                        #{{ $tx->transaction_code }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 whitespace-nowrap">
                                    <div class="font-bold text-slate-900 text-xs">{{ number_format($tx->amount) }} VNĐ</div>
                                    <span class="inline-block mt-0.5 text-[10px] text-slate-500 uppercase font-semibold">{{ $tx->payment_method ?? 'VietQR' }}</span>
                                </td>
                                <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                    <span class="px-2.5 py-1 rounded-full font-bold text-[10px] inline-block bg-emerald-100 text-emerald-800 border border-emerald-200">
                                        Hoàn Tất
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-slate-400 text-xs">
                                    Chưa có giao dịch gia hạn thẻ hoặc nộp phí nào được ghi nhận.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 5. Tra Cứu Kho Sách Trực Tuyến -->
    <div id="search-catalog-section" class="bg-white rounded-3xl p-6 sm:p-7 border border-slate-200 shadow-sm space-y-6 scroll-mt-20">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-5">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">Tra Cứu Kho Sách Trực Tuyến</h2>
                <p class="text-xs text-slate-500 mt-0.5">Xem thông tin chi tiết, định vị vị trí kệ và đăng ký mượn sách trực tuyến</p>
            </div>

            <!-- Search Form -->
            <form action="{{ route('reader.index') }}#search-catalog-section" method="GET" id="reader-search-form" class="space-y-2">
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[200px]">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" placeholder="Tên sách, tác giả, NXB, ISBN, vị trí..." class="w-full pl-9 pr-3 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:bg-white transition">
                    </div>

                    <div class="relative">
                        <button type="button" onclick="openReaderTagModal()" id="btn-reader-tag-filter" class="flex items-center gap-1.5 py-2 px-3 text-xs border rounded-xl transition {{ request('selected_tags') ? 'bg-sky-50 text-sky-800 border-sky-300 font-semibold shadow-2xs' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                            <i data-lucide="tags" class="w-3.5 h-3.5 text-sky-600"></i>
                            <span id="reader-tag-button-label">
                                @if(request('selected_tags'))
                                    Lọc tag ({{ count(array_filter(explode(',', request('selected_tags')))) }})
                                @else
                                    Tất cả thể loại / nhãn tag
                                @endif
                            </span>
                        </button>
                        <input type="hidden" name="selected_tags" id="reader_selected_tags_input" value="{{ request('selected_tags') }}">
                    </div>

                    <select name="status" class="py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                        <option value="">Tất cả tình trạng</option>
                        <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Còn sách</option>
                        <option value="low_stock" {{ request('status') == 'low_stock' ? 'selected' : '' }}>Sắp hết (≤ 2 cuốn)</option>
                        <option value="out_of_stock" {{ request('status') == 'out_of_stock' ? 'selected' : '' }}>Hết lượt</option>
                    </select>

                    <button type="submit" class="py-2 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-xl shadow-xs transition cursor-pointer">Lọc</button>
                </div>

                @if(request('selected_tags'))
                    <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                        <span class="text-slate-500 font-medium text-[11px]">Đang lọc tag:</span>
                        @foreach(array_filter(array_map('trim', explode(',', request('selected_tags')))) as $tag)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-sky-100 text-sky-800 text-[11px] font-semibold rounded-full border border-sky-200">
                                <span>{{ $tag }}</span>
                                <button type="button" onclick="removeSingleTag('{{ $tag }}')" class="hover:text-sky-950 ml-0.5">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </form>
        </div>

        <!-- Book Cards Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-5">
            @foreach($books as $b)
                @php
                    $catName = is_object($b->category) ? ($b->category->name ?? 'Tổng hợp') : ($b->category ?: 'Tổng hợp');
                    $pubName = is_object($b->publisher) ? ($b->publisher->name ?? 'Đang cập nhật') : ($b->publisher ?: 'Đang cập nhật');
                    $avail = $b->available_copies ?? $b->available_qty ?? 0;
                    $total = $b->total_copies ?? $b->total_qty ?? 0;
                    $hasCover = !empty($b->cover_image) || !empty($b->cover_url);
                    $coverSrc = !empty($b->cover_image) ? (str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . $b->cover_image)) : ($b->cover_url ?? '');

                    // Bộ màu Gradient cao cấp cho sách chưa có ảnh bìa
                    $gradients = [
                        'from-blue-600 via-indigo-600 to-slate-900',
                        'from-emerald-600 via-teal-700 to-slate-900',
                        'from-purple-600 via-fuchsia-700 to-slate-900',
                        'from-amber-600 via-orange-700 to-slate-900',
                        'from-rose-600 via-pink-700 to-slate-900',
                        'from-cyan-600 via-sky-700 to-slate-900',
                    ];
                    $grad = $gradients[abs(crc32($b->title ?? '')) % count($gradients)];
                @endphp
                <div class="bg-white border border-slate-200/90 rounded-3xl p-4 flex flex-col justify-between hover:shadow-lg hover:border-indigo-300 transition duration-300 group">
                    <div class="space-y-3">
                        <!-- Bìa sách nghệ thuật (Book Spine & Gradient) -->
                        <div class="relative h-48 rounded-2xl overflow-hidden cursor-pointer shadow-sm group-hover:shadow transition" onclick="document.getElementById('btn-view-{{ $b->id }}')?.click()">
                            @if($hasCover)
                                <img src="{{ $coverSrc }}" alt="{{ $b->title }}" class="w-full h-full object-cover group-hover:scale-105 transition duration-500">
                            @else
                                <div class="w-full h-full bg-gradient-to-br {{ $grad }} p-4 flex flex-col justify-between text-white relative border-l-4 border-white/20 select-none">
                                    <div class="flex items-center justify-between">
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-white/20 backdrop-blur-md text-[9px] font-semibold tracking-wider uppercase border border-white/20">
                                            {{ $catName }}
                                        </span>
                                        <i data-lucide="bookmark" class="w-4 h-4 text-white/80"></i>
                                    </div>
                                    <div class="space-y-1">
                                        <h4 class="font-serif font-bold text-xs sm:text-sm line-clamp-2 leading-snug drop-shadow-sm text-white">
                                            {{ $b->title }}
                                        </h4>
                                        <p class="text-[10px] text-white/80 line-clamp-1 italic">
                                            ✍️ {{ $b->author ?? 'Khuyết danh' }}
                                        </p>
                                    </div>
                                    <div class="text-[9px] text-white/60 tracking-wider uppercase font-mono">
                                        {{ $pubName }}
                                    </div>
                                </div>
                            @endif

                            <!-- Huy hiệu số lượng sách khả dụng nổi bật -->
                            <div class="absolute top-2.5 right-2.5">
                                @if($avail > 0)
                                    <span class="px-2.5 py-1 bg-emerald-600/90 text-white font-bold text-[10px] rounded-full backdrop-blur-md shadow-sm border border-emerald-400/30 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-300 animate-pulse"></span>
                                        Còn {{ $avail }}/{{ $total }} cuốn
                                    </span>
                                @else
                                    <span class="px-2.5 py-1 bg-rose-600/90 text-white font-bold text-[10px] rounded-full backdrop-blur-md shadow-sm border border-rose-400/30">
                                        Hết sách
                                    </span>
                                @endif
                            </div>
                        </div>

                        <!-- Thông tin sách -->
                        <div class="space-y-1.5">
                            <div class="flex items-center justify-between gap-1 text-[11px]">
                                <span class="text-slate-500 font-medium line-clamp-1 flex items-center gap-1">
                                    <i data-lucide="building-2" class="w-3.5 h-3.5 text-slate-400 shrink-0"></i>
                                    {{ $pubName }}
                                </span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 shrink-0">
                                    {{ $catName }}
                                </span>
                            </div>

                            <h3 class="font-bold text-sm text-slate-900 line-clamp-2 leading-snug cursor-pointer group-hover:text-indigo-600 transition" onclick="document.getElementById('btn-view-{{ $b->id }}')?.click()">
                                {{ $b->title }}
                            </h3>

                            <p class="text-xs text-slate-600 flex items-center gap-1">
                                <span class="text-slate-400">Tác giả:</span>
                                <strong class="text-slate-800 line-clamp-1">{{ $b->author ?? 'Khuyết danh' }}</strong>
                            </p>

                            @if(!empty($b->shelf_location))
                                <div class="p-2 bg-amber-50/80 rounded-xl border border-amber-200/60 text-xs text-amber-900 flex items-center justify-between mt-2">
                                    <span class="text-[11px] font-medium flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3.5 h-3.5 text-amber-600"></i>
                                        Vị trí kệ:
                                    </span>
                                    <span class="font-bold text-amber-950 font-mono text-[11px]">{{ $b->shelf_location }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Nút bấm xem chi tiết sách -->
                    <div class="pt-3 border-t border-slate-100 mt-3 flex items-center justify-between gap-2">
                        <div class="text-[11px] text-slate-500">
                            <span>Mã:</span>
                            <span class="font-mono text-slate-700 font-semibold">{{ $b->isbn ? substr($b->isbn, -6) : ('BK-' . $b->id) }}</span>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                id="btn-view-{{ $b->id }}"
                                data-id="{{ $b->id }}"
                                data-title="{{ htmlspecialchars($b->title, ENT_QUOTES) }}"
                                data-author="{{ htmlspecialchars($b->author ?? '', ENT_QUOTES) }}"
                                data-publisher="{{ htmlspecialchars($pubName, ENT_QUOTES) }}"
                                data-category="{{ htmlspecialchars($catName, ENT_QUOTES) }}"
                                data-isbn="{{ $b->isbn ?? '' }}"
                                data-shelf="{{ $b->shelf_location ?? 'Kệ A1' }}"
                                data-avail="{{ $avail }}"
                                data-total="{{ $total }}"
                                data-desc="{{ htmlspecialchars($b->description ?? '', ENT_QUOTES) }}"
                                onclick="openBookDetailModal(this)"
                                class="px-3 py-1.5 bg-indigo-50 hover:bg-indigo-600 text-indigo-700 hover:text-white rounded-xl font-bold text-xs transition flex items-center gap-1.5 cursor-pointer shadow-2xs">
                                <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                <span>Xem</span>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if(method_exists($books, 'links'))
            <div class="pt-4 border-t border-slate-100">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Modal: Chi Tiết Sách & Đăng Ký Mượn -->
<div id="modal-book-detail" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200 animate-fadeIn">
        <div class="flex items-start justify-between border-b border-slate-100 pb-3">
            <div>
                <span id="modal_detail_category" class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-sky-50 text-sky-700 border border-sky-200 uppercase tracking-wider">Thể loại</span>
                <h3 id="modal_detail_title" class="font-bold text-slate-900 text-lg mt-1 line-clamp-2">Tên Tác Phẩm</h3>
            </div>
            <button type="button" onclick="closeModal('modal-book-detail')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <div class="grid grid-cols-2 gap-3 text-xs">
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 space-y-0.5">
                <span class="text-slate-400 text-[10px] uppercase font-bold">Tác giả:</span>
                <p id="modal_detail_author" class="font-bold text-slate-800">...</p>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 space-y-0.5">
                <span class="text-slate-400 text-[10px] uppercase font-bold">Vị trí kệ sách:</span>
                <p id="modal_detail_shelf" class="font-bold text-amber-700">Kệ A1</p>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 space-y-0.5">
                <span class="text-slate-400 text-[10px] uppercase font-bold">Mã định danh ISBN:</span>
                <p id="modal_detail_isbn" class="font-mono font-bold text-slate-800">...</p>
            </div>
            <div class="p-2.5 bg-slate-50 rounded-xl border border-slate-100 space-y-0.5">
                <span class="text-slate-400 text-[10px] uppercase font-bold">Tồn kho khả dụng:</span>
                <p id="modal_detail_copies" class="font-bold text-emerald-700">...</p>
            </div>
        </div>

        <div class="bg-slate-50 border border-slate-200 rounded-2xl p-3 text-xs space-y-1">
            <div class="font-bold text-slate-800 flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-3.5 h-3.5 text-sky-600"></i>
                <span>Tóm Tắt Nội Dung / Giới Thiệu Tác Phẩm:</span>
            </div>
            <p id="modal_detail_description" class="text-slate-600 leading-relaxed text-[11px] max-h-28 overflow-y-auto pr-1 whitespace-pre-line"></p>
        </div>

        <form action="{{ url('/reader/borrow') }}" method="POST" id="form-borrow-book" onsubmit="return handleBorrowSubmit(event)" class="pt-2 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3">
            @csrf
            <input type="hidden" name="book_id" id="modal_borrow_book_id" value="">
            <span class="text-[11px] text-slate-500">Yêu cầu mượn sẽ được gửi ngay đến quầy ca trực của Thủ thư.</span>

            <div class="flex items-center gap-2">
                <button type="button" onclick="closeModal('modal-book-detail')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                    Đóng
                </button>
                <button type="submit" id="modal_detail_borrow_btn" class="px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5 min-w-[120px] cursor-pointer">
                    <i data-lucide="bookmark-plus" class="w-4 h-4"></i>
                    <span>Mượn Sách</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Thanh Toán (Gia Hạn Sách, Tiền Phạt & Gia Hạn Thẻ) -->
<div id="payment-modal" class="fixed inset-0 z-[100] bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200 animate-fadeIn">
        <!-- Header -->
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-base">Thanh Toán Trực Tuyến</h3>
                    <p class="text-[11px] text-slate-500">Cổng thanh toán tự động LibraNova</p>
                </div>
            </div>
            <button type="button" onclick="closePaymentModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- VIEW 1: BƯỚC CẤU HÌNH GIAO DỊCH -->
        <div id="payment-step-config" class="space-y-4">
            <!-- 1. Loại: 3 lựa chọn [Gia hạn sách], [Tiền phạt], [Gia hạn thẻ] -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-2">Chọn dịch vụ thanh toán:</label>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                    <!-- Option 1: Gia hạn sách mượn -->
                    <div id="payment-type-book-card" onclick="selectPaymentType('book_renewal')" class="cursor-pointer border-2 border-indigo-600 bg-indigo-50/60 rounded-2xl p-3 flex flex-col justify-between gap-1.5 transition relative shadow-xs">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="p-1 rounded-md bg-indigo-100 text-indigo-600"><i data-lucide="book-check" class="w-3.5 h-3.5"></i></span>
                                <span class="font-bold text-xs text-slate-900">Gia hạn sách</span>
                            </div>
                            <span id="payment-type-book-check" class="w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px]">
                                <i data-lucide="check" class="w-3 h-3 stroke-[3]"></i>
                            </span>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-500">+7 ngày hạn trả</div>
                            <div class="font-bold text-xs text-indigo-700 mt-0.5">
                                <span id="payment-book-amount-tag">10,000đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Option 2: Tiền phạt trễ hạn -->
                    <div id="payment-type-fine-card" onclick="selectPaymentType('fine')" class="cursor-pointer border-2 border-slate-200 rounded-2xl p-3 flex flex-col justify-between gap-1.5 hover:border-amber-300 transition relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="p-1 rounded-md bg-amber-50 text-amber-600"><i data-lucide="alert-circle" class="w-3.5 h-3.5"></i></span>
                                <span class="font-bold text-xs text-slate-900">Tiền phạt</span>
                            </div>
                            <span id="payment-type-fine-check" class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-white text-[10px]"></span>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-500">Phạt trễ hạn sách</div>
                            <div class="font-bold text-xs text-rose-600 mt-0.5">
                                <span id="payment-fine-amount-tag">{{ number_format(max(0, (float)$totalUnpaidFines)) }}đ</span>
                            </div>
                        </div>
                    </div>

                    <!-- Option 3: Gia hạn thẻ -->
                    <div id="payment-type-card-card" onclick="selectPaymentType('card_renewal')" class="cursor-pointer border-2 border-slate-200 rounded-2xl p-3 flex flex-col justify-between gap-1.5 hover:border-sky-300 transition relative">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="p-1 rounded-md bg-sky-100 text-sky-600"><i data-lucide="calendar-check" class="w-3.5 h-3.5"></i></span>
                                <span class="font-bold text-xs text-slate-900">Gia hạn thẻ</span>
                            </div>
                            <span id="payment-type-card-check" class="w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-white text-[10px]"></span>
                        </div>
                        <div>
                            <div class="text-[11px] text-slate-500">+12 tháng niên khóa</div>
                            <div class="font-bold text-xs text-sky-700 mt-0.5">
                                {{ number_format(max(30000, (float)($rules->card_renewal_fee ?? 30000))) }}đ
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ô LỰA CHỌN SÁCH ĐANG MƯỢN CẦN GIA HẠN -->
            <div id="payment-book-select-container" class="space-y-1.5 p-3.5 bg-indigo-50/60 border border-indigo-200/80 rounded-2xl">
                <div class="flex items-center justify-between">
                    <label for="payment-renewal-book-select" class="text-xs font-bold text-indigo-950 flex items-center gap-1.5">
                        <i data-lucide="book-open" class="w-4 h-4 text-indigo-600"></i>
                        <span>Chọn sách đang mượn cần gia hạn:</span>
                    </label>
                    <span class="text-[10px] font-bold text-indigo-700 bg-indigo-100/90 px-2 py-0.5 rounded-full border border-indigo-200">
                        {{ $activeLoans->count() }} cuốn đang mượn
                    </span>
                </div>
                <div class="relative">
                    <select id="payment-renewal-book-select" onchange="onRenewalBookSelected(this.value)" class="w-full py-2.5 px-3 text-xs bg-white border border-indigo-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 font-medium text-slate-800 shadow-2xs cursor-pointer">
                        @if($activeLoans->count() === 0)
                            <option value="" disabled selected>-- Bạn hiện không có sách nào đang mượn --</option>
                        @else
                            <option value="">-- Bấm vào đây để chọn cuốn sách cần gia hạn --</option>
                            @foreach($activeLoans as $al)
                                @php
                                    $alOverdue = ($al->status === 'overdue' || (int)$al->overdue_days != 0 || (float)$al->fine_amount > 0);
                                    $alFine = abs((float)$al->fine_amount);
                                    if ($alOverdue && $alFine == 0) {
                                        $alFine = abs((int)$al->overdue_days) > 0 ? (abs((int)$al->overdue_days) * $finePerDay) : $finePerDay;
                                    }
                                    $alCost = $alOverdue ? max(10000, (int)$alFine) : 10000;
                                    $alDue = $al->due_date ? \Carbon\Carbon::parse($al->due_date)->format('d/m/Y') : date('d/m/Y');
                                    $alCanRenew = ((int)($al->renew_count ?? 0) < (int)$maxRenews);
                                    $alTitle = $al->book->title ?? 'Sách thư viện';
                                    $alCode = $al->ticket_code ?: ('TK-' . str_pad($al->id, 4, '0', STR_PAD_LEFT));
                                @endphp
                                <option value="{{ $al->id }}"
                                    data-code="{{ $alCode }}"
                                    data-title="{{ htmlspecialchars($alTitle, ENT_QUOTES) }}"
                                    data-overdue="{{ $alOverdue ? '1' : '0' }}"
                                    data-cost="{{ $alCost }}"
                                    data-due="{{ $alDue }}"
                                    data-fine="{{ $alFine }}"
                                    data-renew-count="{{ $al->renew_count }}"
                                    data-can-renew="{{ $alCanRenew ? '1' : '0' }}">
                                    📖 {{ $alTitle }} (#{{ $alCode }}) - Hạn trả: {{ $alDue }} {{ $alOverdue ? '⚠️ [Trễ hạn - Phạt: ' . number_format($alCost) . 'đ]' : '[Phí: ' . number_format($alCost) . 'đ]' }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div id="renewal-book-status-hint" class="flex items-center gap-1.5 text-[11px] text-slate-500 pt-0.5">
                    <i data-lucide="info" class="w-3.5 h-3.5 text-indigo-500 shrink-0"></i>
                    <span>Chọn cuốn sách bạn muốn gia hạn để tự động cộng thêm 7 ngày hạn trả sau khi thanh toán.</span>
                </div>
            </div>

            <!-- Banner thông báo tự động gia hạn khi nộp phạt -->
            <div id="payment-autorenew-banner" class="hidden p-3 bg-amber-50 border border-amber-200 rounded-2xl text-xs text-amber-900 flex items-start gap-2.5">
                <i data-lucide="sparkles" class="w-4 h-4 text-amber-600 mt-0.5 shrink-0"></i>
                <div class="leading-relaxed">
                    Sách đang trễ hạn mượn. Sau khi quét mã thanh toán, hệ thống sẽ <strong>tự động xóa phạt và gia hạn thêm 7 ngày</strong>!
                </div>
            </div>

            <!-- Tóm tắt chi tiết khoản thanh toán -->
            <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-3.5 space-y-2 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Khoản mục:</span>
                    <strong id="payment-summary-title" class="text-slate-900 font-bold">Gia hạn mượn sách (+7 ngày)</strong>
                </div>
                <div id="payment-summary-book-row" class="hidden flex justify-between items-center text-slate-600">
                    <span>Tác phẩm:</span>
                    <strong id="payment-summary-book-title" class="text-indigo-900 font-semibold line-clamp-1 max-w-[220px]">...</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Mã thẻ độc giả:</span>
                    <strong class="font-mono text-slate-800">{{ $user->card_number ?? 'CHƯA CẤP THẺ' }}</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600 border-t border-slate-200/60 pt-2">
                    <span class="font-semibold text-slate-800">Tổng tiền thanh toán:</span>
                    <span id="payment-summary-amount" class="text-base font-extrabold text-indigo-700">10,000đ</span>
                </div>
            </div>

            <!-- 2. Phương thức thanh toán -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Phương thức thanh toán</label>
                <select id="payment_method_select" class="w-full py-2.5 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500 font-medium">
                    <option value="vietqr">Chuyển khoản VietQR Napas 247 (Tự động nhận diện)</option>
                    <option value="momo">Ví điện tử MoMo</option>
                    <option value="vnpay">Cổng thanh toán VNPAY-QR</option>
                </select>
            </div>

            <!-- Actions Step 1 -->
            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closePaymentModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                    Hủy bỏ
                </button>
                <button type="button" onclick="handleGeneratePaymentQR()" class="px-5 py-2.5 bg-sky-600 hover:bg-sky-500 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5 cursor-pointer">
                    <span>Tạo Mã Thanh Toán QR</span>
                    <i data-lucide="arrow-right" class="w-4 h-4"></i>
                </button>
            </div>
        </div>

        <!-- VIEW 2: BƯỚC QUÉT MÃ QR & XÁC NHẬN -->
        <div id="payment-step-qr" class="space-y-4 hidden">
            <div id="payment-qr-badge" class="px-3 py-1.5 rounded-xl text-center text-xs font-bold flex items-center justify-center gap-2 bg-sky-50 text-sky-800 border border-sky-200">
                <i data-lucide="qr-code" class="w-4 h-4"></i>
                <span id="payment-qr-badge-text">Quét mã VietQR Napas 247</span>
            </div>

            <!-- Khung hình ảnh QR Code -->
            <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 flex flex-col items-center justify-center relative">
                <img id="payment-qr-image" src="" alt="Mã QR Thanh Toán" class="w-52 h-52 object-contain bg-white p-2 rounded-xl shadow-xs border border-slate-100">
                <p id="payment-qr-guide" class="text-[11px] text-slate-500 text-center mt-2.5 max-w-xs leading-relaxed">
                    Mở ứng dụng Ngân hàng (MB, Vietcombank, Techcombank, BIDV...) hoặc MoMo quét mã để thanh toán tự động
                </p>
            </div>

            <!-- Thông tin chi tiết giao dịch QR -->
            <div class="bg-slate-50 rounded-2xl p-3 border border-slate-100 space-y-1.5 text-xs">
                <div class="flex justify-between items-center text-slate-600">
                    <span>Dịch vụ:</span>
                    <strong id="payment-qr-type-text" class="text-slate-900">Gia hạn sách</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span id="payment-qr-acc-label">Số tài khoản thụ hưởng:</span>
                    <strong id="payment-qr-acc-val" class="font-mono text-slate-900">0987654321</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Chủ tài khoản:</span>
                    <strong id="payment-qr-holder-val" class="text-slate-900">THU VIEN LIBRANOVA QUOC GIA</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600">
                    <span>Số tiền cần chuyển:</span>
                    <strong id="payment-qr-amount-text" class="text-base text-rose-600 font-bold">10,000đ</strong>
                </div>
                <div class="flex justify-between items-center text-slate-600 border-t border-slate-200/60 pt-1.5">
                    <span>Nội dung chuyển khoản:</span>
                    <span id="payment-qr-desc-text" class="font-mono font-bold text-sky-700 bg-sky-50 px-2 py-0.5 rounded text-[11px] border border-sky-100">...</span>
                </div>
            </div>

            <div class="p-2.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px] flex items-center gap-2">
                <i data-lucide="shield-check" class="w-4 h-4 text-emerald-600 shrink-0"></i>
                <span>Giao dịch được đối soát và kích hoạt hạn trả mới tự động ngay khi chuyển khoản thành công.</span>
            </div>

            <!-- Actions Step 2 -->
            <div class="flex items-center justify-between gap-2 pt-1 border-t border-slate-100">
                <button type="button" onclick="backToPaymentConfig()" class="px-3.5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition flex items-center gap-1.5 cursor-pointer">
                    <i data-lucide="arrow-left" class="w-3.5 h-3.5"></i>
                    <span>Quay lại</span>
                </button>
                <button type="button" id="btn-confirm-payment-final" onclick="confirmPaymentFinished()" class="flex-1 py-2.5 bg-emerald-600 hover:bg-emerald-500 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5 cursor-pointer">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Tôi Đã Chuyển Khoản Thành Công</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Đánh Giá Sách -->
<div id="rate-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 border border-slate-200 animate-fadeIn">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-base">Đánh Giá Tác Phẩm</h3>
            <button type="button" onclick="closeRateModal()" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('reader.book.rate') }}" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" name="book_id" id="rate-book-id" value="">

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Tác phẩm:</label>
                <p id="rate-book-title" class="font-bold text-slate-800 text-sm">...</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-2">Đánh giá điểm sao:</label>
                <div class="flex items-center gap-3">
                    @for($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer">
                            <input type="radio" name="rating" value="{{ $i }}" {{ $i === 5 ? 'checked' : '' }} class="sr-only peer">
                            <span class="text-2xl text-slate-300 peer-checked:text-amber-400 hover:text-amber-300 transition">★</span>
                        </label>
                    @endfor
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cảm nghĩ hoặc nhận xét:</label>
                <textarea name="comment" rows="3" placeholder="Chia sẻ cảm nhận của bạn về cuốn sách này..." class="w-full p-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
            </div>

            <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeRateModal()" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition cursor-pointer">
                    Đóng
                </button>
                <button type="submit" class="px-5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow transition cursor-pointer">
                    Gửi Đánh Giá
                </button>
            </div>
        </form>
    </div>
</div>

<!-- CẤU HÌNH BIẾN TOÀN CỤC CHO JS THANH TOÁN -->
<input type="hidden" id="cfg_bank_name" value="{{ $rules->bank_name ?? 'MB Bank' }}">
<input type="hidden" id="cfg_bank_account" value="{{ $rules->bank_account ?? '0987654321' }}">
<input type="hidden" id="cfg_account_holder" value="{{ $rules->account_holder ?? 'THU VIEN LIBRANOVA QUOC GIA' }}">
<input type="hidden" id="cfg_csrf_token" value="{{ csrf_token() }}">
<input type="hidden" id="cfg_user_has_card" value="{{ $hasCard ? '1' : '0' }}">
<input type="hidden" id="cfg_user_card_number" value="{{ $user->card_number ?? '' }}">
<input type="hidden" id="cfg_user_name" value="{{ $user->name ?? 'Độc giả' }}">
<input type="hidden" id="cfg_user_email" value="{{ $user->email ?? 'reader@libranova.vn' }}">
<input type="hidden" id="cfg_card_renewal_fee" value="{{ abs((int)($rules->card_renewal_fee ?? 30000)) }}">
<input type="hidden" id="cfg_total_unpaid_fines" value="{{ abs((int)$totalUnpaidFines) }}">

<script>
    const userHasCard = (document.getElementById('cfg_user_has_card') && document.getElementById('cfg_user_has_card').value === '1');
    let activeTicketId = null;

    let paymentSelectedType = 'book_renewal';
    let paymentSelectedTicketId = null;
    let paymentSelectedTicketCode = '';
    let paymentSelectedBookTitle = '';
    let paymentCustomFineAmount = null;
    let paymentBookRenewalCost = 10000;
    let paymentAutoRenew = false;
    let paymentIsOverdue = false;

    function notifyNoCard() {
        alert("Bạn chưa được cấp thẻ! Vui lòng Cấp thẻ Độc Giả.");
        return false;
    }

    function handleBorrowSubmit(event) {
        if (!userHasCard) {
            if (event && event.preventDefault) event.preventDefault();
            notifyNoCard();
            return false;
        }
        return true;
    }

    function handleBookRenewalClick(btn) {
        if (!btn) return;
        const d = btn.dataset;
        openBookRenewalPayment(
            d.id, 
            d.code, 
            d.title, 
            d.overdue === '1', 
            parseInt(d.cost) || 10000, 
            d.due
        );
    }

    function openBookRenewalPayment(ticketId, ticketCode, bookTitle, isOverdue, cost, currentDueDate) {
        paymentSelectedTicketId = ticketId;
        paymentSelectedTicketCode = ticketCode || '';
        paymentSelectedBookTitle = bookTitle || 'Sách mượn';
        paymentIsOverdue = (isOverdue === 1 || isOverdue === true || isOverdue === '1');
        paymentBookRenewalCost = Math.abs(parseInt(cost)) || (paymentIsOverdue ? 30000 : 10000);
        paymentCustomFineAmount = paymentIsOverdue ? paymentBookRenewalCost : null;
        paymentAutoRenew = true;

        const bookSelect = document.getElementById('payment-renewal-book-select');
        if (bookSelect && ticketId) {
            bookSelect.value = ticketId;
        }

        openPaymentModal('book_renewal', ticketId, paymentBookRenewalCost, ticketCode, true, bookTitle);
    }

    function onRenewalBookSelected(ticketId, triggerSelectType = true) {
        const select = document.getElementById('payment-renewal-book-select');
        if (!select || !ticketId) return;

        const opt = select.options[select.selectedIndex];
        if (!opt) return;

        paymentSelectedTicketId = ticketId;
        paymentSelectedTicketCode = opt.dataset.code || '';
        paymentSelectedBookTitle = opt.dataset.title || '';
        paymentIsOverdue = (opt.dataset.overdue === '1');
        paymentBookRenewalCost = parseInt(opt.dataset.cost) || (paymentIsOverdue ? 30000 : 10000);
        paymentCustomFineAmount = paymentIsOverdue ? paymentBookRenewalCost : null;
        paymentAutoRenew = true;

        const hint = document.getElementById('renewal-book-status-hint');
        if (hint) {
            if (paymentIsOverdue) {
                hint.innerHTML = '<i data-lucide="alert-triangle" class="w-3.5 h-3.5 text-rose-500 shrink-0"></i><span class="text-rose-700 font-semibold">Sách đã quá hạn! Thanh toán bao gồm phí gia hạn + tiền phạt trễ hạn. Hạn trả mới sẽ được cộng thêm 7 ngày.</span>';
            } else {
                hint.innerHTML = '<i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-500 shrink-0"></i><span class="text-emerald-700 font-medium">Sách còn trong hạn. Sau khi thanh toán, hệ thống tự động cộng thêm 7 ngày hạn trả mới.</span>';
            }
            if (window.lucide) window.lucide.createIcons();
        }

        if (triggerSelectType) {
            selectPaymentType('book_renewal');
        } else {
            const bookTag = document.getElementById('payment-book-amount-tag');
            if (bookTag) bookTag.textContent = paymentBookRenewalCost.toLocaleString('vi-VN') + 'đ';
            const summaryTitle = document.getElementById('payment-summary-title');
            const summaryAmount = document.getElementById('payment-summary-amount');
            const summaryBookRow = document.getElementById('payment-summary-book-row');
            const summaryBookTitle = document.getElementById('payment-summary-book-title');
            const autoRenewBanner = document.getElementById('payment-autorenew-banner');

            if (summaryTitle) {
                summaryTitle.textContent = paymentSelectedTicketCode 
                    ? `Gia hạn mượn sách (+7 ngày) phiếu #${paymentSelectedTicketCode}` 
                    : 'Gia hạn mượn sách (+7 ngày)';
            }
            if (summaryAmount) {
                summaryAmount.textContent = paymentBookRenewalCost.toLocaleString('vi-VN') + 'đ';
            }
            if (summaryBookRow) {
                summaryBookRow.classList.remove('hidden');
                if (summaryBookTitle) summaryBookTitle.textContent = paymentSelectedBookTitle;
            }
            if (autoRenewBanner) {
                if (paymentIsOverdue) autoRenewBanner.classList.remove('hidden');
                else autoRenewBanner.classList.add('hidden');
            }
        }
    }

    function openPaymentModal(type = 'card_renewal', ticketId = null, fineAmount = null, ticketCode = null, autoRenew = false, bookTitle = null) {
        paymentSelectedTicketId = ticketId;
        paymentSelectedTicketCode = ticketCode || '';
        if (bookTitle) paymentSelectedBookTitle = bookTitle;
        if (fineAmount !== null) {
            paymentCustomFineAmount = Math.abs(parseInt(fineAmount));
            if (type === 'book_renewal') {
                paymentBookRenewalCost = paymentCustomFineAmount;
            }
        }
        paymentAutoRenew = !!autoRenew;

        const stepConfig = document.getElementById('payment-step-config');
        const stepQr = document.getElementById('payment-step-qr');
        if (stepConfig) {
            stepConfig.classList.remove('hidden');
            stepConfig.style.display = 'block';
        }
        if (stepQr) {
            stepQr.classList.add('hidden');
            stepQr.style.display = 'none';
        }

        const modal = document.getElementById('payment-modal');
        if (modal) {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            modal.style.display = 'flex';
        }

        const bookSelect = document.getElementById('payment-renewal-book-select');
        if (bookSelect && ticketId) {
            bookSelect.value = ticketId;
        }

        selectPaymentType(type);

        if (window.lucide) window.lucide.createIcons();
    }

    function closePaymentModal() {
        const modal = document.getElementById('payment-modal');
        if (modal) {
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    }

    function selectPaymentType(type) {
        paymentSelectedType = type;
        const bookCard = document.getElementById('payment-type-book-card');
        const fineCard = document.getElementById('payment-type-fine-card');
        const cardCard = document.getElementById('payment-type-card-card');

        const bookCheck = document.getElementById('payment-type-book-check');
        const fineCheck = document.getElementById('payment-type-fine-check');
        const cardCheck = document.getElementById('payment-type-card-check');

        const bookSelectContainer = document.getElementById('payment-book-select-container');

        const renewalFee = Math.abs(parseInt(document.getElementById('cfg_card_renewal_fee')?.value || '30000'));
        const totalUnpaidFines = Math.abs(parseInt(document.getElementById('cfg_total_unpaid_fines')?.value || '0'));
        let fineAmount = Math.abs(paymentCustomFineAmount !== null ? paymentCustomFineAmount : totalUnpaidFines);
        if (fineAmount <= 0 && paymentAutoRenew && type === 'fine') {
            fineAmount = 30000;
        }
        let bookCost = paymentBookRenewalCost > 0 ? paymentBookRenewalCost : (paymentIsOverdue ? (fineAmount > 0 ? fineAmount : 30000) : 10000);

        if (bookSelectContainer) {
            if (type === 'book_renewal') {
                bookSelectContainer.classList.remove('hidden');
                bookSelectContainer.style.display = 'block';

                const bookSelect = document.getElementById('payment-renewal-book-select');
                if (bookSelect) {
                    if (paymentSelectedTicketId) {
                        bookSelect.value = paymentSelectedTicketId;
                    } else if (bookSelect.options.length > 1) {
                        for (let i = 0; i < bookSelect.options.length; i++) {
                            if (bookSelect.options[i].value) {
                                bookSelect.selectedIndex = i;
                                onRenewalBookSelected(bookSelect.options[i].value, false);
                                bookCost = paymentBookRenewalCost;
                                break;
                            }
                        }
                    }
                }
            } else {
                bookSelectContainer.classList.add('hidden');
                bookSelectContainer.style.display = 'none';
            }
        }

        const bookTag = document.getElementById('payment-book-amount-tag');
        const fineTag = document.getElementById('payment-fine-amount-tag');
        if (bookTag) bookTag.textContent = bookCost.toLocaleString('vi-VN') + 'đ';
        if (fineTag) fineTag.textContent = (fineAmount > 0 ? fineAmount : totalUnpaidFines).toLocaleString('vi-VN') + 'đ';

        const summaryTitle = document.getElementById('payment-summary-title');
        const summaryAmount = document.getElementById('payment-summary-amount');
        const summaryBookRow = document.getElementById('payment-summary-book-row');
        const summaryBookTitle = document.getElementById('payment-summary-book-title');
        const autoRenewBanner = document.getElementById('payment-autorenew-banner');

        const normalCardClass = 'cursor-pointer border-2 border-slate-200 rounded-2xl p-3 flex flex-col justify-between gap-1.5 hover:border-slate-300 transition relative';
        const uncheckClass = 'w-4 h-4 rounded-full border border-slate-300 flex items-center justify-center text-white text-[10px]';

        if (bookCard) bookCard.className = normalCardClass;
        if (fineCard) fineCard.className = normalCardClass;
        if (cardCard) cardCard.className = normalCardClass;

        if (bookCheck) { bookCheck.className = uncheckClass; bookCheck.innerHTML = ''; }
        if (fineCheck) { fineCheck.className = uncheckClass; fineCheck.innerHTML = ''; }
        if (cardCheck) { cardCheck.className = uncheckClass; cardCheck.innerHTML = ''; }

        if (type === 'book_renewal') {
            paymentAutoRenew = true;
            if (autoRenewBanner) {
                if (paymentIsOverdue) autoRenewBanner.classList.remove('hidden');
                else autoRenewBanner.classList.add('hidden');
            }

            if (bookCard) bookCard.className = 'cursor-pointer border-2 border-indigo-600 bg-indigo-50/60 rounded-2xl p-3 flex flex-col justify-between gap-1.5 transition relative shadow-xs';
            if (bookCheck) {
                bookCheck.className = 'w-4 h-4 rounded-full bg-indigo-600 flex items-center justify-center text-white text-[10px]';
                bookCheck.innerHTML = '<i data-lucide="check" class="w-3 h-3 stroke-[3]"></i>';
            }

            if (summaryTitle) {
                summaryTitle.textContent = paymentSelectedTicketCode 
                    ? `Gia hạn mượn sách (+7 ngày) phiếu #${paymentSelectedTicketCode}` 
                    : 'Gia hạn mượn sách (+7 ngày)';
            }
            if (summaryAmount) {
                summaryAmount.className = 'text-base font-extrabold text-indigo-700';
                summaryAmount.textContent = bookCost.toLocaleString('vi-VN') + 'đ';
            }
            if (summaryBookRow) {
                if (paymentSelectedBookTitle) {
                    summaryBookRow.classList.remove('hidden');
                    if (summaryBookTitle) summaryBookTitle.textContent = paymentSelectedBookTitle;
                } else {
                    summaryBookRow.classList.add('hidden');
                }
            }
        } else if (type === 'fine') {
            if (autoRenewBanner) {
                if (paymentAutoRenew) autoRenewBanner.classList.remove('hidden');
                else autoRenewBanner.classList.add('hidden');
            }

            if (fineCard) fineCard.className = 'cursor-pointer border-2 border-amber-500 bg-amber-50/60 rounded-2xl p-3 flex flex-col justify-between gap-1.5 transition relative shadow-xs';
            if (fineCheck) {
                fineCheck.className = 'w-4 h-4 rounded-full bg-amber-600 flex items-center justify-center text-white text-[10px]';
                fineCheck.innerHTML = '<i data-lucide="check" class="w-3 h-3 stroke-[3]"></i>';
            }

            if (summaryTitle) {
                summaryTitle.textContent = paymentSelectedTicketCode 
                    ? `Nộp phạt trễ hạn phiếu #${paymentSelectedTicketCode}` 
                    : 'Nộp phạt trễ hạn mượn sách';
            }
            if (summaryAmount) {
                summaryAmount.className = 'text-base font-extrabold text-rose-700';
                summaryAmount.textContent = fineAmount.toLocaleString('vi-VN') + 'đ';
            }
            if (summaryBookRow) summaryBookRow.classList.add('hidden');
        } else {
            paymentAutoRenew = false;
            if (autoRenewBanner) autoRenewBanner.classList.add('hidden');

            if (cardCard) cardCard.className = 'cursor-pointer border-2 border-sky-500 bg-sky-50/60 rounded-2xl p-3 flex flex-col justify-between gap-1.5 transition relative shadow-xs';
            if (cardCheck) {
                cardCheck.className = 'w-4 h-4 rounded-full bg-sky-600 flex items-center justify-center text-white text-[10px]';
                cardCheck.innerHTML = '<i data-lucide="check" class="w-3 h-3 stroke-[3]"></i>';
            }

            if (summaryTitle) summaryTitle.textContent = 'Gia hạn thẻ độc giả niên khóa 1 năm';
            if (summaryAmount) {
                summaryAmount.className = 'text-base font-extrabold text-sky-700';
                summaryAmount.textContent = renewalFee.toLocaleString('vi-VN') + 'đ';
            }
            if (summaryBookRow) summaryBookRow.classList.add('hidden');
        }

        if (window.lucide) window.lucide.createIcons();
    }

    function handleGeneratePaymentQR() {
        const renewalFee = Math.abs(parseInt(document.getElementById('cfg_card_renewal_fee')?.value || '30000'));
        const totalUnpaidFines = Math.abs(parseInt(document.getElementById('cfg_total_unpaid_fines')?.value || '0'));
        let fineAmount = Math.abs(paymentCustomFineAmount !== null ? paymentCustomFineAmount : totalUnpaidFines);
        if (fineAmount <= 0 && paymentAutoRenew) fineAmount = 30000;
        let bookCost = paymentBookRenewalCost > 0 ? paymentBookRenewalCost : (paymentIsOverdue ? (fineAmount > 0 ? fineAmount : 30000) : 10000);

        let amount = 30000;
        if (paymentSelectedType === 'card_renewal') {
            amount = renewalFee;
        } else if (paymentSelectedType === 'book_renewal') {
            amount = bookCost;
        } else {
            amount = fineAmount;
        }

        if (amount <= 0) amount = 10000;

        if (paymentSelectedType === 'card_renewal' && !userHasCard) {
            notifyNoCard();
            return;
        }

        if (paymentSelectedType === 'book_renewal') {
            const bookSelect = document.getElementById('payment-renewal-book-select');
            if (bookSelect && bookSelect.value) {
                paymentSelectedTicketId = bookSelect.value;
            }
            if (!paymentSelectedTicketId) {
                alert("Vui lòng chọn cuốn sách đang mượn mà bạn muốn gia hạn!");
                if (bookSelect) bookSelect.focus();
                return;
            }
        }

        const method = document.getElementById('payment_method_select')?.value || 'vietqr';
        const rawBank = (document.getElementById('cfg_bank_name')?.value || 'MB Bank').toUpperCase();
        let bank = 'MB';
        if (rawBank.indexOf('MB') !== -1) bank = 'MB';
        else if (rawBank.indexOf('TECHCOM') !== -1 || rawBank.indexOf('TCB') !== -1) bank = 'TCB';
        else if (rawBank.indexOf('VIETCOM') !== -1 || rawBank.indexOf('VCB') !== -1) bank = 'VCB';
        else if (rawBank.indexOf('VIETIN') !== -1 || rawBank.indexOf('CTG') !== -1 || rawBank.indexOf('ICB') !== -1) bank = 'ICB';
        else if (rawBank.indexOf('BIDV') !== -1) bank = 'BIDV';
        else if (rawBank.indexOf('ACB') !== -1) bank = 'ACB';
        else if (rawBank.indexOf('TPB') !== -1 || rawBank.indexOf('TIEN PHONG') !== -1) bank = 'TPB';
        else if (rawBank.indexOf('VPB') !== -1 || rawBank.indexOf('VPBANK') !== -1) bank = 'VPB';
        else bank = rawBank.split(/[\s(]/)[0].replace(/[^a-zA-Z0-9]/g, '') || 'MB';

        const account = document.getElementById('cfg_bank_account')?.value || '0987654321';
        const holder = document.getElementById('cfg_account_holder')?.value || 'THU VIEN LIBRANOVA QUOC GIA';
        const cardNumber = document.getElementById('cfg_user_card_number')?.value || 'CHUA CAP THE';
        const userEmail = document.getElementById('cfg_user_email')?.value || 'reader@libranova.vn';

        let desc = '';
        if (paymentSelectedType === 'card_renewal') {
            desc = 'GIA HAN THE ' + cardNumber;
        } else if (paymentSelectedType === 'book_renewal') {
            desc = 'GIA HAN ' + (paymentSelectedTicketCode || cardNumber);
        } else {
            desc = 'NOP PHAT ' + (paymentSelectedTicketCode || cardNumber);
        }

        const qrImg = document.getElementById('payment-qr-image');
        const qrBadge = document.getElementById('payment-qr-badge');
        const qrBadgeText = document.getElementById('payment-qr-badge-text');
        const qrGuide = document.getElementById('payment-qr-guide');
        const qrTypeText = document.getElementById('payment-qr-type-text');
        const qrAmountText = document.getElementById('payment-qr-amount-text');
        const qrDescText = document.getElementById('payment-qr-desc-text');
        const qrAccLabel = document.getElementById('payment-qr-acc-label');
        const qrAccVal = document.getElementById('payment-qr-acc-val');
        const qrHolderVal = document.getElementById('payment-qr-holder-val');

        if (qrTypeText) {
            if (paymentSelectedType === 'card_renewal') {
                qrTypeText.textContent = 'Gia hạn thẻ thư viện (1 năm)';
            } else if (paymentSelectedType === 'book_renewal') {
                qrTypeText.textContent = 'Gia hạn mượn sách (+7 ngày)';
            } else {
                qrTypeText.textContent = 'Nộp phạt trễ hạn mượn sách';
            }
        }
        if (qrAmountText) qrAmountText.textContent = Number(amount).toLocaleString('vi-VN') + 'đ';
        if (qrDescText) qrDescText.textContent = desc;
        if (qrHolderVal) qrHolderVal.textContent = holder;

        if (method === 'vietqr') {
            const qrUrl = 'https://img.vietqr.io/image/' + bank + '-' + account + '-compact2.png?amount=' + amount + '&addInfo=' + encodeURIComponent(desc) + '&accountName=' + encodeURIComponent(holder);
            if (qrImg) qrImg.src = qrUrl;
            if (qrBadge) qrBadge.className = 'px-3 py-1.5 rounded-xl text-center text-xs font-bold flex items-center justify-center gap-2 bg-sky-50 text-sky-800 border border-sky-200';
            if (qrBadgeText) qrBadgeText.textContent = 'Quét mã VietQR Napas 247';
            if (qrGuide) qrGuide.textContent = 'Mở ứng dụng Ngân hàng (MB, Vietcombank, Techcombank, BIDV...) hoặc MoMo quét mã để thanh toán tự động';
            if (qrAccLabel) qrAccLabel.textContent = 'Ngân hàng & STK:';
            if (qrAccVal) qrAccVal.textContent = (document.getElementById('cfg_bank_name')?.value || 'MB Bank') + ' - ' + account;
        } else if (method === 'momo') {
            const momoData = '2|99|' + account + '|' + holder + '|' + userEmail + '|0|0|' + amount + '|' + desc + '|transfer_myqr';
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(momoData);
            if (qrImg) qrImg.src = qrUrl;
            if (qrBadge) qrBadge.className = 'px-3 py-1.5 rounded-xl text-center text-xs font-bold flex items-center justify-center gap-2 bg-pink-50 text-pink-700 border border-pink-200';
            if (qrBadgeText) qrBadgeText.textContent = 'Ví Điện Tử MoMo';
            if (qrGuide) qrGuide.textContent = 'Mở ứng dụng MoMo chọn "Quét mã" để quét mã QR và xác nhận số tiền thanh toán tức thì';
            if (qrAccLabel) qrAccLabel.textContent = 'Ví MoMo nhận tiền:';
            if (qrAccVal) qrAccVal.textContent = account + ' (Ví MoMo Thư Viện)';
        } else if (method === 'vnpay') {
            const vnpayData = '00020101021238540010A000000727012400069704220110' + account + '0208QRIBFTTA5303704540' + amount + '5802VN62' + String(desc.length).padStart(2, '0') + desc + '6304';
            const qrUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=' + encodeURIComponent(vnpayData);
            if (qrImg) qrImg.src = qrUrl;
            if (qrBadge) qrBadge.className = 'px-3 py-1.5 rounded-xl text-center text-xs font-bold flex items-center justify-center gap-2 bg-red-50 text-red-700 border border-red-200';
            if (qrBadgeText) qrBadgeText.textContent = 'Cổng VNPAY-QR';
            if (qrGuide) qrGuide.textContent = 'Mở ứng dụng Mobile Banking hoặc Ví VNPAY quét mã QR để thanh toán';
            if (qrAccLabel) qrAccLabel.textContent = 'Điểm chấp nhận VNPAY:';
            if (qrAccVal) qrAccVal.textContent = 'VNPAY-LIBRA-01 (' + account + ')';
        }

        const stepConfig = document.getElementById('payment-step-config');
        const stepQr = document.getElementById('payment-step-qr');
        if (stepConfig) {
            stepConfig.classList.add('hidden');
            stepConfig.style.display = 'none';
        }
        if (stepQr) {
            stepQr.classList.remove('hidden');
            stepQr.style.display = 'block';
        }
    }

    function backToPaymentConfig() {
        const stepConfig = document.getElementById('payment-step-config');
        const stepQr = document.getElementById('payment-step-qr');
        if (stepQr) {
            stepQr.classList.add('hidden');
            stepQr.style.display = 'none';
        }
        if (stepConfig) {
            stepConfig.classList.remove('hidden');
            stepConfig.style.display = 'block';
        }
    }

    function confirmPaymentFinished() {
        const csrfToken = document.getElementById('cfg_csrf_token')?.value || '';
        const method = document.getElementById('payment_method_select')?.value || 'vietqr';
        const renewalFee = Math.abs(parseInt(document.getElementById('cfg_card_renewal_fee')?.value || '30000'));
        const totalUnpaidFines = Math.abs(parseInt(document.getElementById('cfg_total_unpaid_fines')?.value || '0'));
        let fineAmount = Math.abs(paymentCustomFineAmount !== null ? paymentCustomFineAmount : totalUnpaidFines);
        if (fineAmount <= 0 && paymentAutoRenew) fineAmount = 30000;
        let bookCost = paymentBookRenewalCost > 0 ? paymentBookRenewalCost : (paymentIsOverdue ? (fineAmount > 0 ? fineAmount : 30000) : 10000);

        if (paymentSelectedType === 'book_renewal') {
            const bookSelect = document.getElementById('payment-renewal-book-select');
            if (bookSelect && bookSelect.value) {
                paymentSelectedTicketId = bookSelect.value;
            }
            if (!paymentSelectedTicketId) {
                alert("Vui lòng chọn cuốn sách đang mượn cần gia hạn!");
                return;
            }
        }

        let amount = 30000;
        if (paymentSelectedType === 'card_renewal') {
            amount = renewalFee;
        } else if (paymentSelectedType === 'book_renewal') {
            amount = bookCost;
        } else {
            amount = fineAmount;
        }
        if (amount <= 0) amount = 10000;

        const confirmBtn = document.getElementById('btn-confirm-payment-final');
        if (confirmBtn) {
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<span>⏳ Đang xử lý giao dịch...</span>';
        }

        fetch('/payment/confirm', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                type: paymentSelectedType,
                ticket_id: paymentSelectedTicketId,
                amount: amount,
                payment_method: method,
                auto_renew: paymentAutoRenew || (paymentSelectedType === 'book_renewal')
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'error') {
                alert(data.message || 'Có lỗi xảy ra khi thực hiện thanh toán!');
                if (confirmBtn) {
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i><span>Tôi Đã Chuyển Khoản Thành Công</span>';
                    if (window.lucide) window.lucide.createIcons();
                }
                return;
            }
            alert(data.message || 'Thanh toán thành công! Dữ liệu đã được cập nhật.');
            closePaymentModal();
            window.location.reload();
        })
        .catch(err => {
            alert('Lỗi kết nối khi gửi yêu cầu thanh toán!');
            if (confirmBtn) {
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i data-lucide="check-circle" class="w-4 h-4"></i><span>Tôi Đã Chuyển Khoản Thành Công</span>';
                if (window.lucide) window.lucide.createIcons();
            }
        });
    }

    // Gán biến toàn cục window để gọi an toàn
    window.handleBookRenewalClick = handleBookRenewalClick;
    window.openBookRenewalPayment = openBookRenewalPayment;
    window.onRenewalBookSelected = onRenewalBookSelected;
    window.openPaymentModal = openPaymentModal;
    window.closePaymentModal = closePaymentModal;
    window.selectPaymentType = selectPaymentType;
    window.handleGeneratePaymentQR = handleGeneratePaymentQR;
    window.confirmPaymentFinished = confirmPaymentFinished;
    window.backToPaymentConfig = backToPaymentConfig;

    function openVietQRModal(amount, desc, ticketId) {
        openPaymentModal('fine', ticketId, amount);
    }
    function closeVietQRModal() {
        closePaymentModal();
    }
    function confirmPaymentSuccess() {
        confirmPaymentFinished();
    }
    function openCardRenewModal() {
        openPaymentModal('card_renewal');
    }
    function closeCardRenewModal() {
        closePaymentModal();
    }
    window.openVietQRModal = openVietQRModal;
    window.closeVietQRModal = closeVietQRModal;
    window.confirmPaymentSuccess = confirmPaymentSuccess;
    window.openCardRenewModal = openCardRenewModal;
    window.closeCardRenewModal = closeCardRenewModal;

    function switchHistoryTab(tab) {
        const btnBorrow = document.getElementById('btn-tab-borrow');
        const btnCard = document.getElementById('btn-tab-card');
        const contentBorrow = document.getElementById('tab-content-borrow');
        const contentCard = document.getElementById('tab-content-card');

        if (!btnBorrow || !btnCard || !contentBorrow || !contentCard) return;

        if (tab === 'borrow') {
            btnBorrow.className = 'px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 bg-white text-indigo-900 shadow-xs';
            btnCard.className = 'px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 text-slate-600 hover:text-slate-900';
            contentBorrow.classList.remove('hidden');
            contentCard.classList.add('hidden');
        } else {
            btnCard.className = 'px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 bg-white text-indigo-900 shadow-xs';
            btnBorrow.className = 'px-4 py-2 text-xs font-bold rounded-xl transition flex items-center gap-2 text-slate-600 hover:text-slate-900';
            contentCard.classList.remove('hidden');
            contentBorrow.classList.add('hidden');
        }

        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function filterBorrowTable() {
        const kw = (document.getElementById('filter-borrow-kw')?.value || '').toLowerCase().trim();
        const status = (document.getElementById('filter-borrow-status')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#table-borrow-history .borrow-row');

        rows.forEach(function(row) {
            const title = row.getAttribute('data-title') || '';
            const isbn = row.getAttribute('data-isbn') || '';
            const code = row.getAttribute('data-code') || '';
            const rowStatus = (row.getAttribute('data-status') || '').toLowerCase();

            const matchKw = !kw || title.includes(kw) || isbn.includes(kw) || code.includes(kw);
            const matchStatus = !status || rowStatus === status;

            row.style.display = (matchKw && matchStatus) ? '' : 'none';
        });
    }

    function resetBorrowFilters() {
        const kwInput = document.getElementById('filter-borrow-kw');
        const statusSelect = document.getElementById('filter-borrow-status');
        if (kwInput) kwInput.value = '';
        if (statusSelect) statusSelect.value = '';
        filterBorrowTable();
    }

    function filterCardTable() {
        const kw = (document.getElementById('filter-card-kw')?.value || '').toLowerCase().trim();
        const rows = document.querySelectorAll('#table-card-history .card-tx-row');

        rows.forEach(function(row) {
            const search = row.getAttribute('data-search') || '';
            row.style.display = (!kw || search.includes(kw)) ? '' : 'none';
        });
    }

    function openBookDetailModal(btn) {
        if (!btn) return;
        const d = btn.dataset;
        const id = d.id || d.bookId || '';
        const title = d.title || d.bookTitle || 'Chi tiết sách';
        const author = d.author || d.bookAuthor || 'Đang cập nhật';
        const isbn = d.isbn || d.bookIsbn || 'Chưa có';
        const category = d.category || d.bookCategory || 'Chung';
        const publisher = d.publisher || d.bookPublisher || 'NXB Đang cập nhật';
        const shelf = d.shelf || d.bookLocation || d.location || 'Kệ A1';
        const desc = d.desc || d.bookDescription || d.description || 'Đầu sách này hiện chưa có nội dung tóm tắt chi tiết.';
        const avail = parseInt(d.avail || d.bookAvailable || d.available || '0');
        const total = parseInt(d.total || d.bookTotal || d.totalCopies || '0');

        const elId = document.getElementById('modal_borrow_book_id');
        if (elId) elId.value = id;

        const elTitle = document.getElementById('modal_detail_title');
        if (elTitle) elTitle.textContent = title;

        const elAuthor = document.getElementById('modal_detail_author');
        if (elAuthor) elAuthor.textContent = author;

        const elIsbn = document.getElementById('modal_detail_isbn');
        if (elIsbn) elIsbn.textContent = isbn;

        const elCat = document.getElementById('modal_detail_category');
        if (elCat) elCat.textContent = category;

        const elPub = document.getElementById('modal_detail_publisher');
        if (elPub) elPub.textContent = publisher;

        const elShelf = document.getElementById('modal_detail_shelf') || document.getElementById('modal_detail_location');
        if (elShelf) elShelf.textContent = shelf;

        const elCopies = document.getElementById('modal_detail_copies');
        if (elCopies) elCopies.textContent = `${avail} / ${total} cuốn`;

        const elDesc = document.getElementById('modal_detail_description');
        if (elDesc) elDesc.textContent = desc;

        const elBadge = document.getElementById('modal_detail_stock_badge');
        if (elBadge) {
            if (avail > 0) {
                elBadge.className = 'px-2 py-0.5 rounded-full font-bold text-[10px] bg-emerald-100 text-emerald-800 border border-emerald-200';
                elBadge.textContent = `Còn sẵn ${avail} / ${total} cuốn`;
            } else {
                elBadge.className = 'px-2 py-0.5 rounded-full font-bold text-[10px] bg-rose-100 text-rose-800 border border-rose-200';
                elBadge.textContent = 'Hết sách trên kệ';
            }
        }

        const borrowBtn = document.getElementById('modal_detail_borrow_btn');
        if (borrowBtn) {
            if (avail > 0) {
                borrowBtn.disabled = false;
                borrowBtn.className = 'px-5 py-2 bg-sky-600 hover:bg-sky-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center justify-center gap-1.5 min-w-[120px] cursor-pointer';
            } else {
                borrowBtn.disabled = true;
                borrowBtn.className = 'px-5 py-2 bg-slate-300 text-slate-500 font-bold text-xs rounded-xl shadow-none cursor-not-allowed flex items-center justify-center gap-1.5 min-w-[120px]';
            }
        }

        openModal('modal-book-detail');
        if (window.lucide && typeof window.lucide.createIcons === 'function') {
            window.lucide.createIcons();
        }
    }

    function openModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
            m.style.display = 'flex';
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('flex');
            m.classList.add('hidden');
            m.style.display = 'none';
        }
    }

    window.openBookDetailModal = openBookDetailModal;
    window.openModal = openModal;
    window.closeModal = closeModal;

    function openRateModal(bookId, bookTitle) {
        document.getElementById('rate-book-id').value = bookId;
        document.getElementById('rate-book-title').textContent = bookTitle;
        const modal = document.getElementById('rate-modal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeRateModal() {
        const modal = document.getElementById('rate-modal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    const initialTagsRaw = document.getElementById('initial_selected_tags_storage')?.value || '';
    let readerSelectedTags = new Set(
        initialTagsRaw ? initialTagsRaw.split(',').map(s => s.trim()).filter(Boolean) : []
    );

    function openModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('hidden');
            m.classList.add('flex');
        }
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        if (m) {
            m.classList.remove('flex');
            m.classList.add('hidden');
        }
    }

    function openReaderTagModal() {
        document.querySelectorAll('#modal-category-tags .tag-checkbox').forEach(cb => {
            cb.checked = readerSelectedTags.has(cb.value);
        });
        updateTagBadgeCount();
        openModal('modal-category-tags');
    }

    function onTagChecked(checkbox) {
        if (checkbox.checked) {
            readerSelectedTags.add(checkbox.value);
        } else {
            readerSelectedTags.delete(checkbox.value);
        }
        updateTagBadgeCount();
    }

    function updateTagBadgeCount() {
        const countBadge = document.getElementById('tags-count-badge');
        if (countBadge) {
            countBadge.textContent = readerSelectedTags.size;
        }
    }

    function clearAllSelectedTags() {
        readerSelectedTags.clear();
        document.querySelectorAll('#modal-category-tags .tag-checkbox').forEach(cb => cb.checked = false);
        updateTagBadgeCount();
    }

    function filterTags(keyword) {
        const term = (keyword || '').toLowerCase().trim();
        document.querySelectorAll('#modal-category-tags .tag-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            if (!term || text.includes(term)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }

    function confirmCategoryTagSelection() {
        const tagsArr = Array.from(readerSelectedTags);
        const tagsInput = document.getElementById('reader_selected_tags_input');
        if (tagsInput) {
            tagsInput.value = tagsArr.join(',');
        }
        closeModal('modal-category-tags');
        document.getElementById('reader-search-form').submit();
    }

    function removeSingleTag(tag) {
        readerSelectedTags.delete(tag);
        const tagsInput = document.getElementById('reader_selected_tags_input');
        if (tagsInput) {
            tagsInput.value = Array.from(readerSelectedTags).join(',');
        }
        document.getElementById('reader-search-form').submit();
    }
</script>

@include('partials.modal-category-tags')
@endsection