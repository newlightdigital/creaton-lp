<?php
// Creaton LP pack: global settings shared by every page and the form handler.
// Secrets (Turnstile secret key) belong in config.local.php, which is gitignored.

const CREATON_VERSION       = '0.01';
const CREATON_BASE_URL      = 'https://creaton-acoperisuri-mansardari.ro';
const CREATON_PHONE_E164    = '+40749845759';
const CREATON_PHONE_DISPLAY = '0749 845 759';
const CREATON_EMAIL         = 'office@creaton-acoperisuri-mansardari.ro';
const CREATON_GTM_ID        = 'GTM-57XHFP55';
const CREATON_FB_URL        = 'https://www.facebook.com/creatonacoperisurimansardari';
const CREATON_YT_URL        = 'https://www.youtube.com/@CREATON-ACOPERISURI-MANSARDARI';

// Cloudflare Turnstile site key (public). Empty = widget off; honeypot still active.
// Widget "creaton-lp-forms" in Daniel's CF account, domains: production + localhost.
const CREATON_TURNSTILE_SITEKEY = '0x4AAAAAAD2YterhN7ToUT8n';

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
