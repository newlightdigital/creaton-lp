<?php
// Primeste lead-urile din formularele LP, le valideaza si le trimite pe email,
// apoi redirectioneaza catre /multumim/ (unde GTM declanseaza conversia).

require __DIR__ . '/_app/config.php';

function creaton_back(string $qs = 'err=1'): void
{
    $ref  = $_SERVER['HTTP_REFERER'] ?? '/';
    $path = parse_url($ref, PHP_URL_PATH) ?: '/';
    // Doar path-uri interne, fara handler in bucla.
    if ($path === '/form-handler.php' || !str_starts_with($path, '/')) {
        $path = '/';
    }
    header('Location: ' . $path . '?' . $qs . '#oferta', true, 303);
    exit;
}

// Trimite mailul direct prin serverul SMTP local (Exim pe 127.0.0.1:25). Necesar
// pentru ca mail() este dezactivat pe acest hosting. Fara autentificare: livrarea
// locala si releul de pe localhost sunt permise pentru scripturile de pe server.
function creaton_smtp_send(string $envelope_from, array $rcpts, string $from_header, string $to_header, string $subject, string $body, string $content_type = 'text/plain; charset=UTF-8', string $reply_to = ''): bool
{
    $port = defined('CREATON_SMTP_PORT') ? (int) CREATON_SMTP_PORT : 25;
    $fp = @fsockopen('127.0.0.1', $port, $errno, $errstr, 8);
    if (!$fp) {
        return false;
    }
    stream_set_timeout($fp, 8);
    $get = function () use ($fp) {
        $data = '';
        while (($line = fgets($fp, 515)) !== false) {
            $data .= $line;
            if (strlen($line) < 4 || $line[3] === ' ') {
                break;
            }
        }
        return $data;
    };
    $put = function (string $cmd) use ($fp, $get) {
        fwrite($fp, $cmd . "\r\n");
        return $get();
    };
    $ok = function (string $resp, string $code) {
        return strncmp($resp, $code, 3) === 0;
    };
    $done = false;
    try {
        if ($ok($get(), '220')) {
            $ehlo_host = $_SERVER['SERVER_NAME'] ?? 'localhost';
            if (!$ok($put('EHLO ' . $ehlo_host), '250')) {
                $put('HELO ' . $ehlo_host);
            }
            if ($ok($put('MAIL FROM:<' . $envelope_from . '>'), '250')) {
                $all_rcpt = true;
                foreach ($rcpts as $r) {
                    if (!$ok($put('RCPT TO:<' . $r . '>'), '250')) {
                        $all_rcpt = false;
                        break;
                    }
                }
                if ($all_rcpt && $ok($put('DATA'), '354')) {
                    $msg = 'From: ' . $from_header . "\r\n"
                         . 'To: ' . $to_header . "\r\n"
                         . 'Subject: ' . $subject . "\r\n"
                         . "MIME-Version: 1.0\r\n"
                         . 'Content-Type: ' . $content_type . "\r\n"
                         . ($reply_to !== '' ? 'Reply-To: ' . $reply_to . "\r\n" : '')
                         . "\r\n"
                         . $body;
                    $msg  = preg_replace('/^\./m', '..', $msg); // dot-stuffing
                    $done = $ok($put($msg . "\r\n."), '250');
                }
            }
        }
        $put('QUIT');
    } catch (\Throwable $e) {
        $done = false;
    }
    fclose($fp);
    return $done;
}

// Construieste corpul HTML on-brand al notificarii de lead (graphite + amber, ca
// site-ul). Fara imagini/SVG (Gmail le blocheaza): layout pe tabele, stiluri inline,
// fonturi de sistem. Butoanele tel:/wa.me permit contactarea clientului cu un tap.
function creaton_lead_email_html(string $nume, string $telefon, string $lucrare, string $pagina, string $loc, array $adids, string $host): string
{
    $tel_clean = preg_replace('/[^\d+]/', '', $telefon);
    $digits    = preg_replace('/\D+/', '', $telefon);
    $wa        = ($digits !== '' && $digits[0] === '0') ? '40' . substr($digits, 1) : $digits;
    $site      = 'https://' . $host . '/';
    $data      = date('d.m.Y H:i');

    $lucrare_cell = $lucrare !== ''
        ? '<span style="display:inline-block;background:#1E2A32;color:#ffffff;border-radius:999px;padding:4px 12px;font-size:12px;font-weight:700;line-height:1.4;">' . e($lucrare) . '</span>'
        : '<span style="color:#9aa7b0;">(neselectată)</span>';

    $adrows = '';
    foreach ($adids as $k => $v) {
        $adrows .= strtoupper(e((string) $k)) . ': ' . e((string) $v) . '<br>';
    }
    $adblock = $adrows !== ''
        ? '<tr><td style="padding:4px 28px 18px;"><div style="background:#f4f6f8;border-radius:8px;padding:12px 14px;font-family:\'Courier New\',Courier,monospace;font-size:11px;line-height:1.7;color:#8a97a1;word-break:break-all;">' . $adrows . '</div></td></tr>'
        : '';

    $wa_btn = $wa !== ''
        ? '<td style="width:10px;font-size:0;line-height:0;">&nbsp;</td><td style="border-radius:8px;background:#1E2A32;"><a href="https://wa.me/' . e($wa) . '" style="display:inline-block;padding:13px 22px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;color:#ffffff;text-decoration:none;">Trimiteți WhatsApp</a></td>'
        : '';

    return '<!DOCTYPE html>
<html lang="ro"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Lead nou</title></head>
<body style="margin:0;padding:0;background:#f4f6f8;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f6f8;"><tr><td align="center" style="padding:24px 12px;">
<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="width:100%;max-width:600px;background:#ffffff;border:1px solid #e6eaee;border-radius:14px;overflow:hidden;">
  <tr><td style="background:#1E2A32;padding:22px 28px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0"><tr>
    <td style="font-family:Arial,Helvetica,sans-serif;vertical-align:middle;">
      <span style="font-size:20px;font-weight:800;letter-spacing:2px;color:#ffffff;">CREATON</span>
      <span style="display:block;font-size:11px;letter-spacing:1px;color:#9fb2bd;text-transform:uppercase;margin-top:3px;">Acoperișuri &middot; Mansardări</span>
    </td>
    <td align="right" style="font-family:Arial,Helvetica,sans-serif;font-size:12px;font-weight:700;letter-spacing:1px;color:#F5972A;text-transform:uppercase;vertical-align:middle;">Lead nou</td>
  </tr></table></td></tr>
  <tr><td style="height:4px;background:#F5972A;font-size:0;line-height:0;">&nbsp;</td></tr>
  <tr><td style="padding:26px 28px 6px;font-family:Arial,Helvetica,sans-serif;">
    <div style="font-size:19px;font-weight:700;color:#1E2A32;">Solicitare nouă de ofertă</div>
    <div style="font-size:13px;color:#5b6b76;margin-top:5px;">Un client a completat formularul de pe <a href="' . e($site) . '" style="color:#E17E12;text-decoration:none;">' . e($host) . '</a>.</div>
  </td></tr>
  <tr><td style="padding:16px 28px 2px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-family:Arial,Helvetica,sans-serif;font-size:14px;color:#1E2A32;">
    <tr><td style="padding:11px 0;color:#7a8994;width:110px;border-bottom:1px solid #eef1f4;vertical-align:top;">Nume</td><td style="padding:11px 0;font-weight:700;border-bottom:1px solid #eef1f4;">' . e($nume) . '</td></tr>
    <tr><td style="padding:11px 0;color:#7a8994;border-bottom:1px solid #eef1f4;vertical-align:top;">Telefon</td><td style="padding:11px 0;border-bottom:1px solid #eef1f4;"><a href="tel:' . e($tel_clean) . '" style="color:#1E2A32;font-weight:700;text-decoration:none;">' . e($telefon) . '</a></td></tr>
    <tr><td style="padding:11px 0;color:#7a8994;border-bottom:1px solid #eef1f4;vertical-align:top;">Lucrare</td><td style="padding:11px 0;border-bottom:1px solid #eef1f4;">' . $lucrare_cell . '</td></tr>
    <tr><td style="padding:11px 0;color:#7a8994;vertical-align:top;">Pagina</td><td style="padding:11px 0;color:#41525c;">' . e($pagina) . ' <span style="color:#9aa7b0;">(formular ' . e($loc) . ')</span></td></tr>
    <tr><td style="padding:0 0 4px;color:#7a8994;vertical-align:top;">Data</td><td style="padding:0 0 4px;color:#41525c;">' . e($data) . '</td></tr>
  </table></td></tr>
  <tr><td style="padding:18px 28px 22px;"><table role="presentation" cellpadding="0" cellspacing="0"><tr>
    <td style="border-radius:8px;background:#F5972A;"><a href="tel:' . e($tel_clean) . '" style="display:inline-block;padding:13px 24px;font-family:Arial,Helvetica,sans-serif;font-size:14px;font-weight:700;color:#1E2A32;text-decoration:none;">Sunați clientul</a></td>
    ' . $wa_btn . '
  </tr></table></td></tr>
  ' . $adblock . '
  <tr><td style="padding:16px 28px 24px;border-top:1px solid #eef1f4;font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#9aa7b0;line-height:1.6;">Acest e-mail a fost generat automat de site-ul ' . e($host) . '. Lead-ul este salvat și în jurnalul intern de lead-uri.</td></tr>
</table>
<div style="font-family:Arial,Helvetica,sans-serif;font-size:11px;color:#b6c0c8;margin-top:14px;">Creaton Acoperișuri Mansardări</div>
</td></tr></table>
</body></html>';
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Location: /', true, 303);
    exit;
}

// Honeypot: campul ascuns trebuie sa ramana gol. Botul care il completeaza
// primeste pagina de multumire, dar lead-ul nu se trimite.
if (trim((string) ($_POST['_hp'] ?? '')) !== '') {
    header('Location: /multumim/', true, 303);
    exit;
}

$nume    = trim((string) ($_POST['nume'] ?? ''));
$telefon = trim((string) ($_POST['telefon'] ?? ''));
$digits  = preg_replace('/\D+/', '', $telefon);

if ($nume === '' || mb_strlen($nume) > 120 || strlen($digits) < 9 || strlen($digits) > 15) {
    creaton_back();
}

$lucrari_valide = [
    'Înlocuire acoperiș', 'Montaj acoperiș nou', 'Renovare sau restaurare acoperiș',
    'Reparații și infiltrații', 'Mansardare', 'Hidroizolații și terase', 'Altă lucrare',
];
$lucrare = (string) ($_POST['lucrare'] ?? '');
if (!in_array($lucrare, $lucrari_valide, true)) {
    $lucrare = '';
}

$pagina = preg_replace('/[^a-z0-9-]/', '', (string) ($_POST['pagina'] ?? '')) ?: 'necunoscuta';
$loc    = ($_POST['loc'] ?? '') === 'final' ? 'final' : 'hero';
$adids  = [];
foreach (['gclid', 'gbraid', 'wbraid'] as $k) {
    $v = trim((string) ($_POST[$k] ?? ''));
    if ($v !== '' && strlen($v) < 200 && preg_match('/^[A-Za-z0-9_.-]+$/', $v)) {
        $adids[$k] = $v;
    }
}

// Cloudflare Turnstile: verificam doar daca cheia secreta este configurata.
if (defined('CREATON_TURNSTILE_SECRET') && CREATON_TURNSTILE_SECRET !== '') {
    $token = (string) ($_POST['cf-turnstile-response'] ?? '');
    $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
    curl_setopt_array($ch, [
        CURLOPT_POST           => true,
        // NU trimitem remoteip: in spatele Cloudflare, REMOTE_ADDR este IP-ul
        // edge-ului CF (nu al vizitatorului), iar siteverify respinge tokenul
        // valid daca remoteip nu se potriveste. secret + response sunt suficiente.
        CURLOPT_POSTFIELDS     => http_build_query([
            'secret'   => CREATON_TURNSTILE_SECRET,
            'response' => $token,
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 5,
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);
    if ($raw !== false) { // daca verificarea nu raspunde, lead-ul trece (fail-open)
        $res = json_decode($raw, true);
        if (empty($res['success'])) {
            creaton_back();
        }
    }
}

// Capturam lead-ul pe disc INTAI, ca sa nu pierdem niciodata un lead din cauza
// unei erori de mediu (mail dezactivat, permisiuni etc.). Jurnalul JSONL este in
// afara webroot-ului si sursa de adevar pentru incarcarile de conversii offline.
$log_dir = dirname(__DIR__) . '/leads';
if (@is_dir($log_dir) || @mkdir($log_dir, 0700, true)) {
    $row = [
        'ts' => date('c'), 'nume' => $nume, 'telefon' => $telefon,
        'lucrare' => $lucrare, 'pagina' => $pagina, 'loc' => $loc,
    ] + $adids;
    @file_put_contents(
        $log_dir . '/leads-' . date('Y-m') . '.jsonl',
        json_encode($row, JSON_UNESCAPED_UNICODE) . "\n",
        FILE_APPEND | LOCK_EX
    );
}

// Trimitem lead-ul pe email. Best-effort: orice esec (inclusiv mail() dezactivat
// pe hosting) NU trebuie sa produca 500 sau sa piarda lead-ul (deja pe disc).
try {
    $host  = parse_url(CREATON_BASE_URL, PHP_URL_HOST);
    $lines = [
        'Lead nou de pe ' . $host,
        '',
        'Nume:     ' . $nume,
        'Telefon:  ' . $telefon,
        'Lucrare:  ' . ($lucrare !== '' ? $lucrare : '(neselectata)'),
        '',
        'Pagina:   ' . $pagina . ' (formular ' . $loc . ')',
        'Data:     ' . date('d.m.Y H:i'),
    ];
    foreach ($adids as $k => $v) {
        $lines[] = strtoupper($k) . ':    ' . $v;
    }
    $text_body = implode("\r\n", $lines) . "\r\n";
    $html_body = creaton_lead_email_html($nume, $telefon, $lucrare, $pagina, $loc, $adids, (string) $host);

    // multipart/alternative: partea text pentru clienti simpli / filtre anti-spam +
    // partea HTML on-brand. Ambele base64, ca diacriticele UTF-8 sa treaca sigur pe
    // transportul 7-bit (si base64 nu produce linii care incep cu ".", deci
    // dot-stuffing-ul din SMTP nu le atinge).
    $boundary  = 'creaton_' . bin2hex(random_bytes(12));
    $mime_body =
          '--' . $boundary . "\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($text_body))
        . '--' . $boundary . "\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: base64\r\n\r\n"
        . chunk_split(base64_encode($html_body))
        . '--' . $boundary . "--\r\n";
    $content_type = 'multipart/alternative; boundary="' . $boundary . '"';

    $subject = '=?UTF-8?B?' . base64_encode('Lead nou: ' . $nume . ' - ' . ($lucrare !== '' ? $lucrare : 'solicitare oferta')) . '?=';

    $envelope_from = 'no-reply@' . $host;
    $from_header   = 'Creaton Website <' . $envelope_from . '>';
    $rcpts         = [CREATON_EMAIL];
    if (defined('CREATON_LEAD_BCC') && CREATON_LEAD_BCC !== '') {
        $rcpts[] = CREATON_LEAD_BCC;
    }

    // mail() intai (daca hosting-ul il are activat), altfel SMTP local (Exim).
    $sent = false;
    if (function_exists('mail')) {
        $headers = 'From: ' . $from_header . "\r\n"
                 . 'Reply-To: ' . CREATON_EMAIL . "\r\n"
                 . "MIME-Version: 1.0\r\n"
                 . 'Content-Type: ' . $content_type . "\r\n";
        if (defined('CREATON_LEAD_BCC') && CREATON_LEAD_BCC !== '') {
            $headers .= 'Bcc: ' . CREATON_LEAD_BCC . "\r\n";
        }
        $sent = @mail(CREATON_EMAIL, $subject, $mime_body, $headers);
    }
    if (!$sent) {
        creaton_smtp_send($envelope_from, $rcpts, $from_header, CREATON_EMAIL, $subject, $mime_body, $content_type, CREATON_EMAIL);
    }
} catch (\Throwable $e) {
    // Lead-ul e deja salvat pe disc; nu blocam raspunsul.
}

header('Location: /multumim/?p=' . rawurlencode($pagina) . '&l=' . $loc, true, 303);
exit;
