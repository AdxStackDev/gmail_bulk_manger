<?php
/**
 * CSRF token management.
 *
 * Issues a per-session token and verifies submitted tokens using a
 * timing-safe comparison. Requires an active session (see Session::start()).
 */

final class Csrf
{
    private const SESSION_KEY = 'csrf_token';

    /**
     * Return the current CSRF token, generating one if needed.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = bin2hex(random_bytes(32));
        }

        return $_SESSION[self::SESSION_KEY];
    }

    /**
     * Timing-safe verification of a submitted token.
     */
    public static function verify(?string $token): bool
    {
        return !empty($_SESSION[self::SESSION_KEY])
            && is_string($token)
            && hash_equals($_SESSION[self::SESSION_KEY], $token);
    }
}
