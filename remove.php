<?php
require 'includes/bootstrap.php';

$u = require_login();
$id = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
$fix = mysqli_fetch_assoc(query(
    'SELECT * FROM fixes WHERE id=? AND user_id=?',
    [$id, $u['id']]
));
if (!$fix) {
    http_response_code(404);
    exit('Contribution not found.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    $action = (string)($_POST['action'] ?? '');
    if (!in_array($action, ['remove', 'restore'], true)) {
        http_response_code(400);
        exit('Invalid action.');
    }
    query(
        'UPDATE fixes SET active=? WHERE id=? AND user_id=?',
        [$action === 'restore' ? 1 : 0, $id, $u['id']]
    );
    flash($action === 'restore' ? 'Contribution restored.' : 'Contribution removed. You can restore it from your contributions.');
    redirect('account.php');
}

$pageTitle = 'Remove contribution';
require 'includes/header.php';
?>
<section class="form-page">
    <p class="eyebrow">
        REMOVE FROM THE PUBLIC LOG
    </p>
    <h1>
        Take this one down?
    </h1>
    <h2>
        <?= e($fix['title']) ?>
    </h2>
    <p>
        Your entry will be hidden from the public log. Its data stays saved, and you can restore it any time.
    </p>
    <form method="post" class="actions">
        <?php token_field(); ?>
        <input type="hidden" name="id" value="<?= $id ?>">
        <button class="button" name="action" value="remove">
            Remove contribution
        </button>
        <a href="account.php">
            Keep it
        </a>
    </form>
</section>
<?php require 'includes/footer.php'; ?>
