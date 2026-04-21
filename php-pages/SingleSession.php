<?php
// 1. INIZIALIZZAZIONE
require '../php-Manager/init_session.php';
require_once '../php-Manager/TicketManager.php';
/** @var string $destinazione_profilo */

//si collega al ticket.js
if (isset($_POST['data_scelta']) && isset($_POST['sessione_scelta'])) {

    $data_cercata = $_POST['data_scelta'];
    $sessione_cercata = $_POST['sessione_scelta'];

    $dati_tribune = TicketManager::getInfoSingleSession($data_cercata, $sessione_cercata);

    // funzione per calcolare i singoli prezzi
    function calcolaPrezzo($nome_tribuna, $dati) {
        if (isset($dati[$nome_tribuna]) && $dati[$nome_tribuna]['quantita_disponibile'] > 0) {
            return "€ " . number_format($dati[$nome_tribuna]['prezzo'], 2, ',', '.');
        }
        return "Esaurito";
    }

    $p_premium  = calcolaPrezzo('Courtside Premium', $dati_tribune);
    $p_antenore = calcolaPrezzo('Tribuna Antenore', $dati_tribune);
    $p_fondo    = calcolaPrezzo('Tribuna Fondo Campo', $dati_tribune);
    $p_anello   = calcolaPrezzo('Anello Superiore', $dati_tribune);

    echo $p_premium . "|" . $p_antenore . "|" . $p_fondo . "|" . $p_anello;
    exit();
}

$pagina_html = file_get_contents('../html/single_session.html');

// Iniezione link profilo
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);

$prezzo_iniziale = "";
$pagina_html = str_replace('[prezzo_premium]', $prezzo_iniziale, $pagina_html);
$pagina_html = str_replace('[prezzo_antenore]', $prezzo_iniziale, $pagina_html);
$pagina_html = str_replace('[prezzo_fondo]', $prezzo_iniziale, $pagina_html);
$pagina_html = str_replace('[prezzo_anello]', $prezzo_iniziale, $pagina_html);

echo $pagina_html;