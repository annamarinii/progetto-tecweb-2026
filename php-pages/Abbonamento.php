<?php

require '../php-Manager/init_session.php';
require_once '../php-Manager/TicketManager.php';

/** @var string $destinazione_profilo */

// 1. Chiediamo al DB disponibilità e somma dei prezzi
$dati_abbonamenti = TicketManager::getInfoAbbonamenti();

// 2. Funzione intelligente che calcola lo sconto
function calcolaPrezzoScontato($tribuna, $dati_abbonamenti)
{
    if (isset($dati_abbonamenti[$tribuna]) && $dati_abbonamenti[$tribuna]['disponibili'] > 0) {
        $prezzo_pieno = $dati_abbonamenti[$tribuna]['prezzo_base'];
        return $prezzo_pieno * 0.80;
    }
    return 0;
}

function formattaPrezzoAbbonamento($prezzo_scontato)
{
    if ($prezzo_scontato > 0) {
        return "€ " . number_format($prezzo_scontato, 2, ',', '.');
    }
    return "Esaurito";
}

// 3. Caricamento della pagina e iniezione dei dati dinamici
$pagina_html = file_get_contents('../html/abbonamento.html');
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);

$prezzo_premium = calcolaPrezzoScontato('Courtside Premium', $dati_abbonamenti);
$prezzo_antenore = calcolaPrezzoScontato('Tribuna Antenore', $dati_abbonamenti);
$prezzo_fondo = calcolaPrezzoScontato('Tribuna Fondo Campo', $dati_abbonamenti);
$prezzo_anello = calcolaPrezzoScontato('Anello Superiore', $dati_abbonamenti);

$pagina_html = str_replace('[raw_premium]', $prezzo_premium, $pagina_html);
$pagina_html = str_replace('[raw_antenore]', $prezzo_antenore, $pagina_html);
$pagina_html = str_replace('[raw_fondo]', $prezzo_fondo, $pagina_html);
$pagina_html = str_replace('[raw_anello]', $prezzo_anello, $pagina_html);

// Inietto i prezzi (già scontati) al posto dei segnaposti
$pagina_html = str_replace('[prezzo_premium]', formattaPrezzoAbbonamento($prezzo_premium), $pagina_html);
$pagina_html = str_replace('[prezzo_antenore]', formattaPrezzoAbbonamento($prezzo_antenore), $pagina_html);
$pagina_html = str_replace('[prezzo_fondo]', formattaPrezzoAbbonamento($prezzo_fondo), $pagina_html);
$pagina_html = str_replace('[prezzo_anello]', formattaPrezzoAbbonamento($prezzo_anello), $pagina_html);

echo $pagina_html;
