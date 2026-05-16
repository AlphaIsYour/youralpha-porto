<?php
// admin/pages/writing.php - CRUD for writing table
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'writing';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $title = trim($_POST['title'] ?? '');
    $year = trim($_POST['year'] ?? '');
    $icon_class = trim($_POST['icon_class'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($title === '') {
        $error = 'Title is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE writing SET title=?, year=?, icon_class=?, link_url=?, sort_order=? WHERE id=?");
            $stmt->execute([$title, $year, $icon_class, $link_url, $sort_order, $edit_id]);
            $message = 'Entry updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO writing (title, year, icon_class, link_url, sort_order) VALUES (?,?,?,?,?)");
            $stmt->execute([$title, $year, $icon_class, $link_url, $sort_order]);
            $message = 'Entry added.';
        }
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM writing WHERE id = ?")->execute([$id]);
    $message = 'Entry deleted.';
    $action = 'list';
}

// Fetch for editing
$entry = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM writing WHERE id = ?");
    $stmt->execute([$id]);
    $entry = $stmt->fetch();
}

// Fetch all for list
$entries = [];
if ($action === 'list') {
    $entries = $db->query("SELECT * FROM writing ORDER BY sort_order ASC, id ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Writing - Admin Panel</title>
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
            <a href="tweets.php"><span class="nav-icon">&#9993;</span> Tweets</a>
            <a href="writing.php" class="active"><span class="nav-icon">&#9998;</span> Writing</a>
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
            <h1>Writing</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Writing</div>
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
                <h2 style="margin-bottom:0; border:none; padding:0;">All Entries</h2>
                <a href="?action=add" class="btn btn-primary">Add Entry</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Year</th>
                            <th>Icon</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#95a5a6;">No entries yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td>
                                <?php if ($e['link_url']): ?>
                                    <a href="<?= htmlspecialchars($e['link_url']) ?>" target="_blank"><?= htmlspecialchars($e['title']) ?></a>
                                <?php else: ?>
                                    <?= htmlspecialchars($e['title']) ?>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars($e['year'] ?? '-') ?></td>
                            <td><code><?= htmlspecialchars($e['icon_class'] ?? '-') ?></code></td>
                            <td><?= $e['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $e['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $e['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this entry?')">Delete</a>
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
            <h2><?= $action === 'edit' ? 'Edit Entry' : 'Add New Entry' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <?php if ($entry): ?>
                    <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($entry['title'] ?? $_POST['title'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="year">Year</label>
                        <input type="text" id="year" name="year" value="<?= htmlspecialchars($entry['year'] ?? $_POST['year'] ?? '') ?>" placeholder="2024 or 2023 - Present">
                    </div>
                    <div class="form-group">
                        <label for="icon_class">Icon Class</label>
                        <input type="text" id="icon_class" name="icon_class" value="<?= htmlspecialchars($entry['icon_class'] ?? $_POST['icon_class'] ?? '') ?>" placeholder="fa-code">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="link_url">Link URL</label>
                        <input type="url" id="link_url" name="link_url" value="<?= htmlspecialchars($entry['link_url'] ?? $_POST['link_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($entry['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                    <a href="writing.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
