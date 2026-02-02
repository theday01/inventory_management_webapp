<?php
// Fetch End of Day Settings
$endDaySettings = [
    'enabled' => '1',
    'start' => '05:00',
    'end' => '00:00'
];
if (isset($conn)) {
    $res = $conn->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('day_start_time', 'day_end_time', 'end_day_reminder_enabled')");
    if ($res) {
        while ($row = $res->fetch_assoc()) {
            if ($row['setting_name'] == 'end_day_reminder_enabled') $endDaySettings['enabled'] = $row['setting_value'];
            if ($row['setting_name'] == 'day_start_time') $endDaySettings['start'] = $row['setting_value'];
            if ($row['setting_name'] == 'day_end_time') $endDaySettings['end'] = $row['setting_value'];
        }
    }
}
?>
<script>
window.daySettings = <?php echo json_encode($endDaySettings); ?>;

(function() {
    // End of Day Reminder Logic
    async function checkEndOfDay() {
        if (window.daySettings.enabled !== '1') return;

        const now = new Date();
        const currentTime = now.getHours() * 60 + now.getMinutes();
        
        const [startH, startM] = window.daySettings.start.split(':').map(Number);
        const startTime = startH * 60 + startM;
        
        const [endH, endM] = window.daySettings.end.split(':').map(Number);
        const endTime = endH * 60 + endM;

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

    // Run check on load and every 5 minutes
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => setTimeout(checkEndOfDay, 5000));
    } else {
        setTimeout(checkEndOfDay, 5000);
    }
    setInterval(checkEndOfDay, 5 * 60 * 1000);
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