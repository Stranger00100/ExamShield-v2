<?php
session_start();

require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // ACTION 1: Host Exam (Triggered from create_questions.php)
    if (isset($_POST['action']) && $_POST['action'] === 'host_exam') {
        $exam_id = intval($_POST['exam_id'] ?? 0);

        if ($exam_id <= 0) {
            header("Location: dashboard.php?error=invalid_id");
            exit();
        }

        // Generate final active room code and set status to Active
        $room_code = "ES" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));
        $status    = 'Active';

        $sql = "UPDATE exams SET room_code = ?, status = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $room_code, $status, $exam_id);
            if ($stmt->execute()) {
                $stmt->close();
                header("Location: manage_exam.php?id=" . $exam_id . "&room_code=" . $room_code . "&msg=hosted");
                exit();
            } else {
                die("Execution Error: " . $stmt->error);
            }
        } else {
            die("Preparation Error: " . $conn->error);
        }
    }

    // ACTION 2: Create Exam Draft (Step 1 Form Submission)
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

    // Temporary room code assigned on draft creation
    $room_code     = "ES" . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 4));

    // Server-Side Calculations
    $start_minutes = (int)date('H', strtotime($start_time)) * 60 + (int)date('i', strtotime($start_time));
    $end_minutes   = (int)date('H', strtotime($end_time)) * 60 + (int)date('i', strtotime($end_time));
    
    if ($end_minutes < $start_minutes) {
        $end_minutes += 24 * 60; 
    }
    $duration    = $end_minutes - $start_minutes;
    $total_marks = $part_a_marks + $part_b_marks;

    if (empty($exam_title) || empty($subject) || empty($exam_date) || empty($start_time) || empty($end_time)) {
        header("Location: create_exam.php?error=missing_fields");
        exit();
    }

    $sql = "INSERT INTO exams (
                title, subject, semester, academic_year, language, exam_type, 
                exam_date, start_time, end_time, duration, max_students, 
                part_a_marks, part_b_marks, penalty_marks, total_marks, status, created_by, room_code
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
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
                header("Location: create_questions.php?id=" . $exam_id);
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