<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['idUtente'])) {
    $id = $_SESSION['idUtente'];
    $nome = $_POST['nome'];
    $cognome = $_POST['cognome'];
    $email = $_POST['email'];
    $username = $_POST['username'];

    // Qui dovresti avere un metodo nella tua classe AccountManager
    $successo = AccountManager::updateUtente($id, $nome, $cognome, $email, $username);

    if ($successo) {
        // Torna alla pagina profilo (che ricaricherà i dati aggiornati)
        header("Location: areautente.php?status=success");
    } else {
        header("Location: areautente.php?status=error");
    }
    exit();
}