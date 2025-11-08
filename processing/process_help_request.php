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
if (!$auth->isSessionValid() || !$authorization->requireAuth('individual', 'help_requests', 'create')) {
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
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $amount_requested = $_POST['amount_requested'] ?? '';
    $individual_id = $_SESSION['user_id'];

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
        }
    }

    if (empty($title)) {
        $errors[] = "Title is required";
    }

    if (empty($description)) {
        $errors[] = "Description is required";
    }

    if (!empty($amount_requested) && $amount_requested < 0) {
        $errors[] = "Amount requested must be positive";
    }

    if (empty($errors)) {
        // Insert help request with organization_id
        $stmt = $pdo->prepare("INSERT INTO help_requests (individual_id, organization_id, title, description, amount_requested, status) VALUES (?, ?, ?, ?, ?, 'pending')");
        
        $amount_value = !empty($amount_requested) ? $amount_requested : null;
        
        if ($stmt->execute([$individual_id, $organization_id, $title, $description, $amount_value])) {
            $_SESSION['success_message'] = "Help request submitted successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to submit help request. Please try again."];
        }
    } else {
        $_SESSION['errors'] = $errors;
    }

    header("Location: ../dashboard/individual_dashboard.php");
    exit();
}
?>
