<?php

require '../php-Manager/init_session.php';
/** @var string $destinazione_profilo */

$carrello = isset($_SESSION['carrello']) ? $_SESSION['carrello'] : [];
$totale = 0;
$html_lista = '';

if (empty($carrello)) {
    $html_lista = '<div class="cart-empty"><p>Il carrello è vuoto.</p></div>';
} else {
    foreach ($carrello as $index => $item) {
        $subtotale = $item['prezzo'] * $item['quantita'];
        $totale += $subtotale;

        $html_lista .= "
            <article class='cart-item-card' data-index='{$index}'>
                <div class='cart-item-info'>
                    <h3>{$item['tipologia']} - {$item['titolo']}</h3>
                    <p>Data: {$item['data']} ({$item['sessione']})</p>
                    <p>Quantità: {$item['quantita']}</p>
                </div>
                <div class='cart-item-actions'>
                    <span class='cart-item-price'>€ " . number_format($subtotale, 2, ',', '.') . "</span>
                    <button type='button' class='btn-delete' onclick='rimuoviItem($index)'>Elimina</button>
                </div>
            </article>";
    }
}   

// --- GESTIONE DEL BANNER DI SUCCESSO ---
$html_banner = ""; 

if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['ordine'])) {
    $numero_ordine = htmlspecialchars($_GET['ordine']);

    $html_banner = "
    <div class='order-status-banner success-banner'>
        <div class='banner-icon'>🎉</div>
        <div class='banner-content'>
            <h2>Pagamento Riuscito!</h2>
            <p>Grazie per il tuo acquisto. Il tuo ordine è il: <strong>#{$numero_ordine}</strong></p>
            <p>I tuoi biglietti sono pronti nella tua Area Utente.</p>
        </div>
    </div>
    ";
}

$pagina = file_get_contents('../html/carrello.html');
$pagina = str_replace('[link_profilo]', $destinazione_profilo, $pagina);
$pagina = str_replace('[banner_esito]', $html_banner, $pagina);
$pagina = str_replace('[lista_carrello]', $html_lista, $pagina);
$pagina = str_replace('[totale_carrello]', "€ " . number_format($totale, 2, ',', '.'), $pagina);

echo $pagina;
