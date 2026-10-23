<?php
require 'includes/bootstrap.php';

$u = require_login();
$id = (int)($_GET['id'] ?? 0);
$fields = [
    'title',
    'product',
    'description',
    'resolution',
    'episode_url',
    'evidence_url',
    'mentioned_on',
    'fixed_on'
];
$fix = array_fill_keys($fields, '');
if ($id) {
    $fix = mysqli_fetch_assoc(query(
        'SELECT * FROM fixes WHERE id=? AND user_id=? AND active=1',
        [$id, $u['id']]
    ));
    if (!$fix) {
        http_response_code(404);
        exit('Active contribution not found.');
    }
}
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    check_csrf();
    foreach ($fields as $field) {
        $fix[$field] = trim((string)($_POST[$field] ?? ''));
    }
    foreach (['title' => 180, 'product' => 100, 'description' => 10000, 'resolution' => 10000] as $field => $max) {
        if ($fix[$field] === '' || mb_strlen($fix[$field]) > $max) {
            $errors[] = ucfirst($field) . " is required and must be at most $max characters.";
        }
    }
    if (!valid_url($fix['episode_url']) || !valid_url($fix['evidence_url'])) {
        $errors[] = 'Provide valid HTTP or HTTPS links for the episode and fix evidence.';
    }
    if (!valid_date($fix['mentioned_on']) || !valid_date($fix['fixed_on'])) {
        $errors[] = 'Enter valid mention and fix dates.';
    } elseif ($fix['fixed_on'] < $fix['mentioned_on'] || $fix['fixed_on'] > date('Y-m-d')) {
        $errors[] = 'The fix date must be on or after the mention and cannot be in the future.';
    }
    if (!$errors) {
        $values = [];
        foreach ($fields as $field) {
            $values[] = $fix[$field];
        }
        if ($id) {
            query(
                'UPDATE fixes SET title=?,product=?,description=?,resolution=?,episode_url=?,evidence_url=?,mentioned_on=?,fixed_on=? WHERE id=? AND user_id=? AND active=1',
                [...$values, $id, $u['id']]
            );
        } else {
            query(
                'INSERT INTO fixes (title,product,description,resolution,episode_url,evidence_url,mentioned_on,fixed_on,user_id) VALUES (?,?,?,?,?,?,?,?,?)',
                [...$values, $u['id']]
            );
            $id = (int)mysqli_insert_id(db());
        }
        flash('Your contribution has been saved.');
        redirect('fix.php?id=' . $id);
    }
}

$pageTitle = $id ? 'Edit contribution' : 'Add a fix';
require 'includes/header.php';
?>
<section class="form-page wide">
    <a href="account.php">
        ← My contributions
    </a>
    <p class="eyebrow">
        BRING THE RECEIPTS
    </p>
    <h1>
        <?= $id ? 'Update the record.' : 'Another bug, fixed.' ?>
    </h1>
    <p>
        Connect a “Did They Even Test This?” mention to a documented fix. Entries are community reports.
    </p>
    <?php if ($errors): ?>
        <div class="error" role="alert">
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li>
                        <?= e($error) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <form class="stack" method="post">
        <?php token_field(); ?>
        <label>
            What was fixed?
            <input name="title" required maxlength="180" value="<?= e($fix['title']) ?>" placeholder="A clear, specific title for the bug">
        </label>
        <label>
            Product or app
            <input name="product" required maxlength="100" value="<?= e($fix['product']) ?>">
        </label>
        <label>
            The original bug
            <textarea name="description" required maxlength="10000" rows="5"><?= e($fix['description']) ?></textarea>
        </label>
        <label>
            What changed?
            <textarea name="resolution" required maxlength="10000" rows="5"><?= e($fix['resolution']) ?></textarea>
        </label>
        <label>
            Podcast episode link
            <small>
                Include a timestamp in the URL if possible.
            </small>
            <input type="url" name="episode_url" required maxlength="2048" value="<?= e($fix['episode_url']) ?>" placeholder="https://www.youtube.com/watch?v=…&t=…">
        </label>
        <label>
            Evidence of the fix
            <small>
                A release note, support page, or other public evidence.
            </small>
            <input type="url" name="evidence_url" required maxlength="2048" value="<?= e($fix['evidence_url']) ?>" placeholder="https://…">
        </label>
        <div class="columns">
            <label>
                Mentioned on
                <input type="date" name="mentioned_on" required max="<?= date('Y-m-d') ?>" value="<?= e($fix['mentioned_on']) ?>">
            </label>
            <label>
                Fixed on
                <input type="date" name="fixed_on" required max="<?= date('Y-m-d') ?>" value="<?= e($fix['fixed_on']) ?>">
            </label>
        </div>
        <div class="actions">
            <button class="button">
                Save contribution ↗
            </button>
            <a href="account.php">
                Cancel
            </a>
        </div>
    </form>
</section>
<?php require 'includes/footer.php'; ?>
