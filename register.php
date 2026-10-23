<?php
require 'includes/bootstrap.php';

if (user()) {
    redirect('account.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');

    if (!csrf_valid()) {
        $error = 'Your session changed or expired. Please enter your password and submit again.';
    } elseif ($username === '' || mb_strlen($username) > 190) {
        $error = 'Enter a username of up to 190 characters.';
    } elseif ($password === '') {
        $error = 'Enter a password.';
    } else {
        $created = query(
            'INSERT INTO users (username, password_hash) VALUES (?, ?)',
            [$username, hash_password($password)],
            true
        );

        if ($created) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = mysqli_insert_id(db());
            flash('You’re in. Welcome to the log.');
            redirect('account.php');
        }

        $error = 'That username is already taken. Choose another or log in.';
    }
}

$pageTitle = 'Join the log';
require 'includes/header.php';
?>
<section class="form-page">
    <p class="eyebrow">
        KEEP THE FOLLOW-UP GOING
    </p>
    <h1>
        Join the log.
    </h1>
    <p>
        A little accountability. A better tech experience.
    </p>
    <?php if ($error): ?>
        <p class="error" role="alert">
            <?= e($error) ?>
        </p>
    <?php endif; ?>
    <form method="post" class="stack">
        <?php token_field(); ?>
        <label>
            Username
            <input name="username" autocomplete="username" maxlength="190" required value="<?= e($username) ?>">
        </label>
        <label>
            Password
            <input type="password" name="password" autocomplete="new-password" required>
        </label>
        <button class="button">
            Create account ↗
        </button>
    </form>
    <p>
        Already contributing?
        <a href="login.php">
            Log in
        </a>
    </p>
</section>
<?php require 'includes/footer.php'; ?>
