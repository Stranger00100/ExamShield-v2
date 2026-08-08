<div class="topbar">

    <div class="topbar-left">

    <div class="breadcrumb">

        <span>Teacher Panel</span>

        <i data-lucide="chevron-right"></i>

        <span class="active-page">

            <?php echo $pageTitle; ?>

        </span>

    </div>

    <h1><?php echo $pageTitle; ?></h1>

    <p>Welcome back, Teacher 👋</p>

</div>

    <div class="topbar-right">

        <div class="search-box">

            <i data-lucide="search"></i>

            <input
                type="text"
                placeholder="Search..."
            >

        </div>

        <button class="icon-btn">

            <i data-lucide="bell"></i>

            <span class="notification-dot"></span>

        </button>

        <div class="profile-card">

            <div class="profile-avatar">

                <?php
                echo strtoupper(substr($_SESSION['name'] ?? 'T',0,1));
                ?>

            </div>

            <div>

                <h4>

                    <?php
                    echo $_SESSION['name'] ?? 'Teacher';
                    ?>

                </h4>

                <p>Administrator</p>

            </div>

        </div>

    </div>

</div>