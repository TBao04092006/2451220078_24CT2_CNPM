/**
 * LibraNova Library Management - Client-Side JavaScript
 * Pure Vanilla JavaScript for DOM interactions, VietQR generator, and real-time calculations.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Auto re-initialize Lucide icons if dynamically loaded
    if (window.lucide) {
        window.lucide.createIcons();
    }
});

// Format currency helper
function formatVND(amount) {
    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(amount);
}

// Modal generic opener / closer
function openModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.remove('hidden');
        el.classList.add('flex');
        document.body.style.overflow = 'hidden';
        if (window.lucide) {
            window.lucide.createIcons();
        }
    }
}

function closeModal(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.add('hidden');
        el.classList.remove('flex');
        document.body.style.overflow = '';
    }
}

// Mở xem chi tiết thông báo & xóa chấm đỏ của thông báo đó
function openNotificationDetailFromElement(el) {
    if (!el) return;
    const notifId = el.dataset.id || 'NOTIF-01';
    const notifTitle = el.dataset.title || 'Thông báo hệ thống';
    const notifContent = el.dataset.content || '';
    const notifType = el.dataset.type || 'system';
    const notifTime = el.dataset.time || 'Vừa xong';
    const notifSender = el.dataset.sender || 'Hệ Thống';

    // Update modal elements
    const modal = document.getElementById('modal-notification-detail');
    if (modal) {
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

        // Style based on type
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

        // Open modal
        openModal('modal-notification-detail');

        // Mark item as read visually
        const dot = el.querySelector('.notif-unread-dot');
        if (dot) {
            dot.remove();
        }
        el.classList.remove('bg-blue-50/70', 'bg-emerald-50/70', 'bg-amber-50/70', 'bg-rose-50/70', 'bg-indigo-50/70', 'border-blue-100', 'border-emerald-100', 'border-amber-100', 'border-rose-100', 'border-indigo-100');
        el.classList.add('bg-slate-50', 'border-slate-200', 'opacity-80');

        updateNotificationBadge();
    }
}

function closeNotificationDetailModal() {
    closeModal('modal-notification-detail');
}

// Đánh dấu tất cả là đã đọc -> xóa hết chấm đỏ và ẩn badge đỏ
function markAllNotificationsAsRead() {
    const dots = document.querySelectorAll('.notif-unread-dot');
    dots.forEach(d => d.remove());

    const items = document.querySelectorAll('.notif-item');
    items.forEach(el => {
        el.classList.remove('bg-blue-50/70', 'bg-emerald-50/70', 'bg-amber-50/70', 'bg-rose-50/70', 'bg-indigo-50/70', 'border-blue-100', 'border-emerald-100', 'border-amber-100', 'border-rose-100', 'border-indigo-100');
        el.classList.add('bg-slate-50', 'border-slate-200', 'opacity-80');
    });

    updateNotificationBadge();

    // Close dropdown
    const dropdown = document.getElementById('notification-dropdown');
    if (dropdown) {
        setTimeout(() => dropdown.classList.add('hidden'), 200);
    }
}

function updateNotificationBadge() {
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
            headerBadge.textContent = `${remainingUnread} mới`;
        }
    }
}