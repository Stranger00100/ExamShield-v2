/* =========================================================
   EXAM SHIELD
   OTP VERIFICATION JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTS
    ===================================================== */

    const form =
        document.getElementById("verifyOtpForm");

    const otpInputs =
        Array.from(
            document.querySelectorAll(".otp-input")
        );

    const verifyButton =
        document.getElementById("verifyOtpButton");

    const resendButton =
        document.getElementById("resendOtpButton");

    const countdownElement =
        document.getElementById("countdown");

    const alertBox =
        document.getElementById("otpAlert");

    const alertIcon =
        document.getElementById("otpAlertIcon");

    const alertMessage =
        document.getElementById("otpAlertMessage");

    const resetEmail =
        document.getElementById("resetEmail");


    /* =====================================================
       SETTINGS
    ===================================================== */

    const OTP_LENGTH = 6;

    const OTP_DURATION = 5 * 60;

    let remainingSeconds =
        OTP_DURATION;

    let countdownInterval = null;

    let isVerifying = false;


    /* =====================================================
       GET EMAIL
    ===================================================== */

    const email =
        resetEmail
            ? resetEmail.value.trim()
            : "";


    /* =====================================================
       BASIC CHECK
    ===================================================== */

    if (
        !form ||
        otpInputs.length !== OTP_LENGTH
    ) {

        console.error(
            "ExamShield OTP form is not configured correctly."
        );

        return;

    }


    /* =====================================================
       SHOW ALERT
    ===================================================== */

    function showAlert(
        message,
        type = "error"
    ) {

        if (!alertBox) {
            return;
        }


        alertBox.style.display =
            "flex";


        alertBox.className =
            "forgot-alert " + type;


        if (alertIcon) {

            if (type === "success") {

                alertIcon.className =
                    "fa-solid fa-circle-check";

            } else {

                alertIcon.className =
                    "fa-solid fa-circle-exclamation";

            }

        }


        if (alertMessage) {

            alertMessage.textContent =
                message;

        }

    }


    /* =====================================================
       HIDE ALERT
    ===================================================== */

    function hideAlert() {

        if (!alertBox) {
            return;
        }


        alertBox.style.display =
            "none";


        if (alertMessage) {

            alertMessage.textContent =
                "";

        }

    }


    /* =====================================================
       CLEAR OTP ERROR
    ===================================================== */

    function clearOtpError() {

        otpInputs.forEach(function (input) {

            input.classList.remove(
                "error"
            );

        });

    }


    /* =====================================================
       SHOW OTP ERROR
    ===================================================== */

    function showOtpError() {

        otpInputs.forEach(function (input) {

            input.classList.add(
                "error"
            );

        });


        setTimeout(function () {

            clearOtpError();

        }, 500);

    }


    /* =====================================================
       GET OTP VALUE
    ===================================================== */

    function getOtp() {

        return otpInputs
            .map(function (input) {

                return input.value.trim();

            })
            .join("");

    }


    /* =====================================================
       CHECK COMPLETE OTP
    ===================================================== */

    function isOtpComplete() {

        return getOtp().length === OTP_LENGTH;

    }


    /* =====================================================
       UPDATE INPUT STATE
    ===================================================== */

    function updateInputState(input) {

        if (
            input &&
            input.value.trim() !== ""
        ) {

            input.classList.add(
                "filled"
            );

        } else if (input) {

            input.classList.remove(
                "filled"
            );

        }

    }


    /* =====================================================
       INPUT HANDLING
    ===================================================== */

    otpInputs.forEach(function (input, index) {


        /* -------------------------------------------------
           INPUT
        ------------------------------------------------- */

        input.addEventListener(
            "input",
            function (event) {

                hideAlert();

                clearOtpError();


                /*
                 * Allow numbers only.
                 */

                let value =
                    input.value.replace(
                        /\D/g,
                        ""
                    );


                /*
                 * Only keep one character.
                 */

                input.value =
                    value.slice(0, 1);


                updateInputState(input);


                /*
                 * Move to next box.
                 */

                if (
                    input.value !== "" &&
                    index < OTP_LENGTH - 1
                ) {

                    otpInputs[index + 1].focus();

                    otpInputs[index + 1].select();

                }


                /*
                 * If OTP is complete,
                 * automatically enable verify.
                 */

                updateVerifyButton();

            }
        );


        /* -------------------------------------------------
           KEYDOWN
        ------------------------------------------------- */

        input.addEventListener(
            "keydown",
            function (event) {


                /*
                 * BACKSPACE
                 */

                if (
                    event.key === "Backspace"
                ) {

                    if (
                        input.value === "" &&
                        index > 0
                    ) {

                        otpInputs[index - 1].value =
                            "";

                        updateInputState(
                            otpInputs[index - 1]
                        );

                        otpInputs[index - 1].focus();

                    }

                }


                /*
                 * LEFT ARROW
                 */

                if (
                    event.key === "ArrowLeft" &&
                    index > 0
                ) {

                    event.preventDefault();

                    otpInputs[index - 1].focus();

                }


                /*
                 * RIGHT ARROW
                 */

                if (
                    event.key === "ArrowRight" &&
                    index < OTP_LENGTH - 1
                ) {

                    event.preventDefault();

                    otpInputs[index + 1].focus();

                }

            }
        );


        /* -------------------------------------------------
           PASTE
        ------------------------------------------------- */

        input.addEventListener(
            "paste",
            function (event) {

                event.preventDefault();


                const pastedData =
                    (
                        event.clipboardData ||
                        window.clipboardData
                    )
                    .getData("text")
                    .replace(/\D/g, "")
                    .slice(0, OTP_LENGTH);


                if (!pastedData) {
                    return;
                }


                /*
                 * Put each digit into
                 * its own input.
                 */

                pastedData
                    .split("")
                    .forEach(function (
                        digit,
                        digitIndex
                    ) {

                        if (
                            otpInputs[digitIndex]
                        ) {

                            otpInputs[
                                digitIndex
                            ].value =
                                digit;


                            updateInputState(
                                otpInputs[
                                    digitIndex
                                ]
                            );

                        }

                    });


                /*
                 * Focus the last filled
                 * or last OTP box.
                 */

                const focusIndex =
                    Math.min(
                        pastedData.length,
                        OTP_LENGTH
                    ) - 1;


                if (
                    focusIndex >= 0 &&
                    otpInputs[focusIndex]
                ) {

                    otpInputs[
                        focusIndex
                    ].focus();

                }


                updateVerifyButton();

            }
        );


        /* -------------------------------------------------
           FOCUS
        ------------------------------------------------- */

        input.addEventListener(
            "focus",
            function () {

                hideAlert();

                clearOtpError();

                input.select();

            }
        );


    });


    /* =====================================================
       VERIFY BUTTON STATE
    ===================================================== */

    function updateVerifyButton() {

        if (!verifyButton) {
            return;
        }


        const complete =
            isOtpComplete();


        verifyButton.disabled =
            !complete ||
            isVerifying;

    }


    /* =====================================================
       BUTTON LOADING
    ===================================================== */

    function setVerifyLoading(
        loading
    ) {

        if (!verifyButton) {
            return;
        }


        const text =
            verifyButton.querySelector(
                "span"
            );

        const icon =
            verifyButton.querySelector(
                "i"
            );


        if (loading) {

            verifyButton.disabled =
                true;


            if (text) {

                text.textContent =
                    "Verifying...";

            }


            if (icon) {

                icon.className =
                    "fa-solid fa-spinner fa-spin";

            }

        } else {

            if (text) {

                text.textContent =
                    "Verify Code";

            }


            if (icon) {

                icon.className =
                    "fa-solid fa-arrow-right";

            }


            updateVerifyButton();

        }

    }


    /* =====================================================
       FORM SUBMIT
    ===================================================== */

    form.addEventListener(
        "submit",
        async function (event) {

            event.preventDefault();


            if (isVerifying) {
                return;
            }


            hideAlert();

            clearOtpError();


            const otp =
                getOtp();


            /* ---------------------------------------------
               VALIDATE EMAIL
            --------------------------------------------- */

            if (!email) {

                showAlert(
                    "Your recovery session has expired. Please request a new OTP."
                );

                setTimeout(function () {

                    window.location.href =
                        "forgot-password.php";

                }, 1500);

                return;

            }


            /* ---------------------------------------------
               VALIDATE OTP
            --------------------------------------------- */

            if (otp.length !== OTP_LENGTH) {

                showAlert(
                    "Please enter the complete 6-digit verification code."
                );

                showOtpError();

                return;

            }


            /* ---------------------------------------------
               CHECK EXPIRY
            --------------------------------------------- */

            if (
                remainingSeconds <= 0
            ) {

                showAlert(
                    "This OTP has expired. Please request a new code."
                );

                showOtpError();

                return;

            }


            /* ---------------------------------------------
               LOADING
            --------------------------------------------- */

            isVerifying =
                true;

            setVerifyLoading(
                true
            );


            try {

                const response =
                    await fetch(
                        "api/verify_otp.php",
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/x-www-form-urlencoded",

                                "X-Requested-With":
                                    "XMLHttpRequest"
                            },

                            body:
                                "email=" +
                                encodeURIComponent(email) +

                                "&otp=" +
                                encodeURIComponent(otp)
                        }
                    );


                /*
                 * Check response type.
                 */

                const contentType =
                    response.headers.get(
                        "content-type"
                    ) || "";


                let data;


                if (
                    contentType.includes(
                        "application/json"
                    )
                ) {

                    data =
                        await response.json();

                } else {

                    throw new Error(
                        "Server returned an invalid response."
                    );

                }


                /* -----------------------------------------
                   SUCCESS
                ----------------------------------------- */

                if (
                    response.ok &&
                    data.success
                ) {

                    showAlert(
                        data.message ||
                        "OTP verified successfully.",
                        "success"
                    );


                    /*
                     * Stop countdown.
                     */

                    stopCountdown();


                    /*
                     * Prevent another submission.
                     */

                    isVerifying =
                        true;


                    /*
                     * Redirect to password
                     * reset page.
                     */

                    setTimeout(function () {

                        window.location.href =
                            "reset-password.php";

                    }, 900);


                } else {

                    showAlert(
                        data.message ||
                        "Invalid verification code."
                    );


                    showOtpError();


                    /*
                     * Clear OTP so the user
                     * can enter it again.
                     */

                    clearOtp();


                    /*
                     * Focus first box.
                     */

                    otpInputs[0].focus();


                    isVerifying =
                        false;

                    setVerifyLoading(
                        false
                    );

                }

            } catch (error) {

                console.error(
                    "OTP verification error:",
                    error
                );


                showAlert(
                    "Unable to connect to the server. Please try again."
                );


                isVerifying =
                    false;

                setVerifyLoading(
                    false
                );

            }

        }
    );


    /* =====================================================
       CLEAR OTP
    ===================================================== */

    function clearOtp() {

        otpInputs.forEach(function (input) {

            input.value =
                "";

            input.classList.remove(
                "filled"
            );

            input.classList.remove(
                "error"
            );

        });

    }


    /* =====================================================
       COUNTDOWN
    ===================================================== */

    function startCountdown() {

        stopCountdown();


        remainingSeconds =
            OTP_DURATION;


        updateCountdown();


        countdownInterval =
            setInterval(
                function () {

                    remainingSeconds--;

                    updateCountdown();


                    if (
                        remainingSeconds <= 0
                    ) {

                        stopCountdown();

                        handleOtpExpired();

                    }

                },
                1000
            );

    }


    /* =====================================================
       UPDATE COUNTDOWN
    ===================================================== */

    function updateCountdown() {

        if (!countdownElement) {
            return;
        }


        const minutes =
            Math.floor(
                remainingSeconds / 60
            );


        const seconds =
            remainingSeconds % 60;


        countdownElement.textContent =
            String(minutes).padStart(2, "0") +
            ":" +
            String(seconds).padStart(2, "0");


        if (
            remainingSeconds <= 30
        ) {

            countdownElement.classList.add(
                "expired"
            );

        } else {

            countdownElement.classList.remove(
                "expired"
            );

        }

    }


    /* =====================================================
       STOP COUNTDOWN
    ===================================================== */

    function stopCountdown() {

        if (countdownInterval) {

            clearInterval(
                countdownInterval
            );

            countdownInterval =
                null;

        }

    }


    /* =====================================================
       OTP EXPIRED
    ===================================================== */

    function handleOtpExpired() {

        if (countdownElement) {

            countdownElement.textContent =
                "Expired";

            countdownElement.classList.add(
                "expired"
            );

        }


        showAlert(
            "Your verification code has expired. Please request a new OTP."
        );


        /*
         * Disable verification.
         */

        if (verifyButton) {

            verifyButton.disabled =
                true;

        }


        /*
         * Enable resend.
         */

        if (resendButton) {

            resendButton.disabled =
                false;

            resendButton.textContent =
                "Resend OTP";

        }

    }


    /* =====================================================
       RESEND OTP
    ===================================================== */

    if (resendButton) {

        resendButton.addEventListener(
            "click",
            async function () {

                if (
                    !email ||
                    resendButton.disabled
                ) {

                    return;

                }


                hideAlert();

                clearOtpError();


                /*
                 * Loading state.
                 */

                resendButton.disabled =
                    true;

                resendButton.textContent =
                    "Sending...";


                try {

                    const response =
                        await fetch(
                            "api/send_otp.php",
                            {
                                method: "POST",

                                headers: {
                                    "Content-Type":
                                        "application/x-www-form-urlencoded",

                                    "X-Requested-With":
                                        "XMLHttpRequest"
                                },

                                body:
                                    "email=" +
                                    encodeURIComponent(email)
                            }
                        );


                    const contentType =
                        response.headers.get(
                            "content-type"
                        ) || "";


                    if (
                        !contentType.includes(
                            "application/json"
                        )
                    ) {

                        throw new Error(
                            "Invalid server response."
                        );

                    }


                    const data =
                        await response.json();


                    if (
                        response.ok &&
                        data.success
                    ) {

                        showAlert(
                            data.message ||
                            "A new OTP has been sent.",
                            "success"
                        );


                        /*
                         * Restart timer.
                         */

                        startCountdown();


                        /*
                         * Clear previous OTP.
                         */

                        clearOtp();


                        /*
                         * Focus first input.
                         */

                        otpInputs[0].focus();


                        /*
                         * Allow resend again after
                         * a short cooldown.
                         */

                        startResendCooldown();

                    } else {

                        showAlert(
                            data.message ||
                            "Unable to resend the OTP."
                        );


                        resendButton.disabled =
                            false;

                        resendButton.textContent =
                            "Resend OTP";

                    }

                } catch (error) {

                    console.error(
                        "Resend OTP error:",
                        error
                    );


                    showAlert(
                        "Unable to connect to the server. Please try again."
                    );


                    resendButton.disabled =
                        false;

                    resendButton.textContent =
                        "Resend OTP";

                }

            }
        );

    }


    /* =====================================================
       RESEND COOLDOWN
    ===================================================== */

    function startResendCooldown() {

        if (!resendButton) {
            return;
        }


        let cooldown =
            30;


        resendButton.disabled =
            true;


        resendButton.textContent =
            "Resend in " +
            cooldown +
            "s";


        const interval =
            setInterval(
                function () {

                    cooldown--;


                    if (
                        cooldown <= 0
                    ) {

                        clearInterval(
                            interval
                        );


                        resendButton.disabled =
                            false;

                        resendButton.textContent =
                            "Resend OTP";


                        return;

                    }


                    resendButton.textContent =
                        "Resend in " +
                        cooldown +
                        "s";

                },
                1000
            );

    }


    /* =====================================================
       AUTO FOCUS FIRST INPUT
    ===================================================== */

    setTimeout(
        function () {

            if (otpInputs[0]) {

                otpInputs[0].focus();

            }

        },
        400
    );


    /* =====================================================
       START COUNTDOWN
    ===================================================== */

    startCountdown();


    /* =====================================================
       INITIAL BUTTON STATE
    ===================================================== */

    updateVerifyButton();

});