<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Registration - Donation System</title>
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
            <h1>Organization Registration</h1>
            <p>Create your organization account to receive donations and manage help requests</p>
        </header>

        <!-- Progress Indicator -->
        <div class="progress-container">
            <div class="progress-bar">
                <div class="progress-step active" data-step="1">
                    <div class="step-number">1</div>
                    <div class="step-label">Basic Info</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="2">
                    <div class="step-number">2</div>
                    <div class="step-label">Contact & Auth</div>
                </div>
                <div class="progress-line"></div>
                <div class="progress-step" data-step="3">
                    <div class="step-number">3</div>
                    <div class="step-label">Additional Info</div>
                </div>
            </div>
        </div>

        <main class="form-container">
            <?php
            session_start();
            if (isset($_SESSION['errors'])) {
                echo '<div class="alert alert-error">';
                foreach ($_SESSION['errors'] as $error) {
                    echo '<p>' . htmlspecialchars($error) . '</p>';
                }
                echo '</div>';
                unset($_SESSION['errors']);
            }
            ?>

            <form action="process_organization_registration.php" method="POST" class="registration-form" id="registrationForm">
                
                <!-- Step 1: Basic Information -->
                <div class="form-step active" id="step1">
                    <div class="step-header">
                        <h2>Basic Organization Information</h2>
                        <p>Tell us about your organization's basic details</p>
                    </div>
                    
                    <div class="form-group">
                        <label for="organization_name">Organization Name *</label>
                        <input type="text" id="organization_name" name="organization_name" required>
                        <div class="field-hint">Enter your organization's official name</div>
                    </div>

                    <div class="form-group">
                        <label for="organization_type">Organization Type *</label>
                        <select id="organization_type" name="organization_type" required>
                            <option value="">Select Organization Type</option>
                            <option value="nonprofit">Non-Profit Organization</option>
                            <option value="charity">Charity Foundation</option>
                            <option value="ngo">NGO (Non-Governmental Organization)</option>
                            <option value="religious">Religious Organization</option>
                            <option value="educational">Educational Institution</option>
                            <option value="healthcare">Healthcare Organization</option>
                            <option value="environmental">Environmental Organization</option>
                            <option value="community">Community Organization</option>
                            <option value="other">Other</option>
                        </select>
                        <div class="field-hint">Choose the category that best describes your organization</div>
                    </div>

                    <div class="form-group">
                        <label for="contact_person">Contact Person *</label>
                        <input type="text" id="contact_person" name="contact_person" required>
                        <div class="field-hint">Full name of the primary contact person</div>
                    </div>

                    <div class="step-actions">
                        <button type="button" class="btn btn-secondary" onclick="goToHome()">Back to Home</button>
                        <button type="button" class="btn btn-primary" onclick="nextStep()">Next Step</button>
                    </div>
                </div>

                <!-- Step 2: Contact & Authentication -->
                <div class="form-step" id="step2">
                    <div class="step-header">
                        <h2>Contact & Authentication</h2>
                        <p>Provide contact details and create your account credentials</p>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required>
                        <div class="field-hint">This will be your login email</div>
                    </div>

                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="tel" id="phone" name="phone">
                        <div class="field-hint">Optional - for direct contact</div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="password" name="password" required minlength="6">
                            <button type="button" class="password-toggle" onclick="togglePassword('password')">
                                <span class="eye-icon">👁️</span>
                            </button>
                        </div>
                        <div class="field-hint">Minimum 6 characters</div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password *</label>
                        <div class="password-input-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
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

                <!-- Step 3: Additional Information -->
                <div class="form-step" id="step3">
                    <div class="step-header">
                        <h2>Additional Information</h2>
                        <p>Complete your organization profile with additional details</p>
                    </div>

                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" placeholder="https://example.com">
                        <div class="field-hint">Optional - your organization's website</div>
                    </div>

                    <div class="form-group">
                        <label for="address">Address *</label>
                        <textarea id="address" name="address" rows="3" required></textarea>
                        <div class="field-hint">Your organization's physical address</div>
                    </div>

                    <div class="form-group">
                        <label for="description">Organization Description *</label>
                        <textarea id="description" name="description" rows="4" placeholder="Describe your organization's mission and activities" required></textarea>
                        <div class="field-hint">Help donors understand your organization's purpose</div>
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
                
                if (password !== confirmPassword) {
                    document.getElementById('confirm_password').setCustomValidity('Passwords do not match');
                    document.getElementById('confirm_password').focus();
                    return false;
                } else {
                    document.getElementById('confirm_password').setCustomValidity('');
                }
            }

            return true;
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
            } else {
                this.setCustomValidity('');
            }
        });

        // Initialize progress bar
        updateProgressBar();
    </script>
</body>
</html>
