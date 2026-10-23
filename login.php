<?php
require 'includes/bootstrap.php';

if (user()) {
    redirect('account.php');
}
$error = '';
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $email = trim((string)($_POST['email'] ?? ''));
    $attempts = [];
    foreach ($_SESSION['login_attempts'] ?? [] as $time) {
        if ($time > time() - 900) {
            $attempts[] = $time;
        }
    }
    if (count($attempts) >= 10) {
        $error = 'Too many attempts. Please try again in 15 minutes.';
    } else {
        $account = mysqli_fetch_assoc(query(
            'SELECT * FROM users WHERE email = ? AND active = 1',
            [$email]
        ));
        if ($account && password_verify((string)($_POST['password'] ?? ''), $account['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $account['id'];
            unset($_SESSION['login_attempts'], $_SESSION['csrf']);
            redirect('account.php');
        }
        $attempts[] = time();
        $_SESSION['login_attempts'] = $attempts;
        $error = 'Email or password is incorrect.';
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
            Email
            <input type="email" name="email" autocomplete="email" required maxlength="190" value="<?= e($email) ?>">
        </label>
        <label>
            Password
            <input type="password" name="password" autocomplete="current-password" required maxlength="72">
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
