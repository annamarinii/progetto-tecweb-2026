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

        // Costruiamo la riga HTML per ogni biglietto
        $html_lista .= "
            <article class='cart-item-card'>
                <div class='cart-item-info'>
                    <h3>{$item['tipologia']} - {$item['titolo']}</h3>
                    <p>Data: {$item['data']} ({$item['sessione']})</p>
                    <p>Quantità: {$item['quantita']}</p>
                </div>
                <div class='cart-item-actions'>
                    <span class='cart-item-price'>€ " . number_format($subtotale, 2, ',', '.') . "</span>
                    <button onclick='rimuoviItem($index)'>Elimina</button>
                </div>
            </article>";
    }
}

// --- GESTIONE DEL BANNER DI SUCCESSO ---
$html_banner = ""; // Di default il banner è VUOTO (così la scritta [banner_esito] scompare)

// Controllo se l'utente arriva dal Checkout con successo
if (isset($_GET['success']) && $_GET['success'] == 1 && isset($_GET['ordine'])) {
    $numero_ordine = htmlspecialchars($_GET['ordine']);

    // Se c'è un successo, riempio il banner con l'HTML verde
    $html_banner = "
    <div style='text-align: center; background-color: #e8f5e9; color: #2e7d32; padding: 20px; border-radius: 8px; margin-bottom: 30px; border: 2px solid #4caf50;'>
        <h2>🎉 Pagamento Riuscito!</h2>
        <p>Grazie per il tuo acquisto. Il tuo numero d'ordine è: <strong>#{$numero_ordine}</strong></p>
        <p>I tuoi biglietti sono al sicuro. Puoi visualizzarli nella tua Area Utente.</p>
    </div>
    ";
}
// ---------------------------------------

$pagina = file_get_contents('../html/carrello.html');
$pagina = str_replace('[link_profilo]', $destinazione_profilo, $pagina);
$pagina = str_replace('[banner_esito]', $html_banner, $pagina); // Ora il PHP sa cosa fare!
$pagina = str_replace('[lista_carrello]', $html_lista, $pagina);
$pagina = str_replace('[totale_carrello]', "€ " . number_format($totale, 2, ',', '.'), $pagina);

echo $pagina;
