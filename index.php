<?php
/**
 * =============================================================
 *  TravelWithNaomi — Vortex365 Referral Landing Page
 * -------------------------------------------------------------
 *  Single-page marketing site. Tailwind + Alpine via CDN, no
 *  build step. The lead form posts to submit.php which saves
 *  the lead and forwards to the Vortex365 referral link.
 *
 *  PLACEHOLDERS used in this file (replace before going live):
 *    [MY_REFERRAL_LINK]  — Naomi's Vortex365 referral URL
 *    [MY_WHATSAPP_LINK]  — WhatsApp contact URL
 *    [MY_SOCIAL_LINKS]   — Instagram / Facebook / TikTok URLs
 * =============================================================
 */

// -------- EDIT THESE LINKS --------
$REFERRAL_LINK = '[MY_REFERRAL_LINK]';   // Naomi's Vortex365 referral URL
$WHATSAPP_LINK = '[MY_WHATSAPP_LINK]';   // e.g. https://wa.me/447000000000
$INSTAGRAM     = '[MY_SOCIAL_LINKS]';    // Instagram URL
$FACEBOOK      = '[MY_SOCIAL_LINKS]';    // Facebook URL
$TIKTOK        = '[MY_SOCIAL_LINKS]';    // TikTok URL

$ref = htmlspecialchars($REFERRAL_LINK, ENT_QUOTES, 'UTF-8');
$wa  = htmlspecialchars($WHATSAPP_LINK, ENT_QUOTES, 'UTF-8');

// Did submit.php bounce back a server-side validation error?
$serverError = isset($_GET['error']);

// Canonical URL (host + path, query stripped) for SEO + social cards.
$scheme    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host      = $_SERVER['HTTP_HOST'] ?? 'travelwithnaomi.com';
$path      = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$canonical = htmlspecialchars($scheme . '://' . $host . $path, ENT_QUOTES, 'UTF-8');
$ogImage   = 'https://images.unsplash.com/photo-1530789253388-582c481c54b0?auto=format&fit=crop&w=1200&h=630&q=80';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TravelWithNaomi — Stop Overpaying for Travel</title>
  <meta name="description" content="Naomi gives you free access to a members-only travel portal that beats Expedia and Booking.com up to 90% of the time. Free to join, no credit card.">
  <meta name="theme-color" content="#0B1437">
  <link rel="canonical" href="<?= $canonical ?>">

  <!-- Favicon (SVG; modern browsers) -->
  <link rel="icon" type="image/svg+xml" href="assets/favicon.svg">
  <link rel="apple-touch-icon" href="assets/favicon.svg">

  <!-- Open Graph / social cards -->
  <meta property="og:title" content="TravelWithNaomi — Stop Overpaying for Travel">
  <meta property="og:description" content="A members-only travel portal that beats the major platforms up to 90% of the time. Free to join.">
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $canonical ?>">
  <meta property="og:image" content="<?= $ogImage ?>">
  <meta property="og:site_name" content="TravelWithNaomi">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="TravelWithNaomi — Stop Overpaying for Travel">
  <meta name="twitter:description" content="A members-only travel portal that beats the major platforms up to 90% of the time. Free to join.">
  <meta name="twitter:image" content="<?= $ogImage ?>">

  <!-- Fonts: Playfair Display (display) + Jost (body) -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&family=Playfair+Display:wght@500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind (CDN) + brand config -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            navy:  { DEFAULT: '#0B1437', 900: '#070d24', 800: '#101a44', 700: '#16224f', 600: '#1d2b60' },
            gold:  { DEFAULT: '#C9A84C', light: '#E4C97B', dark: '#A88A38', ink: '#8A6D1F' },
            ink:   { DEFAULT: '#0B1437', 2: '#495066', 3: '#6B7286' },
            paper: '#FFFFFF',
            mist:  '#F3F6FB'
          },
          fontFamily: {
            display: ['"Playfair Display"', 'Georgia', 'serif'],
            body:    ['Jost', 'system-ui', 'sans-serif']
          }
        }
      }
    };
  </script>

  <!-- Alpine (CDN) for nav + form interactivity -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <link rel="stylesheet" href="assets/style.css">
  <script>document.documentElement.classList.add('js');</script>
</head>

<body class="font-body selection:bg-gold/30">

<!-- =========================================================
     SECTION 1 — NAVIGATION
     ========================================================= -->
<header
  x-data="{ open: false, scrolled: false }"
  x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 24)"
  :class="scrolled ? 'nav-solid' : ''"
  class="fixed inset-x-0 top-0 z-[100] transition-all duration-300"
>
  <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8">
    <a href="#top" class="font-display text-xl font-bold tracking-tight transition-colors sm:text-2xl"
       :class="scrolled ? 'text-ink' : 'text-white'">
      Travel<span class="text-gold">With</span>Naomi
    </a>

    <div class="hidden items-center gap-9 md:flex">
      <a href="#how"   class="text-sm font-medium transition-colors hover:text-gold" :class="scrolled ? 'text-ink-2' : 'text-white/85'">How It Works</a>
      <a href="#why"   class="text-sm font-medium transition-colors hover:text-gold" :class="scrolled ? 'text-ink-2' : 'text-white/85'">Why Vortex</a>
      <a href="#about" class="text-sm font-medium transition-colors hover:text-gold" :class="scrolled ? 'text-ink-2' : 'text-white/85'">About Naomi</a>
      <a href="#get-started" class="btn btn-gold px-6 py-2.5 text-sm">Get Started</a>
    </div>

    <button @click="open = !open" class="md:hidden" :aria-expanded="open" aria-label="Toggle menu">
      <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
      <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </nav>

  <div x-show="open" x-cloak x-transition.origin.top
       class="border-t border-line bg-white/95 px-5 py-3 backdrop-blur md:hidden">
    <a @click="open=false" href="#how"   class="block py-3 font-medium text-ink-2">How It Works</a>
    <a @click="open=false" href="#why"   class="block py-3 font-medium text-ink-2">Why Vortex</a>
    <a @click="open=false" href="#about" class="block py-3 font-medium text-ink-2">About Naomi</a>
    <a @click="open=false" href="#get-started" class="btn btn-gold mt-2 block px-6 py-3 text-center">Get Started</a>
  </div>
</header>

<main id="top">

<!-- =========================================================
     SECTION 2 — HERO (drenched navy + destination photo)
     ========================================================= -->
<section class="hero relative flex min-h-[100svh] items-center pt-24">
  <!-- Real destination photo (Maldives overwater) behind a navy gradient overlay -->
  <div class="hero-photo" style="background-image:url('https://images.unsplash.com/photo-1530789253388-582c481c54b0?auto=format&fit=crop&w=2000&q=80');" aria-hidden="true"></div>
  <div class="hero-overlay" aria-hidden="true"></div>

  <div class="float-words" aria-hidden="true">
    <span style="top:17%; left:7%;  animation-duration:18s; animation-delay:0s;">Santorini</span>
    <span style="top:64%; left:13%; animation-duration:22s; animation-delay:3s;">Maldives</span>
    <span style="top:28%; right:9%; animation-duration:20s; animation-delay:6s;">Bali</span>
    <span style="top:75%; right:15%;animation-duration:24s; animation-delay:1.5s;">Paris</span>
    <span style="top:45%; left:47%; animation-duration:26s; animation-delay:8s;">Cape Town</span>
    <span style="top:11%; right:33%;animation-duration:21s; animation-delay:4.5s;">Tokyo</span>
  </div>
  <div class="particles" id="particles" aria-hidden="true"></div>

  <div class="relative z-10 mx-auto max-w-4xl px-5 text-center text-white sm:px-8">
    <h1 class="reveal font-display text-[clamp(2.5rem,7.5vw,5.25rem)] font-bold leading-[1.04] tracking-tight" style="text-wrap:balance;">
      Stop Paying Full Price<br>for Travel.
    </h1>
    <p class="reveal mx-auto mt-7 max-w-2xl text-lg leading-relaxed text-white/85 sm:text-xl" data-delay="1">
      Access exclusive member pricing on hotels, cruises, resorts, vacation packages,
      and family getaways worldwide. Free to join, no credit card required.
    </p>
    <div class="reveal mt-10 flex flex-col items-center gap-4" data-delay="2">
      <a href="<?= $ref ?>" class="btn btn-gold px-10 py-4 text-base sm:text-lg">Get My Free Access</a>
      <p class="text-sm text-white/60">No credit card required · Free to join · Takes 2 minutes</p>
    </div>
  </div>

  <a href="#trust" class="absolute bottom-6 left-1/2 z-10 -translate-x-1/2 text-gold/70 transition hover:text-gold" aria-label="Scroll down">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
  </a>
</section>

<!-- =========================================================
     SECTION 3 — TRUST BAR (light, airy, count-up)
     ========================================================= -->
<section id="trust" class="sec-paper py-20 sm:py-24">
  <div class="mx-auto grid max-w-6xl gap-6 px-5 sm:px-8 md:grid-cols-3" id="stats-row">
    <?php
      $stats = [
        ['90', '%', '85–90% beat rate vs major platforms'],
        ['100', '%', 'Free to create your account'],
        ['1000', '+', 'Members saving on travel daily'],
      ];
      foreach ($stats as $i => [$num, $suffix, $label]):
    ?>
    <div class="reveal card lift px-8 py-10 text-center" data-delay="<?= $i ?>">
      <div class="stat-num"><span class="counter" data-target="<?= $num ?>">0</span><span class="unit"><?= $suffix ?></span></div>
      <div class="stat-rule"></div>
      <p class="mx-auto mt-5 max-w-[15rem] leading-relaxed text-ink-2"><?= $label ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- =========================================================
     SECTION 3b — DESTINATION CATEGORIES (light, editorial cards)
     ========================================================= -->
<section id="categories" class="sec-mist edge-top py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-ink" style="text-wrap:balance;">However you love to travel</h2>
      <p class="lede mt-4 text-lg">Pick the kind of trip you have in mind. Member pricing covers them all.</p>
    </div>

    <div class="mt-14 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
      <?php
        // [label, dropdown interest, photo id, alt, personalised heading, lead_source, url slug]
        $cats = [
          ['Family Vacations', 'Family Vacation', '1542037104857-ffbb0b9155fb', 'A family walking together on holiday',     'Find Out How Much You Could Save on a Family Vacation', 'family-vacations-card', 'family-vacation'],
          ['Beach Getaways',   'Beach Getaway',   '1507525428034-b723cf961d3e', 'A sunlit tropical beach at golden hour',   'Find Out How Much You Could Save on a Beach Getaway',   'beach-getaways-card',   'beach-getaway'],
          ['Cruises',          'Cruise',          '1599640842225-85d111c60e6b', 'A cruise ship moored beside a turquoise bay','Find Out How Much You Could Save on a Cruise',         'cruises-card',          'cruise'],
          ['Weekend Trips',    'Weekend Trip',    '1513635269975-59663e0ac1ad', 'Tower Bridge and the London skyline at dusk','Find Out How Much You Could Save on a Weekend Trip',   'weekend-trips-card',    'weekend-trip'],
        ];
        foreach ($cats as $i => [$label, $interest, $pid, $alt, $heading, $source, $slug]):
      ?>
      <a href="#get-started"
         data-travel-interest="<?= htmlspecialchars($interest, ENT_QUOTES, 'UTF-8') ?>"
         data-form-heading="<?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>"
         data-lead-source="<?= $source ?>"
         data-trip-slug="<?= $slug ?>"
         class="cat-card reveal lift" data-delay="<?= $i ?>"
         aria-label="<?= $label ?> — jump to sign-up">
        <div class="media">
          <img src="https://images.unsplash.com/photo-<?= $pid ?>?auto=format&fit=crop&w=800&q=80"
               alt="<?= $alt ?>" loading="lazy" width="600" height="800">
          <div class="scrim"></div>
          <div class="label">
            <span class="tag"></span>
            <h3 class="font-display text-xl font-semibold leading-tight"><?= $label ?></h3>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 3c — POPULAR TRIPS MEMBERS SEARCH FOR (editorial)
     ========================================================= -->
<section id="trips" class="sec-paper py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-ink" style="text-wrap:balance;">Popular Trips Members Search For</h2>
      <p class="lede mt-4 text-lg">Chances are the trip you're already dreaming about is right here.</p>
    </div>

    <div class="mt-14 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-3">
      <?php
        // [name, description, dropdown interest, photo id, alt, personalised heading, lead_source, url slug]
        $trips = [
          ['Orlando Family Vacation', 'Where family memories are made',          'Family Vacation',            '1597466599360-3b9775841aec', 'The Orlando theme-park castle under a blue sky',     'Find Out How Much You Could Save on an Orlando Family Vacation', 'orlando-family-card',   'orlando-family'],
          ['Caribbean Cruise',        'Sun, sea, and savings on the open water', 'Cruise',                     '1548574505-5e239809ee19',   'Cruise ships docked at a turquoise Caribbean port',  'Find Out How Much You Could Save on a Caribbean Cruise',         'caribbean-cruise-card', 'caribbean-cruise'],
          ['Las Vegas Weekend',       'The ultimate long weekend escape',        'Weekend Trip',               '1581351721010-8cf859cb14a4', 'The Las Vegas Strip lit up at night',                'Find Out How Much You Could Save on a Las Vegas Weekend',        'las-vegas-card',        'las-vegas'],
          ['Cancun Beach Getaway',    'White sand, clear water, member pricing', 'Beach Getaway',              '1519046904884-53103b34b206', 'Palm trees on a white-sand Cancun beach',            'Find Out How Much You Could Save on a Cancun Beach Getaway',     'cancun-beach-card',     'cancun'],
          ['New York City Break',     'The city that never stops surprising you','City Break',                 '1485871981521-5b1fd3805eee', 'The Manhattan skyline at golden hour',               'Find Out How Much You Could Save on a New York City Break',      'new-york-card',         'new-york'],
          ['Visiting Family Overseas','Getting there should not cost a fortune',  'Visiting Family or Friends', '1436491865332-7a61a109cc05', 'An aeroplane wing above the clouds',                 'Find Out How Much You Could Save on Your Next Visit Home',       'visiting-family-card',  'visiting-family'],
        ];
        foreach ($trips as $i => [$name, $desc, $interest, $pid, $alt, $heading, $source, $slug]):
      ?>
      <a href="#get-started"
         data-travel-interest="<?= htmlspecialchars($interest, ENT_QUOTES, 'UTF-8') ?>"
         data-form-heading="<?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>"
         data-lead-source="<?= $source ?>"
         data-trip-slug="<?= $slug ?>"
         class="trip-card card reveal lift" data-delay="<?= $i % 3 ?>">
        <div class="media">
          <img src="https://images.unsplash.com/photo-<?= $pid ?>?auto=format&fit=crop&w=900&q=80"
               alt="<?= $alt ?>" loading="lazy" width="800" height="600">
          <div class="scrim"></div>
          <div class="label">
            <h3 class="font-display text-base font-bold leading-tight sm:text-xl"><?= $name ?></h3>
          </div>
        </div>
        <p class="px-4 py-4 text-sm leading-relaxed text-ink-2 sm:px-6 sm:py-5 sm:text-base"><?= $desc ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 4 — WHAT YOU CAN BOOK (drenched navy, icon cards)
     ========================================================= -->
<section id="booking" class="sec-navy py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-white" style="text-wrap:balance;">Everything you can book through the portal</h2>
      <p class="mt-4 text-lg text-white/70">One membership, your whole trip covered. Tap a category to get started.</p>
    </div>

    <div class="mt-14 grid grid-cols-2 gap-4 sm:gap-6 lg:grid-cols-4">
      <?php
        // The real Vortex365 portal categories. [label, interest, heading, lead_source, slug, icon-paths, caption]
        $booking = [
          ['Hotels', 'All of the Above', 'Find Out How Much You Could Save on Hotels', 'hotels-card', 'hotels',
            'M3 21h18M6 21V5a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v16M10 9h.01M14 9h.01M10 13h.01M14 13h.01M10 17h4', 'Member rates on stays worldwide.'],
          ['Cruises', 'Cruise', 'Find Out How Much You Could Save on a Cruise', 'cruises-card', 'cruise',
            'M12 3v6M5 9h14l-1.5 6H6.5L5 9zM4 19c1.4 1 2.9 1 4 0s2.6-1 4 0 2.9 1 4 0 2.6-1 4 0', 'Sail for less than the cruise lines quote.'],
          ['Cars', 'All of the Above', 'Find Out How Much You Could Save on Car Rentals', 'cars-card', 'cars',
            'M5 13l1.5-4.5A2 2 0 0 1 8.4 7h7.2a2 2 0 0 1 1.9 1.5L19 13M5 13h14v4a1 1 0 0 1-1 1h-1a1 1 0 0 1-1-1M5 13v4a1 1 0 0 0 1 1h1a1 1 0 0 0 1-1M7.5 16h.01M16.5 16h.01', 'Rental cars at your destination for less.'],
          ['Flights', 'All of the Above', 'Find Out How Much You Could Save on Flights', 'flights-card', 'flights',
            'M21 15v-2l-8-5V3.5a1.5 1.5 0 0 0-3 0V8l-8 5v2l8-2.5V18l-2 1.5V21l3.5-1 3.5 1v-1.5L13 18v-5.5z', "Fly for less, wherever you're headed."],
          ['Homes', 'All of the Above', 'Find Out How Much You Could Save on Holiday Homes', 'homes-card', 'homes',
            'M3 11.5 12 4l9 7.5M5 10v10h5v-6h4v6h5V10', 'Private homes and condos at member prices.'],
          ['Weeks', 'All of the Above', 'Find Out How Much You Could Save on Resort Weeks', 'weeks-card', 'weeks',
            'M7 3v3M17 3v3M5 6h14a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1zM4 10h16M8 14h3v3H8z', 'Full resort weeks at deep discounts.'],
          ['Activities', 'All of the Above', 'Find Out How Much You Could Save on Activities', 'activities-card', 'activities',
            'M4 8a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2 2 2 0 0 0 0 4 2 2 0 0 1-2 2H6a2 2 0 0 1-2-2 2 2 0 0 0 0-4zM15 6v12', 'Tours, excursions and experiences.'],
          ['Lifestyle', 'All of the Above', 'Find Out How Much You Could Save on Lifestyle Perks', 'lifestyle-card', 'lifestyle',
            'M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8zM18.5 15l.7 2 2 .7-2 .7-.7 2-.7-2-2-.7 2-.7z', 'Airport lounges, transport and transfers.'],
        ];
        foreach ($booking as $i => [$label, $interest, $heading, $source, $slug, $icon, $caption]):
      ?>
      <a href="#get-started"
         data-travel-interest="<?= htmlspecialchars($interest, ENT_QUOTES, 'UTF-8') ?>"
         data-form-heading="<?= htmlspecialchars($heading, ENT_QUOTES, 'UTF-8') ?>"
         data-lead-source="<?= $source ?>"
         data-trip-slug="<?= $slug ?>"
         class="book-card is-link reveal" data-delay="<?= $i % 3 ?>"
         aria-label="<?= $label ?> — jump to sign-up">
        <span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="<?= $icon ?>"/></svg></span>
        <h3><?= $label ?></h3>
        <p><?= $caption ?></p>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 5 — WHY THIS BEATS THE REST (light)
     ========================================================= -->
<section id="why" class="sec-paper py-24 sm:py-28">
  <div class="mx-auto max-w-5xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-ink" style="text-wrap:balance;">Why this beats the rest</h2>
      <p class="lede mt-4 text-lg">The same trips. A members-only price. See how the portal compares to the platforms you already use.</p>
    </div>

    <div class="reveal card mt-14 overflow-x-auto p-2 sm:p-4" data-delay="1">
      <table class="cmp min-w-[640px]">
        <thead>
          <tr>
            <th>Feature</th>
            <th class="win-col">TravelWithNaomi<br><span class="text-xs font-medium opacity-80">Vortex365</span></th>
            <th>Expedia</th>
            <th>Booking.com</th>
          </tr>
        </thead>
        <tbody>
          <?php
            $cell = function ($val) {
              if ($val === 'tick')  return '<span class="tick" aria-label="Yes">✓</span>';
              if ($val === 'cross') return '<span class="cross" aria-label="No">✕</span>';
              return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            };
            $rows = [
              ['Price competitiveness', 'Beats them up to 90%', 'Standard rates', 'Standard rates'],
              ['Membership fee',        'Free',                'Free',           'Free'],
              ['Exclusive member access','tick',               'cross',          'cross'],
              ['Personal support from Naomi','tick',            'cross',          'cross'],
              ['Savings guarantee',     'tick',                 'cross',          'cross'],
            ];
            foreach ($rows as [$feat, $v, $e, $b]):
          ?>
          <tr>
            <td><?= htmlspecialchars($feat, ENT_QUOTES, 'UTF-8') ?></td>
            <td class="win-col win-strong"><?= $cell($v) ?></td>
            <td><?= $cell($e) ?></td>
            <td><?= $cell($b) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="reveal mt-5 text-center text-xs text-ink-3">Comparison reflects typical member savings. Actual results vary by route, dates and availability.</p>
  </div>
</section>

<!-- =========================================================
     SECTION 6 — TESTIMONIALS (light)
     ========================================================= -->
<section class="sec-mist edge-top py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-ink" style="text-wrap:balance;">Real members, real savings</h2>
      <p class="lede mt-4 text-lg">A few of the people who stopped overpaying after joining through Naomi.</p>
    </div>

    <!-- Carousel: desktop shows all three (grid); mobile shows one at a time
         with swipe, dots and a 7s auto-rotate (paused on touch / reduced-motion). -->
    <div class="t-carousel mt-14" data-carousel>
      <div class="t-track" id="t-track">
      <?php
        $quotes = [
          ['We booked our Orlando family trip for the kids and saved enough to add two extra park days. I kept refreshing Expedia in disbelief.', 'Danielle M.', 'Atlanta, USA'],
          ['Our Caribbean cruise came in hundreds below what the cruise site quoted us. Same cabin, same dates. We are already planning the next one.', 'Marcus & Lena', 'Manchester, UK'],
          ['Solo flights to Europe are usually brutal. Through the portal I paid less than the budget airline was asking. Naomi walked me through it personally.', 'Priya S.', 'Toronto, Canada'],
        ];
        foreach ($quotes as $i => [$text, $name, $place]):
      ?>
      <figure class="t-slide reveal card lift quote flex flex-col p-5 sm:p-7" data-delay="<?= $i ?>">
        <div class="stars mb-3 flex gap-0.5 sm:gap-1" aria-label="5 out of 5 stars">
          <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="h-4 w-4 sm:h-5 sm:w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.45 4.45a1 1 0 00.95.69h4.68c.97 0 1.37 1.24.59 1.81l-3.79 2.75a1 1 0 00-.36 1.12l1.45 4.45c.3.92-.76 1.69-1.54 1.12l-3.79-2.75a1 1 0 00-1.18 0l-3.79 2.75c-.78.57-1.84-.2-1.54-1.12l1.45-4.45a1 1 0 00-.36-1.12L1.33 9.88c-.78-.57-.38-1.81.59-1.81h4.68a1 1 0 00.95-.69L9.05 2.93z"/></svg>
          <?php endfor; ?>
        </div>
        <blockquote class="relative z-10 grow text-sm leading-relaxed text-ink-2 sm:text-[0.98rem]">“<?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>”</blockquote>
        <figcaption class="mt-4 border-t border-line pt-3 sm:mt-6 sm:pt-4">
          <span class="block text-sm font-semibold text-ink sm:text-base"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="text-xs text-ink-3 sm:text-sm"><?= htmlspecialchars($place, ENT_QUOTES, 'UTF-8') ?></span>
        </figcaption>
      </figure>
      <?php endforeach; ?>
      </div>
      <div class="t-dots md:hidden" id="t-dots" role="tablist" aria-label="Choose testimonial"></div>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 6b — HOW IT WORKS (light) — moved below testimonials
     ========================================================= -->
<section id="how" class="sec-paper edge-top py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-ink" style="text-wrap:balance;">Saving starts in three simple steps</h2>
      <p class="lede mt-4 text-lg">From sign-up to your first cheaper trip, the whole thing takes about two minutes.</p>
    </div>

    <div class="steps mt-16 grid gap-12 md:grid-cols-3 md:gap-8">
      <?php
        $steps = [
          ['1', 'Sign Up Free', "Create your account through Naomi's personal link. No card, no fee, no catch."],
          ['2', 'Search Any Trip', 'Flights, hotels and car rentals worldwide, all in one members-only portal.'],
          ['3', 'Save Instantly', 'See your member price and book for less than you would pay anywhere else.'],
        ];
        foreach ($steps as $i => [$n, $title, $desc]):
      ?>
      <div class="reveal relative z-10 text-center" data-delay="<?= $i ?>">
        <div class="step-badge mx-auto"><?= $n ?></div>
        <h3 class="mt-6 font-display text-xl font-semibold text-ink"><?= $title ?></h3>
        <p class="mx-auto mt-3 max-w-xs leading-relaxed text-ink-2"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 7 — ABOUT NAOMI (light)
     ========================================================= -->
<section id="about" class="sec-paper py-24 sm:py-28">
  <div class="mx-auto grid max-w-6xl items-center gap-12 px-5 sm:px-8 md:grid-cols-2 md:gap-16">
    <div class="reveal order-1 mx-auto w-full max-w-sm md:order-none">
      <div class="glow-frame overflow-hidden">
        <img
          src="assets/naomi.jpg"
          alt="Naomi, your TravelWithNaomi Vortex365 ambassador"
          class="h-full w-full object-cover"
          width="640" height="800" loading="lazy"
          onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=80';"
        >
      </div>
    </div>

    <div class="reveal" data-delay="1">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight text-ink">Hi, I'm Naomi</h2>
      <p class="mt-6 text-lg leading-relaxed text-ink-2">
        I discovered Vortex365 and couldn't keep it to myself. Whether you travel for work,
        family or adventure, you deserve to stop overpaying. I'm here personally to help you
        get access and start saving today.
      </p>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 8 — LEAD CAPTURE CTA (compact card → opens modal)
     ========================================================= -->
<section id="get-started" class="sec-navy py-20 sm:py-24">
  <div class="mx-auto max-w-xl px-5 sm:px-8">
    <div class="reveal rounded-3xl border border-gold/40 bg-navy-800/70 px-7 py-10 text-center shadow-[0_40px_90px_-40px_rgba(0,0,0,.9)] sm:px-12 sm:py-12">
      <h2 class="font-display text-[clamp(1.8rem,4.5vw,2.6rem)] font-bold leading-tight text-white" style="text-wrap:balance;">Find Out How Much You Could Save</h2>
      <p class="mx-auto mt-3 max-w-md text-white/70">Tell me where you want to go and see how member pricing could make your next trip more affordable.</p>
      <button type="button" data-open-modal class="btn btn-gold mt-7 px-9 py-4 text-base sm:text-lg">Get My Free Access</button>
      <p class="mt-4 text-xs text-white/55">Free to join · No credit card required · Takes 2 minutes</p>
    </div>
  </div>
</section>

</main>

<!-- =========================================================
     SECTION 10 — FOOTER
     ========================================================= -->
<footer class="bg-navy-900 py-14 text-white">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="flex flex-col items-center gap-8 md:flex-row md:items-start md:justify-between">
      <div class="text-center md:text-left">
        <a href="#top" class="font-display text-xl font-bold">Travel<span class="text-gold">With</span>Naomi</a>
        <p class="mt-3 max-w-xs text-sm text-white/55">Members-only travel savings, shared personally by Naomi.</p>
      </div>

      <nav class="flex gap-7 text-sm text-white/70">
        <a href="#" class="transition hover:text-gold">Privacy</a>
        <a href="#how" class="transition hover:text-gold">How It Works</a>
        <a href="#get-started" class="transition hover:text-gold">Get Started</a>
      </nav>

      <!-- Social links — replace [MY_SOCIAL_LINKS] -->
      <div class="flex gap-4">
        <a href="<?= htmlspecialchars($INSTAGRAM, ENT_QUOTES, 'UTF-8') ?>" aria-label="Instagram" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-white/70 transition hover:border-gold hover:text-gold">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.43.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.43.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.7 3.7 0 01-1.38-.9 3.7 3.7 0 01-.9-1.38c-.16-.43-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.43-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16zm0 1.62c-3.15 0-3.5.01-4.74.07-1.14.05-1.76.24-2.17.4-.55.21-.94.47-1.35.88-.41.41-.67.8-.88 1.35-.16.41-.35 1.03-.4 2.17-.06 1.24-.07 1.59-.07 4.74s.01 3.5.07 4.74c.05 1.14.24 1.76.4 2.17.21.55.47.94.88 1.35.41.41.8.67 1.35.88.41.16 1.03.35 2.17.4 1.24.06 1.59.07 4.74.07s3.5-.01 4.74-.07c1.14-.05 1.76-.24 2.17-.4.55-.21.94-.47 1.35-.88.41-.41.67-.8.88-1.35.16-.41.35-1.03.4-2.17.06-1.24.07-1.59.07-4.74s-.01-3.5-.07-4.74c-.05-1.14-.24-1.76-.4-2.17a3.6 3.6 0 00-.88-1.35 3.6 3.6 0 00-1.35-.88c-.41-.16-1.03-.35-2.17-.4-1.24-.06-1.59-.07-4.74-.07zm0 2.76a5.46 5.46 0 110 10.92 5.46 5.46 0 010-10.92zm0 9a3.54 3.54 0 100-7.08 3.54 3.54 0 000 7.08zm6.95-9.22a1.27 1.27 0 11-2.55 0 1.27 1.27 0 012.55 0z"/></svg>
        </a>
        <a href="<?= htmlspecialchars($FACEBOOK, ENT_QUOTES, 'UTF-8') ?>" aria-label="Facebook" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-white/70 transition hover:border-gold hover:text-gold">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.69.24 2.69.24v2.97h-1.52c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07z"/></svg>
        </a>
        <a href="<?= htmlspecialchars($TIKTOK, ENT_QUOTES, 'UTF-8') ?>" aria-label="TikTok" class="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-white/70 transition hover:border-gold hover:text-gold">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19.59 6.69a4.83 4.83 0 01-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 01-5.2 1.74 2.89 2.89 0 012.89-4.51c.3 0 .59.05.86.13V9.4a6.33 6.33 0 00-1-.05A6.34 6.34 0 005.36 20.5a6.34 6.34 0 0010.86-4.43v-6.9a8.16 8.16 0 004.77 1.52v-3.4a4.85 4.85 0 01-1.4-.6z"/></svg>
        </a>
      </div>
    </div>

    <div class="mt-12 border-t border-white/10 pt-8 text-center">
      <p class="mx-auto max-w-2xl text-xs leading-relaxed text-white/45">
        Naomi is an independent Vortex365 member. This site is not affiliated with or endorsed by Surge365 corporate.
      </p>
      <p class="mt-4 text-xs text-white/40">© 2026 TravelWithNaomi. All rights reserved.</p>
    </div>
  </div>
</footer>

<!-- =========================================================
     LEAD CAPTURE MODAL (holds the real form; opened by the CTA
     and every mapped card via selectTravelIntent / openModal)
     ========================================================= -->
<div class="modal" id="lead-modal" role="dialog" aria-modal="true" aria-labelledby="form-heading">
  <div class="modal-backdrop" data-close-modal></div>
  <div class="modal-dialog" role="document">
    <button type="button" class="modal-close" data-close-modal aria-label="Close">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>

    <form
      action="submit.php" method="post" novalidate
      x-data="leadForm()" @submit.prevent="handle($event)"
    >
      <div class="text-center">
        <h2 id="form-heading" class="font-display text-[clamp(1.6rem,4.5vw,2.3rem)] font-bold leading-tight text-white" style="text-wrap:balance;">Find Out How Much You Could Save</h2>
        <p class="mt-3 text-sm text-white/70 sm:text-base">Fill in your details below and I will personally send you through to your members portal.</p>
      </div>

      <?php if ($serverError): ?>
        <p id="server-error" class="mt-6 rounded-xl border border-red-400/50 bg-red-500/15 px-4 py-3 text-center text-sm text-red-200">
          Something looked off with your details. Please check and try again.
        </p>
      <?php endif; ?>

      <!-- Tracking fields. lead_source defaults to "direct-form" and is updated
           by selectTravelIntent() on any card click. UTM fields are filled on
           page load from sessionStorage (see scripts at the foot of the page). -->
      <input type="hidden" name="lead_source" id="lead_source" value="direct-form">
      <input type="hidden" name="utm_source"   id="utm_source">
      <input type="hidden" name="utm_medium"   id="utm_medium">
      <input type="hidden" name="utm_campaign" id="utm_campaign">
      <input type="hidden" name="utm_content"  id="utm_content">
      <input type="hidden" name="utm_term"     id="utm_term">

      <!-- Two columns on desktop, single column on mobile -->
      <div class="mt-7 grid gap-4 sm:grid-cols-2 sm:gap-5">
        <div>
          <label for="full_name" class="mb-1.5 block text-sm font-medium text-white/80">Full name</label>
          <input id="full_name" name="full_name" type="text" autocomplete="name"
                 class="input-base" placeholder="Jane Doe"
                 x-model="f.full_name" @blur="touch('full_name')" :class="cls('full_name')">
          <p class="field-msg" x-text="errs.full_name"></p>
        </div>

        <div>
          <label for="email" class="mb-1.5 block text-sm font-medium text-white/80">Email address</label>
          <input id="email" name="email" type="email" autocomplete="email"
                 class="input-base" placeholder="jane@email.com"
                 x-model="f.email" @blur="touch('email')" :class="cls('email')">
          <p class="field-msg" x-text="errs.email"></p>
        </div>

        <div>
          <label for="whatsapp" class="mb-1.5 block text-sm font-medium text-white/80">WhatsApp number</label>
          <input id="whatsapp" name="whatsapp" type="tel" autocomplete="tel"
                 class="input-base" placeholder="+1 555 123 4567"
                 x-model="f.whatsapp" @blur="touch('whatsapp')" :class="cls('whatsapp')">
          <p class="field-msg" x-text="errs.whatsapp"></p>
        </div>

        <div>
          <label for="country" class="mb-1.5 block text-sm font-medium text-white/80">Country</label>
          <input id="country" name="country" type="text" autocomplete="country-name"
                 class="input-base" placeholder="United States"
                 x-model="f.country" @blur="touch('country')" :class="cls('country')">
          <p class="field-msg" x-text="errs.country"></p>
        </div>

        <div class="sm:col-span-2">
          <label for="travel_interest" class="mb-1.5 block text-sm font-medium text-white/80">Travel interest</label>
          <select id="travel_interest" name="travel_interest"
                  class="input-base appearance-none" x-model="f.travel_interest"
                  @change="touch('travel_interest')" :class="cls('travel_interest')">
            <option value="" disabled>Choose one…</option>
            <option value="Family Vacation">Family Vacation</option>
            <option value="Beach Getaway">Beach Getaway</option>
            <option value="Cruise">Cruise</option>
            <option value="City Break">City Break</option>
            <option value="Weekend Trip">Weekend Trip</option>
            <option value="Honeymoon or Anniversary">Honeymoon or Anniversary</option>
            <option value="Visiting Family or Friends">Visiting Family or Friends</option>
            <option value="All of the Above">All of the Above</option>
          </select>
          <p class="field-msg" x-text="errs.travel_interest"></p>
        </div>
      </div>

      <button type="submit" class="btn btn-gold mt-7 w-full px-8 py-4 text-base sm:text-lg">
        Claim My Free Access
      </button>
      <p class="mt-4 text-center text-xs text-white/55">No credit card required · Your details stay private · Takes 2 minutes</p>
    </form>
  </div>
</div>

<!-- =========================================================
     SUCCESS OVERLAY (shown on form submit, before redirect)
     ========================================================= -->
<div class="overlay" id="success-overlay" role="status" aria-live="polite">
  <div>
    <div class="ring"></div>
    <h2 class="font-display text-2xl font-bold text-gold sm:text-3xl">Perfect! Taking you to your free access now…</h2>
    <p class="mt-3 text-white/70">Hold tight, your members portal is loading.</p>
  </div>
</div>

<!-- =========================================================
     SCRIPTS — reveal, counters, particles, form logic
     (vanilla JS + Alpine; no animation libraries)
     ========================================================= -->
<script>
  /* ---- Scroll reveal via IntersectionObserver ---- */
  (function () {
    var els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('in-view'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) { e.target.classList.add('in-view'); io.unobserve(e.target); }
      });
    }, { threshold: 0.12, rootMargin: '0px 0px -8% 0px' });
    els.forEach(function (el) { io.observe(el); });
    window.addEventListener('load', function () {
      setTimeout(function () {
        els.forEach(function (el) {
          var r = el.getBoundingClientRect();
          if (r.top < window.innerHeight) el.classList.add('in-view');
        });
      }, 600);
    });
  })();

  /* ---- Count-up stat counters (ease-out-quart) ---- */
  (function () {
    var counters = document.querySelectorAll('.counter');
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    function run(el) {
      var target = parseInt(el.getAttribute('data-target'), 10) || 0;
      if (reduce) { el.textContent = target; return; }
      var start = null, dur = 1600;
      function step(ts) {
        if (!start) start = ts;
        var p = Math.min((ts - start) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 4);
        el.textContent = Math.floor(eased * target);
        if (p < 1) requestAnimationFrame(step); else el.textContent = target;
      }
      requestAnimationFrame(step);
    }
    if (!('IntersectionObserver' in window)) { counters.forEach(run); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { run(e.target); io.unobserve(e.target); } });
    }, { threshold: 0.6 });
    counters.forEach(function (c) { io.observe(c); });
  })();

  /* ---- Hero gold particles ---- */
  (function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    document.querySelectorAll('.particles').forEach(function (host) {
      var n = window.innerWidth < 640 ? 12 : 22;
      for (var i = 0; i < n; i++) {
        var p = document.createElement('i');
        p.style.left = Math.random() * 100 + '%';
        p.style.animationDuration = (8 + Math.random() * 10) + 's';
        p.style.animationDelay = (Math.random() * 10) + 's';
        var sz = 2 + Math.random() * 4;
        p.style.width = sz + 'px'; p.style.height = sz + 'px';
        host.appendChild(p);
      }
    });
  })();

  /* ---- Hero parallax + slow Ken Burns zoom (rAF only while hero visible) ---- */
  (function () {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
    var hero = document.querySelector('.hero');
    var photo = hero && hero.querySelector('.hero-photo');
    if (!photo) return;
    var running = false, t0 = performance.now();
    function frame(now) {
      if (!running) return;
      var sy = window.pageYOffset || document.documentElement.scrollTop || 0;
      var parallax = Math.min(Math.max(sy, 0) * 0.3, 80);            // ≤ 80px, subtle
      var kb = 1.06 + (Math.sin((now - t0) / 9000) + 1) * 0.03;       // slow 1.06 → 1.12 zoom
      photo.style.transform = 'translate3d(0,' + parallax.toFixed(1) + 'px,0) scale(' + kb.toFixed(4) + ')';
      requestAnimationFrame(frame);
    }
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (e) {
          if (e.isIntersecting && !running) { running = true; requestAnimationFrame(frame); }
          else if (!e.isIntersecting) { running = false; }
        });
      }, { threshold: 0 }).observe(hero);
    } else { running = true; requestAnimationFrame(frame); }
  })();

  /* ---- How It Works: grow the connecting line when it enters view ---- */
  (function () {
    var steps = document.querySelector('.steps');
    if (!steps) return;
    if (!('IntersectionObserver' in window)) { steps.classList.add('line-in'); return; }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) { if (e.isIntersecting) { steps.classList.add('line-in'); io.unobserve(steps); } });
    }, { threshold: 0.4 });
    io.observe(steps);
  })();

  /* ---- Testimonials carousel (mobile: 1-up swipe + dots + 7s auto-rotate) ---- */
  (function () {
    var reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var carousel = document.querySelector('[data-carousel]');
    if (!carousel) return;
    var track = carousel.querySelector('.t-track');
    var dotsWrap = carousel.querySelector('.t-dots');
    var n = track.children.length;
    var mq = window.matchMedia('(max-width: 767px)');
    var idx = 0, timer = null;

    for (var i = 0; i < n; i++) {
      var d = document.createElement('button');
      d.className = 't-dot' + (i === 0 ? ' active' : '');
      d.setAttribute('aria-label', 'Show testimonial ' + (i + 1));
      (function (k) { d.addEventListener('click', function () { go(k); restart(); }); })(i);
      dotsWrap.appendChild(d);
    }
    function render() {
      if (mq.matches) track.style.transform = 'translateX(' + (-idx * 100) + '%)';
      else track.style.transform = '';
      var dots = dotsWrap.children;
      for (var i = 0; i < dots.length; i++) dots[i].classList.toggle('active', i === idx);
    }
    function go(i) { idx = (i + n) % n; render(); }
    function next() { go(idx + 1); }
    function start() { if (reduce || !mq.matches) return; stop(); timer = setInterval(next, 7000); }
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function restart() { stop(); start(); }

    // Swipe (mobile)
    var x0 = null;
    track.addEventListener('touchstart', function (e) { x0 = e.touches[0].clientX; stop(); }, { passive: true });
    track.addEventListener('touchend', function (e) {
      if (x0 === null) return;
      var dx = e.changedTouches[0].clientX - x0;
      if (Math.abs(dx) > 40) { dx < 0 ? next() : go(idx - 1); }
      x0 = null; start();
    }, { passive: true });

    // Pause on hover; respect tab visibility; re-evaluate on breakpoint change.
    carousel.addEventListener('mouseenter', stop);
    carousel.addEventListener('mouseleave', start);
    document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
    var onMq = function () { idx = 0; render(); restart(); };
    if (mq.addEventListener) mq.addEventListener('change', onMq); else mq.addListener(onMq);

    render(); start();
  })();

  /* ---- Smart intent capture: lead source + UTM + dual-URL pre-fill ---- */
  (function () {
    // Slug → { interest, heading } for ?trip= and #hash pre-fill.
    var TRIP_MAP = {
      'family-vacation': { interest: 'Family Vacation',            heading: null },
      'beach-getaway':   { interest: 'Beach Getaway',             heading: null },
      'cruise':          { interest: 'Cruise',                    heading: null },
      'caribbean-cruise':{ interest: 'Cruise',                    heading: 'Find Out How Much You Could Save on a Caribbean Cruise' },
      'orlando-family':  { interest: 'Family Vacation',            heading: 'Find Out How Much You Could Save on an Orlando Family Vacation' },
      'las-vegas':       { interest: 'Weekend Trip',              heading: 'Find Out How Much You Could Save on a Las Vegas Weekend' },
      'cancun':          { interest: 'Beach Getaway',             heading: 'Find Out How Much You Could Save on a Cancun Beach Getaway' },
      'new-york':        { interest: 'City Break',                heading: 'Find Out How Much You Could Save on a New York City Break' },
      'visiting-family': { interest: 'Visiting Family or Friends', heading: 'Find Out How Much You Could Save on Your Next Visit Home' },
      'weekend-trip':    { interest: 'Weekend Trip',              heading: null },
      'city-break':      { interest: 'City Break',                heading: null },
      'hotels-resorts':  { interest: 'All of the Above',           heading: 'Find Out How Much You Could Save on Hotels and Resorts' },
      'car-rentals':     { interest: 'All of the Above',           heading: 'Find Out How Much You Could Save on Car Rentals' }
    };

    var prefersFine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    var reduce      = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function setInterest(value) {
      var sel = document.getElementById('travel_interest');
      if (!sel || !value) return;
      var exists = Array.prototype.some.call(sel.options, function (o) { return o.value === value; });
      if (!exists) return;
      sel.value = value;
      sel.dispatchEvent(new Event('change', { bubbles: true })); // keep Alpine x-model in sync
    }

    function pulseDropdown() {
      var sel = document.getElementById('travel_interest');
      if (!sel || reduce) return;
      sel.classList.remove('pulse-gold');
      void sel.offsetWidth; // restart the animation cleanly on re-click
      sel.classList.add('pulse-gold');
      sel.addEventListener('animationend', function h() {
        sel.classList.remove('pulse-gold');
        sel.removeEventListener('animationend', h);
      });
    }

    function setHeading(text, animate) {
      var h = document.getElementById('form-heading');
      if (!h || !text) return;
      if (!animate || reduce) { h.textContent = text; return; }
      h.style.opacity = '0';
      setTimeout(function () { h.textContent = text; h.style.opacity = '1'; }, 200);
    }

    // Stateless + re-entrant: every call produces a fresh pre-fill.
    function selectTravelIntent(interest, heading, leadSource, slug) {
      setInterest(interest);

      var src = document.getElementById('lead_source');
      if (src && leadSource) src.value = leadSource;

      setHeading(heading, true);
      pulseDropdown();

      // Update the URL with BOTH ?trip= and #hash, without jumping.
      if (slug) {
        try {
          var u = new URL(window.location.href);
          u.searchParams.set('trip', slug);
          u.hash = slug;
          history.replaceState(null, '', u.toString());
        } catch (e) {}
      }

      // Open the modal (replaces the old scroll-to-form behaviour).
      openModal();
    }
    window.selectTravelIntent = selectTravelIntent;

    /* ---- Modal open / close ---- */
    var modal = document.getElementById('lead-modal');
    var lastFocus = null;
    function focusables() {
      return modal ? modal.querySelectorAll('button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])') : [];
    }
    function openModal() {
      if (!modal || modal.classList.contains('show')) return;
      lastFocus = document.activeElement;
      modal.classList.remove('closing');
      modal.style.display = 'grid';
      document.body.classList.add('modal-open');
      modal.classList.add('show'); // dialog is visible by default; class plays the entrance
      // Desktop (fine pointer) only: focus Full Name after the entrance settles.
      if (prefersFine) {
        setTimeout(function () { var n = document.getElementById('full_name'); if (n) n.focus({ preventScroll: true }); }, reduce ? 0 : 280);
      }
    }
    function closeModal() {
      if (!modal || !modal.classList.contains('show')) return;
      modal.classList.remove('show');
      document.body.classList.remove('modal-open');
      if (!reduce) modal.classList.add('closing');
      // Timeout-based hide is robust even when animations are throttled.
      setTimeout(function () { modal.style.display = 'none'; modal.classList.remove('closing'); }, reduce ? 0 : 230);
      if (lastFocus && lastFocus.focus) { try { lastFocus.focus({ preventScroll: true }); } catch (e) {} }
    }
    window.openLeadModal = openModal;

    // Compact CTA button: generic open (reset to direct-form + default heading).
    document.querySelectorAll('[data-open-modal]').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var src = document.getElementById('lead_source'); if (src) src.value = 'direct-form';
        setHeading('Find Out How Much You Could Save', false);
        openModal();
      });
    });
    // Close controls: backdrop, ✕ button, ESC, and a basic focus trap.
    document.querySelectorAll('[data-close-modal]').forEach(function (el) { el.addEventListener('click', closeModal); });
    document.addEventListener('keydown', function (e) {
      if (!modal || !modal.classList.contains('show')) return;
      if (e.key === 'Escape') { closeModal(); return; }
      if (e.key === 'Tab') {
        var f = focusables(); if (!f.length) return;
        var first = f[0], last = f[f.length - 1];
        if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
        else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
      }
    });

    // Wire every clickable card (destination, popular trips, booking categories).
    document.querySelectorAll('[data-travel-interest]').forEach(function (card) {
      card.addEventListener('click', function (e) {
        e.preventDefault();
        selectTravelIntent(
          card.getAttribute('data-travel-interest'),
          card.getAttribute('data-form-heading'),
          card.getAttribute('data-lead-source'),
          card.getAttribute('data-trip-slug')
        );
      });
    });

    // --- UTM capture: persist to sessionStorage, populate hidden fields ---
    (function () {
      var keys = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'];
      var params = new URLSearchParams(window.location.search);
      keys.forEach(function (k) {
        var v = params.get(k);
        if (v) { try { sessionStorage.setItem(k, v); } catch (e) {} }
        var stored = null;
        try { stored = sessionStorage.getItem(k); } catch (e) {}
        var field = document.getElementById(k);
        if (field && stored) field.value = stored;
      });
    })();

    // --- Dual-URL pre-fill (?trip= first, else #hash). Silent: dropdown + heading only.
    // Runs after Alpine initialises so x-model doesn't reset the <select>.
    function applyUrlPrefill() {
      var params = new URLSearchParams(window.location.search);
      var slug = params.get('trip');
      if (!slug && window.location.hash) slug = window.location.hash.replace(/^#/, '');
      if (!slug) return;
      var map = TRIP_MAP[slug.toLowerCase()];
      if (!map) return;
      setInterest(map.interest);
      if (map.heading) setHeading(map.heading, false);
    }
    function onReady() {
      applyUrlPrefill();
      // If server-side validation bounced the visitor back, reopen the modal so
      // they see the error and their (now hidden) form.
      if (document.getElementById('server-error')) openModal();
    }
    if (window.Alpine) {
      onReady();
    } else {
      document.addEventListener('alpine:initialized', onReady, { once: true });
      // Fallback if Alpine never loads.
      window.addEventListener('load', function () { setTimeout(onReady, 300); });
    }
  })();

  /* ---- Alpine form component: real-time validation + overlay ---- */
  function leadForm() {
    return {
      f: { full_name: '', email: '', whatsapp: '', country: '', travel_interest: '' },
      errs: {}, touched: {},
      rules: {
        full_name: function (v) { return v.trim().length >= 2 ? '' : 'Please enter your full name.'; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? '' : 'Enter a valid email address.'; },
        whatsapp: function (v) { var d = v.replace(/\D/g, ''); return d.length >= 7 ? '' : 'Enter a valid WhatsApp number.'; },
        country: function (v) { return v.trim().length >= 2 ? '' : 'Please enter your country.'; },
        travel_interest: function (v) { return v ? '' : 'Please choose a travel interest.'; }
      },
      validateField: function (k) { this.errs[k] = this.rules[k](this.f[k]); return !this.errs[k]; },
      touch: function (k) { this.touched[k] = true; this.validateField(k); },
      cls: function (k) { if (!this.touched[k]) return ''; return this.errs[k] ? 'invalid' : 'valid'; },
      handle: function (ev) {
        var ok = true;
        for (var k in this.rules) { this.touched[k] = true; if (!this.validateField(k)) ok = false; }
        if (!ok) {
          for (var key in this.rules) { if (this.errs[key]) { var el = document.getElementById(key); if (el) el.focus(); break; } }
          return;
        }
        document.getElementById('success-overlay').classList.add('show');
        setTimeout(function () { ev.target.submit(); }, 400);
      }
    };
  }
</script>

<style>[x-cloak]{display:none!important;}</style>
</body>
</html>
