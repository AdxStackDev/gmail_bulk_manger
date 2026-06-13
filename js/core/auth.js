/**
 * Core: authentication flow + auth-driven UI state.
 *
 * Handles sign-in/out clicks, the token callback, and toggling page sections
 * based on auth state. onAuthSuccess/onSignOut are intentionally global and
 * may be wrapped by individual pages (e.g. to load stats after sign-in).
 */

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

// Callbacks to be (optionally) overridden by the page
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
