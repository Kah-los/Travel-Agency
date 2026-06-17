<?php
/**
 * =============================================================
 *  TravelWithNaomi — Admin Login
 * -------------------------------------------------------------
 *  Hardcoded credentials live at the top as placeholders.
 *  Replace [ADMIN_USERNAME] / [ADMIN_PASSWORD] before going live.
 *
 *  Basic brute-force protection: after 5 failed attempts the
 *  login is locked for 5 minutes (tracked in the session).
 * =============================================================
 */

// Load shared config so admin credentials live in ONE place (config/db.php),
// shared with the JSON API. Guarded so the page still loads without it.
$configFile = __DIR__ . '/../config/db.php';
if (is_file($configFile)) {
    require_once $configFile;
}

// Harden the session cookie before the session starts.
$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => $secureCookie,
]);
session_start();

// ---- ADMIN CREDENTIALS ----
// Recommended: set APP_ADMIN_USERNAME / APP_ADMIN_PASSWORD in config/db.php.
// These fall back to placeholders if config is not present, so nothing breaks.
define('ADMIN_USERNAME', defined('APP_ADMIN_USERNAME') ? APP_ADMIN_USERNAME : '[ADMIN_USERNAME]');
define('ADMIN_PASSWORD', defined('APP_ADMIN_PASSWORD') ? APP_ADMIN_PASSWORD : '[ADMIN_PASSWORD]');

// ---- Brute-force settings ----
const MAX_ATTEMPTS = 5;
const LOCK_SECONDS = 300; // 5 minutes

// Already logged in? Go straight to the dashboard.
if (!empty($_SESSION['admin_authed'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
$now = time();

// Initialise attempt tracking.
$_SESSION['login_attempts'] = $_SESSION['login_attempts'] ?? 0;
$_SESSION['lock_until']     = $_SESSION['lock_until'] ?? 0;

$locked = $_SESSION['lock_until'] > $now;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($locked) {
        $wait = (int) ceil(($_SESSION['lock_until'] - $now) / 60);
        $error = "Too many attempts. Please try again in {$wait} minute(s).";
    } else {
        $user = trim($_POST['username'] ?? '');
        $pass = (string) ($_POST['password'] ?? '');

        // hash_equals guards against timing attacks.
        $okUser = hash_equals(ADMIN_USERNAME, $user);
        $okPass = hash_equals(ADMIN_PASSWORD, $pass);

        if ($okUser && $okPass) {
            // Success — reset counters, rotate the session id.
            session_regenerate_id(true);
            $_SESSION['admin_authed']   = true;
            $_SESSION['login_attempts'] = 0;
            $_SESSION['lock_until']     = 0;
            $_SESSION['admin_user']     = ADMIN_USERNAME;
            header('Location: dashboard.php');
            exit;
        }

        // Failure — increment and maybe lock.
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= MAX_ATTEMPTS) {
            $_SESSION['lock_until'] = $now + LOCK_SECONDS;
            $_SESSION['login_attempts'] = 0;
            $error = 'Too many attempts. Login locked for 5 minutes.';
            $locked = true;
        } else {
            $remaining = MAX_ATTEMPTS - $_SESSION['login_attempts'];
            $error = "Incorrect username or password. {$remaining} attempt(s) left.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title>Admin Login · TravelWithNaomi</title>
  <link rel="icon" type="image/svg+xml" href="../assets/favicon.svg">
  <link rel="apple-touch-icon" href="../assets/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root { --navy:#0B1437; --navy-700:#16224f; --gold:#C9A84C; --gold-light:#E4C97B; --ink:#0B1437; }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh;
      display: grid; place-items: center; padding: 24px;
      font-family: 'Inter', system-ui, sans-serif;
      color: #fff;
      background:
        radial-gradient(1200px 600px at 80% -10%, rgba(201,168,76,.12), transparent 60%),
        radial-gradient(circle at 20% 110%, #16224f, transparent 55%),
        linear-gradient(160deg, #0B1437 0%, #070d24 100%);
    }
    .card {
      width: 100%; max-width: 400px;
      background: rgba(16,26,68,.7);
      border: 1px solid rgba(201,168,76,.45);
      border-radius: 20px;
      padding: clamp(28px, 5vw, 40px);
      box-shadow: 0 30px 80px -30px rgba(0,0,0,.7);
      backdrop-filter: blur(8px);
    }
    .brand {
      font-family: 'Playfair Display', Georgia, serif;
      font-size: 1.35rem; font-weight: 700; text-align: center;
      margin: 0 0 4px;
    }
    .brand b { color: var(--gold); font-weight: 700; }
    .sub { text-align: center; color: #aab2c9; font-size: .9rem; margin: 0 0 28px; }
    label { display: block; font-size: .8rem; font-weight: 600; color: #cdd3e6; margin: 0 0 7px; letter-spacing: .02em; }
    .field { margin-bottom: 18px; }
    input {
      width: 100%; padding: 13px 15px;
      background: rgba(7,13,36,.6);
      border: 1px solid rgba(255,255,255,.14);
      border-radius: 11px; color: #fff; font-size: 1rem;
      font-family: inherit; transition: border-color .2s, box-shadow .2s;
    }
    input:focus {
      outline: none; border-color: var(--gold);
      box-shadow: 0 0 0 3px rgba(201,168,76,.2);
    }
    button {
      width: 100%; margin-top: 6px; padding: 14px;
      border: none; border-radius: 11px; cursor: pointer;
      font-family: inherit; font-weight: 700; font-size: 1rem;
      color: #0B1437;
      background: linear-gradient(135deg, var(--gold-light), var(--gold));
      transition: transform .15s ease-out, box-shadow .2s;
    }
    button:hover { transform: translateY(-1px); box-shadow: 0 12px 30px -12px rgba(201,168,76,.7); }
    button:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }
    .error {
      background: rgba(220,70,70,.14);
      border: 1px solid rgba(220,70,70,.5);
      color: #ffb4b4; font-size: .88rem;
      padding: 11px 14px; border-radius: 10px; margin-bottom: 20px;
    }
    .back { display:block; text-align:center; margin-top:22px; color:#8b93ad; font-size:.82rem; text-decoration:none; }
    .back:hover { color: var(--gold); }
    @media (prefers-reduced-motion: reduce) { button { transition: none; } }
  </style>
</head>
<body>
  <form class="card" method="post" action="login.php" autocomplete="off" novalidate>
    <h1 class="brand">Travel<b>With</b>Naomi</h1>
    <p class="sub">Ambassador admin area</p>

    <?php if ($error): ?>
      <div class="error" role="alert"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="field">
      <label for="username">Username</label>
      <input type="text" id="username" name="username" required <?= $locked ? 'disabled' : '' ?>>
    </div>

    <div class="field">
      <label for="password">Password</label>
      <input type="password" id="password" name="password" required <?= $locked ? 'disabled' : '' ?>>
    </div>

    <button type="submit" <?= $locked ? 'disabled' : '' ?>>Sign in</button>

    <a class="back" href="../index.php">← Back to the site</a>
  </form>
</body>
</html>
