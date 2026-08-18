<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$teacher_id = $_SESSION['user_id'];
$selected_exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

// Fetch all hosted/active exams for dropdown filter
$stmt_exams = $conn->prepare("SELECT id, title, room_code FROM exams WHERE created_by = ? ORDER BY id DESC");
$stmt_exams->bind_param("i", $teacher_id);
$stmt_exams->execute();
$hosted_exams = $stmt_exams->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_exams->close();
// Query updated to match your database column 'full_name'
$sql = "SELECT 
            s.id AS student_id,
            s.full_name AS student_name,
            s.email AS student_email,
            s.university_number,
            e.title AS exam_title,
            e.room_code,
            e.id AS exam_id,
            COALESCE(ex_sess.status, 'inactive') AS session_status,
            ex_sess.last_activity
        FROM users s
        CROSS JOIN exams e
        LEFT JOIN exam_sessions ex_sess ON ex_sess.student_id = s.id AND ex_sess.exam_id = e.id
        WHERE e.created_by = ? AND s.role = 'student'";
if ($selected_exam_id > 0) {
    $sql .= " AND e.id = ?";
}

$sql .= " ORDER BY ex_sess.last_activity DESC";

$stmt_students = $conn->prepare($sql);
if ($selected_exam_id > 0) {
    $stmt_students->bind_param("ii", $teacher_id, $selected_exam_id);
} else {
    $stmt_students->bind_param("i", $teacher_id);
}

$stmt_students->execute();
$students = $stmt_students->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_students->close();

// Active status calculation
$total_students = count($students);
$active_students = 0;
$current_time = time();

foreach ($students as &$st) {
    $last_active_time = strtotime($st['last_activity'] ?? '0');
    if (($current_time - $last_active_time) <= 120 && $st['session_status'] === 'in_progress') {
        $st['is_live'] = true;
        $active_students++;
    } else {
        $st['is_live'] = false;
    }
}
unset($st);

$activePage = "students";
$pageTitle = "Students";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Students Monitor - Exam Shield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <link rel="stylesheet" href="../assets/css/students.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-content fade-in">

        <div class="students-header">
            <div>
                <h2>Live Student Monitor</h2>
                <p>Track student participation, active room codes, and connection status in real-time.</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-card">
            <form method="GET" action="students.php" style="display: flex; align-items: center; gap: 12px; margin: 0;">
                <div class="filter-group">
                    <label for="exam_id">Filter by Exam Room:</label>
                    <select name="exam_id" id="exam_id" class="filter-select" onchange="this.form.submit()">
                        <option value="0">All Exam Rooms</option>
                        <?php foreach ($hosted_exams as $ex): ?>
                            <option value="<?php echo $ex['id']; ?>" <?php echo ($selected_exam_id === $ex['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($ex['title']); ?> (Code: <?php echo htmlspecialchars($ex['room_code']); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="stats-summary">
            <div class="summary-card">
                <div>
                    <span class="summary-lbl">Total Registered Students</span>
                    <div class="summary-val"><?php echo $total_students; ?></div>
                </div>
                <i data-lucide="users" style="color: #2563eb; width: 32px; height: 32px;"></i>
            </div>
            <div class="summary-card">
                <div>
                    <span class="summary-lbl">Active Now in Exam</span>
                    <div class="summary-val" style="color: #16a34a;"><?php echo $active_students; ?></div>
                </div>
                <i data-lucide="activity" style="color: #16a34a; width: 32px; height: 32px;"></i>
            </div>
            <div class="summary-card">
                <div>
                    <span class="summary-lbl">Inactive / Completed</span>
                    <div class="summary-val" style="color: #64748b;"><?php echo $total_students - $active_students; ?></div>
                </div>
                <i data-lucide="user-check" style="color: #64748b; width: 32px; height: 32px;"></i>
            </div>
        </div>

        <!-- Student Data Table -->
        <div class="table-container">
            <?php if (empty($students)): ?>
                <div style="text-align: center; padding: 3rem; color: #64748b;">
                    <i data-lucide="user-x" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No active students found for the selected exam room.</p>
                </div>
            <?php else: ?>
                <table class="students-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Email</th>
                            <th>Exam Title</th>
                            <th>Room Code</th>
                            <th>Status Signal</th>
                            <th>Last Activity</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $s): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($s['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($s['student_email']); ?></td>
                                <td><?php echo htmlspecialchars($s['exam_title']); ?></td>
                                <td><span style="font-family: monospace; font-weight: 700; color: #2563eb;"><?php echo htmlspecialchars($s['room_code']); ?></span></td>
                                <td>
                                    <?php if ($s['is_live']): ?>
                                        <span class="signal-badge signal-active">
                                            <span class="dot"></span> Active
                                        </span>
                                    <?php else: ?>
                                        <span class="signal-badge signal-inactive">
                                            <span class="dot"></span> Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($s['last_activity'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

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