-- Database: static_porto
CREATE DATABASE IF NOT EXISTS static_porto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE static_porto;

-- Admin users
CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    totp_secret VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Login rate limiting
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip VARCHAR(45) NOT NULL,
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip, attempted_at)
);

-- Site config (key-value pairs)
CREATE TABLE IF NOT EXISTS config (
    id INT AUTO_INCREMENT PRIMARY KEY,
    `key` VARCHAR(100) NOT NULL UNIQUE,
    value TEXT
);

-- About Me
CREATE TABLE IF NOT EXISTS about (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bio_text TEXT NOT NULL,
    github_url VARCHAR(255),
    github_btn_text VARCHAR(100)
);

-- Projects
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(500),
    icon_class VARCHAR(50),
    icon_color VARCHAR(20),
    is_featured TINYINT(1) DEFAULT 0,
    sort_order INT DEFAULT 0
);

-- 100 Things I Love (media)
CREATE TABLE IF NOT EXISTS media (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(20) NOT NULL,
    title VARCHAR(255) NOT NULL,
    author VARCHAR(255),
    link_url VARCHAR(500),
    link_text VARCHAR(100),
    sort_order INT DEFAULT 0
);

-- Spotify / Music
CREATE TABLE IF NOT EXISTS music (
    id INT AUTO_INCREMENT PRIMARY KEY,
    song VARCHAR(255) NOT NULL,
    artist VARCHAR(255),
    spotify_url VARCHAR(500),
    played_at DATETIME,
    sort_order INT DEFAULT 0
);

-- Tweets
CREATE TABLE IF NOT EXISTS tweets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tweet_text TEXT NOT NULL,
    tweet_date VARCHAR(50),
    retweets INT DEFAULT 0,
    likes INT DEFAULT 0,
    tweet_url VARCHAR(500),
    images JSON,
    sort_order INT DEFAULT 0
);

-- Writing / Learning Journey
CREATE TABLE IF NOT EXISTS writing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    year VARCHAR(20),
    icon_class VARCHAR(50),
    link_url VARCHAR(500),
    sort_order INT DEFAULT 0
);

-- Timeline / Resume
CREATE TABLE IF NOT EXISTS timeline (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date_range VARCHAR(50),
    title VARCHAR(255) NOT NULL,
    description TEXT,
    type_label VARCHAR(50),
    sort_order INT DEFAULT 0
);

-- Dribbble
CREATE TABLE IF NOT EXISTS dribbble (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_url VARCHAR(255),
    display_name VARCHAR(100),
    placeholder_text TEXT
);

-- Instagram
CREATE TABLE IF NOT EXISTS instagram (
    id INT AUTO_INCREMENT PRIMARY KEY,
    image_url VARCHAR(500) NOT NULL,
    post_url VARCHAR(500),
    sort_order INT DEFAULT 0
);

-- GitHub repos
CREATE TABLE IF NOT EXISTS github_repos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    url VARCHAR(500),
    stars INT DEFAULT 0,
    sort_order INT DEFAULT 0
);

-- GitHub profile
CREATE TABLE IF NOT EXISTS github_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    joined_date VARCHAR(50),
    total_repos INT DEFAULT 0
);

-- Last Seen / Foursquare
CREATE TABLE IF NOT EXISTS lastseen (
    id INT AUTO_INCREMENT PRIMARY KEY,
    location_name VARCHAR(255),
    location_city VARCHAR(100),
    joined_date VARCHAR(50),
    checkins INT DEFAULT 0,
    provinces INT DEFAULT 0,
    countries JSON,
    last_seen_at DATETIME
);

-- Twitter profile info
CREATE TABLE IF NOT EXISTS twitter_profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100),
    followers INT DEFAULT 0,
    tweet_count INT DEFAULT 0
);
