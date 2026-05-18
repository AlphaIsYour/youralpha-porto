<?php
// admin/setup-totp.php - Setup Google Authenticator (secured)
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/totp.php';
require_once __DIR__ . '/security.php';
requireLogin();

$db = getDB();
$message = '';
$error = '';
$secret = '';
$uri = '';
$hasSecret = false;

// Verify CSRF on POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();
}

// Generate new secret
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'generate') {
        $plainSecret = TOTP::generateSecret(20);
        $encrypted = encryptSecret($plainSecret);
        $stmt = $db->prepare('UPDATE admin_users SET totp_secret = ? WHERE id = ?');
        $stmt->execute([$encrypted, $_SESSION['admin']['id']]);
        $secret = $plainSecret;
        $hasSecret = true;
        $totp = new TOTP($secret);
        $uri = $totp->getProvisioningUri('StaticPorto', $_SESSION['admin']['username']);
        $message = 'Secret generated! Scan the QR code below with Google Authenticator.';
    } elseif ($_POST['action'] === 'verify' && isset($_POST['code'])) {
        $code = trim($_POST['code']);
        $stmt = $db->prepare('SELECT totp_secret FROM admin_users WHERE id = ?');
        $stmt->execute([$_SESSION['admin']['id']]);
        $user = $stmt->fetch();
        if ($user && $user['totp_secret']) {
            $decrypted = decryptSecret($user['totp_secret']);
            $totp = new TOTP($decrypted);
            if ($totp->verify($code)) {
                $message = 'Verified! Google Authenticator is set up. You can now use it to login.';
            } else {
                $error = 'Invalid code. Make sure your Google Authenticator is synced and try again.';
            }
            $secret = $decrypted;
            $hasSecret = true;
            $uri = $totp->getProvisioningUri('StaticPorto', $_SESSION['admin']['username']);
        } else {
            $error = 'No secret found. Generate one first.';
        }
    }
}

// Get current secret (if not just generated/verified)
if (empty($secret)) {
    $stmt = $db->prepare('SELECT totp_secret FROM admin_users WHERE id = ?');
    $stmt->execute([$_SESSION['admin']['id']]);
    $user = $stmt->fetch();
    if ($user && !empty($user['totp_secret'])) {
        $secret = decryptSecret($user['totp_secret']);
        $hasSecret = true;
        $totp = new TOTP($secret);
        $uri = $totp->getProvisioningUri('StaticPorto', $_SESSION['admin']['username']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Google Authenticator - Admin</title>
    <script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
            padding: 40px 20px;
            color: #333;
        }
        .container { max-width: 500px; margin: 0 auto; }
        .card {
            background: #fff;
            border-radius: 12px;
            padding: 32px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.08);
            margin-bottom: 20px;
        }
        h1 { font-size: 1.4rem; font-weight: 700; color: #2c3e50; margin-bottom: 8px; }
        p.desc { font-size: 0.9rem; color: #7f8c8d; margin-bottom: 24px; }
        .secret-box {
            background: #f8f9fa;
            border: 2px dashed #dcdfe6;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            margin-bottom: 20px;
        }
        .secret-key {
            font-family: 'Courier New', monospace;
            font-size: 1.3rem;
            font-weight: 700;
            color: #2c3e50;
            letter-spacing: 3px;
            word-break: break-all;
        }
        .qr-area { text-align: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 16px; }
        .form-group label {
            display: block; font-weight: 600; font-size: 0.85rem;
            color: #2c3e50; margin-bottom: 6px;
        }
        .form-group input {
            width: 100%; padding: 11px 14px; border: 1px solid #dcdfe6;
            border-radius: 8px; font-size: 1.1rem; font-family: 'Courier New', monospace;
            text-align: center; letter-spacing: 8px;
        }
        .form-group input:focus {
            outline: none; border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52,152,219,0.15);
        }
        .btn {
            display: inline-block; padding: 10px 20px; border: none;
            border-radius: 8px; font-size: 0.9rem; font-weight: 600;
            font-family: inherit; cursor: pointer; transition: background 0.2s;
            margin-right: 8px;
        }
        .btn-primary { background: #3498db; color: #fff; }
        .btn-primary:hover { background: #2980b9; }
        .btn-secondary { background: #ecf0f1; color: #2c3e50; }
        .btn-secondary:hover { background: #dfe6e9; }
        .btn-success { background: #27ae60; color: #fff; }
        .btn-success:hover { background: #219a52; }
        .btn-danger { background: #e74c3c; color: #fff; }
        .btn-danger:hover { background: #c0392b; }
        .alert-error {
            background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;
            padding: 10px 14px; border-radius: 6px; font-size: 0.88rem; margin-bottom: 16px;
        }
        .alert-success {
            background: #d4edda; color: #155724; border: 1px solid #c3e6cb;
            padding: 10px 14px; border-radius: 6px; font-size: 0.88rem; margin-bottom: 16px;
        }
        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            color: #3498db; text-decoration: none; font-size: 0.9rem; font-weight: 600;
        }
        .back-link:hover { text-decoration: underline; }
        .steps { margin-bottom: 20px; }
        .steps li { font-size: 0.9rem; color: #555; margin-bottom: 8px; padding-left: 4px; }
        .warning-box {
            background: #fff3cd; color: #856404; border: 1px solid #ffc107;
            padding: 12px 16px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <br><br>
        <div class="card">
            <h1>Google Authenticator Setup</h1>
            <p class="desc">Configure Google Authenticator to login with 6-digit codes.</p>

            <?php if ($error): ?>
                <div class="alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <?php if ($message): ?>
                <div class="alert-success"><?= htmlspecialchars($message) ?></div>
            <?php endif; ?>

            <?php if (!$hasSecret): ?>
                <p style="font-size: 0.9rem; color: #555; margin-bottom: 20px;">
                    No authenticator configured yet. Click below to generate a secret key.
                </p>
                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="generate">
                    <button type="submit" class="btn btn-primary">Generate Secret Key</button>
                </form>
            <?php else: ?>
                <ol class="steps">
                    <li>Scan the QR code below with Google Authenticator app</li>
                    <li>Or manually enter the secret key in the app</li>
                    <li>Enter the 6-digit code from the app to verify</li>
                </ol>

                <div class="qr-area" id="qr-area"></div>

                <div class="secret-box">
                    <div style="font-size: 0.8rem; color: #7f8c8d; margin-bottom: 8px;">Secret Key (manual entry)</div>
                    <div class="secret-key"><?= htmlspecialchars($secret) ?></div>
                </div>

                <form method="POST">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="verify">
                    <div class="form-group">
                        <label for="code">Enter 6-digit code to verify</label>
                        <input type="text" id="code" name="code" maxlength="6" pattern="\d{6}"
                               placeholder="000000" required autofocus autocomplete="off">
                    </div>
                    <button type="submit" class="btn btn-success">Verify & Activate</button>
                </form>

                <br>
                <div class="warning-box">
                    After verifying, delete the setup files (setup-totp.php, migrate-totp.php) from the server.
                </div>
                <form method="POST" style="display: inline;">
                    <?= csrfField() ?>
                    <input type="hidden" name="action" value="generate">
                    <button type="submit" class="btn btn-secondary">Regenerate Secret</button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <script>
        // Generate QR code locally - no data sent to third parties
        (function() {
            var uri = <?= json_encode($uri) ?>;
            var qrArea = document.getElementById('qr-area');
            if (uri && qrArea) {
                var qr = qrcode(0, 'M');
                qr.addData(uri);
                qr.make();
                qrArea.innerHTML = qr.createSvgTag(5, 0);
            }
        })();
    </script>
</body>
</html>
