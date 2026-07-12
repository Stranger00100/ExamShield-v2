<?php

include 'config/database.php';

// Teacher Details
$teacherName = "Demo Teacher";
$teacherUsername = "teacher";
$teacherPassword = password_hash("teacher123", PASSWORD_DEFAULT);
$teacherEmail = "teacher@examshield.com";

// Student Details
$studentName = "Demo Student";
$studentUsername = "student";
$studentUniversity = "BCA24001";
$studentPassword = password_hash("student123", PASSWORD_DEFAULT);
$studentEmail = "student@examshield.com";

// Insert Teacher
$conn->query("
INSERT INTO users
(full_name, username, password, role, email)
VALUES
('$teacherName','$teacherUsername','$teacherPassword','teacher','$teacherEmail')
");

// Insert Student
$conn->query("
INSERT INTO users
(full_name, username, university_number, password, role, email)
VALUES
('$studentName','$studentUsername','$studentUniversity','$studentPassword','student','$studentEmail')
");

echo "<h2>✅ Demo users created successfully.</h2>";

?>