</div> <!-- Close main container from header -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Global Notification Sound
    const globalNotificationSound = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
    let lastGlobalUnreadCount = null;

    function updateNotificationCount() {
        fetch('api.php?action=getNotifications')
            .then(response => response.json())
            .then(data => {
                const currentUnread = parseInt(data.unread_count);

                // Play sound if new notifications arrived (skip on first load)
                if (lastGlobalUnreadCount !== null && currentUnread > lastGlobalUnreadCount) {
                    globalNotificationSound.play().catch(() => {});
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
                        countElement.style.display = 'none';
                    }
                }
                if (countBadge) {
                    countBadge.textContent = data.unread_count;
                    if (data.unread_count > 0) {
                        countBadge.classList.remove('hidden');
                    } else {
                        countBadge.classList.add('hidden');
                    }
                }
            });
    }

    updateNotificationCount();
    setInterval(updateNotificationCount, 30000); // Refresh every 30 seconds
});
</script>
</body>
</html>