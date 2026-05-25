<?php

require 'init_session.php';
require_once 'CarrelloManager.php';
require_once 'DBConnection.php';

if (!isset($_SESSION['idUtente'])) {
    header("Location: ../php-pages/Login.php?error=devi_loggarti");
    exit();
}

$carrello = $_SESSION['carrello'];
$totale = 0;

foreach($carrello as $item) {
    $totale += $item['prezzo'] * $item['quantita'];
}

// creiamo la connessione una sola volta
$conn = DBConnection::getConnessione();

// 3. Avvolgiamo le operazioni in un blocco transazionale try-catch
mysqli_begin_transaction($conn);

try {
    // Creiamo l'ordine passando la connessione attiva
    $idOrdine = CarrelloManager::creaOrdine($conn, $_SESSION['idUtente'], $totale);

    // Associamo i biglietti passando la connessione attiva
    foreach($carrello as $item) {
        CarrelloManager::associaBiglietto($conn, $idOrdine, $item);
    }

    // Se non ci sono state eccezioni, confermiamo la transazione sul DB
    mysqli_commit($conn);

    // 4. Svuotiamo il carrello e diamo il successo
    unset($_SESSION['carrello']);
    $conn->close();

    header("Location: ../php-pages/Carrello.php?success=1&ordine=" . $idOrdine);
    exit();

} catch (Exception $e) {
    // In caso di errore annulliamo qualsiasi modifica parziale (Rollback)
    mysqli_rollback($conn);
    $conn->close();

    // Reindirizzamento alla pagina del carrello esponendo il problema in totale sicurezza
    header("Location: ../php-pages/Carrello.php?error=acquisto_fallito&msg=" . urlencode($e->getMessage()));
    exit();
}