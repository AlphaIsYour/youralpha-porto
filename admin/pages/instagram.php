<?php
// admin/pages/instagram.php - CRUD for instagram table
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'instagram';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $image_url = trim($_POST['image_url'] ?? '');
    $post_url = trim($_POST['post_url'] ?? '');
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($image_url === '') {
        $error = 'Image URL is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE instagram SET image_url=?, post_url=?, sort_order=? WHERE id=?");
            $stmt->execute([$image_url, $post_url, $sort_order, $edit_id]);
            $message = 'Post updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO instagram (image_url, post_url, sort_order) VALUES (?,?,?)");
            $stmt->execute([$image_url, $post_url, $sort_order]);
            $message = 'Post added.';
        }
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM instagram WHERE id = ?")->execute([$id]);
    $message = 'Post deleted.';
    $action = 'list';
}

// Fetch for editing
$post = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM instagram WHERE id = ?");
    $stmt->execute([$id]);
    $post = $stmt->fetch();
}

// Fetch all for list
$posts = [];
if ($action === 'list') {
    $posts = $db->query("SELECT * FROM instagram ORDER BY sort_order ASC, id ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instagram - Admin Panel</title>
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
            <a href="timeline.php"><span class="nav-icon">&#8986;</span> Timeline</a>
            <a href="dribbble.php"><span class="nav-icon">&#9673;</span> Dribbble</a>
            <a href="instagram.php" class="active"><span class="nav-icon">&#9744;</span> Instagram</a>
            <a href="github.php"><span class="nav-icon">&#9733;</span> GitHub</a>
            <a href="lastseen.php"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="../setup-totp.php"><span class="nav-icon">&#9733;</span> Authenticator</a>
        </nav>
        <div class="sidebar-footer"><a href="../dashboard.php?action=logout">Sign Out</a></div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Instagram</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Instagram</div>
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
                <h2 style="margin-bottom:0; border:none; padding:0;">All Instagram Posts</h2>
                <a href="?action=add" class="btn btn-primary">Add Post</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Image</th>
                            <th>Post URL</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($posts)): ?>
                        <tr><td colspan="5" style="text-align:center; color:#95a5a6;">No posts yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($posts as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($p['image_url']) ?>" alt="" style="width:60px; height:60px; object-fit:cover; border-radius:4px;">
                            </td>
                            <td>
                                <?php if ($p['post_url']): ?>
                                    <a href="<?= htmlspecialchars($p['post_url']) ?>" target="_blank"><?= htmlspecialchars(mb_strimwidth($p['post_url'], 0, 50, '...')) ?></a>
                                <?php else: ?>
                                    -
                                <?php endif; ?>
                            </td>
                            <td><?= $p['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this post?')">Delete</a>
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
            <h2><?= $action === 'edit' ? 'Edit Instagram Post' : 'Add New Instagram Post' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <?php if ($post): ?>
                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label for="image_url">Image URL *</label>
                    <input type="url" id="image_url" name="image_url" required value="<?= htmlspecialchars($post['image_url'] ?? $_POST['image_url'] ?? '') ?>">
                    <div class="hint">Path to the image file or external URL</div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="post_url">Post URL</label>
                        <input type="url" id="post_url" name="post_url" value="<?= htmlspecialchars($post['post_url'] ?? $_POST['post_url'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($post['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <?php if (!empty($post['image_url'])): ?>
                <div class="form-group">
                    <label>Current Image</label>
                    <div><img src="<?= htmlspecialchars($post['image_url']) ?>" alt="" style="max-width:200px; border-radius:6px;"></div>
                </div>
                <?php endif; ?>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Post</button>
                    <a href="instagram.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
