<?php
/**
 * =============================================================
 *  TravelWithNaomi — Lead Capture Handler (HTML form)
 * -------------------------------------------------------------
 *  Receives the POST from the lead form on index.php:
 *    1. Validates every field server-side (shared Leads service).
 *    2. Inserts the lead, logs a referral click, emails the
 *       ambassador — all through the SAME code path the JSON API
 *       uses (api/lib/Leads.php), so there is one source of truth.
 *    3. Shows a branded success overlay, then redirects to the
 *       Vortex365 referral link after 3 seconds.
 *
 *  Database/email errors are never exposed to the visitor.
 * =============================================================
 */

require __DIR__ . '/config/db.php';
require __DIR__ . '/api/lib/Leads.php';

// Naomi's Vortex365 referral URL — set APP_REFERRAL_LINK in config/db.php,
// otherwise the classic [MY_REFERRAL_LINK] placeholder is used.
$referral = Leads::referralLink();

// If someone opens submit.php directly (GET), send them home.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.php');
    exit;
}

/* -----------------------------------------------------------
 *  Collect + validate (shared service)
 * --------------------------------------------------------- */
[$clean, $errors] = Leads::validate($_POST);

// The classic form requires WhatsApp + country; enforce that here so the
// HTML flow behaves exactly as before (the API treats them as optional).
if ($clean['whatsapp'] === '') {
    $errors['whatsapp'] = 'required';
}
if ($clean['country'] === '') {
    $errors['country'] = 'required';
}

// On validation failure, bounce back to the form with a flag.
if ($errors) {
    header('Location: index.php?error=1#get-started');
    exit;
}

/* -----------------------------------------------------------
 *  Persist + notify (silent on failure)
 * --------------------------------------------------------- */
$ip  = Validator::clientIp();
$pdo = db();

if ($pdo instanceof PDO) {
    try {
        (new Leads($pdo))->create($clean, $ip);
    } catch (PDOException $e) {
        // Never surface DB errors to the visitor.
        error_log('[TravelWithNaomi] Lead insert failed: ' . $e->getMessage());
    }
}

/* -----------------------------------------------------------
 *  Success overlay → redirect to the referral link
 * --------------------------------------------------------- */
$safeReferral = htmlspecialchars($referral, ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Taking you to your free access…</title>
  <!-- 3-second meta-refresh fallback in case JavaScript is disabled -->
  <meta http-equiv="refresh" content="3;url=<?= $safeReferral ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root { --navy:#0B1437; --gold:#C9A84C; }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh;
      display: grid; place-items: center;
      background: radial-gradient(circle at 50% 30%, #16224f 0%, #0B1437 60%, #070d24 100%);
      color: #fff; font-family: 'Inter', system-ui, sans-serif;
      text-align: center; padding: 24px;
    }
    .ring {
      width: 64px; height: 64px; margin: 0 auto 28px;
      border: 3px solid rgba(201,168,76,.25);
      border-top-color: var(--gold);
      border-radius: 50%;
      animation: spin .9s cubic-bezier(.4,.1,.2,1) infinite;
    }
    h1 {
      font-family: 'Playfair Display', Georgia, serif;
      font-weight: 700; font-size: clamp(1.6rem, 5vw, 2.6rem);
      margin: 0 0 12px; color: var(--gold);
    }
    p { color: #c7cddd; font-size: 1.05rem; margin: 0; }
    a { color: var(--gold); }
    @keyframes spin { to { transform: rotate(360deg); } }
    @media (prefers-reduced-motion: reduce) { .ring { animation: none; } }
  </style>
</head>
<body>
  <main>
    <div class="ring" role="status" aria-label="Loading"></div>
    <h1>Perfect! Taking you to your free access now…</h1>
    <p>If you are not redirected automatically, <a href="<?= $safeReferral ?>">click here to continue</a>.</p>
  </main>
  <script>
    // Primary redirect (JS) fires at 3s; the meta-refresh above is the fallback.
    setTimeout(function () {
      window.location.href = <?= json_encode($referral) ?>;
    }, 3000);
  </script>
</body>
</html>
