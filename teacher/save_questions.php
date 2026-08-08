<?php
session_start();
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $exam_id = intval($_POST['exam_id'] ?? 0);
    $part_a = $_POST['part_a_questions'] ?? [];
    $part_b = $_POST['part_b_questions'] ?? [];
    
    if ($exam_id === 0) {
        header("Location: create_exam.php?error=invalid_exam");
        exit();
    }

    // Since questions are global, we link them to this exam.
    // To handle updates cleanly, we clear out any previously selected questions for this exam first.
    $clear_sql = "UPDATE questions SET exam_id = NULL WHERE exam_id = ?";
    if ($clear_stmt = $conn->prepare($clear_sql)) {
        $clear_stmt->bind_param("i", $exam_id);
        $clear_stmt->execute();
        $clear_stmt->close();
    }

    // Update Part A questions to link to this exam
    if (!empty($part_a)) {
        foreach ($part_a as $q_id) {
            $q_id = intval($q_id);
            $update_sql = "UPDATE questions SET exam_id = ? WHERE id = ?";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("ii", $exam_id, $q_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
    }

    // Update Part B questions to link to this exam
    if (!empty($part_b)) {
        foreach ($part_b as $q_id) {
            $q_id = intval($q_id);
            $update_sql = "UPDATE questions SET exam_id = ? WHERE id = ?";
            if ($update_stmt = $conn->prepare($update_sql)) {
                $update_stmt->bind_param("ii", $exam_id, $q_id);
                $update_stmt->execute();
                $update_stmt->close();
            }
        }
    }

    // Workflow Routing Configuration
    if (isset($_POST['navigate']) && $_POST['navigate'] === 'next') {
        // Direct them to Step 3: Test Case Configurations setup stage
        header("Location: exam_testcases.php?id=" . $exam_id);
    } else {
        header("Location: dashboard.php?success=questions_saved");
    }
    exit();
} else {
    header("Location: create_exam.php");
    exit();
}