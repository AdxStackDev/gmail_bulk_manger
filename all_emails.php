<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];

// Protected page: require sign-in (redirects to login.php when signed out)
$authMode = 'require';

// View configuration for the shared head/header partials
$pageTitle = 'Gmail Stats & Cleanup';
$headingText = 'Gmail Cleanup';
$headingClasses = 'from-red-600 to-orange-600 dark:from-red-400 dark:to-orange-400';
$navLinks = [
    ['href' => 'manage_gmail.php', 'label' => 'Back to Manager', 'class' => 'text-blue-600 dark:text-blue-400'],
    ['href' => 'senders.php', 'label' => 'Senders Manager', 'class' => 'text-purple-600 dark:text-purple-400'],
];
$authBtnClass = 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow';
$signoutBtnClass = 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow';
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/app/views/partials/head.php'; ?>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <?php include __DIR__ . '/app/views/partials/header.php'; ?>


    <div id="controls" class="hidden">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Stats Card -->
            <div class="bg-white dark:bg-gray-800 p-6 rounded-xl shadow-lg">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-white mb-4">Account Statistics</h2>
                <div class="space-y-4">
                    <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2">
                        <span class="text-gray-600 dark:text-gray-400">Total Messages</span>
                        <span id="totalMessages" class="text-2xl font-bold text-blue-600 dark:text-blue-400">Loading...</span>
                    </div>
                    <div class="flex justify-between items-center border-b dark:border-gray-700 pb-2">
                        <span class="text-gray-600 dark:text-gray-400">Total Threads</span>
                        <span id="totalThreads" class="text-2xl font-bold text-purple-600 dark:text-purple-400">Loading...</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600 dark:text-gray-400">Email Address</span>
                        <span id="emailAddress" class="font-medium text-gray-800 dark:text-white">Loading...</span>
                    </div>
                </div>
                <button onclick="loadStats()" class="mt-6 w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-white font-semibold py-2 px-4 rounded transition">
                    Refresh Stats
                </button>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 dark:bg-red-900/20 p-6 rounded-xl shadow-lg border border-red-100 dark:border-red-900/50">
                <h2 class="text-xl font-semibold text-red-700 dark:text-red-400 mb-4">Danger Zone</h2>
                <p class="text-gray-600 dark:text-gray-300 mb-6">
                    These actions are irreversible. Please be certain before proceeding.
                </p>
                
                <button onclick="deleteAllEmails()" class="w-full bg-red-600 hover:bg-red-700 text-white font-bold py-3 px-4 rounded shadow-lg transition transform hover:scale-[1.02]">
                    DELETE ALL EMAILS
                </button>
                <p class="text-xs text-red-500 dark:text-red-400 mt-2 text-center">
                    * This will delete every single email in your account.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- UI layer -->
<script src="js/ui/toast.js"></script>
<script src="js/ui/modal.js"></script>
<script src="js/ui/theme.js"></script>
<!-- Core layer -->
<script src="js/core/session.js"></script>
<script src="js/core/gmailClient.js"></script>
<script src="js/core/auth.js"></script>
<!-- Page layer -->
<script src="js/pages/stats.js"></script>
<!-- Google SDKs -->
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()" onerror="console.error('Failed to load Google API')"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()" onerror="console.error('Failed to load Google Identity Services')"></script>
</body>
</html>
