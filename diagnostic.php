<?php
/**
 * Diagnostic Page - Check if everything is configured correctly
 */
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gmail Manager - Diagnostics</title>
    <meta charset="utf-8"/>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .check {
            background: white;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #ccc;
        }
        .check.success {
            border-left-color: #4CAF50;
        }
        .check.error {
            border-left-color: #f44336;
        }
        .check.warning {
            border-left-color: #ff9800;
        }
        h1 {
            color: #333;
        }
        code {
            background: #f0f0f0;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
        .status {
            font-weight: bold;
            margin-right: 10px;
        }
        .success .status { color: #4CAF50; }
        .error .status { color: #f44336; }
        .warning .status { color: #ff9800; }
    </style>
</head>
<body>
    <h1>🔍 Gmail Manager Diagnostics</h1>
    <p>This page checks if your Gmail Manager is configured correctly.</p>

    <?php
    // Check 1: PHP Version
    $phpVersion = phpversion();
    $phpOk = version_compare($phpVersion, '7.4.0', '>=');
    ?>
    <div class="check <?php echo $phpOk ? 'success' : 'error'; ?>">
        <span class="status"><?php echo $phpOk ? '✓' : '✗'; ?></span>
        <strong>PHP Version:</strong> <?php echo $phpVersion; ?>
        <?php if (!$phpOk): ?>
            <br><small>⚠️ PHP 7.4 or higher is required</small>
        <?php endif; ?>
    </div>

    <?php
    // Check 2: credentials.json exists
    $credsFile = __DIR__ . '/credentials.json';
    $credsExists = file_exists($credsFile);
    ?>
    <div class="check <?php echo $credsExists ? 'success' : 'error'; ?>">
        <span class="status"><?php echo $credsExists ? '✓' : '✗'; ?></span>
        <strong>credentials.json:</strong> <?php echo $credsExists ? 'Found' : 'Not Found'; ?>
        <?php if (!$credsExists): ?>
            <br><small>⚠️ Copy <code>credentials.json.example</code> to <code>credentials.json</code> and add your OAuth credentials</small>
        <?php endif; ?>
    </div>

    <?php
    // Check 3: credentials.json is valid JSON
    $credsValid = false;
    $clientId = null;
    if ($credsExists) {
        $credsContent = file_get_contents($credsFile);
        $creds = json_decode($credsContent, true);
        $credsValid = ($creds !== null && json_last_error() === JSON_ERROR_NONE);
        if ($credsValid) {
            $clientId = $creds['web']['client_id'] ?? $creds['installed']['client_id'] ?? null;
        }
    }
    ?>
    <div class="check <?php echo $credsValid ? 'success' : 'error'; ?>">
        <span class="status"><?php echo $credsValid ? '✓' : '✗'; ?></span>
        <strong>credentials.json Format:</strong> <?php echo $credsValid ? 'Valid JSON' : 'Invalid or corrupted'; ?>
        <?php if (!$credsValid && $credsExists): ?>
            <br><small>⚠️ The file exists but contains invalid JSON</small>
        <?php endif; ?>
    </div>

    <?php
    // Check 4: Client ID exists
    $hasClientId = !empty($clientId);
    ?>
    <div class="check <?php echo $hasClientId ? 'success' : 'error'; ?>">
        <span class="status"><?php echo $hasClientId ? '✓' : '✗'; ?></span>
        <strong>OAuth Client ID:</strong> <?php echo $hasClientId ? 'Found' : 'Not Found'; ?>
        <?php if ($hasClientId): ?>
            <br><small>Client ID: <code><?php echo substr($clientId, 0, 20); ?>...</code></small>
        <?php else: ?>
            <br><small>⚠️ No client_id found in credentials.json</small>
        <?php endif; ?>
    </div>

    <?php
    // Check 5: Session support
    $sessionOk = false;
    try {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $sessionOk = (session_status() === PHP_SESSION_ACTIVE);
    } catch (Exception $e) {
        $sessionOk = false;
    }
    ?>
    <div class="check <?php echo $sessionOk ? 'success' : 'error'; ?>">
        <span class="status"><?php echo $sessionOk ? '✓' : '✗'; ?></span>
        <strong>PHP Sessions:</strong> <?php echo $sessionOk ? 'Working' : 'Not Working'; ?>
        <?php if (!$sessionOk): ?>
            <br><small>⚠️ Sessions are required for CSRF protection</small>
        <?php endif; ?>
    </div>

    <?php
    // Check 6: Required files exist
    $requiredFiles = [
        'config.php' => 'Configuration file',
        'manage_gmail.php' => 'Main manager page',
        'all_emails.php' => 'Stats & cleanup page',
        'js/gmail_api.js' => 'Gmail API JavaScript'
    ];
    
    foreach ($requiredFiles as $file => $description):
        $exists = file_exists(__DIR__ . '/' . $file);
    ?>
    <div class="check <?php echo $exists ? 'success' : 'error'; ?>">
        <span class="status"><?php echo $exists ? '✓' : '✗'; ?></span>
        <strong><?php echo $file; ?>:</strong> <?php echo $exists ? 'Found' : 'Missing'; ?>
        <br><small><?php echo $description; ?></small>
    </div>
    <?php endforeach; ?>

    <?php
    // Check 7: .gitignore exists
    $gitignoreExists = file_exists(__DIR__ . '/.gitignore');
    ?>
    <div class="check <?php echo $gitignoreExists ? 'success' : 'warning'; ?>">
        <span class="status"><?php echo $gitignoreExists ? '✓' : '⚠'; ?></span>
        <strong>.gitignore:</strong> <?php echo $gitignoreExists ? 'Found' : 'Not Found'; ?>
        <?php if (!$gitignoreExists): ?>
            <br><small>⚠️ Recommended to prevent committing credentials.json</small>
        <?php endif; ?>
    </div>

    <hr style="margin: 30px 0;">
    
    <?php
    $allGood = $phpOk && $credsExists && $credsValid && $hasClientId && $sessionOk;
    ?>
    
    <?php if ($allGood): ?>
        <div class="check success">
            <h2 style="margin: 0;">🎉 All checks passed!</h2>
            <p>Your Gmail Manager is configured correctly. You can now:</p>
            <ul>
                <li><a href="manage_gmail.php">Open Gmail Manager</a></li>
                <li><a href="all_emails.php">Open Stats & Cleanup</a></li>
            </ul>
        </div>
    <?php else: ?>
        <div class="check error">
            <h2 style="margin: 0;">❌ Configuration Issues Found</h2>
            <p>Please fix the errors above before using the application.</p>
            <p>See <a href="README.md">README.md</a> for setup instructions.</p>
        </div>
    <?php endif; ?>

    <hr style="margin: 30px 0;">
    
    <h2>📚 Next Steps</h2>
    <ol>
        <li>Fix any errors shown above</li>
        <li>Visit <a href="https://console.cloud.google.com/" target="_blank">Google Cloud Console</a> to set up OAuth</li>
        <li>Add your domain to authorized JavaScript origins</li>
        <li>Test the application with a test Gmail account first</li>
    </ol>

    <p style="text-align: center; color: #666; margin-top: 50px;">
        <small>Gmail Manager v1.0 | <a href="README.md">Documentation</a></small>
    </p>
</body>
</html>
