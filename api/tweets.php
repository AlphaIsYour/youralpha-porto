<?php
require_once 'config.php';
$pdo = getDB();

$tweetsStmt = $pdo->query('SELECT id, tweet_text, tweet_date, retweets, likes, tweet_url, images, sort_order FROM tweets ORDER BY sort_order');
$tweets = $tweetsStmt->fetchAll();

$profileStmt = $pdo->query('SELECT id, username, followers, tweet_count FROM twitter_profile LIMIT 1');
$profile = $profileStmt->fetch();

jsonResponse([
    'tweets' => $tweets,
    'profile' => $profile ?: []
]);
