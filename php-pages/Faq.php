<?php

require_once '../php-Manager/init_session.php';
require_once '../php-Manager/FaqManager.php';
/** @var string $destinazione_profilo */


$messaggio_esito_form = "";

// --- GESTIONE DEL FORM ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!isset($_SESSION['idUtente'])) {
        // NON è loggato: Classe 'message-error'
        $messaggio_esito_form = "
        <div class='form-message message-error'>
            <strong>Attenzione:</strong> Devi effettuare l'accesso per inviare una richiesta. 
            <a href='Login.php'>Clicca qui per accedere</a>.
        </div>";
    } else {
        $testo_messaggio = trim($_POST['messaggio']);
        $id_utente_corrente = $_SESSION['idUtente'];

        if (!empty($testo_messaggio)) {
            $salvataggio_ok = FaqManager::inserisciDomanda($testo_messaggio, $id_utente_corrente);

            if ($salvataggio_ok) {
                // Successo: Classe 'message-success'
                $messaggio_esito_form = "
                <div class='form-message message-success'>
                    <strong>Ottimo! </strong> La tua richiesta è stata inviata. Riceverai risposta nella tua Area Personale.
                </div>";
            } else {
                // Errore database: Classe 'message-error'
                $messaggio_esito_form = "
                <div class='form-message message-error'>
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
        // funzione per pulì i dati
        $domanda_sicura = htmlspecialchars($singola_faq['testo_domanda']);
        $risposta_sicura = htmlspecialchars($singola_faq['testo_risposta']);

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
$pagina_html = str_replace('[lista_faq]', $html_faq_dinamico, $pagina_html);
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html); //se è loggato innietto il link alla sua areautente
$pagina_html = str_replace('[messaggio_esito_form]', $messaggio_esito_form, $pagina_html);

echo $pagina_html;