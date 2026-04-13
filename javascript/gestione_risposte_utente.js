document.addEventListener('DOMContentLoaded', () => {
    // Seleziona tutte le righe dei messaggi dell'area utente (come quelle di gmail)
    const mailRows = document.querySelectorAll('.mail-row');

    mailRows.forEach(row => {
        const header = row.querySelector('.mail-row-header');
        const content = row.querySelector('.mail-content');
        const closeBtn = row.querySelector('.btn-close-mail');

        // 1. Espansione della riga e cambio stato in "Letto"
        header.addEventListener('click', () => {
            const isExpanded = content.style.display === 'block';

            // Animazione fluida simulata con display/hide (CSS gestisce la grafica delle row)
            if (isExpanded) {
                content.style.display = 'none';
            } else {
                content.style.display = 'block';
                // Quando l'apre, lo marca come 'letto'
                // In una versione database, qui scatterà una fetch in background per marcare il record
                if (row.classList.contains('unread')) {
                    row.classList.remove('unread');
                    row.classList.add('read');
                }
            }
        }); 

        content.addEventListener('click', (e) => {
            e.stopPropagation();
        });

        // 2. Tasto chiudi all'interno del pannello
        if (closeBtn) {
            closeBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                content.style.display = 'none';
            });
        }
    });
});
