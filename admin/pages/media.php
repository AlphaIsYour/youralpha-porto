<?php
// admin/pages/media.php - CRUD for media table (100 Things I Love)
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'media';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$validTypes = ['music', 'tv', 'article', 'book', 'youtube', 'podcast', 'theater'];

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $type = trim($_POST['type'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $link_url = trim($_POST['link_url'] ?? '');
    $link_text = trim($_POST['link_text'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($title === '' || !in_array($type, $validTypes)) {
        $error = 'Title and valid type are required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE media SET type=?, title=?, author=?, link_url=?, link_text=?, sort_order=? WHERE id=?");
            $stmt->execute([$type, $title, $author ?: null, $link_url, $link_text, $sort_order, $edit_id]);
            $message = 'Media item updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO media (type, title, author, link_url, link_text, sort_order) VALUES (?,?,?,?,?,?)");
            $stmt->execute([$type, $title, $author ?: null, $link_url, $link_text, $sort_order]);
            $message = 'Media item added.';
        }
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM media WHERE id = ?")->execute([$id]);
    $message = 'Media item deleted.';
    $action = 'list';
}

// Fetch item for editing
$item = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM media WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}

// Fetch all for list
$items = [];
if ($action === 'list') {
    $items = $db->query("SELECT * FROM media ORDER BY sort_order ASC, id ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Media - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header"><h2>Static Porto</h2><small>Admin Panel</small></div>
        <nav class="sidebar-nav">
            <a href="../dashboard.php"><span class="nav-icon">&#9632;</span> Dashboard</a>
            <a href="about.php"><span class="nav-icon">&#9733;</span> About</a>
            <a href="projects.php"><span class="nav-icon">&#9881;</span> Projects</a>
            <a href="media.php" class="active"><span class="nav-icon">&#9835;</span> Media</a>
            <a href="music.php"><span class="nav-icon">&#9836;</span> Music</a>
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
            <h1>Media (100 Things I Love)</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Media</div>
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
                <h2 style="margin-bottom:0; border:none; padding:0;">All Media Items</h2>
                <a href="?action=add" class="btn btn-primary">Add Item</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Author</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($items)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#95a5a6;">No media items yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($items as $m): ?>
                        <tr>
                            <td><?= $m['id'] ?></td>
                            <td><span class="badge badge-type"><?= htmlspecialchars($m['type']) ?></span></td>
                            <td><?= htmlspecialchars($m['title']) ?></td>
                            <td><?= htmlspecialchars($m['author'] ?? '-') ?></td>
                            <td><?= $m['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $m['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $m['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this item?')">Delete</a>
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
            <h2><?= $action === 'edit' ? 'Edit Media Item' : 'Add New Media Item' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <?php if ($item): ?>
                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="type">Type *</label>
                        <select id="type" name="type" required>
                            <option value="">Select type...</option>
                            <?php foreach ($validTypes as $t): ?>
                            <option value="<?= $t ?>" <?= ($item['type'] ?? $_POST['type'] ?? '') === $t ? 'selected' : '' ?>><?= ucfirst($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($item['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($item['title'] ?? $_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="author">Author</label>
                    <input type="text" id="author" name="author" value="<?= htmlspecialchars($item['author'] ?? $_POST['author'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="link_url">Link URL</label>
                        <input type="url" id="link_url" name="link_url" value="<?= htmlspecialchars($item['link_url'] ?? $_POST['link_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="link_text">Link Text</label>
                        <input type="text" id="link_text" name="link_text" value="<?= htmlspecialchars($item['link_text'] ?? $_POST['link_text'] ?? '') ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Item</button>
                    <a href="media.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
