<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';

// Student Authentication Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit();
}

$input = json_decode(file_get_contents('php://input'), true);
$student_exam_id = (int)($input['student_exam_id'] ?? 0);
$event_type = trim($input['event_type'] ?? '');
$details = trim($input['details'] ?? '');

if ($student_exam_id <= 0 || empty($event_type)) {
    echo json_encode(['success' => false, 'error' => 'Invalid telemetry payload']);
    exit();
}

// Verify that the exam session belongs to this active student
$student_id = (int)$_SESSION['user_id'];
$stmt_chk = $conn->prepare("SELECT id FROM student_exams WHERE id = ? AND student_id = ? AND status = 'started' LIMIT 1");
$stmt_chk->bind_param("ii", $student_exam_id, $student_id);
$stmt_chk->execute();
$res_chk = $stmt_chk->get_result();
$valid_attempt = $res_chk ? $res_chk->fetch_assoc() : null;
$stmt_chk->close();

if (!$valid_attempt) {
    echo json_encode(['success' => false, 'error' => 'Session inactive or mismatched']);
    exit();
}

// Log the activity event securely
$stmt = $conn->prepare("INSERT INTO exam_activity_logs (student_exam_id, event_type, event_details) VALUES (?, ?, ?)");
$stmt->bind_param("iss", $student_exam_id, $event_type, $details);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);