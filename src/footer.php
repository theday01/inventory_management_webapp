<script>
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