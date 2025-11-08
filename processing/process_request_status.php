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
if (!$auth->isSessionValid() || !$authorization->requireAuth('organization', 'help_requests', 'update')) {
    header("Location: ../auth/organization_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF Protection
    if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        header("Location: ../dashboard/organization_dashboard.php");
        exit();
    }
    
    $request_id = InputValidator::validateNumber($_POST['request_id'] ?? '');
    $status = $_POST['status'] ?? '';
    $organization_id = $_SESSION['user_id'];
    
    // Validate status
    $valid_statuses = ['approved', 'rejected', 'completed'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['errors'] = ["Invalid status"];
        header("Location: ../dashboard/organization_dashboard.php");
        exit();
    }
    
    // Verify that the request belongs to this organization
    $check_stmt = $pdo->prepare("SELECT id, organization_id FROM help_requests WHERE id = ?");
    $check_stmt->execute([$request_id]);
    $request_data = $check_stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$request_data) {
        $_SESSION['errors'] = ["Help request not found."];
    } elseif ($request_data['organization_id'] != $organization_id) {
        $_SESSION['errors'] = ["You can only update help requests sent to your organization."];
    } else {
        // Update help request status (organization_id is already set, just update status)
        $stmt = $pdo->prepare("UPDATE help_requests SET status = ?, response_date = NOW() WHERE id = ? AND organization_id = ?");
        
        if ($stmt->execute([$status, $request_id, $organization_id])) {
            $_SESSION['success_message'] = "Help request status updated successfully!";
        } else {
            $_SESSION['errors'] = ["Failed to update help request status. Please try again."];
        }
    }

    header("Location: ../dashboard/organization_dashboard.php");
    exit();
}
?>
