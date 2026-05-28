<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/NewsManager.php';
require_once '../php-Manager/Tool.php';

// RECUPERO NEWS
$lista_news_db = NewsManager::getNews();
$html_news_dinamico = "";

if (count($lista_news_db) > 0) {
    // Carichiamo il template visivo fuori dal ciclo per efficienza
    $template_news_card = file_get_contents('../item/news_item.html');

    foreach ($lista_news_db as $news) {
        $titolo = Tool::pulisciInput($news['titolo']);
        $testo = Tool::pulisciInput($news['testo']);
        $percorso_img = Tool::pulisciInput($news['immagine']);

        $timestamp = strtotime($news['data_pubblicazione']);
        $data_iso = date("Y-m-d", $timestamp);
        $data_leggibile = date("j F Y", $timestamp);

        // Copiamo il template
        $card_html = $template_news_card;

        // Iniezione sicura dei dati
        $card_html = str_replace('[PercorsoImg]', $percorso_img, $card_html);
        $card_html = str_replace('[Titolo]', $titolo, $card_html);
        $card_html = str_replace('[DataIso]', $data_iso, $card_html);
        $card_html = str_replace('[DataLeggibile]', $data_leggibile, $card_html);
        $card_html = str_replace('[Testo]', $testo, $card_html);

        $html_news_dinamico .= $card_html;
    }
} else {
    $html_news_dinamico = '<p class="no-news" role="status">Al momento non sono presenti notizie ufficiali.</p>';
}

$pagina_html = file_get_contents('../html/news.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('news'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('news'), $pagina_html);
$pagina_html = str_replace('[lista_news]', $html_news_dinamico, $pagina_html);

echo $pagina_html;