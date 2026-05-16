<?php
// admin/pages/tweets.php - CRUD for tweets table + twitter_profile
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'tweets';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
}

// Handle POST - save tweet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'tweet') {
    $tweet_text = trim($_POST['tweet_text'] ?? '');
    $tweet_date = trim($_POST['tweet_date'] ?? '');
    $retweets = (int)($_POST['retweets'] ?? 0);
    $likes = (int)($_POST['likes'] ?? 0);
    $tweet_url = trim($_POST['tweet_url'] ?? '');
    $images_raw = trim($_POST['images'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($tweet_text === '') {
        $error = 'Tweet text is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        // Validate images JSON if provided
        $images = null;
        if ($images_raw !== '') {
            $decoded = json_decode($images_raw, true);
            if ($decoded === null && json_last_error() !== JSON_ERROR_NONE) {
                $error = 'Images field must be valid JSON array.';
                $action = $edit_id ? 'edit' : 'add';
            } else {
                $images = $images_raw;
            }
        }

        if (!$error) {
            if ($edit_id > 0) {
                $stmt = $db->prepare("UPDATE tweets SET tweet_text=?, tweet_date=?, retweets=?, likes=?, tweet_url=?, images=?, sort_order=? WHERE id=?");
                $stmt->execute([$tweet_text, $tweet_date, $retweets, $likes, $tweet_url, $images, $sort_order, $edit_id]);
                $message = 'Tweet updated.';
            } else {
                $stmt = $db->prepare("INSERT INTO tweets (tweet_text, tweet_date, retweets, likes, tweet_url, images, sort_order) VALUES (?,?,?,?,?,?,?)");
                $stmt->execute([$tweet_text, $tweet_date, $retweets, $likes, $tweet_url, $images, $sort_order]);
                $message = 'Tweet added.';
            }
            $action = 'list';
        }
    }
}

// Handle POST - save twitter profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'profile') {
    $username = trim($_POST['username'] ?? '');
    $followers = (int)($_POST['followers'] ?? 0);
    $tweet_count = (int)($_POST['tweet_count'] ?? 0);
    $profile_id = (int)($_POST['profile_id'] ?? 0);

    if ($profile_id > 0) {
        $stmt = $db->prepare("UPDATE twitter_profile SET username=?, followers=?, tweet_count=? WHERE id=?");
        $stmt->execute([$username, $followers, $tweet_count, $profile_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO twitter_profile (username, followers, tweet_count) VALUES (?,?,?)");
        $stmt->execute([$username, $followers, $tweet_count]);
    }
    $message = 'Twitter profile updated.';
    $action = 'profile';
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM tweets WHERE id = ?")->execute([$id]);
    $message = 'Tweet deleted.';
    $action = 'list';
}

// Fetch tweet for editing
$tweet = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM tweets WHERE id = ?");
    $stmt->execute([$id]);
    $tweet = $stmt->fetch();
}

// Fetch all tweets for list
$tweets = [];
if ($action === 'list') {
    $tweets = $db->query("SELECT * FROM tweets ORDER BY sort_order ASC, id ASC")->fetchAll();
}

// Fetch twitter profile
$twitterProfile = $db->query("SELECT * FROM twitter_profile LIMIT 1")->fetch() ?: [
    'id' => 0, 'username' => '', 'followers' => 0, 'tweet_count' => 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tweets - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header"><h2>Static Porto</h2><small>Admin Panel</small></div>
        <nav class="sidebar-nav">
            <a href="../dashboard.php"><span class="nav-icon">&#9632;</span> Dashboard</a>
            <a href="about.php"><span class="nav-icon">&#9733;</span> About</a>
            <a href="projects.php"><span class="nav-icon">&#9881;</span> Projects</a>
            <a href="media.php"><span class="nav-icon">&#9835;</span> Media</a>
            <a href="music.php"><span class="nav-icon">&#9836;</span> Music</a>
            <a href="tweets.php" class="active"><span class="nav-icon">&#9993;</span> Tweets</a>
            <a href="writing.php"><span class="nav-icon">&#9998;</span> Writing</a>
            <a href="timeline.php"><span class="nav-icon">&#8986;</span> Timeline</a>
            <a href="dribbble.php"><span class="nav-icon">&#9673;</span> Dribbble</a>
            <a href="instagram.php"><span class="nav-icon">&#9744;</span> Instagram</a>
            <a href="github.php"><span class="nav-icon">&#9733;</span> GitHub</a>
            <a href="lastseen.php"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="password.php"><span class="nav-icon">&#9733;</span> Password</a>
        </nav>
        <div class="sidebar-footer"><a href="../dashboard.php?action=logout">Sign Out</a></div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Tweets</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Tweets</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- Twitter Profile Section -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin-bottom:0; border:none; padding:0;">Twitter Profile</h2>
                <a href="?action=profile" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
            <p><strong>Username:</strong> <?= htmlspecialchars($twitterProfile['username'] ?? '-') ?></p>
            <p><strong>Followers:</strong> <?= number_format($twitterProfile['followers'] ?? 0) ?></p>
            <p><strong>Tweet Count:</strong> <?= number_format($twitterProfile['tweet_count'] ?? 0) ?></p>
        </div>

        <!-- Tweets List -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin-bottom:0; border:none; padding:0;">All Tweets</h2>
                <a href="?action=add" class="btn btn-primary">Add Tweet</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tweet</th>
                            <th>Date</th>
                            <th>Retweets</th>
                            <th>Likes</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tweets)): ?>
                        <tr><td colspan="7" style="text-align:center; color:#95a5a6;">No tweets yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($tweets as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><?= htmlspecialchars(mb_strimwidth($t['tweet_text'] ?? '', 0, 50, '...')) ?></td>
                            <td><?= htmlspecialchars($t['tweet_date'] ?? '-') ?></td>
                            <td><?= number_format($t['retweets']) ?></td>
                            <td><?= number_format($t['likes']) ?></td>
                            <td><?= $t['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this tweet?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Tweet -->
        <div class="card">
            <h2><?= $action === 'edit' ? 'Edit Tweet' : 'Add New Tweet' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="form_type" value="tweet">
                <?php if ($tweet): ?>
                    <input type="hidden" name="id" value="<?= $tweet['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="tweet_text">Tweet Text *</label>
                    <textarea id="tweet_text" name="tweet_text" rows="4" required><?= htmlspecialchars($tweet['tweet_text'] ?? $_POST['tweet_text'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="tweet_date">Tweet Date</label>
                        <input type="text" id="tweet_date" name="tweet_date" value="<?= htmlspecialchars($tweet['tweet_date'] ?? $_POST['tweet_date'] ?? '') ?>" placeholder="10 March 2019">
                    </div>
                    <div class="form-group">
                        <label for="tweet_url">Tweet URL</label>
                        <input type="url" id="tweet_url" name="tweet_url" value="<?= htmlspecialchars($tweet['tweet_url'] ?? $_POST['tweet_url'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="retweets">Retweets</label>
                        <input type="number" id="retweets" name="retweets" value="<?= (int)($tweet['retweets'] ?? $_POST['retweets'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label for="likes">Likes</label>
                        <input type="number" id="likes" name="likes" value="<?= (int)($tweet['likes'] ?? $_POST['likes'] ?? 0) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($tweet['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="images">Images (JSON array)</label>
                    <textarea id="images" name="images" rows="3" placeholder='["https://example.com/img1.jpg", "https://example.com/img2.jpg"]'><?= htmlspecialchars($tweet['images'] ?? $_POST['images'] ?? '') ?></textarea>
                    <div class="hint">Enter a JSON array of image URLs, e.g. ["url1", "url2"]</div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Tweet</button>
                    <a href="tweets.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <?php elseif ($action === 'profile'): ?>
        <!-- Edit Twitter Profile -->
        <div class="card">
            <h2>Edit Twitter Profile</h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="form_type" value="profile">
                <input type="hidden" name="profile_id" value="<?= $twitterProfile['id'] ?>">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" id="username" name="username" value="<?= htmlspecialchars($twitterProfile['username'] ?? '') ?>" placeholder="@youralpha">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="followers">Followers</label>
                        <input type="number" id="followers" name="followers" value="<?= (int)($twitterProfile['followers'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label for="tweet_count">Tweet Count</label>
                        <input type="number" id="tweet_count" name="tweet_count" value="<?= (int)($twitterProfile['tweet_count'] ?? 0) ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                    <a href="tweets.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
