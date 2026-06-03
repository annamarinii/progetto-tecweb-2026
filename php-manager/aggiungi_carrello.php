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

    // VALIDAZIONE QUANTITÀ
    if ($nuovo_item['quantita'] < 1 || $nuovo_item['quantita'] > 10) {
        echo json_encode(array('status' => 'error', 'message' => 'Quantità non valida (min 1, max 10).'));
        exit();
    }

    // CONTROLLO DISPONIBILITÀ DB PER SINGLE SESSION
    if ($nuovo_item['tipologia'] === 'Single Session' && $nuovo_item['idProgramma'] && $nuovo_item['titolo']) {
        require_once 'db_connection.php';
        $conn = DBConnection::getConnessione();
        $sql_check = "SELECT COUNT(idBiglietto) AS disponibili FROM BIGLIETTI WHERE tribuna = ? AND idProgramma = ? AND numero_ordine IS NULL AND tipo IS NULL";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("si", $nuovo_item['titolo'], $nuovo_item['idProgramma']);
        $stmt_check->execute();
        $row_check = $stmt_check->get_result()->fetch_assoc();
        $stmt_check->close();
        $disponibili = (int)($row_check['disponibili'] ?? 0);
        if ($nuovo_item['quantita'] > $disponibili) {
            echo json_encode(array('status' => 'error', 'message' => "Sono rimasti solo {$disponibili} biglietti per questo settore."));
            exit();
        }
    }

    // CONTROLLO DISPONIBILITÀ DB PER GROUND PASS
    if ($nuovo_item['tipologia'] === 'Ground Pass' && $nuovo_item['idProgramma']) {
        require_once 'ticket_manager.php';
        $disponibili = TicketManager::getDisponibilitaGroundPass((int)$nuovo_item['idProgramma']);
        if ($nuovo_item['quantita'] > $disponibili) {
            echo json_encode(array('status' => 'error', 'message' => "Sono rimasti solo {$disponibili} biglietti per questa data."));
            exit();
        }
    }

    // CONTROLLO DISPONIBILITÀ DB PER ABBONAMENTO
    if ($nuovo_item['tipologia'] === 'Abbonamento' && $nuovo_item['titolo']) {
        require_once 'ticket_manager.php';
        $disponibili = TicketManager::getDisponibilitaAbbonamento($nuovo_item['titolo']);
        if ($nuovo_item['quantita'] > $disponibili) {
            echo json_encode(array('status' => 'error', 'message' => "Sono rimasti solo {$disponibili} abbonamenti disponibili per questa tribuna."));
            exit();
        }
    }

    // LOGICA DI RAGGRUPPAMENTO E INCREMENTO
    // LOGICA DI RAGGRUPPAMENTO E INCREMENTO
    $trovato = false;
    foreach ($_SESSION['carrello'] as $indice => $item_esistente) {

        // Controllo incrociato: ID, Titolo, Data e Sessione devono coincidere perfettamente
        if ($item_esistente['idProgramma'] == $nuovo_item['idProgramma'] &&
            $item_esistente['titolo'] == $nuovo_item['titolo'] &&
            $item_esistente['data'] == $nuovo_item['data'] &&
            $item_esistente['sessione'] == $nuovo_item['sessione']) {

            // Incremento la quantità
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
