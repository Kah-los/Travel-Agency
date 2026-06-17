<?php
/**
 * =============================================================
 *  TravelWithNaomi — Admin Dashboard
 * -------------------------------------------------------------
 *  Protected: redirects to login.php without a valid session.
 *  Shows lead/click stats, a searchable leads table, a CSV
 *  export, and a logout link. Styled in navy + gold.
 * =============================================================
 */

// Harden the session cookie before the session starts (matches login.php
// and the API so all three share the same hardened session).
$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => $secureCookie,
]);
session_start();

// ---- Gatekeeper ----
if (empty($_SESSION['admin_authed'])) {
    header('Location: login.php');
    exit;
}

// ---- Logout ----
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    header('Location: login.php');
    exit;
}

require __DIR__ . '/../config/db.php';
$pdo = db();

/* -----------------------------------------------------------
 *  Data access (all read-only, all guarded)
 * --------------------------------------------------------- */
$totalLeads  = 0;
$totalClicks = 0;
$last7       = 0;
$leads       = [];
$sources     = [];
$dbDown      = !($pdo instanceof PDO);

if (!$dbDown) {
    try {
        $totalLeads  = (int) $pdo->query('SELECT COUNT(*) FROM leads')->fetchColumn();
        $totalClicks = (int) $pdo->query('SELECT COUNT(*) FROM referral_clicks')->fetchColumn();
        $last7       = (int) $pdo->query(
            'SELECT COUNT(*) FROM leads WHERE created_at >= (NOW() - INTERVAL 7 DAY)'
        )->fetchColumn();
        $leads = $pdo->query(
            'SELECT full_name, email, whatsapp, country, travel_interest, lead_source,
                    utm_source, utm_medium, utm_campaign, utm_content, utm_term, created_at
             FROM leads ORDER BY created_at DESC'
        )->fetchAll();
        // Breakdown of leads by source (highest first).
        $sources = $pdo->query(
            "SELECT COALESCE(NULLIF(lead_source, ''), 'direct-form') AS src, COUNT(*) AS n
             FROM leads GROUP BY src ORDER BY n DESC"
        )->fetchAll();
    } catch (PDOException $e) {
        error_log('[TravelWithNaomi] Dashboard query failed: ' . $e->getMessage());
        $dbDown = true;
    }
}

/* -----------------------------------------------------------
 *  CSV export — must run before any HTML is sent
 * --------------------------------------------------------- */
if (isset($_GET['export']) && !$dbDown) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="travelwithnaomi-leads-' . date('Y-m-d') . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, [
        'Name', 'Email', 'WhatsApp', 'Country', 'Travel Interest', 'Lead Source',
        'UTM Source', 'UTM Medium', 'UTM Campaign', 'UTM Content', 'UTM Term', 'Date Joined',
    ]);
    foreach ($leads as $row) {
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

$adminUser = htmlspecialchars($_SESSION['admin_user'] ?? 'Admin', ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Dashboard · TravelWithNaomi</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
  <link rel="apple-touch-icon" href="../assets/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --navy:#0B1437; --navy-800:#101a44; --navy-700:#16224f;
      --gold:#C9A84C; --gold-light:#E4C97B;
      --line:rgba(255,255,255,.08);
    }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh;
      font-family: 'Inter', system-ui, sans-serif; color: #e8ebf4;
      background:
        radial-gradient(1100px 500px at 100% -10%, rgba(201,168,76,.08), transparent 60%),
        linear-gradient(180deg, #0B1437 0%, #070d24 100%);
    }
    .wrap { max-width: 1180px; margin: 0 auto; padding: clamp(20px, 4vw, 40px); }

    header.bar {
      display: flex; flex-wrap: wrap; gap: 16px;
      align-items: center; justify-content: space-between;
      padding-bottom: 22px; margin-bottom: 30px;
      border-bottom: 1px solid var(--line);
    }
    .brand { font-family: 'Playfair Display', Georgia, serif; font-size: 1.4rem; font-weight: 700; margin: 0; }
    .brand b { color: var(--gold); }
    .who { font-size: .82rem; color: #9aa2bd; }
    .logout {
      display: inline-block; margin-left: 16px;
      padding: 8px 16px; border: 1px solid rgba(201,168,76,.5);
      border-radius: 9px; color: var(--gold); text-decoration: none;
      font-size: .82rem; font-weight: 600; transition: background .2s, color .2s;
    }
    .logout:hover { background: var(--gold); color: var(--navy); }

    .stats { display: grid; gap: 18px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); margin-bottom: 34px; }
    .stat {
      background: linear-gradient(160deg, rgba(22,34,79,.85), rgba(16,26,68,.7));
      border: 1px solid rgba(201,168,76,.28);
      border-radius: 16px; padding: 22px 24px;
    }
    .stat .num { font-family: 'Playfair Display', serif; font-size: 2.4rem; font-weight: 700; color: var(--gold); line-height: 1; }
    .stat .lbl { margin-top: 8px; font-size: .85rem; color: #aeb5cf; }

    .panel {
      background: rgba(16,26,68,.55);
      border: 1px solid var(--line);
      border-radius: 18px; overflow: hidden;
    }
    .panel-head {
      display: flex; flex-wrap: wrap; gap: 14px;
      align-items: center; justify-content: space-between;
      padding: 20px 22px; border-bottom: 1px solid var(--line);
    }
    .panel-head h2 { font-family: 'Playfair Display', serif; font-size: 1.2rem; margin: 0; font-weight: 600; }
    .tools { display: flex; gap: 10px; flex-wrap: wrap; }
    #search {
      padding: 10px 14px; min-width: 210px;
      background: rgba(7,13,36,.6); border: 1px solid rgba(255,255,255,.14);
      border-radius: 10px; color: #fff; font-family: inherit; font-size: .9rem;
    }
    #search:focus { outline: none; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,168,76,.18); }
    .btn-gold {
      display: inline-flex; align-items: center; gap: 6px;
      padding: 10px 16px; border-radius: 10px; text-decoration: none;
      font-weight: 700; font-size: .85rem; color: var(--navy);
      background: linear-gradient(135deg, var(--gold-light), var(--gold));
      transition: transform .15s, box-shadow .2s;
    }
    .btn-gold:hover { transform: translateY(-1px); box-shadow: 0 10px 24px -10px rgba(201,168,76,.6); }

    .table-scroll { overflow-x: auto; }
    table { width: 100%; border-collapse: collapse; font-size: .9rem; }
    thead th {
      text-align: left; padding: 14px 18px;
      font-size: .72rem; letter-spacing: .06em; text-transform: uppercase;
      color: var(--gold); background: rgba(7,13,36,.5); white-space: nowrap;
    }
    tbody td { padding: 14px 18px; border-top: 1px solid var(--line); color: #d6dbec; white-space: nowrap; }
    tbody tr:hover { background: rgba(201,168,76,.06); }
    .empty, .nores { padding: 40px 22px; text-align: center; color: #8b93ad; }
    .nores { display: none; }

    /* Lead sources breakdown */
    .src-grid { padding: 18px 22px; display: grid; gap: 12px; }
    .src-row { display: grid; grid-template-columns: minmax(120px, 200px) 1fr auto; align-items: center; gap: 14px; }
    .src-name { font-size: .86rem; color: #d6dbec; font-family: ui-monospace, SFMono-Regular, Menlo, monospace; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .src-bar { height: 8px; border-radius: 5px; background: rgba(255,255,255,.07); overflow: hidden; }
    .src-fill { display: block; height: 100%; border-radius: 5px; background: linear-gradient(90deg, var(--gold-light), var(--gold)); }
    .src-count { font-size: .85rem; font-weight: 700; color: #fff; white-space: nowrap; }
    .src-pct { font-weight: 500; color: var(--gold); margin-left: 4px; }
    .src-pill {
      display: inline-block; padding: 3px 9px; border-radius: 999px;
      font-size: .74rem; font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
      color: var(--gold); background: rgba(201,168,76,.12); border: 1px solid rgba(201,168,76,.25);
    }

    @media (max-width: 560px) {
      .panel-head { flex-direction: column; align-items: stretch; }
      #search { min-width: 0; width: 100%; }
      .src-row { grid-template-columns: 1fr auto; }
      .src-bar { display: none; }
    }
    @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
  </style>
</head>
<body>
  <div class="wrap">
    <header class="bar">
      <h1 class="brand">Travel<b>With</b>Naomi <span style="font-weight:400;color:#7c84a0;font-size:.95rem;">· Dashboard</span></h1>
      <div>
        <span class="who">Signed in as <?= $adminUser ?></span>
        <a class="logout" href="dashboard.php?logout=1">Log out</a>
      </div>
    </header>

    <?php if ($dbDown): ?>
      <div class="panel" style="padding:26px;border-color:rgba(220,70,70,.4);">
        <strong style="color:#ffb4b4;">The database is currently unavailable.</strong>
        <p style="color:#aeb5cf;margin:.6em 0 0;">Check your credentials in <code>config/db.php</code> and that <code>setup/install.sql</code> has been imported.</p>
      </div>
    <?php else: ?>

      <section class="stats">
        <div class="stat"><div class="num"><?= number_format($totalLeads) ?></div><div class="lbl">Total leads captured</div></div>
        <div class="stat"><div class="num"><?= number_format($totalClicks) ?></div><div class="lbl">Total referral clicks</div></div>
        <div class="stat"><div class="num"><?= number_format($last7) ?></div><div class="lbl">New leads (last 7 days)</div></div>
      </section>

      <!-- Lead Sources breakdown -->
      <section class="panel" style="margin-bottom:34px;">
        <div class="panel-head"><h2>Lead sources</h2><span style="font-size:.8rem;color:#8b93ad;">Where your leads came from</span></div>
        <?php if (!$sources): ?>
          <div class="empty">No leads yet — sources will appear here as they arrive.</div>
        <?php else: ?>
          <div class="src-grid">
            <?php foreach ($sources as $s):
              $pct = $totalLeads > 0 ? round(($s['n'] / $totalLeads) * 100) : 0; ?>
            <div class="src-row">
              <span class="src-name"><?= htmlspecialchars($s['src'], ENT_QUOTES, 'UTF-8') ?></span>
              <span class="src-bar"><span class="src-fill" style="width:<?= max(4, $pct) ?>%"></span></span>
              <span class="src-count"><?= number_format((int) $s['n']) ?> <span class="src-pct"><?= $pct ?>%</span></span>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>

      <section class="panel">
        <div class="panel-head">
          <h2>Leads</h2>
          <div class="tools">
            <input type="search" id="search" placeholder="Search name, email, country…" aria-label="Search leads">
            <a class="btn-gold" href="dashboard.php?export=1">⬇ Export CSV</a>
          </div>
        </div>

        <div class="table-scroll">
          <table id="leads-table">
            <thead>
              <tr>
                <th>Name</th><th>Email</th><th>WhatsApp</th>
                <th>Country</th><th>Travel Interest</th><th>Lead Source</th><th>Date Joined</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!$leads): ?>
                <tr class="no-filter"><td colspan="7" class="empty">No leads captured yet. They will appear here the moment your first visitor signs up.</td></tr>
              <?php else: foreach ($leads as $row): ?>
                <tr>
                  <td><?= htmlspecialchars($row['full_name'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['whatsapp'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['country'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><?= htmlspecialchars($row['travel_interest'], ENT_QUOTES, 'UTF-8') ?></td>
                  <td><span class="src-pill"><?= htmlspecialchars($row['lead_source'] ?? 'direct-form', ENT_QUOTES, 'UTF-8') ?></span></td>
                  <td><?= htmlspecialchars(date('M j, Y · H:i', strtotime($row['created_at'])), ENT_QUOTES, 'UTF-8') ?></td>
                </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
          <div class="nores" id="no-results">No leads match your search.</div>
        </div>
      </section>
    <?php endif; ?>
  </div>

  <script>
    // Client-side instant filter across the leads table.
    (function () {
      var search = document.getElementById('search');
      if (!search) return;
      var table = document.getElementById('leads-table');
      var noRes = document.getElementById('no-results');
      var rows  = Array.prototype.slice.call(table.tBodies[0].rows)
                    .filter(function (r) { return !r.classList.contains('no-filter'); });

      search.addEventListener('input', function () {
        var q = this.value.trim().toLowerCase();
        var shown = 0;
        rows.forEach(function (row) {
          var match = row.textContent.toLowerCase().indexOf(q) !== -1;
          row.style.display = match ? '' : 'none';
          if (match) shown++;
        });
        if (noRes) noRes.style.display = (rows.length && shown === 0) ? 'block' : 'none';
      });
    })();
  </script>
</body>
</html>
