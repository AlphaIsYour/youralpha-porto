<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, title, year, icon_class, link_url, sort_order FROM writing ORDER BY sort_order');
$rows = $stmt->fetchAll();

jsonResponse($rows);
