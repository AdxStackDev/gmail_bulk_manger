/**
 * Page: Stats & Cleanup (all_emails.php).
 *
 * Shows account statistics and provides the "delete all emails" action.
 * Wraps onAuthSuccess to load stats after sign-in. Loaded as a classic script.
 */

// Debug logging
console.log('Gmail Config:', window.GMAIL_CONFIG);

// Initialize theme
if (typeof initTheme === 'function') {
    initTheme();
} else {
    console.error('initTheme function not found');
}

// Check if functions are defined
if (typeof gapiLoaded === 'undefined') {
    console.error('gapiLoaded function not found - core scripts may not have loaded');
}
if (typeof gisLoaded === 'undefined') {
    console.error('gisLoaded function not found - core scripts may not have loaded');
}

// Override onAuthSuccess to load stats
if (typeof onAuthSuccess !== 'undefined') {
    const originalOnAuthSuccess = onAuthSuccess;
    onAuthSuccess = function() {
        console.log('Auth success - loading stats');
        originalOnAuthSuccess();
        loadStats();
    };
}

async function loadStats() {
    if (typeof checkRateLimit !== 'undefined' && !checkRateLimit()) return;

    try {
        console.log('Loading stats...');
        const profile = await gapi.client.gmail.users.getProfile({
            'userId': 'me'
        });

        console.log('Profile loaded:', profile.result);
        document.getElementById('totalMessages').textContent = profile.result.messagesTotal.toLocaleString();
        document.getElementById('totalThreads').textContent = profile.result.threadsTotal.toLocaleString();
        document.getElementById('emailAddress').textContent = profile.result.emailAddress;

    } catch (e) {
        console.error('Error loading stats:', e);
        if (typeof showAlert === 'function') {
            showAlert('Error', 'Failed to load stats: ' + e.message);
        } else {
            alert('Failed to load stats: ' + e.message);
        }
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
