document.addEventListener('DOMContentLoaded', () => {
    // Riferimenti alle sezioni (Viste)
    const views = {
        dash: document.getElementById('news-dashboard'),
        new: document.getElementById('view-nuova-news'),
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

    // --- GESTIONE EDIT (Delegation) ---
    function attaccaEventiMiniature() {
        document.querySelectorAll('.news-mini-card').forEach(card => {
            card.onclick = function() {
                // Recupero dati dai dataset e dal DOM
                const newsId = this.getAttribute('data-news-id');
                const title = this.querySelector('.mini-card-titolo')?.textContent ?? '';
                const testo = this.querySelector('.news-full-text')?.innerHTML;
                const imgPath = this.querySelector('.mini-card-img')?.getAttribute('src');
                const imgName = imgPath ? imgPath.split('/').pop() : "Nessuna immagine";

                // Popolamento form di modifica
                document.getElementById('edit-id-news').value = newsId;
                document.getElementById('edit-titolo').value = title;
                document.getElementById('edit-testo').value = testo;
                document.getElementById('span-img-corrente').textContent = imgName;

                switchView('edit');
            };
        });
    }
    attaccaEventiMiniature();

    // --- MESSAGGI DI FEEDBACK (Puro SoC) ---
    function mostraMessaggioNews(testo, tipo) {
        document.querySelectorAll('.ajax-dynamic-msg').forEach(m => m.remove());
        const contenitore = document.getElementById('gestione-news');
        const msg = document.createElement('div');
        
        // Usiamo le classi definite nel CSS
        msg.className = `ajax-dynamic-msg msg-${tipo}`;
        msg.setAttribute('role', 'status');
        msg.setAttribute('aria-live', 'polite');
        msg.textContent = testo;

        contenitore?.prepend(msg);

        setTimeout(() => {
            msg.classList.add('fade-out');
            setTimeout(() => msg.remove(), 500);
        }, 2500);
    }

    // --- LOGICA AJAX ---
    function gestisciAjaxNews(formId) {
        const form = document.getElementById(formId);
        form?.addEventListener('submit', function(e) {
            e.preventDefault();
            
            fetch(this.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this)
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (data.upload_msg) {
                        mostraMessaggioNews("Salvato, ma errore immagine: " + data.upload_msg, 'error');
                    } else {
                        mostraMessaggioNews('Notizia salvata con successo!', 'success');
                    }
                    
                    if (data.html_miniature) {
                        const grid = document.querySelector('.news-miniatures-grid');
                        if (grid) grid.innerHTML = data.html_miniature;
                        attaccaEventiMiniature();
                    }

                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    if (formId === 'form-nuova-news') this.reset();
                } else {
                    mostraMessaggioNews('Errore durante il salvataggio.', 'error');
                }
            })
            .catch(() => mostraMessaggioNews("Errore di connessione al server.", "error"));
        });
    }

    gestisciAjaxNews('form-nuova-news');
    gestisciAjaxNews('form-modifica-news');

    // --- ELIMINAZIONE ---
    document.querySelector('.btn-delete-mock')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!confirm("Sei sicuro di voler eliminare questa news?")) return;
        
        const form = this.closest('form');
        const formData = new FormData(form);
        formData.append('elimina', 'si');

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                mostraMessaggioNews('News eliminata definitivamente!', 'success');
                if (data.html_miniature) {
                    document.querySelector('.news-miniatures-grid').innerHTML = data.html_miniature;
                    attaccaEventiMiniature();
                }
                setTimeout(() => switchView('list'), 1000);
            }
        });
    });
});