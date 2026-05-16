<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, image_url, post_url, sort_order FROM instagram ORDER BY sort_order');
$rows = $stmt->fetchAll();

jsonResponse($rows);
