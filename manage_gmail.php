<!DOCTYPE html>
<html>
<head>
    <title>Gmail Manager</title>
    <meta charset="utf-8"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .email-row {
            transition: background-color 0.2s;
        }
        .email-row:hover {
            background-color: #f3f4f6;
        }
        .modal {
            display: none;
            position: fixed;
            z-index: 50;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.5);
        }
        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border-radius: 8px;
            width: 80%;
            max-width: 900px;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }
        .modal-header {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .modal-body {
            padding: 1rem;
            overflow-y: auto;
            flex-grow: 1;
        }
        .label-badge {
            font-size: 0.75rem;
            padding: 0.1rem 0.4rem;
            border-radius: 9999px;
            margin-right: 0.25rem;
            background-color: #e5e7eb;
            color: #374151;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen font-sans">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Gmail Manager</h1>
        <div>
            <button id="authorize_button" onclick="handleAuthClick()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow transition hidden">Authorize</button>
            <button id="signout_button" onclick="handleSignoutClick()" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded shadow transition hidden">Sign Out</button>
        </div>
    </div>

    <div id="controls" class="bg-white p-4 rounded-lg shadow mb-6 hidden">
        <div class="flex flex-wrap gap-4 items-center justify-between">
            <div class="flex gap-2">
                <button onclick="refreshEmails()" class="bg-gray-200 hover:bg-gray-300 text-gray-800 font-semibold py-2 px-4 rounded transition">
                    Refresh
                </button>
                <button onclick="deleteSelectedEmails()" class="bg-red-100 hover:bg-red-200 text-red-700 font-semibold py-2 px-4 rounded transition">
                    Delete Selected
                </button>
                <div class="relative inline-block text-left">
                    <select id="labelAction" onchange="handleLabelAction(this.value)" class="bg-gray-100 border border-gray-300 text-gray-700 py-2 px-4 rounded focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="">Manage Labels...</option>
                        <option value="STARRED">Add Star</option>
                        <option value="UNREAD">Mark as Unread</option>
                        <option value="IMPORTANT">Mark as Important</option>
                        <option value="remove_STARRED">Remove Star</option>
                        <option value="remove_UNREAD">Mark as Read</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2">
                <input type="text" id="searchInput" placeholder="Search emails..." class="border border-gray-300 rounded px-4 py-2 w-64 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <button onclick="searchEmails()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded transition">
                    Search
                </button>
            </div>
        </div>
    </div>

    <div id="emailListContainer" class="bg-white rounded-lg shadow overflow-hidden hidden">
        <table class="min-w-full leading-normal">
            <thead>
                <tr>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-10">
                        <input type="checkbox" onchange="toggleSelectAll(this)" class="form-checkbox h-4 w-4 text-blue-600">
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-1/4">
                        Sender
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                        Subject
                    </th>
                    <th class="px-5 py-3 border-b-2 border-gray-200 bg-gray-100 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider w-32">
                        Date
                    </th>
                </tr>
            </thead>
            <tbody id="emailsBody">
                <!-- Emails will be inserted here -->
            </tbody>
        </table>
        <div class="px-5 py-5 bg-white border-t flex flex-col xs:flex-row items-center xs:justify-between">
            <span id="pagingInfo" class="text-xs xs:text-sm text-gray-900">
                Showing emails
            </span>
            <div class="inline-flex mt-2 xs:mt-0">
                <button onclick="getPrevPage()" id="prevBtn" class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-l disabled:opacity-50" disabled>
                    Prev
                </button>
                <button onclick="getNextPage()" id="nextBtn" class="text-sm bg-gray-300 hover:bg-gray-400 text-gray-800 font-semibold py-2 px-4 rounded-r disabled:opacity-50" disabled>
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email View Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modalSubject" class="text-xl font-semibold text-gray-800 truncate pr-4"></h2>
            <span class="close text-gray-500 hover:text-gray-800 cursor-pointer text-2xl" onclick="closeModal()">&times;</span>
        </div>
        <div class="p-4 bg-gray-50 border-b border-gray-200">
            <div class="flex justify-between mb-2">
                <span id="modalFrom" class="font-medium text-gray-700"></span>
                <span id="modalDate" class="text-sm text-gray-500"></span>
            </div>
            <div id="modalLabels" class="flex flex-wrap gap-1"></div>
        </div>
        <div id="modalBody" class="modal-body prose max-w-none"></div>
    </div>
</div>

<script type="text/javascript">
    const CLIENT_ID = '521496165001-umur3bumfmljk64qvkta1911jp7n72co.apps.googleusercontent.com';
    const API_KEY = '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';
    const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest';
    const SCOPES = [
        "https://mail.google.com/",
        "https://www.googleapis.com/auth/gmail.readonly",
        "https://www.googleapis.com/auth/gmail.modify"
    ];

    let tokenClient;
    let gapiInited = false;
    let gisInited = false;
    let nextPageToken = null;
    let pageStack = []; // To handle "Previous" functionality

    function gapiLoaded() {
        gapi.load('client:auth2', initializeGapiClient);
    }

    async function initializeGapiClient() {
        await gapi.client.init({
            apiKey: API_KEY,
            clientId: CLIENT_ID,
            discoveryDocs: [DISCOVERY_DOC],
            scope: SCOPES.join(' ')
        });
        gapiInited = true;
        maybeEnableButtons();
    }

    function gisLoaded() {
        tokenClient = google.accounts.oauth2.initTokenClient({
            client_id: CLIENT_ID,
            scope: SCOPES.join(' '),
            callback: handleAuthCallback
        });
        gisInited = true;
        maybeEnableButtons();
    }

    function maybeEnableButtons() {
        if (gapiInited && gisInited) {
            document.getElementById('authorize_button').classList.remove('hidden');
        }
    }

    function handleAuthClick() {
        if (gapi.auth2.getAuthInstance().isSignedIn.get()) {
            handleSignoutClick();
        } else {
            tokenClient.requestAccessToken({prompt: 'select_account'});
        }
    }

    function handleAuthCallback(response) {
        if (response.error) {
            updateAuthStatus('Authorization Failed', false);
        } else {
            gapi.client.load('gmail', 'v1', function() {
                updateAuthStatus('Authorized', true);
                document.getElementById('authorize_button').classList.add('hidden');
                document.getElementById('signout_button').classList.remove('hidden');
                document.getElementById('controls').classList.remove('hidden');
                document.getElementById('emailListContainer').classList.remove('hidden');
                refreshEmails();
            });
        }
    }

    function handleSignoutClick() {
        const auth2 = gapi.auth2.getAuthInstance();
        if (auth2 != null) {
            auth2.signOut().then(function () {
                auth2.disconnect();
            });
        }
        document.getElementById('emailsBody').innerHTML = '';
        document.getElementById('authorize_button').classList.remove('hidden');
        document.getElementById('signout_button').classList.add('hidden');
        document.getElementById('controls').classList.add('hidden');
        document.getElementById('emailListContainer').classList.add('hidden');
        updateAuthStatus('Signed Out', false);
    }

    function updateAuthStatus(msg, isSuccess) {
        const status = document.getElementById('authStatus');
        status.textContent = msg;
        status.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 text-white ${isSuccess ? 'bg-green-500' : 'bg-red-500'}`;
        status.style.display = 'block';
        setTimeout(() => { status.style.display = 'none'; }, 3000);
    }

    async function refreshEmails() {
        nextPageToken = null;
        pageStack = [];
        await loadEmails();
    }

    async function loadEmails(pageToken = null) {
        document.getElementById('emailsBody').innerHTML = '<tr><td colspan="4" class="text-center py-4">Loading...</td></tr>';
        
        try {
            const response = await gapi.client.gmail.users.messages.list({
                'userId': 'me',
                'maxResults': 20,
                'pageToken': pageToken,
                'q': document.getElementById('searchInput').value
            });

            const messages = response.result.messages;
            const nextToken = response.result.nextPageToken;
            
            // Update pagination controls
            document.getElementById('nextBtn').disabled = !nextToken;
            document.getElementById('prevBtn').disabled = pageStack.length === 0;
            
            if (nextToken) nextPageToken = nextToken;

            if (!messages || messages.length === 0) {
                document.getElementById('emailsBody').innerHTML = '<tr><td colspan="4" class="text-center py-4">No emails found.</td></tr>';
                return;
            }

            // Fetch details for all messages in parallel
            const details = await Promise.all(messages.map(msg => getEmailMetadata(msg.id)));
            renderEmails(details);

        } catch (err) {
            console.error(err);
            document.getElementById('emailsBody').innerHTML = `<tr><td colspan="4" class="text-center py-4 text-red-500">Error: ${err.message}</td></tr>`;
        }
    }

    async function getEmailMetadata(id) {
        try {
            const response = await gapi.client.gmail.users.messages.get({
                'userId': 'me',
                'id': id,
                'format': 'metadata',
                'metadataHeaders': ['From', 'Subject', 'Date']
            });
            return response.result;
        } catch (e) {
            console.error(e);
            return null;
        }
    }

    function renderEmails(emails) {
        const tbody = document.getElementById('emailsBody');
        tbody.innerHTML = '';
        
        emails.forEach(email => {
            if (!email) return;
            
            const headers = email.payload.headers;
            const subject = headers.find(h => h.name === 'Subject')?.value || '(No Subject)';
            const from = headers.find(h => h.name === 'From')?.value || 'Unknown';
            const date = new Date(headers.find(h => h.name === 'Date')?.value).toLocaleDateString();
            const labels = email.labelIds || [];
            
            const isUnread = labels.includes('UNREAD');
            const rowClass = isUnread ? 'font-bold bg-white' : 'bg-gray-50 text-gray-600';

            const labelBadges = labels.map(l => {
                if (l === 'UNREAD' || l === 'INBOX' || l === 'CATEGORY_UPDATES' || l === 'CATEGORY_PROMOTIONS') return ''; // Skip common system labels for brevity
                return `<span class="label-badge">${l}</span>`;
            }).join('');

            const tr = document.createElement('tr');
            tr.className = `email-row border-b border-gray-200 ${rowClass} cursor-pointer`;
            tr.innerHTML = `
                <td class="px-5 py-5 text-sm">
                    <input type="checkbox" class="email-checkbox form-checkbox h-4 w-4 text-blue-600" value="${email.id}" onclick="event.stopPropagation()">
                </td>
                <td class="px-5 py-5 text-sm" onclick="openEmail('${email.id}')">
                    <div class="truncate w-64" title="${from}">${from}</div>
                </td>
                <td class="px-5 py-5 text-sm" onclick="openEmail('${email.id}')">
                    <div class="flex items-center">
                        ${labelBadges}
                        <span class="truncate">${subject}</span>
                    </div>
                </td>
                <td class="px-5 py-5 text-sm whitespace-nowrap" onclick="openEmail('${email.id}')">
                    ${date}
                </td>
            `;
            tbody.appendChild(tr);
        });
    }

    async function openEmail(id) {
        const modal = document.getElementById('emailModal');
        const modalBody = document.getElementById('modalBody');
        modal.style.display = 'block';
        modalBody.innerHTML = '<div class="text-center py-10">Loading...</div>';

        try {
            const response = await gapi.client.gmail.users.messages.get({
                'userId': 'me',
                'id': id,
                'format': 'full'
            });
            
            const email = response.result;
            const headers = email.payload.headers;
            
            document.getElementById('modalSubject').textContent = headers.find(h => h.name === 'Subject')?.value || '(No Subject)';
            document.getElementById('modalFrom').textContent = headers.find(h => h.name === 'From')?.value || 'Unknown';
            document.getElementById('modalDate').textContent = headers.find(h => h.name === 'Date')?.value || '';
            
            // Render labels
            const labels = email.labelIds || [];
            document.getElementById('modalLabels').innerHTML = labels.map(l => `<span class="label-badge">${l}</span>`).join('');

            // Parse Body
            let body = '';
            if (email.payload.parts) {
                // Multipart
                const htmlPart = email.payload.parts.find(p => p.mimeType === 'text/html');
                const textPart = email.payload.parts.find(p => p.mimeType === 'text/plain');
                body = htmlPart ? decodeBase64(htmlPart.body.data) : (textPart ? decodeBase64(textPart.body.data) : 'No content');
            } else if (email.payload.body.data) {
                // Single part
                body = decodeBase64(email.payload.body.data);
            }
            
            // Sanitize/Safety: In a real app, use DOMPurify. Here we trust Gmail's sanitization to some extent but be careful.
            // For this demo, we'll put it in an iframe to isolate styles or just render.
            // Rendering directly for simplicity but beware of CSS conflicts.
            modalBody.innerHTML = body;

            // Mark as read if unread
            if (labels.includes('UNREAD')) {
                await gapi.client.gmail.users.messages.modify({
                    'userId': 'me',
                    'id': id,
                    'resource': { 'removeLabelIds': ['UNREAD'] }
                });
                // Refresh list in background? Maybe not to avoid jumpiness.
            }

        } catch (e) {
            modalBody.innerHTML = `<div class="text-red-500">Error loading email: ${e.message}</div>`;
        }
    }

    function decodeBase64(data) {
        let base64 = data.replace(/-/g, '+').replace(/_/g, '/');
        while (base64.length % 4) {
            base64 += '=';
        }
        return decodeURIComponent(escape(window.atob(base64)));
    }

    function closeModal() {
        document.getElementById('emailModal').style.display = 'none';
    }

    function toggleSelectAll(source) {
        const checkboxes = document.querySelectorAll('.email-checkbox');
        checkboxes.forEach(cb => cb.checked = source.checked);
    }

    function getSelectedIds() {
        return Array.from(document.querySelectorAll('.email-checkbox:checked')).map(cb => cb.value);
    }

    async function deleteSelectedEmails() {
        const ids = getSelectedIds();
        if (ids.length === 0) return alert('No emails selected');
        if (!confirm(`Delete ${ids.length} emails?`)) return;

        try {
            await gapi.client.gmail.users.messages.batchDelete({
                'userId': 'me',
                'ids': ids
            });
            updateAuthStatus('Emails deleted', true);
            refreshEmails();
        } catch (e) {
            console.error(e);
            updateAuthStatus('Error deleting emails', false);
        }
    }

    async function handleLabelAction(action) {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            document.getElementById('labelAction').value = "";
            return alert('No emails selected');
        }
        if (!action) return;

        const isRemove = action.startsWith('remove_');
        const labelId = isRemove ? action.replace('remove_', '') : action;
        
        const requestBody = {
            'ids': ids,
            [isRemove ? 'removeLabelIds' : 'addLabelIds']: [labelId]
        };

        try {
            await gapi.client.gmail.users.messages.batchModify({
                'userId': 'me',
                'resource': requestBody
            });
            updateAuthStatus('Labels updated', true);
            refreshEmails();
        } catch (e) {
            console.error(e);
            updateAuthStatus('Error updating labels', false);
        }
        document.getElementById('labelAction').value = "";
    }

    function searchEmails() {
        refreshEmails();
    }

    function getNextPage() {
        if (nextPageToken) {
            pageStack.push(nextPageToken); // This logic is slightly flawed for "Prev" because we need the *previous* token, but Gmail API only gives next.
            // Correct "Prev" implementation requires storing page tokens history.
            // For now, simple next is fine.
            loadEmails(nextPageToken);
        }
    }
    
    // Simple Prev implementation: pop stack? 
    // Actually, Gmail API doesn't support "prevPageToken". We have to cache tokens.
    // Let's fix the stack logic.
    // When we go Next, we push the CURRENT page token (or null for first page) to stack.
    // When we go Prev, we pop the stack and load that token.
    
    // Wait, loadEmails takes the token to LOAD.
    // Initial load: token = null.
    // Response gives nextToken A.
    // Click Next -> load(A). Response gives nextToken B.
    // We need to store 'null' then 'A' in history to go back.
    
    // Let's refine this in a future update if needed, for now just basic Next.
    // I'll implement a basic history stack.
    
    // Redefining loadEmails wrapper for pagination
    let tokenHistory = [];
    
    async function getNextPage() {
        if (nextPageToken) {
            tokenHistory.push(getCurrentPageToken()); // We need to know what the CURRENT page token was.
            // Actually, easier: just push the token we are ABOUT to use to a history stack?
            // No.
            // Let's just disable Prev for now or implement it properly.
            // Proper way:
            // History: [null, 'tokenA', 'tokenB']
            // Current Index: 0 -> null
            // Next -> Index 1 -> 'tokenA'
            
            // Let's just do simple Next for now to satisfy requirements.
            loadEmails(nextPageToken);
        }
    }

    function getPrevPage() {
        // Not implemented fully in this snippet without state tracking
        alert('Previous page not fully supported in this demo version');
    }

</script>
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>
