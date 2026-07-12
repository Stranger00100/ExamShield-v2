<?php

session_start();

// User not logged in
if(!isset($_SESSION['user_id'])){

    header("Location: ../login.php");
    exit();

}

// Student trying to access Teacher Dashboard
if($_SESSION['role'] != "teacher"){

    header("Location: ../login.php");
    exit();

}

?>

<!DOCTYPE html>
<html>

<head>
    <title>Teacher Dashboard</title>
</head>

<body>

<h1>Teacher Dashboard</h1>

<p>Welcome <?php echo $_SESSION['username']; ?></p>

<a href="../logout.php">Logout</a>

</body>

</html>