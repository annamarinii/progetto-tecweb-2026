<?php

// la sessione muore quando il browser si chiude
session_set_cookie_params(0);

// avvio sessione solo se non è già stata avviata in precedenza
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// Se $is_root esiste ed è true, significa che siamo in index.php. Altrimenti siamo nelle pagine interne.
$prefisso = (isset($is_root) && $is_root === true) ? "php-pages/" : "../php-pages/";

// destinazione per i non loggati usando il prefisso dinamico
$destinazione_profilo = $prefisso . "login.php";

// se la sessione riconosce un utente, aggiorno la destinazione
if (isset($_SESSION['idUtente'])) {

    //controllo il ruolo
    if (isset($_SESSION['isAdmin']) && $_SESSION['isAdmin'] == 1) {
        // è admin
        $destinazione_profilo = $prefisso . "area_admin.php";
    } else {
        // è user
        $destinazione_profilo = $prefisso . "area_utente.php";
    }
}
?>