<?php
// admin/pages/music.php - CRUD for music table
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'music';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $song = trim($_POST['song'] ?? '');
    $artist = trim($_POST['artist'] ?? '');
    $spotify_url = trim($_POST['spotify_url'] ?? '');
    $played_at = trim($_POST['played_at'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($song === '') {
        $error = 'Song name is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        $played_at_val = $played_at !== '' ? $played_at : null;
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE music SET song=?, artist=?, spotify_url=?, played_at=?, sort_order=? WHERE id=?");
            $stmt->execute([$song, $artist, $spotify_url, $played_at_val, $sort_order, $edit_id]);
            $message = 'Track updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO music (song, artist, spotify_url, played_at, sort_order) VALUES (?,?,?,?,?)");
            $stmt->execute([$song, $artist, $spotify_url, $played_at_val, $sort_order]);
            $message = 'Track added.';
        }
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM music WHERE id = ?")->execute([$id]);
    $message = 'Track deleted.';
    $action = 'list';
}

// Fetch for editing
$track = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM music WHERE id = ?");
    $stmt->execute([$id]);
    $track = $stmt->fetch();
}

// Fetch all for list
$tracks = [];
if ($action === 'list') {
    $tracks = $db->query("SELECT * FROM music ORDER BY sort_order ASC, id ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Music - Admin Panel</title>
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
            <a href="music.php" class="active"><span class="nav-icon">&#9836;</span> Music</a>
            <a href="tweets.php"><span class="nav-icon">&#9993;</span> Tweets</a>
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
            <h1>Music</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Music</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin-bottom:0; border:none; padding:0;">All Tracks</h2>
                <a href="?action=add" class="btn btn-primary">Add Track</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Song</th>
                            <th>Artist</th>
                            <th>Played At</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($tracks)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#95a5a6;">No tracks yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($tracks as $t): ?>
                        <tr>
                            <td><?= $t['id'] ?></td>
                            <td><strong><?= htmlspecialchars($t['song']) ?></strong></td>
                            <td><?= htmlspecialchars($t['artist'] ?? '-') ?></td>
                            <td><?= $t['played_at'] ? htmlspecialchars($t['played_at']) : '-' ?></td>
                            <td><?= $t['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $t['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $t['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this track?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <div class="card">
            <h2><?= $action === 'edit' ? 'Edit Track' : 'Add New Track' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <?php if ($track): ?>
                    <input type="hidden" name="id" value="<?= $track['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="song">Song Name *</label>
                        <input type="text" id="song" name="song" required value="<?= htmlspecialchars($track['song'] ?? $_POST['song'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="artist">Artist</label>
                        <input type="text" id="artist" name="artist" value="<?= htmlspecialchars($track['artist'] ?? $_POST['artist'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="spotify_url">Spotify URL</label>
                    <input type="url" id="spotify_url" name="spotify_url" value="<?= htmlspecialchars($track['spotify_url'] ?? $_POST['spotify_url'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="played_at">Played At</label>
                        <?php
                            $playedVal = $track['played_at'] ?? $_POST['played_at'] ?? '';
                            if ($playedVal) {
                                try {
                                    $dt = new DateTime($playedVal);
                                    $playedVal = $dt->format('Y-m-d\TH:i');
                                } catch (Exception $e) {}
                            }
                        ?>
                        <input type="datetime-local" id="played_at" name="played_at" value="<?= htmlspecialchars($playedVal) ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($track['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Track</button>
                    <a href="music.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
