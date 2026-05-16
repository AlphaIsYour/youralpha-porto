<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, type, title, author, link_url, link_text, sort_order FROM media ORDER BY sort_order');
$rows = $stmt->fetchAll();

jsonResponse($rows);
