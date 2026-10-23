<?php
require 'includes/bootstrap.php';

$q = trim((string)($_GET['q'] ?? ''));
$page = max(1, (int)($_GET['page'] ?? 1));
$where = 'f.active = 1 AND u.active = 1';
$params = [];
if ($q !== '') {
    $where .= ' AND (f.title LIKE ? OR f.product LIKE ? OR f.description LIKE ?)';
    $params = array_fill(0, 3, '%' . $q . '%');
}
$total = (int)mysqli_fetch_row(query("SELECT COUNT(*) FROM fixes f JOIN users u ON u.id=f.user_id WHERE $where", $params))[0];
$pages = max(1, (int)ceil($total / 12));
$page = min($page, $pages);
$offset = ($page-1)*12;
$fixes = mysqli_fetch_all(query("SELECT f.*, u.name FROM fixes f JOIN users u ON u.id=f.user_id WHERE $where ORDER BY f.fixed_on DESC, f.id DESC LIMIT 12 OFFSET $offset", $params), MYSQLI_ASSOC);

$pageTitle = 'The fix log';
require 'includes/header.php';
?>
<section class="hero">
    <div>
        <p class="eyebrow">
            <span class="dot">
                ●
            </span>
            THE COMMUNITY FOLLOW-UP
        </p>
        <h1>
            They asked.
            <br>
            Someone
            <em>
                fixed it.
            </em>
        </h1>
        <p class="intro">
            “Did They Even Test This?” called out the bugs.
            <br>
            We’re keeping track of what happened next.
        </p>
        <a class="button" href="edit.php">
            Contribute a fix
            <span>
                ↗
            </span>
        </a>
    </div>
    <aside class="quote">
        <span class="issue">
            THE QUESTION THAT STARTED IT
        </span>
        <p>
            “Did they
            <br>
            even test
            <br>
            <i>
                this?
            </i>
            ”
        </p>
        <span class="quote-foot">
            ↳ WAVEFORM PODCAST
        </span>
    </aside>
</section>
<section class="log">
    <div class="section-heading">
        <div>
            <p class="eyebrow">
                THE RECEIPTS, ALL IN ONE PLACE
            </p>
            <h2>
                The fix log
                <span class="count">
                    <?= $total ?>
                </span>
            </h2>
        </div>
        <p>
            Reported on the pod. Followed up by you.
        </p>
    </div>
    <form class="search" method="get">
        <label for="q">
            Search the log
        </label>
        <div>
            <input type="search" id="q" name="q" value="<?= e($q) ?>" placeholder="Search a product or bug…" maxlength="200">
            <button class="button">
                Search ↗
            </button>
            <?php if ($q !== ''): ?>
                <a href="index.php">
                    Clear
                </a>
            <?php endif; ?>
        </div>
    </form>
    <?php if (!$fixes): ?>
        <div class="empty">
            <span class="empty-symbol">
                ↳
            </span>
            <h3>
                <?= $q !== '' ? 'No matching fixes. Yet.' : 'The first receipt could be yours.' ?>
            </h3>
            <p>
                <?= $q !== '' ? 'Try another product name or a shorter search.' : 'Found a fix for a bug mentioned on the podcast? Add the episode, the story, and a source that shows it’s resolved.' ?>
            </p>
            <a href="edit.php" class="button">
                Add a fix ↗
            </a>
        </div>
    <?php endif; ?>
    <div class="fix-list">
        <?php foreach ($fixes as $fix): ?>
            <article class="fix-row">
                <div class="fix-meta">
                    <span class="badge">
                        ● FIX REPORTED
                    </span>
                    <span>
                        <?= e(date('d M Y', strtotime($fix['fixed_on']))) ?>
                    </span>
                </div>
                <div>
                    <span class="product">
                        <?= e($fix['product']) ?>
                    </span>
                    <h3>
                        <a href="fix.php?id=<?= $fix['id'] ?>">
                            <?= e($fix['title']) ?>
                            <span>
                                ↗
                            </span>
                        </a>
                    </h3>
                    <p>
                        <?= e(mb_strimwidth($fix['description'], 0, 210, '…')) ?>
                    </p>
                    <small>
                        Logged by
                        <?= e($fix['name']) ?>
                    </small>
                </div>
                <a class="read-link" href="fix.php?id=<?= $fix['id'] ?>">
                    View receipt ↗
                </a>
            </article>
        <?php endforeach; ?>
    </div>
    <?php if ($pages > 1): ?>
        <nav class="pagination" aria-label="Pagination">
            <?php if ($page > 1): ?>
                <a href="?<?= e(http_build_query(['q'=>$q,'page'=>$page-1])) ?>">
                    ← Previous
                </a>
            <?php endif; ?>
            <span>
                Page
                <?= $page ?>
                of
                <?= $pages ?>
            </span>
            <?php if ($page < $pages): ?>
                <a href="?<?= e(http_build_query(['q'=>$q,'page'=>$page+1])) ?>">
                    Next →
                </a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>
</section>
<section class="contribute">
    <span class="eyebrow">
        HELP CLOSE THE LOOP
    </span>
    <h2>
        Heard the bug.
        <br>
        Spotted the fix?
    </h2>
    <p>
        A release note. A support update. A fix you can reproduce.
        <br>
        Bring the evidence and add it to the log.
    </p>
    <a class="button" href="edit.php">
        Add your finding ↗
    </a>
</section>
<?php require 'includes/footer.php'; ?>
