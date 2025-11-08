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

// Enhanced authorization check
$auth = new Authentication($pdo);
$authorization = new Authorization($pdo);

// Check if user is logged in and has permission
if (!$auth->isSessionValid() || !$authorization->requireAuth('individual', 'donations', 'create')) {
    header("Location: ../auth/individual_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        header("Location: ../dashboard/individual_dashboard.php");
        exit();
    }
    
    $organization_id = InputValidator::validateNumber($_POST['organization_id'] ?? '');
    $donation_type = $_POST['donation_type'] ?? 'money';
    $amount = null;
    $description = null;
    $individual_id = $_SESSION['user_id'];

    // Validate donation type
    $valid_types = ['money', 'clothes', 'food', 'blood', 'other'];
    if (!in_array($donation_type, $valid_types)) {
        $donation_type = 'money';
    }

    // Validation
    $errors = [];

    if (!$organization_id || $organization_id <= 0) {
        $errors[] = "Please select a valid organization";
    } else {
        // Verify that the organization exists in the database
        $org_check = $pdo->prepare("SELECT id, organization_name FROM organizations WHERE id = ?");
        $org_check->execute([$organization_id]);
        $org_data = $org_check->fetch(PDO::FETCH_ASSOC);
        
        if (!$org_data) {
            $errors[] = "Selected organization does not exist. Please select a valid organization.";
            $organization_id = null;
        } else {
            // Use the organization name from database
            $organization_name = $org_data['organization_name'];
        }
    }

    // For money donations, amount is required
    if ($donation_type === 'money') {
        $amount = InputValidator::validateNumber($_POST['amount'] ?? '', 1);
        if (!$amount || $amount <= 0) {
            $errors[] = "Please enter a valid amount for monetary donations";
        }
    } else {
        // For non-monetary donations, description is required
        $description = trim($_POST['description'] ?? '');
        if (empty($description)) {
            $errors[] = "Please provide a description for your donation";
        }
        // Optional amount for non-monetary donations
        if (!empty($_POST['amount'])) {
            $amount = InputValidator::validateNumber($_POST['amount'] ?? '', 1);
        }
    }

    if (empty($errors)) {
        // Get user email for email notification
        $user_stmt = $pdo->prepare("SELECT email, first_name, last_name FROM individuals WHERE id = ?");
        $user_stmt->execute([$individual_id]);
        $user_data = $user_stmt->fetch(PDO::FETCH_ASSOC);
        
        // Insert donation
        $stmt = $pdo->prepare("INSERT INTO donations (individual_id, organization_id, donation_type, amount, description, status) VALUES (?, ?, ?, ?, ?, 'completed')");
        
        if ($stmt->execute([$individual_id, $organization_id, $donation_type, $amount, $description])) {
            $donation_id = $pdo->lastInsertId();
            
            // Store donation details in session for email notification
            $_SESSION['donation_notification'] = [
                'userEmail' => $user_data['email'] ?? $_SESSION['user_email'] ?? '',
                'userName' => trim(($user_data['first_name'] ?? '') . ' ' . ($user_data['last_name'] ?? '')) ?: $_SESSION['user_name'] ?? 'Valued Donor',
                'organizationName' => $organization_name ?? '',
                'donationType' => $donation_type,
                'amount' => $amount,
                'description' => $description,
                'donationDate' => date('F j, Y'),
                'donationId' => $donation_id
            ];
            
            $_SESSION['success_message'] = "Donation submitted successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to submit donation. Please try again."];
        }
    } else {
        $_SESSION['errors'] = $errors;
    }

    header("Location: ../dashboard/individual_dashboard.php");
    exit();
}
?>
