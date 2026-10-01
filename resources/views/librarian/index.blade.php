@extends('layouts.app')

@section('title', 'Bàn Vận Hành Thủ Thư')

@section('content')
<div class="space-y-6">
    <!-- Header Summary Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-700 flex items-center justify-center shadow-inner">
                <i data-lucide="library" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Nghiệp Vụ Thủ Thư & Quản Trị Kho</h1>
                <p class="text-xs text-slate-500">Quản lý kho sách, lập phiếu mượn/trả, đối soát cấp thẻ độc giả & nộp phạt</p>
            </div>
        </div>

        <!-- Quick Operational Buttons -->
        <div class="flex flex-wrap items-center gap-2">
            <button type="button" onclick="openModal('modal-issue-borrow')" class="px-3.5 py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="plus-circle" class="w-4 h-4"></i>
                Lập Phiếu Mượn
            </button>
            <button type="button" onclick="openModal('modal-add-book')" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="book-plus" class="w-4 h-4"></i>
                Thêm Đầu Sách
            </button>
            <button type="button" onclick="openModal('modal-add-reader')" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="user-plus" class="w-4 h-4"></i>
                Cấp Thẻ Độc Giả
            </button>
            <button type="button" onclick="openModal('modal-proposal')" class="px-3.5 py-2 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="file-text" class="w-4 h-4"></i>
                Đề Xuất Mua
            </button>
        </div>
    </div>

    <!-- 4 Key Operational Metric Tiles -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng đầu sách</div>
            <div class="text-2xl font-bold text-slate-900 mt-1">{{ $books->count() }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Tồn khả dụng: {{ $books->sum('available_qty') }} cuốn</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Phiếu đang mượn</div>
            <div class="text-2xl font-bold text-blue-600 mt-1">{{ $tickets->where('status', 'borrowing')->count() }}</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Hạn mượn tối đa: {{ $rules->max_loan_days ?? 14 }} ngày</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Phiếu quá hạn</div>
            <div class="text-2xl font-bold text-red-600 mt-1">{{ $tickets->where('status', 'overdue')->count() }}</div>
            <div class="text-[11px] text-red-500 font-semibold mt-0.5">Phạt: 5.000đ / ngày trễ</div>
        </div>
        <div class="p-4 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tài khoản khách hàng</div>
            <div class="text-2xl font-bold text-purple-600 mt-1">{{ $readers->count() }}</div>
            <div class="text-[11px] text-slate-500 mt-0.5 font-medium flex items-center gap-1.5">
                <span class="text-emerald-600 font-bold">✓ {{ $readers->whereNotNull('card_number')->count() }} đã cấp</span>
                <span>•</span>
                <span class="text-amber-600 font-bold">⌛ {{ $readers->whereNull('card_number')->count() }} chưa cấp</span>
            </div>
        </div>
    </div>

    <!-- Tabbed Operations Container -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-4">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
            <button type="button" onclick="switchLibTab('tab-tickets')" id="tab-btn-tickets" class="lib-tab active px-4 py-2 text-xs font-bold rounded-xl bg-purple-100 text-purple-800 transition">
                Quầy Mượn - Trả & Nộp Phạt ({{ $tickets->where('status', '!=', 'returned')->count() }})
            </button>
            <button type="button" onclick="switchLibTab('tab-books')" id="tab-btn-books" class="lib-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition">
                Kho Sách & Vị Trí Kệ ({{ $books->count() }})
            </button>
            <button type="button" onclick="switchLibTab('tab-readers')" id="tab-btn-readers" class="lib-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5">
                <span>Quản Lý Tài Khoản Khách Hàng & Thẻ</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-purple-100 text-purple-700">{{ $readers->count() }}</span>
                @if($readers->whereNull('card_number')->count() > 0)
                    <span class="px-1.5 py-0.5 rounded-full text-[9px] font-bold bg-amber-500 text-white animate-pulse">
                        {{ $readers->whereNull('card_number')->count() }} chưa cấp
                    </span>
                @endif
            </button>
            <button type="button" onclick="switchLibTab('tab-proposals')" id="tab-btn-proposals" class="lib-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition">
                Đề Xuất Mua Bổ Sung ({{ $proposals->count() }})
            </button>
        </div>

        <!-- 1. Borrow & Return Tickets View -->
        <div id="tab-tickets" class="lib-tab-content space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Mã phiếu</th>
                            <th class="p-3">Độc giả</th>
                            <th class="p-3">Tựa sách</th>
                            <th class="p-3">Ngày mượn</th>
                            <th class="p-3">Hạn trả</th>
                            <th class="p-3">Trạng thái</th>
                            <th class="p-3">Phí phạt</th>
                            <th class="p-3 text-right">Xử lý</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($tickets as $t)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-mono font-bold text-slate-700">#{{ $t->ticket_code }}</td>
                                <td class="p-3 font-medium text-slate-900">{{ $t->reader->name ?? 'N/A' }}</td>
                                <td class="p-3 font-semibold text-slate-800 line-clamp-1">{{ $t->book->title ?? 'N/A' }}</td>
                                <td class="p-3 text-slate-500">{{ \Carbon\Carbon::parse($t->borrow_date)->format('d/m/Y') }}</td>
                                <td class="p-3 text-slate-600 font-semibold">{{ \Carbon\Carbon::parse($t->due_date)->format('d/m/Y') }}</td>
                                <td class="p-3">
                                    @if($t->status === 'overdue')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-red-100 text-red-700">Trễ {{ $t->overdue_days }} ngày</span>
                                    @elseif($t->status === 'returned')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-slate-100 text-slate-600">Đã trả</span>
                                    @elseif($t->status === 'pending')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-800">Chờ duyệt</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-blue-100 text-blue-700">Đang mượn</span>
                                    @endif
                                </td>
                                <td class="p-3 font-bold {{ $t->fine_amount > 0 ? 'text-red-600' : 'text-slate-400' }}">
                                    {{ number_format($t->fine_amount) }}đ
                                </td>
                                <td class="p-3 text-right space-x-1">
                                    @if($t->status === 'pending')
                                        <form action="{{ route('librarian.tickets.approve', $t->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">
                                                Duyệt & Giao
                                            </button>
                                        </form>
                                        <form action="{{ route('librarian.tickets.reject', $t->id) }}" method="POST" class="inline" onsubmit="return confirm('Từ chối phiếu mượn này?')">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold bg-red-50 text-red-700 hover:bg-red-100 rounded-lg transition">
                                                Từ chối
                                            </button>
                                        </form>
                                    @elseif($t->status !== 'returned')
                                        @if($t->fine_amount > 0)
                                            <button type="button" onclick="openCounterQR('{{ $t->fine_amount }}', 'NOP PHAT PHIEU {{ $t->ticket_code }}')" class="px-2.5 py-1 text-[11px] font-semibold bg-red-50 text-red-700 hover:bg-red-100 rounded-lg border border-red-200 transition">
                                                Tạo VietQR
                                            </button>
                                        @endif
                                        <form action="{{ route('librarian.tickets.return', $t->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold bg-emerald-50 text-emerald-700 hover:bg-emerald-100 rounded-lg border border-emerald-200 transition">
                                                Nhận Sách Trả
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Hoàn tất</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 2. Book Inventory View -->
        <div id="tab-books" class="lib-tab-content hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Tựa sách</th>
                            <th class="p-3">Tác giả</th>
                            <th class="p-3">Thể loại</th>
                            <th class="p-3">Vị trí kệ</th>
                            <th class="p-3">Tồn kho / Tổng</th>
                            <th class="p-3 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($books as $b)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-bold text-slate-900">{{ $b->title }}</td>
                                <td class="p-3 text-slate-600">{{ $b->author }}</td>
                                <td class="p-3 text-slate-500">{{ $b->category->name ?? 'N/A' }}</td>
                                <td class="p-3 font-semibold text-amber-800 bg-amber-50/50 rounded-lg">{{ $b->shelf_location }}</td>
                                <td class="p-3 font-mono font-bold">{{ $b->available_qty }} / {{ $b->total_qty }}</td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('librarian.books.destroy', $b->id) }}" method="POST" class="inline" onsubmit="return confirm('Bạn có chắc chắn muốn xóa đầu sách này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition" title="Xóa sách">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Readers Management View -->
        <div id="tab-readers" class="lib-tab-content hidden space-y-4">
            <!-- Reader Management Toolbar & Filters -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50 p-3.5 rounded-2xl border border-slate-200">
                <div class="relative flex-1 max-w-md">
                    <i data-lucide="search" class="w-4 h-4 absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    <input type="text" id="reader-search-input" oninput="filterReaderTable()" placeholder="Tìm kiếm theo Tên, Email, SĐT hoặc Mã thẻ..." class="w-full pl-9 pr-3 py-2 bg-white border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-purple-500/20 focus:border-purple-500 transition">
                </div>

                <div class="flex items-center gap-1.5 overflow-x-auto pb-1 sm:pb-0">
                    <button type="button" onclick="setReaderFilter('all')" id="rf-btn-all" class="rf-btn px-3 py-1.5 text-xs font-bold rounded-xl bg-purple-600 text-white shadow-xs transition whitespace-nowrap">
                        Tất cả ({{ $readers->count() }})
                    </button>
                    <button type="button" onclick="setReaderFilter('no-card')" id="rf-btn-no-card" class="rf-btn px-3 py-1.5 text-xs font-semibold rounded-xl bg-white hover:bg-slate-100 text-amber-700 border border-amber-200 transition whitespace-nowrap flex items-center gap-1">
                        <span>Chưa cấp thẻ</span>
                        <span class="px-1.5 py-0.2 bg-amber-100 text-amber-800 rounded-full font-bold text-[10px]">{{ $readers->whereNull('card_number')->count() }}</span>
                    </button>
                    <button type="button" onclick="setReaderFilter('has-card')" id="rf-btn-has-card" class="rf-btn px-3 py-1.5 text-xs font-semibold rounded-xl bg-white hover:bg-slate-100 text-emerald-700 border border-emerald-200 transition whitespace-nowrap flex items-center gap-1">
                        <span>Đã có thẻ</span>
                        <span class="px-1.5 py-0.2 bg-emerald-100 text-emerald-800 rounded-full font-bold text-[10px]">{{ $readers->whereNotNull('card_number')->count() }}</span>
                    </button>
                    <button type="button" onclick="setReaderFilter('locked')" id="rf-btn-locked" class="rf-btn px-3 py-1.5 text-xs font-semibold rounded-xl bg-white hover:bg-slate-100 text-red-700 border border-red-200 transition whitespace-nowrap">
                        Đã khóa ({{ $readers->where('status', 'locked')->count() }})
                    </button>
                </div>
            </div>

            <!-- Table of Readers -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs" id="table-readers-list">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Mã thẻ thư viện</th>
                            <th class="p-3">Tài khoản khách hàng</th>
                            <th class="p-3">Email & SĐT đối soát</th>
                            <th class="p-3">Tình trạng thẻ & Hạn</th>
                            <th class="p-3">Trạng thái</th>
                            <th class="p-3 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($readers as $r)
                            @php
                                $rHasCard = !empty($r->card_number);
                                $rowFilter = $r->status === 'locked' ? 'locked' : ($rHasCard ? 'has-card' : 'no-card');
                            @endphp
                            <tr class="reader-row hover:bg-slate-50/80 transition" data-status="{{ $rowFilter }}" data-search="{{ strtolower($r->name . ' ' . $r->email . ' ' . $r->phone . ' ' . ($r->card_number ?? '')) }}">
                                <td class="p-3">
                                    @if($rHasCard)
                                        <span class="font-mono font-bold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-200 text-xs inline-block">
                                            {{ $r->card_number }}
                                        </span>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-900 border border-amber-200 text-xs font-mono">
                                            <span class="font-bold tracking-wider text-amber-700">...-....-....</span>
                                            <span class="px-1.5 py-0.5 rounded bg-amber-200 text-amber-900 font-sans text-[10px] font-bold">Chưa cấp thẻ</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">{{ $r->name }}</div>
                                    <div class="text-[11px] text-slate-400">Đăng ký: {{ $r->created_at ? \Carbon\Carbon::parse($r->created_at)->format('d/m/Y H:i') : 'Đang cập nhật' }}</div>
                                </td>
                                <td class="p-3">
                                    <div class="text-slate-700 font-medium font-mono text-[11px]">{{ $r->email }}</div>
                                    <div class="text-[11px] font-mono text-slate-500 flex items-center gap-1 mt-0.5">
                                        <i data-lucide="phone" class="w-3 h-3 text-sky-600"></i>
                                        <span>{{ $r->phone ?: 'Chưa cập nhật SĐT' }}</span>
                                    </div>
                                </td>
                                <td class="p-3">
                                    @if($rHasCard)
                                        <div class="text-emerald-700 font-semibold text-xs flex items-center gap-1">
                                            <i data-lucide="check-circle" class="w-3.5 h-3.5 text-emerald-600"></i>
                                            <span>Đã cấp thẻ</span>
                                        </div>
                                        <div class="text-[11px] text-slate-500 mt-0.5">
                                            Hạn dùng: {{ $r->card_expiry_date ? \Carbon\Carbon::parse($r->card_expiry_date)->format('d/m/Y') : 'Vĩnh viễn' }}
                                        </div>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full font-bold text-[10px] bg-amber-100 text-amber-800 border border-amber-200">
                                            <i data-lucide="alert-circle" class="w-3 h-3 text-amber-600"></i>
                                            Chờ cấp thẻ
                                        </span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($r->status === 'active')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-700">Hoạt động</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-red-100 text-red-700">Đã khóa</span>
                                    @endif
                                </td>
                                <td class="p-3 text-right space-x-1 whitespace-nowrap">
                                    @if(!$rHasCard)
                                        <button type="button" onclick="quickIssueCard('{{ addslashes($r->name) }}', '{{ $r->email }}', '{{ $r->phone }}')" class="px-2.5 py-1 text-[11px] font-bold bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg shadow-xs transition inline-flex items-center gap-1">
                                            <i data-lucide="credit-card" class="w-3 h-3"></i>
                                            <span>Cấp thẻ ngay</span>
                                        </button>
                                    @else
                                        <form action="{{ route('librarian.readers.renew', $r->id) }}" method="POST" class="inline" onsubmit="return confirm('Xác nhận gia hạn thẻ độc giả [{{ $r->name }}] thêm 1 năm?')">
                                            @csrf
                                            <button type="submit" class="px-2 py-1 text-[11px] font-semibold bg-sky-50 hover:bg-sky-100 text-sky-700 border border-sky-200 rounded-lg transition" title="Gia hạn thêm 1 năm">
                                                +1 Năm
                                            </button>
                                        </form>
                                    @endif

                                    <form action="{{ route('librarian.readers.toggle-lock', $r->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-[11px] font-semibold bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg transition">
                                            {{ $r->status === 'active' ? 'Khóa' : 'Mở' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Purchase Proposals View -->
        <div id="tab-proposals" class="lib-tab-content hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Tựa sách đề xuất</th>
                            <th class="p-3">Tác giả</th>
                            <th class="p-3">Lý do</th>
                            <th class="p-3">SL đề xuất</th>
                            <th class="p-3">Trạng thái duyệt</th>
                            <th class="p-3">Ghi chú Admin</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($proposals as $p)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-bold text-slate-900">{{ $p->title }}</td>
                                <td class="p-3 text-slate-600">{{ $p->author }}</td>
                                <td class="p-3 text-slate-500">{{ $p->reason }}</td>
                                <td class="p-3 font-bold">{{ $p->suggested_qty }}</td>
                                <td class="p-3">
                                    @if($p->status === 'approved')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-emerald-100 text-emerald-700">Đã duyệt</span>
                                    @elseif($p->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-red-100 text-red-700">Từ chối</span>
                                    @else
                                        <span class="px-2 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-700">Chờ duyệt</span>
                                    @endif
                                </td>
                                <td class="p-3 text-slate-400 italic">{{ $p->admin_note ?? 'Chưa có ghi chú' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Lập Phiếu Mượn -->
<div id="modal-issue-borrow" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-sm">Lập Phiếu Mượn Sách Mới</h3>
            <button type="button" onclick="closeModal('modal-issue-borrow')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('librarian.tickets.borrow') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Chọn Độc Giả (Chỉ tài khoản đã có thẻ)</label>
                <select name="reader_id" required class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($readers->whereNotNull('card_number') as $r)
                        <option value="{{ $r->id }}">{{ $r->name }} ({{ $r->card_number }}) - SĐT: {{ $r->phone }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Chọn Sách Mượn</label>
                <select name="book_id" required class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                    @foreach($books->where('available_qty', '>', 0) as $b)
                        <option value="{{ $b->id }}">{{ $b->title }} - Còn {{ $b->available_qty }} cuốn ({{ $b->shelf_location }})</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow transition">
                Tạo Phiếu Mượn & Xuất Kho
            </button>
        </form>
    </div>
</div>

<!-- Modal: Thêm Đầu Sách Mới (Đầy đủ ảnh bìa & mô tả) -->
<div id="modal-add-book" class="fixed inset-0 z-[100] bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4" style="z-index: 1000 !important;">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl space-y-4 border border-slate-200 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3 sticky top-0 bg-white z-10">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg bg-sky-50 text-sky-600 flex items-center justify-center">
                    <i data-lucide="book-plus" class="w-4 h-4"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Thêm Đầu Sách & Xếp Vị Trí Kệ</h3>
            </div>
            <button type="button" onclick="closeModal('modal-add-book')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('librarian.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tựa đề sách</label>
                <input type="text" name="title" required placeholder="Ví dụ: Hoàng Lê Nhất Thống Chí" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:outline-none">
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tác giả</label>
                    <input type="text" name="author" required placeholder="Ngô Gia Văn Phái" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mã ISBN (Tùy chọn)</label>
                    <input type="text" name="isbn" placeholder="978-604-..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Thể loại / Nhãn tag</label>
                    <div class="relative">
                        <input type="text" id="selected_category_display" name="category_name" required readonly
                            onclick="openCategoryTagModal()"
                            placeholder="Nhấn để chọn thể loại & tag..."
                            class="w-full py-2 pl-3 pr-8 text-xs bg-slate-50 hover:bg-sky-50/50 border border-slate-200 rounded-xl cursor-pointer">
                        <input type="hidden" name="category_id" id="selected_category_id" value="">
                        <input type="hidden" name="selected_tags" id="selected_tags_input" value="">
                        <button type="button" onclick="openCategoryTagModal()" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400">
                            <i data-lucide="tags" class="w-3.5 h-3.5"></i>
                        </button>
                    </div>
                    <div id="selected_tags_preview" class="mt-1 flex flex-wrap gap-1"></div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Nhà xuất bản</label>
                    <input type="text" name="publisher_name" id="publisher_name_input" required
                        placeholder="Nhập tên nhà xuất bản..."
                        class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Số lượng nhập kho</label>
                    <input type="number" name="total_qty" value="5" min="1" required class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Vị trí kệ chính xác</label>
                    <input type="text" name="shelf_location" required placeholder="Kệ A2 - Tầng 1 - Ngăn 03" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                </div>
            </div>

            <!-- CHỨC NĂNG 1: Chọn ảnh bìa sách từ máy tính -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Ảnh bìa sách (Chọn file ảnh từ máy tính)
                </label>
                <input type="file" name="cover_image" id="add_book_cover_file" accept="image/*" onchange="previewAddBookCover(this)"
                    class="block w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-sky-50 file:text-sky-700 hover:file:bg-sky-100 cursor-pointer bg-slate-50 border border-slate-200 rounded-xl">
                <input type="hidden" name="cover_base64" id="add_book_cover_base64" value="">
                
                <div id="add_book_cover_preview_container" class="hidden items-center gap-3 p-2 mt-2 bg-slate-50 border border-slate-200 rounded-xl">
                    <img id="add_book_cover_preview" src="" alt="Xem trước ảnh bìa" class="w-12 h-16 object-cover rounded-lg shadow-xs border border-slate-200">
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-semibold text-slate-800 truncate" id="add_book_cover_name"></div>
                        <div class="text-[10px] text-emerald-600 font-medium">✓ Đã nạp file ảnh thành công</div>
                    </div>
                    <button type="button" onclick="clearAddBookCover()" class="p-1 text-rose-500 hover:bg-rose-50 rounded-lg" title="Hủy chọn ảnh">
                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                    </button>
                </div>
            </div>

            <!-- CHỨC NĂNG 2: Ghi giới thiệu tóm tắt nội dung sách -->
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Mô tả / Giới thiệu nội dung sách</label>
                <textarea name="description" rows="3" placeholder="Nhập tóm tắt nội dung cuốn sách, ý nghĩa tác phẩm, thông điệp cốt lõi..."
                    class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/20 focus:outline-none resize-none"></textarea>
            </div>

            <button type="submit" class="w-full py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow transition">
                Lưu Vào Kho & Cập Nhật Kệ
            </button>
        </form>
    </div>
</div>

<!-- Modal: Cấp Thẻ Độc Giả (Đối Soát Email & SĐT) -->
<div id="modal-add-reader" class="fixed inset-0 z-[100] bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center">
                    <i data-lucide="credit-card" class="w-4 h-4"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-900 text-sm">Cấp Thẻ Độc Giả Thư Viện</h3>
                    <p class="text-[11px] text-slate-400">Đối soát chính xác Email & Số điện thoại đã đăng ký</p>
                </div>
            </div>
            <button type="button" onclick="closeModal('modal-add-reader')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>

        <div class="p-3 bg-amber-50/90 border border-amber-200/80 rounded-2xl text-[11px] text-amber-900 space-y-1">
            <div class="font-bold flex items-center gap-1.5 text-amber-800">
                <i data-lucide="shield-alert" class="w-3.5 h-3.5"></i>
                <span>Quy tắc đối soát bảo mật:</span>
            </div>
            <p class="text-amber-800/90 leading-relaxed">
                Để cấp thẻ, thông tin <strong>Địa chỉ Email</strong> và <strong>Số điện thoại</strong> phải trùng khớp với tài khoản độc giả đã đăng ký trên hệ thống. Nếu thông tin không khớp hoặc chưa có tài khoản, hệ thống sẽ từ chối cấp thẻ.
            </p>
        </div>

        <form action="{{ route('librarian.readers.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Địa chỉ Email tài khoản <span class="text-red-500">*</span>
                </label>
                <input type="email" name="email" id="issue_card_email" required placeholder="vidu.docgia@gmail.com" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Số điện thoại tài khoản <span class="text-red-500">*</span>
                </label>
                <input type="text" name="phone" id="issue_card_phone" required placeholder="0912345678" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Họ và tên khách hàng (Tùy chọn)
                </label>
                <input type="text" name="name" id="issue_card_name" placeholder="Nguyễn Văn A" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/20 focus:border-emerald-500 transition">
            </div>

            <div class="pt-2 flex items-center justify-end gap-2">
                <button type="button" onclick="closeModal('modal-add-reader')" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                    Hủy bỏ
                </button>
                <button type="submit" class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Xác Thực & Cấp Thẻ</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Tạo Đề Xuất Mua Bổ Sung -->
<div id="modal-proposal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-900 text-sm">Lập Phiếu Đề Xuất Mua Sách</h3>
            <button type="button" onclick="closeModal('modal-proposal')" class="text-slate-400 hover:text-slate-600"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('librarian.proposals.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tựa đề sách đề xuất</label>
                <input type="text" name="title" required placeholder="Kinh Tế Lượng Hiện Đại" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Tác giả</label>
                <input type="text" name="author" required placeholder="NXB Kinh Tế" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lý do mua</label>
                <select name="reason" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
                    <option value="Sách đã hết tồn kho, nhu cầu mượn cao">Sách hết tồn kho / hot</option>
                    <option value="Độc giả yêu cầu bổ sung">Độc giả đề xuất</option>
                    <option value="Sách cũ bị hỏng / hao mòn cần thay thế">Sách hỏng cần thay mới</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Số lượng cần mua</label>
                <input type="number" name="suggested_qty" value="10" min="1" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl">
            </div>
            <button type="submit" class="w-full py-2.5 bg-amber-500 hover:bg-amber-600 text-slate-950 font-bold text-xs rounded-xl shadow transition">
                Gửi Lên Quản Trị Viên
            </button>
        </form>
    </div>
</div>

<!-- Modal: Tạo QR Thu Phạt Tại Quầy -->
<div id="counter-qr-modal" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl space-y-4 border border-slate-200 text-center">
        <h3 class="font-bold text-slate-900 text-sm">Mã VietQR Thu Phạt Tại Quầy</h3>
        <div class="w-56 h-56 mx-auto bg-slate-100 rounded-2xl border border-slate-200 p-2">
            <img id="counter-qr-img" src="" alt="Mã VietQR" class="w-full h-full object-contain rounded-xl">
        </div>
        <div class="text-xs text-slate-600 font-medium">
            Số tiền: <span id="counter-qr-val" class="font-bold text-red-600 text-sm">0đ</span>
        </div>
        <p class="text-[11px] text-slate-400">Độc giả dùng App Ngân hàng quét trực tiếp tại màn hình</p>
        <button type="button" onclick="closeModal('counter-qr-modal')" class="w-full py-2 bg-slate-200 hover:bg-slate-300 font-semibold text-xs rounded-xl transition">
            Đóng
        </button>
    </div>
</div>

<!-- Modal: Chọn thể loại & Tags -->
@include('partials.modal-category-tags')

<script>
    function switchLibTab(tabId) {
        document.querySelectorAll('.lib-tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.lib-tab').forEach(el => {
            el.className = 'lib-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition';
        });

        document.getElementById(tabId).classList.remove('hidden');
        document.getElementById(`tab-btn-${tabId.replace('tab-', '')}`).className = 'lib-tab active px-4 py-2 text-xs font-bold rounded-xl bg-purple-100 text-purple-800 transition';
    }

    function openModal(id) {
        const m = document.getElementById(id);
        m.classList.remove('hidden');
        m.classList.add('flex');
    }

    function closeModal(id) {
        const m = document.getElementById(id);
        m.classList.remove('flex');
        m.classList.add('hidden');
    }

    function openCounterQR(amount, desc) {
        const qrUrl = `https://img.vietqr.io/image/MB-0987654321-compact2.png?amount=${amount}&addInfo=${encodeURIComponent(desc)}&accountName=${encodeURIComponent('THU VIEN LIBRANOVA QUOC GIA')}`;
        document.getElementById('counter-qr-img').src = qrUrl;
        document.getElementById('counter-qr-val').textContent = Number(amount).toLocaleString('vi-VN') + 'đ';
        openModal('counter-qr-modal');
    }

    let selectedTags = new Set();

    function openCategoryTagModal() {
        // Đồng bộ lại checkbox đã chọn trong giao diện modal tag
        document.querySelectorAll('#modal-category-tags .tag-checkbox').forEach(cb => {
            cb.checked = selectedTags.has(cb.value);
        });
        updateTagBadgeCount();
        openModal('modal-category-tags');
    }

    function onTagChecked(checkbox) {
        if (checkbox.checked) {
            selectedTags.add(checkbox.value);
        } else {
            selectedTags.delete(checkbox.value);
        }
        updateTagBadgeCount();
    }

    function updateTagBadgeCount() {
        const countBadge = document.getElementById('tags-count-badge');
        if (countBadge) countBadge.textContent = selectedTags.size;
    }

    function clearAllSelectedTags() {
        selectedTags.clear();
        document.querySelectorAll('#modal-category-tags .tag-checkbox').forEach(cb => cb.checked = false);
        updateTagBadgeCount();
    }

    function filterTags(keyword) {
        const term = (keyword || '').toLowerCase().trim();
        document.querySelectorAll('#modal-category-tags .tag-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = (!term || text.includes(term)) ? '' : 'none';
        });
    }

    function confirmCategoryTagSelection() {
        const arr = Array.from(selectedTags);
        const displayInput = document.getElementById('selected_category_display');
        const hiddenTagsInput = document.getElementById('selected_tags_input');
        const previewContainer = document.getElementById('selected_tags_preview');

        if (arr.length === 0) {
            alert('Vui lòng tích chọn ít nhất 1 thẻ tag thể loại!');
            return;
        }

        displayInput.value = arr[0] + (arr.length > 1 ? ` (+${arr.length - 1} nhãn)` : '');
        hiddenTagsInput.value = arr.join(', ');

        if (previewContainer) {
            previewContainer.innerHTML = '';
            arr.forEach((tag, idx) => {
                const chip = document.createElement('span');
                chip.className = 'inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-semibold ' + 
                    (idx === 0 ? 'bg-sky-100 text-sky-800 border border-sky-200' : 'bg-slate-100 text-slate-700 border border-slate-200');
                chip.textContent = (idx === 0 ? '★ ' : '') + tag;
                previewContainer.appendChild(chip);
            });
        }
        closeModal('modal-category-tags');
    }

    function previewAddBookCover(input) {
        const file = input.files && input.files[0];
        const previewContainer = document.getElementById('add_book_cover_preview_container');
        const previewImg = document.getElementById('add_book_cover_preview');
        const previewName = document.getElementById('add_book_cover_name');
        const base64Input = document.getElementById('add_book_cover_base64');

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                previewImg.src = e.target.result;
                base64Input.value = e.target.result;
                previewName.textContent = file.name;
                previewContainer.classList.remove('hidden');
                previewContainer.classList.add('flex');
            };
            reader.readAsDataURL(file);
        }
    }

    function clearAddBookCover() {
        const fileInput = document.getElementById('add_book_cover_file');
        const previewContainer = document.getElementById('add_book_cover_preview_container');
        const base64Input = document.getElementById('add_book_cover_base64');
        if (fileInput) fileInput.value = '';
        if (base64Input) base64Input.value = '';
        if (previewContainer) {
            previewContainer.classList.remove('flex');
            previewContainer.classList.add('hidden');
        }
    }

    // Quản lý & Lọc danh sách Độc giả / Khách hàng
    let currentReaderFilter = 'all';

    function setReaderFilter(filterType) {
        currentReaderFilter = filterType;
        document.querySelectorAll('.rf-btn').forEach(btn => {
            btn.className = 'rf-btn px-3 py-1.5 text-xs font-semibold rounded-xl bg-white hover:bg-slate-100 text-slate-700 border border-slate-200 transition whitespace-nowrap';
        });

        const activeBtn = document.getElementById(`rf-btn-${filterType}`);
        if (activeBtn) {
            if (filterType === 'all') activeBtn.className = 'rf-btn px-3 py-1.5 text-xs font-bold rounded-xl bg-purple-600 text-white shadow-xs transition whitespace-nowrap';
            else if (filterType === 'no-card') activeBtn.className = 'rf-btn px-3 py-1.5 text-xs font-bold rounded-xl bg-amber-500 text-white shadow-xs transition whitespace-nowrap flex items-center gap-1';
            else if (filterType === 'has-card') activeBtn.className = 'rf-btn px-3 py-1.5 text-xs font-bold rounded-xl bg-emerald-600 text-white shadow-xs transition whitespace-nowrap flex items-center gap-1';
            else if (filterType === 'locked') activeBtn.className = 'rf-btn px-3 py-1.5 text-xs font-bold rounded-xl bg-red-600 text-white shadow-xs transition whitespace-nowrap';
        }

        filterReaderTable();
    }

    function filterReaderTable() {
        const term = (document.getElementById('reader-search-input')?.value || '').toLowerCase().trim();
        document.querySelectorAll('.reader-row').forEach(row => {
            const rowStatus = row.getAttribute('data-status');
            const rowSearch = row.getAttribute('data-search') || '';

            const matchFilter = (currentReaderFilter === 'all') || (currentReaderFilter === rowStatus);
            const matchSearch = !term || rowSearch.includes(term);

            if (matchFilter && matchSearch) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    function quickIssueCard(name, email, phone) {
        const emailInput = document.getElementById('issue_card_email');
        const phoneInput = document.getElementById('issue_card_phone');
        const nameInput = document.getElementById('issue_card_name');

        if (emailInput) emailInput.value = email || '';
        if (phoneInput) phoneInput.value = phone || '';
        if (nameInput) nameInput.value = name || '';

        openModal('modal-add-reader');
    }
</script>
@endsection