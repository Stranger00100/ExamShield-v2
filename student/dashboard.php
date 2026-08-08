<?php

session_start();

// User not logged in
if(!isset($_SESSION['user_id'])){

    header("Location: ../login.php");
    exit();

}

// Teacher trying to access Student Dashboard
if($_SESSION['role'] != "student"){

    header("Location: ../login.php");
    exit();

}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Student Dashboard</title>
</head>

<body>

<h1>Student Dashboard</h1>

<p>Welcome <?php echo $_SESSION['username']; ?></p>

<a href="../logout.php">Logout</a>

</body>

</html>