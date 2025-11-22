<?php
$creds_file = __DIR__ . '/credentials.json';
if (!file_exists($creds_file)) {
    die('Error: credentials.json not found');
}
$creds = json_decode(file_get_contents($creds_file), true);
$client_id = $creds['web']['client_id'] ?? $creds['installed']['client_id'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gmail Stats & Cleanup</title>
    <meta charset="utf-8"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
        window.GMAIL_CONFIG = {
            clientId: "<?php echo $client_id; ?>"
        };
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-red-600 to-orange-600 dark:from-red-400 dark:to-orange-400 text-center md:text-left">
            Gmail Cleanup
        </h1>
        <div class="flex flex-wrap justify-center items-center gap-4">
            <a href="manage_gmail.php" class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm md:text-base">Back to Manager</a>
            <button id="themeToggle" onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <svg id="sunIcon" class="w-6 h-6 text-yellow-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg id="moonIcon" class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
            <button id="authorize_button" onclick="handleAuthClick()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow hidden text-sm md:text-base">Authorize</button>
            <button id="signout_button" onclick="handleSignoutClick()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow hidden text-sm md:text-base">Sign Out</button>
        </div>
    </div>

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

<script src="js/gmail_api.js"></script>
<script>
    initTheme();

    // Override onAuthSuccess to load stats
    const originalOnAuthSuccess = onAuthSuccess;
    onAuthSuccess = function() {
        originalOnAuthSuccess();
        loadStats();
    };

    async function loadStats() {
        try {
            const profile = await gapi.client.gmail.users.getProfile({
                'userId': 'me'
            });
            
            document.getElementById('totalMessages').textContent = profile.result.messagesTotal.toLocaleString();
            document.getElementById('totalThreads').textContent = profile.result.threadsTotal.toLocaleString();
            document.getElementById('emailAddress').textContent = profile.result.emailAddress;
            
        } catch (e) {
            console.error(e);
            showAlert('Error', 'Failed to load stats: ' + e.message);
        }
    }

    async function deleteAllEmails() {
        showConfirm('NUCLEAR OPTION', 'WARNING: You are about to delete ALL emails in your account. This cannot be undone. Are you absolutely sure?', async () => {
            // Double confirmation
            const verification = prompt("To confirm, type 'DELETE' in the box below:");
            if (verification !== 'DELETE') {
                return showAlert('Cancelled', 'Deletion cancelled. Verification failed.');
            }

            updateAuthStatus('Starting deletion...', true);
            
            try {
                let pageToken = null;
                let hasMore = true;
                let deletedCount = 0;

                while (hasMore) {
                    // List messages
                    const response = await gapi.client.gmail.users.messages.list({
                        'userId': 'me',
                        'maxResults': 500,
                        'pageToken': pageToken
                    });

                    const messages = response.result.messages;
                    if (!messages || messages.length === 0) {
                        hasMore = false;
                        break;
                    }

                    // Batch delete
                    const ids = messages.map(m => m.id);
                    await gapi.client.gmail.users.messages.batchDelete({
                        'userId': 'me',
                        'ids': ids
                    });

                    deletedCount += ids.length;
                    updateAuthStatus(`Deleted ${deletedCount} emails...`, true);

                    pageToken = response.result.nextPageToken;
                    if (!pageToken) hasMore = false;
                }

                updateAuthStatus('All emails deleted.', true);
                loadStats();
                showAlert('Complete', `Successfully deleted ${deletedCount} emails.`);

            } catch (e) {
                console.error(e);
                updateAuthStatus('Error during deletion', false);
                showAlert('Error', 'An error occurred: ' + e.message);
            }
        });
    }
</script>
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>
