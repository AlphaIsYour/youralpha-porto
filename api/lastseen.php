<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, location_name, location_city, joined_date, checkins, provinces, countries, last_seen_at FROM lastseen LIMIT 1');
$row = $stmt->fetch();

if ($row && isset($row['countries'])) {
    $row['countries'] = json_decode($row['countries'], true);
}

jsonResponse($row ?: []);
