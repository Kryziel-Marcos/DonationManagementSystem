<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Login - Donation System</title>
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
            <h1>Organization Login</h1>
            <p>Access your organization account to manage donations and help requests</p>
            <div class="login-features">
                <div class="feature-item">
                    <span class="feature-icon">💰</span>
                    <span>Receive Donations</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📋</span>
                    <span>Manage Requests</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📸</span>
                    <span>Upload Photos</span>
                </div>
            </div>
        </header>

        <main class="form-container">
            <?php
            require_once '../core/security.php';
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success">' . $_SESSION['success_message'] . '</div>';
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

            <form action="process_organization_login.php" method="POST" class="login-form">
                <?php echo CSRFProtection::generateInputField(); ?>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-input-wrapper">
                        <input type="password" id="password" name="password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword('password')">
                            <span class="eye-icon">👁️</span>
                        </button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Login</button>
                    <a href="organization_register.php" class="btn btn-secondary">Register New Organization</a>
                    <a href="../core/index.php" class="btn btn-secondary">Back to Home</a>
                </div>
            </form>
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
    </script>
