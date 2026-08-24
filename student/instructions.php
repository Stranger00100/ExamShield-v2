<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$exam_id = (int)($_GET['exam_id'] ?? 0);

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// 1. Fetch Exam details and verify student registration via MySQLi
$stmt = $conn->prepare("
    SELECT e.id, e.title, e.subject, e.duration, e.total_marks, se.status AS attempt_status
    FROM exams e 
    INNER JOIN student_exams se ON e.id = se.exam_id 
    WHERE e.id = ? AND se.student_id = ? AND e.status = 'published'
    LIMIT 1
");
$stmt->bind_param("ii", $exam_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
$exam = $res ? $res->fetch_assoc() : null;
$stmt->close();

// Redirect if not registered or already submitted
if (!$exam || $exam['attempt_status'] === 'submitted') {
    header("Location: dashboard.php");
    exit();
}

// If student is already in progress, route directly to exam interface
if ($exam['attempt_status'] === 'started') {
    header("Location: exam.php?exam_id=" . $exam_id);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Instructions - Exam Shield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student/student_common.css">
    <link rel="stylesheet" href="../assets/css/student/student_instructions.css">
</head>
<body>
    <!-- Sidebar -->
    <?php include __DIR__ . '/includes/student_sidebar.php'; ?>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <?php include __DIR__ . '/includes/student_header.php'; ?>

        <main class="page-container">
            <div class="instructions-container">
                <div class="instructions-header">
                    <div>
                        <h2><?= htmlspecialchars($exam['title']) ?></h2>
                        <p>Standard Examination Guidelines & Rules</p>
                    </div>
                </div>

                <!-- Exam Meta Data -->
                <div class="exam-meta-grid">
                    <div class="meta-pill">
                        <i class="fa-solid fa-book-open"></i>
                        <div class="meta-pill-text">
                            <small>Subject</small>
                            <strong><?= htmlspecialchars($exam['subject']) ?></strong>
                        </div>
                    </div>
                    <div class="meta-pill">
                        <i class="fa-solid fa-clock"></i>
                        <div class="meta-pill-text">
                            <small>Duration</small>
                            <strong><?= (int)$exam['duration'] ?> Minutes</strong>
                        </div>
                    </div>
                    <div class="meta-pill">
                        <i class="fa-solid fa-trophy"></i>
                        <div class="meta-pill-text">
                            <small>Total Marks</small>
                            <strong><?= (int)$exam['total_marks'] ?> Marks</strong>
                        </div>
                    </div>
                </div>

                <!-- Instructions Rules -->
                <div class="rules-card">
                    <h4>Please read carefully before starting:</h4>
                    <ul class="rules-list">
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>You will receive 1 question from <strong>Part A</strong> and 1 question from <strong>Part B</strong> assigned securely by the server.</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Do not refresh the examination tab or close the browser window while attempting questions.</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Switching tabs, exiting fullscreen, or minimizing the browser will be flagged in activity logs.</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>You can request a question set swap, but it will incur a mark penalty deduction.</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle-check"></i>
                            <span>Once the countdown timer reaches zero, your code will be automatically saved and submitted.</span>
                        </li>
                    </ul>
                </div>

                <!-- Action / Agreement -->
                <div class="instructions-footer">
                    <label class="checkbox-container">
                        <input type="checkbox" id="agreementCheckbox">
                        <span>I have read, understood, and agree to the exam rules.</span>
                    </label>

                    <a href="exam.php?exam_id=<?= $exam_id ?>" id="startExamBtn" class="btn-start-exam disabled">
                        <span>Start Exam</span>
                        <i class="fa-solid fa-arrow-right"></i>
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script>
        const ack = document.getElementById('agreementCheckbox');
        const startBtn = document.getElementById('startExamBtn');

        ack.addEventListener('change', () => {
            if (ack.checked) {
                startBtn.classList.remove('disabled');
            } else {
                startBtn.classList.add('disabled');
            }
        });
    </script>
</body>
</html>