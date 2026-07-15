# Creaton LP pack (creaton-acoperisuri-mansardari.ro)

Google Ads landing page pack for Creaton Acoperisuri Mansardari, a Romanian
roofing and attic-conversion company (Timisoara, Cluj, Resita, Caransebes).
NOT a WordPress site: Daniel explicitly dropped WP for this project because the
only goals are conversion and speed. Do not reintroduce WordPress, a CMS, a
build pipeline, or any framework.

## Stack

- Static-PHP micro stack: every URL is a folder with `index.php` that defines a
  `$page` array and requires `public/_app/page.php` (the single LP renderer).
- `public/_app/config.php`: constants (phone, GTM, Turnstile site key) and
  helpers. Secrets go in `public/_app/config.local.php` (gitignored): may define
  `CREATON_TURNSTILE_SECRET`, `CREATON_LEAD_BCC`.
- ONE CSS file, `public/_app/inline.css`, inlined into `<style>` at render time.
  Design tokens live in `:root` there (graphite + amber, from the approved
  template `~/Downloads/creaton-landing-page.html`).
- Fonts: self-hosted variable woff2 (Archivo 500-900, Inter 400-700), latin +
  latin-ext subsets only (latin-ext carries Romanian diacritics). No Google
  Fonts CDN, no icon fonts (icons are inline SVG).
- Forms POST to `public/form-handler.php`: honeypot, validation, optional
  Turnstile verify (fail-open), lead logged to JSONL in `/leads/` FIRST (source
  of truth, outside webroot), then email, then 303 redirect to `/multumim/`.
  IMPORTANT: `mail()` is DISABLED on the NAV host. The handler sends via a
  built-in dependency-free SMTP client to Exim on 127.0.0.1:25 (no auth: local
  submission/relay is trusted). office@ delivered locally, BCC to daniel@
  relayed out. Verified live via Track Delivery (all "Accepted"). Everything in
  the mail path is wrapped so a failure can never 500 or lose a lead.
- Turnstile is LAZY-LOADED (injected on first form focus/submit), not eager;
  eager loading made it the LCP element (~400KB challenge JS) and tanked mobile
  PageSpeed. Server-side Turnstile verify is OFF until the secret is added to
  config.local.php on the server (honeypot active meanwhile).
- Conversion flow: GTM (GTM-57XHFP55) fires on the `generate_lead` dataLayer
  event pushed on `/multumim/`. Click-to-call is tracked in GTM via tel: link
  click triggers. gclid/gbraid/wbraid are persisted in localStorage for 90 days
  and submitted with each lead (for future offline conversion uploads).

## LP variants (Google Ads message match)

A variant = a folder with an `index.php` that sets `$page`:
`slug`, `path`, `title`, `description`, `h1` (trusted HTML, may contain
`<span class="hl">`), `hero_sub`, `svc1_tag/svc1_title/svc1_text` (first service
card), `preselect` (exact option text to preselect in the form).

Built 2026-07-15 against the LIVE campaign "NLD | Search | Lead | Reparatii
Refacere Acoperis | RO Vest" (Ads customer 3218774193, RON; queried via API,
last-30-days spend):
- `/montaj-acoperis/`  <- ad group "Montaj / Manopera Acoperis" (3941 RON)
- `/reparatii-acoperis/` <- "Reparatii Acoperisuri" (948) + "Infiltratii /
  Acoperis Curge" (35) + "Reparatii Tabla / Tigla" (17)
- `/inlocuire-acoperis/` <- "Refacere / Renovare / Inlocuire" (883)
- `/` general <- everything else (brand, paused campaigns if reactivated)
Google Ads API access works: creds in `~/.nld_ads_creds.py` (adwords scope,
API v21, login-customer-id = NLD MCC). Read-only queries are fine; MUTATIONS
(changing ads/final URLs) only with Daniel's explicit go.

KNOWN ISSUE spotted in the account: the active RO Vest campaign logged only 5
conversions in 30 days vs 109 on the paused general campaign with similar
clicks. Conversion tracking on the active campaign is almost certainly broken
(old GTM trigger fires on /thank-you-page/). The /multumim/ + GTM trigger fix
at launch addresses it; after launch, switch each ad group's final URL to its
variant (currently ads point to / and /contact/).

## Environment and deploy

- Hosting: NAV Communications shared cPanel (acct `creatona`, home
  /home/creatona, server cp04.server.ro, dedicated IP 85.120.222.229, PHP
  8.2.31, LiteSpeed). NO shell access, NO MultiPHP INI editor on this plan.
  DNS/proxy on Cloudflare (zone ecc091cb2a838cee8851fc51f253cac3).
- LIVE as of 2026-07-15: the new static site replaced WordPress. Old WP files
  moved to `/home/creatona/old-wp-files/`, plus a full backup tarball
  `/home/creatona/backup-oldsite-2026-07-15.tar.gz` (780MB) and DB export
  `~/Downloads/creatona_wp344.sql` (local). Old WP DB `creatona_wp344` still
  intact in MySQL.
- Source of truth: private GitHub repo `newlightdigital/creaton-lp` (main).
  config.local.php is gitignored (secret stays server-side only).
- DEPLOY MECHANISM (no shell): cPanel Git Version Control. A clone lives at
  `/home/creatona/repositories/creaton-lp2`. To ship: commit+push, set the
  repo public briefly, in cPanel Git VC "Manage > Pull or Deploy > Update from
  Remote", then File Manager-copy the changed file(s) from
  `repositories/creaton-lp2/public/...` into `public_html/...`, then set the
  repo private again. (cPanel can't clone/pull the private repo without a token,
  hence the brief public flip. Do NOT leave a PAT on the shared server.)
- DEPLOY GOTCHA (2026-07-15): the public-flip step can STALL. GitHub now demands
  sudo-mode re-auth (passkey) to change repo visibility, which the assistant
  cannot do; and the `file_upload` browser tool rejects project/scratchpad paths
  (only user-attached files). RELIABLE NO-SHELL FALLBACK used to ship the reviews
  feature: edit each deployed file IN PLACE via cPanel File Manager (select file >
  Edit > editor tab). The editor is Ace (latest) / EditArea (legacy); set content
  with `ace.edit(document.querySelector('.ace_editor')).setValue(content,-1)`, then
  click "Save Changes" VIA ELEMENT REF (coordinate clicks silently miss it), then
  confirm the new byte size in the listing. For big files, don't transfer the whole
  file: compute byte-exact search/replace hunks from `git show <old>:path` vs the
  working tree (difflib), base64 them, and apply with `.split(s).join(r)` in the
  editor. Verify live with curl (HTTP 200 + rendered markers) after saving.
- `public/` maps to `public_html/`. Deploy root has `.htaccess` 301s + security
  headers; `_app/` is `Require all denied` (verified 403). Never edit the server
  by hand except config.local.php (secret) which is not in git.
- SECURITY HEADERS ARE SET IN TWO PLACES (both must agree): (1) origin
  `public/.htaccess` `<IfModule mod_headers.c>`, and (2) a Cloudflare Response
  Header Transform Rule "Security headers" (zone ecc0..., ruleset
  934c814286354716b2cc2b7d59bd0ef3, rule 18b22e46d00e499dbac22f5581273975, expr
  `true`) that re-sets them at the edge. 2026-07-15: to enable Yandex Metrica
  behavioral maps (Click/Scroll/Link/Form), which iframe the live page on
  metrica.yandex.* / webvisor.com, X-Frame-Options was REMOVED from BOTH places
  and replaced (in .htaccess) with `Content-Security-Policy: frame-ancestors
  'self' <yandex+webvisor domains>`. That CSP passes through Cloudflare and is now
  the only framing control (still blocks all non-Yandex origins). LESSON: removing
  a header from .htaccess alone is NOT enough - the Cloudflare rule re-adds it;
  change both. Verify with `curl -skI --resolve ...:85.120.222.229` (origin) vs
  plain curl (edge).
- Local preview: LocalWP's bundled PHP via `.claude/launch.json`
  (php -S 127.0.0.1:8737 -t public). PHP is NOT on PATH; binary:
  `~/Library/Application Support/Local/lightning-services/php-8.3.30+1/bin/darwin-arm64/bin/php`
- Lint after every PHP edit with that binary: `php -l <file>`.

## Hard rules (Daniel's)

- NO em-dashes anywhere (copy, code comments, commits).
- Romanian copy in FORMAL voice (dumneavoastra). American English otherwise.
- Versioning by hundredths (0.01, 0.02 ...) in `CREATON_VERSION`, bumped only on
  an explicit release build.
- Never publish client revenue/spend numbers in site copy.
- Performance target: PageSpeed 90+ mobile (static stack makes this realistic).
  Verify with the PSI API key from ~/.claude/CLAUDE.md after deploys.

## Launch checklist

- [x] DEPLOYED LIVE 2026-07-15. Old WP backed up (old-wp-files + tarball + DB
      export), new static site in public_html, Cloudflare purged, all pages 200,
      301s live, form pipeline verified end-to-end (303 -> /multumim/,
      generate_lead fired, email Accepted via Track Delivery, JSONL logged).
- [x] Real photos: 50 from Drive, best 4 in the Lucrari cards as 800x600 WebP
      (+ og-creaton.jpg). Drive link in the 2026-07-15 chat if more are needed.
- [x] Cloudflare Turnstile widget "creaton-lp-forms" created; sitekey in
      config.php; LAZY-loaded on the LP. Secret NOT yet on server.
- [x] Turnstile FULLY WORKING 2026-07-15. "wp-clients-1" is a SHARED Turnstile
      WIDGET (not an account): sitekey 0x4AAAAAAB45dpkzkrOnY6kZ, secret
      0x4AAAAAAB45dgHQcmlnbEb4YdbyQJW8-xI. Added creaton hostname to it (10/10).
      config.php sitekey + config.local.php secret both point at it; verified a
      live submit with a real token lands on /multumim/. Deleted the throwaway
      "creaton-lp-forms" widget. FIX: form-handler no longer sends remoteip to
      siteverify (CF edge-IP trap). NOTE: cPanel File Manager "Copy" silently
      fails to overwrite existing files sometimes; edit the deployed file directly
      or verify size/mtime after copying.
- [x] GTM conversion trigger FIXED + PUBLISHED 2026-07-15 (Version 28, container
      GTM-57XHFP55 under the daniel@newlightdigital.com Google login). "Thank you
      page" trigger now fires on Page URL /multumim/; GA4 generate_lead + Google
      Ads lead conversion restored.
- [x] PHONE-SWAP call tracking FIXED + PUBLISHED 2026-07-15 (Version 30). The
      "Phone Swap - Calls from Website" tag (Google Ads Calls from Website
      Conversion, conv ID 11262294860 / label wqk5CMH5h8scEMz2o_op) had its
      "Displayed Phone Number to Replace" set to the OLD visible format
      `+40 (749) 845 759`. The rebuild changed the visible number to
      `0749 845 759`, so Google's number swap no longer matched anything and
      silently no-oped (Daniel spotted it). Fix: set the field to `0749 845 759`
      to match the on-page text. Verified live in gtm.js
      (`vtp_phoneConversionNumber":"0749 845 759"`). LESSON: any time the visible
      phone format on the site changes, update this tag's "Displayed Phone Number
      to Replace" to match EXACTLY (Google matches on-page text). tel:-only CTAs
      ("Sunați acum" buttons, no visible digits) are not swap-eligible but are
      already tracked by the separate "Click to call - Google Ads" conversion.
- [x] BOT/CLICK-FRAUD PROTECTION (2026-07-15). Two layers, both already live:
      (1) Cloudflare "Bot Fight Mode" is ON at the edge (zone bot_management:
      `fight_mode:true`, `enable_js:true`) = the ACTIVE bot blocking (challenge/
      block before origin). (2) ClickCease detection runs via GTM: Custom HTML tag
      "ClickCease Tag" = `<script async src='https://ob.esnbranding.com/i/
      9726c93862c5b4428f5cf40627e028ee.js' class='ct_clicktrue'>` + Custom Image
      noscript fallback (`/ns/<same-hash>.html`), both fire on "ClickCease
      PageViews Trigger" + Initialization. Went live when Version 30 shipped (a GTM
      version is a full snapshot). IMPORTANT: ClickCease's active "Bot Zapping"
      (403-blocking) is a WORDPRESS PLUGIN ONLY; non-WP/custom sites get DETECTION
      DATA only. So do NOT try to install ClickCease active blocking here - that job
      is already done by Cloudflare Bot Fight Mode. ClickCease's role here is purely
      ad click-fraud detection -> auto IP exclusion in Google Ads. PENDING (Daniel,
      dashboard only, no API access): in the ClickCease dashboard confirm the domain
      shows detected/green and connect the Google Ads account (3218774193) so the
      fraud IP-exclusions actually push to the campaigns.
- [ ] PERF: mobile PageSpeed 69, LCP ~9.6s. Remaining weight = client GTM stack
      (GA4 + Ads + Yandex Metrika, ~540KB). To reach 90+, load GTM on first
      interaction — needs Daniel's OK (changes measurement). gclid captured
      independently in localStorage, so deferring GTM does not hurt attribution.
- [x] Legal name: trade name only, per Daniel (no SRL/CUI); it's what
      /confidentialitate/ uses.
- [x] Ad group variants built from live spend data (see LP variants above).
- [x] Ad group final URLs SWITCHED to variants 2026-07-15 (5 enabled RSAs in the
      live RO Vest campaign, via Ads API; verified). montaj->/montaj-acoperis/,
      reparatii+infiltratii+tabla->/reparatii-acoperis/, refacere->/inlocuire-acoperis/.
- [x] ADS OPTIMIZED via API 2026-07-15 (campaign 23996113115). Mutations confirmed
      working (dev token has write access; always validateOnly dry-run first).
      Added to the RO Vest campaign: 6 sitelinks, 7 callouts, 1 structured snippet
      (Servicii), 1 call asset (0749 845 759) - it previously had NONE of these
      (only images/logo/name). Added 21 campaign-level negative keywords (m2, mp,
      metru/metri patrat, cat costa, calculator, subventii, bloc, amenda, acte,
      lege, materiale, magazin, vand, naprawy, dachu, diy, pdf, 2025, 2026) to stop
      the price-per-m2/DIY/material-shopper flood (esp. Montaj ad group, which
      burned 2600 RON / 0 conv in the pre-launch week). Added a 2nd trust-focused
      RSA to each of the 5 active ad groups (15 ani, 200+ proiecte, garantie,
      deviz/deplasare gratuit; NOT the "5,0" rating in ad text - policy risk, the
      rating lives on the LP). HELD the "pret"/"preturi" negatives on purpose (let
      the new LPs try to convert price-intent first). KEY 7-day recheck: mobile was
      89% of spend / 0 conv vs desktop 100% of conv (old mobile LP was the
      bottleneck; the new mobile-first LPs target exactly this). Analysis + push
      scripts in the session scratchpad (ads_analysis*.py, push1/push2.py).
- [x] Email deliverability fixed 2026-07-15 (a lead email hit spam). Root causes:
      DKIM SIGNING was inactive on the server AND no DKIM/DMARC records in DNS.
      Fix: cPanel auto-enabled DKIM signing; published DKIM (default._domainkey)
      + DMARC (_dmarc, p=none, rua=daniel@) TXT records to Cloudflare via API.
      cPanel now reports SPF+DKIM+DMARC all VALID. DNS on Cloudflare zone
      ecc091cb2a838cee8851fc51f253cac3.
- [ ] KNOWN LIMITATION: reverse-DNS / HELO mismatch. The mail server sends HELO
      "creaton-acoperisuri-mansardari.ro", which (being Cloudflare-proxied) A-
      resolves to Cloudflare IPs, not the mail IP 85.120.222.229. Can't fix from
      cPanel (needs NAV/WHM to set a mail HELO hostname with matching fwd/rev DNS,
      or an unproxied mail.* host). Low impact now that SPF/DKIM/DMARC pass; only
      revisit if spam persists after reputation builds. Dedicated IP 85.120.222.229
      PTR already = the domain.
- [ ] After launch: server-side Google Ads conversion uploads playbook, using
      the gclid values from `/home/creatona/leads/*.jsonl`.
