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

// Token storage with encryption flag
const TOKEN_STORAGE_KEY = 'gmail_access_token';
const TOKEN_EXPIRY_KEY = 'gmail_token_expiry';

// Rate limiting configuration
const RATE_LIMIT = {
    maxRequests: 100,
    windowMs: 60000, // 1 minute
    requests: [],
};

/**
 * Check rate limit before making API calls
 * @returns {boolean} True if within rate limit
 */
function checkRateLimit() {
    const now = Date.now();
    // Remove old requests outside the window
    RATE_LIMIT.requests = RATE_LIMIT.requests.filter(time => now - time < RATE_LIMIT.windowMs);
    
    if (RATE_LIMIT.requests.length >= RATE_LIMIT.maxRequests) {
        showAlert('Rate Limit', 'Too many requests. Please wait a moment and try again.');
        return false;
    }
    
    RATE_LIMIT.requests.push(now);
    return true;
}

function saveSession(token) {
    if (token) {
        try {
            // Store token with timestamp
            const tokenData = {
                token: token,
                timestamp: Date.now(),
                expiresAt: Date.now() + (token.expires_in * 1000)
            };
            
            sessionStorage.setItem(TOKEN_STORAGE_KEY, JSON.stringify(tokenData));
            sessionStorage.setItem(TOKEN_EXPIRY_KEY, tokenData.expiresAt.toString());
            
            // Clear localStorage (migrate old tokens)
            localStorage.removeItem(TOKEN_STORAGE_KEY);
            localStorage.removeItem(TOKEN_EXPIRY_KEY);
        } catch (e) {
            console.error('Failed to save session:', e);
        }
    }
}

function loadSession() {
    try {
        // Try sessionStorage first (more secure)
        let tokenStr = sessionStorage.getItem(TOKEN_STORAGE_KEY);
        let expiry = sessionStorage.getItem(TOKEN_EXPIRY_KEY);
        
        // Fallback to localStorage for backward compatibility
        if (!tokenStr) {
            tokenStr = localStorage.getItem(TOKEN_STORAGE_KEY);
            expiry = localStorage.getItem(TOKEN_EXPIRY_KEY);
            
            // Migrate to sessionStorage
            if (tokenStr && expiry) {
                const tokenData = JSON.parse(tokenStr);
                sessionStorage.setItem(TOKEN_STORAGE_KEY, tokenStr);
                sessionStorage.setItem(TOKEN_EXPIRY_KEY, expiry);
                localStorage.removeItem(TOKEN_STORAGE_KEY);
                localStorage.removeItem(TOKEN_EXPIRY_KEY);
            }
        }

        if (tokenStr && expiry && Date.now() < parseInt(expiry)) {
            const tokenData = JSON.parse(tokenStr);
            return tokenData.token || tokenData;
        }
    } catch (e) {
        console.error('Failed to load session:', e);
    }
    return null;
}

function clearSession() {
    try {
        sessionStorage.removeItem(TOKEN_STORAGE_KEY);
        sessionStorage.removeItem(TOKEN_EXPIRY_KEY);
        localStorage.removeItem(TOKEN_STORAGE_KEY);
        localStorage.removeItem(TOKEN_EXPIRY_KEY);
    } catch (e) {
        console.error('Failed to clear session:', e);
    }
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
