<?php
// admin/pages/github.php - CRUD for github_repos and github_profile tables
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'github';
$message = '';
$error = '';

$action = $_GET['action'] ?? 'list';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Handle POST - save repo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
}

// Handle POST - save repo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'repo') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $stars = (int)($_POST['stars'] ?? 0);
    $sort_order = (int)($_POST['sort_order'] ?? 0);
    $edit_id = (int)($_POST['id'] ?? 0);

    if ($name === '') {
        $error = 'Repository name is required.';
        $action = $edit_id ? 'edit' : 'add';
    } else {
        if ($edit_id > 0) {
            $stmt = $db->prepare("UPDATE github_repos SET name=?, description=?, url=?, stars=?, sort_order=? WHERE id=?");
            $stmt->execute([$name, $description, $url, $stars, $sort_order, $edit_id]);
            $message = 'Repository updated.';
        } else {
            $stmt = $db->prepare("INSERT INTO github_repos (name, description, url, stars, sort_order) VALUES (?,?,?,?,?)");
            $stmt->execute([$name, $description, $url, $stars, $sort_order]);
            $message = 'Repository added.';
        }
        $action = 'list';
    }
}

// Handle POST - save profile
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form_type'] ?? '') === 'profile') {
    $username = trim($_POST['gh_username'] ?? '');
    $joined_date = trim($_POST['joined_date'] ?? '');
    $total_repos = (int)($_POST['total_repos'] ?? 0);
    $profile_id = (int)($_POST['profile_id'] ?? 0);

    if ($profile_id > 0) {
        $stmt = $db->prepare("UPDATE github_profile SET username=?, joined_date=?, total_repos=? WHERE id=?");
        $stmt->execute([$username, $joined_date, $total_repos, $profile_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO github_profile (username, joined_date, total_repos) VALUES (?,?,?)");
        $stmt->execute([$username, $joined_date, $total_repos]);
    }
    $message = 'GitHub profile updated.';
    $action = 'profile';
}

// Handle delete
if ($action === 'delete' && $id > 0) {
    $db->prepare("DELETE FROM github_repos WHERE id = ?")->execute([$id]);
    $message = 'Repository deleted.';
    $action = 'list';
}

// Fetch repo for editing
$repo = null;
if (($action === 'edit' || $action === 'add') && $id > 0) {
    $stmt = $db->prepare("SELECT * FROM github_repos WHERE id = ?");
    $stmt->execute([$id]);
    $repo = $stmt->fetch();
}

// Fetch all repos for list
$repos = [];
if ($action === 'list') {
    $repos = $db->query("SELECT * FROM github_repos ORDER BY sort_order ASC, id ASC")->fetchAll();
}

// Fetch github profile
$ghProfile = $db->query("SELECT * FROM github_profile LIMIT 1")->fetch() ?: [
    'id' => 0, 'username' => '', 'joined_date' => '', 'total_repos' => 0
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GitHub - Admin Panel</title>
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
            <a href="instagram.php"><span class="nav-icon">&#9744;</span> Instagram</a>
            <a href="github.php" class="active"><span class="nav-icon">&#9733;</span> GitHub</a>
            <a href="lastseen.php"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="../setup-totp.php"><span class="nav-icon">&#9733;</span> Authenticator</a>
        </nav>
        <div class="sidebar-footer"><a href="../dashboard.php?action=logout">Sign Out</a></div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>GitHub</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / GitHub</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if ($action === 'list'): ?>
        <!-- GitHub Profile Section -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin-bottom:0; border:none; padding:0;">GitHub Profile</h2>
                <a href="?action=profile" class="btn btn-primary btn-sm">Edit Profile</a>
            </div>
            <p><strong>Username:</strong> <?= htmlspecialchars($ghProfile['username'] ?? '-') ?></p>
            <p><strong>Joined:</strong> <?= htmlspecialchars($ghProfile['joined_date'] ?? '-') ?></p>
            <p><strong>Total Repos:</strong> <?= (int)($ghProfile['total_repos'] ?? 0) ?></p>
        </div>

        <!-- Repos List -->
        <div class="card">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                <h2 style="margin-bottom:0; border:none; padding:0;">Repositories</h2>
                <a href="?action=add" class="btn btn-primary">Add Repo</a>
            </div>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Stars</th>
                            <th>Order</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($repos)): ?>
                        <tr><td colspan="6" style="text-align:center; color:#95a5a6;">No repositories yet.</td></tr>
                        <?php else: ?>
                        <?php foreach ($repos as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td>
                                <?php if ($r['url']): ?>
                                    <a href="<?= htmlspecialchars($r['url']) ?>" target="_blank"><strong><?= htmlspecialchars($r['name']) ?></strong></a>
                                <?php else: ?>
                                    <strong><?= htmlspecialchars($r['name']) ?></strong>
                                <?php endif; ?>
                            </td>
                            <td><?= htmlspecialchars(mb_strimwidth($r['description'] ?? '', 0, 60, '...')) ?></td>
                            <td><?= $r['stars'] ?></td>
                            <td><?= $r['sort_order'] ?></td>
                            <td class="actions">
                                <a href="?action=edit&id=<?= $r['id'] ?>" class="btn btn-primary btn-sm">Edit</a>
                                <a href="?action=delete&id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this repo?')">Delete</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php elseif ($action === 'add' || $action === 'edit'): ?>
        <!-- Add / Edit Repo -->
        <div class="card">
            <h2><?= $action === 'edit' ? 'Edit Repository' : 'Add New Repository' ?></h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="form_type" value="repo">
                <?php if ($repo): ?>
                    <input type="hidden" name="id" value="<?= $repo['id'] ?>">
                <?php endif; ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="name">Repository Name *</label>
                        <input type="text" id="name" name="name" required value="<?= htmlspecialchars($repo['name'] ?? $_POST['name'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label for="url">Repository URL</label>
                        <input type="url" id="url" name="url" value="<?= htmlspecialchars($repo['url'] ?? $_POST['url'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3"><?= htmlspecialchars($repo['description'] ?? $_POST['description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="stars">Stars</label>
                        <input type="number" id="stars" name="stars" value="<?= (int)($repo['stars'] ?? $_POST['stars'] ?? 0) ?>">
                    </div>
                    <div class="form-group">
                        <label for="sort_order">Sort Order</label>
                        <input type="number" id="sort_order" name="sort_order" value="<?= (int)($repo['sort_order'] ?? $_POST['sort_order'] ?? 0) ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Repository</button>
                    <a href="github.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>

        <?php elseif ($action === 'profile'): ?>
        <!-- Edit GitHub Profile -->
        <div class="card">
            <h2>Edit GitHub Profile</h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <input type="hidden" name="form_type" value="profile">
                <input type="hidden" name="profile_id" value="<?= $ghProfile['id'] ?>">
                <div class="form-group">
                    <label for="gh_username">Username</label>
                    <input type="text" id="gh_username" name="gh_username" value="<?= htmlspecialchars($ghProfile['username'] ?? '') ?>">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="joined_date">Joined Date</label>
                        <input type="text" id="joined_date" name="joined_date" value="<?= htmlspecialchars($ghProfile['joined_date'] ?? '') ?>" placeholder="Aug 2020">
                    </div>
                    <div class="form-group">
                        <label for="total_repos">Total Repos</label>
                        <input type="number" id="total_repos" name="total_repos" value="<?= (int)($ghProfile['total_repos'] ?? 0) ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Profile</button>
                    <a href="github.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
        <?php endif; ?>
    </main>
</body>
</html>
