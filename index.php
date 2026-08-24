<?php
session_start();

/*
|--------------------------------------------------------------------------
| Exam Shield - Main Homepage
|--------------------------------------------------------------------------
*/

// Check whether user is logged in
$isLoggedIn = isset($_SESSION['user_id']);
$username   = $_SESSION['username'] ?? '';
$role       = $_SESSION['role'] ?? '';


// Decide dashboard URL according to role
$dashboardUrl = 'login.php';

if ($isLoggedIn) {

    if ($role === 'admin') {
        $dashboardUrl = 'admin/dashboard.php';

    } elseif ($role === 'teacher') {
        $dashboardUrl = 'teacher/dashboard.php';

    } elseif ($role === 'student') {
        $dashboardUrl = 'student/dashboard.php';
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
        content="Exam Shield - Secure online coding examination, automated evaluation, activity monitoring and performance analytics."
    >

    <meta
        name="keywords"
        content="coding exam, online examination, programming exam, code evaluation, exam monitoring, Exam Shield"
    >

    <meta
        name="author"
        content="Exam Shield"
    >

    <title>
        Exam Shield | Secure Coding Examination System
    </title>


    <!-- Google Font -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
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


    <!-- Homepage CSS -->

    <link
        rel="stylesheet"
        href="assets/css/home.css"
    >

    <link
        rel="stylesheet"
        href="assets/css/animation.css"
    >
<link rel="stylesheet" href="assets/css/home.css">
<link rel="stylesheet" href="assets/css/animation.css">

</head>


<body>


<!-- =========================================================
     BACKGROUND ANIMATION
========================================================= -->

<div class="background-animation">

    <span class="floating-circle circle-1"></span>

    <span class="floating-circle circle-2"></span>

    <span class="floating-circle circle-3"></span>

    <span class="floating-circle circle-4"></span>

</div>


<!-- =========================================================
     NAVIGATION
========================================================= -->

<header class="main-header" id="header">

    <div class="container nav-container">


        <!-- LOGO -->

        <a href="index.php" class="logo">

            <div class="logo-icon">

                <i class="fa-solid fa-shield-halved"></i>

            </div>

            <div class="logo-text">

                <span class="logo-main">
                    Exam
                </span>

                <span class="logo-highlight">
                    Shield
                </span>

            </div>

        </a>


        <!-- DESKTOP NAVIGATION -->

        <nav class="main-nav" id="mainNav">

            <a
                href="#home"
                class="nav-link active"
            >
                Home
            </a>

            <a
                href="#features"
                class="nav-link"
            >
                Features
            </a>

            <a
                href="#how-it-works"
                class="nav-link"
            >
                How It Works
            </a>

            <a
                href="#security"
                class="nav-link"
            >
                Security
            </a>

            <a
                href="#analytics"
                class="nav-link"
            >
                Analytics
            </a>

            <a
                href="#about"
                class="nav-link"
            >
                About
            </a>

        </nav>


        <!-- NAVIGATION BUTTONS -->

        <div class="nav-actions">

            <?php if ($isLoggedIn): ?>

                <a
                    href="<?php echo htmlspecialchars($dashboardUrl); ?>"
                    class="btn btn-dashboard"
                >

                    <i class="fa-solid fa-gauge-high"></i>

                    Dashboard

                </a>

                <a
                    href="logout.php"
                    class="btn btn-login"
                >

                    <i class="fa-solid fa-right-from-bracket"></i>

                    Logout

                </a>

            <?php else: ?>

                <a
                    href="login.php"
                    class="btn btn-login"
                >

                    <i class="fa-solid fa-right-to-bracket"></i>

                    Login

                </a>

                <a
                    href="register.php"
                    class="btn btn-register"
                >

                    Get Started

                </a>

            <?php endif; ?>

        </div>


        <!-- MOBILE MENU BUTTON -->

        <button
            type="button"
            class="mobile-menu-btn"
            id="mobileMenuBtn"
            aria-label="Open navigation menu"
        >

            <i class="fa-solid fa-bars"></i>

        </button>

    </div>

</header>


<!-- =========================================================
     HERO SECTION
========================================================= -->

<section
    class="hero-section"
    id="home"
>

    <div class="container hero-container">


        <!-- HERO CONTENT -->

        <div class="hero-content">


            <!-- BADGE -->

            <div class="hero-badge">

                <span class="badge-dot"></span>

                Smart • Secure • Automated

            </div>


            <!-- TITLE -->

            <h1>

                Conduct Coding Exams

                <span class="gradient-text">
                    Securely.
                </span>

            </h1>


            <!-- DESCRIPTION -->

            <p class="hero-description">

                Exam Shield is a secure online coding examination
                platform designed to conduct programming exams,
                automatically evaluate code, monitor student
                activities and generate accurate results.

            </p>


            <!-- BUTTONS -->

            <div class="hero-buttons">

                <a
                    href="login.php"
                    class="btn btn-primary"
                >

                    Start Examination

                    <i class="fa-solid fa-arrow-right"></i>

                </a>


                <a
                    href="#how-it-works"
                    class="btn btn-secondary"
                >

                    <i class="fa-solid fa-circle-play"></i>

                    Explore Platform

                </a>

            </div>


            <!-- TRUST ITEMS -->

            <div class="hero-trust">

                <div class="trust-item">

                    <i class="fa-solid fa-shield-halved"></i>

                    <span>
                        Secure Exams
                    </span>

                </div>


                <div class="trust-item">

                    <i class="fa-solid fa-code"></i>

                    <span>
                        Auto Evaluation
                    </span>

                </div>


                <div class="trust-item">

                    <i class="fa-solid fa-chart-line"></i>

                    <span>
                        Smart Analytics
                    </span>

                </div>

            </div>

        </div>


        <!-- HERO VISUAL -->

        <div class="hero-visual">

            <div class="hero-card">


                <!-- CARD HEADER -->

                <div class="hero-card-header">

                    <div class="window-controls">

                        <span></span>
                        <span></span>
                        <span></span>

                    </div>

                    <span>
                        coding_exam.cpp
                    </span>

                    <i class="fa-solid fa-code"></i>

                </div>


                <!-- CODE -->

                <div class="code-window">

                    <div class="code-line">

                        <span class="line-number">
                            01
                        </span>

                        <span class="code-keyword">
                            #include
                        </span>

                        <span>
                            &lt;iostream&gt;
                        </span>

                    </div>


                    <div class="code-line">

                        <span class="line-number">
                            02
                        </span>

                        <span class="code-keyword">
                            using
                        </span>

                        <span>
                            namespace std;
                        </span>

                    </div>


                    <div class="code-line">

                        <span class="line-number">
                            03
                        </span>

                        <span class="code-keyword">
                            int
                        </span>

                        <span>
                            main() {
                        </span>

                    </div>


                    <div class="code-line">

                        <span class="line-number">
                            04
                        </span>

                        <span class="code-function">
                            cout
                        </span>

                        <span>
                            &lt;&lt; "Exam Shield";
                        </span>

                    </div>


                    <div class="code-line">

                        <span class="line-number">
                            05
                        </span>

                        <span>
                            return 0;
                        </span>

                    </div>


                    <div class="code-line">

                        <span class="line-number">
                            06
                        </span>

                        <span>
                            }
                        </span>

                    </div>

                </div>


                <!-- RESULT -->

                <div class="code-result">

                    <div class="result-status">

                        <i class="fa-solid fa-circle-check"></i>

                        <span>
                            Code Evaluated
                        </span>

                    </div>

                    <strong>
                        100%
                    </strong>

                </div>

            </div>


            <!-- FLOATING SECURITY CARD -->

            <div class="floating-card security-floating">

                <div class="floating-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>

                <div>

                    <strong>
                        Exam Protected
                    </strong>

                    <span>
                        Monitoring Active
                    </span>

                </div>

                <i class="fa-solid fa-circle-check success-icon"></i>

            </div>


            <!-- FLOATING RESULT CARD -->

            <div class="floating-card result-floating">

                <div class="floating-icon">

                    <i class="fa-solid fa-chart-simple"></i>

                </div>

                <div>

                    <strong>
                        Performance
                    </strong>

                    <span>
                        92% Average
                    </span>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     FEATURES SECTION
========================================================= -->

<section
    class="features-section"
    id="features"
>

    <div class="container">


        <div class="section-heading">

            <span class="section-label">
                PLATFORM FEATURES
            </span>

            <h2>

                Everything You Need for a

                <span class="gradient-text">
                    Secure Coding Exam
                </span>

            </h2>

            <p>

                A complete examination platform combining
                coding evaluation, monitoring, security and
                performance analysis.

            </p>

        </div>


        <div class="features-grid">


            <div class="feature-card">

                <div class="feature-icon">

                    <i class="fa-solid fa-lock"></i>

                </div>

                <h3>
                    Secure Student Login
                </h3>

                <p>

                    Secure authentication and controlled
                    access to examination resources.

                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">

                    <i class="fa-solid fa-code"></i>

                </div>

                <h3>
                    Online Coding
                </h3>

                <p>

                    Write and submit programming solutions
                    directly through the browser.

                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">

                    <i class="fa-solid fa-microchip"></i>

                </div>

                <h3>
                    Automatic Evaluation
                </h3>

                <p>

                    Automatically compile programs and
                    evaluate solutions using test cases.

                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">

                    <i class="fa-solid fa-eye"></i>

                </div>

                <h3>
                    Live Monitoring
                </h3>

                <p>

                    Monitor student activity throughout
                    the examination.

                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">

                    <i class="fa-solid fa-user-shield"></i>

                </div>

                <h3>
                    Anti-Cheating
                </h3>

                <p>

                    Detect suspicious activities such as
                    tab switching and copy-paste attempts.

                </p>

            </div>


            <div class="feature-card">

                <div class="feature-icon">

                    <i class="fa-solid fa-chart-line"></i>

                </div>

                <h3>
                    Performance Analytics
                </h3>

                <p>

                    Analyze student performance and generate
                    detailed examination reports.

                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     HOW IT WORKS
========================================================= -->

<section
    class="how-section"
    id="how-it-works"
>

    <div class="container">


        <div class="section-heading">

            <span class="section-label">
                HOW IT WORKS
            </span>

            <h2>
                Simple Examination
                <span class="gradient-text">
                    Workflow
                </span>
            </h2>

            <p>

                From secure login to automatic evaluation,
                Exam Shield manages the complete examination
                workflow.

            </p>

        </div>


        <div class="workflow">


            <div class="workflow-step">

                <div class="step-number">
                    01
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-right-to-bracket"></i>

                </div>

                <h3>
                    Secure Login
                </h3>

                <p>
                    Student securely accesses the examination.
                </p>

            </div>


            <div class="workflow-line"></div>


            <div class="workflow-step">

                <div class="step-number">
                    02
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-file-code"></i>

                </div>

                <h3>
                    Join Exam
                </h3>

                <p>
                    Student selects and starts an available exam.
                </p>

            </div>


            <div class="workflow-line"></div>


            <div class="workflow-step">

                <div class="step-number">
                    03
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-laptop-code"></i>

                </div>

                <h3>
                    Write Code
                </h3>

                <p>
                    Student solves programming questions online.
                </p>

            </div>


            <div class="workflow-line"></div>


            <div class="workflow-step">

                <div class="step-number">
                    04
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-gears"></i>

                </div>

                <h3>
                    Auto Evaluation
                </h3>

                <p>
                    Code is compiled and tested automatically.
                </p>

            </div>


            <div class="workflow-line"></div>


            <div class="workflow-step">

                <div class="step-number">
                    05
                </div>

                <div class="step-icon">

                    <i class="fa-solid fa-square-poll-vertical"></i>

                </div>

                <h3>
                    Instant Result
                </h3>

                <p>
                    Results and performance information are generated.
                </p>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     SECURITY SECTION
========================================================= -->

<section
    class="security-section"
    id="security"
>

    <div class="container security-container">


        <div class="security-content">

            <span class="section-label">
                EXAM SECURITY
            </span>

            <h2>

                Reduce Cheating.
                Improve

                <span class="gradient-text">
                    Exam Integrity.
                </span>

            </h2>

            <p>

                Exam Shield combines examination controls,
                activity monitoring and automated evaluation
                to support fair and transparent coding
                examinations.

            </p>


            <div class="security-list">


                <div class="security-item">

                    <div class="security-check">

                        <i class="fa-solid fa-check"></i>

                    </div>

                    <div>

                        <h4>
                            Activity Monitoring
                        </h4>

                        <p>
                            Track suspicious examination activities.
                        </p>

                    </div>

                </div>


                <div class="security-item">

                    <div class="security-check">

                        <i class="fa-solid fa-check"></i>

                    </div>

                    <div>

                        <h4>
                            Automatic Evaluation
                        </h4>

                        <p>
                            Evaluate programs using predefined test cases.
                        </p>

                    </div>

                </div>


                <div class="security-item">

                    <div class="security-check">

                        <i class="fa-solid fa-check"></i>

                    </div>

                    <div>

                        <h4>
                            Transparent Results
                        </h4>

                        <p>
                            Generate consistent examination results.
                        </p>

                    </div>

                </div>

            </div>

        </div>


        <div class="security-visual">

            <div class="security-dashboard-card">

                <div class="security-card-top">

                    <span>
                        Security Monitor
                    </span>

                    <span class="live-status">
                        <i class="fa-solid fa-circle"></i>
                        LIVE
                    </span>

                </div>


                <div class="monitor-status">

                    <div>

                        <span>
                            Exam Status
                        </span>

                        <strong>
                            Active
                        </strong>

                    </div>

                    <i class="fa-solid fa-circle-check"></i>

                </div>


                <div class="monitor-status">

                    <div>

                        <span>
                            Monitoring
                        </span>

                        <strong>
                            Active
                        </strong>

                    </div>

                    <i class="fa-solid fa-eye"></i>

                </div>


                <div class="monitor-status warning">

                    <div>

                        <span>
                            Suspicious Events
                        </span>

                        <strong>
                            02
                        </strong>

                    </div>

                    <i class="fa-solid fa-triangle-exclamation"></i>

                </div>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     ANALYTICS SECTION
========================================================= -->

<section
    class="analytics-section"
    id="analytics"
>

    <div class="container">


        <div class="section-heading">

            <span class="section-label">
                PERFORMANCE ANALYTICS
            </span>

            <h2>

                Turn Examination Data Into

                <span class="gradient-text">
                    Useful Insights
                </span>

            </h2>

            <p>

                Analyze coding performance, test-case results
                and examination activity through detailed
                reports.

            </p>

        </div>


        <div class="analytics-grid">


            <div class="analytics-card large-card">

                <div class="analytics-header">

                    <div>

                        <span>
                            Student Performance
                        </span>

                        <h3>
                            92%
                        </h3>

                    </div>

                    <i class="fa-solid fa-chart-line"></i>

                </div>


                <div class="fake-chart">

                    <div class="chart-bar bar-1"></div>

                    <div class="chart-bar bar-2"></div>

                    <div class="chart-bar bar-3"></div>

                    <div class="chart-bar bar-4"></div>

                    <div class="chart-bar bar-5"></div>

                    <div class="chart-bar bar-6"></div>

                    <div class="chart-bar bar-7"></div>

                </div>

            </div>


            <div class="analytics-card">

                <div class="analytics-icon">

                    <i class="fa-solid fa-code"></i>

                </div>

                <span>
                    Test Cases Passed
                </span>

                <h3>
                    94%
                </h3>

            </div>


            <div class="analytics-card">

                <div class="analytics-icon">

                    <i class="fa-solid fa-clock"></i>

                </div>

                <span>
                    Average Time
                </span>

                <h3>
                    42 min
                </h3>

            </div>


            <div class="analytics-card">

                <div class="analytics-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <span>
                    Pass Rate
                </span>

                <h3>
                    88%
                </h3>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     ADVANCED FEATURES
========================================================= -->

<section
    class="advanced-section"
    id="advanced"
>

    <div class="container">


        <div class="section-heading">

            <span class="section-label">
                FUTURE-READY SECURITY
            </span>

            <h2>

                Advanced Examination
                Protection

            </h2>

            <p>

                Additional capabilities can be integrated
                into the platform as the system evolves.

            </p>

        </div>


        <div class="advanced-grid">


            <div class="advanced-card">

                <i class="fa-solid fa-robot"></i>

                <h3>
                    AI-Assisted Plagiarism
                </h3>

                <p>
                    Analyze code similarity and identify
                    potentially duplicated solutions.
                </p>

                <span>
                    Advanced Module
                </span>

            </div>


            <div class="advanced-card">

                <i class="fa-solid fa-video"></i>

                <h3>
                    Webcam Proctoring
                </h3>

                <p>
                    Support webcam-based examination
                    monitoring with appropriate consent.
                </p>

                <span>
                    Advanced Module
                </span>

            </div>


            <div class="advanced-card">

                <i class="fa-solid fa-face-smile"></i>

                <h3>
                    Face Verification
                </h3>

                <p>
                    Verify student identity during secure
                    examination workflows.
                </p>

                <span>
                    Advanced Module
                </span>

            </div>


            <div class="advanced-card">

                <i class="fa-solid fa-microphone"></i>

                <h3>
                    Voice Monitoring
                </h3>

                <p>
                    Detect voice activity as an additional
                    examination signal for review.
                </p>

                <span>
                    Advanced Module
                </span>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     ABOUT SECTION
========================================================= -->

<section
    class="about-section"
    id="about"
>

    <div class="container about-container">


        <div class="about-content">

            <span class="section-label">
                ABOUT EXAM SHIELD
            </span>

            <h2>

                A Smarter Way to Conduct

                <span class="gradient-text">
                    Coding Examinations
                </span>

            </h2>

            <p>

                Exam Shield is designed to help educational
                institutions conduct programming examinations
                through a secure online environment.

            </p>

            <p>

                The platform combines an online coding editor,
                automated code evaluation, activity monitoring
                and performance reporting.

            </p>


            <a
                href="login.php"
                class="btn btn-primary"
            >

                Get Started

                <i class="fa-solid fa-arrow-right"></i>

            </a>

        </div>


        <div class="about-stats">


            <div class="about-stat">

                <i class="fa-solid fa-code"></i>

                <strong>
                    Coding
                </strong>

                <span>
                    Online Examination
                </span>

            </div>


            <div class="about-stat">

                <i class="fa-solid fa-gears"></i>

                <strong>
                    Automated
                </strong>

                <span>
                    Code Evaluation
                </span>

            </div>


            <div class="about-stat">

                <i class="fa-solid fa-shield"></i>

                <strong>
                    Secure
                </strong>

                <span>
                    Exam Monitoring
                </span>

            </div>


            <div class="about-stat">

                <i class="fa-solid fa-chart-pie"></i>

                <strong>
                    Analytics
                </strong>

                <span>
                    Performance Reports
                </span>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     CTA SECTION
========================================================= -->

<section class="cta-section">

    <div class="container">

        <div class="cta-card">

            <div class="cta-content">

                <span class="section-label">
                    READY TO START?
                </span>

                <h2>
                    Conduct Your Next Coding Exam
                    With Exam Shield
                </h2>

                <p>
                    Secure examination, automated evaluation
                    and meaningful performance insights.
                </p>

            </div>


            <div class="cta-buttons">

                <a
                    href="login.php"
                    class="btn btn-primary"
                >

                    Login

                    <i class="fa-solid fa-arrow-right"></i>

                </a>


                <a
                    href="register.php"
                    class="btn btn-secondary"
                >

                    Create Account

                </a>

            </div>

        </div>

    </div>

</section>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="main-footer">

    <div class="container footer-container">


        <div class="footer-brand">

            <a
                href="index.php"
                class="logo"
            >

                <div class="logo-icon">

                    <i class="fa-solid fa-shield-halved"></i>

                </div>

                <div class="logo-text">

                    <span class="logo-main">
                        Exam
                    </span>

                    <span class="logo-highlight">
                        Shield
                    </span>

                </div>

            </a>


            <p>

                Secure coding examinations with
                automated evaluation and intelligent
                activity monitoring.

            </p>

        </div>


        <div class="footer-links">

            <h4>
                Platform
            </h4>

            <a href="#features">
                Features
            </a>

            <a href="#how-it-works">
                How It Works
            </a>

            <a href="#security">
                Security
            </a>

            <a href="#analytics">
                Analytics
            </a>

        </div>


        <div class="footer-links">

            <h4>
                Account
            </h4>

            <a href="login.php">
                Login
            </a>

            <a href="register.php">
                Register
            </a>

            <a href="forgot-password.php">
                Forgot Password
            </a>

        </div>


        <div class="footer-links">

            <h4>
                System
            </h4>

            <a href="#about">
                About
            </a>

            <a href="#advanced">
                Advanced Features
            </a>

        </div>

    </div>


    <div class="footer-bottom">

        <div class="container">

            <p>
                &copy;
                <?php echo date('Y'); ?>
                Exam Shield. All Rights Reserved.
            </p>

            <p>
                Secure Coding Examination System
            </p>

        </div>

    </div>

</footer>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script src="assets/js/main.js"></script>
<script src="assets/js/home.js"></script>

</body>

</html>