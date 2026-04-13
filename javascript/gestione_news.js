document.addEventListener('DOMContentLoaded', () => {
    const dashView = document.getElementById('news-dashboard');
    const newNewsView = document.getElementById('view-nuova-news');
    const listNewsView = document.getElementById('view-elenco-news');
    const editNewsView = document.getElementById('view-modifica-news');

    function hideAllNewsViews() {
        [dashView, newNewsView, listNewsView, editNewsView].forEach(view => {
            if (view) view.classList.add('hidden');
        });
    }

    // Forza lo stato iniziale: solo dashboard visibile
    hideAllNewsViews();
    dashView?.classList.remove('hidden');

    document.getElementById('btn-new-news')?.addEventListener('click', () => {
        hideAllNewsViews();
        newNewsView.classList.remove('hidden');
    });

    document.getElementById('btn-manage-news')?.addEventListener('click', () => {
        hideAllNewsViews();
        listNewsView.classList.remove('hidden');
    });

    document.querySelectorAll('.btn-back-news').forEach(btn => {
        btn.addEventListener('click', () => {
            hideAllNewsViews();
            dashView.classList.remove('hidden');
        });
    });

    // --- AGGIUNTA: Torna alla lista dalla modifica ---
    document.querySelector('.btn-back-to-list')?.addEventListener('click', () => {
        hideAllNewsViews();
        listNewsView.classList.remove('hidden');
    });

    function attaccaEventiMiniature() {
        document.querySelectorAll('.news-mini-card').forEach(card => {
            card.onclick = function() {
                const newsId = this.getAttribute('data-news-id');
                const title = this.querySelector('h4').textContent;
                const testo = this.querySelector('.news-full-text')?.innerHTML;
                const imgPath = this.querySelector('.mini-card-img')?.getAttribute('src');
                const imgName = imgPath ? imgPath.split('/').pop() : "";

                document.getElementById('edit-id-news').value = newsId;
                document.getElementById('edit-titolo').value = title;
                document.getElementById('edit-testo').value = testo;
                document.getElementById('span-img-corrente').textContent = imgName;

                hideAllNewsViews();
                editNewsView.classList.remove('hidden');
            };
        });
    }
    attaccaEventiMiniature();

    function mostraMessaggioNews(testo, tipo) {
        document.querySelectorAll('.ajax-dynamic-msg').forEach(m => m.remove());
        const contenitore = document.getElementById('gestione-news');
        const msg = document.createElement('div');
        msg.className = 'ajax-dynamic-msg';
        const colore = tipo === 'success' ? 'green' : 'red';
        msg.innerHTML = `<div style="color:${colore}; padding:10px; border:1px solid ${colore}; margin-bottom:20px; font-weight:bold; background:white;">${testo}</div>`;
        contenitore?.prepend(msg);
        setTimeout(() => {
            msg.style.opacity = "0";
            setTimeout(() => msg.remove(), 500);
        }, 2000);
    }

    function gestisciAjaxNews(formId) {
        const form = document.getElementById(formId);
        form?.addEventListener('submit', function(e) {
            e.preventDefault();
            fetch(this.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(this)
            })
            .then(res => {
                if (!res.ok) throw new Error("Errore di rete");
                return res.json();
            })
            .then(data => {
                if (data.status === 'success') {
                    if (data.upload_msg && data.upload_msg !== "") {
                        mostraMessaggioNews("News salvata, ma l'immagine non è stata caricata: " + data.upload_msg, 'error');
                    } else {
                        mostraMessaggioNews('Salvato con successo!', 'success');
                    }
                    if (data.html_miniature) {
                        const grid = document.querySelector('.news-miniatures-grid');
                        if (grid) grid.innerHTML = data.html_miniature;
                        attaccaEventiMiniature();
                    }
                    // Riporta la visuale verso l'alto per vedere il messaggio
                    window.scrollTo({ top: 0, behavior: 'smooth' });

                    if (formId === 'form-nuova-news') {
                        // Svuota i campi se stavamo inserendo una news nuova (per metterne un'altra)
                        this.reset();
                    }
                } else {
                    mostraMessaggioNews('Errore durante il salvataggio!', 'error');
                }
            })
            .catch(err => {
                console.error("Fetch error:", err);
                mostraMessaggioNews("Errore di connessione al server.", "error");
            });
        });
    }

    gestisciAjaxNews('form-nuova-news');
    gestisciAjaxNews('form-modifica-news');

    document.querySelector('.btn-delete-mock')?.addEventListener('click', function(e) {
        e.preventDefault();
        if (!confirm("Eliminare la news?")) return;
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
                mostraMessaggioNews('News eliminata!', 'success');
                if (data.html_miniature) {
                    document.querySelector('.news-miniatures-grid').innerHTML = data.html_miniature;
                    attaccaEventiMiniature();
                }
                setTimeout(() => {
                    hideAllNewsViews();
                    listNewsView?.classList.remove('hidden');
                }, 1000);
            }
        });
    });
});