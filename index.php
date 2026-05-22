<?php

// Inizializzazione della sessione e inclusione dei file manager principali
require_once 'php-Manager/init_session.php';
require_once 'php-Manager/NewsManager.php';
require_once 'php-Manager/Tool.php';

// 1. Recupero delle ultime 3 news dal database per la sezione della Home
$ultime_news = NewsManager::getUltimeNews(3);

// 2. Lettura del template HTML della Home Page
$pagina_html = file_get_contents('home.html');

// 3. Sostituzione dei componenti macro strutturali tramite la classe Tool
$pagina_html = str_replace('[Header]', Tool::buildHeader('home'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('home'), $pagina_html);

// 4. Generazione dinamica e accessibile delle News Cards
$news_html_content = "";
for ($i = 0; $i < 3; $i++) {
    if (isset($ultime_news[$i])) {
        // Sanificazione rigorosa contro vulnerabilità XSS (Requisito di Sicurezza)
        $titolo = Tool::pulisciInput($ultime_news[$i]['titolo']);
        $testo_completo = Tool::pulisciInput($ultime_news[$i]['testo']);
        $immagine = Tool::pulisciInput($ultime_news[$i]['immagine']);
        
        // Taglio del testo per la preview della card commerciale
        $testo_breve = mb_strlen($testo_completo) > 140 ? mb_substr($testo_completo, 0, 140) . '...' : $testo_completo;
        
        // Gestione semantica della data (Tag <time> richiesto dai professori)
        $data_timestamp = strtotime($ultime_news[$i]['data_pubblicazione']);
        $data_iso = date('Y-m-d', $data_timestamp);
        $data_it = date('d/m/Y', $data_timestamp);

        // Costruzione del markup della card a norma Legge Stanca (Criteri AA)
        // Nota: width e height espliciti evitano il Layout Shift; aria-label dà contesto al "Leggi di più"
        $news_html_content .= "
                <article class=\"news-card\">
                    <img src=\"assets/images/{$immagine}\" alt=\"Immagine correlata a: {$titolo}\" width=\"300\" height=\"200\" />
                    <div class=\"news-content\">
                        <time datetime=\"{$data_iso}\" class=\"news-date\">{$data_it}</time>
                        <h3>{$titolo}</h3>
                        <p>{$testo_breve}</p>
                        <a href=\"php-pages/News.php?id={$ultime_news[$i]['idNews']}\" aria-label=\"Leggi la notizia completa: {$titolo}\">Leggi di più</a>
                    </div>
                </article>";
    } else {
        // Card di Fallback se il database dovesse contenere meno di 3 notizie
        $news_html_content .= "
                <article class=\"news-card fallback\">
                    <img src=\"assets/images/patavium_arena.jpg\" alt=\"Campi da tennis in terra rossa del Patavium Open\" width=\"300\" height=\"200\" />
                    <div class=\"news-content\">
                        <h3>Novità in arrivo</h3>
                        <p>Il comitato organizzativo sta preparando i prossimi aggiornamenti. Torna a trovarci a breve!</p>
                    </div>
                </article>";
    }
}

// Iniezione del blocco cards all'interno del rispettivo segnaposto
$pagina_html = str_replace('[NewsCards]', $news_html_content, $pagina_html);

// 5. Rendering finale della pagina web
echo $pagina_html;