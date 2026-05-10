<?php
require_once __DIR__ . '/config.php';
$config = loadCredentials();
$client_id = $config['client_id'];
$csrf_token = $config['csrf_token'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Senders Manager - Gmail</title>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
        window.GMAIL_CONFIG = {
            clientId: "<?php echo htmlspecialchars($client_id, ENT_QUOTES, 'UTF-8'); ?>",
            csrfToken: "<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>"
        };
    </script>
    <style>
        .sender-row {
            transition: all 0.2s;
        }
        .sender-row:hover {
            transform: translateX(4px);
        }
        .progress-bar {
            transition: width 0.3s ease;
        }
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .loading {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
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
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-600 to-pink-600 dark:from-purple-400 dark:to-pink-400 text-center md:text-left">
            📧 Senders Manager
        </h1>
        <div class="flex flex-wrap justify-center items-center gap-4">
            <a href="manage_gmail.php" class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm md:text-base">Email Manager</a>
            <a href="all_emails.php" class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm md:text-base">Stats & Cleanup</a>
            <button id="themeToggle" onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <svg id="sunIcon" class="w-6 h-6 text-yellow-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <svg id="moonIcon" class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
            <button id="authorize_button" onclick="handleAuthClick()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow hidden text-sm md:text-base">Authorize</button>
            <button id="signout_button" onclick="handleSignoutClick()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow hidden text-sm md:text-base">Sign Out</button>
        </div>
    </div>

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

<script src="js/gmail_api.js" onerror="console.error('Failed to load gmail_api.js')"></script>
<script>
    // Initialize
    if (typeof initTheme === 'function') {
        initTheme();
    }

    let sendersData = [];
    let filteredSenders = [];

    // Override onAuthSuccess
    if (typeof onAuthSuccess !== 'undefined') {
        const originalOnAuthSuccess = onAuthSuccess;
        onAuthSuccess = function() {
            originalOnAuthSuccess();
            console.log('Auth success - ready to analyze');
        };
    }

    async function loadSenders() {
        if (typeof checkRateLimit !== 'undefined' && !checkRateLimit()) return;

        const sendersBody = document.getElementById('sendersBody');
        sendersBody.innerHTML = '<div class="text-center py-12 loading"><p class="text-gray-600 dark:text-gray-400">Loading unique senders...</p></div>';

        try {
            updateAuthStatus('Fetching sender list...', true);
            
            // Use Gmail's search to get unique senders directly
            const senderSet = new Set();
            let pageToken = null;
            let processedCount = 0;
            const maxEmails = 5000;

            do {
                const response = await gapi.client.gmail.users.messages.list({
                    'userId': 'me',
                    'maxResults': 500,
                    'pageToken': pageToken
                });

                const messages = response.result.messages || [];
                
                // Process in batches
                const batchSize = 100;
                for (let i = 0; i < messages.length; i += batchSize) {
                    const batch = messages.slice(i, i + batchSize);
                    
                    const metadataPromises = batch.map(msg => 
                        gapi.client.gmail.users.messages.get({
                            'userId': 'me',
                            'id': msg.id,
                            'format': 'metadata',
                            'metadataHeaders': ['From']
                        })
                    );

                    const results = await Promise.allSettled(metadataPromises);

                    results.forEach(result => {
                        if (result.status === 'fulfilled' && result.value && result.value.result) {
                            const headers = result.value.result.payload.headers;
                            const fromHeader = headers.find(h => h.name === 'From');
                            
                            if (fromHeader) {
                                const from = fromHeader.value;
                                const emailMatch = from.match(/<(.+?)>/) || from.match(/([^\s]+@[^\s]+)/);
                                const email = emailMatch ? emailMatch[1] : from;
                                const name = from.replace(/<.*?>/, '').trim() || email;
                                
                                // Store as JSON string to keep both email and name
                                senderSet.add(JSON.stringify({ email, name }));
                            }
                        }
                    });

                    if (i + batchSize < messages.length) {
                        await new Promise(resolve => setTimeout(resolve, 50));
                    }
                }

                processedCount += messages.length;
                updateAuthStatus(`Scanned ${processedCount} emails...`, true);

                pageToken = response.result.nextPageToken;

                if (processedCount >= maxEmails) {
                    break;
                }

                if (pageToken) {
                    await new Promise(resolve => setTimeout(resolve, 100));
                }

            } while (pageToken);

            // Convert set to array of objects
            sendersData = Array.from(senderSet).map(s => JSON.parse(s)).sort((a, b) => a.name.localeCompare(b.name));
            filteredSenders = [...sendersData];

            updateAuthStatus(`Found ${sendersData.length} unique senders`, true);
            renderSenders();
            updateStats();

        } catch (e) {
            console.error('Error loading senders:', e);
            
            if (e.status === 429 || (e.result && e.result.error && e.result.error.code === 429)) {
                sendersBody.innerHTML = `
                    <div class="text-center py-12 text-orange-600 dark:text-orange-400">
                        <svg class="w-16 h-16 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                        </svg>
                        <p class="text-lg font-semibold mb-2">Rate Limit Exceeded</p>
                        <p class="text-sm">Please wait a few minutes and try again.</p>
                    </div>
                `;
                updateAuthStatus('Rate limit exceeded', false);
            } else {
                sendersBody.innerHTML = `<div class="text-center py-12 text-red-500"><p>Error: ${e.message || 'Unknown error'}</p></div>`;
                updateAuthStatus('Error loading senders', false);
            }
        }
    }

    function renderSenders() {
        const sendersBody = document.getElementById('sendersBody');
        const statsContainer = document.getElementById('statsContainer');
        
        if (filteredSenders.length === 0) {
            sendersBody.innerHTML = '<div class="text-center py-12 text-gray-500 dark:text-gray-400"><p>No senders found</p></div>';
            statsContainer.classList.add('hidden');
            return;
        }

        statsContainer.classList.remove('hidden');

        sendersBody.innerHTML = filteredSenders.map(sender => `
            <div class="sender-row flex items-center gap-4 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 border border-gray-200 dark:border-gray-600">
                <input type="checkbox" 
                       class="sender-checkbox form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500" 
                       value="${sender.email}"
                       onchange="updateSelectionStats()">
                
                <div class="flex-grow min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="font-semibold text-gray-900 dark:text-white truncate">${escapeHtml(sender.name)}</span>
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 truncate">${escapeHtml(sender.email)}</div>
                </div>

                <button onclick="deleteSingleSender('${escapeHtml(sender.email)}')" 
                        class="flex-shrink-0 bg-red-100 hover:bg-red-200 dark:bg-red-900/30 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-medium py-2 px-4 rounded-lg transition text-sm">
                    Delete All
                </button>
            </div>
        `).join('');
    }

    function filterSenders() {
        const searchTerm = document.getElementById('searchSender').value.toLowerCase();
        
        if (searchTerm === '') {
            filteredSenders = [...sendersData];
        } else {
            filteredSenders = sendersData.filter(sender => 
                sender.name.toLowerCase().includes(searchTerm) || 
                sender.email.toLowerCase().includes(searchTerm)
            );
        }
        
        sortSenders();
    }

    function sortSenders() {
        const sortBy = document.getElementById('sortBy').value;
        
        switch(sortBy) {
            case 'count-desc':
                filteredSenders.sort((a, b) => b.count - a.count);
                break;
            case 'count-asc':
                filteredSenders.sort((a, b) => a.count - b.count);
                break;
            case 'name-asc':
                filteredSenders.sort((a, b) => a.name.localeCompare(b.name));
                break;
            case 'name-desc':
                filteredSenders.sort((a, b) => b.name.localeCompare(a.name));
                break;
        }
        
        renderSenders();
    }

    function toggleSelectAll(checkbox) {
        const checkboxes = document.querySelectorAll('.sender-checkbox');
        checkboxes.forEach(cb => cb.checked = checkbox.checked);
        updateSelectionStats();
    }

    function updateSelectionStats() {
        const checkboxes = document.querySelectorAll('.sender-checkbox:checked');
        const selectedCount = checkboxes.length;

        document.getElementById('selectedCount').textContent = selectedCount;
    }

    function updateStats() {
        const totalSenders = sendersData.length;

        document.getElementById('totalSenders').textContent = totalSenders.toLocaleString();
        document.getElementById('selectedCount').textContent = '0';
    }

    async function deleteSelectedSenders() {
        const checkboxes = document.querySelectorAll('.sender-checkbox:checked');
        
        if (checkboxes.length === 0) {
            showAlert('No Selection', 'Please select at least one sender to delete.');
            return;
        }

        const senderEmails = Array.from(checkboxes).map(cb => cb.value);

        showConfirm(
            'Delete Emails from Multiple Senders',
            `Are you sure you want to delete ALL emails from ${senderEmails.length} sender(s)? This cannot be undone.`,
            async () => {
                await batchDeleteSenders(senderEmails);
            }
        );
    }

    async function deleteSingleSender(email) {
        showConfirm(
            'Delete All Emails from Sender',
            `Are you sure you want to delete ALL emails from "${email}"? This cannot be undone.`,
            async () => {
                await batchDeleteSenders([email]);
            }
        );
    }

    async function batchDeleteSenders(senderEmails) {
        const modal = document.getElementById('progressModal');
        const progressBar = document.getElementById('progressBar');
        const progressText = document.getElementById('progressText');
        const progressPercent = document.getElementById('progressPercent');
        const progressDetails = document.getElementById('progressDetails');

        modal.classList.remove('hidden');

        try {
            let totalDeleted = 0;

            for (let i = 0; i < senderEmails.length; i++) {
                const email = senderEmails[i];

                progressText.textContent = `Processing ${i + 1} of ${senderEmails.length}`;
                progressDetails.textContent = `Searching emails from: ${email}`;

                // Search for all emails from this sender
                let allIds = [];
                let pageToken = null;
                let hasMore = true;
                
                while (hasMore) {
                    const response = await gapi.client.gmail.users.messages.list({
                        'userId': 'me',
                        'q': `from:${email}`,
                        'pageToken': pageToken,
                        'maxResults': 500
                    });
                    
                    const messages = response.result.messages || [];
                    allIds = allIds.concat(messages.map(m => m.id));
                    
                    pageToken = response.result.nextPageToken;
                    if (!pageToken) hasMore = false;
                    
                    progressDetails.textContent = `Found ${allIds.length} emails from ${email}...`;
                }

                if (allIds.length === 0) {
                    progressDetails.textContent = `No emails found from ${email}`;
                    continue;
                }

                // Delete in chunks of 1000
                const chunkSize = 1000;
                for (let j = 0; j < allIds.length; j += chunkSize) {
                    const chunk = allIds.slice(j, j + chunkSize);
                    
                    await gapi.client.gmail.users.messages.batchDelete({
                        'userId': 'me',
                        'ids': chunk
                    });

                    totalDeleted += chunk.length;
                    
                    const percent = Math.round(((i + (j / allIds.length)) / senderEmails.length) * 100);
                    progressBar.style.width = percent + '%';
                    progressPercent.textContent = percent + '%';
                    progressDetails.textContent = `Deleted ${totalDeleted.toLocaleString()} emails so far...`;
                }
            }

            modal.classList.add('hidden');
            showAlert('Success', `Successfully deleted ${totalDeleted.toLocaleString()} emails from ${senderEmails.length} sender(s).`);
            
            // Reload senders
            loadSenders();

        } catch (e) {
            console.error('Error deleting emails:', e);
            modal.classList.add('hidden');
            showAlert('Error', 'An error occurred while deleting emails: ' + e.message);
        }
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
</script>
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()" onerror="console.error('Failed to load Google API')"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()" onerror="console.error('Failed to load Google Identity Services')"></script>
</body>
</html>
