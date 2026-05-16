<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, date_range, title, description, type_label, sort_order FROM timeline ORDER BY sort_order');
$rows = $stmt->fetchAll();

jsonResponse($rows);
