<?php
/**
 * Session handling.
 *
 * Single responsibility: start a PHP session securely. Centralising this here
 * keeps session concerns out of the page/view layer.
 */

final class Session
{
    /**
     * Start the session if one is not already active.
     */
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
