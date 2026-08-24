<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$exam_id = (int)($_GET['id'] ?? 0);
$room_code = trim($_GET['room_code'] ?? '');
$msg = trim($_GET['msg'] ?? '');

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// Update exam to published if room code is passed
if (!empty($room_code)) {
    $stmt_host = $conn->prepare("UPDATE exams SET status = 'published', room_code = ? WHERE id = ?");
    $stmt_host->bind_param("si", $room_code, $exam_id);
    $stmt_host->execute();
    $stmt_host->close();
}

// Fetch exam details
$stmt = $conn->prepare("SELECT * FROM exams WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$res = $stmt->get_result();
$exam = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$exam) {
    echo "Exam not found.";
    exit();
}

// Count joined students
$stmt_students = $conn->prepare("SELECT COUNT(*) AS total_students FROM student_exams WHERE exam_id = ?");
$stmt_students->bind_param("i", $exam_id);
$stmt_students->execute();
$res_st = $stmt_students->get_result();
$student_stats = $res_st ? $res_st->fetch_assoc() : ['total_students' => 0];
$stmt_students->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Exam - <?= htmlspecialchars($exam['title']) ?></title>
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
        .page-container { padding: 32px; max-width: 1000px; }
        
        .alert-success { background: #ecfdf5; border: 1px solid #a7f3d0; color: #059669; padding: 14px 18px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 10px; font-weight: 500; font-size: 0.95rem; }
        .manage-card { background: #fff; border: 1px solid var(--border-color); border-radius: 16px; padding: 32px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
        .exam-meta-title h2 { font-size: 1.5rem; font-weight: 700; margin-bottom: 4px; }
        .exam-meta-title p { color: var(--text-muted); font-size: 0.9rem; }
        .badge-status { background: #ecfdf5; color: #059669; font-weight: 700; padding: 3px 10px; border-radius: 20px; font-size: 0.75rem; text-transform: uppercase; }

        .room-code-box { background: #f8fafc; border: 2px dashed #93c5fd; border-radius: 14px; padding: 24px; text-align: center; margin: 28px 0; }
        .room-code-box span { font-size: 0.8rem; font-weight: 700; color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; }
        .room-code-badge { font-size: 2.2rem; font-weight: 800; color: var(--primary-blue); letter-spacing: 4px; margin-top: 6px; }

        .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; margin-bottom: 28px; }
        .stat-pill { background: #f8fafc; border: 1px solid var(--border-color); border-radius: 12px; padding: 16px; }
        .stat-pill small { color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; font-weight: 600; display: block; margin-bottom: 4px; }
        .stat-pill strong { font-size: 1.25rem; font-weight: 700; color: var(--text-dark); }

        .btn-group { display: flex; gap: 12px; align-items: center; border-top: 1px solid var(--border-color); padding-top: 24px; }
        .btn { padding: 12px 24px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: 0.2s; cursor: pointer; }
        .btn-primary { background: var(--primary-blue); color: #fff; border: none; }
        .btn-primary:hover { background: #1d4ed8; }
        .btn-outline { background: transparent; border: 1px solid var(--border-color); color: var(--text-dark); }
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
            <li class="active"><a href="create_exam.php"><i class="fa-solid fa-file-circle-plus"></i> Exams</a></li>
            <li><a href="reports.php"><i class="fa-solid fa-chart-pie"></i> Reports</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
        </ul>
        <div class="sidebar-logout">
            <a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </aside>

    <div class="main-wrapper">
        <header class="top-navbar">
            <h2>Manage Exam</h2>
            <span style="font-size: 0.9rem; color: #64748b;">Host Controls</span>
        </header>

        <main class="page-container">
            <?php if ($msg === 'hosted' || !empty($room_code)): ?>
                <div class="alert-success">
                    <i class="fa-solid fa-circle-check"></i>
                    <span>Exam hosted and published successfully. Provide this room code to your students.</span>
                </div>
            <?php endif; ?>

            <div class="manage-card">
                <div class="exam-meta-title">
                    <h2><?= htmlspecialchars($exam['title']) ?></h2>
                    <p><?= htmlspecialchars($exam['subject']) ?> &bull; Status: <span class="badge-status"><?= ucfirst(htmlspecialchars($exam['status'])) ?></span></p>
                </div>

                <div class="room-code-box">
                    <span>Active Exam Room Code</span>
                    <div class="room-code-badge"><?= htmlspecialchars($exam['room_code']) ?></div>
                </div>

                <div class="stats-row">
                    <div class="stat-pill">
                        <small>Duration</small>
                        <strong><?= (int)$exam['duration'] ?> Mins</strong>
                    </div>
                    <div class="stat-pill">
                        <small>Total Marks</small>
                        <strong><?= (int)$exam['total_marks'] ?> Marks</strong>
                    </div>
                    <div class="stat-pill">
                        <small>Students Joined</small>
                        <strong><?= (int)$student_stats['total_students'] ?></strong>
                    </div>
                </div>

                <div class="btn-group">
                    <a href="submissions.php?exam_id=<?= $exam_id ?>" class="btn btn-primary">
                        <i class="fa-solid fa-list-check"></i> View Live Submissions
                    </a>
                    <a href="dashboard.php" class="btn btn-outline">
                        <i class="fa-solid fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>