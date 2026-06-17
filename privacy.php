<?php
/**
 * =============================================================
 *  TravelWithNaomi — Privacy Policy
 * -------------------------------------------------------------
 *  Self-contained, brand-styled page (no external CSS) covering
 *  what the lead form collects, why, and how it is handled.
 *  Linked from the site footer.
 * =============================================================
 */
$updated = 'June 2026';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="index, follow">
  <title>Privacy Policy · TravelWithNaomi</title>
  <meta name="description" content="How TravelWithNaomi collects, uses, and protects the details you share through the sign-up form.">
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
  <link rel="apple-touch-icon" href="assets/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root { --navy:#0B1437; --navy-900:#070d24; --navy-700:#16224f; --gold:#C9A84C; --gold-light:#E4C97B; --line:rgba(255,255,255,.1); }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; color: #e8ebf4;
      font-family: 'Jost', system-ui, sans-serif; line-height: 1.7;
      background:
        radial-gradient(1100px 600px at 85% -10%, rgba(201,168,76,.10), transparent 60%),
        linear-gradient(170deg, #0B1437 0%, #070d24 100%);
    }
    .wrap { max-width: 760px; margin: 0 auto; padding: clamp(28px, 6vw, 72px) clamp(20px, 5vw, 32px); }
    a { color: var(--gold); text-decoration: none; }
    a:hover { text-decoration: underline; }
    .brand { font-family: 'Playfair Display', Georgia, serif; font-size: 1.4rem; font-weight: 700; color: #fff; display: inline-block; margin-bottom: 36px; }
    .brand b { color: var(--gold); }
    h1 { font-family: 'Playfair Display', Georgia, serif; font-weight: 700; font-size: clamp(2rem, 6vw, 3rem); color: #fff; margin: 0 0 8px; line-height: 1.1; }
    .updated { color: #8b93ad; font-size: .9rem; margin: 0 0 40px; }
    h2 { font-family: 'Playfair Display', Georgia, serif; font-weight: 600; font-size: 1.3rem; color: var(--gold-light); margin: 38px 0 12px; }
    p { color: #c7cddd; margin: 0 0 14px; }
    ul { color: #c7cddd; margin: 0 0 14px; padding-left: 1.2rem; }
    li { margin-bottom: 8px; }
    .card { background: rgba(16,26,68,.55); border: 1px solid var(--line); border-radius: 18px; padding: clamp(24px, 5vw, 40px); }
    .back { display: inline-block; margin-top: 40px; padding: 13px 28px; border-radius: 9999px; font-weight: 700; color: var(--navy); background: linear-gradient(135deg, var(--gold-light), var(--gold)); transition: transform .16s ease-out; }
    .back:hover { transform: translateY(-2px); text-decoration: none; }
    .foot { margin-top: 44px; padding-top: 22px; border-top: 1px solid var(--line); color: #7c84a0; font-size: .82rem; }
    @media (prefers-reduced-motion: reduce) { .back { transition: none; } .back:hover { transform: none; } }
  </style>
</head>
<body>
  <div class="wrap">
    <a class="brand" href="index.php">Travel<b>With</b>Naomi</a>

    <article class="card">
      <h1>Privacy Policy</h1>
      <p class="updated">Last updated: <?= htmlspecialchars($updated, ENT_QUOTES, 'UTF-8') ?></p>

      <p>This page explains what information TravelWithNaomi collects when you use the
        sign-up form, why we collect it, and how it is handled. We keep this short and
        plain. If anything is unclear, reach out through the contact links on the
        <a href="index.php">homepage</a>.</p>

      <h2>What we collect</h2>
      <p>When you submit the sign-up form, we collect the details you choose to provide:</p>
      <ul>
        <li>Your full name and email address.</li>
        <li>Your WhatsApp number and country (optional, to help us assist you).</li>
        <li>The type of travel you're interested in.</li>
        <li>Basic technical data: your IP address and the time of your submission.</li>
        <li>Campaign attribution (which link or card you arrived from, including UTM tags),
          used only to understand which content is helpful.</li>
      </ul>

      <h2>Why we collect it</h2>
      <ul>
        <li>To set up your free access to the members-only travel portal.</li>
        <li>To personally follow up and help you start saving on travel.</li>
        <li>To understand which campaigns bring people in, so we can improve the site.</li>
      </ul>

      <h2>How it's stored</h2>
      <p>Your details are stored securely in our database and are accessible only to Naomi
        through a password-protected admin area. We use prepared database statements and
        do not expose your information publicly. We never sell your data.</p>

      <h2>Sharing &amp; third parties</h2>
      <p>To create your account, you are forwarded to the Vortex365 (Surge365) travel
        portal, which has its own privacy practices. We are an independent member and are
        not affiliated with or endorsed by Surge365 corporate. Email notifications about
        your sign-up may be delivered through a standard email provider.</p>

      <h2>Cookies &amp; local storage</h2>
      <p>The public site does not use tracking cookies. We temporarily store campaign tags
        in your browser's session storage so the form can be pre-filled — these clear when
        you close the tab. The admin area uses a single session cookie strictly for login.</p>

      <h2>Data retention</h2>
      <p>We keep your sign-up details for as long as needed to support you, and remove them
        on request.</p>

      <h2>Your choices</h2>
      <p>You can ask us to access, correct, or delete the information you've shared. To make
        a request, contact us through the links on the <a href="index.php">homepage</a>.</p>

      <a class="back" href="index.php">← Back to the site</a>

      <p class="foot">TravelWithNaomi is operated by an independent Vortex365 member.
        This site is not affiliated with or endorsed by Surge365 corporate.</p>
    </article>
  </div>
</body>
</html>
