<?php
/**
 * Shared <head> partial.
 *
 * Expects the following variables to be defined by the including page:
 * @var string $pageTitle
 * @var string $client_id
 * @var string $csrf_token
 */
?>
<head>
    <?php
    /**
     * Auth gate (runs as early as possible to avoid content flash).
     * $authMode is set by the including page:
     *   'require' -> protected page: redirect to login if NOT signed in
     *   'guest'   -> login page: redirect to target if ALREADY signed in
     */
    $authMode = $authMode ?? '';
    ?>
    <?php if ($authMode === 'require'): ?>
    <script>
    (function () {
        try {
            var tok = sessionStorage.getItem('gmail_access_token') || localStorage.getItem('gmail_access_token');
            var exp = sessionStorage.getItem('gmail_token_expiry') || localStorage.getItem('gmail_token_expiry');
            if (!(tok && exp && Date.now() < parseInt(exp, 10))) {
                var here = location.pathname.split('/').pop() + location.search;
                location.replace('login.php?redirect=' + encodeURIComponent(here));
            }
        } catch (e) {}
    })();
    </script>
    <?php elseif ($authMode === 'guest'): ?>
    <script>
    (function () {
        try {
            var tok = sessionStorage.getItem('gmail_access_token') || localStorage.getItem('gmail_access_token');
            var exp = sessionStorage.getItem('gmail_token_expiry') || localStorage.getItem('gmail_token_expiry');
            if (tok && exp && Date.now() < parseInt(exp, 10)) {
                var params = new URLSearchParams(location.search);
                var dest = params.get('redirect') || 'manage_gmail.php';
                if (!/^[\w.\-]+\.php(\?.*)?$/.test(dest)) dest = 'manage_gmail.php';
                location.replace(dest);
            }
        } catch (e) {}
    })();
    </script>
    <?php endif; ?>
    <title><?php echo htmlspecialchars($pageTitle ?? 'Gmail Manager', ENT_QUOTES, 'UTF-8'); ?></title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        };
        window.GMAIL_CONFIG = {
            clientId: "<?php echo htmlspecialchars($client_id, ENT_QUOTES, 'UTF-8'); ?>",
            csrfToken: "<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <link rel="stylesheet" href="assets/css/app.css">
</head>
