<?php
/**
 * Campus Cart - Sell Page (sell.php)
 * Student Item Listing Submission Form
 */
session_start();
require_once __DIR__ . '/../config/db.php';

// Force authentication for sellers
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?notice=" . urlencode("Please log in to post a new listing on Campus Cart."));
    exit;
}

$errorMessage = '';
$userId = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $imageUrl = trim($_POST['image_url'] ?? '');

    // Default placeholder if none provided
    if (empty($imageUrl)) {
        if ($category === 'Books') {
            $imageUrl = 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80';
        } elseif ($category === 'Electronics') {
            $imageUrl = 'https://images.unsplash.com/photo-1553406830-ef2513450d76?auto=format&fit=crop&w=600&q=80';
        } elseif ($category === 'Notes') {
            $imageUrl = 'https://images.unsplash.com/photo-1517842645767-c639042777db?auto=format&fit=crop&w=600&q=80';
        } else {
            $imageUrl = 'https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&w=600&q=80';
        }
    }

    if (empty($title) || empty($category) || empty($price)) {
        $errorMessage = "Please fill in Title, Category, and Price.";
    } elseif (!is_numeric($price) || floatval($price) <= 0) {
        $errorMessage = "Please enter a valid price amount.";
    } else {
        try {
            $insertStmt = $pdo->prepare("
                INSERT INTO products (user_id, title, category, description, price, image_url) 
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $insertStmt->execute([
                $userId,
                $title,
                $category,
                $description,
                floatval($price),
                $imageUrl
            ]);

            $_SESSION['flash_msg'] = "Your listing '{$title}' has been posted to the campus marketplace!";
            $_SESSION['flash_type'] = "success";

            header("Location: profile.php#my-listings");
            exit;
        } catch (Exception $e) {
            $errorMessage = "Failed to create listing: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post a Listing - Campus Cart</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/sell.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="cc-page-wrapper">
    <div class="cc-container">
        
        <div class="cc-page-header">
            <div>
                <h1 class="cc-page-title">List an Item for Sale</h1>
                <p class="cc-page-subtitle">Publish items to student peers across campus departments</p>
            </div>
            <a href="profile.php#my-listings" class="cc-btn cc-btn-outline-sm">View My Listings</a>
        </div>

        <?php if (!empty($errorMessage)): ?>
            <div class="cc-alert cc-alert-danger">
                <?php echo htmlspecialchars($errorMessage); ?>
            </div>
        <?php endif; ?>

        <div class="cc-sell-layout">
            <!-- Sell Form Card -->
            <div class="cc-sell-card">
                <form action="sell.php" method="POST">
                    
                    <div class="cc-form-group">
                        <label for="productTitle" class="cc-form-label">Item Title *</label>
                        <input 
                            type="text" 
                            id="productTitle" 
                            name="title" 
                            class="cc-form-input" 
                            placeholder="e.g. Casio fx-991EX Scientific Calculator / Signals & Systems Text" 
                            value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                        <div class="cc-form-group">
                            <label for="productCategory" class="cc-form-label">Category *</label>
                            <select id="productCategory" name="category" class="cc-form-select" required>
                                <option value="">Select Category</option>
                                <option value="Electronics" <?php echo (($_POST['category'] ?? '') === 'Electronics') ? 'selected' : ''; ?>>Electronics & Gadgets</option>
                                <option value="Books" <?php echo (($_POST['category'] ?? '') === 'Books') ? 'selected' : ''; ?>>Textbooks & Reference</option>
                                <option value="Stationery" <?php echo (($_POST['category'] ?? '') === 'Stationery') ? 'selected' : ''; ?>>Stationery & Drafting</option>
                                <option value="Notes" <?php echo (($_POST['category'] ?? '') === 'Notes') ? 'selected' : ''; ?>>Class & Lecture Notes</option>
                            </select>
                        </div>

                        <div class="cc-form-group">
                            <label for="productPrice" class="cc-form-label">Price (INR ₹) *</label>
                            <div class="cc-price-input-wrapper">
                                <span class="cc-currency-symbol">₹</span>
                                <input 
                                    type="number" 
                                    id="productPrice" 
                                    name="price" 
                                    step="0.50" 
                                    min="1" 
                                    class="cc-form-input cc-price-input" 
                                    placeholder="450.00" 
                                    value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>" 
                                    required
                                >
                            </div>
                        </div>
                    </div>

                    <div class="cc-form-group">
                        <label for="productDescription" class="cc-form-label">Item Condition & Details</label>
                        <textarea 
                            id="productDescription" 
                            name="description" 
                            rows="4" 
                            class="cc-form-textarea" 
                            placeholder="Describe condition (e.g., lightly used, any highlights, includes original cables/box, syllabus semester, etc.)"
                        ><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <div class="cc-form-group">
                        <label for="productImageUrl" class="cc-form-label">Image URL (Optional)</label>
                        <input 
                            type="url" 
                            id="productImageUrl" 
                            name="image_url" 
                            class="cc-form-input" 
                            placeholder="https://images.unsplash.com/... or paste any direct image link" 
                            value="<?php echo htmlspecialchars($_POST['image_url'] ?? ''); ?>"
                        >
                        <p class="cc-form-help">Leave empty to use category-specific high quality default image.</p>
                        <div class="cc-preview-box" id="imagePreviewBox">
                            <span>Image preview will appear here</span>
                        </div>
                    </div>

                    <div style="display: flex; gap: 12px; margin-top: 24px;">
                        <button type="submit" class="cc-btn cc-btn-primary" style="flex: 1;">
                            Publish Listing to Campus
                        </button>
                        <a href="index.php" class="cc-btn cc-btn-outline-sm" style="padding: 10px 20px;">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>

            <!-- Guidelines Sidebar -->
            <div class="cc-sell-guidelines-card">
                <h3 class="cc-guideline-header">Seller Guidelines</h3>
                <ul class="cc-guideline-list">
                    <li>
                        <strong>Fair Student Pricing</strong>
                        Consider pricing used books 30-50% below retail store prices for quick peer adoption.
                    </li>
                    <li>
                        <strong>Honest Condition Notes</strong>
                        Mention marked pages, missing parts, or battery health to maintain high campus credibility.
                    </li>
                    <li>
                        <strong>Campus Exchange Points</strong>
                        Standard meetup locations include Central Library steps, Canteen tables, or Main Quad.
                    </li>
                    <li>
                        <strong>Multiple Item Capabilities</strong>
                        Any student account can both buy products from peers and sell personal surplus items.
                    </li>
                </ul>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
