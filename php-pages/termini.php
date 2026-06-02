<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/tool.php';

$pagina_html = file_get_contents(__DIR__ . '/../pages/termini.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('termini'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('termini'), $pagina_html);

// SEO: descrizione specifica della pagina; le keywords usano il fallback generico.
$pagina_html = Tool::setupSEO(
    $pagina_html,
    "Termini e condizioni di acquisto dei biglietti e abbonamenti per il torneo Patavium Open alla Patavium Arena di Padova."
);

echo $pagina_html;
