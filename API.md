# TravelWithNaomi — JSON REST API

A thin JSON layer over the existing lead-capture data model. Pure PHP 8 + PDO,
no framework, no Composer. All requests are routed through the front controller
at `api/index.php` by `api/.htaccess`.

## Conventions

- **JSON in / JSON out.** Every response sets `Content-Type: application/json`.
- **Envelope:**
  - Success → `{ "ok": true, "data": ... }`
  - Error → `{ "ok": false, "error": { "code": "...", "message": "...", "fields": { ... } } }`
    (`fields` is present only for validation errors.)
- **Base path:** all endpoints live under `/api/v1`.
- **Auth:** admin endpoints use the same PHP session as `admin/login.php`
  / `admin/dashboard.php`. Send the session cookie with each request
  (`-b/-c cookies.txt` in the curl examples).
- **CORS:** same-origin only by default. Set `API_ALLOWED_ORIGINS` in
  `config/db.php` to a comma-separated allowlist to permit cross-origin callers.
- **Rate limiting:** `POST /api/v1/leads` is capped per IP (default 10/min,
  configurable via `API_RATE_LIMIT`).

### Status codes

| Code | Meaning |
|---|---|
| 200 | OK |
| 201 | Created |
| 400 | Malformed request (e.g. invalid JSON) |
| 401 | Not authenticated / invalid credentials |
| 404 | Route or resource not found |
| 405 | Method not allowed |
| 422 | Validation failed (see `error.fields`) |
| 429 | Rate limit exceeded |
| 500 | Server / database error |

---

## Public endpoints

### `POST /api/v1/leads`

Create a lead. Mirrors the HTML form's validation and shares the same insert +
email path (`api/lib/Leads.php`).

Auth: none. Rate-limited per IP.

Request body:

| Field | Required | Notes |
|---|---|---|
| `full_name` | yes | max 120 chars |
| `email` | yes | valid email, max 180 chars |
| `whatsapp` | no | 7–20 digits when present |
| `country` | no | max 80 chars |
| `travel_interest` | yes | one of the 8 allowed values (below) |
| `lead_source` | no | slugified, defaults to `direct-form` |
| `utm_source` `utm_medium` `utm_campaign` `utm_content` `utm_term` | no | cleaned, capped at 100 chars |

Allowed `travel_interest` values: `Family Vacation`, `Beach Getaway`, `Cruise`,
`City Break`, `Weekend Trip`, `Honeymoon or Anniversary`,
`Visiting Family or Friends`, `All of the Above`.

Success (`201`):

```json
{ "ok": true, "data": { "id": 42, "redirect": "https://your-referral-link" } }
```

Validation error (`422`):

```json
{
  "ok": false,
  "error": {
    "code": "validation_failed",
    "message": "Some fields need attention.",
    "fields": { "email": "A valid email address is required." }
  }
}
```

curl:

```bash
curl -s -X POST https://yoursite.com/api/v1/leads \
  -H 'Content-Type: application/json' \
  -d '{
    "full_name": "Jane Doe",
    "email": "jane@example.com",
    "whatsapp": "+1 555 0100",
    "country": "United States",
    "travel_interest": "Cruise",
    "lead_source": "caribbean-cruise-card",
    "utm_source": "facebook",
    "utm_campaign": "cruise_offer"
  }'
```

### `POST /api/v1/referral-clicks`

Log a referral click (captures the caller's IP).

Auth: none.

Success (`201`):

```json
{ "ok": true, "data": { "logged": true } }
```

curl:

```bash
curl -s -X POST https://yoursite.com/api/v1/referral-clicks \
  -H 'Content-Type: application/json' -d '{}'
```

### `GET /api/v1/health`

Liveness probe with a database connectivity check.

Auth: none.

Success (`200`):

```json
{ "ok": true, "data": { "db": true } }
```

curl:

```bash
curl -s https://yoursite.com/api/v1/health
```

---

## Admin endpoints (session auth)

All admin endpoints below return `401` with an `unauthorized` error envelope
when there is no valid admin session.

### `POST /api/v1/admin/login`

Validate credentials (timing-safe), start the admin session. Keeps the
5-attempts / 5-minute lockout. Credentials come from `APP_ADMIN_USERNAME` /
`APP_ADMIN_PASSWORD` in `config/db.php`.

Request body: `{ "username": "...", "password": "..." }`

Success (`200`): `{ "ok": true, "data": { "authenticated": true } }`
Failure (`401`): `invalid_credentials`, or `locked` while locked out.

curl (store the session cookie in `cookies.txt`):

```bash
curl -s -c cookies.txt -X POST https://yoursite.com/api/v1/admin/login \
  -H 'Content-Type: application/json' \
  -d '{"username":"admin","password":"secret"}'
```

### `POST /api/v1/admin/logout`

Destroy the admin session.

Success (`200`): `{ "ok": true, "data": { "logged_out": true } }`

curl:

```bash
curl -s -b cookies.txt -X POST https://yoursite.com/api/v1/admin/logout
```

### `GET /api/v1/admin/leads`

Paginated, searchable list of leads.

Query parameters (all optional):

| Param | Notes |
|---|---|
| `q` | matches name, email, or country |
| `interest` | exact `travel_interest` |
| `source` | exact `lead_source` |
| `date_from` / `date_to` | `YYYY-MM-DD` (inclusive) |
| `page` | 1-based, default 1 |
| `per_page` | default 25, max 100 |
| `sort` | `newest` (default) or `oldest` |

Success (`200`):

```json
{
  "ok": true,
  "data": {
    "leads": [ { "id": 42, "full_name": "Jane Doe", "email": "jane@example.com", "created_at": "2026-06-17 12:00:00" } ],
    "pagination": { "total": 1, "page": 1, "per_page": 25, "pages": 1 }
  }
}
```

curl:

```bash
curl -s -b cookies.txt \
  'https://yoursite.com/api/v1/admin/leads?q=jane&interest=Cruise&page=1&per_page=25&sort=newest'
```

### `GET /api/v1/admin/leads/{id}`

Fetch a single lead by id. `404` if missing.

Success (`200`): `{ "ok": true, "data": { "id": 42, ... } }`

curl:

```bash
curl -s -b cookies.txt https://yoursite.com/api/v1/admin/leads/42
```

### `GET /api/v1/admin/stats`

Aggregate dashboard stats.

Success (`200`):

```json
{
  "ok": true,
  "data": {
    "total_leads": 120,
    "total_clicks": 130,
    "leads_last_7_days": 12,
    "lead_sources": [ { "source": "direct-form", "count": 80 } ],
    "top_interests": [ { "interest": "Cruise", "count": 40 } ]
  }
}
```

curl:

```bash
curl -s -b cookies.txt https://yoursite.com/api/v1/admin/stats
```

### `GET /api/v1/admin/leads/export`

Stream all leads as a CSV download (all columns incl. `lead_source` and the
five UTM fields). Sends `Content-Disposition: attachment`. Requires auth.

curl (save to a file):

```bash
curl -s -b cookies.txt -OJ https://yoursite.com/api/v1/admin/leads/export
```
