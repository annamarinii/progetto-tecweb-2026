document.addEventListener('DOMContentLoaded', () => {

    function attaccaEventiDomande() {
        const mailRows = document.querySelectorAll('.mail-row');

        mailRows.forEach(row => {
            const header = row.querySelector('.mail-row-header');
            const content = row.querySelector('.mail-content');
            
            if (!header.dataset.listener) {
                header.addEventListener('click', () => {
                    const isOpen = row.classList.toggle('open');
                    
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
                            }).catch(err => console.error("Errore aggiornamento lettura:", err));
                        }
                    }
                });
                header.dataset.listener = "true";
            }

            if (!content.dataset.listener) {
                content.addEventListener('click', (e) => e.stopPropagation());
                content.dataset.listener = "true";
            }
        });

        const replyForms = document.querySelectorAll('.mail-reply-form');
        replyForms.forEach(form => {
            if(!form.dataset.ajaxBound) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const btn = this.querySelector('.btn-send-reply');
                    const rigaCorrente = this.closest('.mail-row');

                    if(btn) {
                        btn.textContent = 'Invio...';
                        btn.classList.add('btn-loading'); // Usiamo una classe CSS
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
                            
                            if(rigaCorrente) {
                                rigaCorrente.classList.add('fade-out'); // Animazione via CSS
                                setTimeout(() => {
                                    rigaCorrente.remove();
                                    const lista = document.querySelector('.gmail-list');
                                    if (lista && lista.querySelectorAll('.mail-row').length === 0) {
                                        lista.innerHTML = '<p class="empty-msg">Tutte le domande hanno ricevuto risposta.</p>';
                                    }
                                }, 400);
                            }
                        } else {
                            mostraMessaggioDomande('Errore durante l\'invio.', 'error');
                            if(btn) { btn.textContent = 'Invia Risposta'; btn.classList.remove('btn-loading'); }
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        mostraMessaggioDomande('Errore di comunicazione.', 'error');
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
        // Usiamo classi semantiche per lo stile
        msg.className = `ajax-dynamic-msg-domande msg-${tipo}`;
        msg.textContent = testo;
        
        const h2 = contenitoreAttivo.querySelector('h2');
        if(h2) h2.insertAdjacentElement('afterend', msg);
        else contenitoreAttivo.prepend(msg);

        msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            msg.classList.add('fade-out');
            setTimeout(() => msg.remove(), 500);
        }, 3000);
    }

    attaccaEventiDomande();
});