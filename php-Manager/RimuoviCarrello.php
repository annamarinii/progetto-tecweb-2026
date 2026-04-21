<?php

$is_root = false;
require 'init_session.php';

// Leggo quale indice l'utente vuole eliminare
$dati = json_decode(file_get_contents("php://input"), true);

if (isset($dati['indice'])) {
    $indice = $dati['indice'];

    // Controllo che il biglietto esista davvero in quella posizione
    if (isset($_SESSION['carrello'][$indice])) {

        // 1. Distruggo l'elemento
        unset($_SESSION['carrello'][$indice]);

        // 2. Ricompatto le chiavi dell'array per evitare "buchi" di numerazione
        $_SESSION['carrello'] = array_values($_SESSION['carrello']);

        echo json_encode(array('status' => 'success'));
    } else {
        echo json_encode(array('status' => 'error', 'message' => 'Biglietto non trovato'));
    }
} else {
    echo json_encode(array('status' => 'error', 'message' => 'Nessun indice inviato'));
}
