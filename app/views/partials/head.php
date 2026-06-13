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
