/* =========================================================
   EXAM SHIELD
   HOMEPAGE JAVASCRIPT
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       1. ELEMENTS
    ===================================================== */

    const header = document.querySelector(".main-header");
    const mobileMenuBtn = document.querySelector(".mobile-menu-btn");
    const mainNav = document.querySelector(".main-nav");

    const navLinks = document.querySelectorAll(
        ".nav-link"
    );

    const revealElements = document.querySelectorAll(
        ".reveal, .reveal-left, .reveal-right, .stagger"
    );

    const counters = document.querySelectorAll(
        "[data-counter]"
    );


    /* =====================================================
       2. MOBILE MENU
    ===================================================== */

    if (mobileMenuBtn && mainNav) {

        mobileMenuBtn.addEventListener(
            "click",
            function () {

                const isOpen =
                    mainNav.classList.toggle("show");

                mobileMenuBtn.setAttribute(
                    "aria-expanded",
                    isOpen ? "true" : "false"
                );

                const icon =
                    mobileMenuBtn.querySelector("i");

                if (icon) {

                    if (isOpen) {

                        icon.classList.remove(
                            "fa-bars"
                        );

                        icon.classList.add(
                            "fa-xmark"
                        );

                    } else {

                        icon.classList.remove(
                            "fa-xmark"
                        );

                        icon.classList.add(
                            "fa-bars"
                        );
                    }
                }
            }
        );
    }


    /* =====================================================
       3. CLOSE MOBILE MENU WHEN LINK IS CLICKED
    ===================================================== */

    navLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function () {

                if (
                    window.innerWidth <= 768 &&
                    mainNav
                ) {

                    mainNav.classList.remove(
                        "show"
                    );

                    if (mobileMenuBtn) {

                        mobileMenuBtn.setAttribute(
                            "aria-expanded",
                            "false"
                        );

                        const icon =
                            mobileMenuBtn.querySelector(
                                "i"
                            );

                        if (icon) {

                            icon.classList.remove(
                                "fa-xmark"
                            );

                            icon.classList.add(
                                "fa-bars"
                            );
                        }
                    }
                }
            }
        );
    });


    /* =====================================================
       4. CLOSE MENU WHEN CLICKING OUTSIDE
    ===================================================== */

    document.addEventListener(
        "click",
        function (event) {

            if (
                !mainNav ||
                !mobileMenuBtn
            ) {
                return;
            }

            const clickedInsideMenu =
                mainNav.contains(event.target);

            const clickedButton =
                mobileMenuBtn.contains(event.target);

            if (
                !clickedInsideMenu &&
                !clickedButton
            ) {

                mainNav.classList.remove(
                    "show"
                );

                mobileMenuBtn.setAttribute(
                    "aria-expanded",
                    "false"
                );

                const icon =
                    mobileMenuBtn.querySelector(
                        "i"
                    );

                if (icon) {

                    icon.classList.remove(
                        "fa-xmark"
                    );

                    icon.classList.add(
                        "fa-bars"
                    );
                }
            }
        }
    );


    /* =====================================================
       5. STICKY HEADER
    ===================================================== */

    function updateHeader() {

        if (!header) {
            return;
        }

        if (window.scrollY > 30) {

            header.classList.add(
                "scrolled"
            );

        } else {

            header.classList.remove(
                "scrolled"
            );
        }
    }


    window.addEventListener(
        "scroll",
        updateHeader,
        {
            passive: true
        }
    );


    updateHeader();


    /* =====================================================
       6. SMOOTH SCROLL
    ===================================================== */

    document
        .querySelectorAll(
            'a[href^="#"]'
        )
        .forEach(function (link) {

            link.addEventListener(
                "click",
                function (event) {

                    const targetId =
                        this.getAttribute(
                            "href"
                        );

                    if (
                        !targetId ||
                        targetId === "#"
                    ) {
                        return;
                    }

                    const target =
                        document.querySelector(
                            targetId
                        );

                    if (!target) {
                        return;
                    }

                    event.preventDefault();

                    const headerHeight =
                        header
                            ? header.offsetHeight
                            : 0;

                    const targetPosition =
                        target.getBoundingClientRect()
                            .top
                        +
                        window.scrollY
                        -
                        headerHeight
                        -
                        10;

                    window.scrollTo({
                        top:
                            targetPosition,
                        behavior:
                            "smooth"
                    });
                }
            );
        });


    /* =====================================================
       7. ACTIVE NAVIGATION
    ===================================================== */

    const sections =
        document.querySelectorAll(
            "section[id]"
        );


    function updateActiveNavigation() {

        let currentSection = "";

        const scrollPosition =
            window.scrollY
            +
            (header
                ? header.offsetHeight
                : 70)
            +
            80;


        sections.forEach(
            function (section) {

                const sectionTop =
                    section.offsetTop;

                const sectionHeight =
                    section.offsetHeight;

                if (
                    scrollPosition >=
                    sectionTop
                    &&
                    scrollPosition <
                    sectionTop +
                    sectionHeight
                ) {

                    currentSection =
                        section.getAttribute(
                            "id"
                        );
                }
            }
        );


        navLinks.forEach(
            function (link) {

                const href =
                    link.getAttribute(
                        "href"
                    );

                link.classList.remove(
                    "active"
                );

                if (
                    href ===
                    "#" + currentSection
                ) {

                    link.classList.add(
                        "active"
                    );
                }
            }
        );
    }


    window.addEventListener(
        "scroll",
        updateActiveNavigation,
        {
            passive: true
        }
    );


    updateActiveNavigation();


    /* =====================================================
       8. SCROLL REVEAL
    ===================================================== */

    if (
        "IntersectionObserver"
        in window
    ) {

        const revealObserver =
            new IntersectionObserver(
                function (
                    entries,
                    observer
                ) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                entry.target
                                    .classList
                                    .add(
                                        "active"
                                    );

                                observer.unobserve(
                                    entry.target
                                );
                            }
                        }
                    );
                },
                {
                    threshold: 0.12,

                    rootMargin:
                        "0px 0px -50px 0px"
                }
            );


        revealElements.forEach(
            function (element) {

                revealObserver.observe(
                    element
                );
            }
        );

    } else {

        revealElements.forEach(
            function (element) {

                element.classList.add(
                    "active"
                );
            }
        );
    }


    /* =====================================================
       9. NUMBER COUNTER
    ===================================================== */

    function animateCounter(
        element
    ) {

        const target =
            parseFloat(
                element.dataset.counter
            );

        if (
            isNaN(target)
        ) {
            return;
        }

        const duration = 1600;

        const startTime =
            performance.now();


        function updateCounter(
            currentTime
        ) {

            const elapsed =
                currentTime -
                startTime;

            const progress =
                Math.min(
                    elapsed / duration,
                    1
                );


            const easedProgress =
                1 -
                Math.pow(
                    1 - progress,
                    3
                );


            const currentValue =
                target *
                easedProgress;


            if (
                Number.isInteger(
                    target
                )
            ) {

                element.textContent =
                    Math.floor(
                        currentValue
                    ).toLocaleString();

            } else {

                element.textContent =
                    currentValue.toFixed(
                        1
                    );
            }


            if (
                progress < 1
            ) {

                requestAnimationFrame(
                    updateCounter
                );

            } else {

                if (
                    element.dataset.suffix
                ) {

                    element.textContent =
                        target.toLocaleString()
                        +
                        element.dataset.suffix;
                }
            }
        }


        requestAnimationFrame(
            updateCounter
        );
    }


    if (
        counters.length &&
        "IntersectionObserver"
        in window
    ) {

        const counterObserver =
            new IntersectionObserver(
                function (
                    entries,
                    observer
                ) {

                    entries.forEach(
                        function (entry) {

                            if (
                                entry.isIntersecting
                            ) {

                                animateCounter(
                                    entry.target
                                );

                                observer.unobserve(
                                    entry.target
                                );
                            }
                        }
                    );
                },
                {
                    threshold: 0.6
                }
            );


        counters.forEach(
            function (counter) {

                counterObserver.observe(
                    counter
                );
            }
        );
    }


    /* =====================================================
       10. HERO CARD MOUSE EFFECT
    ===================================================== */

    const heroCard =
        document.querySelector(
            ".hero-card"
        );


    if (
        heroCard &&
        window.matchMedia(
            "(pointer: fine)"
        ).matches
    ) {

        heroCard.addEventListener(
            "mousemove",
            function (event) {

                const rect =
                    heroCard.getBoundingClientRect();


                const x =
                    event.clientX -
                    rect.left;


                const y =
                    event.clientY -
                    rect.top;


                const centerX =
                    rect.width / 2;


                const centerY =
                    rect.height / 2;


                const rotateY =
                    (
                        x -
                        centerX
                    )
                    /
                    35;


                const rotateX =
                    (
                        centerY -
                        y
                    )
                    /
                    35;


                heroCard.style.transform =
                    `
                    perspective(1000px)
                    rotateX(${rotateX}deg)
                    rotateY(${rotateY}deg)
                    translateY(-6px)
                    `;
            }
        );


        heroCard.addEventListener(
            "mouseleave",
            function () {

                heroCard.style.transform =
                    `
                    perspective(1000px)
                    rotateY(-4deg)
                    rotateX(2deg)
                    translateY(0)
                    `;
            }
        );
    }


    /* =====================================================
       11. BUTTON RIPPLE EFFECT
    ===================================================== */

    const buttons =
        document.querySelectorAll(
            ".btn"
        );


    buttons.forEach(
        function (button) {

            button.addEventListener(
                "click",
                function (event) {

                    const rect =
                        button.getBoundingClientRect();


                    const ripple =
                        document.createElement(
                            "span"
                        );


                    const size =
                        Math.max(
                            rect.width,
                            rect.height
                        );


                    const x =
                        event.clientX -
                        rect.left -
                        size / 2;


                    const y =
                        event.clientY -
                        rect.top -
                        size / 2;


                    ripple.style.width =
                        size + "px";

                    ripple.style.height =
                        size + "px";

                    ripple.style.left =
                        x + "px";

                    ripple.style.top =
                        y + "px";

                    ripple.className =
                        "button-ripple";


                    button.appendChild(
                        ripple
                    );


                    setTimeout(
                        function () {

                            ripple.remove();

                        },
                        650
                    );
                }
            );
        }
    );


    /* =====================================================
       12. SECURITY STATUS ANIMATION
    ===================================================== */

    const securityStatuses =
        document.querySelectorAll(
            ".monitor-status"
        );


    securityStatuses.forEach(
        function (status, index) {

            status.addEventListener(
                "mouseenter",
                function () {

                    const icon =
                        status.querySelector(
                            "> i"
                        );

                    if (icon) {

                        icon.style.transform =
                            "scale(1.2)";
                    }
                }
            );


            status.addEventListener(
                "mouseleave",
                function () {

                    const icon =
                        status.querySelector(
                            "> i"
                        );

                    if (icon) {

                        icon.style.transform =
                            "scale(1)";
                    }
                }
            );
        }
    );


    /* =====================================================
       13. FAKE CODE TYPING EFFECT
    ===================================================== */

    const codeTyping =
        document.querySelector(
            ".typing-code"
        );


    if (codeTyping) {

        const originalText =
            codeTyping.textContent;

        codeTyping.textContent = "";

        let characterIndex = 0;


        function typeCode() {

            if (
                characterIndex <
                originalText.length
            ) {

                codeTyping.textContent +=
                    originalText[
                        characterIndex
                    ];

                characterIndex++;

                setTimeout(
                    typeCode,
                    35
                );

            } else {

                codeTyping.classList.add(
                    "typing-complete"
                );
            }
        }


        setTimeout(
            typeCode,
            900
        );
    }


    /* =====================================================
       14. LIVE EXAM STATUS
    ===================================================== */

    const liveStatus =
        document.querySelector(
            ".live-status"
        );


    if (liveStatus) {

        setInterval(
            function () {

                liveStatus.classList.toggle(
                    "status-active"
                );

            },
            1500
        );
    }


    /* =====================================================
       15. DYNAMIC CURRENT YEAR
    ===================================================== */

    const yearElements =
        document.querySelectorAll(
            "[data-current-year]"
        );


    yearElements.forEach(
        function (element) {

            element.textContent =
                new Date().getFullYear();
        }
    );


    /* =====================================================
       16. ESC KEY CLOSE MENU
    ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape"
                &&
                mainNav
                &&
                mainNav.classList.contains(
                    "show"
                )
            ) {

                mainNav.classList.remove(
                    "show"
                );

                if (mobileMenuBtn) {

                    mobileMenuBtn.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                    const icon =
                        mobileMenuBtn.querySelector(
                            "i"
                        );

                    if (icon) {

                        icon.classList.remove(
                            "fa-xmark"
                        );

                        icon.classList.add(
                            "fa-bars"
                        );
                    }
                }
            }
        }
    );


    /* =====================================================
       17. PREVENT BROKEN # LINKS
    ===================================================== */

    document
        .querySelectorAll(
            'a[href="#"]'
        )
        .forEach(
            function (link) {

                link.addEventListener(
                    "click",
                    function (event) {

                        event.preventDefault();
                    }
                );
            }
        );


    /* =====================================================
       18. PARALLAX BACKGROUND
    ===================================================== */

    const backgroundCircles =
        document.querySelectorAll(
            ".floating-circle"
        );


    if (
        backgroundCircles.length &&
        window.matchMedia(
            "(pointer: fine)"
        ).matches
    ) {

        window.addEventListener(
            "mousemove",
            function (event) {

                const mouseX =
                    (
                        event.clientX /
                        window.innerWidth
                    ) -
                    0.5;


                const mouseY =
                    (
                        event.clientY /
                        window.innerHeight
                    ) -
                    0.5;


                backgroundCircles.forEach(
                    function (
                        circle,
                        index
                    ) {

                        const speed =
                            (index + 1) * 8;


                        circle.style.transform =
                            `
                            translate(
                                ${mouseX * speed}px,
                                ${mouseY * speed}px
                            )
                            `;
                    }
                );
            }
        );
    }


    /* =====================================================
       19. PAGE LOADED
    ===================================================== */

    document.body.classList.add(
        "page-loaded"
    );


    console.log(
        "Exam Shield homepage loaded successfully."
    );

});