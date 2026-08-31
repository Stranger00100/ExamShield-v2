<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Student Authentication Gatekeeper
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$exam_id = (int)($_GET['exam_id'] ?? 0);

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// 1. Fetch Exam & Student State via MySQLi
$stmt = $conn->prepare("
    SELECT 
        e.id, e.title, e.subject, e.duration, e.total_marks,
        se.id AS student_exam_id, se.status AS attempt_status, 
        se.started_at, se.part_a_question_id, se.part_b_question_id,
        se.question_set_swaps, se.marks_deducted
    FROM exams e 
    INNER JOIN student_exams se ON e.id = se.exam_id 
    WHERE e.id = ? AND se.student_id = ? AND e.status = 'published'
    LIMIT 1
");
$stmt->bind_param("ii", $exam_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
$exam = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$exam || $exam['attempt_status'] === 'submitted') {
    header("Location: dashboard.php");
    exit();
}

$student_exam_id = (int)$exam['student_exam_id'];

// 2. Handle First Start: Lock in 1 Part A (Easy) & 1 Part B (Hard)
if ($exam['attempt_status'] === 'joined' || empty($exam['started_at'])) {
    // Select 1 question from Part A
    $stmt_a = $conn->prepare("SELECT id FROM questions WHERE exam_id = ? AND part = 'A' ORDER BY RAND() LIMIT 1");
    $stmt_a->bind_param("i", $exam_id);
    $stmt_a->execute();
    $res_a = $stmt_a->get_result();
    $part_a = $res_a ? $res_a->fetch_assoc() : null;
    $stmt_a->close();
    $part_a_id = $part_a['id'] ?? null;

    // Select 1 question from Part B
    $stmt_b = $conn->prepare("SELECT id FROM questions WHERE exam_id = ? AND part = 'B' ORDER BY RAND() LIMIT 1");
    $stmt_b->bind_param("i", $exam_id);
    $stmt_b->execute();
    $res_b = $stmt_b->get_result();
    $part_b = $res_b ? $res_b->fetch_assoc() : null;
    $stmt_b->close();
    $part_b_id = $part_b['id'] ?? null;

    $now = date('Y-m-d H:i:s');
    $stmt_init = $conn->prepare("
        UPDATE student_exams 
        SET status = 'started', started_at = ?, part_a_question_id = ?, part_b_question_id = ? 
        WHERE id = ?
    ");
    $stmt_init->bind_param("siii", $now, $part_a_id, $part_b_id, $student_exam_id);
    $stmt_init->execute();
    $stmt_init->close();

    $started_at = strtotime($now);
    $exam['part_a_question_id'] = $part_a_id;
    $exam['part_b_question_id'] = $part_b_id;
} else {
    $started_at = strtotime($exam['started_at']);
}

// 3. Calculate Server-Side Remaining Time
$duration_seconds = ((int)$exam['duration']) * 60;
$elapsed_seconds = time() - $started_at;
$remaining_seconds = max(0, $duration_seconds - $elapsed_seconds);

if ($remaining_seconds <= 0) {
    header("Location: submit_exam.php?auto=1&exam_id=" . $exam_id);
    exit();
}

// 4. Fetch the Specific Assigned Questions
$q_a_id = (int)$exam['part_a_question_id'];
$q_b_id = (int)$exam['part_b_question_id'];

$part_a_q = null;
$part_b_q = null;

if ($q_a_id > 0) {
    $stmt_qa = $conn->prepare("SELECT * FROM questions WHERE id = ? LIMIT 1");
    $stmt_qa->bind_param("i", $q_a_id);
    $stmt_qa->execute();
    $res_qa = $stmt_qa->get_result();
    $part_a_q = $res_qa ? $res_qa->fetch_assoc() : null;
    $stmt_qa->close();
}

if ($q_b_id > 0) {
    $stmt_qb = $conn->prepare("SELECT * FROM questions WHERE id = ? LIMIT 1");
    $stmt_qb->bind_param("i", $q_b_id);
    $stmt_qb->execute();
    $res_qb = $stmt_qb->get_result();
    $part_b_q = $res_qb ? $res_qb->fetch_assoc() : null;
    $stmt_qb->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Window - <?= htmlspecialchars($exam['title']) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/student/student_exam.css">
</head>
<body class="exam-screen-body">

    <!-- Top Bar -->
    <header class="exam-navbar">
        <div class="exam-brand">
            <div class="brand-logo"><i class="fa-solid fa-shield-halved"></i></div>
            <div>
                <h3><?= htmlspecialchars($exam['title']) ?></h3>
            </div>
        </div>

        <div class="exam-timer-box">
            <i class="fa-solid fa-stopwatch"></i>
            <span id="timerCountdown">--:--</span>
        </div>

        <div class="exam-actions-top">
            <button class="btn-swap-set" id="btnSwapQuestionSet" title="Penalty deduction applies">
                <i class="fa-solid fa-arrows-rotate"></i> Change Question Set
            </button>
            <button class="btn-exam-finish" id="btnFinishExam">Submit Exam</button>
        </div>
    </header>

    <!-- Split Screen Content -->
    <div class="exam-split-layout">
        <!-- Question Pane -->
        <div class="exam-question-pane">
            <div class="question-nav-tabs">
                <button class="part-tab-btn active" data-target="panePartA">
                    <i class="fa-solid fa-code"></i> Part A (Easy)
                </button>
                <button class="part-tab-btn" data-target="panePartB">
                    <i class="fa-solid fa-laptop-code"></i> Part B (Hard)
                </button>
            </div>

            <div class="question-scroll-content">
                <!-- Part A Content -->
                <div id="panePartA" class="question-display-area">
                    <div class="question-header-info">
                        <h4><?= htmlspecialchars($part_a_q['title'] ?? 'Part A Question') ?></h4>
                        <span class="badge-difficulty"><?= htmlspecialchars($part_a_q['difficulty'] ?? 'Easy') ?></span>
                    </div>
                    <div class="question-body-text">
                        <?= nl2br(htmlspecialchars($part_a_q['description'] ?? 'No description provided.')) ?>
                    </div>
                    <?php if (!empty($part_a_q['sample_input'])): ?>
                        <div class="code-io-box">
                            <label>Sample Input</label>
                            <pre><?= htmlspecialchars($part_a_q['sample_input']) ?></pre>
                        </div>
                        <div class="code-io-box">
                            <label>Sample Output</label>
                            <pre><?= htmlspecialchars($part_a_q['sample_output']) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Part B Content -->
                <div id="panePartB" class="question-display-area" style="display: none;">
                    <div class="question-header-info">
                        <h4><?= htmlspecialchars($part_b_q['title'] ?? 'Part B Question') ?></h4>
                        <span class="badge-difficulty"><?= htmlspecialchars($part_b_q['difficulty'] ?? 'Hard') ?></span>
                    </div>
                    <div class="question-body-text">
                        <?= nl2br(htmlspecialchars($part_b_q['description'] ?? 'No description provided.')) ?>
                    </div>
                    <?php if (!empty($part_b_q['sample_input'])): ?>
                        <div class="code-io-box">
                            <label>Sample Input</label>
                            <pre><?= htmlspecialchars($part_b_q['sample_input']) ?></pre>
                        </div>
                        <div class="code-io-box">
                            <label>Sample Output</label>
                            <pre><?= htmlspecialchars($part_b_q['sample_output']) ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Monaco Editor + Integrated Terminal Pane -->
        <div class="exam-editor-pane">
            <div class="editor-toolbar">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <span class="editor-lang-badge">
                        <i class="fa-solid fa-code"></i> Language:
                    </span>
                    <select id="languageSelector" style="background: #1e293b; color: #fff; border: 1px solid #334155; border-radius: 6px; padding: 4px 8px; font-size: 0.82rem; outline: none; cursor: pointer;">
                        <option value="python">Python</option>
                        <option value="c" selected>C (GCC)</option>
                        <option value="cpp">C++ (G++)</option>
                    </select>
                </div>
                <button class="btn-run-code" id="btnRunCode">
                    <i class="fa-solid fa-play"></i> Run Code
                </button>
            </div>
            
            <div id="monacoEditorWorkspace" class="editor-container-slot"></div>

            <!-- Integrated Terminal Output Drawer -->
            <div class="terminal-drawer">
                <div class="terminal-tabs">
                    <button class="terminal-tab-btn active" data-tab="tabOutput">
                        <i class="fa-solid fa-terminal"></i> Output / Console
                    </button>
                    <button class="terminal-tab-btn" data-tab="tabInput">
                        <i class="fa-solid fa-keyboard"></i> Custom Input (stdin)
                    </button>
                    <span id="execStats" style="margin-left: auto; font-size: 0.75rem; color: #64748b; align-self: center;"></span>
                </div>
                
                <div class="terminal-content-area">
                    <div id="tabOutput" class="terminal-pane">
                        <pre class="terminal-output" id="terminalOutputText">Click "Run Code" to compile and view program output...</pre>
                    </div>
                    <div id="tabInput" class="terminal-pane" style="display: none; height: 100%;">
                        <textarea class="custom-input-textarea" id="customInputText" placeholder="Enter custom arguments or inputs for stdin here..."></textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monaco Editor Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.38.0/min/vs/loader.min.js"></script>
    <script>
        // 1. Timer Logic
        let remainingSeconds = <?= $remaining_seconds ?>;
        const timerElem = document.getElementById('timerCountdown');

        function formatTimer() {
            if (remainingSeconds <= 0) {
                window.location.href = 'submit_exam.php?auto=1&exam_id=<?= $exam_id ?>';
                return;
            }
            const mins = Math.floor(remainingSeconds / 60);
            const secs = remainingSeconds % 60;
            timerElem.innerText = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
            remainingSeconds--;
        }
        setInterval(formatTimer, 1000);
        formatTimer();

 // 2. Templates & Editor Buffers
        const templates = {
            'c': "#include <stdio.h>\n\nint main() {\n    printf(\"Hello World\\n\");\n    return 0;\n}",
            'cpp': "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"Hello World\" << endl;\n    return 0;\n}",
            'python': "# Write your solution below\ndef main():\n    print(\"Hello World\")\n\nif __name__ == '__main__':\n    main()"
        };

        const langSelector = document.getElementById('languageSelector');
        let currentLang = langSelector ? langSelector.value : 'c';

        let codeBuffers = {
            partA: { c: templates.c, cpp: templates.cpp, python: templates.python },
            partB: { c: templates.c, cpp: templates.cpp, python: templates.python }
        };
        let activePart = 'partA';
        let editorInstance = null;
        
require.config({ paths: { 'vs': 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.38.0/min/vs' } });
require(['vs/editor/editor.main'], function() {
    editorInstance = monaco.editor.create(document.getElementById('monacoEditorWorkspace'), {
        value: codeBuffers[activePart][currentLang],
        language: currentLang,
        theme: 'vs-dark',
        automaticLayout: true,
        fontSize: 14,
        minimap: { enabled: false },
        contextmenu: false // Disables right-click context menu in Monaco
    });

    // Block Monaco Copy (Ctrl+C / Cmd+C)
    editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyC, function() {
        // Suppress copying from editor
    });

    // Block Monaco Paste (Ctrl+V / Cmd+V) and log proctoring event
    editorInstance.addCommand(monaco.KeyMod.CtrlCmd | monaco.KeyCode.KeyV, function() {
        if (typeof STUDENT_EXAM_ID !== 'undefined' && STUDENT_EXAM_ID) {
            fetch('../api/activity_log.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    student_exam_id: STUDENT_EXAM_ID,
                    event_type: 'paste_attempt',
                    details: 'Intercepted Monaco keyboard paste attempt.'
                })
            }).catch(() => {});
        }
    });
});

      // 3. Dynamic Language Switcher
        if (langSelector) {
            langSelector.addEventListener('change', (e) => {
                if (editorInstance) {
                    // 1. Save current editor state to the active buffer
                    codeBuffers[activePart][currentLang] = editorInstance.getValue();
                    
                    // 2. Switch language and restore corresponding buffer
                    currentLang = e.target.value;
                    monaco.editor.setModelLanguage(editorInstance.getModel(), currentLang === 'cpp' ? 'cpp' : currentLang);
                    editorInstance.setValue(codeBuffers[activePart][currentLang]);
                }
            });
        }

        // 4. Tab Switching between Part A and Part B
        document.querySelectorAll('.part-tab-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const targetBtn = e.currentTarget;
                const targetPane = targetBtn.getAttribute('data-target');
                const nextPart = (targetPane === 'panePartA') ? 'partA' : 'partB';

                if (activePart === nextPart) return;

                // 1. Save outgoing part code from editor
                if (editorInstance) {
                    codeBuffers[activePart][currentLang] = editorInstance.getValue();
                }

                // 2. Switch question DOM panes
                document.querySelectorAll('.part-tab-btn').forEach(b => b.classList.remove('active'));
                targetBtn.classList.add('active');

                document.querySelectorAll('.question-display-area').forEach(pane => pane.style.display = 'none');
                const targetElement = document.getElementById(targetPane);
                if (targetElement) targetElement.style.display = 'block';

                // 3. Switch active part and set incoming buffer
                activePart = nextPart;
                if (editorInstance) {
                    editorInstance.setValue(codeBuffers[activePart][currentLang]);
                }
            });
        });

        // 5. Terminal Tabs Switcher
        document.querySelectorAll('.terminal-tab-btn').forEach(tab => {
            tab.addEventListener('click', (e) => {
                document.querySelectorAll('.terminal-tab-btn').forEach(t => t.classList.remove('active'));
                e.currentTarget.classList.add('active');
                const target = e.currentTarget.getAttribute('data-tab');
                document.getElementById('tabOutput').style.display = (target === 'tabOutput') ? 'block' : 'none';
                document.getElementById('tabInput').style.display = (target === 'tabInput') ? 'block' : 'none';
            });
        });

        // 6. Run Code AJAX Handler
        document.getElementById('btnRunCode').addEventListener('click', () => {
            const runBtn = document.getElementById('btnRunCode');
            const outElem = document.getElementById('terminalOutputText');
            const statsElem = document.getElementById('execStats');
            const customIn = document.getElementById('customInputText').value;

            // Switch to Output Tab
            document.querySelector('[data-tab="tabOutput"]').click();
            
            runBtn.disabled = true;
            runBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Running...';
            outElem.className = 'terminal-output';
            outElem.innerText = 'Compiling and executing code...';

            fetch('../api/run_code.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    code: editorInstance.getValue(),
                    language: currentLang,
                    custom_input: customIn
                })
            })
            .then(res => res.json())
            .then(data => {
                runBtn.disabled = false;
                runBtn.innerHTML = '<i class="fa-solid fa-play"></i> Run Code';
                
                if (data.error) {
                    outElem.className = 'terminal-output error';
                    outElem.innerText = data.error;
                    statsElem.innerText = `Finished with error (${data.execution_time})`;
                } else {
                    outElem.className = 'terminal-output';
                    outElem.innerText = data.output || '[Program finished with no output]';
                    statsElem.innerText = `Execution Time: ${data.execution_time}`;
                }
            })
            .catch(() => {
                runBtn.disabled = false;
                runBtn.innerHTML = '<i class="fa-solid fa-play"></i> Run Code';
                outElem.className = 'terminal-output error';
                outElem.innerText = 'Connection error: Unable to reach compiler server.';
            });
        });

        // 7. Final Submission Handler
document.getElementById('btnFinishExam').addEventListener('click', () => {
    if (confirm("Are you sure you want to finalize and submit your examination?")) {
        if (editorInstance) {
            codeBuffers[activePart][currentLang] = editorInstance.getValue();
        }

        fetch('submit_exam.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                exam_id: <?= $exam_id ?>,
                part_a_code: codeBuffers.partA[currentLang],
                part_b_code: codeBuffers.partB[currentLang],
                language: currentLang
            })
        })
        .then(res => res.json())
        .then(() => {
            window.location.href = 'results.php';
        })
        .catch(() => {
            window.location.href = 'results.php';
        });
    }
    
});
// Question Set Swap Handler with Penalty Warning & Server Restriction
document.getElementById('btnSwapQuestionSet').addEventListener('click', () => {
    const confirmSwap = confirm(
        "⚠️ WARNING: CHANGING YOUR QUESTION SET\n\n" +
        "• Both Part A AND Part B will be changed together.\n" +
        "• A penalty of 5 marks will be deducted from your final score.\n" +
        "• You can only change your question set ONCE.\n\n" +
        "Are you sure you want to proceed?"
    );

    if (!confirmSwap) return;

    const swapBtn = document.getElementById('btnSwapQuestionSet');
    swapBtn.disabled = true;
    swapBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Changing...';

    fetch('../api/swap_question_set.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ exam_id: <?= $exam_id ?> })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(`Question set swapped successfully. A penalty of ${data.penalty_applied} marks was applied.`);
            // Reload window to bind newly randomized questions securely from server
            window.location.reload();
        } else {
            alert(data.error || "Unable to change question set.");
            swapBtn.disabled = false;
            swapBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Change Question Set';
        }
    })
    .catch(() => {
        alert("Connection error: Unable to contact server.");
      swapBtn.disabled = false;
            swapBtn.innerHTML = '<i class="fa-solid fa-arrows-rotate"></i> Change Question Set';
        });
    });
</script>

    <!-- Pass Attempt ID to AntiCheat Proctoring Script -->
    <script>
        const STUDENT_EXAM_ID = <?= (int)$student_exam_id ?>;
    </script>
    <script src="../assets/js/antiCheat.js"></script>
</body>
</html>