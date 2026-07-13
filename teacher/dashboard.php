<?php

session_start();

$activePage = "dashboard";
$pageTitle = "Dashboard";

?>

<!DOCTYPE html>
<html>

<head>

    <title>Teacher Dashboard</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/teacher.css">

</head>

<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

<?php include 'includes/header.php'; ?>

<div class="dashboard-content fade-in">
   <div class="stats-grid">

    <!-- Total Exams -->
    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon blue">
                <i data-lucide="book-copy"></i>
            </div>

            <div class="trend positive">
                +0%
            </div>

        </div>

        <h2>0</h2>

        <h4>Total Exams</h4>

        <p>No exams created yet</p>

    </div>

    <!-- Active Exams -->
    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon green">
                <i data-lucide="play-circle"></i>
            </div>

            <div class="trend neutral">
                0
            </div>

        </div>

        <h2>0</h2>

        <h4>Active Exams</h4>

        <p>No active examinations</p>

    </div>

    <!-- Students Appeared -->
    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon orange">
                <i data-lucide="graduation-cap"></i>
            </div>

            <div class="trend neutral">
                0
            </div>

        </div>

        <h2>0</h2>

        <h4>Students Appeared</h4>

        <p>No student submissions yet</p>

    </div>

    <!-- Reports -->
    <div class="stat-card">

        <div class="stat-top">

            <div class="stat-icon purple">
                <i data-lucide="pie-chart"></i>
            </div>

            <div class="trend neutral">
                0
            </div>

        </div>

        <h2>0</h2>

        <h4>Reports Generated</h4>

        <p>No reports available</p>

    </div>

</div>

</div>

<div class="section">

    <div class="section-header">
        <h2>Quick Actions</h2>
        <p>Frequently used shortcuts</p>
    </div>

    <div class="quick-grid">

        <!-- Create Exam -->

        <a href="create_exam.php" class="quick-card">

            <div class="quick-icon blue">
                <i data-lucide="file-plus-2"></i>
            </div>

            <h3>Create Exam</h3>

            <p>Create a new coding examination with custom settings and question papers.</p>

            <span class="quick-link">
                Open →
            </span>

        </a>

        <!-- Manage Exams -->

        <a href="manage_exams.php" class="quick-card">

            <div class="quick-icon green">
                <i data-lucide="clipboard-list"></i>
            </div>

            <h3>Manage Exams</h3>

            <p>Edit, publish, archive, or delete previously created examinations.</p>

            <span class="quick-link">
                Open →
            </span>

        </a>

        <!-- Reports -->

        <a href="reports.php" class="quick-card">

            <div class="quick-icon purple">
                <i data-lucide="bar-chart-3"></i>
            </div>

            <h3>Reports</h3>

            <p>Analyze student performance and export detailed examination reports.</p>

            <span class="quick-link">
                Open →
            </span>

        </a>

    </div>

</div>

<div class="section">

    <div class="section-header">

        <div>

            <h2>Recent Exams</h2>

            <p class="section-subtitle">
                Your recently created coding examinations.
            </p>

        </div>

        <a href="create_exam.php" class="btn-primary">

            <i data-lucide="plus"></i>

            Create Exam

        </a>

    </div>

    <div class="table-responsive">

        <table class="dashboard-table">

            <thead>

                <tr>

                    <th>Exam Name</th>

                    <th>Room Code</th>

                    <th>Status</th>

                    <th>Date</th>

                    <th>Actions</th>

                </tr>

            </thead>

            <tbody>

                <tr>

                    <td colspan="5">

                        <div class="table-empty">

                            <i data-lucide="clipboard-x"></i>

                            <h3>No Exams Found</h3>

                            <p>Create your first coding examination to get started.</p>

                            <a href="create_exam.php" class="btn-primary">

                                <i data-lucide="plus"></i>

                                Create First Exam

                            </a>

                        </div>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>
<div class="section">

    <div class="section-header">

        <div>

            <h2>Live Exam Monitor</h2>

            <p class="section-subtitle">
                Monitor currently running examinations in real time.
            </p>

        </div>

        <span class="live-badge">

            <span class="live-dot"></span>

            LIVE

        </span>

    </div>

    <div class="live-monitor-empty">

        <div class="monitor-icon">

            <i data-lucide="monitor-smartphone"></i>

        </div>

        <h3>No Live Exams</h3>

        <p>

            Published examinations will automatically appear here once students begin attempting them.

        </p>

        <a href="create_exam.php" class="btn-primary">

            <i data-lucide="rocket"></i>

            Create Your First Exam

        </a>

    </div>

</div>

</div>

</div>
<div class="toast" id="toast">

    <i data-lucide="check-circle"></i>

    <span>

        Action completed successfully.

    </span>

</div>

<?php include 'includes/footer.php'; ?>

</body>

</html>