<?php

require '../php-dbManager/init_session.php';
require_once '../php-dbManager/TicketManager.php';

/** @var string $destinazione_profilo */

//connessione con ticket.js
if (isset($_POST['data_scelta'])) {
    $info = TicketManager::getInfoGroundPass($_POST['data_scelta']);


    if ($info && $info['quantita_disponibile'] > 0) {
        echo "€ " . number_format($info['prezzo'], 2, ',', '.');
    } else {
        echo "Esaurito";
    }
    exit();
}

$prezzo_iniziale = "";

$pagina_html = file_get_contents('../html/ground_passes.html');
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);
$pagina_html = str_replace('[prezzo_ground_std]', $prezzo_iniziale, $pagina_html);

echo $pagina_html;
