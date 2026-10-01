@extends('layouts.app')

@section('title', 'Trung Tâm Quản Trị Hệ Thống')

@section('content')
<div class="space-y-6">
    <!-- Admin Header Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200 shadow-sm">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-800 flex items-center justify-center shadow-inner">
                <i data-lucide="shield-alert" class="w-6 h-6"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-900">Ban Quản Trị Hệ Thống LibraNova</h1>
                <p class="text-xs text-slate-500">Thiết lập chính sách mượn trả, duyệt ngân sách mua sách, kiểm toán tài chính & Kiểm kê kho sách</p>
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.backup.export') }}" class="px-3.5 py-2 bg-slate-900 hover:bg-slate-800 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="download" class="w-4 h-4"></i>
                Sao Lưu Dữ Liệu (JSON)
            </a>
            <button type="button" onclick="openModal('modal-add-staff')" class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                <i data-lucide="user-check" class="w-4 h-4"></i>
                Thêm Nhân Sự
            </button>
        </div>
    </div>

    <!-- Revenue & Financial Breakdown Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="p-5 bg-gradient-to-br from-slate-900 to-indigo-950 text-white rounded-2xl shadow-sm">
            <div class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">Tổng doanh thu phạt & thẻ</div>
            <div class="text-2xl font-bold text-amber-300 mt-1">{{ number_format($totalRevenue) }}đ</div>
            <div class="text-[11px] text-slate-300 mt-0.5">{{ $transactions->count() }} giao dịch ghi nhận</div>
        </div>
        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Thu qua VietQR Napas</div>
            <div class="text-2xl font-bold text-sky-600 mt-1">{{ number_format($vietqrRevenue) }}đ</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Tự động đối soát ngân hàng</div>
        </div>
        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Thu tiền mặt tại quầy</div>
            <div class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($cashRevenue) }}đ</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Thủ thư thu trực tiếp</div>
        </div>
        <div class="p-5 bg-white rounded-2xl border border-slate-200 shadow-sm">
            <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Ví điện tử (MoMo/VNPAY)</div>
            <div class="text-2xl font-bold text-purple-600 mt-1">{{ number_format($walletRevenue) }}đ</div>
            <div class="text-[11px] text-slate-400 mt-0.5">Cổng liên kết số</div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200 shadow-sm space-y-6">
        <div class="flex flex-wrap items-center gap-2 border-b border-slate-100 pb-3">
            <button type="button" onclick="switchAdminTab('tab-rules')" id="tab-btn-rules" class="admin-tab active px-4 py-2 text-xs font-bold rounded-xl bg-amber-100 text-amber-900 transition">
                Cấu Hình Quy Định Thư Viện
            </button>
            <button type="button" onclick="switchAdminTab('tab-proposals')" id="tab-btn-proposals" class="admin-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition">
                Duyệt Đề Xuất Mua Sách ({{ $proposals->where('status', 'pending')->count() }})
            </button>
            <button type="button" onclick="switchAdminTab('tab-staff')" id="tab-btn-staff" class="admin-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition">
                Tài Khoản Nhân Sự ({{ $staffUsers->count() }})
            </button>
            <button type="button" onclick="switchAdminTab('tab-audit')" id="tab-btn-audit" class="admin-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition">
                Nhật Ký Bảo Mật (Audit Log)
            </button>
            <button type="button" onclick="switchAdminTab('tab-inventory')" id="tab-btn-inventory" class="admin-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition flex items-center gap-1.5">
                <i data-lucide="package-search" class="w-3.5 h-3.5 text-sky-600"></i>
                <span>Xem Số Lượng & Kiểm Kê Sách ({{ $books->count() }})</span>
            </button>
        </div>

        <!-- 1. System Rules Form -->
        <div id="tab-rules" class="admin-tab-content">
            <form action="{{ route('admin.rules.update') }}" method="POST" class="space-y-6 max-w-4xl">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="p-5 bg-slate-50/70 border border-slate-200 rounded-2xl space-y-4">
                        <div class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="sliders" class="w-4 h-4 text-sky-600"></i>
                            Chính Sách Mượn - Trả Sách
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Số sách tối đa được mượn cùng lúc</label>
                            <input type="number" name="max_books_per_loan" value="{{ $rules->max_books_per_loan }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Thời hạn mượn tiêu chuẩn (Ngày)</label>
                            <input type="number" name="max_loan_days" value="{{ $rules->max_loan_days }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tiền phạt quá hạn (VNĐ / Ngày)</label>
                            <input type="number" name="fine_per_day" value="{{ $rules->fine_per_day }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Phí gia hạn thẻ thường niên (VNĐ)</label>
                            <input type="number" name="card_renewal_fee" value="{{ $rules->card_renewal_fee }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Số lần gia hạn sách tối đa / phiếu</label>
                            <input type="number" name="max_renewal_times" value="{{ $rules->max_renewal_times }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                    </div>

                    <div class="p-5 bg-slate-50/70 border border-slate-200 rounded-2xl space-y-4">
                        <div class="text-xs font-bold text-slate-800 uppercase tracking-wider flex items-center gap-1.5">
                            <i data-lucide="qr-code" class="w-4 h-4 text-sky-600"></i>
                            Tài Khoản Thụ Hưởng VietQR
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Ngân hàng thụ hưởng</label>
                            <input type="text" name="bank_name" value="{{ $rules->bank_name }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Số tài khoản ngân hàng</label>
                            <input type="text" name="bank_account" value="{{ $rules->bank_account }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1">Tên chủ tài khoản (In hoa)</label>
                            <input type="text" name="account_holder" value="{{ $rules->account_holder }}" class="w-full py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="px-6 py-2.5 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center gap-1.5">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Lưu Cấu Hình Toàn Hệ Thống
                    </button>
                </div>
            </form>
        </div>

        <!-- 2. Purchase Proposals Review -->
        <div id="tab-proposals" class="admin-tab-content hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Tựa sách</th>
                            <th class="p-3">Tác giả</th>
                            <th class="p-3">Lý do đề xuất</th>
                            <th class="p-3">SL</th>
                            <th class="p-3">Trạng thái</th>
                            <th class="p-3 text-right">Hành động</th>
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
                                <td class="p-3 text-right space-x-1">
                                    @if($p->status === 'pending')
                                        <form action="{{ route('admin.proposals.review', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="approved">
                                            <button type="submit" class="px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 text-emerald-700 font-semibold text-[11px] rounded-lg border border-emerald-200">
                                                Duyệt
                                            </button>
                                        </form>
                                        <form action="{{ route('admin.proposals.review', $p->id) }}" method="POST" class="inline">
                                            @csrf
                                            <input type="hidden" name="status" value="rejected">
                                            <button type="submit" class="px-2.5 py-1 bg-red-50 hover:bg-red-100 text-red-700 font-semibold text-[11px] rounded-lg border border-red-200">
                                                Từ chối
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-[11px]">Đã xử lý</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 3. Staff Accounts Management -->
        <div id="tab-staff" class="admin-tab-content hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Mã Thẻ</th>
                            <th class="p-3">Họ và tên</th>
                            <th class="p-3">Email đăng nhập</th>
                            <th class="p-3">Số điện thoại</th>
                            <th class="p-3">Vai trò</th>
                            <th class="p-3">Hạn Thẻ</th>
                            <th class="p-3">Trạng thái</th>
                            <th class="p-3 text-right">Khóa / Mở</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($staffUsers as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 font-mono font-bold text-sky-700">{{ $s->card_number ?? '---' }}</td>
                                <td class="p-3 font-semibold text-slate-900">{{ $s->name }}</td>
                                <td class="p-3 font-mono text-slate-600">{{ $s->email }}</td>
                                <td class="p-3 text-slate-600">{{ $s->phone ?? '---' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $s->role === 'admin' ? 'bg-amber-100 text-amber-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ strtoupper($s->role) }}
                                    </span>
                                </td>
                                <td class="p-3 text-slate-600 font-mono">{{ $s->card_expiry_date ? \Carbon\Carbon::parse($s->card_expiry_date)->format('d/m/Y') : 'Không giới hạn' }}</td>
                                <td class="p-3">
                                    <span class="px-2 py-0.5 rounded font-bold text-[10px] {{ $s->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                        {{ $s->status === 'active' ? 'Hoạt động' : 'Đã khóa' }}
                                    </span>
                                </td>
                                <td class="p-3 text-right">
                                    <form action="{{ route('admin.users.toggle-lock', $s->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-semibold rounded-lg border {{ $s->status === 'active' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200' }}">
                                            {{ $s->status === 'active' ? 'Khóa' : 'Mở' }}
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 4. Security Audit Log -->
        <div id="tab-audit" class="admin-tab-content hidden space-y-4">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold">
                        <tr>
                            <th class="p-3">Thời gian</th>
                            <th class="p-3">Người thao tác</th>
                            <th class="p-3">Hành động</th>
                            <th class="p-3">Chi tiết</th>
                            <th class="p-3">Địa chỉ IP</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($auditLogs as $log)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3 text-slate-500 font-mono text-[11px]">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s d/m/Y') }}</td>
                                <td class="p-3 font-semibold text-slate-900">{{ $log->operator_name }} ({{ $log->operator_role }})</td>
                                <td class="p-3 font-mono font-bold text-sky-700">{{ $log->action }}</td>
                                <td class="p-3 text-slate-600">{{ $log->details }}</td>
                                <td class="p-3 font-mono text-slate-400 text-[11px]">{{ $log->ip_address ?? '127.0.0.1' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 5. KIỂM KÊ SỐ LƯỢNG SÁCH & TÌM THEO THẺ TAG CHO ADMIN -->
        <div id="tab-inventory" class="admin-tab-content hidden space-y-6">
            <!-- Thống kê nhanh kho sách -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200">
                    <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider">Tổng số đầu sách</div>
                    <div class="text-xl font-bold text-slate-900 mt-1">{{ $books->count() }} <span class="text-xs font-normal text-slate-500">đầu sách</span></div>
                </div>
                <div class="p-4 bg-sky-50/60 rounded-2xl border border-sky-100">
                    <div class="text-[11px] font-semibold text-sky-700 uppercase tracking-wider">Tổng bản in lưu kho</div>
                    <div class="text-xl font-bold text-sky-800 mt-1">{{ $books->sum('total_qty') }} <span class="text-xs font-normal text-sky-600">cuốn</span></div>
                </div>
                <div class="p-4 bg-emerald-50/60 rounded-2xl border border-emerald-100">
                    <div class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider">Bản sách sẵn sàng</div>
                    <div class="text-xl font-bold text-emerald-800 mt-1">{{ $books->sum('available_qty') }} <span class="text-xs font-normal text-emerald-600">cuốn</span></div>
                </div>
                <div class="p-4 bg-amber-50/60 rounded-2xl border border-amber-100">
                    <div class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider">Cảnh báo sắp / hết sách</div>
                    <div class="text-xl font-bold text-amber-800 mt-1">{{ $books->where('available_qty', '<=', 2)->count() }} <span class="text-xs font-normal text-amber-600">đầu sách (≤ 2 cuốn)</span></div>
                </div>
            </div>

            <!-- Bộ lọc & Tìm kiếm kiểm kê số lượng sách -->
            <form action="{{ route('admin.index') }}" method="GET" id="admin-book-search-form" class="p-4 bg-slate-50/70 border border-slate-200 rounded-2xl space-y-3">
                <input type="hidden" name="active_tab" value="tab-inventory">
                
                <div class="flex flex-wrap items-center gap-2">
                    <div class="relative flex-1 min-w-[220px]">
                        <i data-lucide="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2"></i>
                        <input type="text" name="book_keyword" value="{{ request('book_keyword') }}" placeholder="Tìm tên sách, tác giả, nhà xuất bản, ISBN, vị trí kệ..." class="w-full pl-9 pr-3 py-2 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20">
                    </div>

                    <!-- Nút tìm tag sách cho Admin -->
                    <div class="relative">
                        <button type="button" onclick="openAdminTagModal()" id="btn-admin-tag-filter" class="flex items-center gap-1.5 py-2 px-3 text-xs border rounded-xl transition {{ request('book_tags') ? 'bg-sky-50 text-sky-800 border-sky-300 font-semibold shadow-xs' : 'bg-white hover:bg-slate-100 text-slate-700 border-slate-200' }}">
                            <i data-lucide="tags" class="w-3.5 h-3.5 text-sky-600"></i>
                            <span id="admin-tag-button-label">
                                @if(request('book_tags'))
                                    Lọc tag ({{ count(array_filter(explode(',', request('book_tags')))) }})
                                @else
                                    Tìm theo tag sách
                                @endif
                            </span>
                        </button>
                        <input type="hidden" name="book_tags" id="admin_selected_tags_input" value="{{ request('book_tags') }}">
                    </div>

                    <select name="stock_status" class="py-2 px-3 text-xs bg-white border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 text-slate-700">
                        <option value="">Tất cả trạng thái tồn</option>
                        <option value="available" {{ request('stock_status') == 'available' ? 'selected' : '' }}>Còn nhiều (> 2 cuốn)</option>
                        <option value="low" {{ request('stock_status') == 'low' ? 'selected' : '' }}>Sắp hết (≤ 2 cuốn)</option>
                        <option value="out" {{ request('stock_status') == 'out' ? 'selected' : '' }}>Đã hết (0 cuốn)</option>
                    </select>

                    <button type="submit" class="py-2 px-4 bg-sky-600 hover:bg-sky-700 text-white font-semibold text-xs rounded-xl shadow-xs transition flex items-center gap-1">
                        <i data-lucide="filter" class="w-3.5 h-3.5"></i>
                        <span>Lọc sách</span>
                    </button>

                    @if(request('book_keyword') || request('book_tags') || request('stock_status'))
                        <a href="{{ route('admin.index', ['active_tab' => 'tab-inventory']) }}" class="py-2 px-3 text-xs text-slate-500 hover:text-rose-600 hover:bg-rose-50 rounded-xl transition">
                            Xóa lọc
                        </a>
                    @endif
                </div>

                @if(request('book_tags'))
                    <div class="flex flex-wrap items-center gap-1.5 pt-1 text-xs">
                        <span class="text-slate-500 font-medium text-[11px]">Đang lọc tag:</span>
                        @foreach(array_filter(array_map('trim', explode(',', request('book_tags')))) as $tag)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-sky-100 text-sky-800 text-[11px] font-semibold rounded-full border border-sky-200">
                                <span>{{ $tag }}</span>
                                <button type="button" onclick="removeAdminTag('{{ $tag }}')" class="hover:text-sky-950 ml-0.5">
                                    <i data-lucide="x" class="w-3 h-3"></i>
                                </button>
                            </span>
                        @endforeach
                    </div>
                @endif
            </form>

            <!-- Table of Inventory Books -->
            <div class="overflow-x-auto bg-white rounded-2xl border border-slate-200">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 uppercase text-[10px] font-bold border-b border-slate-200">
                        <tr>
                            <th class="p-3">Sách & Tác giả</th>
                            <th class="p-3">Thể loại & Thẻ tag</th>
                            <th class="p-3">Nhà xuất bản & Vị trí</th>
                            <th class="p-3 text-center">Tổng số bản</th>
                            <th class="p-3 text-center">Đang mượn</th>
                            <th class="p-3 text-center">Khả dụng</th>
                            <th class="p-3">Tình trạng kho</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($books as $b)
                            @php
                                $total = (int)($b->total_qty ?? $b->total_copies ?? 1);
                                $available = (int)($b->available_qty ?? $b->available_copies ?? 0);
                                $borrowed = max(0, $total - $available);
                                $percent = $total > 0 ? round(($borrowed / $total) * 100) : 0;
                            @endphp
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="p-3">
                                    <div class="font-bold text-slate-900">{{ $b->title }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5">
                                        Tác giả: {{ $b->author }}
                                        @if($b->isbn)
                                            • <span class="font-mono text-[10px] bg-slate-100 px-1 py-0.5 rounded text-slate-600">ISBN: {{ $b->isbn }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="p-3">
                                    <div class="font-semibold text-slate-800">{{ $b->category->name ?? 'Chưa phân loại' }}</div>
                                    @if($b->tags)
                                        <div class="flex flex-wrap gap-1 mt-1 max-w-xs">
                                            @foreach(array_slice(array_filter(array_map('trim', explode(',', $b->tags))), 0, 4) as $tag)
                                                <span class="px-1.5 py-0.5 bg-slate-100 text-slate-700 text-[10px] rounded border border-slate-200">{{ $tag }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="text-slate-700 font-medium">{{ $b->publisher->name ?? 'Thư viện' }}</div>
                                    <div class="text-[11px] text-slate-500 mt-0.5 flex items-center gap-1">
                                        <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                        <span>Kệ: {{ $b->shelf_location }}</span>
                                    </div>
                                </td>
                                <td class="p-3 text-center font-bold text-slate-900 text-sm">{{ $total }}</td>
                                <td class="p-3 text-center font-bold text-amber-700">{{ $borrowed }}</td>
                                <td class="p-3 text-center">
                                    @if($available > 2)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">{{ $available }}</span>
                                    @elseif($available > 0)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-800">{{ $available }}</span>
                                    @else
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800">0 (Hết)</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <div class="w-32">
                                        <div class="flex justify-between text-[10px] text-slate-500 mb-1">
                                            <span>Mượn {{ $percent }}%</span>
                                            <span>Còn {{ $available }}/{{ $total }}</span>
                                        </div>
                                        <div class="w-full bg-slate-100 h-1.5 rounded-full overflow-hidden">
                                            <div class="h-full {{ ($b->available_qty ?? 0) == 0 ? 'bg-red-500' : (($b->available_qty ?? 0) <= 2 ? 'bg-amber-500' : 'bg-sky-500') }}" style="width: <?php echo $percent; ?>%;"></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Thêm Nhân Sự Mới (Đã bổ sung Mã Thẻ, Hạn Thẻ, SĐT) -->
<div id="modal-add-staff" class="fixed inset-0 z-50 bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Cấp Mới Tài Khoản Nhân Sự</h3>
                <p class="text-[11px] text-slate-500">Tạo tài khoản phân quyền cho Thủ thư hoặc Quản trị viên</p>
            </div>
            <button type="button" onclick="closeModal('modal-add-staff')" class="text-slate-400 hover:text-slate-600 cursor-pointer"><i data-lucide="x" class="w-5 h-5"></i></button>
        </div>
        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-3">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Họ và tên <span class="text-red-500">*</span></label>
                <input type="text" name="name" required placeholder="Nguyễn Văn A" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Email đăng nhập <span class="text-red-500">*</span></label>
                    <input type="email" name="email" required placeholder="nhansu@libranova.vn" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Số điện thoại</label>
                    <input type="text" name="phone" placeholder="0901234567" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Mã thẻ nhân sự</label>
                    <input type="text" name="card_number" placeholder="Tự sinh (vd: LIB-STAFF-02)" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:outline-none font-mono">
                    <p class="text-[10px] text-slate-400 mt-0.5">Để trống hệ thống tự cấp mã</p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Hạn thẻ</label>
                    <input type="date" name="card_expiry_date" value="2030-12-31" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:outline-none font-mono">
                    <p class="text-[10px] text-slate-400 mt-0.5">Mặc định 5 năm (2030)</p>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Vai trò</label>
                <select name="role" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500/20 focus:outline-none">
                    <option value="librarian">Thủ thư vận hành</option>
                    <option value="admin">Quản trị viên hệ thống</option>
                </select>
            </div>
            <div class="p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-[11px] text-slate-500">
                Mật khẩu mặc định khởi tạo: <span class="font-mono font-bold text-slate-800">123456</span>
            </div>
            <button type="submit" class="w-full py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-xs rounded-xl shadow transition cursor-pointer flex items-center justify-center gap-2">
                <span>Tạo Tài Khoản Nhân Sự</span>
            </button>
        </form>
    </div>
</div>

<!-- Thẻ lưu trữ giá trị tags ban đầu cho Admin -->
<input type="hidden" id="initial_admin_tags_storage" value="{{ request('book_tags', '') }}">

<script>
    function switchAdminTab(tabId) {
        document.querySelectorAll('.admin-tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.admin-tab').forEach(el => {
            el.className = 'admin-tab px-4 py-2 text-xs font-semibold rounded-xl text-slate-600 hover:bg-slate-100 transition';
        });

        const target = document.getElementById(tabId);
        if (target) target.classList.remove('hidden');

        const btn = document.getElementById('tab-btn-' + tabId.replace('tab-', ''));
        if (btn) {
            btn.className = 'admin-tab active px-4 py-2 text-xs font-bold rounded-xl bg-amber-100 text-amber-900 transition flex items-center gap-1.5';
        }
    }

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

    // Modal Tag Search cho Admin
    const initialAdminTagsRaw = document.getElementById('initial_admin_tags_storage')?.value || '';
    let adminSelectedTags = new Set(
        initialAdminTagsRaw ? initialAdminTagsRaw.split(',').map(s => s.trim()).filter(Boolean) : []
    );

    function openAdminTagModal() {
        document.querySelectorAll('#modal-category-tags .tag-checkbox').forEach(cb => {
            cb.checked = adminSelectedTags.has(cb.value);
        });
        updateAdminTagBadgeCount();
        openModal('modal-category-tags');
    }

    function onTagChecked(checkbox) {
        if (checkbox.checked) adminSelectedTags.add(checkbox.value);
        else adminSelectedTags.delete(checkbox.value);
        updateAdminTagBadgeCount();
    }

    function updateAdminTagBadgeCount() {
        const countBadge = document.getElementById('tags-count-badge');
        if (countBadge) countBadge.textContent = adminSelectedTags.size;
    }

    function clearAllSelectedTags() {
        adminSelectedTags.clear();
        document.querySelectorAll('#modal-category-tags .tag-checkbox').forEach(cb => cb.checked = false);
        updateAdminTagBadgeCount();
    }

    function filterTags(keyword) {
        const term = (keyword || '').toLowerCase().trim();
        document.querySelectorAll('#modal-category-tags .tag-item').forEach(item => {
            const text = item.textContent.toLowerCase();
            item.style.display = (!term || text.includes(term)) ? '' : 'none';
        });
    }

    function confirmCategoryTagSelection() {
        const tagsInput = document.getElementById('admin_selected_tags_input');
        if (tagsInput) tagsInput.value = Array.from(adminSelectedTags).join(',');
        closeModal('modal-category-tags');
        document.getElementById('admin-book-search-form').submit();
    }

    function removeAdminTag(tag) {
        adminSelectedTags.delete(tag);
        const tagsInput = document.getElementById('admin_selected_tags_input');
        if (tagsInput) tagsInput.value = Array.from(adminSelectedTags).join(',');
        document.getElementById('admin-book-search-form').submit();
    }

    // Kiểm tra URL parameters bằng JS thuần túy
    document.addEventListener('DOMContentLoaded', function() {
        const params = new URLSearchParams(window.location.search);
        if (params.get('active_tab') === 'tab-inventory' || params.has('book_keyword') || params.has('book_tags') || params.has('stock_status')) {
            switchAdminTab('tab-inventory');
        }
        if (window.lucide) {
            window.lucide.createIcons();
        }
    });
</script>

@include('partials.modal-category-tags')
@endsection