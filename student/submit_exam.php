<?php
session_start();
require_once __DIR__ . '/../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: ../login.php");
    exit();
}

$student_id = (int)$_SESSION['user_id'];
$raw_input = file_get_contents('php://input');
$payload = json_decode($raw_input, true);

$exam_id = (int)($payload['exam_id'] ?? $_GET['exam_id'] ?? 0);
$part_a_code = $payload['part_a_code'] ?? '';
$part_b_code = $payload['part_b_code'] ?? '';
$language = strtolower(trim($payload['language'] ?? 'c'));

if ($exam_id <= 0) {
    header("Location: dashboard.php");
    exit();
}

// 1. Fetch Student Exam Record
$stmt = $conn->prepare("
    SELECT id, part_a_question_id, part_b_question_id 
    FROM student_exams 
    WHERE exam_id = ? AND student_id = ? 
    ORDER BY id DESC LIMIT 1
");
$stmt->bind_param("ii", $exam_id, $student_id);
$stmt->execute();
$res = $stmt->get_result();
$attempt = $res ? $res->fetch_assoc() : null;
$stmt->close();

if ($attempt) {
    $student_exam_id = (int)$attempt['id'];
    $part_a_qid = !empty($attempt['part_a_question_id']) ? (int)$attempt['part_a_question_id'] : null;
    $part_b_qid = !empty($attempt['part_b_question_id']) ? (int)$attempt['part_b_question_id'] : null;

    // Delete any previous temporary records for this attempt
    $conn->query("DELETE FROM student_submissions WHERE student_exam_id = {$student_exam_id}");

    // 2. Insert Part A Code
    $stmt_a = $conn->prepare("INSERT INTO student_submissions (student_exam_id, question_id, submitted_code, language, score) VALUES (?, ?, ?, ?, 0.00)");
    $stmt_a->bind_param("iiss", $student_exam_id, $part_a_qid, $part_a_code, $language);
    $stmt_a->execute();
    $stmt_a->close();

    // 3. Insert Part B Code
    $stmt_b = $conn->prepare("INSERT INTO student_submissions (student_exam_id, question_id, submitted_code, language, score) VALUES (?, ?, ?, ?, 0.00)");
    $stmt_b->bind_param("iiss", $student_exam_id, $part_b_qid, $part_b_code, $language);
    $stmt_b->execute();
    $stmt_b->close();

    // 4. Mark Exam as Submitted
    $now = date('Y-m-d H:i:s');
    $stmt_close = $conn->prepare("UPDATE student_exams SET status = 'submitted', submitted_at = ?, final_score = 0.00 WHERE id = ?");
    $stmt_close->bind_param("si", $now, $student_exam_id);
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