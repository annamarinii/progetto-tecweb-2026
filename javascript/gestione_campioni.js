document.addEventListener('DOMContentLoaded', () => {
    const views = {
        dash: document.getElementById('campioni-dashboard'),
        new:  document.getElementById('view-nuovo-campione'),
        list: document.getElementById('view-elenco-campioni'),
        edit: document.getElementById('view-modifica-campione')
    };

    function switchView(targetKey) {
        Object.values(views).forEach(view => view?.classList.add('hidden'));
        views[targetKey]?.classList.remove('hidden');
    }

    // --- NAVIGAZIONE ---
    document.getElementById('btn-new-campione')?.addEventListener('click', () => switchView('new'));
    document.getElementById('btn-manage-campioni')?.addEventListener('click', () => switchView('list'));

    document.querySelectorAll('.btn-back-campioni').forEach(btn => {
        btn.addEventListener('click', () => switchView('dash'));
    });

    document.querySelector('.btn-back-to-list-campioni')?.addEventListener('click', () => switchView('list'));

    // --- APERTURA FORM MODIFICA (pura manipolazione DOM, nessuna richiesta HTTP) ---
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.btn-edit-campione-trigger');
        if (!btn) return;

        const row = btn.closest('.news-admin-row');
        if (!row) return;

        document.getElementById('edit-id-campione').value                = btn.dataset.id ?? '';
        document.getElementById('edit-nome-campione').value              = row.querySelector('.news-admin-title')?.textContent ?? '';
        document.getElementById('edit-categoria-campione').value         = btn.dataset.categoria ?? '';
        document.getElementById('edit-anno-campione').value              = btn.dataset.anno ?? '';
        document.getElementById('edit-alt-immagine-campione').value      = btn.dataset.alt ?? '';
        document.getElementById('edit-ordine-campione').value            = btn.dataset.ordine ?? '';
        document.getElementById('span-img-campione-corrente').textContent = btn.dataset.img ?? '';

        switchView('edit');
        document.getElementById('edit-nome-campione')?.focus();
    });

    // --- ELIMINA CAMPIONE dalla lista: conferma, poi il form POSTa normalmente ---
    document.getElementById('gestione-campioni')?.addEventListener('submit', function(e) {
        const targetForm = e.target;
        if (!targetForm.classList.contains('form-delete-campione')) return;
        if (!confirm('Eliminare definitivamente questo campione?')) e.preventDefault();
    });

    // --- ELIMINA CAMPIONE dal form di modifica: aggiunge campo e POSTa normalmente ---
    document.querySelector('.btn-delete-mock-campione')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!confirm('Eliminare definitivamente questo campione?')) return;
        const form = this.closest('form');
        const input = document.createElement('input');
        input.type  = 'hidden';
        input.name  = 'elimina';
        input.value = 'si';
        form.appendChild(input);
        form.submit();
    });
});
