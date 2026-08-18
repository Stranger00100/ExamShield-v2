<?php
$student_name = $_SESSION['full_name'] ?? 'Student';
?>
<header style="background: #ffffff; border-bottom: 1px solid #e2e8f0; padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center;">
    <div style="display: flex; align-items: center; gap: 10px;">
        <i data-lucide="shield-check" style="color: #2563eb; width: 28px; height: 28px;"></i>
        <span style="font-size: 1.2rem; font-weight: 700; color: #0f172a;">Exam Shield</span>
        <span style="background: #eff6ff; color: #2563eb; font-size: 0.75rem; font-weight: 600; padding: 2px 8px; border-radius: 12px;">Student Portal</span>
    </div>
    <div style="display: flex; align-items: center; gap: 15px;">
        <span style="font-size: 0.9rem; font-weight: 500; color: #334155;">👋 <?php echo htmlspecialchars($student_name); ?></span>
        <a href="../logout.php" style="color: #ef4444; text-decoration: none; font-size: 0.875rem; font-weight: 600; display: flex; align-items: center; gap: 4px;">
            <i data-lucide="log-out" style="width: 16px; height: 16px;"></i> Logout
        </a>
    </div>
</header>