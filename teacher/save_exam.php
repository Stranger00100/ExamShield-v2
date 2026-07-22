<?php
session_start();

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Extract incoming form variables
    $exam_title    = trim($_POST['exam_title'] ?? '');
    $subject       = trim($_POST['subject'] ?? '');
    $semester      = trim($_POST['semester'] ?? '');
    $academic_year = trim($_POST['academic_year'] ?? '');
    $language      = trim($_POST['language'] ?? '');
    $exam_type     = trim($_POST['exam_type'] ?? '');
    $exam_date     = $_POST['exam_date'] ?? '';
    $start_time    = $_POST['start_time'] ?? '';
    $end_time      = $_POST['end_time'] ?? '';
    $max_students  = intval($_POST['max_students'] ?? 0);
    $part_a_marks  = intval($_POST['part_a_marks'] ?? 50);
    $part_b_marks  = intval($_POST['part_b_marks'] ?? 50);
    $penalty_marks = intval($_POST['penalty_marks'] ?? 5);
    
    $created_by    = $_SESSION['user_id'] ?? null; 
    $status        = 'Draft';

    // Temporary Step 2 Fix: Generate a unique random room code (e.g., ESC42P)
    // This prevents the duplicate key '' error until Part 2 is built.
    $room_code     = "ES" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

    // Server-Side Fallback Calculations
    $start_minutes = (int)date('H', strtotime($start_time)) * 60 + (int)date('i', strtotime($start_time));
    $end_minutes   = (int)date('H', strtotime($end_time)) * 60 + (int)date('i', strtotime($end_time));
    
    if ($end_minutes < $start_minutes) {
        $end_minutes += 24 * 60; 
    }
    $duration = $end_minutes - $start_minutes;
    $total_marks = $part_a_marks + $part_b_marks;

    if (empty($exam_title) || empty($subject) || empty($exam_date) || empty($start_time) || empty($end_time)) {
        header("Location: create_exam.php?error=missing_fields");
        exit();
    }

    // Added room_code to the columns and an extra ? placeholder
    $sql = "INSERT INTO exams (
                title, subject, semester, academic_year, language, exam_type, 
                exam_date, start_time, end_time, duration, max_students, 
                part_a_marks, part_b_marks, penalty_marks, total_marks, status, created_by, room_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        
        // Updated bind format string to accommodate the extra string variable ("sssssssssiiiiiiiis")
        $stmt->bind_param(
            "sssssssssiiiiiiiis", 
            $exam_title, 
            $subject, 
            $semester, 
            $academic_year, 
            $language, 
            $exam_type, 
            $exam_date, 
            $start_time, 
            $end_time, 
            $duration, 
            $max_students, 
            $part_a_marks, 
            $part_b_marks, 
            $penalty_marks, 
            $total_marks, 
            $status, 
            $created_by,
            $room_code
        );

        if ($stmt->execute()) {
            $exam_id = $conn->insert_id;
            $stmt->close();
            
            if (isset($_POST['navigate']) && $_POST['navigate'] === 'next') {
                header("Location: exam_questions.php?id=" . $exam_id);
            } else {
                header("Location: dashboard.php?success=exam_saved");
            }
            exit();
        } else {
            die("Execution Error: " . $stmt->error);
        }
    } else {
        die("Preparation Error: " . $conn->error);
    }
} else {
    header("Location: create_exam.php");
    exit();
}