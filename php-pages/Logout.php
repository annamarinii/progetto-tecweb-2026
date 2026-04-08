<?php

require_once '../php-dbManager/init_session.php';


if (isset($_SESSION['idUtente'])) {

    // Svuoto la RAM
    session_unset();

    // distruggo il cookie sul browser dell'utente
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
}

// 3. Risultato garantito: l'utente torna sempre al login in modo pulito
header("Location: ../index.html");
exit();
