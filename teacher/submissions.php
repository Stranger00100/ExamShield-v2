<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$exam_id = (int)($_GET['exam_id'] ?? 0);

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Fetch Exam Info
$stmt_exam = $conn->prepare("SELECT title, subject, total_marks, room_code FROM exams WHERE id = ? LIMIT 1");
$stmt_exam->bind_param("i", $exam_id);
$stmt_exam->execute();
$res_exam = $stmt_exam->get_result();
$exam = $res_exam ? $res_exam->fetch_assoc() : null;
$stmt_exam->close();

if (!$exam) {
    echo "Exam not found.";
    exit();
}

// Check columns in users table to prevent Unknown column errors
$user_col_name = 'u.email';
$chk = $conn->query("SHOW COLUMNS FROM users LIKE 'name'");
if ($chk && $chk->num_rows > 0) {
    $user_col_name = 'u.name';
} else {
    $chk_user = $conn->query("SHOW COLUMNS FROM users LIKE 'username'");
    if ($chk_user && $chk_user->num_rows > 0) {
        $user_col_name = 'u.username';
    }
}

// Fetch submissions
$query = "
    SELECT 
        se.id AS student_exam_id,
        {$user_col_name} AS student_name,
        u.email AS student_email,
        se.status,
        se.started_at,
        se.submitted_at,
        se.final_score,
        se.marks_deducted
    FROM student_exams se
    INNER JOIN users u ON se.student_id = u.id
    WHERE se.exam_id = ?
    ORDER BY se.started_at DESC
";

$stmt_sub = $conn->prepare($query);
$stmt_sub->bind_param("i", $exam_id);
$stmt_sub->execute();
$res_sub = $stmt_sub->get_result();
$submissions = $res_sub ? $res_sub->fetch_all(MYSQLI_ASSOC) : [];
$stmt_sub->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submissions - <?= htmlspecialchars($exam['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-blue: #2563eb;
            --sidebar-bg: #0f172a;
            --body-bg: #f8fafc;
            --card-bg: #ffffff;
            --border-color: #e2e8f0;
            --text-dark: #0f172a;
            --text-muted: #64748b;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; }
        body { background: var(--body-bg); color: var(--text-dark); display: flex; min-height: 100vh; }
        .sidebar { width: 260px; background: var(--sidebar-bg); color: #fff; display: flex; flex-direction: column; padding: 24px 16px; flex-shrink: 0; }
        .sidebar-brand { display: flex; align-items: center; gap: 12px; padding: 0 12px 24px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .sidebar-brand i { color: #60a5fa; font-size: 1.4rem; }
        .sidebar-brand h3 { font-size: 1.1rem; }
        .sidebar-nav { list-style: none; margin-top: 24px; display: flex; flex-direction: column; gap: 8px; flex-grow: 1; }
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #94a3b8; text-decoration: none; font-weight: 500; border-radius: 10px; transition: 0.2s; }
        .sidebar-nav li.active a, .sidebar-nav a:hover { background: var(--primary-blue); color: #fff; }
        .sidebar-logout { margin-top: auto; }
        .sidebar-logout a { display: flex; align-items: center; justify-content: center; gap: 8px; background: #ef4444; color: #fff; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; }
        
        .main-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        .top-navbar { background: #fff; padding: 16px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .page-container { padding: 32px; }

        .card { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .card-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { background: #f8fafc; color: var(--text-muted); font-size: 0.8rem; text-transform: uppercase; font-weight: 600; padding: 12px 16px; border-bottom: 1px solid var(--border-color); }
        td { padding: 16px; border-bottom: 1px solid var(--border-color); font-size: 0.9rem; }
        
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
        .badge-joined { background: #f1f5f9; color: #475569; }
        .badge-started { background: #fef3c7; color: #b45309; }
        .badge-submitted { background: #ecfdf5; color: #059669; }

        .btn { padding: 8px 16px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
        .btn-outline { border: 1px solid var(--border-color); color: var(--text-dark); }
        .btn-outline:hover { background: #f1f5f9; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shield-halved"></i>
            <div>
                <h3>Exam Shield</h3>
                <small style="color: #94a3b8; font-size: 0.75rem;">Teacher Portal</small>
            </div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php"><i class="fa-solid fa-shapes"></i> Dashboard</a></li>
            <li class="active"><a href="manage_exam.php?id=<?= $exam_id ?>"><i class="fa-solid fa-file-circle-plus"></i> Exams</a></li>
            <li><a href="reports.php"><i class="fa-solid fa-chart-pie"></i> Reports</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
        </ul>
        <div class="sidebar-logout">
            <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="top-navbar">
            <h2>Live Submissions</h2>
            <a href="manage_exam.php?id=<?= $exam_id ?>" class="btn btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Exam</a>
        </header>

        <main class="page-container">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h3 style="font-size: 1.2rem;"><?= htmlspecialchars($exam['title']) ?></h3>
                        <p style="font-size: 0.85rem; color: #64748b;">Room Code: <strong><?= htmlspecialchars($exam['room_code']) ?></strong></p>
                    </div>
                    <span style="font-weight: 600; font-size: 0.9rem; color: #2563eb;">Total Candidates: <?= count($submissions) ?></span>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Started At</th>
                            <th>Status</th>
                            <th>Score</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submissions)): ?>
                            <tr>
                                <td colspan="4" style="text-align: center; padding: 40px; color: #64748b;">
                                    No students have joined this exam session yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $sub): ?>
                                <tr>
                                    <td>
                                        <strong><?= htmlspecialchars($sub['student_name']) ?></strong><br>
                                        <small style="color: #64748b;"><?= htmlspecialchars($sub['student_email']) ?></small>
                                    </td>
                                    <td><?= !empty($sub['started_at']) ? date('h:i A, M d', strtotime($sub['started_at'])) : '—' ?></td>
                                    <td>
                                        <span class="badge badge-<?= htmlspecialchars($sub['status']) ?>">
                                            <?= ucfirst(htmlspecialchars($sub['status'])) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?= $sub['final_score'] !== null ? htmlspecialchars($sub['final_score']) . ' / ' . (int)$exam['total_marks'] : 'Pending' ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</body>
</html>