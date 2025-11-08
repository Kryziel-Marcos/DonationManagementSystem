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
        header("Location: individual_login.php");
        exit();
    }
    
    $email = InputValidator::validateEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    $errors = [];
    
    if (!$email) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    }
    
    if (empty($errors)) {
        // Use enhanced authentication
        $auth = new Authentication($pdo);
        $result = $auth->login($email, $password, 'individual');
        
        if ($result['success']) {
            header("Location: ../dashboard/individual_dashboard.php");
            exit();
        } else {
            $errors = $result['errors'];
        }
    }
    
    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: individual_login.php");
        exit();
    }
}
?>
