document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti alle sezioni
    const views = {
        dash: document.getElementById('faq-dashboard'),
        form: document.getElementById('view-nuova-faq'),
        list: document.getElementById('view-elenco-faq')
    };

    // Riferimenti FORM
    const faqForm = document.getElementById('form-faq-admin');
    const faqIdInput = document.getElementById('idFaq');
    const domandaInput = document.getElementById('domanda_faq');
    const rispostaInput = document.getElementById('risposta_faq');

    // Funzione di navigazione pulita
    function switchView(target) {
        Object.values(views).forEach(v => v?.classList.add('hidden'));
        views[target]?.classList.remove('hidden');
    }

    // --- GESTIONE NAVIGAZIONE ---

    document.getElementById('btn-new-faq')?.addEventListener('click', () => {
        faqForm?.reset();
        if (faqIdInput) faqIdInput.value = ""; // Modalità inserimento
        switchView('form');
    });

    document.getElementById('btn-manage-faq')?.addEventListener('click', () => switchView('list'));

    document.querySelectorAll('.btn-back-faq').forEach(btn => {
        btn.addEventListener('click', () => switchView('dash'));
    });

    // --- GESTIONE MODIFICA ---
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-edit-faq-trigger');
        if (btn) {
            // Popolamento dati dai dataset (Pura SoC)
            if (domandaInput) domandaInput.value = btn.dataset.q;
            if (rispostaInput) rispostaInput.value = btn.dataset.a;
            if (faqIdInput) faqIdInput.value = btn.dataset.id;

            switchView('form');
        }
    });

    // --- FEEDBACK AJAX ---
    function mostraMessaggioFaq(testo, tipo) {
        document.querySelectorAll('.ajax-dynamic-msg-faq').forEach(m => m.remove());
        
        const contenitore = document.getElementById('gestione-faq');
        const msg = document.createElement('div');
        
        // Usiamo le classi semantiche del torneo
        msg.className = `ajax-dynamic-msg-faq msg-${tipo}`;
        msg.textContent = testo; 

        contenitore?.prepend(msg);

        // Auto-rimozione tramite transizione CSS
        setTimeout(() => {
            msg.classList.add('fade-out');
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    }

    // --- INVIO AJAX ---
    faqForm?.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        const currentId = faqIdInput?.value;

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
                mostraMessaggioFaq('Operazione completata!', 'success');
                
                if (data.html_faq) {
                    const lista = document.querySelector('.faq-list-admin');
                    if (lista) lista.innerHTML = data.html_faq;
                }

                window.scrollTo({ top: 0, behavior: 'smooth' });

                if (!currentId || currentId === "") {
                    this.reset();
                }
            } else {
                mostraMessaggioFaq('Errore nel salvataggio.', 'error');
            }
        })
        .catch(err => {
            console.error('Error:', err);
            mostraMessaggioFaq('Errore di connessione.', 'error');
        });
    });

    // --- ELIMINAZIONE AJAX ---
    document.querySelector('.faq-list-admin')?.addEventListener('submit', function(e) {
        const targetForm = e.target;
        if (targetForm.classList.contains('form-delete-faq')) {
            e.preventDefault();
            if (!confirm('Eliminare definitivamente questa FAQ?')) return;
            
            fetch(targetForm.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(targetForm)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    mostraMessaggioFaq('FAQ Eliminata', 'success');
                    targetForm.closest('.faq-admin-card').classList.add('fade-out');
                    setTimeout(() => targetForm.closest('.faq-admin-card').remove(), 400);
                } else {
                    mostraMessaggioFaq('Errore eliminazione.', 'error');
                }
            })
            .catch(() => mostraMessaggioFaq('Errore di connessione.', 'error'));
        }
    });
});