<?php
// admin/pages/timeline.php - CRUD for timeline table
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'timeline';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $date_range = trim($_POST['date_range'] ?? '');
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type_label = trim($_POST['type_label'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($title === '') {
        $error = 'Title is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE timeline SET date_range=?, title=?, description=?, type_label=?, sort_order=? WHERE id=?");
            $stmt->execute([$date_range, $title, $description, $type_label, $sort_order, $edit_id]);
            $message = 'Timeline entry updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO timeline (date_range, title, description, type_label, sort_order) VALUES (?,?,?,?,?)");
            $stmt->execute([$date_range, $title, $description, $type_label, $sort_order]);
            $message = 'Timeline entry added.';
        }
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM timeline WHERE id = ?")->execute([$id]);
    $message = 'Timeline entry deleted.';
    $action = 'list';
}

// Fetch for editing
$entry = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM timeline WHERE id = ?");
    $stmt->execute([$id]);
    $entry = $stmt->fetch();
}

// Fetch all for list
$entries = [];
if ($action === 'list') {
    $entries = $db->query("SELECT * FROM timeline ORDER BY sort_order ASC, id ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timeline - Admin Panel</title>
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
            <a href="writing.php"><span class="nav-icon">&#9998;</span> Writing</a>
            <a href="timeline.php" class="active"><span class="nav-icon">&#8986;</span> Timeline</a>
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
            <h1>Timeline</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Timeline</div>
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
                <h2 style="margin-bottom:0; border:none; padding:0;">All Timeline Entries</h2>
                <a href="?action=add" class="btn btn-primary">Add Entry</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Date Range</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Type</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($entries)): ?>
                        <tr><td colspan="7" style="text-align:center; color:#95a5a6;">No timeline entries yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($entries as $e): ?>
                        <tr>
                            <td><?= $e['id'] ?></td>
                            <td><?= htmlspecialchars($e['date_range'] ?? '-') ?></td>
                            <td><strong><?= htmlspecialchars($e['title']) ?></strong></td>
                            <td><?= htmlspecialchars(mb_strimwidth($e['description'] ?? '', 0, 50, '...')) ?></td>
                            <td><span class="badge badge-type"><?= htmlspecialchars($e['type_label'] ?? '-') ?></span></td>
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
            <h2><?= $action === 'edit' ? 'Edit Timeline Entry' : 'Add New Timeline Entry' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <?php if ($entry): ?>
                    <input type="hidden" name="id" value="<?= $entry['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="date_range">Date Range</label>
                        <input type="text" id="date_range" name="date_range" value="<?= htmlspecialchars($entry['date_range'] ?? $_POST['date_range'] ?? '') ?>" placeholder="2023 - Present">
                    </div>
                    <div class="form-group">
                        <label for="type_label">Type Label</label>
                        <input type="text" id="type_label" name="type_label" value="<?= htmlspecialchars($entry['type_label'] ?? $_POST['type_label'] ?? '') ?>" placeholder="student, work, life">
                    </div>
                </div>
                <div class="form-group">
                    <label for="title">Title *</label>
                    <input type="text" id="title" name="title" required value="<?= htmlspecialchars($entry['title'] ?? $_POST['title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($entry['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                    <label for="sort_order">Sort Order</label>
                    <input type="number" id="sort_order" name="sort_order" value="<?= (int)($entry['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>" style="max-width:200px;">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Entry</button>
                    <a href="timeline.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
