<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Hệ Thống Quản Lý Thư Viện') - LibraNova</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Google Fonts: Be Vietnam Pro & Playfair Display -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@300;400;500;600;700&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet">
    
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- Tailwind CSS CDN for high-fidelity styling -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Be Vietnam Pro"', 'sans-serif'],
                        serif: ['"Playfair Display"', 'serif'],
                    },
                    colors: {
                        brand: {
                            blue: '#0284c7',
                            darkBlue: '#0f172a',
                            lightBlue: '#38bdf8',
                            purple: '#7e22ce',
                            amber: '#b45309'
                        }
                    }
                }
            }
        }
    </script>
    
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen flex flex-col font-sans relative antialiased selection:bg-sky-100 selection:text-sky-900">

    <!-- Dong Son Drum Light Blue Background Watermarks -->
    <div class="fixed inset-0 pointer-events-none overflow-hidden z-0 select-none opacity-20">
        <!-- Top Right Drum -->
        <svg class="absolute -top-32 -right-32 w-[680px] h-[680px] text-sky-400 stroke-current fill-none" viewBox="0 0 600 600">
            <circle cx="300" cy="300" r="285" stroke-width="2.5" />
            <circle cx="300" cy="300" r="280" stroke-width="1" stroke-dasharray="3,3" />
            <circle cx="300" cy="300" r="268" stroke-width="1.8" />
            <circle cx="300" cy="300" r="230" stroke-width="1.4" />
            <circle cx="300" cy="300" r="140" stroke-width="1.6" />
            <circle cx="300" cy="300" r="88" stroke-width="1.5" />
            <polygon points="300,240 307,275 342,260 320,290 360,300 320,310 342,340 307,325 300,360 293,325 258,340 280,310 240,300 280,290 258,260 293,275" stroke-width="2" />
            <circle cx="300" cy="300" r="8" fill="currentColor" />
        </svg>

        <!-- Center Left Drum -->
        <svg class="absolute top-1/2 -left-44 -translate-y-1/2 w-[760px] h-[760px] text-blue-400 stroke-current fill-none" viewBox="0 0 600 600">
            <circle cx="300" cy="300" r="285" stroke-width="2.5" />
            <circle cx="300" cy="300" r="230" stroke-width="1.4" />
            <circle cx="300" cy="300" r="140" stroke-width="1.6" />
            <polygon points="300,240 307,275 342,260 320,290 360,300 320,310 342,340 307,325 300,360 293,325 258,340 280,310 240,300 280,290 258,260 293,275" stroke-width="2" />
            <circle cx="300" cy="300" r="8" fill="currentColor" />
        </svg>
    </div>

    <!-- Top Navigation Bar -->
    <header class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-slate-200/90 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <!-- Brand Logo -->
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-slate-900 to-indigo-950 flex items-center justify-center text-amber-300 shadow-md">
                    <i data-lucide="book-open" class="w-5 h-5"></i>
                </div>
                <div>
                    <a href="/" class="text-lg font-bold tracking-tight text-slate-900 flex items-center gap-2">
                        LibraNova
                        <span class="text-[11px] font-medium px-2 py-0.5 rounded-full bg-sky-100 text-sky-800 border border-sky-200">Laravel</span>
                    </a>
                    <p class="text-[11px] text-slate-500 hidden sm:block">Hệ Thống Quản Lý Thư Viện Tự Động Hóa</p>
                </div>
            </div>

            <!-- User Status, Notifications & Actions -->
            <div class="flex items-center gap-2.5 sm:gap-3">
                @if(Auth::check())
                    <!-- Session Timeout Badge -->
                    <div class="hidden md:flex items-center gap-1.5 px-3 py-1 bg-slate-100 border border-slate-200 rounded-full text-xs font-mono text-slate-600">
                        <i data-lucide="clock" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span>Phiên:</span>
                        <span id="session-timer" class="font-bold text-sky-700">14:59</span>
                    </div>

                    <!-- CHỨC NĂNG NHẬN THÔNG BÁO (NOTIFICATION BELL) -->
                    <div class="relative">
                        <button type="button" onclick="toggleNotificationDropdown()" id="btn-notification-bell" class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 border border-slate-200 transition relative">
                            <i data-lucide="bell" class="w-4 h-4"></i>
                            <span id="badge-notif-count" class="absolute -top-1 -right-1 w-4 h-4 bg-rose-500 text-white rounded-full text-[9px] font-bold flex items-center justify-center animate-pulse">2</span>
                        </button>

                        <!-- Notification Dropdown Menu -->
                        <div id="notification-dropdown" class="hidden absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-slate-200 p-4 space-y-3 z-50 animate-in fade-in slide-in-from-top-2 duration-150">
                            <div class="flex items-center justify-between border-b border-slate-100 pb-2.5">
                                <div class="font-bold text-xs text-slate-900 flex items-center gap-1.5">
                                    <i data-lucide="bell-ring" class="w-3.5 h-3.5 text-sky-600"></i>
                                    <span>Thông Báo Hệ Thống</span>
                                </div>
                                <span id="notif-header-badge" class="text-[10px] font-semibold bg-sky-50 text-sky-700 px-2 py-0.5 rounded-full border border-sky-200">2 mới</span>
                            </div>

                            <div class="space-y-2 max-h-64 overflow-y-auto pr-1 text-xs" id="notification-items-container">
                                @if(Auth::user()->role === 'admin')
                                    <!-- Thông báo Quản Trị Viên 1 -->
                                    <div class="p-2.5 bg-emerald-50/70 hover:bg-emerald-100/80 rounded-xl border border-emerald-100 space-y-1 cursor-pointer transition notif-item group relative"
                                        data-id="NOTIF-A01"
                                        data-title="Đối soát tài chính & VietQR Napas 247"
                                        data-type="financial"
                                        data-time="15 phút trước"
                                        data-sender="Cổng Thanh Toán Napas 247"
                                        data-content="Hệ thống tự động ghi nhận và đối soát thành công các khoản thu phạt trễ hạn và phí gia hạn thẻ qua mã VietQR Napas 247 vào tài khoản thư viện."
                                        onclick="openNotificationDetailFromElement(this)">
                                        <div class="font-semibold text-slate-900 flex items-center justify-between text-[11px]">
                                            <span class="flex items-center gap-1 text-emerald-800">💰 Đối soát tài chính VietQR</span>
                                            <span class="text-[10px] text-slate-400">15 phút trước</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] line-clamp-2">Hệ thống tự động ghi nhận và đối soát thành công các giao dịch thu phí qua VietQR.</p>
                                        <div class="flex items-center justify-between pt-0.5 text-[10px] text-slate-400">
                                            <span>Cổng Napas 247</span>
                                            <span class="text-sky-600 font-semibold group-hover:underline">Xem chi tiết &rarr;</span>
                                        </div>
                                        <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 absolute top-2.5 right-2 ring-2 ring-white"></span>
                                    </div>

                                    <!-- Thông báo Quản Trị Viên 2 -->
                                    <div class="p-2.5 bg-indigo-50/70 hover:bg-indigo-100/80 rounded-xl border border-indigo-100 space-y-1 cursor-pointer transition notif-item group relative"
                                        data-id="NOTIF-A02"
                                        data-title="Sao lưu hệ thống & Giám sát vận hành"
                                        data-type="system"
                                        data-time="2 giờ trước"
                                        data-sender="Hệ Thống Tự Động"
                                        data-content="Bản sao lưu cấu hình định kỳ cơ sở dữ liệu kho sách, độc giả và lịch sử giao dịch đã hoàn tất an toàn. Mọi dịch vụ đang vận hành ổn định."
                                        onclick="openNotificationDetailFromElement(this)">
                                        <div class="font-semibold text-slate-900 flex items-center justify-between text-[11px]">
                                            <span class="flex items-center gap-1 text-indigo-800">🛡️ Sao lưu hệ thống định kỳ</span>
                                            <span class="text-[10px] text-slate-400">2 giờ trước</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] line-clamp-2">Bản sao lưu dữ liệu toàn bộ kho sách và lịch sử giao dịch đã hoàn tất an toàn.</p>
                                        <div class="flex items-center justify-between pt-0.5 text-[10px] text-slate-400">
                                            <span>Hệ Thống Tự Động</span>
                                            <span class="text-sky-600 font-semibold group-hover:underline">Xem chi tiết &rarr;</span>
                                        </div>
                                        <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 absolute top-2.5 right-2 ring-2 ring-white"></span>
                                    </div>
                                @elseif(Auth::user()->role === 'librarian')
                                    <!-- Thông báo Thủ Thư 1 -->
                                    <div class="p-2.5 bg-amber-50/70 hover:bg-amber-100/80 rounded-xl border border-amber-100 space-y-1 cursor-pointer transition notif-item group relative"
                                        data-id="NOTIF-L01"
                                        data-title="Yêu cầu mượn sách trực tuyến mới"
                                        data-type="loan_request"
                                        data-time="5 phút trước"
                                        data-sender="Hệ Thống Lưu Thông"
                                        data-content="Có yêu cầu đăng ký mượn sách mới từ độc giả gửi đến. Vui lòng kiểm tra tình trạng sách thực tế trên kệ và xác nhận giao sách tại quầy phục vụ."
                                        onclick="openNotificationDetailFromElement(this)">
                                        <div class="font-semibold text-slate-900 flex items-center justify-between text-[11px]">
                                            <span class="flex items-center gap-1 text-amber-900">📚 Yêu cầu mượn sách mới</span>
                                            <span class="text-[10px] text-slate-400">5 phút trước</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] line-clamp-2">Có yêu cầu đăng ký mượn sách mới từ độc giả cần thủ thư kiểm tra và duyệt sách tại quầy.</p>
                                        <div class="flex items-center justify-between pt-0.5 text-[10px] text-slate-400">
                                            <span>Hệ Thống Lưu Thông</span>
                                            <span class="text-sky-600 font-semibold group-hover:underline">Xem chi tiết &rarr;</span>
                                        </div>
                                        <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 absolute top-2.5 right-2 ring-2 ring-white"></span>
                                    </div>

                                    <!-- Thông báo Thủ Thư 2 -->
                                    <div class="p-2.5 bg-rose-50/70 hover:bg-rose-100/80 rounded-xl border border-rose-100 space-y-1 cursor-pointer transition notif-item group relative"
                                        data-id="NOTIF-L02"
                                        data-title="Cảnh báo phiếu mượn quá hạn"
                                        data-type="due_reminder"
                                        data-time="1 giờ trước"
                                        data-sender="Bộ Phận Giám Sát"
                                        data-content="Hệ thống ghi nhận có phiếu mượn sách đã quá hạn trả. Vui lòng liên hệ độc giả để thu hồi sách hoặc hỗ trợ gia hạn / nộp phạt theo quy định."
                                        onclick="openNotificationDetailFromElement(this)">
                                        <div class="font-semibold text-slate-900 flex items-center justify-between text-[11px]">
                                            <span class="flex items-center gap-1 text-rose-800">⚠️ Cảnh báo sách quá hạn</span>
                                            <span class="text-[10px] text-slate-400">1 giờ trước</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] line-clamp-2">Hệ thống ghi nhận có phiếu mượn đã quá hạn cần thủ thư liên hệ thu hồi.</p>
                                        <div class="flex items-center justify-between pt-0.5 text-[10px] text-slate-400">
                                            <span>Bộ Phận Giám Sát</span>
                                            <span class="text-sky-600 font-semibold group-hover:underline">Xem chi tiết &rarr;</span>
                                        </div>
                                        <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 absolute top-2.5 right-2 ring-2 ring-white"></span>
                                    </div>
                                @else
                                    <!-- Thông báo Độc Giả 1 -->
                                    <div class="p-2.5 bg-blue-50/70 hover:bg-blue-100/80 rounded-xl border border-blue-100 space-y-1 cursor-pointer transition notif-item group relative"
                                        data-id="NOTIF-R01"
                                        data-title="Nhắc nhở hạn trả sách mượn"
                                        data-type="due_reminder"
                                        data-time="Vừa xong"
                                        data-sender="Thủ Thư Ca Trực"
                                        data-content="Hạn mượn cuốn sách của bạn sẽ đến hạn vào ngày mai. Hãy kiểm tra danh sách 'Sách Đang Mượn' để hoàn trả hoặc gia hạn đúng hạn để tránh phát sinh phí phạt trễ hạn."
                                        onclick="openNotificationDetailFromElement(this)">
                                        <div class="font-semibold text-slate-900 flex items-center justify-between text-[11px]">
                                            <span class="flex items-center gap-1 text-blue-900">📅 Nhắc nhở hạn trả sách</span>
                                            <span class="text-[10px] text-slate-400">Vừa xong</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] line-clamp-2">Hạn mượn cuốn sách của bạn sẽ đến hạn vào ngày mai. Hãy kiểm tra mục Sách Đang Mượn.</p>
                                        <div class="flex items-center justify-between pt-0.5 text-[10px] text-slate-400">
                                            <span>Thủ Thư Ca Trực</span>
                                            <span class="text-sky-600 font-semibold group-hover:underline">Xem chi tiết &rarr;</span>
                                        </div>
                                        <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 absolute top-2.5 right-2 ring-2 ring-white"></span>
                                    </div>

                                    <!-- Thông báo Độc Giả 2 -->
                                    <div class="p-2.5 bg-emerald-50/70 hover:bg-emerald-100/80 rounded-xl border border-emerald-100 space-y-1 cursor-pointer transition notif-item group relative"
                                        data-id="NOTIF-R02"
                                        data-title="Cập nhật sách mới vào kho lưu thông"
                                        data-type="system"
                                        data-time="2 giờ trước"
                                        data-sender="Ban Quản Lý Thư Viện"
                                        data-content="Thư viện vừa bổ sung thêm các đầu sách mới thuộc chuyên mục Công Nghệ Thông Tin & Kỹ Năng. Kính mời quý bạn đọc tra cứu và đăng ký mượn trực tuyến."
                                        onclick="openNotificationDetailFromElement(this)">
                                        <div class="font-semibold text-slate-900 flex items-center justify-between text-[11px]">
                                            <span class="flex items-center gap-1 text-emerald-900">🎉 Cập nhật kho sách mới</span>
                                            <span class="text-[10px] text-slate-400">2 giờ trước</span>
                                        </div>
                                        <p class="text-slate-600 text-[11px] line-clamp-2">Thư viện vừa bổ sung thêm các đầu sách mới thuộc chuyên mục Công Nghệ Thông Tin & Kỹ Năng.</p>
                                        <div class="flex items-center justify-between pt-0.5 text-[10px] text-slate-400">
                                            <span>Ban Quản Lý</span>
                                            <span class="text-sky-600 font-semibold group-hover:underline">Xem chi tiết &rarr;</span>
                                        </div>
                                        <span class="notif-unread-dot w-2 h-2 rounded-full bg-rose-500 absolute top-2.5 right-2 ring-2 ring-white"></span>
                                    </div>
                                @endif
                            </div>

                            <div class="pt-2 border-t border-slate-100 text-center">
                                <button type="button" onclick="markAllNotificationsAsRead()" class="text-[11px] text-sky-600 font-semibold hover:underline cursor-pointer">
                                    Đánh dấu tất cả là đã đọc
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Role Badge -->
                    @php
                        $role = Auth::user()->role;
                        $roleColors = [
                            'reader' => 'bg-blue-50 text-blue-700 border-blue-200',
                            'librarian' => 'bg-purple-50 text-purple-700 border-purple-200',
                            'admin' => 'bg-amber-50 text-amber-800 border-amber-200'
                        ];
                        $roleLabels = [
                            'reader' => 'Độc Giả',
                            'librarian' => 'Thủ Thư',
                            'admin' => 'Quản Trị Viên'
                        ];
                    @endphp
                    <div class="flex items-center gap-2 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-xl">
                        <div class="text-right hidden sm:block">
                            <div class="text-xs font-bold text-slate-800">{{ Auth::user()->name }}</div>
                            <span class="text-[10px] px-1.5 py-0.2 rounded font-semibold border {{ $roleColors[$role] ?? 'bg-slate-100' }}">
                                {{ $roleLabels[$role] ?? ucfirst($role) }}
                            </span>
                        </div>
                        <form action="{{ route('logout') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition" title="Đăng xuất">
                                <i data-lucide="log-out" class="w-4 h-4"></i>
                            </button>
                        </form>
                    </div>
                @else
                    <a href="{{ route('login') }}" class="px-4 py-2 text-sm font-semibold bg-sky-600 text-white rounded-xl shadow hover:bg-sky-700 transition">
                        Đăng Nhập
                    </a>
                @endif
            </div>
        </div>
    </header>

    <!-- Global Alert Messages -->
    <div id="global-alert-container" class="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 mt-4">
        @if(session('success'))
            <div id="alert-success" class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl flex items-center justify-between shadow-sm mb-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="check-circle-2" class="w-5 h-5 text-emerald-600 flex-shrink-0"></i>
                    <div class="text-sm font-medium">{{ session('success') }}</div>
                </div>
                <button type="button" onclick="document.getElementById('alert-success')?.remove()" class="text-emerald-500 hover:text-emerald-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if(session('error'))
            <div id="alert-error" class="p-4 bg-red-50 border border-red-200 text-red-800 rounded-2xl flex items-center justify-between shadow-sm mb-4">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-triangle" class="w-5 h-5 text-red-600 flex-shrink-0"></i>
                    <div class="text-sm font-medium">{{ session('error') }}</div>
                </div>
                <button type="button" onclick="document.getElementById('alert-error')?.remove()" class="text-red-500 hover:text-red-700 p-1 rounded-lg">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif
    </div>

    <!-- Main Content DUY NHẤT -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-4 sm:p-6 lg:p-8">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="mt-auto border-t border-slate-200/80 bg-white/80 backdrop-blur-sm py-6 relative z-10">
        <div class="max-w-7xl mx-auto px-4 text-center text-xs text-slate-500 flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>
                © {{ date('Y') }} <strong>LibraNova</strong> — Hệ Thống Quản Lý Thư Viện Tự Động Hóa Vận Hành
            </div>
            <div class="flex items-center gap-4 text-[11px] text-slate-400">
                <span>Laravel Framework</span>
                <span>•</span>
                <span>HTML - CSS - JavaScript</span>
                <span>•</span>
                <span>VietQR Napas 247</span>
            </div>
        </div>
    </footer>

    <!-- Modal Chi Tiết Thông Báo (Đặt TRƯỚC thẻ script) -->
    @include('partials.modal-notification-detail')

    <!-- TOÀN BỘ SCRIPT JAVASCRIPT HỆ THỐNG -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (window.lucide) {
                window.lucide.createIcons();
            }
        });

        // Đếm ngược phiên đăng nhập
        let sessionSeconds = 15 * 60;
        const sessionTimerEl = document.getElementById('session-timer');
        if (sessionTimerEl) {
            setInterval(function() {
                if (sessionSeconds > 0) {
                    sessionSeconds--;
                    const m = Math.floor(sessionSeconds / 60);
                    const s = sessionSeconds % 60;
                    sessionTimerEl.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
                }
            }, 1000);
        }

        // Bật / tắt dropdown chuông thông báo
        window.toggleNotificationDropdown = function() {
            const dropdown = document.getElementById('notification-dropdown');
            if (dropdown) {
                dropdown.classList.toggle('hidden');
                if (!dropdown.classList.contains('hidden') && window.lucide) {
                    window.lucide.createIcons();
                }
            }
        };

        // Tự động đóng dropdown khi click ra ngoài
        document.addEventListener('click', function(e) {
            const dropdown = document.getElementById('notification-dropdown');
            const bell = document.getElementById('btn-notification-bell');
            if (dropdown && !dropdown.classList.contains('hidden')) {
                if (!dropdown.contains(e.target) && !bell.contains(e.target)) {
                    dropdown.classList.add('hidden');
                }
            }
        });

        // Mở Modal chung
        window.openModal = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.remove('hidden');
                el.classList.add('flex');
                document.body.style.overflow = 'hidden';
                if (window.lucide) {
                    window.lucide.createIcons();
                }
            }
        };

        // Đóng Modal chung
        window.closeModal = function(id) {
            const el = document.getElementById(id);
            if (el) {
                el.classList.add('hidden');
                el.classList.remove('flex');
                document.body.style.overflow = '';
            }
        };

        // Mở xem chi tiết thông báo
        window.openNotificationDetailFromElement = function(el) {
            if (!el) return;

            const notifId = el.getAttribute('data-id') || 'NOTIF-01';
            const notifTitle = el.getAttribute('data-title') || 'Thông báo hệ thống';
            const notifContent = el.getAttribute('data-content') || '';
            const notifType = el.getAttribute('data-type') || 'system';
            const notifTime = el.getAttribute('data-time') || 'Vừa xong';
            const notifSender = el.getAttribute('data-sender') || 'Hệ Thống';

            const modal = document.getElementById('modal-notification-detail');
            if (modal) {
                // Đổ dữ liệu vào Modal
                const idEl = document.getElementById('notif-modal-id');
                if (idEl) idEl.textContent = '#' + notifId;

                const titleEl = document.getElementById('notif-modal-title');
                if (titleEl) titleEl.textContent = notifTitle;

                const contentEl = document.getElementById('notif-modal-content');
                if (contentEl) contentEl.textContent = notifContent;

                const timeEl = document.getElementById('notif-modal-time');
                if (timeEl) timeEl.textContent = notifTime;

                const senderWrapper = document.getElementById('notif-modal-sender-wrapper');
                const senderEl = document.getElementById('notif-modal-sender');
                if (senderWrapper && senderEl) {
                    if (notifSender) {
                        senderWrapper.classList.remove('hidden');
                        senderEl.textContent = notifSender;
                    } else {
                        senderWrapper.classList.add('hidden');
                    }
                }

                // Định màu sắc theo loại thông báo
                const badgeEl = document.getElementById('notif-modal-badge');
                const iconWrapper = document.getElementById('notif-modal-icon-wrapper');
                const contentBox = document.getElementById('notif-modal-content-box');

                if (badgeEl && iconWrapper && contentBox) {
                    if (notifType === 'financial') {
                        badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-emerald-100 text-emerald-800 border-emerald-200';
                        badgeEl.textContent = 'Tài Chính & VietQR';
                        iconWrapper.className = 'w-11 h-11 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-center flex-shrink-0 text-emerald-600';
                        contentBox.className = 'p-4 sm:p-5 rounded-2xl bg-emerald-50/60 border border-emerald-200 text-xs sm:text-sm text-slate-800 leading-relaxed space-y-2';
                    } else if (notifType === 'loan_request') {
                        badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-amber-100 text-amber-800 border-amber-200';
                        badgeEl.textContent = 'Lưu Thông Quầy';
                        iconWrapper.className = 'w-11 h-11 rounded-2xl bg-amber-50 border border-amber-200 flex items-center justify-center flex-shrink-0 text-amber-600';
                        contentBox.className = 'p-4 sm:p-5 rounded-2xl bg-amber-50/60 border border-amber-200 text-xs sm:text-sm text-slate-800 leading-relaxed space-y-2';
                    } else if (notifType === 'due_reminder' || notifType === 'overdue_alert') {
                        badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-rose-100 text-rose-800 border-rose-200';
                        badgeEl.textContent = 'Hạn Mượn Sách';
                        iconWrapper.className = 'w-11 h-11 rounded-2xl bg-rose-50 border border-rose-200 flex items-center justify-center flex-shrink-0 text-rose-600';
                        contentBox.className = 'p-4 sm:p-5 rounded-2xl bg-rose-50/60 border border-rose-200 text-xs sm:text-sm text-slate-800 leading-relaxed space-y-2';
                    } else {
                        badgeEl.className = 'px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-sky-100 text-sky-800 border-sky-200';
                        badgeEl.textContent = 'Thông Báo Hệ Thống';
                        iconWrapper.className = 'w-11 h-11 rounded-2xl bg-sky-50 border border-sky-200 flex items-center justify-center flex-shrink-0 text-sky-600';
                        contentBox.className = 'p-4 sm:p-5 rounded-2xl bg-sky-50/60 border border-sky-200 text-xs sm:text-sm text-slate-800 leading-relaxed space-y-2';
                    }
                }

                // Tự động đóng menu dropdown thông báo khi mở modal
                const dropdown = document.getElementById('notification-dropdown');
                if (dropdown) {
                    dropdown.classList.add('hidden');
                }

                // Mở Modal
                window.openModal('modal-notification-detail');

                // Xóa chấm đỏ của thông báo này
                const dot = el.querySelector('.notif-unread-dot');
                if (dot) {
                    dot.remove();
                }
                el.classList.remove('bg-blue-50/70', 'bg-emerald-50/70', 'bg-amber-50/70', 'bg-rose-50/70', 'bg-indigo-50/70', 'border-blue-100', 'border-emerald-100', 'border-amber-100', 'border-rose-100', 'border-indigo-100');
                el.classList.add('bg-slate-50', 'border-slate-200', 'opacity-80');

                window.updateNotificationBadge();
            }
        };

        window.closeNotificationDetailModal = function() {
            window.closeModal('modal-notification-detail');
        };

        // Đánh dấu tất cả là đã đọc -> Xóa toàn bộ chấm đỏ
        window.markAllNotificationsAsRead = function() {
            const dots = document.querySelectorAll('.notif-unread-dot');
            dots.forEach(function(d) { d.remove(); });

            const items = document.querySelectorAll('.notif-item');
            items.forEach(function(el) {
                el.classList.remove('bg-blue-50/70', 'bg-emerald-50/70', 'bg-amber-50/70', 'bg-rose-50/70', 'bg-indigo-50/70', 'border-blue-100', 'border-emerald-100', 'border-amber-100', 'border-rose-100', 'border-indigo-100');
                el.classList.add('bg-slate-50', 'border-slate-200', 'opacity-80');
            });

            window.updateNotificationBadge();

            const dropdown = document.getElementById('notification-dropdown');
            if (dropdown) {
                dropdown.classList.add('hidden');
            }
        };

        // Cập nhật số đếm chuông đỏ
        window.updateNotificationBadge = function() {
            const remainingUnread = document.querySelectorAll('.notif-unread-dot').length;
            const badge = document.getElementById('badge-notif-count');
            const headerBadge = document.getElementById('notif-header-badge');

            if (badge) {
                if (remainingUnread === 0) {
                    badge.style.display = 'none';
                } else {
                    badge.style.display = 'flex';
                    badge.textContent = remainingUnread;
                }
            }

            if (headerBadge) {
                if (remainingUnread === 0) {
                    headerBadge.className = 'text-[10px] font-semibold bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full border border-slate-200';
                    headerBadge.textContent = '0 mới';
                } else {
                    headerBadge.className = 'text-[10px] font-semibold bg-sky-50 text-sky-700 px-2 py-0.5 rounded-full border border-sky-200';
                    headerBadge.textContent = remainingUnread + ' mới';
                }
            }
        };
    </script>

    @yield('scripts')
</body>
</html>