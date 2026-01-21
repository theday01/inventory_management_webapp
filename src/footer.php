</div> <div id="virtual-keyboard-container" class="fixed bottom-0 left-0 w-full z-[9999] transform translate-y-full transition-transform duration-300 ease-in-out hidden">
    <button id="vk-toggle-btn" class="absolute -top-12 left-4 bg-dark-surface border border-white/10 text-white p-2 rounded-t-xl shadow-lg flex items-center gap-2 hover:bg-white/5 transition-colors">
        <span class="material-icons-round">keyboard</span>
        <span class="text-xs font-bold">لوحة المفاتيح</span>
    </button>

    <div id="vk-body" class="bg-[#1a1d24] border-t border-white/10 shadow-2xl backdrop-blur-xl p-2 pb-6 select-none">
        <div class="flex justify-between items-center px-2 mb-2 border-b border-white/5 pb-2">
            <div class="flex items-center gap-2">
                <button id="vk-lang-toggle" class="px-3 py-1 bg-white/5 rounded text-xs font-bold text-gray-300 hover:text-white hover:bg-white/10 transition-colors">AR</button>
                <div class="h-4 w-[1px] bg-white/10"></div>
                <span id="vk-drag-handle" class="cursor-grab active:cursor-grabbing text-gray-500 hover:text-gray-300 material-icons-round text-sm">drag_handle</span>
            </div>
            <button id="vk-close-btn" class="text-gray-500 hover:text-red-400 transition-colors"><span class="material-icons-round">keyboard_hide</span></button>
        </div>
        
        <div id="vk-keys" class="flex flex-col gap-1.5 max-w-5xl mx-auto direction-ltr">
            </div>
    </div>
</div>

<style>
    /* Virtual Keyboard Styles */
    #virtual-keyboard-container { font-family: 'Tajawal', sans-serif; }
    #virtual-keyboard-container.visible { transform: translateY(0); }
    
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
                    countElement.textContent = data.unread_count;
                    if (data.unread_count > 0) {
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
                    countBadge.textContent = data.unread_count;
                    if (data.unread_count > 0) countBadge.classList.remove('hidden'); else countBadge.classList.add('hidden');
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

    let currentLayout = 'ar';
    let isShift = false;
    let activeInput = null;

    // Standard Arabic Layout (Reversed for RTL display)
    const layoutAr = [
        ['=','-','0','9','8','7','6','5','4','3','2','1'],
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
    // Numeric Layout
    const layoutNum = [
        ['1','2','3'],
        ['4','5','6'],
        ['7','8','9'],
        ['0','.','-']
    ];

    // --- 3. Render Function ---
    function renderKeyboard() {
        keysContainer.innerHTML = '';
        let layout;
        if (activeInput && activeInput.type === 'number') {
            layout = layoutNum;
            keysContainer.style.direction = 'ltr'; // Numbers left to right
        } else {
            layout = currentLayout === 'ar' ? layoutAr : layoutEn;
            keysContainer.style.direction = 'rtl'; // Arabic right to left
        }
        
        // Theme Application
        container.className = `fixed bottom-0 left-0 w-full z-[9999] transform translate-y-full transition-transform duration-300 ease-in-out hidden vk-size-${vkSettings.size}`;
        if(vkSettings.theme === 'system') {
            body.className = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'vk-theme-dark' : 'vk-theme-light';
        } else {
            body.className = `vk-theme-${vkSettings.theme}`;
        }
        body.classList.add('bg-[#1a1d24]', 'border-t', 'border-white/10', 'shadow-2xl', 'backdrop-blur-xl', 'p-2', 'pb-6', 'select-none'); // preserve base styles

        layout.forEach((row, rowIndex) => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'vk-row';
            
            // Add Row Specific Special Keys (Shift, Tab) - only for text layouts
            if (layout !== layoutNum) {
                if(rowIndex === 1) { // Tab
                     // Simplified: No Tab for mobile/touch optimization, just letters
                }
                if(rowIndex === 2 && currentLayout === 'en') { // Caps
                    // Check layout specifics
                }
            }
            
            row.forEach(char => {
                const keyBtn = createKey(char);
                rowDiv.appendChild(keyBtn);
            });
            
            // Append Backspace to first row
            if(rowIndex === 0) rowDiv.appendChild(createSpecialKey('backspace', 'backspace'));
            // Append Enter to second row
            if(rowIndex === 1) rowDiv.appendChild(createSpecialKey('keyboard_return', 'enter'));
            
            keysContainer.appendChild(rowDiv);
        });

        // Bottom Row (Space, Shift, etc) - only for text layouts
        const bottomRow = document.createElement('div');
        bottomRow.className = 'vk-row';
        
        if (layout === layoutNum) {
            // For numeric, just space and backspace or something simple
            bottomRow.appendChild(createSpecialKey('space_bar', 'space'));
            bottomRow.appendChild(createSpecialKey('keyboard_return', 'enter'));
        } else {
            bottomRow.appendChild(createSpecialKey('arrow_upward', 'shift'));
            bottomRow.appendChild(createSpecialKey('space_bar', 'space'));
            bottomRow.appendChild(createSpecialKey('language', 'lang'));
        }
        
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
            if (activeInput && activeInput.type === 'number') return; // No shift for numbers
            isShift = !isShift;
            renderKeyboard();
        }
        
        if(type === 'lang') {
            if (activeInput && activeInput.type === 'number') return; // No lang switch for numbers
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
        }, 10);
    }

    function hideKeyboard() {
        container.classList.remove('visible');
        setTimeout(() => {
            container.classList.add('hidden');
            toggleBtn.style.opacity = '1';
        }, 300);
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

    closeBtn.addEventListener('click', hideKeyboard);

    // Auto-Attach to Inputs
    document.addEventListener('focusin', function(e) {
        if (!vkSettings.autoSearch) return;

        const target = e.target;
        // Check if it's a text input
        if (target.tagName === 'INPUT' && (target.type === 'text' || target.type === 'search' || target.type === 'email' || target.type === 'number')) {
            // Only auto-show if configured, otherwise user must click toggle
            activeInput = target;
            renderKeyboard(); // Re-render to match input type
            
            // Specific check for search inputs or general Inputs
            if(vkSettings.autoSearch) {
                showKeyboard();
            }
        } else if (target.tagName === 'TEXTAREA') {
            activeInput = target;
            renderKeyboard(); // Re-render for text
            if(vkSettings.autoSearch) showKeyboard();
        }
    });

    // Language Toggle Button (Top Bar)
    langBtn.addEventListener('click', () => {
        currentLayout = currentLayout === 'ar' ? 'en' : 'ar';
        langBtn.textContent = currentLayout.toUpperCase();
        renderKeyboard();
    });

    // Initial Render
    renderKeyboard();
    container.classList.remove('hidden'); // Initially loaded in DOM but translated down
    setTimeout(() => { container.classList.remove('hidden'); }, 100);

});
</script>
</body>
</html>
