<?php
// Renders one full LP variant. A variant page defines $page, then requires this file.
// Keys: slug, path, title, description, h1 (trusted HTML), hero_sub,
//       svc1_tag, svc1_title, svc1_text, preselect ('' = no preselected option).

require_once __DIR__ . '/config.php';

$page = array_merge([
    'slug'        => 'general',
    'path'        => '/',
    'title'       => 'Creaton Acoperișuri Mansardări | Înlocuire, renovare și reparații acoperișuri',
    'description' => 'Echipă cu 15 ani de experiență în Timișoara, Cluj, Reșița și Caransebeș. Ne deplasăm și măsurăm gratuit, apoi primiți o ofertă clară, cu garanție la manoperă.',
    'h1'          => 'Înlocuim, renovăm și reparăm <span class="hl">acoperișuri</span>, la cheie',
    'hero_sub'    => 'Echipă cu 15 ani de experiență și peste 200 de proiecte finalizate. Ne deplasăm și măsurăm gratuit, apoi primiți o ofertă clară, cu preț final și garanție la manoperă.',
    'svc1_tag'    => 'Cel mai cerut',
    'svc1_title'  => 'Înlocuire acoperiș',
    'svc1_text'   => 'Demontăm învelitoarea veche și montăm un acoperiș nou, complet: de la astereală și folie anticondens până la țiglă și tinichigerie. Rezultat etanș, izolat și durabil.',
    'preselect'   => '',
], $page ?? []);

$canonical = CREATON_BASE_URL . $page['path'];

$lucrari = [
    'Înlocuire acoperiș',
    'Montaj acoperiș nou',
    'Renovare sau restaurare acoperiș',
    'Reparații și infiltrații',
    'Mansardare',
    'Hidroizolații și terase',
    'Altă lucrare',
];

// ---- Social proof (Google rating): reused in the hero badge + testimonials ----
$star_svg = '<svg viewBox="0 0 24 24" fill="#F5972A" aria-hidden="true"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>';
$stars5   = str_repeat($star_svg, 5);
$google_g = '<svg class="g-logo" viewBox="0 0 48 48" aria-hidden="true"><path fill="#4285F4" d="M45.12 24.5c0-1.56-.14-3.06-.4-4.5H24v8.51h11.84c-.51 2.75-2.06 5.08-4.39 6.64v5.52h7.11c4.16-3.83 6.56-9.47 6.56-16.17z"/><path fill="#34A853" d="M24 46c5.94 0 10.92-1.97 14.56-5.33l-7.11-5.52c-1.97 1.32-4.49 2.1-7.45 2.1-5.73 0-10.58-3.87-12.31-9.07H4.34v5.7C7.96 41.07 15.4 46 24 46z"/><path fill="#FBBC05" d="M11.69 28.18C11.25 26.86 11 25.45 11 24s.25-2.86.69-4.18v-5.7H4.34C2.85 17.09 2 20.45 2 24s.85 6.91 2.34 9.88l7.35-5.7z"/><path fill="#EA4335" d="M24 10.75c3.23 0 6.13 1.11 8.41 3.29l6.31-6.31C34.91 4.18 29.93 2 24 2 15.4 2 7.96 6.93 4.34 14.12l7.35 5.7c1.73-5.2 6.58-9.07 12.31-9.07z"/></svg>';

// Rating badge wrapper: a link when a Google reviews URL is configured, else a div.
function creaton_rating_open(string $class): string {
    $u = defined('CREATON_REVIEW_URL') ? CREATON_REVIEW_URL : '';
    return $u !== ''
        ? '<a class="' . $class . '" href="' . e($u) . '" target="_blank" rel="noopener">'
        : '<div class="' . $class . '">';
}
function creaton_rating_close(): string {
    return (defined('CREATON_REVIEW_URL') && CREATON_REVIEW_URL !== '') ? '</a>' : '</div>';
}
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($page['title']) ?></title>
<meta name="description" content="<?= e($page['description']) ?>">
<link rel="canonical" href="<?= e($canonical) ?>">
<meta property="og:locale" content="ro_RO">
<meta property="og:type" content="website">
<meta property="og:site_name" content="Creaton Acoperișuri Mansardări">
<meta property="og:title" content="<?= e($page['title']) ?>">
<meta property="og:description" content="<?= e($page['description']) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(CREATON_BASE_URL) ?>/assets/img/og-creaton.jpg">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<!-- Preload the EXACT font URLs @font-face requests (no ?v=), so the preload primes
     the same request instead of a second one. Was double-downloading every font
     (8 files / ~400KB -> 4 / ~200KB). Fonts are immutable, cached 1yr via .htaccess. -->
<link rel="preload" as="font" type="font/woff2" crossorigin href="/assets/fonts/archivo-var-latin-ext.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="/assets/fonts/archivo-var-latin.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="/assets/fonts/inter-var-latin-ext.woff2">
<link rel="preload" as="font" type="font/woff2" crossorigin href="/assets/fonts/inter-var-latin.woff2">
<style>
<?php readfile(__DIR__ . '/inline.css'); ?>
</style>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "RoofingContractor",
  "name": "Creaton Acoperișuri Mansardări",
  "url": "<?= e(CREATON_BASE_URL) ?>/",
  "telephone": "<?= e(CREATON_PHONE_E164) ?>",
  "areaServed": ["Timișoara", "Cluj-Napoca", "Reșița", "Caransebeș", "România"],
  "sameAs": ["<?= e(CREATON_FB_URL) ?>", "<?= e(CREATON_YT_URL) ?>"]
}
</script>
<?php if (CREATON_GTM_ID !== '') : ?>
<script>
// GTM/analytics deferred to first interaction (or a 3s fallback) so the ~640KB
// analytics stack stays off the critical mobile render path (mobile LCP was ~10s).
// gclid is captured independently below, so ad attribution is unaffected, and
// /multumim/ loads GTM eagerly so the lead conversion always fires.
window.dataLayer=window.dataLayer||[];
(function(w,d,i){
  var done=false;
  function load(){
    if(done){return;} done=true;
    w.dataLayer.push({'gtm.start':new Date().getTime(),event:'gtm.js'});
    var f=d.getElementsByTagName('script')[0],j=d.createElement('script');
    j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i;
    f.parentNode.insertBefore(j,f);
  }
  var EV=['scroll','mousemove','touchstart','keydown','pointerdown'];
  function fire(){EV.forEach(function(e){w.removeEventListener(e,fire);});load();}
  EV.forEach(function(e){w.addEventListener(e,fire,{passive:true});});
  setTimeout(load,3000);
})(window,document,'<?= CREATON_GTM_ID ?>');
</script>
<?php endif; ?>
</head>
<body>
<?php if (CREATON_GTM_ID !== '') : ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= CREATON_GTM_ID ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>
<!-- ClickCease (click-fraud detection): loaded directly, NOT via GTM, so it fires on
     every pageview even though the GTM stack is deferred for speed. The matching GTM
     ClickCease tags are paused to avoid a double load. -->
<script async src="https://ob.esnbranding.com/i/9726c93862c5b4428f5cf40627e028ee.js" class="ct_clicktrue"></script>
<noscript><a href="https://www.clickcease.com" rel="nofollow"><img src="https://ob.esnbranding.com/ns/9726c93862c5b4428f5cf40627e028ee.html?ch=1" alt="ClickCease"></a></noscript>
<header class="site-header" id="siteHeader">
  <div class="wrap header-inner">
    <a class="brand" href="#top" aria-label="Creaton Acoperișuri Mansardări">
      <svg class="brand-mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
        <rect width="40" height="40" rx="9" fill="#1E2A32"/>
        <path d="M8 23 L20 12 L32 23" stroke="#F5972A" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/>
        <path d="M11 28 L20 20 L29 28" stroke="#43606F" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>
      </svg>
      <span class="brand-text">
        <span class="brand-name">CREATON</span>
        <span class="brand-sub">Acoperișuri · Mansardări</span>
      </span>
    </a>
    <div class="header-actions">
      <a class="header-phone" href="tel:+40749845759">
        <span class="ic">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.98.36 1.94.7 2.86a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.22-1.22a2 2 0 0 1 2.11-.45c.92.34 1.88.57 2.86.7A2 2 0 0 1 22 16.92z"/></svg>
        </span>
        <span><small>Contact vânzări</small><strong>0749 845 759</strong></span>
      </a>
      <a class="btn btn-amber" href="#oferta">Cereți oferta</a>
    </div>
  </div>
</header>

<!-- ===================== HERO ===================== -->
<section class="hero" id="top">
  <svg class="hero-truss" viewBox="0 0 1200 180" preserveAspectRatio="xMidYMax slice" aria-hidden="true">
    <g stroke="#F5972A" stroke-width="1.4" fill="none" opacity="0.9">
      <path d="M0 180 L150 60 L300 180 M150 60 L150 180 M75 120 L225 120"/>
      <path d="M300 180 L450 60 L600 180 M450 60 L450 180 M375 120 L525 120"/>
      <path d="M600 180 L750 60 L900 180 M750 60 L750 180 M675 120 L825 120"/>
      <path d="M900 180 L1050 60 L1200 180 M1050 60 L1050 180 M975 120 L1125 120"/>
    </g>
  </svg>
  <div class="wrap hero-grid">
    <div class="hero-copy">
      <span class="hero-eyebrow">
        <svg width="16" height="10" viewBox="0 0 16 10" fill="none" aria-hidden="true"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Timișoara · Cluj · Reșița · Caransebeș și în toată țara
      </span>
      <h1><?= $page['h1'] ?></h1>
      <p class="hero-sub"><?= $page['hero_sub'] ?></p>
      <?= creaton_rating_open('g-rating') ?>
        <?= $google_g ?>
        <span class="g-stars"><?= $stars5 ?></span>
        <span class="g-score"><?= e(CREATON_REVIEW_SCORE) ?></span>
        <span class="g-meta"><b><?= (int) CREATON_REVIEW_COUNT ?></b> de recenzii pe Google</span>
      <?= creaton_rating_close() ?>
      <div class="hero-chips">
        <span class="chip"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Deplasare și deviz gratuit</span>
        <span class="chip"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Garanție la manoperă</span>
        <span class="chip"><svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>Companie certificată</span>
      </div>
      <div class="hero-cta">
        <a class="btn btn-amber btn-lg" href="#oferta">Cereți o ofertă gratuită</a>
        <a class="btn btn-ghost-light btn-lg" href="tel:+40749845759">
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.98.36 1.94.7 2.86a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.22-1.22a2 2 0 0 1 2.11-.45c.92.34 1.88.57 2.86.7A2 2 0 0 1 22 16.92z"/></svg>
          Sunați acum
        </a>
      </div>
      <div class="hero-phone-line">
        Preferați telefonic? <a href="tel:+40749845759">0749 845 759</a>
      </div>
    </div>

    <!-- HERO FORM -->
    <div class="form-card" id="oferta">
      <div class="form-body">
        <h2>Cereți o ofertă gratuită</h2>
        <p class="fc-sub">Completați și vă sunăm în cel mai scurt timp. Fără obligații.</p>
        <form class="lead-form" method="post" action="/form-handler.php" novalidate>
          <input type="text" name="_hp" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <input type="hidden" name="pagina" value="<?= e($page['slug']) ?>">
          <input type="hidden" name="loc" value="hero">
          <input type="hidden" name="gclid" value="">
          <input type="hidden" name="gbraid" value="">
          <input type="hidden" name="wbraid" value="">
          <div class="form-error">Vă rugăm să verificați numele și numărul de telefon, apoi să trimiteți din nou.</div>
          <div class="field">
            <label for="hero-nume">Nume <span class="req">*</span></label>
            <input id="hero-nume" type="text" name="nume" placeholder="Numele dvs." autocomplete="name" required>
          </div>
          <div class="field">
            <label for="hero-telefon">Telefon <span class="req">*</span></label>
            <input id="hero-telefon" type="tel" name="telefon" placeholder="07xx xxx xxx" autocomplete="tel" inputmode="tel" required>
          </div>
          <div class="field">
            <label for="hero-lucrare">Ce lucrare vă interesează?</label>
            <select id="hero-lucrare" name="lucrare">
              <option value="">Alegeți din listă</option>
              <?php foreach ($lucrari as $l) : ?>
              <option<?= $l === $page['preselect'] ? ' selected' : '' ?>><?= e($l) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if (CREATON_TURNSTILE_SITEKEY !== '') : ?>
          <div class="cf-turnstile" data-sitekey="<?= e(CREATON_TURNSTILE_SITEKEY) ?>" data-language="ro"></div>
          <?php endif; ?>
          <button type="submit" class="btn btn-amber btn-block btn-lg">Trimiteți solicitarea</button>
          <p class="form-alt">Sau sunați direct: <a href="tel:+40749845759">0749 845 759</a></p>
          <p class="form-note">Prin trimitere sunteți de acord cu <a href="/confidentialitate/">prelucrarea datelor</a> conform GDPR.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ===================== TRUST BAR ===================== -->
<div class="trustbar">
  <div class="wrap trustbar-inner">
    <span class="trust-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg><b>15+</b> ani de experiență</span>
    <span class="trust-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m3 11 18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg><b>200+</b> proiecte finalizate</span>
    <span class="trust-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>Companie certificată</span>
    <span class="trust-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>Garanție la manoperă</span>
    <span class="trust-item"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>Deplasare și deviz gratuit</span>
  </div>
</div>
<div class="ridge"></div>

<!-- ===================== SERVICES ===================== -->
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Servicii</span>
      <h2>Ce facem pentru acoperișul dvs.</h2>
      <p>Ne ocupăm de manoperă și montaj profesionist. Indiferent de tipul de învelitoare, executăm lucrarea corect, curat și la termenul stabilit.</p>
    </div>

    <div class="svc-core reveal">
      <div class="svc-card">
        <?php if ($page['svc1_tag'] !== '') : ?><span class="svc-tag"><?= e($page['svc1_tag']) ?></span><?php endif; ?>
        <div class="svc-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12 12 3l10 9"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg></div>
        <h3><?= e($page['svc1_title']) ?></h3>
        <p><?= e($page['svc1_text']) ?></p>
      </div>
      <div class="svc-card">
        <div class="svc-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 0 1 15-6.7L21 8"/><path d="M21 3v5h-5"/><path d="M21 12a9 9 0 0 1-15 6.7L3 16"/><path d="M3 21v-5h5"/></svg></div>
        <h3>Renovare și restaurare</h3>
        <p>Redăm rezistența și aspectul unui acoperiș uzat: înlocuim elementele deteriorate, reparăm șarpanta și reîmprospătăm învelitoarea, fără să refacem totul de la zero.</p>
      </div>
      <div class="svc-card">
        <div class="svc-ic"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg></div>
        <h3>Reparații și infiltrații</h3>
        <p>Oprim infiltrațiile și rezolvăm rapid problemele punctuale: țigle sparte, coame desfăcute, tinichigerie deteriorată sau hidroizolație compromisă.</p>
      </div>
    </div>

    <div class="svc-sec reveal">
      <div class="svc-mini">
        <div class="mini-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E2A32" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V8l7-5 7 5v13"/><path d="M9 21v-6h6v6"/></svg></div>
        <h4>Mansardări</h4>
        <p>Transformăm podul în spațiu locuibil: izolație, structură și finisaje pentru o cameră în plus.</p>
      </div>
      <div class="svc-mini">
        <div class="mini-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E2A32" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3-4 10-4 10 4 10 4"/><path d="M2 17s3-3 10-3 10 3 10 3"/><path d="M2 7s3-3 10-3 10 3 10 3"/></svg></div>
        <h4>Hidroizolații și terase</h4>
        <p>Hidroizolăm terase și acoperișuri tip terasă și amenajăm terase din lemn.</p>
      </div>
      <div class="svc-mini">
        <div class="mini-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E2A32" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4v9a4 4 0 0 0 4 4h8"/><path d="M4 8h14"/><path d="M16 13l4 4-4 4"/></svg></div>
        <h4>Jgheaburi și sisteme pluviale</h4>
        <p>Montăm și reparăm jgheaburi și burlane pentru un drenaj corect al apei.</p>
      </div>
      <div class="svc-mini">
        <div class="mini-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#1E2A32" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"><path d="m15 12-8.5 8.5a2.12 2.12 0 0 1-3-3L12 9"/><path d="M17.64 15 22 10.64"/><path d="m20.91 11.7-1.25-1.25c-.6-.6-.93-1.4-.93-2.25v-.86L16.01 4.6a5.56 5.56 0 0 0-3.94-1.64H9l.92.82A6.18 6.18 0 0 1 12 8.4v1.56l2 2h.86c.85 0 1.65.33 2.25.93l1.25 1.25"/></svg></div>
        <h4>Dulgherie și șarpantă</h4>
        <p>Executăm și reparăm structura de lemn a acoperișului, corect dimensionată.</p>
      </div>
    </div>

    <p class="svc-foot reveal">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M2 12h20"/></svg>
      Montăm și ferestre de mansardă, luminatoare și executăm foișoare din lemn.
    </p>
  </div>
</section>

<!-- ===================== PROCESS ===================== -->
<section class="section section-mist">
  <div class="wrap">
    <div class="section-head center reveal">
      <span class="eyebrow"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Cum lucrăm</span>
      <h2>Patru pași până la acoperișul reparat</h2>
      <p>Un proces simplu și transparent, fără surprize pe parcurs.</p>
    </div>
    <div class="steps reveal">
      <div class="step">
        <div class="step-num">01</div>
        <h3>Ne contactați</h3>
        <p>Sunați sau completați formularul. Vă răspundem rapid și stabilim o vizită.</p>
      </div>
      <div class="step">
        <div class="step-num">02</div>
        <h3>Măsurăm gratuit</h3>
        <p>Venim la fața locului, evaluăm situația și luăm măsurătorile, fără niciun cost.</p>
      </div>
      <div class="step">
        <div class="step-num">03</div>
        <h3>Primiți oferta</h3>
        <p>Vă trimitem o ofertă clară, cu termen de execuție și preț final, fără costuri ascunse.</p>
      </div>
      <div class="step">
        <div class="step-num">04</div>
        <h3>Executăm lucrarea</h3>
        <p>Începem la data stabilită, lucrăm curat și oferim garanție la manoperă.</p>
      </div>
    </div>
  </div>
</section>

<!-- ===================== WHY US ===================== -->
<section class="section">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>De ce Creaton</span>
      <h2>De ce ne aleg clienții</h2>
    </div>
    <div class="why-grid reveal">
      <div class="why-item">
        <div class="why-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg></div>
        <div><h3>Deplasare și deviz gratuit</h3><p>Venim, măsurăm și vă dăm un preț clar înainte să începem.</p></div>
      </div>
      <div class="why-item">
        <div class="why-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg></div>
        <div><h3>Garanție la manoperă</h3><p>Dacă apare o problemă la lucrarea noastră, o remediem.</p></div>
      </div>
      <div class="why-item">
        <div class="why-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"/><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"/><path d="M4 22h16"/><path d="M10 14.66V17c0 .55-.47.98-.97 1.21C7.85 18.75 7 20.24 7 22"/><path d="M14 14.66V17c0 .55.47.98.97 1.21C16.15 18.75 17 20.24 17 22"/><path d="M18 2H6v7a6 6 0 0 0 12 0V2z"/></svg></div>
        <div><h3>15 ani de experiență</h3><p>Peste 200 de acoperișuri și mansarde executate.</p></div>
      </div>
      <div class="why-item">
        <div class="why-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></div>
        <div><h3>Termene respectate</h3><p>Stabilim o dată de start și ne ținem de ea.</p></div>
      </div>
      <div class="why-item">
        <div class="why-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="m19 21-1-5H6l-1 5"/><path d="M7 16V8l5-4 5 4v8"/><path d="M11 16v-4h2v4"/></svg></div>
        <div><h3>Curățenie la final</h3><p>Vă predăm lucrarea și lăsăm locul curat.</p></div>
      </div>
      <div class="why-item">
        <div class="why-ic"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.98.36 1.94.7 2.86a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.22-1.22a2 2 0 0 1 2.11-.45c.92.34 1.88.57 2.86.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div><h3>Răspuns rapid</h3><p>Când ne sunați, vă răspundem și revenim prompt.</p></div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== PROJECTS ===================== -->
<section class="section section-mist">
  <div class="wrap">
    <div class="section-head reveal">
      <span class="eyebrow"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Lucrări</span>
      <h2>Rezultate pe care le puteți vedea</h2>
      <p>Câteva dintre acoperișurile și mansardele executate de echipa noastră.</p>
    </div>
    <div class="proj-grid reveal">
      <div class="proj-card">
        <div class="media-slot"><img src="/assets/img/lucrari/inlocuire-tigla-metalica-antracit.webp?v=<?= creaton_asset_ver('assets/img/lucrari/inlocuire-tigla-metalica-antracit.webp') ?>" alt="Acoperiș nou din țiglă metalică antracit, finalizat de echipa Creaton" width="800" height="600" loading="lazy" decoding="async"></div>
        <div class="proj-body"><span class="p-tag">Înlocuire</span><h3>Acoperiș nou din țiglă metalică antracit</h3></div>
      </div>
      <div class="proj-card">
        <div class="media-slot"><img src="/assets/img/lucrari/renovare-acoperis-tigla-peste-folie.webp?v=<?= creaton_asset_ver('assets/img/lucrari/renovare-acoperis-tigla-peste-folie.webp') ?>" alt="Renovare în lucru: țiglă metalică montată peste folie anticondens și șipci" width="800" height="600" loading="lazy" decoding="async"></div>
        <div class="proj-body"><span class="p-tag">Renovare</span><h3>De la tablă veche la țiglă metalică nouă</h3></div>
      </div>
      <div class="proj-card">
        <div class="media-slot"><img src="/assets/img/lucrari/montaj-tigla-echipament-siguranta.webp?v=<?= creaton_asset_ver('assets/img/lucrari/montaj-tigla-echipament-siguranta.webp') ?>" alt="Montator cu echipament de siguranță instalând țiglă metalică" width="800" height="600" loading="lazy" decoding="async"></div>
        <div class="proj-body"><span class="p-tag">Montaj</span><h3>Montaj cu echipă proprie și ancorare de siguranță</h3></div>
      </div>
      <div class="proj-card">
        <div class="media-slot"><img src="/assets/img/lucrari/sarpanta-dulgherie-refacere-acoperis.webp?v=<?= creaton_asset_ver('assets/img/lucrari/sarpanta-dulgherie-refacere-acoperis.webp') ?>" alt="Șarpantă și astereală nouă în timpul refacerii unui acoperiș de casă veche" width="800" height="600" loading="lazy" decoding="async"></div>
        <div class="proj-body"><span class="p-tag">Dulgherie</span><h3>Șarpantă și dulgherie refăcute complet</h3></div>
      </div>
    </div>
    <div class="proj-foot reveal">
      <a href="https://www.youtube.com/@CREATON-ACOPERISURI-MANSARDARI" target="_blank" rel="noopener">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 12s0-3.5-.46-5.16a3 3 0 0 0-2.11-2.12C18.77 4.25 12 4.25 12 4.25s-6.77 0-8.43.47A3 3 0 0 0 1.46 6.84 30 30 0 0 0 1 12a30 30 0 0 0 .46 5.16 3 3 0 0 0 2.11 2.12c1.66.47 8.43.47 8.43.47s6.77 0 8.43-.47a3 3 0 0 0 2.11-2.12C23 15.5 23 12 23 12z"/><path d="m9.75 15.02 5.75-3.27-5.75-3.27v6.54z" fill="currentColor" stroke="none"/></svg>
        Vedeți echipa la lucru pe YouTube
      </a>
    </div>
  </div>
</section>

<!-- ===================== TESTIMONIALS ===================== -->
<section class="section">
  <div class="wrap">
    <div class="section-head center reveal">
      <span class="eyebrow"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Recenzii</span>
      <h2>Ce spun clienții noștri</h2>
    </div>
    <?= creaton_rating_open('g-review-summary reveal') ?>
      <?= $google_g ?>
      <div class="g-rs-body">
        <div class="g-rs-top"><span class="g-rs-score"><?= e(CREATON_REVIEW_SCORE) ?></span><span class="g-stars"><?= $stars5 ?></span></div>
        <div class="g-rs-meta">din <b><?= (int) CREATON_REVIEW_COUNT ?> de recenzii</b> pe Google</div>
      </div>
      <?php if (CREATON_REVIEW_URL !== '') : ?><span class="g-rs-link">Citiți recenziile</span><?php endif; ?>
    <?= creaton_rating_close() ?>
    <div class="rev-grid reveal">
      <div class="rev-card">
        <div class="stars" aria-label="5 din 5 stele">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
        </div>
        <p>Am apelat la echipă pentru înlocuirea acoperișului casei și sunt foarte mulțumit de rezultat. Oameni serioși, lucrare curată.</p>
        <div class="rev-who"><div class="rev-avatar">M</div><div><strong>Mihai</strong><span>Timișoara</span></div></div>
      </div>
      <div class="rev-card">
        <div class="stars" aria-label="5 din 5 stele">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
        </div>
        <p>Am ales Creaton pentru renovarea mansardei și a fost o alegere excelentă. Recomand cu încredere serviciile lor.</p>
        <div class="rev-who"><div class="rev-avatar">G</div><div><strong>George</strong><span>Cluj</span></div></div>
      </div>
      <div class="rev-card">
        <div class="stars" aria-label="5 din 5 stele">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="#F5972A"><path d="M12 2 15.09 8.26 22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14l-5-4.87 6.91-1.01L12 2z"/></svg>
        </div>
        <p>Lucrările de hidroizolație au fost făcute cu precizie și profesionalism. De atunci nu am mai avut nicio problemă.</p>
        <div class="rev-who"><div class="rev-avatar">M</div><div><strong>Mircea</strong><span>Caransebeș</span></div></div>
      </div>
    </div>
  </div>
</section>

<!-- ===================== SERVICE AREA ===================== -->
<section class="area">
  <div class="wrap area-inner">
    <div class="reveal">
      <span class="eyebrow on-dark"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Zona de lucru</span>
      <h2>Ne găsiți aici, dar venim la dvs.</h2>
      <p>Lucrăm în Timișoara, Cluj, Reșița și Caransebeș și ne deplasăm în toată țara. Spuneți-ne unde este lucrarea și organizăm o vizită pentru evaluare și măsurători gratuite.</p>
      <div class="area-cities">
        <span class="area-city">Timișoara</span>
        <span class="area-city">Cluj</span>
        <span class="area-city">Reșița</span>
        <span class="area-city">Caransebeș</span>
        <span class="area-city">În toată țara</span>
      </div>
    </div>
    <div class="area-contact reveal">
      <div class="area-line">
        <span class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.98.36 1.94.7 2.86a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.22-1.22a2 2 0 0 1 2.11-.45c.92.34 1.88.57 2.86.7A2 2 0 0 1 22 16.92z"/></svg></span>
        <span><small>Sunați-ne</small><a href="tel:+40749845759">0749 845 759</a></span>
      </div>
      <div class="area-line">
        <span class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></span>
        <span><small>Scrieți-ne</small><a href="#oferta">Trimiteți-ne un mesaj</a></span>
      </div>
      <div class="area-line">
        <span class="ic"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
        <span><small>Program</small><b>Disponibili non-stop pentru urgențe</b></span>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FAQ ===================== -->
<section class="section">
  <div class="wrap">
    <div class="section-head center reveal">
      <span class="eyebrow"><svg width="16" height="10" viewBox="0 0 16 10" fill="none"><path d="M1 9 L8 2 L15 9" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>Întrebări frecvente</span>
      <h2>Răspunsuri la ce ne întrebați des</h2>
    </div>
    <div class="faq reveal">
      <details class="faq-item">
        <summary>Cât costă înlocuirea unui acoperiș? <span class="faq-plus"></span></summary>
        <div class="faq-answer">Depinde de suprafață, de tipul de învelitoare și de starea șarpantei. Tocmai de aceea venim și măsurăm gratuit, apoi vă dăm un preț clar, cu deviz detaliat și fără costuri ascunse.</div>
      </details>
      <details class="faq-item">
        <summary>Oferiți garanție? <span class="faq-plus"></span></summary>
        <div class="faq-answer">Da. Oferim garanție la manoperă pentru toate lucrările executate de echipa noastră, ca să stați liniștit după finalizare.</div>
      </details>
      <details class="faq-item">
        <summary>În cât timp puteți începe lucrarea? <span class="faq-plus"></span></summary>
        <div class="faq-answer">După ce acceptați oferta, stabilim împreună data de start și ne încadrăm în termenul convenit. Pentru urgențe (infiltrații active) intervenim cât mai repede posibil.</div>
      </details>
      <details class="faq-item">
        <summary>Lucrați și în afara Timișoarei? <span class="faq-plus"></span></summary>
        <div class="faq-answer">Da, ne deplasăm în toată țara. Deplasarea pentru evaluare și măsurători este gratuită.</div>
      </details>
      <details class="faq-item">
        <summary>Furnizați și materialele? <span class="faq-plus"></span></summary>
        <div class="faq-answer">Ne ocupăm de execuție și montaj profesionist. Vă putem recomanda furnizori de încredere și vă ajutăm să alegeți soluția potrivită pentru bugetul dvs.</div>
      </details>
      <details class="faq-item">
        <summary>Ce tipuri de învelitoare montați? <span class="faq-plus"></span></summary>
        <div class="faq-answer">Montăm toate tipurile: țiglă ceramică, țiglă din beton, țiglă metalică, tablă și învelitori bituminoase, adaptate nevoilor și bugetului dvs.</div>
      </details>
    </div>
  </div>
</section>

<!-- ===================== FINAL CTA ===================== -->
<section class="final">
  <div class="wrap final-grid">
    <div class="reveal">
      <span class="final-badge">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2 3 14h9l-1 8 10-12h-9l1-8z"/></svg>
        Profitați de ofertele de sezon
      </span>
      <h2>Cereți oferta pentru <span class="hl">acoperișul dvs.</span></h2>
      <p>Vă sunăm, venim și măsurăm gratuit, apoi primiți o ofertă clară, cu preț final și garanție la manoperă. Fără obligații.</p>
      <div class="final-phone">
        <span class="ic"><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#F5972A" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.98.36 1.94.7 2.86a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.22-1.22a2 2 0 0 1 2.11-.45c.92.34 1.88.57 2.86.7A2 2 0 0 1 22 16.92z"/></svg></span>
        <span><small>Sunați acum</small><a href="tel:+40749845759">0749 845 759</a></span>
      </div>
    </div>

    <div class="form-card">
      <div class="form-body">
        <h2>Solicitare rapidă</h2>
        <p class="fc-sub">Lăsați numele și telefonul. Vă contactăm noi.</p>
        <form class="lead-form" method="post" action="/form-handler.php" novalidate>
          <input type="text" name="_hp" class="hp-field" tabindex="-1" autocomplete="off" aria-hidden="true">
          <input type="hidden" name="pagina" value="<?= e($page['slug']) ?>">
          <input type="hidden" name="loc" value="final">
          <input type="hidden" name="gclid" value="">
          <input type="hidden" name="gbraid" value="">
          <input type="hidden" name="wbraid" value="">
          <div class="form-error">Vă rugăm să verificați numele și numărul de telefon, apoi să trimiteți din nou.</div>
          <div class="field">
            <label for="final-nume">Nume <span class="req">*</span></label>
            <input id="final-nume" type="text" name="nume" placeholder="Numele dvs." autocomplete="name" required>
          </div>
          <div class="field">
            <label for="final-telefon">Telefon <span class="req">*</span></label>
            <input id="final-telefon" type="tel" name="telefon" placeholder="07xx xxx xxx" autocomplete="tel" inputmode="tel" required>
          </div>
          <?php if (CREATON_TURNSTILE_SITEKEY !== '') : ?>
          <div class="cf-turnstile" data-sitekey="<?= e(CREATON_TURNSTILE_SITEKEY) ?>" data-language="ro"></div>
          <?php endif; ?>
          <button type="submit" class="btn btn-amber btn-block btn-lg">Trimiteți solicitarea</button>
          <p class="form-alt">Sau sunați: <a href="tel:+40749845759">0749 845 759</a></p>
          <p class="form-note">Prin trimitere sunteți de acord cu <a href="/confidentialitate/">prelucrarea datelor</a> conform GDPR.</p>
        </form>
      </div>
    </div>
  </div>
</section>

<!-- ===================== FOOTER ===================== -->
<footer class="footer">
  <div class="wrap">
    <div class="footer-top">
      <div>
        <div class="brand">
          <svg class="brand-mark" viewBox="0 0 40 40" fill="none" aria-hidden="true">
            <rect width="40" height="40" rx="9" fill="#243743"/>
            <path d="M8 23 L20 12 L32 23" stroke="#F5972A" stroke-width="2.6" stroke-linejoin="round" stroke-linecap="round"/>
            <path d="M11 28 L20 20 L29 28" stroke="#43606F" stroke-width="2.2" stroke-linejoin="round" stroke-linecap="round"/>
          </svg>
          <span class="brand-text">
            <span class="brand-name">CREATON</span>
            <span class="brand-sub">Acoperișuri · Mansardări</span>
          </span>
        </div>
        <p class="footer-cities">Ne găsiți în Timișoara, Cluj, Reșița și Caransebeș, dar ne deplasăm în toată țara.</p>
      </div>
      <div class="footer-links">
        <div class="footer-col">
          <h4>Servicii</h4>
          <a href="#oferta">Înlocuire acoperiș</a>
          <a href="#oferta">Renovare și restaurare</a>
          <a href="#oferta">Reparații și infiltrații</a>
          <a href="#oferta">Mansardări</a>
          <a href="#oferta">Hidroizolații și terase</a>
        </div>
        <div class="footer-col">
          <h4>Contact</h4>
          <a href="tel:+40749845759">0749 845 759</a>
          <a href="#oferta">Trimiteți-ne un mesaj</a>
          <a href="https://www.facebook.com/creatonacoperisurimansardari" target="_blank" rel="noopener">Facebook</a>
          <a href="https://www.youtube.com/@CREATON-ACOPERISURI-MANSARDARI" target="_blank" rel="noopener">YouTube</a>
        </div>
        <div class="footer-col">
          <h4>Informații</h4>
          <a href="https://anpc.ro/" target="_blank" rel="noopener">ANPC</a>
          <a href="https://www.dataprotection.ro/" target="_blank" rel="noopener">GDPR</a>
          <a href="https://ec.europa.eu/consumers/odr/" target="_blank" rel="noopener">Platforma SOL</a>
          <a href="/confidentialitate/">Politica de confidențialitate</a>
        </div>
      </div>
    </div>
    <div class="footer-bottom">
      <span>Copyright © <span id="year"></span> Creaton Acoperișuri Mansardări. Toate drepturile rezervate.</span>
      <span>Site realizat de <a href="https://newlightdigital.com/ro/" target="_blank" rel="noopener">New Light Digital</a></span>
      <div class="footer-social">
        <a href="https://www.facebook.com/creatonacoperisurimansardari" target="_blank" rel="noopener" aria-label="Facebook"><svg width="17" height="17" viewBox="0 0 24 24" fill="#fff"><path d="M22 12a10 10 0 1 0-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0 0 22 12z"/></svg></a>
        <a href="https://www.youtube.com/@CREATON-ACOPERISURI-MANSARDARI" target="_blank" rel="noopener" aria-label="YouTube"><svg width="17" height="17" viewBox="0 0 24 24" fill="#fff"><path d="M23 12s0-3.5-.46-5.16a3 3 0 0 0-2.11-2.12C18.77 4.25 12 4.25 12 4.25s-6.77 0-8.43.47A3 3 0 0 0 1.46 6.84 30 30 0 0 0 1 12a30 30 0 0 0 .46 5.16 3 3 0 0 0 2.11 2.12c1.66.47 8.43.47 8.43.47s6.77 0 8.43-.47a3 3 0 0 0 2.11-2.12C23 15.5 23 12 23 12zM9.75 15.02V8.48L15.5 11.75z"/></svg></a>
      </div>
    </div>
  </div>
</footer>

<!-- ===================== STICKY MOBILE BAR ===================== -->
<div class="mobilebar">
  <a class="btn btn-dark" href="tel:+40749845759">
    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.98.36 1.94.7 2.86a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.22-1.22a2 2 0 0 1 2.11-.45c.92.34 1.88.57 2.86.7A2 2 0 0 1 22 16.92z"/></svg>
    Sunați
  </a>
  <a class="btn btn-amber" href="#oferta">Cereți oferta</a>
</div>

<script>
(function(){
  "use strict";

  // Lazy-load Cloudflare Turnstile only when the user starts filling a form.
  // Loading it eagerly pulls ~400KB of challenge JS on first paint and was the
  // largest-contentful-paint bottleneck on mobile. The widget still solves well
  // before submit (managed mode passes in ~1s once loaded).
  var tsWanted = <?= CREATON_TURNSTILE_SITEKEY !== '' ? 'true' : 'false' ?>;
  if (tsWanted) {
    var tsLoaded = false;
    var loadTurnstile = function(){
      if (tsLoaded) { return; }
      tsLoaded = true;
      var s = document.createElement('script');
      s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js';
      s.async = true; s.defer = true;
      document.head.appendChild(s);
    };
    document.querySelectorAll('.lead-form').forEach(function(form){
      form.addEventListener('focusin', loadTurnstile, {once:true});
      form.addEventListener('submit', loadTurnstile); // safety net for fast fills
    });
  }

  // Year
  var y=document.getElementById('year'); if(y) y.textContent=new Date().getFullYear();

  // Header shadow on scroll
  var header=document.getElementById('siteHeader');
  var onScroll=function(){ header.classList.toggle('scrolled', window.scrollY>8); };
  onScroll(); window.addEventListener('scroll',onScroll,{passive:true});

  // Reveal on scroll
  var reveals=document.querySelectorAll('.reveal');
  if('IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches){
    var io=new IntersectionObserver(function(entries){
      entries.forEach(function(e){ if(e.isIntersecting){ e.target.classList.add('in'); io.unobserve(e.target); } });
    },{threshold:0.12, rootMargin:'0px 0px -40px 0px'});
    reveals.forEach(function(el){ io.observe(el); });
  } else {
    reveals.forEach(function(el){ el.classList.add('in'); });
  }

  // Persist Google Ads click ids for 90 days and pass them with the lead
  var AD_KEYS=['gclid','gbraid','wbraid'];
  try{
    var ps=new URLSearchParams(location.search), ids={};
    AD_KEYS.forEach(function(k){
      var v=ps.get(k);
      if(v){ ids[k]=v; try{ localStorage.setItem('creaton_'+k, JSON.stringify({v:v,t:Date.now()})); }catch(e){} }
      if(!ids[k]){
        try{
          var s=JSON.parse(localStorage.getItem('creaton_'+k)||'null');
          if(s && s.v && (Date.now()-s.t) < 90*864e5){ ids[k]=s.v; }
        }catch(e){}
      }
    });
    AD_KEYS.forEach(function(k){
      document.querySelectorAll('input[name="'+k+'"]').forEach(function(inp){ if(ids[k]) inp.value=ids[k]; });
    });
  }catch(e){}

  // Client-side validation; on success the form POSTs to /form-handler.php
  document.querySelectorAll('.lead-form').forEach(function(form){
    form.addEventListener('submit',function(ev){
      var nume=form.querySelector('[name="nume"]');
      var tel=form.querySelector('[name="telefon"]');
      var digits=(tel.value||'').replace(/\D/g,'');
      var ok=true;
      [nume,tel].forEach(function(f){ f.style.borderColor=''; });
      if(!nume.value.trim()){ nume.style.borderColor='#E14B2A'; ok=false; }
      if(digits.length<9){ tel.style.borderColor='#E14B2A'; ok=false; }
      var card=form.closest('.form-card');
      if(!ok){ ev.preventDefault(); if(card){ card.classList.add('show-error'); } return; }
      if(card){ card.classList.remove('show-error'); }
      var btn=form.querySelector('button[type="submit"]');
      if(btn){ btn.disabled=true; btn.style.opacity='.7'; }
    });
  });

  // Server-side rejection lands back here with ?err=1
  if(location.search.indexOf('err=1')>-1){
    document.querySelectorAll('.form-card').forEach(function(c){ c.classList.add('show-error'); });
  }
})();
</script>
</body>
</html>
