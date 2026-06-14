<?php http_response_code(404); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex">
  <title>Page not found · TravelWithNaomi</title>
  <link rel="icon" type="image/svg+xml" href="/assets/favicon.svg">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@400;500;600&family=Playfair+Display:wght@600;700&display=swap" rel="stylesheet">
  <style>
    :root { --navy:#0B1437; --gold:#C9A84C; --gold-light:#E4C97B; }
    * { box-sizing: border-box; }
    body {
      margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 24px;
      font-family: 'Jost', system-ui, sans-serif; color: #fff; text-align: center;
      background:
        radial-gradient(1100px 600px at 80% -10%, rgba(201,168,76,.14), transparent 60%),
        linear-gradient(160deg, #0B1437 0%, #070d24 100%);
    }
    .code { font-family: 'Playfair Display', serif; font-weight: 700; font-size: clamp(4rem, 18vw, 9rem); color: var(--gold); line-height: 1; }
    h1 { font-family: 'Playfair Display', serif; font-weight: 600; font-size: clamp(1.5rem, 5vw, 2.3rem); margin: .4rem 0 0; }
    p { color: #c7cddd; margin: 1rem auto 0; max-width: 30rem; line-height: 1.6; }
    a.btn {
      display: inline-block; margin-top: 2rem; padding: 14px 32px; border-radius: 9999px;
      font-weight: 700; color: var(--navy); text-decoration: none;
      background: linear-gradient(135deg, var(--gold-light), var(--gold));
      box-shadow: 0 16px 40px -16px rgba(201,168,76,.6);
      transition: transform .16s ease-out, box-shadow .2s ease-out;
    }
    a.btn:hover { transform: translateY(-2px); box-shadow: 0 22px 50px -16px rgba(201,168,76,.75); }
    a.btn:active { transform: scale(.97); }
    @media (prefers-reduced-motion: reduce) { a.btn { transition: none; } a.btn:hover, a.btn:active { transform: none; } }
  </style>
</head>
<body>
  <main>
    <div class="code">404</div>
    <h1>This trip took a wrong turn</h1>
    <p>The page you’re looking for doesn’t exist or has moved. Let’s get you back to planning your next getaway.</p>
    <a class="btn" href="/">Back to TravelWithNaomi</a>
  </main>
</body>
</html>
