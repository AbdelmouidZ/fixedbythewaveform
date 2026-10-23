<?php $currentUser = user(); ?>
<!doctype html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>
            <?= e($pageTitle ?? 'The fix log') ?>
            · Fixed by Waveform
        </title>
        <meta name="description" content="A community log of bugs fixed after Waveform asked: Did They Even Test This?">
        <link rel="stylesheet" href="assets/style.css">
    </head>
    <body>
        <header class="site-header">
            <a class="brand" href="index.php">
                <span class="mark" aria-hidden="true">
                    ▂▆▃█▅▂
                </span>
                FIXED BY
                <strong>
                    WAVEFORM
                </strong>
            </a>
            <nav aria-label="Main navigation">
                <a href="index.php">
                    The fix log
                </a>
                <?php if ($currentUser): ?>
                    <a href="account.php">
                        My contributions
                    </a>
                    <a class="button small" href="edit.php">
                        + Add a fix
                    </a>
                    <form class="inline" method="post" action="logout.php">
                        <?php token_field(); ?>
                        <button class="text-button">
                            Log out
                        </button>
                    </form>
                <?php else: ?>
                    <a href="login.php">
                        Log in
                    </a>
                    <a class="button small" href="register.php">
                        Join the log ↗
                    </a>
                <?php endif; ?>
            </nav>
        </header>
        <main>
            <?php if (isset($_SESSION['flash'])): ?>
                <div class="notice" role="status">
                    <?= e($_SESSION['flash']) ?>
                </div>
            <?php
unset($_SESSION['flash']);
endif;
?>
