/**
 * Page: Senders Manager (senders.php).
 *
 * Lists unique senders from the inbox and supports bulk deletion by sender.
 * Loaded as a classic script; depends on the core and ui layers.
 */

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
