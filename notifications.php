<?php
$page_title = 'الإشعارات';
$current_page = 'notifications.php';
require_once 'src/header.php';
require_once 'src/sidebar.php';
?>

<main class="flex-1 flex flex-col relative overflow-hidden">
    <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary/5 rounded-full blur-[100px] pointer-events-none"></div>
    <div class="absolute bottom-0 left-0 w-[500px] h-[500px] bg-accent/5 rounded-full blur-[100px] pointer-events-none"></div>

    <header class="h-20 bg-dark-surface/50 backdrop-blur-md border-b border-white/5 flex items-center justify-between px-8 relative z-10 shrink-0">
        <div class="flex items-center gap-4">
            <h2 class="text-xl font-bold text-white">الإشعارات</h2>
            
            <div class="flex bg-white/5 p-1 rounded-xl border border-white/5 ml-4">
                <button onclick="setFilter('all')" id="filter-all" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all bg-primary text-white">الكل</button>
                <button onclick="setFilter('unread')" id="filter-unread" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white">غير مقروء</button>
                <button onclick="setFilter('read')" id="filter-read" class="px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white">مقروء</button>
            </div>
        </div>
        
        <div class="flex items-center gap-3">
            <span id="auto-refresh-indicator" class="text-xs text-primary/70 hidden transition-opacity">جاري التحديث...</span>
            
            <button onclick="markAllAsRead()" id="main-notification-btn" class="relative p-2 bg-white/5 hover:bg-green-500/20 rounded-full transition-all text-white" title="تحديد الكل كمقروء">
                <span class="material-icons-round">done_all</span>
                <span id="unread-dot" class="absolute top-0 right-0 w-3 h-3 bg-red-500 rounded-full border-2 border-dark-surface hidden"></span>
            </button>

            <button onclick="loadNotifications(false)" class="p-2 bg-white/5 hover:bg-white/10 rounded-full transition-colors text-white" title="تحديث القائمة">
                <span class="material-icons-round">refresh</span>
            </button>
        </div>
    </header>

    <div class="flex-1 overflow-y-auto p-8 relative z-10">
        <div id="loading-screen" class="flex flex-col items-center justify-center h-full min-h-[300px]">
            <div class="relative w-16 h-16">
                <div class="absolute w-full h-full rounded-full border-4 border-white/5"></div>
                <div class="absolute w-full h-full rounded-full border-4 border-primary border-t-transparent animate-spin"></div>
            </div>
            <p class="mt-6 text-gray-400 text-sm animate-pulse">جاري جلب الإشعارات...</p>
        </div>

        <div id="notifications-container" class="space-y-4 hidden opacity-0 transition-opacity duration-300"></div>
        <div id="pagination-container" class="mt-8 flex justify-center items-center gap-2 pb-10">
        </div>
    </div>

    <!-- Custom Confirmation Modal -->
    <div id="confirm-modal" class="fixed inset-0 z-50 hidden" style="z-index: 100;">
        <!-- Backdrop -->
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0 duration-300" id="confirm-modal-backdrop"></div>
        
        <!-- Modal Content -->
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-full max-w-sm p-6 bg-dark-surface border border-white/10 rounded-2xl shadow-2xl transform scale-90 opacity-0 transition-all duration-300" id="confirm-modal-content">
            <div class="text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-full flex items-center justify-center mx-auto mb-4 ring-4 ring-primary/5">
                    <span class="material-icons-round text-3xl text-primary">priority_high</span>
                </div>
                <h3 class="text-xl font-bold text-white mb-2" id="confirm-title">تأكيد الإجراء</h3>
                <p class="text-gray-400 mb-6 text-sm leading-relaxed" id="confirm-message">هل أنت متأكد من المتابعة؟</p>
                
                <div class="flex items-center gap-3 justify-center">
                    <button id="confirm-btn-yes" class="px-6 py-2.5 bg-primary hover:bg-primary-hover text-white rounded-xl font-bold transition-all shadow-lg shadow-primary/20 active:scale-95">نعم، تأكيد</button>
                    <button id="confirm-btn-no" class="px-6 py-2.5 bg-white/5 hover:bg-white/10 text-gray-300 rounded-xl font-medium transition-all active:scale-95 border border-white/5">إلغاء</button>
                </div>
            </div>
        </div>
    </div>
</main>


<script>
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
            alert("تم تفعيل الصوت بنجاح!"); 
        })
        .catch(error => {
            console.error("Audio playback failed:", error);
            alert("فشل تشغيل الصوت. يرجى التحقق من إعدادات المتصفح للسماح للصوت.");
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
                ? "px-4 py-1.5 rounded-lg text-sm font-medium transition-all bg-primary text-white shadow-lg shadow-primary/20" 
                : "px-4 py-1.5 rounded-lg text-sm font-medium transition-all text-gray-400 hover:text-white bg-white/5";
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
                    sidebarBadge.textContent = totalUnread;
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
            
            container.innerHTML += `
                <div class="bg-dark-surface/60 border ${flashClass} ${isUnread ? 'border-primary/30' : 'border-white/5'} rounded-2xl p-6 transition-all hover:bg-dark-surface/80 animate-fade-in-up" style="animation-delay: ${delay}ms;">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div class="flex items-start space-x-4 space-x-reverse">
                            <div class="p-3 rounded-full ${isUnread ? 'bg-primary/20' : 'bg-gray-700/30'} shrink-0">
                                <span class="material-icons-round ${isUnread ? 'text-primary shake-icon' : 'text-gray-400'}">
                                    ${isUnread ? 'notifications_active' : 'notifications'}
                                </span>
                            </div>
                            <div>
                                <p class="text-white ${isUnread ? 'font-bold' : ''}">${notification.message}</p>
                                <p class="text-gray-400 text-sm mt-1 flex items-center gap-1">
                                    <span class="material-icons-round text-xs">schedule</span>
                                    ${formatArabicDate(notification.created_at)}
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 self-end md:self-center">
                            ${isUnread ? `
                                <button onclick="markAsRead(${notification.id})" class="p-2 text-green-400 bg-green-500/10 hover:bg-green-500/20 rounded-lg border border-green-500/20 transition-all" title="تحديد كمقروء">
                                    <span class="material-icons-round text-base">done</span>
                                </button>
                            ` : ''}
                            <button onclick="deleteNotification(${notification.id})" class="p-2 text-red-400 bg-red-500/10 hover:bg-red-500/20 rounded-lg border border-red-500/20 transition-all" title="حذف">
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
                <p>لا توجد إشعارات في هذه القائمة</p>
            </div>`;
    }
}

function renderPagination(info) {
    const container = document.getElementById('pagination-container');
    if (!info || info.total_pages <= 1) { 
        container.innerHTML = ''; 
        return; 
    }
    
    let html = `<button onclick="loadNotifications(false, ${info.current_page - 1})" ${info.current_page === 1 ? 'disabled' : ''} class="p-2 rounded-lg bg-white/5 text-white disabled:opacity-20 hover:bg-white/10 transition-colors"><span class="material-icons-round">chevron_right</span></button>`;
    
    for (let i = 1; i <= info.total_pages; i++) {
        if (i === 1 || i === info.total_pages || (i >= info.current_page - 1 && i <= info.current_page + 1)) {
            const isActive = i === info.current_page;
            html += `<button onclick="loadNotifications(false, ${i})" class="w-10 h-10 rounded-lg border transition-all ${isActive ? 'bg-primary border-primary text-white shadow-lg shadow-primary/20 scale-110' : 'bg-white/5 border-white/5 text-gray-400 hover:bg-white/10'}">${i}</button>`;
        } else if (i === info.current_page - 2 || i === info.current_page + 2) {
            html += `<span class="text-gray-600 px-1">...</span>`;
        }
    }
    
    html += `<button onclick="loadNotifications(false, ${info.current_page + 1})" ${info.current_page === info.total_pages ? 'disabled' : ''} class="p-2 rounded-lg bg-white/5 text-white disabled:opacity-20 hover:bg-white/10 transition-colors"><span class="material-icons-round">chevron_left</span></button>`;
    
    container.innerHTML = html;
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
    }).then(res => res.json()).then(data => { if(data.success) loadNotifications(true, currentPage); });
}

function markAllAsRead() {
    showConfirm('هل أنت متأكد من تحديد جميع الإشعارات كمقروءة؟', () => {
        fetch('api.php?action=markAllNotificationsRead', {method: 'POST'})
        .then(res => res.json()).then(data => { if(data.success) loadNotifications(false, 1); });
    });
}

function deleteNotification(id) {
    showConfirm('هل أنت متأكد من حذف هذا الإشعار نهائياً؟', () => {
        fetch('api.php?action=deleteNotification', { 
            method: 'POST', headers: {'Content-Type': 'application/json'}, body: JSON.stringify({id: id}) 
        }).then(res => res.json()).then(data => { if(data.success) loadNotifications(true, currentPage); });
    });
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
