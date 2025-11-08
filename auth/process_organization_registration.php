<?php
session_start();

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
    $organization_name = trim($_POST['organization_name']);
    $organization_type = trim($_POST['organization_type']);
    $contact_person = trim($_POST['contact_person']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $website = trim($_POST['website']);
    $address = trim($_POST['address']);
    $description = trim($_POST['description']);

    // Validation
    $errors = [];

    if (empty($organization_name)) {
        $errors[] = "Organization name is required";
    }

    if (empty($organization_type)) {
        $errors[] = "Organization type is required";
    }

    if (empty($contact_person)) {
        $errors[] = "Contact person is required";
    }

    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }

    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters long";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }

    // Validate website URL if provided
    if (!empty($website) && !filter_var($website, FILTER_VALIDATE_URL)) {
        $errors[] = "Invalid website URL format";
    }

    // Check if email already exists
    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM organizations WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = "Email already exists";
        }
    }

    if (empty($errors)) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert organization
        $stmt = $pdo->prepare("INSERT INTO organizations (organization_name, organization_type, contact_person, email, password, phone, website, address, description) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        if ($stmt->execute([$organization_name, $organization_type, $contact_person, $email, $hashed_password, $phone, $website, $address, $description])) {
            $_SESSION['success_message'] = "Registration successful! You can now login.";
            header("Location: organization_login.php");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }

    // If there are errors, store them in session and redirect back
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
        header("Location: organization_register.php");
        exit();
    }
}
?>
