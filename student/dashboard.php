<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$student_id = $_SESSION['user_id'];
$student_name = $_SESSION['full_name'] ?? 'Student';
$error_msg = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : '';

// Fetch active exams available to join
$stmt_active = $conn->prepare("SELECT id, title, subject, room_code, duration, total_marks, exam_date FROM exams WHERE status = 'Active' ORDER BY id DESC");
$stmt_active->execute();
$active_exams = $stmt_active->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_active->close();

// Fetch student's previous submissions history
$stmt_history = $conn->prepare("SELECT 
                                  sub.id,
                                  e.title AS exam_title,
                                  e.subject,
                                  sub.marks_obtained,
                                  e.total_marks,
                                  sub.status,
                                  sub.submitted_at
                                FROM submissions sub
                                JOIN exams e ON sub.exam_id = e.id
                                WHERE sub.student_id = ?
                                ORDER BY sub.submitted_at DESC");
$stmt_history->bind_param("i", $student_id);
$stmt_history->execute();
$submission_history = $stmt_history->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_history->close();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard - Exam Shield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/student.css">
</head>
<body style="background-color: #f8fafc; margin: 0; font-family: 'Poppins', sans-serif;">

<?php include 'includes/header.php'; ?>

<div class="student-container">

    <!-- Welcome Hero Banner -->
    <div class="welcome-banner">
        <h1>Welcome Back, <?php echo htmlspecialchars($student_name); ?>!</h1>
        <p>Ready to start your test? Enter your unique Room Code below to begin your examination session.</p>
    </div>

    <?php if (!empty($error_msg)): ?>
        <div style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 20px;">
            ⚠️ <?php echo $error_msg; ?>
        </div>
    <?php endif; ?>

    <!-- Join Room Card -->
    <div class="join-room-card">
        <h3>Join Exam Room</h3>
        <p>Enter the 6-character Room Code provided by your teacher (e.g., ESE3F7).</p>
        <form action="join_exam.php" method="GET">
            <div class="join-input-group">
                <input type="text" name="room_code" class="room-input" placeholder="ENTER ROOM CODE" maxlength="10" required autocomplete="off">
                <button type="submit" class="btn-join">
                    <i data-lucide="log-in" style="width: 18px; height: 18px;"></i> Enter Room
                </button>
            </div>
        </form>
    </div>

    <!-- Active Live Exams Grid -->
    <h2 class="section-title">Active Examinations</h2>
    <?php if (empty($active_exams)): ?>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; text-align: center; color: #64748b; margin-bottom: 2rem;">
            <i data-lucide="check-circle" style="width: 40px; height: 40px; margin-bottom: 0.5rem; color: #10b981;"></i>
            <p style="margin: 0;">No live exams hosting right now. Check back when your session starts!</p>
        </div>
    <?php else: ?>
        <div class="exams-grid">
            <?php foreach ($active_exams as $ex): ?>
                <div class="student-exam-card">
                    <div>
                        <h3 class="exam-card-title"><?php echo htmlspecialchars($ex['title']); ?></h3>
                        <div class="exam-card-sub"><?php echo htmlspecialchars($ex['subject']); ?></div>
                        
                        <div class="exam-meta-list">
                            <div class="meta-item">
                                <span>Room Code:</span>
                                <strong><span style="font-family: monospace; color: #2563eb;"><?php echo htmlspecialchars($ex['room_code']); ?></span></strong>
                            </div>
                            <div class="meta-item">
                                <span>Duration:</span>
                                <strong><?php echo htmlspecialchars($ex['duration']); ?> mins</strong>
                            </div>
                            <div class="meta-item">
                                <span>Total Marks:</span>
                                <strong><?php echo htmlspecialchars($ex['total_marks']); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <a href="join_exam.php?room_code=<?php echo urlencode($ex['room_code']); ?>" class="btn-join" style="text-decoration: none; justify-content: center;">
                        Start Examination →
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Recent Performance Table -->
    <h2 class="section-title">Past Submission History</h2>
    <div class="table-card">
        <?php if (empty($submission_history)): ?>
            <div style="padding: 2rem; text-align: center; color: #64748b;">
                <i data-lucide="clock" style="width: 36px; height: 36px; margin-bottom: 0.5rem; opacity: 0.5;"></i>
                <p style="margin: 0;">You haven't completed any examinations yet.</p>
            </div>
        <?php else: ?>
            <table class="history-table">
                <thead>
                    <tr>
                        <th>Exam Title</th>
                        <th>Subject</th>
                        <th>Score Obtained</th>
                        <th>Status</th>
                        <th>Submitted At</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($submission_history as $sub): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($sub['exam_title']); ?></strong></td>
                            <td><?php echo htmlspecialchars($sub['subject']); ?></td>
                            <td><strong><?php echo $sub['marks_obtained']; ?></strong> / <?php echo $sub['total_marks']; ?></td>
                            <td>
                                <span class="badge-status badge-submitted">
                                    ● <?php echo ucfirst(htmlspecialchars($sub['status'])); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($sub['submitted_at']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

</div>

<script>
if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>