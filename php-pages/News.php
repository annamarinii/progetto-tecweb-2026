<?php
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
        $html_news_dinamico .= '        <img src="../' . $percorso_img . '" alt="">';
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

$template_percorso = '../html/news.html';

if (file_exists($template_percorso)) {
    $pagina_html = file_get_contents($template_percorso);

    $pagina_finita = str_replace('[lista_news]', $html_news_dinamico, $pagina_html);
    echo $pagina_finita;
} else {
    die("Errore critico: Impossibile trovare il template news.html in " . $template_percorso);
}
?>