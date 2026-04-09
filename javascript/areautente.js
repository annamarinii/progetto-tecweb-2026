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
                
                inputs.forEach(input => {
                    input.removeAttribute('readonly');
                });

                this.textContent = 'Salva Modifiche';
                this.classList.add('btn-save'); 
                
                inputs[0].focus();

            } else {
                this.type = 'submit';
                form.submit();
            }
        });
    }
});