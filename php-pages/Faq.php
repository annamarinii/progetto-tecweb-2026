<?php

require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/FaqManager.php';
/** @var string $destinazione_profilo */


$messaggio_esito_form = "";

// 2. GESTIONE DEL FORM (Se l'utente ha cliccato "Invia richiesta")
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // CONTROLLO SICUREZZA: È loggato?
    if (!isset($_SESSION['idUtente'])) {
        // NON è loggato: Prepara il messaggio di errore (stile rosso)
        $messaggio_esito_form = "<div class='error' style='color: #d9534f; background: #f2dede; padding: 15px; border-radius: 5px; margin-bottom: 20px;'><strong>Attenzione:</strong> Devi effettuare l'accesso per poter inviare una richiesta all'assistenza. <a href='Login.php' style='color: #a94442; text-decoration: underline;'>Clicca qui per accedere</a>.</div>";
    } else {
        // È loggato: Procedo con il salvataggio
        $testo_messaggio = trim($_POST['messaggio']);
        $id_utente_corrente = $_SESSION['idUtente'];

        if (!empty($testo_messaggio)) {
            // Chiamo il model
            $salvataggio_ok = FaqManager::inserisciDomanda($testo_messaggio, $id_utente_corrente);

            if ($salvataggio_ok) {
                // Successo (stile verde)
                $messaggio_esito_form = "<div class='success' style='color: #3c763d; background: #dff0d8; padding: 15px; border-radius: 5px; margin-bottom: 20px;'><strong>Ottimo!</strong> La tua richiesta è stata inviata con successo. Riceverai presto una risposta dal nostro team nella tua Area Personale.</div>";
            } else {
                // Errore database
                $messaggio_esito_form = "<div class='error' style='color: #d9534f; background: #f2dede; padding: 15px; border-radius: 5px; margin-bottom: 20px;'><strong>Errore:</strong> Problema tecnico durante l'invio. Riprova più tardi.</div>";
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