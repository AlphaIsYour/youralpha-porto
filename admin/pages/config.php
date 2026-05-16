<?php
// admin/pages/config.php - Edit site configuration (config table)
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'config';
$message = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();

    $keys = ['site_title', 'site_description', 'twitter_handle', 'instagram_handle', 'github_username'];
    $stmt = $db->prepare("UPDATE config SET `value` = ? WHERE `key` = ?");
    $insertStmt = $db->prepare("INSERT INTO config (`key`, `value`) VALUES (?, ?) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

    foreach ($keys as $key) {
        $value = trim($_POST[$key] ?? '');
        $insertStmt->execute([$key, $value]);
    }

    $message = 'Configuration updated successfully.';
}

// Fetch all config as key => value
$configRows = $db->query("SELECT `key`, `value` FROM config")->fetchAll();
$config = [];
foreach ($configRows as $row) {
    $config[$row['key']] = $row['value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Config - Admin Panel</title>
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
            <a href="github.php"><span class="nav-icon">&#9733;</span> GitHub</a>
            <a href="lastseen.php"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="config.php" class="active"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="password.php"><span class="nav-icon">&#9733;</span> Password</a>
        </nav>
        <div class="sidebar-footer"><a href="../dashboard.php?action=logout">Sign Out</a></div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Site Configuration</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Config</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>General Settings</h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="site_title">Site Title</label>
                    <input type="text" id="site_title" name="site_title" value="<?= htmlspecialchars($config['site_title'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="site_description">Site Description</label>
                    <textarea id="site_description" name="site_description" rows="4"><?= htmlspecialchars($config['site_description'] ?? '') ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="twitter_handle">Twitter Handle</label>
                        <input type="text" id="twitter_handle" name="twitter_handle" value="<?= htmlspecialchars($config['twitter_handle'] ?? '') ?>" placeholder="@youralpha">
                    </div>
                    <div class="form-group">
                        <label for="instagram_handle">Instagram Handle</label>
                        <input type="text" id="instagram_handle" name="instagram_handle" value="<?= htmlspecialchars($config['instagram_handle'] ?? '') ?>" placeholder="@eno4lph_">
                    </div>
                </div>
                <div class="form-group">
                    <label for="github_username">GitHub Username</label>
                    <input type="text" id="github_username" name="github_username" value="<?= htmlspecialchars($config['github_username'] ?? '') ?>" placeholder="AlphaIsYour">
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Configuration</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
