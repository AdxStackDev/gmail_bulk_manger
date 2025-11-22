const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest';
const SCOPES = [
    "https://mail.google.com/",
    "https://www.googleapis.com/auth/gmail.readonly",
    "https://www.googleapis.com/auth/gmail.modify"
];

let tokenClient;
let gapiInited = false;
let gisInited = false;

// --- Session Management ---

function saveSession(token) {
    if (token) {
        localStorage.setItem('gmail_access_token', JSON.stringify(token));
        localStorage.setItem('gmail_token_expiry', Date.now() + (token.expires_in * 1000));
    }
}

function loadSession() {
    const tokenStr = localStorage.getItem('gmail_access_token');
    const expiry = localStorage.getItem('gmail_token_expiry');

    if (tokenStr && expiry && Date.now() < parseInt(expiry)) {
        return JSON.parse(tokenStr);
    }
    return null;
}

function clearSession() {
    localStorage.removeItem('gmail_access_token');
    localStorage.removeItem('gmail_token_expiry');
}

// --- UI Utilities ---

function toggleTheme() {
    const html = document.documentElement;
    const sun = document.getElementById('sunIcon');
    const moon = document.getElementById('moonIcon');

    if (html.classList.contains('dark')) {
        html.classList.remove('dark');
        localStorage.setItem('theme', 'light');
        if (sun) sun.classList.add('hidden');
        if (moon) moon.classList.remove('hidden');
    } else {
        html.classList.add('dark');
        localStorage.setItem('theme', 'dark');
        if (sun) sun.classList.remove('hidden');
        if (moon) moon.classList.add('hidden');
    }
}

function initTheme() {
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        document.documentElement.classList.add('dark');
        const sun = document.getElementById('sunIcon');
        const moon = document.getElementById('moonIcon');
        if (sun) sun.classList.remove('hidden');
        if (moon) moon.classList.add('hidden');
    }
}

// Custom Modal
function showModal(title, message, onConfirm, isConfirm = false) {
    // Check if modal container exists, if not create it
    let modal = document.getElementById('customModal');
    if (!modal) {
        modal = document.createElement('div');
        modal.id = 'customModal';
        modal.className = 'fixed inset-0 z-[60] flex items-center justify-center bg-black bg-opacity-50 hidden';
        modal.innerHTML = `
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-xl p-6 w-96 transform transition-all scale-100">
                <h3 id="customModalTitle" class="text-lg font-bold text-gray-900 dark:text-white mb-2"></h3>
                <p id="customModalMessage" class="text-gray-600 dark:text-gray-300 mb-6"></p>
                <div class="flex justify-end gap-3">
                    <button id="customModalCancel" class="px-4 py-2 rounded text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition">Cancel</button>
                    <button id="customModalConfirm" class="px-4 py-2 rounded bg-blue-600 text-white hover:bg-blue-700 transition">Okay</button>
                </div>
            </div>
        `;
        document.body.appendChild(modal);
    }

    const titleEl = document.getElementById('customModalTitle');
    const msgEl = document.getElementById('customModalMessage');
    const confirmBtn = document.getElementById('customModalConfirm');
    const cancelBtn = document.getElementById('customModalCancel');

    titleEl.textContent = title;
    msgEl.textContent = message;

    if (!isConfirm) {
        cancelBtn.classList.add('hidden');
        confirmBtn.textContent = 'Okay';
    } else {
        cancelBtn.classList.remove('hidden');
        confirmBtn.textContent = 'Confirm';
    }

    modal.classList.remove('hidden');

    // Clean up old listeners
    const newConfirm = confirmBtn.cloneNode(true);
    const newCancel = cancelBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirm, confirmBtn);
    cancelBtn.parentNode.replaceChild(newCancel, cancelBtn);

    newConfirm.onclick = () => {
        modal.classList.add('hidden');
        if (onConfirm) onConfirm();
    };

    newCancel.onclick = () => {
        modal.classList.add('hidden');
    };
}

function showAlert(title, message) {
    showModal(title, message, null, false);
}

function showConfirm(title, message, onConfirm) {
    showModal(title, message, onConfirm, true);
}


// --- Auth Flow ---

function gapiLoaded() {
    gapi.load('client', initializeGapiClient);
}

async function initializeGapiClient() {
    await gapi.client.init({
        discoveryDocs: [DISCOVERY_DOC],
    });
    gapiInited = true;
    maybeEnableButtons();
}

function gisLoaded() {
    if (!window.GMAIL_CONFIG || !window.GMAIL_CONFIG.clientId) {
        console.error("Gmail Config missing or invalid");
        return;
    }
    tokenClient = google.accounts.oauth2.initTokenClient({
        client_id: window.GMAIL_CONFIG.clientId,
        scope: SCOPES.join(' '),
        callback: handleAuthCallback
    });
    gisInited = true;
    maybeEnableButtons();
}

function maybeEnableButtons() {
    if (gapiInited && gisInited) {
        // Check for existing session
        const storedToken = loadSession();
        if (storedToken) {
            gapi.client.setToken(storedToken);
            onAuthSuccess();
        } else {
            const authBtn = document.getElementById('authorize_button');
            if (authBtn) authBtn.classList.remove('hidden');
        }
    }
}

function handleAuthClick() {
    tokenClient.requestAccessToken({ prompt: 'consent' });
}

function handleAuthCallback(response) {
    if (response.error) {
        updateAuthStatus('Authorization Failed', false);
    } else {
        saveSession(response); // Save session
        gapi.client.setToken(response);
        onAuthSuccess();
    }
}

function handleSignoutClick() {
    const token = gapi.client.getToken();
    if (token !== null) {
        google.accounts.oauth2.revoke(token.access_token, () => {
            gapi.client.setToken('');
            clearSession(); // Clear session
            onSignOut();
        });
    } else {
        clearSession();
        onSignOut();
    }
}

// Callbacks to be defined by the page
function onAuthSuccess() {
    updateAuthStatus('Authorized', true);
    const authBtn = document.getElementById('authorize_button');
    const signoutBtn = document.getElementById('signout_button');
    const controls = document.getElementById('controls');
    const listContainer = document.getElementById('emailListContainer');

    if (authBtn) authBtn.classList.add('hidden');
    if (signoutBtn) signoutBtn.classList.remove('hidden');
    if (controls) controls.classList.remove('hidden');
    if (listContainer) listContainer.classList.remove('hidden');

    if (typeof refreshEmails === 'function') {
        refreshEmails();
    }
}

function onSignOut() {
    updateAuthStatus('Signed Out', false);
    const authBtn = document.getElementById('authorize_button');
    const signoutBtn = document.getElementById('signout_button');
    const controls = document.getElementById('controls');
    const listContainer = document.getElementById('emailListContainer');
    const tbody = document.getElementById('emailsBody');

    if (authBtn) authBtn.classList.remove('hidden');
    if (signoutBtn) signoutBtn.classList.add('hidden');
    if (controls) controls.classList.add('hidden');
    if (listContainer) listContainer.classList.add('hidden');
    if (tbody) tbody.innerHTML = '';
}

function updateAuthStatus(msg, isSuccess) {
    const status = document.getElementById('authStatus');
    if (!status) return;
    status.textContent = msg;
    status.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 text-white ${isSuccess ? 'bg-green-500' : 'bg-red-500'}`;
    status.style.display = 'block';
    setTimeout(() => { status.style.display = 'none'; }, 3000);
}
