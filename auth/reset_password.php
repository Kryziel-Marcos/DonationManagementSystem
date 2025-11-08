<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Donation System</title>
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
            <h1>🔑 Reset Password</h1>
            <p>Enter your new password</p>
        </header>

        <main class="form-container">
            <?php
            require_once '../core/security.php';
            
            // Get token from URL or session
            $token = $_GET['token'] ?? $_SESSION['reset_token'] ?? '';
            
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
            
            // Validate token if provided
            if ($token) {
                $host = 'localhost';
                $dbname = 'donation_system';
                $username = 'root';
                $password = '';
                
                try {
                    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
                    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                    
                    $stmt = $pdo->prepare("SELECT email, expires_at, used FROM password_resets WHERE token = ?");
                    $stmt->execute([$token]);
                    $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);
                    
                    if (!$reset_data) {
                        echo '<div class="alert alert-error"><p>Invalid or expired reset token. Please request a new one.</p></div>';
                        echo '<div class="form-actions"><a href="forgot_password.php" class="btn btn-primary">Request New Token</a></div>';
                        exit;
                    }
                    
                    if ($reset_data['used']) {
                        echo '<div class="alert alert-error"><p>This reset token has already been used. Please request a new one.</p></div>';
                        echo '<div class="form-actions"><a href="forgot_password.php" class="btn btn-primary">Request New Token</a></div>';
                        exit;
                    }
                    
                    if (strtotime($reset_data['expires_at']) < time()) {
                        echo '<div class="alert alert-error"><p>This reset token has expired. Please request a new one.</p></div>';
                        echo '<div class="form-actions"><a href="forgot_password.php" class="btn btn-primary">Request New Token</a></div>';
                        exit;
                    }
                    
                    // Show form with token
                    ?>
                    <form action="process_reset_password.php" method="POST" class="login-form" id="resetForm">
                        <?php echo CSRFProtection::generateInputField(); ?>
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        
                        <div class="form-group">
                            <label for="email">Email Address:</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($reset_data['email']); ?>" required readonly>
                        </div>

                        <div class="form-group">
                            <label for="password">New Password:</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="password" name="password" required placeholder="Enter new password">
                                <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                    <span class="eye-icon">👁️</span>
                                </button>
                            </div>
                            <small class="form-hint">Password must be at least 8 characters long</small>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password:</label>
                            <div class="password-input-wrapper">
                                <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm new password">
                                <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                    <span class="eye-icon">👁️</span>
                                </button>
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Reset Password</button>
                            <a href="individual_login.php" class="btn btn-secondary">Back to Login</a>
                            <a href="forgot_password.php" class="btn btn-secondary">Request New Token</a>
                        </div>
                    </form>
                <?php
                } catch(PDOException $e) {
                    echo '<div class="alert alert-error"><p>Database error. Please try again later.</p></div>';
                }
            } else {
                // No token provided
                echo '<div class="alert alert-error"><p>No reset token provided. Please request a password reset.</p></div>';
                echo '<div class="form-actions"><a href="forgot_password.php" class="btn btn-primary">Request Password Reset</a></div>';
            }
            ?>
        </main>
    </div>

    <script>
        // Password visibility toggle
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleButton = passwordField.nextElementSibling;
            const eyeIcon = toggleButton.querySelector('.eye-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }
        
        // Password match validation
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const password = document.getElementById('password').value;
                    const confirmPassword = document.getElementById('confirm_password').value;
                    
                    if (password !== confirmPassword) {
                        e.preventDefault();
                        alert('Passwords do not match!');
                        return false;
                    }
                });
            }
        });
    </script>
</body>
</html>

