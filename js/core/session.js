/**
 * Core: client-side OAuth token persistence.
 *
 * Stores the access token in sessionStorage (preferred) with a backward
 * compatible migration from localStorage. Loaded as a classic script; the
 * functions and constants are global by design.
 *
 * NOTE: For a public multi-user deployment, prefer an authorization-code flow
 * with server-side token storage instead of keeping tokens in the browser.
 */

const TOKEN_STORAGE_KEY = 'gmail_access_token';
const TOKEN_EXPIRY_KEY = 'gmail_token_expiry';

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
