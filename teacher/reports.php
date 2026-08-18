<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

$teacher_id = $_SESSION['user_id'];
$selected_exam_id = isset($_GET['exam_id']) ? intval($_GET['exam_id']) : 0;

// Fetch all hosted/conducted exams for the dropdown filter
$stmt_exams = $conn->prepare("SELECT id, title, room_code, total_marks FROM exams WHERE created_by = ? ORDER BY id DESC");
$stmt_exams->bind_param("i", $teacher_id);
$stmt_exams->execute();
$hosted_exams = $stmt_exams->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_exams->close();

// Query student submission scores
$sql = "SELECT 
            s.id AS student_id,
            s.full_name AS student_name,
            s.university_number,
            e.title AS exam_title,
            e.room_code,
            e.total_marks AS max_marks,
            COALESCE(sub.marks_obtained, 0) AS score,
            COALESCE(sub.status, 'pending') AS submission_status,
            sub.submitted_at
        FROM users s
        CROSS JOIN exams e
        LEFT JOIN submissions sub ON sub.student_id = s.id AND sub.exam_id = e.id
        WHERE e.created_by = ? AND s.role = 'student'";

if ($selected_exam_id > 0) {
    $sql .= " AND e.id = ?";
}

$sql .= " ORDER BY sub.marks_obtained DESC";

$stmt_reports = $conn->prepare($sql);
if ($selected_exam_id > 0) {
    $stmt_reports->bind_param("ii", $teacher_id, $selected_exam_id);
} else {
    $stmt_reports->bind_param("i", $teacher_id);
}

$stmt_reports->execute();
$reports = $stmt_reports->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_reports->close();

// Metrics calculations
$total_submissions = 0;
$total_score_sum = 0;
$highest_score = 0;
$passed_count = 0;

foreach ($reports as $r) {
    if ($r['submission_status'] === 'submitted' || $r['submission_status'] === 'graded') {
        $total_submissions++;
        $total_score_sum += $r['score'];
        if ($r['score'] > $highest_score) {
            $highest_score = $r['score'];
        }
        // Pass percentage >= 40%
        if ($r['max_marks'] > 0 && ($r['score'] / $r['max_marks']) >= 0.40) {
            $passed_count++;
        }
    }
}

$avg_score = $total_submissions > 0 ? round($total_score_sum / $total_submissions, 1) : 0;
$pass_rate = $total_submissions > 0 ? round(($passed_count / $total_submissions) * 100, 1) : 0;

$activePage = "reports";
$pageTitle = "Reports & Analytics";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Reports & Analytics - Exam Shield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <link rel="stylesheet" href="../assets/css/reports.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-content fade-in">

        <div class="reports-header">
            <div>
                <h2>Exam Reports & Performance</h2>
                <p>Analyze student scores, class averages, and detailed exam results.</p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="filter-card">
            <form method="GET" action="reports.php" style="display: flex; align-items: center; gap: 12px; margin: 0;">
                <div class="filter-group">
                    <label for="exam_id">Select Exam Room:</label>
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
            <button onclick="window.print()" class="btn-secondary" style="display: inline-flex; align-items: center; gap: 6px;">
                <i data-lucide="printer" style="width: 16px; height: 16px;"></i> Print Report
            </button>
        </div>

        <!-- Metrics Grid -->
        <div class="metrics-grid">
            <div class="metric-card">
                <div class="metric-icon icon-blue"><i data-lucide="file-check"></i></div>
                <div class="metric-info">
                    <h4><?php echo $total_submissions; ?></h4>
                    <p>Submissions</p>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon icon-purple"><i data-lucide="bar-chart-2"></i></div>
                <div class="metric-info">
                    <h4><?php echo $avg_score; ?></h4>
                    <p>Average Score</p>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon icon-green"><i data-lucide="trophy"></i></div>
                <div class="metric-info">
                    <h4><?php echo $highest_score; ?></h4>
                    <p>Top Score</p>
                </div>
            </div>
            <div class="metric-card">
                <div class="metric-icon icon-amber"><i data-lucide="percent"></i></div>
                <div class="metric-info">
                    <h4><?php echo $pass_rate; ?>%</h4>
                    <p>Pass Rate</p>
                </div>
            </div>
        </div>

        <!-- Performance Data Table -->
        <div class="table-container">
            <?php if (empty($reports)): ?>
                <div style="text-align: center; padding: 3rem; color: #64748b;">
                    <i data-lucide="bar-chart" style="width: 48px; height: 48px; margin-bottom: 1rem; opacity: 0.5;"></i>
                    <p style="margin: 0;">No submission records found for this selection.</p>
                </div>
            <?php else: ?>
                <table class="reports-table">
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>University No</th>
                            <th>Exam Title</th>
                            <th>Room Code</th>
                            <th>Score</th>
                            <th>Result Status</th>
                            <th>Submitted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports as $rep): ?>
                            <?php 
                                $is_pass = ($rep['max_marks'] > 0) && (($rep['score'] / $rep['max_marks']) >= 0.40);
                                $has_submitted = ($rep['submission_status'] === 'submitted' || $rep['submission_status'] === 'graded');
                            ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($rep['student_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($rep['university_number'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($rep['exam_title']); ?></td>
                                <td><span style="font-family: monospace; font-weight: 700; color: #2563eb;"><?php echo htmlspecialchars($rep['room_code']); ?></span></td>
                                <td><strong><?php echo $rep['score']; ?></strong> / <?php echo $rep['max_marks']; ?></td>
                                <td>
                                    <?php if ($has_submitted): ?>
                                        <?php if ($is_pass): ?>
                                            <span class="grade-badge grade-pass">PASSED</span>
                                        <?php else: ?>
                                            <span class="grade-badge grade-fail">FAILED</span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="grade-badge" style="background: #f1f5f9; color: #64748b;">NOT TAKEN</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($rep['submitted_at'] ?? 'N/A'); ?></td>
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