document.addEventListener('DOMContentLoaded', () => {
    const views = {
        dash: document.getElementById('faq-dashboard'),
        form: document.getElementById('view-nuova-faq'),
        list: document.getElementById('view-elenco-faq')
    };

    const faqForm       = document.getElementById('form-faq-admin');
    const faqIdInput    = document.getElementById('idFaq');
    const domandaInput  = document.getElementById('domanda_faq');
    const rispostaInput = document.getElementById('risposta_faq');
    const formTitle     = document.querySelector('#view-nuova-faq .view-title');
    const backFaqBtn    = document.querySelector('#view-nuova-faq .btn-back-faq');

    let previousView = 'dash';

    function switchView(target) {
        Object.values(views).forEach(v => v?.classList.add('hidden'));
        views[target]?.classList.remove('hidden');
    }

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

    // --- APERTURA FORM MODIFICA (pura manipolazione DOM, nessuna richiesta HTTP) ---
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

    // --- ELIMINA FAQ: conferma, poi il form POSTa normalmente ---
    document.querySelector('.faq-list-admin')?.addEventListener('submit', function(e) {
        const targetForm = e.target;
        if (!targetForm.classList.contains('form-delete-faq')) return;
        if (!confirm('Eliminare definitivamente questa FAQ?')) e.preventDefault();
    });
});
