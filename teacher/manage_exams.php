<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$teacher_id = (int)$_SESSION['user_id'];

// Handle Exam Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $delete_id = intval($_GET['id']);

    // Check ownership before deletion
    $stmt_check = $conn->prepare("SELECT id FROM exams WHERE id = ? AND created_by = ?");
    $stmt_check->bind_param("ii", $delete_id, $teacher_id);
    $stmt_check->execute();
    $res_check = $stmt_check->get_result();
    $owned_exam = $res_check ? $res_check->fetch_assoc() : null;
    $stmt_check->close();

    if ($owned_exam) {
        // 1. Delete dependent submissions & logs tied to student_exams for this exam
        $stmt_del_subs = $conn->prepare("
            DELETE ss FROM student_submissions ss
            INNER JOIN student_exams se ON ss.student_exam_id = se.id
            WHERE se.exam_id = ?
        ");
        $stmt_del_subs->bind_param("i", $delete_id);
        $stmt_del_subs->execute();
        $stmt_del_subs->close();

        $stmt_del_logs = $conn->prepare("
            DELETE eal FROM exam_activity_logs eal
            INNER JOIN student_exams se ON eal.student_exam_id = se.id
            WHERE se.exam_id = ?
        ");
        $stmt_del_logs->bind_param("i", $delete_id);
        $stmt_del_logs->execute();
        $stmt_del_logs->close();

        // 2. Delete student exam sessions
        $stmt_del_se = $conn->prepare("DELETE FROM student_exams WHERE exam_id = ?");
        $stmt_del_se->bind_param("i", $delete_id);
        $stmt_del_se->execute();
        $stmt_del_se->close();

        // 3. Delete associated questions
        $stmt_del_q = $conn->prepare("DELETE FROM questions WHERE exam_id = ?");
        $stmt_del_q->bind_param("i", $delete_id);
        $stmt_del_q->execute();
        $stmt_del_q->close();

        // 4. Delete the exam
        $stmt_del_e = $conn->prepare("DELETE FROM exams WHERE id = ? AND created_by = ?");
        $stmt_del_e->bind_param("ii", $delete_id, $teacher_id);
        $stmt_del_e->execute();
        $stmt_del_e->close();

        header("Location: manage_exams.php?msg=deleted");
        exit();
    } else {
        header("Location: manage_exams.php?msg=error");
        exit();
    }
}

// Fetch all exams created by this teacher
$stmt = $conn->prepare("SELECT * FROM exams WHERE created_by = ? ORDER BY id DESC");
$stmt->bind_param("i", $teacher_id);
$stmt->execute();
$exams = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$activePage = "manage_exam";
$pageTitle = "Manage Exams";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exams - Exam Shield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <link rel="stylesheet" href="../assets/css/manage-exams.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-content fade-in">

        <div class="manage-exams-header">
            <div>
                <h2>All Created Exams</h2>
                <p>View, host, and manage all your scheduled or active exams.</p>
            </div>
            <a href="create_exam.php" class="btn-primary" style="text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                <i data-lucide="plus-circle"></i> Create New Exam
            </a>
        </div>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success" style="background: #dcfce7; color: #15803d; padding: 1rem; border-radius: 8px; margin-bottom: 20px;">
                Exam deleted successfully.
            </div>
        <?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'error'): ?>
            <div class="alert alert-danger" style="background: #fee2e2; color: #b91c1c; padding: 1rem; border-radius: 8px; margin-bottom: 20px;">
                Unable to delete exam or unauthorized access.
            </div>
        <?php endif; ?>

        <?php if (empty($exams)): ?>
            <div class="card" style="text-align: center; padding: 3rem; background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0;">
                <i data-lucide="file-text" style="width: 48px; height: 48px; color: #94a3b8; margin-bottom: 1rem;"></i>
                <h3 style="margin: 0 0 0.5rem 0; color: #1e293b;">No Exams Found</h3>
                <p style="color: #64748b; margin-bottom: 1.5rem;">You haven't created any exams yet.</p>
                <a href="create_exam.php" class="btn-primary" style="text-decoration: none;">Create Your First Exam</a>
            </div>
        <?php else: ?>
            <div class="exams-grid">
                <?php foreach ($exams as $e): 
                    $raw_status = strtolower($e['status'] ?? 'draft');
                    $is_active = ($raw_status === 'published' || $raw_status === 'active');
                    $is_draft = ($raw_status === 'draft' || empty($raw_status));
                    $status_class = $is_active ? 'status-active' : ($is_draft ? 'status-draft' : 'status-ended');
                    $display_status = $is_active ? 'Published' : ($is_draft ? 'Draft' : ucfirst($raw_status));
                ?>
                    <div class="exam-card">
                        <div class="exam-card-header">
                            <div>
                                <h3 class="exam-title"><?= htmlspecialchars($e['title']) ?></h3>
                                <span class="exam-subject"><?= htmlspecialchars($e['subject']) ?> • Sem <?= htmlspecialchars($e['semester'] ?? '1') ?></span>
                            </div>
                            <span class="status-badge <?= $status_class ?>">
                                ● <?= htmlspecialchars($display_status) ?>
                            </span>
                        </div>

                        <div class="exam-card-body">
                            <div class="info-row">
                                <span class="label">Room Code:</span>
                                <span class="value code-text"><?= htmlspecialchars($e['room_code'] ?? 'N/A') ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Duration:</span>
                                <span class="value"><?= (int)($e['duration'] ?? 0) ?> Mins</span>
                            </div>
                            <div class="info-row">
                                <span class="label">Total Marks:</span>
                                <span class="value"><?= (int)($e['total_marks'] ?? 0) ?></span>
                            </div>
                            <div class="info-row">
                                <span class="label">Date:</span>
                                <span class="value"><?= !empty($e['exam_date']) ? htmlspecialchars($e['exam_date']) : 'Not Scheduled' ?></span>
                            </div>
                        </div>

                        <div class="exam-card-actions">
                            <a href="manage_exam.php?id=<?= (int)$e['id'] ?>" class="btn-sm btn-view">
                                <i data-lucide="eye" style="width: 14px; height: 14px;"></i> View
                            </a>
                            <a href="create_questions.php?id=<?= (int)$e['id'] ?>" class="btn-sm btn-edit">
                                <i data-lucide="edit-3" style="width: 14px; height: 14px;"></i> Questions
                            </a>
                            <a href="manage_exams.php?action=delete&id=<?= (int)$e['id'] ?>" class="btn-sm btn-delete" onclick="return confirm('Are you sure you want to delete this exam and all its associated submissions?');">
                                <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i> Delete
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
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