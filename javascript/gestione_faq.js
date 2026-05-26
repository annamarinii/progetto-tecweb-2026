document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti alle sezioni
    const views = {
        dash: document.getElementById('faq-dashboard'),
        form: document.getElementById('view-nuova-faq'),
        list: document.getElementById('view-elenco-faq')
    };

    // Riferimenti form e titolo
    const faqForm      = document.getElementById('form-faq-admin');
    const faqIdInput   = document.getElementById('idFaq');
    const domandaInput = document.getElementById('domanda_faq');
    const rispostaInput = document.getElementById('risposta_faq');
    const formTitle    = document.querySelector('#view-nuova-faq .view-title');
    const backFaqBtn   = document.querySelector('#view-nuova-faq .btn-back-faq');

    // Traccia da quale vista si è entrati nel form (per il bottone "torna")
    let previousView = 'dash';

    // Nasconde tutte le viste e mostra solo quella richiesta
    function switchView(target) {
        Object.values(views).forEach(v => v?.classList.add('hidden'));
        views[target]?.classList.remove('hidden');
    }

    // Aggiorna titolo e testo del bottone "torna" in base al contesto
    function configuraFormPerModalita(modalita) {
        if (modalita === 'modifica') {
            if (formTitle) formTitle.textContent = 'Modifica FAQ';
            if (backFaqBtn) backFaqBtn.innerHTML =
                '<span aria-hidden="true" class="arrow-back">&larr;</span> Torna all\'Elenco FAQ';
            previousView = 'list';
        } else {
            if (formTitle) formTitle.textContent = 'Pubblica una Nuova FAQ';
            if (backFaqBtn) backFaqBtn.innerHTML =
                '<span aria-hidden="true" class="arrow-back">&larr;</span> Torna alla Dashboard';
            previousView = 'dash';
        }
    }

    // --- NAVIGAZIONE ---

    document.getElementById('btn-new-faq')?.addEventListener('click', () => {
        faqForm?.reset();
        if (faqIdInput) faqIdInput.value = '';
        configuraFormPerModalita('nuovo');
        switchView('form');
        domandaInput?.focus();
    });

    document.getElementById('btn-manage-faq')?.addEventListener('click', () => switchView('list'));

    document.querySelectorAll('.btn-back-faq').forEach(btn => {
        btn.addEventListener('click', () => switchView(previousView));
    });

    // --- MODIFICA (event delegation: funziona anche dopo aggiornamento AJAX della lista) ---
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-edit-faq-trigger');
        if (!btn) return;

        if (domandaInput)  domandaInput.value  = btn.dataset.q;
        if (rispostaInput) rispostaInput.value = btn.dataset.a;
        if (faqIdInput)    faqIdInput.value    = btn.dataset.id;

        configuraFormPerModalita('modifica');
        switchView('form');
        domandaInput?.focus();
    });

    // --- FEEDBACK AJAX ---
    function mostraMessaggioFaq(testo, tipo) {
        document.querySelectorAll('.ajax-dynamic-msg-faq').forEach(m => m.remove());

        const contenitore = document.getElementById('gestione-faq');
        const msg = document.createElement('div');
        msg.className = `ajax-dynamic-msg-faq msg-${tipo}`;
        msg.setAttribute('role', 'status');
        msg.setAttribute('aria-live', 'polite');
        msg.textContent = testo;

        contenitore?.prepend(msg);

        setTimeout(() => {
            msg.classList.add('fade-out');
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    }

    // --- INVIO AJAX (crea o aggiorna) ---
    faqForm?.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData   = new FormData(this);
        const currentId  = faqIdInput?.value;

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

                if (!currentId || currentId === '') {
                    this.reset();
                }
            } else {
                mostraMessaggioFaq('Errore nel salvataggio.', 'error');
            }
        })
        .catch(() => mostraMessaggioFaq('Errore di connessione.', 'error'));
    });

    // --- ELIMINAZIONE AJAX (event delegation sulla lista) ---
    document.querySelector('.faq-list-admin')?.addEventListener('submit', function (e) {
        const targetForm = e.target;
        if (!targetForm.classList.contains('form-delete-faq')) return;

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
                mostraMessaggioFaq('FAQ eliminata.', 'success');
                const card = targetForm.closest('.faq-admin-card');
                card?.classList.add('fade-out');
                setTimeout(() => card?.remove(), 400);
            } else {
                mostraMessaggioFaq('Errore eliminazione.', 'error');
            }
        })
        .catch(() => mostraMessaggioFaq('Errore di connessione.', 'error'));
    });
});
