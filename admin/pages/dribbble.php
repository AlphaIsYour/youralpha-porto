<?php
// admin/pages/dribbble.php - CRUD for dribbble table (single record)
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'dribbble';
$message = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $profile_url = trim($_POST['profile_url'] ?? '');
    $display_name = trim($_POST['display_name'] ?? '');
    $placeholder_text = trim($_POST['placeholder_text'] ?? '');

    $existing = $db->query("SELECT id FROM dribbble LIMIT 1")->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE dribbble SET profile_url=?, display_name=?, placeholder_text=? WHERE id=?");
        $stmt->execute([$profile_url, $display_name, $placeholder_text, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO dribbble (profile_url, display_name, placeholder_text) VALUES (?,?,?)");
        $stmt->execute([$profile_url, $display_name, $placeholder_text]);
    }

    $message = 'Dribbble settings updated.';
}

// Fetch current data
$dribbble = $db->query("SELECT * FROM dribbble LIMIT 1")->fetch() ?: [
    'profile_url' => '', 'display_name' => '', 'placeholder_text' => ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dribbble - Admin Panel</title>
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
            <a href="dribbble.php" class="active"><span class="nav-icon">&#9673;</span> Dribbble</a>
            <a href="instagram.php"><span class="nav-icon">&#9744;</span> Instagram</a>
            <a href="github.php"><span class="nav-icon">&#9733;</span> GitHub</a>
            <a href="lastseen.php"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="../setup-totp.php"><span class="nav-icon">&#9733;</span> Authenticator</a>
        </nav>
        <div class="sidebar-footer"><a href="../dashboard.php?action=logout">Sign Out</a></div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Dribbble</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Dribbble</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Edit Dribbble Settings</h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="profile_url">Profile URL</label>
                        <input type="url" id="profile_url" name="profile_url" value="<?= htmlspecialchars($dribbble['profile_url']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="display_name">Display Name</label>
                        <input type="text" id="display_name" name="display_name" value="<?= htmlspecialchars($dribbble['display_name']) ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="placeholder_text">Placeholder Text</label>
                    <textarea id="placeholder_text" name="placeholder_text" rows="4"><?= htmlspecialchars($dribbble['placeholder_text']) ?></textarea>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
