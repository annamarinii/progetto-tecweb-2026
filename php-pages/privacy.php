<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/tool.php';

$pagina_html = file_get_contents(__DIR__ . '/../pages/privacy.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('privacy'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('privacy'), $pagina_html);

// SEO: descrizione specifica della pagina; le keywords usano il fallback generico.
$pagina_html = Tool::setupSEO(
    $pagina_html,
    "Informativa sul trattamento dei dati personali per l'acquisto di biglietti e abbonamenti al torneo Patavium Open."
);

echo $pagina_html;
