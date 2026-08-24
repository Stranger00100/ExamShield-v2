<?php

/* =========================================================
   EXAM SHIELD
   RESET PASSWORD PAGE
========================================================= */

session_start();

/* =========================================================
   CHECK OTP VERIFICATION
========================================================= */

if (
    !isset($_SESSION["password_reset"]) ||
    !is_array($_SESSION["password_reset"]) ||
    empty($_SESSION["password_reset"]["verified"]) ||
    $_SESSION["password_reset"]["verified"] !== true
) {
    header("Location: forgot-password.php");
    exit();
}


/* =========================================================
   GET EMAIL
========================================================= */

$email = $_SESSION["password_reset"]["email"] ?? "";

if ($email === "") {
    unset($_SESSION["password_reset"]);

    header("Location: forgot-password.php");
    exit();
}


/* =========================================================
   MASK EMAIL
========================================================= */

function maskEmail($email)
{
    $parts = explode("@", $email);

    if (count($parts) !== 2) {
        return $email;
    }

    $name = $parts[0];
    $domain = $parts[1];

    if (strlen($name) <= 2) {
        $maskedName = substr($name, 0, 1) . "***";
    } else {
        $maskedName =
            substr($name, 0, 2) .
            str_repeat("*", max(2, strlen($name) - 2));
    }

    return $maskedName . "@" . $domain;
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
        content="Create a new secure ExamShield password."
    >

    <title>
        Reset Password | ExamShield
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
         EXISTING FORGOT PASSWORD CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/forgot-password.css"
    >


    <!-- =====================================================
         RESET PASSWORD CSS
    ====================================================== -->

    <style>

        /* =================================================
           RESET CARD
        ================================================= */

        .reset-card {
            text-align: center;
        }


        .reset-email {
            margin-top: 8px;
            color: #64748b;
            font-size: 10px;
        }


        .reset-email strong {
            display: block;
            margin-top: 3px;
            color: #334155;
            font-size: 11px;
            font-weight: 700;
        }


        /* =================================================
           PASSWORD FIELD
        ================================================= */

        .password-wrapper {
            position: relative;
        }


        .password-wrapper input {
            padding-right: 45px !important;
        }


        .password-toggle {
            position: absolute;
            top: 50%;
            right: 14px;

            transform: translateY(-50%);

            border: none;
            background: transparent;

            color: #94a3b8;

            cursor: pointer;

            font-size: 13px;

            z-index: 5;

            transition:
                color 0.2s ease,
                transform 0.2s ease;
        }


        .password-toggle:hover {
            color: #2563eb;

            transform:
                translateY(-50%) scale(1.08);
        }


        /* =================================================
           PASSWORD STRENGTH
        ================================================= */

        .password-strength {
            margin-top: 10px;
            text-align: left;
        }


        .strength-bar {
            display: flex;
            gap: 4px;
            height: 4px;
        }


        .strength-segment {
            flex: 1;

            border-radius: 10px;

            background: #e2e8f0;

            transition:
                background 0.25s ease;
        }


        .strength-label {
            display: flex;
            justify-content: space-between;

            margin-top: 5px;

            color: #94a3b8;

            font-size: 8px;
        }


        #strengthText {
            font-weight: 700;
        }


        /* =================================================
           PASSWORD REQUIREMENTS
        ================================================= */

        .password-requirements {
            margin-top: 15px;

            padding: 12px;

            border-radius: 10px;

            background: #f8fafc;

            text-align: left;
        }


        .requirements-title {
            margin-bottom: 8px;

            color: #475569;

            font-size: 9px;

            font-weight: 700;
        }


        .requirement {
            display: flex;
            align-items: center;
            gap: 7px;

            margin: 5px 0;

            color: #94a3b8;

            font-size: 8px;

            transition:
                color 0.2s ease;
        }


        .requirement i {
            width: 12px;

            font-size: 8px;
        }


        .requirement.valid {
            color: #16a34a;
        }


        .requirement.valid i {
            color: #16a34a;
        }


        /* =================================================
           RESET BUTTON
        ================================================= */

        .reset-password-button {
            width: 100%;

            height: 52px;

            margin-top: 20px;

            border: none;

            border-radius: 12px;

            color: #ffffff;

            background:
                linear-gradient(
                    135deg,
                    #2563eb,
                    #4f46e5,
                    #7c3aed
                );

            box-shadow:
                0 13px 30px
                rgba(37, 99, 235, 0.25);

            font-family: inherit;

            font-size: 11px;

            font-weight: 700;

            cursor: pointer;

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                opacity 0.25s ease;
        }


        .reset-password-button:hover {
            transform:
                translateY(-2px);

            box-shadow:
                0 17px 38px
                rgba(37, 99, 235, 0.34);
        }


        .reset-password-button:disabled {
            cursor: not-allowed;
            opacity: 0.55;
            transform: none;
        }


        /* =================================================
           FORM GROUP
        ================================================= */

        .reset-form-group {
            margin-top: 18px;
            text-align: left;
        }


        .reset-form-group:first-of-type {
            margin-top: 24px;
        }


        .reset-form-label {
            display: block;

            margin-bottom: 7px;

            color: #475569;

            font-size: 9px;

            font-weight: 600;
        }


        .reset-form-label i {
            margin-right: 5px;
            color: #2563eb;
        }


        .reset-input {
            width: 100%;

            height: 47px;

            padding: 0 14px;

            border: 1px solid #e2e8f0;

            border-radius: 10px;

            outline: none;

            background: #f8fafc;

            color: #0f172a;

            font-family: inherit;

            font-size: 10px;

            transition:
                border 0.25s ease,
                box-shadow 0.25s ease,
                background 0.25s ease;
        }


        .reset-input:focus {
            border-color: #2563eb;

            background: #ffffff;

            box-shadow:
                0 0 0 4px
                rgba(37, 99, 235, 0.08);
        }


        .reset-input.input-error {
            border-color: #dc2626;
        }


        .reset-input.input-success {
            border-color: #16a34a;
        }


        /* =================================================
           SUCCESS MESSAGE
        ================================================= */

        .reset-success {
            padding: 12px;

            border-radius: 10px;

            background: #f0fdf4;

            color: #15803d;

            font-size: 9px;

            line-height: 1.6;
        }


        /* =================================================
           SECURITY FOOTER
        ================================================= */

        .reset-security {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 7px;

            margin-top: 18px;

            color: #94a3b8;

            font-size: 8px;
        }


        .reset-security i {
            color: #16a34a;
        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 600px) {

            .reset-card {
                padding-left: 22px;
                padding-right: 22px;
            }

        }

    </style>

</head>


<body>


    <!-- =====================================================
         BACKGROUND
    ====================================================== -->

    <div class="background-animation">

        <span class="floating-shape shape-one"></span>

        <span class="floating-shape shape-two"></span>

        <span class="floating-shape shape-three"></span>

        <span class="floating-shape shape-four"></span>

        <span class="floating-shape shape-five"></span>

    </div>



    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="forgot-page">


        <!-- =================================================
             LEFT INFORMATION
        ================================================== -->

        <section class="forgot-info">


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

                    <i class="fa-solid fa-shield-halved"></i>

                    ACCOUNT SECURITY

                </span>


                <h1>

                    Create a
                    <span>New Password.</span>

                </h1>


                <p>

                    Your identity has been verified.
                    Create a strong new password to
                    protect your ExamShield account.

                </p>



                <!-- SECURITY STEPS -->

                <div class="recovery-steps">


                    <div class="recovery-step">

                        <div class="step-number">

                            <i class="fa-solid fa-check"></i>

                        </div>


                        <div>

                            <h3>
                                Email Verified
                            </h3>

                            <p>
                                Your recovery request was
                                successfully initiated.
                            </p>

                        </div>

                    </div>



                    <div class="recovery-step">

                        <div class="step-number">

                            <i class="fa-solid fa-check"></i>

                        </div>


                        <div>

                            <h3>
                                Identity Verified
                            </h3>

                            <p>
                                Your verification code was
                                successfully confirmed.
                            </p>

                        </div>

                    </div>



                    <div class="recovery-step">

                        <div class="step-number">
                            03
                        </div>


                        <div>

                            <h3>
                                New Password
                            </h3>

                            <p>
                                Set a strong password for
                                your account.
                            </p>

                        </div>

                    </div>


                </div>


            </div>



            <!-- FOOTER -->

            <div class="forgot-info-footer">

                <span>

                    <i class="fa-solid fa-lock"></i>

                    Secure Password

                </span>


                <span>

                    <i class="fa-solid fa-shield-halved"></i>

                    Account Protected

                </span>


                <span>

                    <i class="fa-solid fa-circle-check"></i>

                    Identity Verified

                </span>

            </div>


        </section>



        <!-- =================================================
             RESET CARD
        ================================================== -->

        <section class="forgot-container">


            <div class="forgot-card reset-card">


                <!-- HEADER -->

                <div class="forgot-header">


                    <div class="forgot-icon">

                        <i class="fa-solid fa-key"></i>

                    </div>


                    <h2>
                        Reset Password
                    </h2>


                    <p>
                        Create your new password
                    </p>


                    <div class="reset-email">

                        Password reset for

                        <strong>
                            <?php
                            echo htmlspecialchars(
                                maskEmail($email)
                            );
                            ?>
                        </strong>

                    </div>

                </div>



                <!-- =================================================
                     ALERT
                ================================================== -->

                <div
                    id="resetAlert"
                    class="forgot-alert"
                    style="display:none;"
                >

                    <i
                        id="resetAlertIcon"
                        class="fa-solid"
                    ></i>


                    <span
                        id="resetAlertMessage"
                    ></span>

                </div>



                <!-- =================================================
                     RESET FORM
                ================================================== -->

                <form
                    id="resetPasswordForm"
                    autocomplete="off"
                >


                    <!-- Hidden email -->

                    <input
                        type="hidden"
                        id="resetEmail"
                        value="<?php
                            echo htmlspecialchars($email);
                        ?>"
                    >



                    <!-- =================================================
                         NEW PASSWORD
                    ================================================== -->

                    <div class="reset-form-group">

                        <label
                            class="reset-form-label"
                            for="newPassword"
                        >

                            <i class="fa-solid fa-lock"></i>

                            New Password

                        </label>


                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="newPassword"
                                class="reset-input"
                                placeholder="Enter your new password"
                                autocomplete="new-password"
                                maxlength="72"
                                required
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                data-target="newPassword"
                                aria-label="Show password"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>


                        <!-- PASSWORD STRENGTH -->

                        <div
                            class="password-strength"
                            id="passwordStrength"
                        >

                            <div class="strength-bar">

                                <span class="strength-segment"></span>

                                <span class="strength-segment"></span>

                                <span class="strength-segment"></span>

                                <span class="strength-segment"></span>

                            </div>


                            <div class="strength-label">

                                <span>
                                    Password strength
                                </span>

                                <span id="strengthText">
                                    None
                                </span>

                            </div>

                        </div>


                    </div>



                    <!-- =================================================
                         CONFIRM PASSWORD
                    ================================================== -->

                    <div class="reset-form-group">

                        <label
                            class="reset-form-label"
                            for="confirmPassword"
                        >

                            <i class="fa-solid fa-lock"></i>

                            Confirm Password

                        </label>


                        <div class="password-wrapper">

                            <input
                                type="password"
                                id="confirmPassword"
                                class="reset-input"
                                placeholder="Confirm your new password"
                                autocomplete="new-password"
                                maxlength="72"
                                required
                            >


                            <button
                                type="button"
                                class="password-toggle"
                                data-target="confirmPassword"
                                aria-label="Show password"
                            >

                                <i class="fa-solid fa-eye"></i>

                            </button>

                        </div>

                    </div>



                    <!-- =================================================
                         PASSWORD REQUIREMENTS
                    ================================================== -->

                    <div class="password-requirements">

                        <div class="requirements-title">

                            Password must contain:

                        </div>


                        <div
                            class="requirement"
                            id="requirementLength"
                        >

                            <i class="fa-solid fa-circle-xmark"></i>

                            At least 8 characters

                        </div>


                        <div
                            class="requirement"
                            id="requirementUpper"
                        >

                            <i class="fa-solid fa-circle-xmark"></i>

                            At least one uppercase letter

                        </div>


                        <div
                            class="requirement"
                            id="requirementLower"
                        >

                            <i class="fa-solid fa-circle-xmark"></i>

                            At least one lowercase letter

                        </div>


                        <div
                            class="requirement"
                            id="requirementNumber"
                        >

                            <i class="fa-solid fa-circle-xmark"></i>

                            At least one number

                        </div>


                        <div
                            class="requirement"
                            id="requirementSpecial"
                        >

                            <i class="fa-solid fa-circle-xmark"></i>

                            At least one special character

                        </div>

                    </div>



                    <!-- =================================================
                         BUTTON
                    ================================================== -->

                    <button
                        type="submit"
                        class="reset-password-button"
                        id="resetPasswordButton"
                    >

                        <span>
                            Update Password
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                </form>



                <!-- =================================================
                     SECURITY
                ================================================== -->

                <div class="reset-security">

                    <i class="fa-solid fa-lock"></i>

                    <span>
                        Your password is securely encrypted
                    </span>

                    <i class="fa-solid fa-circle-check"></i>

                </div>


            </div>

        </section>

    </main>



    <!-- =====================================================
         RESET PASSWORD JAVASCRIPT
    ====================================================== -->

    <script src="assets/js/reset-password.js"></script>

</body>

</html>