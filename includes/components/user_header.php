<?php
/**
 * Shared Header Component
 * Variables required:
 * $page_title - The main heading
 * $page_subtitle - The smaller description
 */
$page_title = $page_title ?? "Welcome Back";
$page_subtitle = $page_subtitle ?? "How can we help you today?";
?>
<header class="dashboard-header">
    <div class="header-left">
        <h1><?= $page_title ?></h1>
        <p><?= $page_subtitle ?></p>
    </div>
    <div class="header-right">
        <?php include INC_PATH . "/components/profile_dropdown.php"; ?>
    </div>
</header>
