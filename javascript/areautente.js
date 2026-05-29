document.addEventListener('DOMContentLoaded', () => {
    
    /**
     * --- GESTIONE TAB ---
     * Permette di navigare tra "Dati Personali", "I miei biglietti" e "Notifiche"
     */
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    function attivaSezionePerId(id) {
        const link = document.querySelector(`.tab-link[data-target="${id}"]`);
        if (!link) return;
        tabLinks.forEach(l => l.classList.remove('active'));
        tabContents.forEach(c => c.classList.remove('active'));
        link.classList.add('active');
        const sezione = document.getElementById(id);
        if (sezione) sezione.classList.add('active');
    }

    tabLinks.forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            attivaSezionePerId(link.getAttribute('data-target'));
        });
    });

    // Attiva la sezione corrispondente all'hash URL dopo redirect POST
    const hashIniziale = window.location.hash.slice(1);
    if (hashIniziale) attivaSezionePerId(hashIniziale);

    /**
     * --- GESTIONE MODIFICA PROFILO ---
     * Gestisce lo scambio tra il pulsante "Modifica" e il pulsante "Salva"
     * e abilita gli input del form.
     */
    const btnModifica = document.getElementById('btn-modifica');
    const btnSalva = document.getElementById('btn-salva');
    const profileForm = document.querySelector('.profile-form');
    
    if (btnModifica && btnSalva && profileForm) {
        const inputs = profileForm.querySelectorAll('input');

        btnModifica.addEventListener('click', (e) => {
            // Impediamo comportamenti di default (essendo type="button" non servirebbe, ma è buona prassi)
            e.preventDefault(); 

            // 1. Rendiamo gli input scrivibili rimuovendo 'readonly'
            inputs.forEach(input => {
                input.removeAttribute('readonly');
                input.classList.add('input-editing'); // Classe utile per dare feedback visuale via CSS
            });

            // 2. Gestione visibilità pulsanti
            // Nascondiamo il tasto "Modifica Profilo"
            btnModifica.classList.add('d-none'); 
            // Mostriamo il tasto "Salva Modifiche" (che invierà il form via POST)
            btnSalva.classList.remove('d-none');

            // 3. Portiamo il focus sul primo campo (Nome) per velocizzare la digitazione
            if(inputs[0]) inputs[0].focus();
        });
    }

    /**
     * --- GESTIONE NOTIFICHE (MAIL-STYLE) ---
     * Logica opzionale per chiudere l'anteprima del messaggio se necessario
     */
    const closeMailBtns = document.querySelectorAll('.btn-close-mail');
    closeMailBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            const mailRow = e.target.closest('.mail-row');
            if (mailRow) {
                // Se la tua logica prevede di chiudere il dettaglio al click
                mailRow.classList.remove('active'); 
            }
        });
    });
});