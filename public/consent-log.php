<?php
/* Proof-of-consent record. GDPR art. 7(1) requires us to be able to demonstrate
   that the visitor consented, so every choice made in the banner is appended here.
   Writes outside the web root (next to /leads/), never echoes anything back. */

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

$raw = file_get_contents('php://input', false, null, 0, 2048);   // hard cap, this is a tiny payload
$in  = json_decode((string)$raw, true);
if (!is_array($in)) { http_response_code(400); exit; }

$allowed = ['accept_all', 'reject_all', 'save_prefs'];
$action  = in_array(($in['action'] ?? ''), $allowed, true) ? $in['action'] : 'unknown';
$id      = preg_replace('/[^a-f0-9]/', '', substr((string)($in['id'] ?? ''), 0, 32));
$url     = preg_replace('/[^\w\-\/\.]/', '', substr((string)($in['url'] ?? ''), 0, 120));
$b       = function ($v) { return !empty($v) ? '1' : '0'; };

/* Truncated IP: enough to evidence the consent, not enough to profile the visitor.
   Behind Cloudflare, REMOTE_ADDR is the edge IP, so prefer the forwarded client IP. */
$ip = (string)($_SERVER['HTTP_CF_CONNECTING_IP'] ?? $_SERVER['REMOTE_ADDR'] ?? '');
if (strpos($ip, ':') !== false) { $p = explode(':', $ip); $ip = implode(':', array_slice($p, 0, 3)) . '::'; }
else { $p = explode('.', $ip); $ip = count($p) === 4 ? "$p[0].$p[1].$p[2].0" : ''; }

$ua = preg_replace('/[\r\n\t|]+/', ' ', substr((string)($_SERVER['HTTP_USER_AGENT'] ?? ''), 0, 180));

$line = implode(' | ', [
    date('c'),
    $id,
    $action,
    'v' . (int)($in['v'] ?? 0),
    'analytics=' . $b($in['a'] ?? 0),
    'marketing=' . $b($in['m'] ?? 0),
    'functionality=' . $b($in['f'] ?? 0),
    $url,
    $ip,
    $ua,
]) . "\n";

@file_put_contents(dirname(__DIR__) . '/creaton-consent.log', $line, FILE_APPEND | LOCK_EX);

http_response_code(204);
