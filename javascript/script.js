
// Oggetto di stato unificato per il carrello
const statoAcquisto = {
    tipo: null, // Verrà impostato a 'single' o 'ground'
    data: null,
    sessione: null,
    tribuna: null,
    quantita: 1
};

// Sposta il focus sulla sezione appena rivelata (sul suo titolo h2), così la
// navigazione da tastiera prosegue LOGICAMENTE nel nuovo contenuto invece di
// restare sul giorno selezionato e passare al giorno successivo.
// preventScroll: lo scroll fluido è già gestito da scrollIntoView.
function moveFocusToSection(section) {
    if (!section) return;
    const target = section.querySelector('.step-header h2') || section;
    target.setAttribute('tabindex', '-1'); // focusabile da script, ma fuori dall'ordine di Tab
    target.focus({ preventScroll: true });
}

function selectDate(dayName, dayNumber, event) {
    // 1. Evidenzia la data selezionata
    const allDates = document.querySelectorAll('.date-tile');
    allDates.forEach(date => {
        date.classList.remove('highlight');
        date.setAttribute('aria-pressed', 'false'); // comunica lo stato ai lettori di schermo
    });
    event.currentTarget.classList.add('highlight');
    event.currentTarget.setAttribute('aria-pressed', 'true'); // giorno attualmente selezionato

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
        moveFocusToSection(sessionSection); // il Tab successivo va alle sessioni, non al giorno dopo

    } else if (groundSection) {
        // --- LOGICA PAGINA GROUND PASSES ---
        groundSection.classList.remove('d-none');
        groundSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        moveFocusToSection(groundSection);
    }

    // invia dati al php per determinare il prezzo in base a quello che ho selezionato
    const prezzoDisplay = document.getElementById('prezzo-ground');

    if (prezzoDisplay) {
        let dati = new FormData();
        dati.append('data_scelta', '2027-05-' + dayNumber);

        fetch('ground_passes.php', { method: 'POST', body: dati })
            .then(risposta => risposta.json())
            .then(data => {
                const avviso = document.getElementById('avviso-disponibilita');
                if (data.prezzo && data.quantita_disponibile > 0) {
                    prezzoDisplay.textContent = "€ " + parseFloat(data.prezzo).toLocaleString('it-IT', { minimumFractionDigits: 2 });
                    prezzoDisplay.dataset.rawPrice = data.prezzo;
                    prezzoDisplay.dataset.disponibilita = data.quantita_disponibile;

                    if (avviso) {
                        if (data.quantita_disponibile < 10) {
                            avviso.textContent = 'Affrettati! Rimangono solo ' + data.quantita_disponibile + ' biglietti disponibili.';
                            avviso.classList.remove('d-none');
                        } else {
                            avviso.textContent = '';
                            avviso.classList.add('d-none');
                        }
                    }

                    const qtyInput = document.getElementById('ticket-qty');
                    if (qtyInput) {
                        qtyInput.value = 1;
                        statoAcquisto.quantita = 1;
                    }
                    if (typeof changeQty === 'function') changeQty(0);
                } else {
                    prezzoDisplay.textContent = "Esaurito";
                    prezzoDisplay.classList.add('status-sold-out');
                    prezzoDisplay.dataset.disponibilita = "0";
                    if (avviso) {
                        avviso.textContent = '';
                        avviso.classList.add('d-none');
                    }
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
        moveFocusToSection(seatSection); // dopo la sessione, il focus passa alle tribune
    }

    const dPremium = document.getElementById('prezzo-premium');
    const dAntenore = document.getElementById('prezzo-antenore');
    const dFondo = document.getElementById('prezzo-fondo');
    const dAnello = document.getElementById('prezzo-anello');

    if (dPremium && window.dataSelezionata && tipoSessione) {
        let dati = new FormData();
        dati.append('data_scelta', window.dataSelezionata);
        dati.append('sessione_scelta', tipoSessione);

        fetch('single_session.php', { method: 'POST', body: dati })
            .then(risposta => risposta.json()) 
            .then(data => {
                // Nella tua funzione formatta (dentro il fetch)
                const formatta = (info, elemento) => {
                    const seatCategory = elemento.closest('.seat-category');
                    if (info && info.quantita_disponibile > 0) {
                        elemento.textContent = "€ " + parseFloat(info.prezzo).toLocaleString('it-IT', {minimumFractionDigits: 2});
                        elemento.dataset.rawPrice = info.prezzo;
                        elemento.classList.remove('status-sold-out');
                        if (seatCategory) {
                            seatCategory.classList.remove('disabled-seat');
                            seatCategory.dataset.disponibilita = info.quantita_disponibile;
                        }
                    } else {
                        elemento.textContent = "Esaurito";
                        elemento.dataset.rawPrice = "0";
                        elemento.classList.add('status-sold-out');
                        if (seatCategory) {
                            seatCategory.classList.add('disabled-seat');
                            seatCategory.dataset.disponibilita = "0";
                        }
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

function pulisciPrezzo(elemento) {
    if (!elemento || !elemento.dataset.rawPrice) {
        console.warn("Elemento o prezzo non trovato");
        return 0;
    }
    return parseFloat(elemento.dataset.rawPrice);
}

// STEP 3: Selezione della Tribuna (Usato SOLO in Single Session)
function selectTribune(element) {
    if (element.classList.contains('disabled-seat')) return;

    const allSeats = document.querySelectorAll('.seat-category');
    allSeats.forEach(seat => seat.classList.remove('selected'));

    element.classList.add('selected');
    statoAcquisto.tribuna = element.querySelector('h3').textContent;

    const priceSpan = element.querySelector('.seat-price');
    statoAcquisto.prezzoSingolo = pulisciPrezzo(priceSpan);

    const avviso = document.getElementById('avviso-disponibilita');
    const dispDb = parseInt(element.dataset.disponibilita, 10) || 0;

    if (avviso) {
        if (dispDb > 0 && dispDb < 10) {
            avviso.textContent = 'Affrettati! Rimangono solo ' + dispDb + ' biglietti disponibili.';
            avviso.classList.remove('d-none');
        } else {
            avviso.textContent = '';
            avviso.classList.add('d-none');
        }
    }

    const qtyInput = document.getElementById('ticket-qty');
    if (qtyInput) {
        qtyInput.value = 1;
        if (typeof statoAcquisto !== 'undefined') statoAcquisto.quantita = 1;
    }

    if (typeof changeQty === 'function') {
        changeQty(0);
    }
}

// Gestione Quantità (+ e -) (Usato in ENTRAMBE le pagine)
function changeQty(amount) {
    const qtyInput = document.getElementById('ticket-qty');
    if (!qtyInput) return;

    const selectedSeat = document.querySelector('.seat-category.selected');
    const groundPrice = document.getElementById('prezzo-ground');

    let dispDb = 0;
    if (selectedSeat) {
        dispDb = parseInt(selectedSeat.dataset.disponibilita, 10) || 0;
    } else if (groundPrice && groundPrice.dataset.disponibilita !== undefined) {
        dispDb = parseInt(groundPrice.dataset.disponibilita, 10) || 0;
    } else {
        return;
    }

    if (dispDb <= 0) {
        const btnPlusSoldOut = document.querySelector('.qty-btn.plus');
        if (btnPlusSoldOut) { btnPlusSoldOut.disabled = true; btnPlusSoldOut.setAttribute('aria-disabled', 'true'); }
        return;
    }

    const maxConsentito = Math.min(10, dispDb);

    let newValue = parseInt(qtyInput.value, 10);
    if (isNaN(newValue)) newValue = 1;
    newValue += amount;

    if (newValue < 1) newValue = 1;
    if (newValue > maxConsentito) newValue = maxConsentito;

    qtyInput.value = newValue;
    qtyInput.max = maxConsentito;
    qtyInput.setAttribute('aria-valuemax', maxConsentito);

    if (typeof statoAcquisto !== 'undefined') statoAcquisto.quantita = newValue;

    const btnPlus = document.querySelector('.qty-btn.plus');
    if (btnPlus) {
        const atMax = newValue >= maxConsentito;
        btnPlus.disabled = atMax;
        btnPlus.setAttribute('aria-disabled', atMax ? 'true' : 'false');
    }

    const btnMinus = document.querySelector('.qty-btn.minus');
    if (btnMinus) {
        const atMin = newValue <= 1;
        btnMinus.disabled = atMin;
        btnMinus.setAttribute('aria-disabled', atMin ? 'true' : 'false');
    }
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

        // Accessibilità tastiera: Invio o Spazio attivano la selezione esattamente
        // come il click. Un <div role="button"> non lo fa nativamente (solo i
        // <button> reali). preventDefault evita che lo Spazio scrolli la pagina.
        calendarContainer.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ' || event.key === 'Spacebar') {
                const tile = event.target.closest('.date-tile');
                if (tile) {
                    event.preventDefault();
                    selectDate(tile.dataset.day, tile.dataset.number, { currentTarget: tile });
                }
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
        // Gestione accessibilità tastiera: Invio e Spazio attivano la selezione
        category.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ' || e.key === 'Spacebar') {
                e.preventDefault();
                selectTribune(e.currentTarget);
            }
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
            function calcolaIdIncontro(dataScelta, sessioneScelta, tipo) {
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

            // LOGICA DI ACQUISIZIONE DATI
            if (isSingleSession) {
                if (!statoAcquisto.data || !statoAcquisto.sessione || !statoAcquisto.tribuna) {
                    alert("Per favore, completa la selezione: scegli Data, Sessione e Posto.");
                    return;
                }
                statoAcquisto.tipo = "Single Session";
                const postoScelto = document.querySelector('.seat-category.selected .seat-price');
                statoAcquisto.prezzoSingolo = pulisciPrezzo(postoScelto);
                statoAcquisto.idIncontro = calcolaIdIncontro(statoAcquisto.data, statoAcquisto.sessione, "Single Session");

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
                statoAcquisto.idIncontro = null;

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
                statoAcquisto.idIncontro = calcolaIdIncontro(statoAcquisto.data, null, "Ground Pass");
            }

            // INVIO DATI AL SERVER
            const pacchettoBiglietto = {
                tipologia: statoAcquisto.tipo,
                titolo: statoAcquisto.tribuna,
                data: statoAcquisto.data,
                sessione: statoAcquisto.sessione,
                prezzo: statoAcquisto.prezzoSingolo,
                quantita: statoAcquisto.quantita,
                idIncontro: statoAcquisto.idIncontro
            };

            fetch('../php-manager/aggiungi_carrello.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(pacchettoBiglietto)
            })
                .then(risposta => risposta.json())
                .then(dati => {
                    if (dati.status === 'success') {
                        // Feedback di conferma
                        acquistaBtn.classList.add('added-to-cart');
                        acquistaBtn.textContent = 'AGGIUNTO ✓';

                        // Mostra il link al carrello se non esiste
                        if (!document.getElementById('link-vai-carrello')) {
                            const linkCarrello = document.createElement('a');
                            linkCarrello.id = 'link-vai-carrello';
                            linkCarrello.href = '../php-pages/carrello.php';
                            linkCarrello.innerHTML = 'Vai al carrello ➔';
                            acquistaBtn.parentNode.insertBefore(linkCarrello, acquistaBtn.nextSibling);
                        }

                        setTimeout(() => {
                            acquistaBtn.classList.remove('added-to-cart');
                            acquistaBtn.textContent = 'AGGIUNGI AL CARRELLO';
                        }, 3000);
                    } else {
                        const avviso = document.getElementById('avviso-disponibilita');
                        const msg = dati.message || 'Impossibile aggiungere il biglietto. Verifica la disponibilità.';
                        if (avviso) {
                            avviso.textContent = msg;
                            avviso.classList.remove('d-none');
                        }
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
        // Saltiamo i form che hanno già una logica AJAX o una validazione propria
        const excludedForms = ['form-registrazione', 'form-faq-admin', 'form-nuova-news', 'form-modifica-news'];
        if (excludedForms.includes(form.id) || form.classList.contains('form-delete-faq')) return;

        form.addEventListener('submit', function () {
            // Cerchiamo il bottone di submit o quelli con le classi che usi tu
            const submitBtn = form.querySelector('button[type="submit"], .btn-checkout');

            if (submitBtn) {
                // Disabilita il bottone per evitare il doppio invio
                submitBtn.disabled = true;
                submitBtn.classList.add('btn-loading');
                submitBtn.setAttribute('aria-busy', 'true');

                // Cambia il testo solo se il bottone ne ha uno
                if (submitBtn.textContent.trim() !== '') {
                    submitBtn.textContent = "Attendere...";
                }
            }
        });
    });
});

/* Fa sparire da sole le .form-message stampate dal PHP al caricamento pagina
   (non quelle AJAX, gestite nei rispettivi file). */
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.form-message').forEach(msg => {
        setTimeout(() => {
            msg.classList.add('fade-out');
            setTimeout(() => msg.classList.add('hidden'), 500);
        }, 4000);
    });
});

/* Apre tutte le tendine FAQ appena si preme "Stampa" (Ctrl+P) */
window.addEventListener('beforeprint', function() {
    const dettagliFaq = document.querySelectorAll('details.faq-item');
    dettagliFaq.forEach(function(dettaglio) {
        /* Aggiunge l'attributo che sblocca il contenuto per il browser */
        dettaglio.setAttribute('open', 'open');
    });
});

/* Le richiude quando la finestra di stampa viene chiusa o annullata */
window.addEventListener('afterprint', function() {
    const dettagliFaq = document.querySelectorAll('details.faq-item');
    dettagliFaq.forEach(function(dettaglio) {
        dettaglio.removeAttribute('open');
    });
});

/* ==========================================================================
   GESTIONE RANGE PREZZI IN STAMPA (Universale per Single Session e Ground)
   ========================================================================== */

const rangePrezziTorneo = {
    // Prezzi per Single Session
    'prezzo-premium': 'da € 120,00 a € 250,00',
    'prezzo-antenore': 'da € 80,00 a € 160,00',
    'prezzo-fondo':    'da € 55,00 a € 110,00',
    'prezzo-anello':   'da € 30,00 a € 65,00',
    
    // Prezzo per Ground Pass
    'prezzo-ground':   'da € 20,00 a € 50,00'
};

window.addEventListener('beforeprint', function() {
    // Cerchiamo tutti gli elementi che hanno un ID che inizia con "prezzo-"
    const elementiPrezzo = document.querySelectorAll('[id^="prezzo-"]');
    
    elementiPrezzo.forEach(function(el) {
        if (el.innerText.trim() === "" || el.innerText.includes('---') || el.innerText.includes('[')) {
            el.setAttribute('data-temp-value', el.innerText);
            
            const range = rangePrezziTorneo[el.id];
            
            if (range) {
                el.innerText = range;
                el.setAttribute('aria-label', `Prezzi per questo settore: ${range}`);
            }
        }
    });
});

window.addEventListener('afterprint', function() {
    const elementiPrezzo = document.querySelectorAll('[id^="prezzo-"]');
    elementiPrezzo.forEach(function(el) {
        if (el.hasAttribute('data-temp-value')) {
            el.innerText = el.getAttribute('data-temp-value');
            el.removeAttribute('data-temp-value');
            el.removeAttribute('aria-label');
        }
    });
});