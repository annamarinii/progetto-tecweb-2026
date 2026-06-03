<?php
require_once '../php-manager/init_session.php';
require_once '../php-manager/tool.php';
require_once '../php-manager/ticket_manager.php';

$frammento_card = file_get_contents(__DIR__ . '/../item/biglietto_card.html');
$categorie = TicketManager::getCategorieBiglietti();

$html_cards = "";

if (empty($categorie)) {
    $html_cards = "<p role='status'>Nessun biglietto attualmente disponibile.</p>";
} else {
    foreach ($categorie as $cat) {
        $tipo = $cat['tipo'];
        $prezzo_formattato = $cat['prezzo_min'] !== null
            ? number_format((float)$cat['prezzo_min'], 2, ',', '.') . ' €'
            : 'Vedi disponibilità';

        if ($tipo === 'abbonamento') {
            $link      = 'abbonamento.php';
            $cat_class = 'card-abbonamento';
            $cat_text  = 'Abbonamento';
            $img_url   = '../assets/images/abbonamento.webp';
            $img_alt   = 'Panoramica del campo centrale con le tribune gremite di spettatori durante il Patavium Open';
        } elseif ($tipo === 'single') {
            $link      = 'single_session.php';
            $cat_class = 'card-single';
            $cat_text  = 'Single Session';
            $img_url   = '../assets/images/single_session.webp';
            $img_alt   = 'Spettatori in tribuna che assistono a un match sul campo del Patavium Open';
        } else {
            $link      = 'ground_passes.php';
            $cat_class = 'card-ground';
            $cat_text  = 'Ground Pass';
            $img_url   = '../assets/images/ground_passes.webp';
            $img_alt   = 'Vista dell\'area hospitality e del villaggio del torneo Patavium Open';
        }

        $html_cards .= str_replace(
            ['[CategoriaClass]', '[TipoID]', '[CategoriaText]', '[URLImmagine]', '[AltTextImmagine]', '[PrezzoBiglietto]', '[LinkAcquisto]'],
            [
                $cat_class,
                htmlspecialchars($tipo,             ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($cat_text,         ENT_QUOTES, 'UTF-8'),
                $img_url,
                htmlspecialchars($img_alt,          ENT_QUOTES, 'UTF-8'),
                htmlspecialchars($prezzo_formattato, ENT_QUOTES, 'UTF-8'),
                $link
            ],
            $frammento_card
        );
    }
}

$pagina_html = file_get_contents(__DIR__ . '/../pages/biglietti.html');
$pagina_html = str_replace('[Header]', Tool::buildHeader('biglietti'), $pagina_html);
$pagina_html = str_replace('[Footer]', Tool::buildFooter('biglietti'), $pagina_html);
$pagina_html = str_replace('[lista_biglietti]', $html_cards, $pagina_html);

echo $pagina_html;
