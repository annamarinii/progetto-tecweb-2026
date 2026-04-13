document.addEventListener('DOMContentLoaded', () => {

    // Funzione per attaccare la logica dell'accordion e pre-animazione ai blocchi domande
    function attaccaEventiDomande() {
        const mailRows = document.querySelectorAll('.mail-row');

        mailRows.forEach(row => {
            const header = row.querySelector('.mail-row-header');
            const content = row.querySelector('.mail-content');
            
            // Attacca solo se non è già attaccato per evitare duplicati
            if (!header.dataset.listener) {
                header.addEventListener('click', () => {
                    const isExpanded = content.style.display === 'block';
                    content.style.display = isExpanded ? 'none' : 'block';

                    if (row.classList.contains('unread')) {
                        row.classList.remove('unread');
                        row.classList.add('read');
                        
                        const idDomanda = row.dataset.id;
                        if (idDomanda) {
                            const fd = new FormData();
                            fd.append('segna_letta_admin', 'si');
                            fd.append('id_domanda', idDomanda);
                            fetch('../php-pages/AreaAdmin.php', {
                                method: 'POST',
                                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                                body: fd
                            }).catch(err => console.error("Errore aggiornamento stato lettura:", err));
                        }
                    }
                });
                header.dataset.listener = "true";
            }

            if (!content.dataset.listener) {
                content.addEventListener('click', (e) => {
                    e.stopPropagation();
                });
                content.dataset.listener = "true";
            }
        });

        // Binding dei SUBMIT dei form interni per le risposte (AJAX Real)
        const replyForms = document.querySelectorAll('.mail-reply-form');
        replyForms.forEach(form => {
            if(!form.dataset.ajaxBound) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const btn = this.querySelector('.btn-send-reply');
                    const rigaCorrente = this.closest('.mail-row'); // Individua la riga della domanda

                    if(btn) {
                        btn.textContent = 'Invio in corso...';
                        btn.style.opacity = '0.7';
                        btn.style.pointerEvents = 'none';
                    }

                    const formData = new FormData(this);
                    fetch(this.action, {
                        method: 'POST',
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            mostraMessaggioDomande('Risposta inviata con successo!', 'success');
                            
                            // --- MODIFICA QUI: Rimuovi visivamente la riga della domanda ---
                            if(rigaCorrente) {
                                rigaCorrente.style.transition = "opacity 0.4s ease";
                                rigaCorrente.style.opacity = "0";
                                setTimeout(() => {
                                    rigaCorrente.remove();
                                    // Se la lista è vuota, aggiungi un messaggio di cortesia
                                    const lista = document.querySelector('.gmail-list');
                                    if (lista && lista.querySelectorAll('.mail-row').length === 0) {
                                        lista.innerHTML = '<p style="padding:20px;">Tutte le domande hanno ricevuto risposta.</p>';
                                    }
                                }, 400);
                            }

                            // Aggiorniamo comunque la lista se il server manda HTML (opzionale se rimuovi già a mano)
                            if(data.html_domande) {
                                const grid = document.querySelector('.gmail-list');
                                if(grid) {
                                    grid.innerHTML = data.html_domande;
                                    attaccaEventiDomande(); 
                                }
                            }
                        } else {
                            mostraMessaggioDomande('Errore durante l\'invio.', 'error');
                            if(btn) { btn.textContent = 'Invia Risposta'; btn.style.opacity = '1'; btn.style.pointerEvents = 'auto'; }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        mostraMessaggioDomande('Errore di comunicazione col server.', 'error');
                    });
                });
                form.dataset.ajaxBound = "true";
            }
        });
    }

    function mostraMessaggioDomande(testo, tipo) {
        document.querySelectorAll('.ajax-dynamic-msg-domande').forEach(msg => msg.remove());
        const contenitoreAttivo = document.querySelector('#nuove-domande');
        if (!contenitoreAttivo) return;

        const msg = document.createElement('div');
        msg.className = 'ajax-dynamic-msg-domande';
        const colore = tipo === 'success' ? 'green' : 'red';
        msg.innerHTML = `<div style="color:${colore}; padding:10px; border:1px solid ${colore}; margin-bottom:20px; font-weight:bold; background: white;">${testo}</div>`;
        
        const h2 = contenitoreAttivo.querySelector('h2');
        if(h2) {
            h2.insertAdjacentElement('afterend', msg);
        } else {
            contenitoreAttivo.prepend(msg);
        }

        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            msg.style.transition = "opacity 0.5s ease";
            msg.style.opacity = "0";
            setTimeout(() => { msg.remove(); }, 500);
        }, 3000);
    }

    // Inizializzazione al caricamento
    attaccaEventiDomande();
});