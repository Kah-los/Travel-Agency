<?php
/**
 * =============================================================
 *  TravelWithNaomi API — Fixed-window rate limiter
 * -------------------------------------------------------------
 *  Per-IP, per-route request throttle backed by small JSON files
 *  in the system temp directory. No database row churn, no extra
 *  dependency — works on cPanel shared hosting and Railway.
 *
 *  Fixed window: N requests per `window` seconds. When the cap is
 *  exceeded, hit() returns false and the caller responds 429.
 * =============================================================
 */

declare(strict_types=1);

final class RateLimiter
{
    /**
     * Register a hit for ($key, $ip). Returns true when allowed,
     * false when the limit has been exceeded for the current window.
     */
    public static function hit(string $key, string $ip, int $limit = 10, int $window = 60): bool
    {
        $dir = sys_get_temp_dir() . '/twn_ratelimit';
        if (!is_dir($dir)) {
            @mkdir($dir, 0700, true);
        }

        $bucket = $dir . '/' . hash('sha256', $key . '|' . $ip) . '.json';
        $now    = time();

        $fp = @fopen($bucket, 'c+');
        if ($fp === false) {
            // If we cannot persist state, fail open rather than block real users.
            return true;
        }

        try {
            flock($fp, LOCK_EX);
            $raw  = stream_get_contents($fp);
            $data = json_decode($raw ?: '[]', true);

            $start = is_array($data) ? (int) ($data['start'] ?? 0) : 0;
            $count = is_array($data) ? (int) ($data['count'] ?? 0) : 0;

            if ($now - $start >= $window) {
                // Window expired — start a fresh one.
                $start = $now;
                $count = 0;
            }

            $count++;
            $allowed = $count <= $limit;

            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, json_encode(['start' => $start, 'count' => $count]));
            fflush($fp);

            return $allowed;
        } finally {
            flock($fp, LOCK_UN);
            fclose($fp);
        }
    }
}
