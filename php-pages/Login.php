<?php

require_once '../php-Manager/init_session.php';
require_once "../php-Manager/AccountManager.php";
require_once '../php-Manager/Tool.php';

$messaggio_esito = "";

if (isset($_GET['error']) && $_GET['error'] == 'devi_loggarti') {
    $messaggio_esito = "
    <div class='form-message message-error login-error' role='alert' aria-live='assertive'>
        <strong>Attenzione:</strong> Devi effettuare il login per poter completare l'acquisto.
    </div>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Rimosso htmlspecialchars: la sanificazione avviene in output
    $identificativo = trim($_POST['identificativo']);
    $password = $_POST['password'];

    $utente = AccountManager::verificaLogin($identificativo, $password);

    if ($utente) {
        Tool::avviaSessioneUtente($utente);
        header("Location: ../index.php");
        exit();
    } else {
        $messaggio_esito = "
        <div class='form-message message-error login-error' role='alert' aria-live='assertive'>
            <strong>Accesso negato:</strong> Le credenziali inserite non sono corrette.
        </div>";
    }
}

$pagina_html = file_get_contents('../html/login.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('login'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('login'), $pagina_html);

$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);

echo $pagina_html;