<?php
require_once '../php-Manager/init_session.php';
require_once '../php-Manager/NewsManager.php';
require_once '../php-Manager/Tool.php';

// RECUPERO NEWS
$lista_news_db = NewsManager::getNews();
$html_news_dinamico = "";

if (count($lista_news_db) > 0) {
    foreach ($lista_news_db as $news) {
        $titolo = Tool::pulisciInput($news['titolo']);
        $testo = Tool::pulisciInput($news['testo']);
        $percorso_img = Tool::pulisciInput($news['immagine']);
        
        $timestamp = strtotime($news['data_pubblicazione']);
        $data_iso = date("Y-m-d", $timestamp);
        $data_leggibile = date("j F Y", $timestamp); 

        $html_news_dinamico .= '
        <article class="news-card"> 
            <div class="card-image">
                <img src="../' . $percorso_img . '" alt="Immagine correlata alla notizia: ' . $titolo . '" width="300" height="200">
            </div>
            <div class="card-content">
                <time datetime="' . $data_iso . '">' . $data_leggibile . '</time>
                <h2>' . $titolo . '</h2>
                <p>' . $testo . '</p>
            </div>
        </article>';
    }
} else {
    $html_news_dinamico = '<p class="no-news" role="status">Al momento non sono presenti notizie ufficiali.</p>';
}

$pagina_html = file_get_contents('../html/news.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('news'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('news'), $pagina_html);
$pagina_html = str_replace('[lista_news]', $html_news_dinamico, $pagina_html);

echo $pagina_html;