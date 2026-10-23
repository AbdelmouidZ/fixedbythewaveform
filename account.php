<?php
require 'includes/bootstrap.php';

$u = require_login();
$fixes = mysqli_fetch_all(query(
    'SELECT * FROM fixes WHERE user_id=? ORDER BY created_at DESC',
    [$u['id']]
), MYSQLI_ASSOC);

$pageTitle = 'My contributions';
require 'includes/header.php';
?>
<section class="account">
    <p class="eyebrow">
        YOUR CORNER OF THE LOG
    </p>
    <h1>
        Hey,
        <?= e($u['name']) ?>
        .
    </h1>
    <div class="section-heading">
        <h2>
            My contributions
        </h2>
        <a href="edit.php" class="button">
            + Add a fix
        </a>
    </div>
    <?php if (!$fixes): ?>
        <div class="empty">
            <h3>
                No contributions yet.
            </h3>
            <p>
                Found evidence that a bug from the podcast was fixed? Start your first entry.
            </p>
            <a href="edit.php">
                Add a fix ↗
            </a>
        </div>
    <?php endif; ?>
    <?php foreach ($fixes as $fix): ?>
        <article class="manage-row">
            <div>
                <span class="badge <?= !$fix['active'] ? 'removed' : '' ?>">
                    <?= $fix['active'] ? 'PUBLISHED' : 'REMOVED' ?>
                </span>
                <h3>
                    <?= e($fix['title']) ?>
                </h3>
                <small>
                    <?= e($fix['product']) ?>
                </small>
            </div>
            <div class="actions">
                <?php if ($fix['active']): ?>
                    <a href="fix.php?id=<?= $fix['id'] ?>">
                        View
                    </a>
                    <a href="edit.php?id=<?= $fix['id'] ?>">
                        Edit
                    </a>
                    <a class="danger" href="remove.php?id=<?= $fix['id'] ?>">
                        Remove
                    </a>
                <?php else: ?>
                    <form method="post" action="remove.php">
                        <?php token_field(); ?>
                        <input type="hidden" name="id" value="<?= $fix['id'] ?>">
                        <button name="action" value="restore" class="text-button">
                            Restore ↗
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</section>
<?php require 'includes/footer.php'; ?>
