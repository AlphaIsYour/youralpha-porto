<?php
// admin/dashboard.php - Main dashboard
require_once __DIR__ . '/auth.php';
requireLogin();

$db = getDB();

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
}

// Gather stats from each table
$tables = [
    'projects'     => 'Projects',
    'media'        => 'Media Items',
    'music'        => 'Music Tracks',
    'tweets'       => 'Tweets',
    'writing'      => 'Writing',
    'timeline'     => 'Timeline',
    'instagram'    => 'Instagram Posts',
    'github_repos' => 'GitHub Repos',
];

$stats = [];
foreach ($tables as $table => $label) {
    $count = $db->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $stats[] = ['label' => $label, 'count' => $count];
}

// Current page for active nav highlighting
$currentPage = 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin Panel</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <h2>Static Porto</h2>
            <small>Admin Panel</small>
        </div>
        <nav class="sidebar-nav">
            <a href="dashboard.php" class="active"><span class="nav-icon">&#9632;</span> Dashboard</a>
            <a href="pages/about.php"><span class="nav-icon">&#9733;</span> About</a>
            <a href="pages/projects.php"><span class="nav-icon">&#9881;</span> Projects</a>
            <a href="pages/media.php"><span class="nav-icon">&#9835;</span> Media</a>
            <a href="pages/music.php"><span class="nav-icon">&#9836;</span> Music</a>
            <a href="pages/tweets.php"><span class="nav-icon">&#9993;</span> Tweets</a>
            <a href="pages/writing.php"><span class="nav-icon">&#9998;</span> Writing</a>
            <a href="pages/timeline.php"><span class="nav-icon">&#8986;</span> Timeline</a>
            <a href="pages/dribbble.php"><span class="nav-icon">&#9673;</span> Dribbble</a>
            <a href="pages/instagram.php"><span class="nav-icon">&#9744;</span> Instagram</a>
            <a href="pages/github.php"><span class="nav-icon">&#9733;</span> GitHub</a>
            <a href="pages/lastseen.php"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="pages/config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="pages/password.php"><span class="nav-icon">&#9733;</span> Password</a>
        </nav>
        <div class="sidebar-footer">
            <a href="?action=logout">Sign Out</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <div class="page-header">
            <h1>Dashboard</h1>
            <div class="breadcrumb">Welcome, <?= htmlspecialchars($_SESSION['admin']['username']) ?></div>
        </div>

        <div class="stats-grid">
            <?php foreach ($stats as $s): ?>
            <div class="stat-card">
                <h3><?= (int)$s['count'] ?></h3>
                <p><?= htmlspecialchars($s['label']) ?></p>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="card">
            <h2>Quick Actions</h2>
            <p style="margin-bottom:16px; color:#7f8c8d;">Manage your portfolio content using the sidebar navigation.</p>
            <div style="display:flex; gap:10px; flex-wrap:wrap;">
                <a href="pages/projects.php?action=add" class="btn btn-primary">Add Project</a>
                <a href="pages/media.php?action=add" class="btn btn-success">Add Media Item</a>
                <a href="pages/music.php?action=add" class="btn btn-primary">Add Music Track</a>
                <a href="pages/tweets.php?action=add" class="btn btn-success">Add Tweet</a>
            </div>
        </div>
    </main>
</body>
</html>
