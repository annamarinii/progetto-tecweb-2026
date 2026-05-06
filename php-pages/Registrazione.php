<?php

require_once "../php-Manager/AccountManager.php";

// Identifichiamo se la richiesta è di tipo AJAX
$isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
$messaggio_esito = "";
$status_successo = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Pulizia dei dati
    $nome     = htmlspecialchars(trim($_POST['nome']));
    $cognome  = htmlspecialchars(trim($_POST['cognome']));
    $username = htmlspecialchars(trim($_POST['username']));
    $email    = htmlspecialchars(trim($_POST['email']));
    $password = $_POST['pass']; 
    $ripeti_password = $_POST['ripeti_pass'];

    // Logica di validazione
    if ($password !== $ripeti_password) {
        $messaggio_esito = "Errore: Le password non coincidono.";
    } elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s']{1,30}$/", $nome) || !preg_match("/^[a-zA-ZÀ-ÿ\s']{1,30}$/", $cognome)) {
        $messaggio_esito = "Errore: Nome o cognome non validi (max 30 caratteri, solo lettere).";
    } elseif (!preg_match("/^[a-zA-Z0-9._]{1,16}$/", $username)) {
        $messaggio_esito = "Errore: Username non valido (max 16 caratteri alfanumerici).";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100) {
        $messaggio_esito = "Errore: Indirizzo email non valido.";
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $messaggio_esito = "Errore: La password deve avere tra 8 e 20 caratteri.";
    } else {
        $utente_esiste = AccountManager::check($username, $email);

        if ($utente_esiste) {
            $messaggio_esito = "Errore: Username o Email già in uso. Scegline altri.";
        } else {
            $salvato = AccountManager::registraUtente($username, $email, $password, $nome, $cognome);

            if ($salvato) {
                $status_successo = true;
                $messaggio_esito = "Registrazione completata! Ora puoi fare il Login.";
            } else {
                $messaggio_esito = "Errore tecnico durante il salvataggio. Riprova.";
            }
        }
    }

    // SE LA RICHIESTA È AJAX: Rispondi solo con i dati, non caricare l'HTML
    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => $status_successo ? 'success' : 'error',
            'message' => $messaggio_esito
        ]);
        exit; // Ferma l'esecuzione qui
    }
}

// SE LA RICHIESTA È NORMALE (o primo caricamento): Carica l'HTML come prima
$html_messaggio = "";
if ($messaggio_esito !== "") {
    $classe = $status_successo ? "message-success" : "message-error";
    $html_messaggio = "<div class='form-message $classe'>$messaggio_esito " . ($status_successo ? "<a href='Login.php' class='btn-link'>Fai il Login</a>" : "") . "</div>";
}

$pagina_html = file_get_contents('../html/registrazione.html');
$pagina_finita = str_replace('[messaggio_esito]', $html_messaggio, $pagina_html);

echo $pagina_finita;
