<?php

require_once '../php-manager/init_session.php';
require_once '../php-manager/faq_manager.php';
require_once '../php-manager/tool.php';

$messaggio_esito_form = "";

// --- GESTIONE DEL FORM ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if (!Tool::isLoggedIn()) {
        $messaggio_esito_form = Tool::buildMessage('Attenzione:', 'Devi effettuare l\'accesso per inviare una richiesta. <a href=\'login.php\'>Clicca qui per accedere</a>.');
    } else {
        // Dati puliti solo da tag dannosi in ingresso (strip_tags) senza convertire entità HTML
        $testo_messaggio = trim(strip_tags($_POST['messaggio']));
        $id_utente_corrente = $_SESSION['idUtente'];

        if (!empty($testo_messaggio)) {
            $salvataggio_ok = FaqManager::inserisciDomanda($testo_messaggio, $id_utente_corrente);

            if ($salvataggio_ok) {
                $messaggio_esito_form = Tool::buildMessage('Ottimo!', 'La tua richiesta è stata inviata. Riceverai risposta nella tua Area Personale.', 'success');
            } else {
                $messaggio_esito_form = Tool::buildMessage('Errore:', 'Problema tecnico durante l\'invio. Riprova più tardi.');
            }
        }
    }
}

$lista_faq_db = FaqManager::getFaq();
$html_faq_dinamico = "";

$frammento_faq = file_get_contents(__DIR__ . '/../item/faq_item.html');

if (!empty($lista_faq_db)) {
    foreach ($lista_faq_db as $singola_faq) {
        $html_faq_dinamico .= str_replace(
            ['[FaqID]', '[Domanda]', '[Risposta]'],
            [
                (int)$singola_faq['idFaq'],
                htmlspecialchars($singola_faq['testo_domanda'], ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($singola_faq['testo_risposta'], ENT_QUOTES, 'UTF-8')
            ],
            $frammento_faq
        );
    }
} else {
    $html_faq_dinamico = "<p>Al momento non ci sono domande frequenti disponibili.</p>";
}

$pagina_html = file_get_contents(__DIR__ . '/../pages/faq.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('faq'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('faq'), $pagina_html);

$pagina_html = str_replace('[lista_faq]', $html_faq_dinamico, $pagina_html);
$pagina_html = str_replace('[messaggio_esito_form]', $messaggio_esito_form, $pagina_html);

echo $pagina_html;