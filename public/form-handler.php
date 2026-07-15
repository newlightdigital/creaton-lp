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
function creaton_smtp_send(string $envelope_from, array $rcpts, string $from_header, string $to_header, string $subject, string $body): bool
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
                         . "Content-Type: text/plain; charset=UTF-8\r\n"
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
    $body    = implode("\r\n", $lines) . "\r\n";
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
                 . "Content-Type: text/plain; charset=UTF-8\r\n";
        if (defined('CREATON_LEAD_BCC') && CREATON_LEAD_BCC !== '') {
            $headers .= 'Bcc: ' . CREATON_LEAD_BCC . "\r\n";
        }
        $sent = @mail(CREATON_EMAIL, $subject, $body, $headers);
    }
    if (!$sent) {
        creaton_smtp_send($envelope_from, $rcpts, $from_header, CREATON_EMAIL, $subject, $body);
    }
} catch (\Throwable $e) {
    // Lead-ul e deja salvat pe disc; nu blocam raspunsul.
}

header('Location: /multumim/?p=' . rawurlencode($pagina) . '&l=' . $loc, true, 303);
exit;
