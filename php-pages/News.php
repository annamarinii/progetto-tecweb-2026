<?php
require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/NewsManager.php';

// RECUPERO NEWS
$lista_news_db = NewsManager::getNews();
$html_news_dinamico = "";

if (count($lista_news_db) > 0) {
    foreach ($lista_news_db as $news) {
        $titolo = htmlspecialchars($news['titolo']);
        $testo = htmlspecialchars($news['testo']);
        $percorso_img = htmlspecialchars($news['immagine']);
        
        $timestamp = strtotime($news['data_pubblicazione']);
        $data_iso = date("Y-m-d", $timestamp);
        $data_leggibile = date("j F Y", $timestamp); 

        $html_news_dinamico .= '
        <article class="news-card"> 
            <div class="card-image">
                <img src="../' . $percorso_img . '" alt="' . $titolo . '">
            </div>
            <div class="card-content">
                <time datetime="' . $data_iso . '">' . $data_leggibile . '</time>
                <h2>' . $titolo . '</h2>
                <p>' . $testo . '</p>
            </div>
        </article>';
    }
} else {
    $html_news_dinamico = '<p class="no-news">Al momento non sono presenti notizie ufficiali.</p>';
}

$pagina_html = file_get_contents('../html/news.html');
$pagina_html = str_replace('[lista_news]', $html_news_dinamico, $pagina_html);
$pagina_html = str_replace('[link_profilo]', 'AreaUtente.php', $pagina_html);

echo $pagina_html;