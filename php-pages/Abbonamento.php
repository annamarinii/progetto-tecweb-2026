<?php

require '../php-Manager/init_session.php';
require_once '../php-Manager/TicketManager.php';

/** @var string $destinazione_profilo */

// 1. Chiediamo al DB disponibilità e somma dei prezzi
$dati_abbonamenti = TicketManager::getInfoAbbonamenti();

// 2. Funzione intelligente che calcola lo sconto
function formattaPrezzoAbbonamento($tribuna, $dati_abbonamenti)
{
    // Controllo se esiste la tribuna e se ci sono abbonamenti disponibili (> 0)
    if (isset($dati_abbonamenti[$tribuna]) && $dati_abbonamenti[$tribuna]['disponibili'] > 0) {

        // Prendo la somma delle 14 sessioni (es. 1000€)
        $prezzo_pieno = $dati_abbonamenti[$tribuna]['prezzo_base'];

        // APPLICO LO SCONTO DEL 20% (Moltiplico per 0.80)
        $prezzo_scontato = $prezzo_pieno * 0.80;

        return "€ " . number_format($prezzo_scontato, 2, ',', '.');
    }
    return "Esaurito";
}

// 3. Caricamento della pagina e iniezione dei dati dinamici
$pagina_html = file_get_contents('../html/abbonamento.html');
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);

// Inietto i prezzi (già scontati) al posto dei segnaposti
$pagina_html = str_replace('[prezzo_premium]', formattaPrezzoAbbonamento('Courtside Premium', $dati_abbonamenti), $pagina_html);
$pagina_html = str_replace('[prezzo_antenore]', formattaPrezzoAbbonamento('Tribuna Antenore', $dati_abbonamenti), $pagina_html);
$pagina_html = str_replace('[prezzo_fondo]', formattaPrezzoAbbonamento('Tribuna Fondo Campo', $dati_abbonamenti), $pagina_html);
$pagina_html = str_replace('[prezzo_anello]', formattaPrezzoAbbonamento('Anello Superiore', $dati_abbonamenti), $pagina_html);

echo $pagina_html;
