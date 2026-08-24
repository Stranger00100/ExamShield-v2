<?php
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="sidebar-brand-text">
            <h3>Exam Shield</h3>
            <p>Student Portal</p>
        </div>
    </div>
    <ul class="sidebar-nav">
        <li class="<?= ($currentPage === 'dashboard.php') ? 'active' : '' ?>">
            <a href="dashboard.php"><i class="fa-solid fa-shapes"></i> Dashboard</a>
        </li>
        <li class="<?= ($currentPage === 'join_exam.php') ? 'active' : '' ?>">
            <a href="join_exam.php"><i class="fa-solid fa-right-to-bracket"></i> Join Exam</a>
        </li>
        <li class="<?= ($currentPage === 'results.php') ? 'active' : '' ?>">
            <a href="results.php"><i class="fa-solid fa-chart-simple"></i> Results</a>
        </li>
        <li class="<?= ($currentPage === 'profile.php') ? 'active' : '' ?>">
            <a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a>
        </li>
    </ul>
    <div class="sidebar-logout">
        <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
    </div>
</aside>