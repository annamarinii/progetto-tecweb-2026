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
    // CASO A: Richiesta AJAX per segnare la notifica come letta
    if (isset($_POST['segna_letta_utente']) && $_POST['segna_letta_utente'] === 'si') {
        $id_domanda = $_POST['id_domanda'];
        $successo = AccountManager::segnaRispostaComeLetta($id_domanda, $id_utente_corrente);

        header('Content-Type: application/json');
        echo json_encode(['status' => $successo ? 'success' : 'error']);
        exit();
    }

    // CASO B: Form standard di aggiornamento profilo
    // LATO INGRESSO: Nessun XSS prematuro, solo trim/strip_tags per salvare puro nel DB
    $nome = trim(strip_tags($_POST['nome'] ?? ''));
    $cognome = trim(strip_tags($_POST['cognome'] ?? ''));
    $email = trim(strip_tags($_POST['email'] ?? ''));
    $username = trim(strip_tags($_POST['username'] ?? ''));

    $successo = AccountManager::updateUtente($id_utente_corrente, $nome, $cognome, $email, $username);

    $status = $successo ? "success" : "error";
    header("Location: AreaUtente.php?status=" . $status);
    exit();
}

// ==========================================
// 3. SEZIONE LETTURA (Preparazione Pagina)
// ==========================================
$dati_utente = AccountManager::getUtenteById($id_utente_corrente);

if (!$dati_utente) {
    header("Location: Login.php?error=devi_loggarti");
    exit();
}

// LATO USCITA: Sanificazione rigorosa contro vulnerabilità XSS prima di iniettare nell'HTML
$nome_pulito = Tool::pulisciInput($dati_utente['nome']);
$cognome_pulito = Tool::pulisciInput($dati_utente['cognome']);
$email_pulita = Tool::pulisciInput($dati_utente['email']);
$username_pulito = Tool::pulisciInput($dati_utente['username']);

// Gestione messaggio di esito salvataggio
$messaggio_esito = "";
if (isset($_GET['status'])) {
    if ($_GET['status'] == 'success') {
        $messaggio_esito = "<div class='success-msg' role='alert' aria-live='assertive'>Profilo aggiornato correttamente!</div>";
    } else {
        $messaggio_esito = "<div class='error-msg' role='alert' aria-live='assertive'>Errore durante l'aggiornamento del profilo.</div>";
    }
}

$biglietti = TicketManager::getBigliettiUtente($id_utente_corrente);
$html_ordini = "";

if (empty($biglietti)) {
    $html_ordini = "<p class='empty-list-msg'>Non hai ancora acquistato nessun biglietto.</p>";
} else {
    // Generazione TABELLA SEMANTICA WCAG AA
    $html_ordini = '
    <table class="orders-table-user">
        <caption>Storico dei biglietti acquistati al Patavium Open</caption>
        <thead>
            <tr>
                <th scope="col">Tipo</th>
                <th scope="col">Evento</th>
                <th scope="col">Luogo</th>
                <th scope="col">Settore</th>
                <th scope="col">Ordine N°</th>
                <th scope="col">Qtà</th>
            </tr>
        </thead>
        <tbody>';
        
    foreach ($biglietti as $b) {
        $tribuna = Tool::pulisciInput($b['tribuna']);
        $numero_ordine = Tool::pulisciInput($b['numero_ordine']);

        if ($b['tipo'] == 'abbonamento') {
            $tipo_label = 'Abbonamento';
            $data_evento = '18/05/2027 - 24/05/2027 (Tutte)';
            $stadio = 'Tutti i campi';
        } else {
            $data_evento = date("d/m/Y", strtotime($b['data'])) . ' - ' . ucfirst($b['sessione']);
            $tipo_label = ($b['tipo'] == 'ground') ? 'Ground Pass' : 'Single Session';
            $stadio = ($b['sessione'] == 'serale') ? 'Patavium Arena' : 'Giotto Court';
        }
        $qta = (isset($b['quantita']) && $b['quantita'] > 1) ? Tool::pulisciInput($b['quantita']) : 1;

        $html_ordini .= '
            <tr>
                <td data-label="Tipo"><strong>' . Tool::pulisciInput($tipo_label) . '</strong></td>
                <td data-label="Evento">' . Tool::pulisciInput($data_evento) . '</td>
                <td data-label="Luogo">' . Tool::pulisciInput($stadio) . '</td>
                <td data-label="Settore">' . $tribuna . '</td>
                <td data-label="Ordine N°">' . $numero_ordine . '</td>
                <td data-label="Qtà">' . $qta . '</td>
            </tr>';
    }
    $html_ordini .= '
        </tbody>
    </table>';
}

// Gestione notifiche
$notifiche = FaqManager::getNotificheUtente($id_utente_corrente);
$html_notifiche = "";

if (empty($notifiche)) {
    $html_notifiche = "<p class='empty-list-msg'>Non ci sono nuove comunicazioni.</p>";
} else {
    // TABELLA SEMANTICA PER LE NOTIFICHE
    $html_notifiche = '
    <table class="orders-table-user notifications-table">
        <caption>Messaggi e risposte del team di supporto</caption>
        <thead>
            <tr>
                <th scope="col">Stato</th>
                <th scope="col">Oggetto</th>
                <th scope="col">Domanda</th>
                <th scope="col">Risposta</th>
                <th scope="col">Data</th>
                <th scope="col">Azione</th>
            </tr>
        </thead>
        <tbody>';

    foreach ($notifiche as $n) {
        $status_icon = $n['lettura_user'] ? 'Gia letta' : 'Nuova!';
        $row_class = $n['lettura_user'] ? 'read-row' : 'unread-row';
        $data_formattata = date("d/m/Y H:i", strtotime($n['data_invio']));
        $data_breve = date("d/m", strtotime($n['data_invio']));
        $domanda = Tool::pulisciInput($n['testo_domanda']);
        $risposta = Tool::pulisciInput($n['testo_risposta']);
        $id_domanda = Tool::pulisciInput($n['idDomanda']);

        $html_notifiche .= '
            <tr class="' . $row_class . '" data-id="' . $id_domanda . '">
                <td data-label="Stato">' . $status_icon . '</td>
                <td data-label="Oggetto">Risposta Patavium (' . $data_breve . ')</td>
                <td data-label="Domanda">"' . $domanda . '"</td>
                <td data-label="Risposta">' . $risposta . '</td>
                <td data-label="Data">' . $data_formattata . '</td>
                <td data-label="Azione"><button type="button" class="btn-auth btn-close-mail">Segna Letta</button></td>
            </tr>';
    }
    $html_notifiche .= '
        </tbody>
    </table>';
}

$pagina_html = file_get_contents('../html/areautente.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('areautente'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('areautente'), $pagina_html);

$pagina_html = str_replace('[messaggio_esito]', $messaggio_esito, $pagina_html);
$pagina_html = str_replace('[nome_utente]', $nome_pulito, $pagina_html);
$pagina_html = str_replace('[cognome_utente]', $cognome_pulito, $pagina_html);
$pagina_html = str_replace('[email_utente]', $email_pulita, $pagina_html);
$pagina_html = str_replace('[username_utente]', $username_pulito, $pagina_html);
$pagina_html = str_replace('[lista_ordini]', $html_ordini, $pagina_html);
$pagina_html = str_replace('[lista_notifiche]', $html_notifiche, $pagina_html);

echo $pagina_html;