<?php
session_start();

$activePage = "create_exam";
$pageTitle = "Create Exam";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Exam - Exam Shield</title>
    
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
        
        <?php include 'includes/exam_form.php'; ?>

    </div>
</div>

<!-- Cache-buster appended to force the browser to read the new local file changes instantly -->
<script src="../assets/js/exam.js?v=<?php echo time(); ?>"></script>

<?php include 'includes/footer.php'; ?>
