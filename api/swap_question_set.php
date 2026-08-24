<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access.']);
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$input = json_decode(file_get_contents('php://input'), true);
$exam_id = (int)($input['exam_id'] ?? 0);

if ($exam_id <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid examination identifier.']);
    exit();
}

// 1. Fetch current student exam state
$stmt = $conn->prepare("
    SELECT id, part_a_question_id, part_b_question_id, question_set_swaps, marks_deducted, status 
    FROM student_exams 
    WHERE exam_id = ? AND student_id = ? AND status = 'started' 
    LIMIT 1
");
$stmt->bind_param("ii", $exam_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
$attempt = $res ? $res->fetch_assoc() : null;
$stmt->close();

if (!$attempt) {
    echo json_encode(['success' => false, 'error' => 'No active examination attempt found.']);
    exit();
}

$current_swaps = (int)$attempt['question_set_swaps'];
$student_exam_id = (int)$attempt['id'];
$old_part_a = (int)$attempt['part_a_question_id'];
$old_part_b = (int)$attempt['part_b_question_id'];

// 2. Server-side restriction: Maximum 1 swap allowed
if ($current_swaps >= 1) {
    echo json_encode([
        'success' => false, 
        'error' => 'You have already used your allowed question set change. Further changes are restricted.'
    ]);
    exit();
}

// 3. Fetch penalty configured on the exam (fallback to 5 marks)
$stmt_exam = $conn->prepare("SELECT penalty_marks FROM exams WHERE id = ? LIMIT 1");
$stmt_exam->bind_param("i", $exam_id);
$stmt_exam->execute();
$res_exam = $stmt_exam->get_result();
$exam_meta = $res_exam ? $res_exam->fetch_assoc() : null;
$stmt_exam->close();

$penalty_to_add = (int)($exam_meta['penalty_marks'] ?? 5);
if ($penalty_to_add <= 0) {
    $penalty_to_add = 5;
}

// 4. Select a new Part A question (excluding the currently assigned one if possible)
$stmt_new_a = $conn->prepare("
    SELECT id FROM questions 
    WHERE exam_id = ? AND part = 'A' AND id != ? 
    ORDER BY RAND() 
    LIMIT 1
");
$stmt_new_a->bind_param("ii", $exam_id, $old_part_a);
$stmt_new_a->execute();
$res_new_a = $stmt_new_a->get_result();
$new_a_row = $res_new_a ? $res_new_a->fetch_assoc() : null;
$stmt_new_a->close();
$new_part_a_id = $new_a_row['id'] ?? $old_part_a;

// 5. Select a new Part B question (excluding the currently assigned one if possible)
$stmt_new_b = $conn->prepare("
    SELECT id FROM questions 
    WHERE exam_id = ? AND part = 'B' AND id != ? 
    ORDER BY RAND() 
    LIMIT 1
");
$stmt_new_b->bind_param("ii", $exam_id, $old_part_b);
$stmt_new_b->execute();
$res_new_b = $stmt_new_b->get_result();
$new_b_row = $res_new_b ? $res_new_b->fetch_assoc() : null;
$stmt_new_b->close();
$new_part_b_id = $new_b_row['id'] ?? $old_part_b;

// 6. Update student_exams record with both new questions, increment swaps, and add penalty
$new_swaps_count = $current_swaps + 1;
$total_penalty = (int)$attempt['marks_deducted'] + $penalty_to_add;

$stmt_upd = $conn->prepare("
    UPDATE student_exams 
    SET part_a_question_id = ?, part_b_question_id = ?, question_set_swaps = ?, marks_deducted = ? 
    WHERE id = ?
");
$stmt_upd->bind_param("iiiii", $new_part_a_id, $new_part_b_id, $new_swaps_count, $total_penalty, $student_exam_id);
$stmt_upd->execute();
$stmt_upd->close();

echo json_encode([
    'success' => true,
    'penalty_applied' => $penalty_to_add,
    'total_deductions' => $total_penalty
]);