<?php
$exam_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
header("Location: create_questions.php?id=" . $exam_id);
exit();
?>