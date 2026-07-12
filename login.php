<?php

session_start();

include 'config/database.php';

?>
<?php

$error = "";

if(isset($_POST['login'])){

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    $sql = "SELECT * FROM users WHERE username='$username'";

    $result = mysqli_query($conn, $sql);

    if(mysqli_num_rows($result) == 1){

        $user = mysqli_fetch_assoc($result);

        if(password_verify($password, $user['password'])){

           $_SESSION['user_id'] = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role'] = $user['role'];

if($user['role'] == "teacher"){

    header("Location: teacher/dashboard.php");
    exit();

}else{

    header("Location: student/dashboard.php");
    exit();

}

        }else{

            echo "Invalid Username or Password";

        }

    }else{

        echo "Invalid Username or Password";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Exam Shield | Login</title>

    <link rel="stylesheet" href="assets/css/login.css">

    <!-- Google Font -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

</head>

<body>

    <!-- Background -->

    <div class="background"></div>

    <!-- Navbar -->

    <nav>

        <div class="logo">

            <i class="fa-solid fa-shield-halved"></i>

            <span>Exam Shield</span>

        </div>

        <ul>

            <li><a href="#">Home</a></li>

            <li><a href="#">Features</a></li>

            <li><a href="#">About</a></li>

            <li><a href="#">Contact</a></li>

        </ul>

    </nav>

    <!-- Main Section -->

    <section class="hero">

        <!-- Left Side -->

        <div class="hero-left">

            <h1>

                Secure <br>

                Coding <br>

                <span>Examinations.</span>

            </h1>

            <p>

                Conduct programming laboratory examinations securely with
                automated code evaluation, hidden test cases,
                room-based exams and student activity monitoring.

            </p>

            <button>

                Learn More

            </button>

        </div>

        <!-- Right Side -->

        <div class="login-card">

            <h2>Welcome Back</h2>

            <p>Login to continue</p>

            <div class="role-selector">

                <button class="active">

                    <i class="fa-solid fa-chalkboard-user"></i>

                    Teacher

                </button>

                <button>

                    <i class="fa-solid fa-user-graduate"></i>

                    Student

                </button>

            </div>

            <form method="POST">

                <input
    type="text"
    name="username"
    placeholder="Username"
    required
>

             <input
    type="password"
    name="password"
    placeholder="Password"
    required
>
                <button
    type="submit"
    name="login"
>

                    LOGIN

                </button>

            </form>

            <a href="#">

                Forgot Password?

            </a>

        </div>

    </section>

    <script src="assets/js/login.js"></script>

</body>

</html>