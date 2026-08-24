<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Student Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];

// Fetch individual student results securely with MySQLi
$stmt = $conn->prepare("
    SELECT 
        e.title, e.subject, e.total_marks,
        se.submitted_at, se.final_score, se.marks_deducted, se.status
    FROM student_exams se 
    INNER JOIN exams e ON se.exam_id = e.id 
    WHERE se.student_id = ? AND se.status = 'submitted'
    ORDER BY se.submitted_at DESC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$res = $stmt->get_result();
$results = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Examination Results - Exam Shield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student/student_common.css">
    <link rel="stylesheet" href="../assets/css/student/student_results.css">
</head>
<body>
    <!-- Student Sidebar -->
    <?php include __DIR__ . '/includes/student_sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <?php include __DIR__ . '/includes/student_header.php'; ?>

        <main class="page-container">
            <div class="results-page-container">
                <div class="results-header-banner">
                    <div>
                        <h2>Performance & Results</h2>
                        <p>Review your completed examinations, final scores, and evaluation status.</p>
                    </div>
                    <div class="results-stats-badge">
                        <i class="fa-solid fa-graduation-cap"></i>
                        <span>Completed: <?= count($results) ?></span>
                    </div>
                </div>

                <div class="results-card">
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Exam Title</th>
                                <th>Subject</th>
                                <th>Submission Date</th>
                                <th>Score Obtained</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($results)): ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px 20px;">
                                        <p style="color: var(--text-muted); font-size: 0.95rem;">No completed examination records found.</p>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($results as $item): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($item['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($item['subject']) ?></td>
                                        <td><?= date('M d, Y - h:i A', strtotime($item['submitted_at'])) ?></td>
                                        <td>
                                            <?php if ($item['final_score'] !== null): ?>
                                                <div class="score-pill">
                                                    <span><?= (float)$item['final_score'] ?></span> / <?= (int)$item['total_marks'] ?>
                                                    <?php if ((int)$item['marks_deducted'] > 0): ?>
                                                        <span class="deduction-tag">(-<?= (int)$item['marks_deducted'] ?> penalty)</span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted); font-size: 0.85rem;">Pending Evaluation</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($item['final_score'] !== null): ?>
                                                <span class="status-badge-evaluated"><i class="fa-solid fa-check"></i> Graded</span>
                                            <?php else: ?>
                                                <span class="status-badge-pending"><i class="fa-solid fa-clock"></i> Under Review</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>
</html>