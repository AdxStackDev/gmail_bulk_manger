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
    <title>Gmail Manager</title>
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
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen font-sans transition-colors duration-300">

<div id="authStatus" class="fixed top-4 right-4 px-4 py-2 rounded shadow-lg hidden z-50"></div>

<div class="container mx-auto px-4 py-8 max-w-7xl">
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h1 class="text-3xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-purple-600 dark:from-blue-400 dark:to-purple-400 text-center md:text-left">
            Gmail Manager
        </h1>
        <div class="flex flex-wrap justify-center items-center gap-4">
            <a href="all_emails.php" class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm md:text-base">All Emails Stats</a>
            <button id="themeToggle" onclick="toggleTheme()" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                <!-- Sun Icon -->
                <svg id="sunIcon" class="w-6 h-6 text-yellow-500 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                <!-- Moon Icon -->
                <svg id="moonIcon" class="w-6 h-6 text-gray-600 dark:text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
            </button>
            <button id="authorize_button" onclick="handleAuthClick()" class="bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition hidden text-sm md:text-base">Authorize</button>
            <button id="signout_button" onclick="handleSignoutClick()" class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white font-bold py-2 px-4 rounded-lg shadow-md hover:shadow-lg transition hidden text-sm md:text-base">Sign Out</button>
        </div>
    </div>

    <div id="controls" class="bg-white dark:bg-gray-800 p-4 md:p-6 rounded-xl shadow-lg mb-8 hidden transition-colors duration-300">
        <div class="flex flex-col lg:flex-row gap-4 items-stretch lg:items-center justify-between">
            <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-center lg:justify-start">
                <button onclick="refreshEmails()" class="bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-semibold py-2 px-4 rounded-lg transition shadow-sm text-sm md:text-base">
                    Refresh
                </button>
                <button onclick="deleteSelectedEmails()" class="bg-red-50 dark:bg-red-900/30 hover:bg-red-100 dark:hover:bg-red-900/50 text-red-700 dark:text-red-400 font-semibold py-2 px-4 rounded-lg transition shadow-sm border border-red-200 dark:border-red-800 text-sm md:text-base">
                    Delete Selected
                </button>
                <button onclick="promptDeleteBySender()" class="bg-orange-50 dark:bg-orange-900/30 hover:bg-orange-100 dark:hover:bg-orange-900/50 text-orange-700 dark:text-orange-400 font-semibold py-2 px-4 rounded-lg transition shadow-sm border border-orange-200 dark:border-orange-800 text-sm md:text-base">
                    Delete All from Sender
                </button>
                <div class="relative inline-block text-left w-full sm:w-auto">
                    <select id="labelAction" onchange="handleLabelAction(this.value)" class="w-full sm:w-auto bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-200 py-2 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-sm md:text-base">
                        <option value="">Manage Labels...</option>
                        <option value="STARRED">Add Star</option>
                        <option value="UNREAD">Mark as Unread</option>
                        <option value="IMPORTANT">Mark as Important</option>
                        <option value="remove_STARRED">Remove Star</option>
                        <option value="remove_UNREAD">Mark as Read</option>
                    </select>
                </div>
            </div>
            <div class="flex gap-2 w-full lg:w-auto">
                <input type="text" id="searchInput" placeholder="Search emails..." class="flex-grow lg:w-64 bg-gray-50 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-white rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 shadow-sm text-sm md:text-base">
                <button onclick="searchEmails()" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition shadow-md text-sm md:text-base">
                    Search
                </button>
            </div>
        </div>
    </div>

    <div id="emailListContainer" class="bg-white dark:bg-gray-800 rounded-xl shadow-xl overflow-hidden hidden transition-colors duration-300">
        <!-- Desktop Table View -->
        <table class="min-w-full leading-normal hidden md:table">
            <thead>
                <tr>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider w-10">
                        <input type="checkbox" onchange="toggleSelectAll(this)" class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500">
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider w-1/4">
                        Sender
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider">
                        Subject
                    </th>
                    <th class="px-5 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50 text-left text-xs font-semibold text-gray-600 dark:text-gray-400 uppercase tracking-wider w-32">
                        Date
                    </th>
                </tr>
            </thead>
            <tbody id="emailsBody" class="divide-y divide-gray-200 dark:divide-gray-700">
                <!-- Emails will be inserted here -->
            </tbody>
        </table>

        <!-- Mobile Card View -->
        <div id="mobileEmailList" class="md:hidden grid grid-cols-1 divide-y divide-gray-200 dark:divide-gray-700">
            <!-- Mobile cards will be inserted here -->
        </div>

        <div class="px-5 py-5 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700 flex flex-col xs:flex-row items-center xs:justify-between">
            <span id="pagingInfo" class="text-xs xs:text-sm text-gray-600 dark:text-gray-400 mb-2 xs:mb-0">
                Showing emails
            </span>
            <div class="inline-flex rounded-md shadow-sm">
                <button onclick="getPrevPage()" id="prevBtn" class="text-sm bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 border border-gray-300 dark:border-gray-600 rounded-l-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                    Prev
                </button>
                <button onclick="getNextPage()" id="nextBtn" class="text-sm bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-medium py-2 px-4 border-t border-b border-r border-gray-300 dark:border-gray-600 rounded-r-lg disabled:opacity-50 disabled:cursor-not-allowed transition-colors" disabled>
                    Next
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Email View Modal -->
<div id="emailModal" class="modal">
    <div class="modal-content dark:bg-gray-800 dark:text-white w-full md:w-4/5 max-w-4xl m-4 md:m-auto h-[90vh] md:h-auto flex flex-col rounded-lg shadow-2xl">
        <div class="modal-header dark:border-gray-700 p-4 border-b flex justify-between items-center">
            <h2 id="modalSubject" class="text-lg md:text-xl font-semibold text-gray-800 dark:text-white truncate pr-4"></h2>
            <span class="close text-gray-500 hover:text-gray-800 dark:text-gray-400 dark:hover:text-white cursor-pointer text-2xl" onclick="closeModal()">&times;</span>
        </div>
        <div class="p-4 bg-gray-50 dark:bg-gray-900/50 border-b border-gray-200 dark:border-gray-700">
            <div class="flex flex-col sm:flex-row justify-between mb-2 gap-1">
                <span id="modalFrom" class="font-medium text-gray-700 dark:text-gray-300 text-sm md:text-base break-all"></span>
                <span id="modalDate" class="text-xs md:text-sm text-gray-500 dark:text-gray-400"></span>
            </div>
            <div id="modalLabels" class="flex flex-wrap gap-1"></div>
        </div>
        <div id="modalBody" class="modal-body prose max-w-none dark:prose-invert p-4 flex-grow overflow-y-auto"></div>
        <div class="p-4 border-t border-gray-200 dark:border-gray-700 flex justify-end">
             <button onclick="closeModal()" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded shadow w-full sm:w-auto">Okay</button>
        </div>
    </div>
</div>

<script src="js/gmail_api.js"></script>
<script type="text/javascript">
    let currentPageToken = null;
    let nextPageToken = null;
    let pageStack = []; 

    // Initialize Theme on Load
    initTheme();

    async function refreshEmails() {
        nextPageToken = null;
        pageStack = [];
        currentPageToken = null;
        await loadEmails(null);
    }

    async function loadEmails(pageToken) {
        const loadingHtml = '<tr><td colspan="4" class="text-center py-4 dark:text-gray-300">Loading...</td></tr>';
        document.getElementById('emailsBody').innerHTML = loadingHtml;
        document.getElementById('mobileEmailList').innerHTML = '<div class="text-center py-4 dark:text-gray-300">Loading...</div>';
        
        currentPageToken = pageToken;

        try {
            const response = await gapi.client.gmail.users.messages.list({
                'userId': 'me',
                'maxResults': 20,
                'pageToken': pageToken,
                'q': document.getElementById('searchInput').value
            });

            const messages = response.result.messages;
            const nextToken = response.result.nextPageToken;
            
            nextPageToken = nextToken || null;

            document.getElementById('nextBtn').disabled = !nextPageToken;
            document.getElementById('prevBtn').disabled = pageStack.length === 0;

            if (!messages || messages.length === 0) {
                const noEmailsHtml = '<tr><td colspan="4" class="text-center py-4 dark:text-gray-300">No emails found.</td></tr>';
                document.getElementById('emailsBody').innerHTML = noEmailsHtml;
                document.getElementById('mobileEmailList').innerHTML = '<div class="text-center py-4 dark:text-gray-300">No emails found.</div>';
                return;
            }

            const details = await Promise.all(messages.map(msg => getEmailMetadata(msg.id)));
            renderEmails(details);

        } catch (err) {
            console.error(err);
            const errorHtml = `<tr><td colspan="4" class="text-center py-4 text-red-500">Error: ${err.message}</td></tr>`;
            document.getElementById('emailsBody').innerHTML = errorHtml;
            document.getElementById('mobileEmailList').innerHTML = `<div class="text-center py-4 text-red-500">Error: ${err.message}</div>`;
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
        const mobileList = document.getElementById('mobileEmailList');
        tbody.innerHTML = '';
        mobileList.innerHTML = '';
        
        emails.forEach(email => {
            if (!email) return;
            
            const headers = email.payload.headers;
            const subject = headers.find(h => h.name === 'Subject')?.value || '(No Subject)';
            const from = headers.find(h => h.name === 'From')?.value || 'Unknown';
            const date = new Date(headers.find(h => h.name === 'Date')?.value).toLocaleDateString();
            const labels = email.labelIds || [];
            
            const isUnread = labels.includes('UNREAD');
            const rowClass = isUnread 
                ? 'font-bold bg-white dark:bg-gray-800 text-gray-900 dark:text-white' 
                : 'bg-gray-50 dark:bg-gray-900/50 text-gray-600 dark:text-gray-400';

            const labelBadges = labels.map(l => {
                if (l === 'UNREAD' || l === 'INBOX' || l === 'CATEGORY_UPDATES' || l === 'CATEGORY_PROMOTIONS') return ''; 
                return `<span class="label-badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300 text-xs px-2 py-0.5 rounded-full">${l}</span>`;
            }).join('');

            // Desktop Row
            const tr = document.createElement('tr');
            tr.className = `email-row border-b border-gray-200 dark:border-gray-700 ${rowClass} cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors`;
            tr.innerHTML = `
                <td class="px-5 py-5 text-sm">
                    <input type="checkbox" class="email-checkbox form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500" value="${email.id}" data-from="${from}" onclick="event.stopPropagation()">
                </td>
                <td class="px-5 py-5 text-sm" onclick="openEmail('${email.id}')">
                    <div class="truncate w-64" title="${from}">${from}</div>
                </td>
                <td class="px-5 py-5 text-sm" onclick="openEmail('${email.id}')">
                    <div class="flex items-center flex-wrap gap-1">
                        ${labelBadges}
                        <span class="truncate ml-1">${subject}</span>
                    </div>
                </td>
                <td class="px-5 py-5 text-sm whitespace-nowrap" onclick="openEmail('${email.id}')">
                    ${date}
                </td>
            `;
            tbody.appendChild(tr);

            // Mobile Card
            const card = document.createElement('div');
            card.className = `p-4 ${rowClass} cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors`;
            card.onclick = () => openEmail(email.id);
            card.innerHTML = `
                <div class="flex justify-between items-start mb-2">
                    <div class="flex items-center gap-3 overflow-hidden">
                        <input type="checkbox" class="email-checkbox form-checkbox h-5 w-5 text-blue-600 rounded border-gray-300 dark:border-gray-600 focus:ring-blue-500 flex-shrink-0" value="${email.id}" data-from="${from}" onclick="event.stopPropagation()">
                        <div class="font-semibold truncate text-sm">${from}</div>
                    </div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 whitespace-nowrap ml-2">${date}</div>
                </div>
                <div class="mb-2 text-sm truncate font-medium">${subject}</div>
                <div class="flex flex-wrap gap-1">
                    ${labelBadges}
                </div>
            `;
            mobileList.appendChild(card);
        });
    }

    async function openEmail(id) {
        const modal = document.getElementById('emailModal');
        const modalBody = document.getElementById('modalBody');
        modal.style.display = 'block';
        modalBody.innerHTML = '<div class="text-center py-10 dark:text-gray-300">Loading...</div>';

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
            
            const labels = email.labelIds || [];
            document.getElementById('modalLabels').innerHTML = labels.map(l => `<span class="label-badge bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-300">${l}</span>`).join('');

            let body = '';
            if (email.payload.parts) {
                const htmlPart = email.payload.parts.find(p => p.mimeType === 'text/html');
                const textPart = email.payload.parts.find(p => p.mimeType === 'text/plain');
                body = htmlPart ? decodeBase64(htmlPart.body.data) : (textPart ? decodeBase64(textPart.body.data) : 'No content');
            } else if (email.payload.body.data) {
                body = decodeBase64(email.payload.body.data);
            }
            
            modalBody.innerHTML = `<div class="bg-white p-4 rounded text-black">${body}</div>`;

            if (labels.includes('UNREAD')) {
                await gapi.client.gmail.users.messages.modify({
                    'userId': 'me',
                    'id': id,
                    'resource': { 'removeLabelIds': ['UNREAD'] }
                });
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
        if (ids.length === 0) return showAlert('No Selection', 'Please select emails to delete.');
        
        showConfirm('Delete Emails', `Are you sure you want to delete ${ids.length} emails?`, async () => {
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
        });
    }

    async function promptDeleteBySender() {
        const selectedCheckbox = document.querySelector('.email-checkbox:checked');
        let defaultSender = '';
        if (selectedCheckbox) {
            const fromText = selectedCheckbox.getAttribute('data-from');
            const match = fromText.match(/<(.+)>/);
            defaultSender = match ? match[1] : fromText;
        }

        // Using prompt here as it's an input. Could replace with a custom input modal later.
        const sender = prompt("Enter the sender email address to delete ALL emails from:", defaultSender);
        if (!sender) return;

        showConfirm('Delete All from Sender', `Are you sure you want to delete ALL emails from "${sender}"? This action cannot be undone.`, async () => {
            updateAuthStatus(`Searching emails from ${sender}...`, true);
            
            try {
                let allIds = [];
                let pageToken = null;
                let hasMore = true;
                
                while (hasMore) {
                    const response = await gapi.client.gmail.users.messages.list({
                        'userId': 'me',
                        'q': `from:${sender}`,
                        'pageToken': pageToken,
                        'maxResults': 500 
                    });
                    
                    const messages = response.result.messages || [];
                    allIds = allIds.concat(messages.map(m => m.id));
                    
                    pageToken = response.result.nextPageToken;
                    if (!pageToken) hasMore = false;
                    
                    updateAuthStatus(`Found ${allIds.length} emails...`, true);
                    
                    if (allIds.length > 2000) {
                        // For safety, we might want to break or ask again. 
                        // Since we are in a confirm callback, let's just stop at 2000 for this demo or assume user wants all.
                        // Let's break to avoid infinite loops in demo.
                        break; 
                    }
                }

                if (allIds.length === 0) {
                    showAlert('No Emails Found', `No emails found from ${sender}`);
                    updateAuthStatus('No emails found', false);
                    return;
                }

                // Second confirmation for the count
                showConfirm('Confirm Delete', `Found ${allIds.length} emails from ${sender}. Delete them all?`, async () => {
                    const chunkSize = 1000;
                    for (let i = 0; i < allIds.length; i += chunkSize) {
                        const chunk = allIds.slice(i, i + chunkSize);
                        updateAuthStatus(`Deleting batch ${Math.floor(i/chunkSize) + 1}...`, true);
                        
                        await gapi.client.gmail.users.messages.batchDelete({
                            'userId': 'me',
                            'ids': chunk
                        });
                    }
                    updateAuthStatus(`Successfully deleted ${allIds.length} emails.`, true);
                    refreshEmails();
                });

            } catch (e) {
                console.error(e);
                updateAuthStatus(`Error: ${e.message}`, false);
            }
        });
    }

    async function handleLabelAction(action) {
        const ids = getSelectedIds();
        if (ids.length === 0) {
            document.getElementById('labelAction').value = "";
            return showAlert('No Selection', 'Please select emails to modify.');
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
            pageStack.push(currentPageToken);
            loadEmails(nextPageToken);
        }
    }

    function getPrevPage() {
        if (pageStack.length > 0) {
            const prevToken = pageStack.pop();
            loadEmails(prevToken);
        }
    }

</script>
<script async defer src="https://apis.google.com/js/api.js" onload="gapiLoaded()"></script>
<script async defer src="https://accounts.google.com/gsi/client" onload="gisLoaded()"></script>
</body>
</html>
