<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, profile_url, display_name, placeholder_text FROM dribbble LIMIT 1');
$row = $stmt->fetch();

jsonResponse($row ?: []);
