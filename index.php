<?php

// Inizializzazione della sessione e inclusione dei file manager principali
require_once 'php-Manager/init_session.php';
require_once 'php-Manager/news_manager.php';
require_once 'php-Manager/tool.php';

// 1. Recupero delle ultime 3 news dal database per la sezione della Home
$ultime_news = NewsManager::getUltimeNews(3);

// 2. Lettura del template HTML della Home Page
$pagina_html = file_get_contents(__DIR__ . '/pages/index.html');

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
    $alt_news = !empty($news['alt_immagine']) ? Tool::pulisciInput($news['alt_immagine']) : 'Immagine della news';
    $id_news  = (int) $news['idNews'];

    $data_timestamp = strtotime($news['data_pubblicazione']);
    $data_iso       = date('Y-m-d', $data_timestamp);
    $data_it        = date('d/m/Y', $data_timestamp);

    $news_html_content .= str_replace(
        ['[ImmagineNews]', '[AltNews]', '[TitoloNews]', '[DataIso]', '[DataIt]', '[IdNews]'],
        [$immagine,        $alt_news,   $titolo,        $data_iso,   $data_it,   $id_news],
        $template_card
    );
}

// 6. Iniezione del blocco cards nel segnaposto della pagina
$pagina_html = str_replace('[NewsCards]', $news_html_content, $pagina_html);

// 7. SEO: la home usa i meta tag generici di default (nessun parametro specifico)
$pagina_html = Tool::setupSEO($pagina_html);

// 8. Rendering finale della pagina web
echo $pagina_html;
