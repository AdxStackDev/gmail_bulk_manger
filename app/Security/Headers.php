<?php
/**
 * HTTP security headers.
 *
 * Centralises the Content-Security-Policy and hardening headers. The policy is
 * intentionally relaxed where Google APIs and the Tailwind CDN require it.
 * Note: 'unsafe-inline'/'unsafe-eval' remain because the pages still use inline
 * event handlers and the Tailwind CDN. Tightening these is a follow-up that
 * depends on moving to a local CSS build and addEventListener bindings.
 */

final class Headers
{
    /**
     * Emit all security headers. Must be called before any output.
     */
    public static function send(): void
    {
        // Prevent clickjacking
        header('X-Frame-Options: SAMEORIGIN');

        // XSS protection (legacy browsers)
        header('X-XSS-Protection: 1; mode=block');

        // Prevent MIME sniffing
        header('X-Content-Type-Options: nosniff');

        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');

        // Content Security Policy - relaxed for Google APIs and Tailwind CDN
        header('Content-Security-Policy: ' .
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
}
