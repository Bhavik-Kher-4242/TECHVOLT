<?php

$adminPage = $adminPage ?? 'dashboard';

?>

<header class="admin-header">

    <!-- =========================
         HEADER LEFT
    ========================== -->
    <div class="admin-header-left">


        <div class="admin-page-title">

            <h1>
                <?= ucfirst($adminPage) ?>
            </h1>

            <p>
                TechVolt Admin Panel
            </p>

        </div>

    </div>


    <!-- =========================
         HEADER RIGHT
    ========================== -->
    <div class="admin-header-right">

        <button type="button" class="admin-notification-btn">
            ♢
        </button>

        <div class="admin-profile">

            <div class="admin-profile-avatar">
                A
            </div>

            <div class="admin-profile-info">

                <span class="admin-profile-name">
                    Admin
                </span>

                <span class="admin-profile-role">
                    Administrator
                </span>

            </div>

        </div>

    </div>

</header>