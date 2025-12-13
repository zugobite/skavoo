<!-- Toast Notification Container (always at top of body) -->
<div id="toast-container"></div>
<header>
    <div class="header-left">
        <a href="/feed" class="logo">SKAVOO</a>
        <form class="search-form" action="/search" method="GET">
            <input type="text" name="q" placeholder="Search Users (Ctrl+K)" class="search-input" />
        </form>
    </div>

    <nav class="header-nav">
        <a href="/feed" class="nav-link <?php echo ($_SERVER['REQUEST_URI'] === '/feed') ? 'active' : ''; ?>" title="Feed">
            🏠
        </a>
        <a href="/friends" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/friends') === 0 && strpos($_SERVER['REQUEST_URI'], '/requests') === false) ? 'active' : ''; ?>" title="Friends">
            👥
        </a>
        <a href="/friends/requests" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/friends/requests') === 0) ? 'active' : ''; ?>" title="Friend Requests">
            ✉️
            <?php if (isset($pendingRequestCount) && $pendingRequestCount > 0): ?>
                <span class="nav-badge"><?php echo $pendingRequestCount; ?></span>
            <?php endif; ?>
        </a>
        <a href="/messages" class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/messages') === 0) ? 'active' : ''; ?>" title="Messages">
            💬
        </a>
    </nav>

    <div class="header-right">
        <div class="notification-wrapper">
            <span class="notification-icon" id="notificationToggle" title="Notifications">
                🔔
                <span class="notification-badge" id="notificationCount" style="display: none;">0</span>
            </span>
            <div class="notification-dropdown" id="notificationDropdown">
                <div class="notification-header">
                    <span>Notifications</span>
                    <button type="button" id="markAllReadBtn" class="btn">Mark All Read</button>
                </div>
                <div class="notification-list" id="notificationList">
                    <div class="notification-empty">Loading...</div>
                </div>
            </div>
        </div>

        <div class="user-menu-wrapper">
            <button class="user-menu-toggle" id="userMenuToggle">
                <img src="<?php echo profilePicturePath($_SESSION['profile_picture'] ?? null); ?>" alt="Profile" class="header-avatar">
                <span class="user-name"><?php echo htmlspecialchars($_SESSION['full_name'] ?? $_SESSION['display_name'] ?? 'User'); ?></span>
            </button>
            <div class="user-menu-dropdown" id="userMenuDropdown">
                <a href="/user/profile/<?php echo $_SESSION['user_uuid']; ?>" class="user-menu-item">
                    👤 My Profile
                </a>
                <a href="/user/profile/<?php echo $_SESSION['user_uuid']; ?>/edit" class="user-menu-item">
                    ⚙️ Edit Profile
                </a>
                <div class="user-menu-divider"></div>
                <a href="/logout" class="user-menu-item logout">
                    🚪 Logout
                </a>
            </div>
        </div>
    </div>
</header>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Notification toggle
    const notifToggle = document.getElementById("notificationToggle");
    const notifDropdown = document.getElementById("notificationDropdown");
    const notifList = document.getElementById("notificationList");
    const notifCount = document.getElementById("notificationCount");
    const markAllReadBtn = document.getElementById("markAllReadBtn");

    // User menu toggle
    const userMenuToggle = document.getElementById("userMenuToggle");
    const userMenuDropdown = document.getElementById("userMenuDropdown");

    // Load notification count on page load
    loadNotificationCount();

    // Poll for new notifications every 30 seconds
    setInterval(loadNotificationCount, 30000);

    notifToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        userMenuDropdown.classList.remove('show');
        notifDropdown.classList.toggle('show');
        if (notifDropdown.classList.contains('show')) {
            loadNotifications();
        }
    });

    userMenuToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        notifDropdown.classList.remove('show');
        userMenuDropdown.classList.toggle('show');
    });

    document.addEventListener("click", (e) => {
        if (!notifDropdown.contains(e.target) && !notifToggle.contains(e.target)) {
            notifDropdown.classList.remove('show');
        }
        if (!userMenuDropdown.contains(e.target) && !userMenuToggle.contains(e.target)) {
            userMenuDropdown.classList.remove('show');
        }
    });

    markAllReadBtn.addEventListener("click", async () => {
        try {
            const response = await fetch('/api/notifications/read', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': '<?php echo \App\Helpers\Csrf::token(); ?>'
                }
            });
            if (response.ok) {
                loadNotifications();
                loadNotificationCount();
            }
        } catch (error) {
            console.error('Error marking notifications read:', error);
        }
    });

    async function loadNotificationCount() {
        try {
            const response = await fetch('/api/notifications/count');
            const data = await response.json();
            if (data.count > 0) {
                notifCount.textContent = data.count > 99 ? '99+' : data.count;
                notifCount.style.display = 'flex';
            } else {
                notifCount.style.display = 'none';
            }
        } catch (error) {
            console.error('Error loading notification count:', error);
        }
    }

    async function loadNotifications() {
        try {
            const response = await fetch('/api/notifications');
            const data = await response.json();
            
            if (data.notifications && data.notifications.length > 0) {
                notifList.innerHTML = data.notifications.map(n => `
                    <div class="notification-item ${n.is_read ? '' : 'unread'}" data-id="${n.id}">
                        <img src="${n.actor_picture}" alt="" class="notification-avatar">
                        <div class="notification-content">
                            <div class="notification-text">
                                <strong>${escapeHtml(n.actor_name)}</strong> ${getNotificationMessage(n.type)}
                            </div>
                            <div class="notification-time">${n.time_ago}</div>
                        </div>
                        ${!n.is_read ? '<span class="notification-dot"></span>' : ''}
                    </div>
                `).join('');

                // Add click handlers to mark individual notifications as read
                document.querySelectorAll('.notification-item.unread').forEach(item => {
                    item.addEventListener('click', async () => {
                        const id = item.dataset.id;
                        try {
                            await fetch(`/api/notifications/${id}/read`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-Token': '<?php echo \App\Helpers\Csrf::token(); ?>'
                                }
                            });
                            item.classList.remove('unread');
                            item.querySelector('.notification-dot')?.remove();
                            loadNotificationCount();
                        } catch (error) {
                            console.error('Error marking notification read:', error);
                        }
                    });
                });
            } else {
                notifList.innerHTML = '<div class="notification-empty">No notifications yet</div>';
            }
        } catch (error) {
            console.error('Error loading notifications:', error);
            notifList.innerHTML = '<div class="notification-empty">Error loading notifications</div>';
        }
    }

    function getNotificationMessage(type) {
        switch(type) {
            case 'friend_request': return 'sent you a friend request';
            case 'friend_accept': return 'accepted your friend request';
            case 'post_like': return 'liked your post';
            case 'post_comment': return 'commented on your post';
            case 'message': return 'sent you a message';
            default: return 'interacted with you';
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
});

// Toast notification function
window.showToast = function(message, duration = 3500) {
    let container = document.getElementById('toast-container');
    if (!container) {
        container = document.createElement('div');
        container.id = 'toast-container';
        document.body.prepend(container);
    }
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    container.appendChild(toast);
    // Force reflow for animation
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, duration);
};
</script>