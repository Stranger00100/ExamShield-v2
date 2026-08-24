<?php

session_start();

require_once __DIR__ . "/config/database.php";

$message = "";
$messageType = "";

$username = "";
$email = "";
$role = "";


/* =========================================================
   REGISTRATION PROCESS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirmPassword = $_POST["confirm_password"] ?? "";
    $role = strtolower(trim($_POST["role"] ?? ""));


    /* =====================================================
       VALIDATION
    ===================================================== */

    if (
        $username === "" ||
        $email === "" ||
        $password === "" ||
        $confirmPassword === "" ||
        $role === ""
    ) {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (strlen($username) < 3) {

        $message = "Username must contain at least 3 characters.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } elseif (!in_array($role, ["admin", "teacher", "student"], true)) {

        $message = "Please select a valid account role.";
        $messageType = "error";

    } elseif (strlen($password) < 8) {

        $message = "Password must contain at least 8 characters.";
        $messageType = "error";

    } elseif ($password !== $confirmPassword) {

        $message = "Passwords do not match.";
        $messageType = "error";

    } else {


        /* =================================================
           CHECK EXISTING EMAIL
        ================================================= */

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ? LIMIT 1"
        );


        if (!$check) {

            $message = "Database error. Please try again.";
            $messageType = "error";

        } else {

            $check->bind_param("s", $email);

            $check->execute();

            $check->store_result();


            if ($check->num_rows > 0) {

                $message =
                    "An account with this email already exists.";

                $messageType = "error";

            } else {


                /* =========================================
                   HASH PASSWORD
                ========================================= */

                $hashedPassword =
                    password_hash(
                        $password,
                        PASSWORD_DEFAULT
                    );


                /* =========================================
                   INSERT USER
                ========================================= */

                $insert = $conn->prepare(
                    "INSERT INTO users
                    (username, email, password, role)
                    VALUES (?, ?, ?, ?)"
                );


                if (!$insert) {

                    $message =
                        "Unable to create account.";

                    $messageType =
                        "error";

                } else {

                    $insert->bind_param(
                        "ssss",
                        $username,
                        $email,
                        $hashedPassword,
                        $role
                    );


                    if ($insert->execute()) {

                        /*
                         * Registration successful.
                         *
                         * Redirect to login page instead of
                         * automatically logging the user in.
                         */

                        header(
                            "Location: login.php?registered=1"
                        );

                        exit();

                    } else {

                        $message =
                            "Registration failed. Please try again.";

                        $messageType =
                            "error";

                    }


                    $insert->close();

                }

            }


            $check->close();

        }

    }

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        name="description"
        content="Create your secure Exam Shield account."
    >

    <title>
        Create Account | Exam Shield
    </title>


    <!-- =====================================================
         GOOGLE FONT
    ====================================================== -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- =====================================================
         REGISTER CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/register.css"
    >

</head>


<body>


    <!-- =====================================================
         ANIMATED BACKGROUND
    ====================================================== -->

    <div class="background-animation">

        <span class="floating-shape shape-one"></span>

        <span class="floating-shape shape-two"></span>

        <span class="floating-shape shape-three"></span>

        <span class="floating-shape shape-four"></span>

        <span class="floating-shape shape-five"></span>

    </div>



    <!-- =====================================================
         REGISTER PAGE
    ====================================================== -->

    <main class="register-page">


        <!-- =================================================
             LEFT INFORMATION
        ================================================== -->

        <section class="register-info">


            <!-- BRAND -->

            <div class="brand">

                <div class="brand-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>


                <div>

                    <h2>
                        Exam<span>Shield</span>
                    </h2>

                    <p>
                        Smart Coding Examination
                    </p>

                </div>

            </div>



            <!-- CONTENT -->

            <div class="info-content">


                <span class="security-badge">

                    <i class="fa-solid fa-user-shield"></i>

                    SECURE EXAMINATION PLATFORM

                </span>


                <h1>

                    Create Your
                    <span>Account.</span>

                </h1>


                <p>

                    Join Exam Shield and experience a
                    secure, intelligent and reliable
                    coding examination platform.

                </p>



                <!-- FEATURES -->

                <div class="register-features">


                    <div class="register-feature">

                        <div class="feature-icon">

                            <i class="fa-solid fa-code"></i>

                        </div>

                        <div>

                            <h3>
                                Smart Coding Exams
                            </h3>

                            <p>
                                Conduct and evaluate coding
                                examinations online.
                            </p>

                        </div>

                    </div>



                    <div class="register-feature">

                        <div class="feature-icon">

                            <i class="fa-solid fa-video"></i>

                        </div>

                        <div>

                            <h3>
                                Secure Monitoring
                            </h3>

                            <p>
                                Monitor students during
                                online examinations.
                            </p>

                        </div>

                    </div>



                    <div class="register-feature">

                        <div class="feature-icon">

                            <i class="fa-solid fa-chart-line"></i>

                        </div>

                        <div>

                            <h3>
                                Performance Analytics
                            </h3>

                            <p>
                                Generate detailed examination
                                performance reports.
                            </p>

                        </div>

                    </div>


                </div>

            </div>



            <!-- FOOTER -->

            <div class="register-info-footer">

                <span>

                    <i class="fa-solid fa-lock"></i>

                    Secure Authentication

                </span>


                <span>

                    <i class="fa-solid fa-shield-halved"></i>

                    Anti-Cheating

                </span>


                <span>

                    <i class="fa-solid fa-bolt"></i>

                    Instant Evaluation

                </span>

            </div>


        </section>



        <!-- =================================================
             REGISTER CARD
        ================================================== -->

        <section class="register-container">


            <div class="register-card">


                <!-- HEADER -->

                <div class="register-header">


                    <div class="register-icon">

                        <i class="fa-solid fa-user-plus"></i>

                    </div>


                    <h2>
                        Create Account
                    </h2>


                    <p>
                        Register to start using Exam Shield
                    </p>

                </div>



                <!-- =================================================
                     SERVER MESSAGE
                ================================================== -->

                <?php if ($message !== ""): ?>

                    <div
                        class="register-alert <?php echo htmlspecialchars($messageType); ?>"
                    >

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>

                            <?php
                            echo htmlspecialchars($message);
                            ?>

                        </span>

                    </div>

                <?php endif; ?>



                <!-- =================================================
                     REGISTER FORM
                ================================================== -->

                <form
                    method="POST"
                    action=""
                    class="register-form"
                    id="registerForm"
                    autocomplete="off"
                >


                    <!-- USERNAME -->

                    <div class="form-group">

                        <label for="username">

                            <i class="fa-solid fa-user"></i>

                            Username

                        </label>


                        <div class="input-box">

                            <i
                                class="fa-solid fa-user input-icon"
                            ></i>


                            <input
                                type="text"
                                id="username"
                                name="username"
                                placeholder="Enter your username"
                                value="<?php echo htmlspecialchars($username); ?>"
                                minlength="3"
                                maxlength="50"
                                required
                            >

                        </div>

                    </div>



                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">

                            <i class="fa-solid fa-envelope"></i>

                            Email Address

                        </label>


                        <div class="input-box">

                            <i
                                class="fa-solid fa-envelope input-icon"
                            ></i>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($email); ?>"
                                maxlength="100"
                                required
                            >

                        </div>

                    </div>



                    <!-- ROLE -->

                    <div class="form-group">

                        <label for="role">

                            <i class="fa-solid fa-users"></i>

                            Account Type

                        </label>


                        <div class="input-box select-box">

                            <i
                                class="fa-solid fa-user-tag input-icon"
                            ></i>


                            <select
                                id="role"
                                name="role"
                                required
                            >

                                <option
                                    value=""
                                    disabled
                                    <?php
                                    echo $role === ""
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Select account type
                                </option>


                                <option
                                    value="student"
                                    <?php
                                    echo $role === "student"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Student
                                </option>


                                <option
                                    value="teacher"
                                    <?php
                                    echo $role === "teacher"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Teacher
                                </option>


                                <option
                                    value="admin"
                                    <?php
                                    echo $role === "admin"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Admin
                                </option>

                            </select>


                            <i
                                class="fa-solid fa-chevron-down select-arrow"
                            ></i>

                        </div>

                    </div>



                    <!-- PASSWORD ROW -->

                    <div class="password-row">


                        <!-- PASSWORD -->

                        <div class="form-group">

                            <label for="password">

                                <i class="fa-solid fa-lock"></i>

                                Password

                            </label>


                            <div class="input-box">

                                <i
                                    class="fa-solid fa-lock input-icon"
                                ></i>


                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    placeholder="Create password"
                                    minlength="8"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="password"
                                    aria-label="Show password"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                            </div>

                        </div>



                        <!-- CONFIRM PASSWORD -->

                        <div class="form-group">

                            <label for="confirm_password">

                                <i class="fa-solid fa-lock"></i>

                                Confirm Password

                            </label>


                            <div class="input-box">

                                <i
                                    class="fa-solid fa-lock input-icon"
                                ></i>


                                <input
                                    type="password"
                                    id="confirm_password"
                                    name="confirm_password"
                                    placeholder="Confirm password"
                                    minlength="8"
                                    required
                                >


                                <button
                                    type="button"
                                    class="password-toggle"
                                    data-target="confirm_password"
                                    aria-label="Show password"
                                >

                                    <i class="fa-solid fa-eye"></i>

                                </button>

                            </div>

                        </div>


                    </div>



                    <!-- PASSWORD STRENGTH -->

                    <div
                        class="password-strength"
                        id="passwordStrength"
                    >

                        <div class="strength-header">

                            <span>
                                Password strength
                            </span>

                            <span
                                id="strengthText"
                            >
                                Weak
                            </span>

                        </div>


                        <div class="strength-bar">

                            <span
                                id="strengthProgress"
                            ></span>

                        </div>


                        <div class="password-rules">

                            <span id="ruleLength">

                                <i class="fa-solid fa-circle"></i>

                                8+ characters

                            </span>


                            <span id="ruleNumber">

                                <i class="fa-solid fa-circle"></i>

                                Number

                            </span>


                            <span id="ruleSpecial">

                                <i class="fa-solid fa-circle"></i>

                                Special character

                            </span>

                        </div>

                    </div>



                    <!-- TERMS -->

                    <label class="terms-checkbox">

                        <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            required
                        >


                        <span class="custom-checkbox"></span>


                        <span>

                            I agree to the
                            <a href="#">
                                Terms & Conditions
                            </a>
                            and
                            <a href="#">
                                Privacy Policy
                            </a>

                        </span>

                    </label>



                    <!-- REGISTER BUTTON -->

                    <button
                        type="submit"
                        class="register-button"
                        id="registerButton"
                    >

                        <span>
                            Create Account
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                </form>



                <!-- LOGIN -->

                <div class="login-link">

                    <span>
                        Already have an account?
                    </span>


                    <a href="login.php">

                        Sign In

                        <i
                            class="fa-solid fa-arrow-right"
                        ></i>

                    </a>

                </div>



                <!-- SECURITY -->

                <div class="register-security">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Your information is securely protected
                    </span>

                    <i class="fa-solid fa-circle-check"></i>

                </div>


            </div>

        </section>

    </main>



    <!-- =====================================================
         REGISTER JAVASCRIPT
    ====================================================== -->

    <script src="assets/js/register.js"></script>

</body>

</html>