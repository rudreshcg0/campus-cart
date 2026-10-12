<?php
/**
 * Campus Cart - User Login Page (login.php)
 */
session_start();
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errorMessage = '';
$noticeMessage = isset($_GET['notice']) ? trim($_GET['notice']) : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $errorMessage = "Please enter both student email and password.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];

                $_SESSION['flash_msg'] = "Welcome back, " . htmlspecialchars($user['name']) . "!";
                $_SESSION['flash_type'] = "success";

                header("Location: index.php");
                exit;
            } else {
                $errorMessage = "Invalid email address or password. Please verify and try again.";
            }
        } catch (Exception $e) {
            $errorMessage = "Login system error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Campus Cart</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="cc-page-wrapper">
    <div class="cc-container">
        
        <div class="cc-auth-container">
            <div class="cc-auth-header">
                <span class="cc-auth-badge">PORTAL ACCESS</span>
                <h2 class="cc-auth-title">Student Login</h2>
                <p class="cc-auth-subtitle">Sign in to buy and sell on Campus Cart</p>
            </div>

            <div class="cc-auth-body">
                
                <!-- Notice from redirect -->
                <?php if (!empty($noticeMessage)): ?>
                    <div class="cc-alert cc-alert-info">
                        <?php echo htmlspecialchars($noticeMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Error message -->
                <?php if (!empty($errorMessage)): ?>
                    <div class="cc-alert cc-alert-danger">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <!-- Demo Credentials helper for instant local XAMPP testing -->
                <div class="cc-demo-creds">
                    <strong>Quick Demo Login:</strong>
                    Email: <code>alex@campus.edu</code><br>
                    Password: <code>password123</code>
                </div>

                <form action="login.php" method="POST">
                    <div class="cc-form-group">
                        <label for="email" class="cc-form-label">Campus Email</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="cc-form-input" 
                            placeholder="student@campus.edu" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="cc-form-group">
                        <label for="password" class="cc-form-label">Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="cc-form-input" 
                            placeholder="Enter your password" 
                            required
                        >
                    </div>

                    <button type="submit" class="cc-btn cc-btn-primary cc-btn-block" style="margin-top: 10px;">
                        Log In to Account
                    </button>
                </form>
            </div>

            <div class="cc-auth-footer">
                Don't have a campus account? <a href="register.php">Register Here</a>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
