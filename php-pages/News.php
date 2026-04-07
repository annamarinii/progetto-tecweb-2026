<?php
require_once '../php-dbManager/init_session.php';
require_once '../php-dbManager/NewsManager.php';

$lista_news_db = NewsManager::getNews();

$html_news_dinamico = "";

if (count($lista_news_db) > 0) {
    foreach ($lista_news_db as $news) {
        $titolo = htmlspecialchars($news['titolo']);
        $testo = htmlspecialchars($news['testo']);
        $percorso_img = htmlspecialchars($news['immagine']);
        $autore = htmlspecialchars($news['nome'] . " " . $news['cognome']);

        $timestamp = strtotime($news['data_pubblicazione']);
        $data_iso = date("Y-m-d", $timestamp);
        $data_leggibile = date("j F Y", $timestamp); 

        $html_news_dinamico .= '<article class="news-card">'; 
        $html_news_dinamico .= '    <div class="card-image">';
$html_news_dinamico .= '<img src="../' . $percorso_img . '" alt="' . $titolo . '">';
        $html_news_dinamico .= '    </div>';
        $html_news_dinamico .= '    <div class="card-content">';
        $html_news_dinamico .= '        <time datetime="' . $data_iso . '">' . $data_leggibile . '</time>';
        $html_news_dinamico .= '        <h2>' . $titolo . '</h2>';
        $html_news_dinamico .= '        <p>' . $testo . '</p>';
        $html_news_dinamico .= '    </div>';
        $html_news_dinamico .= '</article>';
    }
} else {
    // Messaggio se il database è vuoto
    $html_news_dinamico = '<p class="no-news">Al momento non sono presenti notizie ufficiali. Torna a trovarci presto!</p>';
}

$pagina_html = file_get_contents('../html/news.html');

$pagina_html = str_replace('[lista_news]', $html_news_dinamico, $pagina_html);
$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);

echo $pagina_html;