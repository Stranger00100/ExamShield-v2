<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Authentication & Role Gatekeeper
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_code = trim($_POST['room_code'] ?? '');

    if (empty($room_code)) {
        $error = "Please enter an exam room code.";
    } else {
        // 1. Verify Room Code against published exams
        $stmt = $conn->prepare("SELECT id, title, status FROM exams WHERE room_code = ? AND status = 'published' LIMIT 1");
        $stmt->bind_param("s", $room_code);
        $stmt->execute();
        $res = $stmt->get_result();
        $exam = $res ? $res->fetch_assoc() : null;
        $stmt->close();

        if (!$exam) {
            $error = "Invalid room code or the exam is not currently active.";
        } else {
            $exam_id = (int)$exam['id'];

            // 2. Strict Check: If attempt is already submitted, permanently lock access
            $stmt_chk = $conn->prepare("SELECT id, status FROM student_exams WHERE student_id = ? AND exam_id = ? LIMIT 1");
            $stmt_chk->bind_param("ii", $student_id, $exam_id);
            $stmt_chk->execute();
            $res_chk = $stmt_chk->get_result();
            $attempt = $res_chk ? $res_chk->fetch_assoc() : null;
            $stmt_chk->close();

            if ($attempt) {
                if ($attempt['status'] === 'submitted') {
                    $error = "You have already completed and submitted this examination. Re-entry is prohibited.";
                } elseif ($attempt['status'] === 'started') {
                    header("Location: exam.php?exam_id=" . $exam_id);
                    exit();
                } else {
                    header("Location: instructions.php?exam_id=" . $exam_id);
                    exit();
                }
            } else {
                // Register fresh joined attempt
                $stmt_reg = $conn->prepare("INSERT INTO student_exams (student_id, exam_id, status) VALUES (?, ?, 'joined')");
                $stmt_reg->bind_param("ii", $student_id, $exam_id);
                if ($stmt_reg->execute()) {
                    $stmt_reg->close();
                    header("Location: instructions.php?exam_id=" . $exam_id);
                    exit();
                } else {
                    $error = "Unable to register your exam session. Please try again.";
                    $stmt_reg->close();
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join Exam - Exam Shield</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student/student_common.css">
    <link rel="stylesheet" href="../assets/css/student/student_joinexam.css">
</head>
<body>
    <?php include __DIR__ . '/includes/student_sidebar.php'; ?>

    <div class="main-wrapper">
        <?php include __DIR__ . '/includes/student_header.php'; ?>

        <main class="page-container">
            <div class="join-exam-wrapper">
                <div class="join-exam-card">
                    <div class="join-icon-badge">
                        <i class="fa-solid fa-door-open"></i>
                    </div>
                    <h2>Enter Exam Room</h2>
                    <p>Enter the unique room code assigned by your instructor to authenticate and access your test session.</p>

                    <?php if (!empty($error)): ?>
                        <div class="alert-box alert-danger">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <span><?= htmlspecialchars($error) ?></span>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="join_exam.php" class="join-form">
                        <div class="input-code-group">
                            <input 
                                type="text" 
                                name="room_code" 
                                placeholder="e.g. EXAM-1234" 
                                required 
                                autocomplete="off"
                                autofocus
                            >
                        </div>
                        <button type="submit" class="btn-join-submit">
                            <span>Verify & Proceed</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>