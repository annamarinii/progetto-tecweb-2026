document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-registrazione');
    const inputs = form.querySelectorAll('input');

    const validaCampo = (input, isSubmit = false) => {
        const erroreSpan = document.getElementById(`err-${input.id}`);
        let messaggio = "";

        // Reset dello stato
        input.classList.remove('invalid');
        if (erroreSpan) erroreSpan.style.display = 'none';

        // Evita di mostrare errore "campo richiesto" se è vuoto e non è fase di submit
        if (input.value.trim() === '' && !isSubmit) {
            return true;
        }

        // 1. Controllo validità generale (pattern, email, required)
        if (!input.checkValidity()) {
            input.classList.add('invalid');
            messaggio = input.title || "Campo non valido";
        }

        // 2. Controllo specifico per la ripetizione password
        if (input.id === 'ripeti_pass') {
            const passOriginale = document.getElementById('pass').value;
            if (input.value !== passOriginale) {
                input.classList.add('invalid');
                messaggio = "Le password non coincidono";
            }
        }

        // Mostra il messaggio se c'è un errore
        if (messaggio && erroreSpan) {
            erroreSpan.textContent = messaggio;
            erroreSpan.style.display = 'block';
            return false;
        }
        return true;
    };

    // Applica i controlli a ogni input
    inputs.forEach(input => {
        // Rimosso il controllo 'input' in tempo reale per non dar fastidio mentre l'utente scrive.
        
        // Controlla quando l'utente clicca fuori dal campo
        input.addEventListener('blur', () => validaCampo(input));
    });

    // Controllo finale al momento dell'invio
    form.addEventListener('submit', (e) => {
        let formValido = true;
        inputs.forEach(input => {
            if (!validaCampo(input, true)) formValido = false;
        });

        if (!formValido) {
            e.preventDefault(); // Blocca l'invio del form
            alert("Correggi gli errori prima di inviare.");
        }
    });
});