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
- `public/` maps to `public_html/`. Deploy root has `.htaccess` 301s + security
  headers; `_app/` is `Require all denied` (verified 403). Never edit the server
  by hand except config.local.php (secret) which is not in git.
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
