<header>
    <div class="header-left">
        <a href="/feed" class="logo">SKAVOO</a>
        <form class="search-form">
            <input type="text" name="q" placeholder="Search by name or email" class="search-input" />
        </form>
    </div>

    <div class="header-right">
        <div class="notification-wrapper">
            <span class="notification-icon" id="notificationToggle" title="Notifications">
                🔔
                <span class="notification-badge" id="notificationCount">3</span>
            </span>
            <div class="notification-dropdown" id="notificationDropdown">
                <p>No notifications yet.</p>
            </div>
        </div>

        <button onclick="window.location.href='/user/profile/<?php echo $_SESSION['user_uuid']; ?>'">Profile</button>
        <button onclick="window.location.href='/logout'">Logout</button>
    </div>
</header>

<script>
    document.addEventListener("DOMContentLoaded", () => {
        const toggle = document.getElementById("notificationToggle");
        const dropdown = document.getElementById("notificationDropdown");

        toggle.addEventListener("click", (e) => {
            dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
            e.stopPropagation();
        });

        document.addEventListener("click", (e) => {
            if (!dropdown.contains(e.target)) {
                dropdown.style.display = "none";
            }
        });
    });
</script>