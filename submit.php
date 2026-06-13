<?php
/**
 * =============================================================
 *  TravelWithNaomi — Lead Capture Handler
 * -------------------------------------------------------------
 *  Receives the POST from the lead form on index.php:
 *    1. Sanitises + validates every field server-side.
 *    2. Inserts the lead into `leads`.
 *    3. Logs a row in `referral_clicks`.
 *    4. Emails a notification to the ambassador.
 *    5. Shows a branded success overlay, then redirects to the
 *       Vortex365 referral link after 3 seconds.
 *
 *  Database errors are never exposed to the visitor.
 * =============================================================
 */

require __DIR__ . '/config/db.php';

// ---- PLACEHOLDERS — replace before going live ----
const MY_REFERRAL_LINK = '[MY_REFERRAL_LINK]';        // Naomi's Vortex365 referral URL
const MY_EMAIL         = '[MY_EMAIL]';                // Where lead notifications are sent

// If someone opens submit.php directly (GET), send them home.
if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: index.php');
    exit;
}

/* -----------------------------------------------------------
 *  Helpers
 * --------------------------------------------------------- */
function clean(string $key): string {
    $value = $_POST[$key] ?? '';
    // Strip control chars / tags, collapse whitespace, trim.
    $value = strip_tags((string) $value);
    $value = preg_replace('/\s+/u', ' ', $value);
    return trim($value);
}

function client_ip(): string {
    foreach (['HTTP_CF_CONNECTING_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'] as $key) {
        if (!empty($_SERVER[$key])) {
            // X-Forwarded-For can be a list; take the first.
            $ip = trim(explode(',', $_SERVER[$key])[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }
    }
    return '0.0.0.0';
}

/* -----------------------------------------------------------
 *  Collect + validate
 * --------------------------------------------------------- */
$fullName = clean('full_name');
$email    = clean('email');
$whatsapp = clean('whatsapp');
$country  = clean('country');
$interest = clean('travel_interest');

// Must mirror the <select> options in index.php, or valid submissions are rejected.
$allowedInterests = [
    'Family Vacation',
    'Beach Getaway',
    'Cruise',
    'City Break',
    'Weekend Trip',
    'Honeymoon or Anniversary',
    'Visiting Family or Friends',
    'All of the Above',
];
$errors = [];

if ($fullName === '' || mb_strlen($fullName) > 120) {
    $errors[] = 'name';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
    $errors[] = 'email';
}
// WhatsApp: digits, spaces, +, -, () — at least 7 digits.
$digits = preg_replace('/\D+/', '', $whatsapp);
if (strlen($digits) < 7 || strlen($digits) > 20) {
    $errors[] = 'whatsapp';
}
if ($country === '' || mb_strlen($country) > 80) {
    $errors[] = 'country';
}
if (!in_array($interest, $allowedInterests, true)) {
    $errors[] = 'interest';
}

// On validation failure, bounce back to the form with a flag.
if ($errors) {
    header('Location: index.php?error=1#get-started');
    exit;
}

/* -----------------------------------------------------------
 *  Persist (silent on DB failure)
 * --------------------------------------------------------- */
$ip = client_ip();
$pdo = db();

if ($pdo instanceof PDO) {
    try {
        $stmt = $pdo->prepare(
            'INSERT INTO leads (full_name, email, whatsapp, country, travel_interest, ip_address)
             VALUES (:full_name, :email, :whatsapp, :country, :travel_interest, :ip)'
        );
        $stmt->execute([
            ':full_name'       => $fullName,
            ':email'           => $email,
            ':whatsapp'        => $whatsapp,
            ':country'         => $country,
            ':travel_interest' => $interest,
            ':ip'              => $ip,
        ]);

        // Log the referral click that is about to happen.
        $pdo->prepare('INSERT INTO referral_clicks (ip_address) VALUES (:ip)')
            ->execute([':ip' => $ip]);
    } catch (PDOException $e) {
        // Never surface DB errors to the visitor.
        error_log('[TravelWithNaomi] Lead insert failed: ' . $e->getMessage());
    }
}

/* -----------------------------------------------------------
 *  Notify the ambassador by email
 *  ----------------------------------------------------------
 *  The subject + body are identical regardless of transport.
 *  If SMTP is configured (smtp_ready()), send via PHPMailer over
 *  TLS. Otherwise fall back to PHP mail(). Either way, email
 *  failures never block the redirect.
 * --------------------------------------------------------- */
if (MY_EMAIL !== '[MY_EMAIL]' && filter_var(MY_EMAIL, FILTER_VALIDATE_EMAIL)) {
    $subject = 'New TravelWithNaomi lead: ' . $fullName;
    $body =
        "You captured a new lead from your landing page.\n\n" .
        "Name:            {$fullName}\n" .
        "Email:           {$email}\n" .
        "WhatsApp:        {$whatsapp}\n" .
        "Country:         {$country}\n" .
        "Travel interest: {$interest}\n" .
        "IP address:      {$ip}\n" .
        "Captured at:     " . date('Y-m-d H:i:s') . " UTC\n";

    if (smtp_ready()) {
        // ---- SMTP path (PHPMailer over TLS) ----
        require_once __DIR__ . '/libs/phpmailer/Exception.php';
        require_once __DIR__ . '/libs/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/libs/phpmailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(false); // false = don't throw; we check return value
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->Port       = SMTP_PORT;
            // 465 = implicit SSL, anything else (e.g. 587) = STARTTLS.
            $mail->SMTPSecure = (SMTP_PORT === 465)
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress(MY_EMAIL);
            $mail->addReplyTo($email, $fullName);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $body; // plain text
            $mail->send();
        } catch (\Throwable $e) {
            // Log silently; never surface to the visitor or block the redirect.
            error_log('[TravelWithNaomi] SMTP send failed: ' . $e->getMessage() . ' | ' . $mail->ErrorInfo);
        }
    } else {
        // ---- Fallback path (PHP mail(), the cPanel default) ----
        $headers = [
            'From: TravelWithNaomi <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>',
            'Reply-To: ' . $email,
            'Content-Type: text/plain; charset=UTF-8',
        ];
        // @-suppressed: mail() may be disabled on some hosts; never block the redirect.
        @mail(MY_EMAIL, $subject, $body, implode("\r\n", $headers));
    }
}

/* -----------------------------------------------------------
 *  Success overlay → redirect to the referral link
 * --------------------------------------------------------- */
$referral = MY_REFERRAL_LINK;
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
