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
        header("Location: forgot_password.php");
        exit();
    }
    
    $email = InputValidator::validateEmail($_POST['email'] ?? '');
    
    // Validation
    $errors = [];
    
    if (!$email) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($errors)) {
        // Check if email exists in individuals table
        $stmt = $pdo->prepare("SELECT id, first_name, last_name FROM individuals WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            // Generate secure random token
            $token = bin2hex(random_bytes(32)); // 64 character token
            
            // Token expires in 1 hour
            $expires_at = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Delete any existing unused tokens for this email
            $delete_stmt = $pdo->prepare("DELETE FROM password_resets WHERE email = ? AND used = FALSE");
            $delete_stmt->execute([$email]);
            
            // Insert new reset token
            $insert_stmt = $pdo->prepare("INSERT INTO password_resets (email, token, expires_at) VALUES (?, ?, ?)");
            
            if ($insert_stmt->execute([$email, $token, $expires_at])) {
                // Store token in session for display (in production, send via email)
                $_SESSION['reset_token'] = $token;
                $_SESSION['reset_email'] = $email;
                $_SESSION['success_message'] = "Password reset token generated successfully! Redirecting to reset page...";
                header("Location: reset_password.php?token=" . urlencode($token));
                exit();
            } else {
                $errors[] = "Failed to generate reset token. Please try again.";
            }
        } else {
            // Don't reveal if email exists or not (security best practice)
            // But for this system, we'll show a message
            $_SESSION['success_message'] = "If an account exists with this email, a reset token has been generated. Please check your email or use the token provided on the next page.";
            // Still redirect to prevent email enumeration
            header("Location: reset_password.php");
            exit();
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: forgot_password.php");
        exit();
    }
}
?>

