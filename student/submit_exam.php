<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../compiler/evaluator.php';

// Student Authentication Gatekeeper
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$exam_id = 0;
$part_a_code = '';
$part_b_code = '';
$language = 'c';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $payload = json_decode(file_get_contents('php://input'), true);
    $exam_id = (int)($payload['exam_id'] ?? 0);
    $part_a_code = trim($payload['part_a_code'] ?? '');
    $part_b_code = trim($payload['part_b_code'] ?? '');
    $language = strtolower(trim($payload['language'] ?? 'c'));
} else {
    $exam_id = (int)($_GET['exam_id'] ?? 0);
}

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// 1. Fetch Active Student Exam Attempt Record
$stmt = $conn->prepare("
    SELECT id, part_a_question_id, part_b_question_id, status, marks_deducted 
    FROM student_exams 
    WHERE exam_id = ? AND student_id = ? AND status != 'submitted' 
    LIMIT 1
");
$stmt->bind_param("ii", $exam_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
$attempt = $res ? $res->fetch_assoc() : null;
$stmt->close();

if ($attempt) {
    $student_exam_id = (int)$attempt['id'];
    $part_a_qid = (int)$attempt['part_a_question_id'];
    $part_b_qid = (int)$attempt['part_b_question_id'];
    $penalty = (int)$attempt['marks_deducted'];

    $score_a = 0.00;
    $score_b = 0.00;

    // 2. Evaluate Part A
    if (!empty($part_a_code) && $part_a_qid > 0) {
        $eval_a = evaluateStudentCode($conn, $part_a_qid, $part_a_code, $language);
        $score_a = (float)$eval_a['score'];

        $stmt_sub_a = $conn->prepare("
            INSERT INTO student_submissions (student_exam_id, question_id, submitted_code, language, score) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_sub_a->bind_param("iissd", $student_exam_id, $part_a_qid, $part_a_code, $language, $score_a);
        $stmt_sub_a->execute();
        $stmt_sub_a->close();
    }

    // 3. Evaluate Part B
    if (!empty($part_b_code) && $part_b_qid > 0) {
        $eval_b = evaluateStudentCode($conn, $part_b_qid, $part_b_code, $language);
        $score_b = (float)$eval_b['score'];

        $stmt_sub_b = $conn->prepare("
            INSERT INTO student_submissions (student_exam_id, question_id, submitted_code, language, score) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt_sub_b->bind_param("iissd", $student_exam_id, $part_b_qid, $part_b_code, $language, $score_b);
        $stmt_sub_b->execute();
        $stmt_sub_b->close();
    }

    // 4. Compute Final Score
    $total_calculated_score = max(0.00, ($score_a + $score_b) - $penalty);

    // 5. Mark Exam as Submitted and Save Final Score
    $now = date('Y-m-d H:i:s');
    $stmt_close = $conn->prepare("
        UPDATE student_exams 
        SET status = 'submitted', submitted_at = ?, final_score = ? 
        WHERE id = ?
    ");
    $stmt_close->bind_param("sdi", $now, $total_calculated_score, $student_exam_id);
    $stmt_close->execute();
    $stmt_close->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit();
}

header("Location: results.php");
exit();