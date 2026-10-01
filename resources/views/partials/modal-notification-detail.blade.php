<!-- Modal Chi Tiết Thông Báo (Laravel Blade) -->
<div id="modal-notification-detail" class="fixed inset-0 z-[120] bg-slate-950/60 backdrop-blur-sm hidden items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 space-y-5 animate-in zoom-in-95 duration-200" onclick="event.stopPropagation()">
        <!-- Header -->
        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-4">
            <div class="flex items-center gap-3">
                <div id="notif-modal-icon-wrapper" class="w-11 h-11 rounded-2xl bg-blue-50 border border-blue-200 flex items-center justify-center flex-shrink-0 shadow-2xs">
                    <i data-lucide="bell" id="notif-modal-icon" class="w-5 h-5 text-blue-600"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span id="notif-modal-badge" class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border bg-blue-100 text-blue-800 border-blue-200">
                            Thông Báo Hệ Thống
                        </span>
                        <span id="notif-modal-id" class="text-[11px] font-mono text-slate-400">
                            #NOTIF-01
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5">
                        <i data-lucide="calendar" class="w-3.5 h-3.5 text-slate-400"></i>
                        <span id="notif-modal-time">Vừa xong</span>
                        <span id="notif-modal-sender-wrapper" class="hidden">
                            <span>•</span>
                            <span id="notif-modal-sender" class="font-medium text-slate-700">Ban Quản Lý</span>
                        </span>
                    </p>
                </div>
            </div>

            <button type="button" onclick="closeNotificationDetailModal()" class="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded-xl transition">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>

        <!-- Title -->
        <div>
            <h3 id="notif-modal-title" class="text-base sm:text-lg font-bold text-slate-900 leading-snug">
                Tiêu đề thông báo
            </h3>
        </div>

        <!-- Content Box -->
        <div id="notif-modal-content-box" class="p-4 sm:p-5 rounded-2xl bg-blue-50/60 border border-blue-200 text-xs sm:text-sm text-slate-800 leading-relaxed space-y-2">
            <div id="notif-modal-content" class="whitespace-pre-line font-normal">
                Nội dung chi tiết của thông báo.
            </div>
        </div>

        <!-- Status Confirmation -->
        <div class="flex items-center justify-between text-xs text-slate-500 bg-slate-50 px-3.5 py-2.5 rounded-xl border border-slate-200/80">
            <div class="flex items-center gap-2 text-emerald-700 font-medium">
                <i data-lucide="check-circle-2" class="w-4 h-4 text-emerald-600"></i>
                <span>Đã đánh dấu thông báo này là đã đọc</span>
            </div>
            <span class="text-[11px] text-slate-400">Tự động đồng bộ</span>
        </div>

        <!-- Action Buttons -->
        <div class="flex items-center justify-end gap-3 pt-2">
            <button type="button" onclick="closeNotificationDetailModal()" class="px-4 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs rounded-xl transition">
                Đã hiểu & Đóng
            </button>
        </div>
    </div>
</div>