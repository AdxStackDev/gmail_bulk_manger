<?php
/**
 * Application configuration / credential loader.
 *
 * Reads the Google OAuth client id from credentials.json and exposes only the
 * values the front-end is allowed to see (the public client id and the CSRF
 * token). The client secret is never exposed to the browser.
 */

final class Config
{
    /**
     * Load OAuth credentials for use by the view layer.
     *
     * @return array{client_id: string, csrf_token: string}
     */
    public static function credentials(): array
    {
        $credsFile = dirname(__DIR__) . '/credentials.json';

        if (!file_exists($credsFile)) {
            die('Error: credentials.json not found. Please copy credentials.json.example to credentials.json and add your Google OAuth credentials.');
        }

        $creds = json_decode(file_get_contents($credsFile), true);

        if (!$creds) {
            die('Error: Invalid credentials.json format');
        }

        $clientId = $creds['web']['client_id'] ?? $creds['installed']['client_id'] ?? '';

        if ($clientId === '') {
            die('Error: client_id not found in credentials.json');
        }

        return [
            'client_id'  => $clientId,
            'csrf_token' => Csrf::token(),
        ];
    }
}
