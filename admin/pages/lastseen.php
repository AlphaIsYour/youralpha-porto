<?php
// admin/pages/lastseen.php - CRUD for lastseen table (single record)
require_once __DIR__ . '/../auth.php';
requireLogin();

$db = getDB();
$currentPage = 'lastseen';
$message = '';
$error = '';

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
    $location_name = trim($_POST['location_name'] ?? '');
    $location_city = trim($_POST['location_city'] ?? '');
    $joined_date = trim($_POST['joined_date'] ?? '');
    $checkins = (int)($_POST['checkins'] ?? 0);
    $provinces = (int)($_POST['provinces'] ?? 0);
    $countries_raw = trim($_POST['countries'] ?? '');
    $last_seen_at = trim($_POST['last_seen_at'] ?? '');

    // Parse comma-separated countries into JSON array
    $countries_arr = array_filter(array_map('trim', explode(',', $countries_raw)));
    $countries_json = json_encode(array_values($countries_arr));

    $last_seen_val = $last_seen_at !== '' ? $last_seen_at : null;

    $existing = $db->query("SELECT id FROM lastseen LIMIT 1")->fetch();

    if ($existing) {
        $stmt = $db->prepare("UPDATE lastseen SET location_name=?, location_city=?, joined_date=?, checkins=?, provinces=?, countries=?, last_seen_at=? WHERE id=?");
        $stmt->execute([$location_name, $location_city, $joined_date, $checkins, $provinces, $countries_json, $last_seen_val, $existing['id']]);
    } else {
        $stmt = $db->prepare("INSERT INTO lastseen (location_name, location_city, joined_date, checkins, provinces, countries, last_seen_at) VALUES (?,?,?,?,?,?,?)");
        $stmt->execute([$location_name, $location_city, $joined_date, $checkins, $provinces, $countries_json, $last_seen_val]);
    }

    $message = 'Last seen data updated.';
}

// Fetch current data
$lastseen = $db->query("SELECT * FROM lastseen LIMIT 1")->fetch() ?: [
    'location_name' => '', 'location_city' => '', 'joined_date' => '',
    'checkins' => 0, 'provinces' => 0, 'countries' => '[]', 'last_seen_at' => ''
];

// Parse countries JSON to comma-separated string for display
$countries_display = '';
if (!empty($lastseen['countries'])) {
    $decoded = json_decode($lastseen['countries'], true);
    if (is_array($decoded)) {
        $countries_display = implode(', ', $decoded);
    }
}

// Format last_seen_at for datetime-local input
$last_seen_val = '';
if (!empty($lastseen['last_seen_at'])) {
    try {
        $dt = new DateTime($lastseen['last_seen_at']);
        $last_seen_val = $dt->format('Y-m-d\TH:i');
    } catch (Exception $e) {
        $last_seen_val = $lastseen['last_seen_at'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Last Seen - Admin Panel</title>
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
            <a href="lastseen.php" class="active"><span class="nav-icon">&#9873;</span> Last Seen</a>
            <a href="config.php"><span class="nav-icon">&#9881;</span> Config</a>
            <a href="password.php"><span class="nav-icon">&#9733;</span> Password</a>
        </nav>
        <div class="sidebar-footer"><a href="../dashboard.php?action=logout">Sign Out</a></div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Last Seen</h1>
            <div class="breadcrumb"><a href="../dashboard.php">Dashboard</a> / Last Seen</div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <div class="card">
            <h2>Edit Last Seen Data</h2>
            <form method="POST" action="">
                <?= csrfField() ?>
                <div class="form-row">
                    <div class="form-group">
                        <label for="location_name">Location Name</label>
                        <input type="text" id="location_name" name="location_name" value="<?= htmlspecialchars($lastseen['location_name']) ?>">
                    </div>
                    <div class="form-group">
                        <label for="location_city">Location City</label>
                        <input type="text" id="location_city" name="location_city" value="<?= htmlspecialchars($lastseen['location_city']) ?>" placeholder="Malang, ID">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="joined_date">Joined Date</label>
                        <input type="text" id="joined_date" name="joined_date" value="<?= htmlspecialchars($lastseen['joined_date']) ?>" placeholder="July 7, 2025">
                    </div>
                    <div class="form-group">
                        <label for="last_seen_at">Last Seen At</label>
                        <input type="datetime-local" id="last_seen_at" name="last_seen_at" value="<?= htmlspecialchars($last_seen_val) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="checkins">Check-ins</label>
                        <input type="number" id="checkins" name="checkins" value="<?= (int)$lastseen['checkins'] ?>">
                    </div>
                    <div class="form-group">
                        <label for="provinces">Provinces</label>
                        <input type="number" id="provinces" name="provinces" value="<?= (int)$lastseen['provinces'] ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label for="countries">Countries (comma-separated)</label>
                    <textarea id="countries" name="countries" rows="4"><?= htmlspecialchars($countries_display) ?></textarea>
                    <div class="hint">Enter country names separated by commas, e.g. "Malang, Jember, France, Japan"</div>
                </div>
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>
