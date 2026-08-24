/* =========================================================
   EXAM SHIELD
   FORGOT PASSWORD JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       ELEMENTS
    ===================================================== */

    const form =
        document.getElementById("forgotPasswordForm");

    const email =
        document.getElementById("email");

    const sendButton =
        document.getElementById("sendOtpButton");


    /* =====================================================
       EMAIL VALIDATION
    ===================================================== */

    function isValidEmail(value) {

        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(
            value
        );

    }


    /* =====================================================
       SHOW ERROR
    ===================================================== */

    function showError(message) {

        removeMessage();


        const alert =
            document.createElement("div");

        alert.className =
            "forgot-alert error";

        alert.id =
            "forgotAlert";


        alert.innerHTML = `
            <i class="fa-solid fa-circle-exclamation"></i>
            <span>${escapeHtml(message)}</span>
        `;


        if (form) {

            form.parentNode.insertBefore(
                alert,
                form
            );

        }


        if (email) {

            const inputBox =
                email.closest(".input-box");


            if (inputBox) {

                inputBox.classList.add(
                    "input-error"
                );

            }

        }

    }


    /* =====================================================
       SHOW SUCCESS
    ===================================================== */

    function showSuccess(message) {

        removeMessage();


        const alert =
            document.createElement("div");

        alert.className =
            "forgot-alert success";

        alert.id =
            "forgotAlert";


        alert.innerHTML = `
            <i class="fa-solid fa-circle-check"></i>
            <span>${escapeHtml(message)}</span>
        `;


        if (form) {

            form.parentNode.insertBefore(
                alert,
                form
            );

        }

    }


    /* =====================================================
       REMOVE MESSAGE
    ===================================================== */

    function removeMessage() {

        const existingAlert =
            document.getElementById(
                "forgotAlert"
            );


        if (existingAlert) {

            existingAlert.remove();

        }


        if (email) {

            const inputBox =
                email.closest(".input-box");


            if (inputBox) {

                inputBox.classList.remove(
                    "input-error"
                );

            }

        }

    }


    /* =====================================================
       ESCAPE HTML
       Prevents user input from being inserted
       directly into HTML.
    ===================================================== */

    function escapeHtml(value) {

        const div =
            document.createElement("div");

        div.textContent =
            value;

        return div.innerHTML;

    }


    /* =====================================================
       INPUT FOCUS
    ===================================================== */

    if (email) {

        email.addEventListener(
            "focus",
            function () {

                removeMessage();

            }
        );


        email.addEventListener(
            "input",
            function () {

                const value =
                    this.value.trim();


                const inputBox =
                    this.closest(".input-box");


                if (inputBox) {

                    inputBox.classList.remove(
                        "input-error"
                    );

                }


                if (
                    value !== "" &&
                    !isValidEmail(value)
                ) {

                    inputBox?.classList.add(
                        "input-error"
                    );

                }

            }
        );

    }


    /* =====================================================
       FORM SUBMIT
    ===================================================== */

    if (form) {

        form.addEventListener(
            "submit",
            async function (event) {

                event.preventDefault();


                const emailValue =
                    email
                        ? email.value.trim()
                        : "";


                /* -----------------------------------------
                   VALIDATION
                ----------------------------------------- */

                if (emailValue === "") {

                    showError(
                        "Please enter your email address."
                    );

                    email?.focus();

                    return;

                }


                if (
                    !isValidEmail(
                        emailValue
                    )
                ) {

                    showError(
                        "Please enter a valid email address."
                    );

                    email?.focus();

                    return;

                }


                /* -----------------------------------------
                   LOADING
                ----------------------------------------- */

                setLoading(true);


                removeMessage();


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
                                    encodeURIComponent(
                                        emailValue
                                    )
                            }
                        );


                    /*
                     * Check whether the server returned
                     * valid JSON.
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

                        const text =
                            await response.text();

                        throw new Error(
                            "Server returned an invalid response."
                        );

                    }


                    /* -------------------------------------
                       SUCCESS
                    ------------------------------------- */

                    if (
                        response.ok &&
                        data.success
                    ) {

                        showSuccess(
                            data.message ||
                            "Verification code sent successfully."
                        );


                        /*
                         * Save email temporarily.
                         * This allows the OTP page to know
                         * which email is being verified.
                         */

                        sessionStorage.setItem(
                            "examshield_reset_email",
                            emailValue
                        );


                        /*
                         * Small delay so the user can see
                         * the success message.
                         */

                        setTimeout(
                            function () {

                                /*
                                 * If your OTP verification
                                 * page is named verify-otp.php,
                                 * this will open it.
                                 */

                                window.location.href =
                                    "verify-otp.php";

                            },
                            900
                        );


                    } else {

                        showError(
                            data.message ||
                            "Unable to send verification code."
                        );


                        setLoading(false);

                    }

                } catch (error) {

                    console.error(
                        "Forgot password error:",
                        error
                    );


                    showError(
                        "Unable to connect to the server. Please try again."
                    );


                    setLoading(false);

                }

            }
        );

    }


    /* =====================================================
       LOADING STATE
    ===================================================== */

    function setLoading(isLoading) {

        if (!sendButton) {
            return;
        }


        const text =
            sendButton.querySelector(
                "span"
            );

        const icon =
            sendButton.querySelector(
                "i"
            );


        if (isLoading) {

            sendButton.disabled =
                true;

            sendButton.classList.add(
                "loading"
            );


            if (text) {

                text.textContent =
                    "Sending Code...";

            }


            if (icon) {

                icon.className =
                    "fa-solid fa-spinner";

            }

        } else {

            sendButton.disabled =
                false;

            sendButton.classList.remove(
                "loading"
            );


            if (text) {

                text.textContent =
                    "Send Verification Code";

            }


            if (icon) {

                icon.className =
                    "fa-solid fa-paper-plane";

            }

        }

    }


    /* =====================================================
       ENTER KEY
    ===================================================== */

    if (email) {

        email.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Enter"
                ) {

                    event.preventDefault();

                    form?.requestSubmit();

                }

            }
        );

    }


    /* =====================================================
       AUTO FOCUS
    ===================================================== */

    if (
        email &&
        window.innerWidth > 600
    ) {

        setTimeout(
            function () {

                email.focus();

            },
            500
        );

    }

});