<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/account_manager.php';
require_once '../php-manager/tool.php';

// CONTROLLO SESSIONE
if (Tool::isLoggedIn()) {
    header("Location: area_utente.php");
    exit();
}

$messaggio_esito = "";
$nome     = '';
$cognome  = '';
$username = '';
$email    = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

    // Pulizia dei dati (Inversione di rotta: solo trim in ingresso, XSS gestito in uscita)
    $nome     = trim($_POST['nome']     ?? '');
    $cognome  = trim($_POST['cognome']  ?? '');
    $username = trim($_POST['username'] ?? '');
    $email    = trim($_POST['email']    ?? '');
    $password = $_POST['pass'] ?? '';
    $ripeti_password = $_POST['ripeti_pass'] ?? '';

    // Logica di validazione
    $status_successo = false;

    // Validazione lato controller tramite i validatori centralizzati di Tool.
    // (registraUtente() ri-valida comunque tutto in modo difensivo lato Manager.)
    if (empty($nome) || empty($cognome) || empty($username) || empty($email) || empty($password) || empty($ripeti_password)) {
        $messaggio_esito = Tool::buildMessage('Errore:', 'Tutti i campi sono obbligatori.');
    } elseif (!Tool::validaNomeProprio($nome) || !Tool::validaNomeProprio($cognome)) {
        $messaggio_esito = Tool::buildMessage('Errore:', 'Nome e Cognome devono contenere solo lettere (max 30 caratteri).');
    } elseif (!Tool::validaUsername($username)) {
        $messaggio_esito = Tool::buildMessage('Errore:', 'Username non valido (max 16 caratteri, solo lettere, numeri, punti e underscore).');
    } elseif ($password !== $ripeti_password) {
        $messaggio_esito = Tool::buildMessage('Errore:', 'Le password non coincidono.');
    } elseif (!Tool::validaEmailCompleta($email)) {
        $messaggio_esito = Tool::buildMessage('Errore:', 'Indirizzo email non valido o troppo lungo (max 30 caratteri).');
    } elseif (!AccountManager::validaPassword($password)) {
        $messaggio_esito = Tool::buildMessage('Errore:', 'La password deve avere almeno 8 caratteri, una lettera maiuscola, una minuscola, un numero e un carattere speciale.');
    } else {
        $risultato = AccountManager::registraUtente($nome, $cognome, $username, $email, $password);

        if ($risultato === true) {
            $status_successo = true;
            $messaggio_esito = Tool::buildMessage('Ottimo!', 'Registrazione completata. <a href=\'login.php\'>Vai al login</a>', 'success');
        } elseif ($risultato === 'email_esistente') {
            $messaggio_esito = Tool::buildMessage('Errore:', 'Esiste già un account con questa e-mail. <a href=\'login.php\'>Accedi qui</a>');
        } elseif ($risultato === 'username_esistente') {
            $messaggio_esito = Tool::buildMessage('Errore:', 'Questo username è già in uso.');
        } elseif ($risultato === 'dati_non_validi') {
            $messaggio_esito = Tool::buildMessage('Errore:', 'I dati inseriti non sono validi. Controlla i campi e riprova.');
        } else {
            $messaggio_esito = Tool::buildMessage('Errore:', 'Si è verificato un errore tecnico. Riprova più tardi.');
        }
    }

    if ($isAjax) {
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => $status_successo ? 'success' : 'error',
            'message' => strip_tags($messaggio_esito)
        ]);
        exit;
    }
}

// 4. PREPARAZIONE PAGINA
$pagina_html = file_get_contents('../pages/registrazione.html');

$pagina_html = str_replace('[Header]',        Tool::buildHeader('registrazione'), $pagina_html);
$pagina_html = str_replace('[Footer]',        Tool::buildFooter('registrazione'), $pagina_html);
$pagina_html = str_replace('[MessaggioEsito]', $messaggio_esito,                  $pagina_html);
$pagina_html = str_replace('[ValoreNome]',     htmlspecialchars($nome,     ENT_QUOTES, 'UTF-8'), $pagina_html);
$pagina_html = str_replace('[ValoreCognome]',  htmlspecialchars($cognome,  ENT_QUOTES, 'UTF-8'), $pagina_html);
$pagina_html = str_replace('[ValoreUsername]', htmlspecialchars($username, ENT_QUOTES, 'UTF-8'), $pagina_html);
$pagina_html = str_replace('[ValoreEmail]',    htmlspecialchars($email,    ENT_QUOTES, 'UTF-8'), $pagina_html);

// SEO: descrizione specifica della pagina; le keywords usano il fallback generico.
$pagina_html = Tool::setupSEO(
    $pagina_html,
    "Crea il tuo account Patavium Open per acquistare biglietti e accedere al tuo profilo."
);

echo $pagina_html;
