<?php
/**
 * =============================================================
 *  TravelWithNaomi — JSON REST API front controller
 * -------------------------------------------------------------
 *  All /api/* requests route here (see api/.htaccess). This file
 *  parses the path, applies CORS, and dispatches to a handler.
 *
 *  Conventions:
 *    - JSON in / JSON out, always Content-Type: application/json.
 *    - Success → { ok:true, data }, error → { ok:false, error }.
 *    - Prepared statements + the shared Leads service everywhere.
 *
 *  Routes (all prefixed /api/v1):
 *    POST /leads
 *    POST /referral-clicks
 *    GET  /health
 *    POST /admin/login
 *    POST /admin/logout
 *    GET  /admin/leads
 *    GET  /admin/leads/{id}
 *    GET  /admin/leads/export
 *    GET  /admin/stats
 * =============================================================
 */

declare(strict_types=1);

// Never leak PHP errors/HTML into a JSON response; we surface clean envelopes.
ini_set('display_errors', '0');

// Load the library first so we can always answer with a JSON envelope, even
// when configuration is missing.
require __DIR__ . '/lib/Response.php';
require __DIR__ . '/lib/Validator.php';
require __DIR__ . '/lib/Leads.php';
require __DIR__ . '/lib/Auth.php';
require __DIR__ . '/lib/RateLimiter.php';

$configFile = __DIR__ . '/../config/db.php';
if (!is_file($configFile)) {
    Response::error(500, 'not_configured', 'The API is not configured. Copy config/db.php.example to config/db.php.');
}
require $configFile;

/* -----------------------------------------------------------
 *  Config defaults (overridable in config/db.php)
 * --------------------------------------------------------- */
// Comma-separated list of allowed cross-origin origins. Empty = same-origin only.
if (!defined('API_ALLOWED_ORIGINS')) {
    define('API_ALLOWED_ORIGINS', '');
}
// Public lead submissions allowed per IP per minute.
if (!defined('API_RATE_LIMIT')) {
    define('API_RATE_LIMIT', 10);
}

/* -----------------------------------------------------------
 *  CORS — same-origin by default; opt-in allowlist via config.
 * --------------------------------------------------------- */
apply_cors();

/* -----------------------------------------------------------
 *  Resolve method + path
 * --------------------------------------------------------- */
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

// Preflight: answer and stop.
if ($method === 'OPTIONS') {
    http_response_code(204);
    exit;
}

$path = api_path();                 // e.g. "v1/leads" or "v1/admin/leads/42"
$segments = $path === '' ? [] : explode('/', $path);

// Everything lives under /v1.
if (($segments[0] ?? '') !== 'v1') {
    Response::error(404, 'not_found', 'Unknown API version or route.');
}
array_shift($segments); // drop "v1"

/* -----------------------------------------------------------
 *  Dispatch
 * --------------------------------------------------------- */
$resource = $segments[0] ?? '';

try {
    if ($resource === 'health') {
        require_method($method, 'GET');
        handle_health();
    } elseif ($resource === 'leads' && count($segments) === 1) {
        require_method($method, 'POST');
        handle_create_lead();
    } elseif ($resource === 'referral-clicks' && count($segments) === 1) {
        require_method($method, 'POST');
        handle_referral_click();
    } elseif ($resource === 'admin') {
        handle_admin(array_slice($segments, 1), $method);
    } else {
        Response::error(404, 'not_found', 'Resource not found.');
    }
} catch (PDOException $e) {
    error_log('[TravelWithNaomi API] DB error: ' . $e->getMessage());
    Response::error(500, 'server_error', 'An unexpected error occurred.');
} catch (\Throwable $e) {
    // Catch-all so the client always receives a JSON envelope, never a raw
    // PHP fatal/HTML page. Details are logged, not exposed.
    error_log('[TravelWithNaomi API] Unhandled error: ' . $e->getMessage());
    Response::error(500, 'server_error', 'An unexpected error occurred.');
}

/* ===========================================================
 *  Handlers — PUBLIC
 * ========================================================= */

function handle_health(): void
{
    $pdo = db();
    $dbUp = false;
    if ($pdo instanceof PDO) {
        try {
            $pdo->query('SELECT 1');
            $dbUp = true;
        } catch (PDOException $e) {
            $dbUp = false;
        }
    }
    Response::ok(['db' => $dbUp]);
}

function handle_create_lead(): void
{
    $ip = Validator::clientIp();

    if (!RateLimiter::hit('leads', $ip, (int) API_RATE_LIMIT, 60)) {
        Response::error(429, 'rate_limited', 'Too many submissions. Please try again shortly.');
    }

    $input = read_json_body();
    [$clean, $errors] = Leads::validate($input);

    if ($errors !== []) {
        Response::error(422, 'validation_failed', 'Some fields need attention.', $errors);
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        Response::error(500, 'server_error', 'The database is currently unavailable.');
    }

    $service = new Leads($pdo);
    $leadId  = $service->create($clean, $ip);

    Response::created([
        'id'       => $leadId,
        'redirect' => Leads::referralLink(),
    ]);
}

function handle_referral_click(): void
{
    $ip  = Validator::clientIp();
    $pdo = db();
    if (!$pdo instanceof PDO) {
        Response::error(500, 'server_error', 'The database is currently unavailable.');
    }

    (new Leads($pdo))->logClick($ip);
    Response::created(['logged' => true]);
}

/* ===========================================================
 *  Handlers — ADMIN (session auth)
 * ========================================================= */

function handle_admin(array $segments, string $method): void
{
    $action = $segments[0] ?? '';

    // Login + logout do their own auth handling.
    if ($action === 'login') {
        require_method($method, 'POST');
        admin_login();
        return;
    }
    if ($action === 'logout') {
        require_method($method, 'POST');
        Auth::logout();
        Response::ok(['logged_out' => true]);
        return;
    }

    // Everything else requires an authenticated admin.
    if (!Auth::check()) {
        Response::error(401, 'unauthorized', 'Authentication required.');
    }

    $pdo = db();
    if (!$pdo instanceof PDO) {
        Response::error(500, 'server_error', 'The database is currently unavailable.');
    }

    if ($action === 'leads') {
        // /admin/leads, /admin/leads/export, /admin/leads/{id}
        $sub = $segments[1] ?? null;
        if ($sub === null) {
            require_method($method, 'GET');
            admin_list_leads($pdo);
        } elseif ($sub === 'export') {
            require_method($method, 'GET');
            admin_export_leads($pdo);
        } elseif (ctype_digit((string) $sub)) {
            require_method($method, 'GET');
            admin_get_lead($pdo, (int) $sub);
        } else {
            Response::error(404, 'not_found', 'Lead not found.');
        }
        return;
    }

    if ($action === 'stats') {
        require_method($method, 'GET');
        admin_stats($pdo);
        return;
    }

    Response::error(404, 'not_found', 'Admin resource not found.');
}

function admin_login(): void
{
    $input = read_json_body();
    $user  = trim((string) ($input['username'] ?? ''));
    $pass  = (string) ($input['password'] ?? '');

    $remaining = Auth::lockRemaining();
    if ($remaining > 0) {
        $minutes = (int) ceil($remaining / 60);
        Response::error(401, 'locked', "Too many attempts. Try again in {$minutes} minute(s).");
    }

    if (Auth::attempt($user, $pass)) {
        Response::ok(['authenticated' => true]);
    }

    Response::error(401, 'invalid_credentials', 'Incorrect username or password.');
}

function admin_list_leads(PDO $pdo): void
{
    $q        = Validator::clean($_GET['q'] ?? '');
    $interest = Validator::clean($_GET['interest'] ?? '');
    $source   = Validator::clean($_GET['source'] ?? '');
    $dateFrom = Validator::clean($_GET['date_from'] ?? '');
    $dateTo   = Validator::clean($_GET['date_to'] ?? '');

    $page    = max(1, (int) ($_GET['page'] ?? 1));
    $perPage = (int) ($_GET['per_page'] ?? 25);
    $perPage = max(1, min(100, $perPage));
    $offset  = ($page - 1) * $perPage;

    $sort  = strtolower((string) ($_GET['sort'] ?? 'newest'));
    $order = $sort === 'oldest' ? 'ASC' : 'DESC';

    // Build a WHERE clause from the optional filters (all bound).
    $where  = [];
    $params = [];

    if ($q !== '') {
        $where[] = '(full_name LIKE :q OR email LIKE :q OR country LIKE :q)';
        $params[':q'] = '%' . $q . '%';
    }
    if ($interest !== '') {
        $where[] = 'travel_interest = :interest';
        $params[':interest'] = $interest;
    }
    if ($source !== '') {
        $where[] = 'lead_source = :source';
        $params[':source'] = $source;
    }
    if ($dateFrom !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateFrom)) {
        $where[] = 'created_at >= :date_from';
        $params[':date_from'] = $dateFrom . ' 00:00:00';
    }
    if ($dateTo !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateTo)) {
        $where[] = 'created_at <= :date_to';
        $params[':date_to'] = $dateTo . ' 23:59:59';
    }

    $whereSql = $where === [] ? '' : ('WHERE ' . implode(' AND ', $where));

    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM leads {$whereSql}");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetchColumn();

    $listStmt = $pdo->prepare(
        "SELECT id, full_name, email, whatsapp, country, travel_interest, lead_source,
                utm_source, utm_medium, utm_campaign, utm_content, utm_term, ip_address, created_at
         FROM leads
         {$whereSql}
         ORDER BY created_at {$order}
         LIMIT :limit OFFSET :offset"
    );
    foreach ($params as $key => $value) {
        $listStmt->bindValue($key, $value);
    }
    $listStmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
    $listStmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $listStmt->execute();
    $rows = $listStmt->fetchAll();

    Response::ok([
        'leads'      => $rows,
        'pagination' => [
            'total'    => $total,
            'page'     => $page,
            'per_page' => $perPage,
            'pages'    => (int) ceil($total / $perPage),
        ],
    ]);
}

function admin_get_lead(PDO $pdo, int $id): void
{
    $stmt = $pdo->prepare(
        'SELECT id, full_name, email, whatsapp, country, travel_interest, lead_source,
                utm_source, utm_medium, utm_campaign, utm_content, utm_term, ip_address, created_at
         FROM leads WHERE id = :id'
    );
    $stmt->execute([':id' => $id]);
    $lead = $stmt->fetch();

    if (!$lead) {
        Response::error(404, 'not_found', 'Lead not found.');
    }

    Response::ok($lead);
}

function admin_stats(PDO $pdo): void
{
    $totalLeads  = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
    $totalClicks = (int) $pdo->query('SELECT COUNT(*) FROM referral_clicks')->fetchColumn();
    $last7       = (int) $pdo->query(
        'SELECT COUNT(*) FROM leads WHERE created_at >= (NOW() - INTERVAL 7 DAY)'
    )->fetchColumn();

    $sources = $pdo->query(
        "SELECT COALESCE(NULLIF(lead_source, ''), 'direct-form') AS source, COUNT(*) AS count
         FROM leads GROUP BY source ORDER BY count DESC"
    )->fetchAll();

    $interests = $pdo->query(
        'SELECT travel_interest AS interest, COUNT(*) AS count
         FROM leads GROUP BY travel_interest ORDER BY count DESC'
    )->fetchAll();

    Response::ok([
        'total_leads'       => $totalLeads,
        'total_clicks'      => $totalClicks,
        'leads_last_7_days' => $last7,
        'lead_sources'      => array_map(static fn($r) => [
            'source' => $r['source'],
            'count'  => (int) $r['count'],
        ], $sources),
        'top_interests'     => array_map(static fn($r) => [
            'interest' => $r['interest'],
            'count'    => (int) $r['count'],
        ], $interests),
    ]);
}

function admin_export_leads(PDO $pdo): void
{
    $rows = $pdo->query(
        'SELECT full_name, email, whatsapp, country, travel_interest, lead_source,
                utm_source, utm_medium, utm_campaign, utm_content, utm_term, created_at
         FROM leads ORDER BY created_at DESC'
    );

    if (!headers_sent()) {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="travelwithnaomi-leads-' . date('Y-m-d') . '.csv"');
    }

    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'Name', 'Email', 'WhatsApp', 'Country', 'Travel Interest', 'Lead Source',
        'UTM Source', 'UTM Medium', 'UTM Campaign', 'UTM Content', 'UTM Term', 'Date Joined',
    ]);
    foreach ($rows as $row) {
        fputcsv($out, [
            $row['full_name'],
            $row['email'],
            $row['whatsapp'],
            $row['country'],
            $row['travel_interest'],
            $row['lead_source'] ?? 'direct-form',
            $row['utm_source'] ?? '',
            $row['utm_medium'] ?? '',
            $row['utm_campaign'] ?? '',
            $row['utm_content'] ?? '',
            $row['utm_term'] ?? '',
            $row['created_at'],
        ]);
    }
    fclose($out);
    exit;
}

/* ===========================================================
 *  Plumbing
 * ========================================================= */

/** Extract the API path after "/api/", with query string removed. */
function api_path(): string
{
    // Prefer the value the .htaccess rewrite passes through.
    $raw = $_GET['_route'] ?? null;
    if ($raw === null) {
        $uri = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
        // Strip everything up to and including "/api/".
        $pos = strpos($uri, '/api/');
        $raw = $pos === false ? '' : substr($uri, $pos + 5);
    }
    return trim((string) $raw, '/');
}

/** Decode the JSON request body (tolerates form-encoded too). */
function read_json_body(): array
{
    $raw = file_get_contents('php://input') ?: '';
    if ($raw !== '') {
        $decoded = json_decode($raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }
        // Body present but not valid JSON.
        if (json_last_error() !== JSON_ERROR_NONE && empty($_POST)) {
            Response::error(400, 'invalid_json', 'Request body must be valid JSON.');
        }
    }
    return $_POST;
}

/** Enforce the HTTP method for a route, else 405. */
function require_method(string $actual, string $expected): void
{
    if ($actual !== $expected) {
        if (!headers_sent()) {
            header('Allow: ' . $expected);
        }
        Response::error(405, 'method_not_allowed', "Use {$expected} for this endpoint.");
    }
}

/** Apply CORS headers based on the configured allowlist. */
function apply_cors(): void
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    if ($origin === '') {
        return; // Same-origin request — nothing to do.
    }

    $allowed = array_filter(array_map('trim', explode(',', (string) API_ALLOWED_ORIGINS)));
    if (in_array($origin, $allowed, true)) {
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Vary: Origin');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');
    }
}
