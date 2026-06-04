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
    // Raggruppa le FAQ per categoria mantenendo l'ordine di arrivo dal DB
    $faq_per_categoria = [];
    foreach ($lista_faq_db as $singola_faq) {
        $faq_per_categoria[$singola_faq['categoria']][] = $singola_faq;
    }

    // Ordine di visualizzazione preferito delle categorie
    $ordine_categorie = ['Info Pratiche e Accessi', 'Biglietteria', 'Regolamento'];
    $categorie_ordinate = array_keys($faq_per_categoria);
    usort($categorie_ordinate, function ($a, $b) use ($ordine_categorie) {
        $posA = array_search($a, $ordine_categorie);
        $posB = array_search($b, $ordine_categorie);
        if ($posA === false) $posA = PHP_INT_MAX;
        if ($posB === false) $posB = PHP_INT_MAX;
        return $posA <=> $posB;
    });

    foreach ($categorie_ordinate as $categoria) {
        $faq_categoria = $faq_per_categoria[$categoria];
        if (empty($faq_categoria)) {
            continue; // salta le categorie senza FAQ
        }

        $categoria_sicura = htmlspecialchars($categoria, ENT_QUOTES, 'UTF-8');
        // Id univoco per l'h2 a partire dal nome categoria (es. cat-info-pratiche-e-accessi)
        $id_categoria = 'cat-' . preg_replace('/[^a-z0-9]+/', '-', strtolower($categoria));
        $id_categoria = trim($id_categoria, '-');

        $html_items = "";
        foreach ($faq_categoria as $singola_faq) {
            $html_items .= str_replace(
                ['[FaqID]', '[Domanda]', '[Risposta]'],
                [
                    (int)$singola_faq['idFaq'],
                    htmlspecialchars($singola_faq['testo_domanda'], ENT_QUOTES, 'UTF-8'),
                    htmlspecialchars($singola_faq['testo_risposta'], ENT_QUOTES, 'UTF-8')
                ],
                $frammento_faq
            );
        }

        $html_faq_dinamico .= '<section class="faq-category" aria-labelledby="' . $id_categoria . '">'
            . '<h2 id="' . $id_categoria . '" class="faq-category-title">' . $categoria_sicura . '</h2>'
            . $html_items
            . '</section>';
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