<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/AccountManager.php';
require_once '../php-Manager/FaqManager.php';
require_once '../php-Manager/TicketManager.php';
require_once '../php-Manager/Tool.php';

// 1. CONTROLLO ACCESSO
if (!Tool::isLoggedIn()) {
    header("Location: Login.php?error=devi_loggarti");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aggiornamento profilo
    $nome     = trim(strip_tags($_POST['nome']     ?? ''));
    $cognome  = trim(strip_tags($_POST['cognome']  ?? ''));
    $email    = trim(strip_tags($_POST['email']    ?? ''));
    $username = trim(strip_tags($_POST['username'] ?? ''));

    if ($nome === '' || $cognome === '' || $username === ''
        || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: AreaUtente.php?status=error");
        exit();
    }

    $successo = AccountManager::updateUtente($id_utente_corrente, $nome, $cognome, $email, $username);

    $status = $successo ? "success" : "error";
    header("Location: AreaUtente.php?status=" . $status);
    exit();
}

// ==========================================
// SEZIONE LETTURA (Preparazione Pagina)
// ==========================================
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if (!$dati_utente) {
    header("Location: Login.php?error=devi_loggarti");
    exit();
}

$nome_pulito     = Tool::pulisciInput($dati_utente['nome']);
$cognome_pulito  = Tool::pulisciInput($dati_utente['cognome']);
$email_pulita    = Tool::pulisciInput($dati_utente['email']);
$username_pulito = Tool::pulisciInput($dati_utente['username']);

$messaggio_esito = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $messaggio_esito = "<div class='success-msg' role='alert' aria-live='assertive'>Profilo aggiornato correttamente!</div>";
    } else {
        $messaggio_esito = "<div class='error-msg' role='alert' aria-live='assertive'>Errore durante l'aggiornamento del profilo.</div>";
    }
}

$frammento_biglietto = file_get_contents(__DIR__ . '/../item/utente_biglietto_card.html');
$frammento_notifica  = file_get_contents(__DIR__ . '/../item/utente_notifica_row.html');

// --- BIGLIETTI ---
$biglietti   = TicketManager::getBigliettiUtente($id_utente_corrente);
$html_ordini = "";

if (empty($biglietti)) {
    $html_ordini = "<p class='empty-list-msg'>Non hai ancora acquistato nessun biglietto.</p>";
} else {
    $corpi_cards = '';
    foreach ($biglietti as $b) {
        $tribuna       = htmlspecialchars($b['tribuna'],       ENT_QUOTES, 'UTF-8');
        $numero_ordine = htmlspecialchars($b['numero_ordine'], ENT_QUOTES, 'UTF-8');

        if ($b['tipo'] == 'abbonamento') {
            $tipo_label  = 'Abbonamento';
            $data_evento = '18/05/2027 - 24/05/2027 (Tutte)';
            $stadio      = 'Tutti i campi';
        } else {
            $sessione_sicura = htmlspecialchars(ucfirst($b['sessione']), ENT_QUOTES, 'UTF-8');
            $data_evento     = date("d/m/Y", strtotime($b['data'])) . ' - ' . $sessione_sicura;
            $tipo_label      = ($b['tipo'] == 'ground') ? 'Ground Pass' : 'Single Session';
            $stadio          = ($b['sessione'] == 'serale') ? 'Patavium Arena' : 'Giotto Court';
        }
        $qta = (isset($b['quantita']) && $b['quantita'] > 1) ? (int)$b['quantita'] : 1;

        $corpi_cards .= str_replace(
            ['[TipoLabel]', '[DataEvento]', '[Stadio]', '[Tribuna]', '[NumeroOrdine]', '[Quantita]'],
            [$tipo_label, $data_evento, $stadio, $tribuna, $numero_ordine, $qta],
            $frammento_biglietto
        );
    }
    $html_ordini = '<ul class="biglietti-list" aria-label="Elenco dei tuoi biglietti acquistati">'
        . $corpi_cards
        . '</ul>';
}

// --- NOTIFICHE ---
$notifiche      = FaqManager::getNotificheUtente($id_utente_corrente);
$html_notifiche = "";

if (empty($notifiche)) {
    $html_notifiche = "<p class='empty-list-msg'>Non ci sono nuove comunicazioni.</p>";
} else {
    $corpi_notifiche = '';
    foreach ($notifiche as $n) {
        $data_formattata = date("d/m/Y H:i", strtotime($n['data_invio']));
        $data_breve      = date("d/m", strtotime($n['data_invio']));
        $domanda         = htmlspecialchars($n['testo_domanda'],  ENT_QUOTES, 'UTF-8');
        $risposta        = htmlspecialchars($n['testo_risposta'], ENT_QUOTES, 'UTF-8');

        $corpi_notifiche .= str_replace(
            ['[DataBreve]', '[Domanda]', '[Risposta]', '[DataFormattata]'],
            [$data_breve, $domanda, $risposta, $data_formattata],
            $frammento_notifica
        );
    }
    $html_notifiche = '
    <table class="orders-table-user notifications-table">
        <caption>Messaggi e risposte del team di supporto</caption>
        <thead>
            <tr>
                <th scope="col">Oggetto</th>
                <th scope="col">Domanda</th>
                <th scope="col">Risposta</th>
                <th scope="col">Data</th>
            </tr>
        </thead>
        <tbody>' . $corpi_notifiche . '
        </tbody>
    </table>';
}

$pagina_html = file_get_contents(__DIR__ . '/../html/areautente.html');
$pagina_html = str_replace('[Header]',           Tool::buildHeader('areautente'), $pagina_html);
$pagina_html = str_replace('[Footer]',           Tool::buildFooter('areautente'), $pagina_html);
$pagina_html = str_replace('[messaggio_esito]',  $messaggio_esito,  $pagina_html);
$pagina_html = str_replace('[nome_utente]',      $nome_pulito,      $pagina_html);
$pagina_html = str_replace('[cognome_utente]',   $cognome_pulito,   $pagina_html);
$pagina_html = str_replace('[email_utente]',     $email_pulita,     $pagina_html);
$pagina_html = str_replace('[username_utente]',  $username_pulito,  $pagina_html);
$pagina_html = str_replace('[lista_ordini]',     $html_ordini,      $pagina_html);
$pagina_html = str_replace('[lista_notifiche]',  $html_notifiche,   $pagina_html);

echo $pagina_html;
