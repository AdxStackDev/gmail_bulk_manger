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
        $clientId = getenv('GMAIL_CLIENT_ID') ?: ($_ENV['GMAIL_CLIENT_ID'] ?? ($_SERVER['GMAIL_CLIENT_ID'] ?? ''));

        if ($clientId === '') {
            $credsFile = dirname(__DIR__) . '/credentials.json';

            if (file_exists($credsFile)) {
                $creds = json_decode(file_get_contents($credsFile), true);
                if ($creds) {
                    $clientId = $creds['web']['client_id'] ?? $creds['installed']['client_id'] ?? '';
                }
            }
        }

        if ($clientId === '') {
            die('Error: credentials.json not found or GMAIL_CLIENT_ID environment variable is missing.');
        }

        return [
            'client_id'  => $clientId,
            'csrf_token' => Csrf::token(),
        ];
    }
}
