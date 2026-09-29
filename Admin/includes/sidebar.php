<?php

$adminPage = $adminPage ?? 'dashboard';
$adminBase = $adminBase ?? './';

?>

<aside class="admin-sidebar">

    <!-- =========================
         SIDEBAR BRAND
    ========================== -->
    <div class="admin-sidebar-brand">

        <a href="<?= $adminBase ?>dashboard.php">
            <img
                src="<?= $adminBase ?>../assets/images/logo 3.png"
                alt="TechVolt"
            >
        </a>

        <span>ADMIN PANEL</span>

    </div>


    <!-- =========================
         SIDEBAR NAVIGATION
    ========================== -->
    <nav class="admin-sidebar-nav">

        <!-- Main -->
        <div class="sidebar-section-title">
            MAIN
        </div>

        <a
            href="<?= $adminBase ?>dashboard.php"
            class="sidebar-link <?= $adminPage === 'dashboard' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">⌂</span>
            <span class="sidebar-text">Dashboard</span>
        </a>


        <!-- Management -->
        <div class="sidebar-section-title">
            MANAGEMENT
        </div>

        <a
            href="<?= $adminBase ?>products/manage-products.php"
            class="sidebar-link <?= $adminPage === 'products' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">▣</span>
            <span class="sidebar-text">Products</span>
        </a>


        <a
            href="<?= $adminBase ?>categories/manage-category.php"
            class="sidebar-link <?= $adminPage === 'categories' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">▦</span>
            <span class="sidebar-text">Categories</span>
        </a>


        <a
            href="<?= $adminBase ?>inventory/stock-management.php"
            class="sidebar-link <?= $adminPage === 'inventory' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">▤</span>
            <span class="sidebar-text">Inventory</span>
        </a>


        <a
            href="<?= $adminBase ?>orders/manage-orders.php"
            class="sidebar-link <?= $adminPage === 'orders' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">◫</span>
            <span class="sidebar-text">Orders</span>
        </a>


        <a
            href="<?= $adminBase ?>users/manage-users.php"
            class="sidebar-link <?= $adminPage === 'users' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">♙</span>
            <span class="sidebar-text">Customers</span>
        </a>

        <a
            href="<?= $adminBase ?>contact/manage-messages.php"
            class="sidebar-link <?= $adminPage === 'contact' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">✉</span>
            <span class="sidebar-text">Contact Messages</span>
        </a>
        <a
            href="<?= $adminBase ?>manage-returns.php"
            class="sidebar-link <?= $adminPage === 'returns' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">↩</span>
            <span class="sidebar-text">Returns</span>
        </a>


        <a
            href="<?= $adminBase ?>reports.php"
            class="sidebar-link <?= $adminPage === 'reports' ? 'active' : '' ?>"
        >
            <span class="sidebar-icon">▥</span>
            <span class="sidebar-text">Reports</span>
        </a>

    </nav>


    <!-- =========================
         SIDEBAR FOOTER
    ========================== -->
    <div class="admin-sidebar-footer">

        <a
            href="<?= $adminBase ?>../index.php"
            class="sidebar-footer-link"
        >
            <span class="sidebar-icon">↗</span>
            <span class="sidebar-text">View Website</span>
        </a>


        <a
            href="<?= $adminBase ?>admin-logout.php"
            class="sidebar-footer-link logout-link"
        >
            <span class="sidebar-icon">⇥</span>
            <span class="sidebar-text">Logout</span>
        </a>

    </div>

</aside>