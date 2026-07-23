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
<?php require __DIR__ . '/../_app/consent.php'; ?>
<title>Politica de confidențialitate și de cookie-uri | Creaton Acoperișuri Mansardări</title>
<meta name="description" content="Cum prelucrăm datele personale colectate prin formularul de contact și ce cookie-uri folosim: ce date colectăm, în ce scop, cât le păstrăm și ce drepturi aveți.">
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
  <h1>Politica de confidențialitate și de cookie-uri</h1>
  <p>Ultima actualizare: iulie 2026</p>

  <h2>Cine suntem</h2>
  <p>Acest site este operat de Creaton Acoperișuri Mansardări (denumit în continuare "noi"). Ne puteți contacta prin <a href="/#oferta">formularul de contact</a> de pe pagina principală sau telefonic la <?= e(CREATON_PHONE_DISPLAY) ?>.</p>

  <h2>Ce date colectăm</h2>
  <ul>
    <li><strong>Date trimise prin formular:</strong> numele, numărul de telefon și, opțional, tipul lucrării care vă interesează.</li>
    <li><strong>Date tehnice:</strong> identificatori ai clicului publicitar (de exemplu GCLID, atunci când ajungeți la noi dintr-un anunț Google), pagina de pe care ați trimis solicitarea și data trimiterii.</li>
    <li><strong>Cookie-uri și instrumente de măsurare:</strong> folosim Google Tag Manager și instrumente de analiză și de publicitate. Acestea se activează numai după ce vă exprimați acordul în bannerul de cookie-uri.</li>
  </ul>

  <h2>În ce scop și pe ce temei</h2>
  <p>Folosim datele din formular exclusiv pentru a vă contacta în legătură cu solicitarea dumneavoastră de ofertă (demersuri precontractuale, art. 6 alin. 1 lit. b GDPR).</p>
  <p>Pentru măsurarea traficului și a eficienței campaniilor noastre publicitare ne întemeiem pe consimțământul dumneavoastră, exprimat prin bannerul de cookie-uri (art. 6 alin. 1 lit. a GDPR). Vă puteți retrage acordul oricând, din secțiunea de mai jos.</p>

  <h2>Cât timp păstrăm datele</h2>
  <p>Păstrăm solicitările primite cel mult 2 ani de la ultima interacțiune, apoi le ștergem. Dovada opțiunii dumneavoastră privind cookie-urile se păstrează 12 luni, după care vă vom întreba din nou.</p>

  <h2>Cui transmitem datele</h2>
  <p>Nu vindem și nu închiriem datele dumneavoastră. Ele pot fi procesate în numele nostru de furnizorul de găzduire web și, numai dacă ați acceptat cookie-urile corespunzătoare, de Google Ireland Limited (Google Analytics, Google Ads, Google Tag Manager) și de Yandex (Yandex Metrica), conform acordurilor de prelucrare ale acestora.</p>

  <h2>Cookie-uri și tehnologii similare</h2>
  <p>Cookie-urile sunt fișiere text de mici dimensiuni pe care site-ul le salvează în browserul dumneavoastră. Le folosim în patru categorii:</p>
  <ul>
    <li><strong>Strict necesare.</strong> Fac site-ul să funcționeze, permit trimiterea formularului și rețin opțiunea dumneavoastră privind cookie-urile. Nu pot fi dezactivate și nu au nevoie de consimțământ.</li>
    <li><strong>Analiză.</strong> Ne arată câți vizitatori avem și ce pagini citesc, ca să îmbunătățim site-ul.</li>
    <li><strong>Marketing.</strong> Ne permit să măsurăm rezultatele campaniilor Google Ads și să evităm afișarea repetată a acelorași reclame.</li>
    <li><strong>Funcționalitate.</strong> Rețin preferințele dumneavoastră, ca site-ul să fie afișat la fel la următoarea vizită.</li>
  </ul>
  <p>Folosim modul de consimțământ Google (Consent Mode v2). Până când vă exprimați acordul, instrumentele de analiză și de publicitate nu scriu cookie-uri și primesc doar semnale anonime, fără identificatori.</p>
  <p>Cookie-urile pe care le folosim concret:</p>
  <ul>
    <li><strong>creaton_consent</strong> (setat de acest site): reține opțiunea dumneavoastră privind cookie-urile. Durată: 12 luni.</li>
    <li><strong>_ga, _ga_*</strong> (Google Analytics): disting vizitatorii pentru statistici de trafic. Durată: până la 2 ani. Se scriu doar cu acordul pentru analiză.</li>
    <li><strong>_gcl_au, _gcl_aw</strong> (Google Ads): măsoară conversiile provenite din anunțuri. Durată: până la 90 de zile. Se scriu doar cu acordul pentru marketing.</li>
    <li><strong>_ym_*</strong> (Yandex Metrica): statistici privind modul de utilizare a site-ului. Durată: până la 1 an. Se scriu doar cu acordul pentru analiză.</li>
  </ul>
  <p><a href="#" data-creaton-cookies>Modificați preferințele privind cookie-urile</a></p>
  <p>Puteți, de asemenea, să ștergeți sau să blocați cookie-urile din setările browserului dumneavoastră. Rețineți că blocarea totală a cookie-urilor poate afecta funcționarea unor părți ale site-ului.</p>

  <h2>Drepturile dumneavoastră</h2>
  <p>Aveți dreptul de acces, rectificare, ștergere, restricționare, portabilitate și opoziție, dreptul de a vă retrage oricând consimțământul privind cookie-urile (fără ca aceasta să afecteze legalitatea prelucrării efectuate anterior retragerii), precum și dreptul de a depune o plângere la <a href="https://www.dataprotection.ro/" target="_blank" rel="noopener">ANSPDCP</a>. Pentru orice cerere, folosiți <a href="/#oferta">formularul de contact</a> sau sunați-ne la <?= e(CREATON_PHONE_DISPLAY) ?>.</p>
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
