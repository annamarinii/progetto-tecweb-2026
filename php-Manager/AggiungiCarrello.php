<?php

$is_root = false;
require_once 'init_session.php';

// Leggo i dati inviati dal JavaScript
$dati = json_decode(file_get_contents("php://input"), true);

if ($dati) {
    if (!isset($_SESSION['carrello'])) {
        $_SESSION['carrello'] = array();
    }

    // Creo l'oggetto usando l'operatore ternario classico (un solo '?')
    $nuovo_item = array(
        'tipologia'   => isset($dati['tipologia']) ? $dati['tipologia'] : 'Biglietto',
        'titolo'      => isset($dati['titolo']) ? $dati['titolo'] : '',
        'data'        => isset($dati['data']) ? $dati['data'] : '',
        'sessione'    => isset($dati['sessione']) ? $dati['sessione'] : '',
        'prezzo'      => isset($dati['prezzo']) ? $dati['prezzo'] : 0,
        'quantita'    => isset($dati['quantita']) ? (int)$dati['quantita'] : 1,
        'idProgramma' => isset($dati['idProgramma']) ? $dati['idProgramma'] : null
    );

    // LOGICA DI RAGGRUPPAMENTO E INCREMENTO
    $trovato = false;
    foreach ($_SESSION['carrello'] as $indice => $item_esistente) {
        // Se il biglietto (stesso programma e stessa tribuna) esiste già...
        if ($item_esistente['idProgramma'] == $nuovo_item['idProgramma'] &&
            $item_esistente['titolo'] == $nuovo_item['titolo']) {

            // ...incremento la quantità esistente con quella nuova
            $_SESSION['carrello'][$indice]['quantita'] += $nuovo_item['quantita'];
            $trovato = true;
            break;
        }
    }

    // Se è un biglietto nuovo, lo aggiungo normalmente all'array
    if (!$trovato) {
        $_SESSION['carrello'][] = $nuovo_item;
    }

    echo json_encode(array('status' => 'success'));
} else {
    echo json_encode(array('status' => 'error'));
}
