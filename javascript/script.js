
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
    const prezzoDisplay = document.getElementById('prezzo-ground');

    if (prezzoDisplay) {
        let dati = new FormData();
        dati.append('data_scelta', '2027-05-' + dayNumber);

        fetch('GroundPasses.php', { method: 'POST', body: dati })
            .then(risposta => risposta.json()) 
            .then(data => {
                if (data.prezzo && data.quantita_disponibile > 0) {
                    prezzoDisplay.textContent = "€ " + parseFloat(data.prezzo).toLocaleString('it-IT', { minimumFractionDigits: 2 });
                    prezzoDisplay.dataset.rawPrice = data.prezzo;
                } else {
                    prezzoDisplay.textContent = "Esaurito";
                    prezzoDisplay.classList.add('status-sold-out');
                }
            })
            .catch(errore => console.error("Errore AJAX:", errore));
    }
}


// STEP 2: Selezione della Sessione e Chiamata AJAX
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

    const dPremium = document.getElementById('prezzo-premium');
    const dAntenore = document.getElementById('prezzo-antenore');
    const dFondo = document.getElementById('prezzo-fondo');
    const dAnello = document.getElementById('prezzo-anello');

    if (dPremium && window.dataSelezionata && tipoSessione) {
        let dati = new FormData();
        dati.append('data_scelta', window.dataSelezionata);
        dati.append('sessione_scelta', tipoSessione);

        fetch('SingleSession.php', { method: 'POST', body: dati })
            .then(risposta => risposta.json()) 
            .then(data => {
                // Nella tua funzione formatta (dentro il fetch)
                const formatta = (info, elemento) => {
                    const seatCategory = elemento.closest('.seat-category');
                    if (info && info.quantita_disponibile > 0) {
                        // 1. Quello che vede l'utente (formattato)
                        elemento.textContent = "€ " + parseFloat(info.prezzo).toLocaleString('it-IT', {minimumFractionDigits: 2});
                        
                        // 2. Quello che legge il codice (dato puro)
                        elemento.dataset.rawPrice = info.prezzo; // Salva es: 120.50
                        elemento.classList.remove('status-sold-out');
                        if (seatCategory) seatCategory.classList.remove('disabled-seat');
                    } else {
                        elemento.textContent = "Esaurito";
                        elemento.dataset.rawPrice = "0";
                        elemento.classList.add('status-sold-out');
                        if (seatCategory) seatCategory.classList.add('disabled-seat');
                    }
                };

                // Mapping dei dati ricevuti dal JSON del PHP
                formatta(data.premium, dPremium);
                formatta(data.antenore, dAntenore);
                formatta(data.fondo, dFondo);
                formatta(data.anello, dAnello);
            })
            .catch(errore => console.error("Errore AJAX:", errore));
    }
}

// STEP 3: Selezione della Tribuna (Usato SOLO in Single Session)
function selectTribune(element) {
    if (element.classList.contains('disabled-seat')) return;

    const allSeats = document.querySelectorAll('.seat-category');
    allSeats.forEach(seat => seat.classList.remove('selected'));

    element.classList.add('selected');
    statoAcquisto.tribuna = element.querySelector('h3').textContent;

    // Recuperiamo il prezzo usando il nuovo metodo pulito
    const priceSpan = element.querySelector('.seat-price');
    statoAcquisto.prezzoSingolo = pulisciPrezzo(priceSpan); 
}

// Gestione Quantità (+ e -) (Usato in ENTRAMBE le pagine)
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

// GESTIONE FINALE: Aggiunta al carrello (AJAX verso PHP)
document.addEventListener('DOMContentLoaded', () => {
    const calendarContainer = document.getElementById('calendarContainer');

    if (calendarContainer) {
        // Usiamo l'Event Delegation: un unico listener per tutto il contenitore
        calendarContainer.addEventListener('click', (event) => {
            const tile = event.target.closest('.date-tile');
            
            if (tile) {
                const dayName = tile.dataset.day;
                const dayNumber = tile.dataset.number;
                selectDate(dayName, dayNumber, { currentTarget: tile });
            }
        });
    }

    // 2. GESTIONE SELEZIONE SESSIONE
    const sessionButtons = document.querySelectorAll('.session-card .buy-btn');

    sessionButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const tipo = this.getAttribute('data-session'); 
            
            // 3. Debug: controlla nella console del browser (F12) se questo appare
            console.log("Hai cliccato sulla sessione:", tipo);

            if (tipo) {
                // 4. Eseguiamo la funzione originale
                showSeatSelection(event, tipo);
            } else {
                console.error("Errore: attributo data-session mancante nel link!");
            }
        });
    });

    // 3. GESTIONE SELEZIONE TRIBUNA
    const seatCategories = document.querySelectorAll('.seat-category');
    seatCategories.forEach(category => {
        category.addEventListener('click', (e) => {
            selectTribune(e.currentTarget);
        });
        // Gestione accessibilità tastiera
        category.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') selectTribune(e.currentTarget);
        });
    });

    // 4. GESTIONE QUANTITÀ
    const btnMinus = document.querySelector('.qty-btn.minus');
    const btnPlus = document.querySelector('.qty-btn.plus');

if (btnMinus && btnPlus) {
    // Listener per il tasto meno
    btnMinus.addEventListener('click', () => {
        changeQty(-1);
    });

    // Listener per il tasto più
    btnPlus.addEventListener('click', () => {
        changeQty(1);
    });
}

    const acquistaBtn = document.getElementById('final-buy-btn');

    if (acquistaBtn) {
        acquistaBtn.addEventListener('click', (e) => {
            e.preventDefault();

            // Evita doppi invii se l'animazione è in corso
            if (acquistaBtn.classList.contains('added-to-cart')) return;

            // Identificazione della pagina corrente
            const isSingleSession = document.getElementById('titolo-single') !== null;
            const isAbbonamento = document.getElementById('titolo-abbonamento') !== null;
            const isGroundPass = (!isSingleSession && !isAbbonamento);

            // FUNZIONE INTERNA 1: Calcola l'ID del programma basandosi sulla data
            function calcolaIdProgramma(dataScelta, sessioneScelta, tipo) {
                if (tipo === "Abbonamento") return null;

                // Estrae il numero del giorno (es. "18" da "18 Maggio 2027")
                const matchGiorno = dataScelta.match(/\d+/);
                if (!matchGiorno) return null;
                const giorno = parseInt(matchGiorno[0]);

                if (tipo === "Ground Pass") {
                    // 18 Maggio -> ID 15, 19 -> 16, ..., 24 -> 21
                    return 15 + (giorno - 18);
                }

                if (tipo === "Single Session") {
                    // 18 Diurna -> 1, 18 Serale -> 2, 19 Diurna -> 3...
                    let baseId = (giorno - 18) * 2 + 1;
                    if (sessioneScelta && sessioneScelta.toLowerCase().includes('serale')) {
                        baseId += 1;
                    }
                    return baseId;
                }
                return null;
            }

            // FUNZIONE INTERNA 2: Pulisce il prezzo HTML per il database
            function pulisciPrezzo(elemento) {
                if (!elemento || !elemento.dataset.rawPrice) {
                    console.warn("Elemento o prezzo non trovato");
                    return 0;
                }
                return parseFloat(elemento.dataset.rawPrice);
            }

            // LOGICA DI ACQUISIZIONE DATI
            if (isSingleSession) {
                if (!statoAcquisto.data || !statoAcquisto.sessione || !statoAcquisto.tribuna) {
                    alert("Per favore, completa la selezione: scegli Data, Sessione e Posto.");
                    return;
                }
                statoAcquisto.tipo = "Single Session";
                const postoScelto = document.querySelector('.seat-category.selected .seat-price');
                statoAcquisto.prezzoSingolo = pulisciPrezzo(postoScelto);
                statoAcquisto.idProgramma = calcolaIdProgramma(statoAcquisto.data, statoAcquisto.sessione, "Single Session");

            } else if (isAbbonamento) {
                if (!statoAcquisto.tribuna) {
                    alert("Per favore, seleziona una Tribuna per il tuo Abbonamento.");
                    return;
                }
                statoAcquisto.tipo = "Abbonamento";
                statoAcquisto.data = "Intero Torneo (18-24 Maggio)";
                statoAcquisto.sessione = "Tutte le sessioni";
                const abbScelto = document.querySelector('.seat-category.selected .seat-price');
                statoAcquisto.prezzoSingolo = pulisciPrezzo(abbScelto);
                statoAcquisto.idProgramma = null;

            } else if (isGroundPass) {
                if (!statoAcquisto.data) {
                    alert("Per favore, seleziona una Data per il tuo Ground Pass.");
                    return;
                }
                statoAcquisto.tipo = "Ground Pass";
                statoAcquisto.sessione = "Intera Giornata";
                statoAcquisto.tribuna = "Ingresso Generale";
                const prezzoDisplay = document.getElementById('prezzo-ground');
                statoAcquisto.prezzoSingolo = pulisciPrezzo(prezzoDisplay);
                statoAcquisto.idProgramma = calcolaIdProgramma(statoAcquisto.data, null, "Ground Pass");
            }

            // INVIO DATI AL SERVER
            const pacchettoBiglietto = {
                tipologia: statoAcquisto.tipo,
                titolo: statoAcquisto.tribuna,
                data: statoAcquisto.data,
                sessione: statoAcquisto.sessione,
                prezzo: statoAcquisto.prezzoSingolo,
                quantita: statoAcquisto.quantita,
                idProgramma: statoAcquisto.idProgramma
            };

            fetch('../php-Manager/AggiungiCarrello.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(pacchettoBiglietto)
            })
                .then(risposta => risposta.json())
                .then(dati => {
                    if (dati.status === 'success') {
                        // Feedback visivo: Successo
                        acquistaBtn.classList.add('added-to-cart');
                        acquistaBtn.textContent = 'AGGIUNTO ✓';
                        acquistaBtn.style.opacity = '0.7';

                        // Mostra il link al carrello se non esiste
                        if (!document.getElementById('link-vai-carrello')) {
                            const linkCarrello = document.createElement('a');
                            linkCarrello.id = 'link-vai-carrello';
                            linkCarrello.href = '../php-pages/Carrello.php';
                            linkCarrello.innerHTML = 'Vai al carrello ➔';
                            linkCarrello.style.display = 'block';
                            linkCarrello.style.marginTop = '10px';
                            acquistaBtn.parentNode.insertBefore(linkCarrello, acquistaBtn.nextSibling);
                        }

                        setTimeout(() => {
                            acquistaBtn.classList.remove('added-to-cart');
                            acquistaBtn.textContent = 'AGGIUNGI AL CARRELLO';
                            acquistaBtn.style.opacity = '1';
                        }, 3000);
                    } else {
                        alert("Errore nel salvataggio del biglietto.");
                    }
                })
                .catch(errore => console.error("Errore di connessione AJAX:", errore));
        });
    }
});



// PROTEZIONE GLOBALE FORM ANTI-DOPPIO CLICK
// Si applica a Login, Modulo Contatti, Admin e Checkout
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

