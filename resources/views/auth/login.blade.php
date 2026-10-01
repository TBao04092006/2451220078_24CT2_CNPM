@extends('layouts.app')

@section('title', 'Đăng Nhập Hệ Thống')

@section('content')
<div class="max-w-md mx-auto my-6 sm:my-8 space-y-4">
    <!-- 1. BỘ CHỌN NHANH VAI TRÒ (ĐỘC GIẢ - THỦ THƯ - ADMIN) -->
    <div class="bg-white/80 backdrop-blur-sm p-1.5 rounded-2xl border border-slate-200/90 shadow-xs flex items-center gap-1">
        <button type="button" onclick="selectRole('reader', 'docgia@gmail.com')" id="role-btn-reader" class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 bg-blue-600 text-white shadow-xs">
            <i data-lucide="book-open" class="w-3.5 h-3.5"></i>
            <span>Độc Giả</span>
        </button>

        <button type="button" onclick="selectRole('librarian', 'thuthu@gmail.com')" id="role-btn-librarian" class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900">
            <i data-lucide="library" class="w-3.5 h-3.5"></i>
            <span>Thủ Thư</span>
        </button>

        <button type="button" onclick="selectRole('admin', 'admin@gmail.com')" id="role-btn-admin" class="flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900">
            <i data-lucide="shield-check" class="w-3.5 h-3.5"></i>
            <span>Quản Trị</span>
        </button>
    </div>

    <!-- 2. KHUNG FORM ĐĂNG NHẬP -->
    <div class="bg-white rounded-3xl p-6 sm:p-8 border border-slate-200 shadow-xl space-y-6">
        <div class="text-center space-y-1.5">
            <div id="role-icon-box" class="w-12 h-12 rounded-2xl bg-sky-50 text-sky-600 flex items-center justify-center mx-auto shadow-xs">
                <i data-lucide="log-in" class="w-6 h-6"></i>
            </div>
            <h1 class="text-xl font-bold text-slate-900" id="login-heading">Đăng Nhập Cổng Độc Giả</h1>
            <p class="text-xs text-slate-500" id="login-subheading">Truy cập mượn sách, tra cứu và gia hạn thẻ thư viện</p>
        </div>

        {{-- Hiển thị thông báo lỗi hoặc thành công nếu có --}}
        @if(session('error'))
            <div class="p-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-xs flex items-center gap-2">
                <i data-lucide="alert-circle" class="w-4 h-4 flex-shrink-0 text-red-500"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if(session('success'))
            <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-xs flex items-center gap-2">
                <i data-lucide="check-circle" class="w-4 h-4 flex-shrink-0 text-emerald-500"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Địa chỉ Email</label>
                <div class="relative">
                    <i data-lucide="mail" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="email" name="email" id="email-input" value="{{ old('email', 'an.nguyen@libranova.vn') }}" required class="w-full pl-10 pr-3.5 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:bg-white transition" placeholder="email@libranova.vn">
                </div>
                @error('email')
                    <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Mật khẩu</label>
                <div class="relative">
                    <i data-lucide="lock" class="w-4 h-4 text-slate-400 absolute left-3.5 top-1/2 -translate-y-1/2"></i>
                    <input type="password" name="password" id="password-input" required value="" class="w-full pl-10 pr-10 py-2.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20 focus:bg-white transition" placeholder="••••••••">
                    <button type="button" onclick="togglePasswordVisibility('password-input', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition p-1">
                        <i data-lucide="eye" class="w-4 h-4 eye-open"></i>
                        <i data-lucide="eye-off" class="w-4 h-4 eye-closed hidden"></i>
                    </button>
                </div>
                @error('password')
                    <p class="mt-1 text-[11px] text-red-500">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex items-center justify-between text-xs pt-1">
                <label class="flex items-center gap-2 cursor-pointer text-slate-600">
                    <input type="checkbox" name="remember" checked class="rounded border-slate-300 text-sky-600 focus:ring-sky-500">
                    <span>Ghi nhớ đăng nhập</span>
                </label>
                <a href="{{ route('password.request') }}" class="text-sky-600 hover:text-sky-700 font-semibold transition">
                    Quên mật khẩu?
                </a>
            </div>

            <button type="submit" id="submit-btn" class="w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center justify-center gap-2">
                <span>Truy cập hệ thống</span>
                <i data-lucide="arrow-right" class="w-4 h-4"></i>
            </button>
        </form>

        <div class="pt-4 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs text-slate-500">
            2024 © <span class="font-semibold text-slate-700">Libranova</span>
            <a href="{{ route('register') }}" class="font-semibold text-sky-600 hover:text-sky-700 flex items-center gap-1">
                <i data-lucide="user-plus" class="w-3.5 h-3.5"></i>
                Đăng ký tài khoản mới
            </a>
        </div>
    </div>

    <!-- 3. NÚT BÁO CÁO ADMIN CHO THỦ THƯ & ĐỘC GIẢ -->
    <div class="text-center pt-1">
        <button type="button" onclick="openReportAdminModal()" class="inline-flex items-center gap-1.5 text-xs text-slate-500 hover:text-rose-600 transition font-medium">
            <i data-lucide="help-circle" class="w-4 h-4 text-slate-400"></i>
            <span>Gặp sự cố đăng nhập?</span>
            <strong class="underline">Báo cáo Admin</strong>
        </button>
    </div>
</div>

<!-- MODAL: BÁO CÁO SỰ CỐ CHO QUẢN TRỊ VIÊN -->
<div id="modal-report-admin" class="fixed inset-0 z-[100] bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl space-y-4 border border-slate-200">
        <div class="flex items-center justify-between border-b border-slate-100 pb-3">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center">
                    <i data-lucide="alert-octagon" class="w-4 h-4"></i>
                </div>
                <h3 class="font-bold text-slate-900 text-sm">Báo Cáo Sự Cố Cho Quản Trị Viên</h3>
            </div>
            <button type="button" onclick="closeModal('modal-report-admin')" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <form action="{{ route('report.admin') }}" method="POST" class="space-y-3.5">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Họ tên / Email / Mã số thẻ</label>
                <input type="text" name="reporter_info" required placeholder="Nhập email hoặc họ tên của bạn..." class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-700 mb-1">Vấn đề gặp phải</label>
                <select name="issue_type" id="report-issue-type" onchange="toggleReportReason(this.value)" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl font-medium">
                    <option value="Tài khoản">Tài khoản (Quên mật khẩu, Bị khóa tài khoản, Hết hạn thẻ)</option>
                    <option value="Lý do khác">Lý do khác (Nhập chi tiết bên dưới)</option>
                </select>
            </div>

            <div id="div-custom-reason" class="hidden">
                <label class="block text-xs font-semibold text-slate-700 mb-1">Lý do khác: Nhập lý do cụ thể</label>
                <textarea name="custom_reason" id="custom-reason-input" rows="3" class="w-full py-2 px-3 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-sky-500/20" placeholder="Mô tả chi tiết sự cố bạn đang gặp phải..."></textarea>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100">
                <button type="button" onclick="closeModal('modal-report-admin')" class="w-1/3 py-2 text-xs bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold rounded-xl transition">
                    Hủy
                </button>
                <button type="submit" class="w-2/3 py-2 text-xs bg-rose-600 hover:bg-rose-700 text-white font-semibold rounded-xl shadow transition flex items-center justify-center gap-1.5">
                    <i data-lucide="send" class="w-3.5 h-3.5"></i>
                    <span>Gửi Báo Cáo</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function selectRole(role, defaultEmail) {
        const btnReader = document.getElementById('role-btn-reader');
        const btnLibrarian = document.getElementById('role-btn-librarian');
        const btnAdmin = document.getElementById('role-btn-admin');
        const emailInput = document.getElementById('email-input');
        const passInput = document.getElementById('password-input');
        const heading = document.getElementById('login-heading');
        const subheading = document.getElementById('login-subheading');
        const submitBtn = document.getElementById('submit-btn');

        // Reset all buttons style
        [btnReader, btnLibrarian, btnAdmin].forEach(btn => {
            btn.className = 'flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 text-slate-600 hover:text-slate-900';
        });

        if (role === 'reader') {
            btnReader.className = 'flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 bg-blue-600 text-white shadow-xs';
            heading.textContent = 'Đăng Nhập Cổng Độc Giả';
            subheading.textContent = 'Truy cập mượn sách, tra cứu và gia hạn thẻ thư viện';
            submitBtn.className = 'w-full py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center justify-center gap-2';
        } else if (role === 'librarian') {
            btnLibrarian.className = 'flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 bg-purple-600 text-white shadow-xs';
            heading.textContent = 'Đăng Nhập Cổng Thủ Thư';
            subheading.textContent = 'Bàn vận hành quản lý kho sách, lập phiếu mượn trả & tạo VietQR';
            submitBtn.className = 'w-full py-3 bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center justify-center gap-2';
        } else if (role === 'admin') {
            btnAdmin.className = 'flex-1 py-2 px-2.5 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 bg-amber-600 text-white shadow-xs';
            heading.textContent = 'Đăng Nhập Ban Quản Trị';
            subheading.textContent = 'Cấu hình quy định hệ thống, duyệt đề xuất & kiểm kê kho';
            submitBtn.className = 'w-full py-3 bg-amber-600 hover:bg-amber-700 text-white font-semibold text-xs rounded-xl shadow transition flex items-center justify-center gap-2';
        }

        if (emailInput) {
            emailInput.value = defaultEmail;
        }
        if (passInput) {
            passInput.value = 'password';
        }

        if (window.lucide) {
            window.lucide.createIcons();
        }
    }

    function togglePasswordVisibility(inputId, btnEl) {
        const input = document.getElementById(inputId);
        if (!input) return;
        const isPassword = input.type === 'password';
        input.type = isPassword ? 'text' : 'password';

        const eyeOpen = btnEl.querySelector('.eye-open');
        const eyeClosed = btnEl.querySelector('.eye-closed');
        if (eyeOpen && eyeClosed) {
            if (isPassword) {
                eyeOpen.classList.add('hidden');
                eyeClosed.classList.remove('hidden');
            } else {
                eyeOpen.classList.remove('hidden');
                eyeClosed.classList.add('hidden');
            }
        }
        if (window.lucide && window.lucide.createIcons) {
            lucide.createIcons();
        }
    }

    function openReportAdminModal() {
        const m = document.getElementById('modal-report-admin');
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

    function toggleReportReason(val) {
        const divReason = document.getElementById('div-custom-reason');
        if (val === 'Lý do khác') {
            divReason.classList.remove('hidden');
        } else {
            divReason.classList.add('hidden');
        }s
    }
</script>
@endsection