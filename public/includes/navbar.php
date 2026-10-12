<?php
/**
 * Campus Cart - Reusable Navigation Bar
 */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Calculate active page for navigation highlight
$currentPage = basename($_SERVER['PHP_SELF']);

// Count cart items if user is logged in
$cartCount = 0;
if (isset($_SESSION['user_id']) && isset($pdo)) {
    try {
        $cartStmt = $pdo->prepare("SELECT COUNT(*) as count FROM cart WHERE user_id = ?");
        $cartStmt->execute([$_SESSION['user_id']]);
        $cartCount = (int)$cartStmt->fetch()['count'];
    } catch (Exception $e) {
        $cartCount = 0;
    }
}
?>
<nav class="cc-navbar">
    <div class="cc-container cc-nav-inner">
        <!-- Brand Wordmark -->
        <a href="index.php" class="cc-brand">
            <span class="cc-brand-text">Campus Cart</span>
        </a>

        <!-- Navigation Links -->
        <ul class="cc-nav-links">
            <li>
                <a href="index.php" class="<?php echo ($currentPage == 'index.php') ? 'active' : ''; ?>">
                    Home
                </a>
            </li>
            <li>
                <a href="index.php#categories">
                    Categories
                </a>
            </li>
            <li>
                <a href="sell.php" class="<?php echo ($currentPage == 'sell.php') ? 'active' : ''; ?>">
                    Sell
                </a>
            </li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <li>
                    <a href="profile.php" class="<?php echo ($currentPage == 'profile.php') ? 'active' : ''; ?>">
                        Profile
                    </a>
                </li>
                <li>
                    <a href="cart.php" class="cc-cart-link <?php echo ($currentPage == 'cart.php') ? 'active' : ''; ?>">
                        Cart
                        <?php if ($cartCount > 0): ?>
                            <span class="cc-cart-count"><?php echo $cartCount; ?></span>
                        <?php endif; ?>
                    </a>
                </li>
            <?php endif; ?>
        </ul>

        <!-- User Authentication Actions -->
        <div class="cc-nav-auth">
            <?php if (isset($_SESSION['user_id'])): ?>
                <span class="cc-user-greeting">Hi, <?php echo htmlspecialchars(explode(' ', $_SESSION['user_name'])[0]); ?></span>
                <a href="logout.php" class="cc-btn cc-btn-outline-sm">Logout</a>
            <?php else: ?>
                <a href="login.php" class="cc-nav-link-subtle <?php echo ($currentPage == 'login.php') ? 'active' : ''; ?>">Log In</a>
                <a href="register.php" class="cc-btn cc-btn-primary-sm <?php echo ($currentPage == 'register.php') ? 'active' : ''; ?>">Register</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
