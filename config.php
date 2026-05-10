<?php
/**
 * Gmail Manager Configuration
 * Security-enhanced configuration file
 */

// Start session for CSRF protection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Generate CSRF token if not exists
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

/**
 * Load Google OAuth credentials
 * @return array Credentials array with client_id
 */
function loadCredentials() {
    $creds_file = __DIR__ . '/credentials.json';
    
    if (!file_exists($creds_file)) {
        die('Error: credentials.json not found. Please copy credentials.json.example to credentials.json and add your Google OAuth credentials.');
    }
    
    $creds = json_decode(file_get_contents($creds_file), true);
    
    if (!$creds) {
        die('Error: Invalid credentials.json format');
    }
    
    $client_id = $creds['web']['client_id'] ?? $creds['installed']['client_id'] ?? '';
    
    if (empty($client_id)) {
        die('Error: client_id not found in credentials.json');
    }
    
    return [
        'client_id' => $client_id,
        'csrf_token' => $_SESSION['csrf_token']
    ];
}

/**
 * Verify CSRF token
 * @param string $token Token to verify
 * @return bool True if valid
 */
function verifyCsrfToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Set security headers
 */
function setSecurityHeaders() {
    // Prevent clickjacking
    header("X-Frame-Options: SAMEORIGIN");
    
    // XSS protection
    header("X-XSS-Protection: 1; mode=block");
    
    // Prevent MIME sniffing
    header("X-Content-Type-Options: nosniff");
    
    // Referrer policy
    header("Referrer-Policy: strict-origin-when-cross-origin");
    
    // Content Security Policy - Relaxed for Google APIs
    // Note: 'unsafe-inline' and 'unsafe-eval' are needed for Google APIs to work
    header("Content-Security-Policy: " . 
        "default-src 'self'; " .
        "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.tailwindcss.com https://apis.google.com https://accounts.google.com https://*.googleapis.com; " .
        "style-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://accounts.google.com; " .
        "font-src 'self' data: https://fonts.gstatic.com https://frontend-cdn.perplexity.ai; " .
        "img-src 'self' data: https: blob:; " .
        "connect-src 'self' https://www.googleapis.com https://accounts.google.com https://*.googleapis.com https://apis.google.com https://www.google.com; " .
        "frame-src https://accounts.google.com https://*.googleapis.com; " .
        "object-src 'none'; " .
        "base-uri 'self';"
    );
}

// Apply security headers
setSecurityHeaders();
