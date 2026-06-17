<?php
/**
 * =============================================================
 *  TravelWithNaomi API — Input sanitisation + helpers
 * -------------------------------------------------------------
 *  Small, dependency-free helpers shared by the API and the
 *  classic HTML form handler. Mirrors the cleaning logic that
 *  originally lived in submit.php.
 * =============================================================
 */

declare(strict_types=1);

final class Validator
{
    /** Strip tags/control chars, collapse whitespace, trim. */
    public static function clean($value): string
    {
        $value = strip_tags((string) ($value ?? ''));
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
        return trim($value);
    }

    /**
     * Turn a value into a safe slug (lowercase, [a-z0-9-] only).
     * Returns the fallback when empty or longer than 100 chars.
     */
    public static function slug($value, string $fallback = ''): string
    {
        $slug = strtolower(self::clean($value));
        $slug = preg_replace('/[^a-z0-9\-]/', '', $slug) ?? '';
        if ($slug === '' || mb_strlen($slug) > 100) {
            return $fallback;
        }
        return $slug;
    }

    /** Clean a value and cap its length, returning null when empty. */
    public static function nullableCapped($value, int $max = 100): ?string
    {
        $v = self::clean($value);
        return $v === '' ? null : mb_substr($v, 0, $max);
    }

    /** Best-effort client IP, honouring common proxy headers. */
    public static function clientIp(): string
    {
        foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
            if (!empty($_SERVER[$key])) {
                $ip = trim(explode(',', $_SERVER[$key])[0]);
                if (filter_var($ip, FILTER_VALIDATE_IP)) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }
}
