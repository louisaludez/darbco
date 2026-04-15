<?php
// ============================================================
//  DARBCO System — CSRF Protection Helper
//  File   : config/csrf.php
//  Usage  : Call Csrf::token() in forms, Csrf::verify() in
//           controllers before processing any POST request.
// ============================================================

declare(strict_types=1);

class Csrf
{
    private const TOKEN_KEY    = '_darbco_csrf_token';
    private const TOKEN_LENGTH = 32; // bytes

    /**
     * Generate (or return existing) CSRF token for this session.
     */
    public static function token(): string
    {
        if (empty($_SESSION[self::TOKEN_KEY])) {
            $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(self::TOKEN_LENGTH));
        }
        return $_SESSION[self::TOKEN_KEY];
    }

    /**
     * Render a hidden CSRF input field — paste into every <form>.
     */
    public static function field(): string
    {
        return '<input type="hidden" name="_csrf_token" value="'
            . htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8')
            . '">';
    }

    /**
     * Verify the submitted token against the session token.
     * Terminates with 403 on failure.
     */
    public static function verify(): void
    {
        $submitted = $_POST['_csrf_token'] ?? '';
        $expected  = $_SESSION[self::TOKEN_KEY] ?? '';

        if (!hash_equals($expected, $submitted)) {
            http_response_code(403);
            die('<h2>403 — CSRF token mismatch. Please go back and try again.</h2>');
        }

        // Rotate token after successful verification
        unset($_SESSION[self::TOKEN_KEY]);
    }
}
