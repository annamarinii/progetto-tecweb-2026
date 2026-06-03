<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/news_manager.php';
require_once '../php-manager/tool.php';

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
        // Testo alternativo accessibile dell'immagine (fallback se vuoto/non valorizzato)
        $alt_immagine = !empty($news['alt_immagine']) ? Tool::pulisciInput($news['alt_immagine']) : 'Immagine della news';

        $timestamp = strtotime($news['data_pubblicazione']);
        $data_iso = date("Y-m-d", $timestamp);
        $data_leggibile = date("j F Y", $timestamp);

        // Copiamo il template
        $card_html = $template_news_card;

        // Iniezione sicura dei dati
        $card_html = str_replace(
            ['[IdNews]', '[PercorsoImg]', '[AltImmagine]', '[Titolo]', '[DataIso]', '[DataLeggibile]', '[Testo]'],
            [(int)$news['idNews'], $percorso_img, $alt_immagine, $titolo, $data_iso, $data_leggibile, $testo],
            $card_html
        );

        $html_news_dinamico .= $card_html;
    }
} else {
    $html_news_dinamico = '<p class="no-news" role="status">Al momento non sono presenti notizie ufficiali.</p>';
}

$pagina_html = file_get_contents('../pages/news.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('news'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('news'), $pagina_html);
$pagina_html = str_replace('[lista_news]', $html_news_dinamico, $pagina_html);

// SEO DINAMICA: usa la news in primo piano (la più recente, prima della lista) per
// generare meta description e keywords specifiche. Se non ci sono news, restano null
// e Tool::setupSEO applicherà i valori di fallback generici.
$metaDesc = null;
$metaKeys = null;

if (count($lista_news_db) > 0) {
    $news_in_primo_piano = $lista_news_db[0];

    // Description: testo della news ripulito dall'HTML e troncato a 150 caratteri.
    // (setupSEO penserà poi all'escaping prima di inserirlo nel content="...").
    $testo_pulito = trim(strip_tags($news_in_primo_piano['testo']));
    $limite = 150;
    $lunghezza = function_exists('mb_strlen') ? mb_strlen($testo_pulito, 'UTF-8') : strlen($testo_pulito);
    if ($lunghezza > $limite) {
        $testo_pulito = function_exists('mb_substr')
            ? mb_substr($testo_pulito, 0, $limite, 'UTF-8')
            : substr($testo_pulito, 0, $limite);
        $testo_pulito = rtrim($testo_pulito) . '...';
    }
    $metaDesc = $testo_pulito;

    // Keywords: keyword fisse + 2-3 parole significative estratte dal titolo
    // (scartando articoli/preposizioni corte).
    $titolo_pulito = trim(strip_tags($news_in_primo_piano['titolo']));
    $parole_titolo = preg_split('/\s+/', $titolo_pulito, -1, PREG_SPLIT_NO_EMPTY);
    $parole_chiave = array_filter($parole_titolo, static function ($parola) {
        $len = function_exists('mb_strlen') ? mb_strlen($parola, 'UTF-8') : strlen($parola);
        return $len > 3;
    });
    $parole_chiave = array_slice(array_values($parole_chiave), 0, 3);
    $metaKeys = 'Patavium Open, News, ' . implode(', ', $parole_chiave);
}

$pagina_html = Tool::setupSEO($pagina_html, $metaDesc, $metaKeys);

echo $pagina_html;