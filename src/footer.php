<?php
// Fetch End of Day Settings & Keyboard Settings
$endDaySettings = [
    'enabled' => '1',
    'start' => '05:00',
    'end' => '00:00',
    'keyboard_enabled' => '0'
];
if (isset($conn)) {
    $res = $conn->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('day_start_time', 'day_end_time', 'end_day_reminder_enabled', 'keyboard_enabled')");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['setting_name'] == 'end_day_reminder_enabled') $endDaySettings['enabled'] = $row['setting_value'];
            if ($row['setting_name'] == 'day_start_time') $endDaySettings['start'] = $row['setting_value'];
            if ($row['setting_name'] == 'day_end_time') $endDaySettings['end'] = $row['setting_value'];
            if ($row['setting_name'] == 'keyboard_enabled') $endDaySettings['keyboard_enabled'] = $row['setting_value'];
        }
    }
}
?>
<script>
window.daySettings = <?php echo json_encode($endDaySettings); ?>;

/* Virtual Keyboard Logic */
document.addEventListener('DOMContentLoaded', function() {
    // Disable virtual keyboard on small screens (mobile) to allow native keyboard
    if (window.innerWidth < 768 || window.daySettings.keyboard_enabled !== '1') return;

    // Inject Keyboard HTML
    const kbHTML = `
    <div id="virtual-keyboard" class="fixed bottom-0 left-0 w-full z-[99999] transition-transform duration-300 translate-y-full bg-[#1e1e2e] border-t border-white/10 shadow-2xl font-sans" dir="ltr">
        <div class="flex items-center justify-between px-4 py-2 bg-black/20 border-b border-white/5 backdrop-blur-md">
            <div class="flex gap-2">
                <button type="button" data-layout="ar" class="kb-lang-btn px-4 py-1.5 text-xs font-bold rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all border border-white/5">العربية</button>
                <button type="button" data-layout="fr" class="kb-lang-btn px-4 py-1.5 text-xs font-bold rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all border border-white/5">Français</button>
                <button type="button" data-layout="num" class="kb-lang-btn px-4 py-1.5 text-xs font-bold rounded-lg bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white transition-all border border-white/5">123</button>
            </div>
            <button type="button" id="kb-hide-btn" class="p-2 text-gray-400 hover:text-white bg-white/5 hover:bg-red-500/20 hover:text-red-400 rounded-lg transition-all">
                <span class="material-icons-round text-lg">keyboard_hide</span>
            </button>
        </div>
        <div id="kb-keys" class="p-2 flex flex-col gap-1.5 select-none pb-6 sm:pb-3 overflow-y-auto max-h-[45vh]"></div>
    </div>
    <button type="button" id="kb-show-btn" class="fixed bottom-4 right-4 z-[99998] w-12 h-12 flex items-center justify-center bg-indigo-600 hover:bg-indigo-500 text-white rounded-full shadow-lg hidden transition-all hover:scale-110 active:scale-95 backdrop-blur-sm border border-white/10">
        <span class="material-icons-round text-2xl">keyboard</span>
    </button>
    <style>
        .kb-key {
            background: rgba(255,255,255,0.08);
            border: 1px solid rgba(255,255,255,0.1);
            color: white;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.1s;
            user-select: none;
            height: 45px;
            box-shadow: 0 2px 0 rgba(0,0,0,0.2);
            flex: 1;
        }
        .kb-key:active { transform: translateY(2px); box-shadow: none; background: rgba(255,255,255,0.15); }
        .kb-key.special { background: rgba(59, 130, 246, 0.2); border-color: rgba(59, 130, 246, 0.4); color: #60a5fa; }
        .kb-key.action { background: rgba(255,255,255,0.15); flex: 1.5; }
        .kb-key.space { flex: 6; }
        #virtual-keyboard.active { transform: translateY(0); }
        @media print {
            #virtual-keyboard, #kb-show-btn { display: none !important; }
        }
    </style>
    `;
    document.body.insertAdjacentHTML('beforeend', kbHTML);

    const kbContainer = document.getElementById('virtual-keyboard');
    const keysContainer = document.getElementById('kb-keys');
    let activeInput = null;
    let currentLayout = 'ar'; // Default

    const layouts = {
        'ar': [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'backspace'],
            ['ض', 'ص', 'ث', 'ق', 'ف', 'غ', 'ع', 'ه', 'خ', 'ح', 'ج', 'د'],
            ['ش', 'س', 'ي', 'ب', 'ل', 'ا', 'ت', 'ن', 'م', 'ك', 'ط', 'enter'],
            ['ئ', 'ء', 'ؤ', 'ر', 'لا', 'ى', 'ة', 'و', 'ز', 'ظ', '.', ','],
            ['space']
        ],
        'fr': [
            ['1', '2', '3', '4', '5', '6', '7', '8', '9', '0', 'backspace'],
            ['a', 'z', 'e', 'r', 't', 'y', 'u', 'i', 'o', 'p'],
            ['q', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm'],
            ['w', 'x', 'c', 'v', 'b', 'n', ',', '.', '@', 'enter'],
            ['space']
        ],
        'num': [
            ['7', '8', '9', 'backspace'],
            ['4', '5', '6', 'enter'],
            ['1', '2', '3', '.'],
            ['0', '00', 'space']
        ]
    };

    function renderKeys(layoutName) {
        currentLayout = layoutName;
        const rows = layouts[layoutName];
        keysContainer.innerHTML = '';
        
        document.querySelectorAll('.kb-lang-btn').forEach(btn => {
            if(btn.dataset.layout === layoutName) {
                btn.classList.add('bg-primary/20', 'text-primary', 'border-primary/50');
                btn.classList.remove('text-gray-400');
            } else {
                btn.classList.remove('bg-primary/20', 'text-primary', 'border-primary/50');
                btn.classList.add('text-gray-400');
            }
        });

        rows.forEach(row => {
            const rowDiv = document.createElement('div');
            rowDiv.className = 'flex w-full gap-1.5 justify-center';
            
            row.forEach(key => {
                const btn = document.createElement('div');
                btn.className = 'kb-key';
                
                if (key === 'space') {
                    btn.classList.add('space');
                    btn.innerHTML = '␣';
                    btn.dataset.action = 'space';
                } else if (key === 'backspace') {
                    btn.innerHTML = '<span class="material-icons-round">backspace</span>';
                    btn.classList.add('action');
                    btn.dataset.action = 'backspace';
                } else if (key === 'enter') {
                    btn.innerHTML = '<span class="material-icons-round">keyboard_return</span>';
                    btn.classList.add('special');
                    btn.dataset.action = 'enter';
                } else {
                    btn.textContent = key;
                    btn.dataset.char = key;
                }

                btn.addEventListener('mousedown', (e) => {
                    e.preventDefault();
                    handleKey(btn);
                });

                rowDiv.appendChild(btn);
            });
            keysContainer.appendChild(rowDiv);
        });
    }

    function handleKey(btn) {
        if (!activeInput) return;
        
        const action = btn.dataset.action;
        const char = btn.dataset.char;
        
        let val = activeInput.value;
        let start = val.length;
        let end = val.length;

        try {
            // Safely attempt to get selection range (throws on type='number' in some browsers)
            start = activeInput.selectionStart;
            end = activeInput.selectionEnd;
            if (start === null) {
                start = val.length;
                end = val.length;
            }
        } catch (e) {
            // Fallback for inputs that don't support selection
            start = val.length;
            end = val.length;
        }

        // Keep focus
        try { activeInput.focus(); } catch(e) {}

        try {
            if (action === 'backspace') {
                if (start === end && start > 0) {
                    activeInput.value = val.slice(0, start - 1) + val.slice(end);
                    activeInput.setSelectionRange(start - 1, start - 1);
                } else if (start !== end) {
                    activeInput.value = val.slice(0, start) + val.slice(end);
                    activeInput.setSelectionRange(start, start);
                }
            } else if (action === 'enter') {
                if (activeInput.tagName === 'TEXTAREA') {
                    activeInput.value = val.slice(0, start) + '\n' + val.slice(end);
                    activeInput.setSelectionRange(start + 1, start + 1);
                } else {
                     activeInput.dispatchEvent(new KeyboardEvent('keydown', {'key': 'Enter', bubbles: true}));
                     // Also try to blur or submit
                     // activeInput.blur(); 
                }
            } else if (action === 'space') {
                activeInput.value = val.slice(0, start) + ' ' + val.slice(end);
                activeInput.setSelectionRange(start + 1, start + 1);
            } else if (char) {
                activeInput.value = val.slice(0, start) + char + val.slice(end);
                activeInput.setSelectionRange(start + 1, start + 1);
            }
        } catch(e) {
            // Fallback for inputs that don't support selectionStart (like type='number')
            if (action === 'backspace') {
                 activeInput.value = activeInput.value.slice(0, -1);
            } else if (char) {
                 activeInput.value = activeInput.value + char;
            } else if (action === 'space') {
                 // Numbers don't support space usually
            }
        }
        
        activeInput.dispatchEvent(new Event('input', { bubbles: true }));
        activeInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function showKeyboard(input) {
        if (!input || input.readOnly || input.disabled) return;
        activeInput = input;
        kbContainer.classList.add('active');
        document.getElementById('kb-show-btn').classList.add('hidden');
        
        const kbHeight = kbContainer.offsetHeight;
        document.body.style.paddingBottom = (kbHeight + 20) + 'px';
        
        setTimeout(() => {
             input.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }, 300);
    }

    function hideKeyboard() {
        kbContainer.classList.remove('active');
        document.body.style.paddingBottom = '0';
        if (activeInput) {
            document.getElementById('kb-show-btn').classList.remove('hidden');
        }
    }

    document.addEventListener('focusin', (e) => {
        const target = e.target;
        if ((target.tagName === 'INPUT' && !['checkbox', 'radio', 'file', 'submit', 'button', 'hidden', 'range'].includes(target.type)) || target.tagName === 'TEXTAREA') {
            showKeyboard(target);
        }
    });

    document.getElementById('kb-hide-btn').addEventListener('click', hideKeyboard);
    
    document.getElementById('kb-show-btn').addEventListener('click', () => {
        if (activeInput) {
             showKeyboard(activeInput);
             try { activeInput.focus(); } catch(e){}
        }
    });

    document.querySelectorAll('.kb-lang-btn').forEach(btn => {
        btn.addEventListener('click', () => renderKeys(btn.dataset.layout));
    });

    renderKeys('ar');
});

(function() {
    // End of Day Reminder Logic
    async function checkEndOfDay() {
        if (window.daySettings.enabled !== '1') return;

        const now = new Date();
        const currentTime = now.getHours() * 60 + now.getMinutes();
        
        const [startH, startM] = window.daySettings.start.split(':').map(Number);
        const startTime = startH * 60 + startM;
        
        const [endH, endM] = window.daySettings.end.split(':').map(Number);
        let endTime = endH * 60 + endM;
        
        // Adjust for 2 minute reminder before closing
        endTime -= 2;
        if (endTime < 0) endTime += 1440;

        let inWindow = false;
        
        // Logic: Alert if we are PAST the end time but BEFORE the start time (the "closed" window)
        // Case 1: Night shift wrap (e.g., End 00:00, Start 05:00)
        // Window is 00:00 to 05:00
        if (endTime < startTime) {
            inWindow = (currentTime >= endTime && currentTime < startTime);
        } 
        // Case 2: Same day (e.g., End 20:00, Start 08:00)
        // Window is 20:00 to 23:59 OR 00:00 to 08:00
        else {
            inWindow = (currentTime >= endTime || currentTime < startTime);
        }

        if (inWindow) {
            // Check throttle
            const lastAlert = parseInt(localStorage.getItem('end_day_last_alert') || '0');
            const throttleTime = 30 * 60 * 1000; // 30 minutes
            if (Date.now() - lastAlert < throttleTime) return;

            try {
                // Check if day is open
                const res = await fetch('api.php?action=get_business_day_status');
                const data = await res.json();
                
                if (data.success && data.data.status === 'open') {
                    // Show Alert
                    showToast(window.__('end_day_reminder_msg'), 'info');
                    
                    if ('Notification' in window && Notification.permission === 'granted') {
                         new Notification('⚠️ ' + window.__('end_of_day_reminder_title'), {
                             body: window.__('end_day_reminder_msg'),
                             tag: 'end-day-alert',
                             lang: '<?php echo get_locale(); ?>'
                         });
                    }

                    localStorage.setItem('end_day_last_alert', Date.now());
                }
            } catch (e) {
                console.error('Error checking day status:', e);
            }
        }
    }

    // Run check on load and every 1 minute to ensure reminder accuracy
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(checkEndOfDay, 5000));
    } else {
        setTimeout(checkEndOfDay, 5000);
    }
    setInterval(checkEndOfDay, 60 * 1000);
})();

document.addEventListener('DOMContentLoaded', function() {
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
});

</script>
</body>
</html>