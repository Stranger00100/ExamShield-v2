<div class="sidebar">

   <div class="logo">

    <div class="logo-icon">
        <i data-lucide="shield-check"></i>
    </div>

    <div class="logo-text">
        <h2>Exam Shield</h2>
        <p>Teacher Panel</p>
    </div>

</div>
    <ul class="menu">

<li class="<?php echo ($activePage == 'dashboard') ? 'active' : ''; ?>">            <a href="dashboard.php">
               <i data-lucide="layout-dashboard"></i>
                Dashboard
            </a>
        </li>

        <li class="<?= ($activePage == 'create_exam') ? 'active' : ''; ?>">
            <a href="create_exam.php">
              <i data-lucide="file-plus-2"></i>
                Create Exam
            </a>
        </li>

        <li class="<?= ($activePage == 'manage_exam') ? 'active' : ''; ?>">
            <a href="manage_exams.php">
             <i data-lucide="book-open"></i>
                Manage Exams
            </a>
        </li>

        <li class="<?= ($activePage == 'students') ? 'active' : ''; ?>">
            <a href="students.php">
             <i data-lucide="users"></i>
                Students
            </a>
        </li>

        <li class="<?= ($activePage == 'reports') ? 'active' : ''; ?>">
            <a href="reports.php">
              <i data-lucide="bar-chart-3"></i>
                Reports
            </a>
        </li>

        <li class="<?= ($activePage == 'settings') ? 'active' : ''; ?>">
            <a href="settings.php">
              <i data-lucide="settings"></i>
                Settings
            </a>
        </li>

    </ul>

    <div class="logout">

        <a href="../logout.php">
            <i data-lucide="log-out"></i> Logout
        </a>

    </div>

</div>