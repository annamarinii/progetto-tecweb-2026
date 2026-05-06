/**
 * Gestione dinamica del Carrello - Patavium Open
 */

window.rimuoviItem = function(indice) {
    // Selezioniamo la card tramite l'attributo data-index (coerenza con il PHP)
    const cardDaRimuovere = document.querySelector(`article[data-index="${indice}"]`);

    // Feedback immediato: usiamo le classi CSS invece di modificare lo stile inline
    if (cardDaRimuovere) {
        cardDaRimuovere.classList.add('removing-item'); 
    }

    fetch('../php-Manager/RimuoviCarrello.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ indice: indice })
    })
    .then(res => {
        if (!res.ok) throw new Error("Errore di rete");
        return res.json();
    })
    .then(data => {
        if(data.status === 'success') {
            // Se il carrello è vuoto, il server universitario deve servire il messaggio "vuoto"
            if (data.carrelloVuoto) {
                location.reload(); 
            } else {
                if (cardDaRimuovere) {
                    // Animazione di uscita
                    cardDaRimuovere.classList.add('fade-out-right');
                    
                    setTimeout(() => {
                        cardDaRimuovere.remove();
                        // Aggiorniamo il totale ricevuto dal PHP
                        aggiornaTotaleCarrello(data.nuovoTotale);
                    }, 300);
                }
            }
        } else {
            // Ripristino in caso di errore logico del server
            if (cardDaRimuovere) cardDaRimuovere.classList.remove('removing-item');
            alert("Errore: " + (data.message || "Impossibile rimuovere l'articolo."));
        }
    })
    .catch(err => {
        console.error("Errore AJAX:", err);
        if (cardDaRimuovere) cardDaRimuovere.classList.remove('removing-item');
    });
};

/**
 * Aggiorna il totale nel DOM con formattazione locale it-IT
 */
function aggiornaTotaleCarrello(nuovoTotale) {
    const totaleElement = document.getElementById('totale-prezzo');
    const subTotaleElement = document.getElementById('cart-subtotale');
    
    const totaleFormattato = "€ " + parseFloat(nuovoTotale).toLocaleString('it-IT', {
        minimumFractionDigits: 2
    });

    if (totaleElement) totaleElement.textContent = totaleFormattato;
    if (subTotaleElement) subTotaleElement.textContent = totaleFormattato;
}

document.addEventListener('DOMContentLoaded', () => {
    const btnCheckout = document.getElementById('btn-checkout');
    if (btnCheckout) {
        btnCheckout.addEventListener('click', function(e) {
            // Invece di andare su Checkout.php, facciamo una chiamata AJAX
            // o gestiamo l'acquisto qui se hai un file che se ne occupa
            this.textContent = "Elaborazione...";
            this.classList.add('btn-loading');
            this.disabled = true;

            // Se vuoi restare sulla stessa pagina, devi avere un file PHP 
            // che riceve questa richiesta, ad esempio lo stesso carrello.php
            window.location.href = '../php-Manager/Checkout.php';
        });
    }
});