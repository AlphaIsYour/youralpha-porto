<?php
// admin/pages/about.php - CRUD for about table (single record)
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'about';
$message = '';

// Handle POST - update the about record
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $bio_text = trim($_POST['bio_text'] ?? '');
    $github_url = trim($_POST['github_url'] ?? '');
    $github_btn_text = trim($_POST['github_btn_text'] ?? '');

    // Check if record exists
    $existing = $db->query("SELECT id FROM about LIMIT 1")->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE about SET bio_text = ?, github_url = ?, github_btn_text = ? WHERE id = ?");
        $stmt->execute([$bio_text, $github_url, $github_btn_text, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO about (bio_text, github_url, github_btn_text) VALUES (?, ?, ?)");
        $stmt->execute([$bio_text, $github_url, $github_btn_text]);
    }

    $message = 'About section updated successfully.';
}

// Fetch current data
$about = $db->query("SELECT * FROM about LIMIT 1")->fetch() ?: [
    'bio_text' => '', 'github_url' => '', 'github_btn_text' => ''
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About - Admin Panel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Static Porto</h2>
            <small>Admin Panel</small>
        </div>
        <nav class="sidebar-nav">
            <a href="../dashboard.php"><span class="nav-icon">&#9632;</span> Dashboard</a>
            <a href="about.php" class="active"><span class="nav-icon">&#9733;</span> About</a>
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
            <a href="config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="../setup-totp.php"><span class="nav-icon">&#9733;</span> Authenticator</a>
        </nav>
        <div class="sidebar-footer">
            <a href="../dashboard.php?action=logout">Sign Out</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>About Me</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / About</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Edit About Section</h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="form-group">
                    <label for="bio_text">Bio Text</label>
                    <textarea id="bio_text" name="bio_text" rows="6"><?= htmlspecialchars($about['bio_text']) ?></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="github_url">GitHub URL</label>
                        <input type="url" id="github_url" name="github_url" value="<?= htmlspecialchars($about['github_url']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="github_btn_text">GitHub Button Text</label>
                        <input type="text" id="github_btn_text" name="github_btn_text" value="<?= htmlspecialchars($about['github_btn_text']) ?>">
                    </div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
