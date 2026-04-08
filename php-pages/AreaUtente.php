<?php

require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/AccountManager.php';
/** @var string $destinazione_profilo */


// controllo se è loggato o meno
if (!isset($_SESSION['idUtente'])) {
    header("Location: login.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

// questo nel caso in cui l'utente sia stato cancellato dal DB mentre era loggato
if (!$dati_utente) {
    header("Location: Login.php");
    exit();
}

$nome_pulito = htmlspecialchars($dati_utente['nome']);
$cognome_pulito = htmlspecialchars($dati_utente['cognome']);
$email_pulita = htmlspecialchars($dati_utente['email']);
$username_pulito = htmlspecialchars($dati_utente['username']);

// gestione temporanea per biglietti e notifiche
$html_ordini = "<p>Nessun biglietto acquistato al momento.</p>";
$html_notifiche = "<p>Non ci sono nuove comunicazioni.</p>";

$pagina_html = file_get_contents('../html/areautente.html');

$pagina_html = str_replace('[nome_utente]', $nome_pulito, $pagina_html);
$pagina_html = str_replace('[cognome_utente]', $cognome_pulito, $pagina_html);
$pagina_html = str_replace('[email_utente]', $email_pulita, $pagina_html);
$pagina_html = str_replace('[username_utente]', $username_pulito, $pagina_html);

$pagina_html = str_replace('[lista_ordini]', $html_ordini, $pagina_html);
$pagina_html = str_replace('[lista_notifiche]', $html_notifiche, $pagina_html);


echo $pagina_html;
