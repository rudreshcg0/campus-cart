<?php
/**
 * Campus Cart - User Profile & Account Page (profile.php)
 * Account Information, Listed Items Management & Order History
 */
session_start();
require_once __DIR__ . '/../config/db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?notice=" . urlencode("Please log in to access your profile."));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$message = '';
$messageType = '';

// Handle Delete Listing action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_product') {
    $productId = (int)$_POST['product_id'];
    try {
        $delStmt = $pdo->prepare("DELETE FROM products WHERE id = ? AND user_id = ?");
        $delStmt->execute([$productId, $userId]);
        $_SESSION['flash_msg'] = "Listing has been successfully removed.";
        $_SESSION['flash_type'] = "info";
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Failed to remove listing: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: profile.php#my-listings");
    exit;
}

// Handle Update Profile Picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_avatar') {
    $avatarUrl = trim($_POST['avatar_url'] ?? '');
    try {
        $upStmt = $pdo->prepare("UPDATE users SET avatar_url = ? WHERE id = ?");
        $upStmt->execute([$avatarUrl ?: null, $userId]);
        $_SESSION['flash_msg'] = !empty($avatarUrl) ? "Profile picture updated successfully." : "Profile picture removed.";
        $_SESSION['flash_type'] = "success";
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Failed to update profile picture: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: profile.php");
    exit;
}

// Handle Remove Profile Picture
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_avatar') {
    try {
        $upStmt = $pdo->prepare("UPDATE users SET avatar_url = NULL WHERE id = ?");
        $upStmt->execute([$userId]);
        $_SESSION['flash_msg'] = "Profile picture removed.";
        $_SESSION['flash_type'] = "info";
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Failed to remove profile picture: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: profile.php");
    exit;
}

// Retrieve flash message
if (isset($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

// 1. Fetch User Information
$userStmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$userStmt->execute([$userId]);
$user = $userStmt->fetch();

// 2. Fetch User Listed Products
$listingsStmt = $pdo->prepare("SELECT * FROM products WHERE user_id = ? ORDER BY id DESC");
$listingsStmt->execute([$userId]);
$myProducts = $listingsStmt->fetchAll();

// 3. Fetch User Order History
$ordersStmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
$ordersStmt->execute([$userId]);
$myOrders = $ordersStmt->fetchAll();

// Calculate initials for avatar
$nameParts = explode(' ', $user['name'] ?? 'Student');
$initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - Campus Cart</title>
    <!-- Separate Stylesheets Following Industry Standard Architecture -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="cc-page-wrapper">
    <div class="cc-container">
        
        <div class="cc-page-header">
            <div>
                <h1 class="cc-page-title">Student Profile & Dashboard</h1>
                <p class="cc-page-subtitle">Manage campus listings, view purchase records, and manage account</p>
            </div>
            <a href="sell.php" class="cc-btn cc-btn-primary-sm">+ Post New Listing</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="cc-alert cc-alert-<?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="cc-profile-grid">
            
            <!-- Left Sidebar Profile Card -->
            <aside class="cc-profile-sidebar">
                <div class="cc-profile-header-card">
                    <?php if (!empty($user['avatar_url'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_url']); ?>" alt="Profile Picture" class="cc-profile-img">
                    <?php else: ?>
                        <div class="cc-profile-avatar"><?php echo htmlspecialchars($initials); ?></div>
                    <?php endif; ?>
                    <h3 class="cc-profile-name"><?php echo htmlspecialchars($user['name']); ?></h3>
                    <p class="cc-profile-email"><?php echo htmlspecialchars($user['email']); ?></p>

                    <!-- Profile Photo Actions -->
                    <div class="cc-avatar-actions">
                        <button type="button" class="cc-btn-link-action" onclick="document.getElementById('avatarForm').style.display = document.getElementById('avatarForm').style.display === 'none' ? 'block' : 'none';">
                            <?php echo !empty($user['avatar_url']) ? 'Change Photo' : 'Add Photo'; ?>
                        </button>
                        <?php if (!empty($user['avatar_url'])): ?>
                            <form action="profile.php" method="POST" style="display:inline;" onsubmit="return confirm('Remove your profile picture?');">
                                <input type="hidden" name="action" value="remove_avatar">
                                <button type="submit" class="cc-btn-link-action" style="color: var(--cc-danger);">Remove</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <!-- Photo Add/Edit Form -->
                    <form id="avatarForm" action="profile.php" method="POST" class="cc-avatar-form" style="display: none;">
                        <input type="hidden" name="action" value="update_avatar">
                        <input type="url" name="avatar_url" class="cc-form-input cc-avatar-input" placeholder="Paste image URL (https://...)" value="<?php echo htmlspecialchars($user['avatar_url'] ?? ''); ?>" required>
                        <div style="display: flex; gap: 6px; margin-top: 6px; justify-content: center;">
                            <button type="submit" class="cc-btn-table-action">Save Photo</button>
                            <button type="button" class="cc-btn-table-action" onclick="document.getElementById('avatarForm').style.display = 'none';">Cancel</button>
                        </div>
                    </form>
                </div>

                <div class="cc-profile-stats">
                    <div class="cc-stat-block">
                        <div class="cc-stat-num"><?php echo count($myProducts); ?></div>
                        <div class="cc-stat-label">Listed Items</div>
                    </div>
                    <div class="cc-stat-block">
                        <div class="cc-stat-num"><?php echo count($myOrders); ?></div>
                        <div class="cc-stat-label">Orders Placed</div>
                    </div>
                </div>

                <ul class="cc-profile-menu">
                    <li><a href="#my-listings">My Listed Items (<?php echo count($myProducts); ?>)</a></li>
                    <li><a href="#order-history">Order History (<?php echo count($myOrders); ?>)</a></li>
                    <li><a href="sell.php">Post New Item for Sale</a></li>
                    <li><a href="cart.php">View Active Cart</a></li>
                    <li><a href="logout.php" style="color: var(--cc-danger);">Logout of Account</a></li>
                </ul>
            </aside>

            <!-- Main Content: Listed Items & Order History -->
            <div class="cc-profile-main">
                
                <!-- Section 1: My Listed Items -->
                <section id="my-listings" class="cc-profile-section">
                    <div class="cc-section-title-wrap">
                        <h2 class="cc-section-heading">My Listed Items</h2>
                        <span style="font-size: 12px; color: var(--cc-text-subtle);">Active in Marketplace</span>
                    </div>

                    <?php if (empty($myProducts)): ?>
                        <div style="padding: 24px 0; text-align: center; color: var(--cc-text-muted);">
                            <p style="margin-bottom: 12px;">You haven't listed any items for sale yet.</p>
                            <a href="sell.php" class="cc-btn cc-btn-primary-sm">Create Your First Listing</a>
                        </div>
                    <?php else: ?>
                        <div class="cc-table-responsive">
                            <table class="cc-data-table">
                                <thead>
                                    <tr>
                                        <th>Item Title</th>
                                        <th>Category</th>
                                        <th>Price</th>
                                        <th>Date Listed</th>
                                        <th style="text-align: right;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myProducts as $prod): ?>
                                        <tr>
                                            <td>
                                                <div style="display: flex; align-items: center; gap: 10px;">
                                                    <img 
                                                        src="<?php echo htmlspecialchars($prod['image_url'] ?: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80'); ?>" 
                                                        alt="<?php echo htmlspecialchars($prod['title']); ?>" 
                                                        style="width: 38px; height: 38px; object-fit: cover; border-radius: var(--cc-radius); border: 1px solid var(--cc-border);"
                                                    >
                                                    <strong style="color: var(--cc-text-main);"><?php echo htmlspecialchars($prod['title']); ?></strong>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($prod['category']); ?></td>
                                            <td><span style="font-family: var(--cc-font-mono); font-weight: 700;">₹<?php echo number_format($prod['price'], 2); ?></span></td>
                                            <td><?php echo date('d M Y', strtotime($prod['created_at'])); ?></td>
                                            <td style="text-align: right;">
                                                <form action="profile.php" method="POST" onsubmit="return confirm('Delete this listing from Campus Cart?');" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete_product">
                                                    <input type="hidden" name="product_id" value="<?php echo (int)$prod['id']; ?>">
                                                    <button type="submit" class="cc-btn-table-delete">
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

                <!-- Section 2: Order History -->
                <section id="order-history" class="cc-profile-section">
                    <div class="cc-section-title-wrap">
                        <h2 class="cc-section-heading">Order History</h2>
                        <span style="font-size: 12px; color: var(--cc-text-subtle);">Purchases & Meetup Receipts</span>
                    </div>

                    <?php if (empty($myOrders)): ?>
                        <div style="padding: 24px 0; text-align: center; color: var(--cc-text-muted);">
                            <p style="margin-bottom: 12px;">You haven't placed any orders yet.</p>
                            <a href="index.php" class="cc-btn cc-btn-primary-sm">Browse Marketplace Items</a>
                        </div>
                    <?php else: ?>
                        <div class="cc-table-responsive">
                            <table class="cc-data-table">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Payment Method</th>
                                        <th>Total Paid</th>
                                        <th>Order Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myOrders as $order): ?>
                                        <tr>
                                            <td>
                                                <strong style="font-family: var(--cc-font-mono); color: var(--cc-primary);">
                                                    #CC-<?php echo str_pad((string)$order['id'], 4, '0', STR_PAD_LEFT); ?>
                                                </strong>
                                            </td>
                                            <td><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></td>
                                            <td>
                                                <span class="cc-order-pay-method">
                                                    <?php echo htmlspecialchars($order['payment_method']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <strong style="font-family: var(--cc-font-mono);">
                                                    ₹<?php echo number_format($order['total_amount'], 2); ?>
                                                </strong>
                                            </td>
                                            <td>
                                                <span class="cc-order-status-completed">Completed</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </section>

            </div>

        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
