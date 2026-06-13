/**
 * UI: transient status toast.
 *
 * Renders short-lived feedback messages in the #authStatus element.
 * Loaded as a classic script; functions are global by design so the rest of
 * the app (and inline handlers) can call them.
 */

function updateAuthStatus(msg, isSuccess) {
    const status = document.getElementById('authStatus');
    if (!status) return;
    status.textContent = msg;
    status.className = `fixed top-4 right-4 px-4 py-2 rounded shadow-lg z-50 text-white ${isSuccess ? 'bg-green-500' : 'bg-red-500'}`;
    status.style.display = 'block';
    setTimeout(() => { status.style.display = 'none'; }, 3000);
}
