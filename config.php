<?php
/**
 * Gmail Manager bootstrap.
 *
 * This file is the single include point for every page. It wires together the
 * separated backend concerns (session, security headers, CSRF, credentials)
 * and exposes the same helper functions the pages already use, so existing
 * page code continues to work unchanged.
 */

require_once __DIR__ . '/app/Security/Session.php';
require_once __DIR__ . '/app/Security/Csrf.php';
require_once __DIR__ . '/app/Security/Headers.php';
require_once __DIR__ . '/app/Config.php';

// Bootstrap side effects (must run before any output)
Session::start();
Headers::send();

/* -------------------------------------------------------------------------
 * Backwards-compatible helpers
 * ---------------------------------------------------------------------- */

/**
 * @return array{client_id: string, csrf_token: string}
 */
function loadCredentials(): array
{
    return Config::credentials();
}

function verifyCsrfToken(?string $token): bool
{
    return Csrf::verify($token);
}

function setSecurityHeaders(): void
{
    Headers::send();
}
