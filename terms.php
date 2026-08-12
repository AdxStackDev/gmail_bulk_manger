<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];

$authMode = ''; // Public page
$pageTitle = 'Terms of Service - Gmail Manager';
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/app/views/partials/head.php'; ?>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans text-gray-800 dark:text-gray-200 transition-colors duration-300">

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-800 pb-4">
        <a href="manage_gmail.php" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
            Gmail Manager
        </a>
        <div class="flex items-center gap-4">
            <a href="manage_gmail.php" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Back to App</a>
            <?php include __DIR__ . '/app/views/partials/theme-toggle.php'; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 md:p-10 space-y-6">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Terms of Service</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: August 13, 2026</p>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">1. Acceptance of Terms</h2>
            <p>By accessing or using <strong>Gmail Manager</strong>, you agree to be bound by these Terms of Service. If you do not agree to these terms, please do not use the application.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">2. Description of Service</h2>
            <p>Gmail Manager provides an interface to organize, filter, and batch delete emails from your Gmail inbox using official Google APIs.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">3. User Responsibility & Permanent Actions</h2>
            <p class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 p-4 rounded-lg text-sm text-amber-900 dark:text-amber-200">
                <strong>⚠️ Warning:</strong> Deleting emails through Gmail Manager is a permanent action. You are solely responsible for reviewing emails before triggering any bulk deletion. The application authors are not liable for accidental data loss.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">4. Limitation of Liability</h2>
            <p>The application is provided "AS IS" and "AS AVAILABLE" without warranties of any kind. In no event shall the authors or copyright holders be liable for any claims, damages, or data loss arising from the use of this software.</p>
        </section>

        <section class="space-y-3 border-t border-gray-200 dark:border-gray-700 pt-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">5. Termination</h2>
            <p class="text-sm">You may stop using the service at any time by revoking access in your Google Security settings or closing the application.</p>
        </section>
    </div>

    <!-- Footer -->
    <div class="mt-8 text-center text-xs text-gray-500 dark:text-gray-400 space-x-4">
        <span>© 2026 Gmail Manager</span>
        <span>•</span>
        <a href="privacy.php" class="hover:underline text-blue-600 dark:text-blue-400">Privacy Policy</a>
        <span>•</span>
        <a href="terms.php" class="hover:underline text-blue-600 dark:text-blue-400">Terms of Service</a>
    </div>
</div>

<script src="js/ui/theme.js"></script>
<script>
    if (typeof initTheme === 'function') initTheme();
</script>
</body>
</html>
