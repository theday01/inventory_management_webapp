<?php
require_once 'session.php';
require_once __DIR__ . '/src/language.php';
$page_title = __('notifications_page_title');
$current_page = 'notifications.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<style>
    #pagination-container {
        background-color: rgb(13 16 22);
        backdrop-filter: blur(12px);
        border-color: rgba(255, 255, 255, 0.05);
        position: sticky;
        bottom: -50px;
        z-index: 20;
    }

    .pagination-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        padding: 0.5rem 0.75rem;
        background-color: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: rgb(209, 213, 219);
        border-radius: 0.625rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .pagination-btn:hover:not(:disabled):not(.opacity-50) {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.2);
        color: white;
    }

    .pagination-btn:disabled {
        cursor: not-allowed;
        opacity: 0.5;
    }

    .pagination-btn.bg-primary {
        background-color: var(--color-primary, #059669);
        border-color: var(--color-primary, #059669);
        color: white;
    }

    .pagination-btn.bg-primary:hover {
        background-color: var(--color-primary-hover, #047857);
    }
</style>

<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent/5 rounded-full blur-[100px] pointer-events-none"></div>

    <header class="h-auto md:h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex flex-col md:flex-row items-start md:items-center justify-between p-4 md:px-8 relative z-10 shrink-0 gap-4">
        <div class="flex flex-col md:flex-row md:items-center gap-4 w-full md:w-auto">
            <h2 class="text-xl font-bold text-white"><?= __('notifications_page_title') ?></h2>
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex items-center gap-2 bg-yellow-500/10 border border-yellow-500/20 px-3 py-1.5 rounded-lg w-full md:w-auto">
                    <span class="material-icons-round text-yellow-400 text-sm">info</span>
                    <span class="text-yellow-400 text-xs"><?= __('notifications_auto_delete_msg') ?></span>
                </div>
            </div>
        </div>
        
        <div class="flex flex-col-reverse md:flex-row items-center gap-3 w-full md:w-auto justify-between md:justify-end">
             <div class="flex bg-white/5 p-1 rounded-xl border border-white/5 w-full md:w-auto justify-center">
                <button onclick="setFilter('all')" id="filter-all" class="flex-1 md:flex-none px-4 py-1.5 rounded-lg text-sm font-medium transition-all bg-primary text-white text-center"><?= __('filter_all') ?></button>
                <button onclick="setFilter('unread')" id="filter-unread" class="flex-1 md:flex-none px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white text-center"><?= __('filter_unread') ?></button>
                <button onclick="setFilter('read')" id="filter-read" class="flex-1 md:flex-none px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white text-center"><?= __('filter_read') ?></button>
            </div>

            <div class="flex items-center gap-3 w-full md:w-auto justify-end">
                <span id="auto-refresh-indicator" class="text-xs text-primary/70 hidden transition-opacity whitespace-nowrap"><?= __('auto_refreshing') ?></span>
                
                <button onclick="markAllAsRead()" id="main-notification-btn" class="relative p-2 bg-white/5 hover:bg-green-500/20 rounded-full transition-all text-white" title="<?= __('mark_all_read_title') ?>">
                    <span class="material-icons-round">done_all</span>
                    <span id="unread-dot" class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-dark-surface hidden"></span>
                </button>

                <button onclick="loadNotifications(false)" class="p-2 bg-white/5 hover:bg-white/10 rounded-full transition-colors text-white" title="<?= __('refresh_list_title') ?>">
                    <span class="material-icons-round">refresh</span>
                </button>
            </div>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-4 md:p-8 relative z-10">
        <div id="loading-screen" class="flex flex-col items-center justify-center h-full min-h-[300px]">
            <div class="relative w-16 h-16">
                <div class="absolute w-full h-full rounded-full border-4 border-white/5"></div>
                <div class="absolute w-full h-full rounded-full border-4 border-primary border-t-transparent animate-spin"></div>
            </div>
            <p class="mt-6 text-gray-400 text-sm animate-pulse"><?= __('fetching_notifications') ?></p>
        </div>

        <div id="notifications-container" class="space-y-4 hidden opacity-0 transition-opacity duration-300 pb-20"></div>
        <div id="pagination-container" class="mt-8 flex justify-center items-center gap-2 pb-10 flex-wrap">
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden" style="z-index: 100;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0 duration-300" id="confirm-modal-backdrop"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[90%] md:w-full max-w-sm p-6 bg-dark-surface border border-white/10 rounded-2xl shadow-2xl transform scale-90 opacity-0 transition-all duration-300" id="confirm-modal-content">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 ring-4 ring-primary/5">
                    <span class="material-icons-round text-3xl text-primary">priority_high</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-2" id="confirm-title"><?= __('confirm_action_title') ?></h3>
                <p class="text-gray-400 mb-6 text-sm leading-relaxed" id="confirm-message"><?= __('confirm_action_message') ?></p>
                
                <div class="flex flex-col md:flex-row items-center gap-3 justify-center">
                    <button id="confirm-btn-yes" class="w-full md:w-auto px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 active:scale-95"><?= __('yes_confirm') ?></button>
                    <button id="confirm-btn-no" class="w-full md:w-auto px-6 py-2.5 bg-white/5 hover:bg-white/10 text-gray-300 rounded-xl font-medium transition-all active:scale-95 border border-white/5"><?= __('cancel') ?></button>
                </div>
            </div>
        </div>
    </div>
</main>


<script>
// Helper function to escape HTML
function escapeHtml(text) {
  if (text == null) return '';
  return String(text)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// صوت تنبيه قصير مدمج (Base64) لتجنب مشاكل الروابط الخارجية
const soundBase64 = "data:audio/mp3;base64,//uQxAAAAANIAAAAABxBTUUzLjEwMAr///8=";
const notificationSound = new Audio(soundBase64);
let audioCtx = null;
function gentleChime() {
    try {
        if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
        if (audioCtx.state === 'suspended') audioCtx.resume();
        const osc = audioCtx.createOscillator();
        const gain = audioCtx.createGain();
        osc.type = 'sine';
        osc.frequency.value = 660;
        gain.gain.setValueAtTime(0.0, audioCtx.currentTime);
        gain.gain.linearRampToValueAtTime(0.15, audioCtx.currentTime + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.6);
        osc.connect(gain); gain.connect(audioCtx.destination);
        osc.start(); osc.stop(audioCtx.currentTime + 0.6);
        const osc2 = audioCtx.createOscillator();
        const gain2 = audioCtx.createGain();
        osc2.type = 'sine';
        osc2.frequency.value = 880;
        gain2.gain.setValueAtTime(0.0, audioCtx.currentTime + 0.35);
        gain2.gain.linearRampToValueAtTime(0.12, audioCtx.currentTime + 0.38);
        gain2.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.95);
        osc2.connect(gain2); gain2.connect(audioCtx.destination);
        osc2.start(audioCtx.currentTime + 0.35); osc2.stop(audioCtx.currentTime + 0.95);
    } catch (e) {
        notificationSound.currentTime = 0;
        notificationSound.volume = 0.2;
        notificationSound.play().catch(()=>{});
    }
}

let lastUnreadCount = null;
let currentFilter = 'all';
let currentPage = 1;
let soundEnabled = false; // متغير لتتبع حالة تفعيل الصوت

// دالة لتجربة الصوت وتفعيله (يجب استدعاؤها بحدث نقر من المستخدم)
function testSound() {
    notificationSound.currentTime = 0;
    notificationSound.play()
        .then(() => {
            console.log("Audio test successful");
            soundEnabled = true; // تم تفعيل الصوت بنجاح
            // إظهار رسالة صغيرة للمستخدم
            alert(window.__('sound_enabled_success')); 
        })
        .catch(error => {
            console.error("Audio playback failed:", error);
            alert(window.__('sound_enable_failed'));
        });
}

// محاولة تفعيل الصوت عند أي نقرة في الصفحة
document.addEventListener('click', function initAudio() {
    if (!soundEnabled) {
        notificationSound.load(); // تحميل الصوت فقط
        soundEnabled = true;
        console.log("Audio initialized by user interaction");
    }
}, { once: true });

function formatArabicDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('ar-MA', {
        year: 'numeric', month: 'long', day: 'numeric',
        hour: '2-digit', minute: '2-digit', hour12: false
    });
}

function setFilter(filter) {
    currentFilter = filter;
    currentPage = 1;
    
    ['all', 'unread', 'read'].forEach(f => {
        const btn = document.getElementById('filter-' + f);
        if(btn) {
            btn.className = (f === filter) 
                ? "flex-1 md:flex-none px-4 py-1.5 rounded-lg text-sm font-medium transition-all bg-primary text-white shadow-lg shadow-primary/20 text-center" 
                : "flex-1 md:flex-none px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white bg-white/5 text-center";
        }
    });

    loadNotifications(false, 1);
}

function loadNotifications(isBackgroundUpdate = false, page = 1) {
    if (!isBackgroundUpdate) {
        currentPage = page;
        document.getElementById('loading-screen').classList.remove('hidden');
        document.getElementById('notifications-container').classList.add('hidden');
    }
    
    fetch(`api.php?action=getNotifications&page=${page}&filter=${currentFilter}`) 
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const totalUnread = parseInt(data.unread_count); 
                const sidebarBadge = document.getElementById('notification-count');
                const unreadDot = document.getElementById('unread-dot');
                const markAllBtn = document.getElementById('main-notification-btn');

                if (sidebarBadge) {
                    sidebarBadge.textContent = totalUnread > 50 ? '*50' : totalUnread;
                    if (totalUnread > 0) {
                        sidebarBadge.classList.remove('bg-green-500');
                        sidebarBadge.classList.add('bg-red-500');
                    } else {
                        sidebarBadge.classList.remove('bg-red-500');
                        sidebarBadge.classList.add('bg-green-500');
                    }
                    sidebarBadge.style.display = 'inline-flex';
                }

                if(unreadDot) unreadDot.classList.toggle('hidden', totalUnread === 0);

                // --- منطق تشغيل الصوت المحدث ---
                // الشرط: العدد الحالي أكبر من السابق، والعدد الحالي لا يساوي 0
                if (lastUnreadCount !== null && totalUnread > lastUnreadCount) {
                    gentleChime();
                    
                    if(markAllBtn) markAllBtn.classList.add('new-notification-blink', 'shake-icon');
                } else {
                    // للتجربة فقط: طباعة في الكونسول
                   // console.log(`No new notifications. Current: ${totalUnread}, Last: ${lastUnreadCount}`);
                }

                lastUnreadCount = totalUnread;
                renderNotifications(data.data, isBackgroundUpdate);
                renderPagination(data.pagination);
            }
        })
        .catch(err => {
            console.error("API Error:", err);
            document.getElementById('loading-screen').classList.add('hidden');
        });
}

function renderNotifications(notifications, isBackgroundUpdate) {
    const container = document.getElementById('notifications-container');
    const loadingScreen = document.getElementById('loading-screen');
    
    loadingScreen.classList.add('hidden');
    container.classList.remove('hidden');
    
    if (!isBackgroundUpdate) {
        container.classList.remove('opacity-0');
    }

    container.innerHTML = '';
    
    if (notifications.length > 0) {
        notifications.forEach((notification, index) => {
            const isUnread = notification.status === 'unread';
            const flashClass = isUnread ? 'unread-flash-card' : '';
            const delay = isBackgroundUpdate ? 0 : index * 50;
            const safeMessage = escapeHtml(notification.message);
            
            container.innerHTML += `
                <div class="bg-dark-surface/60 border ${flashClass} ${isUnread ? 'border-primary/30' : 'border-white/5'} rounded-2xl p-4 md:p-6 transition-all hover:bg-dark-surface/80 animate-fade-in-up" style="animation-delay: ${delay}ms;">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-start space-x-4 space-x-reverse w-full">
                            <div class="p-3 rounded-full ${isUnread ? 'bg-primary/20' : 'bg-gray-700/30'} shrink-0">
                                <span class="material-icons-round ${isUnread ? 'text-primary shake-icon' : 'text-gray-400'}">
                                    ${isUnread ? 'notifications_active' : 'notifications'}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-white ${isUnread ? 'font-bold' : ''} break-words">${safeMessage}</p>
                                <p class="text-gray-400 text-sm mt-1 flex items-center gap-1">
                                    <span class="material-icons-round text-xs">schedule</span>
                                    ${formatArabicDate(notification.created_at)}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end md:self-center shrink-0">
                            ${isUnread ? `
                                <button onclick="markAsRead(${notification.id})" class="p-2 text-green-400 bg-green-500/10 hover:bg-green-500/20 rounded-lg border border-green-500/20 transition-all" title="${window.__('mark_as_read_tooltip')}">
                                    <span class="material-icons-round text-base">done</span>
                                </button>
                            ` : ''}
                            <button onclick="deleteNotification(${notification.id})" class="p-2 text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-lg border border-red-500/20 transition-all" title="${window.__('delete_tooltip')}">
                                <span class="material-icons-round text-base">delete</span>
                            </button>
                        </div>
                    </div>
                </div>`;
        });
    } else {
        container.innerHTML = `
            <div class="flex flex-col items-center justify-center py-20 text-gray-500">
                <span class="material-icons-round text-6xl opacity-20 mb-4">notifications_off</span>
                <p>${window.__('no_notifications_in_list')}</p>
            </div>`;
    }
}

function renderPagination(info) {
    const container = document.getElementById('pagination-container');
    if (!info || info.total_pages <= 1) { 
        container.innerHTML = ''; 
        return; 
    }
    
    let paginationHTML = `
        <div class="flex items-center gap-2 flex-wrap justify-center">
    `;
    
    paginationHTML += `<button class="pagination-btn ${currentPage === 1 ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage - 1}" ${currentPage === 1 ? 'disabled' : ''}><span class="material-icons-round">chevron_right</span></button>`;

    const pagesToShow = [];
    if (info.total_pages <= 7) {
        for (let i = 1; i <= info.total_pages; i++) pagesToShow.push(i);
    } else {
        if (info.current_page <= 4) {
            for (let i = 1; i <= 5; i++) pagesToShow.push(i);
            pagesToShow.push('...');
            pagesToShow.push(info.total_pages);
        } else if (info.current_page >= info.total_pages - 3) {
            pagesToShow.push(1);
            pagesToShow.push('...');
            for (let i = info.total_pages - 4; i <= info.total_pages; i++) pagesToShow.push(i);
        } else {
            pagesToShow.push(1);
            pagesToShow.push('...');
            for (let i = info.current_page - 2; i <= info.current_page + 2; i++) pagesToShow.push(i);
            pagesToShow.push('...');
            pagesToShow.push(info.total_pages);
        }
    }

    pagesToShow.forEach(page => {
        if (page === '...') {
            paginationHTML += `<span class="px-2 py-1">...</span>`;
        } else {
            paginationHTML += `<button class="pagination-btn ${page === currentPage ? 'bg-primary text-white' : 'hover:bg-white/10'}" data-page="${page}">${page}</button>`;
        }
    });
    
    paginationHTML += `<button class="pagination-btn ${currentPage === info.total_pages ? 'opacity-50 cursor-not-allowed' : ''}" data-page="${currentPage + 1}" ${currentPage === info.total_pages ? 'disabled' : ''}><span class="material-icons-round">chevron_left</span></button>`;
    paginationHTML += `</div>`;
    container.innerHTML = paginationHTML;
    
    // Add event listeners for pagination buttons
    container.addEventListener('click', (e) => {
        if (e.target.closest('.pagination-btn')) {
            const btn = e.target.closest('.pagination-btn');
            const page = parseInt(btn.dataset.page);
            if (!isNaN(page) && page > 0) {
                currentPage = page;
                loadNotifications(false, page);
            }
        }
    });
}

// Modal Logic
let confirmCallback = null;
function showConfirm(message, callback) {
    const modal = document.getElementById('confirm-modal');
    const backdrop = document.getElementById('confirm-modal-backdrop');
    const content = document.getElementById('confirm-modal-content');
    const msgEl = document.getElementById('confirm-message');
    if(!modal) return;
    msgEl.textContent = message;
    confirmCallback = callback;
    modal.classList.remove('hidden');
    requestAnimationFrame(() => {
        backdrop.classList.remove('opacity-0');
        content.classList.remove('scale-90', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    });
}
function hideConfirm() {
    const modal = document.getElementById('confirm-modal');
    const backdrop = document.getElementById('confirm-modal-backdrop');
    const content = document.getElementById('confirm-modal-content');
    if(!modal) return;
    backdrop.classList.add('opacity-0');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-90', 'opacity-0');
    setTimeout(() => { modal.classList.add('hidden'); confirmCallback = null; }, 300);
}

document.addEventListener('DOMContentLoaded', () => {
    const btnYes = document.getElementById('confirm-btn-yes');
    const btnNo = document.getElementById('confirm-btn-no');
    const backdrop = document.getElementById('confirm-modal-backdrop');
    if(btnYes) btnYes.addEventListener('click', () => { if (confirmCallback) confirmCallback(); hideConfirm(); });
    if(btnNo) btnNo.addEventListener('click', hideConfirm);
    if(backdrop) backdrop.addEventListener('click', hideConfirm);

    loadNotifications(false, 1);
});

fetch('api.php?action=checkExpiringProducts')
    .then(res => res.json())
    .then(() => loadNotifications(true, currentPage))
    .catch(err => console.log('Auto-check error:', err));
    
function markAsRead(id) {
    fetch('api.php?action=markNotificationRead', { 
        method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id: id}) 
    }).then(res => res.json()).then(data => { 
        if(data.success) {
            loadNotifications(true, currentPage);
            if(typeof showToast === 'function') showToast(window.__('notification_marked_read'), true);
        } else {
            if(typeof showToast === 'function') showToast(data.message || window.__('action_failed'), false);
        }
    }).catch(err => {
        if(typeof showToast === 'function') showToast(window.__('action_failed'), false);
    });
}

function markAllAsRead() {
    showConfirm(window.__('confirm_mark_all_read'), () => {
        fetch('api.php?action=markAllNotificationsRead', {method: 'POST'})
        .then(res => res.json()).then(data => { 
            if(data.success) {
                loadNotifications(false, 1);
                if(typeof showToast === 'function') showToast(window.__('all_notifications_marked_read'), true);
            } else {
                if(typeof showToast === 'function') showToast(data.message || window.__('action_failed'), false);
            }
        }).catch(err => {
            if(typeof showToast === 'function') showToast(window.__('action_failed'), false);
        });
    });
}

function deleteNotification(id) {
    showConfirm(window.__('confirm_delete_notification'), () => {
        fetch('api.php?action=deleteNotification', { 
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id: id}) 
        }).then(res => res.json()).then(data => { 
            if(data.success) {
                loadNotifications(true, currentPage);
                if(typeof showToast === 'function') showToast(window.__('notification_deleted'), true);
            } else {
                if(typeof showToast === 'function') showToast(data.message || window.__('action_failed'), false);
            }
        }).catch(err => {
            if(typeof showToast === 'function') showToast(window.__('action_failed'), false);
        });
    });
}
</script>

<div id="loading-overlay" class="fixed inset-0 bg-black/70 backdrop-blur-sm z-[9999] hidden flex items-center justify-center">
    <div class="bg-dark-surface rounded-2xl shadow-2xl p-12 border border-white/10 flex flex-col items-center gap-6">
        <div class="relative w-20 h-20">
            <div class="absolute inset-0 border-4 border-transparent border-t-primary border-r-primary rounded-full animate-spin"></div>
            <div class="absolute inset-2 border-4 border-transparent border-b-primary/50 rounded-full animate-spin" style="animation-direction: reverse;"></div>
        </div>
        <div class="text-center">
            <h3 class="text-lg font-bold text-white mb-2"><?= __('loading_text') ?></h3>
            <p id="loading-message" class="text-sm text-gray-400"><?= __('please_wait') ?></p>
        </div>
    </div>
</div>

<script>
    // دوال إدارة شاشة التحميل
    function showLoadingOverlay(message = window.__('processing_data_msg')) {
        const loadingOverlay = document.getElementById('loading-overlay');
        const loadingMessage = document.getElementById('loading-message');
        loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
    }

    function hideLoadingOverlay() {
        const loadingOverlay = document.getElementById('loading-overlay');
        loadingOverlay.classList.add('hidden');
    }
</script>




<style>
@keyframes card-flash-glow { 0%, 100% { box-shadow: 0 0 5px rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.3); } 50% { box-shadow: 0 0 20px rgba(59, 130, 246, 0.5); border-color: rgba(59, 130, 246, 0.8); background-color: rgba(59, 130, 246, 0.05); } }
.unread-flash-card { animation: card-flash-glow 1.5s infinite ease-in-out; position: relative; overflow: hidden; }
.unread-flash-card::before { content: ''; position: absolute; top: 0; right: 0; width: 4px; height: 100%; background: #3b82f6; box-shadow: 0 0 10px #3b82f6; }
@keyframes pulse-glow { 0% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0.7); } 70% { box-shadow: 0 0 0 10px rgba(59, 130, 246, 0); } 100% { box-shadow: 0 0 0 0 rgba(59, 130, 246, 0); } }
.new-notification-blink { animation: pulse-glow 2s infinite; }
.shake-icon { animation: shake 0.5s ease-in-out; display: inline-block; }
@keyframes shake { 0%, 100% { transform: rotate(0); } 25% { transform: rotate(15deg); } 75% { transform: rotate(-15deg); } }
@keyframes fadeInUp { from { opacity: 0; transform: translateY(15px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 0.4s ease forwards; }
</style>

<?php require_once 'src/footer.php'; ?>
