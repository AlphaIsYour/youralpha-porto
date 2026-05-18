<?php
// admin/index.php - Login page (TOTP-based with security hardening)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/security.php';
startSession();

if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$locked = false;
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Check lockout
if (isLockedOut($clientIp)) {
    $remaining = getLockoutRemaining($clientIp);
    $minutes = ceil($remaining / 60);
    $error = "Too many failed attempts. Try again in {$minutes} minute(s).";
    $locked = true;
}

// Generate CSRF token
if (empty($_SESSION['login_csrf'])) {
    $_SESSION['login_csrf'] = bin2hex(random_bytes(32));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$locked) {
    $code = trim($_POST['code'] ?? '');
    $csrf = $_POST['csrf_token'] ?? '';

    // Verify CSRF
    if (!hash_equals($_SESSION['login_csrf'] ?? '', $csrf)) {
        $error = 'Invalid request. Please refresh and try again.';
    } elseif ($code === '' || !preg_match('/^\d{6}$/', $code)) {
        $error = 'Please enter a valid 6-digit code.';
    } else {
        $db = getDB();
        $stmt = $db->prepare('SELECT id, username, totp_secret FROM admin_users WHERE totp_secret IS NOT NULL AND totp_secret != "" LIMIT 1');
        $stmt->execute();
        $user = $stmt->fetch();

        if ($user) {
            // Decrypt the stored secret
            $secret = decryptSecret($user['totp_secret']);
            $totp = new TOTP($secret);
            if ($totp->verify($code)) {
                // Success - clear failed attempts and regenerate CSRF
                clearAttempts($clientIp);
                unset($_SESSION['login_csrf']);
                $_SESSION['admin'] = [
                    'id' => $user['id'],
                    'username' => $user['username'],
                ];
                // Regenerate session ID to prevent session fixation
                session_regenerate_id(true);
                header('Location: dashboard.php');
                exit;
            } else {
                recordFailedAttempt($clientIp);
                $error = 'Invalid code. Please check your Google Authenticator and try again.';
            }
        } else {
            $error = 'No authenticator configured. Please set up Google Authenticator first.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Static Porto</title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #1a1a2e;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #333;
        }
        .login-card {
            background: #fff;
            border-radius: 12px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
        }
        .login-card h1 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        .login-card .subtitle {
            font-size: 0.9rem;
            color: #7f8c8d;
            margin-bottom: 28px;
        }
        .form-group {
            margin-bottom: 18px;
        }
        .form-group label {
            display: block;
            font-weight: 600;
            font-size: 0.85rem;
            color: #2c3e50;
            margin-bottom: 6px;
        }
        .form-group input {
            width: 100%;
            padding: 11px 14px;
            border: 1px solid #dcdfe6;
            border-radius: 8px;
            font-size: 1.4rem;
            font-family: 'Courier New', monospace;
            text-align: center;
            letter-spacing: 10px;
            transition: border-color 0.2s;
        }
        .form-group input:focus {
            outline: none;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.15);
        }
        .form-group input:disabled {
            background: #f5f5f5;
            cursor: not-allowed;
        }
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #3498db;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            transition: background 0.2s;
            margin-top: 8px;
        }
        .btn-login:hover { background: #2980b9; }
        .btn-login:disabled { background: #bdc3c7; cursor: not-allowed; }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 0.88rem;
            margin-bottom: 18px;
        }
        .logo-area {
            text-align: center;
            margin-bottom: 24px;
        }
        .logo-area .icon {
            width: 56px;
            height: 56px;
            background: #3498db;
            border-radius: 14px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
            margin-bottom: 10px;
        }
        .hint {
            text-align: center;
            font-size: 0.8rem;
            color: #95a5a6;
            margin-top: 16px;
        }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo-area">
            <div class="icon">SP</div>
        </div>
        <h1>Admin Panel</h1>
        <p class="subtitle">Enter the 6-digit code from Google Authenticator</p>

        <?php if ($error): ?>
            <div class="alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['login_csrf'] ?? '') ?>">
            <div class="form-group">
                <label for="code">Authentication Code</label>
                <input type="text" id="code" name="code" maxlength="6" pattern="\d{6}"
                       placeholder="000000" required autofocus autocomplete="off"
                       inputmode="numeric" <?= $locked ? 'disabled' : '' ?>>
            </div>
            <button type="submit" class="btn-login" <?= $locked ? 'disabled' : '' ?>>Sign In</button>
        </form>
        <p class="hint">Open Google Authenticator to get your code</p>
    </div>
</body>
</html>
