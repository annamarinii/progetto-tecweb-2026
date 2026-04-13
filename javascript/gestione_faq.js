document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti alle viste (sezioni)
    const faqDash = document.getElementById('faq-dashboard');
    const newFaqView = document.getElementById('view-nuova-faq');
    const listFaqView = document.getElementById('view-elenco-faq');

    // Riferimenti agli elementi del FORM
    const faqForm = document.getElementById('form-faq-admin');
    const faqIdInput = document.getElementById('idFaq'); // UNIFORMATO A NOME DB
    const domandaInput = document.getElementById('domanda_faq');
    const rispostaInput = document.getElementById('risposta_faq');

    // Funzione per nascondere tutte le sottoviste della sezione FAQ
    function hideAllFaqViews() {
        [faqDash, newFaqView, listFaqView].forEach(view => {
            if (view) view.classList.add('hidden');
        });
    }

    // --- NAVIGAZIONE DASHBOARD ---

    // Bottone "Aggiungi Nuova FAQ"
    document.getElementById('btn-new-faq')?.addEventListener('click', () => {
        hideAllFaqViews();
        faqForm?.reset(); // Pulisce i campi
        if (faqIdInput) faqIdInput.value = ""; // Fondamentale: resetta l'ID per nuova inserzione
        newFaqView?.classList.remove('hidden');
    });

    // Bottone "Elenco FAQ"
    document.getElementById('btn-manage-faq')?.addEventListener('click', () => {
        hideAllFaqViews();
        listFaqView?.classList.remove('hidden');
    });

    // Bottoni "Indietro"
    document.querySelectorAll('.btn-back-faq').forEach(btn => {
        btn.addEventListener('click', () => {
            hideAllFaqViews();
            faqDash?.classList.remove('hidden');
        });
    });

    // --- GESTIONE MODIFICA (Delegata al click sulle card) ---
    document.addEventListener('click', (e) => {
        if (e.target.classList.contains('btn-edit-faq-trigger')) {
            const btn = e.target;
            
            // Popoliamo i campi con i dati salvati nei dataset HTML
            if (domandaInput) domandaInput.value = btn.dataset.q;
            if (rispostaInput) rispostaInput.value = btn.dataset.a;
            if (faqIdInput) faqIdInput.value = btn.dataset.id; // Scrive l'idFaq del DB

            hideAllFaqViews();
            newFaqView?.classList.remove('hidden');
        }
    });

    // --- FEEDBACK UTENTE (Messaggi AJAX) ---
    function mostraMessaggioFaq(testo, tipo) {
        // Rimuove messaggi precedenti
        document.querySelectorAll('.ajax-dynamic-msg-faq').forEach(m => m.remove());
        
        const contenitore = document.getElementById('gestione-faq');
        const msg = document.createElement('div');
        
        // Usa le classi CSS esterne (esito-msg, esito-success, esito-error)
        msg.className = `ajax-dynamic-msg-faq esito-msg ${tipo === 'success' ? 'esito-success' : 'esito-error'}`;
        msg.textContent = testo; 

        contenitore?.prepend(msg);

        // Auto-rimozione dopo 3 secondi
        setTimeout(() => {
            msg.style.opacity = "0";
            msg.style.transition = "opacity 0.5s";
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    }

    // --- INVIO FORM AJAX ---
    faqForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Rimossa scritta Attendere... come richiesto

        const formData = new FormData(this);
        const currentId = faqIdInput ? faqIdInput.value : "";

        fetch(this.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => {
            if (!res.ok) throw new Error('Errore di rete');
            return res.json();
        })
        .then(data => {
            if (data.status === 'success') {
                mostraMessaggioFaq('Operazione completata con successo!', 'success');
                
                // Aggiorna l'elenco FAQ senza ricaricare la pagina
                if (data.html_faq) {
                    const lista = document.querySelector('.faq-list-admin');
                    if (lista) lista.innerHTML = data.html_faq;
                }

                // Scorre la vista verso l'alto per far notare il messaggio
                window.scrollTo({ top: 0, behavior: 'smooth' });

                // Logica post-salvataggio
                if (!currentId || currentId === "") {
                    // Se era una nuova FAQ, svuota i campi per poterne scrivere subito un'altra
                    this.reset();
                    if (faqIdInput) faqIdInput.value = "";
                }
            } else {
                mostraMessaggioFaq('Errore durante il salvataggio dei dati.', 'error');
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            mostraMessaggioFaq('Errore di connessione al server.', 'error');
        });
    });

    // --- ELIMINAZIONE FAQ DA ELENCO (Evita ricaricamento pagina) ---
    document.querySelector('.faq-list-admin')?.addEventListener('submit', function(e) {
        const targetForm = e.target;
        if (targetForm.classList.contains('form-delete-faq')) {
            e.preventDefault(); // Evita il ricaricamento nativo della pagina
            if (!confirm('Eliminare definitivamente questa FAQ?')) return;
            
            fetch(targetForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(targetForm)
            })
            .then(res => {
                if (!res.ok) throw new Error('Errore di rete');
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    mostraMessaggioFaq('FAQ Eliminata', 'success');
                    targetForm.closest('.faq-admin-card').remove();
                } else {
                    mostraMessaggioFaq('Errore durante l\'eliminazione.', 'error');
                }
            })
            .catch(err => {
                console.error('Fetch error:', err);
                mostraMessaggioFaq('Errore di connessione al server.', 'error');
            });
        }
    });

});