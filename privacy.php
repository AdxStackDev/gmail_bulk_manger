<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];

$authMode = ''; // Public page
$pageTitle = 'Privacy Policy - Adx Mail Manager';
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/app/views/partials/head.php'; ?>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans text-gray-800 dark:text-gray-200 transition-colors duration-300">

<div class="container mx-auto px-4 py-8 max-w-4xl">
    <!-- Header -->
    <div class="flex justify-between items-center mb-8 border-b border-gray-200 dark:border-gray-800 pb-4">
        <a href="manage_gmail.php" class="text-2xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400">
            Adx Mail Manager
        </a>
        <div class="flex items-center gap-4">
            <a href="manage_gmail.php" class="text-sm font-medium text-blue-600 dark:text-blue-400 hover:underline">Back to App</a>
            <?php include __DIR__ . '/app/views/partials/theme-toggle.php'; ?>
        </div>
    </div>

    <!-- Content -->
    <div class="bg-white dark:bg-gray-800 rounded-xl shadow-xl p-6 md:p-10 space-y-6">
        <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white">Privacy Policy</h1>
        <p class="text-xs text-gray-500 dark:text-gray-400">Last updated: August 13, 2026</p>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">1. Introduction</h2>
            <p>Welcome to <strong>Adx Mail Manager</strong> ("we", "our", or "us"). We respect your privacy and are committed to protecting the data you share when using our web application located at <code class="bg-gray-100 dark:bg-gray-700 px-2 py-0.5 rounded">mails-managerprov1-5.onrender.com</code>.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">2. Information We Access</h2>
            <p>Adx Mail Manager interacts with your Google account via official Google OAuth 2.0 APIs. We access:</p>
            <ul class="list-disc list-inside space-y-1 pl-2 text-sm">
                <td><strong>Google Profile Information:</strong> Your email address and basic profile info to authenticate you.</td>
                <td><strong>Gmail API Data:</strong> Message headers (From, Subject, Date), message body, labels, and thread counts required to display and clean up your emails.</td>
            </ul>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">3. How We Use Information</h2>
            <p>We use your information strictly to provide you with the email management tools offered by the application:</p>
            <ul class="list-disc list-inside space-y-1 pl-2 text-sm">
                <td>Displaying your email list, stats, and unique sender addresses.</td>
                <td>Performing actions strictly initiated by you (such as bulk email deletion, label modification, or sender cleanup).</td>
            </ul>
            <p class="font-semibold text-red-600 dark:text-red-400 text-sm">We DO NOT store your email messages, credentials, or personal data on external databases. OAuth access tokens are kept securely in your local browser session storage and expire automatically.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">4. Data Sharing & Third Parties</h2>
            <p>We do <strong>NOT</strong> sell, rent, share, or transfer your personal data or email contents to any third parties, advertisers, or AI/LLM models under any circumstances.</p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">5. Google API Limited Use Disclosure</h2>
            <p class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 p-4 rounded-lg text-sm text-blue-900 dark:text-blue-200">
                Adx Mail Manager's use and transfer of information received from Google APIs to any other app will adhere to the <a href="https://developers.google.com/terms/api-services-user-data-policy" target="_blank" rel="noopener noreferrer" class="underline font-semibold">Google API Services User Data Policy</a>, including the Limited Use requirements.
            </p>
        </section>

        <section class="space-y-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white border-b pb-1 border-gray-200 dark:border-gray-700">6. Revoking Access</h2>
            <p>You can revoke Adx Mail Manager's access to your Google account at any time by visiting your <a href="https://myaccount.google.com/permissions" target="_blank" rel="noopener noreferrer" class="text-blue-600 dark:text-blue-400 underline">Google Account Security Settings</a>.</p>
        </section>

        <section class="space-y-3 border-t border-gray-200 dark:border-gray-700 pt-4">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">7. Contact Us</h2>
            <p class="text-sm">If you have any questions about this Privacy Policy, please contact the developer via Google Cloud Console or the repository project owner.</p>
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
