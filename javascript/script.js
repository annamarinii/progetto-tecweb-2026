
// Oggetto di stato unificato per il carrello
const statoAcquisto = {
    tipo: null, // Verrà impostato a 'single' o 'ground'
    data: null,
    sessione: null,
    tribuna: null,
    quantita: 1
};

/**
 * STEP 1: Selezione della Data (Usato in ENTRAMBE le pagine)
 */
function selectDate(dayName, dayNumber, event) {
    // 1. Evidenzia la data selezionata
    const allDates = document.querySelectorAll('.date-tile');
    allDates.forEach(date => date.classList.remove('highlight'));
    event.currentTarget.classList.add('highlight');

    // 2. Salva la data nello stato
    const fullDateString = `${dayName} ${dayNumber} Maggio 2026`;
    statoAcquisto.data = fullDateString;

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
}

/**
 * STEP 2: Selezione della Sessione (Usato SOLO in Single Session)
 */
function showSeatSelection(event) {
    event.preventDefault(); 
    
    const sessionCard = event.currentTarget.closest('.session-card');
    if (sessionCard) {
        statoAcquisto.sessione = sessionCard.querySelector('h3').textContent;
    }

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
}

/**
 * STEP 3: Selezione della Tribuna (Usato SOLO in Single Session)
 */
function selectTribune(element) {
    const allSeats = document.querySelectorAll('.seat-category');
    allSeats.forEach(seat => seat.classList.remove('selected'));
    
    element.classList.add('selected');
    statoAcquisto.tribuna = element.querySelector('h3').textContent;
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
            // Capiamo in quale pagina siamo valutando quale contenitore esiste
            const isSingleSession = document.getElementById('session-selection') !== null;
            const isGroundPass = document.getElementById('ground-pass-selection') !== null;

            if (isSingleSession) {
                // Validazione per Single Session: serve Data + Sessione + Tribuna
                if (!statoAcquisto.data || !statoAcquisto.sessione || !statoAcquisto.tribuna) {
                    e.preventDefault(); 
                    alert("Per favore, completa la selezione: scegli una Data, una Sessione e un Posto.");
                } else {
                    statoAcquisto.tipo = "Single Session";
                    localStorage.setItem('bigliettoInCorso', JSON.stringify(statoAcquisto));
                }
            } else if (isGroundPass) {
                // Validazione per Ground Pass: serve SOLO la Data
                if (!statoAcquisto.data) {
                    e.preventDefault();
                    alert("Per favore, seleziona una Data per il tuo Ground Pass.");
                } else {
                    statoAcquisto.tipo = "Ground Pass";
                    statoAcquisto.sessione = "Intera Giornata"; // Valori di default
                    statoAcquisto.tribuna = "Ingresso Generale"; 
                    localStorage.setItem('bigliettoInCorso', JSON.stringify(statoAcquisto));
                }
            }
        });
    }
});