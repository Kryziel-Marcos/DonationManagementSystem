<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donation Management System</title>
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
            <h1>Donation Management System</h1>
            <p>Connecting individuals with organizations for donations and help requests</p>
            <div class="hero-features">
                <div class="feature-item">
                    <span class="feature-icon">💝</span>
                    <span>Secure Donations</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">🤝</span>
                    <span>Help Requests</span>
                </div>
                <div class="feature-item">
                    <span class="feature-icon">📸</span>
                    <span>Transparency</span>
                </div>
            </div>
        </header>

        <main class="main-content">
            <div class="user-type-selection">
                <h2>Choose Your User Type</h2>
                <p class="selection-description">Select how you'd like to participate in our donation and help request system</p>
                <div class="user-cards">
                    <div class="card user-card individual-card">
                        <div class="card-header">
                            <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">👤</div>
                            <h3 class="card-title">Individual</h3>
                            <p class="card-description">Donate money and request help from organizations. Make a difference in your community by supporting causes you care about.</p>
                        </div>
                        <div class="card-content">
                            <div class="card-features">
                                <div class="feature">✓ Make secure donations</div>
                                <div class="feature">✓ Request help when needed</div>
                                <div class="feature">✓ Track your contributions</div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="../auth/individual_register.php" class="button button-primary">Register as Individual</a>
                            <a href="../auth/individual_login.php" class="button button-secondary">Login as Individual</a>
                        </div>
                    </div>
                    <div class="card user-card organization-card">
                        <div class="card-header">
                            <div class="card-icon" style="font-size: 3rem; margin-bottom: 1rem; text-align: center;">🏢</div>
                            <h3 class="card-title">Organization</h3>
                            <p class="card-description">Receive donations and manage help requests. Build trust with donors through transparent reporting and photo documentation.</p>
                        </div>
                        <div class="card-content">
                            <div class="card-features">
                                <div class="feature">✓ Receive donations</div>
                                <div class="feature">✓ Manage help requests</div>
                                <div class="feature">✓ Upload documentary photos</div>
                            </div>
                        </div>
                        <div class="card-footer">
                            <a href="../auth/organization_register.php" class="button button-primary">Register as Organization</a>
                            <a href="../auth/organization_login.php" class="button button-secondary">Login as Organization</a>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <footer>
            <p>&copy; 2025 Donation & Help Request System. All rights reserved.</p>
            <div style="margin-top: 20px;">
                <a href="security_demo.php" class="btn btn-secondary" style="background: #28a745; color: white;">🔒 View Security Implementation</a>
            </div>
        </footer>
    </div>
</body>
</html>
