/**
 * Page: Gmail Manager (manage_gmail.php).
 *
 * Email list rendering, pagination, viewing, deletion and label actions.
 * Depends on the core (gmailClient/auth/session) and ui (modal/toast/theme)
 * layers being loaded first. Loaded as a classic script.
 */

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
