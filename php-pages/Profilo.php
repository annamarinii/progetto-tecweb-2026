<?php
require_once '../php-dbManager/init_session.php';

// Se l'utente NON è loggato, lo sbattiamo fuori al login
if (!isset($_SESSION['idUtente'])) {
    header("Location: login.php");
    exit();
}

// Se arriviamo qui, l'utente è loggato. 
// Carichiamo l'HTML del profilo (crea un file profilo.html)
$pagina_html = file_get_contents('../html/profilo.html');

// Sostituiamo i placeholder con i dati reali della sessione
$pagina_html = str_replace('[username]', $_SESSION['username'], $pagina_html);
$pagina_html = str_replace('[nome]', $_SESSION['nome'], $pagina_html);

echo $pagina_html;