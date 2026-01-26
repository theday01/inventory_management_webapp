<div id="virtual-keyboard-container" class="fixed bottom-0 left-0 w-4/5 z-[9999] transform translate-y-full transition-transform duration-300 ease-in-out hidden left-1/10">
    <button id="vk-toggle-btn" class="absolute -top-12 left-4 bg-dark-surface border border-white/10 text-white p-2 rounded-t-xl shadow-lg flex items-center gap-2 hover:bg-white/5 transition-colors">
        <span class="material-icons-round">keyboard</span>
        <span class="text-xs font-bold">لوحة المفاتيح</span>
    </button>

    <div id="vk-body" class="bg-transparent border-t border-white/10 shadow-2xl backdrop-blur-xl p-2 pb-6 select-none">
        <div class="flex justify-between items-center px-2 mb-2 border-b border-white/5 pb-2">
            <div class="flex items-center gap-2">
                <button id="vk-lang-toggle" class="px-3 py-1 bg-white/5 rounded text-xs font-bold text-gray-300 hover:text-white hover:bg-white/10 transition-colors">AR</button>
                <div class="h-4 w-[1px] bg-white/10"></div>
                <span id="vk-drag-handle" class="cursor-grab active:cursor-grabbing text-gray-500 hover:text-gray-300 material-icons-round text-sm">drag_handle</span>
            </div>
            <div class="flex items-center gap-2">
                <button id="vk-pin-btn" class="text-gray-500 hover:text-yellow-400 transition-colors p-1 rounded hover:bg-white/5" title="تثبيت لوحة المفاتيح"><span class="material-icons-round text-sm">push_pin</span></button>
                <button id="vk-close-btn" class="text-gray-500 hover:text-red-400 transition-colors p-1 rounded hover:bg-white/5"><span class="material-icons-round text-sm">keyboard_hide</span></button>
            </div>
        </div>
        
        <div id="vk-keys" class="flex flex-col gap-1.5 max-w-5xl mx-auto direction-ltr"></div>
    </div>
</div>

<style>
    /* Virtual Keyboard Styles */
    #virtual-keyboard-container { 
        font-family: 'Tajawal', sans-serif; 
        /* Ensure keyboard stays above all content */
        z-index: 9999;
        width: 70% !important;
        left: 15% !important;
        right: auto !important;
    }
    #virtual-keyboard-container.visible { 
        transform: translateY(0); 
    }
    
    #vk-body {
        width: 55% !important;
        margin: 0 auto !important;
        box-sizing: border-box !important;
    }
    
    /* Add smooth transition for content padding */
    body, main, #settings-content-area {
        transition: padding-bottom 0.3s ease-out;
    }
    
    /* Highlight active input when keyboard is visible */
    input.vk-active-input, textarea.vk-active-input {
        box-shadow: 0 0 0 3px rgba(var(--primary-rgb), 0.3) !important;
        border-color: var(--primary-color) !important;
    }
    
    /* Toast Animation */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(-20px);
        }
    }
    
    .animate-fade-in {
        animation: fadeIn 0.3s ease-out;
    }
    
    
        /* ================================================= */
    /* بداية التعديل: إصلاح اتجاه اللوحة */
    /* ================================================= */
    #vk-keys {
        display: flex;
        flex-direction: column;
        gap: 6px;
        max-width: 64rem; /* max-w-5xl equivalent */
        margin-left: auto;
        margin-right: auto;
        
        /* اتجاه يُحدد ديناميكياً */
    }
    .vk-row { display: flex; justify-content: center; gap: 6px; width: 100%; }
    
    .vk-key {
        position: relative;
        display: flex;
        justify-content: center;
        align-items: center;
        border-radius: 6px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.1s;
        box-shadow: 0 2px 0 rgba(0,0,0,0.2);
        user-select: none;
        flex: 1;
        min-width: 0;
        
        /* لضمان أن الحرف داخل الزر يبقى في المنتصف تماماً */
        direction: ltr; 
    }
    
    .vk-key:active { transform: translateY(2px); box-shadow: none; }
    
    /* Themes */
    .vk-theme-dark .vk-key { background-color: #374151; color: white; border-top: 1px solid rgba(255,255,255,0.1); }
    .vk-theme-dark .vk-key:hover { background-color: #4B5563; }
    .vk-theme-dark .vk-key.vk-special { background-color: #1F2937; }
    
    .vk-theme-light .vk-key { background-color: #FFFFFF; color: #1F2937; border: 1px solid #E5E7EB; }
    .vk-theme-light .vk-key:hover { background-color: #F3F4F6; }
    .vk-theme-light .vk-key.vk-special { background-color: #E5E7EB; }
    .vk-theme-light #vk-body { background-color: #F9FAFB; color: #1F2937; border-top: 1px solid #D1D5DB; }
    .vk-theme-light #vk-toggle-btn { background-color: #F3F4F6; color: #1F2937; border-color: #D1D5DB; }

    /* Sizes */
    .vk-size-small .vk-key { height: 35px; font-size: 14px; }
    .vk-size-medium .vk-key { height: 45px; font-size: 16px; }
    .vk-size-large .vk-key { height: 60px; font-size: 20px; }

    /* Special Key Widths */
    .vk-key-space { flex: 6; }
    .vk-key-enter { flex: 2; background-color: var(--primary-color) !important; color: white !important; }
    .vk-key-backspace { flex: 1.5; }
    .vk-key-shift { flex: 1.5; }
    .vk-key-tab { flex: 1.2; }
    
    /* Pin Button Style */
    #vk-pin-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
    }
    
    #vk-pin-btn.text-yellow-400 span {
        color: #facc15;
    }
    
    /* Fast Toggle Button Appearance */
    #vk-toggle-btn {
        transition: opacity 0.2s ease-in-out, visibility 0.2s ease-in-out;
        will-change: opacity, visibility;
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // --- 1. Settings Loading ---
    // In a real app, these would come from PHP injection into a global var
    const vkSettings = {
        enabled: <?php 
            $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name='virtualKeyboardEnabled'"); 
            echo ($res && $res->num_rows > 0 && $res->fetch_assoc()['setting_value'] == '1') ? 'true' : 'false'; 
        ?>,
        theme: '<?php 
            $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name='virtualKeyboardTheme'"); 
            echo ($res && $res->num_rows > 0) ? $res->fetch_assoc()['setting_value'] : 'system'; 
        ?>',
        size: '<?php 
            $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name='virtualKeyboardSize'"); 
            echo ($res && $res->num_rows > 0) ? $res->fetch_assoc()['setting_value'] : 'medium'; 
        ?>',
        vibrate: <?php 
            $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name='virtualKeyboardVibrate'"); 
            echo ($res && $res->num_rows > 0 && $res->fetch_assoc()['setting_value'] == '1') ? 'true' : 'false'; 
        ?>,
        autoSearch: <?php 
            $res = $conn->query("SELECT setting_value FROM settings WHERE setting_name='virtualKeyboardAutoSearch'"); 
            echo ($res && $res->num_rows > 0 && $res->fetch_assoc()['setting_value'] == '1') ? 'true' : 'false'; 
        ?>
    };
    
    let lastGlobalUnreadCount = null;
    let globalSoundEnabled = false;
    let globalAudioCtx = null;
    const globalNotifSound = new Audio("data:audio/mp3;base64,//uQxAAAAANIAAAAABxBTUUzLjEwMAr///8=");
    document.addEventListener('click', function initAudio() {
        if (!globalSoundEnabled) {
            try { globalNotifSound.load(); } catch(e) {}
            globalSoundEnabled = true;
        }
    }, { once: true });
    function globalGentleChime() {
        try {
            if (!globalAudioCtx) globalAudioCtx = new (window.AudioContext || window.webkitAudioContext)();
            if (globalAudioCtx.state === 'suspended') globalAudioCtx.resume();
            const osc = globalAudioCtx.createOscillator();
            const gain = globalAudioCtx.createGain();
            osc.type = 'sine';
            osc.frequency.value = 660;
            gain.gain.setValueAtTime(0.0, globalAudioCtx.currentTime);
            gain.gain.linearRampToValueAtTime(0.15, globalAudioCtx.currentTime + 0.02);
            gain.gain.exponentialRampToValueAtTime(0.001, globalAudioCtx.currentTime + 0.6);
            osc.connect(gain); gain.connect(globalAudioCtx.destination);
            osc.start(); osc.stop(globalAudioCtx.currentTime + 0.6);
            const osc2 = globalAudioCtx.createOscillator();
            const gain2 = globalAudioCtx.createGain();
            osc2.type = 'sine';
            osc2.frequency.value = 880;
            gain2.gain.setValueAtTime(0.0, globalAudioCtx.currentTime + 0.35);
            gain2.gain.linearRampToValueAtTime(0.12, globalAudioCtx.currentTime + 0.38);
            gain2.gain.exponentialRampToValueAtTime(0.001, globalAudioCtx.currentTime + 0.95);
            osc2.connect(gain2); gain2.connect(globalAudioCtx.destination);
            osc2.start(globalAudioCtx.currentTime + 0.35); osc2.stop(globalAudioCtx.currentTime + 0.95);
        } catch (e) {
            try {
                globalNotifSound.currentTime = 0;
                globalNotifSound.volume = 0.2;
                globalNotifSound.play().catch(()=>{});
            } catch(_e) {}
        }
    }
    function updateNotificationCount() {
        fetch('api.php?action=getNotifications')
            .then(response => response.json())
            .then(data => {
                const currentUnread = parseInt(data.unread_count);
                if (lastGlobalUnreadCount !== null && currentUnread > lastGlobalUnreadCount && currentUnread !== 0) {
                    globalGentleChime();
                }
                lastGlobalUnreadCount = currentUnread;
                const countElement = document.getElementById('notification-count');
                const countBadge = document.getElementById('notification-count-badge');
                if (countElement) {
                    countElement.textContent = currentUnread > 50 ? '*50' : currentUnread;
                    if (currentUnread > 0) {
                        countElement.classList.remove('bg-green-500'); 
                        countElement.classList.add('bg-red-500'); 
                        countElement.style.display = 'inline-flex';
                    } else {
                        countElement.classList.remove('bg-red-500'); 
                        countElement.classList.add('bg-green-500'); 
                        countElement.style.display = 'inline-flex';
                    }
                }
                if (countBadge) {
                    countBadge.textContent = currentUnread > 50 ? '*50' : currentUnread;
                    if (currentUnread > 0) countBadge.classList.remove('hidden'); else countBadge.classList.add('hidden');
                }
            });
    }
    updateNotificationCount();
    setInterval(updateNotificationCount, 30000);

    if (!vkSettings.enabled) return;

    // --- 2. State & Layouts ---
    const container = document.getElementById('virtual-keyboard-container');
    const body = document.getElementById('vk-body');
    const keysContainer = document.getElementById('vk-keys');
    const toggleBtn = document.getElementById('vk-toggle-btn');
    const closeBtn = document.getElementById('vk-close-btn');
    const langBtn = document.getElementById('vk-lang-toggle');
    const pinBtn = document.getElementById('vk-pin-btn');

    let currentLayout = 'ar';
    let isShift = false;
    let activeInput = null;
    let isPinned = false;

    // Standard Arabic Layout (Reversed for RTL display)
    const layoutAr = [
        ['1','2','3','4','5','6','7','8','9','0','-','='], // الأرقام بالترتيب الطبيعي
        ['\\','د','ج','ح','خ','ه','ع','غ','ف','ق','ث','ص','ض'],
        ['ط','ك','م','ن','ت','ا','ل','ب','ي','س','ش'],
        ['ظ','ز','و','ة','ى','لا','ر','ؤ','أ','ء','ئ','ذ']
    ];
    // QWERTY Layout
    const layoutEn = [
        ['1','2','3','4','5','6','7','8','9','0','-','='],
        ['q','w','e','r','t','y','u','i','o','p','[',']'],
        ['a','s','d','f','g','h','j','k','l',';','\''],
        ['z','x','c','v','b','n','m',',','.','/']
    ];
    // --- 3. Render Function ---
    function renderKeyboard() {
        keysContainer.innerHTML = '';
        let layout;
        layout = currentLayout === 'ar' ? layoutAr : layoutEn;

        keysContainer.style.direction = currentLayout === 'ar' ? 'rtl' : 'ltr';
        
        // Theme Application - preserve visible state
        const wasVisible = container.classList.contains('visible');
        let newClassName = `fixed bottom-0 left-0 w-full z-[9999] transform translate-y-full transition-transform duration-300 ease-in-out vk-size-${vkSettings.size}`;
        if (!wasVisible) {
            newClassName += ' hidden';
        }
        container.className = newClassName;
        if (wasVisible) {
            container.classList.add('visible');
        }
        
        if(vkSettings.theme === 'system') {
            body.className = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'vk-theme-dark' : 'vk-theme-light';
        } else {
            body.className = `vk-theme-${vkSettings.theme}`;
        }
        body.classList.add('bg-[#1a1d24]', 'border-t', 'border-white/10', 'shadow-2xl', 'backdrop-blur-xl', 'p-2', 'pb-6', 'select-none'); // preserve base styles

        layout.forEach((row, rowIndex) => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'vk-row';
            
            row.forEach(char => {
                const keyBtn = createKey(char);
                rowDiv.appendChild(keyBtn);
            });
            
            // Append Backspace to first row for all layouts
            if(rowIndex === 0) rowDiv.appendChild(createSpecialKey('backspace', 'backspace'));
            // Append Enter to second row for all layouts
            if(rowIndex === 1) rowDiv.appendChild(createSpecialKey('keyboard_return', 'enter'));
            
            keysContainer.appendChild(rowDiv);
        });

        // Bottom Row (Space, Shift, etc)
        const bottomRow = document.createElement('div');
        bottomRow.className = 'vk-row';
        
        bottomRow.appendChild(createSpecialKey('arrow_upward', 'shift'));
        bottomRow.appendChild(createSpecialKey('space_bar', 'space'));
        bottomRow.appendChild(createSpecialKey('language', 'lang'));
        
        keysContainer.appendChild(bottomRow);
    }

    function createKey(char) {
        const btn = document.createElement('div');
        btn.className = 'vk-key';
        // Handle Shift Case - only for text layouts
        let displayChar = char;
        if (activeInput && activeInput.type !== 'number' && currentLayout === 'en' && isShift) displayChar = char.toUpperCase();
        
        btn.textContent = displayChar;
        btn.onclick = (e) => handleKeyPress(e, displayChar);
        return btn;
    }

    function createSpecialKey(icon, type) {
        const btn = document.createElement('div');
        btn.className = `vk-key vk-special vk-key-${type}`;
        if(type === 'shift' && isShift) btn.style.backgroundColor = 'var(--primary-color)';
        
        const span = document.createElement('span');
        span.className = 'material-icons-round text-sm';
        span.textContent = icon;
        
        if(type === 'space') { span.textContent = ''; }
        if(type === 'lang') { span.textContent = currentLayout.toUpperCase(); span.className = 'font-bold text-xs'; }
        
        btn.appendChild(span);
        btn.onclick = (e) => handleSpecialKey(e, type);
        return btn;
    }

    // --- 4. Event Handling ---
    function handleKeyPress(e, char) {
        e.preventDefault();
        e.stopPropagation();
        
        if(vkSettings.vibrate) navigator.vibrate(20);
        
        if(activeInput) {
            // Check if input is numeric and restrict to numbers only
            if (activeInput.type === 'number' || activeInput.inputMode === 'numeric') {
                if (!/[0-9٠-٩.]/.test(char)) return;
            }
            
            const start = activeInput.selectionStart;
            const end = activeInput.selectionEnd;
            const text = activeInput.value;
            
            activeInput.value = text.substring(0, start) + char + text.substring(end);
            activeInput.selectionStart = activeInput.selectionEnd = start + 1;
            
            // Trigger input event for frameworks/listeners
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            activeInput.focus();
        }
        
        // Auto-disable shift after one char if desired, but standard keyboard keeps caps until toggle usually. 
        // Let's toggle off shift if it was a single press logic, but for simplicity, toggle logic is manual.
    }

    function handleSpecialKey(e, type) {
        e.preventDefault();
        e.stopPropagation();
        if(vkSettings.vibrate) navigator.vibrate(30);

        if(type === 'backspace' && activeInput) {
            const start = activeInput.selectionStart;
            const end = activeInput.selectionEnd;
            const text = activeInput.value;
            
            if(start === end && start > 0) {
                activeInput.value = text.substring(0, start - 1) + text.substring(end);
                activeInput.selectionStart = activeInput.selectionEnd = start - 1;
            } else {
                activeInput.value = text.substring(0, start) + text.substring(end);
                activeInput.selectionStart = activeInput.selectionEnd = start;
            }
            activeInput.dispatchEvent(new Event('input', { bubbles: true }));
            activeInput.focus();
        }
        
        if(type === 'enter' && activeInput) {
            // Trigger Submit or New Line
            activeInput.dispatchEvent(new KeyboardEvent('keydown', {'key': 'Enter'}));
            // If it's a search box, try finding the submit button
            const form = activeInput.closest('form');
            if(form) form.dispatchEvent(new Event('submit'));
        }
        
        if(type === 'space' && activeInput) {
            handleKeyPress(e, ' ');
        }
        
        if(type === 'shift') {
            if (activeInput && (activeInput.type === 'number' || activeInput.inputMode === 'numeric')) return; // No shift for numbers
            isShift = !isShift;
            renderKeyboard();
        }
        
        if(type === 'lang') {
            if (activeInput && (activeInput.type === 'number' || activeInput.inputMode === 'numeric')) return; // No lang switch for numbers
            currentLayout = currentLayout === 'ar' ? 'en' : 'ar';
            langBtn.textContent = currentLayout.toUpperCase();
            renderKeyboard();
        }
    }

    // --- 5. Visibility Logic ---
    function showKeyboard() {
        container.classList.remove('hidden');
        // Small delay to allow display block to apply before transition
        setTimeout(() => {
            container.classList.add('visible');
            toggleBtn.style.opacity = '0';
            toggleBtn.style.pointerEvents = 'none';
            toggleBtn.style.visibility = 'hidden';
            
            // Add bottom padding to body/main content to prevent overlap
            addKeyboardPadding();
        }, 10);
    }

    function hideKeyboard() {
        if (isPinned) {
            // إذا كانت اللوحة مثبتة، لا تغلقها
            return;
        }
        
        container.classList.remove('visible');
        setTimeout(() => {
            container.classList.add('hidden');
            toggleBtn.style.opacity = '1';
            toggleBtn.style.pointerEvents = 'auto';
            toggleBtn.style.visibility = 'visible';
            
            // Remove bottom padding
            removeKeyboardPadding();
        }, 300);
    }

    // Add dynamic padding to prevent content being hidden
    function addKeyboardPadding() {
        // Removed padding addition to prevent issues
    }

    function removeKeyboardPadding() {
        const mainContent = document.getElementById('settings-content-area') || 
                          document.querySelector('main');
        
        if (mainContent) {
            mainContent.style.paddingBottom = '';
        }
    }

    // Smart scroll to keep input visible above keyboard
    function scrollToInput(input) {
        if (!input) return;
        
        setTimeout(() => {
            const keyboardHeight = body.offsetHeight;
            const inputRect = input.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            
            // Calculate if input is hidden behind keyboard
            const inputBottom = inputRect.bottom;
            const keyboardTop = viewportHeight - keyboardHeight;
            
            if (inputBottom > keyboardTop - 20) {
                // Input is hidden, scroll it into view
                const scrollTarget = inputRect.top + window.scrollY - 20;
                
                // Smooth scroll to position
                const scrollableParent = findScrollableParent(input);
                if (scrollableParent && scrollableParent !== document.body && scrollableParent !== document.documentElement) {
                    // Scroll within parent container
                    scrollableParent.scrollTo({
                        top: scrollableParent.scrollTop + (inputBottom - keyboardTop + 40),
                        behavior: 'smooth'
                    });
                } else {
                    // Scroll entire window
                    window.scrollTo({
                        top: scrollTarget,
                        behavior: 'smooth'
                    });
                }
            }
        }, 350); // Wait for keyboard animation
    }

    // Helper: Find scrollable parent
    function findScrollableParent(element) {
        let parent = element.parentElement;
        while (parent) {
            const overflow = window.getComputedStyle(parent).overflow;
            const overflowY = window.getComputedStyle(parent).overflowY;
            if ((overflow === 'auto' || overflow === 'scroll' || overflowY === 'auto' || overflowY === 'scroll') 
                && parent.scrollHeight > parent.clientHeight) {
                return parent;
            }
            parent = parent.parentElement;
        }
        return null;
    }
    // Toggle Button
    toggleBtn.addEventListener('click', () => {
        // الاعتماد على حالة الظهور الفعلية بدلاً من وجود/عدم وجود hidden
        if (container.classList.contains('visible')) {
            hideKeyboard();
        } else {
            showKeyboard();
        }
    });

    closeBtn.addEventListener('click', () => {
        hideKeyboard();
        // تأكد من أن الزر يظهر بسرعة
        setTimeout(() => {
            toggleBtn.style.opacity = '1';
            toggleBtn.style.pointerEvents = 'auto';
            toggleBtn.style.visibility = 'visible';
        }, 100);
    });

    // دالة showToast لعرض الرسائل
    function showToast(type, message) {
        const toast = document.createElement('div');
        toast.className = `fixed bottom-24 left-4 px-4 py-3 rounded-lg font-bold text-white text-sm z-[10000] animate-fade-in flex items-center gap-2 shadow-lg`;
        
        // تحديد النمط بناءً على النوع
        if (type === 'success') {
            toast.classList.add('bg-green-500/90', 'border', 'border-green-400/30');
        } else if (type === 'info') {
            toast.classList.add('bg-blue-500/90', 'border', 'border-blue-400/30');
        } else if (type === 'error') {
            toast.classList.add('bg-red-500/90', 'border', 'border-red-400/30');
        }
        
        toast.textContent = message;
        document.body.appendChild(toast);
        
        // إزالة الرسالة بعد 3 ثوانٍ
        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease-out';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    // Auto-Attach to Inputs
    // Auto-Attach to Inputs
    document.addEventListener('focusin', function(e) {
        if (!vkSettings.autoSearch) return;

        const target = e.target;
        // Check if it's a text input
        if (target.tagName === 'INPUT' && (target.type === 'text' || target.type === 'search' || target.type === 'email' || target.type === 'number')) {
            activeInput = target;
            
            // تحديد الاتجاه بناءً على نوع الحقل بشكل صحيح
            if (target.type === 'number' || target.type === 'email') {
                // الأرقام والإيميل دائماً من اليسار لليمين
                target.setAttribute('dir', 'ltr');
                target.style.textAlign = 'left';
            } else if (target.type === 'text' || target.type === 'search') {
                // النصوص العربية من اليمين لليسار
                target.setAttribute('dir', 'rtl');
                target.style.textAlign = 'right';
            }
            
            renderKeyboard(); // Re-render to match input type
            
            if(vkSettings.autoSearch) {
                showKeyboard();
            }
        } else if (target.tagName === 'TEXTAREA') {
            activeInput = target;
            activeInput.setAttribute('dir', 'rtl');
            activeInput.style.textAlign = 'right';
            renderKeyboard();
            if(vkSettings.autoSearch) showKeyboard();
        }
    });

    // Language Toggle Button (Top Bar)
    langBtn.addEventListener('click', () => {
        currentLayout = currentLayout === 'ar' ? 'en' : 'ar';
        langBtn.textContent = currentLayout.toUpperCase();
        renderKeyboard();
    });

    // Pin Button Logic
    pinBtn.addEventListener('click', () => {
        isPinned = !isPinned;
        
        if (isPinned) {
            // حفظ حالة التثبيت في localStorage
            localStorage.setItem('vk-pinned', 'true');
            pinBtn.classList.add('text-yellow-400');
            pinBtn.innerHTML = '<span class="material-icons-round text-sm">push_pin</span>';
            
            // عرض رسالة نجاح
            showToast('success', '📌 تم تثبيت لوحة المفاتيح');
            
            // التأكد من أن اللوحة مرئية عند التثبيت
            showKeyboard();
        } else {
            // إزالة حالة التثبيت
            localStorage.removeItem('vk-pinned');
            pinBtn.classList.remove('text-yellow-400');
            pinBtn.innerHTML = '<span class="material-icons-round text-sm">push_pin</span>';
            
            // عرض رسالة
            showToast('info', 'تم إلغاء تثبيت لوحة المفاتيح');
        }
    });

    // Initial Render
    renderKeyboard();
    container.classList.remove('hidden'); // Initially loaded in DOM but translated down
    setTimeout(() => { container.classList.remove('hidden'); }, 100);

    // استعادة حالة التثبيت عند تحميل الصفحة
    if (localStorage.getItem('vk-pinned') === 'true') {
        isPinned = true;
        pinBtn.classList.add('text-yellow-400');
        showKeyboard();
        // تأكد من أن الزر مخفي عند التثبيت
        toggleBtn.style.opacity = '0';
        toggleBtn.style.pointerEvents = 'none';
        toggleBtn.style.visibility = 'hidden';
    } else {
        // تأكد من أن الزر مرئي عند البداية إذا لم تكن اللوحة مثبتة
        toggleBtn.style.opacity = '1';
        toggleBtn.style.pointerEvents = 'auto';
        toggleBtn.style.visibility = 'visible';
    }

    // Re-adjust scroll on window resize
    let resizeTimeout;
    window.addEventListener('resize', () => {
        clearTimeout(resizeTimeout);
        resizeTimeout = setTimeout(() => {
            if (container.classList.contains('visible') && activeInput) {
                addKeyboardPadding();
                scrollToInput(activeInput);
            }
        }, 200);
    });

    // Keep input visible when scrolling (optional - can be removed if too aggressive)
    let scrollTimeout;
    window.addEventListener('scroll', () => {
        if (!container.classList.contains('visible') || !activeInput) return;
        
        clearTimeout(scrollTimeout);
        scrollTimeout = setTimeout(() => {
            const keyboardHeight = body.offsetHeight;
            const inputRect = activeInput.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const keyboardTop = viewportHeight - keyboardHeight;
            
            // If input scrolled behind keyboard, blur it
            if (inputRect.bottom > keyboardTop + 50) {
                // Input is way behind keyboard - might want to hide keyboard
                // (optional behavior)
            }
        }, 100);
    }, { passive: true });

});

</script>
</body>
</html>