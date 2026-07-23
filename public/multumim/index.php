<?php
// Pagina de multumire: aici se declanseaza conversia (eveniment generate_lead in
// dataLayer, cules de GTM). Noindex: pagina nu are ce cauta in Google.

require __DIR__ . '/../_app/config.php';

$pagina = preg_replace('/[^a-z0-9-]/', '', (string) ($_GET['p'] ?? '')) ?: 'general';
$loc    = ($_GET['l'] ?? '') === 'final' ? 'final' : 'hero';

header('X-Robots-Tag: noindex, nofollow');
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php require __DIR__ . '/../_app/consent.php'; ?>
<title>Vă mulțumim! | Creaton Acoperișuri Mansardări</title>
<meta name="robots" content="noindex, nofollow">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<style>
<?php readfile(__DIR__ . '/../_app/inline.css'); ?>
</style>
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
  event: 'generate_lead',
  form_location: '<?= $loc ?>',
  lp_variant: '<?= $pagina ?>'
});
</script>
<?php if (CREATON_GTM_ID !== '') : ?>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','<?= CREATON_GTM_ID ?>');</script>
<?php endif; ?>
</head>
<body>
<?php if (CREATON_GTM_ID !== '') : ?>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?= CREATON_GTM_ID ?>" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<?php endif; ?>

<section class="hero" style="min-height:100vh;display:flex;align-items:center">
  <div class="wrap hero-grid" style="grid-template-columns:1fr;max-width:640px">
    <div class="form-card" style="text-align:center">
      <div class="form-success" style="display:block">
        <div class="ok-ic">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#E17E12" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <h3>Vă mulțumim!</h3>
        <p>Am primit solicitarea dumneavoastră. Vă sunăm în cel mai scurt timp pentru a stabili vizita și măsurătorile gratuite.</p>
        <p style="margin-top:18px">Aveți o urgență? Sunați-ne direct:</p>
        <p style="margin-top:10px"><a class="btn btn-amber btn-lg" href="tel:<?= e(CREATON_PHONE_E164) ?>"><?= e(CREATON_PHONE_DISPLAY) ?></a></p>
        <p style="margin-top:22px"><a href="/" style="color:var(--amber-2);font-weight:600">&larr; Înapoi la pagina principală</a></p>
      </div>
    </div>
  </div>
</section>

</body>
</html>
