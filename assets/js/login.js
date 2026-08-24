/* =========================================================
   EXAM SHIELD
   LOGIN PAGE JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       1. PASSWORD SHOW / HIDE
    ===================================================== */

    const passwordInput =
        document.getElementById("password");

    const passwordToggle =
        document.getElementById("togglePassword");


    if (passwordInput && passwordToggle) {

        passwordToggle.addEventListener("click", function () {

            const icon =
                passwordToggle.querySelector("i");


            if (passwordInput.type === "password") {

                passwordInput.type = "text";

                icon.classList.remove("fa-eye");

                icon.classList.add("fa-eye-slash");

                passwordToggle.setAttribute(
                    "aria-label",
                    "Hide password"
                );

            } else {

                passwordInput.type = "password";

                icon.classList.remove("fa-eye-slash");

                icon.classList.add("fa-eye");

                passwordToggle.setAttribute(
                    "aria-label",
                    "Show password"
                );

            }

        });

    }


    /* =====================================================
       2. INPUT FOCUS ANIMATION
    ===================================================== */

    const inputFields =
        document.querySelectorAll(
            ".input-box input, .input-box select"
        );


    inputFields.forEach(function (input) {

        input.addEventListener("focus", function () {

            const box =
                this.closest(".input-box");

            if (box) {
                box.classList.add("focused");
            }

        });


        input.addEventListener("blur", function () {

            const box =
                this.closest(".input-box");

            if (box) {
                box.classList.remove("focused");
            }

        });

    });


    /* =====================================================
       3. LOGIN FORM VALIDATION
    ===================================================== */

    const loginForm =
        document.querySelector(".login-form");


    if (loginForm) {

        loginForm.addEventListener(
            "submit",
            function (event) {

                const email =
                    document.getElementById("email");

                const password =
                    document.getElementById("password");

                const role =
                    document.getElementById("role");


                let valid = true;


                /* -----------------------------------------
                   EMAIL
                ----------------------------------------- */

                if (!email.value.trim()) {

                    showInputError(
                        email,
                        "Email address is required."
                    );

                    valid = false;

                } else if (!isValidEmail(email.value.trim())) {

                    showInputError(
                        email,
                        "Enter a valid email address."
                    );

                    valid = false;

                } else {

                    removeInputError(email);

                }


                /* -----------------------------------------
                   PASSWORD
                ----------------------------------------- */

                if (!password.value) {

                    showInputError(
                        password,
                        "Password is required."
                    );

                    valid = false;

                } else {

                    removeInputError(password);

                }


                /* -----------------------------------------
                   ROLE
                ----------------------------------------- */

                if (!role.value) {

                    showInputError(
                        role,
                        "Please select your role."
                    );

                    valid = false;

                } else {

                    removeInputError(role);

                }


                /* -----------------------------------------
                   STOP SUBMIT
                ----------------------------------------- */

                if (!valid) {

                    event.preventDefault();

                    return;

                }


                /* -----------------------------------------
                   LOGIN LOADING
                ----------------------------------------- */

                const loginButton =
                    document.querySelector(
                        ".login-button"
                    );


                if (loginButton) {

                    loginButton.classList.add(
                        "loading"
                    );


                    const buttonText =
                        loginButton.querySelector(
                            "span"
                        );


                    if (buttonText) {

                        buttonText.textContent =
                            "Signing In...";

                    }

                }

            }
        );

    }


    /* =====================================================
       4. EMAIL VALIDATION
    ===================================================== */

    function isValidEmail(email) {

        const emailPattern =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        return emailPattern.test(email);

    }


    /* =====================================================
       5. INPUT ERROR
    ===================================================== */

    function showInputError(input, message) {

        const box =
            input.closest(".input-box");


        if (!box) {
            return;
        }


        box.classList.add("input-error");


        let error =
            box.parentElement.querySelector(
                ".field-error"
            );


        if (!error) {

            error =
                document.createElement("small");

            error.className =
                "field-error";

            box.parentElement.appendChild(error);

        }


        error.textContent = message;

    }


    /* =====================================================
       6. REMOVE INPUT ERROR
    ===================================================== */

    function removeInputError(input) {

        const box =
            input.closest(".input-box");


        if (!box) {
            return;
        }


        box.classList.remove(
            "input-error"
        );


        const error =
            box.parentElement.querySelector(
                ".field-error"
            );


        if (error) {

            error.remove();

        }

    }


    /* =====================================================
       7. CLEAR ERROR WHILE TYPING
    ===================================================== */

    inputFields.forEach(function (input) {

        input.addEventListener(
            "input",
            function () {

                removeInputError(this);

            }
        );

        input.addEventListener(
            "change",
            function () {

                removeInputError(this);

            }
        );

    });


    /* =====================================================
       8. PASSWORD CAPS LOCK DETECTION
    ===================================================== */

    if (passwordInput) {

        passwordInput.addEventListener(
            "keyup",
            function (event) {

                if (
                    event.getModifierState &&
                    event.getModifierState("CapsLock")
                ) {

                    showCapsLockMessage();

                } else {

                    hideCapsLockMessage();

                }

            }
        );

    }


    function showCapsLockMessage() {

        if (
            document.querySelector(
                ".caps-lock-warning"
            )
        ) {
            return;
        }


        const warning =
            document.createElement("small");


        warning.className =
            "caps-lock-warning";


        warning.innerHTML =
            '<i class="fa-solid fa-triangle-exclamation"></i> Caps Lock is ON';


        const passwordGroup =
            passwordInput.closest(".form-group");


        if (passwordGroup) {

            passwordGroup.appendChild(
                warning
            );

        }

    }


    function hideCapsLockMessage() {

        const warning =
            document.querySelector(
                ".caps-lock-warning"
            );


        if (warning) {

            warning.remove();

        }

    }


    /* =====================================================
       9. AUTO HIDE SERVER ALERT
    ===================================================== */

    const alert =
        document.querySelector(".login-alert");


    if (alert) {

        setTimeout(function () {

            alert.style.opacity = "0";

            alert.style.transform =
                "translateY(-8px)";

            alert.style.transition =
                "all 0.4s ease";


            setTimeout(function () {

                alert.remove();

            }, 400);

        }, 6000);

    }


    /* =====================================================
       10. REMEMBER ME
    ===================================================== */

    const rememberCheckbox =
        document.querySelector(
            'input[name="remember"]'
        );


    const emailInput =
        document.getElementById("email");


    if (
        rememberCheckbox &&
        emailInput
    ) {

        const savedEmail =
            localStorage.getItem(
                "examshield_email"
            );


        if (savedEmail) {

            emailInput.value =
                savedEmail;

            rememberCheckbox.checked =
                true;

        }


        loginForm.addEventListener(
            "submit",
            function () {

                if (
                    rememberCheckbox.checked
                ) {

                    localStorage.setItem(
                        "examshield_email",
                        emailInput.value.trim()
                    );

                } else {

                    localStorage.removeItem(
                        "examshield_email"
                    );

                }

            }
        );

    }


    /* =====================================================
       11. ENTER KEY SUPPORT
    ===================================================== */

    inputFields.forEach(function (input) {

        input.addEventListener(
            "keydown",
            function (event) {

                if (
                    event.key === "Enter"
                ) {

                    if (
                        loginForm &&
                        typeof loginForm.requestSubmit ===
                        "function"
                    ) {

                        event.preventDefault();

                        loginForm.requestSubmit();

                    }

                }

            }
        );

    });


    /* =====================================================
       12. PAGE READY ANIMATION
    ===================================================== */

    document.body.classList.add(
        "login-page-ready"
    );

});