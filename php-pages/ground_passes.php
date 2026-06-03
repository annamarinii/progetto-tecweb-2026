<?php

require '../php-manager/init_session.php';
require_once '../php-manager/ticket_manager.php';
require_once '../php-manager/tool.php';


//connessione con ticket.js
if (isset($_POST['data_scelta'])) {
    $info = TicketManager::getInfoGroundPass($_POST['data_scelta']);

    header('Content-Type: application/json');
    if ($info && $info['quantita_disponibile'] > 0) {
        echo json_encode([
            'prezzo' => $info['prezzo'],
            'quantita_disponibile' => $info['quantita_disponibile']
        ]);
    } else {
        echo json_encode([
            'prezzo' => 0,
            'quantita_disponibile' => 0
        ]);
    }
    exit();
}

$prezzo_iniziale = "";

$pagina_html = file_get_contents('../pages/ground_passes.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('ground_passes'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('ground_passes'), $pagina_html);
$pagina_html = str_replace('[prezzo_ground_std]', $prezzo_iniziale, $pagina_html);

echo $pagina_html;
