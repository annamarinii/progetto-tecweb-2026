<?php

require '../php-Manager/init_session.php';
require_once '../php-Manager/Tool.php';

$carrello = isset($_SESSION['carrello']) ? $_SESSION['carrello'] : [];
$totale = 0;
$html_lista = '';
$html_pulsante_acquisto = '';

// 1. GESTIONE LISTA PRODOTTI E CALCOLO TOTALE
// 1. GESTIONE LISTA PRODOTTI E CALCOLO TOTALE
if (empty($carrello)) {
    $html_lista = '<div class="cart-empty"><p>Il carrello è vuoto.</p></div>';

    // Messaggio che sostituisce il pulsante se il carrello è vuoto
    $html_pulsante_acquisto = '<p class="checkout-disabled-msg">Il carrello è vuoto. Aggiungi dei biglietti per poter acquistare.</p>';
} else {
    // Carichiamo il template UNA SOLA VOLTA per non appesantire il server
    $template_cart_item = file_get_contents('../html/item/carrello_item.html');

    foreach ($carrello as $index => $item) {
        $subtotale = $item['prezzo'] * $item['quantita'];
        $totale += $subtotale;

        $subtotale_formattato = number_format($subtotale, 2, ',', '.');

        // Facciamo una copia "fresca" del template per questo specifico prodotto
        $item_html = $template_cart_item;

        // Rimpiazziamo i segnaposti con i valori veri
        $item_html = str_replace('[Index]', $index, $item_html);
        $item_html = str_replace('[Tipologia]', $item['tipologia'], $item_html);
        $item_html = str_replace('[Titolo]', $item['titolo'], $item_html);
        $item_html = str_replace('[Data]', $item['data'], $item_html);
        $item_html = str_replace('[Sessione]', $item['sessione'], $item_html);
        $item_html = str_replace('[Quantita]', $item['quantita'], $item_html);
        $item_html = str_replace('[Subtotale]', $subtotale_formattato, $item_html);

        // Aggiungiamo il tassello HTML completato alla grande lista
        $html_lista .= $item_html;
    }

    // Pulsante attivo se il carrello contiene elementi
    $html_pulsante_acquisto = '
        <form action="../php-Manager/Checkout.php" method="POST">
            <button type="submit" class="btn-checkout">Completa Acquisto</button>
        </form>';
}

// 2. GESTIONE DEL BANNER DI SUCCESSO (dopo acquisto)
$html_banner = ""; 
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['ordine'])) {
    $numero_ordine = htmlspecialchars($_GET['ordine']);

    $html_banner = "
    <div class='order-status-banner success-banner'>

        <div class='banner-content'>
            <h2>Pagamento Riuscito!</h2>
            <p>Grazie per il tuo acquisto. Il tuo ordine è il: <strong>#{$numero_ordine}</strong></p>
            <p>I tuoi biglietti sono pronti nella tua Area Utente.</p>
        </div>
    </div>
    ";
}

// 3. CARICAMENTO TEMPLATE E SOSTITUZIONE SEGNAPOSTO
$pagina = file_get_contents('../html/carrello.html');

$pagina = str_replace('[Header]', Tool::buildHeader('carrello'), $pagina);
$pagina = str_replace('[Footer]', Tool::buildFooter('carrello'), $pagina);

$pagina = str_replace('[banner_esito]', $html_banner, $pagina);
$pagina = str_replace('[lista_carrello]', $html_lista, $pagina);
$pagina = str_replace('[totale_carrello]', "€ " . number_format($totale, 2, ',', '.'), $pagina);
$pagina = str_replace('[pulsante_acquisto]', $html_pulsante_acquisto, $pagina);

echo $pagina;