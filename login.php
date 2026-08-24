<?php

session_start();

require_once __DIR__ . "/config/database.php";

$message = "";
$messageType = "";


/* =========================================================
   LOGIN PROCESS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $role = trim($_POST["role"] ?? "");


    /* -----------------------------------------------------
       VALIDATION
    ----------------------------------------------------- */

    if ($email === "" || $password === "" || $role === "") {

        $message = "Please fill in all fields.";
        $messageType = "error";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $messageType = "error";

    } else {

        /* -------------------------------------------------
           FIND USER
        ------------------------------------------------- */

        $sql = "SELECT id, username, email, password, role
                FROM users
                WHERE email = ?
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        if ($stmt) {

            $stmt->bind_param("s", $email);

            $stmt->execute();

            $result = $stmt->get_result();


            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();


                /* -----------------------------------------
                   CHECK ROLE
                ----------------------------------------- */

                if (
                    strtolower($user["role"]) !==
                    strtolower($role)
                ) {

                    $message =
                        "This account does not belong to the selected role.";

                    $messageType = "error";

                }

                /* -----------------------------------------
                   CHECK PASSWORD
                ----------------------------------------- */

                elseif (
                    password_verify(
                        $password,
                        $user["password"]
                    )
                ) {

                    /* -------------------------------------
                       CREATE SESSION
                    ------------------------------------- */

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["username"] =
                        $user["username"];

                    $_SESSION["email"] =
                        $user["email"];

                    $_SESSION["role"] =
                        strtolower($user["role"]);


                    /* -------------------------------------
                       REDIRECT BY ROLE
                    ------------------------------------- */

                    if (
                        strtolower($user["role"])
                        === "admin"
                    ) {

                        header(
                            "Location: admin/dashboard.php"
                        );

                        exit();

                    } elseif (
                        strtolower($user["role"])
                        === "teacher"
                    ) {

                        header(
                            "Location: teacher/dashboard.php"
                        );

                        exit();

                    } elseif (
                        strtolower($user["role"])
                        === "student"
                    ) {

                        header(
                            "Location: student/dashboard.php"
                        );

                        exit();

                    } else {

                        $message =
                            "Invalid account role.";

                        $messageType =
                            "error";
                    }

                } else {

                    $message =
                        "Incorrect email or password.";

                    $messageType =
                        "error";
                }

            } else {

                $message =
                    "Incorrect email or password.";

                $messageType =
                    "error";
            }

            $stmt->close();

        } else {

            $message =
                "Database error. Please try again.";

            $messageType =
                "error";
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
        content="Exam Shield secure login system"
    >

    <title>
        Login | Exam Shield
    </title>


    <!-- Google Font -->

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


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <!-- Login CSS -->

    <link
        rel="stylesheet"
        href="assets/css/login.css"
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
         LOGIN PAGE
    ====================================================== -->

    <main class="login-page">


        <!-- =================================================
             LEFT INFORMATION PANEL
        ================================================== -->

        <section class="login-info">


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



            <div class="info-content">

                <span class="security-badge">

                    <i class="fa-solid fa-shield-check"></i>

                    SECURE EXAMINATION PLATFORM

                </span>


                <h1>

                    Secure.
                    <span>Smart.</span>
                    Reliable.

                </h1>


                <p>

                    Conduct coding examinations securely,
                    monitor students in real time,
                    automatically evaluate code and generate
                    accurate results.

                </p>



                <!-- FEATURES -->

                <div class="login-features">


                    <div class="login-feature">

                        <div class="feature-icon">

                            <i class="fa-solid fa-video"></i>

                        </div>

                        <div>

                            <h3>
                                Live Exam Monitoring
                            </h3>

                            <p>
                                Monitor student activity
                                during examinations.
                            </p>

                        </div>

                    </div>



                    <div class="login-feature">

                        <div class="feature-icon">

                            <i class="fa-solid fa-code"></i>

                        </div>

                        <div>

                            <h3>
                                Automatic Evaluation
                            </h3>

                            <p>
                                Compile and evaluate coding
                                answers automatically.
                            </p>

                        </div>

                    </div>



                    <div class="login-feature">

                        <div class="feature-icon">

                            <i class="fa-solid fa-user-shield"></i>

                        </div>

                        <div>

                            <h3>
                                Anti-Cheating Protection
                            </h3>

                            <p>
                                Detect suspicious activity
                                during online exams.
                            </p>

                        </div>

                    </div>


                </div>

            </div>



            <div class="login-info-footer">

                <span>
                    <i class="fa-solid fa-lock"></i>
                    Secure Authentication
                </span>

                <span>
                    <i class="fa-solid fa-bolt"></i>
                    Instant Results
                </span>

                <span>
                    <i class="fa-solid fa-chart-line"></i>
                    Performance Analytics
                </span>

            </div>


        </section>



        <!-- =================================================
             RIGHT LOGIN PANEL
        ================================================== -->

        <section class="login-container">


            <div class="login-card">


                <!-- CARD HEADER -->

                <div class="login-header">


                    <div class="login-icon">

                        <i class="fa-solid fa-user-lock"></i>

                    </div>


                    <h2>
                        Welcome Back
                    </h2>


                    <p>
                        Sign in to continue to Exam Shield
                    </p>

                </div>



                <!-- ALERT -->

                <?php if ($message !== ""): ?>

                    <div
                        class="login-alert <?php echo htmlspecialchars($messageType); ?>"
                    >

                        <i class="fa-solid fa-circle-exclamation"></i>

                        <span>
                            <?php
                            echo htmlspecialchars($message);
                            ?>
                        </span>

                    </div>

                <?php endif; ?>



                <!-- LOGIN FORM -->

                <form
                    method="POST"
                    action=""
                    class="login-form"
                    autocomplete="off"
                >


                    <!-- EMAIL -->

                    <div class="form-group">

                        <label for="email">

                            <i class="fa-solid fa-envelope"></i>

                            Email Address

                        </label>


                        <div class="input-box">

                            <i class="fa-solid fa-envelope input-icon"></i>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                value="<?php echo htmlspecialchars($email ?? ""); ?>"
                                required
                            >

                        </div>

                    </div>



                    <!-- PASSWORD -->

                    <div class="form-group">

                        <label for="password">

                            <i class="fa-solid fa-lock"></i>

                            Password

                        </label>


                        <div class="input-box">

                            <i class="fa-solid fa-lock input-icon"></i>


                            <input
                                type="password"
                                id="password"
                                name="password"
                                placeholder="Enter your password"
                                required
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                id="togglePassword"
                                aria-label="Show password"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                    </div>



                    <!-- ROLE -->

                    <div class="form-group">

                        <label for="role">

                            <i class="fa-solid fa-users"></i>

                            Login As

                        </label>


                        <div class="input-box select-box">

                            <i class="fa-solid fa-user-tag input-icon"></i>


                            <select
                                id="role"
                                name="role"
                                required
                            >

                                <option
                                    value=""
                                    disabled
                                    <?php
                                    echo empty($role)
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Select your role
                                </option>


                                <option
                                    value="admin"
                                    <?php
                                    echo ($role ?? "") === "admin"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Admin
                                </option>


                                <option
                                    value="teacher"
                                    <?php
                                    echo ($role ?? "") === "teacher"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Teacher
                                </option>


                                <option
                                    value="student"
                                    <?php
                                    echo ($role ?? "") === "student"
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Student
                                </option>

                            </select>


                            <i class="fa-solid fa-chevron-down select-arrow"></i>

                        </div>

                    </div>



                    <!-- OPTIONS -->

                    <div class="login-options">

                        <label class="remember-me">

                            <input
                                type="checkbox"
                                name="remember"
                            >

                            <span class="custom-checkbox"></span>

                            <span>
                                Remember me
                            </span>

                        </label>


                        <a
                            href="forgot-password.php"
                            class="forgot-link"
                        >
                            Forgot Password?
                        </a>

                    </div>



                    <!-- LOGIN BUTTON -->

                    <button
                        type="submit"
                        class="login-button"
                    >

                        <span>
                            Sign In
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                </form>



                <!-- REGISTER -->

                <div class="register-link">

                    <span>
                        Don't have an account?
                    </span>

                    <a href="register.php">
                        Create Account
                    </a>

                </div>



                <!-- SECURITY FOOTER -->

                <div class="login-security">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Protected by Exam Shield
                    </span>

                    <i class="fa-solid fa-circle-check"></i>

                </div>


            </div>


        </section>


    </main>



    <!-- =====================================================
         LOGIN JAVASCRIPT
    ====================================================== -->

    <script>

        document.addEventListener(
            "DOMContentLoaded",
            function () {

                const password =
                    document.getElementById(
                        "password"
                    );

                const toggle =
                    document.getElementById(
                        "togglePassword"
                    );


                if (
                    password &&
                    toggle
                ) {

                    toggle.addEventListener(
                        "click",
                        function () {

                            const icon =
                                toggle.querySelector(
                                    "i"
                                );


                            if (
                                password.type ===
                                "password"
                            ) {

                                password.type =
                                    "text";

                                icon.classList.remove(
                                    "fa-eye"
                                );

                                icon.classList.add(
                                    "fa-eye-slash"
                                );

                                toggle.setAttribute(
                                    "aria-label",
                                    "Hide password"
                                );

                            } else {

                                password.type =
                                    "password";

                                icon.classList.remove(
                                    "fa-eye-slash"
                                );

                                icon.classList.add(
                                    "fa-eye"
                                );

                                toggle.setAttribute(
                                    "aria-label",
                                    "Show password"
                                );
                            }

                        }
                    );

                }


                /* =========================================
                   INPUT FOCUS EFFECT
                ========================================== */

                const inputs =
                    document.querySelectorAll(
                        ".input-box input, .input-box select"
                    );


                inputs.forEach(
                    function (input) {

                        input.addEventListener(
                            "focus",
                            function () {

                                this
                                    .closest(
                                        ".input-box"
                                    )
                                    .classList
                                    .add(
                                        "focused"
                                    );
                            }
                        );


                        input.addEventListener(
                            "blur",
                            function () {

                                this
                                    .closest(
                                        ".input-box"
                                    )
                                    .classList
                                    .remove(
                                        "focused"
                                    );
                            }
                        );

                    }
                );


                /* =========================================
                   BUTTON LOADING
                ========================================== */

                const form =
                    document.querySelector(
                        ".login-form"
                    );

                const loginButton =
                    document.querySelector(
                        ".login-button"
                    );


                if (
                    form &&
                    loginButton
                ) {

                    form.addEventListener(
                        "submit",
                        function () {

                            if (
                                form.checkValidity()
                            ) {

                                loginButton.classList.add(
                                    "loading"
                                );

                                loginButton.querySelector(
                                    "span"
                                ).textContent =
                                    "Signing In...";

                            }

                        }
                    );
                }

            }
        );

    </script>

</body>

</html>