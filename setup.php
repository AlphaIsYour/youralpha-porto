<?php
/**
 * ONE-TIME SETUP SCRIPT
 * Visit http://localhost/static-porto/setup.php in your browser
 * Creates database, tables, seeds data, creates admin user
 * DELETE THIS FILE AFTER RUNNING
 */

$host = 'localhost';
$port = '3307';
$user = 'root';
$pass = '';
$dbname = 'static_porto';

header('Content-Type: text/plain; charset=utf-8');
echo "=== Static Porto Setup ===\n\n";

try {
    // Connect without database
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$dbname`");
    echo "[OK] Database '$dbname' created\n";

    // Read and execute SQL file
    $sql = file_get_contents(__DIR__ . '/database.sql');
    // Remove CREATE DATABASE and USE statements since we already handled them
    $sql = preg_replace('/CREATE DATABASE.*?;/s', '', $sql);
    $sql = preg_replace('/USE.*?;/s', '', $sql);

    $statements = array_filter(array_map('trim', explode(';', $sql)));
    foreach ($statements as $stmt) {
        if (!empty($stmt) && $stmt !== '--') {
            $pdo->exec($stmt);
        }
    }
    echo "[OK] Tables created\n";

    // Create admin user (admin / admin123)
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO admin_users (username, password_hash) VALUES ('admin', '$hash')");
    echo "[OK] Admin user created (username: admin, password: admin123)\n";

    // Seed config
    $configData = [
        ['site_title', 'Youralpha'],
        ['site_description', "I'm Youralpha, an Information Technology student at Brawijaya University. I'm a developer and designer who loves building experiences (both online and off)!"],
        ['twitter_handle', '@youralpha'],
        ['instagram_handle', '@eno4lph_'],
        ['github_username', 'AlphaIsYour'],
    ];
    $stmt = $pdo->prepare("INSERT INTO config (`key`, `value`) VALUES (?, ?)");
    foreach ($configData as $c) {
        $stmt->execute($c);
    }
    echo "[OK] Config seeded\n";

    // Seed about
    $pdo->exec("INSERT INTO about (bio_text, github_url, github_btn_text) VALUES (
        'Hi! I''m Youralpha, an Information Technology student at Brawijaya University. I love building things with code and exploring new technologies. Check out my projects on GitHub!',
        'https://github.com/AlphaIsYour',
        'Check out my GitHub'
    )");
    echo "[OK] About seeded\n";

    // Seed projects
    $projects = [
        ['MindBridge', 'Mental Health Consultation Platform - WordPress + Docker', 'https://github.com/AlphaIsYour/mindbridge-platform', 'icon-heart', '#e74c3c', 1, 1],
        ['Farmify', 'Smart Farming System with Laravel REST API & IoT monitoring', 'https://github.com/AlphaIsYour/farmify', 'icon-leaf', '#27ae60', 0, 2],
        ['Bekasin', 'E-commerce for second-hand items - Next.js + TypeScript', 'https://github.com/AlphaIsYour/bekasin', 'icon-cart', '#3498db', 0, 3],
        ['Leaderboard Koruptor', 'Solidity smart contracts with Hardhat', 'https://github.com/AlphaIsYour/leaderboard-koruptor', 'icon-shield', '#e67e22', 0, 4],
        ['Youru Cinema', 'Next.js streaming app with Docker', 'https://github.com/AlphaIsYour/youru-cinema', 'icon-film', '#9b59b6', 0, 5],
        ['Aplikasi Kasir', 'Flutter POS app with backend', 'https://github.com/AlphaIsYour', 'icon-calculator', '#1abc9c', 0, 6],
        ['AI Finance Tracker', 'PHP/Python/SQL + WhatsApp integration', 'https://github.com/AlphaIsYour', 'icon-chart', '#f39c12', 0, 7],
        ['Static Porto', 'This portfolio website - interactive desk illustration', 'https://github.com/AlphaIsYour/static-porto', 'icon-code', '#34495e', 0, 8],
    ];
    $stmt = $pdo->prepare("INSERT INTO projects (name, description, url, icon_class, icon_color, is_featured, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?)");
    foreach ($projects as $p) {
        $stmt->execute($p);
    }
    echo "[OK] Projects seeded (8 items)\n";

    // Seed media (100 Things I Love)
    $media = [
        ['music', 'All I\'ve Ever Wanted (Live from the Walt Disney Concert Hall)', 'Airborne Toxic Event', 'https://open.spotify.com', 'Listen on Spotify', 1],
        ['tv', 'Scrubs', null, 'https://www.hulu.com', 'Watch on Hulu', 2],
        ['article', 'Ask Culture vs Guess Culture', null, 'https://ask.metafilter.com', 'Read on MetaFilter', 3],
        ['tv', 'Lovesick', null, 'https://www.netflix.com', 'Watch on Netflix', 4],
        ['book', 'Story of Your Life and Others', 'Ted Chiang', 'https://www.amazon.com', 'Buy on Amazon', 5],
        ['tv', 'A Dark Quiet Death (S1E5)', 'Mythic Quest', 'https://tv.apple.com', 'Watch on Apple TV+', 6],
        ['music', 'The Midnight Organ Fight', 'Frightened Rabbit', 'https://open.spotify.com', 'Listen on Spotify', 7],
        ['article', 'xkcd', null, 'https://xkcd.com', 'Read on xkcd', 8],
        ['youtube', 'The Greatest Title Sequence I\'ve Ever Seen', 'Tom Scott', 'https://youtube.com', 'Watch on YouTube', 9],
        ['tv', 'Game Changer', null, 'https://www.dropout.tv', 'Watch on Dropout', 10],
        ['book', 'Getting Real', '37 Signals', 'https://www.amazon.com', 'Buy on Amazon', 11],
        ['article', 'How to Say Nothing in 500 Words', null, 'https://www.paulroberts.info/pdf/How_to_Say_Nothing.pdf', 'Read PDF', 12],
        ['tv', 'Firefly', null, 'https://www.hulu.com', 'Watch on Hulu', 13],
        ['music', '69 Love Songs', 'Magnetic Fields', 'https://open.spotify.com', 'Listen on Spotify', 14],
        ['book', 'Kitchen Confidential (Audiobook)', 'Anthony Bourdain', 'https://www.audible.com', 'Listen on Audible', 15],
        ['youtube', 'Please do not be cynical', 'Conan O\'Brien', 'https://youtube.com', 'Watch on YouTube', 16],
        ['article', 'The Sad, Beautiful Fact That We\'re All Going To Miss Almost Everything', 'Linda Holmes', 'https://www.npr.org', 'Read on NPR', 17],
        ['tv', 'Ted Lasso', null, 'https://tv.apple.com', 'Watch on Apple TV+', 18],
        ['book', 'How to Win Friends and Influence People', null, 'https://www.amazon.com', 'Buy on Amazon', 19],
        ['article', 'We Don\'t Sell Saddles Here', 'Stewart Butterfield', 'https://medium.com', 'Read on Medium', 20],
        ['podcast', 'Wait Wait... Don\'t Tell Me!', null, 'https://www.npr.org', 'Listen on NPR', 21],
        ['music', 'Barenaked Ladies', null, 'https://open.spotify.com', 'Listen on Spotify', 22],
        ['youtube', 'The Story of Watsi', 'Chase Adams', 'https://youtube.com', 'Watch on YouTube', 23],
        ['tv', 'The Rehearsal', 'Nathan Fielder', 'https://www.hbomax.com', 'Watch on HBO Max', 24],
        ['podcast', 'Offbook: The Improvised Musical', null, 'https://youtube.com', 'Listen on YouTube', 25],
        ['tv', 'Bojack Horseman', null, 'https://www.netflix.com', 'Watch on Netflix', 26],
        ['podcast', 'Mystery Show', 'Starlee Kine', 'https://gimletmedia.com', 'Listen on Gimlet', 27],
        ['article', 'Maker\'s Schedule, Manager\'s Schedule', 'Paul Graham', 'http://www.paulgraham.com', 'Read on Paul Graham', 28],
        ['theater', 'My Girlfriend\'s Boyfriend', 'Mike Birbiglia', 'https://www.netflix.com', 'Watch on Netflix', 29],
        ['tv', 'Finding Frances', 'Nathan for You', 'https://www.hulu.com', 'Watch on Hulu', 30],
        ['youtube', 'Orson Welles Talks About Making Citizen Kane', null, 'https://youtube.com', 'Watch on YouTube', 31],
        ['book', 'Kellogg\'s', 'B.J. Novak', 'https://www.amazon.com', 'Buy on Amazon', 32],
        ['podcast', 'You Made It Weird', 'Pete Holmes', 'https://podcasts.apple.com', 'Listen on Apple Podcasts', 33],
        ['tv', 'Arrested Development', null, 'https://www.netflix.com', 'Watch on Netflix', 34],
        ['book', 'Attached', null, 'https://www.amazon.com', 'Buy on Amazon', 35],
        ['youtube', 'Jake and Amir', null, 'https://youtube.com', 'Watch on YouTube', 36],
        ['article', 'Little Big Details', null, 'https://littlebigdetails.com', 'Visit Site', 37],
        ['tv', 'Magic For Humans', null, 'https://www.netflix.com', 'Watch on Netflix', 38],
        ['article', '1,001 Rules for my Unborn Son', null, 'https://1001rulesforlife.tumblr.com', 'Read on Tumblr', 39],
        ['theater', 'Hamilton Original Broadway Cast', null, 'https://open.spotify.com', 'Listen on Spotify', 40],
    ];
    $stmt = $pdo->prepare("INSERT INTO media (type, title, author, link_url, link_text, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($media as $m) {
        $stmt->execute($m);
    }
    echo "[OK] Media seeded (40 items)\n";

    // Seed music
    $music = [
        ['Watch Me', 'Labi Siffre', 'https://open.spotify.com/search/results/Watch Me - Labi Siffre', '2025-06-24 18:16:20', 1],
        ['Foxglove', 'Murder By Death', 'https://open.spotify.com/search/results/Foxglove - Murder By Death', '2025-06-19 23:16:56', 2],
        ['The Ballad of the Kingsmen', 'Todd Snider', 'https://open.spotify.com/search/results/The Ballad of the Kingsmen - Todd Snider', '2025-06-19 23:11:54', 3],
        ['Secrets on Our Lips', 'Astronautalis', 'https://open.spotify.com/search/results/Secrets on Our Lips - Astronautalis', '2025-06-19 23:08:22', 4],
        ['The Wild Mercury', 'Vandaveer', 'https://open.spotify.com/search/results/The Wild Mercury - Vandaveer', '2025-06-19 23:04:39', 5],
        ['Little Trouble', 'Better Oblivion Community Center', 'https://open.spotify.com/search/results/Little Trouble - Better Oblivion Community Center', '2025-06-19 23:00:03', 6],
        ['Oh Lord', 'Foxy Shazam', 'https://open.spotify.com/search/results/Oh Lord - Foxy Shazam', '2025-06-19 22:54:59', 7],
        ['Into The Wild', 'Lewis Watson', 'https://open.spotify.com/search/results/Into The Wild - Lewis Watson', '2025-06-19 22:52:02', 8],
    ];
    $stmt = $pdo->prepare("INSERT INTO music (song, artist, spotify_url, played_at, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($music as $m) {
        $stmt->execute($m);
    }
    echo "[OK] Music seeded (8 tracks)\n";

    // Seed tweets
    $pdo->exec("INSERT INTO tweets (tweet_text, tweet_date, retweets, likes, tweet_url, images, sort_order) VALUES (
        'Ini rill kh?',
        '10 March 2019',
        3307,
        7389,
        'https://twitter.com/youralpha/status/1108046737208459264',
        '[\"https://pbs.twimg.com/media/D2CSoj4W0AAxRlJ.jpg\",\"https://pbs.twimg.com/media/D2CSonbWoAEvJFj.jpg\",\"https://pbs.twimg.com/media/D2CSomUW0AA5K2I.jpg\",\"https://pbs.twimg.com/media/D2CgTFWUYAAHQ8p.jpg\",\"https://pbs.twimg.com/media/D2CgVyZUkAA88Pg.jpg\"]',
        1
    )");
    echo "[OK] Tweets seeded\n";

    // Seed twitter profile
    $pdo->exec("INSERT INTO twitter_profile (username, followers, tweet_count) VALUES ('@youralpha', 1325, 155)");
    echo "[OK] Twitter profile seeded\n";

    // Seed writing
    $writing = [
        ['Building Full-Stack Apps with Next.js', '2024', 'fa-code', 'https://github.com/AlphaIsYour', 1],
        ['WordPress + Docker Development Setup', '2024', 'fa-database', 'https://github.com/AlphaIsYour', 2],
        ['Mobile Development with Flutter', '2023', 'fa-mobile', 'https://github.com/AlphaIsYour', 3],
        ['Studying IT at Brawijaya University', '2023 - Present', 'fa-university', 'https://github.com/AlphaIsYour', 4],
        ['Exploring IoT with Smart Farming', '2024', 'fa-globe', 'https://github.com/AlphaIsYour', 5],
    ];
    $stmt = $pdo->prepare("INSERT INTO writing (title, year, icon_class, link_url, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($writing as $w) {
        $stmt->execute($w);
    }
    echo "[OK] Writing seeded (5 entries)\n";

    // Seed timeline
    $timeline = [
        ['2023 - Present', 'Brawijaya University', 'Studying Information Technology at the Faculty of Vocational', 'student', 1],
        ['2020 - 2023', 'High School', 'Completed high school education in Jember', 'student', 2],
        ['2005 - 2020', 'Growing Up in Jember', 'Spent my formative years in Jember, East Java', 'life', 3],
    ];
    $stmt = $pdo->prepare("INSERT INTO timeline (date_range, title, description, type_label, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($timeline as $t) {
        $stmt->execute($t);
    }
    echo "[OK] Timeline seeded (3 entries)\n";

    // Seed dribbble
    $pdo->exec("INSERT INTO dribbble (profile_url, display_name, placeholder_text) VALUES (
        'https://dribbble.com/youralpha',
        'youralpha',
        'Design portfolio coming soon! Check out my GitHub for now.'
    )");
    echo "[OK] Dribbble seeded\n";

    // Seed instagram
    $instagram = [
        ['images/instagram/CJmWlHJpo98.jpg', 'https://www.instagram.com/p/CJmWlHJpo98/', 1],
        ['images/instagram/B9sR2C8JUX3.jpg', 'https://www.instagram.com/p/B9sR2C8JUX3/', 2],
        ['images/instagram/ByMB8tKJWIF.jpg', 'https://www.instagram.com/p/ByMB8tKJWIF/', 3],
        ['images/instagram/Bx2Xu-3JaK6.jpg', 'https://www.instagram.com/p/Bx2Xu-3JaK6/', 4],
    ];
    $stmt = $pdo->prepare("INSERT INTO instagram (image_url, post_url, sort_order) VALUES (?, ?, ?)");
    foreach ($instagram as $ig) {
        $stmt->execute($ig);
    }
    echo "[OK] Instagram seeded (4 posts)\n";

    // Seed github repos
    $repos = [
        ['mindbridge-platform', 'Mental Health Consultation Platform - WordPress + Docker', 'https://github.com/AlphaIsYour/mindbridge-platform', 0, 1],
        ['farmify', 'Smart Farming System with Laravel REST API & IoT', 'https://github.com/AlphaIsYour/farmify', 0, 2],
        ['bekasin', 'E-commerce for second-hand items - Next.js + TypeScript', 'https://github.com/AlphaIsYour/bekasin', 0, 3],
        ['leaderboard-koruptor', 'Solidity smart contracts with Hardhat', 'https://github.com/AlphaIsYour/leaderboard-koruptor', 0, 4],
        ['youru-cinema', 'Next.js streaming app with Docker', 'https://github.com/AlphaIsYour/youru-cinema', 0, 5],
        ['static-porto', 'This portfolio website - interactive desk illustration', 'https://github.com/AlphaIsYour/static-porto', 0, 6],
    ];
    $stmt = $pdo->prepare("INSERT INTO github_repos (name, description, url, stars, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($repos as $r) {
        $stmt->execute($r);
    }
    echo "[OK] GitHub repos seeded (6 repos)\n";

    // Seed github profile
    $pdo->exec("INSERT INTO github_profile (username, joined_date, total_repos) VALUES ('AlphaIsYour', 'Aug 2020', 30)");
    echo "[OK] GitHub profile seeded\n";

    // Seed lastseen
    $countries = ['Malang', 'Jember', 'Peru', 'Viet Nam', 'Canada', 'Bolivia', 'Italy', 'Costa Rica', 'Cambodia', 'France', 'Switzerland', 'Mexico', 'Taiwan', 'China', 'Portugal', 'United Kingdom', 'Iceland', 'Laos', 'Finland', 'Belgium', 'Netherlands'];
    $countriesJson = json_encode($countries);
    $pdo->exec("INSERT INTO lastseen (location_name, location_city, joined_date, checkins, provinces, countries, last_seen_at) VALUES (
        'The Yoralph Room',
        'Malang, ID',
        'July 7, 2025',
        135,
        5,
        '$countriesJson',
        '2025-06-25 05:29:44'
    )");
    echo "[OK] Last seen seeded\n";

    echo "\n=== Setup Complete! ===\n";
    echo "Admin login: http://localhost/static-porto/admin/\n";
    echo "Username: admin\n";
    echo "Password: admin123\n";
    echo "\nDELETE setup.php AND database.sql AFTER VERIFYING!\n";

} catch (PDOException $e) {
    echo "\n[ERROR] " . $e->getMessage() . "\n";
}
