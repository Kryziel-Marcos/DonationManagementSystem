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

// Check if user is logged in
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: ../auth/individual_login.php");
    exit();
}

$user_type = $_SESSION['user_type'];
$user_id = $_SESSION['user_id'];

// Validate user type
if (!in_array($user_type, ['individual', 'organization'])) {
    $_SESSION['errors'] = ['Invalid user type.'];
    header("Location: ../auth/individual_login.php");
    exit();
}

// Check session validity
if (!$auth->isSessionValid()) {
    header("Location: ../auth/individual_login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF Protection
    if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
        $_SESSION['errors'] = ["Invalid request. Please try again."];
        $redirect_url = $user_type === 'individual' 
            ? '../dashboard/individual_dashboard.php' 
            : '../dashboard/organization_dashboard.php';
        header("Location: " . $redirect_url);
        exit();
    }
    
    $errors = [];
    
    // Validate file upload
    if (!isset($_FILES['profile_photo']) || $_FILES['profile_photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please select a valid photo file.";
    } else {
        $file = $_FILES['profile_photo'];
        
        // Check file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file['type'], $allowed_types)) {
            $errors[] = "Only JPEG, PNG, and GIF images are allowed.";
        }
        
        // Check file size (max 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            $errors[] = "File size must be less than 5MB.";
        }
    }
    
    if (empty($errors)) {
        // Create uploads directory if it doesn't exist
        $upload_dir = '../assets/uploads/profile_photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Get old profile photo path if exists
        $table_name = $user_type === 'individual' ? 'individuals' : 'organizations';
        $old_photo_stmt = $pdo->prepare("SELECT profile_photo FROM {$table_name} WHERE id = ?");
        $old_photo_stmt->execute([$user_id]);
        $old_photo = $old_photo_stmt->fetchColumn();
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = $user_type . '_' . $user_id . '_' . uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                // Update profile photo in database
                $stmt = $pdo->prepare("UPDATE {$table_name} SET profile_photo = ? WHERE id = ?");
                $stmt->execute([$file_path, $user_id]);
                
                // Delete old profile photo if it exists
                if ($old_photo && file_exists($old_photo) && $old_photo !== $file_path) {
                    unlink($old_photo);
                }
                
                $_SESSION['success_message'] = "Profile photo uploaded successfully!";
            } catch(PDOException $e) {
                // Delete uploaded file if database update fails
                unlink($file_path);
                $errors[] = "Failed to save profile photo. Please try again.";
            }
        } else {
            $errors[] = "Failed to upload photo. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
}

// Redirect back to appropriate dashboard
$redirect_url = $user_type === 'individual' 
    ? '../dashboard/individual_dashboard.php' 
    : '../dashboard/organization_dashboard.php';
    
header("Location: " . $redirect_url);
exit();
?>

