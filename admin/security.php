<?php
// admin/security.php - Rate limiting and security helpers

require_once __DIR__ . '/db.php';

// Maximum login attempts before lockout
define('MAX_LOGIN_ATTEMPTS', 5);
// Lockout duration in seconds (15 minutes)
define('LOCKOUT_DURATION', 900);

/**
 * Record a failed login attempt
 */
function recordFailedAttempt(string $ip): void {
    $db = getDB();
    $db->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip VARCHAR(45) NOT NULL,
        attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_ip_time (ip, attempted_at)
    )");
    $stmt = $db->prepare("INSERT INTO login_attempts (ip) VALUES (?)");
    $stmt->execute([$ip]);
}

/**
 * Check if IP is locked out
 */
function isLockedOut(string $ip): bool {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$ip, LOCKOUT_DURATION]);
        $count = (int)$stmt->fetchColumn();
        return $count >= MAX_LOGIN_ATTEMPTS;
    } catch (PDOException) {
        return false;
    }
}

/**
 * Clear failed attempts for IP (after successful login)
 */
function clearAttempts(string $ip): void {
    $db = getDB();
    try {
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE ip = ?");
        $stmt->execute([$ip]);
    } catch (PDOException) {}
}

/**
 * Get remaining lockout time in seconds
 */
function getLockoutRemaining(string $ip): int {
    $db = getDB();
    try {
        $stmt = $db->prepare("SELECT MIN(attempted_at) FROM login_attempts WHERE ip = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL ? SECOND)");
        $stmt->execute([$ip, LOCKOUT_DURATION]);
        $oldest = $stmt->fetchColumn();
        if (!$oldest) return 0;
        $elapsed = time() - strtotime($oldest);
        return max(0, LOCKOUT_DURATION - $elapsed);
    } catch (PDOException) {
        return 0;
    }
}

/**
 * Get encryption key from config file (outside web root ideally)
 */
function getEncryptionKey(): string {
    $keyFile = __DIR__ . '/.totp_key';
    if (file_exists($keyFile)) {
        return trim(file_get_contents($keyFile));
    }
    // Generate and store key on first run
    $key = bin2hex(random_bytes(32));
    file_put_contents($keyFile, $key);
    // Try to protect the file (best effort on Windows)
    @chmod($keyFile, 0600);
    return $key;
}

/**
 * Encrypt TOTP secret for storage
 */
function encryptSecret(string $secret): string {
    $key = hex2bin(getEncryptionKey());
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($secret, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
    return base64_encode($iv . $encrypted);
}

/**
 * Decrypt TOTP secret
 */
function decryptSecret(string $encrypted): string {
    $key = hex2bin(getEncryptionKey());
    $data = base64_decode($encrypted);
    $iv = substr($data, 0, 16);
    $ciphertext = substr($data, 16);
    return openssl_decrypt($ciphertext, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
}
