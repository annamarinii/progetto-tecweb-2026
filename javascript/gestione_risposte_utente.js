document.addEventListener('DOMContentLoaded', () => {
    const mailRows = document.querySelectorAll('.mail-row');

    mailRows.forEach(row => {
        const header = row.querySelector('.mail-row-header');
        const content = row.querySelector('.mail-content');
        const closeBtn = row.querySelector('.btn-close-mail');

        if (header && !header.dataset.listener) {
            header.addEventListener('click', () => {
                const isExpanded = content.style.display === 'block';

                if (isExpanded) {
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';

                    // Se è unread, lo segniamo come letto nel DB
                    if (row.classList.contains('unread')) {
                        const idDomanda = row.dataset.id; // Assicurati che nel HTML ci sia data-id=".."
                        
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
                                    row.classList.remove('unread');
                                    row.classList.add('read');
                                }
                            })
                            .catch(err => console.error("Errore aggiornamento lettura utente:", err));
                        }
                    }
                }
            });
            header.dataset.listener = "true";
        }

        if (content) {
            content.addEventListener('click', (e) => e.stopPropagation());
        }

        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                content.style.display = 'none';
            });
        }
    });
});