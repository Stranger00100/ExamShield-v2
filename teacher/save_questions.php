<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'teacher') {
    header("Location: ../login.php");
    exit();
}

require_once __DIR__ . '/../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: manage_exam.php");
    exit();
}

// Read and sanitize parameters
$exam_id = isset($_POST['exam_id']) ? intval($_POST['exam_id']) : 0;
$question_id = isset($_POST['question_id']) ? intval($_POST['question_id']) : 0;
$part = isset($_POST['part']) && in_array($_POST['part'], ['A', 'B']) ? $_POST['part'] : 'A';
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$question_type = isset($_POST['question_type']) ? trim($_POST['question_type']) : 'Coding';
$difficulty = isset($_POST['difficulty']) ? trim($_POST['difficulty']) : 'Easy';
$language = isset($_POST['language']) ? trim($_POST['language']) : 'C';
$question = isset($_POST['question']) ? trim($_POST['question']) : '';
$description = isset($_POST['description']) ? trim($_POST['description']) : '';
$constraints = isset($_POST['constraints']) ? trim($_POST['constraints']) : '';
$input_format = isset($_POST['input_format']) ? trim($_POST['input_format']) : '';
$output_format = isset($_POST['output_format']) ? trim($_POST['output_format']) : '';
$sample_input = isset($_POST['sample_input']) ? trim($_POST['sample_input']) : '';
$sample_output = isset($_POST['sample_output']) ? trim($_POST['sample_output']) : '';
$marks = isset($_POST['marks']) ? floatval($_POST['marks']) : 0.0;

if ($exam_id <= 0 || empty($title) || empty($question)) {
    $error = urlencode("Please fill in all required fields.");
    header("Location: create_questions.php?id={$exam_id}&error={$error}");
    exit();
}

// Verify Exam Existence using MySQLi
$stmt = $conn->prepare("SELECT id FROM exams WHERE id = ?");
$stmt->bind_param("i", $exam_id);
$stmt->execute();
$exam_exists = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$exam_exists) {
    header("Location: manage_exam.php");
    exit();
}

if ($question_id > 0) {
    // UPDATE existing question
    $update_sql = "UPDATE questions SET 
        part = ?, 
        title = ?, 
        question_type = ?, 
        difficulty = ?, 
        language = ?, 
        question = ?, 
        description = ?, 
        constraints = ?, 
        input_format = ?, 
        output_format = ?, 
        sample_input = ?, 
        sample_output = ?, 
        marks = ? 
        WHERE id = ? AND exam_id = ?";
    
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param(
        "sssssssssssddii", 
        $part, $title, $question_type, $difficulty, $language, $question,
        $description, $constraints, $input_format, $output_format,
        $sample_input, $sample_output, $marks, $question_id, $exam_id
    );
    $stmt_update->execute();
    $stmt_update->close();

    header("Location: create_questions.php?id={$exam_id}&msg=saved");
    exit();

} else {
    // INSERT new question
    // Check Part Limit (Max 10)
    $stmt_count = $conn->prepare("SELECT COUNT(*) as cnt FROM questions WHERE exam_id = ? AND part = ?");
    $stmt_count->bind_param("is", $exam_id, $part);
    $stmt_count->execute();
    $count_row = $stmt_count->get_result()->fetch_assoc();
    $current_count = $count_row['cnt'] ?? 0;
    $stmt_count->close();

    if ($current_count >= 10) {
        $error = urlencode("Part {$part} already has the maximum limit of 10 questions.");
        header("Location: create_questions.php?id={$exam_id}&error={$error}");
        exit();
    }

    // Auto calculate next question_number for this part
    $stmt_max = $conn->prepare("SELECT MAX(question_number) as max_num FROM questions WHERE exam_id = ? AND part = ?");
    $stmt_max->bind_param("is", $exam_id, $part);
    $stmt_max->execute();
    $max_row = $stmt_max->get_result()->fetch_assoc();
    $max_num = $max_row['max_num'] ?? 0;
    $next_q_number = intval($max_num) + 1;
    $stmt_max->close();

    $insert_sql = "INSERT INTO questions (
        exam_id, part, question_number, title, question_type, difficulty, language, 
        question, description, constraints, input_format, output_format, 
        sample_input, sample_output, marks
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($insert_sql);
    $stmt_insert->bind_param(
        "isisssssssssssd",
        $exam_id, $part, $next_q_number, $title, $question_type, $difficulty, $language,
        $question, $description, $constraints, $input_format, $output_format,
        $sample_input, $sample_output, $marks
    );
    $stmt_insert->execute();
    $stmt_insert->close();

    header("Location: create_questions.php?id={$exam_id}&msg=saved");
    exit();
}