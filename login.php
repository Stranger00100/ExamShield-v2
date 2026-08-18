<?php
session_start();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] === "teacher") {
        header("Location: teacher/dashboard.php");
    } else {
        header("Location: student/dashboard.php");
    }
    exit();
}

include 'config/database.php';

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $selected_role = trim($_POST['role'] ?? 'teacher');

    // Query user by username AND selected role
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND role = ?");
    $stmt->bind_param("ss", $username, $selected_role);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === "teacher") {
                header("Location: teacher/dashboard.php");
            } else {
                header("Location: student/dashboard.php");
            }
            exit();
        } else {
            $error = "Invalid Password!";
        }
    } else {
        $error = "Account not found for selected role (" . ucfirst($selected_role) . ")!";
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

            <li><a href="index.php">Home</a></li>

          <a href="index.php#features">Features</a>

<a href="index.php#about">About</a>

<a href="index.php#contact">Contact</a>

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
<a href="index.php#about" class="learn-btn">
    Learn More
</a>
        </div>
<!-- Right Side -->

        <div class="login-card">

            <h2>Welcome Back</h2>

            <p>Login to continue</p>
            <?php if(!empty($error)) : ?>

    <div class="error-message">

        <?php echo $error; ?>

    </div>

<?php endif; ?>

            <!-- Role Selector -->
            <div class="role-selector">
                <button type="button" class="role-btn active" data-role="teacher" onclick="setRole('teacher', this)">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    Teacher
                </button>
                <button type="button" class="role-btn" data-role="student" onclick="setRole('student', this)">
                    <i class="fa-solid fa-user-graduate"></i>
                    Student
                </button>
            </div>

            <form method="POST">
                <input type="hidden" name="role" id="selected_role" value="teacher">

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

           <a href="forgot-password.php">

    Forgot Password?

</a>

        </div>

    </section>

    <script src="assets/js/login.js"></script>

    <script>
    function setRole(role, element) {
        document.getElementById('selected_role').value = role;
        
        // Toggle active tab visual state
        const buttons = document.querySelectorAll('.role-selector button');
        buttons.forEach(btn => btn.classList.remove('active'));
        element.classList.add('active');
    }
    </script>

</body>

</html>