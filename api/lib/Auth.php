<?php
/**
 * =============================================================
 *  TravelWithNaomi API — Admin authentication
 * -------------------------------------------------------------
 *  Session-based auth that reuses the SAME session flag
 *  ($_SESSION['admin_authed']) as admin/login.php and
 *  admin/dashboard.php, so a login through either path grants
 *  access to both. Keeps the 5-attempts / 5-minute lockout.
 *
 *  Admin credentials are read from config constants:
 *    APP_ADMIN_USERNAME / APP_ADMIN_PASSWORD
 *  (falling back to the classic placeholders so nothing breaks
 *  before they are configured).
 * =============================================================
 */

declare(strict_types=1);

final class Auth
{
    private const MAX_ATTEMPTS = 5;
    private const LOCK_SECONDS = 300; // 5 minutes

    /** Ensure a hardened session is running (safe to call repeatedly). */
    public static function startSession(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }
        // Match the cookie hardening used by admin/login.php + dashboard.php so
        // a login through either path shares the same session.
        $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        session_set_cookie_params([
            'lifetime' => 0,
            'path'     => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure'   => $secure,
        ]);
        session_start();
    }

    public static function username(): string
    {
        return defined('APP_ADMIN_USERNAME') ? APP_ADMIN_USERNAME : '[ADMIN_USERNAME]';
    }

    public static function password(): string
    {
        return defined('APP_ADMIN_PASSWORD') ? APP_ADMIN_PASSWORD : '[ADMIN_PASSWORD]';
    }

    /** True when the current session is an authenticated admin. */
    public static function check(): bool
    {
        self::startSession();
        return !empty($_SESSION['admin_authed']);
    }

    /** Seconds remaining on a lockout, or 0 when not locked. */
    public static function lockRemaining(): int
    {
        self::startSession();
        $lockUntil = (int) ($_SESSION['lock_until'] ?? 0);
        return max(0, $lockUntil - time());
    }

    /**
     * Attempt a login. Mirrors admin/login.php: timing-safe compare,
     * attempt counter, and a 5-minute lockout after 5 failures.
     *
     * @return bool True on success.
     */
    public static function attempt(string $user, string $pass): bool
    {
        self::startSession();

        $_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
        $_SESSION['lock_until']     = $_SESSION['lock_until'] ?? 0;

        $okUser = hash_equals(self::username(), $user);
        $okPass = hash_equals(self::password(), $pass);

        if ($okUser && $okPass) {
            session_regenerate_id(true);
            $_SESSION['admin_authed']   = true;
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lock_until']     = 0;
            $_SESSION['admin_user']     = self::username();
            return true;
        }

        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= self::MAX_ATTEMPTS) {
            $_SESSION['lock_until']     = time() + self::LOCK_SECONDS;
            $_SESSION['login_attempts'] = 0;
        }

        return false;
    }

    /** Destroy the current admin session. */
    public static function logout(): void
    {
        self::startSession();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();
    }
}
