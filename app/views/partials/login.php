<?php
/**
 * Login screen partial.
 *
 * Shown when the user is signed out. The "Sign in with Google" button triggers
 * the same OAuth flow as the header Authorize button (handleAuthClick()).
 * Visibility is toggled by onAuthSuccess()/onSignOut() in js/core/auth.js.
 */
?>
<div id="loginScreen" class="flex items-center justify-center py-12 md:py-20">
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl max-w-md w-full p-8 text-center transition-colors duration-300">
        <div class="mx-auto w-16 h-16 flex items-center justify-center rounded-full bg-blue-50 dark:bg-gray-700 mb-6">
            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>

        <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Welcome to Gmail Manager</h2>
        <p class="text-gray-600 dark:text-gray-400 mb-8">
            Sign in with your Google account to view, search, and clean up your inbox. Your access token stays in your browser.
        </p>

        <button onclick="handleAuthClick()" class="w-full flex items-center justify-center gap-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold py-3 px-4 rounded-lg shadow-sm transition">
            <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                <path fill="none" d="M0 0h48v48H0z"/>
            </svg>
            Sign in with Google
        </button>

        <p class="text-xs text-gray-400 dark:text-gray-500 mt-6">
            By continuing, you grant this app permission to read and manage your Gmail messages. You can revoke access at any time from your Google account.
        </p>
    </div>
</div>
