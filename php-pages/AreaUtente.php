<?php
require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';

// 1. CONTROLLO ACCESSO
if (!isset($_SESSION['idUtente'])) {
    header("Location: Login.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];

// ==========================================
// 2. SEZIONE SCRITTURA (Gestione POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Recupero i dati dal form
    $nome     = isset($_POST['nome']) ? $_POST['nome'] : '';
    $cognome  = isset($_POST['cognome']) ? $_POST['cognome'] : '';
    $email    = isset($_POST['email']) ? $_POST['email'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';

    // Chiamo il metodo dell'AccountManager per aggiornare il DB
    $successo = AccountManager::updateUtente($id_utente_corrente, $nome, $cognome, $email, $username);

    // Redirect alla pagina stessa con lo status nell'URL
    $status = $successo ? "success" : "error";
    header("Location: areautente.php?status=" . $status);
    exit();
}

// ==========================================
// 3. SEZIONE LETTURA (Preparazione Pagina)
// ==========================================

// Recuperiamo i dati (saranno quelli nuovi se c'è stato un aggiornamento)
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if (!$dati_utente) {
    header("Location: Login.php");
    exit();
}

$nome_pulito = htmlspecialchars($dati_utente['nome']);
$cognome_pulito = htmlspecialchars($dati_utente['cognome']);
$email_pulita = htmlspecialchars($dati_utente['email']);
$username_pulito = htmlspecialchars($dati_utente['username']);

// Gestione messaggio di esito salvataggio
$messaggio_esito = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $messaggio_esito = "<div class='success' style='color:green; margin-bottom:15px;'>Profilo aggiornato correttamente!</div>";
    } else {
        $messaggio_esito = "<div class='error' style='color:red; margin-bottom:15px;'>Errore durante l'aggiornamento del profilo.</div>";
    }
}

// Gestione temporanea per biglietti e notifiche
$html_ordini = "<p>Nessun biglietto acquistato al momento.</p>";
$html_notifiche = "<p>Non ci sono nuove comunicazioni.</p>";

// Caricamento template HTML
$pagina_html = file_get_contents('../html/areautente.html');

// Sostituzioni finali
$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);
$pagina_html = str_replace('[nome_utente]', $nome_pulito, $pagina_html);
$pagina_html = str_replace('[cognome_utente]', $cognome_pulito, $pagina_html);
$pagina_html = str_replace('[email_utente]', $email_pulita, $pagina_html);
$pagina_html = str_replace('[username_utente]', $username_pulito, $pagina_html);
$pagina_html = str_replace('[lista_ordini]', $html_ordini, $pagina_html);
$pagina_html = str_replace('[lista_notifiche]', $html_notifiche, $pagina_html);

echo $pagina_html;
?>