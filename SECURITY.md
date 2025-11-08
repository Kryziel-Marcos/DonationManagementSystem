# Security Implementation Documentation

## Overview
This document outlines the comprehensive security measures implemented in the Donation & Help Request System, addressing the requirements specified in the security requirements image.

## 🔐 Authentication (Login & User Roles)

### Enhanced Password Security
- **Minimum Requirements**: 8 characters minimum
- **Complexity Requirements**: 
  - At least one uppercase letter
  - At least one lowercase letter  
  - At least one number
  - At least one special character
- **Implementation**: `Authentication::validatePassword()` method

### Account Security
- **Login Attempt Limiting**: Maximum 5 failed attempts
- **Account Lockout**: 15-minute lockout after failed attempts
- **Session Management**: 1-hour automatic timeout
- **Secure Sessions**: Session ID regeneration on login

### User Roles
- **Individual Users**: Can donate and request help
- **Organization Users**: Can receive donations and manage requests
- **Role-Based Access**: Distinct permissions for each user type

## 🔑 Authorization (Access Control)

### Permission Matrix

#### Individual Permissions
- ✅ Create donations
- ✅ View own donations
- ✅ Create help requests
- ✅ View own help requests
- ✅ Update personal profile
- ❌ Access organization data
- ❌ Manage other users' requests

#### Organization Permissions
- ✅ View donations made to them
- ✅ Update donation status
- ✅ View help requests
- ✅ Update help request status
- ✅ Upload documentary photos
- ✅ Delete photos
- ✅ Update organization profile
- ❌ Access other organizations' data
- ❌ View other users' personal information

### Implementation
- **Resource-Level Authorization**: `Authorization::hasPermission()`
- **Data Access Control**: `Authorization::canAccessData()`
- **Automatic Redirects**: Unauthorized access redirects to appropriate login

## 🛡️ Input Validation & Sanitization

### Input Types Handled
1. **Email Validation**
   - Server-side format validation
   - Sanitization using PHP filters
   - Duplicate email prevention

2. **Numeric Input**
   - Amount validation (min: 1, max: 999,999.99)
   - Organization ID validation
   - Range checking for all numeric fields

3. **Text Input**
   - HTML entity encoding (XSS prevention)
   - Length limits (strings: 255 chars, text: 1000 chars)
   - Trim whitespace

4. **Phone Numbers**
   - Format validation
   - Length limits (10-20 characters)
   - Special character filtering

5. **File Uploads**
   - MIME type validation
   - File size limits (5MB max)
   - Allowed extensions (jpg, jpeg, png, gif)
   - Secure filename generation

### CSRF Protection
- **Token Generation**: Random 64-character tokens
- **Token Validation**: `hash_equals()` for timing attack prevention
- **Form Integration**: Automatic token injection in all forms
- **Request Validation**: All POST requests validated

## 🔒 Security Code Snippets

### Password Validation
```php
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
```

### CSRF Protection
```php
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
```

### Input Sanitization
```php
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
```

### Role-Based Authorization
```php
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
```

## 🚀 Security Features Implementation

### Database Security
- **Prepared Statements**: All database queries use prepared statements
- **SQL Injection Prevention**: Parameterized queries throughout
- **Password Hashing**: `password_hash()` with `PASSWORD_DEFAULT`
- **Secure Storage**: Login attempt tracking and account lockout

### Session Security
- **Secure Cookies**: HTTPOnly, Secure, SameSite flags
- **Session Regeneration**: New session ID on login
- **Timeout Management**: Automatic session expiration
- **Activity Tracking**: Last activity monitoring

### File Security
- **Upload Validation**: MIME type and extension checking
- **Secure Filenames**: Unique, unpredictable filenames
- **Size Limits**: Maximum file size enforcement
- **Path Security**: Upload directory isolation

## 🔍 Security Testing

### Authentication Testing
1. **Login Attempts**: Test account lockout after 5 failed attempts
2. **Password Requirements**: Verify strong password enforcement
3. **Session Timeout**: Test automatic logout after inactivity
4. **Role Separation**: Verify users can only access their role's features

### Authorization Testing
1. **Access Control**: Test unauthorized access prevention
2. **Data Isolation**: Verify users can only see their own data
3. **Permission Matrix**: Test all permission combinations
4. **Redirect Behavior**: Verify proper redirects for unauthorized access

### Input Validation Testing
1. **XSS Prevention**: Test HTML/JavaScript injection attempts
2. **SQL Injection**: Test malicious SQL input
3. **CSRF Protection**: Test cross-site request forgery attempts
4. **File Upload**: Test malicious file upload attempts

## 📋 Security Checklist

### ✅ Implemented Features
- [x] Strong password requirements
- [x] Account lockout protection
- [x] Session timeout management
- [x] Role-based access control
- [x] CSRF token protection
- [x] Input validation and sanitization
- [x] XSS prevention
- [x] SQL injection protection
- [x] File upload security
- [x] Secure session handling
- [x] Data access restrictions
- [x] Error handling and logging

### 🔧 Configuration Files
- `core/security.php` - Main security configuration and classes
- `database/database_setup.sql` - Enhanced database schema with security fields
- `core/security_demo.php` - Security demonstration page

### 🎯 Security Demonstration
Access the security demonstration at: `core/security_demo.php`

This page showcases:
- Authentication features
- Authorization matrix
- Input validation examples
- Code snippets
- Security feature summary

## 🚨 Security Best Practices

1. **Regular Updates**: Keep PHP and dependencies updated
2. **HTTPS**: Use HTTPS in production environments
3. **Error Logging**: Monitor security-related errors
4. **Backup Security**: Secure database backups
5. **Access Monitoring**: Log and monitor access attempts
6. **Code Review**: Regular security code reviews
7. **Penetration Testing**: Periodic security testing

## 📞 Security Contact

For security-related issues or questions, please refer to the system administrator or development team.

---

**Note**: This security implementation follows industry best practices and provides comprehensive protection against common web application vulnerabilities. Regular security audits and updates are recommended for production environments.
