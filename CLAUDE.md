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
  relayed out. Verified live via Track Delivery (all "Accepted"). The lead email
  is multipart/alternative (2026-07-22): a plain-text part (simple clients / spam
  filters) + an on-brand HTML part built by `creaton_lead_email_html()` (graphite
  + amber like the site, image/SVG-free table layout so Gmail renders it, tel: /
  wa.me one-tap buttons to the lead's number); both parts base64 so the UTF-8
  diacritics survive the 7-bit path. `creaton_smtp_send()` takes a Content-Type +
  Reply-To so the same sender carries either format. Everything in the mail path
  is wrapped so a failure can never 500 or lose a lead.
- Turnstile is LAZY-LOADED (injected on first form focus/submit), not eager;
  eager loading made it the LCP element (~400KB challenge JS) and tanked mobile
  PageSpeed. Server-side Turnstile verify is OFF until the secret is added to
  config.local.php on the server (honeypot active meanwhile).
- Conversion flow: GTM (GTM-57XHFP55) is LAZY-loaded on the LP (first interaction
  or a 3s fallback) so the ~640KB analytics stack stays off the critical mobile
  render path -- this is the mobile-LCP win (eager GTM pinned LCP at ~9.6s; lazy =
  ~3.3s / score 87). CRITICAL: a deferred GTM misses the very tap that wakes it, so
  the tap conversions do NOT use GTM auto-event Click triggers. Instead a tiny
  delegation listener in page.php pushes `lp_call_click` (tel:), `lp_whatsapp_click`
  (wa.me) and `lp_form_start` (form focus) to the dataLayer the instant they happen;
  the three GTM triggers "Declanșator Telefon Site / Whatsapp / Start Formular" were
  switched from Click to Custom Event triggers matching those exact event names
  (GTM v32, 2026-07-20), and GTM replays the queue when it loads. DO NOT rename these
  events or revert those triggers to Click -- either one alone silently zeroes call /
  WhatsApp / form-start conversions (verified live 2026-07-20: click_whatsapp +
  click_to_call GA4 events and the Google Ads conv 11262294860 pings all fire through
  the deferred GTM). Form-COMPLETE still fires on `generate_lead` on `/multumim/`,
  which loads GTM EAGERLY (standalone file), so the lead conversion is never deferred.
  gclid/gbraid/wbraid persisted in localStorage 90 days, submitted with each lead.
- CONSENT (2026-07-23): self-hosted Consent Mode v2 CMP, ported from acoperix.ro
  (same code, restyled to the graphite + amber tokens). Three pieces:
  `_app/consent.php` (the gtag consent DEFAULTS, everything denied except
  security_storage, + wait_for_update 500 + url_passthrough; also re-applies a
  stored choice inline on later pageviews), `assets/js/consent.js` (banner +
  preferences panel, 4 categories with strictly-necessary locked, writes the
  `creaton_consent` cookie for 12 months, clears GA/Ads/Yandex cookies on
  withdrawal; API `window.creatonCookies.show()/.showPrefs()/.state()`), and
  `consent-log.php` (proof of consent per GDPR art. 7(1), appended to
  `/home/creatona/creaton-consent.log`, OUTSIDE the webroot, truncated IP).
  consent.php is required as the FIRST thing in <head> on page.php, /multumim/
  and /confidentialitate/. RULE: it must stay ahead of every GTM loader, or tags
  fire ungated. Any `[data-creaton-cookies]` link reopens the panel (there is one
  in the LP footer and in the policy). Bump VERSION in consent.js to re-ask
  everyone after a material change. NOTE: ad_storage is denied until the visitor
  accepts, so Ads/GA4 see fewer cookie-based conversions than before (modeling
  fills part of the gap) - expected, and it lands mid Maximize-Conversions
  learning. DELIBERATE, do NOT "fix" without asking (Daniel, 2026-07-23): the
  NON-Google tags stay UNGATED. Consent Mode only gates the Google tags
  automatically; Yandex Metrica (Custom HTML in GTM) and ClickCease (loaded
  directly in page.php, ahead of consent) were both left firing regardless of
  consent, on Daniel's explicit call after I offered to add per-tag consent
  checks. consent.js still clears the `_ym_*` cookies when analytics is refused.
  Same for the gclid/gbraid localStorage capture in page.php, which still runs
  before consent: it is first-party and only feeds lead attribution, so it stays
  as is unless we decide to be strict.

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
- `/jgheaburi-burlane/` <- ad group "Jgheaburi / Burlane" (id 199300572675,
  CREATED 2026-07-22; gutters / sisteme pluviale - see checklist)
- `/` general <- ad group "General / Firma Acoperisuri" (kw: firma acoperisuri,
  servicii acoperisuri) + any traffic without a dedicated page
RO Vest now runs 7 ad groups -> 5 LP targets (the /reparatii-acoperis/ page
catches 3 repair-intent groups: Reparatii Acoperisuri, Infiltratii, Reparatii
Tabla / Tigla). All ad-group final URLs verified via API 2026-07-22.
Google Ads API access works: creds in `~/.nld_ads_creds.py` (adwords scope,
API v21, login-customer-id = NLD MCC). Read-only queries are fine; MUTATIONS
(new ad groups, changing ads/final URLs) only with Daniel's explicit go.

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
- DEPLOY (2026-07-18, PRIMARY + preferred): just `git push` to main. A GitHub
  Action (`.github/workflows/deploy.yml`) auto-deploys `public/` to `public_html`
  via FTPS on every push touching `public/` (verified working). FTP account
  `daniel@creaton-acoperisuri-mansardari.ro` is jailed to public_html; creds in
  `~/.creaton_ftp.py` (local, chmod 600) and repo GitHub Secrets FTP_SERVER /
  FTP_USERNAME / FTP_PASSWORD. Excludes config.local.php; `/leads/` sits outside the
  jail so it is never touched. For an on-demand deploy without git, use Python
  `ftplib.FTP_TLS` (host cp04.server.ro, port 21, `ssl._create_unverified_context()`,
  `prot_p()`) or curl `--ssl-reqd`; upload to a `.tmp` name then `rename` for an
  atomic swap (no half-written 500s). This REPLACED the cPanel File Manager dance
  below, which is now only an emergency fallback (needs a live cPanel session, which
  expires and cannot be re-created by the assistant - hence the FTP move).
- DEPLOY MECHANISM (no shell, LEGACY fallback): cPanel Git Version Control. A clone lives at
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
- [x] COOKIE CONSENT LIVE 2026-07-23: ported the acoperix.ro CMP (Consent Mode v2,
      deny by default) to every page - see the Stack bullet for the file layout and
      the ordering rule. Also aligned `/confidentialitate/`: the legal basis for
      traffic/campaign measurement moved from legitimate interest (art. 6(1)(f)) to
      CONSENT (art. 6(1)(a), given via the banner), plus a new "Cookie-uri și
      tehnologii similare" section listing the 4 categories and the concrete cookies
      (creaton_consent, _ga/_ga_*, _gcl_au/_gcl_aw, _ym_*), a "Modificați
      preferințele" link and the withdraw-consent right. Page title is now
      "Politica de confidențialitate și de cookie-uri". Verified live: defaults
      denied -> stored choice re-applied -> gtm.js, in that order, on all 5 LP
      variants + /multumim/; consent-log.php returns 405 on GET (POST only).
      DEPLOY GOTCHA: this push did NOT auto-trigger the Action (no run was created
      even though it touched public/**). Fixed with `gh workflow run deploy.yml
      --ref main` (the workflow has workflow_dispatch enabled). Check that a run
      actually exists after pushing; do not assume the push deployed.
- [x] GUTTERS FUNNEL ADDED 2026-07-22: built the `/jgheaburi-burlane/` LP variant
      (message match for montaj / reparatii / inlocuire jgheaburi si burlane; also
      added a "Jgheaburi si burlane" option to the lead-form service dropdown in
      page.php so the variant preselects it), then on Daniel's explicit go CREATED a
      new "Jgheaburi / Burlane" ad group (id 199300572675) in the LIVE RO Vest
      campaign (23996113115) via the Ads API. 13 keywords (phrase + exact: montaj /
      reparatii / inlocuire jgheaburi, jgheaburi si burlane, montaj burlane, jgheaburi
      tabla, sistem / sisteme pluviale, + timisoara / cluj) and 2 RSAs, final URL =
      the new LP, display path /jgheaburi/burlane, ASCII copy (no diacritics) + the
      "Nu Vindem Materiale" theme to match the other ad groups. Built ATOMICALLY:
      googleAds:mutate with a temp ad-group resource name (customers/CID/adGroups/-1)
      referenced by the criteria + ads, so all 16 ops apply in one call; validateOnly
      dry-run FIRST, then --live (all OK). Fresh ads/keywords land UNDER_REVIEW /
      strength PENDING at creation (normal Google review latency, not an error). LP
      shipped via the usual git push -> Action; verified live HTTP 200. Bidding stays
      MAXIMIZE_CONVERSIONS so the ad group needs no CPC bid; it enters learning like
      any new one.
- [x] PRICE-INTENT NEGATIVES ADDED 2026-07-18 (was undocumented): reversed the
      2026-07-15 decision to HOLD price terms - the new mobile LPs were not converting
      price-shoppers well enough to justify the spend. Added 6 campaign-level BROAD
      negatives to RO Vest (23996113115): "pret" and "preturi" (each in plain and
      diacritic spelling = 4 keywords) plus "pret pe m2" and "cat costa acoperis". RO
      Vest now carries 27 broad negatives (the 21 from 07-15 + these 6). Verified via
      campaign_criterion query 2026-07-22.
- [x] CRITICAL BUG FOUND + FIXED 2026-07-20: form-lead conversions were INVISIBLE in the
      account because the GTM "Formular Completat - Google Ads" tag fired to the WRONG
      Google Ads account -- conversion ID AW-11226994680 / label UB4VCNGeN-QeHZbZOeQp (a
      different account, almost certainly copy-pasted from another container's setup). The
      account's real id is AW-11262294860 and the correct Formular Completat snippet is
      AW-11262294860/tR0VCKzoiqgcEMz2o_op. Fixed the tag ID + label, published GTM v33;
      verified live on /multumim/ (conversion now fires to 11262294860 with the gclid
      attached via the _gcl_aw cookie the LP Conversion Linker sets). WhatsApp/call/phone-
      swap were always on the correct 11262294860, so only the FORM channel was affected
      (low volume: only 2-3 form leads total, ever). Lead EMAILS were never affected (that
      pipeline is server-side / separate). NOTE: enhanced conversions "Provide new customer
      data" on this tag is wired to {{DL - Email/First/Last/Phone}} which are EMPTY on
      /multumim/ (generate_lead push carries none) -- harmless (gclid attribution works) but
      could be improved later by pushing hashed email/phone on /multumim/. HOW TO GET A
      CONVERSION'S CORRECT ID+LABEL via Ads API: conversion_action.tag_snippets -> event
      _snippet send_to = "AW-<id>/<label>"; account id = customer.conversion_tracking
      _setting.conversion_tracking_id (= 11262294860 for 3218774193).
- [ ] PAST form-lead recovery (offline conv upload): only Pradan Adrian (Jul 17) has a
      gclid in /leads/*.jsonl (Speriosu Jul 19 has NONE -> not recoverable). Legacy Ads API
      ConversionUploadService.UploadClickConversions is BLOCKED for this account
      (CUSTOMER_NOT_ALLOWLISTED; Google now pushes the Data Manager API for new
      integrations). Prepared a manual-upload CSV at ~/Downloads/creaton-conversion-
      recovery.csv (Google Ads > Goals/Tools > Conversions > Uploads). 1 conversion, low
      value -- Daniel's call whether to bother.
- [x] BIDDING SWITCHED 2026-07-20: RO Vest campaign (23996113115) MANUAL_CPC -> MAXIMIZE
      CONVERSIONS (no tCPA; targetCpaMicros=0), at Daniel's explicit direction via API.
      I advised AGAINST switching now and he overrode it: tracking was fixed the SAME day
      (so the trailing conversion history the algo learns from is unreliable), primary
      conversions are only ~8/mo (Smart Bidding wants ~15-30), and the real lead volume
      (Click-to-call ~97, WhatsApp ~147/mo) is SECONDARY so the algo does NOT optimize
      toward it. I offered to promote call+WhatsApp to primary for signal -- Daniel declined
      ("leave primary vs secondary as is"). Expect a rough ~1-2 wk learning period; it now
      spends the full RON500/day. REVERT if needed: campaigns:mutate manual_cpc {} with
      updateMask manualCpc (or set a bidding strategy in the UI).
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
- [x] PERF DONE 2026-07-20: mobile LCP 9.2s -> 3.3s, PageSpeed 87 (was ~69), CLS 0,
      FCP 1.0s. Fix = LAZY-load GTM (first interaction / 3s fallback) to get the
      ~640KB analytics stack off the critical path, WITHOUT losing tap conversions:
      page.php queues lp_call_click / lp_whatsapp_click / lp_form_start in the
      dataLayer on tap, and the matching GTM Custom Event triggers (v32) replay them
      when GTM loads (see Conversion flow above; verified live). Also added
      metric-matched font fallbacks (`Archivo Fallback` / `Inter Fallback` in
      inline.css, size-adjust + ascent/descent overrides from the real font metrics)
      so the web-font swap never repaints larger or shifts layout. A prior naive
      "defer GTM" attempt (Jul 18, no dataLayer queue) silently zeroed call/WhatsApp/
      form-start conversions (Jul 19: 48 clicks / 0 conv) - that is why the queue +
      Custom Event triggers are mandatory, not optional.
- [x] WHATSAPP LEAD CHANNEL RESTORED 2026-07-20: the WP->static rebuild (Jul 15)
      dropped the site's WhatsApp button, which was the top lead action on the old
      site (~147 "Click WhatsApp" conv / 30d, ~5-6/day -> 0 after relaunch). Re-added
      a floating wa.me FAB (desktop/tablet, `.wa-fab`) + a 3rd button in the sticky
      mobile bar (`.btn-wa`), both to wa.me/40749845759 (0749 845 759, Daniel
      confirmed it is the WhatsApp number) with a formal prefilled message. NOTE for
      lead-count sanity: Click-to-call (~97/30d) + Click-WhatsApp (~147/30d) are
      SECONDARY conversions, so they never show in the Ads "Conversions" column
      (which counts only Formular Completat + calls). The real contact volume is
      ~8/day, not the "6" that column shows. Campaign bid strategy = MANUAL_CPC.
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
