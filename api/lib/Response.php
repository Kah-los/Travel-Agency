<?php
/**
 * =============================================================
 *  TravelWithNaomi API — JSON Response helper
 * -------------------------------------------------------------
 *  Centralises the success/error envelope, HTTP status codes,
 *  and the JSON Content-Type header so every endpoint is
 *  consistent.
 *
 *  Envelope:
 *    success → { "ok": true,  "data": ... }
 *    error   → { "ok": false, "error": { code, message, fields } }
 * =============================================================
 */

declare(strict_types=1);

final class Response
{
    /** Send a success envelope and stop. */
    public static function ok($data = null, int $status = 200): void
    {
        self::send($status, ['ok' => true, 'data' => $data]);
    }

    /** Send a 201 Created success envelope and stop. */
    public static function created($data = null): void
    {
        self::ok($data, 201);
    }

    /**
     * Send an error envelope and stop.
     *
     * @param array<string,string> $fields Optional per-field validation messages.
     */
    public static function error(int $status, string $code, string $message, array $fields = []): void
    {
        $error = ['code' => $code, 'message' => $message];
        if ($fields !== []) {
            $error['fields'] = $fields;
        }
        self::send($status, ['ok' => false, 'error' => $error]);
    }

    /** Encode the payload, set headers, emit, and exit. */
    private static function send(int $status, array $payload): void
    {
        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
        }
        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}
