<?php

require 'init_session.php';
require_once 'carrello_manager.php';

if (!isset($_SESSION['idUtente'])) {
    header("Location: ../php-pages/login.php?error=devi_loggarti");
    exit();
}

$carrello = $_SESSION['carrello'] ?? [];
$totale   = 0;

foreach ($carrello as $item) {
    $totale += (float) $item['prezzo'] * (int) $item['quantita'];
}

// processaAcquisto gestisce internamente connessione, transazione, commit e rollback
try {
    $idOrdine = CarrelloManager::processaAcquisto(
        (int) $_SESSION['idUtente'],
        $totale,
        $carrello
    );

    unset($_SESSION['carrello']);

    header("Location: ../php-pages/carrello.php?success=1&ordine=" . $idOrdine);
    exit();

} catch (Exception $e) {
    header("Location: ../php-pages/carrello.php?error=acquisto_fallito&msg=" . urlencode($e->getMessage()));
    exit();
}
