<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Student Authentication Gatekeeper
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];

// Fetch user profile securely using MySQLi prepared statement
$stmt = $conn->prepare("SELECT id, name, email, role, created_at FROM users WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();
$user = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$user) {
    header("Location: ../logout.php");
    exit();
}

$displayName = $user['name'] ?? 'Student';
$initial = strtoupper(substr($displayName, 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Exam Shield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student/student_common.css">
    <link rel="stylesheet" href="../assets/css/student/student_profile.css">
</head>
<body>
    <!-- Student Sidebar -->
    <?php include __DIR__ . '/includes/student_sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <?php include __DIR__ . '/includes/student_header.php'; ?>

        <main class="page-container">
            <div class="profile-page-wrapper">
                <div class="profile-card">
                    <!-- Profile Header Banner -->
                    <div class="profile-header-section">
                        <div class="profile-avatar-large"><?= $initial ?></div>
                        <div class="profile-title-info">
                            <h2><?= htmlspecialchars($displayName) ?></h2>
                            <p>Registered Examination Student</p>
                        </div>
                    </div>

                    <!-- Personal & Account Metadata Grid -->
                    <div class="profile-details-grid">
                        <div class="profile-field-group">
                            <label>Full Name</label>
                            <span class="field-value"><?= htmlspecialchars($user['name']) ?></span>
                        </div>

                        <div class="profile-field-group">
                            <label>Student ID Number</label>
                            <span class="field-value">#<?= (int)$user['id'] ?></span>
                        </div>

                        <div class="profile-field-group">
                            <label>Registered Institutional Email</label>
                            <span class="field-value"><?= htmlspecialchars($user['email']) ?></span>
                        </div>

                        <div class="profile-field-group">
                            <label>Account Role</label>
                            <div class="role-badge-pill">
                                <i class="fa-solid fa-user-graduate"></i>
                                <span><?= ucfirst(htmlspecialchars($user['role'])) ?></span>
                            </div>
                        </div>

                        <div class="profile-field-group">
                            <label>Account Registration Date</label>
                            <span class="field-value">
                                <?= !empty($user['created_at']) ? date('F d, Y', strtotime($user['created_at'])) : 'Active' ?>
                            </span>
                        </div>

                        <div class="profile-field-group">
                            <label>System Portal Access</label>
                            <span class="field-value" style="color: #059669;">
                                <i class="fa-solid fa-circle-check"></i> Verified & Active
                            </span>
                        </div>
                    </div>

                    <!-- Readonly Notice -->
                    <div class="profile-notice-box">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Institutional credentials and identification numbers are managed by examination administrators. Contact your instructor for account modifications.</span>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>