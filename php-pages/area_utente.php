<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/account_manager.php';
require_once '../php-manager/faq_manager.php';
require_once '../php-manager/ticket_manager.php';
require_once '../php-manager/tool.php';

// 1. CONTROLLO ACCESSO
if (!Tool::isLoggedIn()) {
    header("Location: login.php?error=devi_loggarti");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Aggiornamento profilo
    $nome     = trim(strip_tags($_POST['nome']     ?? ''));
    $cognome  = trim(strip_tags($_POST['cognome']  ?? ''));
    $email    = trim(strip_tags($_POST['email']    ?? ''));
    $username = trim(strip_tags($_POST['username'] ?? ''));

    // Validazione formale lato controller (coerente con la registrazione).
    // La validazione definitiva è comunque ripetuta dentro updateUtente().
    if (!Tool::validaNomeProprio($nome) || !Tool::validaNomeProprio($cognome)
        || !Tool::validaUsername($username)
        || !Tool::validaEmailCompleta($email)) {
        header("Location: area_utente.php?status=dati_non_validi");
        exit();
    }

    $esito = AccountManager::updateUtente($id_utente_corrente, $nome, $cognome, $email, $username);

    // $esito === true => ok; altrimenti codice di errore (stringa) o false tecnico.
    if ($esito === true) {
        $status = "success";
    } elseif ($esito === 'email_esistente' || $esito === 'username_esistente') {
        $status = $esito;
    } else {
        $status = "error";
    }
    header("Location: area_utente.php?status=" . $status);
    exit();
}

// ==========================================
// SEZIONE LETTURA (Preparazione Pagina)
// ==========================================
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if (!$dati_utente) {
    header("Location: login.php?error=devi_loggarti");
    exit();
}

$nome_pulito     = Tool::pulisciInput($dati_utente['nome']);
$cognome_pulito  = Tool::pulisciInput($dati_utente['cognome']);
$email_pulita    = Tool::pulisciInput($dati_utente['email']);
$username_pulito = Tool::pulisciInput($dati_utente['username']);

$messaggio_esito = "";
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $messaggio_esito = Tool::buildMessage('Ottimo!', 'Profilo aggiornato correttamente.', 'success');
            break;
        case 'dati_non_validi':
            $messaggio_esito = Tool::buildMessage('Errore:', 'Dati non validi. Controlla nome, cognome, username ed email.');
            break;
        case 'email_esistente':
            $messaggio_esito = Tool::buildMessage('Errore:', 'Questa email è già associata a un altro account.');
            break;
        case 'username_esistente':
            $messaggio_esito = Tool::buildMessage('Errore:', 'Questo username è già in uso.');
            break;
        default:
            $messaggio_esito = Tool::buildMessage('Errore:', 'Si è verificato un errore durante l\'aggiornamento del profilo.');
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
        $tribuna       = Tool::pulisciInput($b['tribuna']);
        $numero_ordine = Tool::pulisciInput($b['numero_ordine']);

        if ($b['tipo'] == 'abbonamento') {
            $tipo_label  = 'Abbonamento';
            $data_evento = '18/05/2027 - 24/05/2027 (Tutte)';
            $stadio      = 'Tutti i campi';
        } else {
            $sessione_sicura = Tool::pulisciInput(ucfirst($b['sessione']));
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
        $domanda         = Tool::pulisciInput($n['testo_domanda']);
        $risposta        = Tool::pulisciInput($n['testo_risposta']);

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

$pagina_html = file_get_contents(__DIR__ . '/../pages/area_utente.html');
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
