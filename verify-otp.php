<?php

session_start();

/* =========================================================
   EXAM SHIELD
   OTP VERIFICATION PAGE
========================================================= */

/*
 * The email and OTP are stored in the session by
 * api/send_otp.php.
 */

$resetData = $_SESSION["password_reset"] ?? null;


/* =========================================================
   CHECK RESET SESSION
========================================================= */

if (!$resetData || empty($resetData["email"])) {

    header("Location: forgot-password.php");
    exit();

}


$email = $resetData["email"];


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

        $maskedName =
            substr($name, 0, 1) . "***";

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
        content="Verify your ExamShield password reset OTP."
    >

    <title>
        Verify OTP | ExamShield
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
         OTP CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="assets/css/forgot-password.css"
    >

    <style>

        /* =================================================
           OTP PAGE
        ================================================= */

        .otp-card {

            text-align: center;

        }


        .otp-email {

            margin-top: 8px;

            color: #64748b;

            font-size: 10px;

            line-height: 1.6;

        }


        .otp-email strong {

            display: block;

            margin-top: 3px;

            color: #334155;

            font-size: 11px;

            font-weight: 700;

        }


        /* =================================================
           OTP BOXES
        ================================================= */

        .otp-input-container {

            display: flex;

            justify-content: center;

            gap: 9px;

            margin-top: 27px;

        }


        .otp-input {

            width: 50px;

            height: 56px;

            border: 1px solid #e2e8f0;

            border-radius: 12px;

            outline: none;

            background: rgba(248, 250, 252, 0.8);

            color: #0f172a;

            text-align: center;

            font-size: 20px;

            font-weight: 700;

            transition:
                border 0.25s ease,
                box-shadow 0.25s ease,
                transform 0.25s ease;

        }


        .otp-input:focus {

            border-color: #2563eb;

            background: #ffffff;

            box-shadow:
                0 0 0 4px
                rgba(37, 99, 235, 0.08);

            transform:
                translateY(-2px);

        }


        .otp-input.filled {

            border-color: #2563eb;

            background: #ffffff;

        }


        .otp-input.error {

            border-color: #dc2626;

            box-shadow:
                0 0 0 4px
                rgba(220, 38, 38, 0.07);

            animation:
                otpShake 0.35s ease;

        }


        @keyframes otpShake {

            0%,
            100% {

                transform:
                    translateX(0);

            }

            25% {

                transform:
                    translateX(-5px);

            }

            75% {

                transform:
                    translateX(5px);

            }

        }


        /* =================================================
           TIMER
        ================================================= */

        .otp-timer {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 7px;

            margin-top: 18px;

            color: #64748b;

            font-size: 9px;

        }


        .otp-timer i {

            color: #2563eb;

        }


        #countdown {

            color: #2563eb;

            font-weight: 700;

        }


        #countdown.expired {

            color: #dc2626;

        }


        /* =================================================
           VERIFY BUTTON
        ================================================= */

        .verify-otp-button {

            width: 100%;

            height: 52px;

            margin-top: 22px;

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

            transition:
                transform 0.25s ease,
                box-shadow 0.25s ease,
                opacity 0.25s ease;

        }


        .verify-otp-button:hover {

            transform:
                translateY(-2px);

            box-shadow:
                0 17px 38px
                rgba(37, 99, 235, 0.34);

        }


        .verify-otp-button:disabled {

            cursor: not-allowed;

            opacity: 0.55;

            transform: none;

        }


        /* =================================================
           RESEND
        ================================================= */

        .resend-section {

            margin-top: 19px;

            color: #94a3b8;

            font-size: 9px;

        }


        .resend-button {

            margin-left: 4px;

            padding: 0;

            border: none;

            background: transparent;

            color: #2563eb;

            font-family: inherit;

            font-size: 9px;

            font-weight: 700;

        }


        .resend-button:hover {

            text-decoration:
                underline;

        }


        .resend-button:disabled {

            cursor: not-allowed;

            color: #94a3b8;

            text-decoration: none;

        }


        /* =================================================
           CHANGE EMAIL
        ================================================= */

        .change-email {

            margin-top: 15px;

        }


        .change-email a {

            display: inline-flex;

            align-items: center;

            gap: 6px;

            color: #64748b;

            font-size: 9px;

            font-weight: 600;

            transition:
                color 0.25s ease;

        }


        .change-email a:hover {

            color: #2563eb;

        }


        /* =================================================
           MOBILE
        ================================================= */

        @media (max-width: 600px) {

            .otp-input-container {

                gap: 6px;

            }


            .otp-input {

                width: 43px;

                height: 52px;

                font-size: 18px;

            }

        }


        @media (max-width: 380px) {

            .otp-input-container {

                gap: 4px;

            }


            .otp-input {

                width: 39px;

                height: 48px;

                font-size: 16px;

                border-radius: 10px;

            }

        }

    </style>

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
         MAIN
    ====================================================== -->

    <main class="forgot-page">


        <!-- =================================================
             LEFT SIDE
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

                    SECURE VERIFICATION

                </span>


                <h1>

                    Verify Your
                    <span>Identity.</span>

                </h1>


                <p>

                    We've sent a verification code to your
                    registered email address. Enter the code
                    to continue recovering your ExamShield
                    account.

                </p>



                <!-- STEPS -->

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
                                Your recovery request has
                                been initiated.
                            </p>

                        </div>

                    </div>



                    <div class="recovery-step">

                        <div class="step-number">
                            02
                        </div>


                        <div>

                            <h3>
                                Verify OTP
                            </h3>

                            <p>
                                Enter the six-digit code
                                sent to your email.
                            </p>

                        </div>

                    </div>



                    <div class="recovery-step">

                        <div class="step-number">
                            03
                        </div>


                        <div>

                            <h3>
                                Reset Password
                            </h3>

                            <p>
                                Create a new secure password.
                            </p>

                        </div>

                    </div>


                </div>


            </div>



            <!-- FOOTER -->

            <div class="forgot-info-footer">

                <span>

                    <i class="fa-solid fa-lock"></i>

                    Secure Verification

                </span>


                <span>

                    <i class="fa-solid fa-clock"></i>

                    OTP Expires Quickly

                </span>


                <span>

                    <i class="fa-solid fa-shield-halved"></i>

                    Account Protected

                </span>

            </div>


        </section>



        <!-- =================================================
             OTP CARD
        ================================================== -->

        <section class="forgot-container">


            <div class="forgot-card otp-card">


                <!-- HEADER -->

                <div class="forgot-header">


                    <div class="forgot-icon">

                        <i class="fa-solid fa-envelope-circle-check"></i>

                    </div>


                    <h2>
                        Verify OTP
                    </h2>


                    <p>
                        Enter the verification code
                    </p>


                    <div class="otp-email">

                        We sent a 6-digit code to

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
                    id="otpAlert"
                    class="forgot-alert"
                    style="display:none;"
                >

                    <i
                        id="otpAlertIcon"
                        class="fa-solid"
                    ></i>


                    <span
                        id="otpAlertMessage"
                    ></span>

                </div>



                <!-- =================================================
                     OTP FORM
                ================================================== -->

                <form
                    id="verifyOtpForm"
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
                         OTP INPUTS
                    ================================================== -->

                    <div
                        class="otp-input-container"
                        id="otpInputs"
                    >

                        <input
                            type="text"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            aria-label="OTP digit 1"
                        >

                        <input
                            type="text"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            aria-label="OTP digit 2"
                        >

                        <input
                            type="text"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            aria-label="OTP digit 3"
                        >

                        <input
                            type="text"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            aria-label="OTP digit 4"
                        >

                        <input
                            type="text"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            aria-label="OTP digit 5"
                        >

                        <input
                            type="text"
                            class="otp-input"
                            maxlength="1"
                            inputmode="numeric"
                            aria-label="OTP digit 6"
                        >

                    </div>



                    <!-- =================================================
                         COUNTDOWN
                    ================================================== -->

                    <div class="otp-timer">

                        <i class="fa-regular fa-clock"></i>

                        <span>
                            Code expires in
                        </span>

                        <span id="countdown">
                            05:00
                        </span>

                    </div>



                    <!-- =================================================
                         VERIFY BUTTON
                    ================================================== -->

                    <button
                        type="submit"
                        class="verify-otp-button"
                        id="verifyOtpButton"
                    >

                        <span>
                            Verify Code
                        </span>

                        <i class="fa-solid fa-arrow-right"></i>

                    </button>


                </form>



                <!-- =================================================
                     RESEND
                ================================================== -->

                <div class="resend-section">

                    Didn't receive the code?

                    <button
                        type="button"
                        id="resendOtpButton"
                        class="resend-button"
                    >
                        Resend OTP
                    </button>

                </div>



                <!-- =================================================
                     CHANGE EMAIL
                ================================================== -->

                <div class="change-email">

                    <a href="forgot-password.php">

                        <i class="fa-solid fa-arrow-left"></i>

                        Change email address

                    </a>

                </div>



                <!-- =================================================
                     SECURITY
                ================================================== -->

                <div class="forgot-security">

                    <i class="fa-solid fa-lock"></i>

                    <span>
                        Secure OTP verification
                    </span>

                    <i class="fa-solid fa-circle-check"></i>

                </div>


            </div>

        </section>

    </main>



    <!-- =====================================================
         OTP JAVASCRIPT
    ====================================================== -->

    <script>

        /*
         * This page loads the OTP JavaScript dynamically.
         */

    </script>


    <script src="assets/js/verify-otp.js"></script>

</body>

</html>