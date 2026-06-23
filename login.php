<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];

// Login page: bounce already-signed-in users back to their target page.
$authMode = 'guest';
$pageTitle = 'Sign In - Gmail Manager';
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/app/views/partials/head.php'; ?>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<div class="container mx-auto px-4 py-6 max-w-7xl">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
            Gmail Manager
        </h1>
        <?php include __DIR__ . '/app/views/partials/theme-toggle.php'; ?>
    </div>

    <?php include __DIR__ . '/app/views/partials/login.php'; ?>
</div>

<!-- UI layer -->
<script src="js/ui/toast.js"></script>
<script src="js/ui/modal.js"></script>
<script src="js/ui/theme.js"></script>
<!-- Core layer -->
<script src="js/core/session.js"></script>
<script src="js/core/gmailClient.js"></script>
<script src="js/core/auth.js"></script>
<!-- Login page behavior -->
<script>
    if (typeof initTheme === 'function') initTheme();

    (function () {
        var params = new URLSearchParams(location.search);
        var dest = params.get('redirect') || 'manage_gmail.php';
        if (!/^[\w.\-]+\.php(\?.*)?$/.test(dest)) dest = 'manage_gmail.php';
        // After a successful sign-in, go to the originally requested page.
        onAuthSuccess = function () {
            location.replace(dest);
        };
    })();
</script>
<!-- Google SDKs -->
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>
