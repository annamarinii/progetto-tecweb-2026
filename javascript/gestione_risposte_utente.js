document.addEventListener('DOMContentLoaded', () => {
    const mailRows = document.querySelectorAll('.mail-row');

    mailRows.forEach(row => {
        const header = row.querySelector('.mail-row-header');
        const content = row.querySelector('.mail-content');
        const closeBtn = row.querySelector('.btn-close-mail');

        if (header && !header.dataset.listener) {
            header.addEventListener('click', () => {
                // TOGGLE della classe 'open' invece di modificare lo stile inline
                const isOpening = row.classList.toggle('open');

                // Se l'utente apre una mail non letta, la segniamo come letta
                if (isOpening && row.classList.contains('unread')) {
                    const idDomanda = row.dataset.id;
                    
                    if (idDomanda) {
                        const fd = new FormData();
                        fd.append('segna_letta_utente', 'si');
                        fd.append('id_domanda', idDomanda);

                        fetch('../php-pages/areautente.php', {
                            method: 'POST',
                            headers: { 'X-Requested-With': 'XMLHttpRequest' },
                            body: fd
                        })
                        .then(res => {
                            if(res.ok) {
                                // Feedback visivo immediato tramite classi
                                row.classList.remove('unread');
                                row.classList.add('read');
                            }
                        })
                        .catch(err => console.error("Errore AJAX lettura utente:", err));
                    }
                }
            });
            header.dataset.listener = "true";
        }

        // Impedisce la chiusura se si clicca dentro il contenuto della risposta
        if (content) {
            content.addEventListener('click', (e) => e.stopPropagation());
        }

        // Gestione pulsante chiudi
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                row.classList.remove('open');
            });
        }
    });
});