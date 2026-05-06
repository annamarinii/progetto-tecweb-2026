<?php

require_once '../php-Manager/init_session.php';
require_once "../php-Manager/AccountManager.php";

$messaggio_esito = "";

if (isset($_GET['error']) && $_GET['error'] == 'devi_loggarti') {
    $messaggio_esito = "
    <div class='form-message message-error login-error'>
        <strong>Attenzione:</strong> Devi effettuare il login per poter completare l'acquisto.
    </div>";
}

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
        $_SESSION['cognome'] = $utente['cognome']; 
        $_SESSION['email'] = $utente['email'];
        header("Location: ../index.php");
        exit();
    } else {
        $messaggio_esito = "
        <div class='form-message message-error login-error'>
            <strong>Accesso negato:</strong> Le credenziali inserite non sono corrette.
        </div>";
    }
}

$pagina_html = file_get_contents('../html/login.html');
$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);


echo $pagina_html;
?>