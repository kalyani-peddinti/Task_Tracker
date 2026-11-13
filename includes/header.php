<?php
$pageTitle = $pageTitle ?? 'Task Tracker';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Task Tracker</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
<header class="topbar">
    <a class="brand" href="<?= is_logged_in() ? 'index.php' : 'login.php' ?>">Task Tracker</a>
    <nav>
        <?php if (is_logged_in()): ?>
            <span class="welcome">Hi, <?= e($_SESSION['user_name'] ?? 'User') ?></span>
            <a href="index.php">Dashboard</a>
            <form class="nav-form" action="logout.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
                <button class="link-button" type="submit">Log out</button>
            </form>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php if ($flash = get_flash()): ?>
    <div class="alert <?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
<?php endif; ?>
