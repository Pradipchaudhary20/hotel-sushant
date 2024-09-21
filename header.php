<header>
    <div id="logo">
        <h1>
            Hotel
            <br>
            <span>yeti</span>
        </h1>
    </div>
    <nav>
        <ul id="h_menu">
            <li>
                <a href="index.php">Home</a>
            </li>
            <li>
                <a href="index.php?page=about">About Us</a>
            </li>
            <li>
                <a href="index.php?page=gallery">Gallery</a>
            </li>

            <?php
            // Start the session if not already started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            if (isset($_SESSION['username'])): ?>
                <li class="dropdown">
                    <a href="#" class="dropdown-toggle">
                        <strong><?php echo htmlspecialchars($_SESSION['username']); ?></strong> <span
                            class="dropdown-arrow">&#9662;</span>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="index.php?page=mybookings">My Bookings</a></li>
                        <li><a href="logout.php">Logout</a></li>
                    </ul>
                </li>
            <?php else: ?>
                <li>
                    <a href="index.php?page=login">Login</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</header>