<?php
require 'includes/bootstrap.php';

$fix = mysqli_fetch_assoc(query(
    'SELECT f.*,u.username FROM fixes f JOIN users u ON u.id=f.user_id WHERE f.id=? AND f.active=1 AND u.active=1',
    [(int)($_GET['id'] ?? 0)]
));
if (!$fix) {
    http_response_code(404);
    $pageTitle = 'Not found';
    require 'includes/header.php';
    echo '<section class="form-page"><h1>Entry not found.</h1><p>This entry may have been removed.</p><a href="index.php">Back to the fix log</a></section>';
    require 'includes/footer.php';
    exit;
}

$pageTitle = $fix['title'];
require 'includes/header.php';
?>
<article class="detail">
    <a href="index.php">
        ← The fix log
    </a>
    <p class="eyebrow">
        <?= e($fix['product']) ?>
        <span class="badge">
            ● FIX REPORTED
        </span>
    </p>
    <h1>
        <?= e($fix['title']) ?>
    </h1>
    <p class="muted">
        Logged by
        <?= e($fix['username']) ?>
        · Updated
        <?= e(date('d M Y',strtotime($fix['updated_at']))) ?>
    </p>
    <div class="timeline">
        <div>
            <span>
                MENTIONED ON THE POD
            </span>
            <strong>
                <?= e(date('d M Y',strtotime($fix['mentioned_on']))) ?>
            </strong>
        </div>
        <span aria-hidden="true">
            →
        </span>
        <div>
            <span>
                FIX REPORTED
            </span>
            <strong>
                <?= e(date('d M Y',strtotime($fix['fixed_on']))) ?>
            </strong>
        </div>
    </div>
    <h2>
        The original bug
    </h2>
    <p class="prose"><?= e($fix['description']) ?></p>
    <h2>
        What changed
    </h2>
    <p class="prose"><?= e($fix['resolution']) ?></p>
    <div class="actions">
        <a class="button" href="<?= e($fix['evidence_url']) ?>" target="_blank" rel="noopener noreferrer">
            See the fix evidence ↗
        </a>
        <a href="<?= e($fix['episode_url']) ?>" target="_blank" rel="noopener noreferrer">
            Watch the podcast mention ↗
        </a>
    </div>
    <p class="muted">
        Community-submitted evidence; not independently verified by Waveform.
    </p>
    <?php if ($currentUser && $currentUser['id'] === $fix['user_id']): ?>
        <a href="edit.php?id=<?= $fix['id'] ?>">
            Edit your contribution →
        </a>
    <?php endif; ?>
</article>
<?php require 'includes/footer.php'; ?>
