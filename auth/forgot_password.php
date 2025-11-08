<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Donation System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/notifications.js"></script>
    <script src="../assets/theme-toggle.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔐 Forgot Password</h1>
            <p>Reset your password to regain access to your account</p>
        </header>

        <main class="form-container">
            <?php
            require_once '../core/security.php';
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['errors'])) {
                echo '<div class="alert alert-error">';
                foreach ($_SESSION['errors'] as $error) {
                    echo '<p>' . htmlspecialchars($error) . '</p>';
                }
                echo '</div>';
                unset($_SESSION['errors']);
            }
            ?>

            <form action="process_forgot_password.php" method="POST" class="login-form">
                <?php echo CSRFProtection::generateInputField(); ?>
                <div class="form-group">
                    <label for="email">Email Address:</label>
                    <input type="email" id="email" name="email" required placeholder="Enter your registered email">
                    <small class="form-hint">Enter the email address associated with your account</small>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Send Reset Token</button>
                    <a href="individual_login.php" class="btn btn-secondary">Back to Login</a>
                    <a href="../core/index.php" class="btn btn-secondary">Back to Home</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>

