<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, username, joined_date, total_repos FROM github_profile LIMIT 1');
$profile = $stmt->fetch();

$stmt = $pdo->query('SELECT id, name, description, url, stars, sort_order FROM github_repos ORDER BY sort_order');
$repos = $stmt->fetchAll();

jsonResponse([
    'profile' => $profile ?: [],
    'repos' => $repos
]);
