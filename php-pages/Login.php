<?php

session_start(); //avvio la sessione
require_once "../php-dbManager/AccountManager.php";

// se è già loggato
if (isset($_SESSION['idUtente'])) {
    header("Location: ../html/areautente.html");
    exit();
}

$messaggio_esito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // prendo il campo "identificativo" può essere email o username
    $identificativo = htmlspecialchars(trim($_POST['identificativo']));
    $password = $_POST['password'];

    $utente = AccountManager::verificaLogin($identificativo, $password);

    if ($utente) {
        $_SESSION['idUtente'] = $utente['idUtente'];
        $_SESSION['username'] = $utente['username'];
        $_SESSION['nome'] = $utente['nome'];
        $_SESSION['isAdmin'] = $utente['isAdmin'];

        //dove lo porto se fa accesso?? modificare
        header("Location: ../index.html");
        exit();
    } else {
        // errore se le credenziali non sono valide
        $messaggio_esito = "<div class='error' style='color: #d9534f; background: #f2dede; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;'><strong>Errore:</strong> Credenziali non valide. Controlla e riprova.</div>";
    }
}

$pagina_html = file_get_contents('../html/login.html');
$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);

echo $pagina_html;
