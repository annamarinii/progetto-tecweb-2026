<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/AccountManager.php';
require_once '../php-Manager/Tool.php';

// CONTROLLO SESSIONE
if (Tool::isLoggedIn()) {
    header("Location: AreaUtente.php");
    exit();
}

$messaggio_esito = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    // Pulizia dei dati (Inversione di rotta: solo trim in ingresso, XSS gestito in uscita)
    $nome = trim($_POST['nome'] ?? '');
    $cognome = trim($_POST['cognome'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['pass'] ?? '';
    $ripeti_password = $_POST['ripeti_pass'] ?? '';

    // Logica di validazione
    $status_successo = false;

    if (empty($nome) || empty($cognome) || empty($username) || empty($email) || empty($password) || empty($ripeti_password)) {
        $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> Tutti i campi sono obbligatori.</div>";
    } elseif ($password !== $ripeti_password) {
        $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> Le password non coincidono.</div>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> Indirizzo email non valido.</div>";
    } elseif (strlen($password) < 8 || strlen($password) > 20) {
        $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> La password deve avere tra 8 e 20 caratteri.</div>";
    } else {
        $risultato = AccountManager::registraUtente($nome, $cognome, $username, $email, $password);

        if ($risultato === true) {
            $status_successo = true;
            $messaggio_esito = "<div class='form-message message-success' role='alert' aria-live='assertive'><strong>Ottimo!</strong> Registrazione completata. Stai per essere reindirizzato al login...</div>";
        } elseif ($risultato === 'email_esistente') {
            $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> Esiste già un account con questa e-mail. <a href='Login.php'>Accedi qui</a></div>";
        } elseif ($risultato === 'username_esistente') {
            $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> Questo username è già in uso.</div>";
        } else {
            $messaggio_esito = "<div class='form-message message-error' role='alert' aria-live='assertive'><strong>Errore:</strong> Si è verificato un errore tecnico. Riprova più tardi.</div>";
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => $status_successo ? 'success' : 'error',
            'message' => $messaggio_esito
        ]);
        exit;
    }
}

// 4. PREPARAZIONE PAGINA
$pagina_html = file_get_contents('../html/registrazione.html');

$pagina_html = str_replace('[Header]', Tool::buildHeader('registrazione'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('registrazione'), $pagina_html);
$pagina_html = str_replace('[MessaggioEsito]', $messaggio_esito, $pagina_html);

echo $pagina_html;
