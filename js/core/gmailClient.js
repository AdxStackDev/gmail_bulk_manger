/**
 * Core: Gmail API client bootstrap + rate limiting.
 *
 * Owns the gapi client initialisation, the Google Identity Services token
 * client setup, and a simple client-side rate limiter. Loaded as a classic
 * script; symbols are global so the auth and page layers can use them.
 */

const DISCOVERY_DOC = 'https://www.googleapis.com/discovery/v1/apis/gmail/v1/rest';
const SCOPES = [
    "https://mail.google.com/",
    "https://www.googleapis.com/auth/gmail.readonly",
    "https://www.googleapis.com/auth/gmail.modify"
];

let tokenClient;
let gapiInited = false;
let gisInited = false;

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

// --- gapi / GIS loaders ---

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
