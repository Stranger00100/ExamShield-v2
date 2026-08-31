<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

$student_exam_id = (int)($_GET['student_exam_id'] ?? 0);
$success_msg = '';

if ($student_exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// 1. One-Click File Download Handler
if (isset($_GET['download']) && isset($_GET['sub_id'])) {
    $sub_id = (int)$_GET['sub_id'];
    $stmt_dl = $conn->prepare("
        SELECT ss.submitted_code, ss.language, u.username, q.part 
        FROM student_submissions ss 
        INNER JOIN student_exams se ON ss.student_exam_id = se.id 
        INNER JOIN users u ON se.student_id = u.id 
        LEFT JOIN questions q ON ss.question_id = q.id 
        WHERE ss.id = ? LIMIT 1
    ");
    $stmt_dl->bind_param("i", $sub_id);
    $stmt_dl->execute();
    $dl_res = $stmt_dl->get_result()->fetch_assoc();
    $stmt_dl->close();

    if ($dl_res) {
        $ext = ($dl_res['language'] === 'python') ? 'py' : (($dl_res['language'] === 'cpp') ? 'cpp' : 'c');
        $filename = "Submission_{$dl_res['username']}_Part" . ($dl_res['part'] ?? 'A') . ".{$ext}";
        header('Content-Type: text/plain');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dl_res['submitted_code'];
        exit();
    }
}

// 2. Manual Score Adjustment Handler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_score') {
    $sub_id = (int)($_POST['submission_id'] ?? 0);
    $new_score = (float)($_POST['score'] ?? 0.0);

    if ($sub_id > 0) {
        $stmt_upd = $conn->prepare("UPDATE student_submissions SET score = ? WHERE id = ? AND student_exam_id = ?");
        $stmt_upd->bind_param("dii", $new_score, $sub_id, $student_exam_id);
        $stmt_upd->execute();
        $stmt_upd->close();

        // Recalculate Final Exam Score
        $stmt_recalc = $conn->prepare("
            UPDATE student_exams se
            SET final_score = (
                SELECT GREATEST(0.00, COALESCE(SUM(score), 0.00) - se.marks_deducted)
                FROM student_submissions 
                WHERE student_exam_id = se.id
            )
            WHERE se.id = ?
        ");
        $stmt_recalc->bind_param("i", $student_exam_id);
        $stmt_recalc->execute();
        $stmt_recalc->close();

        $success_msg = "Marks updated and final score recalculated successfully!";
    }
}

// 3. Fetch Metadata
$stmt = $conn->prepare("
    SELECT 
        se.id AS student_exam_id, se.exam_id, se.status, se.final_score, se.marks_deducted, se.submitted_at,
        u.full_name AS student_name, u.email AS student_email,
        e.title AS exam_title, e.subject AS exam_subject, e.total_marks
    FROM student_exams se
    INNER JOIN users u ON se.student_id = u.id
    INNER JOIN exams e ON se.exam_id = e.id
    WHERE se.id = ? LIMIT 1
");
$stmt->bind_param("i", $student_exam_id);
$stmt->execute();
$res = $stmt->get_result();
$attempt = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$attempt) {
    echo "Submission record not found.";
    exit();
}

// 4. Fetch Submissions
$stmt_code = $conn->prepare("
    SELECT 
        ss.id AS sub_id, ss.submitted_code, ss.language, ss.score,
        q.part, q.title AS question_title, q.description, q.marks AS question_marks
    FROM student_submissions ss
    LEFT JOIN questions q ON ss.question_id = q.id
    WHERE ss.student_exam_id = ?
    ORDER BY ss.id ASC
");
$stmt_code->bind_param("i", $student_exam_id);
$stmt_code->execute();
$res_code = $stmt_code->get_result();
$submissions = $res_code ? $res_code->fetch_all(MYSQLI_ASSOC) : [];
$stmt_code->close();

// 5. Fetch Proctoring Telemetry
$stmt_logs = $conn->prepare("
    SELECT event_type, event_details, logged_at 
    FROM exam_activity_logs 
    WHERE student_exam_id = ? 
    ORDER BY logged_at ASC
");
$stmt_logs->bind_param("i", $student_exam_id);
$stmt_logs->execute();
$res_logs = $stmt_logs->get_result();
$activity_logs = $res_logs ? $res_logs->fetch_all(MYSQLI_ASSOC) : [];
$stmt_logs->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Code - <?= htmlspecialchars($attempt['student_name'] ?: $attempt['student_email']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/themes/prism-tomorrow.min.css">
    <style>
        :root { 
            --primary-blue: #2563eb; 
            --sidebar-bg: #0f172a; 
            --body-bg: #f8fafc; 
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
        .sidebar-nav a { display: flex; align-items: center; gap: 12px; padding: 12px 16px; color: #94a3b8; text-decoration: none; font-weight: 500; border-radius: 10px; }
        .sidebar-nav li.active a { background: var(--primary-blue); color: #fff; }
        .sidebar-logout { margin-top: auto; }
        .sidebar-logout a { display: flex; align-items: center; justify-content: center; gap: 8px; background: #ef4444; color: #fff; padding: 12px; border-radius: 10px; text-decoration: none; font-weight: 600; }
        .main-wrapper { flex-grow: 1; display: flex; flex-direction: column; }
        .top-navbar { background: #fff; padding: 16px 32px; border-bottom: 1px solid var(--border-color); display: flex; justify-content: space-between; align-items: center; }
        .page-container { padding: 32px; max-width: 1200px; margin: 0 auto; width: 100%; }
        .meta-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; padding: 20px 24px; margin-bottom: 24px; display: flex; justify-content: space-between; align-items: center; }
        .submission-card { background: #fff; border: 1px solid var(--border-color); border-radius: 14px; padding: 24px; margin-bottom: 24px; }
        .code-container { position: relative; border-radius: 10px; overflow: hidden; margin-top: 14px; }
        .code-toolbar { display: flex; justify-content: space-between; align-items: center; background: #2d2d2d; padding: 8px 16px; color: #94a3b8; font-size: 0.8rem; font-weight: 600; border-radius: 10px 10px 0 0; }
        pre[class*="language-"] { margin: 0 !important; border-radius: 0 0 10px 10px !important; }
        .btn-toolbar { background: #475569; color: #fff; border: none; padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; transition: 0.2s; }
        .btn-toolbar:hover { background: #64748b; }
        .log-badge { background: #fef2f2; border: 1px solid #fee2e2; color: #991b1b; padding: 12px 18px; border-radius: 10px; margin-bottom: 24px; font-size: 0.85rem; }
        .test-console { background: #18181b; border-radius: 8px; margin-top: 14px; padding: 14px; color: #fff; font-family: monospace; font-size: 0.85rem; }
        .test-console textarea { width: 100%; background: #27272a; border: 1px solid #3f3f46; color: #e4e4e7; padding: 8px; border-radius: 6px; font-family: inherit; font-size: 0.85rem; margin-top: 6px; resize: vertical; }
        .test-output { background: #09090b; border: 1px solid #27272a; border-radius: 6px; padding: 10px; margin-top: 8px; color: #22c55e; white-space: pre-wrap; min-height: 40px; }
        .score-form { display: flex; align-items: center; gap: 8px; margin-top: 16px; }
        .score-input { width: 80px; padding: 6px 10px; border: 1px solid var(--border-color); border-radius: 6px; font-weight: 700; text-align: center; }
        .btn-save-score { background: var(--primary-blue); color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; font-size: 0.8rem; cursor: pointer; }
        .btn-outline { border: 1px solid var(--border-color); color: var(--text-dark); padding: 8px 16px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 0.85rem; }
    </style>
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-brand">
            <i class="fa-solid fa-shield-halved"></i>
            <div><h3>Exam Shield</h3><small style="color: #94a3b8; font-size: 0.75rem;">Teacher Portal</small></div>
        </div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php"><i class="fa-solid fa-shapes"></i> Dashboard</a></li>
            <li class="active"><a href="manage_exams.php"><i class="fa-solid fa-file-circle-plus"></i> Exams</a></li>
            <li><a href="reports.php"><i class="fa-solid fa-chart-pie"></i> Reports</a></li>
            <li><a href="profile.php"><i class="fa-solid fa-user"></i> Profile</a></li>
        </ul>
        <div class="sidebar-logout"><a href="../logout.php"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a></div>
    </aside>

    <div class="main-wrapper">
        <header class="top-navbar">
            <h2>Candidate Evaluation Suite</h2>
            <a href="submissions.php?exam_id=<?= (int)$attempt['exam_id'] ?>" class="btn-outline"><i class="fa-solid fa-arrow-left"></i> Back to Submissions</a>
        </header>

        <main class="page-container">
            <?php if (!empty($success_msg)): ?>
                <div style="background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-weight: 600;">
                    <i class="fa-solid fa-circle-check"></i> <?= htmlspecialchars($success_msg) ?>
                </div>
            <?php endif; ?>

            <div class="meta-card">
                <div>
                    <h3 style="font-size: 1.25rem;"><?= htmlspecialchars($attempt['student_name'] ?: $attempt['student_email']) ?></h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem;"><?= htmlspecialchars($attempt['student_email']) ?> &bull; <?= htmlspecialchars($attempt['exam_title']) ?> (<?= htmlspecialchars($attempt['exam_subject']) ?>)</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1.35rem; font-weight: 700; color: #2563eb;">
                        Total Score: <?= $attempt['final_score'] !== null ? htmlspecialchars($attempt['final_score']) : '0.00' ?> / <?= (int)$attempt['total_marks'] ?>
                    </div>
                    <?php if ((int)$attempt['marks_deducted'] > 0): ?>
                        <small style="color: #ef4444; font-weight: 600;">Penalty Applied: -<?= (int)$attempt['marks_deducted'] ?> marks</small>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Proctoring Summary -->
            <?php if (!empty($activity_logs)): ?>
                <div class="log-badge">
                    <div style="font-weight: 700; margin-bottom: 6px;"><i class="fa-solid fa-triangle-exclamation"></i> Proctoring Flags Detected (<?= count($activity_logs) ?>)</div>
                    <ul style="margin-left: 20px; font-size: 0.8rem; line-height: 1.5;">
                        <?php foreach (array_slice($activity_logs, 0, 5) as $log): ?>
                            <li>[<?= date('h:i:s A', strtotime($log['logged_at'])) ?>] <strong><?= htmlspecialchars($log['event_type']) ?></strong>: <?= htmlspecialchars($log['event_details']) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if (empty($submissions)): ?>
                <div class="submission-card" style="text-align: center; color: var(--text-muted); padding: 40px;">
                    No code submissions were recorded for this attempt.
                </div>
            <?php else: ?>
                <?php foreach ($submissions as $index => $sub): 
                    $lang_code = strtolower($sub['language'] ?? 'c');
                    $prism_lang = ($lang_code === 'python') ? 'python' : 'clike';
                ?>
                    <div class="submission-card">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h4><?= !empty($sub['part']) ? 'Part ' . htmlspecialchars($sub['part']) : ($index === 0 ? 'Part A Solution' : 'Part B Solution') ?>: <?= htmlspecialchars($sub['question_title'] ?? 'Task') ?></h4>
                            <span style="font-weight: 700; color: #059669; font-size: 0.9rem;">
                                Current Marks: <?= htmlspecialchars($sub['score']) ?> <?= !empty($sub['question_marks']) ? '/ ' . htmlspecialchars($sub['question_marks']) : '' ?>
                            </span>
                        </div>

                        <?php if (!empty($sub['description'])): ?>
                            <p style="color: var(--text-muted); font-size: 0.88rem; margin: 8px 0;"><?= nl2br(htmlspecialchars($sub['description'])) ?></p>
                        <?php endif; ?>

                        <!-- Code Viewer -->
                        <div class="code-container">
                            <div class="code-toolbar">
                                <span><i class="fa-solid fa-code"></i> LANGUAGE: <?= strtoupper(htmlspecialchars($sub['language'] ?? 'C')) ?></span>
                                <div style="display: flex; gap: 8px;">
                                    <button class="btn-toolbar" onclick="copyCode('code-block-<?= $index ?>', this)"><i class="fa-regular fa-copy"></i> Copy</button>
                                    <a href="view_submission.php?student_exam_id=<?= $student_exam_id ?>&download=1&sub_id=<?= (int)$sub['sub_id'] ?>" class="btn-toolbar"><i class="fa-solid fa-download"></i> Export .<?= ($lang_code === 'python') ? 'py' : 'c' ?></a>
                                </div>
                            </div>
                            <pre><code id="code-block-<?= $index ?>" class="language-<?= $prism_lang ?>"><?= htmlspecialchars($sub['submitted_code']) ?></code></pre>
                        </div>

                        <!-- Live Interactive Testing Console -->
                        <div class="test-console">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <span><i class="fa-solid fa-terminal"></i> Test Execution Console</span>
                                <button type="button" class="btn-toolbar" style="background:#0284c7;" onclick="runCandidateCode(<?= $index ?>, '<?= htmlspecialchars($lang_code) ?>', this)"><i class="fa-solid fa-play"></i> Run Code</button>
                            </div>
                            <textarea id="stdin-<?= $index ?>" rows="2" placeholder="Custom Input (stdin) for testing..."></textarea>
                            <div class="test-output" id="stdout-<?= $index ?>">Console output will appear here...</div>
                        </div>

                        <!-- Manual Score Override Form -->
                        <form method="POST" class="score-form">
                            <input type="hidden" name="action" value="update_score">
                            <input type="hidden" name="submission_id" value="<?= (int)$sub['sub_id'] ?>">
                            <label style="font-size: 0.85rem; font-weight: 600; color: #475569;">Adjust Score:</label>
                            <input type="number" step="0.5" name="score" value="<?= htmlspecialchars($sub['score']) ?>" max="<?= (float)($sub['question_marks'] ?? 100) ?>" min="0" class="score-input" required>
                            <button type="submit" class="btn-save-score"><i class="fa-solid fa-floppy-disk"></i> Save Score</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </main>
    </div>
    <!-- Prism Syntax Highlighting Dependencies -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/prism.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-c.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-cpp.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/prism/1.29.0/components/prism-python.min.js"></script>

    <script>
    function copyCode(elemId, btn) {
        const text = document.getElementById(elemId).innerText;
        navigator.clipboard.writeText(text).then(() => {
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fa-solid fa-check"></i> Copied!';
            setTimeout(() => { btn.innerHTML = originalText; }, 2000);
        });
    }

    function runCandidateCode(index, language, btn) {
        const code = document.getElementById('code-block-' + index).innerText;
        const customInput = document.getElementById('stdin-' + index).value;
        const outputElem = document.getElementById('stdout-' + index);

        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Executing...';
        outputElem.innerText = 'Compiling and executing code on server...';

        fetch('../api/run_code.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                code: code,
                language: language,
                custom_input: customInput
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play"></i> Run Code';
            if (data.error) {
                outputElem.style.color = '#ef4444';
                outputElem.innerText = data.error;
            } else {
                outputElem.style.color = '#22c55e';
                outputElem.innerText = data.output || '[Program executed with no output]';
            }
        })
        .catch(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-play"></i> Run Code';
            outputElem.style.color = '#ef4444';
            outputElem.innerText = 'Server error: Execution failed.';
        });
    }
    </script>
</body>
</html>