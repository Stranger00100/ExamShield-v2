<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Handle Settings Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Action 1: Update Account Profile & Avatar Photo
    if ($action === 'update_account') {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $profile_img_filename = null;

        // Handle File Upload
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['profile_image']['tmp_name'];
            $file_name = $_FILES['profile_image']['name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array($file_ext, $allowed_extensions)) {
                $new_filename = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
                $upload_dir   = __DIR__ . '/../uploads/';

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                if (move_uploaded_file($file_tmp, $upload_dir . $new_filename)) {
                    $profile_img_filename = $new_filename;
                }
            }
        }

        if (!empty($full_name) && !empty($email)) {
            if ($profile_img_filename) {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, profile_image = ? WHERE id = ?");
                $stmt->bind_param("sssi", $full_name, $email, $profile_img_filename, $user_id);
            } else {
                $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ? WHERE id = ?");
                $stmt->bind_param("ssi", $full_name, $email, $user_id);
            }

            if ($stmt->execute()) {
                $success_msg = "Profile updated successfully.";
            } else {
                $error_msg = "Failed to update profile.";
            }
            $stmt->close();
        }
    }

    // Action 2: Change Password
    if ($action === 'change_password') {
        $current_pass = $_POST['current_password'] ?? '';
        $new_pass     = $_POST['new_password'] ?? '';

        $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_data = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if ($user_data && password_verify($current_pass, $user_data['password'])) {
            $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
            $stmt_up = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
            $stmt_up->bind_param("si", $new_hash, $user_id);
            if ($stmt_up->execute()) {
                $success_msg = "Password changed successfully.";
            } else {
                $error_msg = "Failed to update password.";
            }
            $stmt_up->close();
        } else {
            $error_msg = "Incorrect current password.";
        }
    }
}

// Fetch user details
$stmt = $conn->prepare("SELECT full_name, email, profile_image FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

$user_name = $user['full_name'] ?? 'Teacher Administrator';
$user_email = $user['email'] ?? 'teacher@examshield.com';
$profile_image = $user['profile_image'] ?? null;
$first_letter = strtoupper(substr($user_name, 0, 1));

$activePage = "settings";
$pageTitle = "Settings";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Settings - Exam Shield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-content fade-in">

        <div class="settings-container">

            <?php if (!empty($success_msg)): ?>
                <div class="alert alert-success" style="background: #dcfce7; color: #15803d; padding: 1rem; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($success_msg); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger" style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 20px;">
                    <?php echo htmlspecialchars($error_msg); ?>
                </div>
            <?php endif; ?>

            <!-- Profile Tile -->
            <div class="profile-card">
                <div class="profile-avatar-container" onclick="openModal('accountModal')">
                    <div class="profile-avatar">
                        <?php if ($profile_image && file_exists(__DIR__ . '/../uploads/' . $profile_image)): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($profile_image); ?>" alt="Profile">
                        <?php else: ?>
                            <?php echo $first_letter; ?>
                        <?php endif; ?>
                    </div>
                    <div class="avatar-edit-overlay">
                        <i data-lucide="camera" style="width: 14px; height: 14px;"></i>
                    </div>
                </div>
                <div class="profile-info">
                    <h3><?php echo htmlspecialchars($user_name); ?></h3>
                    <p><?php echo htmlspecialchars($user_email); ?></p>
                    <span class="profile-badge">Faculty / Examiner</span>
                </div>
            </div>

            <!-- Settings Options List -->
            <div class="settings-group">
                
                <div class="settings-item" onclick="openModal('accountModal')">
                    <div class="item-left">
                        <div class="icon-wrapper bg-blue"><i data-lucide="user"></i></div>
                        <div class="item-text">
                            <h4>Account Profile</h4>
                            <p>Update full name, email address, and profile photo</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="chevron-icon"></i>
                </div>

                <div class="settings-item" onclick="openModal('securityModal')">
                    <div class="item-left">
                        <div class="icon-wrapper bg-green"><i data-lucide="shield-check"></i></div>
                        <div class="item-text">
                            <h4>Security & Password</h4>
                            <p>Update account login credentials and password</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="chevron-icon"></i>
                </div>

                <div class="settings-item" onclick="openModal('notificationsModal')">
                    <div class="item-left">
                        <div class="icon-wrapper bg-amber"><i data-lucide="bell"></i></div>
                        <div class="item-text">
                            <h4>Notifications</h4>
                            <p>Manage session updates and submission alerts</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="chevron-icon"></i>
                </div>

            </div>

            <!-- Danger Zone -->
            <div class="settings-group">
                <a href="logout.php" class="settings-item">
                    <div class="item-left">
                        <div class="icon-wrapper bg-red"><i data-lucide="log-out"></i></div>
                        <div class="item-text">
                            <h4 style="color: #ef4444;">Log Out</h4>
                            <p>Sign out of your session</p>
                        </div>
                    </div>
                    <i data-lucide="chevron-right" class="chevron-icon"></i>
                </a>
            </div>

        </div>

    </div>

</div>

<!-- 1. Account Profile & Photo Upload Modal -->
<div id="accountModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Edit Profile & Photo</h3>
            <button class="close-btn" onclick="closeModal('accountModal')">&times;</button>
        </div>
        <form method="POST" action="settings.php" enctype="multipart/form-data">
            <input type="hidden" name="action" value="update_account">
            <div class="form-group">
                <label>Profile Picture</label>
                <input type="file" name="profile_image" accept="image/*" class="form-control">
            </div>
            <div class="form-group">
                <label>Full Name</label>
                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($user_name); ?>" required>
            </div>
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($user_email); ?>" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('accountModal')">Cancel</button>
                <button type="submit" class="btn-primary">Save Profile</button>
            </div>
        </form>
    </div>
</div>

<!-- 2. Security Modal -->
<div id="securityModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Change Password</h3>
            <button class="close-btn" onclick="closeModal('securityModal')">&times;</button>
        </div>
        <form method="POST" action="settings.php">
            <input type="hidden" name="action" value="change_password">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" class="form-control" required>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeModal('securityModal')">Cancel</button>
                <button type="submit" class="btn-primary">Update Password</button>
            </div>
        </form>
    </div>
</div>

<!-- 3. Notifications Modal -->
<div id="notificationsModal" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-header">
            <h3>Notifications</h3>
            <button class="close-btn" onclick="closeModal('notificationsModal')">&times;</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 12px;">
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <span style="font-size: 0.9rem; color: #1e293b;">Notify on student submission</span>
            </label>
            <label style="display: flex; align-items: center; gap: 10px; cursor: pointer;">
                <input type="checkbox" checked style="width: 18px; height: 18px;">
                <span style="font-size: 0.9rem; color: #1e293b;">Notify when student joins room</span>
            </label>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-primary" onclick="closeModal('notificationsModal')">Done</button>
        </div>
    </div>
</div>

<script>
function openModal(id) {
    document.getElementById(id).classList.add('active');
}

function closeModal(id) {
    document.getElementById(id).classList.remove('active');
}

if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>