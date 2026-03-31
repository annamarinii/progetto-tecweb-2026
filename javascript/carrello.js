document.addEventListener('DOMContentLoaded', () => {
    const cartContainer = document.getElementById('cart-items-container');
    const emptyMsg = document.getElementById('cart-empty-msg');
    const subtotalEl = document.getElementById('cart-subtotale');
    const totalEl = document.getElementById('cart-totale');
    const btnCheckout = document.getElementById('btn-checkout');

    function renderCart() {
        // Leggi i dati dal localStorage
        let carrello = JSON.parse(localStorage.getItem('carrelloItems')) || [];
        
        // Pulisci i vecchi item (mantiene l'empty message, lo nascondiamo se serve)
        const oldCards = cartContainer.querySelectorAll('.cart-item-card');
        oldCards.forEach(card => card.remove());

        if (carrello.length === 0) {
            emptyMsg.style.display = 'block';
            subtotalEl.textContent = '€ 0.00';
            totalEl.textContent = '€ 0.00';
            btnCheckout.setAttribute('disabled', 'true');
            return;
        }

        // Ci sono elementi, nascondi il messaggio vuoto
        emptyMsg.style.display = 'none';
        btnCheckout.removeAttribute('disabled');

        let totale = 0;

        // Crea dinamicamente le card per ogni biglietto
        carrello.forEach(item => {
            const prezzoUnitario = item.prezzoSingolo || 0;
            const quantita = item.quantita || 1;
            const prezzoTotaleRiga = prezzoUnitario * quantita;
            
            totale += prezzoTotaleRiga;

            const card = document.createElement('article');
            card.className = 'cart-item-card';

            card.innerHTML = `
                <div class="cart-item-info">
                    <h3>${item.tipo} - ${item.tribuna}</h3>
                    <p><strong>Data:</strong> ${item.data}</p>
                    <p><strong>Sessione:</strong> ${item.sessione}</p>
                    <p><strong>Quantità:</strong> ${quantita}</p>
                </div>
                <div class="cart-item-actions">
                    <span class="cart-item-price">€ ${prezzoTotaleRiga.toFixed(2)}</span>
                    <button type="button" class="btn-remove-item" aria-label="Rimuovi biglietto" onclick="rimuoviDalCarrello(${item.id})">
                        Elimina
                    </button>
                </div>
            `;
            
            cartContainer.appendChild(card);
        });

        // Aggiorna i testi del totale
        subtotalEl.textContent = `€ ${totale.toFixed(2)}`;
        totalEl.textContent = `€ ${totale.toFixed(2)}`;
    }

    // Modalità globale per la funzione onclick nell'HTML generato
    window.rimuoviDalCarrello = function(idToRemove) {
        let carrello = JSON.parse(localStorage.getItem('carrelloItems')) || [];
        carrello = carrello.filter(item => item.id !== idToRemove);
        localStorage.setItem('carrelloItems', JSON.stringify(carrello));
        
        // Ri-renderizza
        renderCart();
    };

    // Primo render all'avvio
    renderCart();
});
