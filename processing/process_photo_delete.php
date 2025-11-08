<?php
session_start();

// Check if user is logged in as organization
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'organization') {
    header("Location: ../auth/organization_login.php");
    exit();
}

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

$organization_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['photo_id'])) {
    $photo_id = (int)$_POST['photo_id'];
    
    try {
        // First, get the photo details to verify ownership and get file path
        $stmt = $pdo->prepare("
            SELECT photo_path FROM documentary_photos 
            WHERE id = ? AND organization_id = ?
        ");
        $stmt->execute([$photo_id, $organization_id]);
        $photo = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($photo) {
            // Delete the photo file from server
            if (file_exists($photo['photo_path'])) {
                unlink($photo['photo_path']);
            }
            
            // Delete the photo record from database
            $delete_stmt = $pdo->prepare("DELETE FROM documentary_photos WHERE id = ? AND organization_id = ?");
            $delete_stmt->execute([$photo_id, $organization_id]);
            
            $_SESSION['success_message'] = "Photo deleted successfully!";
        } else {
            $_SESSION['errors'] = ['Photo not found or you do not have permission to delete it.'];
        }
    } catch(PDOException $e) {
        $_SESSION['errors'] = ['Failed to delete photo. Please try again.'];
    }
} else {
    $_SESSION['errors'] = ['Invalid request.'];
}

header("Location: ../dashboard/organization_dashboard.php");
exit();
?>
