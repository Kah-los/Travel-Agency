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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TravelWithNaomi — Stop Overpaying for Travel</title>
  <meta name="description" content="Naomi Henry gives you free access to a members-only travel portal that beats Expedia and Booking.com up to 90% of the time. Free to join, no credit card.">
  <meta name="theme-color" content="#0B1437">

  <!-- Open Graph -->
  <meta property="og:title" content="TravelWithNaomi — Stop Overpaying for Travel">
  <meta property="og:description" content="A members-only travel portal that beats the major platforms up to 90% of the time. Free to join.">
  <meta property="og:type" content="website">

  <!-- Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700;800&display=swap" rel="stylesheet">

  <!-- Tailwind (CDN) + brand config -->
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            navy:  { DEFAULT: '#0B1437', 900: '#070d24', 800: '#101a44', 700: '#16224f', 600: '#1d2b60' },
            gold:  { DEFAULT: '#C9A84C', light: '#E4C97B', dark: '#A88A38' }
          },
          fontFamily: {
            display: ['"Playfair Display"', 'Georgia', 'serif'],
            body:    ['Inter', 'system-ui', 'sans-serif']
          }
        }
      }
    };
  </script>

  <!-- Alpine (CDN) for nav + form interactivity -->
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

  <!-- Custom brand styles -->
  <link rel="stylesheet" href="assets/style.css">

  <!-- Add `js` to <html> so reveal styles only apply when JS is on -->
  <script>document.documentElement.classList.add('js');</script>
</head>

<body class="bg-navy text-white font-body antialiased selection:bg-gold/30">

<!-- =========================================================
     SECTION 1 — NAVIGATION
     ========================================================= -->
<header
  x-data="{ open: false, scrolled: false }"
  x-init="window.addEventListener('scroll', () => scrolled = window.scrollY > 24)"
  :class="scrolled ? 'bg-navy-900/90 shadow-[0_8px_30px_-12px_rgba(0,0,0,.8)] backdrop-blur' : 'bg-transparent'"
  class="fixed inset-x-0 top-0 z-[100] transition-colors duration-300"
>
  <nav class="mx-auto flex max-w-6xl items-center justify-between px-5 py-4 sm:px-8">
    <a href="#top" class="font-display text-xl font-bold tracking-tight sm:text-2xl">
      Travel<span class="text-gold">With</span>Naomi
    </a>

    <!-- Desktop links -->
    <div class="hidden items-center gap-8 md:flex">
      <a href="#how" class="text-sm text-white/80 transition hover:text-gold">How It Works</a>
      <a href="#why" class="text-sm text-white/80 transition hover:text-gold">Why Vortex</a>
      <a href="#about" class="text-sm text-white/80 transition hover:text-gold">About Naomi</a>
      <a href="#get-started" class="btn-gold px-6 py-2.5 text-sm">Get Started</a>
    </div>

    <!-- Hamburger -->
    <button @click="open = !open" class="md:hidden" :aria-expanded="open" aria-label="Toggle menu">
      <svg x-show="!open" xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
      <svg x-show="open" x-cloak xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 text-gold" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
  </nav>

  <!-- Mobile menu -->
  <div x-show="open" x-cloak x-transition.origin.top
       class="border-t border-white/10 bg-navy-900/95 px-5 py-4 backdrop-blur md:hidden">
    <a @click="open=false" href="#how" class="block py-3 text-white/85">How It Works</a>
    <a @click="open=false" href="#why" class="block py-3 text-white/85">Why Vortex</a>
    <a @click="open=false" href="#about" class="block py-3 text-white/85">About Naomi</a>
    <a @click="open=false" href="#get-started" class="btn-gold mt-2 block px-6 py-3 text-center">Get Started</a>
  </div>
</header>

<main id="top">

<!-- =========================================================
     SECTION 2 — HERO
     ========================================================= -->
<section class="hero-bg relative flex min-h-[100svh] items-center overflow-hidden pt-24">
  <!-- floating destination names (gold, subtle) -->
  <div class="float-words" aria-hidden="true">
    <span style="top:18%; left:8%;  animation-duration:17s; animation-delay:0s;">Santorini</span>
    <span style="top:62%; left:14%; animation-duration:21s; animation-delay:3s;">Maldives</span>
    <span style="top:30%; right:10%;animation-duration:19s; animation-delay:6s;">Bali</span>
    <span style="top:74%; right:16%;animation-duration:23s; animation-delay:1.5s;">Paris</span>
    <span style="top:44%; left:46%; animation-duration:25s; animation-delay:8s;">Cape Town</span>
    <span style="top:12%; right:34%;animation-duration:20s; animation-delay:4.5s;">Tokyo</span>
  </div>
  <!-- drifting gold particles (generated by JS) -->
  <div class="particles" id="particles" aria-hidden="true"></div>

  <div class="relative z-10 mx-auto max-w-4xl px-5 text-center sm:px-8">
    <p class="reveal mb-5 text-sm font-medium tracking-wide text-gold/90">Hosted personally by Naomi Henry · Vortex365 Ambassador</p>
    <h1 class="reveal font-display text-[clamp(2.6rem,8vw,5.25rem)] font-bold leading-[1.04] tracking-tight" style="text-wrap:balance;">
      Stop Overpaying<br>for Travel
    </h1>
    <p class="reveal mx-auto mt-7 max-w-2xl text-lg leading-relaxed text-white/80 sm:text-xl" data-delay="1">
      Naomi Henry gives you access to a members-only travel portal that beats Expedia
      and Booking.com up to 90% of the time, completely free to join.
    </p>
    <div class="reveal mt-9 flex flex-col items-center gap-4" data-delay="2">
      <a href="<?= $ref ?>" class="btn-gold px-10 py-4 text-base sm:text-lg">Get My Free Access</a>
      <p class="text-sm text-white/55">No credit card required · Free to join · Takes 2 minutes</p>
    </div>
  </div>

  <!-- scroll cue -->
  <a href="#trust" class="absolute bottom-6 left-1/2 -translate-x-1/2 text-gold/70 transition hover:text-gold" aria-label="Scroll down">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.6"><path stroke-linecap="round" stroke-linejoin="round" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
  </a>
</section>

<!-- =========================================================
     SECTION 3 — TRUST BAR (count-up stats)
     ========================================================= -->
<section id="trust" class="bg-navy-900 py-16 sm:py-20">
  <div class="mx-auto grid max-w-6xl gap-6 px-5 sm:px-8 md:grid-cols-3" id="stats-row">
    <?php
      $stats = [
        ['90', '%', '85–90% beat rate vs major platforms'],
        ['100', '%', 'Free to create your account'],
        ['1000', '+', 'Members saving on travel daily'],
      ];
      foreach ($stats as $i => [$num, $suffix, $label]):
    ?>
    <div class="reveal lift rounded-2xl border border-gold/35 bg-navy-700/60 px-7 py-9 text-center" data-delay="<?= $i ?>">
      <div class="font-display text-5xl font-bold text-gold">
        <span class="counter" data-target="<?= $num ?>">0</span><?= $suffix ?>
      </div>
      <p class="mx-auto mt-3 max-w-[15rem] text-sm leading-relaxed text-white/75"><?= $label ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- =========================================================
     SECTION 4 — HOW IT WORKS
     ========================================================= -->
<section id="how" class="bg-navy py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight" style="text-wrap:balance;">Saving starts in three simple steps</h2>
      <p class="mt-4 text-white/70">From sign-up to your first cheaper trip, the whole thing takes about two minutes.</p>
    </div>

    <div class="steps-line mt-16 grid gap-12 md:grid-cols-3 md:gap-8">
      <?php
        $steps = [
          ['1', 'Sign Up Free', "Create your account through Naomi's personal link. No card, no fee, no catch."],
          ['2', 'Search Any Trip', 'Flights, hotels and car rentals worldwide, all in one members-only portal.'],
          ['3', 'Save Instantly', 'See your member price and book for less than you would pay anywhere else.'],
        ];
        foreach ($steps as $i => [$n, $title, $desc]):
      ?>
      <div class="reveal relative z-10 text-center" data-delay="<?= $i ?>">
        <div class="mx-auto flex h-[76px] w-[76px] items-center justify-center rounded-full border border-gold/40 bg-navy-800 font-display text-3xl font-bold text-gold shadow-[0_0_40px_-10px_rgba(201,168,76,.6)]">
          <?= $n ?>
        </div>
        <h3 class="mt-6 font-display text-xl font-semibold"><?= $title ?></h3>
        <p class="mx-auto mt-3 max-w-xs text-sm leading-relaxed text-white/70"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 5 — WHY THIS BEATS THE REST (comparison)
     ========================================================= -->
<section id="why" class="bg-navy-900 py-24 sm:py-28">
  <div class="mx-auto max-w-5xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight" style="text-wrap:balance;">Why this beats the rest</h2>
      <p class="mt-4 text-white/70">The same trips. A members-only price. See how the portal compares to the platforms you already use.</p>
    </div>

    <div class="reveal mt-14 overflow-x-auto rounded-2xl border border-white/10 bg-navy-800/40 p-2 sm:p-4">
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
            <td class="font-medium text-white"><?= htmlspecialchars($feat, ENT_QUOTES, 'UTF-8') ?></td>
            <td class="win-col font-semibold"><?= $cell($v) ?></td>
            <td><?= $cell($e) ?></td>
            <td><?= $cell($b) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <p class="reveal mt-5 text-center text-xs text-white/45">Comparison reflects typical member savings. Actual results vary by route, dates and availability.</p>
  </div>
</section>

<!-- =========================================================
     SECTION 6 — TESTIMONIALS
     ========================================================= -->
<section class="bg-navy py-24 sm:py-28">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="reveal mx-auto max-w-2xl text-center">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight" style="text-wrap:balance;">Real members, real savings</h2>
      <p class="mt-4 text-white/70">A few of the people who stopped overpaying after joining through Naomi.</p>
    </div>

    <div class="mt-14 grid gap-7 md:grid-cols-3">
      <?php
        $quotes = [
          ['We booked our Orlando family trip for the kids and saved enough to add two extra park days. I kept refreshing Expedia in disbelief.', 'Danielle M.', 'Atlanta, USA'],
          ['Our Caribbean cruise came in hundreds below what the cruise site quoted us. Same cabin, same dates. We are already planning the next one.', 'Marcus & Lena', 'Manchester, UK'],
          ['Solo flights to Europe are usually brutal. Through the portal I paid less than the budget airline was asking. Naomi walked me through it personally.', 'Priya S.', 'Toronto, Canada'],
        ];
        foreach ($quotes as $i => [$text, $name, $place]):
      ?>
      <figure class="reveal lift relative rounded-2xl border border-white/10 border-l-[3px] border-l-gold bg-navy-800/60 p-7 shadow-[0_20px_50px_-30px_rgba(0,0,0,.8)]" data-delay="<?= $i ?>">
        <div class="mb-4 flex gap-1 text-gold" aria-label="5 out of 5 stars">
          <?php for ($s = 0; $s < 5; $s++): ?>
            <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.45 4.45a1 1 0 00.95.69h4.68c.97 0 1.37 1.24.59 1.81l-3.79 2.75a1 1 0 00-.36 1.12l1.45 4.45c.3.92-.76 1.69-1.54 1.12l-3.79-2.75a1 1 0 00-1.18 0l-3.79 2.75c-.78.57-1.84-.2-1.54-1.12l1.45-4.45a1 1 0 00-.36-1.12L1.33 9.88c-.78-.57-.38-1.81.59-1.81h4.68a1 1 0 00.95-.69L9.05 2.93z"/></svg>
          <?php endfor; ?>
        </div>
        <blockquote class="text-[0.98rem] leading-relaxed text-white/85">“<?= htmlspecialchars($text, ENT_QUOTES, 'UTF-8') ?>”</blockquote>
        <figcaption class="mt-6 border-t border-white/10 pt-4">
          <span class="block font-semibold text-white"><?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?></span>
          <span class="text-sm text-white/55"><?= htmlspecialchars($place, ENT_QUOTES, 'UTF-8') ?></span>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 7 — ABOUT NAOMI
     ========================================================= -->
<section id="about" class="bg-navy-900 py-24 sm:py-28">
  <div class="mx-auto grid max-w-6xl items-center gap-12 px-5 sm:px-8 md:grid-cols-2 md:gap-16">
    <!-- Photo (assets/naomi.jpg). Fallback keeps the section intact before upload. -->
    <div class="reveal order-1 mx-auto w-full max-w-sm md:order-none">
      <div class="glow-frame overflow-hidden rounded-3xl">
        <img
          src="assets/naomi.jpg"
          alt="Naomi Henry, your TravelWithNaomi Vortex365 ambassador"
          class="h-full w-full object-cover"
          width="640" height="800"
          loading="lazy"
          onerror="this.onerror=null; this.src='https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=900&q=80';"
        >
      </div>
    </div>

    <div class="reveal" data-delay="1">
      <h2 class="font-display text-[clamp(2rem,5vw,3rem)] font-bold leading-tight">Hi, I'm Naomi Henry</h2>
      <p class="mt-6 text-lg leading-relaxed text-white/80">
        I discovered Vortex365 and couldn't keep it to myself. Whether you travel for work,
        family or adventure, you deserve to stop overpaying. I'm here personally to help you
        get access and start saving today.
      </p>
      <a href="<?= $wa ?>" class="btn-ghost mt-8 inline-flex items-center gap-2 px-7 py-3">
        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163a11.867 11.867 0 01-1.587-5.946C.16 5.335 5.495 0 12.05 0a11.82 11.82 0 018.413 3.488 11.82 11.82 0 013.48 8.414c-.003 6.557-5.338 11.892-11.893 11.892a11.9 11.9 0 01-5.688-1.448L.057 24zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884a9.86 9.86 0 001.51 5.26l-.999 3.648 3.748-.985zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414z"/></svg>
        Message Me Directly
      </a>
    </div>
  </div>
</section>

<!-- =========================================================
     SECTION 8 — LEAD CAPTURE FORM
     ========================================================= -->
<section id="get-started" class="bg-navy py-24 sm:py-28">
  <div class="mx-auto max-w-2xl px-5 sm:px-8">
    <form
      action="submit.php" method="post" novalidate
      x-data="leadForm()" @submit.prevent="handle($event)"
      class="reveal rounded-3xl border border-gold/45 bg-navy-800/70 p-7 shadow-[0_40px_90px_-40px_rgba(0,0,0,.9)] sm:p-10"
    >
      <div class="text-center">
        <h2 class="font-display text-[clamp(1.8rem,5vw,2.6rem)] font-bold leading-tight">Get your free travel access now</h2>
        <p class="mt-3 text-white/70">Fill in your details below and I will personally send you through to your members portal.</p>
      </div>

      <?php if ($serverError): ?>
        <p class="mt-6 rounded-xl border border-red-400/50 bg-red-500/15 px-4 py-3 text-center text-sm text-red-200">
          Something looked off with your details. Please check and try again.
        </p>
      <?php endif; ?>

      <div class="mt-8 grid gap-5">
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

        <div>
          <label for="travel_interest" class="mb-1.5 block text-sm font-medium text-white/80">Travel interest</label>
          <select id="travel_interest" name="travel_interest"
                  class="input-base appearance-none" x-model="f.travel_interest"
                  @change="touch('travel_interest')" :class="cls('travel_interest')">
            <option value="" disabled>Choose one…</option>
            <option value="Leisure">Leisure</option>
            <option value="Business">Business</option>
            <option value="Family">Family</option>
            <option value="All of the Above">All of the Above</option>
          </select>
          <p class="field-msg" x-text="errs.travel_interest"></p>
        </div>
      </div>

      <button type="submit" class="btn-gold mt-8 w-full px-8 py-4 text-base sm:text-lg">
        Claim My Free Access
      </button>
      <p class="mt-4 text-center text-xs text-white/50">No credit card required · Your details stay private · Takes 2 minutes</p>
    </form>
  </div>
</section>

<!-- =========================================================
     SECTION 9 — FINAL CTA
     ========================================================= -->
<section class="relative overflow-hidden bg-navy-900 py-28">
  <div class="hero-bg absolute inset-0 opacity-90" aria-hidden="true"></div>
  <div class="relative z-10 mx-auto max-w-3xl px-5 text-center sm:px-8">
    <h2 class="reveal font-display text-[clamp(2.2rem,6vw,3.6rem)] font-bold leading-[1.08]" style="text-wrap:balance;">
      Your next trip should cost less. Start here.
    </h2>
    <p class="reveal mx-auto mt-6 max-w-xl text-lg text-white/80" data-delay="1">
      Join through my personal link and get instant access to the Vortex365 members portal, completely free.
    </p>
    <div class="reveal mt-9 flex flex-col items-center gap-4" data-delay="2">
      <a href="<?= $ref ?>" class="btn-gold px-10 py-4 text-base sm:text-lg">Claim My Free Access Now</a>
      <p class="text-sm text-white/55">Free to join · No credit card · Cancel anytime</p>
    </div>
  </div>
</section>

</main>

<!-- =========================================================
     SECTION 10 — FOOTER
     ========================================================= -->
<footer class="border-t border-white/10 bg-navy-900 py-14">
  <div class="mx-auto max-w-6xl px-5 sm:px-8">
    <div class="flex flex-col items-center gap-8 md:flex-row md:items-start md:justify-between">
      <div class="text-center md:text-left">
        <a href="#top" class="font-display text-xl font-bold">Travel<span class="text-gold">With</span>Naomi</a>
        <p class="mt-3 max-w-xs text-sm text-white/55">Members-only travel savings, shared personally by Naomi Henry.</p>
      </div>

      <nav class="flex gap-7 text-sm text-white/70">
        <a href="#" class="transition hover:text-gold">Privacy</a>
        <a href="#how" class="transition hover:text-gold">How It Works</a>
        <a href="<?= $wa ?>" class="transition hover:text-gold">Contact</a>
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
        Naomi Henry is an independent Vortex365 member. This site is not affiliated with or endorsed by Surge365 corporate.
      </p>
      <p class="mt-4 text-xs text-white/40">© 2026 TravelWithNaomi. All rights reserved.</p>
    </div>
  </div>
</footer>

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
    // Safety net: if anything is still hidden after load, reveal it.
    window.addEventListener('load', function () {
      setTimeout(function () {
        els.forEach(function (el) {
          var r = el.getBoundingClientRect();
          if (r.top < window.innerHeight) el.classList.add('in-view');
        });
      }, 600);
    });
  })();

  /* ---- Count-up stat counters ---- */
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
        var eased = 1 - Math.pow(1 - p, 4); // ease-out-quart
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
    var host = document.getElementById('particles');
    if (!host) return;
    var n = window.innerWidth < 640 ? 14 : 26;
    for (var i = 0; i < n; i++) {
      var p = document.createElement('i');
      p.style.left = Math.random() * 100 + '%';
      p.style.animationDuration = (8 + Math.random() * 10) + 's';
      p.style.animationDelay = (Math.random() * 10) + 's';
      var sz = 2 + Math.random() * 4;
      p.style.width = sz + 'px'; p.style.height = sz + 'px';
      host.appendChild(p);
    }
  })();

  /* ---- Alpine form component: real-time validation + overlay ---- */
  function leadForm() {
    return {
      f: { full_name: '', email: '', whatsapp: '', country: '', travel_interest: '' },
      errs: {},
      touched: {},
      rules: {
        full_name: function (v) { return v.trim().length >= 2 ? '' : 'Please enter your full name.'; },
        email: function (v) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v.trim()) ? '' : 'Enter a valid email address.'; },
        whatsapp: function (v) { var d = v.replace(/\D/g, ''); return d.length >= 7 ? '' : 'Enter a valid WhatsApp number.'; },
        country: function (v) { return v.trim().length >= 2 ? '' : 'Please enter your country.'; },
        travel_interest: function (v) { return v ? '' : 'Please choose a travel interest.'; }
      },
      validateField: function (k) {
        this.errs[k] = this.rules[k](this.f[k]);
        return !this.errs[k];
      },
      touch: function (k) { this.touched[k] = true; this.validateField(k); },
      cls: function (k) {
        if (!this.touched[k]) return '';
        return this.errs[k] ? 'invalid' : 'valid';
      },
      handle: function (ev) {
        var ok = true;
        for (var k in this.rules) { this.touched[k] = true; if (!this.validateField(k)) ok = false; }
        if (!ok) {
          // Focus the first invalid field.
          for (var key in this.rules) { if (this.errs[key]) { var el = document.getElementById(key); if (el) el.focus(); break; } }
          return;
        }
        // Show overlay, then submit so submit.php saves + redirects.
        document.getElementById('success-overlay').classList.add('show');
        setTimeout(function () { ev.target.submit(); }, 400);
      }
    };
  }
</script>

<style>[x-cloak]{display:none!important;}</style>
</body>
</html>
