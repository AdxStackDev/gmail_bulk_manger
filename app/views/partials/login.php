<?php
/**
 * Login screen & Homepage landing section.
 *
 * Serves as the public homepage outlining the purpose of Adx Mail Manager
 * to fulfill Google OAuth verification requirements.
 */
?>
<div id="loginScreen" class="py-8 md:py-12 max-w-5xl mx-auto space-y-12">
    <!-- Hero Section -->
    <div class="text-center space-y-4">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-50 dark:bg-gray-800 text-blue-600 dark:text-blue-400 mb-2 shadow-sm">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
            </svg>
        </div>
        <h1 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white tracking-tight">
            Adx Mail Manager
        </h1>
        <p class="text-lg md:text-xl text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
            A fast, secure web tool to organize, search, and bulk clean up your Gmail inbox by sender, label, and thread count.
        </p>
        
        <!-- Sign in Action Card -->
        <div class="pt-4 flex justify-center">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8 max-w-md w-full border border-gray-100 dark:border-gray-700">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">Sign in to Access Your Inbox</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Connect securely using official Google OAuth 2.0. No password or email content is ever saved on our servers.
                </p>
                <button onclick="handleAuthClick()" class="w-full flex items-center justify-center gap-3 bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 font-semibold py-3 px-4 rounded-xl shadow-md hover:shadow-lg transition">
                    <svg class="w-5 h-5" viewBox="0 0 48 48" aria-hidden="true">
                        <path fill="#EA4335" d="M24 9.5c3.54 0 6.71 1.22 9.21 3.6l6.85-6.85C35.9 2.38 30.47 0 24 0 14.62 0 6.51 5.38 2.56 13.22l7.98 6.19C12.43 13.72 17.74 9.5 24 9.5z"/>
                        <path fill="#4285F4" d="M46.98 24.55c0-1.57-.15-3.09-.38-4.55H24v9.02h12.94c-.58 2.96-2.26 5.48-4.78 7.18l7.73 6c4.51-4.18 7.09-10.36 7.09-17.65z"/>
                        <path fill="#FBBC05" d="M10.53 28.59c-.48-1.45-.76-2.99-.76-4.59s.27-3.14.76-4.59l-7.98-6.19C.92 16.46 0 20.12 0 24c0 3.88.92 7.54 2.56 10.78l7.97-6.19z"/>
                        <path fill="#34A853" d="M24 48c6.48 0 11.93-2.13 15.89-5.81l-7.73-6c-2.15 1.45-4.92 2.3-8.16 2.3-6.26 0-11.57-4.22-13.47-9.91l-7.98 6.19C6.51 42.62 14.62 48 24 48z"/>
                        <path fill="none" d="M0 0h48v48H0z"/>
                    </svg>
                    Sign in with Google
                </button>
            </div>
        </div>
    </div>

    <!-- Application Purpose & Features Overview (Required by Google OAuth Verification) -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-lg p-8 border border-gray-100 dark:border-gray-700 space-y-6">
        <div class="border-b border-gray-200 dark:border-gray-700 pb-4">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">What is Adx Mail Manager?</h2>
            <p class="text-gray-600 dark:text-gray-300 mt-1">
                Adx Mail Manager is an email management tool designed to help users search, organize, and batch clean up clutter in their Gmail accounts efficiently.
            </p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="space-y-2 p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-purple-100 dark:bg-purple-900/50 text-purple-600 dark:text-purple-400 flex items-center justify-center font-bold text-lg">📧</div>
                <h3 class="font-bold text-gray-900 dark:text-white">Batch Delete by Sender</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Scan your inbox for unique sender addresses and delete thousands of unwanted promotional newsletters in a single click.
                </p>
            </div>

            <div class="space-y-2 p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-blue-100 dark:bg-blue-900/50 text-blue-600 dark:text-blue-400 flex items-center justify-center font-bold text-lg">🔍</div>
                <h3 class="font-bold text-gray-900 dark:text-white">Advanced Search & Read</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Filter messages using Gmail search operators, preview full message content in a clean modal, and update read/unread states.
                </p>
            </div>

            <div class="space-y-2 p-4 bg-gray-50 dark:bg-gray-700/40 rounded-xl">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 dark:bg-emerald-900/50 text-emerald-600 dark:text-emerald-400 flex items-center justify-center font-bold text-lg">🛡️</div>
                <h3 class="font-bold text-gray-900 dark:text-white">Privacy First Architecture</h3>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Your OAuth credentials and access tokens are kept strictly in your local browser session storage. We store zero email data on external servers.
                </p>
            </div>
        </div>

        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-xl p-4 text-xs text-blue-900 dark:text-blue-200 flex items-center gap-3">
            <svg class="w-6 h-6 flex-shrink-0 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>
                <strong>Compliance Disclosure:</strong> Adx Mail Manager uses official Google Gmail APIs under the Google API Services User Data Policy. For more details, review our <a href="privacy.php" class="underline font-semibold">Privacy Policy</a> and <a href="terms.php" class="underline font-semibold">Terms of Service</a>.
            </span>
        </div>
    </div>
</div>
