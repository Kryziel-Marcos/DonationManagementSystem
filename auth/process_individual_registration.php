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
        header("Location: individual_register.php");
        exit();
    }
    
    $first_name = InputValidator::sanitizeString($_POST['first_name'] ?? '');
    $last_name = InputValidator::sanitizeString($_POST['last_name'] ?? '');
    $email = InputValidator::validateEmail($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $phone = InputValidator::validatePhone($_POST['phone'] ?? '');
    $address = InputValidator::sanitizeText($_POST['address'] ?? '');

    // Validation
    $errors = [];

    if (empty($first_name)) {
        $errors[] = "First name is required";
    }

    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }

    if (!$email) {
        $errors[] = "Valid email is required";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } else {
        // Enhanced password validation
        $auth = new Authentication($pdo);
        $passwordErrors = $auth->validatePassword($password);
        $errors = array_merge($errors, $passwordErrors);
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    if ($phone && !$phone) {
        $errors[] = "Invalid phone number format";
    }

    // Check if email already exists
    if (!$errors) {
        $stmt = $pdo->prepare("SELECT id FROM individuals WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists";
        }
    }

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert user
        $stmt = $pdo->prepare("INSERT INTO individuals (first_name, last_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$first_name, $last_name, $email, $hashed_password, $phone, $address])) {
            $_SESSION['success_message'] = "🎉 Registration successful! Welcome to our community. You can now login with your credentials.";
            header("Location: individual_login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: individual_register.php");
        exit();
    }
}
?>
