<?php
require '../php-Manager/init_session.php';
require_once '../php-Manager/TicketManager.php';

/** @var string $destinazione_profilo */

// Gestione richiesta dinamica (AJAX)
if (isset($_POST['data_scelta']) && isset($_POST['sessione_scelta'])) {
    $data_cercata = $_POST['data_scelta'];
    $sessione_cercata = $_POST['sessione_scelta'];

    $dati_tribune = TicketManager::getInfoSingleSession($data_cercata, $sessione_cercata);

    // Prepariamo l'array dei dati grezzi: niente simboli euro, niente scritte "Esaurito"
    $response = [
        'premium'  => $dati_tribune['Courtside Premium'] ?? null,
        'antenore' => $dati_tribune['Tribuna Antenore'] ?? null,
        'fondo'    => $dati_tribune['Tribuna Fondo Campo'] ?? null,
        'anello'   => $dati_tribune['Anello Superiore'] ?? null
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}

// Caricamento della pagina statica
$pagina_html = file_get_contents('../html/single_session.html');

// Iniezione link profilo
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);

// Placeholder iniziali: li lasciamo vuoti o con un trattino
$fill = "---";
$tags = ['[prezzo_premium]', '[prezzo_antenore]', '[prezzo_fondo]', '[prezzo_anello]'];
$pagina_html = str_replace($tags, $fill, $pagina_html);

echo $pagina_html;