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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    // Validate form data
    if (empty($_POST['title'])) {
        $errors[] = "Photo title is required.";
    }
    
    if (empty($_POST['description'])) {
        $errors[] = "Photo description is required.";
    }
    
    // Validate file upload
    if (!isset($_FILES['photo']) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = "Please select a valid photo file.";
    } else {
        $file = $_FILES['photo'];
        
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
        $upload_dir = '../assets/uploads/documentary_photos/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        // Generate unique filename
        $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $unique_filename = uniqid() . '_' . time() . '.' . $file_extension;
        $file_path = $upload_dir . $unique_filename;
        
        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $file_path)) {
            try {
                // Insert photo record into database
                $stmt = $pdo->prepare("
                    INSERT INTO documentary_photos (organization_id, title, description, photo_path) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([
                    $organization_id,
                    $_POST['title'],
                    $_POST['description'],
                    $file_path
                ]);
                
                $_SESSION['success_message'] = "Photo uploaded successfully!";
            } catch(PDOException $e) {
                // Delete uploaded file if database insert fails
                unlink($file_path);
                $errors[] = "Failed to save photo information. Please try again.";
            }
        } else {
            $errors[] = "Failed to upload photo. Please try again.";
        }
    }
    
    if (!empty($errors)) {
        $_SESSION['errors'] = $errors;
    }
}

header("Location: ../dashboard/organization_dashboard.php");
exit();
?>
