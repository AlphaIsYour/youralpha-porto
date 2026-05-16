<?php
// admin/pages/projects.php - CRUD for projects table
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'projects';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST - save (add or edit)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $icon_class = trim($_POST['icon_class'] ?? '');
    $icon_color = trim($_POST['icon_color'] ?? '');
    $is_featured = isset($_POST['is_featured']) ? 1 : 0;
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($name === '') {
        $error = 'Project name is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE projects SET name=?, description=?, url=?, icon_class=?, icon_color=?, is_featured=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $description, $url, $icon_class, $icon_color, $is_featured, $sort_order, $edit_id]);
            $message = 'Project updated successfully.';
        } else {
            $stmt = $db->prepare("INSERT INTO projects (name, description, url, icon_class, icon_color, is_featured, sort_order) VALUES (?,?,?,?,?,?,?)");
            $stmt->execute([$name, $description, $url, $icon_class, $icon_color, $is_featured, $sort_order]);
            $message = 'Project added successfully.';
        }
        $action = 'list';
    }
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM projects WHERE id = ?")->execute([$id]);
    $message = 'Project deleted.';
    $action = 'list';
}

// Fetch project for editing
$project = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $project = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $project->execute([$id]);
    $project = $project->fetch();
}

// Fetch all for list view
$projects = [];
if ($action === 'list') {
    $projects = $db->query("SELECT * FROM projects ORDER BY sort_order ASC, id ASC")->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header"><h2>Static Porto</h2><small>Admin Panel</small></div>
        <nav class="sidebar-nav">
            <a href="../dashboard.php"><span class="nav-icon">&#9632;</span> Dashboard</a>
            <a href="about.php"><span class="nav-icon">&#9733;</span> About</a>
            <a href="projects.php" class="active"><span class="nav-icon">&#9881;</span> Projects</a>
            <a href="media.php"><span class="nav-icon">&#9835;</span> Media</a>
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
            <h1>Projects</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Projects</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- List View -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin-bottom:0; border:none; padding:0;">All Projects</h2>
                <a href="?action=add" class="btn btn-primary">Add Project</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Featured</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($projects)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#95a5a6;">No projects yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($projects as $p): ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td><strong><?= htmlspecialchars($p['name']) ?></strong></td>
                            <td><?= htmlspecialchars(mb_strimwidth($p['description'] ?? '', 0, 60, '...')) ?></td>
                            <td><?= $p['is_featured'] ? '<span class="badge badge-featured">Featured</span>' : '-' ?></td>
                            <td><?= $p['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $p['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $p['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this project?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Form -->
        <div class="card">
            <h2><?= $action === 'edit' ? 'Edit Project' : 'Add New Project' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <?php if ($project): ?>
                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Project Name *</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($project['name'] ?? $_POST['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="url">URL</label>
                        <input type="url" id="url" name="url" value="<?= htmlspecialchars($project['url'] ?? $_POST['url'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($project['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="icon_class">Icon Class</label>
                        <input type="text" id="icon_class" name="icon_class" value="<?= htmlspecialchars($project['icon_class'] ?? $_POST['icon_class'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="icon_color">Icon Color</label>
                        <input type="text" id="icon_color" name="icon_color" value="<?= htmlspecialchars($project['icon_color'] ?? $_POST['icon_color'] ?? '') ?>" placeholder="#3498db">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($project['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_featured" <?= !empty($project['is_featured'] ?? $_POST['is_featured'] ?? 0) ? 'checked' : '' ?>>
                            Featured project
                        </label>
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Project</button>
                    <a href="projects.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
