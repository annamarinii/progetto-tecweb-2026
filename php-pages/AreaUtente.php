<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/AccountManager.php';
require_once '../php-Manager/FaqManager.php';
require_once '../php-Manager/TicketManager.php';

// 1. CONTROLLO ACCESSO
if (!isset($_SESSION['idUtente'])) {
    header("Location: Login.php");
    exit();
}

$id_utente_corrente = $_SESSION['idUtente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CASO A: Richiesta AJAX per segnare la notifica come letta
    if (isset($_POST['segna_letta_utente']) && $_POST['segna_letta_utente'] === 'si') {
        $id_domanda = $_POST['id_domanda'];

        // Chiamiamo il metodo del manager (dovrai crearlo o adattarlo)
        // Passiamo anche l'id_utente per sicurezza, così un utente non può segnare come lette domande altrui
        $successo = AccountManager::segnaRispostaComeLetta($id_domanda, $id_utente_corrente);

        header('Content-Type: application/json');
        echo json_encode(['status' => $successo ? 'success' : 'error']);
        exit();
    }

    // CASO B: Form standard di aggiornamento profilo (quello che avevi già)
    $nome = isset($_POST['nome']) ? $_POST['nome'] : '';
    $cognome = isset($_POST['cognome']) ? $_POST['cognome'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $username = isset($_POST['username']) ? $_POST['username'] : '';

    $successo = AccountManager::updateUtente($id_utente_corrente, $nome, $cognome, $email, $username);

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

// Gestione temporanea per biglietti
$html_ordini = "<p>Nessun biglietto acquistato al momento.</p>";

$biglietti = TicketManager::getBigliettiUtente($id_utente_corrente);
$html_ordini = "";

if (empty($biglietti)) {
    $html_ordini = "<p style='padding: 20px;'>Non hai ancora acquistato nessun biglietto.</p>";
} else {
    foreach ($biglietti as $b) {
        // Formattazione dati
        $data_evento = date("d/m/Y", strtotime($b['data']));
        $tipo_label = ($b['tipo'] == 'ground') ? 'Ground Pass' : 'Single Session';
        $badge_class = ($b['tipo'] == 'ground') ? 'badge-ground' : 'badge-session';
        $stadio = ($b['sessione'] == 'serale') ? 'Patavium Arena' : 'Giotto Court';

        $html_ordini .= '
    <li class="ticket-card no-qr">
        <div class="ticket-info">
            <span class="ticket-badge ' . $badge_class . '">' . $tipo_label . '</span>
            <h3 class="ticket-title">Patavium Open 2026</h3>
            <div class="ticket-details">
                <div class="t-detail">
                    <span>Data e Sessione</span>
                    <strong>' . $data_evento . ' - ' . ucfirst($b['sessione']) . '</strong>
                </div>
                <div class="t-detail">
                    <span>Luogo / Stadio</span>
                    <strong>' . $stadio . '</strong>
                </div>
                <div class="t-detail">
                    <span>Settore / Tribuna</span>
                    <strong>' . htmlspecialchars($b['tribuna']) . '</strong>
                </div>
                <div class="t-detail">
                    <span>Ordine N°</span>
                    <strong>' . $b['numero_ordine'] . '</strong>
                </div>
            </div>
        </div>
    </li>';
    }
}

// Gestione notifiche (domande risposte dal team)
$notifiche = FaqManager::getNotificheUtente($id_utente_corrente);
$html_notifiche = "";

if (empty($notifiche)) {
    $html_notifiche = "<p style='padding: 20px;'>Non ci sono nuove comunicazioni.</p>";
} else {
    foreach ($notifiche as $n) {
        $status = $n['lettura_user'] ? 'read' : 'unread';
        $data_formattata = date("d/m/Y H:i", strtotime($n['data_invio']));
        $data_breve = date("d/m", strtotime($n['data_invio']));

        $html_notifiche .= '
        <li class="mail-row ' . $status . '" data-id="' . $n['idDomanda'] . '">
            <div class="mail-row-header">
                <div class="read-dot" aria-hidden="true"></div>
                <div class="mail-sender">Supporto Patavium Open</div>
                <div class="mail-subject">Risposta alla tua domanda del ' . $data_breve . '</div>
                <div class="mail-date">' . $data_formattata . '</div>
            </div>
            <div class="mail-content" style="display: none;">
                <div class="mail-body">
                    <div class="qa-container">
                        <div class="user-question-box">
                            <span class="box-label">La tua domanda:</span>
                            <p>' . htmlspecialchars($n['testo_domanda']) . '</p>
                        </div>
                        <div class="admin-answer-box">
                            <span class="box-label">Risposta del Team:</span>
                            <p>' . htmlspecialchars($n['testo_risposta']) . '</p>
                        </div>
                    </div>
                </div>
                <div class="form-actions-row">
                    <button type="button" class="btn-auth btn-close-mail">Chiudi messaggio</button>
                </div>
            </div>
        </li>';
    }
}

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