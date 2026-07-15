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
  Turnstile verify (fail-open), email via mail() to office@, JSONL lead log in
  `/leads/` (outside webroot, gitignored), then 303 redirect to `/multumim/`.
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

- Hosting: NAV Communications shared cPanel ("WP Silver" plan), DNS/proxy on
  Cloudflare. Old WordPress site still live until this replaces it.
- Deploy root mapping: `public/` maps to `public_html/` on the server. The
  `/leads/` dir is created next to it (one level above webroot).
- GitHub is the source of truth; deploy is a deliberate separate step (cPanel
  Git or rsync). Never edit the server directly.
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

## Launch checklist (pending)

- [x] Real photos: 50 downloaded from the client's Drive folder, best 4 picked
      and wired into the Lucrari cards as 800x600 WebP (+ og-creaton.jpg
      1200x630). Originals cached in the session scratchpad; Drive folder link
      in the 2026-07-15 chat if more are needed.
- [x] Cloudflare Turnstile: widget "creaton-lp-forms" created via API in
      Daniel's CF account (domains: production + localhost). Sitekey in
      config.php; secret in local config.local.php, RECREATE config.local.php
      ON THE SERVER at deploy (never commit it).
- [ ] Update the GTM conversion trigger from /thank-you-page/ to /multumim/
      (old WP thank-you URL 301s here). This also fixes the broken conversion
      tracking on the RO Vest campaign (see KNOWN ISSUE above).
- [x] Legal name: trade name "Creaton Acoperisuri Mansardari" per Daniel
      2026-07-15 (no SRL/CUI available); already what /confidentialitate/ uses.
- [x] Ad group variants built from live spend data (see LP variants above).
- [ ] After deploy + Daniel's go: switch each ad group's final URL to its
      variant (Ads API mutation, needs explicit approval).
- [ ] After launch: server-side Google Ads conversion uploads playbook
      (~/.claude/playbooks/google-ads-server-side-conversions.md), using the
      gclid values from the leads log.
