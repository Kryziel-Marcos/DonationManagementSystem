<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Demonstration - Donation System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/notifications.js"></script>
    <script src="../assets/theme-toggle.js"></script>
    <style>
        .security-demo {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .security-section {
            background: white;
            border-radius: 12px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .security-feature {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #007bff;
        }
        .security-icon {
            font-size: 24px;
            margin-right: 15px;
        }
        .code-block {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin: 15px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            overflow-x: auto;
        }
        .demo-buttons {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }
        .demo-button {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            text-decoration: none;
            display: inline-block;
            transition: all 0.3s ease;
        }
        .demo-button.primary {
            background: linear-gradient(135deg, #007bff, #0056b3);
            color: white;
        }
        .demo-button.secondary {
            background: #6c757d;
            color: white;
        }
        .demo-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .permission-matrix {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        .permission-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .permission-card.allowed {
            border-left: 4px solid #28a745;
        }
        .permission-card.denied {
            border-left: 4px solid #dc3545;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>🔒 Security Demonstration</h1>
            <p>Comprehensive Security Implementation for Donation & Help Request System</p>
        </header>

        <main class="security-demo">
            <!-- Authentication Section -->
            <section class="security-section">
                <h2>🔐 Authentication (Login & User Roles)</h2>
                <p>Enhanced authentication system with role-based access control and security measures.</p>
                
                <div class="security-feature">
                    <div class="security-icon">🛡️</div>
                    <div>
                        <h4>Secure Password Requirements</h4>
                        <p>Minimum 8 characters, uppercase, lowercase, numbers, and special characters required.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">⏰</div>
                    <div>
                        <h4>Session Management</h4>
                        <p>Automatic session timeout (1 hour), secure session regeneration, and activity tracking.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">🚫</div>
                    <div>
                        <h4>Account Lockout Protection</h4>
                        <p>Maximum 5 failed login attempts with 15-minute lockout period.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">👥</div>
                    <div>
                        <h4>Role-Based Access</h4>
                        <p>Two distinct user types: Individuals and Organizations with specific permissions.</p>
                    </div>
                </div>

                <div class="demo-buttons">
                    <a href="../auth/individual_login.php" class="demo-button primary">Test Individual Login</a>
                    <a href="../auth/organization_login.php" class="demo-button secondary">Test Organization Login</a>
                </div>
            </section>

            <!-- Authorization Section -->
            <section class="security-section">
                <h2>🔑 Authorization (Access Control)</h2>
                <p>Role-based permission system ensuring users can only access authorized resources.</p>
                
                <h3>Permission Matrix</h3>
                <div class="permission-matrix">
                    <div class="permission-card allowed">
                        <h4>Individual Permissions</h4>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li>✓ Create donations</li>
                            <li>✓ View own donations</li>
                            <li>✓ Create help requests</li>
                            <li>✓ View own requests</li>
                            <li>✓ Update profile</li>
                        </ul>
                    </div>
                    <div class="permission-card allowed">
                        <h4>Organization Permissions</h4>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li>✓ View donations to them</li>
                            <li>✓ Update donation status</li>
                            <li>✓ View help requests</li>
                            <li>✓ Update request status</li>
                            <li>✓ Upload photos</li>
                            <li>✓ Delete photos</li>
                        </ul>
                    </div>
                    <div class="permission-card denied">
                        <h4>Restricted Access</h4>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li>✗ Cross-user data access</li>
                            <li>✗ Unauthorized operations</li>
                            <li>✗ Session hijacking</li>
                            <li>✗ Privilege escalation</li>
                        </ul>
                    </div>
                </div>

                <div class="demo-buttons">
                    <a href="../dashboard/individual_dashboard.php" class="demo-button primary">Individual Dashboard</a>
                    <a href="../dashboard/organization_dashboard.php" class="demo-button secondary">Organization Dashboard</a>
                </div>
            </section>

            <!-- Input Validation Section -->
            <section class="security-section">
                <h2>🛡️ Input Validation & Sanitization</h2>
                <p>Comprehensive input validation and sanitization to prevent security vulnerabilities.</p>
                
                <div class="security-feature">
                    <div class="security-icon">📧</div>
                    <div>
                        <h4>Email Validation</h4>
                        <p>Server-side email format validation and sanitization using PHP filters.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">🔢</div>
                    <div>
                        <h4>Numeric Input Validation</h4>
                        <p>Amount validation with min/max limits, preventing negative values and overflow.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">📝</div>
                    <div>
                        <h4>Text Sanitization</h4>
                        <p>HTML entity encoding, length limits, and XSS prevention for all text inputs.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">📁</div>
                    <div>
                        <h4>File Upload Security</h4>
                        <p>MIME type validation, file size limits, and secure filename generation.</p>
                    </div>
                </div>
                
                <div class="security-feature">
                    <div class="security-icon">🛡️</div>
                    <div>
                        <h4>CSRF Protection</h4>
                        <p>Cross-Site Request Forgery protection with secure token validation.</p>
                    </div>
                </div>
            </section>

            <!-- Code Examples Section -->
            <section class="security-section">
                <h2>💻 Security Code Snippets</h2>
                <p>Key security implementations demonstrating best practices.</p>
                
                <h3>Enhanced Password Validation</h3>
                <div class="code-block">
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
                </div>

                <h3>CSRF Protection Implementation</h3>
                <div class="code-block">
// Generate CSRF token
public static function generateToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Validate CSRF token
public static function validateToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Usage in forms
if (!CSRFProtection::validateToken($_POST['csrf_token'] ?? '')) {
    $_SESSION['errors'] = ["Invalid request. Please try again."];
    header("Location: login.php");
    exit();
}
                </div>

                <h3>Input Sanitization</h3>
                <div class="code-block">
public static function sanitizeString($input, $maxLength = SecurityConfig::MAX_STRING_LENGTH) {
    $input = trim($input);
    $input = htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
    $input = substr($input, 0, $maxLength);
    return $input;
}

public static function validateEmail($email) {
    $email = trim($email);
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    
    return $email;
}
                </div>

                <h3>Role-Based Authorization</h3>
                <div class="code-block">
public function hasPermission($userId, $userType, $resource, $action = 'read') {
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
                </div>
            </section>

            <!-- Security Features Summary -->
            <section class="security-section">
                <h2>🔒 Security Features Summary</h2>
                <div class="permission-matrix">
                    <div class="permission-card allowed">
                        <h4>Authentication</h4>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li>✓ Strong password requirements</li>
                            <li>✓ Account lockout protection</li>
                            <li>✓ Session timeout management</li>
                            <li>✓ Secure session handling</li>
                            <li>✓ Role-based user types</li>
                        </ul>
                    </div>
                    <div class="permission-card allowed">
                        <h4>Authorization</h4>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li>✓ Role-based access control</li>
                            <li>✓ Resource-level permissions</li>
                            <li>✓ Data access restrictions</li>
                            <li>✓ Action-based authorization</li>
                        </ul>
                    </div>
                    <div class="permission-card allowed">
                        <h4>Input Security</h4>
                        <ul style="text-align: left; margin-top: 10px;">
                            <li>✓ CSRF token protection</li>
                            <li>✓ XSS prevention</li>
                            <li>✓ SQL injection protection</li>
                            <li>✓ Input validation & sanitization</li>
                            <li>✓ File upload security</li>
                        </ul>
                    </div>
                </div>
            </section>
        </main>

        <footer>
            <p>&copy; 2025 Donation & Help Request System. Security Implementation Complete.</p>
            <div class="demo-buttons">
                <a href="../core/index.php" class="demo-button primary">Back to Main System</a>
            </div>
        </footer>
    </div>
</body>
</html>
