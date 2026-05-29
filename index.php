<?php

// Inizializzazione della sessione e inclusione dei file manager principali
require_once 'php-Manager/init_session.php';
require_once 'php-Manager/NewsManager.php';
require_once 'php-Manager/Tool.php';

// 1. Recupero delle ultime 3 news dal database per la sezione della Home
$ultime_news = NewsManager::getUltimeNews(3);

// 2. Lettura del template HTML della Home Page
$pagina_html = file_get_contents(__DIR__ . '/home.html');

// 3. Sostituzione dei componenti macro strutturali tramite la classe Tool
$pagina_html = str_replace('[Header]', Tool::buildHeader('home'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('home'), $pagina_html);

// 4. Caricamento del template frammento una sola volta prima del ciclo
$template_card = file_get_contents(__DIR__ . '/item/news_home_card.html');

// 5. Generazione delle News Cards tramite foreach
//    Nessun fallback: si assume che il DB contenga sempre news pubblicate.
//    basename() isola il nome file dal percorso eventualmente già presente nel DB
//    (es. "assets/images/sinner.jpg" → "sinner.jpg"), garantendo la sincronizzazione
//    con il placeholder src="assets/images/[ImmagineNews]" nel template.
$news_html_content = "";
foreach ($ultime_news as $news) {
    $titolo   = Tool::pulisciInput($news['titolo']);
    $immagine = basename(Tool::pulisciInput($news['immagine']));
    $id_news  = (int) $news['idNews'];

    $data_timestamp = strtotime($news['data_pubblicazione']);
    $data_iso       = date('Y-m-d', $data_timestamp);
    $data_it        = date('d/m/Y', $data_timestamp);

    $news_html_content .= str_replace(
        ['[ImmagineNews]', '[TitoloNews]', '[DataIso]', '[DataIt]', '[IdNews]'],
        [$immagine,        $titolo,        $data_iso,   $data_it,   $id_news],
        $template_card
    );
}

// 6. Iniezione del blocco cards nel segnaposto della pagina
$pagina_html = str_replace('[NewsCards]', $news_html_content, $pagina_html);

// 7. Rendering finale della pagina web
echo $pagina_html;
