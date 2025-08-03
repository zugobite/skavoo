<header>
    <div class="header-left">
        <a href="/feed" class="logo">SKAVOO</a>
        <form action="/search" method="GET" class="search-form">
            <input type="text" name="q" placeholder="Search by name or email" class="search-input" />
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="header-right">
        <button onclick="window.location.href='/user/profile/<?php echo $_SESSION['user_uuid']; ?>'">Profile</button>
        <button onclick="window.location.href='/logout'">Logout</button>
    </div>
</header>