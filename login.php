<?php
require 'includes/bootstrap.php';

if (user()) {
    redirect('account.php');
}
$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim((string)($_POST['username'] ?? ''));
    $attempts = [];
    foreach ($_SESSION['login_attempts'] ?? [] as $time) {
        if ($time > time() - 900) {
            $attempts[] = $time;
        }
    }
    if (!csrf_valid()) {
        $error = 'Your session changed or expired. Please enter your password and submit again.';
    } elseif (count($attempts) >= 10) {
        $error = 'Too many attempts. Please try again in 15 minutes.';
    } else {
        $account = mysqli_fetch_assoc(query(
            'SELECT * FROM users WHERE username = ? AND active = 1',
            [$username]
        ));
        if ($account && verify_password((string)($_POST['password'] ?? ''), $account['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $account['id'];
            unset($_SESSION['login_attempts']);
            redirect('account.php');
        }
        $attempts[] = time();
        $_SESSION['login_attempts'] = $attempts;
        $error = 'Username or password is incorrect.';
    }
}

$pageTitle = 'Log in';
require 'includes/header.php';
?>
<section class="form-page">
    <p class="eyebrow">
        WELCOME BACK
    </p>
    <h1>
        Back to the log.
    </h1>
    <p>
        Log in to manage your contributions.
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
            <input name="username" autocomplete="username" required maxlength="190" value="<?= e($username) ?>">
        </label>
        <label>
            Password
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button class="button">
            Log in ↗
        </button>
    </form>
    <p>
        New here?
        <a href="register.php">
            Create an account
        </a>
    </p>
</section>
<?php require 'includes/footer.php'; ?>
