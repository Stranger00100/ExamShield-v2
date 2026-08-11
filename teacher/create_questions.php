<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

// Validate Exam ID
$exam_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($exam_id <= 0) {
    header("Location: manage_exams.php");
    exit();
}

// Fetch Exam Details using MySQLi
$stmt = $conn->prepare("SELECT * FROM exams WHERE id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$result = $stmt->get_result();
$exam = $result->fetch_assoc();
$stmt->close();

if (!$exam) {
    header("Location: manage_exams.php");
    exit();
}

$error_message = '';
$success_message = '';

// Handle Question Deletion
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['question_id'])) {
    $del_q_id = intval($_GET['question_id']);
    
    // Fetch question to know its part before deleting
    $stmt_q = $conn->prepare("SELECT part FROM questions WHERE id = ? AND exam_id = ?");
    $stmt_q->bind_param("ii", $del_q_id, $exam_id);
    $stmt_q->execute();
    $target_q = $stmt_q->get_result()->fetch_assoc();
    $stmt_q->close();

    if ($target_q) {
        $deleted_part = $target_q['part'];

        // Delete question
        $del_stmt = $conn->prepare("DELETE FROM questions WHERE id = ? AND exam_id = ?");
        $del_stmt->bind_param("ii", $del_q_id, $exam_id);
        $del_stmt->execute();
        $del_stmt->close();

        // Re-number remaining questions sequentially in the same part
        $stmt_renumber = $conn->prepare("SELECT id FROM questions WHERE exam_id = ? AND part = ? ORDER BY question_number ASC, id ASC");
        $stmt_renumber->bind_param("is", $exam_id, $deleted_part);
        $stmt_renumber->execute();
        $remaining_result = $stmt_renumber->get_result();
        
        $new_q_num = 1;
        $stmt_update_num = $conn->prepare("UPDATE questions SET question_number = ? WHERE id = ?");
        while ($q_item = $remaining_result->fetch_assoc()) {
            $stmt_update_num->bind_param("ii", $new_q_num, $q_item['id']);
            $stmt_update_num->execute();
            $new_q_num++;
        }
        $stmt_update_num->close();
        $stmt_renumber->close();

        header("Location: create_questions.php?id=" . $exam_id . "&msg=deleted");
        exit();
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'deleted') {
    $success_message = "Question deleted and question numbers updated successfully.";
} elseif (isset($_GET['msg']) && $_GET['msg'] === 'saved') {
    $success_message = "Question saved successfully.";
} elseif (isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}

// Check Edit Mode
$edit_mode = false;
$edit_question = [
    'id' => '',
    'part' => 'A',
    'title' => '',
    'question_type' => 'Coding',
    'difficulty' => 'Easy',
    'language' => $exam['language'] ?? 'C',
    'question' => '',
    'description' => '',
    'constraints' => '',
    'input_format' => '',
    'output_format' => '',
    'sample_input' => '',
    'sample_output' => '',
    'marks' => $exam['part_a_marks']
];

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['question_id'])) {
    $edit_q_id = intval($_GET['question_id']);
    $stmt_edit = $conn->prepare("SELECT * FROM questions WHERE id = ? AND exam_id = ?");
    $stmt_edit->bind_param("ii", $edit_q_id, $exam_id);
    $stmt_edit->execute();
    $fetched_q = $stmt_edit->get_result()->fetch_assoc();
    $stmt_edit->close();

    if ($fetched_q) {
        $edit_mode = true;
        $edit_question = $fetched_q;
    }
}

// Load Questions for Pool (Part A)
$stmt_a = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? AND part = 'A' ORDER BY question_number ASC");
$stmt_a->bind_param("i", $exam_id);
$stmt_a->execute();
$part_a_questions = $stmt_a->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_a->close();

// Load Questions for Pool (Part B)
$stmt_b = $conn->prepare("SELECT * FROM questions WHERE exam_id = ? AND part = 'B' ORDER BY question_number ASC");
$stmt_b->bind_param("i", $exam_id);
$stmt_b->execute();
$part_b_questions = $stmt_b->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt_b->close();

$part_a_count = count($part_a_questions);
$part_b_count = count($part_b_questions);

// Variables for sidebar/header includes
$activePage = "manage_exam";
$pageTitle = "Create Questions";
?>
<!DOCTYPE html>
<html>
<head>
    <title>Create Questions - Exam Shield</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    <link rel="stylesheet" href="../assets/css/teacher.css">
    <link rel="stylesheet" href="../assets/css/create-questions.css">
</head>
<body>

<?php include 'includes/sidebar.php'; ?>

<div class="main-content">

    <?php include 'includes/header.php'; ?>

    <div class="dashboard-content fade-in">
        
        <!-- Action bar -->
        <div style="display: flex; justify-content: flex-end; margin-bottom: 20px;">
            <a href="manage_exams.php" class="btn-primary" style="background-color: #64748b; text-decoration: none;">← Back to Exams</a>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <!-- Exam Information Card -->
        <div class="card exam-info-card">
            <h2 class="card-title">Exam Details</h2>
            <div class="exam-info-grid">
                <div class="info-item">
                    <span class="info-label">Exam Title</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['title']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Subject</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['subject']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Semester</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['semester']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Language</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['language']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Duration</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['duration']); ?> mins</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Total Marks</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['total_marks']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Part A Marks / Qn</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['part_a_marks']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Part B Marks / Qn</span>
                    <span class="info-value"><?php echo htmlspecialchars($exam['part_b_marks']); ?></span>
                </div>
            </div>
        </div>

        <!-- Question Pool Status -->
        <div class="question-pool-status">
            <div class="pool-card">
                <div class="pool-header">
                    <h3>PART A</h3>
                    <span class="badge"><?php echo htmlspecialchars($exam['part_a_marks']); ?> Marks / Qn</span>
                </div>
                <div class="pool-count"><?php echo $part_a_count; ?> / 10 Questions</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo ($part_a_count / 10) * 100; ?>%;"></div>
                </div>
            </div>

            <div class="pool-card">
                <div class="pool-header">
                    <h3>PART B</h3>
                    <span class="badge"><?php echo htmlspecialchars($exam['part_b_marks']); ?> Marks / Qn</span>
                </div>
                <div class="pool-count"><?php echo $part_b_count; ?> / 10 Questions</div>
                <div class="progress-bar">
                    <div class="progress-fill" style="width: <?php echo ($part_b_count / 10) * 100; ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Question Builder Section -->
        <div class="card question-builder">
            <h2><?php echo $edit_mode ? 'Edit Question' : 'Add Question'; ?></h2>

            <form action="save_questions.php" method="POST" id="questionForm">
                <input type="hidden" name="exam_id" value="<?php echo $exam_id; ?>">
                <input type="hidden" name="question_id" value="<?php echo htmlspecialchars($edit_question['id']); ?>">

                <!-- Part Selection -->
                <div class="form-group">
                    <label>Select Exam Part</label>
                    <div class="part-selector">
                        <label class="part-option">
                            <input type="radio" name="part" value="A" <?php echo ($edit_question['part'] === 'A') ? 'checked' : ''; ?> onchange="updateDefaultMarks('A')">
                            <span class="part-btn">PART A</span>
                        </label>
                        <label class="part-option">
                            <input type="radio" name="part" value="B" <?php echo ($edit_question['part'] === 'B') ? 'checked' : ''; ?> onchange="updateDefaultMarks('B')">
                            <span class="part-btn">PART B</span>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-2">
                        <label for="title">Question Title *</label>
                        <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($edit_question['title']); ?>" placeholder="e.g. Fibonacci Series">
                    </div>
                    <div class="form-group">
                        <label for="question_type">Question Type</label>
                        <select id="question_type" name="question_type">
                            <option value="Coding" <?php echo ($edit_question['question_type'] === 'Coding') ? 'selected' : ''; ?>>Coding</option>
                            <option value="MCQ" <?php echo ($edit_question['question_type'] === 'MCQ') ? 'selected' : ''; ?>>MCQ</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="difficulty">Difficulty</label>
                        <select id="difficulty" name="difficulty">
                            <option value="Easy" <?php echo ($edit_question['difficulty'] === 'Easy') ? 'selected' : ''; ?>>Easy</option>
                            <option value="Medium" <?php echo ($edit_question['difficulty'] === 'Medium') ? 'selected' : ''; ?>>Medium</option>
                            <option value="Hard" <?php echo ($edit_question['difficulty'] === 'Hard') ? 'selected' : ''; ?>>Hard</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="language">Programming Language</label>
                        <select id="language" name="language">
                            <option value="C" <?php echo ($edit_question['language'] === 'C') ? 'selected' : ''; ?>>C</option>
                            <option value="C++" <?php echo ($edit_question['language'] === 'C++') ? 'selected' : ''; ?>>C++</option>
                            <option value="Java" <?php echo ($edit_question['language'] === 'Java') ? 'selected' : ''; ?>>Java</option>
                            <option value="Python" <?php echo ($edit_question['language'] === 'Python') ? 'selected' : ''; ?>>Python</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="marks">Marks *</label>
                        <input type="number" step="0.01" id="marks" name="marks" required value="<?php echo htmlspecialchars($edit_question['marks']); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="question">Question Statement *</label>
                    <textarea id="question" name="question" rows="4" required placeholder="Enter the main problem statement..."><?php echo htmlspecialchars($edit_question['question']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="description">Description / Notes (Optional)</label>
                    <textarea id="description" name="description" rows="2" placeholder="Additional background information..."><?php echo htmlspecialchars($edit_question['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="constraints">Constraints (Optional)</label>
                    <textarea id="constraints" name="constraints" rows="2" placeholder="e.g. 1 <= N <= 10^5"><?php echo htmlspecialchars($edit_question['constraints']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="input_format">Input Format</label>
                        <textarea id="input_format" name="input_format" rows="2" placeholder="Describe expected input structure..."><?php echo htmlspecialchars($edit_question['input_format']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="output_format">Output Format</label>
                        <textarea id="output_format" name="output_format" rows="2" placeholder="Describe expected output structure..."><?php echo htmlspecialchars($edit_question['output_format']); ?></textarea>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sample_input">Sample Input</label>
                        <textarea id="sample_input" name="sample_input" class="code-font" rows="3" placeholder="Sample input data..."><?php echo htmlspecialchars($edit_question['sample_input']); ?></textarea>
                    </div>
                    <div class="form-group">
                        <label for="sample_output">Sample Output</label>
                        <textarea id="sample_output" name="sample_output" class="code-font" rows="3" placeholder="Sample output data..."><?php echo htmlspecialchars($edit_question['sample_output']); ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <a href="create_questions.php?id=<?php echo $exam_id; ?>" class="btn-primary" style="background-color: #64748b; text-decoration: none;">Clear</a>
                    <button type="submit" class="btn-primary"><?php echo $edit_mode ? 'Update Question →' : 'Add Question →'; ?></button>
                </div>
            </form>
        </div>

        <!-- Question Pool List Section -->
        <div class="question-pool-container">
            <h2>Question Pool</h2>

            <!-- PART A QUESTIONS -->
            <div class="pool-section">
                <div class="pool-section-header">
                    <h3>PART A — <span><?php echo $part_a_count; ?> / 10</span></h3>
                </div>
                <?php if ($part_a_count === 0): ?>
                    <div class="empty-state">No questions added to Part A yet.</div>
                <?php else: ?>
                    <div class="questions-grid">
                        <?php foreach ($part_a_questions as $q): ?>
                            <div class="question-card">
                                <div class="question-header">
                                    <span class="q-number">Q<?php echo htmlspecialchars($q['question_number']); ?></span>
                                    <h4 class="q-title"><?php echo htmlspecialchars($q['title']); ?></h4>
                                </div>
                                <div class="question-meta">
                                    <span class="badge badge-diff"><?php echo htmlspecialchars($q['difficulty']); ?></span>
                                    <span class="badge badge-lang"><?php echo htmlspecialchars($q['language']); ?></span>
                                    <span class="badge badge-marks"><?php echo htmlspecialchars($q['marks']); ?> Marks</span>
                                </div>
                                <div class="question-actions">
                                    <a href="create_questions.php?id=<?php echo $exam_id; ?>&action=edit&question_id=<?php echo $q['id']; ?>" class="btn-sm edit-button">Edit</a>
                                    <a href="create_questions.php?id=<?php echo $exam_id; ?>&action=delete&question_id=<?php echo $q['id']; ?>" class="btn-sm delete-button" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- PART B QUESTIONS -->
            <div class="pool-section">
                <div class="pool-section-header">
                    <h3>PART B — <span><?php echo $part_b_count; ?> / 10</span></h3>
                </div>
                <?php if ($part_b_count === 0): ?>
                    <div class="empty-state">No questions added to Part B yet.</div>
                <?php else: ?>
                    <div class="questions-grid">
                        <?php foreach ($part_b_questions as $q): ?>
                            <div class="question-card">
                                <div class="question-header">
                                    <span class="q-number">Q<?php echo htmlspecialchars($q['question_number']); ?></span>
                                    <h4 class="q-title"><?php echo htmlspecialchars($q['title']); ?></h4>
                                </div>
                                <div class="question-meta">
                                    <span class="badge badge-diff"><?php echo htmlspecialchars($q['difficulty']); ?></span>
                                    <span class="badge badge-lang"><?php echo htmlspecialchars($q['language']); ?></span>
                                    <span class="badge badge-marks"><?php echo htmlspecialchars($q['marks']); ?> Marks</span>
                                </div>
                                <div class="question-actions">
                                    <a href="create_questions.php?id=<?php echo $exam_id; ?>&action=edit&question_id=<?php echo $q['id']; ?>" class="btn-sm edit-button">Edit</a>
                                    <a href="create_questions.php?id=<?php echo $exam_id; ?>&action=delete&question_id=<?php echo $q['id']; ?>" class="btn-sm delete-button" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div> <!-- End .dashboard-content -->

</div> <!-- End .main-content -->

<script>
const partAMarks = <?php echo floatval($exam['part_a_marks']); ?>;
const partBMarks = <?php echo floatval($exam['part_b_marks']); ?>;
const isEditMode = <?php echo $edit_mode ? 'true' : 'false'; ?>;

function updateDefaultMarks(part) {
    if (!isEditMode) {
        const marksInput = document.getElementById('marks');
        if (part === 'A') {
            marksInput.value = partAMarks;
        } else if (part === 'B') {
            marksInput.value = partBMarks;
        }
    }
}

if (typeof lucide !== 'undefined') {
    lucide.createIcons();
}
</script>

<?php include 'includes/footer.php'; ?>

</body>
</html>