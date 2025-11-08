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

if (!$auth->isSessionValid() || !$authorization->requireAuth('organization', 'dashboard', 'read')) {
    header("Location: ../auth/organization_login.php");
    exit();
}

$organization_id = $_SESSION['user_id'];

$org_stmt = $pdo->prepare("SELECT organization_name, organization_type, description, profile_photo FROM organizations WHERE id = ?");
$org_stmt->execute([$organization_id]);
$org_details = $org_stmt->fetch(PDO::FETCH_ASSOC);
$profile_photo = $org_details['profile_photo'] ?? null;

if (!$org_details) {
    $_SESSION['errors'] = ['Organization not found. Please contact support.'];
    header("Location: ../auth/organization_login.php");
    exit();
}

$donations_stmt = $pdo->prepare("
    SELECT d.*, i.first_name, i.last_name 
    FROM donations d 
    JOIN individuals i ON d.individual_id = i.id 
    WHERE d.organization_id = ? 
    ORDER BY d.donation_date DESC
");
$donations_stmt->execute([$organization_id]);
$donations = $donations_stmt->fetchAll(PDO::FETCH_ASSOC);

$requests_stmt = $pdo->prepare("
    SELECT hr.*, i.first_name, i.last_name 
    FROM help_requests hr 
    JOIN individuals i ON hr.individual_id = i.id 
    WHERE hr.organization_id = ?
    ORDER BY hr.request_date DESC
");
$requests_stmt->execute([$organization_id]);
$help_requests = $requests_stmt->fetchAll(PDO::FETCH_ASSOC);

$photos_stmt = $pdo->prepare("
    SELECT * FROM documentary_photos 
    WHERE organization_id = ? 
    ORDER BY upload_date DESC
");
$photos_stmt->execute([$organization_id]);
$documentary_photos = $photos_stmt->fetchAll(PDO::FETCH_ASSOC);

$total_stmt = $pdo->prepare("SELECT SUM(amount) as total FROM donations WHERE organization_id = ? AND status = 'completed' AND amount IS NOT NULL");
$total_stmt->execute([$organization_id]);
$total_result = $total_stmt->fetch(PDO::FETCH_ASSOC);
$total_donations = $total_result ? ($total_result['total'] ?? 0) : 0;

$type_counts = [
    'money' => 0,
    'clothes' => 0,
    'food' => 0,
    'blood' => 0,
    'other' => 0
];
foreach ($donations as $donation) {
    $type = $donation['donation_type'] ?? 'money';
    if (isset($type_counts[$type])) {
        $type_counts[$type]++;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organization Dashboard - Donation System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/styles.css">
    <script src="../assets/notifications.js"></script>
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
                                <span class="profile-icon">🏢</span>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div class="header-text">
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['user_name']); ?>!</h1>
            <p>Organization Dashboard - <?php echo ucfirst(str_replace('_', ' ', $org_details['organization_type'])); ?></p>
                    </div>
                </div>
            <div class="user-info">
                <span>Email: <?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                <a href="#" class="btn btn-secondary" onclick="event.preventDefault(); showLogoutConfirmation(function() { window.location.href = '../core/logout.php'; });">Logout</a>
                </div>
            </div>
        </header>

        <section class="dashboard-section">
            <h2>Organization Information</h2>
            <div class="org-info">
                <div class="org-details">
                    <h3><?php echo htmlspecialchars($org_details['organization_name'] ?? 'Unknown Organization'); ?></h3>
                    <p class="org-type"><?php echo ucfirst(str_replace('_', ' ', $org_details['organization_type'] ?? 'organization')); ?></p>
                    <?php if (!empty($org_details['description'])): ?>
                        <div class="org-description">
                            <h4>About Us</h4>
                            <p><?php echo nl2br(htmlspecialchars($org_details['description'])); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <main class="dashboard-content">
            <?php
            if (isset($_SESSION['success_message'])) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        if (window.notifications) {
                            window.notifications.showAlert("success", "Success", "' . addslashes($_SESSION['success_message']) . '");
                        }
                    });
                </script>';
                unset($_SESSION['success_message']);
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
                            <p class="card-description" style="margin-bottom: 1.5rem;">Upload or update your organization's profile photo.</p>
                            <div class="profile-upload-section">
                                <div class="current-profile-photo" style="margin-bottom: 1.5rem;">
                                    <h3 class="card-title" style="font-size: 1.25rem; margin-bottom: 1rem;">Current Profile Photo</h3>
                                    <div class="profile-photo-preview">
                                        <?php if ($profile_photo && file_exists($profile_photo)): ?>
                                            <img src="<?php echo htmlspecialchars($profile_photo); ?>" alt="Current Profile Photo" class="profile-photo-large">
                                        <?php else: ?>
                                            <div class="profile-photo-placeholder-large">
                                                <span class="profile-icon-large">🏢</span>
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

            <section class="dashboard-section quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-cards">
                    <div class="action-card">
                        <div class="action-icon">📸</div>
                        <h3>Upload Photos</h3>
                        <p>Share documentary photos to build trust with donors</p>
                        <button class="btn btn-primary" onclick="scrollToSection('photo-section')">Upload Photos</button>
                    </div>
                    <div class="action-card">
                        <div class="action-icon">📋</div>
                        <h3>Manage Requests</h3>
                        <p>Review and respond to help requests</p>
                        <button class="btn btn-primary" onclick="scrollToSection('requests-section')">Manage Requests</button>
                    </div>
                </div>
            </section>

            <section class="dashboard-section stats-section">
                <h2>Statistics</h2>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Total Monetary Donations</h3>
                        <p class="stat-value">₱<?php echo number_format($total_donations, 2); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Total Donations</h3>
                        <p class="stat-value"><?php echo count($donations); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Help Requests</h3>
                        <p class="stat-value"><?php echo count($help_requests); ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>Documentary Photos</h3>
                        <p class="stat-value"><?php echo count($documentary_photos); ?></p>
                    </div>
                </div>
                <div class="stats-grid" style="margin-top: 20px;">
                    <div class="stat-card">
                        <h3>💰 Money Donations</h3>
                        <p class="stat-value"><?php echo $type_counts['money']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>👕 Clothes Donations</h3>
                        <p class="stat-value"><?php echo $type_counts['clothes']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>🍔 Food Donations</h3>
                        <p class="stat-value"><?php echo $type_counts['food']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>🩸 Blood Donations</h3>
                        <p class="stat-value"><?php echo $type_counts['blood']; ?></p>
                    </div>
                    <div class="stat-card">
                        <h3>📦 Other Donations</h3>
                        <p class="stat-value"><?php echo $type_counts['other']; ?></p>
                    </div>
                </div>
            </section>

            <section class="card dashboard-section accordion" id="photo-section">
                <div class="accordion-item">
                    <button class="accordion-trigger" type="button" onclick="toggleSection('photo-section')" aria-expanded="false">
                        <span>📸 Documentary Photos</span>
                        <span class="accordion-icon">▼</span>
                    </button>
                    <div class="accordion-content collapsed" id="photo-section-content" data-state="closed">
                        <div class="accordion-content-inner">
                            <p class="card-description" style="margin-bottom: 1.5rem;">Share photos showing where donations go to build trust with donors and demonstrate transparency.</p>
                            
                            <div class="photo-upload-section">
                                <h3 class="card-title" style="font-size: 1.25rem; margin-bottom: 1rem;">Upload New Photo</h3>
                                <form action="../processing/process_photo_upload.php" method="POST" enctype="multipart/form-data" class="photo-upload-form">
                                    <div class="form-group">
                                        <label for="title" class="label label-required">Photo Title</label>
                                        <input type="text" id="title" name="title" class="input" required placeholder="e.g., Food Distribution at Community Center">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="description" class="label label-required">Description</label>
                                        <textarea id="description" name="description" class="textarea" rows="3" required placeholder="Describe what the photo shows and how it relates to donations received"></textarea>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="photo" class="label label-required">Select Photo</label>
                                        <div class="file-upload-wrapper">
                                            <input type="file" id="photo" name="photo" class="input" accept="image/*" required>
                                            <div class="file-upload-info">
                                                <span class="file-icon">📷</span>
                                                <span class="file-text">Choose photo (JPEG, PNG, GIF - Max 5MB)</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="form-actions">
                                        <button type="submit" class="button button-primary">Upload Photo</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <section class="card dashboard-section">
                <div class="card-header">
                    <h2 class="card-title">Photo Gallery</h2>
                </div>
                <div class="card-content">
                    <?php if (empty($documentary_photos)): ?>
                        <div class="empty-state">
                            <div class="empty-icon">📷</div>
                            <h3>No photos uploaded yet</h3>
                            <p>Upload your first documentary photo to show donors where their contributions go.</p>
                        </div>
                <?php else: ?>
                    <div class="photo-gallery">
                        <?php foreach ($documentary_photos as $photo): ?>
                            <div class="photo-card">
                                <div class="photo-container">
                                    <img src="<?php echo htmlspecialchars($photo['photo_path']); ?>" alt="<?php echo htmlspecialchars($photo['title']); ?>" loading="lazy">
                                    <div class="photo-overlay">
                                        <form action="../processing/process_photo_delete.php" method="POST" class="delete-form" data-photo-id="<?php echo $photo['id']; ?>" data-photo-title="<?php echo htmlspecialchars($photo['title'], ENT_QUOTES); ?>">
                                            <input type="hidden" name="photo_id" value="<?php echo $photo['id']; ?>">
                                            <button type="button" class="delete-btn" title="Delete Photo" onclick="showDeleteModal(this.closest('form'))">
                                                <span class="delete-icon">🗑️</span>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <div class="photo-info">
                                    <h4><?php echo htmlspecialchars($photo['title']); ?></h4>
                                    <p class="photo-description"><?php echo nl2br(htmlspecialchars($photo['description'])); ?></p>
                                    <p class="photo-date">Uploaded: <?php echo date('M j, Y g:i A', strtotime($photo['upload_date'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-section" id="donations-section">
                <h2>💰 Donations Received</h2>
                <p class="section-description">Review and manage donations received from individuals.</p>
                <?php if (empty($donations)): ?>
                    <p>No donations received yet.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Donor</th>
                                    <th>Type</th>
                                    <th>Details</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($donations as $donation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($donation['first_name'] . ' ' . $donation['last_name']); ?></td>
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
                                        <td><span class="status status-<?php echo $donation['status']; ?>"><?php echo ucfirst($donation['status']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>

            <section class="dashboard-section" id="requests-section">
                <h2>📋 Help Requests</h2>
                <p class="section-description">Review and respond to help requests from individuals.</p>
                <?php if (empty($help_requests)): ?>
                    <p>No help requests to manage.</p>
                <?php else: ?>
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Requester</th>
                                    <th>Title</th>
                                    <th>Description</th>
                                    <th>Amount Requested</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($help_requests as $request): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($request['first_name'] . ' ' . $request['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($request['title']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($request['description'], 0, 50)) . (strlen($request['description']) > 50 ? '...' : ''); ?></td>
                                        <td><?php echo $request['amount_requested'] ? '₱' . number_format($request['amount_requested'], 2) : 'N/A'; ?></td>
                                        <td><?php echo date('M j, Y', strtotime($request['request_date'])); ?></td>
                                        <td><span class="status status-<?php echo $request['status']; ?>"><?php echo ucfirst($request['status']); ?></span></td>
                                        <td>
                                            <?php if ($request['status'] == 'pending'): ?>
                                                <form action="../processing/process_request_status.php" method="POST" style="display: inline;">
                                                    <?php echo CSRFProtection::generateInputField(); ?>
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="approved">
                                                    <button type="submit" class="btn btn-success btn-sm">Approve</button>
                                                </form>
                                                <form action="../processing/process_request_status.php" method="POST" style="display: inline;">
                                                    <?php echo CSRFProtection::generateInputField(); ?>
                                                    <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                                                    <input type="hidden" name="status" value="rejected">
                                                    <button type="submit" class="btn btn-danger btn-sm">Reject</button>
                                                </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <div id="deleteModal" class="modal-overlay" style="display: none;">
        <div class="modal-dialog">
            <div class="modal-header">
                <div class="modal-icon-wrapper">
                    <svg class="modal-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 9V11M12 15H12.01M5.07183 19H18.9282C20.4678 19 21.4301 17.3333 20.6603 16L13.7321 4C12.9623 2.66667 11.0377 2.66667 10.2679 4L3.33975 16C2.56995 17.3333 3.53223 19 5.07183 19Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </div>
                <h2 class="modal-title">Delete Photo</h2>
                <button class="modal-close" onclick="closeDeleteModal()" aria-label="Close modal">
                    <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="modal-content">
                <p class="modal-message">Are you sure you want to delete this photo?</p>
                <p class="modal-subtitle" id="modalPhotoTitle"></p>
                <p class="modal-warning">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="modal-btn modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <form id="deleteModalForm" method="POST" action="../processing/process_photo_delete.php" style="display: inline;">
                    <input type="hidden" name="photo_id" id="modalPhotoId">
                    <button type="submit" class="modal-btn modal-btn-danger">Delete Photo</button>
                </form>
            </div>
        </div>
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

        function showDeleteModal(form) {
            const modal = document.getElementById('deleteModal');
            const photoId = form.querySelector('input[name="photo_id"]').value;
            const photoTitle = form.getAttribute('data-photo-title');
            
            document.getElementById('modalPhotoId').value = photoId;
            document.getElementById('modalPhotoTitle').textContent = photoTitle ? `"${photoTitle}"` : '';
            
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            setTimeout(() => {
                modal.classList.add('active');
            }, 10);
        }

        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('active');
            document.body.style.overflow = '';
            
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('deleteModal');
                if (modal.style.display === 'flex') {
                    closeDeleteModal();
                }
            }
        });

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
    </script>
</body>
</html>
