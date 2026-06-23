<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];

// Protected page: require sign-in (redirects to login.php when signed out)
$authMode = 'require';

// View configuration for the shared head/header partials
$pageTitle = 'Senders Manager - Gmail';
$headingText = '📧 Senders Manager';
$headingClasses = 'from-purple-600 to-pink-600 dark:from-purple-400 dark:to-pink-400';
$navLinks = [
    ['href' => 'manage_gmail.php', 'label' => 'Email Manager', 'class' => 'text-blue-600 dark:text-blue-400'],
    ['href' => 'all_emails.php', 'label' => 'Stats & Cleanup', 'class' => 'text-blue-600 dark:text-blue-400'],
];
$authBtnClass = 'bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow';
$signoutBtnClass = 'bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow';
?>
<!DOCTYPE html>
<html lang="en">
<?php include __DIR__ . '/app/views/partials/head.php'; ?>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<!-- Progress Modal -->
<div id="progressModal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Deleting Emails</h3>
        <div class="mb-4">
            <div class="flex justify-between text-sm text-gray-600 dark:text-gray-400 mb-2">
                <span id="progressText">Processing...</span>
                <span id="progressPercent">0%</span>
            </div>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2.5">
                <div id="progressBar" class="progress-bar bg-blue-600 h-2.5 rounded-full" style="width: 0%"></div>
            </div>
        </div>
        <p id="progressDetails" class="text-sm text-gray-500 dark:text-gray-400">Preparing...</p>
    </div>
</div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <!-- Header -->
    <?php include __DIR__ . '/app/views/partials/header.php'; ?>


    <!-- Controls -->
    <div id="controls" class="hidden">
        <!-- Info Banner -->
        <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 dark:text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-800 dark:text-blue-300">
                    <p class="font-semibold mb-1">How It Works</p>
                    <p>This page lists all unique sender email addresses from your inbox. Select senders and click "Delete Selected" to remove ALL emails from those senders. The app will search and delete all matching emails in real-time.</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white dark:bg-gray-800 p-4 md:p-6 rounded-xl shadow-lg mb-6">
            <div class="flex flex-col md:flex-row gap-4 items-stretch md:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-3">
                    <button onclick="loadSenders()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-semibold py-2 px-6 rounded-lg transition shadow-md text-sm md:text-base">
                        🔄 Load Senders
                    </button>
                    <button onclick="deleteSelectedSenders()" class="bg-gradient-to-r from-red-600 to-red-700 hover:from-red-700 hover:to-red-800 text-white font-semibold py-2 px-6 rounded-lg transition shadow-md text-sm md:text-base">
                        🗑️ Delete Selected
                    </button>
                </div>
                <div class="flex gap-2 items-center">
                    <input type="text" id="searchSender" placeholder="Search senders..." class="flex-grow md:w-64 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-sm md:text-base" onkeyup="filterSenders()">
                    <select id="sortBy" onchange="sortSenders()" class="bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-sm md:text-base">
                        <option value="count-desc">Most Emails</option>
                        <option value="count-asc">Least Emails</option>
                        <option value="name-asc">Name A-Z</option>
                        <option value="name-desc">Name Z-A</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Stats Summary -->
        <div id="statsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 hidden">
            <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white">
                <div class="text-sm opacity-90">Total Unique Senders</div>
                <div id="totalSenders" class="text-3xl font-bold mt-2">0</div>
            </div>
            <div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white">
                <div class="text-sm opacity-90">Selected Senders</div>
                <div id="selectedCount" class="text-3xl font-bold mt-2">0</div>
            </div>
        </div>

        <!-- Senders List -->
        <div id="sendersContainer" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800 dark:text-white">Email Senders</h2>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll(this)" class="form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500">
                        <span class="text-sm text-gray-600 dark:text-gray-400">Select All</span>
                    </label>
                </div>
                
                <div id="sendersBody" class="space-y-2">
                    <div class="text-center py-12 text-gray-500 dark:text-gray-400">
                        <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-lg font-medium">Click "Load Senders" to get started</p>
                        <p class="text-sm mt-2">This will load all unique sender email addresses from your inbox</p>
                    </div>
                </div>
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
<script src="js/pages/senders.js"></script>
<!-- Google SDKs -->
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()" onerror="console.error('Failed to load Google API')"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()" onerror="console.error('Failed to load Google Identity Services')"></script>
</body>
</html>
