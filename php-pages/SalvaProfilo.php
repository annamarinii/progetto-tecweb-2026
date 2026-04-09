<?php
require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['idUtente'])) {
    $id = $_SESSION['idUtente'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $username = $_POST['username'];

    $successo = AccountManager::updateUtente($id, $nome, $cognome, $email, $username);

    if ($successo) {
        // Torna alla pagina profilo (che ricaricherà i dati aggiornati)
        header("Location: areautente.php?status=success");
    } else {
        header("Location: areautente.php?status=error");
    }
    exit();
}

