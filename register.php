<?php
require 'includes/bootstrap.php';

if (user()) {
    redirect('account.php');
}
$error = '';
$name = $email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $name = trim((string)($_POST['name'] ?? ''));
    $email = trim((string)($_POST['email'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    if ($name === '' || mb_strlen($name) > 80 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
        $error = 'Enter a name (up to 80 characters) and a valid email address.';
    } elseif (strlen($password) < 12 || strlen($password) > 72) {
        $error = 'Use a password between 12 and 72 bytes long.';
    } elseif ($password !== ($_POST['confirm_password'] ?? '')) {
        $error = 'Passwords do not match.';
    } else {
        $created = query( 'INSERT INTO users (name,email,password_hash) VALUES (?,?,?)', [$name, $email, password_hash($password, PASSWORD_DEFAULT)], true);
        if ($created) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = mysqli_insert_id(db());
            unset($_SESSION['csrf']);
            flash('You’re in. Welcome to the log.');
            redirect('account.php');
        }
        $error = 'Unable to register with this email. Try logging in or use another address.';
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
            Display name
            <input name="name" autocomplete="nickname" maxlength="80" required value="<?= e($name) ?>">
        </label>
        <label>
            Email
            <input type="email" name="email" autocomplete="email" maxlength="190" required value="<?= e($email) ?>">
        </label>
        <label>
            Password
            <small>
                12–72 bytes
            </small>
            <input type="password" name="password" autocomplete="new-password" minlength="12" maxlength="72" required>
        </label>
        <label>
            Confirm password
            <input type="password" name="confirm_password" autocomplete="new-password" required maxlength="72">
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
