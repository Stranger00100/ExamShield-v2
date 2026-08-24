<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$studentName = $_SESSION['name'] ?? 'Student';
$initial = strtoupper(substr($studentName, 0, 1));
?>
<header class="top-navbar">
    <div class="search-input-box">
        <i class="fa-solid fa-magnifying-glass" style="color: #94a3b8;"></i>
        <input type="text" placeholder="Search exams...">
    </div>
    <div class="user-profile-badge">
        <div class="avatar-circle"><?= $initial ?></div>
        <div>
            <strong style="display: block; font-size: 0.9rem;"><?= htmlspecialchars($studentName) ?></strong>
            <small style="color: #64748b; font-size: 0.8rem;">Student</small>
        </div>
    </div>
</header>