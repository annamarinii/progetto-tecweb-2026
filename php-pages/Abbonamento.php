<?php

require '../php-Manager/init_session.php';
require_once '../php-Manager/TicketManager.php';
require_once '../php-Manager/Tool.php';

// 1. Chiediamo al DB disponibilità e somma dei prezzi
$dati_abbonamenti = TicketManager::getInfoAbbonamenti();

// 2. Caricamento della pagina e iniezione dei dati dinamici
$pagina_html = file_get_contents('../html/abbonamento.html');

$pagina_html = str_replace('[Header]', Tool::buildHeader('abbonamento'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('abbonamento'), $pagina_html);

$prezzo_premium = Tool::calcolaPrezzoScontato('Courtside Premium', $dati_abbonamenti);
$prezzo_antenore = Tool::calcolaPrezzoScontato('Tribuna Antenore', $dati_abbonamenti);
$prezzo_fondo = Tool::calcolaPrezzoScontato('Tribuna Fondo Campo', $dati_abbonamenti);
$prezzo_anello = Tool::calcolaPrezzoScontato('Anello Superiore', $dati_abbonamenti);

$pagina_html = str_replace('[raw_premium]', $prezzo_premium, $pagina_html);
$pagina_html = str_replace('[raw_antenore]', $prezzo_antenore, $pagina_html);
$pagina_html = str_replace('[raw_fondo]', $prezzo_fondo, $pagina_html);
$pagina_html = str_replace('[raw_anello]', $prezzo_anello, $pagina_html);

// Inietto i prezzi (già scontati) al posto dei segnaposti
$pagina_html = str_replace('[prezzo_premium]', Tool::formattaPrezzoAbbonamento($prezzo_premium), $pagina_html);
$pagina_html = str_replace('[prezzo_antenore]', Tool::formattaPrezzoAbbonamento($prezzo_antenore), $pagina_html);
$pagina_html = str_replace('[prezzo_fondo]', Tool::formattaPrezzoAbbonamento($prezzo_fondo), $pagina_html);
$pagina_html = str_replace('[prezzo_anello]', Tool::formattaPrezzoAbbonamento($prezzo_anello), $pagina_html);

echo $pagina_html;
