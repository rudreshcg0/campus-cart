<?php
/**
 * Campus Cart - Shopping Cart & Checkout Page (cart.php)
 */
session_start();
require_once __DIR__ . '/../config/db.php';

// Require login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?notice=" . urlencode("Please log in to view your shopping cart."));
    exit;
}

$userId = (int)$_SESSION['user_id'];
$message = '';
$messageType = '';

// 1. Handle Remove from Cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'remove_item') {
    $cartId = (int)$_POST['cart_id'];
    try {
        $deleteStmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
        $deleteStmt->execute([$cartId, $userId]);
        $_SESSION['flash_msg'] = "Item removed from cart.";
        $_SESSION['flash_type'] = "info";
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Error removing item: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
    }
    header("Location: cart.php");
    exit;
}

// 2. Handle Checkout Order Placement
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'checkout') {
    $paymentMethod = trim($_POST['payment_method'] ?? 'COD');
    
    try {
        // Fetch current cart items and sum total
        $calcStmt = $pdo->prepare("
            SELECT SUM(p.price) as total_sum, COUNT(c.id) as item_count 
            FROM cart c 
            JOIN products p ON c.product_id = p.id 
            WHERE c.user_id = ?
        ");
        $calcStmt->execute([$userId]);
        $summary = $calcStmt->fetch();

        $totalAmount = floatval($summary['total_sum'] ?? 0);
        $itemCount = intval($summary['item_count'] ?? 0);

        if ($itemCount === 0) {
            $_SESSION['flash_msg'] = "Your cart is currently empty.";
            $_SESSION['flash_type'] = "warning";
            header("Location: cart.php");
            exit;
        }

        // Fetch all product IDs being purchased in this order
        $getProductsStmt = $pdo->prepare("SELECT product_id FROM cart WHERE user_id = ?");
        $getProductsStmt->execute([$userId]);
        $purchasedProductIds = $getProductsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Insert into orders table
        $orderStmt = $pdo->prepare("
            INSERT INTO orders (user_id, total_amount, payment_method) 
            VALUES (?, ?, ?)
        ");
        $orderStmt->execute([$userId, $totalAmount, $paymentMethod]);
        $orderId = $pdo->lastInsertId();

        // Delete purchased items from marketplace listings so other students cannot buy them
        if (!empty($purchasedProductIds)) {
            $inPlaceholders = implode(',', array_fill(0, count($purchasedProductIds), '?'));
            $deleteProductsStmt = $pdo->prepare("DELETE FROM products WHERE id IN ($inPlaceholders)");
            $deleteProductsStmt->execute($purchasedProductIds);
        }

        // Empty user cart
        $clearCartStmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $clearCartStmt->execute([$userId]);

        $_SESSION['flash_msg'] = "Order #CC-{$orderId} placed successfully via {$paymentMethod}! Total: ₹" . number_format($totalAmount, 2);
        $_SESSION['flash_type'] = "success";

        header("Location: profile.php#order-history");
        exit;
    } catch (Exception $e) {
        $_SESSION['flash_msg'] = "Checkout error: " . $e->getMessage();
        $_SESSION['flash_type'] = "danger";
        header("Location: cart.php");
        exit;
    }
}

// Retrieve flash message
if (isset($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

// Fetch user's cart items
$stmt = $pdo->prepare("
    SELECT c.id as cart_id, c.created_at as added_at, p.id as product_id, p.title, p.category, p.price, p.image_url, u.name as seller_name
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN users u ON p.user_id = u.id
    WHERE c.user_id = ?
    ORDER BY c.id DESC
");
$stmt->execute([$userId]);
$cartItems = $stmt->fetchAll();

// Calculate totals
$subtotal = 0;
foreach ($cartItems as $item) {
    $subtotal += floatval($item['price']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Cart - Campus Cart</title>
    <!-- Separate Stylesheets Following Industry Standard Architecture -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/cart.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="cc-page-wrapper">
    <div class="cc-container">
        
        <div class="cc-page-header">
            <div>
                <h1 class="cc-page-title">Shopping Cart</h1>
                <p class="cc-page-subtitle">Review items and arrange campus peer delivery or pickup</p>
            </div>
            <a href="index.php" class="cc-btn cc-btn-outline-sm">← Continue Browsing</a>
        </div>

        <?php if (!empty($message)): ?>
            <div class="cc-alert cc-alert-<?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="cc-cart-table-card" style="padding: 48px 24px; text-align: center;">
                <h3 style="font-size: 18px; margin-bottom: 8px; color: var(--cc-text-main);">Your cart is empty</h3>
                <p style="color: var(--cc-text-muted); font-size: 13px; margin-bottom: 20px;">
                    Explore textbooks, electronics, notes, and drafting tools listed by students.
                </p>
                <a href="index.php" class="cc-btn cc-btn-primary">Browse Campus Marketplace</a>
            </div>
        <?php else: ?>
            <div class="cc-cart-layout">
                
                <!-- Cart Items Table Card -->
                <div class="cc-cart-table-card">
                    <table class="cc-cart-table">
                        <thead>
                            <tr>
                                <th>Item Details</th>
                                <th>Category</th>
                                <th>Price</th>
                                <th style="text-align: right;">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="cc-cart-item-info">
                                            <img 
                                                src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80'); ?>" 
                                                alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                                class="cc-cart-item-thumb"
                                            >
                                            <div>
                                                <div class="cc-cart-item-title"><?php echo htmlspecialchars($item['title']); ?></div>
                                                <div class="cc-cart-item-cat">Seller: <?php echo htmlspecialchars(explode(' ', $item['seller_name'])[0]); ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="font-size: 12px; color: var(--cc-text-muted); font-weight: 500;">
                                            <?php echo htmlspecialchars($item['category']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="cc-cart-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    </td>
                                    <td style="text-align: right;">
                                        <form action="cart.php" method="POST" onsubmit="return confirm('Remove this item from your cart?');">
                                            <input type="hidden" name="action" value="remove_item">
                                            <input type="hidden" name="cart_id" value="<?php echo (int)$item['cart_id']; ?>">
                                            <button type="submit" class="cc-btn cc-btn-danger-sm">
                                                Remove
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Cart Summary & Proceed to Buy -->
                <div class="cc-cart-summary-card">
                    <h3 class="cc-summary-title">Order Summary</h3>
                    
                    <div class="cc-summary-row">
                        <span>Total Items:</span>
                        <span><strong><?php echo count($cartItems); ?></strong></span>
                    </div>

                    <div class="cc-summary-row">
                        <span>Items Subtotal:</span>
                        <span>₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <div class="cc-summary-row">
                        <span>Campus Exchange Fee:</span>
                        <span class="cc-badge-free">FREE</span>
                    </div>

                    <div class="cc-summary-row total">
                        <span>Total Payable:</span>
                        <span class="cc-summary-total-val">₹<?php echo number_format($subtotal, 2); ?></span>
                    </div>

                    <button type="button" id="btnProceedToBuy" class="cc-btn cc-btn-primary cc-btn-block" style="margin-top: 20px; padding: 12px;">
                        Proceed to Buy
                    </button>

                    <p style="font-size: 11px; color: var(--cc-text-subtle); margin-top: 12px; text-align: center;">
                        Direct peer-to-peer settlement. Choose UPI, Card or Cash on Delivery.
                    </p>
                </div>

            </div>
        <?php endif; ?>

    </div>
</main>

<!-- Payment Modal (UPI, Card, Cash on Delivery / COD) -->
<div id="checkoutModal" class="cc-modal-backdrop">
    <div class="cc-modal-dialog">
        <div class="cc-modal-header">
            <h3 class="cc-modal-title">Select Payment Method</h3>
            <button type="button" class="cc-modal-close" id="modalCloseBtn">&times;</button>
        </div>

        <form action="cart.php" method="POST" id="checkoutForm">
            <input type="hidden" name="action" value="checkout">

            <div class="cc-modal-body">
                <div style="background-color: var(--cc-primary-light); border: 1px solid #bfdbfe; padding: 10px 14px; border-radius: var(--cc-radius); margin-bottom: 16px;">
                    <span style="font-size: 12px; color: var(--cc-text-muted);">Amount to Pay:</span>
                    <strong style="font-size: 16px; color: var(--cc-primary); font-family: var(--cc-font-mono); margin-left: 6px;">
                        ₹<?php echo number_format($subtotal, 2); ?>
                    </strong>
                </div>

                <div class="cc-payment-methods">
                    <!-- Option 1: UPI -->
                    <label class="cc-payment-method-card">
                        <input type="radio" name="payment_method" value="UPI" checked>
                        <div class="cc-payment-method-info">
                            <div class="cc-payment-method-name">UPI (Google Pay / PhonePe / Paytm / BHIM)</div>
                            <div class="cc-payment-method-sub">Instant zero-fee transfer via student UPI ID</div>
                        </div>
                    </label>

                    <!-- Option 2: Card -->
                    <label class="cc-payment-method-card">
                        <input type="radio" name="payment_method" value="Card">
                        <div class="cc-payment-method-info">
                            <div class="cc-payment-method-name">Debit / Credit Card</div>
                            <div class="cc-payment-method-sub">Visa, MasterCard, RuPay bank cards</div>
                        </div>
                    </label>

                    <!-- Option 3: COD -->
                    <label class="cc-payment-method-card">
                        <input type="radio" name="payment_method" value="COD">
                        <div class="cc-payment-method-info">
                            <div class="cc-payment-method-name">Cash on Delivery (COD) / Campus Meetup</div>
                            <div class="cc-payment-method-sub">Inspect physical item and pay seller in person</div>
                        </div>
                    </label>
                </div>

                <!-- Dynamic input fields injected by cart.js -->
                <div id="dynamicPaymentFields" class="cc-payment-dynamic-fields">
                    <!-- Default UPI field is populated by cart.js -->
                </div>
            </div>

            <div class="cc-modal-footer">
                <button type="button" class="cc-btn cc-btn-outline-sm" id="modalCancelBtn">Cancel</button>
                <button type="submit" class="cc-btn cc-btn-primary">Confirm & Place Order</button>
            </div>
        </form>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Dedicated Cart & Modal JavaScript -->
<script src="assets/js/cart.js"></script>
</body>
</html>
