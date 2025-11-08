<?php
/**
 * Security Configuration and Helper Functions
 * Implements comprehensive security measures for the Donation & Help Request System
 */

// Set secure session parameters BEFORE starting session
ini_set('session.cookie_httponly', 1);
ini_set('session.cookie_secure', 1);
ini_set('session.use_strict_mode', 1);
ini_set('session.cookie_samesite', 'Strict');

// Security Configuration
class SecurityConfig {
    // Password requirements
    const MIN_PASSWORD_LENGTH = 8;
    const REQUIRE_UPPERCASE = true;
    const REQUIRE_LOWERCASE = true;
    const REQUIRE_NUMBERS = true;
    const REQUIRE_SPECIAL_CHARS = true;
    
    // Session security
    const SESSION_TIMEOUT = 3600; // 1 hour
    const MAX_LOGIN_ATTEMPTS = 5;
    const LOCKOUT_DURATION = 900; // 15 minutes
    
    // Input validation
    const MAX_STRING_LENGTH = 255;
    const MAX_TEXT_LENGTH = 1000;
    const MAX_AMOUNT = 999999.99;
    
    // File upload security
    const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif'];
    const MAX_FILE_SIZE = 5242880; // 5MB
    const UPLOAD_PATH = '../assets/uploads/';
}

/**
 * Enhanced Authentication Class
 */
class Authentication {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Enhanced password validation
     */
    public function validatePassword($password) {
        $errors = [];
        
        if (strlen($password) < SecurityConfig::MIN_PASSWORD_LENGTH) {
            $errors[] = "Password must be at least " . SecurityConfig::MIN_PASSWORD_LENGTH . " characters long";
        }
        
        if (SecurityConfig::REQUIRE_UPPERCASE && !preg_match('/[A-Z]/', $password)) {
            $errors[] = "Password must contain at least one uppercase letter";
        }
        
        if (SecurityConfig::REQUIRE_LOWERCASE && !preg_match('/[a-z]/', $password)) {
            $errors[] = "Password must contain at least one lowercase letter";
        }
        
        if (SecurityConfig::REQUIRE_NUMBERS && !preg_match('/[0-9]/', $password)) {
            $errors[] = "Password must contain at least one number";
        }
        
        if (SecurityConfig::REQUIRE_SPECIAL_CHARS && !preg_match('/[^A-Za-z0-9]/', $password)) {
            $errors[] = "Password must contain at least one special character";
        }
        
        return $errors;
    }
    
    /**
     * Check login attempts and implement lockout
     */
    public function checkLoginAttempts($email, $userType) {
        $table = ($userType === 'individual') ? 'individuals' : 'organizations';
        
        // Check if user is locked out
        $stmt = $this->pdo->prepare("
            SELECT login_attempts, last_attempt_time 
            FROM {$table} 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            $lastAttempt = strtotime($user['last_attempt_time']);
            $lockoutExpiry = $lastAttempt + SecurityConfig::LOCKOUT_DURATION;
            
            if ($user['login_attempts'] >= SecurityConfig::MAX_LOGIN_ATTEMPTS && time() < $lockoutExpiry) {
                return false; // User is locked out
            }
        }
        
        return true;
    }
    
    /**
     * Record failed login attempt
     */
    public function recordFailedAttempt($email, $userType) {
        $table = ($userType === 'individual') ? 'individuals' : 'organizations';
        
        $stmt = $this->pdo->prepare("
            UPDATE {$table} 
            SET login_attempts = login_attempts + 1, 
                last_attempt_time = NOW() 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
    }
    
    /**
     * Reset login attempts on successful login
     */
    public function resetLoginAttempts($email, $userType) {
        $table = ($userType === 'individual') ? 'individuals' : 'organizations';
        
        $stmt = $this->pdo->prepare("
            UPDATE {$table} 
            SET login_attempts = 0, 
                last_attempt_time = NULL 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
    }
    
    /**
     * Enhanced login with security checks
     */
    public function login($email, $password, $userType) {
        $errors = [];
        
        // Check login attempts
        if (!$this->checkLoginAttempts($email, $userType)) {
            $errors[] = "Account temporarily locked due to too many failed login attempts. Please try again later.";
            return ['success' => false, 'errors' => $errors];
        }
        
        $table = ($userType === 'individual') ? 'individuals' : 'organizations';
        $nameField = ($userType === 'individual') ? 'CONCAT(first_name, " ", last_name)' : 'organization_name';
        
        $stmt = $this->pdo->prepare("
            SELECT id, {$nameField} as name, email, password 
            FROM {$table} 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password'])) {
            // Successful login
            $this->resetLoginAttempts($email, $userType);
            
            // Set secure session
            $this->setSecureSession($user, $userType);
            
            return ['success' => true, 'user' => $user];
        } else {
            // Failed login
            $this->recordFailedAttempt($email, $userType);
            $errors[] = "Invalid email or password";
            return ['success' => false, 'errors' => $errors];
        }
    }
    
    /**
     * Set secure session with additional security measures
     */
    private function setSecureSession($user, $userType) {
        // Regenerate session ID to prevent session fixation
        session_regenerate_id(true);
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $userType;
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['login_time'] = time();
        $_SESSION['last_activity'] = time();
        
        // Set session timeout
        $_SESSION['timeout'] = time() + SecurityConfig::SESSION_TIMEOUT;
    }
    
    /**
     * Check if session is valid and not expired
     */
    public function isSessionValid() {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['timeout'])) {
            return false;
        }
        
        // Check session timeout
        if (time() > $_SESSION['timeout']) {
            $this->logout();
            return false;
        }
        
        // Update last activity
        $_SESSION['last_activity'] = time();
        
        return true;
    }
    
    /**
     * Secure logout
     */
    public function logout() {
        // Clear all session variables
        $_SESSION = array();
        
        // Destroy session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
}

/**
 * Authorization Class for Role-Based Access Control
 */
class Authorization {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Check if user has permission to access a resource
     */
    public function hasPermission($userId, $userType, $resource, $action = 'read') {
        // Define role-based permissions
        $permissions = [
            'individual' => [
                'dashboard' => ['read'],
                'donations' => ['create', 'read'],
                'help_requests' => ['create', 'read'],
                'profile' => ['read', 'update']
            ],
            'organization' => [
                'dashboard' => ['read'],
                'donations' => ['read', 'update'],
                'help_requests' => ['read', 'update'],
                'photos' => ['create', 'read', 'delete'],
                'profile' => ['read', 'update']
            ]
        ];
        
        if (!isset($permissions[$userType][$resource])) {
            return false;
        }
        
        return in_array($action, $permissions[$userType][$resource]);
    }
    
    /**
     * Check if user can access specific data
     */
    public function canAccessData($userId, $userType, $dataType, $dataId) {
        switch ($dataType) {
            case 'donation':
                if ($userType === 'individual') {
                    // Individuals can only see their own donations
                    $stmt = $this->pdo->prepare("SELECT individual_id FROM donations WHERE id = ?");
                    $stmt->execute([$dataId]);
                    $donation = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $donation && $donation['individual_id'] == $userId;
                } elseif ($userType === 'organization') {
                    // Organizations can see donations made to them
                    $stmt = $this->pdo->prepare("SELECT organization_id FROM donations WHERE id = ?");
                    $stmt->execute([$dataId]);
                    $donation = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $donation && $donation['organization_id'] == $userId;
                }
                break;
                
            case 'help_request':
                if ($userType === 'individual') {
                    // Individuals can only see their own requests
                    $stmt = $this->pdo->prepare("SELECT individual_id FROM help_requests WHERE id = ?");
                    $stmt->execute([$dataId]);
                    $request = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $request && $request['individual_id'] == $userId;
                } elseif ($userType === 'organization') {
                    // Organizations can see requests assigned to them
                    $stmt = $this->pdo->prepare("SELECT organization_id FROM help_requests WHERE id = ?");
                    $stmt->execute([$dataId]);
                    $request = $stmt->fetch(PDO::FETCH_ASSOC);
                    return $request && $request['organization_id'] == $userId;
                }
                break;
        }
        
        return false;
    }
    
    /**
     * Require authentication and specific permission
     */
    public function requireAuth($userType = null, $resource = null, $action = 'read') {
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
            header("Location: ../auth/individual_login.php");
            exit();
        }
        
        if ($userType && $_SESSION['user_type'] !== $userType) {
            header("Location: ../core/index.php");
            exit();
        }
        
        if ($resource && !$this->hasPermission($_SESSION['user_id'], $_SESSION['user_type'], $resource, $action)) {
            header("Location: ../core/index.php");
            exit();
        }
        
        return true;
    }
}

/**
 * Input Validation and Sanitization Class
 */
class InputValidator {
    
    /**
     * Sanitize string input
     */
    public static function sanitizeString($input, $maxLength = SecurityConfig::MAX_STRING_LENGTH) {
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $input = substr($input, 0, $maxLength);
        return $input;
    }
    
    /**
     * Sanitize text input (for longer content)
     */
    public static function sanitizeText($input, $maxLength = SecurityConfig::MAX_TEXT_LENGTH) {
        $input = trim($input);
        $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
        $input = substr($input, 0, $maxLength);
        return $input;
    }
    
    /**
     * Validate and sanitize email
     */
    public static function validateEmail($email) {
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }
        
        return $email;
    }
    
    /**
     * Validate and sanitize numeric input
     */
    public static function validateNumber($input, $min = 0, $max = SecurityConfig::MAX_AMOUNT) {
        $input = trim($input);
        
        if (!is_numeric($input)) {
            return false;
        }
        
        $number = floatval($input);
        
        if ($number < $min || $number > $max) {
            return false;
        }
        
        return $number;
    }
    
    /**
     * Validate phone number
     */
    public static function validatePhone($phone) {
        $phone = trim($phone);
        $phone = preg_replace('/[^0-9+\-\(\)\s]/', '', $phone);
        
        if (strlen($phone) < 10 || strlen($phone) > 20) {
            return false;
        }
        
        return $phone;
    }
    
    /**
     * Validate file upload
     */
    public static function validateFileUpload($file, $allowedTypes = SecurityConfig::ALLOWED_IMAGE_TYPES) {
        $errors = [];
        
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "File upload error";
            return ['valid' => false, 'errors' => $errors];
        }
        
        if ($file['size'] > SecurityConfig::MAX_FILE_SIZE) {
            $errors[] = "File size too large. Maximum size is " . (SecurityConfig::MAX_FILE_SIZE / 1024 / 1024) . "MB";
        }
        
        $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($fileExtension, $allowedTypes)) {
            $errors[] = "Invalid file type. Allowed types: " . implode(', ', $allowedTypes);
        }
        
        // Check MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedMimeTypes = [
            'image/jpeg',
            'image/jpg', 
            'image/png',
            'image/gif'
        ];
        
        if (!in_array($mimeType, $allowedMimeTypes)) {
            $errors[] = "Invalid file type detected";
        }
        
        return ['valid' => empty($errors), 'errors' => $errors];
    }
    
    /**
     * Generate secure filename
     */
    public static function generateSecureFilename($originalName) {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $filename = uniqid() . '_' . time() . '.' . $extension;
        return $filename;
    }
}

/**
 * CSRF Protection Class
 */
class CSRFProtection {
    
    /**
     * Generate CSRF token
     */
    public static function generateToken() {
        if (!isset($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateToken($token) {
        return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
    
    /**
     * Generate CSRF input field
     */
    public static function generateInputField() {
        $token = self::generateToken();
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
    }
}

// Initialize security features
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
