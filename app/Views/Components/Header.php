<style>
    .notification-wrapper {
        position: relative;
        display: inline-block;
        margin-right: 10px;
    }

    .notification-icon {
        font-size: 14px;
        background-color: #ece9d8;
        border: 2px solid #7f9db9;
        border-radius: 6px;
        padding: 5px 8px;
        cursor: pointer;
        user-select: none;
        text-shadow: 1px 1px #fff;
    }

    .notification-icon:hover {
        background-color: #d4d0c8;
    }

    .notification-dropdown {
        display: none;
        position: absolute;
        top: 115%;
        margin-top: 10px;
        right: 0;
        background-color: #ffffff;
        border: 2px solid #7f9db9;
        border-radius: 6px;
        width: 350px;
        z-index: 1000;
        font-family: Tahoma, sans-serif;
        font-size: 13px;
        padding: 10px;
        max-height: 300px;
        overflow-y: auto;
    }

    .notification-dropdown p {
        margin: 0;
        padding: 5px;
    }

    .notification-badge {
        position: absolute;
        top: -8px;
        right: -8px;
        background-color: red;
        font-weight: bold;
        box-shadow: none !important;
        text-shadow: none !important;
        color: white;
        font-size: 10px;
        padding: 2px 5px;
        border: 2px solid #ece9d8;
        border-radius: 50%;
        z-index: 1001;
        pointer-events: none;
    }
</style>

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