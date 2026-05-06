document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-registrazione');
    
    if (form) {
        const inputs = form.querySelectorAll('input');

        const validaCampo = (input, isSubmit = false) => {
            const erroreSpan = document.getElementById(`err-${input.id}`);
            let messaggio = "";

            // Reset dello stato tramite classi CSS (Pura SoC)
            input.classList.remove('invalid');
            if (erroreSpan) {
                erroreSpan.classList.remove('visible');
                erroreSpan.textContent = "";
            }

            // Evita errore "campo richiesto" se vuoto e non è fase di invio
            if (input.value.trim() === '' && !isSubmit) {
                return true;
            }

            // 1. Controllo validità HTML5 (pattern, email, required)
            if (!input.checkValidity()) {
                input.classList.add('invalid');
                messaggio = input.title || "Campo non valido";
            }

            // 2. Controllo specifico: password match
            if (input.id === 'ripeti_pass') {
                const passOriginale = document.getElementById('pass').value;
                if (input.value !== passOriginale) {
                    input.classList.add('invalid');
                    messaggio = "Le password non coincidono";
                }
            }

            // Gestione visibilità errore tramite classi
            if (messaggio && erroreSpan) {
                erroreSpan.textContent = messaggio;
                erroreSpan.classList.add('visible'); 
                return false;
            }
            return true;
        };

        // Controlli in tempo reale all'uscita dal campo
        inputs.forEach(input => {
            input.addEventListener('blur', () => validaCampo(input));
        });

        // Validazione finale all'invio
        form.addEventListener('submit', (e) => {
            e.preventDefault(); // BLOCCA il ricaricamento della pagina

            let formValido = true;
            inputs.forEach(input => {
                if (!validaCampo(input, true)) {
                    formValido = false;
                }
            });

            if (formValido) {
                const btnSubmit = form.querySelector('button[type="submit"]');
                btnSubmit.disabled = true;
                btnSubmit.textContent = "Registrazione in corso...";

                const formData = new FormData(form);

                // Invio asincrono
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    let msgDiv = document.getElementById('messaggio-ajax');
                    if (!msgDiv) {
                        msgDiv = document.createElement('div');
                        msgDiv.id = 'messaggio-ajax';
                        form.appendChild(msgDiv);
                    }

                    if (data.status === 'success') {
                        msgDiv.className = 'form-message message-success';
                        msgDiv.innerHTML = `🎉 ${data.message} <a href="Login.php">Vai al Login</a>`;
                        form.reset();
                        // Opzionale: riporta il bottone allo stato originale dopo il reset
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = "Registrati Subito";
                    } else {
                        msgDiv.className = 'form-message message-error';
                        msgDiv.textContent = data.message;
                        btnSubmit.disabled = false;
                        btnSubmit.textContent = "Registrati Subito";
                    }
                })
                .catch(err => {
                    console.error("Errore:", err);
                    alert("Errore di connessione al server.");
                    btnSubmit.disabled = false;
                    btnSubmit.textContent = "Registrati Subito";
                });
            }
        });
    }
}); 