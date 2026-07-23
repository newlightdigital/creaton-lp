/* Creaton consent manager. Same self-hosted CMP we run on acoperix.ro, adapted to
   the Creaton tokens (graphite + amber) and pages. Renders the banner + preferences
   panel, writes the consent cookie and drives Google Consent Mode v2. The DEFAULT
   consent state is set by the inline snippet in _app/consent.php, which runs in
   <head> BEFORE the GTM loader, so no tag can fire ungated.
   Public API: window.creatonCookies.show() / .showPrefs() / .state() */
(function () {
  'use strict';

  var COOKIE = 'creaton_consent';
  var VERSION = 1;                 // bump to re-ask everyone after a material change
  var MONTHS = 12;                 // consent expiry, then we ask again
  var POLICY = '/confidentialitate/';
  var LOGGER = '/consent-log.php'; // proof-of-consent record (GDPR accountability)

  var d = document;
  var root = null, dialog = null, lastFocus = null, view = 'banner';

  /* ---------- consent state ---------- */

  function read() {
    try {
      var m = d.cookie.match(/(?:^|;\s*)creaton_consent=([^;]*)/);
      if (!m) return null;
      var c = JSON.parse(decodeURIComponent(m[1]));
      return (c && c.v === VERSION) ? c : null;
    } catch (e) { return null; }
  }

  function write(c) {
    var exp = new Date();
    exp.setMonth(exp.getMonth() + MONTHS);
    d.cookie = COOKIE + '=' + encodeURIComponent(JSON.stringify(c)) +
      ';expires=' + exp.toUTCString() + ';path=/;SameSite=Lax' +
      (location.protocol === 'https:' ? ';Secure' : '');
  }

  function gtag() { window.dataLayer = window.dataLayer || []; window.dataLayer.push(arguments); }

  function apply(c) {
    var ads = c.m ? 'granted' : 'denied';
    gtag('consent', 'update', {
      ad_storage: ads,
      ad_user_data: ads,
      ad_personalization: ads,
      analytics_storage: c.a ? 'granted' : 'denied',
      functionality_storage: c.f ? 'granted' : 'denied',
      personalization_storage: c.f ? 'granted' : 'denied',
      security_storage: 'granted'
    });
    window.dataLayer.push({
      event: 'creaton_consent_update',
      creaton_consent_analytics: !!c.a,
      creaton_consent_marketing: !!c.m,
      creaton_consent_functionality: !!c.f
    });
    window.creatonConsent = c;
  }

  /* Drop cookies the visitor just withdrew consent for. Best effort: we can only
     clear what is readable on this host, third-party cookies are theirs to expire. */
  function forget(c) {
    var kill = [];
    if (!c.a) kill = kill.concat(['_ga', '_gid', '_gat', '_ym_uid', '_ym_d', '_ym_isad', '_ym_visorc', '_ym_hostIndex']);
    if (!c.m) kill = kill.concat(['_gcl_au', '_gcl_aw', '_gcl_dc']);
    var host = location.hostname.replace(/^www\./, '');
    d.cookie.split(';').forEach(function (raw) {
      var name = raw.split('=')[0].trim();
      var hit = kill.some(function (k) { return name === k || name.indexOf(k + '_') === 0 || name.indexOf('_ga_') === 0 && !c.a; });
      if (!hit) return;
      ['/', location.pathname].forEach(function (p) {
        ['', host, '.' + host].forEach(function (dm) {
          d.cookie = name + '=;expires=Thu, 01 Jan 1970 00:00:01 GMT;path=' + p + (dm ? ';domain=' + dm : '');
        });
      });
    });
  }

  /* Proof of consent, stored server side outside the web root. Fire and forget. */
  function record(c, action) {
    try {
      var body = JSON.stringify({ id: c.id, ts: c.ts, action: action, a: c.a ? 1 : 0, m: c.m ? 1 : 0, f: c.f ? 1 : 0, v: c.v, url: location.pathname });
      if (navigator.sendBeacon) navigator.sendBeacon(LOGGER, new Blob([body], { type: 'application/json' }));
      else { var x = new XMLHttpRequest(); x.open('POST', LOGGER, true); x.setRequestHeader('Content-Type', 'application/json'); x.send(body); }
    } catch (e) {}
  }

  function rid() {
    try {
      var a = new Uint8Array(8); (window.crypto || window.msCrypto).getRandomValues(a);
      return Array.prototype.map.call(a, function (b) { return ('0' + b.toString(16)).slice(-2); }).join('');
    } catch (e) { return String(new Date().getTime()); }
  }

  function save(a, m, f, action) {
    var c = { v: VERSION, ts: new Date().toISOString(), id: rid(), a: !!a, m: !!m, f: !!f };
    write(c); apply(c); forget(c); record(c, action); close();
  }

  /* ---------- UI ---------- */

  var CSS =
    '#crt-cc-backdrop{position:fixed;inset:0;background:rgba(16,26,34,.62);z-index:2147483000;opacity:0;transition:opacity .18s ease}' +
    '#crt-cc-backdrop.on{opacity:1}' +
    '#crt-cc{position:fixed;z-index:2147483001;left:50%;top:50%;transform:translate(-50%,-48%);width:min(94vw,470px);' +
      'background:var(--paper,#fff);color:var(--ink,#1E2A32);border-radius:var(--radius,14px);' +
      'box-shadow:var(--shadow-lg,0 24px 60px rgba(16,26,34,.18));padding:26px 26px 22px;opacity:0;' +
      'transition:opacity .18s ease,transform .18s ease;' +
      "font-family:'Inter',system-ui,sans-serif;font-size:15px;line-height:1.6;max-height:88vh;overflow-y:auto;-webkit-overflow-scrolling:touch}" +
    '#crt-cc.on{opacity:1;transform:translate(-50%,-50%)}' +
    "#crt-cc h2{font-family:'Archivo','Inter',sans-serif;font-size:20px;line-height:1.25;margin:0 0 10px;font-weight:800;color:var(--ink,#1E2A32)}" +
    '#crt-cc p{margin:0 0 16px;color:var(--ink-2,#4A575F)}' +
    '#crt-cc a{color:var(--amber-2,#E17E12)}' +
    '#crt-cc .crt-cc-btns{display:flex;gap:10px;margin:0 0 12px}' +
    "#crt-cc .crt-cc-btns button{flex:1 1 0;min-width:0;font-family:'Archivo','Inter',sans-serif;font-weight:700;font-size:15px;" +
      'padding:13px 10px;border-radius:var(--radius-sm,10px);cursor:pointer;transition:background .15s ease,color .15s ease,border-color .15s ease}' +
    '#crt-cc .crt-yes{background:var(--amber,#F5972A);color:var(--graphite,#1E2A32);border:2px solid var(--amber,#F5972A)}' +
    '#crt-cc .crt-yes:hover{background:var(--amber-2,#E17E12);border-color:var(--amber-2,#E17E12)}' +
    '#crt-cc .crt-no{background:transparent;color:var(--ink,#1E2A32);border:2px solid var(--line-2,#D5DBDE)}' +
    '#crt-cc .crt-no:hover{border-color:var(--graphite,#1E2A32);background:var(--mist,#F1F3F4)}' +
    '#crt-cc .crt-cc-foot{display:flex;justify-content:space-between;align-items:center;gap:14px;flex-wrap:wrap;font-size:13.5px}' +
    '#crt-cc .crt-link{background:none;border:0;padding:0;font:inherit;color:var(--muted,#7A868F);text-decoration:underline;cursor:pointer}' +
    '#crt-cc .crt-link:hover{color:var(--amber-2,#E17E12)}' +
    '#crt-cc .crt-cat{display:flex;gap:12px;align-items:flex-start;padding:14px 0;border-top:1px solid var(--line,#E4E8EA)}' +
    "#crt-cc .crt-cat b{display:block;font-family:'Archivo','Inter',sans-serif;font-size:15px;margin:0 0 2px}" +
    '#crt-cc .crt-cat span{display:block;color:var(--muted,#7A868F);font-size:13.5px;line-height:1.5}' +
    '#crt-cc .crt-sw{position:relative;flex:0 0 46px;height:26px;margin-top:2px}' +
    '#crt-cc .crt-sw input{position:absolute;inset:0;width:100%;height:100%;opacity:0;margin:0;cursor:pointer}' +
    '#crt-cc .crt-sw i{position:absolute;inset:0;border-radius:999px;background:var(--line-2,#D5DBDE);transition:background .15s ease;pointer-events:none}' +
    '#crt-cc .crt-sw i:after{content:"";position:absolute;top:3px;left:3px;width:20px;height:20px;border-radius:50%;background:#fff;' +
      'box-shadow:0 1px 3px rgba(16,26,34,.3);transition:transform .15s ease}' +
    '#crt-cc .crt-sw input:checked+i{background:var(--amber,#F5972A)}' +
    '#crt-cc .crt-sw input:checked+i:after{transform:translateX(20px)}' +
    '#crt-cc .crt-sw input:disabled+i{background:var(--graphite,#1E2A32);opacity:.45}' +
    '#crt-cc .crt-sw input:focus-visible+i{outline:2px solid var(--amber,#F5972A);outline-offset:2px}' +
    /* 100vw, not 100%: never inherit a document that is wider than the viewport.
       Sits above the sticky mobile bar (72px), so "Sunați" stays tappable while deciding. */
    '@media (max-width:640px){#crt-cc{top:auto;bottom:72px;left:0;transform:translateY(14px);width:100vw;max-width:100vw;max-height:78vh;' +
      'border-radius:var(--radius,14px) var(--radius,14px) 0 0;padding:22px 20px 18px}' +
      '#crt-cc.on{transform:translateY(0)}' +
      '#crt-cc .crt-cc-btns{flex-direction:column}}';

  var T = {
    title: 'Cookie-uri pe acest site',
    body: 'Folosim cookie-uri ca site-ul să funcționeze corect, ca să înțelegem cum este folosit și ca să măsurăm rezultatele campaniilor noastre. Nu vindem datele dumneavoastră nimănui.',
    yes: 'Acceptă toate',
    no: 'Refuză toate',
    prefs: 'Setări',
    policy: 'Politica de confidențialitate',
    ptitle: 'Preferințe privind cookie-urile',
    pbody: 'Alegeți ce categorii acceptați. Vă puteți răzgândi oricând din subsolul paginii.',
    save: 'Salvează preferințele',
    cats: [
      { k: 'n', n: 'Strict necesare', d: 'Fac site-ul să funcționeze, permit trimiterea formularului și rețin opțiunea dumneavoastră privind cookie-urile. Nu pot fi dezactivate.', locked: true },
      { k: 'a', n: 'Analiză', d: 'Ne arată câți vizitatori avem și ce pagini citesc, ca să îmbunătățim site-ul.' },
      { k: 'm', n: 'Marketing', d: 'Ne permit să măsurăm rezultatele campaniilor Google Ads și să evităm afișarea repetată a acelorași reclame.' },
      { k: 'f', n: 'Funcționalitate', d: 'Rețin preferințele dumneavoastră, ca site-ul să fie afișat la fel la următoarea vizită.' }
    ]
  };

  function el(html) { var t = d.createElement('div'); t.innerHTML = html; return t.firstElementChild; }

  function esc(s) { return String(s).replace(/[&<>"]/g, function (ch) { return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' })[ch]; }); }

  function bannerHTML() {
    return '<h2 id="crt-cc-title">' + esc(T.title) + '</h2>' +
      '<p>' + esc(T.body) + '</p>' +
      '<div class="crt-cc-btns">' +
        '<button type="button" class="crt-yes" data-act="all">' + esc(T.yes) + '</button>' +
        '<button type="button" class="crt-no" data-act="none">' + esc(T.no) + '</button>' +
      '</div>' +
      '<div class="crt-cc-foot">' +
        '<button type="button" class="crt-link" data-act="prefs">' + esc(T.prefs) + '</button>' +
        '<a href="' + POLICY + '">' + esc(T.policy) + '</a>' +
      '</div>';
  }

  function prefsHTML() {
    var c = read() || { a: false, m: false, f: false };
    var rows = T.cats.map(function (cat) {
      var on = cat.locked ? true : !!c[cat.k];
      return '<div class="crt-cat"><label class="crt-sw">' +
        '<input type="checkbox" data-cat="' + cat.k + '"' + (on ? ' checked' : '') + (cat.locked ? ' disabled' : '') +
        ' aria-label="' + esc(cat.n) + '"><i></i></label>' +
        '<div><b>' + esc(cat.n) + '</b><span>' + esc(cat.d) + '</span></div></div>';
    }).join('');
    return '<h2 id="crt-cc-title">' + esc(T.ptitle) + '</h2>' +
      '<p>' + esc(T.pbody) + '</p>' + rows +
      '<div class="crt-cc-btns" style="margin-top:18px">' +
        '<button type="button" class="crt-yes" data-act="save">' + esc(T.save) + '</button>' +
      '</div>' +
      '<div class="crt-cc-btns">' +
        '<button type="button" class="crt-no" data-act="all">' + esc(T.yes) + '</button>' +
        '<button type="button" class="crt-no" data-act="none">' + esc(T.no) + '</button>' +
      '</div>' +
      '<div class="crt-cc-foot"><a href="' + POLICY + '">' + esc(T.policy) + '</a></div>';
  }

  function render() {
    dialog.innerHTML = view === 'prefs' ? prefsHTML() : bannerHTML();
    var focusable = dialog.querySelector('button, input:not(:disabled), a');
    if (focusable) focusable.focus();
  }

  function onClick(e) {
    var b = e.target.closest('[data-act]');
    if (!b) return;
    var act = b.getAttribute('data-act');
    if (act === 'all') return save(true, true, true, 'accept_all');
    if (act === 'none') return save(false, false, false, 'reject_all');
    if (act === 'prefs') { view = 'prefs'; return render(); }
    if (act === 'save') {
      var v = {};
      dialog.querySelectorAll('input[data-cat]').forEach(function (i) { v[i.getAttribute('data-cat')] = i.checked; });
      return save(v.a, v.m, v.f, 'save_prefs');
    }
  }

  /* Keep focus inside the dialog while a decision is pending. */
  function onKey(e) {
    if (e.key !== 'Tab' || !dialog) return;
    var f = dialog.querySelectorAll('button, input:not(:disabled), a[href]');
    if (!f.length) return;
    var first = f[0], last = f[f.length - 1];
    if (e.shiftKey && d.activeElement === first) { e.preventDefault(); last.focus(); }
    else if (!e.shiftKey && d.activeElement === last) { e.preventDefault(); first.focus(); }
  }

  function open(which) {
    if (dialog) { view = which; return render(); }
    view = which;
    lastFocus = d.activeElement;
    var style = d.createElement('style'); style.textContent = CSS; d.head.appendChild(style);
    root = el('<div id="crt-cc-backdrop"></div>');
    dialog = el('<div id="crt-cc" role="dialog" aria-modal="true" aria-labelledby="crt-cc-title"></div>');
    d.body.appendChild(root); d.body.appendChild(dialog);
    render();
    dialog.addEventListener('click', onClick);
    d.addEventListener('keydown', onKey);
    requestAnimationFrame(function () { root.classList.add('on'); dialog.classList.add('on'); });
  }

  function close() {
    if (!dialog) return;
    dialog.classList.remove('on'); root.classList.remove('on');
    d.removeEventListener('keydown', onKey);
    var dg = dialog, bd = root; dialog = null; root = null;
    setTimeout(function () { dg.remove(); bd.remove(); }, 220);
    if (lastFocus && lastFocus.focus) lastFocus.focus();
  }

  /* ---------- boot ---------- */

  window.creatonCookies = {
    show: function () { open('banner'); },
    showPrefs: function () { open('prefs'); },
    state: function () { return read(); }
  };

  function boot() {
    /* Footer "Preferințe cookie-uri" links work on every page. */
    d.querySelectorAll('[data-creaton-cookies]').forEach(function (n) {
      n.addEventListener('click', function (e) { e.preventDefault(); open('prefs'); });
    });
    if (!read()) open('banner');   // no valid choice on file: ask
  }

  if (d.readyState === 'loading') d.addEventListener('DOMContentLoaded', boot); else boot();
})();
