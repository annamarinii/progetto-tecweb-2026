// Questo file ora gestisce solo le AZIONI, non il disegno della pagina
// Funzione globale per rimuovere un biglietto dal carrello in modo diretto
window.rimuoviItem = function(indice) {
    // Rimosso il pop-up di conferma! Partiamo subito con la chiamata al server.
    fetch('../php-Manager/RimuoviCarrello.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ indice: indice })
    })
        .then(res => res.json())
        .then(data => {
            if(data.status === 'success') {
                // Se il PHP ha rimosso il biglietto con successo, ricarico la pagina all'istante
                location.reload();
            } else {
                console.error("Il server non è riuscito a rimuovere il biglietto.");
            }
        })
        .catch(errore => console.error("Errore di connessione AJAX:", errore));
};

// ... qui sotto c'è la tua gestione del btn-checkout che va benissimo ...

document.addEventListener('DOMContentLoaded', () => {
    const btnCheckout = document.getElementById('btn-checkout');
    if (btnCheckout) {
        btnCheckout.addEventListener('click', function() {
            window.location.href = '../php-Manager/Checkout.php';
        });
    }
});