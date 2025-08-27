document.addEventListener('DOMContentLoaded', function () {
    const bell = document.getElementById('notification-bell');
    const countBadge = document.getElementById('notification-count');
    const dropdown = document.getElementById('notifications-dropdown');

    let unreadNotificationIds = [];

    const fetchNotifications = () => {
        fetch('/gutu-hospital/backend/notification_handler.php?action=fetch_unread')
            .then(response => response.json())
            .then(data => {
                if (data.success && data.notifications.length > 0) {
                    const notifications = data.notifications;
                    unreadNotificationIds = notifications.map(n => n.id);

                    // Update badge
                    countBadge.textContent = notifications.length;
                    countBadge.style.display = 'block';

                    // Populate dropdown
                    dropdown.innerHTML = ''; // Clear existing
                    notifications.forEach(n => {
                        const item = document.createElement('div');
                        item.className = 'notification-item';
                        
                        const message = document.createElement('p');
                        message.textContent = n.message;
                        
                        const time = document.createElement('div');
                        time.className = 'timestamp';
                        // Format timestamp nicely
                        const date = new Date(n.createdAt);
                        time.textContent = date.toLocaleString();

                        item.appendChild(message);
                        item.appendChild(time);
                        dropdown.appendChild(item);
                    });

                } else {
                    // No new notifications
                    countBadge.style.display = 'none';
                    dropdown.innerHTML = '<div class="no-notifications">No new notifications.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching notifications:', error);
            });
    };

    const markAsRead = () => {
        if (unreadNotificationIds.length === 0) {
            return; // Nothing to mark
        }

        fetch('/gutu-hospital/backend/notification_handler.php?action=mark_as_read', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ ids: unreadNotificationIds })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reset for next fetch
                unreadNotificationIds = [];
                countBadge.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error marking notifications as read:', error);
        });
    };

    // --- Event Listeners ---
    bell.addEventListener('click', (e) => {
        e.stopPropagation(); // Prevent click from closing dropdown immediately
        const isVisible = dropdown.style.display === 'block';
        dropdown.style.display = isVisible ? 'none' : 'block';
        
        // If we are showing the dropdown and there are unread items, mark them as read
        if (!isVisible && countBadge.style.display === 'block') {
            markAsRead();
        }
    });

    // Close dropdown if clicking outside
    document.addEventListener('click', function(e) {
        if (!bell.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.style.display = 'none';
        }
    });


    // Initial fetch when the page loads
    fetchNotifications();
});
