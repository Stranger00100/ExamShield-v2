<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Authentication & Role Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$student_name = $_SESSION['name'] ?? 'Student';

// 1. Fetch Dynamic Student Statistics using MySQLi
$stmt_stats = $conn->prepare("
    SELECT 
        (SELECT COUNT(*) FROM exams WHERE status = 'published') AS available_exams,
        (SELECT COUNT(*) FROM student_exams WHERE student_id = ? AND status = 'submitted') AS completed_exams,
        (SELECT COUNT(*) FROM student_exams WHERE student_id = ? AND status = 'started') AS in_progress_exams,
        (SELECT AVG(final_score) FROM student_exams WHERE student_id = ? AND status = 'submitted' AND final_score IS NOT NULL) AS avg_score
");
$stmt_stats->bind_param("iii", $student_id, $student_id, $student_id);
$stmt_stats->execute();
$res_stats = $stmt_stats->get_result();
$stats = $res_stats ? $res_stats->fetch_assoc() : [];
$stmt_stats->close();

$available_count   = (int)($stats['available_exams'] ?? 0);
$completed_count   = (int)($stats['completed_exams'] ?? 0);
$in_progress_count = (int)($stats['in_progress_exams'] ?? 0);
$avg_score         = (isset($stats['avg_score']) && $stats['avg_score'] !== null) ? round((float)$stats['avg_score'], 1) . '%' : 'N/A';

// 2. Fetch Relevant Available Examinations
$stmt_exams = $conn->prepare("
    SELECT 
        e.id, e.title, e.subject, e.duration, e.total_marks, e.room_code,
        se.status AS attempt_status, se.id AS student_exam_id
    FROM exams e 
    LEFT JOIN student_exams se ON e.id = se.exam_id AND se.student_id = ?
    WHERE e.status = 'published'
    ORDER BY e.created_at DESC
");
$stmt_exams->bind_param("i", $student_id);
$stmt_exams->execute();
$res_exams = $stmt_exams->get_result();
$exams = $res_exams ? $res_exams->fetch_all(MYSQLI_ASSOC) : [];
$stmt_exams->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Exam Shield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student/student_common.css">
    <link rel="stylesheet" href="../assets/css/student/student_dashboard.css">
</head>
<body>
    <!-- Student Sidebar Navigation -->
    <?php include __DIR__ . '/includes/student_sidebar.php'; ?>

    <!-- Main Content Area -->
    <div class="main-wrapper">
        <?php include __DIR__ . '/includes/student_header.php'; ?>

        <main class="page-container">
            <!-- Metric Stats Row -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">
                        <i class="fa-solid fa-file-signature"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $available_count ?></span>
                        <span class="stat-label">Available Exams</span>
                        <span class="stat-subtext"><?= $available_count === 0 ? 'No active exams' : 'Ready to appear' ?></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon amber">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $in_progress_count ?></span>
                        <span class="stat-label">In Progress</span>
                        <span class="stat-subtext"><?= $in_progress_count === 0 ? 'No active sessions' : 'Ongoing attempts' ?></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $completed_count ?></span>
                        <span class="stat-label">Completed Exams</span>
                        <span class="stat-subtext"><?= $completed_count === 0 ? 'No submissions yet' : 'Graded papers' ?></span>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fa-solid fa-chart-line"></i>
                    </div>
                    <div class="stat-details">
                        <span class="stat-value"><?= $avg_score ?></span>
                        <span class="stat-label">Average Score</span>
                        <span class="stat-subtext">Overall performance</span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <section class="quick-actions-section">
                <h3 class="section-title">Quick Actions</h3>
                <div class="action-grid">
                    <div class="action-card">
                        <div>
                            <div class="action-card-header">
                                <div class="action-card-icon"><i class="fa-solid fa-key"></i></div>
                                <h4>Join Examination</h4>
                            </div>
                            <p>Enter an exam room code given by your teacher to access the test window.</p>
                        </div>
                        <a href="join_exam.php" class="action-link">Enter Room Code &rarr;</a>
                    </div>

                    <div class="action-card">
                        <div>
                            <div class="action-card-header">
                                <div class="action-card-icon"><i class="fa-solid fa-square-poll-vertical"></i></div>
                                <h4>View Results</h4>
                            </div>
                            <p>Review submitted assessments, total marks obtained, and exam evaluations.</p>
                        </div>
                        <a href="results.php" class="action-link">View Results &rarr;</a>
                    </div>

                    <div class="action-card">
                        <div>
                            <div class="action-card-header">
                                <div class="action-card-icon"><i class="fa-solid fa-id-card"></i></div>
                                <h4>Profile Details</h4>
                            </div>
                            <p>Check your student registration data, institutional email, and account status.</p>
                        </div>
                        <a href="profile.php" class="action-link">View Profile &rarr;</a>
                    </div>
                </div>
            </section>

            <!-- Published Examinations List -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3>Published Examinations</h3>
                </div>
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Exam Title</th>
                                <th>Subject</th>
                                <th>Duration</th>
                                <th>Total Marks</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($exams)): ?>
                                <tr>
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <i class="fa-regular fa-folder-open"></i>
                                            <p>No exams available. Your teacher hasn't published any exams yet.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($exams as $exam): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($exam['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($exam['subject']) ?></td>
                                        <td><?= (int)$exam['duration'] ?> Mins</td>
                                        <td><?= (int)$exam['total_marks'] ?> Marks</td>
                                        <td>
                                            <?php if ($exam['attempt_status'] === 'submitted'): ?>
                                                <span class="badge badge-completed">Completed</span>
                                            <?php elseif ($exam['attempt_status'] === 'started'): ?>
                                                <span class="badge badge-in-progress">In Progress</span>
                                            <?php else: ?>
                                                <span class="badge badge-published">Available</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($exam['attempt_status'] === 'submitted'): ?>
                                                <a href="results.php" class="btn-table btn-table-outline">View Result</a>
                                            <?php elseif ($exam['attempt_status'] === 'started'): ?>
                                                <a href="exam.php?exam_id=<?= (int)$exam['id'] ?>" class="btn-table btn-table-warning">Continue</a>
                                            <?php else: ?>
                                                <a href="instructions.php?exam_id=<?= (int)$exam['id'] ?>" class="btn-table btn-table-primary">Enter Exam</a>
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