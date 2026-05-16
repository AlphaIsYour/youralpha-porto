<?php
require_once 'config.php';
$pdo = getDB();

$stmt = $pdo->query('SELECT id, bio_text, github_url, github_btn_text FROM about LIMIT 1');
$row = $stmt->fetch();

jsonResponse($row ?: []);
