<?php

// Get cart count
$cartCount = 0;

if (isset($_SESSION['user_id'])) {

    $stmt = $conn->prepare("
        SELECT SUM(quantity) AS total
        FROM cart
        WHERE user_id = ?
    ");

    $stmt->execute([$_SESSION['user_id']]);

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $cartCount = $row['total'] ?? 0;
}

?>

<nav class="navbar navbar-expand-lg custom-navbar fixed-top">

    <div class="container">

        <!-- Logo -->
        <a class="navbar-brand d-flex align-items-center" href="index.php">

            <img src="assets/images/logo/logo.png"
                 alt="Brew Haven Logo"
                 class="logo">

            <span class="brand-name ms-3">
                Brew Haven
            </span>

        </a>

        <!-- Mobile Toggle -->
        <button class="navbar-toggler border-0 shadow-none"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#navbarNav"
                aria-controls="navbarNav"
                aria-expanded="false"
                aria-label="Toggle navigation">

            <i class="fa-solid fa-bars text-white fs-3"></i>

        </button>

        <div class="collapse navbar-collapse" id="navbarNav">

            <!-- Center Menu -->
            <ul class="navbar-nav mx-auto">

                <li class="nav-item">
                    <a class="nav-link active" href="index.php">Home</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="menu.php">Menu</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="reservation.php">Reservations</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="about.php">About</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="gallery.php">Gallery</a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" href="contact.php">Contact</a>
                </li>

            </ul>

            <!-- Right Side -->
            <div class="d-flex align-items-center nav-icons">

                <!-- Search -->
                <a href="menu.php" class="icon-btn" aria-label="Search the menu">
                    <i class="fa-solid fa-magnifying-glass"></i>
                </a>

                <!-- Cart -->
                <a href="cart.php" class="icon-btn position-relative">

                    <i class="fa-solid fa-cart-shopping"></i>

                    <span class="cart-count">
                        <?php echo $cartCount; ?>
                    </span>

                </a>

                <?php if(isset($_SESSION['user_id'])): ?>

                    <!-- User Dropdown -->
                    <div class="dropdown ms-3">

                        <button class="btn login-btn dropdown-toggle"
                                type="button"
                                data-bs-toggle="dropdown"
                                aria-expanded="false">

                            <i class="fa-solid fa-user me-2"></i>

                            <?php
                            $firstName = explode(' ', $_SESSION['full_name'])[0];
                            echo htmlspecialchars($firstName);
                            ?>

                        </button>

                        <ul class="dropdown-menu dropdown-menu-end">

                            <li>
                                <a class="dropdown-item" href="dashboard.php">
                                    <i class="fa-solid fa-user me-2"></i>
                                    Profile
                                </a>
                            </li>

                            <li>
                                <a class="dropdown-item" href="cart.php">
                                    <i class="fa-solid fa-cart-shopping me-2"></i>
                                    My Cart
                                </a>
                            </li>

                            <li><form method="post" action="logout.php"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><button class="dropdown-item" type="submit"><i class="fa-solid fa-right-from-bracket me-2"></i>Logout</button></form></li>

                        </ul>

                    </div>

                <?php else: ?>

                    <!-- Guest Buttons -->
                    <a href="login.php" class="btn login-btn ms-3">
                        Login
                    </a>

                    <a href="register.php" class="btn btn-outline-light ms-2">
                        Register
                    </a>

                <?php endif; ?>

            </div>

        </div>

    </div>

</nav>
