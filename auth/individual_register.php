<?php
// Include security configuration
require_once '../core/security.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Registration - Donation System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/notifications.js"></script>
    <script src="../assets/theme-toggle.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <h1>Individual Registration</h1>
            <p>Create your account to donate and request help</p>
        </header>

        <!-- Progress Indicator -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Personal Info</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Account Setup</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Contact Details</div>
                </div>
            </div>
        </div>

        <main class="form-container">
            <?php
            if (isset($_SESSION['errors'])) {
                echo '<div class="alert alert-error">';
                foreach ($_SESSION['errors'] as $error) {
                    echo '<p>' . htmlspecialchars($error) . '</p>';
                }
                echo '</div>';
                unset($_SESSION['errors']);
            }
            ?>

            <form action="process_individual_registration.php" method="POST" class="registration-form" id="registrationForm">
                <?php echo CSRFProtection::generateInputField(); ?>
                
                <!-- Step 1: Personal Information -->
                <div class="form-step active" id="step1">
                    <div class="step-header">
                        <h2>Personal Information</h2>
                        <p>Tell us about yourself</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="first_name">First Name *</label>
                        <input type="text" id="first_name" name="first_name" required>
                        <div class="field-hint">Enter your first name</div>
                    </div>

                    <div class="form-group">
                        <label for="last_name">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" required>
                        <div class="field-hint">Enter your last name</div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" onclick="goToHome()">Back to Home</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Next Step</button>
                    </div>
                </div>

                <!-- Step 2: Account Credentials -->
                <div class="form-step" id="step2">
                    <div class="step-header">
                        <h2>Account Credentials</h2>
                        <p>Create your login credentials</p>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                        <div class="field-hint">This will be your login email</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                        <div class="field-hint">Minimum 8 characters with uppercase, lowercase, number, and special character</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                        <div class="field-hint">Re-enter your password</div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Next Step</button>
                    </div>
                </div>

                <!-- Step 3: Contact Details -->
                <div class="form-step" id="step3">
                    <div class="step-header">
                        <h2>Contact Details</h2>
                        <p>Complete your profile with contact information</p>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                        <div class="field-hint">Optional - for direct contact</div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address</label>
                        <textarea id="address" name="address" rows="3"></textarea>
                        <div class="field-hint">Optional - your home address</div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" onclick="prevStep()">Previous</button>
                        <button type="submit" class="btn btn-primary">Complete Registration</button>
                    </div>
                </div>
            </form>
        </main>
    </div>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function updateProgressBar() {
            const progressSteps = document.querySelectorAll('.progress-step');
            const progressLines = document.querySelectorAll('.progress-line');
            
            progressSteps.forEach((step, index) => {
                if (index + 1 <= currentStep) {
                    step.classList.add('active');
                } else {
                    step.classList.remove('active');
                }
            });

            progressLines.forEach((line, index) => {
                if (index + 1 < currentStep) {
                    line.classList.add('completed');
                } else {
                    line.classList.remove('completed');
                }
            });
        }

        function showStep(stepNumber) {
            // Hide all steps
            document.querySelectorAll('.form-step').forEach(step => {
                step.classList.remove('active');
            });
            
            // Show current step
            document.getElementById(`step${stepNumber}`).classList.add('active');
            
            // Update progress bar
            updateProgressBar();
        }

        function nextStep() {
            if (validateCurrentStep()) {
                if (currentStep < totalSteps) {
                    currentStep++;
                    showStep(currentStep);
                }
            }
        }

        function prevStep() {
            if (currentStep > 1) {
                currentStep--;
                showStep(currentStep);
            }
        }

        function validateCurrentStep() {
            const currentStepElement = document.getElementById(`step${currentStep}`);
            const requiredFields = currentStepElement.querySelectorAll('[required]');
            
            for (let field of requiredFields) {
                if (!field.value.trim()) {
                    field.focus();
                    field.style.borderColor = 'var(--error-color)';
                    return false;
                } else {
                    field.style.borderColor = '';
                }
            }

            // Additional validation for step 2
            if (currentStep === 2) {
                const password = document.getElementById('password').value;
                const confirmPassword = document.getElementById('confirm_password').value;
                
                // Enhanced password validation
                const passwordErrors = validatePassword(password);
                if (passwordErrors.length > 0) {
                    showAutoError('Password Requirements', passwordErrors.join('<br>'), 7000);
                    document.getElementById('password').focus();
                    return false;
                }
                
                if (password !== confirmPassword) {
                    showFieldError(document.getElementById('confirm_password'), 'Passwords do not match');
                    return false;
                } else {
                    document.getElementById('confirm_password').setCustomValidity('');
                }
            }

            return true;
        }

        function validatePassword(password) {
            const errors = [];
            
            if (password.length < 8) {
                errors.push("Password must be at least 8 characters long");
            }
            
            if (!/[A-Z]/.test(password)) {
                errors.push("Password must contain at least one uppercase letter");
            }
            
            if (!/[a-z]/.test(password)) {
                errors.push("Password must contain at least one lowercase letter");
            }
            
            if (!/[0-9]/.test(password)) {
                errors.push("Password must contain at least one number");
            }
            
            if (!/[^A-Za-z0-9]/.test(password)) {
                errors.push("Password must contain at least one special character");
            }
            
            return errors;
        }

        function goToHome() {
            window.location.href = '../core/index.php';
        }

        // Password visibility toggle
        function togglePassword(fieldId) {
            const passwordField = document.getElementById(fieldId);
            const toggleButton = passwordField.nextElementSibling;
            const eyeIcon = toggleButton.querySelector('.eye-icon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                eyeIcon.textContent = '🙈';
            } else {
                passwordField.type = 'password';
                eyeIcon.textContent = '👁️';
            }
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (password !== confirmPassword) {
                this.setCustomValidity('Passwords do not match');
                showFieldError(this, 'Passwords do not match');
            } else {
                this.setCustomValidity('');
                // Clear any existing error
                const existingError = this.parentNode.querySelector('.field-error');
                if (existingError) {
                    existingError.remove();
                }
                this.style.borderColor = '';
            }
        });

        // Enhanced form submission
        document.getElementById('registrationForm').addEventListener('submit', function(e) {
            if (validateCurrentStep()) {
                showAutoInfo('Processing', 'Creating your account...', 3000);
                // Add loading state to submit button
                const submitBtn = this.querySelector('button[type="submit"]');
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
            }
        });

        // Initialize progress bar
        updateProgressBar();
    </script>
</body>
</html>
