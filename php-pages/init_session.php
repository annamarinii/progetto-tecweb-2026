<?php

// la sessione muore quando il browser si chiude
session_set_cookie_params(0);

// avvio sessione solo se non è già stata avviata in precedenza
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

