<?php

// la sessione muore quando il browser si chiude
session_set_cookie_params(0);

// avvio sessione solo se non è già stata avviata in precedenza
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// destinazione per in non loggati
$destinazione_profilo = "../php-pages/login.php";

// se la sessione riconosce un utente, aggiorno la destinazione
if (isset($_SESSION['id_utente'])) {
    //$destinazione_profilo = "../php-pages/areautente.php";
    $destinazione_profilo = "../html/areautente.html";
}

