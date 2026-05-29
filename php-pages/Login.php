<?php

require_once '../php-Manager/init_session.php';
require_once "../php-Manager/AccountManager.php";
require_once '../php-Manager/Tool.php';

$messaggio_esito = "";

if (isset($_GET['error']) && $_GET['error'] == 'devi_loggarti') {
    $messaggio_esito = Tool::buildMessage('Attenzione:', "Devi effettuare il login per poter completare l'acquisto.", 'error login-error');
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Rimosso htmlspecialchars: la sanificazione avviene in output
    $identificativo = trim($_POST['identificativo']);
    $password = $_POST['password'];

    $utente = AccountManager::verificaLogin($identificativo, $password);

    if ($utente) {
        session_regenerate_id(true);
        Tool::avviaSessioneUtente($utente);
        header("Location: ../index.php");
        exit();
    } else {
        $messaggio_esito = Tool::buildMessage('Accesso negato:', 'Le credenziali inserite non sono corrette.', 'error login-error');
    }
}

$pagina_html = file_get_contents('../html/login.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('login'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('login'), $pagina_html);

$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);

echo $pagina_html;