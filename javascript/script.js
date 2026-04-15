
// Oggetto di stato unificato per il carrello
const statoAcquisto = {
    tipo: null, // Verrà impostato a 'single' o 'ground'
    data: null,
    sessione: null,
    tribuna: null,
    quantita: 1
};


function selectDate(dayName, dayNumber, event) {
    // 1. Evidenzia la data selezionata
    const allDates = document.querySelectorAll('.date-tile');
    allDates.forEach(date => date.classList.remove('highlight'));
    event.currentTarget.classList.add('highlight');

    // 2. Salva la data nello stato
    const fullDateString = `${dayName} ${dayNumber} Maggio 2027`;
    statoAcquisto.data = fullDateString;

    window.dataSelezionata = '2027-05-' + dayNumber;


    // 3. Aggiorna i testi del riepilogo (se esistono nella pagina)
    const dateDisplay = document.getElementById('selected-full-date');
    if (dateDisplay) dateDisplay.textContent = fullDateString;

    const dateLabels = document.querySelectorAll('.current-date-label');
    dateLabels.forEach(label => label.textContent = fullDateString);

    // 4. Verifica in che pagina ci troviamo e mostra la sezione successiva
    const sessionSection = document.getElementById('session-selection'); // Esiste in Single Session
    const groundSection = document.getElementById('ground-pass-selection'); // Esiste in Ground Passes

    if (sessionSection) {
        // --- LOGICA PAGINA SINGLE SESSION ---
        sessionSection.classList.remove('d-none');

        // Aggiorna Sticky Bar
        const stepSession = document.getElementById('step-session');
        if (stepSession) stepSession.classList.add('active');

        const stickyBtn = document.getElementById('sticky-btn');
        if (stickyBtn) {
            stickyBtn.textContent = 'SELEZIONA SESSIONE';
            stickyBtn.href = '#session-selection';
        }

        sessionSection.scrollIntoView({ behavior: 'smooth', block: 'start' });

    } else if (groundSection) {
        // --- LOGICA PAGINA GROUND PASSES ---
        groundSection.classList.remove('d-none');
        groundSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }


    // invia dati al php per determinare il prezzo in base a quello che ho selezionato
    const prezzoDisplay = document.getElementById('prezzo-display');

    if (prezzoDisplay) {
        let dati = new FormData();
        dati.append('data_scelta', '2027-05-' + dayNumber);

        fetch('GroundPasses.php', { method: 'POST', body: dati })
            .then(risposta => risposta.text())
            .then(prezzo => {
                // Inietta il prezzo ricevuto dal PHP nell'HTML
                prezzoDisplay.innerHTML = prezzo;
            })
            .catch(errore => console.error("Errore", errore));
    }
}


/**
 * STEP 2: Selezione della Sessione e Chiamata AJAX
 */
function showSeatSelection(event, tipoSessione) {
    event.preventDefault();

    // 1. Salva la sessione nello stato dell'acquisto
    const sessionCard = event.currentTarget.closest('.session-card');
    if (sessionCard) {
        statoAcquisto.sessione = sessionCard.querySelector('h3').textContent;
    }

    // 2. Mostra la sezione delle tribune
    const seatSection = document.getElementById('seat-selection');
    if (seatSection) {
        seatSection.classList.remove('d-none');

        // Aggiorna Sticky Bar
        const stepSeat = document.getElementById('step-seat');
        if (stepSeat) stepSeat.classList.add('active');

        const stickyBtn = document.getElementById('sticky-btn');
        if (stickyBtn) {
            stickyBtn.textContent = 'SELEZIONA POSTO';
            stickyBtn.href = '#seat-selection';
        }

        seatSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }

    // 3. LA CHIAMATA AJAX (Taglio della stringa)
    // Cerco gli span dove andrò a stampare i prezzi
    const dPremium = document.getElementById('prezzo-premium');
    const dAntenore = document.getElementById('prezzo-antenore');
    const dFondo = document.getElementById('prezzo-fondo');
    const dAnello = document.getElementById('prezzo-anello');

    // Controlliamo di avere tutti i dati necessari prima di chiamare il DB
    if (dPremium && window.dataSelezionata && tipoSessione) {

        let dati = new FormData();
        dati.append('data_scelta', window.dataSelezionata);
        dati.append('sessione_scelta', tipoSessione);

        fetch('SingleSession.php', { method: 'POST', body: dati })
            .then(risposta => risposta.text()) // Riceviamo la stringa "€ 120,00|€ 85,00|..."
            .then(testoRicevuto => {
                // Spezziamo la stringa in un array usando il separatore "|"
                let prezzi = testoRicevuto.split('|');

                // Se il PHP ha risposto correttamente con 4 pezzi, aggiorniamo il testo
                if (prezzi.length === 4) {
                    dPremium.innerHTML = prezzi[0];
                    dAntenore.innerHTML = prezzi[1];
                    dFondo.innerHTML = prezzi[2];
                    dAnello.innerHTML = prezzi[3];
                }
            })
            .catch(errore => console.error("Errore AJAX:", errore));
    }
}

/**
 * STEP 3: Selezione della Tribuna (Usato SOLO in Single Session)
 */
function selectTribune(element) {
    const allSeats = document.querySelectorAll('.seat-category');
    allSeats.forEach(seat => seat.classList.remove('selected'));

    element.classList.add('selected');
    statoAcquisto.tribuna = element.querySelector('h3').textContent;

    // Estrai il prezzo per salvarlo
    const priceSpan = element.querySelector('.seat-price');
    if (priceSpan) {
        let priceText = priceSpan.textContent.replace('€', '').replace(',', '.').trim();
        statoAcquisto.prezzoSingolo = parseFloat(priceText);
    }
}

/**
 * Gestione Quantità (+ e -) (Usato in ENTRAMBE le pagine)
 */
function changeQty(amount) {
    const qtyInput = document.getElementById('ticket-qty');
    if (!qtyInput) return; // Se l'input non esiste, blocca la funzione

    let currentValue = parseInt(qtyInput.value, 10);
    if (isNaN(currentValue)) currentValue = 1;

    let newValue = currentValue + amount;

    if (newValue < 1) newValue = 1;
    if (newValue > 10) newValue = 10;

    qtyInput.value = newValue;
    statoAcquisto.quantita = newValue;
}

/**
 * GESTIONE FINALE: Aggiunta al carrello (Adattato per ENTRAMBE le pagine)
 */
document.addEventListener('DOMContentLoaded', () => {
    const acquistaBtn = document.getElementById('final-buy-btn');

    if (acquistaBtn) {
        acquistaBtn.addEventListener('click', (e) => {
            if (acquistaBtn.classList.contains('added-to-cart')) {
                e.preventDefault();
                return;
            }

            // Capiamo in quale pagina siamo valutando gli IDs
            const isSingleSession = document.getElementById('titolo-single') !== null;
            const isAbbonamento = document.getElementById('titolo-abbonamento') !== null;
            const isGroundPass = (!isSingleSession && !isAbbonamento);

            let carrello = JSON.parse(localStorage.getItem('carrelloItems')) || [];

            if (isSingleSession) {
                // Validazione per Single Session
                if (!statoAcquisto.data || !statoAcquisto.sessione || !statoAcquisto.tribuna) {
                    e.preventDefault();
                    alert("Per favore, completa la selezione: scegli una Data, una Sessione e un Posto.");
                    return;
                }
                statoAcquisto.tipo = "Single Session";

            } else if (isAbbonamento) {
                // Validazione per Abbonamento
                if (!statoAcquisto.tribuna) {
                    e.preventDefault();
                    alert("Per favore, seleziona una Tribuna per il tuo Abbonamento.");
                    return;
                }
                statoAcquisto.tipo = "Abbonamento";
                statoAcquisto.data = "Intero Torneo (18-24 Maggio)";
                statoAcquisto.sessione = "Tutte le sessioni";

            } else if (isGroundPass) {
                // Validazione per Ground Pass
                if (!statoAcquisto.data) {
                    e.preventDefault();
                    alert("Per favore, seleziona una Data per il tuo Ground Pass.");
                    return;
                }
                statoAcquisto.tipo = "Ground Pass";
                statoAcquisto.sessione = "Intera Giornata";
                statoAcquisto.tribuna = "Ingresso Generale";
                statoAcquisto.prezzoSingolo = 25.00; // Prezzo di default
            }

            let existingItemIndex = carrello.findIndex(item =>
                item.tipo === statoAcquisto.tipo &&
                item.data === statoAcquisto.data &&
                item.sessione === statoAcquisto.sessione &&
                item.tribuna === statoAcquisto.tribuna
            );

            if (existingItemIndex > -1) {
                carrello[existingItemIndex].quantita += statoAcquisto.quantita;
            } else {
                carrello.push({ ...statoAcquisto, id: Date.now() });
            }

            localStorage.setItem('carrelloItems', JSON.stringify(carrello));

            // Animazione e feedback visivo del bottone
            e.preventDefault();
            acquistaBtn.classList.add('added-to-cart');
            acquistaBtn.textContent = 'AGGIUNTO AL CARRELLO ✓';
            acquistaBtn.style.pointerEvents = 'none'; // Disabilita i click temporaneamente
            acquistaBtn.style.opacity = '0.7';

            // Genera il link "Vai al carrello" in modo STATICO (appare solo una volta)
            if (!document.getElementById('link-vai-carrello')) {
                const linkCarrello = document.createElement('a');
                linkCarrello.id = 'link-vai-carrello';
                linkCarrello.href = 'carrello.html';
                linkCarrello.innerHTML = 'Vai al carrello ➔';

                // Lo inseriamo sotto al bottone
                acquistaBtn.parentNode.insertBefore(linkCarrello, acquistaBtn.nextSibling);
            }

            // --- Reset del bottone dopo 3 secondi (3000 millisecondi) ---
            setTimeout(() => {
                acquistaBtn.classList.remove('added-to-cart');
                acquistaBtn.textContent = 'AGGIUNGI AL CARRELLO'; // Torna esattamente come prima
                acquistaBtn.style.pointerEvents = 'auto'; // Riabilita il click per altre aggiunte
                acquistaBtn.style.opacity = '1';
            }, 3000);
            // -----------------------------------------------------------
        });
    }
});

/**
 * PROTEZIONE GLOBALE FORM ANTI-DOPPIO CLICK
 * Si applica a Login, Modulo Contatti, Admin e Checkout
 */
document.addEventListener('DOMContentLoaded', () => {
    const allForms = document.querySelectorAll('form');

    allForms.forEach(form => {
        // Escludiamo i form che hanno logiche AJAX proprietarie o validazioni custom
        const excludedForms = ['form-registrazione', 'form-faq-admin', 'form-nuova-news', 'form-modifica-news'];
        if (excludedForms.includes(form.id) || form.classList.contains('form-delete-faq')) return;

        form.addEventListener('submit', function () {
            // Cerchiamo il bottone di submit o quelli con le classi che usi tu
            const submitBtn = form.querySelector('button[type="submit"], .btn-checkout');

            if (submitBtn) {
                // Blocca il bottone e dà un feedback visivo
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.7';
                submitBtn.style.cursor = 'wait';

                // Cambiamo il testo per far capire che sta caricando
                // Manteniamo le icone originali se ci sono, cambiando solo il testo
                if (submitBtn.textContent.trim() !== '') {
                    submitBtn.textContent = "Attendere...";
                }
            }
        });
    });
});

