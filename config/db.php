<?php
/**
 * =============================================================
 *  TravelWithNaomi — Database Connection
 * -------------------------------------------------------------
 *  Edit the four credential constants below, then everything
 *  else in the site (submit.php, admin/dashboard.php) uses the
 *  $pdo connection created here.
 *
 *  On a connection failure the site fails SILENTLY: visitors
 *  never see a database error or a stack trace. The error is
 *  written to the PHP error log for you to inspect instead.
 * =============================================================
 */

// ---- DATABASE CREDENTIALS — replace the placeholder values ----
define('DB_HOST', '[DB_HOST]');   // e.g. 'localhost'  (cPanel) or the Railway MYSQLHOST
define('DB_NAME', '[DB_NAME]');   // e.g. 'naomi_travel'
define('DB_USER', '[DB_USER]');   // e.g. 'naomi_admin'
define('DB_PASS', '[DB_PASS]');   // your database password

// ---- Optional: port (Railway exposes a non-standard port) ----
define('DB_PORT', '3306');

/**
 * Returns a shared PDO connection, or null if the database is
 * unreachable. Never throws to the page.
 *
 * @return PDO|null
 */
function db() {
    static $pdo = null;
    static $tried = false;

    if ($tried) {
        return $pdo;
    }
    $tried = true;

    $dsn = sprintf(
        'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
        DB_HOST,
        DB_PORT,
        DB_NAME
    );

    try {
        $pdo = new PDO($dsn, DB_USER, DB_PASS, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    } catch (PDOException $e) {
        // Silent for the visitor; logged for the owner.
        error_log('[TravelWithNaomi] DB connection failed: ' . $e->getMessage());
        $pdo = null;
    }

    return $pdo;
}

// Establish the connection test immediately so includes can rely on db().
db();
