<?php
// Include security configuration (handles session_start)
require_once '../core/security.php';

// Database configuration
$host = 'localhost';
$dbname = 'donation_system';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        header("Location: reset_password.php");
        exit();
    }
    
    $token = $_POST['token'] ?? '';
    $email = InputValidator::validateEmail($_POST['email'] ?? '');
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    $errors = [];
    
    if (empty($token)) {
        $errors[] = "Reset token is required";
    }
    
    if (!$email) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($new_password)) {
        $errors[] = "Password is required";
    } else {
        // Enhanced password validation
        $auth = new Authentication($pdo);
        $passwordErrors = $auth->validatePassword($new_password);
        $errors = array_merge($errors, $passwordErrors);
    }
    
    if ($new_password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($errors)) {
        // Verify token
        $stmt = $pdo->prepare("SELECT email, expires_at, used FROM password_resets WHERE token = ? AND email = ?");
        $stmt->execute([$token, $email]);
        $reset_data = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$reset_data) {
            $errors[] = "Invalid reset token or email address";
        } elseif ($reset_data['used']) {
            $errors[] = "This reset token has already been used";
        } elseif (strtotime($reset_data['expires_at']) < time()) {
            $errors[] = "This reset token has expired. Please request a new one.";
        }
    }
    
    if (empty($errors)) {
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password in individuals table
        $update_stmt = $pdo->prepare("UPDATE individuals SET password = ? WHERE email = ?");
        
        if ($update_stmt->execute([$hashed_password, $email])) {
            // Mark token as used
            $mark_stmt = $pdo->prepare("UPDATE password_resets SET used = TRUE WHERE token = ?");
            $mark_stmt->execute([$token]);
            
            // Reset login attempts when password is reset
            $reset_attempts = $pdo->prepare("UPDATE individuals SET login_attempts = 0, account_locked = FALSE WHERE email = ?");
            $reset_attempts->execute([$email]);
            
            $_SESSION['success_message'] = "Password reset successfully! You can now login with your new password.";
            unset($_SESSION['reset_token']);
            unset($_SESSION['reset_email']);
            header("Location: individual_login.php");
            exit();
        } else {
            $errors[] = "Failed to update password. Please try again.";
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }
}
?>

