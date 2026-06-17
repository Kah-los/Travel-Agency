<?php
/**
 * =============================================================
 *  TravelWithNaomi — Database Migration Runner (CLI only)
 * -------------------------------------------------------------
 *  Applies pending SQL files from migrations/ in filename order.
 *  Tracks applied files in schema_migrations. Safe to re-run.
 *
 *  Usage:  php migrate.php
 * =============================================================
 */

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "migrate.php must be run from the command line.\n");
    exit(1);
}

require __DIR__ . '/config/db.php';

$pdo = db();
if (!$pdo instanceof PDO) {
    fwrite(STDERR, "Database connection failed. Check config/db.php.\n");
    exit(1);
}

$migrationsDir = __DIR__ . '/migrations';
if (!is_dir($migrationsDir)) {
    fwrite(STDERR, "Migrations directory not found: {$migrationsDir}\n");
    exit(1);
}

/** @return list<string> */
function pending_migrations(PDO $pdo, string $dir): array
{
    $applied = [];
    try {
        $rows = $pdo->query('SELECT filename FROM schema_migrations ORDER BY filename')->fetchAll();
        foreach ($rows as $row) {
            $applied[$row['filename']] = true;
        }
    } catch (PDOException $e) {
        // schema_migrations may not exist yet — first migration creates it.
    }

    $files = glob($dir . '/*.sql') ?: [];
    sort($files, SORT_STRING);

    $pending = [];
    foreach ($files as $path) {
        $name = basename($path);
        if (!isset($applied[$name])) {
            $pending[] = $path;
        }
    }

    return $pending;
}

/** Split a migration file into individual statements (handles PREPARE blocks). */
function split_sql_statements(string $sql): array
{
    // Strip single-line comments.
    $sql = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
    $parts = array_filter(array_map('trim', explode(';', $sql)), static fn(string $s): bool => $s !== '');
    return array_values($parts);
}

$pending = pending_migrations($pdo, $migrationsDir);

if ($pending === []) {
    echo "Nothing to migrate — database is up to date.\n";
    exit(0);
}

echo 'Applying ' . count($pending) . " migration(s)...\n";

foreach ($pending as $path) {
    $filename = basename($path);
    $sql = file_get_contents($path);
    if ($sql === false) {
        fwrite(STDERR, "Could not read {$filename}\n");
        exit(1);
    }

    echo "  → {$filename} ... ";

    try {
        $pdo->beginTransaction();

        foreach (split_sql_statements($sql) as $statement) {
            $pdo->exec($statement);
        }

        $stmt = $pdo->prepare('INSERT INTO schema_migrations (filename) VALUES (:filename)');
        $stmt->execute([':filename' => $filename]);

        $pdo->commit();
        echo "OK\n";
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        fwrite(STDERR, "FAILED\n  " . $e->getMessage() . "\n");
        exit(1);
    }
}

echo "Done.\n";
