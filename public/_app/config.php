<?php
// Creaton LP pack: global settings shared by every page and the form handler.
// Secrets (Turnstile secret key) belong in config.local.php, which is gitignored.

const CREATON_VERSION       = '0.01';
const CREATON_BASE_URL      = 'https://creaton-acoperisuri-mansardari.ro';
const CREATON_PHONE_E164    = '+40749845759';
const CREATON_PHONE_DISPLAY = '0749 845 759';
// Lead recipient: To + Reply-To on the lead email. BCC goes to CREATON_LEAD_BCC
// (config.local.php). Per Daniel: deliver to the client's Gmail, not the server mailbox.
const CREATON_EMAIL         = 'creatonacoperisurimansardari@gmail.com';
const CREATON_GTM_ID        = 'GTM-57XHFP55';
const CREATON_FB_URL        = 'https://www.facebook.com/creatonacoperisurimansardari';
const CREATON_YT_URL        = 'https://www.youtube.com/@CREATON-ACOPERISURI-MANSARDARI';

// Social proof: Google rating shown on the LP (hero badge + testimonials summary).
// Bump the count as reviews grow. CREATON_REVIEW_URL is optional: paste the Google
// Business Profile reviews link and both badges become clickable (open in new tab).
const CREATON_REVIEW_SCORE  = '5,0';
const CREATON_REVIEW_COUNT  = 48;
const CREATON_REVIEW_URL    = '';

// Cloudflare Turnstile site key (public). Empty = widget off; honeypot still active.
// Shared "wp-clients-1" widget (Daniel's CF account); creaton hostname added to it.
// Secret pairs with this in config.local.php on the server.
const CREATON_TURNSTILE_SITEKEY = '0x4AAAAAAB45dpkzkrOnY6kZ';

$creaton_local = __DIR__ . '/config.local.php';
if (is_file($creaton_local)) {
    require $creaton_local; // may define CREATON_TURNSTILE_SECRET, CREATON_LEAD_BCC
}

function creaton_asset_ver(string $rel): string
{
    $abs = dirname(__DIR__) . '/' . ltrim($rel, '/');
    return is_file($abs) ? (string) filemtime($abs) : CREATON_VERSION;
}

function e(string $s): string
{
    return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}
