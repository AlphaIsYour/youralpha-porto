<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, song, artist, spotify_url, played_at, sort_order FROM music ORDER BY sort_order');
$rows = $stmt->fetchAll();

jsonResponse($rows);
