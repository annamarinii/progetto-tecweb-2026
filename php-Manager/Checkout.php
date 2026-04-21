<?php

require 'init_session.php';
require_once 'CarrelloManager.php';

// Sicurezza: solo gli utenti loggati possono acquistare
if (!isset($_SESSION['idUtente'])) {
    header("Location: Login.php?error=devi_loggarti");
    exit();
}

$carrello = $_SESSION['carrello'];
$totale = 0;

// 1. Calcoliamo il totale
foreach($carrello as $item) {
    $totale += $item['prezzo'] * $item['quantita'];
}

// 2. Creiamo l'ordine
$idOrdine = CarrelloManager::creaOrdine($_SESSION['idUtente'], $totale);

// 3. Associamo i biglietti (SENZA IL CICLO FOR!)
foreach($carrello as $item) {
    // Ora basta chiamarlo una volta sola. Il manager leggerà $item['quantita']
    // e aggiornerà il numero esatto di righe nel database in un solo respiro.
    CarrelloManager::associaBiglietto($idOrdine, $item);
}

// 4. Svuotiamo il carrello e diamo il successo
unset($_SESSION['carrello']);

header("Location: ../php-pages/Carrello.php?success=1&ordine=" . $idOrdine);
exit();
