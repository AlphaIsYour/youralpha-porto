<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, name, description, url, icon_class, icon_color, is_featured, sort_order FROM projects ORDER BY sort_order');
$rows = $stmt->fetchAll();

jsonResponse($rows);
