<?php

require_once '../php-Manager/init_session.php';
require_once '../php-Manager/FaqManager.php';
require_once '../php-Manager/Tool.php';

$messaggio_esito_form = "";

// --- GESTIONE DEL FORM ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!Tool::isLoggedIn()) {
        $messaggio_esito_form = "
        <div class='form-message message-error' role='alert' aria-live='assertive'>
            <strong>Attenzione:</strong> Devi effettuare l'accesso per inviare una richiesta. 
            <a href='Login.php'>Clicca qui per accedere</a>.
        </div>";
    } else {
        // Dati puliti solo da tag dannosi in ingresso (strip_tags) senza convertire entità HTML
        $testo_messaggio = trim(strip_tags($_POST['messaggio']));
        $id_utente_corrente = $_SESSION['idUtente'];

        if (!empty($testo_messaggio)) {
            $salvataggio_ok = FaqManager::inserisciDomanda($testo_messaggio, $id_utente_corrente);

            if ($salvataggio_ok) {
                // Successo: Classe 'message-success'
                $messaggio_esito_form = "
                <div class='form-message message-success' role='alert' aria-live='assertive'>
                    <strong>Ottimo! </strong> La tua richiesta è stata inviata. Riceverai risposta nella tua Area Personale.
                </div>";
            } else {
                // Errore database: Classe 'message-error'
                $messaggio_esito_form = "
                <div class='form-message message-error' role='alert' aria-live='assertive'>
                    <strong>Errore:</strong> Problema tecnico durante l'invio. Riprova più tardi.
                </div>";
            }
        }
    }
}

$lista_faq_db = FaqManager::getFaq();
$html_faq_dinamico = "";

if (count($lista_faq_db) > 0) {
    foreach ($lista_faq_db as $singola_faq) {
        $domanda_sicura = Tool::pulisciInput($singola_faq['testo_domanda']);
        $risposta_sicura = Tool::pulisciInput($singola_faq['testo_risposta']);

        // costruzione blocco da buttare dentro html
        $html_faq_dinamico .= '<details class="faq-item">';
        $html_faq_dinamico .= '    <summary>' . $domanda_sicura . '</summary>';
        $html_faq_dinamico .= '    <div class="faq-content">';
        $html_faq_dinamico .= '        <p>' . $risposta_sicura . '</p>';
        $html_faq_dinamico .= '    </div>';
        $html_faq_dinamico .= '</details>';
    }
} else {
    // mostro se il db è vuoto
    $html_faq_dinamico = "<p>Al momento non ci sono domande frequenti disponibili.</p>";
}

$pagina_html = file_get_contents('../html/faq.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('faq'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('faq'), $pagina_html);

$pagina_html = str_replace('[lista_faq]', $html_faq_dinamico, $pagina_html);
$pagina_html = str_replace('[messaggio_esito_form]', $messaggio_esito_form, $pagina_html);

echo $pagina_html;