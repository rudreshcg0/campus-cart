<?php
/**
 * Campus Cart - Landing Home Page (index.php)
 */
session_start();
require_once __DIR__ . '/../config/db.php';

$message = '';
$messageType = '';

// Handle Add to Cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_to_cart') {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php?notice=" . urlencode("Please log in to add items to your cart."));
        exit;
    }

    $productId = (int)$_POST['product_id'];
    $userId = (int)$_SESSION['user_id'];

    if ($productId > 0) {
        try {
            // Check if product exists
            $checkProduct = $pdo->prepare("SELECT id FROM products WHERE id = ?");
            $checkProduct->execute([$productId]);
            
            if ($checkProduct->fetch()) {
                // Check if already in cart
                $checkCart = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
                $checkCart->execute([$userId, $productId]);
                
                if ($checkCart->fetch()) {
                    $_SESSION['flash_msg'] = "This item is already in your cart.";
                    $_SESSION['flash_type'] = "info";
                } else {
                    $insertCart = $pdo->prepare("INSERT INTO cart (user_id, product_id) VALUES (?, ?)");
                    $insertCart->execute([$userId, $productId]);
                    $_SESSION['flash_msg'] = "Item added to your cart successfully!";
                    $_SESSION['flash_type'] = "success";
                }
            }
        } catch (Exception $e) {
            $_SESSION['flash_msg'] = "Failed to add item: " . $e->getMessage();
            $_SESSION['flash_type'] = "danger";
        }
    }

    // Redirect to preserve state and avoid resubmission
    $redirectParams = [];
    if (!empty($_GET['category']) && $_GET['category'] !== 'All') {
        $redirectParams['category'] = $_GET['category'];
    }
    if (!empty($_GET['q'])) {
        $redirectParams['q'] = $_GET['q'];
    }
    $redirectUrl = !empty($redirectParams) ? 'index.php?' . http_build_query($redirectParams) : 'index.php';
    header("Location: " . $redirectUrl);
    exit;
}

// Retrieve flash message if any
if (isset($_SESSION['flash_msg'])) {
    $message = $_SESSION['flash_msg'];
    $messageType = $_SESSION['flash_type'] ?? 'info';
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}

// Category filter & Search logic
$selectedCategory = isset($_GET['category']) ? trim($_GET['category']) : 'All';
if (empty($selectedCategory) || strcasecmp($selectedCategory, 'all') === 0) {
    $selectedCategory = 'All';
}
$searchQuery = isset($_GET['q']) ? trim($_GET['q']) : '';

// Base query for products joined with sellers
$sql = "SELECT p.*, u.name as seller_name FROM products p JOIN users u ON p.user_id = u.id WHERE 1=1";
$params = [];

// Filter strictly by category if not 'All'
if ($selectedCategory !== 'All') {
    $sql .= " AND LOWER(p.category) = LOWER(?)";
    $params[] = $selectedCategory;
}

if (!empty($searchQuery)) {
    $sql .= " AND (p.title LIKE ? OR p.description LIKE ?)";
    $params[] = '%' . $searchQuery . '%';
    $params[] = '%' . $searchQuery . '%';
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$categories = ['All', 'Electronics', 'Books', 'Stationery', 'Notes'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Campus Cart - College Marketplace</title>
    <!-- Separate Stylesheets Following Industry Standard Architecture -->
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="cc-page-wrapper">
    <div class="cc-container">

        <!-- Flash Alert Notification -->
        <?php if (!empty($message)): ?>
            <div class="cc-alert cc-alert-<?php echo htmlspecialchars($messageType); ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Hero Carousel Section -->
        <section class="cc-hero-section">
            <div class="carousel-container">
                <div id="carouselTrack">
                    <!-- Carousel Slide 1: Indian Campus Marketplace -->
                    <div class="carousel-slide">
                        <img 
                            src="https://images.unsplash.com/photo-1541339907198-e08756dedf3f?auto=format&fit=crop&w=1200&q=80"
                            alt="Indian College Students on University Campus"
                            class="carousel-img"
                        >
                        <div class="carousel-caption">
                            <span class="carousel-caption-tag">Peer-to-Peer Marketplace</span>
                            <h2>Save Big on College Essentials</h2>
                            <p>Buy used textbooks, lab equipment, drafting kits, and semester notes directly from fellow campus students.</p>
                        </div>
                    </div>

                    <!-- Carousel Slide 2: Engineering & Tech Hardware -->
                    <div class="carousel-slide">
                        <img 
                            src="https://5.imimg.com/data5/SELLER/Default/2025/11/557288299/YR/YZ/TJ/26422793/image-0901-500x500.jpg" 
                            alt="Indian Engineering College Lab Kits and Electronics"
                            class="carousel-img"
                        >
                        <div class="carousel-caption">
                            <span class="carousel-caption-tag">Electronics & Gadgets</span>
                            <h2>Lab Kits, Calculators & Components</h2>
                            <p>Finished your project? Pass on your Arduino boards, sensors, and scientific calculators to juniors.</p>
                        </div>
                    </div>

                    <!-- Carousel Slide 3: Course Books & Handwritten Notes -->
                    <div class="carousel-slide">
                        <img 
                            src="https://i0.wp.com/puralika.com/wp-content/uploads/2020/06/img_20200607_114601-016999207791983014595.jpeg?fit=1200%2C1200&ssl=1"
                            alt="Indian College Central Library Study and Textbooks"
                            class="carousel-img"
                        >
                        <div class="carousel-caption">
                            <span class="carousel-caption-tag">Academics & Study Guides</span>
                            <h2>Semester Textbooks & Handwritten Notes</h2>
                            <p>Hand-me-down reference books and topper lecture summaries available for easy pickup at the library steps.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories Filter Bar & Search -->
        <section id="categories" class="cc-categories-bar">
            <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap;">
                <span class="cc-categories-label">Filter by Category:</span>
                <div class="cc-category-pills">
                    <?php foreach ($categories as $cat): ?>
                        <?php 
                            $isActive = (strcasecmp($selectedCategory, $cat) === 0);
                            
                            // Pass the selected category via GET URL parameter
                            if ($cat === 'All') {
                                $catLink = !empty($searchQuery) ? 'index.php?q=' . urlencode($searchQuery) : 'index.php';
                            } else {
                                $queryParams = ['category' => $cat];
                                if (!empty($searchQuery)) {
                                    $queryParams['q'] = $searchQuery;
                                }
                                $catLink = 'index.php?' . http_build_query($queryParams);
                            }
                        ?>
                        <a href="<?php echo htmlspecialchars($catLink); ?>" 
                           class="cc-cat-pill <?php echo $isActive ? 'active' : ''; ?>"
                           aria-current="<?php echo $isActive ? 'true' : 'false'; ?>">
                            <?php if ($isActive): ?>
                                <span style="font-weight: bold; margin-right: 2px;">•</span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($cat); ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Search Form -->
            <form action="index.php" method="GET" class="cc-search-form">
                <?php if ($selectedCategory !== 'All'): ?>
                    <input type="hidden" name="category" value="<?php echo htmlspecialchars($selectedCategory); ?>">
                <?php endif; ?>
                <input 
                    type="text" 
                    name="q" 
                    class="cc-search-input" 
                    placeholder="Search <?php echo ($selectedCategory !== 'All') ? htmlspecialchars($selectedCategory) : 'titles, notes...'; ?>" 
                    value="<?php echo htmlspecialchars($searchQuery); ?>"
                >
                <button type="submit" class="cc-btn cc-btn-primary-sm">Search</button>
                <?php if (!empty($searchQuery) || $selectedCategory !== 'All'): ?>
                    <a href="index.php" class="cc-btn cc-btn-outline-sm">Reset</a>
                <?php endif; ?>
            </form>
        </section>

        <!-- Catalog Section -->
        <section class="cc-catalog-section">
            <div class="cc-section-header">
                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                    <h3 class="cc-section-title">
                        <?php 
                            if ($selectedCategory !== 'All') {
                                echo htmlspecialchars($selectedCategory) . ' Listings';
                            } else {
                                echo 'Latest Campus Listings';
                            }
                        ?>
                    </h3>
                    <?php if ($selectedCategory !== 'All'): ?>
                        <a href="index.php" class="cc-clear-filter-btn" title="View all items across all categories">
                            ✕ Show All Categories
                        </a>
                    <?php endif; ?>
                </div>
                <span class="cc-section-count"><?php echo count($products); ?> item<?php echo count($products) === 1 ? '' : 's'; ?> available</span>
            </div>

            <?php if (empty($products)): ?>
                <div class="cc-empty-state">
                    <h4 class="cc-empty-title">
                        No items found in <?php echo ($selectedCategory !== 'All') ? htmlspecialchars($selectedCategory) : 'catalog'; ?>
                    </h4>
                    <p class="cc-empty-desc">
                        <?php if ($selectedCategory !== 'All'): ?>
                            There are currently no items listed under "<?php echo htmlspecialchars($selectedCategory); ?>".
                        <?php else: ?>
                            Be the first to list an item in this category or try another search keyword.
                        <?php endif; ?>
                    </p>
                    <div style="display: flex; justify-content: center; gap: 10px; flex-wrap: wrap;">
                        <?php if ($selectedCategory !== 'All'): ?>
                            <a href="index.php" class="cc-btn cc-btn-outline-sm">View All Categories</a>
                        <?php endif; ?>
                        <a href="sell.php" class="cc-btn cc-btn-primary">Post a New Listing</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="cc-products-grid">
                    <?php foreach ($products as $item): ?>
                        <article class="cc-product-card">
                            <div class="cc-card-img-wrapper">
                                <span class="cc-card-category-badge"><?php echo htmlspecialchars($item['category']); ?></span>
                                <img 
                                    src="<?php echo htmlspecialchars($item['image_url'] ?: 'https://images.unsplash.com/photo-1544716278-ca5e3f4abd8c?auto=format&fit=crop&w=600&q=80'); ?>" 
                                    alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                    class="cc-card-img"
                                    loading="lazy"
                                >
                            </div>
                            <div class="cc-card-body">
                                <h4 class="cc-card-title" title="<?php echo htmlspecialchars($item['title']); ?>">
                                    <?php echo htmlspecialchars($item['title']); ?>
                                </h4>
                                <p class="cc-card-desc">
                                    <?php echo htmlspecialchars($item['description'] ?: 'No additional description provided.'); ?>
                                </p>
                                <div class="cc-card-meta">
                                    <span class="cc-card-seller">Seller: <?php echo htmlspecialchars(explode(' ', $item['seller_name'])[0]); ?></span>
                                    <span><?php echo date('M j', strtotime($item['created_at'])); ?></span>
                                </div>
                                <div class="cc-card-footer">
                                    <span class="cc-card-price">₹<?php echo number_format($item['price'], 2); ?></span>
                                    <form action="index.php" method="POST">
                                        <input type="hidden" name="action" value="add_to_cart">
                                        <input type="hidden" name="product_id" value="<?php echo (int)$item['id']; ?>">
                                        <button type="submit" class="cc-btn cc-btn-primary-sm">
                                            + Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div>
</main>

<!-- Reusable Footer Partial -->
<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- Dedicated JS Carousel Implementation -->
<script src="assets/js/carousel.js"></script>
</body>
</html>
