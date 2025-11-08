<?php
require_once '../core/security.php';

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

$auth = new Authentication($pdo);
$authorization = new Authorization($pdo);

if (!$auth->isSessionValid() || !$authorization->requireAuth('individual', 'dashboard', 'read')) {
    header("Location: ../auth/individual_login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

$profile_stmt = $pdo->prepare("SELECT profile_photo FROM individuals WHERE id = ?");
$profile_stmt->execute([$user_id]);
$profile_photo = $profile_stmt->fetchColumn();

$donations_stmt = $pdo->prepare("
    SELECT d.*, o.organization_name 
    FROM donations d 
    JOIN organizations o ON d.organization_id = o.id 
    WHERE d.individual_id = ? 
    ORDER BY d.donation_date DESC
");
$donations_stmt->execute([$user_id]);
$donations = $donations_stmt->fetchAll(PDO::FETCH_ASSOC);

$requests_stmt = $pdo->prepare("
    SELECT hr.*, o.organization_name 
    FROM help_requests hr 
    LEFT JOIN organizations o ON hr.organization_id = o.id 
    WHERE hr.individual_id = ? 
    ORDER BY hr.request_date DESC
");
$requests_stmt->execute([$user_id]);
$help_requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

$organizations_stmt = $pdo->prepare("SELECT id, organization_name FROM organizations ORDER BY organization_name");
$organizations_stmt->execute();
$organizations = $organizations_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Individual Dashboard - Donation System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
    <script src="../assets/notifications.js"></script>
    <script src="../assets/emailjs-integration.js"></script>
    <script src="../assets/theme-toggle.js"></script>
    <script src="../assets/confirmation-dialog.js"></script>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <div class="profile-section">
                    <div class="profile-photo-container">
                        <?php if ($profile_photo && file_exists($profile_photo)): ?>
                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Profile Photo" class="profile-photo">
                        <?php else: ?>
                            <div class="profile-photo-placeholder">
                                <span class="profile-icon">👤</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="header-text">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Individual Dashboard</p>
                    </div>
                </div>
            <div class="user-info">
                <span class="badge badge-default">Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                <a href="#" class="button button-secondary" onclick="event.preventDefault(); showLogoutConfirmation(function() { window.location.href = '../core/logout.php'; });">Logout</a>
                </div>
            </div>
        </header>

        <main class="dashboard-content">
            <?php
            $donationNotification = null;
            if (isset($_SESSION['donation_notification'])) {
                $donationNotification = $_SESSION['donation_notification'];
                unset($_SESSION['donation_notification']);
            }
            
            if (isset($_SESSION['success_message'])) {
                $successMessage = addslashes($_SESSION['success_message']);
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        if (window.notifications) {
                            window.notifications.showAlert("success", "Success", "' . $successMessage . '");
                        }
                    });
                </script>';
                unset($_SESSION['success_message']);
            }
            
            if ($donationNotification) {
                $donationDataJson = json_encode($donationNotification);
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        setTimeout(function() {
                            if (window.sendDonationEmail && typeof window.sendDonationEmail === "function") {
                                const donationData = ' . $donationDataJson . ';
                                window.sendDonationEmail(donationData).then(function() {
                                    console.log("Donation confirmation email sent successfully");
                                }).catch(function(error) {
                                    console.error("Failed to send donation email:", error);
                                });
                            } else {
                                console.warn("EmailJS integration not available");
                            }
                        }, 1000);
                    });
                </script>';
            }
            if (isset($_SESSION['errors'])) {
                $errorMessages = implode(' ', $_SESSION['errors']);
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        if (window.notifications) {
                            window.notifications.showAlert("error", "Error", "' . addslashes($errorMessages) . '");
                        }
                    });
                </script>';
                unset($_SESSION['errors']);
            }
            ?>

            <section class="card dashboard-section accordion" id="profile-section">
                <div class="accordion-item">
                    <button class="accordion-trigger" type="button" onclick="toggleSection('profile-section')" aria-expanded="false">
                        <span>📷 Profile Photo</span>
                        <span class="accordion-icon">▼</span>
                    </button>
                    <div class="accordion-content collapsed" id="profile-section-content" data-state="closed">
                        <div class="accordion-content-inner">
                            <p class="card-description" style="margin-bottom: 1.5rem;">Upload or update your profile photo.</p>
                            <div class="profile-upload-section">
                                <div class="current-profile-photo" style="margin-bottom: 1.5rem;">
                                    <h3 class="card-title" style="font-size: 1.25rem; margin-bottom: 1rem;">Current Profile Photo</h3>
                                    <div class="profile-photo-preview">
                                        <?php if ($profile_photo && file_exists($profile_photo)): ?>
                                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Current Profile Photo" class="profile-photo-large">
                                        <?php else: ?>
                                            <div class="profile-photo-placeholder-large">
                                                <span class="profile-icon-large">👤</span>
                                                <p>No profile photo uploaded</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <form action="../processing/process_profile_photo_upload.php" method="POST" enctype="multipart/form-data" class="profile-upload-form">
                                    <?php echo CSRFProtection::generateInputField(); ?>
                                    <div class="form-group">
                                        <label for="profile_photo" class="label label-required">Select Profile Photo</label>
                                        <div class="file-upload-wrapper">
                                            <input type="file" id="profile_photo" name="profile_photo" class="input" accept="image/*" required>
                                            <div class="file-upload-info">
                                                <span class="file-icon">📷</span>
                                                <span class="file-text">Choose photo (JPEG, PNG, GIF - Max 5MB)</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="button button-primary">Upload Profile Photo</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card dashboard-section quick-actions">
                <div class="card-header">
                    <h2 class="card-title">Quick Actions</h2>
                </div>
                <div class="card-content">
                    <div class="action-cards">
                        <div class="card action-card">
                            <div class="card-header">
                                <div class="action-icon">💝</div>
                                <h3 class="card-title">Make Donation</h3>
                                <p class="card-description">Support organizations with your contributions</p>
                            </div>
                            <div class="card-footer">
                                <button class="button button-primary" onclick="scrollToSection('donation-section')">Donate Now</button>
                            </div>
                        </div>
                        <div class="card action-card">
                            <div class="card-header">
                                <div class="action-icon">🤝</div>
                                <h3 class="card-title">Request Help</h3>
                                <p class="card-description">Get assistance when you need it most</p>
                            </div>
                            <div class="card-footer">
                                <button class="button button-primary" onclick="scrollToSection('help-section')">Request Help</button>
                            </div>
                        </div>
                        <div class="card action-card">
                            <div class="card-header">
                                <div class="action-icon">📊</div>
                                <h3 class="card-title">View History</h3>
                                <p class="card-description">Track your donations and requests</p>
                            </div>
                            <div class="card-footer">
                                <button class="button button-secondary" onclick="scrollToSection('history-section')">View History</button>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card dashboard-section accordion" id="donation-section">
                <div class="accordion-item">
                    <button class="accordion-trigger" type="button" onclick="toggleSection('donation-section')" aria-expanded="false">
                        <span>💝 Make a Donation</span>
                        <span class="accordion-icon">▼</span>
                    </button>
                    <div class="accordion-content collapsed" id="donation-section-content" data-state="closed">
                        <div class="accordion-content-inner">
                            <p class="card-description" style="margin-bottom: 1.5rem;">Choose an organization and make a donation. You can donate money, clothes, food, blood, or other items.</p>
                            <form action="../processing/process_donation.php" method="POST" class="donation-form" id="donationForm">
                                <?php echo CSRFProtection::generateInputField(); ?>
                                <div class="form-group">
                                    <label for="organization_id" class="label">Select Organization</label>
                                    <select id="organization_id" name="organization_id" class="select" required>
                            <option value="">Choose an organization</option>
                            <?php foreach ($organizations as $org): ?>
                                <option value="<?php echo $org['id']; ?>" data-name="<?php echo htmlspecialchars($org['organization_name']); ?>"><?php echo htmlspecialchars($org['organization_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="hidden" id="organization_name" name="organization_name" value="">
                    </div>
                                <div class="form-group">
                                    <label for="donation_type" class="label">Donation Type</label>
                                    <select id="donation_type" name="donation_type" class="select" required onchange="toggleDonationFields()">
                                        <option value="money">💰 Money</option>
                                        <option value="clothes">👕 Clothes</option>
                                        <option value="food">🍔 Food</option>
                                        <option value="blood">🩸 Blood</option>
                                        <option value="other">📦 Other</option>
                                    </select>
                                </div>
                                <div class="form-group" id="amount-group">
                                    <label for="amount" class="label">Donation Amount (₱)</label>
                                    <input type="number" id="amount" name="amount" class="input" step="0.01" min="1" placeholder="Enter amount">
                                    <small class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem; display: block;">Required for monetary donations</small>
                                </div>
                                <div class="form-group" id="description-group" style="display: none;">
                                    <label for="description" class="label">Donation Description</label>
                                    <textarea id="description" name="description" class="textarea" rows="4" placeholder="Describe your donation (e.g., '5 bags of rice', '10 winter coats', 'Type O+ blood', etc.)"></textarea>
                                    <small class="text-muted" style="font-size: 0.75rem; margin-top: 0.25rem; display: block;">Required for non-monetary donations</small>
                                </div>
                                <button type="submit" class="button button-primary">Make Donation</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card dashboard-section accordion" id="help-section">
                <div class="accordion-item">
                    <button class="accordion-trigger" type="button" onclick="toggleSection('help-section')" aria-expanded="false">
                        <span>🤝 Request Help</span>
                        <span class="accordion-icon">▼</span>
                    </button>
                    <div class="accordion-content collapsed" id="help-section-content" data-state="closed">
                        <div class="accordion-content-inner">
                            <p class="card-description" style="margin-bottom: 1.5rem;">Submit a help request to a specific organization for assistance.</p>
                            <form action="../processing/process_help_request.php" method="POST" class="help-request-form">
                                <?php echo CSRFProtection::generateInputField(); ?>
                                <div class="form-group">
                                    <label for="help_organization_id" class="label">Select Organization</label>
                                    <select id="help_organization_id" name="organization_id" class="select" required>
                                        <option value="">Choose an organization</option>
                                        <?php foreach ($organizations as $org): ?>
                                            <option value="<?php echo $org['id']; ?>"><?php echo htmlspecialchars($org['organization_name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="title" class="label label-required">Request Title</label>
                                    <input type="text" id="title" name="title" class="input" required placeholder="Brief description of your request">
                                </div>
                                <div class="form-group">
                                    <label for="description" class="label label-required">Description</label>
                                    <textarea id="description" name="description" class="textarea" rows="4" required placeholder="Provide detailed information about your request"></textarea>
                                </div>
                                <div class="form-group">
                                    <label for="amount_requested" class="label">Amount Requested (₱) <span class="optional">(Optional)</span></label>
                                    <input type="number" id="amount_requested" name="amount_requested" class="input" step="0.01" min="0" placeholder="Enter amount if requesting financial help">
                                </div>
                                <button type="submit" class="button button-primary">Submit Help Request</button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card dashboard-section" id="history-section">
                <div class="card-header">
                    <h2 class="card-title">📊 Your Donations</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($donations)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">💝</div>
                            <h3>No donations made yet</h3>
                            <p>Start making a difference by donating to organizations you care about.</p>
                            <button class="button button-primary" onclick="scrollToSection('donation-section')">Make Your First Donation</button>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table">
                            <thead>
                                <tr>
                                    <th>Organization</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donation['organization_name']); ?></td>
                                        <td>
                                            <?php
                                            $type_icons = [
                                                'money' => '💰',
                                                'clothes' => '👕',
                                                'food' => '🍔',
                                                'blood' => '🩸',
                                                'other' => '📦'
                                            ];
                                            $icon = $type_icons[$donation['donation_type'] ?? 'money'] ?? '📦';
                                            echo $icon . ' ' . ucfirst($donation['donation_type'] ?? 'money');
                                            ?>
                                        </td>
                                        <td>
                                            <?php if ($donation['donation_type'] === 'money'): ?>
                                                ₱<?php echo number_format($donation['amount'] ?? 0, 2); ?>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($donation['description'] ?? 'N/A'); ?>
                                                <?php if (!empty($donation['amount'])): ?>
                                                    <br><small>(Value: ₱<?php echo number_format($donation['amount'], 2); ?>)</small>
                                                <?php endif; ?>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($donation['donation_date'])); ?></td>
                                        <td><span class="badge status status-<?php echo $donation['status']; ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>

            <section class="card dashboard-section">
                <div class="card-header">
                    <h2 class="card-title">📋 Your Help Requests</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($help_requests)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">🤝</div>
                            <h3>No help requests submitted yet</h3>
                            <p>Submit a help request when you need assistance from organizations.</p>
                            <button class="button button-primary" onclick="scrollToSection('help-section')">Submit Help Request</button>
                        </div>
                    <?php else: ?>
                        <div class="table-wrapper">
                            <table class="table">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Amount Requested</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($help_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['title']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($request['description'], 0, 50)) . (strlen($request['description']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo $request['amount_requested'] ? '₱' . number_format($request['amount_requested'], 2) : 'N/A'; ?></td>
                                        <td><span class="badge status status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                        <td><?php echo date('M j, Y', strtotime($request['request_date'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </main>
    </div>

    <script>
        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            const content = document.getElementById(sectionId + '-content');
            if (content && content.classList.contains('collapsed')) {
                toggleSection(sectionId);
                setTimeout(() => {
                    section.scrollIntoView({ 
                        behavior: 'smooth',
                        block: 'start'
                    });
                }, 300);
            } else {
                section.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function toggleSection(sectionId) {
            const content = document.getElementById(sectionId + '-content');
            const trigger = document.querySelector(`#${sectionId} .accordion-trigger`);
            const icon = document.querySelector(`#${sectionId} .accordion-icon`);
            if (content && trigger) {
                const isExpanded = trigger.getAttribute('aria-expanded') === 'true';
                trigger.setAttribute('aria-expanded', !isExpanded);
                content.setAttribute('data-state', isExpanded ? 'closed' : 'open');
                content.classList.add('animating');
                content.classList.toggle('collapsed');
                if (icon) {
                    icon.textContent = isExpanded ? '▼' : '▲';
                }
                setTimeout(() => {
                    content.classList.remove('animating');
                }, 300);
            }
        }

        function toggleDonationFields() {
            const donationType = document.getElementById('donation_type').value;
            const amountGroup = document.getElementById('amount-group');
            const descriptionGroup = document.getElementById('description-group');
            const amountInput = document.getElementById('amount');
            const descriptionInput = document.getElementById('description');

            if (donationType === 'money') {
                amountGroup.style.display = 'block';
                descriptionGroup.style.display = 'none';
                amountInput.setAttribute('required', 'required');
                descriptionInput.removeAttribute('required');
                descriptionInput.value = '';
            } else {
                amountGroup.style.display = 'block';
                descriptionGroup.style.display = 'block';
                amountInput.removeAttribute('required');
                descriptionInput.setAttribute('required', 'required');
            }
        }

        (function() {
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initAccordions);
            } else {
                initAccordions();
            }
            
            function initAccordions() {
                const allAccordions = document.querySelectorAll('.accordion-content');
                allAccordions.forEach(function(accordion) {
                    if (accordion.classList.contains('collapsed')) {
                        accordion.classList.add('no-animate');
                        accordion.setAttribute('data-state', 'closed');
                        const trigger = accordion.closest('.accordion-item')?.querySelector('.accordion-trigger');
                        if (trigger) {
                            trigger.setAttribute('aria-expanded', 'false');
                        }
                        const icon = accordion.closest('.accordion-item')?.querySelector('.accordion-icon');
                        if (icon) {
                            icon.textContent = '▼';
                        }
                        setTimeout(() => {
                            accordion.classList.remove('no-animate');
                        }, 100);
                    }
                });
            }
        })();

        document.addEventListener('DOMContentLoaded', function() {
            const orgSelect = document.getElementById('organization_id');
            const orgNameInput = document.getElementById('organization_name');
            
            if (orgSelect && orgNameInput) {
                orgSelect.addEventListener('change', function() {
                    const selectedOption = this.options[this.selectedIndex];
                    if (selectedOption.value) {
                        orgNameInput.value = selectedOption.getAttribute('data-name') || selectedOption.text;
                    }
                });
            }

            toggleDonationFields();
        });
    </script>
