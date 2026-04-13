<?php

$is_root = true;
require_once 'php-dbManager/init_session.php';
require_once 'php-dbManager/NewsManager.php';
/** @var string $destinazione_profilo */

$ultime_news = NewsManager::getUltimeNews(3);

$pagina_html = file_get_contents('home.html');

$pagina_html = str_replace('[link_profilo]', $destinazione_profilo, $pagina_html);

for ($i = 0; $i < 3; $i++) {
    $num = $i + 1; // Genera 1, 2 e 3 per i segnaposti

    if (isset($ultime_news[$i])) {
        $notizia = $ultime_news[$i];

        $testo_breve = strlen($notizia['testo']) > 120 ? substr($notizia['testo'], 0, 120) . '...' : $notizia['testo'];

        $data_db = strtotime($notizia['data_pubblicazione']);
        $mesi = ['', 'Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'];
        $data_formattata = date('j', $data_db) . ' ' . $mesi[(int)date('n', $data_db)] . ' ' . date('Y', $data_db);

        $pagina_html = str_replace("[news_{$num}_img]", htmlspecialchars($notizia['immagine']), $pagina_html);
        $pagina_html = str_replace("[news_{$num}_date_iso]", date('Y-m-d', $data_db), $pagina_html);
        $pagina_html = str_replace("[news_{$num}_date]", $data_formattata, $pagina_html);
        $pagina_html = str_replace("[news_{$num}_title]", htmlspecialchars($notizia['titolo']), $pagina_html);
        $pagina_html = str_replace("[news_{$num}_text]", htmlspecialchars($testo_breve), $pagina_html);
    } else {
        // Fallback: se mancano notizie, inserisco dati vuoti di sicurezza
        $pagina_html = str_replace("[news_{$num}_img]", "assets/images/default-news.jpg", $pagina_html);
        $pagina_html = str_replace("[news_{$num}_date_iso]", "", $pagina_html);
        $pagina_html = str_replace("[news_{$num}_date]", "", $pagina_html);
        $pagina_html = str_replace("[news_{$num}_title]", "In arrivo...", $pagina_html);
        $pagina_html = str_replace("[news_{$num}_text]", "Presto nuove notizie sul torneo.", $pagina_html);
    }
}

echo $pagina_html;
