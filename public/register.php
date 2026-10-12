<?php
/**
 * Campus Cart - User Registration Page (register.php)
 */
session_start();
require_once __DIR__ . '/../config/db.php';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email) || empty($password)) {
        $errorMessage = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = "Please enter a valid email address.";
    } elseif (strlen($password) < 6) {
        $errorMessage = "Password must be at least 6 characters long.";
    } elseif ($password !== $confirmPassword) {
        $errorMessage = "Passwords do not match.";
    } else {
        try {
            // Check if email already registered
            $checkEmail = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $checkEmail->execute([$email]);
            
            if ($checkEmail->fetch()) {
                $errorMessage = "An account with this email address already exists. Please log in.";
            } else {
                // Hash password securely
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                
                $insertStmt = $pdo->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
                $insertStmt->execute([$name, $email, $hashedPassword]);
                $newUserId = $pdo->lastInsertId();

                // Automatically log in new user
                $_SESSION['user_id'] = $newUserId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                $_SESSION['flash_msg'] = "Registration successful! Welcome to Campus Cart, " . htmlspecialchars($name) . ".";
                $_SESSION['flash_type'] = "success";

                header("Location: index.php");
                exit;
            }
        } catch (Exception $e) {
            $errorMessage = "Registration error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - Campus Cart</title>
    <link rel="stylesheet" href="assets/css/global.css">
    <link rel="stylesheet" href="assets/css/auth.css">
</head>
<body>

<?php include __DIR__ . '/includes/navbar.php'; ?>

<main class="cc-page-wrapper">
    <div class="cc-container">
        
        <div class="cc-auth-container">
            <div class="cc-auth-header">
                <span class="cc-auth-badge">NEW ACCOUNT</span>
                <h2 class="cc-auth-title">Student Registration</h2>
                <p class="cc-auth-subtitle">Join your college marketplace network</p>
            </div>

            <div class="cc-auth-body">
                
                <?php if (!empty($errorMessage)): ?>
                    <div class="cc-alert cc-alert-danger">
                        <?php echo htmlspecialchars($errorMessage); ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="cc-form-group">
                        <label for="name" class="cc-form-label">Full Name & Major</label>
                        <input 
                            type="text" 
                            id="name" 
                            name="name" 
                            class="cc-form-input" 
                            placeholder="e.g. Maya Sharma (IT 2nd Yr)" 
                            value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" 
                            required 
                            autofocus
                        >
                    </div>

                    <div class="cc-form-group">
                        <label for="email" class="cc-form-label">Campus Email Address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="cc-form-input" 
                            placeholder="studentname@campus.edu" 
                            value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" 
                            required
                        >
                        <p class="cc-form-help">Use your institutional or active student email.</p>
                    </div>

                    <div class="cc-form-group">
                        <label for="password" class="cc-form-label">Create Password</label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            class="cc-form-input" 
                            placeholder="Minimum 6 characters" 
                            required
                        >
                    </div>

                    <div class="cc-form-group">
                        <label for="confirm_password" class="cc-form-label">Confirm Password</label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="cc-form-input" 
                            placeholder="Re-type password" 
                            required
                        >
                    </div>

                    <button type="submit" class="cc-btn cc-btn-primary cc-btn-block" style="margin-top: 10px;">
                        Create Student Account
                    </button>
                </form>
            </div>

            <div class="cc-auth-footer">
                Already have an account? <a href="login.php">Log In Here</a>
            </div>
        </div>

    </div>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

</body>
</html>
