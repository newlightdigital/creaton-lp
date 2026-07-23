<?php
// Google Consent Mode v2 defaults + the self-hosted CMP loader. Same setup as
// acoperix.ro. MUST be required as early as possible in <head>, BEFORE any GTM
// loader, so no tag can fire before a consent state exists. Everything is denied
// by default; consent.js flips it once the visitor chooses (and re-applies the
// stored choice inline below on every later pageview, before GTM boots).
?>
<!-- Consent Mode v2 defaults: must run before GTM so no tag can fire ungated -->
<script>
window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments)}
(function(){var D='denied';
gtag('consent','default',{ad_storage:D,ad_user_data:D,ad_personalization:D,analytics_storage:D,functionality_storage:D,personalization_storage:D,security_storage:'granted',wait_for_update:500});
gtag('set','url_passthrough',true);
try{var m=document.cookie.match(/(?:^|;\s*)creaton_consent=([^;]*)/);if(m){var c=JSON.parse(decodeURIComponent(m[1]));
if(c&&c.v===1){var A=c.m?'granted':D;gtag('consent','update',{ad_storage:A,ad_user_data:A,ad_personalization:A,analytics_storage:c.a?'granted':D,functionality_storage:c.f?'granted':D,personalization_storage:c.f?'granted':D,security_storage:'granted'});window.creatonConsent=c}}}catch(e){}
})();
</script>
<script src="/assets/js/consent.js?v=<?= creaton_asset_ver('assets/js/consent.js') ?>" defer></script>
