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
        CURLOPT_POSTFIELDS     => http_build_query([
            'secret'   => CREATON_TURNSTILE_SECRET,
            'response' => $token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
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

$stamp = date('d.m.Y H:i');
$lines = [
    'Lead nou de pe ' . parse_url(CREATON_BASE_URL, PHP_URL_HOST),
    '',
    'Nume:     ' . $nume,
    'Telefon:  ' . $telefon,
    'Lucrare:  ' . ($lucrare !== '' ? $lucrare : '(neselectata)'),
    '',
    'Pagina:   ' . $pagina . ' (formular ' . $loc . ')',
    'Data:     ' . $stamp,
];
foreach ($adids as $k => $v) {
    $lines[] = strtoupper($k) . ':    ' . $v;
}
$body = implode("\r\n", $lines) . "\r\n";

$host    = parse_url(CREATON_BASE_URL, PHP_URL_HOST);
$headers = 'From: Creaton Website <no-reply@' . $host . ">\r\n"
         . "Content-Type: text/plain; charset=UTF-8\r\n";
if (defined('CREATON_LEAD_BCC') && CREATON_LEAD_BCC !== '') {
    $headers .= 'Bcc: ' . CREATON_LEAD_BCC . "\r\n";
}

$subject = 'Lead nou: ' . $nume . ' - ' . ($lucrare !== '' ? $lucrare : 'solicitare oferta');
@mail(CREATON_EMAIL, '=?UTF-8?B?' . base64_encode($subject) . '?=', $body, $headers);

// Jurnal local (in afara webroot) pentru incarcari ulterioare de conversii offline.
$log_dir = dirname(__DIR__) . '/leads';
if (is_dir($log_dir) || @mkdir($log_dir, 0700)) {
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

header('Location: /multumim/?p=' . rawurlencode($pagina) . '&l=' . $loc, true, 303);
exit;
