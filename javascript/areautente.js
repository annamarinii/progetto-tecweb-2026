document.addEventListener('DOMContentLoaded', () => {
    // --- GESTIONE TAB
    const tabLinks = document.querySelectorAll('.tab-link');
    const tabContents = document.querySelectorAll('.tab-content');

    tabLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault(); 
            tabLinks.forEach(l => l.classList.remove('active'));
            tabContents.forEach(c => c.classList.remove('active'));
            this.classList.add('active');
            const targetId = this.getAttribute('data-target');
            document.getElementById(targetId).classList.add('active');
        });
    });

    // --- GESTIONE MODIFICA PROFILO
    const btnModifica = document.querySelector('.btn-auth');
    const inputs = document.querySelectorAll('.profile-form input');
    const form = document.querySelector('.profile-form');

    if (btnModifica) {
        btnModifica.addEventListener('click', function() {
            // Se il bottone è in modalità "Modifica"
            if (this.textContent === 'Modifica Profilo') {
                
                // 1. Rendi i campi editabili (tranne l'email se vuoi che sia fissa)
                inputs.forEach(input => {
                    input.removeAttribute('readonly');
                });

                // 2. Cambia aspetto al bottone
                this.textContent = 'Salva Modifiche';
                this.classList.add('btn-save'); 
                
                // 3. Porta il focus sul primo campo
                inputs[0].focus();

            } else {
                // Se il bottone è in modalità "Salva"
                // Trasformiamo il bottone in 'submit' per inviare il form al PHP
                this.type = 'submit';
                form.submit();
            }
        });
    }
});