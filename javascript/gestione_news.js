document.addEventListener('DOMContentLoaded', () => {
    const views = {
        dash: document.getElementById('news-dashboard'),
        new:  document.getElementById('view-nuova-news'),
        list: document.getElementById('view-elenco-news'),
        edit: document.getElementById('view-modifica-news')
    };

    function switchView(targetKey) {
        Object.values(views).forEach(view => view?.classList.add('hidden'));
        views[targetKey]?.classList.remove('hidden');
    }

    // --- NAVIGAZIONE ---
    document.getElementById('btn-new-news')?.addEventListener('click', () => switchView('new'));
    document.getElementById('btn-manage-news')?.addEventListener('click', () => switchView('list'));

    document.querySelectorAll('.btn-back-news').forEach(btn => {
        btn.addEventListener('click', () => switchView('dash'));
    });

    document.querySelector('.btn-back-to-list')?.addEventListener('click', () => switchView('list'));

    // --- APERTURA FORM MODIFICA (pura manipolazione DOM, nessuna richiesta HTTP) ---
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-edit-news-trigger');
        if (!btn) return;

        const row = btn.closest('.news-admin-row');
        if (!row) return;

        document.getElementById('edit-id-news').value            = btn.dataset.id ?? '';
        document.getElementById('edit-titolo').value             = row.querySelector('.news-admin-title')?.textContent ?? '';
        document.getElementById('edit-testo').value              = row.querySelector('.news-admin-testo-hidden')?.textContent ?? '';
        document.getElementById('span-img-corrente').textContent = btn.dataset.img ?? '';

        const evidenzaCheck = document.getElementById('edit-evidenza');
        if (evidenzaCheck) evidenzaCheck.checked = btn.dataset.inevidenza === '1';

        switchView('edit');
        document.getElementById('edit-titolo')?.focus();
    });

    // --- ELIMINA NEWS dalla lista: conferma, poi il form POSTa normalmente ---
    document.getElementById('gestione-news')?.addEventListener('submit', function(e) {
        const targetForm = e.target;
        if (!targetForm.classList.contains('form-delete-news')) return;
        if (!confirm('Eliminare definitivamente questa news?')) e.preventDefault();
    });

    // --- ELIMINA NEWS dal form di modifica: aggiunge campo e POSTa normalmente ---
    document.querySelector('.btn-delete-mock')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!confirm('Eliminare definitivamente questa news?')) return;
        const form = this.closest('form');
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'elimina';
        input.value = 'si';
        form.appendChild(input);
        form.submit();
    });
});
