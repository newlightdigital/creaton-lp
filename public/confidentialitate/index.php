<?php
// Politica de confidentialitate (GDPR). Ceruta de politica Google Ads pentru
// paginile de destinatie cu formulare de lead.
// Per Daniel (2026-07-15): folosim numele comercial "Creaton Acoperisuri
// Mansardari"; datele juridice (SRL, CUI) nu sunt disponibile momentan.

require __DIR__ . '/../_app/config.php';
?>
<!DOCTYPE html>
<html lang="ro">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Politica de confidențialitate | Creaton Acoperișuri Mansardări</title>
<meta name="description" content="Cum prelucrăm datele personale colectate prin formularul de contact: ce date colectăm, în ce scop, cât le păstrăm și ce drepturi aveți.">
<link rel="canonical" href="<?= e(CREATON_BASE_URL) ?>/confidentialitate/">
<link rel="icon" href="/assets/img/favicon.svg" type="image/svg+xml">
<style>
<?php readfile(__DIR__ . '/../_app/inline.css'); ?>
.legal{max-width:760px;margin:0 auto;padding:120px 22px 80px}
.legal h1{font-size:2rem;margin-bottom:8px}
.legal h2{font-size:1.25rem;margin:34px 0 10px}
.legal p,.legal li{color:var(--ink-2);font-size:.98rem}
.legal ul{padding-left:22px}
.legal a{color:var(--amber-2);font-weight:600}
</style>
</head>
<body>

<header class="site-header" id="siteHeader">
  <div class="wrap header-inner">
    <a class="brand" href="/" aria-label="Creaton Acoperișuri Mansardări">
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
      <a class="btn btn-amber" href="/#oferta">Cereți oferta</a>
    </div>
  </div>
</header>

<main class="legal">
  <h1>Politica de confidențialitate</h1>
  <p>Ultima actualizare: iulie 2026</p>

  <h2>Cine suntem</h2>
  <p>Acest site este operat de Creaton Acoperișuri Mansardări (denumit în continuare "noi"). Ne puteți contacta la <a href="mailto:<?= e(CREATON_EMAIL) ?>"><?= e(CREATON_EMAIL) ?></a> sau la <?= e(CREATON_PHONE_DISPLAY) ?>.</p>

  <h2>Ce date colectăm</h2>
  <ul>
    <li><strong>Date trimise prin formular:</strong> numele, numărul de telefon și, opțional, tipul lucrării care vă interesează.</li>
    <li><strong>Date tehnice:</strong> identificatori ai clicului publicitar (de exemplu GCLID, atunci când ajungeți la noi dintr-un anunț Google), pagina de pe care ați trimis solicitarea și data trimiterii.</li>
    <li><strong>Cookie-uri și instrumente de măsurare:</strong> folosim Google Tag Manager și instrumente de analiză și publicitate Google pentru a măsura performanța campaniilor.</li>
  </ul>

  <h2>În ce scop și pe ce temei</h2>
  <p>Folosim datele din formular exclusiv pentru a vă contacta în legătură cu solicitarea dumneavoastră de ofertă (demersuri precontractuale, art. 6 alin. 1 lit. b GDPR) și pentru măsurarea eficienței campaniilor noastre publicitare (interes legitim, art. 6 alin. 1 lit. f GDPR).</p>

  <h2>Cât timp păstrăm datele</h2>
  <p>Păstrăm solicitările primite cel mult 2 ani de la ultima interacțiune, apoi le ștergem.</p>

  <h2>Cui transmitem datele</h2>
  <p>Nu vindem și nu închiriem datele dumneavoastră. Ele pot fi procesate în numele nostru de furnizori de găzduire web și de servicii Google (măsurare publicitară), conform acordurilor de prelucrare ale acestora.</p>

  <h2>Drepturile dumneavoastră</h2>
  <p>Aveți dreptul de acces, rectificare, ștergere, restricționare, portabilitate și opoziție, precum și dreptul de a depune o plângere la <a href="https://www.dataprotection.ro/" target="_blank" rel="noopener">ANSPDCP</a>. Pentru orice cerere, scrieți-ne la <a href="mailto:<?= e(CREATON_EMAIL) ?>"><?= e(CREATON_EMAIL) ?></a>.</p>
</main>

<footer class="footer">
  <div class="wrap">
    <div class="footer-bottom" style="border-top:0;padding-top:0">
      <span>Copyright © <?= date('Y') ?> Creaton Acoperișuri Mansardări. Toate drepturile rezervate.</span>
      <span><a href="/">Înapoi la pagina principală</a></span>
    </div>
  </div>
</footer>

<script>
var header=document.getElementById('siteHeader');
var onScroll=function(){ header.classList.toggle('scrolled', window.scrollY>8); };
onScroll(); window.addEventListener('scroll',onScroll,{passive:true});
</script>
</body>
</html>
